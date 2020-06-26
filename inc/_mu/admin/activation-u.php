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
function wpbc_booking_activate_u() {
    
    ////////////////////////////////////////////////////////////////////////////
    // DB Tables
    ////////////////////////////////////////////////////////////////////////////
    if ( true ){
        global $wpdb;

        $charset_collate = '';
        $wp_queries = array();

        if ( wpbc_is_field_in_table_exists( 'bookingtypes', 'users' ) == 0 ) {
            $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD users BIGINT(20) DEFAULT '1'";
            $wpdb->query( $simple_sql );
        }
        if ( wpbc_is_field_in_table_exists( 'booking_seasons', 'users' ) == 0 ) {
            $simple_sql = "ALTER TABLE {$wpdb->prefix}booking_seasons ADD users BIGINT(20) DEFAULT '1'";
            $wpdb->query( $simple_sql );
        }
        if ( wpbc_is_field_in_table_exists( 'booking_coupons', 'users' ) == 0 ) {
            $simple_sql = "ALTER TABLE {$wpdb->prefix}booking_coupons ADD users BIGINT(20) DEFAULT '1'";
            $wpdb->query( $simple_sql );
        }
    }

    
    ////////////////////////////////////////////////////////////////////////////
    // Demo
    ////////////////////////////////////////////////////////////////////////////    
    if ( wpbc_is_this_demo() ) {

        $wp_queries = array();
        $wp_queries[] = "DELETE FROM {$wpdb->prefix}booking_types_meta ";
        $wp_queries[] = "DELETE FROM {$wpdb->prefix}bookingtypes WHERE users=1 ;";
        foreach ( $wp_queries as $wp_q )
            $wpdb->query( $wp_q );

        $activated_user = apply_bk_filter( 'wpbc_is_user_in_activation_process' );                
        if ( $activated_user === false ) {                                      // Users activation
        
            // User Demo
            make_bk_action( 'wpbc_reactivate_user', 2, false );
            update_user_option( 2, 'booking_user_role', 'super_admin' );
            update_user_option( 2, 'booking_max_num_of_resources', 5 );

            // User Owner1
            make_bk_action( 'wpbc_reactivate_user', 3 );            
            update_user_option( 3, 'booking_max_num_of_resources', 5 );

            // User Owner2
            make_bk_action( 'wpbc_reactivate_user', 4 );
            update_user_option( 4, 'booking_max_num_of_resources', 5 );

            // User Owner3
            make_bk_action( 'wpbc_reactivate_user', 5 );
            update_user_option( 5, 'booking_max_num_of_resources', 5 );

            $wp_queries = array();
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes AS bk SET bk.cost='100' WHERE bk.booking_type_id=13;";
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes AS bk SET bk.cost='500' WHERE bk.booking_type_id=14;";
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes AS bk SET bk.cost='300' WHERE bk.booking_type_id=15;";
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes AS bk SET bk.cost='50' WHERE bk.booking_type_id=16;";
            
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET title = '" . __( 'Royal Villa', 'booking' ) . "' WHERE booking_type_id=14 ;";
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET title = '" . __( 'Suite', 'booking' ) . "' WHERE booking_type_id=15 ;";
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET title = '" . __( 'Apartment#1', 'booking' ) . "' WHERE booking_type_id=16 ;";
            $wp_queries[] = "INSERT INTO {$wpdb->prefix}bookingtypes ( title, cost, users ) VALUES ( '" . __( 'Apartment#2', 'booking' ) . "',75 , 5 );";

            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '2' WHERE booking_type_id=17 ;";
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '2' WHERE booking_type_id=16 ;";
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '3' WHERE booking_type_id=15 ;";
            $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '5' WHERE booking_type_id=14 ;";
            foreach ( $wp_queries as $wp_q )
                $wpdb->query( $wp_q );


            $us_form = get_user_option( 'booking_form', 3 );
            update_user_option( 3, 'booking_form', '<strong>Individual booking form of Owner1:</strong><br/><br/>' . $us_form );
            $us_form = get_user_option( 'booking_form', 4 );
            update_user_option( 4, 'booking_form', '<strong>Individual booking form of Owner2:</strong><br/><br/>' . $us_form );
            $us_form = get_user_option( 'booking_form', 5 );
            update_user_option( 5, 'booking_form', '<strong>Individual booking form of Owner3:</strong><br/><br/>' . $us_form );

            $us_form = get_user_option( 'booking_form_show', 3 );
            update_user_option( 3, 'booking_form_show', '<strong>Data of Owner1</strong>: ' . $us_form );
            $us_form = get_user_option( 'booking_form_show', 4 );
            update_user_option( 4, 'booking_form_show', '<strong>Data of Owner2</strong>: ' . $us_form );
            $us_form = get_user_option( 'booking_form_show', 5 );
            update_user_option( 5, 'booking_form_show', '<strong>Data of Owner3</strong>: ' . $us_form );

            update_user_option( 3, 'booking_view_days_num', '90' );
            update_user_option( 4, 'booking_view_days_num', '90' );
            update_user_option( 5, 'booking_view_days_num', '90' );
            
            make_bk_action( 'regenerate_booking_search_cache' );
        }
    }    
}
add_bk_action( 'wpbc_other_versions_activation',   'wpbc_booking_activate_u'   );


/** D e a c t i v a t e */
function wpbc_booking_deactivate_u() {

    ////////////////////////////////////////////////////////////////////////////
    // DB Tables
    ////////////////////////////////////////////////////////////////////////////
    // global $wpdb;
    
}
add_bk_action( 'wpbc_other_versions_deactivation', 'wpbc_booking_deactivate_u' );