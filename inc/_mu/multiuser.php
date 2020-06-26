<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

require_once(WPBC_PLUGIN_DIR. '/inc/_mu/admin/api-settings-u.php' );            // Settings page
require_once(WPBC_PLUGIN_DIR. '/inc/_mu/admin/wpbc-users-table.php' );                // Users Table for Settings page
require_once(WPBC_PLUGIN_DIR. '/inc/_mu/admin/activation-u.php' );              // Activate / Deactivate
require_once(WPBC_PLUGIN_DIR. '/inc/_mu/admin/page-users.php' );                // Settings Page

class wpdev_bk_multiuser {

    public $current_user;
    public $activated_user;
    public $client_side_active_params_of_user;
    public $super_admin_id;

    public function __construct(){

        // Get first administrator user ID and set it as superadmin
        $admin_id = 1;
        global $wpdb;
        $sql_check_table = "SELECT ID FROM {$wpdb->prefix}users as u LEFT JOIN {$wpdb->prefix}usermeta as m ON u.ID = m.user_id  WHERE m.meta_key='{$wpdb->prefix}capabilities'" ;
        $res = $wpdb->get_results( $sql_check_table . "AND m.meta_value LIKE '%administrator%' ORDER BY ID ASC LIMIT 0,1" );
        if ( count( $res )>0 ) {
            $admin_id = $res[0]->ID;
        }
        $this->super_admin_id = array( $admin_id );                             // ID of Super Administrators
        $this->activated_user = false;
        $this->client_side_active_params_of_user = false;

        add_bk_filter( 'get_default_super_admin_id', array( $this, 'get_default_super_admin_id' ) );

        add_bk_action( 'check_for_resources_of_notsuperadmin_in_booking_listing', array( &$this, 'check_for_resources_of_notsuperadmin_in_booking_listing' ) );
        add_bk_filter( 'multiuser_is_current_user_active', array( &$this, 'multiuser_is_current_user_active' ) );                                                   //FixIn: 6.0.1.17

        add_bk_filter( 'multiuser_resource_list', array( $this, 'multiuser_resource_list' ) );
        add_bk_filter( 'multiuser_modify_SQL_for_current_user', array( $this, 'multiuser_modify_SQL_for_current_user' ) );

        add_bk_filter( 'multiuser_is_user_can_be_here', array( $this, 'multiuser_is_user_can_be_here' ) );
        add_bk_filter( 'is_user_super_admin', array( $this, 'is_user_super_admin' ) );
        add_bk_filter( 'get_default_bk_resource_for_user', array( $this, 'get_default_bk_resource_for_user' ) );
        add_bk_filter( 'get_bk_resources_of_user', array( $this, 'get_bk_resources_of_user' ) );
        add_bk_filter( 'get_user_of_this_bk_resource', array( $this, 'get_user_of_this_bk_resource' ) );

        add_bk_action( 'check_multiuser_params_for_client_side_by_user_id', array( &$this, 'check_multiuser_params_for_client_side_by_user_id' ) );
        add_bk_action( 'check_multiuser_params_for_client_side', array( &$this, 'check_multiuser_params_for_client_side' ) );
        add_bk_action( 'finish_check_multiuser_params_for_client_side', array( &$this, 'finish_check_multiuser_params_for_client_side' ) );
        add_bk_filter( 'get_client_side_active_params_of_user', array( &$this, 'get_client_side_active_params_of_user' ) );

        add_bk_filter( 'get_sql_for_checking_new_bookings_multiuser', array( &$this, 'get_sql_for_checking_new_bookings_multiuser' ) );
        add_bk_filter( 'update_sql_for_checking_new_bookings', array( &$this, 'update_sql_for_checking_new_bookings' ) );
        add_bk_filter( 'update_where_sql_for_getting_bookings_in_multiuser', array( &$this, 'update_where_sql_for_getting_bookings_in_multiuser' ) );

        add_bk_filter( 'wpdev_bk_get_option', array( &$this, 'wpdev_bk_get_option' ) );
        add_bk_filter( 'wpdev_bk_update_option', array( &$this, 'wpdev_bk_update_option' ) );
        add_bk_filter( 'wpdev_bk_delete_option', array( &$this, 'wpdev_bk_delete_option' ) );
        add_bk_filter( 'wpdev_bk_add_option', array( &$this, 'wpdev_bk_add_option' ) );
        add_bk_action( 'wpbc_reactivate_user', array( &$this, 'reactivate_user' ) );
        add_bk_action( 'wpbc_deactivate_user', array( &$this, 'deactivate_user' ) );
        add_bk_filter( 'wpbc_is_user_in_activation_process', array( &$this, 'is_user_in_activation_process' ) );

        add_filter( 'wpbc_before_showing_settings_page_is_show_page', array($this, 'wpbc_before_showing_settings_page_is_show_page'), 10, 4 );


        add_bk_filter( 'wpbc_multiuser_get_booking_form_show_of_regular_user', array( &$this, 'multiuser_get_booking_form_show_of_regular_user' ) );    //FixIn: 8.1.3.19

        // After cration user  at admin panel
        // add_action( 'user_register', array(&$this, 'activate_user_after_sign_up' ), 10, 1 );     // Activate booking admin panel,  just  after sign-up of new user
        // During Activation  (after visitor click  on the verification  link)
        // add_action( 'wpmu_new_user', array(&$this, 'activate_user_after_sign_up' ), 10, 1 );     // Activate booking admin panel,  just  after sign-up of new user
    }


