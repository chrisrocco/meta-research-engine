<?php

use ArangoDBClient\DocumentHandler as ArangoDocumentHandler;
use ArangoDBClient\Document as ArangoDocument;
use ArangoDBClient\Statement as ArangoStatement;

class ConflictManager {


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

    function compare($assignments_array){
        $conflicts = array_merge(
            $this->compareStructures($assignments_array),
            $this->compareScopes($assignments_array),
            $this->compareValues($assignments_array)
        );

        return $conflicts;
    }
    function test($assignments_array){
        $this->compareValues($assignments_array);
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
            $inputs[$input][] = $assignment["_key"];
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
                $inputs[$input][] = $assignment["_key"];               // Record their response into inputs
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
        $value_conflicts = [];
        /* Start Scan */
        $comparisonSchedule = [];
        foreach($assignments_array as &$assignment){
            $myKey = $assignment['_key'];
//            Schedule constants
            $comparisonSchedule['constants group'][$myKey] = $assignment['encoding']['constants'];
//            Schedule branches
            foreach($assignment['encoding']['branches'] as $key => $value){
                $comparisonSchedule['branch '.$key][$myKey] = $value;
            }
        }

        echo "Schedule Object \r\n";
        echo "--------------- \r\n";
        echo json_encode($comparisonSchedule, JSON_PRETTY_PRINT);
        /* End Scan */
        return $value_conflicts;

        function executeSchedule($schedule){
            $valueConflicts = [];
            /* Foreach group */
            foreach($schedule as $groupName => $group){
                /* for each question */
                foreach($this->variables as $variable){
                    $inputs = []; // records the responses to this question

                    /* For each assignment */
                    foreach($group as $assignmentKey => $assignment){
                        /* get this assignments input to the question (variable) */
                        $input = $this->getInput($variable, $assignment);
                        $inputs[$variable][$assignmentKey] = $input;
                    }

                    $valueConflicts[] = $this->newConflict("value", [
                        "variable" => $variable,
                        "inputs" => $inputs
                    ]);
                }
            }

            return $valueConflicts;
        }
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
        if (!isset($this->variables)) {
            echo "src/app/ConflictManager::getScope called before study variables initialized"; //TODO: throw exception
            return null;
        }

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
                return $variableInstance['content'];
            }
        }
        return null;
    }
    private function getBranchCount($assignment){
        return count($assignment['encoding']['branches']);
    }
    private function newConflict($type, $body){
        return [
            "type" => $type,
            "body" => $body
        ];
    }


    /**
     * Initializes $this->variables
     * @param $studyName research_studies/_key
     */
    private function setVariables ($studyName) {
        $variablesStatement = new ArangoStatement ($this->arangodb_connection, [
            'query' => 'FOR variable IN INBOUND @studyName variable_of
                            RETURN variable._key',
            'bindVars' => [
                'studyName' => "research_studies/".$this->$studyName
            ],
            '_flat' => true
        ]);

        $this->variables = json_decode($variablesStatement->execute()->getAll());
    }

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

    /*----------------*/
    /* Initialization */
    /*----------------*/
    public $variables;
    private $arangodb_connection;
    private $arangodb_documentHandler;
    private $studyName;


    public function __construct($db_connection, $studyName) {
        $this->arangodb_connection = $db_connection;
        $this->arangodb_documentHandler = new ArangoDocumentHandler($db_connection);
        $this->studyName = $studyName;
        $this->setVariables($studyName);

        //$this->variables = json_decode("[ \"acclimationDuration\", \"acclimationPeriod\", \"ageAtStart\", \"ageAtWeight\", \"airCirculation\", \"animalLocations\", \"averageFinalWeight\", \"beddingMaterial\", \"breed\", \"cageType\", \"changeFrequency\", \"compoundFrequency\", \"compoundName\", \"constantTemperature\", \"darkHours\", \"daysOnTreatment\", \"dietID\", \"dietType\", \"dietVendor\", \"dietVendorCity\", \"dosage\", \"enrichmentType\", \"errorOfMeasurmentType\", \"errorOfMeasurmentValue\", \"ethicalStatement\", \"exerciseFreq\", \"exerciseType\", \"facilityCityState\", \"facilityCountry\", \"facilityHumidity\", \"facilityName\", \"feedingFrequency\", \"forcedExcecise\", \"geneticManipulationType\", \"lightHours\", \"lightingSchedule\", \"lightStartTime\", \"micePerCage\", \"mouseVendorName\", \"pathogenFreeEnvironment\", \"percentCarbohydrates\", \"percentEnergy\", \"percentFat\", \"percentProtein\", \"routeOfAdministration\", \"sampleSize\", \"sex\", \"surgeryType\", \"temperatureRange\", \"vendorCountry\", \"vendorName\", \"whereReported\" ]", true);
    }
}