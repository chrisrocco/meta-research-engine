<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/16/2017
 * Time: 2:08 PM
 */

$json = file_get_contents(__DIR__ . '/assignments.json' );

$assignments = json_decode( $json );

var_dump( $assignments );