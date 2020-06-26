<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly
require_once(WPBC_PLUGIN_DIR. '/inc/_bl/lib_l.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bl/wpbc-search-availability.php' );        // Search Availability Class
require_once(WPBC_PLUGIN_DIR. '/inc/_bl/l-toolbar.php' );

require_once(WPBC_PLUGIN_DIR. '/inc/_bl/admin/api-settings-l.php' );            // Settings page
require_once(WPBC_PLUGIN_DIR. '/inc/_bl/admin/page-search.php' );               // Settings page
require_once(WPBC_PLUGIN_DIR. '/inc/_bl/admin/wpbc-coupons-table.php' );
require_once(WPBC_PLUGIN_DIR. '/inc/_bl/admin/page-coupons.php' );              // Settings page

require_once(WPBC_PLUGIN_DIR. '/inc/_bl/admin/activation-l.php' );              // Activate / Deactivate

require_once(WPBC_PLUGIN_DIR. '/inc/_bl/wpdev-booking-search-widget.php' );
if (file_exists(WPBC_PLUGIN_DIR. '/inc/_mu/multiuser.php')) { require_once(WPBC_PLUGIN_DIR. '/inc/_mu/multiuser.php' ); }


class wpdev_bk_biz_l {

    var $wpdev_bk_multiuser;

    function __construct(){



        add_action('wpbc_define_js_vars', array(&$this, 'wpbc_define_js_vars') );
        add_action('wpbc_enqueue_js_files', array(&$this, 'wpbc_enqueue_js_files') );
        add_action('wpbc_enqueue_css_files',array(&$this, 'wpbc_enqueue_css_files') );

        // Coupons advanced cost customization option.
        add_bk_filter('coupons_discount_apply', array(&$this, 'coupons_discount_apply'));
        add_bk_filter('get_coupons_discount_info', array(&$this, 'get_coupons_discount_info'));
        add_bk_filter('wpdev_get_additional_description_about_coupons', array(&$this, 'wpdev_get_additional_description_about_coupons'));
		add_bk_filter('wpbc_get_coupon_code_discount_value',			array(&$this, 'wpbc_get_coupon_code_discount_value'));
        add_bk_action('wpbc_set_coupon_inactive', array(&$this, 'wpbc_set_coupon_inactive'));

        // JS - Tooltip
        add_filter('wpdev_booking_show_availability_at_calendar', array(&$this, 'show_availability_at_calendar') , 10, 2 );                // Write JS files

        // INSERT - UPDATE    --   ID   or  Dates
        add_bk_action('wpdev_booking_reupdate_bk_type_to_childs', array(&$this, 'reupdate_bk_type_to_childs')); // Main function

        // Filters for changing view of Dates...
        add_bk_filter('get_bk_dates_sql', array(&$this, 'get_sql_bk_dates_for_all_resources'));  // Modify SQL
        add_bk_filter('get_bk_dates', array(&$this,     'get_bk_dates_for_all_resources'));  // Modify Result of dates

        add_bk_filter('cancel_pending_same_resource_bookings_for_specific_dates', array(&$this, 'cancel_pending_same_resource_bookings_for_specific_dates'));  // Modify Result of dates

        //Booking Table Admin Page -- Show also bookins, where SOME dates belong to this Type
        // SQL Modification for Admin Panel dates:  (situation, when some bookings dates exist at several resources )
        add_bk_filter('get_sql_4_dates_from_other_types', array(&$this,     'get_sql_4_dates_from_other_types'));


        // For some needs
        add_bk_filter('get_booking_types_hierarhy_linear', array(&$this,     'get_booking_types_hierarhy_linear'));  // Modify Result of dates





        // If number = 1 - its means that  booking resource - single
        add_bk_filter('wpbc_get_number_of_child_resources', array(&$this, 'get_max_available_items_for_resource'));
        add_bk_filter('wpbc_get_max_visitors_for_bk_resources', array(&$this, 'get_max_visitors_for_bk_resources'));             //FixIn: 5.4.5.4


        // Booking Page  - Show only for PARENT booking resource

        add_bk_action('check_if_bk_res_parent_with_childs_set_parent_res', array(&$this, 'check_if_bk_res_parent_with_childs_set_parent_res'));


        // Search functionality
        add_bk_filter('wpdev_get_booking_search_form', array(&$this, 'wpdev_get_booking_search_form'));
        add_bk_filter('wpdev_get_booking_search_results', array(&$this, 'wpdev_get_booking_search_results'));
        add_bk_action('wpdev_ajax_booking_search', array($this, 'wpdev_ajax_booking_search'));

        add_bk_action('regenerate_booking_search_cache', array($this, 'regenerate_booking_search_cache'));

        if ( class_exists('wpdev_bk_multiuser')) {  $this->wpdev_bk_multiuser = new wpdev_bk_multiuser();
        } else {                                $this->wpdev_bk_multiuser = false; }

    }


// <editor-fold defaultstate="collapsed" desc=" S U P P O R T       F u n c t i o n s ">

// S U P P O R T       F u n c t i o n s    //////////////////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define JavaScripts Variables               //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_define_js_vars( $where_to_load = 'both' ){

        $my_page = 'client';                                            // Get a page
        $parent_booking_resources_values = '';
        if (   wpbc_is_new_booking_page() ) $my_page = 'add';
        else if ( wpbc_is_bookings_page() ) $my_page = 'booking';

//                $resources_cache = wpbc_br_cache();                                     // Get booking resources from  cache
//                $resource_objects = $resources_cache->get_resources();
        if (
                       ( $my_page == 'add' )
                    && ( ! isset( $_GET['booking_type'] ) )                     // For situation, when default bk resource is not set and this is parent resource
                    && ( get_bk_option( 'booking_default_booking_resource') == '' )     // and default booking resource == 'All resources'
            )
                wpbc_set_default_resource_to__get();

        if (
                   ( $my_page != 'add' )
                || ( isset( $_GET['parent_res'] ) )
                || (
                       ( $my_page == 'add' )                                    // For situation, when default bk resource is not set and this is parent resource
                    && ( ! isset( $_GET['booking_type'] ) )
                    && (  $this->check_if_bk_res_have_childs(  get_bk_option( 'booking_default_booking_resource') ) )
                   )
        ) {
                $parent_booking_resources_values = '';
                $arr = $this->get_booking_types_hierarhy_linear();  // Define Parent BK Resources (types) for JS
                foreach ($arr as $bk_res) {
                    if ($bk_res['count'] > 1 )
                        if (isset($bk_res['obj']->id))
                            $parent_booking_resources_values .= $bk_res['obj']->id .',';
                }
                if ( strlen($parent_booking_resources_values)>0 )
                    $parent_booking_resources_values = substr($parent_booking_resources_values, 0,-1);
        }

