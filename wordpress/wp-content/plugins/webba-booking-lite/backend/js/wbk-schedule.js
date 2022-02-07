// WEBBA Booking schedule page javascripts
// onload functions
jQuery(function ($) {
	// service buttons click
	jQuery('[id^=load_schedule_]').click(function() {
		jQuery('[id^=load_schedule_]').removeClass('button-primary');
		jQuery('[id^=load_schedule_]').addClass('button');
		jQuery('#auto_unlock').removeClass('button-primary');
		jQuery('#auto_lock').removeClass('button-primary');
		jQuery(this).removeClass('button');
		jQuery(this).addClass('button-primary');
		var service_id = jQuery(this).attr('id');
		service_id = service_id.substring(14, service_id.length);
        jQuery('#days_container').html('<div class="loading"></div>');
        jQuery('#control_container').html('');
    	wbk_load_schedule( 0, service_id );
	});
	jQuery('#auto_lock').click(function() {
		jQuery('[id^=load_schedule_]').removeClass('button-primary');
		jQuery('[id^=load_schedule_]').addClass('button');
		jQuery('.wbk-shedule-tools-btn').removeClass('button-primary');
		jQuery(this).addClass('button-primary');
		wbk_render_tool('auto_lock');
	});
	jQuery('#auto_unlock').click(function() {
		jQuery('[id^=load_schedule_]').removeClass('button-primary');
		jQuery('[id^=load_schedule_]').addClass('button');
		jQuery('.wbk-shedule-tools-btn').removeClass('button-primary');
		jQuery(this).addClass('button-primary');
		wbk_render_tool('auto_unlock');
	});
	jQuery('#auto_lock_timeslot').click(function() {
		jQuery('[id^=load_schedule_]').removeClass('button-primary');
		jQuery('[id^=load_schedule_]').addClass('button');
		jQuery('.wbk-shedule-tools-btn').removeClass('button-primary');
		jQuery(this).addClass('button-primary');
		wbk_render_tool('auto_lock_timeslot');
	});
	jQuery('#auto_unlock_timeslot').click(function() {
		jQuery('[id^=load_schedule_]').removeClass('button-primary');
		jQuery('[id^=load_schedule_]').addClass('button');
		jQuery('.wbk-shedule-tools-btn').removeClass('button-primary');
		jQuery(this).addClass('button-primary');
		wbk_render_tool('auto_unlock_timeslot');
	});
	jQuery('#create_multiple_bookings').click(function() {
		jQuery('[id^=load_schedule_]').removeClass('button-primary');
		jQuery('[id^=load_schedule_]').addClass('button');
		jQuery('.wbk-shedule-tools-btn').removeClass('button-primary');
		jQuery(this).addClass('button-primary');
		wbk_render_tool('create_multiple_bookings');
	});

});
//render tools
function wbk_render_tool(tool){

	var data = {
		'action': 'wbk_render_tool',
		'tool': tool
	};
	jQuery('#control_container').html('');
	jQuery('#days_container').html('<div class="loading"></div>');
 	jQuery.post(ajaxurl, data, function( response ) {
 		jQuery('#days_container').html('<div class="wbk-schedule-row">'+ response + '</div>');
 		var wbk_date_format = jQuery('#wbk_backend_date_format').val();
 		jQuery( '#lock_date_range' ).datepick( {rangeSelect: true, monthsToShow: 3, dateFormat: wbk_date_format } );
 		jQuery( '#lock_exclude_date' ).datepick( {multiSelect: 999, monthsToShow: 3, dateFormat: wbk_date_format } );
 		jQuery('#auto_lock_launch').click(function() {
			wbk_auto_lock();
		});
		jQuery('#auto_unlock_launch').click(function() {
			wbk_auto_unlock();
		});
 		jQuery('#auto_lock_time_slot_launch').click(function() {
			wbk_auto_lock_time_slot();
		});
 		jQuery('#auto_unlock_time_slot_launch').click(function() {
			wbk_auto_unlock_time_slot();
		});
		jQuery( '#lock_date_range, #lock_exclude_date, #lock_service_list, #lock_category_list' ).focus(function() {
			jQuery( this ).removeClass('wbk-input-error');
		});
		jQuery('#lock_service_list').change( function(){
			if( jQuery(this).val() != -1 ){
					jQuery('#lock_category_list').val(-1);
			}
		});
		jQuery('#lock_category_list').change( function(){
			if( jQuery(this).val() != -1 ){
					jQuery('#lock_service_list').val(-1);
			}
		});
		jQuery('#days_of_week').chosen({width: '500px'});
		jQuery('#create_multiple_bookings_services').change( function(){
			var service_id = jQuery(this).val();
			if( service_id == -1 ){
				jQuery('#multiple_booking_form_container').html('');
				return;
			}
			jQuery('#multiple_booking_form_container').html('<label for="lock_date_range" style="width:100%;display:block;" class="wbk_remains">Select date:</label><input class="wbk_input_500 wbk_remains" type="text" id="create_multiple_bookings_date">');
			var wbk_date_format = jQuery('#wbk_backend_date_format').val();
			jQuery( '#create_multiple_bookings_date' ).datepick( { onSelect: function(dates) {

				render_multiple_dates_form( dates ); },    dateFormat: wbk_date_format
			});

		});
	});
}
// auto lock
function wbk_auto_lock(){

	var service_id = jQuery('#lock_service_list').val();
	var category_id = jQuery('#lock_category_list').val();

	var error_status = 0;
	if( service_id == -1 && category_id == -1 ){
		jQuery('#lock_service_list').addClass('wbk-input-error');
		jQuery('#lock_category_list').addClass('wbk-input-error');
		error_status = 1;
	}
	var date_range = jQuery.trim( jQuery('#lock_date_range').val() );
	if( date_range == '' ){
		jQuery('#lock_date_range').addClass('wbk-input-error');
		error_status = 1;
	}
	var date_exclude = jQuery.trim( jQuery('#lock_exclude_date').val() );

	if ( error_status == 1 ){
		return
	}
	var days_of_week = jQuery('#days_of_week').val();
	var data = {
		'action': 'wbk_auto_lock',
		'date_range': date_range,
		'date_exclude': date_exclude,
		'service_id': service_id,
		'category_id': category_id,
		'days_of_week': days_of_week
	};
	jQuery('#days_container').html('<div class="loading"></div>');
 	jQuery.post(ajaxurl, data, function( response ) {
 		jQuery('#days_container').html('<div class="wbk-schedule-row">'+ response + '</div>');
	});
}
// auto lock time slot
function wbk_auto_lock_time_slot(){
	var service_id = jQuery('#lock_service_list').val();
	var category_id = jQuery('#lock_category_list').val();
	var error_status = 0;
	if( service_id == -1 && category_id == -1 ){
		jQuery('#lock_service_list').addClass('wbk-input-error');
		jQuery('#lock_category_list').addClass('wbk-input-error');
		error_status = 1;
	}
	var date_range = jQuery.trim( jQuery('#lock_date_range').val() );
	if( date_range == '' ){
		jQuery('#lock_date_range').addClass('wbk-input-error');
		error_status = 1;
	}
	var time_start = parseInt( jQuery.trim( jQuery('#lock_time_start').val() ) );
	var time_end =  parseInt( jQuery.trim( jQuery('#lock_time_end').val() ) );
	if ( time_start > time_end ){
		jQuery('#lock_time_start').addClass('wbk-input-error');
		jQuery('#lock_time_end').addClass('wbk-input-error');
		error_status = 1;
	}
	if ( error_status == 1 ){
		return
	}
	var days_of_week = jQuery('#days_of_week').val();
	var data = {
		'action': 'wbk_auto_lock_time_slot',
		'date_range': date_range,
		'service_id': service_id,
		'category_id': category_id,
		'time_start': time_start,
		'time_end': time_end,
		'days_of_week': days_of_week
	};
	jQuery('#days_container').html('<div class="loading"></div>');
 	jQuery.post(ajaxurl, data, function( response ) {
 		if ( response == '-1' || response == '-2' || response == '-3' ){
 			jQuery('#days_container').html('Internal error: ' + response );
 		} else {
			jQuery('#days_container').html('<div class="wbk-schedule-row">'+ response + '</div>');
 		}
	});
}
// auto unlock time slot
function wbk_auto_unlock_time_slot(){
	var service_id = jQuery('#lock_service_list').val();
	var category_id = jQuery('#lock_category_list').val();
	var error_status = 0;
	if( service_id == -1 && category_id == -1 ){
		jQuery('#lock_service_list').addClass('wbk-input-error');
		jQuery('#lock_category_list').addClass('wbk-input-error');
		error_status = 1;
	}
	var date_range = jQuery.trim( jQuery('#lock_date_range').val() );
	if( date_range == '' ){
		jQuery('#lock_date_range').addClass('wbk-input-error');
		error_status = 1;
	}
	var time_start = parseInt( jQuery.trim( jQuery('#lock_time_start').val() ) );
	var time_end =  parseInt( jQuery.trim( jQuery('#lock_time_end').val() ) );
	if ( time_start > time_end ){
		jQuery('#lock_time_start').addClass('wbk-input-error');
		jQuery('#lock_time_end').addClass('wbk-input-error');
		error_status = 1;
	}
	if ( error_status == 1 ){
		return
	}var days_of_week = jQuery('#days_of_week').val();
	var data = {
		'action': 'wbk_auto_unlock_time_slot',
		'date_range': date_range,
		'service_id': service_id,
		'category_id': category_id,
		'time_start': time_start,
		'time_end': time_end,
		'days_of_week': days_of_week
	};
	jQuery('#days_container').html('<div class="loading"></div>');
 	jQuery.post(ajaxurl, data, function( response ) {
 		if ( response == '-1' || response == '-2' || response == '-3' ){
 			jQuery('#days_container').html('Internal error: ' + response );
 		} else {
			jQuery('#days_container').html('<div class="wbk-schedule-row">'+ response + '</div>');
 		}
	});
}
// auto unlock
function wbk_auto_unlock(){

	var service_id = jQuery('#lock_service_list').val();
	var category_id = jQuery('#lock_category_list').val();
	var error_status = 0;
	if( service_id == -1 && category_id == -1 ){
		jQuery('#lock_service_list').addClass('wbk-input-error');
		error_status = 1;
	}
	var date_range = jQuery.trim( jQuery('#lock_date_range').val() );
	if( date_range == '' ){
		jQuery('#lock_date_range').addClass('wbk-input-error');
		error_status = 1;
	}
	var date_exclude = jQuery.trim( jQuery('#lock_exclude_date').val() );

	if ( error_status == 1 ){
		return
	}
	var days_of_week = jQuery('#days_of_week').val();
	var data = {
		'action': 'wbk_auto_unlock',
		'date_range': date_range,
		'category_id': category_id,
		'date_exclude': date_exclude,
		'service_id': service_id,
		'days_of_week': days_of_week
	};
	jQuery('#days_container').html('<div class="loading"></div>');
 	jQuery.post(ajaxurl, data, function( response ) {
 		jQuery('#days_container').html('<div class="wbk-schedule-row">'+ response + '</div>');
	});
}
// load schedule
function wbk_load_schedule( start, service_id ) {
	var data = {
		'action': 'wbk_schedule_load',
		'service_id': service_id,
		'start': start
	};

 	jQuery.post(ajaxurl, data, function( response ) {

 		if ( response == -1 ){
 			jQuery('#days_container').html('error');
 			return;
 		}

	 	if ( start == 0 ){
	 		jQuery('#days_container').html('<div class="wbk-schedule-row">'+ response + '</div>');

	 	} else {
	 		jQuery('#days_container > .wbk-schedule-row').append(response);
	 	}
	 	setEvents();
	 	var next_week = parseInt(start) + 1;
	 	jQuery('#control_container').html('<div class="wbk-schedule-row"><a class="button" id="show_next_week_' + service_id + '_' + next_week + '">' + wbkl10n.shownextweek + '</a></div>');

	    // load next week
	    jQuery('[id^=show_next_week_]').click( function() {
	    	jQuery('#control_container > .wbk-schedule-row').html('<div class="loading"></div>');
	    	var id_start = jQuery(this).attr('id');
			id_start = id_start.substring(15, id_start.length);
	     	var result = id_start.split('_');
			wbk_load_schedule(result[1], result[0]);
	    });

	});

}

