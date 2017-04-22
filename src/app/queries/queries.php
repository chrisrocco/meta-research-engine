<?php
return [
    "getCollaborators" => "FOR paper IN OUTBOUND @assignment assignment_of FOR assignment IN INBOUND paper assignment_of RETURN assignment",
    "getVariables" => "FOR var IN INBOUND CONCAT('research_studies/', @studyName) models SORT var._key RETURN var._key",
    "getStudyStructure" => "FOR domain IN INBOUND CONCAT ('research_studies/', @studyName) subdomain_of LET fields = ( FOR field IN INBOUND domain variable_of RETURN field ) LET subDomains = ( FOR subDomain IN INBOUND domain subdomain_of LET subDomainFields = ( FOR subDomainField IN INBOUND subDomain subdomain_of RETURN subDomainField ) RETURN MERGE (subDomain, { 'fields': subDomainFields, 'subdomains': [] } ) )  RETURN MERGE(domain, { 'fields': fields, 'subdomains': subDomains })",
    "getAssignmentsByStudent" => 'FOR assignment IN INBOUND CONCAT("users/", @userID) assigned_to
                                    FOR paper IN OUTBOUND assignment._id assignment_of
                                        RETURN MERGE(assignment, {title: paper.title, pmcID: paper._key})',

];