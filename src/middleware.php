<?php
// Application middleware
$app->add(new \Slim\Middleware\JwtAuthentication([
	"path" => ["/"],
	"secure" => false,   // For development only
    "passthrough" => ["/register", "/login", "/control", "/hello"],
    "secret" => $app->getContainer()->get("settings")['JWT_secret'],
]));