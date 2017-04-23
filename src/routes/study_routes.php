<?php

/*
 * GET studies/{studyname}/structure
 * Summary: Gets the domain / field structure of the specified research study
 */
$app->GET("/studies/{studyname}/structure", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    $study = new StudyHandler($studyName);
    $structure = $study->getStructure();

    return $response->write(json_encode($structure, JSON_PRETTY_PRINT));
});

/**
 * GET studies/{studyname}/variables
 * Summary: Gets a list of every field's name
 */
$app->GET("/studies/{studyname}/variables", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    $study = new StudyHandler($studyName);
    $variables = $study->getVariables();

    return $response->write(json_encode($variables, JSON_PRETTY_PRINT));
});
