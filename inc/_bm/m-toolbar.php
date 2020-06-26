<?php /**
 * @version 1.0
 * @package Booking Calendar 
 * @category UI elements for Toolbar Booking Listing / Calendar Overview pages
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2016-02-15
 * 
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit, if accessed directly


////////////////////////////////////////////////////////////////////////////    
//  B u t t o n s   -  ADD NEW Booking page
////////////////////////////////////////////////////////////////////////////  


/** Selection  of Custom  booking forms */
function wpbc_in_settings__form_selection( $params = array() ) {

    $defaults = array( 
                          'name'        => 'select_booking_form'
                        , 'title'       => __('Booking Form', 'booking') 
                        , 'description' => __('Select default custom booking form' ,'booking')
                        , 'group'       => 'general'
                        , 'init_options' => array()                             // Init default list of options
                    );
    $params = wp_parse_args( $params, $defaults );
    

    // Check if we can be here  ////////////////////////////////////////////////
    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );
    if ( ( !$is_can ) && ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) !== 'On' ) )
        return;


    ////////////////////////////////////////////////////////////////////////////
    $booking_forms_extended = get_bk_option( 'booking_forms_extended' );
    if ( $booking_forms_extended !== false ) {

        $booking_forms_extended = maybe_unserialize( $booking_forms_extended );
      
//		if ( ! is_array( $booking_forms_extended ) ) {																		//FixIn: 7.2.1.2
//			// Something wrong with  this custom  booking forms,  so  we will  try  to reset  it.
//			delete_bk_option( 'booking_forms_extended' );		// Delete custom form  option
//			$booking_forms_extended = array();
//		}

        ////////////////////////////////////////////////////////////////////////////
        // DropDown list with Custom Forms  ////////////////////////////////////////


        $form_options = $params['init_options'];

        $form_options['standard'] = array(  
                                            'title' => __('Standard', 'booking')
                                            , 'class' => ''
                                            , 'attr' => array( 'style' => '' )   
                                        );

        $form_options[ 'optgroup_cf_s' ] = array( 'optgroup' => true, 'close'  => false, 'title'  => '&nbsp;' . __('Custom Forms' ,'booking')  );

        foreach ( $booking_forms_extended as $cust_form ) {

            $form_options[ $cust_form['name'] ] = array(  
                                            'title' => apply_bk_filter( 'wpdev_check_for_active_language', $cust_form['name'] )
                                            , 'attr' => array()   
                                        );
        }

        $templates[ 'optgroup_cf_e' ] = array( 'optgroup' => true, 'close'  => true );

        ////////////////////////////////////////////////////////////////////////

        
        WPBC_Settings_API::field_select_row_static(   $params['name']
                                                    , array(  
                                                              'type'              => 'select'
                                                            , 'title'             => $params['title']
                                                            , 'label'             => ''
                                                            , 'disabled'          => false
                                                            , 'disabled_options'  => array()
                                                            , 'multiple'          => false
                                                            , 'description'       => $params['description']
                                                            , 'description_tag'   => 'span'
                                                            , 'tr_class'          => $params['group'] . '_standard_section'
                                                            , 'group'             => $params['group']
                                                            , 'class'             => ''
                                                            , 'css'               => 'margin-right:10px;'
                                                            , 'only_field'        => false
                                                            , 'attr'              => array()                                                    
                                                            , 'value'             => ''
                                                            , 'options'           => $form_options
                                                        )
                                            );

    }
}



