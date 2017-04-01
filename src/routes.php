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
    echo "You are authorized";
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




    $response->write('How about implementing classesIDStudentsGet as a GET method ?');
    return $response;
});


/**
 * POST classesIDStudentsPost
 * Summary: Enrolls a student in a class
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/classes/{ID}/students', function($request, $response, $args) {


    $studentID = $args['studentID'];

    $response->write('How about implementing classesIDStudentsPost as a POST method ?');
    return $response;
});


/**
 * GET studentIDClassesGet
 * Summary:
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/student/{ID}/classes', function($request, $response, $args) {




    $response->write('How about implementing studentIDClassesGet as a GET method ?');
    return $response;
});


/**
 * GET teacherIDClassesGet
 * Summary: Returns a list of a teacher&#39;s classes
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/teacher/{ID}/classes', function($request, $response, $args) {




    $response->write('How about implementing teacherIDClassesGet as a GET method ?');
    return $response;
});

/**
 * POST teacherIDClassesPost
 * Summary: Creates a class under a teaher
 * Notes:

 */
$app->POST('/teacher/{ID}/classes', function($request, $response, $args) {



    $body = $request->getParsedBody();
    $response->write('How about implementing teacherIDClassesPost as a POST method ?');
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
