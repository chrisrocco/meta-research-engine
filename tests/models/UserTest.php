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

class UserTest extends BaseTestCase
{
    static $testUserData = [
        'name'  => 'Bob Jones',
        'email' =>  'chris.rocco7@gmail.com',
        'password'  =>  'password'
    ];


    public function testCreate(){
        $result = User::createOrUpdate(self::$testUserData);

        self::assertTrue( $result !== false );

        return $result;
    }

    public function testFind(){
        $userModel = User::find($this->testCreate());

        self::assertTrue( $userModel->_key() !== false );
    }

}