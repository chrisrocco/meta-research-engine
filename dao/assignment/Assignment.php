<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 12:01 PM
 */

namespace uab\MRE\dao;

use vector\ArangoORM\DB\DB;
use vector\ArangoORM\Models\Core\BaseModel;
use vector\ArangoORM\Models\Core\EdgeModel;

class Assignment extends EdgeModel
{
    static $collection = 'assignments';

    public static $blank = [
        'done'          =>  false,
        'completion'    =>  0,
        'version'       => -1,
        'encoding'      =>  null
    ];

    /**
     * @param $paper Paper
     * @param $user  User
     * @return Assignment
     */
    public static function assign( $paper, $user ){
        return static::createEdge(
            $user,
            $paper,
            static::$blank
        );
    }

    // Project == Study
    public function getProject(){
        $AQL = "FOR project IN OUTBOUND @paperID @@paper_to_study
                    RETURN project";
        $bindings = [
            'paperID'  =>  $this->get( '_from' ),
            '@paper_to_study'   =>  PaperOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Project::class)[0];
    }

    /**
     * @return Paper
     */
    public function getPaper () {
        return Paper::retrieve( BaseModel::idToKey( $this->getFrom() ) );
    }

    public function isDone() {
        return !( $this->get('done') === false || $this->get('done') === 'false' );
    }
}