/** Selection  of Custom  booking forms */
function wpbc_toolbar_btn__form_selection( $params = array() ) {

    $defaults = array( 
                          'on_change'   => false
                        , 'title'       => __('Booking Form', 'booking') . ':'
                    );
    $params = wp_parse_args( $params, $defaults );
    
    
    // Check if we can be here  ////////////////////////////////////////////////
    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );
    if ( ( !$is_can ) && ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) !== 'On' ) )
        return;



    // Set Default custom booking form for specific selected booking resource //
    if ( isset( $_GET['booking_type'] ) ) {
        $my_booking_form_name = apply_bk_filter( 'wpbc_get_default_custom_form', 'standard', $_GET['booking_type'] );
        if ( ! isset( $_GET['booking_form'] ) )
            $_GET['booking_form'] = $my_booking_form_name;
    }

    ////////////////////////////////////////////////////////////////////////////
    $booking_forms_extended = get_bk_option( 'booking_forms_extended' );
    if ( $booking_forms_extended !== false ) {

        $booking_forms_extended = maybe_unserialize( $booking_forms_extended );
        
        $parameter_name = 'booking_form';

        if ( $params['on_change'] === false ) {
            
            $link_base = wpbc_get_new_booking_url__base( array( $parameter_name ) ) . '&' . $parameter_name . '=' ;        

            $on_change = 'location.href=\'' . $link_base . '\' + this.value;';
            
        } else {
            $on_change = $params['on_change'];
        }

        
        // Show DropDown list with Custom forms selection
        wpbc_dropdown_list_with_custom_forms( $booking_forms_extended, $on_change , $params );

    }
}


////////////////////////////////////////////////////////////////////////////    
//  B u t t o n s   -  ADD NEW Booking page
////////////////////////////////////////////////////////////////////////////  

/**
	 * Interface for selection / creation / deletion  of Custom forms at Booking > Settings > Fields page (and in Booking > Resources > Advanced cost page )
 * 
 * @param boolean $is_show_add_new_custom_form - show or not "add new booking form fields
 */
function wpbc_toolbar_btn__custom_forms_in_settings_fields( $is_show_add_new_custom_form = true) {

    // Check  if user can  be here
    $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
    if ( (! $is_can) && ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) !== 'On' ) ) return;

    
    ////////////////////////////////////////////////////////////////////////////
    // Add New Custom Form /////////////////////////////////////////////////////
    if ( ( isset($_POST['booking_form_new_name'] )) && ( ! empty( $_POST['booking_form_new_name'] ) ) ) {
                
        $new_name = substr( $_POST['booking_form_new_name'] , 0 ,30 );          //Form name no longer than 30 symbols
        
        $new_name = wpbc_get_slug_format( $new_name );                                // Remove all symbols, which can  generate an issues

        $_GET['booking_form'] = $new_name;
                       
        // Get Default Forms
        $my_default_form      = str_replace( '\\n\\', '', wpbc_get_default_booking_form() ) ;
        $my_default_form_show = str_replace( '\\n\\', '', wpbc_get_default_booking_form_show() ) ;

        
        $booking_forms_extended = get_bk_option( 'booking_forms_extended');
        
        if ( $booking_forms_extended === false ) {                              // First Time Creation
        
            $booking_forms_extended = array( array('name'=>$new_name, 'form'=>$my_default_form, 'content'=>$my_default_form_show) );
            
        } else {                                                                // Append Custom form to exist forms
            
            $booking_forms_extended = maybe_unserialize($booking_forms_extended);
            
            // Get all exist custom names
            $exist_custom_forms_names = array();
            foreach ( $booking_forms_extended as $cust_form ) {
                $exist_custom_forms_names[] = $cust_form['name'];
            }
            
            // If new custom name not exist  then add this custom form.
            if ( ! in_array( $new_name, $exist_custom_forms_names) )
                $booking_forms_extended[] = array( 'name' => $new_name, 'form' => $my_default_form, 'content' => $my_default_form_show );
        } 
        
        // Save Changes
        update_bk_option( 'booking_forms_extended' , serialize($booking_forms_extended) );

        wpbc_show_changes_saved_message();
        
        // Reload Page
        ?><script type="text/javascript"> window.location.href='<?php echo wpbc_get_settings_url() . '&tab=form&booking_form=' . $new_name; ?>'; </script><?php
    }

    
    ////////////////////////////////////////////////////////////////////////////
    // Get Custom  Forms ///////////////////////////////////////////////////////

    $booking_forms_extended = get_bk_option( 'booking_forms_extended');
    
    if ( $booking_forms_extended === false ) {
        
        $booking_forms_extended = array();                                      // No Forms, yet.
        
    } else {
        
        $booking_forms_extended = maybe_unserialize($booking_forms_extended);
        
        // Check  about possible issues ////////////////////////////////////////
        // If the Name of Custom  form contain "+" symbol, it can generate issue 
        // of not loading custom form. We need to replace this symbol.
        $is_fix_exist = false;
        foreach ( $booking_forms_extended as $key => $value ) {

            if ( strpos( $value['name'], '+' ) !== false ) {
                $value['name'] = str_replace( '+', 'plus', $value['name'] );
                $booking_forms_extended[$key]['name'] = $value['name'];
                $is_fix_exist = true;
            }
        }
        if ( $is_fix_exist ) {
            update_bk_option( 'booking_forms_extended', serialize( $booking_forms_extended ) );
            $booking_forms_extended = get_bk_option( 'booking_forms_extended' );
            $booking_forms_extended = maybe_unserialize($booking_forms_extended);
        }
    }
    

    
    
    ////////////////////////////////////////////////////////////////////////////
    // DropDown list with Custom Forms  ////////////////////////////////////////
    
    $on_change = 'changeBookingForm(this);';
    
    // Show DropDown list with Custom forms selection
    wpbc_dropdown_list_with_custom_forms( $booking_forms_extended, $on_change );
    
    if ( $is_show_add_new_custom_form ) {
        // Show Buttos for creation and delettion of custom booking forms.
        wpbc_buttons_add_new_custom_form();
    }
        
}


