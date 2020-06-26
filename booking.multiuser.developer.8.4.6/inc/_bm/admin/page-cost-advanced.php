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


/**
	 * Show Content
 *  Update Content
 *  Define Slug
 *  Define where to show
 */
class WPBC_Page_Settings__advancedcost extends WPBC_Page_Structure {

    const SUBMIT_FORM = 'wpbc_advancedcost';                                   // Main Form Name
    const ACTION_FORM = 'wpbc_advancedcost_action';                            // Form for sub-actions: like Add New | Edit actions
        
    const HTML_PREFIX = 'ac_';

  
    public function in_page() {
        return 'wpbc-resources';
    }


    public function tabs() {

        $tabs = array();

        $tabs[ 'cost_advanced' ] = array(
                              'title'       => __('Advanced Cost','booking')            // Title of TAB    
                            , 'hint'        => __('Customization of additional cost, which depend from form fields', 'booking')                      // Hint    
                            , 'page_title'  => __('Advanced Cost Settings' ,'booking')                                // Title of Page    
                            //, 'link'      => ''                               // Can be skiped,  then generated link based on Page and Tab tags. Or can  be extenral link
                            //, 'position'  => 'left'                           // 'left'  ||  'right'  ||  ''
                            //, 'css_classes'=> ''                              // CSS class(es)
                            //, 'icon'      => ''                               // Icon - link to the real PNG img
                            , 'font_icon' => 'glyphicon glyphicon-tasks'        // CSS definition  of forn Icon
                            , 'default'   => false                              // Is this tab activated by default or not: true || false. 
                            //, 'disabled'  => false                            // Is this tab disbaled: true || false. 
                            , 'hided'     => false                              // Is this tab hided: true || false. 
                            , 'subtabs'   => array()   
                    );
        return $tabs;
    }


