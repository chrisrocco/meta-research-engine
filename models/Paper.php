<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:59 PM
 */

namespace Models;


use DB\QueryBank;
use Models\Core\VertexModel;

class Paper extends VertexModel
{
    static $collection = 'papers';
}