<?php /**
 * @version 1.0
 * @package Booking > Resources page
 * @category Settings page 
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2016-08-13
 * 
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly



/** Show Content
 *  Update Content
 *  Define Slug
 *  Define where to show
 */
class WPBC_Page_Settings__seasonfilters extends WPBC_Page_Structure {

    const SUBMIT_FORM = 'wpbc_seasonfilters';                                   // Main Form Name
    const ACTION_FORM = 'wpbc_action_seasonfilters';                            // Form for sub-actions: like Add New | Edit actions

    
    private $html_prefix = 'sf_';

    private $edit_filter_id = 0;
    
    public function in_page() {
        return 'wpbc-resources';
    }
    

    public function tabs() {
        
        $tabs = array();
        
        $tabs[ 'filter' ] = array(
                              'title'       => __('Season Filters','booking')            // Title of TAB    
                            , 'hint'        => __('Customizaton of Season Filters', 'booking')                      // Hint    
                            , 'page_title'  => __('Season Filters' ,'booking') . ' ' . __('Settings' ,'booking')    // Title of Page   
                            //, 'link'      => ''                               // Can be skiped,  then generated link based on Page and Tab tags. Or can  be extenral link
                            //, 'position'  => 'left'                           // 'left'  ||  'right'  ||  ''
                            //, 'css_classes'=> ''                              // CSS class(es)
                            //, 'icon'      => ''                               // Icon - link to the real PNG img
                            , 'font_icon' => 'glyphicon glyphicon-link'       // CSS definition  of forn Icon
                            , 'default'   => false                              // Is this tab activated by default or not: true || false. 
                            //, 'disabled'  => false                            // Is this tab disbaled: true || false. 
                            //, 'hided'     => false                            // Is this tab hided: true || false. 
                            , 'subtabs'   => array()   
                    );
        /**
        
        $subtabs = array();        
        $subtabs[ 'filter' ] = array( 
                            'type' => 'subtab'                                  // Required| Possible values:  'subtab' | 'separator' | 'button' | 'goto-link' | 'html'
                            , 'title' => __('Season Filters' ,'booking')        // Title of TAB    
                            , 'page_title' => __('Season Filters' ,'booking') . ' ' . __('Settings' ,'booking')    // Title of Page   
                            , 'hint' => __('Season Filters' ,'booking')      // Hint    
                            , 'link' => ''                                      // link
                            , 'position' => ''                                  // 'left'  ||  'right'  ||  ''
                            , 'css_classes' => ''                               // CSS class(es)
                            //, 'icon' => 'http://.../icon.png'                 // Icon - link to the real PNG img
                            //, 'font_icon' => 'glyphicon glyphicon-envelope'   // CSS definition of Font Icon
                            , 'default' =>  false                               // Is this sub tab activated by default or not: true || false. 
                            , 'disabled' => false                               // Is this sub tab deactivated: true || false. 
                            , 'checkbox'  => false                              // or definition array  for specific checkbox: array( 'checked' => true, 'name' => 'feature1_active_status' )   //, 'checkbox'  => array( 'checked' => $is_checked, 'name' => 'enabled_active_status' )
                            , 'content' => 'content'                            // Function to load as conten of this TAB
                        );        
        $tabs[ 'resources' ]['subtabs'] = $subtabs;
        */
        
        return $tabs;
    }


