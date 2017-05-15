<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 7:42 PM
 */

class QuickTest extends \Tests\BaseTestCase {
    function testQuickly(){
        $paper = \Models\Vertices\Paper::retrieve(4332781);
        $user = \Models\Vertices\User::retrieve(4323770);

        \Models\Edges\Assignment::assign( $paper, $user );
    }
}