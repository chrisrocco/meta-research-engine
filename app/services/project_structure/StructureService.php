<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 1:40 PM
 */

namespace uab\mre\app;


use triagens\ArangoDb\Document;
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
            $dh->save( $adjNode->getCollection(), $doc );
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
}