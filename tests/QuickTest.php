<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 7:42 PM
 */

class QuickTest extends \Tests\BaseTestCase {
    function testQuickly(){
        $papers = \DB\DB::queryModel("FOR paper in papers
                               SORT RAND()
                               LIMIT 1
                               RETURN paper", [], \Models\Vertices\Paper::class);
        $randomPaperModel = $papers[0];


        var_dump( $randomPaperModel );
    }
}