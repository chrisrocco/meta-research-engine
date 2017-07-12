<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/15/2017
 * Time: 6:53 PM
 */

namespace uab\MRE\dao;

use vector\ArangoORM\Models\Core\EdgeModel;

class EnrolledIn extends EdgeModel{
    static $collection = "enrolled_in";
}