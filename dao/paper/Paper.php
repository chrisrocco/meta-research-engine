<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:59 PM
 */

namespace uab\MRE\dao;

use vector\ArangoORM\DB\DB;
use vector\ArangoORM\Models\Core\VertexModel;

class Paper extends VertexModel {
    static $collection = 'papers';

    /**
     * @return Project
     */
    public function getProject() {
        $AQL = "FOR project IN OUTBOUND @paperID @@paper_to_study
                    RETURN project";
        $bindings = [
            'paperID' => $this->id(),
            '@paper_to_study' => PaperOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Project::class)[0];
    }

    /**
     * @param $project Project
     */
    public function getPaperOf ($project) {
        $example = [
            '_from' => $this->id(),
            '_to' => $project->id()
        ];
        $paperOfSet = PaperOf::getByExample($example);
        if (!$paperOfSet) {
            throw new \Exception(PaperOf::$collection . " edge not found : ".json_encode($example));
        }
        if (count($paperOfSet) > 1) {
            throw new \Exception("Multiple identical edges in ".PaperOf::$collection." ".json_encode($example));
        }
        return $paperOfSet[0];
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
        RoccoMasterEncoding::merge( $assignmentObject, $masterEncodingObject );
        $this->update( 'masterEncoding', $masterEncodingObject );
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

    public static function updateStatusByKey ($paperKey) {
        $paper = Paper::retrieve($paperKey);
        if (!$paper) {
            return false;
        }
        return $paper->updateStatus();
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
        if ($assignmentCount === 0) {
            $status = "pending";
            $this->update('status', $status);
            return $status;
        }

        foreach ($assignments as $assignment) {
            if ($assignment->get('done') == false
                || $assignment->get('done') == 'false' ) {
                $status = "active";
                $this->update('status', $status);
                return $status;
            }
        }

        $project = $this->getProject();
        if ($project && $assignmentCount < $project->get('assignmentTarget')) {
            $status = "clean";
            $this->update('status', $status);
            return $status;
        }

        $status = "complete";
        $this->update('status', $status);
        return $status;

    }

    /**
     * @param $project Project
     * @param $newPriority int
     */
    public function updatePriority ($project, $newPriority) {
        $paperOf = $this->getPaperOf($project);
        $paperOf->update('priority', $newPriority);
    }

    /**
     * @param $project Project
     * @return int
     */
    public function getPriority ($project) {
        $paperOf = $this->getPaperOf($project);
        return intval($paperOf->get('priority'));
    }
}