        wp_localize_script('wpbc-global-vars', 'wpbc_global5', array(
              'max_visitors_4_bk_res' => '[]'
            , 'message_verif_visitors_more_then_available' => esc_js(__('Try selecting fewer visitors. The number of visitors may be more than the number of available units on selected day(s)!' ,'booking'))
            , 'is_use_visitors_number_for_availability' => ( (get_bk_option( 'booking_is_use_visitors_number_for_availability') == 'On')?'true':'false' )
            , 'availability_based_on' => get_bk_option( 'booking_availability_based_on'  )
            , 'parent_booking_resources' => '[' . $parent_booking_resources_values . ']'
        ) );

    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load JavaScripts Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_enqueue_js_files( $where_to_load = 'both' ){
        wp_enqueue_script( 'wpbc-bl', WPBC_PLUGIN_URL . '/inc/js/biz_l.js', array( 'wpbc-global-vars' ), WP_BK_VERSION_NUM );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load CSS Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_enqueue_css_files( $where_to_load = 'both' ){
        if ( ( $where_to_load == 'client' ) || ( $where_to_load == 'both' ) ) {
            wp_enqueue_style('wpbc-css-search-form', WPBC_PLUGIN_URL . '/inc/css/search-form.css', array( ), WP_BK_VERSION_NUM );
        }
    }




    function get_available_spots_for_bk_res( $type_id ){

        $availability_based_on_visitors   = get_bk_option( 'booking_availability_based_on');

        if ($availability_based_on_visitors == 'visitors') {                // Based on Visitors
            // $max_visitors_in_bk_res = $this->get_max_visitors_for_bk_resources($type_id);
            $max_visitors_in_bk_res_summ=$this->get_summ_max_visitors_for_bk_resources($type_id);
            return $max_visitors_in_bk_res_summ;
        } else {                                                            // Based on Items.
            $max_visit_std         = $this->get_max_available_items_for_resource($type_id);
            return $max_visit_std;
        }

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" C O U P O N S  ">

        // Apply advanced cost to the cost from paypal form
        function coupons_discount_apply( $summ , $form , $bktype  ){
//debuge('check coupon'  , $summ);
            $original_summ = $summ;                                         // Original cost for booking

            $this->delete_expire_coupons();  // Delete some coupons if they are expire already

            $coupons = $this->get_coupons_for_this_resource($bktype);

            if ( count($coupons) <= 0) return $original_summ;               // No coupons so return as it is

            $booking_form_show = get_form_content ($form, $bktype);

            if (isset($booking_form_show['coupon']))
                if (! empty($booking_form_show['coupon'])) {

                        $entered_code = $booking_form_show['coupon'];
                        $entered_code = trim( stripslashes( $entered_code ) );
                        foreach ($coupons as $coupon) {

							$entered_code		 = strtolower( $entered_code );											//FixIn: 7.2.1.3
							$coupon->coupon_code = strtolower( $coupon->coupon_code );									//FixIn: 7.2.1.3

							if ($entered_code == $coupon->coupon_code) {

								if ( ( $summ >= $coupon->coupon_min_sum ) && ( ! empty($coupon->coupon_active) ) ) {        //FixIn: 5.4.2
									if ($coupon->coupon_type == 'fixed') {      // Fixed discount
										if ($coupon->coupon_value < $summ) {
											return ($original_summ - $coupon->coupon_value);
										} else {
											return 0;																	//FixIn: 8.1.2.2
										}
									}
									if ($coupon->coupon_type == '%') {          // Procent of
										if ($coupon->coupon_value <= 100) {
											return ($original_summ - $coupon->coupon_value * $original_summ / 100 );
										}
									}
								}
							}
                        }

                }

            return  $original_summ ;
        }


        //FixIn: 5.4.2
        /**
	 * Set coupons inactive after specific number of usage
         *
         * @global type $wpdb
         * @param type $booking_id
         * @param type $bktype
         * @param type $booking_days_count
         * @param type $times_array
         * @param type $form
         * @return boolean
         */
        function wpbc_set_coupon_inactive($booking_id, $bktype, $booking_days_count, $times_array, $form = false){
            global $wpdb;
            if ($form === false) {
               $form = escape_any_xss($_POST["form"]);
            }
            $coupons = $this->get_coupons_for_this_resource($bktype);

            if ( count($coupons) <= 0) return false;                            // No coupons so return as it is

            $booking_form_show = get_form_content ($form, $bktype);
            if (isset($booking_form_show['coupon']))
                if (! empty($booking_form_show['coupon'])) {

                        $entered_code = $booking_form_show['coupon'];
                        $entered_code = trim( stripslashes( $entered_code ) );
						$entered_code = strtolower( $entered_code );													//FixIn: 8.2.1.19

                        foreach ($coupons as $coupon) {

                          if ($entered_code == $coupon->coupon_code)
                            if ( /*( $summ >= $coupon->coupon_min_sum ) &&*/ ( ! empty($coupon->coupon_active) ) ) {
                               // Set  coupon one time lower
                               $coupon_active = ( (int) $coupon->coupon_active ) - 1;
//debuge($coupon_active);
                               $wp_query = "UPDATE {$wpdb->prefix}booking_coupons SET coupon_active = {$coupon_active} WHERE coupon_id = {$coupon->coupon_id}";
                               $wpdb->query( $wp_query );
                               return  true;
                            }
                        }
                }
            return  false;
        }


        // Get > Array discount info,   if it can be apply to the specific bk_resource and summ or return FALSE
        function get_coupons_discount_info( $summ , $form , $bktype  ){

            $original_summ = $summ;                                         // Original cost for booking

            $coupons = $this->get_coupons_for_this_resource($bktype);

            if ( count($coupons) <= 0) return false;               // No coupons so return as it is

            $booking_form_show = get_form_content ($form, $bktype);

            if (isset($booking_form_show['coupon']))
                if (! empty($booking_form_show['coupon'])) {

                        $entered_code = $booking_form_show['coupon'];
                        $entered_code = trim( stripslashes( $entered_code ) );
                        $entered_code = strtolower( $entered_code );													//FixIn: 8.0.2.7
//debuge($entered_code, $coupons );
                        foreach ($coupons as $coupon) {

                          if ($entered_code == $coupon->coupon_code)
                            if ( ( $summ >= $coupon->coupon_min_sum ) && ( ! empty($coupon->coupon_active) ) ) {          //FixIn: 5.4.2
                                if ($coupon->coupon_type == 'fixed') {      // Fixed discount
                                    if ($coupon->coupon_value < $summ) {

                                        $currency = wpbc_get_currency_symbol_for_user( $bktype );

                                        return (array($original_summ, $coupon->coupon_value, addslashes($entered_code) ,   $currency  . $coupon->coupon_value ));
                                    } else {

                                        $currency = wpbc_get_currency_symbol_for_user( $bktype );                        //FixIn: 8.1.2.2

                                        return (array($original_summ, $coupon->coupon_value, addslashes($entered_code) ,   $currency  . $original_summ ));

									}
                                }
                                if ($coupon->coupon_type == '%') {          // Procent of
                                    if ($coupon->coupon_value < 100) {
                                        return (array($original_summ , $coupon->coupon_value * $original_summ / 100,  addslashes($entered_code),    round($coupon->coupon_value,0)  . '%' ));
                                    }
                                }
                            }

                        }

                }

            return  false ;
        }


		function wpbc_get_coupon_code_discount_value( $blank, $bk_type , $dates,  $time_array, $form_post ) {

			// get COST without discount
            $summ_without_discounts   = apply_bk_filter('wpdev_get_bk_booking_cost', $bk_type , $dates, $time_array , $form_post , false );

            // Get Array with info according discount
            $additional_discount_info = $this->get_coupons_discount_info( $summ_without_discounts , $form_post , $bk_type  );

			$coupon_value = 0;

            if ($additional_discount_info !== false) {                      // If discount is exist

				$coupon_value = $additional_discount_info[1];															 //  % with currency
				/*
                $currency = wpbc_get_currency_symbol_for_user( $bk_type );

                if (strpos($additional_discount_info[3], '%') !== false)    // % or $
                     $coupon_value = $additional_discount_info[1];															 //  % with currency
                else $coupon_value = $additional_discount_info[3] ;                                                          // Only currency
				 */
			}

			return $coupon_value;
		}


        // Get Line with description according Coupon Discount, which is apply
        function wpdev_get_additional_description_about_coupons($blank, $bk_type , $dates,  $time_array, $form_post ){

            // get COST without discount
            $summ_without_discounts   = apply_bk_filter('wpdev_get_bk_booking_cost', $bk_type , $dates, $time_array , $form_post , false );

            // Get Array with info according discount
            $additional_discount_info = $this->get_coupons_discount_info( $summ_without_discounts , $form_post , $bk_type  );

            if ($additional_discount_info !== false) {                      // If discount is exist

                $currency = wpbc_get_currency_symbol_for_user( $bk_type );

                if (strpos($additional_discount_info[3], '%') !== false)    // % or $
                     $coupon_value = $additional_discount_info[3] . ' (' . $currency  . $additional_discount_info[1] . ') '; //  % with currency
                else $coupon_value = $additional_discount_info[3] ;                                                          // Only currency

                $blank = ' <span style="font-style:italic;font-size:85%;" class="coupon_description">[' .
                                      __('coupon' ,'booking') .  ' <strong>' . $additional_discount_info[2] .'</strong>: ' .
                                       $coupon_value .
                                      ' ' . __('discount' ,'booking') .
                          ']</span>';

	            //FixIn: 8.3.3.2
        		$blank = wpbc_replace_shortcode_hint( $blank, array(
                                              'shortcode'  => '[coupon_discount_hint]'
                                            , 'span_class' => 'coupon_discount_hint_tip' . $bk_type , 'span_value' => '...'
                                            , 'input_name' => 'coupon_discount_hint_hint'     . $bk_type , 'input_data' => '...'
                                ) );

            }

            return $blank;
        }



              // Delete all expire coupons
              function delete_expire_coupons() {
                 global $wpdb;
                 $wpbc_bdtb_coupons = $wpdb->prefix . "booking_coupons";
                 $sql = "DELETE FROM $wpbc_bdtb_coupons WHERE expiration_date < CURDATE() ";
                 if ( false === $wpdb->query( $sql ) ){
                       echo '<div class="error_message ajax_message textleft" style="font-size:12px;font-weight:600;">';
                       debuge_error('Error during deleting from DB coupon' ,__FILE__,__LINE__); echo  '</div>';
                 }
              }


              // Get coupons for specific resource
              function get_coupons_for_this_resource($my_bk_type_id=''){
                  global $wpdb;
                  $wpbc_bdtb_coupons = $wpdb->prefix . "booking_coupons";
                  $sql = "SELECT * FROM $wpbc_bdtb_coupons WHERE expiration_date >= CURDATE()";
                  $result = $wpdb->get_results( $sql .  " AND (support_bk_types='all' OR support_bk_types LIKE '%,".$my_bk_type_id.",%')" );
                  return $result;
              }

 // </editor-fold>


//<editor-fold defaultstate="collapsed" desc=" S E A R C H  ">


    function show_booking_search_results( $bk_custom_fields = array() ){

        ////////////////////////////////////////////////////////////////////////
        // Prepare parameters for search                                        //FixIn: 6.0.1.1
        ////////////////////////////////////////////////////////////////////////

        $booking_cache_content = get_bk_option( 'booking_cache_content');
        if ( ( empty($booking_cache_content) ) || ( $this->is_booking_search_cache_expire() ) ) {
            $this->regenerate_booking_search_cache();
            $booking_cache_content = get_bk_option( 'booking_cache_content');
        }
        if ( is_serialized( $booking_cache_content ) )
            $booking_cache_content = unserialize( $booking_cache_content );

        if ( ! empty( $_REQUEST[ 'bk_users' ] ) ) $sql_req_where = ' users IN (' . $_REQUEST['bk_users'] . ') ';    //FixIn:6.1.0.3
        else                                      $sql_req_where = ' 1=1 ' ;

        // ID of booking resources.
        $booking_types1      = $this->get_booking_types( 0, $sql_req_where );
        $booking_types2      = $this->get_booking_types_hierarhy( $booking_types1 );
        $booking_types       = $this->get_booking_types_hierarhy_linear( $booking_types2 );

        // Get only parents and single booking resources.
        $parents_or_single = $this->get_booking_types_hierarhy( $booking_types1 );

        ////////////////////////////////////////////////////////////////////////
        // Search
        ////////////////////////////////////////////////////////////////////////

	    //FixIn: 8.3.3.99
		ob_start();

        $search_availability = new WPBC_Search_Availability();

        // Set parameters
        $search_availability->set_custom_fields( $bk_custom_fields );
        $search_availability->set_booking_types( $booking_types );
        $search_availability->set_parents_or_single( $parents_or_single );
        $search_availability->set_cache_content( $booking_cache_content );
        $search_availability->define_parameters();

        // Search
        $search_availability->searching();

        //FixIn: 8.3.3.99

		$search_reults = ob_get_contents();
		ob_end_clean();

		return $search_reults;

    }



    function wpdev_ajax_booking_search( $bk_custom_fields = array() ){

		//FixIn: 8.3.3.99
		$search_results_to_show = $this->show_booking_search_results( $bk_custom_fields );
        echo $search_results_to_show;

    }


    function wpdev_get_booking_search_results($search_results, $attr){


        if ( isset($_GET['check_in']) )  { $_REQUEST['bk_check_in'] = $_GET['check_in']; }
        if ( isset($_GET['check_out']) ) { $_REQUEST['bk_check_out'] = $_GET['check_out']; }
        if ( isset($_GET['visitors']) )  { $_REQUEST['bk_visitors'] = $_GET['visitors']; }
        if ( isset($_GET['category']) )  { $_REQUEST['bk_category'] = $_GET['category']; }
        if ( isset($_GET['tag']) )       { $_REQUEST['bk_tag'] = $_GET['tag']; }
        if ( isset($_GET['bk_users']) )  { $_REQUEST['bk_users'] = $_GET['bk_users']; }

        if ( isset($_GET['bk_no_results_title']) )  { $_REQUEST['bk_no_results_title'] = $_GET['bk_no_results_title']; }
        if ( isset($_GET['bk_search_results_title']) )  { $_REQUEST['bk_search_results_title'] = $_GET['bk_search_results_title']; }

        if ( isset($_GET['additional_search']) )  { $_REQUEST['additional_search'] = $_GET['additional_search']; }  //FixIn: 6.0.1.1

        // Custom fields in the search  request ////
        $bk_custom_fields = array();
        foreach ($_REQUEST as $key => $value) {
           if ( (!empty($value))  &&  (strpos($key, 'booking_') === 0) ) {
               $bk_custom_fields[$key] = $value;
           }
        }


        $search_results_to_show = $this->show_booking_search_results( $bk_custom_fields );

        //FixIn: 8.3.3.99
        return $search_results_to_show;

    }

    // Get Search form results
    function wpdev_get_booking_search_form($search_form, $attr){ global $wpdb;

        $searchresults = false;
        $noresultstitle = $searchresultstitle = '';
        if (! empty($attr)) {
            if (isset($attr['searchresults'])) {
                $searchresults = $attr['searchresults'];
                $searchresults =  apply_bk_filter('wpdev_check_for_active_language', $searchresults );

            }
            if (isset($attr['searchresultstitle'])) {
                $searchresultstitle = $attr['searchresultstitle'];
                $searchresultstitle =  apply_bk_filter('wpdev_check_for_active_language', $searchresultstitle );
            }
            if (isset($attr['noresultstitle'])) {
                $noresultstitle = $attr['noresultstitle'];
                $noresultstitle =  apply_bk_filter('wpdev_check_for_active_language', $noresultstitle );
            }
        }

        //FixIn: 8.3.3.99
        ob_start();

            ?>
<style type="text/css">
    #datepick-div .datepick-header {
           width: 172px !important;
    }
    #datepick-div {
        -border-radius: 3px;
        -box-shadow: 0 0 2px #888888;
        -webkit-border-radius: 3px;
        -webkit-box-shadow: 0 0 2px #888888;
        -moz-border-radius: 3px;
        -moz-box-shadow: 0 0 2px #888888;
        width: 172px !important;
        z-index: 2147483647;
    }
    #datepick-div .datepick .datepick-days-cell a{
        font-size: 12px;
    }
    #datepick-div table.datepick tr td {
        border-top: 0 none !important;
        line-height: 24px;
        padding: 0 !important;
        width: 24px;
    }
    #datepick-div .datepick-control {
        font-size: 10px;
        text-align: center;
    }

</style>
<script type="text/javascript" >
var search_emty_days_warning = '<?php echo esc_js(__('Please select check-in and check-out days!' ,'booking')); ?>';

function getMinRangeDaysSelections(){
   if (bk_days_selection_mode == 'dynamic')    return  bk_2clicks_mode_days_min;
   if (bk_days_selection_mode == 'fixed')      return  bk_1click_mode_days_num;
   return 0;
}

function selectCheckInDay(date) {

    // Check Start week day selection for range days                            //FixIn: 6.1.1.2
    var selected_check_in_date = document.getElementById('booking_search_check_in').value.split('-');
    var date_to_check = new Date();
    date_to_check.setFullYear(    parseInt( selected_check_in_date[0] )
                                , ( parseInt( selected_check_in_date[1] ) - 1 )
                                , parseInt( selected_check_in_date[2] )
                            );
    if (  ( bk_days_selection_mode == 'fixed' ) && ( bk_1click_mode_days_start != -1 )  ){

        var startDay = getAbsClosestValue(date_to_check.getDay(), bk_1click_mode_days_start);
        date_to_check.setDate( date_to_check.getDate() -  ( date_to_check.getDay() - startDay )  );
        var string_date = date_to_check.getFullYear() ;
        string_date += '-' ;
        string_date += ( ( date_to_check.getMonth() + 1 ) < 10 ) ? '0' + ( date_to_check.getMonth() + 1 ) : ( date_to_check.getMonth() + 1 ) ;
        string_date += '-' ;
        string_date += ( ( date_to_check.getDate() ) < 10 ) ? '0' + ( date_to_check.getDate() ) : ( date_to_check.getDate() )
        jQuery('#booking_search_check_in').val( string_date );
    }
    if (  ( bk_days_selection_mode == 'dynamic' ) && ( bk_2clicks_mode_days_start != -1 )  ){

        var startDay = getAbsClosestValue(date_to_check.getDay(), bk_2clicks_mode_days_start);
        date_to_check.setDate( date_to_check.getDate() -  ( date_to_check.getDay() - startDay )  );
        var string_date = date_to_check.getFullYear() ;
        string_date += '-' ;
        string_date += ( ( date_to_check.getMonth() + 1 ) < 10 ) ? '0' + ( date_to_check.getMonth() + 1 ) : ( date_to_check.getMonth() + 1 ) ;
        string_date += '-' ;
        string_date += ( ( date_to_check.getDate() ) < 10 ) ? '0' + ( date_to_check.getDate() ) : ( date_to_check.getDate() )
        jQuery('#booking_search_check_in').val( string_date );
    }


    if (document.getElementById('booking_search_check_out') != null) {

       var start_bk_month_4_check_out = document.getElementById('booking_search_check_in').value.split('-');
       var myDate = new Date();
       myDate.setFullYear( (1*start_bk_month_4_check_out[0]+0), (1*start_bk_month_4_check_out[1]-1) ,  ( (1*start_bk_month_4_check_out[2] ) ) );
       var days_interval = getMinRangeDaysSelections();
       if (days_interval>0) days_interval--;
       myDate.setDate(myDate.getDate() + days_interval );
       var my_date = myDate.getDate() ; if (my_date < 10 ) my_date = '0' + my_date;

       var my_month = (myDate.getMonth()+1); if (my_month < 10 ) my_month = '0' + my_month;
       document.getElementById('booking_search_check_out').value = myDate.getFullYear() + '-' + my_month + '-' + my_date ;
   }

}

