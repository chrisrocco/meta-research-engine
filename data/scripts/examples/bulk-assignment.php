<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 1:47 PM
 *
 * Generates some random users and papers
 *
 * Each paper is double encoded to two random students
 */

require __DIR__ . '/../../../vendor/autoload.php';

use \triagens\ArangoDb;
use \Models\Vertices\User;
use \Models\Vertices\Paper;
use \Models\Edges\Assignment;

// Make some users
$users = [];
for ($i = 0; $i < 20; $i++){
    $users[] = User::create([
        'first_name'    =>  'User ' . $i,
        'last_name'     =>  ' ',
        'email'         =>  $i . '@gmail.com',
        'password'      =>  'password'
    ]);

    print 'Created user ' . $users[$i]->id() . "\n";
}

// Make some papers
$papers = [];
for ($i = 0; $i < 20; $i++){
    $papers[] = Paper::create([
        'Title' =>  'Study #' . $i,
        'pmcID' =>  (rand(1000000, 9000000))
    ]);

    print 'Created paper ' . $papers[$i]->id() . "\n";
}

// Make the edges ( Double Encoded )
foreach ( $papers as $paper ){
    $randomUser = $users[ rand(0, count($users)-1) ];
    $randomUser2 = $users[ rand(0, count($users)-1) ];

    $a1 = Assignment::assign( $paper, $randomUser )->id();
    $a2 = Assignment::assign( $paper, $randomUser2 )->id();

    print "Created assignment \n";
}

// Make a graph

$double_encoded_graph = new ArangoDb\Graph('assignments_PHP');

$assignment = new ArangoDb\EdgeDefinition();
$assignment->setRelation( Assignment::$collection );
$assignment->addToCollection( User::$collection );
$assignment->addFromCollection( Paper::$collection );

$double_encoded_graph->addEdgeDefinition($assignment);

$gh = new ArangoDb\GraphHandler( \DB\DB::getConnection() );
$gh->createGraph($double_encoded_graph);

print "created graph " . $double_encoded_graph->getInternalId() . "\n";