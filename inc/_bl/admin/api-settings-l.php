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
	 * Check in/out  - Settings ( Calendar ) page
 * 
 * @param array $fields
 * @return array
 */
function wpbc_settings_calendar_check_in_out_times__bl( $fields, $default_options_values ) {
    
    //  Divider  ///////////////////////////////////////////////////////////////        
    $fields['hr_calendar_before_rcheck_in_available_for_parents'] = array( 'type' => 'hr', 'group' => 'calendar' , 'tr_class'    => 'wpbc_recurrent_check_in_out_time_slots wpbc_check_in_out_time_slots wpbc_sub_settings_grayed');
    
    $fields['booking_check_in_available_for_parents'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_check_in_available_for_parents']   //'Off'            
                            , 'title'       =>  ''
                            , 'label'       => __('Use "Check In" date as available in calendar for booking resources with capacity higher then 1 for search results' ,'booking')
                            , 'description' => ''
                            , 'group'       => 'calendar'
                            , 'tr_class'    => 'wpbc_recurrent_check_in_out_time_slots wpbc_check_in_out_time_slots wpbc_sub_settings_grayed'
        );    
    $fields['booking_check_out_available_for_parents'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_check_out_available_for_parents']   //'Off'            
                            , 'title'       =>  ''
                            , 'label'       => __('Use "Check Out" date as available in calendar for booking resources with capacity higher then 1 search results' ,'booking')
                            , 'description' => ''
                            , 'group'       => 'calendar'
                            , 'tr_class'    => 'wpbc_recurrent_check_in_out_time_slots wpbc_check_in_out_time_slots wpbc_sub_settings_grayed'
        );    
    
    return $fields;
}
add_filter('wpbc_settings_calendar_check_in_out_times', 'wpbc_settings_calendar_check_in_out_times__bl' ,10, 2);            // Check In/Out Times


/**
	 * Showing availability in tooltip - Settings ( Calendar ) page
 * 
 * @param array $fields 
 * @return array
 */
function wpbc_settings_calendar_showing_info_in_cal__bl( $fields, $default_options_values ) {
    
    // Showing availability in tooltip
    $fields['booking_is_show_availability_in_tooltips'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_is_show_availability_in_tooltips']   //'Off'            
                            , 'title'       =>  __('Show availability in tooltip' ,'booking')
                            , 'label'       => __('Check this box to display the available number of booking resources with a tooltip, when mouse hovers over each day on the calendar(s).' ,'booking')
                            , 'description' => ''
                            , 'group'       => 'calendar'
                            , 'tr_class'    => ''
        );       
    $fields['booking_highlight_availability_word'] = array(   
                                'type'          => 'text'
                                , 'default'     => $default_options_values['booking_highlight_availability_word']   //__('Available: ' ,'booking')
                                , 'placeholder' => __('Available: ' ,'booking')
                                , 'title'       => __('Availability Title' ,'booking')                                
                                , 'description' => sprintf(__('Type your %savailability%s description' ,'booking'),'<b>','</b>')
                                //,'description_tag' => 'span'
                                , 'class'       => 'regular-text'
                                , 'group'       => 'calendar'
                                , 'tr_class'    => 'wpbc_show_availability_in_tooltips wpbc_sub_settings_grayed'
                        );

    return $fields;
}
add_filter('wpbc_settings_calendar_showing_info_in_cal', 'wpbc_settings_calendar_showing_info_in_cal__bl' ,10, 2);


/**
	 * Use pending days as available - Settings ( Advanced ) page
 * 
 * @param array $fields 
 * @return array
 */
