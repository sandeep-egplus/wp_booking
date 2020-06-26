<?php
/**
 * @package  Stripe Checkout Integration
 * @category Payment Gateway for Booking Calendar 
 * @author wpdevelop
 * @version 1.0
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2017-10-08
 * Integration based on Stripe PHP library 5.2.3 2017-09-27
 * Based on guide: https://stripe.com/docs/checkout/php and https://stripe.com/docs/checkout
 *
 * Initially provided customization by: ingoratsdorf
 */

//FixIn:8.0.1.10

/*
 * Testing:
 * Use 	test card number—4242 4242 4242 4242,
 * 		any future month and year for the expiration,
 * 		any three-digit number for the CVC, and any random ZIP code.

   $stripe = array(
					  "secret_key"      => "sk_test_BQokikJOvBiI2HlWgH4olfQ2",		// booking_stripe_stripe_secret_test_key = sk_test_BQokikJOvBiI2HlWgH4olfQ2
					  "publishable_key" => "pk_test_6pRNASCoBOKtIshFeQd4XMUh"		// booking_stripe_stripe_public_test_key = pk_test_6pRNASCoBOKtIshFeQd4XMUh
				);
 */


if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly
                                                                                
if ( ! defined( 'WPBC_STRIPE_GATEWAY_ID' ) )        define( 'WPBC_STRIPE_GATEWAY_ID', 'stripe' );


//                                                                              <editor-fold   defaultstate="collapsed"   desc=" Gateway API " >

/** API  for  Payment Gateway  */
class WPBC_Gateway_API_STRIPE extends WPBC_Gateway_API  {

