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

require __DIR__ . '/build-structure.php';
require __DIR__ . '/bulk-assignment.php';

foreach ( $papers as $paper ){
    $study->addpaper($paper);

    print "Added paper to study \n";
}