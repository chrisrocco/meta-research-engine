<?php
namespace Tests;

use Email\Email;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 5:46 PM
 */

class EmailTest extends \Tests\BaseTestCase {

    static $test_data = [
        'email' =>  'caleb.falcione@gmail.com',
        'name'  =>  'Chris Rocco',
        'id'    =>  '2000',
        'hash_code' =>  '1234567891011121314151617181920'
    ];

    public function testValidationEmail(){

        $email = Email::validationEmail(
            self::$test_data['email'],
            self::$test_data['name'],
            self::$test_data['id'],
            self::$test_data['hash_code']
        );

        $result = $email->send();

        self::assertTrue($result);
    }

}