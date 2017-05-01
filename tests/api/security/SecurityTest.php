<?php

namespace Tests\API;
use DB\DB;
use Tests\BaseTestCase;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 5:05 PM
 */

class SecurityTest extends BaseTestCase {

    public function testSecuredRoute(){
        $response = $this->runApp("GET", '/secure');
        self::assertEquals(401, $response->getStatusCode());
    }

    public function testAuthenticate(){
        $cursor = DB::getAll('users');
        if( $cursor->getCount() === 0){
            echo 'We need at least one user to run this test';
            return;
        }
        $doc = $cursor->current();
        $good_email = $doc->get('email');
        $good_password = $doc->get('password');

        $response = $this->runApp('POST', '/users/login', [
            'email'     =>  $good_email,
            'password'  =>  $good_password
        ]);

        $body = (string)$response->getBody();
        $token = json_decode($body, true)['token'];

        $next_response = $this->runApp('GET', '/secure', null, [
            [ 'Authorization', 'Bearer ' . $token ]
        ]);

        self::assertEquals(200, $next_response->getStatusCode());
    }
}