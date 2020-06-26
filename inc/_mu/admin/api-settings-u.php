<?php
/**
 * @version     1.0
 * @package     General Settings API - Saving different options
 * @category    Settings API
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


/**
	 * Genereal Settings ( Calendar ) - Range Days Selection
 * 
 * @param array $fields 
 * @return array
 */
/*
function wpbc_settings_calendar_range_days_selection__mu( $fields, $default_options_values ) {
    
    $field_options = array( '' => ' - ' );
    foreach ( range( 365, 1, 1) as $value ) {
        $field_options[ $value ] = $value;
    }
   
    $fields['booking_available_days_num_from_todayS'] = array(   
                            'type'          => 'select'
                            , 'default' => ''            
                            , 'title'       => __('!!! Limit available days from today', 'booking')
                            , 'description' => __('Select number of available days in calendar start from today.' ,'booking')
                            , 'options'     => $field_options
                            , 'group'       => 'calendar'
                    );

    return $fields;
}
add_filter('wpbc_settings_calendar_range_days_selection', 'wpbc_settings_calendar_range_days_selection__mu' ,10, 2);
*/



/**
 * Set using CUSTOM_FORMS_FOR_REGULAR_USERS - Settings ( Advanced ) page
 *
 * @param array $fields
 * @return array
 */
function wpbc_settings_custom_forms_for_regular_users__mu( $fields, $default_options_values ) {

	//FixIn: 8.1.3.19

    $fields['booking_is_custom_forms_for_regular_users'] = array(
                            'type'          => 'checkbox'
                            , 'default'     => $default_options_values['booking_is_custom_forms_for_regular_users']   //'Off'
                            , 'title'       => __('Activate custom booking forms for regular users' ,'booking')
                            , 'label'       => __('Check this box if you want to use multiple custom booking forms for activated regular users' ,'booking')
                            , 'description' => ''
                            , 'group'       => 'advanced'
							//, 'tr_class'    => 'wpbc_sub_settings_grayed'
        );

    return $fields;
}
add_filter('wpbc_settings_edit_url_hash', 'wpbc_settings_custom_forms_for_regular_users__mu' ,10, 2);


////////////////////////////////////////////////////////////////////////////////
// Show Fields in Table
////////////////////////////////////////////////////////////////////////////////

/**
	 * Add Column to Resources Table -- Header
 * 
 * @param array $columns
 * @return array
 */
function wpbc_resources_table_header__user_title__mu( $columns ) {    
    
    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );    
    if ( ! $is_can ) return $columns;
    
    
    $columns[ 'users' ] = array(   
                                  'title'   => __( 'User', 'booking' ) 
                                , 'style'   => 'width:12em;text-align:center;'
                                , 'class'   => 'wpbc_hide_mobile'        
                                , 'sortable'=> true 
                        );
    return $columns;
}
add_filter( 'wpbc_resources_table_header__user_title',      'wpbc_resources_table_header__user_title__mu', 10, 1 );   // Hook for showing header in  resources table
add_filter( 'wpbc_seasonfilters_table_header__user_title',  'wpbc_resources_table_header__user_title__mu', 10, 1 );   // Hook for showing header in  season filters  table
add_filter( 'wpbc_discountcoupons_table_header__user_title','wpbc_resources_table_header__user_title__mu', 10, 1 );   // Hook for showing header in  coupons table


//Maybe: to show button  with  selection of users for prevent of too many options...
/**
	 * Show Column in Resources Table - ROW
 * 
 * @param int $row_num
 * @param array $resource
 */
