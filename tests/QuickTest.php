<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 7:42 PM
 */

class QuickTest extends \Tests\BaseTestCase {
    function testQuickly(){
        $studyModel = \Models\Vertices\Study::retrieve( 4249305 );
        $paperModel = $studyModel->getNextPaper()[0];

        var_dump( $paperModel );
    }
}