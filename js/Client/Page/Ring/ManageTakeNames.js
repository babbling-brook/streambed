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
 * @fileOverview Code relating to the managing of ring take names.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Ring !== 'object') {
    BabblingBrook.Client.Page.Ring = {};
}


/**
 *  @namespace Scripts associated with the page for managing a rings take names.
 */
BabblingBrook.Client.Page.Ring.ManageTakeNames = (function () {
    'use strict';

    var jq_processing;

    // Predfine functions due to jslint complaining about valid circular function calls.
    var updateClickEvents, addToEditList, updateEditList, formClickEvents, clearForm, deleteFromEditList;

    /**
     * Recreate click events on edit list.
     */
    updateClickEvents = function () {
        jQuery('#edit_take_names li .edit-take-name')
            .unbind('click')
            .click(function () {
                if (jQuery('#take_name').val() !== '' || jQuery('#amount').val() !== '') {
                    var clear = confirm('This will clear the fields. Are you sure?');
                    if (!clear) {
                        return;
                    }
                }
                var jq_this = jQuery(this);
                jQuery('#create').val('Update');
                jQuery('#delete').removeClass('hide');
                jQuery('#take_name').val(jq_this.text());
                jQuery('#amount').val(jq_this.parent().find('.edit-amount').text());
                jQuery('#stream').val(jq_this.parent().find('.edit-stream').text());
                jQuery('#ring_take_name_id').val(jq_this.parent().find('.edit-ring-take-name-id').text());
                formClickEvents();
                return false;
            });
    };

    /**
     * Add an item to the edit list.
     * @param {string} ring_take_name_id Passed in as it may not be the vlaue on the form. ie after an insert.
     */
    addToEditList = function (ring_take_name_id) {
        var jq_line = jQuery('#take_name_line_template>li').clone();
        jQuery('.edit-ring-take-name-id', jq_line).text(ring_take_name_id);
        jQuery('.edit-stream', jq_line).text(jQuery('#stream').val());
        jQuery('.edit-amount', jq_line).text(jQuery('#amount').val());
        jQuery('.edit-take-name', jq_line).text(jQuery('#take_name').val());
        jQuery('#edit_take_names ul').prepend(jq_line);
        updateClickEvents();
    };

    /**
     * Update a value in the edit list.
     * @param {string} ring_take_name_id
     */
    updateEditList = function (ring_take_name_id) {
        var jq_row = jQuery('#edit_take_names ul').find('.edit-ring-take-name-id:contains("' + ring_take_name_id + '")')
            .parent();
        if (jq_row.length > 0) {
            jQuery('.edit-stream', jq_row).html(jQuery('#stream').val());
            jQuery('.edit-amount', jq_row).html(jQuery('#amount').val());
            jQuery('.edit-take-name', jq_row).html(jQuery('#take_name').val());
        }
    };

    /**
     * Click events for the form. Need to be rebound when buttons shown/hidden.
     */
    formClickEvents = function () {

        jQuery('#create').unbind('click');
        jQuery('#clear').unbind('click');
        jQuery('#delete').unbind('click');

        /**
         * Click event for submiting take names.
         */
        jQuery('#create').click(function () {
            jq_processing.addClass('block-loading');
            // This is a client request as take names can only be managed on the ring home site.
            BabblingBrook.Library.post(
                'inserttakename',
                {
                    take_name : jQuery('#take_name').val(),
                    amount : jQuery('#amount').val(),
                    ring_take_name_id : jQuery('#ring_take_name_id').val(),
                    stream : jQuery('#stream').val()
                },
                function (data) {
                    jQuery('#take_name_error').html('');
                    jQuery('#amount_error').html('');
                    jQuery('#stream_error').html('');
                    if (data.errors !== false) {
                        if (typeof data.errors.name !== 'undefined') {
                            jQuery('#take_name_error').html(data.errors.name);
                        }
                        if (typeof data.errors.amount !== 'undefined') {
                            jQuery('#amount_error').html(data.errors.amount);
                        }
                        if (typeof data.errors.stream_version !== 'undefined') {
                            jQuery('#stream_error').html(data.errors.stream_version);
                        }
                        if (typeof data.errors.stream !== 'undefined') {
                            jQuery('#stream_error').html(data.errors.stream);
                        }
                        jq_processing.removeClass('block-loading');
                    } else {
                        if (typeof data.ring_take_name_id === 'undefined') {
                            console.error('../ring/inserttakename has not returned a value for ring_take_name_id');
                        }
                        if (jQuery('#create').val() === 'Create') {
                            addToEditList(data.ring_take_name_id);
                        } else {
                            updateEditList(data.ring_take_name_id);
                        }
                        clearForm();
                    }
                }
            );
            return false;
        });

        /**
         * Clears the form and resets hidden elements.
         */
        jQuery('#clear').click(function () {
            clearForm();
            return false;
        });

        /**
         * Deletes a take name from its ID.
         */
        jQuery('#delete').click(function () {
            var delete_take_name = confirm('Are you sure you want to delete this take name?');
            if (delete_take_name) {
                jq_processing.addClass('block-loading');
                var ring_take_name_id = jQuery('#ring_take_name_id').val();
                // This is a client request as take names can only be managed on the ring home site.
                BabblingBrook.Library.post(
                    'deletetakename',
                    {
                        ring_take_name_id : ring_take_name_id
                    },
                    function (data) {
                        if (typeof data.success !== 'undefined' && data.success === true) {
                            clearForm();
                            deleteFromEditList(ring_take_name_id);
                        } else {
                            console.error(
                                '../ring/deletetakename has not deleted ring_take_name_id : ' + ring_take_name_id
                            );
                        }
                        jq_processing.removeClass('block-loading');
                    }
                );
            }

            return false;
        });
    };

    /**
     * Clears the form on request.
     */
    clearForm = function () {
        jQuery('#take_name').val('');
        jQuery('#amount').val('');
        jQuery('#stream').val('');
        jQuery('#ring_take_name_id').val('');
        jQuery('#create').val('Create');
        jQuery('#delete').addClass('hide');
        formClickEvents();
        jq_processing.removeClass('block-loading');
    };

    /**
     * Delete from edit list.
     * @param {string} ring_take_name_id
     */
    deleteFromEditList = function (ring_take_name_id) {
        var jq_row = jQuery('#edit_take_names ul')
            .find('.edit-ring-take-name-id:contains("' + ring_take_name_id + '")')
            .parent();
        jq_row.remove();
    };

    return {

        construct :function () {
            jq_processing = jQuery('#create_take_name');

            updateClickEvents();
            formClickEvents();

            var jq_selector = jQuery('#select_stream_selector');

            /**
             * Show and hide the select stream selector.
             *
             * @return boolean
             */
            jQuery('#select_stream').click(function () {
                if (jq_selector.is(':visible')) {

                    jq_selector.slideUp(250, function () {
                        jQuery('#select_stream').text('Search');
                        jq_selector.empty();
                    });
                    return false;
                }

                var actions = [
                {
                    name : 'Select',
                    onClick : function (event, jq_row, row) {
                        event.preventDefault();
                        var stream_url = BabblingBrook.Library.makeStreamUrl(
                            {
                                domain : row.domain,
                                username : row.username,
                                name : row.name,
                                version : '0/0/0'
                            }
                        );
                        jQuery('#stream').val(stream_url);
                        jq_selector
                            .css('display','')
                            .slideUp(250, function() {
                                jq_selector
                                    .addClass('hide')
                                    .css('display','')
                            });
                        jQuery('#select_stream').text('Search');
                    }
                }
                ];
                var stream_search_table = new BabblingBrook.Client.Component.Selector(
                    'stream',
                    'stream_results',
                    jq_selector,
                    actions,
                    {
                        show_fields : {
                            version : false
                        }
                    }
                );

                jq_selector.slideDown(250, function() {
                    jQuery('#select_stream').text('Close search');
                    jq_selector.removeClass('hide');
                });

                return false;

            });

        }

    };
}());


jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Ring.ManageTakeNames.construct();
});