function wpbc_resources_table_show_col__user_field__mu( $row_num, $resource ) {
    
    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );    
    if ( ! $is_can ) return;


    $wpbc_users_cache = wpbc_users_cache();    
    
    $wpbc_users_cache->set_sorting_params( 'ID', 'ASC' );
    
    $all_users = $wpbc_users_cache->get_activated_users_only();                 // Data loaded only  once on first  request,  next  its getted from  static varibale

    ?><td class="wpbc_hide_mobile"><?php                                                                 // DropDown list with Custom Forms 
    
    $select_options = array();

    foreach ( $all_users as $single_user ) {

        //$single_user = get_object_vars( $single_user );
        
        $single_user['role'] = maybe_unserialize($single_user['role']);
        


        $single_user['role'] = array_keys($single_user['role']);
        $single_user['role'] = implode( ',',  $single_user['role'] );

                
        $select_options[ $single_user['id'] ] = array(  
                                                    'title'      => $single_user['display_name'] . ' [' . $single_user['role'] . ']'
                                                    , 'selected' => ( (isset( $resource['users'] )) && ($resource['users'] == $single_user['id'] ) ) ? true : false
                                                );
    }
    
    // Mark selectbox with  different color,  if selected user for booking resources different from current logged in user
    $logged_in_user = wp_get_current_user();
    $logged_in_user_id = $logged_in_user->ID;        
    if ( $logged_in_user_id != $resource['users'] )
        $style = 'color:#d70;';
    else 
        $style = '';
    
    ?><select autocomplete="off" id="booking_resource_users_<?php echo $resource['id' ]; ?>" 
                                 name="booking_resource_users_<?php echo $resource['id' ]; ?>"    
                                 style="width:100%;<?php echo $style; ?>"
        ><?php  

            foreach ( $select_options as $option_value => $option_data ) {

                ?><option value="<?php echo esc_attr( $option_value ); ?>"      <?php if ( $option_value == $logged_in_user_id ) { echo ' style="font-weight:600;" '; } ?>
                    <?php selected(  $option_data['selected'], true ); ?> 
                ><?php echo esc_attr( $option_data['title'] ); ?></option><?php 
            }
        
    ?></select><?php    
    
    ?></td><?php 
}
add_action( 'wpbc_resources_table_show_col__user_field',        'wpbc_resources_table_show_col__user_field__mu', 10, 2 );
add_action( 'wpbc_seasonfilters_table_show_col__user_field',    'wpbc_resources_table_show_col__user_field__mu', 10, 2 );
add_action( 'wpbc_discountcoupons_table_show_col__user_field',  'wpbc_resources_table_show_col__user_field__mu', 10, 2 );


function wpbc_resources_table_show_col__user_text__mu( $row_num, $resource ) {
    
    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );    
    if ( ! $is_can ) return;


    $wpbc_users_cache = wpbc_users_cache();    
    
    $wpbc_users_cache->set_sorting_params( 'ID', 'ASC' );
    
    $all_users = $wpbc_users_cache->get_activated_users_only();                 // Data loaded only  once on first  request,  next  its getted from  static varibale

    ?><td class="wpbc_hide_mobile" style="text-align:center;"><?php                                                                 // DropDown list with Custom Forms 
    
    $user_text = '';
    $select_options = array();
//debuge($all_users);
    foreach ( $all_users as $single_user ) {

        //$single_user = get_object_vars( $single_user );
        
        $single_user['role'] = maybe_unserialize($single_user['role']);
        


        $single_user['role'] = array_keys($single_user['role']);
        $single_user['role'] = implode( ',',  $single_user['role'] );

                
        $select_options[ $single_user['id'] ] = array(  
                                                    'title'      => $single_user['display_name'] .  ' &nbsp; ' . wpbc_get_user_status( $single_user )  //' [' . $single_user['role'] . ']'
                                                    , 'selected' => ( (isset( $resource['users'] )) && ($resource['users'] == $single_user['id'] ) ) ? true : false
                                                );
        
        if (  $select_options[ $single_user['id'] ][ 'selected' ]  )
            $user_text = $select_options[ $single_user['id'] ]['title'];
    }
    
    // Mark selectbox with  different color,  if selected user for booking resources different from current logged in user
    $logged_in_user = wp_get_current_user();
    $logged_in_user_id = $logged_in_user->ID;        
    if ( $logged_in_user_id != $resource['users'] )
        $style = 'color:#d70;';
    else 
        $style = '';
    
    ?><span  class="wpbc_user_col_text" style="width:100%;<?php echo $style; ?>" ><?php 
    
        echo $user_text;  
        
    ?></span><?php    
    
    ?></td><?php 
}
add_action( 'wpbc_resources_table_show_col__user_text',  'wpbc_resources_table_show_col__user_text__mu', 10, 2 );
add_action( 'wpbc_seasonfilters_table_show_col__user_text',  'wpbc_resources_table_show_col__user_text__mu', 10, 2 );


////////////////////////////////////////////////////////////////////////////////
// SQL -  Updating | Inserting
////////////////////////////////////////////////////////////////////////////////

