<?php
/**
 * @version 1.0
 * @package  iDEAL via Sisow
 * @category Payment Gateway for Booking Calendar 
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2017-03-27
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly
                                                                                
if ( ! defined( 'WPBC_IDEAL_GATEWAY_ID' ) )        define( 'WPBC_IDEAL_GATEWAY_ID', 'ideal' );    



//                                                                              <editor-fold   defaultstate="collapsed"   desc=" iDEAL    S u p p o r t   F u n c t i o n s " >    


    function wpbc_ideal_transaction_request() {
        
        // Get Sisow integration Object
        $sisow_obj = wpbc_ideal_get_sisow_obj();
        
        $payment_options = $sisow_obj['payment_options'];
        
        $sisow = $sisow_obj['sisow'];

        // Define parameters from Ajax request
        //TODO: Clean  here
        $sisow->payment     = $_REQUEST['ideal_obj']['payment'];                // '' - empty (iDEAL) - Type of payment option (initially empty so by default iDEAL will be selected)
        $sisow->issuerId    = $_REQUEST['ideal_obj']['issuerid'];               // '99' - test          ID of the selected iDEAL bank, see DirectoryRequest; if not available then redirect to the sisow bank selectionpage
        
        $sisow->purchaseId  = intval( $_REQUEST['ideal_obj']['purchaseid'] );   // Booking ID (also set to entranceCode in sisow.cls5)   // maximum of 16 characters
        $sisow->description = $_REQUEST['ideal_obj']['description'];            // mandatory; max 32 alphanumeric    
        $sisow->amount      = $_REQUEST['ideal_obj']['amount'];                 //'2.34'    // mandatory; min 0.45

        $params__nonce = $_REQUEST['ideal_obj']['ideal_nonce'];

        /**
	 * returnurl
            The URL to which is returned after a 'normal' transaction process.

            cancelurl 
            The URL to which is returned after an unsuccessful transaction. If not supplied then the returnurl is used.

            callbackurl 
            This URL is used within 20 minutes after a transaction to report the status of transactions which were not
            completed 'normally' (closed browser or browsing to another page immediately after a transaction). 
            The Callback Daemon initially verifies if 'notifyurl' is supplied so it can be used. ‘callback=true’ will then be appended to the querystring.

            notifyurl 
            This URL is used to report the status of a transaction and will be called up to 5 times.
            After a completed transaction 'returnurl' will be used to return to if a transaction was successful and 'cancelurl' if a
            transaction was not successful. 'notifiy=true' will be appended to the querystring.
         */
        
        $return_link = WPBC_PLUGIN_URL  . '/inc/gateways/wpbc-response.php'
                                        . '?payed_booking=' . $sisow->purchaseId  // Booking ID
                                        . '&wp_nonce=' . $params__nonce
                                        . '&pay_sys=ideal'; 
        
	$sisow->returnUrl = $return_link;                                       //"http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
	$sisow->notifyUrl = $sisow->returnUrl;

//debuge('$payment_options, $sisow',$payment_options, $sisow);return;        

        // Send curl transaction
        $ex = $sisow->TransactionRequest();

	if ( ( $ex ) < 0 ) {                                                    // Error            
            echo '<span class="wpdev-help-message wpdev-element-message alert alert-warning ">'
                 . '<strong>Error!</strong> <strong>'. $sisow->errorCode . ' <code>'. $ex . '</code></strong> '. $sisow->errorMessage
                 .'</span><div class="clear" style="margin:10px;"></div>';            
            return;
	}        
        // Good - redirect  to Sisow
        ?> <script type='text/javascript'> window.location.replace("<?php echo $sisow->issuerUrl; ?>"); </script> <?php
    }

    
    function wpbc_ideal_get_sisow_obj( $params = array() ) {
        
        // Load Sisow wrapper class
        require_once ( dirname(__FILE__) . "/sisow.cls5.php" );

        $payment_options = array();
        $payment_options[ 'is_active' ]             = get_bk_option( 'booking_ideal_is_active' );                // 'On' | 'Off'   
                        
        if ( ! empty( $params ) ) {
            $payment_options[ 'subject' ]               = get_bk_option( 'booking_ideal_subject' );                  // 'Payment for booking %s on these day(s): %s'
                $payment_options[ 'subject' ] = apply_bk_filter('wpdev_check_for_active_language', $payment_options[ 'subject' ] );
                $payment_options[ 'subject' ] = wpbc_replace_booking_shortcodes( $payment_options[ 'subject' ], $params );
                $payment_options[ 'subject' ] = str_replace('"', '', $payment_options[ 'subject' ]);
                $payment_options[ 'subject' ] = substr($payment_options[ 'subject' ], 0, 32);       
        }
        $payment_options[ 'return_url' ]            = get_bk_option( 'booking_ideal_return_url' );              // '/successful'
        $payment_options[ 'cancel_return_url' ]     = get_bk_option( 'booking_ideal_cancel_return_url' );       // '/failed'
        
        $payment_options[ 'payment_button_title' ]  = get_bk_option( 'booking_ideal_payment_button_title' );    // 'Pay via iDEAL'        
            $payment_options[ 'payment_button_title' ]  =  apply_bk_filter('wpdev_check_for_active_language', $payment_options[ 'payment_button_title' ] );
                    
        $payment_options[ 'merchantid' ]   = get_bk_option( 'booking_ideal_merchant_id' );  // 
        $payment_options[ 'merchantkey' ]  = get_bk_option( 'booking_ideal_merchant_key' ); // 
        $payment_options[ 'shopid' ]       = '';
        $payment_options[ 'is_testmode' ]  = ( ( get_bk_option( 'booking_ideal_test' ) == 'TEST' ) ? true : false );
                
        //$payment_options[ 'curency' ]      = get_bk_option( 'booking_ideal_curency' );                  // 'EUR'            
        $payment_options[ 'is_auto_approve_cancell_booking' ] = get_bk_option( 'booking_ideal_is_auto_approve_cancell_booking' );      // 'On' | 'Off'   
        
        // New instance
        $sisow = new Sisow( $payment_options[ 'merchantid' ], $payment_options[ 'merchantkey' ], $payment_options[ 'shopid' ] );
    
        return array( 'sisow' => $sisow, 'payment_options' => $payment_options );
    }
