<?php // (C) Copyright Bobbing Wide 2019

class Tests_issue_16 extends BW_UnitTestCase {

	/** 
	 * set up logic
	 * 
	 * - ensure any database updates are rolled back
	 */
	function setUp() : void {
		parent::setUp();
		oik_require( "admin/oik-types-admin.php", "oik-types" );
	}

	/**
	 * Tests getting singular name from args or args['labels]
	 * Note: Function now in admin/oik-types-admin.php
	 */
	
	function test_bw_return_singular_name() {
		$args = [ 'singular_name' => 'singular'];
		$singular_name = bw_return_singular_name( $args );
		$this->assertEquals( $singular_name, 'singular' );

		$args = [ 'labels' => [ 'singular_name' => 'labels singular' ] ];
		$singular_name = bw_return_singular_name( $args );
		$this->assertEquals( $singular_name, 'labels singular' );

		$args =  [ 'singular_name' => 'singular',
		           'labels' => [ 'singular_name' => 'labels singular' ] ];
		$singular_name = bw_return_singular_name( $args );
		$this->assertEquals( $singular_name, 'singular' );


	}

	/**
	 * This also tests issues #5 and #13 for custom taxonomies needing show_in_rest
	 */
	function test_oiktax_register_taxonomy() {
		$taxonomy = bw_query_taxonomy( 'test-16' );
		$this->assertFalse( $taxonomy );
		$args = [ 'type' => 'tags'
				, 'label' => 'Brands'
				, 'title' => 'Product Brand - e.g. Accuphase'
				, 'singular_name' => 'Brand'
				];
		$data = [ 'args' => $args ];
		oiktax_register_taxonomy( "test-16", $data );
		$taxonomy = bw_query_taxonomy( 'test-16' );
		$this->assertTrue( $taxonomy );
		$taxonomy = get_taxonomy( 'test-16' );
		//print_r( $taxonomy );
		$this->assertEquals( $taxonomy->label, 'Brands');
		$this->assertEquals( $taxonomy->labels->name, 'Brands' );
		$this->assertEquals( $taxonomy->labels->singular_name, 'Brand');
		$this->assertTrue( $taxonomy->show_in_rest );
	}
}

