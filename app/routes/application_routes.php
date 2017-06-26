<?php
use uab\MRE\dao\AdminOf;
use uab\MRE\dao\Assignment;
use uab\MRE\dao\EnrolledIn;
use uab\MRE\dao\Paper;
use uab\MRE\dao\PaperOf;
use uab\MRE\dao\Project;
use uab\MRE\dao\SerializedProjectStructure;
use uab\MRE\dao\User;
use uab\MRE\dao\Variable;
use vector\ArangoORM\DB\DB;

$app->GET('/loadPaperCoder', function($request, $response, $args) {

    $queryParams = $request->getQueryParams();
    $key = $queryParams['key'];

    $assignment = Assignment::retrieve( $key );
    $paper = $assignment->getPaper();
    $project = $assignment->getProject();
    $questionsList = $project->getVariablesFlat();
    $structure = $project->getStructureFlat();

    $data = [
        "assignment" => $assignment->toArray(),
        "paper"     => $paper->toArray(),
        "questions" =>  $questionsList,
        "structure" =>  $structure
    ];

    $response->write(json_encode($data, JSON_PRETTY_PRINT));
    return $response;
});

$app->GET ('/loadEncoderDashboard', function ($request, $response, $args) {
    $queryParams = $request->getQueryParams();
    $userKey = $queryParams['userKey'];
    $user = User::retrieve($userKey);
    if (!$user) {
        return $response
            ->write("No user with key ". $userKey." found")
            ->withStatus(409);
    }
    $query = DB::query('
            LET user = DOCUMENT (@userID)
            LET assignmentStatuses = (
                FOR paper, assignment IN INBOUND user._id @@assignments
                    return TO_BOOL(assignment.done)
            )
            
            RETURN {
                "projects" : (
                    FOR project IN OUTBOUND user._id @@enrolled_in
                        RETURN project
                ),
                "activeAssignments" : (
                    FOR status IN assignmentStatuses
                        FILTER status == false
                        COLLECT WITH COUNT INTO length
                        RETURN length
                ),
                "completeAssignments" : (
                    FOR status IN assignmentStatuses
                        FILTER status == true
                        COLLECT WITH COUNT INTO length RETURN length
                ),
                "totalActivePapers" : (
                    FOR paper IN @@papers
                        FILTER paper.status != "pending"
                        COLLECT WITH COUNT INTO length RETURN length
                ),
                "totalUsers" : COUNT (@@users),
                "totalProjects" : COUNT (@@projects),
                "totalQuestionsAnswered" : 
                SUM (
                    FOR p IN @@papers
                        FILTER IS_ARRAY (p.masterEncoding)
                        FOR record IN p.masterEncoding
                            FOR response IN record.responses
                                RETURN COUNT (response.users)
                )
            }',
        [
            'userID' => User::$collection."/".$userKey,
            '@assignments' => Assignment::$collection,
            '@enrolled_in' => EnrolledIn::$collection,
            '@papers' => Paper::$collection,
            '@users' => User::$collection,
            '@projects' => Project::$collection
        ],
        true)->getAll();
        return $response
            ->write(json_encode($query[0]))
            ->withStatus(200);
});

$app->GET ('/loadProjectBuilder', function ($request, $response, $args) {
    $queryParams = $request->getQueryParams();
    $projectKey = $queryParams['projectKey'];
    $project = Project::retrieve($projectKey);
    $result_set_structure = SerializedProjectStructure::getByExample([ "_key" => $projectKey ]);

    $data = [];
    if( count( $result_set_structure ) == 1 ){
        $structure = $result_set_structure[0];
        $data['structure'] = $structure->get('structure');
    } else {
        $data['structure'] = false;
    }
    $data['projectName'] = $project->get('name');
    return $response->write(json_encode($data, JSON_PRETTY_PRINT));
});

$app->GET('/loadAssignments', function($request, $response, $args) {

    $queryParams = $request->getQueryParams();
    $userKey = $queryParams['userKey'];

    $user = User::retrieve( $userKey );

    $AQL = 'LET user = @root
            LET assignments = (
                FOR vertex, edge IN INBOUND user @@assignments
                    FOR project IN OUTBOUND vertex @@paper_to_project
                        RETURN {
                            "assignment": edge,
                            "paper": vertex,
                            "project": project
                        }
            )
            LET projects = (
                FOR vertex, edge IN ANY user @@enrollments
                    RETURN vertex
            )
            RETURN {
                assignments: assignments,
                projects: projects
            }';
    $bindings = [
        'root'  =>  $user->id(),
        '@assignments'  =>  Assignment::$collection,
        '@paper_to_project'   =>  PaperOf::$collection,
        '@enrollments'  =>  EnrolledIn::$collection
    ];

    $data = DB::query($AQL, $bindings)->getAll();

    $response->write( json_encode($data, JSON_PRETTY_PRINT) );
    return $response;
});

