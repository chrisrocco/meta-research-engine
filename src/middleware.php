<?php
// Application middleware

$app->add(new \Slim\Middleware\JwtAuthentication([
    "path" => ["/"],
	"secure" => false,   // For development only
    "passthrough" => ["/register", "/login", "/control", "/hello"],
    "secret" => getenv("JWT_SECRET"),
]));