<?php // (C) Copyright Bobbing Wide 2013-2018

/**
 * Lazy implementation for "oik-taxonomies" 
 *
 * oik-taxonomies options page
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                       Validate? Perform action        Display preview Display add  Display edit Display select list
 * ------------------------------- --------  -------------------   --------------- ------------ ------------ -------------------
 * preview_taxonomy                    No        n/a                   Yes             -            -            -
 * delete_taxonomy                     No        delete selected taxonomy  -               -            -            Yes
 * edit_taxonomy                       No        n/a                   -               -            Yes          Yes
 *
 * _oik_tax_edit_taxonomy         Yes       update selected taxonomy  -               -            Yes          Yes
 * _oik_tax_add_taxonomy
 * _oik_tax_add_oik_tax
 * 
 * 
*/

function oiktax_lazy_taxonomies_do_page() {
  BW_::oik_menu_header( __( "taxonomies", "oik-types" ), "w100pc" );
  $validated = false;
  
  $preview_taxonomy = bw_array_get( $_REQUEST, "preview_taxonomy", null );
  $delete_taxonomy = bw_array_get( $_REQUEST, "delete_taxonomy", null );
  $edit_taxonomy = bw_array_get( $_REQUEST, "edit_taxonomy", null );
  
  /** These codes override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_tax_edit_taxonomy = bw_array_get( $_REQUEST, "_oik_tax_edit_taxonomy", null );
  $oik_tax_add_oik_tax = bw_array_get( $_REQUEST, "_oik_tax_add_oik_tax", null );
  $oik_tax_add_taxonomy = bw_array_get( $_REQUEST, "_oik_tax_add_taxonomy", null );
  if ( $oik_tax_add_taxonomy || $oik_tax_add_oik_tax ) {
    $preview_taxonomy = null;
    $delete_taxonomy = null;
    $edit_taxonomy = null; 
  }  
  
  
  if ( $preview_taxonomy ) {
    oik_box( NULL, NULL, "Preview", "oik_tax_preview" );
  } 
  
  if ( $delete_taxonomy ) { 
    _oik_tax_delete_taxonomy( $delete_taxonomy );
  }  

  if ( $edit_taxonomy ) {
    global $bw_taxonomy;
    $bw_taxonomies = get_option( "bw_taxonomies" );
    $bw_taxonomy = bw_array_get( $bw_taxonomies, $edit_taxonomy, null );
    $bw_taxonomy['args']['taxonomy'] = $edit_taxonomy; 
    bw_trace2( $bw_taxonomy );
  }
  if ( $oik_tax_edit_taxonomy ) {  
    $validated = _oik_tax_taxonomy_validate( false );
  }  
  
  if ( $oik_tax_add_oik_tax ) {
    $validated = _oik_tax_taxonomy_validate( true );
  }
  
  if ( $oik_tax_add_taxonomy || ( $oik_tax_add_oik_tax && !$validated )  ) {
    oik_box( NULL, NULL, "Add new", "oik_tax_add_oik_tax" );
  }
  
  if ( $edit_taxonomy || $oik_tax_edit_taxonomy || $validated ) {
    oik_box( null, null, "Edit taxonomy", "oik_tax_edit_taxonomy" );
  }
  BW_::oik_box( NULL, NULL, __( "taxonomies", "oik-types" ), "oik_tax_taxonomies" );
  oik_menu_footer();
  bw_flush();
}

/** 
 * Display a current taxonomy
 */
function _oik_tax_taxonomy_row( $taxonomy, $data ) {
  // bw_trace2();
  $row = array();
  $row[] = $taxonomy;
  $args = $data['args'];
  $taxonomies = bw_array_get( $data, 'taxonomies', null );
  // Lookup the type and return the l10n value? 
  $taxonomy_types = oik_tax_query_taxonomy_types();
  $type = bw_array_get( $taxonomy_types, $args['type'], "&nbsp;" );
  $row[] = esc_html( stripslashes( $type )) . "&nbsp;";
  $row[] = esc_html( stripslashes( $args['label'] ) ) . "&nbsp;";  
  $row[] = esc_html( stripslashes( $args['title'] ) ) . "&nbsp;";  
  $links = null;
  //$links .= retlink( null, admin_url("admin.php?page=oik_taxonomies&amp;preview_taxonomy=$taxonomy"), "Preview" );
  //$links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_taxonomies&amp;delete_taxonomy=$taxonomy"), "Delete" ); 
  $links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_taxonomies&amp;edit_taxonomy=$taxonomy"), "Edit" );   
  $row[] = $links;
  bw_tablerow( $row );
}

/**
 * Display the table of oik custom taxonomies
 */
function _oik_tax_taxonomy_table() {
  $bw_taxonomies = get_option( "bw_taxonomies" );
  if ( is_array( $bw_taxonomies) && count( $bw_taxonomies )) {
    foreach ( $bw_taxonomies as $taxonomy => $data ) {
      //$taxonomy = bw_array_get( $bw_taxonomy, "taxonomy", null );
      _oik_tax_taxonomy_row( $taxonomy, $data );
    }
  }  
}

/** 
 * Check if the taxonomy exists
 */
function _oik_tax_check_taxonomy_exists( $bw_taxonomy )  {
  global $bw_taxonomies;
  $exists = bw_array_get( $bw_taxonomies, $bw_taxonomy, false );
  return( $exists );
}  
 

/**
 * Check if it already exists as a taxonomy
 *
 * If not then add to the options using bw_update_option() 
 * then empty out the taxonomy field for the next one
 *
 */
function _oik_tax_add_oik_tax( $bw_taxonomy ) {
  $taxonomy = bw_array_get( $bw_taxonomy['args'], "taxonomy", null );
  $taxonomy_exists = _oik_tax_check_taxonomy_exists( $taxonomy ); 
  if ( $taxonomy_exists ) {
    p( "taxonomy $taxonomy already defined, try another taxonomy" );   
    $ok = false;

  } else {
    unset( $bw_taxonomy['args']['taxonomy'] );
    bw_update_option( $taxonomy, $bw_taxonomy, "bw_taxonomies" );
    // We don't need to add the taxonomy now! 
    $bw_taxonomy['args']['taxonomy'] = "";
    $ok = true;
  }
  return( $ok ); 
}

/**
 * Update an oik custom taxonomy
 */
function _oik_tax_update_taxonomy( $bw_taxonomy ) {
  $taxonomy = bw_array_get( $bw_taxonomy['args'], "taxonomy", null );
  if ( $taxonomy ) { 
    unset( $bw_taxonomy['args']['taxonomy'] );
    bw_update_option( $taxonomy, $bw_taxonomy, "bw_taxonomies" );
  } else {
    bw_trace2( $taxonomy, "Logic error?" );
  }  
}

/**
 * Delete an oik taxonomy
 */
function _oik_tax_delete_taxonomy( $bw_taxonomy ) {
  bw_delete_option( $bw_taxonomy, "bw_taxonomies" );
}  


/**
 * Taxonomy must not be blank
 * 
 * @param string $taxonomy - reference to the required taxonomy name
 * which should be in slug form (must not contain capital letters or spaces) 
 * and not more than 32 characters long (database structure restriction)
 * 
 */
function oik_diy_validate_taxonomy( &$taxonomy ) {
  $valid = isset( $taxonomy );
  if ( $valid ) { 
    $taxonomy = trim( $taxonomy );
    $valid = strlen( $taxonomy ) > 0;
    $valid &= strlen( $taxonomy ) <= 32;
  } 
  if ( !$valid ) { 
    p( "Taxonomy must not be blank and not more than 32 characters" ); 
      
  } else {  
    $sanitized_taxonomy = sanitize_key( $taxonomy );
    if ( $sanitized_taxonomy != $taxonomy ) {
      p( "Taxonomy name should be lower case. e.g. " . strtolower( $sanitized_taxonomy ) );
      $valid = false;
      $taxonomy = $sanitized_taxonomy;
    }
  }  
  return $valid;
}
    
/**
 
 */
function _oik_tax_taxonomy_validate( $add_taxonomy=true ) {

  global $bw_taxonomy;
  $bw_taxonomy['args']['taxonomy'] = bw_array_get( $_REQUEST, "taxonomy", null );
  $bw_taxonomy['args']['type'] = bw_array_get( $_REQUEST, "type", null );
  $bw_taxonomy['args']['label'] = bw_array_get( $_REQUEST, "label", null );
  ///$bw_taxonomy['args']['required'] = bw_array_get( $_REQUEST, "required", null );
  $bw_taxonomy['args']['title'] = bw_array_get( $_REQUEST, "title", null );
	$bw_taxonomy['args']['show_in_rest'] = bw_array_get( $_REQUEST, "show_in_rest", null );
  
  bw_trace2( $bw_taxonomy, "bw_taxonomy" );
  
  $ok = oik_diy_validate_taxonomy( $bw_taxonomy['args']['taxonomy'] );
  
  // validate the taxonomies and add the taxonomy IF it's OK to add
  // $add_taxonomy = bw_array_get( $_REQUEST, "_oik_tax_add_oik_tax", false );
  if ( $ok ) {
    if ( $add_taxonomy ) {
      $ok = _oik_tax_add_oik_tax( $bw_taxonomy );  
    } else {
      $ok = _oik_tax_update_taxonomy( $bw_taxonomy );
    }
  }  
  return( $ok );
}

/**
 * Note the bw_register_taxonomy function is commented out! Herb 2013/10/15 - as is the similar function in oik-fields.php
 
/** 
 * Register a taxonomy named $taxonomy_name of type $taxonomy_type with title $taxonomy_title and additional values $args
 * 
 * @param string $taxonomy_name - meta_key of the taxonomy - precede with an underscore so it's not in custom taxonomies
 * @param string $taxonomy_type - type of taxonomy e.g. text, textarea, radio button
 * @param string $taxonomy_title - title of the taxonomy
 * @param string $args - $taxonomy_type specific values including:
 * <code>
 *   #length - for text taxonomies
 *   #validate - for any taxonomy
 *   #type - for noderef 
 *   #options - for select 
 *   #multiple - for multiple select taxonomies
 *   #form - bool - whether or not to display the taxonomy on an "Add New" form - defaults to true 
 *   #theme - bool - whether or not to display the taxonomy during "theming" - defaults to true
 * </code>   
function bw_register_taxonomy( $taxonomy_name, $taxonomy_type, $taxonomy_title, $args=NULL ) {
  global $bw_taxonomies;
  $data = array( '#taxonomy_type' => $taxonomy_type,
                 '#title' => $taxonomy_title, 
                 '#args' => $args, 
               );
  $bw_taxonomies[$taxonomy_name] = $data;
}


 */
function oik_tax_taxonomies() {
  BW_::p( "" );
  bw_form();
  stag( "table", "widefat" );
  stag( "thead");
  bw_tablerow( array( "Name", "Type", "Label", "Title", "Actions" ));
  etag( "thead");
  _oik_tax_taxonomy_table();
  etag( "table" );
  e( isubmit( "_oik_tax_add_taxonomy", __( "Add taxonomy", "oik-types" ), null, "button-primary" ) );
  etag( "form" );
}

/**
 * Return the different choices for Taxonomy type
 *
 * @return array = associative array of taxonomy type and l10n value for the label
 */
function oik_tax_query_taxonomy_types( $taxonomy_types=null ) {
  $taxonomy_types['tags'] = __( 'Tags' ); 
  $taxonomy_types['categories'] = __( 'Categories' );
  return( $taxonomy_types );
}  

/**
 * Add a Taxonomy name
 */
function oik_tax_add_oik_tax( ) {
  global $bw_taxonomy;
  bw_form();
  stag( "table", "wide-fat" );
  bw_textfield( "taxonomy", 32, "Name", $bw_taxonomy['args']['taxonomy'] );
  bw_textfield( "label", 32, "Label", $bw_taxonomy['args']['label'] );
  // $taxonomy_types = apply_filters( "oik_query_taxonomy_types", array() );
  $taxonomy_types = oik_tax_query_taxonomy_types();
  bw_trace2( $taxonomy_types, "taxonomy_types" );
  bw_select( "type", "Type", null, array( "#options" => $taxonomy_types ) );
  bw_textfield( "title", 100, "Title", stripslashes( $bw_taxonomy['args']['title'] ) );
	bw_checkbox( "show_in_rest", "Show in REST", $bw_taxonomy['args']['show_in_rest'] );
  etag( "table" );
  
  p( isubmit( "_oik_tax_add_oik_tax", "Add new taxonomy", null, "button-primary" ) );
  etag( "form" );
}

/**
 * Display form to edit a custom taxonomy
 */
function oik_tax_edit_taxonomy( ) {
  global $bw_taxonomy;
  bw_form();
  stag( "table", "wide-fat" );
  bw_tablerow( array( "Name", $bw_taxonomy['args']['taxonomy'] . ihidden( 'taxonomy', $bw_taxonomy['args']['taxonomy']) ) );
  
  $taxonomy_types = oik_tax_query_taxonomy_types();
  $type = bw_array_get( $taxonomy_types, $bw_taxonomy['args']['type'], "&nbsp;" );
  bw_tablerow( array( "Type", $type . ihidden( 'type', $bw_taxonomy['args']['type']) ) );
  bw_textfield( "label", 32, "Label", stripslashes( $bw_taxonomy['args']['label'] ) );
  bw_textfield( "title", 100, "Title", stripslashes( $bw_taxonomy['args']['title'] ) );
	bw_checkbox( "show_in_rest", "Show in REST", bw_array_get(  $bw_taxonomy['args'], 'show_in_rest', null ) );
  etag( "table" );
  
  p( isubmit( "_oik_tax_edit_taxonomy", "Change taxonomy", null, "button-primary" ) );
  etag( "form" );
}

/**
 *
 */
function oik_tax_preview() {
  oik_require( "includes/oik-sc-help.inc" );
  $preview_taxonomy = bw_array_get( $_REQUEST, "preview_taxonomy", null );
  if ( $preview_taxonomy ) {
    sdiv( "oik_preview");
    //bw_invoke_taxonomy( $preview_taxonomy, null, "Preview of the $preview_taxonomy taxonomy" );
    p( "Preview not yet implemented" );
    ediv( "oik_preview");
  }
}

if ( !function_exists( "bw_update_option" ) ) {
/** Set the value of an option taxonomy in the options group
 *
 * @param string $taxonomy the option taxonomy to be set
 * @param mixed $value the value of the option
 * @param string $options - the name of the option taxonomy
 * @return mixed $value
 *
 * Parms are basically the same as for update_option
 */
function bw_update_option( $taxonomy, $value=NULL, $options="bw_options" ) {
  $bw_options = get_option( $options );
  $bw_options[ $taxonomy ] = $value;
  bw_trace2( $bw_options );
  update_option( $options, $bw_options );
  return( $value );
}
}

/** Remove an option taxonomy from a set
 *
 * @param string $taxonomy the option taxonomy to be removed
 * @param string $options - the name of the options set
 * @return mixed $value - current values for the options
 *
 */
if ( !function_exists( "bw_delete_option" ) ) {
function bw_delete_option( $taxonomy, $options="bw_options" ) {
  $bw_options = get_option( $options );
  unset( $bw_options[ $taxonomy ] );
  // bw_trace2( $bw_options );
  update_option( $options, $bw_options );
  return( $options );
}
}


