<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 2:01 PM
 */

namespace Tests\Models;


use Models\Vertices\User;
use Tests\BaseTestCase;
use triagens\ArangoDb\Exception;

class CrudTest extends BaseTestCase {

    /**
     * @return User
     */
    public function testCreate(){
        $userModel = User::create( [
            'first_name'  => 'Test',
            'last_name'  => 'Account',
            'email' =>  'test@gmail.com',
            'password'  =>  'password'
        ] );

        self::assertTrue( $userModel instanceof User);

        return $userModel;
    }

    /**
     * @param $existingUserModel User
     * @depends testCreate
     * @return User
     */
    public function testRetrieve( $existingUserModel ){
        $userModel = User::retrieve(  $existingUserModel->key() );

        self::assertInstanceOf( User::class, $userModel );
        self::assertNotNull( $userModel->getDocument() );

        return $userModel;
    }

    /**
     * @param $existingUserModel User
     * @depends testRetrieve
     */
    public function testUpdate( $existingUserModel ){
        $new_email = 'changed@gmail.com';


        $existingUserModel->update('email', $new_email );

        $from_db = User::retrieve( $existingUserModel->key() );

        self::assertEquals($new_email, $from_db->get('email') );
    }

    /**
     * @param $existingUserModel User
     * @depends testRetrieve
     */
    public function testDelete( $existingUserModel ){
        $existingUserModel->delete();

        $rs = User::getByExample([
            "_key" => $existingUserModel->key()
        ]);

        self::assertEquals( 0, count($rs) );
    }

}