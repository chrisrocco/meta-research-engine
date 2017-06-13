<?php
require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';

require __DIR__ . '/../database/db_connect.php';

// Instantiate the Slim App
$app = new \Slim\App($settings);

/* CORS Support */
header("Access-Control-Allow-Origin: *");
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
require( __DIR__ . "/../src/routes/project_routes.php");
require( __DIR__ . "/../src/routes/core_routes.php");
require( __DIR__ . "/../src/routes/application_routes.php");

// Run App
$app->run();



// Handle Errors

register_shutdown_function( "fatal_handler" );

function fatal_handler() {
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if( $error !== NULL) {

        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        global $settings;

        $mail = new \PHPMailer();
        $mail->isSMTP();
        $mail->Host         =   $settings['smtp']['host'];
        $mail->SMTPAuth     =   $settings['smtp']['smtp_auth'];
        $mail->Username     =   $settings['smtp']['username'];
        $mail->Password     =   $settings['smtp']['password'];
        $mail->SMTPSecure   =   $settings['smtp']['smtp_secure'];
        $mail->Port         =   $settings['smtp']['port'];

        $mail->From         =   "error_reporting@researchcoder.com";
        $mail->FromName     =   "Crash Reporter";

        $mail->addAddress("chris.rocco7@gmail.com", "Chris Rocco");
        $mail->addAddress("caleb.falcione@gmail.com", "Caleb Falcione");
        $mail->isHTML(true);

        $mail->Subject = "Researchcoder.com Crash Report";
        $mail->Body = format_error( $errno, $errstr, $errfile, $errline);

        $mail->send();

        $error_log = fopen( "crash report", "w" );
        fwrite( $error_log, format_error( $errno, $errstr, $errfile, $errline) );
        fclose( $error_log );
    }
}

function format_error( $errno, $errstr, $errfile, $errline ) {
    $trace = print_r( debug_backtrace( false ), true );

    $content = "
  <table>
  <thead><th>Item</th><th>Description</th></thead>
  <tbody>
  <tr>
    <th>Error</th>
    <td><pre>$errstr</pre></td>
  </tr>
  <tr>
    <th>Errno</th>
    <td><pre>$errno</pre></td>
  </tr>
  <tr>
    <th>File</th>
    <td>$errfile</td>
  </tr>
  <tr>
    <th>Line</th>
    <td>$errline</td>
  </tr>
  <tr>
    <th>Trace</th>
    <td><pre>$trace</pre></td>
  </tr>
  </tbody>
  </table>";

    return $content;
}