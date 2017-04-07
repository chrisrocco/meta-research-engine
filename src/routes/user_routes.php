<?php
// set up some aliases for less typing later
use ArangoDBClient\Collection as ArangoCollection;
use ArangoDBClient\CollectionHandler as ArangoCollectionHandler;
use ArangoDBClient\Connection as ArangoConnection;
use ArangoDBClient\ConnectionOptions as ArangoConnectionOptions;
use ArangoDBClient\DocumentHandler as ArangoDocumentHandler;
use ArangoDBClient\EdgeHandler as ArangoEdgeHandler;
use ArangoDBClient\Document as ArangoDocument;
use ArangoDBClient\Edge as ArangoEdge;
use ArangoDBClient\Exception as ArangoException;
use ArangoDBClient\Export as ArangoExport;
use ArangoDBClient\ConnectException as ArangoConnectException;
use ArangoDBClient\ClientException as ArangoClientException;
use ArangoDBClient\ServerException as ArangoServerException;
use ArangoDBClient\Statement as ArangoStatement;
use ArangoDBClient\UpdatePolicy as ArangoUpdatePolicy;

/**
 * POST usersLoginPost
 * Summary: Logs in user
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/users/login', function ($request, $response, $args) {
    $email = $request->getParam("email");
    $password = $request->getParam("password");
    $collectionHandler = new ArangoCollectionHandler($this->arangodb_connection);
    $cursor = $collectionHandler->byExample('users', ['email' => $email, 'password' => $password]);

    // Query the user
    if ($cursor->getCount() == 0) {
        $ResponseToken = [
            "status" => "INVALID",
            "msg" => "No account with that email and password in the database"
        ];
        return $response
            ->write(json_encode($ResponseToken, JSON_PRETTY_PRINT))
            ->withStatus(403);
    }

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
    $token = $this->JWT->encode($data, $this->get("settings")['JWT_secret']);

    $ResponseToken = [
        "token" => $token,
        "user" => $userDetails
    ];
    return $response
        ->write(json_encode($ResponseToken, JSON_PRETTY_PRINT))
        ->withStatus(200);
});

/**
 * POST usersRegisterPost
 * Summary: Registers user
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/users/register', function ($request, $response, $args) {
    $collectionHandler = new ArangoCollectionHandler($this->arangodb_connection);
    $documentHandler = new ArangoDocumentHandler($this->arangodb_connection);
    $formData = $request->getParams();
    // Validate role input
    if (!isset($formData['name']) ||
        !isset($formData['email']) ||
        !isset($formData['password']) ||
        !isset($formData['role']) ||
        !in_array($formData['role'], User::roles)
    ) {
        echo "Missing or Invalid Param(s)";
        return;
    }
    // Make sure account with email does not already exist
    if ($collectionHandler->byExample('users', ['email' => $formData['email']])->getCount() > 0) {
        return $response
            ->write("An account with that email already exists")
            ->withStatus(409);
    }
    // Create a new document
    $user = new ArangoDocument();
    $user->set('name', $formData['name']);
    $user->set('email', $formData['email']);
    $user->set('password', $formData['password']);
    $user->set('date_created', date("Y-m-d"));
    $user->set('role', $formData['role']);
    $id = $documentHandler->save('users', $user);
    // check that the user was created
    $result = $documentHandler->has('users', $id);
    if ($result == true) {
        return $response
            ->write("Account created successfully. ID: " . $id)
            ->withStatus(200);
    } else {
        return $response
            ->write("Could not create account")
            ->withStatus(500);
    }
});