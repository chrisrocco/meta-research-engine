<?php


use \Models\Vertices\Study;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:33 PM
 *
 *
 * Follows the lifecycle of a study
 *
 * 1.) New study is created                         |   POST    /studies
 * 2.) Paper is added to the study                  |   POST    /studies/{key}/paper
 * 3.) Structure of the study is fetched            |   GET     /studies/{key}/structure
 * 3.) Variables of the study are fetched           |   GET     /studies/{key}/variables
 */
class StudyTest extends \Tests\BaseTestCase
{
    function testCreateStudy(){
        $random_name = "study " . rand(0, 9999);
        $response = $this->runApp("POST", "/studies", [
            'name'  =>  $random_name,
            'description'   =>  "A test study"
        ]);

        self::assertEquals(200, $response->getStatusCode());

        $studies = Study::getByExample( [ 'name' => $random_name] );
        $study = $studies[0];

        return $study;
    }

    /**
     * @depends testCreateStudy
     * @param $study_name Study
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

    /**
     * @depends testCreateStudy
     */
    function testGetProjects( $study ){
        $response = $this->runApp("GET", "/loadProjects");

        echo ( (string)$response->getBody() );
    }
}