	/**
	 * Get standard or custom  booking form  of regular  user,  otherwise return  $blank
	 *
	 * @param string  $blank
	 * @param string  $booking_resource_id
	 * @param string  $custom_form_name
	 *
	 * @return mixed|string
	 */
    function multiuser_get_booking_form_show_of_regular_user( $blank, $booking_resource_id , $custom_form_name ){       //FixIn: 8.1.3.19

		$user_id = apply_bk_filter( 'get_user_of_this_bk_resource', false, $booking_resource_id );

		$is_booking_resource_user_super_admin = apply_bk_filter('is_user_super_admin',  $user_id );

		if ( ! $is_booking_resource_user_super_admin ) {                                                				// Regular User

			if (    ( get_bk_option( 'booking_is_custom_forms_for_regular_users' ) === 'On' )
			     && ( ( $custom_form_name != 'standard' ) && ( ! empty( $custom_form_name ) ) )
			) {
				// Custom  form 	( Regular User )

                $serialized_form_content = get_user_option( 'booking_forms_extended', $user_id );
                $booking_form_show = wpbc_get_custom_booking_form( $blank, $custom_form_name, $serialized_form_content );
			} else {

				// Standard Form 	( Regular User )
				$booking_form_show = get_user_option( 'booking_form_show', $user_id );
			}

		} else {																										//Super booking admin, return $blank  value,  its have to be  form  from wp_options
			$booking_form_show = $blank;
		}

		return  $booking_form_show;
    }


    /**
	 * Cehck if Show or NOT specific Settings page,  depend if Regular  user ativated or its super booking admin.
     *
     * @param type $is_show_this_page
     * @param type $page_tag
     * @param type $active_page_tab
     * @param type $active_page_subtab
     * @return type
     */
    public function wpbc_before_showing_settings_page_is_show_page( $is_show_this_page, $page_tag, $active_page_tab, $active_page_subtab ) {

        $is_user_active = $this->multiuser_is_user_can_be_here( true, 'check_for_active_users' );

        return $is_user_active;
    }

    ////////////////////////////////////////////////////////////////////////////
    // S U P P O R T
    ////////////////////////////////////////////////////////////////////////////

    function activate_user_after_sign_up( $us_id ) {
        if ( ! user_can( $us_id, "subscriber" ) ) {                             // Restrict Reactivation  only to Subscribers
            $this->reactivate_user($us_id, false);                                  // Activate user  with  ID = $us_id
            //update_user_option($us_id , 'booking_user_role', 'super_admin' );     // Set  user as Super Booking  Admin  user
            update_user_option($us_id, 'booking_max_num_of_resources',  5 );        // Set  maximum allow booking resources for this user
        }
    }

    // If the booking resources is not set, and current user  is not superadmin, so then get only the booking resources of the current user
	function check_for_resources_of_notsuperadmin_in_booking_listing() {

		$my_resources = '';
		$is_superadmin = $this->multiuser_is_user_can_be_here( true, 'only_super_admin' );
		$user = wp_get_current_user();
		$user_bk_id = $user->ID;
		if ( ! $is_superadmin ) { // User not superadmin

			$bk_ids = $this->get_bk_resources_of_user( false );

			if ( $bk_ids !== false ) {
				$my_res_id = array();
				foreach ( $bk_ids as $bk_id ) {
					$my_res_id[] = $bk_id->ID;
				}

				$my_resources = implode( ',', $my_res_id );
				if ( ( ! isset( $_REQUEST[ 'wh_booking_type' ] )) || (empty( $_REQUEST[ 'wh_booking_type' ] )) ) {
					$_REQUEST[ 'wh_booking_type' ] = $my_resources;
				} else {

					$resources_in_url = explode( ',', $_REQUEST[ 'wh_booking_type' ] );

					foreach ( $resources_in_url as $rik => $rid ) {
						if ( ! in_array( $rid, $my_res_id ) ) {
							unset( $resources_in_url[ $rik ] );
						}
					}
					if ( ! empty( $resources_in_url ) )
						$_REQUEST[ 'wh_booking_type' ] = implode( ',', $resources_in_url );
					else
						$_REQUEST[ 'wh_booking_type' ] = $my_resources;
				}
			}
		}
	}


