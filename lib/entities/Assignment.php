<?php
namespace Entities;
use QueryBank;

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/22/2017
 * Time: 5:13 PM
 */
class Assignment
{
    private $ID;

    function __construct($ID) {
        global $documentHandler;
        if (!$documentHandler->has("assignments", $ID)) {
            throw new Exception("That assignment does not exist");
        }

        $this->ID = $ID;
    }



    public function update($newData){
        $encoding = json_decode($newData['encoding'], false);
        global $documentHandler;
        $assignment = $documentHandler->get("assignments", $this->assignmentObject['_key']);
        $assignment->set("done", $newData['done']);
        $assignment->set("completion", $newData['completion']);
        $assignment->encoding = $encoding;
        return $documentHandler->replace($assignment);
    }

    public function getAssignment(){
        return QueryBank::execute("getAssignmentByID", [
            "assignmentID" => $this->ID
        ]);
    }

    public function pullRequest(){
        // create new paper handler
        // merge this assignment
    }



    public static function create($userID, $paperID){

    }
}