//                                                                              </editor-fold>


//                                                                              <editor-fold   defaultstate="collapsed"   desc=" Gateway API " >

/** API  for  Payment Gateway  */
class WPBC_Gateway_API_IDEAL extends WPBC_Gateway_API  {                     
    

    
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

        // Check  if currently  is showing this Gateway
        if (    
                   (  ( ! empty( $gateway_id ) ) && ( $gateway_id !== $this->get_id() )  )      // Does we need to show this Gateway
                || ( ! $this->is_gateway_on() )                                                 // Payment Gateway does NOT active
           ) return $output ; 


        // Get Sisow integration Object
        $sisow_obj = wpbc_ideal_get_sisow_obj( $params );
        $sisow = $sisow_obj['sisow'];
        $payment_options = $sisow_obj['payment_options'];


        ////////////////////////////////////////////////////////////////////////
        // Payment Form 
        ////////////////////////////////////////////////////////////////////////
        ob_start();
        
        $html_client_id = 'ideal_' . $params['booking_resource_id'];            // ideal_XXX        // Based on booking resource ID $params['booking_resource_id'] (also may be to  use $params['booking_id'] ??? )
        
        ?><div style="width:100%;clear:both;margin-top:20px;"></div><?php         
                   // ideal_XXX
        ?><div id="<?php echo $html_client_id; ?>" class="ideal_div wpbc-payment-form" style="text-align:left;clear:both;"><?php 
        
        
        /**
	 * We need to open payment form in separate window, if this booking was made togather with other
         *  in booking form  was used several  calendars from  different booking resources. 
         *  So we are having several  payment forms for each  booked resource. 
         *  System transfer this parameter $params['payment_form_target'] = ' target="_blank" ';
         *  otherwise $params['payment_form_target'] = '';
        */ 
        ?><form method="post" name="iDEAL_Payment<?php echo $html_client_id; ?>"><?php 

            // Create Nonce Field.      action: WPBC_PAY_VIA_iDEAL     name: 'wpbc_nonce_ideal_XXXX'
            echo  wp_nonce_field( 'WPBC_PAY_VIA_iDEAL', 'wpbc_nonce_' . $html_client_id ,  true , false );

            // Section  for Ajax response - here will be code after response from  server
            echo '<div class="wpbc_ideal_ajax_response" style="display:block;"></div>';

            // gateway_hint:  ( Deposit | Total | Amount to pay )
            echo "<strong>" . $params['gateway_hint'] . ': ' . $params[ 'cost_in_gateway_hint' ] . "</strong><br />";


            // Get List of BANKs ///////////////////////////////////////////////////

            // $return_banks - will  be pre filled by data
            $is_selectbox = false;   // Reurn Array | selectbox options
            $is_error = $sisow->DirectoryRequest( $return_banks, $is_selectbox, $payment_options[ 'is_testmode' ] );    // there are 2 methods for filling the available issuers in the select/dropdown below, the REST method DirectoryRequest is used
            if ( $is_error < 0 ) {
                // Some error
                if ( $is_error === -1 ) 
                    echo '<span class="wpdev-help-message wpdev-element-message alert alert-warning ">'
                         . '<strong>Error!</strong> <strong>'. $sisow->errorCode . ' <code>'. $ex . '</code></strong> '. 'iDEAL response failed'
                         .'</span><div class="clear" style="margin:10px;"></div>';            
                if ( $is_error === -2 ) 
                    echo '<span class="wpdev-help-message wpdev-element-message alert alert-warning ">'
                         . '<strong>Error!</strong> <strong>'. $sisow->errorCode . ' <code>'. $ex . '</code></strong> '. 'iDEAL response parse failed. ' . $sisow->errorMessage
                         .'</span><div class="clear" style="margin:10px;"></div>';            
                $payment_form = ob_get_clean();

                return $output . $payment_form;         
            }
            ////////////////////////////////////////////////////////////////////////

