<?php
/**
 * @package  Stripe Response
 * @category Payment Gateway for Booking Calendar
 * @author wpdevelop
 * @version 1.0
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2017-10-13
 * Integration based on Stripe PHP library 5.2.3 2017-09-27
 * Based on guide: https://stripe.com/docs/checkout/php and https://stripe.com/docs/checkout
 *
 * Initially provided customization by: ingoratsdorf
 */

//FixIn:8.0.1.10

error_reporting(E_ALL ^ E_NOTICE);


/**
 * Define loading all Booking Calendar scripts in response mode from wpbc-response.php
 * and after that return to this script, without die() in   wpbc-response.php
 */
define( 'WP_BK_RESPONSE_IPN_MODE', true );

/**
 * Buffer all output so we can still use the header / location directive,
 * otherwise in wpbc-response.php was set "Content-Type: text/html; charset=..."
 */
ob_start();

require_once( dirname( __FILE__ ) . '/../wpbc-response.php' );

// This integration  have minimum PHP version requirement!
if ( version_compare( PHP_VERSION, '5.3.3' ) < 0 ) {
	ob_end_clean();
	echo 'Stripe payment require PHP version 5.3.3 or newer!';
	die();
}

// if ( ! class_exists('Stripe') ) {                                                                                       //FixIn: 8.1.3.14
if ( ! class_exists( 'Stripe\Stripe' ) ) {                                                                              //FixIn: 8.1.3.14
	require_once( dirname( __FILE__ ) . '/stripe-php/init.php' );
}
//else {
//	if ( version_compare( Stripe::VERSION, '5.2.3' ) < 0 ) {
//		ob_end_clean();
//		echo 'Other plugin using Stripe library  older than 5.2.3 which  is required by Booking Calendar';
//		die();
//	}
//}

/**
 * Test response data:
 * array(
              [payed_booking] => 3798
			* [wp_nonce] => 150806396881.96
			* [pay_sys] => stripe
			* [x_amount] => 113.14
			* [x_currency_code] => EUR
			* [x_description] => Payment for booking via Stripe Apartment - #3 on these day(s): 2017/11/16 12:00 PM - 2017/11/17 10:00 AM
			* [x_invoice_num] => booking #3798
			* [x_booking_id] => 3798
			* [wpbc_stripe_payment] => bc42d6d2cf
			* [_wp_http_referer] => /wp-admin/admin-ajax.php
			* [x_email] => user@beta.com
			* [x_first_name] => John
			* [stripeToken] => tok_1............k
			* [stripeTokenType] => card
			* [stripeEmail] => user@beta.com
		* )
 */


////////////////////////////////////////////////////////////////////////
// Support functions
////////////////////////////////////////////////////////////////////////



/**
 * Check Payment Nonce of Booking
 *
 * Each  booking have unique 'pay_status' field in Database
 * This field transfer into payment form function get_payment_form( $output, $params, $gateway_id = '' )    as      $params[ '__nonce' ] => 150806225688.01
 * Then this field added to the response file in $_GET[ 'wp_nonce' ] = $params['__nonce']
 *
 * So we need to  check if specific booking with booking_id have this payment nonce.
 *
 * @param $booking_id
 *
 * @return bool
 */
function wpbc_stripe_check_payment_nonce_booking( $booking_id ) {

	if ( isset( $_GET['wp_nonce'] ) ) {
		$wp_nonce = $_GET['wp_nonce'];
	} else {
		return  false;
	}

	global $wpdb;

	$sql = $wpdb->prepare( "SELECT pay_status FROM {$wpdb->prefix}booking WHERE booking_id IN ( %d ) LIMIT 0,1", $booking_id );

	$sql_results = $wpdb->get_results( $sql );

	if ( count( $sql_results ) > 0 ) {
		if ( $sql_results[0]->pay_status == $wp_nonce ) {
			return  true;
		}
	}

	return  false;
}


/**
 *  Update Payment status of booking
 * @param $booking_id
 * @param $status
 *
 * @return bool
 */
function wpbc_stripe_update_payment_status( $booking_id, $status ){

	global $wpdb;

	// Update payment status
	$update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_status = %s WHERE bk.booking_id = %d;", $status, $booking_id );

	if ( false === $wpdb->query( $update_sql  ) ){
		return  false;
	}

	return  true;
}


/**
 * Auto cancel booking and redirect
 * @param $booking_id
 * @param $stripe_error_code
 */
function wpbc_stripe_auto_cancel_booking( $booking_id , $stripe_error_code ){

	// Lets check whether the user wanted auto-approve or cancel
	$auto_approve = get_bk_option( 'booking_stripe_is_auto_approve_cancell_booking'  );
	if ($auto_approve == 'On')
		wpbc_auto_cancel_booking( $booking_id );

	$stripe_error_url   = get_bk_option( 'booking_stripe_order_failed' );

	$stripe_error_url = wpbc_make_link_absolute( $stripe_error_url );

	// if relay is active, this will point to some valid url the user entered. If not, it will point to the original gateway url
	header ("Location: ". $stripe_error_url ."?error=".$stripe_error_code);
}


