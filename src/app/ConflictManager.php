<?php
class ConflictManager {

    function compare($assignments_array){
        $conflicts = [];

        $conflicts[] = $this->compareStructures($assignments_array);
        $conflicts[] = $this->compareScopes($assignments_array);
        $conflicts[] = $this->compareValues($assignments_array);

        return $conflicts;
    }

    private function compareStructures($assignments_array){
        $structure_conflicts = [];                                  // A place to store conflicts
        /* Start Scan */
        $inputs = [];                                               // A place to store the different user responses
        foreach($assignments_array as $assignment) {                // For every assignment we are comparing
            $input = count($assignment['encoding']['branches']);    // Get it's number of branches
            $inputs[$input] = $assignment["_key"];                  // Record their response
        }
        if(count($inputs) > 1) $structure_conflicts[] = $inputs;    // More than one response was recorded
        /* End Scan */
        return $structure_conflicts;
    }

    private function compareScopes($assignments_array){
        $scope_conflicts = [];
        /* Start Scan */
        $inputs = [];
        foreach($assignments_array as $assignment){

        }
        /* End Scan */
        return $scope_conflicts;
    }

    private function compareValues($assignments_array){
        $value_conflicts = [];
        /* Start Scan */
        $inputs = [];
        foreach($assignments_array as $assignment){

        }
        /* End Scan */
        return $value_conflicts;
    }
}