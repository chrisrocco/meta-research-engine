<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 6/19/2017
 * Time: 1:05 AM
 */

namespace uab\MRE\dao;

class ParsedResponse
{
    public $question_key;
    public $scope;
    public $data;

    function __construct( $question_key, $scope, $data )
    {
        $this->question_key = $question_key;
        $this->scope = $scope;
        $this->data = $data;
    }
}