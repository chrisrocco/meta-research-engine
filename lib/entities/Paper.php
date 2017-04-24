<?php
namespace Entities;
use QueryBank;

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/22/2017
 * Time: 5:15 PM
 */
class Paper {

    private $ID;

    function __construct($ID){
        global $documentHandler;
        if (!$documentHandler->has("papers", $ID)) {
            throw new Exception("That paper does not exist");
        }

        $this->ID = $ID;
    }

    function getObject(){
        global $documentHandler;
        return $documentHandler->getById("papers", $this->ID)->getAll();
    }

    function mergeAssignment($assignmentHandler){
        echo "Going to merge assignment ";
        var_dump($assignmentHandler->getAssignment());


        $masterEncoding = [];
        $assignment = $assignmentHandler->getAssignment();

        foreach($assignment['encoding']['constants'] as $input){
            recordInput($masterEncoding['constants'], $input);
        }

        function recordInput(&$targetArray, $input){
            $scope = [

            ];
            $numBranches = [

            ];
            $values = [

            ];
        }
    }

    function getAssignments(){
    }

    function getMasterEncoding(){
        return $this->getObject()['masterEncoding'];
    }



    public static function create($studyName){
    }
}