	function multiuser_is_current_user_active( $blank = true ){                                //FixIn: 6.0.1.17

        $user = wp_get_current_user();

        $is_user_active = get_user_option( 'booking_is_active', $user->ID );
        if ( $is_user_active == 'On' )
            return  true;

        $is_user_super_admin = apply_bk_filter('is_user_super_admin',  $user->ID );
        if ( $is_user_super_admin )
            return true;

        return false;
    }


    function get_default_super_admin_id() {

        return $this->super_admin_id;
    }
    // Check if this USER  is Super (booking) Admin or not
    function is_user_super_admin($user_bk_id = 0) {
        if ($user_bk_id === 0) {
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
        }

        $u_value = get_user_option( 'booking_user_role', $user_bk_id );
        if ($u_value == 'super_admin') return true;

        if ( in_array($user_bk_id, $this->super_admin_id) ) return true;       // User ID inside of SUper Admin ID
        else                                             return false;
    }

    // Check if user LOW LEVEL
    function is_user_low_level($user_bk_id = 0) {
        if ($user_bk_id === 0) {
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
        }

        $u_value = get_user_option( 'booking_user_role', $user_bk_id );
        if ($u_value == 'low_level_user') return true;
        // if ( in_array($user_bk_id, $this->super_admin_id) ) return true;       // User ID inside of SUper Admin ID
        else                                                return false;
    }

    // Get default booking resource for this active user
    function get_default_bk_resource_for_user( $blank= false,  $user_bk_id = false ){

        global $wpdb;

        if ( $user_bk_id === false ) {
            if ( $this->is_user_super_admin() )
                return false;
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
        }

        $wp_q = $wpdb->prepare( "SELECT booking_type_id as ID FROM {$wpdb->prefix}bookingtypes WHERE users = %d ORDER BY parent, prioritet LIMIT 0, 1;", $user_bk_id );
        $res = $wpdb->get_results( $wp_q );
        if ( count( $res ) > 0 ) {
            return $res[0]->ID;
        } else
            return false;
    }

    // Get default booking resource for this active user
    function get_bk_resources_of_user( $blank= false,  $user_bk_id = false ) {

        $all_resources = wpbc_get_br_as_objects();

        return $all_resources;
    }

    function get_user_of_this_bk_resource($blank= false, $bk_res_id){

        // Use WPBC_BR_Cache for resources
        if ( ( defined( 'WPBC_RESOURCES_CACHE' ) ) && WPBC_RESOURCES_CACHE ) {
            $wpbc_br_cache = wpbc_br_cache();

            $user_id = $wpbc_br_cache->get_resource_attr( $bk_res_id, 'users');
//debuge('$user_id, $bk_res_id',$bk_res_id,$user_id, );
            return  $user_id;
        }
            global $wpdb;

            $wp_q = $wpdb->prepare( "SELECT users  FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id = %d ", $bk_res_id );
            $res = $wpdb->get_results( $wp_q );
            if ( count($res) > 0 ) {
                return $res[0]->users;
            }  else return false;
    }



    /**
	 * Update WHERE SQL for getting bookings relative to  actual  user only
     *
     * @param string $sql_req - WHERE SQL
     * @return string         - updated WHERE SQL
     */
    function update_where_sql_for_getting_bookings_in_multiuser( $sql_req ){

        $user = wp_get_current_user();
        $user_bk_id = $user->ID;

        if ( $this->is_user_super_admin( $user_bk_id ) )
            return $sql_req;                                                    // SuperAdmin

        $user_resources = array();


        if ( ( defined( 'WPBC_RESOURCES_CACHE' ) ) && WPBC_RESOURCES_CACHE ) {  // Use WPBC_BR_Cache for resources
            $wpbc_br_cache = wpbc_br_cache();
            $resources = $wpbc_br_cache->get_resources();
            foreach ( $resources as $res ) {

                if ( $res['users'] == $user_bk_id )
                    $user_resources[] = $res['id'];
            }

        } else {                                                                // Old way

            $bk_ids = $this->get_bk_resources_of_user();
            if ( $bk_ids !== false ) {
                foreach ( $bk_ids as $bk_id ) {
                    $user_resources[] = $bk_id->ID;
                }
            }
        }

        $user_resources = implode(',', $user_resources);

        $sql_req .= " AND bk.booking_type IN ( $user_resources )";

        return $sql_req;
    }


