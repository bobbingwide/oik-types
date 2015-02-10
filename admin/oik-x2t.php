<?php // (C) Copyright Bobbing Wide 2013

/**
 * Taxonomies to Types mapping page
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                       Validate? Perform action        Display preview Display add  Display edit Display select list
 * ------------------------------- --------  -------------------   --------------- ------------ ------------ -------------------
 * preview_x2t                    No        n/a                   Yes             -            -            -
 * delete_x2t                     No        delete selected x2t  -               -            -            Yes
 * edit_x2t                       No        n/a                   -               -            Yes          Yes
 *
 * _oik_x2t_edit_x2t         Yes       update selected x2t  -               -            Yes          Yes
 * _oik_x2t_add_x2t
 * _oik_x2t_add_oik_x2t
 * 
 * 
*/
function oikx2t_lazy_do_page() {
  oik_menu_header( "Taxonomies to Types", "w100pc" );
  $validated = false;
  
  $preview_x2t = bw_array_get( $_REQUEST, "preview_x2t", null );
  $delete_x2t = bw_array_get( $_REQUEST, "delete_x2t", null );
  $edit_x2t = bw_array_get( $_REQUEST, "edit_x2t", null );
  
  /** These codes override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_x2t_edit_x2t = bw_array_get( $_REQUEST, "_oik_x2t_edit_x2t", null );
  $oik_x2t_add_oik_x2t = bw_array_get( $_REQUEST, "_oik_x2t_add_oik_x2t", null );
  $oik_x2t_add_x2t = bw_array_get( $_REQUEST, "_oik_x2t_add_x2t", null );
  if ( $oik_x2t_add_x2t || $oik_x2t_add_oik_x2t ) {
    $preview_x2t = null;
    $delete_x2t = null;
    $edit_x2t = null; 
  }  
  
  
  if ( $preview_x2t ) {
    oik_box( NULL, NULL, "Preview", "oik_x2t_preview" );
  } 
  
  if ( $delete_x2t ) { 
    _oik_x2t_delete_x2t( $delete_x2t );
  }  

  if ( $edit_x2t ) {
    global $bw_x2t;
    $bw_x2ts = get_option( "bw_x2ts" );
    $bw_x2t = bw_array_get( $bw_x2ts, $edit_x2t, null );
    $bw_x2t['args']['x2t'] = $edit_x2t; 
    bw_trace2( $bw_x2t );
  }
  if ( $oik_x2t_edit_x2t ) {  
    $validated = _oik_x2t_x2t_validate( false );
  }  
  
  if ( $oik_x2t_add_oik_x2t ) {
    $validated = _oik_x2t_x2t_validate( true );
  }
  
  if ( $oik_x2t_add_x2t || ( $oik_x2t_add_oik_x2t && !$validated )  ) {
    oik_box( NULL, NULL, "Add new", "oik_x2t_add_oik_x2t" );
  }
  
  if ( $edit_x2t || $oik_x2t_edit_x2t || $validated ) {
    // oik_box( null, null, "Edit relationship", "oik_x2t_edit_x2t" );
  }
  oik_box( NULL, NULL, "Taxonomies to types relationships", "oik_x2t_x2ts" );
  oik_menu_footer();
  bw_flush();
}

/** 
 * Display a current x2t mapping
 */
function _oik_x2t_x2t_row( $x2t, $data ) {
  bw_trace2();
  $row = array();
  // $row[] = $x2t;
  $args = $data['args'];
  //$Taxonomies = bw_array_get( $data, 'Taxonomies', null );
  $row[] = esc_html( stripslashes( $args['type'] ) ) . "&nbsp";
  $row[] = esc_html( stripslashes( $args['taxonomy'] ) ) . "&nbsp";  
  //$row[] = icheckbox( "hierarchical[$x2t]", $args['hierarchical'], true );
  //$row[] = icheckbox( "expand[$x2t]", $expand, true );
  $links = null;
  //$links = retlink( null, admin_url("admin.php?page=oik_x2t&amp;preview_x2t=$x2t"), "Preview" );
  //$links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_x2t&amp;delete_x2t=$x2t"), "Delete" ); 
  $links .= "&nbsp;";
  // $links .= retlink( null, admin_url("admin.php?page=oik_x2t&amp;edit_x2t=$x2t"), "Edit" );   
  $row[] = $links;
  bw_tablerow( $row );
}

/**
 * Display the table of Taxonomy to Type relationships
 * 
 */
function _oik_x2t_x2t_table() {
  $bw_x2ts = get_option( "bw_x2ts" );
  if ( is_array( $bw_x2ts) && count( $bw_x2ts )) {
    foreach ( $bw_x2ts as $x2t => $data ) {
      //$x2t = bw_array_get( $bw_x2t, "x2t", null );
      _oik_x2t_x2t_row( $x2t, $data );
    }
  }  
}

/** 
 * @TODO - implement logic to prevent the relationship being added multiple times
 * 
 */
function oik_x2t_check_relationship_exists( $x2t )  {
  $x2t_exists = bw_get_option( $x2t, "bw_x2ts" );
  return( $x2t_exists );
}  

/**
 * Check if the Taxonomy to post relationship already exists
 *
 * If not then add to the options using bw_update_option() 
 * then empty out the x2t field for the next one
 *
 */
function _oik_x2t_add_oik_x2t( $bw_x2t ) {
  $x2t = bw_array_get( $bw_x2t['args'], "x2t", null );
  $x2t_exists = oik_x2t_check_relationship_exists( $x2t ); 
  if ( $x2t_exists ) {
    p( "Relationship already defined, try another Type or Taxonomy" );   
    $ok = false;

  } else {
    unset( $bw_x2t['args']['x2t'] );
    bw_update_option( $x2t, $bw_x2t, "bw_x2ts" );
    // We don't need to add the x2t now! 
    $bw_x2t['args']['x2t'] = "";
    $ok = true;
  }
  return( $ok ); 
}

/**
 * Update the taxonomy to type relationship
 */
function _oik_x2t_update_x2t( $bw_x2t ) {
  $x2t = bw_array_get( $bw_x2t['args'], "x2t", null );
  if ( $x2t ) { 
    unset( $bw_x2t['args']['x2t'] );
    bw_update_option( $x2t, $bw_x2t, "bw_x2ts" );
  } else {
    bw_trace2( $x2t, "Logic error?" );
  }  
}

/**
 * Delete the taxonomy to type relationship
 */
function _oik_x2t_delete_x2t( $bw_x2t ) {
  bw_delete_option( $bw_x2t, "bw_x2ts" );
}  


/**
 * x2t must not be blank
 */
function oik_diy_validate_x2t( $x2t ) {
  $valid = isset( $x2t );
  if ( $valid ) { 
    $x2t = trim( $x2t );
    $valid = strlen( $x2t ) > 0;
  } 
  if ( !$valid ) { 
    p( "x2t must not be blank" );   
  }  
  return $valid;
}
    
/**
 * Validate the taxonomy to type relationship
 */
function _oik_x2t_x2t_validate( $add_x2t=true ) {

  global $bw_x2t;
  $bw_x2t['args']['type'] = bw_array_get( $_REQUEST, "type", null );
  $bw_x2t['args']['taxonomy'] = bw_array_get( $_REQUEST, "taxonomy", null );
  
  $bw_x2t['args']['x2t'] = $bw_x2t['args']['type'] . "." .  $bw_x2t['args']['taxonomy'];   
  // $bw_x2t['args']['hierarchical'] = bw_array_get( $_REQUEST, "hierarchical", null );
  // $bw_x2t['args']['title'] = bw_array_get( $_REQUEST, "title", null );
  
  bw_trace2( $bw_x2t, "bw_x2t" );
  
  $ok = oik_diy_validate_x2t( $bw_x2t['args']['x2t'] );
  
  // validate the fields and add the x2t IF it's OK to add
  // $add_x2t = bw_array_get( $_REQUEST, "_oik_x2t_add_oik_x2t", false );
  if ( $ok ) {
    if ( $add_x2t ) {
      $ok = _oik_x2t_add_oik_x2t( $bw_x2t );  
    } else {
      $ok = _oik_x2t_update_x2t( $bw_x2t );
    }
  }  
  return( $ok );
}


/**
 * Display the table of existing Taxonomy to type relationships
 * 
 * This may be extended to include custom taxonomies and categories as well
 * - which will require the tag or category to be a custom field name **?**
 *
 */
function oik_x2t_x2ts() {
  p( "" );
  bw_form();
  stag( "table", "widefat" );
  stag( "thead");
  bw_tablerow( array( "Type", "Taxonomy", "Actions" ));
  etag( "thead");
  _oik_x2t_x2t_table();
  etag( "table" );
  p( isubmit( "_oik_x2t_add_x2t", "Add relationship", null, "button-primary" ) );
  etag( "form" );
} 

/**
 * Return a list of taxonomies
 */
function bw_list_taxonomies() {
  $taxonomies = get_taxonomies( null, 'names' ); 
  return( $taxonomies );
}

function oik_x2t_add_oik_x2t( ) {
  global $bw_x2t;
  bw_form();
  stag( "table", "wide-fat" );
  $types = bw_list_registered_post_types();
  bw_select( "type", "Type", null, array( "#options" => $types ) ); 
  $taxonomies = bw_list_taxonomies();
  bw_select( "taxonomy", "Taxonomy", null, array( "#options" => $taxonomies )) ; 
  etag( "table" );
  p( isubmit( "_oik_x2t_add_oik_x2t", "Add new taxonomy to type", null, "button-primary" ) );
  etag( "form" );
}

/**
 * Edit the taxonomy to type relationship
 */
function oik_x2t_edit_x2t( ) {
  global $bw_x2t;
  bw_form();
  stag( "table", "wide-fat" );
  
  bw_tablerow( array( "Relationship", $bw_x2t['args']['x2t'] . ihidden( 'x2t', $bw_x2t['args']['x2t']) ) );
  //bw_textfield( "x2t", 20, "Post x2t", $bw_x2t['args']['x2t'] );
  //bw_textfield( "type", 30, "Type", stripslashes( $bw_x2t['args']['type'] ) );
  //$field = esc_textarea( $bw_x2t['args']['field'] );
  //bw_trace2( $field, "esc_textarea field", false );
  bw_textfield( "taxonomy", 100, "taxonomy", stripslashes( $bw_x2t['args']['taxonomy'] ) );
  //bw_checkbox( "hierarchical", "Hierarchical x2t?", $bw_x2t['args']["hierarchical"] );
  etag( "table" );
  p( isubmit( "_oik_x2t_edit_x2t", "Change relationship", null, "button-primary" ) );
  etag( "form" );
}

/**
 * View the taxonomy to type relationship
 */
function oik_x2t_preview() {
  oik_require( "includes/oik-sc-help.inc" );
  $preview_x2t = bw_array_get( $_REQUEST, "preview_x2t", null );
  if ( $preview_x2t ) {
    sdiv( "oik_preview");
    //bw_invoke_x2t( $preview_x2t, null, "Preview of the $preview_x2t x2t" );
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


