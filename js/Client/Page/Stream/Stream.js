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
 * @fileOverview Display a stream of posts.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Stream !== 'object') {
    BabblingBrook.Client.Page.Stream = {};
}

/**
 * @namespace Displays a list of posts.
 * @package JS_Client
 */
BabblingBrook.Client.Page.Stream.Stream = (function () {
    'use strict';

    var jq_stream_posts;

    /**
     * @type {string} Location of the template for displaying posts in a stream.
     */
    var post_template_path = '#post_stream_template>.post';

    var post_photowall_template_path = '#stream_photowall_template>.post';

    /**
     * An instance of a DisplayStream class used for displaying the posts.
     * @type object
     */
    var stream_display;

    /*
     * The presentation type of the current stream.
     * @type string
     */
    var presentation_type;

    /**
     * @type {object} The current filters available to the user in the filter drop down.
     */
    var current_filters;

    /**
     *
     * @type array  The moderation rings being used to sort the currently displayed stream.
     */
    var current_moderation_rings;

    /**
     * @type {string} The url of the stream that posts are being displayed for.
     */
    var stream_url;

    /**
     * A standard stream object for the stream being displayed.
     */
    var stream;

    /***
     * @type {object} The filter currently being used to display results.
     */
    var current_filter;

    /**
     * If there is a rhythm in the stream url then it is stored here.
     * @type {object} See the Model.rhythmName definition for details.
     */
    var url_rhythm;

    /**
     * The uid for the visible sort request. Used to determine if returnign sort request results should be displayed.
     *
     * @type string
     */
    var page_uid;

    /**
     * Resets variables to their defaults.
     *
     * @returns {void}
     */
    var reset = function () {
        jq_stream_posts = jQuery('#stream_container');
        jq_stream_posts.empty();
        stream_url = undefined;
        current_filter = undefined;
        current_filters = undefined;
        url_rhythm = undefined;
        stream = undefined;
    };

    /**
     * Fetches and stores the rhythm in the url if there is one.
     *
     * @returns {void}
     */
    var getUrlRhythm = function () {
        var stream_action = BabblingBrook.Library.extractAction(window.location.href);
        if (stream_action === 'rhythm') {
            var url = window.location.href;
            url_rhythm = {
                domain : BabblingBrook.Library.extractPathItem(url, 8),
                username : BabblingBrook.Library.extractPathItem(url, 9),
                name : BabblingBrook.Library.decodeUrlComponent(BabblingBrook.Library.extractPathItem(url, 10)),
                version :
                    BabblingBrook.Library.extractPathItem(url, 11) + '/' +
                        BabblingBrook.Library.extractPathItem(url, 12) + '/' +
                        BabblingBrook.Library.extractPathItem(url, 13)
            };
        }
    };

    /**
     * Callback to use when displaying posts so that the sort_value is displayed.
     *
     * @param {object} jq_post The jQuery object that contains the post.
     * @param {object} post The BabblingBrook.Models.post object used to display the post.
     *
     * @return void
     */
    var onPostDisplayed = function (jq_post, post) {
        if (typeof post.sort === 'undefined' || post.sort === '') {
            jQuery('div>span.sort-score-intro', jq_post).addClass('hide');
            return;
        }
        var jq_sort = jQuery('div>span.sort-score-intro>span.sort-score', jq_post);
        jq_sort.text(post.sort);
        if (parseInt(post.sort) < -2) {
            jq_post.addClass('low-sort');
        }
        if (parseInt(post.sort) > 1000000000) {
            jq_post.addClass('high-sort');
        }
    };

    /**
     * Register to run updates to this sort request.
     *
     * @param {type} sort_request
     *
     * @returns {void}
     */
    var registerForUpdateRequests = function (sort_request) {
        var json_url = BabblingBrook.Library.makeStreamUrl(sort_request.streams[0], 'json');
        var update_sort = {
            streams : sort_request.streams,
            client_uid: sort_request.client_uid,
            post_id : null,
            type : sort_request.type,
            filter : sort_request.filter,
            moderation_rings : sort_request.moderation_rings,
            priority : sort_request.priority,
            refresh_frequency : sort_request.refresh_frequency,
            block_numbers : sort_request.block_numbers,
            success : onSorted.bind(null, undefined),
            error : streamRequestErrorCallback.bind(null, json_url)
        };
        BabblingBrook.Client.Core.FetchMore.register(update_sort);
    };

    /**
     * Refreshes the sort results for a stream with the given paramaters.
     *
     * @param {object} params Name value pairs of parameters.
     *
     * @returns {void}
     */
    var onParamSearch = function (params) {
        jq_stream_posts.empty();
        displayPresentationType();
    }

    /**
     * Checks if the the stream arrays for two sort_requests are identical.
     */
    var doStreamsMatch = function (streams1, streams2) {
        var length1 = streams1.length;
        var length2 = streams2.length;
        for (var i=0; i<length1; i++) {
            var found = false;
            for (var j=0; j<length2; j++) {
                if (BabblingBrook.Library.doStreamsMatch(streams1[i], streams2[j]) === true) {
                    found = true;
                }
            }
            if (found === false) {
                return false;
            }
        }
        return true;
    };

    /*
     * Recieves sorted posts from the domus domain. Stores them and displays if they are selected.
     *
     * Renders the first set of returned results.
     *
     * @param {function} onPostsReady Callback to call once the posts have been sorted.
     *      Used by the class that inherits from the StreamDisplay class.
     * @param {object} data
     * @param {object[]} data.posts See BabblingBrook.Models.posts for a full definition.
     * @param {object} data.sort_request See BabblingBrook.Models.sortRequest for a full definition.
     *
     * @return void
     */
    var onSorted = function (onPostsReady, data) {
        if (data.sort_request.update === false) {
            BabblingBrook.Client.Component.StreamSideBar.onSorted(data.sort_request.filter.url, onParamSearch);
        }

        // Abandon the results if the user has moved on. they are cached in the Domus domain, so no fuss.
        if (data.sort_request.client_uid !== page_uid) {
            return;
        }

        if (data.sort_request.filter.url === BabblingBrook.Library.makeRhythmUrl(current_filter, 'json')) {
            if (data.sort_request.update === false) {
                onPostsReady(data.posts);
                registerForUpdateRequests(data.sort_request);
            } else {
                stream_display.update(data.posts);
            }

            jq_stream_posts.removeClass('block-loading');
        }
        BabblingBrook.Client.Page.Stream.Stream.onStreamLoadedHook(stream);
    };

    var showStreamNotFound = function (stream_json_url) {
        var stream_url = BabblingBrook.Library.changeUrlAction(stream_json_url, '');
        var jq_stream_not_found = jQuery('#stream_not_found_template>div').clone();
        jQuery('.stream-location', jq_stream_not_found).text(stream_url);
        jQuery('#content_page')
            .empty()
            .append(jq_stream_not_found);
    }

    /**
     * Error callback from a request to fetch data about this stream.
     *
     * @param {object} stream_json_url The json url used to request the stream data.
     * @param {object}error_data Standard data returned with the error.
     *
     * @retun void
     */
    var streamRequestErrorCallback = function(stream_json_url, error_data) {
        var error_code = error_data.error_code;
        if (error_code === '404') {
            showStreamNotFound(stream_json_url);
            return;
        }

        var message = 'An unknown error has occoured whilst fetching stream data for : ' + stream_json_url;

        switch (error_code) {
            case '404':
                message = 'stream not found: ' + stream_json_url;
                break;

            case 'SortRequest_filter':
                message = 'A filter has not been found for : ' + stream_json_url;
                break;
        }

        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : message
        });
    };

    var onCancelPost = function() {
        jQuery('.make-post').addClass('hide');
        setupMakePost();
    };

    /**
     * Callback to insert a new post into the sorted data.
     *
     * @param {object} post See BabblingBrook.Models.posts for full details.
     *
     * @return void
     */
    var insertNewPost = function(post) {
        if (presentation_type === 'photowall') {
            stream_display.insertPost(post);
        } else {
            post.stream_name = BabblingBrook.Library.normaliseResourceName(post.stream_name);
            stream.name = BabblingBrook.Library.normaliseResourceName(stream.name);
            if (post.stream_domain === stream.domain
                && post.stream_username === stream.username
                && post.stream_name === stream.name
            ) {
                stream_display.update([post], true);
            }
        }

        // Recreate the make post form.
        var make_post = new BabblingBrook.Client.Component.MakePost(insertNewPost, onCancelPost);
        jQuery('.make-post').html('');
        var stream_json_url = BabblingBrook.Library.changeUrlAction(stream_url, 'json');
        make_post.setupNewPost(stream_json_url, jQuery('.make-post'), 'minimised');
    };

    /**
     * Updates the stream url to reflect the currently selected filter.
     *
     * @param {string} filter_url The url of the filter that is now selected.
     *
     * @return void
     */
    var updateStreamUrlForFilter = function(filter_url) {
        var new_url = BabblingBrook.Library.changeUrlAction(stream_url, 'rhythm');
        var rhythm_domain = BabblingBrook.Library.extractDomain(filter_url);
        var rhythm_username = BabblingBrook.Library.extractUsername(filter_url);
        var rhythm_name = BabblingBrook.Library.extractName(filter_url);
        var rhythm_version = BabblingBrook.Library.extractVersion(filter_url);
        new_url = new_url +
            '/' + rhythm_domain +
            '/' + rhythm_username +
            '/' + rhythm_name +
            '/' + rhythm_version;
        BabblingBrook.Client.Core.Ajaxurl.changeUrl(new_url, 'BabblingBrook.Client.Page.Stream.Stream.construct');
    };

    /**
     * Display the stream depending on the presentation type.
     *
     * @return void
     */
    var displayPresentationType = function () {
        switch (presentation_type) {
            case 'photowall':
                createPhotoWall();
                break;

            case 'list':
                createCascade();
                break;

            default:
                throw 'Invalid stream presentation type :' + presentation_type;
        }
    };

    var createCascade = function () {
        stream_display = new BabblingBrook.Client.Component.Cascade(
            jq_stream_posts,
            post_template_path,
            sortStreamForFilter.bind(null, true, undefined),
            undefined,
            undefined,
            onPostDisplayed
        );
    };

    var createPhotoWall = function () {
        jQuery('#stream_container').empty().addClass('block-loading');
        jQuery('#content_page').addClass('photowall-content');
        stream_display = new BabblingBrook.Client.Component.Photowall(
            jq_stream_posts,
            post_photowall_template_path,
            sortStreamForFilter.bind(null, true, undefined)
        );
    };

    /**
     * Callback for when the filter rhythm has been changed and the pages results need updating.
     *
     * @param {object} filter A BabblingBrook.Models.rhythmName object that points to the selectd filter.
     *
     * @return void
     */
    var onFilterChanged = function(filter) {
        current_filter = filter;
        var filter_url = BabblingBrook.Library.makeRhythmUrl(filter);
        // Change the url
        updateStreamUrlForFilter(filter_url);

        displayPresentationType();
    }


    /**
     * Setup the location of the current stream.
     *
     * @returns {void}
     */
    var setupStreamLocation = function () {
        if (typeof stream === 'undefined') {
            stream = BabblingBrook.Library.makeStreamFromUrl(window.location.href, true);
        }
        // Normalise the url to prevent problems with trailing slashes.
        stream_url = BabblingBrook.Library.makeStreamUrl(stream, '');
    };

    var setSideBarType = function () {
        switch (presentation_type) {
            case 'photowall':
                jQuery('#sidebar').addClass('sidebar-top').removeClass('sidebar-side');
                jQuery('#sidebar_extra').addClass('sidebar-hide');
                jQuery('#sidebar_open').removeClass('hide');
                break;

            case 'list':
                jQuery('#sidebar').addClass('sidebar-side').removeClass('sidebar-top');
                jQuery('#sidebar_extra').removeClass('sidebar-hide');
                jQuery('#sidebar_open').addClass('hide');
                break;

            default:
                throw 'Invalid stream presentation type :' + presentation_type;
        }
        BabblingBrook.Client.Component.Resize.retest();
    };

    /**
     * Load the stream data
     *
     * @returns {void}
     */
    var fetchStreamData = function() {
        var stream_json_url = BabblingBrook.Library.changeUrlAction(stream_url, 'json');
        BabblingBrook.Client.Core.Streams.getStream(
            stream.domain,
            stream.username,
            stream.name,
            BabblingBrook.Library.makeVersionString(stream.version),
            function (stream_data) {
                if (stream_data.default_rhythms.length < 1) {
                    stream_data.default_rhythms = BabblingBrook.Client.ClientConfig.default_sort_filters;
                }

                presentation_type = stream_data.presentation_type
                setSideBarType();

                current_filters = stream_data.default_rhythms;
                for (var i in current_filters) {
                    var version = current_filters[i].version;
                    if (typeof version === 'object') {
                        var version_string = version.major + '/' + version.minor + '/' + version.patch;
                        current_filters[i].version = version_string;
                        current_filters[i].priority = current_filters[i].sort_order;
                    }
                }

                BabblingBrook.Client.Component.StreamSideBar.onStreamChanged(stream_data);
                setupFilters();
                BabblingBrook.Client.Component.StreamSideBar.onFiltersLoaded(current_filters, onFilterChanged, onParamSearch);

                displayPresentationType();

                sortStreamForAllFilters();
            },
            streamRequestErrorCallback.bind(null, stream_json_url)
        );
    };

    /**
     * Setup the 'make a post' at the top of the stream.
     *
     * @returns {void}
     */
    var setupMakePost = function () {
        if (BabblingBrook.Settings.feature_switches['MAKE_SELF_POST'] === false) {
            return;
        }
        var stream_json_url = BabblingBrook.Library.changeUrlAction(stream_url, 'json');

        var make_post = new BabblingBrook.Client.Component.MakePost(insertNewPost, onCancelPost);

        var jq_make_post = jQuery('.make-post');
        jq_make_post.slideDown(250, function () {
            jq_make_post.removeClass('hide');
        });

        make_post.setupNewPost(stream_json_url, jQuery('.make-post'), 'minimised');
    };

    /**
     * Setsup the first filter used to sort a stream.
     *
     * @returns {undefined}
     */
    var setupFirstFilter = function () {
        getUrlRhythm();
        if (typeof url_rhythm === 'object') {
            current_filter = url_rhythm;
            var current_filter_url = BabblingBrook.Library.makeRhythmUrl(current_filter, '');
            current_filter.params = [];
            var already_subscribed = false;
            // Only add the rhythm found in the url if it does not already exist.
            jQuery.each(current_filters, function (i, filter) {
                var filter_url = BabblingBrook.Library.makeRhythmUrl(filter,'');
                if (filter_url === current_filter_url) {
                    filter.priority = 1;
                    already_subscribed = true;
                }
            })
            if (already_subscribed === false) {
                current_filters.unshift(url_rhythm);
            }
        } else {
            current_filter = current_filters[0];
        }
    };

    /**
     * Sets up the filters for the current stream.
     *
     * @returns {void}
     */
    var setupFilters = function () {
        var subscribed_filters = {};
        var subscription_id = BabblingBrook.Client.Core.StreamSubscriptions.getStreamSubscriptionIDFromStream(stream);
        if (typeof subscription_id !== 'undefined') {
            var subscription = BabblingBrook.Client.Core.StreamSubscriptions.getStreamSubscriptionFromId(subscription_id);
            var count_of_filter_subscriptions = Object.keys(subscription.filters).length;
            if (count_of_filter_subscriptions > 0) {
                jQuery.extend(
                    true,
                    subscribed_filters,
                    subscription.filters
                );
                current_filters = [];
            }
        }

        var priority = 10;
        jQuery.each(subscribed_filters, function (i, filter) {
            subscribed_filters[i].priority = priority;
            priority++;
            current_filters.push(subscribed_filters[i]);
        });
        setupFirstFilter();
    };

    /**
     * Assign the moderation rings for this stream.
     *
     * @returns {void}
     */
    var setupModerationRings = function () {
        current_moderation_rings = BabblingBrook.Client.Component.StreamNav.getModerationRings(stream_url);
    };

    var generateUIDHashForStream = function(stream, filter, rings) {

        var hash_string = BabblingBrook.Library.makeStreamUrl(stream, '', true) + '|' +
            BabblingBrook.Library.makeRhythmUrl(filter, '', true) + '|';
        jQuery.each(rings, function(i, ring) {
            hash_string += ring.domain + '/' + ring.username + '|';
        });

        var hash = BabblingBrook.Library.generateHashCode(hash_string);
        return hash;
    };

    /**
     * Make a sort request for this stream with the passed in filter.
     *
     * @param {boolean} Should this sort request replace the results on the page when it returns.
     * @param {object} params Any client paramaters that should be passed to the sort request.
     * @param {function} onPostsReady Callback to call once the posts have been sorted.
     *      Used by the class that inherits from the DisplayStream class.
     * @param {type} filter A standard BabblingBrook.Models.rhythmName object.
     *
     * @returns void}
     */
    var sortStreamForFilter = function (set_client_uid, params, onPostsReady, filter) {

        if (typeof filter === 'undefined') {
            filter = current_filter;
        }
        var sort_filter = {
            url : BabblingBrook.Library.makeRhythmUrl(filter, 'json'),
            priority : filter.priority,
            name : filter.name
        };

        var client_uid = generateUIDHashForStream(stream, filter, current_moderation_rings);
        if (set_client_uid === true) {
            page_uid = client_uid;
        }
        var stream_json_url = BabblingBrook.Library.changeUrlAction(stream_url, 'json');
        BabblingBrook.Client.Component.StreamSideBar.onFilterLoading(filter, undefined, onParamSearch);
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                sort_request : {
                    type : 'stream',
                    client_uid : client_uid,
                    streams : [stream],
                    filter : sort_filter,
                    moderation_rings : current_moderation_rings,
                    posts_to_timestamp : null,
                    client_params : params
                }
            },
            'SortRequest',
            onSorted.bind(null, onPostsReady),
            streamRequestErrorCallback.bind(null, stream_json_url),
            BabblingBrook.Client.User.Config.action_timeout
        );
    }

    /**
     * Send a sort request for the top filters assigned to his stream.
     *
     * @returns {void}
     */
    var sortStreamForAllFilters = function () {
        var qty_to_sort = 2;
        for (var i = 0; i < qty_to_sort; i++) {
            if (typeof current_filters[i] === 'undefined') {
                return;
            }
            sortStreamForFilter(false, undefined, function() {}, current_filters[i]);
        }
    };

    /**
     * Public methods
     */
    return {
        /**
         * Initiate the list.
         *
         * @param {object} stream_name A standard stream_name object for the stream to display.
         *
         * @return {undefined}
         */
        construct : function (stream_name) {
            BabblingBrook.Client.Core.Loaded.onStreamSubscriptionsLoaded( function () {
                reset();

                if (typeof stream_name !== 'undefined') {
                    stream = stream_name;
                }

                setupStreamLocation();
                setupMakePost();
                setupModerationRings();
                fetchStreamData();
            });
        },

        /**
         * An overridable hook that is called when the stream has loaded.
         *
         * @param {object} stream The stream that has been displayed.
         *
         * @returns {void}
         */
        onStreamLoadedHook : function (stream) {}

    };
}());
jQuery(function () {
    'use strict';
    BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
        // The stream page can be instantiated from other pages such as the home page.
        // We only want to construct it if we are actually on the stream page.
        if (jQuery('#on_stream_page').val() === 'true') {
            BabblingBrook.Client.Page.Stream.Stream.construct();
        }
    });
});