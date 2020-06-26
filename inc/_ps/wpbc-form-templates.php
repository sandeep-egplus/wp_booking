<?php
/**
 * @version     1.0
 * @package     Booking Calendar
 * @category    Default Form Templates
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


////////////////////////////////////////////////////////////////////////////////
// Booking Form Templates
////////////////////////////////////////////////////////////////////////////////

/**
	 * Get Default Booking Form during activation of plugin or get  this data for init creation of custom booking form
 *
 * @return string
 */
function wpbc_get_default_booking_form() {

    $is_demo = wpbc_is_this_demo();

    $booking_form = '[calendar] \n\
<div class="standard-form"> \n\
 <p style="display: none;">'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
 <p style="display: none;">'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
 <p style="display: none;">'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
 <p>'.__('Comments' ,'booking').':<br />[text comments class:materialize-textarea] </p> \n\
 <div class="deg-btn" id="calculator-btn"> \n\
 <div class="deg-btn-wrapper waves-effect waves-red waves-light"> \n\
 <button id="calculator" type="button">LEAVE CALCULATOR</button> \n\
 </div> \n\
 </div> \n\
 <p>[submit class:btn "'.__('SUBMIT' ,'booking').'"]</p> \n\
</div>';

    if ( class_exists( 'wpdev_bk_biz_s' ) )
        $booking_form = '[calendar] \n\
        <div class="standard-form"> \n\
         <p style="display: none;">'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
         <p style="display: none;">'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
         <p style="display: none;">'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
         <p>'.__('Comments' ,'booking').':<br />[text comments class:materialize-textarea] </p> \n\
         <div class="deg-btn" id="calculator-btn"> \n\
         <div class="deg-btn-wrapper waves-effect waves-red waves-light"> \n\
         <button id="calculator" type="button">LEAVE CALCULATOR</button> \n\
         </div> \n\
         </div> \n\
         <div>[submit "'.__('SUBMIT' ,'booking').'"]</div> \n\
        </div>';


    if ( ( class_exists( 'wpdev_bk_biz_s' ) ) && ( $is_demo ) )
        $booking_form = '[calendar] \n\
        <div class="standard-form"> \n\
         <p style="display: none;">'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
         <p style="display: none;">'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
         <p style="display: none;">'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
         <p>'.__('Comments' ,'booking').':<br />[text comments class:materialize-textarea] </p> \n\
         <div class="deg-btn" id="calculator-btn"> \n\
         <div class="deg-btn-wrapper waves-effect waves-red waves-light"> \n\
         <button id="calculator" type="button">LEAVE CALCULATOR</button> \n\
         </div> \n\
         </div> \n\
         <p>[submit "'.__('SUBMIT' ,'booking').'"]</p> \n\
        </div>';


    if ( ( class_exists( 'wpdev_bk_biz_m' ) ) && ( $is_demo ) )
        $booking_form = '[calendar] \n\
        <div class="standard-form"> \n\
         <p style="display: none;">'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
         <p style="display: none;">'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
         <p style="display: none;">'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
         <p>'.__('Comments' ,'booking').':<br />[text comments class:materialize-textarea] </p> \n\
         <div class="deg-btn" id="calculator-btn"> \n\
         <div class="deg-btn-wrapper waves-effect waves-red waves-light"> \n\
         <button id="calculator" type="button">LEAVE CALCULATOR</button> \n\
         </div> \n\
         </div> \n\
         <p>[submit "'.__('SUBMIT' ,'booking').'"]</p> \n\
        </div>';

    if ( ( class_exists( 'wpdev_bk_biz_l' ) ) && ( $is_demo ) )
        $booking_form = '[calendar] \n\
        <div class="standard-form"> \n\
         <p style="display: none;">'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
         <p style="display: none;">'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
         <p style="display: none;">'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
         <p>'.__('Comments' ,'booking').':<br />[text comments class:materialize-textarea] </p> \n\
         <div class="deg-btn" id="calculator-btn"> \n\
         <div class="deg-btn-wrapper waves-effect waves-red waves-light"> \n\
         <button id="calculator" type="button">LEAVE CALCULATOR</button> \n\
         </div> \n\
         </div> \n\
         <p>[submit "'.__('SUBMIT' ,'booking').'"]</p> \n\
        </div>';

    return $booking_form;
}


