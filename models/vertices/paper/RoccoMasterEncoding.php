<?php

/* Assignment
 * Master Encoding
 *
 * assignment record    : question, location, data, user
 * master record        : question, location, responses
 * master response      : data, users
 * master response users: array
 * */

namespace Models\Vertices\Paper;

class RoccoMasterEncoding {
    static function merge( $assignment, &$masterEncoding ){

        foreach ( $masterEncoding as &$masterRecord ){
            self::cleanup( $assignment["_key"], $masterRecord );
        }

        $log = [];
        $log['headers']['Assignment'] = $assignment;
        $log['headers']['Report'] = $masterEncoding;
        $parsedAssignment = RoccoMasterEncoding::parseAssignment( $assignment );
        foreach ( $parsedAssignment as $i => $userRecord ){
            $masterRecord = &RoccoMasterEncoding::matchRecord( $userRecord, $masterEncoding );
            if( $masterRecord == self::$NO_MATCH ){
                RoccoMasterEncoding::record( $userRecord, $masterEncoding );
                $log[]["Added New Record"] = $userRecord;
                continue;
            }
            $masterResponse = &RoccoMasterEncoding::matchResponse( $userRecord, $masterRecord );
            if( $masterResponse == self::$NO_MATCH ){
                RoccoMasterEncoding::recordResponse( $userRecord, $masterRecord );
                $log[]['Recorded New Response'][] = [
                    "userRecord" => $userRecord,
                    "master" => $masterRecord
                ];
                continue;
            }
            self::cleanup( $userRecord['user'], $masterRecord );
            RoccoMasterEncoding::recordResponseUser( $userRecord, $masterResponse, $log );
        }

        self::cleanupEmptyRecords( $masterEncoding );
        return $log;
    }

    static function conflictedStatus ($masterEncoding) {
        foreach ($masterEncoding as $record) {
            if (count($record['responses']) > 1 ) {
                return true;
            }
        }
        return false;
    }

    static function parseAssignment( $assignment ){
        $output = [];
        if( isset( $assignment['encoding']['constants'] ) ){
            foreach ( $assignment['encoding']['constants'] as $constant ){
                $output[] = [
                    "user" => $assignment['_key'],
                    "question" => $constant['question'],
                    "location" => 0,
                    "data" => $constant['data']
                ];
            }
        }
        if ( isset( $assignment['encoding']['branches'] ) ) {
            foreach ( $assignment['encoding']['branches'] as $branchNum => $branchVariables ){
                foreach($branchVariables as $variable) {
                    $output[] = [
                        "user" => $assignment['_key'],
                        "question" => $variable['question'],
                        "location" => $branchNum + 1,
                        "data" => $variable['data']
                    ];
                }
            }
        }
        return $output;
    }
    static function &matchRecord( $userRecord, &$masterEncoding ){
        $question = $userRecord['question'];
        $location = $userRecord['location'];

        foreach ( $masterEncoding as &$masterRecord ){
            $masterQ = $masterRecord['question'];
            $masterL = $masterRecord['location'];

            if( $question == $masterQ && $location == $masterL ) return $masterRecord;
        }
        return self::$NO_MATCH;
    }
    static function &matchResponse( $userRecord, &$masterRecord ){
        $userData = $userRecord['data'];
        foreach ( $masterRecord['responses'] as &$masterResponse ){
            $masterData = $masterResponse['data'];
            if( RoccoMasterEncoding::compareData($userData, $masterData) ){
                return $masterResponse;
            }
        }
        return self::$NO_MATCH;
    }

    static function record( $userRecord, &$masterEncoding ){
        $masterEncoding[] = [
            "question" => $userRecord['question'],
            "location" => $userRecord['location'],
            "responses" => [
                [
                    "data" => $userRecord['data'],
                    "users" => [ $userRecord['user'] ]
                ]
            ]
        ];
    }
    static function recordResponse( $userRecord, &$masterRecord ){
        $userKey = $userRecord['user'];
        self::cleanup( $userKey, $masterRecord );
        $responseData = [
            "data" => $userRecord['data'],
            "users" => [ $userRecord['user'] ]
        ];
        $masterRecord['responses'][] = $responseData;
    }
    static function recordResponseUser( $userRecord, &$masterResponse, &$log = null ){
        $userKey = $userRecord['user'];
        if( !in_array($userKey, $masterResponse['users']) ){
            $masterResponse['users'][] = $userRecord['user'];
            if( $log !== null ){
                $log[]["Added User to Existing Response"] = [
                    "User Record" => $userRecord,
                    "Master Response" => $masterResponse
                ];
            }
        }
    }

    static function cleanup( $userKey, &$masterRecord, &$log = null ){
        for( $i = 0; $i < count($masterRecord['responses']); $i++){
            $response = &$masterRecord['responses'][$i];
            if( in_array($userKey, $response['users'])){
                $index = array_search( $userKey, $response['users']);
                array_splice($response['users'], $index, 1);

                if( $log !== null ){
                    $log[]["Changed User Answer"] = [
                        "User" => $userKey,
                        "Old Response" => $response
                    ];
                }
            }
            if( count( $response['users'] ) == 0 ){
                array_splice( $masterRecord['responses'], $i, 1);

                if( $log !== null ) {
                    $log[]["Deleted Empty Response"] = [
                        "Old Response" => $response
                    ];
                }
            }
        }
    }

    static function cleanupEmptyRecords( &$masterEncoding ){
        $count = count( $masterEncoding );
        for ( $i = 0; $i < $count; $i++ ){
            if( count( $masterEncoding[$i]['responses'] ) === 0 ){
                unset( $masterEncoding[$i] );
            }
        }
        $masterEncoding = array_values( $masterEncoding );
    }

    static function compareData( $A, $B, $log = null ){
        $result = true;
        foreach ($A as $key => $value) {
            if( $A[$key] != $B[$key] ) $result = false;
        }
        if( $log ){
            $log[] = [
                "compared" => $A,
                "with" => $B,
                "and got" => $result
            ];
        }
        return $result;
    }

    static $NO_MATCH = "";
}