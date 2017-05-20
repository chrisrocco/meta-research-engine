<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 12:41 PM
 */

namespace Models\Core;
use DB\DB;
use triagens\ArangoDb\Document;

/**
 * Class BaseModel
 * @package Models\Core
 *
 * Associated with a single collection in the database.
 * Instances model a single document   in the database.
 * Uses active record keeping - stays in sync with database
 *
 * -----------------------
 * --------- API ---------
 * -----------------------
 *
 * Properties
 * [+] -> collection                            // Collection name. Should be overridden by sub-classes.
 *
 * Document Manipulation
 * [+] key()
 * [+] id()
 * [+] get(property)
 * [+] toArray()
 * [+] update( property, new-value )
 * [+] delete()
 *
 * Collection Queries
 * [_] retrieve( _key )
 * [_] getByExample( {obj} )
 *
 * Helpers
 * [:] getClass()                               // Accurate for sub-classes
 * [:] getCollectionName()                      // Accurate for sub-classes
 * [:] wrap( document )                         // Used after DB queries
 * [:] wrapAll( cursor )                        // Used after DB queries
 * [:] addMetaData( data )                      // Called on object creation
 */
abstract class BaseModel {

    /**
     * @var Document
     */
    protected $arango_document;

    public function key(){
        return $this->arango_document->getInternalKey();
    }
    public function id(){
        return $this->arango_document->getInternalId();
    }
    public function get($property){
        return $this->arango_document->get($property);
    }
    public function toArray(){
        return $this->arango_document->getAll();
    }
    public function getDocument(){
        return $this->arango_document;
    }

    /*------------------------------------------------*/
    /*--------------------- CRUD ---------------------*/
    /*------------------------------------------------*/

    /**
     * Fetches a document from the database, wraps it in a model, and returns it.
     * @param $_key
     * @return bool|mixed
     */
    public static function retrieve( $_key ){
        $doc = DB::retrieve( static::getCollectionName(), $_key );

        if( !$doc ) return false;

        return static::wrap($doc);
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
     * @return BaseModel[]
     */
    public static function getByExample( $example ){
        $cursor = DB::getByExample( static::getCollectionName(), $example );

        return self::wrapAll( $cursor );
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

    static function wrap( $arango_document ){
        $class = static::getClass();
        $model = new $class;
        $model->arango_document = $arango_document;
        return $model;
    }
    static function wrapAll( &$cursor ){
        // wraps a result set consisting of documents into this model's type
        $data_set = [];
        while($cursor->valid()){
            $doc = $cursor->current();
            $data_set[] = self::wrap($doc);
            $cursor->next();
        }
        return $data_set;
    }

    static function idToKey ($idString) {
        $whatIWant = false;
        if (($pos = strpos($idString, "/")) !== FALSE) {
            $whatIWant = substr($idString, $pos+1);
        }
        return $whatIWant;
    }

    protected static function addMetaData( &$data ){
        $data["date_created"] = date("F j, Y, g:i a");
    }

    static $collection;     // uses a default collection name. For example, the BaseModel, 'User' would use 'users'. If this gets overridden, you will have to create the DB collection manually.

}