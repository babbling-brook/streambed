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
 * @fileOverview Manages take data.
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
 * Manages a users take data.
 *
 * Takes are cached into blocks so that the requested amount of data can be built up
 * before passing it back to the client.
 * This makes requests a little more complex than they might be.
 * First of all the block number is calculated.
 * The oldest block of takes is numbred one and newer blocks increment from that.
 * The excpetion is the most recent takes which are stored in block 0 untill there are sufficient to create a new block,
 * at which time block 0 is flushed.
 * If the time on the request is zero then the block request starts at 0 and counts down
 * from the most recent block until enough takes have been requested.
 * If takes from a timestamp are requested then first of all a request is made to the
 * relavent scientia domain for the block number of that time.
 * Now that we know the block number, the cache is checked, and if they are already stored they are restored from cache.
 * Otherwise a request is sent to the scientia domain for that block of takes.
 * The qty is now checked and subsequent blocks are fetched until enough data has been fetched.
 * A complication in this process lies in the caching of the zero block as this data is constantly updated, as a result
 * the zero block is flushed at the start of every request. The scientia domain can still cache this if it wants.
 *
 * @fixme This module fetches both stream and user takes but they are not integrated together.
 */
BabblingBrook.Domus.ManageTakes = (function () {
    'use strict';

    /**
     * @type {object} takes All takes are stored here. If there is a domain and post id, but it is empty, then
     *      takes have already been requested for that post - but none were found.
     * @type {object} takes[domain] The domain that the post that was taken is stored at.
     * @type {object} takes[domain][post_id] The id of the taken post
     *      - local to the domain that the post that was taken is stored at.
     * @type {object} takes[domain][post_id][field_id] The id of the field in the post that was taken.
     * @type {number} takes[domain][post_id][field_id].value The value of the take.
     * @type {number} takes[domain][post_id][field_id].take_time Timestamp for when the post was taken.
     */
    var takes = {};

    /**
     * @type {number} Timestamp for the oldest take that was downloaded when the domus was initialised.
     *      If an post has a creation timestamp after this then it can be fetched from the takes object,
     *      if it is before this, and it does not exist in the takes object then the server needs to be checked.
     */
    var last_taken_post_time;

    /**
     * @type {object} user_takes A users take data for all posts in a stream.
     * @type {object} user_takes[domain] The domain of the users takes.
     * @type {object} user_takes[domain][username] The username of the users takes.
     * @type {object} user_takes[domain][username][stream_url] The url of the stream the takes are for.
     * @type {number} user_takes[domain][username][stream_url].refresh_frequency
     *      How long to wait before fetching new  takes.
     * @type {object} user_takes[domain][username][stream_url].block_requests Block number requests. Indexed by time.
     * @type {number} user_takes[domain][username][stream_url].block_requests.block_number. null until returned.
     * @type {Number|Undefined} user_takes[domain][username][stream_url].last_full_block
     *      ID of the last full block when the data was requested. Only used if block_id 0 has been requested.
     * @type {object} user_takes[domain][username][stream_url].blocks The Block objects.
     *      Indexed associatively using block_id.
     * @type {number} user_takes[domain][username][stream_url].blocks[block_id].from_time
     *      The from time that ablock covers.
     * @type {number} user_takes[domain][username][stream_url].blocks[block_id].to_time The to time that a block covers.
     * @type {object[]} user_takes[domain][username][stream_url].blocks[block_id].takes This users takes.
     *      An array of field objects wach containing an array of take objects
     * @type {number[]} user_takes[domain][username][stream_url].blocks[block_id].takes[field_id]
     * @type {string} user_takes[domain][username][stream_url].blocks[block_id].takes[field_id].take_domain
     *      The domain of the post that is taken.
     * @type {number} user_takes[domain][username][stream_url].blocks[block_id].takes[field_id].post_id
     *      The post_id of the post that is taken.
     * @type {number} user_takes[domain][username][stream_url].blocks[block_id].takes[field_id].value The take value.
     * @type {number} user_takes[domain][username][stream_url].blocks[block_id].takes[field_id].time The take timestamp.
     */
    var user_stream_takes = {};

    /**
     * @type {object} user_takes A users take data for all sub posts under an post.
     * @type {object} user_takes[domain] The domain of the users takes.
     * @type {object} user_takes[domain][username] The username of the users takes.
     * @type {object} user_takes[domain][username][post_domain] The domain of the post the takes are for.
     * @type {object} user_takes[domain][username][post_domain][post_id] The id of the post.
     * @type {number} user_takes[domain][username][post_domain][post_id].refresh_frequency How long to wait before
     *                                                                                       fetching new takes.
     * @type {object} user_takes[domain][username][post_domain][post_id].blocks The Block objects.
     *                                                                            Indexed associatively using block_id.
     * @type {number} user_takes[domain][username][post_domain][post_id].blocks.time A timestamp for the newest
     *                                                                                 take in the block.
     * @type {object[]} user_takes[domain][username][post_domain][post_id].blocks.takes This users takes.
     *                                                                                    An array of take objects
     * @type {string} user_takes[domain][username][post_domain][post_id].blocks.takes.take_domain The domain of the
     *                                                                                              post that is taken.
     * @type {number} user_takes[domain][username][post_domain][post_id].blocks.takes.post_id The post_id of the
     *                                                                                           post that is taken.
     * @type {number} user_takes[domain][username][post_domain][post_id].blocks.takes.value The take value.
     * @type {number} user_takes[domain][username][post_domain][post_id].blocks.takes.time The take timestamp.
     */
    var user_tree_takes;

    /**
     * @type {object} user_takes A users take data.
     * @type {object} user_takes[domain] The domain of the users takes.
     * @type {object} user_takes[domain][username] The username of the users takes.
     * @type {number} user_takes[domain][username].refresh_frequency How long to wait before fetching new takes.
     * @type {object} user_takes[domain][username].blocks The Block objects. Indexed associatively using block_id.
     * @type {number} user_takes[domain][username].blocks.time A timestamp for the newest take in the block.
     * @type {object[]} user_takes[domain][username].blocks.takes This users takes. An array of take objects.
     * @type {string} user_takes[domain][username].blocks.takes.take_domain The domain of the post that is taken.
     * @type {number} user_takes[domain][username].blocks.takes.post_id The post_id of the post that is taken.
     * @type {number} user_takes[domain][username].blocks.takes.value The take value.
     * @type {number} user_takes[domain][username].blocks.takes.time The take timestamp.
     */
    var user_takes;

    /**
     * A list of streams that have had blocks of takes fetched.
     *
     * Array of pointers to the take objects
     *
     * @type {array} streams_with_takes_fetched[domain][username]
     *      [name][major][minor][patch][block_number].takes An array of takes for this block.
     *      If this is undinfed then the takes have not been fetched for this block.
     * @type {array} streams_with_takes_fetched[domain][username]
     *      [name][major][minor][patch][block_number].to_timestamp The ending time for takes in this block
     * @type {array} streams_with_takes_fetched[domain][username]
     *      [name][major][minor][patch][block_number].from_timestamp The starting time for takes in this block.
     */
    var stream_takes = {};


    var getAllTakes, getNewBlockNumber;    // Valid circular reference defined here to prevent jslint error.

    /**
     * @type {boolean} An error flag for bailing out when a infinite recursive error occurs.
     */
    var loop_error = false;

    /**
     * Receive a User take block number request.
     *
     * @param {object} data See BabblingBrook.Models.streamUserTakeRequest with 'time',
     *                      'block_number' and 'domain' extensions for details.
     * @param {string} scientia_domain The domain the data came from.
     * @param {object} request The request object that requested this data. Needed for errors.
     *
     * @return void
     */
    var receiveUserTakeBlockNumber = function (data, scientia_domain, request) {
        var r = BabblingBrook.Models.streamUserTakeRequest(data, '', ['time', 'block_number', 'domain']);
        if (!r) {
            request.errorCallback();
            return;
        }

        if (r.type === 'stream') {
            BabblingBrook.Library.createNestedObjects(
                user_stream_takes,
                [r.domain, r.username, r.stream_url, 'block_requests'],
                'object'
            );
            user_stream_takes[r.domain][r.username][r.stream_url].block_requests[r.time] = r.block_number;
        } else if (r.type === 'tree') {
            BabblingBrook.Library.createNestedObjects(
                user_tree_takes,
                [r.domain, r.username, r.post_domain, r.post_id, 'block_requests'],
                'object'
            );
            user_tree_takes[r.domain][r.username][r.domain][r.post_id].block_requests[r.time] = null;
        } else if (r.type === 'all') {
            BabblingBrook.Library.createNestedObjects(user_takes, [r.domain, r.username, 'block_requests'], 'object');
            user_takes[r.domain][r.username].block_requests[r.time] = null;
        }
    };

    /**
     * Append new takes to a request object.
     * @param {object} request See getTakes function for definition.
     * @param {object[]} takes
     */
    var appendTakes = function (request, takes) {

        jQuery.each(takes, function (i, take) {
            // Keep adding takes until the requested qty is full or the to_timestamp is exceeded.
            if (typeof request.qty !== 'undefined') {
                if (request.results.length >= request.qty) {
                    request.finished = true;
                    return false;            // Exit the jQuery.each funciton.
                }
            }
            if (typeof request.to_timestamp !== 'undefined') {
                if (take.date_taken < request.to_timestamp) {
                    request.finished = true;
                    return false;            // Exit the jQuery.each funciton.
                }
            }
            request.results.push(jQuery.extend({}, take, true));
            return true;                // Continue with the jQuery.each funciton.
        });
    };

    /**
     * Receive user takes for a block.
     *
     * @param {object} data See BabblingBrook.Models.streamUserTakeRequest with 'block_number',
     *                      'domain' and 'takes' extensions for details.
     */
    var processReceiveUserTakeBlock = function (request, wall, data) {
        if (typeof request.last_full_block === 'undefined' && typeof data.last_full_block !== 'undefined') {
            //request.last_full_block = jQuery.extend({}, data.last_full_block, true);
            wall.last_full_block = data.last_full_block;
        }

        wall.blocks[data.block_number] = jQuery.extend({}, data.takes, true);

        // This is only usually present after fetching the 0 block. Without this check a recursive error can be caused.
        if (request.block_number === 0 && typeof wall.last_full_block === 'undefined') {
            loop_error = true;
            console.error(request, data);
            console.error('last_full_bloock is missing from data in the zero block.');
            throw 'Thread execution stopped.';
        }
        getAllTakes(request);
    };

    /**
     * Fetch the take data for the given block number.
     * @param {object} request See getTakes function for definition.
     */
    var getNewTakesBlock = function (request) {
        var blocks;
        var wall;
        if (request.type === 'stream') {
            BabblingBrook.Library.createNestedObjects(
                user_stream_takes,
                [request.domain, request.username, request.stream_url, request.field_id,'blocks'],
                'object'
            );
            wall = user_stream_takes[request.domain][request.username][request.stream_url][request.field_id];
        } else if (request.type === 'tree') {
            BabblingBrook.Library.createNestedObjects(
                user_tree_takes,
                [request.domain, request.username, request.post_domain, request.post_id, request.field_id, 'blocks'],
                'object'
            );
            wall = user_stream_takes[request.domain][request.username][request.post_domain][request.post_id][request.field_id];
        } else if (request.type === 'all') {
            BabblingBrook.Library.createNestedObjects(user_takes, [request.domain, request.username, 'blocks'], 'object');
            wall = user_takes[request.domain][request.username];
        }
        blocks = wall.blocks;

        var data = {
            username : request.username,
            stream_url : request.stream_url,
            time : request.time,
            type : request.type,
            block_number : request.block_number,
            field_id : request.field_id
        };
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            request.domain,
            'FetchUserTakesBlock',
            data,
            undefined,
            processReceiveUserTakeBlock.bind(null, request, wall),
            request.errorCallback,
            request.timeout
        );
    };

    /**
     * Fetch the block number for the given time in the current request.
     *
     * @param {object} request See getTakes function for definition.
     */
    getNewBlockNumber = function (request) {

        var data = {
            username : request.username,
            stream_url : request.stream_url,
            time : request.time,
            type : request.type
        };

        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            request.domain,
            'FetchUserTakesBlockNumber',
            data,
            undefined,
            /**
             * Success callback. Encapsulated in a clousure so that the request object can be forwarded.
             *
             * @param {object} data See the receiveUserTakeBlockNumber for a definition.
             * @param {string} scientia_domain See the receiveUserTakeBlockNumber for a definition.
             *
             * @return void
             */
            function (data, scientia_domain) {
                receiveUserTakeBlockNumber(data, scientia_domain, request);
                request.block_number = data.block_number;
                getAllTakes(request);
            },
            request.errorCallback
        );

//        BabblingBrook.Library.wait(
//            function () {        // Timeout condition.
//                return (block_request !== null);
//            },
//            function () {
//                r.block_number = block_request;
//                getAllTakes(r);
//            },
//            r.errorCallback,
//            {
//                message : 'BabblingBrook.Domus.ManageTakes - getNewBlockNumber error',
//                request : r
//            }
//        );
    };

    /**
     * Run a requests callback.
     * @param {object} request See getTakes function for definition.
     */
    var runCallback = function (request) {
        // Copy the results so that other requests that may be running concurrently do not interfere.
        var results = jQuery.extend(true, {}, request.results);
        request.successCallback(results);
    };

    /**
     * Checks if more are needed.
     * @param {object} request See getTakes function for definition.
     * @param {object} Wall Relevent section of one of user_stream_takes, user_tree_takes or user_takes.
     */
    var enough = function (request, wall) {
        if (parseInt(request.block_number, 10) === 0) {
            if (parseInt(wall.last_full_block, 10) === 0) {
                // if this is the 0 block and there are no more, then send back what there is.
                runCallback(request);
                return;
            } else {
                // Fetch the first full block.
                request.block_number = wall.last_full_block;

                getAllTakes(request);
                return;
            }
        } else if (request.block_number === 1) {
            // The oldest takes have been fetched.
            runCallback(request);
        } else {
            // Fetch the next block.
            request.block_number--;
            getAllTakes(request);
            return;
        }
    };

    /**
     * Clears the zero block from the cache.
     *
     * @param {object} request See getTakes function for definition.
     *
     * @return void
     */
    var clearZeroBlocks = function (request) {
        if (request.type === 'stream') {
            BabblingBrook.Library.createNestedObjects(
                user_stream_takes,
                [request.domain, request.username, request.stream_url, 'blocks'],
                'object'
            );
            delete user_stream_takes[request.domain][request.username][request.stream_url].blocks['0'];
        } else if (request.type === 'tree') {
            BabblingBrook.Library.createNestedObjects(
                user_tree_takes,
                [request.domain, request.username, request.post_domain, request.post_id, 'blocks'],
                'object'
            );
            delete user_tree_takes[request.domain][request.username][request.domain][request.post_id].blocks['0'];
        } else if (request.type === 'all') {
            BabblingBrook.Library.createNestedObjects(user_takes, [request.domain, request.username, 'blocks'], 'object');
            delete user_takes[request.domain][request.username].blocks['0'];
        }
    };

    /**
     * Get any results cached for streams.
     *
     * If none or not enough results then fetch more.
     *
     * @param {object} request See getTakes function for definition.
     *
     * @return void
     */
    getAllTakes = function (request) {
        if (loop_error === true) {
            return;
        }

       // Needs to have some form of error catch.
        // If the time is null then the latest block number is being requested.
        if (request.time === null && typeof request.block_number === 'undefined') {
            request.block_number = 0;
        }
        // If we don't know the block number then fetch the block number for this time.
        // This function will be re-called once the block_number has been obtained.
        if (request.time !== null && typeof request.block_number === 'undefined') {
            getNewBlockNumber(request);
            return;
        }
        // Fetch the correct wall of blocks for this request.
        var wall;
        if (request.type === 'stream') {
            BabblingBrook.Library.createNestedObjects(
                user_stream_takes,
                [request.domain, request.username, request.stream_url, request.field_id, 'blocks'],
                'object'
            );
            wall = user_stream_takes[request.domain][request.username][request.stream_url][request.field_id];
        } else if (request.type === 'tree') {
            BabblingBrook.Library.createNestedObjects(
                user_tree_takes,
                [request.domain, request.username, request.post_domain, request.post_id, request.field_id, 'blocks'],
                'object'
            );
            wall = user_stream_takes[request.domain][request.username][request.post_domain][request.post_id][request.field_id];
        } else if (request.type === 'all') {
            BabblingBrook.Library.createNestedObjects(user_takes, [request.domain, request.username, 'blocks'], 'object');
            wall = user_takes[request.domain][request.username];
        }

        // Use cached if they are available, apart from the zero block - in which case ensure they have been refetched.
        if (typeof wall.blocks[request.block_number] !== 'undefined') {
            // This block is cached. load them up.
            appendTakes(request, wall.blocks[request.block_number]);
        } else {
            // We need to fetch this block for the first time. When they have been retrieved, this function is recalled.
            getNewTakesBlock(request);
            return;
        }
        // Do we have enough yet.
        // These functions call this one recursively untill all blocks have beeen fetched.
        if (typeof request.qty !== 'undefined') {
            if (request.qty <= wall.blocks[request.block_number].length) {
                enough(request, wall);
            } else {
                // We have enough. run the callback.
                runCallback(request);
            }
        } else if (typeof request.to_timestamp !== 'undefined' && request.results.length > 0) {
            if (request.to_timestamp <= request.results[request.results.length - 1].date_taken) {
                enough(request, wall);
            } else {
                // We have enough. run the callback.
                runCallback(request);
            }
        } else if (request.results.length < 1) {
            enough(request, wall);
        }
        return;
    };

    /**
     * Append new takes to the take data.
     *
     * Called directly as a callback for fetching posts.
     *
     * @param {function} callback A function to call with the take_data,
     *      once they have been appended to the takes object.
     * @param {object} take_data an array of take data. See takes for details.
     * @refactor change to appendTakes when appendTakes function above is removed.
     */
    var tackOnTakes = function (callback, take_data) {
        jQuery.each(take_data, function(domain, post_ids) {
            if (BabblingBrook.Library.doesNestedObjectExist(takes, [domain]) === false) {
                takes[domain] = {};
            }

            jQuery.each(post_ids, function(post_id, fields) {
                takes[domain][post_id] = fields;
            });
        });
        callback(take_data);
    };

    /**
     * Callback for fetching a users latest takes data.
     *
     * @param {object} take_data Data object returned from the server.
     * @param {object} take_data.takes Object contining the takes. See takes object for details.
     * @param {object} take_data.last_post_time Unix timestamp for the creation date of the last take recorded.
     *
     * @return void
     */
    var latestTakesCallback = function (take_data) {
        takes = take_data.takes;
        last_taken_post_time = take_data.last_post_time;
    };

    /**
     * Callback for when the scientia domain returns a block of takes for a stream.
     *
     * @param {object} request The take request object.
     * @param {object} block The block that is to be be fetched.
     * @param {number} block.block_number The block number.
     * @param {number} block.from_timestamp The time that the block starts.
     * @param {number} block.to_timestamp The time that the block ends.
     * @param {object} response_data The data returned from the scientia domain.
     * @param {object[]} response_data.takes An array of take objects.
     * @param {number} response_data.takes[].value The value of the take.
     * @param {string} response_data.takes[].post_id The id of the post.
     * @param {number} response_data.takes[].timestamp The time that the post was taken.
     * @param {string} response_data.takes[].username The username of the user that made this take.
     * @param {string} response_data.takes[].domain The domain of the user that made this take.
     *
     * @returns {undefined}
     */
    var onStreamTakesBlockFetched = function (request, block, response_data) {
        var test2 = BabblingBrook.Test.isA([
            [response_data.takes, 'array']
        ]);
        if (test2 === false) {
            request.errorCallback('domus_fetch_stream_takes_block_test');
            return;
        }
        var takes_length = response_data.takes.length;
        var test2;
        for (var i=0; i<takes_length; i++) {
            var test2 = BabblingBrook.Test.isA([
                [response_data.takes[i].value, 'int'],
                [response_data.takes[i].post_id, 'string'],
                [response_data.takes[i].timestamp, 'uint'],
                [response_data.takes[i].take_user_username, 'username'],
                [response_data.takes[i].take_user_domain, 'domain'],
                [response_data.takes[i].post_user_username, 'username'],
                [response_data.takes[i].post_user_domain, 'domain'],
                [response_data.takes[i].post_domain, 'domain'],
            ]);
            if (test2 === false) {
                request.errorCallback('domus_fetch_stream_takes_block_test2');
                return;
            }
        }

        // Insert all takes into stream_takes
        var stream_array = BabblingBrook.Library.formatResourceAsArray(request.stream);
        stream_array.push(block.block_number);
        BabblingBrook.Library.createNestedObjects(stream_takes, stream_array);
        var stream_takes_stream = stream_takes[request.stream.domain][request.stream.username][request.stream.name];
        var version = request.stream.version;
        var stream_takes_stream_version = stream_takes_stream[version.major][version.minor][version.patch];
        var stream_takes_block = stream_takes_stream_version[block.block_number];
        stream_takes_block.from_timestamp = block.from_timestamp;
        stream_takes_block.to_timestamp = block.to_timestamp;
        stream_takes_block.takes = {};
        var block_takes = stream_takes_block.takes;

        jQuery.each(response_data.takes, function (i, take) {
            var new_take = {
                value : take.value,
                timestamp : take.timestamp,
                take_user : {
                    domain : take.take_user_domain,
                    username : take.take_user_username
                },
                post_user : {
                    domain : take.post_user_domain,
                    username : take.post_user_username
                }
            };
            if (typeof block_takes[take.post_domain] === 'undefined') {
                block_takes[take.post_domain] = {};
            }
            if (typeof block_takes[take.post_domain][take.post_id] === 'undefined') {
                block_takes[take.post_domain][take.post_id] = [];
            }
            block_takes[take.post_domain][take.post_id].push(new_take);
        });

        appendStreamTakesBlock(request, block_takes);
    };

    /**
     * Appends a block of takes to the request object and checks if all takes have been fetched.
     * @param {object} request The request object to append takes to.
     * @param {object} takes the takes to append.
     *
     * @returns {undefined}
     */
    var appendStreamTakesBlock = function (request, takes) {
        jQuery.extend(request.results, takes);
        request.blocks_fetched++;
        if (request.blocks_fetched === request.blocks_to_fetch) {
            request.successCallback(request.results);
        }
    };

    /**
     * Requests a block of stream takes from the scientia domain.
     *
     * @param {object} request The take request object.
     * @param {object} block The block that is to be be fetched.
     * @param {number} block.block_number The block number.
     * @param {number} block.from_timestamp The time that the block starts.
     * @param {number} block.to_timestamp The time that the block ends.
     *
     * @returns {undefined}
     */
    var requestStreamTakesBlock = function (request, block) {
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            request.stream.domain,
            'FetchStreamTakes',
            {
                stream : request.stream,
                block_number : block.block_number,
                field_id : request.field_id
            },
            false,
            onStreamTakesBlockFetched.bind(null, request, block),
            request.errorCallback,
            request.timeout
        );
    };

    /**
     * Recieves the block numbers for a stream from the scientia domain.
     *
     * @param {object} request The take request object.
     * @param {array} block_numbers An array of block number details.
     * @param {object} response_data The data returned from the scientia domain.
     * @param {array} response_data.blocks The block number data.
     * @param {number} response_data.blocks[].block_number The block number for this block.
     * @param {array} response_data.blocks[].from_timestamp The time that this block number starts.
     * @param {array} response_data.blocks[].to_timestamp The time that this block number ends.
     *
     * @returns {undefined}
     */
    var onStreamBlockNumbersFetched = function (request, response_data) {
        var test2 = BabblingBrook.Test.isA([
            [response_data.blocks, 'array']
        ]);
        if (test2 === false) {
            request.errorCallback('domus_fetch_stream_block_numbers_test');
            return;
        }
        var blocks_length = response_data.blocks.length;
        var test2;
        for (var i=0; i<blocks_length; i++) {
            var test2 = BabblingBrook.Test.isA([
                [response_data.blocks[i].block_number, 'uint'],
                [response_data.blocks[i].to_timestamp, 'uint'],
                [response_data.blocks[i].from_timestamp, 'uint']
            ]);
            if (test2 === false) {
                request.errorCallback('domus_fetch_stream_block_numbers_test2');
                return;
            }
        }

        // Create an empty container for the stream takes. Don't fetch them unless they are needed.
        var stream_array = BabblingBrook.Library.formatResourceAsArray(request.stream);
        BabblingBrook.Library.createNestedObjects(stream_takes, stream_array);
        var stream_takes_stream = stream_takes[request.stream.domain][request.stream.username][request.stream.name];
        var version = request.stream.version;
        var stream_takes_stream_version = stream_takes_stream[version.major][version.minor][version.patch];

        var blocks_length = response_data.blocks.length;
        request.blocks_length = blocks_length;
        for(var i=0; i<blocks_length; i++) {
            stream_takes_stream_version[response_data.blocks[i].block_number] = {};
            var stream_takes_block = stream_takes_stream_version[response_data.blocks[i].block_number];
            stream_takes_block.from_timestamp = response_data.blocks[i].from_timestamp;
            stream_takes_block.to_timestamp = response_data.blocks[i].to_timestamp;
        }
        getTakesFromCachedStreamBlocks(request);
    };

    /**
     * Used cached stream block numbers to fetch the takes (cached or not) for a stream.
     *
     * @param {object} A request object.
     *
     * @returns {undefined}
     */
    var getTakesFromCachedStreamBlocks = function (request) {
        var stream_array = BabblingBrook.Library.formatResourceAsArray(request.stream);
        BabblingBrook.Library.createNestedObjects(stream_takes, stream_array);
        var blocks = BabblingBrook.Library.getNestedObject(stream_takes, stream_array);
        request.blocks_to_fetch = 0;
        jQuery.each(blocks, function (block_number, block) {
            // Select any block that overlaps the request.
            // Easier to selct the inverse and negate it than define all four positive cases.
            var block_exists = (request.to_timestamp < block.from_timestamp
                && block.to_timestamp < request.from_timestamp);
            if (block_exists === false) {
                request.blocks_to_fetch++;
                if (typeof block.takes === 'undefined') {
                    block.block_number = block_number;
                    requestStreamTakesBlock(request, block);
                } else {
                    appendStreamTakesBlock(request, block.takes);
                }
            }
        })
        if (request.blocks_to_fetch === 0) {
            request.successCallback({});
        }
    };

    /**
     * Fetch all the block numbers for a stream.
     *
     * @param {object} request The take request object.
     *
     * @returns {undefined}
     */
    var requestStreamBlockNumbers = function (request) {
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            request.stream.domain,
            'FetchStreamBlockNumbers',
            {
                from_timestamp : request.from_timestamp,
                to_timestamp : request.to_timestamp,
                stream : request.stream
            },
            false,
            onStreamBlockNumbersFetched.bind(null, request),
            request.errorCallback,
            request.timeout
        );
    };

    return {

        /**
         * Get takes for a user. Three use cases :
         *     latest = All the latest takes by this user. These are latest by post timestamp, not take
         *     timestamp. These are loaded when the domus starts.
         *     stream = All takes by this user within a stream.
         *     tree = All takes by this user within the sub posts of an post.
         *     post = The takes on all fields of an post.
         *
         * @param {string} domain The domain of the user that takes are being requested for.
         * @param {string} username The name of the user that takes are being requested for.
         * @param {Number|Null} from_timestamp The time from which to fetch takes for. Null = now.
         * @param {function} successCallback The function to call once the data has been fetched.
         *                            Accepts two paramaters: an array of takes and an error object.
         *                            See BabblingBrook.Models.streamUserTakeRequest for definition.
         *
         * @param {String|Undefined} stream_url The url of a stream that takes are being requested for.
         * @param {String|Undefined} post_domain The domain of the post that sub post takes are being requested for.
         * @param {Number|Undefined} post_id The id of the post that sub post takes are being requested for.
         * @param {Number|Undefined} qty The quantity of takes to fetch. Either this or to_timestamp must be populated.
         * @param {Number|Undefined} to_timestamp The time up to which to fetch takes. Either this or qty must be populated.
         * @param {Number|Undefined} field_id The field id to fetch takes for. Defaults to 2 : the main value field.
         *
         * @return void
         */
        getTakesForUser : function (domain, username, from_timestamp, successCallback, errorCallback, stream_url,
            post_domain, post_id, qty, to_timestamp, field_id
        ) {
            if (field_id === 'undefined') {
                field_id = 2;
            }

            if (typeof qty === 'undefined' && typeof to_timestamp === 'undefined') {
                console.error('qty or to_timestamp must be populated.');
                throw 'Thread execution halted.';
            }

            var type = 'all';
            if (typeof stream_url === 'string') {
                type = 'stream';
            } else if (typeof post_domain === 'string') {
                type = 'tree';
            }

            var results = [];
            // This request object is passed around until the results are processed.
            var request = {
                domain : domain,
                username : username,
                time : from_timestamp,
                to_timestamp : to_timestamp,
                block_number : undefined,
                successCallback : successCallback,
                errorCallback : errorCallback,
                stream_url : stream_url,
                post_domain : post_domain,
                post_id : post_id,
                results : results,
                qty : qty,
                type : type,
                field_id : field_id
            };
           // clearZeroBlocks(request); cant call this yet, as we don't know the users.
            getAllTakes(request);
        },


        /**
         * Get all the takes for a stream between two times.
         *
         * @param {string} domain The domain of the user that takes are being requested for.
         * @param {string} username The name of the user that takes are being requested for.
         * @param {Number} from_timestamp The time from which to fetch takes for.
         * @param {Number|null} to_time The time up to which to fetch takes. Null = now.
         * @param {function} successCallback The function to call once the data has been fetched.
         *                            Accepts one paramaters: an array of user/take objects.
         *
         * @param {String|Undefined} stream The stream that takes are being requested for.
         * @param {Number|Undefined} field_id The field id to fetch takes for. Defaults to 2 : the main value field.
         *
         * @return void
         */
        getTakesForStream : function (from_timestamp, to_timestamp, successCallback, errorCallback,
            stream, field_id
        ) {
            if (field_id === 'undefined') {
                field_id = 2;
            }
            // This request object is passed around until the results are processed.
            var request = {
                to_timestamp : to_timestamp,
                from_timestamp : from_timestamp,
                successCallback : successCallback,
                errorCallback : errorCallback,
                stream : stream,
                field_id : field_id,
                results : {},
                blocks_fetched : 0,
                blocks_to_fetch : null
            };

            var stream_array = BabblingBrook.Library.formatResourceAsArray(stream);
            var do_cached_results_exist = BabblingBrook.Library.doesNestedObjectExist(stream_takes, stream_array);
            if (do_cached_results_exist === true) {
                getTakesFromCachedStreamBlocks(request);
            } else {
                requestStreamBlockNumbers(request);
            }
        },


//        /**
//         * Fetch the most recent takes for the logged in user.
//         *
//         * This is called when the domus is loaded as it preloads many of the takes that are going to be
//         * requested soon after loading.
//         * NB. Latest is sorted by post creation date and not the take date. This makes it possible to know
//         * when it is neccesary to fetch a take and when there isn't one.
//         *
//         * @return void
//         */
//        fetchLatest : function () {
//            var url = '/' + BabblingBrook.Domus.User.username + '/data/getlatesttakes';
//            BabblingBrook.Library.get(
//                url,
//                latestTakesCallback,
//                'domus_get_takes_user'
//            );
//        },

        /**
         * Fetches takes for a particular post.
         *
         * Used when an post is fetched or when the client requests take data on an post.
         *
         * @param {string} domain The domain of the post to fetch takes for.
         * @param {number} post_id The id of the post local to the domain, which takes are being requested for.
         * @param {number} [post_creation_timestamp] The creation timestamp of the post that
         *      takes are being fetched for. Used to accertain if the server should be checked for takes.
         *      If the date is older than last_taken_post_time or undefined and the take is not recorded
         *      then the server will be called.
         * @param {function} successCallback Used to send the requested data back to the client.
         * @param {function} errorCallback Used to send an error back to the client.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @return array An array of field ids containeg take values and timestamps.
         *      If a field is missing then it has not been taken.
         */
        getTakesForPost : function (domain, post_id, post_creation_timestamp,
            successCallback, errorCallback, timeout
        ) {
            if (BabblingBrook.Library.doesNestedObjectExist(takes, [domain, post_id]) === true) {
                var post_takes = {};
                post_takes[domain] = {};
                post_takes[domain][post_id] = takes[domain][post_id];
                successCallback(post_takes);
                return;
            }

            if(typeof post_creation_timestamp !== 'undefined' && post_creation_timestamp > last_taken_post_time) {
                var empty_takes = {};
                empty_takes[domain] = {};
                empty_takes[domain][post_id] = [];
                successCallback(empty_takes);
            }

            // Fetch from the server.
            var url = '/' + BabblingBrook.Domus.User.username + '/data/getposttakes';
            BabblingBrook.Library.post(
                url,
                {
                    post_domain : domain,
                    post_id : post_id,
                    username : BabblingBrook.Domus.User.username
                },
                tackOnTakes.bind(null, successCallback),
                errorCallback,
                'GetTakes_failed',
                timeout
            );

        }

    };
}());