<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 1:40 PM
 */

namespace uab\mre\app;


use triagens\ArangoDb\Document;
use triagens\ArangoDb\Exception;
use uab\MRE\dao\Domain;
use uab\MRE\dao\Project;
use uab\MRE\dao\SubdomainOf;
use uab\MRE\dao\Variable;
use uab\MRE\dao\VariableOf;
use uab\mre\lib\AdjList;
use uab\mre\lib\AdjNode;
use vector\ArangoORM\DB\DB;

class StructureService
{
    public static function replaceStructure( Project $project, AdjListStructure $adjList ){
        $adjList->validateParents();
        $project->removeStructure(5);

        $dh = DB::getDocumentHandler();
        foreach ( $adjList->getNodes() as $adjNode ){
            $doc = Document::createFromArray( $adjNode->getData() );
            $doc->setInternalKey($adjNode->getId());
            $dh->store( $adjNode->getCollection(), $doc );
        }
        foreach ( $adjList->getNodes() as $adjNode ){
            $col = $adjNode->getCollection();
            $parent = $adjNode->getParentId();
            if( $col == Domain::$collection ){
                $id = Domain::$collection."/".$adjNode->getId();
                if( $parent === AdjNode::ROOT ){
                    $parent = $project->id();
                } else {
                    $parent = Domain::$collection."/".$parent;
                }
                DB::createEdge( SubdomainOf::$collection, $id, $parent, []);
            }
            if( $col == Variable::$collection ){
                $id = Variable::$collection."/".$adjNode->getId();
                $parent = Domain::$collection."/".$parent;
                DB::createEdge( VariableOf::$collection, $id, $parent, []);
            }
        }
    }
    public static function getStructureAdj( Project $project ){
        $AQL = "
            LET domains = (
                FOR domain, edge IN 1..6 INBOUND @project @@domain_to_domain
                    RETURN MERGE( {parent:edge._to}, domain )
            )
            LET questions = (
                FOR domain IN 1..6 INBOUND @project @@domain_to_domain
                    FOR question, edge IN INBOUND domain @@question_to_domain
                        RETURN MERGE( {parent:edge._to}, question )
            )
            RETURN {
                domains: domains,
                questions: questions
            }
        ";
        $bindings = [
            "project"   =>  $project->id(),
            "@domain_to_domain" => SubdomainOf::$collection,
            "@question_to_domain"   =>  VariableOf::$collection
        ];
        try {
            return DB::queryFirst( $AQL, $bindings );
        } catch ( Exception $e ){
            return false;
        }
    }
    public static function getStructureNested( Project $project ){
        $domains = [];
        foreach( self::getTopLevelDomains( $project ) as $subdomain ){
            $domains[] = self::recursiveGetDomain( $subdomain );
        }
        return $domains;
    }

    private function getTopLevelDomains( Project $project ){
        $AQL = "FOR domain in INBOUND @root @@domain_to_domain
                SORT domain.name
                RETURN domain";
        $bindings = [
            "root" => $project->id(),
            "@domain_to_domain" => SubdomainOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Domain::class);
    }
    private function recursiveGetDomain( $domain ){
        $variables  = $domain->getVariables();
        $subdomains = [];
        foreach ( $domain->getSubdomains() as $subdomain) {
            $subdomains[] = self::recursiveGetDomain($subdomain);
        }
        $flat_vars = [];
        foreach ($variables as $var ){
            $flat_vars[] = $var->toArray();
        }
        $d = $domain->toArray();
        $v = [
            'variables'     =>  $flat_vars,
            'subdomains'    =>  $subdomains
        ];
        return array_merge( $d, $v );
    }
}