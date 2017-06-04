<?php

use Models\Vertices\Project\Project;
use Models\Vertices\Domain;
use Models\Vertices\SerializedProjectStructure;
use Models\Vertices\Variable;
use Models\Vertices\Paper\Paper;
use Models\Edges\Assignment\Assignment;
use Models\Edges\Assignment\AssignmentManager;
use Models\Vertices\User;
use vector\ArangoORM\DB\DB;

/*
 * GET projects/{projectname}/structure
 * Summary: Gets the domain / field structure of the specified research project
 */
$app->GET("/projects/{key}/structure", function ($request, $response, $args) {
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
 * GET projects/{projectname}/variables
 * Summary: Gets a list of every field's name
 */
$app->GET("/projects/{key}/variables", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    $variables = $project->getVariablesFlat();
    if(!$variables) return $response->write("No Domains")->withStatus(400);

    return $response
        ->write(json_encode($variables, JSON_PRETTY_PRINT))
        ->withStatus(200);
});

error_reporting( E_ALL );
ini_set('display_errors', 1);

$app->POST ('/projects/{key}/structure', function ($request, $response, $args) {
    $formData = $request->getParams();
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
            '_key' => $newDom['id'],
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
        $newQuestion['_key'] = $newQuestion['id'];
        unset($newQuestion['id'], $newQuestion['parent'], $newQuestion['$$hashKey']);
        $question = Variable::create( $newQuestion );
        $tempDomIDMap[$tempParent]->addVariable($question);
    }

    $newVersion = $project->updateVersion();

    $serializedStructure = SerializedProjectStructure::retrieve($projectKey);
    if (!$serializedStructure) {
        $serializedStructure = SerializedProjectStructure::create( ['_key' => $projectKey]);
    }
    $serializedStructure->update('structure', $structure );
    $serializedStructure->update('version', $newVersion);

    return $response
        ->write("Successfully updated project structure")
        ->withStatus(200);
});

$app->POST ('/projects/members', function ($request, $response, $args) {
    $userKey = $request->getParam('userKey');
    $registrationCode = $request->getParam('registrationCode');

    $user = User::retrieve($userKey);

    $project_result_set = Project::getByExample( [ "registrationCode" => $registrationCode ] );

    if( count($project_result_set) === 0 ) return $response->withStatus( 404 )->write( "Project Not Found" );

    $project = $project_result_set[0];
    $enroll_result = $project->addUser( $user, $registrationCode );

    try {
        $assignmentTarget = $project->getUserAssignmentCap();
        $assignedPapers = AssignmentManager::assignUpTo($project, $user, $assignmentTarget);
        foreach ($assignedPapers as $paper) {
            $paper->updateStatus();
        }
    } catch (Exception $e) {
        throw new Exception( "Caleb Code Exception" );
    }


    if( $enroll_result == 200 ){
        return $response
            ->write( json_encode( [ 'studyName' => $project->get( 'name' ) ], JSON_PRETTY_PRINT) );
    }

    switch( $enroll_result ) {
        case 400 :
            $message = "Project / registration code mismatch";
            return $response->withStatus( 400 )->write( $message );
            break;
        case 409 :
            $message = "User already enrolled in Project. Aborting enrollment";
            return $response->withStatus( 409 )->write( $message );
            break;
        default:
            $status = 500;
            $message = "No exception here! Just a 500";
            return $response->withStatus( 500 )->write( $message );
            break;
    }
});

/**
 * POST projects/{projectname}/papers
 * Summary: Adds a paper to a project
 * FailCodes: badFileNameError, parseFailure, emptyFileError, columnCountError, interpretFailure
 * SuccessCode: success
 */
$app->POST("/projects/{key}/papers", function ($request, $response, $args) {
    $project_key = $args['key'];
    $project = Project::retrieve($project_key);
    $EXPECTED = "papersCSV";

    if (!$project) {
        return $response
            ->write ("No project with key ". $project_key." found.")
            ->withStatus(404);
    }

    /* ----- Validation Steps -----
     * 1.) File was posted
     * 2.) File is of type .csv
     * 3.) Structure of csv is valid
     * */
    if( !isset( $_FILES[$EXPECTED] ) ){
        return $response
            ->write(json_encode([
                'reason' => "badFileNameError",
                'msg' => "No file named ".$EXPECTED." uploaded"
            ]), JSON_PRETTY_PRINT)
            ->withStatus(400);
    }

    //try to parse the csv
    try {
        $csv = array_map('str_getcsv', file( $_FILES[$EXPECTED]['tmp_name'] ));
    } catch (Exception $e) {
        return $response
            ->write(json_encode([
                'reason' => "parseFailure",
                'msg' => $e->getMessage()
            ]), JSON_PRETTY_PRINT)
            ->withStatus(400);
    }
    //Is the file empty?
    if (!isset($csv[0])) {
        return $response
            ->write(json_encode([
                'reason' => "emptyFileError",
                'msg' => "Empty csv file given"
            ]), JSON_PRETTY_PRINT)
            ->withStatus(400);
    }
    //Are there exactly three columns?
    foreach ( $csv as $i => $row ){
        if ( count($row) !== 3 ) {
            return $response
                ->write(json_encode([
                    'reason' => "columnCountError",
                    'row' => $i + 1,
                    'msg' => "Incorrect number of columns specified: " . count( $row )
                ]), JSON_PRETTY_PRINT)
                ->withStatus(400);
        }
    }

    //Try to interpret the data
    try {
        foreach ($csv as $row) {
            $paperModel = Paper::create([
                'title' => $row[0],
                'description' => $row[1],
                'url' => $row[2],
                'status' => "pending",
                'masterEncoding' => []
            ]);
            $project->addpaper( $paperModel );
        }
    } catch (Exception $e) {
        return $response
            ->write(json_encode([
                'reason' => "interpretFailure",
                'msg' => $e->getMessage()
            ]), JSON_PRETTY_PRINT)
            ->withStatus(400);
    }

    $count = count( $csv );
    return $response
        ->write(json_encode([
            'reason' => "success",
            'newPaperCount' => $count,
            'msg' => "Added $count papers to project"
        ]), JSON_PRETTY_PRINT)
        ->withStatus(200);
});

$app->GET("/projects/{key}/papers", function( $request, $response, $args){
    $projectKey = $args['key'];
    $project = Project::retrieve( $projectKey );
    $papersArray = $project->getPapersFlat();
    return $response->write( json_encode($papersArray) );
});

/**
 * POST projects
 * Summary: Creates a project
 */
$app->POST("/projects", function ($request, $response, $args) {
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
