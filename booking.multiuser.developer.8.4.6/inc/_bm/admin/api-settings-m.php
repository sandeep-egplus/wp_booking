<?php
/**
 * @version     1.0
 * @package     General Settings API - Saving different options
 * @category    Settings API
 * @author      wpdevelop
 *
 * @web-site    https://wpbookingcalendar.com/
 * @email       info@wpbookingcalendar.com 
 * @modified    2016-02-28
 * 
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


/**
	 * Limit available days from today - Settings ( Availbaility ) page
 * 
 * @param array $fields 
 * @return array
 */
function wpbc_settings_calendar_unavailable_days__bm( $fields, $default_options_values ) {
    
    $field_options = array( '' => ' - ' );
    foreach ( range( 365, 1, 1) as $value ) {
        $field_options[ $value ] = $value;
    }
   
    $fields['booking_available_days_num_from_today'] = array(   
                            'type'          => 'select'
                            , 'default'     => $default_options_values['booking_available_days_num_from_today']   //''            
                            , 'title'       => __('Limit available days from today', 'booking')
                            , 'description' => __('Select number of available days in calendar start from today.' ,'booking')
                            , 'options'     => $field_options
                            , 'group'       => 'availability'
                    );

    return $fields; 
}
add_filter('wpbc_settings_calendar_unavailable_days', 'wpbc_settings_calendar_unavailable_days__bm' ,10, 2);


/**
	 * Extend Unavailbale booking days interval - Settings ( Availbaility ) page
 * 
 * @param array $fields 
 * @return array
 */