/**
	 * Update SQL during Saving data at Booking > Resources page - Here we are getting User from COLLUMN
 * 
 * @param array $sql            array(
                                                            'sql' => array(
                                                                                  'start'   => "UPDATE {$wpdb->prefix}bookingtypes SET "
                                                                                , 'params' => array( 'title = %s' )
                                                                                , 'end'    => " WHERE booking_type_id = %d"
                                                                        )
                                                            , 'values' => array(
                                                                                  $validated_value 
                                                                        )
                                                        )
 * @param int $resource_id
 * @param array $resource
 * @return string - updated SQL
 */
function wpbc_resources_table__update_sql_array__mu( $sql, $resource_id, $resource ) {

    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );    
    if ( ! $is_can ) return $sql;
    
    // Validate User
    $validated_default_user = WPBC_Settings_API::validate_text_post_static( 'booking_resource_users_' . $resource_id );
    $validated_default_user = intval($validated_default_user);
        
    $sql['sql']['params'][] = 'users = %d';
    $sql['values'][]        = $validated_default_user;
    
    return $sql;
}
add_filter( 'wpbc_resources_table__update_sql_array',       'wpbc_resources_table__update_sql_array__mu', 10, 3 );       // Hook for updating validates users field
add_filter( 'wpbc_seasonfilters_table__update_sql_array',   'wpbc_resources_table__update_sql_array__mu', 10, 3 );   
add_filter( 'wpbc_discountcoupons_table__update_sql_array', 'wpbc_resources_table__update_sql_array__mu', 10, 3 );   


/**
	 * Update SQL during Inserting data at Booking > Resources page
 * 
 * @param array $sql                                array(
                                                            'sql'       => array(
                                                                                  'start'      => "INSERT INTO {$wpdb->prefix}bookingtypes "
                                                                                , 'params'     => array( 'title' )    
                                                                                , 'param_types' => array( '%s' )    
                                                                        )
                                                            , 'values'  => array( $validated_title . $sufix )
                                                    )
 * @param array $params                             array( 'sufix' => $sufix )
 * @return array - updated SQL
 */
function wpbc_resources_table__add_new_sql_array__mu( $sql, $params ) {  
    
    $user = wp_get_current_user();
    $user_id = $user->ID;
        
    
    $resources_cache = wpbc_br_cache();                                         // Get booking resources from  cache        
    $resource_list = $resources_cache->get_single_parent_resources();

    // Parent
    $validated_parent = WPBC_Settings_API::validate_text_post_static( 'select_booking_resource' );
    $validated_parent = intval( $validated_parent );
    // Get User owner of parent resource,  if we submit child booking resources 
    if (   ( ! empty( $validated_parent ) ) && ( isset( $resource_list[ $validated_parent ] ) )   ){
        $user_id = intval( $resource_list[ $validated_parent ][ 'users' ] );
    }
    
    
    $sql['sql']['params'][]      = 'users';
    $sql['sql']['param_types'][] = '%d';
    $sql['values'][]             = $user_id;
    
    return $sql;
}
add_filter( 'wpbc_resources_table__add_new_sql_array', 'wpbc_resources_table__add_new_sql_array__mu', 10, 2 );   // Hook for validated fields.


/**
	 * Update SQL during Inserting data at Booking > Resources > Filters page      - Here we are getting User  as Active Current User
 * 
 * @param array $sql                                array(
                                                            'sql'       => array(
                                                                                  'start'      => "INSERT INTO {$wpdb->prefix}booking_seasons "
                                                                                , 'params'     => array( 'title' , 'filter' )    
                                                                                , 'param_types' => array( '%s' , '%s' )    
                                                                        )
                                                            , 'values'  => array( $validated_title , $ser_filter )
                                                        )
 * @return array - updated SQL
 */
function wpbc_add_current_users_column__sql_insert_array( $sql ) {
        
    $user = wp_get_current_user();
    $user_id = $user->ID;
        
    $sql['sql']['params'][]      = 'users';
    $sql['sql']['param_types'][] = '%d';
    $sql['values'][]             = $user_id;
    
    return $sql;
}
add_filter( 'wpbc_seasonfilters_table__add_new_sql_array',      'wpbc_add_current_users_column__sql_insert_array', 10, 1 );   // Hook for validated fields.
add_filter( 'wpbc_discountcoupons_table__add_new_sql_array',    'wpbc_add_current_users_column__sql_insert_array', 10, 1 );   
             

