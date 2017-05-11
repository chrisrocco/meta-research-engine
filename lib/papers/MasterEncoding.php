<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/5/2017
 * Time: 6:52 PM
 */

namespace Papers;


class MasterEncoding implements \JsonSerializable {
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

//        $structureResponse = $encoding->getStructureResponse();
//        $this->mergeResponse($this->structureResponses, $structureResponse);

//        $scopeResponses = $encoding->getScopeResponses();
//        foreach ($scopeResponses as $scopeResponse) {
//            $this->mergeResponse($this->scopeResponses, $scopeResponse);
//        }

        $remoteValueResponses = $encoding->getValueResponses();
        foreach ($remoteValueResponses as $valueResponse) {
            $this->mergeResponse($this->valueResponses, $valueResponse);
        }

    }

    /**
     * @param $masterArr Response[]
     * @param $remote Response
     */
    private function mergeResponse (&$masterArr, $remote) {
        $remoteID = $remote->getUsers()[0];
        foreach ($masterArr as $master) {
            //if our response is the same as a previously-recorded response
            if ($master->getContent() === $remote->getContent()) {
                //if our response doesn't already have us listed
                if (!$master->hasUser($remoteID)) {
                    //add us to the response
                    $master->addUser($remoteID);
                }
                //Otherwise our response already includes us, so everything is good.
                //at this point, we are certainly successfully merged, so we can return
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

    //A workaround in order to serialize private properties
    public function jsonSerialize() {
        $records = [];
        foreach ($this->valueResponses as $valueResponse) {
//            $records[$valueResponse->getBranchIndex()][(string) $valueResponse->getVariableID()][] = $valueResponse;
            $records[] = [
                'varID' => $valueResponse->getVariableID(),
                'location' => $valueResponse->getBranchIndex(),
                'responses' => $valueResponse
            ];
        }
        return $records;
    }
}