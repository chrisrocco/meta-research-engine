<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 7/11/2017
 * Time: 2:26 PM
 */

namespace uab\mre\lib;


use Exception;

class DuplicateIdException extends \Exception
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
        parent::__construct("Tried to add a duplicate id $id");
    }
}