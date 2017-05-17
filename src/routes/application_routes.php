<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 5/13/2017
 * Time: 8:41 PM
 */


/**
 * GET loadAssignmentGet
 * Summary: Called in the Paper Coder activity
 * Notes: Returns the assignment, the associated structure, and question list

 */
$app->GET('/loadAssignment', function($request, $response, $args) {

    $queryParams = $request->getQueryParams();
    $key = $queryParams['key'];

    $assignment = \Models\Edges\Assignment::retrieve( $key );
    $study = $assignment->getProject();
    $questionsList = $study->getVariablesFlat();
    $structure = $study->getStructureFlat();

    $data = [
        "assignment" => $assignment->toArray(),
        "questions" =>  $questionsList,
        "structure" =>  $structure
    ];

    $response->write(json_encode($data, JSON_PRETTY_PRINT));
    return $response;
});

$app->GET ('/loadProjectBuilder', function ($request, $response, $args) {
    $queryParams = $request->getQueryParams();
    $projectKey = $queryParams['studyKey'];
    $project = \Models\Vertices\Project\Project::retrieve($projectKey);
    $structure = \Models\Vertices\SerializedProjectStructure::retrieve($projectKey);
    $data = [
        'structure' => $structure->get('structure'),
        'projectName' => $project->get('name')
    ];
    return $response->write(json_encode($data, JSON_PRETTY_PRINT));
});


/**
 * GET loadAssignmentsDashboardGet
 * Summary: Called from a users dashboard
 * Notes: Returns a list of assignments, their papers, and conflicts

 */
$app->GET('/loadAssignmentsDashboard', function($request, $response, $args) {

    $queryParams = $request->getQueryParams();
    $userKey = $queryParams['userKey'];

    $user = \Models\Vertices\User::retrieve( $userKey );

    $AQL = 'FOR vertex, edge IN INBOUND @root @@assignments
                FOR study IN OUTBOUND vertex @@paper_to_study
                    return {
                        "assignment": edge,
                        "paper": vertex,
                        "study": study
                    }';
    $bindings = [
        'root'  =>  $user->id(),
        '@assignments'  =>  \Models\Edges\Assignment::$collection,
        '@paper_to_study'   =>  \Models\Edges\PaperOf::$collection
    ];

    $data = \DB\DB::query($AQL, $bindings)->getAll();

    $response->write( json_encode($data, JSON_PRETTY_PRINT) );
    return $response;
});

$app->GET('/loadProjects', function($request, $response, $args) {
    $queryParams = $request->getQueryParams();

    $cursor = \DB\DB::getAll( \Models\Vertices\Project\Project::$collection );
    $documents = $cursor->getAll();
    $flat = [];
    foreach ( $documents as $doc ){
        $flat[] = $doc->getAll();
    }

    $data = [
        'projects' => $flat
    ];

    $response->write(json_encode( $data, JSON_PRETTY_PRINT ));
    return $response;
});