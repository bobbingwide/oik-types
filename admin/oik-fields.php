<?php // (C) Copyright Bobbing Wide 2013

/**
 * Lazy implementation for "oik-fields" 
 *
 * oik-fields options page
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                       Validate? Perform action        Display preview Display add  Display edit Display select list
 * ------------------------------- --------  -------------------   --------------- ------------ ------------ -------------------
 * preview_field                    No        n/a                   Yes             -            -            -
 * delete_field                     No        delete selected field  -               -            -            Yes
 * edit_field                       No        n/a                   -               -            Yes          Yes
 *
 * _oik_fie_edit_field         Yes       update selected field  -               -            Yes          Yes
 * _oik_fie_add_field
 * _oik_fie_add_oik_fie
 * 
 * 
*/

function oikfie_lazy_fields_do_page() {
  oik_menu_header( "Fields", "w100pc" );
  $validated = false;
  
  $preview_field = bw_array_get( $_REQUEST, "preview_field", null );
  $delete_field = bw_array_get( $_REQUEST, "delete_field", null );
  $edit_field = bw_array_get( $_REQUEST, "edit_field", null );
  
  /** These codes override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_fie_edit_field = bw_array_get( $_REQUEST, "_oik_fie_edit_field", null );
  $oik_fie_add_oik_fie = bw_array_get( $_REQUEST, "_oik_fie_add_oik_fie", null );
  $oik_fie_add_field = bw_array_get( $_REQUEST, "_oik_fie_add_field", null );
  if ( $oik_fie_add_field || $oik_fie_add_oik_fie ) {
    $preview_field = null;
    $delete_field = null;
    $edit_field = null; 
  }  
  
  
  if ( $preview_field ) {
    oik_box( NULL, NULL, "Preview", "oik_fie_preview" );
  } 
  
  if ( $delete_field ) { 
    _oik_fie_delete_field( $delete_field );
  }  

  if ( $edit_field ) {
    global $bw_field;
    $bw_fields = get_option( "bw_fields" );
    $bw_field = bw_array_get( $bw_fields, $edit_field, null );
    $bw_field['args']['field'] = $edit_field; 
    bw_trace2( $bw_field );
  }
  if ( $oik_fie_edit_field ) {  
    $validated = _oik_fie_field_validate( false );
  }  
  
  if ( $oik_fie_add_oik_fie ) {
    $validated = _oik_fie_field_validate( true );
  }
  
  if ( $oik_fie_add_field || ( $oik_fie_add_oik_fie && !$validated )  ) {
    oik_box( NULL, NULL, "Add new", "oik_fie_add_oik_fie" );
  }
  
  if ( $edit_field || $oik_fie_edit_field || $validated ) {
    oik_box( null, null, "Edit field", "oik_fie_edit_field" );
  }
  oik_box( NULL, NULL, "fields", "oik_fie_fields" );
  oik_menu_footer();
  bw_flush();
}

/** 
 * Display a current field
 */
function _oik_fie_field_row( $field, $data ) {
  bw_trace2();
  $row = array();
  $row[] = $field;
  $args = $data['args'];
  $fields = bw_array_get( $data, 'fields', null );
  $row[] = esc_html( stripslashes( $args['type'] ) ) . "&nbsp";
  $row[] = esc_html( stripslashes( $args['title'] ) ) . "&nbsp";  
  $row[] = icheckbox( "required[$field]", $args['required'], true );
  //$row[] = icheckbox( "expand[$field]", $expand, true );
  $links = null;
  //$links .= retlink( null, admin_url("admin.php?page=oik_fields&amp;preview_field=$field"), "Preview" );
  //$links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_fields&amp;delete_field=$field"), "Delete" ); 
  $links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_fields&amp;edit_field=$field"), "Edit" );   
  $row[] = $links;
  bw_tablerow( $row );
}

/**
 * Display the table of oik custom fields
 * 
 */
function _oik_fie_field_table() {
  $bw_fields = get_option( "bw_fields" );
  if ( is_array( $bw_fields) && count( $bw_fields )) {
    foreach ( $bw_fields as $field => $data ) {
      //$field = bw_array_get( $bw_field, "field", null );
      _oik_fie_field_row( $field, $data );
    }
  }  
}

/** 
 *
 */
function _oik_fie_check_field_exists( $bw_field )  {
  global $bw_fields;
  $exists = bw_array_get( $bw_fields, $bw_field, false );
  return( $exists );
}  
 

/**
 * Check if it already exists as a field
 *
 * If not then add to the options using bw_update_option() 
 * then empty out the field field for the next one
 *
 */
function _oik_fie_add_oik_fie( $bw_field ) {
  $field = bw_array_get( $bw_field['args'], "field", null );
  $field_exists = _oik_fie_check_field_exists( $field ); 
  if ( $field_exists ) {
    p( "field $field already defined, try another field" );   
    $ok = false;

  } else {
    unset( $bw_field['args']['field'] );
    bw_update_option( $field, $bw_field, "bw_fields" );
    // We don't need to add the field now! 
    $bw_field['args']['field'] = "";
    $ok = true;
  }
  return( $ok ); 
}

function _oik_fie_update_field( $bw_field ) {
  $field = bw_array_get( $bw_field['args'], "field", null );
  if ( $field ) { 
    unset( $bw_field['args']['field'] );
    bw_update_option( $field, $bw_field, "bw_fields" );
  } else {
    bw_trace2( $field, "Logic error?" );
  }  
}

function _oik_fie_delete_field( $bw_field ) {
  bw_delete_option( $bw_field, "bw_fields" );
}  


/**
 * Field must not be blank
 */
function oik_fie_validate_field( &$field ) {
  $valid = isset( $field );
  if ( $valid ) { 
    $field = trim( $field );
    $valid = strlen( $field ) > 0;
  } 
  if ( !$valid ) { 
    p( "Field must not be blank" );   
  } else {  
    $sanitized_field = sanitize_key( $field );
    if ( $sanitized_field != $field ) {
      p( "Field name should be lower case, preferably starting with an underscore e.g. _" . strtolower( $sanitized_field ) );
      $valid = false;
      $field = $sanitized_field;
      if ( substr( $field, 0 ) != "_" ) {
        $field = "_". $field;
      }  
    }
  }  
  return $valid;
}
    
/**
 
 */
function _oik_fie_field_validate( $add_field=true ) {

  global $bw_field;
  $bw_field['args']['field'] = bw_array_get( $_REQUEST, "field", null );
  $bw_field['args']['type'] = bw_array_get( $_REQUEST, "type", null );
  $bw_field['args']['title'] = bw_array_get( $_REQUEST, "title", null );
  $bw_field['args']['required'] = bw_array_get( $_REQUEST, "required", null );
  $bw_field['args']['title'] = bw_array_get( $_REQUEST, "title", null );
  
  /** These are variable values that should be set depending on the field type
   */
  $bw_field['args']['args']['#type'] = bw_array_get( $_REQUEST, "#type", null );
  $bw_field['args']['args']['#multiple'] = bw_array_get( $_REQUEST, "#multiple", null );
  $bw_field['args']['args']['#options'] = bw_array_get( $_REQUEST, "#options", null );
  $bw_field['args']['args']['#optional'] = bw_array_get( $_REQUEST, "#optional", null );
  
  bw_trace2( $bw_field, "bw_field" );
  
  $ok = oik_fie_validate_field( $bw_field['args']['field'] );
  
  // validate the fields and add the field IF it's OK to add
  // $add_field = bw_array_get( $_REQUEST, "_oik_fie_add_oik_fie", false );
  if ( $ok ) {
    if ( $add_field ) {
      $ok = _oik_fie_add_oik_fie( $bw_field );  
    } else {
      $ok = _oik_fie_update_field( $bw_field );
    }
  }  
  return( $ok );
}

/**
 *
 
/** 
 * Register a field named $field_name of type $field_type with title $field_title and additional values $args
 * 
 * @param string $field_name - meta_key of the field - precede with an underscore so it's not in custom fields
 * @param string $field_type - type of field e.g. text, textarea, radio button
 * @param string $field_title - title of the field
 * @param string $args - $field_type specific values including:
 * <code>
 *   #length - for text fields
 *   #validate - for any field
 *   #type - for noderef 
 *   #options - for select 
 *   #optional - for optional select fields... those which support 'None' or 'Not selected' 
 *   #multiple - for multiple select fields
 *   #form - bool - whether or not to display the field on an "Add New" form - defaults to true 
 *   #theme - bool - whether or not to display the field during "theming" - defaults to true
 * </code>   
 *
 *  function bw_register_field( $field_name, $field_type, $field_title, $args=NULL ) {
 *    global $bw_fields;
 *    $data = array( '#field_type' => $field_type,
 *                   '#title' => $field_title, 
 *                  '#args' => $args, 
 *                 );
 *    $bw_fields[$field_name] = $data;
 *  }
 *
 *
 */
function oik_fie_fields() {
  p( "" );
  bw_form();
  stag( "table", "widefat" );
  stag( "thead");
  bw_tablerow( array( "Name", "Type", "Title", "Required?", "Actions" ));
  etag( "thead");
  _oik_fie_field_table();
  etag( "table" );
  p( isubmit( "_oik_fie_add_field", "Add field", null, "button-primary" ) );
  etag( "form" );
} 

/**
 * Display form to add a custom field
 */
function oik_fie_add_oik_fie( ) {
  global $bw_field;
  bw_form();
  stag( "table", "wide-fat" );
  bw_textfield( "field", 20, "Name", $bw_field['args']['field'] );
  // bw_textfield( "type", 20, "Type", $bw_field['args']['type'] );
  $field_types = apply_filters( "oik_query_field_types", array() );
  // bw_trace2( $field_types, "field_types" );
  bw_select( "type", "Type", null, array( "#options" => $field_types ) );
  bw_textfield( "title", 100, "Title", stripslashes( $bw_field['args']['title'] ), 10 );
  bw_checkbox( "required", "Required field?", $bw_field['args']["required"] );
  etag( "table" );
  p( isubmit( "_oik_fie_add_oik_fie", "Add new field", null, "button-primary" ) );
  etag( "form" );
}

/**
 * Determine the action hook to invoke to display the options for a particular field type
 *
 * Other plugins can add their own action hooks for the field type
 */
function oik_fie_edit_field_options( $type ) {
  $field_options = array( "checkbox" => "oik_fie_edit_field_type_checkbox"
                        , "currency" => "oik_fie_edit_field_type_currency"
                        , "date" => "oik_fie_edit_field_type_date"
                        , "email" => "oik_fie_edit_field_type_email" 
                        , "noderef" => "oik_fie_edit_field_type_noderef"
                        , "numeric" => "oik_fie_edit_field_type_numeric"
                        , "select" => "oik_fie_edit_field_type_select"
                        , "text" => "oik_fie_edit_field_type_text" 
                        , "textarea" => "oik_fie_edit_field_type_textarea" 
                        , "url" => "oik_fie_edit_field_type_url" 
                        );
  $action_hook = bw_array_get( $field_options, $type, null );
  if ( $action_hook ) {
    add_action( "oik_fie_edit_field_type_$type", $action_hook );
  }
} 


/**
 * Display the field options for a "checkbox" type field
 */ 
function oik_fie_edit_field_type_checkbox() {
} 

/**
 * Display the field options for a "currency" type field
 */ 
function oik_fie_edit_field_type_currency() {
}
 
/**
 * Display the field options for a "date" type field
 */ 
function oik_fie_edit_field_type_date() {
} 

/**
 * Display the field options for an "email" type field
 */ 
function oik_fie_edit_field_type_email() {
} 

 

/**
 * Handle the field options for a "noderef" type field 
 * 
 * options that go in bw_field['args']['#options'] are:
 * #type - the post_types that this field can refer to
 * #array( '#options' => $options )
 
 */
function oik_fie_edit_field_type_noderef() {
  //e( __FUNCTION__ );
  global $bw_field;
  $post_types = bw_list_registered_post_types();
  $argsargs = bw_array_get( $bw_field['args'], 'args', null );
  $argsargs['#type'] = bw_array_get( $argsargs, "#type", null );
  $argsargs['#multiple'] = bw_array_get( $argsargs, "#multiple", null );
  $argsargs['#optional'] = bw_array_get( $argsargs, "#optional", null );
  
  
  bw_select( "#type", "Node type(s)", $argsargs['#type'], array( '#options' => $post_types, '#multiple' => 10 ) );
  bw_form_field( "#multiple", "numeric", "Single or multiple select", $argsargs['#multiple'] );
  bw_form_field( "#optional", "checkbox", "Optional", $argsargs['#optional'] );
}


/**
 * Display the field options for a "numeric" type field
 */ 
function oik_fie_edit_field_type_numeric() {
}
 

/**
 * Display the field options for a "select" type field
 *
 * #options  - the finite list of possible options
 * #multiple - whether or not it's a multiple select list and if so, how many rows to display
 *  
 */
function oik_fie_edit_field_type_select() {
  global $bw_field;
  $argsargs = bw_array_get( $bw_field['args'], 'args', null );
  $argsargs['#options'] = bw_array_get( $argsargs, "#options", null );
  $argsargs['#multiple'] = bw_array_get( $argsargs, "#multiple", null );
  
  bw_form_field( "#options", "textarea", "Options", $argsargs['#options'], array( '#hint' => "Separate options by commas" ) );
  bw_form_field( "#multiple", "numeric", "Single or multiple select", $argsargs['#multiple'] );

}

/**
 * Display the field options for a "text" type field
 */ 
function oik_fie_edit_field_type_text() {

}

/**
 * Display the field options for a "textarea" type field
 */ 
function oik_fie_edit_field_type_textarea() {

}

/**
 * Display the field options for an "URL" type field
 */ 
function oik_fie_edit_field_type_URL() {

}


 

/**
 * 

/** 
 * Display form to edit a field
 * 
 * @TODO Display field specific attributes
 */
function oik_fie_edit_field( ) {
  
  global $bw_field;
  bw_form();
  stag( "table", "wide-fat" );
  bw_tablerow( array( "Name", $bw_field['args']['field'] . ihidden( 'field', $bw_field['args']['field']) ) );
  bw_tablerow( array( "Type", $bw_field['args']['type'] . ihidden( 'type', $bw_field['args']['type']) ) ); 
  // bw_textfield( "type", 30, "Type", stripslashes( $bw_field['args']['type'] ) );
  bw_textfield( "title", 100, "Title", stripslashes( $bw_field['args']['title'] ), 10 );
  bw_checkbox( "required", "Required field?", $bw_field['args']["required"] );
  $type = $bw_field['args']['type']; 
  add_action( "oik_fie_edit_field_options", "oik_fie_edit_field_options" ); 
  do_action( "oik_fie_edit_field_options", $type );
  do_action( "oik_fie_edit_field_type_$type" );
  etag( "table" );
  p( isubmit( "_oik_fie_edit_field", "Change field", null, "button-primary" ) );
  etag( "form" );
}

/**
 * View a field 
 */
function oik_fie_preview() {
  oik_require( "includes/oik-sc-help.inc" );
  $preview_field = bw_array_get( $_REQUEST, "preview_field", null );
  if ( $preview_field ) {
    sdiv( "oik_preview");
    //bw_invoke_field( $preview_field, null, "Preview of the $preview_field field" );
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