            // Payment request form
            ?>
            <style type="text/css">
                table.wpbc_ideal_payment_table {
                    margin:10px 0;
                    width:100%;
                    border: none;
                }
                .wpbc_ideal_payment_table td {
                    padding:10px 0;
                    border: none;
                }
                .wpbc_ideal_payment_table td label{
                    font-weight: 600;
                }
            </style>
            <?php
            ?><table class="wpbc_ideal_payment_table" cellspacing="0" cellpadding="0"><?php 
            
            // Its from PDF tutorial  ???
            $payment_method = array(
                                        '' => 'iDEAL'
                                      , 'mistercash' => 'MisterCash'
                                      , 'ebill' => 'digital acceptgiro'
                                      , 'overboeking' => 'bank/giro transfer'
                                      , 'sofort' => 'SofortBanking/DIRECTebanking'
                                      , 'paypalec' => 'PayPal'
                                      , 'webshop' => 'Webshop Giftcard'
                                      //, 'podium' => 'Podium Cadeaukaart'
                                      , 'maestro' => 'Maestro'
                                      , 'mastercard' => 'MasterCard'
                                      , 'visa' => 'Visa'
                                );            
            // This list  from payment sisow example 
            $payment_method = array(
                                        '' => 'iDEAL'
                                      , 'sofort' => 'DIRECTebanking'
                                      , 'mistercash' => 'MisterCash'
                                      , 'webshop' => 'WebShop GiftCard'
                                      , 'podium' => 'Podium Cadeaukaart'
                                );
            // Payment method ///////////////////////////////////////////////////
            ?><tr>
                <td><label><?php _e( 'Pay via', 'booking' ); ?></label>:</td>                
                <td>
                    <select name="ideal_payment" style="height:29px;padding:0 6px;" size="1">
                    <?php 
                        foreach ( $payment_method as $payment_method_id => $payment_method_name ) {
                            ?><option value="<?php echo $payment_method_id; ?>"><?php echo $payment_method_name; ?></option><?php
                        }
                    ?>                        
                    </select>
                </td>
            </tr>
            <?php 
        
            // Payment Bank ///////////////////////////////////////////////////
            ?><tr>
                <td><label><?php _e( 'Bank Name', 'booking' ); ?></label>:</td>
                <td>
                    <?php if ( is_array(  $return_banks ) )  { ?>
                        <select name="ideal_issuerid" style="height:29px;padding:0 6px;" size="1">
                        <?php 
                            foreach ( $return_banks as $bank_id => $bank_name ) {
                                ?><option value="<?php echo $bank_id; ?>"><?php echo $bank_name; ?></option><?php
                            }
                        ?>                        
                        </select>
                    <?php } ?>
                </td>
            </tr>
            <?php 
                        
            ?><tr><td colspan="2" style="display:none;"><?php 

                // wp_nonce, for checking booking in response from server
                ?><input type="hidden" name="ideal_nonce" value="<?php echo $params['__nonce']; ?>" /><?php   

                // Payment reference             purchaseid -> booking ID     
                ?><input type="hidden" name="purchaseid" maxlength="16" value="<?php echo substr( $params[ 'booking_id' ], 0, 16 ); ?>" /><?php   // Booking ID (also set to entranceCode in sisow.cls5)  

                // Description
                ?><input type="hidden" name="description" maxlength="32" value="<?php echo $payment_options[ 'subject' ]; ?>" /><?php              

                // Amount
                ?><input type="hidden" name="amount" size="10" title="Cost" value="<?php echo $params['cost_in_gateway']; ?>" /><?php       // mandatory; min 0.45
        
            ?></td></tr><?php 
            
            ?></table><?php
 
