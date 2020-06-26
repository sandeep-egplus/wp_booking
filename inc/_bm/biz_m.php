<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


if ( ! defined( 'IS_USE_WPDEV_BK_CACHE' ) ) { define( 'IS_USE_WPDEV_BK_CACHE', true ); }

require_once(WPBC_PLUGIN_DIR. '/inc/_bm/wpbc-calc-string.php' );                                                        //FixIn: 8.1.3.17
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/lib_m.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/admin/wpbc-seasons-table.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/admin/page-cost.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/admin/page-cost-advanced.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/admin/page-availability.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/admin/page-seasons.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/wpbc-m-costs.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/m-toolbar.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/form-conditions.php' );

require_once(WPBC_PLUGIN_DIR. '/inc/_bm/admin/api-settings-m.php' );            // Settings page
require_once(WPBC_PLUGIN_DIR. '/inc/_bm/admin/activation-m.php' );              // Activate / Deactivate

if (file_exists(WPBC_PLUGIN_DIR. '/inc/_bl/biz_l.php')) { require_once(WPBC_PLUGIN_DIR. '/inc/_bl/biz_l.php' ); }


global $wpbc_cache_season_filters;

class wpdev_bk_biz_m {

    var $wpdev_bk_biz_l;

    function __construct(){

		add_bk_action('wpdev_ajax_show_cost', array($this, 'wpdev_ajax_show_cost'));

		add_bk_filter('wpdev_reapply_bk_form', array(&$this, 'wpdev_reapply_bk_form'));

		add_bk_filter('check_if_cost_exist_in_field', array(&$this, 'check_if_cost_exist_in_field'));

		add_bk_filter('wpbc_get_default_custom_form', array(&$this, 'wpbc_get_default_custom_form'));

		add_bk_filter('wpdev_season_rates', array(&$this, 'apply_season_rates'));
		add_bk_filter('get_available_days', array(&$this, 'get_available_days'));

		add_bk_filter('fixed_deposit_amount_apply', array(&$this, 'fixed_deposit_amount_apply'));

		add_bk_filter('advanced_cost_apply', array(&$this, 'advanced_cost_apply'));

		add_bk_filter('early_late_booking_apply', array(&$this, 'early_late_booking_apply'));							//FixIn: 8.2.1.17

		add_bk_filter('reupdate_static_cost_hints_in_form', array(&$this, 'reupdate_static_cost_hints_in_form'));   	//FixIn: 5.4.5.5

		add_bk_filter('wpdev_get_booking_form', array(&$this, 'wpdev_get_booking_form'));

		add_bk_filter('wpdev_get_booking_form_content',  'wpbc_get_custom_booking_form' );								//FixIn: 8.1.3.19

		add_action('wpbc_define_js_vars', array(&$this, 'wpbc_define_js_vars') );
		add_action('wpbc_enqueue_js_files', array(&$this, 'wpbc_enqueue_js_files') );
		add_action('wpbc_enqueue_css_files',array(&$this, 'wpbc_enqueue_css_files') );

		add_bk_filter('wpdev_bk_define_additional_js_options_for_bk_shortcode', array(&$this, 'wpdev_bk_define_additional_js_options_for_bk_shortcode'));

		add_filter('wpdev_booking_availability_filter', array(&$this, 'js_availability_filter') , 10, 2 );
		add_filter('wpdev_booking_show_rates_at_calendar', array(&$this, 'show_rates_at_calendar') , 10, 2 );

		add_bk_filter('get_unavailbale_dates_of_season_filters', array(&$this, 'get_unavailbale_dates_of_season_filters'));

		 add_bk_filter('wpdev_check_for_additional_calendars_in_form', array(&$this, 'wpdev_check_for_additional_calendars_in_form'));
		 add_bk_filter('check_cost_for_additional_calendars', array(&$this, 'check_cost_for_additional_calendars'));

		if ( class_exists('wpdev_bk_biz_l')) {  $this->wpdev_bk_biz_l = new wpdev_bk_biz_l();
		} else {                                $this->wpdev_bk_biz_l = false; }

    }


   // Possible to book many different items / rooms / facilties via. one form
   function wpdev_check_for_additional_calendars_in_form( $form, $my_boook_type, $options = false ) {

        $calendars = array(); $cal_num = -1;$additional_calendars = '';
        $form = preg_replace( '/\[calendar\*\s*\]/', '', $form);                //FixIn: 6.1.1.17
        while ( strpos($form, '[calendar') !== false ) { $cal_num++; $calendars[$cal_num] = array();
             $cal_start = strpos($form, '[calendar');
             $cal_end = strpos($form, ']' , $cal_start+1);

             $new_cal = substr($form, ($cal_start+9),  ($cal_end - $cal_start-9) );
             $new_cal = trim($new_cal);
             $params = explode(' ', $new_cal);
             foreach ($params as $param) {
                 $param = explode('=',$param);
                 $calendars[$cal_num][$param[0]] = $param[1];
             }

             if (isset($calendars[$cal_num]['id'])) {

                 $bk_type = $calendars[$cal_num]['id'];

                 $my_selected_dates_without_calendar = '';
                 $my_boook_count =1;
                 $bk_otions = array();

                 if (! empty($options)) {
                     $my_booking_form = $options['booking_form' ];
                     $my_selected_dates_without_calendar = $options['selected_dates' ];
                     $my_boook_count = $options['cal_count'];
                     $bk_otions = $options['otions'];
                 }

                //Fix
                $bk_cal = '<a name="bklnk'.$bk_type.'"></a><div id="booking_form_div'.$bk_type.'" class="booking_form_div">';
                 $additional_calendars .= $bk_type . ',';
                 $bk_cal .= apply_bk_filter('pre_get_calendar_html',$bk_type, $my_boook_count, $bk_otions );
                 //$bk_cal .= '<div id="calendar_booking'.$bk_type.'">&nbsp;</div>';
                 //$bk_cal .= '<textarea rows="3" cols="50" id="date_booking'.$bk_type.'" name="date_booking'.$bk_type.'" style="display:none;"></textarea>';   // Calendar code
                 $bk_cal .= '<input type="hidden" name="parent_of_additional_calendar'.$bk_type.'" id="parent_of_additional_calendar'.$bk_type.'" value="'.$my_boook_type.'" /> ';
                //Fix
                $bk_cal .= '<div id="submiting'.$bk_type.'"></div><div class="form_bk_messages" id="form_bk_messages'.$bk_type.'" ></div>';
                $bk_cal .= wp_nonce_field('INSERT_INTO_TABLE',  ("wpbc_nonce" . $bk_type) ,  true , false );
                $bk_cal .= wp_nonce_field('CALCULATE_THE_COST', ("wpbc_nonceCALCULATE_THE_COST" . $bk_type) ,  true , false );
                $bk_cal .= '</div>';
                 $additional_bk_types = array();

                 $start_script_code = apply_bk_filter('get_script_for_calendar',$bk_type, $additional_bk_types, $my_selected_dates_without_calendar, $my_boook_count );
                 $start_script_code = apply_bk_filter('wpdev_bk_define_additional_js_options_for_bk_shortcode', $start_script_code, $bk_type, $bk_otions);

                 $form = substr_replace($form,  $bk_cal  .$start_script_code  , $cal_start, ($cal_end - $cal_start+1) );
//$form .= '<div  id="gateway_payment_forms'.$bk_type.'"></div>';

                 //Todo: this element is add showhint elemnts, think how to make it in more good way, 2 lines above is added showhint shortcode
                 // its also not really correct thing
                 //$form = $this->wpdev_reapply_bk_form($form, $bk_type);     //cost hint
             }

         }
         if (isset($additional_calendars))
             if ($additional_calendars!=''){
                 $additional_calendars = substr($additional_calendars, 0, -1);
                 $form .= ' <input type="hidden" name="additional_calendars'.$my_boook_type.'" id="additional_calendars'.$my_boook_type.'" value="'.$additional_calendars.'" /> ';

             }
         return $form;
   }



   // Get total and costs for each other calendars, which are inside of this form
   function check_cost_for_additional_calendars($summ, $post_form, $post_bk_type,  $time_array , $is_discount_calculate = true ){

        $summ_total = $summ;

            // Check for additional calendars:
            $send_form_content = $post_form;
            $offset = 0;
            $summ_additional = array();
            $dates_additional = array();
            while ( strpos( $send_form_content , 'textarea^date_booking' , $offset) !== false ) {
                $offset = strpos( $send_form_content , 'textarea^date_booking' , $offset)+1;
                $offset_end = strpos( $send_form_content , '^' , $offset+20);
                $other_bk_id = substr($send_form_content, $offset+20, $offset_end - $offset -20 ) ;                             // ID

                $offset_end_dates_data = strpos( $send_form_content , '~' ,  $offset_end );
                if ($offset_end_dates_data === false) { $offset_end_dates_data = strlen($send_form_content); }
                $other_bk_dates = substr($send_form_content, $offset_end+1 , $offset_end_dates_data - $offset_end-1  );         // Dates

                // Replace inside of form old ID to the new correct ID
                $send_form_content = wpbc_get_form_with_replaced_id($send_form_content, $other_bk_id,  $post_bk_type );   //Form

                if (empty($other_bk_dates) ) $summ_add = 0;
                else $summ_add = apply_bk_filter('wpdev_get_bk_booking_cost', $other_bk_id , $other_bk_dates , $time_array , $send_form_content , $is_discount_calculate );
                $summ_add = floatval( $summ_add );
                //$summ_add = round($summ_add,2);//FixIn: 8.1.3.33
                $summ_additional[ $other_bk_id ]= $summ_add;
                $dates_additional[ $other_bk_id ]= $other_bk_dates;

                $send_form_content = $post_form;
            }

//debuge($summ, $summ_additional);
            foreach ($summ_additional as $ss) { $summ_total += $ss; }           // Summ all costs

//debuge(array($summ_total, $summ_additional, $dates_additional));
        return array($summ_total, $summ_additional, $dates_additional) ;

   }


// S U P P O R T       F u n c t i o n s    //////////////////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define JavaScripts Variables               //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_define_js_vars( $where_to_load = 'both' ){

        $wpdev_bk_season_filter = '';
        $max_monthes_in_calendar = wpdev_bk_get_max_days_in_calendar();
        $my_day_tag =  date('n-j-Y' );                                          // TODAY
        $my_day_arr = explode('-',$my_day_tag);
        $day    = ($my_day_arr[1]+0);
        $month  = ($my_day_arr[0]+0);
        $year   = ($my_day_arr[2]+0);

        for ($i = 0; $i < $max_monthes_in_calendar ; $i++) {                    // Days
            $wpdev_bk_season_filter .= '"'.$my_day_tag.'":[],';                  //FixIn:6.1
            $day++;
            $my_day_tag =  date('n-j-Y' , mktime(0, 0, 0, $month, $day, $year ));
        }

        if (  ! empty( $wpdev_bk_season_filter) )                               //FixIn:6.1
            $wpdev_bk_season_filter = substr ( $wpdev_bk_season_filter, 0, -1); //FixIn:6.1

