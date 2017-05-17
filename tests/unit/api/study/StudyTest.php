<?php


use \Models\Vertices\Study;
use \Models\Vertices\User;

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
 * 3.) User is enrolled in the study                |   POST    /studies/{key}/members
 * 4.) Structure of the study is fetched            |   GET     /studies/{key}/structure
 * 5.) Variables of the study are fetched           |   GET     /studies/{key}/variables
 */
class StudyTest extends \Tests\BaseTestCase
{
    function testCreateStudy(){
        $random_name = "study " . rand(0, 9999);
        $response = $this->runApp("POST", "/studies", [
            'name'  =>  $random_name,
            'description'   =>  "A test study2"
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
        $jsonPaperData = file_get_contents( __DIR__ . '/../../../data/papers.json');

        $response = $this->runApp("POST", "/studies/".$study->key()."/papers", [
            "papers" => $jsonPaperData
        ]);

        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @depends testCreateStudy
     * @param $study
     */
    function testGetPapers( $study ){
        $response = $this->runApp("GET", "/studies/".$study->key()."/papers");
        $papers = json_decode( $response->getbody() );
        var_dump($papers);

        self::assertEquals( 200, $response->getStatusCode() );
    }

    /**
     * @depends testCreateStudy
     * @param $study Study
     */
    function testAddUser ($study) {
        $response = $this->runApp("POST", "/studies/".$study->key()."/members", [
            'userKey' => $this->user->key(),
            'registrationCode' => $study->get('registrationCode')
        ]);

        $status = $response->getStatusCode();
        self::assertTrue(200 === $status || 409 === $status);
    }

    /**
     * @depends testCreateStudy
     */
    function testGetStructure( $study ){
        $key = $study->key();
        $response = $this->runApp("GET", "/studies/$key/structure");

        $status = $response->getStatusCode();
        self::assertTrue(200 === $status || 400 === $status);
    }

    /**
     * @depends testCreateStudy
     */
    function testGetVariables( $study ){
//        $key = "2826667";
        $key = $study->key();
        $response = $this->runApp("GET", "/studies/$key/variables");

        $status = $response->getStatusCode();
        self::assertTrue(200 === $status || 400 === $status);    }

    /**
     * @depends testCreateStudy
     */
    function testGetProjects( $study ){
        $response = $this->runApp("GET", "/loadProjects");

       // echo ( (string)$response->getBody() );
    }

    /**
     * @var $user User
     */
    private $user;
    function setUp() {
        $random_email = rand(0, 99999) . '@gmail.com';
        $password = 'password';

        $user = User::register(
            'Random',
            'Register',
            $random_email,
            'password'
        );
        $new_hash = $user->rehash();
        $user->validate( $new_hash );
        $this->user = $user;
    }
}