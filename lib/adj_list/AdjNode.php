<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 11:43 AM
 */

namespace uab\mre\lib;


class AdjNode implements \JsonSerializable
{
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }
    private $parent_id;
    private $data;
    private $collection;

    function __construct( $id, $parent_id, $collection, $data )
    {
        $this->id = $id;
        $this->parent_id = $parent_id;
        $this->data = $data;
        $this->collection = $collection;
    }

    const ROOT = "#";

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id'    =>  $this->getId(),
            'parent_id' =>  $this->getParentId(),
            'collection' =>  $this->getCollection(),
            'data'      =>  $this->getData()
        ];
    }
}