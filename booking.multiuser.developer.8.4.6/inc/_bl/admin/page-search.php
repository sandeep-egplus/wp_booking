<?php
/**
 * @version     1.0
 * @package     Booking > Settings > Search page - Saving search availability  form
 * @category    Settings API
 * @author      wpdevelop
 *
 * @web-site    https://wpbookingcalendar.com/
 * @email       info@wpbookingcalendar.com 
 * @modified    2016-08-05
 * 
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


/** API  for  Settings Page - Search Availability  */
class WPBC_API_SettingsSearchAvailability extends WPBC_Settings_API  {                             
    
    /**
	 * Settings API Constructor
     *  During creation,  system try to load values from DB, if exist.
     * 
     * @param type $id - "Pure Name"
     */
    public function __construct( $id,  $init_fields_values = array(), $options = array() ) {
        
        $default_options = array( 
                        'db_prefix_option' => '' 
                      , 'db_saving_type'   => 'separate'                 
            );                 
                                                                                // separate_prefix: update_bk_option( $this->options['db_prefix_option'] . $settings_id . '_' . $field_name , $value );
        $options = wp_parse_args( $options, $default_options );

        // add_bk_action( 'wpbc_other_versions_activation',   array( $this, 'activate'   ) );      // Activate
        // add_bk_action( 'wpbc_other_versions_deactivation', array( $this, 'deactivate' ) );      // Deactivate
        
        parent::__construct( $id, $options, $init_fields_values );              // Define ID of Setting page and options                
    }
    
    
    /** Define settings Fields  */
    public function init_settings_fields() {
        
        $this->fields = array();
        
        $this->fields['booking_search_form_show'] = array(   
                                      'type'        => 'wp_textarea'
                                    , 'default'     => ''
                                    , 'placeholder' => ''
                                    , 'title'       => ''
                                    , 'description' => ''
                                    , 'description_tag' => ''
                                    , 'css'         => ''
                                    , 'group'       => 'general'
                                    , 'tr_class'    => ''
                                    , 'rows'        => 14
                                    , 'show_in_2_cols' => true            
                                    // Default options:    
                                    , 'class'           => ''                   // Any extra CSS Classes to append to the Editor textarea 
                                    , 'default_editor'  => 'html'               // 'tinymce' | 'html'       // 'html' is used for the "Text" editor tab.
                                    , 'show_visual_tabs'=> true                 // Remove Visual Mode from the Editor        
                                    , 'teeny'           => true                 // Whether to output the minimal editor configuration used in PressThis 
                                    , 'drag_drop_upload'=> false                // Enable Drag & Drop Upload Support (since WordPress 3.9) 
                            );
        
        $this->fields['booking_search_form_show_help'] = array(   
                                        'type' => 'help'                                        
                                        , 'value' => array()
                                        , 'cols' => 2
                                        , 'group' => 'search_form_help'
                                );
        $this->fields['booking_search_form_show_help']['value'][] = '<strong>'. sprintf(__('Use these shortcodes for customization: ' ,'booking')) . '</strong>';
        $this->fields['booking_search_form_show_help']['value'][] = sprintf(__('%s - search inside posts/pages which are part of this category, ' ,'booking'),'<code>[search_category]</code>');
        $this->fields['booking_search_form_show_help']['value'][] = sprintf(__('%s - search inside posts/pages which have this tag, ' ,'booking'),'<code>[search_tag]</code>');
        $this->fields['booking_search_form_show_help']['value'][] = sprintf(__('%s - check-in date, ' ,'booking'),'<code>[search_check_in]</code>');
        $this->fields['booking_search_form_show_help']['value'][] = sprintf(__('%s - check-out date, ' ,'booking'),'<code>[search_check_out]</code>');
        $this->fields['booking_search_form_show_help']['value'][] = sprintf(__('%s - default selection number of visitors, ' ,'booking'),'<code>[search_visitors]</code>');
        $this->fields['booking_search_form_show_help']['value'][] = sprintf( __('Example: %s - custom number of visitor selections"' ,'booking'),'<code>[search_visitors "1" "2" "3" "4" "5" "6" "7" "8" "9" "10"]</code>' );
        $this->fields['booking_search_form_show_help']['value'][] = sprintf( '%s - +/- 2 ' . __('days' ,'booking') . ', ','<code>[additional_search "3"]</code>');
        $this->fields['booking_search_form_show_help']['value'][] = sprintf(__('%s - search button, ' ,'booking'),'<code>[search_button]</code>');
        $this->fields['booking_search_form_show_help']['value'][] = __('HTML tags is accepted.' ,'booking');
        
        ////////////////////////////////////////////////////////////////////////
        
        $this->fields['booking_found_search_item'] = array(   
                                      'type'        => 'wp_textarea'
                                    , 'default'     => ''
                                    , 'placeholder' => ''
                                    , 'title'       => ''
                                    , 'description' => ''
                                    , 'description_tag' => ''
                                    , 'css'         => ''
                                    , 'group'       => 'search_results'
                                    , 'tr_class'    => ''
                                    , 'rows'        => 20
                                    , 'show_in_2_cols' => true            
                                    // Default options:    
                                    , 'class'           => ''                   // Any extra CSS Classes to append to the Editor textarea 
                                    , 'default_editor'  => 'html'               // 'tinymce' | 'html'       // 'html' is used for the "Text" editor tab.
                                    , 'show_visual_tabs'=> true                 // Remove Visual Mode from the Editor        
                                    , 'teeny'           => true                 // Whether to output the minimal editor configuration used in PressThis 
                                    , 'drag_drop_upload'=> false                // Enable Drag & Drop Upload Support (since WordPress 3.9) 
                            );
        
        $this->fields['booking_found_search_item_help'] = array(   
                                        'type' => 'help'                                        
                                        , 'value' => array()
                                        , 'cols' => 2
                                        , 'group' => 'search_results_help'
                                );
        $this->fields['booking_found_search_item_help']['value'][] = '<strong>'. sprintf(__('Use these shortcodes for customization: ' ,'booking')) . '</strong>';
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - resource title, ' ,'booking'),'<code>[booking_resource_title]</code>');
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - link to the page with booking form, ' ,'booking'),'<code>[link_to_booking_resource "Book now"]</code>');
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - link to the page with booking form, ' ,'booking'),'<code>[book_now_link]</code> (URL)');
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - availability of booking resource, ' ,'booking'),'<code>[num_available_resources]</code>');
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - maximum number of visitors for the booking resource, ' ,'booking'),'<code>[max_visitors]</code>');
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - cost of booking the resource, ' ,'booking'),'<code>[standard_cost]</code>');
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - featured image, taken from the featured image associated with the post, ' ,'booking'),'<code>[booking_featured_image]</code>');
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - booking info, taken from the excerpt associated with the post, ' ,'booking'),'<code>[booking_info]</code>');
        $this->fields['booking_found_search_item_help']['value'][] = '<hr/>';
        $this->fields['booking_found_search_item_help']['value'][] = '<code>'.'[cost_hint]'.'</code> - ' . __('Full cost of the booking.' ,'booking');
        $this->fields['booking_found_search_item_help']['value'][] = '<code>'.'[original_cost_hint]'.'</code> - ' . __('Cost of the booking for the selected dates only.' ,'booking');
        $this->fields['booking_found_search_item_help']['value'][] = '<code>'.'[additional_cost_hint]'.'</code> - ' . __('Additional cost, which depends on the fields selection in the form.' ,'booking');
        $this->fields['booking_found_search_item_help']['value'][] = '<code>'.'[deposit_hint]'.'</code> - ' . __('The deposit cost of the booking.' ,'booking');
        $this->fields['booking_found_search_item_help']['value'][] = '<code>'.'[balance_hint]'.'</code> - ' . __('Balance cost of the booking - difference between deposit and full cost.' ,'booking');        
        $this->fields['booking_found_search_item_help']['value'][] = '<hr/>';
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - check-in date, ' ,'booking'),'<code>[search_check_in]</code>');
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - check-out date, ' ,'booking'),'<code>[search_check_out]</code>');

        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - ID of booking resource, ' ,'booking'),'<code>[booking_resource_id]</code>');    			//FixIn: 8.1.2.1
        $this->fields['booking_found_search_item_help']['value'][] = sprintf(__('%s - ID of page with booking form, ' ,'booking'),'<code>[booking_resource_post_id]</code>');

        $this->fields['booking_found_search_item_help']['value'][] = __('HTML tags is accepted.' ,'booking');

        
        ////////////////////////////////////////////////////////////////////////

        $options = array();
        for ( $mm = 1; $mm < 25; $mm++ ) {
            $options[$mm . 'h'] = $mm . ' ' . __( 'hour(s)', 'booking' );
        }
        for ( $mm = 1; $mm < 32; $mm++ ) {
            $options[$mm . 'd'] = $mm . ' ' . __( 'day(s)', 'booking' );
        }
        $this->fields['booking_cache_expiration'] = array(   
                                    'type' => 'select'
                                    , 'default' => ''
                                    , 'title' => __('Cache expiration', 'booking')
                                    , 'description' => __('Select time of cache expiration', 'booking')  
                                    , 'description_tag' => 'span'
                                    , 'css' => ''
                                    , 'options' => $options
                                    , 'group' => 'search_cache'
                            );        
    }


