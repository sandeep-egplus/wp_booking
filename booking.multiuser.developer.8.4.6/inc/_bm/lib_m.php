<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  S u p p o r t    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// Check  if this date available for specific booking resource, depend from the season filter.
function is_this_day_available_on_season_filters( $date, $bk_type, $season_filters = array() ){

    if ( empty($season_filters) ) {
        $season_filters = apply_bk_filter('get_available_days',  $bk_type );
    }

    $is_day_inside_filters = false ;
    $is_all_days_available = $season_filters['available'];
    $season_filters_dates_count = count( $season_filters['days'] );

    if ($season_filters_dates_count > 0) {

        $season_filters_dates = $season_filters['days'];


        $date_arr = explode( '-', $date );
        $d_mday = (int) $date_arr[2];
        $d_mon  = (int) $date_arr[1];
        $d_year = (int) $date_arr[0];

        foreach ( $season_filters_dates as $filter_num => $season_filters_dates_value ) {           //FixIn: 6.0.1.13
            $version = '1.0';
            if (isset($season_filters_dates[$filter_num]['version']))                               // Version 2.0
                if ($season_filters_dates[$filter_num]['version'] == '2.0')   {

                    $version = '2.0';
                    if (isset($season_filters_dates[$filter_num][ $d_year ]))
                        if (isset($season_filters_dates[$filter_num][ $d_year ][ $d_mon ]))
                            if (isset($season_filters_dates[$filter_num][ $d_year ][ $d_mon ][ $d_mday ]))
                                if ($season_filters_dates[$filter_num][ $d_year ][ $d_mon ][ $d_mday ] == 1 ) {
                                    $is_day_inside_filters = true;
                                    break;
                                }
                }

            if ($version == '1.0') {                                    // Version 1.0

                $is_day_inside_filter = '';


                if (  $season_filters_dates[$filter_num]['days'][  $d_mday  ] == 'On' )     $is_day_inside_filter .= 'day ';
                if (  $season_filters_dates[$filter_num]['monthes'][  $d_mon  ] == 'On' )   $is_day_inside_filter .= 'month ';
                if ( isset(  $season_filters_dates[$filter_num]['year'][  $d_year  ] ) )
                    if (  $season_filters_dates[$filter_num]['year'][  $d_year  ] == 'On' )     $is_day_inside_filter .= 'year ';
                $d_wday = (int) date( "w", mktime(0, 0, 0, $d_mon, $d_mday, $d_year ) );
                if ($is_day_inside_filter == 'day month year ') {
                    if (  $season_filters_dates[$filter_num]['weekdays'][ $d_wday ] == 'On' ) $is_day_inside_filter .= 'week ';
                    if ($is_day_inside_filter == 'day month year week ') {$is_day_inside_filters = true; break;}
                }
            }

        }
    }



    if ($is_day_inside_filters) {
        if ($is_all_days_available) return false;
        else                        return true;
    } else {
        if ($is_all_days_available) return true;
        else                        return false;
    }

}




/**
	 * Check if this day inside of filter  , return TRUE  or FALSE   or   array( 'hour', 'start_time', 'end_time']) if HOUR filter this FILTER ID
 *
 * @param int $day
 * @param int $month
 * @param int $year
 * @param int $filter_id
 *
 * @return bool
 */
function wpbc_is_day_inside_of_filter( $day , $month, $year, $filter_id ){

    global $wpdb;

    if ( IS_USE_WPDEV_BK_CACHE ) {
        global $wpbc_cache_season_filters;

        if (! isset($wpbc_cache_season_filters)) $wpbc_cache_season_filters = array();
        if (! isset($wpbc_cache_season_filters[$filter_id])) {
            $result = $wpdb->get_results( "SELECT booking_filter_id as id, filter FROM {$wpdb->prefix}booking_seasons" );

            foreach ($result as $value) {
                $wpbc_cache_season_filters[$value->id] = array($value);
            }
            if (isset($wpbc_cache_season_filters[$filter_id])) {
                $result = $wpbc_cache_season_filters[$filter_id];
            }
        } else {
            $result = $wpbc_cache_season_filters[$filter_id];
        }
    } else
        $result = $wpdb->get_results( $wpdb->prepare( "SELECT filter FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d " , $filter_id ) );

    if (count($result)>0){

        foreach($result as $filter) {

            if ( is_serialized( $filter->filter ) ) $filter = unserialize($filter->filter);
            else $filter = $filter->filter ;
        }

        return wpbc_is_day_in_season_filter($day , $month, $year, $filter);
    }
    return false;    // there are no filter so not inside of filter
}


