<?php
require __DIR__ . '/../vendor/autoload.php';

\DB\DB::enterDevelopmentMode();

$settings = require __DIR__ . '/../src/settings.php';

// Instantiate the Slim App
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require( __DIR__ . "/../src/routes/user_routes.php");
require( __DIR__ . "/../src/routes/assignment_routes.php");
require( __DIR__ . "/../src/routes/study_routes.php");
require( __DIR__ . "/../src/routes/test_routes.php");

// Run App
$app->run();