function setDaysForCheckOut(date){

   var class_day = (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();
   var additional_class = 'date_available ';

   for (var i=0; i<user_unavilable_days.length;i++) {
       if (date.getDay()==user_unavilable_days[i])   return [false, 'cal4date-' + class_day +' date_user_unavailable' ];
   }

   var my_test_date = new Date();
   my_test_date.setFullYear(wpdev_bk_today[0],(wpdev_bk_today[1]-1), wpdev_bk_today[2] ,0,0,0); //Get today
   if ( (days_between( date, my_test_date)+1) < block_some_dates_from_today ) return [false, 'cal4date-' + class_day +' date_user_unavailable'];


   if ( (document.getElementById('booking_search_check_in') != null) && (document.getElementById('booking_search_check_in').value != '') ) {

        var value = document.getElementById('booking_search_check_in').value;
        var year_m_d = value.split("-");
        var checkInDate = new Date();
        checkInDate.setFullYear( year_m_d[0], (year_m_d[1]-1) , (year_m_d[2]-1) );
        var days_interval = getMinRangeDaysSelections();
        if (days_interval>0) days_interval--;
        checkInDate.setDate(checkInDate.getDate() + days_interval);

        if(checkInDate <= date ) {
           return [true, 'cal4date-' + class_day + ' ' + additional_class+ ' ' ]; // Available
        } else                     return [false, ''];    // Unavailable

   } else return [true, 'cal4date-' + class_day + ' ' + additional_class+ ' ' ]; // Available
}


function applyCSStoDays4CheckInOut(date ){
   var class_day = (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();
   var additional_class = 'date_available ';

   for (var i=0; i<user_unavilable_days.length;i++) {
       if (date.getDay()==user_unavilable_days[i])   return [false, 'cal4date-' + class_day +' date_user_unavailable' ];
   }

   var my_test_date = new Date();
   my_test_date.setFullYear(wpdev_bk_today[0],(wpdev_bk_today[1]-1), wpdev_bk_today[2] ,0,0,0); //Get today
   if ( (days_between( date, my_test_date)+1) < block_some_dates_from_today ) return [false, 'cal4date-' + class_day +' date_user_unavailable'];

   return [true, 'cal4date-' + class_day + ' ' + additional_class+ ' ' ]; // Available
}

jQuery(document).ready( function(){

    jQuery('#booking_search_check_in').datepick(
        {   onSelect: selectCheckInDay,
            beforeShowDay: applyCSStoDays4CheckInOut,
            showOn: 'focus',
            multiSelect: 0,
            numberOfMonths: 1,
            stepMonths: 1,
            prevText: '&laquo;',
            nextText: '&raquo;',
            dateFormat: 'yy-mm-dd',
            changeMonth: false,
            changeYear: false,
            minDate: 0, maxDate: booking_max_monthes_in_calendar, //'1Y',
            showStatus: false,
            multiSeparator: ', ',
            closeAtTop: false,
            firstDay:<?php echo get_bk_option( 'booking_start_day_weeek' ); ?>,
            gotoCurrent: false,
            hideIfNoPrevNext:true,
            useThemeRoller :false,
            mandatory: true/**/,
            _mainDivId:  ['datepick-div', 'ui-datepicker-div','widget_wpdev_booking']
            <?php
            if (! empty($_GET['check_in'])) {
                echo ", defaultDate: '".$_GET['check_in']."', showDefault:true";
            } ?>
        }
    );
    jQuery('#booking_search_check_out').datepick(
        {   beforeShowDay: setDaysForCheckOut,
            showOn: 'focus',
            multiSelect: 0,
            numberOfMonths: 1,
            stepMonths: 1,
            prevText: '&laquo;',
            nextText: '&raquo;',
            dateFormat: 'yy-mm-dd',
            changeMonth: false,
            changeYear: false,
            minDate: 0, maxDate: booking_max_monthes_in_calendar, //'1Y',
            showStatus: false,
            multiSeparator: ', ',
            closeAtTop: false,
            firstDay:<?php echo get_bk_option( 'booking_start_day_weeek' ); ?>,
            gotoCurrent: false,
            hideIfNoPrevNext:true,
            useThemeRoller :false,
            mandatory: true
            <?php
            if (! empty($_GET['check_out'])) {
                echo ", defaultDate: '".$_GET['check_out']."', showDefault:true";
            } ?>
        }
    );
});
</script>
            <?php

		//FixIn: 8.3.3.99
		$search_css_js = ob_get_contents();
		ob_end_clean();

        // Get   shortcode   parameters ////////////////////////////////////
        //if ( isset( $attr['param'] ) )   { $my_boook_count = $attr['param'];  }

        $booking_search_form_show = get_bk_option( 'booking_search_form_show');
        $booking_search_form_show =  apply_bk_filter('wpdev_check_for_active_language', $booking_search_form_show );


        $booking_search_form_show = str_replace( '[search_category]',
                  '<input type="text" size="10" value="" name="category" id="booking_search_category" >',
                                                   $booking_search_form_show);

        $booking_search_form_show = str_replace( '[search_tag]',
                  '<input type="text" size="10" value="" name="tag" id="booking_search_tag" >',
                                                   $booking_search_form_show);


	    //FixIn: 8.2.1.4
        $booking_search_form_show = str_replace( '[search_check_in]',
                  '<input type="text" size="10" value="" name="check_in" id="booking_search_check_in" placeholder="' . esc_js( date_i18n('Y-m-d') ) . '">',
                                                   $booking_search_form_show);
        $booking_search_form_show = str_replace( '[search_check_out]',
                  '<input type="text" size="10" value=""  name="check_out"  id="booking_search_check_out" placeholder="' . esc_js( date_i18n('Y-m-d', strtotime( '+1 day' ) ) ) . '">',
                                                   $booking_search_form_show);


        if (isset($attr['users'])) {
            $booking_search_form_show .=  '<input type="hidden" size="10" value="'.$attr['users'].'"   name="bk_users"  id="booking_bk_users">';
        }

        $booking_search_form_show .=  '<input type="hidden" value="'.$noresultstitle.'"   name="bk_no_results_title"  id="bk_no_results_title">';
        $booking_search_form_show .=  '<input type="hidden"  value="'.$searchresultstitle.'"   name="bk_search_results_title"  id="bk_search_results_title">';

        //FixIn: 6.0.1.1
        ////////////////////////////////////////////////////////////////////////
        $search_shortcode = 'search_visitors';
        $find_search_visitors = preg_match_all('/\['.$search_shortcode.'[^\]]*\]/', $booking_search_form_show, $found_matches  );

        if ( count($found_matches) > 0 )
            foreach ( $found_matches[0] as $key => $found_shortcode ) {

                $found_shortcode_params = str_replace( array( '[' . $search_shortcode , ']' ), '', $found_shortcode );

                $found_shortcode_params = trim( $found_shortcode_params );

                if ( empty( $found_shortcode_params ) ) $found_shortcode_params = array( "1", "2", "3", "4", "5", "6" );
                else                                    $found_shortcode_params = explode( ' ', $found_shortcode_params );

                $code_to_insert = "<select style='width:50px;'  name='visitors'>";

                foreach ( $found_shortcode_params as $v ) {

                    $v = str_replace( array( "'", '"' ), '', $v );

                    $code_to_insert .= "<option value='{$v}' " . selected( isset( $_GET['visitors'] ) ? $_GET['visitors'] : '', $v, false  ) . ">{$v}</option>";
                }

                $code_to_insert .= "</select>";

                $booking_search_form_show = str_replace( $found_shortcode, $code_to_insert, $booking_search_form_show );
            }
        ////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////
        $search_shortcode = 'additional_search';
        $find_search_visitors = preg_match_all( '/\[' . $search_shortcode . '[^\]]*\]/', $booking_search_form_show, $found_matches );

        if ( count($found_matches) > 0 )
            foreach ( $found_matches[0] as $key => $found_shortcode ) {

                $found_shortcode_param = str_replace( array( '[' . $search_shortcode , ']' ), '', $found_shortcode );

                $found_shortcode_param = trim( $found_shortcode_param );

                if ( empty( $found_shortcode_param ) )  $found_shortcode_param = "2";
                else                                    $found_shortcode_param = str_replace( array( "'", '"' ), '', $found_shortcode_param );

                $code_to_insert = "<input type='checkbox' name='additional_search' value='{$found_shortcode_param}' "
                                    . checked( isset( $_GET['additional_search'] ) ? $_GET['additional_search'] : '', $found_shortcode_param, false  )
                                    . "/>";

                $booking_search_form_show = str_replace( $found_shortcode, $code_to_insert, $booking_search_form_show );
            }
        ////////////////////////////////////////////////////////////////////////
        //FixIn: 6.0.1.1


        if ($searchresults === false) {

            $wpbc_ajax_search_nonce = wp_nonce_field('BOOKING_SEARCH',  "wpbc_search_nonce" ,  true , false );
            $booking_search_form_show = str_replace( '[search_button]'
                                                     , $wpbc_ajax_search_nonce .
                                                            '<input type="button" onclick="searchFormClck(this.form, \''.
                                                            wpbc_get_booking_locale(). '\');" value="'.__('Search' ,'booking').'" class="search_booking btn">'
                                                     , $booking_search_form_show);

            $search_form = '<div  id="booking_search_form" class="booking_form_div0 booking_search_form">
                    <form name="booking_search_form" action="" method="post">'.
                         $booking_search_form_show .
                        '<div style="clear:both;"></div>
                    </form>
                </div>
                <div id="booking_search_ajax"></div>
                <div id="booking_search_results"></div>';
        } else {
            $booking_search_form_show = str_replace( '[search_button]',
                      '<input type="submit" onclick="if ( (this.form.check_in.value == \'\') || (this.form.check_out.value == \'\') ) { alert(search_emty_days_warning); return false; }" " value="'.__('Search' ,'booking').'" class="search_booking btn">', $booking_search_form_show);

            $search_form = '<div  id="booking_search_form" class="booking_form_div0 booking_search_form">
                    <form name="booking_search_form" action="'.$searchresults.'" method="get">'.
                         $booking_search_form_show .
                        '<div style="clear:both;"></div>
                    </form>
                </div>';
        }

        $search_form = apply_filters( 'wpbc_search_form', $search_form );												//FixIn: 8.1.2.1

        return $search_css_js . $search_form;
    }


              // Generate NEW booking search cache
              function regenerate_booking_search_cache(){

                        // wp_cache_flush(); //FixIn: 5.4.5.10
                        $available_booking_resources = array();
                        global $wpdb;
                        $sql = "SELECT ID, post_title, guid, post_content, post_excerpt
                                FROM {$wpdb->posts}
                                WHERE post_status = 'publish' AND ( post_type != 'revision' ) AND post_content LIKE '%[booking %'";
                        $postss = $wpdb->get_results($sql);
//debuge( count($postss) );
                        if( !empty($postss))
                          foreach ($postss as $value) {

                              $post_id = $value->ID;

                              $post_custom_fields = array();
                              $post_meta = get_post_meta($post_id, '' , false ) ;

                              foreach ($post_meta as $meta_key=>$meta_value) {
                                  if (strpos($meta_key, 'booking_') === 0 ) {
                                      $post_custom_fields[$meta_key] = $meta_value;
                                  }
                              }
                              $value->custom_fields = $post_custom_fields;
//debuge($value );
                              $image_src = false;
                              if ( 	$post_id &&
                                    function_exists('has_post_thumbnail') &&
                                    has_post_thumbnail( $post_id ) &&
                                    ($image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'post-thumbnail' ) )
                                 )
                                  {
                                      if (count($image)>2) {
                                          $image_src = $image[0];
                                          $image_w   = $image[1];
                                          $image_h   = $image[2];
                                      }
                                  }


                              $shortcode_start   = strpos($value->post_content,     '[booking ');
                              $shortcode_end     = strpos($value->post_content, ']',$shortcode_start);
                              $shortcode_content = substr($value->post_content, $shortcode_start+9, $shortcode_end - $shortcode_start-9);

                              $shortcode_content_attr = explode(' ', $shortcode_content);
                              $shortcode_attributes = array();

                              foreach ($shortcode_content_attr as $attr) {
                                  $attr_key_value = explode('=', $attr);
                                  if (count($attr_key_value)>1)
                                        $shortcode_attributes[ $attr_key_value[0] ] = $attr_key_value[1];
                              }
//debuge( $post_id, $shortcode_attributes );
                              if (! isset($shortcode_attributes['type'])) $shortcode_attributes['type']=1;
                              else $shortcode_attributes['type'] = intval( $shortcode_attributes['type'] );

                              $value->booking = $shortcode_attributes;
                              $value->booking_resource = $shortcode_attributes['type'];

                              if ($image_src !== false)
                                $value->picture = array($image_src,$image_w,$image_h);
                              else $value->picture = 0;


                              $us_id = apply_bk_filter('get_user_of_this_bk_resource', false, $value->booking_resource );
                              if ($us_id !== false) {
                                  $value->user = $us_id;
                              }

                              $categories = get_the_terms($post_id,'category');
                              $post_cats = array();
                              if (! empty($categories))
                                  foreach ($categories as $cat) {
                                      $post_cats[]=array('category'=>$cat->name, 'slug'=>$cat->slug, 'ID'=>$cat->term_id);
                                  }
                              $value->category = $post_cats;

                              $tags = get_the_terms($post_id,'post_tag');
                              $post_tags = array();
                              if (! empty($tags))
                                  foreach ($tags as $cat) {
                                      $post_tags[]=array('tag'=>$cat->name, 'slug'=>$cat->slug, 'ID'=>$cat->term_id);
                                  }
                              $value->tags = $post_tags;

                              $value->post_title    = htmlspecialchars($value->post_title, ENT_QUOTES);
                              $value->post_content  = '';   // htmlspecialchars($value->post_content, ENT_QUOTES);      //FixIn: 5.4.5.10
                              $value->post_excerpt  = htmlspecialchars($value->post_excerpt, ENT_QUOTES);

 //debuge( array('Page/Post ID:'=> $post_id, 'booking resources ID:'=>$shortcode_attributes['type'] ) );
                              if (! isset($available_booking_resources[$shortcode_attributes['type']])) {
                                  $available_booking_resources[$shortcode_attributes['type']] = $value;
                              }


                          }

                          $available_booking_resources_serilized = serialize($available_booking_resources);
                          update_bk_option( 'booking_cache_content' ,  $available_booking_resources_serilized );
                          update_bk_option( 'booking_cache_created' ,    date_i18n('Y-m-d H:i:s'   ) );

              }


              function is_booking_search_cache_expire(){


                  $previos = get_bk_option( 'booking_cache_created'     );
                  $previos = explode(' ',$previos);
                  $previos_time = explode(':',$previos[1]);
                  $previos_date = explode('-',$previos[0]);

                  $previos_sec = mktime($previos_time[0], $previos_time[1], $previos_time[2], $previos_date[1], $previos_date[2], $previos_date[0]);
                  $now_sec = time();                                            //FixIn: 6.2.3.6


                  $period =  get_bk_option( 'booking_cache_expiration'     );

                if (substr($period,-1,1) == 'd' ) {
                    $period = substr($period,0,-1);
                    $period = $period * 24 * 60 * 60;
                }

                if (substr($period,-1,1) == 'h' ) {
                    $period = substr($period,0,-1);
                    $period = $period * 60 * 60;
                }

                  $now_tm = explode(' ',date_i18n('Y-m-d H:i:s'   ) );
                  $now_tm_time = explode(':',$now_tm[1]);
                  $now_tm_date = explode('-',$now_tm[0]);
                  $now_tm_sec = mktime($now_tm_time[0], $now_tm_time[1], $now_tm_time[2], $now_tm_date[1], $now_tm_date[2], $now_tm_date[0]);

                if( ($previos_sec + $period ) > $now_tm_sec )
                    return  0;
                else return  1;
              }

 //</editor-fold>


