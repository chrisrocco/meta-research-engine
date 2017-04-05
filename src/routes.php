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

// Routes

/**
 * TEST
 * Summary: Control test for cloning to a server
 */
$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->getBody()->write($args['name']);
});

/**
 * SECURE
 * Summary: This route is secured by PSR-7 middleware
 */
$app->get('/secure', function ($request, $response, $args) {
    echo "You are authorized";
    return;
});

/* GET studies/{studyname}/structure
 * Summary: Gets the domain / field structure of the specified research study
 *
 */
$app->GET("/studies/{studyname}/structure", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    //Check if the research study exists
    if (!$this->arangodb_documentHandler->has('ResearchStudy', $studyName)) {
        return $response->write("No research study with name " . $studyName . " found.")
            ->withStatus(400);
    }

    //The study exists, return the structure
    $statement = new ArangoStatement($this->arangodb_connection, [
        'query' => "FOR domain IN INBOUND CONCAT (\"ResearchStudy/\", @studyName) subDomainOf //For each top-level domain
   
                   //assemble the domain's fields
                    LET fields = (
                        FOR field IN INBOUND domain fieldOf
                        RETURN field
                    )
                    
                    //assemble the domain's subdomains
                    LET subDomains = (
                        FOR subDomain IN INBOUND domain subDomainOf
                            //assemble the subDomain's fields
                            LET subDomainFields = (
                                FOR subDomainField IN INBOUND subDomain fieldOf
                                RETURN subDomainField
                            )
                            
                            //Returns what will be a child node in the HTML DOM tree
                            RETURN MERGE (subDomain, {
                                \"fields\": subDomainFields,
                                \"subdomains\": []
                                }
                            )
                    )
                    
                    //Sort alphabetically
                    SORT domain.name
                    
                    //Returns what will be a node in the HTML DOM tree with ONE level of its children
                    RETURN MERGE(domain, {
                        \"fields\": fields,
                        \"subdomains\": subDomains
                    })",
        'bindVars' => [
            'studyName' => $studyName
        ],
        '_flat' => true
    ]);

    $res = $statement->execute()->getAll();


    return $response->write(json_encode($res, JSON_PRETTY_PRINT));

});

/**
 * GET studies/{studyname}/variables
 * Summary: Gets a list of every field's name
 */
$app->GET ("/studies/{studyname}/variables", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    //Check if the research study exists
    if (!$this->arangodb_documentHandler->has('ResearchStudy', $studyName)) {
        return $response->write("No research study with name " . $studyName . " found.")
            ->withStatus(400);
    }

    //The study exists, run the query
    $statement = new ArangoStatement($this->arangodb_connection, [
        'query' => "FOR field IN exFields
                        SORT field.name
                        RETURN field.name",
        '_flat' => true
    ]);

    $resultSet = $statement->execute()->getAll();

    return $response->write(json_encode($resultSet,JSON_PRETTY_PRINT));


});

/** POST studies/{studyname}/papers
 *  Add a new paper to the database
 */
$app->POST ("/studies/{studyname}/papers", function ($request, $response, $args) {
    $studyName = $args['studyname'];
    $formData = $request->getParams();

    //Check to make sure that the research study exists
    if (!$this->arangodb_documentHandler->has("ResearchStudy", $studyName)) {
        return $response->write("No research study with name ".$studyName." found")
            ->withStatus(400);
    }

    //check if we have all form data
    if (!isset($formData['pmcID']) || !isset($formData['title'])) {
        return $response->write("Please include 'pmcID' and 'title' parameters in the post request")
            ->withStatus(400);
    }

    if ($this->arangodb_documentHandler->has("papers", $formData['pmcID'])) {
        return $response->write("A paper with pmcID ".$formData['pmcID']." already exists")
            ->withStatus(409);
    }

    //Create the paper document
    $paper = new ArangoDocument();
    $paper->set("_key", $formData['pmcID']);
    $paper->set("title", $formData['title']);
    $paperID = $this->arangodb_documentHandler->save("papers", $paper);

    if (!$paperID) {
        return $response->write("Something went wrong when saving the paper")
            ->withStatus(500);
    }

    //Create the edge from the new paper to the research study
    $edge = new ArangoDocument();
    $edge->set ("_from", "papers/".$formData['pmcID']);
    $edge->set ("_to", "ResearchStudy/".$studyName);
    $edgeID = $this->arangodb_documentHandler->save("paperOf", $edge);

    if (!$edgeID) {
        return $response->write("Something went wrong when assigning the paper to the research study");
    }

    return $response->write("Successfully added paper ".$formData['pmcID']." to research study ".$studyName);
} );

/**
 * GET assignmentsIDGet
 * Summary: Returns a single assignment
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/assignments/{ID}', function ($request, $response, $args) {
    if (!isset($args['ID']))    {
        return $response->write("Please specify an assignment ID in the URL");
    }
    $ID = $args['ID'];
    if (!$this->arangodb_documentHandler->has("assignedTo", $ID)) {
        echo "No assignment found";
        return;
    }
    $assignment = $this->arangodb_documentHandler->get("assignedTo", $ID)->getAll();
    return $response->write(json_encode($assignment, JSON_PRETTY_PRINT));
});

/**
 * PUT assignmentsIDPut
 * Summary: Updates assignment with a students work
 * Notes:
 */