function wpbc_settings_calendar_extend_unavailable_interval__bm( $fields, $default_options_values ) {
 
    //  Divider  ///////////////////////////////////////////////////////////        
    $fields['hr_after_available_days_num_from_today'] = array( 'type' => 'hr', 'group' => 'availability' );

    
    $field_options = array(
                              '' => array(
                                                  'title' =>  __('None' ,'booking')
                                                , 'attr' =>  array(
                                                                    'id' => 'booking_unavailable_extra_in_out_none'
                                                                )
                                                , 'html' => '<br />'            // Show each option  with  New Line
                                            )
                            , 'm' => array(
                                                  'title' =>  ucfirst(__('minutes' ,'booking')) .  ' / ' . ucfirst(__('hours' ,'booking'))
                                                , 'attr' =>  array(
                                                                    'id' => 'booking_unavailable_extra_in_out_minutes'
                                                                )
                                                , 'html' => '<br />'            // Show each option  with  New Line
                                            )
                            , 'd' => array(
                                                  'title' =>  ucfirst(__('day(s)' ,'booking'))
                                                , 'attr' =>  array(
                                                                    'id' => 'booking_unavailable_extra_in_out_days'
                                                                )
                                                , 'html' => '<br />'            // Show each option  with  New Line
                                            )
                        );
    //  Days  ///////////////////////////////////////////////////////////  
    $fields['booking_unavailable_extra_in_out'] = array(   
                                'type'          => 'radio'
                                , 'default'     => $default_options_values['booking_unavailable_extra_in_out']   //''            
                                , 'title'       => __('Unavailable time before / after booking' ,'booking')
                                , 'description' => '<strong>' . __('Important!' ,'booking') . '</strong> ' 
                                                 . __('This feature is applying only for bookings for specific timeslots, or if activated check in/out time option.' ,'booking')
                                , 'options'     => $field_options
                                , 'group'       => 'availability'
                        );
      
    //  Number of months  //////////////////////////////////////////////////
    $extra_time = array();
    $extra_time[''] = ' - ';
    foreach ( range( 5, 55 , 5 ) as $extra_num) {                                           // Each 5 minutes
        $extra_time[ $extra_num . 'm' ] = $extra_num . ' ' . __( 'minutes', 'booking' );
    }                                    
    $extra_time[ '60' . 'm' ] =  '1 ' . __( 'hour', 'booking' );
    foreach ( range( 65, 115 , 5 ) as $extra_num) {                                         // 1 hour + Each 5 minutes
        $extra_time[ $extra_num . 'm' ] =  '1 ' . __( 'hour', 'booking' ) . ' ' . ($extra_num - 60 ) . ' ' . __( 'minutes', 'booking' );
    }

    foreach ( range( 120, 1380 , 60 ) as $extra_num) {                                      // Each Hour based on minutes
        $extra_time[ $extra_num . 'm' ] = ($extra_num / 60) . ' ' . __( 'hours', 'booking' );
    }
 
    $fields['booking_unavailable_extra_minutes_in'] = array(   
                                'type'          => 'select'
                                , 'default'     => $default_options_values['booking_unavailable_extra_minutes_in']   //''            
                                , 'title'       => __('Before booking' ,'booking')
                                , 'description' => __('Select unavailable time interval.' ,'booking')
                                , 'options'     => $extra_time
                                , 'group'       => 'availability'
                                , 'tr_class'    => 'wpbc_unavailable_extra_minutes_in_out wpbc_sub_settings_grayed'
                        );
    $fields['booking_unavailable_extra_minutes_out'] = array(   
                                'type'          => 'select'
                                , 'default'     => $default_options_values['booking_unavailable_extra_minutes_out']   //''            
                                , 'title'       => __('After booking' ,'booking')
                                , 'description' => __('Select unavailable time interval.' ,'booking')
                                , 'options'     => $extra_time
                                , 'group'       => 'availability'
                                , 'tr_class'    => 'wpbc_unavailable_extra_minutes_in_out wpbc_sub_settings_grayed'
                        );
    
    $extra_time = array();
    $extra_time[''] = ' - ';
    foreach ( range( 1, 30 , 1 ) as $extra_num) {                                           // Each Day
        $extra_time[ $extra_num . 'd' ] = $extra_num . ' ' . __( 'day(s)', 'booking' );
    }
    $fields['booking_unavailable_extra_days_in'] = array(   
                                'type'          => 'select'
                                , 'default'     => $default_options_values['booking_unavailable_extra_days_in']   //''            
                                , 'title'       => __('Before booking' ,'booking')
                                , 'description' => __('Select unavailable time interval.' ,'booking')
                                , 'options'     => $extra_time
                                , 'group'       => 'availability'
                                , 'tr_class'    => 'wpbc_unavailable_extra_days_in_out wpbc_sub_settings_grayed'
                        );    
    $fields['booking_unavailable_extra_days_out'] = array(   
                                'type'          => 'select'
                                , 'default'     => $default_options_values['booking_unavailable_extra_days_out']   //''            
                                , 'title'       => __('After booking' ,'booking')
                                , 'description' => __('Select unavailable time interval.' ,'booking')
                                , 'options'     => $extra_time
                                , 'group'       => 'availability'
                                , 'tr_class'    => 'wpbc_unavailable_extra_days_in_out wpbc_sub_settings_grayed'
                        );
    
    return $fields; 
}
add_filter('wpbc_settings_calendar_extend_unavailable_interval', 'wpbc_settings_calendar_extend_unavailable_interval__bm' ,10, 2);

 

/**
	 * Showing cost in day cell - Settings ( Calendar ) page
 * 
 * @param array $fields 
 * @return array
 */
