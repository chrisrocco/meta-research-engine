<?php
use \Models\Vertices\User;
use Models\Vertices\Paper;
use Models\Edges\Assignment;

/**
 * GET assignmentsIDGet
 * Summary: Returns a single assignment
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/assignments/{key}', function ($request, $response, $args) {

    $assignment = Assignment::retrieve($args["key"]);

    return $response->write(json_encode($assignment->toArray(),  JSON_PRETTY_PRINT));
});

/**
 * PUT assignmentsIDPut
 * Summary: Updates assignment with a students work
 * Notes:
 */
$app->PUT('/assignments/{key}', function ($request, $response, $args) {
    $formData = $request->getParams();

    $assignment = Assignment::retrieve( $args['key'] );
    foreach ($formData as $key => $value){
        if($assignment->get($key) !== null){
            $assignment->update($key, $value);
        }
    }

    return $response
        ->write("Updated Assignment " . $args['key'])
        ->withStatus(200);
});

/**
 * GET studentsIDAssignmentsGet
 * Summary: Returns a list of assignments to a student
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/users/{key}/assignments', function ($request, $response, $args) {
    $user = User::retrieve($args['key']);
    $assignments = $user->getAssignments( true );
    return $response->write(json_encode($assignments, JSON_PRETTY_PRINT));
});

/**
 * POST studentsIDAssignmentsPost
 * Summary: Creates an assignment to a student
 * Notes:
 */
$app->POST('/assignments', function ($request, $response, $args) {
    $paperKey = $request->getParam("paperKey");
    $userKey = $request->getParam("userKey");

    $user = User::retrieve( $userKey );
    $paper = Paper::retrieve( $paperKey );
    Assignment::assign( $paper, $user );

    $response->write("Created Assignment");
});