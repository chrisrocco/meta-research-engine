<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 12:31 PM
 */

namespace uab\mre\lib;


class ObjValidator
{
    public static function forceSchema( $php_obj, $properties_arr ){
        foreach ( $properties_arr as $prop ){
            if( !isset($php_obj[$prop] )){
                throw new SchemaValidatorException($properties_arr, $prop, $php_obj);
            }
        }
    }
}