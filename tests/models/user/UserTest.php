<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 2:01 PM
 */

namespace Tests\Models\User;


use Models\Vertices\User;
use Tests\BaseTestCase;

class UserTest extends BaseTestCase {

    public function testNewRegister(){
        $random_email = rand(0, 9999) . '@gmail.com';
        $password = 'password';

        $user = User::register(
            'Random',
            'Register',
            $random_email,
            'password'
        );
        self::assertInstanceOf(User::class, $user);

        return [
            'email'     =>  $random_email,
            'password'  =>  $password
        ];
    }

    /**
     * @param $existingUser User
     * @depends testNewRegister
     */
    public function testExistingRegister( $fresh_creds ){
        $result = User::register(
            "name",
            "name",
            $fresh_creds['email'],
            "password"
        );

        self::assertEquals(User::EXISTS, $result);
    }

    /**
     * @param $existingUser User
     * @depends testNewRegister
     */
    public function testValidate( $fresh_creds ){
        $fresh_user = User::getByExample([
            'email' =>  $fresh_creds['email']
        ])[0];

        self::assertEquals(false, $fresh_user->get('active'));

        $new_hash = $fresh_user->rehash();
        $fresh_user->validate( $new_hash );

        self::assertEquals(true, $fresh_user->get('active'));
    }

    /**
     * @depends testNewRegister
     * @param $good_user User
     */
    public function testGoodLogin( $fresh_creds ){
        $response = User::login(
            $fresh_creds['email'],
            $fresh_creds['password']
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