/**
	 * Check if this day inside of filter  , return TRUE  or FALSE   or   array( 'hour', 'start_time', 'end_time']) if HOUR filter this FILTER ID
 *
 * @param int $day
 * @param int $month
 * @param int $year
 * @param STRING $filter - data from resource meta
 *
 * @return bool
 */
function wpbc_is_day_in_season_filter( $day , $month, $year, $filter ){

    if ( isset( $filter ) > 0 ) {

        $filter = maybe_unserialize( $filter );

        if ( (isset( $filter['version'] )) && ($filter['version'] == '2.0') ) { // Ver: 2.0

            if (       ( isset( $filter[$year] ) )
                    && ( isset( $filter[$year][$month] ) )
                    && ( isset( $filter[$year][$month][$day] ) )
                    && ( $filter[$year][$month][$day] == 1 )
                ){

                return true;
            } else
                return false;

        } else {                                                                // Ver: 1.0

            $week_day_num = date( 'w', mktime( 0, 0, 0, $month, $day, $year ) );
            $weekdays = array();
            $days = array();
            $monthes = array();
            $years = array();

            foreach ( $filter['weekdays'] as $key => $value ) {
                if ( $value == 'On' )
                    $weekdays[] = $key;
            }
            foreach ( $filter['days'] as $key => $value ) {
                if ( $value == 'On' )
                    $days[] = $key;
            }
            foreach ( $filter['monthes'] as $key => $value ) {
                if ( $value == 'On' )
                    $monthes[] = $key;
            }
            foreach ( $filter['year'] as $key => $value ) {
                if ( $value == 'On' )
                    $years[] = $key;
            }

            if ( ( ! empty( $filter['start_time'] )) && ( ! empty( $filter['end_time'] )) ) {

                return array( 'hour', $filter['start_time'], $filter['end_time'] );             // Its hourly filter, so its apply to all days
            }

            if ( ! in_array( $week_day_num, $weekdays ) )   return false;
            if ( ! in_array( $day, $days ) )                return false;
            if ( ! in_array( $month, $monthes ) )           return false;
            if ( ! in_array( $year, $years ) )              return false;

            return true;                                                        // Its inside of filter
        }
    }

    return false;
}


function wpdev_bk_get_max_days_in_calendar(){
    $max_monthes_in_calendar = get_bk_option( 'booking_max_monthes_in_calendar');
    if (strpos($max_monthes_in_calendar, 'm') !== false) {
        $max_monthes_in_calendar = str_replace('m', '', $max_monthes_in_calendar) * 31 +5;
    } else {
        $max_monthes_in_calendar = str_replace('y', '', $max_monthes_in_calendar) * 365+15 ;
    }
    return $max_monthes_in_calendar;
}


//FixIn: 6.1.1.18
/**
	 * Extend booking dates/times interval to extra hours or days.
 *
 * @param array $param - array( $blocked_days_range, $prior_check_out_date )
 * @return  array( $blocked_days_range, $prior_check_out_date )
 */