    /** Show Content of Settings page */
    public function content() {

        $this->css();
        
        ////////////////////////////////////////////////////////////////////////
        // Checking 
        ////////////////////////////////////////////////////////////////////////

        do_action( 'wpbc_hook_settings_page_header', 'seasonfilters');              // Define Notices Section and show some static messages, if needed
        
        if ( ! wpbc_is_mu_user_can_be_here('activated_user') ) return false;            // Check if MU user activated, otherwise show Warning message.
   
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

            // Toolbar
            wpbc_add_new_seasonfilters_toolbar();


        echo '</span>';

        ?><div class="clear" style="margin-bottom:20px;"></div><?php
        
        // Scroll links ////////////////////////////////////////////////////////
        
        wpbc_toolbar_search_by_id__top_form( array( 
                                                    'search_form_id' => 'wpbc_seasonfilters_search_form'
                                                  , 'search_get_key' => 'wh_search_id'
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
            
                    // Need to  exclude 'edit_season_id' parameter from  $_GET,  if we was using direct link for ediing,  in case for edit other season filters....
                    $exclude_params = array( 'edit_season_id' );
                    $only_these_parameters = array( 'tab', 'page_num', 'wh_search_id' );
                    echo wpbc_get_params_in_url( wpbc_get_resources_url( false, false ), $exclude_params, $only_these_parameters );

                    ?>" method="post" autocomplete="off"><?php                           
               // N o n c e   field, and key for checking   S u b m i t 
               wp_nonce_field( 'wpbc_settings_page_' . self::ACTION_FORM );

		        // Add hidden input SEARCH KEY field into  main form, if previosly was searching by ID or Title
		        wpbc_hidden_search_by_id_field_in_main_form( array( 'search_get_key' => 'wh_search_id' ) );			//FixIn: 8.0.1.12

            ?><input type="hidden" name="is_form_sbmitted_<?php echo self::ACTION_FORM; ?>" id="is_form_sbmitted_<?php echo self::ACTION_FORM; ?>" value="1" /><?php   
            
                ?><input type="hidden" name="action_<?php echo self::ACTION_FORM; ?>"    id="action_<?php echo self::ACTION_FORM; ?>"    value="-1" /><?php                 
                ?><input type="hidden" name="edit_season_id_<?php echo self::ACTION_FORM; ?>" id="edit_season_id_<?php echo self::ACTION_FORM; ?>" value="-1" /><?php                 

                $this->wpbc_check_sub_actions();                                // Check  "Adding New" season filter | Edit | Delete single exist  season filter.
                
            ?></form><?php
        
            
            ////////////////////////////////////////////////////////////////////
            // Main Form
            ////////////////////////////////////////////////////////////////////
            
            ?><form  name="<?php echo self::SUBMIT_FORM; ?>" id="<?php echo self::SUBMIT_FORM; ?>" action="" method="post" autocomplete="off">
                <?php 
                   // N o n c e   field, and key for checking   S u b m i t 
                   wp_nonce_field( 'wpbc_settings_page_' . self::SUBMIT_FORM );

					// Add hidden input SEARCH KEY field into  main form, if previosly was searching by ID or Title
					wpbc_hidden_search_by_id_field_in_main_form( array( 'search_get_key' => 'wh_search_id' ) );			//FixIn: 8.0.1.12

                ?><input type="hidden" name="is_form_sbmitted_<?php echo self::SUBMIT_FORM; ?>" id="is_form_sbmitted_<?php echo self::SUBMIT_FORM; ?>" value="1" /><?php                 
                ?><div class="clear" style="margin-top:20px;"></div>
                <div id="wpbc_<?php echo $this->html_prefix; ?>table" class="wpbc_settings_row wpbc_settings_row_rightNO"><?php 
                
                    // wpbc_open_meta_box_section( 'wpbc_settings_seasonfilters_seasonfilters', __('Resources', 'booking') );
                        
                    $this->wpbc_seasonfilters_table__show();
                        
                    // wpbc_close_meta_box_section();
                ?>
                </div>
                <div class="clear"></div>                
                <select id="bulk-action-selector-bottom" name="bulk-action">
                    <option value="-1"><?php _e('Bulk Actions', 'booking'); ?></option>
                    <option value="edit"><?php _e('Edit', 'booking'); ?></option>
                    <option value="delete"><?php _e('Delete', 'booking'); ?></option>
                </select>    
                
                <a href="javascript:void(0);" onclick="javascript: jQuery('#wpbc_seasonfilters').submit();"
                  class="button button-primary wpbc_button_save" ><?php _e('Save Changes','booking'); ?></a>
                <a href="javascript:void(0);" id="wpbc_button_delete"
                  class="button wpbc_button_delete" style="display:none;background: #d9534f;border:#b92c28 1px solid;color:#eee;" ><?php _e('Delete','booking'); ?></a>
                    
            </form>
            <script type="text/javascript">
                jQuery('#bulk-action-selector-bottom').on( 'change', function(){    
                    if ( jQuery('#bulk-action-selector-bottom option:selected').val() == 'delete' ) { 
                        jQuery('.wpbc_button_delete').show();
                        jQuery('.wpbc_button_save').hide();
                    } else {
                        jQuery('.wpbc_button_delete').hide();
                        jQuery('.wpbc_button_save').show();
                    }
                } ); 
                jQuery('#wpbc_button_delete').on( 'click', function(){    
                    if ( wpbc_are_you_sure('<?php echo esc_js( __('Do you really want to do this ?' ,'booking') ); ?>') ) { 
                        jQuery('#wpbc_seasonfilters').submit(); 
                    }
                } ); 
            </script>
        </span>
        <?php       
    
        do_action( 'wpbc_hook_settings_page_footer', 'seasonfilters' );
        
        $this->enqueue_js();
    }


    /** Save Chanages */  
    public function update() {

        // if (  ( wpbc_is_this_demo() ) ||  ( ! class_exists( 'wpdev_bk_personal' ) )  ) return;

        global $wpdb;

        $wpbc_sf_table = new WPBC_SF_Table( 'seasonfilters_submit' );
        $linear_seasonfilters_for_one_page = $wpbc_sf_table->get_linear_data_for_one_page();
                
        if ( isset( $_POST['bulk-action' ] ) ) 
            $submit_action = $_POST['bulk-action' ];
        else 
            $submit_action = 'edit';
        
        $bulk_action_arr_id = array();

        foreach ( $linear_seasonfilters_for_one_page as $resource_id => $resource ) {

            // Check posts of only visible on page booking seasonfilters 
            if ( isset( $_POST[ $this->html_prefix . 'title_' . $resource_id ] ) ) {

                    switch ( $submit_action ) {
                        case 'delete':                                          // Delete

                            if ( isset( $_POST[ $this->html_prefix . 'select_' . $resource_id ] ) )
                                    $bulk_action_arr_id[] = intval( $resource_id );
                            break;

                        default:                                                // Edit

                                // Validate POST value
                                $validated_value = WPBC_Settings_API::validate_text_post_static( $this->html_prefix . 'title_' . $resource_id );

                                //if ( $validated_value != $resource['title'] ) {               // Check  if its different from  original value in DB

                                    // Need this complex query  for ability to  define different paramaters in differnt versions.
                                    $sql_arr = apply_filters(   'wpbc_seasonfilters_table__update_sql_array'
                                                                        , array(
                                                                                'sql'       => array(
                                                                                                      'start'   => "UPDATE {$wpdb->prefix}booking_seasons SET "
                                                                                                    , 'params' => array( 'title = %s' )                         
                                                                                                    , 'end'    => " WHERE booking_filter_id = %d"
                                                                                            )
                                                                                , 'values'  => array( $validated_value )
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

                    
                //}
            }
        }


        if ( ! empty( $bulk_action_arr_id ) ) {
            
                    switch ( $submit_action ) {
                        
                        case 'delete':                                          // Delete
                            $bulk_action_arr_id = implode( ',', $bulk_action_arr_id );
                            $sql = "DELETE FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id IN ({$bulk_action_arr_id})";

                            if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error during deleting items in DB' ,__FILE__ , __LINE__); }         // Action inDB

                            wpbc_show_message ( __( 'Deleted', 'booking'), 5 );
                            
                        default:                                                // Edit
                            break;
                    }
        }
        
        make_bk_action( 'wpbc_reinit_seasonfilters_cache' );

        /**
        // Get Validated Email fields
        $validated_fields = $this->get_api()->validate_post();        
        $validated_fields = apply_filters( 'wpbc_fields_before_saving_to_db__seasonfilters', $validated_fields );   //Hook for validated fields.        
        $this->get_api()->save_to_db( $validated_fields );
        */
        
        // Old way of saving:
        // update_bk_option( 'booking_cache_expiration' , WPBC_Settings_API::validate_text_post_static( 'booking_cache_expiration' ) );
    }



    // <editor-fold     defaultstate="collapsed"                        desc=" CSS  &   JS   "  >
    
    /** CSS for this page */
    private function css() {
        ?>
        <style type="text/css">  
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
            /* Show Labels under checkbox */
/*            .wpbc_sf_section_selectable.sfd_days .wpbc_selectable_head .check-column .wpbc-form-checkbox input[type='checkbox'], */
            .wpbc_sf_section_selectable.sfd_days .wpbc_selectable_body .check-column .wpbc-form-checkbox input[type='checkbox'] { 
                display:block;
                clear:both;                
                margin: 10px 0 0 0;
                padding:0;                
            } 
            .wpbc_sf_section_selectable.sfd_days .wpbc_selectable_body .check-column .wpbc-form-checkbox { 
                line-height: 3em;
                vertical-align: middle;
            }
            .wpbc_sf_section_selectable {
                border: 1px solid #ccc; 
                padding: 10px;
                margin: 20px 0 0;
            }
            .wpbc_sf_section_selectable .wpbc_selectable_head .check-column {
                float: left; 
            }
            .wpbc_sf_section_selectable .wpbc_selectable_body {
                margin-left:100px;
            }
            .wpbc_sf_section_selectable .wpbc_selectable_body .wpbc_row{
                margin: 0;
                float:left;
            }
            .wpbc_sf_section_selectable .wpbc_selectable_body .wpbc_row .check-column {
                padding: 1px 5px 4px;
            }
            .wpbc_sf_section_selectable .wpbc_selectable_body .wpbc_checkbox_selected {
                background: #ddd;
                font-weight:600;
                color: #08d;
            }
            /* Range Days */
            .wpbc_season_filter__range_days .wpbc_sf_section_selectable {
                margin: 0;
                font-size: 0.9em;
                line-height: 1.5em;
                padding-bottom: 5px 20px;
                text-align: center;                
            }
            .wpbc_season_filter__range_days .wpbc_sf_section_selectable input[type='checkbox']{
                margin:-3px 0 0;
            }
            /* Weekend color */
            .wpbc_season_filter__range_days .wpbc_sf_section_selectable .weekday6, 
            .wpbc_season_filter__range_days .wpbc_sf_section_selectable .weekday7{
                color:#d71 !important;
            }
            @media (max-width: 782px) {
                .wpbc_sf_section_selectable .wpbc_selectable_body .check-column .wpbc-form-checkbox { 
                    line-height: 3em;
                    vertical-align: middle;
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
        
        
        // Find all Checkboxes and set  Lables bold
        $js_script .= " jQuery(document).ready(function(){
                            jQuery( '.check-column input[type=\'checkbox\']:checked' ).parent().parent().addClass('wpbc_checkbox_selected');
                        });
                      ";        
        // on checkbox click
        $js_script .= " jQuery('.check-column input[type=\'checkbox\']').on( 'change', function(){    
                                jQuery( '.check-column input[type=\'checkbox\']' ).parent().parent().removeClass('wpbc_checkbox_selected');
                                jQuery( '.check-column input[type=\'checkbox\']:checked' ).parent().parent().addClass('wpbc_checkbox_selected');
                            } ); ";   
        // on Reset button  click - deselect  all
        $js_script .= " jQuery('.wpbc-reset-button').on( 'click', function(){    
                                jQuery( '.check-column input[type=\'checkbox\']' ).parent().parent().removeClass('wpbc_checkbox_selected');
                            } ); ";   
        
        
        // on TAB click - deselect  all
        $js_script .= " jQuery('.wpbc_season_filter__range_days .nav-tab input[type=\'checkbox\']').on( 'change', function(){    
                                jQuery( '.check-column input[type=\'checkbox\']' ).parent().parent().removeClass('wpbc_checkbox_selected');
                                jQuery( '.check-column input[type=\'checkbox\']:checked' ).parent().parent().addClass('wpbc_checkbox_selected');                                
                            } ); ";   

