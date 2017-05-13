<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:59 PM
 */

namespace Models\Vertices;


use DB\DB;
use Models\Core\VertexModel;
use Models\Edges\PaperOf;

class Paper extends VertexModel {
    static $collection = 'papers';

    public function getStudy(){
        $AQL = "FOR study IN ANY @paper @@paper_to_study
                    LIMIT 1
                    RETURN study";
        $bindings = [
            'paper'    =>  $this->id(),
            '@paper_to_study'  =>  PaperOf::$collection,
        ];
        $arr = DB::queryModel($AQL, $bindings, Study::class);
        $study = $arr[0];
        return $study;
    }
}