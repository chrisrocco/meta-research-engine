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
        return $this->varID;
    }
    public function getScope() {
        switch ($this->branch) {
            case -1 :
                return "constant";
                break;
            default :
                return "variable";
                break;
        }
    }

    public function getBranchIndex () {
        return $this->branch + 1;
    }

    private $varID;
    private $branch; //its number

    public function __construct($content, $variableID, $scope, $branchNum = -1, $users = [])
    {
        parent::__construct($content, $users);
        $this->varID = $variableID;
        $this->branch = $branchNum;
    }

    public static function batchConstruct ($variableInstances, $userID, $scope, $branchNum = -1) {
        $resultArr = [];
        foreach ($variableInstances as $variableInstance) {
            array_push($resultArr, new ValueResponse(
                $variableInstance->content,
                $variableInstance->field,
                $scope,
                $branchNum,
                [$userID]));
        }
        return $resultArr;
    }
}