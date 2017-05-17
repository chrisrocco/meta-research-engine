<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:59 PM
 */

namespace Models\Vertices;


use Models\Core\VertexModel;
use Models\Edges\Assignment;

class Paper extends VertexModel {
    static $collection = 'papers';

    /**
     * @param $assignment Assignment
     */
    public function merge ($assignment) {
        $this->masterEncoding = $this->get('masterEncoding');
        $valueResponses = self::getValueRecords($assignment);
        foreach ($valueResponses as $remoteRecord) {
            $masterRecord = $this->getRecordByLocation($remoteRecord['question'], $remoteRecord['location']);
            if (!$masterRecord) {
                //We have a new record
                $this->addRecord($remoteRecord);
            } else {
                $this->updateRecord(self::mergeRecord($masterRecord, $remoteRecord['responses'][0]));
            }
        }
        $this->update('masterEncoding', $this->masterEncoding);
    }

    public $masterEncoding = [];

    public function getConflicts () {

    }

    private function getStructure ($user) {

    }

    private function getScopes ($user) {

    }

    private function getValues ($user) {

    }

    private function getRecordByLocation ($questionKey, $location) {
        for ($i = 0; $i < count($this->masterEncoding); $i++) {
            if ($this->masterEncoding[$i]['question'] === $questionKey
                && $this->masterEncoding[$i]['location'] === $location
            ) {
                $record = &$this->masterEncoding[$i];
                return $record;
            }
        }
        return false;
    }

    private function updateRecord ($record) {
        for ($i = 0; $i < count($this->masterEncoding); $i++) {
            if ($this->masterEncoding[$i]['question'] === $record['question']
                && $this->masterEncoding[$i]['location'] === $record['location']
            ) {
//                echo "\n\t".json_encode($record);
                $this->masterEncoding[$i]['responses'] = $record['responses'];
                return;
            }
        }
        $this->addRecord($record);
    }

    private function addRecord ($record) {
        $this->masterEncoding [] = $record;
    }

    private function getUsers () {

    }

    /**
     * @param $assignment Assignment
     */
    private static function getValueRecords ($assignment) {
        $assID = -1;
        $encoding = [];
        if (!is_a($assignment, Assignment::class)) {
            $assID = $assignment['_key'];
            $encoding = $assignment['encoding'];
        } else {
            $assID = $assignment->key();
            $encoding = $assignment->get('encoding');
        }
        $responses = [];
        if (!self::validateEncoding($encoding)) {
            return $responses;
        }
        foreach ($encoding['constants'] as $response) {
            $responses[] = self::createRecord($response['question'], 0, $response['data'], $assID);
        }
        foreach ($encoding['branches'] as $branchIndex => $branch) {
            foreach ($branch as $response) {
                self::createRecord($response['question'], $branchIndex + 1, $response['data'], $assID);
            }
        }
        return $responses;
    }


    /**
     * @param $masterArr array of responses to the same question in the same location
     * @param $remote record to merge
     */
    private static function mergeRecord (&$masterRecord, $remoteResponse) {
        $masterResponses = $masterRecord['responses'];
        $remoteUserID = $remoteResponse['users'][0];

        foreach ($masterResponses as $i => &$masterResponse) {
            //if the response already has us listed
            if(in_array($remoteUserID, $masterResponse['users'])) {
                //if our response is not the same
                if ($masterResponse['data'] != $remoteResponse['data']) {
                    //remove us from the response
                    $masterResponse = self::response_removeUser ($masterResponse, $remoteUserID);
                } else { //our response is the same
                    //return
                    $masterRecord['responses'] = $masterResponses;
                    return $masterRecord;
                }
            } else { //we are not listed
                //if our response is the same
                if ($masterResponse['data'] == $remoteResponse['data']) {
                    //add us to the response
                    $masterResponse = self::response_addUser($masterResponse, $remoteUserID);
                    $masterResponses[$i] = $masterResponse;
                }
            }
        }
        //We have a new response
        $masterResponses[] = $remoteResponse;
        //We're good
        $masterRecord['responses'] = $masterResponses;
        return $masterRecord;
    }

    private static function response_addUser (&$response, $userKey) {
        $response['users'][] = $userKey;
        return $response;
    }

    private static function response_removeUser (&$response, $userKey) {
        $index = array_search($userKey, $response['users']);
        if ($index === false) {return false;}
        unset($response['users'][$index]);
        return $response;
    }

    private static function validateEncoding ($encoding) {
        return isset($encoding['constants'])
            && isset($encoding['branches'])
            && is_array($encoding['constants'])
            && is_array($encoding['branches']);
    }

    private static function createRecord ($varKey, $location, $data, $user) {
        $record = [
            'question' => $varKey,
            'location' => $location,
            'responses' => [[
                'data' => $data,
                'users' => [$user]
            ]]
        ];
        return $record;
    }
}