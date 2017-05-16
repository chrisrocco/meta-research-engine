<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 12:01 PM
 */

namespace Models\Edges;


use DB\DB;
use Models\Core\EdgeModel;
use Models\Vertices\Paper;
use Models\Vertices\Study;
use Models\Vertices\User;

class Assignment extends EdgeModel
{
    static $collection = 'assignments';

    public static $blank = [
        'done'          =>  false,
        'completion'    =>  0,
        'encoding'      =>  [
            'constants' => [],
            'branches' => [[]]
        ]
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

    // Project == Study
    public function getProject(){
        $AQL = "FOR project IN OUTBOUND @paperKey @@paper_to_study
                    RETURN project";
        $bindings = [
            'paperKey'  =>  $this->get( '_from' ),
            '@paper_to_study'   =>  PaperOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Study::class)[0];
    }
}