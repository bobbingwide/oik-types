<?php // (C) Copyright Bobbing Wide 2013-2018

/**
 * Lazy implementation for "oik-types" admin menu
 *
 * Note: You can register multiple sub-pages with the same page name.
 * This means that more than one callback function will be called when the page is loaded.
 * I'm not sure of the value of this. 
 * 
 * Note: To avoid getting the oik menu duplicated the name of the first submenu item needs to be the same
 * as the main menu item. see http://geekpreneur.blogspot.com/2009/07/getting-unwanted-sub-menu-item-in.html
 * In most "normal" WP menus the main menu gives you the full list
 */
function oikcpt_lazy_admin_menu() {
  add_menu_page( __('[oik] Types', 'oik'), __('Types', 'oik'), 'manage_options', 'oik_types_menu', 'oik_types_menu', 'div' ); 
  add_submenu_page( 'oik_types_menu', __( 'Summary', 'oik-types'), __( 'Summary', 'oik-types'), 'manage_options', 'oik_types_menu', 'oik_types_menu');
  add_submenu_page( 'oik_types_menu', __( 'oik types', 'oik-types'), __( 'Types', 'oik-types'), 'manage_options', 'oik_types', 'oikcpt_types_do_page');
  add_submenu_page( 'oik_types_menu', __( 'oik fields', 'oik-types'), __('Fields', 'oik-types'), 'manage_options', 'oik_fields', 'oikfie_fields_do_page' );
  add_submenu_page( 'oik_types_menu', __( 'oik taxonomies', 'oik-types'), __('Taxonomies', 'oik-types'), 'manage_options', 'oik_taxonomies', 'oiktax_taxonomies_do_page' );
  add_submenu_page( 'oik_types_menu', __( 'oik fields to types', 'oik-types'), __('Fields to types', 'oik-types'), 'manage_options', 'oik_f2t', 'oikf2t_do_page' );
  add_submenu_page( 'oik_types_menu', __( 'oik taxonomies to types', 'oik-types'), __('Taxonomies to types', 'oik-types'), 'manage_options', 'oik_x2t', 'oikx2t_do_page' );
}

/**
 * Return a list of registered post_types
 * 
 * @return array Registered post types
 */
function bw_list_registered_post_types() {
  $types = get_post_types();
  bw_trace2( $types, "types", false, BW_TRACE_VERBOSE );
  return( $types );
}

/**
 * Display the oik types page overview - in one big page
 * 
 * Calls "oik_types_box" action to allow other plugins to add their own "oik_types_box"es.
 * @TODO Actually what we want to do is display the stuff in tabs.  Need to look at other code to do this. 2013/10/28
 * 
 */
function oik_types_menu() {
  oik_menu_header( "types", "w100pc" );
  oik_box( NULL, NULL, "Types", "oikcpt_types_do_page" );
  oik_box( NULL, NULL, "Fields", "oikfie_fields_do_page" );
  oik_box( NULL, NULL, "Taxonomies", "oiktax_taxonomies_do_page" );
  oik_box( NULL, NULL, "Fields to Types", "oikf2t_do_page" );
  oik_box( NULL, NULL, "Taxonomies to Types", "oikx2t_do_page" );
  do_action( "oik_types_box" );
  oik_menu_footer();
  bw_flush();
}

/**
 * Lazy implementation of the oik-types admin page
 */
function oikcpt_types_do_page() {
  oik_require( "admin/oik-types.php", "oik-types" );
  oikcpt_lazy_types_do_page();
}

/**
 * Lazy implementation of the oik-fields admin page
 */
function oikfie_fields_do_page() {
  oik_require( "admin/oik-fields.php", "oik-types" );
  oikfie_lazy_fields_do_page();
}

/**
 * Lazy implemenation of the oik-taxonomies admin page
 */
function oiktax_taxonomies_do_page() {
  bw_backtrace();
  oik_require( "admin/oik-taxonomies.php", "oik-types" );
  oiktax_lazy_taxonomies_do_page();
}

/**
 * Page for defining the mapping of fields to types or types to fields
 */
function oikt2f_do_page() {
  oik_require( "admin/oik-t2f.php", "oik-types" );
  oikt2f_lazy_do_page();
} 
 
/**
 * Page for defining the mapping of fields to types or types to fields
 */
function oikf2t_do_page() {
  oik_require( "admin/oik-f2t.php", "oik-types" );
  oikf2t_lazy_do_page();
}
  
/**
 * Page for defining the mapping of taxonomies to types or types to taxonomies
 */
function oikx2t_do_page() {
  oik_require( "admin/oik-x2t.php", "oik-types" );
  oikx2t_lazy_do_page();
}


