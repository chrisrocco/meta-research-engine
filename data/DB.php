<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 1:39 PM
 */

namespace DB;

use Models\Core\BaseModel;
use triagens\ArangoDb\CollectionHandler;
use triagens\ArangoDb\Connection;
use triagens\ArangoDb\DocumentHandler;
use triagens\ArangoDb\EdgeHandler;
use triagens\ArangoDb\Statement;

/**
 * Class DB
 * @package DB
 *
 * -----------------------
 * --------- API ---------
 * -----------------------
 *
 * Properties
 * [_] -> connection                            Statically properties get set the first time they are requested
 * [_] -> document_handler
 * [_] -> collection_handler
 * [_] -> edge_handler
 *
 * CRUD Operations
 * [_] create( collection, document )
 * [_] retrieve( collection, _key )
 * [_] update( document )
 * [_] delete( document )
 * [_] createEdge( collection, from_id, to_id, document )
 *
 * Query Methods
 * [_] query( AQL-string, bind-variables, flat-option )
 * [_] queryModel( AQL-string, bind-variables, model-class )                            // Wraps the resulting documents in a model class
 * [_] getAll( collection )
 * [_] getByExample( {} )
 */
class DB
{
    /*----------------------------------------------------*/
    /*----------------------- CRUD -----------------------*/
    /*----------------------------------------------------*/
    public static function create( $col, $doc){
        $dh = self::getDocumentHandler();
        return $dh->save( $col, $doc, [
                'createCollection'  =>  'true'
            ]);
    }
    public static function retrieve( $col, $_key ){
        $dh = self::getDocumentHandler();

        if(!$dh->has( $col, $_key)) return false;

        return $dh->get( $col, $_key );
    }
    public static function update( $doc ){
        $dh = self::getDocumentHandler();
        $dh->replace( $doc );
    }
    public static function delete( $doc ){
        $dh = self::getDocumentHandler();
        $dh->remove( $doc );
    }
    public static function createEdge( $col, $from, $to, $doc){
        $eh = self::getEdgeHandler();
        return $eh->saveEdge( $col, $from, $to, $doc, [
            'createCollection'  =>  true
        ]);
    }

    /*----------------------------------------------------*/
    /*----------------------- Query -----------------------*/
    /*----------------------------------------------------*/
    public static function query($query_string, $bindVars = [], $flat = true){
        $connection = self::getConnection();
        $statement = new Statement(
            $connection, [
                'query' => $query_string,
                'bindVars'  => $bindVars,
                '_flat' => $flat
            ]
        );
        return $statement->execute();
    }

    /**
     * @param $query_string
     * @param array $bindVars
     * @param $modelClass BaseModel::class The class of the model type
     * @return BaseModel[]
     */
    public static function queryModel($query_string, $bindVars = [], $modelClass){
        $cursor = self::query($query_string, $bindVars, false);
        $model = new $modelClass;
        return $model::wrapAll($cursor);
    }
    public static function getAll( $col ){
        $ch = self::getCollectionHandler();
        return $ch->all( $col );
    }
    public static function getByExample( $col, $example ){
        $ch = self::getCollectionHandler();
        return $ch->byExample( $col, $example);
    }

    /*----------------------------------------------------*/
    /*--------------------- Accessors -----------------------*/
    /*----------------------------------------------------*/
    static $connection;
    private static $document_handler;
    private static $edge_handler;
    private static $collection_handler;

    /**
     * @return DocumentHandler
     */
    static function getDocumentHandler(){
        if(self::$document_handler){
            return self::$document_handler;
        }

        $dh = new DocumentHandler(self::getConnection());
        self::$document_handler = $dh;

        return self::getDocumentHandler();
    }

    /**
     * @return EdgeHandler
     */
    static function getEdgeHandler(){
        if(self::$edge_handler){
            return self::$edge_handler;
        }

        $eh = new EdgeHandler(self::getConnection());
        self::$edge_handler = $eh;

        return self::getEdgeHandler();
    }

    /**
     * @return CollectionHandler
     */
    static function getCollectionHandler(){
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
    static function getConnection(){
        if(self::$connection){
            return self::$connection;
        }

        $settings = require __DIR__ . '/../src/settings.php';
        $config = $settings['settings']['database_connection_options'];
        $connection = new Connection($config);
        self::$connection = $connection;

        return self::getConnection();
    }
}