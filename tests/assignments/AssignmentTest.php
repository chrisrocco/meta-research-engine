<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 7/26/2017
 * Time: 9:50 PM
 */

namespace Tests;


use uab\mre\app\Assignment;

class AssignmentTest extends BaseTestCase {

    function testParseAssignment(){
        $json = file_get_contents(__DIR__ . '/../test_data/assignment.json');
        $assignment = json_decode( $json, true);
        $this->assertTrue( count($assignment['encoding']['constants']) > 10 );
        $assignment = Assignment::parse($assignment);
        var_dump( $assignment->toArray() );
    }

}