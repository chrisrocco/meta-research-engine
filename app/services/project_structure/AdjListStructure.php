<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 1:42 PM
 */

namespace uab\mre\app;


use uab\MRE\dao\Domain;
use uab\MRE\dao\Variable;
use uab\mre\lib\AdjList;
use uab\mre\lib\AdjNode;
use uab\mre\lib\BadNodeException;

/**
 * Class AdjListStructure
 * @package uab\mre\app
 *
 * Garuntees an adjacency list of nodes with the following criteria:
 *  1.) No duplicate ID's
 *  2.) Collection type of Domain::$collection || Variable::$collection
 *  3.) Valid parent reference to another node, or is a root node
 */
class AdjListStructure extends AdjList
{
    public function addNode(AdjNode $adj_node) {
        $col = $adj_node->getCollection();
        if(!($col === Domain::$collection || $col === Variable::$collection)){
            throw new BadNodeException( $col );
        }
        parent::addNode($adj_node);
    }
}