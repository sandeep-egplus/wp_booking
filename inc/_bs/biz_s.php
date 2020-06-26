<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly
require_once(WPBC_PLUGIN_DIR. '/inc/_bs/lib_s.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bs/wpbc-s-costs.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bs/s-toolbar.php' );

require_once(WPBC_PLUGIN_DIR. '/inc/_bs/admin/api-settings-s.php' );            // Settings page
require_once(WPBC_PLUGIN_DIR. '/inc/_bs/admin/activation-s.php' );              // Activate / Deactivate

require_once( WPBC_PLUGIN_DIR . '/inc/_bs/admin/page-email-payment.php' );      // Email - Payment Request

require_once( WPBC_PLUGIN_DIR . '/inc/gateways/wpbc-class-gw-api.php' );        //Payment Gateways API - Abstract Class
require_once( WPBC_PLUGIN_DIR . '/inc/gateways/page-gateways.php' );            //Payment Gateways - General Settings Page
    

//    require_once( WPBC_PLUGIN_DIR . '/inc/_bs/admin/page-payment-paypal.php' );    //TODO: Finish this !
//    require_once( WPBC_PLUGIN_DIR . '/inc/_bs/admin/page-payment-sage.php' );      //TODO: Finish this !

if (file_exists(WPBC_PLUGIN_DIR. '/inc/_bm/biz_m.php')) { require_once(WPBC_PLUGIN_DIR. '/inc/_bm/biz_m.php' ); }


class wpdev_bk_biz_s {

    var $wpdev_bk_biz_m;

    // Constructor
    function __construct() {
         
        add_filter('wpdev_booking_form', array(&$this, 'add_paypal_form'));     // Add DIV structure, where to show payment form
                
        //if ( WP_BK_PAYMENT_FORM_ONLY_IN_REQUEST !== true )															//FixIn: 8.1.3.23
		add_action('wpdev_new_booking', array(&$this, 'show_paypal_form_in_ajax_request'),1,5); // Make showing Paypal in Ajax
        
        add_action('wpbc_update_cost_of_new_booking', array(&$this, 'wpbc_update_cost_of_new_booking'),1,5); // Make showing Paypal in Ajax

        add_bk_filter('get_bk_currency_format', array(&$this, 'get_bk_currency_format'));

        add_action('wpbc_define_js_vars', array(&$this, 'wpbc_define_js_vars') );
        add_action('wpbc_enqueue_js_files', array(&$this, 'wpbc_enqueue_js_files') );
        add_action('wpbc_enqueue_css_files',array(&$this, 'wpbc_enqueue_css_files') );

        add_filter('wpdev_booking_form_content', array(&$this, 'wpdev_booking_form_content'),10,2 );

        add_filter('wpdev_get_booking_cost', array(&$this, 'get_booking_cost'),10,4 );
        add_bk_filter('wpdev_get_bk_booking_cost', array(&$this, 'get_booking_cost'));

        add_bk_action('wpdev_booking_post_inserted', array(&$this, 'booking_post_inserted'));
        add_bk_filter('get_booking_cost_from_db', array(&$this, 'get_booking_cost_from_db'));
        add_bk_filter('wpdev_get_payment_form', array(&$this, 'get_payment_form') );       

        add_bk_action('wpdev_save_bk_cost', array(&$this, 'wpdev_save_bk_cost'));          // Ajax POST request for updating cost
        add_bk_action('wpdev_send_payment_request', array(&$this, 'wpdev_send_payment_request'));          // Ajax POST request for email sending payment request
        add_bk_action('wpdev_change_payment_status', array(&$this, 'wpdev_change_payment_status'));          // Ajax POST request for email sending payment request

        add_bk_action('check_pending_not_paid_auto_cancell_bookings', array(&$this, 'check_pending_not_paid_auto_cancell_bookings'));          //Check and delete all Pending not paid bookings, which older then a 1-n days

	    add_filter( 'wpbc_booking_cost_custom_round', array( $this, 'wpbc_booking_cost_custom_round_apply' ), 10, 1 );

         if ( class_exists('wpdev_bk_biz_m')) {
                $this->wpdev_bk_biz_m = new wpdev_bk_biz_m();
        } else { $this->wpdev_bk_biz_m = false; } 

    }


 //   S U P P O R T     F U N C T I O N S    //////////////////////////////////////////////////////////////////////////////////////////////////


    // Get booking types from DB
    function get_booking_type($booking_id) {
        global $wpdb;
        $types_list = $wpdb->get_results($wpdb->prepare( "SELECT title, cost FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = %d" , $booking_id ));
        return $types_list;
    }

    // Get cost of booking resource
    function get_cost_of_booking_resource($bk_type_id) {
        global $wpdb;
        $cost = $wpdb->get_var($wpdb->prepare( "SELECT cost FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = %d" , $bk_type_id ));
        return (isset( $cost) ) ? $cost : 0 ;
    }

    // Get booking types from DB
    function get_booking_types() {
        global $wpdb;

        if ( class_exists('wpdev_bk_biz_l')) {  // If Business Large then get resources from that
            $types_list = apply_bk_filter('get_booking_types_hierarhy_linear',array() );
            for ($i = 0; $i < count($types_list); $i++) {
                $types_list[$i]['obj']->count = $types_list[$i]['count'];
                $types_list[$i] = $types_list[$i]['obj'];
                //if ( ($booking_type_id != 0) && ($booking_type_id == $types_list[$i]->booking_type_id ) ) return $types_list[$i];
            }
            //if ($booking_type_id == 0)

        } else $types_list = $wpdb->get_results( "SELECT booking_type_id as id, title, cost FROM {$wpdb->prefix}bookingtypes  ORDER BY title" );

        $types_list = apply_bk_filter('multiuser_resource_list', $types_list);
        return $types_list;
    }

    




 //  C O S T    I n s e r t i n g    ///////////////////////////////////////////////////////////////////////////////////////

    //  Update C O S T    ---  Function call after booking is inserted or modificated in post request
    function booking_post_inserted($booking_id, $booking_type, $booking_days_count, $times_array, $post_form = false){
           global $wpdb;

           if ($post_form === false) {
               $post_form = escape_any_xss($_POST["form"]);
           }
           // Check if total cost field exist and get cost from that field
           $fin_summ = apply_bk_filter('check_if_cost_exist_in_field', false, $post_form, $booking_type );

           if ($fin_summ == false)
                $summ = $this->get_booking_cost( $booking_type, $booking_days_count, $times_array , $post_form );
           else $summ = $fin_summ;

           $summ = str_replace(' ', '', $summ);
           $summ = floatval(  $summ);
           //$summ = round($summ,2);//FixIn: 8.1.3.33
			$summ = number_format( $summ, wpbc_get_cost_decimals(), '.', '' );                  //FixIn: 8.3.2.1               //FixIn: 8.2.1.24

            $update_sql =  $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.cost = %f WHERE bk.booking_id = %d ", $summ, $booking_id );
            if ( false === $wpdb->query( $update_sql  ) ){
                ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during updating cost in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                die();
            }/**/

    }


