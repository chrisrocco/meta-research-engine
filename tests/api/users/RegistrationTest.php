<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 6/19/2017
 * Time: 1:33 AM
 */

namespace Tests;


use uab\MRE\dao\User;
use vector\ArangoORM\DB\DB;

class RegistrationTest extends BaseTestCase
{
    private $user_data = [
        'first_name'    =>  'Jon',
        'last_name'     =>  'Doe',
        'email'         =>  'test@gmail.com',
        'password'      =>  'password'
    ];

    function testRegister(){
        $this->runApp("POST", "/users/register", [
            'first_name'    =>  $this->user_data['first_name'],
            'last_name'     =>  $this->user_data['last_name'],
            'email'         =>  $this->user_data['email'],
            'password'      =>  $this->user_data['password'],
        ]);

        $user = User::getByExample( [ 'email' => $this->user_data['email']]);
        self::assertEquals( 1, count($user) );

        $response = $this->runApp("POST", "/users/register", [
            'first_name'    =>  $this->user_data['first_name'],
            'last_name'     =>  $this->user_data['last_name'],
            'email'         =>  $this->user_data['email'],
            'password'      =>  $this->user_data['password'],
        ]);

        self::assertEquals( 409, $response->getStatusCode() );
    }

    function testLogin(){
        // Manually create a user
        User::create([
            'first_name'    =>  $this->user_data['first_name'],
            'last_name'     =>  $this->user_data['last_name'],
            'email'         =>  $this->user_data['email'],
            'password'      =>  password_hash($this->user_data['password'], PASSWORD_DEFAULT),
            'active'        =>  true,
            'hash_code'     =>  ""
        ]);
        $response = $this->runApp("POST", "/users/login", [
            'email'         =>  $this->user_data['email'],
            'password'      =>  $this->user_data['password'],
        ]);
        self::assertEquals( 200, $response->getStatusCode() );
    }

    function testBadLogin(){
        $response = $this->runApp("POST", "/users/login", [
            'email'         =>  "boob@boob.boob",
            'password'      =>  "boob",
        ]);
        self::assertEquals( 401, $response->getStatusCode() );
    }

    function testInactiveLogin(){
        // Manually create a user
        User::create([
            'first_name'    =>  $this->user_data['first_name'],
            'last_name'     =>  $this->user_data['last_name'],
            'email'         =>  $this->user_data['email'],
            'password'      =>  password_hash($this->user_data['password'], PASSWORD_DEFAULT),
            'active'        =>  false,
            'hash_code'     =>  ""
        ]);
        $response = $this->runApp("POST", "/users/login", [
            'email'         =>  $this->user_data['email'],
            'password'      =>  $this->user_data['password'],
        ]);
        self::assertEquals( 401, $response->getStatusCode() );
    }

    function tearDown()
    {
        parent::tearDown();
        $ch = DB::getCollectionHandler();
        $ch->truncate( User::$collection );
    }

}