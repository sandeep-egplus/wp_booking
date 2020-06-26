<?php /**
 * @version 1.0
 * @package Booking > Resources > Availability page 
 * @category Settings page 
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2016-09-01
 * 
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


/**
	 * Show Content
 *  Update Content
 *  Define Slug
 *  Define where to show
 */
class WPBC_Page_Settings__ravailability extends WPBC_Page_Structure {

    const SUBMIT_FORM = 'wpbc_ravailability';                                   // Main Form Name
    const ACTION_FORM = 'wpbc_ravailability_action';                            // Form for sub-actions: like Add New | Edit actions

    const IS_SHOW_DESCRIPTION_COLLUMN = true;                                   // If not show,  then  its will  improve performace.
    
    private $html_prefix = 'avy_';
    private $edit_resource_id = 0;
    private $edit_availability = false;

    
    public function in_page() {
        return 'wpbc-resources';
    }
    

    public function tabs() {
        
        $tabs = array();
                
        $tabs[ 'availability' ] = array(
                              'title'       => __('Availability','booking')            // Title of TAB    
                            , 'hint'        => __('Configuration of availability for booking resources', 'booking')                      // Hint    
                            , 'page_title'  => __('Availability Settings', 'booking')                                // Title of Page    
                            //, 'link'      => ''                               // Can be skiped,  then generated link based on Page and Tab tags. Or can  be extenral link
                            //, 'position'  => 'left'                           // 'left'  ||  'right'  ||  ''
                            //, 'css_classes'=> ''                              // CSS class(es)
                            //, 'icon'      => ''                               // Icon - link to the real PNG img
                            , 'font_icon' => 'glyphicon glyphicon-check'        // CSS definition  of forn Icon
                            , 'default'   => false                              // Is this tab activated by default or not: true || false. 
                            //, 'disabled'  => false                            // Is this tab disbaled: true || false. 
                            , 'hided'     => false                              // Is this tab hided: true || false. 
                            , 'subtabs'   => array()   
                    );
        
        /*
        $subtabs = array();        
        $subtabs[ 'gcal' ] = array( 
                            'type' => 'subtab'                                  // Required| Possible values:  'subtab' | 'separator' | 'button' | 'goto-link' | 'html'
                            , 'title' => __('Google Calendar' ,'booking') . '  - ' . __('Events Import' ,'booking')         // Title of TAB    
                            , 'page_title' => __('Google Calendar' ,'booking') . ' ' . __('Settings' ,'booking')    // Title of Page   
                            , 'hint' => __('Customization of synchronization with Google Calendar' ,'booking')      // Hint    
                            , 'link' => ''                                      // link
                            , 'position' => ''                                  // 'left'  ||  'right'  ||  ''
                            , 'css_classes' => ''                               // CSS class(es)
                            //, 'icon' => 'http://.../icon.png'                 // Icon - link to the real PNG img
                            //, 'font_icon' => 'glyphicon glyphicon-envelope'   // CSS definition of Font Icon
                            , 'default' =>  true                                // Is this sub tab activated by default or not: true || false. 
                            , 'disabled' => false                               // Is this sub tab deactivated: true || false. 
                            , 'checkbox'  => false                              // or definition array  for specific checkbox: array( 'checked' => true, 'name' => 'feature1_active_status' )   //, 'checkbox'  => array( 'checked' => $is_checked, 'name' => 'enabled_active_status' )
                            , 'content' => 'content'                            // Function to load as conten of this TAB
                        );        
        $tabs[ 'users' ]['subtabs'] = $subtabs;
        */
        
        return $tabs;
    }


