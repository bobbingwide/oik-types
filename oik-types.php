<?php
/*
Plugin Name: oik-types 
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-types
Description: oik types - custom post types, fields and taxonomies UI
Depends: oik base plugin, oik fields
Version: 1.9.2
Author: bobbingwide
Author URI: http://www.oik-plugins.com//author/bobbingwide
License: GPL2

    Copyright 2013-2017 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

oikcpt_plugin_loaded();

/**
 * Implement "oik_fields_loaded" for oik-types
 *
 * Register / update CPTs
 */
function oikcpt_fields_loaded() {
	bw_load_plugin_textdomain( "oik-types" );
  $bw_types = get_option( "bw_types" );
  if ( $bw_types ) {
    foreach ( $bw_types as $type => $data ) {
      oikcpt_register_post_type( $type, $data );
    }
  }  
}

/**
 * Set the required values for the $args to register_post_type()
 * 
 * - bw_register_post_type() requires "singular_label" in order to create "singular_name"
 * - Sometimes we need to cast stdObject to an array
 * - "on" is used as the checkbox representation of true.
 * - we only set args to false if the field is in the original array
 *
 * @TODO Determine when 'sometimes' is!
 
 * @param array $data_args - 
 * @param bool $cast - true when we need to cast objects to arrays
 * @param array $original_args 
 * @return array $args
 */
function oikcpt_adjust_args( $data_args, $cast=true, $original_args=array() ) {
	$args = array();
	foreach ( $data_args as $key => $data ) {
		if ( $cast && is_object( $data )) {
			$data = (array) $data;
		}
		if ( $data == "on" ) {
			$data = true;
		}
		if ( $key == "singular_name" ) {
			$key = "singular_label";
		}
		//bw_trace2( $data, "data", false );  
		if ( $data ) {
			$args[$key] = $data;
		} else {
			if ( isset( $original_args[$key] ) && is_bool( $original_args[$key ] ) ) {
				$args[$key] = false;
			}
		}
	}
	bw_trace2( $args, "args", true, BW_TRACE_VERBOSE );
	return( $args );
}

/**
 * Register or update a custom post type
 * 
 * Fields, categories and taxonomies are registered separately
 *
 * @param string $type - the post type - expected to be lower case - sanitize_key
 * @param array $data - registration information from the bw_types options array
 */
function oikcpt_register_post_type( $type, $data ) {
	$args = oikcpt_adjust_args( $data["args"], true );
	$type_exists = post_type_exists( $type ); 
	if ( $type_exists ) {
		bw_update_post_type( $type, $args ); 
	} else {
		if ( isset( $args['cap'] ) ) {
			$args['capabilities'] = $args['cap'];
			unset( $args['cap'] ) ;
		}
		bw_register_post_type( $type, $args );
	}  
}

/**
 * Handle a change of the has_archive / rewrite setting
 * 
 * If a post type is not already registered we don't have a problem defining it with "has_archive" set to true
 * So long as we remember that we need to revisit permalinks
 * 
 * @TODO Complete the code one day! 2015/06/18
 * 
 * 
 */
function bw_update_archive_stuff( $type, $value ) {


}

/**
 * Update post_type supports features
 *
 * @param string $type - post type
 * @param array $value -   
 */
function bw_update_post_type_supports( $type, $value ) {
  global $_wp_post_type_features;
  //bw_trace2( $_wp_post_type_features[$type], "pft before", true, BW_TRACE_VERBOSE );
  $features = get_all_post_type_supports( $type );
  //bw_trace2( $features, "features", false );
  if ( count( $features) ) {
    foreach ( $features as $feature => $fval ) {
      //bw_trace2( $feature, "feature", false );
      remove_post_type_support( $type, $feature );
    }
  }    
  add_post_type_support( $type, $value );
  //bw_trace2( $_wp_post_type_features[$type], "ptf after", false, BW_TRACE_VERBOSE );
}

