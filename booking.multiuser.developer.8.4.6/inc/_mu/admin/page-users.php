<?php /**
 * @version 1.0
 * @package Booking > Settings > Users page
 * @category Settings page
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2016-08-13
 *
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


/**
	 * Show Content
 *  Update Content
 *  Define Slug
 *  Define where to show
 */
class WPBC_Page_Settings__bookingusers extends WPBC_Page_Structure {


    public function in_page() {

        if ( ! wpbc_is_mu_user_can_be_here( 'only_super_admin' ) ) {            // If this User not "super admin",  then  do  not load this page at all
            return (string) rand(100000, 1000000);
        }

        return 'wpbc-settings';
    }


    public function tabs() {

        $tabs = array();

        $tabs[ 'users' ] = array(
                              'title'       => __('Users', 'booking')            // Title of TAB
                            , 'hint'        => __('Manage Users', 'booking')                      // Hint
                            , 'page_title'  => __('Users Settings', 'booking')                                // Title of Page
                            //, 'link'      => ''                                 // Can be skiped,  then generated link based on Page and Tab tags. Or can  be extenral link
                            //, 'position'  => 'left'                             // 'left'  ||  'right'  ||  ''
                            //, 'css_classes'=> ''                                // CSS class(es)
                            //, 'icon'      => ''                                 // Icon - link to the real PNG img
                            , 'font_icon' => 'glyphicon glyphicon-user'           // CSS definition  of forn Icon
                            , 'default'   => false                              // Is this tab activated by default or not: true || false.
                            //, 'disabled'  => false                              // Is this tab disbaled: true || false.
                            //, 'hided'     => ( !class_exists('wpdev_bk_biz_m') ) ? false : true                              // Is this tab hided: true || false.
                            , 'subtabs'   => array()
                    );

        /*
        $subtabs = array();
        $subtabs[ 'gcal' ] = array(
                            'type' => 'subtab'                                  // Required| Possible values:  'subtab' | 'separator' | 'button' | 'goto-link' | 'html'
                            , 'title' => __('Google Calendar' ,'booking') . '  - ' . __('Events Import' ,'booking')         // Title of TAB
                            , 'page_title' => __('Google Calendar' ,'booking') . ' ' . __('Settings' ,'booking')    // Title of Page
                            , 'hint' => __('Customization of synchronization with Google Calendar' ,'booking')      // Hint
                            , 'link' => ''                                      // link
                            , 'position' => ''                                  // 'left'  ||  'right'  ||  ''
                            , 'css_classes' => ''                               // CSS class(es)
                            //, 'icon' => 'http://.../icon.png'                 // Icon - link to the real PNG img
                            //, 'font_icon' => 'glyphicon glyphicon-envelope'   // CSS definition of Font Icon
                            , 'default' =>  true                                // Is this sub tab activated by default or not: true || false.
                            , 'disabled' => false                               // Is this sub tab deactivated: true || false.
                            , 'checkbox'  => false                              // or definition array  for specific checkbox: array( 'checked' => true, 'name' => 'feature1_active_status' )   //, 'checkbox'  => array( 'checked' => $is_checked, 'name' => 'enabled_active_status' )
                            , 'content' => 'content'                            // Function to load as conten of this TAB
                        );
        $tabs[ 'users' ]['subtabs'] = $subtabs;
        */

        return $tabs;
    }