/** Buttons for creation  new Custom Forms */
function wpbc_buttons_add_new_custom_form() {

    // D E L E T E  ////////////////////////////////////////////////////////////    
    if (  (isset($_GET['booking_form'])) && ($_GET['booking_form'] != 'standard')  ) {  // If we load some custom form  so  then show Delete button  for this custom form

        $user = wp_get_current_user();
        $user_bk_id = $user->ID;
        
        $params = array(  
                      'label_for' => 'min_cost'                                 // "For" parameter  of label element
                    , 'label' => '' //__('Add New Field', 'booking')            // Label above the input group
                    , 'style' => ''                                             // CSS Style of entire div element
                    , 'items' => array(     
                                        array( 
                                            'type' => 'button'
                                            , 'title' => __('Delete', 'booking')  // __('Reset', 'booking')
                                            , 'hint' => array( 'title' => __('Delete selected booking form' ,'booking') , 'position' => 'top' )
                                            , 'class' => 'button tooltip_top' 
                                            , 'font_icon' => 'glyphicon glyphicon-trash'
                                            , 'icon_position' => 'right'
                                            , 'action' => " if ( wpbc_are_you_sure('" . esc_js(__('Do you really want to delete selected booking form ?' ,'booking')) . "') ) { "
                                                          . " wpbc_delete_custom_booking_form( document.getElementById('select_booking_form').options[document.getElementById('select_booking_form').selectedIndex].value, " . $user_bk_id . " ); "
                                                          . "}"  

                                        )                            
                            )
                    );

        ?><div class="control-group wpbc-no-padding"><?php 
                wpbc_bs_input_group( $params );                   
        ?></div><?php   
        
    }    
    
    ?><form name="post_settings_form_fields_new_form" action="" method="post" id="post_settings_form_fields_new_form"><?php 
    
    
        // Add New Custom Button
        $params = array(  
                      'label_for' => 'min_cost'                             // "For" parameter  of label element
                    , 'label' => '' //__('Add New Field', 'booking')        // Label above the input group
                    , 'style' => ''                                         // CSS Style of entire div element
                    , 'items' => array(     
                                        array( 
                                            'type' => 'button'
                                            , 'title' => __('Add New Custom Form', 'booking')  // __('Reset', 'booking')
                                            , 'hint' => array( 'title' => __('Add New Custom Form' ,'booking') , 'position' => 'top' )
                                            , 'class' => 'button button-primary tooltip_top' 
                                            , 'font_icon' => 'glyphicon glyphicon-plus'
                                            , 'attr' => array( 'id' => 'add_new_custom_form_name_button' )
                                            , 'icon_position' => 'right'
                                            , 'action' => " jQuery('#wpbc_create_new_custom_form_name_fields').show(); "
                                                        . "jQuery('#add_new_custom_form_name_button').hide();"
                                                        . " setTimeout(function ( ) { jQuery('#booking_form_new_name').focus(); } ,100); "  // Focus

                                        )                            
                            )
                    );

        ?><div class="control-group wpbc-no-padding"><?php 
                wpbc_bs_input_group( $params );                   
        ?></div><?php   
        
        
        // Create new form Text fields
        $params = array(  
                      'label_for' => 'min_cost'                             // "For" parameter  of label element
                    , 'label' => '' //__('Add New Field', 'booking')        // Label above the input group
                    , 'style' => ''                                         // CSS Style of entire div element
                    , 'items' => array(     
                                        array(
                                            'type'          => 'text' 
                                            , 'id'          => 'booking_form_new_name'  
                                            , 'name'        => 'booking_form_new_name'  
                                            , 'attr' => array( 'maxlength' => '30' )
                                            , 'label'       => ''  
                                            , 'disabled'    => false
                                            , 'class'       => ''
                                            , 'style'       => 'min-width:210px;'
                                            , 'placeholder' => __('Type the name of booking form' ,'booking')                                                                                                                                    
                                            , 'attr'        => array()
                                            , 'value' => ''
                                            , 'onfocus' => ''                                            
                                        )
                                        , array( 
                                            'type' => 'button'
                                            , 'title' => __('Create', 'booking')  // __('Reset', 'booking')
                                            , 'hint' => array( 'title' => __('Create new form' ,'booking') , 'position' => 'top' )
                                            , 'class' => 'button button-primary tooltip_top' 
                                            , 'font_icon' => 'glyphicon glyphicon-plus'
                                            , 'icon_position' => 'right'                                                                            
                                            , 'action' => " document.forms['post_settings_form_fields_new_form'].submit(); "
                                        )                            
                                        , array( 
                                            'type' => 'button'
                                            , 'title' => __('Cancel', 'booking')  // __('Reset', 'booking')
                                            , 'hint' => array( 'title' => __('Cancel' ,'booking') , 'position' => 'top' )
                                            , 'class' => 'button tooltip_top' 
                                            , 'font_icon' => 'glyphicon glyphicon-remove'
                                            , 'icon_position' => 'right'
                                            , 'action' => " jQuery('#wpbc_create_new_custom_form_name_fields').hide(); "
                                                        . "jQuery('#add_new_custom_form_name_button').show();"
                                        )                            
                            )
                    );

        ?><div class="control-group wpbc-no-padding" id="wpbc_create_new_custom_form_name_fields"><?php 
                wpbc_bs_input_group( $params );                   
        ?></div><?php     
        
    ?></form><?php        
}



