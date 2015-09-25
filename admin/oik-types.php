<?php // (C) Copyright Bobbing Wide 2013-2015

/**
 * oik-types - Custom Post Types page
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                       Validate? Perform action        Display preview Display add  Display edit Display select list
 * ------------------------------- --------  -------------------   --------------- ------------ ------------ -------------------
 * preview_type                    No        n/a                   Yes             -            -            -
 * delete_type                     No        delete selected type  -               -            -            Yes
 * edit_type                       No        n/a                   -               -            Yes          Yes
 *
 * _oik_cpt_edit_type         Yes       update selected type  -               -            Yes          Yes
 * _oik_cpt_add_type
 * _oik_cpt_add_oik_cpt
 * 
 * 
 */
function oikcpt_lazy_types_do_page() {
  oik_menu_header( "types", "w100pc" );
  $validated = false;
  
  $preview_type = bw_array_get( $_REQUEST, "preview_type", null );
  $delete_type = bw_array_get( $_REQUEST, "delete_type", null );
  $edit_type = bw_array_get( $_REQUEST, "edit_type", null );
  
  /** These codes override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_cpt_edit_type = bw_array_get( $_REQUEST, "_oik_cpt_edit_type", null );
  $oik_cpt_add_oik_cpt = bw_array_get( $_REQUEST, "_oik_cpt_add_oik_cpt", null );
  $oik_cpt_add_type = bw_array_get( $_REQUEST, "_oik_cpt_add_type", null );
  if ( $oik_cpt_add_type || $oik_cpt_add_oik_cpt ) {
    $preview_type = null;
    $delete_type = null;
    $edit_type = null; 
  }  
  
  
  if ( $preview_type ) {
    oik_box( NULL, NULL, "Preview", "oik_cpt_preview" );
  } 
  
  if ( $delete_type ) { 
    _oik_cpt_delete_type( $delete_type );
  }  

  if ( $edit_type ) {
    bw_build_overridden_type( $edit_type );
  }
  if ( $oik_cpt_edit_type ) {  
    $validated = _oik_cpt_type_validate( false );
  }  
  
  if ( $oik_cpt_add_oik_cpt ) {
    $validated = _oik_cpt_type_validate( true );
  }
  
  if ( $oik_cpt_add_type || ( $oik_cpt_add_oik_cpt && !$validated )  ) {
    oik_box( NULL, NULL, "Add new", "oik_cpt_add_oik_cpt" );
  }
  
  if ( $edit_type || $oik_cpt_edit_type || $validated ) {
    oik_box( null, null, "Edit type", "oik_cpt_edit_type" );
  }
  oik_box( NULL, NULL, "types", "oik_cpt_types" );
  //oik_box( NULL, NULL, "registered types", "oik_cpt_registered_types" );
  oik_box( null, null, "registered types", "oik_cpt_registered_types" );
  oik_menu_footer();
  bw_flush();
}

/**
 * Build the overridden post_type definition
 * 
 * The registered type contains the values that have been set by the plugins and even this plugin during "init"
 * Now we want to display these values so that they can be overridden.
 * The $bw_types values trump any original values.
 * BUT it's not just a simple case of array_merge() since this will not remove items from the "supports" array.
 * So we replace this array if the $bw_override_type array is not empty. 
 *  
 * @param string $edit_type - the post_type being updated
 * 
 * @global $bw_type - the values for the overridden type
 */
function bw_build_overridden_type( $edit_type ) {
  global $bw_type;
  $bw_types = get_option( "bw_types" );
  $bw_type_regs = oikcpt_get_registered_type_args( $edit_type );
  $bw_type_override = bw_array_get( $bw_types, $edit_type, null );
  if ( $bw_type_override ) { 
    bw_trace2( $bw_type_override, "bw_type_override" );
    $bw_type = array_merge( $bw_type_regs, $bw_type_override );
    $bw_type['args']['supports'] = bw_array_get( $bw_type_override['args'], "supports", array() );
    //$bw_type = $bw_type_override;    // array_merge()
  } else {
    $bw_type = $bw_type_regs;
  }    
  $bw_type['args']['type'] = $edit_type; 
  bw_trace2( $bw_type );
} 

/**
 * Return the registered post type's values as an args array
 *
 * Which then gets overridden by the values from the bw_types options
 * 
 * @return array with an 'args' index containing the values.
 */
function oikcpt_get_registered_type_args( $type ) {
  $post_type_object = get_post_type_object( $type );
  $post_type_array = (array) $post_type_object;
  // $supports = bw_array_get( $post_type_array, "supports", null );
  // $supports =  
  $supports = get_all_post_type_supports( $type );
  // bw_trace2( $supports );
  
  if ( empty( $supports ) ) {
    $post_type_array['supports'] = array( 'title', 'editor' );  
  } else {
    unset( $post_type_array['supports'] );
    foreach ( $supports as $support => $sval ) {
      // bw_trace2( $post_type_array );
      $post_type_array['supports'][] = $support;
    }  
  }  
  $post_type_args['args'] = $post_type_array;
  bw_trace2( $post_type_args, "registered post type" );
  return( $post_type_args );
}

/**
 * Return the singular name for the post_type, if defined
 */
function bw_return_singular_name( $args ) {
  $singular_name = bw_array_get( $args, "singular_name", null );
  if ( !$singular_name ) {
    $labels = bw_array_get( $args, 'labels', null );
    if ( $labels ) {
      $singular_name = bw_array_get( $labels, "singular_name", null );
    }  
  }
  return( $singular_name );
}   
 

/** 
 * Display a current type
 

  $post_type_args['label'] = 'todo';
  $post_type_args['description'] = 'todo list items';
  $post_type_args['hierarchical'] = true; 
  $post_type_args['has_archive]
  
 */
function _oik_cpt_type_row( $type, $data ) {
  bw_trace2();
  $row = array();
  $row[] = $type;
  $args = $data['args'];
  $fields = bw_array_get( $data, 'fields', null );
  $row[] = esc_html( stripslashes( $args['label'] ) ) . "&nbsp";
  $singular_name = bw_return_singular_name( $args );
  $row[] = esc_html( stripslashes( $singular_name ) ) . "&nbsp";
  $row[] = esc_html( stripslashes( $args['description'] ) ) . "&nbsp";  
  $row[] = icheckbox( "hierarchical[$type]", $args['hierarchical'], true );
  $args['has_archive'] = bw_array_get( $args, 'has_archive', null );
  $row[] = icheckbox( "has_archive[$type]", $args['has_archive'], true );
  $links = null;
  //$row[] = icheckbox( "expand[$type]", $expand, true );
  //$links = retlink( null, admin_url("admin.php?page=oik_types&amp;preview_type=$type"), "Preview" );
  //$links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_types&amp;delete_type=$type"), "Delete" ); 
  $links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_types&amp;edit_type=$type"), "Edit" );   
  $row[] = $links;
  bw_tablerow( $row );
}

/**
 * Display the table of oik custom post types
 * 
 */
function _oik_cpt_type_table() {
  $bw_types = get_option( "bw_types" );
  if ( is_array( $bw_types) && count( $bw_types )) {
    foreach ( $bw_types as $type => $data ) {
      //$type = bw_array_get( $bw_type, "type", null );
      _oik_cpt_type_row( $type, $data );
    }
  }  
}

/**
 * Display the table of registered post types
 * 
 */
function _oik_cpt_registered_table() {
  // $bw_types = get_option( "bw_types" );
  global $wp_post_types;
  $bw_types = $wp_post_types;
  bw_trace2( $bw_types );

  
  if ( is_array( $bw_types) && count( $bw_types )) {
    foreach ( $bw_types as $type => $data ) {
      //$type = bw_array_get( $bw_type, "type", null );
      //$type_array = (array) $data ;
      $data_array['args'] = (array) $data; 
      _oik_cpt_type_row( $type, $data_array );
    }
  }  
}


/**
 * Check if it already exists as a post_type
 *
 * If not then add to the options using bw_update_option() 
 * then empty out the type field for the next one
 *
 */
function _oik_cpt_add_oik_cpt( $bw_type ) {
  $type = bw_array_get( $bw_type['args'], "type", null );
  $type_exists = post_type_exists( $type ); 
  if ( $type_exists ) {
    p( "Type $type already defined, try another type" );   
    $ok = false;

  } else {
    unset( $bw_type['args']['type'] );
    bw_update_option( $type, $bw_type, "bw_types" );
    // We don't need to add the type now! 
    $bw_type['args']['type'] = "";
    $ok = true;
  }
  return( $ok ); 
}

function _oik_cpt_update_type( $bw_type ) {
  $type = bw_array_get( $bw_type['args'], "type", null );
  if ( $type ) { 
    unset( $bw_type['args']['type'] );
    bw_update_option( $type, $bw_type, "bw_types" );
  } else {
    bw_trace2( $type, "Logic error?" );
  }  
}

function _oik_cpt_delete_type( $bw_type ) {
  bw_delete_option( $bw_type, "bw_types" );
}  


/**
 * type must not be blank
 */
function oik_diy_validate_type( $type ) {
  $valid = isset( $type );
  if ( $valid ) { 
    $type = trim( $type );
    $valid = strlen( $type ) > 0;
    $valid &= strlen( $type ) < 20;
  } 
  if ( !$valid ) { 
    p( "Type must not be blank" );   
  }  
  return $valid;
}
    
/**
 * Validate the oik custom post type definition
 */
function _oik_cpt_type_validate( $add_type=true ) {

  global $bw_type;
  $bw_type['args']['type'] = bw_array_get( $_REQUEST, "type", null );
  $bw_type['args']['label'] = bw_array_get( $_REQUEST, "label", null );
  $bw_type['args']['singular_name'] = bw_array_get( $_REQUEST, "singular_name", null );
  $bw_type['args']['description'] = bw_array_get( $_REQUEST, "description", null );
  $bw_type['args']['hierarchical'] = bw_array_get( $_REQUEST, "hierarchical", null );
  $bw_type['args']['has_archive'] = bw_array_get( $_REQUEST, "has_archive", null );
  $bw_type['args']['title'] = bw_array_get( $_REQUEST, "title", null );
  $bw_type['args']['public'] = bw_array_get( $_REQUEST, "public", null );
  $bw_type['args']['exclude_from_search'] = bw_array_get( $_REQUEST, "exclude_from_search", null );
  $bw_type['args']['publicly_queryable'] = bw_array_get( $_REQUEST, "publicly_queryable", null );
  $bw_type['args']['show_ui'] = bw_array_get( $_REQUEST, "show_ui", null );
  $bw_type['args']['show_in_nav_menus'] = bw_array_get( $_REQUEST, "show_in_nav_menus", null );
  $bw_type['args']['show_in_menu'] = bw_array_get( $_REQUEST, "show_in_menu", null );
  $bw_type['args']['show_in_admin_bar'] = bw_array_get( $_REQUEST, "show_in_admin_bar", null );
  $bw_type['args']['rewrite'] = bw_array_get( $_REQUEST, "rewrite", null );
  
  $bw_type['args']['supports'] = bw_array_get( $_REQUEST, "supports", null );

  bw_trace2( $bw_type, "bw_type" );
  
  $ok = oik_diy_validate_type( $bw_type['args']['type'] );
  
  // validate the fields and add the type IF it's OK to add
  // $add_type = bw_array_get( $_REQUEST, "_oik_cpt_add_oik_cpt", false );
  if ( $ok ) {
    if ( $add_type ) {
      $ok = _oik_cpt_add_oik_cpt( $bw_type );  
    } else {
      $ok = _oik_cpt_update_type( $bw_type );
    }
  }  
  return( $ok );
}

/**
 * Display the table of oik custom post types
 */
function oik_cpt_types() {
  p( "" );
  bw_form();
  stag( "table", "widefat" );
  stag( "thead");
  bw_tablerow( array( "Type", "Plural", "Singular", "Description", "Hierarchical?", "Archive?", "Actions" ));
  etag( "thead");
  _oik_cpt_type_table();
  etag( "table" );
  p( isubmit( "_oik_cpt_add_type", "Add type", null, "button-primary" ) );
  etag( "form" );
  // bw_flush();
}

 
/**
 * Display the table of registered post types 
 * 
 * This table includes the oik custom post types.
 */
function oik_cpt_registered_types() {
  p( "" );
  bw_form();
  stag( "table", "widefat" );
  stag( "thead");
  bw_tablerow( array( "type", "Plural", "Singular", "Description", "Hierarchical?", "Archive?", "Actions" ));
  etag( "thead");
  _oik_cpt_registered_table();
  etag( "table" );
  //p( isubmit( "_oik_cpt_add_type", "Add type", null, "button-primary" ) );
  etag( "form" );
  // bw_flush();
} 

/**
 * Display form to add a custom post type
 */
function oik_cpt_add_oik_cpt( ) {
  global $bw_type;
  bw_form();
  stag( "table", "wide-fat" );
  bw_textfield( "type", 20, "Post type", $bw_type['args']['type'] );
  bw_textfield( "label", 20, "Plural label", $bw_type['args']['label'] );
  $singular_name = bw_return_singular_name( $bw_type['args'] );
  bw_textfield( "singular_name", 20, "Singular label", $singular_name );
  bw_textarea( "description", 100, "Description", stripslashes( $bw_type['args']['description'] ), 2 );
  oik_cpt_edit_type_fields( $bw_type );
  etag( "table" );
  p( isubmit( "_oik_cpt_add_oik_cpt", "Add new type", null, "button-primary" ) );
  etag( "form" );
}

/** 
 * Display post_type args
 * 
 * Used for add and edit fields. For the early version we only handle the "most important" fields; anything else can be created in a custom plugin.
 *
 * args supported/not supported - if not supported the default value is used
 * Y public
 * Y hierarchical
 * Y has_archive
 * Y exclude_from_search
 * Y publicly_queryable
 * Y show_ui 
 * Y show_in_nav_menus
 * Y show_in_menu
 * Y rewrite
 * Y supports array
 * Y show_in_admin_bar   added 2014/07/11
 * N menu_position
 * N menu_icon
 * N capability_type   default post
 * N capabilities array default edit, read, delete, edit_posts, edit_others_posts, publish_posts, read_private_posts etcetera
 * N map_meta_cap
 * 
If you assign a 'capability_type' and then take a look into the $GLOBALS['wp_post_types']['your_cpt_name'] array, then you'll see the following:

[cap] => stdClass Object
(
	[edit_post]		 => "edit_{$capability_type}"
	[read_post]		 => "read_{$capability_type}"
	[delete_post]		 => "delete_{$capability_type}"
	[edit_posts]		 => "edit_{$capability_type}s"
	[edit_others_posts]	 => "edit_others_{$capability_type}s"
	[publish_posts]		 => "publish_{$capability_type}s"
	[read_private_posts]	 => "read_private_{$capability_type}s"
        [delete_posts]           => "delete_{$capability_type}s"
        [delete_private_posts]   => "delete_private_{$capability_type}s"
        [delete_published_posts] => "delete_published_{$capability_type}s"
        [delete_others_posts]    => "delete_others_{$capability_type}s"
        [edit_private_posts]     => "edit_private_{$capability_type}s"
        [edit_published_posts]   => "edit_published_{$capability_type}s"
)
Note the "s" at the end of plural capabilities.

hierarchical
(boolean) (optional) Whether the post type is hierarchical (e.g. page). Allows Parent to be specified. The 'supports' parameter should contain 'page-attributes' to show the parent select box on the editor page.
Default: false
Note: this parameter was planned for Pages. Be careful, when choosing it for your custom post type - if you are planning to have many entries (say - over 100), you will run into memory issue. With this parameter set to true WordPress will fetch all entries of that particular post type, together with all meta data, on each administration page load for your post type.
supports
(array/boolean) (optional) An alias for calling add_post_type_support() directly. As of 3.5, boolean false can be passed as value instead of an array to prevent default (title and editor) behavior.
Default: title and editor
'title'
'editor' (content)
'author'
'thumbnail' (featured image, current theme must also support post-thumbnails)
'excerpt'
'trackbacks'
'custom-fields'
'comments' (also will see comment count balloon on edit screen)
'revisions' (will store revisions)
'page-attributes' (menu order, hierarchical must be true to show Parent option)
'post-formats' add post formats, see Post Formats
Note: When you use custom post type that use thumbnails remember to check that the theme also supports thumbnails or use add_theme_support function.
register_meta_box_cb
(string) (optional) Provide a callback function that will be called when setting up the meta boxes for the edit form. Do remove_meta_box() and add_meta_box() calls in the callback.
Default: None
taxonomies
(array) (optional) An array of registered taxonomies like category or post_tag that will be used with this post type. This can be used in lieu of calling register_taxonomy_for_object_type() directly. Custom taxonomies still need to be registered with register_taxonomy().
Default: no taxonomies
has_archive
(boolean or string) (optional) Enables post type archives. Will use $post_type as archive slug by default.
Default: false
Note: Will generate the proper rewrite rules if rewrite is enabled. Also use rewrite to change the slug used.
permalink_epmask
(string) (optional) The default rewrite endpoint bitmasks. For more info see Trac Ticket 12605 and this Make WordPress Plugins summary of endpoints.
Default: EP_PERMALINK
Note: In 3.4, this argument is effectively replaced by the 'ep_mask' argument under rewrite.
rewrite
(boolean or array) (optional) Triggers the handling of rewrites for this post type. To prevent rewrites, set to false.
Default: true and use $post_type as slug
$args array
'slug' => string Customize the permastruct slug. Defaults to the $post_type value. Should be translatable.
'with_front' => bool Should the permastruct be prepended with the front base. (example: if your permalink structure is /blog/, then your links will be: false->/news/, true->/blog/news/). Defaults to true
'feeds' => bool Should a feed permastruct be built for this post type. Defaults to has_archive value.
'pages' => bool Should the permastruct provide for pagination. Defaults to true
'ep_mask' => const As of 3.4 Assign an endpoint mask for this post type. For more info see Trac Ticket 19275 and this Make WordPress Plugins summary of endpoints.
If not specified and permalink_epmask is set, inherits from permalink_epmask
If not specified and permalink_epmask is not set, defaults to EP_PERMALINK
Note: If registering a post type inside of a plugin, call flush_rewrite_rules() in your activation and deactivation hook (see Flushing Rewrite on Activation below). If flush_rewrite_rules() is not used, then you will have to manually go to Settings > Permalinks and refresh your permalink structure before your custom post type will show the correct structure.
query_var
(boolean or string) (optional) Sets the query_var key for this post type.
Default: true - set to $post_type
'false' - Disables query_var key use. A post type cannot be loaded at /?{query_var}={single_post_slug}
'string' - /?{query_var_string}={single_post_slug} will work as intended.
Note: The query_var parameter has no effect if the ‘publicly_queryable’ parameter is set to false. query_var adds the custom post type’s query var to the built-in query_vars array so that WordPress will recognize it. WordPress removes any query var not included in that array.
If set to true it allow you to request a custom posts type (book) using this: example.com/?book=life-of-pi
If set to a string rather than true (for example ‘publication’), you can do: example.com/?publication=life-of-pi

can_export
(boolean) (optional) Can this post_type be exported.
Default: true
_builtin
(boolean) (not for general use) Whether this post type is a native or "built-in" post_type. Note: this Codex entry is for documentation - core developers recommend you don't use this when registering your own post type
Default: false
'false' - default this is a custom post type
'true' - this is a built-in native post type (post, page, attachment, revision, nav_menu_item)
_edit_link
(boolean) (not for general use) Link to edit an entry with this post type. Note: this Codex entry is for documentation '-' core developers recommend you don't use this when registering your own post type
Default:
'post.php?post=%d'
 */
function oik_cpt_edit_type_fields( $bw_type ) {

  bw_checkbox( "hierarchical", "Hierarchical type?", $bw_type['args']["hierarchical"] );
  bw_checkbox( "has_archive", "Has archive?", bw_array_get( $bw_type['args'], "has_archive", false) );
  bw_checkbox( "public", "Public", $bw_type['args']["public"] );
  bw_checkbox( "exclude_from_search", "Exclude from search", $bw_type['args']["exclude_from_search"] ); 
  bw_checkbox( "publicly_queryable", "Publicly queryable", $bw_type['args']["publicly_queryable"] ); 
  bw_checkbox( "show_ui", "Show UI", $bw_type['args']["show_ui"] ); 
  bw_checkbox( "show_in_nav_menus", "Show in nav menus", $bw_type['args']["show_in_nav_menus"] ); 
  bw_checkbox( "show_in_menu", "Show in menu", $bw_type['args']["show_in_menu"] ); 
  bw_checkbox( "show_in_admin_bar", "Show in admin bar", bw_array_get( $bw_type['args'], "show_in_admin_bar", true ) ); 
  // bw_checkbox( "rewrite", "Rewrite", $bw_type['args']["rewrite"] ); 
  oik_cpt_edit_rewrite( $bw_type['args']['supports'] ); 
  
  //bw_checkbox( "", "", $bw_type['args'][""] ); 
  //bw_checkbox( "", "", $bw_type['args'][""] ); 
  //bw_checkbox( "", "", $bw_type['args'][""] ); 
  //bw_checkbox( "", "", $bw_type['args'][""] ); 
  //bw_checkbox( "", "", $bw_type['args'][""] ); 
  //bw_checkbox( "", "", $bw_type['args'][""] ); 
  //bw_checkbox( "", "", $bw_type['args'][""] ); 
  oik_cpt_edit_supports( $bw_type['args']['supports'] );
  
}

/**
 * Display a multi-select box for "supports"
 *
 * The full set includes 
 * - the 'core' values
 * - the ones we know about
 * - the others that have been registered and didn't know about
 * 
 * {@see TRAC #34009}
 */
function oik_cpt_edit_supports( $supports ) {
  //bw_backtrace();
	add_filter( "oik_post_type_supports", "oik_cpt_oik_post_type_supports_core", 10 );
	add_filter( "oik_post_type_supports", "oik_cpt_oik_post_type_supports", 11 );
	add_filter( "oik_post_type_supports", "oik_cpt_oik_post_type_supports_unknown_registered", 12 );
	$supports_options = oik_cpt_get_all_post_type_supports();
	$count = count( $supports_options );
  bw_select( "supports", "Supports", $supports, array( "#options" => $supports_options, "#multiple" => $count ) );
}

/**
 * List all the currently registered post type supports options
 *
 * @return array all the supports options that could possibly be chosen 
 */
function oik_cpt_get_all_post_type_supports() {
	$supports_options = array();
	$supports_options = apply_filters( "oik_post_type_supports", $supports_options );
	bw_trace2( $supports_options, "supports_options", false, BW_TRACE_DEBUG );
	return( $supports_options );
}

/**
 * Return the 'core' options used in add_post_type_supports()
 *
 * The full set may include features that are only available for certain post types
 * 
 * @param array $supports_options
 * @return array The core set
 */
function oik_cpt_oik_post_type_supports_core( $supports_options ) {
	$supports_options['author'] = __( "Author" );
	$supports_options['comments'] = __( "Comments" );
	$supports_options['custom-fields'] = __( "Custom fields" );
	$supports_options['editor'] = __( "Content editor" );
	$supports_options['excerpt'] = __( "Excerpt" );
	$supports_options['page-attributes'] = __( "Page attributes" );
	$supports_options['post-formats'] = __( "Post formats" );
	$supports_options['revisions'] = __( "Revisions" );
	$supports_options['thumbnail'] = __( "Thumbnail - featured image" );
	$supports_options['trackbacks'] = __( "Trackbacks" );
  $supports_options['title'] = __( "Title" );
	return( $supports_options );
}

/**
 * Implement "oik_post_type_supports" for oik-types
 *
 * oik_cpt_oik_post_type_supports_unknown_registered() 
 * can find the post type supports that have been registered
 * but there's no way of knowing what post type supports could be available.
 * 
 * Until other plugins respond to this filter we'll add the ones we know about: 
 
 * - home ( oik-types )
 * - publicize ( Jetpack, oik-clone )
 * - genesis-seo ( Genesis Framework )
 * - genesis-scripts ( Genesis Framework )
 * - genesis-layouts ( Genesis Framework )
 * - genesis-cpt-archives-settings ( Genesis Framework )
 * 
 * Notes: 
 * - genesis-seo may get overridden by WordPress SEO
 * - genesis-cpt-archives-settings required has_archive
 * 
 * @param array $supports_options
 * @return array updated array
 */
function oik_cpt_oik_post_type_supports( $supports_options ) {
	$supports_options[ 'publicize' ] = "Publicize with Jetpack";
	$supports_options[ 'home'] = "Display in blog home page";
	$supports_options[ 'genesis-layouts' ] = "Genesis layouts";
	$supports_options[ 'genesis-seo' ] = "Genesis SEO";
	$supports_options[ 'genesis-scripts' ] = "Genesis scripts";
	$supports_options[ 'genesis-cpt-archives-settings' ] = "Genesis CPT archives settings";
	bw_trace2( $supports_options, "supports_options" );
	return( $supports_options );	
}

/**
 * Implement "oik_post_type_supports" to add any remaining registered ones
 *
 * @param array $supports_options
 * @return array Updated with the 'unknown' values
 */
function oik_cpt_oik_post_type_supports_unknown_registered( $supports_options ) {
	global $_wp_post_type_features;
	foreach ( $_wp_post_type_features as $post_type => $features ) {
		foreach ( $features as $key => $value ) {
			if ( !isset( $supports_options[ $key ] ) ) {
				$supports_options[ $key ] = $key;
			}
		}
	}
	return( $supports_options );
}

/**
 * Display the rewrite array values
 * 
             [rewrite] => Array
                (
                    [slug] => oik_presentation
                    [with_front] => 1
                    [pages] => 1
                    [feeds] => 
                    [ep_mask] => 1
                )

 * 
 */
function oik_cpt_edit_rewrite( $rewrite ) {
  bw_trace2(); 
}

/** 
 * Display the Edit type form
 *
 */
function oik_cpt_edit_type( ) {
  global $bw_type;
  bw_form();
  stag( "table", "wide-fat" );
  bw_tablerow( array( "type", $bw_type['args']['type'] . ihidden( 'type', $bw_type['args']['type']) ) );
  bw_textfield( "label", 30, "Plural label", stripslashes( $bw_type['args']['label'] ) );
  $singular_name = bw_return_singular_name( $bw_type['args'] ); 
  bw_textfield( "singular_name", 30, "Singular label", stripslashes( $singular_name ) );
  bw_textarea( "description", 100, "Description", stripslashes( $bw_type['args']['description'] ), 2 );
  oik_cpt_edit_type_fields( $bw_type );
  etag( "table" );
  p( isubmit( "_oik_cpt_edit_type", "Change type", null, "button-primary" ) );
  etag( "form" );
}

/**
 * Preview the post type - whatever that means
 */
function oik_cpt_preview() {
  oik_require( "includes/oik-sc-help.inc" );
  $preview_type = bw_array_get( $_REQUEST, "preview_type", null );
  if ( $preview_type ) {
    sdiv( "oik_preview");
    //bw_invoke_type( $preview_type, null, "Preview of the $preview_type type" );
    p( "Preview not yet implemented" );
    ediv( "oik_preview");
  }
}

if ( !function_exists( "bw_update_option" ) ) {
/** Set the value of an option field in the options group
 *
 * @param string $field the option field to be set
 * @param mixed $value the value of the option
 * @param string $options - the name of the option field
 * @return mixed $value
 *
 * Parms are basically the same as for update_option
 */
function bw_update_option( $field, $value=NULL, $options="bw_options" ) {
  $bw_options = get_option( $options );
  $bw_options[ $field ] = $value;
  bw_trace2( $bw_options );
  update_option( $options, $bw_options );
  return( $value );
}
}

/** Remove an option field from a set
 *
 * @param string $field the option field to be removed
 * @param string $options - the name of the options set
 * @return mixed $value - current values for the options
 *
 */
if ( !function_exists( "bw_delete_option" ) ) {
function bw_delete_option( $field, $options="bw_options" ) {
  $bw_options = get_option( $options );
  unset( $bw_options[ $field ] );
  // bw_trace2( $bw_options );
  update_option( $options, $bw_options );
  return( $options );
}
}