/**
 * Update the existing post_type with the overrides
 * 
 * This includes removing "supports" capability as well as adding it.
 * 
 * @TODO - We also need to do something special for "has_archive" and "rewrite"; otherwise the rewrite rules will be incorrect.
 *   
 * @TODO - Confirm that ignoring labels for WordPress 4.3 is the right solution. See TRAC #33543
 * Basically this solution means we can't override the builtin post types defaults. Is that the only minor issue? 
 * 
 *
 * @param string $type - the post type registration to update
 * @param array $args - the options values to apply - already converted to bool where necessary
 */
function bw_update_post_type( $type, $args ) {
	$post_type_object = get_post_type_object( $type );
	//bw_trace2( $post_type_object, "post_type_object before", true );
	foreach ( $args as $key => $value ) {
		if ( $key == "labels" ) {
			continue;
		}	
		if ( $key == "supports" ) {
			bw_update_post_type_supports( $type, $value ); 
		}
		if ( $key == "has_archive" ) {
			bw_update_archive_stuff( $type, $value );
		}
		/* 
		 * Intercept when attachments are required in the nav_menu
		 */
		if ( $type == 'attachment' && "show_in_nav_menus" == $key && $value ) {
			add_filter( "nav_menu_meta_box_object", "oik_types_nav_menu_meta_box_object", 11 );
		} 
		if ( is_array( $value ) ) {
			// convert to stdObject? 
			$post_type_object->$key = (object) $value;
			} else {
			$post_type_object->$key = $value;
		}
	}
	//bw_trace2( $post_type_object, "post_type_object after", false );
} 

/**
 * Implement "oik_fields_loaded" for oik-types fields
 *
 * We can register the fields either after or before all the types have been registered
 * But we can't perform the mapping until both the field and the type have been registered.
 *
 * **?** 2013/11/26 This is a bit of a shame since we really only need to know about the field when it's actually being used.
 * Ditto for most of the information about a post_type. If it's not being referenced anywhere then why do we need to bother with the details.
 * 
 */ 
function oikfie_fields_loaded() {
  $bw_fields = get_option( "bw_fields" );
  if ( $bw_fields ) {
    foreach ( $bw_fields as $field => $data ) {
      oikfie_register_field( $field, $data );
    }
  }  
}

/**
 * Implement "oik_fields_loaded" for oik-types taxonomies
 *
 * We can register the taxonomies either after or before all the types have been registered
 * But we can't perform the mapping until both the taxonomy and the type have been registered
 */ 
function oiktax_fields_loaded() {
  $bw_taxonomies = get_option( "bw_taxonomies" );
  if ( $bw_taxonomies ) {
    foreach ( $bw_taxonomies as $taxonomy => $data ) {
      oiktax_register_taxonomy( $taxonomy, $data );
    }
  }  
}

/**
 * Implement "oik_fields_loaded" for field to type relationships
 *
 */ 
function oikf2t_fields_loaded() {
  $bw_f2ts = get_option( "bw_f2ts" );
  if ( $bw_f2ts ) {
    //bw_trace2( $bw_f2ts );
    foreach ( $bw_f2ts as $field => $bw_f2t ) {
      bw_register_field_for_object_type( $bw_f2t['args']['field'], $bw_f2t['args']['type'] );
    }
  }  
}

/**
 * Implement "oik_fields_loaded" for taxonomy to type relationships
 * 
 */ 
function oikx2t_fields_loaded() {
  $bw_x2ts = get_option( "bw_x2ts" );
  if ( $bw_x2ts ) {
    //bw_trace2( $bw_x2ts );
    foreach ( $bw_x2ts as $taxonomy => $bw_x2t ) {
      // bw_register_field_for_object_type( $bw_f2t['args']['field'], $bw_f2t['args']['type'] );
      register_taxonomy_for_object_type( $bw_x2t['args']['taxonomy'], $bw_x2t['args']['type'] ); 
    }
  }  
}

/**
 * Register a custom field
 * 
 * @param string $field - the field name
 * @param array $data - the field's attributes
   e.g.
            [args] => Array
                (
                    [type] => text
                    [title] => A text field containing the name of the summit
                    [required] => 0
                )

 */
