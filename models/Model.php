<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 12:41 PM
 */

namespace Models;
use DB\DB;
use phpDocumentor\Reflection\Types\Array_;
use triagens\ArangoDb\Document;

abstract class Model {

    public function key(){
        return $this->arango_document->getInternalKey();
    }
    public function get($property){
        return $this->arango_document->get($property);
    }

    public function getDocument(){
        return $this->arango_document;
    }

    /*------------------------------------------------*/
    /*--------------------- CRUD ---------------------*/
    /*------------------------------------------------*/

    /**
     * Creates a new record in the database, wraps it in a model, and returns it.
     * @param $data
     * @return Model
     */
    public static function create( $data ){
        $document = Document::createFromArray( $data );
        $key = DB::create( static::getCollectionName(), $document );
        $document = DB::retrieve( static::getCollectionName(), $key );
        return static::createFromDocument($document);
    }

    /**
     * Fetches a document from the database, wraps it in a model, and returns it.
     * @param $_key
     * @return bool|mixed
     */
    public static function retrieve( $_key ){
        $doc = DB::retrieve( static::getCollectionName(), $_key );

        if( !$doc ) return false;

        return static::createFromDocument($doc);
    }

    /**
     * Changes one property of the document, and updates it in the database
     * @param $property
     * @param $data
     */
    public function update( $property, $data ){
        $this->arango_document->set( $property, $data );
        DB::update( $this->arango_document );
    }

    /**
     * Deletes the document from the database
     */
    public function delete(){
        DB::delete( $this->arango_document );
    }

    /*------------------------------------------------*/
    /*--------------------- Query ---------------------*/
    /*------------------------------------------------*/

    /**
     * Query by example.
     * @param $example array            [ 'email' => 'chris.rocco7@gmail.com' ]
     * @return Model[]
     */
    public static function getByExample( $example ){
        $cursor = DB::getByExample( static::getCollectionName(), $example );

        $data_set = [];
        while($cursor->valid()){
            $doc = $cursor->current();
            $data_set[] = self::createFromDocument($doc);
            $cursor->next();
        }
        return $data_set;
    }

    /*--------------------------------------------------*/
    /*--------------------- Helper ---------------------*/
    /*--------------------------------------------------*/
    protected static function getClass(){
        return static::class;
    }
    protected static function getCollectionName(){
        if( static::$collection ){
            return static::$collection;
        }

        // Default name will be used: 'User' would become 'users'
        $rc = new \ReflectionClass(static::class);
        $default_name = strtolower($rc->getShortName()) . "s";
        static::$collection = $default_name;

        return static::getCollectionName();
    }
    protected static function createFromDocument( $arango_document ){
        $class = static::getClass();
        $model = new $class;
        $model->arango_document = $arango_document;
        return $model;
    }


    /**
     * @var Document
     */
    protected $arango_document;
    static $collection;     // uses a default collection name. For example, the Model, 'User' would use 'users'. If this gets overridden, you will have to create the DB collection manually.

}