// view appointment
function viewAppointment( appointment_id, service_id, appointment_status, parent ) {

  	jQuery( 'input' ).removeClass('wbk-input-error');
	jQuery( '#appointment_dialog_content' ).css( 'display', 'none' );

 	jQuery( '#dialog-appointment' ).dialog({
	    resizable: false,
        height:700,
        width: 700,
        title: wbkl10n.appointment,
        modal: true,
        open: function( event, ui ) {
        	jQuery( '.ui-dialog-buttonset' ).css( 'display', 'none' );
			jQuery( '#dialog-appointment' ).append( '<div class="loading"></div>');
			var data = {
						'action': 'wbk_view_appointment',
	 					'appointment_id': appointment_id,
						'service_id': service_id
	 				};
			jQuery(this).closest('.ui-dialog').find('button').eq(2).addClass('wbk_hidden');
	 		jQuery.post( ajaxurl, data, function(response) {

	 				if ( response != -1 && response != -2 && response != -3 && response != -4 && response != -5 && response != 0 ){
						jQuery( '.loading' ).remove();
			        	jQuery( '.ui-dialog-buttonset' ).css( 'display', 'block' );
						jQuery( '#appointment_dialog_content' ).css( 'display', 'block' );

						var objdata = jQuery.parseJSON(response);
						jQuery( '#wbk-appointment-time' ).val( objdata.time );
						jQuery( '#wbk-appointment-name' ).val( objdata.name );
						jQuery( '#wbk-appointment-email' ).val( objdata.email );
						jQuery( '#wbk-appointment-phone' ).val( objdata.phone );
						jQuery( '#wbk-appointment-desc' ).val( objdata.desc );
						jQuery( '#wbk-appointment-extra' ).val( objdata.extra );
						jQuery( '#wbk-appointment-quantity' ).val( objdata.quantity );

						jQuery( '#wbk-appointment-time' ).attr('readonly', true);
						jQuery( '#wbk-appointment-name' ).attr('readonly', true);
						jQuery( '#wbk-appointment-email' ).attr('readonly', true);
						jQuery( '#wbk-appointment-phone' ).attr('readonly', true);
						jQuery( '#wbk-appointment-desc' ).attr('readonly', true);
						jQuery( '#wbk-appointment-extra' ).attr('readonly', true);
						jQuery( '#wbk-appointment-quantity' ).attr('readonly', true);
						jQuery.each( objdata.extra, function( key, value ) {
							jQuery( "[data-id='"+ value[0] + "']" ).val( value[2] );
							jQuery( "[data-id='"+ value[0] + "']" ).attr('readonly', true);
						});
	 				} else {
						jQuery( '.loading' ).remove();
						jQuery( '#appointment_dialog_content' ).css( 'display', 'block' );
 						jQuery( '#appointment_dialog_content' ).html('error');
	 				}
	 			});


        },
		create:function () {

		},
        buttons: [
        	{
        		text: wbkl10n.delete,
        		click: function() {
					jQuery('.wbk_hidden').removeClass('wbk_hidden');

        		}
        	},
			{
				text: wbkl10n.confirm,
				click: function() {
					jQuery( '.ui-dialog-buttonpane' ).append( '<div class="loading"></div>' );
					jQuery( '.ui-dialog-buttonset' ).css( 'display', 'none' );
					var data = {
						'action': 'wbk_delete_appointment',
						'appointment_id': appointment_id,
						'service_id': service_id
					};
					jQuery.post( ajaxurl, data, function(response) {
						if ( response != -1 && response != -2 && response != -3 ){
							jQuery( '.loading' ).remove();
							jQuery( '.ui-dialog-buttonset' ).css( 'display', 'block' );
							if ( appointment_status == 1 ){
								var objdata = jQuery.parseJSON(response);
								parent.html( objdata.day );
								setEvents();
							} else {
								parent.html('');
							}
							jQuery( '#dialog-appointment' ).dialog( 'close' );

						} else {
							jQuery( '.loading' ).remove();
							jQuery( '.ui-dialog-buttonset' ).css( 'display', 'block' );
							parent.html('');
							jQuery( '#dialog-appointment' ).dialog( 'close' );
						}
					});
				}
			},
			{
				text: wbkl10n.close,
				click: function() {
		 			jQuery( '.loading' ).remove();
		        	jQuery( '#dialog-appointment' ).dialog( 'close' );
				}
	        }
	        ]

    });

}

