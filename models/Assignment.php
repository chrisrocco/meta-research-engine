<?php

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/2/2017
 * Time: 12:22 AM
 */
class Assignment {
    public static function validateCompletion($input){
        return (
            is_int($input) && $input >= 0 && $input <= 100
        );
    }
    public static function validateDone($input){
        return is_bool($input);
    }
}