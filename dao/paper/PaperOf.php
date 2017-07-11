<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 3:57 PM
 */

namespace uab\MRE\dao;

use vector\ArangoORM\Models\Core\EdgeModel;

class PaperOf extends EdgeModel
{
    static $collection = "paper_of";
}