function wpbc_settings_pending_days_as_available__bl( $fields, $default_options_values ) {
    
    // Showing availability in tooltip
    $fields['booking_is_show_pending_days_as_available'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_is_show_pending_days_as_available']   //_'Off'            
                            , 'title'       =>  __('Use pending days as available' ,'booking')
                            , 'label'       => sprintf(__('Check this box if you want to show the pending days as available in calendars' ,'booking') )
                            , 'description' => ''
                            , 'group'       => 'advanced'
                            , 'tr_class'    => ''
        );       
    $fields['booking_auto_cancel_pending_bookings_for_approved_date'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_auto_cancel_pending_bookings_for_approved_date']   //'Off'            
                            , 'title'       => __('Auto-cancel bookings' ,'booking')
                            , 'label'       => __('Auto Cancel all pending bookings for the specific date(s), if some booking is approved for these date(s)' ,'booking')
                            , 'description' => ''
                            , 'group'       => 'advanced'
                            , 'tr_class'    => 'wpbc_pending_days_as_available_sub_settings wpbc_sub_settings_grayed'
                        );
        return $fields;
?>
<tr>
    <td style="padding:0px;" colspan="2">
        <div style="margin: 0px 0 10px 50px;">
            <table id="togle_settings_show_pending_days_as_available" style="width:100%;<?php if ($is_show_pending_days_as_available != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">                                       
                <tr valign="top">
                    <td scope="row">
                        <label for="booking_auto_cancel_pending_bookings_for_approved_date">

                            <input <?php if ($booking_auto_cancel_pending_bookings_for_approved_date == 'On') echo "checked"; ?>  
                                   value="<?php echo $booking_auto_cancel_pending_bookings_for_approved_date; ?>"  type="checkbox" 
                                   name="booking_auto_cancel_pending_bookings_for_approved_date" id="booking_auto_cancel_pending_bookings_for_approved_date" 
                                   onclick="javascript: if (this.checked) alert('<?php printf(__('Warning!!! After you approved the specific booking(s), all your pending bookings of the same booking resource as an approved booking for the dates, which are intersect with dates of approved booking, will be automatically canceled!' ,'booking') );?>');"                                   
                             />                                 
                             <?php _e('Auto Cancel all pending bookings for the specific date(s), if some booking is approved for these date(s)' ,'booking'); ?>
                        </label>                                 
                    </td>
                </tr>
            </table>
        </div>                
    </td>
 </tr>

 <?php
}
add_filter('wpbc_settings_pending_days_as_available', 'wpbc_settings_pending_days_as_available__bl' ,10, 2);


/**
	 * Set capacity based on number of visitors - Settings ( Advanced ) page
 * 
 * @param array $fields 
 * @return array
 */
function wpbc_settings_capacity_based_on_visitors__bl( $fields, $default_options_values ) {
    
    //  Divider  ///////////////////////////////////////////////////////////////        
    $fields['hr_calendar_before_is_use_visitors_number_for_availability'] = array( 'type' => 'hr', 'group' => 'advanced' );
    
    // Showing availability in tooltip
    $fields['booking_is_use_visitors_number_for_availability'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_is_use_visitors_number_for_availability']   //'Off'            
                            , 'title'       =>  __('Set capacity based on number of visitors' ,'booking')
                            , 'label'       => sprintf(__('Check this box if you want total availability (daily capacity) to depend on the number of selected visitors.' ,'booking'),  '<code>[select visitors "1" "2" 3" "4"]</code>')
                            , 'description' => '<strong>' . __('Important!' ,'booking') . '</strong> ' . sprintf( __('Please read more info about configuration of this parameter %shere%s' ,'booking'), '<a href="https://wpbookingcalendar.com/faq/booking-resource/" target="_blank">', '</a>' )
                            , 'description_tag' => 'p'
                            , 'group'       => 'advanced'
        );       
    
    
                       
    $fields['booking_availability_based_on_prefix'] = array(   
                                'type'          => 'pure_html'
                                , 'group'       => 'advanced'
                                , 'html'        => '<tr valign="top" class="wpbc_tr_set_gen_booking_availability_based_on wpbc_availability_based_on_sub_settings wpbc_sub_settings_grayed">                                                        
                                                        <td colspan="2"><fieldset>'
                        );          
        
    $field_options = array(
                                'items'     => array( 'title' => __( 'Add tooltip on calendar(s) to show availability based on the number of available booking resource items remaining for each day.'  ,'booking')
                                                    , 'attr' => array( 'id' => 'availability_based_on_items' ) 
                                                    , 'html' => '<p class="description" style=" line-height: 1.6em;margin: -15px 0 10px 40px"><strong>' . __('Note' ,'booking') . ':</strong> ' 
                                                                . sprintf( __( 'Be sure to match the maximum number of visitors for the %sone booking resource%s with the number of visitors specified on the booking form.' ,'booking'),'<strong>','</strong>')
                                                                . '</p><hr/>'
                                                    )                                                                  
                              , 'visitors'  => array( 'title' => __( 'Display tooltip on calendar(s) to show availability based on total (fixed) number of visitors for the resource, which can be at free booking resource items.' ,'booking')
                                                    , 'attr' => array( 'id' => 'availability_based_on_visitors' ) 
                                                    , 'html' => '<p class="description" style=" line-height: 1.6em;margin: -15px 0 10px 40px"><strong>' . __('Note' ,'booking') . ':</strong> ' 
                                                                . sprintf( __( 'Be sure to match the maximum number of visitors for %sall booking resources%s with the number of visitors specified on the booking form.'  ,'booking'),'<strong>','</strong>')
                                                                . '</p>'
                                                    )                              
                        );        
    $fields['booking_availability_based_on'] = array(   
                                'type'          => 'radio'
                                , 'default'     => $default_options_values['booking_availability_based_on']   //'items'            
                                , 'title'       => ''
                                , 'description' => ''
                                , 'options'     => $field_options
                                , 'group'       => 'advanced'
                                , 'tr_class'    => 'wpbc_availability_based_on_sub_settings wpbc_sub_settings_grayed'
                                , 'only_field'  => true
                        );
    $fields['booking_availability_based_on_sufix'] = array(   
                                'type'          => 'pure_html'
                                , 'group'       => 'advanced'
                                , 'html'        => '       </fieldset>
                                                        </td>
                                                    </tr>'            
                        );        
    
    //  Divider  ///////////////////////////////////////////////////////////////        
    $fields['hr_calendar_after_availability_based_on'] = array( 'type' => 'hr', 'group' => 'advanced', 'tr_class'    => 'wpbc_availability_based_on_sub_settings wpbc_sub_settings_grayed' );

    $fields['booking_is_dissbale_booking_for_different_sub_resources'] = array(   
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_is_dissbale_booking_for_different_sub_resources']   //'Off'            
                            , 'title'       => __('Disable bookings in different booking resources' ,'booking')
                            , 'label'       => __('Check this box to dissable reservations, which can be stored in different booking resources.' ,'booking')
                            , 'description' => '<strong>' . __('Note' ,'booking') . '!</strong> ' . __('When checked, all reserved days must be at same booking resource otherwise error message will show.' ,'booking')
                            , 'group'       => 'advanced'
                            , 'tr_class'    => 'wpbc_availability_based_on_sub_settings wpbc_sub_settings_grayed'
        );       
    
    
    return $fields;
}
add_filter('wpbc_settings_capacity_based_on_visitors', 'wpbc_settings_capacity_based_on_visitors__bl' ,10, 2);