	/**
	 * Get payment Form
	 * @param string $output    - other active payment forms
	 * @param array $params     - input params                          array (
																				[booking_id] => 112
																				[id] => 112
																				[days_input_format] => 22.07.2016
																				[days_only_sql] => 2016-07-22
																				[dates_sql] => 2016-07-22 12:00:01, 2016-07-22 14:00:02
																				[check_in_date_sql] => 2016-07-22 12:00:01
																				[check_out_date_sql] =>  2016-07-22 14:00:02
																				[dates] => July 22, 2016 12:00 - July 22, 2016 14:00
																				[check_in_date] => July 22, 2016 12:00
																				[check_out_date] => July 22, 2016 14:00
																				[check_out_plus1day] => July 23, 2016 14:00
																				[dates_count] => 1
																				[days_count] => 1
																				[nights_count] => 1
																				[cost] => 15000.00
																				[cost_format] => 15 000,0
																				[siteurl] => http://beta
																				[resource_title] => Apartment#3
																				[bookingtype] => Apartment#3
																				[remote_ip] => 127.0.0.1
																				[user_agent] => Mozilla/5.0 (Windows NT 10.0; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0
																				[request_url] => http://beta/resource-3-id4/
																				[current_date] => July 7, 2016
																				[current_time] => 14:00
																				[selected_short_timedates_hint] => July 22, 2016 12:00 - July 22, 2016 14:00
																				[nights_number_hint] => 1
																				[cost_hint] => 15 000,0
																				[rangetime] => 12:00 - 14:00
																				[name] => John
																				[secondname] => Smith
																				[email] => smith@email-server.com
																				[phone] => 123-456-789
																				[address] => Baker str.
																				[city] => London
																				[postcode] => 232432
																				[country] => GB
																				[visitors] => 1
																				[children] => 0
																				[details] => Test booking
																				[term_and_condition] => I Accept term and conditions
																				[booking_resource_id] => 4
																				[resource_id] => 4
																				[type_id] => 4
																				[type] => 4
																				[resource] => 4
																				[content] => 'Content of booking fields data .... '
																				[moderatelink] => http://link?page=wpbc&view_mode=vm_listing&tab=actions&wh_booking_id=112
																				[visitorbookingediturl] => http://link?booking_hash=a42f9aaa580f11dbe1a928651220e2d0
																				[visitorbookingcancelurl] => http://link?booking_hash=a42f9aaa580f11dbe1a928651220e2d0&booking_cancel=1
																				[visitorbookingpayurl] => http://link?booking_hash=a42f9aaa580f11dbe1a928651220e2d0&booking_pay=1
																				[bookinghash] => a42f9aaa580f11dbe1a928651220e2d0
																				[__booking_id] => 112
																				[__cost] => 3750
																				[__resource_id] => 4
																				[__form] => text^selected_short_timedates_hint4^July 22, 2016 12:00 - July 22, 2016 14:00~text^nights_number_hint4^1~text^cost_hint4^15 000,0~select-one^rangetime4^12:00 - 14:00~text^name4^John~text^secondname4^Smith~email^email4^smith@wpbookingcalendar.com~text^phone4^123-456-789~text^address4^Baker str.~text^city4^London~text^postcode4^232432~select-one^country4^GB~select-one^visitors4^1~select-one^children4^0~textarea^details4^Test booking ~checkbox^term_and_condition4[]^I Accept term and conditions
																				[__nonce] => 33979
																				[__is_deposit] => 1
																				[__additional_calendars] => array()
																				[__payment_type] => payment_form
																				[__cost_format] => 3 750,0
																				[cost_in_gateway] => 3750
																				[cost_in_gateway_hint] => 3 750,0
																				[is_deposit] => 1
																			)
	 * @return string        - you must  return  in format: return $output . $your_payment_form_content
	 */
	public function get_payment_form( $output, $params, $gateway_id = '' ) {
//debuge($params);die;


		// Check  if currently  is showing this Gateway
		if (
				   (  ( ! empty( $gateway_id ) ) && ( $gateway_id !== $this->get_id() )  )      // Does we need to show this Gateway
				|| ( ! $this->is_gateway_on() )                                                 // Payment Gateway does NOT active
		   ) return $output ;


		////////////////////////////////////////////////////////////////////////
		// Payment Options /////////////////////////////////////////////////////
		$payment_options                         = array();
		$payment_options['subject']              = get_bk_option( 'booking_stripe_subject' );                        	// 'Payment for booking %s on these day(s): %s'
		$payment_options['subject']              = apply_bk_filter( 'wpdev_check_for_active_language', $payment_options['subject'] );
		$payment_options['subject']              = wpbc_replace_booking_shortcodes( $payment_options['subject'], $params );
		$payment_options['payment_button_title'] = get_bk_option( 'booking_stripe_payment_button_title' );            	// 'Pay via Stripe'
		$payment_options['payment_button_title'] = apply_bk_filter( 'wpdev_check_for_active_language', $payment_options['payment_button_title'] );
		$payment_options['account_mode'] 		 = get_bk_option( 'booking_stripe_account_mode' );                            					// 'TEST'
		if ( 'test' == $payment_options['account_mode'] ) {
			$payment_options['publishable_key']  = get_bk_option( 'booking_stripe_publishable_key_test' );              // 'pk_test_6pRNASCoBOKtIshFeQd4XMUh'
			// $payment_options['secret_key']       = get_bk_option( 'booking_stripe_secret_key_test' );                // 'sk_test_BQokikJOvBiI2HlWgH4olfQ2'
		} else {
			$payment_options['publishable_key']  = get_bk_option( 'booking_stripe_publishable_key' );                   // 'pk_test_6pRNASCoBOKtIshFeQd4XMUh'
			// $payment_options['secret_key']       = get_bk_option( 'booking_stripe_secret_key' );                     // 'sk_test_BQokikJOvBiI2HlWgH4olfQ2'
		}
		$payment_options['curency'] 			 = get_bk_option( 'booking_stripe_curency' );                        	// 'USD'

		////////////////////////////////////////////////////////////////////////
		// Check about not correct configuration  of settings:
		////////////////////////////////////////////////////////////////////////
		if ( empty( $payment_options[ 'curency' ] ) )          return 'Wrong configuration in gateway settings.' . ' <em>Empty: "Currency" option</em>';
		if ( empty( $payment_options[ 'publishable_key' ] ) )  return 'Wrong configuration in gateway settings.' . ' <em>Empty: "Publishable Key" option</em>';
		// if ( empty( $payment_options[ 'secret_key' ] ) )       return 'Wrong configuration in gateway settings.' . ' <em>Empty: "Secret Key" option</em>';


		////////////////////////////////////////////////////////////////////////
		// Prepare Parameters for payment form
		////////////////////////////////////////////////////////////////////////
		//$stripe_charge_url =  WPBC_PLUGIN_URL . '/inc/gateways/wpbc-response.php?pay_sys=' . $this->get_id();
		//$stripe_charge_url =  WPBC_PLUGIN_URL . '/inc/gateways/stripe/stripe-charge.php?'
		$stripe_charge_url = untrailingslashit( plugins_url( '', __FILE__ ) )                        					// http://server.com/wp-content/plugins/booking/inc/gateways/stripe
							. '/stripe-charge.php?'
							. 'payed_booking=' . $params['booking_id']
							. '&wp_nonce=' . $params['__nonce']
							. '&wpdev_active_locale=' . wpbc_get_booking_locale()                                       //FixIn: 8.4.5.1
							. '&pay_sys=' . $this->get_id();

		////////////////////////////////////////////////////////////////////////
		// Payment Form
		////////////////////////////////////////////////////////////////////////
		ob_start();

		?><div style="width:100%;clear:both;margin-top:20px;"></div><?php
		?><div class="stripe_div wpbc-replace-ajax wpbc-payment-form" style="text-align:left;clear:both;"><?php

		/**
		 *  We need to open payment form in separate window, if this booking was made togather with other
		 *  in booking form  was used several  calendars from  different booking resources.
		 *  So we are having several  payment forms for each  booked resource.
		 *  System transfer this parameter $params['payment_form_target'] = ' target="_blank" ';
		 *  otherwise $params['payment_form_target'] = '';
		 */

		?><form action="<?php echo $stripe_charge_url; ?>" <?php echo $params['payment_form_target']; ?>
				method="POST" id="stripePayForm" name="stripePayForm"
				style="text-align:left;" class="booking_stripePayForm"><?php

			echo "<strong>" . $params['gateway_hint'] . ': ' . $params[ 'cost_in_gateway_hint' ] . "</strong><br />";

		?><input type="hidden" name="x_amount" value="<?php echo $params['cost_in_gateway']; ?>" /><?php
		?><input type="hidden" name="x_currency_code" value="<?php echo $payment_options[ 'curency' ]; ?>" /><?php
		?><input type="hidden" name="x_description" value="<?php echo $payment_options[ 'subject' ]; ?>" /><?php
		?><input type="hidden" name="x_invoice_num" value="<?php echo 'booking #'.$params[ 'booking_id' ]; ?>" /><?php
		?><input type="hidden" name="x_booking_id" value="<?php echo $params[ 'booking_id' ]; ?>" /><?php

		wp_nonce_field( 'wpbc_stripe', 'wpbc_stripe_payment' );	// Setting up nonce for added security

		//                                                                              <editor-fold   defaultstate="collapsed"   desc=" BILLING INFORMATION " >

		// Required only when using a European Payment Processor

		// Email
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_customer_email' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_email" value="<?php echo substr( $params[ $billing_field_name ], 0, 255 ); ?>" /><?php
		}
		// First Name
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_firstnames' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_first_name" value="<?php echo substr( $params[ $billing_field_name ], 0, 50 ); ?>" /><?php
		}
		// Last Name
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_surname' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_last_name" value="<?php echo substr( $params[ $billing_field_name ], 0, 50 ); ?>" /><?php
		}
		// Address
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_address1' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_address" value="<?php echo substr( $params[ $billing_field_name ], 0, 60 ); ?>" /><?php
		}
		// City
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_city' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_city" value="<?php echo substr( $params[ $billing_field_name ], 0, 40 ); ?>" /><?php
		}
		// Country
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_country' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_country" value="<?php echo substr( $params[ $billing_field_name ], 0, 60 ); ?>" /><?php
		}
		// ZIP Code
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_post_code' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_zip" value="<?php echo substr( $params[ $billing_field_name ], 0, 20 ); ?>" /><?php
		}
		// State
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_state' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_state" value="<?php echo substr( $params[ $billing_field_name ], 0, 40 ); ?>" /><?php
		}
		// Phone
		$billing_field_name = (string) trim( get_bk_option( 'booking_billing_phone' ) );
		if ( isset( $params[ $billing_field_name ] ) ) {
			?><input type="hidden" name="x_phone" value="<?php echo substr( $params[ $billing_field_name ], 0, 25 ); ?>" /><?php
		}
		//                                                                              </editor-fold>

		/*
		 * echo  ' data-image="' . trim(get_bk_option( 'booking_stripe_custom_image' )) . '"' ;
		 * A relative or absolute URL pointing to a square image of your brand or product.
		 * The recommended minimum size is 128x128px. The supported image types are: .gif, .jpeg, and .png.
		 */

		/**
		 * Please note! ajax_script will be replaced to script after form will show in page.
		 * If we will use script directly  here, so then error at  the page will appear and its will not work
		 */

		// Zero-decimal currencies - check  more here https://stripe.com/docs/currencies#zero-decimal
		// Its can  generate this issue: ... ?error=Invalid%20parameters%20were%20supplied%20to%20Stripe%27s%20API

		//FixIn: 8.4.0.1	- start
		// Zero-decimal currencies: BIF, MGA, CLP, DJF, PYG, RWF, GNF, UGX, JPY, KMF, KRW, VND, VUV, XAF, XOF, XPF
		// Add support of Zero-decimal currencies in  Stripe payment system
		$check_currency = strtolower( $payment_options['curency'] );  //FixIn: 8.2.1.16
		if ( in_array(  $check_currency , array( 'bif', 'mga', 'clp', 'djf', 'pyg', 'rwf', 'gnf', 'ugx', 'jpy', 'kmf', 'krw', 'vnd', 'vuv', 'xaf', 'xof', 'xpf' ) ) ) {
			$is_cents = 1;
		} else {
			$is_cents = 100;
		}

		// Minimum and maximum charge amounts - https://stripe.com/docs/currencies#minimum-and-maximum-charge-amounts
		/*
			USD 	$0.50
			AUD 	$0.50
			BRL 	R$0.50
			CAD 	$0.50
			CHF 	0.50 Fr
			DKK 	2.50-kr.
			EUR 	€0.50
			GBP 	£0.30
			HKD 	$4.00
			JPY 	¥50
			MXN 	$10
			NOK 	3.00-kr.
			NZD 	$0.50
			SEK 	3.00-kr.
			SGD 	$0.50
		 */

		$currency_minimum = array(
			  'usd' =>  0.50
			, 'aud' => 	0.50
			, 'brl' => 	0.50
			, 'cad' => 	0.50
			, 'chf' => 	0.50
			, 'dkk' => 	2.50
			, 'eur' => 	0.50
			, 'gbp' => 	0.30
			, 'hkd' => 	4.00
			, 'jpy' => 	50
			, 'mxn' => 	10
			, 'nok' => 	3.00
			, 'nzd' => 	0.50
			, 'sek' => 	3.00
			, 'sgd' => 	0.50
		);

		$is_min_currency_error = false;

		foreach ( $currency_minimum as $min_currency => $min_currency_value ) {

			if ( (  $min_currency == $check_currency ) && ( floatval( $params['cost_in_gateway'] ) * $is_cents < floatval( $min_currency_value ) * $is_cents ) ) {
				$is_min_currency_error = true;
				echo  '<strong>' . __('Error' ,'booking') . '</strong>! ' . 'Stripe ' . 'require minimum amount in this currency as ' . '<strong>' . strtoupper( $min_currency ) . '</strong> '
					. '<strong>' . $min_currency_value . '</strong>';
			}
		}

		if ( ! $is_min_currency_error ) {
			?><ajax_script
				src="https://checkout.stripe.com/checkout.js" class="stripe-button"
				data-key="<?php 		echo $payment_options['publishable_key']; ?>"
				data-panel-label="<?php echo sprintf( __('Please pay %s', 'booking'), '{{amount}}' ); ?>"
				data-amount="<?php 		echo floatval( $params['cost_in_gateway'] ) * $is_cents; ?>"
				data-name="<?php 		echo get_bloginfo( 'name' ); ?>"
				data-description="<?php	echo substr( $payment_options[ 'subject' ], 0, 255); ?>"
				data-email="<?php 		if ( ! empty( $params['email'] ) ) { echo $params['email']; } ?>"
				data-locale="auto"
				data-currency="<?php 	echo $payment_options['curency']; ?>"
				data-zip-code="true"
				data-label="<?php 		echo trim( $payment_options[ 'payment_button_title' ] ); ?>" >
			</ajax_script><?php
		}
		//FixIn: 8.4.0.1	- end
		?></form></div><?php

		$payment_form = ob_get_clean();

		return $output . $payment_form;
	}


	/** Define settings Fields  */
	public function init_settings_fields() {

		$this->fields = array();

		// On | Off
		$this->fields['is_active'] = array(
									  'type'        => 'checkbox'
									, 'default'     => 'On'
									, 'title'       => __( 'Enable / Disable', 'booking' )
									, 'label'       => __( 'Enable this payment gateway', 'booking')
									, 'description' => ''
									, 'group'       => 'general'

								);

		// Switcher accounts - Test | Live
		$this->fields['account_mode'] = array(
									  'type' 		=> 'radio'
									, 'default' 	=> 'test'
									, 'title' 		=> __( 'Chose payment account', 'booking' )
									, 'description' => ''//__( 'Select TEST for the Test Server and LIVE in the live environment', 'booking' )
									, 'description_tag' => 'span'
									, 'css' 		=> ''
									, 'options' => array(
											 'test' => array( 'title' => __( 'TEST', 'booking' ), 'attr' => array( 'id' => 'stripe_mode_test' ) )
											,'live' => array( 'title' => __( 'LIVE', 'booking' ), 'attr' => array( 'id' => 'stripe_mode_live' ) )
										)
									, 'group' 		=> 'general'
		);

		// Public Key
		$this->fields['publishable_key'] = array(
									  'type'        => 'text'
									, 'default'     => ( wpbc_is_this_demo() ? 'pk_test_6pRNASCoBOKtIshFeQd4XMUh' : '' )
									//, 'placeholder' => ''
									, 'title'       => __('Publishable key', 'booking')
									, 'description' => __('Required', 'booking') . '.<br/>'
													   . sprintf( __('This parameter have to assigned to you by %s' ,'booking'), 'Stripe' )
													   . ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() : '' )
									, 'description_tag' => 'span'
									, 'css'         => ''//'width:100%'
									, 'group'       => 'general'
									, 'tr_class'    => 'wpbc_sub_settings_grayed wpbc_sub_settings_mode_live'
									//, 'validate_as' => array( 'required' )
							);
		// Secret Key
		$this->fields['secret_key'] = array(
									  'type'        => 'text'
									, 'default'     => ( wpbc_is_this_demo() ? 'sk_test_BQokikJOvBiI2HlWgH4olfQ2' : '' )
									//, 'placeholder' => ''
									, 'title'       => __('Secret key', 'booking')
									, 'description' => __('Required', 'booking') . '.<br/>'
													   . sprintf( __( 'This parameter have to assigned to you by %s' ,'booking'), 'Stripe' )
													   . ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() : '' )
									, 'description_tag' => 'span'
									, 'css'         => ''//'width:100%'
									, 'group'       => 'general'
									, 'tr_class'    => 'wpbc_sub_settings_grayed wpbc_sub_settings_mode_live'
									//, 'validate_as' => array( 'required' )
							);


		  // Public Key
		$this->fields['publishable_key_test'] = array(
									  'type'        => 'text'
									, 'default'     => ( wpbc_is_this_demo() ? 'pk_test_6pRNASCoBOKtIshFeQd4XMUh' : '' )
									//, 'placeholder' => ''
									, 'title'       => __('Publishable key', 'booking') . ' (' . __( 'TEST', 'booking' ) . ')'
					, 'description' => __('Required', 'booking') . '.<br/>'
													   . sprintf( __('This parameter have to assigned to you by %s' ,'booking'), 'Stripe' )
													   . ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() : '' )
									, 'description_tag' => 'span'
									, 'css'         => ''//'width:100%'
									, 'group'       => 'general'
									, 'tr_class'    => 'wpbc_sub_settings_grayed wpbc_sub_settings_mode_test'
									//, 'validate_as' => array( 'required' )
							);
		// Secret Key
		$this->fields['secret_key_test'] = array(
									  'type'        => 'text'
									, 'default'     => ( wpbc_is_this_demo() ? 'sk_test_BQokikJOvBiI2HlWgH4olfQ2' : '' )
									//, 'placeholder' => ''
									, 'title'       => __('Secret key', 'booking') . ' (' . __( 'TEST', 'booking' ) . ')'
									, 'description' => __('Required', 'booking') . '.<br/>'
													   . sprintf( __( 'This parameter have to assigned to you by %s' ,'booking'), 'Stripe' )
													   . ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() : '' )
											. '<div class="wpbc-settings-notice notice-info" style="text-align:left;"><strong>'
												. __('Note:' ,'booking') . '</strong> '
												. 'Testing at front-end side. Use following <strong>test</strong> card number <strong>4242 4242 4242 4242</strong> (Visa),'
												. ' a valid expiration date in the future, and any random CVC number, to create a successful payment.'
												. '<br>If you need to create test card payments using cards for other than US billing country,'
												. ' use Stripe international test cards from <a href="https://stripe.com/docs/testing#cards" target="_blank">this page</a>.'
											. '</div>'
									, 'description_tag' => 'span'
									, 'css'         => ''//'width:100%'
									, 'group'       => 'general'
									, 'tr_class'    => 'wpbc_sub_settings_grayed wpbc_sub_settings_mode_test'
									//, 'validate_as' => array( 'required' )
							);
	// https://stripe.com/docs/testing#cards
	// Instead, use any of the following test card numbers, a valid expiration date in the future, and any random CVC number, to create a successful payment.
	//

		// Currency
		$currency_list = array(
								  'USD' => __( 'U.S. Dollars', 'booking' )
								, 'GBP' => __( 'Pounds Sterling', 'booking' )
								, 'EUR' => __( 'Euros', 'booking' )
								, 'CAD' => __( 'Canadian Dollars', 'booking' )

								, 'AED' =>  'United Arab Emirates dirham',
								'AFN' =>  'Afghan afghani' . '*',
								'ALL' =>  'Albanian lek',
								'AMD' =>  'Armenian dram',
								'ANG' =>  'Netherlands Antillean guilder',
								'AOA' =>  'Angolan kwanza' . '*',
								'ARS' =>  'Argentine peso' . '*',
								'AUD' =>  'Australian dollar',
								'AWG' =>  'Aruban florin',
								'AZN' =>  'Azerbaijani manat',
								'BAM' =>  'Bosnia and Herzegovina convertible mark',
								'BBD' =>  'Barbadian dollar',
								'BDT' =>  'Bangladeshi taka',
								'BGN' =>  'Bulgarian lev',
								'BIF' =>  'Burundian franc',
								'BMD' =>  'Bermudian dollar',
								'BND' =>  'Brunei dollar',
								'BOB' =>  'Bolivian boliviano' . '*',
								'BRL' =>  'Brazilian real' . '*',
								'BSD' =>  'Bahamian dollar',
								'BWP' =>  'Botswana pula',
								'BZD' =>  'Belize dollar',
								// 'CAD' =>  'Canadian dollar',
								'CDF' =>  'Congolese franc',
								'CHF' =>  'Swiss franc',
								'CLP' =>  'Chilean peso' . '*',
								'CNY' =>  'Chinese yuan',
								'COP' =>  'Colombian peso' . '*',
								'CRC' =>  'Costa Rican col&oacute;n' . '*',
								'CVE' =>  'Cape Verdean escudo' . '*',
								'CZK' =>  'Czech koruna' . '*',
								'DJF' =>  'Djiboutian franc' . '*',
								'DKK' =>  'Danish krone' . '*',
								'DOP' =>  'Dominican peso',
								'DZD' =>  'Algerian dinar',
								'EGP' =>  'Egyptian pound',
								'ETB' =>  'Ethiopian birr',
								// 'EUR' =>  'Euro',
								'FJD' =>  'Fijian dollar',
								'FKP' =>  'Falkland Islands pound' . '*',
								// 'GBP' =>  'Pound sterling',
								'GEL' =>  'Georgian lari',
								'GIP' =>  'Gibraltar pound',
								'GMD' =>  'Gambian dalasi',
								'GNF' =>  'Guinean franc' . '*',
								'GTQ' =>  'Guatemalan quetzal' . '*',
								'GYD' =>  'Guyanese dollar' . '*',
								'HKD' =>  'Hong Kong dollar',
								'HNL' =>  'Honduran lempira' . '*',
								'HRK' =>  'Croatian kuna',
								'HTG' =>  'Haitian gourde',
								'HUF' =>  'Hungarian forint' . '*',
								'IDR' =>  'Indonesian rupiah',
								'ILS' =>  'Israeli new shekel',
								'INR' =>  'Indian rupee' . '*',
								'ISK' =>  'Icelandic kr&oacute;na',
								'JMD' =>  'Jamaican dollar',
								'JPY' =>  'Japanese yen',
								'KES' =>  'Kenyan shilling',
								'KGS' =>  'Kyrgyzstani som',
								'KHR' =>  'Cambodian riel',
								'KMF' =>  'Comorian franc',
								'KRW' =>  'South Korean won',
								'KYD' =>  'Cayman Islands dollar',
								'KZT' =>  'Kazakhstani tenge',
								'LAK' =>  'Lao kip' . '*',
								'LBP' =>  'Lebanese pound',
								'LKR' =>  'Sri Lankan rupee',
								'LRD' =>  'Liberian dollar',
								'LSL' =>  'Lesotho loti',
								'MAD' =>  'Moroccan dirham',
								'MDL' =>  'Moldovan leu',
								'MGA' =>  'Malagasy ariary',
								'MKD' =>  'Macedonian denar',
								'MMK' =>  'Burmese kyat',
								'MNT' =>  'Mongolian t&ouml;gr&ouml;g',
								'MOP' =>  'Macanese pataca',
								'MRO' =>  'Mauritanian ouguiya',
								'MUR' =>  'Mauritian rupee' . '*',
								'MVR' =>  'Maldivian rufiyaa',
								'MWK' =>  'Malawian kwacha',
								'MXN' =>  'Mexican peso' . '*',
								'MYR' =>  'Malaysian ringgit',
								'MZN' =>  'Mozambican metical',
								'NAD' =>  'Namibian dollar',
								'NGN' =>  'Nigerian naira',
								'NIO' =>  'Nicaraguan c&oacute;rdoba' . '*',
								'NOK' =>  'Norwegian krone',
								'NPR' =>  'Nepalese rupee',
								'NZD' =>  'New Zealand dollar',
								'PAB' =>  'Panamanian balboa' . '*',
								'PEN' =>  'Peruvian nuevo sol' . '*',
								'PGK' =>  'Papua New Guinean kina',
								'PHP' =>  'Philippine peso',
								'PKR' =>  'Pakistani rupee',
								'PLN' =>  'Polish z&#x142;oty',
								'PYG' =>  'Paraguayan guaran&iacute;' . '*',
								'QAR' =>  'Qatari riyal',
								'RON' =>  'Romanian leu',
								'RSD' =>  'Serbian dinar',
								'RUB' =>  'Russian ruble',
								'RWF' =>  'Rwandan franc',
								'SAR' =>  'Saudi riyal',
								'SBD' =>  'Solomon Islands dollar',
								'SCR' =>  'Seychellois rupee',
								'SEK' =>  'Swedish krona',
								'SGD' =>  'Singapore dollar',
								'SHP' =>  'Saint Helena pound' . '*',
								'SLL' =>  'Sierra Leonean leone',
								'SOS' => 'Somali shilling',
								'SRD' =>  'Surinamese dollar' . '*',
								'STD' =>  'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra',
								'SZL' =>  'Swazi lilangeni',
								'THB' =>  'Thai baht',
								'TJS' =>  'Tajikistani somoni',
								'TOP' =>  'Tongan pa&#x2bb;anga',
								'TRY' =>  'Turkish lira',
								'TTD' =>  'Trinidad and Tobago dollar',
								'TWD' =>  'New Taiwan dollar',
								'TZS' =>  'Tanzanian shilling',
								'UAH' =>  'Ukrainian hryvnia',
								'UGX' =>  'Ugandan shilling',
								// 'USD' =>  'United States dollar',
								'UYU' =>  'Uruguayan peso' . '*',
								'UZS' =>  'Uzbekistani som',
								'VND' =>  'Vietnamese &#x111;&#x1ed3;ng',
								'VUV' =>  'Vanuatu vatu',
								'WST' =>  'Samoan t&#x101;l&#x101;',
								'XAF' =>  'Central African CFA franc',
								'XCD' =>  'East Caribbean dollar',
								'XOF' =>  'West African CFA franc' . '*',
								'XPF' =>  'CFP franc' . '*',
								'YER' =>  'Yemeni rial',
								'ZAR' =>  'South African rand',
								'ZMW' =>  'Zambian kwacha'
							);
		$this->fields['curency'] = array(
									'type' => 'select'
									, 'default' => 'USD'
									, 'title' => __('Accepted Currency', 'booking')
									, 'description' => __('The currency code that gateway will process the payment in.', 'booking')
													. '<div class="wpbc-settings-notice notice-info" style="text-align:left;"><strong>'
														. __('Note:' ,'booking') . '</strong> '
														. __('Setting the currency that is not supported by the payment processor will result in an error.' ,'booking')
													   . '<br/><strong>' . __( 'For more information:' ) . '</strong> '
													   . '<a href="https://stripe.com/docs/currencies#charge-currencies">Stripe Docs</a>'
	//													   . '<ul style="list-style: inside disc;">'
	//                                                       . ' <li>' . 'JCB, Discover, and Diners Club cards can only be charged in USD' . '</li>'
	//                                                       . ' <li>' . 'Currencies marked with * are not supported by American Express' . '</li>'
	//                                                       . ' <li>' . 'Brazilian Stripe accounts (currently in Preview) can only charge in Brazilian Real' . '</li>'
	//                                                       . ' <li>' . 'Mexican Stripe accounts (currently in Preview) can only charge in Mexican Peso' . '</li>'
	//													   . '</ul>'
													. '</div>'
									, 'description_tag' => 'span'
									, 'css' => ''
									, 'options' => $currency_list
									, 'group' => 'general'
							);
		// Payment Button Title
		$this->fields['payment_button_title'] = array(
								'type'          => 'text'
								, 'default'     => __('Pay via' ,'booking') .' Stripe'
								, 'placeholder' => __('Pay via' ,'booking') .' Stripe'
								, 'title'       => __('Payment button title' ,'booking')
								, 'description' => __('Enter the title of the payment button' ,'booking')
								,'description_tag' => 'p'
								, 'css'         => 'width:100%'
								, 'group'       => 'general'
								, 'tr_class'    => 'wpbc_sub_settings_payment_button_title wpbc_sub_settings_grayed'
						);
		//$this->fields['description_hr'] = array( 'type' => 'hr' );

		// Additional settings /////////////////////////////////////////////////
		$this->fields['subject'] = array(
								'type'          => 'textarea'
								, 'default'     => sprintf(__('Payment for booking %s on these day(s): %s'  ,'booking'),'[resource_title]','[dates]')
								, 'placeholder' => sprintf(__('Payment for booking %s on these day(s): %s'  ,'booking'),'[resource_title]','[dates]')
								, 'title'       => __('Payment description at gateway website' ,'booking')
								, 'description' => sprintf(__('Enter the service name or the reason for the payment here.' ,'booking'),'<br/>','</b>')
													. '<br/>' .  __('You can use any shortcodes, which you have used in content of booking fields data form.' ,'booking')
													// . '<div class="wpbc-settings-notice notice-info" style="text-align:left;"><strong>'
													//    . __('Note:' ,'booking') . '</strong> '
													//    . sprintf( __('This field support only up to %s characters by payment system.' ,'booking'), '255' )
													//. '</div>'
								,'description_tag' => 'p'
								, 'css'         => 'width:100%'
								, 'rows' => 2
								, 'group'       => 'general'
								, 'tr_class'    => 'wpbc_sub_settings_is_description_show wpbc_sub_settings_grayedNO'
						);


		////////////////////////////////////////////////////////////////////
		// Return URL    &   Auto approve | decline
		////////////////////////////////////////////////////////////////////

		//  Success URL
		$this->fields['order_successful_prefix'] = array(
								'type'          => 'pure_html'
								, 'group'       => 'auto_approve_cancel'
								, 'html'        => '<tr valign="top" class="wpbc_tr_stripe_order_successful">
														<th scope="row">'.
															WPBC_Settings_API::label_static( 'stripe_order_successful'
																, array(   'title'=> __('Return URL after Successful order' ,'booking'), 'label_css' => '' ) )
														.'</th>
														<td><fieldset>' . '<code style="font-size:14px;">' .  get_option('siteurl') . '</code>'
								, 'tr_class'    => 'relay_response_sub_class'
						);
		$this->fields['order_successful'] = array(
								'type'          => 'text'
								, 'default'     => '/successful'
								, 'placeholder' => '/successful'
								, 'css'         => 'width:75%'
								, 'group'       => 'auto_approve_cancel'
								, 'only_field'  => true
								, 'tr_class'    => 'relay_response_sub_class'
						);
		$this->fields['order_successful_sufix'] = array(
								'type'          => 'pure_html'
								, 'group'       => 'auto_approve_cancel'
								, 'html'        =>    '<p class="description" style="line-height: 1.7em;margin: 0;">'
														. __('The URL where visitor will be redirected after completing payment.' ,'booking')
														. '<br/>' . sprintf( __('For example, a URL to your site that displays a %s"Thank you for the payment"%s.' ,'booking'),'<b>','</b>')
													. '</p>
														   </fieldset>
														</td>
													</tr>'
								, 'tr_class'    => 'relay_response_sub_class'
						);

		//  Failed URL
		$this->fields['order_failed_prefix'] = array(
								'type'          => 'pure_html'
								, 'group'       => 'auto_approve_cancel'
								, 'html'        => '<tr valign="top" class="wpbc_tr_stripe_order_failed">
														<th scope="row">'.
															WPBC_Settings_API::label_static( 'stripe_order_failed'
																, array(   'title'=> __('Return URL after Failed order' ,'booking'), 'label_css' => '' ) )
														.'</th>
														<td><fieldset>' . '<code style="font-size:14px;">' .  get_option('siteurl') . '</code>'
								, 'tr_class'    => 'relay_response_sub_class'
						);
		$this->fields['order_failed'] = array(
								'type'          => 'text'
								, 'default'     => '/failed'
								, 'placeholder' => '/failed'
								, 'css'         => 'width:75%'
								, 'group'       => 'auto_approve_cancel'
								, 'only_field'  => true
								, 'tr_class'    => 'relay_response_sub_class'
						);
		$this->fields['order_failed_sufix'] = array(
								'type'          => 'pure_html'
								, 'group'       => 'auto_approve_cancel'
								, 'html'        =>    '<p class="description" style="line-height: 1.7em;margin: 0;">'
														. __('The URL where the visitor will be redirected after completing payment.' ,'booking')
														. '<br/>' . sprintf( __('For example, the URL to your website that displays a %s"Payment Canceled"%s page.' ,'booking'),'<b>','</b>' )
													. '</p>
														   </fieldset>
														</td>
													</tr>'
								, 'tr_class'    => 'relay_response_sub_class'
						);
		// Auto Approve / Cancel
        $this->fields['is_auto_approve_cancell_booking'] = array(
                                      'type'        => 'checkbox'
                                    , 'default'     => 'Off'
                                    , 'title'       => __( 'Automatically approve/cancel booking', 'booking' )
                                    , 'label'       => __('Check this box to automatically approve bookings, when visitor makes a successful payment, or automatically cancel the booking, when visitor makes a payment cancellation.' ,'booking')
                                    , 'description' =>  '<div class="wpbc-settings-notice notice-warning" style="text-align:left;">'
                                                            . '<strong>' . __('Warning' ,'booking') . '!</strong> ' . __('This will not work, if the visitor leaves the payment page.' ,'booking')
                                                        . '</div>'
                                    , 'description_tag' => 'p'
                                    , 'group'       => 'auto_approve_cancel'
							        , 'tr_class'    => 'relay_response_sub_class'
                                );

	}

    
    // Support /////////////////////////////////////////////////////////////////

	/**
	 * Return info about Gateway
	 *
	 * @return array        Example: array(
											'id'      => 'stripe
										  , 'title'   => 'Stripe'
										  , 'currency'   => 'USD'
										  , 'enabled' => true
										);
	 */
	public function get_gateway_info() {

		$gateway_info = array(
					  'id'       => $this->get_id()
					, 'title'    => 'Stripe'
					, 'currency' => get_bk_option(  'booking_' . $this->get_id() . '_' . 'curency' )
					, 'enabled'  => $this->is_gateway_on()
		);
		return $gateway_info;
	}

    
    /**
 	 * Get payment Statuses of gateway
     * 
     * @return array
     */
    public function get_payment_status_array() {
        
        return array(
                          'ok'      => array( 'Stripe:OK' )
                        , 'pending' => array( 'Stripe:Pending' )
                        , 'unknown' => array( 'Stripe:Unknown' )
                        , 'error'   => array(   'Stripe:Failed',
												'Stripe:REJECTED',
												'Stripe:NOTAUTHED',
												'Stripe:MALFORMED',
												'Stripe:INVALID',
												'Stripe:ABORT',
												'Stripe:ERROR' )
                    ); 
    }


}