    //  Update C O S T
    function update_booking_cost($booking_id, $cost){
           global $wpdb;

           $summ = floatval($cost);
           //$summ = round($summ,2);//FixIn: 8.1.3.33

            $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.cost=%f WHERE bk.booking_id= %d ", $summ, $booking_id );
            if ( false === $wpdb->query( $update_sql ) ){
                ?> <script type="text/javascript"> document.getElementById('submiting<?php echo '1';//$bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during updating cost in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                die();
            }/**/

    }



    // Get Cost from DB
    function get_booking_resorce_cost($resource_id) {
        global $wpdb;
        $slct_sql = $wpdb->prepare( "SELECT cost FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id = %d", $resource_id );
        $slct_sql_results  = $wpdb->get_results( $slct_sql );
        if ( count($slct_sql_results) > 0 ) { 
            return $slct_sql_results[0]->cost;                 
        } else {
            return '';
        }
    }


    // Get Cost from DB
    function get_booking_cost_from_db($booking_cost, $booking_id) {
        global $wpdb;
        $slct_sql = $wpdb->prepare( "SELECT cost FROM {$wpdb->prefix}booking WHERE booking_id = %d LIMIT 0,1", $booking_id );
        $slct_sql_results  = $wpdb->get_results( $slct_sql );
        if ( count($slct_sql_results) > 0 ) { return $slct_sql_results[0]->cost; }
        return '';
    }

    // Check and delete all Pending not paid bookings, which older then a 1-n days
    function check_pending_not_paid_auto_cancell_bookings($bk_type) { 

            if ( defined('WP_ADMIN') ) if ( WP_ADMIN === true )  return;
            $is_check_active   =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' );   // Is this function Active
            if ($is_check_active != 'On') return;

            global $wpdb;
            $num_of_hours_ago  =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' );        // Num of hours ago for specific booking
    
            if ( strpos( $num_of_hours_ago, ':' ) === false ) {                 //FixIn: 7.0.1.25
                $num_of_min_ago = '0';
            } else {
                $num_of_hours_ago = explode(':', $num_of_hours_ago);
                $num_of_min_ago   = intval( $num_of_hours_ago[1] );
                $num_of_hours_ago = intval( $num_of_hours_ago[0] );
            }
            
            // TODO: add here in a future possibility to cancel not ALL, but specific bookings with booking payment type: Error or Failed
            // Right now all bookings, which  have no successfully payed status or pending are canceled.
            $labels_payment_status_ok = wpbc_get_payment_status_ok();
            $labels_payment_status_ok = implode( "', '" , $labels_payment_status_ok);           
            $labels_payment_status_ok = "'" . $labels_payment_status_ok;

            $labels_payment_status_pending = wpbc_get_payment_status_pending();
            $labels_payment_status_pending = implode( "', '", $labels_payment_status_pending);
            $labels_payment_status_ok .= "', '" . $labels_payment_status_pending . "'";

            $trash_bookings = ' AND bk.trash != 1 ';                                //FixIn: 6.1.1.10  - check also  below usage of {$trash_bookings}
            
            // Cancell only Pending, Old (hours) and not Paid bookings
            $slct_sql = $wpdb->prepare("SELECT DISTINCT bk.booking_id as id, bk.modification_date as date,  dt.approved AS approved, bk.pay_status AS pay_status
                         FROM {$wpdb->prefix}booking AS bk

                         INNER JOIN {$wpdb->prefix}bookingdates as dt
                         ON    bk.booking_id = dt.booking_id

                          WHERE bk.pay_status NOT IN ( {$labels_payment_status_ok} ) {$trash_bookings} AND 
                                dt.approved=0 AND
                                bk.modification_date < ( NOW() - INTERVAL '%d:%d' HOUR_MINUTE ) ", $num_of_hours_ago, $num_of_min_ago );          // //FixIn: 7.0.1.25
                          
                        // old: bk.modification_date < ( NOW() - INTERVAL %d HOUR ) ", $num_of_hours_ago );
            $pending_not_paid  = $wpdb->get_results( $slct_sql );
            $approved_id = array();
            foreach ($pending_not_paid as $value) {
               $approved_id []= $value->id;
            }
            $approved_id_str = join( ',', $approved_id);

            if ( count($approved_id)>0 ) {

                // Send decline emails
                $auto_cancel_pending_unpaid_bk_is_send_email =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' );
                if ($auto_cancel_pending_unpaid_bk_is_send_email == 'On') {
                    $auto_cancel_pending_unpaid_bk_email_reason  =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason' );
                    foreach ($approved_id as $booking_id) {
                        // wpbc_send_email_deny($booking_id,1, $auto_cancel_pending_unpaid_bk_email_reason );
                        wpbc_send_email_trash( $booking_id, 1, $auto_cancel_pending_unpaid_bk_email_reason  );
                    }
                }

                
                
                if ( false === $wpdb->query( "UPDATE {$wpdb->prefix}booking AS bk SET bk.trash = 1 WHERE booking_id IN ({$approved_id_str})" ) ){ 
                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bk_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo 'Error during auto deleting booking at DB of pending bookings'; ?></div>'; </script> <?php
                    die();
                }
                
//                // Auto cancellation
//                if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}bookingdates WHERE booking_id IN ({$approved_id_str})" ) ){
//                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bk_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo 'Error during auto deleting dates at DB of pending bookings'; ?></div>'; </script> <?php
//                    die();
//                }
//                if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}booking WHERE booking_id IN ({$approved_id_str})" ) ){
//                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bk_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo 'Error auto deleting booking at DB of pending bookings' ; ?></div>'; </script> <?php
//                    die();
//                }
            }

    }


 //  R E S O U R C E     T A B L E     C O S T    C o l l  u m n    ////////////////////////////////////////////////////////////////////////////






 //   C L I E N T     S I D E    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define JavaScripts Variables               //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_define_js_vars( $where_to_load = 'both' ){ 
        
        $specific_selected_dates = get_bk_option( 'booking_range_selection_days_specific_num_dynamic');
        $js_specific_selected_dates = rangeNumListToCommaNumList( $specific_selected_dates );
        $booking_highlight_timeslot_word = get_bk_option( 'booking_highlight_timeslot_word');
        $booking_highlight_timeslot_word =  apply_bk_filter('wpdev_check_for_active_language', $booking_highlight_timeslot_word );
        if (empty($booking_highlight_timeslot_word)) $booking_highlight_timeslot_word = '';
        
        wp_localize_script('wpbc-global-vars', 'wpbc_global3', array(              
             'bk_1click_mode_days_num' => intval( get_bk_option('booking_range_selection_days_count') )            /* Number of days selection with 1 mouse click */
             ,'bk_1click_mode_days_start' => '['. get_bk_option('booking_range_start_day') .']'                     /* { -1 - Any | 0 - Su,  1 - Mo,  2 - Tu, 3 - We, 4 - Th, 5 - Fr, 6 - Sat } */
             ,'bk_2clicks_mode_days_min' => intval( get_bk_option('booking_range_selection_days_count_dynamic') )   /* Min. Number of days selection with 2 mouse clicks */
             ,'bk_2clicks_mode_days_max' => intval( get_bk_option('booking_range_selection_days_max_count_dynamic'))/* Max. Number of days selection with 2 mouse clicks */
             ,'bk_2clicks_mode_days_specific' => '['. $js_specific_selected_dates . ']'                             /* Exmaple [5,7] */
             ,'bk_2clicks_mode_days_start' => '[' . get_bk_option('booking_range_start_day_dynamic') . ']'          /* { -1 - Any | 0 - Su,  1 - Mo,  2 - Tu, 3 - We, 4 - Th, 5 - Fr, 6 - Sat } */
			 ,'bk_highlight_timeslot_word' => esc_js( $booking_highlight_timeslot_word )								//FixIn: 8.2.1.99
             ,'is_booking_recurrent_time' => ( ( get_bk_option( 'booking_recurrent_time' ) !== 'On')?'false':'true' )         
             ,'is_booking_used_check_in_out_time' => ( ( get_bk_option( 'booking_range_selection_time_is_active' ) !== 'On')?'false':'true' )
            , 'bk_currency_pos' => get_bk_option( 'booking_currency_pos' )      //FixIn: 7.0.1.49
        ) );        
    }    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load JavaScripts Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_js_files( $where_to_load = 'both' ){
    	//wp_enqueue_script( 'wpbc-times', wpbc_plugin_url( '/inc/js/wpbc_times.js'),   array( 'wpbc-main-client' ), WP_BK_VERSION_NUM );         				//FixIn: TimeFree 2   - comment it for Free
        wp_enqueue_script( 'wpbc-bs', WPBC_PLUGIN_URL . '/inc/js/biz_s.js', 		array( 'wpbc-global-vars' ), WP_BK_VERSION_NUM );
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load CSS Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_css_files( $where_to_load = 'both' ){ 
        
    }


    //    A d d    E l e m e n t s     t o     B o o k  i n g     F o r m   //
    
        /**
	 * Add DIV structure, where to show payment form
         * 
         * @param string $form_content  - booking form with  calendar
         * @return string               - modified booking form
         */
        function add_paypal_form($form_content) {
         
            // If all gateways OFF - then no payment form
            $is_turned_off = apply_bk_filter('is_all_payment_forms_off', true); 
            if ( $is_turned_off )
                return $form_content ;

            
            // If we at adminpanel - then no payment form
            if ( strpos( $_SERVER['REQUEST_URI'], 'booking.php' ) !== false ) 
                return $form_content ;

            
            /* Get in booking form  in line like this
            * <form id="booking_form3" class="booking_form vertical" action="" method="post">
            * ID of booking resource here: booking_form3"
            */
            
            $str_start = strpos( $form_content, 'booking_form');
            $str_fin   = strpos( $form_content, '"', $str_start);

            $booking_resource_id = substr($form_content,$str_start+12, ($str_fin-$str_start-12) );

            $form_content .= '<div  id="gateway_payment_forms' . $booking_resource_id . '"></div>';
            return $form_content;
        }


        // Add  F I X E D   R a n g e    T I M E   to   Form        //////////////
        function wpdev_booking_form_content ($my_form_content, $bk_type){
            if( get_bk_option( 'booking_range_selection_time_is_active') == 'On' )  {

            	$exception_pages = array( '/page-no-change-over/' );
            	// Do  not use check in/out times at  specific pages
				$request_uri = $_SERVER['REQUEST_URI'];                                 //FixIn:5.4.1
				if ( strpos( $request_uri, 'booking_hash=') !== false ) {
					$request_uri = parse_url($request_uri);
					if (  ( ! empty($request_uri ) ) && ( isset($request_uri['path'] ) )  ){
						$request_uri = $request_uri['path'];
					} else {
						$request_uri = $_SERVER['REQUEST_URI'];
					}
				}
				if  ( in_array( $request_uri, $exception_pages ) )
						return $my_form_content;

                if ( strpos($my_form_content, 'name="starttime') !== false )  $my_form_content = str_replace( 'name="starttime', 'name="advanced_stime', $my_form_content);
                if ( strpos($my_form_content, 'name="endtime') !== false )    $my_form_content = str_replace( 'name="endtime',   'name="advanced_etime', $my_form_content);

                $my_form_content .= '<input name="starttime'.$bk_type.'"  id="starttime'.$bk_type.'" type="text" value="'.get_bk_option( 'booking_range_selection_start_time').'" style="display:none;">';
                $my_form_content .= '<input name="endtime'.$bk_type.'"  id="endtime'.$bk_type.'" type="text" value="'.get_bk_option( 'booking_range_selection_end_time').'"  style="display:none;">';
            }
            return $my_form_content;
        }



//  A d m i n    p a n e l   ->   Booking   ///////////////////////////////////////////////////////////////////////


    // Save booking cost, after direct edit at admin panel from Ajax request
    function wpdev_save_bk_cost(){ global $wpdb;

           $booking_id = intval( $_POST[ "booking_id" ] );
           $cost = $_POST[ "cost" ];
           $cost = str_replace(',', '.', $cost);
           $summ = floatval(  $cost );
           //$summ = round($summ,2);//FixIn: 8.1.3.33

           if ( $summ >= 0 ) {
               $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.cost=%f WHERE bk.booking_id= %d ", $summ, $booking_id );

                if ( false === $wpdb->query( $update_sql ) ){
                    ?> <script type="text/javascript"> 
                            var my_message = '<?php echo html_entity_decode( esc_js( get_debuge_error('Error during cost saving' ,__FILE__,__LINE__) ),ENT_QUOTES) ; ?>';
                            wpbc_admin_show_message( my_message, 'error', 30000 );                                                                                              
                       </script> <?php
                        die();
                }

			    if ( get_bk_option( 'booking_send_email_on_cost_change')  == 'On' ) {                                    //FixIn: 8.1.3.30
                    $booking_data = apply_bk_filter('wpbc_get_booking_data',  $booking_id);
                    wpbc_send_email_modified($booking_id, $booking_data['type'], $booking_data['form']);
                }
                
                ?>
                    <script type="text/javascript">
                        var my_message = '<?php echo html_entity_decode( esc_js( __('Cost saved successfully' ,'booking') ),ENT_QUOTES) ; ?>';
                        wpbc_admin_show_message( my_message, 'success', 3000 );                                                                      
                    </script>
                <?php
           } else {
                ?>
                    <script type="text/javascript">
                        var my_message = '<?php echo html_entity_decode( esc_js( __('Cost is not correct. It must be greater than 0' ,'booking') ),ENT_QUOTES) ; ?>';
                        wpbc_admin_show_message( my_message, 'warning', 5000 );                                                                      
                    </script>
                <?php
           }
    }



//  P a y m e n t     r e q u e s t  //HASH_EDIT  ///////////////////////////////////////////////////////////////////////

    // P A Y M E N T    R E Q U E S T    -->  Show Paypal form in

    function get_bk_currency_format( $sum ){
        $cost_currency_format_decimal_separator   = get_bk_option( 'booking_cost_currency_format_decimal_separator'  );
        $cost_currency_format_thousands_separator = get_bk_option( 'booking_cost_currency_format_thousands_separator' );
        $cost_currency_format_thousands_separator = str_replace('space', ' ', $cost_currency_format_thousands_separator);
        $cost_currency_format_decimal_number = get_bk_option( 'booking_cost_currency_format_decimal_number'  );
        if ( ! isset($cost_currency_format_decimal_number)) $cost_currency_format_decimal_number = 2;
        $cost_currency_format_decimal_number = intval($cost_currency_format_decimal_number);

        $sum = round($sum,  $cost_currency_format_decimal_number);

        $sum = number_format($sum, $cost_currency_format_decimal_number, $cost_currency_format_decimal_separator, $cost_currency_format_thousands_separator);

        return $sum;
    }

    // Payment request ONLY from email!!!
    function get_payment_form( $booking_id, $booking_type ){//, $booking_days_count, $times_array , $booking_form ){

        global $wpdb;

        $bk_title    = $this->get_booking_type( $booking_type );
        $summ        = $this->get_booking_cost_from_db( '', $booking_id );

         $sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}booking as bk WHERE bk.booking_id = %d", $booking_id );
         $result_bk = $wpdb->get_results( $sql );

         if (  ( count($result_bk)>0 )  ) {

            $sdform = $result_bk[0]->form;

            $dates = wpbc_get_str_sql_dates_in_booking($result_bk[0]->booking_id);
            //    $my_dates_4_send = wpbc_change_dates_format($dates);

            $my_d_c = explode(',', $dates);
            $my_dates_4_send = '';
            foreach ($my_d_c as $value) {

                $my_single_date = substr(trim($value),0,10);
                if( strpos($my_single_date, '-') !== false)     $my_single_date = explode('-',$my_single_date);
                else                                            $my_single_date = explode('.',$my_single_date);
                $my_dates_4_send .=  $my_single_date[2].'-'.$my_single_date[1].'-'.$my_single_date[0].  ', ' ;

            }
            $dates = substr($my_dates_4_send,0,-2) ;
            $booking_days_count = $dates;

            $start_time = trim($my_d_c[0]);
            $end_time   = trim($my_d_c[count($my_d_c)-1]);
            $start_time = substr($start_time,-8,5);
            $end_time = substr($end_time,-8,5);

         } else { return ''; }

        ///////////////////////////////////////////////////////////////////////////

        $wp_nonce = microtime( true ) * 100 ;   //ceil( time() / ( 86400 / 2 ));    //FixIn: 7.0.1.63

        $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_status='$wp_nonce' WHERE bk.booking_id= %d ", $booking_id );
        if ( false === $wpdb->query( $update_sql  ) ){
            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }

        //get_bk_option( 'booking_cost_currency_format_decimal_separator'  );
        //get_bk_option( 'booking_cost_currency_format_thousands_separator' );
        $cost_currency_format_decimal_number = get_bk_option( 'booking_cost_currency_format_decimal_number'  );
        if ( ! isset($cost_currency_format_decimal_number)) $cost_currency_format_decimal_number = 2;
        $cost_currency_format_decimal_number = intval($cost_currency_format_decimal_number);

        $summ = round($summ,  $cost_currency_format_decimal_number);

        if ( ($summ + 0) == 0)  $real_payment_form = '';
        else {

            // // Payment request ONLY from email!!!

            
            $output = apply_bk_filter(  'wpbc_get_gateway_forms'
                                        , ''
                                        , array (
                                                  'booking_id' => $booking_id           // 9        
                                                , 'cost' => $summ                       // 75
                                                , 'resource_id' => $booking_type        // 4                                          
                                                , 'form' => $sdform                     // select-one^rangetime4^10:00 - 12:00~text^name4^Jo~text^secondname4^Smith~email^email4^smith@wpbookingcalendar.com~text^phone4^378934753489~text^address4^Baker street 7~text^city4^London~text^postcode4^798787~select-one^country4^GB~select-one^visitors4^1~select-one^children4^0~textarea^details4^test booking ~checkbox^term_and_condition4[]^I Accept term and conditions
                                                , 'nonce' => $wp_nonce                  // 33962
                                                , 'payment_type' => 'payment_request'   // 'payment_request' | 'payment_form'
                                            )
                        );                    

            
            $original_symbols  = array( '&nbsp;'  );
            $temporary_symbols = array( '^space^' );
            $output = str_replace( $original_symbols, $temporary_symbols, $output);
            
            $output = esc_js($output);  
            $output = html_entity_decode($output);

            $original_symbols  = array( '^space^' , "\\n", 'CURRENCY_SYMBOL' );
            
                make_bk_action('check_multiuser_params_for_client_side', $booking_type );      // MU        - Get correct currency of specific user at  front-end
            
            $temporary_symbols = array( '&nbsp;'  , '',     wpbc_get_currency_symbol() );
            
                make_bk_action('finish_check_multiuser_params_for_client_side', $booking_type );        // MU
            
            $output = str_replace( $original_symbols, $temporary_symbols, $output);

//FixIn: 8.4.0.3
            $real_payment_form = '<span id="gateway_payment_req_forms' . $booking_type .'" class="wpdevelop">';
            $real_payment_form .= $output;                                      // Show Payment forms
  	        $real_payment_form .= '</span>';
            $real_payment_form .= '<script type="text/javascript">';            // Show and Scroll to Payment form
			//FixIn: 8.3.3.4
			$real_payment_form .=   ' if ( jQuery( "#gateway_payment_req_forms' . $booking_type . ' ' . '.wpbc-replace-ajax.wpbc-payment-form" ).length > 0 ){ ' ;
			$real_payment_form .=   ' jQuery( "#gateway_payment_req_forms' . $booking_type . ' ' . '.wpbc-replace-ajax.wpbc-payment-form" ).html( jQuery( "#gateway_payment_req_forms' . $booking_type . ' ' . '.wpbc-replace-ajax.wpbc-payment-form" ).html().replace( /ajax_script/gi, "script" ) );';	//FixIn:8.0.1.10	//Fix: Stripe integration trick
			$real_payment_form .=   ' } ' ;
			$real_payment_form .=   ' jQuery("#booking_form_div' . $booking_type . '" ).hide();';
			$real_payment_form .=   ' makeScroll("#gateway_payment_req_forms' . $booking_type . '" );';
			$real_payment_form .=   ' jQuery("#submiting' . $booking_type . '").html("");';
//FixIn: 8.4.0.3 end

            $real_payment_form .= '</script>';
                        

        }

        return $real_payment_form ;
    }

    function update_payment_request_count($booking_id, $value){
        global $wpdb;
        $value++;
        $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_request= %d WHERE bk.booking_id= %d ", $value, $booking_id );
        if ( false === $wpdb->query( $update_sql ) ){
            ?> <script type="text/javascript"> 
                    var my_message = '<?php echo html_entity_decode( esc_js( get_debuge_error('Error during updating wp_payment_request_count status in BD' ,__FILE__,__LINE__) ),ENT_QUOTES) ; ?>';
                    wpbc_admin_show_message( my_message, 'error', 30000 );                                                                                                                     
            </script> <?php
            die();
        }

    }

    // Send Email request to customer for payment
    function wpdev_send_payment_request(){ global $wpdb;

        $booking_id = intval( $_POST[ "booking_id" ] );
        $reason = stripslashes( $_POST["reason"] );                             //FixIn: 7.0.1.46       - trasnalte words like don\'t to don't

        $sql = "SELECT * FROM {$wpdb->prefix}booking as bk WHERE bk.booking_id = $booking_id ";
        $result_bk = $wpdb->get_results( $sql );

        if (  ( count($result_bk)>0 )  ) {

          $is_email_payment_request_adress   = get_bk_option( 'booking_is_email_payment_request_adress' );

          $reason  = htmlspecialchars( str_replace('\"','"', $reason ));
          $reason  =  str_replace("\'","'",$reason );

          foreach ($result_bk as $res) {

             if ( $is_email_payment_request_adress != 'Off') {
                 $is_send = wpbc_send_email_payment_request($res->booking_id, $res->booking_type , $res->form , $reason );

                 if ( $is_send ) $this->update_payment_request_count($res->booking_id, ($res->pay_request) );
             }
          }
          ?>
             <script type="text/javascript">
                var my_message = '<?php echo html_entity_decode( esc_js( __('Request has been sent' ,'booking') ),ENT_QUOTES) ; ?>';
                wpbc_admin_show_message( my_message, 'success', 3000 );            
             </script>
          <?php

        } else {
            ?> <script type="text/javascript"> 
                var my_message = '<?php echo html_entity_decode( esc_js( __('Request has failed' ,'booking') ),ENT_QUOTES) ; ?>';
                wpbc_admin_show_message( my_message, 'error', 3000 );            
            </script> <?php
        }
    }

    // Chnage the status of payment
    function wpdev_change_payment_status($booking_id = '', $payment_status = '', $payment_status_show = false  ){ global $wpdb;

        if ($booking_id === '') {
            $booking_id      = $_POST[ "booking_id" ];
            $payment_status  = $_POST[ "payment_status" ];
            $payment_status_show  = $_POST[ "payment_status_show" ];
        }

        $sql =  $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}booking as bk WHERE bk.booking_id= %d ", $booking_id );
        $result_bk = $wpdb->get_results( $sql );

        if (  ( count($result_bk)>0 )  ) {

            $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_status= %s WHERE bk.booking_id= %d ", $payment_status, $booking_id );
            if ( false === $wpdb->query( $update_sql  ) ){
                 ?> <script type="text/javascript"> 
                        var my_message = '<?php echo html_entity_decode( esc_js( get_debuge_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__) ),ENT_QUOTES) ; ?>';
                        wpbc_admin_show_message( my_message, 'error', 30000 );                                                                                                                                          
                    </script> <?php
                 die();
            }
            if ($payment_status_show !== false ) {
                ?><script type="text/javascript">                    
                    set_booking_row_payment_status('<?php echo $booking_id; ?>','<?php echo $payment_status; ?>','<?php echo $payment_status_show; ?>');
                    var my_message = '<?php echo html_entity_decode( esc_js( __('The payment status is changed successfully' ,'booking') ),ENT_QUOTES) ; ?>';
                    wpbc_admin_show_message( my_message, 'success', 3000 );                                 
                  </script><?php
            }
        } else {
            if ($payment_status_show !== false ) {
                ?> <script type="text/javascript"> 
                    var my_message = '<?php echo html_entity_decode( esc_js( __('The changing of payment status is failed' ,'booking') ),ENT_QUOTES) ; ?>';
                    wpbc_admin_show_message( my_message, 'error', 3000 );                                
                </script> <?php
            }
        }

    }



// P A Y M E N T    A J A X   F O R M   //////////////////////////////////////////////////////////////////////

