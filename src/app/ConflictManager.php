<?php
class ConflictManager {

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
    private function getScope($fieldName, $assignment){
        if(mt_rand(0,1) === 0){
            return "variable";
        }
        return "constant";
    }
    private function getInput($fieldName, $assignment){
        $sample_responses = [
            "3 weeks",
            "15 lbs",
            "5 grams",
            "74 degrees fareignheight",
            "Didn't say"
        ];
        return $sample_responses[mt_rand(0, count($sample_responses)-1)]; // A random variable
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

    /*----------------*/
    /* Initialization */
    /*----------------*/
    public $variables;
    public function __construct() {
        $this->variables = json_decode("[ \"acclimationDuration\", \"acclimationPeriod\", \"ageAtStart\", \"ageAtWeight\", \"airCirculation\", \"animalLocations\", \"averageFinalWeight\", \"beddingMaterial\", \"breed\", \"cageType\", \"changeFrequency\", \"compoundFrequency\", \"compoundName\", \"constantTemperature\", \"darkHours\", \"daysOnTreatment\", \"dietID\", \"dietType\", \"dietVendor\", \"dietVendorCity\", \"dosage\", \"enrichmentType\", \"errorOfMeasurmentType\", \"errorOfMeasurmentValue\", \"ethicalStatement\", \"exerciseFreq\", \"exerciseType\", \"facilityCityState\", \"facilityCountry\", \"facilityHumidity\", \"facilityName\", \"feedingFrequency\", \"forcedExcecise\", \"geneticManipulationType\", \"lightHours\", \"lightingSchedule\", \"lightStartTime\", \"micePerCage\", \"mouseVendorName\", \"pathogenFreeEnvironment\", \"percentCarbohydrates\", \"percentEnergy\", \"percentFat\", \"percentProtein\", \"routeOfAdministration\", \"sampleSize\", \"sex\", \"surgeryType\", \"temperatureRange\", \"vendorCountry\", \"vendorName\", \"whereReported\" ]", true);
    }
}