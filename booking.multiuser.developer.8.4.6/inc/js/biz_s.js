// Highlighting range days at calendar
var payment_request_id = 0;


// Check is this day booked or no
function is_this_day_booked(bk_type, td_class, i){ // is is not obligatory parameter

    if (    ( jQuery('#calendar_booking'+bk_type+' .cal4date-' + td_class).hasClass('date_user_unavailable') )
         || ( jQuery('#calendar_booking'+bk_type+' .cal4date-' + td_class).hasClass('datepick-unselectable') )
         || (  ( jQuery('#calendar_booking'+bk_type+' .cal4date-' + td_class).hasClass('check_out_time') )  && ( i!=0 )  )
       ){ // If we find some unselect option so then make no selection at all in this range
                jQuery('#calendar_booking'+bk_type+' .cal4date-' + td_class).removeClass('datepick-current-day');
                document.body.style.cursor = 'default';return true;
    }

    //Check if in selection range are reserved days, if so then do not make selection
    if(typeof(date_approved[ bk_type ]) !== 'undefined')
        if(typeof(date_approved[ bk_type ][ td_class ]) !== 'undefined') { //alert(date_approved[ bk_type ][ td_class ][0][5]);
              for (var j=0; j < date_approved[ bk_type ][ td_class ].length ; j++) {
                    if ( ( date_approved[ bk_type ][ td_class ][j][3] == 0) &&  ( date_approved[ bk_type ][ td_class ][j][4] == 0) )  {document.body.style.cursor = 'default';return true;}
                    //Fixed on 04/02/15; ver. 5.3.1
                    if ( ( (date_approved[ bk_type ][ td_class ][j][5] * 1) == 2 ) && ( i != 0 ) && ( j==0 ) ) {document.body.style.cursor = 'default';return true;}
              }
        }

    if(typeof( date2approve[ bk_type ]) !== 'undefined')
        if(typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined') {
              for ( j=0; j < date2approve[ bk_type ][ td_class ].length ; j++) {
                    if ( ( date2approve[ bk_type ][ td_class ][j][3] == 0) &&  ( date2approve[ bk_type ][ td_class ][j][4] == 0) )  {document.body.style.cursor = 'default';return true;}
                    //Fixed on 04/02/15; ver. 5.3.1
                    if ( ( (date2approve[ bk_type ][ td_class ][j][5] * 1) == 2 ) && ( i != 0 ) && ( j==0 ) ) {document.body.style.cursor = 'default';return true;}
              }
        }

    return false;
}


// Get the closets ABS value of element in array to the current myValue
function getAbsClosestValue(myValue, myArray){

    if (myArray.length == 0 ) return myValue;       // If the array is empty -> return  the myValue

    var obj = myArray[0];
    var diff = Math.abs(myValue - obj);             // Get distance between  1st element
    var closetValue = myArray[0];                   // Save 1st element

    for (var i = 1; i < myArray.length; i++) {
        obj = myArray[i];

        if ( Math.abs(myValue - obj) < diff ) {     // we found closer value -> save it
            diff = Math.abs(myValue - obj);
            closetValue = obj;
        }
    }

    return closetValue;
}