function oikfie_register_field( $field, $data ) {
  //bw_trace2();
  $args = $data["args"];
  $argsargs = bw_array_get( $args, "args", null ); 
  // @TODO - add the args for each field
  //bw_trace2( $argsargs, "argsargs" );
  bw_register_field( $field, $args['type'], $args['title'], $argsargs ); 
}

/**
 * Register a custom taxonomy
 * 
 * @TODO - Support plural and singular labels
 *
 * @param string $taxonomy - taxonomy name
 * @param array $data - the taxonomy attributes
              [args] => Array
                  (
                      [type] => tags
                      [label] => Brands
                      [title] => Product Brand - e.g. Accuphase
                  )

 */
function oiktax_register_taxonomy( $taxonomy, $data ) {
  //bw_trace2();
  $args = $data["args"];
  $type = bw_array_get( $args, "type", null );
  $label = bw_array_get( $args, "label", null );
  if ( $type == "tags" ) {
    bw_register_custom_tags( $taxonomy, null, $label ); 
  } else { 
    bw_register_custom_category( $taxonomy, null, $label ); 
  }  
}

/**
 * Implement "oik_admin_menu" for oik-types
 */
function oikcpt_admin_menu() {
  oik_require( "admin/oik-types-admin.php", "oik-types" );
  oikcpt_lazy_admin_menu();
}

/**
 * Implement "admin_notices" action for oik-types 
 * 
 * Dependency checking for oik-types:
 * Now dependent upon oik v2.1 and oik-fields v1.33
 * Now dependent upon oik v2.2 and oik-fields v1.35
 * 
 */ 
function oik_types_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    bw_trace2( $plugin_basename );
    add_action( "after_plugin_row_oik-types/oik-types.php", "oik_types_activation" );   
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
      require_once( "admin/oik-activation.php" );
    }
  }  
  $depends = "oik:2.2,oik-fields:1.35";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Implement "pre_get_posts" for oik-types
 *
 * Updates the array of post types which can be displayed on the page that's showing the blog posts.
 * i.e. The home page, as opposed to the front page.
 *
 * Notes: 
 * - You can't check for main query in "pre_get_posts" 
 * - You can't use WP_Query::is_main_query() either
 * - You can't check is_home() in pre_get_posts for other reasons
 * - Assumes that the "post" post type, for blog posts, will always be included.
 * - Once we've run the main query we don't need this filter any more.
 
 * 
 * @param WP_Query $query - the query object for the current query
 * @return WP_Query - the updated query object 
 */
function oik_types_pre_get_posts( $query ) {
		//bw_trace2();
	if ( is_home() && false == $query->get('suppress_filters') ) {
		$post_types = array( "post" );
		global $wp_post_types;
		foreach ( $wp_post_types as $post_type => $data ) {
			$supports = post_type_supports( $post_type, "home" );
			if ( $supports ) {
				$post_types[] = $post_type;
				if ( $post_type == "attachment" ) {
					add_filter( "posts_where", "oik_types_posts_where", 10, 2);
				}
			}
		}
		$query->set( 'post_type', $post_types );
		remove_filter( "pre_get_posts", "oik_types_pre_get_posts" );
	}
	return( $query );
}

/**
 * Implement "posts_where" filter to search for attachments as well
 * 
 * When the post type also includes 'attachment', we expect to find
 * `
 * wp_posts.post_status = 'publish'
 * `
 * but need it to be
 * `
 * wp_posts.post_status IN ('publish', 'inherit' )
 * `
 * 
 * @param string $where - the current where clause
 * @param array $this_parm - 
 * @return string the updated where clause
 */
function oik_types_posts_where( $where, $this_parm ) {
  $where = str_replace( "= 'publish'", "IN ( 'publish', 'inherit' )", $where );
  //bw_trace2();
  return( $where );
}

/**
 * Override the default query for Media
 *
 * Implement "nav_menu_meta_box_object" to set the post_status to "inherit" when the
 * object type is 'attachment'.
 *
 * Note: This code completely overrides the _default_query array.
 *
 * @param object $object
 * @return filtered object 
 * 
 */
