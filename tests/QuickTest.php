<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 7:42 PM
 */

class QuickTest extends \Tests\BaseTestCase {
    function testQuickly(){
        $paper = \Models\Vertices\Paper::retrieve(4967118);
        $assignment = \Models\Edges\Assignment::retrieve(4967417);
        $paper->merge($assignment);
    }
}