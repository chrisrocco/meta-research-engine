<?php

namespace Tests\API;
use Tests\BaseTestCase;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 5:05 PM
 */

class LoginTest extends BaseTestCase {

    static $test_data = [
        'email' =>  'chris.rocco7@gmail.com',
        'password'  =>  'password'
    ];

    public function testLogin(){
        $response = $this->runApp("POST", "/users/login", self::$test_data);

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