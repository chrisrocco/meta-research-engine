<?php

use uab\mre\app\AdjListStructure;
use uab\mre\app\StructureService;
use uab\MRE\dao\AdminOf;
use uab\MRE\dao\AssignmentManager;
use uab\MRE\dao\Domain;
use uab\MRE\dao\Paper;
use uab\MRE\dao\Project;
use uab\MRE\dao\SerializedProjectStructure;
use uab\MRE\dao\User;
use uab\MRE\dao\Variable;
use uab\mre\lib\AdjList;
use uab\mre\lib\AdjNode;
use uab\mre\lib\BadNodeException;
use uab\mre\lib\DuplicateIdException;
use uab\mre\lib\NoParentException;
use uab\mre\lib\ObjValidator;
use uab\mre\lib\SchemaValidatorException;
use uab\mre\RectangleService;
use vector\ArangoORM\DB\DB;
use vector\PMCAdapter\PMCAdapter;
use vector\MRE\Middleware\MRERoleValidator;
use vector\MRE\Middleware\RequireProjectAdmin;

$container = $app->getContainer();

/**
 * Legacy
 * ==================
 */
$app->GET("/projects/{key}/structure", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    if (!$project) return $response->withStatus(404)->write("Project not found");

    $structure = $project->getStructure();
    if (!$structure) return $response->write("No Domains")->withStatus(404);

    return $response->write(json_encode($structure, JSON_PRETTY_PRINT));
});
$app->GET('/projects/{key}/structure/flat', function ($req, $res, $args) {
    $project = Project::retrieve($args['key']);

    $serializedStructure = SerializedProjectStructure::generate($project);

    $result = [
        'structure' => $serializedStructure,
        'version' => $project->get('version')
    ];

    return $res->write(json_encode($result, JSON_PRETTY_PRINT));
});
/**
 * ==================
 * Legacy
 */

$app->GET("/projects/{key}/structure/nested", function ($request, $response, $args) {
    $project = Project::retrieve($args['key']);
    if (!$project) return $response->withStatus(404)->write("Project not found");

    $structure = StructureService::getStructureNested( $project );

    return $response->withJson($structure);
});
$app->GET('/projects/{key}/structure/adjacent', function ($req, $res, $args) {
    $project = Project::retrieve($args['key']);

    $structure = StructureService::getStructureAdj( $project );

    $result = [
        'structure' => $structure,
        'version' => $project->get('version')
    ];

    return $res->write(json_encode($result, JSON_PRETTY_PRINT));
});

$app->GET("/projects/{key}/variables", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    $variables = $project->getVariablesFlat();
    if (!$variables) return $response->write("No Domains")->withStatus(400);

    return $response
        ->write(json_encode($variables, JSON_PRETTY_PRINT))
        ->withStatus(200);
});

/**
 * UPDATE A PROJECT'S STRUCTURE
 *
 * This route accepts a project structure in an adjacency list format
 * to replace the existing structure.
 *
 * 1.) Parses the user input as adjacency list, validating it
 * 2.) Hands off the constructed adjacency list to a service for upload
 * 3.) Descriptive exception handling
 */
