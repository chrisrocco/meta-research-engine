<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 10:02 PM
 */


$app->GET('/secure', function ($request, $response, $args) {

    return $response
        ->write("You are authenticated.")
        ->withStatus(200);

});