function wpbc_settings_calendar_showing_info_in_cal__bm( $fields, $default_options_values ) {
    
        //  Divider  ///////////////////////////////////////////////////////////////        
    $fields['hr_calendar_before_show_cost_in_date_cell'] = array( 'type' => 'hr', 'group' => 'calendar' );

    $fields['booking_is_show_cost_in_date_cell'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_is_show_cost_in_date_cell']   //'Off'            
                            , 'title'       =>  __('Showing cost in date cell' ,'booking')
                            , 'label'       => sprintf(__(' Check this box to display the %sdaily cost at the date cells%s in the calendar(s).' ,'booking'),'<b>','</b>')
                            , 'description' => ''
                            , 'group'       => 'calendar'
                            , 'tr_class'    => 'wpbc_show_cost_in_day_cell'
        );       
    
    
    
    //  Selections currency  symbol  ///////////////////////////////////////////
    $fields['booking_cost_in_date_cell_currency_html_prefix'] = array(   
                                'type'          => 'pure_html'
                                , 'group'       => 'calendar'
                                , 'html'        => '<tr valign="top" class="wpbc_tr_set_gen_booking_cost_in_date_cell_currency 
                                                                            wpbc_show_cost_in_day_cell_currency wpbc_sub_settings_grayed 
                                                                            ">
                                                        <th scope="row">'.
                                                            WPBC_Settings_API::label_static( 'set_gen_booking_cost_in_date_cell_currency'
                                                                , array(   'title'=> __('Currency symbol' ,'booking'), 'label_css' => 'margin: 0.25em 0 !important;vertical-align: middle;' ) )
                                                        .'</th>
                                                        <td><fieldset>'
                        );        
    $currency_formats =  array( '&#36;', '&#8364;', '&#163;', '&#165;' );
    $field_options    = array();
    foreach ( $currency_formats as $format) {
        $field_options[ $format ] = array( 'title' => $format );
    }
    $field_options['custom'] =  array( 'title' =>  __('Custom' ,'booking') . ':', 'attr' =>  array( 'id' => 'cost_in_date_cell_currency_selection_custom' ) );
            
    $fields['booking_cost_in_date_cell_currency_selection'] = array(   
                                'type'          => 'radio'
                                , 'default'     => '&#36;'            
                                , 'title'       => ''
                                , 'description' => '' 
                                , 'options'     => $field_options
                                , 'group'       => 'calendar'                                
                                , 'only_field' => true
                        );
    
    $booking_cost_in_date_cell_currency = get_bk_option( 'booking_cost_in_date_cell_currency');       
    $fields['booking_cost_in_date_cell_currency'] = array(  
                            'type'          => 'text'
                            , 'default'     => $default_options_values['booking_cost_in_date_cell_currency']   //'&#36;'
                            , 'value'       => htmlentities( $booking_cost_in_date_cell_currency )      // Display value of this field in specific way
                            , 'group'       => 'calendar'
                            , 'placeholder' => '&#36;'
                            , 'css' => 'width:5em;'
                            , 'only_field' => true
        );    
    
    $fields['booking_cost_in_date_cell_currency_html_sufix'] = array(   
                                'type'          => 'pure_html'
                                , 'group'       => 'calendar'
                                , 'html'        => '          <p class="description">' 
                                                                . sprintf(__('Type your %scurrency symbol%s to display near daily cost in date cells. %sDocumentation on currency symbols%s' ,'booking'),'<b>','</b>','<a href="http://dev.w3.org/html5/html-author/charref" target="_blank">','</a>')
                                                        . '   </p>
                                                           </fieldset>
                                                        </td>
                                                    </tr>'            
                        );        
    
    
    
    // Showing cost in tooltip
    $fields['booking_is_show_cost_in_tooltips'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_is_show_cost_in_tooltips']   //'Off'            
                            , 'title'       =>  __('Showing cost in tooltip' ,'booking')
                            , 'label'       => __(' Check this box to display the daily cost with a tooltip when mouse hovers over each day on the calendar(s).' ,'booking')
                            , 'description' => ''
                            , 'group'       => 'calendar'
                            , 'tr_class'    => ''
        );       
    $fields['booking_highlight_cost_word'] = array(   
                                'type'          => 'text'
                                , 'default'     => $default_options_values['booking_highlight_cost_word']   //__('Cost: ' ,'booking')
                                , 'title'       => __('Cost Title' ,'booking')
                                , 'placeholder' => __('Cost: ' ,'booking')
                                , 'description' => sprintf(__('Type your %scost%s description' ,'booking'),'<b>','</b>')
                                //,'description_tag' => 'span'
                                , 'class'       => 'regular-text'
                                , 'group'       => 'calendar'
                                , 'tr_class'    => 'wpbc_show_cost_in_tooltip wpbc_sub_settings_grayed'
                        );
    

    return $fields;
}
add_filter('wpbc_settings_calendar_showing_info_in_cal', 'wpbc_settings_calendar_showing_info_in_cal__bm' ,10, 2);


//FixIn: 8.1.3.15
/**
	 * Showing Title for Timeslots  - Settings ( Calendar ) page
 *
 * @param array $fields
 * @return array
 */
