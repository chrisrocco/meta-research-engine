<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/17/2017
 * Time: 2:30 PM
 */

namespace uab\MRE\dao;

use vector\ArangoORM\DB\DB;

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
                priority = TO_NUMBER(paperOf.priority)
                
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
     * @param $excludeUser User
     * @return Paper[]
     */
    public function nextPapers ($numPapers = 1, $excludeUser = null) {
        if( is_numeric( $numPapers ) ) $numPapers = intval( $numPapers );
        $aql = 'LET project = DOCUMENT ( @projectID )
            FOR pap, paperOf IN INBOUND project._id @@paper_to_project
                LET assignments = (
                    FOR user, assignment IN OUTBOUND pap._id @@paper_to_user
                        //FILTER project.version == assignment.projectVersion
                        RETURN user._id == @excludeUserID
                )
            COLLECT
                paper = pap,
                assignmentCount = COUNT (assignments),
                priority = TO_NUMBER(paperOf.priority),
                exclude = assignments ANY == true
                
            FILTER ((priority == 0 && assignmentCount < project.assignmentTarget) || priority > 0) && !exclude
            SORT priority DESC, assignmentCount DESC
            LIMIT @queueLimit
            RETURN paper';
        $bindVars = [
            'projectID' => $this->project->id(),
            'excludeUserID' => ($excludeUser instanceof User)? $excludeUser->id() : "dummyID",
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