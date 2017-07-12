<?php
$settings = require __DIR__ . '/../app/settings.php';
$db_settings = $settings['settings']['database_connection_options'];
\vector\ArangoORM\DB\DB::connect($db_settings);