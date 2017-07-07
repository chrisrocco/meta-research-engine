<?php

use uab\MRE\dao\User;

// Application middleware
$container = $app->getContainer();

$app->add( new \Slim\Middleware\JwtAuthentication([
	"path" => ["/"],
    "passthrough" => ["/users/", "/reportError"],
    "attribute" => "jwt",
    "secure" => true,
    "relaxed" => [ "localhost", "dev.researchcoder.com" ],
    "secret" => $app->getContainer()->get("settings")['JWT_secret'],
    "callback" => function ($req, $res, $arguments) use ($container) {
        $jwt = $arguments['decoded'];
        $container['jwt'] = $jwt; //So that other middleware can have access to the token
        $userKey = $jwt->data->_key;
        $user = User::retrieve($userKey);
        if (!$user) {
            $res->write( json_encode([
                'status' => "USER_NOT_FOUND",
                'msg' => "No user with key $userKey found."
            ], JSON_PRETTY_PRINT) );
            return false;
        }
        if (!$user->get('active')) {
            $res->write( json_encode([
                'status' => "USER_NOT_VALIDATED",
                'msg' => "You have not validated your email."
            ], JSON_PRETTY_PRINT) );
            return false;
        }
        $container['user'] = $user;
    }
]) );