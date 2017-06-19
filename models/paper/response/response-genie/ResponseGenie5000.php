<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 6/19/2017
 * Time: 1:04 AM
 */

namespace uab\MRE\Paper\ResponseGenie5000;


use Models\Vertices\Paper\Paper;
use Models\Vertices\User;

class ResponseGenie5000
{
    function __construct( Paper $paper, ParsedAssignment $parsedAssignment )
    {
    }

    /**
     * @param $messy_encoding mixed The un-processed encoding data from the assignment record
     * @param User $user The user whom the assignment belongs to
     */
    public static function parseAssignment( $messy_encoding, User $user ){

    }
}