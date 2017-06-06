<?php
use Monolog\Logger;
use triagens\ArangoDb\ConnectionOptions;
use triagens\ArangoDb\UpdatePolicy;

$settings = [
    'settings' => [
        // JWT secret
        'JWT_secret' => 'supersecretkeyyoushouldnotcommitotgithub',

        // Database Connection
        "database_connection_options" => [
            ConnectionOptions::OPTION_DATABASE => 'development',
            ConnectionOptions::OPTION_ENDPOINT => 'tcp://localhost:8529',
            ConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
            ConnectionOptions::OPTION_AUTH_USER => 'root',
            ConnectionOptions::OPTION_AUTH_PASSWD => '',
            ConnectionOptions::OPTION_CONNECTION => 'Keep-Alive',
            ConnectionOptions::OPTION_TIMEOUT => 3,
            ConnectionOptions::OPTION_RECONNECT => true,
            ConnectionOptions::OPTION_CREATE => true,
            ConnectionOptions::OPTION_UPDATE_POLICY => UpdatePolicy::LAST,
        ],

        // Slim Project Settings
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        "determineRouteBeforeAppMiddleware" => true,

        // Monolog settings
        'logger' => [
            'name' => 'slim-lib',
            'path' => __DIR__ . '/../logs/lib.log',
            'level' => Logger::DEBUG,
        ],

        // Mail Server
        'smtp'  =>  [
            'host'          =>  'smtp.gmail.com',
            'smtp_auth'     =>  true,
            'username'      =>  '_________@gmail.com',
            'password'      =>  '______________',
            'smtp_secure'   =>  'tls',
            'port'          =>  587
        ]
    ]
];

return $settings;