// add appointment
function addAppointment( time, service_id, parent ) {

  	jQuery( 'input' ).removeClass('wbk-input-error');
	jQuery( '#appointment_dialog_content' ).css( 'display', 'none' );

 	jQuery( '#dialog-appointment' ).dialog({
	    resizable: false,
        height:700,
        width: 700,
        title: wbkl10n.appointment,
        modal: true,
        open: function( event, ui ) {
        	jQuery( '.ui-dialog-buttonset' ).css( 'display', 'none' );
			jQuery( '#dialog-appointment' ).append( '<div class="loading"></div>');
			var data = {
						'action': 'wbk_prepare_appointment',
	 					'time': time,
						'service_id': service_id
	 				};
	 		jQuery.post( ajaxurl, data, function(response) {

	 				if ( response != -1 && response != -2 && response != -3 && response != -4 && response != -5 && response != 0 ){
						jQuery( '.loading' ).remove();
			        	jQuery( '.ui-dialog-buttonset' ).css( 'display', 'block' );
						jQuery( '#appointment_dialog_content' ).css( 'display', 'block' );

						var objdata = jQuery.parseJSON(response);
						jQuery( '#wbk-appointment-time' ).val( objdata.time );
						jQuery( '#wbk-appointment-timestamp' ).val( objdata.timestamp );
						jQuery( '#wbk-appointment-name' ).val( '' );
						jQuery( '#wbk-appointment-email' ).val( '' );
						jQuery( '#wbk-appointment-phone' ).val( '' );
						jQuery( '#wbk-appointment-desc' ).val( '' );
						jQuery( '#wbk-appointment-extra' ).val( '' );
						jQuery( '#wbk-appointment-quantity-max' ).val( objdata.available );

						jQuery( '#wbk-appointment-time' ).attr('readonly', true);
						jQuery( '#wbk-appointment-name' ).attr('readonly', false);
						jQuery( '#wbk-appointment-email' ).attr('readonly', false);
						jQuery( '#wbk-appointment-phone' ).attr('readonly', false);
						jQuery( '#wbk-appointment-desc' ).attr('readonly', false);
						jQuery( '.wbk_table_custom_field_part' ).attr('readonly', false);

						jQuery( '#wbk-appointment-quantity' ).attr('readonly', false);
						jQuery( '#wbk-appointment-quantity' ).val('1');

						if( objdata.quantity == 1 )	{
							jQuery( '#wbk-appointment-quantity' ).attr('type', 'hidden');
							jQuery( '#wbk-appointment-quantity-label' ).css('display', 'none' );
					 		jQuery( '#wbk-appointment-quantity' ).val(1);


						} else {
							jQuery( '#wbk-appointment-quantity').attr('type', 'text');
							jQuery( '#wbk-appointment-quantity-label' ).css('display', 'inline' );
						}

						if ( wbkl10n.phonemask == 'enabled' ||  wbkl10n.phonemask == 'enabled_mask_plugin' ){
    						jQuery('#wbk-appointment-phone').mask(wbkl10n.phoneformat);
    					}

			    		jQuery( 'input' ).focus(function() {
							jQuery( this ).removeClass('wbk-input-error');
						});

	 				} else {
						jQuery( '.loading' ).remove();
						jQuery( '#appointment_dialog_content' ).css( 'display', 'block' );
 						jQuery( '#appointment_dialog_content' ).html('error ' + response);
	 				}
	 			});

        },
        buttons: [{
        	text: wbkl10n.add,
        	click: function() {

				var error_status = 0;

				extra_value = [];
		 		jQuery( '.wbk_table_custom_field_part' ).each( function() {
					var extra_item = [];
					extra_item.push( jQuery( this ).attr('data-id') );
					extra_item.push( jQuery( this ).attr('data-label') );
					extra_item.push( jQuery( this ).val() );
					extra_value.push( extra_item );
				});

		 		jQuery( '#wbk-appointment-extra' ).val( JSON.stringify( extra_value ) );
 				var name = jQuery.trim( jQuery( '#wbk-appointment-name' ).val() );
				var email = jQuery.trim( jQuery( '#wbk-appointment-email' ).val() );
				var phone = jQuery.trim( jQuery( '#wbk-appointment-phone' ).val() );
				var desc =  jQuery.trim( jQuery( '#wbk-appointment-desc' ).val() );
				var time = jQuery.trim( jQuery( '#wbk-appointment-timestamp' ).val() );
				var extra = jQuery.trim( jQuery( '#wbk-appointment-extra' ).val() );
				var quantity = parseInt( jQuery.trim( jQuery( '#wbk-appointment-quantity' ).val() ) );

				if ( !wbkCheckString( name, 3, 128 ) ){
	 				error_status = 1;
	 				jQuery( '#wbk-appointment-name' ).addClass('wbk-input-error');
	 			}
	 			if ( !wbkCheckEmail( email ) ){
	 				error_status = 1;
	 				jQuery( '#wbk-appointment-email' ).addClass('wbk-input-error');
	 			}
				if( wbkl10n.phone_required == '3' ){
					if ( !wbkCheckString( phone, 3, 30 ) ){
						error_status = 1;
						jQuery( '#wbk-appointment-phone' ).addClass('wbk-input-error');
					}
				}
	 			if ( !wbkCheckString( desc, 0, 255 ) ){
	 				error_status = 1;
	 				jQuery( '#wbk-appointment-desc' ).addClass('wbk-input-error');
	 			}
	 			if ( !wbkCheckString( extra, 0, 1000 ) ){
	 				error_status = 1;
	 				jQuery( '#wbk-appointment-extra' ).addClass('wbk-input-error');
	 			}
	 			var available = parseInt( jQuery( '#wbk-appointment-quantity-max' ).val() );
	 			if ( !wbkCheckIntegerMinMax( quantity, 1, available ) ) {
					error_status = 1;
	 				jQuery( '#wbk-appointment-quantity' ).addClass('wbk-input-error');
	 			}
	 			if ( error_status == 1 ) {
	 				return;
	 			}

	 			jQuery( '.ui-dialog-buttonpane' ).append( '<div class="loading"></div>' );
 			 	jQuery( '.ui-dialog-buttonset' ).css( 'display', 'none' );

				var data = {
					'action': 'wbk_add_appointment_backend',
 	 				'service_id': service_id,
 	 				'name': name,
 	 				'time': time,
 	 				'email': email,
 	 				'phone': phone,
 	 				'desc': desc,
 	 				'extra': extra,
 	 				'quantity': quantity
	 			};

				jQuery.post( ajaxurl, data, function(response) {

					if ( response != -1 &&
  						 response != -2 &&
 						 response != -3 &&
 						 response != -4 &&
 						 response != -5 &&
 						 response != -6 &&
 						 response != -7 &&
 						 response != -8 &&
 						 response != -9 &&
 						 response != -10 &&
 						 response != -11 &&
 						 response != -12 ) {
							jQuery( '.loading' ).remove();
							jQuery( '.ui-dialog-buttonset' ).css( 'display', 'block' );
							var objdata = jQuery.parseJSON(response);
							parent.html( objdata.day );
		                    setEvents();
		        			jQuery( '#dialog-appointment' ).dialog( 'close' );
		 				} else {
							jQuery( '.loading' ).remove();
		 				 	jQuery( '.ui-dialog-buttonset' ).css( 'display', 'block' );
							jQuery( '#appointment_dialog_content' ).html('error ' + response);
		  				}
	 			});
        		}
       		},
       		{
       			text: wbkl10n.close,
				click: function() {
					jQuery( '.loading' ).remove();
		        	jQuery( '#dialog-appointment' ).dialog( 'close' );
		        }
		    }
        ]
    });

}

