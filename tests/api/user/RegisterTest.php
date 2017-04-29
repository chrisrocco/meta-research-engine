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
        'first_name'  => 'Chris',
        'last_name'  => 'Rocco',
        'email' =>  'chris.rocco7@gmail.com',
        'password'  =>  'password'
    ];


    public function testNewRegister(){
        $response = $this->runApp('POST', '/users/register', [
            'first_name'    =>  self::$test_data['first_name'],
            'last_name'     =>  self::$test_data['last_name'],
            'email'         =>  rand(0, 9999) . '@gmail.com',
            'password'      =>  self::$test_data['password'],
        ]);

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testExistingRegister(){

        $response = $this->runApp('POST', '/users/register',
            self::$test_data
        );

        self::assertEquals(409, $response->getStatusCode());
    }
}