<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/19/2017
 * Time: 7:20 AM
 */

namespace MasterEncoding;


class Record  implements \JsonSerializable {

    /**
     * @param $other Record
     */
    public function merge ($other) {
        foreach ($other->responses as $remote) {
            foreach ($remote[MasterEncoding::RESPONSE_USERS] as $key) {
                $hasUpdated = false;
                foreach ($this->responses as &$master) {
                    $equalValue = self::responseEquals($master, $remote);
                    $hasKey = self::hasKey($master, $key);
                        if ($equalValue) {
                            $hasUpdated = true;
                            if (!$hasKey) {
                                self::addKey($master, $key);
                            }
                            continue;
                        }
                        if ($hasKey) {
                            self::removeKey($master, $key);
                        }
                }
                //If we get here and no matching record has been found, a new response needs to be recorded
                if (!$hasUpdated) {
                    $this->addResponse($remote);
                }
            }
        }
        $this->cleanResponses();
    }

    /**
     * @param $response array
     */
    private function addResponse (&$response) {
        $this->responses[] = $response;
    }

    /**
     * Removes any empty responses from $this->responses
     * Re-indexes $this->responses and $this->responses[$i][MasterEncoding::RESPONSE_USERS]
     */
    private function cleanResponses () {
        foreach ($this->responses as $index => &$response) {
            if(count ($response[MasterEncoding::RESPONSE_USERS]) === 0) {
                unset($this->responses[$index]);
                continue;
            }
            $response[MasterEncoding::RESPONSE_USERS] = array_values($response[MasterEncoding::RESPONSE_USERS]);
        }
        $this->responses = array_values($this->responses);
    }

    /**
     * @param $responseA array
     * @param $responseB array
     * @return bool
     */
    private static function responseEquals ($responseA, $responseB) {
        return $responseA[MasterEncoding::VALUE_TERM] == $responseB[MasterEncoding::VALUE_TERM];
    }

    /**
     * @param $response array
     * @param $key string
     * @return bool
     */
    private static function hasKey ($response, $key) {
        foreach ($response[MasterEncoding::RESPONSE_USERS] as $compare) {
            if ($key === $compare) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $response array
     * @param $key string
     */
    private static function addKey (&$response, $key) {
        if (array_search($key, $response[MasterEncoding::RESPONSE_USERS]) !== false) {
            return;
        }
        array_push($response[MasterEncoding::RESPONSE_USERS], $key);
    }

    /**
     * @param $response array
     * @param $key string
     */
    private static function removeKey (&$response, $key) {
        $index = array_search($key, $response[MasterEncoding::RESPONSE_USERS]);
        if ($index === false) {
            return;
        }
        unset($response[MasterEncoding::RESPONSE_USERS][$index]);
    }

    /** Takes an encoding and converts into an array of Records
     * @param $encoding
     * @param $key
     * @return mixed Record[] | bool
     */
    public static function parseEncoding ($encoding, $key) {
        //Quick sanity-check
        $shallowValidity = isset($encoding[MasterEncoding::ENCODING_CONSTANTS])
            && isset($encoding[MasterEncoding::ENCODING_BRANCHES])
            && is_array($encoding[MasterEncoding::ENCODING_CONSTANTS])
            && is_array($encoding[MasterEncoding::ENCODING_BRANCHES]);
        if (!$shallowValidity) {
            return false;
        }
        //Actually try to parse the encoding now
        $records = [];
        foreach ($encoding[MasterEncoding::ENCODING_CONSTANTS] as $response) {
            try {
                $records[] = Record::newSingle(
                    $key, $response[MasterEncoding::RECORD_NAME],
                    0, $response[MasterEncoding::VALUE_TERM]
                );
            } catch (\Exception $e) {
                return false;
            }
        }
        return $records;
    }

    /**
     * @param $name string
     * @param $location string
     * @param $data array
     * @param $key string
     * @return Record
     */
    public static function newSingle ($key, $name, $location, $data) {
        return new Record([
            MasterEncoding::RECORD_NAME => $name,
            MasterEncoding::RECORD_LOCATION => $location,
            MasterEncoding::RECORD_RESPONSES => [[
                MasterEncoding::RESPONSE_USERS => [$key],
                MasterEncoding::VALUE_TERM => $data,
            ]]
        ]);
    }

    public function toStorage() {
        return [
            MasterEncoding::RECORD_NAME => $this->name(),
            MasterEncoding::RECORD_LOCATION => $this->location(),
            MasterEncoding::RECORD_RESPONSES => $this->responses()

        ];
    }

    /**
     * @return string
     */
    public function name () {
        return $this->name;
    }

    /**
     * @return string
     */
    public function location () {
        return $this->location;
    }

    /**
     * @return array
     */
    public function responses() {
        return $this->responses;
    }

    private $name;
    private $location;
    private $responses = [];

    public function __construct($storage) {
        $shallowValidity = isset($storage[MasterEncoding::RECORD_NAME])
            && isset($storage[MasterEncoding::RECORD_LOCATION])
            && isset($storage[MasterEncoding::RECORD_RESPONSES]);
        if (!$shallowValidity) {
            throw new \Exception("Record::__construct() - bad storage (shallow)");
        }

        $this->name = $storage[MasterEncoding::RECORD_NAME];
        $this->location = $storage[MasterEncoding::RECORD_LOCATION];
        $this->responses = $storage[MasterEncoding::RECORD_RESPONSES];

        foreach ($this->responses as $response) {
            $validity = isset($response[MasterEncoding::RESPONSE_USERS])
                && isset($response[MasterEncoding::VALUE_TERM]);
            if (!$validity) {
                throw new \Exception("Record::__construct() - bad storage (deep)");
            }
        }
    }

    public function jsonSerialize() {
        return $this->toStorage();
    }

}