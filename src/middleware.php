<?php
// Application middleware

$app->add(new \Slim\Middleware\JwtAuthentication([
	"path" => ["/"],
    "passthrough" => ["/users/"],
    "secret" => $app->getContainer()->get("settings")['JWT_secret'],
]));