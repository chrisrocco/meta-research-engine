<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 2:26 PM
 */

namespace uab\mre\lib;


use Exception;

class NoParentException extends \Exception
{
    public $node;
    public $parent_id;

    public function __construct($node, $parent_id)
    {
        $this->parent_id = $parent_id;
        $this->node = $node;
        parent::__construct("Node ".$node->getId()." claims parent $parent_id, which does not exist");
    }
}