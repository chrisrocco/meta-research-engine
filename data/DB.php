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
use triagens\ArangoDb\DocumentHandler;

class DB
{
    private static $connection;
    private static $document_handler;
    private static $collection_handler;

    /**
     * @return DocumentHandler
     */
    public static function getDocumentHandler(){
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
    public static function getCollectionHandler(){
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