// Highligt selectable date in Calendar
function hoverDayPro(value, date, bk_type) {

    if (date == null) {
        jQuery('.datepick-days-cell-over').removeClass('datepick-days-cell-over');                          // clear all highlight days selections
        return false;
    }

    var inst = jQuery.datepick._getInst( document.getElementById( 'calendar_booking' + bk_type ) );

    var i=0 ; var td_class; var td_overs = [];                                                              // local variables

    if(typeof( check_conditions_for_range_days_selection ) == 'function') {check_conditions_for_range_days_selection( date , bk_type);} // Highlight dates based on the conditions

    // Fixed Days Selection mode - 1 mouse click
    if (bk_days_selection_mode == 'fixed') {

        jQuery('.datepick-days-cell-over').removeClass('datepick-days-cell-over');                          // clear all selections

        if(typeof( check_conditions_for_start_day_selection ) == 'function')
            check_conditions_for_start_day_selection(bk_type, date, 'start');
        if (bk_1click_mode_days_start != -1) {                                                              // find the Closest start day to the hover day

            var startDay = getAbsClosestValue(date.getDay(), bk_1click_mode_days_start);
            date.setDate( date.getDate() -  ( date.getDay() - startDay )  );

            if(typeof( check_conditions_for_range_days_selection_for_check_in ) == 'function') {check_conditions_for_range_days_selection_for_check_in( date , bk_type);} // Highlight dates based on the conditions
        }
        if(typeof( check_conditions_for_start_day_selection ) == 'function')
            check_conditions_for_start_day_selection(bk_type, date, 'end');

        // We are mouseover the date,  that selected. Do not highlight it.
         if ( inst.dates.length > 0 )
             for ( var date_index = 0; date_index < inst.dates.length ; date_index++ ){     //FixIn: 8.4.5.16
             //for ( var date_index in inst.dates ) {
                if ( ( inst.dates[ date_index ].getFullYear() === date.getFullYear() ) &&
                     ( inst.dates[ date_index ].getMonth() === date.getMonth() ) &&
                     ( inst.dates[ date_index ].getDate() === date.getDate() ) ) {
                        return false;
                }
            }

        for( i=0; i < bk_1click_mode_days_num ; i++) {                                                      // recheck  if all days are available for the booking
            td_class =  (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();

            if (  is_this_day_booked(bk_type, td_class, i)  ) return false;                                 // check if day is booked

            td_overs[td_overs.length] = '#calendar_booking'+bk_type+ ' .cal4date-' + td_class;              // add to array for later make selection by class
            date.setDate(date.getDate() + 1);                                                               // set next date
        }

        for ( i=0; i < td_overs.length ; i++) {                                                             // add class to all elements
            jQuery( td_overs[i] ).addClass('datepick-days-cell-over');
        }
        return true;
    }


    // Dynamic Days Selection mode - 2 mouse clicks
    if (bk_days_selection_mode == 'dynamic') {

        jQuery('.datepick-days-cell-over').removeClass('datepick-days-cell-over');                          // clear all highlight days selections

        // Highligh days Before Selection
        if ( ( inst.dates.length == 0 ) || ( inst.dates.length > 1 ) ) {                                    // We are not clicked yet on days, or the selection was done and we are need to make new selection
            var selceted_first_day = new Date();
            selceted_first_day.setFullYear(date.getFullYear(),(date.getMonth()), (date.getDate() ) );

            // We are mouseover the date,  that selected. Do not highlight it.
             if ( inst.dates.length > 0 )
                for ( var date_index = 0; date_index < inst.dates.length ; date_index++ ){     //FixIn: 8.4.5.16
                //for ( var date_index in inst.dates ) {
                    if ( ( inst.dates[ date_index ].getFullYear() === selceted_first_day.getFullYear() ) &&
                         ( inst.dates[ date_index ].getMonth() === selceted_first_day.getMonth() ) &&
                         ( inst.dates[ date_index ].getDate() === selceted_first_day.getDate() ) ) {
                            return false;
                    }
                }

            if(typeof( check_conditions_for_start_day_selection ) == 'function')
                check_conditions_for_start_day_selection(bk_type, date, 'start');
            if (bk_2clicks_mode_days_start != -1) {

                var startDay = getAbsClosestValue(date.getDay(), bk_2clicks_mode_days_start);
                selceted_first_day.setDate( date.getDate() -  ( date.getDay() - startDay )  );

                if(typeof( check_conditions_for_range_days_selection_for_check_in ) == 'function') {check_conditions_for_range_days_selection_for_check_in( selceted_first_day , bk_type);} // Highlight dates based on the conditions
            }
            if(typeof( check_conditions_for_start_day_selection ) == 'function')
                check_conditions_for_start_day_selection(bk_type, date, 'end');

            i=0;
            while( ( i < bk_2clicks_mode_days_min) ) {
               i++;
               td_class =  (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();
               if (   is_this_day_booked(bk_type, td_class, (i-1))   ) return false;                         // check if day is booked
               td_overs[td_overs.length] = '#calendar_booking'+bk_type+ ' .cal4date-' + td_class;            // add to array for later make selection by class
               selceted_first_day.setFullYear(selceted_first_day.getFullYear(),(selceted_first_day.getMonth()), (selceted_first_day.getDate() + 1) );
            }
        }

        // First click on days
        if (inst.dates.length == 1) {                                                                       // select start date in Dynamic range selection, after first days is selected
            var selceted_first_day = new Date();
            selceted_first_day.setFullYear(inst.dates[0].getFullYear(),(inst.dates[0].getMonth()), (inst.dates[0].getDate() ) ); //Get first Date
            var is_check = true;
            i=0;

            while(  (is_check ) || ( i < bk_2clicks_mode_days_min ) ) {                                         // Untill rich MIN days number.
               i++;
               td_class =  (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();

                if (  is_this_day_booked(bk_type, td_class, (i-1))  ) return false;                             // check if day is booked

                td_overs[td_overs.length] = '#calendar_booking'+bk_type+ ' .cal4date-' + td_class;              // add to array for later make selection by class

                var is_discreet_ok = true;
                if (bk_2clicks_mode_days_specific.length>0) {              // check if we set some discreet dates
                    is_discreet_ok = false;
                    for (var di = 0; di < bk_2clicks_mode_days_specific.length; di++) {   // check if current number of days inside of discreet one
                         if ( (  i == bk_2clicks_mode_days_specific[di] )  ) {
                             is_discreet_ok = true;
                             di = (bk_2clicks_mode_days_specific.length + 1);
                         }
                    }
                }

                if (   ( date.getMonth() == selceted_first_day.getMonth() )  &&
                       ( date.getDate() == selceted_first_day.getDate() )  &&
                       ( date.getFullYear() == selceted_first_day.getFullYear() )  && ( is_discreet_ok )  )
                {is_check =  false;}

                if ((selceted_first_day > date ) && ( i >= bk_2clicks_mode_days_min ) && ( i < bk_2clicks_mode_days_max )  && (is_discreet_ok)  )   {
                    is_check =  false;
                }
                if ( i >= bk_2clicks_mode_days_max ) is_check =  false;
                selceted_first_day.setFullYear(selceted_first_day.getFullYear(),(selceted_first_day.getMonth()), (selceted_first_day.getDate() + 1) );
            }
        }

        // Highlight Days
        for ( i=0; i < td_overs.length ; i++) {                                                             // add class to all elements
            jQuery( td_overs[i] ).addClass('datepick-days-cell-over');
        }
        return true;
    }
}


// select a day
function selectDayPro(all_dates,   bk_type){

    if(typeof( prepare_tooltip ) == 'function') {setTimeout("prepare_tooltip("+bk_type+");",1000);}

    if(typeof( check_conditions_for_range_days_selection ) == 'function') {check_conditions_for_range_days_selection( all_dates , bk_type);}

    // Help with range selection
    bkRangeDaysSelection(all_dates,   bk_type);

    // Conditional showing form elements
    // We are need to  get the dates from  the textarea and not from  all_dates variable
    // because in the range days selection  the dates can be changed
   if(typeof( check_condition_sections_in_bkform ) == 'function') {check_condition_sections_in_bkform( jQuery('#date_booking' + bk_type).val() , bk_type);}

    // HERE WE WILL DISABLE ALL OPTIONS IN RANGE TIME INTERVALS FOR SINGLE DAYS SELECTIONS FOR THAT DAYS WHERE HOURS ALREADY BOOKED
    bkDisableBookedTimeSlots( jQuery('#date_booking' + bk_type).val() , bk_type);

    //Calculate the cost and show inside of form
    if(typeof( showCostHintInsideBkForm ) == 'function') {  showCostHintInsideBkForm( bk_type); }

    if (false)
    if (bk_days_selection_mode == 'dynamic') {                                  // Check if range days selection with 2 mouse clicks active
        // Check if we made first click and show message
        if ( jQuery('#booking_form_div'+bk_type+' input[type="button"]').prop('disabled' ) ) {
            var message_verif_select_checkout = "Please, select 'Check Out' date at Calendar.";
            jQuery( '#date_booking' + bk_type  )
                    .after('<span class="wpbc_message_select_checkout wpdev-help-message wpdev-element-message alert alert-warning">'+ message_verif_select_checkout +'</span>'); // Show message
            jQuery(".wpbc_message_select_checkout")
                    .animate( {opacity: 1}, 10000 )
                    .fadeOut( 2000 );
        } else {    // Check  if we clicked second time and remove message.
            jQuery(".wpbc_message_select_checkout").remove();
        }
    }
}


// Make range select
function bkRangeDaysSelection(all_dates,   bk_type){

         var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
     var td_class;

     if ( (bk_days_selection_mode == 'fixed') || (bk_days_selection_mode == 'dynamic') ) {  // Start range selections checking

        var internal_bk_1click_mode_days_num = bk_1click_mode_days_num;

        if ( all_dates.indexOf(' - ') != -1 ){                  // Dynamic selections
            var start_end_date = all_dates.split(" - ");

            var is_dynamic_startdayequal_to_last = true;
            if (inst.dates.length>1){
                if (bk_days_selection_mode == 'dynamic') { // Dinamic
                    is_dynamic_startdayequal_to_last = false;
                }
            }


            if ( ( start_end_date[0] == start_end_date[1] ) && (is_dynamic_startdayequal_to_last ===true)   ) {    // First click at day
              if(typeof( check_conditions_for_start_day_selection ) == 'function') {
                    var start_dynamic_date = start_end_date[0].split(".");
                    var real_start_dynamic_date=new Date();
                    real_start_dynamic_date.setFullYear( start_dynamic_date[2],  start_dynamic_date[1]-1,  start_dynamic_date[0] );    // get date of click
                    check_conditions_for_start_day_selection(bk_type, real_start_dynamic_date, 'start');
              }
              if (bk_2clicks_mode_days_start != -1) {             // Activated some specific week day start range selectiosn
                    var start_dynamic_date = start_end_date[0].split(".");
                    var real_start_dynamic_date=new Date();
                    real_start_dynamic_date.setFullYear( start_dynamic_date[2],  start_dynamic_date[1]-1,  start_dynamic_date[0] );    // get date of click

                    if (real_start_dynamic_date.getDay() !=  bk_2clicks_mode_days_start) {

                        var startDay = getAbsClosestValue(real_start_dynamic_date.getDay(), bk_2clicks_mode_days_start);
                        real_start_dynamic_date.setDate( real_start_dynamic_date.getDate() -  ( real_start_dynamic_date.getDay() - startDay )  );


                        all_dates = jQuery.datepick._formatDate(inst, real_start_dynamic_date );
                        all_dates += ' - ' + all_dates ;
                        jQuery('#date_booking' + bk_type).val(all_dates); // Fill the input box

                        if(typeof( check_conditions_for_range_days_selection ) == 'function') {check_conditions_for_range_days_selection( all_dates , bk_type);} // Highlight dates based on the conditions

                        // check this day for already booked
                        var selceted_first_day = new Date;
                        selceted_first_day.setFullYear(real_start_dynamic_date.getFullYear(),(real_start_dynamic_date.getMonth()), (real_start_dynamic_date.getDate() ) );
                        i=0;
                        while(    ( i < bk_2clicks_mode_days_min ) ) {

                           td_class =  (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();
                           if (   is_this_day_booked(bk_type, td_class, (i))   ) {
                                // Unselect all dates and set  properties of Datepick
                                jQuery('#date_booking' + bk_type).val('');      //FixIn: 5.4.3
                                inst.stayOpen = false;                          //FixIn: 5.4.3
                                inst.dates=[];
                                jQuery.datepick._updateDatepick(inst);
                                return false;   // check if day is booked
                           }
                           selceted_first_day.setFullYear(selceted_first_day.getFullYear(),(selceted_first_day.getMonth()), (selceted_first_day.getDate() + 1) );
                           i++;
                        }

                        // Selection of the day
                        inst.cursorDate.setFullYear(real_start_dynamic_date.getFullYear(),(real_start_dynamic_date.getMonth()), (real_start_dynamic_date.getDate() ) );
                        inst.dates=[inst.cursorDate];
                        jQuery.datepick._updateDatepick(inst);
                     }
              } else { // Set correct date, if only single date is selected, and possible press send button then.
                    var start_dynamic_date = start_end_date[0].split(".");
                    var real_start_dynamic_date=new Date();
                    real_start_dynamic_date.setFullYear( start_dynamic_date[2],  start_dynamic_date[1]-1,  start_dynamic_date[0] );    // get date of click
                    inst.cursorDate.setFullYear(real_start_dynamic_date.getFullYear(),(real_start_dynamic_date.getMonth()), (real_start_dynamic_date.getDate() ) );
                    inst.dates=[inst.cursorDate];
                    jQuery.datepick._updateDatepick(inst);
                    jQuery('#date_booking' + bk_type).val(start_end_date[0]);
              }
              if(typeof( check_conditions_for_start_day_selection ) == 'function')
                check_conditions_for_start_day_selection(bk_type, '', 'end');
              submit_bk_color = jQuery('#booking_form_div'+bk_type+' input[type="button"]').css('color');

              if (bk_2clicks_mode_days_min>1) {
                jQuery('#booking_form_div'+bk_type+' input[type="button"]').attr('disabled', 'disabled'); // Disbale the submit button
                jQuery('#booking_form_div'+bk_type+' input[type="button"]').css('color', '#aaa');
              }
              setTimeout(function ( ) {jQuery('#calendar_booking' + bk_type + ' .datepick-unselectable.timespartly.check_out_time,#calendar_booking' + bk_type + ' .datepick-unselectable.timespartly.check_in_time').removeClass('datepick-unselectable');} ,500);
              return false;
            } else {  // Last day click

                    jQuery('#booking_form_div'+bk_type+' input[type="button"]').removeAttr('disabled');  // Activate the submit button
                    jQuery('#booking_form_div'+bk_type+' input[type="button"]').css('color',  submit_bk_color );

                    var start_dynamic_date = start_end_date[0].split(".");
                    var real_start_dynamic_date=new Date();
                    real_start_dynamic_date.setFullYear( start_dynamic_date[2],  start_dynamic_date[1]-1,  start_dynamic_date[0] );    // get date

                    var end_dynamic_date = start_end_date[1].split(".");
                    var real_end_dynamic_date=new Date();
                    real_end_dynamic_date.setFullYear( end_dynamic_date[2],  end_dynamic_date[1]-1,  end_dynamic_date[0] );    // get date

                    internal_bk_1click_mode_days_num = 1; // need to count how many days right now

                    var temp_date_for_count = new Date();

                    for( var j1=0; j1 < 365 ; j1++) {
                        temp_date_for_count = new Date();
                        temp_date_for_count.setFullYear(real_start_dynamic_date.getFullYear(),(real_start_dynamic_date.getMonth()), (real_start_dynamic_date.getDate() + j1) );

                        if ( (temp_date_for_count.getFullYear() == real_end_dynamic_date.getFullYear()) && (temp_date_for_count.getMonth() == real_end_dynamic_date.getMonth()) && (temp_date_for_count.getDate() == real_end_dynamic_date.getDate()) )  {
                            internal_bk_1click_mode_days_num = j1;
                            j1=1000;
                        }
                    }
                    internal_bk_1click_mode_days_num++;
                    all_dates =  start_end_date[0];
                    if (internal_bk_1click_mode_days_num < bk_2clicks_mode_days_min ) internal_bk_1click_mode_days_num = bk_2clicks_mode_days_min;

                    var is_backward_direction = false;
                    if (bk_2clicks_mode_days_specific.length>0) {              // check if we set some discreet dates

                        var is_discreet_ok = false;
                        while (  is_discreet_ok === false ) {

                            for (var di = 0; di < bk_2clicks_mode_days_specific.length; di++) {   // check if current number of days inside of discreet one
                                 if (
                                    ( (  internal_bk_1click_mode_days_num == bk_2clicks_mode_days_specific[di] )  ) &&
                                      (internal_bk_1click_mode_days_num <= bk_2clicks_mode_days_max) ) {
                                     is_discreet_ok = true;
                                     di = (bk_2clicks_mode_days_specific.length + 1);
                                 }
                            }
                            if (is_backward_direction === false)
                                if (  is_discreet_ok === false )
                                    internal_bk_1click_mode_days_num++;

                            // BackWard directions, if we set more than maximum days
                            if (internal_bk_1click_mode_days_num >= bk_2clicks_mode_days_max) is_backward_direction = true;

                            if (is_backward_direction === true)
                                if (  is_discreet_ok === false )
                                    internal_bk_1click_mode_days_num--;

                            if (internal_bk_1click_mode_days_num < bk_2clicks_mode_days_min )  is_discreet_ok = true;
                        }

                    } else {
                        if (internal_bk_1click_mode_days_num > bk_2clicks_mode_days_max) internal_bk_1click_mode_days_num = bk_2clicks_mode_days_max;
                    }


            }
        } // And Range selections checking

        var temp_bk_days_selection_mode = bk_days_selection_mode ;
        bk_days_selection_mode = 'multiple';

        inst.dates = [];                                        // Emty dates in datepicker
        var all_dates_array;
        var date_array;
        var date;
        var date_to_ins;

        // Get array of dates
        if ( all_dates.indexOf(',') == -1 ) {all_dates_array = [all_dates];}
        else                                {all_dates_array = all_dates.split(",");}

        var original_array = [];
        var isMakeSelection = false;

        if ( temp_bk_days_selection_mode != 'dynamic' ) {
            // Gathering original (already selected dates) date array
            for( var j=0; j < all_dates_array.length ; j++) {                           //loop array of dates
                all_dates_array[j] = all_dates_array[j].replace(/(^\s+)|(\s+$)/g, "");  // trim white spaces in date string

                date_array = all_dates_array[j].split(".");                             // get single date array

                date=new Date();
                date.setFullYear( date_array[2],  date_array[1]-1,  date_array[0] );    // get date

                if ( (date.getFullYear() == inst.cursorDate.getFullYear()) && (date.getMonth() == inst.cursorDate.getMonth()) && (date.getDate() == inst.cursorDate.getDate()) )  {
                    isMakeSelection = true;
                    if(typeof( check_conditions_for_start_day_selection ) == 'function')
                        check_conditions_for_start_day_selection(bk_type, inst.cursorDate, 'start');
                    if (bk_1click_mode_days_start != -1) {
                        var startDay = getAbsClosestValue(inst.cursorDate.getDay(), bk_1click_mode_days_start);
                        inst.cursorDate.setDate( inst.cursorDate.getDate() -  ( inst.cursorDate.getDay() - startDay )  );

                        bk_days_selection_mode = temp_bk_days_selection_mode;
                        if(typeof( check_conditions_for_range_days_selection_for_check_in ) == 'function') {check_conditions_for_range_days_selection_for_check_in( inst.cursorDate , bk_type);} // Highlight dates based on the conditions
                        temp_bk_days_selection_mode = bk_days_selection_mode ;
                        bk_days_selection_mode = 'multiple';
                        internal_bk_1click_mode_days_num = bk_1click_mode_days_num;

                    }
                    if(typeof( check_conditions_for_start_day_selection ) == 'function')
                        check_conditions_for_start_day_selection(bk_type, inst.cursorDate, 'end');

                }
            }
        } else {
            isMakeSelection = true;                                                         // dynamic range selection
        }

        var isEmptySelection = false;
        if (isMakeSelection) {
            var date_start_range = inst.cursorDate;

            if ( temp_bk_days_selection_mode != 'dynamic' ) {
                original_array.push( jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, inst.cursorDate , null))  ); //add date
            } else {
                original_array.push( jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, real_start_dynamic_date , null))  ); //set 1st date from dynamic range
                date_start_range = real_start_dynamic_date;
            }
            var dates_array = [];
            var range_array = [];
            var td;
            // Add dates to the range array
            for( var i=1; i < internal_bk_1click_mode_days_num ; i++) {

                dates_array[i] = new Date();
                // dates_array[i].setDate( (date_start_range.getDate() + i) );

                dates_array[i].setFullYear(date_start_range.getFullYear(),(date_start_range.getMonth()), (date_start_range.getDate() + i) );

                td_class =  (dates_array[i].getMonth()+1) + '-'  +  dates_array[i].getDate() + '-' + dates_array[i].getFullYear();
                td =  '#calendar_booking'+bk_type+' .cal4date-' + td_class;
                 if (jQuery(td).hasClass('datepick-unselectable') ){ // If we find some unselect option so then make no selection at all in this range
                     jQuery(td).removeClass('datepick-current-day');
                     isEmptySelection = true;
                }

                //Check if in selection range are reserved days, if so then do not make selection
                if (   is_this_day_booked(bk_type, td_class, i)   ) isEmptySelection = true;
                /////////////////////////////////////////////////////////////////////////////////////

                date_to_ins =  jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, dates_array[i], null));

                range_array.push( date_to_ins );
            }

            // check if some dates are the same in the arrays so the remove them from both
            for( i=0; i < range_array.length ; i++) {
                for( j=0; j < original_array.length ; j++) {       //loop array of dates

                if ( (original_array[j] != -1) && (range_array[i] != -1) )
                    if ( (range_array[i].getFullYear() == original_array[j].getFullYear()) && (range_array[i].getMonth() == original_array[j].getMonth()) && (range_array[i].getDate() == original_array[j].getDate()) )  {
                        range_array[i] = -1;
                        original_array[j] = -1;
                    }
                }
            }

            // Add to the dates array
            for( j=0; j < original_array.length ; j++) {       //loop array of dates
                    if (original_array[j] != -1) inst.dates.push(original_array[j]);
            }
            for( i=0; i < range_array.length ; i++) {
                    if (range_array[i] != -1) inst.dates.push(range_array[i]);
            }
        }
        if (! isEmptySelection) isEmptySelection = checkIfSomeDaysUnavailable(inst.dates, bk_type);
        if (isEmptySelection) inst.dates=[];

        //jQuery.datepick._setDate(inst, dates_array);
        if ( temp_bk_days_selection_mode != 'dynamic' ) {
            jQuery.datepick._updateInput('#calendar_booking'+bk_type);
        } else {
           if (isEmptySelection) jQuery.datepick._updateInput('#calendar_booking'+bk_type);
           else {       // Dynamic range selections, transform days from jQuery.datepick
               dateStr = (inst.dates.length == 0 ? '' : jQuery.datepick._formatDate(inst, inst.dates[0])); // Get first date
                for ( i = 1; i < inst.dates.length; i++)
                     dateStr += jQuery.datepick._get(inst, 'multiSeparator') +  jQuery.datepick._formatDate(inst, inst.dates[i]);  // Gathering all dates
                jQuery('#date_booking' + bk_type).val(dateStr); // Fill the input box
           }
        }
        if ( ( is_dynamic_startdayequal_to_last === false ) && ( start_end_date[0] == start_end_date[1] ) )  {
            if ( inst.dates.length == 1 ) {
                inst.dates.push(inst.dates[0]);
                //jQuery.datepick._updateDatepick(inst);
            }
        }
        jQuery.datepick._notifyChange(inst);
        jQuery.datepick._adjustInstDate(inst);
        jQuery.datepick._showDate(inst);

        bk_days_selection_mode = temp_bk_days_selection_mode ;
     }
 }


