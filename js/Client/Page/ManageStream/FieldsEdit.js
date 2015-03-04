/**
 * Copyright 2015 Sky Wickenden
 *
 * This file is part of StreamBed.
 * An implementation of the Babbling Brook Protocol.
 *
 * StreamBed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * at your option any later version.
 *
 * StreamBed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with StreamBed.  If not, see <http://www.gnu.org/licenses/>
 *
 * @fileOverview Javascript used on the Stream update field iframes.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.ManageStream !== 'object') {
    BabblingBrook.Client.Page.ManageStream = {};
}

/**
 * Class for creating field objects for updating fields.
 * Note. This is not a class singleton.
 *       Instances are created for each field by BabblingBrook.Client.Page.ManageStream.FieldsSetup below.
 *
 * @param {object} jq_field JQuery object for the field container.
 * @param {number} field_id The unique server side identity for this field.
 *
 * @return {void}
 */
BabblingBrook.Client.Page.ManageStream.FieldUpdate = function(jq_field, field_id) {
    'use strict';

    /**
     * Turn the loading indicator into an error icon.
     *
     * @param {string} error_message Message to display in the icon title text.
     *
     * @return void
     */
    var showFieldLoadingError = function (error_message) {
        jQuery('.ajax-loading-container', jq_field).removeClass('hide');
        jQuery('.ajax-loading-container>div', jq_field)
            .attr('title', error_message)
            .addClass('ajax-loading-error')
            .removeClass('ajax-loading');
    };

    /**
     * Setup click event for adding a list item.
     */
    var setUpAddListItem = function () {
        jQuery('.add-list-item', jq_field).click(function (e) {
            jq_field.addClass('block-loading');
            jQuery('.add-list-item-error', jq_field).html('').addClass('hide');
            var add_list_item_url = jQuery('#field_url').val() + 'addlistitem';
            BabblingBrook.Library.post(
                add_list_item_url,
                {
                    field_id : field_id,
                    new_list_item : jQuery('.new-list-item', jq_field).val()
                },
                /**
                 * Callback for adding a new item to a list.
                 *
                 * @param {object} return_data The return data.
                 * @param {object} return_data.success Was the process successful.
                 * @param {object} return_data.errors A list of errors to display, indexed by error code.
                 *
                 * @return void
                 */
                function(return_data){
                    if(typeof return_data.success !== 'boolean') {
                        var error_message = 'Data returned from ' + add_list_item_url + ' is invalid.';
                        console.error(error_message);
                        showFieldLoadingError(error_message);
                        return;
                    }

                    // Show any errors
                    if(return_data.success === false) {
                        jQuery.each(return_data.errors, function(error_code, error) {
                            jQuery('.add-list-item-error', jq_field).append(error + '<br/>').removeClass('hide');
                        });
                        jQuery('.add-list-item-error', jq_field).removeClass('hide');
                        return;
                    }

                    // Append the new item
                    var jq_list = jQuery('.list-items', jq_field);
                    jq_list.append(
                        jQuery('<option></option>')
                            .val(return_data.list_id)
                            .html(jQuery('.new-list-item', jq_field).val())
                    );

                    // Sort the list box
                    jq_list.append(jq_list.find('option').sort(function(a, b){
                        if (a.text < b.text) {
                            return -1;
                        }
                        if (a.text === b.text) {
                            return 0;
                        }
                        return 1;
                    }));

                    jQuery('.new-list-item', jq_field).val('');
                    jq_field.removeClass('block-loading');
                }
            );
        });
    };

    /**
     * Setup click event for removing list items.
     */
    var setUpRemoveListItem = function () {
        jQuery('.remove-list-item', jq_field).click(function () {
            // If multiple are selected then send each as a seperate request.
            var size = jQuery('.list-items option:selected', jq_field).size();
            if (size === 0) {
                return;
            }
            var count = 0;
            var delete_list_item_url = jQuery('#field_url').val() + 'removelistitem';
            jq_field.addClass('block-loading');
            var exit_error = false;
            jQuery('.list-items option:selected', jq_field).each(function () {
                // Exit from the loop if there is an error.
                if(exit_error === true) {
                    return false;
                }

                var jq_option = jQuery(this);
                BabblingBrook.Library.post(
                    delete_list_item_url,
                    {
                        field_id : field_id,
                        list_item_to_delete : jq_option.text()
                    },
                    /**
                     * Callback for removing a list item.
                     *
                     * @param {object} return_data The return data.
                     * @param {object} return_data.success Was the process successful.
                     *
                     * @return void
                     */
                    function(return_data){
                        if(typeof return_data.success !== 'boolean') {
                            var error_message = 'Data returned from ' + delete_list_item_url + ' is invalid.';
                            console.error(error_message);
                            showFieldLoadingError(error_message);
                            exit_error = true;
                            return;
                        }
                        jq_option.remove();
                        count++;
                        if (count === size) {
                            jq_field.removeClass('block-loading');
                        }

                    }
                );
            });
        });
    };

    /**
     * Load the value if one is set.
     */
    var setValueDropDown = function (value_id) {
        var html;
        if (value_id > '') {
            switch (value_id) {
                case '13':
                    html = jQuery('.value-updown', jq_field).html();
                    break;
                case '14':
                    html = jQuery('.value-linear', jq_field).html();
                    break;
                case '15':
                    html = jQuery('.value-log', jq_field).html();
                    break;
                case '16':
                    html = jQuery('.value-text', jq_field).html();
                    break;
                case '24':
                    html = jQuery('.value-stars', jq_field).html();
                    break;
                case '46':
                    html = jQuery('.value-button', jq_field).html();
                    break;
                default:
                    html = 'Select type of value:';
                    break;
            }
            jQuery('select.value dt span', jq_field).html(html);
        }
    };


    /**
     * Ajax postback for updates to fields.
     *
     * @param {object} post_data The data being posted back. (Varies depending on field).
     *
     * @return void
     */
    var postBackField = function(post_data) {
        var update_url = jQuery('#field_url').val() + 'update';
        jq_field.addClass('block-loading');
        BabblingBrook.Library.post(
            update_url,
            post_data,
            /**
             * Callback for updating a field.
             *
             * @param {object} return_data The return data.
             * @param {object} return_data.success Was the process successful.
             * @param {object} return_data.errors A list of errors to display, indexed by error code.
             *
             * @return void
             */
            function(return_data){
                 jQuery('.error', jq_field).html('').addClass('hide');
                if(typeof return_data.success !== 'boolean') {
                    var error_message = 'Data returned from ' + update_url + ' is invalid.';
                    console.error(error_message);
                    showFieldLoadingError(error_message);
                    return;
                }

                if(return_data.success === false) {
                    jQuery.each(return_data.errors, function(error_code, error) {
                        error_code = error_code.replace(/_/g, '-');
                        jQuery('.' + error_code + '-error', jq_field).html(error).removeClass('hide');
                    });
                }
                jq_field.removeClass('block-loading');
            }
        );
    };

    var toggleTextFilterDisplay = function() {
        if (jQuery('.text-type', jq_field).val() === 'just_text') {
            jQuery('.text-filter', jq_field).prop('disabled', false);
        } else {
            jQuery('.text-filter', jq_field).prop('disabled', true);
        }
    }

    /**
     * Setup process for textbox fields.
     *
     * @return void
     */
    var setupTextField = function() {
        // Show/hide regex fields in a text field.
        jQuery('.text-filter').change(function (e) {
            if (e.target.value === 'more') {
                jQuery('.regex-rows', jq_field).removeClass('hide');
            } else {
                jQuery('.regex-rows', jq_field).addClass('hide');
                jQuery('.text-regex-error', jq_field).val('');
                jQuery('.text-regex', jq_field).val('');
            }
        });

        toggleTextFilterDisplay();
        jQuery('.text-type', jq_field).change(toggleTextFilterDisplay);

        // Send changes via ajax.
        jQuery(
            '.field-label, .text-max, .text-required, .text-filter, .text-regex, .text-regex-error, .text-type',
            jq_field
        ).change(function (e) {
            var post_data = {
                type : 'textbox',
                field_id : field_id,
                label : jQuery('.field-label', jq_field).val(),
                text_type : jQuery('.text-type', jq_field).val(),
                max_size : jQuery('.text-max', jq_field).val(),
                required : jQuery('.text-required', jq_field).is(':checked'),
                filter : jQuery('.text-filter', jq_field).val(),
                regex : jQuery('.text-regex', jq_field).val(),
                regex_error : jQuery('.text-regex-error', jq_field).val()
            };
            postBackField(post_data);
        });
    };

    /**
     * Setup process for link fields.
     *
     * @return void
     */
    var setupLinkField = function() {

        // Send changes via ajax.
        jQuery('.field-label, .link-required', jq_field)
            .change(function (e) {
                var post_data = {
                    type : 'link',
                    field_id : field_id,
                    label : jQuery('.field-label', jq_field).val(),
                    required : jQuery('.link-required', jq_field).val()
                };
                postBackField(post_data);
            });
    };


    /**
     * Setup process for checkbox fields.
     *
     * @return void
     */
    var setupCheckboxField = function() {

        // Send changes via ajax.
        jQuery('.field-label, .checkbox-default', jq_field)
            .change(function () {
                var post_data = {
                    type : 'checkbox',
                    field_id : field_id,
                    label : jQuery('.field-label', jq_field).val(),
                    checkbox_default : jQuery('.checkbox-default', jq_field).val()
                };
                postBackField(post_data);
            });
    };

    /**
     * Setup process for list fields.
     *
     * @return void
     */
    var setupListField = function() {
        setUpAddListItem();
        setUpRemoveListItem();
        // Send changes via ajax.
        jQuery('.field-label, .list-select-qty-min, .list-select-qty-max', jq_field)
            .change(function () {
                var post_data = {
                    type : 'list',
                    field_id : field_id,
                    label : jQuery('.field-label', jq_field).val(),
                    select_qty_min : jQuery('.list-select-qty-min', jq_field).val(),
                    select_qty_max : jQuery('.list-select-qty-max', jq_field).val()
                };
                postBackField(post_data);
            });
    };

    /**
     * Setup process for openlist fields.
     *
     * @return void
     */
    var setupOpenListField = function() {

        // Send changes via ajax.
        jQuery('.field-label, .list-select-qty-min, .list-select-qty-max', jq_field)
            .change(function (e) {
                var post_data = {
                    type : 'openlist',
                    field_id : field_id,
                    label : jQuery('.field-label', jq_field).val(),
                    select_qty_min : jQuery('.list-select-qty-min', jq_field).val(),
                    select_qty_max : jQuery('.list-select-qty-max', jq_field).val()
                };
                postBackField(post_data);
            });
    };

    /**
     * Calls an ajax postback when the type is changed and displays the new types field.
     *
     */
    var setupTypeChange = function() {
        jQuery('select.type', jq_field).change(function () {
            jq_field.addClass('block-loading');
            var type_changed_url = jQuery('#field_url').val() + 'typechanged';
            var new_type = jQuery('select.type', jq_field).val();
            BabblingBrook.Library.post(
                type_changed_url,
                {
                    field_id : field_id,
                    type : new_type
                },
                /**
                 * Callback for changing the type of a field.
                 *
                 * @param {object} return_data The return data.
                 * @param {object} return_data.success Was the process successful.
                 *
                 * @return void
                 */
                function(return_data){
                    if(typeof return_data.success !== 'boolean') {
                        var error_message = 'Data returned from ' + type_changed_url + ' is invalid.';
                        console.error(error_message);
                        showFieldLoadingError(error_message);
                        return;
                    }

                    jQuery('.inner-field-container', jq_field).html(return_data.html);
                    jQuery('.field-type', jq_field).val(new_type)
                    setup();
                    jq_field.removeClass('block-loading');
                }
            );
        });
    };

    /**
     * Setup click event for removing list items.
     */
    var setUpRemoveValueListItem = function () {
        jQuery('.remove-value-list-item', jq_field).click(function () {
            // If multiple are selected then send each as a seperate request.
            var size = jQuery('.value-list-items option:selected', jq_field).size();
            if (size === 0) {
                return;
            }
            var count = 0;
            var delete_list_item_url = jQuery('#field_url').val() + 'removevaluelistitem';
            jq_field.addClass('block-loading');
            var exit_error = false;
            jQuery('.value-list-items option:selected', jq_field).each(function () {
                // Exit from the loop if there is an error.
                if(exit_error === true) {
                    return false;
                }

                var jq_option = jQuery(this);
                BabblingBrook.Library.post(
                    delete_list_item_url,
                    {
                        field_id : field_id,
                        take_value_list_id : jq_option.val()
                    },
                    /**
                     * Callback for removing a list item.
                     *
                     * @param {object} return_data The return data.
                     * @param {object} return_data.success Was the process successful.
                     *
                     * @return void
                     */
                    function(return_data){
                        if(typeof return_data.success !== 'boolean') {
                            var error_message = 'Data returned from ' + delete_list_item_url + ' is invalid.';
                            console.error(error_message);
                            showFieldLoadingError(error_message);
                            exit_error = true;
                            return;
                        }
                        jq_option.remove();
                        count++;
                        if (count === size) {
                            jq_field.removeClass('block-loading');
                        }
                    }
                );
            });
        });
    };

    /**
     * Setup click event for adding a list item to a value list.
     */
    var setUpAddValueListItem = function () {
        jQuery('.add-value-list-item', jq_field).click(function () {
            if (jQuery('.new-value-list-item', jq_field).val() === '') {
                return;
            }

            jq_field.addClass('block-loading');
            jQuery('.add-value-list-item-error', jq_field).html('').addClass('hide');
            var add_value_list_item_url = jQuery('#field_url').val() + 'addvaluelistitem';
            BabblingBrook.Library.post(
                add_value_list_item_url,
                {
                    field_id : field_id,
                    new_value_list_item : jQuery('.new-value-list-item', jq_field).val()
                },
                /**
                 * Callback for adding a new item to a list.
                 *
                 * @param {object} return_data The return data.
                 * @param {object} return_data.success Was the process successful.
                 * @param {object} return_data.errors A list of errors to display, indexed by error code.
                 *
                 * @return void
                 */
                function(return_data){
                    if(typeof return_data.success !== 'boolean') {
                        var error_message = 'Data returned from ' + add_value_list_item_url + ' is invalid.';
                        console.error(error_message);
                        showFieldLoadingError(error_message);
                        return;
                    }

                    // Show any errors
                    if(return_data.success === false) {
                        jQuery.each(return_data.errors, function(error_code, error) {
                            jQuery('.add-value-list-item-error', jq_field).append(error + '<br/>').removeClass('hide');
                        });
                        jQuery('.add-value-list-item-error', jq_field).removeClass('hide');
                        return;
                    }

                    // Append the new item
                    var jq_list = jQuery('.value-list-items', jq_field);
                    jq_list.append(
                        jQuery('<option></option>')
                            .val(return_data.take_value_list_id)
                            .html(return_data.value + ' ' + jQuery('.new-value-list-item', jq_field).val())
                    );

                    jQuery('.new-value-list-item', jq_field).val('');
                    jq_field.removeClass('block-loading');
                }
            );
        });
    };


    /**
     *    Options to show/hide if the user sets the value.
     */
    var valueUser = function () {
        jQuery('.value-min-row', jq_field).addClass('hide');
        jQuery('.value-max-row', jq_field).addClass('hide');
        jQuery('.value-rhythm-row', jq_field).addClass('hide');
        jQuery('.value-min', jq_field).val('');
        jQuery('.value-max', jq_field).val('');
        jQuery('.value-rhythm', jq_field).val('');
        jQuery('.value-list-remove-item-row', jq_field).addClass('hide');
        jQuery('.value-list-add-item-row', jq_field).addClass('hide');
    };

    /**
     * Options to show/hide if the value is set here numerically.
     */
    var valueMinMaxHere = function () {
        jQuery('.value-max-row', jq_field).removeClass('hide');

        // Don't show minimum for stars and button
        var field_value = jQuery('select.value', jq_field).attr('data-value-id');
        if (field_value !== '24' && field_value !== '46') {
            jQuery('.value-min-row', jq_field).removeClass('hide');
        } else {
            jQuery('.value-min-row', jq_field).addClass('hide');
            jQuery('.value-min-row input.value-min', jq_field).val(0);
        }
        jQuery('.value-rhythm-row', jq_field).addClass('hide');
        jQuery('.value-rhythm', jq_field).val('');
        jQuery('.value-list-remove-item-row', jq_field).addClass('hide');
        jQuery('.value-list-add-item-row', jq_field).addClass('hide');
    };

    /**
     * Options to show/hide if the value is set here Rhythmically.
     *
     * @return void
     */
    var valueRhythmHere = function () {
        jQuery('.value-min-row', jq_field).addClass('hide');
        jQuery('.value-max-row', jq_field).addClass('hide');
        jQuery('.value-rhythm-row', jq_field).removeClass('hide');
        jQuery('.value-min', jq_field).val('');
        jQuery('.value-max', jq_field).val('');
        jQuery('.value-list-remove-item-row', jq_field).addClass('hide');
        jQuery('.value-list-add-item-row', jq_field).addClass('hide');
    };


    /**
     * Options to show/hide if the value is set to be a list.
     *
     * @return void
     */
    var valueList = function () {
        jQuery('.value-min-row', jq_field).addClass('hide');
        jQuery('.value-max-row', jq_field).addClass('hide');
        jQuery('.value-rhythm-row', jq_field).addClass('hide');
        jQuery('.value-list-remove-item-row', jq_field).removeClass('hide');
        jQuery('.value-list-add-item-row', jq_field).removeClass('hide');
        setUpAddValueListItem();
        setUpRemoveValueListItem();
    };

    /**
     * Setup the max and min fields depending on content of the value options drop down.
     *
     * @return void
     */
    var valueOptions = function () {
        // Special case if the value type is hidden - the values are caculated from the list length.
        if (jQuery('.field-row-value-options', jq_field).hasClass('hide') === true) {
            valueList();
            return;
        }

        var value_option_id = jQuery('.value-options option:selected', jq_field).val();
        switch (value_option_id) {
            case '17':
                valueUser();
                break;
            case '18':
                valueMinMaxHere();
                break;
            case '19':
                valueUser();
                break;
            case '20':
                valueRhythmHere();
                break;
            case '21':
                valueUser();
                break;
        }
    };

    /**
     * Show/hide items on the value options menu.
     *
     * @return void
     */
    var setUpValueOptionsDropdown = function () {
        jQuery('.value-options option', jq_field).removeClass('hide');
        var field_value = jQuery('select.value option:selected', jq_field).attr('data-value-id');
        var option_id = jQuery('.value-options option:selected', jq_field).attr('value');

        // linear, logarithmic stars and buttons can not have a user set value.
        if (field_value === '148') {    // list value
            jQuery('.value-options option[value=18]', jq_field).attr('selected', 'selected');
            jQuery('.field-row-value-options', jq_field).addClass('hide');

        } else if (field_value === '14' || field_value === '15' || field_value === '24' || field_value === '46') {
            jQuery('.field-row-value-options', jq_field).removeClass('hide');
            jQuery('.value-options option[value=17]', jq_field).remove();
            if (option_id === '17') {
                jQuery('.value-options', jq_field).val('18');
            }

        } else {    // Arrows
            jQuery('.field-row-value-options', jq_field).removeClass('hide');
            var jq_option = jQuery('.value-options option[value=17]', jq_field);
            if (jq_option.length === 0) {
                var jq_any_value = jQuery('#stream_fileds_edit_any_value_template>option').clone();
                jQuery('.value-options', jq_field).prepend(jq_any_value);
            }
        }

        jQuery('.value-options', jq_field).change(function (e) {
            valueOptions();

        });
    };

    /**
     * Setup the drop down selector that is used to decide who can take a value field.
     *
     * @return void
     */
    var setupWhoCanTake = function () {
        // The primary take field always defaults to anyone
        var display_order = jq_field.index() + 1;
        if (display_order === 2) {
            jQuery('.value-who-can-edit-row', jq_field).addClass('hide');
        }

        jQuery('.who-can-take', jq_field).change(function () {
            jq_field.addClass('block-loading');
            var value_type_changed_url = jQuery('#field_url').val() + 'whocantakechanged';
            var who_can_take = this.value;
            BabblingBrook.Library.post(
                value_type_changed_url,
                {
                    field_id : field_id,
                    who_can_take : who_can_take
                },
                /**
                 * Callback for changing the who_can_take value
                 *
                 * @param {object} return_data The return data.
                 * @param {object} return_data.success Was the process successful.
                 *
                 * @return void
                 */
                function(return_data){
                    if(typeof return_data.success !== 'boolean') {
                        var error_message = 'Data returned from ' + value_type_changed_url + ' is invalid.';
                        console.error(error_message);
                        showFieldLoadingError(error_message);
                        return;
                    }
                    jq_field.removeClass('block-loading');
                }
            );
        });
    };

    /**
     * Setup the drop down selector that is used to select the type of value.
     *
     * @return void
     */
    var setupValueSelector = function() {
        // If value is set then select it in the drop down.
        var value_id = jQuery('select.value option:selected', jq_field).attr('data-value-id');
        if (value_id > '') {
            setValueDropDown(value_id);
        }

        setupWhoCanTake();

        // Click function to select a new value
        jQuery('select.value', jq_field).change(function () {
            jq_field.addClass('block-loading');
            var value_type_changed_url = jQuery('#field_url').val() + 'valuetypechanged';
            var new_value_id = jQuery('option:selected', this).attr('data-value-id');
            BabblingBrook.Library.post(
                value_type_changed_url,
                {
                    field_id : field_id,
                    value_id : new_value_id
                },
                /**
                 * Callback for changing the type of a value.
                 *
                 * @param {object} return_data The return data.
                 * @param {object} return_data.success Was the process successful.
                 *
                 * @return void
                 */
                function(return_data){
                    if(typeof return_data.success !== 'boolean') {
                        var error_message = 'Data returned from ' + value_type_changed_url + ' is invalid.';
                        console.error(error_message);
                        showFieldLoadingError(error_message);
                        return;
                    }
                    jQuery('select.value', jq_field).attr('data-value-id', new_value_id);
                    setUpValueOptionsDropdown();
                    valueOptions();
                    jq_field.removeClass('block-loading');
                }
            );
        });
    };

    /**
     * Setup a value field.
     */
    var setupValueField = function() {

        setupValueSelector();

        // Set up value dropdown actions
        setUpValueOptionsDropdown();

        // Initial setup of value options
        valueOptions();

        // Send changes via ajax.
        jQuery('.field-label, .value-options, .value-rhythm, .value-max, .value-min', jq_field)
            .change(function () {
                var post_data = {
                    type : 'value',
                    field_id : field_id,
                    label : jQuery('.field-label', jq_field).val(),
                    value_type : jQuery('select.value', jq_field).attr('data-value-id'),
                    value_option : jQuery('.value-options', jq_field).val(),
                    value_min : jQuery('.value-min', jq_field).val(),
                    value_max : jQuery('.value-max', jq_field).val(),
                    value_rhythm : jQuery('.value-rhythm', jq_field).val()
                };
                postBackField(post_data);
            });
    };


    /**
    * Setup a new field object.
    *
    * @return void
    */
    var setup = function () {
        // If the label is blank on loading then take the one from title.
        if (jQuery('.field-label', jq_field).val() === '') {
            var field_value = jQuery('h4#stream_field_' + field_id).text().trim();
            jQuery('.field-label', jq_field).val(field_value);
        }

        // Update the main pages title for this field when it changes.
        jQuery('.field-label', jq_field).change(function (e) {
            var title = jQuery(this).val();
            // Don't change if the title is empty as it causes the h4 to shrink to 1 pixel.
            if(title === '') {
                return;
            }
            var jq_header = jQuery('h4#stream_field_' + field_id);
            var heading = jq_header.html();
            var heading_span = heading.substr(0, heading.lastIndexOf('</span>') + 7);
            jq_header.html(heading_span + title);
        });

        /**  Type Dropdown **/
        setupTypeChange();

        // Setup this particular type of field
        var type = jQuery('.field-type', jq_field).val();
        switch (type) {
            case 'textbox':
                setupTextField();
                break;

            case 'link':
                setupLinkField();
                break;

            case 'checkbox':
                setupCheckboxField();
                break;

            case 'list':
                setupListField();
                break;

            case 'openlist':
                setupOpenListField();
                break;

            case 'value':
                setupValueField();
                break;
        }

        // Turn off ajax loading
        jq_field.removeClass('block-loading');

    };
    setup();

};
/**
 * Static method for generic code that applies to events for all instances of fields.
 *
 * @return void
 */