    function get_sql_for_checking_new_bookings_multiuser($sql_req){
        $user = wp_get_current_user();
        $user_bk_id = $user->ID;
        global $wpdb;
        $my_resources = '';
        $bk_ids = $this->get_bk_resources_of_user();
        if ($bk_ids !== false) {
          foreach ($bk_ids as $bk_id) { $my_resources .= $bk_id->ID . ','; }
          $my_resources = substr($my_resources,0,-1);
        }

        $trash_bookings = ' AND bk.trash != 1 ';                                //FixIn: 6.1.1.10  - check also  below usage of {$trash_bookings}

        if ($my_resources!='')
            $sql_req = "SELECT bk.booking_id FROM {$wpdb->prefix}booking as bk
                    WHERE  bk.is_new = 1 {$trash_bookings} AND bk.booking_type IN ($my_resources)";
        else
            $sql_req = "SELECT bk.booking_id FROM {$wpdb->prefix}booking as bk
                    WHERE  bk.is_new = 1 {$trash_bookings}";

        return $sql_req;
    }

    function update_sql_for_checking_new_bookings($update_sql, $tid, $user_bk_id = false){
        if (empty($user_bk_id)) {
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
        }
        global $wpdb;


        if ( $tid <= 0 ) {

            $trash_bookings = ' AND bk.trash != 1 ';                                //FixIn: 6.1.1.10  - check also  below usage of {$trash_bookings}

            $sql_req = "SELECT bk.booking_id as id FROM {$wpdb->prefix}booking as bk
            INNER JOIN {$wpdb->prefix}bookingtypes as bt
            ON    bk.booking_type = bt.booking_type_id
            WHERE  bk.is_new = 1 {$trash_bookings} AND bt.users = {$user_bk_id}" ;

            $bookings = $wpdb->get_results( $sql_req  );

            if ( count($bookings) > 0 ) {

                $booking_str_id = '';
                foreach ( $bookings as $key=>$value ) {
                    $booking_str_id .= $value->id . ',';
                }
                if ( strlen($booking_str_id) > 0 )
                    $booking_str_id = substr($booking_str_id,0,-1);
                $update_sql = "UPDATE {$wpdb->prefix}booking AS bk SET bk.is_new = 0 WHERE bk.is_new = 1 AND bk.booking_id IN ({$booking_str_id}); ";
            }
        }

        return $update_sql;
    }


    ////////////////////////////////////////////////////////////////////////////
    // G e n e r a l    H o o k    E n g i n e
    ////////////////////////////////////////////////////////////////////////////
    // Get
    function wpdev_bk_get_option($blank, $option, $default){

        $user_bk_id = 1;
//debuge('before',$user_bk_id);
        if  ( ! (  ( defined( 'DOING_AJAX' ) )  && ( DOING_AJAX )  ) ) {        //Fix: 5.1.6
            if ( ( defined('WP_ADMIN') ) && ( WP_ADMIN === true ) ) {
                //if (! function_exists('wp_get_current_user') )   return $blank;
                $user = wp_get_current_user();
                $user_bk_id = $user->ID;
            }
        }
//debuge($user_bk_id);
        if ($this->client_side_active_params_of_user !== false) {               // Client side get STD option for this user
            $user_bk_id = $this->client_side_active_params_of_user;
        }

        if ( $this->is_user_super_admin($user_bk_id) )
            return $blank;

        // Exeptions /////////////////////////////////////////////////////////////////////////////////////////
        $exception_value = $this->check_get_option_exception($blank, $option, $default);
        if ( $exception_value !== 'no-exceptions' ) return $exception_value;
        //////////////////////////////////////////////////////////////////////////////////////////////////////

        $u_value = get_user_option( $option, $user_bk_id );
        $u_value = maybe_unserialize( $u_value );                               // Fix in ver.5.3
        if ( empty( $u_value ) )
            return $blank;

        return $u_value;
    }

    // Update
    function wpdev_bk_update_option($blank, $option, $newvalue){

        $user_bk_id = 1;
        if ($this->client_side_active_params_of_user !== false) {           // Client side get STD option for this user
            $user_bk_id = $this->client_side_active_params_of_user;
        }

        if (defined('WP_ADMIN'))
        if ( WP_ADMIN === true )  {
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
        }

        if ($this->activated_user !== false) {                                  // Get ID for Activation process at admin panel
            $user_bk_id = $this->activated_user;
        }

        if ( $this->is_user_super_admin($user_bk_id) )  return $blank;                                 // Standard Way

        return update_user_option( $user_bk_id, $option, $newvalue ) ;

    }