$app->POST('/projects/{key}/structure', function ($request, $response, $args) {

    $projectKey = $args['key'];
    $structure = json_decode( $request->getParam('structure'), true );

    /**
     * Chris Code Start
     * ===================
     */
    try {
        $structure_schema = ['domains', 'questions'];                               // first thing's first..
        $node_schema = ['name', 'tooltip', 'icon', 'parent', 'id'];                 // shared by both domains & questions
        ObjValidator::forceSchema($structure, $structure_schema);                   // make sure we get domains and questions
        $arr_domains = $structure['domains'];                                       // - the domains
        $arr_questions = $structure['questions'];                                   // - the questions
        $adj_list = new AdjListStructure();                                         // start a new structure adjacency list
        foreach ($arr_domains as $domain) {                                         // parse the domains
            ObjValidator::forceSchema($domain, $node_schema);                           // enforce schema
            $parent = AdjNode::ROOT;                                                    // default parent to root
            if ($domain['parent'] != "#") $parent = $domain['parent'];                  // ~ else parse parent
            $node = new AdjNode($domain['id'], $parent, Domain::$collection, [          // make node object
                'name' => $domain['name'],
                'description' => $domain['description'],
                'tooltip' => $domain['tooltip'],
                'icon' => $domain['icon'],
            ]);
            $adj_list->addNode($node);                                                  // add it to the structure adjacency list
        }
        foreach ($arr_questions as $question) {                                     // parse the questions
            ObjValidator::forceSchema($question, $node_schema);                         // enforce schema
            $id = $question['id'];                                                      // temp id
            $parent = $question['parent'];                                              // temp parent
            unset($question['id'], $question['parent'], $question['$$hashKey']);        // dynamic attributes prevent using schema. careful here!
            $node = new AdjNode($id, $parent, Variable::$collection, $question);        // make node object
            $adj_list->addNode($node);                                                  // add it to the structure adjacency list
        }
        $adj_list->validateParents();                                               // guarantees that all nodes have a valid parent
        $project = Project::retrieve($projectKey);                                  // get the project
        if (!$project) return $response                                             // ~ if it exists
            ->withStatus(404)->write("Project not found");
        StructureService::replaceStructure($project, $adj_list);                    // with a confirmed good structure, a service provider handles the upload
        $project->updateVersion();                                                  // increment the version after a structural change
        return $response->withJson([
            "status"    =>  "OK",
            "current_version"   =>  $project->get('version')
        ]);
    } catch ( BadNodeException $bne ) {                             // Thrown when a malformed node is provided.
        return $response->withStatus(400)->withJson([
            "status"    =>  BadNodeException::class,
            "collection"    =>  $bne->collection,
            "msg"       =>  $bne->getMessage()
        ]);
    } catch ( DuplicateIdException $die ){                          // Thrown when duplicate ID's are provided
        return $response->withStatus(400)->withJson([
            "status"    =>  DuplicateIdException::class,
            "duplicate_id"  =>  $die->id,
            "msg"   =>  $die->getMessage()
        ]);
    } catch ( NoParentException $npe ){
        return $response->withStatus(400)->withJson([               // Thrown when a node claims a parent that does not exist
            "status"    =>  NoParentException::class,
            "node_id"   =>  $npe->node,
            "parent_id" =>  $npe->parent_id,
            "msg"   =>  $npe->getMessage()
        ]);
    } catch ( SchemaValidatorException $sve ){                      // Thrown when anything is missing required properties
        return $response->withStatus(400)->withJson([
            "status"    =>  SchemaValidatorException::class,
            "required"   =>  $sve->required,
            "missing"   =>  $sve->missing,
            "from"      =>  $sve->from,
            "msg"   =>  $sve->getMessage()
        ]);
    }
    /**
     * ===================
     * Chris Code End
     */
})->add(new RequireProjectAdmin($container));

$app->POST("/projects/{key}/fork", function ($request, $response, $args) {
    $projectKey = $args['key'];
    $formData = $request->getParams();

    $oldProject = Project::retrieve($projectKey);
    if (!$oldProject) {
        return $response
            ->write("No project found with key " . $projectKey)
            ->withStatus(404);
    }

    $name = isset($formData['name']) ? $formData['name'] : $oldProject->get('name');
    $description = isset($formData['description']) ? $formData['description'] : $oldProject->get('description');
    $assignmentTarget = isset($formData['assignmentTarget']) ? abs(intval($formData['assignmentTarget'])) : $oldProject->get('assignmentTarget');

    //Create the new project
    $project = Project::create([
        'name' => $name,
        'description' => $description,
        'registrationCode' => Project::generateRegistrationCode(6),
        'version' => 1,
        'assignmentTarget' => $assignmentTarget
    ]);

    $user = $this['user'];
    AdminOf::createEdge($project, $user);

    //Get the old project's structure
    $structure = $oldProject->getStructure();

    //Copy the structure over
    foreach ($structure as $rawTopDomain) {
        $topDomain = Domain::createFromRaw($rawTopDomain);
        $project->addDomain($topDomain);
        $topDomain->addRawVariables($rawTopDomain['variables']);
        $topDomain->addRawSubdomainsRecursive($rawTopDomain['subdomains']);
    }

    $serializedStructure = SerializedProjectStructure::getByProject($project);
    $serializedStructure->refresh();


    return $response
        ->write(json_encode($project, JSON_PRETTY_PRINT))
        ->withStatus(200);
})->add(new RequireProjectAdmin($container));

