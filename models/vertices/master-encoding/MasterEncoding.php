<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/19/2017
 * Time: 7:20 AM
 */

namespace MasterEncoding;


use triagens\ArangoDb\Exception;

class MasterEncoding implements \JsonSerializable {

    //List of constants at the bottom

    public function merge($encoding, $key) {
        $remoteRecords = Record::parseEncoding($encoding, $key);
        if (!$remoteRecords) {
            throw new \Exception("MasterEncoding::merge() - bad encoding");
        }
        foreach ($remoteRecords as $remote) {
            $master = $this->matchRecord($remote->name(), $remote->location());
            if (!$master) {
                $this->addRecord($remote);
                continue;
            }
            $master->merge($remote);
        }
    }

    /**
     * @param $conflictLevel int
     * @return array
     */
    public function report ($conflictLevel) {

    }

    /**
     * return array
     */
    public function toStorage() {
        $result = [];

        foreach ($this->internal as $name) {
            foreach ($name as $record) {
                $result[] = $record->toStorage();
            }
        }
        return $result;
    }

    /** Takes what we store in the database (an array of Records->toStorage()) and
     *  returns the internal representation
     * @param $storage array
     */
    private function parseStorage ($storage) {
        $records = [];
        //Validate the storage
        if (!is_array($storage)) {
            throw new \Error("MasterEncoding::parseStorage() - \$storage is not an array");
        }
        foreach ($storage as $unparsedRecord) {
            $records[] = new Record ($unparsedRecord);
        }
        //Convert to internal
        foreach ($records as $record) {
            $this->addRecord($record);
        }
        return $this->internal;
    }

    /** Gets the corresponding Record in this MasterEncoding
     * @param $location string
     * @param $name string
     * @return mixed Record | false
     */
    private function matchRecord ($name, $location) {
        if (!isset($this->internal[$name][$location])) {
            return [];
        }
        return $this->internal[$name][$location];
    }

    /**
     * @param $record Record
     */
    private function addRecord ($record) {
        $this->internal[$record->name()][$record->location()] = $record;
    }

    private $internal = [[]]; //$internal[name][location] = Record

    public function __construct($storage) {
        $this->parseStorage($storage);
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }


    const BLANK = [];

    const RECORD_NAME = "question";
    const RECORD_LOCATION = "location";
    const RECORD_RESPONSES = "responses";

    const ENCODING_CONSTANTS = "constants";
    const ENCODING_BRANCHES = "branches";

    const RESPONSE_USERS = "users";
    const VALUE_TERM = "data";
    const SCOPE_TERM = "scope";
    const STRUCTURE_TERM = "branches";

    const SCOPE_CONSTANT = "constant";
    const SCOPE_VARIABLE = "variable";

}