/**
	 * JavaScript     at Bottom of Settings page
 * 
 * @param string $page_tag
 */
function wpbc_settings_enqueue_js__bl( $page_tag, $active_page_tab, $active_page_subtab ) {

    // Check if this correct  page /////////////////////////////////////////////
    
    if ( !(
               ( $page_tag == 'wpbc-settings')                                      // Load only at 'wpbc-settings' menu
            && (  ( ! isset( $_GET['tab'] ) ) || ( $_GET['tab'] == 'general' )  )   // At ''general' tab
          )
      ) return;
  
    // JavaScript //////////////////////////////////////////////////////////////    
    $js_script = '';

    // Hide Availability Word sub-settings, if the checkbox have not checked
    $js_script .= " 
                    // Hide Availability Word sub-settings, if the checkbox have not checked
                    if ( ! jQuery('#set_gen_booking_is_show_availability_in_tooltips').is(':checked') ) {   
                        jQuery('.wpbc_show_availability_in_tooltips').addClass('hidden_items'); 
                    }
    
                    ";
        
    // Hide or show Availability Word in tooltip subsettings,  if the checkbox checked
    $js_script .= " jQuery('#set_gen_booking_is_show_availability_in_tooltips').on( 'change', function(){    
                            if ( this.checked ) { 
                                jQuery('.wpbc_show_availability_in_tooltips').removeClass('hidden_items');               // Show 
                            } else {
                                jQuery('.wpbc_show_availability_in_tooltips').addClass('hidden_items');                  // Hide 
                            }
                        } ); ";     
    
    ////////////////////////////////////////////////////////////////////////////
    // Advanced section 
    ////////////////////////////////////////////////////////////////////////////    
                                                                                // Hide "Auto cancel pending booking section" on Load
    $js_script .= " 
                    if ( ! jQuery('#set_gen_booking_is_show_pending_days_as_available').is(':checked') ) {   
                        jQuery('.wpbc_pending_days_as_available_sub_settings').addClass('hidden_items'); 
                    }
    
                    ";        
                                                                                // Hide or Show "Auto cancel pending booking section" on Click "Use pending days as available"
    $js_script .= " jQuery('#set_gen_booking_is_show_pending_days_as_available').on( 'change', function(){            
                            if ( this.checked ) { 
                                jQuery('.wpbc_pending_days_as_available_sub_settings').removeClass('hidden_items'); // Show 
                                jQuery('#set_gen_booking_is_days_always_available').prop('checked', false );
                            } else {
                                jQuery('.wpbc_pending_days_as_available_sub_settings').addClass('hidden_items');    // Hide 
                            }
                        } ); "; 

                                                                                // Warning  on Click "Auto-cancel all pending bookings"
    $js_script .= " jQuery('#set_gen_booking_auto_cancel_pending_bookings_for_approved_date').on( 'change', function(){    
        
                            if ( this.checked ) { 
                            
                                var answer = confirm('" 
                                              . esc_js( sprintf(__('Warning!!! After you approved the specific booking(s), all your pending bookings of the same booking resource as an approved booking for the dates, which are intersect with dates of approved booking, will be automatically canceled!' ,'booking') ) ) 
                                      .  "' );  
                                if ( answer) { 
                                    this.checked = true;                                   
                                } else { 
                                    this.checked = false;                                   
                                }                                 
                            } 
                        } ); ";            
    
                                                                                // Hide "Capacity  based on number of visitors" on Load
    $js_script .= " 
                    if ( ! jQuery('#set_gen_booking_is_use_visitors_number_for_availability').is(':checked') ) {   
                        jQuery('.wpbc_availability_based_on_sub_settings').addClass('hidden_items'); 
                    }
    
                    ";        
                                                                                // Hide or Show "Capacity  based on number of visitors" on Click
    $js_script .= " jQuery('#set_gen_booking_is_use_visitors_number_for_availability').on( 'change', function(){            
                            if ( this.checked ) { 
                                jQuery('.wpbc_availability_based_on_sub_settings').removeClass('hidden_items'); // Show                                 
                            } else {
                                jQuery('.wpbc_availability_based_on_sub_settings').addClass('hidden_items');    // Hide 
                            }
                        } ); "; 
    
    
    wpbc_enqueue_js( $js_script );                                              // Eneque JS to  the footer of the page.
}
add_action( 'wpbc_after_settings_content',  'wpbc_settings_enqueue_js__bl', 10, 3 );