            ?><a href="javascript:void(0);" class="btn" onclick="javascript:wpbc_pay_via_ideal(  <?php echo $params['booking_resource_id']; ?> );"><?php echo $payment_options[ 'payment_button_title' ]; ?></a><?php 
 
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
        // Merchant ID
        $this->fields['merchant_id'] = array(   
                                      'type'        => 'text'
                                    , 'default'     => ''
                                    //, 'placeholder' => ''
                                    , 'title'       => __('Merchant ID', 'booking')
                                    , 'description' => __('Required', 'booking') . '.<br/>'
                                                       . __('Enter your iDEAL Merchant ID' ,'booking')
                                                       . ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() : '' )
                                    , 'description_tag' => 'span'
                                    , 'css'         => ''//'width:100%'
                                    , 'group'       => 'general'
                                    , 'tr_class'    => 'wpbc_sub_settings_grayed'
                                    //, 'validate_as' => array( 'required' )
                            );
        // Merchant Key
        $this->fields['merchant_key'] = array(   
                                      'type'        => 'text'
                                    , 'default'     => ''
                                    //, 'placeholder' => ''
                                    , 'title'       => __('Merchant Key', 'booking')
                                    , 'description' => __('Required', 'booking') . '.<br/>'
                                                       . __('Enter your iDEAL Merchant Key.' ,'booking')
                                                       . ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() : '' )
                                    , 'description_tag' => 'span'
                                    , 'css'         => ''//'width:100%'
                                    , 'group'       => 'general'
                                    , 'tr_class'    => 'wpbc_sub_settings_grayed'
                                    //, 'validate_as' => array( 'required' )
                            );
        
        
        // Payment mode        
        $this->fields['test'] = array(   
                                    'type' => 'select'
                                    , 'default' => 'TEST'
                                    , 'title' => __('Chose payment mode' ,'booking')
                                    , 'description' => __('Select TEST for the Test Server and LIVE in the live environment' ,'booking') 
                                                    . '<div class="wpbc-settings-notice notice-info" style="text-align:left;"><strong>' 
                                                        . __('Note:' ,'booking') . '</strong> '
                                                        . sprintf( __('Test mode requires the option %s to be selected in the %s account configuration section %s', 'booking' )
                                                                    , '<strong>' .  __( 'Test with Simulator' , 'booking' ) . '</strong>'
                                                                    , '<strong>Sisow</strong>'
                                                                    , '<strong>' . __( 'My Profile – Connection' , 'booking' ) . '</strong>'
                                                                )
                                                    . '</div>'
                                    , 'description_tag' => 'span'
                                    , 'css' => ''
                                    , 'options' => array(   // 'SIMULATOR' => __('SIMULATOR', 'booking')  // Does not support in actual api
                                                            'TEST' => __('TEST', 'booking')  
                                                          , 'LIVE' => __('LIVE', 'booking')                                                              
                                                    )      
                                    , 'group' => 'general'
                            );
        /*                    
        // Currency        
        $currency_list = array(
                                  "EUR" => __('Euros' ,'booking')
                                //, "USD" => __('U.S. Dollars' ,'booking')
                            );
        $this->fields['curency'] = array(   
                                    'type' => 'select'
                                    , 'default' => 'EUR'
                                    , 'title' => __('Accepted Currency', 'booking')
                                    , 'description' => __('The currency code that gateway will process the payment in.', 'booking')  
                                    , 'description_tag' => 'span'
                                    , 'css' => ''
                                    , 'options' => $currency_list
                                    , 'group' => 'general'
                            );
         */
        // Payment Button Title        
        $this->fields['payment_button_title'] = array(   
                                'type'          => 'text'
                                , 'default'     => __('Pay via' ,'booking') .' iDEAL'
                                , 'placeholder' => __('Pay via' ,'booking') .' iDEAL'
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
                                                    . '<div class="wpbc-settings-notice notice-info" style="text-align:left;"><strong>' 
                                                        . __('Note:' ,'booking') . '</strong> '
                                                        . sprintf( __('This field support only up to %s characters by payment system.' ,'booking'), '32' ) 
                                                    . '</div>'
                                                    . '<div class="wpbc-settings-notice notice-info" style="text-align:left;"><strong>' 
                                                        . __('Note:' ,'booking') . '</strong> '
                                                        . sprintf( __('If not supplied then the description as configured in the administration/management portal section will be used. If the option %s is selected then the configured description will always be applied.', 'booking' )
                                                                    , '<strong>' . __( 'Always use Description', 'booking' ) . '</strong>'
                                                                )
                                                    . '</div>'

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
        $this->fields['return_url_prefix'] = array(   
                                'type'          => 'pure_html'
                                , 'group'       => 'auto_approve_cancel'     
                                , 'html'        => '<tr valign="top" class="wpbc_tr_ideal_return_url">
                                                        <th scope="row">'.
                                                            WPBC_Settings_API::label_static( 'ideal_return_url'
                                                                , array(   'title'=> __('Return URL after Successful order' ,'booking'), 'label_css' => '' ) )
                                                        .'</th>
                                                        <td><fieldset>' . '<code style="font-size:14px;">' .  get_option('siteurl') . '</code>'
                        );                
        $this->fields['return_url'] = array(   
                                'type'          => 'text'
                                , 'default'     => '/successful'
                                , 'placeholder' => '/successful'
                                , 'css'         => 'width:75%'
                                , 'group'       => 'auto_approve_cancel'
                                , 'only_field'  => true           
                        );
        $this->fields['return_url_sufix'] = array(   
                                'type'          => 'pure_html'
                                , 'group'       => 'auto_approve_cancel'
                                , 'html'        =>    '<p class="description" style="line-height: 1.7em;margin: 0;">' 
                                                        . __('The URL where visitor will be redirected after completing payment.' ,'booking') 
                                                        . '<br/>' . sprintf( __('For example, a URL to your site that displays a %s"Thank you for the payment"%s.' ,'booking'),'<b>','</b>')
                                                    . '</p>
                                                           </fieldset>
                                                        </td>
                                                    </tr>'            
                        );        

        //  Failed URL
        $this->fields['cancel_return_url_prefix'] = array(   
                                'type'          => 'pure_html'
                                , 'group'       => 'auto_approve_cancel'     
                                , 'html'        => '<tr valign="top" class="wpbc_tr_ideal_cancel_return_url">
                                                        <th scope="row">'.
                                                            WPBC_Settings_API::label_static( 'ideal_cancel_return_url'
                                                                , array(   'title'=> __('Return URL after Failed order' ,'booking'), 'label_css' => '' ) )
                                                        .'</th>
                                                        <td><fieldset>' . '<code style="font-size:14px;">' .  get_option('siteurl') . '</code>'
                        );                
        $this->fields['cancel_return_url'] = array(   
                                'type'          => 'text'
                                , 'default'     => '/failed'
                                , 'placeholder' => '/failed'
                                , 'css'         => 'width:75%'
                                , 'group'       => 'auto_approve_cancel'
                                , 'only_field'  => true           
                        );
        $this->fields['cancel_return_url_sufix'] = array(   
                                'type'          => 'pure_html'
                                , 'group'       => 'auto_approve_cancel'
                                , 'html'        =>    '<p class="description" style="line-height: 1.7em;margin: 0;">' 
                                                        . __('The URL where the visitor will be redirected after completing payment.' ,'booking') 
                                                        . '<br/>' . sprintf( __('For example, the URL to your website that displays a %s"Payment Canceled"%s page.' ,'booking'),'<b>','</b>' )
                                                    . '</p>
                                                           </fieldset>
                                                        </td>
                                                    </tr>'            
                        );             
        //Auto Approve | Cancel                              
        $this->fields['is_auto_approve_cancell_booking'] = array(   
                                      'type'        => 'checkbox'
                                    , 'default'     => 'Off'            
                                    , 'title'       => __( 'Automatically approve/cancel booking', 'booking' )
                                    , 'label'       => __('Check this box to automatically approve bookings, when visitor makes a successful payment, or automatically cancel the booking, when visitor makes a payment cancellation.' ,'booking')
//                                    , 'description' =>  '<div class="wpbc-settings-notice notice-warning" style="text-align:left;">'
//                                                            . '<strong>' . __('Warning' ,'booking') . '!</strong> ' . __('This will not work, if the visitor leaves the payment page.' ,'booking')
//                                                        . '</div>'
                                    , 'description_tag' => 'p'
                                    , 'group'       => 'auto_approve_cancel'
                                );
    }

    
    // Support /////////////////////////////////////////////////////////////////    

    
    /**
	 * Return info about Gateway
     * 
     * @return array        Example: array(
                                            'id'      => 'ideal
                                          , 'title'   => 'iDEAL'
                                          , 'currency'   => 'EUR'
                                          , 'enabled' => true
                                        );        
     */
    public function get_gateway_info() {

        $gateway_info = array(
                      'id'       => $this->get_id()
                    , 'title'    => 'iDEAL Sisow'
                    , 'currency' => ''  // get_bk_option(  'booking_' . $this->get_id() . '_' . 'curency' )
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
                        'ok'        => array( 'ideal:Success' )
                        , 'pending' => array( 'ideal:Expired' )
                        , 'unknown' => array()
                        , 'error'   => array( 'ideal:Failure', 'ideal:Cancelled' )
                    ); 
    }

    
    //  R E S P O N S E  ///////////////////////////////////////////////////////
    
    /**
	 * Update Payment Status after  Response from specific Payment system website.
     *
     * @param type $response_status
     * @param type $pay_system
     * @param type $status
     * @param type $booking_id
     * @param type $wp_nonce
     * 
     * @return string - $response_status
     */  
    public function update_payment_status__after_response( $response_status, $pay_system, $status, $booking_id, $wp_nonce ) {    
        
        if ( $pay_system == WPBC_IDEAL_GATEWAY_ID ) { 

//debuge( $_REQUEST );            
            /**
	 * $_REQUEST:
            Success
                        [payed_booking] => 213
                        [wp_nonce] => 149062421825
                        [pay_sys] => ideal
                        [status] => Success
                        [trxid] => TEST080518182127
                        [ec] => 213
                        [sha1] => a447ba32cb1dc67816b641fc64d0094360a422dc
            Cancelled
                        [payed_booking] => 214
                        [wp_nonce] => 149062466028
                        [pay_sys] => ideal
                        [status] => Cancelled
                        [trxid] => TEST080518182338
                        [ec] => 214
                        [sha1] => 8e69dcbe92df84e9c0ee85e48a4d7e11fe3dc35a
            Expired
                        [payed_booking] => 215
                        [wp_nonce] => 149062476103
                        [pay_sys] => ideal
                        [status] => Expired
                        [trxid] => TEST080518182381
                        [ec] => 215
                        [sha1] => ccc0f423d034c10e6a0f45a24b87a4ba3c5d25f3
            Failure
                        [payed_booking] => 216
                        [wp_nonce] => 149062482948
                        [pay_sys] => ideal
                        [status] => Failure
                        [trxid] => TEST080518182414
                        [ec] => 216
                        [sha1] => eb9372a66dea3c934def106427efd43a33cec943
             */
            
            if ( isset( $_REQUEST['trxid'] ) ) {
                
                $status = '';
                
                if ( isset( $_REQUEST['notify'] ) || isset( $_REQUEST['callback'] ) ) {
                    /**
	 * Auto Response from iDEAL (Sisow)
                     * callbackurl 
                        This URL is used within 20 minutes after a transaction to report the status of transactions which were not
                        completed 'normally' (closed browser or browsing to another page immediately after a transaction). 
                        The Callback Daemon initially verifies if 'notifyurl' is supplied so it can be used. ‘callback=true’ will then be appended to the querystring.

                        notifyurl 
                        This URL is used to report the status of a transaction and will be called up to 5 times.
                        After a completed transaction 'returnurl' will be used to return to if a transaction was successful and 'cancelurl' if a
                        transaction was not successful. 'notifiy=true' will be appended to the querystring.
                     */
                
                    // Get Sisow integration Object
                    $sisow_obj = wpbc_ideal_get_sisow_obj();
                    $sisow = $sisow_obj['sisow'];
                    $sisow->StatusRequest( $_REQUEST["trxid"] );
		
                    if( $sisow->status == "Success" ) {
                        $status = 'ideal:Success';   
                    }
                    
                } else {

                    if( $_REQUEST['status'] == 'Success')
                        $status = 'ideal:Success';   
                    
                    if( $_REQUEST['status'] == 'Cancelled')
                        $status = 'ideal:Cancelled';   
                    
                    if( $_REQUEST['status'] == 'Expired')
                        $status = 'ideal:Expired';   
                    
                    if( $_REQUEST['status'] == 'Failure')
                        $status = 'ideal:Failure';   
                }
                
                return $status;
                
            }

        }                      
        return $response_status;                
    }
    
               
    /**
	 * If activated "Auto approve|decline" and then Redirect to  "Success" or "Failed" payment page.
     * 
     * @param string $pay_system - name of gateway
     * @param string $status     - status of payment   
     * @param type $booking_id
     */
    public function auto_approve_or_cancell_and_redirect( $pay_system, $status, $booking_id ) {
        
        if ( $pay_system == WPBC_IDEAL_GATEWAY_ID ) {

            $auto_approve = get_bk_option( 'booking_ideal_is_auto_approve_cancell_booking' ); 

            $payment_status = $this->get_payment_status_array();            
            
            if ( in_array( $status,  $payment_status['ok'] ) ) {
                if ( $auto_approve == 'On' ) wpbc_auto_approve_booking( $booking_id );  
                wpbc_redirect( get_bk_option( 'booking_ideal_return_url' ) );
            }  
                
            if ( in_array( $status,  $payment_status['error'] ) ) {            
                if ( $auto_approve == 'On' ) wpbc_auto_cancel_booking( $booking_id );
                wpbc_redirect( get_bk_option( 'booking_ideal_cancel_return_url' ) );
            }
        }
        
    }

}

