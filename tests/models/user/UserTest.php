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

class UserTest extends BaseTestCase
{

    public function testNewRegister(){
        $random_email = rand(0, 9999) . '@gmail.com';

        $user = User::register(
            'Random',
            'Register',
            $random_email,
            'password'
        );
        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    /**
     * @param $existingUser User
     * @depends testNewRegister
     */
    public function testExistingRegister( $existingUser ){
        $result = User::register(
            $existingUser->get('first_name'),
            $existingUser->get('last_name'),
            $existingUser->get('email'),
            $existingUser->get('password')
        );

        self::assertEquals(User::EXISTS, $result);
    }

    /**
     * @param $existingUser User
     * @depends testNewRegister
     */
    public function testValidate( $fresh_user ){
        self::assertEquals(false, $fresh_user->get('active'));

        $new_hash = $fresh_user->rehash();
        $fresh_user->validate( $new_hash );

        self::assertEquals(true, $fresh_user->get('active'));
    }

    /**
     * @depends testNewRegister
     * @param $good_user User
     */
    public function testGoodLogin( $good_user ){
        $response = User::login(
            $good_user->get('email'),
            $good_user->get('password')
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