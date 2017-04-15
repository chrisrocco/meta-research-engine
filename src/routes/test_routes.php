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

    $assignmentKey = $request->getParam("assignmentKey");
    $conflictManager = new ConflictManager($this->arangodb_connection, "BigDataUAB");

    $statement = new ArangoStatement($this->arangodb_connection,
        [
            "query" => 'FOR paper IN OUTBOUND @assignment assignment_of
                            FOR assignment IN INBOUND paper assignment_of
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