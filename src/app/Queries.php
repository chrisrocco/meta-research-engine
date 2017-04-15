<?php

/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 4/10/2017
 * Time: 8:06 PM
 */
class Queries {
    public $queries;

    function query($name, $params){
        return new ArangoStatement(
            $this->arangodb_connection, [
                'query' => $this->queries[$name],
                'bindVars' => $params,
                '_flat' => true
            ]
        );
    }

    function __call($name, $arguments) {
        return $this->query($name, $arguments);
    }

    function __construct($arangoConnection) {
        $getCollaborators =
            'FOR student IN INBOUND CONCAT("classes/", @classID) enrolled_in '
            . ' RETURN UNSET (student, "password")';



        $this->queries['getCollaborators'] = $getCollaborators;
    }

    function tester(){
        Queries::getCollaborators(["classID" => 1234]);
    }
}