    // Claculate the cost for specific days(times) based on base_cost for specific period
    function get_cost_for_period($period, $base_cost, $days, $times = array(array('00','00','01'), array('24','00','02')) ) {

//FixIn: 6.2.3.3
if ( $times[1] == array('23','59','02') ) 
        $times[1] = array('24','00','00');                      
$days_array = $days;
$times_array = $times;
if ( count($days_array) == 1 ) {
    $d_day = $days_array[0];
    if (! empty($d_day)) {
        $d_day = explode('.',$d_day);
        $day =  ($d_day[0]+0); $month =  ($d_day[1]+0); $year = ($d_day[2]+0);
        $start_time_in_ms = mktime($times_array[0][0], $times_array[0][1], $times_array[0][2], $month, $day, $year );
        $end_time_in_ms = mktime($times_array[1][0], $times_array[1][1], $times_array[1][2], $month, $day, $year );        
        if ( ( $end_time_in_ms - $start_time_in_ms ) < 0 ) {
            //We need to  add one extra day,  because the end time outside of 24:00 already
            $days[] = date('d.m.Y', mktime(0, 0, 0, $month, ($day+1), $year )  );
        }
    }        
}             
//FixIn: 6.2.3.3 - end


            $fin_cost = 0 ;

            $is_time_apply_to_cost  = get_bk_option( 'booking_is_time_apply_to_cost'  );

            if ($is_time_apply_to_cost == 'On') {                           // Make some corrections if TIME IS APPLY TO THE COST
                if ($period == 'day') {
                    $period = 'hour';
                    $base_cost = $base_cost / 24 ;
                } else if ($period == 'night') {
                    $period = 'hour';
                    $base_cost = $base_cost / 24 ;
                } else if ($period == 'hour') {                             // Skip here evrything fine
                } else {                                                    // Skip here evrything fine
                }
            }

            if ($period == 'day') {

                $fin_cost = count($days) * $base_cost;

            } else if ($period == 'night') {

                $night_count = (count($days)>1) ? (count($days)-1) : 1;
                $fin_cost = $night_count * $base_cost;

            } else if ($period == 'hour') {

                $start_time = $times[0];
                $end_time   = $times[1];
                if ($end_time == array('00','00','00')) $end_time = array('24','00','00');

                if (count($days)<=1) {

                        $m_dif =  ($end_time[0] * 60 + intval($end_time[1]) ) - ($start_time[0] * 60 + intval($start_time[1]) ) ;
                        $fin_cost =   $m_dif * $base_cost / 60;

                } else {
                    $full_days_count = count($days) - 2;

                    $full_days_cost =   $full_days_count* 24 * 60 * $base_cost / 60;
                    $check_in_cost  = ( 24 * 60  - ($start_time[0] * 60 + intval($start_time[1]) ) ) * $base_cost / 60;
                    $check_out_cost = ( $end_time[0] * 60 + intval($end_time[1]) )  * $base_cost / 60;
                    $fin_cost = $check_in_cost + $full_days_cost + $check_out_cost ;
                }

            } else { // Fixed

                $fin_cost = $base_cost;
            }

            //$fin_cost = round( $fin_cost ,2 );//FixIn: 8.1.3.33

            return  $fin_cost;
    }


