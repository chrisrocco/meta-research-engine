<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/16/2017
 * Time: 10:07 PM
 */

namespace Models\Vertices;


use vector\ArangoORM\Models\Core\VertexModel;

class SerializedProjectStructure extends VertexModel {
    static $collection = 'serialized_project_structures';
}