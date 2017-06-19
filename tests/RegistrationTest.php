<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 6/19/2017
 * Time: 1:33 AM
 */

namespace Tests;


use Models\Vertices\User;

class RegistrationTest extends BaseTestCase
{
    private $user_data = [
        'first_name'    =>  'Jon',
        'last_name'     =>  'Doe',
        'email'         =>  'test@gmail.com',
        'password'      =>  'password'
    ];

    function testRegister(){
        $this->runApp("GET", "/users/register", [
            'first_name'    =>  $this->user_data['first_name'],
            'last_name'     =>  $this->user_data['last_name'],
            'email'         =>  $this->user_data['email'],
            'password'      =>  $this->user_data['password'],
        ]);

        $user = User::getByExample( [ 'email' => $this->user_data['email']]);
        self::assertEquals( 1, count($user) );
    }

    function testDuplicateRegister(){
        self::assertTrue( false );
    }

    function testLogin(){
        self::assertTrue( false );
    }

    function testBadLogin(){
        self::assertTrue( false );
    }

    function testInactiveLogin(){
        self::assertTrue( false );
    }

}