<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

// This funciton is called in page-gateways.php before showing payment forms
// and in wpbc-search-availability.php for search  results cost  hint
/**
 * Getting total cost  of the booking.
 * 
 * Example of function call:
        $total_cost_of_booking = wpbc_calc_cost_of_booking( array(
              'form' => 'select-one^visitors'.$value->id.'^'.$min_free_items, 
              'days_input_format' => wpbc_get_comma_seprated_dates_from_to_day( date_i18n("d.m.Y", strtotime($bk_date_start) ), date_i18n("d.m.Y", strtotime($bk_date_finish) ) ), 
              'resource_id' => $value->id, 
              'booking_form_type' => apply_bk_filter('wpbc_get_default_custom_form', 'standard', $value->id )
          ) ) ;
 * 
 * @param type $args
 * @return string - formated cost.
 */
function wpbc_calc_cost_of_booking( $args = array() ) {
     
    $defaults = array(
                          'form' => ''                                          // text^cost_hint2^740.00~select-multiple^rangetime2[]^15:00 - 16:00~text^name2^~text^secondname2^~email^email2^~text^phone2^~select-one^accommodation_meals2^ ~select-one^visitors2^1~textarea^details2^~checkbox^term_and_condition2[]^
                        , 'days_input_format' => ''                             // 'string' => "30.02.2014, 31.02.2014, 01.03.2014" 
                        , 'resource_id' => 1                                    // ID of booking resource
                        , 'booking_form_type' => 'standard'                     // Default custom  form  of booking resource
                        , 'payment_cost' => 0.0                                 // Amout to  show in payment form NOW        
                        , 'is_check_additional_calendars' => true
    );
    $params = wp_parse_args( $args, $defaults );
            
    $_POST['booking_form_type'] = $params['booking_form_type'];                 // Its required for the correct calculation  of the Advanced Cost.
    
    /*
    // TODO: Set for multiuser - user ID (ajax request do not transfear it
    //$this->client_side_active_params_of_user
    */    
    make_bk_action('check_multiuser_params_for_client_side', $params[ 'resource_id' ] );

    

    $str_dates__dd_mm_yyyy = $params[ 'days_input_format' ];                    //FixIn: 5.4.5.2
    $booking_type          = $params[ 'resource_id' ];
    $booking_form_data     = $params[ 'form' ];

    $is_check_additional_calendars = $params[ 'is_check_additional_calendars' ];
    
//debuge('$str_dates__dd_mm_yyyy, $booking_type, $booking_form_data', $str_dates__dd_mm_yyyy, $booking_type, $booking_form_data);

    $dates_in_diff_formats = wpbc_get_dates_in_diff_formats( $str_dates__dd_mm_yyyy, $booking_type, $booking_form_data );

    $start_time         = $dates_in_diff_formats['start_time'];             // Array( [0] => 10, [1] => 00, [2] => 01 )
    $end_time           = $dates_in_diff_formats['end_time'];               // Array( [0] => 12, [1] => 00, [2] => 02 )
    $only_dates_array   = $dates_in_diff_formats['array'];                  // [0] => '2015-10-15', [1] => '2015-10-15'
    $dates              = $dates_in_diff_formats['string'];                 // '15.12.2015, 16.12.2015, 17.12.2015'

//debuge('NEW', $start_time, $end_time, $only_dates_array, $dates_in_diff_formats);
    
    
    // Get cost of main calendar with all rates discounts and  so on...
    $summ = apply_filters('wpdev_get_booking_cost', $booking_type, $dates, array($start_time, $end_time ), $booking_form_data );
    $summ = floatval( $summ );
    //$summ = round($summ,2);//FixIn: 8.1.3.33

    // Get only Original cost
    $summ_original = apply_bk_filter('wpdev_get_bk_booking_cost', $booking_type, $dates, array($start_time, $end_time ), $booking_form_data, true , true );
    $summ_original = floatval( $summ_original );
    //$summ_original = round($summ_original,2);//FixIn: 8.1.3.33


    // Get description according coupons discount for main calendar if its exist
    $coupon_info_4_main_calendar = apply_bk_filter('wpdev_get_additional_description_about_coupons', '', $booking_type, $dates, array($start_time, $end_time ), $booking_form_data   );
	$coupon_discount_value		 = apply_bk_filter('wpbc_get_coupon_code_discount_value', '', $booking_type, $dates, array($start_time, $end_time ), $booking_form_data   );
    ////////////////////////////////////////////////////////////////////////////
    // Check additional cost based on several calendars inside of this form  
    ////////////////////////////////////////////////////////////////////////////
    if ( $is_check_additional_calendars ) {
        $additional_calendars_cost = apply_bk_filter('check_cost_for_additional_calendars', $summ, $booking_form_data, $booking_type,  array($start_time, $end_time)   ); // Apply cost according additional calendars        
        $summ_total       = $additional_calendars_cost[0];                          // $summ
        $summ_additional  = $additional_calendars_cost[1];                          // $array
        $dates_additional = $additional_calendars_cost[2];                          // $array
    } else {
        $summ_total       = $summ;
        $summ_additional  = array();
        $dates_additional = array();
    }
    $additional_description = '';           
    if ( count($summ_additional)>0 ) {  // we have additional calendars inside of this form

            // Main calendar description and discount info //
            $additional_description .= '<br />' . get_booking_title($booking_type) . ': ' . wpbc_get_cost_with_currency_for_user( $summ, $booking_type );
            if ($coupon_info_4_main_calendar != '')
                $additional_description .=   $coupon_info_4_main_calendar ;
            $coupon_info_4_main_calendar = '';
            $additional_description .= '<br />' ;

            // Additional calendars - info and discounts //
            foreach ($summ_additional as $key=>$ss) {

                $additional_description .= get_booking_title($key) . ': ' . wpbc_get_cost_with_currency_for_user( $ss, $key );

                // Discounts info ///////////////////////////////////////////////////////////////////////////////////////////////////////
//debuge($booking_form_data, $key ,  $booking_type );                
                $form_content_for_specific_calendar = wpbc_get_form_with_replaced_id( $booking_form_data, $key,  $booking_type );

                $dates_in_specific_calendar = $dates_additional[$key];
                $coupon_info_4_calendars = apply_bk_filter('wpdev_get_additional_description_about_coupons', '', $key , $dates_in_specific_calendar , array($start_time, $end_time ), $form_content_for_specific_calendar );
                if ($coupon_info_4_calendars != '')
                    $additional_description .=   $coupon_info_4_calendars ;
                $coupon_info_4_calendars = '';
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $additional_description .= '<br />' ;
            }
    }
 
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    $summ_deposit = apply_bk_filter('fixed_deposit_amount_apply', $summ_total , $booking_form_data, $booking_type, $str_dates__dd_mm_yyyy ); // Apply fixed deposit

//debuge($summ_deposit, $summ_total , $booking_form_data, $booking_type, $str_dates__dd_mm_yyyy );    

    // $summ_deposit = apply_bk_filter('advanced_cost_apply',      $summ_deposit , $booking_form_data, $booking_type, explode(',', $str_dates__dd_mm_yyyy)  );    // Fix: 6.1.1.12
     
    if ( $summ_deposit != $summ_total )  $is_deposit = true;
    else                                $is_deposit = false;
    $summ_balance = $summ_total - $summ_deposit;
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
    $summ_additional_hint = $summ_total - $summ_original;


	//FixIn: 8.3.3.14
    return array(
        
        'additional_description' => $additional_description
            
        // N E W    
        , 'payment_cost'            => number_format( (float) $params['payment_cost'], wpbc_get_cost_decimals(), '.', '' )                  //FixIn: 8.3.2.1               //FixIn: 8.2.1.24

        , 'payment_cost_hint'       => wpbc_cost_show( $params['payment_cost'], array(  'currency' => 'CURRENCY_SYMBOL' ) )   
    
        , 'total_cost'              => number_format( (float) $summ_total, wpbc_get_cost_decimals(), '.', '' )                  //FixIn: 8.3.2.1
        , 'cost_hint'               => wpbc_cost_show( $summ_total, array(  'currency' => 'CURRENCY_SYMBOL' ) )                             // [cost_hint]            - TOTAL cost
        , 'total_cost_hint'         => wpbc_cost_show( $summ_total, array(  'currency' => 'CURRENCY_SYMBOL' ) )   
    
        , 'deposit_cost'            => number_format( (float) $summ_deposit, wpbc_get_cost_decimals(), '.', '' )                  //FixIn: 8.3.2.1
        , 'deposit_hint'            => wpbc_cost_show( $summ_deposit, array(  'currency' => 'CURRENCY_SYMBOL' ) )                           // [deposit_hint]         - Deposit cost
        , 'deposit_cost_hint'       => wpbc_cost_show( $summ_deposit, array(  'currency' => 'CURRENCY_SYMBOL' ) )   
        
        , 'balance_cost'            => number_format( (float) $summ_balance, wpbc_get_cost_decimals(), '.', '' )                  //FixIn: 8.3.2.1
        , 'balance_hint'            => wpbc_cost_show( $summ_balance, array(  'currency' => 'CURRENCY_SYMBOL' ) )                           // [balance_hint]         - Balance cost - difference between deposit and full cost
        , 'balance_cost_hint'       => wpbc_cost_show( $summ_balance, array(  'currency' => 'CURRENCY_SYMBOL' ) )   
    
        , 'original_cost'           => number_format( (float) $summ_original, wpbc_get_cost_decimals(), '.', '' )                  //FixIn: 8.3.2.1
        , 'original_cost_hint'      => wpbc_cost_show( $summ_original, array(  'currency' => 'CURRENCY_SYMBOL' ) )                          // [original_cost_hint]   - Cost of the booking only for selected dates only.
        
        , 'additional_cost'         => number_format( (float) $summ_additional_hint, wpbc_get_cost_decimals(), '.', '' )                  //FixIn: 8.3.2.1
        , 'additional_cost_hint'    => wpbc_cost_show( $summ_additional_hint, array(  'currency' => 'CURRENCY_SYMBOL' ) )                   // [additional_cost_hint] - Additional cost, which depends on the fields selection in the form. 
		
        , 'coupon_discount'         => number_format( (float) $coupon_discount_value, wpbc_get_cost_decimals(), '.', '' )                  //FixIn: 8.3.2.1
        , 'coupon_discount_hint'    => wpbc_cost_show( $coupon_discount_value, array(  'currency' => 'CURRENCY_SYMBOL' ) )                  
            
    );
}


