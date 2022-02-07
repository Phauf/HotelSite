//if( wbkl10n.jquery_no_conflict == 'disabled' ){
//} else {
//	WBK_jQuery_ = jQuery.noConflict();
//}
WBK_jQuery_ = jQuery;
class WBK_Dashboard{
    constructor() {
        const get_this = () => { return this };
        get_this().initialize_plugion_fields();
        get_this().initialize_plugion_events();
	}
  	initialize_plugion_fields(){
        const get_this = () => { return this };
        plugion.add_setter( 'wbk_business_hours', function(element, value) {
            element.val(value);
            var format = element.attr('data-format');
            jQuery('.wbk_repeater_add_btn').unbind();
            var $repeater = element.siblings('.repeater').repeater({
                show: function () {
                    jQuery(this).slideDown('fast');
                    var start_field = jQuery(this).find('.wbk_bh_data_start');
                    var end_field = jQuery(this).find('.wbk_bh_data_end');
                    if( start_field.val() == '' ){
                        jQuery(this).find('.wbk_bh_data_status').val('active');
                        if( jQuery(this).closest('.repeater').find('.wbk_business_hours_group').eq(-2).length > 0 ){
                            var prev_day = jQuery(this).closest('.repeater').find('.wbk_business_hours_group').eq(-2).find('.wbk_bh_data_day option:selected').attr('data-number');
                            var prev_start = jQuery(this).closest('.repeater').find('.wbk_business_hours_group').eq(-2).find('.wbk_bh_data_start').val();
                            var prev_end = jQuery(this).closest('.repeater').find('.wbk_business_hours_group').eq(-2).find('.wbk_bh_data_end').val();
                            prev_day++;
                            if( prev_day == 8 ){
                                prev_day = 1;
                            }
                            jQuery(this).find('.wbk_bh_data_day option[data-number="' + prev_day + '"]').attr('selected','selected');
                            start_field.val(prev_start);
                            end_field.val(prev_end);
                        } else {
                            jQuery(this).find('.wbk_bh_data_day option[data-number="2"]').attr('selected','selected');
                            start_field.val(32400);
                            end_field.val(64800);
                        }
                    }
                    jQuery(this).find('.wbk_bh_data_day').change( function(){
                        element.trigger('change');
                    });
                    var start = jQuery(this).find('.wbk_bh_data_start').val();
                    var end = jQuery(this).find('.wbk_bh_data_end').val();
                    var parent = jQuery(this);
                    var s = jQuery(this).find('.slider-time').slider({
                        range: true,
                        min: 0,
                        max: 86400,
                        step: 300,
                        values: [ start, end ],
                        slide: function( event, ui ) {
                            var date = new Date(null);
                            date.setUTCSeconds(ui.values[0]);
                            if( format == 'ampm'){
                                var start = wbk_format_ampm( date );
                            } else{
                                var start = date.getUTCHours() + ':' + ( date.getUTCMinutes() < 10?'0':'') + date.getUTCMinutes();
                            }
                            var date = new Date(null);
                            date.setUTCSeconds(ui.values[1]);
                            if( format == 'ampm'){
                                var end = wbk_format_ampm( date );
                            } else{
                                var end = date.getUTCHours() + ':' +( date.getUTCMinutes() < 10?'0':'') + date.getUTCMinutes();
                            }
                            start_field.val(ui.values[0]);
                            end_field.val(ui.values[1]);
                            parent.find('.wbk_business_hours_group_time').html( start + ' - ' + end );
                            element.trigger('change');
                        }
                    });
                    s.slider("option", "slide").call(s, null, { values: s.slider("values") });
                    setTimeout( function(){
                        window.dispatchEvent(new Event('resize'));
                        }, 100 );
                    element.trigger('change');

                },
                hide: function (deleteElement) {
                    element.trigger('change');
                    jQuery(this).slideUp(deleteElement);
                },
                isFirstItemUndeletable: true
            });
            if( value !== null ){
                value = value.replace(/'/g, '"');
            }
            if( value != '' && value !== null  ){
                value = JSON.parse(value);
                value = value['dow_availability'];
                $repeater.setList( value );
            } else {
                $repeater.setList( {} );
            }
        });
        plugion.add_getter( 'wbk_business_hours', function(element) {
            if( element.siblings('.repeater').find('.wbk_bh_data_start').length == 0 ){

                return '';
            }
            var value = element.siblings('.repeater').repeaterVal();
            return JSON.stringify( value );
        });
        plugion.add_validator( 'wbk_business_hours', function(value, required, element) {
            return true;
        });
        plugion.add_getter( 'wbk_app_custom_data', function(element) {
            var custom_data = [];
            element.parent().find('.wbk_custom_data_item').each(function() {
                var id = jQuery( this ).attr( 'data-field-id' );
                var title = jQuery( this ).attr( 'data-title' );
                var val = jQuery( this ).val();
                custom_data.push( [id, title, val ] );
            });
            if( custom_data.length > 0 ){
                element.val( JSON.stringify( custom_data ) );
            }
            return element.val();
        });
        plugion.add_setter( 'wbk_app_custom_data', function(element, value) {
            element.val( value );
            if( value == '' ){
                return;
            }
            var custom_data = jQuery.parseJSON( value );
            jQuery.each(custom_data, function(k, v) {
                var id =  v[0].trim();
                var value = v[2].trim();
                element.parent().find( '[data-field-id="' +  id + '"]' ).val( value )
            });
        });
        plugion.add_getter( 'wbk_date_range', function(element){
            if( element.attr('data-start') == '' || element.attr('data-end') == '' ){
                return '';
            }
            return [element.attr('data-start'), element.attr('data-end')];
        });
        plugion.add_setter( 'wbk_date_range', function(element, value ){
            var datepicker_start = element.parent().find('.plugion_input_date_range_start').pickadate('picker');
            if( typeof datepicker_start === 'undefined' ){
                var datepicker_start = element.parent().find('.plugion_input_date_range_start').pickadate({
                    format: element.attr('data-dateformat'),
                    onSet: function(thingSet) {
                        if (thingSet.select == null) {
                            element.siblings('.plugion_input_container_small').find('.plugion_input_date_range_start').siblings('label').removeClass('plugion_label_pickadate');
                            element.attr('data-start', '');
                        } else {
                            element.siblings('.plugion_input_container_small').find('.plugion_input_date_range_start').siblings('label').addClass('plugion_label_pickadate');
                            element.attr('data-start', this.get('select', 'mm/dd/yyyy'));
                        }
                    }
                });
            }
            var datepicker_end = element.parent().find('.plugion_input_date_range_end').pickadate('picker');
            if( typeof datepicker_end === 'undefined' ){
                var datepicker_end = element.parent().find('.plugion_input_date_range_end').pickadate({
                    format: element.attr('data-dateformat'),
                    onSet: function(thingSet) {
                        if (thingSet.select == null) {
                            element.siblings('.plugion_input_container_small').find('.plugion_input_date_range_end').siblings('label').removeClass('plugion_label_pickadate');
                            element.attr('data-end', '');
                        } else {
                            element.siblings('.plugion_input_container_small').find('.plugion_input_date_range_end').siblings('label').addClass('plugion_label_pickadate');
                            element.attr('data-end', this.get('select', 'mm/dd/yyyy'));
                        }
                    }
                });
            }
            if ( value != null) {
                if( value == '' ){
                    var date_start = new Date();
                    element.parent().find('.plugion_input_date_range_start').pickadate('picker').set('select', [date_start.getUTCFullYear(), date_start.getUTCMonth(), date_start.getUTCDate()]);
                    var default_daterange = element.attr('data-rangedefault');
                    if( !plugion.validate_integer( default_daterange ) ){
                        default_daterange = 14;
                    }
                    var date_end = new Date( date_start.getTime() + 86400000 * default_daterange );
                    element.parent().find('.plugion_input_date_range_end').pickadate('picker').set('select', [date_end.getUTCFullYear(), date_end.getUTCMonth(), date_end.getUTCDate()]);
                    return;
                }

                var start = value[0];
                var date_start = new Date(Date.parse(start.replace(/[-]/g, '/') + ' UTC'));
                element.parent().find('.plugion_input_date_range_start').pickadate('picker').set('select', [date_start.getUTCFullYear(), date_start.getUTCMonth(), date_start.getUTCDate()]);

                var end = value[1];
                var date_end = new Date(Date.parse(end.replace(/[-]/g, '/') + ' UTC'));
                element.parent().find('.plugion_input_date_range_end').pickadate('picker').set('select', [date_end.getUTCFullYear(), date_end.getUTCMonth(), date_end.getUTCDate()]);

            }
        });
        jQuery('#wbk_csv_export').on( 'click', function(){
            var delimiter = jQuery(this).attr('data-delimiter');
            var table_rows = jQuery('.plugion_table .plugion_table_row_item').get().map(function(row) {
              return jQuery(row).find('.plugion_exportable').not('.plugion_hidden').get().map(function(cell) {
                  return jQuery(cell).html().replace(/<\/?[^>]+(>|$)/g, "").replace(/(\r\n|\n|\r)/gm, "");
              });
            });
            jQuery('#slf_table_csv_export_container').html('<div class="loading"></div>');
            jQuery('.plugion_loader_quad').removeClass('plugion_hidden');

            var converted = '';
            jQuery.each( table_rows, function( index, value ) {
                converted += value.join(delimiter) + '%%';
            });
            var data = {
                'data':  converted,
            };
            jQuery.ajax(plugionl10n.rest_url + 'wbk/v1/csv-export/', {
                method: "POST",
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', plugionl10n.nonce);
                },
                data: data,
                statusCode: {
                    200: function(response) {
                        jQuery('.plugion_loader_quad').addClass('plugion_hidden');
                        location.href = response.url;
                    }
                }
            });
        });

  	}
    build_booking_time_field(){
        if( jQuery('#appointment_time').length == 0 ){
            return;
        }
        const get_this = () => { return this };
        var date = jQuery('#appointment_day').siblings("[name='day_submit']").val();
        var service_id = jQuery('#appointment_service_id').val();
        if( date == '' || service_id == 'plugion_null' ){
            return;
        }

        jQuery('#appointment_time').attr('disabled', true);
        jQuery('#appointment_time option').remove();
        jQuery('#appointment_time').niceSelect('update');
        jQuery('#appointment_time').siblings('.nice-select').addClass('plugion_loader');
        var current_booking = '';
        if( jQuery('#appointment_time').closest('.plugion_property_container_update_form').length > 0 ){
            current_booking = jQuery('#appointment_time').closest('.plugion_property_container_update_form').attr('data-id');
        }
        var data = {
            'date':  date,
            'service_id': service_id,
            'current_booking': current_booking
        };

        jQuery.ajax(plugionl10n.rest_url + 'wbk/v1/get-available-time-slots-day/', {
            method: "POST",
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', plugionl10n.nonce);
            },
            data: data,
            statusCode: {
                200: function(response) {
                    jQuery('#appointment_time').siblings('.nice-select').removeClass('plugion_loader');
                    var options_html = ' <option value="plugion_null">' + plugionl10n.select_option +'</option>';
                    jQuery.each( response.time_slots, function( i, val ) {
                        if(  val.free_places != 0 ){
                            options_html += '<option value="'+ val.start  +'" data-availablility=' + val.free_places + ' >' + val.formated_time_backend + ' (' + val.free_places + ' available)' + '</option>';
                        }
                    });
                    jQuery('#appointment_time').html( options_html );
                    jQuery('#appointment_time').attr('disabled', false);
                    if( jQuery('#appointment_time').attr('data-initial-val') != '' ){
                        jQuery('#appointment_time').val( jQuery('#appointment_time').attr('data-initial-val') );
                        jQuery('#appointment_time').attr('data-initial-val', '');
                    }
                    jQuery('#appointment_time').on( 'change', function(){
                        var quantity = jQuery(this).find('option:selected').attr('data-availablility');
                        var options_html = '<option value="plugion_null">' + plugionl10n.select_option + '</option>';
                        var i;

                        for( i = 1; i <= quantity; i++){
                            if( i == 1 ){
                                var selected = ' selected ';
                            } else {
                                var selected = '';
                            }
                            options_html += '<option ' + selected + ' value="' + i + '">' + i + '</option>';
                        }
                        jQuery('#appointment_quantity').html(options_html);
                        jQuery('#appointment_quantity').niceSelect('update');

                    });
                    jQuery('#appointment_time').niceSelect('update');
                    jQuery('#appointment_time').trigger('change');

                    if( jQuery('#appointment_quantity').attr('data-initial-val') != '' ){
                        jQuery('#appointment_quantity').val( jQuery('#appointment_quantity').attr('data-initial-val') );
                        jQuery('#appointment_quantity').attr('data-initial-val', '');
                    }

                    jQuery('#appointment_quantity').niceSelect('update');
                    return;
                },
                400: function(response) {
                    jQuery('#appointment_time').siblings('.nice-select').removeClass('plugion_loader');
                    return;
                },
                403: function(response) {
                    jQuery('#appointment_time').siblings('.nice-select').removeClass('plugion_loader');
                    return;
                }

            }
        });
    }
    initialize_plugion_events(){
        const get_this = () => { return this };
        jQuery(document).on( 'plugion_properties_form_initialized', function(){
            // initialize automatic update of step based on duration and interval
            jQuery( '#service_duration, #service_interval_between' ).focusout(function() {
        		var duration = parseInt( jQuery( '#service_duration' ).val() );
        		var interval = parseInt( jQuery( '#service_interval_between' ).val() );
                var sum =  duration + interval;
                if( Number.isInteger(sum) ){
                    jQuery( '#service_step' ).val( duration + interval );

                }
        	});
            if( jQuery('.plugion_property_container_update_form').attr('data-table') == 'wbk_cancelled_appointments' ){
                jQuery('#appointment_name').parent().parent().css('display','none');
            }
            jQuery('#coupon_amount_fixed').focusout( function(){
                jQuery('#coupon_amount_percentage').val('0');
            });
            jQuery('#coupon_amount_percentage').focusout( function(){
                jQuery('#coupon_amount_fixed').val('0');
            });
        });
        jQuery(document).on( 'plugion_filter_form_initialized', function(){
            jQuery('#wbk_category_list').change( function(){
                if( jQuery(this).val() == 0 ){
                    return;
                }
                var services = jQuery(this).find('option:selected').attr('data-services');

                if( services != 'false' ){
                    jQuery('#appointment_service_id').val(jQuery.parseJSON(services));
                    jQuery('#appointment_service_id').trigger('chosen:updated');
                }
            });

        });

        jQuery(document).on( 'plugion_after_dependency_initialized', function(){
            // initialize loading of available time slots when service or
            // day is updated in the appointment settings
            jQuery('#appointment_service_id, #appointment_day').change( function(){

                get_this().build_booking_time_field();
            });
            get_this().build_booking_time_field();

        });


    }
    get_available_time_slots_day( service, day ){

    }

}
class WBK_Form{
    constructor( container ) {
        const get_this = () => { return this };
        this.container = container;
    }
    get_form_values(){
        // // TODO: add container content. replace id with name
        const get_this = () => { return this };
        var form_data = new FormData();
        var name = jQuery.trim( jQuery( '[name="wbk-name"]' ).val() );
        form_data.append( 'name', name);
    	var email = jQuery.trim( jQuery( '[name="wbk-email"]'  ).val() );
        form_data.append( 'email', email );
    	if( jQuery( '[name="wbk-phone-cf7it-national"]').length > 0 ){
    		var phone_code = jQuery.trim( jQuery( '[name="wbk-phone-cf7it-national"]').parent().find('.selected-flag').attr('title') );
    		phone_code = phone_code.match(/\d+/)[0];
    		var phone = '+' +  phone_code + ' ' +  jQuery.trim( jQuery( '[name="wbk-phone-cf7it-national"]').val() );
    	} else {
    		var phone = jQuery.trim( jQuery( '[name="wbk-phone"]' ).val() );
    	}
        form_data.append( 'phone', phone );
    	var desc =  jQuery.trim( jQuery( '#wbk-comment' ).val() );
        form_data.append( 'desc', desc );
    	var current_category = jQuery( '#wbk-category-id' ).val();
    	if ( !wbkCheckIntegerMinMax( current_category, 1, 1000000 ) ){
    		current_category = 0;
    	}
        form_data.append( 'current_category', current_category );
    	var extra_data = [];
    	// custom fields values (text)
    	jQuery('.wbk-text, .wbk-email-custom').not('#wbk-name,#wbk-email,#wbk-phone').each(function() {
    		if( jQuery(this).closest('.wpcf7cf-hidden').length == 0 ){
    			var extra_item = [];
    			extra_item.push( jQuery( this ).attr('id') );
    			extra_item.push( jQuery('label[for="' + jQuery( this ).attr('id') + '"]').html() );
    			extra_item.push( jQuery( this ).val() );
    			extra_data.push( extra_item );
    		}
    	});
    	// custom fields values (checkbox)
    	jQuery('.wbk-checkbox-custom.wpcf7-checkbox').each(function() {
    		if( jQuery(this).closest('.wpcf7cf-hidden').length == 0 ){
    			var extra_item = [];
    			extra_item.push( jQuery( this ).attr('id') );
    			extra_item.push( jQuery('label[for="' + jQuery( this ).attr('id') + '"]').html() );
    			var current_checkbox_value = '';
    			jQuery(this).children('span').each(function() {
    				jQuery(this).children('input:checked').each(function() {
    					current_checkbox_value += jQuery(this).val() + ' ';
    				});
    			});
                current_checkbox_value = jQuery.trim( current_checkbox_value );

    			extra_item.push( current_checkbox_value );
    			extra_data.push( extra_item );
    		}
    	});
    	jQuery('.wbk-select').not('#wbk-book-quantity, #wbk-service-id').each(function() {
    		if( jQuery(this).closest('.wpcf7cf-hidden').length == 0 ){
    			var extra_item = [];
    			extra_item.push( jQuery( this ).attr('id') );
    			extra_item.push( jQuery('label[for="' + jQuery( this ).attr('id') + '"]').html() );
    			extra_item.push( jQuery( this ).val() );
    			extra_data.push( extra_item );
    		}
    	});
    	// custom fields text areas
    	jQuery('.wbk-textarea').not('#wbk-comment,#wbk-customer_desc').each(function() {
    		if( jQuery(this).closest('.wpcf7cf-hidden').length == 0 ){
    			var extra_item = [];
    			extra_item.push( jQuery( this ).attr('id') );
    			extra_item.push( jQuery('label[for="' + jQuery( this ).attr('id') + '"]').html() );
    			extra_item.push( jQuery( this ).val() );
    			extra_data.push( extra_item );
    		}
    	});
        extra_data = JSON.stringify( extra_data );
        form_data.append( 'extra_data', extra_data);
    	// secondary names, emails
    	var secondary_data = [];
    	jQuery('[id^="wbk-secondary-name"]').each(function() {
    		var name_p = jQuery(this).val();
    		var name_id = jQuery(this).attr('id');
    		if ( wbkCheckString( name_p, 1, 128 ) ){
    			var arr = name_id.split( '_' );
    			var id2 = 'wbk-secondary-email_' + arr[1];
    			email_p = jQuery('#' + id2).val();
    			var person = new Object();
    			person.name = name_p;
    			person.email = email_p;
    			secondary_data.push(person);
    		}
    	});
        var times = [];
        var services = [];
        var quantities = [];
        jQuery('.wbk-slot-active-button').not('#wbk-to-checkout').each(function() {
            var btn_id = jQuery(this).attr('id');
            var time = btn_id.substring(17, btn_id.length);
            var service = jQuery(this).attr( 'data-service' );
            times.push(time);
            services.push(service);
            if( jQuery( ".wbk-book-quantity[data-service='" + service + "']" ).length > 0 ){
                quantities.push( jQuery( ".wbk-book-quantity[data-service='" + service + "']" ).val() );
            } else {
                quantities.push(1);
            }

        });
        if( times.length == 0 ){
            var times = [];
            var services = [];
            var quantities = [];
            times.push(jQuery('#wbk-book_appointment').attr('data-time'));
            services.push(  jQuery('#wbk-service-id').val() );
            if( jQuery( ".wbk-book-quantity" ).length > 0 ){
                quantities.push( jQuery( ".wbk-book-quantity" ).val() );
            } else {
                quantities.push(1);
            }
        }
        form_data.append( 'times', times);
        form_data.append( 'services', services);
        form_data.append( 'secondary_data', secondary_data);
        form_data.append( 'quantities', quantities);

        return form_data;
    }
    update_amounts(){
        const get_this = () => { return this };
        var form_data  = get_this().get_form_values();
        form_data.append( 'action', 'wbk_calculate_amounts' );
        jQuery('.wbk_form_label_total').html('<span class="wbk-loading_small"></div>');
        jQuery.ajax({
            url: wbkl10n.ajaxurl,
            type: 'POST',
            data: form_data,
            cache: false,
            processData: false,
            contentType: false,
            success: function( response ) {
            	response_obj = jQuery.parseJSON( response );
                jQuery('.wbk_form_label_total').html(response_obj.total_formated);

            }
        });
    }
    clear_date_container(){
        const get_this = () => { return this };
        get_this().container.find('.wbk_date_container').html('');
    }
    clear_time_container(){
        const get_this = () => { return this };
        get_this().container.find('.wbk_date_container').html('');
    	WBK_jQuery_('#wbk-to-checkout').fadeOut( function(){
    		WBK_jQuery_('#wbk-to-checkout').remove();
    	});
    }
    clear_form(){
        const get_this = () => { return this };
        get_this().container.find('.wbk_booking_form_container').html('');
    }
    clear_done(){
        const get_this = () => { return this };
        get_this().container.find('.wbk_booking_done').html('');
        get_this().container.find('.wbk_payment').html('');
    }
    clear_timeslots(){
        const get_this = () => { return this };
        get_this().container.find('.wbk_slots_container').html('');

    }
    initialize(){
        const get_this = () => { return this };
        get_this().container.find('.wbk_services').change(function() {
    		get_this().clear_date_container();
            get_this().clear_time_container();
            get_this().clear_form();
            get_this().clear_done();
            get_this().clear_timeslots();

    		var service_id =  WBK_jQuery_(this).val();
    		if ( service_id != 0 ){
    			wbk_renderSetDate( true );
    			var service_desc = WBK_jQuery_(this).find('[value="' + service_id + '"]').attr('data-desc');
    			if( wbkl10n.show_desc == 'enabled' ){
    				get_this().container.find( '.wbk_description_holder' ).html( '<label class="wbk-input-label">' + service_desc + '</label>' );
    			}
    			var multi_limit = WBK_jQuery_(this).find(':selected').attr('data-multi-limit');
    			if( multi_limit == '' ){
     				wbkl10n.multi_limit = wbkl10n.multi_limit_default;
    			} else {
     				wbkl10n.multi_limit = multi_limit;
    			}
    			wbkl10n.multi_low_limit = WBK_jQuery_(this).find(':selected').attr('data-multi-low-limit');
    		} else {
                get_this().clear_date_container();
                get_this().clear_time_container();
    		}
    	});

    }
}

