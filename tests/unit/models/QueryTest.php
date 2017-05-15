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

class QueryTest extends BaseTestCase
{
    function testGetByExample(){
        $models = User::getByExample( [ 'email' => 'test@gmail.com' ]);

        if( count($models) > 0 ){
            self::assertInstanceOf( User::class, $models[0] );
        }
    }
}