<?php
// set up some aliases for less typing later
use ArangoDBClient\Collection as ArangoCollection;
use ArangoDBClient\CollectionHandler as ArangoCollectionHandler;
use ArangoDBClient\Connection as ArangoConnection;
use ArangoDBClient\ConnectionOptions as ArangoConnectionOptions;
use ArangoDBClient\DocumentHandler as ArangoDocumentHandler;
use ArangoDBClient\Document as ArangoDocument;
use ArangoDBClient\Exception as ArangoException;
use ArangoDBClient\Export as ArangoExport;
use ArangoDBClient\ConnectException as ArangoConnectException;
use ArangoDBClient\ClientException as ArangoClientException;
use ArangoDBClient\ServerException as ArangoServerException;
use ArangoDBClient\Statement as ArangoStatement;
use ArangoDBClient\UpdatePolicy as ArangoUpdatePolicy;

// Routes

// This is a control test route to make sure the server is running properly
$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->getBody()->write($args['name']);
});

// This route is authenticated using middleware
$app->get('/secure', function ($request, $response, $args) {
    echo var_dump($this->DB->query("SELECT * FROM users"));
    return;
});

$app->get('/test/arangodb', function ($request, $response, $args) {
    $documentHandler = new ArangoDocumentHandler($this->arangodb_connection);
    $user = new ArangoDocument();
    $user->set("email", rand(0,100)."@gmail.com");
    $user->set("age", rand(0,90));
    $user->scopes = ["admin", "manager", "user"];
    $userID = $documentHandler->save('created_with_php', $user);
    $result = $documentHandler->has("created_with_php", $userID);
    echo var_dump($result);
    $response->getBody()->write($result);
    return;
});

/**
 * DELETE assignmentsIDDelete
 * Summary: Deletes an assignment
 * Notes:

 */
$app->DELETE('/assignments/{ID}', function($request, $response, $args) {




    $response->write('How about implementing assignmentsIDDelete as a DELETE method ?');
    return $response;
});


/**
 * GET assignmentsIDGet
 * Summary: Returns a single assignment
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/assignments/{ID}', function($request, $response, $args) {




    $response->write('How about implementing assignmentsIDGet as a GET method ?');
    return $response;
});


/**
 * PUT assignmentsIDPut
 * Summary: Updates assignment with a students work
 * Notes:

 */
$app->PUT('/assignments/{ID}', function($request, $response, $args) {



    $body = $request->getParsedBody();
    $response->write('How about implementing assignmentsIDPut as a PUT method ?');
    return $response;
});


/**
 * GET studentsIDAssignmentsGet
 * Summary: Returns a list of assignments to a student
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/students/{ID}/assignments', function($request, $response, $args) {




    $response->write('How about implementing studentsIDAssignmentsGet as a GET method ?');
    return $response;
});


/**
 * POST studentsIDAssignmentsPost
 * Summary: Creates an assignment to a student
 * Notes:

 */
$app->POST('/students/{ID}/assignments', function($request, $response, $args) {



    $body = $request->getParsedBody();
    $response->write('How about implementing studentsIDAssignmentsPost as a POST method ?');
    return $response;
});