/**
	 * Get Default Form to SHOW during activation of plugin or get  this data for init creation of custom booking form
 *
 * @return string
 */
function wpbc_get_default_booking_form_show() {

    $is_demo = wpbc_is_this_demo();

    $booking_form = '<div class="standard-content-form"> \n\
<strong>'. __('First Name' ,'booking').'</strong>:<span class="fieldvalue">[name]</span><br/> \n\
<strong>'. __('Last Name' ,'booking').'</strong>:<span class="fieldvalue">[secondname]</span><br/> \n\
<strong>'. __('Email' ,'booking').'</strong>:<span class="fieldvalue">[email]</span><br/> \n\
<strong>'. __('Comments' ,'booking').'</strong>:<br /><span class="fieldvalue"> [comments]</span> \n\
</div>';

    if ( class_exists( 'wpdev_bk_biz_s' ) )
    $booking_form = '<div class="standard-content-form"> \n\
<strong>'. __('First Name' ,'booking').'</strong>:<span class="fieldvalue">[name]</span><br/> \n\
<strong>'. __('Last Name' ,'booking').'</strong>:<span class="fieldvalue">[secondname]</span><br/> \n\
<strong>'. __('Email' ,'booking').'</strong>:<span class="fieldvalue">[email]</span><br/> \n\
<strong>'. __('Comments' ,'booking').'</strong>:<br /><span class="fieldvalue"> [comments]</span> \n\
</div>';

    if ( ( class_exists( 'wpdev_bk_biz_m' ) ) && ( $is_demo ) )
    $booking_form = '<div class="standard-content-form"> \n\
<strong>'. __('First Name' ,'booking').'</strong>:<span class="fieldvalue">[name]</span><br/> \n\
<strong>'. __('Last Name' ,'booking').'</strong>:<span class="fieldvalue">[secondname]</span><br/> \n\
<strong>'. __('Email' ,'booking').'</strong>:<span class="fieldvalue">[email]</span><br/> \n\
<strong>'. __('Comments' ,'booking').'</strong>:<br /><span class="fieldvalue"> [comments]</span> \n\
</div>';

    if ( ( class_exists( 'wpdev_bk_biz_l' ) ) && ( $is_demo ) )
    $booking_form = '<div class="standard-content-form"> \n\
<strong>'. __('First Name' ,'booking').'</strong>:<span class="fieldvalue">[name]</span><br/> \n\
<strong>'. __('Last Name' ,'booking').'</strong>:<span class="fieldvalue">[secondname]</span><br/> \n\
<strong>'. __('Email' ,'booking').'</strong>:<span class="fieldvalue">[email]</span><br/> \n\
<strong>'. __('Comments' ,'booking').'</strong>:<br /><span class="fieldvalue"> [comments]</span> \n\
</div>';

    return $booking_form;
}


////////////////////////////////////////////////////////////////////////////////
// Search Form Templates
////////////////////////////////////////////////////////////////////////////////

/**
	 * Default Search Form templates
 *
 * @param string $search_form_type
 * @return string
 */
