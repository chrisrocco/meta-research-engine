<?php


use \Models\Vertices\Project\Project;
use \Models\Vertices\User;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:33 PM
 *
 *
 * Follows the lifecycle of a project
 *
 * 1.) New project is created                         |   POST    /projects
 * 2.) Paper is added to the project                  |   POST    /projects/{key}/paper
 * 3.) User is enrolled in the project                |   POST    /projects/{key}/members
 * 4.) Structure of the project is fetched            |   GET     /projects/{key}/structure
 * 5.) Variables of the project are fetched           |   GET     /projects/{key}/variables
 */
class ProjectTest extends \Tests\BaseTestCase
{

    protected $withMiddleware = false;

    function testCreateProject(){
        $random_name = "project " . rand(0, 9999);
        $response = $this->runApp("POST", "/projects", [
            'name'  =>  $random_name,
            'description'   =>  "A test project2"
        ]);

        self::assertEquals(200, $response->getStatusCode());

        $projects = Project::getByExample( [ 'name' => $random_name] );
        $project = $projects[0];

        return $project;
    }

    /**
     * @depends testCreateProject
     * @param $project
     */
    function testGetPapers( $project ){
        $response = $this->runApp("GET", "/projects/".$project->key()."/papers");
        $papers = json_decode( $response->getbody() );
//        var_dump($papers);

        self::assertEquals( 200, $response->getStatusCode() );
    }


    /**
     * @depends testCreateProject
     * @param
     */
    function testGetStructure( $project ){
        $key = $project->key();
        $response = $this->runApp("GET", "/projects/$key/structure");

        $status = $response->getStatusCode();
        self::assertTrue(200 === $status || 400 === $status);
    }

    /**
     * @depends testCreateProject
     */
    function testGetVariables( $project ){
//        $key = "2826667";
        $key = $project->key();
        $response = $this->runApp("GET", "/projects/$key/variables");

        $status = $response->getStatusCode();
        self::assertTrue(200 === $status || 400 === $status);    }

    /**
     * @depends testCreateProject
     */
    function testGetProjects( $project ){
        $response = $this->runApp("GET", "/loadProjects");

       // echo ( (string)$response->getBody() );
        $status = $response->getStatusCode();
        self::assertTrue(200 === $status || 400 === $status);
    }

    /**
     * @var $user User
     */
    static $user;
    static function setUpBeforeClass() {
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
        self::$user = $user;
//        echo PHP_EOL.self::$user->key();
    }
}