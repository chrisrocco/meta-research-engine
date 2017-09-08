<?php
require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../app/settings.php';
require __DIR__ . '/../database/db_connect.php';

//FIXME: temporary CORS workaround
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS, POST, PUT, DELETE');
header( "Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Headers, Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers" );

// Instantiate the Slim App
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../app/dependencies.php';

// Register middleware
require __DIR__ . '/../app/middleware.php';

// Register routes
require(__DIR__ . "/../app/routes/user_routes.php");
require(__DIR__ . "/../app/routes/assignment_routes.php");
require(__DIR__ . "/../app/routes/project_routes.php");
require(__DIR__ . "/../app/routes/core_routes.php");
require(__DIR__ . "/../app/routes/application_routes.php");


try {
    // Run App
    $app->run();
} catch ( Exception $e ) {
    $message = [
        "file"  =>  $e->getFile(),
        "msg"   =>  $e->getMessage(),
        "line"  =>  $e->getLine(),
        "code"  =>  $e->getCode(),
        "trace" =>  $e->getTraceAsString()
    ];

    \Email\Email::errorReportEmail( json_encode( $message ) );

    echo json_encode( $message, JSON_PRETTY_PRINT );
}
