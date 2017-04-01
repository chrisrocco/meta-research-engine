<?php

// DIC configuration
$container = $app->getContainer();

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// MySQL Client
$container ['DB'] = function ($c) {
	$host = $c ['settings'] ['DB'] ['host'];
	$user = $c ['settings'] ['DB'] ['user'];
	$pass = $c ['settings'] ['DB'] ['pass'];
	$dbName = $c ['settings'] ['DB'] ['dbname'];
	$DB = new MeekroDB($host, $user, $pass, $dbName);
	return $DB;
};

// JWT Helper
$container['JWT'] = function ($c){
	return new \Firebase\JWT\JWT;
};