//                                                                              </editor-fold>



//                                                                              <editor-fold   defaultstate="collapsed"   desc=" Settings  Page " >

/** Settings  Page  */
class WPBC_Settings_Page_Gateway_IDEAL extends WPBC_Page_Structure {
     
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
            $this->gateway_api = new WPBC_Gateway_API_IDEAL( WPBC_IDEAL_GATEWAY_ID , $init_fields_values );    
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
        $is_data_exist = get_bk_option( 'booking_'. WPBC_IDEAL_GATEWAY_ID .'_is_active' );
        if (  ( ! empty( $is_data_exist ) ) && ( $is_data_exist == 'On' )  )
            $icon = '<i class="menu_icon icon-1x glyphicon glyphicon-check"></i> &nbsp; ';
        else 
            $icon = '<i class="menu_icon icon-1x glyphicon glyphicon-unchecked"></i> &nbsp; ';
        
        
        $subtabs[ WPBC_IDEAL_GATEWAY_ID ] = array( 
                            'type' => 'subtab'                                  // Required| Possible values:  'subtab' | 'separator' | 'button' | 'goto-link' | 'html'
                            , 'title' =>  $icon .  'iDEAL Sisow'                     // Title of TAB    
                            , 'page_title' => sprintf( __('%s Settings', 'booking'), 'iDEAL' )  // Title of Page   
                            , 'hint' => sprintf( __('Integration of %s payment system', 'booking'), 'iDEAL Sisow' )  
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
        do_action( 'wpbc_hook_settings_page_header', 'gateway_settings_' . WPBC_IDEAL_GATEWAY_ID );
        
        if ( ! wpbc_is_mu_user_can_be_here('activated_user') ) return false;    // Check if MU user activated, otherwise show Warning message.
   
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
        
        $submit_form_name = 'wpbc_gateway_' . WPBC_IDEAL_GATEWAY_ID;               // Define form name
        
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
<?php /**/ ?>
                    <div class="clear" style="height:10px;"></div>
                    <div class="wpbc-settings-notice notice-success" style="text-align:left;">
                        <?php 
                                printf( __('Processing your %s payments through %s' ,'booking')
                                        , '<strong>iDeal</strong>', '<strong><a href="https://www.sisow.nl" target="_blank">Sisow</a></strong>' );
                        ?>
                    </div>
                    <div class="clear" style="height:10px;"></div>
<?php /**/ ?>                    
                <div class="clear"></div>  
                <div class="metabox-holder">

                    <div class="wpbc_settings_row wpbc_settings_row_left_NO" >
                    <?php                             
                        wpbc_open_meta_box_section( $submit_form_name . 'general', sprintf( __('%s Settings', 'booking'), 'iDEAL' )   );                            
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
        
        $validated_fields = apply_filters( 'wpbc_gateway_ideal_validate_fields_before_saving', $validated_fields );   //Hook for validated fields.
        
//debuge($validated_fields);        
        
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
        
        /*
        $js_script = '';
        
        //Show|Hide grayed section   
        $js_script .= " 
                        if ( ! jQuery('#ideal_ipn_is_send_error_email').is(':checked') ) {   
                            jQuery('.wpbc_tr_ideal_ipn_error_email').addClass('hidden_items'); 
                        }
                      ";        
        // Hide|Show  on Click      Checkbox
        $js_script .= " jQuery('#ideal_ipn_is_send_error_email').on( 'change', function(){    
                                if ( this.checked ) { 
                                    jQuery('.wpbc_tr_ideal_ipn_error_email').removeClass('hidden_items');
                                } else {
                                    jQuery('.wpbc_tr_ideal_ipn_error_email').addClass('hidden_items');
                                }
                            } ); ";        
        
        
        
        // Eneque JS to  the footer of the page
        wpbc_enqueue_js( $js_script );  
        */
    }
    
