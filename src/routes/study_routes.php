<?php
use Entities\Study as Study;

/*
 * GET studies/{studyname}/structure
 * Summary: Gets the domain / field structure of the specified research study
 */
$app->GET("/studies/{studyname}/structure", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    $study = new Study($studyName);
    $structure = $study->getStructure();

    return $response->write(json_encode($structure, JSON_PRETTY_PRINT));
});

/**
 * GET studies/{studyname}/variables
 * Summary: Gets a list of every field's name
 */
$app->GET("/studies/{studyname}/variables", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    $study = new Study($studyName);
    $variables = $study->getVariables();

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