function wpbc_settings_calendar_title_for_timeslots__bm( $fields, $default_options_values ) {


    // Showing booking details in tooltip
    $fields['booking_is_show_booked_data_in_tooltips'] = array(
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_is_show_booked_data_in_tooltips']   //'Off'
                            , 'title'       =>  __('Show booking details in tooltip' ,'booking')
                            , 'label'       => sprintf(__('Check this box to display booking details with a tooltip, when mouse hovers over each day on the calendar(s). %sIts works only for bookings for specific timeslot(s)!%s' ,'booking'),'<b>','</b>','<b>','</b>')
                            , 'description' => ''
                            , 'group'       => 'calendar'
                            , 'tr_class'    => ''
        );
    $fields['booking_booked_data_in_tooltips'] = array(
                                'type'          => 'text'
                                , 'default'     => $default_options_values['booking_booked_data_in_tooltips']
								, 'title'         => __('Booking details' ,'booking')
                            	, 'description'   => sprintf(__( 'You can use the shortcodes from the bottom form of Settings Fields page.' ,'booking'),'<b>','</b>','<b>','</b>')
                                , 'placeholder' => '[name] [secondname]'
                                //,'description_tag' => 'span'
                                , 'class'       => 'regular-text'
                                , 'group'       => 'calendar'
                                , 'tr_class'    => 'wpbc_show_booked_data_in_tooltips wpbc_sub_settings_grayed'
                        );
    $fields['booking_booked_data_in_tooltips_help'] = array(
                                'type'          => 'html'
							    , 'cols'        => 1
                                , 'group'       => 'calendar'
                                , 'tr_class'    => 'wpbc_show_booked_data_in_tooltips wpbc_sub_settings_grayed'
								, 'html'     =>   '<div class="wpbc-settings-notice notice-info" style="text-align:left;">'
												. '<strong>' . __('Note!' ,'booking') . '</strong> '
												. sprintf( __('This option can impact to speed of page loading.' ,'booking') )
										        . '</div>'
                        );

    return $fields;
}
add_filter('wpbc_settings_calendar_title_for_timeslots', 'wpbc_settings_calendar_title_for_timeslots__bm' ,10, 2);



/**
	 * Override VALIDATED fields BEFORE saving to DB
 * Description:
 * cost_in_date_cell_currency_selection... does not exist in the DB
 * they  exist  only in settings page, so  need to  get  values from these options and ovverride values.
 * 
 * @param array $validated_fields
 */
function wpbc_settings_validate_fields_before_saving__bm( $validated_fields ) {
    
    unset( $validated_fields[ 'booking_cost_in_date_cell_currency_selection' ] );    
    
    return $validated_fields;
}
add_filter( 'wpbc_settings_validate_fields_before_saving', 'wpbc_settings_validate_fields_before_saving__bm', 10, 1 );   // Hook for validated fields.


/**
	 * JavaScript     at Bottom of Settings page
 * 
 * @param string $page_tag
 */