    // </editor-fold>    
}
add_action('wpbc_menu_created',  array( new WPBC_Settings_Page_Gateway_IDEAL() , '__construct') );    // Executed after creation of Menu


/**
	 * Override VALIDATED fields BEFORE saving to DB
 * Description:
 * Check "Return URLs" and "IDEAL Email"m, etc...
 * 
 * @param array $validated_fields
 */
function wpbc_gateway_ideal_validate_fields_before_saving__all( $validated_fields ) {
                                                    
    $validated_fields['return_url']         = wpbc_make_link_relative( $validated_fields['return_url'] );
    $validated_fields['cancel_return_url']  = wpbc_make_link_relative( $validated_fields['cancel_return_url'] );
    
    if ( wpbc_is_this_demo() ) {
        
        $validated_fields['merchant_id'] = '';
        $validated_fields['merchant_key']  = '';
        $validated_fields['test']  = 'TEST';
    } 
    
    return $validated_fields;
}
add_filter( 'wpbc_gateway_ideal_validate_fields_before_saving', 'wpbc_gateway_ideal_validate_fields_before_saving__all', 10, 1 );   // Hook for validated fields.

//                                                                              </editor-fold>



//                                                                              <editor-fold   defaultstate="collapsed"   desc=" Activate | Deactivate " >    