/** Show Selectbox with Custom Forms */
function wpbc_dropdown_list_with_custom_forms( $booking_forms_extended, $on_change , $params = array() ) {
    
    $defaults = array( 
                          'on_change'   => false
                        , 'title'       => __('Booking Form', 'booking') . ':'
                    );
    $params = wp_parse_args( $params, $defaults );
    
    
    ////////////////////////////////////////////////////////////////////////////
    // DropDown list with Custom Forms  ////////////////////////////////////////

    
    $form_options = array();
    
    $form_options['standard'] = array(  
                                        'title' => __('Standard', 'booking')
                                        , 'id' => ''   
                                        , 'name' => ''  
                                        , 'style' => 'padding:3px;border-bottom: 1px dashed #ccc;'
                                        , 'class' => ''
                                        , 'disabled' => false
                                        , 'selected' => false
                                        , 'attr' => array()   
                                    );

    $form_options[ 'optgroup_cf_s' ] = array( 'optgroup' => true, 'close'  => false, 'title'  => '&nbsp;' . __('Custom Forms' ,'booking')  );
    
    foreach ( $booking_forms_extended as $cust_form ) {

        $form_options[ $cust_form['name'] ] = array(  
                                        'title' => apply_bk_filter( 'wpdev_check_for_active_language', $cust_form['name'] )
                                        , 'id' => ''   
                                        , 'name' => ''  
                                        , 'style' => ''
                                        , 'class' => ''     
                                        , 'disabled' => false
                                        , 'selected' => ( (isset( $_GET['booking_form'] )) && ($_GET['booking_form'] == $cust_form['name']) ) ? true : false
                                        , 'attr' => array()   
                                    );
    }
    
    $templates[ 'optgroup_cf_e' ] = array( 'optgroup' => true, 'close'  => true );
    
    ////////////////////////////////////////////////////////////////////////

    $params = array(  
                      'label_for' => 'select_booking_form'                        // "For" parameter  of label element
                    , 'label' => ''                                         // Label above the input group
                    , 'style' => ''                                         // CSS Style of entire div element
                    , 'items' => array(
                                    array(      
                                        'type' => 'addon' 
                                        , 'element' => 'text'               // text | radio | checkbox
                                        , 'text' => $params['title']
                                        , 'class' => ''                     // Any CSS class here
                                        , 'style' => 'font-weight:600;'    // CSS Style of entire div element
                                    )  
                                    , array(    
                                          'type' => 'select'  
                                        , 'id' => 'select_booking_form'         // HTML ID  of element
                                        , 'name' => 'select_booking_form'         // HTML ID  of element
                                        , 'options' => $form_options            // Associated array  of titles and values 
                                        //, 'disabled_options' => array( 'any' )      // If some options disbaled,  then its must list  here
                                        //, 'default' => 'specific'             // Some Value from optins array that selected by default                                      
                                        , 'style' => ''                         // CSS of select element
                                        , 'class' => ''                         // CSS Class of select element
                                        , 'attr' => array()                     // Any  additional attributes, if this radio | checkbox element 
                                        , 'onchange' => $on_change              
                                    )
                    )
              );     
    ?><div class="control-group wpbc-no-padding"><?php 
            wpbc_bs_input_group( $params );                   
    ?></div><?php
        
}


