<?php
// Application middleware

$app->add(new \Slim\Middleware\JwtAuthentication([
	"path" => ["/"],
    "passthrough" => ["/users/", "/reportError", "/500"],

    "secure" => false,
    "secret" => $app->getContainer()->get("settings")['JWT_secret'],
]));