////////////////////////////////////////////////////////////////////////////////
// Activate | Deactivate
////////////////////////////////////////////////////////////////////////////////

/** A c t i v a t e */
function wpbc_booking_activate_IDEAL() {
    
    $op_prefix = 'booking_' . WPBC_IDEAL_GATEWAY_ID . '_';

    add_bk_option( $op_prefix . 'is_active', 'Off' );
    add_bk_option( $op_prefix . 'subject', sprintf( __('Payment for booking %s on these day(s): %s'  ,'booking'), '[resource_title]','[dates]') );
    add_bk_option( $op_prefix . 'return_url', '/successful' );
    add_bk_option( $op_prefix . 'cancel_return_url', '/failed' );    
    add_bk_option( $op_prefix . 'payment_button_title' , __('Pay via' ,'booking') .' iDEAL' );    
    add_bk_option( $op_prefix . 'merchant_id', '' );
    add_bk_option( $op_prefix . 'merchant_key', '' );
    add_bk_option( $op_prefix . 'test', 'TEST' );    
    //add_bk_option( $op_prefix . 'curency', 'EUR' );
    //add_bk_option( $op_prefix . 'is_description_show', 'Off' );
    add_bk_option( $op_prefix . 'is_auto_approve_cancell_booking' , 'Off' );    
}
add_bk_action( 'wpbc_other_versions_activation',   'wpbc_booking_activate_IDEAL'   );
                

