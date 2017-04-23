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
$app->POST("/conflictscan", function ($request, $response, $args) {
    global $connection;

    $assignmentKey = $request->getParam("assignmentKey");
    $conflictManager = new ConflictManager($connection, "BigDataUAB");

    $statement = new ArangoStatement($connection,
        [
            "query" => 'FOR paper IN OUTBOUND @assignment assignment_of
                            FOR assignment IN INBOUND paper assignment_of
                                FILTER assignment.completion != null
                                RETURN assignment',
            "bindVars" => [
                "assignment" => "assignments/".$assignmentKey
            ],
            "_flat" => true
        ]);
    $assignments_array = $statement->execute()->getAll();

    $conflicts = $conflictManager->generateConflictReport($assignments_array);
    return $response
        ->write(json_encode($conflicts, JSON_PRETTY_PRINT));
});

$app->GET("/queries", function ($req, $res){
    $assignments = QueryBank::execute("getCollaborators", [ "assignment" => "assignments/608113" ]);

    echo json_encode($assignments, JSON_PRETTY_PRINT);
});