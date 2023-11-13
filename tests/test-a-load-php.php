<?php

/**
 * @package oik-types
 * @copyright (C) Copyright Bobbing Wide 2023
 *
 * Unit tests to load all the files for PHP 8.2, except batch ones
 */

class Tests_load_libs extends BW_UnitTestCase
{

    /**
     * set up logic
     *
     * - ensure any database updates are rolled back
     * - we need oik-googlemap to load the functions we're testing
     */
    function setUp(): void
    {
        parent::setUp();

    }

    function test_load_admin_php() {

        $files = glob( 'admin/*.php');
        //print_r( $files );

        foreach ( $files as $file ) {
            switch ( $file ) {
                case 'admin/oik-types-cli.php':
                    break;

                default:
                    oik_require( $file, 'oik-types');
            }

        }
        $this->assertTrue( true );


    }

    function test_load_admin() {
        $this->load_dir_files( 'admin', ['admin/oik-types-cli.php'] );
        $this->assertTrue( true );

    }


    function load_dir_files( $dir, $excludes=[] ) {
        $files = glob( "$dir/*.php");
        //print_r( $files );

        foreach ( $files as $file ) {
            if ( !in_array( $file, $excludes ) ) {
                oik_require( $file, 'oik-types');
            }
            //oik_require( $file, 'oik-types');
        }
    }

    function test_load_plugin() {
        oik_require( 'oik-types.php', 'oik-types');
        $this->assertTrue( true );

    }

}