$app->POST("/projects/{key}/makeOwner", function ($request, $response, $args) {
    $give_to_email = $request->getParam("userEmail");
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);

    $user = $this['user'];
    if (!$project->isAdmin($user)) {
        return $response->write("You are not an admin of this project.")->withStatus(403);
    }

    $user_set = User::getByExample(['email' => $give_to_email]);

    if (count($user_set) === 0) {
        return $response->withStatus(400)->write(json_encode([
            "status" => "NO_USER"
        ], JSON_PRETTY_PRINT));
    }

    $newAdmin = $user_set[0];

    if ($project->isAdmin($newAdmin)) {
        return $response->withStatus(409)->write("that user is already an owner");
    }

    AdminOf::createEdge($project, $newAdmin);

    return $response->write(
        json_encode([
            "projectName" => $project->get('name'),
            "newOwner" => $newAdmin->get('first_name')
        ])
    );
})->add(new RequireProjectAdmin($container));

$app->POST('/projects/members', function ($request, $response, $args) {
    $registrationCode = strtoupper(trim($request->getParam('registrationCode')));
    $user = $this['user'];

    $project_result_set = Project::getByExample(["registrationCode" => $registrationCode]);

    if (count($project_result_set) === 0) return $response->withStatus(404)->write("Project Not Found");

    $project = $project_result_set[0];
    $enroll_result = $project->addUser($user, $registrationCode);

    switch ($enroll_result) {
        case 200 :
            break;
        case 400 :
            $message = "Project / registration code mismatch";
            return $response->withStatus(400)->write($message);
            break;
        case 409 :
            $message = "User already enrolled in Project. Aborting enrollment";
            return $response->withStatus(409)->write($message);
            break;
        default:
            $status = 500;
            $message = "No exception here! Just a 500";
            return $response->withStatus(500)->write($message);
            break;
    }

    try {
        $assignmentTarget = $project->getUserAssignmentCap();
        $assignedPapers = AssignmentManager::assignUpTo($project, $user, $assignmentTarget);
        foreach ($assignedPapers as $paper) {
            $paper->updateStatus();
        }
    } catch (Exception $e) {
        throw new Exception("Caleb Code Exception");
    }

    if ($enroll_result === 200) {
        return $response
            ->write(json_encode(['studyName' => $project->get('name')], JSON_PRETTY_PRINT));
    }
});

/**
 * POST projects/{key}/papers
 * Summary: Adds a paper to a project
 * FailCodes: badFileNameError, parseFailure, emptyFileError, columnCountError, interpretFailure
 * SuccessCode: success
 */
