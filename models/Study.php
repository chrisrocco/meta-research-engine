<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:49 PM
 */

namespace Models;


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
}