    /** Show Content of Settings page */
    public function content() {

        $this->css();

        ////////////////////////////////////////////////////////////////////////
        // Checking
        ////////////////////////////////////////////////////////////////////////

        do_action( 'wpbc_hook_settings_page_header', 'bookingusers');              // Define Notices Section and show some static messages, if needed

        if ( ! wpbc_is_mu_user_can_be_here('activated_user') ) return false;    // Check if MU user activated, otherwise show Warning message.

        if ( ! wpbc_is_mu_user_can_be_here('only_super_admin') ) return false;  // User is not Super admin, so exit.  Basically its was already checked at the bottom of the PHP file, just in case.


        ////////////////////////////////////////////////////////////////////////
        // Load Data
        ////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////
        //  S u b m i t   Main Form
        ////////////////////////////////////////////////////////////////////////

        $submit_form_name = 'wpbc_bookingusers';                         // Define form name

        // $this->get_api()->validated_form_id = $submit_form_name;             // Define ID of Form for ability to  validate fields (like required field) before submit.

        if ( isset( $_POST['is_form_sbmitted_'. $submit_form_name ] ) ) {

            // Nonce checking    {Return false if invalid, 1 if generated between, 0-12 hours ago, 2 if generated between 12-24 hours ago. }
            $nonce_gen_time = check_admin_referer( 'wpbc_settings_page_' . $submit_form_name );  // Its stop show anything on submiting, if its not refear to the original page

            // Save Changes
            $this->update();
        }


        ////////////////////////////////////////////////////////////////////////
        // JavaScript: Tooltips, Popover, Datepick (js & css)
        ////////////////////////////////////////////////////////////////////////

        echo '<span class="wpdevelop">';

        wpbc_js_for_bookings_page();

        echo '</span>';

        ?><div class="clear"></div><?php

        wpbc_toolbar_search_by_id__top_form( array(
                                                    'search_form_id' => 'wpbc_booking_users_search_form'
                                                  , 'search_get_key' => 'wh_user_id'
                                                  , 'is_pseudo'      => false
                                            ) );

        ////////////////////////////////////////////////////////////////////////
        // Content  ////////////////////////////////////////////////////////////
        ?>
        <div class="clear" style="margin-bottom:0px;"></div>
        <span class="metabox-holder">
            <form  name="<?php echo $submit_form_name; ?>" id="<?php echo $submit_form_name; ?>" action="" method="post" autocomplete="off">
                <?php
                   // N o n c e   field, and key for checking   S u b m i t
                   wp_nonce_field( 'wpbc_settings_page_' . $submit_form_name );

                // Add hidden input SEARCH KEY field into  main form, if previosly was searching by ID or Title
                wpbc_hidden_search_by_id_field_in_main_form( array( 'search_get_key' => 'wh_user_id' ) );			//FixIn: 8.0.1.12

                ?><input type="hidden" name="is_form_sbmitted_<?php echo $submit_form_name; ?>" id="is_form_sbmitted_<?php echo $submit_form_name; ?>" value="1" /><?php
                ?><div class="clear"></div><?php
                ?><input type="hidden" name="wpbc_action" id="wpbc_action" value="" /><?php
                ?><div class="clear" style="height:10px;"></div>
                <?php echo ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() : '' ); ?>
                <div class="clear" style="height:10px;"></div>

                <div id="wpbc_booking_resource_table" class="wpbc_settings_row wpbc_settings_row_rightNO"><?php

                    // wpbc_open_meta_box_section( 'wpbc_settings_bookingusers_bookingusers', __('Resources', 'booking') );

                        $this->wpbc_bookingusers_table__show();

                    // wpbc_close_meta_box_section();
                ?>
                </div>
                <div class="clear"></div>
                <input type="submit" value="<?php _e('Save Changes','booking'); ?>" class="button button-primary wpbc_submit_button" />
            </form>
        </span>
        <?php

        do_action( 'wpbc_hook_settings_page_footer', 'bookingusers' );

