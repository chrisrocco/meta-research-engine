<?php

/*
 * GET studies/{studyname}/structure
 * Summary: Gets the domain / field structure of the specified research study
 */
$app->GET("/studies/{key}/structure", function ($request, $response, $args) {
    $study_key = $args['key'];
    $study = \Models\Study::retrieve($study_key);
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

    $study_key = $args['key'];
    $study = \Models\Study::retrieve($study_key);
    $variables = $study->getVariablesFlat();
    if(!$variables) return $response->write("No Domains")->withStatus(400);

    return $response->write(json_encode($variables, JSON_PRETTY_PRINT));
});

/**
 * POST studies/{studyname}/papers
 * Summary: Adds a paper to a study
 */
$app->POST("/studies/{key}/papers", function ($request, $response, $args) {
    $formData = $request->getParams();
    $study_key = $args['key'];

    $study = \Models\Study::retrieve($study_key);

    $paper = \Models\Paper::create([
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

    $study = \Models\Study::create([
        'name'  =>  $formData['name']
    ]);

    return $response->write("Created Study");
});

/**
 * POST studies/{key}/domains
 * Summary: Adds a domain to a study
 *
 * The domain should have it's subdomains already build
 */
$app->POST("/studies/{key}/domains", function ($request, $response, $args) {

    $domain = \Models\Domain::retrieve( $request->getParam("domainKey") );
    $study = \Models\Study::retrieve( $args['key'] );

    $domain->addDomain( $domain );

    return $response->write("Created Study");
});