//TOSO: Finish here
/** Save Custom Booking Form */
function wpbc_make_save_custom_booking_form() {
    
    $booking_form = $booking_form_show = $new_name ='';
    if ( isset($_GET['booking_form'])  )         $new_name = $_GET['booking_form'];
    if ( (isset($_POST['booking_form_new_name'])) && (! empty($_POST['booking_form_new_name'])) )  
        $new_name = substr( $_POST['booking_form_new_name'] , 0 ,30 );

    if (isset($_POST['booking_form'])) {
        
            // We can  not use here such code:
            // WPBC_Settings_API::validate_textarea_post_static( 'booking_form' );
            // becuse its will  remove also JavaScript,  which  possible to  use for wizard form  or in some other cases.
            $booking_form =  trim( stripslashes( $_POST['booking_form'] ) );
    }
    if ( isset( $_POST['booking_form_show'] ) ) {
        $booking_form_show =  trim( stripslashes( $_POST['booking_form_show'] ) );
    }

    if ( ( ! empty($new_name) ) && ( ! empty($booking_form) ) && ( ! empty($booking_form_show) ) ) {

        $booking_forms_extended = get_bk_option( 'booking_forms_extended');
        if ($booking_forms_extended !== false) {
            if ( is_serialized( $booking_forms_extended ) ) $booking_forms_extended = unserialize($booking_forms_extended);
            $i = 0;
            // Check already exist names for rewrite it
            foreach ($booking_forms_extended as $value) {
                if ($value['name'] == $new_name){
                    $booking_forms_extended[$i]['form']     = $booking_form;
                    $booking_forms_extended[$i]['content']  = $booking_form_show;
                    $i = 'modified';
                    break;
                } $i++;
            }
            if ($i !== 'modified') {  // add new booking form
                $booking_forms_extended[count($booking_forms_extended)] = array('name'=>$new_name, 'form'=>$booking_form, 'content'=> $booking_form_show );
            }

        } else {
            $booking_forms_extended = array( array('name'=>$new_name, 'form'=>$booking_form, 'content'=>$booking_form_show ) );
        }

        update_bk_option( 'booking_forms_extended' , serialize($booking_forms_extended) );
    }

}
add_bk_action('wpbc_make_save_custom_booking_form', 'wpbc_make_save_custom_booking_form' );



