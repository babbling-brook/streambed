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
 * @fileOverview Display an post and sub posts functionality.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Post !== 'object') {
    BabblingBrook.Client.Page.Post = {};
}

/**
 * @namespace Singleton enabling the viewing of an post and its sub posts.
 * @package JS_Client
 */
BabblingBrook.Client.Page.Post.PostWithTree = (function () {
    'use strict';
    /**
     * @type {string} The root post being displayed on this page.
     */
    var post;

    /**
     * @type {object} The stream object that the root post object resides in.
     */
    var stream;

    /**
     * @type {string} The url of the stream that this post resides in.
     */
    var stream_url;

    /**
     * @type {object} The template used to display child posts.
     */
    var child_post_template;

    /**
     * Callback functions to call after the root post has been displayed.
     *
     * External scripts can call registerOnRootPostDisplayHook to push a function into this array.
     * The functions are called after the root post has been displayed and can be used to amend the display.
     * Each function is passed a jquery object of the post, the post object and the stream object.
     *
     * @type {function[]}
     */
    var root_post_display_hooks = [];

    /**
     * @type {object} filters Default filters to sort the sub posts on this page. Indexed by url
     * @type {string} filters.name
     * @type {number} filters.priority The priority in the sort queue for this sort request.
     * @type {object} filters.tree The sorted post data in tree format. Indexed with post_id, with recurssion.
     */
    var default_filters = [];
    /**
     * @type {object} filters Filters used to sort the sub posts on this page. Indexed by url
     * @type {string} filters.name
     * @type {number} filters.priority The priority in the sort queue for this sort request.
     * @type {object} filters.tree The sorted post data in tree format. Indexed with post_id, with recurssion.
     */
    var filters;

    var current_filter_url;

    var deferred_stream_loaded = jQuery.Deferred();

    /**
     * Callback for when the filter alogrithm has been changed and the pages results need updating.
     *
     * @param {string} filter_url The url of the filter that is now selected.
     *
     * @return void
     */
    var onFilterChanged = function(filter) {
        // Do nothing if the filter hasn't changed.
        filter.name = BabblingBrook.Library.normaliseResourceName(filter.name);
        var filter_url = BabblingBrook.Library.makeRhythmUrl(filter, 'json');
        if(current_filter_url === filter_url) {
            return;
        }
        jQuery('#loading_children').removeClass('hide');
        jQuery('#root_post>.children').empty();

        setupTree(filter);
        current_filter_url = filter_url;
    };

    /**
     * Callback to use when displaying posts so that the sort_value is appended.
     *
     * Call with bind to include the sort score i.e. displaySort.bind(null, sort_value)
     *
     * @param {string} sort_value The value to display for the sort value.
     * @param {object} jq_post The jQuery object that contains the post.
     *
     * @return void
     */
    var displaySort = function (post, jq_post) {
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
     * The display callback with taken data included if it is required.
     *
     * @param {boolean} [update=false] Is this an post update In which case it should not be displayed immediately.
     *      If the post already exists then it needs appending to the existing post ready to show, if it is a new
     *      post then it needs appending in the correct place and a show more link showing.
     * @param {object} post The post to display
     * @param {boolean} [taken]
     *
     * @return void
     */
    var displayCallbackWithTaken = function (update, post, taken) {
        // Don't display the post if it has no children and it is not taken.
        if (typeof taken === 'boolean' && taken === false) {
            return;
        }

        var jq_branch_location = jQuery('.post[data-post-id=' + post.parent_id + ']>ul.children');
        var jq_this_post = jQuery('li.post[data-post-id=' + post.post_id + ']', jq_branch_location);

        BabblingBrook.Client.Component.Post(
            post,
            jq_this_post,
            child_post_template,
            '>ul.children',
            child_post_template,
            displaySort.bind(null, post),
            update
        );
    };

    /**
     * Callback used by the tree walk to display the post.
     *
     * Calls the display post class with the correct details to display the post.
     *
     * @param {boolean} [update=false] Is this an post update In which case it should not be displayed immediately.
     *      If the post already exists then it needs appending to the existing post ready to show, if it is a new
     *      post then it needs appending in the correct place and a show more link showing.
     * @param {object} post The post to display
     * @param {boolean} has_children Does this post have any children in the tree.
     *
     * @return void
     */
    var displayCallback = function (update, post, has_children) {
        var jq_branch_location = jQuery('.post[data-post-id=' + post.parent_id + ']>ul.children');
        var jq_this_post = jQuery('li.post[data-post-id=' + post.post_id + ']', jq_branch_location);

        if (jq_this_post.length === 0) {
            // Insert a place holder for the post before the various callbacks are triggered, as they will get inserted
            // in a haphazard order if we wait for the callbacks to return.
            var jq_post_place_holder = jQuery('#post_tree_dummy_post>li').clone();
            jq_post_place_holder.attr('data-post-id', post.post_id);
             // Location if it does not exist.
            if (update === true) {
                // @note This just places new posts at the top of its parents children, rather than
                //      inserting in its correct sort order place.
                jq_branch_location.prepend(jq_post_place_holder);
                jq_this_post = jQuery('>li:first', jq_branch_location);
            } else {
                jq_branch_location.append(jq_post_place_holder);
                jq_this_post = jQuery('>li:last', jq_branch_location);
            }
        } else {
//            jq_branch_location.append(jq_post_place_holder);
//            jq_this_post = jQuery('>li:last', jq_branch_location);
        }

        // Don't show deleted posts if they have no children - unless the user has taken it.
        if (has_children === false && post.username === 'deleted') {
            BabblingBrook.Client.Component.Value.areAnyTaken(post, displayCallbackWithTaken.bind(null, update, post));
        } else {
            displayCallbackWithTaken(update, post);
        }
    };

    /**
     * Finds an post in the tree.
     * This works because objects are passed by reference in JS.
     * @param {object} tree The tree to search (or sub tree).
     * @param {number} post_id The id to search for.
     * @return {Object|boolean} the sub tree with the searched for post_id or false.
     */
    var searchTree = function (tree, post_id) {
        var found_tree = false;
        jQuery.each(tree, function (i, branch) {
            if (i === post_id) {
                found_tree = branch;
                return false;    // Escape the jQuery.each function.
            }

            // search any branches.
            found_tree = searchTree(branch, post_id);
            if (found_tree !== false) {
                return false;    // Escape the jQuery.each function.
            }
            return true;        // Continue the jQuery.each function.
        });
        return found_tree;
    };

    /**
     * Callback to display the tree of sorted sub posts.
     *
     * @param {object} data
     * @param {object} data.sort_request Details of the origional sort request.
     *                                   See See BabblingBrookModes.posts with the 'returned'
     *                                   and possibly 'tree_base' extensions for details.
     * @param {object[]} data.posts An array of sorted posts for display on this page.
     *                            See BabblingBrookModes.posts with the 'tree' extension for details.
     *
     * @return void
     */
    var displaySortedPosts = function (data) {
        BabblingBrook.Client.Component.StreamSideBar.onSorted(data.sort_request.filter.url);
        // Create the tree data
        if (data.sort_request.update === true) {

            BabblingBrook.Client.Core.Tree.update(
                data.posts,
                post.post_id,
                post.domain,
                data.sort_request.filter.url
            );
            if (current_filter_url === data.sort_request.filter.url) {
                BabblingBrook.Client.Core.Tree.displayTreeUpdate(
                    post.post_id,
                    post.domain,
                    current_filter_url,
                    displayCallback.bind(null, true)
                );
            }
        } else {
            BabblingBrook.Client.Core.Tree.create(
                data.posts,
                post.post_id,
                post.domain,
                data.sort_request.filter.url
            );
            if (current_filter_url === data.sort_request.filter.url) {
                BabblingBrook.Client.Core.Tree.displayTree(
                    post.post_id,
                    post.domain,
                    current_filter_url,
                    displayCallback.bind(null, false)
                );
            }

            jQuery('#loading_children').addClass('hide');

            // Register the update process
            var update_sort = {
                streams : data.sort_request.streams,
                client_uid : data.sort_request.client_uid,
                post_id : data.sort_request.post_id,
                type : data.sort_request.type,
                filter : data.sort_request.filter,
                moderation_rings : data.sort_request.moderation_rings,
                refresh_frequency : data.sort_request.refresh_frequency,
                priority : data.sort_request.priority,
                success : displaySortedPosts,
                error : setupTreeError
            };

            BabblingBrook.Client.Core.FetchMore.register(update_sort);
            BabblingBrook.Client.Component.Tutorial.readCommentsHack(stream, post);
        }
    };

    /**
     * Lets the domus domain of the stream know that the stream has been displayed.
     *
     * @return void
     * @protocol This should probably be removed as can't relly on clients to use it so the numbers would not
     *      be reliable.
     */
    var recordStreamDisplayed = function () {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                'feature' : 'stream_post',
                'url' : stream_url
            },
            'RecordFeatureUsed'
        );
    };

    /**
     * Report an error in the setting up of the post tree.
     */
    var setupTreeError = function (error_data) {
        BabblingBrook.Client.Component.StreamError.error(error_data, 'tree');
    };

    /**
     * Setup the tree of child posts.
     *
     * @return void
     */
    var setupTree = function (filter) {
        jQuery('#tree_root>li>.children').empty();
        var moderation_rings = [
//            {
//                url : window.location.host + '/test ring'
//            }
        ];

        filter.name = BabblingBrook.Library.normaliseResourceName(filter.name)
        var filter_url = BabblingBrook.Library.makeRhythmUrl(filter, 'json');
        var sort_filter = {
            url : filter_url,
            priority : filter.priority,
            name : filter.name
        };

        if (typeof stream === 'undefined') {
            stream = BabblingBrook.Library.makeStreamFromUrl(stream_url);
        }

        var client_uid = post.domain + '/' + post.post_id;
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                sort_request : {
                    type : 'tree',
                    client_uid : client_uid,
                    streams : [stream],
                    post_id : post.post_id,
                    filter : sort_filter,
                    moderation_rings : moderation_rings,
                    posts_to_timestamp : null
                }
            },
            'SortRequest',
            displaySortedPosts,
            setupTreeError
        );
    };

    /**
     * Callback for after a root post has been displayed.
     *
     * @param {object} post A standard post object for the post being displayed.
     * @param {object} jq_post Jquery object that holds the post being displayed.
     *
     * @returns {void}
     */
    var onDisplayedRootPost = function (post, jq_post) {
        deferred_stream_loaded.done(function () {
            var length = root_post_display_hooks.length;
            for (var i = 0; i < length; i++) {
                root_post_display_hooks[i](jq_post, post, stream);
            }
        });
    };

    /**
     * Callback for when the steam has been fetched.
     *
     * @param {object} stream_data The stream data from the streams domain.
     *
     * @returns {void}
     */
    var onStreamFetched = function (stream_data) {
        // @fixme This should be being returned in the version object format.
        stream = jQuery.extend({}, stream_data);
        if (typeof stream_data.version === 'string') {
            stream.version = BabblingBrook.Library.makeVersionObject(stream_data.version);
        }

        deferred_stream_loaded.resolve();
        BabblingBrook.Client.Component.StreamSideBar.onStreamChanged(stream_data);
    };

    /**
     * Called by client.js when the post data has been retrieved.
     *
     * @param {object[]} post Standard post object.
     *
     * @return void
     */
    var setup = function (post_data) {
        post = post_data;
        stream_url = BabblingBrook.Library.makeStreamUrl(
            {
                domain : post.stream_domain,
                username : post.stream_username,
                name : post.stream_name,
                version : post.stream_version
            },
            'json'
        );
        BabblingBrook.Client.Core.Streams.getStream(
            post.domain,
            post.stream_username,
            post.stream_name,
            post.stream_version,
            onStreamFetched
        );

        var tree_branch_template = jQuery('#tree_branch_template>ul').clone();
        jQuery('#loading_main_post').replaceWith(tree_branch_template);
        var jq_post_location = jQuery('>li.post', tree_branch_template);

        var root_post_template = jQuery('#post_tree_root_template>li').clone();
        root_post_template.attr('id', 'root_post');

        child_post_template = jQuery('#post_tree_child_template>li').clone();

        BabblingBrook.Client.Component.Post(
            post,
            jq_post_location,
            root_post_template,
            '>ul.children',
            child_post_template,
            onDisplayedRootPost.bind(null, post)
        );

        recordStreamDisplayed();

        setupTree(filters[0]);
    };

    /**
     * Constructor and public methods
     */
    return {

        /**
         * Get the post details. Called when the page has loaded to start the ball rolling
         *
         * When the domus domain is ready, request the post data, and start sorting
         *
         * @return void
         */
        construct : function () {
            var post_url = window.location.pathname;
            var url_parts =  post_url.split('/');
            var post_domain = url_parts[2];
            filters = BabblingBrook.Client.ClientConfig.default_sort_filters;
            filters[0].name = BabblingBrook.Library.normaliseResourceName(filters[0].name);
            current_filter_url = BabblingBrook.Library.makeRhythmUrl(filters[0], 'json');
            BabblingBrook.Client.Component.StreamSideBar.onFiltersLoaded(filters, onFilterChanged);

            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    domain : post_domain,
                    post_id :  url_parts[3]
                },
                'GetPost',
                setup,
                /**
                 * Error callback. Encapsulated to inform the error class that this is a tree structure.
                 *
                 * @param error_data The error data returned from the domus domain.
                 *
                 * @return void
                 */
                function (error_message) {
                    var message = 'There has been an error whilst fetching an post.';
                    if (error_message.error_code === 'GetPost_not_found') {
                        message = 'This post does not exist.';
                    } else if (error_message.error_code === 'GetPost_takes_failed') {
                        message = 'There was an error whilst fetching your take data for this post.';
                    }
                    BabblingBrook.Client.Component.Messages.addMessage({
                        type : 'error',
                        message : message,
                        full : error_message.error_code
                    });
                    jQuery('#loading_main_post').remove();
                }
            );
            BabblingBrook.Client.Core.Loaded.setPostWithTreeLoaded();

        },

        /**
         * An empty public function that can be used to hook additional display callbacks in.
         *
         * Hooks must be attatched in a call to the
         * BabblingBrook.Client.Core.Loaded.onPostWithTreeLoaded() deferred object.
         *
         * @param {object} jq_post A jquery object holding the post being displayed.
         *
         * @returns {void}
         */
        registerOnRootPostDisplayHook : function (onDisplayRootPostExtraCallback) {
            if (typeof onDisplayRootPostExtraCallback !== 'function') {
                console.trace();
                throw "Passed in onDisplayRootPostExtraCallback is not a function";
            }
            root_post_display_hooks.push(onDisplayRootPostExtraCallback);
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
        BabblingBrook.Client.Page.Post.PostWithTree.construct();
    });
});