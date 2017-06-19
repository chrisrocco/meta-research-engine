<?php
use triagens\ArangoDb\ConnectionOptions;

$settings = [
    'settings' => [
        // JWT secret
        'JWT_secret' => 'pohuafophafgkjlafgjkhlfgajhklkjhsga',

        //Google API key
        'google_api_token' => "",

        // Database Connection
        "database_connection_options" => [
            ConnectionOptions::OPTION_DATABASE => 'meta-research-engine',
            ConnectionOptions::OPTION_ENDPOINT => 'tcp://localhost:8529',
            ConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
            ConnectionOptions::OPTION_AUTH_USER => 'root',
            ConnectionOptions::OPTION_AUTH_PASSWD => '',
            ConnectionOptions::OPTION_CONNECTION => 'Keep-Alive',
            ConnectionOptions::OPTION_TIMEOUT => 3,
            ConnectionOptions::OPTION_RECONNECT => true,
            ConnectionOptions::OPTION_CREATE => true,
            ConnectionOptions::OPTION_UPDATE_POLICY => \triagens\ArangoDb\UpdatePolicy::LAST,
        ],

        // Slim Project Settings
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name' => 'slim-lib',
            'path' => __DIR__ . '/../logs/lib.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Mail Server
        'smtp'  =>  [
            'host'          =>  'smtp.gmail.com',
            'smtp_auth'     =>  true,
            'username'      =>  '',
            'password'      =>  '',
            'smtp_secure'   =>  'tls',
            'port'          =>  587
        ]
    ]
];

return $settings;