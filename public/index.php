<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/arangodb-php/autoload.php'; // Because the composer install is broken

require __DIR__ . '/../src/app/ConflictManager.php';
require __DIR__ . '/../src/app/queries/QueryBank.php';
require __DIR__ . '/../src/app/handlers/AssignmentHandler.php';
require __DIR__ . '/../src/app/handlers/PaperHandler.php';
require __DIR__ . '/../src/app/handlers/StudyHandler.php';
require __DIR__ . '/../src/app/handlers/UserHandler.php';
require __DIR__ . '/../src/app/Merge.php';



$settings = require __DIR__ . '/../src/settings.php';

// Open a DB connection
$connection = new ArangoDBClient\Connection($settings['settings']['arangodb_connection_options']);
$documentHandler = new ArangoDBClient\DocumentHandler($connection);
$collectionHandler = new ArangoDBClient\CollectionHandler($connection);
$edgeHandler = new ArangoDBClient\EdgeHandler($connection);

// Instantiate the app
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require( __DIR__ . "/../src/routes/user_routes.php");
require( __DIR__ . "/../src/routes/assignment_routes.php");
require( __DIR__ . "/../src/routes/class_routes.php");
require( __DIR__ . "/../src/routes/study_routes.php");

// Run app
$app->run();