function wpbc_get_extended_block_dates( $param ) {
//debuge($param);
    list( $blocked_days_range, $prior_check_out_date ) = $param;
    $my_booking_date = $blocked_days_range[0];


    //FixIn: 6.1.1.18
    $booking_unavailable_extra_in_out = get_bk_option( 'booking_unavailable_extra_in_out' );
    ////////////////////////////////////////////////////////////////
    // Extend unavailbale interval to extra hours; cleaning time, or any other service time
    ////////////////////////////////////////////////////////////////
    /*
    $extra_hours_in  = 1;
    $extra_hours_out = 1;
    if ( substr( $my_booking_date, -1 ) == '1' )
        $my_booking_date = date( 'Y-m-d H:i:s', strtotime( '-' . $extra_hours_in  . ' hour', strtotime( $my_booking_date ) ) );
    if ( substr( $my_booking_date, -1 ) == '2' )
        $my_booking_date = date( 'Y-m-d H:i:s', strtotime( '+' . $extra_hours_out . ' hour', strtotime( $my_booking_date ) ) );
    */
    if ( $booking_unavailable_extra_in_out == 'm' ) {

        $extra_minutes_in  = str_replace( array('m','d'), '', get_bk_option( 'booking_unavailable_extra_minutes_in' )  );       // 0
        $extra_minutes_out = str_replace( array('m','d'), '', get_bk_option( 'booking_unavailable_extra_minutes_out' ) );      // 30

        if ( ( ! empty( $extra_minutes_in ) ) && ( substr( $my_booking_date, -1 ) == '1' ) ) {
            $my_booking_date = date( 'Y-m-d H:i:s', strtotime( '-' . $extra_minutes_in  . ' minutes', strtotime( $my_booking_date ) ) );
        }
        if ( ( ! empty( $extra_minutes_out ) ) && ( substr( $my_booking_date, -1 ) == '2' ) ) {
            $my_booking_date = date( 'Y-m-d H:i:s', strtotime( '+' . $extra_minutes_out . ' minutes', strtotime( $my_booking_date ) ) );
        }

        // Fix overlap of previous times
        if ( $prior_check_out_date !== false ) {
            if (
                   ( substr( $my_booking_date, -1 ) == '1' )
                && ( substr( $prior_check_out_date, -1 ) == '2' )
                && ( strtotime( $prior_check_out_date ) >= strtotime( $my_booking_date )  )
               ) {
                $my_booking_date = date( 'Y-m-d H:i:s', strtotime( '-1 second', strtotime( $prior_check_out_date ) ) );
            }
        }

        $blocked_days_range = array( $my_booking_date );


        /* Fix of one internal  free date in special  situation
         * For exmaple we have booked 2016-05-05 10:00 - 12:00
         * and we have shift  previous date 23 hours and next date shift 23 hours
         * its means that we will have one booked date 2016-05-04 11:00 and other booked date 2016-05-06 11:00
         * And have free date 2016-05-05 - so  need to  fix it.
         */
        if ( $prior_check_out_date !== false ) {
            if (
                   ( substr( $my_booking_date, -1 ) == '2' )
                && ( substr( $prior_check_out_date, -1 ) == '1' )
                && ( wpbc_get_difference_in_days(  date( 'Y-m-d 00:00:00',  strtotime($my_booking_date) ), date( 'Y-m-d 00:00:00',  strtotime($prior_check_out_date) )  ) >= 2  )
               ) {
                $blocked_days_range = array( date( 'Y-m-d 00:00:00', strtotime( '-1 day', strtotime( $my_booking_date ) ) ), $my_booking_date );
            }
        }


        $prior_check_out_date = $my_booking_date;
    }

    ////////////////////////////////////////////////////////////////
    // Extend unavailbale interval to extra DAYS
    ////////////////////////////////////////////////////////////////
    if ( $booking_unavailable_extra_in_out == 'd' ) {

        $extra_days_in  = str_replace( array('m','d'), '', get_bk_option( 'booking_unavailable_extra_days_in' ) );          // 0
        $extra_days_out = str_replace( array('m','d'), '', get_bk_option( 'booking_unavailable_extra_days_out' ) );         // 21

        $initial_check_in_day = $initial_check_out_day = false;
        if ( ( ! empty( $extra_days_in ) ) && ( substr( $my_booking_date, -1 ) == '1' ) ) {
            $initial_check_in_day = $my_booking_date;
            $my_booking_date = date( 'Y-m-d H:i:s', strtotime( '-' . $extra_days_in . ' day', strtotime( $my_booking_date ) ) );
            $blocked_days_range = array( $my_booking_date );        // We have shifted our Start date,  so  need to  start  from  begining
        }

        if ( ( ! empty( $extra_days_out ) ) && ( substr( $my_booking_date, -1 ) == '2' ) ) {
            $initial_check_out_day = $my_booking_date;
            $my_booking_date = date( 'Y-m-d H:i:s', strtotime( '+' . $extra_days_out . ' day', strtotime( $my_booking_date ) ) );
            $blocked_days_range = array();
        }

        if ( $prior_check_out_date !== false ) { // Check if we intersected with  previous date, because dates sorted. Check  only for Check out dates. //TODO: Test more detail here
            if (
                   ( substr( $my_booking_date, -1 ) == '1' )
                && ( substr( $prior_check_out_date, -1 ) == '2' )
                && ( strtotime( $prior_check_out_date ) >= strtotime( $my_booking_date )  )
               ) {
                $my_booking_date = date( 'Y-m-d H:i:s', strtotime( '-1 second', strtotime( $prior_check_out_date ) ) );
            }
        }
        $prior_check_out_date = $my_booking_date;

        if ( $initial_check_in_day !== false ) {
            if ( get_bk_option( 'booking_range_selection_time_is_active')  == 'On' )    $conditional_date = 0;  //If we are using check in/out times, so  need to block  also initial date
            else                                                                        $conditional_date = 1;  // Otherwise we do not need to  block  this date for times, booking,  becaseu its will be blocked by  check out date.

// if we are using start time and end time shortcodes in the booking form, then $conditional_date = 0;
//$conditional_date = 0;  //FixIn: 8.2.1.7


            for ( $di = ($extra_days_in-1); $di >= $conditional_date; $di-- ) {
                $blocked_days_range[] = date( 'Y-m-d 00:00:00', strtotime( '-' . $di . ' day', strtotime( $initial_check_in_day ) ) );
            }
        }
        if (  $initial_check_out_day !== false ) {
            if ( ( $extra_days_in > 0 ) && ( $extra_days_out > 0 ) )
                $conditional_date = 0;                                          //Do  not skip our one day  booking,  if we set intervals for start and end range
            else
                $conditional_date = 1;

            if ( get_bk_option( 'booking_range_selection_time_is_active')  == 'On' )                //FixIn: 7.0.1.38
                $conditional_date = 0;                                          //If we are using check in/out times, so  need to block  also initial date

            for ( $di = $conditional_date; $di < $extra_days_out; $di++ ) {
                $blocked_days_range[] = date( 'Y-m-d 00:00:00', strtotime( '+' . $di . ' day', strtotime( $initial_check_out_day ) ) );
            }
            $blocked_days_range[] = $my_booking_date;
        }
    }

    //FixIn: 8.2.1.7
	// Additional checking about available dates between last  date and check  in or check out dates.
	if (  ( is_array( $param ) ) &&  ( isset(  $param[ 1 ]) )  && (  $param[ 1 ] !== false ) ) {

    	$previos_step_date = $param[ 1 ];

    	// Check if this date is check in date
		if ( substr( $previos_step_date, -1 ) == '1' ) {
			// Get only date from
			$start_date_ins = date( 'Y-m-d 00:00:00', strtotime( $previos_step_date ) );
			$end_date_ins   = date( 'Y-m-d 00:00:00',   strtotime( $blocked_days_range[ 0 ] ) );

			while (  strtotime( $end_date_ins ) > strtotime( '+1 day', strtotime( $start_date_ins ) ) ) {
				$start_date_ins = date( 'Y-m-d 00:00:00', strtotime( '+1 day', strtotime( $start_date_ins ) ) );
				$blocked_days_range[] = $start_date_ins;
			}
			sort($blocked_days_range);
		} else {
			$last_step_date = $blocked_days_range[ ( count($blocked_days_range) - 1 ) ];
			if ( substr( $last_step_date, -1 ) == '2' ) {

				// Get only date from
				$start_date_ins = date( 'Y-m-d 00:00:00', strtotime( $previos_step_date ) );
				$end_date_ins   = date( 'Y-m-d 00:00:00', strtotime( $last_step_date ) );

				while (  strtotime( $end_date_ins ) > strtotime( '+1 day', strtotime( $start_date_ins ) ) ) {
					$start_date_ins = date( 'Y-m-d 00:00:00', strtotime( '+1 day', strtotime( $start_date_ins ) ) );
					$blocked_days_range[] = $start_date_ins;
				}
				array_unique( $blocked_days_range );
				sort($blocked_days_range);
			}
		}
	}

    return array( $blocked_days_range, $prior_check_out_date );

}
add_filter('wpbc_get_extended_block_dates_filter', 'wpbc_get_extended_block_dates');