    // C A L C U L A T E     C O S T     f o r      B o o k i n g

	/**
	 * @param      $booking_type					=> 4
	 * @param      $booking_days_count				=> 10.06.2018, 11.06.2018, 12.06.2018, 13.06.2018, 14.06.2018
	 * @param      $times_array						array(  	[0] => array( [0] => 14, [1] => 00, [2] => 01 ), 		[1] => array( [0] => 12, [1] => 00, [2] => 02 ) )
	 * @param      $post_form						text^selected_short_timedates_hint4^06/11/2018 14:00 - 06/14/2018 12:00~text^nights_number_hint4^3~text^cost_hint4^$100.00~text^name4^~email^email4^~select-one^visitors4^1~select-one^children4^0~text^starttime4^14:00~text^endtime4^12:00
	 * @param bool $is_discount_calculate			true
	 * @param bool $is_only_original_cost			false
	 *
	 * @return array|bool|float|int|mixed|null|string
	 */
    function get_booking_cost($booking_type, $booking_days_count, $times_array, $post_form, $is_discount_calculate = true, $is_only_original_cost = false){

                $paypal_price_period    = get_bk_option( 'booking_paypal_price_period' );
                $is_time_apply_to_cost  = get_bk_option( 'booking_is_time_apply_to_cost'  );
                if ( ($is_time_apply_to_cost == 'Off') && ($paypal_price_period != 'hour') ) $times_array = array(array('00','00','01'), array('24','00','02'));

                $days_array     = explode(',', $booking_days_count);

                // Get sorted days
                $dates_in_diff_formats = wpbc_get_sorted_days_array( $booking_days_count );
                foreach ( $dates_in_diff_formats as $key_d => $value_d ) {
                   $dates_in_diff_formats[ $key_d ] =  date_i18n( 'd.m.Y', strtotime( $value_d ) );
                }
                $days_array = $dates_in_diff_formats;

                $days_count     = count($days_array);
                

                $paypal_dayprice        = $this->get_cost_of_booking_resource( $booking_type ) ;
                $paypal_dayprice_orig   = $paypal_dayprice;


                if (
						( get_bk_option( 'booking_recurrent_time' ) !== 'On') 
					||	(	   ( $times_array[ 0 ][ 0 ] == '00' ) 
							&& ( $times_array[ 0 ][ 1 ] == '00' ) 
							&& ( $times_array[ 1 ][ 0 ] == '00' ) 
							&& ( $times_array[ 1 ][ 1 ] == '00' ) 
						) 
					||  (	   ( get_bk_option( 'booking_recurrent_time' ) == 'On' )									//FixIn:7.1.2.11
							&& ( get_bk_option( 'booking_paypal_price_period' ) == 'day' ) 
							&& ( get_bk_option( 'booking_is_time_apply_to_cost' ) != 'On' )
						)
				){
					if ( ! class_exists('wpdev_bk_biz_m') ) {

                        $summ = $this->get_cost_for_period(
                                                            get_bk_option( 'booking_paypal_price_period' ),
                                                            $this->get_cost_of_booking_resource( $booking_type ) ,
                                                            $days_array,
                                                            $times_array
                            );

                    } else  {

                        $paypal_dayprice        = apply_bk_filter('wpdev_season_rates', $paypal_dayprice, $days_array, $booking_type, $times_array,$post_form);  // Its return array with day costs
//debuge( $paypal_dayprice);
                        if (is_array($paypal_dayprice)) {
                            $summ = 0.0;
                            for ($ki = 0; $ki < count($paypal_dayprice); $ki++) { $summ += $paypal_dayprice[$ki]; }
                        } else {
                            $summ = (1* $paypal_dayprice * $days_count );
                        }

                    }

                } else { // Recurent time in evry days calculation

                    $final_summ = 0;
                    $temp_days = $days_array;
                    $temp_paypal_dayprice = $paypal_dayprice;

                    foreach ( $temp_days as $day_numb => $days_array ) {  // lOOP EACH DAY

                        $days_array = array($days_array);
                        $paypal_dayprice = $temp_paypal_dayprice;


                        if ( ! class_exists('wpdev_bk_biz_m') ) {

                            $summ = $this->get_cost_for_period(
                                                                get_bk_option( 'booking_paypal_price_period' ),
                                                                $this->get_cost_of_booking_resource( $booking_type ) ,
                                                                $days_array,
                                                                $times_array
                                );

                            if (get_bk_option( 'booking_paypal_price_period' ) == 'fixed')          $final_summ = 0; // if we are have fixed cost calculation so we will not gathering all costs but get just last one.

                            // Set first day as 0, if we have true all these conditions
                            if (   (get_bk_option( 'booking_paypal_price_period' ) == 'night')
                                && (get_bk_option( 'booking_is_time_apply_to_cost' ) != 'On' )
                                && ( count($temp_days)>1 ) && ($final_summ == 0 ) && ($summ > 0) ) 
                            {                                 
                                $final_summ = -1*$summ + 0.000001;  // last number is need for definition its only for first day and make its little more than 0, then at final cost there is ROUND to the 2 nd number after comma.
                            }


                        } else  {

                            $paypal_dayprice        = apply_bk_filter('wpdev_season_rates', $paypal_dayprice, $days_array, $booking_type, $times_array,$post_form);  // Its return array with day costs
//debuge('$paypal_dayprice',$paypal_dayprice);
                            if (is_array($paypal_dayprice)) {
                                $summ = 0.0;
                                for ($ki = 0; $ki < count($paypal_dayprice); $ki++) { $summ += $paypal_dayprice[$ki]; }
                            } else {
                                $summ = (1* $paypal_dayprice * $days_count );
                            }

                        }

                        $final_summ += $summ;
                        $summ = 0.0;
                    }

                    $paypal_dayprice = $temp_paypal_dayprice;
                    $days_array = $temp_days;
                    $summ = $final_summ;
                }

                if (get_bk_option( 'booking_paypal_price_period' ) == 'fixed') {                        
                        if (is_array($paypal_dayprice) )  $summ = $paypal_dayprice[0] ;
                        else                             $summ = $paypal_dayprice ;
                }


                //$summ = round($summ,2);	//FixIn: 8.1.3.33
                $summ_original_without_additional = $summ ;

                if ($is_only_original_cost) {
                    if ($is_discount_calculate) {
                        $summ_original_without_additional = apply_bk_filter('coupons_discount_apply', $summ_original_without_additional, $post_form, $booking_type ); // Apply discounts coupons
                    }
                    return $summ_original_without_additional;
                }

                if ( $summ > 0 ) {                                              // Apply additional  cost,  only  if the booking cost > 0
//debuge('advanced_cost_apply', $summ , $post_form, $booking_type, $days_array );
                $summ = apply_bk_filter('advanced_cost_apply', $summ , $post_form, $booking_type, $days_array );    // Apply advanced cost managemnt
//debuge($summ,$post_form);

                $summ = apply_bk_filter('early_late_booking_apply', $summ , $post_form, $booking_type, $days_array );   // Apply early_late_booking		//FixIn: 8.2.1.17

                if ( $is_discount_calculate )
                    $summ = apply_bk_filter('coupons_discount_apply', $summ , $post_form, $booking_type ); // Apply discounts based on coupons
                }

                $summ = apply_filters( 'wpbc_booking_cost_custom_round', $summ );

                return $summ;
    }

