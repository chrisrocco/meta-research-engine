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

$app->POST ('/reportError', function ($request, $response, $args) {
    $formData = $request->getParams();
    $error = json_encode($formData,JSON_PRETTY_PRINT);
    $email = \Email\Email::errorReportEmail($error);
    $email->send();
});

$app->GET("/500", function ($request, $response, $args) {
    return $response
        ->write ("I am an error. Deal with me.")
        ->withStatus(500);
});