function wpbc_settings_enqueue_js__bm( $page_tag, $active_page_tab, $active_page_subtab ) {

    // Check if this correct  page /////////////////////////////////////////////
    
    if ( !(
               ( $page_tag == 'wpbc-settings')                                      // Load only at 'wpbc-settings' menu
            && (  ( ! isset( $_GET['tab'] ) ) || ( $_GET['tab'] == 'general' )  )   // At ''general' tab
          )
      ) return;
  
    // JavaScript //////////////////////////////////////////////////////////////    
    $js_script = '';

    $booking_cost_in_date_cell_currency = get_bk_option( 'booking_cost_in_date_cell_currency');       
    
    // Function  to  load on initial stage of page loading, set  correct  value of text  custom currncy  and select  correct radio button.
    $js_script .= " function wpbc_check_radio_for_cost_in_date_cell_currency() {
                        
                        // Select by  default custom  value, later  check all other predefined values
                        jQuery( '#cost_in_date_cell_currency_selection_custom' ).prop('checked', true);
                        
                        jQuery('input[name=\"set_gen_booking_cost_in_date_cell_currency_selection\"]').each(function() {
                           var radio_button_value = jQuery( this ).val()
                           var encodedStr = radio_button_value.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                                                                                        return '&#'+i.charCodeAt(0)+';';
                                                                                    });
                           if ( encodedStr == '". $booking_cost_in_date_cell_currency ."' ) {
                                jQuery( this ).prop('checked', true);                     
                           }
                        });
                        
                    }
                    wpbc_check_radio_for_cost_in_date_cell_currency();
                    jQuery('#set_gen_booking_cost_in_date_cell_currency').val('". $booking_cost_in_date_cell_currency ."');
                        
                    // Hide Currency Symbol sub-settings,  if the checkbox of showing currency  is not checked
                    if ( ! jQuery('#set_gen_booking_is_show_cost_in_date_cell').is(':checked') ) {   
                        jQuery('.wpbc_show_cost_in_day_cell_currency').addClass('hidden_items'); 
                    }

                    // Hide Currency Symbol sub-settings,  if the checkbox of showing currency  is not checked
                    if ( ! jQuery('#set_gen_booking_is_show_cost_in_tooltips').is(':checked') ) {   
                        jQuery('.wpbc_show_cost_in_tooltip').addClass('hidden_items'); 
                    }
    
                    ";
    
    // On click specific radio  button currency, set correct  value of custom text  currency
    $js_script .= " jQuery('input[name=\"set_gen_booking_cost_in_date_cell_currency_selection\"]').on( 'change', function(){    
                            if (  ( this.checked ) && ( jQuery(this).val() != 'custom' )  ){ 
                               
                                jQuery('#set_gen_booking_cost_in_date_cell_currency').val( jQuery(this).val().replace(/[\u00A0-\u9999<>\&]/gim, 
                                    function(i) {
                                        return '&#'+i.charCodeAt(0)+';';
                                    }) 
                                );
                            }                            
                        } ); "; 
    
    //Check chnaging text in text field of custom  currency - select custom radio button.
    $js_script .= " jQuery('#set_gen_booking_cost_in_date_cell_currency').on( 'change', function(){    
                            jQuery( '#cost_in_date_cell_currency_selection_custom' ).prop('checked', true);
                        } ); ";        
    
    // Hide or show Currency Symbol selection subsettings,  if the checkbox of showing currency  checked or no.
    $js_script .= " jQuery('#set_gen_booking_is_show_cost_in_date_cell').on( 'change', function(){    
                            if ( this.checked ) { 
                                jQuery('.wpbc_show_cost_in_day_cell_currency').removeClass('hidden_items');     // Show 
                            } else {
                                jQuery('.wpbc_show_cost_in_day_cell_currency').addClass('hidden_items');        // Hide 
                            }
                        } ); ";        
    
    // Hide or show Cost Word in tooltip subsettings,  if the checkbox checked
    $js_script .= " jQuery('#set_gen_booking_is_show_cost_in_tooltips').on( 'change', function(){    
                            if ( this.checked ) { 
                                jQuery('.wpbc_show_cost_in_tooltip').removeClass('hidden_items');               // Show 
                            } else {
                                jQuery('.wpbc_show_cost_in_tooltip').addClass('hidden_items');                  // Hide 
                            }
                        } ); ";        
    
    
    // Hide sub-settings, depend from  selection of radio-button "unavailable_extra_in_out" ( "Unavailable time before / after booking" )
    $js_script .= " // Hide Days and Minutes,  if None selected 
                    if ( jQuery('#booking_unavailable_extra_in_out_none').is(':checked') ) {   
                        jQuery('.wpbc_unavailable_extra_minutes_in_out,.wpbc_unavailable_extra_days_in_out').addClass('hidden_items'); 
                    }
                    // Hide Days,  if Minutes selected 
                    if ( jQuery('#booking_unavailable_extra_in_out_minutes').is(':checked') ) {   
                        jQuery('.wpbc_unavailable_extra_days_in_out').addClass('hidden_items'); 
                    }
                    // Hide Minutes,  if Days selected 
                    if ( jQuery('#booking_unavailable_extra_in_out_days').is(':checked') ) {   
                        jQuery('.wpbc_unavailable_extra_minutes_in_out').addClass('hidden_items'); 
                    }
                    ";        
    // On click specific radio  button currency, set correct  value of custom text  currency
    $js_script .= " jQuery('input[name=\"set_gen_booking_unavailable_extra_in_out\"]').on( 'change', function(){    
            
                        jQuery('.wpbc_unavailable_extra_minutes_in_out,.wpbc_unavailable_extra_days_in_out').addClass('hidden_items'); 
                        
                        if (  ( this.checked ) && ( jQuery(this).val() == 'm' )  ){ 
                           jQuery('.wpbc_unavailable_extra_minutes_in_out').removeClass('hidden_items');     
                        }                            
                        if (  ( this.checked ) && ( jQuery(this).val() == 'd' )  ){ 
                           jQuery('.wpbc_unavailable_extra_days_in_out').removeClass('hidden_items');     
                        }                            
                    } ); "; 
    


	// FixIn: 8.1.3.15
    // Hide Booking Details for tooltip sub-settings, if the checkbox have not checked
    $js_script .= " 
                    // Hide Availability Word sub-settings, if the checkbox have not checked
                    if ( ! jQuery('#set_gen_booking_is_show_booked_data_in_tooltips').is(':checked') ) {   
                        jQuery('.wpbc_show_booked_data_in_tooltips').addClass('hidden_items'); 
                    }
    
                    ";

    // Hide or show Booking Details in tooltip subsettings,  if the checkbox checked
    $js_script .= " jQuery('#set_gen_booking_is_show_booked_data_in_tooltips').on( 'change', function(){    
                            if ( this.checked ) { 
                                jQuery('.wpbc_show_booked_data_in_tooltips').removeClass('hidden_items');               // Show 
                            } else {
                                jQuery('.wpbc_show_booked_data_in_tooltips').addClass('hidden_items');                  // Hide 
                            }
                        } ); ";

    wpbc_enqueue_js( $js_script );                                              // Eneque JS to  the footer of the page.
}
add_action( 'wpbc_after_settings_content',  'wpbc_settings_enqueue_js__bm', 10, 3 );


