<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 1:47 PM
 */

require __DIR__ . '/../../../vendor/autoload.php';

use \triagens\ArangoDb;


\DB\DB::enterDevelopmentMode();

// Make some users
$users = [];
for ($i = 0; $i < 20; $i++){
    $users[] = \Models\User::create([
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
    $papers[] = \Models\Paper::create([
        'Title' =>  'Study #' . $i,
        'pmcID' =>  (rand(1000000, 9000000))
    ]);

    print 'Created paper ' . $papers[$i]->id() . "\n";
}

// Make the edges ( Double Encoded )
foreach ( $papers as $paper ){
    $randomUser = $users[ rand(0, count($users)-1) ];
    $randomUser2 = $users[ rand(0, count($users)-1) ];

    $a1 = \Models\Assignment::assign( $paper, $randomUser )->id();
    $a2 = \Models\Assignment::assign( $paper, $randomUser2 )->id();

    print "Created assignment \n";
}

// Make a graph

$double_encoded_graph = new ArangoDb\Graph('assignments_PHP');

$assignment = new ArangoDb\EdgeDefinition();
$assignment->setRelation( \Models\Assignment::$collection );
$assignment->addToCollection( \Models\User::$collection );
$assignment->addFromCollection( \Models\Paper::$collection );

$double_encoded_graph->addEdgeDefinition($assignment);

$gh = new ArangoDb\GraphHandler( \DB\DB::getConnection() );
$gh->createGraph($double_encoded_graph);

print "created graph " . $double_encoded_graph->getInternalId() . "\n";