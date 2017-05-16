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
        $masterEncoding = $this->get('masterEncoding');
        $valueResponses = self::getValueResponses($assignment);
        foreach ($valueResponses as $remote) {
            self::mergeResponse($masterEncoding, $remote);
        }
        $this->update('masterEncoding', $masterEncoding);
    }

    /**
     * @param $assignment Assignment
     */
    private static function getValueResponses ($assignment) {
        $assID = $assignment->key();
        $encoding = $assignment->get('encoding');
        $responses = [];
        if (!self::validateEncoding($encoding)) {
            return $responses;
        }
        foreach ($encoding['constants'] as $response) {
            $responses[] = self::createResponse($response['question'], 0, $response['data'], $assID);
        }
        foreach ($encoding['branches'] as $branchIndex => $branch) {
            foreach ($branch as $response) {
                self::createResponse($response['question'], $branchIndex + 1, $response['data'], $assID);
            }
        }
        return $responses;
    }


    /**
     * @param $masterArr array of responses to the same question in the same location
     * @param $remote the response to merge
     */
    private static function mergeResponse (&$masterArr, $remote) {
        $remoteID = $remote['users'][0];

        //remove previous response
        foreach ($masterArr as $masterIndex => $master) {
            if ($master['question'] === $remote['question']
                && $master['location'] === $remote['location']
                && in_array($remoteID, $master['users'])) {
                unset($master['users'][array_search($remoteID, $master['users'])] );
            }
            //We don't want empty responses
            if (count($master['users']) == 0) {
                unset($masterArr[$masterIndex]);
            }
        }

        $masterArr = array_values($masterArr);

        //Add new response
        foreach ($masterArr as $master) {
            //if our response is the same as a previously-recorded response
            if ($master['question'] === $remote['question']
                && $master['location'] === $remote['location']
                && $master['data'] == $remote['data']) {
                //if our response doesn't already have us listed
                if (!in_array($remoteID, $master['users'])) {
                    //add us to the response
                    array_push($master['users'], $remoteID);
                }
                //Otherwise the response already includes us, so everything is good.
                //at this point, we are certainly successfully merged, so we can return
                return;
            }
        }
    }

    private static function validateEncoding ($encoding) {
        return isset($encoding['constants'])
            && isset($encoding['branches'])
            && is_array($encoding['constants'])
            && is_array($encoding['branches']);
    }

    private static function createResponse ($varKey, $location, $data, $user) {
        return [
            'question' => $varKey,
            'location' => $location,
            'data' => $data,
            'users' => [$user]
        ];
    }
}