    //                                                                              <editor-fold   defaultstate="collapsed"   desc=" Custom Validate $_POST " >    
    
    /**
	 * Custom Validate Textarea in POST request - escape data correctly.
     *  Primary  executed for element with ID: "validate_   ID    _post"        validate_   booking_search_form_show    _post
     *
     * @param string $post_key - key for POST 
     * @return string | false,  if no such POST
     */
    public function validate_booking_search_form_show_post( $post_key ) {
        return $this->validate_js_post( $post_key );
    }
    

    /**
	 * Custom Validate Textarea in POST request - escape data correctly.
     *  Primary  executed for element with ID: "validate_   ID    _post"        validate_   booking_found_search_item    _post
     *
     * @param string $post_key - key for POST 
     * @return string | false,  if no such POST
     */
    public function validate_booking_found_search_item_post( $post_key ) {
        return $this->validate_js_post( $post_key );
    }
    

    /**
	 * Custom Validate Textarea in POST request - escape data correctly. Allow JS.
     *
     * @param string $post_key - key for POST 
     * @return string | false,  if no such POST
     */
    public function validate_js_post( $post_key ) {

        $value = false;

        if ( isset( $_POST[ $post_key ] ) ) {

            $value = wp_kses(   trim( stripslashes( $_POST[ $post_key ] ) ),
                                array_merge(
                                                array(    'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
                                                        , 'script' => array( 'type' => true )       // Allow JS
                                                ),
                                                wp_kses_allowed_html( 'post' )
                                )
                    );
        }

        return $value;
    }

