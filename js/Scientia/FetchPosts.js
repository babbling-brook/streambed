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
 * @fileOverview Manages the fetching of posts
 * @author Sky Wickenden
 */
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Scientia !== 'object') {
    BabblingBrook.Scientia = {};
}

/**
 * @namespace A singleton class that manages the fetching of post records from this scientia domain and passing
 *            them back to the users domus domain.
 * @package JS_Scientia
 */
BabblingBrook.Scientia.FetchPosts = (function () {
    'use strict';

    /**
     * @type {object} streams Holds additional details about cached posts
     * @type {string} streams.[url] The url of the stream that the posts belong to. with a 'json' action.
     * @type {number} streams.[url].last_block_number The number of the last full block in the stream.
     * @type {number} streams.[url].refresh_frequency How often new posts are provided for update requests.
     * @type {number} streams.[url].cache_timestamp A timestamp for when the last_block_number was last fetched.
     * @type {object} streams.[url].stream_blocks.[block_number]
     *      A list of objects indexed by block numbers that have been fetched.
     * @type {object} streams.[url].stream_blocks.[block_number].timestamp Time the block was fetched.
     * @type {array} streams.[url].stream_blocks.[block_number].with_content Do these posts have content.
     * @type {array} streams.[url].stream_blocks.[block_number].post_ids The ids of the posts in this block.
     * @type {object} streams.[url].tree_blocks.[post_id] A tree block for a top parent post id.
     * @type {object} streams.[url].tree_blocks.[post_id][tree_block_number] The block number in this tree.
     *      A list of objects indexed by block numbers that have been fetched.
     * @type {object} streams.[url].tree_blocks.[post_id][tree_block_number] The parent post id of this tree block.
     * @type {object} streams.[url].tree_blocks.[post_id][tree_block_number].timestamp Time the block was fetched.
     * @type {array} streams.[url].tree_blocks.[post_id][tree_block_number].with_content Do these posts have content.
     * @type {array} streams.[url].tree_blocks.[post_id][tree_block_number].post_ids
     *      The ids of the posts in this block.
     *
     */
    var streams = {};

    /**
     * @type {number} Records the request number - used to access the correct settings.
     */
    var request;

    /**
     * Request settings are stored here in case requests overlap while data is being fetched.
     * @type {object[]} settings Records settings for each request.
     * @type {string} settings.type Valid values are : 'stream' and 'tree'.
     * @type {string} settings.stream_url
     * @type {number} settings.post_id
     * @type {object} settings.posts Holds the posts that are being sought.
     *                                See BabblingBrook.Scientia.FetchPosts.posts object for definition.
     * @type {number[]} settings.block_numbers An array of the block numbers that posts have been fetched from.
     * @type {number} settings.last_block_number The last block number in this stream.
     * @type {number} settings.qty The qty of posts to fetch.
     * @type {number|null} settings.posts_from_timestamp The date to fetch posts from.
     * @type {number|null} settings.posts_to_timestamp The date to fetch posts to.
     * @type {boolean} settings.update Is this an update request.
     * @type {Number} settings.sort_id The domus domains unique id for this request.
     * @type {number} settings.refresh_frequency Time in seconds until new data is available. Downloaded with requests.
     * @type {number} settings.seek_direction Are posts being loaded from past_to_present or present_to_past.
     *      This is set deppending on the values of posts_from_timestamp and posts_to_timestamp.
     * @type {number} settings.timeout Timestamp in milliseconds for when this request will timeout.
     * @type {boolean} seettings.with_content Should the posts be fetched with full content rows.
     *
     * @fixme refactor this so that the settings object is passed around rather than stored here.
     *             As in BabblingBrookTakeData.
     */
    var settings = [];

    /**
     * Check that a block number is valid.
     *
     * @param {type} request The current request object.
     * @param {number} block_number The block number to check for validity.
     *
     * @returns {boolean}
     */
    var isBlockNumberValid = function (request, block_number) {
        if (parseInt(block_number) > parseInt(settings[request].last_block_number)) {
            return false;
        }
        if (block_number === 0 || block_number === 'none') {
            return false;
        }

        return true;
    };

    /**
     * Calculate which block should be fetched next.
     *
     * The latest posts are stored in block zero. The first full block is the last_block_number
     * and the earliest posts are stored in block one.
     *
     * @param {type} request The current request object.
     *
     * @returns {number|false}
     */
    var calculateNextBlockNumber = function (request) {
        var next_block_number = null;
        if (settings[request].seek_direction === 'past_to_present') {
            next_block_number = ++settings[request].current_block_number;
        } else {
            if (settings[request].current_block_number === 0) {
                next_block_number = settings[request].last_block_number;
            } else {
                next_block_number = --settings[request].current_block_number;
            }
        }

        if (isBlockNumberValid(request, next_block_number) === false) {
            return 'none';
        } else {
            return next_block_number;
        }
    };

    /**
     * Do we have enough posts yet. If not then request more, if so then send them to the users domus domain.
     *
     * @param {number} next_block_number The number of the block to fetch next.
     * @param {number} request The request number used for accessing this requests settings.
     *
     * @return boolean
     */
    var haveEnough = function (next_block_number, request) {
        // 'none' is reserved for when the first block has been fetched and there are no earlier posts to fetch.
        if (next_block_number === 'none') {
            return true;
        }

        var posts = settings[request].posts;
        if (posts.length === 0) {
            return false;
        }

        var qty =  settings[request].qty;
        if (posts.length > qty) {
            return true;
        }

        // Ensure the posts are date sorted.
        posts.sort(function (a, b) {
            return b.timestamp - a.timestamp;
        });

        var from = settings[request].posts_from_timestamp;
        if (settings[request].seek_direction === 'present_to_past') {
            if (posts[0].timestamp < from) {
                return true;
            }
        }

        var to = settings[request].posts_to_timestamp;
        if (settings[request].seek_direction === 'past_to_present') {
            if (posts[posts.length - 1].timestamp < to) {
                return true;
            }
        }

        return false;
    };

    /**
     * Copy posts from the cache into the current request.
     *
     * @returns {boolean}
     */
    var copyCachedPosts = function (request, block_number) {
        var stream_url = BabblingBrook.Library.makeStreamUrl(settings[request].stream, '');
        if (typeof streams[stream_url] !== 'object') {
            return false;
        }
        var block_type;
        var blocks;
        if (settings[request].type === 'stream') {
            block_type = 'stream_blocks';
            blocks = streams[stream_url].stream_blocks;
        } else if (settings[request].type === 'tree') {
            block_type = 'tree_blocks';
            var tree_blocks = streams[stream_url].tree_blocks;
            if (typeof tree_blocks[settings[request].post_id] !== 'undefined') {
                blocks = tree_blocks[settings[request].post_id];
            }
        }
        if (typeof blocks === 'undefined' || typeof blocks[block_number] === 'undefined') {
            return false;
        }

        if (blocks[block_number].with_content === false && settings[request].with_content === true) {
            return false;
        }
        var post_list = blocks[block_number].post_ids;
        for (var i = 0; i < post_list.length; i++) {
            var post = BabblingBrook.Scientia.Cache.getItem('post_header', post_list[i]);
            if (post !== false && settings[request].with_content === true) {
                var content = BabblingBrook.Scientia.Cache.getItem('post_content', post_list[i]);
                if (content !== false) {
                    post.content = content;
                }
            }
            if (post !== false) {
                settings[request].posts.push(post);
            }
        }
        settings[request].refresh_frequency = streams[stream_url].refresh_frequency;
        // @fixme block_numbers should be appended in a function that tests for duplicates.
        settings[request].block_numbers.push(block_number);
        return true;
    };

    /**
     * Cache some newly fetched posts for reuse later.
     *
     * @param {string} stream_url The stream url to cache the posts in.
     * @param {object} new_posts A block of new posts to cache.
     * @param {number} block_number The number of this block of posts.
     * @param {boolean} with_content Is the content included in the posts.
     * @param {number|undefined} [post_id] If these posts exist in a tree, then this is the top parent post_id.
     * @param {boolean} [create_block=true] Should the container for this post be created. Should only be true
     *      if a complete block of posts is being passsed in. Otherwise errors will arrise where partial blocks
     *      are returned.
     *
     * @returns {void}
     */
    var cachePosts = function (stream_url, new_posts, block_number, with_content, post_id, create_block) {
        // Create the structure for the cache references in the stream object.
        if (create_block === true || typeof create_block === 'undefined') {
            if (typeof post_id === 'undefined') {
                if (BabblingBrook.Library.doesNestedObjectExist(streams, [stream_url, 'stream_blocks']) === true) {
                    var stream_blocks = streams[stream_url].stream_blocks;
                    if (typeof stream_blocks[block_number] === 'undefined') {
                        stream_blocks[block_number] = {};
                        stream_blocks[block_number].post_ids = [];
                        var now = Math.round(new Date().getTime() / 1000);
                        stream_blocks[block_number].timestamp = now;
                        stream_blocks[block_number].with_content = with_content;
                    }
                }
            }
            if (typeof post_id !== 'undefined') {
                if (BabblingBrook.Library.doesNestedObjectExist(streams, [stream_url, 'tree_blocks']) === true) {
                    var tree_blocks = streams[stream_url].tree_blocks;
                    if (typeof tree_blocks[post_id] === 'undefined') {
                        tree_blocks[post_id] = {};
                    }
                    if (typeof tree_blocks[post_id][block_number] === 'undefined') {
                        tree_blocks[post_id][block_number] = {};
                        tree_blocks[post_id][block_number].post_ids = [];
                        var now = Math.round(new Date().getTime() / 1000);
                        tree_blocks[post_id][block_number].timestamp = now;
                        tree_blocks[post_id][block_number].with_content = with_content;
                    }
                }
            }

        }

        // Cache in the global scientia cache.
        for (var i = 0; i < new_posts.length; i++) {
            var post_header = jQuery.extend(true, {}, new_posts[i]);
            var content;
            if (typeof post_header.content !== 'undefined') {
                post_header.content = undefined;
                content = jQuery.extend(true, {}, new_posts[i].content);
            }
            BabblingBrook.Scientia.Cache.cacheItem('post_header', new_posts[i].post_id, 'memory', post_header);
            if (typeof content !== 'undefined') {
                BabblingBrook.Scientia.Cache.cacheItem('post_content', new_posts[i].post_id, 'memory', content);
            }

            var stream_name = {
                name : new_posts[i].stream_name,
                username : new_posts[i].stream_username,
                domain : new_posts[i].stream_domain,
                version : new_posts[i].stream_version
            };
            var post_stream_url = BabblingBrook.Library.makeStreamUrl(stream_name, '');
            var nested_stream_block = [post_stream_url, 'stream_blocks', block_number];
            if (BabblingBrook.Library.doesNestedObjectExist(streams, nested_stream_block) === true) {
                streams[post_stream_url].stream_blocks[block_number].post_ids.push(new_posts[i].post_id);
            }

            jQuery.each(streams, function (stream_url, stream) {
                var nested_tree_block = ['tree_blocks', post_id, block_number];
                if (BabblingBrook.Library.doesNestedObjectExist(stream, nested_tree_block) === true) {
                    streams[stream_url].tree_blocks[post_id][block_number].post_ids.push(new_posts[i].post_id);
                }
            });
        }
    };

    /**
     * We have all the posts request (or all available) Pass them back to the domus domain.
     *
     * @param {number} request The request number used for accessing this requests settings.
     *
     * @param {type} request
     * @returns {undefined}
     */
    var finishRequest = function (request) {
        var post_data = {
            stream_url : settings[request].stream_url,
            posts : settings[request].posts,
            refresh_frequency : settings[request].refresh_frequency,
            type : settings[request].type,
            sort_id : settings[request].sort_id,
            block_numbers : settings[request].block_numbers
        };
        settings[request].successCallback(post_data);
    };

    /**
     * Fetches blocks of posts until enough have been fetched.
     *
     * This is the main routine in the class. It recursively calls itself untill enough blocks have ben fetched.
     *
     * @param {number} block_number The number of the first block to fetch. If this block does not contain enough.
     *                             posts, then the next block down will be iteratively fetch until there are enough.
     *                             0 = latest.
     * @param {number} request The request number used for accessing this requests settings.
     *
     * @returns {void}
     */
    var fetchblocks = function (block_number, request) {
        if (block_number === 'none') {
            finishRequest(request);
            return;
        }
        settings[request].current_block_number = block_number;

        if (copyCachedPosts(request, block_number) === true) {
            var next_block_number = calculateNextBlockNumber(request);
            if (haveEnough(next_block_number, request) === true) {
                finishRequest(request);
            } else {
                fetchblocks(next_block_number, request);
            }
            return;
        }

        var stream_url = BabblingBrook.Library.makeStreamUrl(settings[request].stream, '');
        var url = BabblingBrook.Library.changeUrlAction(stream_url, 'getpostsblock');
        url = BabblingBrook.Library.extractPath(url);

        BabblingBrook.Library.get(
            url,
            {
                block_number : block_number,
                post_id : settings[request].post_id,
                type : settings[request].type,
                with_content : settings[request].with_content
            },
            /**
             * Callback for the getpostsblock data request.
             *
             * @param {string} data Data returned from the server.
             *                      The following paramaters are part of this string until parsed.
             * @param {object} data.posts See BabblingBrook.Scientia.FetchPosts.posts object for definition.
             *
             * @returns {void}
             */
            function (data) {
                for (var i = 0; i < data.posts.length; i++) {
                    if (settings[request].type === 'stream') {
                        data.posts[i].stream_block = block_number;
                    } else if (settings[request].type === 'tree') {
                        data.posts[i].tree_block = block_number;
                    }
                }
                cachePosts(
                    stream_url,
                    data.posts,
                    block_number,
                    settings[request].with_content,
                    settings[request].post_id
                );

                // Add all posts to this request
                // Add all even if it goes above qty requested or past the dates requested.
                // The filter rhythm can remove surplus if it wants to.
                BabblingBrook.Library.createNestedObjects(settings[request], ['posts'], 'array');
                jQuery.each(data.posts, function (i, post) {
                    settings[request].posts.push(post);
                });
                settings[request].block_numbers.push(block_number);
                var next_block_number = calculateNextBlockNumber(request);
                if (haveEnough(next_block_number, request) === true) {
                    finishRequest(request);
                } else {
                    fetchblocks(next_block_number, request);
                }

            },
            settings[request].errorCallback,
            'scientia_server_get_posts',
            settings[request].timeout
        );
    };

    /**
     * Fetch the latest update data for a request.
     *
     * @param {number} request The request number used for accessing this requests settings.
     *
     * @return void
     */
    var fetchUpdate = function (request) {
        var url = BabblingBrook.Library.makeStreamUrl(settings[request].stream, 'getpostslatest');
        url = BabblingBrook.Library.extractPath(url);
        BabblingBrook.Library.get(
            url,
            {
                post_id : settings[request].post_id
            },
            /**
             * Callback for the getpostsblock data request.
             * @param {string} data Data returned from the server.
             *                      The following paramaters are part of this string until parsed.
             * @param {object} posts See BabblingBrook.Scientia.FetchPosts.posts object for definition.
             */
            function (data) {
                var post_data = {
                    stream_url : settings[request].stream_url,
                    posts : data.posts,
                    type : settings[request].type,
                    refresh_frequency : data.refresh_frequency,
                    sort_id : settings[request].sort_id,
                    block_numbers : settings[request].block_numbers,
                };
                settings[request].successCallback(post_data);
            },
            settings[request].errorCallback,
            'scientia_server_get_posts',
            settings[request].timeout
        );
    };

    /**
     * Calculate which block should be fetched first.
     *
     * @param {type} request The current request object.
     *
     * @returns {void}
     */
    var calculateFirstBlock = function (request) {
        var first_block = null;
        if (settings[request].seek_direction === 'past_to_present') {
            if (settings[request].last_block_number === 0) {
                first_block = 0;
            } else {
                first_block = 1;
            }
        } else {
            first_block = 0;
        }
        return first_block;
    };

    /**
     * Check if the header data has expired.
     *
     * @param {number} request The request number used for accessing this requests settings.
     *
     * @returns {boolean}
     */
    var hasHeaderExpired = function (request) {
        var stream_url = BabblingBrook.Library.makeStreamUrl(settings[request].stream, '');
        var cache_timestamp = streams[stream_url].cache_timestamp;
        var refresh_frequency = streams[stream_url].refresh_frequency;
        var now = Math.round(new Date().getTime() / 1000);
        if (cache_timestamp + refresh_frequency > now) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Start the process of fetching blocks of posts from the stream.
     *
     * @param {number} request The request number used for accessing this requests settings.
     *
     * @returns {void}
     */
    var startFetchingBlocks = function (request) {
        var stream_url = BabblingBrook.Library.makeStreamUrl(settings[request].stream, '');
        if (settings[request].update === true) {
            if (typeof settings[request].last_block_number !== 'undefined'
                && settings[request].last_block_number.length > 0
                && settings[request].last_block_number !== streams[stream_url].last_block_number
            ) {
                // not done yet.
            }
            fetchUpdate(request);
        } else {
            settings[request].last_block_number = streams[stream_url].last_block_number;
            var first_block_number = calculateFirstBlock(request);
            fetchblocks(first_block_number, request);
        }
    };

    /**
     * Fetch the header data for this stream.
     *
     * Details how often a stream updates and it's latest full blcok number.
     *
     * @param {number} request The request number used for accessing this requests settings.
     *
     * @returns {void}
     */
    var getStreamHeader = function (request) {
        var url = BabblingBrook.Library.makeStreamUrl(settings[request].stream, 'getblockheader');
        url = BabblingBrook.Library.extractPath(url);
        BabblingBrook.Library.get(
            url,
            {
                post_id : settings[request].post_id
            },
            /**
             * Callback for the request to fetch details about the blocks of posts for this stream.
             *
             * @param {object} data Data returned from the server.
             * @param {number} data.last_block_number The last block to exists for this stream.
             * @param {number} data.refresh_frequency How frequently this streams data updates.
             *
             * @return void
             */
            function (data) {
                var stream_url = BabblingBrook.Library.makeStreamUrl(settings[request].stream, '');
                BabblingBrook.Library.createNestedObjects(streams, [stream_url]);
                streams[stream_url].last_block_number = data.last_block_number;
                streams[stream_url].refresh_frequency = data.refresh_frequency;
                streams[stream_url].stream_blocks = {};
                streams[stream_url].tree_blocks = {};
                if (typeof settings[request].post_id !== 'undefined') {
                    streams[stream_url].tree_blocks[settings[request].post_id] = {};
                }
                settings[request].refresh_frequency = data.refresh_frequency;
                var now = Math.round(new Date().getTime() / 1000);
                streams[stream_url].cache_timestamp = now;
                startFetchingBlocks(request);
            },
            settings[request].errorCallback,
            'scientia_server_get_block_details',
            settings[request].timeout
        );
    }

    /**
     * BabblingBrook.Scientia.FetchPosts constructor. Define public methods in here.
     */
    return {

        /**
         * Fetches a bunch of posts for a stream or tree.
         *
         * Each block file will be cached with http if already fetched, so no need to recache.
         *
         * @param {object} sort_request See BabblingBrook.Models.sortRequest for full details.
         *                              (With scientia and possibly tree extensions).
         * @param {boolean} with_content Should the posts be fetched with full content rows.
         * @param {string} successCallback Called with the success data. See the Controller definition for more details.
         * @param {string} errorCallback Called if there is an error. See Controller definition for more details.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @fixme A lot of objects are being passed around taken apart, put back together and passed around again.
         *             Can be stream lined.
         *
         * @return void
         */
        get : function (sort_request, with_content, successCallback, errorCallback, timeout) {
            sort_request.successCallback = successCallback;
            sort_request.errorCallback = errorCallback;
            sort_request.timeout = timeout;
            sort_request.with_content = with_content;
            sort_request.posts = [];
            if (typeof sort_request.block_numbers === 'undefined') {
                sort_request.block_numbers = [];
            }
            // Posts are only fetched from past to present if there is a past date and no present date.
            // This is to purposfuly bias post selection towards the present unless specifically looking for past data.
            if (sort_request.posts_to_timestamp === null && sort_request.posts_from_timestamp !== null) {
                sort_request.seek_direction = 'past_to_present';
            } else {
                sort_request.seek_direction = 'present_to_past';
            }

            request++;
            request = settings.push(sort_request) - 1;
            // Start the process of fetching posts by fetching the streams block header, so that we
            // know when the last block has been fetched.
            // We may already have fetched this stream, so only refetch it if the refresh timeout has expired.
            var stream_url = BabblingBrook.Library.makeStreamUrl(settings[request].stream, '');
            if (BabblingBrook.Library.doesNestedObjectExist(streams, [stream_url]) === true) {
                if (hasHeaderExpired(request) === false) {
                    startFetchingBlocks(request);
                } else {
                    getStreamHeader(request);
                }
            } else {
                getStreamHeader(request);
            }
        },

        /**
         * Appends a newly created post to an already fetched stream so it is present if the stream reloads.
         *
         * !note this is currently not functional as it appends the post in the https subdomain, whereas they are
         * fetched from the http subdomain.
         *
         * @param {object} post A full post object
         *
         * @returns {void}
         */
        appendNewPostToStream : function (post) {
            var stream_name = {
                name : post.stream_name,
                username : post.stream_username,
                domain : post.stream_domain,
                version : post.stream_version
            };
            var stream_url = BabblingBrook.Library.makeStreamUrl(stream_name, 'json');

            cachePosts(stream_url, [post], 0, true, post.top_parent_id, false);
        }
    };
}());