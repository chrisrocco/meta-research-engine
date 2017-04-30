<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 2:01 PM
 */

namespace Tests\Models\User;


use Models\User;
use Tests\BaseTestCase;

class LoginTest extends BaseTestCase
{
    static $testUserData = [
        'first_name'  => 'Chris',
        'last_name'  => 'Rocco',
        'email' =>  'chris.rocco7@gmail.com',
        'password'  =>  'password'
    ];

    public function testGoodLogin(){
        $response = User::login(
            self::$testUserData['email'],
            self::$testUserData['password']
        );

        self::assertContains('token', json_encode($response));
    }

    public function testBadLogin(){
        $response = User::login(
            'fake email',
            'fake password'
        );

        self::assertEquals(User::INVALID, $response);
    }
}