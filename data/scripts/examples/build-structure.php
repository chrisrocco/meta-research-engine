<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 4:17 PM
 */

require __DIR__ . '/../../../vendor/autoload.php';

\DB\DB::enterDevelopmentMode();


// Make a study
$study = \Models\Study::create([
    'name'  =>  'study ' . rand(0, 1000)
]);

// Make some domains
$domains = [];
for( $i = 0; $i < rand(5, 15); $i++ ){
    $domain = \Models\Domain::create([
        'name'  =>  'domain ' . rand( 1000, 9999)
    ]);
    print "created domain " . $domain->id() . "\n";

    foreach (randomVars() as &$var){
        $domain->addVariable($var);
    }

    $domains[] = $domain;
}

// add the domains to a study
foreach ($domains as &$domain){
    $study->addDomain( $domain );
    print "added domain to study " . $domain->id() . "\n";
}


/* helper */
function randomVars(){
    $vars = [];
    for( $i = 0; $i < rand(3, 10); $i++ ){
        $var = \Models\Variable::create([
            'name'  =>  'variable ' . rand(1000, 9999)
        ]);
        array_push($vars, $var);
        print "created variable " . $var->id() . "\n";
    }
    return $vars;
}