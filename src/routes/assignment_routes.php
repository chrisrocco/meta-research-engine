<?php
use Models\Vertices\Paper\Paper;
use \Models\Vertices\User;
use Models\Edges\Assignment\Assignment;
/**
 * GET assignmentsIDGet
 * Summary: Returns a single assignment
 * Notes:
 * Output-Formats: [application/json]
 */
$app->GET('/assignments/{key}', function ($request, $response, $args) {
    $assignment = Assignment::retrieve($args["key"]);

    return $response->write(json_encode($assignment->toArray(), JSON_PRETTY_PRINT));
});

/**
 * PUT assignmentsIDPut
 * Summary: Updates assignment with a students work
 * Notes:
 */
$app->PUT('/assignments/{key}', function ($request, $response, $args) {
    $formData = $request->getParsedBody();

    $assignment = Assignment::retrieve($args['key']);
    $assignment->update('done', $formData['done']);
    $assignment->update('completion', $formData['completion']);
    $assignment->update('encoding', $formData['encoding']);


    try {
        $paper = $assignment->getPaper();
        if (!$paper) {
            return $response
                ->write("Could not get Paper from assignment. Not merging into masterEncoding.")
                ->withStatus(500);
        }
        if (json_decode($formData['done']) === true) {
            $paper->roccoMerge($assignment);
        }
        $status = $paper->updateStatus();
    } catch ( Exception $e ){
        return $response
            ->write( json_encode([
                "error" => $e
            ]));
    }

    return $response
        ->write( json_encode([
            'msg' => "Assignment successfully updated.",
            'status' => $status
        ]))
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
    $assignments = $user->getAssignments(true);
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

    $user = User::retrieve($userKey);
    $paper = Paper::retrieve($paperKey);
    Assignment::assign($paper, $user);

    $response->write("Created Assignment");
});