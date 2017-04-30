<?php

namespace Tests\API;
use Tests\BaseTestCase;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 5:05 PM
 */

class SecurityTest extends BaseTestCase {

    // try to access secure route
    public function testSecuredRoute(){
        $response = $this->runApp("GET", '/secure');
        self::assertEquals(401, $response->getStatusCode());
    }

    // get an auth token

    // try again
}