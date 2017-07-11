<?php

use uab\MRE\dao\AdminOf;
use uab\MRE\dao\AssignmentManager;
use uab\MRE\dao\Domain;
use uab\MRE\dao\Paper;
use uab\MRE\dao\Project;
use uab\MRE\dao\SerializedProjectStructure;
use uab\MRE\dao\User;
use uab\MRE\dao\Variable;
use vector\PMCAdapter\PMCAdapter;
use vector\MRE\Middleware\MRERoleValidator;
use vector\MRE\Middleware\RequireProjectAdmin;

$container = $app->getContainer();

/*
 * GET projects/{key}/structure
 * Summary: Gets the domain / field structure of the specified project
 */
$app->GET("/projects/{key}/structure", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    if (!$project) {
        return $response
            ->write("Project " . $project_key . " not found")
            ->withStatus(409);
    }

    $structure = $project->getStructure();
    if (!$structure) return $response->write("No Domains")->withStatus(400);

    return $response->write(json_encode($structure, JSON_PRETTY_PRINT));
});

$app->GET('/projects/{key}/structure/flat', function($req, $res, $args) {
    $project = Project::retrieve( $args['key'] );

    $serializedStructure = SerializedProjectStructure::generate($project);

    $result = [
        'structure' => $serializedStructure,
        'version' => $project->get('version')
    ];

    return $res->write( json_encode($result, JSON_PRETTY_PRINT) );
});

/**
 * GET projects/{key}/variables
 * Summary: Gets a list of every question in the project
 */
$app->GET("/projects/{key}/variables", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    $variables = $project->getVariablesFlat();
    if (!$variables) return $response->write("No Domains")->withStatus(400);

    return $response
        ->write(json_encode($variables, JSON_PRETTY_PRINT))
        ->withStatus(200);
});

$app->POST('/projects/{key}/structure', function ($request, $response, $args) {
    $formData = $request->getParams();
    $projectKey = $args['key'];
    $structure = json_decode($formData['structure'], true);

    $project = Project::retrieve($projectKey);
    if (!$project) {
        return $response
            ->write("No project found with key " . $projectKey)
            ->withStatus(404);
    }

    $tempDomIDMap = []; // temporary domain id => Domain

    //Remove the project's old structure
    $project->removeStructure(4);

    //Create each of the new domains
    foreach ($structure['domains'] as $newDom) {
        $domain = Domain::create([
//            '_key' => $newDom['id'],
            'name' => $newDom['name'],
            'description' => $newDom['description'],
            'tooltip' => $newDom['tooltip'],
            'icon' => $newDom['icon']
        ]);
        $tempDomIDMap[$newDom['id']] = $domain;
    }
    //Connect the domain_to_domain edges
    foreach ($structure['domains'] as $newDom) {
        if ($newDom['parent'] === "#") {
            $project->addDomain($tempDomIDMap[$newDom['id']]);
        } else {
            $tempDomIDMap[$newDom['parent']]->addSubdomain($tempDomIDMap[$newDom['id']]);
        }
    }

//    var_dump($tempDomIDMap);

    //Create the new questions and connect them to parent domains
    foreach ($structure['questions'] as $newQuestion) {
        $tempParent = $newQuestion['parent'];
        //$newQuestion['_key'] = $newQuestion['id'];
        unset($newQuestion['id'], $newQuestion['parent'], $newQuestion['$$hashKey']);
        $question = Variable::create($newQuestion);
        $tempDomIDMap[$tempParent]->addVariable($question);
    }

    $newVersion = $project->updateVersion();

    $serializedStructure = SerializedProjectStructure::getByProject($project);
    $serializedStructure->update('structure', $structure);
    $serializedStructure->update('version', $newVersion);

    return $response
        ->write("Successfully updated project structure")
        ->withStatus(200);
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

    $user_set = User::getByExample( ['email'=>$give_to_email] );

    if( count($user_set) === 0 ){
        return $response->withStatus( 400 )->write( json_encode( [
            "status"    =>  "NO_USER"
        ], JSON_PRETTY_PRINT ) );
    }

    $newAdmin = $user_set[0];

    if ($project->isAdmin($newAdmin)) {
        return $response->withStatus( 409 )->write("that user is already an owner");
    }

    AdminOf::createEdge( $project, $user );

    return $response->write(
        json_encode([
            "projectName" => $project->get('name'),
            "newOwner"  => $user->get('first_name')
        ])
    );
})->add(new RequireProjectAdmin($container));

$app->POST('/projects/members', function ($request, $response, $args) {
    $registrationCode = strtoupper( trim($request->getParam('registrationCode')) );
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

// Check which projects a user is enrolled in
$app->GET("/getEnrollments", function ($request, $response, $args) {
    $user = $this['user'];
    // TODO - be right back. In case i forget about this.d
});
