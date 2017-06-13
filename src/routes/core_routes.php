<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 10:02 PM
 */


use Firebase\JWT\JWT;

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

$app->POST ('/renewToken', function ( $req, $res ){
    $decoded = $req->getAttribute("jwt");
    var_dump( $decoded );
    return;

    // TODO: move this login into another class. It's being duplicated right now.

    // Building the JWT
    $tokenId = base64_encode(random_bytes(64));
    $issuedAt = time();
    $expire = $issuedAt + 60 * 30;            // Adding 60 seconds
    $data = [
        'iat' => $issuedAt,         // Issued at: time when the token was generated
        'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss' => "dev",       // Issuer
        'exp' => $expire,           // Expire
        'data' => $decoded
    ];

    $settings = require __DIR__ . '/../settings.php';
    $token = JWT::encode($data, $settings['settings']['JWT_secret']);

    return $res->write( json_encode( [ 'token' => $token ], JSON_PRETTY_PRINT ));
});