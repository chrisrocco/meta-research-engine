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

class SchemaValidatorException extends \Exception
{
    public $required;
    public $missing;
    public $from;

    public function __construct( $required, $missing, $from_obj )
    {
        $this->required = $required;
        $this->missing = $missing;
        $this->from = $from_obj;

        parent::__construct("required property '$missing' missing from schema: [".implode($required, ",")."]");
    }
}