/**
 * Auto approve booking and redirect
 *
 * @param $booking_id
 */
function wpbc_stripe_auto_approve_booking( $booking_id ){

	// Lets check whether the user wanted auto-approve or cancel
	$auto_approve = get_bk_option( 'booking_stripe_is_auto_approve_cancell_booking'  );
	if ($auto_approve == 'On'){
		wpbc_auto_approve_booking( $booking_id );
	}


	$stripe_success_url = get_bk_option( 'booking_stripe_order_successful' );
	if ( empty( $stripe_success_url ) ) {
		$stripe_success_url = get_bk_option( 'booking_thank_you_page_URL' );
	}

	$stripe_success_url = wpbc_make_link_absolute( $stripe_success_url );

	// if relay is active, this will point to some valid url the user entered. If not, it will point to the original gateway url
	header ("Location: ". $stripe_success_url );
}


////////////////////////////////////////////////////////////////////////
// Check response parameters
////////////////////////////////////////////////////////////////////////

/**
 * Nonce checking - check valid and generated between 0-12 hours ago
 *
 * Was added via wp_nonce_field( 'wpbc_stripe', 'wpbc_stripe_payment' );
 *
 * Return:
 *      false if the nonce is invalid,
 *      1 if the nonce is valid and generated between 0-12 hours ago,
 *      2 if the nonce is valid and generated between 12-24 hours ago.
 */
$nonce_gen_time = check_admin_referer( 'wpbc_stripe', 'wpbc_stripe_payment' );
if ( $nonce_gen_time != 1 ) {
	ob_end_clean();
	echo 'Stripe payment nonce invalid!';
	die();
}

// We have submitted the booking_id in both the form GET URL plus in the form itself as POST, so we can check
$booking_id  = isset( $_GET['payed_booking'] ) ? intval( $_GET['payed_booking'] ) : '' ;
$post_booking_id = isset( $_POST['x_booking_id'] ) ? intval( $_POST['x_booking_id'] ) : '';
if ( ( $booking_id != $post_booking_id ) || ( '' === $booking_id ) ) {
	echo 'There are inconsistencies in the submitted booking ID!';
	die();
}


// Clear all errors to start with parsing
$stripe_error_code = '';

// Retrive token directly from Stripe via their JS interface previously
$token = isset( $_POST['stripeToken'] ) ? $_POST['stripeToken'] : '';
if ( empty( $token ) ) {
	$stripe_error_code = "No Stripe token has been submitted with the payment. Please check the settings!";
}
if ( ! isset( $_POST['stripeEmail'] ) ) {
	$stripe_error_code = "No Stripe costomer email address has been submitted with the payment. Please check the settings!";
}
if ( ! isset( $_POST['x_currency_code'] ) ) {
	$stripe_error_code = "No currency has been submitted with the payment. Please check the settings!";
}
if ( ! isset( $_POST['x_amount'] ) ) {
	$stripe_error_code = "No currency has been submitted with the payment. Please check the settings!";
}
if ( ! isset( $_POST['x_first_name'] ) ) {
	$stripe_error_code = "No customer name has been submitted with the payment. Please check the settings!";
}
if ( ! isset( $_POST['x_last_name'] ) ) {
	$stripe_error_code = "No customer name has been submitted with the payment. Please check the settings!";
}



////////////////////////////////////////////////////////////////////////
// Validate Payment Gateway response parameters
////////////////////////////////////////////////////////////////////////

// Get secret key
$stripe_account_mode = get_bk_option( 'booking_stripe_account_mode' );
if ( 'test' == $stripe_account_mode ) {
	$stripe_secret_key = get_bk_option( 'booking_stripe_secret_key_test' );
} else {
	$stripe_secret_key = get_bk_option( 'booking_stripe_secret_key' );
}
// Check whether secret key was assigned
if ( empty( $stripe_secret_key ) ) {
	$stripe_error_code = "Configuration issue. No Stripe secret key has been retrieved from settings.";
}


// Cost is tranferred from booking calendar in real currency values, for stripe we need it in per cent, ie $ -> cents
$cost = isset( $_POST['x_amount'] ) ? ( floatval( $_POST['x_amount'] ) * 100 ) : 0;
$check_currency = strtolower( $_POST['x_currency_code'] );  //FixIn: 8.2.1.16
if ( in_array(  $check_currency , array( 'jpy' ) ) ) {
	$cost = $cost / 100;
}
if ( $cost <= 0 ) {
	$stripe_error_code = "The cost is zero or negative. Con not make such payments. Please check the settings!";
}