$app->GET('/loadManageProject', function ($request, $response, $args) {
    $queryParams = $request->getQueryParams();
    $projectKey = $queryParams['projectKey'];

    $project = Project::retrieve($projectKey);
    if (!$project) {
        return $response->write ('No project with key '.$projectKey.' found')
            ->withStatus(400);
    }

    $papers = DB::query(
        'FOR paper IN INBOUND @studyID @@paper_to_study
    COLLECT
        key = paper._key,
        title = paper.title,
        description = paper.description,
        status = paper.status,
        assignedUsers = (
             FOR user, assignment IN OUTBOUND paper._id @@assignments
                RETURN {"_key" : user._key, "first_name" : user.first_name, "last_name" : user.last_name, "email" : user.email}
        ),
        assignmentCount = COUNT ( 
            FOR user, assignment IN OUTBOUND paper._id @@assignments
                RETURN TRUE
        )
    RETURN {
        "key" : key,
        "title" : title,
        "status" : status,
        "description" : description,
        "assignedUsers" : assignedUsers,
        "assignmentCount" : assignmentCount
    }',
        [
            'studyID' => $project->id(),
            '@paper_to_study' => PaperOf::$collection,
            '@assignments' => Assignment::$collection
        ],
        true
    )->getAll();

    if ($papers === false) {
        return $response->write("Error retrieving papers")
            ->withStatus(500);
    }

    $return = [
        'project' => $project->toArray(),
        'papers' => $papers
    ];

    return $response
        ->write (json_encode($return, JSON_PRETTY_PRINT))
        ->withStatus(200);


});

$app->GET('/loadProjects', function($request, $response, $args) {
    $user_data = (array)($request->getAttribute("jwt")->data);

    $AQL = "FOR project IN OUTBOUND @user @@admin_of
                RETURN project";
    $bindings = [
        "user"      =>  User::$collection."/".$user_data['_key'],
        "@admin_of" =>  AdminOf::$collection
    ];

    $result_set = DB::query( $AQL, $bindings, true )->getAll();

    $data = [
        'projects' => $result_set
    ];

    $response->write(json_encode( $data, JSON_PRETTY_PRINT ));
    return $response;
});

$app->GET('/loadCodeBook', function($request, $response, $args) {

    $cursor = DB::getAll( Project::$collection );
    $projects = Project::wrapAll( $cursor );

    $output = [];
    foreach ( $projects as $project ){
        $output[] = [
            "project" => $project->toArray(),
            "structure" => $project->getStructureFlat()
        ];
    }

    return $response
        ->write( json_encode($output, JSON_PRETTY_PRINT) );
});

$app->GET('/loadConflictResolution', function($request, $response, $args) {
    $assignmentKey = $request->getQueryParam("assignmentKey");

    $myAssignment   = Assignment::retrieve( $assignmentKey );
    $thePaper       = $myAssignment->getPaper();
    $collaborators  = $thePaper->getCollaborators();

    /* Inject actual question objects */
    $masterEncodingArr = $thePaper->get( 'masterEncoding' );
    foreach ( $masterEncodingArr as &$record ){
        $questionKey = $record['question'];
        $questionModel = Variable::retrieve( $questionKey );
        $record['question'] = $questionModel->toArray();
    }
    $paperObj = $thePaper->toArray();
    $paperObj['masterEncoding'] = $masterEncodingArr;

    $output = [];
    $output['assignment']   = $myAssignment->toArray();
    $output['paper']        = $paperObj;
    foreach ( $collaborators as $user ){
        if( $user->id() == $myAssignment->getTo() ) continue;   // don't include myself as a collaborator
        $output['collaborators'][] = [
            "_key"  =>  $user->key()
        ];
    }

    return $response
        ->write( json_encode($output, JSON_PRETTY_PRINT) );
});

$app->GET('/activities/report', function($request, $response, $args) {
    $paperKey = $request->getQueryParam("paperKey");
    $thePaper       = Paper::retrieve( $paperKey );

    /* Inject actual question objects */
    $masterEncodingArr = $thePaper->get( 'masterEncoding' );
    foreach ( $masterEncodingArr as &$record ){
        $questionKey = $record['question'];
        $questionModel = Variable::retrieve( $questionKey );
        if( $questionModel !== false ){
            $record['question'] = $questionModel->toArray();
        }
    }
    $paperObj = $thePaper->toArray();
    $paperObj['masterEncoding'] = $masterEncodingArr;

    $output = [];
    $output['paper']        = $paperObj;

    return $response->withJson( $output );
});