// <editor-fold defaultstate="collapsed" desc=" C L I E N T   S I D E ">

//   C L I E N T   S I D E        //////////////////////////////////////////////////////////////////////////////////////////////////

    // JavaScript TOOLTIP - Availability  arrays with variables
    function show_availability_at_calendar($blank, $type_id, $max_days_count = 365 ) {

        if ($max_days_count == 365) {

            $max_monthes_in_calendar = get_bk_option( 'booking_max_monthes_in_calendar');

            if (strpos($max_monthes_in_calendar, 'm') !== false) {
                $max_days_count = str_replace('m', '', $max_monthes_in_calendar) * 31 +5;
            } else {
                $max_days_count = str_replace('y', '', $max_monthes_in_calendar) * 365+15 ;
            }

        }
        $start_script_code = '';

        $skip_booking_id = '';  // Id of booking to skip in calendar
        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type !== false) {
                $skip_booking_id = $my_booking_id_type[0];
            }
        }


        // Save at the Advnaced settings these 3 parameters
        $is_show_availability_in_tooltips =    get_bk_option( 'booking_is_show_availability_in_tooltips' );
        $highlight_availability_word      =    get_bk_option( 'booking_highlight_availability_word');
        $highlight_availability_word      =  apply_bk_filter('wpdev_check_for_active_language', $highlight_availability_word );

        $is_bookings_depends_from_selection_of_number_of_visitors = get_bk_option( 'booking_is_use_visitors_number_for_availability');

        global $wpdb;
        if (get_bk_option( 'booking_is_show_pending_days_as_available') == 'On')
             $sql_req = $this->get_sql_bk_dates_for_all_resources('', $type_id, '1',  $skip_booking_id) ;
        else $sql_req = $this->get_sql_bk_dates_for_all_resources('', $type_id, 'all',  $skip_booking_id) ;
        $dates_approve   = $wpdb->get_results( $sql_req );


        $busy_dates = array();          // Busy dates and booking ID as values for each day
        $busy_dates_bk_type = array();  // Busy dates and booking TYPE ID as values for each day

        $check_in_dates  = array();     // Number of  Bookings with Check - In  Date for this specific date
        $check_out_dates = array();     // Number of  Bookings with Check - Out Date for this specific date
        $is_check_in_day_approved  = array();     // Last Status of Check - In  day  1 - approved, 0 - pending
        $is_check_out_day_approved = array();     // Last Status of Check - Out day  1 - approved, 0 - pending

        $temp_time_checking_arr = array();

        // Get DAYS Array with bookings ID inside of each day. So COUNT of day will be number of booked childs
        foreach ($dates_approve as $date_object) {
            $date_without_time = explode(' ', $date_object->booking_date);
            $date_only_time    = $date_without_time[1];
            $date_without_time = $date_without_time[0];

            // Show the Cehck In/Out date as available for the booking resources with  capcity > 1 ///////////////////////////////////////////
            if ( (get_bk_option( 'booking_range_selection_time_is_active')  == 'On') &&
//                 (get_bk_option( 'booking_check_out_available_for_parents') == 'On') &&
                 ( substr($date_only_time,-2) == '02') )  {

                if ( isset( $check_out_dates[ $date_without_time ] ) )
                     $check_out_dates[ $date_without_time ][] = $date_object->type ;                            // $check_out_dates[ $date_without_time ] + 1;
                else $check_out_dates[ $date_without_time ]   = array( $date_object->type );                    // 1

                $is_check_out_day_approved[ $date_without_time ] = $date_object->approved ;

                continue;
            }
            if ( (get_bk_option( 'booking_range_selection_time_is_active') == 'On') &&
//                 (get_bk_option( 'booking_check_in_available_for_parents') == 'On') &&
                 ( substr($date_only_time,-2) == '01') )  {

                if ( isset( $check_in_dates[ $date_without_time ] ) )
                     $check_in_dates[ $date_without_time ][] = $date_object->type ;                         // $check_in_dates[ $date_without_time ] + 1;
                else $check_in_dates[ $date_without_time ]   = array( $date_object->type );                 // 1

                $is_check_in_day_approved[ $date_without_time ] = $date_object->approved ;

                continue;
            } /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            if (!isset( $busy_dates[ $date_without_time ] )) {
                $temp_time_checking_arr[$date_without_time][$date_object->booking_id] = $date_only_time; // For checking single day selection
                $busy_dates[ $date_without_time ] = array($date_object->booking_id);

                if (! empty($date_object->date_res_type)) $busy_dates_bk_type[ $date_without_time ] = array($date_object->date_res_type);
                else                                      $busy_dates_bk_type[ $date_without_time ] = array($date_object->type);


            } else {

                if (   ( isset($temp_time_checking_arr[$date_without_time][$date_object->booking_id])  ) &&
                       (  $temp_time_checking_arr[$date_without_time][$date_object->booking_id]  != $date_only_time )
                   ){
                    // Skip Here is situation, when same booking at the same day and in dif time, so skip it, we are leave only start date
                } else {
                    $busy_dates[ $date_without_time ][] = $date_object->booking_id ;
                    $temp_time_checking_arr[$date_without_time][$date_object->booking_id] = $date_only_time;

                    if (! empty($date_object->date_res_type)) $busy_dates_bk_type[ $date_without_time ][] = $date_object->date_res_type ;
                    else                                      $busy_dates_bk_type[ $date_without_time ][] = $date_object->type ;

                }
            }
        }

        $max_visit_std         = $this->get_max_available_items_for_resource($type_id);
        $is_availability_based_on_items_not_visitors = true;

        $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');
        $availability_based_on_visitors   = get_bk_option( 'booking_availability_based_on');
        if ($is_use_visitors_number_for_availability == 'On')
            if ($availability_based_on_visitors == 'visitors')
                $is_availability_based_on_items_not_visitors = false;
        $max_visitors_in_bk_res = $this->get_max_visitors_for_bk_resources($type_id);
        $max_visitors_in_bk_res_summ=$this->get_summ_max_visitors_for_bk_resources($type_id);


        if ( ($is_show_availability_in_tooltips !== 'On')  )  $start_script_code .= ' is_show_availability_in_tooltips = false; ';
        else                                                  $start_script_code .= ' is_show_availability_in_tooltips = true; ';

        $start_script_code .= " highlight_availability_word =  '". esc_js($highlight_availability_word) .  " '; ";

        $start_script_code .= "  availability_per_day[{$type_id}] = [];  ";
        $start_script_code .= "  wpbc_check_in_dates[{$type_id}] = [];  ";
        $start_script_code .= "  wpbc_check_out_dates[{$type_id}] = [];  ";
        $start_script_code .= "  wpbc_check_in_out_closed_dates[{$type_id}] = [];  ";

        $my_day =  date('m.d.Y' );          // Start days from TODAY
        $type_id_childs =  $this->get_booking_types($type_id);          // Get ID of the all childs elements of this parent resource.

        if (count($type_id_childs)<=1)
            $is_single = true;
        else
            $is_single = false;


        $cached_season_filters = array();
        foreach ($type_id_childs as $bk_type_id_child) {
            $cached_season_filters[ $bk_type_id_child->id ] = apply_bk_filter('get_available_days', $bk_type_id_child->id );
        }

        for ($i = 0; $i < $max_days_count; $i++) {

            $my_day_arr = explode('.',$my_day);

            $day0 = $day = ($my_day_arr[1]+0);
            $month0 = $month= ($my_day_arr[0]+0);
            $year0 = $year = ($my_day_arr[2]+0);

            if  ($day< 10) $day0 = '0' . $day;
            if  ($month< 10) $month0 = '0' . $month;

            $my_day_tag  =  $month . '-' . $day . '-' . $year ;
            $my_day_tag0 =  $month . '-' . $day0 .'-' . $year0 ;

            // Set rechecking availability based on the season filters of the booking resources:
            $search_date = $year . '-' . $month0 . '-' . $day0 ;

            foreach ($type_id_childs as $bk_type_id_child) {                // Loop in IDs
                if ( $bk_type_id_child->parent != 0 ) {}
                $bk_type_id_child = $bk_type_id_child->id;

                $is_date_available = is_this_day_available_on_season_filters( $search_date, $bk_type_id_child, $cached_season_filters[ $bk_type_id_child ] );    // Get availability

                if (! $is_date_available) {

                    if (!isset( $busy_dates[ $search_date ] )) {
                        $busy_dates[ $search_date ] = array('filter');
                    } else {
                        $busy_dates[ $search_date ][] = 'filter';
                    }

                    if (!isset( $busy_dates_bk_type[ $search_date ] )) {
                        $busy_dates_bk_type[ $search_date ] = array($bk_type_id_child);
                    } else {
                        $busy_dates_bk_type[ $search_date ][] = $bk_type_id_child ;
                    }

                }
            }

            																											//FixIn: 8.1.2.10	- section-start
			$booking_is_days_always_available = get_bk_option( 'booking_is_days_always_available' );
			// if ( in_array( $type_id, array( '12', '15', '17' ) ) ) $booking_is_days_always_available = 'On';     // Set  dates in calendar always available only  for specific resources with specific ID
			if ( $booking_is_days_always_available == 'On' ) {
				// No Booked days
				if ( $is_availability_based_on_items_not_visitors ) {
					$my_max_visit = $max_visit_std;
				} else {
					$my_max_visit = $max_visitors_in_bk_res_summ;
				}
			} else {																									//FixIn: 8.1.2.10	- section-end
				if ( $is_availability_based_on_items_not_visitors ) { // Calculate availability based on ITEMS

					if ( isset( $busy_dates[ $year . '-' . $month0 . '-' . $day0 ] ) ) {
						$my_max_visit = $max_visit_std - count( $busy_dates[ $year . '-' . $month0 . '-' . $day0 ] );
					} else {
						$my_max_visit = $max_visit_std;
					}

				} else {                                             // Calculate availability based on VISITORS

					if ( isset( $busy_dates_bk_type[ $year . '-' . $month0 . '-' . $day0 ] ) ) {

						if ( $is_single ) { // For single bk res
							$my_max_visit = $max_visitors_in_bk_res_summ;
							if ( isset( $temp_time_checking_arr[ $year . '-' . $month0 . '-' . $day0 ] ) ) {
								foreach ( $temp_time_checking_arr[ $year . '-' . $month0 . '-' . $day0 ] as $bk_id => $bk_time ) {
									$bk_time = explode( ':', $bk_time );
									if ( $bk_time[2] == '00' ) {
										$my_max_visit = 0;
									}
								}
							}

						} else {  // For Parent bk res
							$already_busy_visitors_summ = 0;
							foreach ( $busy_dates_bk_type[ $year . '-' . $month0 . '-' . $day0 ] as $busy_type_id ) {
								if ( isset( $max_visitors_in_bk_res[ $busy_type_id ] ) ) {
									$already_busy_visitors_summ += $max_visitors_in_bk_res[ $busy_type_id ];
								}
							}
							$my_max_visit = $max_visitors_in_bk_res_summ - $already_busy_visitors_summ;
						}
					} else {
						$my_max_visit = $max_visitors_in_bk_res_summ;
					}

				}
			}																											//FixIn: 8.1.2.10

            $start_script_code .= "  availability_per_day[". $type_id ."]['".$my_day_tag."'] = '".$my_max_visit."' ;  ";

            // check for the CLOSED days (where exist  check in and check out dates of the same Child resources
            $check_in_out_closed_dates = 0;
            if (  ( isset( $check_in_dates[ "{$year}-{$month0}-{$day0}" ] ) ) && ( isset( $check_out_dates[ "{$year}-{$month0}-{$day0}" ] ) )  ){

                $check_in_out_closed_dates = array_intersect($check_in_dates[ "{$year}-{$month0}-{$day0}" ], $check_out_dates[ "{$year}-{$month0}-{$day0}" ] );
                $check_in_out_closed_dates = count( $check_in_out_closed_dates );
                $start_script_code .= " wpbc_check_in_out_closed_dates[{$type_id}]['{$my_day_tag}'] = {$check_in_out_closed_dates}; ";
            }
            if ( isset( $check_in_dates[ "{$year}-{$month0}-{$day0}" ] ) ){
                $start_script_code .= " wpbc_check_in_dates[{$type_id}]['{$my_day_tag}'] = ["
                            . "[" . ( count( $check_in_dates[ "{$year}-{$month0}-{$day0}" ] ) - $check_in_out_closed_dates ) . "]"
                            . ',' .$is_check_in_day_approved[ "{$year}-{$month0}-{$day0}" ]
                            . "]; " ;
            }
            if ( isset( $check_out_dates[ "{$year}-{$month0}-{$day0}" ] ) ){
                $start_script_code .= " wpbc_check_out_dates[{$type_id}]['{$my_day_tag}'] = ["
                            . "[" . ( count( $check_out_dates[ "{$year}-{$month0}-{$day0}" ]  ) - $check_in_out_closed_dates ) . "]"
                            . ',' .$is_check_out_day_approved[ "{$year}-{$month0}-{$day0}" ]
                            . "]; " ;
            }


            $my_day =  date('m.d.Y' , mktime(0, 0, 0, $month, ($day+1), $year ));   // Next day
        }

        //$max_visitors_in_bk_res = $this->get_max_visitors_for_bk_resources($type_id);
        foreach ($max_visitors_in_bk_res as $key=>$value) {
            if(! empty($key))
             $start_script_code .= "  max_visitors_4_bk_res[". $key ."] = ".$value." ;  ";
        }