        $this->enqueue_js();
    }



    /** Save Chanages */
    public function update() {

//debuge('$_POST',$_POST);


        // if (  ( wpbc_is_this_demo() ) ||  ( ! class_exists( 'wpdev_bk_personal' ) )  ) return;           // No Saving in Demo

        global $wpdb;

        $wpbc_users_table         = new WPBC_Users_Table( 'bookingusers_submit' );
        $linear_data_for_one_page = $wpbc_users_table->get_linear_data_for_one_page();

        switch ( $_POST['wpbc_action'] ) {

            case 'activate' :
                    if ( ! wpbc_is_this_demo() )
                        foreach ( $linear_data_for_one_page as $us_data ) {

                            $us_id = $us_data['id'];

                            if ( isset( $_POST['br-select-' . $us_id ] ) ) {    // Check posts of only selected users

                                make_bk_action( 'wpbc_reactivate_user', $us_id );
                                // $this->reactivate_user( $us_id );               // Activate here user - Its mean set default settings for this user, and then refresh page with normal link


                                $user_name = get_userdata( $us_id );
                                $user_name = $user_name->display_name;
                                wpbc_show_message ( '<strong>[' . $user_name . ']</strong>. ' . __('User  is Activated','booking') . '.', 5 );
                            }
                        }
                break;

            case 'deactivate' :
            case 'delete_settings' :
            case 'delete_data' :
                    if ( ! wpbc_is_this_demo() )
                        foreach ( $linear_data_for_one_page as $us_data ) {

                            $us_id = $us_data['id'];

                            if ( isset( $_POST['br-select-' . $us_id ] ) ) {    // Check posts of only selected users

                                $is_delete_user_bookings = ( ( $_POST['wpbc_action'] == 'delete_data' )     ? true : false );

                                if ( $is_delete_user_bookings )
                                    $is_delete_user_options = true;
                                else
                                    $is_delete_user_options  = ( ( $_POST['wpbc_action'] == 'delete_settings' ) ? true : false );


                                make_bk_action( 'wpbc_deactivate_user', $us_id ,$is_delete_user_options, $is_delete_user_bookings);
                                // $this->deactivate_user($_GET['deactivate_user'],$is_delete_user_options, $is_delete_user_bookings);

                                $user_name = get_userdata( $us_id );
                                $user_name = $user_name->display_name;
                                wpbc_show_message ( '<strong>[' . $user_name . ']</strong>. ' . __('User  is Deactivated','booking') . '.', 5 );
                            }
                        }
                break;

            case 'set_user_super':
                    if ( ! wpbc_is_this_demo() )
                        foreach ( $linear_data_for_one_page as $us_data ) {

                            $us_id = $us_data['id'];

                            if ( isset( $_POST['br-select-' . $us_id ] ) ) {    // Check posts of only selected users

                                update_user_option( $us_id, 'booking_user_role', 'super_admin' );

                                $user_name = get_userdata( $us_id );
                                $user_name = $user_name->display_name;
                                wpbc_show_message ( '<strong>[' . $user_name . ']</strong> - ' . __('Super Admin','booking') . '.', 5 );
                            }
                        }



                break;

            case 'low_level_user':
                if ( ! wpbc_is_this_demo() )
                        foreach ( $linear_data_for_one_page as $us_data ) {

                            $us_id = $us_data['id'];

                            if ( isset( $_POST['br-select-' . $us_id ] ) ) {    // Check posts of only selected users

                                update_user_option( $us_id, 'booking_user_role', 'low_level_user' );
                                $user_name = get_userdata( $us_id );
                                $user_name = $user_name->display_name;
                                wpbc_show_message ( '<strong>[' . $user_name . ']</strong> - Low level user.', 5 );
                            }
                        }
                break;

            case 'set_user_regular':
                    if ( ! wpbc_is_this_demo() )
                        foreach ( $linear_data_for_one_page as $us_data ) {

                            $us_id = $us_data['id'];

                            if ( isset( $_POST['br-select-' . $us_id ] ) ) {    // Check posts of only selected users

                                update_user_option( $us_id, 'booking_user_role', 'regular' );
                                $user_name = get_userdata( $us_id );
                                $user_name = $user_name->display_name;
                                wpbc_show_message ( '<strong>[' . $user_name . ']</strong> - ' . __('Regular User','booking') . '.', 5 );
                            }
                        }
                break;

            default:                                                            // Save some data
                        foreach ( $linear_data_for_one_page as $us_data ) {

                            $us_id = $us_data['id'];

                            // Check posts of only visible on page booking bookingusers
                            if ( isset( $_POST['max_resources_' . $us_id ] ) ) {

                                // Validate POST value
                                $validated_value = intval( WPBC_Settings_API::validate_text_post_static( 'max_resources_' . $us_id ) );

                                // Check  if its different from  original value in DB
                                if ( $validated_value != $us_data['max_res'] ) {

                                    update_user_option( $us_id, 'booking_max_num_of_resources', $validated_value );

                                    /**
                                    // Save to DB
                                    if ( false === $wpdb->query(
                                            $wpdb->prepare(
                                                            "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE booking_type_id = %d "
                                                            , $validated_value
                                                            , intval($us_id) )
                                                        )
                                        ){ debuge_error( 'Error saving to DB' ,__FILE__ , __LINE__); }
                                    */
                                }
                            }
                        }
                        wpbc_show_changes_saved_message();
                break;
        }

        make_bk_action( 'wpbc_reinit_users_cache' );
        make_bk_action( 'wpbc_reinit_booking_users_data' );



        /**
        // Get Validated Email fields
        $validated_fields = $this->get_api()->validate_post();
        $validated_fields = apply_filters( 'wpbc_fields_before_saving_to_db__bookingusers', $validated_fields );   //Hook for validated fields.
//debuge($validated_fields);
        $this->get_api()->save_to_db( $validated_fields );
        */

        // update_bk_option( 'booking_cache_expiration' , WPBC_Settings_API::validate_text_post_static( 'booking_cache_expiration' ) );     // Old way of saving:
    }



    // <editor-fold     defaultstate="collapsed"                        desc=" CSS  &   JS   "  >

    /** CSS for this page */
    private function css() {
        ?>
        <style type="text/css">
            .wpbc-help-message {
                border:none;
            }
            @media (max-width: 399px) {
            }
        </style>
        <?php
    }



    /**
	 * Add Custon JavaScript - for some specific settings options
     *      Executed After post content, after initial definition of settings,  and possible definition after POST request.
     *
     * @param type $menu_slug
     */
    private function enqueue_js(){

        // JavaScript //////////////////////////////////////////////////////////////

        $js_script = '';

        /*
        //Show|Hide grayed section
        $js_script .= "
                        if ( ! jQuery('#bookingusers_booking_gcal_auto_import_is_active').is(':checked') ) {
                            jQuery('.wpbc_tr_auto_import').addClass('hidden_items');
                        }
                      ";
        // Hide|Show  on Click      Checkbox
        $js_script .= " jQuery('#bookingusers_booking_gcal_auto_import_is_active').on( 'change', function(){
                                if ( this.checked ) {
                                    jQuery('.wpbc_tr_auto_import').removeClass('hidden_items');
                                } else {
                                    jQuery('.wpbc_tr_auto_import').addClass('hidden_items');
                                }
                            } ); ";
        // Hide|Show  on Click      Radion
        $js_script .= " jQuery('input[name=\"paypal_pro_hosted_solution\"]').on( 'change', function(){
                                jQuery('.wpbc_sub_settings_paypal_account_type').addClass('hidden_items');
                                if ( jQuery('#paypal_type_standard').is(':checked') ) {
                                    jQuery('.wpbc_sub_settings_paypal_standard').removeClass('hidden_items');
                                } else {
                                    jQuery('.wpbc_sub_settings_paypal_pro_hosted').removeClass('hidden_items');
                                }
                            } ); ";
        */
        ////////////////////////////////////////////////////////////////////////


        // Eneque JS to  the footer of the page
        wpbc_enqueue_js( $js_script );
    }

    // </editor-fold>



    ////////////////////////////////////////////////////////////////////////////
    //   B o o k i n g      R e s o u r c e s      T a b l e
    ////////////////////////////////////////////////////////////////////////////

    /** Show bookingusers table */
    public function wpbc_bookingusers_table__show() {
		
		

        // echo ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() . '<div class="clear" style="height:20px;"></div>' : '' );

        $wpbc_users_table = new WPBC_Users_Table(
                            'users'
                            , array(
                                  'url_sufix'   =>  '#wpbc_users_link'
                                , 'rows_func'   =>  array( $this, 'wpbc_users_table__show_rows' )
                                , 'columns'     =>  array(
                                                            'check' => array(     'title' => '<input type="checkbox" value="" id="br-select-all" name="resource_id_all" />'
                                                                                , 'class' => 'check-column wpbc_hide_mobile'
                                                                        )
                                                            , 'id' => array(      'title' => __( 'ID' )
                                                                                , 'style' => 'width:5em;'
                                                                                , 'class' => 'wpbc_hide_mobile'
                                                                                , 'sortable' => true
                                                                        )
                                                            , 'title' => array(   'title' => __( 'Users', 'booking' )
                                                                                , 'style' => 'width:12em;'
                                                                                , 'sortable' => true
                                                                            )
                                                            , 'status' => array(  'title' => __( 'Status', 'booking' )
                                                                                , 'class' => 'wpbc_hide_mobile'
                                                                                , 'style' => 'width:7em;'
                                                                            )
                                                            , 'role' => array(    'title' => __( 'User Role', 'booking' )
                                                                                , 'class' => 'wpbc_hide_mobile'
                                                                                , 'style' => 'width:9em;'
                                                                                , 'sortable' => true
                                                                            )
                                                            , 'resources' => array(   'title' => __( 'Resources', 'booking' )
                                                                                , 'style' => 'width:7em;'
                                                                            )
                                                            , 'actions' => array( 'title' => __( 'Actions', 'booking' )
                                                                                , 'style' => 'text-align:center;'
                                                                            )
                                                            , 'set_as' => array(  'title' => ucwords( __( 'Set user as', 'booking' ) )
                                                                                , 'style' => 'width:5em;text-align:center;'
                                                                            )
                                                        )
                                , 'is_show_pseudo_search_form' => false
                            )
                        );

        $wpbc_users_table->display();

    }

