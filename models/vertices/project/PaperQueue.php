<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/17/2017
 * Time: 2:30 PM
 */

namespace Models\Vertices\Project;

use DB\DB;
use Models\Edges\Assignment;
use Models\Edges\PaperOf;
use Models\Vertices\Paper;


class PaperQueue {


    public function getQueueRaw () {
        $queue = DB::query('
            LET project = DOCUMENT ( @projectID )
            FOR paper, paperOf IN INBOUND project._id @@paper_to_project
                LET assignments = (
                    FOR user, assignment IN OUTBOUND paper._id @@paper_to_user
                        FILTER project.version == assignment.projectVersion
                        RETURN assignment
                )
            COLLECT
                paperKey = paper._key,
                paperTitle = paper.title,
                pmcID = paper.pmcID,
                assignmentCount = COUNT (assignments)
                ,priority = paperOf.priority
                
            FILTER (priority == 0 && assignmentCount <= project.assignmentTarget) || priority > 0
            SORT priority DESC, assignmentCount DESC
            RETURN {
                "paperKey" : paperKey,
                "pmcID" : pmcID,
                "assignments" : assignmentCount,
                "priority" : priority,
                "paperTitle" : paperTitle
            }
            ', [
                'projectID' => $this->project->id(),
                '@paper_to_project' => PaperOf::$collection,
                '@paper_to_user' => Assignment::$collection
            ], true
        )->getAll();
        return $queue;
    }

    /**
     * Gets the next queueItem in the PaperQueue. Returns false if the queue is empty
     * @return mixed
     */
    public function next ($queueLimit = 1) {
        $result = DB::query('
            LET project = DOCUMENT ( @projectID )
            FOR paper, paperOf IN INBOUND project._id @@paper_to_project
                LET assignments = (
                    FOR user, assignment IN OUTBOUND paper._id @@paper_to_user
                        FILTER project.version == assignment.projectVersion
                        RETURN assignment
                )
            COLLECT
                paperKey = paper._key,
                paperTitle = paper.title,
                pmcID = paper.pmcID,
                assignmentCount = COUNT (assignments)
                ,priority = paperOf.priority
                
            FILTER (priority == 0 && assignmentCount <= project.assignmentTarget) || priority > 0
            SORT priority DESC, assignmentCount DESC
            LIMIT @queueLimit
            RETURN {
                "paperKey" : paperKey,
                "pmcID" : pmcID,
                "assignments" : assignmentCount,
                "priority" : priority,
                "paperTitle" : paperTitle
            }
            ', [
            'projectID' => $this->project->id(),
            'queueLimit' => $queueLimit,
            '@paper_to_project' => PaperOf::$collection,
            '@paper_to_user' => Assignment::$collection
        ], true
        )->getAll();
        if (count($result) === 0) {
            return false;
        }
        $dequeuedPapers = $result;
        foreach ($dequeuedPapers as $dequeuedPaper) {
            $this->updatePriority ($dequeuedPaper);
        }
        return $dequeuedPapers;
    }

    /**
     * @param $paperKey string
     * @param $newPriority int
     * @return \Models\Edges\PaperOf
     * @throws \Exception
     */
    public function changePriority ($paperKey, $newPriority ) {
        $example = [
            '_from' => Paper::$collection."/".$paperKey,
            '_to' => $this->project->id()
        ];
        $paperOfSet = PaperOf::getByExample($example);
        if (!$paperOfSet) {
            throw new \Exception(PaperOf::$collection . " edge not found : ".json_encode($example));
        }
        if (count($paperOfSet) > 1) {
            throw new \Exception("Multiple identical edges in ".PaperOf::$collection." ".json_encode($example));
        }
        $paperOfSet[0]->update('priority', $newPriority);
        return $paperOfSet[0];
    }

    public function updatePriority ($queueItem) {
        $priority = $queueItem['priority'];
        if ($priority > 0) {
            $this->changePriority($queueItem['paperKey'], $priority - 1);
        }
    }

    private $project;

    public function __construct($projectKey) {
        $project = Project::retrieve($projectKey);
        if (!$project) {
            throw new \Exception("Study not found");
        }
        $this->project = $project;
    }
}