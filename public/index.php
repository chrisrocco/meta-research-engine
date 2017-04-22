<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/arangodb-php/autoload.php'; // Because the composer install is broken
require __DIR__ . '/../models/loader.php';
require __DIR__ . '/../src/app/ConflictManager.php';
require __DIR__ . '/../src/app/queries/Queries.php';

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();