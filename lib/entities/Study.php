<?php
namespace Entities;
use QueryBank;

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/22/2017
 * Time: 5:11 PM
 */
class Study {

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

    function addPaper($pmcID, $title){
        global $documentHandler;
        if ($documentHandler->has("papers", $pmcID)) {
            throw new Exception("A paper with pmcID " . $pmcID . " already exists");
        }

        //Create the paper document
        $paper = new ArangoDBClient\Document();
        $paper->set("_key", $pmcID);
        $paper->set("title", $title);
        $documentHandler->save("papers", $paper);

        //Create the edge from the new paper to the research study
        $edge = new ArangoDBClient\Document();
        $edge->set("_from", "papers/" . $pmcID);
        $edge->set("_to", "research_studies/" . $this->ID);
        $documentHandler->save("paper_of", $edge);
    }



    public static function create($name){

    }
}