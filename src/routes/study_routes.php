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

    //Check if the research study exists
    if (!$this->arangodb_documentHandler->has('research_studies', $studyName)) {
        return $response->write("No research study with name " . $studyName . " found.")
            ->withStatus(400);
    }

    //The study exists, return the structure
    $statement = new ArangoStatement($this->arangodb_connection, [
        'query' => "FOR domain IN INBOUND CONCAT (\"research_studies/\", @studyName) subdomain_of //For each top-level domain
   
                   //assemble the domain's fields
                    LET fields = (
                        FOR field IN INBOUND domain variable_of
                        RETURN field
                    )
                    
                    //assemble the domain's subdomains
                    LET subDomains = (
                        FOR subDomain IN INBOUND domain subdomain_of
                            //assemble the subDomain's fields
                            LET subDomainFields = (
                                FOR subDomainField IN INBOUND subDomain subdomain_of
                                RETURN subDomainField
                            )
                            
                            //Returns what will be a child node in the HTML DOM tree
                            RETURN MERGE (subDomain, {
                                \"fields\": subDomainFields,
                                \"subdomains\": []
                                }
                            )
                    )
                    
                    //Sort alphabetically
                    SORT domain.name
                    
                    //Returns what will be a node in the HTML DOM tree with ONE level of its children
                    RETURN MERGE(domain, {
                        \"fields\": fields,
                        \"subdomains\": subDomains
                    })",
        'bindVars' => [
            'studyName' => $studyName
        ],
        '_flat' => true
    ]);

    $res = $statement->execute()->getAll();


    return $response->write(json_encode($res, JSON_PRETTY_PRINT));

});

/**
 * GET studies/{studyname}/variables
 * Summary: Gets a list of every field's name
 */
$app->GET("/studies/{studyname}/variables", function ($request, $response, $args) {
    $studyName = $args['studyname'];

    //Check if the research study exists
    if (!$this->arangodb_documentHandler->has('research_studies', $studyName)) {
        return $response->write("No research study with name " . $studyName . " found.")
            ->withStatus(400);
    }

    //The study exists, run the query
    $statement = new ArangoStatement($this->arangodb_connection, [
        'query' => 'FOR var IN INBOUND CONCAT("research_studies/", @studyName) variable_of
                        SORT var._key
                        RETURN var._key',
        'bindVars' => [
            'studyName' => $studyName
        ],
        '_flat' => true
    ]);

    $resultSet = $statement->execute()->getAll();

    return $response->write(json_encode($resultSet, JSON_PRETTY_PRINT));


});
