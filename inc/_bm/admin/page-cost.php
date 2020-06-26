<?php /**
 * @version 1.0
 * @package Booking > Resources > Cost and rates page 
 * @category Settings page 
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2016-09-09
 * 
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

require_once( WPBC_PLUGIN_DIR. '/inc/_bm/admin/page-cost-rate.php' );
require_once( WPBC_PLUGIN_DIR. '/inc/_bm/admin/page-cost-valuation.php' );
require_once( WPBC_PLUGIN_DIR. '/inc/_bm/admin/page-cost-deposit.php' );
require_once( WPBC_PLUGIN_DIR. '/inc/_bm/admin/page-cost-early-late-booking.php' );        								//FixIn: 8.2.1.17

/**
	 * Show Content
 *  Update Content
 *  Define Slug
 *  Define where to show
 */
class WPBC_Page_Settings__rcosts extends WPBC_Page_Structure {

    const SUBMIT_FORM = 'wpbc_rcosts';                                   // Main Form Name
    const ACTION_FORM = 'wpbc_rcosts_action';                            // Form for sub-actions: like Add New | Edit actions
    
    
    private $edit_resource_id = 0;

    
    public function in_page() {
        return 'wpbc-resources';
    }


    public function tabs() {

        $tabs = array();

        $tabs[ 'cost' ] = array(
                              'title'       => __('Costs and Rates','booking')            // Title of TAB    
                            , 'hint'        => __('Customization of rates, valuation days cost and deposit amount ', 'booking')                      // Hint    
                            , 'page_title'  => __('Costs and Rates Settings', 'booking')                                // Title of Page    
                            //, 'link'      => ''                               // Can be skiped,  then generated link based on Page and Tab tags. Or can  be extenral link
                            //, 'position'  => 'left'                           // 'left'  ||  'right'  ||  ''
                            //, 'css_classes'=> ''                              // CSS class(es)
                            //, 'icon'      => ''                               // Icon - link to the real PNG img
                            , 'font_icon' => 'glyphicon glyphicon-signal'        // CSS definition  of forn Icon
                            , 'default'   => false                              // Is this tab activated by default or not: true || false. 
                            //, 'disabled'  => false                            // Is this tab disbaled: true || false. 
                            , 'hided'     => false                              // Is this tab hided: true || false. 
                            , 'subtabs'   => array()   
                    );

        return $tabs;
    }


