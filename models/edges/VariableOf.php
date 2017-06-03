<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:57 PM
 */

namespace Models\Edges;


use vector\ArangoORM\Models\Core\EdgeModel;

class VariableOf extends EdgeModel
{
    static $collection = "variable_of";
}