/** D e a c t i v a t e */
function wpbc_booking_deactivate_IDEAL() {
    
    $op_prefix = 'booking_' . WPBC_IDEAL_GATEWAY_ID . '_';
    
    delete_bk_option( $op_prefix . 'is_active' );
    delete_bk_option( $op_prefix . 'subject' );
    delete_bk_option( $op_prefix . 'return_url' );
    delete_bk_option( $op_prefix . 'cancel_return_url' );
    delete_bk_option( $op_prefix . 'payment_button_title' );
    delete_bk_option( $op_prefix . 'merchant_id' );
    delete_bk_option( $op_prefix . 'merchant_key' );
    delete_bk_option( $op_prefix . 'test' );
    //delete_bk_option( $op_prefix . 'curency' );
    delete_bk_option( $op_prefix . 'is_description_show' );
    delete_bk_option( $op_prefix . 'is_auto_approve_cancell_booking' );
    
}
add_bk_action( 'wpbc_other_versions_deactivation', 'wpbc_booking_deactivate_IDEAL' );

//                                                                              </editor-fold>


// Hook for getting gateway payment form to  show it after  booking process,  or for "payment request" after  clicking on link in email.
// Note,  here we generate new Object for correctly getting payment fields data of specific WP User  in WPBC MU version. 
add_filter( 'wpbc_get_gateway_payment_form', array( new WPBC_Gateway_API_IDEAL( WPBC_IDEAL_GATEWAY_ID ), 'get_payment_form' ), 10, 3 );



////////////////////////////////////////////////////////////////////////////////
// Load JavaScripts Files
////////////////////////////////////////////////////////////////////////////////
/**
	 * Load  JavaScript file
 * 
 * @param string $where_to_load
 */
function wpbc_enqueue_js_files_ideal( $where_to_load = 'both' ){     
    if (  ( $where_to_load == 'both' ) || ( $where_to_load == 'client' ) )
        wp_enqueue_script( 'wpbc-payment-ideal', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/iDEAL.js', array( 'wpbc-global-vars' ), WP_BK_VERSION_NUM );
}
add_action('wpbc_enqueue_js_files', 'wpbc_enqueue_js_files_ideal' );

////////////////////////////////////////////////////////////////////////////////
// Define AJAX Request
////////////////////////////////////////////////////////////////////////////////
function wpbc_ajax_WPBC_PAY_VIA_iDEAL() {
   
        // if ( ! wpdev_check_nonce_in_admin_panel( $_POST['action'] ) ) return false;  //FixIn: 7.2.1.10          // This line for admin panel
        
        // Get nonce from Ajax Request  
        $nonce = ( isset($_REQUEST['wpbc_nonce']) ) ? $_REQUEST['wpbc_nonce'] : ''; 
        
		if ( '' === $nonce ) return  false;	//FixIn: 7.2.1.10
		
        if ( ! wp_verify_nonce( $nonce, $_POST['action'] ) ) {                  // This nonce is not valid.                 
            wp_die( sprintf(__('%sError!%s Request do not pass security check! Please refresh the page and try one more time.' ,'booking'),'<strong>','</strong>') );                                                         // Its prevent of showing '0' et  the end of request.
        }
        
        wpbc_ideal_transaction_request();
        
        wp_die('');                                                             // Its prevent of showing '0' et  the end of request.
}
// Add Hook  for catching Ajax response from  wpbc-ajax.php  file
add_action( 'wp_ajax_nopriv_' . 'WPBC_PAY_VIA_iDEAL', 'wpbc_ajax_' . 'WPBC_PAY_VIA_iDEAL');      // Client         (not logged in)        
add_action( 'wp_ajax_'        . 'WPBC_PAY_VIA_iDEAL', 'wpbc_ajax_' . 'WPBC_PAY_VIA_iDEAL');      // Admin & Client (logged in usres)