    /** Show Content of Settings page */
    public function content() {

        $this->css();
        
        ////////////////////////////////////////////////////////////////////////
        // Checking 
        ////////////////////////////////////////////////////////////////////////

        do_action( 'wpbc_hook_settings_page_header', 'availability');              // Define Notices Section and show some static messages, if needed
        
        if ( ! wpbc_is_mu_user_can_be_here('activated_user') ) return false;    // Check if MU user activated, otherwise show Warning message.
   
        // if ( ! wpbc_is_mu_user_can_be_here('only_super_admin') ) return false;  // User is not Super admin, so exit.  Basically its was already checked at the bottom of the PHP file, just in case.
        
        
        ////////////////////////////////////////////////////////////////////////
        // Load Data 
        ////////////////////////////////////////////////////////////////////////
        
        
        ////////////////////////////////////////////////////////////////////////
        //  S u b m i t   Main Form  
        ////////////////////////////////////////////////////////////////////////
        
        // $this->get_api()->validated_form_id = self::SUBMIT_FORM;             // Define ID of Form for ability to  validate fields (like required field) before submit.
        
        if ( isset( $_POST['is_form_sbmitted_'. self::SUBMIT_FORM ] ) ) {

            // Nonce checking    {Return false if invalid, 1 if generated between, 0-12 hours ago, 2 if generated between 12-24 hours ago. }
            $nonce_gen_time = check_admin_referer( 'wpbc_settings_page_' . self::SUBMIT_FORM );  // Its stop show anything on submiting, if its not refear to the original page

            // Save Changes 
            $this->update();
        }                
        
        
         
        ////////////////////////////////////////////////////////////////////////
        // JavaScript: Tooltips, Popover, Datepick (js & css) 
        ////////////////////////////////////////////////////////////////////////
        
        echo '<span class="wpdevelop">';
        
        wpbc_js_for_bookings_page();                                        

        //  T o o l b a r
        if ( class_exists( 'wpdev_bk_biz_l' ) ) {
            
            
            wpbc_bs_toolbar_sub_html_container_start();
                    
            ?><div id="booking_resources_toolbar_container" class="visibility_container clearfix-height" style="display:block;margin-top:-5px;"><?php 
                ?><div class="control-group wpbc-no-padding" style="float:right;margin-right: 0;margin-left: 15px;"><?php 

                    // Show | Hide Children
                
                    ?><a href="javascript:void(0);" onclick="javascript:jQuery('.wpbc_resource_child').toggle(500);jQuery('.wpbc_show_hide_children').toggle();" 
                         class="button wpbc_show_hide_children tooltip_left" data-original-title="<?php _e('Show Children Resources' , 'booking') ?>" style="display:none;"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a><?php    
                    ?><a href="javascript:void(0);" onclick="javascript:jQuery('.wpbc_resource_child').toggle(500);jQuery('.wpbc_show_hide_children').toggle();" 
                         class="button wpbc_show_hide_children tooltip_left" data-original-title="<?php _e('Hide Children Resources' , 'booking') ?>"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span></a><?php    
                    
                    /**
	 * Save Button
                     * Note! This button submit saving of chnages to Booking Resources Table
                        
                        ?><a                 
                             class="button button-primary " 
                             href="javascript:void(0)"
                             onclick="javascript:jQuery('#wpbc_bresources').submit();" 
                             ><?php _e('Save Changes' , 'booking') ?></a><?php    
                    */
                    
                ?></div><?php
            ?></div><?php
            wpbc_bs_toolbar_sub_html_container_end();       
        }


        echo '</span>';

        ?><div class="clear" style="margin-bottom:20px;"></div><?php

        
        wpbc_toolbar_search_by_id__top_form( array( 
                                                    'search_form_id' => 'wpbc_booking_resources_search_form'
                                                  , 'search_get_key' => 'wh_resource_id'
                                                  , 'is_pseudo'      => false
                                            ) );

        
        ////////////////////////////////////////////////////////////////////////
        // Content  ////////////////////////////////////////////////////////////
        ?>
        <div class="clear" style="margin-bottom:0px;"></div>
        <span class="metabox-holder"><?php 
        
            ////////////////////////////////////////////////////////////////////
            // Sub Actions Form
            ////////////////////////////////////////////////////////////////////     
        
            ?><form  name="<?php echo self::ACTION_FORM; ?>" id="<?php echo self::ACTION_FORM; ?>" action="<?php 
            
                    // Need to  exclude 'edit_resource_id' parameter from  $_GET,  if we was using direct link for ediing,  in case for edit other season filters....
                    $exclude_params = array( 'edit_resource_id' );
                    $only_these_parameters = false;// array( 'tab', 'page_num', 'wh_search_id' );
                    $is_escape_url = false;
                    $only_get = true; 
                    echo wpbc_get_params_in_url( wpbc_get_resources_url( false, false ), $exclude_params, $only_these_parameters, $is_escape_url , $only_get );

                    ?>" method="post" autocomplete="off"><?php                           
               // N o n c e   field, and key for checking   S u b m i t 
               wp_nonce_field( 'wpbc_settings_page_' . self::ACTION_FORM );

		        // Add hidden input SEARCH KEY field into  main form, if previosly was searching by ID or Title
				wpbc_hidden_search_by_id_field_in_main_form( array( 'search_get_key' => 'wh_resource_id' ) );			//FixIn: 8.0.1.12

            ?><input type="hidden" name="is_form_sbmitted_<?php echo self::ACTION_FORM; ?>" id="is_form_sbmitted_<?php echo self::ACTION_FORM; ?>" value="1" /><?php   
            
                ?><input type="hidden" name="action_<?php echo self::ACTION_FORM; ?>"    id="action_<?php echo self::ACTION_FORM; ?>"    value="-1" /><?php                 
                ?><input type="hidden" name="edit_resource_id_<?php echo self::ACTION_FORM; ?>" id="edit_resource_id_<?php echo self::ACTION_FORM; ?>" value="-1" /><?php                 

                $id_of_selected_resources = $this->wpbc_check_sub_actions();    // Check  "Adding New" season filter | Edit | Delete single exist  season filter.
             
                if ( ! empty( $id_of_selected_resources ) ) {
                    
                    $this->edit_resource_id = explode( ',', $id_of_selected_resources );
                }
            ?></form><?php
        
            
            ////////////////////////////////////////////////////////////////////
            // Main Form
            ////////////////////////////////////////////////////////////////////
            
            ?><form  name="<?php echo self::SUBMIT_FORM; ?>" id="<?php echo self::SUBMIT_FORM; ?>" action="" method="post" autocomplete="off">
                <?php 
                   // N o n c e   field, and key for checking   S u b m i t 
                   wp_nonce_field( 'wpbc_settings_page_' . self::SUBMIT_FORM );
                ?><input type="hidden" name="is_form_sbmitted_<?php echo self::SUBMIT_FORM; ?>" id="is_form_sbmitted_<?php echo self::SUBMIT_FORM; ?>" value="1" /><?php                 
                ?>
                <div class="clear" style="margin-top:20px;"></div>
                <div id="wpbc_booking_resource_table" class="wpbc_settings_row wpbc_settings_row_rightNO"><?php 
                
                    // wpbc_open_meta_box_section( 'wpbc_settings_ravailability_resources', __('Resources', 'booking') );
                        
                        $this->wpbc_resources_table__show();
                        
                    // wpbc_close_meta_box_section();
                ?>
                </div>
                <div class="clear"></div>                
                <select id="bulk-action-selector-bottom" name="bulk-action">
                    <option value="-1"><?php _e('Bulk Actions', 'booking'); ?></option>
                    <option value="edit"><?php _e('Set Availability', 'booking'); ?></option>
                    <option value="delete"><?php _e('Delete', 'booking'); ?></option>
                </select>    
                
                <a href="javascript:void(0);" id="wpbc_button_save"
                  class="button button-primary wpbc_button_save" style="display:none;" ><?php _e('Set Availability','booking'); ?></a>
                <a href="javascript:void(0);" id="wpbc_button_delete"
                  class="button wpbc_button_delete" style="display:none;background: #d9534f;border:#b92c28 1px solid;color:#eee;" ><?php _e('Delete','booking'); ?></a>
                
                    
                <span class="wpbc_button_delete" style="display:none;">
                    <div class="clear" style="height:10px;"></div>
                    <div class="wpbc-settings-notice notice-warning" style="text-align:left;">
                        <strong><?php _e('Note!' ,'booking'); ?></strong> <?php 
                            printf( __('Please reassign exist booking(s) from selected resource(s) to other resources or delete exist booking(s) from  this resource(s). Otherwise you will have %slost bookings%s.' ,'booking')
                                    ,'<a href="' . wpbc_get_menu_url('booking') . '&wh_booking_type" >','</a>');
                        ?>
                    </div>
                    <div class="clear" ></div>
                </span>    
                    
                    
            </form>
            <script type="text/javascript">
                jQuery('#bulk-action-selector-bottom').on( 'change', function(){    
                    jQuery('.wpbc_button_save').hide();
                    jQuery('.wpbc_button_delete').hide();
                    if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'delete' ) { 
                        jQuery('.wpbc_button_delete').show();
                    } 
                    if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'edit' ) { 
                        jQuery('.wpbc_button_save').show();
                    }
                } ); 
                jQuery('#wpbc_button_delete').on( 'click', function(){    
                    if ( wpbc_are_you_sure('<?php echo esc_js( __('Do you really want to do this ?' ,'booking') ); ?>') ) { 
                        jQuery('#<?php echo self::SUBMIT_FORM; ?>').submit(); 
                    }
                } ); 
                jQuery('#wpbc_button_save').on( 'click', function(){    
                    var cheked_elements = [];
                    var selected_element = '';
                        jQuery( '#<?php echo self::SUBMIT_FORM; ?> .wpbc_selectable_body .check-column input[type=\'checkbox\']:checked' ).each(function() {
                            selected_element = jQuery( this ).val();                           
                            selected_element = selected_element.substr( 9 );
                            cheked_elements.push( selected_element );
                        });
                    cheked_elements = cheked_elements.join( ',' );              // resources separator
                    jQuery( '#edit_resource_id_<?php echo self::ACTION_FORM; ?>' ).val( cheked_elements );
                    jQuery( '#action_<?php echo self::ACTION_FORM; ?>' ).val( 'set_availability' );
                    jQuery('#<?php echo self::ACTION_FORM; ?>').submit();
                } );
                // Show Available or Unavailable label before Season Table
                jQuery('input[name=\'<?php echo $this->html_prefix; ?>availability_general\']').on( 'change', function(){  
                    jQuery( '.wpbc_general_availability_item' ).addClass( 'hidden_items' );
                    jQuery( '.wpbc_availability_item' ).addClass( 'hidden_items' );                        
                    if ( jQuery( this ).val() == 'On' ) {                        
                        jQuery( '.wpbc_general_availability_item.wpbc_available_item' ).removeClass( 'hidden_items' );
                        jQuery( '.wpbc_availability_item.wpbc_unavailable_item' ).removeClass( 'hidden_items' );
                    } else {
                        jQuery( '.wpbc_general_availability_item.wpbc_unavailable_item' ).removeClass( 'hidden_items' );
                        jQuery( '.wpbc_availability_item.wpbc_available_item' ).removeClass( 'hidden_items' );
                    }
                } );              
            </script>
        </span>
        <?php       
    
        do_action( 'wpbc_hook_settings_page_footer', 'availability' );
        
        $this->enqueue_js();
    }


    /** Save Chanages */  
    public function update() {

        // if (  ( wpbc_is_this_demo() ) ||  ( ! class_exists( 'wpdev_bk_personal' ) )  )
        //    return;

        global $wpdb;

        $wpbc_br_table = new WPBC_BR_Table( 'resources_submit' );
        $linear_resources_for_one_page = $wpbc_br_table->get_linear_data_for_one_page();
                
        if ( isset( $_POST['bulk-action' ] ) ) 
            $submit_action = $_POST['bulk-action' ];
        else 
            $submit_action = 'edit';
        
        $bulk_action_arr_id = array();

        foreach ( $linear_resources_for_one_page as $resource_id => $resource ) {

            // Check posts of only visible on page booking resources 
            if ( isset( $_POST['br-select-' . $resource_id ] ) ) {              // !!!  > Check  if the checkbox selected - in other cases we need to  check  some other field here to  save data

                    switch ( $submit_action ) {
                        case 'delete':                                          // Delete

                            if ( isset( $_POST['br-select-' . $resource_id ] ) )
                                    $bulk_action_arr_id[] = intval( $resource_id );
                            break;

                        default:                                                // Edit
                            break;
                    }

                    
                //}
            }

        }

      
        if ( ! empty( $bulk_action_arr_id ) ) {
            
            switch ( $submit_action ) {

                case 'delete':                                          // Delete
                    $bulk_action_arr_id = implode( ',', $bulk_action_arr_id );
                    $sql = "DELETE FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id IN ({$bulk_action_arr_id})";

                    if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error during deleting items in DB' ,__FILE__ , __LINE__); }         // Action inDB

                    wpbc_show_message ( __( 'Deleted', 'booking'), 5 );

                default:                                                // Edit
                    break;
            }
        }
        
        make_bk_action( 'wpbc_reinit_booking_resource_cache' );

        
        /**  // Get Validated 
        $validated_fields = $this->get_api()->validate_post();        
        $validated_fields = apply_filters( 'wpbc_fields_before_saving_to_db__ravailability', $validated_fields );   //Hook for validated fields.                   
        $this->get_api()->save_to_db( $validated_fields );

        // Old way of saving:
        // update_bk_option( 'booking_cache_expiration' , WPBC_Settings_API::validate_text_post_static( 'booking_cache_expiration' ) );
        */
                     
    }



    // <editor-fold     defaultstate="collapsed"                        desc=" CSS  &   JS   "  >
    
    /** CSS for this page */
    private function css() {
        ?>
        <style type="text/css"> 
            .wpbc_in_table_button {
                margin: 3px 5px;
            }
            .wpbc_available_item_text {
                color: #5cb85c;
            }
            .wpbc_unavailable_item_text {
                color: #d9534f;
            }
            .label.wpbc_general_availability_item, 
            .label.wpbc_availability_item {
                font-size:0.9em;
            }
            .wpbc_edited_resource_label .label {
                font-size:0.9em;
                font-style: normal;
                font-weight: 600;
            }
            .form-table .description {
                line-height: 2em;
            }
            .wpbc-help-message {
                border:none;
            }
            /* Selectbox element in toolbar */
            .visibility_container select optgroup{                            
                color:#999;
                vertical-align: middle;
                font-style: italic;
                font-weight: 400;
            }
            .visibility_container select option {
                padding:5px;
                font-weight: 600;
            }
            .visibility_container select optgroup option{
                padding: 5px 20px;       
                color:#555;
                font-weight: 600;
            }
            #wpbc_create_new_custom_form_name_fields {
                width: 360px;
                display:none;
            }
            @media (max-width: 399px) {
                #wpbc_create_new_custom_form_name_fields {
                    width: 100%;
                }          
            }
        </style>
        <?php
    }
    
    
    
    /**
	 * Add Custon JavaScript - for some specific settings options
     *      Executed After post content, after initial definition of settings,  and possible definition after POST request.
     * 
     * @param type $menu_slug
     */
    private function enqueue_js(){                                                        
        
        // JavaScript //////////////////////////////////////////////////////////////
        
        $js_script = '';
        
        /*
        // Hide|Show  on Click      Radion
        $js_script .= " jQuery('input[name=\"paypal_pro_hosted_solution\"]').on( 'change', function(){    
                                jQuery('.wpbc_sub_settings_paypal_account_type').addClass('hidden_items'); 
                                if ( jQuery('#paypal_type_standard').is(':checked') ) {   
                                    jQuery('.wpbc_sub_settings_paypal_standard').removeClass('hidden_items');
                                } else {
                                    jQuery('.wpbc_sub_settings_paypal_pro_hosted').removeClass('hidden_items');
                                }
                            } ); ";        
        */
        ////////////////////////////////////////////////////////////////////////
        
        
        // Eneque JS to  the footer of the page
        wpbc_enqueue_js( $js_script );                
    }

    // </editor-fold>
    
    
    //                                                                          <editor-fold   defaultstate="collapsed"   desc=" B o o k i n g      R e s o u r c e s      T a b l e  " >    
    
    /** Show booking resources table */
    public function wpbc_resources_table__show() {    
        // echo ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() . '<div class="clear" style="height:20px;"></div>' : '' );
        
        $columns = array();
        $columns[ 'check' ] = array( 'title' => '<input type="checkbox" value="" id="br-select-all" name="resource_id_all" />'
                                        , 'class' => 'check-column' 
                                    );
        $columns[ 'id' ] = array(         'title' => __( 'ID' )
                                        , 'style' => 'width:5em;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'sortable' => true 
                                    );
        
        //$columns = apply_filters ('wpbc_resources_table_header__cost_title' , $columns );
        
        $columns[ 'title' ] = array(      'title' => __( 'Resource Name', 'booking' )
                                        , 'style'   => 'width:15em;'
                                        , 'sortable' => true 
                                    );
                       
        $columns[ 'availability' ] = array(   'title' => __( 'Availability', 'booking' )
                                            , 'style'   => 'width:10em;text-align:left;padding-left:1em;'
                                            , 'class'   => ''                   // 'wpbc_hide_mobile'        
                                    );
        
        if ( self::IS_SHOW_DESCRIPTION_COLLUMN )
            $columns[ 'availability_descr' ] = array(   'title' => __( 'Description', 'booking' )
                                            , 'style'   => ''
                                            , 'class'   => 'wpbc_hide_mobile'        
                                    );
                               
        $columns = apply_filters ('wpbc_resources_table_header__info_title' , $columns );                                                                
        $columns = apply_filters ('wpbc_resources_table_header__user_title' , $columns );        
        
        $wpbc_br_table = new WPBC_BR_Table( 
                            'resources' 
                            , array(
                                  'url_sufix'   =>  '#wpbc_resources_link'
                                , 'rows_func'   =>  array( $this, 'wpbc_resources_table__show_rows' ) 
                                , 'columns'     =>  $columns
                                , 'is_show_pseudo_search_form' => false                                
                            )
                        );

        $wpbc_br_table->display();             
    }   
    

    /**
	 * Show rows for booking resource table
     * 
     * @param int $row_num
     * @param array $resource
     */
    public function wpbc_resources_table__show_rows( $row_num, $resource ) {

        $css_class = ' wpbc_resource_row';
        $is_selected_row = false;
        if (
                (       ( is_array( $this->edit_resource_id ) )  
                    &&  ( in_array( $resource['id'], $this->edit_resource_id ) ) 
                )
             ||     
                ( $this->edit_resource_id == $resource['id'] ) 
            ){
            $css_class .= ' row_selected_color';
            $is_selected_row = true;
        }
        
        
        if ( class_exists( 'wpdev_bk_biz_l' ) ) {

                if ( intval($resource['count'] ) > 1 ) {  
                    $css_class .= ' wpbc_resource_parent wpbc_resource_capacity' . $resource['count'] ;
                } else {

                    if ( empty( $resource['parent'] ) ) {
                        $css_class .= ' wpbc_resource_single';
                    } else {
                        $css_class .= ' wpbc_resource_child';
                    }
                }
        }
                
        ?><tr class="wpbc_row<?php echo $css_class; ?>" id="resource_<?php echo $resource['id']; ?>"><?php

            ?><th class="check-column">
                    <label class="screen-reader-text" for="br-select-<?php echo $resource['id' ]; ?>"><?php echo esc_js(__('Select Booking Resource', 'booking')); ?></label>
                    <input type="checkbox" 
                                   id="br-select-<?php echo $resource['id' ]; ?>" 
                                   name="br-select-<?php echo $resource['id' ]; ?>" 
                                   value="resource_<?php echo $resource['id' ]; ?>" 
                                   <?php checked( $is_selected_row ); ?>
                        />       
            </th>
            <td class="wpbc_hide_mobile"><?php echo $resource['id' ]; ?></td>
            <?php //do_action( 'wpbc_resources_table_show_col__cost_text', $row_num, $resource ); ?>
            <td>
                <span style="float:right;<?php  
                            if ( ! empty( $resource['parent']) ) {
                               echo 'width:95%;font-weight:400;';
                            } else {
                              echo 'width:100%;font-weight:600;';
                            }
                            ?>"><?php echo esc_attr( $resource['title'] ); ?></span>
            </td>
            <td style="text-align:left;">
                <a  class="tooltip_top button-secondary button wpbc_in_table_button" 
                    href="javascript:void(0);" 
                    onclick="javascript:jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('set_availability');
                                        jQuery('#edit_resource_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                        jQuery('#<?php echo self::ACTION_FORM; ?>').submit();"                            
                    title="<?php _e('Availability' ,'booking'); ?>"
                ><?php _e('Availability', 'booking'); ?></a>                        
            </td>
            <?php if ( self::IS_SHOW_DESCRIPTION_COLLUMN ) { ?>
            <td class="wpbc_hide_mobile wpdevelop" style="font-size:0.9em;font-style: italic;"><?php 
            
                $availability_res = wpbc_get_resource_meta( $resource['id' ], 'availability' );
                if ( count( $availability_res )>0 ) {

                    $availability = maybe_unserialize( $availability_res[0]->value );                              


                    echo __('All days', 'booking') . ' <strong>' . ( ( $availability[ 'general' ] == 'On' ) 
                                                                        ? '<span class="wpbc_available_item_text">' . __('available', 'booking' ) . '</span>'
                                                                        : '<span class="wpbc_unavailable_item_text">' . __('unavailable', 'booking' ) . '</span>'
                                                                      ) . '</strong>';


                    $active_season_filters_names = array();

                    foreach ( $availability['filter'] as $season_filter_id => $season_filter_state ) {
                        if ( $season_filter_state == 'On' ) {

                            $wpbc_sf_cache = wpbc_sf_cache();
                            $item_arr = $wpbc_sf_cache->get_resource_attr( $season_filter_id );             

                            // $active_season_filters_names[] =  '"<strong>' . apply_bk_filter( 'wpdev_check_for_active_language', $item_arr['title'] ) . '</strong>"';
                            
                            
                            $active_season_filters_names[] = '<a data-original-title="'. ( esc_html( strip_tags( wpbc_get_filter_description( $item_arr['filter'] ) ) ) ) .'" 
                                                                class="tooltip_top" style="" 
                                                                href="' . wpbc_get_resources_url() . '&tab=filter&edit_season_id=' . $item_arr['booking_filter_id' ] . '&wh_search_id=' . $item_arr['booking_filter_id' ] . '"
                                                              >' . apply_bk_filter( 'wpdev_check_for_active_language', $item_arr['title'] ) . '</a>';
                            
                        }
                    }

                    if ( ! empty( $active_season_filters_names ) ) {

                        echo ' ' . sprintf( __('and %s on seasons:','booking'), 
                                            ' <strong>' . ( ( $availability[ 'general' ] == 'Off' ) 
                                                            ? '<span class="wpbc_available_item_text">' . __('available', 'booking' ) . '</span>'
                                                            : '<span class="wpbc_unavailable_item_text">' . __('unavailable', 'booking' ) . '</span>'
                                                          ) . '</strong>'
                                           ) . ' ';
                        echo implode( ', ', $active_season_filters_names );
                    }
                } else {
                    echo ' --- ';
                }

            ?></td><?php 
            }
            ?> 
            <?php do_action( 'wpbc_resources_table_show_col__info_text',    $row_num, $resource ); ?>                    
            <?php do_action( 'wpbc_resources_table_show_col__user_text',         $row_num, $resource ); ?>
                    
        </tr>
        <?php    
    }

    //                                                                              </editor-fold>
    
    
    //                                                                              <editor-fold   defaultstate="collapsed"   desc=" A c t i o n  s    F o r m " >    
    
    public function wpbc_check_sub_actions(){

        global $wpdb;
        
        $html_prefix = 'bra_';
        $edit_resource_id = '';
        
        if ( isset( $_GET['edit_resource_id'] ) ) {                                    // In case if we need to  open  direct  link for editing some filter
            
            $action     = 'set_availability';
            $edit_resource_id  = wpbc_clean_digit_or_csd( $_GET['edit_resource_id'] );
        } else {
        
                if ( isset( $_POST['is_form_sbmitted_'. self::ACTION_FORM ] ) ) {

                    // Nonce checking    {Return false if invalid, 1 if generated between, 0-12 hours ago, 2 if generated between 12-24 hours ago. }
                    $nonce_gen_time = check_admin_referer( 'wpbc_settings_page_' . self::ACTION_FORM );  // Its stop show anything on submiting, if its not refear to the original page

                    // If we have wrong nonce,  so its will be stop executing here            
                } else 
                    return false;                                                       // If we do  not submit sub action,  then exit    

                ////////////////////////////////////////////////////////////////////////

                if ( isset( $_POST[ 'action_' .  self::ACTION_FORM ] ) ){ 
                    $action = $_POST[ 'action_' .  self::ACTION_FORM ];
                }
                
                if ( empty( $action ) ) return  false;

                if ( isset( $_POST[ 'edit_resource_id_' .  self::ACTION_FORM ] ) ) {
                    //$edit_resource_id = WPBC_Settings_API::validate_text_post_static( 'edit_resource_id_' .  self::ACTION_FORM );
                    $edit_resource_id = wpbc_clean_digit_or_csd( $_POST[ 'edit_resource_id_' .  self::ACTION_FORM ] );
                }
        }
        ////////////////////////////////////////////////////////////////////////
        
        if (  in_array( $action, array( 'set_availability' ) )  )               // Actions for showing section - MetaBox
             $is_show_section = true;
        else $is_show_section = false;
                
        if ( $is_show_section ) {
            ?><div class="clear" style="margin-top:20px;"></div><?php 
            ?><div id="wpbc_<?php echo $this->html_prefix; ?>table_<?php echo $action; ?>" class="wpbc_settings_row wpbc_settings_row_rightNO"><?php                   
        }
        
        ////////////////////////////////////////////////////////////////////////
        if ( ! empty( $edit_resource_id ) ) {
            
            $selected_booking_resources_id = explode( ',', $edit_resource_id );                    
            
            switch ( $action ) {

                ////////////////////////////////////////////////////////////////////
                // S Q L
                ////////////////////////////////////////////////////////////////////

                case 'update_sql_availability': 

                    // Validate                                                     // Title
                    // $validated_title = WPBC_Settings_API::validate_text_post_static( $html_prefix . 'days_filter_name' );

                    $available_sf = new WPBC_SF_Table_all_seasons( 
                                    'seasonfilters' 
                                    , array(
                                          'url_sufix'   =>  '#wpbc_' . $this->html_prefix . 'sf_table'  // Link to  scroll
                                        //, 'rows_func'   =>  array( $this, 'seasonfilters_table__show_rows' ) 
                                        , 'columns'     =>  array()
                                        , 'is_show_pseudo_search_form' => false
                                        , 'edit_booking_resource_id_arr'    => $selected_booking_resources_id
                                    )
                                );
                    $season_filters_for_br = $available_sf->get_linear_data_for_one_page();

                    // Loop all  selected booking resources.
                    foreach ( $selected_booking_resources_id as $resource_id ) { 
                        
                        // Get  previous saved maeta availability data
                        $availability_res = wpbc_get_resource_meta( $resource_id, 'availability' );

                        $availability = array();
                        $availability[ 'general' ] = 'On';
                        $availability[ 'filter' ]  = array();                   // [filter] => Array ( [33] => Off,  [29] => Off, [27] => On, ... )
                        if ( count( $availability_res ) > 0 ) {

                            $availability = maybe_unserialize( $availability_res[0]->value ); 
                            
                            $availability[ 'filter' ]  = array();               
                                                                                /**
	 * Clear previos data of season filter. For exmaple its can impact in situation,
                                                                                    when some season filters was re-aasigned to  different User and not  these 
                                                                                    season filters does not listed in the actual season filter table for this resource.
                                                                                */
                        }
           
                        // Check $_POST  and update data to  this resource
                        
                        $availability[ 'general' ] = WPBC_Settings_API::validate_radio_post_static( $this->html_prefix . 'availability_general' );                   
                        
                        
                        // List  of season filters that  available (listed in table)  for this booking resource(s)
                        foreach ( $season_filters_for_br as $season_filter_id => $season_filter_data) {
                            
                            if ( isset( $_POST[ $this->html_prefix . 'select_' . $season_filter_id ] ) ) {
                                // Enabled
                                $availability[ 'filter' ][ $season_filter_id ] = 'On';
                            } else {
                                // Disabled
                                $availability[ 'filter' ][ $season_filter_id ] = 'Off';
                            }
                        }

                        // Save new meta availability data     
                        wpbc_save_resource_meta( $resource_id, 'availability', $availability );
                        
                        wpbc_show_changes_saved_message();   

                        make_bk_action( 'wpbc_reinit_seasonfilters_cache' );                        
                    }    
                    // break;               // We comment it,  because after saving this info,  we need to  show availability            


                ////////////////////////////////////////////////////////////////////
                // Edit Visual Section
                ////////////////////////////////////////////////////////////////////

                case 'set_availability':                                          // Edit Section

                        // Get data
                        $resource_titles = array();                    
                        $wpbc_br_cache = wpbc_br_cache();
                        foreach ( $selected_booking_resources_id as $bk_res_id ) {

                            $title_res =  apply_bk_filter('wpdev_check_for_active_language', $wpbc_br_cache->get_resource_attr( $bk_res_id, 'title') );

                            $resource_titles[]= $title_res;
                        }

                        wpbc_open_meta_box_section( $action . '_new', __('Set Availability', 'booking') );

                            $this->seasonfilters_section( $action, $edit_resource_id , $resource_titles );

                        wpbc_close_meta_box_section();
                        
                    break;

                default:
                    return false;
                    break;
            }
            
        } else {
            wpbc_show_message_in_settings( __( 'Nothing Found', 'booking' ) . '.', 'warning', __('Error' ,'booking') . '.' );
        }
        ////////////////////////////////////////////////////////////////////////

        if ( $is_show_section ) {    
                
            ?><div class="clear" style="margin-top:20px;"></div><?php 
            ?></div><?php                 
        }
        
        ////////////////////////////////////////////////////////////////////////
        
        return $edit_resource_id;
    }
    

    /**
	 * Show Availability Section  for setting availability for selected booking resource(s)
     * 
     * @param string $action    - name of action
     * @param string $edit_resource_id - ID of booking resource (one or several , separated ) - for settings availability 
     */
    private function seasonfilters_section( $action, $edit_resource_id, $resource_titles ) {

        
// debuge( '$action, $edit_resource_id, $_POST ',  $action, $edit_resource_id, $_POST  );   

        $edit_resource_id_array = explode( ',', $edit_resource_id );    

        $edit_id = $edit_resource_id_array[0];

        $availability_res = wpbc_get_resource_meta( $edit_id, 'availability' );

        $availability = array();
        $availability[ 'general' ] = 'On';
        $availability[ 'filter' ]  = array();                               // [filter] => Array ( [33] => Off,  [29] => Off, [27] => On, ... )

        if ( count( $availability_res )>0 ) {

            $availability = maybe_unserialize( $availability_res[0]->value );                              
        }
        $this->edit_availability = $availability;


        ////////////////////////////////////////////////////////////////////
        // General - Available or Unavailable 
        ////////////////////////////////////////////////////////////////////

        ?><table class="form-table"><tbody><?php   

            $resource_titles_text = array();
            foreach ( $resource_titles as $single_resource_title ) {
                $resource_titles_text[] = '<span class="label label-default label-info" >' . $single_resource_title . '</span>';
            }
            $resource_titles_text = '<span class="wpdevelop">' . implode(' ', $resource_titles_text ) . '</span>';


            ?>
            <tr valign="top" >
                <th scope="row" style="vertical-align:middle;">
                    <?php 
                    if ( count($resource_titles) > 1 )  _e('Resources', 'booking'); 
                    else                                _e('Resource', 'booking'); 
                    ?>
                </th>
                <td class="description wpbc_edited_resource_label">
                    <?php echo $resource_titles_text; ?>
                </td> 
            </tr>                
            <?php

            WPBC_Settings_API::field_radio_row_static(                                              
                                                      $this->html_prefix . 'availability_general'
                                            , array(  
                                                      'type'              => 'radio'
                                                    , 'options'           => array(
                                                                                      'On' => __( 'available', 'booking' )
                                                                                    , 'Off' => __( 'unavailable', 'booking' )

                                                    )
                                            , 'value'             => $availability[ 'general' ]
                                                    , 'title'             => __('All days', 'booking')

                                                                            .  '&nbsp;&nbsp;&nbsp;<span class="wpdevelop">'
                                                                                . '<span class="label label-default label-danger ' 
                                                                                    . ( ( $availability[ 'general' ] == 'Off' ) ? '' : 'hidden_items' ) 
                                                                                    . ' wpbc_unavailable_item  wpbc_general_availability_item" >'  . __('unavailable', 'booking' ) . '</span>'

                                                                                . '<span class="label label-default label-success ' 
                                                                                    . ( ( $availability[ 'general' ] == 'On' ) ? '' : 'hidden_items' ) 
                                                                                    . ' wpbc_available_item wpbc_general_availability_item" >' . __('available', 'booking' )   . '</span>'
                                                                            . '</span>'            

                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'p'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => ''
                                                    , 'only_field'        => false
                                                    , 'attr'              => array()                                                    
                                                // , 'validate_as'       => array( 'required' )
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    )
                                    );

            ?>
            <tr valign="top" >
                <td colspan="2" class="description">
                    <?php 
                    printf(   __('Select %s days by activating specific season filter below or %sadd new season filter%s' ,'booking')

                                , '<span style="font-weight:600;" class=" ' 
                                       . ( ( $availability[ 'general' ] == 'On' ) ? '' : 'hidden_items' ) 
                                       . ' wpbc_unavailable_item  wpbc_availability_item" >'  . __('unavailable', 'booking' ) . '</span>' 

                                . '<span style="font-weight:600;" class=" ' 
                                       . ( ( $availability[ 'general' ] == 'Off' ) ? '' : 'hidden_items' ) 
                                       . ' wpbc_available_item wpbc_availability_item" >' . __('available', 'booking' )   . '</span>' 

                                , '<a class="button button-secondary" href="' . wpbc_get_resources_url() . '&tab=filter">','</a>'
                            );

                            
                    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );    

                    if ( ( $is_can ) && ( class_exists( 'wpdev_bk_multiuser' ) ) ) {
                        ?><span class="wpdevelop">
                        <a data-original-title="<?php echo esc_js( __('Hide season filters', 'booking') ); ?>" 
                           class="button wpbc_show_hide_children tooltip_left wpbc_seasonfilters_btn_to_hide" style="display:none;float:right;"
                           onclick="javascript: jQuery('.wpbc_seasonfilters_row_to_hide').addClass('hidden_items');jQuery(this).hide();jQuery('.wpbc_seasonfilters_btn_to_show').show();" 
                           href="javascript:void(0);" 
                           style="display: inline-block;"><span aria-hidden="true" class="glyphicon glyphicon-eye-close"></span></a>            
                        <a data-original-title="<?php echo esc_js( __('Show all exist season filters', 'booking') ); ?>" 
                           class="button wpbc_show_hide_children tooltip_left wpbc_seasonfilters_btn_to_show" style="float:right;"
                           onclick="javascript: jQuery('.wpbc_seasonfilters_row_to_hide').removeClass('hidden_items');jQuery(this).hide();jQuery('.wpbc_seasonfilters_btn_to_hide').show();" 
                           href="javascript:void(0);" 
                           style="display: inline-block;"><span aria-hidden="true" class="glyphicon glyphicon-eye-open"></span></a>
                        </span>
                        <?php
                    }
                            
                    ?>
                </td>
            </tr>                
            <?php

        ?></tbody></table><?php


        ////////////////////////////////////////////////////////////////////
        // Season Filters Table     
        ////////////////////////////////////////////////////////////////////
        $columns = array();
        $columns[ 'check' ] = array( 'title' => '<input type="checkbox" value="" id="' . $this->html_prefix . 'select_all" name="' . $this->html_prefix . 'select_id_all" />'
                                        , 'class' => 'check-column' 
                                    );
        $columns[ 'id' ] = array(         'title' => __( 'ID' )
                                        , 'style' => 'width:4em;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        //, 'sortable' => true 
                                    );

        $columns = apply_filters ('wpbc_seasonfilters_table_header__before_title' , $columns );


        $columns[ 'enabled' ] = array(    'title' => __( 'Enabled', 'booking' )
                                        , 'style' => 'width:5em'
                                        //, 'sortable' => true 
                                    );
        $columns[ 'title' ] = array(      'title' => __( 'Title', 'booking' )
                                        , 'style' => 'width:25%'
                                        //, 'sortable' => true 
                                    );

        $columns[ 'info' ] = array(      'title' => __( 'Info', 'booking' )
                                        , 'style' => ''
                                        //, 'class' => 'wpbc_hide_mobile'
                                        //, 'sortable' => true 
                                    );

        $columns = apply_filters ('wpbc_seasonfilters_table_header__user_title' , $columns );                
        if ( isset($columns[ 'users' ] ) ) {
            $columns[ 'users' ][ 'sortable' ] = false;
        }


        $columns = apply_filters ('wpbc_seasonfilters_table_header__last' , $columns );        

        $wpbc_sf_table = new WPBC_SF_Table_all_seasons( 
                            'seasonfilters' 
                            , array(
                                  'url_sufix'   =>  '#wpbc_' . $this->html_prefix . 'sf_table'  // Link to  scroll
                                , 'rows_func'   =>  array( $this, 'seasonfilters_table__show_rows' ) 
                                , 'columns'     =>  $columns
                                , 'is_show_pseudo_search_form' => false

                                , 'edit_booking_resource_id_arr'    => $edit_resource_id_array                                    
                            )
                        );

        $wpbc_sf_table->display();             

        ?>
        <a href="javascript:void(0);" class="button button-primary"
           onclick="javascript: if ( jQuery('#sfd_days_filter_name').val() == '') { wpbc_field_highlight( '#sfd_days_filter_name' );  return false; }
                                jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('update_sql_availability');
                                jQuery('#edit_resource_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $edit_resource_id; ?>');
                                jQuery(this).closest('form').submit();" 
            ><?php _e('Save Changes', 'booking') ?></a>
        <?php            

    }


    /**
	 * Show rows for booking resource table
     * 
     * @param int $row_num
     * @param array $resource
     */
    public function seasonfilters_table__show_rows( $row_num, $item ) {

        $css_class = ' wpbc_seasonfilters_row';

        //if ( $this->edit_resource_id == $item['id'] ) {
        //    $css_class .= ' row_selected_color';
        //}




        ?><tr class="wpbc_row<?php echo $css_class; ?>  wpbc_<?php

                    if ( ! empty( $item['hidded'] ) ) {
                        echo ' hidden_items wpbc_seasonfilters_row_to_hide';
                    }

                    ?>" id="resource_<?php echo $item['id']; ?>"><?php

                    ?><th class="check-column">
                            <label class="screen-reader-text" for="<?php echo $this->html_prefix; ?>select_<?php echo $item['id' ]; ?>"><?php echo esc_js(__('Select Booking Resource', 'booking')); ?></label>
                            <input type="checkbox" 
                                           id="<?php echo $this->html_prefix; ?>select_<?php echo $item['id' ]; ?>" 
                                           name="<?php echo $this->html_prefix; ?>select_<?php echo $item['id' ]; ?>" 
                                           value="<?php echo $this->html_prefix; ?>id_<?php echo $item['id' ]; ?>" 
                                           <?php    
                                            $is_checked = false;
                                            if (   ( isset( $this->edit_availability['filter'] ) ) 
                                                && ( isset( $this->edit_availability['filter'][ $item['id' ] ] ) )  
                                                && ( $this->edit_availability['filter'][ $item['id' ] ] == 'On' )  
                                                ) {
                                                $is_checked = true;
                                            }
                                            checked( $is_checked ); 
                                            ?>
                                />       
                    </th>
                    <td class="wpbc_hide_mobile"><?php echo $item['id' ]; ?></td>
                    <?php do_action( 'wpbc_seasonfilters_table_show_col__before_title', $row_num, $item ); ?>
                    <td style="text-align:left;">&nbsp;&nbsp;&nbsp;<?php 
                        if (  $is_checked ) 
                            echo '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                        else
                            echo '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>'; 
                    ?>
                    </td>
                    <td>
                        <a href="<?php echo wpbc_get_resources_url() . '&tab=filter&edit_season_id=' . $item['id' ] . '&wh_search_id=' . $item['id' ]; ?>"
                           style="font-weight:600;"
                           ><?php echo esc_attr( $item['title'] ); ?></a>
                    </td>
                    <td><?php 

                        if (  (  $is_checked ) && ( isset( $this->edit_availability['general'] ) )  ){

                            echo '<span class="label label-default label-danger ' 
                                    . ( ( $this->edit_availability[ 'general' ] == 'On' ) ? '' : 'hidden_items' ) 
                                    . ' wpbc_unavailable_item  wpbc_availability_item" >'  . __('unavailable', 'booking' ) . '</span>';

                            echo '<span class="label label-default label-success ' 
                                    . ( ( $this->edit_availability[ 'general' ] == 'Off' ) ? '' : 'hidden_items' ) 
                                    . ' wpbc_available_item wpbc_availability_item" >' . __('available', 'booking' )   . '</span>';

                        }
                            echo '&nbsp;&nbsp;&nbsp;' . wpbc_get_filter_description( $item['filter' ] ); 
                    ?></td>
                    <?php do_action( 'wpbc_seasonfilters_table_show_col__user_text',  $row_num, $item ); ?>
                    <?php do_action( 'wpbc_seasonfilters_table_show_col__last',         $row_num, $item ); ?>
        </tr>
        <?php    
    }

    //                                                                              </editor-fold>
    
}
add_action('wpbc_menu_created', array( new WPBC_Page_Settings__ravailability() , '__construct') );    // Executed after creation of Menu
