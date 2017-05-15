<?php

use Models\Vertices\Paper;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 12:00 AM
 */
class PaperTest extends \Tests\BaseTestCase
{
    public function testCreate(){
        $paper_data = [
            'title' =>  'Test Paper',
            'pmcID' =>  '12345'
        ];
        $paper = Paper::create($paper_data);

        $from_DB = Paper::retrieve( $paper->key() );

        self::assertInstanceOf(Paper::class, $from_DB);
    }
}