<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 6/4/2017
 * Time: 2:06 AM
 */

namespace Models\Edges\Assignment;


use Models\Vertices\Project\Project;
use Models\Vertices\User;
use Models\Vertices\Paper\Paper;
use Models\Vertices\Project\PaperQueue;

class AssignmentManager
{
    /**
     * @param $project Project
     * @param $user User
     * @param $assignmentCap int
     * @return Paper[]
     */
    public static function assignUpTo ($project, $user, $assignmentCap) {
        $queue = new PaperQueue($project);
        $papers = $queue->nextPapers($assignmentCap);
        foreach ($papers as $paper) {
            Assignment::assign($paper, $user);
            $queue->decrementPriority($paper);
        }
        return $papers;
    }


}