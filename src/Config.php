<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 6/13/2017
 * Time: 10:16 PM
 */

namespace uab\MRE\Config;

class Config
{
    static $settings;

    static function initSettings( $settings_array ){
        self::$settings = $settings_array;
    }

    static function getSetting( $name ){
        if( isset( self::$settings[$name] ) )
            return self::$settings[ $name ];
    }
}