<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:49 PM
 */

namespace Models;


use DB\Queries\QueryBank;
use Models\Core\VertexModel;

class Study extends VertexModel {

    static $collection = "studies";

    /**
     * @param $paper Paper
     */
    function addPaper( $paper ){
        PaperOf::create(
            $this->id(), $paper->id(), []
        );
    }

    /**
     * @param $domain Domain
     */
    function addDomain( $domain ){
        SubdomainOf::create(
            $this->id(), $domain->id(), []
        );
    }

    /**
     * @return array
     */
    public function getStructure(){
        return QueryBank::execute("get_study_structure", [
            "studyName" =>  $this->key()
        ])->getAll();
    }

    /**
     * @return array
     */
    public function getVariableNames(){
        return QueryBank::execute("get_study_variables", [
            "studyName" =>  $this->key()
        ])->getAll();
    }
}