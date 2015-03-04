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
 * @fileOverview Creats a search table for users, streams and rhythms.
 */

/**
 * Initialise the table.
 *
 * @param {string} type one of : user, stream, rhythm.
 * @param {string} id A unique identifier for tag ids.
 * @param {object} jq_selector jQuery selector to append the table to.
 * @param {object} actions An array of objects containing action names and their callback functions.
 *                The the action 'close' is reserved for the close button.
 * @param {string} actions.[].name The name(or html) to appear in the action link.
 * @param {string} [actions.[].class] The class to use on the action link. Defaults to the name if undefined.
 *      (required for names that use html).
 * @param {function} actions.[].onClick A callback for when the action is clicked.
 *                                  jq_row is passed in to enable jquery access to contents of the tr.
 *                                  The row of search data is passed.
 * @param {function} [actions.[].onReady] An on display callback.
 *                                  jq_row is passed in to enable jq uery access to contents of the tr.
 *                                  The row of search data is passed.
 * @param {boolean} [options] Extra optional paramaters.
 * @param {boolean} [options.show_fields] Which fields should be shown.
 * @param {boolean} [options.show_fields.domain] Should the domain column be shown
 * @param {boolean} [options.show_fields.username] Should the username column be shown
 * @param {boolean} [options.show_fields.name] Should the name column be shown
 * @param {boolean} [options.show_fields.version] Should the version column be shown
 * @param {boolean} [options.show_fields.date_created] Should the date_created column be shown
 * @param {boolean} [options.show_fields.status] Should the status column be shown
 * @param {boolean} [options.show_fields.stream_kind] Should the stream_kind column be shown
 * @param {boolean} [options.show_fields.user_type] Should the user_type column be shown
 * @param {boolean} [options.show_fields.rhythm_category] See show_fields
 *      and user_show_fields for defaults.
 * @param {boolean} [options.show_fields.ring_ban] Shows the ban status of members of rings.
 *      Can only be turned on if the ring_filter option is used.
 * @param {funtion|undefined} [options.onReady] A callback function that is called when the
 *      data is ready for display. It accepts one argument; the page number.
 * @param {funtion|undefined} [options.onBeforeRedraw] A callback function that is called before the page is redrawn
 *   after a new page of data has loaded.
 * @param {boolean} [options.show_close=false] Show the close link.
 * @param {string|undefined} [options.loading_selector] The jQuery selector to add the 'block-loading' class to.
 *      Deafults to the selectors table.
 * @param {object} [options.initial_values] Initial values for the search fields.
 * @param {string} [options.initial_values.domain] Initial domain value for the search field.
 * @param {string} [options.initial_values.username] Initial username value for the search field.
 * @param {string} [options.initial_values.name] Initial name value for the search field.
 * @param {string} [options.initial_values.version] Initial version value for the search field.
 * @param {string} [options.initial_values.user_type] Initial user_type value for the search field.
 * @param {string} [options.initial_values.date_created] Initial date_created value for the search field.
 * @param {string} [options.initial_values.status] Initial status value for the search field.
 * @param {string} [options.exact_match] If any search filters on a field should be an exact match.
 * @param {string} [options.exact_match.domain] Should the domain field be an exact match.
 * @param {string} [options.exact_match.username] Should the username field be an exact match.
 * @param {string} [options.exact_match.name] Should the name field be an exact match.
 * @param {string} [options.exact_match.version] Should the version field be an exact match.
 * @param {string} [options.exact_match.date_created] Should the date_created field be an exact match.
 * @param {string} [options.stream_kind] The kind of stream to search for. 'standard', 'user' or 'all'.
 * @param {string} [options.rhythm_category] The category of rhythm to search for.
 * @param {string} [options.user_type] The type of user to search for. 'user', 'ring', 'all'. defaults to 'all'.
 * @param {string} [options.ring_filter] Only fetch the results of users of a particular ring.
 *      Also results in an additional pice of data returning; The ban field to indicate if a user is banned.
 * @param {string} [options.ring_filter.domain] The domain of the ring to fetch results for.
 * @param {string} [options.ring_filter.username] The username of the ring to fetch results for.
 * @param {string} [options.only_joinable_rings=false] Only display rings that the user can join.
 *      (only used if searching for user_type = 'ring')
 * @param {object|undefined} [options.users_to_vet_for_ring] If this is defined then only users that have requested
 *      to be a member of the given ring are selected.
 * @param {object|undefined} [options.users_to_vet_for_ring.domain] The domain of the ring to vet users for.
 * @param {object|undefined} [options.users_to_vet_for_ring.username] The username of the ring to vet users for.
 * @param {string} [options.additional_selector_class]
 * @param {number} [page] The page number to load. If missing then page one is loaded.
 *
 * @namespace Creats a search table for users, streams and rhythms.
 * @package JS_Client
 */
