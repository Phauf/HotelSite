// WEBBA Booking settings page scripts
// onload function
jQuery(function ($) {
    jQuery( '#tabs' ).tabs();
    var format = jQuery('#wbk_holydays_format').val();
   	//jQuery( '#wbk_holydays' ).datepick( {multiSelect: 999, monthsToShow: 3, dateFormat: format });
   	jQuery('#wbk_button_background').minicolors();
	jQuery('#wbk_button_color').minicolors();
   	jQuery( '.wbk_customer_message_btn' ).on( 'click', function() {
	    var caretPos = document.getElementById( 'wbk_email_customer_book_message' ).selectionStart;
	    var textAreaTxt = jQuery( '#wbk_email_customer_book_message' ).val();
	    var txtToAdd = '#' + jQuery(this).attr('id');
	    var newCaretPos = caretPos + txtToAdd.length;
	    jQuery( '#wbk_email_customer_book_message' ).val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
	    jQuery( '#wbk_email_customer_book_message' ).focus();
	    document.getElementById( 'wbk_email_customer_book_message' ).setSelectionRange(newCaretPos, newCaretPos);
	});
   	jQuery( '.wbk_email_editor_toggle' ).on( 'click', function() {
		jQuery(this).siblings('.wbk_email_editor_wrap').toggle('fast');
	});
	jQuery('.wbk_appointments_lock_timeslot_if_parital_booked').chosen({width: '300px'});
    jQuery('.wbk_appointments_continuous').chosen({width: '300px'});
    jQuery('.wbk_stripe_additional_fields').chosen({width: '300px'});
    jQuery('.wbk_appointments_table_columns').chosen({width: '300px'});
    jQuery('.wbk_appointments_lock_day_if_timeslot_booked').chosen({width: '300px'});
    jQuery('.wbk_appointments_lock_one_before_and_one_after').chosen({width: '300px'});

    jQuery('#wbk_remove_ediotors').on( 'click', function() {
        if( tinymce.editors.length > 0 ) {
            for( i = 0; i < tinymce.editors.length; i++ )  {
               tinyMCE.editors[ i ].destroy();
            }
         }
        return false;
    });

});
