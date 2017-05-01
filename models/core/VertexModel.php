<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/29/17
 * Time: 11:53 PM
 */

namespace Models\Core;


use DB\DB;
use triagens\ArangoDb\Document;
use triagens\ArangoDb\Edge;

class VertexModel extends BaseModel {

    /**
     * Creates a new record in the database, wraps it in a model, and returns it.
     * @param $data array
     * @return BaseModel
     */
    public static function create( $data ){

        self::addMetaData( $data );

        $document = Document::createFromArray( $data );
        $key = DB::create( static::getCollectionName(), $document );
//        $document = DB::retrieve( static::getCollectionName(), $key );    // Performance optimization
        $document->setInternalKey($key);
        return static::wrap($document);
    }

}