<?php

use ArangoDBClient\DocumentHandler as ArangoDocumentHandler;
use ArangoDBClient\Document as ArangoDocument;
use ArangoDBClient\Statement as ArangoStatement;

class ConflictManager {

    function generateConflictReport($assignments_array){
        assert($this->variables);

        $conflicts = array_merge(
            $this->compareStructures($assignments_array),
            $this->compareScopes($assignments_array),
            $this->compareValues($assignments_array)
        );
        return $conflicts;
    }

    /*-------------------------------*/
    /* Conflict Detection Algorithms */
    /*-------------------------------*/
    private function compareStructures($assignments_array){
        $structure_conflicts = [];                                              // A place to store conflicts
        /* Start Scan */
        $inputs = [];                                                           // A place to store the different user responses
        foreach($assignments_array as $assignment) {                            // For every assignment we are comparing
            $input = $this->getBranchCount($assignment);                        // Get it's number of branches
            $this->recordResponse($inputs, $input, $assignment['_key']);
        }
        if(count($inputs) > 1){                                                 // More than one response was recorded
            $structure_conflicts[] = $this->newConflict("structure", [
                "inputs" => $inputs
            ]);
        }
        /* End Scan */
        return $structure_conflicts;
    }
    private function compareScopes($assignments_array){
        $scope_conflicts = [];
        /* Start Scan */
        foreach($this->variables as $fieldName){                     // For every variable in the database
            $inputs = [];                                            // A place to store the scope inputs
            foreach($assignments_array as $assignment){              // For every assignment
                $input = $this->getScope($fieldName, $assignment);   // Check what they said the field's scope was
                $this->recordResponse($inputs, $input, $assignment['_key']);// Record their response into inputs
            }
            if(count($inputs) > 1){
                $scope_conflicts[] = $this->newConflict("scope", [
                    "variable" => $fieldName,
                    "inputs" => $inputs
                ]);
            }                             // If there were multiple responses
        }
        /* End Scan */
        return $scope_conflicts;
    }
    private function compareValues($assignments_array){
        /* Start Scan */
        $comparisonSchedule = [];
        foreach($assignments_array as &$assignment){
            $myKey = $assignment['_key'];
            /* Schedule constants */
            $comparisonSchedule['constants group'][$myKey] = $assignment['encoding']['constants'];
            /* Schedule branches */
            foreach($assignment['encoding']['branches'] as $key => $value){
                $comparisonSchedule['branch '.$key][$myKey] = $value;
            }
        }

        $value_conflicts = $this->executeSchedule($comparisonSchedule);
        return $value_conflicts;
    }
    private function executeSchedule($schedule){

        $valueConflicts = [];
        /* for each group */
        foreach($schedule as $groupName => $group){
            /* for each question */
            foreach($this->variables as $variable){
                /* for each assignment */
                $inputs = []; // records the responses to this question

                foreach($group as $assignmentKey => $assignment){
                    /* get this assignments input to the question (variable) */
                    $input = $this->getInput($variable, $assignment);
                    if(!$input) continue;
                    $this->recordResponse($inputs, $input, $assignmentKey);
                }

                if(count($inputs) > 1 ){
                    $valueConflicts[] = $this->newConflict("value", [
                        "variable" => $variable,
                        "scope" => $groupName,
                        "inputs" => $inputs
                    ]);
                }
            }

        }

        return $valueConflicts;
    }

    /*------------------*/
    /* Helper Functions */
    /*------------------*/
    /**
     * Given a raw assignment object, gets the scope
     * @param $fieldName _key of the study-level variable to search for
     * @param $assignment raw assignment object
     * @return "constant" | "variable" | null
     */
    private function getScope($fieldName, $assignment){
        foreach ($assignment['encoding']['constants'] as $variableInstance) {
            if ($variableInstance['field'] === $fieldName) {
                return "constant";
            }
        }
        return "variable";
    }
    /**
     * Gets the variable response from the given branch. Can be 'constants' or any element of 'branches'
     * Runs in linear time with respect to count($branch)
     * @param $fieldName Unique key of the study-level variable
     * @param $branch Array of variable instances
     * @return 'content' object of the variable instance corresponding to fieldName, null otherwise
     */
    private function getInput($fieldName, $branch){
        foreach ($branch as $variableInstance) {
            if ($variableInstance['field'] === $fieldName) {
                return $variableInstance['content'];       // TODO - account for different variable types
            }
        }
    }
    private function getBranchCount($assignment){
        return count($assignment['encoding']['branches']);
    }
    private function recordResponse(&$inputs, $response, $assignmentKey){
        /* If the someone else has already responded this way */
        foreach($inputs as &$input){

            if($input['response'] == $response){
                array_push($input['assignmentKeys'], $assignmentKey);
                return;
            }
        }
        /* Else, record the new response */
        $inputs[] = [
            "response" => $response,
            "assignmentKeys" => [$assignmentKey]
        ];
    }
    private function newConflict($type, $body){
        return [
            "type" => $type,
            "body" => $body
        ];
    }