        ////////////////////////////////////////////////////////////////////////
        
        
        // Eneque JS to  the footer of the page
        wpbc_enqueue_js( $js_script );    
        ?>
        <script type="text/javascript">
            function wpbc_check_selected_checkboxes_in_sections() { 

                if (  jQuery('.sfd_weekday .wpbc_selectable_body .check-column input[type=\'checkbox\']:checked').length == 0 ) {
                    wpbc_field_highlight( '.sfd_weekday' );
                    return  false;
                }
                if (  jQuery('.sfd_days .wpbc_selectable_body .check-column input[type=\'checkbox\']:checked').length == 0 ) {
                    wpbc_field_highlight( '.sfd_days' );
                    return  false;
                }
                if (  jQuery('.sfd_months .wpbc_selectable_body .check-column input[type=\'checkbox\']:checked').length == 0 ) {
                    wpbc_field_highlight( '.sfd_months' );
                    return  false;
                }
                if (  jQuery('.sfd_years .wpbc_selectable_body .check-column input[type=\'checkbox\']:checked').length == 0 ) {
                    wpbc_field_highlight( '.sfd_years' );
                    return  false;
                }
                return true;
            }            
        </script>
        <?php
    }

    // </editor-fold>
    

    //                                                                          <editor-fold   defaultstate="collapsed"   desc=" S e a s o n      F i l t e r s      T a b l e  " >    
    
    ////////////////////////////////////////////////////////////////////////////
    //   S e a s o n      F i l t e r s      T a b l e 
    ////////////////////////////////////////////////////////////////////////////
    
    /** Show booking seasonfilters table */
    public function wpbc_seasonfilters_table__show() {    
        // echo ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() . '<div class="clear" style="height:20px;"></div>' : '' );
        
        $columns = array();
        $columns[ 'check' ] = array( 'title' => '<input type="checkbox" value="" id="' . $this->html_prefix . 'select_all" name="' . $this->html_prefix . 'select_id_all" />'
                                        , 'class' => 'check-column' 
                                    );
        $columns[ 'id' ] = array(         'title' => __( 'ID' )
                                        , 'style' => 'width:5em;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'sortable' => true 
                                    );
        
        $columns = apply_filters ('wpbc_seasonfilters_table_header__before_title' , $columns );
        
        $columns[ 'title' ] = array(      'title' => __( 'Title', 'booking' )
                                        , 'style' => 'width:12em;'
                                        , 'sortable' => true 
                                    );
        
        $columns[ 'info' ] = array(      'title' => __( 'Info', 'booking' )
                                        , 'style' => ''
                                        //, 'class' => 'wpbc_hide_mobile'
                                        //, 'sortable' => true 
                                    );
                
        $columns = apply_filters ('wpbc_seasonfilters_table_header__user_title' , $columns );        

        $columns[ 'actions' ] = array(       'title' => __( 'Actions', 'booking' )
                                        , 'style' => 'width:7em;text-align:center;'
                                        //, 'sortable' => true 
                                    );
        
        $columns = apply_filters ('wpbc_seasonfilters_table_header__last' , $columns );        
        
        $wpbc_sf_table = new WPBC_SF_Table( 
                            'seasonfilters' 
                            , array(
                                  'url_sufix'   =>  '#wpbc_' . $this->html_prefix . 'table'  // Link to  scroll
                                , 'rows_func'   =>  array( $this, 'wpbc_seasonfilters_table__show_rows' ) 
                                , 'columns'     =>  $columns
                                , 'is_show_pseudo_search_form' => false
                            )
                        );

        $wpbc_sf_table->display();             
    }   
    

    /**
	 * Show rows for booking resource table
     * 
     * @param int $row_num
     * @param array $resource
     */
    public function wpbc_seasonfilters_table__show_rows( $row_num, $resource ) {
        
        $css_class = ' wpbc_seasonfilters_row';
                
        if ( $this->edit_filter_id == $resource['id'] ) {
            $css_class .= ' row_selected_color';
        }
        
        ?><tr class="wpbc_row<?php echo $css_class; ?>" id="resource_<?php echo $resource['id']; ?>"><?php

                    ?><th class="check-column">
                            <label class="screen-reader-text" for="<?php echo $this->html_prefix; ?>select_<?php echo $resource['id' ]; ?>"><?php echo esc_js(__('Select Booking Resource', 'booking')); ?></label>
                            <input type="checkbox" 
                                           id="<?php echo $this->html_prefix; ?>select_<?php echo $resource['id' ]; ?>" 
                                           name="<?php echo $this->html_prefix; ?>select_<?php echo $resource['id' ]; ?>" 
                                           value="<?php echo $this->html_prefix; ?>id_<?php echo $resource['id' ]; ?>" 
                                />       
                    </th>
                    <td class="wpbc_hide_mobile"><?php echo $resource['id' ]; ?></td>
                    <?php do_action( 'wpbc_seasonfilters_table_show_col__before_title', $row_num, $resource ); ?>
                    <td>
                        <input type="text" 
                               value="<?php echo esc_attr( $resource['title'] ); ?>" 
                               id="<?php echo $this->html_prefix . 'title_' . $resource['id' ]; ?>" 
                               name="<?php echo $this->html_prefix . 'title_' . $resource['id' ]; ?>" 
                               class="large-text" 
                               style="float:right;<?php  
                                    if ( ! empty( $resource['parent']) ) {
                                       echo 'width:95%;font-weight:400;';
                                    } else {
                                      echo 'width:100%;font-weight:600;';
                                    }
                                    ?>" 
                        />
                    </td>
                    <td><?php echo wpbc_get_filter_description( $resource['filter' ] ); ?></td>
                    <?php do_action( 'wpbc_seasonfilters_table_show_col__user_field',  $row_num, $resource ); ?>
                    <td style="text-align:center;">
                        <a  
                            href="javascript:void(0);" 
                            onclick="javascript:jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('edit_season_filter');
                                                jQuery('#edit_season_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                                jQuery('#<?php echo self::ACTION_FORM; ?>').submit();"
                            class="tooltip_top button-secondary button" 
                            title="<?php _e('Edit' ,'booking'); ?>"
                        ><i class="glyphicon glyphicon-edit"></i></a>
                        <a  
                            href="javascript:void(0);" 
                            onclick="javascript:if ( wpbc_are_you_sure('<?php echo esc_js(__('Do you really want to do this ?' ,'booking')); ?>') ) {
                                        jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('delete_season_filter');
                                        jQuery('#edit_season_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                        jQuery('#<?php echo self::ACTION_FORM; ?>').submit();
                                    }"
                            class="tooltip_top button-secondary button" 
                            title="<?php _e('Completely Delete' ,'booking'); ?>"                        
                        ><i class="glyphicon glyphicon-remove"></i></a>

                    </td>
                    <?php do_action( 'wpbc_seasonfilters_table_show_col__last',         $row_num, $resource ); ?>
        </tr>
        <?php    
    }
    
    //                                                                          </editor-fold>
        
    
    //                                                                              <editor-fold   defaultstate="collapsed"   desc=" A c t i o n  s    F o r m " >    
    
    /**
	 * Check Actions in  ACTION_FORM :  Add New | Edit | Delete                - Here we can  EDIT only  ONE season  per action
     * 
     * @return boolean  - false, if nothing made  here
     */
    public function wpbc_check_sub_actions(){

        global $wpdb;
        
        $html_prefix = 'sfd_';

        if ( isset( $_GET['edit_season_id'] ) ) {                                    // In case if we need to  open  direct  link for editing some filter
            
            $action     = 'edit_season_filter';
            $edit_season_id  = intval( $_GET['edit_season_id'] );
            
        } else {
        
                if ( isset( $_POST['is_form_sbmitted_'. self::ACTION_FORM ] ) ) {

                    // Nonce checking    {Return false if invalid, 1 if generated between, 0-12 hours ago, 2 if generated between 12-24 hours ago. }
                    $nonce_gen_time = check_admin_referer( 'wpbc_settings_page_' . self::ACTION_FORM );  // Its stop show anything on submiting, if its not refear to the original page

                    // If we have wrong nonce,  so its will be stop executing here            
                } else 
                    return false;                                                       // If we do  not submit sub action,  then exit    

                ////////////////////////////////////////////////////////////////////////

                if ( isset( $_POST[ 'action_' .  self::ACTION_FORM ] ) ) 
                    $action = $_POST[ 'action_' .  self::ACTION_FORM ];

                if ( empty( $action ) ) return  false;

                if ( isset( $_POST[ 'edit_season_id_' .  self::ACTION_FORM ] ) ) 
                    $edit_season_id = intval( $_POST[ 'edit_season_id_' .  self::ACTION_FORM ] );
        }
        ////////////////////////////////////////////////////////////////////////
        
        if (  in_array( $action, array( 'create_filter_range_days', 'create_filter_conditional', 'edit_season_filter' ) )  )     // Actions for showing section - MetaBox
             $is_show_section = true;
        else $is_show_section = false;
                
        if ( $is_show_section ) {
            ?><div class="clear" style="margin-top:20px;"></div><?php 
            ?><div id="wpbc_<?php echo $this->html_prefix; ?>table_<?php echo $action; ?>" class="wpbc_settings_row wpbc_settings_row_rightNO"><?php                   
        }
        
        ////////////////////////////////////////////////////////////////////////
        
        switch ( $action ) {

            ////////////////////////////////////////////////////////////////////
            // Range Days
            ////////////////////////////////////////////////////////////////////
            
            case 'create_filter_range_days':                                         //  Add New - Range Dates

                wpbc_open_meta_box_section( $action . '_new', __('Specific Dates Filter', 'booking')  );  
                    $this->season_filter__range_days();
                wpbc_close_meta_box_section();
                break;
            
            case 'insert_sql_filter_range_days' :
            case 'update_sql_filter_range_days':                                // Update SQL - Conditional Dates

                // Validate                                                     // Title
                $validated_title = WPBC_Settings_API::validate_text_post_static( $html_prefix . 'days_filter_name' );

                $filter = array();
                for ( $yy = ( date_i18n( 'Y') - 1 ); $yy < ( date_i18n( 'Y') + 10 ); $yy++ ) {
                    
                    $filter[$yy] = array();     
                    
                    for ( $mm = 1; $mm < 13; $mm++ ) {
                        
                        $filter[$yy][$mm] = array();
                        
                        for ( $dd = 1; $dd < 32; $dd++ ) {
                            
                            $day_filter_id = $yy . '-' . $mm . '-' . $dd;              
                            
                            if (  isset( $_POST[ $html_prefix . $day_filter_id  ] ) ) {
                                $filter[ $yy ][ $mm ][ $dd ] = 1 ;
                            }
                        }
                    }
                }
                $filter['name'] = $validated_title;
                $filter['version'] = '2.0';
                                   
                $ser_filter = serialize( $filter );
                
                
                ////////////////////////////////////////////////////////////////
                // S Q L
                //////////////////////////////////////////////////////////////// 
                
                if ( $action == 'insert_sql_filter_range_days' ) { 
                    $sql = $this->get_insert_sql( $validated_title , $ser_filter );     // SQL INSERT
                }
                if ( $action == 'update_sql_filter_range_days' ) {                     
                    $sql = $this->get_update_sql( $validated_title , $ser_filter, $edit_season_id );     // SQL UPDATE
                }

                if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error saving to DB' ,__FILE__ , __LINE__); }         // Save to DB

                wpbc_show_changes_saved_message();   

                make_bk_action( 'wpbc_reinit_seasonfilters_cache' );
                

                break;                

            
            ////////////////////////////////////////////////////////////////////
            // Conditional
            ////////////////////////////////////////////////////////////////////
            
            case 'create_filter_conditional':                                   // Add New - Conditional Dates
                
                wpbc_open_meta_box_section( $action . '_new', __('Conditional Dates Filter', 'booking')  );  
                    $this->season_filter__conditional();
                wpbc_close_meta_box_section();
                break;

            
            case 'insert_sql_filter_conditional' :
            case 'update_sql_filter_conditional': 

                $filter = array(  'weekdays' => array()
                                , 'days'     => array()
                                , 'monthes'  => array()
                                , 'year'     => array()                    
                            ); 
                
                // Validate                                                     // Title
                $validated_title = WPBC_Settings_API::validate_text_post_static( $html_prefix . 'days_filter_name' );
                                
                for ( $k = 0; $k < 7; $k++ ) {                                  // Weekdays                   
                   $filter['weekdays'][$k] = WPBC_Settings_API::validate_checkbox_post_static( $html_prefix . 'weekday_' . $k );                   
                }                
                for ( $k = 1; $k < 32; $k++ ) {                                 // Days                   
                   $filter['days'][$k] = WPBC_Settings_API::validate_checkbox_post_static( $html_prefix .     'days_' . $k );                   
                }                
                for ( $k = 1; $k < 13; $k++ ) {                                 // Monthes
                   $filter['monthes'][$k] = WPBC_Settings_API::validate_checkbox_post_static( $html_prefix .  'months_' . $k );                   
                }                
                $start_year = intval( date('Y') - 1 );                          // Years
                $end_year   = intval( date('Y') + 9 );
                for ( $k = $start_year; $k < ( $end_year + 1 ); $k++ ) { 
                   $filter['year'][$k] = WPBC_Settings_API::validate_checkbox_post_static( $html_prefix .     'years_' . $k );                   
                }
                $ser_filter = serialize( $filter );
                
                ////////////////////////////////////////////////////////////////
                // S Q L
                //////////////////////////////////////////////////////////////// 
                
                if ( $action == 'insert_sql_filter_conditional' ) { 
                    $sql = $this->get_insert_sql( $validated_title , $ser_filter );     // SQL INSERT
                }
                if ( $action == 'update_sql_filter_conditional' ) {                     
                    $sql = $this->get_update_sql( $validated_title , $ser_filter, $edit_season_id );     // SQL UPDATE
                }

                if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error saving to DB' ,__FILE__ , __LINE__); }         // Save to DB

                wpbc_show_changes_saved_message();   

                make_bk_action( 'wpbc_reinit_seasonfilters_cache' );
                
                break;                
            

            ////////////////////////////////////////////////////////////////////
            // Edit Visual Section
            ////////////////////////////////////////////////////////////////////
            
            case 'edit_season_filter':                                          // Edit Section
                
                if ( $edit_season_id > 0 ) {
                    
                    $wpbc_sf_cache = wpbc_sf_cache();
                    
                    $item_arr = $wpbc_sf_cache->get_resource_attr( $edit_season_id );

                    if ( ! empty( $item_arr ) ) {

                        $item_arr['filter'] = maybe_unserialize( $item_arr['filter'] );             // Check type of this season filter here 
                         
                        if (   (  isset( $item_arr['filter']['version'] )  )  &&  ( $item_arr['filter']['version'] == '2.0'  )   ) {
                                                                                // Range Days Season Filter
                            wpbc_open_meta_box_section( $action . '_new', __('Edit', 'booking') . ' ' . __('Specific Dates Filter', 'booking')  );  
                                $this->season_filter__range_days( $item_arr );
                            wpbc_close_meta_box_section();
                            
                        } else {                                                // Conditionl Season Filter 
                            
                            wpbc_open_meta_box_section( $action . '_new', __('Edit', 'booking') . ' ' . __('Conditional Dates Filter', 'booking')  );
                                $this->season_filter__conditional( $item_arr );
                            wpbc_close_meta_box_section();
                        }

                    } else wpbc_show_message_in_settings( __( 'Nothing Found', 'booking' ) . '.', 'warning', __('Error' ,'booking') . '.' );

                } else wpbc_show_message_in_settings( __( 'Nothing Found', 'booking' ) . '.', 'warning', __('Error' ,'booking') . '.' );
                
                break;
            
            ////////////////////////////////////////////////////////////////////
            // SQL Delete
            ////////////////////////////////////////////////////////////////////
                
            case 'delete_season_filter':                                        // Delete
                if ( $edit_season_id <= 0 ) return false;
                
                    $sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d" , $edit_season_id ) ;

                    if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error during deleting items in DB' ,__FILE__ , __LINE__); }         // Action inDB

                    wpbc_show_message ( __( 'Deleted', 'booking'), 5 );

                break;
                
            default:
                return false;
                break;
        }
        
        ////////////////////////////////////////////////////////////////////////

        if ( $is_show_section ) {    
                
            ?><div class="clear" style="margin-top:20px;"></div><?php 
            ?></div><?php                 
        }
        
        ////////////////////////////////////////////////////////////////////////
        
        return true;
    }
    
    
            /**
	 * Get SQL  for inserting
             * 
             * @param string $validated_title - name of filter - title
             * @param string $ser_filter  - serilized filter
             * @return string           - SQL
             */
            private function get_insert_sql( $validated_title , $ser_filter ) {
                    
                    global $wpdb;
                
                    // Need this complex query  for ability to  define different paramaters in differnt versions.
                    $sql_arr = apply_filters(   'wpbc_seasonfilters_table__add_new_sql_array'
                                                        , array(
                                                                'sql'       => array(
                                                                                      'start'      => "INSERT INTO {$wpdb->prefix}booking_seasons "
                                                                                    , 'params'     => array( 'title' , 'filter' )    
                                                                                    , 'param_types' => array( '%s' , '%s' )    
                                                                            )
                                                                , 'values'  => array( $validated_title , $ser_filter )
                                                            )
                                        );                                                                                                                                                                                    
                    $sql = $wpdb->prepare(    $sql_arr['sql']['start']                                                              // SQL
                                                .         '( ' . implode( ',' , $sql_arr['sql']['params'] ) . ') '
                                                . ' VALUES ( ' . implode( ',' , $sql_arr['sql']['param_types'] ) . ') '
                                            , $sql_arr[ 'values' ]                                                                  // Array of validated parameters
                                        ); 

                    return $sql;
            }

            
            /**
	 * Get SQL  for updating
             * 
             * @param string $validated_title - name of filter - title
             * @param string $ser_filter  - serilized filter
             * @param int $edit_season_id - ID of element
             * @return string           - SQL
             * 
             */
            private function get_update_sql( $validated_title , $ser_filter, $edit_season_id ) {
                
                    global $wpdb;
                    
                    // We no need to update it with  Filters, because we can  change User only in TABLE,  so here we do not update User Collumn
                    $sql_arr = apply_filters(   'wpbc_seasonfilters_table__single_update_sql_array'
                                                        , array(
                                                                'sql'       => array(
                                                                                      'start'   => "UPDATE {$wpdb->prefix}booking_seasons SET "
                                                                                    , 'params' => array( 'title = %s', 'filter = %s' )                         
                                                                                    , 'end'    => " WHERE booking_filter_id = %d"
                                                                            )
                                                                , 'values'  => array( $validated_title , $ser_filter  )
                                                            )
                                        );                
                    $sql_arr['values'][] = intval( $edit_season_id );                // last parameter  for " WHERE booking_type_id = %d "

                    $sql = $wpdb->prepare(    $sql_arr['sql']['start']                          // SQL
                                                . implode( ',' , $sql_arr['sql']['params'] ) 
                                                . $sql_arr['sql']['end']            
                                            , $sql_arr[ 'values' ]                              // Array of validated parameters
                                        );                     

                    return $sql;
            }
            
            
    /**
	 * Conditional     Season Filter   Section
     * 
     * @param array $item_arr - if editing,  then transfer here this element 
     */
    public function season_filter__conditional( $item_arr = array() ) {

        if ( ! empty( $item_arr ) ) $is_edit = true;
        else                        $is_edit = false;

        if ( $is_edit )
            $this->edit_filter_id = $item_arr['id'];
        
        $html_prefix = 'sfd_';
        
        // Name
        ?><table class="form-table"><tbody><?php   

            WPBC_Settings_API::field_text_row_static(                                              
                                                      $html_prefix . 'days_filter_name'
                                            , array(  
                                                      'type'              => 'text'
                                                    , 'title'             => __('Filter Name', 'booking')
                                                    , 'description'       => __('Type filter name', 'booking')
                                                    , 'placeholder'       => ''
                                                    , 'description_tag'   => 'span'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => ''
                                                    , 'only_field'        => false
                                                    , 'attr'              => array()                                                    
                                                    , 'validate_as'       => array( 'required' )
                                                    , 'value'             => ( $is_edit ) ? $item_arr['title'] : ''
                                                )
                                    );                  
        ?></tbody></table><?php


        //                                                                      <editor-fold   defaultstate="collapsed"   desc=" W E E K    D A Y S " >    

        /**
	 * Selections of several  checkboxes like in gMail with shift :)
        * Need to  have this structure: 
        * .wpbc_selectable_table
        *      .wpbc_selectable_head
        *              .check-column
        *                  :checkbox
        *      .wpbc_selectable_body
        *          .wpbc_row
        *              .check-column
        *                  :checkbox
        *      .wpbc_selectable_foot             
        *              .check-column
        *                  :checkbox
        */
        $name_elmnt = $html_prefix . 'weekday'; 
        $title_elmnt = __( 'Weekdays', 'booking' );
        $elmnt_arr = array(   __('Sunday' ,'booking')
                            , __('Monday' ,'booking')
                            , __('Tuesday' ,'booking')
                            , __('Wednesday' ,'booking')
                            , __('Thursday' ,'booking')
                            , __('Friday' ,'booking')
                            , __('Saturday' ,'booking')  
                        );            
        ?><div class="wpbc_selectable_table wpbc_sf_section_selectable <?php echo $name_elmnt; ?>">
            <div class="wpbc_selectable_head">
                <div class="check-column">
                    <label for="<?php echo $name_elmnt; ?>_all" class="wpbc-form-checkbox">
                        <input type="checkbox" autocomplete="off" value="Off"
                               name="<?php echo $name_elmnt; ?>_all" id="<?php echo $name_elmnt; ?>_all" />&nbsp;<?php  
                            echo $title_elmnt; ?>
                    </label>
                </div>
            </div>
            <?php
            ?><div class="wpbc_selectable_body"><?php
                foreach ( $elmnt_arr as $i_el => $title_el ) {            
                ?>
                <div class="wpbc_row">
                    <div class="check-column">
                        <label for="<?php echo $name_elmnt; ?>_<?php echo $i_el; ?>" class="wpbc-form-checkbox" style="<?php if ( date_i18n( "w") ==  $i_el ) { echo 'border-bottom:1px dashed;'; } ?>" >
                            <input type="checkbox" autocomplete="off" value="Off" 
                                   <?php if ( $is_edit ) {    checked( $item_arr['filter']['weekdays'][ $i_el ], 'On' );    }  ?> 
                                   name="<?php echo $name_elmnt . '_' . $i_el; ?>" id="<?php echo $name_elmnt . '_' . $i_el; ?>" /><?php  
                                echo $title_el; ?>
                        </label>
                    </div>
                </div>
                <?php
                }
            ?></div><?php 
            ?><div class="clear"></div><?php
        ?></div><?php
        ?><div class="clear"></div><?php
        //                                                                              </editor-fold>


        //                                                                              <editor-fold   defaultstate="collapsed"   desc=" D A Y S " >    


        /**
	 * Selections of several  checkboxes like in gMail with shift :)
        * Need to  have this structure: 
        * .wpbc_selectable_table
        *      .wpbc_selectable_head
        *              .check-column
        *                  :checkbox
        *      .wpbc_selectable_body
        *          .wpbc_row
        *              .check-column
        *                  :checkbox
        *      .wpbc_selectable_foot             
        *              .check-column
        *                  :checkbox
        */
        $name_elmnt = $html_prefix . 'days';  
        $title_elmnt = ucwords(strtolower( __( 'days', 'booking' ) )) . ': '; 
        $elmnt_arr = array_combine( range( 1, 31 ), range( 1, 31 ) );

        ?><div class="wpbc_selectable_table wpbc_sf_section_selectable <?php echo $name_elmnt; ?>">
            <div class="wpbc_selectable_head">
                <div class="check-column">
                    <label for="<?php echo $name_elmnt; ?>_all" class="wpbc-form-checkbox">
                        <input type="checkbox" autocomplete="off" value="Off" name="<?php echo $name_elmnt; ?>_all" id="<?php echo $name_elmnt; ?>_all" />&nbsp;<?php  
                            echo  $title_elmnt; ?>
                    </label>
                </div>
            </div>
            <?php
            ?><div class="wpbc_selectable_body"><?php
                foreach ( $elmnt_arr as $i_el => $title_el ) {    
                    $title_el = ($title_el < 10 ) ? '0' . $title_el : $title_el;    
                    if ( $i_el % 32 == 0 ) { echo '<div class="clear"></div>'; } // New line
                ?>
                <div class="wpbc_row">
                    <div class="check-column">
                        <label for="<?php echo $name_elmnt; ?>_<?php echo $i_el; ?>" class="wpbc-form-checkbox" style="<?php if ( date_i18n( "d") ==  $i_el ) { echo 'border-bottom:1px dashed;'; } ?>" >
                            <input type="checkbox" autocomplete="off" value="Off" 
                                   <?php if ( $is_edit ) {    checked( $item_arr['filter']['days'][ $i_el ], 'On' );    }  ?> 
                                   name="<?php echo $name_elmnt . '_' . $i_el; ?>" id="<?php echo $name_elmnt . '_' . $i_el; ?>" /><?php  
                                echo $title_el; ?>
                        </label>
                    </div>
                </div>
                <?php                                        
                }
            ?></div><?php 
            ?><div class="clear"></div><?php
        ?></div><?php
        ?><div class="clear"></div><?php            
        //                                                                              </editor-fold>


        //                                                                              <editor-fold   defaultstate="collapsed"   desc=" M O N T H S " >    

        /**
	 * Selections of several  checkboxes like in gMail with shift :)
        * Need to  have this structure: 
        * .wpbc_selectable_table
        *      .wpbc_selectable_head
        *              .check-column
        *                  :checkbox
        *      .wpbc_selectable_body
        *          .wpbc_row
        *              .check-column
        *                  :checkbox
        *      .wpbc_selectable_foot             
        *              .check-column
        *                  :checkbox
        */
        $name_elmnt = $html_prefix . 'months';  
        $title_elmnt = __( 'Months', 'booking' );
        $elmnt_arr = array(
                            1 =>  __('January' ,'booking'), 
                            2 =>  __('February' ,'booking'), 
                            3 =>  __('March' ,'booking'), 
                            4 =>  __('April' ,'booking'), 
                            5 =>  __('May' ,'booking'), 
                            6 =>  __('June' ,'booking'), 
                            7 =>  __('July' ,'booking'), 
                            8 =>  __('August' ,'booking'), 
                            9 =>  __('September' ,'booking'), 
                            10 =>  __('October' ,'booking'), 
                            11 =>  __('November' ,'booking'), 
                            12 =>  __('December' ,'booking'),                             
                            );

        ?><div class="wpbc_selectable_table wpbc_sf_section_selectable <?php echo $name_elmnt; ?>">
            <div class="wpbc_selectable_head">
                <div class="check-column">
                    <label for="<?php echo $name_elmnt; ?>_all" class="wpbc-form-checkbox">
                        <input type="checkbox" autocomplete="off" value="Off" name="<?php echo $name_elmnt; ?>_all" id="<?php echo $name_elmnt; ?>_all" />&nbsp;<?php  
                            echo $title_elmnt; ?>
                    </label>
                </div>
            </div>
            <?php
            ?><div class="wpbc_selectable_body"><?php
                foreach ( $elmnt_arr as $i_el => $title_el ) {    
                ?>
                <div class="wpbc_row">
                    <div class="check-column">
                        <label for="<?php echo $name_elmnt; ?>_<?php echo $i_el; ?>" class="wpbc-form-checkbox" style="<?php if ( date_i18n( "m") ==  $i_el ) { echo 'border-bottom:1px dashed;'; } ?>" >
                            <input type="checkbox" autocomplete="off" value="Off" 
                                   <?php if ( $is_edit ) {    checked( $item_arr['filter']['monthes'][ $i_el ], 'On' );    }  ?> 
                                   name="<?php echo $name_elmnt . '_' . $i_el; ?>" id="<?php echo $name_elmnt . '_' . $i_el; ?>" /><?php  
                                echo $title_el; ?>
                        </label>
                    </div>
                </div>
                <?php                    
                if ( $i_el % 12 == 0 ) { echo '<div class="clear"></div>'; } // New line                    
                }
            ?></div><?php 
            ?><div class="clear"></div><?php
        ?></div><?php
        ?><div class="clear"></div><?php
        //                                                                              </editor-fold>


        //                                                                              <editor-fold   defaultstate="collapsed"   desc=" YEARS " >    


        /**
	 * Selections of several  checkboxes like in gMail with shift :)
        * Need to  have this structure: 
        * .wpbc_selectable_table
        *      .wpbc_selectable_head
        *              .check-column
        *                  :checkbox
        *      .wpbc_selectable_body
        *          .wpbc_row
        *              .check-column
        *                  :checkbox
        *      .wpbc_selectable_foot             
        *              .check-column
        *                  :checkbox
        */
        $name_elmnt = $html_prefix . 'years'; 
        $title_elmnt = __( 'Years', 'booking' );
        $elmnt_arr = array_combine( range( (date('Y') - 1) , (date('Y') + 9) ), range( (date('Y') - 1) , (date('Y') + 9) ) );

        ?><div class="wpbc_selectable_table wpbc_sf_section_selectable <?php echo $name_elmnt; ?>">
            <div class="wpbc_selectable_head">
                <div class="check-column">
                    <label for="<?php echo $name_elmnt; ?>_all" class="wpbc-form-checkbox">
                        <input type="checkbox" autocomplete="off" value="Off" name="<?php echo $name_elmnt; ?>_all" id="<?php echo $name_elmnt; ?>_all" />&nbsp;<?php  
                            echo $title_elmnt; ?>
                    </label>
                </div>
            </div>
            <?php
            ?><div class="wpbc_selectable_body"><?php
                foreach ( $elmnt_arr as $i_el => $title_el ) {    
                    $title_el = ($title_el < 10 ) ? '0' . $title_el : $title_el;    
                    //if ( $i_el % 16 == 0 ) { echo '<div class="clear"></div>'; } // New line
                ?>
                <div class="wpbc_row">
                    <div class="check-column">
                        <label for="<?php echo $name_elmnt; ?>_<?php echo $i_el; ?>" class="wpbc-form-checkbox" style="<?php if ( date_i18n( "Y") ==  $i_el ) { echo 'border-bottom:1px dashed;'; } ?>" >
                            <input type="checkbox" autocomplete="off" value="Off" 
                                   <?php if ( $is_edit ) {    checked(   
                                                                        (  isset( $item_arr['filter']['year'][ $i_el ] ) ? $item_arr['filter']['year'][ $i_el ] : 'Off'  )
                                                                        , 'On' 
                                                                );    }  ?> 
                                   name="<?php echo $name_elmnt . '_' . $i_el; ?>" id="<?php echo $name_elmnt . '_' . $i_el; ?>" /><?php  
                                echo $title_el; ?>
                        </label>
                    </div>
                </div>
                <?php                                        
                }
            ?></div><?php 
            ?><div class="clear"></div><?php
        ?></div><?php
        ?><div class="clear" style="height:10px;"></div><?php 
        //                                                                              </editor-fold>

        ?>
        <a href="javascript:void(0);" class="button button-primary"
           onclick="javascript: if ( jQuery('#sfd_days_filter_name').val() == '') { wpbc_field_highlight( '#sfd_days_filter_name' );  return false; }
                                if ( ! wpbc_check_selected_checkboxes_in_sections() ) { return false; }
                                <?php if ( $is_edit ) { ?> 
                                    jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('update_sql_filter_conditional');
                                    jQuery('#edit_season_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $item_arr['booking_filter_id']; ?>');
                                <?php } else {?> 
                                    jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('insert_sql_filter_conditional');
                                <?php } ?>                    
                                jQuery(this).closest('form').submit();" 
            ><?php echo ( ( $is_edit ) ? __('Save Changes', 'booking') : __('Add New', 'booking') ); ?></a>
        <a href="javascript:void(0);" class="button wpbc-reset-button"  
           onclick="javascript:jQuery(this).closest('form').find('input[type=\'text\'],input[type=\'checkbox\']').val('').removeAttr('checked').removeAttr('selected');" 
            ><?php _e('Reset', 'booking'); ?></a>
        <?php            
    }


    
    /**
	 * Range Days     Season Filter   Section
     * 
     * @param array $item_arr - if editing,  then transfer here this element 
     */
    public function season_filter__range_days( $item_arr = array() ) {
//debuge($item_arr);
        if ( ! empty( $item_arr ) ) $is_edit = true;
        else                        $is_edit = false;
        
        if ( $is_edit )
            $this->edit_filter_id = $item_arr['id'];
        

        $html_prefix = 'sfd_';
        
        ?><div class="wpbc_season_filter__range_days"><?php 
            // Name
            ?><table class="form-table"><tbody><?php   

                WPBC_Settings_API::field_text_row_static(                                              
                                                          $html_prefix . 'days_filter_name'
                                                , array(  
                                                          'type'              => 'text'
                                                        , 'title'             => __('Filter Name', 'booking')
                                                        , 'description'       => __('Type filter name', 'booking')
                                                        , 'placeholder'       => ''
                                                        , 'description_tag'   => 'span'
                                                        , 'tr_class'          => ''
                                                        , 'class'             => ''
                                                        , 'css'               => ''
                                                        , 'only_field'        => false
                                                        , 'attr'              => array()                                                    
                                                        , 'validate_as'       => array( 'required' )
                                                        , 'value'             => ( $is_edit ) ? $item_arr['title'] : ''
                                                    )
                                        );                  
            ?></tbody></table><?php

            $dwa = array( 1 => __( 'Mo', 'booking' )
                        , 2 => __( 'Tu', 'booking' )
                        , 3 => __( 'We', 'booking' )
                        , 4 => __( 'Th', 'booking' )
                        , 5 => __( 'Fr', 'booking' )
                        , 6 => __( 'Sa', 'booking' )
                        , 7 => __( 'Su', 'booking' ) );

            

            $elmnt_months = array(
                                1 =>  __('January' ,'booking'), 
                                2 =>  __('February' ,'booking'), 
                                3 =>  __('March' ,'booking'), 
                                4 =>  __('April' ,'booking'), 
                                5 =>  __('May' ,'booking'), 
                                6 =>  __('June' ,'booking'), 
                                7 =>  __('July' ,'booking'), 
                                8 =>  __('August' ,'booking'), 
                                9 =>  __('September' ,'booking'), 
                                10 =>  __('October' ,'booking'), 
                                11 =>  __('November' ,'booking'), 
                                12 =>  __('December' ,'booking') );

            ?><div class="clear" style="height:5px;"></div><?php
            echo '<span class="wpdevelop">';
                /*
                // Header
                for ( $yy = ( date_i18n( 'Y') - 1 ); $yy < ( date_i18n( 'Y') + 10 ); $yy++ ) {
                    ?><div class="visibility_container clearfix-height sf_container_<?php echo $yy; ?>" style="display:<?php echo ( $yy == date_i18n( 'Y') ) ? 'block' : 'none'  ?>;margin-top:-5px;"><?php 

                        echo '<h3>' . $yy  . '</h3>';
                        
                    ?></div><?php    
                }
                ?><div class="clear"></div><?php
                */
                // Tabs 
                wpbc_bs_toolbar_tabs_html_container_start();

                    for ( $yy = ( date_i18n( 'Y') - 1 ); $yy < ( date_i18n( 'Y') + 10 ); $yy++ ) {
                    
                
                        wpbc_bs_display_tab(   array(
                                                        'title'         => $yy 
                                                        // , 'hint' => array( 'title' => __('Manage bookings' ,'booking') , 'position' => 'top' )
                                                        , 'onclick'     =>    "jQuery('.wpbc_season_filter__range_days .visibility_container').hide();"
                                                                            . "jQuery('.wpbc_season_filter__range_days .sf_container_' + jQuery(this).find('input[type=\'checkbox\']').val()  ).show();"
                                                                            . "jQuery('.wpbc_season_filter__range_days .nav-tab').removeClass('nav-tab-active');"
                                                                            . "jQuery(this).addClass('nav-tab-active');"
                                                                            . "jQuery('.wpbc_season_filter__range_days .nav-tab i.icon-white').removeClass('icon-white');"
                                                                            . "jQuery('.wpbc_season_filter__range_days .nav-tab-active i').addClass('icon-white');"
                                                        
                                                        , 'font_icon'   => ''
                                                        , 'default'     => ( $yy == date_i18n( 'Y') ) ? true : false
                                                        , 'checkbox'    => array( 'checked' => false, 'name' => 'sf_tab_check_' . $yy , 'value' => $yy
                                                                                  , 'onclick' =>  " jQuery( '.sf_rd_year_' + jQuery(this).val() ).prop('checked',  jQuery(this).is(':checked') ); "
                                                                                )
                                        ) ); 
                    }

                wpbc_bs_toolbar_tabs_html_container_end();
            echo '</span>';
            ?><div class="clear" style="height:5px;"></div><?php
            
            // Containers 
            for ( $yy = ( date_i18n( 'Y') - 1 ); $yy < ( date_i18n( 'Y') + 10 ); $yy++ ) {
                ?><div class="visibility_container clearfix-height sf_container_<?php echo $yy; ?>" style="display:<?php echo ( $yy == date_i18n( 'Y') ) ? 'block' : 'none'  ?>;margin-top:-5px;"><?php 
                
                

                    foreach ( $elmnt_months as $mm => $month_title ) {


                        //                                                                              <editor-fold   defaultstate="collapsed"   desc=" D A Y S " >    


                        /**
	 * Selections of several  checkboxes like in gMail with shift :)
                        * Need to  have this structure: 
                        * .wpbc_selectable_table
                        *      .wpbc_selectable_head
                        *              .check-column
                        *                  :checkbox
                        *      .wpbc_selectable_body
                        *          .wpbc_row
                        *              .check-column
                        *                  :checkbox
                        *      .wpbc_selectable_foot             
                        *              .check-column
                        *                  :checkbox
                        */

                        $name_elmnt  = $html_prefix . 'all_' . $yy . '_' . $mm;  
                        $title_elmnt = $month_title . ': '; 

                        ?><div class="wpbc_selectable_table wpbc_sf_section_selectable <?php echo $name_elmnt; ?>">
                            <div class="wpbc_selectable_head">
                                <div class="check-column">
                                    <label for="<?php echo $name_elmnt; ?>_all" class="wpbc-form-checkbox">
                                        <input type="checkbox" autocomplete="off" value="Off" name="<?php echo $name_elmnt; ?>_all" id="<?php echo $name_elmnt; ?>_all" /> &nbsp; <?php  
                                            echo  $title_elmnt; ?>
                                    </label>
                                </div>
                            </div>
                            <?php
                            ?><div class="wpbc_selectable_body"><?php

                                $day_num_previous = '00';
                                for ($dd = 1; $dd < 32; $dd++ ) {

                                    $title_el = ($dd < 10 ) ? '0' . $dd : $dd;    

                                    $day_filter_id = $yy . '-' . $mm . '-' . $dd;
                                    $day_num       = date( "d", mktime( 0, 0, 0, $mm, $dd, $yy ) );
                                    $day_week      = date( "N", mktime( 0, 0, 0, $mm, $dd, $yy ) );
                                    $is_checked = false;                        
                                    if (       ( isset( $item_arr ) ) 
                                            && ( isset( $item_arr[ 'filter' ] ) )
                                            && ( isset( $item_arr[ 'filter' ][ $yy ] ) )
                                            && ( isset( $item_arr[ 'filter' ][ $yy ][ $mm ] ) )
                                            && ( isset( $item_arr[ 'filter' ][ $yy ][ $mm ][ $dd ] ) )
                                        ) {
                                            $is_checked = true;
                                    }

                                    if ( $day_num_previous < $day_num ) {
                                        $day_num_previous = $day_num;
                                        ?>
                                        <div class="wpbc_row">
                                            <div class="check-column weekday<?php echo $day_week ?>">
                                                <label for="<?php echo $html_prefix . $day_filter_id; ?>" class="wpbc-form-checkbox" style="">
                                                    <?php 
                                                    echo '<div class="day_num">' . $title_el . '</div>'; 
                                                    ?>
                                                    <input type="checkbox" autocomplete="off" value="<?php echo $day_filter_id; ?>" 
                                                           <?php if ( $is_edit ) { checked( $is_checked ); }  ?> 
                                                           id="<?php echo $html_prefix . $day_filter_id; ?>" 
                                                           name="<?php echo $html_prefix . $day_filter_id; ?>"
                                                           class="sf_rd_year_<?php echo $yy; ?>"
                                                           /><?php  

                                                    echo '<div class="day_week">' . $dwa[ $day_week ] . '</div>';
                                                    ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php 
                                    }
                                }
                            ?></div><?php 
                            ?><div class="clear"></div><?php
                        ?></div><?php
                        ?><div class="clear"></div><?php            
                        //                                                                              </editor-fold>


                    }
                ?></div><?php   // End Year
            }        
            ?><div class="clear" style="margin-top:20px;"></div>    
            <a href="javascript:void(0);" class="button button-primary"
               onclick="javascript: if ( jQuery('#sfd_days_filter_name').val() == '') { wpbc_field_highlight( '#sfd_days_filter_name' );  return false; }
                                    <?php if ( $is_edit ) { ?> 
                                        jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('update_sql_filter_range_days');
                                        jQuery('#edit_season_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $item_arr['booking_filter_id']; ?>');
                                    <?php } else {?> 
                                        jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('insert_sql_filter_range_days');
                                    <?php } ?>                    
                                    jQuery(this).closest('form').submit();" 
                ><?php echo ( ( $is_edit ) ? __('Save Changes', 'booking') : __('Add New', 'booking') ); ?></a>
            <a href="javascript:void(0);" class="button wpbc-reset-button"  
               onclick="javascript:jQuery(this).closest('form').find('input[type=\'text\'],input[type=\'checkbox\']').val('').removeAttr('checked').removeAttr('selected');" 
                ><?php _e('Reset', 'booking'); ?></a>

        </div><?php 
    }

    //                                                                              </editor-fold>    
        
}
add_action('wpbc_menu_created', array( new WPBC_Page_Settings__seasonfilters() , '__construct') );    // Executed after creation of Menu




















