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
 * @fileOverview Receives messages from the sugestions domain.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Domus !== 'object') {
    BabblingBrook.Domus = {};
}



/**
 * @namespace Singleton class that manages interaction with rhythm filters for the client.
 * @package JS_Domus
 */
BabblingBrook.Domus.Filter = (function () {
    'use strict';

    /**
     * @type {boolean} Has the iframe been inserted into the page yet?
     */
    var iframe_inserted = false;

    // function declared here due to jsLint issue. Function is validly called circularly in a timeout.
    var startTopSort;

    /*
     * @type {object[]} filters Array of filters. See BabblingBrook.Models.filter for a full definition.
     */
    var filters = [];

    /**
     * Stores filter sort requests awaiting processing.
     * @note this is very similar to an array of sortRequest obejcts.
     *       If working on it then amend any incsistancies and merge the definitions.
     * @type {object[]} queue
     * @type {number} queue.uid A unique id for this item.
     *      Needed as the queue is resorted, so can not rely on the array key.
     * @type {string} queue.type The type of request. See BabblingBrook.Models.sortType for valid values.
     * @type {string} queue.streams An array of streams that are having their posts sorted.
     * @type {number} queue.streams[].fetched_to_timestamp The to timestamp that posts have been fetched for.
     * @type {number} queue.streams[].fetched_from_timestamp The from timestamp that posts have been fetched from.
     * @type {object} queue.rhythm A standart rhythm name object for the filter.
     * @type {Number|Null} queue.post_id Used to restrict the sort to a single post (used in tree sorts).
     * @type {string} queue.rhythm_url The url of the Rhythm that is to be used for the sort - connects to filters.
     * @type {number} [queue.priority] The priority for this sort (only prioritises within the domain)
     *      Requests with a higher priority than the process running will inturupt it.
     * @type {number} queue.refresh_frequencey The time in seconds to allow cached
     *      results to be returned before searching for an update.
     * @type {number} queue.status Has it been processed. 0 = no, 1 = in process.
     * @type {number} queue.processed_time A timestamp for when the results were processed.
     * @type {object[]} queue.moderation_rings An array of urls pointing the
     *      moderation rings to use in this sort request.
     * @type {boolean} queue.moderation_rings.moderated
     * @type {boolean} queue.update Is this an update request.
     * @type {boolean} queue.private_page The page number if this is a request for private posts.
     * @type {boolean} queue.moderation_data_loaded Set to true when the moderation_rings have been processed.
     * @type {boolean} queue.private_posts_loaded Set to true when the private posts have loaded.
     * @type {number} queue.start_time The time that the request started.
     * @type {number|undefined} queue.sort_qty The quantity of posts that are being requested by the filter.
     * @type {number|undefined|null} queue.posts_from_timestamp The start_timestamp to fetch posts from.
     * @type {number|undefined|null} queue.posts_to_timestamp The end timestamp to fetch posts upto.
     *      If null uses the current time.
     * @type {string} queue.successCallbacks Used to call the client once the sort request has processed.
     *      This is an array so that identical requests can be merged and all callbacks still called.
     * @type {string} queue.errorCallbacks Used to call the client if the sort request errors.
     *      This is an array so that identical requests can be merged and all callbacks still called.
     * @type {string} queue.block_numbers The stream block numbers that posts have been fetched from.
     * @type {string} queue.filterSuccessCallback Used to call the filter domain once the posts have been fetched.
     * @type {string} queue.filterErrorsCallback Used to call the filter domain if there
     * @type {string} queue.client_domain The domain of the client that made the sort request.
     * @type {number} queue.client_timeout The time that the client making this request will timeout.
     * @type {array} queue.posts The posts that have been fetched for the rhythm in this sort request.
     *      is an error with fetching the posts.
     * @type {boolean} queue.with_content Should the posts be fetched with their content.
     * @type {object} queue.client_params Any client paramaters that have been passed through from the client
     *      website.
     * @param {number} filter_timeout The time that the filter making the request for posts will timeout.
     */
    var queue = [];

    /**
     * @type {number} The next valid id value for an item in the queue.
     */
    var next_queue_id = 1;

    /**
     * @type {number} The queue id of the currently running sort request. 0 = none.
     */
    var processing = 0;

    /**
     * Returns the currently processing sort request from the queue.
     */
    var getCurrentSortRequest = function () {
        var sort_request;
        jQuery.each(queue, function (index, request) {
            if (request.uid === processing) {
                sort_request = request;
                return false;    // Exit the jQuery.each function.
            }
            return true;        // continue the jQuery.each function.
        });
        return sort_request;
    };

    /**
     * Finish the current queue item.
     */
    var finishQueueRequest = function () {
        jQuery.each(queue, function (index, request) {
            if (request.uid === processing) {
                queue.splice(index, 1);
                return false;    // Exit the jQuery.each function.
            }
            return true;        // continue the jQuery.each function.
        });
        processing = 0;
    };

    /**
     * Callback for when a sortRequest is in error.
     *
     * @param {string} error_code An error code that is forwarded to the client domain.
     *      See BabblingBrook.models.clientErrors for valid values.
     * @param {object} error_data Data associated with this error. May contain data on the ring that is in error.
     * @param {object} sort_request The current sort request.
     *
     * @return void
     */
    var sortError = function (error_code, error_data, sort_request) {
        console.error('Filter error');
        console.log(sort_request);
        console.log(error_code);
        console.log(error_data);
        for (var i = 0; i < sort_request.errorCallbacks.length; i++) {
            sort_request.errorCallbacks[i](error_code, error_data);
        }
        finishQueueRequest();
    };

    /**
     * Find a filter in the filter object, return a reference to it.
     *
     * @param {string} The url of the filter.
     * @return {object} The found filter.
     */
    var findFilter = function (filter_url) {
        var found_filter = false;
        jQuery.each(filters, function (index, filter) {
            if (filter.url === filter_url) {
                found_filter = filter;
                return false;        // Escape the jQuery.each function.
            }
            return true;            // Continue the jQuery.each function.
        });
        if (found_filter === false) {
            console.error('Filter not found.');
        }
        return found_filter;
    };

    /**
     * Fetches an algorithms javascript.
     *
     * @param {object} sort_request
     *
     * @return vod
     */
    var getRhythm = function (sort_request) {
        var successCallback = function (rhythm) {
            var filter = findFilter(sort_request.filter.url);
            filter.rhythm = rhythm;
            if(typeof sort_request.deferred_start !== 'undefined') {
                sort_request.deferred_start.resolve();
            }
        };
        var scientia_data = {url : sort_request.filter.url};
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            sort_request.rhythm.domain,
            'FetchRhythm',
            scientia_data,
            false,
            successCallback,
            sortError.bind(null, 'SortRequest_filter', {}, sort_request),
            sort_request.client_timeout
        );
    };

    /**
     * Appends new filters to the filter object. If the rhythm code is not present then it is requested.
     *
     * @param {object} filter A filter object. See BabblingBrook.Models.filter for full definition.
     */
    var appendFilter = function (sort_request) {

        var found = false;
        jQuery.each(filters, function (j, existing_filter) {
            if (sort_request.filter.url === existing_filter.url) {
                found = true;
                return false;    // Break from the jQuery.each function.
            }
            return true;        // Continue with the jQuery.each function.
        });

        if (!found) {
            sort_request.filter.rhythm = undefined;
            filters.push(sort_request.filter);
            getRhythm(sort_request);
        }

    };

    /**
     * Saves the current sort results to the server so that they can be cached as public results.
     *
     * This is only done if the user who generated the results is the owner of the stream.
     *
     * @param {object} posts See BabblingBrook.Models.posts for a full definition.
     * @param {object} sort_request See queue object for a full definition.
     *
     * @returns {void}
     */
    var sendOwnersResultsToServer = function(posts, sort_request) {
        var post_scores = [];
        for (var i=0; i < posts.length; i++) {
            if (parseInt(posts[i].sort) >= 0) {
                post_scores.push({
                    post_id : posts[i].post_id,
                    score : posts[i].sort
                });
            }
        }

        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            BabblingBrook.Domus.User.domain,
            'StoreOwnersStreamSortResults',
            {
                posts : post_scores,
                // @fixme Only storing the results for the first stream fetched.
                stream : sort_request.streams[0],
                top_parent_id : sort_request.post_id,
                filter_rhythm : sort_request.rhythm
            },
            true,
            function () {},
            sortError.bind(null, 'StoreOwnersStreamSortResults_failed', {}, sort_request),
            sort_request.client_timeout
        );
    };

    /**
     * Checks if all the passed in stream name objects are owned by the current user.
     *
     * @param {array} streams The streams to check
     *
     * @returns {boolean}
     */
    var areAllStreamsOwnedByCurrentUser = function (streams) {
        var length = streams.length;
        for (var i=0; i<length; i++) {
            if (streams[i].domain !== BabblingBrook.Domus.User.domain
                || streams[i].username !== BabblingBrook.Domus.User.username
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Receives data from the filter and caches before passing back to the client.
     *
     * @param {object} posts See BabblingBrook.Models.posts for a full definition.
     *
     * @return {void}
     */
    var finishSortRequest = function (posts) {
        // Duplicate so that deletes do not effect stored data.
        var sort_request = jQuery.extend(true, {}, getCurrentSortRequest());
        sort_request.processed_time = Math.round(new Date().getTime() / 1000);
        if (typeof sort_request.streams !== 'undefined'
            && areAllStreamsOwnedByCurrentUser(sort_request.streams)
            && sort_request.update === false
        ) {
            sendOwnersResultsToServer(posts, sort_request);
        }
        // Delete data from the sort_request object that is not part of the protocol.
        delete sort_request.uid;
        delete sort_request.status;
        delete sort_request.filter.rhythm;
        delete sort_request.filter.priority;
        delete sort_request.private_posts_loaded;
        var sort_result = {
            sort_request : sort_request,
            posts : posts
        };
        BabblingBrook.Domus.SortedStreamResults.appendResults(
            sort_request.type,
            sort_request.streams,
            sort_request.rhythm,
            sort_request.moderation_rings,
            sort_request.post_id,
            sort_request.client_domain,
            sort_request.client_params,
            posts,
            3600
        );
        // Call all the success callbacks.
        for (var i = 0; i < sort_request.successCallbacks.length; i++) {
            sort_request.successCallbacks[i](sort_result);
        }

        finishQueueRequest();

        // Start the next sort process.
        startTopSort();
    };

    /**
     * Callback for when the filter domain has finished processing a sort request.
     *
     * @param {object} sort_request The sort request that has been finsihed.
     * @param {object} data
     * @param {object} data.posts The sorted posts.
     *                             See BabblingBrook.Models.posts with 'sorted' and possibly 'tree' extensions.
     * @param {string} data.type The type of sort request that is returning.
     * @param {Number|Undefined} data.post_id The top parent post id. Only present if this is a tree sort.
     */
    var sortFinished = function (sort_request, data) {
        BabblingBrook.TestErrors.clearErrors();
        var test1 = BabblingBrook.Models.sortType(data.type);

        var test2;
        var test3 = true;
        if (data.type === 'tree') {
            test2 = BabblingBrook.Models.posts(data.posts, '', ['tree', 'sorted']);
            test3 = BabblingBrook.Test.isA([data.post_id, 'string']);
        } else {
            test2 = BabblingBrook.Models.posts(data.posts, '', ['sorted']);
        }
        if (!test1 || !test2 || !test3) {
            sortError(
                'filter_domus_sort_finished',
                {
                    filter_message_data : data
                },
                sort_request
            );
        }
        finishSortRequest(data.posts);

    };

    /**
     * Checks if the top sort request is being processed and if not switches to it.
     */
    startTopSort = function () {    // No var statment as it is declared above due to jsLint.
        if (processing !== 0) {
            return;
        }

        // Sort the queue so that we can be sure to process the rhythm with the highest priority.
        queue.sort(function (a, b) {
            return a.filter.priority - b.filter.priority;
        });
        if (typeof queue[0] === 'undefined') {
            return;
        }
        var queue_id = 0;
        // There is a sort to process.
        processing = queue[queue_id].uid;
        queue[queue_id].status = 1;
        // @note This is disabled because it causes the page loading icon to spin every time it reloads the iframe.
        //       Need a way to refresh the javascript space without reloading.
        // Reload the filter iframe.
        //BabblingBrook.Domus.Loaded.filter = false;
        //document.getElementById('filter').src = document.getElementById('filter').src;
        //setTimeout(function () {
        //    if (BabblingBrook.Domus.Loaded.filter) {


        // Have to wait for both the filter domain and the filter rhythm to load before continuing.
        queue[queue_id].deferred_start = jQuery.Deferred();
        BabblingBrook.Domus.Loaded.onFilterLoaded(function () {
            queue[queue_id].deferred_start.done(function () {
                // Send a request to the filter rhythm domain to process this sort request.
                var user = {
                    username : BabblingBrook.Domus.User.username,
                    domain : BabblingBrook.Domus.User.domain
                };
                BabblingBrook.Domus.Interact.postAMessage(
                    {
                        type : queue[queue_id].type,
                        client_uid : queue[queue_id].client_uid,
                        rhythm : queue[queue_id].filter.rhythm.rhythm,
                        streams : queue[queue_id].streams,
                        posts_from_timestamp : queue[queue_id].posts_from_timestamp,
                        posts_to_timestamp : queue[queue_id].posts_to_timestamp,
                        post_id : queue[queue_id].post_id,
                        user : user,
                        client_domain : queue[queue_id].client_domain,
                        client_params : queue[queue_id].client_params
                    },
                    'filter',
                    'RunRhythm',
                    sortFinished.bind(null, queue[queue_id]),
                    sortError.bind(null, 'filter_run_rhythm', {}, queue[queue_id]),
                    undefined,
                    queue[queue_id].client_timeout
                );
            });
        });
        if (typeof queue[queue_id].filter.rhythm !== 'undefined') {
            queue[queue_id].deferred_start.resolve();
        }
    };

    /**
     * Checks if to arrays of moderation ring objects are the same.
     * @param {object[]} rings1 An array of moderation ring objects.
     * @param {object[]} rings2 Another array of moderation ring objects.
     */
    var areModerationRingsEqual = function (rings1, rings2) {

        if (rings1.length !== rings2.length) {
            return false;
        }

        var all_equal = true;
        jQuery.each(rings1, function (i, ring1) {
            var inner_equal = false;
            jQuery.each(rings2, function (j, ring2) {
                if (ring1.url === ring2.url) {
                    inner_equal = true;
                }
            });
            if (!inner_equal) {
                all_equal = false;
                return false;    // Exit the jQuery.each function.
            } else {
                return true;    // Continue the jQuery.each function.
            }
        });
        return all_equal;
    };

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

    /**
     * Add a sort request to the queue.
     * @param {object} request For details see BabblingBrook.Domus.Filter.queue.
     *      Includes additional successCallback and errorCallback functions.
     */
    var addToQueue = function (request) {
        // Defaults.
        request.processed_time = 0;
        request.status = 0;
        var found = false;
        jQuery.each(queue, function (index, sort_request) {
            if (doStreamsMatch(request.streams, sort_request.streams) === true
                && request.filter.url === sort_request.filter.url
                && request.post_id === sort_request.post_id  // Differentiates between stream and tree requests.
                && request.update === sort_request.update
                && areModerationRingsEqual(request.moderation_rings, sort_request.moderation_rings) === true
                && request.private_page === sort_request.private_page
                && request.type === sort_request.type
            ) {
                // If the new priority is higher then use the new one.
                if (request.filter.priority > queue[index].filter.priority) {
                    queue[index].filter.priority = request.filter.priority;
                }
                queue[index].status = 0;
                // Need to send the results to both callbacks.
                queue[index].successCallbacks.push(request.successCallbacks[0]);
                queue[index].errorCallbacks.push(request.errorCallbacks[0]);

                found = true;
                return false; // exit the .each function.
            }

            return true; // Continue the .each function.
        });

        if (found === false) {
            request.uid = next_queue_id;
            request.start_time = Math.round(new Date().getTime() / 1000);
            queue.push(request);
            next_queue_id++;
        }
    };

    var fixToAndFromTimestamps = function(request) {
        request.posts_from_timestamp = null;
        request.posts_to_timestamp = null;
        var posts_length = request.posts.length;
        for (var i=0; i<posts_length; i++) {
            if (request.posts_from_timestamp === null || request.posts[i].timestamp < request.posts_from_timestamp) {
                request.posts_from_timestamp = request.posts[i].timestamp;
            }
            if (request.posts_to_timestamp === null || request.posts[i].timestamp > request.posts_to_timestamp) {
                request.posts_to_timestamp = request.posts[i].timestamp;
            }
        }
    };

    /**
     * Checks to see if all moderation data has been processed.
     *
     * If it has then the posts are passed back to the filter for sorting.
     *
     * @param {object} request The sort request being moderated.
     *      see BabblingBrook.Models.posts for full definition.
     */
    var hasModerationFinished = function (request) {
        var ring_count = Object.keys(request.moderation_rings).length;

        var all_moderated = true;
        if (typeof request.streams !== 'undefined') {
            var streams_length = request.streams.length;
            for (var i=0; i<streams_length; i++) {
                if (typeof request.streams[i].moderation_count === 'undefined'
                    || request.streams[i].moderation_count !== ring_count
                ) {
                    all_moderated = false;
                }
            }
        } else {
            if (request.rings.moderation_count !== ring_count) {
                all_moderated = false;
            }
        }

        if (all_moderated === true) {
            request.moderation_data_loaded = true;
            var request_posts = {
                posts : request.posts,
                block_numbers : request.block_numbers
            };
            fixToAndFromTimestamps(request);
            request.filterSuccessCallback(request_posts);
        }
    };

    /**
     * Remove any posts that are listed in this moderation ring with a negative value.
     * If all moderation rings have now been processed then set the moderation_data_loaded flag.
     *
     * @param {object} request The sort request being moderated.
     * @param {object} ring The ring that is moderating the sort request
     * @param {number} stream_index The index of the stream in the request object that is being moderated.
     * @param {object} moderation_takes The takes from the ring that are used to moderate this request.
     * @param {object} moderation_takes.take A take that is used to moderate this request.
     * @param {number} moderation_takes.take.post_id The post id of a take that is being moderated.
     * @param {number} moderation_takes.take.value The value of the modertion take.
     *
     * @return void
     */
    var moderate = function (request, ring, stream_index, moderation_takes) {
        jQuery.each(moderation_takes, function (index, take) {
            jQuery.each(request.posts, function (index, post) {
                // For some reason the jQuery.each still iterates over deleted posts.
                if (typeof post !== 'undefined') {
                    if (post.post_id === take.post_id && take.value < 0) {
                        request.posts.splice(index, 1);
                    }
                }
            });
        });
        if (typeof stream_index !== 'undefined') {
            request.streams[stream_index].moderation_count++;
        } else {
            request.rings.moderation_count++;
        }
        hasModerationFinished(request);
    };

    /**
     * Fetch moderation data from the rings scientia domain.
     *
     * @param {object} request
     * @param {number} oldest_post_date The time to get takes to
     * @param {number} stream_index The index of the stream in the request to fetch moderation data for.
     *
     * @return void
     */
    var getModerationData = function (request, oldest_post_date, stream_index) {
        var stream_url;
        if (typeof stream_index !== 'undefined') {
            request.streams[stream_index].moderation_count = 0;
            stream_url = BabblingBrook.Library.makeStreamUrl(request.streams[stream_index], 'json');
        } else {
            request.rings.moderation_count = 0;
        }
        jQuery.each(request.moderation_rings, function (i, ring) {
            var domain = BabblingBrook.Library.extractDomain(ring.url);
            var username = BabblingBrook.Library.extractUsername(ring.url);

            var onGetModerationDataError = function () {
                var error_data = {
                    ring_domain : domain,
                    ring_username : username
                };
                request.filterErrorCallback('GetPosts_moderation', error_data);
            };
            BabblingBrook.Domus.ManageTakes.getTakesForUser(
                domain,
                username,
                request.posts_to_timestamp,
                moderate.bind(null, request, ring, stream_index),
                onGetModerationDataError,
                stream_url,
                request.post_domain,
                request.post_id,
                undefined,    // Qty is not used.
                oldest_post_date,
                request.client_timeout
            );
        });

        hasModerationFinished(request);
    };

    /**
     * Checks if all the required user posts have loaded and sends them to the rhtyhtm if they have.
     *
     * Called by each process, the last to call it will succeed.
     * The logic could be simplifed by providing a fallback for options that only load from one
     * source, but by listing them all the logic is clear.
     *
     * @param {object} request The request being checked.
     *
     * @returns {void}
     */
    var haveAllUserPostsLoaded = function (request) {
        var posts_to_sort = {
            posts : request.posts
        }
        if (request.type === 'local_public' || request.type === 'global_pubilc') {
            if (request.user_posts_loaded === true) {
                request.filterSuccessCallback(posts_to_sort);
            }
        } else if (request.type === 'local_all' || request.type === 'global_all') {
            if (request.public_posts_loaded === true && request.private_posts_loaded === true) {
                request.filterSuccessCallback(posts_to_sort);
            }
        } else if (request.type === 'local_private' || request.type === 'global_private'
            || request.type === 'local_sent_private' || request.type === 'global_sent_private'
            || request.type === 'sent_all'
        ) {
            if (request.private_posts_loaded === true) {
                request.filterSuccessCallback(posts_to_sort);
            }
        }
    }

    /**
     * Checks if private posts have loaded for all streams in the request object.
     *
     * @param {array} streams A reference to the streams array in the request object.
     *
     * @returns {boolean}
     */
    var haveAllPrivatePostsLoaded = function (streams) {
        if (typeof streams === 'undefined') {
            return true;
        }
        var length = streams.length;
        for(var i=0; i< length; i++) {
            if (typeof streams[i].private_posts_loaded === 'undefined'
                || streams[i].private_posts_loaded === false
            ) {
                return false;
            }
        }
        return true;
    };

    var fetchingPrivatePosts = function (stream_index, request, oldest_post_date, newest_post_time, type) {
        var stream_url;
        if (typeof stream_index !== 'undefined') {
            stream_url = BabblingBrook.Library.makeStreamUrl(request.streams[stream_index], '', true);
        }

        BabblingBrook.Library.post(
            '/data/getprivateposts',
            {
                client_domain : request.client_domain,
                stream_url : stream_url,
                post_id : request.post_id,
                oldest_post_date : oldest_post_date,
                newest_post_date : newest_post_time,
                page : request.private_page,
                type : type,
                with_content : request.with_content
            },
            /**
             * Callback for fetching private posts.
             *
             * @param {object} posts Container for standard post objects.
             *
             * @return void
             */
            function (private_post_data) {
                if (private_post_data.posts.length > 0) {
                    var private_posts = private_post_data.posts;
                    // See if the private last date is older than the main stream last date.
                    var last_private_post_date = private_posts[private_posts.length - 1].timestamp;
                    if (last_private_post_date < oldest_post_date) {
                        oldest_post_date = last_private_post_date;
                    }
                    request.posts.push.apply(request.posts, private_post_data.posts);
                }

                if (typeof stream_index !== 'undefined') {
                    request.streams[stream_index].private_posts_loaded = true;
                }

                request.private_posts_loaded = haveAllPrivatePostsLoaded(request.streams);
                // Now we have the private post data we can fetch the moderation data
                if (request.type === 'tree' || request.type === 'stream') {
                    getModerationData(request, oldest_post_date, stream_index);
                } else {
                    haveAllUserPostsLoaded(request);
                }
            },
            request.filterErrorCallback.bind(null, 'GetPosts_private'),
            'filter_getprivateposts',
            request.filter_timeout
        );
    };

    /**
     * Fetch the required private posts for this sort request.
     *
     * @param {object} request See request definition above.
     * @param {number} oldest_post_date
     * @returns {undefined}
     */
    var fetchPrivatePosts = function(request, oldest_post_date) {
        // request.posts_to_timestamp will upload as string 'null' when it has a null value,
        // but it needs to be undefined.
        var newest_post_time;
        if (request.posts_to_timestamp !== null) {
            newest_post_time = request.posts_to_timestamp;
        }

        var type = request.type;
        if (type === 'local_all') {
            type = 'local_private';
        }
        if (type === 'global_all') {
            type = 'global_private';
        }

        if (typeof request.streams === 'undefined') {
            fetchingPrivatePosts(undefined, request, oldest_post_date, newest_post_time, type);
        } else {
            var length = request.streams.length;
            for(var i=0; i<length; i++) {
                fetchingPrivatePosts(i, request, oldest_post_date, newest_post_time, type);
            }
        }
    };

    /**
     * Iterates through a stream array to check if all streams have posts.
     *
     * @param {object} streams The streams object in the request object.
     *
     * @returns {boolean}
     */
    var haveAllStreamPostsBeenFetched = function(streams) {
        var length = streams.length;
        for(var i=0; i<length; i++) {
            if (typeof streams[i].fetched === 'undefined' || streams[i].fetched !== true
            ) {
                return false;
            }
        }
        return true;
    }

    /*
     * Receives posts from third party scientia iframes for streams and trees and builds them up ready to
     * return to the rhythm domain. If all streams have reported back then they are returned to the filter domain.
     *
     * @param {object} stream
     * @param {object} data
     * @param {object} data.posts See BabblingBrook.Models.posts for details.
     * @param {number} data.refresh_frequency Time in seconds until new data is available.
     * @param {string} scientia_domain
     */
    var receiveTreeAndStreamPosts = function (stream, data, scientia_domain) {
        var request = getCurrentSortRequest();
        BabblingBrook.TestErrors.clearErrors();
        var test1 = BabblingBrook.Test.isA([
            [data.posts, 'array'],
            [data.type, 'string'],
            [data.refresh_frequency, 'uint|undefined'],
            [data.sort_id, 'uint'],
            [data.block_numbers, 'array']
        ]);
        var test2 = BabblingBrook.Models.posts(data.posts, '');
        if (test1 === false || test2 === false) {
            request.filterErrorCallback('GetPosts_failed');
            return;
        }

        // Ensure there is a block number if there is an post.
// This is causing errors when there are only posts in the zero block.
//        if (data.posts.length > 0 && data.block_numbers.length === 0) {
//            request.filterErrorCallback('GetPosts_failed_block_number_missing');
//            return;
//        }

        if (typeof data.refresh_frequency !== 'number') {
            data.refresh_frequency = 999999;
        }

        request.refresh_frequency = data.refresh_frequency;
        request.block_numbers = data.block_numbers;

        request.posts.push.apply(request.posts, data.posts);

        stream.fetched = true;

        if (haveAllStreamPostsBeenFetched(request.streams) === true) {
            request.tree_and_stream_data_loaded = true;
            // Fetch the private data.
            // This is done seperately as the moderation data may be on a seperate domain to the posts.
            // Have to wait until now to fetch the moderation ring takes
            // as we don't have an end date until the data has retunred.
            var oldest_post_date = Math.round(new Date().getTime() / 1000);    // Unix now timstamp, in seconds.
            if (data.posts.length > 0) {
                oldest_post_date = data.posts[data.posts.length - 1].timestamp;
            }

            if (request.moderation_rings.length !== 0) {
                request.moderation_data_loaded = false;
            }

            fetchPrivatePosts(request, oldest_post_date);
        }

    };

    /**
     * Fetch posts for stream and tree sort requests.
     *
     * @param {object} request The sort request to fetch posts for.
     *
     * @returns {void}
     */
    var fetchStreamAndTreePosts = function (request) {
        var length = request.streams.length;
        for (var i=0; i<length; i++) {
            var post_request_data = {
                sort_request : {
                    type : request.type,
                    client_uid : request.client_uid,
                    stream : request.streams[i],
                    post_id : request.post_id,
                    qty : request.sort_qty,
                    posts_from_timestamp : request.posts_from_timestamp,
                    posts_to_timestamp : request.posts_to_timestamp,
                    moderation_rings : request.moderation_rings,
                    update : request.update,
                    sort_id : processing,
                    block_numbers : request.block_numbers,
                    client_timeout : request.filter_timeout,

                },
                with_content : request.with_content,
                search_phrase : request.search_phrase,
                search_title : request.search_title,
                search_other_fields : request.search_other_fields
            };
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request.streams[i].domain,
                'GetPosts',
                post_request_data,
                true,
                receiveTreeAndStreamPosts.bind(null, request.streams[i]),
                request.filterErrorCallback.bind('GetPosts_failed'),
                request.filter_timeout
            );
        }
    }


    /**
     * Fetch posts for public user sort requests.
     *
     * @param {object} request The sort request to fetch posts for.
     *
     * @returns {void}
     */
    var fetchPublicUserPosts = function (request) {
       // request.posts_to_timestamp will upload as string 'null' when it has a null value,
        // but it needs to be undefined.
        var newest_post_time;
        if (request.posts_to_timestamp !== null) {
            newest_post_time = request.posts_to_timestamp;
        }
        var oldest_post_time;
        if (request.posts_from_timestamp !== null) {
            oldest_post_time = request.posts_from_timestamp;
        }

        BabblingBrook.Library.post(
            '/data/getuserposts',
            {
                // removed this since there can be multiple streams.
                //stream_url : BabblingBrook.Library.makeStreamUrl(request.streams[i]),
                post_id : request.post_id,
                oldest_post_date : oldest_post_time,
                newest_post_date : newest_post_time,
                page : request.private_page,
                type : request.type,
                username : request.user.username,
                domain : request.user.domain,
                with_content : request.with_content,
                search_phrase : request.search_phrase,
                search_title : request.search_title,
                search_other_fields : request.search_other_fields
            },
            /**
             * Callback for fetching private posts.
             *
             * @param {object} post_data A container object ofr posts.
             * @param {object} post_data.posts Container for standard post objects.
             *
             * @return void
             */
            function (post_data) {
                if (post_data.posts.length > 0) {
                    request.posts.push.apply(request.posts, post_data.posts);
                }

                request.public_posts_loaded = true;
                haveAllUserPostsLoaded(request);

            },
            request.filterErrorCallback.bind(null, 'GetPosts_private'),
            'filter_getprivateposts',
            request.filter_timeout
        );
    };

    /**
     * Insert the filter rhythm iframe into the DOM.
     *
     * @returns {undefined}
     */
    var insertIframe = function () {
        console.log('creating filter rhythm iframe.');
        if (iframe_inserted === false) {
            var main_domain = window.location.host.substring(6);
            jQuery('body').append(' ' +
                '<iframe style="display:none" id="filter" name="filter_window" ' +
                        'src="http://filter.' + main_domain + '">' +
                '</iframe>'
            );
            iframe_inserted = true;
        }
    };

    /**
     * Creates a nested takes object from the passed in array of streams.
     *
     * @param {array} streams An array of stream name objects with a user_takes paramater.
     * @param {object} A user name object for the user that made the takes.
     *
     * @returns {object} The user_takes nested in a heirarchical stream  object.
     */
    var createNestedTakes = function (streams, user) {

        var nested_takes = {};
        var streams_length = streams.length;
        for (var i=0; i<streams_length; i++) {
            if (typeof nested_takes[streams[i].domain] === 'undefined') {
                nested_takes[streams[i].domain] = {};
            }
            var stream_domain = nested_takes[streams[i].domain];
            if (typeof stream_domain[streams[i].username] === 'undefined') {
                stream_domain[streams[i].username] = {};
            }
            var stream_username = stream_domain[streams[i].username];
            if (typeof stream_username[streams[i].name] === 'undefined') {
                stream_username[streams[i].name] = {};
            }
            var stream_name = stream_username[streams[i].name];
            if (typeof stream_name[streams[i].version.major] === 'undefined') {
                stream_name[streams[i].version.major] = {};
            }
            var stream_major = stream_name[streams[i].version.major];
            if (typeof stream_major[streams[i].version.minor] === 'undefined') {
                stream_major[streams[i].version.minor] = {};
            }
            var stream_minor = stream_major[streams[i].version.minor];
            if (typeof stream_minor[streams[i].version.patch] === 'undefined') {
                stream_minor[streams[i].version.patch] = {};
            }
            var stream_patch = stream_minor[streams[i].version.patch];
            var takes = streams[i].user_takes[user.domain][user.username];
            jQuery.each(takes, function (i, take) {
                stream_patch[take.post_id] = take;
            });
        }
        return nested_takes;
    };

    var onTakesForUserStreamFetched = function (user, sort_request, stream_count, onTakesFetched, stream_takes) {
        sort_request.streams[stream_count].user_takes[user.domain][user.username] = stream_takes;
        var streams_length = sort_request.streams.length;
        var fetched_all = true;
        for (var i=0; i<streams_length; i++) {
            if (sort_request.streams[i].user_takes[user.domain][user.username] === false) {
                fetched_all = true;
            }
        }
        if (fetched_all === true) {
            var nested_takes = createNestedTakes(sort_request.streams, user);
            onTakesFetched(nested_takes);
        }
    };

    var onTakesForStreamFetched = function (sort_request, onTakesFetched, stream_takes) {
        jQuery.extend(true, sort_request.stream_takes, stream_takes);
        sort_request.stream_takes_fetched++;
        if (sort_request.stream_takes_fetched === sort_request.streams.length) {
            onTakesFetched(sort_request.stream_takes);
        }
    };

    /**
     * Does the passed in streams array contain any stream versions that are 'latest' or 'all'.
     *
     * @param {array} streams An array of stream name objects.
     *
     * @returns {boolean}
     */
    var doesStreamContainALatestOrAllVersion = function (stream) {
        if (stream.version.major === 'latest'
            || stream.version.minor === 'latest'
            || stream.version.patch === 'latest'
            || stream.version.major === 'all'
            || stream.version.minor === 'all'
            || stream.version.patch === 'all'
        ) {
            return true;
        } else {
            return false;
        }
    };

    /**
     * Replaces the 'latest' and 'all' streams a request with a exact versions.
     *
     * @param {object} sort_request The sort_request that is being processed.
     * @param {number} stream_index The index of the 'latest' or 'all' stream version that is being replaced.
     * @param {object} response_data The data returned from the scientia domain.
     * @param {object} response.data.streams The new exact stream name objects that are replacing the inexact one.
     *
     * @returns {void}
     */
    var onExactStreamsFetched = function (sort_request, stream_index, response_data) {
        var test1 = BabblingBrook.Test.isA([
            [response_data.streams, 'array'],
        ]);
        if (test1 === false) {
            sort_request.errorCallbacks[0]('SortRequest_all_stream_conversion_failed');
            return;
        }
        var streams_length = response_data.streams.length;
        var test2;
        for (var i=0; i<streams_length; i++) {
            test2 = BabblingBrook.Models.streamName(response_data.streams[i], true);
            if (test2 === false) {
                sort_request.errorCallbacks[0]('SortRequest_all_stream_conversion_failed');
                return;
            }
        }

        // this splices an array of exact streams into the middle of the existing array of streams.
        var args = [stream_index, 1].concat(response_data.streams);
        Array.prototype.splice.apply(sort_request.streams, args);

        var any_inexact_streams_found = false;
        var streams_length = sort_request.streams.length;
        for (var i=0; i<streams_length; i++) {
            if (doesStreamContainALatestOrAllVersion(sort_request.streams[i]) === true) {
                any_inexact_streams_found = true;
            }
        }

        if (any_inexact_streams_found === false) {
            initiateSortRequest(sort_request);
        }
    };

    /**
     * Converts any 'latest' stream objects to exact stream object in the sort request.
     *
     * @param {type} sort_request The sort request to convert 'latest' streams for.
     *
     * @returns {void}
     */
    var convertStreamUrls = function (sort_request) {
        if (typeof sort_request.streams === 'undefined') {
            initiateSortRequest(sort_request);
            return;
        }

        var streams_length = sort_request.streams.length;
        var any_inexact_streams_found = false;
        for (var i=0; i<streams_length; i++) {
            if (doesStreamContainALatestOrAllVersion(sort_request.streams[i]) === true) {
                any_inexact_streams_found = true;
                BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                    sort_request.streams[i].domain,
                    'FetchExactStreamVersions',
                    {
                        stream : sort_request.streams[i],
                    },
                    false,
                    onExactStreamsFetched.bind(null, sort_request, i),
                    sort_request.errorCallbacks[0].bind(null, 'SortRequest_latest_error'),
                    sort_request.client_timeout
                );
            }
        }

        if (any_inexact_streams_found === false) {
            initiateSortRequest(sort_request);
        }
    };

    /**
     * Initialise the sort request after streams have been converted to exact versions.
     *
     * @param {type} sort_request
     *
     * @returns {undefined}
     */
    var initiateSortRequest = function (sort_request) {
        addToQueue(sort_request);
        // Check if currently running rhythm is the request at the top of the queue. Switch over if not.
        startTopSort();
    };

    /**
     * The BabblingBrook.Domus.Filter constructor.
     */
    return {

        /**
         * Called when the page has loaded to create the filter domain.
         */
        construct : function () {
            // Regularly call the start function to check if any queued sorts need processing.
            setInterval(function () {
                startTopSort();
            }, 1000);        // 1 second.
        },

        /**
         * A sort has been requested by the client. Process it.
         *
         * @param {object} sort_request See BabblingBrook.Models.sortRequest for a definition (base model).
         * @param {function} successCallback Called with the success data.
         * @param {function} errorCallback Called if there is an error.
         * @param {string} client_domain The domain of the client that requested the posts.
         *
         * @return void
         */
        sortRequested : function (sort_request, successCallback, errorCallback, client_domain, client_timeout) {
            insertIframe();
            sort_request.rhythm = BabblingBrook.Library.makeRhythmFromUrl(sort_request.filter.url);
            if (typeof sort_request.update !== 'undefined' && sort_request.update === false) {
                var cached_results = BabblingBrook.Domus.SortedStreamResults.getCachedResults(
                    sort_request.type,
                    sort_request.streams,
                    sort_request.rhythm,
                    sort_request.moderation_rings,
                    sort_request.post_id,
                    sort_request.client_params
                );
                if (cached_results !== false) {
                    sort_request.processed_time = cached_results.timestamp;
                    sort_request.sort_qty = cached_results.posts.length;
                    var sort_result = {
                        sort_request : sort_request,
                        posts : cached_results.posts
                    }
                    successCallback(sort_result);
                    return;
                }
            }

            // Callbacks are in arrays incase multiple requests are made - all callbacks are called after the
            // the request has processed.
            sort_request.successCallbacks = [successCallback];
            sort_request.errorCallbacks = [errorCallback];
            sort_request.client_timeout = client_timeout;
            sort_request.private_posts_loaded = false;
            appendFilter(sort_request);
            sort_request.filter = findFilter(sort_request.filter.url);
            sort_request.client_domain = client_domain;
            if (typeof sort_request.posts_from_timestamp === 'undefined') {
                sort_request.posts_from_timestamp = null;
            }
            if (typeof sort_request.posts_to_timestamp === 'undefined') {
                sort_request.posts_to_timestamp = null;
            }

            // The sort request is registered after any 'latest' and 'all' stream names are convert to exact versions.
            convertStreamUrls(sort_request);
        },

        /**
         * Get the posts needed to process a sort request.
         *
         * Called from the rhythm domain when it is ready for the posts.
         *
         * @param {number} sort_qty The quantity of posts to fetch for sorting.
         * @param {boolean} with_content Should the posts be fetched with their content.
         * @param {string|undefined} search_phrase The search phrase to use when fetching posts.
         * @param {boolean|undefined} search_title If search_phrase is set, this decides
         *      if the title fields should be searched.
         * @param {boolean|undefined} search_other_fields If search_phrase is set,
         *      this decides if the fields other than the title should be searched.
         * @param {number} posts_from_timestamp Date from which posts should start to be fetched.
         * @param {number} posts_to_timestamp Date from which posts should to be fetched untill.
         * @param {function} successCallback Called with the success data.
         * @param {function} errorCallback Called if there is an error.
         *      Important: This sends an error to the filter domain which in turns sends it back to this domus to
         *      forward it to the client.
         * @param {number} filter_timeout The time that the filter making this request will timeout.
         *
         * @return void
         */
        getPosts : function (sort_qty, posts_from_timestamp, posts_to_timestamp, with_content,
            search_phrase, search_title, search_other_fields, successCallback, errorCallback, filter_timeout
        ) {
            var sort_request = getCurrentSortRequest();
            sort_request.posts = [];
            if (typeof posts_from_timestamp === 'undefined') {
                posts_from_timestamp = null;
            }
            if (typeof posts_to_timestamp === 'undefined') {
                posts_to_timestamp = null;
            }
            sort_request.sort_qty = sort_qty;
            sort_request.filterSuccessCallback = successCallback;
            sort_request.filterErrorCallback = errorCallback;
            sort_request.filter_timeout = filter_timeout;
            sort_request.with_content = with_content;
            sort_request.search_phrase = search_phrase;
            sort_request.search_title = search_title;
            sort_request.search_other_fields = search_other_fields;

            // Public user posts are not constrained by stream/tree block numbers and can be fetched straight away.
            if (sort_request.type === 'local_public' || sort_request.type === 'global_pubilc'
                || sort_request.type === 'local_all' || sort_request.type === 'global_all'
            ) {
                fetchPublicUserPosts(sort_request);
            }

            // Stream and tree private posts have to wait until the oldest public post date is known before
            // fetching them, so they are fetched later.
            if (sort_request.type !== 'local_public' && sort_request.type !== 'global_pubilc'
                && sort_request.type !== 'stream' && sort_request.type !== 'tree'
            ) {
                fetchPrivatePosts(sort_request);
            }
            if (sort_request.type === 'stream' || sort_request.type === 'tree') {
                fetchStreamAndTreePosts(sort_request);
            }
        },

        /**
         * Recieves a request from the rhythm to fetch some takes for a user.
         *
         * @param {object} user A standard user object for the user whose takes are being fetched.
         * @param {object} field_id The streams field id that takes are being fetched for.
         * @param {function} onTakesFetched Success callback.
         * @param {function} onTakesFetchedError Error callback.
         * @returns {undefined}
         */
        getTakesForUser : function (user, field_id, onTakesFetched, onTakesFetchedError) {
            var sort_request = getCurrentSortRequest();
            var streams_length = sort_request.streams.length;
            for (var i=0; i<streams_length; i++) {
                var stream_url = BabblingBrook.Library.makeStreamUrl(sort_request.streams[i], 'json');
                if (typeof sort_request.streams[i].user_takes === 'undefined') {
                    sort_request.streams[i].user_takes = {};
                    sort_request.streams[i].user_takes[user.domain] = {};
                    sort_request.streams[i].user_takes[user.domain][user.username] = {};
                    sort_request.streams[i].user_takes[user.domain][user.username] = false;
                }
                BabblingBrook.Domus.ManageTakes.getTakesForUser(
                    user.domain,
                    user.username,
                    sort_request.posts_to_timestamp,
                    onTakesForUserStreamFetched.bind(null, user, sort_request, i, onTakesFetched),
                    onTakesFetchedError,
                    stream_url,
                    undefined,
                    undefined,
                    sort_request.sort_qty,
                    undefined,
                    field_id
                );
            }
        },

        /**
         * Recieves a request from the rhythm to fetch all user takes for the posts it is processing.
         *
         * @param {number} field_id The id of the field to fetch takes for.
         * @param {function} onTakesFetched Success callback.
         * @param {function} onTakesFetchedError Error callback.
         *
         * @returns {undefined}
         */
        getTakes : function (field_id, onTakesFetched, onTakesFetchedError) {
            var sort_request = getCurrentSortRequest();
            sort_request.stream_takes_fetched = 0;
            sort_request.stream_takes = {};
            var streams_length = sort_request.streams.length;
            for (var i=0; i<streams_length; i++) {
                if (typeof sort_request.streams[i].user_takes === 'undefined') {
                    sort_request.streams[i].user_takes = {};
                }
                BabblingBrook.Domus.ManageTakes.getTakesForStream(
                    sort_request.posts_from_timestamp,
                    sort_request.posts_to_timestamp,
                    onTakesForStreamFetched.bind(null, sort_request, onTakesFetched),
                    onTakesFetchedError,
                    sort_request.streams[i],
                    field_id
                );
            }
        },

        /**
         * Fetches the url of rhythm of the currently running sort request.
         *
         * @returns {string}
         */
        getCurentRhythmUrl : function () {
            var sort_request = getCurrentSortRequest();
            var url = BabblingBrook.Library.makeRhythmUrl(sort_request.filter.rhythm.rhythm, 'getdata');
            return url;
        }

    };
}());