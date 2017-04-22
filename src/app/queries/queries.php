<?php
return [
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
                                        RETURN MERGE(assignment, {title: paper.title, pmcID: paper._key})',
];