////////////////////////////////////////////////////////////////////////////////
// Booking Resources Table
////////////////////////////////////////////////////////////////////////////////


/**
	 * Add Column to Resources Table -- Header
 * 
 * @param array $columns
 * @return array
 */
function wpbc_resources_table_header__parentchild_title__bl( $columns ) {    
    
    $columns[ 'parent' ] = array(   
                                  'title'   => __( 'Parent', 'booking' ) 
                                , 'style'   => 'width:12em;text-align:center;'
                                , 'class'   => 'wpbc_hide_mobile_xs'        
                                //, 'sortable'=> true 
                        );
    $columns[ 'prioritet' ] = array(   
                                  'title'   => __( 'Priority', 'booking' ) 
                                , 'style'   => 'width:7em;'
                                , 'class'   => 'wpbc_hide_mobile'        
                                , 'sortable'=> true 
                        );
    if ( get_bk_option( 'booking_is_use_visitors_number_for_availability') == 'On' )
        $columns[ 'visitors' ] = array(   
                                  'title'   => __( 'Max visitors', 'booking' ) 
                                , 'style'   => 'width:7em;'
                                , 'class'   => 'wpbc_hide_mobile'        
                                //, 'sortable'=> true 
                        );    
    return $columns;
}
add_filter( 'wpbc_resources_table_header__parentchild_title', 'wpbc_resources_table_header__parentchild_title__bl', 10, 1 );   // Hook for validated fields.

/**
	 * Add Column to Resources Table -- Header
 * 
 * @param array $columns
 * @return array
 */
function wpbc_resources_table_header__info_title__bl( $columns ) {    
    
    $columns[ 'info' ] = array(   
                                  'title'   => __( 'Info', 'booking' ) 
                                , 'style'   => 'width:5em;text-align:center;'
                                , 'class'   => 'wpbc_hide_mobile'        
                                //, 'sortable'=> true 
                        );
    return $columns;
}
add_filter( 'wpbc_resources_table_header__info_title', 'wpbc_resources_table_header__info_title__bl', 10, 1 );   // Hook for validated fields.