//                                                                              </editor-fold>



//                                                                              <editor-fold   defaultstate="collapsed"   desc=" Settings  Page " >

/** Settings  Page  */
class WPBC_Settings_Page_Gateway_STRIPE extends WPBC_Page_Structure {

	public $gateway_api = false;


	/**
	 * Define interface for  Gateway  API
	 *
	 * @param string $selected_email_name - name of Email template
	 * @param array $init_fields_values - array of init form  fields data - this array  can  ovveride "default" fields and loaded data.
	 * @return object Email API
	 */
	public function get_api( $init_fields_values = array() ){

		if ( $this->gateway_api === false ) {
			$this->gateway_api = new WPBC_Gateway_API_STRIPE( WPBC_STRIPE_GATEWAY_ID , $init_fields_values );
		}

		return $this->gateway_api;
	}


	public function in_page() {                                                 // P a g e    t a g
		return 'wpbc-settings';
	}


	public function tabs() {                                                    // T a b s      A r r a y

		$tabs = array();

		$subtabs = array();

		// Checkbox Icon, for showing in toolbar panel does this payment system active
		$is_data_exist = get_bk_option( 'booking_'. WPBC_STRIPE_GATEWAY_ID .'_is_active' );
		if (  ( ! empty( $is_data_exist ) ) && ( $is_data_exist == 'On' )  )
			$icon = '<i class="menu_icon icon-1x glyphicon glyphicon-check"></i> &nbsp; ';
		else
			$icon = '<i class="menu_icon icon-1x glyphicon glyphicon-unchecked"></i> &nbsp; ';


		$subtabs[ WPBC_STRIPE_GATEWAY_ID ] = array(
							'type' => 'subtab'                                  // Required| Possible values:  'subtab' | 'separator' | 'button' | 'goto-link' | 'html'
							, 'title' =>  $icon . 'Stripe'       // Title of TAB
							, 'page_title' => sprintf( __('%s Settings', 'booking'), 'Stripe' )  // Title of Page
							, 'hint' => sprintf( __('Integration of %s payment system' ,'booking' ), 'Stripe' )    // Hint
							, 'link' => ''                                      // link
							, 'position' => ''                                  // 'left'  ||  'right'  ||  ''
							, 'css_classes' => ''                               // CSS class(es)
							//, 'icon' => 'http://.../icon.png'                 // Icon - link to the real PNG img
							//, 'font_icon' => 'glyphicon glyphicon-envelope'   // CSS definition of Font Icon
							, 'default' =>  false                                // Is this sub tab activated by default or not: true || false.
							, 'disabled' => false                               // Is this sub tab deactivated: true || false.
							, 'checkbox'  => false                              // or definition array  for specific checkbox: array( 'checked' => true, 'name' => 'feature1_active_status' )   //, 'checkbox'  => array( 'checked' => $is_checked, 'name' => 'enabled_active_status' )
							, 'content' => 'content'                            // Function to load as conten of this TAB
						);

		$tabs[ 'payment' ]['subtabs'] = $subtabs;

		return $tabs;
	}


