<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 11:43 AM
 */

namespace uab\mre\lib;


class AdjList implements \JsonSerializable
{
    /**
     * @var AdjNode[]
     */
    private $nodes = [];

    public function addNode( AdjNode $adj_node ){
        if( isset(
            $this->nodes[ $adj_node->getId() ]
        )) throw new DuplicateIdException( $adj_node->getId() );

        $this->nodes[$adj_node->getId()] = $adj_node;
    }

    /**
     * @return AdjNode[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    public function validateParents(){
        foreach ( $this->nodes as $node ){
            $parent = $node->getParentId();
            if( $parent == AdjNode::ROOT ) continue;
            if( !isset(
                $this->nodes[$parent]
            )) throw new NoParentException( $node, $parent );
        }
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return $this->nodes;
    }
}