/** Delete Custom Booking Form - run in Ajax request */
function wpbc_make_delete_custom_booking_form(){

    if (isset($_POST['formname'])) {
        
        $form_name = $_POST['formname'];
        
        if ( !defined( 'WP_ADMIN' ) ) define( 'WP_ADMIN', true );               // this function is executed only in admin panel. In some servers, the WP_ADMIN constant is not defined, and thats can generate issue in MultiUser version, where will not select the specific user
        
        $booking_forms_extended = get_bk_option( 'booking_forms_extended' );
        if ( $booking_forms_extended !== false ) {
            
            $booking_forms_extended = maybe_unserialize( $booking_forms_extended );

            $booking_forms_extended_new = array();
            
            foreach ( $booking_forms_extended as $value ) {

                if ( $value['name'] == $form_name ) {
                    continue;  //skip it
                } else {
                    $booking_forms_extended_new[] = $value;
                }
            }

            update_bk_option( 'booking_forms_extended', serialize( $booking_forms_extended_new ) );
            
            ?>
                <script type="text/javascript">
                    var my_message = '<?php echo html_entity_decode( esc_js( __( 'Deleted', 'booking' ) ), ENT_QUOTES ); ?>';
                    wpbc_admin_show_message( my_message, 'warning', 1000 );                                              
                    window.location.href='<?php echo wpbc_get_settings_url() . '&tab=form&booking_form=standard'; ?>';
                </script> <?php
        } else {
            ?>
                <script type="text/javascript">
                    var my_message = '<?php echo html_entity_decode( esc_js( __( 'There are no extended booking forms', 'booking' ) ), ENT_QUOTES ); ?>';
                    wpbc_admin_show_message( my_message, 'warning', 1000 );                          
                    window.location.href='<?php echo wpbc_get_settings_url() . '&tab=form&booking_form=standard'; ?>';
                </script> <?php
        }
    }
}
add_bk_action('wpbc_make_delete_custom_booking_form', 'wpbc_make_delete_custom_booking_form' );
    



    ////////////////////////////////////////////////////////////////////////////
    //   S e a s o n      F i l t e r s      T a b l e 
    ////////////////////////////////////////////////////////////////////////////

    /** Show Toolbar at  Booking > Resources page - Add New Resource */
    function wpbc_add_new_seasonfilters_toolbar() {

        $submit_form_name = WPBC_Page_Settings__seasonfilters::ACTION_FORM;
        
        wpbc_clear_div();

        //  Toolbar ////////////////////////////////////////////////////////////////

        ?><div id="toolbar_seasonfilters" style="position:relative;"><?php

            wpbc_bs_toolbar_sub_html_container_start();

            //  T o o l b a r
            ?><div id="seasonfilters_toolbar_container" class="visibility_container clearfix-height" style="display:block;margin-top:-5px;"><?php 
                
                ?><div class="control-group wpbc-no-padding" style="margin:8px 15px 0 0;"><?php 
                    ?><a href="javascript:void(0);" 
                        onclick="javascript:jQuery('#action_<?php echo $submit_form_name; ?>').val('create_filter_range_days');jQuery('#<?php echo $submit_form_name; ?>').submit();"
                        style="margin:0 15px 0 0;"
                         class="button tooltip_top" data-original-title="<?php _e('Create dates filter' , 'booking'); ?>">
                         <span class="glyphicon glyphicon-th" aria-hidden="true"></span>&nbsp;<span class="in-button-text"><?php 
                                _e('Create dates filter', 'booking');
                    ?></span></a><?php    

                    ?><a href="javascript:void(0);" 
                        onclick="javascript:jQuery('#action_<?php echo $submit_form_name; ?>').val('create_filter_conditional');jQuery('#<?php echo $submit_form_name; ?>').submit();"
                        style="margin:0;"
                        class="button tooltip_top" data-original-title="<?php _e('Create conditional days filter' , 'booking'); ?>">
                            <span class="glyphicon glyphicon-equalizer" aria-hidden="true"></span>&nbsp;<span class="in-button-text"><?php 
                                _e('Create conditional days filter', 'booking'); 
                    ?></span></a><?php    
                    
                ?></div><?php 
                
                wpbc_clear_div();

               // wpbc_toolbar_expand_collapse_btn( 'advanced_booking_filter' );   

            ?></div><?php

            wpbc_bs_toolbar_sub_html_container_end();       

        ?></div><?php

        wpbc_clear_div();

    }