function oik_types_nav_menu_meta_box_object( $object=null ) {
	//bw_trace2();
	if ( isset( $object->name ) && 'attachment' == $object->name ) {
	  $object->_default_query = array(
        'post_status' => 'inherit',
      ); 
	}
	return( $object );
}  

/**
 * Function performed when oik-types.php is loaded 
 * 
 * 
 */
function oikcpt_plugin_loaded() {
  //bw_trace2();
  add_action( "oik_fields_loaded", "oikcpt_fields_loaded", 11 );
  add_action( "oik_fields_loaded", "oikfie_fields_loaded", 12 );
  add_action( "oik_fields_loaded", "oiktax_fields_loaded", 13 );
  add_action( "oik_fields_loaded", "oikf2t_fields_loaded", 14 );
  add_action( "oik_fields_loaded", "oikx2t_fields_loaded", 15 );
  add_action( "oik_admin_menu", "oikcpt_admin_menu" );
  add_action( "admin_notices", "oik_types_activation" );
  add_action( "pre_get_posts", "oik_types_pre_get_posts" );
	add_action( "pre_get_posts", "oik_types_pre_get_posts_for_archive", 11 );
	add_filter( "posts_orderby", "oik_types_posts_orderby", 10, 2 ); 
	add_action( "setup_theme", "oik_types_setup_theme" );
	add_action( "run_oik-types.php", "oik_types_run_oik_types" );
}

/**
 * Implement "setup_theme" for oik-types
 *
 * Defer registering our hook for 'register_post_type_args' since we need to ensure that $wp_rewrite
 * has been initialised. 
 * 
 * See TRAC 36579
 */
function oik_types_setup_theme() {
	add_filter( "register_post_type_args", "oik_types_register_post_type_args", 10, 2 );
}

/**
 * Implement "register_post_type_args" filter for oik-types
 *
 * The 'register_post_type_args filter was introduced in WordPress 4.4
 * If the site it 4.4 or higher then this gets invoked for each post type being registered
 * So we can update the registration earlier rather than later.
 *
 * @param array $args post type args
 * @param string $post_type the post type being registered
 * @return array updated post type args array
 */
function oik_types_register_post_type_args( $args, $post_type ) {
	//bw_backtrace();
	static $bw_types = null;
	if ( !$bw_types ) {
		$bw_types = get_option( "bw_types" );
		bw_trace2( $bw_types, "bw_types", true, BW_TRACE_DEBUG );
	}
	if ( $bw_types ) { // && is_array( $bw_types) && count( $bw_types ) )
		$oik_types_override = bw_array_get( $bw_types, $post_type, null );
		if ( $oik_types_override ) {
			$override_args = oikcpt_adjust_args( $oik_types_override['args'], false, $args );
			bw_trace2( $override_args, "override_args", true, BW_TRACE_VERBOSE );
			$args = array_merge( $args, $override_args );
		}
	}	
	return( $args );
}

/**
 * Implement "pre_get_posts" action for oik-types for archive pages
 *
 * There should be no need to access "bw_types" since the fields should
 * already have been copied to the post_type definition. 
 * Confirm this! 
 * 
 * @param object $query Instance of WP_Query
 */
function oik_types_pre_get_posts_for_archive( $query ) {
	if ( $query->is_main_query() ) {
	
		if ( $query->is_archive() ) { 
			$archive_posts_per_page = null;
			$post_type = bw_array_get( $query->query, 'post_type', null );
			if ($post_type ) {
				$archive_posts_per_page = oik_types_get_archive_posts_per_page( $post_type );
			} elseif ( $query->is_tax() || $query->is_category() ) {
				$archive_posts_per_page = oik_types_get_archive_posts_per_page_for_taxonomy( $query );
			}
			
			if ( $archive_posts_per_page ) {
				$query->set( 'posts_per_page', $archive_posts_per_page );
			}
			
		} else {
			// Not archive so nothing to do.
		}	
	}	
}

