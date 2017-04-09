<?php
class ConflictManager {

    function compare($assignments_array){
        $conflicts = [];

        $conflicts[] = [
            "Structure Conflicts" => $this->compareStructures($assignments_array)
        ];
        $conflicts[] = [
            "Scope Conflicts" => $this->compareScopes($assignments_array)
        ];
        $conflicts[] = [
            "Value Conflicts" => $this->compareValues($assignments_array)
        ];

        return $conflicts;
    }

    private function compareStructures($assignments_array){
        $structure_conflicts = [];                                  // A place to store conflicts
        /* Start Scan */
        $inputs = [];                                               // A place to store the different user responses
        foreach($assignments_array as $assignment) {                // For every assignment we are comparing
            $input = $this->getBranchCount($assignment);    // Get it's number of branches
            $inputs[$input][] = $assignment["_key"];
        }
        if(count($inputs) > 1) $structure_conflicts[] = $inputs;    // More than one response was recorded
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
//            if(count($inputs) > 1) ...                             // If there were multiple responses
            $scope_conflicts[] = [
                "Variable" => $fieldName,
                "Scope Inputs" => $inputs
            ];
        }
        /* End Scan */
        return $scope_conflicts;
    }

    private function compareValues($assignments_array){
        $value_conflicts = [];
        /* Start Scan */
        $inputs = [];
        foreach($this->variables as $fieldName){                     // For every variable in the database
            $inputs = [];                                            // A place to store the scope inputs
            foreach($assignments_array as $assignment){              // For every assignment
                $input = $this->getInput($fieldName, $assignment);   // Check what they said the field's scope was
                $inputs[$input][] = $assignment["_key"];               // Record their response into inputs
            }
//            if(count($inputs) > 1) ...                             // If there were multiple responses
            $value_conflicts[] = [
                "Variable" => $fieldName,
                "Value Inputs" => $inputs
            ];
        }
        /* End Scan */
        return $value_conflicts;
    }


    /*------------------*/
    /* Helper Functions */
    /*------------------*/

    // Caleb implement these
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

    /*----------------*/
    /* Initialization */
    /*----------------*/
    public $variables;
    public function __construct()
    {
        $this->variables = json_decode("[ \"acclimationDuration\", \"acclimationPeriod\", \"ageAtStart\", \"ageAtWeight\", \"airCirculation\", \"animalLocations\", \"averageFinalWeight\", \"beddingMaterial\", \"breed\", \"cageType\", \"changeFrequency\", \"compoundFrequency\", \"compoundName\", \"constantTemperature\", \"darkHours\", \"daysOnTreatment\", \"dietID\", \"dietType\", \"dietVendor\", \"dietVendorCity\", \"dosage\", \"enrichmentType\", \"errorOfMeasurmentType\", \"errorOfMeasurmentValue\", \"ethicalStatement\", \"exerciseFreq\", \"exerciseType\", \"facilityCityState\", \"facilityCountry\", \"facilityHumidity\", \"facilityName\", \"feedingFrequency\", \"forcedExcecise\", \"geneticManipulationType\", \"lightHours\", \"lightingSchedule\", \"lightStartTime\", \"micePerCage\", \"mouseVendorName\", \"pathogenFreeEnvironment\", \"percentCarbohydrates\", \"percentEnergy\", \"percentFat\", \"percentProtein\", \"routeOfAdministration\", \"sampleSize\", \"sex\", \"surgeryType\", \"temperatureRange\", \"vendorCountry\", \"vendorName\", \"whereReported\" ]", true);
    }
}