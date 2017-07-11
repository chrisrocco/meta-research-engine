<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 4:05 PM
 */

namespace uab\MRE\dao;


use vector\ArangoORM\Models\Core\VertexModel;

class Variable extends VertexModel
{
    static $collection = 'variables';

    public static function createFromRaw ($rawDomain) {
        $prospect = [];
        foreach ($rawDomain as $key=>$value) {
            if (in_array($key, self::ignored_raw_keys)) {
                continue;
            }
            $prospect[$key] = $value;
        }
        return Variable::create($prospect);
    }

    const ignored_raw_keys = [
        '_key',
        '_id',
        '_rev',
        'date_created',
    ];
}