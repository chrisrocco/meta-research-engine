<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 7:42 PM
 */
require __DIR__ . '/../lib/master_encoding/MasterEncoding.php';

class QuickTest extends \Tests\BaseTestCase {
    function testQuickly(){
        $masterEncoding = [];
        $assignments = json_decode( file_get_contents( __DIR__ . '/data/assignments.json' ), true );
        $A = $assignments[0];
        $B = $assignments[1];
        $C = $assignments[0];

        $mergeLogA = MasterEncoding::merge( $A, $masterEncoding );
        $mergeLogB = MasterEncoding::merge( $B, $masterEncoding );
        $mergeLogC = MasterEncoding::merge( $C, $masterEncoding );


        $file = fopen( __DIR__ . "/data/masterEncodingOutput.json", "w");
        fwrite( $file, json_encode($masterEncoding, JSON_PRETTY_PRINT) );
        fclose( $file );
    }
}