////////////////////////////////////////////////////////////////////////////////
// Suport Functions
////////////////////////////////////////////////////////////////////////////////

/**
	 * Get Form with replaced old ID to new  one
 * 
 * @param string $booking_form_str
 * @param int $new_id
 * @param int $old_id
 * @return string
 */
function wpbc_get_form_with_replaced_id( $booking_form_str, $new_id, $old_id ) {

    $booking_form_arr = explode( '~', $booking_form_str );
    $formdata_additional = '';
    for ( $i = 0; $i < count( $booking_form_arr ); $i++ ) {
        $my_form_field = explode( '^', $booking_form_arr[$i] );
        if ( $formdata_additional !== '' )
            $formdata_additional .= '~';

        if ( substr( $my_form_field[1], strlen( $my_form_field[1] ) - 2, 2 ) == '[]' )
            $my_form_field[1] = substr( $my_form_field[1], 0, ( strlen( $my_form_field[1] ) - strlen( '' . $old_id ) ) - 2 ) . $new_id . '[]';
        else
            $my_form_field[1] = substr( $my_form_field[1], 0, ( strlen( $my_form_field[1] ) - strlen( '' . $old_id ) ) ) . $new_id;

        $formdata_additional .= $my_form_field[0] . '^' . $my_form_field[1] . '^' . $my_form_field[2];
    }

    return $formdata_additional;
}



