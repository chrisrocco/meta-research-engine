<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 12:01 PM
 */

namespace Models\Edges;


use Models\Core\EdgeModel;

class Assignment extends EdgeModel
{
    static $collection = 'assignments';

    private static $blank = [
        'done'          =>  false,
        'completion'    =>  0,
        'encoding'      =>  null
    ];

    /**
     * @param $paper Paper
     * @param $user  User
     * @return Assignment
     */
    public static function assign( $paper, $user ){
        return static::create(
            $user->id(), $paper->id(),
            static::$blank
        );
    }
}