/**
	 * Replace HINT shortcode in form, with  ability to  use same hint shortcode several  times in the same form.
 *
 * @param string $return_form
 * @param array $params
 * @return string
 */
function wpbc_replace_shortcode_hint( $return_form, $params ) {

	$params['shortcode'] = str_replace( array( '[', ']' ), '', $params['shortcode'] );

	// Trick. Replacing only 1 time and prevent esacaping any paterns in replacing
	$return_form = preg_replace( '/\['. $params['shortcode'] . '\]/', 'REPLACED_SINGLE_WORD', $return_form, 1);

	// Replace 1st occurence of HINT shortcode with span element with  specific HTML ID and INPUT text element for saving this value
	$return_form = str_replace(   'REPLACED_SINGLE_WORD'
								, '<span id="'.  $params['span_class'] .'">' . $params['span_value'] . '</span>'
								. '<input id="'. $params['input_name'] .'" name="'. $params['input_name'] .'" value="'. $params['input_data'] .'" style="display:none;" type="text" />'
								, $return_form );

	// Replace all  other same shortcodes - only  to  show with  HTML CLASS identificator
	$return_form = str_replace(   '['. $params['shortcode'] .']'
								, '<span class="'. $params['span_class'] .'">' . $params['span_value'] . '</span>'
								, $return_form );
	return $return_form;
}


