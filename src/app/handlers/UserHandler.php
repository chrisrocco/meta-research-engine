<?php

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/22/2017
 * Time: 5:13 PM
 */
class UserHandler {

    const SUCCESS = 0;
    const ALREADY_EXISTS = 1;
    const ERROR = 2;
    const INVALID = 3;


    public function sendValidation(){

    }

    public function validateAccount(){

    }



    public static function register($name, $email, $password, $role){

        global $collectionHandler;
        global $documentHandler;

        // Make sure account with email does not already exist
        if ($collectionHandler->byExample('users', [ 'email' => $email ])->getCount() > 0) {
            return UserHandler::ALREADY_EXISTS;
        }

        // Create a new document
        $user = new ArangoDBClient\Document;
        $user->set('name', $name);
        $user->set('email', $email);
        $user->set('password', $password);
        $user->set('date_created', date("Y-m-d"));
        $user->set('role', $role);
        $id = $documentHandler->save('users', $user);

        // check that the user was created
        $result = $documentHandler->has('users', $id);
        if ($result == true) {
            return UserHandler::SUCCESS;
        } else {
            return UserHandler::ERROR;
        }
    }

    public static function login($email, $password){

        global $collectionHandler;
        $cursor = $collectionHandler->byExample('users', ['email' => $email, 'password' => $password]);

        if ($cursor->getCount() == 0) return UserHandler::INVALID;

        $user = $cursor->current()->getAll();
        $userDetails = [
            "ID" => $user["_key"],
            "name" => $user['name'],
            "email" => $user['email'],
            "role" => $user['role']
        ];

        // Building the JWT
        $tokenId = base64_encode(random_bytes(64));
        $issuedAt = time();
        $expire = $issuedAt + 60 * 30;            // Adding 60 seconds
        $data = [
            'iat' => $issuedAt,         // Issued at: time when the token was generated
            'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss' => "Big Data",       // Issuer
            'exp' => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'userId' => $user["_key"], // userid from the users table
                'userEmail' => $user["name"], // User name
            ]
        ];

        global $settings;
        $token = \Firebase\JWT\JWT::encode($data, $settings['settings']['JWT_secret']);

        return [
            "token" => $token,
            "user" => $userDetails
        ];
    }
}