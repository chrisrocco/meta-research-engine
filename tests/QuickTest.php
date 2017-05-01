<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 7:42 PM
 */

class QuickTest extends \Tests\BaseTestCase {
    function testQuickly(){
        $study = \Models\Vertices\Study::retrieve(2826667);
        $vars = $study->getVariablesFlat();

        echo json_encode($vars, JSON_PRETTY_PRINT);
    }
}