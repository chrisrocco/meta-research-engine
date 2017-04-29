<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 2:01 PM
 */

namespace Tests\Models;


use Models\User;
use Tests\BaseTestCase;

class RegisterTest extends BaseTestCase
{
    static $testUserData = [
        'first_name'  => 'Chris',
        'last_name'  => 'Rocco',
        'email' =>  'chris.rocco7@gmail.com',
        'password'  =>  'password'
    ];


    public function testNewRegister(){
        $result = User::register(
            self::$testUserData['first_name'],
            self::$testUserData['last_name'],
            rand(0, 9999) . self::$testUserData['email'],
            self::$testUserData['password']
        );

        self::assertInternalType("string", $result);
    }

    public function testExistingRegister(){
        $result = User::register(
            self::$testUserData['first_name'],
            self::$testUserData['last_name'],
            self::$testUserData['email'],
            self::$testUserData['password']
        );

        self::assertEquals(User::EXISTS, $result);
    }

}