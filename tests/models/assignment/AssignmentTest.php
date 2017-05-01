<?php
namespace Tests\Models;

use \Models\Edges\Assignment;
use triagens\ArangoDb\Edge;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/30/17
 * Time: 12:00 AM
 */
class AssignmentTest extends \Tests\BaseTestCase
{

    /**
     * @return Assignment
     */
    public function testCreate(){

        $assignment = Assignment::create(
            'users/2691361', 'papers/2706538',
            [
                'completion'    =>  0,
                'encoding'      =>  null,
                'done'          =>  false,
                'data_created'  =>  date('m-d-y')
            ]
        );

        self::assertInstanceOf( Assignment::class, $assignment );

        return $assignment;
    }

    /**
     * @depends testCreate
     * @param $existing_assignment  Assignment
     */
    public function testUpdate( $existing_assignment ){
        $existing_assignment->update('completion', 100);
        $existing_assignment->setTo( 'users/9999999' );

        $assignment = Assignment::retrieve( $existing_assignment->key() );
    }
}