if ( true !== wpbc_stripe_check_payment_nonce_booking( $booking_id ) ) {

	$stripe_error_code = 'Booking payment nonce invalid!';
}

if ( ! function_exists('curl_init') ) {                                                                //FixIn: 8.1.1.1
	$stripe_error_code = 'Require additional PHP library';
}

// All of the above errors should be gateway processing or configuration issues,
// we just relay back to the eror url without doing cancellation of the booking since it's no user mistake
if ( $stripe_error_code !== '' ) {

	//$stripe_thanks_url  = get_bk_option( 'booking_thank_you_page_URL' );
	//$stripe_success_url = get_bk_option( 'booking_stripe_order_successful' );
	$stripe_error_url   = get_bk_option( 'booking_stripe_order_failed' );

	$stripe_error_url = wpbc_make_link_absolute( $stripe_error_url );

	header( "Location: " . $stripe_error_url . "?error=" . $stripe_error_code );
	die();
}





////////////////////////////////////////////////////////////////////////
// Charge Process
////////////////////////////////////////////////////////////////////////

try {
	\Stripe\Stripe::setApiKey( $stripe_secret_key );

	$customer = \Stripe\Customer::create( array(
		'email'       => $_POST['stripeEmail'],
		'source'      => $token,
		'description' => $_POST['x_first_name'] . " " . $_POST['x_last_name'],
		'metadata'    => array(
							"Name" => $_POST['x_first_name'] . " " . $_POST['x_last_name']
						)
	) );

	$charge = \Stripe\Charge::create( array(
		'customer' => $customer->id,
		'amount'   => $cost,
		'currency' => $_POST['x_currency_code'],
		'metadata' => array(
							"Booking ID"  => $_POST['x_invoice_num'],
							"Name"        => $_POST['x_first_name'] . " " . $_POST['x_last_name'],
							"Description" => $_POST['x_description']
						)
	) );
}
catch ( \Stripe\Error\Card $e ) {

	// Since it's a decline, \Stripe\Error\Card will be caught
	$body = $e->getJsonBody();
	$err  = $body['error'];

	$stripe_error_code = ' Status is:' . $e->getHttpStatus();
	$stripe_error_code .= ' Type is:' . $err['type'];
	$stripe_error_code .= ' Code is:' . $err['code'];
	$stripe_error_code .= ' Param is:' . $err['param'];
	$stripe_error_code .= ' Message is:' . $err['message'];

	wpbc_stripe_update_payment_status( $booking_id , 'Stripe:ERROR');
	wpbc_stripe_auto_cancel_booking( $booking_id, $stripe_error_code );
	die();

}
catch ( \Stripe\Error\RateLimit $e ) {

	wpbc_stripe_update_payment_status( $booking_id , 'Stripe:ERROR');
	wpbc_stripe_auto_cancel_booking( $booking_id, "Stripe. Too many requests made to the API too quickly" );
	die();

}
catch ( \Stripe\Error\InvalidRequest $e ) {

	wpbc_stripe_update_payment_status( $booking_id , 'Stripe:ERROR');
	wpbc_stripe_auto_cancel_booking( $booking_id, "Invalid parameters were supplied to Stripe's API" );
	die();

}
catch ( \Stripe\Error\Authentication $e ) {

	wpbc_stripe_update_payment_status( $booking_id , 'Stripe:ERROR');
	wpbc_stripe_auto_cancel_booking( $booking_id, "Stripe authentication failed!" );    // (maybe you changed API keys recently)
	die();

}
catch ( \Stripe\Error\ApiConnection $e ) {

	wpbc_stripe_update_payment_status( $booking_id , 'Stripe:ERROR');
	wpbc_stripe_auto_cancel_booking( $booking_id, "Stripe. Network communication with Stripe failed" );
	die();

}
catch ( \Stripe\Error\Base $e ) {
	// Display a very generic error to the user
	wpbc_stripe_update_payment_status( $booking_id , 'Stripe:ERROR');
	wpbc_stripe_auto_cancel_booking( $booking_id, "Some Stripe API error that we cannot explain further" );
	die();

}
catch ( Exception $e ) {
	// Something else happened, completely unrelated to Stripe
	wpbc_stripe_update_payment_status( $booking_id , 'Stripe:ERROR');
	wpbc_stripe_auto_cancel_booking( $booking_id, __( "Stripe. Ouch, something went wrong!", 'booking' ) );
	die(); // should not get here since redirected
}

// If we made the script to here, then all is well and we can approve the booking if auto is on
wpbc_stripe_update_payment_status( $booking_id , 'Stripe:OK');
wpbc_stripe_auto_approve_booking( $booking_id );

die(); // we should not get here since redirected