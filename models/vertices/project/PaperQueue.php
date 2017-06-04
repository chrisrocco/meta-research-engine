<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/17/2017
 * Time: 2:30 PM
 */

namespace Models\Vertices\Project;

use vector\ArangoORM\DB\DB;
use Models\Edges\Assignment\Assignment;
use Models\Edges\PaperOf;
use Models\Vertices\Paper\Paper;


class PaperQueue {


    /**
     * Note: the "paper" attribute is a Paper object that has had getAll() executed.
     *  {
     *      "paper" : array,
     *      "assignmentCount" : int,
     *      "priority" : int
     *  }
     * @return array
     */
    public function getQueueRaw () {
        $aql = 'LET project = DOCUMENT ( @projectID )
            FOR pap, paperOf IN INBOUND project._id @@paper_to_project
                LET assignments = (
                    FOR user, assignment IN OUTBOUND pap._id @@paper_to_user
                        //FILTER project.version == assignment.projectVersion
                        RETURN assignment
                )
            COLLECT
                paper = pap,
                assignmentCount = COUNT (assignments),
                priority = paperOf.priority
                
            FILTER (priority == 0 && assignmentCount < project.assignmentTarget) || priority > 0
            SORT priority DESC, assignmentCount DESC
            RETURN {"paper" : paper, "assignmentCount" : assignmentCount, "priority" : priority}';
        $bindVars = [
            'projectID' => $this->project->id(),
            '@paper_to_project' => PaperOf::$collection,
            '@paper_to_user' => Assignment::$collection
        ];
        return DB::query($aql, $bindVars, true)->getAll();
    }

    /**
     * @param int $numPapers
     * @return Paper[]
     */
    public function nextPapers ($numPapers = 1) {
        $aql = 'LET project = DOCUMENT ( @projectID )
            FOR pap, paperOf IN INBOUND project._id @@paper_to_project
                LET assignments = (
                    FOR user, assignment IN OUTBOUND pap._id @@paper_to_user
                        //FILTER project.version == assignment.projectVersion
                        RETURN assignment
                )
            COLLECT
                paper = pap,
                assignmentCount = COUNT (assignments),
                priority = paperOf.priority
                
            FILTER (priority == 0 && assignmentCount < project.assignmentTarget) || priority > 0
            SORT priority DESC, assignmentCount DESC
            LIMIT @queueLimit
            RETURN paper';
        $bindVars = [
            'projectID' => $this->project->id(),
            'queueLimit' => $numPapers,
            '@paper_to_project' => PaperOf::$collection,
            '@paper_to_user' => Assignment::$collection
        ];
        return DB::queryModel($aql, $bindVars, Paper::class);
    }


    /**
     * @param $paper Paper
     */
    public function decrementPriority ($paper) {
        $priority = $paper->getPriority($this->project);
        $newPriority = $priority > 0 ? $priority - 1 : $priority;
        $paper->updatePriority($this->project, $newPriority);
    }


    private $project;

    /**
     * @param $project Project
     */
    public function __construct($project) {
        $this->project = $project;
    }
}