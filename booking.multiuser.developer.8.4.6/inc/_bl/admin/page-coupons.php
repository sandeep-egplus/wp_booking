<?php /**
 * @version 1.0
 * @package Booking > Resources > Coupons page 
 * @category Settings page 
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2016-09-26
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
class WPBC_Page_Settings__discountcoupons extends WPBC_Page_Structure {

    const SUBMIT_FORM = 'wpbc_discountcoupons';                                   // Main Form Name
    const ACTION_FORM = 'wpbc_action_discountcoupons';                            // Form for sub-actions: like Add New | Edit actions
    
    private $html_prefix = 'dcoupons_';
    private $edit_item_id = 0;


    public function in_page() {
        return 'wpbc-resources';
    }
    

    public function tabs() {
        
        $tabs = array();
        
        $tabs[ 'coupons' ] = array(
                              'title'       => __('Coupons','booking')            // Title of TAB    
                            , 'hint'        => __('Setting coupons for discount', 'booking')                      // Hint    
                            , 'page_title'  => __('Coupons' ,'booking') . ' ' . __('Settings' ,'booking')    // Title of Page   
                            //, 'link'      => ''                               // Can be skiped,  then generated link based on Page and Tab tags. Or can  be extenral link
                            //, 'position'  => 'left'                           // 'left'  ||  'right'  ||  ''
                            //, 'css_classes'=> ''                              // CSS class(es)
                            //, 'icon'      => ''                               // Icon - link to the real PNG img
                            , 'font_icon' => 'glyphicon glyphicon-tags'       // CSS definition  of forn Icon
                            , 'default'   => false                              // Is this tab activated by default or not: true || false. 
                            //, 'disabled'  => false                            // Is this tab disbaled: true || false. 
                            //, 'hided'     => false                            // Is this tab hided: true || false. 
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

        do_action( 'wpbc_hook_settings_page_header', 'discountcoupons');              // Define Notices Section and show some static messages, if needed
        
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

            //                                                                              <editor-fold   defaultstate="collapsed"   desc=" T o o l b a r " >    
            ////////////////////////////////////////////////////////////////////
            //  T o o l b a r
            ////////////////////////////////////////////////////////////////////
        
            wpbc_clear_div();

            ?><div id="toolbar_seasonfilters" style="position:relative;"><?php

                wpbc_bs_toolbar_sub_html_container_start();

                ?><div id="seasonfilters_toolbar_container" class="visibility_container clearfix-height" style="display:block;margin-top:-5px;"><?php 

                    ?><div class="control-group wpbc-no-padding" style="margin:8px 15px 0 0;"><?php 

                        ?><a href="javascript:void(0);" 
                            onclick="javascript:jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('add_new_coupon');jQuery('#<?php echo self::ACTION_FORM; ?>').submit();"
                            style="margin:0 15px 0 0;"
                             class="button tooltip_top" data-original-title="<?php _e('Create dates filter' , 'booking'); ?>">
                             <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>&nbsp;<span class="in-button-text"><?php 
                                    _e('Add New Discount Coupon', 'booking');
                        ?></span></a><?php    

                    ?></div><?php 

                    wpbc_clear_div();
                   // wpbc_toolbar_expand_collapse_btn( 'advanced_booking_filter' );   

                ?></div><?php

                wpbc_bs_toolbar_sub_html_container_end();       

            ?></div><?php

            wpbc_clear_div();
            
            //                                                                              </editor-fold>

        echo '</span>';

        ?><div class="clear" style="margin-bottom:20px;"></div><?php
        
        // Scroll links ////////////////////////////////////////////////////////
        
        wpbc_toolbar_search_by_id__top_form( array( 
                                                    'search_form_id' => 'wpbc_discountcoupons_search_form'
                                                  , 'search_get_key' => 'wh_search_id'
                                                  , 'is_pseudo'      => false
                                            ) );
        
        ////////////////////////////////////////////////////////////////////////
        // Content
        ////////////////////////////////////////////////////////////////////////
        ?>
        <div class="clear" style="margin-bottom:0px;"></div>
        <span class="metabox-holder"><?php  
        
            ////////////////////////////////////////////////////////////////////
            // Sub Actions Form
            ////////////////////////////////////////////////////////////////////     
            ?><form  name="<?php echo self::ACTION_FORM; ?>" id="<?php echo self::ACTION_FORM; ?>" action="<?php 
            
                    // Need to  exclude 'edit_item_id' parameter from  $_GET,  if we was using direct link for ediing,  in case for edit other season filters....
                    $exclude_params = array( 'edit_item_id' );
                    $only_these_parameters = array( 'tab', 'page_num', 'wh_search_id' );
                    echo wpbc_get_params_in_url( wpbc_get_resources_url( false, false ), $exclude_params, $only_these_parameters );

                    ?>" method="post" autocomplete="off"><?php                           
               // N o n c e   field, and key for checking   S u b m i t 
               wp_nonce_field( 'wpbc_settings_page_' . self::ACTION_FORM );

		        // Add hidden input SEARCH KEY field into  main form, if previosly was searching by ID or Title
		        wpbc_hidden_search_by_id_field_in_main_form( array( 'search_get_key'  => 'wh_search_id' ) );													//FixIn: 8.0.1.12


		        ?><input type="hidden" name="is_form_sbmitted_<?php echo self::ACTION_FORM; ?>" id="is_form_sbmitted_<?php echo self::ACTION_FORM; ?>" value="1" /><?php
            
                ?><input type="hidden" name="action_<?php echo self::ACTION_FORM; ?>"    id="action_<?php echo self::ACTION_FORM; ?>"    value="-1" /><?php                 
                ?><input type="hidden" name="edit_item_id_<?php echo self::ACTION_FORM; ?>" id="edit_item_id_<?php echo self::ACTION_FORM; ?>" value="-1" /><?php                 

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
					wpbc_hidden_search_by_id_field_in_main_form( array( 'search_get_key'  => 'wh_search_id' ) );													//FixIn: 8.0.1.12

                ?><input type="hidden" name="is_form_sbmitted_<?php echo self::SUBMIT_FORM; ?>" id="is_form_sbmitted_<?php echo self::SUBMIT_FORM; ?>" value="1" /><?php                 
                ?><div class="clear" style="margin-top:20px;"></div>
                <div id="wpbc_<?php echo $this->html_prefix; ?>table" class="wpbc_settings_row wpbc_settings_row_rightNO"><?php 
                
                    // wpbc_open_meta_box_section( 'wpbc_settings_discountcoupons', __('Resources', 'booking') );
                        
                    $this->wpbc_discountcoupons_table__show();
                        
                    // wpbc_close_meta_box_section();
                ?>
                </div>
                <div class="clear"></div>                
                <select id="bulk-action-selector-bottom" name="bulk-action">
                    <option value="-1"><?php _e('Bulk Actions', 'booking'); ?></option>
                    <option value="edit"><?php _e('Edit', 'booking'); ?></option>
                    <option value="delete"><?php _e('Delete', 'booking'); ?></option>
                </select>    
                
                <a href="javascript:void(0);" onclick="javascript: jQuery('#<?php echo self::SUBMIT_FORM; ?>').submit();"
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
                        jQuery('#<?php echo self::SUBMIT_FORM; ?>').submit(); 
                    }
                } ); 
            </script>
        </span>
        <?php       
    
        do_action( 'wpbc_hook_settings_page_footer', 'discountcoupons' );
        
        $this->enqueue_js();
    }

    

    /** Save Chanages  - to Multiple Items */  
    public function update() {
        // if (  ( wpbc_is_this_demo() ) ||  ( ! class_exists( 'wpdev_bk_personal' ) )  ) return;

        global $wpdb;

        $wpbc_dc_table = new WPBC_DC_Table( 'discountcoupons_submit' );
        $linear_discountcoupons_for_one_page = $wpbc_dc_table->get_linear_data_for_one_page();
                
        if ( isset( $_POST['bulk-action' ] ) )  
            $submit_action = $_POST['bulk-action' ];
        else                                    
            $submit_action = 'edit';
        
        $bulk_action_arr_id = array();

        foreach ( $linear_discountcoupons_for_one_page as $resource_id => $resource ) {
            
            if ( isset( $_POST[ $this->html_prefix . 'coupon_min_sum_' . $resource_id ] ) ) {      // Check posts of only visible on page booking discountcoupons 

                    switch ( $submit_action ) {

                        case 'delete':                                          // Delete

                            if ( isset( $_POST[ $this->html_prefix . 'select_' . $resource_id ] ) )
                                    $bulk_action_arr_id[] = intval( $resource_id );
                            break;

                        default:                                                // Edit

                            ////////////////////////////////////////////////////////////////
                            //  G  e t      V a l i d a  t e d     D a t a 
                            ////////////////////////////////////////////////////////////////                 
                            $validated_params = array();

                            // ID ( for editing only ),  otherwise it = '-1'
                            $validated_params[ 'id' ] = intval( $resource_id );

                            /*
                            // Code
                            $validated_params[ 'coupon_code' ] = WPBC_Settings_API::validate_text_post_static( $this->html_prefix . 'coupon_code_' . $resource_id );

                            // Saving
                            $validated_params[ 'coupon_value' ] = str_replace( ',', '.', $_POST[ $this->html_prefix . 'coupon_value_' . $resource_id ] );  // In case,  if someone was make mistake and use , instead of.
                            $validated_params[ 'coupon_value' ] = floatval( $validated_params[ 'coupon_value' ] );

                            // Saving type
                            if ( $_POST[ $this->html_prefix . 'coupon_type_' . $resource_id ] == 'fixed' ) 
                                $validated_params[ 'coupon_type' ] = 'fixed';
                            else 
                                $validated_params[ 'coupon_type' ] = '%';
                            */
                            
                            // Min sum
                            $validated_params[ 'coupon_min_sum' ] = str_replace( ',', '.', $_POST[ $this->html_prefix . 'coupon_min_sum_' . $resource_id ] );             // In case,  if someone was make mistake and use , instead of.
                            $validated_params[ 'coupon_min_sum' ] = floatval( $validated_params[ 'coupon_min_sum' ] );

                            
                            // Expiration
                            $expiration_y = intval( $_POST[ $this->html_prefix . 'expiration_y_' . $resource_id ] );
                            $expiration_m = intval( $_POST[ $this->html_prefix . 'expiration_m_' . $resource_id ] );
                            $expiration_d = intval( $_POST[ $this->html_prefix . 'expiration_d_' . $resource_id ] );
                            $expiration_date = mktime( 0, 0, 0, $expiration_m, $expiration_d, $expiration_y );                
                            $validated_params[ 'expiration_date' ] = date_i18n( 'Y-m-d H:i:s', $expiration_date );

                            // Number of usage
                            $validated_params[ 'coupon_active' ] = intval( $_POST[ $this->html_prefix . 'coupon_active_' . $resource_id ] );                            
//debuge($validated_params);
                            // Need this complex query  for ability to  define different paramaters in differnt versions.
                            $sql_arr = apply_filters(   'wpbc_discountcoupons_table__update_sql_array'
                                                                , array(
                                                                        'sql'       => array(
                                                                                              'start'   => "UPDATE {$wpdb->prefix}booking_coupons SET "
                                                                                            , 'params' => array( 
                                                                                                                   'coupon_active = %d'
                                                                                                                //  , 'coupon_code = %s'
                                                                                                                //  , 'coupon_value = %f'
                                                                                                                //  , 'coupon_type = %s'
                                                                                                                  , 'expiration_date = %s'
                                                                                                                  , 'coupon_min_sum = %f'
                                                                                                                //, 'support_bk_types = %s'                                                                                                                                                                                
                                                                                                                )                         
                                                                                            , 'end'    => " WHERE coupon_id = %d"
                                                                                        )
                                                                        , 'values'  => array( 
                                                                                                $validated_params[ 'coupon_active' ]
                                                                                            //    $validated_params[ 'coupon_code' ]
                                                                                            //  , $validated_params[ 'coupon_value' ]
                                                                                            //  , $validated_params[ 'coupon_type' ]
                                                                                              , $validated_params[ 'expiration_date' ]
                                                                                              , $validated_params[ 'coupon_min_sum' ]
                                                                                            //, ( $validated_params[ 'support_bk_types' ] == 'all' ? 'all' : ',' . $validated_params[ 'support_bk_types' ] . ',' )                                                           
                                                                                        )
                                                                    )
                                                                , $resource_id, $resource 
                                                );                
                            $sql_arr['values'][] = intval( $validated_params['id'] );              // last parameter  for " WHERE booking_type_id = %d "

                            $sql = $wpdb->prepare(    $sql_arr['sql']['start']                          // SQL
                                                        . implode( ',' , $sql_arr['sql']['params'] ) 
                                                        . $sql_arr['sql']['end']            
                                                    , $sql_arr[ 'values' ]                              // Array of validated parameters
                                                );                     

                            if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error saving to DB' ,__FILE__ , __LINE__); }         // Save to DB

// debuge( '$_POST, $sql,$resource, $validated_params', $_POST, $sql,  $resource, $validated_params );

                            wpbc_show_changes_saved_message();

                        break;
                    }
                
            }
        }


        if ( ! empty( $bulk_action_arr_id ) ) {
            
                    switch ( $submit_action ) {
                        
                        case 'delete':                                          // Delete
                            $bulk_action_arr_id = implode( ',', $bulk_action_arr_id );
                            $sql = "DELETE FROM {$wpdb->prefix}booking_coupons WHERE coupon_id IN ({$bulk_action_arr_id})";

                            if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error during deleting items in DB' ,__FILE__ , __LINE__); }         // Action inDB

                            wpbc_show_message ( __( 'Deleted', 'booking'), 5 );
                            
                        default:                                                // Edit
                            break;
                    }
        }
        
        make_bk_action( 'wpbc_reinit_discountcoupons_cache' );

        /**
        // Get Validated Email fields
        $validated_fields = $this->get_api()->validate_post();        
        $validated_fields = apply_filters( 'wpbc_fields_before_saving_to_db__discountcoupons', $validated_fields );   //Hook for validated fields.        
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
            .wpbc-table-2-columns1,
            .wpbc-table-2-columns2 {
                width:70%;
                float:left;
                clear:none;
            }
            .wpbc-table-2-columns2 {
                   width:30%;
            }
            td.wpbc_select_next_to_text select{
                height: 26px;                
                vertical-align: baseline;
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
            @media (max-width: 782px) {
                .wpbc-table-2-columns1,
                .wpbc-table-2-columns2 {
                    width:100%;
                    float:none;
                    clear:both;
                }                
                td.wpbc_select_next_to_text input[type="text"],
                td.wpbc_select_next_to_text select{
                    float:left;
                    width:48% !important;
                }
                td.wpbc_select_next_to_text span.description {
                    clear:both;
                    display:block;
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
                                                                                // on checkbox click
        // $js_script .= " jQuery('.check-column input[type=\'checkbox\']').on( 'change', function(){    
        //                        jQuery( '.check-column input[type=\'checkbox\']' ).parent().parent().removeClass('wpbc_checkbox_selected');
        //                        jQuery( '.check-column input[type=\'checkbox\']:checked' ).parent().parent().addClass('wpbc_checkbox_selected');
        //                    } ); ";   
        
        wpbc_enqueue_js( $js_script );                                          // Eneque JS to  the footer of the page         
    }

    // </editor-fold>
    

    //                                                                          <editor-fold   defaultstate="collapsed"   desc=" C o u p o n s      T a b l e  " >    
    
    /** Show booking discountcoupons table */
    public function wpbc_discountcoupons_table__show() {
        
        // echo ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() . '<div class="clear" style="height:20px;"></div>' : '' );
        
        $columns = array();
        $columns[ 'check' ] = array(    'title' => '<input type="checkbox" value="" id="' . $this->html_prefix . 'select_all" name="' . $this->html_prefix . 'select_id_all" />'
                                      , 'class' => 'check-column' 
                                    );
        $columns[ 'id' ] = array(         'title' => __( 'ID' )
                                        , 'style' => 'width:5em;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'sortable' => true 
                                    );
        
        $columns = apply_filters ('wpbc_discountcoupons_table_header__before_coupon_code' , $columns );
        
        
        $columns[ 'coupon_code' ] = array(
                                          'title' => __( 'Coupon Code', 'booking' )
                                        , 'style' => ''
                                        , 'sortable' => true 
                                    );
        
        $columns[ 'coupon_value' ] = array(      
                                          'title' => __( 'Savings', 'booking' )
                                        , 'style' => 'width:6em;text-align:center;'
                                        , 'sortable' => true 
                                    );
                
        $columns[ 'coupon_min_sum' ] = array(      
                                          'title' => __( 'Minimum Cost', 'booking' )
                                        , 'style' =>  'width:10em;text-align:center;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'sortable' => true 
                                    );

        $columns[ 'expiration_date' ] = array(      
                                          'title' => __( 'Expiration', 'booking' ) . ' <span style="font-size:0.9em;">(' . strtolower( __( 'Year', 'booking' ) ) 
                                                                                          . '/' . strtolower( __('Month', 'booking' ) )  
                                                                                          . '/' . strtolower( __('Day', 'booking' ) )  . ')</span>'
                                        , 'style' =>  'width:17em;text-align:center;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'sortable' => true 
                                    );

        $columns[ 'coupon_active' ] = array(
                                          'title' => __( 'Number of usage', 'booking' )
                                        , 'style' => 'width:11em;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'sortable' => true 
                                    );

        $columns[ 'support_bk_types' ] = array(
                                          'title' => __( 'Resources', 'booking' )
                                        , 'style' => 'width:12em;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'sortable' => true 
                                    );
        
        $columns = apply_filters( 'wpbc_discountcoupons_table_header__user_title' , $columns );        

        $columns[ 'actions' ] = array(    'title' => __( 'Actions', 'booking' )
                                        , 'style' => 'width:7em;text-align:center;'
                                    );
        
        $columns = apply_filters( 'wpbc_discountcoupons_table_header__last' , $columns );        
        
        $wpbc_dc_table = new WPBC_DC_Table( 
                            'discountcoupons' 
                            , array(
                                  'url_sufix'   =>  '#wpbc_' . $this->html_prefix . 'table'  // Link to  scroll
                                , 'rows_func'   =>  array( $this, 'wpbc_discountcoupons_table__show_rows' ) 
                                , 'columns'     =>  $columns
                                , 'is_show_pseudo_search_form' => false
                            )
                        );

        $wpbc_dc_table->display();             
    }   
    

    /**
	 * Show rows for booking resource table
     * 
     * @param int $row_num
     * @param array $resource
     */
    public function wpbc_discountcoupons_table__show_rows( $row_num, $resource ) {
        
        $css_class = ' wpbc_discountcoupons_row';
                
        if ( $this->edit_item_id == $resource['id'] ) {                         // Edit only 1 row per time
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
                    <?php do_action( 'wpbc_discountcoupons_table_show_col__before_coupon_code', $row_num, $resource ); ?>
                    <td style="text-align:left;">
                        <span class="wpdevelop"><span class="label label-default label-info" style="font-size:0.9em;background-color: #39d;"><?php echo esc_attr( $resource['coupon_code'] ); ?></span></span>
                        <?php 
                                /* Text  field
                                <input type="text" 
                                       value="<?php echo esc_attr( $resource['coupon_code'] ); ?>" 
                                       id="<?php echo $this->html_prefix . 'coupon_code_' . $resource['id' ]; ?>" 
                                       name="<?php echo $this->html_prefix . 'coupon_code_' . $resource['id' ]; ?>" 
                                       class="large-text" 
                                />
                                 */                        
                        ?>
                    </td>
                    <td style="text-align:left;"><?php 
                    
                        $coupon_value = str_replace( ',', '.', $resource['coupon_value']  );                            // In case,  if someone was make mistake and use , instead of .
                        $coupon_value = floatval( $coupon_value );
                            
                        $booking_resource_id = 0;                               // if 0 then  get  currency just  of current user.
                        $currency = wpbc_get_currency_symbol_for_user( $booking_resource_id );                            

                        /*  Text  and Select  field!
                        if( 0 ) {
                            ?><div class="field-currency" style="<?php if ($resource['coupon_type'] != 'fixed' ) { echo 'visibility: hidden;'; } ?>"><?php echo $currency; ?></div><?php    
                        }
                        ?><input type="text" 
                               value="<?php echo $coupon_value; ?>" 
                               id="<?php echo $this->html_prefix . 'coupon_value_' . $resource['id' ]; ?>" 
                               name="<?php echo $this->html_prefix . 'coupon_value_' . $resource['id' ]; ?>" 
                               style="margin-right: 4px;width: 55%;float:left;" 
                        /><?php 
                        
                        WPBC_Settings_API::field_select_row_static(                                              
                                                                    $this->html_prefix . 'coupon_type_' . $resource['id' ]
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'float:left;width:40%;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                                                                    
                                                    , 'value'             => (  ( isset( $resource['coupon_type'] ) ) ? $resource['coupon_type'] : '%'  )
                                                    , 'options'           => array(
                                                                                    'fixed' => $currency
                                                                                  , '%'     => '%'
                                                                                )
                                                )
                                    );                                          
                        if ( 0 ) {
                            ?><div class="field-currency" style="<?php if ($resource['coupon_type'] != '%' ) { echo 'visibility: hidden;'; } ?>">%</div><?php                             
                        }
                        */                        
                                                
                        ?><div class="field-currency"><?php
                        if( $resource['coupon_type'] === '%' ) {                            
                              echo $coupon_value . ' %';                             
                        } else {
                            echo wpbc_get_cost_with_currency_for_user( $coupon_value , $booking_resource_id );
                        }
                        ?></div><?php
                        
                    ?></td>
                    <td style="text-align:center;" class="wpbc_hide_mobile"><?php 
                        $coupon_min_sum = str_replace( ',', '.', $resource['coupon_min_sum']  );                            // In case,  if someone was make mistake and use , instead of .
                        $coupon_min_sum = floatval( $coupon_min_sum );
                    
                        ?><div class="field-currency"><?php echo $currency; ?></div><?php
                        ?><input type="text" 
                               value="<?php echo $coupon_min_sum; ?>" 
                               id="<?php echo $this->html_prefix . 'coupon_min_sum_' . $resource['id' ]; ?>" 
                               name="<?php echo $this->html_prefix . 'coupon_min_sum_' . $resource['id' ]; ?>" 
                               style="margin-left: 10px;width: 50%;" 
                        /><?php                         
                    ?></td>
                    <td style="text-align:center;" class="wpbc_hide_mobile"><?php 
                        /* 
                            $date_format = get_bk_option( 'booking_date_format');
                            $expiration_date  = date_i18n( $date_format, strtotime( $resource['expiration_date' ] ) );
                            echo  $expiration_date; 
                        */
                        $item_arr = array();
                        $item_arr['expiration_y'] = intval( substr( $resource['expiration_date'], 0, 4 ) );
                        $item_arr['expiration_m'] = intval( substr( $resource['expiration_date'], 5, 2 ) );
                        $item_arr['expiration_d'] = intval( substr( $resource['expiration_date'], 8, 2 ) );
                                                
                        WPBC_Settings_API::field_select_row_static(                                              
                                                      $this->html_prefix . 'expiration_y_' . $resource['id']
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:5em;margin:2px 1px;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             => $item_arr['expiration_y']
                                                    , 'options'           => array_combine( range( ( date('Y') - 1 ), ( date('Y') + 10 ) ), range( ( date('Y') - 1 ), ( date('Y') + 10 ) )  )
                                                )
                                    );   
                    //echo '<span style="font-weight:600;"> / </span>';
                    WPBC_Settings_API::field_select_row_static(                                              
                                                      $this->html_prefix . 'expiration_m_' . $resource['id']
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:4em;margin:2px 1px;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             => $item_arr['expiration_m']
                                                    , 'options'           => array_combine( range( 1, 12 ), range( 1, 12 ) )
                                                )
                                    );           
                    //echo '<span style="font-weight:600;"> / </span>';
                    WPBC_Settings_API::field_select_row_static(                                              
                                                      $this->html_prefix . 'expiration_d_' . $resource['id']
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:4em;margin:2px 1px;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             => $item_arr['expiration_d']
                                                    , 'options'           => array_combine( range( 1, 31 ), range( 1, 31 ) )
                                                )
                                    );                                          
                    ?></td>
                    <td class="wpbc_hide_mobile" style="text-align:center;">
                        <input type="text" 
                               value="<?php echo intval( $resource['coupon_active'] ); ?>" 
                               id="<?php echo $this->html_prefix . 'coupon_active_' . $resource['id' ]; ?>" 
                               name="<?php echo $this->html_prefix . 'coupon_active_' . $resource['id' ]; ?>" 
                               style="width: 4em;" 
                        />                        
                    </td>
                    <td class="wpbc_hide_mobile"><?php 

                            $resource_titles_text = array();

                            $resources = explode( ',', $resource['support_bk_types' ] ); 
                            if ( $resources[0] == 'all' ) 
                                $resource_titles_text[] = '<span class="label label-default label-warning" style="font-size:1em;" >' .  __('All resources' ,'booking') . '</span>';
                            else {

                                $wpbc_br_cache = wpbc_br_cache();

                                foreach ( $resources as $resource_id ) {

                                    $title_res = $wpbc_br_cache->get_resource_attr( $resource_id, 'title');
                                    if ( ! empty( $title_res ) ) {
                                        $title_res =  apply_bk_filter('wpdev_check_for_active_language', $title_res );

                                        $resource_titles_text[] = '<span class="label label-default label-info" >' . $title_res . '</span>';
                                    }
                                }                                     
                            }
                            $resource_titles_text = '<span class="wpdevelop">' . implode(' ', $resource_titles_text ) . '</span>';

                            echo $resource_titles_text;
                            
                    ?></td>
                    <?php do_action( 'wpbc_discountcoupons_table_show_col__user_field',  $row_num, $resource ); ?>
                    <td style="text-align:center;">
                        <a  
                            href="javascript:void(0);" 
                            onclick="javascript:jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('edit_this_item');
                                                jQuery('#edit_item_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                                jQuery('#<?php echo self::ACTION_FORM; ?>').submit();"
                            class="tooltip_top button-secondary button" 
                            title="<?php _e('Edit' ,'booking'); ?>"
                        ><i class="glyphicon glyphicon-edit"></i></a>
                        <a  
                            href="javascript:void(0);" 
                            onclick="javascript:if ( wpbc_are_you_sure('<?php echo esc_js(__('Do you really want to do this ?' ,'booking')); ?>') ) {
                                        jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('delete_this_item');
                                        jQuery('#edit_item_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $resource['id' ]; ?>');
                                        jQuery('#<?php echo self::ACTION_FORM; ?>').submit();
                                    }"
                            class="tooltip_top button-secondary button" 
                            title="<?php _e('Completely Delete' ,'booking'); ?>"                        
                        ><i class="glyphicon glyphicon-remove"></i></a>
                    </td>
                    <?php do_action( 'wpbc_discountcoupons_table_show_col__last',         $row_num, $resource ); ?>
        </tr>
        <?php    
    }
    
    //                                                                          </editor-fold>
        
    
    //                                                                              <editor-fold   defaultstate="collapsed"   desc=" A c t i o n  s    F o r m " >    
    
    /**
	 * Check  ACTION_FORM for Single Item :  Add New | Edit | Delete                - Here we can  EDIT only  ONE season  per action
     * 
     * @return boolean  - false, if nothing done here
     */
    public function wpbc_check_sub_actions(){

        global $wpdb;
        
        $html_prefix = 'sfd_';

        if ( isset( $_GET['edit_item_id'] ) ) {                                 // In case if we need to  open  direct  link for editing this item
            
            $action     = 'edit_this_item';
            $edit_item_id  = intval( $_GET['edit_item_id'] );
            
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

                if ( isset( $_POST[ 'edit_item_id_' .  self::ACTION_FORM ] ) ) 
                    $edit_item_id = intval( $_POST[ 'edit_item_id_' .  self::ACTION_FORM ] );
        }
        ////////////////////////////////////////////////////////////////////////

        
        switch ( $action ) {
            
            case 'add_new_coupon':                                              //  Add New - Coupon

                wpbc_open_meta_box_section( $action . '_new', __('Add New Discount Coupon', 'booking')  );  
                    $this->add_new_coupon();
                wpbc_close_meta_box_section();
                break;
            
            case 'insert_sql_coupon' :
            case 'update_sql_coupon':                                           // Update SQL - Conditional Dates
      
                ////////////////////////////////////////////////////////////////
                //  G  e t      V a l i d a  t e d     D a t a 
                ////////////////////////////////////////////////////////////////                 
                $validated_params = array();
                
                // ID ( for editing only ),  otherwise it = '-1'
                $validated_params[ 'id' ] = $edit_item_id;
                
                // Code
                $validated_params[ 'coupon_code' ] = WPBC_Settings_API::validate_text_post_static( $html_prefix . 'coupon_code' );
				$validated_params[ 'coupon_code' ] = strtolower( $validated_params[ 'coupon_code' ] );					//FixIn: 7.2.1.3
				$validated_params[ 'coupon_code' ] = str_replace('%', '', $validated_params[ 'coupon_code' ] );
                
                // Saving
                $validated_params[ 'coupon_value' ] = str_replace( ',', '.', $_POST[ $html_prefix . 'coupon_value' ] );             // In case,  if someone was make mistake and use , instead of.
                $validated_params[ 'coupon_value' ] = floatval( $validated_params[ 'coupon_value' ] );
                
                // Saving type
                if ( $_POST[ $html_prefix . 'coupon_type' ] == 'fixed' ) 
                    $validated_params[ 'coupon_type' ] = 'fixed';
                else 
                    $validated_params[ 'coupon_type' ] = '%';
                
                // Expiration
                $expiration_y = intval( $_POST[ $html_prefix . 'expiration_y' ] );
                $expiration_m = intval( $_POST[ $html_prefix . 'expiration_m' ] );
                $expiration_d = intval( $_POST[ $html_prefix . 'expiration_d' ] );
                $expiration_date = mktime( 0, 0, 0, $expiration_m, $expiration_d, $expiration_y );                
                $validated_params[ 'expiration_date' ] = date_i18n( 'Y-m-d H:i:s', $expiration_date );
                
                // Min sum
                $validated_params[ 'coupon_min_sum' ] = str_replace( ',', '.', $_POST[ $html_prefix . 'coupon_min_sum' ] );             // In case,  if someone was make mistake and use , instead of.
                $validated_params[ 'coupon_min_sum' ] = floatval( $validated_params[ 'coupon_min_sum' ] );
                
                // Number of usage
                $validated_params[ 'coupon_active' ] = intval( $_POST[ $html_prefix . 'coupon_active' ] );
                
                // Resources                 
                $res_id_arr = array();
                if ( is_array(  $_POST[ $html_prefix . 'support_bk_types' ] ) ) {                    
                    foreach ( $_POST[ $html_prefix . 'support_bk_types' ] as $res_id ) {
                        if ( $res_id !== 'all' )
                            $res_id_arr[] = intval( $res_id );
                        else {
                            $res_id_arr = array();                              // Exist loop,  if exist  'all'
                            break;
                        }
                    }
                }

                if ( ! empty( $res_id_arr ) ) {
                    $validated_params[ 'support_bk_types' ] = implode( ',', $res_id_arr );
                } else 
                    $validated_params[ 'support_bk_types' ] = 'all';

                // If "regular user" user,  then  instead of 'all' value need to  get CSV booking resources: '78,79,90'
                if ( $validated_params[ 'support_bk_types' ] == 'all' ) {
                    
                    $is_super = wpbc_is_mu_user_can_be_here( 'only_super_admin' );  
                    if ( ! $is_super ) {                                            

                        $wpbc_br_cache = wpbc_br_cache();

                        $resources_arr = $wpbc_br_cache->get_resources();
                        
                        $validated_params[ 'support_bk_types' ] = implode(',', array_keys( $resources_arr ) );
                    }
                }
                                                                                /* array( 
                                                                                            [id] => -1
                                                                                            [coupon_code] => 'fs"%^&*@#!~\?/
                                                                                            [coupon_value] => 11.2222
                                                                                            [coupon_type] => %
                                                                                            [expiration_date] => 2016-09-25 00:00:00
                                                                                            [coupon_min_sum] => 33.4444
                                                                                            [coupon_active] => 44
                                                                                            [support_bk_types] => 83,1
                                                                                ) */
//debuge( $validated_params );        

                ////////////////////////////////////////////////////////////////
                // S Q L
                //////////////////////////////////////////////////////////////// 

                if ( $action == 'insert_sql_coupon' ) { 
                    $sql = $this->get_insert_sql( $validated_params );      // SQL INSERT
                }                
                if ( $action == 'update_sql_coupon' ) {                     
                    $sql = $this->get_update_sql( $validated_params );      // SQL UPDATE
                }
//debuge($sql);

                if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error saving to DB' ,__FILE__ , __LINE__); }         // Save to DB

                wpbc_show_changes_saved_message();   

                make_bk_action( 'wpbc_reinit_discountcoupons_cache' );
                break;                
            

            ////////////////////////////////////////////////////////////////////
            // Edit Visual Section
            ////////////////////////////////////////////////////////////////////
        
            case 'edit_this_item':                                              // Edit Section
                
                if ( $edit_item_id > 0 ) {
                    
                    $wpbc_dc_cache = wpbc_dc_cache();
                    
                    $item_arr = $wpbc_dc_cache->get_resource_attr( $edit_item_id );

                    if ( ! empty( $item_arr ) ) {

                        wpbc_open_meta_box_section( $action . '_new', __('Edit', 'booking') . ' ' . __('Coupon', 'booking') . ' [ ID : ' . $edit_item_id . ' ]'  );  
                            
                            // Transform Expiration  date '2017-09-26 00:00:00'
                            $item_arr['expiration_y'] = intval( substr( $item_arr['expiration_date'], 0, 4 ) );
                            $item_arr['expiration_m'] = intval( substr( $item_arr['expiration_date'], 5, 2 ) );
                            $item_arr['expiration_d'] = intval( substr( $item_arr['expiration_date'], 8, 2 ) );
                        
                            $this->add_new_coupon( $item_arr );
                        wpbc_close_meta_box_section();

                    } else wpbc_show_message_in_settings( __( 'Nothing Found', 'booking' ) . '.', 'warning', __('Error' ,'booking') . '.' );

                } else wpbc_show_message_in_settings( __( 'Nothing Found', 'booking' ) . '.', 'warning', __('Error' ,'booking') . '.' );
                
                break;
            
            ////////////////////////////////////////////////////////////////////
            // SQL Delete
            ////////////////////////////////////////////////////////////////////
          
            case 'delete_this_item':                                        // Delete

                if ( $edit_item_id <= 0 ) return false;
           
                    $sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}booking_coupons WHERE coupon_id = %d" , $edit_item_id ) ;

                    if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error during deleting items in DB' ,__FILE__ , __LINE__); }         // Action inDB
                    
                    make_bk_action( 'wpbc_reinit_discountcoupons_cache' );

                    wpbc_show_message ( __( 'Deleted', 'booking'), 5 );

                break;
                
            default:
                return false;
                break;
        }
        
        return true;
    }
    
    
            /**
	 * Get SQL  for inserting
             * 
             * @param string $validated_title - name of filter - title
             * @param string $ser_filter  - serilized filter
             * @return string           - SQL
             */
            private function get_insert_sql( $validated_params ) {
                    
                    global $wpdb;
                
                    // Need this complex query  for ability to  define different paramaters in differnt versions.
                    $sql_arr = apply_filters(   'wpbc_discountcoupons_table__add_new_sql_array'
                                                        , array(
                                                                'sql'       => array(
                                                                                      'start'      => "INSERT INTO {$wpdb->prefix}booking_coupons "
                                                                                    , 'params'     => array( 
                                                                                                            'coupon_active'
                                                                                                          , 'coupon_code'
                                                                                                          , 'coupon_value'
                                                                                                          , 'coupon_type'
                                                                                                          , 'expiration_date'
                                                                                                          , 'coupon_min_sum'
                                                                                                          , 'support_bk_types'                                                                                                                                                                                
                                                                                                        )    
                                                                                    , 'param_types' => array(
                                                                                                              '%d'
                                                                                                            , '%s'
                                                                                                            , '%f'
                                                                                                            , '%s'
                                                                                                            , '%s'
                                                                                                            , '%f'
                                                                                                            , '%s'                                                                                                                                                                                
                                                                                                        )    
                                                                            )
                                                                , 'values'  => array( 
                                                                                      $validated_params[ 'coupon_active' ]
                                                                                    , $validated_params[ 'coupon_code' ]
                                                                                    , $validated_params[ 'coupon_value' ]
                                                                                    , $validated_params[ 'coupon_type' ]
                                                                                    , $validated_params[ 'expiration_date' ]
                                                                                    , $validated_params[ 'coupon_min_sum' ]
                                                                                    , ( $validated_params[ 'support_bk_types' ] == 'all' ? 'all' : ',' . $validated_params[ 'support_bk_types' ] . ',' )
                                                                                )
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
             * @param int $edit_item_id - ID of element
             * @return string           - SQL
             * 
             */
            private function get_update_sql( $validated_params ) {

                    global $wpdb;
                    
                    // We no need to update it with  Filters, because we can  change User only in TABLE,  so here we do not update User Collumn
                    $sql_arr = apply_filters(   'wpbc_discountcoupons_table__single_update_sql_array'
                                                        , array(
                                                                'sql'       => array(
                                                                                      'start'   => "UPDATE {$wpdb->prefix}booking_coupons SET "
                                                                                    , 'params' => array( 
                                                                                                            'coupon_active = %d'
                                                                                                          , 'coupon_code = %s'
                                                                                                          , 'coupon_value = %f'
                                                                                                          , 'coupon_type = %s'
                                                                                                          , 'expiration_date = %s'
                                                                                                          , 'coupon_min_sum = %f'
                                                                                                          , 'support_bk_types = %s'                                                                                                                                                                                
                                                                                                        )                         
                                                                                    , 'end'    => " WHERE coupon_id = %d"
                                                                                )
                                                                , 'values'  => array( 
                                                                                      $validated_params[ 'coupon_active' ]
                                                                                    , $validated_params[ 'coupon_code' ]
                                                                                    , $validated_params[ 'coupon_value' ]
                                                                                    , $validated_params[ 'coupon_type' ]
                                                                                    , $validated_params[ 'expiration_date' ]
                                                                                    , $validated_params[ 'coupon_min_sum' ]
                                                                                    , ( $validated_params[ 'support_bk_types' ] == 'all' ? 'all' : ',' . $validated_params[ 'support_bk_types' ] . ',' )                                                           
                                                                                )
                                                            )
                                        );                
                    $sql_arr['values'][] = intval( $validated_params['id'] );              // last parameter  for " WHERE booking_type_id = %d "

                    $sql = $wpdb->prepare(    $sql_arr['sql']['start']                          // SQL
                                                . implode( ',' , $sql_arr['sql']['params'] ) 
                                                . $sql_arr['sql']['end']            
                                            , $sql_arr[ 'values' ]                              // Array of validated parameters
                                        );                     
                    return $sql;
            }
            

    
    /**
	 * show "Add New Coupon" form
     * 
     * @param array $item_arr - if editing,  then transfer here this element 
     */
    public function add_new_coupon( $item_arr = array() ) {
        
        $booking_resource_id = 0;                                               // if 0 then  get  currency just  of current user.
        $currency = wpbc_get_currency_symbol_for_user( $booking_resource_id );                            
        
        if ( ! empty( $item_arr ) ) $is_edit = true;
        else                        $is_edit = false;

        if ( $is_edit )            
            $this->edit_item_id = $item_arr['id'];
        
        $html_prefix = 'sfd_';
        
        ?><div class="wpbc_season_filter__range_days"><?php 
            // Name
            ?><table class="form-table wpbc-table-2-columns1"><tbody><?php   

                WPBC_Settings_API::field_text_row_static(                                              
                                                          $html_prefix . 'coupon_code'
                                                , array(  
                                                          'type'              => 'text'
                                                        , 'title'             => __('Coupon Code', 'booking')
                                                        , 'description'       => __('Enter coupon code.', 'booking')
                                                        , 'placeholder'       => ''
                                                        , 'description_tag'   => 'span'
                                                        , 'tr_class'          => ''
                                                        , 'class'             => ''
                                                        , 'css'               => 'width:15em;'
                                                        , 'only_field'        => false
                                                        , 'attr'              => array()                                                    
                                                        , 'validate_as'       => array( 'required' )
                                                        , 'value'             => ( $is_edit ) ? $item_arr['coupon_code'] : ''
                                                    )
                                        );    
                
            ?><tr valign="top" >
                <th scope="row" style="vertical-align: middle;"><label for="<?php echo $html_prefix; ?>coupon_value" class="wpbc-form-text"><?php  _e('Savings', 'booking'); ?></label></th>                
                <td class="wpbc_select_next_to_text"><fieldset><?php 
                
                    WPBC_Settings_API::field_text_row_static(                                              
                                                      $html_prefix . 'coupon_value'
                                            , array(  
                                                      'type'              => 'text'
                                                    , 'title'             => __('Savings', 'booking')
                                                    , 'description'       => ''
                                                    , 'placeholder'       => ''
                                                    , 'description_tag'   => 'span'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'margin-right: 9px;width: 10em;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                    //, 'validate_as'       => array( 'required' )
                                                    , 'value'             =>  ( $is_edit ) ? $item_arr['coupon_value'] : '10'
                                                )
                                    );                             
                    WPBC_Settings_API::field_select_row_static(                                              
                                                      $html_prefix . 'coupon_type'
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:4em;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             =>  ( $is_edit ) ? $item_arr['coupon_type'] : '%'
                                                    , 'options'           => array(
                                                                                    'fixed' => $currency
                                                                                  , '%'     => '%'
                                                                                )
                                                )
                                    );                  
                    ?><span class="description"> <?php _e('Enter number of fixed or percentage savings.', 'booking') ?></span></fieldset></td>
            </tr><?php 
            ?><tr valign="top" >
                <th scope="row" style="vertical-align: middle;"><label for="<?php echo $html_prefix; ?>expiration_y" class="wpbc-form-text"><?php  _e('Expiration Date', 'booking'); ?></label></th>                
                <td class=""><fieldset><?php 
                    WPBC_Settings_API::field_select_row_static(                                              
                                                      $html_prefix . 'expiration_y'
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:5em;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             => ( $is_edit ) ? $item_arr['expiration_y'] : date('Y') + 1
                                                    , 'options'           => array_combine( range( ( date('Y') - 1 ), ( date('Y') + 10 ) ), range( ( date('Y') - 1 ), ( date('Y') + 10 ) )  )
                                                )
                                    );   
                    ?><span style="font-weight:600;"> / </span><?php
                    WPBC_Settings_API::field_select_row_static(                                              
                                                      $html_prefix . 'expiration_m'
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:4em;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             => ( $is_edit ) ? $item_arr['expiration_m'] : date('n')
                                                    , 'options'           => array_combine( range( 1, 12 ), range( 1, 12 ) )
                                                )
                                    );           
                    ?><span style="font-weight:600;"> / </span><?php
                    WPBC_Settings_API::field_select_row_static(                                              
                                                      $html_prefix . 'expiration_d'
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:4em;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             => ( $is_edit ) ? $item_arr['expiration_d'] : date('j')
                                                    , 'options'           => array_combine( range( 1, 31 ), range( 1, 31 ) )
                                                )
                                    );                  
                    ?><span class="description"> <?php _e('Select Expiration Date of the coupon.', 'booking') ?></span></fieldset></td>
            </tr><?php 

                WPBC_Settings_API::field_text_row_static(                                              
                                                          $html_prefix . 'coupon_min_sum'
                                                , array(  
                                                          'type'              => 'text'
                                                        , 'title'             => __('Minimum Booking Cost', 'booking')
                                                        , 'description'       => __('Enter minimum booking cost, when coupon is applicable.', 'booking')
                                                        , 'placeholder'       => ''
                                                        , 'description_tag'   => 'span'
                                                        , 'tr_class'          => ''
                                                        , 'class'             => ''
                                                        , 'css'               => 'width:15em;'
                                                        , 'only_field'        => false
                                                        , 'attr'              => array()                                                    
                                                        , 'validate_as'       => array( 'required' )
                                                        , 'value'             => ( $is_edit ) ? $item_arr['coupon_min_sum'] : '0'
                                                    )
                                        );    
                
                WPBC_Settings_API::field_text_row_static(                                              
                                                          $html_prefix . 'coupon_active'
                                                , array(  
                                                          'type'              => 'text'
                                                        , 'title'             => __('Maximum number of usage', 'booking')
                                                        , 'description'       => __('Enter maximum number of times, when coupon is applicable.', 'booking')
                                                        , 'placeholder'       => ''
                                                        , 'description_tag'   => 'span'
                                                        , 'tr_class'          => ''
                                                        , 'class'             => ''
                                                        , 'css'               => 'width:15em;'
                                                        , 'only_field'        => false
                                                        , 'attr'              => array()                                                    
                                                        , 'validate_as'       => array( 'required' )
                                                        , 'value'             => ( $is_edit ) ? $item_arr['coupon_active'] : '999'
                                                    )
                                        ); 
            ?></tbody></table><?php     
                
            ?><table class="form-table wpbc-table-2-columns2"><tbody><?php       
            ?><tr valign="top" >
                
                <td class="">
                    <div style="font-weight:600;margin-bottom: 10px;"><label for="<?php echo $html_prefix; ?>support_bk_types" class="wpbc-form-text" ><?php  _e('Resources', 'booking'); ?></label></div>
                    <fieldset><?php 
                
                        $this->show__multiple_resource_selection( $item_arr );
                        
                ?><p class="description"> <?php _e('Select booking resources, where is possible to apply this coupon code.', 'booking') ?></p></fieldset></td>
            </tr><?php 
            
            ?></tbody></table><?php                    
        
       

        ?><div class="clear" style="margin-top:20px;"></div><?php 

        ?><a href="javascript:void(0);" class="button button-primary"
           onclick="javascript: if ( jQuery('#<?php echo $html_prefix; ?>coupon_code').val() == '') { wpbc_field_highlight( '#<?php echo $html_prefix; ?>coupon_code' );  return false; }
                                <?php if ( $is_edit ) { ?> 
                                    jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('update_sql_coupon');
                                    jQuery('#edit_item_id_<?php echo self::ACTION_FORM; ?>').val('<?php echo $item_arr['id']; ?>');
                                <?php } else {?> 
                                    jQuery('#action_<?php echo self::ACTION_FORM; ?>').val('insert_sql_coupon');
                                <?php } ?>                    
                                jQuery(this).closest('form').submit();" 
            ><?php echo ( ( $is_edit ) ? __('Save Changes', 'booking') : __('Add New', 'booking') ); ?></a>
        </div><?php 
    }

      
            /** Selection of booking resources */
            function show__multiple_resource_selection( $item_arr ) {

                if ( ! empty( $item_arr ) ) $is_edit = true;
                else                        $is_edit = false;

                //$params = wp_parse_args( $params, $defaults );

                $resources_cache = wpbc_br_cache();                                     // Get booking resources from  cache        

                $resource_objects = $resources_cache->get_resources();                  // $resource_objects = $resources_cache->get_single_parent_resources();     // 'single_parent'

                $resource_options = array(); 
                $resource_options['all'] = array(     'title' => __( 'All resources', 'booking' )
                                                    , 'value' => 'all'
                                                    , 'attr'  => array( 'class' => 'wpbc_single_resource' )
                                                );

                foreach ( $resource_objects as $br) {

                    $br_option = array();

                    // Title
                    $br_option_title = apply_bk_filter('wpdev_check_for_active_language', $br['title'] );

                    if ( (isset( $br['parent'] )) && ($br['parent'] == 0 ) && (isset( $br['count'] )) && ($br['count'] > 1 ) )
                        $br_option_title .= ' [' . __('parent resource', 'booking') . ']';

                    // CLASS
                    $br_option['class'] = 'wpbc_single_resource';
                    if ( isset( $br['parent'] ) ) {
                        if ( $br['parent'] == 0 ) {
                            if (  ( isset( $br['count'] ) ) && ( $br['count'] > 1 )  )
                                $br_option['class'] = 'wpbc_parent_resource';
                        } else {
                            $br_option['class'] = 'wpbc_child_resource';
                        }
                    } 
                    
                    if ( $br_option['class'] === 'wpbc_child_resource' ) {
                        $br_option_title = ' &nbsp;&nbsp;&nbsp; ' . $br_option_title;
                    }
                    // Option
                    $resource_options[ $br['id'] ] = array(   'title' => $br_option_title
                                                            , 'value' => $br['id']
                                                            , 'attr'  => $br_option
                                                        );
                }

                // Show Selectbox
                $html_prefix = 'sfd_';
                WPBC_Settings_API::field_select_row_static(                                              
                                              $html_prefix . 'support_bk_types'
                                    , array(  
                                              'type'              => 'select'                                                
                                            , 'title'             => ''
                                            , 'label'             => ''
                                            , 'disabled'          => false
                                            , 'disabled_options'  => array()
                                            , 'multiple'          => true

                                            , 'description'       => ''
                                            , 'description_tag'   => 'span'

                                            , 'group'             => 'general'
                                            , 'tr_class'          => ''
                                            , 'class'             => ''
                                            , 'css'               => 'width:100%;height:11em;'
                                            , 'only_field'        => true
                                            , 'attr'              => array()                                                    

                                            , 'value'             => ( $is_edit ) ? explode( ',', $item_arr['support_bk_types'] ) : 'all'
                                            , 'options'           => $resource_options
                                        )
                            );                                          
            }
    
    //                                                                              </editor-fold>    
        
}
add_action('wpbc_menu_created', array( new WPBC_Page_Settings__discountcoupons() , '__construct') );    // Executed after creation of Menu