class Plugion{
    constructor() {
        const get_this = () => { return this };
        get_this().properties_default_content_add = new Object();
        get_this().properties_default_content_update = new Object();
        get_this().filter_form_content = new Object();
        get_this().filter_form_values = new Object();
        get_this().discard_buffer = '';
        get_this().tables = new Object();
        get_this().field_validators = {
            // validation for text field
            text: function(value, required, element) {
                if (value.trim() == '' && required == '1') {
                    return false;
                }
                if( element.attr('data-type') == '' ){
                    if (value.trim().length > 256) {
                        return false;
                    }
                } else {
                    if( element.attr('data-type') == 'positive_integer' ){
                        return get_this().validate_integer_range( value, 1, 2147483647 );
                    }
                    if( element.attr('data-type') == 'none_negative_integer' ){
                        if( required == '' && value == '' ){
                            return true;
                        }
                        return get_this().validate_integer_range( value, 0, 2147483647 );
                    }
                    if( element.attr('data-type') == 'integer' ){
                        return get_this().validate_integer_range( value,-2147483647, 2147483647 );
                    }
                    if( element.attr('data-type') == 'float' ){
                        return get_this().validate_integer_range( value );
                    }
                    if( element.attr('data-type') == 'none_negative_float' ){
                        if( get_this().validate_float( value ) ){
                            if ( value => 0 ){
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    }
                    if( element.attr('data-type') == 'email' ){
                        return get_this().validate_email( value );
                    }
                }
                return true;
            },
            // validation for radio button
            radio: function(value, required, element) {
                return true;
            },
            // validation for checbox button
            checkbox: function(value, required, element) {
                return true;
            },
            // validation for select
            select: function(value, required, element) {
                if( element.prop('multiple') ){
                    if( ( value == null || value == '' ) && required == '1') {
                        return false;
                    }
                } else {
                    if (value.trim() == 'plugion_null' && required == '1') {
                        return false;
                    }
                }
                return true;
            },
            // validation for datetime input
            datetime: function(value, required, element) {
                if (value.trim() == '' && required == '1') {
                    var date_input = jQuery('#' + element.attr('id') + '_date');
                    if (date_input.val() == '') {
                        date_input.addClass('plugion_input_field_error');
                    }
                    var time_input = jQuery('#' + element.attr('id') + '_time');
                    if (time_input.val() == '') {
                        time_input.addClass('plugion_input_field_error');
                    }
                    return false;
                }
                return true;
            },
            date: function(value, required, element) {
                if (value.trim() == '' && required == '1') {
                    return false;
                }
                return true;
            },
            textarea: function(value, required, element) {
                if (value.trim().length > 65535) {
                    return false;
                }
                if (value.trim() == '' && required == '1') {
                    return false;
                }
                return true;
            },
            editor: function(value, required, element) {
                if (value.trim().length > 65535) {
                    return false;
                }
                if (value.trim() == '' && required == '1') {
                    return false;
                }
                return true;
            },
            date_range: function(value, required, element) {
                var start = Date.parse( element.attr('data-start')  );
                var end = Date.parse( element.attr('data-end')  );
                if( ( isNaN(start) || isNaN(end) ) && required == '1' ){
                    return false;
                }
                if( ( !isNaN(start) && isNaN(end) ) || ( isNaN(start) && !isNaN(end) ) ){
                    return false;
                }
                if( start > end ){
                    return false;
                }
                return true;
            }
        };
        get_this().field_setters = {
            // setter for radio
            radio: function(element, value) {
                element.val([value]);
            },
            // setter for checkbox
            checkbox: function(element, value) {
                element.val([value]);
            },
            // setter for select
            select: function(element, value) {
                if (value == '' || value == null) {
                    if( element.attr('data-default') == '' ){
                        value = 'plugion_null';
                    } else {
                        value = element.attr('data-default');
                    }
                }
                if( element.prop('multiple') ){
                    element.chosen('destroy');
                    if( value == 'plugion_null' ){
                        element.chosen({width: '99%'});
                        return;
                    }
                    if( !Array.isArray( value ) ){
                        value = JSON.parse(value);
                    }
                    element.val(value);
                    element.chosen({width: '99%'});
                } else {
                    element.niceSelect('destroy');
                    element.val(value);
                    element.attr('data-initial-val', value );
                    element.niceSelect();
                }

            },
            datetime: function(element, value) {
                var datepicker = element.siblings('.plugion_input_container_small').find('.plugion_input_datetime_date').pickadate({
                    format: element.attr('data-dateformat'),
                    onSet: function(thingSet) {
                        if (thingSet.select == null) {
                            element.siblings('.plugion_input_container_small').find('.plugion_input_datetime_date').siblings('label').removeClass('plugion_label_pickadate');
                            element.attr('data-date', '');
                        } else {
                            element.attr('data-date', this.get('select', 'dd mmm yyyy'));
                            element.siblings('.plugion_input_container_small').find('.plugion_input_datetime_date').siblings('label').addClass('plugion_label_pickadate');
                        }
                        if (element.attr('data-date') != '' && element.attr('data-time') != '') {
                            element.val(element.attr('data-date') + ' ' + element.attr('data-time') + ' ' + element.attr('data-timezone'));
                        } else {
                            element.val('')
                        }
                    }
                });
                var timepicker = element.siblings('.plugion_input_container_small').find('.plugion_input_datetime_time').pickatime({
                    format: element.attr('data-timeformat'),
                    onSet: function(thingSet) {
                        if (thingSet.select == null) {
                            element.siblings('.plugion_input_container_small').find('.plugion_input_datetime_time').siblings('label').removeClass('plugion_label_pickadate');
                            element.attr('data-time', '');
                        } else {
                            element.attr('data-time', this.get('select', 'HH:i:00'));
                            element.siblings('.plugion_input_container_small').find('.plugion_input_datetime_time').siblings('label').addClass('plugion_label_pickadate');
                        }
                        if (element.attr('data-date') != '' && element.attr('data-time') != '') {
                            element.val(element.attr('data-date') + ' ' + element.attr('data-time') + ' ' + element.attr('data-timezone'));
                        } else {
                            element.val('')
                        }
                    }
                });
                if (value != '' && value != null) {
                    date1 = new Date(Date.parse(value.replace(/[-]/g, '/') + ' UTC'));
                    date2 = new Date(Date.parse(value.replace(/[-]/g, '/') + ' ' + element.attr('data-timezone')));
                    var delta = (date1 - date2) / 1000;
                    date1.setSeconds(date1.getSeconds() + delta);
                    datepicker.pickadate('picker').set('select', [date1.getUTCFullYear(), date1.getUTCMonth(), date1.getUTCDate()]);
                    timepicker.pickatime('picker').set('select', [date1.getUTCHours(), date1.getUTCMinutes()]);
                }
            },
            date: function(element, value) {
                var datepicker = element.pickadate({
                    format: element.attr('data-dateformat'),
                    formatSubmit: 'yyyy-mm-dd',
                    onSet: function(thingSet) {
                        if (thingSet.select == null) {
                            element.siblings('label').removeClass('plugion_label_pickadate');
                        } else {
                            element.siblings('label').addClass('plugion_label_pickadate');
                        }
                    }
                });
                if (value != '' && value != null) {
                    var date = new Date(Date.parse(value.replace(/[-]/g, '/') + ' UTC'));
                    datepicker.pickadate('picker').set('select', [date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate()]);
                }
            },
            editor:  function(element, value) {
                var editor = tinymce.get(element.attr('id') );
                if( editor != null ){
                    editor.destroy();
                }
                element.val(value);
                var seconds = 0;
                if( jQuery('.plugion_accordion').length > 0 ){
                    seconds = 300;
                }
                setTimeout(
                    function(){
                        wp.editor.initialize( element.attr('id'), {
                            tinymce: {
                              height : '150',
                              wpautop  : true,
                              theme    : 'modern',
                              skin     : 'lightgray',
                              language : 'en',
                              valid_elements : '*[*]',
                              formats  : {
                                  alignleft  : [
                                      { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'left' } },
                                      { selector: 'img,table,dl.wp-caption', classes: 'alignleft' }
                                  ],
                                  aligncenter: [
                                      { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'center' } },
                                      { selector: 'img,table,dl.wp-caption', classes: 'aligncenter' }
                                  ],
                                  alignright : [
                                      { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'right' } },
                                      { selector: 'img,table,dl.wp-caption', classes: 'alignright' }
                                  ],
                                  strikethrough: { inline: 'del' }
                              },
                              relative_urls       : false,
                              remove_script_host  : false,
                              convert_urls        : false,
                              browser_spellcheck  : true,
                              fix_list_elements   : true,
                              entities            : '38,amp,60,lt,62,gt',
                              entity_encoding     : 'raw',
                              keep_styles         : true,
                              paste_webkit_styles : 'font-weight font-style color',
                              preview_styles      : 'font-family font-size font-weight font-style text-decoration text-transform',
                              tabfocus_elements   : ':prev,:next',
                              plugins    : 'charmap,hr,media,paste,tabfocus,textcolor,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview',
                              resize     : 'vertical',
                              menubar    : false,
                              indent     : false,
                              toolbar1   : 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                              toolbar2   : 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                              toolbar3   : '',
                              toolbar4   : '',
                              body_class : 'id post-type-post post-status-publish post-format-standard',
                              wpeditimage_disable_captions: false,
                              wpeditimage_html5_captions  : true

                            },
                            quicktags   : true,
                            mediaButtons: true
                        })
                        tinymce.get(element.attr('id') ).onChange.add(function (ed, e) {
                            element.trigger('change');
                        });
                        jQuery(window).trigger('resize');
                        }, seconds);

            },
            date_range: function(element, value) {
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
                        element.parent().find('.plugion_input_date_range_start').pickadate('picker').clear();
                        element.parent().find('.plugion_input_date_range_end').pickadate('picker').clear();
                        return;
                    }
                    var value = value.split( ' - ');
                    var start = value[0];
                    var date_start = new Date(Date.parse(start.replace(/[-]/g, '/') + ' UTC'));
                    element.parent().find('.plugion_input_date_range_start').pickadate('picker').set('select', [date_start.getUTCFullYear(), date_start.getUTCMonth(), date_start.getUTCDate()]);

                    var end = value[1];
                    var date_end = new Date(Date.parse(end.replace(/[-]/g, '/') + ' UTC'));
                    element.parent().find('.plugion_input_date_range_end').pickadate('picker').set('select', [date_end.getUTCFullYear(), date_end.getUTCMonth(), date_end.getUTCDate()]);

                }
            }

        };
        get_this().field_getters = {
            // getter for radio
            radio: function(element) {
                if (element.is(':checked')) {
                    return element.val();
                } else {
                    return null;
                }
            },
            // getter for checkbox
            checkbox: function(element) {
                if (element.is(':checked')) {
                    return element.val();
                } else {
                    return '';
                }
            },
            datetime: function(element) {
                if (element.val() == '') {
                    return;
                }
                var in_time_zone = Date.parse(element.val());
                var date = new Date(in_time_zone);
                return date.toJSON().slice(0, 19).replace('T', ' ');
            },
            date: function(element) {

                return element.siblings('input').val();
            },
            editor: function(element){
                var content;
                var editor = tinyMCE.get(element.attr('id'));
                if (editor) {
                    // Ok, the active tab is Visual
                    content = editor.getContent();
                } else {
                    // The active tab is HTML, so just query the textarea
                    content = jQuery( '#'+ element.attr('id') ).val();
                }
                return content;
            },
            date_range: function( element ){
                if( element.attr('data-start') == '' || element.attr('data-end') == '' ){
                    return '';
                }
                return element.attr('data-start') + ' - ' +element.attr('data-end');
            },
            select: function( element ){
                var value = element.val();
                if( value === null || value.length == 0 ){
                    value = '';
                }
                return value;

            }
        };

        jQuery('.plugion_property_container_add_form').each(function() {
            get_this().properties_default_content_add[jQuery(this).attr('data-table')] =  jQuery(this).html();
            jQuery(this).html('');
        });
        jQuery('.plugion_property_container_update_form').each(function() {
            get_this().properties_default_content_update[jQuery(this).attr('data-table')] = jQuery(this).html();
            jQuery(this).html('');
        });
        // store filter form content to object
        jQuery('.plugion_filter_form').each(function() {
            get_this().filter_form_content[jQuery(this).attr('data-table')] = jQuery(this).html();
            jQuery(this).html('');
        });
        jQuery('.plugion_property_container_add_form').not(':eq(0)').remove();
        jQuery('.plugion_property_container_update_form').not(':eq(0)').remove();
        // initialize add new element form
        jQuery('.plugion_table_add_button').click(function() {
            jQuery('.plugion_property_container_update_form').hide();
            jQuery('.plugion_property_container_update_form').html('');

            jQuery('.plugion_property_container_add_form').html('');
            jQuery('.plugion_property_container_add_form').hide();

            jQuery('.plugion_filter_form').html('');
            jQuery('.plugion_filter_form').hide();
            jQuery('.plugion_property_container_add_form').show('slide', {
                direction: 'right'
            }, 300, function(){
                get_this().initialize_property_form('add', jQuery(this).attr('data-table'));
            });
        });
        // initialize filter form
        jQuery('.plugion_filter_button').click(function() {
            jQuery('.plugion_property_container_update_form').hide();
            jQuery('.plugion_property_container_update_form').html('');
            jQuery('.plugion_property_container_add_form').html('');
            jQuery('.plugion_property_container_add_form').hide();
            jQuery('.plugion_filter_form').show('slide', {
                direction: 'right'
            }, 300);
            get_this().initialize_filter_form(jQuery(this).attr('data-table'));
        });

        jQuery(document).trigger('plugion_before_row_events', false );

        get_this().set_rows_events();
        jQuery('.plugion_table').each(function() {
            get_this().tables[jQuery(this).attr('data-table')] = get_this().init_datatable(jQuery(this));

        });

        jQuery.fn.plugion_observe = function(eventName, callback) {
            return this.each(function() {
                var el = this;
                jQuery(document).on(eventName, function() {
                    callback.apply(el, arguments);
                })
            });
        }


    }

    init_datatable(elem) {

        const get_this = () => { return this };

        var i = 0;
        var columnDefs = [];
        elem.find('th').each(function() {
            if (jQuery(this).attr('data-sorttype') != '') {
                columnDefs.push({
                    'type': jQuery(this).attr('data-sorttype'),
                    'targets': i
                });
            }
            i++;
        });
        var page_length = get_this().get_cookie('page_length');
        if( page_length == null ){
            page_length = 10;
        }
        var dt = elem.DataTable({
            'order': [[ 0, 'desc' ]],
            columnDefs: columnDefs,
            'pageLength': page_length,
            'lengthMenu': [ 10, 25, 50, 75, 100, 250, 500, 1000, 2000, 5000 ],
            responsive: {
                details: {
                   display: jQuery.fn.dataTable.Responsive.display.childRowImmediate,
                   type: ''
               }
            },
            'language': {
                "decimal": "",
                "emptyTable": plugionl10n.no_data_in_table,
                "info": plugionl10n.showing_start_to_end,
                "infoEmpty": plugionl10n.showing_0,
                "infoFiltered": plugionl10n.filtered_from_total,
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": plugionl10n.show_menu_entries,
                "loadingRecords": plugionl10n.loading,
                "processing": plugionl10n.processing,
                "search": plugionl10n.search,
                "zeroRecords": plugionl10n.no_matching_records,
                "paginate": {
                    "first": plugionl10n.first,
                    "last": plugionl10n.last,
                    "next": plugionl10n.next,
                    "previous": plugionl10n.previous
                },
                "aria": {
                    "sortAscending": plugionl10n.activate_ascending,
                    "sortDescending": plugionl10n.activate_descending
                }
            }
        });
        elem.on( 'length.dt', function ( e, settings, len ) {
            get_this().set_cookie( 'page_length', len, 90 );
        });

        return dt;
    }

    initialize_property_form(action, table) {
        const get_this = () => { return this };
        var i;
        if (typeof tinymce !== 'undefined'){
            if( tinymce.editors.length > 0 ) {
                for( i = 0; i < tinymce.editors.length; i++ )  {
                    tinyMCE.editors[ i ].destroy();
                }
            }
        }
        if (action == 'add') {
            jQuery('.plugion_property_container_add_form').html(get_this().properties_default_content_add[table]);
            jQuery('.plugion_property_container_add_form').attr('data-table', table);
        }
        if (action == 'update') {
            jQuery('.plugion_property_container_update_form').html(get_this().properties_default_content_update[table]);
            jQuery('.plugion_property_container_update_form').attr('data-table', table);
        }
        jQuery('.plugion_properties_cancel').click(function() {
            jQuery('.plugion_property_container_' + action + '_form').hide('slide', {
                direction: 'right'
            }, 300);
        });
        // save button click
        jQuery('#plugion_properties_save').click(function() {
            get_this().save_properties(action, false);
        });
        // save and close button
        jQuery('#plugion_properties_save_close').click(function() {
            get_this().save_properties(action, true);
        });
        // delete button
        jQuery('#plugion_properties_delete').click(function() {
            jQuery('.plugion_delete_conirmation_holder').css('display', 'block');
        });
        // delete confirm button
        jQuery('#plugion_properties_delete_confirm').click(function() {
            jQuery(this).parent().css('display','none');
            get_this().delete_rows([jQuery('.plugion_property_container_update_form').attr('data-id')]);

        });
        // remove error class on focus
        jQuery('input').focus(function() {
            jQuery(this).removeClass('plugion_input_field_error');

        });
        jQuery('select').change(function() {

            jQuery(this).removeClass('plugion_input_field_error');
            jQuery(this).siblings('.nice-select').removeClass('plugion_input_field_error');
        });
        // action sensetive
        if (action == 'add') {
            get_this().set_properties_default();
            get_this().init_dependency();
            jQuery('#plugion_properties_discard').click(function() {
                get_this().initialize_property_form('add', table);
            });
        } else {
            if (action == 'update') {
                jQuery('.plugion_properties_title').html(plugionl10n.loading);
            }
            jQuery('#plugion_properties_discard').click(function() {
                get_this().set_properties(get_this().discard_buffer);
                get_this().init_dependency();
            });
        }

        // initialize accordion
        jQuery('.plugion_accordion').accordion({
            'transitionSpeed': 200,
            'singleOpen': false
        });
        jQuery(document).trigger('plugion_properties_form_initialized');

    }

    initialize_filter_form(table) {
        const get_this = () => { return this };
        jQuery('.plugion_filter_form').html(get_this().filter_form_content[table]);
        jQuery('.plugion_filter_form').attr('data-table', table);
        jQuery('.plugion_filter_cancel').click(function() {
            jQuery('.plugion_filter_form').hide('slide', {
                direction: 'right'
            }, 300);
        });

        // todo: field not initialized when peoperty form is opened for the second time
        if (get_this().filter_form_values[table] != undefined) {
            jQuery.each(get_this().filter_form_values[table], function(k, v) {

                var elem = jQuery("#" + v.name );
                get_this().set_field_value(elem, v.value);
            });
        } else{
            jQuery('.plugion_filter_input').each(function() {
                get_this().set_field_value( jQuery(this), '');
            });
        }
        jQuery('#plugion_filter_apply').click(function() {
            get_this().apply_filters(false);
        });
        jQuery('#plugion_filter_apply_close').click(function() {
            get_this().apply_filters(true);
        });
        jQuery(document).trigger('plugion_filter_form_initialized');
    }

    init_dependency() {
        const get_this = () => { return this };
        jQuery('.plugion_field_container').each(function() {
            var field_container = jQuery(this);
            if( typeof jQuery(this).attr('data-dependency') !== 'undefined' && jQuery(this).attr('data-dependency') != '[]') {
                var deps = jQuery.parseJSON(jQuery(this).attr('data-dependency'));
                jQuery.each(deps, function(k, v) {
                    var event_name = 'plugion_' + v[0] + '_change';
                    field_container.plugion_observe(event_name, function(e) {
                        get_this().apply_dependency(jQuery(this));
                    });
                });
            }
        });
        jQuery('.plugion_property_input').change(function() {
            var event_name = 'plugion_' + jQuery(this).attr('name') + '_change';
            jQuery(this).trigger(event_name);
        });
        jQuery('.plugion_property_input').trigger('change');
        // initialize discard changes button
        jQuery('.plugion_property_input, .plugion_input_date_range_start, .plugion_input_date_range_end').focusout(function() {
            jQuery('#plugion_properties_discard').fadeIn('slow', function() {
                jQuery(this).removeClass('plugion_hidden');
            });
        });
        jQuery('.plugion_input_radio_label').click(function() {
            jQuery('#plugion_properties_discard').fadeIn('slow', function() {
                jQuery(this).removeClass('plugion_hidden');
            });
        });
        jQuery(document).trigger('plugion_after_dependency_initialized' );
    }

    apply_dependency(elem) {
        const get_this = () => { return this };

        var deps = jQuery.parseJSON(elem.attr('data-dependency'));
        var passed = true;
        jQuery.each(deps, function(k, v) {
            var subject = jQuery(".plugion_input[name='" + v[0] + "']");

            if (subject.hasClass('plugion_input_radio')) {
                subject = jQuery(".plugion_input[name='" + v[0] + "']:checked");

            }
            // operand equals
            if (v[1] == '=') {
                if (subject.val() != v[2]) {
                    passed = false;
                }
            }
            // operand not equals
            if (v[1] == '!=') {
                if (subject.val() == v[2]) {
                    passed = false;
                }
            }
            // operand more than
            if (v[1] == '>') {
                if ( parseFloat( subject.val() ) <= parseFloat( v[2] ) ) {


                    passed = false;
                }
            }

            if (subject.hasClass('plugion_hidden')) {
                passed = false;
            }
        });
        if (!passed) {
            if (!elem.hasClass('plugion_hidden')) {
                get_this().hide_element(elem);
            }
        } else {
            if (elem.hasClass('plugion_hidden')) {
                get_this().show_element(elem);
            }
        }
    }

    hide_element(elem) {
        elem.addClass('plugion_hidden');
    }

    show_element(elem) {
        elem.removeClass('plugion_hidden');
    }

    apply_filters(close) {
        const get_this = () => { return this };
        var es = false;
        var filters = [];
        var error_labels = [];
        jQuery(document).trigger('plugion_before_apply_filters' );
        jQuery('.plugion_filter_input').each(function() {
            var value = get_this().get_field_value(jQuery(this));             
            if( !get_this().field_validators[jQuery(this).attr('data-validation')]( value, jQuery(this).attr('data-required'), jQuery(this))) {
                es = true;
                var name = jQuery(this).attr('id');
                error_labels.push(jQuery('label[for=' + name + ']').html());
                jQuery(this).addClass('plugion_input_field_error');
                jQuery(this).siblings('.chosen-container').find('.chosen-choices').addClass('plugion_input_field_error');
                jQuery(this).siblings('.nice-select').addClass('plugion_input_field_error');
            }
            if (value != '' && value != 'plugion_null') {
                var filter = {
                    name: jQuery(this).attr('id'),
                    value: value
                }
                filters.push(filter);
            }
        });
        if( es ){
            return;
        }
        jQuery('.plugion_input_field_error').removeClass('plugion_input_field_error');
        var table = jQuery('.plugion_filter_form').attr('data-table');
        var data = {
            'table': table,
            'filters': filters
        }
        get_this().freeze_form();
        jQuery.ajax(plugionl10n.rest_url + 'plugion/v1/get-rows', {
            method: "POST",
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', plugionl10n.nonce);
            },
            data: data,
            statusCode: {
                200: function(response) {
                    get_this().tables[table].destroy();
                    jQuery('.plugion_table[data-table="' + table + '"] > tbody').replaceWith(response);
                    jQuery(document).trigger('plugion_before_row_events', true );

                    get_this().set_rows_events();
                    get_this().tables[table] = get_this().init_datatable(jQuery('.plugion_table[data-table="' + table + '"]'));
                    get_this().unfreeze_form();
                    get_this().filter_form_values[table] = filters;
                    if (close) {
                        if (close == true) {
                            get_this().set_rows_events();
                            jQuery('.plugion_filter_form').hide('slide', {
                                direction: 'right'
                            }, 300);
                            return;
                        }
                    }
                },
                400: function(response) {
                    get_this().unfreeze_form();
                },
                404: function(response) {
                    get_this().unfreeze_form();
                },
                401: function(response) {
                    get_this().unfreeze_form();
                },
                403: function(response) {
                    get_this().unfreeze_form();
                    jQuery('.plugion_properties_title').html(plugionl10n.failed);
                    jQuery('.plugion_property_content_inner').html(plugionl10n.forbidden);
                    jQuery('.plugion_form_controls > input').prop('disabled', true);

                }
            }
        });

    }

    delete_rows(row_ids) {
        const get_this = () => { return this };
        var table = jQuery('.plugion_property_container_update_form').attr('data-table')
        var data = {
            'table': table,
            'row_ids': row_ids
        };
        get_this().freeze_form();
        jQuery.ajax(plugionl10n.rest_url + 'plugion/v1/delete-rows', {
            method: "POST",
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', plugionl10n.nonce);
            },
            data: data,
            statusCode: {
                200: function(response) {
                    jQuery('.plugion_property_container_update_form').hide('slide', {
                        direction: 'right'
                    }, 300);
                    jQuery.each(response, function(k, v) {
                        get_this().tables[table].row("tr[data-id='" + v + "']").remove().draw();
                    });
                    return;
                },
                403: function(response) {
                    get_this().unfreeze_form();
                    jQuery('.plugion_properties_title').html(plugionl10n.failed);
                    jQuery('.plugion_property_content_inner').html(plugionl10n.forbidden);
                    jQuery('.plugion_form_controls > input').prop('disabled', true);
                    return;
                }
            }
        });
    }


    save_properties(action, close) {
        const get_this = () => { return this };
        jQuery('#plugion_propery_info').html('');
        var es = false;
        var fields = [];
        jQuery(document).trigger('plugion_before_save_properties' );
        var error_labels = [];
        jQuery('.plugion_property_input').not('.nice-select').each(function() {
            if (!jQuery(this).closest('.plugion_field_container').hasClass('plugion_hidden')) {
                var name = jQuery(this).attr('id');
                var value = get_this().get_field_value(jQuery(this));

                if( !get_this().field_validators[jQuery(this).attr('data-validation')]( value, jQuery(this).attr('data-required'), jQuery(this))) {
                    es = true;
                    error_labels.push(jQuery('label[for=' + name + ']').html());
                    jQuery(this).addClass('plugion_input_field_error');
                    jQuery(this).siblings('.nice-select').addClass('plugion_input_field_error');
                } else {
                    if (value !== null) {
                        var field = {
                            name: jQuery(this).attr('name'),
                            value: value
                        }
                        fields.push(field);
                    }
                }
            }
        });
        if (es === true) {
            jQuery('#plugion_propery_info').html(plugionl10n.properties_error_list_title + ' ' + error_labels.join(', '));

            jQuery('.plugion_property_content_inner').scrollTop(0);

            return;
        }
        var table = jQuery('.plugion_property_container_' + action + '_form').attr('data-table');
        if (action == 'add') {
            var data = {
                'table': table,
                'fields': fields
            };
        } else {
            if (action == 'update') {
                var data = {
                    'table': table,
                    'fields': fields,
                    'row_id': jQuery('.plugion_property_container_update_form').attr('data-id')
                };
            }
        }
        get_this().freeze_form();
        jQuery.ajax(plugionl10n.rest_url + 'plugion/v1/save-properties', {
            method: "POST",
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', plugionl10n.nonce);
            },
            data: data,
            statusCode: {
                200: function(response) {
                    if (action == 'add') {

                        var node = get_this().tables[table].row.add(response.row_data).draw().node();

                        get_this().tables[table].order([
                            [0, 'desc']
                        ]).draw(true);


                        node.setAttribute('data-id', response.db_row_data.id);
                        if (response.row_options.canedit == true) {
                            node.classList.add('plugion_editable_row');
                        }
                        get_this().set_rows_events();
                        if (close == true) {
                            jQuery('.plugion_property_container_' + action + '_form').hide('slide', {
                                direction: 'right'
                            }, 300);
                            return;
                        }
                        jQuery('.plugion_property_container_add_form').hide();
                        jQuery('.plugion_property_container_add_form').html('');
                        jQuery('.plugion_property_container_update_form').show();
                        jQuery('.plugion_property_container_update_form').html(get_this().properties_default_content_update);
                        get_this().initialize_property_form('update', table);
                        get_this().discard_buffer = response.db_row_data;

                        jQuery('.plugion_property_container_update_form').attr('data-id', response.db_row_data.id);
                        get_this().set_properties(response.db_row_data);
                        get_this().init_dependency();
                        jQuery(document).trigger('plugion_after_row_added', response.db_row_data );
                    }
                    if (action == 'update') {

                        var node = get_this().tables[table].row("tr[data-id='" + response.db_row_data.id + "']").data(response.row_data).draw();

                        if (close == true) {
                            get_this().set_rows_events();
                            jQuery('.plugion_property_container_' + action + '_form').hide('slide', {
                                direction: 'right'
                            }, 300);
                            return;
                        }
                        get_this().discard_buffer = response.db_row_data;

                    }
                    get_this().unfreeze_form();
                    get_this().set_rows_events();

                    if (close) {
                        jQuery('.plugion_property_container_' + action).hide('slide', {
                            direction: 'right'
                        }, 300);
                        return;
                    }
                    if (null != response.db_row_data.name) {
                        var title = response.db_row_data.name;
                        if (title.length > 30) {
                            title = title.substring(0, 30) + '...';
                        }
                        jQuery('.plugion_properties_title').html(title);
                    } else {
                        jQuery('.plugion_properties_title').html('');
                    }
                },
                400: function(response) {
                    get_this().unfreeze_form();
                    jQuery('.plugion_properties_title').html(plugionl10n.failed);
                    jQuery('.plugion_property_content_inner').html(plugionl10n.bad_request);
                    jQuery('.plugion_form_controls > input').prop('disabled', true);
                },
                404: function(response) {
                    get_this().unfreeze_form();
                    jQuery('.plugion_properties_title').html(plugionl10n.failed);
                    jQuery('.plugion_property_content_inner').html(plugionl10n.element_not_found);
                    jQuery('.plugion_form_controls > input').prop('disabled', true);
                },
                401: function(response) {
                    get_this().unfreeze_form();
                    jQuery('.plugion_properties_title').html(plugionl10n.failed);
                    jQuery('.plugion_property_content_inner').html(plugionl10n.forbidden);
                    jQuery('.plugion_form_controls > input').prop('disabled', true);
                },
                403: function(response) {
                    get_this().unfreeze_form();
                    jQuery('.plugion_properties_title').html(plugionl10n.failed);
                    jQuery('.plugion_property_content_inner').html(plugionl10n.forbidden);
                    jQuery('.plugion_form_controls > input').prop('disabled', true);
                },
                422: function(response) {
                    jQuery('#plugion_propery_info').html(response.responseJSON[0][1]);
                    jQuery('.plugion_property_content_inner').scrollTop(0);
                    get_this().unfreeze_form();
                }
            }
        });
    }


    freeze_form() {
        jQuery('.plugion_line_loader').removeClass('plugion_hidden');
        jQuery('.plugion_overlay').removeClass('plugion_hidden');
        jQuery('.plugion_form_controls > input').prop('disabled', true);
    }

    unfreeze_form() {
        jQuery('.plugion_line_loader').addClass('plugion_hidden');
        jQuery('.plugion_overlay').addClass('plugion_hidden');
        jQuery('.plugion_form_controls > input').prop('disabled', false);
    }

    set_rows_events() {
        const get_this = () => { return this };
        jQuery('.plugion_table tr').unbind('click');
        jQuery('.plugion_table tr.plugion_editable_row').click(function() {
            var table = jQuery(this).closest('table').attr('data-table');
            jQuery('.plugion_property_container_add_form').hide();
            jQuery('.plugion_property_container_add_form').html('');
            jQuery('.plugion_property_container_update_form').hide();
            jQuery('.plugion_property_container_update_form').html('');
            jQuery('.plugion_filter_form').html('');
            jQuery('.plugion_filter_form').hide();
            jQuery('.plugion_property_container_update_form').show('slide', {
                direction: 'right'
            }, 300);
            get_this().initialize_property_form('update', table);
            get_this().freeze_form();
            var row_id = jQuery(this).attr('data-id');
            var data = {
                'table': jQuery('.plugion_property_container_update_form').attr('data-table'),
                'row_id': row_id
            };
            jQuery.ajax(plugionl10n.rest_url + 'plugion/v1/load-properties', {
                method: "POST",
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', plugionl10n.nonce);
                },
                data: data,
                statusCode: {
                    200: function(response) {
                        get_this().discard_buffer = response;
                        jQuery('.plugion_property_container_update_form').attr('data-id', row_id);
                        get_this().set_properties(response);
                        get_this().init_dependency();
                        get_this().unfreeze_form();

                    },
                    400: function(response) {
                        get_this().unfreeze_form();
                        jQuery('.plugion_properties_title').html(plugionl10n.failed);
                        jQuery('.plugion_property_content_inner').html(plugionl10n.bad_request);
                        jQuery('.plugion_form_controls > input').prop('disabled', true);
                    },
                    404: function(response) {
                        get_this().unfreeze_form();
                        jQuery('.plugion_properties_title').html(plugionl10n.failed);
                        jQuery('.plugion_property_content_inner').html(plugionl10n.element_not_found);
                        jQuery('.plugion_form_controls > input').prop('disabled', true);
                    },
                    401: function(response) {
                        get_this().unfreeze_form();
                        jQuery('.plugion_properties_title').html(plugionl10n.failed);
                        jQuery('.plugion_property_content_inner').html(plugionl10n.forbidden);
                        jQuery('.plugion_form_controls > input').prop('disabled', true);
                    },
                    403: function(response) {
                        get_this().unfreeze_form();
                        jQuery('.plugion_properties_title').html(plugionl10n.failed);
                        jQuery('.plugion_property_content_inner').html(plugionl10n.forbidden);
                        jQuery('.plugion_form_controls > input').prop('disabled', true);
                    }
                }
            });
        });

        jQuery('.plugion_block_icon').unbind('click');
        jQuery('.plugion_block_icon').click(function(event) {
        	jQuery(this).addClass('hide_element');
        	jQuery(this).siblings('.plugion_block_loader').removeClass('hide_element');

            var row_id = jQuery(this).parents('tr').attr('data-id');
            var table = jQuery(this).parents('table').attr('data-table');

            event.stopPropagation();
            get_this().duplicate_row( table, row_id);
        });

        jQuery(document).trigger('plugion_row_events_set');
    }

    set_properties(data) {
        const get_this = () => { return this };
        if (null == data) {
            jQuery('.plugion_properties_title').html(plugionl10n.failed);
            jQuery('.plugion_property_content_inner').html(plugionl10n.bad_request);
        }
        jQuery.each(data, function(k, v) {
            if (jQuery(".plugion_property_input[name='" + k + "']").length > 0) {
                var elem = jQuery(".plugion_property_input[name='" + k + "']");
                get_this().set_field_value(elem, v);
                if (k == 'name') {
                    var title = v;
                    if (v != null) {
                        if (title.length > 30) {
                            title = title.substring(0, 30) + '...';
                        }
                        jQuery('.plugion_properties_title').html(title);
                    } else {
                        jQuery('.plugion_properties_title').html('');
                    }
                }
            }
        });

        jQuery(document).trigger('plugion_properties_set' );

    }
    set_properties_default() {
        const get_this = () => { return this };
        jQuery('.plugion_property_input').each(function() {
            if (typeof(jQuery(this).attr('data-default')) != 'undefined') {
                 get_this().set_field_value(jQuery(this), jQuery(this).attr('data-default') );
            }
        });
    }
    set_field_value(elem, value) {
        const get_this = () => { return this };
        if (typeof elem.attr('data-setter') !== 'undefined') {
            get_this().field_setters[elem.attr('data-setter')](elem, value);
        } else {
            elem.val(value);
        }
        jQuery(document).trigger('plugion_input_set', [elem, value] );
    }

    get_field_value(elem) {
        const get_this = () => { return this };
        if (typeof elem.attr('data-getter') !== 'undefined') {

            var value = get_this().field_getters[elem.attr('data-getter')](elem);
        } else {
            var value = elem.val();
        }
        return value;
    }

    add_setter(name, func){
        const get_this = () => { return this };
        get_this().field_setters[name] = func;
    }

    add_getter(name, func){
        const get_this = () => { return this };
        get_this().field_getters[name] = func;
    }

    add_validator(name, func){
        const get_this = () => { return this };
        get_this().field_validators[name] = func;
    }

    validate_integer( val ) {
     	return /^\+?(0|[1-9]\d*)$/.test(val);
    }

    validate_float( val ) {
     	return /^(?:[1-9]\d*|0)?(?:\.\d+)?$/.test(val);
    }

    validate_string_length( val, min, max ) {
        if ( val.length < min || val.length > max ) {
    		return false;
    	} else {
    		return true;
    	}
    }

    validate_email( val ) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if ( val == '' || !re.test(val) ){
            return false;
        }
        return true;

    }

    validate_integer_range( val, min, max ) {
        const get_this = () => { return this };
        if( !get_this().validate_integer( val ) ){
            return false
        }
    	val = parseInt( val );
    	min = parseInt( min );
    	max = parseInt( max );

        if ( val < min || val > max ) {
    		return false;
    	} else {
    		return true;
    	}
    }

    valodate_phone( val ) {
    	var pattern = new RegExp(/^[(]{0,1}[0-9]{3}[)]{0,1}[-\s\.]{0,1}[0-9]{3}[-\s\.]{0,1}[0-9]{4}$/);
    	return pattern.test(val);
    }

    validate_price( val ) {
        const get_this = () => { return this };
    	if(  val == '' ){
    		return false;
    	}
    	if( wbkCheckInteger( val ) ){
    		if ( get_this().validate_integer_range( val, 0, MAX_SAFE_INTEGER ) ){
    			return true;
    		}
    	}
    	if( get_this().validate_float( val ) ){
    		if ( val >= 0 || val <= MAX_SAFE_INTEGER  ) {
    			return true;
    		}
    	}
    	return false;
    }

    set_cookie( name,value ,days ){
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    get_cookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    duplicate_row( table, row_id ) {
        const get_this = () => { return this };
        var data = {
            'table': table,
            'row_id': row_id
        };
        jQuery.ajax(plugionl10n.rest_url + 'plugion/v1/duplicate-row', {
            method: "POST",
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', plugionl10n.nonce);
            },
            data: data,
            statusCode: {
                200: function(response) {
                        var node = get_this().tables[table].row.add(response.row_data).draw().node();
                        get_this().tables[table].order([
                            [0, 'desc']
                        ]).draw(true);

                        node.setAttribute('data-id', response.db_row_data.id);
                        if (response.row_options.canedit == true) {
                            node.classList.add('plugion_editable_row');
                        }
                        get_this().set_rows_events();
                        jQuery(document).trigger('plugion_after_row_added', response.db_row_data );

        				jQuery('.plugion_block_loader').addClass('hide_element');
                        jQuery('.plugion_block_icon').removeClass('hide_element');

                },
                400: function(response) {

                },
                404: function(response) {

                },
                401: function(response) {

                },
                403: function(response) {

                },
                422: function(response) {

                }
            }
        });
    }
}
var plugion;
jQuery(function($) {
    plugion = new Plugion();
    jQuery(document).trigger('plugion_initialized');

});
