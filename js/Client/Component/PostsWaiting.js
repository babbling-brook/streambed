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
 * @fileOverview Keeps track of the number of posts waiting to be viewed.
 * @author Sky Wickenden
 */

/**
 * This is singleton Keeps track of the number of posts waiting to be viewed. It should be loaded on page load.
 *
 *
 * @namespace Javascript used for the private messaging inbox page.
 * @package JS_Client
 */
BabblingBrook.Client.Component.PostsWaiting = (function () {
    'use strict';

     /**
      * @type object The waiting data as returned from the users domus domain.
      */
     var waiting_data;

     var defer_waiting_data = jQuery.Deferred();

    /**
     * Update the view of the post count - indicating how many posts are waiting to be viewed.
     *
     * @return void
     */
    var updatePostCountView = function() {
        var local_post_count = parseInt(waiting_data.private_client.qty) + parseInt(waiting_data.public_client.qty);
        var global_post_count = parseInt(waiting_data.private_global.qty) + parseInt(waiting_data.public_global.qty);
        if (local_post_count > 0) {
            jQuery('#message_count').text(' (' + local_post_count + ')').removeClass('hide');
            jQuery('#local_message_count').text(' (' + local_post_count + ')').removeClass('hide');
        } else {
            jQuery('#message_count').addClass('hide');
            jQuery('#local_message_count').addClass('hide');
        }

        if (global_post_count > 0) {
            jQuery('#global_message_count').text(' (' + global_post_count + ')').removeClass('hide');
        } else {
            jQuery('#global_message_count').addClass('hide');
        }
    };


    /**
     * Success function for requesting the waiting message count from a users domus domain.
     *
     * @param {object} data
     *
     * @return void
     */
    var onFetchedWaitingPostCount = function(data) {
        jQuery('#message_count').removeClass('checking');
        jQuery('#local_message_count').removeClass('checking');
        jQuery('#global_message_count').removeClass('checking');

        setTimeout(fetchWaitingPostCount, 300000); // every 5 min

        waiting_data = data;
        defer_waiting_data.resolve();

        updatePostCountView();
    };

    /**
     * Throws an error if the process fails.
     *
     * @return void
     */
    var onFetchedWaitingPostCountError = function() {
        throw('Failed to fetch WaitingPostCount.');
    };

    var fetchWaitingPostCount = function() {
        jQuery('#message_count').addClass('checking');
        jQuery('#local_message_count').addClass('checking');
        jQuery('#global_message_count').addClass('checking');

        BabblingBrook.Client.Core.Interact.postAMessage(
            {},
            'GetWaitingPostCount',
            onFetchedWaitingPostCount,
            onFetchedWaitingPostCountError
        );
    };

    /**
     * Update the timestamp for when an inbox was viewed.
     *
     * @param {boolean} global IS this the global inbox.
     * @param {string} type Is this a public or private inbox.
     *
     * @returns {void}
     */
    var updateViewTime = function (global, type) {
        var now = Math.round(new Date().getTime() / 1000);
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                global : global,
                time_viewed : now,
                type : type
            },
            'SetWaitingPostCount',
            function () {},
            function () {}
        );
    };

    return {

        construct : function () {
            fetchWaitingPostCount();
        },

        /**
         * Updates the timestamp for when the global inbox was last viewed.
         *
         * @param {string} inbox_type The type of inbox. See Posts.constructor for definition.
         */
        onInboxViewed : function (inbox_type) {
            defer_waiting_data.done(function (){
                var now = Math.round(new Date().getTime() / 1000);
                var empty_stamp = {
                    qty : 0,
                    timestamp : now
                }
                switch (inbox_type) {
                    case 'local_private':
                        updateViewTime(false, 'private');
                        waiting_data.private_client = empty_stamp;
                        break;

                    case 'global_private':
                        updateViewTime(true, 'private');
                        waiting_data.private_global = empty_stamp;
                        break;

                    case 'local_public':
                        updateViewTime(false, 'public');
                        waiting_data.public_client = empty_stamp;
                        break;

                    case 'global_public':
                        updateViewTime(true, 'public');
                        waiting_data.public_global = empty_stamp;
                        break;

                    case 'local_all':
                        updateViewTime(false, 'private');
                        updateViewTime(false, 'public');
                        waiting_data.private_client = empty_stamp;
                        waiting_data.public_client = empty_stamp;
                        break;

                    case 'global_all':
                        updateViewTime(true, 'private');
                        updateViewTime(true, 'public');
                        waiting_data.private_global = empty_stamp;
                        waiting_data.public_global = empty_stamp;
                        break;
                }
                updatePostCountView();
            });
        },

        /**
         * Returns the waiting_data object.
         *
         * @returns {object}
         */
        getWaitingData : function () {
            return waiting_data;
        }
    };
}());