////////////////////////////////////////////////////////////////////////////////
// Booking Resources Table
////////////////////////////////////////////////////////////////////////////////


/**
	 * Add Column Header to Resources Table -- Cost Fields
 * 
 * @param array $columns
 * @return array
 */
function wpbc_resources_table_header__customform_title__bm( $columns ) {    

    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );
    if ( ( ! $is_can ) && ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) !== 'On' ) )              // Not Super and CF CONST do not active
        return $columns;
    
    $columns[ 'form' ] = array(   
                                  'title' => __( 'Default Form', 'booking' ) 
                                , 'style' => 'width:12em;text-align:center'
                                , 'class' => 'wpbc_hide_mobile_xs'
                                // , 'sortable' => true 
                        );
    return $columns;
}
add_filter( 'wpbc_resources_table_header__customform_title', 'wpbc_resources_table_header__customform_title__bm', 10, 1 );   // Hook for validated fields.



/**
	 * Show Column in Resources Table - Edit Cost
 * 
 * @param int $row_num
 * @param array $resource
 */
function wpbc_resources_table_show_col__customform_field__bm( $row_num, $resource ) {

		/*
		Array
        (
            [booking_type_id] => 1
            [title] => Default
            [users] => 1
            [import] =>
            [export] =>
            [cost] => 25
            [default_form] => standard
            [prioritet] => 0
            [parent] => 0
            [visitors] => 1
            [id] => 1
            [count] => 6
        )*/

    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );
    if ( ( ! $is_can ) && ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) !== 'On' ) )              // Not Super and CF CONST do not active
        return;
    
    ////////////////////////////////////////////////////////////////////////////

	if ( class_exists( 'wpdev_bk_multiuser' ) ) {                                    // Check what to show in MU		//FixIn: 8.1.3.19
		$is_booking_resource_user_super_admin = apply_bk_filter('is_user_super_admin',  $resource[ 'users' ] );
	} else {
		$is_booking_resource_user_super_admin = true;
	}

	if ( ! $is_booking_resource_user_super_admin ) {   								    								// Check what to show in MU	for ( Regular Users )

		if ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) !== 'On' ) {									// Only  standard forms for regular  users
			$booking_forms_extended = array();
		} else {																										// Get custom forms,  as well
			$booking_forms_extended = get_user_option( 'booking_forms_extended', $resource[ 'users' ] );
		}

	} else {
		$booking_forms_extended = get_bk_option( 'booking_forms_extended' );
	}

	if ( ! empty( $booking_forms_extended ) ) {
		$booking_forms_extended = maybe_unserialize( $booking_forms_extended );
	}


    ?><td class="wpbc_hide_mobile_xs"><?php                                                                 // DropDown list with Custom Forms 
    
    $form_options = array();
    if (  is_array(  $booking_forms_extended ) )
        foreach ( $booking_forms_extended as $cust_form ) {

            $form_options[ $cust_form['name'] ] = array(  
                                                    'title'      => apply_bk_filter( 'wpdev_check_for_active_language', $cust_form['name'] )
                                                    , 'selected' => ( (isset( $resource['default_form'] )) && ($resource['default_form'] == $cust_form['name']) ) ? true : false
                                                );
        }
    
    ?><select autocomplete="off" id="booking_resource_default_form_<?php echo $resource['id' ]; ?>" 
                                 name="booking_resource_default_form_<?php echo $resource['id' ]; ?>"   
                                 style="width:100%;"
        ><?php  
        
        ?><option value="standard" style="padding:3px;border-bottom: 1px dashed #ccc;" ><?php echo esc_attr( __('Standard', 'booking') );  ?></option><?php 
        
        ?><optgroup label="<?php  echo esc_attr( '&nbsp;' . __('Custom Forms' ,'booking') ); ?>"><?php 

            foreach ( $form_options as $option_value => $option_data ) {

                ?><option value="<?php echo esc_attr( $option_value ); ?>" 
                    <?php selected(  $option_data['selected'], true ); ?> 
                ><?php echo esc_attr( $option_data['title'] ); ?></option><?php 
            }
        
        ?></optgroup><?php     
        
    ?></select><?php    
    
    ?></td><?php 
}
add_action( 'wpbc_resources_table_show_col__customform_field',  'wpbc_resources_table_show_col__customform_field__bm', 10, 2 );