////////////////////////////////////////////////////////////////////////////////
// V a l u a t i o n    Days   -   Calculation
////////////////////////////////////////////////////////////////////////////////

/**
	 * Get array of "Valuation days"
 * 
 * @param int $booking_type
 * @param array $days_array         - array( [0] => '10.10.2016', [1] => ' 11.10.2016', [2] => ' 12.10.2016', ...
 * @param array $times_array        - array ([0] => Array( [0] => 00, [1] => 00, [2] => 01 ),  [1] => Array ( [0] => 24, [1] => 00, [2] => 02 )  )
 * @return bool | array             - array ( [1] => 100 , [2] => add5, [3] => add5, [4] => add5, [5] => add5, [6] => 95%, [7] => 0, ... )
 */
function wpbc_get_valuation_days_array( $booking_type, $days_array, $times_array ){
//debuge('$booking_type, $days_array', $booking_type, $days_array);
    $days_costs = array();
    $maximum_days_count = count( $days_array );
    
    $costs_depends = wpbc_get_resource_meta( $booking_type, 'costs_depends' );

    if ( count( $costs_depends ) > 0 )        
        $costs_depends = maybe_unserialize( $costs_depends[0]->value );        
    else
        return false;

    if ( empty( $costs_depends ) ) return false;                                //FixIn: 7.0.1.4


    $sortedDates = wpbc_get_sorted_days_array( implode( ',', $days_array ) );
    if ( !empty( $sortedDates ) ) {
        $check_in_date = $sortedDates[0];
        $check_in_date = explode( ' ', $check_in_date );
        $check_in_date = $check_in_date[0];
        $check_in_date = explode( '-', $check_in_date );
        $check_in = array();
        $check_in['year'] =  intval( $check_in_date[0] );
        $check_in['month'] = intval( $check_in_date[1] );
        $check_in['day'] =   intval( $check_in_date[2] );
    } else
        return false;

    $is_togather_applied = false;                                               //FixIn: 7.0.1.7
//debuge('$costs_depends',$costs_depends, $days_array);    
    foreach ( $costs_depends as $value ) {                        // Loop all items of "Valuation days" cost settings.    
        
        if ( $value['active'] == 'On' ) {                         // Only Active
            
            // Season filters //////////////////////////////////////////////////
            
            $is_can_continue_with_this_item = true;

            if ( ! empty( $value['season_filter'] ) ) {                         // Check  if this day inside of filter
                $is_day_inside_of_filter = wpbc_is_day_inside_of_filter( $check_in['day'], $check_in['month'], $check_in['year'], $value['season_filter'] );
                if ( ! $is_day_inside_of_filter )
                    $is_can_continue_with_this_item = false;
            }
            if ( ! $is_can_continue_with_this_item )
                continue;
            
            ////////////////////////////////////////////////////////////////////

            if ( $value['type'] == 'summ' ) {

                // Check  situation, when the "Together" date alredy set  by some other setting ///////
                if ( isset( $days_costs[$value['from']] ) ) {
                    $is_can_continue = false;
                    for ( $ii = 1; $ii < $value['from']; $ii++ ) { // Recheck if all previous dates are also set - its mean that was set "Together" option
                        if ( !isset( $days_costs[$ii] ) ) {
                            $is_can_continue = true;            // We have one date not set, its mean the previousl was set For or From selecttors, and we can apply Together
                        }
                    }
                    if ( !$is_can_continue )
                        continue;                    // Aleready set this option
                } //////////////////////////////////////////////////////////////

                if ( $value['from'] <= ($maximum_days_count) ) {                // We will apply Togather       //FixIn: 7.0.1.7
                    $is_togather_applied = true;                                    
                }
                
                if ( $value['cost_apply_to'] == '%' )
                    $value['cost'] .= '%';

                if ( $value['from'] == ($maximum_days_count) ) {

                    $days_costs[$value['from']] = $value['cost'];

                    if ( strpos( $value['cost'], '%' ) !== false )
                        $assign_value = $value['cost'];
                    else
                        $assign_value = 0;

                    for ( $ii = 1; $ii < $value['from']; $ii++ ) {
                        $days_costs[$ii] = $assign_value;
                    }

                // return $days_costs;                                          //FixIn:6.2.1.3
                } elseif ( $value['from'] < ($maximum_days_count) ) {

                    $days_costs[$value['from']] = $value['cost'];
                    if ( strpos( $value['cost'], '%' ) !== false )
                        $assign_value = $value['cost'];
                    else
                        $assign_value = 0;
                    for ( $ii = 1; $ii < $value['from']; $ii++ ) {
                        $days_costs[$ii] = $assign_value;
                    }
                }
                
            } elseif ( $value['type'] == '=' ) {
//debuge($days_costs,$value, (int) $is_togather_applied , $maximum_days_count);   				
                if ( strtolower( $value['from'] ) == 'last' ) {                 //FixIn: 7.0.1.7
                 
                    //if ( $is_togather_applied ) {                               // Previously  we was applied TOGETHER,  so no need to  apply LAST,  because its have to  be already calculated in togather term (//FixIn: 7.0.1.7)
					
					//FixIn: 7.2.1.20  - Apply For = LAST,  event if previoys TOGATHER = NN% settings was applyied
					if (	( $is_togather_applied ) 
						 && ( ( isset( $days_costs[ $maximum_days_count ] ) ) && ( strpos( $days_costs[ $maximum_days_count ], '%' ) === false ) ) 
					){ 
                        continue;;      
                    }
                    $value['from'] = $maximum_days_count;
                }
                // if ( isset( $days_costs[ $value['from'] ] ) ) continue;      // Aleready set this option      //FixIn:6.2.1.3
                
                if ( $value['from'] <= $maximum_days_count ) {

                    if ( $value['cost_apply_to'] == 'add' )
                        $days_costs[$value['from']] = 'add' . $value['cost'];
                    elseif ( $value['cost_apply_to'] == '%' )
                        $days_costs[$value['from']] = $value['cost'] . '%';
                    elseif ( $value['cost_apply_to'] == 'fixed' )
                        $days_costs[$value['from']] = $value['cost'];
                    else
                        $days_costs[$value['from']] = $value['cost'];
                }

            } elseif ( $value['type'] == '>' ) {
                for ( $i = $value['from']; $i <= $value['to']; $i++ ) {
                    if ( $i <= $maximum_days_count )
                        if ( !isset( $days_costs[$i] ) ) {

                            if ( $value['cost_apply_to'] == 'add' )         $days_costs[$i] = 'add' . $value['cost'];
                            elseif ( $value['cost_apply_to'] == '%' )       $days_costs[$i] = $value['cost'] . '%';
                            elseif ( $value['cost_apply_to'] == 'fixed' )   $days_costs[$i] = $value['cost'];
                            else                                            $days_costs[$i] = $value['cost'];
                        }
                }
            }
        }
    }



    for ( $i = 1; $i <= $maximum_days_count; $i++ ) {
        if ( !isset( $days_costs[$i] ) ) {
            $days_costs[$i] = '100%';
        }
    }
    ksort( $days_costs );
//debuge($days_costs);
    return $days_costs;
}