var wbk_dashboard;
// events intialization start
jQuery(document).on( 'plugion_initialized', function( event ) {
    wbk_dashboard = new WBK_Dashboard();
    jQuery('.table_wbk_appointments').on( 'draw.dt', function () {
        wbk_hide_default_custom_field();
    });
});
jQuery(document).on( 'plugion_before_row_events', function( event, skip_th ){
    wbk_init_app_custom_fiels( skip_th );
    jQuery('.plugion_table_add_button[data-table="wbk_cancelled_appointments"]').remove();
});
jQuery(document).on( 'wbk_on_form_rendered', function( event, container ){
    wbk_form = new WBK_Form( container );
    wbk_form.update_amounts();

    container.find('input, select').change( function(){
        wbk_form = new WBK_Form( container );
        wbk_form.update_amounts();
    });
});
jQuery( function ($) {

})
// events intialization end
function wbk_format_ampm(date) {
    var hours = date.getUTCHours();
    var minutes = date.getUTCMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0'+minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}
function wbk_init_app_custom_fiels( skip_th = false ){
    if( typeof( wbk_custom_fields ) == "undefined" ){
        return;
    }
    if( wbk_custom_fields != '' ){
        var items =  wbk_get_custom_fields( wbk_custom_fields );
        if( !skip_th ){

            var title_html = '';
            jQuery.each( items, function( i, val ) {
                title_html += '<th class="plugion_cell plugion_exportable">' + val[1] + '</th>';
            });
            jQuery( title_html ).insertAfter('#title_appointment_extra');
        }
        jQuery('.wbk_app_custom_data_value').each(function() {
            wbk_set_custom_fields_value( jQuery(this), items  )
        });
        wbk_hide_default_custom_field();
    }
}
function wbk_hide_default_custom_field(){
    if( wbk_custom_fields != '' ){
        var items =  wbk_get_custom_fields( wbk_custom_fields );
        if( items.length > 0 ){
            jQuery('#title_appointment_extra').addClass('plugion_hidden');
            jQuery('.wbk_app_custom_data_value').parent().addClass('plugion_hidden');
        }
    }
}
function wbk_set_custom_fields_value( elem, items  ){
    var td_elem = elem.parent();
    var value_html = '';
    jQuery.each( items, function( i, val ) {
        var current_val = '';
        var elem_html = elem.html();
        elem_html = elem_html.trim();
        if( elem_html != '' ){
            var custom_data = jQuery.parseJSON( elem_html );
            jQuery.each(custom_data, function(k, v) {
                if( val[0].trim() == v[0].trim() ){
                    current_val = v[2].trim();
                }
            });
        }
        value_html += '<td class="plugion_cell plugion_exportable">' + current_val +'</td>';
    });
    jQuery( value_html ).insertAfter(td_elem);

}
function wbk_get_custom_fields( input  ){
    var items = input.split(',');
    var result = [];
    jQuery.each( items, function( i, val ) {
        val = val.trim();
        var title = val.substring(
            val.lastIndexOf('[') + 1,
            val.lastIndexOf(']')
        );
        var id = val.substring(
            0,
            val.lastIndexOf('[')
        );
        if( id == '' ){
            id = val;
            title = val;
        }
        result.push( [ id.trim(), title.trim() ] );
    });
    return result;
}
