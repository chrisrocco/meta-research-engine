<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:59 PM
 */

namespace Models\Vertices;


use DB\DB;
use Models\Core\BaseModel;
use Models\Core\VertexModel;
use Models\Edges\Assignment;
use Models\Edges\PaperOf;
use Models\Vertices\Project\Project;

use MasterEncoding\MasterEncoding;
use triagens\ArangoDb\Exception;

class Paper extends VertexModel {
    static $collection = 'papers';

    /**
     * @return \Models\Vertices\Project\Project
     */
    public function getProject() {
        $AQL = "FOR project IN OUTBOUND @paperKey @@paper_to_study
                    RETURN project";
        $bindings = [
            'paperKey' => $this->key(),
            '@paper_to_study' => PaperOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Project::class)[0];
    }

    /**
     * @param $assignment Assignment
     */
    public function merge ($assignment) {
        $masterEncoding = new MasterEncoding($this->get('masterEncoding'));
        $encoding = $assignment->get('encoding');
        if (!$encoding) { //Maybe this already happens if the attribute isn't found?
            throw new Exception("'encoding' attribute not found on ".$assignment->id());
        }
//        $userKey = BaseModel::idToKey($assignment->getTo());
        $userKey = $assignment->key();
        $masterEncoding->merge($encoding, $userKey);
        $this->update('masterEncoding', $masterEncoding->toStorage());
    }

    public function getReport ($conflictLevel) {

    }

    public static function blankMasterEncoding () {
        return MasterEncoding::BLANK;
    }

}

