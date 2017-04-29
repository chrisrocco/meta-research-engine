<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 12:41 PM
 */

namespace Models;
use DB\DB;
use triagens\ArangoDb\Document;

abstract class Model {

    static $collection;     // uses a default collection name. For example, the Model, 'User' would use 'users'. If this gets overridden, you will have to create the DB collection manually.
    protected $dataObject;

    public function get($property){
        return $this->dataObject[$property];
    }
    public function set($property, $value){
        $this->dataObject[$property] = $value;
    }

    /*------*/
    /* CRUD */
    /*------*/
    public static function find( $_key ){
        $dh = DB::getDocumentHandler();

        $doc = $dh->getById( static::getCollection(), $_key );
        $data = $doc->getAll();

        return static::construct($data);
    }

    /**
     * @param $data
     * @return Model[]
     */
    public static function findByExample($data){
        $ch = DB::getCollectionHandler();
        $cursor = $ch->byExample(static::getCollection(), $data);

        $data_set = [];
        while($cursor->valid()){
            $doc = $cursor->current();
            $data_set[] = self::construct($doc->getAll());
            $cursor->next();
        }
        return $data_set;
    }

    /**
     * @param $data
     * @return mixed the document _key
     */
    public static function createOrUpdate($data){
        $doc = Document::createFromArray($data);
        $dh = DB::getDocumentHandler();
        return $dh->store($doc, static::getCollection());
    }


    public static function getClass(){
        return static::class;
    }
    public static function getCollection(){
        if( static::$collection ){
            return static::$collection;
        }

        // Default name will be used: 'User' would become 'users'
        $rc = new \ReflectionClass(static::class);
        $default_name = strtolower($rc->getShortName()) . "s";
        static::$collection = $default_name;
        // Dynamically create the collection in the DB
        $ch = DB::getCollectionHandler();
        if(!$ch->has(static::$collection)){
            $ch->create(static::$collection);
        }

        return static::getCollection();
    }
    protected static function construct($data){
        $class = static::getClass();
        return new $class($data);
    }

    function __call($name, $arguments) {
        if($arguments){
            $this->dataObject[$name] = $arguments[0];
        } else {
            return $this->dataObject[$name];
        }
    }
}