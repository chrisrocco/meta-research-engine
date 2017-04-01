<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

// use the following line when using Composer
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/arangodb-php/autoload.php'; // Because the composer install is broken

// set up some aliases for less typing later
use ArangoDBClient\Collection as ArangoCollection;
use ArangoDBClient\CollectionHandler as ArangoCollectionHandler;
use ArangoDBClient\Connection as ArangoConnection;
use ArangoDBClient\ConnectionOptions as ArangoConnectionOptions;


use ArangoDBClient\DocumentHandler as ArangoDocumentHandler;
use ArangoDBClient\Document as ArangoDocument;
use ArangoDBClient\Exception as ArangoException;
use ArangoDBClient\Export as ArangoExport;
use ArangoDBClient\ConnectException as ArangoConnectException;
use ArangoDBClient\ClientException as ArangoClientException;
use ArangoDBClient\ServerException as ArangoServerException;
use ArangoDBClient\Statement as ArangoStatement;
use ArangoDBClient\UpdatePolicy as ArangoUpdatePolicy;

// set up some basic connection options
$connectionOptions = [
    // database name
    ArangoConnectionOptions::OPTION_DATABASE => 'bigdata2',
    // server endpoint to connect to
    ArangoConnectionOptions::OPTION_ENDPOINT => 'tcp://45.55.64.92:8529',
    // authorization type to use (currently supported: 'Basic')
    ArangoConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
    // user for basic authorization
    ArangoConnectionOptions::OPTION_AUTH_USER => 'root',
    // password for basic authorization
    ArangoConnectionOptions::OPTION_AUTH_PASSWD => 'dickbutt123',
    // connection persistence on server. can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
    ArangoConnectionOptions::OPTION_CONNECTION => 'Keep-Alive',
    // connect timeout in seconds
    ArangoConnectionOptions::OPTION_TIMEOUT => 3,
    // whether or not to reconnect when a keep-alive connection has timed out on server
    ArangoConnectionOptions::OPTION_RECONNECT => true,
    // optionally create new collections when inserting documents
    ArangoConnectionOptions::OPTION_CREATE => true,
    // optionally create new collections when inserting documents
    ArangoConnectionOptions::OPTION_UPDATE_POLICY => ArangoUpdatePolicy::LAST,
];

// turn on exception logging (logs to whatever PHP is configured)
ArangoException::enableLogging();

$connection = new ArangoConnection($connectionOptions);	
$collectionHandler = new ArangoCollectionHandler($connection);

    // clean up first
    if ($collectionHandler->has('users_phptest')) {
        $collectionHandler->drop('users_phptest');
    }

    // create a new collection
    $userCollection = new ArangoCollection();
    $userCollection->setName('users_phptest');
    $id = $collectionHandler->create($userCollection);

    // print the collection id created by the server
    var_dump($id);
    // check if the collection exists
    $result = $collectionHandler->has('users_phptest');
    var_dump($result);
	
$handler = new ArangoDocumentHandler($connection);

	for($i = 0; $i < 100; $i++){
		$user = new ArangoDocument();
		$user->set("email", rand(0,100)."@gmail.com");
		$user->set("age", rand(0,90));
		$user->scopes = ["admin", "manager", "user"];
		$userID = $handler->save('users_phptest', $user);
		$result = $handler->has("users_phptest", $userID);
		var_dump($result);
	}