<?php
namespace uab\MRE\dao;

use vector\ArangoORM\Models\Core\EdgeModel;

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 6/13/2017
 * Time: 10:00 PM
 */
class AdminOf extends EdgeModel{
    static $collection = "admin_of";
}