<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:02 PM
 */

require __DIR__ . '/../../vendor/autoload.php';

class CollectionBuilder extends \DB\DB {
    static function dropCollections(  ){
        $ch = parent::getCollectionHandler();
        $collections = $ch->getAllCollections([ 'excludeSystem' => true ]);
        foreach ($collections as $name => $type){

            print 'droppping ' . $name . "\n";
            $ch->drop( $name );
        }
    }
}

CollectionBuilder::dropCollections();