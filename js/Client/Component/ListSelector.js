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
 * @fileOverview Allows featuers to be searched for and added to a list.
 * @author Sky Wickenden
 */

/**
 * Presents a list of items that can be removed.
 * Includes a location textbox and a search table for new items to be added. Includes a link to change the
 * version if relevent.
 *
 * @param {string} type The type of feature being used. Valid values are 'rhythm', 'stream', 'ring'.
 * @param {string} id A unique id used to identify this selector (will be used in css).
 * @param {object} jq_location A jQuery object pointing to the location of the new list selector.
 * @param {string} get_list_url The url to fetch existing list items from.
 *      success callback data must be in the format:
 *          {
 *              success : true,
 *              list_items : <array>    // In name object format, with an additional paramater for sort_order.
 *          }
 *      error callback data must be in the format:
 *          {
 *              success : false,
 *              error : <string>
 *          }
 *
 * @param {string} switch_url The url to switch one item in the list for another.
 *      (Including different verisons of the same item).
 *      success callback data must be in the format:
 *          {
 *              success : true
 *          }
 *      error callback data must be in the format:
 *          {
 *              success : false,
 *              error : <string>
 *          }
 * @param {string} new_item_url The url to add new items to the list.
 *      success callback data must be in the format:
 *          {
 *              success : true
 *          }
 *      error callback data must be in the format:
 *          {
 *              success : false,
 *              error : <string>
 *          }
 * @param {string} delete_item_url The url to call to delete an item from the list. *
 *      success callback data must be in the format:
 *          {
 *              success : true
 *          }
 *      error callback data must be in the format:
 *          {
 *              success : false,
 *              error : <string>
 *          }
 * @param {string} swap_url The url to call to swap the display order of two items.
 *      success callback data must be in the format:
 *          {
 *              success : true
 *          }
 *      error callback data must be in the format:
 *          {
 *              success : false,
 *              error : <string>
 *          }
 * @param {string} [rhythm_cat_type] The type of Rhythm category to limit results by.
 *      Only used if type is set to Rhythm.
 * @param {string} [stream_kind] Limit stream to a particular kind. Only used if type is set to stream.
 * @param {boolean} [search_fields] The fields to display in the search selector. See Selector.js for options.
 * @param {boolean} [user_type] The type of user to search for. 'users', 'rings', or 'all'. Defaults to all.
 * @param {boolean} [only_joinable_rings] Only displays rings the user can choose to join.
 *
 * @namespace Editing of streams.
 * @package JS_Client
 */
