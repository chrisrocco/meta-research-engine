<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 6/19/2017
 * Time: 1:05 AM
 */

namespace uab\MRE\dao;

class ParsedAssignment
{
    /**
     * @var ParsedResponse[]
     */
    public $responses;
    public $user_key;

    function __construct( $user_key )
    {
        $this->user_key = $user_key;
    }

    function addResponse( ParsedResponse $response ){
        array_push( $this->responses, $response );
    }
}