//debuge_speed();
        return $start_script_code;
    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" S U P P O R T     A D M I N     F u n c t i o n s ">

// S U P P O R T     A D M I N     F u n c t i o n s    ///////////////////////////////////////////////////////////////////////////////////

        // Just Get ALL booking types from DB
        function get_booking_types($booking_type_id = 0, $where = '') {
            global $wpdb;
            $additional_fields = '';

            if ($where === '') {
                $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
                $us_id = apply_bk_filter('get_user_of_this_bk_resource', false, $booking_type_id );
                if ($us_id !== false)
                    $where =  $wpdb->prepare( " users = %d " , $us_id );
            }

            if ($booking_type_id != 0 ) {

                $where1 = $wpdb->prepare( " WHERE ( booking_type_id = %d OR parent = %d ) ", $booking_type_id, $booking_type_id);

                if ($where != '')   $where = $where1 . ' AND ' . $where;
                else                $where = $where1;

            } else {
                if ($where != '') $where = ' WHERE ' . $where;
            }

            if ( class_exists('wpdev_bk_multiuser')) {  // If Business Large then get resources from that
                $additional_fields = ', users ';
            }

            $wpbc_sql = "SELECT booking_type_id as id, title, parent, prioritet, cost, visitors {$additional_fields}
                         FROM {$wpdb->prefix}bookingtypes {$where} ORDER BY parent, prioritet" ;

            $types_list = $wpdb->get_results( $wpbc_sql );
            return $types_list;
        }

                // Get hierarhy structure TREE of booking resources
                function get_booking_types_hierarhy($bk_types=array()) {

                    if ( count($bk_types)==0) $bk_types = $this->get_booking_types();

                    $res= array( );

                    foreach ($bk_types as $bt) {
                        if ( $bt->parent == '0' ) {
                            $res[$bt->id] = array( 'obj'=> $bt,  'child'=>array() , 'count'=>1 );
                        }
                    }

                    foreach ($bk_types as $bt) {
                        if ( $bt->parent != '0' ) {
                            if (! isset($res[$bt->parent]['child'][$bt->prioritet])) $res[$bt->parent]['child'][$bt->prioritet] = $bt;
                            else $res[$bt->parent]['child'][ 100* count($res[$bt->parent]['child']) ] = $bt;
                            $res[$bt->parent]['count'] = count($res[$bt->parent]['child'])+1;
                        }
                    }
                    return $res;
                }

                        // FUNCTION  FOR SETTINGS ////////////////////////////////////////////////////////////
                        // Get linear structure of resources from hierarhy for showing it at the settings page
                        function get_booking_types_hierarhy_linear($bk_types=array()) {
                            if ( count($bk_types)==0) $bk_types = $this->get_booking_types_hierarhy();

                            $res= array();

                            foreach ($bk_types as $bt) {
                                if (isset($bt['obj']))
                                    $res[] = array( 'obj' => $bt['obj'], 'count' => $bt['count'] );
                                foreach ($bt['child'] as $b) {
                                    $res[] = array( 'obj' => $b, 'count' => '1' );
                                }
                            }

                            return $res;
                        }


        // Get Maximum available of items for this resource. Based on capacity.
        function get_max_available_items_for_resource($bk_type) {
            $bk_types =  $this->get_booking_types($bk_type);
            $bk_types =  $this->get_booking_types_hierarhy($bk_types);
            if (isset($bk_types[$bk_type]))
                if (isset($bk_types[$bk_type]['count']))
                    $max_available_items = $bk_types[$bk_type]['count']  ;

            if (isset($max_available_items))
                return $max_available_items;
            else
                return 1;
        }


        // Get NUM of Visitors, which was filled at booking form, if USE VISITORS NUM is Active
        function get_num_visitors_from_form($formdata, $bktype){

            if (get_bk_option( 'booking_is_use_visitors_number_for_availability') == 'On')
                 $is_use_visitors_number_for_availability =  true;
            else $is_use_visitors_number_for_availability =  false;

            $visitors_number = 1;

            if ($is_use_visitors_number_for_availability) {
                if (isset($formdata)) {
                    $form_data =  get_form_content($formdata, $bktype) ;
                    if ( isset($form_data['visitors']) ) {
                        $visitors_number = $form_data['visitors'];
                    }
                }
                return $visitors_number;
            } else return 1;


        }


        // Get Array with ID of booking resources and MAX visitors for each of BK Resources
        function get_max_visitors_for_bk_resources($booking_type_id = 0){

                $bk_types = $this->get_booking_types($booking_type_id);
                $bk_types = $this->get_booking_types_hierarhy($bk_types);
                $bk_types = $this->get_booking_types_hierarhy_linear($bk_types);        // Get linear array sorted by Priority

                $max_visitors_for_bk_types = array();
                foreach ($bk_types as $value) {
                    if (isset($value['obj']->visitors))
                        $max_visitors_for_bk_types[  $value['obj']->id  ] = $value['obj']->visitors ;
                    else
                        $max_visitors_for_bk_types[  $value['obj']->id  ] = 1;
                }

                return $max_visitors_for_bk_types;
        }

        // Just MAX Number of visitors
        function get_summ_max_visitors_for_bk_resources($booking_type_id = 0){
            $max_visitors_in_bk_res = $this->get_max_visitors_for_bk_resources($booking_type_id);
            $max_visitors_in_bk_res_summ=0;
            foreach ($max_visitors_in_bk_res as $value_element) {
                $max_visitors_in_bk_res_summ += $value_element;
            }
            return $max_visitors_in_bk_res_summ;
        }
// </editor-fold>


