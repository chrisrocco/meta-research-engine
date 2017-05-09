<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/5/2017
 * Time: 8:11 PM
 */

namespace Papers;


class ValueResponse extends Response {
    public function getType () {
        return "value";
    }

    public function getVariableID () {
        return $this->variableID;
    }

    public function getScope() {
        return $this->scope;
    }

    public function getBranchNum () {
        return $this->branchNum;
    }


    private $variableID;
    private $scope;
    private $branchNum;

    public function __construct($content, $variableID, $scope, $branchNum = -1, $users = [])
    {
        parent::__construct($content, $users);
        $this->variableID = $variableID;
        $this->scope = $scope;
        $this->branchNum = $branchNum;
    }

    public static function batchConstruct ($variableInstances, $userID, $scope, $branchNum = -1) {
        $resultArr = [];
        foreach ($variableInstances as $variableInstance) {
            array_push($resultArr, new ValueResponse(
                $variableInstance['content'],
                $variableInstance['field'],
                $scope,
                $branchNum,
                [$userID]));
        }
        return $resultArr;
    }
}