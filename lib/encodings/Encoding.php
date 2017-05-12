<?php
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/9/2017
 * Time: 2:43 PM
 */

namespace Encodings;


class Encoding implements \JsonSerializable {
    private $id;
    private $branches;
    private $constants;


    /**
     * @return StructureResponse
     */
    public function getStructureResponse() {
        return new StructureResponse(count($this->branches), [$this->id]);
    }

    /**
     * @return ScopeResponse[]
     */
    public function getScopeResponses () {
        $resultArr = [];

        //Assemble scopes from constants
        foreach ($this->constants as $valueResponse) {
            if (!is_a($valueResponse, ValueResponse::class)) {
                continue;
            }
            array_push($resultArr, new ScopeResponse($valueResponse->getVariableID(), "constant", [$this->id]));
        }

        //Assemble scopes from branches
        //NOTE: we could have used $this->branches[0] and assumed identical variables in each branch,
        //      but this is more general
        foreach ($this->branches as $branch) {
            foreach ($branch as $valueResponse) {
                array_push($resultArr, new ScopeResponse($valueResponse->getVariableID(), "variable", [$this->id]));
            }
        }
        //Strip out the redundant ScopeResponses and return
        return array_unique($resultArr);
    }

    /**
     * @return ValueResponse[]
     */
    public function getValueResponses () {
        //Initialize the result array
        $resultArr = array_merge($this->constants, []); //I was unsure if $resultArr = $this->constants would only be a reference
        //Add each of the branches to the result array
        foreach ($this->branches as $branch) {
            $resultArr = array_merge($resultArr, $branch);
        }
        return $resultArr;
    }

    public function __construct($encoding, $id) {
        try {
            $this->id = $id;
            //Construct constants
            $this->constants = ValueResponse::batchConstruct($encoding->constants, $id, -1);

            //Construct branches
            $this->branches = [];
            for ($i = 0; $i < count($encoding->branches); $i++) {
                $this->branches[$i] = ValueResponse::batchConstruct($encoding->branches[$i], $id, $i);
            }

        } catch (Exception $e) {
            //TODO
        }
    }

    //A workaround in order to serialize private properties
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}