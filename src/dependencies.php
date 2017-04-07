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

// ArangoDB connection
$container["arangodb_connection"] = function($c){
    return new ArangoDBClient\Connection($c ['settings'] ['arangodb_connection_options']);
};
$container["arangodb_documentHandler"] = function($c){
    return new ArangoDBClient\DocumentHandler($c ['arangodb_connection']);
};
$container["arangodb_collectionHandler"] = function($c){
    return new ArangoDBClient\CollectionHandler($c ['arangodb_connection']);
};
$container["arngodb_edgeHandler"] = function($c) {
    return new ArangoDBClient\EdgeHandler ($c ['arangodb_connection']);
};