	/** Show Content of Settings page */
	public function content() {


		$this->css();

		////////////////////////////////////////////////////////////////////////
		// Checking
		////////////////////////////////////////////////////////////////////////

		do_action( 'wpbc_hook_settings_page_header', 'gateway_settings');       // Define Notices Section and show some static messages, if needed
		do_action( 'wpbc_hook_settings_page_header', 'gateway_settings_' . WPBC_STRIPE_GATEWAY_ID );

		if ( ! wpbc_is_mu_user_can_be_here('activated_user') ) return false;       // Check if MU user activated, otherwise show Warning message.

		// if ( ! wpbc_is_mu_user_can_be_here('only_super_admin') ) return false;  // User is not Super admin, so exit.  Basically its was already checked at the bottom of the PHP file, just in case.


		////////////////////////////////////////////////////////////////////////
		// Load Data
		////////////////////////////////////////////////////////////////////////

		// $this->check_compatibility_with_older_7_ver();

		$init_fields_values = array();

		$this->get_api( $init_fields_values );


		////////////////////////////////////////////////////////////////////////
		//  S u b m i t   Main Form
		////////////////////////////////////////////////////////////////////////

		$submit_form_name = 'wpbc_gateway_' . WPBC_STRIPE_GATEWAY_ID;               // Define form name

		$this->get_api()->validated_form_id = $submit_form_name;                // Define ID of Form for ability to  validate fields (like required field) before submit.

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


		////////////////////////////////////////////////////////////////////////
		// Content
		////////////////////////////////////////////////////////////////////////
		?>
		<div class="clear" style="margin-bottom:10px;"></div>

		<span class="metabox-holder">
			<form  name="<?php echo $submit_form_name; ?>" id="<?php echo $submit_form_name; ?>" action="" method="post" autocomplete="off">
				<?php
				   // N o n c e   field, and key for checking   S u b m i t
				   wp_nonce_field( 'wpbc_settings_page_' . $submit_form_name );
				?><input type="hidden" name="is_form_sbmitted_<?php echo $submit_form_name; ?>" id="is_form_sbmitted_<?php echo $submit_form_name; ?>" value="1" />


					<div class="clear" style="height:10px;"></div>
					<?php
					if ( version_compare( PHP_VERSION, '5.3.3' ) < 0 ) {
						echo '';
						?>
						<div class="wpbc-settings-notice notice-error" style="text-align:left;">
							<strong><?php _e('Error' ,'booking'); ?></strong>! <?php
								echo 'Stripe ';
								printf( __('require PHP version %s or newer!' ,'booking'), '<strong>5.3.3</strong>');
							?>
						</div>
						<div class="clear" style="height:10px;"></div>
						<?php
					}
					if ( ( ! function_exists('curl_init') ) && ( ! wpbc_is_this_demo() ) ){								//FixIn: 8.1.1.1
						?>
						<div class="wpbc-settings-notice notice-error" style="text-align:left;">
							<strong><?php _e('Error' ,'booking'); ?></strong>! <?php
								echo 'Stripe ';
								printf( 'require CURL library in your PHP!' , '<strong>'.PHP_VERSION.'</strong>');
							?>
						</div>
						<div class="clear" style="height:10px;"></div>
						<?php
					}
					?>
					<div class="wpbc-settings-notice notice-info" style="text-align:left;">
						<strong><?php _e('Note!' ,'booking'); ?></strong> <?php
							printf( __('If you have no account on this system, please visit %s to create one.' ,'booking')
								, '<a href="https://dashboard.stripe.com/register"  target="_blank" style="text-decoration:none;">stripe.com</a>');
						?>
					</div>
					<div class="clear" style="height:5px;"></div>
					<div class="wpbc-settings-notice notice-warning" style="text-align:left;">
						<strong><?php _e('Important!' ,'booking'); ?></strong> <?php
						printf( __('Please configure all fields inside the %sBilling form fields%s section at %sPayments General%s tab.' ,'booking')
							, '<strong>', '</strong>', '<strong>', '</strong>' );
						?>
					</div>
					<div class="clear" style="height:10px;"></div>

				<div class="clear"></div>
				<div class="metabox-holder">

					<div class="wpbc_settings_row wpbc_settings_row_left_NO" >
					<?php
						wpbc_open_meta_box_section( $submit_form_name . 'general', 'Stripe' );
							$this->get_api()->show( 'general' );
						wpbc_close_meta_box_section();
					?>
					</div>
					<div class="clear"></div>


					<div class="wpbc_settings_row wpbc_settings_row_left_NO" >
					<?php
						wpbc_open_meta_box_section( $submit_form_name . 'auto_approve_cancel', __('Advanced', 'booking')   );
							$this->get_api()->show( 'auto_approve_cancel' );
						wpbc_close_meta_box_section();
					?>
					</div>
					<div class="clear"></div>

				</div>

				<input type="submit" value="<?php _e('Save Changes', 'booking'); ?>" class="button button-primary" />
			</form>
		</span>
		<?php

		$this->enqueue_js();
	}