/**
	 * Update SQL during Updating data at Booking > Resources > Filters page      - Here we are getting User  as Active Current User
 * 
 * @param array $sql    - exmaple of possible param = array(
                                                                'sql'       => array(
                                                                                      'start'   => "UPDATE {$wpdb->prefix}booking_seasons SET "
                                                                                    , 'params' => array( 'title = %s, filter = %s' )                         
                                                                                    , 'end'    => " WHERE booking_filter_id = %d"
                                                                            )
                                                                , 'values'  => array( $validated_title , $ser_filter  )
                                                            )
 * @return string - updated SQL
 */
function wpbc_add_current_users_column__sql_update_array( $sql, $resource_id, $resource ) {
    
    $user = wp_get_current_user();
    $user_id = $user->ID;
        
    $sql['sql']['params'][] = 'users = %d';
    $sql['values'][]        = $user_id;
    
    return $sql;
}
//add_filter( 'wpbc_seasonfilters_table__single_update_sql_array',   'wpbc_add_current_users_column__sql_update_array', 10, 1 );   // Hook for validated fields.
//add_filter( 'wpbc_discountcoupons_table__single_update_sql_array', 'wpbc_add_current_users_column__sql_update_array', 10, 1 );


////////////////////////////////////////////////////////////////////////////////
// Support
////////////////////////////////////////////////////////////////////////////////
                         
/**
	 * Check about number of exist booking resources and maximum allowed booking resources  for Regular User
 * 
 * @param int $max_resources
 * 
 * example: $validated_resources_count = apply_filters( 'wpbc_check_max_allowed_booking_resources', $validated_resources_count );
 */            
function wpbc_check_max_allowed_booking_resources( $resources_count ) {
    
    // If user super admin  then  return  original
    $is_super_admin = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );    
    if ( $is_super_admin ) return $resources_count;
    
    
    $user = wp_get_current_user();
    $user_id = $user->ID;
    
    $max_resources = get_user_option( 'booking_max_num_of_resources', $user_id );

    $wpbc_br_cache = wpbc_br_cache();
    $exist_booking_resources = $wpbc_br_cache->get_resources();
    
    $allowed_resources_count = $max_resources - count( $exist_booking_resources );
    if ( $allowed_resources_count < 0 ) 
         $allowed_resources_count = 0;


    if ( $resources_count > $allowed_resources_count )
        return $allowed_resources_count;
    else 
        return $resources_count;    
}
add_filter( 'wpbc_check_max_allowed_booking_resources', 'wpbc_check_max_allowed_booking_resources', 10, 1 );


/**
	 * Get Label of User Status for echo: 'Super Admin' | 'Regular User' | 'Inactive User'
 * 
 * @param array $user - getted array  from DB - array( 
                                                            'id' => 0
                                                          , 'active' => false
                                                          , 'super'  => ''                                    // 'super_admin'  | 'regular'
                                                      );
 */
function wpbc_get_user_status( $user ) {
    
        $label_text = '';
    
        $defaults = array( 
                              'id' => 0
                            , 'active' => false
                            , 'super'  => ''                                    // 'super_admin'  | 'regular'
                        );
        $user = wp_parse_args( $user, $defaults );
    
    
        $is_booking_active_for_user = $user['active'];
        $is_user_super_admin        = ( ( $user['super'] == 'super_admin' ) ? true : false );
        
        $default_super_admin_id_arr = apply_bk_filter( 'get_default_super_admin_id' );  // Get list  of Super booking admin users,  that  defined by  default - e.g. uer  with  ID = 1
        if ( in_array( $user['id' ], $default_super_admin_id_arr ) ) 
                $is_user_super_admin = true;                                    // User ID inside of Super Admin ID                

        if ( ( $is_booking_active_for_user == 'On') || ( $is_user_super_admin ) ) { 
            if ( $is_user_super_admin ){
                $label_text .= '<span class="label label-default label-info" style="font-weight:600;background-color: #ee8000;">';
                $label_text .= __('Super Admin' ,'booking');
                $label_text .= '</span>';
            } else {
                $label_text .= '<span class="label label-default label-info" style="font-weight:600;background-color: #39d;">';
                $label_text .= __('Regular User' ,'booking');
                $label_text .= '</span>';
            }
        } else {
                $label_text .= '<span class="label label-default label-info" style="font-weight:400;background-color: #89a;color:#fdfdfd;">';
                $label_text .= __('Inactive User' ,'booking');
                $label_text .= '</span>';
        }    
        
        return $label_text;
}