function checkIfSomeDaysUnavailable(selected_dates, bk_type) {

    var i, j, td_class;

    for ( j=0; j< selected_dates.length; j++){
         // Check among availbaility filters
         if (typeof( is_this_day_available ) == 'function') {
            var is_day_available = is_this_day_available( selected_dates[j], bk_type);
            if ( is_day_available instanceof Array ) is_day_available = is_day_available[0];        //FixIn: 6.0.1.8
            if (! is_day_available) {return true;}
        }

       td_class =  (selected_dates[j].getMonth()+1) + '-' + selected_dates[j].getDate() + '-' + selected_dates[j].getFullYear();

       // Get dates and time from pending dates
       if(typeof( date2approve[ bk_type ]) !== 'undefined')
       if(typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined')
         if( ( date2approve[ bk_type ][ td_class ][0][3] == 0) &&  ( date2approve[ bk_type ][ td_class ][0][4] == 0) ) //check for time here
               {return true;} // day fully booked

       // Get dates and time from aproved dates
       if(typeof(date_approved[ bk_type ]) !== 'undefined')
       if(typeof(date_approved[ bk_type ][ td_class ]) !== 'undefined')
         if( ( date_approved[ bk_type ][ td_class ][0][3] == 0) &&  ( date_approved[ bk_type ][ td_class ][0][4] == 0) )
               {return true;} // day fully booked

    }

    return  false;
}


function save_this_booking_cost(booking_id, cost, wpdev_active_locale){

    if ( cost != '' ) {

        wpbc_admin_show_message_processing( 'saving' );

        var ajax_type_action='SAVE_BK_COST';

        jQuery.ajax({                                           // Start Ajax Sending
            url: wpbc_ajaxurl,
            type:'POST',
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond' ).html( data ) ;},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' https://wpbookingcalendar.com/faq/#ajax-sending-error');}},
            // beforeSend: someFunction,
            data:{
                // ajax_action : ajax_type_action,
                action : ajax_type_action,
                booking_id : booking_id,
                cost : cost,
                wpdev_active_locale:wpdev_active_locale,
                wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value
            }
        });
        return false;
    }
    return true;
}


function sendPaymentRequestByEmail(payment_request_id , request_reason, user_id, wpdev_active_locale) {     //FixIn:5.4.5.6 - user_id: user_id,

    wpdev_active_locale = wpbc_get_selected_locale( payment_request_id,  wpdev_active_locale ); //FixIn: 5.4.5

    wpbc_admin_show_message_processing( '' );

    var ajax_type_action='SEND_PAYMENT_REQUEST';

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpbc_ajaxurl,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond' ).html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' https://wpbookingcalendar.com/faq/#ajax-sending-error');}},
        // beforeSend: someFunction,
        data:{
            // ajax_action : ajax_type_action,
            action : ajax_type_action,
            booking_id : payment_request_id,
            reason : request_reason,
            user_id: user_id,
            wpdev_active_locale:wpdev_active_locale,
            wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value
        }
    });
    return false;
}