        wp_localize_script('wpbc-global-vars', 'wpbc_global4', array(
              'bk_cost_depends_from_selection_line1' => wpbc_get_currency() . ' ' . esc_js(__('per 1 day' ,'booking'))
            , 'bk_cost_depends_from_selection_line2' => '% ' . esc_js(__('from the cost of 1 day ' ,'booking'))
            , 'bk_cost_depends_from_selection_line3' => sprintf( esc_js(__('Additional cost in %s per 1 day' ,'booking')), wpbc_get_currency() )
            , 'bk_cost_depends_from_selection_line14summ' => wpbc_get_currency() . ' ' . esc_js(__(' for all days!' ,'booking'))
            , 'bk_cost_depends_from_selection_line24summ' => '% ' . esc_js( __('for all days!' ,'booking') )
            , 'wpdev_bk_season_filter' => '{'.$wpdev_bk_season_filter.'}'       //FixIn:6.1
            //, 'wpdev_bk_season_filter_action' => 'false;'                     //FixIn:6.1
            //,'wpbc_available_days_num_from_today' =>  ( ( strpos($_SERVER['REQUEST_URI'],'wpbc-new') !== false ) ? '0' : intval( get_bk_option('booking_available_days_num_from_today') ) )
            ,'wpbc_available_days_num_from_today' => intval( get_bk_option('booking_available_days_num_from_today') )
			,'bk_show_info_in_form' => ( ( get_bk_option( 'booking_is_show_booked_data_in_tooltips' ) !== 'On') ? 'false' : 'true' )		//FixIn: 8.1.3.15
        ) );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load JavaScripts Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_enqueue_js_files( $where_to_load = 'both' ){
        wp_enqueue_script( 'wpbc-bm',         WPBC_PLUGIN_URL . '/inc/js/biz_m.js', array( 'wpbc-global-vars' ), WP_BK_VERSION_NUM );
        wp_enqueue_script( 'wpbc-conditions', WPBC_PLUGIN_URL . '/inc/js/form-conditions.js', array( 'wpbc-bm' ), WP_BK_VERSION_NUM );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load CSS Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_enqueue_css_files( $where_to_load = 'both' ){

    }


    // write JS variables
    function js_define_variables(){
    ?>
var bk_cost_depends_from_selection_line1 = '<?php echo wpbc_get_currency() . ' '.   esc_js(__('per 1 day' ,'booking')); ?>';
var bk_cost_depends_from_selection_line2 = '<?php echo '% '. esc_js(__('from the cost of 1 day ' ,'booking')); ?>';
var bk_cost_depends_from_selection_line3 = '<?php echo sprintf( esc_js(__('Additional cost in %s per 1 day' ,'booking')), wpbc_get_currency() ); ?>';
var bk_cost_depends_from_selection_line14summ = '<?php echo wpbc_get_currency() . ' '.   esc_js(__(' for all days!' ,'booking')); ?>';
var bk_cost_depends_from_selection_line24summ = '<?php echo '% '. esc_js(__(' for all days!' ,'booking')); ?>';
var wpdev_bk_season_filter = [];<?php
        $max_monthes_in_calendar = wpdev_bk_get_max_days_in_calendar();
        $my_day =  date('m.d.Y' );                                          // TODAY
        for ($i = 0; $i < $max_monthes_in_calendar ; $i++) {                // Days

            $my_day_arr = explode('.',$my_day);
            $day    = ($my_day_arr[1]+0);
            $month  = ($my_day_arr[0]+0);
            $year   = ($my_day_arr[2]+0);
            $my_day_tag =   $month . '-' . $day . '-' . $year ;

            echo " wpdev_bk_season_filter['".$my_day_tag."'] = [];";

            $my_day =  date('m.d.Y' , mktime(0, 0, 0, $month, ($day+1), $year ));
        }
    }

    // Apply scripts for the conditions in the rnage days selections
    function wpdev_bk_define_additional_js_options_for_bk_shortcode( $start_script_code, $bk_type, $bk_otions){

        /*  $options    structure:
            {select-day condition="season" for="High season" value="14"},
            {select-day condition="season" for="Low season" value="2-5"},
            {select-day condition="weekday" for="1" value="4"},
            {select-day condition="weekday" for="5" value="3"},
            {select-day condition="weekday" for="6" value="2,7"},
            {select-day condition="weekday" for="0" value="7,14"}
         */
        if (empty($bk_otions)) return $start_script_code;                   // Return default scripts if options is empty.

        /* $matches    structure:
         * Array
            (
                [0] => {select-day condition="weekday" for="6" value="2,7"},
                [1] => select-day
                [2] => condition
                [3] => weekday
                [4] => for
                [5] => 6
                [6] => value
                [7] => 2,7
            )
         */
        $param ='\s*([condition|for|value]+)=[\'"]{1}([^\'"]+)[\'"]{1}\s*'; // Find all possible options
        $pattern_to_search='%\s*{([^\s]+)'. $param . $param . $param .'}\s*[,]?\s*%';
        preg_match_all($pattern_to_search, $bk_otions, $matches, PREG_SET_ORDER);
//debuge($matches);
        /////////////////////////////////////////////////////////////////////////////////////////////////


        /*     Strucure example
               (
                    [select-day] => Array
                        (
                            [season] => Array
                                (
                                    [0] => Array
                                        (
                                            [for] => High season
                                            [value] => 14
                                        )

                                    [1] => Array
                                        (
                                            [for] => Low season
                                            [value] => 2-5
                                        )

                                )

                            [weekday] => Array
                                (
                                    [0] => Array
                                        (
                                            [for] => 1
                                            [value] => 4
                                        )

                                    [1] => Array
                                        (
                                            [for] => 5
                                            [value] => 3
                                        )

                                    [2] => Array
                                        (
                                            [for] => 6
                                            [value] => 2,7
                                        )

                                    [3] => Array
                                        (
                                            [for] => 0
                                            [value] => 7,14
                                        )

                                )

                        )

                )
         */
        $conditions = array();                                              // Create strucure from the options:
        foreach ($matches as $option) {
            if (! isset($conditions[ $option[1] ]) ) $conditions[ $option[1] ] = array();
            //                       select-day    season                     select-day    season
            if (! isset($conditions[ $option[1] ][$option[3]]) ) $conditions[ $option[1] ][ $option[3] ] = array();

            $conditions[ $option[1] ][ $option[3] ][]=array();
            $ind = count(  $conditions[ $option[1] ][ $option[3] ] ) - 1;   // Get index of the specific rule for the conditions.

            $conditions[ $option[1] ][ $option[3] ][$ind][ $option[4]  ] = $option[5];    // [for] => High season
            $conditions[ $option[1] ][ $option[3] ][$ind][ $option[6]  ] = $option[7];    // [value] => 14
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
//debuge($conditions);

        $script_code = '';                                                  // Define JS variables for the calendar

        //Define the Start day depend from the Season Filter
        if (isset($conditions['start-day']) ) {
            // S E A S O N S  conditions ///////////////////////////////////
            $seasons = array();
            if (isset($conditions['start-day']['season']) ) {

                $script_code .= "<script type='text/javascript'> jQuery(document).ready( function(){ ";     //FixIn: 5.4.5.12

                $script_code .= " if (typeof( wpdev_bk_seasons_conditions_for_start_day[". $bk_type. "] ) == 'undefined'){ ";
                $script_code .= " wpdev_bk_seasons_conditions_for_start_day[". $bk_type. "] = []; } ";

                foreach ($conditions['start-day']['season'] as $season) {

                    $seasons[]              = $season['for'];
                    $escaped_season_title   = wpdev_bk_get_escape_season_filter_name( $season['for'] );
                    $condition_season_value = rangeNumListToCommaNumList( $season['value'] );

                    $script_code .= " wpdev_bk_seasons_conditions_for_start_day[". $bk_type. "][ wpdev_bk_seasons_conditions_for_start_day[". $bk_type. "].length] = ['".$escaped_season_title."',[".$condition_season_value."]]; ";
                }
                $script_code .= " }); </script>";                               //FixIn: 5.4.5.12

                // Define Script for Applying specific CSS Classes into the dates in Calendar
                $script_code .= wpdev_bk_define_js_script_for_definition_season_filters( $seasons ) ;
            } //////////////////////////////////////////////////////////////

        }

        if (isset($conditions['select-day']) ) {

            // S E A S O N S  conditions ///////////////////////////////////
            $seasons = array();
            if (isset($conditions['select-day']['season']) ) {

                $script_code .= "<script type='text/javascript'> jQuery(document).ready( function(){ ";     //FixIn: 5.4.5.12

                $script_code .= " if (typeof( wpdev_bk_seasons_conditions_for_range_selection[". $bk_type. "] ) == 'undefined'){ ";
                $script_code .= " wpdev_bk_seasons_conditions_for_range_selection[". $bk_type. "] = []; } ";

                foreach ($conditions['select-day']['season'] as $season) {

                    $seasons[]              = $season['for'];
                    $escaped_season_title   = wpdev_bk_get_escape_season_filter_name( $season['for'] );
                    $condition_season_value = rangeNumListToCommaNumList( $season['value'] );

                    $script_code .= " wpdev_bk_seasons_conditions_for_range_selection[". $bk_type. "][ wpdev_bk_seasons_conditions_for_range_selection[". $bk_type. "].length] = ['".$escaped_season_title."',[".$condition_season_value."]]; ";
                }
                $script_code .= " }); </script>";                               //FixIn: 5.4.5.12
//debuge($seasons);
                // Define Script for Applying specific CSS Classes into the dates in Calendar
                $script_code .= wpdev_bk_define_js_script_for_definition_season_filters( $seasons ) ;
            } //////////////////////////////////////////////////////////////


            // Weekday conditions //////////////////////////////////////////
            if (isset($conditions['select-day']['weekday']) ) {

                $script_code .= "<script type='text/javascript'> jQuery(document).ready( function(){ ";     //FixIn: 5.4.5.12

                $script_code .= " if (typeof( wpdev_bk_weekday_conditions_for_range_selection[". $bk_type. "] ) == 'undefined'){ ";
                $script_code .= " wpdev_bk_weekday_conditions_for_range_selection[". $bk_type. "] = []; } ";

                foreach ($conditions['select-day']['weekday'] as $weekday) {
                    $day_of_week             = $weekday['for'];
                    $condition_weekday_value = rangeNumListToCommaNumList( $weekday['value'] );
                    $script_code .= " wpdev_bk_weekday_conditions_for_range_selection[". $bk_type. "][ wpdev_bk_weekday_conditions_for_range_selection[". $bk_type. "].length] = [".$day_of_week.",[".$condition_weekday_value."]]; ";
                }
                $script_code .= " }); </script>";                               //FixIn: 5.4.5.12
            } //////////////////////////////////////////////////////////////
        }
        return $script_code . $start_script_code;
    }




//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function get_default_booking_form($bk_type){
        global $wpdb;
        $res_view_max = $wpdb->get_results( $wpdb->prepare( "SELECT default_form FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = %d ",  $bk_type ) );
        $default_form =  $res_view_max[0]->default_form;
        if ($default_form == '') return 'standard';
        else return $default_form;
    }


     // Just Get ALL booking types from DB
    function get_standard_cost_for_bk_resource($booking_type_id = 0) {

        $res = $this->get_booking_types($booking_type_id);

        if (count($res)>0) {
            return $res[0]->cost;
        } else return 0;

    }

    // Just Get ALL booking types from DB
    function get_booking_types($booking_type_id = 0) {
        global $wpdb;
        $max_stringg_sql='';
        $order_type = 'title';

        if ( class_exists('wpdev_bk_biz_l')) {  // If Business Large then get resources from that
            $types_list = apply_bk_filter('get_booking_types_hierarhy_linear',array() );
            //$types_list = apply_bk_filter('multiuser_resource_list', $types_list);

            for ($i = 0; $i < count($types_list); $i++) {
                $types_list[$i]['obj']->count = $types_list[$i]['count'];
                $types_list[$i] = $types_list[$i]['obj'];
                if ( (isset($booking_type_id)) &&(isset($types_list[$i]->booking_type_id)) && ($booking_type_id != 0) && ($booking_type_id == $types_list[$i]->booking_type_id ) ) return $types_list[$i];
            }
            if ($booking_type_id == 0) return $types_list;
        }

        // Get booking resources only  as numbers                               //FixIn:5.4.3
        $booking_type_id_array = explode(',',$booking_type_id);
        $booking_type_id = array();
        foreach ( $booking_type_id_array as $bk_t ) {
            $bk_t = (int) $bk_t;
            if ( $bk_t > 0 )
                $booking_type_id[] = $bk_t;
        }
        $booking_type_id = implode(',',$booking_type_id);

        if ($booking_type_id == 0 ) {  // Normal getting
            $types_list = $wpdb->get_results( "SELECT booking_type_id as id, title, cost {$max_stringg_sql} FROM {$wpdb->prefix}bookingtypes  ORDER BY {$order_type}" );
        } else {
            $types_list = $wpdb->get_results( "SELECT booking_type_id as id, title, cost {$max_stringg_sql} FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id IN ( {$booking_type_id} )" );
        }
        //$types_list = apply_bk_filter('multiuser_resource_list', $types_list);

        return $types_list;
    }


    // Set meta data from booking type
    function set_bk_type_meta($type_id, $meta_key, $meta_value){
        global $wpdb;

        $result = $wpdb->get_results( $wpdb->prepare( "SELECT count(type_id) as cnt FROM {$wpdb->prefix}booking_types_meta WHERE type_id = %d AND meta_key = %s "
                                    , $type_id, $meta_key ) );
//debuge($type_id, $meta_key, $meta_value, $result);
        if ( $result[0]->cnt > 0 ) {
            if ( false === $wpdb->query(( "UPDATE {$wpdb->prefix}booking_types_meta SET meta_value = '".$meta_value."' WHERE type_id = " .  $type_id . " AND meta_key ='".$meta_key."'") ) ){
//debuge($type_id,$meta_key, $meta_value);
               debuge_error('Error during updating to DB booking availability of booking resource',__FILE__,__LINE__ );
               return false;
            }
        } else {
            if ( false === $wpdb->query(( "INSERT INTO {$wpdb->prefix}booking_types_meta ( type_id, meta_key, meta_value) VALUES ( " .  $type_id . ", '" .  $meta_key . "', '" .  $meta_value . "' );") ) ){
//debuge($type_id,$meta_key, $meta_value);
                debuge_error('Error during updating to DB booking availability of booking resource' ,__FILE__,__LINE__);
               return false;
            }
        }
        return true;
    }

    //Get available days depends from seaosn filter
    function get_available_days( $type_id ){
        $filters = array(); global $wpdb;
        $return_result = array('available'=>true,'days'=> $filters ) ;

        $availability_res = wpbc_get_resource_meta( $type_id, 'availability' );
        if ( count($availability_res)>0 ) {
            if ( is_serialized( $availability_res[0]->value ) )   $availability = unserialize($availability_res[0]->value);
            else                                                  $availability = $availability_res[0]->value;

            $days_avalaibility = $availability['general'];
            $seasonfilter      = $availability['filter'];
            if (is_array($seasonfilter))
                foreach ($seasonfilter as $key => $value) {
                    if ($value == 'On') {


                        if ( IS_USE_WPDEV_BK_CACHE ) {
                            global $wpbc_cache_season_filters;
                            $filter_id = $key;
                            if (! isset($wpbc_cache_season_filters)) $wpbc_cache_season_filters = array();
                            if (! isset($wpbc_cache_season_filters[$filter_id])) {
                                $result = $wpdb->get_results( "SELECT booking_filter_id as id, filter FROM {$wpdb->prefix}booking_seasons" );

                                foreach ($result as $value) {
                                    $wpbc_cache_season_filters[$value->id] = array($value);
                                }

								if ( isset( $wpbc_cache_season_filters[ $filter_id ] ) ) {
									$result = $wpbc_cache_season_filters[$filter_id];
								} else {
									$result = array();
								}

                            } else {
                                $result = $wpbc_cache_season_filters[$filter_id];
                            }
                        } else
                            $result = $wpdb->get_results( $wpdb->prepare( "SELECT filter FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d" , $key ) );
                        if (! empty($result))
                        foreach($result as $filter) {

                            //FixIn:6.0.1.8
                            if ( is_serialized( $filter->filter ) ) $filter_data = unserialize($filter->filter);
                            else                                    $filter_data = $filter->filter;

                            if ( isset($filter->id) ) $filters[$filter->id]=$filter_data;
                            else                      $filters[]=$filter_data;

                        }
                    }
                }
        }
          else  $days_avalaibility = 'On';


        if ( $days_avalaibility == 'On' ) $return_result['available'] = true;
        else                              $return_result['available'] = false;
        $return_result['days'] = $filters;
//debuge($return_result);
        return $return_result;
    }

    // Set available and unavailable days into calendar form using JS variables.
    function js_availability_filter($blank, $type_id ) { $script = '';
        $res_days = $this->get_available_days( $type_id );
//debuge($res_days);
        $version = '1.0';

        $script .= ' is_all_days_available['.$type_id.'] = ' . ($res_days['available']+0) . '; ';
        $script .= ' avalaibility_filters['.$type_id.'] = []; ';


            foreach ($res_days['days'] as $filter_id => $value) {                             //FixIn: 6.0.1.8

                $version = '1.0';

                if (isset($value['version']))                               // Version 2.0
                    if ($value['version'] == '2.0')   {
                        $version = '2.0';
                        $value_js_header =  '[ ["2.0"], [';
                        $value_js = '';
                        foreach ($value as $yy => $monthes) {
                            if ( ($yy != 'name') && ($yy != 'version') )
                                foreach ($monthes as $mm=>$days) {
                                    if ($mm>0)
                                        foreach ($days as $dd=>$dvalue) {
                                            if ($dvalue==1) {
                                               $value_js  .= '"' . $yy . '-' . $mm . '-' . $dd . '", ';
                                            }
                                        }
                                }
                        }

                        if ( ! empty( $value_js ) ) {                           //FixIn: 5.4.1
                            $value_js = substr($value_js, 0, -2);               // Delete last ", "
                            $value_js = $value_js_header . $value_js . '], '.$filter_id.' ]';     //FixIn: 6.0.1.8
                            $script .= ' avalaibility_filters['.$type_id.'][ avalaibility_filters['.$type_id.'].length ]= '.$value_js . '; ';
                        }
                    }


                if ($version == '1.0') {                                    // Version 1.0

                    $value_js =  '[ [   ';
                    foreach ($value['weekdays'] as $key => $val) { if ($val == 'On') $value_js .= $key . ', '; }// loop week days
                    $value_js =  substr($value_js, 0, -2); //Delete last ", "
                    $value_js .=  '], [   ';
                    foreach ($value['days'] as $key => $val) { if ($val == 'On') $value_js .= $key . ', '; }// loop all days numbers
                    $value_js =  substr($value_js, 0, -2); //Delete last ", "
                    $value_js .=  '], [   ';
                    foreach ($value['monthes'] as $key => $val) { if ($val == 'On') $value_js .= $key . ', '; }// loop all monthes nums
                    $value_js =  substr($value_js, 0, -2); //Delete last ", "
                    $value_js .=  '], [   ';
                    foreach ($value['year'] as $key => $val) { if ($val == 'On') $value_js .= $key . ', '; } // loop all years nums
                    $value_js =  substr($value_js, 0, -2); //Delete last ", "
                    $value_js .=  '], '.$filter_id.' ]';                      //FixIn: 6.0.1.8

                    // Time availability
                    //FixIn: 5.4.3
                    if (  (! empty($value['start_time']))  &&   (! empty($value['end_time']))  )  {
//                        $strt_time = explode(':',$value['start_time']);
//                        $fin_time = explode(':',$value['end_time']);
//                        $script .= ' if(typeof( global_avalaibility_times['.$type_id.']) == "undefined") {  global_avalaibility_times['.$type_id.'] = [];  }';
//
//
//
//                        if (  (count($res_days['days']) ==1 ) &&  (($res_days['available']+0) == 0)  ){
//                            $script .= ' is_all_days_available['.$type_id.'] = ' . 1 . '; '; // Set all days available
//                            // set start unavailable hours
//                            $script .= ' global_avalaibility_times['.$type_id.'][ global_avalaibility_times['.$type_id.'].length ]= [ ["00","00"],  ["'.$strt_time[0] . '", "'.$strt_time[1] . '"]  ]; ';
//                            // set end unavailable hours
//                            $script .= ' global_avalaibility_times['.$type_id.'][ global_avalaibility_times['.$type_id.'].length ]= [ ["'.$fin_time[0] . '", "'.$fin_time[1] . '"], ["23","59"]  ]; ';
//
//                        } else
//                          $script .= ' global_avalaibility_times['.$type_id.'][ global_avalaibility_times['.$type_id.'].length ]= [ ["'.$strt_time[0] . '", "'.$strt_time[1] . '"],  ["'.$fin_time[0] . '", "'.$fin_time[1] . '"]  ]; ';
//
                    } else
                     $script .= ' avalaibility_filters['.$type_id.'][ avalaibility_filters['.$type_id.'].length ]= '.$value_js . '; ';
                }
            }

        return $script;//' alert('.$type_id.'); ';
    }



    function get_unavailbale_dates_of_season_filters($blank, $type_id ){
        $res_days = $this->get_available_days( $type_id );

        return($res_days);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // B o o k i n g   F O R M S    customization
    // ////////////////////////////////////////////////




    ///////////////////////////////////////////////////////////
    // Get Content of CUSTOM Form  and CUSTOM Form Content data
    //
    //
    // Get Booking form Fields content
    function wpdev_get_booking_form( $booking_form_def_value, $my_booking_form_name ){

    	$default_return_form_content = $booking_form_def_value;
		$custom_form_name 			 = $my_booking_form_name;
		$serialized_form_content 	 = false;
		$what_to_return 			 = 'form';
    	return wpbc_get_custom_booking_form( $default_return_form_content, $custom_form_name, $serialized_form_content, $what_to_return );

    	/*
        $my_booking_form_name = str_replace( '  ', ' ', $my_booking_form_name );

        $booking_forms_extended = get_bk_option( 'booking_forms_extended' );

        if ( $booking_forms_extended !== false ) {

            $booking_forms_extended = maybe_unserialize( $booking_forms_extended );

            if ( is_array( $booking_forms_extended ) )
                foreach ( $booking_forms_extended as $value ) {

                    if ( isset( $value['name'] ) ) {
                        $value['name'] = str_replace( '  ', ' ', $value['name'] );

                        if ( $value['name'] == $my_booking_form_name ) {
                            return $value['form'];
                        }
                    }
                }
        }
        return $booking_form_def_value;
        */
    }







    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////






// C O S T   H I N T    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Check if total cost field exist, and if its exist get cost from it
    function check_if_cost_exist_in_field( $blank , $formmy, $booking_type ){

        $form_elements = get_form_content ($formmy, $booking_type);
        if (isset($form_elements['_all_']))
           if (isset($form_elements['_all_']['total_bk_cost' . $booking_type ])) {
                $fin_cost = $form_elements['_all_']['total_bk_cost' . $booking_type ];
                return $fin_cost;
           }

        return false;
    }


    // Set fields inside of form for editing total cost
    function wpdev_reapply_bk_form_for_cost_input($return_form, $bk_type){

        $my_form = '';

        if ( wpbc_is_new_booking_page() ) {
            $my_form =  '<div id="show_edit_cost_fields"><p><div class="legendspan">'.__('Standard booking resource cost' ,'booking') . ':</div> '. '<input type="text" disabled="disabled" value="'.$this->get_standard_cost_for_bk_resource($bk_type).'" id="standard_bk_cost'.$bk_type.'"  name="standard_bk_cost'.$bk_type.'" /></p>';
            $my_form .= '<p><div class="legendspan">'.__('Total booking resource cost' ,'booking') . ':</div>  '. '<input type="text" value="0" id="total_bk_cost'.$bk_type.'"  name="total_bk_cost'.$bk_type.'" /></p>';

            if ( strpos($_SERVER['REQUEST_URI'],'booking_hash') !== false ) {
                $my_form .= '<script type="text/javascript">jQuery(document).ready( function(){ ';
                if (isset($_GET['booking_hash'])) {
                    $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
                    if ($my_booking_id_type !== false) {
                        $booking_id = $my_booking_id_type[0];
                        $cost = apply_bk_filter('get_booking_cost_from_db', '', $booking_id);
                        $my_form .= ' jQuery("#total_bk_cost'.$bk_type.'").val("'.$cost.'") ';
                    }
                }
                $my_form .= '});</script></div>';
            } else {
                $my_form .= '<script type="text/javascript">jQuery(document).ready( function(){ if(typeof( showCostHintInsideBkForm ) == "function") { show_cost_init=setTimeout(function(){ showCostHintInsideBkForm('.$bk_type.'); },2500);  } });</script></div>';
            }
        }
        $return_form = str_replace('[cost_corrections]', $my_form, $return_form);

        return $return_form ;
    }




    // Check the form according show Hint and modificate it
    function wpdev_reapply_bk_form( $return_form, $bk_type, $my_booking_form = '' ){

        $cost_with_currency = wpbc_get_cost_with_currency_for_user( '0.00', $bk_type );

        $_POST['booking_form_type'] = $my_booking_form;                                                         // Its required for the correct calculation  of the Advanced Cost.
        $show_cost_hint = apply_bk_filter( 'advanced_cost_apply', 0, '', $bk_type, array(), true );             // Get info  to show advanced cost.
        $return_form    = apply_bk_filter( 'reupdate_static_cost_hints_in_form', $return_form, $bk_type );      //FixIn: 5.4.5.5

        if ( function_exists( 'get_booking_title' ) ) {
            $bk_title = get_booking_title( $bk_type );
            $bk_title = apply_bk_filter( 'wpdev_check_for_active_language', $bk_title );
        } else
            $bk_title = '';

        foreach ( $show_cost_hint as $key_name => $value ) {

            if (  strpos( $return_form, '['.$key_name.']' ) !== false ) {
//                $return_form = str_replace( '['.$key_name.']',
//                                            '<span id="bookinghint_' . $key_name . $bk_type.'">'.$cost_with_currency.'</span>'
//                                          . '<input style="display:none;" type="text" value="0.00" id="'.$key_name.''.$bk_type.'"  name="'.$key_name.''.$bk_type.'" />'
//                                          , $return_form);
                $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                                      'shortcode'  => '['.$key_name.']'
                                                    , 'span_class' => 'bookinghint_' . $key_name . $bk_type , 'span_value' => $cost_with_currency
                                                    , 'input_name' => $key_name . $bk_type                  , 'input_data' => '0.00'
                                        ) );

            }
        }

        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[cost_hint]'
                                            , 'span_class' => 'booking_hint' . $bk_type , 'span_value' => $cost_with_currency
                                            , 'input_name' => 'cost_hint'    . $bk_type , 'input_data' => '0.00'
                                ) );
        //FixIn: 8.4.2.1
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[estimate_day_cost_hint]'
                                            , 'span_class' => 'estimate_booking_day_cost_hint' . $bk_type , 'span_value' => $cost_with_currency
                                            , 'input_name' => 'estimate_day_cost_hint'    . $bk_type , 'input_data' => '0.00'
                                ) );
        //FixIn: 8.4.4.7
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[estimate_night_cost_hint]'
                                            , 'span_class' => 'estimate_booking_night_cost_hint' . $bk_type , 'span_value' => $cost_with_currency
                                            , 'input_name' => 'estimate_night_cost_hint'    . $bk_type , 'input_data' => '0.00'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[original_cost_hint]'
                                            , 'span_class' => 'original_booking_hint' . $bk_type , 'span_value' => $cost_with_currency
                                            , 'input_name' => 'original_cost_hint'    . $bk_type , 'input_data' => '0.00'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[additional_cost_hint]'
                                            , 'span_class' => 'additional_booking_hint' . $bk_type , 'span_value' => $cost_with_currency
                                            , 'input_name' => 'additional_cost_hint'    . $bk_type , 'input_data' => '0.00'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[deposit_hint]'
                                            , 'span_class' => 'deposit_booking_hint' . $bk_type , 'span_value' => $cost_with_currency
                                            , 'input_name' => 'deposit_hint'         . $bk_type , 'input_data' => '0.00'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[coupon_discount_hint]'
                                            , 'span_class' => 'coupon_discount_booking_hint' . $bk_type , 'span_value' => $cost_with_currency
                                            , 'input_name' => 'coupon_discount_hint' . $bk_type , 'input_data' => '0.00'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[balance_hint]'
                                            , 'span_class' => 'balance_booking_hint' . $bk_type , 'span_value' => $cost_with_currency
                                            , 'input_name' => 'balance_hint'         . $bk_type , 'input_data' => '0.00'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[resource_title_hint]'
                                            , 'span_class' => 'resource_title_hint_tip' . $bk_type , 'span_value' => $bk_title
                                            , 'input_name' => 'resource_title_hint'     . $bk_type , 'input_data' => $bk_title
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(                                                                         // Dates and Times Hints
                                              'shortcode'  => '[check_in_date_hint]'
                                            , 'span_class' => 'check_in_date_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'check_in_date_hint'     . $bk_type , 'input_data' => '...'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[check_out_date_hint]'
                                            , 'span_class' => 'check_out_date_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'check_out_date_hint'     . $bk_type , 'input_data' => '...'
                                ) );
        //FixIn: 8.0.2.12
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[check_out_plus1day_hint]'
                                            , 'span_class' => 'check_out_plus1day_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'check_out_plus1day_hint'     . $bk_type , 'input_data' => '...'
                                ) );

        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[start_time_hint]'
                                            , 'span_class' => 'start_time_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'start_time_hint'     . $bk_type , 'input_data' => '...'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[end_time_hint]'
                                            , 'span_class' => 'end_time_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'end_time_hint'     . $bk_type , 'input_data' => '...'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[selected_dates_hint]'
                                            , 'span_class' => 'selected_dates_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'selected_dates_hint'     . $bk_type , 'input_data' => '...'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[selected_timedates_hint]'
                                            , 'span_class' => 'selected_timedates_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'selected_timedates_hint'     . $bk_type , 'input_data' => '...'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[selected_short_dates_hint]'
                                            , 'span_class' => 'selected_short_dates_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'selected_short_dates_hint'     . $bk_type , 'input_data' => '...'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[selected_short_timedates_hint]'
                                            , 'span_class' => 'selected_short_timedates_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'selected_short_timedates_hint'     . $bk_type , 'input_data' => '...'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[days_number_hint]'
                                            , 'span_class' => 'days_number_hint_tip' . $bk_type , 'span_value' => '<br><br>'
                                            , 'input_name' => 'days_number_hint'     . $bk_type , 'input_data' => '<br><br>'
                                ) );
        $return_form = wpbc_replace_shortcode_hint( $return_form, array(
                                              'shortcode'  => '[nights_number_hint]'
                                            , 'span_class' => 'nights_number_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'nights_number_hint'     . $bk_type , 'input_data' => '...'
                                ) );

        $return_form = $this->wpdev_reapply_bk_form_for_cost_input($return_form, $bk_type);
        if (function_exists('wpdev_bk_form_conditions_parsing'))
            $return_form = wpdev_bk_form_conditions_parsing( $return_form, $bk_type);
        return $return_form ;
    }


    // Ajax function call, for showing cost
    function wpdev_ajax_show_cost(){

        make_bk_action('check_multiuser_params_for_client_side', $_POST[ "bk_type"] );

        // TODO: Set for multiuser - user ID (ajax request do not transfear it
        // $this->client_side_active_params_of_user

        $str_dates__dd_mm_yyyy = $_POST[ "all_dates" ];                         //FixIn: 5.4.5.2
        $booking_type          = $_POST[ "bk_type"];
        $booking_form_data     = $_POST['form'];

//debuge('$str_dates__dd_mm_yyyy, $booking_type, $booking_form_data', $str_dates__dd_mm_yyyy, $booking_type, $booking_form_data);

        $dates_in_diff_formats = wpbc_get_dates_in_diff_formats( $str_dates__dd_mm_yyyy, $booking_type, $booking_form_data );

	    //FixIn: 8.2.1.28
		if ( 'On' === get_bk_option( 'booking_last_checkout_day_available' ) ) {
			// Remove last  date from  the cost  calculation  function,  if this option last_checkout_day_available activated.
			array_pop( $dates_in_diff_formats['array'] );
			$str_dates__dd_mm_yyyy = $dates_in_diff_formats['array'];
			foreach ( $str_dates__dd_mm_yyyy as $d_k => $d_v ) {
				$str_dates__dd_mm_yyyy[ $d_k ] =  gmdate( "d.m.Y", strtotime( $d_v ));
			}
			$str_dates__dd_mm_yyyy = implode( ', ', $str_dates__dd_mm_yyyy );
			$dates_in_diff_formats = wpbc_get_dates_in_diff_formats( $str_dates__dd_mm_yyyy, $booking_type, $booking_form_data );
		}
		//FixIn: 8.2.1.28 end

        $start_time         = $dates_in_diff_formats['start_time'];             // Array( [0] => 10, [1] => 00, [2] => 01 )
        $end_time           = $dates_in_diff_formats['end_time'];               // Array( [0] => 12, [1] => 00, [2] => 02 )
        $only_dates_array   = $dates_in_diff_formats['array'];                  // [0] => '2015-10-15', [1] => '2015-10-15'
        $dates              = $dates_in_diff_formats['string'];                 // '15.12.2015, 16.12.2015, 17.12.2015'

//debuge('$dates_in_diff_formats', $start_time, $end_time, $only_dates_array, $dates_in_diff_formats);


        // Get cost of main calendar with all rates discounts and  so on...
        $summ = apply_filters('wpdev_get_booking_cost', $booking_type, $dates, array( $start_time, $end_time ), $booking_form_data );
        $summ = floatval( $summ );
        //$summ = round($summ,2);								//FixIn: 8.1.3.33

        $summ_original = apply_bk_filter('wpdev_get_bk_booking_cost', $booking_type, $dates, array($start_time, $end_time ), $booking_form_data, true , true );
        $summ_original = floatval( $summ_original );
        //$summ_original = round($summ_original,2);				//FixIn: 8.1.3.33


        //TODO: 10/03/2015 - Finish here
        $show_cost_hint = apply_bk_filter('advanced_cost_apply', $summ_original , $booking_form_data, $booking_type, explode(',', $dates) , true );    // Get info  to show advanced cost.

//debuge( $show_cost_hint );

        // Get description according coupons discount for main calendar if its exist
        $coupon_info_4_main_calendar = apply_bk_filter('wpdev_get_additional_description_about_coupons', '', $booking_type, $dates, array($start_time, $end_time ), $booking_form_data   );
		$coupon_discount_value		 = apply_bk_filter('wpbc_get_coupon_code_discount_value', '', $booking_type, $dates, array($start_time, $end_time ), $booking_form_data   );

        // Check additional cost based on several calendars inside of this form //////////////////////////////////////////////////////////////
        $additional_calendars_cost = $this->check_cost_for_additional_calendars( $summ, $booking_form_data, $booking_type,  array( $start_time, $end_time ) );
        $summ_total       = $additional_calendars_cost[0];
        $summ_additional  = $additional_calendars_cost[1];
        $dates_additional = $additional_calendars_cost[2];

        $additional_description = '';
        $additional_dates_description = '';    //FixIn: 8.3.3.3
		if ( ! empty( $dates_additional ) ) {  // we have additional calendars inside of this form
            // Additional calendars - dates
            foreach ( $dates_additional as $key => $ss) {
            	$dates_in_diff_formats_additional = wpbc_get_dates_in_diff_formats( $ss, $key, $booking_form_data );
 				$full_additional_days = [];
	            foreach ( $dates_in_diff_formats_additional['array'] as $ful_add_day ) {
					$full_additional_days[]= $ful_add_day . ' 00:00:00';
 				}
	            $additional_dates_description .= '<br/>' . get_booking_title( $key ) . ': ' .  wpbc_get_dates_short_format( implode( ',', $full_additional_days ) );
            }
		}

        if ( count($summ_additional)>0 ) {  // we have additional calendars inside of this form

            // Main calendar description and discount info //
            $additional_description .= '<br />' . get_booking_title( $booking_type ) . ': ' . wpbc_get_cost_with_currency_for_user( $summ, $booking_type );
            if ($coupon_info_4_main_calendar != '')
                $additional_description .=   $coupon_info_4_main_calendar ;
            $coupon_info_4_main_calendar = '';
            $additional_description .= '<br />' ;


            // Additional calendars - info and discounts //
            foreach ($summ_additional as $key=>$ss) {

                $additional_description .= get_booking_title($key) . ': ' . wpbc_get_cost_with_currency_for_user( $ss, $key );

                // Discounts info ///////////////////////////////////////////////////////////////////////////////////////////////////////
                $form_content_for_specific_calendar = wpbc_get_form_with_replaced_id( $booking_form_data, $key ,  $booking_type );
                $dates_in_specific_calendar = $dates_additional[$key];
                $coupon_info_4_calendars = apply_bk_filter('wpdev_get_additional_description_about_coupons', '', $key , $dates_in_specific_calendar , array($start_time, $end_time ), $form_content_for_specific_calendar );
                if ($coupon_info_4_calendars != '')
                    $additional_description .= $coupon_info_4_calendars ;
                $coupon_info_4_calendars = '';
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $additional_description .= '<br />' ;
            }

        }
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        $summ_deposit = apply_bk_filter('fixed_deposit_amount_apply', $summ_total , $booking_form_data, $booking_type , $dates ); // Apply fixed deposit
//        $summ_deposit = apply_bk_filter('advanced_cost_apply', $summ_deposit , $booking_form_data, $booking_type, explode(',', $dates)  );    // Fix: 6.1.1.12
        if ($summ_deposit != $summ_total )  $is_deposit = true;
        else                                $is_deposit = false;
        $summ_balance = $summ_total - $summ_deposit;
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $summ_additional_hint = $summ_total - $summ_original;

        $summ_original         = wpbc_get_cost_with_currency_for_user( $summ_original, $booking_type );
        $summ_additional_hint  = wpbc_get_cost_with_currency_for_user( $summ_additional_hint, $booking_type );
        $summ_total_orig       = $summ_total;
        $summ_total            = wpbc_get_cost_with_currency_for_user( $summ_total, $booking_type );
        $summ_deposit          = wpbc_get_cost_with_currency_for_user( $summ_deposit, $booking_type );
		$coupon_discount_value_hint = wpbc_get_cost_with_currency_for_user( $coupon_discount_value, $booking_type );
        $summ_balance          = wpbc_get_cost_with_currency_for_user( $summ_balance, $booking_type );

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Dates and Times Hints: ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $check_in_date_hint             =
        $check_out_date_hint            =
        $start_time_hint                =
        $end_time_hint                  =
        $selected_dates_hint            =
        $selected_timedates_hint        =
        $selected_short_dates_hint      =
        $selected_short_timedates_hint  =
        $days_number_hint               =
        $nights_number_hint             =  '...';

        if ( ! empty( $only_dates_array ) ) {

            if ( (! isset($start_time[0])) || ($start_time[0] == '') ) $start_time[0] = '00';
            if ( (! isset($start_time[1])) || ($start_time[1] == '') ) $start_time[1] = '00';
            if ( (! isset($end_time[0])) || ($end_time[0] == '') ) $end_time[0] = '00';
            if ( (! isset($end_time[1])) || ($end_time[1] == '') ) $end_time[1] = '00';


            $selected_dates_hint            =  array();
            $selected_timedates_hint        =  array();

            $days_and_times = array();
            $only_full_days = array();
            foreach ( $only_dates_array as $day_num => $day ) {

                if ($day_num==0) { //First  date
                    $days_and_times[] = $day . ' '.$start_time[0].':'.$start_time[1].':'.$start_time[2];
                } else if ( $day_num == ( count( $only_dates_array ) - 1 ) ) {  //Last date
                    $days_and_times[] = $day . ' '.$end_time[0].':'.$end_time[1].':'.$end_time[2];
                } else {
                    $days_and_times[] = $day . ' 00:00:00';
                }
                $only_full_days[] = $day . ' 00:00:00';

                // Wide Dates
                $selected_dates_hint[]      = wpbc_change_dates_format( $only_full_days[ (count($only_full_days)-1) ] ) ;
                $selected_timedates_hint[]  = wpbc_change_dates_format( $days_and_times[ (count($days_and_times)-1) ] ) ;
            }

            // Remove duplicated same dates, if we are selected only 1 date
            $selected_dates_hint     = array_values( array_unique( $selected_dates_hint ) );
            $selected_timedates_hint = array_values( array_unique( $selected_timedates_hint ) );

            // Number of days & nights
            $days_number_hint               = count( $selected_dates_hint );
            $nights_number_hint             = ($days_number_hint>1) ? ($days_number_hint-1) : $days_number_hint;

            // Wide Dates
            $selected_dates_hint            = implode(', ', $selected_dates_hint );
            $selected_timedates_hint        = implode(', ', $selected_timedates_hint );

            //Short Dates
            $selected_short_timedates_hint  = wpbc_get_dates_short_format(  implode(',', $days_and_times) );
            $only_full_days = array_values(array_unique($only_full_days));
            $selected_short_dates_hint      = wpbc_get_dates_short_format(  implode(',', $only_full_days) );

            $selected_short_timedates_hint 	.= $additional_dates_description;    //FixIn: 8.3.3.3
            $selected_short_dates_hint 		.= $additional_dates_description;    //FixIn: 8.3.3.3

            // Check  In / Out Dates
            $check_in_date_hint             = wpbc_change_dates_format( $only_full_days[0]  );
            $check_out_date_hint            = wpbc_change_dates_format( $only_full_days[ (count($only_full_days)-1) ] );
            //FixIn: 8.0.2.12
            $check_out_plus1day_hint 		= wpbc_change_dates_format( date( 'Y-m-d H:i:s', strtotime( '+1 day', strtotime( $only_full_days[ (count($only_full_days)-1) ] ) ) ) );

            // Times:
            $time_format = get_bk_option( 'booking_time_format');
            if ( $time_format === false  ) $time_format = '';

            $start_time_hint = date_i18n( $time_format, mktime( $start_time[0], $start_time[1], $start_time[2] ) );
            $end_time_hint   = date_i18n( $time_format, mktime( $end_time[0], $end_time[1], $end_time[2] ) );
        }

        ?> <script type="text/javascript">
                if ( jQuery('#booking_hint<?php echo $booking_type; ?>' ).length > 0 ) {
                    jQuery( '#booking_hint<?php echo $booking_type; ?>,.booking_hint<?php echo $booking_type; ?>' ).html( '<?php
                        echo ( ( $summ_total . $coupon_info_4_main_calendar . $additional_description ) ); ?>' );
                    jQuery( '#cost_hint<?php echo $booking_type; ?>' ).val( '<?php
                        echo strip_tags( ( ( $summ_total  ) ) ); ?>' );
                }
            <?php
            foreach ( $show_cost_hint as $cost_hint_key => $cost_hint_value ) {
                ?>
                if ( jQuery('#bookinghint_<?php echo $cost_hint_key . $booking_type ; ?>' ).length > 0 ) {
                    jQuery( '#bookinghint_<?php echo $cost_hint_key . $booking_type; ?>,.bookinghint_<?php echo $cost_hint_key . $booking_type; ?>' ).html( '<?php
                        echo ( ( wpbc_get_cost_with_currency_for_user( $cost_hint_value, $booking_type ) ) ); ?>' );
                    jQuery( '#<?php echo $cost_hint_key . $booking_type; ?>' ).val( '<?php
                        echo strip_tags( ( ( wpbc_get_cost_with_currency_for_user( $cost_hint_value, $booking_type )  ) ) ); ?>' );
                }
                <?php
            }
            ?>
				<?php
				//FixIn: 8.4.2.1
				$estimate_day_cost_hint  = wpbc_get_cost_with_currency_for_user( $summ_total_orig / $days_number_hint, $booking_type );
				$estimate_day_cost_hint_val  = strip_tags( $estimate_day_cost_hint );
				?>
                jQuery( '#estimate_booking_day_cost_hint<?php echo $booking_type;
                      ?>,.estimate_booking_day_cost_hint<?php echo $booking_type; ?>' ).html( '<?php   echo  ( $estimate_day_cost_hint ); ?>' );
                jQuery( '#estimate_day_cost_hint<?php    echo $booking_type; ?>' ).val( '<?php echo( $estimate_day_cost_hint_val ); ?>' );

				<?php
				//FixIn: 8.4.4.7
				$estimate_night_cost_hint  = wpbc_get_cost_with_currency_for_user( $summ_total_orig / $nights_number_hint, $booking_type );
				$estimate_night_cost_hint_val  = strip_tags( $estimate_night_cost_hint );
				?>
                jQuery( '#estimate_booking_night_cost_hint<?php echo $booking_type;
                      ?>,.estimate_booking_night_cost_hint<?php echo $booking_type; ?>' ).html( '<?php   echo  ( $estimate_night_cost_hint ); ?>' );
                jQuery( '#estimate_night_cost_hint<?php    echo $booking_type; ?>' ).val( '<?php echo( $estimate_night_cost_hint_val ); ?>' );

                jQuery( '#additional_booking_hint<?php echo $booking_type;
                      ?>,.additional_booking_hint<?php echo $booking_type; ?>' ).html( '<?php            echo ( ( $summ_additional_hint ) ); ?>' );
                jQuery( '#additional_cost_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $summ_additional_hint ) ) ); ?>' );

                jQuery( '#original_booking_hint<?php echo $booking_type;
                      ?>,.original_booking_hint<?php echo $booking_type; ?>' ).html( '<?php            echo ( ( $summ_original ) ); ?>' );
                jQuery( '#original_cost_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $summ_original ) ) ); ?>' );

                jQuery( '#deposit_booking_hint<?php echo $booking_type;
                      ?>,.deposit_booking_hint<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $summ_deposit ) ); ?>' );
                jQuery( '#deposit_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $summ_deposit ) ) ); ?>' );

                jQuery( '#coupon_discount_booking_hint<?php echo $booking_type;
                      ?>,.coupon_discount_booking_hint<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $coupon_discount_value_hint ) ); ?>' );
                jQuery( '#coupon_discount_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $coupon_discount_value ) ) ); ?>' );

                jQuery( '#balance_booking_hint<?php echo $booking_type;
                      ?>,.balance_booking_hint<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $summ_balance ) ); ?>' );
                jQuery( '#balance_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $summ_balance ) ) ); ?>' );

                if ( jQuery('#total_bk_cost<?php echo $booking_type; ?>' ).length > 0 ) {
                    if (
                             ( jQuery( '#total_bk_cost<?php echo $booking_type; ?>' ).val() == 0 )
                          || ( location.href.indexOf('booking_hash') === -1 )                           //FixIn: 7.0.1.28
                        ) jQuery( '#total_bk_cost<?php echo $booking_type; ?>' ).val( '<?php
                            echo strip_tags( ( ( $summ_total_orig  ) ) ); ?>' );
                }

                jQuery( '#coupon_discount_hint<?php echo $booking_type;
                      ?>,.coupon_discount_hint<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $coupon_discount_value ) ); ?>' );
                jQuery( '#coupon_discount<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $coupon_discount_value ) ) ); ?>' );

                // Dates and Times shortcodes:
                jQuery( '#check_in_date_hint_tip<?php echo $booking_type;
                      ?>,.check_in_date_hint_tip<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $check_in_date_hint ) ); ?>' );
                jQuery( '#check_in_date_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $check_in_date_hint ) ) ); ?>' );

                jQuery( '#check_out_date_hint_tip<?php echo $booking_type;
                      ?>,.check_out_date_hint_tip<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $check_out_date_hint ) ); ?>' );
                jQuery( '#check_out_date_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $check_out_date_hint ) ) ); ?>' );
				//FixIn: 8.0.2.12
                jQuery( '#check_out_plus1day_hint_tip<?php echo $booking_type;
                      ?>,.check_out_plus1day_hint_tip<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $check_out_plus1day_hint ) ); ?>' );
                jQuery( '#check_out_plus1day_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $check_out_plus1day_hint ) ) ); ?>' );
				// End fix
                jQuery( '#start_time_hint_tip<?php echo $booking_type;
                      ?>,.start_time_hint_tip<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $start_time_hint ) ); ?>' );
                jQuery( '#start_time_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $start_time_hint ) ) ); ?>' );

                jQuery( '#end_time_hint_tip<?php echo $booking_type;
                      ?>,.end_time_hint_tip<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $end_time_hint ) ); ?>' );
                jQuery( '#end_time_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $end_time_hint ) ) ); ?>' );

                jQuery( '#selected_dates_hint_tip<?php echo $booking_type;
                      ?>,.selected_dates_hint_tip<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $selected_dates_hint ) ); ?>' );
                jQuery( '#selected_dates_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $selected_dates_hint ) ) ); ?>' );

                jQuery( '#selected_timedates_hint_tip<?php echo $booking_type;
                      ?>,.selected_timedates_hint_tip<?php echo $booking_type; ?>' ).html( '<?php       echo ( ( $selected_timedates_hint ) ); ?>' );
                jQuery( '#selected_timedates_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $selected_timedates_hint ) ) ); ?>' );

                jQuery( '#selected_short_dates_hint_tip<?php echo $booking_type;
                      ?>,.selected_short_dates_hint_tip<?php echo $booking_type; ?>' ).html( '<?php           echo ( ( $selected_short_dates_hint ) ); ?>' );
                jQuery( '#selected_short_dates_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $selected_short_dates_hint ) ) ); ?>' );

                jQuery( '#selected_short_timedates_hint_tip<?php echo $booking_type;
                      ?>,.selected_short_timedates_hint_tip<?php echo $booking_type; ?>' ).html( '<?php           echo ( ( $selected_short_timedates_hint ) ); ?>' );
                jQuery( '#selected_short_timedates_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $selected_short_timedates_hint ) ) ); ?>' );

                jQuery( '#days_number_hint_tip<?php echo $booking_type; ?>,.days_number_hint_tip<?php echo $booking_type; ?>' ).html('<br>Selected days: <?php echo (($days_number_hint)); ?>' );
                jQuery( '#days_number_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $days_number_hint ) ) ); ?>' );

                jQuery( '#nights_number_hint_tip<?php echo $booking_type;
                      ?>,.nights_number_hint_tip<?php echo $booking_type; ?>' ).html( '<?php           echo ( ( $nights_number_hint ) ); ?>' );
                jQuery( '#nights_number_hint<?php    echo $booking_type; ?>' ).val( '<?php echo strip_tags( ( ( $nights_number_hint ) ) ); ?>' );


                var balance = jQuery('#leave-balance').val();
                var selectedDays = <?php echo (($days_number_hint)); ?>;
                var result = balance-selectedDays;
                jQuery( '#leave-calculator #result' ).show().html('Selected days: '+selectedDays+'<br> Your leave balance is: '+balance+ '<br> Your leave balance after this will be:'+result);

                jQuery("#leave-balance").on('keyup click focus', function() {
                  balance = jQuery(this).val();
                  result = balance-selectedDays;
                  jQuery( '#leave-calculator #result' ).show().html('Selected days: '+selectedDays+'<br> Your leave balance is: '+balance+ '<br> Your leave balance after this will be:'+result);
                });

                if(selectedDays > 1 && selection == "compassionate-leave") {
                  jQuery('.datepick-days-cell').not('.datepick-current-day').removeClass('date_available').addClass('datepick-unselectable');
                }
                if(selectedDays > 2 && selection == "compassionate-leave") {
                  location.reload();
                }


           </script> <?php
    }



    /////////////////////////////////////////////////////////////////////////////////////


// R A T E S  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



    // Define JavaScript variable for showing tooltip rates for 1 day
    function show_rates_at_calendar($blank, $type_id ) {  $start_script_code = '';

        // Save at the Advnaced settings these 2 parameters
        $is_show_cost_in_tooltips =    get_bk_option( 'booking_is_show_cost_in_tooltips' );
        $highlight_cost_word = get_bk_option( 'booking_highlight_cost_word'); ;
        $highlight_cost_word = apply_bk_filter('wpdev_check_for_active_language', $highlight_cost_word );

        $is_show_cost_in_date_cell =    get_bk_option( 'booking_is_show_cost_in_date_cell' );
        $booking_cost_in_date_cell_currency  = get_bk_option( 'booking_cost_in_date_cell_currency');

        if ( ( $is_show_cost_in_tooltips !== 'On' ) && ( $is_show_cost_in_date_cell !== 'On' ) )
            return $start_script_code;

        if ( $is_show_cost_in_tooltips == 'On' )
            $start_script_code .= ' is_show_cost_in_tooltips = true; ';

        if ( $is_show_cost_in_date_cell == 'On' )
            $start_script_code .= ' is_show_cost_in_date_cell = true; ';


        $cost_currency = get_bk_option( 'booking_paypal_curency' );
        if ($cost_currency == 'USD' ) $cost_currency = '$';
        elseif ($cost_currency == 'EUR' ) $cost_currency = '&euro;';
        $start_script_code .= " cost_curency =  '". esc_js( $highlight_cost_word /*.$cost_currency*/ ) . " '; ";        //FixIn: 7.0.1.49

        $start_script_code .= " wpbc_curency_symbol =  '". esc_js($booking_cost_in_date_cell_currency) . "'; ";         //FixIn: 7.0.1.49

        // Get cost of 1 time unit
        $cost = 0;
        $result = $this->get_booking_types($type_id); // Main info according booking type
        if ( count($result)>0 )  $cost = $result[0]->cost;

        // Get period of costs - multiplier
        $price_period        =  get_bk_option( 'booking_paypal_price_period' );

        if ($price_period == 'day') {
            $cost_multiplier = 1;
        } elseif ($price_period == 'night') {
            $cost_multiplier = 1;
        } elseif ($price_period == 'hour') {
            $cost_multiplier = 24;
        } else {
            $cost_multiplier = 1;
        }


        $prices_per_day = array();                                          // PHP Debug
        $prices_per_day[$type_id] = array();                                // PHP Debug

        $start_script_code .= "  prices_per_day[". $type_id ."] = [] ;  ";

        $max_monthes_in_calendar = wpdev_bk_get_max_days_in_calendar();
        $my_day =  date('m.d.Y' );          // Start days from TODAY

        for ($i = 0; $i < $max_monthes_in_calendar ; $i++) {

            $my_day_arr = explode('.',$my_day);

            $day = ($my_day_arr[1]+0);
            $month= ($my_day_arr[0]+0);
            $year = ($my_day_arr[2]+0);

            $my_day_tag =   $month . '-' . $day . '-' . $year ;

            $fin_day_cost =  $this->get_1_day_cost_apply_rates($type_id, $cost, $day , $month, $year );
            //$fin_day_cost = round($fin_day_cost,2);//FixIn: 8.1.3.33
			$fin_day_cost = wpbc_cost_show( $fin_day_cost , array( 'currency' => '&nbsp;' ) ) ;								//FixIn: 7.2.1.11
			$fin_day_cost = strip_tags($fin_day_cost);

            $prices_per_day[$type_id][$my_day_tag] = $fin_day_cost;         // PHP Debug
            if ( ! empty( $fin_day_cost) )                                                                                //FixIn: 8.1.2.9
                $start_script_code .= "  prices_per_day[". $type_id ."]['".$my_day_tag."'] = '".$fin_day_cost."' ;  ";

            $my_day =  date('m.d.Y' , mktime(0, 0, 0, $month, ($day+1), $year ));
        }

        //debuge($prices_per_day); die;                                     // PHP Debug

        return $start_script_code;
    }

    // Apply season rates to D A Y S array with/without $time_array   -   send from P A Y P A L form
    // $days_array = array( 'dd.mm.yyyy', 'dd.mm.yyyy', ... )
    function apply_season_rates( $paypal_dayprice, $days_array, $booking_type, $times_array, $post_form ) {

// debuge($paypal_dayprice, $days_array, $booking_type, $times_array, $post_form, 'test ');

     if ($times_array[0] ==  array('00','00','00') ) $times_array[0] =  array('00','00','01');
     if ($times_array[1] ==  array('00','00','02') ) $times_array[1] =  array('24','00','02');

        $one_night = 0;
        $paypal_price_period        =  get_bk_option( 'booking_paypal_price_period' );
        $costs_depends_from_selection_new = array();
        if ($paypal_price_period == 'day') {

            $costs_depends_from_selection = wpbc_get_valuation_days_array($booking_type, $days_array, $times_array );
//debuge($costs_depends_from_selection);
            if ($costs_depends_from_selection !== false) {
                $costs_depends_from_selection[0]=0;
                for ($ii = 1; $ii < count($costs_depends_from_selection); $ii++) {
                    $costs_depends_from_selection_new[] = $costs_depends_from_selection[$ii];
                }
            }
//debuge($costs_depends_from_selection_new);
        }elseif ($paypal_price_period == 'night') {
//debuge($paypal_price_period);
            if (count($days_array)>1) {
                if (  ( ($times_array[0] == array('00','00','01') )  && ($times_array[1] == array('00','00','00') ))  ||
                      ( ($times_array[0] == array('00','00','01') )  && ($times_array[1] == array('24','00','02') ))
                   ) { $one_night = 1; }
            }

//array_pop( $days_array );$one_night = 0; //Basically  we need to remove one day  if we are using "Valuation days" cos t settings and cost per 1 night,  but still  something is going wrong.

            $costs_depends_from_selection = wpbc_get_valuation_days_array($booking_type, $days_array, $times_array );
//debuge($costs_depends_from_selection)      ;
            if ($costs_depends_from_selection !== false) {
                $costs_depends_from_selection[0]=0;
                //for ($ii = 1; $ii < count($costs_depends_from_selection); $ii++) {
                for ($ii = 1; $ii < ( count($costs_depends_from_selection) ); $ii++) {
                    $costs_depends_from_selection_new[] = $costs_depends_from_selection[$ii];
                    $one_night = 0;
                }
//debuge($costs_depends_from_selection_new);
                if ( count($costs_depends_from_selection_new) > 1 ) {
                    //If we have default value for last day - "100%" - its means no setting of "Valuation days",  then  set cost  for this day  to 0, becase of cost  per night
                    if ( $costs_depends_from_selection_new[ (count($costs_depends_from_selection_new) -1 ) ] == '100%' )    //FixIn: 7.0.1.4
                         $costs_depends_from_selection_new[ (count($costs_depends_from_selection_new) -1 ) ] = 0;

                }
            }


        }elseif ($paypal_price_period == 'hour') {


        } else {
                //return array($paypal_dayprice); //fixed
        }

        $days_rates = array();
        //FixIn: 6.2.3.2
        if ( count($days_array) == 1 ) {
            $d_day = $days_array[0];
            if (! empty($d_day)) {
                $d_day = explode('.',$d_day);
                $day =  ($d_day[0]+0); $month =  ($d_day[1]+0); $year = ($d_day[2]+0);
                $start_time_in_ms = mktime($times_array[0][0], $times_array[0][1], $times_array[0][2], $month, $day, $year );
                $end_time_in_ms = mktime($times_array[1][0], $times_array[1][1], $times_array[1][2], $month, $day, $year );
                if ( ( $end_time_in_ms - $start_time_in_ms ) < 0 ) {
                    //We need to  add one extra day,  because the end time outside of 24:00 already
                    $days_array[] = date('d.m.Y', mktime(0, 0, 0, $month, ($day+1), $year )  );
                }
            }
        }
        //FixIn: 6.2.3.2 - end
        for($i=0;$i<(count($days_array) - $one_night );$i++){ $d_day = $days_array[$i];
//debuge($days_array);
           if (! empty($d_day)) {
//            foreach ($days_array as $d_day) { $i++;
               $times_array_check = array(array('00','00','01'),array('24','00','02'));
               if ( $i==0 )                   { $times_array_check[0] =  $times_array[0]; }
               if ( $i == (count($days_array) -1- $one_night )) { $times_array_check[1] =  $times_array[1]; }

                //$times_array_check = array($times_array[0],$times_array[1]);  // Its will make cost calculation only between entered times, even on multiple days
               $d_day = explode('.',$d_day);
               $day =  ($d_day[0]+0); $month =  ($d_day[1]+0); $year = ($d_day[2]+0);
               $week =  date('w', mktime(0, 0, 0, $month, $day, $year) );
               $days_rates[] = $this->get_1_day_cost_apply_rates($booking_type, $paypal_dayprice, $day , $month, $year, $times_array_check , $post_form );
            }
        }
        //if (count($days_rates)>1) $days_rates[count($days_rates)-1] = 0;
        // If fixed deposit so take only for first day cost

        if ($paypal_price_period == 'fixed') { if (count($days_rates)>0) { $days_rates = array($days_rates[0]); } else {$days_rates = array();} }
//debuge($days_rates);

        /**/
//debuge($costs_depends_from_selection_new);
        if ( ( count($costs_depends_from_selection_new)>0)  &&
             (! ( (count($days_array) == 1 )  && (empty($days_array[0])) ) )
           ){
            $rates_with_procents = array();
            // check is some value of $costs_depends_from_selection_new consist % if its true so then apply this procents to days
            $is_rates_with_procents = false;
            for ($iii = 0; $iii < count($costs_depends_from_selection_new); $iii++) {
                if ( strpos($costs_depends_from_selection_new[$iii], 'add') !== false ) {
                    $my_vvalue = floatval(str_replace('add','',$costs_depends_from_selection_new[$iii] ) );
                    $rates_with_procents[]= $my_vvalue + $days_rates[$iii];
                        $is_rates_with_procents = true;                         //FixIn:6.2.3.7
                } elseif ( strpos($costs_depends_from_selection_new[$iii], '%') !== false ) {
                    $is_rates_with_procents = true;
                    $proc = str_replace('%','',$costs_depends_from_selection_new[$iii] ) * 1;
                    if (isset($days_rates[$iii]))
                            $rates_with_procents[]= $proc*$days_rates[$iii]/100;
                } else {
                    $rates_with_procents[]= floatval($costs_depends_from_selection_new[$iii]);// $days_rates[$iii]; // just cost
                }
            }
//debuge( (int) $is_rates_with_procents, $rates_with_procents,$costs_depends_from_selection_new);
            if ($is_rates_with_procents) return $rates_with_procents;               // Rates with procents from cost depends from number of days
            else                         return $costs_depends_from_selection_new;  // Cost depends from number of days
        } else                           return $days_rates;                        // Just pure rates

    }

            // Get count of MINUTES from time in format "17:20" or array(17, 20)
            function get_minutes_num_from_time($time_array){
                if (is_string($time_array)) {
                    $time_array = explode(':',$time_array);
                }
                if (is_array($time_array)) {
                    return  ($time_array[0]*60+ intval($time_array[1]));
                }
                return $time_array;
            }

            // Get COST based on hourly rate - $hour_cost and start and end time during 1 day
            /*  $times_array                        (its arrayin fomat
            //  (start_minutes, end minutes)                        or
            //  ("12:00", "17:30")                                  or
            //  (array("12","00","00"), array("22", "00", "00"))    /**/
            function get_cost_between_times($times_array, $hour_cost) {
                    $start_time = $times_array[0];      // Get Times
                    if (count($times_array)>1) $end_time   = $times_array[1];
                    else                       $end_time = array('24','00','00');

                    if ( $end_time == array('23','59','02') )
                        $end_time = array('24','00','00');                      //FixIn: 6.2.3.1

                    if (is_string($start_time)) { $start_time = explode(':', $start_time);$start_time[2] = '00'; }
                    if (is_string($end_time))   { $end_time   = explode(':', $end_time);  $end_time[2] = '00'; }

                    if ( (is_int($end_time)) && (is_int($start_time)) ) {   // 1000000 correction need to make.

                        if ($end_time > 1000000) { $ostatok = $end_time % 1000000;
                            if ($ostatok == 0) $end_time = $end_time  / 1000000;
                            else               $end_time = ( $end_time + ( 1000000 - $ostatok ) )  / 1000000;
                        }
                        if ($start_time > 1000000) { $ostatok = $start_time  % 1000000;
                            if ($ostatok == 0) $start_time = $start_time  / 1000000;
                            else               $start_time = ( $start_time + ( 1000000 - $ostatok ) )  / 1000000;
                        }
                        //return round(  ( ($end_time - $start_time) * ($hour_cost / 60 ) ) , 2 );
                        return  ( ($end_time - $start_time) * ($hour_cost / 60 ) );                             //FixIn: 7.0.1.44
                    }

                    if (empty($start_time[0]) ) $start_time[0] = '00';
                    if (empty($end_time[0]) ) $end_time[0] = '00';

                    if (! isset($start_time[1])) $start_time[1] = '00';
                    if (! isset($end_time[1])) $end_time[1] = '00';



                    if ( ($end_time[0] == '00') && ($end_time[1] == '00') ) $end_time[0] = '24';


                    $m_dif =  ($end_time[0] * 60 + intval($end_time[1]) ) - ($start_time[0] * 60 + intval($start_time[1]) ) ;
                    $h_dif = intval($m_dif / 60) ;
                    $m_dif = ($m_dif - ($h_dif*60) ) / 60 ;

                    //$summ = round( ( 1 * $h_dif * $hour_cost ) + ( 1 * $m_dif * $hour_cost ) , 2);
                    $summ =  ( 1 * $h_dif * $hour_cost ) + ( 1 * $m_dif * $hour_cost );                          //FixIn: 7.0.1.44

                    return $summ;
            }


    // Get 1 DAY cost OR cost from time to  time at  $times_array
    function get_1_day_cost_apply_rates( $type_id, $base_cost, $day , $month, $year, $times_array=false, $post_form = '' ) {


//debuge('Start', $type_id, $base_cost, $day , $month, $year, $times_array);

        $price_period =  get_bk_option( 'booking_paypal_price_period' );       // Get cost period and set multiplier for it.

        if ($price_period == 'day') {         $cost_multiplier = 1;
        } elseif ($price_period == 'night') { $cost_multiplier = 1;
        } elseif ($price_period == 'hour')  { $cost_multiplier = 24;        // Day have a 24 hours
        } else {                              $cost_multiplier = 1;   }     // fixed  // return $base_cost;

        $rate_meta_res = wpbc_get_resource_meta($type_id,'rates');         // Get all RATES for this bk resource

        if ( count($rate_meta_res)>0 ) {
            if ( is_serialized( $rate_meta_res[0]->value ) )  $rate_meta = unserialize($rate_meta_res[0]->value);
            else                                              $rate_meta = $rate_meta_res[0]->value;

            $rate              = $rate_meta['rate'];                        // Rate values                           (key -> ID)
            $seasonfilter      = $rate_meta['filter'];                      // If this filter assign to rate On/Off  (key -> ID)
//debuge($rate_meta);
            if (isset($rate_meta['rate_type']))   $rate_type = $rate_meta['rate_type'];       // is rate curency or %
            else                                  $rate_type = array();


            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Get    B A S E    C O S T   with   Rates  and get    H O U R L Y   R a t e s
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            $base_cost_with_rates = $base_cost;
            $hourly_rates = array();
            ////////////////////////////////////////////////////////////////
            // Get here Cost of the day with rates - $base_cost_with_rates, If curency rate is assing for this day so then just assign it and stop
            // also get all hour filters rates
            foreach ($seasonfilter as $filter_id => $is_filter_ON) {  // Id_filter => On  || Id_filter => Off
                if ($is_filter_ON == 'On') {                                       // Only activated filters
                    $is_day_inside_of_filter = wpbc_is_day_inside_of_filter($day , $month, $year, $filter_id);  // Check  if this day inside of filter

                    if ( $is_day_inside_of_filter === true ) {              // If return true then Only D A Y filters here
                        if ( isset($rate_type[$filter_id]) ) {                    // It Can be situation that in previos version is not set rate_type so need to check its
                            if ($rate_type[$filter_id] == '%') $base_cost_with_rates =  ( ($base_cost_with_rates * $rate[$filter_id] / 100) ) ; // %
                            else {                                          // Here is the place where we need in future create the priority of rates according direct curency value
                                   $base_cost_with_rates =  $rate[$filter_id]; break;} //here rate_type  == 'curency so we return direct value and break all other rates
                        } else $base_cost_with_rates =  ( ($base_cost_with_rates * $rate[$filter_id] / 100) ) ; // Default - %
                    }

                    if( is_array($is_day_inside_of_filter) ) {              // Its HOURLY filter, save them for future work
                      if ($is_day_inside_of_filter[0] == 'hour') { $hourly_rates[$filter_id]=array( 'rate'=>$rate[$filter_id], 'rate_type'=>$rate_type[$filter_id], 'time'=>array($is_day_inside_of_filter[1],$is_day_inside_of_filter[2]) ); }
                    }

                } // close ON if
            }  // close foreach

// Customization for the  Joao ///////////////////////////////////////////////////////////////////////
if ($post_form !='') {

$booking_form_show = get_form_content ($post_form, $type_id);
$booking_form_show = $booking_form_show['_all_'] ;;
//debuge($booking_form_show); die;
}
if (strpos($base_cost_with_rates, '=')) {



$base_cost_with_rates = str_replace('[', '', $base_cost_with_rates);  // [visitors=1:140;2:150]
$base_cost_with_rates = str_replace(']', '', $base_cost_with_rates);
$base_cost_with_rates = explode('=',$base_cost_with_rates);

$my_field_name = $base_cost_with_rates[0];                  // visitors

$my_temp_field_values = explode(';',$base_cost_with_rates[1]);
$my_field_values = array();
foreach ($my_temp_field_values as $m_value) {
    $m_value = explode(':',$m_value);
    $my_field_values[$m_value[0]] = $m_value[1];
}
/*[1] => Array
        (
            [1] => 140
            [2] => 150
        )*/
if ($post_form !='') {
    foreach ($booking_form_show as $bk_ft_key=>$bk_ft_value) {
        if ( $bk_ft_key == ($my_field_name . $type_id) ) {
            if ( isset(  $my_field_values[ $bk_ft_value  ]  ) ) {
                $base_cost_with_rates = $my_field_values[ $bk_ft_value  ] ;
                break;
            }
        }
    }
} else {
    $base_cost_with_rates = array_shift(array_values($my_field_values));
}
if (is_array($base_cost_with_rates)) {
     $base_cost_with_rates = array_shift(array_values($my_field_values));
}
}

//debuge($base_cost_with_rates);
// Customization for the  Joao ///////////////////////////////////////////////////////////////////////


//debuge($my_field_name, $my_field_values)                ;
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
//debuge(array( '$base_cost'=>$base_cost, '$base_cost_with_rates'=>$base_cost_with_rates,'$hourly_rates'=>$hourly_rates, $rate_type[$filter_id], $price_period));
//die;
            if ( ( count($hourly_rates) == 0 ) && ($price_period == 'fixed') ) {
                return $base_cost_with_rates;
            }

            // H O U R s ///////////////////////////////////////////////////
            $general_hours_arr = array();

            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Get   S T A R T  and   E N D   T i m e   for this day (or 0-24 or from function params $starttime)
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($times_array === false) {                                   // Time is not pass to the function
                $global_start_time = array('00','00','00');
                $global_finis_time = array('24','00','00');
            } else {                                                        // Time is set and we need calculate cost between it
                $global_start_time = $times_array[0];
                if (count($times_array)>1) $global_finis_time   = $times_array[1];
                else                       $global_finis_time = array('24','00','00');
                if (is_string($global_start_time))   { $global_start_time = explode(':', $global_start_time);$global_start_time[2] = '00'; }
                if (is_string($global_finis_time))   { $global_finis_time   = explode(':', $global_finis_time);  $global_finis_time[2] = '00'; }
                if ($global_finis_time == array('00','00','00')) $global_finis_time = array('24','00','00');
             }
             $general_hours_arr[ $this->get_minutes_num_from_time($global_start_time)*1000000 ] = array('start' , $base_cost_with_rates, '' );  // start glob work times array
             $general_hours_arr[ $this->get_minutes_num_from_time($global_finis_time)*1000000 ] = array('end'   , $base_cost_with_rates, '' );  // end glob work times array
             /////////////////////////////////////////////////////////////////////////////////////////////////////////


            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Get   all   H O U R L Y    R A T E S    in    S o r t e d    by  Minutes*100   array
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            foreach ($hourly_rates as $hour_filter_id => $hour_rate) {
               if (! isset($hour_rate['rate_type']) ) $hour_rate['rate_type'] ='%';

               $r__start = 1000000 * $this->get_minutes_num_from_time($hour_rate['time'][0]);
               $r__fin   = 1000000 * $this->get_minutes_num_from_time($hour_rate['time'][1]);
               while( isset($general_hours_arr[$r__start]) ) {$r__start--;}
               while( isset($general_hours_arr[$r__fin]) )   {$r__fin--;}

               $general_hours_arr[$r__start] = array('rate_start' , $hour_rate['rate'] , $hour_rate['rate_type'] );
               $general_hours_arr[$r__fin]   = array('rate_end'   , $hour_rate['rate'] , $hour_rate['rate_type'] );
            }
            ksort( $general_hours_arr );                                    // SORT time(rate) arrays with start/end time
            /////////////////////////////////////////////////////////////////////////////////////////////////////////

//debuge(array('$general_hours_arr'=>$general_hours_arr));

            if (    ($price_period == 'hour') ||                            // Get hour rates, already based on cost with applying rates for days not hours
                    ( ($price_period == 'fixed') && ( count($hourly_rates)>0 ) )
               )                                                               $base_hour_cost = $base_cost_with_rates ;
            else                                                               $base_hour_cost = $base_cost_with_rates / 24 ;

//debuge(array('$base_hour_cost'=>$base_hour_cost));

            $is_continue = false;                                           // Calculate cost for our times in array segments
            $general_summ_array = array();
            $cur_rate = $base_hour_cost;
            $cur_type = 'curency';
            foreach ($general_hours_arr as $minute_time => $rate_value) {

                if ($is_continue) {                                         // Calculation
                    if ($cur_type == 'curency') {
                        if ($price_period == 'fixed')  $general_summ_array[] = $cur_rate;
                        else                           $general_summ_array[] = $this->get_cost_between_times( array($previos_time[0] ,$minute_time), $cur_rate);
                    } else {
                        $procent_base =  $this->get_cost_between_times( array($previos_time[0] ,$minute_time), $base_hour_cost);
                        $general_summ_array[] =  ( ($procent_base * $cur_rate / 100) ) ; // %
                    }
                }

                if ( $rate_value[0] == 'start' ) { $is_continue = true; }   // start calculate from this time
                if ( $rate_value[0] == 'end'   ) { break; }                 // Finish calculation

                $previos_time = array($minute_time, $rate_value);           // Save previos time and rate

                if ( $rate_value[0] == 'rate_start' ) {                              // RATE start so get type and value of rate
                    $cur_type = $rate_value[2];
                    if ( ($price_period == 'hour') || ($price_period == 'fixed') )
                          $cur_rate = $rate_value[1];
                    else  {
                        if ($cur_type == 'curency') $cur_rate = $rate_value[1] / 24;
                        else $cur_rate = $rate_value[1];
                    }

                }
                if ( $rate_value[0] == 'rate_end'   ) {                              // Rate end so set standard  type and rate
                    $cur_rate = $base_hour_cost;
                    $cur_type = 'curency';
                }
            } // close foreach time cost array

 //debuge( array('$general_summ_array' =>  $general_summ_array )  );//die;

            if ( count($general_hours_arr) > 0 ) {                          // summ all costs into one variable - its 1 day cost ( or cost between times), with already aplly day rates filters
                   if ($price_period == 'fixed')  $return_cost = $general_summ_array[0];
                   else {
                        $return_cost = 0;
                        foreach ($general_summ_array as $vv) { $return_cost += $vv;  }
                   }
            } else                      $return_cost = $base_cost_with_rates;

            ////////////////////////////////////////////////////////////////
//debuge('$return_cost, $price_period, $hourly_rates', $return_cost, $price_period, $hourly_rates);
            return $return_cost;   // Evrything is calculated based on hours
            /*
            if( ($times_array !== false) && (count($hourly_rates)==0) ) {   //hourly rates do not exist BUT we set time from one time to end time
                if ($price_period == 'hour')        $hour_cost = $return_cost ;
                else {
                    if ($price_period == 'fixed')   return $return_cost;
                    elseif ($price_period == 'night')   return $return_cost; // alredy calculated, because time is exist //FIXED now
                    elseif ($price_period == 'day')   return $return_cost; // alredy calculated, because time is exist //FIXED now
                    else                            $hour_cost = $base_cost / 24 ;
                }
                    return $this->get_cost_between_times($times_array, $hour_cost);
            } else  return  $return_cost;    // Return day price after assigning of rates
            /**/

        } // Finish R A T E S  work


        // There    N o    R A T E S  at all
        if ($times_array === false)                 return  $cost_multiplier * $base_cost;      // No times, cost for 1 day
        else { // Also need to check according times hour
            if ($price_period == 'hour')            $hour_cost = $base_cost ;
            else {
                    if ($price_period == 'fixed')   return $base_cost;
                    else                            $hour_cost = $base_cost / 24 ;
            }
            return $this->get_cost_between_times($times_array, $hour_cost);                     // Cost for some time interval
        }

    }



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
	 * Reupdate static cost hints in booking form. Showing standard additional  costs,  if selected specific option in selectbox or checkbox.
     *
     * @param string $form - booking form
     * @param int $bktype - Id of booking resource
     * @return string - content of booking form.
     */
    function reupdate_static_cost_hints_in_form( $form , $bktype ) {     //FixIn: 5.4.5.5

        $booking_form_name='';
        if (isset($_POST['booking_form_type']) ){
            if (! empty($_POST['booking_form_type'])) {
                $booking_form_name = $_POST['booking_form_type'];
                $booking_form_name = str_replace("\'",'',$booking_form_name);
                if ($booking_form_name == 'standard') $booking_form_name = '';
            }
        }

        if ($booking_form_name === '')
            $field__values = get_bk_option( 'booking_advanced_costs_values' );
        else
            $field__values = get_bk_option( 'booking_advanced_costs_values_for' . $booking_form_name );

        $field__values_unserilize = array();
        if ( ! empty( $field__values ) ) {																				//FixIn: 8.0.2.3
            if ( is_serialized( $field__values ) )
                $field__values_unserilize = unserialize($field__values);
            else
                $field__values_unserilize = $field__values;
        }



        foreach ( $field__values_unserilize as $key => $value ) {

            $pattern = '\[(' . $key . ')_hint_static' . '([^\]]*)' . '\]';

            preg_match_all("/$pattern/", $form, $matches, PREG_SET_ORDER);

            /*
            [0] => Array
                (
                    [0] => [surflessons_hint_static "1"]
                    [1] => surflessons
                    [2] =>  "1"
                )

            [1] => Array
                (
                    [0] => [surflessons_hint_static "2"]
                    [1] => surflessons
                    [2] =>  "2"
                )
             */
            if ( count($matches) > 0 ) {

                foreach ( $matches as $static_hint ) {

                    $cost_for_insert = '';
                    if ( isset($field__values_unserilize[ $static_hint[1] ] ) ) {

//debuge($field__values_unserilize[ $static_hint[1] ], $static_hint);

                        if ( isset( $field__values_unserilize[ $static_hint[1] ][ 'checkbox' ] ) )             // Check additional cost in  standard checkbox, like this [checkbox some_name ""]
                            $cost_for_insert = $field__values_unserilize[ $static_hint[1] ][ 'checkbox' ];


                        if ( isset( $field__values_unserilize[ $static_hint[1] ][ $static_hint[2] ] ) )
                            $cost_for_insert = $field__values_unserilize[ $static_hint[1] ][ $static_hint[2] ];

                        $static_hint[2] = str_replace( "'", '', $static_hint[2] );
                        $static_hint[2] = str_replace( '"', '', $static_hint[2] );
                        $static_hint[2] = trim($static_hint[2]);
                        if ( isset( $field__values_unserilize[ $static_hint[1] ][ $static_hint[2] ] ) )
                            $cost_for_insert = $field__values_unserilize[ $static_hint[1] ][ $static_hint[2] ];
//debuge($static_hint, $cost_for_insert);

                        if ( strpos($cost_for_insert, '%') === false ) {
                            $cost_currency = wpbc_get_currency_symbol_for_user( $bktype );
                            $cost_for_insert = $cost_currency . ' ' . $cost_for_insert;
                        } else {
                            //Here we are have percents, then set  it to the empty
                            $cost_for_insert = '';
                        }
                        // Replace staic cost  hint element to  the cost  value
                        $form = str_replace( $static_hint[0], $cost_for_insert, $form );
                    }

                }

                //debuge($matches);
            }
        }

        //debuge($field__values_unserilize);

        return $form;
    }



	/**
	 * Apply "early booking discount" or "Last minute booking discount" to  the booking.
	 *
	 * @param $cost				=> 125
	 * @param $form				=> text^selected_short_timedates_hint4^06/09/2018 14:00 - 06/11/2018 12:00~text^nights_number_hint4^2~text^cost_hint4^$125.00~text^name4^~email^email4^~select-one^visitors4^1~select-one^children4^0~text^starttime4^14:00~text^endtime4^12:00
	 * @param $resource_id		=> 4
	 * @param $booking_days_arr	=> Array ( [0] => 09.06.2018 [1] => 10.06.2018 [2] => 11.06.2018 )
	 *
	 * @return mixed
	 */
    function early_late_booking_apply( $cost , $form , $resource_id , $booking_days_arr ){								//FixIn: 8.2.1.17

		$el_data = false;

		// Get Early / Late booking discount data for Resource
        $meta_data = wpbc_get_resource_meta( $resource_id, 'costs_early_late_booking' );
        if ( count( $meta_data ) > 0 ) {
	        $el_data = maybe_unserialize( $meta_data[0]->value );
        }


        if ( ! empty( $el_data ) ) {
//debuge($el_data);
	        /**
	         *  $el_data::

			    [early_booking_active] 				=> Off		|   	On
				[early_booking_amount] 				=> 100		|   	75
				[early_booking_type] 				=> %		|   	fixed
				[early_booking_days_condition] 		=> 0		|   	180
				[early_booking_season_filter] 		=> 0		|   	1

				[last_min_booking_active] 			=> Off		|   	On
				[last_min_booking_amount] 			=> 100		|   	25
				[last_min_booking_type] 			=> %		|   	%
				[last_min_booking_days_condition] 	=> 0		|   	7
				[last_min_booking_season_filter] 	=> 0		|   	3
			 */

			$booking_days_string = implode( ',', $booking_days_arr );

	        // Get sorted days
	        $sorted_dates = wpbc_get_sorted_days_array( $booking_days_string );

	        if ( ! empty( $sorted_dates ) ) {

	        	////////////////////////////////////////////////////////////////////////////////////////////////////////
	        	// E A R L Y  BOOKING
				////////////////////////////////////////////////////////////////////////////////////////////////////////
		        if ( $el_data['early_booking_active'] == 'On' ) {

			        $apply_after_days = intval( $el_data['early_booking_days_condition'] );

					$dates_diff = wpbc_get_difference_in_days( '+' . $apply_after_days . ' days', $sorted_dates[0] ); 	//debuge( '$dates_diff', $dates_diff, '+' . $apply_after_days . ' days', $sorted_dates[0] );

					// Check in  MORE  than XX days from  Today
					if ( $dates_diff <= 0 ){

						// Its inside season filter, or NO season filter to  apply in settings, (value = 0)
						if ( $this->is_check_in_day_in_season_filter( $el_data['early_booking_season_filter'], $booking_days_string ) ) {

							// Apply discount here
							$discount_val = intval( $el_data['early_booking_amount'] );

							if ( $el_data['early_booking_type'] == '%') {			// %
								$cost = $cost - $cost * $discount_val / 100 ;
							} else {												// fixed
								$cost = $cost - $discount_val;
							}

							if ( $cost < 0 ) { $cost = 0; }		// Check  negative
						}
					}
		        }

		        ////////////////////////////////////////////////////////////////////////////////////////////////////////
		        // LAST  MINUTE BOOKING
				////////////////////////////////////////////////////////////////////////////////////////////////////////
		        if ( $el_data['last_min_booking_active'] == 'On' ) {

			        $apply_after_days = intval( $el_data['last_min_booking_days_condition'] );

					$dates_diff = wpbc_get_difference_in_days( '+' . $apply_after_days . ' days', $sorted_dates[0] ); 	//debuge( '$dates_diff', $dates_diff, '+' . $apply_after_days . ' days', $sorted_dates[0] );

					// Check in  MORE  than XX days from  Today
					if ( $dates_diff > 0 ){

						// Its inside season filter, or NO season filter to  apply in settings, (value = 0)
						if ( $this->is_check_in_day_in_season_filter( $el_data['last_min_booking_season_filter'], $booking_days_string ) ) {

							// Apply discount here
							$discount_val = intval( $el_data['last_min_booking_amount'] );

							if ( $el_data['last_min_booking_type'] == '%') {		// %
								$cost = $cost - $cost * $discount_val / 100 ;
							} else {												// fixed
								$cost = $cost - $discount_val;
							}

							if ( $cost < 0 ) { $cost = 0; }		// Check  negative
						}
					}
		        }

	        }
        }

    	return $cost;
    }


    // Apply advanced cost to the cost from paypal form
    function advanced_cost_apply( $summ , $form , $bktype , $days_array , $is_get_description = false ){

// Reset Custom Booking Forms to  NONE
// update_bk_option( 'booking_forms_extended' , serialize( array() ) );

//debuge($summ , $form , $bktype , $days_array , $is_get_description, 'advanced_cost_apply' );

        $booking_form_name='';
        if (isset($_POST['booking_form_type']) ){
            if (! empty($_POST['booking_form_type'])) {
                $booking_form_name = $_POST['booking_form_type'];
                $booking_form_name = str_replace("\'",'',$booking_form_name);
                if (   ( $booking_form_name == 'standard' )
                    || ( get_bk_option( 'booking_advanced_costs_values_for' . $booking_form_name ) === false )          // Form does not exist
                 ) $booking_form_name = '';
            }
        }

//debuge($form);
        $additional_cost = 0;                                               // advanced cost, which will apply
        $booking_form_show = get_form_content ($form, $bktype);

//debuge($booking_form_show);
        if ($booking_form_name === '') { $field__values = get_bk_option( 'booking_advanced_costs_values' ); }     // Get saved advanced cost structure for STANDARD form
        else { $field__values = get_bk_option( 'booking_advanced_costs_values_for' . $booking_form_name ); }
//debuge(gettype($field__values), $booking_form_name);
        $full_procents = 1;
        $advanced_cost_hint = array();
        if ( $field__values !== false ) {                                   // Its exist
            if ( is_serialized( $field__values ) )   $field__values_unserilize = unserialize($field__values);
            else                                               $field__values_unserilize = $field__values;
        	$booking_form_show['content'] ='';



            if (! empty($field__values_unserilize)) {                       // Checking
                if (is_array($field__values_unserilize)) {
                    foreach ($field__values_unserilize as $key_name => $value) {    // repeat in format "visitors"  =>  array ("1"=>25, "2"=>"200%")
                        $key_name= trim($key_name);                         // Get trim visitors name (or some other)

                        $advanced_cost_hint[$key_name] = array( 'value' => $value , 'fixed' => array(), 'percent' => array() );	// FixIn: 8.1.3.17.1
//debuge($key_name, $value);
//debuge($booking_form_show);
                        if (isset( $booking_form_show['_all_fields_'][$key_name] )) {       // Get value sending from booking form like this $booking_form_show["visitors"]
                            $selected_value = $booking_form_show['_all_fields_'][$key_name];


                            if ( is_array($selected_value) )  $selected_value_array = $selected_value;
                            else {
                                if ( strpos($selected_value,',')===false )
                                     $selected_value_array = array($selected_value);
                                else $selected_value_array = explode(',',$selected_value);
                            }

//debuge($value, $selected_value_array);
                            foreach ($selected_value_array as $selected_value ) {
								$selected_value = trim($selected_value);
								$selected_value = str_replace(' ','_',$selected_value);
								if (
										($selected_value == '') ||
										($selected_value == 'yes') ||
										($selected_value ==  __('yes' ,'booking') )
									) $selected_value = 'checkbox';

								if ( isset($value[$selected_value]) ) {         // check how its value for selected value in cash or procent

//debuge('$selected_value',$value[$selected_value]);
									$additional_single_cost = $value[$selected_value];
									$additional_single_cost = str_replace(',','.',$additional_single_cost);
									$full_additional_single_cost = 0;

									// FixIn: 8.1.3.17
									if(  ( substr( $additional_single_cost , -1 ) == '%' ) && ( substr( $additional_single_cost , 0, 1 ) == '+' )  ){
										$additional_single_cost = substr($additional_single_cost, 0, -1);
										$additional_single_cost = substr($additional_single_cost, 1 );
										// Calc
										$additional_single_cost = $this->wpbc_replace_shortcodes_to_values( $additional_single_cost, $booking_form_show['_all_fields_'] );
										$full_additional_single_cost              = floatval( $summ * ( $additional_single_cost / 100 ) );

										$advanced_cost_hint[ $key_name ]['fixed'][] = $full_additional_single_cost;		// FixIn: 8.1.3.17.1
										$additional_cost                          += $full_additional_single_cost;
									}
									else if ( substr( $additional_single_cost , -1 ) == '%' ) {
										$additional_single_cost = substr($additional_single_cost,0,-1);
										// Calc
										$additional_single_cost = $this->wpbc_replace_shortcodes_to_values( $additional_single_cost, $booking_form_show['_all_fields_'] );
										$advanced_cost_hint[ $key_name ]['percent'][] = ( ( $additional_single_cost * 1 / 100 ) );				// FixIn: 8.1.3.17.1
										$full_procents                              = ( ( $additional_single_cost * $full_procents / 100 ) );
									}
									else if ( substr( $additional_single_cost , -4 ) == '/day' ) {
										$additional_single_cost = str_replace( '/day', '', $additional_single_cost );
										//Calc
										$additional_single_cost = $this->wpbc_replace_shortcodes_to_values( $additional_single_cost, $booking_form_show['_all_fields_'] );
										$full_additional_single_cost              = floatval( $additional_single_cost ) * count( $days_array );
										$advanced_cost_hint[ $key_name ]['fixed'][] = $full_additional_single_cost;		// FixIn: 8.1.3.17.1
										$additional_cost                          += $full_additional_single_cost;
									}
									else if ( substr( $additional_single_cost , -6 ) == '/night' ) {
										$additional_single_cost = str_replace( '/night', '', $additional_single_cost );
										//Calc
										$additional_single_cost = $this->wpbc_replace_shortcodes_to_values( $additional_single_cost, $booking_form_show['_all_fields_'] );
										$nights_count           = ( count( $days_array ) - 1 );
										if ( $nights_count == 0 ) {
											$nights_count = 1;
										}
										$full_additional_single_cost              = floatval( $additional_single_cost ) * $nights_count;
										$advanced_cost_hint[ $key_name ]['fixed'][] = $full_additional_single_cost;		// FixIn: 8.1.3.17.1
										$additional_cost                          += $full_additional_single_cost;
									} else {                                                                      // cashe
										$additional_single_cost = $this->wpbc_replace_shortcodes_to_values( $additional_single_cost, $booking_form_show['_all_fields_'] );
										$full_additional_single_cost              =  $additional_single_cost;
										$advanced_cost_hint[ $key_name ]['fixed'][] = $full_additional_single_cost;		// FixIn: 8.1.3.17.1
										$additional_cost                         += $full_additional_single_cost;
									}

								}
                            }

                        }

                    }
                }
            }
        }


        if ( $is_get_description ) {
//debuge('Before', $advanced_cost_hint);
            foreach ( $advanced_cost_hint as $key_name => $array_values ) {

                if (! isset($advanced_cost_hint[$key_name]['cost_hint']))
                    $advanced_cost_hint[$key_name]['cost_hint'] = '';

                if ( ! empty( $array_values['percent'] ) ) {

                    if ( get_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents' ) == 'On' ) {
                        $tot_sum = ( ($summ + $additional_cost) * $full_procents );
                        $advanced_cost_hint[$key_name]['cost_hint'] =  $tot_sum - $tot_sum / ( array_sum( $advanced_cost_hint[$key_name]['percent'] ) );		// FixIn: 8.1.3.17.1
                    } else {
//debuge($summ , $full_procents )                        ;
                        $tot_sum = ( ($summ ) * $full_procents );

                        $advanced_cost_hint[$key_name]['cost_hint'] = $tot_sum - $tot_sum / ( array_sum( $advanced_cost_hint[$key_name]['percent'] ) );			// FixIn: 8.1.3.17.1
//debuge($tot_sum,$advanced_cost_hint[$key_name]['percent'], $advanced_cost_hint[$key_name]['cost_hint']);
                    }
                } else if ( ! empty($array_values['fixed'])) {
                    $advanced_cost_hint[$key_name]['cost_hint'] = array_sum( $advanced_cost_hint[$key_name]['fixed'] );	// FixIn: 8.1.3.17.1
                }
            }

			$show_advanced_cost_hints = array();
			foreach ( $advanced_cost_hint as $key => $value ) {
				$show_advanced_cost_hints[$key . '_hint'] = $value['cost_hint'];
			}

//debuge($advanced_cost_hint);
			return $show_advanced_cost_hints;
        }

        if ( get_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents' ) == 'On' ) {
            return ( $summ + $additional_cost ) * $full_procents;
        } else {
            return $summ * $full_procents + $additional_cost ;
        }
    }


    // FixIn: 8.1.3.17
    function wpbc_replace_shortcodes_to_values( $additional_single_cost, $booking_form_field_values ){
//debuge($additional_single_cost, $booking_form_field_values);

		// Replace form fields to  values,  if exist  some shortcodes.

		if ( strpos( $additional_single_cost, '[') !== false ) {

			foreach ( $booking_form_field_values as $field_key => $field_val ) {

				if ( strtolower( $field_val ) == 'yes' ) {
					$field_val = 1;
				}
				if ( strtolower( $field_val ) == 'no' ) {
					$field_val = 0;
				}
				$additional_single_cost = str_replace( '['.  $field_key .']' , $field_val, $additional_single_cost );
			}
		}

		$how_many = preg_match_all( '/[\+\-\*\/\(\)]/',  $additional_single_cost, $matches );
//debuge( '$additional_single_cost, $how_many, $matches', $additional_single_cost, $how_many, $matches );
		if ( ! empty($how_many ) ) {
			$additional_single_cost = wpbc_str_calc( $additional_single_cost );
		}

    	return floatval( $additional_single_cost );
    }


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Apply fixed deposit cost to the cost from paypal form
    function fixed_deposit_amount_apply($summ , $post_form, $booking_type , $booking_days = false ) {

        $original_summ = $summ;                                             // Original cost for booking
        // $is_resource_deposit_payment_active   = get_bk_option( 'booking_is_resource_deposit_payment_active');
        // if ($is_resource_deposit_payment_active == 'On') {

                $fixed_deposit = wpbc_get_resource_meta( $booking_type ,'fixed_deposit');

                if ( count($fixed_deposit) > 0 ) {
                    if ( is_serialized( $fixed_deposit[0]->value ) ) $fixed_deposit = unserialize($fixed_deposit[0]->value);
                    else                                             $fixed_deposit = $fixed_deposit[0]->value;
                }
                else $fixed_deposit = array('amount'=>'100',
                                            'type'=>'%',
                                            'active' => 'Off',
                                            'apply_after_days' => '0',
                                            'season_filter' => '0'
                                           );

                $resource_deposit_amount            = $fixed_deposit['amount'];
                $resource_deposit_amount_apply_to   = $fixed_deposit['type'];
                $resource_deposit_is_active         = $fixed_deposit['active'];

                if (isset($fixed_deposit['apply_after_days']))
                     $resource_deposit_apply_after_days  = $fixed_deposit['apply_after_days'];
                else $resource_deposit_apply_after_days  = '0';

                if (isset($fixed_deposit['season_filter']))
                     $resource_deposit_season_filter  = $fixed_deposit['season_filter'];
                else $resource_deposit_season_filter  = '0';



                // Check if the difference between TODAY and Check In date is valid for the Apply of deposit.
                if ($booking_days !== false) {
                    $sortedDates = wpbc_get_sorted_days_array($booking_days);
                    if ( ! empty($sortedDates) ) {
                        $dates_diff  = wpbc_get_difference_in_days('+' . $resource_deposit_apply_after_days . ' days', $sortedDates[0]);

                        if ($dates_diff > 0)
                            return $summ;
                    }
                }

                if ( ! $this->is_check_in_day_in_season_filter( $resource_deposit_season_filter, $booking_days ) )
                    return $summ;

                if ($resource_deposit_is_active == 'On') {

                    if ($resource_deposit_amount_apply_to == '%') $summ = $summ * $resource_deposit_amount / 100 ;
                    else $summ = $resource_deposit_amount;
                }
        // }
        return ($summ );
    }


        function is_check_in_day_in_season_filter( $season_filter_id, $days_string ) {

            if ( $season_filter_id == '0' )                                     // All days - not the season  filter
                return true;

            $sortedDates = wpbc_get_sorted_days_array( $days_string );                     // Get Check in day from string: 06.04.2015, 05.04.2015, 07.04.2015, 08.04.2015, 26.03.2015, 09.04.2015, 27.03.2015

            if ( ! empty( $sortedDates ) ) {
                $check_in_date = $sortedDates[0];
                $check_in_date = explode(' ', $check_in_date);
                $check_in_date = $check_in_date[0];
                $check_in_date = explode('-', $check_in_date);
                $check_in = array();
                $check_in['year']  = intval( $check_in_date[0] );
                $check_in['month'] = intval( $check_in_date[1] );
                $check_in['day']   = intval( $check_in_date[2] );
            } else
                return false;



            $is_day_inside_of_filter = wpbc_is_day_inside_of_filter(          // Check  if this day inside of filter
                                                                        $check_in['day'],
                                                                        $check_in['month'],
                                                                        $check_in['year'],
                                                                        $season_filter_id
                                                                      );
//debuge($check_in_date, $is_day_inside_of_filter , 'tra ta ta');
            if ( $is_day_inside_of_filter )
                return true;
            else
                return  false;
        }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




 //  R E S O U R C E     T A B L E     C O S T    C o l l  u m n    ////////////////////////////////////////////////////////////////////////////


       function wpbc_get_default_custom_form($blank, $booking_resource_id) {
            global $wpdb;
            $types_list = $wpdb->get_results( $wpdb->prepare( "SELECT default_form FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = %d " , $booking_resource_id ) );
            if ($types_list)
                return $types_list[0]->default_form;
            else
                return $blank;

       }

}
