<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 1:39 PM
 */

namespace DB;


use triagens\ArangoDb\CollectionHandler;
use triagens\ArangoDb\Connection;
use triagens\ArangoDb\Cursor;
use triagens\ArangoDb\DocumentHandler;

class DB
{
    /*----------------------------------------------------*/
    /*----------------------- CRUD -----------------------*/
    /*----------------------------------------------------*/
    public static function create( $col, $doc){
        $dh = self::getDocumentHandler();
        return $dh->save( $col, $doc );
    }
    public static function retrieve( $col, $_key ){
        $dh = self::getDocumentHandler();

        if(!$dh->has( $col, $_key)) return false;

        return $dh->get( $col, $_key );
    }
    public static function update( $doc ){
        $dh = self::getDocumentHandler();
        $dh->update( $doc );
    }
    public static function delete( $doc ){
        $dh = self::getDocumentHandler();
        $dh->remove( $doc );
    }




    public static function getAll( $col ){
        $ch = self::getCollectionHandler();
        return $ch->all( $col );
    }


    /**
     * Given an example, returns a set of Models
     * @param $data
     * @return Cursor
     */
    public static function getByExample( $col, $example ){
        $ch = self::getCollectionHandler();
        return $ch->byExample( $col, $example);
    }




    private static $connection;
    private static $document_handler;
    private static $collection_handler;

    /**
     * @return DocumentHandler
     */
    private static function getDocumentHandler(){
        if(self::$document_handler){
            return self::$document_handler;
        }

        $dh = new DocumentHandler(self::getConnection());
        self::$document_handler = $dh;

        return self::getDocumentHandler();
    }

    /**
     * @return CollectionHandler
     */
    private static function getCollectionHandler(){
        if(self::$collection_handler){
            return self::$collection_handler;
        }

        $ch = new CollectionHandler(self::getConnection());
        self::$collection_handler = $ch;

        return self::getCollectionHandler();
    }

    /**
     * @return Connection
     */
    public static function getConnection(){
        if(self::$connection){
            return self::$connection;
        }

        $settings = require __DIR__ . '/../src/settings.php';
        if(self::$is_dev_mode){
            $connection = new Connection($settings['arangodb_development_connection_options']);
        } else {
            $connection = new Connection($settings['arangodb_connection_options']);
        }
        self::$connection = $connection;

        return self::getConnection();
    }







    public static function enterDevelopmentMode(){
        self::$is_dev_mode = true;
    }
    private static $is_dev_mode;
}