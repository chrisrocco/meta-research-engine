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

        return $random_name;
    }

    /**
     * @depends testCreateStudy
     * @param $study_name \Models\Study
     */
    function testAddPaper( $study_name ){

        $studies = \Models\Study::getByExample( [ 'name' => $study_name] );
        $study = $studies[0];

        $response = $this->runApp("POST", "/studies/".$study->key()."/papers", [
            "title"     =>  "test paper",
            "pmcID"     =>  rand(100000, 20000)
        ]);

        self::assertEquals(200, $response->getStatusCode());
    }

}