//TODO: Set correct  buttons actions

    /**
	 * Show rows for booking resource table
     *
     * @param int $row_num
     * @param array $user
     */
	

	
    public function wpbc_users_table__show_rows( $row_num, $user ) {


	
//debuge($user);

        $admin_url = admin_url('user-edit.php?user_id=' . $user['id' ] ) ;

        $meta_value                 = maybe_unserialize( $user['role'] );

        /**
	 * We are getting all  these data from  SQL request  at the wpbc-users-table.php file
        //
        // $booking_max_num_res        = get_user_option( 'booking_max_num_of_resources', $user['id'] );
        // $is_booking_active_for_user = get_user_option( 'booking_is_active' , $user['id'] );
        // $is_user_super_admin        = apply_bk_filter('is_user_super_admin', $user['id'] );
        */

        $booking_max_num_res        = $user['max_res'];
        $is_booking_active_for_user = $user['active'];
        $is_user_super_admin        = ( ( $user['super'] == 'super_admin' ) ? true : false );

        $default_super_admin_id_arr = apply_bk_filter( 'get_default_super_admin_id' );  // Get list  of Super booking admin users,  that  defined by  default - e.g. uer  with  ID = 1
        if ( in_array( $user['id' ], $default_super_admin_id_arr ) )
                $is_user_super_admin = true;                                    // User ID inside of Super Admin ID


        ?><tr class="wpbc_row" id="resource_<?php echo $user['id']; ?>"><?php //print_r($user);

                  ?><th class="check-column wpbc_hide_mobile">
                            <label class="screen-reader-text" for="br-select-<?php echo $user['id' ]; ?>"><?php echo esc_js(__('Select Booking Resource', 'booking')); ?></label>
                            <input type="checkbox"
                                           id="br-select-<?php echo $user['id' ]; ?>"
                                           name="br-select-<?php echo $user['id' ]; ?>"
                                           value="resource_<?php echo $user['id' ]; ?>"
                                />
                    </th>
                    <td class="wpbc_hide_mobile"><?php
                        echo $user['id' ]; ?>
                    </td>
                    <td>
                        <strong><a href="<?php echo $admin_url; ?>"><?php  echo esc_js( $users['display_name'] );  ?></a></strong>
                    </td>
                    <td class="wpbc_hide_mobile"><?php

                        echo wpbc_get_user_status( $user );
/*                        if ( ( $is_booking_active_for_user == 'On') || ( $is_user_super_admin ) ) {
                            if ( $is_user_super_admin ){
                                ?><span class="label label-default label-info" style="font-weight:600;background-color: #d53;"><?php
                                    _e('Super Admin' ,'booking');
                                ?></span><?php
                            } else {
                                ?><span class="label label-default label-info" style="font-weight:600;background-color: #39d;"><?php
                                    _e('Regular User' ,'booking');
                                ?></span><?php
                            }
                        } else {
                                ?><span class="label label-default label-info" style="font-weight:400;background-color: #89a;color:#fdfdfd;"><?php
                                    _e('Inactive User' ,'booking');
                                ?></span><?php
                        }
*/
                        ////////////////////////////////////////////////////////
                        ?>
                    </td>
                    <td class="wpbc_hide_mobile"><?php

                        if (  ( isset( $meta_value['subscriber'] ) ) &&  ( $meta_value['subscriber'] == 1 )  )          $u_access = 'Subscriber';
                        if (  ( isset( $meta_value['supervisor'] ) ) &&  ( $meta_value['supervisor'] == 1 )  )          $u_access = 'Supervisor';
                        if (  ( isset( $meta_value['contributor'] ) ) &&  ( $meta_value['contributor'] == 1 )  )        $u_access = 'Contributor';
                        if (  ( isset( $meta_value['author'] ) ) && ( $meta_value['author'] == 1 )  )                   $u_access = 'Author';
                        if (  ( isset( $meta_value['editor'] ) ) && ( $meta_value['editor'] == 1 )  )                   $u_access = 'Editor';
                        if (  ( isset( $meta_value['administrator'] ) ) && ( $meta_value['administrator'] == 1 )  )     $u_access = '<strong>Administrator</strong>';

                        ?> <span class="label label-default label-info" style="font-weight:400;"><?php echo $u_access; ?></span> <?php

                    ?></td>
                    <td>
                        <input type="text"
                               <?php if ( $is_user_super_admin ) { ?>disabled="DISABLED" value="<?php _e('Unlimited' ,'booking');?>" style="color:#bbb;" <?php } else { ?>
                                value="<?php echo esc_js( $booking_max_num_res ); ?>"
                               <?php } ?>
                               id="max_resources_<?php echo $user['id' ]; ?>"
                               name="max_resources_<?php echo $user['id' ]; ?>"
                               class="large-text"
                        />
                    </td>
                    <td style="padding-left:2em;" >
                        <?php if ( ! $is_user_super_admin ) { ?>
                            <?php if ( $is_booking_active_for_user != 'On') { ?>
                                <a class="button" href="javascript:void(0);" onclick="javascript: var answer = confirm('<?php
                                                                        echo esc_js( __('Do you really want' ,'booking')
                                                                                    . ' ' . __('make user active' ,'booking') );  ?>?'); if (! answer){ return false; }
                                                                    jQuery('.check-column input[type=\'checkbox\']').prop('checked', false);
                                                                    jQuery('#br-select-<?php echo $user['id' ]; ?>').prop('checked', true);
                                                                    jQuery('#wpbc_action').val('activate');
                                                                    jQuery('#wpbc_bookingusers').submit();"><?php _e('Activate' ,'booking'); ?></a>
                            <?php } else { ?>
                                <a class="button" href="javascript:void(0);" onclick="javascript: var answer = confirm('<?php
                                                                    echo esc_js( __('Do you really want' ,'booking')
                                                                                . ' ' . __('make user inactive' ,'booking') );  ?>?'); if (! answer){ return false; }jQuery('.check-column input[type=\'checkbox\']').prop('checked', false);
                                                                    jQuery('#br-select-<?php echo $user['id' ]; ?>').prop('checked', true);
                                                                    jQuery('#wpbc_action').val('deactivate');
                                                                    jQuery('#wpbc_bookingusers').submit();"><?php _e('Deactivate' ,'booking'); ?></a>
                                <a class="button" href="javascript:void(0);" onclick="javascript: var answer = confirm('<?php
                                                                        echo esc_js( __('Do you really want' ,'booking')
                                                                                    . ' ' . __('delete configuration' ,'booking') );  ?>?'); if (! answer){ return false; }
                                                                    jQuery('.check-column input[type=\'checkbox\']').prop('checked', false);
                                                                    jQuery('#br-select-<?php echo $user['id' ]; ?>').prop('checked', true);
                                                                    jQuery('#wpbc_action').val('delete_settings');
                                                                    jQuery('#wpbc_bookingusers').submit();"><?php echo ucwords( __('Delete settings' ,'booking') ); ?></a>
                                <a class="button" href="javascript:void(0);" onclick="javascript: var answer = confirm('<?php
                                                                        echo esc_js( __('Do you really want' ,'booking')
                                                                                    . ' ' . __('delete all booking data' ,'booking') );  ?>?'); if (! answer){ return false; }
                                                                    jQuery('.check-column input[type=\'checkbox\']').prop('checked', false);
                                                                    jQuery('#br-select-<?php echo $user['id' ]; ?>').prop('checked', true);
                                                                    jQuery('#wpbc_action').val('delete_data');
                                                                    jQuery('#wpbc_bookingusers').submit();"><?php echo ucwords( __('Delete data' ,'booking') ); ?></a>
                            <?php } ?>

                        <?php } ?>
                    </td>
                    <td>
                        <?php if ( ! $is_user_super_admin ) { ?>
                                <a class="button button-primary" href="javascript:void(0);" onclick="javascript: var answer = confirm('<?php
                                                                        echo esc_js( __('Do you really want' ,'booking')
                                                                                    . ' ' . strtolower( __( 'Set user as', 'booking' ) ) . ' ' . __('Super Admin' ,'booking') );  ?>?'); if (! answer){ return false; }
                                                                            jQuery('.check-column input[type=\'checkbox\']').prop('checked', false);
                                                                            jQuery('#br-select-<?php echo $user['id' ]; ?>').prop('checked', true);
                                                                            jQuery('#wpbc_action').val('set_user_super');
                                                                            jQuery('#wpbc_bookingusers').submit();"><?php _e('Super Admin' ,'booking'); ?></a>
                        <?php } else { ?>
                                <a class="button" href="javascript:void(0);" onclick="javascript: var answer = confirm('<?php
                                                                        echo esc_js( __('Do you really want' ,'booking')
                                                                                    . ' ' . strtolower( __( 'Set user as', 'booking' ) ) . ' ' . __('Regular User' ,'booking') );  ?>?'); if (! answer){ return false; }
                                                           jQuery('.check-column input[type=\'checkbox\']').prop('checked', false);
                                                           jQuery('#br-select-<?php echo $user['id' ]; ?>').prop('checked', true);
                                                           jQuery('#wpbc_action').val('set_user_regular');
                                                           jQuery('#wpbc_bookingusers').submit();"><?php _e('Regular User' ,'booking'); ?></a>
                        <?php } ?>
                    </td>
        </tr>
        <?php
    }

}
add_action('wpbc_menu_created', array( new WPBC_Page_Settings__bookingusers() , '__construct') );    // Executed after creation of Menu
