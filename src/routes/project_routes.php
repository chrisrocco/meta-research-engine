<?php

use Models\Vertices\Project\Project;
use Models\Vertices\Domain;
use Models\Vertices\Variable;
use Models\Vertices\Paper;
use Models\Vertices\User;

/*
 * GET studies/{projectname}/structure
 * Summary: Gets the domain / field structure of the specified research project
 */
$app->GET("/studies/{key}/structure", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    if (!$project) {
        return $response
            ->write("Project ".$project_key." not found")
            ->withStatus(409);
    }

    $structure = $project->getStructureFlat();
    if(!$structure) return $response->write("No Domains")->withStatus(400);

    return $response->write(json_encode($structure, JSON_PRETTY_PRINT));
});

/**
 * GET studies/{projectname}/variables
 * Summary: Gets a list of every field's name
 */
$app->GET("/studies/{key}/variables", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    $variables = $project->getVariablesFlat();
    if(!$variables) return $response->write("No Domains")->withStatus(400);

    return $response
        ->write(json_encode($variables, JSON_PRETTY_PRINT))
        ->withStatus(200);
});

$app->POST ('/studies/{key}/structure', function ($request, $response, $args) {
    $formData = $request->getParams();
    var_dump( $formData );
    $projectKey = $args['key'];
    $structure = json_decode( $formData['structure'], true);

    //TODO: check that the user of the token is an admin for the project

    $project = Project::retrieve($projectKey);
    if (!$project) {
        return $response
            ->write("No project found with key ". $projectKey)
            ->withStatus(404);
    }

    $tempDomIDMap = []; // temporary domain id => Domain

    //Remove the project's old structure
    $project->removeStructure(4);

    //Create each of the new domains
    foreach ($structure['domains'] as $newDom) {
        $domain = Domain::create([
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
            $project->addDomain( $tempDomIDMap[$newDom['id']] );
        }
        else {
            $tempDomIDMap[$newDom['parent']]->addSubdomain( $tempDomIDMap[$newDom['id']] );
        }
    }

//    var_dump($tempDomIDMap);

    //Create the new questions and connect them to parent domains
    foreach ($structure['questions'] as $newQuestion) {
        $tempParent = $newQuestion['parent'];
        unset($newQuestion['id'], $newQuestion['parent'], $newQuestion['$$hashKey']);
        $question = Variable::create( $newQuestion );
        $tempDomIDMap[$tempParent]->addVariable($question);
    }

    $newVersion = $project->updateVersion();

    $serializedStructure = \Models\Vertices\SerializedProjectStructure::retrieve($projectKey);
    if (!$serializedStructure) {
        $serializedStructure = \Models\Vertices\SerializedProjectStructure::create( ['_key' => $projectKey]);
    }
    $serializedStructure->update('structure', $structure );
    $serializedStructure->update('version', $newVersion);

    return $response
        ->write("Successfully updated project structure")
        ->withStatus(200);
});

$app->POST ('/studies/members', function ($request, $response, $args) {
    $formData = $request->getParams();
    $userKey = $formData['userKey'];
    $registrationCode = $formData['registrationCode'];
    $user = User::retrieve($userKey);

    /* Query Start */
    $AQL = "FOR project IN @@project_collection
                FILTER project.registrationCode == @registrationCode
                RETURN project._key";
    $bindings = [
        'registrationCode' => $registrationCode,
        '@project_collection' => Project::$collection
    ];
    $projectKey = \DB\DB::query( $AQL, $bindings )->getAll()[0];
    /* End Query */

    $project = Project::retrieve($projectKey);

    if (!$user) {
        return $response
            ->write("User ".$userKey. " not found")
            ->withStatus(400);
    }
    if (!$user->get('active')) {
        return $response
            ->write("User not verified. Please verify your email.")
            ->withStatus(400);
    }
    if (!$project) {
        return $response
            ->write("Project ".$projectKey. " not found")
            ->withStatus(404);
    }

    $status = $project->addUser ( $user, $registrationCode );

    switch($status) {
        case 400 :
            $message = "Project / registration code mismatch";
            break;
        case 409 :
            $message = "User already enrolled in Project. Aborting enrollment";
            break;
        case 200 :
            $message = "User successfully enrolled in project";
            break;
        default :
            $status = 500;
            $message = "Something went very, very wrong";
            break;
    }

    return $response
        ->write($message)
        ->withStatus($status);
});

/**
 * POST studies/{projectname}/papers
 * Summary: Adds a paper to a project
 */
$app->POST("/studies/{key}/papers", function ($request, $response, $args) {
    $formData = $request->getParsedBody();

    $paperArray = json_decode( $formData['papers'], true );

    $project_key = $args['key'];

    $project = Project::retrieve($project_key);

    foreach ( $paperArray as $paper ){
        $paperModel = Paper::create([
            'title'     =>  $paper['title'],
            'pmcID'     =>  $paper['pmcID']
        ]);
        $project->addpaper( $paperModel );
    }

    $count = count( $paperArray );
    return $response->write("Added $count papers to project");
});

$app->GET("/studies/{key}/papers", function( $request, $response, $args){
    $projectKey = $args['key'];
    $project = Project::retrieve( $projectKey );
    $papersArray = $project->getPapersFlat();
    return $response->write( json_encode($papersArray) );
});

/**
 * POST studies
 * Summary: Creates a project
 */
$app->POST("/studies", function ($request, $response, $args) {
    $formData = $request->getParams();

    $characters = 'ABCDEFGHIJKLMNOPQRZTUVWXYZ123456789';
    $registrationCode = '';
    $random_string_length = 6;
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $random_string_length; $i++) {
        $registrationCode .= $characters[mt_rand(0, $max)];
    }

    $project = Project::create([
        'name'  =>  $formData['name'],
        'description'   =>  $formData['description'],
        'registrationCode' => $registrationCode,
        'version' => 1,
        'assignmentTarget' => 2
    ]);

    return $response->write(
        json_encode([
            "projectKey" => $project->key(),
            "registrationCode" => $registrationCode
        ])
    );
});
