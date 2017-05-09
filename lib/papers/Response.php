<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/5/2017
 * Time: 6:52 PM
 */

namespace Papers;


abstract class Response
{

    public function getContent() {
        return $this->content;
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

    private $content;
    private $users;

    public function __construct($content, $users = [])
    {
        $this->content = $content;
        $this->users = $users;
    }
}