BabblingBrook.Client.Component.Selector = function (type, id, jq_selector, actions, options, page) {
    'use strict';
    // The maximum number of rows to show.
    var row_qty = 10;

    if (typeof page === 'undefined') {
        page = 1;
    }

    var show_fields = {
        domain : true,
        username : true,
        name : true,
        version : true,
        date_created : false,
        status : false,
        stream_kind : false,
        user_type : false,
        rhythm_category : false,
        ring_ban : false
    };

    var user_show_fields = {
        domain : true,
        username : true,
        name : false,
        version : false,
        date_created : false,
        status : false,
        stream_kind : false,
        user_type : false,
        rhythm_category : false,
        ring_ban : false
    };

    var initial_values = {
        domain : '',
        username : '',
        name : '',
        version : 'major/minor/patch',
        date_created : '',
        status : '',
        stream_kind : 'standard',
        user_type : '' ,
        rhythm_category : '',
        ring_membership_type : '',
        ring_ban : 'all',
    };

    var only_joinable_rings = false;

    var exact_match = {
        domain : false,
        username : false,
        name : false
    }

    var user_exact_match = {
        domain : false,
        username : false
    }

    var sort_order = {};

    var stream_sort_order = {
        domain : 'ascending',
        username : 'ascending',
        name : 'ascending',
        version : 'ascending',
        status : 'ascending',
        stream_kind : 'ascending'
    };

    var rhythm_sort_order = {
        domain : 'ascending',
        username : 'ascending',
        name : 'ascending',
        version : 'ascending',
        status : 'ascending'
    };

    var user_sort_order = {
        domain : 'ascending',
        username : 'ascending',
        user_type : 'ascending',
        ring_ban : 'ascending'
    };

    // An array of column names in the priority that they should be sorted.
    var sort_priority = [];

    var stream_sort_priority = [
        'domain',
        'username',
        'name',
        'version',
        'status',
        'stream_kind'
    ];

    var rhythm_sort_priority = [
        'domain',
        'username',
        'name',
        'version',
        'status'
    ];

    var user_sort_priority = [
        'domain',
        'username',
        'user_type',
        'ring_ban'
    ];

    // A flag to indicate that a new page (or filter request) has been loaded
    // rather than the initial display of the data.
    var new_page = false;

    var user_type = '';

    var onReady = function () {};

    var jq_loading;

    if (typeof options === 'undefined') {
        options = {};
    }

    var jq_table = jQuery('#selector_template>table').clone();
    if (typeof options.additional_selector_class !== 'undefined') {
        jq_table.addClass(options.additional_selector_class);
    }

    jQuery('tr.search>.stream-kind>select', jq_table).val(initial_values.stream_kind);

    var onStartLoading = function () {
        jq_loading.addClass('block-loading');
    };

    var jq_table_body;

    var onFinishedLoading = function () {
        jq_loading.removeClass('block-loading');
    };

    var valid_types = [
        'user',
        'stream',
        'rhythm'
    ];

    var showPageLinks = function (row_count) {
        jQuery('tfoot .page-number', jq_table).text(page);
        if (page === 1) {
            jQuery('tfoot .first', jq_table).addClass('pale');
            jQuery('tfoot .last', jq_table).addClass('pale');
        } else {
            jQuery('tfoot .first', jq_table).removeClass('pale');
            jQuery('tfoot .last', jq_table).removeClass('pale');
        }
        if (row_count < row_qty) {
            jQuery('tfoot .next', jq_table).addClass('pale');
        } else {
            jQuery('tfoot .next', jq_table).removeClass('pale');
        }
    };

    /**
     * Callback with a selection of data to display.
     * @param {object} data
     */
    var onSearchReturned = function (search_data) {
        if (new_page === true && typeof options.onBeforeRedraw === 'function') {
            options.onBeforeRedraw();
        }
        new_page = true;
        jq_table_body.html('');
        jQuery.each(search_data, function (index, row) {
            var jq_row = jQuery('#selector_row_template>tbody>tr').clone();
            jQuery.each(row, function (key, value) {
                key = key.replace('_', '-');
                var jq_column = jQuery('.' + key, jq_row);
                jq_column.text(value);
            });

            var jq_action_column = jQuery('.action', jq_row);
            jQuery.each(actions, function (i, action) {
                if (action.name !== 'close') {        // close is reserved for the close button.
                    if (typeof action.class === 'undefined') {
                        action.class = action.name;
                    }

                    var jq_action = jQuery(
                        '<a href="" class="selector-action-' + action.class.toLowerCase() + '">'
                    );
                    jq_action.html(action.name);
                    jq_action_column.append(jq_action);

                    if (typeof action.onReady === 'function') {
                        action.onReady(jq_row, row);
                    }

                    if (typeof action.onClick === 'function') {
                        jq_action.click(function (event) {
                            action.onClick(event, jq_row, row);
                        });
                    }
                }
            });

            // Remove the columns that are not being used.
            jQuery.each(show_fields, function (key, value) {
                if (value === false) {
                    key = key.replace('_', '-');
                    jQuery('.' + key, jq_row).remove();
                }
            });
            jq_table_body.append(jq_row);
        });

        showPageLinks(search_data.length);
        onFinishedLoading();
        onReady(page);
    };

    /**
     * Display a user friendly error if a user selection returns an error.
     */
    var onSearchError = function (error_code, error_data) {
        var error_message = jQuery('#selector_error_template').html();
        if (typeof error_code.error_code !== 'undefined' && error_code.error_code === 'scientia_setup_timeout') {
            error_message = jQuery('#selector_error_domain_template').html();
        }
        showError(error_message);
        onFinishedLoading();
        console.log(error_code);
        console.log(error_data);
    };

    /**
     * Combines the user selected sort order with the defaults for the current type.
     *
     * @return void
     */
    var finaliseSortOrder = function () {
        var final_sort_order = jQuery.extend({}, sort_order);
        var default_sort_order;
        if (type === 'stream') {
            default_sort_order = stream_sort_order;
        } else if (type === 'rhythm') {
            default_sort_order = rhythm_sort_order;
        } else if (type === 'user') {
            default_sort_order = user_sort_order;
        }
        jQuery.each(default_sort_order, function (key, value) {
            if (typeof final_sort_order[key] === 'undefined') {
                final_sort_order[key] = value;
            }
        });
        return final_sort_order;
    };

    /**
     * Combines the user selected sort priority with the defaults for the current type.
     *
     * @return void
    */
    var finaliseSortPriority = function () {
        var final_sort_priority = jQuery.extend([], sort_priority);
        var default_sort_priority;
        if (type === 'stream') {
            default_sort_priority = stream_sort_priority;
        } else if (type === 'rhythm') {
            default_sort_priority = rhythm_sort_priority;
        } else if (type === 'user') {
            default_sort_priority = user_sort_priority;
        }
        for (var value in default_sort_priority) {
            if (typeof final_sort_priority[default_sort_priority[value]] === 'undefined') {
                final_sort_priority.push(default_sort_priority[value]);
            }
        }
        return final_sort_priority;
    }

    /**
     * Fetch data from the server and populate the table.
     *
     * @param {string} domain Domain search constraints.
     * @param {string} username Username search constraints.
     * @param {string} name Name search constraints.
     * @param {string} version Version search constraints.
     * @param {string} date_created Date created search constraints.
     * @param {string} status status search constraints.
     * @param {string} user_type User type search constraints.
     * @param {string} stream_kind Stream kind search constraints.
     * @param {string} rhythm_category Rhythm category constraints.
     * @param {string} ring_ban_filter Ring ban filter constraints.
     * @param {number} [current_page=1] The page number to return. Optional.
     *
     * @return void
     */
    var getData = function (domain, username, name, version, status, date_created,
        stream_kind, rhythm_category, ring_ban_filter, current_page
    ) {
        if (typeof current_page !== 'undefined') {
            page = current_page;
        }

        onStartLoading();

        // Fetch data from this sites domus domain, or if the domain column is filtered then use that.
        var filter_domain = window.location.host;
        if (domain.length > 0 && BabblingBrook.Test.isA([domain, 'domain'])) {
            filter_domain = domain;
        }

        var final_sort_order = finaliseSortOrder();
        var final_sort_priority = finaliseSortPriority();

        var search_type;
        var request_data = {
            domain_filter : filter_domain,
            username_filter : username,
           // date_created : date_created,
            page : page,
            row_qty : row_qty,
            sort_order : final_sort_order,
            sort_priority : final_sort_priority,
            exact_match : exact_match
        };
        switch (type) {
            case 'stream':
                search_type = 'StreamSearch';
                request_data.kind = stream_kind;
                request_data.include_versions = show_fields.version;
                request_data.status = status;
                request_data.name_filter = name;
                request_data.version_filter = version;
                break;

            case 'rhythm':
                search_type = 'RhythmSearch';
                request_data.cat_type = rhythm_category;
                request_data.include_versions = show_fields.version;
                request_data.status = status;
                request_data.name_filter = name;
                request_data.version_filter = version;
                break;

            case 'user':
                search_type = 'UserSearch';
                request_data.only_joinable_rings = only_joinable_rings;
                request_data.user_type = user_type;
                request_data.ring_username = '';
                request_data.ring_domain = '';
                request_data.ring_ban_filter = 'all';
                if (typeof options.users_to_vet_for_ring === 'object') {
                    request_data.users_to_vet_for_ring = options.users_to_vet_for_ring;
                } else {
                    request_data.users_to_vet_for_ring = false;
                }
                if (typeof options.ring_filter === 'object') {
                    request_data.ring_username = options.ring_filter.username;
                    request_data.ring_domain = options.ring_filter.domain;
                    request_data.domain_filter = options.ring_filter.domain; // Forces the search onto the ring domain.
                    request_data.exact_match.domain = true;
                    request_data.ring_ban_filter = ring_ban_filter;
                }
                break;
        }

        BabblingBrook.Client.Core.Interact.postAMessage(request_data, search_type, onSearchReturned, onSearchError);
    };

    var showError = function (error) {
        var jq_error_row = jQuery('thead>.error-row', jq_table);
        jq_error_row.removeClass('hide');
        jQuery('td', jq_error_row).html(error);
    };

    var hideError = function () {
        var jq_error_row = jQuery('thead>.error-row', jq_table);
        jq_error_row.addClass('hide');
        jQuery('td', jq_error_row).html('');
    };

    /**
     *  Get the filter data from the table and refresh the table.
     */
    var getDataFromTable = function () {
        var error;
        hideError();
        var domain = initial_values.domain;
        var jq_domain = jQuery('tr.search>.domain>input', jq_selector);
        if (jq_domain.length !== 0) {
            domain = jq_domain.val();
        }
        var username = initial_values.username;
        var jq_username = jQuery('tr.search>.username>input', jq_selector);
        if (jq_username.length !== 0) {
            username = jq_username.val();
        }
        var name = initial_values.name;
        var jq_name = jQuery('tr.search>.name>input', jq_selector);
        if (jq_name.length !== 0) {
            name = jq_name.val();
        }
        var version = initial_values.version;
        var jq_version = jQuery('tr.search>.version>input', jq_selector);
        if (jq_version.length !== 0) {
            version = jq_version.val();
            var version_parts = version.split('/');
            if (version_parts.length !== 3) {
                error = jQuery('#version_error_part_template').html();
            }
            var version_error = jQuery('#version_error_template').html();
            if (version_parts[0] !== 'major' && isNaN(version_parts[0]) === true) {
                error = version_error;
            }
            if (version_parts[1] !== 'minor' && isNaN(version_parts[1]) === true) {
                error = version_error;
            }
            if (version_parts[2] !== 'patch' && isNaN(version_parts[2]) === true) {
                error = version_error;
            }
        }
        var status = initial_values.status;
        var jq_status = jQuery('tr.search>.status>select', jq_selector);
        if (jq_status.length !== 0) {
            status = jq_status.val();
        }
        var jq_user_type = jQuery('tr.search>.user_type>input', jq_selector);
        if (jq_user_type.length !== 0) {
            user_type = jq_user_type.val();
            if (user_type === 'All') {
                user_type = '';
            }
        }
        var stream_kind = initial_values.stream_kind;
        var jq_stream_kind = jQuery('tr.search>.stream-kind>select', jq_selector);
        if (jq_stream_kind.length !== 0) {
            stream_kind = jq_stream_kind.val();
        }
        var date_created = initial_values.date_created;
        var jq_date_created = jQuery('tr.search>.date_created>input', jq_selector);
        if (jq_date_created.length !== 0) {
            date_created = jq_date_created.val();
        }
        var rhythm_category = initial_values.rhythm_category;
        var jq_rhythm_category = jQuery('tr.search>.rhythm_category>input', jq_selector);
        if (jq_rhythm_category.length !== 0) {
            rhythm_category = jq_rhythm_category.val();
        }
        var ring_ban_filter = initial_values.ring_ban;
        var jq_ring_ban = jQuery('tr.search>.ring-ban>select', jq_selector);
        if (jq_ring_ban.length !== 0) {
            ring_ban_filter = jq_ring_ban.val();
        }
        if (typeof error === 'string') {
            showError(error);
        } else {
            getData(
                domain,
                username,
                name,
                version,
                status,
                date_created,
                stream_kind,
                rhythm_category,
                ring_ban_filter,
                page
            );
        }
    };

    /**
     * Handles a click on a sortable column and fetches new results.
     */
    var onSortClicked = function(column_name) {
        var class_name = column_name.replace('_', '-');
        var jq_colum_title = jQuery('thead tr.titles td.' + class_name + ' .column-sort', jq_table);
        var index = sort_priority.indexOf(column_name);
        if (index >= 0) {
            sort_priority.splice(index, 1);
        }
        if (jq_colum_title.hasClass('sort-up') === false && jq_colum_title.hasClass('sort-down') === false) {
            sort_order[column_name] = 'descending';
            jq_colum_title.addClass('sort-down');
            sort_priority.push(column_name);
        } else if(sort_order[column_name] === 'descending') {
            sort_order[column_name] = 'ascending';
            jq_colum_title.switchClass('sort-down', 'sort-up');
            sort_priority.push(column_name);
        } else {
            sort_order[column_name] = undefined;
            jq_colum_title.removeClass('sort-up');
        }
        page = 1;
        getDataFromTable();
    };

    /**
     * Setup the table headers to enable sorting for that column.
     *
     * @return void
     */
    var setupSorting = function () {
//        if (type === 'stream') {
//            sort_order = stream_sort_order;
//        } else if (type === 'rhythm') {
//            sort_order = rhythm_sort_order;
//        } else if (type === 'user') {
//            sort_order = user_sort_order;
//        }
        jQuery('.titles>.name', jq_table).click(onSortClicked.bind(null, 'name'));
        jQuery('.titles>.domain', jq_table).click(onSortClicked.bind(null, 'domain'));
        jQuery('.titles>.username', jq_table).click(onSortClicked.bind(null, 'username'));
        jQuery('.titles>.version', jq_table).click(onSortClicked.bind(null, 'version'));
        jQuery('.titles>.status', jq_table).click(onSortClicked.bind(null, 'status'));
        jQuery('.titles>.stream-kind', jq_table).click(onSortClicked.bind(null, 'stream_kind'));
        jQuery('.titles>.user-type', jq_table).click(onSortClicked.bind(null, 'user_type'));
        jQuery('.titles>.ring-ban', jq_table).click(onSortClicked.bind(null, 'ring_ban'));
    };

    /**
     * Setup the selector table using the options that were passed in.
     */
    var setup = function () {
        if (jQuery.inArray(type, valid_types) === -1) {
            throw 'Invalid type in BabblingBrook.Client.Component.Selector.setup. "' + type + '"';
        }

        if (type === 'user') {
            show_fields = user_show_fields;
        }

        if (typeof options !== 'object') {
            options = {};
        }

        if (typeof options.only_joinable_rings === 'boolean') {
            only_joinable_rings = options.only_joinable_rings;
        }

        if (typeof options.show_fields === 'object') {
            jQuery.each(options.show_fields, function(index, option) {
                show_fields[index] = option;
            });
        }

        if (type === 'user') {
            exact_match = user_exact_match;
        }
        if (typeof options.exact_match === 'undefined') {
            options.exact_match = {};
        }
        jQuery.each(options.exact_match, function (i, value) {
            exact_match[i] = value;
        });

        if (typeof options.loading_selector === 'undefined') {
            jq_loading = jq_table;
        } else {
            jq_loading = jQuery(options.loading_selector);
        }

        if (typeof options.onReady === 'function') {
            onReady = options.onReady;
        }

        if (typeof options.user_type !== 'undefined') {
            user_type = options.user_type;
        };

        jq_table.attr('id', 'selector_' + id);
        jQuery('.version', jq_table).addClass();
        jQuery('tr.search>.action>.help-icon', jq_table).attr('id', 'help_' + id);
        jQuery('tr.search>.action>.help-icon>.help-title', jq_table).attr('id', 'help_title_' + id);
        jQuery('tr.search>.action>.help-icon>.help-content', jq_table).attr('id', 'help_content_' + id);

        if (typeof options.initial_values === 'object') {
            jQuery.each(options.initial_values, function(index, value) {
                initial_values[index] = value;
            });
        }
        jQuery.each(initial_values, function(index, value) {
            jQuery('tr.search>.' + index + '>input', jq_table).val(value);
        });
        // Remove columns that are not being used.
        // This must happen after the defaults are entered. Otherwise it will cause errors.
        jQuery.each(show_fields, function (key, value) {
            if (value === false) {
                key = key.replace('_', '-');
                jQuery('.' + key, jq_table).remove();
            }
        });

        jq_selector.append(jq_table);

        jq_table_body = jQuery('tbody', jq_table);

        jQuery('tfoot .first', jq_table).click(function() {
            if (jQuery(this).hasClass('pale') === true) {
                return false;
            }
            page = 1;
            getDataFromTable();
            return false;
        });

        jQuery('tfoot .last', jq_table).click(function() {
            if (jQuery(this).hasClass('pale') === true) {
                return false;
            }
            page--;
            if (page < 1) {
                page = 1;
            }
            getDataFromTable();
            return false;
        });

        jQuery('tfoot .next', jq_table).click(function() {
            if (jQuery(this).hasClass('pale') === true) {
                return false;
            }
            page++;
            getDataFromTable();
            return false;
        });
        // If the screen width is too small then padding is stripped away.
        BabblingBrook.Client.Component.Resize.retest();

        // Refetch data if filter edited.
        jQuery('thead input, thead select', jq_table).change(function () {
            page = 1;
            getDataFromTable();
        });

        setupSorting();

        // Get generic results.
        getDataFromTable();
    };
    setup();
};