    /** Show Content of Settings page */
    public function content() {

        $this->css();

        ////////////////////////////////////////////////////////////////////////
        // Checking 
        ////////////////////////////////////////////////////////////////////////

        do_action( 'wpbc_hook_settings_page_header', 'costs_and_rates');        // Define Notices Section and show some static messages, if needed

        if ( ! wpbc_is_mu_user_can_be_here('activated_user') ) return false;    // Check if MU user activated, otherwise show Warning message.

        // if ( ! wpbc_is_mu_user_can_be_here('only_super_admin') ) return false;  // User is not Super admin, so exit.  Basically its was already checked at the bottom of the PHP file, just in case.

        // Load Data 

        // $this->get_api()->validated_form_id = self::SUBMIT_FORM;             // Define ID of Form for ability to  validate fields (like required field) before submit.

        ////////////////////////////////////////////////////////////////////////
        //  S u b m i t   Main Form  
        ////////////////////////////////////////////////////////////////////////                
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
            // Actions Form
            ////////////////////////////////////////////////////////////////////     

            ?><form  name="<?php echo self::ACTION_FORM; ?>" id="<?php echo self::ACTION_FORM; ?>" action="<?php 

                    // Need to  exclude 'edit_resource_id' parameter from  $_GET,  if we was using direct link for editing,  in case for edit other season filters....
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

					// Add hidden input SEARCH KEY field into  main form, if previosly was searching by ID or Title
					wpbc_hidden_search_by_id_field_in_main_form( array( 'search_get_key' => 'wh_resource_id' ) );			//FixIn: 8.0.1.12

                ?><input type="hidden" name="is_form_sbmitted_<?php echo self::SUBMIT_FORM; ?>" id="is_form_sbmitted_<?php echo self::SUBMIT_FORM; ?>" value="1" /><?php                 
                ?>
                <div class="clear" style="margin-top:20px;"></div>
                <div id="wpbc_booking_resource_table" class="wpbc_settings_row wpbc_settings_row_rightNO"><?php 

                // wpbc_open_meta_box_section( 'wpbc_settings_rcosts_resources', __('Resources', 'booking') );

                $this->wpbc_resources_table__show();

                // wpbc_close_meta_box_section();
                ?>
                </div>
                <div class="clear"></div>                
                <select id="bulk-action-selector-bottom" name="bulk-action">
                    <option value="-1"><?php _e('Bulk Actions', 'booking'); ?></option>
                    <option value="edit"><?php _e('Edit', 'booking'); ?></option>
                    <option value="set_rate"><?php _e('Set Rate', 'booking'); ?></option>
                    <option value="set_valuation"><?php _e('Set Valuation Days', 'booking'); ?></option>
                    <option value="set_deposit"><?php _e('Set Deposit Amount', 'booking'); ?></option>
                    <option value="set_early_late_booking"><?php _e('Early / Late Booking', 'booking'); ?></option>		<?php //FixIn: 8.2.1.17 ?>
                    <option value="delete"><?php _e('Delete', 'booking'); ?></option>
                </select>    

                <a href="javascript:void(0);" onclick="javascript: jQuery('#<?php echo self::SUBMIT_FORM; ?>').submit();"
                  class="button button-primary wpbc_button_save wpbc_button_action" ><?php _e('Save Changes','booking'); ?></a>
                <a href="javascript:void(0);" class="button wpbc_button_set_cost wpbc_button_set_rate wpbc_button_action" style="display:none;" ><?php _e('Set Rate','booking'); ?></a>
                <a href="javascript:void(0);" class="button wpbc_button_set_cost wpbc_button_set_valuation wpbc_button_action" style="display:none;" ><?php _e('Set Valuation Days','booking'); ?></a>
                <a href="javascript:void(0);" class="button wpbc_button_set_cost wpbc_button_set_deposit wpbc_button_action" style="display:none;" ><?php _e('Set Deposit Amount','booking'); ?></a>
                <a href="javascript:void(0);" class="button wpbc_button_set_cost wpbc_button_set_early_late_booking wpbc_button_action" style="display:none;" ><?php _e('Set Early / Late Booking Amount','booking'); ?></a>	<?php //FixIn: 8.2.1.17 ?>
                <a href="javascript:void(0);"
                  class="button wpbc_button_delete wpbc_button_action" id="wpbc_button_delete" style="display:none;background: #d9534f;border:#b92c28 1px solid;color:#eee;" ><?php _e('Delete','booking'); ?></a>
                <span class="wpbc_button_delete wpbc_button_action" style="display:none;">
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
        </span>
        <?php       

        do_action( 'wpbc_hook_settings_page_footer', 'costs_and_rates' );

        $this->enqueue_js();
    }


    /**  Save Chanages - Main Form  */  
    public function update() {

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

                        //if ( isset( $_POST['br-select-' . $resource_id ] ) ) {              // !!!  > Check  if the checkbox selected - in other cases we need to  check  some other field here to  save data
            if ( isset( $_POST['booking_resource_cost_' . $resource_id ] ) ) {    // Cehck  cost field (submit), if its exist,  then check  for validation.

                switch ( $submit_action ) {
                    case 'delete':                                          // Delete

                        if ( isset( $_POST['br-select-' . $resource_id ] ) )
                                $bulk_action_arr_id[] = intval( $resource_id );
                    break;

                    default:                                                // Edit Cost

                        //$validated_value = WPBC_Settings_API::validate_text_post_static( 'booking_resource_' . $resource_id );            // Validate POST value

                        // Need this complex query  for ability to  define different paramaters in differnt versions.
                        $sql_arr = apply_filters(   'wpbc_resources_table__update_sql_cost_array'
                                                            , array(
                                                                    'sql'       => array(
                                                                                          'start'   => "UPDATE {$wpdb->prefix}bookingtypes SET "
                                                                                        , 'params' => array() //array( 'title = %s' )                         
                                                                                        , 'end'    => " WHERE booking_type_id = %d"
                                                                                )
                                                                    , 'values'  => array() // array( $validated_value )
                                                                )
                                                            , $resource_id, $resource 
                                            );                
                        $sql_arr['values'][] = intval( $resource_id );              // last parameter  for " WHERE booking_type_id = %d "
                        $sql = $wpdb->prepare(    $sql_arr['sql']['start']                          // SQL
                                                    . implode( ', ' , $sql_arr['sql']['params'] ) 
                                                    . $sql_arr['sql']['end']            
                                                , $sql_arr[ 'values' ]                              // Array of validated parameters
                                            ); 
                        if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error saving to DB' ,__FILE__ , __LINE__); }         // Save to DB

                        wpbc_show_changes_saved_message();   

                    break;
                }
            }
        }


