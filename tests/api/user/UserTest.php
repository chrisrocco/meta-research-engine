<?php

namespace Tests\API;
use Models\Vertices\User;
use Tests\BaseTestCase;

/**
 * User: chris
 * Date: 4/29/17
 * Time: 5:05 PM
 *
 *
 * This test suite follows the lifecycle of a user using the public API.
 *
 * 1.) New user registers                               |   POST    /users/register
 * 2.) User validates his email using the hash_code     |   GET     /users/validate
 * 3.) User logs in and receives an API token           |   POST    /users/login
 * 4.) API token is used to access a secure route       |   GET     /secure
 */

class UserTest extends BaseTestCase {

    public function testNewRegister(){
        $randomEmail = rand(0, 9999) . '@gmail.com';
        $password = "password";

        $response = $this->runApp('POST', '/users/register', [
            'first_name'    =>  "Unique",
            'last_name'     =>  "Email",
            'email'         =>  $randomEmail,
            'password'      =>  $password,
        ]);

        self::assertEquals(200, $response->getStatusCode());

        return [
            'email'     =>  $randomEmail,
            'password'  =>  $password
        ];
    }

    /**
     * @param $existing_email
     * @depends testNewRegister
     */
    public function testExistingRegister( $fresh_creds ){

        $response = $this->runApp('POST', '/users/register', [
            'first_name'  => 'Chris',
            'last_name'  => 'Rocco',
            'email' =>  $fresh_creds['email'],
            'password'  =>  'password'
        ]);

        self::assertEquals(409, $response->getStatusCode());
    }

    /**
     * Takes a newly registered user, and makes sure their account is NOT active.
     * Sends a request to /users/validate with the users _key and hash
     * Fetches the users again to make sure their account has been activated
     * @param $just_registered
     * @depends testNewRegister
     */
    public function testValidate( $fresh_creds ){
        $user_set = User::getByExample( [ 'email'   =>  $fresh_creds['email'] ]);
        self::assertTrue( count($user_set) > 0 );
        $user = $user_set[0];
        self::assertFalse(  $user->get('active')    );

        $response = $this->runApp('GET', '/users/validate', [
            '_key'        =>  $user->key(),
            'hash_code' =>  $user->get('hash_code')
        ]);

        self::assertEquals(200, $response->getStatusCode());

        $user = User::retrieve( $user->key() );
        self::assertEquals(true, $user->get('active') );
    }

    /**
     * @depends testNewRegister
     * @param $just_registered_email string
     */
    public function testLogin( $fresh_creds ){
        $response = $this->runApp("POST", "/users/login", [
            'email'     =>  $fresh_creds['email'],
            'password'  =>  $fresh_creds['password']
        ]);

        self::assertEquals(200, $response->getStatusCode());
        self::assertContains('token', (string)$response->getBody());
    }

    public function testBadLogin(){
        $response = $this->runApp("POST", "/users/login", [
            'email'         =>  'fake@gmail.com',
            'password'      =>  'not a password'
        ]);

        self::assertEquals(401, $response->getStatusCode());
    }

    public function testSecuredRoute(){
        $response = $this->runApp("GET", '/secure');
        self::assertEquals(401, $response->getStatusCode());
    }

    /**
     * @depends testNewRegister
     * @param $fresh_creds
     */
    public function testAuthenticate( $fresh_creds ){

        $response = $this->runApp('POST', '/users/login', [
            'email'     =>  $fresh_creds['email'],
            'password'  =>  $fresh_creds['password']
        ]);

        $body = (string)$response->getBody();
        $token = json_decode($body, true)['token'];

        $next_response = $this->runApp('GET', '/secure', null, [
            [ 'Authorization', 'Bearer ' . $token ]
        ]);

        self::assertEquals(200, $next_response->getStatusCode());
    }
}