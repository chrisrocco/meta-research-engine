<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:59 PM
 */

namespace uab\MRE\dao;

use triagens\ArangoDb\Exception;
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

    /**
     * @return Assignment[]
     */
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

    public function getMasterEncoding () {
        try {
            return $this->get('masterEncoding');
        } catch (\OutOfBoundsException $e) {
            return [];
        }
    }

    public static function updateStatusByKey ($paperKey) {
        $paper = Paper::retrieve($paperKey);
        if (!$paper) {
            return false;
        }
        return $paper->updateStatus();
    }

    public function updateStatus () {
        try {
            $masterEncoding = $this->getMasterEncoding();
            $conflicted = RoccoMasterEncoding::conflictedStatus($masterEncoding);

            if ($conflicted) {
                return $this->setStatus("conflicted");
            }
        } catch (Exception $e) {}

        $assignments = $this->getAssignments();
        $assignmentCount = count($assignments);
        if ($assignmentCount === 0) {
            return $this->setStatus("pending");
        }

        foreach ($assignments as $assignment) {
            if (!$assignment->isDone()) {
                return $this->setStatus("active");
            }
        }

        $project = $this->getProject();
        if ($project && $assignmentCount < $project->getPaperAssignmentTarget()) {
            return $this->setStatus("clean");
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

    public function setStatus($status) {
        $this->update('status', $status);
        return $status;
    }
}

