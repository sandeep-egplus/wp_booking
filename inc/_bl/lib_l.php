<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  S Q L   Modifications  for  Booking Listing  ///////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Resources
function get_l_bklist_sql_resources($blank, $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 ){

        global $wpdb;
        $sql_where = '';

        // BL                                                               // Childs in dif sub resources
        $sql_where.=   "        OR ( bk.booking_id IN (
                                                 SELECT DISTINCT booking_id
                                                 FROM {$wpdb->prefix}bookingdates as dtt
                                                 WHERE  " ;
        if ($wh_approved !== '')
                                $sql_where.=                  " dtt.approved = $wh_approved  AND " ;
        $sql_where.= wpbc_set_sql_where_for_dates($wh_booking_date, $wh_booking_date2, 'dtt.') ;

        $sql_where.=                                          "   (
                                                                dtt.type_id IN ( ". $wh_booking_type ." )
                                                                OR  dtt.type_id IN (
                                                                                     SELECT booking_type_id
                                                                                     FROM {$wpdb->prefix}bookingtypes as bt
                                                                                     WHERE  bt.parent IN ( ". $wh_booking_type ." )
                                                                                    )
                                                             )
                                                 )
                              ) " ;
        // BL                                                               // Just Childs sub resources
        $sql_where.=   "         OR ( bk.booking_type IN (
                                                     SELECT booking_type_id
                                                     FROM {$wpdb->prefix}bookingtypes as bt
                                                     WHERE  bt.parent IN ( ". $wh_booking_type ." )
                                                    )
                              )" ;

    return $sql_where;
}
add_bk_filter('get_l_bklist_sql_resources', 'get_l_bklist_sql_resources');


// Resources
function get_l_bklist_sql_resources_for_calendar_view($blank, $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 ){

        global $wpdb;
        $sql_where = '';

        // BL                                                               // Childs in dif sub resources
        $sql_where.=   "        OR ( bk.booking_id IN (
                                                 SELECT DISTINCT booking_id
                                                 FROM {$wpdb->prefix}bookingdates as dtt
                                                 WHERE  " ;
        if ($wh_approved !== '')
                                $sql_where.=                  " dtt.approved = $wh_approved  AND " ;
        $sql_where.= wpbc_set_sql_where_for_dates($wh_booking_date, $wh_booking_date2, 'dtt.') ;

        $sql_where.=                                          "   (
                                                                dtt.type_id IN ( ". $wh_booking_type ." )
                                                                OR  dtt.type_id IN (
                                                                                     SELECT booking_type_id
                                                                                     FROM {$wpdb->prefix}bookingtypes as bt
                                                                                     WHERE  bt.parent IN ( ". $wh_booking_type ." )
                                                                                    )
                                                             )
                                                 )
                              ) " ;
/*
        // BL                                                               // Just Childs sub resources
        $sql_where.=   "         OR ( bk.booking_type IN (
                                                     SELECT booking_type_id
                                                     FROM {$wpdb->prefix}bookingtypes as bt
                                                     WHERE  bt.parent IN ( ". $wh_booking_type ." )
                                                    )
                              )" ;
/**/
    return $sql_where;
}
add_bk_filter('get_l_bklist_sql_resources_for_calendar_view', 'get_l_bklist_sql_resources_for_calendar_view');


//FixIn: 6.1.1.9       
/**
	 * Check  if this resource child
 * 
 * @param true | false
 */
function wpbc_is_this_child_resource( $resource_id ) {
   
    if ( ! empty( $resource_id ) )																						//FixIn: 7.1.2.10
		$booking_resource_attr = get_booking_resource_attr( $resource_id );
	
    /**
    [booking_type_id] => 11
    [title] => Apartment#1-2
    [users] => 1
    [import] => 
    [cost] => 50
    [default_form] => standard
    [prioritet] => 2
    [parent] => 2
    [visitors] => 1
     */
    if ( ( ! empty( $resource_id ) ) && ( $booking_resource_attr->parent != 0 )    )
        return true;
    else 
        return false;
}


/**
	 * Get ID of parent booking resource,  for this child resource
 * 
 * @param int $resource_id
 * @return int - ID of booking resource
 */
function wpbc_get_parent_resource( $resource_id ) {
    
    $booking_resource_attr = get_booking_resource_attr( $resource_id );
    
    return $booking_resource_attr->parent;
}