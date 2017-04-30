<?php
// Application middleware

$app->add(new \Slim\Middleware\JwtAuthentication([
	"path" => ["/"],
    "passthrough" => ["/users/", "/studies/"],
    "secret" => $app->getContainer()->get("settings")['JWT_secret'],
]));