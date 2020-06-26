<?php
/**
 * @version     1.0
 * @package     Booking Calendar
 * @category    A c t i v a t e    &    D e a c t i v a t e
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


/** A c t i v a t e */
function wpbc_booking_activate_l() {
    
    // Generate New Search Cache
    make_bk_action( 'regenerate_booking_search_cache' );                        

    
    ////////////////////////////////////////////////////////////////////////////
    // DB Tables
    ////////////////////////////////////////////////////////////////////////////
    if ( true ){
        global $wpdb;            
        $charset_collate = '';            
        if ( ! empty($wpdb->charset) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
        if  (wpbc_is_field_in_table_exists('bookingtypes','prioritet') == 0){
            $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD prioritet INT(4) DEFAULT '0'";
            $wpdb->query( $simple_sql );
        }
        if  (wpbc_is_field_in_table_exists('bookingtypes','parent') == 0){
            $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD parent bigint(20) DEFAULT '0'";
            $wpdb->query( $simple_sql );
        }
        if  (wpbc_is_field_in_table_exists('bookingtypes','visitors') == 0){
            $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD visitors bigint(20) DEFAULT '1'";
            $wpdb->query( $simple_sql );
        }
        if  (wpbc_is_field_in_table_exists('bookingdates','type_id') == 0){
            $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingdates ADD type_id bigint(20)";
            $wpdb->query( $simple_sql );
        }   
        
        // Need to  create index based on 3 fields,  its because we can have same dates with  same booking ID,  but for different booking resources
        if  (wpbc_is_index_in_table_exists('bookingdates','booking_id_dates') != 0) {
            $simple_sql = "DROP INDEX booking_id_dates ON  {$wpdb->prefix}bookingdates ;";
            $wpdb->query( $simple_sql );
        }
        if  (wpbc_is_index_in_table_exists('bookingdates','booking_id_dates') == 0) {
            $simple_sql = "CREATE UNIQUE INDEX booking_id_dates ON {$wpdb->prefix}bookingdates ( booking_id, booking_date, type_id );";
            $wpdb->query( $simple_sql );
        }
        
        
        if ( ( ! wpbc_is_table_exists('booking_coupons')  )) {                      // Booking Types   M E T A  table

                $wp_queries=array();
                $wp_queries[] = "CREATE TABLE {$wpdb->prefix}booking_coupons (
                     coupon_id bigint(20) unsigned NOT NULL auto_increment,
                     coupon_active int(10) NOT NULL default 1,
                     coupon_code varchar(200) NOT NULL default '',
                     coupon_value FLOAT(7,2) NOT NULL DEFAULT 0.00,
                     coupon_type varchar(200) NOT NULL default '',
                     expiration_date datetime,
                     coupon_min_sum FLOAT(7,2) NOT NULL DEFAULT 0.00,
                     support_bk_types text ,
                     PRIMARY KEY  (coupon_id)
                    ) $charset_collate;";

                foreach ($wp_queries as $wp_q) $wpdb->query( $wp_q );
        }
        $booking_version_num = get_option( 'booking_version_num');        
        if ($booking_version_num === false ) $booking_version_num = '0';
        if ( version_compare('5.4.3', $booking_version_num) > 0 ){                  // Update,  if we have version 5.4.2 or loweer                
            $wp_query = "UPDATE {$wpdb->prefix}booking_coupons SET coupon_active = 1000000 WHERE coupon_active = 1";          // Update all coupons usage number
            $wpdb->query( $wp_query );                
        }                    
    }

    
    ////////////////////////////////////////////////////////////////////////////
    // Demo
    ////////////////////////////////////////////////////////////////////////////            
    if (    ( wpbc_is_this_demo() )
         || (  $_SERVER['HTTP_HOST'] === 'beta'  )
    ){

        update_bk_option( 'booking_form', str_replace( '\\n\\', '', wpbc_get_default_booking_form() ) );
        update_bk_option( 'booking_is_show_availability_in_tooltips', 'On' );
        update_bk_option( 'booking_skin', '/inc/skins/premium-marine.css' );
        update_bk_option( 'booking_type_of_day_selections', 'multiple' );
        update_bk_option( 'booking_range_selection_type', 'dynamic' );
        update_bk_option( 'booking_range_selection_days_count', '7' );
        update_bk_option( 'booking_range_selection_days_max_count_dynamic', 30 );
        update_bk_option( 'booking_range_selection_days_specific_num_dynamic', '' );
        update_bk_option( 'booking_range_start_day', '-1' );
        update_bk_option( 'booking_range_selection_days_count_dynamic', '1' );
        update_bk_option( 'booking_range_start_day_dynamic', '-1' );
        update_bk_option( 'booking_range_selection_time_is_active', 'Off' );
        update_bk_option( 'booking_range_selection_start_time', '14:00' );
        update_bk_option( 'booking_range_selection_end_time', '12:00' ); /**/
        update_bk_option( 'booking_is_show_legend', 'Off' );
        update_bk_option( 'booking_is_use_visitors_number_for_availability', 'On' );
        //update_bk_option( 'booking_availability_based_on' ,  'visitors' );                
        update_bk_option( 'booking_is_show_cost_in_tooltips', 'On' );
        update_bk_option( 'booking_is_show_cost_in_date_cell', 'Off' );
        update_bk_option( 'booking_form_show', str_replace( '\\n\\', '', wpbc_get_default_booking_form_show() ) );
        update_bk_option( 'booking_search_form_show', str_replace( '\\n\\r', "\n", wpbc_get_default_search_form_template( 'advanced' ) ) );      //FixIn:6.1.0.1
        update_bk_option( 'booking_found_search_item', str_replace( '\\n\\r', "\n", wpbc_get_default_search_results_template( 'advanced' ) ) );   //FixIn:6.1.0.1

        $wp_queries = array();
        $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;" , __('Standard' ,'booking'), __('Default' ,'booking') );
        $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;" , __('Superior' ,'booking'), __('Resource #1' ,'booking') );
        $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;" , __('Presidential Suite' ,'booking'), __('Resource #2' ,'booking') );
        $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;" , __('Royal Villa' ,'booking'), __('Resource #3' ,'booking') );
        $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '2' WHERE title = %s ;" , __('Standard' ,'booking') );
        $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '3' WHERE title = %s ;" , __('Superior' ,'booking') );
        $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '150', visitors = '4' WHERE title = %s ;" , __('Presidential Suite' ,'booking') );
        $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '500', visitors = '5' WHERE title = %s ;" , __('Royal Villa' ,'booking') );
        $wp_queries[] = 'DELETE FROM '.$wpdb->prefix .'booking_types_meta ; ';
        $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 4, "rates", "a:3:{s:6:\"filter\";a:3:{i:3;s:3:\"Off\";i:2;s:3:\"Off\";i:1;s:2:\"On\";}s:4:\"rate\";a:3:{i:3;s:1:\"0\";i:2;s:1:\"0\";i:1;s:3:\"200\";}s:9:\"rate_type\";a:3:{i:3;s:1:\"%\";i:2;s:1:\"%\";i:1;s:1:\"%\";}}" );';
        $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 3, "costs_depends", "a:3:{i:0;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:1:\">\";s:4:\"from\";s:1:\"1\";s:2:\"to\";s:1:\"2\";s:4:\"cost\";s:3:\"250\";s:13:\"cost_apply_to\";N;}i:1;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:1:\"=\";s:4:\"from\";s:1:\"3\";s:2:\"to\";s:1:\"3\";s:4:\"cost\";s:3:\"200\";s:13:\"cost_apply_to\";s:5:\"fixed\";}i:2;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:4:\"summ\";s:4:\"from\";s:1:\"4\";s:2:\"to\";s:1:\"2\";s:4:\"cost\";s:3:\"875\";s:13:\"cost_apply_to\";s:5:\"fixed\";}}" );';
        foreach ($wp_queries as $wp_q) $wpdb->query($wp_q);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Insert Default child objects     ////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
	//FixIn: 8.1.3.16
    if (    ( wpbc_is_this_demo() )
         || (  $_SERVER['HTTP_HOST'] === 'beta'  )
    ){

		$my_sql          = array();
		$child_resources = $wpdb->get_results( "SELECT booking_type_id FROM {$wpdb->prefix}bookingtypes  WHERE parent = 1" );
		$child_1         = $wpdb->get_results( "SELECT title FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = 1" );
		if ( ( count( $child_resources ) == 0 ) && ( count( $child_1 ) > 0 ) ) {
			for ( $i = 1; $i < 6; $i ++ ) {
				$my_sql[] = 'INSERT INTO ' . $wpdb->prefix . 'bookingtypes ( title, parent, cost, prioritet ) VALUES ( "' .
				            $child_1[0]->title . '-' . $i . '", "1", "25", "' . $i . '") ';
			}
		}
		$child_resources = $wpdb->get_results( "SELECT booking_type_id FROM {$wpdb->prefix}bookingtypes  WHERE parent = 2" );
		$child_2         = $wpdb->get_results( "SELECT title FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = 2" );
		if ( ( count( $child_resources ) == 0 ) && ( count( $child_2 ) > 0 ) ) {
			for ( $i = 1; $i < 4; $i ++ ) {
				$my_sql[] = 'INSERT INTO ' . $wpdb->prefix . 'bookingtypes ( title, parent, cost, prioritet ) VALUES ( "' .
				            $child_2[0]->title . '-' . $i . '", "2", "50", "' . $i . '") ';
			}
			if ( $child_2[0]->title == __( 'Superior', 'booking' ) ) {
				$my_sql[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '50' WHERE title = %s ", __( 'Superior', 'booking' ) );
			}
		}
		foreach ( $my_sql as $wp_q ) {
			if ( false === $wpdb->query( $wp_q ) ) {
				debuge_error( 'Error during updating to DB booking resources', __FILE__, __LINE__ );
			}
		}
	}
       
    // Set default number of support visitors at child objects for demo site
    if (    ( wpbc_is_this_demo() )
         || (  $_SERVER['HTTP_HOST'] === 'beta'  )
    ){

           $wp_queries = array();
           $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '2' WHERE title LIKE '". __('Standard' ,'booking') ."-%' ;";
           $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '3' WHERE title LIKE '". __('Superior' ,'booking') ."-%' ;";
           foreach ($wp_queries as $wp_q) $wpdb->query($wp_q);
     }
    
}
add_bk_action( 'wpbc_other_versions_activation',   'wpbc_booking_activate_l'   );



/** D e a c t i v a t e */
function wpbc_booking_deactivate_l() {
    
    delete_bk_option( 'booking_cache_content'  );                               // Generation in specific function - regenerate_booking_search_cache
    delete_bk_option( 'booking_cache_created'  );
    
    ////////////////////////////////////////////////////////////////////////////
    // DB Tables
    ////////////////////////////////////////////////////////////////////////////
    global $wpdb;
    
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}booking_coupons" );
}
add_bk_action( 'wpbc_other_versions_deactivation', 'wpbc_booking_deactivate_l' );