/**
 * Retrieves archive_posts_per_page if set.
 *
 * @param string $post_type
 * @return null|integer
 */
function oik_types_get_archive_posts_per_page( $post_type ) {	
	$archive_posts_per_page = null;
	//bw_trace2( $post_type, "post_type");
	$post_type_object = get_post_type_object( $post_type );
	//bw_trace2( $post_type_object, "post_type_object", false );
	if ( property_exists( $post_type_object, "archive_posts_per_page" ) ) {
		$archive_posts_per_page = $post_type_object->archive_posts_per_page;
	}
	return( $archive_posts_per_page );
}

/**
 * Returns archive_posts_per_page for a taxonomy query
 *
 * @TODO Implement logic to determine taxonomy and the post types its associated to
 * 
 * @param object $query the WP_Query object
 * @return null|integer 
 */
function oik_types_get_archive_posts_per_page_for_taxonomy( $query ) {
	$taxonomy_archive_posts_per_page = null;
	$post_types = oik_types_get_involved_taxonomies_post_types( $query );
	foreach ( $post_types as $post_type ) {
		$archive_posts_per_page = oik_types_get_archive_posts_per_page( $post_type );
		if ( $archive_posts_per_page && $archive_posts_per_page > $taxonomy_archive_posts_per_page ) {
			$taxonomy_archive_posts_per_page = $archive_posts_per_page;
		} 
	}
	return( $taxonomy_archive_posts_per_page );
}

/**
 * Returns array of post_types involved with the taxonomies
 * 
 * We know it's a taxonomy query so we find which post types are associated with each taxonomy and return the set.
 * Note: The WP_Tax_Query object applies to both tags and categories. 
 
     [tax_query] => WP_Tax_Query Object
        (
            [queries] => Array
                (
                    [0] => Array
                        (
                            [taxonomy] => letters
                            [terms] => Array
                                (
                                    [0] => 539
                                )

                            [field] => slug
                            [operator] => IN
                            [include_children] => 1
                        )

                )

            [relation] => AND
            [table_aliases:protected] => Array
                (
                )

            [queried_terms] => Array
                (
                    [letters] => Array
                        (
                            [terms] => Array
                                (
                                    [0] => 539
                                )

                            [field] => slug
                        )

                )

            [primary_table] => 
            [primary_id_column] => 
        )

 * 
 * 
 * @param object $query WP_Query object
 * @return array - may be empty
 */ 
function oik_types_get_involved_taxonomies_post_types( $query ) {
	$post_types = array();
	$queried_terms = $query->tax_query->queried_terms;
	foreach ( $queried_terms as $taxonomy => $data ) {
		$taxonomy_object = get_taxonomy( $taxonomy );
		bw_trace2( $taxonomy_object, "taxonomy_object", false );
		$post_types += $taxonomy_object->object_type;
	}
	bw_trace2( $post_types, "post_types", false );

	return( $post_types );
}

/**
 * Order front-end archives by post title  
 * 
 * @TODO Consider what to do for "posts"
 * @TODO Use the setting defined for the post type / taxonomy
 *
 * @param string $orderby - current value of orderby
 * @param object $query - a WP_Query object
 * @return string the orderby we want
 */
function oik_types_posts_orderby( $orderby, $query ) {
	//bw_backtrace();
	//bw_trace2();
	global $wpdb;
	if ( !is_admin() ) {
		if ( $query->is_post_type_archive() ) {
			$orderby = "$wpdb->posts.post_title asc";
		}
		if ( $query->is_tax() ) {
			$orderby = "$wpdb->posts.post_title asc";
		}
		if ( $query->is_category() ) {
			$orderby = "$wpdb->posts.post_title asc";
		}
	}
	//$post_type = $query->query['post_type'];
	//bw_trace2( $post_type, "post_type" );
	return( $orderby );
}

/**
 * Run "oik-types.php" in batch
 */
function oik_types_run_oik_types() {
	oik_require( "admin/oik-types-cli.php", "oik-types" );
	oik_types_lazy_run_oik_types();
		
}



