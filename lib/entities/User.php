<?php
namespace Entities;
use QueryBank;

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/22/2017
 * Time: 5:13 PM
 */
class User {

    const SUCCESS = 0;
    const ALREADY_EXISTS = 1;
    const ERROR = 2;
    const INVALID = 3;

    private $ID;

    function __construct($ID) {
        global $documentHandler;

        if (!$documentHandler->has('users', $ID)) {
            throw new Exception("No student with that ID found");
        }

        $this->ID = $ID;
    }

    public function getAssignments(){
        return QueryBank::execute("getAssignmentsByStudent", [ "userID" => $this->ID ]);
    }

    public function createAssignment($pmcID){
        global $documentHandler;

        // make sure paper exists
        if (!$documentHandler->has("papers", $pmcID)) {
            throw new Exception("No paper with that ID");
        }

        // make sure assignment doesnt exist
        $existCount = QueryBank::execute("assignmentExistCount", [
            "userID" => $this->ID,
            "pmcID" => $pmcID
        ]);
        if(count($existCount) > 0){
            throw new Exception("Duplicate Assignment");
        }

        // generate blank encoding
        $blankEncoding = QueryBank::execute("getBlankEncoding", ['studyName' => "research_studies/BigDataUAB"]);

        // Create the assignment
        $assignmentObject = ArangoDBClient\Document::createFromArray([
            "done" => false,
            "completion" => 0,
            "encoding" => $blankEncoding[0]
        ]);
        $assignmentID = $documentHandler->save("assignments", $assignmentObject);

        // Create the assignment_of edge
        $assignment_of = ArangoDBClient\Document::createFromArray([
            "_to" => "papers/" . $pmcID,
            "_from" => $assignmentID
        ]);
        $documentHandler->save("assignment_of", $assignment_of);

        // Create the assigned_to edge
        $assigned_to = ArangoDBClient\Document::createFromArray([
            "_to" => "users/" . $this->ID,
            "_from" => $assignmentID
        ]);
        $documentHandler->save("assigned_to", $assigned_to);
    }

    public function sendValidation(){

    }

    public function validateAccount(){

    }


    public static function register($name, $email, $password, $role){

        global $collectionHandler;
        global $documentHandler;

        // Make sure account with email does not already exist
        if ($collectionHandler->byExample('users', [ 'email' => $email ])->getCount() > 0) {
            return User::ALREADY_EXISTS;
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
            return User::SUCCESS;
        } else {
            return User::ERROR;
        }
    }

    public static function login($email, $password){

        global $collectionHandler;
        $cursor = $collectionHandler->byExample('users', ['email' => $email, 'password' => $password]);

        if ($cursor->getCount() == 0) return User::INVALID;

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