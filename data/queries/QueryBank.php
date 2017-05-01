<?php
namespace DB\Queries;

use DB\DB;
use triagens\ArangoDb\Statement;

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/10/2017
 * Time: 8:06 PM
 */
class QueryBank {

    private static $queries;

    /**
     * @param $name     string  The name of the saved query
     * @param $params   array   The bind variables
     * @return \triagens\ArangoDb\Cursor
     */
    public static function execute($name, $params){
        $connection = DB::getConnection();
        $q = self::getQueries();

        $query_string = $q[$name];

        $statement = new Statement(
            $connection, [
                'query' => $query_string,
                'bindVars' => $params,
                '_flat' => true
            ]
        );

        return $statement->execute();
    }

    function __call($name, $arguments) {
        $bindVars = $arguments[0];
        return QueryBank::execute($name, $bindVars);
    }

    static function getQueries(){
        if(self::$queries) return self::$queries;

        $q = require __DIR__ . '/queries.php';
        self::$queries = $q;

        return self::getQueries();
    }
}