/**
 * GET classesIDStudentsGet
 * Summary: Returns a list of students in a class
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/classes/{ID}/students', function($request, $response, $args) {
    $classID = $args["ID"];
    $collectionHandler = new ArangoCollectionHandler($this->arangodb_connection);

    //Make sure the class exists
    if (!$collectionHandler.has('classes', $classID)) {
        $res = [
            'status' => "INVALID",
            'msg' => "No class with ID ".$classID." exists"
        ];
    } //The class exists, proceed
    else {
        $statement = new ArangoStatement(
            $this->arangodb_connection, [
                'query' => 'FOR student IN INBOUND CONCAT("classes/", @classID) enrolledIn RETURN student._key',
                'bindVars' => [
                    'studentID' => $classID
                ],
                '_flat' => true
            ]
        );
        $res = $statement->execute()->getAll();
    }

    $response->write(json_encode($res,JSON_PRETTY_PRINT));
    return $response;
});


/**
 * POST classesIDStudentsPost
 * Summary: Enrolls a student in a class
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/classes/{ID}/students', function($request, $response, $args) {
    $classID = $args["ID"];
    $studentID = $request->getParam("studentID");
    $collectionHandler = new ArangoCollectionHandler ($this->arangodb_connection);

    //Make sure the class exists
    if (!$collectionHandler->has('classes', $classID)) {
        $res = [
            'status' => "INVALID",
            'msg' => "No class with ID ".$classID." exists."
        ];

    } //Make sure the student exists
    else if (!$collectionHandler->has('users', $studentID)) {
        $res = [
            'status' => "INVALID",
            'msg' => "No student with ID ".$studentID." exists."
        ];

    } //Make sure the student isn't already enrolled
    else if ($collectionHandler->getByExample('enrolledIn', ['_from' => $studentID, '_to' => $classID])->getCount() > 0) {
        $res = [
            'status' => "DUPLICATE",
            'msg' => "Student ".$studentID." is already enrolled in class ".$classID
        ];

    else {
        $documentHandler = new ArangoDocumentHandler($this->arangodb_connection);
        $edge = new ArangoDocument();
        $edge->set('_from', $studentID);
        $edge->set('_to', $classID);
        $documentHandler->save('enrolledIn', edge);
        $res = [
            'status' => "OK",
            'msg' => "Successfully enrolled student ".$studentID." into class ".$classID
        ];
    }

    $response->write (json_encode($res, JSON_PRETTY_PRINT));
    return $response;
});


/**
 * GET studentIDClassesGet
 * Summary:
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/student/{ID}/classes', function($request, $response, $args) {
    $studentID = $args["ID"];

    $docHandler = new ArangoDocumentHandler($this->arangodb_connection);
    if (!$docHandler->has('users', $studentID)) {
        $res = [
            'status' => "ERROR",
            'msg' => "No student with that ID found"
        ];
    } else { //The student exists
        $statement = new ArangoStatement(
            $this->arangodb_connection, [
                'query' => 'FOR class IN OUTBOUND CONCAT("users/", @studentID) teaches RETURN class',
                'bindVars' => [
                    'studentID' => $studentID
                ],
                '_flat' => true
            ]
        );
        $res = $statement->execute()->getAll();
    }

    $response->write(json_encode ($res, JSON_PRETTY_PRINT));
    return $response;
});


/**
 * GET teacherIDClassesGet
 * Summary: Returns a list of a teacher&#39;s classes
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/teacher/{ID}/classes', function($request, $response, $args) {
    $teacherID = $args["ID"];

    $docHandler = new ArangoDocumentHandler($this->arangodb_connection);
    if (!$docHandler->has('users', $teacherID)) {
        $res = [
            'status' => "ERROR",
            'msg' => "No student with that ID found"
        ];
    } else { //The student exists
        $statement = new ArangoStatement(
            $this->arangodb_connection, [
                'query' => 'FOR class IN OUTBOUND CONCAT("users/", @teacherID) teaches RETURN class',
                'bindVars' => [
                    'teacherID' => $teacherID
                ],
                '_flat' => true
            ]
        );
        $res = $statement->execute()->getAll();
    }

    $response->write(json_encode ($res, JSON_PRETTY_PRINT));
    return $response;
});


/**
 * POST teacherIDClassesPost
 * Summary: Creates a class under a teaher
 * Notes:

 */
$app->POST('/teacher/{ID}/classes', function($request, $response, $args) {
    $teacherID = args["ID"];

    //Make sure teacher exists

    $className = $request->getParsedBody()->name;
    $documentHandler = new ArangoDocumentHandler();

    $class = new ArangoDocument();
    $class.set("name", $className);
    $classID = $documentHandler->save($class, "classes");

    $edge = new ArangoDocument ();
    $edge->set ("_from", "users/".$teacherID);
    $edge->set ("_to", "classes/".$classID);



    return $response;
});


/**
 * POST usersLoginPost
 * Summary: Logs in user
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/users/login', function($request, $response, $args) {


    $email = $args['email'];    $password = $args['password'];

    $response->write('How about implementing usersLoginPost as a POST method ?');
    return $response;
});


/**
 * POST usersRegisterPost
 * Summary: Registers user
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/users/register', function($request, $response, $args) {


    $name = $args['name'];    $email = $args['email'];    $password = $args['password'];

    $response->write('How about implementing usersRegisterPost as a POST method ?');
    return $response;
});
