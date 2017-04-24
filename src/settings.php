<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name' => 'slim-lib',
            'path' => __DIR__ . '/../logs/lib.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // JWT secret
        'JWT_secret' => 'testing',

        "arangodb_connection_options" => [
            // database name
            ArangoDBClient\ConnectionOptions::OPTION_DATABASE => 'bigdata',
            // server endpoint to connect to
            ArangoDBClient\ConnectionOptions::OPTION_ENDPOINT => 'tcp://35.184.147.35:8529',
            // authorization type to use (currently supported: 'Basic')
            ArangoDBClient\ConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
            // user for basic authorization
            ArangoDBClient\ConnectionOptions::OPTION_AUTH_USER => 'bigdata-client',
            // password for basic authorization
            ArangoDBClient\ConnectionOptions::OPTION_AUTH_PASSWD => 'bigdatauab',
            // connection persistence on server. can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
            ArangoDBClient\ConnectionOptions::OPTION_CONNECTION => 'Keep-Alive',
            // connect timeout in seconds
            ArangoDBClient\ConnectionOptions::OPTION_TIMEOUT => 3,
            // whether or not to reconnect when a keep-alive connection has timed out on server
            ArangoDBClient\ConnectionOptions::OPTION_RECONNECT => true,
            // optionally create new collections when inserting documents
            ArangoDBClient\ConnectionOptions::OPTION_CREATE => true,
            // optionally create new collections when inserting documents
            ArangoDBClient\ConnectionOptions::OPTION_UPDATE_POLICY => ArangoDBClient\UpdatePolicy::LAST,
        ]
    ],
];