    //                                                                              </editor-fold>
}


/**
	 * Show Content
 *  Update Content
 *  Define Slug
 *  Define where to show
 */
class WPBC_Page_SettingsSearchAvailability extends WPBC_Page_Structure {
    

    public $gateway_api = false;
    
    /**
	 * API - for Fields of this Settings Page
     * 
     * @param array $init_fields_values - array of init form  fields data - this array  can  ovveride "default" fields and loaded data.
     * @return object API
     */
    public function get_api( $init_fields_values = array() ){
        
        if ( $this->gateway_api === false ) {
            $this->gateway_api = new WPBC_API_SettingsSearchAvailability( 'search_availability' , $init_fields_values );    
        }        
        return $this->gateway_api;
    }

    
    public function in_page() {
        
        if ( ! wpbc_is_mu_user_can_be_here( 'only_super_admin' ) ) {            // If this User not "super admin",  then  do  not load this page at all            
            return (string) rand(100000, 1000000);
        }
        
        return 'wpbc-settings';
    }
    

    public function tabs() {
        
        $tabs = array();
                
        $tabs[ 'search' ] = array(
                              'title'     => __( 'Search', 'booking')             // Title of TAB    
                            , 'page_title'=> __( 'Search Settings', 'booking')      // Title of Page    
                            , 'hint'      => __( 'Search Settings', 'booking')               // Hint    
                            //, 'link'      => ''                                 // Can be skiped,  then generated link based on Page and Tab tags. Or can  be extenral link
                            //, 'position'  => ''                                 // 'left'  ||  'right'  ||  ''
                            //, 'css_classes'=> ''                                // CSS class(es)
                            //, 'icon'      => ''                                 // Icon - link to the real PNG img
                            , 'font_icon' => 'glyphicon glyphicon-search'         // CSS definition  of forn Icon
                            //, 'default'   => false                               // Is this tab activated by default or not: true || false. 
                            //, 'disabled'  => false                              // Is this tab disbaled: true || false. 
                            //, 'hided'     => false                              // Is this tab hided: true || false. 
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

        do_action( 'wpbc_hook_settings_page_header', 'search_availability_settings');    // Define Notices Section and show some static messages, if needed
        
        if ( ! wpbc_is_mu_user_can_be_here('activated_user') ) return false;    // Check if MU user activated, otherwise show Warning message.
   
        if ( ! wpbc_is_mu_user_can_be_here('only_super_admin') ) return false;  // User is not Super admin, so exit.  Basically its was already checked at the bottom of the PHP file, just in case.
        
        
        ////////////////////////////////////////////////////////////////////////
        // Load Data 
        ////////////////////////////////////////////////////////////////////////
        
        $init_fields_values = array();               
        
        $this->get_api( $init_fields_values );
        
        
        ////////////////////////////////////////////////////////////////////////
        //  S u b m i t   Main Form  
        ////////////////////////////////////////////////////////////////////////
        
        $submit_form_name = 'wpbc_search_availability';                         // Define form name
        
        // $this->get_api()->validated_form_id = $submit_form_name;             // Define ID of Form for ability to  validate fields (like required field) before submit.
        
        if ( isset( $_POST['is_form_sbmitted_'. $submit_form_name ] ) ) {

            // Nonce checking    {Return false if invalid, 1 if generated between, 0-12 hours ago, 2 if generated between 12-24 hours ago. }
            $nonce_gen_time = check_admin_referer( 'wpbc_settings_page_' . $submit_form_name );  // Its stop show anything on submiting, if its not refear to the original page

            // Save Changes 
            $this->update();
        }   
        
        // Cache Reset                      
        if (  ( isset( $_GET['cache_reset'] ) ) && ( $_GET['cache_reset'] == '1' )  ) {
            make_bk_action( 'regenerate_booking_search_cache' );
            wpbc_show_message( __('Cache Updated', 'booking' ), 5 );
        }
        ////////////////////////////////////////////////////////////////////////
        // JavaScript: Tooltips, Popover, Datepick (js & css) 
        ////////////////////////////////////////////////////////////////////////
        
        echo '<span class="wpdevelop">';
        
        wpbc_js_for_bookings_page();                                        
        
        echo '</span>';

        ?><div class="clear" style="margin-bottom:0px;"></div><?php
        
        
        ////////////////////////////////////////////////////////////////////////
        // Toolbar 
        ////////////////////////////////////////////////////////////////////////
        wpbc_bs_toolbar_sub_html_container_start();

        ?><span class="wpdevelop"><div class="visibility_container clearfix-height" style="display:block;"><?php


     
            ?><div class="wpdvlp-sub-tabs" style="background:none;border:none;box-shadow: none;padding:0;"><div class="wpdvlp-tabs-wrapper"><span class="nav-tabs" style="text-align:right;">
                <a href="javascript:void(0);" onclick="javascript:wpbc_scroll_to('#wpbc_settings_search_availability_form_metabox' );" original-title="" class="nav-tab go-to-link"><span><?php _e('Search Availability Form', 'booking'); ?></span></a>
                <a href="javascript:void(0);" onclick="javascript:wpbc_scroll_to('#wpbc_settings_search_results_form_metabox' );" original-title="" class="nav-tab go-to-link"><span><?php _e('Search Results', 'booking'); ?></span></a>
                <a href="javascript:void(0);" onclick="javascript:wpbc_scroll_to('#wpbc_settings_search_cache_metabox' );" original-title="" class="nav-tab go-to-link"><span><?php _e('Search Cache', 'booking'); ?></span></a>
                <?php 
            $save_button = array( 'title' => __('Save Changes', 'booking'), 'form' => $submit_form_name );
            $this->toolbar_save_button( $save_button );                         // Save Button                 
                ?>
            </span></div></div><?php 
            
        
            
        ?></div></span><?php
        
        wpbc_bs_toolbar_sub_html_container_end();
        
        ?><div class="clear"></div><?php

        
        // Scroll links ////////////////////////////////////////////////////////
        ?>
        <?php
        
        
        ////////////////////////////////////////////////////////////////////////
        // Content  ////////////////////////////////////////////////////////////
        ?>
        <div class="clear" style="margin-bottom:10px;"></div>
        <span class="metabox-holder">
            <form  name="<?php echo $submit_form_name; ?>" id="<?php echo $submit_form_name; ?>" action="" method="post" autocomplete="off">
                <?php 
                   // N o n c e   field, and key for checking   S u b m i t 
                   wp_nonce_field( 'wpbc_settings_page_' . $submit_form_name );
                ?><input type="hidden" name="is_form_sbmitted_<?php echo $submit_form_name; ?>" id="is_form_sbmitted_<?php echo $submit_form_name; ?>" value="1" /><?php                 
                ?><div class="clear"></div><?php 
                
                ?><div class="clear" style="height:10px;"></div>
                <div class="wpbc-settings-notice notice-info" style="text-align:left;">
                    <strong><?php _e('Note!' ,'booking'); ?></strong> <?php 
                        printf( __('If you do not see search results at front-end side of your website, please check troubleshooting instruction %shere%s' ,'booking'),'<a href="https://wpbookingcalendar.com/faq/no-search-results/" target="_blank">','</a>');
                    ?>
                </div>
                <div class="clear" style="height:10px;"></div><?php 

                     
                ?><div class="wpbc_settings_row wpbc_settings_row_left"><?php
                
                    wpbc_open_meta_box_section( 'wpbc_settings_search_availability_form', __('Search Availability Form', 'booking') );                    
                        $this->toolbar_reset_to_default( 'search_form_show' );                                // Reset to Default Forms                                                                             
                        $this->get_api()->show( 'general' );                               
                    wpbc_close_meta_box_section();
                ?>
                </div>  
                <div class="wpbc_settings_row wpbc_settings_row_right"><?php                
                
                    wpbc_open_meta_box_section( 'wpbc_settings_search_availability_form_help', __('Help', 'booking') );
                        $this->get_api()->show( 'search_form_help' );   
                    wpbc_close_meta_box_section();
                ?>
                </div>
                <div class="clear"></div>

                
                <div class="wpbc_settings_row wpbc_settings_row_left"><?php                
                
                    wpbc_open_meta_box_section( 'wpbc_settings_search_results_form', __('Search Results' ,'booking') );                               
                        $this->toolbar_reset_to_default( 'found_search_item' );                                // Reset to Default Forms                    
                        $this->get_api()->show( 'search_results' );  
                        
                        echo  '<div class="clear" style="height:5px;"></div>'
                            . '<div class="wpbc-settings-notice notice-info" style="text-align:left;">'
                            .   '<strong>' . __('Note!' ,'booking') . '</strong> '
                            .        __('CSS customization of search form and search results you can make at this file' ,'booking') 
                            .       ': <code>' . WPBC_PLUGIN_URL . '/inc/css/search-form.css</code>'
                            . '</div>'
                            . '<div class="clear"></div>';

                    wpbc_close_meta_box_section();
                
                ?>
                </div>  
                <div class="wpbc_settings_row wpbc_settings_row_right"><?php                
                
                    wpbc_open_meta_box_section( 'wpbc_settings_search_results_form_help', __('Help', 'booking') );                        
                        $this->get_api()->show( 'search_results_help' );                           
                    wpbc_close_meta_box_section();
                ?>
                </div>
                <div class="clear"></div>
                
                <div class="wpbc_settings_row wpbc_settings_row_leftNO"><?php                
                
                    wpbc_open_meta_box_section( 'wpbc_settings_search_cache', __('Search Cache' ,'booking') );    
                           $this->get_api()->show( 'search_cache' ); 
                           $this->show_info_search_cache_and_pages_with_booking_forms();
                           $this->show_reset_search_cache_button();
                    wpbc_close_meta_box_section();
                
                ?>
                </div>  
                <div class="clear"></div>
                <input type="submit" value="<?php _e('Save Changes','booking'); ?>" class="button button-primary wpbc_submit_button" />  
            </form>
        </span>
        <?php       
    
        do_action( 'wpbc_hook_settings_page_footer', 'search_availability_settings' );
    }


    /** Save Chanages */  
    public function update() {

        // Get Validated Email fields
        $validated_fields = $this->get_api()->validate_post();
//debuge($validated_fields, $_POST );        

        // Overwrite - allow HTML to  save
        $validated_fields['booking_search_form_show']  =  trim( stripslashes( $_POST[ 'search_availability' . '_' . 'booking_search_form_show' ] ) );
        $validated_fields['booking_found_search_item'] =  trim( stripslashes( $_POST[ 'search_availability' . '_' . 'booking_found_search_item' ] ) );
        
        $validated_fields = apply_filters( 'wpbc_search_availability_validate_fields_before_saving', $validated_fields );   //Hook for validated fields.
        
//debuge($validated_fields);        
        
        $this->get_api()->save_to_db( $validated_fields );
        
        wpbc_show_changes_saved_message();        
        
        // Old way of saving:
        // update_bk_option( 'booking_cache_expiration' , WPBC_Settings_API::validate_text_post_static( 'booking_cache_expiration' ) );
        
        
    }


    // <editor-fold     defaultstate="collapsed"                        desc=" CSS  "  >
    
    /** CSS for this page */
    private function css() {
        ?>
        <style type="text/css">  
            .wpbc-help-message {
                border:none;
            }
            /* toolbar fix */
            .wpdevelop .visibility_container .control-group {
                margin: 0 8px 5px 0;
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
    
    // </editor-fold>
    
    
    // <editor-fold     defaultstate="collapsed"                        desc="  S u p p o r t   "  >

    
    /** Show Save button  in toolbar  for saving form */
    private function toolbar_save_button( $save_button ) {
                
        ?>
        <div class="clear-for-mobile"></div><input 
                                type="button" 
                                class="button button-primary wpbc_submit_button" 
                                value="<?php echo $save_button['title']; ?>" 
                                onclick="if (typeof document.forms['<?php echo $save_button['form']; ?>'] !== 'undefined'){ 
                                            document.forms['<?php echo $save_button['form']; ?>'].submit(); 
                                         } else { 
                                             wpbc_admin_show_message( '<?php echo  ' <strong>Error!</strong> Form <strong>' , $save_button['form'] , '</strong> does not exist.'; ?>.', 'error', 10000 );   //FixIn: 7.0.1.56
                                         }" 
                                />
        <?php
    }
    
    
    /** Selection  of default Template and Button for Reseting  */
    private function toolbar_reset_to_default( $template_type ) {

        ?><div class="wpdevelop"><?php 
        ?><div class="visibility_container" style="display:block;"><?php 
        
        
        $templates = array();
        
        if ( $template_type == 'search_form_show' ) {
            
            
            $templates['selector_hint'] = array(  
                                                    'title' => __('Select Template', 'booking')
                                                    , 'id' => ''   
                                                    , 'name' => ''  
                                                    , 'style' => 'font-weight: 400;border-bottom:1px dashed #ccc;'    
                                                    , 'class' => ''     
                                                    , 'disabled' => false
                                                    , 'selected' => false
                                                    , 'attr' => array()   
                                                );       
            $templates[ 'inline' ] = array(  
                                                    'title' => __('Inline Search Form Template', 'booking')
                                                    , 'id' => ''   
                                                    , 'name' => ''  
                                                    , 'style' => ''
                                                    , 'class' => ''     
                                                    , 'disabled' => false
                                                    , 'selected' => false
                                                    , 'attr' => array()   
                                                );        
            $templates[ 'horizontal' ] = array(  
                                                    'title' => __('Horizontal Search Form Template', 'booking')
                                                    , 'id' => ''   
                                                    , 'name' => ''  
                                                    , 'style' => ''
                                                    , 'class' => ''     
                                                    , 'disabled' => false
                                                    , 'selected' => false
                                                    , 'attr' => array()   
                                                );        
            $templates[ 'standard' ] = array(  
                                                    'title' => __('Standard Search Form Template', 'booking')
                                                    , 'id' => ''   
                                                    , 'name' => ''  
                                                    , 'style' => ''
                                                    , 'class' => ''     
                                                    , 'disabled' => false
                                                    , 'selected' => false
                                                    , 'attr' => array()   
                                                );        
            $templates[ 'advanced' ] = array(  
                                                    'title' => __('Advanced', 'booking')
                                                    , 'id' => ''   
                                                    , 'name' => ''  
                                                    , 'style' => ''
                                                    , 'class' => ''     
                                                    , 'disabled' => false
                                                    , 'selected' => false
                                                    , 'attr' => array()   
                                                );        
            
        } else if ( $template_type == 'found_search_item' ) {
            
            $templates['selector_hint'] = array(  
                                                    'title' => __('Select Template', 'booking')
                                                    , 'id' => ''   
                                                    , 'name' => ''  
                                                    , 'style' => 'font-weight: 400;border-bottom:1px dashed #ccc;'    
                                                    , 'class' => ''     
                                                    , 'disabled' => false
                                                    , 'selected' => false
                                                    , 'attr' => array()   
                                                );       

            $templates[ 'standard' ] = array(  
                                                    'title' => __('Standard', 'booking')
                                                    , 'id' => ''   
                                                    , 'name' => ''  
                                                    , 'style' => ''
                                                    , 'class' => ''     
                                                    , 'disabled' => false
                                                    , 'selected' => false
                                                    , 'attr' => array()   
                                                );        
            $templates[ 'advanced' ] = array(  
                                                    'title' => __('Advanced', 'booking')
                                                    , 'id' => ''   
                                                    , 'name' => ''  
                                                    , 'style' => ''
                                                    , 'class' => ''     
                                                    , 'disabled' => false
                                                    , 'selected' => false
                                                    , 'attr' => array()   
                                                );        
        }                                      
                                                                
        $params = array(  
                          'label_for' => $template_type                         // "For" parameter  of label element
                        , 'label' => '' //__('Add New Field', 'booking')        // Label above the input group
                        , 'style' => ''                                         // CSS Style of entire div element
                        , 'items' => array(
//                                array(      
//                                    'type' => 'addon' 
//                                    , 'element' => 'text'           // text | radio | checkbox
//                                    , 'text' => __('Reset Search Availability Form', 'booking') . ':'
//                                    , 'class' => ''                 // Any CSS class here
//                                    , 'style' => 'font-weight:600;' // CSS Style of entire div element
//                                )  
                                // Warning! Can be text or selectbox, not both  OR you need to define width                     
                                 array(                                            
                                      'type' => 'select'                              
                                    , 'id'   => $template_type
                                    , 'name' => $template_type
                                    , 'style' => 'width:200px;'                            
                                    , 'class' => ''   
                                    , 'multiple' => false
                                    , 'disabled' => false
                                    , 'disabled_options' => array()             // If some options disbaled,  then its must list  here                                
                                    , 'attr' => array()                         // Any  additional attributes, if this radio | checkbox element                                                   
                                    , 'options' => $templates                   // Associated array  of titles and values                                                       
                                    , 'value' => ''                             // Some Value from optins array that selected by default                                                                              
                                    , 'onfocus' => ''
                                    //, 'onchange' => "wpbc_show_fields_generator( this.options[this.selectedIndex].value );"
                                )              
                        )
                    );

            
            
            
        ?><div class="control-group wpbc-no-padding"><?php 
                wpbc_bs_input_group( $params );                   
        ?></div><?php
        
        
        $params = array(  
                      'label_for' => $template_type                             // "For" parameter  of label element
                    , 'label' => '' //__('Add New Field', 'booking')            // Label above the input group
                    , 'style' => ''                                             // CSS Style of entire div element
                    , 'items' => array(     
                                        array( 
                                            'type' => 'button'
                                            , 'title' => __('Reset', 'booking')  // __('Reset', 'booking')
                                            , 'hint' => array( 'title' => __('Reset current Form' ,'booking') , 'position' => 'top' )
                                            , 'class' => 'button tooltip_top' 
                                            , 'font_icon' => 'glyphicon glyphicon-repeat'
                                            , 'icon_position' => 'right'
                                            , 'action' => " var sel_res_val = document.getElementById('" . $template_type . "').options[ document.getElementById('" . $template_type . "').selectedIndex ].value;"
                                                        . " if   ( sel_res_val == 'selector_hint') { "
                                                        . "    wpbc_field_highlight( '#" . $template_type . "' ); return;"          //. "  jQuery('#wpbc_search_availability').submit();"
                                                        . " }"  
                                                        //. " if ( wpbc_are_you_sure('" . esc_js(__('Do you really want to do this ?' ,'booking')) . "') ) {"
                                                        . "    wpbc_reset_form_to_template( 'search_availability_booking_" . $template_type . "', sel_res_val ); "          //. "  jQuery('#wpbc_search_availability').submit();"
                                                        //. " }"  
                                        )                            
                            )
                    );

        ?><div class="control-group wpbc-no-padding"><?php 
                wpbc_bs_input_group( $params );                   
        ?></div><?php
        
        
        ?></div></div><?php 
        ?><div class="clear"></div><?php   
        
        ?>
        <script type="text/javascript">
            function wpbc_reset_form_to_template( form_id, template_name ) {
                var search_form_content = '';
                if ( form_id == 'search_availability_booking_search_form_show' ) {                    
                    if ( template_name == 'inline' ) {
                        search_form_content = '<?php echo str_replace( '\\n\\r', '\n', wpbc_get_default_search_form_template('inline') ); ?>';
                    }
                    if ( template_name == 'horizontal' ) {
                        search_form_content = '<?php echo str_replace( '\\n\\r', '\n', wpbc_get_default_search_form_template('horizontal') ); ?>';
                    }
                    if ( template_name == 'standard' ) {
                        search_form_content = '<?php echo str_replace( '\\n\\r', '\n', wpbc_get_default_search_form_template('standard') ); ?>';
                    }
                    if ( template_name == 'advanced' ) {
                        search_form_content = '<?php echo str_replace( '\\n\\r', '\n', wpbc_get_default_search_form_template('advanced') ); ?>';
                    }
                }
                
                if ( form_id == 'search_availability_booking_found_search_item' ) {
                    if ( template_name == 'standard' ) {
                        search_form_content = '<?php echo str_replace( '\\n\\r', '\n', wpbc_get_default_search_results_template('standard') ); ?>';
                    }
                    if ( template_name == 'advanced' ) {
                        search_form_content = '<?php echo str_replace( '\\n\\r', '\n', wpbc_get_default_search_results_template('advanced') ); ?>';
                    }
                }
                wpbc_reset_wp_editor_content( form_id, search_form_content );
            }
        </script>
        <?php
    }

    
    /** Show Time of cache Expiring and Found Pages with  booking forms */      
    private function show_info_search_cache_and_pages_with_booking_forms() {
        
        ?>                                            
        <div class="clear"></div>
        <p class="wpbc-settings-notice notice-info" style="text-align:left;">
            <span class="description"><?php printf(__('Cache will expire:' ,'booking'));?> </span><?php
            
                $period = get_bk_option( 'booking_cache_expiration' );
                if ( substr( $period, -1, 1 ) == 'd' ) {
                    $period = substr( $period, 0, -1 );
                    $period = $period * 24 * 60 * 60;
                }
                if ( substr( $period, -1, 1 ) == 'h' ) {
                    $period = substr( $period, 0, -1 );
                    $period = $period * 60 * 60;
                }
                $previos = get_bk_option( 'booking_cache_created' );
                $previos = explode( ' ', $previos );
                $previos_time = explode( ':', $previos[1] );
                $previos_date = explode( '-', $previos[0] );
                $previos_sec = mktime( $previos_time[0], $previos_time[1], $previos_time[2], $previos_date[1], $previos_date[2], $previos_date[0] );

                $expire_sec = ($previos_sec + $period);
                $cache_epire_on = date_i18n( 'Y-m-d H:i:s T', $expire_sec );

                echo '<code>' . $cache_epire_on . '</code>';
                
                $found_records = get_bk_option( 'booking_cache_content' );      //FixIn: 6.0.1.15
                if ( !empty( $found_records ) ) {
                    if ( is_serialized( $found_records ) )
                        $found_records = @unserialize( $found_records );
                    $found_records_num = count( $found_records );
                } else
                    $found_records_num = 0;
                ?>
                <br/><span class="description"><?php printf( __( 'Found: %s booking forms inside of posts or pages ', 'booking' ), '<code>' . $found_records_num . '</code>' ); ?>:</span>
                <?php
                foreach ( $found_records as $found_record ) {
                    echo '<br/><code>';
                    echo '[', __( 'Page' ), ' ID=', $found_record->ID, '] '
                        , ' [', __( 'Resource', 'booking' ), ' ID=', $found_record->booking_resource, '] '
                        , $found_record->guid;
                    echo '</code>';
                }
                ?>
        </p>
        <div class="clear"></div>
        <?php
    }
    
    
    /** Button  for Search Cache reseting */
    private function show_reset_search_cache_button() {


        $link = wpbc_get_settings_url() . '&tab=search';
        
        ?>
        <div class="clear" style="height:20px;"></div>
        <input class="button-primary0 button" 
               style="float:left;font-weight:600;" 
               type="button" 
               value="<?php _e('Reset Search Cache' ,'booking'); ?>" 
               onclick="javascript:window.location.href='<?php echo $link ;?>&cache_reset=1';" 
               name="reset_form" />
        <div class="clear"></div>
        <?php
    }
    
    // </editor-fold>
    
}
add_action('wpbc_menu_created', array( new WPBC_Page_SettingsSearchAvailability() , '__construct') );    // Executed after creation of Menu