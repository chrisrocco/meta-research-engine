<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/5/2017
 * Time: 6:52 PM
 */

namespace Papers;


class MasterEncoding
{
    /**
     * @param $assignment Assignment
     */
    public function merge($assignment) {
        if (!is_a($assignment, Assignment::class)) {
            try {
                $assignment = new Assignment($assignment);
            } catch (Exception $e) {
                //TODO
                return;
            }
        }

        $encoding = $assignment->getEncoding();

        $structureResponse = $encoding->getStructureResponse();
        $this->mergeResponse($this->structureResponses, $structureResponse);

        $scopeResponses = $encoding->getScopeResponses();
        foreach ($scopeResponses as $scopeResponse) {
            $this->mergeResponse($this->scopeResponses, $scopeResponse);
        }

        $valueResponses = $encoding->getValueResponses();
        foreach ($valueResponses as $valueResponse) {
            $this->mergeResponse($this->valueResponses, $valueResponse);
        }

    }

    /**
     * @param $master Response[]
     * @param $remote Response
     */
    private function mergeResponse (&$masterArr, $remote) {
        $remoteID = $remote->getUsers()[0];
        foreach ($masterArr as $master) {
            if ($master->getContent() === $remote->getContent()) {
                if (!$master->hasUser($remoteID)) {
                    $master->addUser($remoteID);
                }
                //Successfully merged
                return;
            }
        }
        //We have a response that hasn't been recorded before
        array_push($masterArr, $remote);
    }


    private $paperID;
    private $structureResponses;
    private $scopeResponses;
    private $valueResponses;

    public function __construct($paperID){
        $this->paperID = $paperID;
        $this->structureResponses = [];
        $this->scopeResponses = [];
        $this->valueResponses = [];
    }
}