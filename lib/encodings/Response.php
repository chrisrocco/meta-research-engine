<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/5/2017
 * Time: 6:52 PM
 */

namespace Encodings;


abstract class Response implements \JsonSerializable {

    public function getContent() {
        return $this->data;
    }

    public function getUsers() {
        return $this->users;
    }

    public abstract function getType();

    public function addUser ($userKey) {
        array_push($this->users, $userKey);
    }

    public function hasUser ($userKey) {
        return in_array($userKey, $this->users);
    }

    protected $data;
    protected $users;

    public function __construct($data, $users = [])
    {
        $this->data = $data;
        $this->users = $users;
    }

    //A workaround in order to serialize private properties
    public function jsonSerialize() {
        return get_object_vars($this);
    }

    public function __toString() {
        return json_encode($this);
    }
}