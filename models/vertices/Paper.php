<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:59 PM
 */

namespace Models\Vertices;


use DB\DB;
use Models\Core\VertexModel;
use Models\Edges\Assignment;
use Models\Edges\PaperOf;
use Models\Vertices\Project\Project;

class Paper extends VertexModel {
    static $collection = 'papers';

    /**
     * @return \Models\Vertices\Project\Project
     */
    public function getProject(){
        $AQL = "FOR project IN OUTBOUND @paperKey @@paper_to_study
                    RETURN project";
        $bindings = [
            'paperKey'  =>  $this->key(),
            '@paper_to_study'   =>  PaperOf::$collection
        ];
        return DB::queryModel($AQL, $bindings, Project::class)[0];
    }

    /**
     * @param $assignment Assignment
     */
    public function merge ($assignment) {
        $this->masterEncoding = $this->get('masterEncoding');

        $valueRecords = self::getValueRecords($assignment);
        foreach ($valueRecords as $remoteRecord) {
            $masterRecord = $this->getValueRecordByLocationAndName($remoteRecord['question'], $remoteRecord['location']);
            if (!$masterRecord) {
                //We have a new record
                $this->addValueRecord($remoteRecord);
            } else {
                $this->updateValueRecord(self::mergeRecord($masterRecord, $remoteRecord['responses'][0], 'data'));
            }
        }

        $scopeRecords = self::getScopeRecords($assignment);
        foreach ($scopeRecords as $remoteRecord) {
            $masterRecord = $this->getScopeRecordByName($remoteRecord['question']);
//            echo PHP_EOL.json_encode($masterRecord);
            if (!$masterRecord) {
                //We have a new record
                $this->addScopeRecord($remoteRecord);
            } else {
                $this->updateScopeRecord(self::mergeRecord($masterRecord, $remoteRecord['responses'][0], 'scope'));
            }
        }

        $structureRecord = self::getStructureRecord($assignment);
        $masterRecord = $this->getStructure();

        $this->updateStructureRecord(self::mergeRecord($masterRecord, $structureRecord['responses'][0], 'branches'));



        //figure out the conflicted flag


        $this->update('masterEncoding', $this->masterEncoding);
//        $this->update('isConflicted', $isConflicted);
    }

    const blankMasterEncoding = [
        'values' => [],
        'scopes' => [],
        'structure' => [
            'responses' => []
        ]
        ];

    private $masterEncoding = [];

    public function getConflicts () {}

    private function getStructure () {
        return $this->masterEncoding['structure'];
    }

    private function getScopes () {
        return $this->masterEncoding['scopes'];
    }

    private function getValues () {
        return $this->masterEncoding['structure'];
    }

    /**
     * @param $masterArr array of responses to the same question in the same location
     * @param $remote record to merge
     */
    private static function mergeRecord (&$masterRecord, $remoteResponse, $valueName) {
        $masterResponses = $masterRecord['responses'];
        $remoteUserID = $remoteResponse['users'][0];

        foreach ($masterResponses as $i => &$masterResponse) {
            //if the response already has us listed
            if(in_array($remoteUserID, $masterResponse['users'])) {
                //if our response is not the same
                if ($masterResponse[$valueName] != $remoteResponse[$valueName]) {
                    //remove us from the response
                    $masterResponse = self::response_removeUser ($masterResponse, $remoteUserID);
                    //If we just created an empty response
                    if (count($masterResponse['users']) === 0) {
                        //remove that response
                        unset($masterResponses[$i]);
                    }
                } else { //our response is the same
                    //return
                    $masterRecord['responses'] = $masterResponses;
                    return $masterRecord;
                }
            } else { //we are not listed
                //if our response is the same
                if ($masterResponse[$valueName] == $remoteResponse[$valueName]) {
                    //add us to the response
                    $masterResponse = self::response_addUser($masterResponse, $remoteUserID);
                    $masterResponses[$i] = $masterResponse;
                    $masterRecord['responses'] = $masterResponses;
                    return $masterRecord;
                }
            }
        }
        //We have a new response
        $masterResponses[] = $remoteResponse;
//        $isConflicted = true;
        //We're good
        $masterRecord['responses'] = $masterResponses;
        return $masterRecord;
    }