function wpbc_get_default_search_form_template( $search_form_type = '' ){     //FixIn:6.1.0.1

  switch ( $search_form_type ) {

      case 'inline':
          return   '<div class="wpdevelop">' . '\n\r'
                 . '    <div class="form-inline well">' . '\n\r'
                 . '        <label>'.__('Check in' ,'booking').':</label> [search_check_in]' . '\n\r'
                 . '        <label>'.__('Check out' ,'booking').':</label> [search_check_out]' . '\n\r'
                 . '        <label>'.__('Guests' ,'booking').':</label> [search_visitors]' . '\n\r'
                 . '        [search_button]' . '\n\r'
                 . '    </div>' . '\n\r'
                 . '</div>';

      case 'horizontal':
          return   '<div class="wpdevelop">' . '\n\r'
                 . '    <div class="form-horizontal well">' . '\n\r'
                 . '        <label>'.__('Check in' ,'booking').':</label> [search_check_in]' . '\n\r'
                 . '        <label>'.__('Check out' ,'booking').':</label> [search_check_out]' . '\n\r'
                 . '        <label>'.__('Guests' ,'booking').':</label> [search_visitors]' . '\n\r'
                 . '        <hr/>\n\        [search_button]' . '\n\r'
                 . '    </div>' . '\n\r'
                 . '</div>';

      case 'advanced':
          return   '<div class="wpdevelop">' . '\n\r'
                 . '    <div class="form-inline well">' . '\n\r'
                 . '        <label>'.__('Check in' ,'booking').':</label> [search_check_in]' . '\n\r'
                 . '        <label>'.__('Check out' ,'booking').':</label> [search_check_out]' . '\n\r'
                 . '        <label>'.__('Guests' ,'booking').':</label> [search_visitors]' . '\n\r'
                 . '        [search_button]' . '\n\r'
                 . '        <br/><label>[additional_search "3"] +/- 2 '.__('days' ,'booking').'</label>' . '\n\r'
                 . '    </div>' . '\n\r'
                 . '</div>';
      default:
          return

                   ' <label>'.__('Check in' ,'booking').':</label> [search_check_in]' . '\n\r'
                 . ' <label>'.__('Check out' ,'booking').':</label> [search_check_out]' . '\n\r'
                 . ' <label>'.__('Guests' ,'booking').':</label> [search_visitors]' . '\n\r'
                 . ' [search_button] ';
  }

}


/**
	 * Default Search Results templates
 *
 * @param string $search_form_type
 * @return string
 */
function wpbc_get_default_search_results_template( $search_form_type = '' ){     //FixIn:6.1.0.1

    switch ($search_form_type) {

      case 'advanced':
          return   '<div class="wpdevelop">' . '\n\r'
                 . '  ' . '<div style="float:right;"><div>Cost: <strong>[cost_hint]</strong></div>' . '\n\r'
                 . '  ' . '[link_to_booking_resource "Book now"]</div>' . '\n\r'
                 . '  ' . '<a href="[book_now_link]" class="wpbc_book_now_link">' . '\n\r'
                 . '  ' . '    ' .'[booking_resource_title]' . '\n\r'
                 . '  ' . '</a>' . '\n\r'
                 . '  ' . '[booking_featured_image]' . '\n\r'
                 . '  ' . '[booking_info]' . '\n\r'
                 . '  ' . '<div>' . '\n\r'
                 . '  ' . '  ' . __('Availability' ,'booking').': [num_available_resources] item(s).' . '\n\r'
                 . '  ' . '  ' . __('Max. persons' ,'booking').': [max_visitors]' . '\n\r'
                 . '  ' . '  ' . 'Check in/out: <strong>[search_check_in]</strong> - ' . '\n\r'
                 . '  ' . '                ' . '<strong>[search_check_out]</strong>' . '\n\r'
                 . '  ' . '</div>' . '\n\r'
                 . '</div>';

      default:
          return   '<div class="wpdevelop">' . '\n\r'
                 . '    <div style="float:right;">' . '\n\r'
                 . '        ' . '<div>From [standard_cost]</div>' . '\n\r'
                 . '        ' . '[link_to_booking_resource "Book now"]' . '\n\r'
                 . '    </div>' . '\n\r'
                 . '    [booking_resource_title]' . '\n\r'
                 . '    [booking_featured_image]' . '\n\r'
                 . '    [booking_info]' . '\n\r'
                 . '    <div>' . '\n\r'
                 . '        ' . __('Availability' ,'booking').': [num_available_resources] item(s).' . '\n\r'
                 . '        ' . __('Max. persons' ,'booking').': [max_visitors]' . '\n\r'
                 . '    </div>' . '\n\r'
                 . '</div>';
    }
}
