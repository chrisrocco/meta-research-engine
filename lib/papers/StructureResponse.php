<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/5/2017
 * Time: 7:21 PM
 */

namespace Papers;


class StructureResponse extends Response
{
    public function getType() {
        return "structure";
    }

    public function __construct($numBranches, $users = []) {
        $content = [
            'numBranches' => $numBranches
        ];
        parent::__construct($content, $users);
    }
}