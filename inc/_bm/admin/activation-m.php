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
function wpbc_booking_activate_m() {
    
    ////////////////////////////////////////////////////////////////////////////
    // DB Tables
    ////////////////////////////////////////////////////////////////////////////
    if ( true ) {
        global $wpdb;

        $charset_collate = '';
        if ( !empty( $wpdb->charset ) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( !empty( $wpdb->collate ) )
            $charset_collate .= " COLLATE $wpdb->collate";

        if ( wpbc_is_field_in_table_exists( 'bookingtypes', 'default_form' ) == 0 ) {
            $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD default_form varchar(249) NOT NULL default 'standard'";
            $wpdb->query( $simple_sql );
        }

        if ( ( ! wpbc_is_table_exists('booking_seasons')  )) {
            $wp_queries=array();
            $wp_queries[] = "CREATE TABLE {$wpdb->prefix}booking_seasons (
                 booking_filter_id bigint(20) unsigned NOT NULL auto_increment,
                 title varchar(200) NOT NULL default '',
                 filter text ,
                 PRIMARY KEY  (booking_filter_id)
                ) $charset_collate;";

            $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter ) VALUES ( "'. wpbc_clean_parameter( __('Weekend' ,'booking') ) .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:2:"On";i:1;s:3:"Off";i:2;s:3:"Off";i:3;s:3:"Off";i:4;s:3:"Off";i:5;s:3:"Off";i:6;s:2:"On";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\' );';
          //$wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter ) VALUES ( "'. wpbc_clean_parameter( __('Work days' ,'booking') ) .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:3:"Off";i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:3:"Off";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\' );';

            ////////////////////////////////////////////////////////////
            // Configuration  of the Own Conditional Season Filters
            ////////////////////////////////////////////////////////////
            $date_identificator = strtotime( '+4 weeks' );                //$date_identificator = strtotime( 'first day of next month' );
            $my_date_title = date_i18n( 'F', $date_identificator );
            $my_date = date( 'Y-n', $date_identificator );
            $my_date = explode( '-', $my_date );
            $next_year = $my_date[0];
            $next_month = $my_date[1];

            $filter = array();
            $filter['weekdays'] = array();
            for ( $k = 0; $k < 7; $k++ ) {
                $filter['weekdays'][$k] = 'On';
            }
            $filter['days'] = array();
            for ( $k = 1; $k < 32; $k++ ) {
                if ( $k < 15 )
                    $filter['days'][$k] = 'On';
                else
                    $filter['days'][$k] = 'Off';
            }
            $filter['monthes'] = array();
            for ( $k = 1; $k < 13; $k++ ) {
                if ( $next_month == $k )
                    $filter['monthes'][$k] = 'On';
                else
                    $filter['monthes'][$k] = 'Off';
            }
            $filter['year'] = array();
            $start_year = date( 'Y' );
            for ( $k = ($start_year - 1); $k < ($start_year + 11); $k++ ) {
                if ( $next_year == $k )
                    $filter['year'][$k] = 'On';
                else
                    $filter['year'][$k] = 'Off';
            }
            $filter['start_time'] = ''; $filter['end_time'] = '';      
            $configurable_filter = serialize($filter);                    
            $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter ) VALUES ( "'. wpbc_clean_parameter( '1 - 14, '. $my_date_title ) .'", \''. 
                                                                                                          $configurable_filter .'\' );';
            $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter ) VALUES ( "'. wpbc_clean_parameter( __('High season' ,'booking') ) .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:2:"On";i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:3:"Off";i:2;s:3:"Off";i:3;s:3:"Off";i:4;s:3:"Off";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:3:"Off";i:11;s:3:"Off";i:12;s:3:"Off";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\' );';
            foreach ( $wp_queries as $wp_q ) 
                $wpdb->query( $wp_q );
        }

        // Booking Types   M E T A  table
        if ( ( ! wpbc_is_table_exists('booking_types_meta')  )) {
            $wp_queries=array();
            $wp_queries[] = "CREATE TABLE {$wpdb->prefix}booking_types_meta (
                 meta_id bigint(20) unsigned NOT NULL auto_increment,
                 type_id bigint(20) NOT NULL default 0,
                 meta_key varchar(200) NOT NULL default '',
                 meta_value text ,
                 PRIMARY KEY  (meta_id)
                ) $charset_collate;";

            foreach ($wp_queries as $wp_q) $wpdb->query( $wp_q );
        }

    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    // Demo
    ////////////////////////////////////////////////////////////////////////////            
    if ( wpbc_is_this_demo() ) {
        update_bk_option( 'booking_form', str_replace('\\n\\','', wpbc_get_default_booking_form() ) );
        update_bk_option( 'booking_form_show', str_replace('\\n\\','', wpbc_get_default_booking_form_show() ) );
        update_bk_option( 'booking_type_of_day_selections' , 'range' );
        update_bk_option( 'booking_range_selection_type', 'dynamic');
        update_bk_option( 'booking_range_selection_days_count','7');
        update_bk_option( 'booking_range_selection_days_max_count_dynamic',30);
        update_bk_option( 'booking_range_selection_days_specific_num_dynamic','');
        update_bk_option( 'booking_range_start_day' , '-1' );
        update_bk_option( 'booking_range_selection_days_count_dynamic','3');
        update_bk_option( 'booking_range_start_day_dynamic' , '-1' );
        update_bk_option( 'booking_range_selection_time_is_active', 'On');
        update_bk_option( 'booking_range_selection_start_time','14:00');
        update_bk_option( 'booking_range_selection_end_time','12:00');/**/
        update_bk_option( 'booking_view_days_num','30');
        update_bk_option( 'booking_is_show_cost_in_date_cell',  'On');
        update_bk_option( 'booking_skin', '/css/skins/traditional.css');                                  
        update_bk_option( 'booking_advanced_costs_values', 
            unserialize( 's:247:"a:3:{s:8:"visitors";a:4:{i:1;s:4:"100%";i:2;s:4:"200%";i:3;s:4:"300%";i:4;s:4:"400%";}s:8:"children";a:4:{i:0;s:4:"100%";i:1;s:4:"100%";i:2;s:4:"100%";i:3;s:4:"100%";}s:18:"term_and_condition";a:1:{s:28:"I_Accept_term_and_conditions";s:4:"100%";}}"') );

        $wp_queries=array();
        $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 4, "rates", "a:3:{s:6:\"filter\";a:3:{i:3;s:3:\"Off\";i:2;s:3:\"Off\";i:1;s:2:\"On\";}s:4:\"rate\";a:3:{i:3;s:1:\"0\";i:2;s:1:\"0\";i:1;s:3:\"200\";}s:9:\"rate_type\";a:3:{i:3;s:1:\"%\";i:2;s:1:\"%\";i:1;s:1:\"%\";}}" );';
        $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 3, "costs_depends", "a:3:{i:0;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:1:\">\";s:4:\"from\";s:1:\"1\";s:2:\"to\";s:1:\"2\";s:4:\"cost\";s:2:\"50\";s:13:\"cost_apply_to\";s:5:\"fixed\";}i:1;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:1:\"=\";s:4:\"from\";s:1:\"3\";s:2:\"to\";s:1:\"4\";s:4:\"cost\";s:2:\"45\";s:13:\"cost_apply_to\";s:5:\"fixed\";}i:2;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:4:\"summ\";s:4:\"from\";s:1:\"4\";s:2:\"to\";s:1:\"2\";s:4:\"cost\";s:3:\"175\";s:13:\"cost_apply_to\";s:5:\"fixed\";}}" );';
        $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 2, "availability", "a:2:{s:7:\"general\";s:2:\"On\";s:6:\"filter\";a:3:{i:3;s:3:\"Off\";i:2;s:2:\"On\";i:1;s:3:\"Off\";}}" );';
        foreach ($wp_queries as $wp_q) $wpdb->query($wp_q);
    }
    
}
add_bk_action( 'wpbc_other_versions_activation',   'wpbc_booking_activate_m'   );



/** D e a c t i v a t e */
function wpbc_booking_deactivate_m() {
    ////////////////////////////////////////////////////////////////////////////
    // DB Tables
    ////////////////////////////////////////////////////////////////////////////
    global $wpdb;
    
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}booking_seasons");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}booking_types_meta");    
}
add_bk_action( 'wpbc_other_versions_deactivation', 'wpbc_booking_deactivate_m' );