	/** Update Email template to DB */
	public function update() {

		// Get Validated Email fields
		$validated_fields = $this->get_api()->validate_post();

		$validated_fields = apply_filters( 'wpbc_gateway_stripe_validate_fields_before_saving', $validated_fields );   //Hook for validated fields.

		$this->get_api()->save_to_db( $validated_fields );

		wpbc_show_message ( __('Settings saved.', 'booking'), 5 );              // Show Save message
	}


	// <editor-fold     defaultstate="collapsed"                        desc=" CSS & JS  "  >

	/** CSS for this page */
	private function css() {
		?>
		<style type="text/css">
			.wpbc-help-message {
				border:none;
				margin:0 !important;
				padding:0 !important;
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
		$js_script = '';

		//Show|Hide grayed section
		$js_script .= " 
						if ( ! jQuery('#stripe_mode_test').is(':checked') ) {   
							jQuery('.wpbc_sub_settings_mode_test').addClass('hidden_items'); 
						}
						if ( ! jQuery('#stripe_mode_live').is(':checked') ) {   
							jQuery('.wpbc_sub_settings_mode_live').addClass('hidden_items'); 
						}
					  ";
		// Hide|Show  on Click      Radion
		$js_script .= " jQuery('input[name=\"stripe_account_mode\"]').on( 'change', function(){    
								jQuery('.wpbc_sub_settings_mode_test,.wpbc_sub_settings_mode_live').addClass('hidden_items'); 
								if ( jQuery('#stripe_mode_test').is(':checked') ) {   
									jQuery('.wpbc_sub_settings_mode_test').removeClass('hidden_items');
								} else {
									jQuery('.wpbc_sub_settings_mode_live').removeClass('hidden_items');
								}
							} ); ";