    // Delete
    function wpdev_bk_delete_option($blank, $option){

        $user_bk_id = 1;
        if ($this->client_side_active_params_of_user !== false) {           // Client side get STD option for this user
            $user_bk_id = $this->client_side_active_params_of_user;
        }

	    if ( ( defined( 'WP_ADMIN' ) ) && ( WP_ADMIN === true ) ) {
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
        }

        if ( $this->is_user_super_admin($user_bk_id) )  return $blank;                                 // Standard Way

        return delete_user_option( $user_bk_id, $option);

    }

    // Add
    function wpdev_bk_add_option($blank, $option, $value, $deprecated,  $autoload){

        $user_bk_id = 1;
        if ($this->client_side_active_params_of_user !== false) {           // Client side get STD option for this user
            $user_bk_id = $this->client_side_active_params_of_user;
        }

        if ( WP_ADMIN === true )  {
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
        }

        if ($this->activated_user === false) {                              // Get ID for Activation process at admin panel

                        //debuge($_SERVER, WP_ADMIN, $user_bk_id, $this->is_user_super_admin($user_bk_id));die;
                        // If we make activation from cron and do not know about user
                        if ( wpbc_is_this_demo() )
                            return $blank;


            $user = wp_get_current_user();
            $user_bk_id = $user->ID;

        } else {    // Right now is Activation process is go on.
            $user_bk_id = $this->activated_user;
        }

        if ( $this->is_user_super_admin($user_bk_id) )  return $blank;                                 // Standard Way

        return update_user_option( $user_bk_id, $option, $value );
        //return add_user_meta($user_bk_id, $option, $value);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Exceptions
    ////////////////////////////////////////////////////////////////////////////

    // Get exeption for some option to common users
    function check_get_option_exception($blank, $option, $default){
        if ($option == 'booking_default_booking_resource') return $this->get_default_bk_resource_for_user();
        if ($option == 'booking_is_show_legend' ) return $blank;
        return 'no-exceptions';
    }

    // Delete common users options After ACTIVATION - needed that user get this option from superadmin
    function delete_options_for_users_after_activation( $us_id ) {

        $option_name = '';
        $is_get_multiuser_general_options = true;

        $options_for_delete = wpbc_get_default_options( $option_name, $is_get_multiuser_general_options );

        foreach ($options_for_delete as $value) {
            delete_user_option($us_id, $value);
        }
    }


    ////////////////////////////////////////////////////////////////////////////
    // M a i n   f u n c t i o n s
    ////////////////////////////////////////////////////////////////////////////

    /**
	 * Return  ID of user,  that currently  in activation  process,  or FALSE if no activation process
     *
     * @return int | bool
     */
    function is_user_in_activation_process() {

        return $this->activated_user;
    }

    // Funct. Admin function call of ACTIVATION of user
    function reactivate_user( $us_id, $is_delete_options = true ) {
        global $wpdb;

        $us_status = get_user_option( 'booking_is_active',$us_id);

         $us_data = get_userdata($us_id);
         $nicename = $us_data->user_nicename;

        if ( $us_status == 'Off') { // Its mean that user already was perviosly activated, so have all options. so just set On this option
             update_user_option($us_id, 'booking_is_active','On');               // Now user is active
            if ($is_delete_options) // Clean the settings
                if (! $this->is_user_super_admin($us_id) )
                    $this->delete_options_for_users_after_activation($us_id);
             return ;
        }
        //////////////////////////////////////////////////////////////////////////////////////////////////
        // Need to check if this user exist
        $this->activated_user = $us_id;                                     // Activation is started


        make_bk_action('wpdev_booking_activate_user');                      // General hook for activation of plugin

        // Set Access for this user /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $metavalues = get_user_option( 'capabilities', $us_id );            // Set access level as has this user
        foreach ($metavalues as $key=>$value) {
            if ($value == 1) {
                $my_role= $key;
                update_user_option( $us_id, 'booking_user_role_booking', $my_role );
                update_user_option( $us_id, 'booking_user_role_addbooking', $my_role );
                update_user_option( $us_id, 'booking_user_role_resources', $my_role );
                update_user_option( $us_id, 'booking_user_role_settings', $my_role );
                break;
            }
        }


        ////////////////////////////////////////////////////////////////////////
        // Update Emails  with  new user email
        ////////////////////////////////////////////////////////////////////////
        $email_option_names = array(  WPBC_EMAIL_NEW_ADMIN_PREFIX . WPBC_EMAIL_NEW_ADMIN_ID
                                    , WPBC_EMAIL_NEW_VISITOR_PREFIX . WPBC_EMAIL_NEW_VISITOR_ID
                                    , WPBC_EMAIL_APPROVED_PREFIX . WPBC_EMAIL_APPROVED_ID
                                    , WPBC_EMAIL_DENY_PREFIX . WPBC_EMAIL_DENY_ID
                                    , WPBC_EMAIL_TRASH_PREFIX . WPBC_EMAIL_TRASH_ID
                                    , WPBC_EMAIL_DELETED_PREFIX . WPBC_EMAIL_DELETED_ID
                                    , WPBC_EMAIL_MODIFICATION_PREFIX . WPBC_EMAIL_MODIFICATION_ID
                                    , WPBC_EMAIL_PAYMENT_REQUEST_PREFIX . WPBC_EMAIL_PAYMENT_REQUEST_ID
                              );
        foreach ( $email_option_names as $email_option_name ) {

            // Get Email Data
            $email_data = get_bk_option( $email_option_name );

            // Modify Emails
            // if ( $email_option_name ==  WPBC_EMAIL_NEW_ADMIN_PREFIX . WPBC_EMAIL_NEW_ADMIN_ID ) {						//FixIn: 8.1.2.17
                $email_data['to'] = get_user_option('user_email',  $us_id );    //$email_data['to_name'] = '';
            // }																											//FixIn: 8.1.2.17
            $email_data['from'] = get_user_option('user_email',  $us_id );      //$email_data['from_name'] = '';

            // Update Email Data
            update_user_option( $us_id, $email_option_name, $email_data );
        }
        ////////////////////////////////////////////////////////////////////////


//        // Update to user Emails Adress //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//        $user_bk_email = htmlspecialchars('"Booking system" <' . get_user_option('user_email',  $us_id ) .'>') ;
//        // All
//        update_user_option($us_id, 'booking_email_reservation_adress', $user_bk_email);          // htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
//        update_user_option($us_id, 'booking_email_reservation_from_adress', $user_bk_email);     // htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
//        update_user_option($us_id, 'booking_email_approval_adress', $user_bk_email);             //htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
//        update_user_option($us_id, 'booking_email_deny_adress', $user_bk_email);                 //htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
//        // Pro
//        update_user_option($us_id, 'booking_email_modification_adress', $user_bk_email );        //htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
//        update_user_option($us_id, 'booking_email_newbookingbyperson_adress', $user_bk_email );  //htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
//        // Business Small
//        update_user_option($us_id, 'booking_email_payment_request_adress', $user_bk_email);      //  htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));


        update_user_option($us_id, 'booking_paypal_emeil',              get_user_option('user_email',  $us_id ));      //  htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        update_user_option($us_id, 'booking_paypal_ipn_verified_email', get_user_option('user_email',  $us_id ));
        update_user_option($us_id, 'booking_paypal_ipn_invalid_email' , get_user_option('user_email',  $us_id ));
        update_user_option($us_id, 'booking_paypal_ipn_error_email' ,   get_user_option('user_email',  $us_id ));

        // Delete options from standard boking settings - Its mean that this settings have to getted from SUPER admin initial options
        if ($is_delete_options)
            if (! $this->is_user_super_admin($us_id) )
                $this->delete_options_for_users_after_activation($us_id);


        // Create 1 default booking resource for this user //////////////////////////////////////////////////////////////////////////////////////////////////
        if (  $this->get_default_bk_resource_for_user(false, $us_id ) === false ) { // No booking resource, so create some one
            $wp_query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}bookingtypes ( title, cost, users ) VALUES ( '". __('Default' ,'booking') . " ({$nicename})',50 ,%d );", $us_id );
            if ( false === $wpdb->query( $wp_query  ) ) {    // All users data
                ?> <script type="text/javascript"> document.getElementById('ajax_working').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during creating default booking resource for user' ,__FILE__,__LINE__ ); ?></div>'; </script> <?php
            }
        }

        $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter, users ) VALUES ( "'. __('Weekend' ,'booking') . ' ('.$nicename.')' .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:2:"On";i:1;s:3:"Off";i:2;s:3:"Off";i:3;s:3:"Off";i:4;s:3:"Off";i:5;s:3:"Off";i:6;s:2:"On";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\', '.$us_id.' );';
        $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter, users ) VALUES ( "'. __('Work days' ,'booking') . ' ('.$nicename.')' .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:3:"Off";i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:3:"Off";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\', '.$us_id.' );';
        $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter, users ) VALUES ( "'. __('High season' ,'booking') . ' ('.$nicename.')' .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:2:"On";i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:3:"Off";i:2;s:3:"Off";i:3;s:3:"Off";i:4;s:3:"Off";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:3:"Off";i:11;s:3:"Off";i:12;s:3:"Off";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\', '.$us_id.' );';

        foreach ($wp_queries as $wp_q) $wpdb->query( $wp_q );


        // Activate this user ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        update_user_option($us_id, 'booking_is_active','On');               // Now user is active
        update_user_option($us_id, 'booking_max_num_of_resources', 99 );    // Max num of resources for this user

        $this->activated_user = false;                                      // Activation id Finished
        return;
        /////////////////////////////////////////////////////////////////////////////////////////////
        ?> <script type="text/javascript">
                var my_message = '<?php echo html_entity_decode( esc_js( __('User  is Activated' ,'booking') ),ENT_QUOTES) ; ?>';
                wpbc_admin_show_message( my_message, 'success', 3000 );
        </script> <?php
    }

