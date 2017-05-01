<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:33 PM
 */
class StudyTest extends \Tests\BaseTestCase
{
    function testCreateStudy(){
        $random_name = "study " . rand(0, 9999);
        $response = $this->runApp("POST", "/studies", [
            'name'  =>  $random_name
        ]);

        self::assertEquals(200, $response->getStatusCode());

        $studies = \Models\Study::getByExample( [ 'name' => $random_name] );
        $study = $studies[0];

        return $study;
    }

    /**
     * @depends testCreateStudy
     * @param $study_name \Models\Study
     */
    function testAddPaper( $study ){

        $response = $this->runApp("POST", "/studies/".$study->key()."/papers", [
            "title"     =>  "test paper",
            "pmcID"     =>  rand(100000, 20000)
        ]);

        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @depends testCreateStudy
     */
    function testGetStructure( $study ){
        $key = $study->key();
        $response = $this->runApp("GET", "/studies/$key/structure");

        self::assertEquals(200 || 400, $response->getStatusCode());
    }

    /**
     * @depends testCreateStudy
     */
    function testGetVariables( $study ){
//        $key = "2826667";
        $key = $study->key();
        $response = $this->runApp("GET", "/studies/$key/variables");

        self::assertEquals(200 || 400, $response->getStatusCode());
    }
}