<?php
namespace uab\mre\app;


use triagens\ArangoDb\Document;
use triagens\ArangoDb\Exception;
use vector\ArangoORM\DB\DB;

class BackupService {

    public static function backup( $from_collection, $document_key ){
        try {
            $dh = DB::getDocumentHandler();
            $doc = $dh->get( $from_collection, $document_key );

            $backup = Document::createFromArray([
                "from_collection"   => $from_collection,
                "document_key"      =>  $document_key,
                "data"              =>  $doc->getAll()
            ]);

            $dh->store( $backup, "backups" );
        } catch ( Exception $e ){
            return $e;
        }
    }

}