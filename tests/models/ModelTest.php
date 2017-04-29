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

class ModelTest extends BaseTestCase
{
    static $testUserData = [
        'first_name'  => 'Test',
        'last_name'  => 'Account',
        'email' =>  'test@gmail.com',
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

    public function testFindByExample(){
        $result_set = User::findByExample([
            'email' =>  self::$testUserData['email']
        ]);

        self::assertTrue( $result_set !== false );
    }
}