    // TODO - refractor into another class
    /**
     * Gets the paper for a given assignment
     * @param $assignmentID string _id of the assignment in question
     * @return string _id of the paper
     */
    private function getPaperID ($assignmentID) {
        if (!$this->arangodb_documentHandler->has('assignments', $assignmentID)) {
            echo [
                'msg' => "Assignment ".$assignmentID." does not exist",
                'status' => 400
            ];
        }

        $paperStatement = new ArangoStatement($this->arangodb_connection, [
            'query' => 'FOR paper IN INBOUND @assignmentID assignment_of
                        RETURN paper._id',
            'bindVars' => [
                'assignmentID' => "assignments".$assignmentID
            ],
            '_flat' => true
        ]);
        return $paperStatement->execute()->getAll()[0];
    }

    /**
     * Handles the storing of conflicts in the database
     * @param $paperID string _id of the paper, acting as the entry point
     * @return ['msg' => <return message>, 'status' => <html code>]
     */
    function updateConflictsByPaperID ($paperID) {

        //Generate conflicts
        $conflictNeighborsStatement = new ArangoStatement($this->arangodb_connection, [
            'query' => 'FOR assignment IN INBOUND @paperID assignment_of
                            RETURN assignment',
            'bindVars' => [
                'paperID' => $paperID
            ],
            '_flat' => true
        ]);
        $conflicts = $this->compare($conflictNeighborsStatement->execute()->getAll());

        //Remove old conflicts
        $removeOldConflictsStatement = new ArangoStatement($this->arangodb_connection, [
            'query' => 'FOR conflict IN INBOUND @paperID conflict_of
                        REMOVE conflict IN conflicts',
            'bindVars' => [
                'paperID' => $paperKey
            ],
            '_flat' => true
        ]);
        $removeOldConflictsStatement->execute();

        //Create the new conflicts and corresponding edges
        foreach ($conflicts as $conflict) {
            //Create the conflicts document (the conflict)
            $conflictID = $this->arangodb_documentHandler->save('conflicts', ArangoDocument::createFromArray($conflict));

            if (!$conflictID) {
                return [
                    'msg' => "Could not store conflicts in database",
                    'status' => 500
                ];
            }

            //Create the conflict_of edge
            $edge = ArangoDocument::createFromArray( [
                '_from' => $conflictID,
                '_to' => $paperKey
            ]);
            $edgeID = $this->arangodb_documentHandler->save('conflict_of', $edge);

            if (!$edgeID) {
                return [
                    'msg' => "Could create edge from conflict to paper",
                    'status' => 500
                ];
            }
        }
        return [
            'msg' => "Successfully updated conflict state for paper" . $paperKey,
            'status' => 200
        ];
    }

    /**
     * Handles the storing of conflicts in the database. Counterpart to updateConflictsByPaperID
     * @param $paperID string _id of the paper, acting as the entry point
     * @return ['msg' => <return message>, 'status' => <html code>]
     */
    function updateConflictsByAssignmentKey ($assignmentID) {
        return $this->updateConflictsByPaperID($this->getPaperID($assignmentID));
    }

    /*----------------*/
    /* Initialization */
    /*----------------*/
    public $variables;

    public function __construct($studyName) {
        $this->variables = json_decode("[ \"acclimationDuration\", \"acclimationPeriod\", \"ageAtStart\", \"ageAtWeight\", \"airCirculation\", \"animalLocations\", \"averageFinalWeight\", \"beddingMaterial\", \"breed\", \"cageType\", \"changeFrequency\", \"compoundFrequency\", \"compoundName\", \"constantTemperature\", \"darkHours\", \"daysOnTreatment\", \"dietID\", \"dietType\", \"dietVendor\", \"dietVendorCity\", \"dosage\", \"enrichmentType\", \"errorOfMeasurmentType\", \"errorOfMeasurmentValue\", \"ethicalStatement\", \"exerciseFreq\", \"exerciseType\", \"facilityCityState\", \"facilityCountry\", \"facilityHumidity\", \"facilityName\", \"feedingFrequency\", \"forcedExcecise\", \"geneticManipulationType\", \"lightHours\", \"lightingSchedule\", \"lightStartTime\", \"micePerCage\", \"mouseVendorName\", \"pathogenFreeEnvironment\", \"percentCarbohydrates\", \"percentEnergy\", \"percentFat\", \"percentProtein\", \"routeOfAdministration\", \"sampleSize\", \"sex\", \"surgeryType\", \"temperatureRange\", \"vendorCountry\", \"vendorName\", \"whereReported\" ]", true);
    }
}