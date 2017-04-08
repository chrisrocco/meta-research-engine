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
 * GET classesIDStudentsGet
 * Summary: Returns a list of students in a class
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/classes/{ID}/students', function ($request, $response, $args) {
    $classID = $args["ID"];

    //Make sure the class exists
    if (!$this->arangodb_documentHandler->has('classes', $classID)) {
        return $response->write("No class with ID " . $classID . " exists.")
            ->withStatus(400);
    }

    //The class exists, proceed
    $statement = new ArangoStatement(
        $this->arangodb_connection, [
            'query' => 'FOR student IN INBOUND CONCAT("classes/", @classID) enrolled_in RETURN UNSET (student, "password")',
            'bindVars' => [
                'classID' => $classID
            ],
            '_flat' => true
        ]
    );
    $resultSet = $statement->execute()->getAll();

    return $response->write(json_encode($resultSet, JSON_PRETTY_PRINT));
});

/**
 * POST classesIDStudentsPost
 * Summary: Enrolls a student in a class
 * Notes:
 * Output-Formats: [application/json]
 */
$app->POST('/classes/{ID}/students', function ($request, $response, $args) {
    $classID = $args["ID"];
    $userID = $request->getParam("studentID");

    //Make sure the class exists
    if (!$this->arangodb_documentHandler->has('classes', $classID)) {
        return $response->write("No class with ID " . $classID . " exists.")
            ->withStatus(400);
    }

    //Make sure the student exists
    if (!$this->arangodb_documentHandler->has('users', $userID)) {
        return $response->write("No student with ID " . $userID . " exists.")
            ->withStatus(400);
    }

    //Make sure the student isn't already enrolled
    if ($this->arangodb_collectionHandler->byExample('enrolled_in', ['_from' => "users/" . $userID, '_to' => "classes/" . $classID])->getCount() > 0) {
        return $response->write("Student " . $userID . " is already enrolled in class " . $classID)
            ->withStatus(409);
    }

    // Create the enrollment
    $edge = new ArangoDocument();
    $edge->set('_from', "users/" . $userID);
    $edge->set('_to', "classes/" . $classID);
    $enrollmentID = $this->arangodb_documentHandler->save('enrolled_in', $edge);
    if ($enrollmentID) {
        return $response->write("Successfully enrolled student " . $userID . " into class " . $classID)
            ->withStatus(200);
    } else {
        return $response->write("Something went wrong.")
            ->withStatus(500);
    }
});



/**
 * GET studentIDClassesGet
 * Summary:
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/students/{ID}/classes', function ($request, $response, $args) {
    $userID = $args["ID"];
    /* Make sure student exists */
    if (!$this->arangodb_documentHandler->has('users', $userID)) {
        return $response
            ->write("No student with that ID found")
            ->withStatus(400);
    }
    /* Query the DB */
    $statement = new ArangoStatement(
        $this->arangodb_connection, [
            'query' => 'FOR class IN OUTBOUND CONCAT("users/", @studentID) enrolled_in
                            LET teachers = (
                                FOR teacher IN INBOUND class._id teaches
                                    RETURN teacher.name
                            )
                            RETURN MERGE (class, {teachers : teachers})',
            'bindVars' => [
                'studentID' => $userID
            ],
            '_flat' => true
        ]
    );
    $result_set = $statement->execute()->getAll();
    return $response
        ->write(json_encode($result_set, JSON_PRETTY_PRINT));
});

/**
 * GET teacherIDClassesGet
 * Summary: Returns a list of a teacher&#39;s classes
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/teachers/{ID}/classes', function ($request, $response, $args) {
    $teacherID = $args["ID"];

    // Make sure the user exists
    if (!$this->arangodb_documentHandler->has('users', $teacherID)) {
        return $response
            ->write("No user with that ID found")
            ->withStatus(400);
    }

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

    return $response->write(json_encode($res, JSON_PRETTY_PRINT));
});

/**
 * POST teacherIDClassesPost
 * Summary: Creates a class under a teacher
 * Notes:
 */
$app->POST('/teachers/{ID}/classes', function ($request, $response, $args) {
    $teacherID = $args["ID"];
    // make sure teacher exists
    if (!$this->arangodb_documentHandler->has("users", $teacherID)) {
        echo "Account does not exist";
        return;
    }
    // make sure they are a teacher
    $teacher = $this->arangodb_documentHandler->get("users", $teacherID)->getAll();
    if ($teacher['role'] !== "teacher") {
        echo "You're not a teacher! Fuck off, " . $teacher['name'];
        return;
    }
    // make sure class name is submitted
    if ($request->getParam("name") === null) {
        echo "Class name can't be null";
        return;
    }

    // Create the class
    $className = $request->getParam("name");
    $class = new ArangoDocument();
    $class->set("name", $className);
    $classID = $this->arangodb_documentHandler->save("classes", $class);

    // Link it to the teacher
    $teachesEdge = new ArangoDocument();
    $teachesEdge->set("_to", $classID);
    $teachesEdge->set("_from", "users/" . $teacherID);
    $result = $this->arangodb_documentHandler->save("teaches", $teachesEdge);

    // Build a response object
    if ($result) {
        $res = [
            "status" => "OK",
            "teacher" => $this->arangodb_documentHandler->get("users", $teacherID)->getAll(),
            "class" => $class->getAll()
        ];
        return $response->write(json_encode($res, JSON_PRETTY_PRINT));
    } else {
        echo "Something went wrong";
        return;
    }
});