// set events
function setEvents() {
	// appointment add
	jQuery('[id^=app_add_]').unbind('click');
 	jQuery('[id^=app_add_]').click( function() {
 		var id = jQuery(this).attr('id');
		var arr = id.split('_');
		var parent = jQuery(this).parent().parent().parent();
		addAppointment( arr[3], arr[2], parent );

 	});
 	// appointment click event
 	jQuery('[id^=wbk_appointment_]').unbind('click');
 	jQuery('[id^=wbk_appointment_]').click( function() {

 		var id = jQuery(this).attr('id');
		var arr = id.split('_');
		if ( arr[4] == 1 ){
			var parent = jQuery(this).parent().parent().parent();
		} else {
			var parent = jQuery(this).parent();
		}
		var parent = jQuery(this).parent().parent().parent();
 		viewAppointment( arr[2], arr[3], arr[4], parent );

 	});

   	// day lock click event
	jQuery('[id^=day_lock_]').unbind('click');
	jQuery('[id^=day_lock_]').click( function() {
		var id = jQuery(this).attr('id');
		var arr = id.split('_');
		var data = {
			'action': 'wbk_lock_day',
			'service_id': arr[2],
			'day': arr[3]
		};
		var prev_html = jQuery('#day_controls_' + arr[3]).html();
		jQuery('#day_controls_' + arr[3]).html('<div class="loading"></div><div class="cb"></div>');
	 	jQuery.post(ajaxurl, data, function(response) {
			if ( response == -1 ){
				jQuery('#day_controls_' + arr[3]).html(prev_html);
				setEvents();
			} else {
			 	jQuery('#day_controls_' + arr[3]).html(response);
			 	jQuery('#day_title_' + arr[3]).removeClass('green_bg');
			 	jQuery('#day_title_' + arr[3]).addClass('red_bg');
			 	setEvents();
			}
		});

	});
	// day unlock click event
	jQuery('[id^=day_unlock_]').unbind('click');
	jQuery('[id^=day_unlock_]').click( function() {
		var id = jQuery(this).attr('id');
		var arr = id.split('_');
		var data = {
			'action': 'wbk_unlock_day',
			'service_id': arr[2],
			'day': arr[3]
		};
		var prev_html = jQuery('#day_controls_' + arr[3]).html();
		jQuery('#day_controls_' + arr[3]).html('<div class="loading"></div><div class="cb"></div>');
	 	jQuery.post(ajaxurl, data, function(response) {
			if ( response == -1 ){
				jQuery('#day_controls_' + arr[3]).html(prev_html);
				setEvents();
			} else {
			 	jQuery('#day_controls_' + arr[3]).html(response);
			 	jQuery('#day_title_' + arr[3]).removeClass('red_bg');
			 	jQuery('#day_title_' + arr[3]).addClass('green_bg');
			 	setEvents()
			}
		});

	});

	// time lock click event
	jQuery('[id^=time_lock_]').unbind('click');
	jQuery('[id^=time_lock_]').click( function() {
		var id = jQuery(this).attr('id');
		var arr = id.split('_');
		var data = {
			'action': 'wbk_lock_time',
			'service_id': arr[2],
			'time': arr[3]
		};
		var control_parent = jQuery(this).parent();
		var timeslot =control_parent.parent().find('.timeslot_time');
		var initial_html = control_parent.html();
		control_parent.html('<div class="loading"></div><div class="cb"></div>');
 	 	jQuery.post(ajaxurl, data, function(response) {
			if ( response == -1 ){

			} else {
				control_parent.html(initial_html);
			 	control_parent.find('a').not('.wbk-appointment-backend').remove();
				control_parent.append(response);
			 	setEvents();
			 	timeslot.addClass('red_font');
			}
		});
	});

	// time unlock click event
	jQuery('[id^=time_unlock_]').unbind('click');
	jQuery('[id^=time_unlock_]').click( function() {
		var id = jQuery(this).attr('id');
		var arr = id.split('_');
		var data = {
			'action': 'wbk_unlock_time',
			'service_id': arr[2],
			'time': arr[3]
		};
		var control_parent = jQuery(this).parent();
		var timeslot = control_parent.parent().find('.timeslot_time');
		var initial_html = control_parent.html();
		control_parent.html('<div class="loading"></div><div class="cb"></div>');
 	 	jQuery.post(ajaxurl, data, function(response) {
			if ( response == -1 ){

			} else {
				control_parent.html(initial_html);
			 	control_parent.find('a').not('.wbk-appointment-backend').remove();
				control_parent.append(response);
			 	setEvents();
			 	timeslot.removeClass('red_font');
			}
		});
	});
}
function render_multiple_dates_form( dates ){
	if( dates.length > 0 ){
		var data = {
			'action': 'wbk_create_multiple_bookings_auto',
			'date': jQuery('#create_multiple_bookings_date').val(),
			'service_id': jQuery('#create_multiple_bookings_services').val()
		};
		jQuery('#control_container').html('');
		jQuery('#multiple_booking_form_container').children().not('.wbk_remains').remove();
		jQuery('#multiple_booking_form_container').append('<br><div class="loading"></div>');
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery('.loading').remove();

			jQuery('#multiple_booking_form_container').append(response);
			jQuery( 'input, select' ).focus(function() {
				jQuery( this ).removeClass('wbk-input-error');
			});
			jQuery('#wbk-book_appointment').click(function() {
				var name = jQuery.trim( jQuery( '#wbk-customer_name' ).val() );
				var email = jQuery.trim( jQuery( '#wbk-customer_email' ).val() );
				var phone = jQuery.trim( jQuery( '#wbk-customer_phone' ).val() );
				var desc =  jQuery.trim( jQuery( '#wbk-customer_desc' ).val() );
				var quantity = parseInt( jQuery.trim( jQuery( '#wbk-book-quantity' ).val() ) );
				var times = jQuery.trim( jQuery( '#wbk_times' ).val() );
				var error_status = 0;

				if ( !wbkCheckString( times, 1, 1024 ) ){
					error_status = 1;
					jQuery( '#wbk_times' ).addClass('wbk-input-error');
				}
				if ( !wbkCheckString( name, 1, 128 ) ){
	 				error_status = 1;
	 				jQuery( '#wbk-customer_name' ).addClass('wbk-input-error');
	 			}
	 			if ( !wbkCheckEmail( email ) ){
	 				error_status = 1;
	 				jQuery( '#wbk-customer_email' ).addClass('wbk-input-error');
	 			}
				if( wbkl10n.phone_required == '3' ){
					if ( !wbkCheckString( phone, 3, 30 ) ){
						error_status = 1;
						jQuery( '#wbk-appointment-phone' ).addClass('wbk-input-error');
					}
				}
	 			if ( !wbkCheckString( desc, 0, 255 ) ){
	 				error_status = 1;
	 				jQuery( '#wbk-customer_desc' ).addClass('wbk-input-error');
	 			}
				if( error_status == 1 ){
					return;
				}
				var data = {
					'action': 'wbk_create_multiple_bookings_auto_processing',
					'date': jQuery('#create_multiple_bookings_date').val(),
					'service_id': jQuery('#create_multiple_bookings_services').val(),
					'times': times,
					'name': name,
 					'email': email,
					'phone': phone,
					'desc': desc,
					'quantity' : quantity
				};
				jQuery('#multiple_booking_form_container').html('<br><div class="loading"></div>');
				jQuery.post(ajaxurl, data, function( response ) {
					jQuery('#multiple_booking_form_container').html(response);
				});
			});
		});
	} else {
		jQuery('#multiple_booking_form_container').children().not('.wbk_remains').remove();
	}
}
