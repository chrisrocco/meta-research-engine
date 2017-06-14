<?php
use Models\Vertices\Project\Project;
use Models\Vertices\User;
use triagens\ArangoDb\EdgeDefinition;
use triagens\ArangoDb\Graph;
use triagens\ArangoDb\GraphHandler;
use uab\MRE\Models\Project\AdminOf;
use vector\ArangoORM\DB\DB;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db_connect.php';


const GRAPH_NAME = "project_managers";

$conn = DB::getConnection();
$gh = new GraphHandler( $conn );
$g  =   new Graph( GRAPH_NAME );

$ed = new EdgeDefinition( AdminOf::$collection, [User::$collection], [Project::$collection] );
$g->addEdgeDefinition( $ed );
try {
    $gh->createGraph($g);
    print "Created graph '".GRAPH_NAME."'";
} catch ( \triagens\ArangoDb\ServerException $e ) {
    print "Graph " . GRAPH_NAME . " already exists";
}