// <editor-fold defaultstate="collapsed" desc="A d m i n   D A T E S    F u n c t i o n s">

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// A d m i n   D A T E S    F u n c t i o n s     ////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        //    Check at which child BK RES this Booking resource have to be  - SQL UPDATE resource
        //
        // Params: 'wpdev_booking_reupdate_bk_type_to_childs', $booking_id, $bktype, str_replace('|',',',$dates),  array($start_time, $end_time )
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //      TODO: 2. May be We need to set for each item (child resource) maximum support of visitors
        function reupdate_bk_type_to_childs($booking_id, $bktype, $dates, $start_end_time_arr , $formdata, $skip_page_checking_for_updating = false  ) {

	        $booking_is_days_always_available = get_bk_option( 'booking_is_days_always_available' );					//FixIn: 8.1.2.10    - section-start
	        // if ( in_array( $type_id, array( '12', '15', '17' ) ) ) $booking_is_days_always_available = 'On';     // Set  dates in calendar always available only  for specific resources with specific ID
	        if ( $booking_is_days_always_available == 'On' ) {
		        return true;
	        }																											//FixIn: 8.1.2.10    - section-end

			$is_exit = false;
			// Hook for overwriting updating dates. Usually  used for do not allow to  make this updating.
			$is_exit = apply_filters( 'wpbc_is_reupdate_dates_to_child_resources', $is_exit, $booking_id, $bktype, $dates, $start_end_time_arr , $formdata, $skip_page_checking_for_updating );
			if ( true === $is_exit )
				return $is_exit;


            // Skip Reupdate is the Show Pending as available is activated.
            $is_show_pending_days_as_available                          = get_bk_option( 'booking_is_show_pending_days_as_available');
            if  ($is_show_pending_days_as_available == 'On')   return false;


            global $wpdb;
//debuge($booking_id, $bktype, $dates, $start_end_time_arr , $formdata );
//TODO: DEBUG
//REMOVE TODAT DEBUG
//$booking_id = 8;
//$bktype =2;
//$dates = '15.02.2011, 16.02.2011, 17.02.2011';
//$start_end_time_arr = array( array('00','00','00') , array('00','00','00') );
//$formdata = 'text^name2^fsdfs~text^secondname2^SDD~email^email2^email@server.com~text^address2^adress~text^city2^city~text^postcode2^post code~select-one^country2^GB~select-one^visitors2^3';
//debuge($booking_id, $bktype, $dates, $formdata );


            $bk_types =  $this->get_booking_types($bktype);                 // Get Hierarhy structure of BK Resource
            $bk_types =  $this->get_booking_types_hierarhy($bk_types);
            $max_available_items = 0;

            if (isset($bk_types[$bktype]))
                if (isset($bk_types[$bktype]['count']))
                    $max_available_items = $bk_types[$bktype]['count']  ;           // Max Childs count


            //Sort the Dates in the correct order:
            $my_dates = explode(',',$dates);
            $i=0;
            foreach ($my_dates as $md) { // Set in dates in such format: yyyy.mm.dd
                if ($md != '') {
                    $md = trim($md);
                    $md = explode('.',$md);
                    $my_dates[$i] = $md[2] . '.' . ( (intval($md[1])<10) ? ('0'.intval($md[1])) : $md[1] ) . '.' . ( (intval($md[0])<10) ? ('0'.intval($md[0])) : $md[0] ) ;
                    //$my_dates[$i] = $md[2] . '.' . $md[1] . '.' . $md[0] ;
                } else { unset($my_dates[$i]) ; } // If some dates is empty so remove it   // This situation can be if using several bk calendars and some calendars is not checked
                $i++;
            }
            sort($my_dates); // Sort dates


//debuge($my_dates);


            //if (count($dates)==1)   // We have only 1 day and DIF Time, so duplicate this one day
                //if ($start_end_time_arr[0]!=$start_end_time_arr[1])
                    //$dates[] = $dates[0]  ;
//debuge($bktype, $bk_types, $max_available_items);
//debuge($dates);

            $dates_new =  array();
            foreach ($my_dates as $d) {
                $d1 = explode('.',trim($d));
                $day =    $d1[2];
                $month =  $d1[1];
                $year =   $d1[0];
                $dates_new[] = intval($month) .'-'. intval($day) .'-'. intval($year)  ;// Month-Day-Year   Dates for normal using inside of this Funcion
            }
//REMOVE TODAT DEBUG debuge($dates_new);

            $my_page = 'client';                                            // Get a page
            if ( ! $skip_page_checking_for_updating ) {
                if ( wpbc_is_new_booking_page( 'HTTP_REFERER' ) )
                    $my_page = 'add';
                else if (  wpbc_is_bookings_page( 'HTTP_REFERER' ) )
                    $my_page = 'booking';
            }
        if ( isset($_SERVER['HTTP_REFERER'] ) )
            if ( strpos($_SERVER['HTTP_REFERER'],'parent_res') !== false  ) $my_page = 'client'; // admin add page and we add at parent res
        if ($my_page == 'add') if ( $this->check_if_bk_res_have_childs($bktype) ) { $my_page = 'client'; }
//REMOVE TODAT DEBUG debuge($my_page);

        if ( ($my_page == 'client')  ) // If client so go on

            if ($max_available_items > 1) {                                 // Make change only if we have some childs - capacity

               $visitors_number = $this->get_num_visitors_from_form($formdata, $bktype);     // Get NUM Visitors from bk form, if use visitors num for availability is active else return false
               $updated_type_id = $bktype;                                  // Bk TYPE ID
               $bk_types        = $this->get_booking_types_hierarhy_linear($bk_types);       // Get linear array sorted by Priority

//REMOVE TODAT DEBUG
//debuge($visitors_number, $dates_new, $bk_types);
//die;
//REMOVE TODAT DEBUG
//debuge('O - Get Matrix for Bk Types');
               // 0. Get Matrix for Bk Types with busy days for each types.
               // Example: [ [TYPE_ID] => [  [DATE]=>BK_ID, [10-23-2010]=>22  ], [5] => .....  ]
               /*     [1] => Array (
                            [1-12-2011] => 5
                            [1-13-2011] => 5
                            [1-14-2011] => 5
                            [1-17-2011] => 2
                            [1-18-2011] => 2 )
                      [6] => Array (
                            [1-13-2011] => 7
                            [1-14-2011] => 7
                            [1-15-2011] => 7
                            [1-18-2011] => 4
                            [1-19-2011] => 4 )
               /**/
               $bk_types_busy_dates_matrix = array();
               foreach ($bk_types as $res_obj) {
                   $r_id = $res_obj['obj']->id;
                   $bk_types_busy_dates_matrix[$r_id] = $this->get_all_reserved_day_for_bk_type('all', $r_id, $booking_id );
               }
//REMOVE TODAT DEBUG
//debuge('$bk_types_busy_dates_matrix',$bk_types_busy_dates_matrix);

// $dates_for_each_visitors - reduce the size of this archive based on visitors number of specific type


                // 0. Create INIT arrays for visitors:
                // Example: $is_this_visitor_setup  = [ [0]=false, [1]=false ...]
                //          $dates_for_each_visitors= [ [0]=>['11-20-2010'=>false, '11-20-2010'=>false], [1]=>['11-20-2010'=>false, '11-20-2010'=>false]...]
                $dates_for_each_visitors = array();
                $is_this_visitor_setup = array();
                for ($i = 0; $i < $visitors_number; $i++) {
                    $dates_for_each_visitors[$i]=array();
                    foreach ($dates_new as $selected_date) {
                        $dates_for_each_visitors[$i][$selected_date] = false;               // this date is not set up to some room (bk type)
                    }
                    $is_this_visitor_setup[$i]=false;                       // this visitor is not SETUP yet
                }
//REMOVE TODAT DEBUG
//debuge( '$is_this_visitor_setup, $dates_for_each_visitors, $visitors_number',  $is_this_visitor_setup, $dates_for_each_visitors, $visitors_number);



                // Get Max number of visitors for each booking type from linear array
                // Exmaple: [bk_ID] => MAX_VISITORS
                /*          [1] => 1
                            [6] => 1
                            [7] => 3
                            [8] => 2
                /**/
                $max_visitors_for_bk_types = array();
                foreach ($bk_types as $value) {
                    if (isset($value['obj']->visitors))
                        $max_visitors_for_bk_types[  $value['obj']->id  ] = $value['obj']->visitors ;
                    else
                        $max_visitors_for_bk_types[  $value['obj']->id  ] = 1;
                }

//REMOVE TODAT DEBUG
//debuge('$max_visitors_for_bk_types',$max_visitors_for_bk_types);
//die;
//debuge('$bk_types_busy_dates_matrix',$bk_types_busy_dates_matrix);
                // 1. Check availability of days in BK Resource, WITHOUT JUMPING ONE BOOKING TO DIF Resources
                $vis_num = 0;                                                     // Visitor NUMber
                foreach ($bk_types_busy_dates_matrix as $bk_type_id => $busy_dates) {       //Example: [ [5] => [  [DATE]=>BK_ID, [10-23-2010]=>22  ], .....  ]


                    if ($vis_num>= count($is_this_visitor_setup)) break;         //  All visitor -> Return
                    while ($is_this_visitor_setup[$vis_num] !== false) {          // Next visitor. This visitor is setuped.
                          $vis_num++;
                          if ($vis_num>= count($is_this_visitor_setup)) break;   //  All visitor -> Return
                    }
                    if ($vis_num>= count($is_this_visitor_setup)) break;         // All visitor -> Return



                    $is_some_dates_busy_in_this_type = false;               // Check all SELECTED dates  line inside of this TYPE (room)
                    foreach ( $dates_for_each_visitors[$vis_num] as $selected_date_for_visitor=>$is_day_setup ) {
                        if (isset($busy_dates[$selected_date_for_visitor]))  { $is_some_dates_busy_in_this_type = true ; break;  } // Some day  is busy, get next bk type
                    }

                    if ($is_some_dates_busy_in_this_type === false ) { // All days is FREE inside this type
                        $is_this_visitor_setup[$vis_num] = 1;                                 // This visitor is SETUPED
                        foreach ( $dates_for_each_visitors[$vis_num] as $selected_date_for_visitor=>$is_day_setup ) {
                            $dates_for_each_visitors[$vis_num][$selected_date_for_visitor] = $bk_type_id;

                            $bk_types_busy_dates_matrix[$bk_type_id][$selected_date_for_visitor] =  $booking_id; // MARK ALSO MATRIX
                        }

                        // Reduce the number of visitors based on visitor capacity for this booking resource (MAX VIS NUMBER)
                        $reduce_value_based_on_max_visitors = $max_visitors_for_bk_types[$bk_type_id] - 1 ;
                        for ($re = 0; $re < $reduce_value_based_on_max_visitors; $re++) {
                           // array_pop( $dates_for_each_visitors );          //decrese the number of visitors
                           // array_pop( $is_this_visitor_setup );
                            $vis_num++;                                                           // Next visitor
                            if ($vis_num>= count($is_this_visitor_setup)) break;
                            $is_this_visitor_setup[$vis_num] = 1;                                 // This visitor is SETUPED
                            $dates_for_each_visitors[$vis_num] = array();
                        }

                        $vis_num++;                                                           // Next visitor
                    }
                }
//array_pop( $dates_for_each_visitors );
//REMOVE TODAT DEBUG
//debuge($is_this_visitor_setup, $dates_for_each_visitors, $bk_types_busy_dates_matrix);
//REMOVE TODAT DEBUG
//die;



                // Continue Check availability of days in BK Resource, WITH JUMPING
                // (  One visitor can be start in one resource then go to other resource )
                while ($vis_num< count($is_this_visitor_setup)) {                    // Check if we proceed all visitors if not so go inside

                        if ($vis_num>= count($is_this_visitor_setup)) break;         // We are proceed all visitor, so return
                        while ($is_this_visitor_setup[$vis_num] !== false) {          // This visitor is setuped so get next one
                              $vis_num++;
                              if ($vis_num>= count($is_this_visitor_setup)) break;   // We are proceed all visitor, so return
                        }
                        if ($vis_num>= count($is_this_visitor_setup)) break;         // We are proceed all visitor, so return



                        foreach ( $dates_for_each_visitors[$vis_num] as $selected_date_for_visitor=>$is_day_setup ) {

                            foreach ($bk_types_busy_dates_matrix as $bk_type_id => $busy_dates) {  //Example: [ [5] => [  [DATE]=>BK_ID, [10-23-2010]=>22  ], .....  ]

                                if (! isset($busy_dates[$selected_date_for_visitor]))  { // DATE is FREE in This Resource

                                    if (isset($dates_for_each_visitors[$vis_num][$selected_date_for_visitor])) {

                                        $dates_for_each_visitors[$vis_num][$selected_date_for_visitor] = $bk_type_id;        // Set Room for selected day of visitor
                                        $bk_types_busy_dates_matrix[$bk_type_id][$selected_date_for_visitor] = $booking_id;  // MARK MATRIX

                                    }

                                    // Reduce the number of visitors based on visitor capacity for this booking resource (MAX VIS NUMBER)
                                    $reduce_value_based_on_max_visitors = $max_visitors_for_bk_types[$bk_type_id] - 1 ;
                                    for ($re = 1; $re <= $reduce_value_based_on_max_visitors; $re++) {

                                        if ( isset($dates_for_each_visitors[ $vis_num + $re ]) )  // Check if this visitor is exist
                                            if ( isset( $dates_for_each_visitors[ $vis_num + $re ][$selected_date_for_visitor] ) ) {  // Check if this date is exist
                                                //unset( $dates_for_each_visitors[ $vis_num + $re ][$selected_date_for_visitor] );     // Unset

                                                if ( ($vis_num + $re) >= count($is_this_visitor_setup)) break;
                                                $is_this_visitor_setup[$vis_num + $re] = 1;                                 // This visitor is SETUPED
                                                $dates_for_each_visitors[$vis_num + $re] = array();

                                            }
                                    }


                                    break; // Get next date of visitor
                                }

                            }
                        } // Process all days from visitor

                        $is_this_visitor_setup[$vis_num] = 1; // Mark this visitor as setuped and recheck below in loop this
                        foreach ( $dates_for_each_visitors[$vis_num] as $selected_date_for_visitor=>$is_day_setup ) {
                            if ($is_day_setup === false ) $is_this_visitor_setup[$vis_num] = false;
                        }

                        $vis_num++; // Get next visitor
                }


//debuge( $updated_type_id);
                    ////////////////////////////////////////////////////////
                    // MAKE UPDATE OF    DB
                    ////////////////////////////////////////////////////////

                    // Get default bk Resource  - Type   (  first visitor, first day type)
                if ( (count($dates_for_each_visitors) > 0 ) && (is_array($dates_for_each_visitors[0])) )
                    foreach ($dates_for_each_visitors[0] as $value) { if (!empty($value)) {$updated_type_id=$value;} break; }

//debuge($dates_for_each_visitors, $updated_type_id);
                   //  Updated ID with NEW - UPDATE Booking TABLE    with new bk. res. type
                   if ( $updated_type_id != $bktype ) {

                        // Fix the booking form ID of elements /////////////////////////////////////////////////////////////////
                        $formdata_new = '';
                        $formdata_array = explode('~',$formdata);
                        $formdata_array_count = count($formdata_array);
                        for ( $i=0 ; $i < $formdata_array_count ; $i++) {
                            $elemnts = explode('^',$formdata_array[$i]);

                            $type = $elemnts[0];
                            $element_name = $elemnts[1];
                            $value = $elemnts[2];
                            $value = str_replace("\\n", "\n", $value);          //FixIn: 6.1.1.7
                            if ( substr($element_name, -2 ) == '[]' )
                                $element_name = substr($element_name, 0, -1 * (strlen($bktype)+2) ) . $updated_type_id . '[]' ;  // Change bk RES. ID in elemnts of FORM
                            else
                                $element_name = substr($element_name, 0, -1 * strlen($bktype) ) . $updated_type_id  ;  // Change bk RES. ID in elemnts of FORM

                            if ($formdata_new!='') $formdata_new.= '~';
                            $formdata_new .= $type . '^' . $element_name . '^' . $value;
                        } ////////////////////////////////////////////////////////////////////////////////////////////////

                        // Update
                        $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.form = %s, bk.booking_type=%d WHERE bk.booking_id = %d ;"
                                , $formdata_new , $updated_type_id, $booking_id );

                        if ( false === $wpdb->query( $update_sql ) ){
                            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during updating exist booking type in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                            die();
                        }
                   }/////////////////////////////////////////////////////////////



                   // If we have situation with bookings in diferent resource so we are delete current booking and need to show error message. ////////
                   $booking_is_dissbale_booking_for_different_sub_resources = get_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources');
                   if( $booking_is_dissbale_booking_for_different_sub_resources == 'On') {

                           $is_dates_inside_one_resource = true;            // We will recheck if all days inside of one resource, or there is exist some jumping.
                           foreach ($dates_for_each_visitors as $vis_num => $array_dates_for_each_visitors) {
                               foreach ($array_dates_for_each_visitors as $k_day => $v_type_id) {
                                   $type_id_for_this_user = $v_type_id;
                                   break;
                               }
                               foreach ($array_dates_for_each_visitors as $k_day => $v_type_id) {
                                  if ($v_type_id != $type_id_for_this_user) {
                                      $is_dates_inside_one_resource = false;
//debuge( '$updated_type_id, $k_day,  $v_type_id,  $is_dates_inside_one_resource', $updated_type_id, $k_day,  $v_type_id,  $is_dates_inside_one_resource)                                      ;
                                      break;
                                  }
                               }
                           }

                           if ( ! $is_dates_inside_one_resource ) {

                               // Edit Process                                  //FixIn: 7.0.1.43
                               if ( strpos( $_SERVER['HTTP_REFERER'],'booking_hash') !== false  ) {
                                   ?> <script type="text/javascript">
                                        jQuery('#gateway_payment_forms<?php echo $bktype; ?>').hide();
                                        jQuery('.booking_summary').hide();
                                        //setTimeout( function(){ jQuery('#calendar_booking<?php echo $bktype; ?>').show(); }, 1000 );
                                        //setTimeout( function(){ jQuery('#booking_form_div<?php echo $bktype; ?>').show(); }, 1000 );
                                        jQuery('#booking_form_div<?php echo $bktype; ?> input[type=button]').prop("disabled", false);
                                        showMessageUnderElement( '#ajax_respond_insert<?php echo $bktype ?>' ,
                                                '<?php  echo html_entity_decode( esc_js( sprintf(__('Sorry, the reservation was not made because these days are already booked!!! %s (Its not possible to store this sequence of the dates into the one resource.) %s Please %srefresh%s the page and try other days.' ,'booking') ,'<br />','<br />','<a href="javascript:void(0)" onclick="javascript:location.reload();">','</a>') ) );
                                        ?>', '');


                                        // Scroll to the calendar
                                        setTimeout( function(){ makeScroll('#calendar_booking<?php echo $bktype ?>' ); }, 550);
                                    </script> <?php

                               } else {

	                                //FixIn: 8.3.3.1
								   	$auto_cancel_reason = sprintf(__('Sorry, the reservation was not made because these days are already booked!!! %s (Its not possible to store this sequence of the dates into the one resource.) %s Please %srefresh%s the page and try other days.' ,'booking') ,'<br />','<br />','','') ;
								   	wpbc_send_email_trash( $booking_id, 1, $auto_cancel_reason );

								    $update_sql =  $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.trash = 1, bk.remark = %s WHERE bk.booking_id = %d ", strip_tags( $auto_cancel_reason ), $booking_id );
                                    // if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}booking WHERE booking_id = %d ", $booking_id ) ) ){
                                    if ( false === $wpdb->query( $update_sql ) ){
									//FixIn: 8.3.3.1
                                          ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during booking dates cleaning in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                                         die();
                                    }
                                    echo ' ';
                                    ?> <script type="text/javascript">
                                              if ( type_of_thank_you_message == 'page' ) {
                                                   clearTimeout(timeoutID_of_thank_you_page);
                                              }
                                              //FixIn: 6.2.1.1
                                              jQuery('#gateway_payment_forms<?php echo $bktype; ?>').hide();
                                              //jQuery('#submiting<?php echo $bktype; ?>').html('<div class="wpdev-help-message alert alert-error" style="height:auto;width:90%;text-align:center;margin:1px auto;"><?php printf(__('Sorry, the reservation was not made because these days are already booked!!! %s (Its not possible to store this sequence of the dates into the one resource.) %s Please %srefresh%s the page and try other days.' ,'booking') ,'<br />','<br />','<a href="javascript:void(0)" onclick="javascript:location.reload();">','</a>'); ?></div>');
                                              jQuery('.booking_summary').hide();

                                             //FixIn: 7.0.1.43	//FixIn: 8.3.3.1
                                             showMessageUnderElement( '#date_booking<?php echo $bktype ?>' ,
                                                     '<?php  echo html_entity_decode( esc_js( sprintf(__('Sorry, the reservation was not made because these days are already booked!!! %s (Its not possible to store this sequence of the dates into the one resource.) %s Please %srefresh%s the page and try other days.' ,'booking') ,'<br />','<br />','<a href="javascript:void(0)" onclick="javascript:location.reload();">','</a>') ) );
                                             ?>', '');
                                             // Scroll to the calendar
                                             setTimeout( function(){ makeScroll('#calendar_booking<?php echo $bktype ?>' ); }, 550);
                                         </script>
                                    <?php
                                    exit;
                               }
                           }
                   }

                   // Update Dates: //FixIn: 8.3.3.1
                   // Firstly delete all dates, from Basic insert for future clean work
                   if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}bookingdates WHERE booking_id = %d ", $booking_id ) ) ){
                         ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during booking dates cleaning in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                        die();
                   }


                   // Now insert all new dates
                   $insert='';
                   $start_time = $start_end_time_arr[0];
                   $end_time   = $start_end_time_arr[1];
                    $is_approved_dates = '0';
                    $auto_approve_new_bookings_is_active       =  get_bk_option( 'booking_auto_approve_new_bookings_is_active' );
                    if ( trim($auto_approve_new_bookings_is_active) == 'On')
                        $is_approved_dates = '1';
//TODO fix here update process for $bk_type_for_date_init
//debuge($dates_for_each_visitors);
                   foreach ($dates_for_each_visitors as $vis_num => $value_dates) {

                       // We have selection only one day and times is diferent
                       if ( ( count($value_dates)==1 ) && ( $start_time != $end_time ) ) $value_dates[]='previos_day';


                       $i=0;
//debuge($value_dates);
                       foreach ($value_dates as $my_date_init => $bk_type_for_date_init) { $i++;

                            if ($bk_type_for_date_init != 'previos_day' ) {              // Checking for one day selection situation
                                $my_date          = $my_date_init;
                                $my_date = explode('-',$my_date);
                                $bk_type_for_date = $bk_type_for_date_init;
                            }

                            if ( get_bk_option( 'booking_recurrent_time' ) !== 'On') {

                                if ($i == 1) {
                                    $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], $start_time[0], $start_time[1], $start_time[2] );
                                }elseif ($i == count($value_dates)) {
                                    $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], $end_time[0], $end_time[1], $end_time[2] );
                                }else {
                                    $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], '00', '00', '00' );
                                }

                                if ( !empty($insert) ) $insert .= ', ';
                                if ($bk_type_for_date !== $updated_type_id) $insert .= $wpdb->prepare( "(%d, %s, %d, %d)", $booking_id, $date, $is_approved_dates, $bk_type_for_date );
                                else                                        $insert .= $wpdb->prepare( "(%d, %s, %d, NULL)",$booking_id,$date, $is_approved_dates );

                            } else {

                                //if ($my_date_previos  == $my_date) continue; // escape for single day selections.
                                $my_date_previos = $my_date;

                                $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], $start_time[0], $start_time[1], $start_time[2] );
                                if ( !empty($insert) ) $insert .= ', ';
                                if ($bk_type_for_date !== $updated_type_id) $insert .= $wpdb->prepare( "(%d, %s, %d, %d)", $booking_id, $date, $is_approved_dates, $bk_type_for_date );
                                else                                        $insert .= $wpdb->prepare( "(%d, %s, %d, NULL)",$booking_id,$date, $is_approved_dates );

                                $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], $end_time[0], $end_time[1], $end_time[2] );
                                if ( !empty($insert) ) $insert .= ', ';
                                if ($bk_type_for_date !== $updated_type_id) $insert .= $wpdb->prepare( "(%d, %s, %d, %d)", $booking_id, $date, $is_approved_dates, $bk_type_for_date );
                                else                                        $insert .= $wpdb->prepare( "(%d, %s, %d, NULL)",$booking_id,$date, $is_approved_dates );
                            }

                       }
                   }

                   if ( !empty($insert) )
                        if ( false === $wpdb->query( "INSERT INTO {$wpdb->prefix}bookingdates (booking_id, booking_date, approved, type_id) VALUES {$insert}"  ) ){
                            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during inserting into BD - Dates' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                            die();
                        }

             } // end if of $max_available_items > 1

        }

                // Get Array with busy dates of BK Resource with values as bk_IDs  [9-21-2010] => bk_id, ...
                function get_all_reserved_day_for_bk_type($approved = 'all', $bk_type = 1, $skip_booking_id = '') {
                   global $wpdb;
                    $dates_array = $time_array = array();

                    // Get all reserved dates for $bk_type, including childs But skiped this booking: $skip_booking_id
                    $sql_req = $this->get_sql_bk_dates_for_all_resources('', $bk_type, $approved, $skip_booking_id) ;
//debuge($skip_booking_id, $sql_req);
                    $dates_approve   = $wpdb->get_results( $sql_req );

                    // Get Array with MAX available days
                    // $available_dates = $this->get_bk_dates_for_all_resources( $dates_approve, $approved, 1, $bk_type) ;
                    $return_dates = array();

                    foreach ($dates_approve as $my_date) {
                        if (  ($my_date->date_res_type == $bk_type) || ( is_null($my_date->date_res_type) )   ){ // Dates belong only to this BK Res (type)
                            $my_dat = explode(' ',$my_date->booking_date);


                            // Show the Cehck In/Out date as available for the booking resources with  capcity > 1 //Bence
                            if ( (get_bk_option( 'booking_range_selection_time_is_active')  == 'On') &&
                                 (get_bk_option( 'booking_check_out_available_for_parents') == 'On') &&
                                 (substr($my_dat[1],-2) == '02') ) continue;
                            if ( (get_bk_option( 'booking_range_selection_time_is_active') == 'On') &&
                                 (get_bk_option( 'booking_check_in_available_for_parents') == 'On') &&
                                 (substr($my_dat[1],-2) == '01') ) continue;
                            /**/
                            $my_dt = explode('-',$my_dat[0]);
                            $my_key =  $my_dt[0].'-'.$my_dt[1].'-'.$my_dt[2] ;
                            $my_key_new =  ($my_dt[1]+0).'-'.($my_dt[2]+0).'-'.($my_dt[0]+0) ;

                            $return_dates[$my_key_new]  =  $my_date->booking_id;// $available_dates[$my_key]['max'];
                        }
                    }
                    // TODO, later booking ID have to be NUM of availabe seats at this type
                    return $return_dates;       // Return array each KEY - its Day, Value - booking ID
                }


        // Get UnAvailable days (availability == 0) from - $dates_approve and return only them for client side
        // OR  return availability array (MAX available items array) if $is_return_available_days_array = 1 at client side page
        function get_bk_dates_for_all_resources($dates_approve, $approved, $is_return_available_days_array = 0, $bk_type = 1) {  //return $dates_approve;

            if (count($dates_approve) == 0 )  return array();               // If emty so then return empty


            $max_available_items = $this->get_max_available_items_for_resource($bk_type);   // Get MAX aavailable Number

            $my_page = 'client';                                            // Get a page
            if (   wpbc_is_new_booking_page() ) $my_page = 'add';
            else if ( wpbc_is_bookings_page() ) $my_page = 'booking';


            if  ( ( $my_page == 'add' ) && ( isset($_GET['parent_res'])) ) $my_page = 'client';

            // If NOT Client page so then return this dates
            if (
                    ( $my_page == 'booking' ) ||
                    ( ( $my_page == 'add' ) && (! isset($_GET['parent_res'])) )
               ) {
                return $dates_approve;
               }




            $available_dates = array();
            $return_dates = array();



            // check correct sort of dates with times: /////////////////////
            // For exmaple if we have 2 bookings for same date at [1]09:00-10:00 and [2]10:00-11:00 the sort we will have:
            // [1]09:00 , [2]10:00 , [1]10:00 , [2]11:00
            // but need to have
            // [1]09:00 , [1]10:00 , [2]10:00 , [2]11:00
            ////////////////////////////////////////////////////////////////
//if ($approved=='0') debuge($dates_approve);
            $dates_correct_sort = array();
            foreach ($dates_approve as $my_date) {

                $formated_date = $my_date->booking_date;                                            // Nice Time & Date
                $formated_date = explode(' ',$formated_date);
                $curr_nice_date = $formated_date[0];
                $curr_nice_time = $formated_date[1];
                $formated_date[0] = explode('-',$formated_date[0]);
                $formated_date[1] = explode(':',$formated_date[1]);

                //Set this check in/out availble only if ht emax availbvale items > 1 - for the parents elements
                if (($my_page == 'client') && ($max_available_items>1)){   // Bence
                    // Show the Cehck In/Out date as available for the booking resources with  capcity > 1
                    if ( (get_bk_option( 'booking_range_selection_time_is_active')  == 'On') &&
                         (get_bk_option( 'booking_check_out_available_for_parents') == 'On') &&
                         ($formated_date[1][2] == '2') ) continue;
                    if ( (get_bk_option( 'booking_range_selection_time_is_active') == 'On') &&
                         (get_bk_option( 'booking_check_in_available_for_parents') == 'On') &&
                         ($formated_date[1][2] == '1') ) continue;
                }/**/

//debuge($formated_date[1][2]);

                if ( empty($my_date->date_res_type) )   $curr_bk_type = $my_date->type;             // Nice Type
                else                                    $curr_bk_type = $my_date->date_res_type;

                $curr_bk_id = $my_date->booking_id;                                                 //Nice bk ID


                if (! isset($dates_correct_sort[ $curr_bk_type ]))          // Type
                    $dates_correct_sort[ $curr_bk_type ] = array();

                if (! isset($dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ]))          // Date
                    $dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ] = array();

                if (! isset($dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ][ $curr_bk_id ]))          // ID
                    $dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ][ $curr_bk_id ] = array();

                $dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ][ $curr_bk_id ][ $curr_nice_time ] = $my_date;    // Time
            }


            // Change ID key to Time key
            foreach ($dates_correct_sort as $k_type=>$bt_value) {
                foreach ($bt_value as $k_date=>$bd_value) {

                    foreach ($bd_value as $k_id=>$bid_value) {
                        ksort($dates_correct_sort[ $k_type ][ $k_date ][ $k_id ]);  // Sort time inside of single booking
                        foreach ($bid_value as $k_start_time => $date_finish_value) {
                            $dates_correct_sort[ $k_type ][ $k_date ][ $k_start_time ] = $dates_correct_sort[ $k_type ][ $k_date ][ $k_id ];
                            unset($dates_correct_sort[ $k_type ][ $k_date ][ $k_id ]);
                            break;
                        }
                    }
                  ksort($dates_correct_sort[ $k_type ][ $k_date ]);         // Sort inside of date by time
                }
            }


            // Compress to linear array
            $linear_dates_array = array();
            foreach ($dates_correct_sort as $bt_value) {
                foreach ($bt_value as  $bd_value) {
                    foreach ($bd_value as $bstarttime_value) {
                        foreach ($bstarttime_value as $bstart_end_time_value) {
                            $linear_dates_array[] = $bstart_end_time_value;
                        }
                    }
                }
            }
            $dates_approve = $linear_dates_array;

