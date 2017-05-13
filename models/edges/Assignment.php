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
        'encoding'      =>  []
    ];

    /**
     * @param $paper Paper
     * @param $user  User
     * @return Assignment
     */
    public static function assign( $paper, $user ){
        $paperStudy = $paper->getStudy();
        $studyVariablesArray = $paperStudy->getVariablesFlat();

        if( $paperStudy == null ) throw new \Exception("No study associated with that paper");

        $newEncoding = [
            "constants" => [],
            "branches" => [[]]
        ];
        foreach ( $studyVariablesArray as $variable ){
            $newEncoding['constants'][] = [
                "field" =>  $variable['_key'],
                "content" => []
            ];
        }

        $assignment = static::$blank;
        $assignment['encoding'] = $newEncoding;

        var_dump( $assignment );

        return static::create(
            $user->id(), $paper->id(),
            $assignment
        );
    }
}