    public function content() {
        
        $this->css();

        ////////////////////////////////////////////////////////////////////////
        // Checking ////////////////////////////////////////////////////////////
        
        do_action( 'wpbc_hook_settings_page_header', 'advanced_cost_settings');       // Define Notices Section and show some static messages, if needed
        
        if ( ! wpbc_is_mu_user_can_be_here('activated_user') ) return false;    // Check if MU user activated, otherwise show Warning message.
   
        // if ( ! wpbc_is_mu_user_can_be_here('only_super_admin') ) return false;  // User is not Super admin, so exit.  Basically its was already checked at the bottom of the PHP file, just in case.
        
        
        //////////////////////////////////////////////////////////////////////// 
        // Submit  /////////////////////////////////////////////////////////////
        
        $submit_form_name = 'wpbc_advancedcost_form';                           // Define form name
                
        if ( isset( $_POST['is_form_sbmitted_'. $submit_form_name ] ) ) {

            // Nonce checking    {Return false if invalid, 1 if generated between, 0-12 hours ago, 2 if generated between 12-24 hours ago. }
            $nonce_gen_time = check_admin_referer( 'wpbc_settings_page_' . $submit_form_name  );  // Its stop show anything on submiting, if its not refear to the original page

            // Save Changes 
            $this->update();
        }                
        
         
        ////////////////////////////////////////////////////////////////////////
        // Toolbar /////////////////////////////////////////////////////////////
        wpbc_bs_toolbar_sub_html_container_start();

        ?><span class="wpdevelop"><div class="visibility_container clearfix-height" style="display:block;"><?php

            wpbc_js_for_bookings_page();                                            // JavaScript functions
        
            if ( function_exists( 'wpbc_toolbar_btn__custom_forms_in_settings_fields' ) ) {
                $is_show_add_new_custom_form = false;
                wpbc_toolbar_btn__custom_forms_in_settings_fields( $is_show_add_new_custom_form );
            }
                        
            $save_button = array( 'title' => __('Save Changes', 'booking'), 'form' => $submit_form_name );
            $this->toolbar_save_button( $save_button );                         // Save Button 
            
        ?></div></span><?php
        
        wpbc_bs_toolbar_sub_html_container_end();
        
        ?><div class="clear"></div><?php

        // Scroll links ////////////////////////////////////////////////////////
        /*
        ?>
        <div class="wpdvlp-sub-tabs" style="background:none;border:none;box-shadow: none;padding:0;"><span class="nav-tabs" style="text-align:right;">
            <a href="javascript:void(0);" onclick="javascript:wpbc_scroll_to('#wpbc_settings_form_fields_metabox' );" original-title="" class="nav-tab go-to-link"><span><?php echo ucwords( __('Form fields', 'booking') ); ?></span></a>
            <a href="javascript:void(0);" onclick="javascript:wpbc_scroll_to('#wpbc_settings_form_fields_show_metabox' );" original-title="" class="nav-tab go-to-link"><span><?php _e('Content of Booking Fields' , 'booking' ); ?></span></a>
        </span></div>
        <?php
        */
        
        ?>
        <div class="clear" style="height:10px;"></div>
        <div class="wpbc-settings-notice notice-info" style="text-align:left;">
            <strong><?php _e('Note!' ,'booking'); ?></strong> <?php                                 
                    _e( 'Configure additional cost, which depend from selection of selectbox(es) and checkbox(es).', 'booking' );
                    echo ' ';
                    printf( __( 'Fields %s(selectbox(es) and checkbox(es))%s are shown here automatically if they exist in the %sbooking form%s.', 'booking' )
                            , '<em>', '</em>'
                            , '<em><a href="' . wpbc_get_settings_url() . '&tab=form" >', '</a></em>'
                        );
            ?>
        </div>
        <div class="clear" style="height:10px;"></div>
        <?php 
        ////////////////////////////////////////////////////////////////////////
        // Content  ////////////////////////////////////////////////////////////
        ?>
        <div class="clear" style="margin-bottom:10px;"></div>
        <span class="metabox-holder">
            <form  name="<?php echo $submit_form_name; ?>" id="<?php echo $submit_form_name; ?>" action="" method="post">
                <?php 
                   // N o n c e   field, and key for checking   S u b m i t 
                   wp_nonce_field( 'wpbc_settings_page_' . $submit_form_name );
                ?><input type="hidden" name="is_form_sbmitted_<?php echo $submit_form_name; ?>" id="is_form_sbmitted_<?php echo $submit_form_name; ?>" value="1" /><?php 
                
                ?><div class="wpbc_settings_row wpbc_settings_row_left"><?php
                
                    wpbc_open_meta_box_section( 'wpbc_settings_advancedcost', __('Advanced Cost', 'booking') );
                        $this->show_ac_form();                    
                    wpbc_close_meta_box_section();
                ?>
                </div>  
                <div class="wpbc_settings_row wpbc_settings_row_right"><?php                
                
                    wpbc_open_meta_box_section( 'wpbc_settings_advancedcost_help', __('Help', 'booking') );
                        $this->show_ac_help();     
                    wpbc_close_meta_box_section();
                ?>
                </div>
                <div class="clear"></div>
                <input type="submit" value="<?php _e('Save Changes','booking'); ?>" class="button button-primary wpbc_submit_button" />  
            </form>
        </span>
        <?php       
    
        do_action( 'wpbc_hook_settings_page_footer', 'advanced_cost_settings' );
    }

    
    /** Save Chanages */  
    public function update() {

        // Check  server restrictions in php.ini file relative to  length of $_POST variabales
        if (  function_exists ('wpbc_check_post_key_max_number')) { wpbc_check_post_key_max_number(); }

        // Array,  and FALSE or name of custom  form
        list( $advanced_cost_fields_arr, $custom_form_name ) = $this->get_advanced_cost_arr();

        foreach ( $advanced_cost_fields_arr as $field_name => $field_values ) {
            
            foreach ( $field_values as $field_value_name => $field_value_value ) {
                
                if ( isset( $_POST[ self::HTML_PREFIX . $field_name . '_' . $field_value_name ] ) ) {
                    
                    //$validated_value = WPBC_Settings_API::validate_text_post_static( self::HTML_PREFIX . $field_name . '_' . $field_value_name );

                    $validated_value = str_replace( ',', '.', $_POST[ self::HTML_PREFIX . $field_name . '_' . $field_value_name ]  );    // In case,  if someone was make mistake and use , instead of .
                    $validated_value = trim( $validated_value );
                    
                    //In case, if someone set empty field, and set percentage, then  set 100
                    if (  
                                ( $_POST[ self::HTML_PREFIX . $field_name . '_' . $field_value_name . '_type' ]  ==  '%' )
                            && ( $validated_value === '' )
                        ) 
                         $validated_value = '100';
                    else {
                    	//FixIn: 8.1.3.17
                    	if ( strpos( $validated_value, '[' ) !== false ) {
                    		$validated_value = $validated_value;
						} else {
		                    $validated_value = floatval( $validated_value );
	                    }
                    }
                    
                    // Transform $_POST value to  saved format  value
                    switch ( $_POST[ self::HTML_PREFIX . $field_name . '_' . $field_value_name . '_type' ] ) {
                        case '%':
                            $validated_value .= '%';
                            break;                        
                        case 'per_day':
                            $validated_value .= '/day';
                            break;
                        case 'per_night':
                            $validated_value .= '/night';
                            break;
                        case 'add_%':
                            $validated_value = '+' . $validated_value . '%';
                            break;                        
                        default:                                                // 'fixed' - nothing todo
                            break;
                    }      

                    $advanced_cost_fields_arr[ $field_name ][ $field_value_name ] = $validated_value;
                }
            }
        }
        
/*
        // Standard Form   |   Custom  form
        if ( $custom_form_name === false )
            $saved_advancedcost = get_bk_option( 'booking_advanced_costs_values' );                                      
        else
            $saved_advancedcost = get_bk_option( 'booking_advanced_costs_values_for' . $custom_form_name );
                                                                                /**  Array (     [visitors] => Array (
                                                                                                        [1] => 1
                                                                                                        [2] => 2
                                                                                                        [3] => 3
                                                                                                        [4] => 4 )
                                                                                                [children] => Array (
                                                                                                        [0] => 100%
                                                                                                        [1] => 100%
                                                                                                        [2] => 100%
                                                                                                        [3] => 100% )
                                                                                                [term_and_condition] => Array (
                                                                                                        [I_Accept_term_and_conditions] => 100%
                                                                                                    )
                                                                                            )
                                                                                

        $saved_advancedcost = maybe_unserialize( $saved_advancedcost );
        
debuge('$saved_advancedcost', $saved_advancedcost);        
*/
//debuge('Validated', $advanced_cost_fields_arr );


        if ( $custom_form_name === false )                                      // Standard
            update_bk_option( 'booking_advanced_costs_values'                           , maybe_serialize( $advanced_cost_fields_arr ) );
        else                                                                    // Custom
            update_bk_option( 'booking_advanced_costs_values_for' . $custom_form_name   , maybe_serialize( $advanced_cost_fields_arr ) );
        
        wpbc_show_changes_saved_message(); 
    }
        
    
    // <editor-fold     defaultstate="collapsed"                        desc=" CSS  "  >
    
