<?php
/**
 * Created by PhpStorm.
 * User: Chris Rocco
 * Date: 5/28/2017
 * Time: 10:01 PM
 */

$settings = require __DIR__ . '/../src/settings.php';
$db_settings = $settings['settings']['database_connection_options'];
\vector\ArangoORM\DB\DB::connect($db_settings);