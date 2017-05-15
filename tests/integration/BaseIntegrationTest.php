<?php

namespace Tests;

use DB\DB;
use Models\Vertices\User;
use triagens\ArangoDb\Connection;
use triagens\ArangoDb\ConnectionOptions;

/**
 * Tests the ability to define a study, variables, domains, and use these to build a project structure.
 */
class BaseIntegrationTest extends BaseTestCase {

    protected function setUp()
    {
        parent::setUp();

        // connect to testing DB
        $settings = require __DIR__ . '/../../src/settings.php';
        $config = $settings['settings']['database_connection_options'];
        $config[ConnectionOptions::OPTION_AUTH_USER] = "integration-testing";
        $config[ConnectionOptions::OPTION_AUTH_PASSWD] = "integrationTesting();";
        $config[ConnectionOptions::OPTION_DATABASE] = "integration-testing";
        $connection = new Connection($config);
        DB::$connection = $connection;

        require_once ( __DIR__ . '/../../data/scripts/db-truncate.php');
        require_once ( __DIR__ . '/../../data/scripts/db-init.php');
    }

    function testSetup(){
        $user = User::create([
            "name" => "Success!"
        ]);

        $dupe = User::retrieve( $user->key() );

        self::assertEquals("Success!", $dupe->get('name'));
    }

}
