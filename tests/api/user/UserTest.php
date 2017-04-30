<?php

namespace Tests\API;
use Models\User;
use Tests\BaseTestCase;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 5:05 PM
 */

class UserTest extends BaseTestCase {

    public function testNewRegister(){
        $randomEmail = rand(0, 9999) . '@gmail.com';
        $response = $this->runApp('POST', '/users/register', [
            'first_name'    =>  "Unique",
            'last_name'     =>  "Email",
            'email'         =>  $randomEmail,
            'password'      =>  "password",
        ]);

        self::assertEquals(200, $response->getStatusCode());

        return $randomEmail;
    }

    /**
     * @param $existing_email
     * @depends testNewRegister
     */
    public function testExistingRegister( $existing_email ){

        $response = $this->runApp('POST', '/users/register', [
            'first_name'  => 'Chris',
            'last_name'  => 'Rocco',
            'email' =>  $existing_email,
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
    public function testValidate( $just_registered ){
        $user_set = User::getByExample( [ 'email'   =>  $just_registered ]);
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
    public function testLogin( $just_registered_email ){
        $user_set = User::getByExample( [ 'email'   =>  $just_registered_email ]);
        self::assertTrue( count($user_set) > 0 );
        $just_registered = $user_set[0];

        $response = $this->runApp("POST", "/users/login", [
            'email'     =>  $just_registered->get('email'),
            'password'  =>  $just_registered->get('password')
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
}