<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/21/2017
 * Time: 12:40 AM
 */

namespace uab\mre\app;


class RectangleService
{
    static function toString( $data ){
        if(isset($data['value'])) return $data['value'];
        if(isset($data['selections'])){
            return implode(", ", $data['selections']);
        }
        return "no response";
    }
}