$app->POST("/projects/{key}/papers", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    $user = $this['user'];
    $paperData = $request->getParsedBody()['papers'];

    if (!$project->isAdmin($user)) {
        return $response->write("You are not an admin of this project.")->withStatus(403);
    }

    //Is the file empty?
    if (!isset($paperData[0])) {
        return $response
            ->write(json_encode([
                'reason' => "emptyFileError",
                'msg' => "Empty csv file given"
            ]), JSON_PRETTY_PRINT)
            ->withStatus(400);
    }
    //Are there exactly three columns?
    foreach ($paperData as $i => $row) {
        if (count($row) !== 3) {
            return $response
                ->write(json_encode([
                    'reason' => "columnCountError",
                    'row' => $i + 1,
                    'msg' => "Incorrect number of columns specified: " . count($row)
                ]), JSON_PRETTY_PRINT)
                ->withStatus(400);
        }
    }
    foreach ($paperData as $paperRow) {
        $paperModel = Paper::create([
            'title' => $paperRow[0],
            'description' => $paperRow[1],
            'url' => $paperRow[2],
            'status' => "pending",
            'masterEncoding' => []
        ]);
        $project->addpaper($paperModel);
    }

    $count = count($paperData);
    return $response
        ->write(json_encode([
            'reason' => "success",
            'newPaperCount' => $count,
            'msg' => "Added $count papers to project"
        ]), JSON_PRETTY_PRINT)
        ->withStatus(200);
})->add(new RequireProjectAdmin($container));
$app->POST("/projects/{key}/papers/byPMCID", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    $user = $this['user'];
    $pmcIDs = $request->getParsedBody()['pmcIDs'];

    if (!$project->isAdmin($user)) {
        return $response->write("You are not an admin of this project.")->withStatus(403);
    }

    $found = [];
    $not_found = [];

    $adapter = new PMCAdapter("ResearchCoder", "chris.rocco7@gmail.com");
    foreach ($pmcIDs as $pmcID) {
        $result = $adapter->lookupPMCID($pmcID);
        if ($adapter->wasSuccessful()) {
            $paperModel = Paper::create([
                'title' => $result->getTitle(),
                'description' => $result->getJournalName(),
                'url' => PMCAdapter::getEmbeddingURL($pmcID),
                'status' => "pending",
                'masterEncoding' => []
            ]);
            $project->addpaper($paperModel);
            $found[] = $pmcID;
        } else {
            $not_found[] = $pmcID;
        }
    }

    return $response
        ->write(json_encode([
            'reason' => "success",
            'newPaperCount' => count($found),
            'succeeded' => $found,
            'failed' => $not_found
        ]), JSON_PRETTY_PRINT)
        ->withStatus(200);
})->add(new RequireProjectAdmin($container));
$app->GET("/projects/{key}/papers", function ($request, $response, $args) {
    $projectKey = $args['key'];
    $project = Project::retrieve($projectKey);
    $papersArray = $project->getPapersFlat();
    return $response->write(json_encode($papersArray));
})->add(new RequireProjectAdmin($container));

$app->POST("/projects", function ($request, $response, $args) {
    $formData = $request->getParams();

    $registrationCode = Project::generateRegistrationCode(6);

    $project = Project::create([
        'name' => $formData['name'],
        'description' => $formData['description'],
        'registrationCode' => $registrationCode,
        'version' => 1,
        'assignmentTarget' => 2
    ]);

    $user = $this['user'];
    AdminOf::createEdge($project, $user);

    return $response->write(
        json_encode([
            "projectKey" => $project->key(),
            "registrationCode" => $registrationCode
        ])
    );
})->add(new MRERoleValidator(['manager']));


$app->GET("/projects/{id}/rectangle", function ($request, $response, $args) {
    $project = Project::retrieve($args['id']);

    // results of table headers query
//    $project_questions = json_decode( file_get_contents(__DIR__.'/th.json'), true );
    $project_questions = $project->getVariablesFlat();

// results of table rows query
    $query_rows = DB::query("FOR paper IN INBOUND @project paper_of
                                FOR user, assignment IN ANY paper assignments
                                    RETURN {
                                        project: 123,
                                        paper: paper.title,
                                        user: user._id,
                                        inputs: assignment.encoding.constants[*]
                                    }", [
                                        "project" => $project->id()
                                    ] )->getAll();

// define columns
    $columns = [ 'user', 'paper' ];
    foreach ( $project_questions as $var ){
        $columns[] = $var['_key'];
    }
// start a rectangle
    $rect = new Rectangle( $columns );
    foreach ( $query_rows as $row ){
        $responses = [];
        foreach( $row['inputs'] as $input ){
            $responses[$input['question']] = RectangleService::toString($input['data']);
        }
        $out = $responses;
        $out['paper'] = $row['paper'];
        $out['user'] = $row['user'];
        $rect->recordRow( $out );
    }

// pretty headers
    $headers = ['user', 'paper'];
    foreach ( $project_questions as $var ){
        $headers[] = $var['name'];
    }
    $rect->setHeaders( $headers );

    return $response->write($rect->exportCSV());
});