/**
	 * Update SQL during Saving data at Booking > Resources page
 * 
 * @param array $sql            array(
                                                            'sql' => array(
                                                                                  'start'   => "UPDATE {$wpdb->prefix}bookingtypes SET "
                                                                                , 'params' => array( 'title = %s' )
                                                                                , 'end'    => " WHERE booking_type_id = %d"
                                                                        )
                                                            , 'values' => array(
                                                                                  $validated_value 
                                                                        )
                                                        )
 * @param int $resource_id
 * @param array $resource
 * @return string - updated SQL
 */
function wpbc_resources_table__update_sql_array__bm( $sql, $resource_id, $resource ) {
    
    
    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );
    if ( ( ! $is_can ) && ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) !== 'On' ) )              // Not Super and CF CONST do not active
        return $sql;
    
    // Validate Title
    $validated_default_form = WPBC_Settings_API::validate_text_post_static( 'booking_resource_default_form_' . $resource_id );
    
    
    $sql['sql']['params'][] = 'default_form = %s';
    $sql['values'][]        = $validated_default_form;
    
    return $sql;
}
add_filter( 'wpbc_resources_table__update_sql_array', 'wpbc_resources_table__update_sql_array__bm', 10, 3 );   // Hook for validated fields.


/**
	 * Update SQL during Inserting data at Booking > Resources page
 * 
 * @param array $sql                                array(
                                                            'sql'       => array(
                                                                                  'start'      => "INSERT INTO {$wpdb->prefix}bookingtypes "
                                                                                , 'params'     => array( 'title' )    
                                                                                , 'param_types' => array( '%s' )    
                                                                        )
                                                            , 'values'  => array( $validated_title . $sufix )
                                                    )
 * @param array $params                             array( 'sufix' => $sufix )
 * @return array - updated SQL
 */
function wpbc_resources_table__add_new_sql_array__bm( $sql, $params ) {  
    
    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );
    if ( ( ! $is_can ) && ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) !== 'On' ) )              // Not Super and CF CONST do not active
        return $sql;
        
    // Validate custom booking Form
    $validated_default_form = WPBC_Settings_API::validate_text_post_static( 'select_booking_form' );
    
    $sql['sql']['params'][]      = 'default_form';
    $sql['sql']['param_types'][] = '%s';
    $sql['values'][]        = $validated_default_form;
    
    return $sql;
}
add_filter( 'wpbc_resources_table__add_new_sql_array', 'wpbc_resources_table__add_new_sql_array__bm', 10, 2 );   // Hook for validated fields.




