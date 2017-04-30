<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:02 PM
 */

require __DIR__ . '/../../vendor/autoload.php';

$collections = json_decode(file_get_contents(__DIR__ . '/collections.json'), true);

class CollectionBuilder extends \DB\DB {
    static function initCollections( $collections_data ){
        $ch = parent::getCollectionHandler();
        foreach ($collections_data as $name => $type){
            if($ch->has($name)) continue;

            if($type === 'edge') $type = 3;
            if($type === 'vertex') $type = 2;

            $ch->create($name, [ 'type' => $type ]);
        }
    }
}

\DB\DB::enterDevelopmentMode();
CollectionBuilder::initCollections($collections);

echo "The following collections have been initialized: \r\n";
echo json_encode($collections, JSON_PRETTY_PRINT);
echo "\r\n";