    private function getValueRecordByLocationAndName ($questionKey, $location) {
        for ($i = 0; $i < count($this->masterEncoding['values']); $i++) {
            if ($this->masterEncoding['values'][$i]['question'] === $questionKey
                && $this->masterEncoding['values'][$i]['location'] === $location
            ) {
                $record = &$this->masterEncoding['values'][$i];
                return $record;
            }
        }
        return false;
    }

    private function getScopeRecordByName ($questionKey) {
        for ($i = 0; $i < count($this->masterEncoding['scopes']); $i++) {
            if ($this->masterEncoding['scopes'][$i]['question'] === $questionKey
            ) {
                $record = &$this->masterEncoding['scopes'][$i];
                return $record;
            }
        }
        return false;
    }

    private function updateValueRecord ($record) {
        for ($i = 0; $i < count($this->masterEncoding['values']); $i++) {
            if ($this->masterEncoding['values'][$i]['question'] === $record['question']
                && $this->masterEncoding['values'][$i]['location'] === $record['location']
            ) {
//                echo "\n\t".json_encode($record);
                $this->masterEncoding['values'][$i]['responses'] = $record['responses'];
                return;
            }
        }
        $this->addValueRecord($record);
    }

    private function updateScopeRecord ($record) {
        for ($i = 0; $i < count($this->masterEncoding['scopes']); $i++) {
            if ($this->masterEncoding['scopes'][$i]['question'] === $record['question']
            ) {
//                echo "\n\t".json_encode($record);
                $this->masterEncoding['scopes'][$i]['responses'] = $record['responses'];
                return;
            }
        }
        $this->addScopeRecord($record);
    }

    private function updateStructureRecord ($record) {
        $this->masterEncoding['structure'] = $record;
    }

    private function addValueRecord ($record) {
        $this->masterEncoding['values'][] = $record;
    }

    private function addScopeRecord ($record) {
        $this->masterEncoding['scopes'][] = $record;
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
        $records = [];
        if (!self::validateEncoding($encoding)) {
            return $records;
        }
        foreach ($encoding['constants'] as $response) {
            $records[] = self::createRecord($response['question'], 0, $response['data'], $assID);
        }
        foreach ($encoding['branches'] as $branchIndex => $branch) {
            foreach ($branch as $response) {
                self::createRecord($response['question'], $branchIndex + 1, $response['data'], $assID);
            }
        }
        return $records;
    }

    /**
     * @param $assignment Assignment
     * @return array scopeRecords
     */
    private static function getScopeRecords ($assignment) {
        if (!is_a($assignment, Assignment::class)) {
            $assID = $assignment['_key'];
            $encoding = $assignment['encoding'];
        } else {
            $assID = $assignment->key();
            $encoding = $assignment->get('encoding');
        }
        $records = [];
        if (!self::validateEncoding($encoding)) {
            return $records;
        }
        foreach ($encoding['constants'] as $response) {
            $records[] = [
                'question' => $response['question'],
                'responses' => [
                    [
                    'scope' => 'constant',
                    'users' => [$assID]
                    ]
                ]
            ];
        }
        foreach($encoding['branches'][0] as $response) {
            $records[] = [
                'question' => $response['question'],
                'responses' => [
                    'scope' => 'variable',
                    'users' => [$assID]
                ]
            ];
        }
        return $records;
    }


    /**
     * @param $assignment Assignment
     * @return array structureRecord
     */
    private static function getStructureRecord ($assignment) {
        if (!is_a($assignment, Assignment::class)) {
            $assID = $assignment['_key'];
            $encoding = $assignment['encoding'];
        } else {
            $assID = $assignment->key();
            $encoding = $assignment->get('encoding');
        }
        $records = [];
        if (!self::validateEncoding($encoding)) {
            return $records;
        }
        $record = [
            'responses' => [[
                'branches' => count($encoding['branches']),
                'users' => [$assID]
            ]]
        ];
        return $record;
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