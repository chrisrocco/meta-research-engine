<?php

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/10/2017
 * Time: 8:06 PM
 */
class QueryBank {
    public static $queries;

    public static function execute($name, $params){
        QueryBank::loadQueries();
        global $connection;
        $statement = new ArangoDBClient\Statement(
            $connection, [
                'query' => QueryBank::$queries[$name],
                'bindVars' => $params,
                '_flat' => true
            ]
        );
        return $statement->execute()->getAll();
    }

    function __call($name, $arguments) {
        $bindVars = $arguments[0];
        return QueryBank::query($name, $bindVars);
    }

    private static function loadQueries(){
        if(!isset(QueryBank::$queries)){
            $string = file_get_contents("../src/app/queries/queries.json");
            QueryBank::$queries = json_decode($string, true);
        }
    }
}