    // DEACTIVATE and delete all settings for specific user.
    function deactivate_user($us_id, $is_delete_options_also = true, $is_delete_user_bookings = false) {
        global $wpdb;

        if ($is_delete_options_also == true) {

            // Delete all meta bookings values for specific user
            if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE user_id = %d" ,$us_id ) . " AND meta_key LIKE '%booking_%' " )) {    // All users data
                ?> <script type="text/javascript"> document.getElementById('ajax_working').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during deleting user meta at DB' ,__FILE__,__LINE__ ); ?></div>'; </script> <?php
                die();
            }

            if ($is_delete_user_bookings == true) {                         // Delete User BK Resources and user bookings of this resources

                // First select IDs of resources, which we need to delete
                $bk_res_IDs = $wpdb->get_results( $wpdb->prepare( "SELECT booking_type_id as ID FROM {$wpdb->prefix}bookingtypes WHERE users =  %d" , $us_id ) );
                $string_res_id = '';
                foreach ($bk_res_IDs as $br_ID) {
                    if ($string_res_id!='') $string_res_id.=',';
                    $string_res_id .= $br_ID->ID;
                }
                // secondly select bookings ID
                $bk_IDs = $wpdb->get_results( "SELECT booking_id as ID FROM {$wpdb->prefix}booking WHERE booking_type IN ( {$string_res_id} ) " );
                $string_id = '';
                foreach ($bk_IDs as $bk_ID) {
                    if ($string_id!='') $string_id.=',';
                    $string_id .= $bk_ID->ID;
                }
                // D E L E T E     D a t a
                // Dates
                if ($string_id!='') if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}bookingdates WHERE booking_id IN ({$string_id})"  ) ) { ?> <script type="text/javascript"> document.getElementById('ajax_working').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during updating exist booking for deleting dates in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php die(); }
                // Bookings
                if ($string_id!='') if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}booking WHERE booking_id IN ({$string_id})" ) ){ ?> <script type="text/javascript"> document.getElementById('ajax_working').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during deleting booking at DB',__FILE__,__LINE__ ); ?></div>'; </script> <?php die(); }
                // Meta data of Resources
                if ($string_res_id!='') if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}booking_types_meta WHERE type_id IN ({$string_res_id})" ) ) { ?> <script type="text/javascript"> document.getElementById('ajax_working').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during deleting booking at DB',__FILE__,__LINE__ ); ?></div>'; </script> <?php die(); }
                // Resources
                if ($string_res_id!='') if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id IN ({$string_res_id})" ) ) { ?> <script type="text/javascript"> document.getElementById('ajax_working').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during deleting booking resources at DB' ,__FILE__,__LINE__); ?></div>'; </script> <?php die(); }
                // Season filters
                if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}booking_seasons WHERE users =  %d" , $us_id) ) ) { ?> <script type="text/javascript"> document.getElementById('ajax_working').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php debuge_error('Error during deleting season filters at DB',__FILE__,__LINE__ ); ?></div>'; </script> <?php die(); }
            }

        } else { // Just turn Off user
            update_user_option($us_id, 'booking_is_active','Off');
        }
        ?> <script type="text/javascript">
                var my_message = '<?php echo html_entity_decode( esc_js( __('User  is Deactivated' ,'booking') ),ENT_QUOTES) ; ?>';
                wpbc_admin_show_message( my_message, 'success', 3000 );
        </script> <?php

    }

    // Filter. Modify of some SQL request for having users inside belong request
    function multiuser_modify_SQL_for_current_user( $where ) {

        if ( defined('WP_ADMIN') ) {                                            // If at client side so then return default
            if ( WP_ADMIN !== true )  return $where;
        } else                        return $where;

        $user = wp_get_current_user();

        $user_bk_id = $user->ID;
        if (    ( $this->is_user_super_admin($user_bk_id) )
			||  (  ( defined( 'DOING_AJAX' )   ) && ( DOING_AJAX )  && ( 0 == $user_bk_id ) )   //FixIn: 7.2.1.22	// Exception in TimeLine (when  user not logged in for showing all booking resources
		){
			return $where;          // Standard Way
		}

        $where .=  ' users = ' . $user_bk_id . ' ';
        return $where;

    }

    // Filter. Skip some booking resources at the BK Types, if they do not belong to user
    function multiuser_resource_list($types_list) {

        $user = wp_get_current_user();
        $user_bk_id = $user->ID;
        if ( $this->is_user_super_admin($user_bk_id) )  return $types_list; // Standard Way

        $types_list_new = array();

        foreach ($types_list as $single_type) {
            if ($single_type->users == $user_bk_id) {
                $types_list_new[] = $single_type;
            }
        }

        return $types_list_new;
    }




    // Check if user can be at some admin panel, which belong to specific booking resource
    function multiuser_is_user_can_be_here($blank, $bk_resource_type){
        global $wpdb;

        $user = wp_get_current_user();
        $user_bk_id = $user->ID;

        // 1. Check if user ACTIVE
        if ( $bk_resource_type == 'check_for_active_users' ) {

            if ( $this->is_user_super_admin($user_bk_id) ) return true;         // SuperAdmin

            $is_user_active = get_user_option( 'booking_is_active' , $user_bk_id );

            if ($is_user_active != 'On') {


                ?><div class="clear" style="height:10px;"></div>
                <div class="wpbc-settings-notice notice-warning" style="text-align:left;">
                    <strong><?php _e('Warning!' ,'booking'); ?></strong> <?php
                        printf( __('%sYou do not have permissions for this page.%s Your account is not active, please contact administrator.%s' ,'booking'), '', ' ','')
                    ?>
                </div>
                <div class="clear" style="height:10px;"></div><?php

                return false;                                                   // User is not active
            } else
                return true;                                                    // User is active
        }

        // 2. Check on SUPER ADMIN
        if ($bk_resource_type == 'only_super_admin') {
            return $this->is_user_super_admin($user_bk_id);
        }

        // 2. Check on SUPER ADMIN
        if ($bk_resource_type == 'not_low_level_user') {

            if ($this->is_user_low_level($user_bk_id))   // If user low level then return     FALSE
                return false;
            else                                         // if user NOT low level then return TRUE
                return true;
        }


        // 3. CHECK on RESOURCES (if user can open specific booking resource or not

        if ($bk_resource_type<=0) return true;

        // check for administrator
        if ( $this->is_user_super_admin($user_bk_id) )  return true; // Standard Way

        $wp_q = $wpdb->prepare( "SELECT users FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id = %d",  $bk_resource_type );
        $res = $wpdb->get_results( $wp_q );
        if (  count($res) > 0 ) {
            if ($res[0]->users == $user_bk_id) return true;
            else {

            echo '<div id="no-reservations"  class="warning_message textleft">';
            printf(__('%sYou do not have permissions for this booking resources.%s' ,'booking'), '<h2>', '</h2>');
            echo '</div><div style="clear:both;height:10px;"></div>';

                return false; }
        } else {
            echo '<div id="no-reservations"  class="warning_message textleft">';
            printf(__('%sNo this booking resources.%s' ,'booking'), '<h2>', '</h2>');
            echo '</div><div style="clear:both;height:10px;"></div>';

            return false; } // no such bk resource

    }


    // Set Active user - from now all options will get for this user
    function check_multiuser_params_for_client_side_by_user_id( $user_bk_id ) {
	    $this->client_side_active_params_of_user = intval( $user_bk_id );        //FixIn: 8.4.5.15
    }

    // Set Active user - from now all options will get for this user
    function get_client_side_active_params_of_user(  ) {
        return $this->client_side_active_params_of_user ;
    }

    // Activation of process INIT at CLIENT side options, which belong to USER
    function check_multiuser_params_for_client_side($bk_type_id) {
        global $wpdb;
        $wp_q = $wpdb->prepare( "SELECT users FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id = %d ", $bk_type_id );
        $res = $wpdb->get_results( $wp_q );
        if (  count($res) == 0 ) {
            ?> <script type="text/javascript">
                if (document.getElementById('submiting<?php echo $bk_type_id; ?>') !== null)
                    document.getElementById('submiting<?php echo $bk_type_id; ?>').innerHTML = '<?php debuge_error('Error during searching this booking resources',__FILE__,__LINE__ ); ?>';
            </script> <?php
            return;
        } else {
           $this->check_multiuser_params_for_client_side_by_user_id($res[0]->users);
           //debuge($this->client_side_active_params_of_user);die;
        }

    }

    // Finish activating of Client side belong bkresource
    function finish_check_multiuser_params_for_client_side($bk_type) {
        $this->client_side_active_params_of_user = false;
    }

}       