//Maybe: to show button  with  selection of users for prevent of too many options...
/**
	 * Show Column in Resources Table - ROW
 * 
 * @param int $row_num
 * @param array $resource
 */
function wpbc_resources_table_show_col__parentchild_field__bl( $row_num, $resource ) {
    

    $resources_cache = wpbc_br_cache();                                         // Get booking resources from  cache        
    $resource_list = $resources_cache->get_single_parent_resources();

    
    // Parent resource
    ?><td class="wpbc_hide_mobile_xs"><?php                                                                 // DropDown list with Custom Forms 
    
    $select_options = array();
    
    ?><select autocomplete="off" id="booking_resource_parent_<?php echo $resource['id' ]; ?>" 
                                 name="booking_resource_parent_<?php echo $resource['id' ]; ?>"  
                                 style="width:80%;margin:0 10%;"
        ><option value="0" <?php selected(  $resource['parent'], '0' ); ?> > - </option>
            <?php  

            foreach ( $resource_list as $single_par_resource ) { 
				if ($resource['id'] != $single_par_resource['id']) {													//FixIn:7.1.2.3
					?>
					<option value="<?php echo $single_par_resource['id']; ?>" <?php selected(  $resource['parent'], $single_par_resource['id'] ); ?> 
						><?php echo $single_par_resource['title']; ?></option>
            <?php 			
				} 
			}
    ?></select><?php    
    
    ?></td><?php 

    // Priority 
    ?><td class="wpbc_hide_mobile">
        <input  type="text" 
                value="<?php echo esc_attr( $resource['prioritet'] ); ?>" 
                id="booking_resource_prioritet_<?php echo $resource['id' ]; ?>" 
                name="booking_resource_prioritet_<?php echo $resource['id' ]; ?>" 
                class="large-text" 
                style="width:70%;margin:0 15%;font-weight:400;" 
            />
    </td><?php 
    
    if ( get_bk_option( 'booking_is_use_visitors_number_for_availability') == 'On' ) {
        
        // Max visitors 
        ?><td class="wpbc_hide_mobile">
            <?php if (  ( intval($resource['count'] ) > 1 ) ||  ( empty( $resource['parent'] ) )  ){  ?>
            <input  type="text" 
                    value="<?php echo esc_attr( $resource['visitors'] ); ?>" 
                    id="booking_resource_max_visitors_<?php echo $resource['id' ]; ?>" 
                    name="booking_resource_max_visitors_<?php echo $resource['id' ]; ?>" 
                    class="large-text" 
                    style="width:50%;margin:0 25%;font-weight:400;" 
                />
            <?php } else { 
                
                echo '<div style="text-align:center;font-weight:600;">' . esc_js( $resource['visitors'] ) . '</div>';
                
                } ?>
        </td><?php 
        
    }
        
}
add_action( 'wpbc_resources_table_show_col__parentchild_field',  'wpbc_resources_table_show_col__parentchild_field__bl', 10, 2 );



/**
	 * Show Column in Resources Table - ROW
 * 
 * @param int $row_num
 * @param array $resource
 */
