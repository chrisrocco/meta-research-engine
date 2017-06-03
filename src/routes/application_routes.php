<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 5/13/2017
 * Time: 8:41 PM
 */
use Models\Edges\Assignment;
use Models\Edges\EnrolledIn;
use Models\Vertices\Paper\Paper;
use Models\Vertices\Project\Project;
use Models\Vertices\SerializedProjectStructure;
use Models\Vertices\User;
use Models\Vertices\Variable;
use vector\ArangoORM\DB\DB;


/**
 * GET loadAssignmentGet
 * Summary: Called in the Paper Coder activity
 * Notes: Returns the assignment, the associated structure, and question list

 */
$app->GET('/loadPaperCoder', function($request, $response, $args) {

    $queryParams = $request->getQueryParams();
    $key = $queryParams['key'];

    $assignment = \Models\Edges\Assignment::retrieve( $key );
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

/*$app->GET('/loadConflictResolution', function($request, $response, $args) {
    $queryParams = $request->getQueryParams();
    $paperKey = $queryParams['paperKey'];
    $paper = \Models\Vertices\Paper::retrieve($paperKey);

    if (!$paper) {
        return $response
            ->write ("No paper with key ".$paperKey." found")
            ->withStatus(409);
    }

    $result = \DB\DB::query(
        'LET paper = DOCUMENT(@paperID)
                    LET valueRecords = (
                        FOR valueRecord IN masterEncoding.values
                            LET question = DOCUMENT ( CONCAT(@@questions,"/", valueRecord.question) )
                            RETURN MERGE (
                                {question : UNSET (question, "_id", "_rev") }, 
                                UNSET (valueRecord, "question")
                            )
                    )
                    
                    RETURN {
                        values : valueRecords,
                        scopes : masterEncoding.scopes,
                        structure : masterEncoding.structure
                    }',
        [
            'paperID' => \Models\Vertices\Paper::$collection."/".$paperKey,
            '@questions' => \Models\Vertices\Variable::$collection
        ],
        true
    )->getAll();

    return $response
        ->write(json_encode($result))
        ->withStatus (200);
});*/

$app->GET ('/loadEncoderDashboard', function ($request, $response, $args) {
    $queryParams = $request->getQueryParams();
    $userKey = $queryParams['userKey'];
    $user = \Models\Vertices\User::retrieve($userKey);
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

/**
 * GET loadAssignmentsDashboardGet
 * Summary: Called from a users dashboard
 * Notes: Returns a list of assignments, their papers, and conflicts

 */
$app->GET('/loadAssignments', function($request, $response, $args) {

    $queryParams = $request->getQueryParams();
    $userKey = $queryParams['userKey'];

    $user = User::retrieve( $userKey );

    $AQL = 'FOR vertex, edge IN INBOUND @root @@assignments
                FOR project IN OUTBOUND vertex @@paper_to_project
                    return {
                        "assignment": edge,
                        "paper": vertex,
                        "project": project
                    }';
    $bindings = [
        'root'  =>  $user->id(),
        '@assignments'  =>  \Models\Edges\Assignment::$collection,
        '@paper_to_project'   =>  \Models\Edges\PaperOf::$collection
    ];

    $data = DB::query($AQL, $bindings)->getAll();

    $response->write( json_encode($data, JSON_PRETTY_PRINT) );
    return $response;
});

$app->GET('/loadManageProject', function ($request, $response, $args) {
    $queryParams = $request->getQueryParams();
    $projectKey = $queryParams['projectKey'];

    $project = \Models\Vertices\Project\Project::retrieve($projectKey);
    if (!$project) {
        return $response->write ('No project with key '.$projectKey.' found')
            ->withStatus(409);
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
            '@paper_to_study' => \Models\Edges\PaperOf::$collection,
            '@assignments' => \Models\Edges\Assignment::$collection
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
    $queryParams = $request->getQueryParams();

    $cursor = DB::getAll( \Models\Vertices\Project\Project::$collection );
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