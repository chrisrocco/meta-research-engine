<?php

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/22/2017
 * Time: 5:11 PM
 */
class StudyHandler {

    private $ID;

    function __construct($ID) {
        global $documentHandler;
        if (!$documentHandler->has('research_studies', $ID)) {
            throw new Exception('No study with that ID');
        }
        $this->ID = $ID;
    }



    function getVariables() {
        return QueryBank::execute("getVariables", ["studyName" => $this->ID]);
    }

    function getStructure(){
        return QueryBank::execute("getStudyStructure", [ "studyName" => $this->ID ]);
    }



    public static function create($name){

    }
}