<?php
// set up some aliases for less typing later
use ArangoDBClient\Collection as ArangoCollection;
use ArangoDBClient\CollectionHandler as ArangoCollectionHandler;
use ArangoDBClient\Connection as ArangoConnection;
use ArangoDBClient\ConnectionOptions as ArangoConnectionOptions;
use ArangoDBClient\DocumentHandler as ArangoDocumentHandler;
use ArangoDBClient\EdgeHandler as ArangoEdgeHandler;
use ArangoDBClient\Document as ArangoDocument;
use ArangoDBClient\Edge as ArangoEdge;
use ArangoDBClient\Exception as ArangoException;
use ArangoDBClient\Export as ArangoExport;
use ArangoDBClient\ConnectException as ArangoConnectException;
use ArangoDBClient\ClientException as ArangoClientException;
use ArangoDBClient\ServerException as ArangoServerException;
use ArangoDBClient\Statement as ArangoStatement;
use ArangoDBClient\UpdatePolicy as ArangoUpdatePolicy;

// Routes

/*
 * GET studies/{studyname}/structure
 * Summary: Gets the domain / field structure of the specified research study
 */
$app->GET("/studies/{studyname}/structure", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    global $documentHandler;
    global $connection;

    //Check if the research study exists
    if (!$documentHandler->has('research_studies', $studyName)) {
        return $response->write("No research study with name " . $studyName . " found.")
            ->withStatus(400);
    }

    $res = QueryBank::execute("getStudyStructure", [
        "studyName" => "BigDataUAB"
    ]);

    return $response->write(json_encode($res, JSON_PRETTY_PRINT));

});

/**
 * GET studies/{studyname}/variables
 * Summary: Gets a list of every field's name
 */
$app->GET("/studies/{studyname}/variables", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    global $documentHandler;

    //Check if the research study exists
    if (!$documentHandler->has('research_studies', $studyName)) {
        return $response->write("No research study with name " . $studyName . " found.")
            ->withStatus(400);
    }

    $resultSet = QueryBank::execute("getVariables", ["studyName" => "BigDataUAB"]);

    return $response->write(json_encode($resultSet, JSON_PRETTY_PRINT));
});
