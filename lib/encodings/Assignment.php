<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/9/2017
 * Time: 2:43 PM
 */

namespace Encodings;


class Assignment implements \JsonSerializable
{

    public function getID() {
        return $this->id;
    }

    public function getEncoding() {
        return $this->encoding;
    }

    public function getDateCreated () {
        return $this->date_created;
    }

    public function getStatus () {
        return $this->status;
    }

    public function getCompletion() {
        return $this->completion;
    }

    private $id;
    private $encoding;
    private $date_created;
    private $status;
    private $completion;

    public function __construct($assignment){
        if (is_string($assignment)) {
            $assignment = json_decode($assignment);
        }
        try {
            $this->id = $assignment->_key;
            $this->encoding = new Encoding($assignment->encoding, $this->id);
            $this->date_created = $assignment->date_created;
            $this->status = $assignment->status;
            $this->completion = $assignment->completion;
        } catch (Exception $e) {
            //TODO
        }
    }

    //A workaround in order to serialize private properties
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}