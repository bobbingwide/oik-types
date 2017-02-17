<?php // (C) Copyright Bobbing Wide 2017


if ( PHP_SAPI !== "cli" ) { 
	die( "norty" );
}

/** 
 * oik-types CLI
 */


function oik_types_lazy_run_oik_types() {
	oik_require( "admin/oik-types-admin.php", "oik-types" );
	oik_require( "admin/oik-types.php", "oik-types" );
	
	oik_types_display_post_types();
	
	oik_types_set_archive_posts_per_page();
	oik_types_display_post_types();
}

function oik_types_display_post_types() {
	$types = bw_list_registered_post_types();
	//print_r( $types ); 
	foreach ( $types as $type ) {
		oik_types_display_post_type( $type );
	}
}

/**
 * Displays a post type
 */
function oik_types_display_post_type( $post_type ) {
	$archive_posts_per_page = query_archive_posts_per_page( $post_type );
	echo "$post_type $archive_posts_per_page" . PHP_EOL;
}

function query_archive_posts_per_page( $post_type ) {
	$post_type_object = get_post_type_object( $post_type );
	bw_trace2( $post_type_object, "post_type_object", false );
	if ( property_exists( $post_type_object, "archive_posts_per_page" ) ) {
		$archive_posts_per_page = $post_type_object->archive_posts_per_page;
	} else {
		$archive_posts_per_page = "";
	}
	return( $archive_posts_per_page );
}

function set_archive_posts_per_page( $post_type, $archive_posts_per_page ) {
	$post_type_object = get_post_type_object( $post_type );
	$post_type_object->archive_posts_per_page = $archive_posts_per_page;
}
	

/**
 * Update archive posts per page
 
 We need to access the bw_types option and set the setting

 */
function oik_types_update_archive_posts_per_page( $post_type, $archive_posts_per_page ) {
  $bw_types = get_option( "bw_types" );
	$bw_type = bw_array_get( $bw_types, $post_type, null );
	if ( !$bw_type ) {
		$bw_type = set_bw_type_from_post_type_object( $post_type );
	}
	$bw_type['args']['type'] = $post_type;
	$bw_type['args']['archive_posts_per_page'] = $archive_posts_per_page;
	_oik_cpt_update_type( $bw_type );
	set_archive_posts_per_page( $post_type, $archive_posts_per_page );
}

/**
 *
 */
function oik_types_set_archive_posts_per_page() {
	
	oik_types_update_archive_posts_per_page( "oik-themes", 21 );
	oik_types_update_archive_posts_per_page( "oik-plugins", 21 );
	oik_types_update_archive_posts_per_page( "oik_api", 99 );
	oik_types_update_archive_posts_per_page( "oik_class", 99 );
	oik_types_update_archive_posts_per_page( "oik_file", 99 );
	oik_types_update_archive_posts_per_page( "oik_hook", 99 );
	oik_types_update_archive_posts_per_page( "oik_shortcodes", 54 );
 
}

/** 
 * Set defaults for archive_posts_per_page
attachment
custom_css
customize_changeset
download 
jetpack-portfolio
nav_menu_item
oik-faq
oik-plugins 21
oik-themes  21
oik_api 99
oik_class
oik_edd_apikey
oik_file
oik_hook
oik_parsed_source
oik_pluginversion
oik_premiumversion
oik_sc_param
oik_shortcodes
oik_testimonials
oik_themeversion
oik_themiumversion
oik_todo
page
post
revision
shortcode_example
 */ 
function oik_types_posts_per_page_defaults() {
	$defaults = array( "oik-plugins" 		=> 21 
									 , "oik-themes" 		=> 21
									 , "oik_shortcodes" => 50
									 , "oik-faq" => 21
									 );
									 
									 
	return( $defaults );
}

/**
 * Create a bw_type from a post type object
 * 
 * When all we want to do is to set one field we still have to create the whole object if it doesn't already exist.
 * We use oikcp_get_registered_type_args() since it caters for the supports array.
 *
 * This code has to be kept in line with _oik_cpt_type_validate().
 * 
 * @param string $post_type
 * @return array the bw_type entry
 */
function set_bw_type_from_post_type_object( $post_type ) {
	$args = oikcpt_get_registered_type_args( $post_type );
	$bw_type = array();
  $bw_type['args']['type'] = $post_type;
  $bw_type['args']['label'] = bw_array_get( $args['args'], "label", null );
  $bw_type['args']['singular_name'] = bw_return_singular_name( $args['args'] );
  $bw_type['args']['description'] = bw_array_get( $args['args'], "description", null );
  $bw_type['args']['hierarchical'] = bw_array_get( $args['args'], "hierarchical", null );
  $bw_type['args']['has_archive'] = bw_array_get( $args['args'], "has_archive", null );
  $bw_type['args']['title'] = bw_array_get( $args['args'], "title", null );
  $bw_type['args']['public'] = bw_array_get( $args['args'], "public", null );
  $bw_type['args']['exclude_from_search'] = bw_array_get( $args['args'], "exclude_from_search", null );
  $bw_type['args']['publicly_queryable'] = bw_array_get( $args['args'], "publicly_queryable", null );
  $bw_type['args']['show_ui'] = bw_array_get( $args['args'], "show_ui", null );
  $bw_type['args']['show_in_nav_menus'] = bw_array_get( $args['args'], "show_in_nav_menus", null );
  $bw_type['args']['show_in_menu'] = bw_array_get( $args['args'], "show_in_menu", null );
  $bw_type['args']['show_in_admin_bar'] = bw_array_get( $args['args'], "show_in_admin_bar", null );
  $bw_type['args']['rewrite'] = bw_array_get( $args['args'], "rewrite", null );
  $bw_type['args']['supports'] = bw_array_get( $args['args'], "supports", null );
	$bw_type['args']['archive_sort'] = bw_array_get( $args['args'], "archive_sort", null );
	$bw_type['args']['archive_posts_per_page'] = bw_array_get( $args['args'], "archive_posts_per_page", null );
	return( $bw_type );
}