		// Eneque JS to  the footer of the page
		wpbc_enqueue_js( $js_script );
	}

	// </editor-fold>

}
add_action('wpbc_menu_created',  array( new WPBC_Settings_Page_Gateway_STRIPE() , '__construct') );    // Executed after creation of Menu



/**
 * Override VALIDATED fields BEFORE saving to DB
 * Description:
 * Check "Return URLs" and "STRIPE Email"m, etc...
 *
 * @param array $validated_fields
 */
function wpbc_gateway_stripe_validate_fields_before_saving__all( $validated_fields ) {

	$validated_fields['order_successful'] = wpbc_make_link_relative( $validated_fields['order_successful'] );
	$validated_fields['order_failed']     = wpbc_make_link_relative( $validated_fields['order_failed'] );

	if ( wpbc_is_this_demo() ) {
		$validated_fields['publishable_key'] 	  = 'pk_test_6pRNASCoBOKtIshFeQd4XMUh';
		$validated_fields['secret_key']      	  = 'sk_test_BQokikJOvBiI2HlWgH4olfQ2';
		$validated_fields['publishable_key_test'] = 'pk_test_6pRNASCoBOKtIshFeQd4XMUh';
		$validated_fields['secret_key_test']      = 'sk_test_BQokikJOvBiI2HlWgH4olfQ2';
		$validated_fields['account_mode'] 		  = 'test';
	}

	return $validated_fields;
}
add_filter( 'wpbc_gateway_stripe_validate_fields_before_saving', 'wpbc_gateway_stripe_validate_fields_before_saving__all', 10, 1 );   // Hook for validated fields.

