<?php

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/23/2017
 * Time: 1:03 AM
 */
class Merge {

    /*
     * [
            "structure" => [
                [
                    "response" => 0,
                    "assignmentKeys" => [1, 2, 3]
                ]
            ],
            "scopes" => [],
            "values" => [
                "constants" => [],
                "branches" => [
                    [

                    ],
                    [

                    ]
                ]
            ]
        ];
    */

    public $master;
    private $encoding;
    private $assignmentKey;
    private $variables;


    function __construct($master, $assignment) {
        $this->variables = json_decode("[ \"acclimationDuration\", \"acclimationPeriod\", \"ageAtStart\", \"ageAtWeight\", \"airCirculation\", \"animalLocations\", \"averageFinalWeight\", \"beddingMaterial\", \"breed\", \"cageType\", \"changeFrequency\", \"compoundFrequency\", \"compoundName\", \"constantTemperature\", \"darkHours\", \"daysOnTreatment\", \"dietID\", \"dietType\", \"dietVendor\", \"dietVendorCity\", \"dosage\", \"enrichmentType\", \"errorOfMeasurmentType\", \"errorOfMeasurmentValue\", \"ethicalStatement\", \"exerciseFreq\", \"exerciseType\", \"facilityCityState\", \"facilityCountry\", \"facilityHumidity\", \"facilityName\", \"feedingFrequency\", \"forcedExcecise\", \"geneticManipulationType\", \"lightHours\", \"lightingSchedule\", \"lightStartTime\", \"micePerCage\", \"mouseVendorName\", \"pathogenFreeEnvironment\", \"percentCarbohydrates\", \"percentEnergy\", \"percentFat\", \"percentProtein\", \"routeOfAdministration\", \"sampleSize\", \"sex\", \"surgeryType\", \"temperatureRange\", \"vendorCountry\", \"vendorName\", \"whereReported\" ]", true);
        $this->encoding = $assignment['encoding'];
        $this->assignmentKey = $assignment['_key'];
        $this->master = $master;
    }

    function generateReport($assignments_array){
        $conflicts = array_merge(
            $this->compareStructures($assignments_array),
            $this->compareScopes($assignments_array),
            $this->compareValues($assignments_array)
        );
        return $conflicts;
    }

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

    private function getScope($fieldName, $assignment){
        foreach ($assignment['encoding']['constants'] as $variableInstance) {
            if ($variableInstance['field'] === $fieldName) {
                return "constant";
            }
        }
        return "variable";
    }
    private function getInput($fieldName, $branch){
        foreach ($branch as $variableInstance) {
            if ($variableInstance['field'] === $fieldName) {
                return $variableInstance['content'];       // TODO - account for different variable types
            }
        }
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


    public function getBranchCount(){
        return count($this->encoding['branches']);
    }

    function mergeStructures(){
        $input = count($this->encoding['branches']);
    }

    function recordStructureResponse($response){
        // check if value already exists
        foreach($this->master['structure'] as $record){
            if($record['response'] === $response){
                $record['assignmentKeys'][] = $this->assignmentKey;
                return;
            }
        }

        $this->master['structure'][] = [
            "response" => $response,
            "assignmentKeys" => [ $this->assignmentKey ]
        ];
    }

    function recordScopeResponse($fieldName, $scope){
        $this->master['scopes'][$fieldName][] = [

        ];
    }
}