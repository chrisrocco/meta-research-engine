<?php
use Entities\Assignment as Assignment;

/**
 * GET assignmentsIDGet
 * Summary: Returns a single assignment
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/assignments/{ID}', function ($request, $response, $args) {
    $assignment = new Assignment($args['ID']);
    $assignmentObject = $assignment->getAssignment();
    return $response->write(json_encode($assignmentObject,  JSON_PRETTY_PRINT));
});

/**
 * PUT assignmentsIDPut
 * Summary: Updates assignment with a students work
 * Notes:
 */
$app->PUT('/assignments/{ID}', function ($request, $response, $args) {
    $formData = $request->getParams();

    $assignment = new Assignment($args['ID']);
    $assignment->update($formData);

    return $response
        ->write("Updated Assignment " . $args['ID'])
        ->withStatus(200);
});

/**
 * GET studentsIDAssignmentsGet
 * Summary: Returns a list of assignments to a student
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/users/{ID}/assignments', function ($request, $response, $args) {
    $user = new UserHandler($args["ID"]);
    $assignments = $user->getAssignments();
    return $response->write(json_encode($assignments, JSON_PRETTY_PRINT));
});

/**
 * POST studentsIDAssignmentsPost
 * Summary: Creates an assignment to a student
 * Notes:
 */
$app->POST('/users/{ID}/assignments', function ($request, $response, $args) {
    $pmcID = $request->getParam("pmcID");

    $user = new UserHandler($args['ID']);
    $user->createAssignment($pmcID);

    $response->write("Created Assignment");
});