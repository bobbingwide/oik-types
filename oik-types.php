<?php
/*
Plugin Name: oik-types 
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-types
Description: oik types - custom post types, fields and taxonomies UI
Depends: oik base plugin, oik fields
Version: 1.9.0
Author: bobbingwide
Author URI: http://www.oik-plugins.com//author/bobbingwide
License: GPL2

    Copyright 2013-2015 Bobbing Wide (email : herb@bobbingwide.com )

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
 */
function oikcpt_adjust_args( $data_args, $cast=true ) {
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
     $args[$key] = $data;
  }
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
  //bw_trace2();
  $args = oikcpt_adjust_args( $data["args"], true );
  $type_exists = post_type_exists( $type ); 
  if ( $type_exists ) {
    bw_update_post_type( $type, $args ); 
  } else {
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
  //bw_trace2( $_wp_post_type_features[$type] );
  $features = get_all_post_type_supports( $type );
  //bw_trace2( $features, "features", false );
  if ( count( $features) ) {
    foreach ( $features as $feature => $fval ) {
      //bw_trace2( $feature, "feature", false );
      remove_post_type_support( $type, $feature );
    }
  }    
  add_post_type_support( $type, $value );
  //bw_trace2( $_wp_post_type_features[$type], "ptf after", false );
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
	//bw_trace2();
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
	//bw_trace2( $post_type_object, "after", false );
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
 * - You can't check for main query in "pre_get_posts"!
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
 * @param array $this - 
 * @return string the updated where clause
 */
function oik_types_posts_where( $where, $this ) {
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
  add_filter( "pre_get_posts", "oik_types_pre_get_posts" );
}

oikcpt_plugin_loaded();

