<?php

namespace Tests\Users;

use Entities\User;
use Tests\BaseTestCase;

class LoginTest extends BaseTestCase {

    static $testUser = [
        'email'     =>  'chris.rocco7@gmail.com',
        'password'  =>  'password'
    ];

    public function testLogin(){

        $result = User::login(
            self::$testUser['email'],
            self::$testUser['password']
        );

        self::assertFalse($result == User::INVALID);
        self::assertFalse( !$result );
    }

}
