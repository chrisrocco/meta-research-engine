<?php

/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 4/8/2017
 * Time: 7:47 PM
 */
class ConflictDetection {

    function newConflict ($encodingA, $encodingB) {
        $conflict = Conflict($encodingA, $encodingB);

        //Structure
        if (count($encodingA->branches) != count($encodingB->branches)) {
            $structureError = [
                'branchesA' => "",
                'branchesB' => ""
            ];
            $conflict->addError("structure", $structureError);
        }

        //Scope
         

        //Value


        return $conflict;
    }
}

class Conflict {

    private $encodingA;
    private $encodingB;
    private $errors;

    function __construct($encodingA, $encodingB) {
        $this->encodingA = $encodingA;
        $this->encodingB = $encodingB;
        $this->errors = array();
    }

    public function getErrors() {
        return $this->errors;
    }

    public function addError($type, $body) {
        if ($type !== "structure" ||
            $type !== "scope" ||
            $type !== "value") {
            //TODO: Throw an error
        }
        if (!isset($body)) {
            //TODO: Throw a different error
        }

        $this->errors[] = [
            'type' => $type,
            'body' => $body
        ];
    }


}