////////////////////////////////////////////////////////////////////////////////
// Get & Set Booking Resource Meta data
////////////////////////////////////////////////////////////////////////////////

global $wpbc_cache_booking_types_meta;

/**
	 * Get meta data for booking resource, like Rates, Availability or "Valuation days"  [ USE CACHE ]
 * 
 * @param type      $resource_id        - ID of booking resource
 * @param string    $meta_key           - type of meat,  for example: 'availability' | 'rates' | 'costs_depends' | 'fixed_deposit'
 * @return array
 */
function wpbc_get_resource_meta( $resource_id, $meta_key ) {
    global $wpdb;

    if ( IS_USE_WPDEV_BK_CACHE ) {                                              // Use Cache        
        global $wpbc_cache_booking_types_meta;
        
        if ( ! isset( $wpbc_cache_booking_types_meta ) )    $wpbc_cache_booking_types_meta = array();
        
        if ( ! isset( $wpbc_cache_booking_types_meta[$meta_key] ) ) {
            
            $wpbc_cache_booking_types_meta[$meta_key] = array();

            $result = $wpdb->get_results( $wpdb->prepare( "SELECT type_id, meta_id as id, meta_value as value FROM {$wpdb->prefix}booking_types_meta WHERE  meta_key = %s ", $meta_key ) );

            foreach ( $result as $value ) {
                
                if ( ! isset( $wpbc_cache_booking_types_meta[$meta_key][$value->type_id] ) )    $wpbc_cache_booking_types_meta[$meta_key][$value->type_id] = array();
                
                $wpbc_cache_booking_types_meta[$meta_key][$value->type_id][] = $value;
            }
            if ( ! isset( $wpbc_cache_booking_types_meta[$meta_key][$resource_id] ) )   return array();
            
            return $wpbc_cache_booking_types_meta[$meta_key][$resource_id];
            
        } else {
            
            if ( ! isset( $wpbc_cache_booking_types_meta[$meta_key][$resource_id] ) )   return array();
            
            return $wpbc_cache_booking_types_meta[$meta_key][$resource_id];
        }
        
    } else {                                                                    // Get info  1st  time
        
        $result = $wpdb->get_results( 
                                        $wpdb->prepare(   "SELECT meta_id as id, meta_value as value FROM {$wpdb->prefix}booking_types_meta WHERE type_id = %d AND meta_key = %s "
                                                        , $resource_id, $meta_key ) 
                                    );
        return $result;
    }
}



/**
	 * Save meta data for booking resource, like Rates, Availability or "Valuation days"  [ update  CACHE ]
 * 
 Example: wpbc_save_resource_meta( $resource_id, 'availability', $availability );
 */
function wpbc_save_resource_meta( $resource_id, $meta_key, $data ) {
    
    $data = maybe_serialize( $data );
    
    global $wpdb;

    $result = $wpdb->get_results(   $wpdb->prepare( 
                                                    "SELECT count(type_id) as cnt FROM {$wpdb->prefix}booking_types_meta WHERE type_id = %d AND meta_key = %s "
                                                    , $resource_id
                                                    , $meta_key 
                                                ) 
                                );                                                    
                                                    
    if (  ( ! empty($result) ) && ( $result[0]->cnt > 0 )  ) {
        
        $sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking_types_meta SET meta_value = %s WHERE type_id = %d  AND meta_key = %s " 
                               , $data, $resource_id, $meta_key
                            );
        
    } else {
        $sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}booking_types_meta ( type_id, meta_key, meta_value ) VALUES ( %d, %s, %s ) " 
                               ,$resource_id, $meta_key, $data
                            );
    }

    if ( false === $wpdb->query( $sql )  ){ debuge_error( 'Error saving to DB' ,__FILE__ , __LINE__); return false; }         // Save to DB

    
    
    // Update Cache data
    global $wpbc_cache_booking_types_meta;
    unset( $wpbc_cache_booking_types_meta[$meta_key] );

    return true;
}
