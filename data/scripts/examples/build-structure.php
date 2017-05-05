<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 4:17 PM
 */
require __DIR__ . '/../../../vendor/autoload.php';

use \Models\Vertices\Study;
use \Models\Vertices\Variable;
use \Models\Vertices\Domain;
use \Models\Edges\SubdomainOf;
use \Models\Edges\VariableOf;

$vars_per_domain = 2;
$subdomains_per_domain = 3;
$top_level_domains = 3;

// Make a study
$study = Study::create([
    'name'  =>  'study ' . rand(0, 1000)
]);

// Make some variables
function randomVars( $how_many ){
    $vars = [];
    for( $i = 0; $i < $how_many; $i++ ){
        $var = Variable::create([
            'name'  =>  'variable ' . rand(1000, 9999)
        ]);
        array_push($vars, $var);
        print "created variable " . $var->id() . "\n";
    }
    return $vars;
}

// Make some domains
$domains = [];
for( $i = 0; $i < $top_level_domains; $i++ ){
    $domain = Domain::create([
        'name'  =>  'domain ' . rand( 1000, 9999)
    ]);
    print "created domain " . $domain->id() . "\n";
    for( $j = 0; $j < $subdomains_per_domain; $j++ ){
        // add some subdomains
        $subdomain = Domain::create([
            'name'  =>  'subdomain ' . rand(1, 9999)
        ]);
        print "created subdomain " . $subdomain->id() . "\n";

        foreach (randomVars($vars_per_domain) as &$var){
            $subdomain->addVariable($var);
        }
        $domain->addSubdomain( $subdomain );
    }
    foreach (randomVars($vars_per_domain) as &$var){
        $domain->addVariable($var);
    }
    $domains[] = $domain;
}

// Add the domains to a study
foreach ($domains as &$d){
    $study->addDomain( $d );
    print "added domain to study " . $d->id() . "\n";
}

// Create a graph
$graph = new \triagens\ArangoDb\Graph("study_structures_PHP");
$variable_of = new \triagens\ArangoDb\EdgeDefinition();
$variable_of->setRelation(VariableOf::$collection);
$variable_of->addFromCollection(Variable::$collection);
$variable_of->addToCollection(Domain::$collection);
$subdomain_of = new \triagens\ArangoDb\EdgeDefinition();
$subdomain_of->setRelation(SubdomainOf::$collection);
$subdomain_of->addFromCollection(Domain::$collection);
$subdomain_of->addToCollection(Domain::$collection);
$subdomain_of->addToCollection(Study::$collection);
$graph->addEdgeDefinition($variable_of);
$graph->addEdgeDefinition($subdomain_of);
$gh = new \triagens\ArangoDb\GraphHandler( \DB\DB::getConnection() );

try {
    $gh->createGraph($graph);
    print "created graph " . $graph->getInternalId() . "\n";
} catch ( Exception $e ){
    if( $e->getCode() === 1925 ){
        print "Graph already exists \n";
    }
}