/**
	 * Get description of each season filter in human language
 * 
 * @param obj $filter
 * @return string
 */
function wpbc_get_filter_description( $filter ) {
    
    $filter = maybe_unserialize( $filter );

    $result = '';
    $description = '';
    
    $weekdays = array( __( 'Su', 'booking' ), __( 'Mo', 'booking' ), __( 'Tu', 'booking' ), __( 'We', 'booking' ), __( 'Th', 'booking' ), __( 'Fr', 'booking' ), __( 'Sa', 'booking' ) );
    
    $monthes = array( 0, __( 'Jan', 'booking' ), __( 'Feb', 'booking' )
                    , __( 'Mar', 'booking' ), __( 'Apr', 'booking' ), __( 'May', 'booking' )
                    , __( 'Jun', 'booking' ), __( 'Jul', 'booking' ), __( 'Aug', 'booking' )
                    , __( 'Sep', 'booking' ), __( 'Oct', 'booking' ), __( 'Nov', 'booking' ), __( 'Dec', 'booking' ) );

    if ( (isset( $filter['version'] )) && ( $filter['version'] == '2.0' ) ) {   // New filter
        //'New filter version 2.0';
        $last_day = '';
        $last_day_id = '';
        $last_show_day = '';
        $short_days = array();
        foreach ( $filter as $key_year => $value_months ) {
            if ( is_numeric( $key_year ) ) {                                    // check only years - skip  the "version" and "name" key  fields here
                foreach ( $value_months as $value_month => $value_dates ) {

                    foreach ( $value_dates as $date_num => $is_date ) {

                        if ( ($date_num >= 1) && ($is_date == '1') ) {
                            if ( $value_month < 9 )
                                $v_month = '0' . $value_month;
                            else
                                $v_month = $value_month;
                            if ( $date_num < 9 )
                                $d_num = '0' . $date_num;
                            else
                                $d_num = $date_num;
                            $dte = $key_year . '-' . $v_month . '-' . $d_num;

                            if ( empty( $last_day ) ) {                         // First date
                                $short_days[] = $dte;
                                $last_show_day = $dte;
                            } else {                                            // All other days
                                if ( wpbc_is_next_day( $dte, $last_day ) ) {
                                    if ( $last_show_day != '-' ) {
                                        $short_days[] = '-';
                                    }
                                    $last_show_day = '-';
                                } else {
                                    if ( $last_show_day != $last_day ) {
                                        $short_days[] = $last_day;
                                    }
                                    $short_days[] = ',';
                                    $short_days[] = $dte;
                                    $last_show_day = $dte;
                                }
                            }
                            $last_day = $dte;
                        }
                    }
                }
            }
        }

        if ( isset( $dte ) )
            if ( $last_show_day != $dte ) {
                $short_days[] = $dte;
            }

        $short_dates_content = '';
        foreach ( $short_days as $dt ) {
            if ( $dt == '-' ) {
                $short_dates_content .= '<span class="date_tire1"> - </span>';
            } elseif ( $dt == ',' ) {
                $short_dates_content .= '<span class="date_tire1">, </span>';
            } else {
                $short_dates_content .= '<strong>';
                $bk_date = wpbc_get_date_in_correct_format( $dt );
                $short_dates_content .= $bk_date[0];
                $short_dates_content .= '</strong>';
            }
        }
        $description = $short_dates_content;
        if ( empty( $short_days ) )
            $description = '<span style="color:#ff0000;font-weight:600;">' . __( 'No days', 'booking' ) . '</span>';
    } else if ( (!empty( $filter['start_time'] )) && (!empty( $filter['start_time'] )) ) { // Time availability
        $description .= __( 'From', 'booking' ) . ' <strong>' . $filter['start_time'] . '</strong> ' . __( 'to', 'booking' ) . ' <strong>' . $filter['end_time'] . '</strong> ' . __( 'time', 'booking' );
    } else {
                                                                                // Week days
        $cnt = 0;
        foreach ( $filter['weekdays'] as $key => $value ) {
            if ( $value == 'On' ) {
                if ( $result !== '' )
                    $result .=', ';
                $result .= $weekdays[$key];
                $cnt++;
            }
        }
        if ( ($result !== '' ) || ($cnt == 0) ) {
            if ( $cnt == 7 )
                $description .= '';
            elseif ( $cnt == 0 )
                return '<span style="color:#ff0000;font-weight:600;">' . __( 'No days', 'booking' ) . '</span>';
            else
                $description .= __( 'Every', 'booking' ) . ' <strong>' . $result . '</strong> ';
        }
                                                                                // Days
        $cnt = 0;
        $result = '';
        foreach ( $filter['days'] as $key => $value ) {
            if ( $value == 'On' ) {
                if ( $result !== '' )
                    $result .=', ';
                $result .= $key;
                $cnt++;
            }
        }
        if ( ($result !== '' ) || ($cnt == 0) ) {
            if ( $cnt == 31 ) {
                if ( $description == '' )
                    $description .= __( 'Each day ', 'booking' ) . ' ';
                else
                    $description .= __( 'on each day ', 'booking' ) . ' ';
            } elseif ( $cnt == 0 )
                return '<span style="color:#ff0000;font-weight:600;">' . __( 'No days', 'booking' ) . '</span>';
            else {
                if ( $description == '' )
                    $description .= __( 'On each ', 'booking' ) . ' <strong>' . $result . '</strong> ';
                else
                    $description .= __( 'on each ', 'booking' ) . ' <strong>' . $result . '</strong> ';
            }
        }
                                                                                // Monthes
        $cnt = 0;
        $result = '';
        foreach ( $filter['monthes'] as $key => $value ) {
            if ( $value == 'On' ) {
                if ( $result !== '' )
                    $result .=', ';
                $result .= $monthes[$key];
                ;
                $cnt++;
            }
        }
        if ( ($result !== '' ) || ($cnt == 0) ) {
            if ( $cnt == 12 )
                $description .= __( 'of every month ', 'booking' );
            elseif ( $cnt == 0 )
                return '<span style="color:#ff0000;font-weight:600;">' . __( 'No days', 'booking' ) . '</span>';
            else
                $description .= __( 'of', 'booking' ) . ' <strong>' . $result . '</strong> ';
        }
                                                                                // Years
        $cnt = 0;
        $result = '';
        foreach ( $filter['year'] as $key => $value ) {
            if ( $value == 'On' ) {
                if ( $result !== '' )
                    $result .=', ';
                $result .= $key;
                $cnt++;
            }
        }
        
        if ( ($result !== '' ) || ($cnt == 0) ) {
            if ( $cnt == 0 )
                return '<span style="color:#ff0000;font-weight:600;">' . __( 'No days', 'booking' ) . '</span>';
            else
                $description .= ' <strong>' . $result . '</strong>';
        }
    }
    return $description;
}