        if ( ! empty( $bulk_action_arr_id ) ) {

            switch ( $submit_action ) {

                case 'delete':                                          // Delete
                    $bulk_action_arr_id = implode( ',', $bulk_action_arr_id );
                    $sql = "DELETE FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id IN ({$bulk_action_arr_id})";

                    if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error during deleting items in DB' ,__FILE__ , __LINE__); }         // Action inDB

                    wpbc_show_message ( __( 'Deleted', 'booking'), 5 );

                default:                                                        // Edit
                    break;
            }
        }

        make_bk_action( 'wpbc_reinit_booking_resource_cache' );                 // Refresh  resource cache    
    }


    // <editor-fold     defaultstate="collapsed"                        desc=" CSS  &   JS   "  >
    
    /** CSS for this page */
    private function css() {
        ?>
        <style type="text/css"> 
            .wpbc_2_fields_in_collumn input[type="text"],
            .wpbc_2_fields_in_collumn select {
                float: left;
                margin: 0 1% 0 0;
                width: 49%;                
            }
            .wpbc_2_fields_in_collumn select {
                margin: 0 0 0 1%;
            }
            .button.wpbc_in_table_button {
                margin: 3px 5px;
            }
            .wpbc_available_item_text {
                color: #5cb85c;
            }
            .wpbc_unavailable_item_text {
                color: #d9534f;
            }
            .label.wpbc_general_rcosts_item, 
            .label.wpbc_rcosts_item {
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
            /* Valuation days */
            .wpbc_sortable_table .widefat.wpbc_vd_table td.sort{
                vertical-align: baseline;
            }            
            .wpbc_sortable_table .widefat.wpbc_vd_table td.sort::before{
                vertical-align: middle;
            }
            .wpbc_page .wpdevelop .wpbc_vd_table .wpbc_vd_type_select {
                margin:1px 10px 4px 1px;
            }
            .wpbc_page .wpdevelop .wpbc_vd_table .wpbc_vd_days_fieldset{
                min-width:15em;
                margin:1px;
            }
            .wpbc_page .wpdevelop label.wpbc_vd_days_label, 
            .wpbc_page .wpdevelop label.wpbc_vd_to_label {
                float: left;
                font-size: 1em;
                line-height: 1.8em;
                font-weight: 600;
                margin: 2px 1px 1px;
                padding-left: 5px;
                padding-right: 5px;
            }
            
            @media (max-width: 782px) {                 
                .wpbc_resources_table td.field-currency-cost {                    /* the same as .wpbc_resources_table th.wpbc_hide_mobile */
                    display: none;
                }
                /* Valuation days */
                .wpdevelop.wpbc_resources_table .wpbc_vd_table > thead > tr > th, 
                .wpdevelop.wpbc_resources_table .wpbc_vd_table > tbody > tr > th, 
                .wpdevelop.wpbc_resources_table .wpbc_vd_table > tfoot > tr > th, 
                .wpdevelop.wpbc_resources_table .wpbc_vd_table > thead > tr > td, 
                .wpdevelop.wpbc_resources_table .wpbc_vd_table > tbody > tr > td, 
                .wpdevelop.wpbc_resources_table .wpbc_vd_table > tfoot > tr > td {
                    vertical-align: top;
                }
                .wpbc_page .wpdevelop .wpbc_vd_table select, 
                .wpbc_page .wpdevelop .wpbc_vd_table input{
                    width:100% !important;
                }
                .wpbc_page .wpdevelop .wpbc_vd_table .wpbc_vd_days_fieldset{
                    min-width:auto;
                .wpbc_page .wpdevelop .wpbc_vd_table label.wpbc_vd_days_label, 
                .wpbc_page .wpdevelop .wpbc_vd_table label.wpbc_vd_to_label {
                    font-size: 1.1em;
                    line-height: 2em;
                }
            }
            @media (max-width: 399px) {
                .field-currency-cost {
                    width:20%;
                }
                .field-currency-cost .field-currency {
                    display:none;
                }
                .wpbc_2_fields_in_collumn input[type="text"],
                .wpbc_2_fields_in_collumn select {
                    float: none;
                    margin: 0 0 5px 0;
                    width: 100%;                
                }
                .wpbc_2_fields_in_collumn select {
                    margin: 0;
                }
                
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
        ob_start();

        ?><script type="text/javascript"><?php                                  // Its trick only for highlighting JavaScript code here. This line is not diplaying.

        ob_clean();

        ?>
            jQuery('#bulk-action-selector-bottom').on( 'change', function(){    
                jQuery('.wpbc_button_action').hide();
                if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'edit' )   { jQuery('.wpbc_button_save').show(); }
                if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'set_rate' )       { jQuery('.wpbc_button_set_rate').show(); } 
                if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'set_valuation' )  { jQuery('.wpbc_button_set_valuation').show(); } 
                if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'set_deposit' )    { jQuery('.wpbc_button_set_deposit').show(); } 
                if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'set_early_late_booking' ) { jQuery('.wpbc_button_set_early_late_booking').show(); } <?php //FixIn: 8.2.1.17 ?>
                if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'delete' ) { jQuery('.wpbc_button_delete').show(); }
            } ); 
            jQuery('#wpbc_button_delete').on( 'click', function(){    
                if ( wpbc_are_you_sure('<?php echo esc_js( __('Do you really want to do this ?' ,'booking') ); ?>') ) { 
                    jQuery('#<?php echo self::SUBMIT_FORM; ?>').submit(); 
                }
            } ); 
            jQuery('.wpbc_button_set_cost').on( 'click', function(){    
                var cheked_elements = [];
                var selected_element = '';
                    jQuery( '#<?php echo self::SUBMIT_FORM; ?> .wpbc_selectable_body .check-column input[type=\'checkbox\']:checked' ).each(function() {
                        selected_element = jQuery( this ).val();                           
                        selected_element = selected_element.substr( 9 );
                        cheked_elements.push( selected_element );
                    });
                cheked_elements = cheked_elements.join( ',' );              // resources separator
                jQuery( '#edit_resource_id_<?php echo self::ACTION_FORM; ?>' ).val( cheked_elements );
                jQuery( '#action_<?php echo self::ACTION_FORM; ?>' ).val( jQuery('#bulk-action-selector-bottom option:selected').val() );
                jQuery('#<?php echo self::ACTION_FORM; ?>').submit();
            } ); 
        <?php 

        $js_script = ob_get_contents(); 

        ?></script><?php                                                        // Its trick only for highlighting JavaScript code here. This line is not diplaying.

        ob_end_clean();

        // Its will not work,  if we are using somehting like jQuery(document).ready(function(){ ... - Need direct showing in PHP code.        
        wpbc_enqueue_js( $js_script );                                          // Eneque JS to  the footer of the page 
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
        
        $columns = apply_filters ('wpbc_resources_table_header__cost_title' , $columns );

        if ( isset( $columns[ 'cost' ] ) ) $columns[ 'cost' ]['class'] = 'wpbc_hide_mobile';

        $columns[ 'title' ] = array(      'title' => __( 'Resource Name', 'booking' )
                                        , 'style'   => 'width:15em;'
                                        , 'sortable' => true 
                                    );

        $columns[ 'rcosts' ] = array(   'title' => __( 'Costs and Rates', 'booking' )
                                            , 'style'   => 'text-align:left;'
                                            , 'class'   => ''                   // 'wpbc_hide_mobile'        
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
            <?php do_action( 'wpbc_resources_table_show_col__cost_field', $row_num, $resource ); ?>
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
                    onclick="javascript:jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('set_rate');
                                        jQuery('#edit_resource_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                        jQuery('#<?php echo self::ACTION_FORM; ?>').submit();"                            
                    title="<?php _e('Rates' ,'booking'); ?>"
                ><span class="in-button-text"><?php _e('Rates', 'booking'); ?>&nbsp;&nbsp;</span><i class="menu_icon icon-1x glyphicon glyphicon-signal"></i></a>
                <a  class="tooltip_top button-secondary button wpbc_in_table_button" 
                    href="javascript:void(0);" 
                    onclick="javascript:jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('set_valuation');
                                        jQuery('#edit_resource_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                        jQuery('#<?php echo self::ACTION_FORM; ?>').submit();"                            
                    title="<?php _e('Valuation days' ,'booking'); ?>"
                ><span class="in-button-text"><?php _e('Valuation days', 'booking'); ?>&nbsp;&nbsp;</span><i class="menu_icon icon-1x glyphicon glyphicon glyphicon-equalizer"></i></a>
                <a  class="tooltip_top button-secondary button wpbc_in_table_button" 
                    href="javascript:void(0);" 
                    onclick="javascript:jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('set_deposit');
                                        jQuery('#edit_resource_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                        jQuery('#<?php echo self::ACTION_FORM; ?>').submit();"                            
                    title="<?php _e('Deposit' ,'booking'); ?>"
                ><span class="in-button-text"><?php _e('Deposit', 'booking'); ?>&nbsp;&nbsp;</span><i class="menu_icon icon-1x glyphicon glyphicon-compressed"></i></a>
                <a  class="tooltip_top button-secondary button wpbc_in_table_button" <?php //FixIn: 8.2.1.17 ?>
                    href="javascript:void(0);"
                    onclick="javascript:jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('set_early_late_booking');
                                        jQuery('#edit_resource_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                        jQuery('#<?php echo self::ACTION_FORM; ?>').submit();"
                    title="<?php _e('Set Early / Late booking discount' ,'booking'); ?>"
                ><span class="in-button-text"><?php _e('Early / Late', 'booking'); ?>&nbsp;&nbsp;</span><i class="menu_icon icon-1x glyphicon glyphicon-transfer"></i></a>
            </td>
            <?php do_action( 'wpbc_resources_table_show_col__info_text', $row_num, $resource ); ?>                    
            <?php do_action( 'wpbc_resources_table_show_col__user_text', $row_num, $resource ); ?>
                    
        </tr>
        <?php    
    }

    //                                                                              </editor-fold>
    
          
    //  A c t i o n  s    F o r m //////////////////////////////////////////////
    
    /**
	 * Show and Save Costs for: Rates, "Valuation days", Deposit
     * 
     * @return boolean
     */
    public function wpbc_check_sub_actions(){
        
        $edit_resource_id = '';
        
        if ( isset( $_GET['edit_resource_id'] ) ) {                             // In case if we need to  open  direct  link for editing some filter
            
            $action     = 'set_rate';                                           // Default action  - settings rate - if we transfer from URL
            $edit_resource_id  = wpbc_clean_digit_or_csd( $_GET['edit_resource_id'] );
            
        } else {
        
                if ( isset( $_POST['is_form_sbmitted_'. self::ACTION_FORM ] ) ) {

                    // Nonce checking    {Return false if invalid, 1 if generated between, 0-12 hours ago, 2 if generated between 12-24 hours ago. }
                    $nonce_gen_time = check_admin_referer( 'wpbc_settings_page_' . self::ACTION_FORM );  // Its stop show anything on submiting, if its not refear to the original page

                    // If we have wrong nonce,  so its will be stop executing here            
                } else 
                    return false;                                                       // If we do  not submit sub action,  then exit    

                ////////////////////////////////////////////////////////////////////////

                if ( isset(   $_POST[ 'action_' .  self::ACTION_FORM ] ) ){ 
                    $action = $_POST[ 'action_' .  self::ACTION_FORM ];
                }
                
                if ( empty( $action ) ) return  false;

                if ( isset( $_POST[ 'edit_resource_id_' .  self::ACTION_FORM ] ) ) {                    
                    $edit_resource_id = wpbc_clean_digit_or_csd( $_POST[ 'edit_resource_id_' .  self::ACTION_FORM ] );
                }
        }
        ////////////////////////////////////////////////////////////////////////
        
        $rate_obj = false;
        $valuation_obj = false;
        $deposit_obj = false;
        $early_late_booking_obj = false;		//FixIn: 8.2.1.17

        if ( ! empty( $edit_resource_id ) ) {
            
            switch ( $action ) {
                
                // Rates ///////////////////////////////////////////////////////
                
                case 'update_sql_rate':
                    $rate_obj = new WPBC_Section_Rate(  $edit_resource_id
                                                        , array(  
                                                                'action_form'   => self::ACTION_FORM    
                                                            )                            
                                                    );                    
                    $rate_obj->update_sql();
                    
                case 'set_rate':
                    
                    if ($rate_obj === false)
                        $rate_obj = new WPBC_Section_Rate(  $edit_resource_id
                                                            , array(  
                                                                    'action_form'   => self::ACTION_FORM    
                                                                )                            
                                                        );                    
                    $rate_obj->display();
                    
                    break;
                
                // "Valuation days" ////////////////////////////////////////////
                
                case 'update_sql_valuation':
                    $valuation_obj = new WPBC_Section_Valuation(  $edit_resource_id
                                                        , array(  
                                                                'action_form'   => self::ACTION_FORM    
                                                            )                            
                                                    );                    
                    $valuation_obj->update_sql();
                    
                case 'set_valuation':
                    
                    if ($valuation_obj === false)
                        $valuation_obj = new WPBC_Section_Valuation(  $edit_resource_id
                                                            , array(  
                                                                    'action_form'   => self::ACTION_FORM    
                                                                )                            
                                                        );                    
                    $valuation_obj->display();
                    
                    break;

                // Deposit /////////////////////////////////////////////////////
                    
                case 'update_sql_deposit':
                    $deposit_obj = new WPBC_Section_Deposit(  $edit_resource_id
                                                            , array(  
                                                                'action_form'   => self::ACTION_FORM    
                                                            )                            
                                                    );                    
                    $deposit_obj->update_sql();
                    
                case 'set_deposit':
                    
                    if ($deposit_obj === false)
                        $deposit_obj = new WPBC_Section_Deposit(  $edit_resource_id
                                                                , array(  
                                                                    'action_form'   => self::ACTION_FORM    
                                                                )                            
                                                        );                    
                    $deposit_obj->display();
                    
                    break;


                // Early / Late Booking /////////////////////////////////////////////////////
                //FixIn: 8.2.1.17

                case 'update_sql_early_late_booking':
                    $early_late_booking_obj = new WPBC_Section_Early_Late_Booking(  $edit_resource_id
                                                            , array(
                                                                'action_form'   => self::ACTION_FORM
                                                            )
                                                    );
                    $early_late_booking_obj->update_sql();

                case 'set_early_late_booking':

                    if ($early_late_booking_obj === false)
                        $early_late_booking_obj = new WPBC_Section_Early_Late_Booking(  $edit_resource_id
                                                                , array(
                                                                    'action_form'   => self::ACTION_FORM
                                                                )
                                                        );
                    $early_late_booking_obj->display();

                    break;

                default:
                    break;
            }
        }    

        return $edit_resource_id;
    }
   
}
add_action('wpbc_menu_created', array( new WPBC_Page_Settings__rcosts() , '__construct') );    // Executed after creation of Menu