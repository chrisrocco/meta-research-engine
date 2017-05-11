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

//        $remoteValueResponses = $encoding->getValueResponses();
//        foreach ($remoteValueResponses as $valueResponse) {
//            $this->mergeResponse($this->valueResponses, $valueResponse);
//        }

        $valueResponses = $encoding->getValueResponses();
        foreach ($valueResponses as $remoteResponse) {
            $branch = $remoteResponse->getBranchIndex();
            $varID = (string)$remoteResponse->getVariableID();
            if (!isset($this->records[$branch])) {
                $this->records[$branch] = [$varID => []];
            }
            if (!isset($this->records[$branch][$varID]) ) {
                $this->records[$branch][$varID] = [];
            }
            $masterResponses = &$this->records[$branch][$varID];
            $this->mergeResponse($masterResponses, $remoteResponse);
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
    private $records;

    public function __construct($paperID, $records){
        $this->paperID = $paperID;
        $this->records = [];
    }

    //In order to selectively serialize properties
    public function jsonSerialize() {
        //Option 1
//        return $this->records;
        //Option 3
        $output = [];
        foreach ($this->records as $branch => $varRecords) {
            foreach ($varRecords as $varID => $record) {
                $output[] = [
                    'varID' => $varID,
                    'location' => $branch,
                    'responses' => $record
                ];
            }
        }
        return $output;
    }
}