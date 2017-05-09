<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/5/2017
 * Time: 8:09 PM
 */

namespace Papers;


class ScopeResponse extends Response {
    public function getType () {
        return "scope";
    }

    public function __construct($variableID, $scope, $users) {
        $content = [
            'field' => $variableID,
            'scope' => $scope
        ];
        parent::__construct($content, $users);
    }
}