BabblingBrook.Client.Component.ListSelector = function (type, id, jq_location, get_list_url, switch_url, new_item_url,
    delete_item_url, swap_url, rhythm_cat_type, stream_kind, search_fields, user_type, only_joinable_rings
) {
    'use strict';
    var jq_list_selector;

    var jq_add_location;

    var jq_search;

    var jq_error_row;

    /**
     * Callback for when the server has deleted an item row.
     *
     * @param {type} jq_row A jquery object pointing to the row that has been deleted.
     *
     * @param {object} response_data The data sent back from the server.
     * @param {boolean} response_data.success Was the request successful.
     * @param {string} response_data.error If success is false there will be an error message here.
     *
     * @returns {void}
     */
    var onItemDeleted = function (jq_row, response_data) {
        jQuery('.list-selector-location>input', jq_row).removeClass('textbox-loading');
        if (response_data.success === true) {
            jq_row.remove();
        } else {
            onListSelectorError(response_data.error);
        }
    };

    /**
     * Called when the delete button for a list item is clicked.
     *
     * @returns {void}
     */
    var onDeleteItemRequested = function () {
        var jq_this = jQuery(this);
        var jq_location = jq_this.parent().parent().find('.list-selector-location>input');
        var item_url = jq_this.parent().parent().find('.list-selector-location').attr('data-location');
        var item_name;
        switch(type) {
            case 'rhythm':
                item_name = BabblingBrook.Library.makeRhythmFromUrl(item_url);
                break;

            case 'stream':
                item_name = BabblingBrook.Library.makeStreamFromUrl(item_url);
                break;

            case 'user':
                item_name = BabblingBrook.Library.makeUserFromUrl(item_url);
                item_name.is_ring = true;
                break;
        }
        if (typeof item_name === 'string') {
            onListSelectorError(item_name);
        } else {
            jq_location.addClass('textbox-loading');
            jq_location.attr('disabled', 'disabled');
            BabblingBrook.Library.post(
                delete_item_url,
                {
                    name : item_name
                },
                onItemDeleted.bind(null, jq_this.parent().parent()),
                onListSelectorError
            );
        }
    };

    /**
     * Callback for when an existing item textbox has been edited.
     *
     * @returns {undefined}
     */
    var onExistingItemChanged = function () {
        var jq_item_input = jQuery(this);
        var new_url = jq_item_input.val();
        var old_url = jq_item_input.parent().attr('data-location');
        var old_item_name;
        var new_item_name;
        if (new_url !== old_url) {
            switch(type) {
                case 'rhythm':
                    old_item_name = BabblingBrook.Library.makeRhythmFromUrl(old_url);
                    new_item_name = BabblingBrook.Library.makeRhythmFromUrl(new_url);
                    break;

                case 'stream':
                    old_item_name = BabblingBrook.Library.makeStreamFromUrl(old_url);
                    new_item_name = BabblingBrook.Library.makeStreamFromUrl(new_url);
                    break;

                case 'user':
                    old_item_name = BabblingBrook.Library.makeUserFromUrl(old_url);
                    old_item_name.is_ring = true;
                    new_item_name = BabblingBrook.Library.makeUserFromUrl(new_url);
                    new_item_name.is_ring = true;
                    break;
            }
            if (typeof old_item_name === 'string') {
                onListSelectorError('Original ' + type + ' url is broken: ' + old_url);
            } else if (typeof new_item_name === 'string') {
                onListSelectorError(new_url);
            } else {
                jq_item_input
                    .val(new_url)
                    .addClass('textbox-loading')
                    .attr('disabled', 'disabled');
                BabblingBrook.Library.post(
                    switch_url,
                    {
                        old_name : old_item_name,
                        new_name : new_item_name
                    },
                    onChangedListItemUrl.bind(null, jq_item_input.parent().parent()),
                    onListSelectorError
                );
            }
        } else {
            clearError();
            switchLastRow();
        }
    };

    /**
     * Callback for when a new item has been sent to the server.
     *
     * @param {string} item_url The url of the item that has been added.
     * @param {object} response_data The data sent back from the server.
     * @param {boolean} response_data.success Was the request successful.
     * @param {string} response_data.error If success is false there will be an error message here.
     *
     * @returns {void}
     */
    var onNewItemAppended = function (item_url, response_data) {
        jq_add_location.removeClass('textbox-loading');
        jQuery('tr', jq_search).removeClass('block-loading');
        if (response_data.success === true) {
            appendListRow(item_url);

            var default_item_value = jq_add_location.attr('data-default-value');
            jq_add_location
                .val(default_item_value)
                .removeAttr('disabled');
            clearError();
            switchLastRow();
        } else {
            onListSelectorError(response_data.error);
        }
    };

    /**
     * Adds a new item to the list
     *
     * @param {type} item_url The url of the item to add.
     *
     * @returns {void}
     */
    var addNewItem = function(item_url) {
        jq_add_location
            .val(item_url)
            .attr('disabled', 'disabled')
            .addClass('textbox-loading');

        var item_name;
        switch(type) {
            case 'rhythm':
                item_name = BabblingBrook.Library.makeRhythmFromUrl(item_url);
                break;

            case 'stream':
                item_name = BabblingBrook.Library.makeStreamFromUrl(item_url);
                break;

            case 'user':
                item_name = BabblingBrook.Library.makeUserFromUrl(item_url);
                item_name.is_ring = true;
                break;
        }

        if (typeof item_name === 'string') {
            onListSelectorError(item_name);
        } else {
            BabblingBrook.Library.post(
                new_item_url,
                {
                    name : item_name
                },
                onNewItemAppended.bind(null, item_url),
                onListSelectorError
            );
        }
    };


    /**
     * Opens the search feature.
     *
     * Creates a standard search form.
     *
     * @param {object} event The event object.
     *
     * @returns {void}
     */
    var onOpenSearch = function (event) {
        var jq_this = jQuery(this);
        jq_this.parent().find('a.close-search').removeClass('hide');
        jq_this.addClass('hide');
        jQuery('.list-selector-search-row', jq_list_selector).removeClass('hide').show();
        jQuery('.list-selector-search', jq_list_selector).removeClass('hide').show();
        switchLastRow();

        var actions = [{
            name : 'Add',
            onClick : function (event, jq_row) {
                event.preventDefault();
                if (jq_add_location.attr('disabled') === 'disabled') {
                    return;
                }
                var name;
                var domain;
                var username;
                var url;
                switch(type) {
                    case 'rhythm':
                        name = jQuery('.name', jq_row).text();
                        domain = jQuery('.domain', jq_row).text();
                        username = jQuery('.username', jq_row).text();
                        url = domain + '/' + username + '/rhythm/' + name + '/0/0/0';
                        break;

                    case 'stream':
                        name = jQuery('.name', jq_row).text();
                        domain = jQuery('.domain', jq_row).text();
                        username = jQuery('.username', jq_row).text();
                        url = domain + '/' + username + '/stream/' + name + '/0/0/0';
                        break;

                    case 'user':
                        domain = jQuery('.domain', jq_row).text();
                        username = jQuery('.username', jq_row).text();
                        url = domain + '/' + username;
                        break;
                }

                jq_row.addClass('block-loading');
                addNewItem(url);
            }
        }];
        var options = {
            show_fields : search_fields,
            stream_kind : stream_kind,
            only_joinable_rings : only_joinable_rings,
            user_type: user_type,
            onReady : function () {
                jq_search.removeClass('hide');
                jq_search.slideDown();
            },
            rhythm_category : rhythm_cat_type
        };
        BabblingBrook.Client.Component.Selector(
            type,
            'list_selector_' + id,
            jq_search,
            actions,
            options
        );
        event.stopPropagation();
        return false;
    };

    /**
     * Closes the search feature.
     *
     * @param {object} event The event object.
     *
     * @returns {void}
     */
    var onCloseSearch = function (event) {
        var jq_this = jQuery(this);
        jq_this.parent().find('a.open-search').removeClass('hide');
        jq_this.addClass('hide');
        var jq_search_results = jQuery('.list-selector-search', jq_list_selector);
        jq_search_results.slideUp(function(){
            //jQuery('.list-selector-search-row', jq_list_selector).addClass('hide');
            jq_search_results.html('');
            switchLastRow();
        });
        event.stopPropagation();
        return false;
    };

    /**
     * Appends a new row to the list table.
     *
     * @param {string} item_url The url of the item to append.
     *
     * @returns {void}
     */
    var appendListRow = function(item_url) {
        var jq_row = jQuery('#list_selector_location_row_template>tbody>tr').clone();
        jQuery('.list-selector-location>input', jq_row).val(item_url);
        jQuery('.list-selector-location', jq_row).attr('data-location', item_url);

        if (type !== 'user') {
            jQuery('.list-selector-change-version>a', jq_row).removeClass('hide');
        }

        jQuery('>tbody', jq_list_selector).append(jq_row);
        showOrderButtons();
    };

    /**
     * Display the correct order buttons.
     */
    var showOrderButtons = function () {
        var jq_rows = jQuery('>tbody>tr', jq_list_selector);
        jQuery('.move-icon', jq_rows).removeClass('hidden');
        jQuery('.move-up', jq_rows.first()).addClass('hidden');
        jQuery('.move-down', jq_rows.last()).addClass('hidden');

    };

    /**
     * Callback for when the preexisting list of items has been fetched.
     *
     * @param {object} response_data The data sent back from the server.
     * @param {boolean} response_data.success Was the request successful.
     * @param {string} response_data.error If success is false there will be an error message here.
     * @param {array} response_data.list_items An array of items in object name format.
     * @param {integer} response_data.items[].sort_order The order to display the items in. 1 is high.
     *
     * @returns {void}
     */
    var onListFetched = function(response_data) {
        if (response_data.success === true) {
            var length = response_data.items.length;
            var item_url;
            for (var i = 0; i < length; i++) {
                switch(type) {
                    case 'rhythm':
                        item_url = BabblingBrook.Library.makeRhythmUrl(response_data.items[i]);
                        break;

                    case 'stream':
                        item_url = BabblingBrook.Library.makeStreamUrl(response_data.items[i]);
                        break;

                    case 'user':
                        item_url = BabblingBrook.Library.makeUserUrl(response_data.items[i]);
                        break;
                }
                appendListRow(item_url, response_data.items[i].sort_order);
            }
            jq_list_selector.removeClass('block-loading');
        } else {
            onListSelectorError(response_data.error);
        }

        showOrderButtons();
    };

    /**
     * Switches the css for the last row in the footer based on what is hidden.
     *
     * @returns {void}
     */
    var switchLastRow = function() {
        var jq_foot_rows = jQuery('>tfoot>tr', jq_list_selector);
        var jq_reverse_rows = jQuery(jq_foot_rows.get().reverse());
        jq_foot_rows.removeClass('last-row');
        jq_reverse_rows.each(function(i, row) {
            var jq_row = jQuery(row);
            if (jq_row.hasClass('hide') === true) {
                return true;
            } else {
                jq_row.addClass('last-row');
                return false;
            }
        });
    };

    /**
     * Displays a list selector error message
     *
     * @param {string|undefined} error The error message. If undefined then a default message will be displayed.
     *
     * @returns {undefined}
     */
    var onListSelectorError = function (error) {
        jq_add_location
            .removeClass('textbox-loading')
            .removeAttr('disabled');
        jQuery('tr', jq_search).removeClass('block-loading');
        jQuery('.list-selector-location>input', jq_search)
            .removeClass('textbox-loading')
            .removeAttr('disabled');

        if (typeof error === 'undefined') {
            error = 'An unknown error has occurred';
        }

        jq_error_row.removeClass('hide');
        jQuery('td' , jq_error_row).html(error);

        switchLastRow();
    };

    /**
     * Fetch the list of items that has already been selected for this list.
     *
     * @returns {void}
     */
    var fetchList = function () {
        BabblingBrook.Library.post(
            get_list_url,
            {},
            onListFetched,
            onListSelectorError
        );
    };

    /**
     * Removes any error messages.
     *
     * @returns {void}
     */
    var clearError = function() {
        jq_error_row.addClass('hide');
        jQuery('>td', jq_error_row).html('');
    };

    /**
     * Callback for after a preexisting item url has been changed.
     *
     * @param {object} jq_item_row Jquery object pointing to the row that has been updated.
     * @param {object} response_data The response object from the server.
     * @param {boolean} response_data.success Was the request successful.
     * @param {string} response_data.error If success is false there will be an error message here.
     *
     * @returns {void}
     */
    var onChangedListItemUrl = function (jq_item_row, response_data) {
        var jq_input = jq_item_row.find('.list-selector-location>input');
        jq_input
            .removeClass('textbox-loading')
            .removeAttr('disabled');
        jq_item_row.find('select').removeAttr('disabled');
        if (response_data.success === false) {
            onListSelectorError(response_data.error);
        } else {
            jq_item_row.find('.list-selector-location').attr('data-location', jq_input.val());
            clearError();
            switchLastRow();
        }
    };

    /**
     * Catches change events when select version drop down is used.
     *
     * @returns {void}
     */
    var onDifferentVersionSelected = function () {
        var jq_this = jQuery(this);
        var jq_item_row = jq_this.parent().parent().parent();
        var old_url = jq_item_row.find('.list-selector-location').attr('data-location');
        var jq_input = jq_item_row.find('.list-selector-location>input');
        var new_version = jq_this.val();
        var new_url;
        var old_item_name;
        var new_item_name;
        switch(type) {
            case 'rhythm':
                new_url = BabblingBrook.Library.changeRhythmUrlVersion(old_url, new_version);
                old_item_name = BabblingBrook.Library.makeRhythmFromUrl(old_url);
                new_item_name = BabblingBrook.Library.makeRhythmFromUrl(new_url);
                break;

            case 'stream':
                new_url = BabblingBrook.Library.changeStreamUrlVersion(old_url, new_version);
                old_item_name = BabblingBrook.Library.makeStreamFromUrl(old_url);
                new_item_name = BabblingBrook.Library.makeStreamFromUrl(new_url);
                break;
        }
        if (typeof old_item_name === 'string') {
            onListSelectorError('Original ' + type + ' url is broken: ' + old_item_name);
        } else if (typeof new_item_name === 'string') {
            onListSelectorError('New ' + type + ' url is broken : ' + new_item_name);
        } else {
            jq_input
                .val(new_url)
                .addClass('textbox-loading')
                .attr('disabled', 'disabled');
            jq_this.attr('disabled', 'disabled');
            BabblingBrook.Library.post(
                switch_url,
                {
                    old_name : old_item_name,
                    new_name : new_item_name
                },
                onChangedListItemUrl.bind(null, jq_item_row),
                onListSelectorError
            );
        }
    };

    /**
     * Displays the versions available for the selected item.
     *
     * @param {object} jq_change_version_link JQuery object pointing to the link the user clicked.
     * @param {object} response_data The version data sent from the server.
     * @param {boolean} response_data.success Was the request successful.
     * @param {string} response_data.error If success is false there will be an error message here.
     * @param {object} response.data.versions An array of versions that are available for this item.
     *      Indexed by the version string and contianing the version string.
     *
     * @returns {void}
     */
    var onVersionsFetched = function(jq_change_version_link, response_data) {
        var jq_change_version = jq_change_version_link.parent().find('span');
        var jq_select = jQuery('select', jq_change_version);
        jq_change_version_link.addClass('hide');
        jq_change_version.removeClass('hide');
        var versions = response_data.versions;
        for (var key in versions) {
            if (versions.hasOwnProperty(key)) {
                var jq_option = jQuery('#list_selector_version_row_template>option').clone();
                jq_option.val(key);
                jq_option.text(versions[key]);
                jq_select.append(jq_option);
            }
        }
    };

    /**
     * Callback for when a change version link is clicked.
     *
     * @returns {void}
     */
    var onSelectDifferentVersion = function () {
        var jq_this = jQuery(this);
        jq_this.addClass('text-loading');
        var item_url = jq_this.parent().parent().find('.list-selector-location').attr('data-location');
        var item_name;
        var url;
        switch(type) {
            case 'rhythm':
                item_name = BabblingBrook.Library.makeRhythmFromUrl(item_url);
                break;

            case 'stream':
                item_name = BabblingBrook.Library.makeStreamFromUrl(item_url);
                break;
        }

        if (typeof item_name === 'string') {
            onListSelectorError(item_name);
        } else {
            switch(type) {
                case 'rhythm':
                    url = BabblingBrook.Library.makeRhythmUrl(item_name, 'Versions', true);
                    break;

                case 'stream':
                    url = BabblingBrook.Library.makeStreamUrl(item_name, 'Versions', true);
                    break;
            }
            BabblingBrook.Library.post(
                'http://' + url,
                {},
                onVersionsFetched.bind(null, jq_this),
                onListSelectorError
            );
        }
    };

    /**
     * Setup the click events on the link to change an items version.
     *
     * @returns {void}
     */
    var setupSelectFromDifferentVersionLinks = function () {
        if (type === 'ring') {
            return;
        }

        jq_list_selector.on(
            'click',
            '.list-selector-change-version>a',
            onSelectDifferentVersion
        );
        jq_list_selector.on(
            'change',
            '.list-selector-change-version>span>select',
            onDifferentVersionSelected
        );
    };

    /**
     * Callback for after the server has swaped the display order of two rows.
     *
     * @param {object} jq_row_1 A jQuery object for the first row that has been swappped.
     * @param {object} jq_row_2 A jQuery object for the second row that has been swappped.
     * @param {object} response_data The response returned from the server.
     * @param {boolean} response_data.success Was the request successful.
     * @param {string} response_data.error If success is false there will be an error message here.
     *
     * @return void
     */
    var onSwaped = function (jq_row_1, jq_row_2, response_data) {
        jQuery('.move-up', jq_row_1).attr('src', '/images/ui/up-arrow-untaken.svg');
        jQuery('.move-up', jq_row_2).attr('src', '/images/ui/up-arrow-untaken.svg');
        jQuery('.move-down', jq_row_1).attr('src', '/images/ui/down-arrow-untaken.svg');
        jQuery('.move-down', jq_row_2).attr('src', '/images/ui/down-arrow-untaken.svg');
        if (response_data.success === false) {
            onListSelectorError(response_data.error);
        } else {
            var jq_placeholder = jq_row_1.clone();
            jq_row_1.before(jq_placeholder);
            jq_row_2.after(jq_row_1);
            jq_placeholder.after(jq_row_2);
            jq_placeholder.remove();
        }
        showOrderButtons();
    };

    /**
     * Send a request to the server to swap two rows
     *
     * @param {object} jq_row_1 A jQuery object for the first row that is to be swappped.
     * @param {object} jq_row_2 A jQuery object for the second row that is to be swappped.
     *
     * @return void
     */
    var swapRows = function (jq_row_1, jq_row_2) {
        var url_1 = jq_row_1.find('td.list-selector-location').attr('data-location');
        var url_2 = jq_row_2.find('td.list-selector-location').attr('data-location');
        var item_name_1;
        var item_name_2;
        switch(type) {
            case 'rhythm':
                item_name_1 = BabblingBrook.Library.makeRhythmFromUrl(url_1);
                item_name_2 = BabblingBrook.Library.makeRhythmFromUrl(url_2);
                break;

            case 'stream':
                item_name_1 = BabblingBrook.Library.makeStreamFromUrl(url_1);
                item_name_2 = BabblingBrook.Library.makeStreamFromUrl(url_2);
                break;

            case 'user':
                item_name_1 = BabblingBrook.Library.makeUserFromUrl(url_1);
                item_name_2 = BabblingBrook.Library.makeUserFromUrl(url_2);
                break;
        }
        BabblingBrook.Library.post(
            swap_url,
            {
                item_name_1 : item_name_1,
                item_name_2 : item_name_2
            },
            onSwaped.bind(null, jq_row_1, jq_row_2),
            onListSelectorError
        );
    }

    /**
     * A click event handler to move a row down one place.
     */
    var onMoveDown = function () {
        jQuery(this).attr('src', '/images/ui/down-arrow-paused.svg');
        var jq_row_1 = jQuery(this).parent().parent();
        var jq_row_2 = jq_row_1.next();
        swapRows(jq_row_1, jq_row_2);
    }

    /**
     * A click event handler to move a row up one place.
     */
    var onMoveUp = function () {
        jQuery(this).attr('src', '/images/ui/up-arrow-paused.svg');
        var jq_row_1 = jQuery(this).parent().parent();
        var jq_row_2 = jq_row_1.prev();
        swapRows(jq_row_1, jq_row_2);
    }

    /**
     * Setsup the list selector.
     */
    var setup = function () {
        jq_list_selector = jQuery('#list_selector_template>table').clone();
        jq_location.append(jq_list_selector);

        jq_add_location = jQuery('.list-selector-add-location', jq_list_selector);
        jq_search = jQuery('.list-selector-search', jq_list_selector);
        jq_error_row = jQuery('>tfoot>tr.error', jq_list_selector);

        fetchList();

        setupSelectFromDifferentVersionLinks();

        jQuery('>tfoot>tr>td>a.close-search', jq_list_selector).click(onCloseSearch);
        jQuery('>tfoot>tr>td>a.open-search', jq_list_selector).click(onOpenSearch);

        jq_list_selector.on('blur', '.list-selector-location>input', onExistingItemChanged);

        jq_list_selector.on('click','.list-selector-remove>img', onDeleteItemRequested);

        jq_list_selector.on('click','.list-selector-sort>img.move-up', onMoveUp);
        jq_list_selector.on('click','.list-selector-sort>img.move-down', onMoveDown);

        var default_new_rhythm_value = jq_add_location.attr('data-default-value');
        jq_add_location
            .val(default_new_rhythm_value)
            .focus(function () {
                if (jq_add_location.val() === default_new_rhythm_value) {
                    jq_add_location.val('');
                }
            })
            .blur(function () {
                if (jq_add_location.val() === '') {
                    jq_add_location.val(default_new_rhythm_value);
                } else {
                    jq_add_location.attr('disabled', 'disabled');
                    addNewItem(jq_add_location.val());
                }
            });
    };
    setup();
};