$app->PUT('/assignments/{ID}', function ($request, $response, $args) {
    $formData = $request->getParams();
    /* Validate request */
    if(
        !isset($formData['encoding'])   ||
        !isset($formData['done'])       ||
        !isset($formData['completion'])
    ){
        return $response
            ->write("Bad Request")
            ->withStatus(400);
    }
    // TODO - validate encoding integrity before insert
    /* Make sure assignment exists */
    if (!$this->arangodb_documentHandler->has("assignedTo", $args["ID"])) {
        echo "That assignment does not exist";
        return;
    }
    /* Update Document */
    $assignment = $this->arangodb_documentHandler->get("assignedTo", $args["ID"]);
    $assignment->set("done", $formData['done']);
    $assignment->set("completion", $formData['completion']);
    $assignment->set("encoding", $formData['encoding']);
    $result = $this->arangodb_documentHandler->update($assignment);

    if ($result) {
        return $response
            ->withStatus(200);
    } else {
        return $response
            ->write("Could not update assignment")
            ->withStatus(500);
    }
});

/**
 * GET studentsIDAssignmentsGet
 * Summary: Returns a list of assignments to a student
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/users/{ID}/assignments', function ($request, $response, $args) {
    $studentID = $args["ID"];
    // make sure student exists
    if (!$this->arangodb_documentHandler->has('users', $studentID)) {
        $res = [
            'status' => "ERROR",
            'msg' => "No student with that ID found"
        ];
        return $response->write(json_encode($res, JSON_PRETTY_PRINT));
    }
    $statement = new ArangoStatement(
        $this->arangodb_connection, [
            'query' => 'FOR paper, assignment IN INBOUND CONCAT("users/", @studentID) assignedTo 
                        RETURN MERGE(assignment, {title: paper.title, pmcID: paper._key})',
            'bindVars' => [
                'studentID' => $studentID
            ],
            '_flat' => true
        ]
    );
    $resultSet = $statement->execute()->getAll();
    $response->write(json_encode($resultSet, JSON_PRETTY_PRINT));
    return $response;
});

/**
 * POST studentsIDAssignmentsPost
 * Summary: Creates an assignment to a student
 * Notes:
 */
$app->POST('/users/{ID}/assignments', function ($request, $response, $args) {
    $studentID = $args['ID'];
    $paperID = $request->getParam("pmcID");

    // Make sure student exists
    if (!$this->arangodb_documentHandler->has("users", $studentID)) {
        echo "No user with that ID";
        return;
    }
    // Make sure paper exists
    if (!$this->arangodb_documentHandler->has("papers", $paperID)) {
        echo "No paper with that ID";
        return;
    }

    // Create the assignment object
    $assignmentEdge = new ArangoDocument();
    $assignmentEdge->set("_to", "users/" . $studentID);
    $assignmentEdge->set("_from", "papers/" . $paperID);
    $assignmentEdge->set("done", false);
    $assignmentEdge->set("completion", 0);
    $assignmentEdge->set("encoding", null);
    $newAssignmentID = $this->arangodb_documentHandler->save("assignedTo", $assignmentEdge);

    // get the new assignment and return it
    if ($newAssignmentID) {
        $res = [
            "status" => "OK",
            "assignment" => $this->arangodb_documentHandler->get("assignedTo", $newAssignmentID)->getAll()
        ];
    } else {
        return $response
            ->write("Something went wrong :(")
            ->withStatus(500);
    }
    return $response->write(json_encode($res, JSON_PRETTY_PRINT));
});

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
        return $response->write("No class with ID ".$classID." exists.")
            ->withStatus(400);
    }

    //The class exists, proceed
    $statement = new ArangoStatement(
        $this->arangodb_connection, [
            'query' => 'FOR student IN INBOUND CONCAT("classes/", @classID) enrolledIn RETURN student._key',
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
    $studentID = $request->getParam("studentID");

    //Make sure the class exists
    if (!$this->arangodb_documentHandler->has('classes', $classID)) {
        return $response->write("No class with ID " . $classID . " exists.")
            ->withStatus(400);
    }

    //Make sure the student exists
    if (!$this->arangodb_documentHandler->has('users', $studentID)) {
        return $response->write("No student with ID " . $studentID . " exists.")
            ->withStatus(400);
    }

    //Make sure the student isn't already enrolled
    if ($this->arangodb_collectionHandler->byExample('enrolledIn', ['_from' => "users/".$studentID, '_to' => "classes/".$classID])->getCount() > 0) {
        return $response->write("Student " . $studentID . " is already enrolled in class " . $classID)
            ->withStatus(409);
    }

    // Create the enrollment
    $edge = new ArangoDocument();
    $edge->set('_from', "users/".$studentID);
    $edge->set('_to', "classes/".$classID);
    $enrollmentID = $this->arangodb_documentHandler->save('enrolledIn', $edge);
    if($enrollmentID){
        return $response->write("Successfully enrolled student " . $studentID . " into class " . $classID)
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
    $studentID = $args["ID"];
    /* Make sure student exists */
    if (!$this->arangodb_documentHandler->has('users', $studentID)) {
        return $response
            ->write("No student with that ID found")
            ->withStatus(400);
    }
    /* Query the DB */
    $statement = new ArangoStatement(
        $this->arangodb_connection, [
            'query' => 'FOR class IN OUTBOUND CONCAT("users/", @studentID) enrolledIn
                            LET teachers = (
                                FOR teacher IN INBOUND class._id teaches
                                    RETURN teacher.name
                            )
                            RETURN MERGE (class, {teachers : teachers})',
            'bindVars' => [
                'studentID' => $studentID
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

    $response->write(json_encode($res, JSON_PRETTY_PRINT));
    return $response;
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
    $tokenId = base64_encode(mcrypt_create_iv(32));
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
    if (!isset($formData['name'])    ||
        !isset($formData['email'])    ||
        !isset($formData['password'])    ||
        !isset($formData['role'])    ||
        !in_array($formData['role'], User::roles)) {
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