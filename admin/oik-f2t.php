<?php // (C) Copyright Bobbing Wide 2013

/**
 * Fields to Types mapping page
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                       Validate? Perform action        Display preview Display add  Display edit Display select list
 * ------------------------------- --------  -------------------   --------------- ------------ ------------ -------------------
 * preview_f2t                    No        n/a                   Yes             -            -            -
 * delete_f2t                     No        delete selected f2t  -               -            -            Yes
 * edit_f2t                       No        n/a                   -               -            Yes          Yes
 *
 * _oik_f2t_edit_f2t         Yes       update selected f2t  -               -            Yes          Yes
 * _oik_f2t_add_f2t
 * _oik_f2t_add_oik_f2t
 * 
 * 
*/
function oikf2t_lazy_do_page() {
  oik_menu_header( "Fields to Types", "w100pc" );
  $validated = false;
  
  $preview_f2t = bw_array_get( $_REQUEST, "preview_f2t", null );
  $delete_f2t = bw_array_get( $_REQUEST, "delete_f2t", null );
  $edit_f2t = bw_array_get( $_REQUEST, "edit_f2t", null );
  
  /** These codes override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_f2t_edit_f2t = bw_array_get( $_REQUEST, "_oik_f2t_edit_f2t", null );
  $oik_f2t_add_oik_f2t = bw_array_get( $_REQUEST, "_oik_f2t_add_oik_f2t", null );
  $oik_f2t_add_f2t = bw_array_get( $_REQUEST, "_oik_f2t_add_f2t", null );
  if ( $oik_f2t_add_f2t || $oik_f2t_add_oik_f2t ) {
    $preview_f2t = null;
    $delete_f2t = null;
    $edit_f2t = null; 
  }  
  
  
  if ( $preview_f2t ) {
    oik_box( NULL, NULL, "Preview", "oik_f2t_preview" );
  } 
  
  if ( $delete_f2t ) { 
    _oik_f2t_delete_f2t( $delete_f2t );
  }  

  if ( $edit_f2t ) {
    global $bw_f2t;
    $bw_f2ts = get_option( "bw_f2ts" );
    $bw_f2t = bw_array_get( $bw_f2ts, $edit_f2t, null );
    $bw_f2t['args']['f2t'] = $edit_f2t; 
    bw_trace2( $bw_f2t );
  }
  if ( $oik_f2t_edit_f2t ) {  
    $validated = _oik_f2t_f2t_validate( false );
  }  
  
  if ( $oik_f2t_add_oik_f2t ) {
    $validated = _oik_f2t_f2t_validate( true );
  }
  
  if ( $oik_f2t_add_f2t || ( $oik_f2t_add_oik_f2t && !$validated )  ) {
    oik_box( NULL, NULL, "Add new", "oik_f2t_add_oik_f2t" );
  }
  
  if ( $edit_f2t || $oik_f2t_edit_f2t || $validated ) {
    // oik_box( null, null, "Edit relationship", "oik_f2t_edit_f2t" );
  }
  oik_box( NULL, NULL, "Fields to types relationships", "oik_f2t_f2ts" );
  oik_menu_footer();
  bw_flush();
}

/** 
 * Display a current f2t mapping
 */
function _oik_f2t_f2t_row( $f2t, $data ) {
  bw_trace2();
  $row = array();
  // $row[] = $f2t;
  $args = $data['args'];
  //$fields = bw_array_get( $data, 'fields', null );
  $row[] = esc_html( stripslashes( $args['type'] ) ) . "&nbsp";
  $row[] = esc_html( stripslashes( $args['field'] ) ) . "&nbsp";  
  $links = null;
  // $links .= retlink( null, admin_url("admin.php?page=oik_f2t&amp;preview_f2t=$f2t"), "Preview" );
  // $links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_f2t&amp;delete_f2t=$f2t"), "Delete" ); 
  $links .= "&nbsp;";
  //$links .= retlink( null, admin_url("admin.php?page=oik_f2t&amp;edit_f2t=$f2t"), "Edit" );   
  $row[] = $links;
  bw_tablerow( $row );
}

/**
 * Display the table of Field to Type relationships
 * 
 */
function _oik_f2t_f2t_table() {
  $bw_f2ts = get_option( "bw_f2ts" );
  if ( is_array( $bw_f2ts) && count( $bw_f2ts )) {
    foreach ( $bw_f2ts as $f2t => $data ) {
      //$f2t = bw_array_get( $bw_f2t, "f2t", null );
      _oik_f2t_f2t_row( $f2t, $data );
    }
  }  
}

/** 
 * Check if the relationship already exists
 * 
 */
function oik_f2t_check_relationship_exists( $f2t )  {
  global $bw_mapping;
  list( $type, $field ) = explode( ".", $f2t );
  bw_trace2( $bw_mapping );
  $exists = bw_array_get( $bw_mapping['field'], $type, false );
  if ( $exists ) {
    $exists = bw_array_get( $exists, $field, false );
  }  
  return( $exists );
}  

/**
 * Add a field to post relationship
 *
 * Check if the relationship already exists.
 * If not then add to the options using bw_update_option() 
 * then empty out the f2t field for the next one
 *
 */
function _oik_f2t_add_oik_f2t( $bw_f2t ) {
  $f2t = bw_array_get( $bw_f2t['args'], "f2t", null );
  $f2t_exists = oik_f2t_check_relationship_exists( $f2t ); 
  if ( $f2t_exists ) {
    p( "Relationship already defined, try another type or field" );   
    $ok = false;

  } else {
    unset( $bw_f2t['args']['f2t'] );
    bw_update_option( $f2t, $bw_f2t, "bw_f2ts" );
    // We don't need to add the f2t now! 
    $bw_f2t['args']['f2t'] = "";
    $ok = true;
  }
  return( $ok ); 
}

/**
 * Update the field to post relationship
 */
function _oik_f2t_update_f2t( $bw_f2t ) {
  $f2t = bw_array_get( $bw_f2t['args'], "f2t", null );
  if ( $f2t ) { 
    unset( $bw_f2t['args']['f2t'] );
    bw_update_option( $f2t, $bw_f2t, "bw_f2ts" );
  } else {
    bw_trace2( $f2t, "Logic error?" );
  }  
}

/**
 * Delete the field to post relationship 
 */
function _oik_f2t_delete_f2t( $bw_f2t ) {
  bw_delete_option( $bw_f2t, "bw_f2ts" );
}  


/**
 * f2t must not be blank
 */
function oik_diy_validate_f2t( $f2t ) {
  $valid = isset( $f2t );
  if ( $valid ) { 
    $f2t = trim( $f2t );
    $valid = strlen( $f2t ) > 0;
  } 
  if ( !$valid ) { 
    p( "f2t must not be blank" );   
  }  
  return $valid;
}
    
/**
 * Validate the field to type relationship
 */
function _oik_f2t_f2t_validate( $add_f2t=true ) {

  global $bw_f2t;
  $bw_f2t['args']['type'] = bw_array_get( $_REQUEST, "type", null );
  $bw_f2t['args']['field'] = bw_array_get( $_REQUEST, "field", null );
  
  $bw_f2t['args']['f2t'] = $bw_f2t['args']['type'] . "." .  $bw_f2t['args']['field'];   
  // $bw_f2t['args']['hierarchical'] = bw_array_get( $_REQUEST, "hierarchical", null );
  // $bw_f2t['args']['title'] = bw_array_get( $_REQUEST, "title", null );
  
  bw_trace2( $bw_f2t, "bw_f2t" );
  
  $ok = oik_diy_validate_f2t( $bw_f2t['args']['f2t'] );
  
  // validate the fields and add the f2t IF it's OK to add
  // $add_f2t = bw_array_get( $_REQUEST, "_oik_f2t_add_oik_f2t", false );
  if ( $ok ) {
    if ( $add_f2t ) {
      $ok = _oik_f2t_add_oik_f2t( $bw_f2t );  
    } else {
      $ok = _oik_f2t_update_f2t( $bw_f2t );
    }
  }  
  return( $ok );
}


/**
 * Display the table of existing field to type relationships
 * 
 * This may be extended to include custom taxonomies and categories as well
 * - which will require the tag or category to be a custom field name **?**
 *
 */
function oik_f2t_f2ts() {
  p( "" );
  bw_form();
  stag( "table", "widefat" );
  stag( "thead");
  bw_tablerow( array( "Type", "Field", "Actions" ));
  etag( "thead");
  _oik_f2t_f2t_table();
  etag( "table" );
  p( isubmit( "_oik_f2t_add_f2t", "Add relationship", null, "button-primary" ) );
  etag( "form" );
} 


/**
 * Display the form to add a field to post relationship
 */
function oik_f2t_add_oik_f2t( ) {
  global $bw_f2t;
  bw_form();
  stag( "table", "wide-fat" );
  // bw_textfield( "f2t", 20, "Post f2t", $bw_f2t['args']['f2t'] );
  // bw_textfield( "type", 20, "Type", $bw_f2t['args']['type'] );
  $types = bw_list_registered_post_types();
  bw_select( "type", "Type", null, array( "#options" => $types ) ); 
  // bw_textfield( "field", 20, "Field", stripslashes( $bw_f2t['args']['field'] ), 10 );
  $fields = bw_list_fields();
  bw_select( "field", "Field", null, array( "#options" => $fields )) ; 
  // bw_checkbox( "hierarchical", "Hierarchical f2t?", $bw_f2t['args']["hierarchical"] );
  etag( "table" );
  
  p( isubmit( "_oik_f2t_add_oik_f2t", "Add new field to type", null, "button-primary" ) );
  etag( "form" );
}

/**
 * Display the form to edit a field to post relationship
 */
function oik_f2t_edit_f2t( ) {
  global $bw_f2t;
  bw_form();
  stag( "table", "wide-fat" );
  
  bw_tablerow( array( "Relationship", $bw_f2t['args']['f2t'] . ihidden( 'f2t', $bw_f2t['args']['f2t']) ) );
  //bw_textfield( "f2t", 20, "Post f2t", $bw_f2t['args']['f2t'] );
  bw_textfield( "type", 30, "Type", stripslashes( $bw_f2t['args']['type'] ) );
  //$field = esc_textarea( $bw_f2t['args']['field'] );
  //bw_trace2( $field, "esc_textarea field", false );
  bw_textfield( "field", 100, "Field", stripslashes( $bw_f2t['args']['field'] ), 10 );
  //bw_checkbox( "hierarchical", "Hierarchical f2t?", $bw_f2t['args']["hierarchical"] );
  etag( "table" );
  
  p( isubmit( "_oik_f2t_edit_f2t", "Change relationship", null, "button-primary" ) );
  etag( "form" );
}

function oik_f2t_preview() {
  oik_require( "includes/oik-sc-help.inc" );
  $preview_f2t = bw_array_get( $_REQUEST, "preview_f2t", null );
  if ( $preview_f2t ) {
    sdiv( "oik_preview");
    //bw_invoke_f2t( $preview_f2t, null, "Preview of the $preview_f2t f2t" );
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


