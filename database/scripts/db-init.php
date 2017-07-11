<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db_connect.php';

$schema = json_decode( file_get_contents( __DIR__ . '/../schema.json' ), true );
$documents = json_decode( file_get_contents( __DIR__ . '/../documents.json' ), true );

\vector\ArangoORM\DB\DB::buildFromSchema( $schema );
\vector\ArangoORM\DB\DB::createDocuments( $documents );