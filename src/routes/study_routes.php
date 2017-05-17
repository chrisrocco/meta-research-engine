<?php

use Models\Vertices\Study;
use Models\Vertices\Domain;
use Models\Vertices\Paper;
use Models\Vertices\User;

/*
 * GET studies/{studyname}/structure
 * Summary: Gets the domain / field structure of the specified research study
 */
$app->GET("/studies/{key}/structure", function ($request, $response, $args) {
    $study_key = $args['key'];
    $study = Study::retrieve($study_key);
    $structure = $study->getStructureFlat();
    if(!$structure) return $response->write("No Domains")->withStatus(400);

    return $response->write(json_encode($structure, JSON_PRETTY_PRINT));
});

/**
 * GET studies/{studyname}/variables
 * Summary: Gets a list of every field's name
 */
$app->GET("/studies/{key}/variables", function ($request, $response, $args) {
    $study_key = $args['key'];
    $study = Study::retrieve($study_key);
    $variables = $study->getVariablesFlat();
    if(!$variables) return $response->write("No Domains")->withStatus(400);

    return $response
        ->write(json_encode($variables, JSON_PRETTY_PRINT))
        ->withStatus(200);
});

$app->POST ('/studies/{key}/structure', function ($request, $response, $args) {
    $formData = $request->getParams();
    $studyKey = $args['key'];
    $structure = $formData['structure'];

    //TODO: check that the user of the token is an admin for the study
    //TODO: actually change the structure of the study
    $study = Study::retrieve($studyKey);
    if (!$study) {
        return $response
            ->write("No study found with key ". $studyKey)
            ->withStatus(404);
    }

    $serializedStructure = \Models\Vertices\SerializedProjectStructure::retrieve($studyKey);

    if (!$serializedStructure) {
        $serializedStructure = \Models\Vertices\SerializedProjectStructure::create(
            [
                '_key' => $studyKey,
                'structure' => $structure
            ]
        );
    }

    $obj = json_decode( $structure );

    var_dump( $obj );

    $serializedStructure->update('structure', $obj);
    return $response
        ->write("Successfully hackishly updated project structure")
        ->withStatus(200);
});

$app->POST ('/studies/members', function ($request, $response, $args) {
    //$studyKey = $args['key'];

    $formData = $request->getParams();
    $userKey = $formData['userKey'];
    $registrationCode = $formData['registrationCode'];

    $user = User::retrieve($userKey);
    $studyKey = \DB\DB::query('
        FOR study IN studies
            FILTER study.registrationCode == @registrationCode
            RETURN study._key
    ',[
        'registrationCode' => $registrationCode
    ])->getAll()[0];

    $study = Study::retrieve($studyKey);

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

    if (!$study) {
        return $response
            ->write("Study ".$studyKey. " not found")
            ->withStatus(400);
    }

    $status = $study->addUser ($user, $registrationCode);

    switch($status) {
        case 400 :
            $message = "Study / registration code mismatch";
            break;
        case 409 :
            $message = "User already enrolled in Study. Aborting enrollment";
            break;
        case 200 :
            $message = "User successfully enrolled in study";
            break;
        default :
            $status = 500;
            $message = "Something went very, very, wrong";
            break;
    }

    return $response
        ->write($message)
        ->withStatus($status);
});

/**
 * POST studies/{studyname}/papers
 * Summary: Adds a paper to a study
 */
$app->POST("/studies/{key}/papers", function ($request, $response, $args) {
    $formData = $request->getParams();
    $study_key = $args['key'];

    $study = Study::retrieve($study_key);

    $paper = Paper::create([
        'title'     =>  $formData['title'],
        'pmcID'     =>  $formData['pmcID']
    ]);

    $study->addpaper($paper);

    return $response->write("Added paper to study");
});

/**
 * POST studies
 * Summary: Creates a study
 */
$app->POST("/studies", function ($request, $response, $args) {
    $formData = $request->getParams();

    $study = Study::create([
        'name'  =>  $formData['name'],
        'description'   =>  $formData['description'],
        'registrationCode' => base64_encode(random_bytes(8))
    ]);

    return $response->write(
        json_encode([
            "projectKey" => $study->key(),
            "registrationCode" => $study->get('registrationCode')
        ])
    );
});

/**
 * POST studies/{key}/domains
 * Summary: Adds a domain to a study
 *
 * The domain should have it's subdomains already build
 */
$app->POST("/studies/{key}/domains", function ($request, $response, $args) {

    $domain = Domain::retrieve( $request->getParam("domainKey") );
    $study = Study::retrieve( $args['key'] );

    $domain->addDomain( $domain );

    return $response->write("Created Study");
});