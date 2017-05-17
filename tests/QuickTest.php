<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 7:42 PM
 */

class QuickTest extends \Tests\BaseTestCase {
    function testQuickly(){

        $obj = json_decode('{"domains":[{"id":"diet","parent":"#","name":"Diet","description":"data related to the diet","icon":"fa fa-cutlery","$$hashKey":"object:3"},{"id":"lighting","parent":"#","name":"Lighting","description":"Ligting in the facility","icon":"fa fa-lightbulb-o","$$hashKey":"object:4"},{"id":"mice","parent":"#","name":"Mice","description":"mice information","icon":"fa fa-circle","$$hashKey":"object:5"},{"id":"sex","parent":"mice","name":"Sex","description":"How many of the mice were male and female?","icon":"fa fa-intersex","$$hashKey":"object:6"}],"questions":[{"id":"micePerCage","parent":"mice","type":"number","min":1,"max":10,"unit":"","name":"Mice Per Cage","question":"How many mice were there per cage?","$$hashKey":"object:24"},{"id":"males","parent":"sex","type":"number","min":1,"max":10,"unit":"mice","name":"Male Mice","question":"How many of the mice were male?","icon":"fa fa-mars","$$hashKey":"object:25"},{"id":"females","parent":"sex","type":"number","min":1,"max":10,"unit":"mice","name":"Female Mice","question":"How many of the mice were female?","icon":"fa fa-venus","$$hashKey":"object:26"}]}');
        echo json_encode( $obj, JSON_PRETTY_PRINT );
//        var_dump( $obj );
    }
}