BabblingBrook.Client.Page.ManageStream.FieldUpdate.globalFieldEvents = function () {
    'use strict';

    // Global click function.
    jQuery(document).on('click', function (e) {
        var $clicked = jQuery(e.target);

        // Detect if a type change has been selected.
        if ($clicked.parents().hasClass('type') === true && $clicked.is('li') === false) {
            jQuery('.type dd ul').toggleClass('hide');
        } else {
             jQuery('.type dd ul').addClass('hide');
        }

        // Detect if a value change has been abandoned.
        if ($clicked.parents().hasClass('value-dd') === true && $clicked.is('li') === false) {
            jQuery('select.value dd ul').toggleClass('hide');
        } else {
            jQuery('select.value dd ul').addClass('hide');
        }
    });

    // Display 'more' in text field filter menu as bold.
    jQuery('.field-filter option[value="more"]').css('font-weight', 'bold');
};


BabblingBrook.Client.Page.ManageStream.FieldsEdit = (function() {
    'use strict';

    var freeze_field_buttons = false;


    /*
     * Attatch an accordian and sort to the fields.
     *
     * @param {number} [active=0] The display order of the accordian element to open after attatching the new field.
     */
    var attachFieldAccordian = function (active) {
        if (typeof active === 'undefined') {
            active = 0;
        }

        // Prevents animation effects effecting accordian sorting.
        var stop = false;
        jQuery('#stream_fields h3').click(function (event) {
            if (stop) {
                event.stopImmediatePropagation();
                event.preventDefault();
                stop = false;
            }
        });
        // Attatch acordian and sort.
        // Note, currently using jQuery-ui1.9. This settting changes in 1.90.
        // see http://forum.jquery.com/topic/ui-accordion-heightstyle-content-not-working
        jQuery('#stream_fields').accordion({
            header: '.stream_field_header',
            autoHeight: false,
            heightStyle: "content"
        });
    };


    /**
     * Resort the field up/down icons.
     */
    var resortUpDownGraphics = function () {
        jQuery('#stream_fields .sort-up img').removeClass('hide');
        jQuery('#stream_fields .sort-down img').removeClass('hide');
        jQuery('#stream_fields .sort-up:nth(2) img').addClass('hide');
        jQuery('#stream_fields .sort-down:last img').addClass('hide');
    };

    /**
     * Swap two DOM elements, maintaining their event handlers etc (No jquery option for doing this with IDs).
     * @param {object} a The first node.
     * @param {object} b The second node.
     */
    var swapNodes = function (a, b) {
        var aparent = a.parentNode;
        var asibling = a.nextSibling === b ? a : a.nextSibling;
        b.parentNode.insertBefore(a, b);
        aparent.insertBefore(b, asibling);
    };

    /**
     * Moves the display order of a field and swaps them around in the display.
     * @param {string} action Direction to move. 'moveup' or 'movedown'.
     * @param {string} move_id The id of the field being moved.
     */
    var moveDisplayOrder = function (action, move_id) {
        var jq_container = jQuery('#otf_container_' + move_id);
        jq_container.addClass('block-loading');
        BabblingBrook.Library.post(
            jQuery('#field_url').val() + action,
            {
                field_id : move_id,
            },
            /**
             * Success callback for moving an post field.
             *
             * @param {object} callback_data Any data sent back from the request.
             * @param {number} callback_data.field_id The id of the field that has been moved. (Indicates its location).
             *
             * @return void
             */
            function (callback_data) {
                swapNodes(
                    document.getElementById('otf_container_' + move_id),
                    document.getElementById('otf_container_' + callback_data.switch_id)
                );
                resortUpDownGraphics();
                jQuery('#otf_container_' + move_id + ' .ajax-loading-container').addClass('hidden');
                jq_container.removeClass('block-loading');
                freeze_field_buttons = false;
            }
        );
    };

    /**
     * Attatch events to the field buttons.
     */
    var attatchFieldButtons = function () {
        var value;
        // Move display order up
        jQuery('.sort-up img').click(function () {
            if (freeze_field_buttons === true) {
                return;
            }
            freeze_field_buttons = true;
            jQuery(this).parent().parent().find('.ajax-loading').removeClass('hide');
            value = jQuery(this).parent().find('.value').text();
            moveDisplayOrder('moveup', value);
        });

        // Move display order down
        jQuery('.sort-down img').click(function () {
            if (freeze_field_buttons === true) {
                return;
            }
            freeze_field_buttons = true;
            jQuery(this).parent().parent().find('.ajax-loading').removeClass('hide');
            value = jQuery(this).parent().find('.value').text();
            moveDisplayOrder('movedown', value);
        });

        // Delete field
        jQuery('.delete img').click(function () {
            if (freeze_field_buttons === true) {
                return;
            }
            freeze_field_buttons = true;
            var jq_button = jQuery(this);
            var field_id = jq_button.parent().find('.value').text();
            var jq_container = jQuery('#otf_container_' + field_id);
            jq_container.addClass('block-loading');
            BabblingBrook.Library.post(
                jQuery('#field_url').val() + 'delete',
                {
                    field_id : field_id,
                },
                /**
                 * Success callback for moving an post field.
                 *
                 * @param {object} callback_data Any data sent back from the request.
                 * @param {number} id The id of the field that has been deleted. (Indicates its location).
                 *
                 * @return void
                 */
                function (callback_data) {
                    resortUpDownGraphics();
                    jq_container.removeClass('block-loading');
                    freeze_field_buttons = false;
                    jq_container.remove();
                }
            );

        });
    };


    /**
     * setup the 'add new field' button.
     */
    var setupAddNewFieldButton = function () {
        jQuery('#add_new_field').click(function () {
            // Insert a new field into the DB and return its ID
            BabblingBrook.Library.post(
                jQuery('#field_url').val() + 'create',
                {
                    stream_extra_id : jQuery('#StreamExtra_stream_extra_id').val()
                },
                function (data) {
                    if (data !== null && typeof data === 'object') {
                        jQuery('#stream_fields').accordion('destroy');
                        jQuery('#stream_fields h3').unbind('click');
                        jQuery('#stream_fields').append(data.html);
                        var field_count = jQuery('#stream_fields .field-container').length;

                        // Set the field in the accordian.
                        attachFieldAccordian(field_count - 1);
                        resortUpDownGraphics();
                        attatchFieldButtons();

                        // Initialise the field
                        var jq_field = jQuery('#otf_container_' + data.id);
                        var new_field = new BabblingBrook.Client.Page.ManageStream.FieldUpdate(jq_field, data.id);
                    }
                },
                function (request, error) {
                    console.error(error);
                }
            );
        });
    };

    /**
     * Unbind the event handlers attatched to field buttons.
     */
    var unbindFieldButtons = function () {
        jQuery('.sort-up img').unbind('click');
        jQuery('.sort-down img').unbind('click');
        jQuery('.delete img').unbind('click');
    };

    /**
     * Deals with errors in DeleteAllPosts process.
     *
     * @param {string} error The error message.
     *
     * @returns {void}
     */
    var onDeleteAllPostsError = function (error) {
        throw error;
    };

    /**
     * Sends a request to the server to delete all posts inthis stream (Only posts by the stream owner).
     *
     * @returns {void}
     */
    var onDeleteAllPostsClicked = function () {

        var url = BabblingBrook.Library.changeUrlAction(window.location.pathname, 'deleteallownerposts');
        BabblingBrook.Library.post(
            url,
            {},
            /**
             * Success callback for deleting all owner posts
             *
             * @param {object} response_data Any data sent back from the request.
             * @param {boolean} response_data.success Was the request successful.
             * @param {string} [response_data.error] An error message. Onpresent if success is false.
             *
             * @return void
             */
            function (response_data) {
                if(response_data.success === false) {
                    onDeleteAllPostsError(response_data.error);
                } else {
                    BabblingBrook.Client.Core.Ajaxurl.redirect(window.location.pathname);
                }
            },
            onDeleteAllPostsError.bind('There was a server error when requesting to delete all owner posts.')
        );
    };


    return {

        /**
         * Setup each field.
         *
         * @return void
         * @note This is clunky. It is creating a large object for each field. There may be a better way
         *   of doing this where most of the code is shared.
         */
        construct : function () {
            attachFieldAccordian();
            setupAddNewFieldButton();
            attatchFieldButtons();
            resortUpDownGraphics();

            jQuery('#delete_all_posts').click(onDeleteAllPostsClicked);

            jQuery('.field-container').each(function(i){
                var jq_field = jQuery(this);
                var field_id = jq_field.attr('id');
                field_id = field_id.substr(field_id.lastIndexOf('_') + 1);
                var new_field_update = new BabblingBrook.Client.Page.ManageStream.FieldUpdate(jq_field, field_id);
            });

            BabblingBrook.Client.Page.ManageStream.FieldUpdate.globalFieldEvents();
        }


    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageStream.FieldsEdit.construct();
});