//                                                                              </editor-fold>



//                                                                              <editor-fold   defaultstate="collapsed"   desc=" Activate | Deactivate " >

////////////////////////////////////////////////////////////////////////////////
// Activate | Deactivate
////////////////////////////////////////////////////////////////////////////////

/** A c t i v a t e */
function wpbc_booking_activate_STRIPE() {

	$op_prefix = 'booking_' . WPBC_STRIPE_GATEWAY_ID . '_';

	add_bk_option( $op_prefix . 'is_active',    		( wpbc_is_this_demo() ? 'On' : 'Off' )  );
	add_bk_option( $op_prefix . 'account_mode',         'test' );
	add_bk_option( $op_prefix . 'publishable_key', 		( wpbc_is_this_demo() ? 'pk_test_6pRNASCoBOKtIshFeQd4XMUh' : '' ) );
	add_bk_option( $op_prefix . 'secret_key', 			( wpbc_is_this_demo() ? 'sk_test_BQokikJOvBiI2HlWgH4olfQ2' : '' ) );
	add_bk_option( $op_prefix . 'publishable_key_test', ( wpbc_is_this_demo() ? 'pk_test_6pRNASCoBOKtIshFeQd4XMUh' : '' ) );
	add_bk_option( $op_prefix . 'secret_key_test', 		( wpbc_is_this_demo() ? 'sk_test_BQokikJOvBiI2HlWgH4olfQ2' : '' ) );
	add_bk_option( $op_prefix . 'curency',          	'USD' );
	add_bk_option( $op_prefix . 'payment_button_title' , __('Pay via' ,'booking') .' Stripe');
	add_bk_option( $op_prefix . 'subject',      		 sprintf( __('Payment for booking %s on these day(s): %s'  ,'booking'), '[resource_title]','[dates]') );
	add_bk_option( $op_prefix . 'order_successful',     '/successful' );
	add_bk_option( $op_prefix . 'order_failed',         '/failed');
	add_bk_option( $op_prefix . 'is_auto_approve_cancell_booking' , 'Off' );
}
add_bk_action( 'wpbc_other_versions_activation',   'wpbc_booking_activate_STRIPE'   );


