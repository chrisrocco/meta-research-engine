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
        if(!self::$is_dev_mode){
            print 'You almost fucked up';
            return;
        };

        $ch = parent::getCollectionHandler();
        $collections = $ch->getAllCollections([ 'excludeSystem' => true ]);
        foreach ($collections as $name => $type){

            print 'droppping ' . $name . "\n";
            $ch->drop( $name );
        }
    }
}

\DB\DB::enterDevelopmentMode();   // Please don't removed this. Im going to regret writing this script

CollectionBuilder::dropCollections();