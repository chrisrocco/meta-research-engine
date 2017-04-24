<?php

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/10/2017
 * Time: 8:06 PM
 */
class QueryBank {
    public static function execute($name, $params){
        global $connection;
        $statement = new ArangoDBClient\Statement(
            $connection, [
                'query' => QueryBank::$queries[$name],
                'bindVars' => $params,
                '_flat' => true
            ]
        );
        return $statement->execute()->getAll();
    }

    function __call($name, $arguments) {
        $bindVars = $arguments[0];
        return QueryBank::query($name, $bindVars);
    }

    private static $queries = [
        "getCollaborators" => "FOR paper IN OUTBOUND @assignment assignment_of FOR assignment IN INBOUND paper assignment_of RETURN assignment",
        "getVariables" => "FOR var IN INBOUND CONCAT('research_studies/', @studyName) models SORT var._key RETURN var._key",
        "getStudyStructure" => "FOR domain IN INBOUND CONCAT (\"research_studies/\", @studyName) subdomain_of //For each top-level domain
   
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
        "getAssignmentsByStudent" => 'FOR assignment IN INBOUND CONCAT("users/", @userID) assigned_to
                                    FOR paper IN OUTBOUND assignment._id assignment_of
                                        RETURN MERGE(
                                            UNSET(assignment, "encoding"),
                                            {title: paper.title, pmcID: paper._key}
                                        )',
        "getBlankEncoding" => 'LET constants = (
                                FOR field IN INBOUND @studyName models
                                    RETURN {
                                        "field" : field._key,
                                        "content" : {value : ""}
                                    }
                            )
                            RETURN {
                                "constants" : constants,
                               "branches" : [[]]
                            }',
        "assignmentExistCount" => 'FOR assignment IN INBOUND CONCAT("users/", @userID) assigned_to
                                FOR paper IN OUTBOUND assignment._id assignment_of
                                    FILTER paper._key == @pmcID
                                    RETURN 1',
        "getAssignmentByID" => 'LET assignment = DOCUMENT( CONCAT ("assignments/", @assignmentID) )
                                FOR paper IN OUTBOUND assignment._id assignment_of
                                    RETURN MERGE( UNSET (assignment, "_id", "_rev"), {title: paper.title, pmcID: paper._key})'
    ];
}