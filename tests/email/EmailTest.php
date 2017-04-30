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

    public function testValidationEmail(){

        $email = Email::validationEmail(
            'chris.rocco7@gmail.com',
            'Chris Rocco',
            '2000',
            '1234567891011121314151617181920'
        );

        $result = $email->send();

        self::assertTrue($result);
    }

}