//debuge($dates_approve, $dates_correct_sort);
            if ($max_available_items == 1) {

                 $booking_id_arr = array();
                 foreach ($dates_approve as $my_date) {

                        if ($my_date->approved == $approved) {
                            $booking_id_arr[]=$my_date->booking_id;
                            array_push($return_dates, $my_date);
                        }
                 }
//debuge($return_dates);
                return $return_dates;
            }




            // Get max available items for specific date.
            // $max_available_items

//debuge($max_available_items);

            // Sort all bookings by dates
            $bookings_in_dates = array();
            foreach ($dates_approve as $my_date) {
                $formated_date = $my_date->booking_date;                                            // Nice Time & Date
                $formated_date = explode(' ',$formated_date);
                $curr_nice_date = $formated_date[0];
                $curr_nice_time = $formated_date[1];
                $formated_date[0] = explode('-',$formated_date[0]);
                $formated_date[1] = explode(':',$formated_date[1]);

                if (! isset($bookings_in_dates[ $curr_nice_date ])) $bookings_in_dates[ $curr_nice_date ] = array();
                if (! isset($bookings_in_dates[ $curr_nice_date ][ 'id' ])) $bookings_in_dates[ $curr_nice_date ][ 'id' ] = array();

                if (! isset($bookings_in_dates[ $curr_nice_date ][ 'id' ][ $my_date->booking_id ]))
                    $bookings_in_dates[ $curr_nice_date ][ 'id' ][ $my_date->booking_id ] = array();

                $bookings_in_dates[ $curr_nice_date ][ 'id' ][ $my_date->booking_id ][] = $curr_nice_time;

            }

