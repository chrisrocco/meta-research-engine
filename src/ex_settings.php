<?php
$settings = [
    'displayErrorDetails' => true, // set to false in production
    'addContentLengthHeader' => false, // Allow the web server to send the content-length header

    // Monolog settings
    'logger' => [
        'name' => 'slim-lib',
        'path' => __DIR__ . '/../logs/lib.log',
        'level' => \Monolog\Logger::DEBUG,
    ],

    // JWT secret
    'JWT_secret' => 'supersecretkeyyoushouldnotcommittogithub',

    "arangodb_connection_options" => [
        // database name
        \triagens\ArangoDb\ConnectionOptions::OPTION_DATABASE => 'database_name',
        // server endpoint to connect to
        \triagens\ArangoDb\ConnectionOptions::OPTION_ENDPOINT => 'tcp://____________:8529',
        // authorization type to use (currently supported: 'Basic')
        \triagens\ArangoDb\ConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
        // user for basic authorization
        \triagens\ArangoDb\ConnectionOptions::OPTION_AUTH_USER => 'database_username',
        // password for basic authorization
        \triagens\ArangoDb\ConnectionOptions::OPTION_AUTH_PASSWD => 'database_password',
        // connection persistence on server. can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
        \triagens\ArangoDb\ConnectionOptions::OPTION_CONNECTION => 'Keep-Alive',
        // connect timeout in seconds
        \triagens\ArangoDb\ConnectionOptions::OPTION_TIMEOUT => 3,
        // whether or not to reconnect when a keep-alive connection has timed out on server
        \triagens\ArangoDb\ConnectionOptions::OPTION_RECONNECT => true,
        // optionally create new collections when inserting documents
        \triagens\ArangoDb\ConnectionOptions::OPTION_CREATE => true,
        // optionally create new collections when inserting documents
        \triagens\ArangoDb\ConnectionOptions::OPTION_UPDATE_POLICY => \triagens\ArangoDb\UpdatePolicy::LAST,
    ],

    "arangodb_development_connection_options"   =>  [
        \triagens\ArangoDb\ConnectionOptions::OPTION_DATABASE => 'database_name',
        // server endpoint to connect to
        \triagens\ArangoDb\ConnectionOptions::OPTION_ENDPOINT => 'tcp://____________:8529',
        // authorization type to use (currently supported: 'Basic')
        \triagens\ArangoDb\ConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
        // user for basic authorization
        \triagens\ArangoDb\ConnectionOptions::OPTION_AUTH_USER => 'database_username',
        // password for basic authorization
        \triagens\ArangoDb\ConnectionOptions::OPTION_AUTH_PASSWD => 'database_password',
        // connection persistence on server. can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
        \triagens\ArangoDb\ConnectionOptions::OPTION_CONNECTION => 'Keep-Alive',
        // connect timeout in seconds
        \triagens\ArangoDb\ConnectionOptions::OPTION_TIMEOUT => 3,
        // whether or not to reconnect when a keep-alive connection has timed out on server
        \triagens\ArangoDb\ConnectionOptions::OPTION_RECONNECT => true,
        // optionally create new collections when inserting documents
        \triagens\ArangoDb\ConnectionOptions::OPTION_CREATE => true,
        // optionally create new collections when inserting documents
        \triagens\ArangoDb\ConnectionOptions::OPTION_UPDATE_POLICY => \triagens\ArangoDb\UpdatePolicy::LAST,
    ]
];

return $settings;
