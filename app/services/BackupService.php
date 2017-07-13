<?php
namespace uab\mre\app;


use triagens\ArangoDb\Document;
use triagens\ArangoDb\Exception;
use uab\MRE\dao\Project;
use vector\ArangoORM\DB\DB;

class BackupService {

    public static function backup( $from_collection, $document_key, $extra_data = null ){
        try {
            $dh = DB::getDocumentHandler();
            $doc = $dh->get( $from_collection, $document_key );

            $backup = Document::createFromArray([
                "from_collection"   => $from_collection,
                "document_key"      =>  $document_key,
                "data"              =>  $doc->getAll(),
                "date"              =>  date("Y-m-d h:i:sa")
            ]);

            if($extra_data){
                $backup->set("extra_data", $extra_data);
            }

            $dh->store( $backup, "backups" );
        } catch ( Exception $e ){
            return $e;
        }
    }

    public static function backupProject( Project $project ){
        $structure = StructureService::getStructureAdj( $project );
        self::backup( Project::$collection, $project->key(), ["structure" => $structure] );
    }

}