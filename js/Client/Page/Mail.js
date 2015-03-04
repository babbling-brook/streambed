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
 * @fileOverview Javascript used for the private messaging service.
 *
 * @author Sky Wickenden
 */

/**
 * There are additional nested objects for this class in the Post folder.
 * These must be included after this file or they will error.
 *
 * @namespace Javascript used for the private messaging service.
 * @package JS_Client
 */
BabblingBrook.Client.Page.Mail = (function () {
    'use strict';

    /**
     * What page number has so far been downloaded.
     * If this is set to -1 then all avaiable posts have been downloaded.
     */
    var page = 0;

    var jq_post_list;

    /**
     * Indicates what type of posts are currently displayed. Used to catch a mismatch if the page has
     * been changed before the posts are returned.
     *
     * @type {string}
     */
    var current_type;

    /**
     * An instance of the DisplayCascade class used for ddisplaying the posts.
     * @type object
     */
    var cascade;

    /**
     * A copy of the old waiting data timestamps, before it was updated due to fetching the posts.
     * Needed to mark the posts as new.
     *
     * @type object
     */
    var last_waiting_data;

    var defer_for_wait_data = jQuery.Deferred();

    /**
     * Get the next page number of the currently selected type of post.
     *
     * @param {string} The type of private messages being returned.
     */
    var getNextPageNumber = function (type) {
        page++;
        return page;
    };

    var onPostsFetchedError = function (d) {
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : 'Unable to retrieve some of your posts.'
        });
    };

    /**
     * Fetch a users messages
     *
     * @param {string} type What type of messages are being fetched. valid values:
     *      "local_private",
     *      "global_private",
     *      "local_sent_private",
     *      "global_sent_private",
     * @param {function} onPostsReady Callback to call once the posts have been sorted.
     *      Defined in DisplayCascade.
     *
     *  @return void
     */
    var fetchMessages = function (type, onPostsReady) {
        var next_page_number = getNextPageNumber(type);
        if (next_page_number === 0) {
            jQuery('#load_more').addClass('hide');
            return;
        }
        jQuery('#load_more').removeClass('hide');

        var default_filter = {
            url : BabblingBrook.Library.changeUrlAction(
                BabblingBrook.Client.User.Config.default_private_filter,
                'json'
            ),
            name : BabblingBrook.Library.extractName(BabblingBrook.Client.User.Config.default_private_filter),
            priority : BabblingBrook.Client.User.Config.default_private_filter_priority
        }

        var client_uid = BabblingBrook.Library.generateHashCode(type);

        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                sort_request : {
                    type : type,
                    client_uid : client_uid,
                    filter : default_filter,
                    moderation_rings : [],
                    posts_to_timestamp : null,
                    private_page : next_page_number,
                    user : {
                        username : BabblingBrook.Client.User.username,
                        domain : BabblingBrook.Client.User.domain
                    }
                }
            },
            'SortRequest',
            onPostsFetched.bind(null, type, next_page_number, onPostsReady),
            onPostsFetchedError,
            BabblingBrook.Client.User.Config.action_timeout + 5000   // Add five seconds to the timeout to
                                                        // allow timeouts in the domus domain to be reported.
        );
    };

    /**
     * Displays posts when they have been returned from the domus domain.
     *
     * @param {string} type The type of private messages being returned.
     * @param {number} page_number The page number of this set of posts.
     * @param {array} post_data An array of sorted post objects.
     *      See BabblingBrook.Models.posts for full definition.
     *
     * @return {void}
     */
    var onPostsFetched = function (type, page_number, onPostsReady, post_data) {
        // If the page has changed then these results are now redundant.
        if (current_type !== type) {
            return;
        }

        if (page_number === 1) {
            last_waiting_data = jQuery.extend({}, BabblingBrook.Client.Component.PostsWaiting.getWaitingData());
            defer_for_wait_data.resolve();
            BabblingBrook.Client.Component.PostsWaiting.onInboxViewed(type);
        }

        if (post_data.sort_request.update === false) {
            onPostsReady(post_data.posts);
            //registerForUpdateRequests(post_data.sort_request);
        } else {
            cascade.update(post_data.posts);
        }

        jq_post_list.removeClass('block-loading');
    };

    /**
     * Callback to use when the post is ready to display and any amendments need to be made.
     *
     * @param {object} jq_post The jQuery object that contains the post.
     * @param {object} post The BabblingBrook.Models.post object used to display the post.
     *
     * @return void
     */
    var onBeforePostDisplayed = function (jq_post, post) {
        defer_for_wait_data.done(function () {
            var timestamp;
            if (current_type === 'local_private' || current_type === 'local_public' || current_type === 'local_all') {
                if (post.status === 'private') {
                    timestamp = last_waiting_data.private_client.timestamp;
                } else if (post.status === 'public') {
                    timestamp = last_waiting_data.public_client.timestamp;
                }
            } else {
                if (post.status === 'private') {
                    timestamp = last_waiting_data.private_global.timestamp;
                } else if (post.status === 'public') {
                    timestamp = last_waiting_data.public_global.timestamp;
                }
            }
            if (parseInt(post.timestamp) > parseInt(timestamp)) {
                jq_post.addClass('new-post');
            }
        });
    }

    return {

        construct : function () {
        },

        /**
         * Changes the type of private messages being displayed on the page.
         *
         * @param {string} type What type of messages are being fetched. valid values:
         *      "local_private",
         *      "global_private",
         *      "local_public",
         *      "global_public",
         *      "local_all",
         *      "global_all",
         *      "local_sent_private",
         *      "global_sent_private"
         */
        changeType : function (type) {
            BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
                var post_template_path = '#post_inbox_template>.post';
                if (type === 'local_sent_private' || type === 'global_sent_private') {
                    post_template_path = '#post_sent_template>.post';
                }

                jq_post_list = jQuery('#post_list')
                current_type = type;
                jq_post_list.empty();
                page = 0;
                cascade = new BabblingBrook.Client.Component.Cascade(
                    jq_post_list,
                    post_template_path,
                    fetchMessages.bind(null, type),
                    '.post-replies',
                    jQuery('#post_sent_template>.post'),
                    onBeforePostDisplayed,
                    undefined,
                    undefined,
                    fetchMessages.bind(null, type)
                );
            });
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
        BabblingBrook.Client.Page.Mail.construct();
    });
});