    function wpbc_booking_cost_custom_round_apply( $cost ){                                                             //FixIn: 8.1.3.3

		return $cost;																									//FixIn: 8.1.3.33

		// Fix for some other customer.
	    //$cost = round( $cost, 2 );
		//	10.1  - 10.24 = 10.25
		//	10.26 - 10.49 = 10.50
		//	10.51 - 10.74 = 10.75
		//	10.76 - 10.99 = 11

		$cost_n = floor( $cost );
		$cost_d = ($cost - $cost_n)*100;

		$cost = $cost_n;
		if ( $cost_d >= 10 )
			$cost = $cost_n + 0.25;
		if ( $cost_d >= 26 )
			$cost = $cost_n + 0.50;
		if ( $cost_d >= 51 )
			$cost = $cost_n + 0.75;
		if ( $cost_d >= 76 )
			$cost = $cost_n + 1;
		return $cost;
    }
    
    // Update Cost and Cost_Nonce in DB for the new booking
    function wpbc_update_cost_of_new_booking( $booking_id, $booking_type, $booking_days_count, $times_array , $booking_form ) {
        
        
        $summ = $this->get_booking_cost( $booking_type, $booking_days_count, $times_array, $booking_form );

        $summ_deposit = apply_bk_filter('fixed_deposit_amount_apply', $summ , $booking_form, $booking_type, $booking_days_count ); // Apply fixed deposit
//      $summ_deposit = apply_bk_filter('advanced_cost_apply', $summ_deposit, $booking_form, $booking_type, explode(',', $booking_days_count)  );    // Fix: 6.1.1.12         

        $is_deposit = false;
        if ($summ_deposit != $summ ) {
            $is_deposit = true;
            $summ__full = $summ;
            $summ       = $summ_deposit;
        }

        // Check for additional calendars
        $additional_calendars = array();
        $summ_additional_calendars = apply_bk_filter('check_cost_for_additional_calendars', $summ, $booking_form, $booking_type,  $times_array  ); // Apply cost according additional calendars
        if (isset($summ_additional_calendars))
            if( is_array($summ_additional_calendars) ) {
                $summ = $summ_additional_calendars[0];
                $additional_calendars = $summ_additional_calendars[2];
            }

        ///////////////////////////////////////////////////////////////////////////

        global $wpdb;
        $wp_nonce = microtime( true ) * 100 ;   //ceil( time() / ( 86400 / 2 ));    //FixIn: 7.0.1.63

        $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_status='$wp_nonce' WHERE bk.booking_id= %d", $booking_id ) ;
        if ( false === $wpdb->query( $update_sql  ) ){
            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
            return  false;            
        }
        
        return array(
                        'total_cost' => ( $is_deposit ? $summ__full : $summ )
                      , 'deposit_cost' => $summ_deposit
                      , 'wp_nonce' => $wp_nonce
                      , 'is_deposit' => $is_deposit
                      , 'additional_calendars' => $additional_calendars
                );
    }

    
    // Show Paypal form from Ajax request - Just  usual  payment form, after  booking process.
    function show_paypal_form_in_ajax_request($booking_id, $booking_type, $booking_days_count, $times_array , $booking_form ){

		//FixIn: 8.1.3.23
		if ( get_bk_option( 'booking_payment_form_in_request_only' ) == 'On' ) {
			return false;
		}

//debuge($_POST);

        $respond_array = $this->wpbc_update_cost_of_new_booking($booking_id, $booking_type, $booking_days_count, $times_array, $booking_form);
        
        if (! empty($respond_array) ) {
            
            $wp_nonce = $respond_array['wp_nonce'];
            $is_deposit = $respond_array['is_deposit'];
            $additional_calendars = $respond_array['additional_calendars'];  
            
            $summ_deposit = $respond_array['deposit_cost'];
            if ( $is_deposit ) {
                $summ__full = $respond_array['total_cost'];
                $summ       = $respond_array['deposit_cost'];                
            } else {
                $summ = $respond_array['total_cost'];
            }
            
        } else {            
            die;    // Something was wrong !
        }
        
        $bk_title    = $this->get_booking_type( $booking_type );
        
        //$summ = round($summ,2);	//FixIn: 8.1.3.33
        
        
        // Show Paypal form from Ajax request - "Usual Payment Form(s)", after  booking process.
        $output = apply_bk_filter(  'wpbc_get_gateway_forms'
                                    , ''
                                    , array (
                                              'booking_id' => $booking_id           // 9        
                                            , 'cost' => $summ                       // 75
                                            , 'resource_id' => $booking_type        // 4                                          
                                            , 'form' => $_POST["form"]              // select-one^rangetime4^10:00 - 12:00~text^name4^Jo~text^secondname4^Smith~email^email4^smith@wpbookingcalendar.com~text^phone4^378934753489~text^address4^Baker street 7~text^city4^London~text^postcode4^798787~select-one^country4^GB~select-one^visitors4^1~select-one^children4^0~textarea^details4^test booking ~checkbox^term_and_condition4[]^I Accept term and conditions
                                            , 'nonce' => $wp_nonce                  // 33962
                                            , 'payment_type' => 'payment_form'   // 'payment_request' | 'payment_form'
                                                , 'is_deposit' => $is_deposit      // true | false
                                                , 'additional_calendars' => $additional_calendars                                            
                                            , 'booking_form_type' => isset( $_POST['booking_form_type'] ) ? $_POST['booking_form_type'] : 'standard'        // If we are using custom booking form during creation  new booking,  so  transfer this parameter, for calculation  additional  cost  in biz_m version
                                        )
                    );                    

        // Just make some Notes about deposit and balances
        if ($is_deposit)
            if (($summ__full-$summ_deposit)>0) {
                
                $summ_show           = wpbc_get_cost_with_currency_for_user( $summ_deposit, $booking_type );
                $full_summ_show      = wpbc_get_cost_with_currency_for_user( $summ__full, $booking_type );
                $balance_summ_show   = wpbc_get_cost_with_currency_for_user( ($summ__full-$summ_deposit), $booking_type );
                
                $cost__title_deposit  = __('deposit' ,'booking').": ";
                $cost__title_total    = __('Total cost' ,'booking').": ";
                $cost__title_balace   = __('balance' ,'booking').": ";

                $today_day = date_i18n( get_bk_option( 'booking_date_format') ); //date('m.d.Y')  ; //FixIn:5.4.5.7
                
                $cost_summ_with_title='';
                $cost_summ_with_title .= $cost__title_total   . $full_summ_show .  " /";
                $cost_summ_with_title .= $cost__title_deposit . $summ_show .  ", ";
                $cost_summ_with_title .= $cost__title_balace  . $balance_summ_show .  "/";
                $cost_summ_with_title .= ' - '  . $today_day .'';

                make_bk_action('wpdev_make_update_of_remark' , $booking_id , $cost_summ_with_title , true );

                $this->update_booking_cost($booking_id, $summ_deposit );
        } // fin. notes.



        $is_turned_off = apply_bk_filter('is_all_payment_forms_off', true);
        if ($is_turned_off)  return;

        make_bk_action('wpbc_set_coupon_inactive', $booking_id, $booking_type, $booking_days_count, $times_array , $booking_form );
        
        
        

        if (    ( ($summ + 0) > 0) 
//             || ( get_bk_option( 'booking_is_show_booking_summary_in_payment_form' ) == 'On' )  
            ){   
            
            $original_symbols  = array( '&nbsp;'  );
            $temporary_symbols = array( '^space^' );
            $output = str_replace( $original_symbols, $temporary_symbols, $output);
            
            $output = esc_js($output);  
            $output = html_entity_decode($output);
            
            $original_symbols  = array( '^space^' , 'CURRENCY_SYMBOL' );
            
                make_bk_action('check_multiuser_params_for_client_side', $booking_type );      // MU        - Get correct currency of specific user at  front-end
            
            $temporary_symbols = array( '&nbsp;'  ,  wpbc_get_currency_symbol() );
            
                make_bk_action('finish_check_multiuser_params_for_client_side', $booking_type );        // MU

            $output = str_replace( $original_symbols, $temporary_symbols, $output);

            ?>
            <script type="text/javascript">
               document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML ='';
               if (document.getElementById('gateway_payment_forms<?php echo $booking_type; ?>') != null) {
                  document.getElementById('gateway_payment_forms<?php echo $booking_type; ?>').innerHTML = '<div class=\"wpdevelop\" style=\"height:auto;margin:20px 0px;\" ><?php echo $output; ?></div>';

				   //FixIn:8.0.1.10	//Fix: Stripe integration trick				//FixIn: 8.3.3.4
                  if ( jQuery( '#gateway_payment_forms<?php echo $booking_type; ?> .wpbc-replace-ajax.wpbc-payment-form' ).length > 0 ){
					  jQuery( '#gateway_payment_forms<?php echo $booking_type; ?> .wpbc-replace-ajax.wpbc-payment-form' ).html( jQuery( '#gateway_payment_forms<?php echo $booking_type; ?> .wpbc-replace-ajax.wpbc-payment-form' ).html().replace( /ajax_script/gi, 'script' ) );
				  }
                  setTimeout(function() { makeScroll("#gateway_payment_forms<?php echo $booking_type; ?>" ); }, 500);
              }
            </script>
            <?php
        } else {
            
        }
    }

}