    /** CSS for this page */
    private function css() {
        ?>
        <style type="text/css">  
            .wpbc_ac_fieldvalue_label {
                line-height: 1.4em;
                padding-right:5px;
                min-width: 25px;
            }
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
            @media (max-width: 782px) {  
                .form-table td fieldset label.wpbc_ac_fieldvalue_label {
                    line-height: 2.4em;
                }
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
    
    
    // <editor-fold     defaultstate="collapsed"                        desc=" Toolbar "  >
    
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
    
    
    // </editor-fold>
    

    /** Show Advanced Cost Form */
    private function show_ac_form() {
        
        // Array,  and FALSE or name of custom  form
        list( $advanced_cost_fields_arr, $custom_form_name ) = $this->get_advanced_cost_arr();

        //$temp_resource_id = 0;
        //$currency = wpbc_get_currency_symbol_for_user( $temp_resource_id );
        $currency = wpbc_get_currency_symbol();
        //$currency = wpbc_get_currency();
        $price_period = wpbc_get_per_day_night_title();
        
        ?><table class="form-table"><tbody><?php   
            
            foreach ( $advanced_cost_fields_arr as $field_name => $field_values ) {

                ?><tr valign="top" >
                    <th scope="row"><?php echo $field_name; ?></th>
                    <td class="description wpbc_edited_resource_label"><?php 
                        foreach ( $field_values as $field_value_name => $field_value_value ) {
                          
                            ?><fieldset><?php  
                            
                                if ( $field_value_name !== 'checkbox' ) {
                                    ?><label class="wpbc_ac_fieldvalue_label" for="<?php echo self::HTML_PREFIX . $field_name . '_' . $field_value_name; ?>" style="float:left;font-weight:600;"><?php                                     
                                            echo $field_value_name , ' = ';
                                    ?></label><?php 
                                }

                                $field_value_value_type = 'fixed';             
                                // Transformation  from input to  input and select    
                                // TODO: parse: $field_value_value here.
                                $field_value_value = trim( $field_value_value );
                                
                                if(  ( substr( $field_value_value , -1 ) == '%' ) && ( substr( $field_value_value , 0, 1 ) == '+' )  ){
                                    $field_value_value_type = 'add_%';   
                                    //$field_value_value = str_replace( array( '%', '+' ), '', $field_value_value );
									if ( substr( $field_value_value , -1 ) == '%' ) {
										$field_value_value = substr($field_value_value,0,-1);
									}
									if ( substr( $field_value_value , 0, 1 ) == '+' ) {
										$field_value_value = substr($field_value_value, 1 );
									}
                                }
                                else if ( substr( $field_value_value , -1 ) == '%' ) {
                                    $field_value_value_type = '%';   
                                    //$field_value_value = str_replace( '%', '', $field_value_value );
									if ( substr( $field_value_value , -1 ) == '%' ) {
										$field_value_value = substr($field_value_value,0,-1);
									}

                                }
                                else if ( substr( $field_value_value , -4 ) == '/day' ) {
                                    $field_value_value_type = 'per_day';   
                                    $field_value_value = str_replace( '/day', '', $field_value_value );
                                }
                                else if ( substr( $field_value_value , -6 ) == '/night' ) {
                                    $field_value_value_type = 'per_night';   
                                    $field_value_value = str_replace( '/night', '', $field_value_value );
                                }
                                $field_value_value = str_replace( ',', '.', $field_value_value );    // In case,  if someone was make mistake and use , instead of .

	                        	//FixIn: 8.1.3.17
								if ( strpos( $field_value_value, '[' ) !== false ) {
									$field_value_value = $field_value_value;
								} else {
									$field_value_value = floatval( $field_value_value );
								}
                                
                                if (  ( $field_value_value === 0.0 ) && ( $field_value_value_type === '%' )  ){
                                    $warning_style = 'border:1px solid #FC940F';
                                } else 
                                    $warning_style = '';
                                
                                WPBC_Settings_API::field_text_row_static(                                              
                                                      self::HTML_PREFIX . $field_name . '_' . $field_value_name
                                                    , array(  
                                                              'type'              => 'text'
                                                            , 'title'             => ''
                                                            , 'description'       => ''
                                                            , 'placeholder'       => ''
                                                            , 'description_tag'   => 'span'
                                                            , 'tr_class'          => ''
                                                            , 'class'             => ''
                                                            , 'css'               => 'float:left;margin:1px 10px 4px 1px;width:11em;' . $warning_style
                                                            , 'only_field'        => true
                                                            , 'attr'              => array()                                                    
                                                            //, 'validate_as'       => array( 'required' )
                                                            , 'value'             => (  ( isset( $field_value_value ) ) ? $field_value_value : ''  )
                                                        )
                                    ); 
                                
                                WPBC_Settings_API::field_select_row_static(                                              
                                                      self::HTML_PREFIX . $field_name . '_' . $field_value_name . '_type'
                                            , array(  
                                                      'type'              => 'select'
                                                
                                                    , 'title'             => __('Deposit type', 'booking')
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'float:left;margin:1px 10px 1px 1px;width:11em;' . $warning_style
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             => $field_value_value_type
                                                    , 'options'           => array(
                                                                                  'fixed'   => $currency 
                                                                                , '%'       => '% ' . __( 'of total cost' ,'booking' )
                                                                                , 'per_day' => $currency . ' / ' . __( 'day', 'booking' )
                                                                                , 'per_night'   => $currency . ' / ' . __( 'night', 'booking' )
                                                                                , 'add_%'   => '% ' . __('as additional sum' ,'booking') 
                                                                                )
                                                )
                                    );                                                  
                            ?></fieldset><?php
                        }
                        ?>
                    </td>    
                </tr><?php                         
            }
        ?></tbody></table><?php
    }
        
    
    /**
	 * Get Array of Saved Advanced Cost of selected booking form
     *  selection of booking form depend from $_GET['booking_form']   
     *  (by default standard form)
     * 
     * @return array                                                            array(
     *                                                                                  Array (
                                                                                            [cust_form2] => Array (
                                                                                                    [90] => 90
                                                                                                    [80] => 80%
                                                                                                )
                                                                                            [visitors] => Array (
                                                                                                    [1] => 1
                                                                                                    [2] => 2
                                                                                                    [3] => 3
                                                                                                    [4] => +4%
                                                                                                )
                                                                                            [term_and_condition] => Array (
                                                                                                    [I_Accept_term_and_conditions] => 11%
                                                                                                )
                                                                                            [make_cleaning] => Array (
                                                                                                    [checkbox] => 22%
                                                                                                )
                                                                                    )
     *                                                                          , 'customform_name' | false if standard form )
     */
    public function get_advanced_cost_arr() {
        
        $custom_form_name = false;
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Standard Form Content                                         - 1
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $booking_form       =  get_bk_option( 'booking_form' );
                 
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');                       // Custom form getting for MU
        if (    ( isset( $_GET['booking_form'] ) ) 
             && ( strtolower( $_GET['booking_form'] ) != 'standard' )           // skip if standard
             && ( $is_can || ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) === 'On' ) )   ){
            
          //////////////////////////////////////////////////////////////////////
          // Custom Forms Content
          //////////////////////////////////////////////////////////////////////

            /**
	 * $custom_forms_arr = get_bk_option( 'booking_forms_extended');
                $custom_forms_arr = maybe_unserialize( $custom_forms_arr );             
                                                                                /*  Array(     [0] => Array (
                                                                                                                    [name] => my-cust-form-2
                                                                                                                    [form] => [calendar]  Custom F
                                                                                                                    [content] =>  Custom Form 2 - Sup
                                                                                                        )
                                                                                                    [1] => Array (
                                                                                                                    [name] => my-new-super-form
                                                                                                                    [form] => [calendar]  ... Sel
                                                                                                                    [content] =>   Times:[rangetime]
                                                                                                        )
                                                                                                )
                                                                                */

            $custom_form_name = $_GET['booking_form'];                                                              // It only compare exist var. to $_GET['booking_form'], and NOT save $_GET value
            
            $booking_form = apply_bk_filter( 'wpdev_get_booking_form', $booking_form, $custom_form_name );          // Its get ONLY form Content
                                                                                /** [calendar]  Custom F .... */                          
        }

        // Get only SELECT, CHCKBOX & RADIO fields
        $selection_fields = wpbc_get_select_checkbox_fields_from_booking_form( $booking_form );                     //wpbc_get_fields_from_booking_form( $booking_form );  
                                                                                /** array(  
                                                                  [0] => 4
                                                                  [1] => Array (
                                                                                [0] => Array ( [0] => [text* name], [1] => [email* email],        [2] => [select visitors class:col-md-1 "1" "2" "3" "4"] ...
                                                                                [1] => Array ( [0] => text* ,       [1] => email*,    [2] => select ,  [3] => select  )
                                                                                [2] => Array( [0] =>  name,         [1] =>  email,          ,[2] =>  visitors,          [3] =>  children )
                                                                                [3] => Array ( [0] => ,             [1] => ,                [2] =>  class:col-md-1 ,    [3] =>  class:col-md-1  )
                                                                                [4] => Array ( [0] =>               [1] =>                  [2] => "1" "2" "3" "4"      [3] => "0" "1" "2" "3" )
                                                                                */
           
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Load Advanced Cost    (  Custom  forms   &   Standard form ) - separately
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ( $custom_form_name === false )
            $saved_advancedcost = get_bk_option( 'booking_advanced_costs_values' );                                      // Standard Form
        else
            $saved_advancedcost = get_bk_option( 'booking_advanced_costs_values_for' . $custom_form_name );              // Custom Form
                                                                                /**  Array (     [visitors] => Array (
                                                                                                        [1] => 1
                                                                                                        [2] => 2
                                                                                                        [3] => 3
                                                                                                        [4] => 4 )
                                                                                                [children] => Array (
                                                                                                        [0] => 100%
                                                                                                        [1] => 100%
                                                                                                        [2] => 100%
                                                                                                        [3] => 100% )
                                                                                                [term_and_condition] => Array (
                                                                                                        [I_Accept_term_and_conditions] => 100%
                                                                                                    )
                                                                                            )
                                                                                */

        $saved_advancedcost = maybe_unserialize( $saved_advancedcost );
               
// debuge('Loaded Saved Advanced Cost', $saved_advancedcost);        
        ////////////////////////////////////////////////////////////////////////
        

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Get Advanced Cost Array      -       with loaded saved advanced cost  values which  assigned to exist fields values in actual  booking form
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $advanced_cost_fields_arr = array();
        
        if ( $selection_fields !== false ) {                                                                            // Generate  Def. Array  of fields with values
            
            foreach ( $selection_fields[1][2] as $field_ind => $field_name) {
                
                $field_name   = trim( $field_name );
                $field_values = trim( $selection_fields[1][4][ $field_ind ] );                                          // "1" "2" "3" "4"     OR   "I Accept term and conditions"     OR    ""
                
                // Get arrray of values: $field_values_arr
                // $field_values_num = preg_match_all( '%\s*"[a-zA-Z0-9.:\s,\[\]/\\-_!@&-=+?~]{0,}"\s*%', $field_values, $field_values_arr) ;
                $field_values_num = preg_match_all( '%\s*"[^"]*"\s*%', $field_values, $field_values_arr) ;              //FixIn:7.0.1.27
//debuge($field_values_arr);                
                $advanced_cost_fields_arr[ $field_name ] = array();
                
                for ( $j = 0; $j < $field_values_num; $j++ ) {
                    
                    // Prepare Field Name from Value
                    $field_value2name = trim( str_replace( '"', '', $field_values_arr[0][$j] ) );
                    $field_value2name = explode( '@@', $field_value2name );
                    $field_value2name = $field_value2name[( count( $field_value2name ) - 1 )];
                    $field_value2name = trim( str_replace( ' ', '_', $field_value2name ) );
//debuge($field_value2name);
                    if ( $field_value2name === '' ) // Its simple checkbox set 0 index
                        $field_value2name = 'checkbox';

                    if (  
                           (!empty( $saved_advancedcost ) )  
                        && ( isset( $saved_advancedcost[ $field_name ] ) )
                        && ( isset( $saved_advancedcost[ $field_name ][ $field_value2name ] ) )
                        )
                        $advanced_cost_fields_arr[ $field_name ][ $field_value2name ] = $saved_advancedcost[ $field_name ][ $field_value2name ];    // Get Saved Value
                    else
                        $advanced_cost_fields_arr[ $field_name ][ $field_value2name ] = '0';                // Default values, if not exist, it yet
                }
            }
        }                                                                       /** Array (
                                                                                            [cust_form2] => Array (
                                                                                                    [90] => 90
                                                                                                    [80] => 80
                                                                                                )
                                                                                            [visitors] => Array (
                                                                                                    [1] => 1
                                                                                                    [2] => 2
                                                                                                    [3] => 3
                                                                                                    [4] => 4
                                                                                                )
                                                                                            [children] => Array (
                                                                                                    [0] => 0%
                                                                                                    [1] => 1%
                                                                                                    [2] => 2%
                                                                                                    [3] => 3%
                                                                                                )
                                                                                            [term_and_condition] => Array (
                                                                                                    [I_Accept_term_and_conditions] => 11%
                                                                                                )
                                                                                            [make_cleaning] => Array (
                                                                                                    [checkbox] => 22%
                                                                                                )
                                                                                    ) */
//debuge( '$advanced_cost_fields_arr', $advanced_cost_fields_arr );  
        
        return array( $advanced_cost_fields_arr, $custom_form_name );
    }
   
    
    /** Show help  section */
    private function show_ac_help() {
        
        ?><strong><?php 
                          _e('Enter additional cost in formats:' ,'booking'); ?></strong>
                          <p class="description"><?php printf(__('For example, if the original cost of the booking is %s, then after applying additional costs the total cost will be folowing' ,'booking'), '<code>$80</code>');?>:</p><?php
                          ?><ul style="list-style: disc outside none;margin: 0 15px;line-height: 1.8em;">
                              <li>
                                  <strong><?php _e('Enter fixed cost' ,'booking');?></strong>:<?php printf(__('%s, then total cost will be %s' ,'booking'), '<code>55</code>', '<code>$80 + $55 = $135</code>');?>
                              </li>
                              <li>
                                  <strong><?php _e('Enter percentage of the entire booking' ,'booking');?></strong>:<?php printf(__('%s, then total cost will be %s' ,'booking'), '<code>200%</code>', '<code>$80 * 200% = $160</code>'); ?>
                              </li>
                              <li>
                                  <strong><?php _e('Enter fixed amount for each selected day' ,'booking');?></strong>:<?php printf(__('%s, then total cost will be (if selected 3 days) %s' ,'booking'), '<code>50/day</code> ' .__('or' ,'booking') . '<code>50/night</code>' , '<code>3 * $80 + 3 * $50 = $390</code>'); ?>                                      
                              </li>
                              <li>
                                  <strong><?php _e('Enter percentage as additional sum, which is based only on original cost and not full sum' ,'booking');?></strong>:<?php printf(__('%s, then total cost will be %s' ,'booking'), '<code>+75%</code>', '<code>80 + 80 * 75% = $140</code>'); ?>
                              </li>                                      
                          </ul>
						  <?php  printf(__('Please check more info about configuration of this cost settings on this %spage%s.' ,'booking')
						  , '<em><a href="https://wpbookingcalendar.com/faq/adding-additional-costs-of-additional-charges/" >','</a></em>'
		                  ); ?>
                          <hr />
                          <ul style="list-style: disc outside none;margin: 0 15px;line-height: 1.8em;">
                              <li>
                                  <strong><?php _e('Use arithmetic expressions in cost configurations, including fields shortcodes and simple mathematics operations' ,'booking');?></strong>:<?php
								  	printf(__('%s, then total cost will be %s' ,'booking'), '<code>( [visitors] * 50 )</code>', '<code>$80 + 2 * $50 = $180</code>');
								  	printf(__('if selected %s' ,'booking'), '<strong>2</strong> <code>[visitors]</code>');
								  	?>
                              </li>
		 				  </ul>
                          <?php  printf(__('Please check more info about configuration of this cost settings on this %spage%s.' ,'booking')
                                  , '<em><a href="https://wpbookingcalendar.com/faq/expresions-in-advanced-costs/" >','</a></em>'
                                ); 
    }
}
add_action('wpbc_menu_created', array( new WPBC_Page_Settings__advancedcost() , '__construct') );                   // Executed after creation of Menu