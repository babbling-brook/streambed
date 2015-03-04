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
 * @fileOverview Javascript related to a users edit streams module.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.User !== 'object') {
    BabblingBrook.Client.Page.User = {};
}

/**
 * @namespace Enables the user to edit their stream subscriptions.
 * @package JS_Client
 */
BabblingBrook.Client.Page.User.StreamSubscriptions = (function () {
    'use strict';

    /**
     * @type {boolean} Used to create history when a selector is opened. But not after the back button is presssed.
     */
    var create_history = true;
    var create_nested_history = true;

    var ux_stream_search;
    var ux_stream_suggestion;

    /**
     * Click event for the 'change versions' link being click in stream details.
     *
     * @returns {undefined}
     */
    var onChangeStreamVersionClicked = function (event) {
        event.stopPropagation();

        var stream_subscription_id = jQuery(this).parent().parent().parent().attr('data-subscription-id');
        var stream = getStreamFromSubscriptionId(stream_subscription_id);
        var jq_change_line = jQuery(this).parent();

        jq_change_line.addClass('selecting-version');
        jq_change_line.find('.change-version').addClass('hide');
        jq_change_line
            .find('.cancel-version-change')
                .removeClass('hide');

        var jq_loading_option = jQuery('#Page_StreamSubscriptions_select_version_loading_template>option').clone();
        jq_change_line
            .find('.select-new-version')
                .addClass('select-loading')
                .prepend(jq_loading_option);
        jq_change_line.find('.stream-version-selectors').removeClass('hide');
        jq_change_line.find('.version-description').addClass('hide');

        BabblingBrook.Client.Core.Interact.postAMessage(
            stream,
            'FetchStreamVersions',
            onFetchedResourceVersions.bind(null, jq_change_line)
        );
        return false;
    };


    /**
     * Click event for the 'change versions' link being click in filter details.
     *
     * @returns {undefined}
     */
    var onChangeFilterVersionClicked = function (event) {
        event.stopPropagation();

        var jq_change_line = jQuery(this).parent();
        var jq_filter = jq_change_line.parent().parent();
        var filter_subscription_id = jq_filter.attr('data-filter-id');
        var jq_stream = jq_filter.parent().parent().parent().parent();

        var stream_subscription_id = jq_stream.attr('data-subscription-id');

        var filter = getFilterFromSubscriptionId(stream_subscription_id, filter_subscription_id);

        jq_change_line
            .addClass('selecting-version')
            .find('.change-version')
                .addClass('hide');
        jq_change_line
            .find('.cancel-version-change')
                .removeClass('hide');
        jq_change_line.find('.filter-version-selectors').removeClass('hide');

        var jq_loading_option = jQuery('#Page_StreamSubscriptions_select_version_loading_template>option').clone();
        jq_change_line
            .find('.select-new-version')
                .addClass('select-loading')
                .prepend(jq_loading_option);
        jq_change_line.find('.stream-version-selectors').removeClass('hide');
        jq_change_line.find('.version-description').addClass('hide');
        BabblingBrook.Client.Core.Interact.postAMessage(
            filter,
            'FetchRhythmVersions',
            onFetchedResourceVersions.bind(null, jq_change_line)
        );
        return false;
    };

    /**
     * Populates the change version select list when a stream r rhythm versions have been fetched.
     *
     * @param {object} jq_change_line The change version row.
     * @param {object} response_data Data returned from the scientia domain.
     *
     * @returns {undefined}
     */
    var onFetchedResourceVersions = function (jq_change_line, response_data) {
        var jq_select_versions = jq_change_line.find('.select-new-version');
        var jq_select_major = jq_change_line.find('.select-new-version.major-version');
        var jq_select_minor = jq_change_line.find('.select-new-version.minor-version');
        var jq_select_patch = jq_change_line.find('.select-new-version.patch-version');
        jq_change_line.find('.option-loading').remove();
        jq_select_versions.removeClass('select-loading');
        var old_version = jq_change_line.find('.version-description').text();
        var old_version_parts = old_version.split('/');

        var jq_latest_option = jQuery('<option>latest</option>');
        if (old_version_parts[0] === 'latest') {
            jq_latest_option.attr('selected', 'selected');
        }
        jq_select_major.append(jq_latest_option);

        jq_latest_option = jQuery('<option>latest</option>');
        if (old_version_parts[1] === 'latest') {
            jq_latest_option.attr('selected', 'selected');
        }
        jq_select_minor.append(jq_latest_option);

        jq_latest_option = jQuery('<option>latest</option>');
        if (old_version_parts[2] === 'latest') {
            jq_latest_option.attr('selected', 'selected');
        }
        jq_select_patch.append(jq_latest_option);
        if (jq_change_line.hasClass('stream-version-row') === true) {
            var jq_all_option = jQuery('<option>all</option>');
            if (old_version_parts[0] === 'all') {
                jq_all_option.attr('selected', 'selected');
            }
            jq_select_major.append(jq_all_option);
            jq_all_option = jQuery('<option>all</option>');
            if (old_version_parts[1] === 'all') {
                jq_all_option.attr('selected', 'selected');
            }
            jq_select_minor.append(jq_all_option);
            jq_all_option = jQuery('<option>all</option>');
            if (old_version_parts[2] === 'all') {
                jq_all_option.attr('selected', 'selected');
            }
            jq_select_patch.append(jq_all_option);
        }

        // make a nested object of versions for easier itteration
        var major_versions = {};
        var current_major_version;
        var current_minor_version;
        var current_patch_version;
        jQuery.each(response_data.versions, function (i, version) {
            var version_parts = version.split('/');
            if (typeof current_major_version === 'undefined' || current_major_version !== version_parts[0]) {
                major_versions[version_parts[0]] = {};
                current_major_version = version_parts[0];
                current_minor_version = undefined;
                current_patch_version = undefined;
            }

            if (typeof current_minor_version === 'undefined' || current_minor_version !== version_parts[1]) {
                major_versions[version_parts[0]][version_parts[1]] = {};
                current_minor_version = version_parts[1];
                current_patch_version = undefined;
            }

            if (typeof current_patch_version === 'undefined' || current_patch_version !== version_parts[2]) {
                major_versions[version_parts[0]][version_parts[1]][version_parts[2]] = {};
                current_patch_version = version_parts[2];
            }
        });

        // Create the major version options.
        jQuery.each(major_versions, function (major_version, minor_versions) {
            var jq_option = jQuery('<option>');
            jq_option.text(major_version);
            if (major_version === old_version_parts[0]) {
                jq_option.attr('selected', 'selected');
            }
            jq_select_major.append(jq_option);

            if (major_version === old_version_parts[0]) {
                jQuery.each(minor_versions, function (minor_version, patch_versions) {
                    var jq_option = jQuery('<option>');
                    jq_option.text(minor_version);
                    if (minor_version === old_version_parts[1]) {
                        jq_option.attr('selected', 'selected');
                    }
                    jq_select_minor.append(jq_option);

                    if (minor_version === old_version_parts[1]) {
                        jQuery.each(patch_versions, function (patch_version, empty) {
                            var jq_option = jQuery('<option>');
                            jq_option.text(patch_version);
                            if (patch_version === old_version_parts[2]) {
                                jq_option.attr('selected', 'selected');
                            }
                            jq_select_patch.append(jq_option);
                        });
                    }
                });
            }
        });
    };

    /**
     * Click event handler for cancelling version changes for both streams and rhythms.
     *
     * @returns {undefined}
     */
    var onCloseVersionChange = function (event) {
        if (typeof event !== 'undefined') {
            event.stopPropagation();
        }

        jQuery(this).addClass('hide')
        var jq_change_line = jQuery(this).parent();
        jq_change_line
            .removeClass('selecting-version')
            .find('.change-version')
                .removeClass('hide');
        jq_change_line
            .find('.select-new-version')
                .find('option')
                    .not('.select-title')
                        .remove();
        jq_change_line.find('.major-version').parent().addClass('hide');
        jq_change_line.find('.version-description').removeClass('hide');
        jq_change_line
            .find('.cancel-version-change')
                .addClass('hide');

        return false;
    };

    /**
     * Handles events for both stream and filter version changes.
     *
     * @param {string} selected_version The version that was selected.
     * @param {object} jq_change_version_row A jquery reference to the version row.
     * @param {object} resource A rhythm or stream object depending on the resource_type.
     * @param {string} resource_type Is this a 'rhythm' or a 'stream'.
     *
     * @returns {undefined}
     */
    var onVersionChanged = function (selected_version, jq_change_version_row, resource, resource_type) {
        jq_change_version_row
            .removeClass('selecting-version')
            .find('.select-new-version')
                .removeClass('select-loading')
                .empty();
        jq_change_version_row
            .find('.version-description')
                .text(selected_version);

        // Update full name line.
        var resource_url = BabblingBrook.Library.makeResourceUrl(resource, '', resource_type);
        jq_change_version_row.siblings('div.full-name').children('span.full-name').text(resource_url);
        onCloseVersionChange.call(jq_change_version_row.find('.cancel-version-change'));
    };

    /**
     * Change event for when a new stream version has been selected.
     *
     * @returns {unresolved}
     */
    var onFilterVersionChanged = function (event) {
        event.stopPropagation();

        var jq_select = jQuery(this);
        var jq_change_line = jq_select.parent().parent();
        var jq_filter = jq_change_line.parent().parent();
        var filter_subscription_id = jq_filter.attr('data-filter-id');
        var jq_stream = jq_filter.parent().parent().parent().parent();
        var stream_subscription_id = jq_stream.attr('data-subscription-id');
        var filter = getFilterFromSubscriptionId(stream_subscription_id, filter_subscription_id);
        var stream = getStreamFromSubscriptionId(stream_subscription_id);

        var jq_major = jq_select.parent().find('.major-version');
        var jq_minor = jq_select.parent().find('.minor-version');
        var jq_patch = jq_select.parent().find('.patch-version');
        var selected_major = jQuery('option:selected', jq_major).text();
        var selected_minor = jQuery('option:selected', jq_minor).text();
        var selected_patch = jQuery('option:selected', jq_patch).text();
        if (jq_select.hasClass('major-version') === true) {
            selected_minor = 'latest';
            selected_patch = 'latest';
        }
        if (jq_select.hasClass('minor-version') === true) {
            selected_patch = 'latest';
        }
        var selected_version = selected_major + '/' + selected_minor + '/' + selected_patch;
        var select_version_object = BabblingBrook.Library.makeVersionObject(selected_version);

        jq_change_line.addClass('select-loading');

        BabblingBrook.Client.Core.StreamSubscriptions.changeFilterVersion(
            stream_subscription_id,
            filter_subscription_id,
            select_version_object,
            stream,
            filter,
            onVersionChanged.bind(null, selected_version, jq_change_line, filter, 'rhythm')
        );
    };

    /**
     * Change event for when a new filter version has been selected.
     *
     * @returns {unresolved}
     */
    var onStreamVersionChanged = function (event) {
        event.stopPropagation();

        var jq_select = jQuery(this);
        var stream_subscription_id = jq_select.parent().parent().parent().parent().attr('data-subscription-id');
        var stream = getStreamFromSubscriptionId(stream_subscription_id);

        var jq_major = jq_select.parent().find('.major-version');
        var jq_minor = jq_select.parent().find('.minor-version');
        var jq_patch = jq_select.parent().find('.patch-version');
        var selected_major = jQuery('option:selected', jq_major).text();
        var selected_minor = jQuery('option:selected', jq_minor).text();
        var selected_patch = jQuery('option:selected', jq_patch).text();
        if (jq_select.hasClass('major-version') === true) {
            selected_minor = 'latest';
            selected_patch = 'latest';
        }
        if (jq_select.hasClass('minor-version') === true) {
            selected_patch = 'latest';
        }

        var selected_version = selected_major + '/' + selected_minor + '/' + selected_patch;

        var jq_change_version_row = jq_select.parent().parent();
        jq_change_version_row.addClass('select-loading');

        var select_version_object = BabblingBrook.Library.makeVersionObject(selected_version);

        BabblingBrook.Client.Core.StreamSubscriptions.changeStreamVersion(
            stream_subscription_id,
            select_version_object,
            stream,
            onVersionChanged.bind(null, selected_version, jq_change_version_row, stream, 'stream')
        );
    };

    /**
     * Append a filter line to the filter list.
     *
     * @param {integer} filter_id
     *
     * @returns {undefined}
     */
    var appendFilterLine = function (filter) {
        var jq_line = jQuery('#stream_subscription_unsubscribe_filter_template>li').clone();
        jq_line.attr('data-filter-id', filter.filter_subscription_id);
        var filter_url = BabblingBrook.Library.makeRhythmUrl(filter);
        jq_line.attr('title', filter_url);
        jQuery('.filter-name', jq_line).text(filter.name);
        if (filter.locked === true) {
            jQuery('.delete', jq_line).addClass('hidden');
        }
        jQuery('#edit_streams ul.filters').append(jq_line);
    };


    /**
     * Handles clicks on 'details' links on filter rows when a stream details block is open.
     *
     * @returns {false} Cancels the click event.
     */
    var onFilterDetailsClicked = function (event) {
        event.stopPropagation();

        var jq_filter = jQuery(this).parent();
        var jq_stream = jq_filter.parent().parent().parent().parent();
        var stream_subscription_id = jq_stream.attr('data-subscription-id');

        // Hide again if allready open.
        if (jQuery('div.details', jq_filter).length) {
            jQuery('div.details', jq_filter).slideUp(250, function () {
                jq_filter.removeClass('open');
                jQuery(this).remove();
            });
            return false;
        }
        jQuery('ul.filters>li').removeClass('open');
        jq_filter.addClass('open');

        // Clear any old data.
        jQuery('div.details', jq_filter).remove();

        //close other open filter details.
        jQuery('#edit_streams ul.filters div.details').slideUp(250);

        var filter_id = jq_filter.attr('data-filter-id');
        var filter;
        var subscription = BabblingBrook.Client.Core.StreamSubscriptions.getStreamSubscriptionFromId(
            stream_subscription_id
        );
        filter = subscription.filters[filter_id];

        jq_filter.append('<div class="details"></div>');
        var jq_details = jQuery('div.details:first', jq_filter);
        jq_details.addClass('hide');
        // Placed in an object so it can be updated by the changeVersions event.
        var filter_url = BabblingBrook.Library.makeRhythmUrl(filter, '');
        var jq_filter_details = jQuery('#subscription_filter_details_template>div').clone();
        jQuery('.full-name', jq_filter_details).text(filter_url);
        jQuery('.filter-description', jq_filter_details).text(filter.description);

        var version_string = BabblingBrook.Library.makeVersionString(filter.version);
        jQuery('.version-description', jq_filter_details).text(version_string);
        jq_details.append(jq_filter_details);

        jq_details.slideDown(250);
        return false;
    };

    /**
     * Generate the click events for filter links (details and remove).
     *
     * @returns false
     */
    var onFilterDeleteClicked = function (event) {
        event.stopPropagation();

        var jq_filter = jQuery(this).parent();
        var filter_subscription_id = jq_filter.attr('data-filter-id');
        var jq_stream = jq_filter.parent().parent().parent().parent();
        var stream_subscription_id = jq_stream.attr('data-subscription-id');
        var stream = getStreamFromSubscriptionId(stream_subscription_id);
        var filter = getFilterFromSubscriptionId(stream_subscription_id, filter_subscription_id);
        jq_filter.addClass('block-loading');
        BabblingBrook.Client.Core.StreamSubscriptions.unSubscribeStreamFilter(
            stream_subscription_id,
            filter_subscription_id,
            stream,
            filter,
            function () {
                jq_filter.slideUp(250).remove();
            }
        );
        return false;
    };

    /**
     * Adds a filter to a stream after it has been selected.
     *
     * @param {object} rhythm The rhythm filter that has been selected.
     * @param {object} stream The stream that a filter is being added to.
     * @param {object} jq_stream Jquery object pointing to the row that is being edited.
     */
    var addFilterToStream = function (rhythm, stream, jq_stream) {
        var stream_subscription_id = jq_stream.attr('data-subscription-id');
        jq_stream.addClass('block-loading');
        // This is a client post and should not go through the domus domain.
        // Server will fetch filter data if not present on the server.

        BabblingBrook.Client.Core.StreamSubscriptions.subscribeStreamFilter(
            stream_subscription_id,
            stream,
            rhythm,
            /**
             * Success callback for ajax request to subscribe a filter to a users stream subscription.
             *
             * @param {object} response_data Data sent back from the request.
             *
             * @return void
             */
            function (response_data) {
                // Add the new stream to the list of filters for this stream.
                appendFilterLine(response_data.subscription);
                jq_stream.removeClass('block-loading');
            }
        );

    };

    /**
     * Closes the filter search table.
     *
     * @returns void
     */
    var closeFilterSearch = function (jq_stream) {
        jQuery('.filter-search').slideUp(250, function () {
            jQuery('.search-new-filter', jq_stream)
                .text('Search for a new filter')
                .parent().removeClass('open block-loading');
            jQuery('.filter-search').remove();
        });
    };

    /**
     * Callback that fires when the search data table is ready.
     *
     * @param {number} subscription_id The id of the stream subscription the rhythm search is in.
     * @param {object} jq_stream A jQuery object pointing to the dom for the stream that is being edited.
     * @param {number} current_page The current page number of the selector.
     *
     * @returns void
     */
    var onFilterSearchReady = function (subscription_id, jq_stream, current_page) {
        jQuery('.filter-search').slideDown(250);
        jQuery('.search-new-filter').parent().removeClass('block-loading');
        if (create_nested_history === true) {
            BabblingBrook.Client.Core.Ajaxurl.changeUrl(
                window.location.href,
                'BabblingBrook.Client.Page.User.StreamSubscriptions.reconstructRhythmSearch',
                document.title,
                [subscription_id, current_page]
            );
        } else {
            create_nested_history = true;
        }
    };


    /**
     * Returns the stream that matches a subscription id.
     *
     * @param {string} stream_subscription_id The id of the stream subscription the filter is in.
     * @param {string} filter_subscription_id The id of the filter dubscription.
     *
     * @returns {object} The stream object
     */
    var getFilterFromSubscriptionId = function (stream_subscription_id, filter_subscription_id) {
        var filter;
        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, subscribed_stream) {
            if (subscribed_stream.stream_subscription_id === stream_subscription_id) {
                jQuery.each(subscribed_stream.filters, function (j, subscribed_filter) {
                    if (subscribed_filter.filter_subscription_id === filter_subscription_id) {
                        filter = subscribed_filter;
                        return false;    // Exit the jQuery.each function.
                    }
                });
            }
            if (typeof filter !== 'undefined') {
                return false;       // Exit the jQuery.each function.
            } else {
                return true;        // Continue the jQuery.each function.
            }
        });
        return filter;
    };

    /**
     * Returns the stream that matches a subscription id.
     *
     * @param {number} subscription_id The id of the stream subscription.
     *
     * @returns {object} The stream object
     */
    var getStreamFromSubscriptionId = function (subscription_id) {
        var stream;
        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, subscribed_stream) {
            if (subscribed_stream.stream_subscription_id === subscription_id) {
                stream = subscribed_stream;
                return false;    // Exit the jQuery.each function.
            }
            return true;         // Continue the jQuery.each function.
        });
        return stream;
    };


    /**
     * Setup the filter search table.
     *
     * @param {object} The click event object.
     * @param {number} [stream_subscription_id] The id of the stream subscription the rhythm search is in.
     *      Only passed in by history pops. An actual click uses 'this' to work it out.
     * @param {number} [page] The page of results to display.
     *
     * @return false
     */
    var onSearchForFilterClicked = function (event, stream_subscription_id, page) {
        if (typeof event !== 'undefined') {
            event.stopPropagation();
        }
        var jq_stream;
        var jq_opener;
        if (typeof stream_subscription_id === 'undefined') {
            jq_opener = jQuery(this).parent();
            jq_stream = jQuery(this).parent().parent().parent().parent().parent();
            stream_subscription_id = jq_stream.attr('data-subscription-id');
        } else {
            jq_stream = jQuery('#edit_streams>li[data-subscription-id=' + stream_subscription_id + ']');
            jq_opener = jq_stream.find('.filter-search-opener');
        }

        var stream = getStreamFromSubscriptionId(stream_subscription_id);

        // Hide search if it is visible
        if (jQuery('.filter-suggestions').is(':visible')) {
            closeFilterSuggestions(jq_stream);
        }

        if (jQuery('.filter-search', jq_stream).length > 0) {
            closeFilterSearch(jq_stream);
            return false;
        }

        jq_opener.addClass('open block-loading');
        jQuery('.search-new-filter', jq_stream).text('Hide filter search');

        // Add a div to display the results.
        jq_opener.append('<div class="filter-search" style="display:none;"></div>');

        var actions = [
        {
            name : 'Subscribe',
            onClick : function (event, jq_row, row) {
                event.preventDefault();
                var rhythm = {
                    domain : row.domain,
                    username : row.username,
                    name : row.name,
                    version : {
                        major : 'latest',
                        minor : 'latest',
                        patch : 'latest'
                    }
                }
                addFilterToStream(rhythm, stream, jq_stream);
                closeFilterSearch(jq_stream);
                BabblingBrook.Client.Page.User.StreamSubscriptions.onRhythmSubscribedHook();
            }
        },
        {
            name : 'Description',
            onClick : function (event, jq_row, row) {
                event.preventDefault();
                var jq_next_row = jq_row.next();
                if (jq_next_row.hasClass('search-rhythm-description-row') === true) {
                    jQuery('.search-rhythm-description', jq_next_row).slideUp('fast', function (){
                        jq_next_row.remove();
                    });
                } else {

                    jQuery('.selector-action-description', jq_row).addClass('text-loading');
                    BabblingBrook.Client.Core.Interact.postAMessage(
                        {
                            domain : row.domain,
                            username : row.username,
                            name : row.name,
                            version : 'latest/latest/latest'
                        },
                        'FetchRhythm',
                        function (rhythm_data) {
                            jQuery('.selector-action-description', jq_row).removeClass('text-loading');
                            var jq_next_row = jQuery('#search_rhythm_description_template>tbody>tr').clone();
                            jQuery('.search-rhythm-description', jq_next_row)
                                .text(rhythm_data.rhythm.description);
                            jq_row.after(jq_next_row);
                            jQuery('.search-rhythm-description', jq_next_row)
                                .slideDown('fast')
                                .removeClass('hide');
                        },
                        function () {console.error('error fetching rhythm');}
                    );
                }

            }
        },
        {
            name : 'View',
            onReady : function(jq_row, row) {
                var url = BabblingBrook.Library.makeRhythmUrl(
                    {
                        domain : row.domain,
                        username : row.username,
                        name : row.name,
                        version : row.version,
                    },
                    'view'
                );
                jQuery('.selector-action-view', jq_row).attr('href', 'http://' + url);
            }
        }
        ];

        var search_table = new BabblingBrook.Client.Component.Selector(
            'rhythm',
            'filter',
            jQuery('.filter-search'),
            actions,
            {
                show_fields : {
                    version : false
                },
                initial_values : {
                    rhythm_category : 'sort'
                },
                onReady : onFilterSearchReady.bind(null, stream_subscription_id, jq_stream),
                additional_selector_class : 'selector-2'
            },
            page
        );

        return false;
    };

    var closeFilterSuggestions = function (jq_stream) {
        jQuery('.filter-suggestions', jq_stream).slideUp(250, function () {
            jQuery('.filter-suggest-opener', jq_stream).removeClass('open block-loading');
            jQuery('.suggest-new-filter', jq_stream).text('Show filter suggestions')
            jQuery('.filter-suggestions', jq_stream).remove();
        });
    }

    /**
     * Callback for when filter suggestions have been generated.

     * @param {string} subscription_id The id of the stream subscription that suggestions are generated for.
     * @param {object} stream The id of the stream subscription object that suggestions are generated for.
     * @param {object} jq_stream A jquery reference to the streams display location.
     * @param {object} suggestions The generated suggestions.
     *
     * @returns {undefined}
     */
    var onFetchedFilterSuggestions = function (subscription_id, stream, jq_stream, suggestions) {
        var jq_suggestions = jQuery('.filter-suggestions', jq_stream);

        if (suggestions.length === 0) {
            var jq_no_suggestions = jQuery('#no_suggestions_template>div').clone();
            jq_suggestions.append(jq_no_suggestions);
        }

        var jq_suggestions_table = jq_suggestions.find('.filter-suggestions-table');
        jQuery.each(suggestions, function (i, suggestion) {
            var url = BabblingBrook.Library.makeRhythmUrl(suggestion, 'view');
            var jq_filter = jQuery('#subscription_filter_suggestion_table_template>tbody>tr').clone();
            jq_filter.attr('data-filter-name', suggestion.name);
            jq_filter.attr('data-filter-domain', suggestion.domain);
            jq_filter.attr('data-filter-username', suggestion.username);
            jq_filter.attr('data-filter-version', suggestion.version);
            jQuery('.view-suggestion-filter', jq_filter).attr('href', 'http://' + url);
            jQuery('.rhythm', jq_filter).attr('title', url);
            jQuery('.rhythm', jq_filter).text(suggestion.name);
            jq_suggestions_table.append(jq_filter);

            jQuery('.add-suggestion-filter').click(function () {
                var rhythm = {
                    domain : jq_filter.attr('data-filter-domain'),
                    username : jq_filter.attr('data-filter-username'),
                    name : jq_filter.attr('data-filter-name'),
                    version : {
                        major : 'latest',
                        minor : 'latest',
                        patch : 'latest'
                    }
                };

                addFilterToStream(rhythm, stream, jq_stream);
                closeFilterSuggestions(jq_stream);
                return false;
            });
            jQuery('.view-suggestion-filter').click(function (jq_row) {
                var jq_filter = jQuery(this).parent().parent();
                var url = BabblingBrook.Library.makeRhythmUrl(
                    {
                        domain : jq_filter.attr('data-filter-domain'),
                        username : jq_filter.attr('data-filter-username'),
                        name : jq_filter.attr('data-filter-name'),
                        version : 'latest/latest/latest'
                    },
                    'view'
                );
                BabblingBrook.Client.Core.Ajaxurl.redirect(url);
                return false;
            });
        });

        jq_suggestions.parent().slideDown(250, function () {
            jQuery('.filter-suggest-opener', jq_stream).removeClass('block-loading');
        });

        if (create_nested_history === true) {
            BabblingBrook.Client.Core.Ajaxurl.changeUrl(
                window.location.href,
                'BabblingBrook.Client.Page.User.StreamSubscriptions.reconstructRhythmSuggestions',
                document.title,
                [subscription_id]
            );
        } else {
            create_nested_history = true;
        }

    };

    /**
     * Click event for showing filter suggestions.
     *The id of the stream subscription the suggestions are for.
     * @param {object} event Click event object.
     * @param {number} [stream_subscription_id] The id of the stream subscription the suggestions are for.
     *
     * @return {void}
     */
    var onShowFilterSuggestionsClicked = function (event, stream_subscription_id) {
        if (typeof event !== 'undefined') {
            event.stopPropagation();
        }

        var jq_stream;
        if (typeof stream_subscription_id === 'undefined') {
            jq_stream = jQuery(this).parent().parent().parent().parent().parent();
            stream_subscription_id = jq_stream.attr('data-subscription-id');
        } else {
            jq_stream = jQuery('#edit_streams>li[data-subscription-id=' + stream_subscription_id + ']');
        }
        var jq_opener = jq_stream.find('.filter-suggest-opener');
        var stream = getStreamFromSubscriptionId(stream_subscription_id);

        // Hide search if it is visible.
        if (jQuery('.filter-search').is(':visible')) {
            closeFilterSearch(jq_stream);
        }
        if (jQuery('.filter-suggestions').is(':visible')
            || jQuery('.filter-suggest-opener', jq_stream).hasClass('block-loading')
        ) {
            closeFilterSuggestions(jq_stream);
            return false;
        }

        // Add a div to display the results.
        var jq_container = jQuery('#subscription_filter_suggestion_template>div').clone();
        jq_opener
            .addClass('open block-loading')
            .append(jq_container)
            .find('.suggest-new-filter').text('Hide rhythm suggestions');

        BabblingBrook.Client.Core.Suggestion.fetch(
            'stream_filter_suggestion',
            onFetchedFilterSuggestions.bind(null, stream_subscription_id, stream, jq_stream),
            {
                domain : stream.domain,
                username : stream.username,
                name : stream.name,
                version : BabblingBrook.Library.makeVersionString(stream.version)
            }
        );

        return false;
    };

    /**
     * Append a moderation line to a stream description.
     *
     * @param {object} A moderatiion ring row from the stream suscriptions object.
     *
     * @return {undefined}
     */
    var appendModerationLine = function (ring) {
        var jq_line = jQuery('#moderation_line_template>li').clone();
        var ring_url = ring.domain + '/' + ring.username;
        jq_line.attr('title', ring_url);
        jQuery('.ring-name', jq_line).text(ring.username);
        jq_line.attr('data-ring-subscription-id', ring.ring_subscription_id);
        jQuery('.details', jq_line).attr('href', 'http://' + ring_url + '/profile');
        jQuery('#edit_streams ul.moderation-rings').append(jq_line);
    };

    /**
     * Generate the click events for moderation links (details and remove)
     * @param jq_stream Object Jquery Object for the stream UL element that events are being attatched to.
     * @param stream Array
     */
    var onRemoveModerationRingClicked = function (event) {
        if (typeof event !== 'undefined') {
            event.stopPropagation();
        }

        var jq_row = jQuery(this).parent();
        var ring_subscription_id = jq_row.attr('data-ring-subscription-id');
        var jq_stream = jq_row.parent().parent().parent().parent();
        var stream_subscription_id = jq_stream.attr('data-subscription-id');
        var stream = getStreamFromSubscriptionId(stream_subscription_id);
        var ring_name = jq_row.attr('title');
        var ring_parts = ring_name.split('/');
        var ring = {
            domain : ring_parts[0],
            username : ring_parts[1]
        }
        jq_row.addClass('block-loading');

        BabblingBrook.Client.Core.StreamSubscriptions.unsubscribeStreamModerationRing(
            stream_subscription_id,
            ring_subscription_id,
            stream,
            ring,
            function(response_data) {
                jq_row.slideUp(250).remove();
            }
        );

    };

    /**
     * Sends a new moderation line to the DB for adding to this users domus domain.
     *
     * @param {object} jq_stream jQuery object holding the details of the stream the moderation line is being added to.
     * @param {string} ring_name The url of the ring that is being added.
     *
     * @return {void}
     */
    var addModerationLine = function (stream, stream_subscription_id, ring, jq_stream) {
        // This is a client post and should not go through the domus domain.
        // The server will fetch ring details if they are not present.


        BabblingBrook.Client.Core.StreamSubscriptions.subscribeStreamModerationRing(
            stream_subscription_id,
            stream,
            ring,
            function(response_data) {
                appendModerationLine(response_data.subscription);
            }
        );
    };

    var closeRingSuggestions = function (jq_stream) {
        jQuery('#edit_streams .moderation-suggestions').slideUp(250, function () {
            jQuery('#edit_streams .suggest-new-moderation-ring')
                .text('Show moderation suggestions')
                .parent()
                    .removeClass('open block-loading');
            jQuery('#edit_streams .moderation-suggestions').remove();
        });
    };

    /**
     * Callback for when the moderation ring_suggestions have been fetched.
     *
     * @param {number} subscription_id The id of the stream subscription the rhythm search is in.
     * @param {object} suggestions The suggestions that have been generated by the rhythm.
     *
     * @returns {void}
     */
    var onRingSuggestionsFetched = function (stream_subscription_id, stream, jq_stream, jq_opener, suggestions) {
        var jq_suggestions = jQuery('#edit_streams #moderation_suggestions');
        if (suggestions.length === 0) {
            var jq_no_suggestions = jQuery('#no_suggestions_template>div').clone();
            jQuery('.moderation-suggestions', jq_stream).append(jq_no_suggestions);
        }
        jQuery.each(suggestions, function (i, suggestion) {
            var jq_line = jQuery('#moderation_ring_suggetstion_line_template>tbody>tr').clone();
            jq_line.attr('data-ring-name', suggestion.username);
            jq_line.attr('data-ring-domain', suggestion.domain);
            jQuery('.view-suggested-moderation-ring', jq_line)
                .attr('href', 'http://' + suggestion.domain + '/' + suggestion.username);
            jQuery('.ring', jq_line)
                .attr('title', suggestion.domain + '/' + suggestion.username)
                .text(suggestion.username);
            jq_suggestions.append(jq_line);
        });

        jQuery('.add-suggested-moderation-ring').click(function () {
            var jq_ring = jQuery(this).parent().parent();
            var new_ring = {
                domain : jq_ring.attr('data-ring-domain'),
                username : jq_ring.attr('data-ring-name')
            };
            addModerationLine(stream, stream_subscription_id, new_ring, jq_stream);
            closeRingSuggestions(jq_stream);
            return false;
        });

        jQuery('.join-moderation-ring-filter').click(function () {
            var jq_ring = jQuery(this).parent().parent();
            var username = jq_ring.attr('data-ring-name');
            var domain = jq_ring.attr('data-ring-domain');
            var url =  'http://' + domain + '/' + username + '/profile';
            BabblingBrook.Client.Core.Ajaxurl.redirect(url);
            return false;
        });

        jq_suggestions.parent().slideDown(250, function () {
            jq_suggestions.parent().parent().removeClass('block-loading');
        });

        if (create_nested_history === true) {
            BabblingBrook.Client.Core.Ajaxurl.changeUrl(
                window.location.href,
                'BabblingBrook.Client.Page.User.StreamSubscriptions.reconstructRingSuggestions',
                document.title,
                [stream_subscription_id]
            );
        } else {
            create_nested_history = true;
        }
    };

    /**
     * Setup the filter suggestion feature.
     *
     * @param {number} subscription_id The id of the stream subscription the rhythm search is in.
     *
     * @return {undefined}
     */
    var onSuggestModerationRingsClicked = function (event, stream_subscription_id) {
        if (typeof event !== 'undefined') {
            event.stopPropagation();
        }

        var jq_stream;
        if (typeof stream_subscription_id === 'undefined') {
            jq_stream = jQuery(this).parent().parent().parent().parent().parent();
            stream_subscription_id = jq_stream.attr('data-subscription-id');
        } else {
            jq_stream = jQuery('#edit_streams>li[data-subscription-id=' + stream_subscription_id + ']');
        }
        var jq_opener = jq_stream.find('.suggestion-moderation-rings-opener');
        var stream = getStreamFromSubscriptionId(stream_subscription_id);


        if (jq_opener.hasClass('open') === true) {
            closeRingSuggestions(jq_stream);
            return false;
        }

        // Hide search if it is visible.
        if (jq_opener.hasClass('open') === true) {
            closeRingSearch(jq_stream);
        }

        // Add a div to display the results.
        var jq_container = jQuery('#moderation_ring_suggestion_container_template>div').clone();
        jQuery('table', jq_container).attr('id', 'moderation_suggestions');
        jq_opener
            .append(jq_container)
            .addClass('open block-loading')
            .find('#edit_streams .suggest-new-moderation-ring').text('Hide moderation suggestions');

        BabblingBrook.Client.Core.Suggestion.fetch(
            'stream_ring_suggestion',
            onRingSuggestionsFetched.bind(null, stream_subscription_id, stream, jq_stream, jq_opener),
            {
                domain : stream.domain,
                username : stream.username,
                name : stream.name,
                version : BabblingBrook.Library.makeVersionString(stream.version)
            }
        );

        return false;
    };

    /**
     * Closes the ring search table.
     *
     * @returns void
     */
    var closeRingSearch = function (jq_stream) {
        jQuery('#moderation_ring_search', jq_stream).slideUp(250, function () {
            jQuery('.search-moderation-rings-opener', jq_stream).removeClass('open block-loading');
            jQuery('.search-new-moderation-ring', jq_stream).text('Search for a moderation ring');
            jQuery('#moderation_ring_search', jq_stream).remove();
        });
    };

    /**
     * Callback that fires when the ring search data table is ready.
     *
     * @returns void
     */
    var onRingSearchReady = function (subscription_id, current_page) {
        jQuery('#moderation_ring_search').slideDown(250);
        jQuery('#edit_streams .search-new-moderation-ring').parent().removeClass('block-loading');

        if (create_nested_history === true) {
            BabblingBrook.Client.Core.Ajaxurl.changeUrl(
                window.location.href,
                'BabblingBrook.Client.Page.User.StreamSubscriptions.reconstructRingSearch',
                document.title,
                [subscription_id, current_page]
            );
        } else {
            create_nested_history = true;
        }
    }

    /**
     * Setup the link for searching moderation rings.
     *s
     * @param {number} stream_subscription_id The id of the stream subscription the rhythm search is in.
     * @param {number} page The page of results to display.
     *
     * @return void
     */
    var onSearchForModerationRingsClicked = function (event, stream_subscription_id, page) {
        if (typeof event !== 'undefined') {
            event.stopPropagation();
        }

        var jq_stream;
        if (typeof stream_subscription_id === 'undefined') {
            jq_stream = jQuery(this).parent().parent().parent().parent().parent();
            stream_subscription_id = jq_stream.attr('data-subscription-id');
        } else {
            jq_stream = jQuery('#edit_streams>li[data-subscription-id=' + stream_subscription_id + ']');
        }
        var jq_opener = jq_stream.find('.search-moderation-rings-opener');
        var stream = getStreamFromSubscriptionId(stream_subscription_id);

        // Hide search if it is visible.
        if (jQuery('.suggestion-moderation-rings-opener', jq_stream).hasClass('open')) {
            closeRingSuggestions(jq_stream);
        }

        if (jq_opener.hasClass('open') === true) {
            closeRingSearch();
            return false;
        }

        jq_opener.addClass('open block-loading');
        jQuery('.search-new-moderation-ring', jq_opener).text('Hide moderation ring search');

        jq_opener.append('<div id="moderation_ring_search" style="display:none;"></div>');
        var jq_search = jQuery('#moderation_ring_search');
        var actions = [
        {
            name : 'Subscribe',
            onClick : function (event, jq_row) {
                event.preventDefault();
                var new_ring = {
                    domain : jQuery('.domain', jq_row).text(),
                    username : jQuery('.username', jq_row).text()
                };
                addModerationLine(stream, stream_subscription_id, new_ring, jq_stream);
                closeRingSearch();
                BabblingBrook.Client.Page.User.StreamSubscriptions.onModerationRingSubscribedHook();
            },
            onReady : function (jq_row, row) {
                var subscribed = false;
                var ring_name = row.domain + '/' + row.username;
                jQuery.each(stream.rings, function (i, ring) {
                    var subscribed_ring_name = ring.domain + '/' + ring.username;
                    if (subscribed_ring_name === ring_name) {
                        subscribed = true;
                    }
                });
                if (subscribed === true) {
                    jQuery('.selector-action-subscribe', jq_row).addClass('hidden');
                }
            }
        },
        {
            name : 'View',
            onReady : function(jq_row, row) {
                var url =  'http://' + row.domain + '/' + row.username + '/profile';
                jQuery('.selector-action-view', jq_row).attr('href', url);
            }
        }
        ];
        var search_table = new BabblingBrook.Client.Component.Selector(
            'user',
            'moderation_ring_search',
            jq_search,
            actions,
            {
                initial_values : {
                    user_type : 'ring',
                },
                onReady : onRingSearchReady.bind(null, stream_subscription_id),
                user_type : 'ring',
                additional_selector_class : 'selector-2'
            },
            page

        );

        return false;
    };

    /**
     * Callback to display a subscriptions details.
     *
     * @param {object} event The jQuery event object.
     * @param {string} [subscription_id] The id of the stream to open up.
     *      Only required if manually opening. The 'details' link is caluclated automatically.
     *
     * @returns {undefined}
     */
    var onDisplayStreamDetails = function (event, subscription_id) {
        if (typeof event !== 'undefined') {
            event.stopPropagation();
        }
        if (typeof subscription_id !== 'string') {
            subscription_id = jQuery(this).parent().attr('data-subscription-id');
        }
        var jq_stream = jQuery('#edit_streams>li[data-subscription-id=' + subscription_id + ']');

        // Prevents double click issues.
        if (jq_stream.hasClass('opening') === true) {
            return false;
        }

        // Hide again if allready open.
        if (jq_stream.hasClass('open') === true) {
            jQuery('div.details', jq_stream).slideUp(250, function () {
                jq_stream.removeClass('opening').removeClass('open', 250);
                jQuery(this).remove();
            });
            return false;
        }

        jq_stream.addClass('opening').toggleClass('open', 250, function () {
            jq_stream.removeClass('opening');
        });

        // Clear any old data.
        jQuery('div.details', jq_stream).remove();

        //close other open details.
        jQuery('#edit_streams>li>div.details').not(jq_stream).slideUp(250, function () {
            jQuery(this).parent().toggleClass('open', 250);
            jQuery(this).remove();
        });

        // This is necessary to ensure that the old table does not interfere with the new one,
        // due to use of slide up to hide the old one.
        jQuery('.filter-options', jq_stream).remove();
        // Find location in BabblingBrook.Client.User.StreamSubscriptions.
        var stream = getStreamFromSubscriptionId(subscription_id);
        jq_stream.append('<div class="details"></div>');
        var jq_details = jQuery('div.details:first', jq_stream);
        jq_details.addClass('hide');
    //    jq_details.removeClass('hide');
        var stream_url = BabblingBrook.Library.makeStreamUrl(stream);
        var jq_details_content = jQuery('#stream_details_line_template>div').clone();
        jQuery('span.full-name', jq_details_content).text(stream_url);
        jQuery('.stream-description', jq_details_content).text(stream.description);
        var version_string = BabblingBrook.Library.makeVersionString(stream.version);
        jQuery('.version-description', jq_details_content).text(version_string);

        jq_details.append(jq_details_content);
        if (BabblingBrook.Settings.feature_switches['CHANGE_STREAM_MODERATION_RING'] === false) {
            jQuery('.moderation-block', jq_details).addClass('hide');
        }

        // Setup filters
        jQuery.each(stream.filters, function (i, filter) {
            appendFilterLine(filter);
        });

        // Setup the moderation ring features
        jQuery.each(stream.rings, function (i, ring) {
            appendModerationLine(ring);
        });

        jq_details
            .slideDown(250)
            .removeClass('hide');
        jq_details.parent().removeClass('block-loading');

        if (create_history === true) {
            BabblingBrook.Client.Core.Ajaxurl.changeUrl(
                window.location.href,
                'BabblingBrook.Client.Page.User.StreamSubscriptions.reconstructStreamSubscription',
                document.title,
                [subscription_id]
            );
        } else {
            create_history = true;
        }

        return false;
    };

    /**
     * Setup a line in the list of streams that the user is subscribed to.
     *
     * @param {object} stream A standard stream name object extended with the StreamSubscription attributes.
     *
     * @returns {undefined}
     */
    var setupSubscribedLine = function (stream) {
        var jq_line = jQuery('#subscribed_line_template>li').clone();
        jq_line.attr('data-subscription-id', stream.stream_subscription_id);
        jQuery('.stream-name', jq_line).text(stream.name);
        if (stream.locked === true) {
            jQuery('.delete', jq_line).addClass('hidden');
        }
        jQuery('#edit_streams').append(jq_line);

    };

    /**
    * Add a stream to a users subscriptions.
    *
    * @param {string} name
    * @param {string} domain
    * @param {string} username
    * @param {string} version
    * @param {object} jq_row
    */
    var addStreamCallback = function (name, domain, username, version, jq_row) {
        jq_row.addClass('block-loading');
        // This is a client post and should not go through the domus domain.
        // Server will fetch stream data if it is not available locally.
        var stream = {
            name : name,
            domain : domain,
            username : username,
            version : BabblingBrook.Library.makeVersionObject(version)
        };

        BabblingBrook.Client.Core.StreamSubscriptions.subscribeStream(
            stream,
            function(response_data) {
                jq_row.removeClass('block-loading');
                setupSubscribedLine(response_data.subscription);
                BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSubscribedHook();
            }
        );
    };

    /**
     * Setsup the search table for suggestion new streams.
     *
     * @return void
     */
    var setupStreamSuggestions = function () {

        var suggestions_opener_text = jQuery('#stream_suggestions_opener_message_template').text();
        var suggestions_closer_text = jQuery('#stream_suggestions_closer_message_template').text();
        var jq_stream_suggestions = jQuery('#stream_suggestions_opener');
        ux_stream_suggestion = new BabblingBrook.Client.Component.Suggestions(
            jq_stream_suggestions,
            suggestions_opener_text,
            suggestions_closer_text,
            'stream_suggestion',
            'streams_to_subscribe',
            'BabblingBrook.Client.Page.User.StreamSubscriptions.reconstructStreamSuggestions',
            [
                {
                    name : 'Subscribe',
                    onClick : function (suggestion, jq_row) {
                        addStreamCallback(
                            suggestion.name,
                            suggestion.domain,
                            suggestion.username,
                            'latest/latest/latest',
                            jq_row
                        );
                    }
                },
                {
                    name : 'Description',
                    onClick : function(suggestion, jq_row) {
                        var jq_next_row = jq_row.next();
                        if (jq_next_row.hasClass('search-stream-description-row') === true) {
                            jQuery('.search-stream-description', jq_next_row).slideUp('fast', function (){
                                jq_next_row.remove();
                            });
                        } else {
                            jQuery('.selector-action-description', jq_row).addClass('text-loading');
                            BabblingBrook.Client.Core.Interact.postAMessage(
                                {
                                    stream : {
                                        domain : suggestion.domain,
                                        username : suggestion.username,
                                        name : suggestion.name,
                                        version : suggestion.version
                                    }
                                },
                                'FetchStream',
                                function (stream) {
                                    jQuery('.selector-action-description', jq_row).removeClass('text-loading');
                                    var jq_next_row = jQuery('#search_stream_description_template>tbody>tr').clone();
                                    jQuery('.search-stream-description', jq_next_row).text(stream.description);
                                    jq_row.after(jq_next_row);
                                    jQuery('.search-stream-description', jq_next_row)
                                        .slideDown('fast')
                                        .removeClass('hide');
                                },
                                function () {console.error('error fetching stream');}
                            );
                        }
                    }
                },
                {
                    name : 'View',
                    onClick : function (suggestion) {
                        var url = BabblingBrook.Library.makeStreamUrl(suggestion, '');
                        BabblingBrook.Client.Core.Ajaxurl.redirect(url);
                    }
                }
            ]
        );
    };

    /**
     * Setsup the search table for finding new streams.
     *
     * @param page The page of results to show.
     *
     * @return void
     */
    var setupStreamSearch = function(page) {
        BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSearchOpenedHook();
        // Add a div to display the results.
        var actions = [
        {
            name : 'Subscribe',
            onClick : function (event, jq_row) {
                event.preventDefault();
                var name = jQuery('.name', jq_row).text();
                var domain = jQuery('.domain', jq_row).text();
                var username = jQuery('.username', jq_row).text();
                var version = {};
                addStreamCallback(name, domain, username, 'latest/latest/latest', jq_row);
            }
        },
        {
            name : 'Description',
            onClick : function(event, jq_row, row) {
                event.preventDefault();
                var jq_next_row = jq_row.next();
                if (jq_next_row.hasClass('search-stream-description-row') === true) {
                    jQuery('.search-stream-description', jq_next_row).slideUp('fast', function (){
                        jq_next_row.remove();
                    });
                } else {
                    var name = jQuery('.name', jq_row).text();
                    var domain = jQuery('.domain', jq_row).text();
                    var username = jQuery('.username', jq_row).text();
                    // Fetching the stream directly because don't know exact version to fetch.

                    jQuery('.selector-action-description', jq_row).addClass('text-loading');
                    BabblingBrook.Client.Core.Interact.postAMessage(
                        {
                            stream : {
                                domain : domain,
                                username : username,
                                name : name,
                                version : 'latest/latest/latest'
                            }
                        },
                        'FetchStream',
                        function (streams) {
                            jQuery('.selector-action-description', jq_row).removeClass('text-loading');
                            var jq_next_row = jQuery('#search_stream_description_template>tbody>tr').clone();
                            jQuery('.search-stream-description', jq_next_row)
                                .text(streams.streams[0].description);
                            jq_row.after(jq_next_row);
                            jQuery('.search-stream-description', jq_next_row)
                                .slideDown('fast')
                                .removeClass('hide');
                        },
                        function () {console.error('error fetching stream');}
                    );
                }
            }
        },
        {
            name : 'View',
            onReady : function(jq_row, row) {
                var url = BabblingBrook.Library.makeStreamUrl(row, '', false);
                jQuery('.selector-action-view', jq_row).attr('href', url);
            }
        }
        ];
        var search_table = new BabblingBrook.Client.Component.Selector(
            'stream',
            'stream',
            jQuery('#stream_search_container'),
            actions,
            {
                initial_values : {
                    rhythm_category : 'sort',
                    domain : window.location.hostname
                },
                onReady : ux_stream_search.onChange,// onStreamSearchReady,
                onBeforeRedraw : ux_stream_search.onBeforeChange,// onStreamSearchRedraw,
                show_fields : {
                    version : false
                },
                additional_selector_class : 'selector-2'
            },
            page
        );

        return false;
    };

    /**
     * Sets up all delete stream subscription links in an .on handler.
     * @returns {undefined}
     */
    var onUnsubscribeStreamClicked = function(event) {
        event.stopPropagation();

        var jq_stream = jQuery(this).parent();
        if (jq_stream.hasClass('block-loading') === true) {
            return false;
        }
        var subscription_id = jQuery(this).parent().attr('data-subscription-id');
        var stream = getStreamFromSubscriptionId(subscription_id);

        jq_stream.addClass('block-loading');

        BabblingBrook.Client.Core.StreamSubscriptions.unsubscribeStream(
            stream,
            subscription_id,
            function(response_data) {
                jq_stream.removeClass('block-loading');
                jq_stream.slideUp(250, function() {
                    jq_stream.remove()
                });
            }
        );
        return false;
    };

    return {
        /**
         * Page constructor.
         *
         * @return void
         */
        construct : function() {
            BabblingBrook.Client.Core.Loaded.onStreamSubscriptionsLoaded( function () {
                // Setup the list of streams the user is subscribed to.
                jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, stream) {
                    setupSubscribedLine(stream);
                });

                BabblingBrook.Client.Core.Loaded.setStreamSubscriptionsPageLoaded();

                var streams_opener_text = jQuery('#stream_search_opener_message_template').text();
                var streams_closer_text = jQuery('#stream_search_closer_message_template').text();
                ux_stream_search = new BabblingBrook.Client.Component.TableOpenerUX(
                    streams_opener_text,
                    streams_closer_text,
                    jQuery('#stream_search_opener'),
                    jQuery('#stream_search_container'),
                    '#selector_stream',
                    setupStreamSearch.bind(null, 1),
                    'BabblingBrook.Client.Page.User.StreamSubscriptions.reconstructStreamSearch'
                );

                setupStreamSuggestions();

                jQuery('#page').on('click', '#edit_streams>li>a.details', onDisplayStreamDetails);

                jQuery('#page').on('click', '#edit_streams>li>a.delete', onUnsubscribeStreamClicked);

                jQuery('#edit_streams')
                    .on('click', '.stream-version-row .change-version', onChangeStreamVersionClicked);

                jQuery('#edit_streams').on('click', '.cancel-version-change', onCloseVersionChange);

                jQuery('#edit_streams').on('change', '.stream-versions', onStreamVersionChanged);

                jQuery('#edit_streams').on('click', '.filter-details', onFilterDetailsClicked);

                jQuery('#edit_streams')
                    .on('click', '.filter-version-row .change-version', onChangeFilterVersionClicked);

                jQuery('#edit_streams').on('change', '.filter-versions', onFilterVersionChanged);

                jQuery('#edit_streams').on('click', '.filter-delete', onFilterDeleteClicked);

                jQuery('#edit_streams').on('click', '.suggest-new-filter', onShowFilterSuggestionsClicked);

                jQuery('#edit_streams').on('click', '.search-new-filter', onSearchForFilterClicked);

                jQuery('#edit_streams').on('click', '.search-new-moderation-ring', onSearchForModerationRingsClicked);

                jQuery('#edit_streams').on('click', '.moderation-rings .delete', onRemoveModerationRingClicked);

                jQuery('#edit_streams').on('click', '.suggest-new-moderation-ring', onSuggestModerationRingsClicked);

            });

        },

        /**
         * Reconstructs the stream search selector when the back button is pressed.
         *
         * @returns {undefined}
         */
        reconstructStreamSearch : function (page) {
            create_history = false;
            setupStreamSearch(page);
        },

        /**
         * Reconstructs the stream search selector when the back button is pressed.
         *
         * @returns {undefined}
         */
        reconstructStreamSuggestions : function () {
            create_history = false;
            ux_stream_suggestion.autoOpen();
        },

        /**
         * Reconstructs the suggestion selector when the back button is pressed.
         *
         * @param {object} subscription_id The id of the stream subscription to reopen.
         *
         * @returns {undefined}
         */
        reconstructRhythmSuggestions : function (subscription_id) {
            create_history = false;
            create_nested_history = false;
            onDisplayStreamDetails(undefined, subscription_id);
            onShowFilterSuggestionsClicked(undefined, subscription_id);
        },

        /**
         * Reconstructs the suggestion selector when the back button is pressed.
         *
         * @param {object} subscription_id The id of the stream subscription to reopen.
         *
         * @returns {undefined}
         */
        reconstructRingSuggestions : function (subscription_id) {
            create_history = false;
            create_nested_history = false;
            onDisplayStreamDetails(undefined, subscription_id);
            onSuggestModerationRingsClicked(undefined, subscription_id);
        },

        /**
         * Reconstructs an open stream subscription details when the back button is pressed.
         *
         * @param subscription_id The id of the stream subscription to reopen.
         *
         * @returns {undefined}
         */
        reconstructStreamSubscription : function (subscription_id) {
            create_history = false;
            onDisplayStreamDetails(undefined, subscription_id);
        },

        /**
         * Reconstructs the rhythm search selector when the back button is pressed.
         *
         * Also opens up the relevant stream subscription
         *
         * @returns {undefined}
         */
        reconstructRhythmSearch : function (subscription_id, page) {
            create_history = false;
            create_nested_history = false;
            onDisplayStreamDetails(undefined, subscription_id);
            onSearchForFilterClicked(undefined, subscription_id, page);
        },

        /**
         * Reconstructs the ring search selector when the back button is pressed.
         *
         * Also opens up the relevant stream subscription
         *
         * @returns {undefined}
         */
        reconstructRingSearch : function (subscription_id, page) {
            create_history = false;
            create_nested_history = false;
            onDisplayStreamDetails(undefined, subscription_id);
            onSearchForModerationRingsClicked(undefined, subscription_id, page);
        },

        /**
         * A hook used by tutorials to detect when a stream has been subscribed to.
         */
        onStreamSubscribedHook : function () {},

        /**
         * A hook used by tutorials to detect when a stream sort rhythm has been subscribed to.
         */
        onRhythmSubscribedHook : function () {},

        /**
         * A hook used by tutorials to detect when the 'search for more streams' link has been clicked.
         *
         * @returns {undefined}
         */
        onStreamSearchOpenedHook : function () {},

        /**
         * Opens the stream search table.
         *
         * @returns {undefined}
         */
        openStreamSearch : function () {
            setupStreamSearch(1);
        },

        /**
         * A hook used by tutorials to detect when a stream sort rhythm has been subscribed to.
         */
        onModerationRingSubscribedHook : function () {}
    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.User.StreamSubscriptions.construct();
    jQuery('#choose_stream a.suggestions').trigger('click');
});