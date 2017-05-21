<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:59 PM
 */

namespace Models\Vertices\Paper;


use DB\DB;
use Models\Core\BaseModel;
use Models\Core\VertexModel;
use Models\Edges\Assignment;
use Models\Edges\PaperOf;
use Models\Vertices\Project\Project;
use Models\Vertices\User;
use triagens\ArangoDb\Document;
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

    public function getAssignments() {
        return DB::queryModel(
            'FOR user, ass IN OUTBOUND @paperID @@assignments
                        RETURN ass',
            [
                'paperID' => $this->id(),
                '@assignments' => Assignment::$collection
            ],
            Assignment::class
        );
    }

    /**
     * @param $assignment Assignment
     */
    public function roccoMerge( $assignment ){
        $masterEncodingObject = $this->get('masterEncoding');
        $assignmentObject = $assignment->toArray();
        $mergeLog = RoccoMasterEncoding::merge( $assignmentObject, $masterEncodingObject );
        $this->update( 'masterEncoding', $masterEncodingObject );

        $arango_doc = Document::createFromArray( $mergeLog );
        DB::create( "merge_logs", $arango_doc );
    }

    public function getCollaborators(){
        $AQL = "FOR user, assignment IN OUTBOUND @paper @@paper_to_user
                    RETURN user";
        $bindings = [
            "paper"             =>  $this->id(),
            "@paper_to_user"    =>  Assignment::$collection
        ];
        $users = DB::queryModel($AQL, $bindings, User::class);
        return $users;
    }

    public function updateStatus () {
        $masterEncoding = $this->get('masterEncoding');
        $conflicted = RoccoMasterEncoding::conflictedStatus($masterEncoding);

        if ($conflicted) {
            $status = "conflicted";
            $this->update('status', $status);
            return $status;
        }


        $assignments = $this->getAssignments();
        $assignmentCount = count($assignments);
        if (count($assignments) === 0) {
            $status = "pending";
            $this->update('status', $status);
            return $status;
        }

        foreach ($assignments as $assignment) {
            if ($assignment->get('done') == false) {
                $status = "active";
                $this->update('status', $status);
                return $status;
            }
        }

            //no new features
//        if ($assignmentCount < $this->getProject()->get('assignmentTarget')) {
//            $status = "complete";
//            $this->update('status', $status);
//            return $status;
//        }

        $status = "clean";
        $this->update('status', $status);
        return $status;
    }

}

