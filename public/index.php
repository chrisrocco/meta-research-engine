<?php
require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';

/* Connect to Testing DB */
$config = $settings['settings']['database_connection_options'];
$config[\triagens\ArangoDb\ConnectionOptions::OPTION_AUTH_USER] = "integration-testing";
$config[\triagens\ArangoDb\ConnectionOptions::OPTION_AUTH_PASSWD] = "integrationTesting();";
$config[\triagens\ArangoDb\ConnectionOptions::OPTION_DATABASE] = "integration-testing";
$connection = new \triagens\ArangoDb\Connection($config);
\DB\DB::$connection = $connection;
/* End Connect */

// Instantiate the Slim App
$app = new \Slim\App($settings);

/* CORS Support */
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
$app->add(function($request, $response, $next) {
    $route = $request->getAttribute("route");

    $methods = [];

    if (!empty($route)) {
        $pattern = $route->getPattern();

        foreach ($this->router->getRoutes() as $route) {
            if ($pattern === $route->getPattern()) {
                $methods = array_merge_recursive($methods, $route->getMethods());
            }
        }
        //Methods holds all of the HTTP Verbs that a particular route handles.
    } else {
        $methods[] = $request->getMethod();
    }

    $response = $next($request, $response);


    return $response->withHeader("Access-Control-Allow-Methods", 'GET, POST, PUT, DELETE, OPTIONS');
});
/* End CORS Support */

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require( __DIR__ . "/../src/routes/user_routes.php");
require( __DIR__ . "/../src/routes/assignment_routes.php");
require( __DIR__ . "/../src/routes/study_routes.php");
require( __DIR__ . "/../src/routes/test_routes.php");
require( __DIR__ . "/../src/routes/application_routes.php");

// Run App
$app->run();