//FixIn: 8.1.3.15
/**
 * Get booking details for showing in mouse over TOOLTIP in CALENDAR for TIMESLOTS
 *
 * @param $blank
 * @param $type_id
 *
 * @return string|void
 */
function wpbc_booking_get_additional_info_to_dates( $blank, $type_id ) {

    if ( get_bk_option( 'booking_is_show_booked_data_in_tooltips' ) !== 'On' ) {
	    return '';
    }

    $start_year              = intval( date_i18n( "Y" ) );
    $start_month             = intval( date_i18n( "m" ) );
    $start_day               = 1;
    $max_monthes_in_calendar = wpdev_bk_get_max_days_in_calendar();

    $real_date       = mktime( 0, 0, 0, ( $start_month ), $start_day, $start_year );
    $wh_booking_date = date_i18n( "Y-m-d", $real_date );

    $real_date        = strtotime( '+' . $max_monthes_in_calendar . ' days', $real_date );
    $wh_booking_date2 = date_i18n( "Y-m-d", $real_date );

    $args = array(
	    'wh_booking_type'       => intval( $type_id ),
	    'wh_approved'           => '',
	    'wh_booking_id'         => '',
	    'wh_is_new'             => '',
	    'wh_pay_status'         => 'all',
	    'wh_keyword'            => '',
	    'wh_booking_date'       => $wh_booking_date,
	    'wh_booking_date2'      => $wh_booking_date2,
	    'wh_modification_date'  => '3',
	    'wh_modification_date2' => '',
	    'wh_cost'               => '',
	    'wh_cost2'              => '',
	    'or_sort'               => '',
	    'page_num'              => '1',
	    'wh_trash'              => '',
	    'limit_hours'           => '0,24',
	    'only_booked_resources' => 0,
	    'page_items_count'      => '100000'
    );

    $bk_listing 	= wpbc_get_bookings_objects( $args );
    $tip_bookings 	= $bk_listing['bookings'];


	// Get array  of bookings  >  dates_additional_info[" . $resource_id . "]['" . $my_day_tag . "'][" . $my_end_time_in_minutes . "] = '" . booking details  . "' ;

	$start_script_code = " dates_additional_info[" . $type_id . "] = []; ";

	foreach ( $tip_bookings as $booking_id => $booking_data ) {

		//FixIn: 8.2.1.20
		//$check_out_date = $booking_data->dates_short[ count( $booking_data->dates_short ) - 1 ];
		foreach ( $booking_data->dates as $bk_check_date ) {

			$check_out_date = $bk_check_date->booking_date;

			$is_it_check_out = substr( $check_out_date, - 1 );
			$is_it_check_out = intval( $is_it_check_out );
			if ( 2 == $is_it_check_out ) {

				$check_out_date_only = substr( $check_out_date, 0, 10 );
				$check_out_time_only = substr( $check_out_date, 11 );

				$date_key   = explode( '-', $check_out_date_only );
				$my_day_tag = intval( $date_key[1] ) . "-" . intval( $date_key[2] ) . "-" . ( $date_key[0] );

				$my_time_in_minutes = explode( ':', $check_out_time_only );
				$my_time_in_minutes = intval( $my_time_in_minutes[0] ) * 60 + intval( $my_time_in_minutes[1] );

				$start_script_code .= "if ( dates_additional_info[" . $type_id . "]['" . $my_day_tag . "'] == undefined ) { ";
				$start_script_code .= "dates_additional_info[" . $type_id . "]['" . $my_day_tag . "'] = []; } ";

				// Get booking details hint relative settings
				$text_in_day_cell = '';

				if ( function_exists( 'get_title_for_showing_in_day' ) ) {
					$text_in_day_cell = esc_textarea( get_title_for_showing_in_day( $booking_id, $tip_bookings, get_bk_option( 'booking_booked_data_in_tooltips' ) ) );
				}


				$booking_data_hint = $text_in_day_cell;                //$booking_data->form_data[ 'secondname' ];

				$booking_data_hint = esc_js( $booking_data_hint );
				$booking_data_hint = str_replace( array( '"', "'" ), '', $booking_data_hint );

				$start_script_code .= " dates_additional_info[" . $type_id . "]['" . $my_day_tag . "'][ " . $my_time_in_minutes . " ] = '" . $booking_data_hint . "' ;  ";
			}
		}
	}

//debuge($start_script_code);die;

	return $start_script_code;
}
add_filter( 'wpbc_booking_get_additional_info_to_dates', 'wpbc_booking_get_additional_info_to_dates', 10, 2 );


