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
 * @fileOverview Used to update the sidebar displayed with streams and posts.
 * @author Sky Wickenden
 */

/**
 * @namespace Used to update the sidebar displayed with streams and posts.
 * @package JS_Client
 */
BabblingBrook.Client.Component.StreamSideBar = (function () {
    'use strict';

    /**
     * The currently loaded set of filters.
     *
     * @type objects
     */
    var page_filters = [];

    /**
     * Success callback for subscribing a user.
     *
     * @param {object} jq_subscribe A jQuery object represent the subscribe/unsubscribe link.
     * @param {object} callback_data Data returned with the ajax request.
     * @param {boolean} callback_data.success Was the request successful.
     *
     * @return void
     */
    var onSubscribed = function (jq_subscribe, callback_data) {
        jq_subscribe
            .removeClass('loading subscribe')
            .addClass('unsubscribe');
        jQuery('a', jq_subscribe)
            .text('Unsubscribe')
            .attr('data-subscription-id', callback_data.subscription.stream_subscription_id);
    }

    /**
     * Success callback for removing a subscription to a stream for a user.
     *
     * @param {object} jq_subscribe A jQuery object represent the subscribe/unsubscribe link.
     * @param {object} callback_data Data returned with the ajax request.
     * @param {boolean} callback_data.success Was the request successful.
     *
     * @return void
     */
    var onUnsubscribe = function (jq_subscribe, callback_data) {
        jq_subscribe
            .removeClass('loading unsubscribe')
            .addClass('subscribe');
        jQuery('a', jq_subscribe)
            .text('Subscribe')
            .attr('data-subscription-id', '');
    }

    /**
     * Callback for clicks on the subscribe/unsubscribe link.
     *
     * @param {object} jq_subscribe A jQuery object represent the subscribe/unsubscribe link.
     * @param {object} stream_data Data about the stream. See BabblingBrook.models.stream for a full description.
     *
     * @return false Cancels the user click event to prevent a link being followed.
     */
    var onSubscribedClicked = function (jq_subscribe, stream_data) {

        // This process can be overridden by a hook used in the tutorials.
        if (BabblingBrook.Client.Component.StreamSideBar.onSubscribeHook(jq_subscribe, stream_data) === true) {
            return;
        }

        jq_subscribe.addClass('loading');
        if(jq_subscribe.hasClass('unsubscribe') === true) {
            BabblingBrook.Client.Core.StreamSubscriptions.unsubscribeStream(
                {
                    name : stream_data.name,
                    domain : stream_data.domain,
                    username : stream_data.username,
                    version : BabblingBrook.Library.makeVersionObject(stream_data.version)
                },
                jq_subscribe.find('a').attr('data-subscription-id'),
                onUnsubscribe.bind(null, jq_subscribe)
            );

        } else {
            BabblingBrook.Client.Core.StreamSubscriptions.subscribeStream(
                {
                    name : stream_data.name,
                    domain : stream_data.domain,
                    username : stream_data.username,
                    version : BabblingBrook.Library.makeVersionObject(stream_data.version)
                },
                onSubscribed.bind(null, jq_subscribe)
            );
        }
        return false;
    };

    /**
     * If fetching suggestions fails, don't show the user an error, just report it to the console.
     *
     * @param {string} text_to_fetch_suggestions_for The text used to search for suggestions .
     *
     * @return void
     */
    var onTasksClientParamFetchParamsFailed = function (text_to_fetch_suggestions_for) {
        console.error('Error whilst fetching suggestions for tasks stream meta task client parameter');
        console.log(text_to_fetch_suggestions_for);
    };

    /**
     * Callback for when open list suggestions have been fetched. Pass the data on to the real callback.
     *
     * @param {function} onSuggestionsFetched The callback to pass the suggestions array to.
     * @param {object} suggestion_data The data object returned from the domus domiain.
     * @param {array} suggetsion_data.suggestions An array of suggestion strings.
     *
     * @return {void}
     */
    var onOpenListSuggestionsFetched = function (onSuggestionsFetched, suggestion_data) {
        onSuggestionsFetched(suggestion_data.suggestions);
    };

    /**
     * Fetch suggestions for teh task stream parameter 'meta task'.
     *
     * @param {type} text_to_fetch_suggestions_for The contents of the client_param textbox.
     * @param {type} onSuggestionsFetched The callback to call when the suggestions have been fetched.
     *
     * @returns {void}
     */
    var onTasksClientParamChanged = function (text_to_fetch_suggestions_for, onSuggestionsFetched) {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                text_to_fetch_suggestions_for : text_to_fetch_suggestions_for,
                stream : {
                    domain : BabblingBrook.Library.extractDomain(window.location.href),
                    username : BabblingBrook.Library.extractUsername(window.location.href),
                    name : BabblingBrook.Library.extractName(window.location.href),
                    version : BabblingBrook.Library.extractVersion(window.location.href)
                },
                field_id : 6
            },
            'OpenListSuggestionsFetch',
            onOpenListSuggestionsFetched.bind(null, onSuggestionsFetched),
            onTasksClientParamFetchParamsFailed.bind(null, text_to_fetch_suggestions_for)
        );
    };

    /**
     * Show any client paramaters for this filter.
     *
     * @param {object} filter The rhythm object for the selected filter.
     * @param {function} onParamSearch Callback for when param filters are applied.
     *
     * @returns {void}
     */
    var showClientParameters = function (filter, onParamSearch) {
        if (typeof filter.params === 'undefined') {
            return;
        }

        // Don't redisplay if the rhythm has not changed - it would remove the connents of the param textfields.
        var old_rhythm_url = jQuery('#sidebar_extra .client-params').attr('data-rhythm_url');
        var new_rhythm_url = BabblingBrook.Library.makeRhythmUrl(filter);
        if (new_rhythm_url === old_rhythm_url) {
            return;
        }

        jQuery('#sidebar_extra .client-params')
            .empty()
            .addClass('hide')
            .attr('data-rhythm_url', new_rhythm_url);

        for(var i = 0; i < filter.params.length; i++) {
            var jq_param_row = jQuery('#client_params_sidebar_template>div').clone();
            jQuery('.client-param-label', jq_param_row).text(filter.params[i].name);
            if (filter.params[i].hint.length > 0) {
                jQuery('.help-icon', jq_param_row).attr('id', 'help_param' + i);
                jQuery('.help-title', jq_param_row)
                    .attr('id', 'help_title_param' + i)
                    .text(filter.params[i].name);
                jQuery('.help-content', jq_param_row)
                    .attr('id', 'help_content_param' + i)
                    .text(filter.params[i].hint);
            } else {
                jQuery('.help-icon', jq_param_row).remove();
            }

            jQuery('#sidebar_extra .client-params')
                .append(jq_param_row)
                .removeClass('hide');

            // Special case for tasks stream paramaters.
            if (window.location.pathname.indexOf('sky/stream/tasks/') !== -1 && filter.params[i].name === 'meta task') {
                BabblingBrook.Client.Component.HelpHints.attatch(
                    jQuery('input', jq_param_row),
                    onTasksClientParamChanged,
                    function () {},
                    false
                );
            }
        }
        if (filter.params.length > 0) {
            var jq_apply = jQuery('#client_params_apply_template>div').clone();
            jQuery('#sidebar_extra .client-params').append(jq_apply);

            jQuery('.client-param-apply', jq_apply).click(function () {
                var params = {};
                jQuery('#sidebar_extra .client-params .client-param-row').each(function(){
                    var jq_row = jQuery(this);
                    var name = jq_row.find('.client-param-label').text();
                    var param = jq_row.find('input').val();
                    params[name] = param;
                });
                onParamSearch(params);
            });

        }
    };

    /**
     * Show a subscribed link depending on if the user is subscribed or not.
     *
     * Ignores the version numbers.
     *
     * @param {string} stream_url The url of the stream to subscribe to.
     * @param stream_data A standard stream object. See BabblingBrook.Models.stream
     *
     * @returns {void}
     */
    var displaySubscribedLink = function (stream_url, stream_data) {
        if (BabblingBrook.Settings.feature_switches['SUBSCRIBE_LINK'] === false) {
            return;
        }
        var stream_on_page = BabblingBrook.Library.extractDomain(stream_url) + '/' +
            BabblingBrook.Library.extractUsername(stream_url) + '/' +
            BabblingBrook.Library.extractName(stream_url);
        var jq_subscribe = jQuery('#sidebar_extra>.subscribe');
        var stream_subscription_id = false;
        var hide_link = false;
        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, stream) {
            var stream_subscribed_to = stream.domain + '/' +
                stream.username + '/' +
                stream.name;
            if (stream_subscribed_to === stream_on_page) {
                if (stream.locked === true) {
                    hide_link = true;
                }
                stream_subscription_id = stream.stream_subscription_id;
                return false;    // Break out of the jQuery.each funciton.
            }
            return true;        // Continue the jQuery.each funciton.
        });

        if (hide_link === true) {
            jq_subscribe.addClass('hide');
            return;
        }
        jq_subscribe.removeClass('hide');

        if (stream_subscription_id !== false) {
            // Display the unsubscribed button.
            jQuery('a', jq_subscribe)
                .text('Unsubscribe')
                .attr('data-subscription-id', stream_subscription_id);
            jq_subscribe.addClass('unsubscribe').removeClass('subscribe');
        } else {
            // Display the subscribe button.
            jQuery('a', jq_subscribe).text('Subscribe');
            jq_subscribe.addClass('subscribe').removeClass('unsubscribe');
        }
        jq_subscribe.removeClass('hide').unbind('click').click(function (event) {
            event.preventDefault();
            onSubscribedClicked(jq_subscribe, stream_data);
        });
    }

    /**
     * Show the meta link.
     *
     * @param stream_data A standard stream object. See BabblingBrook.Models.stream
     *
     * @returns {void}
     */
    var displayMetaLink = function (stream_data) {
        if (BabblingBrook.Settings.feature_switches['META_LINKS'] === false) {
            return;
        }
        jQuery('#sidebar_extra>.meta-url>a').attr('href', 'http://' + stream_data.meta_url);
        jQuery('#sidebar_extra>.meta-url').removeClass('hide');
    }

    /**
     * Public methods
     */
    return {

        /**
         * Setup the side bar using the details from a fetched stream.
         *
         * @param stream_data A standard stream object. See BabblingBrook.Models.stream
         *
         * @returns {void}
         */
        onStreamChanged : function(stream_data) {
            if (BabblingBrook.Models.stream(stream_data) === false) {
                console.trace();
                throw 'Stream data is not valid';
            }

            var stream_url = BabblingBrook.Library.makeStreamUrl(stream_data, 'posts');
             jQuery('#sidebar_loading_title').remove();
             jQuery('#sidebar_loading_description').remove();
             jQuery('#sidebar_extra>.title').removeClass('hide');
             jQuery('#sidebar_extra>.title>h3>a')
                 .attr('title', 'Owned by: ' + stream_data.domain + '/' + stream_data.username)
                 .text(stream_data.name)
                 .attr('href', 'http://' + stream_url);
             jQuery('#sidebar_extra>.description').text(stream_data.description);

             jQuery('#sort_bar>dt').addClass('block-loading');

             displayMetaLink(stream_data);
             displaySubscribedLink(stream_url, stream_data);
        },

        /**
         * Displays the filters in the dropdown.
         *
         * @param {array} filters An array of filter objects to display on the sidebar.
         * @param {function} onFilterChanged Callback for when a different filter is selected.
         *      Needs to accept a single filter object as a paramater.
         * @param {function} onParamSearch Callback for when paraters have been entered.
         *
         * @return void
         */
        onFiltersLoaded : function (filters, onFilterChanged, onParamSearch) {
            var filter_missing = false;
            jQuery.each(filters, function(index, filter) {
                var filter_url = BabblingBrook.Library.makeRhythmUrl(filter, '');
                page_filters[filter_url] = filter;
                // Recursively fetch rhythm and recall this function until all rhythnms have been fetched.
                if (typeof filter.fetching !== 'undefined' && filter.fetching === true) {
                    filter_missing = true;
                } else if (typeof filter.description === 'undefined' && typeof filter.fetching === 'undefined') {
                    filter_missing = true;
                    filter.fetching = true;

                    // Temporary code needed to convert version to string until version object refactoring is done.
                    var filter_with_version_string = {};
                    jQuery.extend(filter_with_version_string, filter);
                    if (typeof filter.version === 'object') {
                        filter_with_version_string.version = BabblingBrook.Library.makeVersionString(
                            filter_with_version_string.version
                        );
                    }

                    BabblingBrook.Client.Core.Interact.postAMessage(
                        filter_with_version_string,
                        'FetchRhythm',
                        function (rhythm_data) {
                            // ensure that the version value of the filter includes any 'latest' qualifiers
                            // rather than the exact verison number that was returned from the request.
                            // Otherwise the sort rhythm will appear twice in the drop down after it is clicked.
                            var latest_version = filters[index].version;
                            filters[index] = jQuery.extend({}, rhythm_data.rhythm, true);
                            filters[index].version = latest_version;
                            BabblingBrook.Client.Component.StreamSideBar.onFiltersLoaded(
                                filters,
                                onFilterChanged,
                                onParamSearch
                            );
                        },
                        function () {
                            console.error('error fetching a filter');
                        },
                        BabblingBrook.Client.User.Config.action_timeout
                    );
                }
            });
            if (filter_missing === true) {
                return;
            }
            if (BabblingBrook.Settings.feature_switches['STREAM_SORT'] === false) {
                jQuery('#sort_bar').addClass('level-hide');
                jQuery('.filter-details').addClass('level-hide');
            }

            var jq_sort_bar = jQuery('#sort_bar');
            jQuery('div', jq_sort_bar).remove();
            jq_sort_bar.append('<div class="hide"></div>');
            var jq_sort_options = jQuery('#sort_bar div');

            jQuery.each(filters, function (index, filter) {
                var filter_url = BabblingBrook.Library.makeRhythmUrl(filter, '');
                jq_sort_options.append(
                    '<dd class="unsorted" title="' + filter_url + '" value="' + filter_url + '">' + filter.name + '</dd>'
                );
            });
//            jQuery('#sort_bar dd:last')
//                .removeClass('side-loading')
//                .addClass('side-and-bottom-loading');

            // Detect an abandoned click.
            jQuery(document).unbind('sidebar.filters'); // remove any stale events
            jQuery(document).click('sidebar.filters', function (e) {
                var jq_clicked = jQuery(e.target);
                if (!jq_clicked.parents().is('#sort_bar') && !jq_clicked.is('#sort_bar')) {
                    jQuery('#sort_bar div').addClass('hide');
                    jQuery('#sort_bar dt').removeClass('active');
                }
            });

            // Open / close menu.
            jQuery('#sort_bar dt').off().on('click', function () {
                jQuery('#sort_bar div').toggleClass('hide');
                jQuery('#sort_bar dt').toggleClass('active');
            });

            // Switch options.
            jQuery('#sort_bar dd').click(function (e) {
                var jq_clicked = jQuery(e.target);
                jQuery('#sort_bar dt').html(jq_clicked.html());
                jQuery('#sort_bar dt').attr('value', jq_clicked.attr('value'));
                jQuery('#sort_bar div').addClass('hide');
                jQuery('#sort_bar dt').removeClass('active');

                // Change the description.
                var filter_url = jq_clicked.attr('value');
                var filter = page_filters[filter_url];
                jQuery('#sidebar_extra .filter-name').text(filter.name + ' : ');
                jQuery('#sidebar_extra .filter-description').text(filter.description);

                showClientParameters(filter, onParamSearch);

                var filter_name = BabblingBrook.Library.makeRhythmFromUrl(jq_clicked.attr('value'))
                filter_name.version = BabblingBrook.Library.extractVersion(jq_clicked.attr('value'));
                BabblingBrook.Client.Component.StreamSideBar.onRhythmSelectedHook(filter_name);
                onFilterChanged(filter_name);
            });
        },

        /**
         * Called when a filter starts to be processed.  Marks it as loading in the side bar.
         *
         * @param {object} filter BabblingBrook.Models.rhythmName object for the filter that is loading.
         * @returns {void}
         */
        onFilterLoading : function (filter) {
            var filter_url = BabblingBrook.Library.makeRhythmUrl(filter, '');
            var jq_sort_dd = jQuery('#sort_bar dd[value="' + filter_url + '"]');

            if (jq_sort_dd.is(':last-child') === true) {
                jq_sort_dd.addClass('side-and-bottom-loading');
            } else {
                jq_sort_dd.addClass('side-loading');
            }
        },

        /**
         * Change the appearance of the drop down now that the results have come back.
         *
         * @param {string} filter_json_url
         */
        onSorted : function (filter_json_url, onParamSearch) {

            var filter_url = BabblingBrook.Library.changeUrlAction(filter_json_url, '');
            // Change the description.
            var filter = page_filters[filter_url];
            var current_filter_url = jQuery('#sort_bar dt').attr('value');  // .val() does not work on dt.
            if (filter_url === current_filter_url || current_filter_url === '') {
                jQuery('#sidebar_extra .filter-name').text(filter.name + ' : ');
                jQuery('#sidebar_extra .filter-description').text(filter.description);
                showClientParameters(filter, onParamSearch);
            }

            // change highlighting on the sort menu.
            var jq_sort_dd = jQuery('#sort_bar dd[value="' + filter_url + '"]');
            jq_sort_dd.removeClass('side-loading side-and-bottom-loading');
            if (jQuery('#sort_bar .side-loading').length === 0
                && jQuery('#sort_bar .side-and-bottom-loading').length === 0
            ) {
                jQuery('#sort_bar dt').removeClass('block-loading');
            }
            if (jq_sort_dd.length > 0) {
                // Animate title
                jQuery('#sort_bar dt')
                    .removeClass('unsorted')
                    .addClass('sorted relaxed');
                // Animate row
                jq_sort_dd
                    .removeClass('unsorted')
                    .addClass('sorted relaxed');

                // If this is the first result back then set the title for the drop down.
                if (jQuery('#sort_bar dt').attr('value') === '') {
                    jQuery('#sort_bar dt').attr('value', filter_url);
                    jQuery('#sort_bar dt').html(jq_sort_dd.html());
                }
            }
        },

        /**
         * A hook that is called when the user selects a new sort rhythm.
         *
         * @param {obejct} rhythm_name A standard rhythmName object
         */
        onRhythmSelectedHook : function (rhythm_name) {},

        /**
         * A hook that is called when the user selects a new sort rhythm.
         *
         * @param {object} jq_subscribe A jQuery object representing the subscribe/unsubscribe link.
         * @param {object} stream_data Data about the stream. See BabblingBrook.models.stream for a full description.
         *
         * @return {boolean} When overridding this hook, return true so that the default action does not occour.
         */
        onSubscribeHook : function (jq_subscribe, stream_data) {
            return false
        }

    };
}());