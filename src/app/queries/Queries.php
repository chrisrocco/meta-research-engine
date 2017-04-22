<?php

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/10/2017
 * Time: 8:06 PM
 */
class Queries {
    public $arangodb_connection;
    public $queries;

    function query($name, $params){
        $statement = new ArangoDBClient\Statement(
            $this->arangodb_connection, [
                'query' => $this->queries[$name],
                'bindVars' => $params,
                '_flat' => true
            ]
        );
        return $statement->execute()->getAll();
    }

    function __call($name, $arguments) {
        $bindVars = $arguments[0];
        return $this->query($name, $bindVars);
    }

    function __construct($arangodb_connection) {
        $this->arangodb_connection = $arangodb_connection;
        $string = file_get_contents("../src/app/queries/queries.json");
        $this->queries = json_decode($string, true);
    }

}