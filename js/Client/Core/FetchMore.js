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
 * @fileOverview Keeps a record of which streams are requesting new data and when to fetch it.
 * @author Sky Wickenden
 */

/**
 * @namespace
 * Updates requests are reconfigured each time one returns so that the queue in the domus
 * domain does not get overwhelmed.
 * @package JS_Client
 */
BabblingBrook.Client.Core.FetchMore = (function () {
    'use strict';
    /**
     * @type {object[]} sort_requests Registered sort requests
     * @type {string} sort_requests.streams An array of stream objects that represent this sort request.
     * @type {number} sort_requests.post_id Id of the post to setup a repeat request for. Only neeeded for tree requests.
     * @type {number} sort_requests.refresh_frequency Contains the refresh rate in seconds.
     * @type {string} sort_requests.type The type of sort request. Valid values are 'stream' and 'tree'.
     * @type {object} sort_requests.filter Filter to use on this stream.
     * @type {string} sort_requests.filter.name The name of the filter.
     * @type {number} sort_requests.filter.priority The priority of this search request.
     * @type {string[]} sort_requests.moderation_rings An array of urls pointing to moderation rings to be used
     *                                           with this sort request.
     * @type {number} sort_requests.time_last_ran The time this update request last ran.
     * @type {boolean} sort_requests.running Is this request currently running.
     */
    var sort_requests = [];

    var doStreamArraysMatch = function (streams1, streams2) {
        var length1 = streams1.length();
        var length2 = streams2.length();
        if (length1 !== length2) {
            return;
        }

        for(var i=0; i<length1; i++) {
            if (BabblingBrook.Library.doResourcesMatch(streams1[i], streams2[i]) === false) {
                return false;
            }
        }
        return true;
    };

    /**
     * Checks if a request is already registered.
     *
     * Returns true if just one filter matches a request with an existing filter.
     *
     * @param {object} request See BabblingBrook.Client.Core.FetchMore.register for details.
     * @return {Boolean|Number} false or the pre existing stream index.
     */
    var exists = function (request) {
        var stream_index = false;
        jQuery.each(sort_requests, function (i, sort_request) {
            if (doStreamArraysMatch(request.streams, sort_request.streams) === true) {
                if (request.post_id === sort_request.post_id && request.filter.url === sort_request.filter.url) {
                    stream_index = i;
                    return false;        // Exit from jQuery.each loop.
                }
            }
            return true;        // Continue jQuery.each loop.
        });
        return stream_index;
    };

    /**
     * Success callback after fetching a stream.
     *
     * Used to reset the time_last_ran and then call the real callback.
     *
     * @param {object} stream The stream object in the local streams object that was used to genreated this callback.
     * @param {object} stream_data The data returned with the stream request.
     *
     * @return void
     */
    var streamFetched = function (stream, stream_data) {
        stream.time_last_ran = Math.round(new Date().getTime() / 1000);
        stream.running = false;
        stream.success(stream_data);
    }

    /**
     * This function runs every second to check if updates should be requested.
     */
    var repeat = function () {
        var now = Math.round(new Date().getTime() / 1000);
        jQuery.each(sort_requests, function (i, sort_request) {
            var refresh_frequency = sort_request.refresh_frequency;
            if (BabblingBrook.Client.User.Config.override_stream_update_frequency > 0) {
                refresh_frequency = BabblingBrook.Client.User.Config.override_stream_update_frequency;
            }
            if (!sort_request.running && refresh_frequency < now - sort_request.time_last_ran) {
                BabblingBrook.Client.Core.Interact.postAMessage(
                    {
                        sort_request : {
                            type : sort_request.type,
                            client_uid : sort_request.client_uid,
                            streams : sort_request.streams,
                            post_id : sort_request.post_id,
                            filter : sort_request.filter,
                            moderation_rings : sort_request.moderation_rings,
                            refresh_frequency : sort_request.refresh_frequency,
                            update : true,
                            block_numbers : sort_request.block_numbers
                        }
                    },
                    'SortRequest',
                    streamFetched.bind(null, sort_request),
                    sort_request.error
                );
                sort_request.running = true;

            }
        });
        setTimeout(repeat, 1000);
    };
    setTimeout(repeat, 1000);        // Set the initial cycle going.

    /**
     * Public functions.
     */
    return {

        /**
         * Register a new stream.
         *
         * @param {object} request
         * @param {string} request.stream_url Url of the stream to setup a repeat request for.
         * @param {number} request.post_id Id of the post to setup a repeat request for.
         *                                  Only neeeded for tree requests.
         * @param {string} request.type The type of sort request. Valid values are 'stream' and 'tree'.
         * @param {object[]} request.filter Filter objects to use on this stream.
         * @param {string} request.filter.url The url of the filter.
         * @param {string} request.filter.name The name of the filter.
         * @param {string} request.filter.priority The name of the filter.
         * @param {string[]} request.moderation_rings An array of urls pointing to moderation rings to be used
         *                                            with this sort request.
         * @param {number} request.refresh_frequency The frequency in seconds at which new results are requested.
         * @param {function} request.success The success callback.
         * @param {function} request.error The error callback.
         */
        register : function (request) {
            //!!!!!!!! Curently only running one update request at a time until added the code
            // to remove requests that are not running.
            sort_requests = [];

            var stream_index = exists(request);

            // Only register if it is not already registered.
            // If a variable needs changing then remove it first and then reregister.
            if (stream_index === false) {

                request.time_last_ran = Math.round(new Date().getTime() / 1000);      // Unix now timstamp, in seconds.
                request.running = false;
                sort_requests.push(request);
            } else {

                sort_requests[stream_index].running = false;
                sort_requests[stream_index].time_last_ran = Math.round(new Date().getTime() / 1000);
            }

        },

        /**
         * This will remove update requests.
         */
        remove : function () {

        },

        /**
         * This will remove update requests that are not in the stream navbar.
         */
        removeAllNotInNav : function () {

        }

    };
}());
