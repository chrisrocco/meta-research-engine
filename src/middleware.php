<?php
// Application middleware

$app->add( new \Slim\Middleware\JwtAuthentication([
	"path" => ["/"],
    "passthrough" => ["/users/", "/reportError"],
    "attribute" => "jwt",
    "secure" => true,
    "relaxed" => ["localhost", "dev.researchcoder.com"],
    "secret" => $app->getContainer()->get("settings")['JWT_secret'],
]) );