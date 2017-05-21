<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 12:01 PM
 */

namespace Models\Edges;


use DB\DB;
use Models\Core\BaseModel;
use Models\Core\EdgeModel;
use Models\Vertices\Paper\Paper;
use Models\Vertices\Project\Project;
use Models\Vertices\User;

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
        return static::create(
            $user->id(),
            $paper->id(),
            static::$blank
        );
    }

    public static function assignByKey ($paperKey, $userKey, $version) {
        $template = static::$blank;
        $template['version'] = $version;
       return static::create(
            User::$collection."/".$userKey,
            Paper::$collection."/".$paperKey,
            $template
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

    public function getPaper () {
        return Paper::retrieve( BaseModel::idToKey( $this->getFrom() ) );
    }
}