function wpbc_resources_table_show_col__info_text__bl( $row_num, $resource ) {
    
    $resources_cache = wpbc_br_cache();                                         // Get booking resources from  cache        
    $resource_list = $resources_cache->get_single_parent_resources();
    
    ?><td style="text-align:center;" class="wpbc_hide_mobile"><?php  

        if ( intval($resource['count'] ) > 1 ) {    
            ?><span style="font-weight:400;background-color: #e80;font-size: 0.8em;" class="label label-default label-info"><?php _e( 'Capacity' , 'booking' ); ?>: &nbsp;&nbsp;<strong><?php echo $resource['count']; ?></strong></span><?php 
        } else {
            
            if ( empty( $resource['parent'] ) ) {
                ?><span style="font-weight:600;background-color: #79b;font-size: 0.75em;" class="label label-default label-info"><?php echo strtoupper(__( 'Single' , 'booking' ) ); ?></span><?php                 
            } else {
                ?><span style="font-weight:400;background-color: #39d;font-size: 0.75em;" class="label label-default label-info"><?php echo strtoupper(__( 'Child' , 'booking' ) ); ?></span><?php                 
            }
        }
    ?></td><?php
    
}
add_action( 'wpbc_resources_table_show_col__info_text',  'wpbc_resources_table_show_col__info_text__bl', 10, 2 );


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
function wpbc_resources_table__update_sql_array__bl( $sql, $resource_id, $resource ) {
    
    // Priority
    $validated_prioritet = WPBC_Settings_API::validate_text_post_static( 'booking_resource_prioritet_' . $resource_id );
    $validated_prioritet = intval( $validated_prioritet );
    
    if ( $validated_prioritet < 0 ) 
        $validated_prioritet = 0;                                               // Minimum must be 1
        
        
    $sql['sql']['params'][] = 'prioritet = %d';
    $sql['values'][]        = $validated_prioritet;

    
    // Parent
    $validated_parent = WPBC_Settings_API::validate_text_post_static( 'booking_resource_parent_' . $resource_id );
    $validated_parent = intval( $validated_parent );
    
    $sql['sql']['params'][] = 'parent = %d';
    $sql['values'][]        = $validated_parent;
    

    // Max. Visitors.
    $temp_m_v_resource_id = $resource_id;
    if ( ! isset( $_POST[ 'booking_resource_max_visitors_' . $temp_m_v_resource_id ] ) ) { // Check  about child booking resources,  here we need to get  max number of visitors from parent resource
        if ( ! empty( $resource['parent'] ) ) {
            $temp_m_v_resource_id = $resource['parent'];                                 // Redefine resource ID to parent,  for saving info about max visitors from parent resource (usually  such  rows at  the sam page
        }
    }
    
    if ( isset( $_POST[ 'booking_resource_max_visitors_' . $temp_m_v_resource_id ] ) ) { 
        $validated_max_visitors = WPBC_Settings_API::validate_text_post_static( 'booking_resource_max_visitors_' . $temp_m_v_resource_id );
        $validated_max_visitors = intval( $validated_max_visitors );
        
        if ( $validated_max_visitors < 1 ) 
            $validated_max_visitors = 1;                                        //Minimum must be 1
        
        $sql['sql']['params'][] = 'visitors = %d';
        $sql['values'][]        = $validated_max_visitors;
    } 

    return $sql;
}
add_filter( 'wpbc_resources_table__update_sql_array', 'wpbc_resources_table__update_sql_array__bl', 10, 3 );   // Hook for validated fields.


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
function wpbc_resources_table__add_new_sql_array__bl( $sql, $params ) {  

    $resources_cache = wpbc_br_cache();                                         // Get booking resources from  cache        
    $resource_list = $resources_cache->get_single_parent_resources();

    // Priority
    $validated_prioritet = WPBC_Settings_API::validate_text_post_static( 'resources_priority' );
    $validated_prioritet = intval( $validated_prioritet );
    if ( $validated_prioritet < 0 ) $validated_prioritet = 0;
    if ( isset($params['index'] ) ) $validated_prioritet += intval( $params['index'] );
    
    $sql['sql']['params'][]      = 'prioritet';
    $sql['sql']['param_types'][] = '%d';
    $sql['values'][]             = $validated_prioritet;
    
    // Parent
    $validated_parent = WPBC_Settings_API::validate_text_post_static( 'select_booking_resource' );
    $validated_parent = intval( $validated_parent );
            
    $sql['sql']['params'][]      = 'parent';
    $sql['sql']['param_types'][] = '%d';
    $sql['values'][]             = $validated_parent;
    
    
    // Get Cost and Max Visitors of parent resource,  if we submit child booking resources 
    if (   ( ! empty( $validated_parent ) ) && ( isset( $resource_list[ $validated_parent ] ) )   ){

        $cost_parent = $resource_list[ $validated_parent ][ 'cost' ];

        $sql['sql']['params'][]      = 'cost';
        $sql['sql']['param_types'][] = '%s';
        $sql['values'][]             = $cost_parent;
        
        
        $validated_max_visitors = intval( $resource_list[ $validated_parent ][ 'visitors' ] );
        if ( $validated_max_visitors < 1 ) $validated_max_visitors = 1;                                        // Minimum must be 1
        
        $sql['sql']['params'][]      = 'visitors';
        $sql['sql']['param_types'][] = '%d';
        $sql['values'][]             = $validated_max_visitors;        
    }
    
    return $sql;
}
add_filter( 'wpbc_resources_table__add_new_sql_array', 'wpbc_resources_table__add_new_sql_array__bl', 10, 2 );   // Hook for validated fields.