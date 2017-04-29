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

    static $collection;     // should be overridden by each child class
    protected $dataObject;

    function __construct($data) {
        $this->dataObject = $data;

        $ch = DB::getCollectionHandler();
        if(!$ch->has(static::getCollection())){
            $ch->create(static::getCollection());
        }
    }

    /*------*/
    /* CRUD */
    /*------*/
    public function save(){
        return static::store($this->dataObject);
    }

    public static function find( $_key ){
        $dh = DB::getDocumentHandler();

        $doc = $dh->getById( static::getCollection(), $_key );
        $data = $doc->getAll();

        $class = static::getClass();
        return new $class($data);
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

        return static::getCollection();
    }

    function __call($name, $arguments) {
        if($arguments){
            $this->dataObject[$name] = $arguments[0];
        } else {
            return $this->dataObject[$name];
        }
    }
}