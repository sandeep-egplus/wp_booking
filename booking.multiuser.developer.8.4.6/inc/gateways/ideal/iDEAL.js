function wpbc_pay_via_ideal( booking_resource_id ){
    
    // Get Values
    var wpbc_ideal_obj = {
                              'payment' : jQuery( '#ideal_' + booking_resource_id + ' select[name="ideal_payment"]' ).find( ':selected' ).val()
                            , 'issuerid' : jQuery( '#ideal_' + booking_resource_id + ' select[name="ideal_issuerid"]' ).find( ':selected' ).val()
                            , 'purchaseid' : jQuery( '#ideal_' + booking_resource_id + ' input[name="purchaseid"]' ).val()
                            , 'description' : jQuery( '#ideal_' + booking_resource_id + ' input[name="description"]' ).val()
                            , 'amount' : jQuery( '#ideal_' + booking_resource_id + ' input[name="amount"]' ).val()
                            , 'ideal_nonce' : jQuery( '#ideal_' + booking_resource_id + ' input[name="ideal_nonce"]' ).val()
                        };

    jQuery( '#ideal_' + booking_resource_id + ' .wpbc_ideal_payment_table').html( '<span class="glyphicon glyphicon-refresh wpbc_spin"></span> &nbsp Processing...' );      // '<div style="height:20px;width:100%;text-align:center;margin:15px auto;">Loading ... <img style="vertical-align:middle;box-shadow:none;width:14px;" src="'+wpdev_bk_plugin_url+'/assets/img/ajax-loader.gif"><//div>'
    
// console.log( wpbc_ideal_obj );
    
    jQuery.ajax({                                       
        url: wpbc_ajaxurl, 
        type:'POST',                                                            
        success: function ( data, textStatus ){                                 // Note,  here we direct show HTML to TimeLine frame
                    if( textStatus == 'success') {
                        jQuery( '#ideal_' + booking_resource_id + ' .wpbc_ideal_ajax_response' ).html( data ); 
                        return true;
                    }
                },
        error:  function ( XMLHttpRequest, textStatus, errorThrown){ 
                    console.log( 'Ajax Error! Status: ' + textStatus );
                    alert( 'Ajax Error! Status: ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText );
                },
        // beforeSend: someFunction,
        data:{
                action:             'WPBC_PAY_VIA_iDEAL',
                ideal_obj:          wpbc_ideal_obj,
                wpdev_active_locale:wpbc_active_locale,
                wpbc_nonce:         document.getElementById('wpbc_nonce_' + 'ideal_' + booking_resource_id ).value 
        }
    });     
    
}