// Chnage the booking status of booking
function change_booking_payment_status(booking_id, payment_status, payment_status_show) {

    wpbc_admin_show_message_processing( 'updating' );

    var ajax_type_action = 'CHANGE_PAYMENT_STATUS';

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpbc_ajaxurl,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond' ).html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' https://wpbookingcalendar.com/faq/#ajax-sending-error');}},
        // beforeSend: someFunction,
        data:{
            // ajax_action : ajax_type_action,
            action : ajax_type_action,
            booking_id : booking_id,
            payment_status : payment_status,
            payment_status_show: payment_status_show,
            wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value
        }
    });
}

//FixIn: 7.0.1.34 - added #print_loyout_content before #wpbc_print_row -- because we are having 2 such  elements. May be in future to  use CLASS instead of ID
function wpbc_print_specific_booking( booking_id ){

    jQuery("#print_loyout_content").html(jQuery("#booking_print_loyout").html());   // Set Print Loyout Data
    jQuery("#print_loyout_content .wpbc_print_rows").hide();                                              // Hide all rows
    jQuery("#print_loyout_content #wpbc_print_row" + booking_id ).show();                                 // Sow only 1 row
//alert(booking_id);
    //jQuery(".modal-footer").hide();                                                 // Hide Footer
    // Show Modal
    //FixIn: 7.0.1.10
    if ( jQuery.isFunction( jQuery('#wpbc_print_modal').modal ) ) {
        jQuery('#wpbc_print_modal').modal('show');
    } else {
        alert('Warning! Booking Calendar. Its seems that  you have deactivated loading of Bootstrap JS files at Booking Settings General page in Advanced section.')
    }
}


function wpbc_print_specific_booking_for_timeline( booking_id ){
    jQuery("#print_loyout_content").html(jQuery("#wpbc-booking-id-"+booking_id).parent().parent().html());   // Set Print Loyout Data
    jQuery("#print_loyout_content").children().hide();
    jQuery("#print_loyout_content").children('.popover-content').show();
    // Show Modal
    //FixIn: 7.0.1.10
    if ( jQuery.isFunction( jQuery('#wpbc_print_modal').modal ) ) {
        jQuery('#wpbc_print_modal').modal('show');
    } else {
        alert('Warning! Booking Calendar. Its seems that  you have deactivated loading of Bootstrap JS files at Booking Settings General page in Advanced section.')
    }

}