/**
 * Get custom booking form
 *
 * @param string $default_return_form_content
 * @param string $custom_form_name
 * @param bool | string $serialized_form_content (optional)             default false
 * @param string $what_to_return (optional) { 'content' | 'form' }      default 'content'
 *
 * @return string
 */
function wpbc_get_custom_booking_form( $default_return_form_content, $custom_form_name, $serialized_form_content = false, $what_to_return = 'content' ){     //FixIn: 8.1.3.19

	$custom_form_name = str_replace( '  ', ' ', $custom_form_name );

	if ( false === $serialized_form_content ) {
		$serialized_form_content = get_bk_option( 'booking_forms_extended' );
	}

	if ( ! empty( $serialized_form_content ) ) {

		$custom_form_content = maybe_unserialize( $serialized_form_content );

		if ( is_array( $custom_form_content ) ) {

			foreach ( $custom_form_content as $one_from ) {

				if ( isset( $one_from['name'] ) ) {

					$one_from['name'] = str_replace( '  ', ' ', $one_from['name'] );

					if ( ( $one_from['name'] == $custom_form_name ) && ( ( isset( $one_from[ $what_to_return ] ) ) ) ) {
						return $one_from[ $what_to_return ];
					}
				}
			}
        }
	}

	return $default_return_form_content;
}
