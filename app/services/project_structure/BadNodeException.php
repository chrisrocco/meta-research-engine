<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 2:26 PM
 */

namespace uab\mre\lib;


use Exception;
use uab\MRE\dao\Domain;
use uab\MRE\dao\Variable;

class BadNodeException extends \Exception
{
    public $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;

        $opts = [
            Domain::$collection,
            Variable::$collection
        ];
        parent::__construct("Node collection must be one of " . $opts . ". $collection given.");
    }
}