//debuge($bookings_in_dates);
            // check time intersections

            // Set for dates $available_dates -> MAX number of available ITEMS per day inside of loop
            foreach ($dates_approve as $my_date) {

                        // Date KEY ////////////////////////////////////
                        $my_dat = explode(' ',$my_date->booking_date);
                        $my_dt = explode('-',$my_dat[0]);
                        $my_tm = explode(':',$my_dat[1]);
                        $my_key =  $my_dt[0].'-'.$my_dt[1].'-'.$my_dt[2] ;

                        // GET AVAILABLE DAYS ARRAY ////////////////////
                        if ( isset($available_dates[$my_key]) )  {          // Get all booked days in array and add id and last id (its will show)

                            if ( ! in_array($my_date->booking_id, $available_dates[$my_key]['id']) ) {

                                $available_dates[$my_key]['max']--;

                                array_push( $available_dates[$my_key]['id'], $my_date->booking_id);
                                $available_dates[$my_key]['last_id'] = $my_date->booking_id;
                                $available_dates[$my_key]['approved'] += $my_date->approved;

                            } elseif (
                                    //($my_date->date_res_type > 0 ) &&                     //Fixed: 2013.07.21 23:26
                                    ($my_date->type !== $my_date->date_res_type ) ) {
                                $available_dates[$my_key]['max']--;
                            }

                        } else {
                            $my_max_show = $max_available_items - 1;
                            $available_dates[$my_key] = array(  'id' => array($my_date->booking_id),
                                                                'max' => $my_max_show,
                                                                'last_id' => $my_date->booking_id,
                                                                'approved' => $my_date->approved);
                        }
             }  // Date loop

//debuge($available_dates);

            // If need just return Array with MAX available ITEMS per day so then return it
            if ( $is_return_available_days_array == 1) {return $available_dates;}

            // Get Unavailable days and return them
            foreach ($dates_approve as $my_date) {
                $my_dat = explode(' ',$my_date->booking_date);
                $my_dt = explode('-',$my_dat[0]);
                $my_key =  $my_dt[0].'-'.$my_dt[1].'-'.$my_dt[2] ;

                // Get Unavailable days, based on MAX availability
                if (    ( $available_dates[$my_key]['max'] <= 0 )
                        && ($available_dates[$my_key]['last_id'] == $my_date->booking_id )
                        ) {
                    if ($available_dates[$my_key]['approved'] > 0 ) $available_dates[$my_key]['approved'] = 1;
                    if ($approved == $available_dates[$my_key]['approved'] )
                        array_push($return_dates, $my_date);
                }
            }
//debuge($approved, $return_dates);
            return $return_dates;
        }


        // S Q L    Modify SQL request according Dates - Get rows, from resource of childs and other dates, which partly belong to bk_type
        function get_sql_bk_dates_for_all_resources($mysql, $bk_type, $approved, $skip_booking_id = '' ) {
             global $wpdb;
             $skip_bookings = '';
             $my_page = 'client';
             if ( wpbc_is_new_booking_page() ) { $my_page = 'add';
             } else if ( wpbc_is_bookings_page() ) { $my_page = 'booking'; }

             if  ( ( $my_page == 'add' ) && ( isset($_GET['parent_res'])) ) $my_page = 'client';


            if (  (isset($_GET['booking_hash'])) ||  ($skip_booking_id != '')   ){

                if (($skip_booking_id != '')) { $my_booking_id = $skip_booking_id;
                } else {
                    $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
                    if ($my_booking_id_type !== false)  $my_booking_id = $my_booking_id_type[0];
                }

            } else { $skip_bookings = ''; }

            $my_approve_rule = '';
            if ( ( ($my_page == 'booking') || ( $my_page=='add') )            // For client side this checking DISABLE coloring of dates in CAPACITY DATES
                 || (get_bk_option( 'booking_is_show_pending_days_as_available') == 'On')
                )
                if ($approved == 'all') $my_approve_rule = '';              // Otherwize, if booking will approved it will not calculate those days, and during availability = 0 , the days will be possible to book and this is WRONG
                else                    $my_approve_rule = 'dt.approved = '.$approved.' AND ';

            $trash_bookings = ' AND bk.trash != 1 ';                                //FixIn: 6.1.1.10  - check also  below usage of {$trash_bookings}

            $sql_req = "SELECT DISTINCT dt.booking_date, dt.type_id as date_res_type, dt.booking_id, dt.approved, bk.form, bt.parent, bt.prioritet, bt.booking_type_id as type
                        FROM {$wpdb->prefix}bookingdates as dt
                                 INNER JOIN {$wpdb->prefix}booking as bk
                                 ON    bk.booking_id = dt.booking_id
                                         INNER JOIN {$wpdb->prefix}bookingtypes as bt
                                         ON    bk.booking_type = bt.booking_type_id

                     WHERE ".$my_approve_rule." 1=1 {$trash_bookings} AND
                                              (      bk.booking_type IN ( {$bk_type} ) ";   // All bookings from PARENT TYPE
            if (($skip_bookings == '') && ($my_page =='client') && ($skip_booking_id=='') )
                $sql_req .=                           "OR bt.parent  IN ( {$bk_type} ) ";   // Bookings from CHILD Type
            $sql_req .=                               "OR dt.type_id IN ( {$bk_type} ) ";   // Bk. Dates from OTHER TYPEs, which belong to This TYPE
            $sql_req .=                        ") "
                     .$skip_bookings ;
            if ($skip_booking_id != '')
            $sql_req .=         "   AND dt.booking_id NOT IN ( {$skip_booking_id} ) ";
            $sql_req .=         " ORDER BY dt.booking_date" ;
//debuge($sql_req);
            return $sql_req;

          }

        // Booking Table Admin Page -- Show also bookins, where SOME dates belong to this Type
        // S Q L    Modification for Admin Panel dates:  (situation, when some bookings dates exist at several resources ) - Booking Tables
        function get_sql_4_dates_from_other_types($blank_sql  , $bk_type, $approved ){
            global $wpdb;

            $sql = " OR  bk.booking_id IN ( SELECT DISTINCT booking_id FROM {$wpdb->prefix}bookingdates as dtt WHERE  dtt.approved IN ( {$approved} ) AND dtt.type_id = {$bk_type} ) ";

            return $sql;
        }


   // Cancel Pending bookings for the specific dates of bookings list, of the same booking resource
   function cancel_pending_same_resource_bookings_for_specific_dates($blank, $approved_id_str){

        $is_show_pending_days_as_available                          = get_bk_option( 'booking_is_show_pending_days_as_available');
        $booking_auto_cancel_pending_bookings_for_approved_date     = get_bk_option( 'booking_auto_cancel_pending_bookings_for_approved_date');

       // If the show pending as available AND auto cancellation is not activated so  then SKIP
       if ( ($is_show_pending_days_as_available != 'On') ||
            ($booking_auto_cancel_pending_bookings_for_approved_date !='On')
          ) return $blank;

       global $wpdb;

       $approved_id_str_array = explode(',',$approved_id_str);

       $my_bk_array = array();

       // Because we can have the several ID from the different Booking resources,
       // So thats why we are need to work seperately with  each booking, because we
       // are need to cancel  only the bookings from the same Booking Resource
       foreach ($approved_id_str_array as $approved_id_str) {

           $trash_bookings = ' AND bk.trash != 1 ';                                //FixIn: 6.1.1.10  - check also  below usage of {$trash_bookings}

           $approved_id_str =(int) $approved_id_str;
            // Select the Dates and Booking Resources of the Bookings, what  was APPROVED
            $mysql = "SELECT DISTINCT (dt.booking_date) AS date, bk.booking_type
                      FROM {$wpdb->prefix}bookingdates as dt
                         INNER JOIN {$wpdb->prefix}booking as bk
                         ON    bk.booking_id = dt.booking_id
                       WHERE dt.booking_id = {$approved_id_str} {$trash_bookings}
                      ORDER BY date ASC";

            $my_dates = $wpdb->get_results( $mysql );

            if (count($my_dates)==0) break;
//debuge($my_dates);
            // Get Start and Last dates - its because we was order by dates
            $wh_booking_date = $my_dates[0]->date;
            $wh_booking_date2= $my_dates[ (count($my_dates)-1) ]->date;


            //Ceck  times - If we have the FULL date booking, so then set start and the end times in correct way as FULL
            $check_start_time = substr($wh_booking_date, 11);
            if ( $check_start_time == '00:00:01')  {
                $wh_booking_date = substr($wh_booking_date, 0, 11 ) . '00:00:00';
            }
            $check_end_time = substr($wh_booking_date2, 11);
            if ( ( $check_end_time == '00:00:00') || ( $check_end_time == '00:00:02') ) {
                $wh_booking_date2 = substr($wh_booking_date2, 0, 11 ) . '23:59:59';
            }
//debuge($wh_booking_date2);

            // Booking resource
            $wh_booking_type = $my_dates[0]->booking_type;
                        /*
                        // Get DISTINCT booking resource
                        $wh_booking_type = array();
                        foreach ($my_dates as $value) {
                            if (! in_array($value->booking_type, $wh_booking_type)) {
                                $wh_booking_type[]=$value->booking_type;
                            }
                        }
                        $wh_booking_type = implode(',',$wh_booking_type);/**/

            // Pending
            $wh_approved = '0';

//debuge($wh_booking_type, $wh_booking_date, $wh_booking_date2);


             // Get Pending bookings ID of the same Booking Resource
             $sql_start_select = " SELECT bk.booking_id as id " ;
             $sql = " FROM {$wpdb->prefix}booking as bk" ;
             $sql_where = " WHERE " .                                                      // Date (single) connection (Its required for the correct Pages in SQL: LIMIT Keyword)
                    "       EXISTS (
                                     SELECT *
                                     FROM {$wpdb->prefix}bookingdates as dt
                                     WHERE  bk.booking_id = dt.booking_id {$trash_bookings} " ;
                         $sql_where.=        " AND dt.approved = ".$wh_approved." " ;            // Pending
                         $sql_where.=        " AND ( dt.booking_date >= '" . $wh_booking_date . "' ) ";
                         $sql_where.=        " AND ( dt.booking_date <= '" . $wh_booking_date2 . "' ) ";
                         $sql_where.=   " AND (  " ;
                         $sql_where.=   "       ( bk.booking_type IN  ( ". $wh_booking_type ." ) ) " ;     // BK Resource conections
                         $sql_where .= apply_bk_filter('get_l_bklist_sql_resources', ''  , $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 );
                         $sql_where.=   "     )  " ;
             $sql_where.=   "     )  " ;
//debuge($sql_where);
             $my_bk = $wpdb->get_results( $sql_start_select . $sql . $sql_where );
//debuge($my_bk);
             foreach ($my_bk as $value) {
                if (! in_array($value->id, $my_bk_array)) {
                    $my_bk_array[]=$value->id;
                }
             }
       }

       if (isset($_POST['user_id'])) {
            $user_bk_id = $_POST['user_id'];
       } else {
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
       }

       $all_bk_id = implode('|',$my_bk_array);
//debuge($all_bk_id);
       //$bk_url_listing     = wpbc_get_bookings_url()  . '&wh_booking_id='.str_replace('|',',',$all_bk_id).'&view_mode=vm_listing&tab=actions';

       if (count($my_bk_array)>0) {
            // Delete all other Pending bookings
            ?><script type="text/javascript">
                    // Delete the pending bookings for the same dates
                    delete_booking('<?php echo $all_bk_id; ?>' , <?php echo $user_bk_id; ?>, '<?php echo wpbc_get_booking_locale(); ?>' , 1);

                    var my_message = '<?php echo html_entity_decode( esc_js( sprintf(__('The folowing pending booking(s): %s deleted.' ,'booking'), str_replace('|', ',', $all_bk_id) ) ),ENT_QUOTES) ; ?>';
                    wpbc_admin_show_message( my_message, 'success', 5000 );
            </script><?php
       }
       return $all_bk_id;
   }
// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" A d m i n   B O O K I N G   P a g e ">

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// A d m i n   B O O K I N G   P a g e     ////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        function check_if_bk_res_have_childs($bk_type_id) {
            if ($bk_type_id<1) return false;
            global $wpdb;
            $mysql=  $wpdb->prepare( "SELECT booking_type_id as id, prioritet  FROM {$wpdb->prefix}bookingtypes WHERE ( parent= %d )  ORDER BY prioritet", $bk_type_id );
            $types_list = $wpdb->get_results( $mysql );
            if (count($types_list)>0)  return count($types_list);
            else return false;
        }

        // check if this resource Parent and have some childs, so then assign to $_GET['parent_res'] = 1
        function check_if_bk_res_parent_with_childs_set_parent_res($bk_type_id) {

            if ( $this->check_if_bk_res_have_childs( $bk_type_id ) )
                $_GET['parent_res'] = 1;
        }
 // </editor-fold>


}