/** D e a c t i v a t e */
function wpbc_booking_deactivate_STRIPE() {

	$op_prefix = 'booking_' . WPBC_STRIPE_GATEWAY_ID . '_';

	delete_bk_option( $op_prefix . 'is_active' );
	delete_bk_option( $op_prefix . 'account_mode' );
	delete_bk_option( $op_prefix . 'publishable_key' );
	delete_bk_option( $op_prefix . 'secret_key' );
	delete_bk_option( $op_prefix . 'publishable_key_test' );
	delete_bk_option( $op_prefix . 'secret_key_test' );
	delete_bk_option( $op_prefix . 'curency' );
	delete_bk_option( $op_prefix . 'payment_button_title' );
	delete_bk_option( $op_prefix . 'subject' );
	delete_bk_option( $op_prefix . 'order_successful' );
	delete_bk_option( $op_prefix . 'order_failed' );
	delete_bk_option( $op_prefix . 'is_auto_approve_cancell_booking' );
}
add_bk_action( 'wpbc_other_versions_deactivation', 'wpbc_booking_deactivate_STRIPE' );

//                                                                              </editor-fold>


// Hook for getting gateway payment form to  show it after  booking process,  or for "payment request" after  clicking on link in email.
// Note,  here we generate new Object for correctly getting payment fields data of specific WP User  in WPBC MU version. 
add_filter( 'wpbc_get_gateway_payment_form', array( new WPBC_Gateway_API_STRIPE( WPBC_STRIPE_GATEWAY_ID ), 'get_payment_form' ), 10, 3 );