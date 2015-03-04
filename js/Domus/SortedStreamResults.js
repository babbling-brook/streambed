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
 * @fileOverview Stores stream results that have been sorted.
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
 * @namespace Stores and provides accesss to stream data.
 * @package JS_Shared
 */
BabblingBrook.Domus.SortedStreamResults = (function () {
    'use strict';

    /**
     * Stores the results from stream sort requests.
     *
     * All results are stored in seperate containers depending on the sort type.
     *
     * Inside the containers each sort request is cached using a cache_uid that is generated
     * from the sort request detials.
     *
     * @type {number} timestamp A unix timestamp for when the sorted results where cached here.
     * @type {number} timeout The number of seconds before this sort request is no longer
     *      valid and should be dropped.
     * @type {array} cached An array of post objects index by the sort order.
     * @type {array} [streams] An array of streams that were used to generate the results.
     *      Used to delete a cache when something in the results may have changed.
     *      Only used for streams caches.
     * @type {string} top_parent_post_id The id of the top parent post id for trees caches.
     *      Used to delete a cache when something in the results may have changed.appendResults
     * @type {string} cached[].domain The domain of a post in the results.
     * @type {string} cached[].id The id of a post in the results.
     * @type {number} cached[].score The score that was assigned to the sort request.
     */
    var results = {
        stream : {},
        tree : {},
        local_private : {},         // also contains a [domain] paramater before the rhythm.
        global_private : {},
        local_sent_private : {},    // also contains a [domain] paramater before the rhythm.
        global_sent_private : {},
        sent_all : {},
        local_public : {},          // also contains a [domain] paramater before the rhythm.
        global_pubilc : {},
        local_all : {},             // also contains a [domain] paramater before the rhythm.
        global_all : {}
    };

    /**
     * In order to prevent the storage of unneccessary data, only the relevant data is stored.
     *
     * @param {object} sorted_results See results object.
     *
     * @returns {array} An array of cleaned results.
     */
    var cleanSortResults = function (sorted_results) {
        var clean_results = [];
        for(var i = 0; i < sorted_results.length; i++) {
            var clean_line = {
                domain : sorted_results[i].domain,
                post_id : sorted_results[i].post_id
            };
            if (typeof sorted_results[i].sort !== 'undefined' && sorted_results[i].sort !== null) {
                clean_line.sort = sorted_results[i].sort;
            }
            if (typeof sorted_results[i].top_parent_id !== 'undefined' && sorted_results[i].top_parent_id !== null) {
                clean_line.top_parent_id = sorted_results[i].top_parent_id;
            }
            if (typeof sorted_results[i].parent_id !== 'undefined' && sorted_results[i].parent_id !== null) {
                clean_line.parent_id = sorted_results[i].parent_id;
            }
            clean_results.push(clean_line);
        }
        return clean_results;
    };

    /**
     * Resorts some sort results to ensure that they are in the correct order after an update.
     *
     * @param {object} sort_results See results object.
     *
     * @returns {void}
     */
    var resort = function (sort_results) {
        sort_results.sort(function (a, b) {
            return parseInt(a.score) - parseInt(b.score);
        });
    };

    /**
     * Generates a unique uid for a cached stream request.
     *
     * @param {type} type
     * @param {type} streams
     * @param {type} rhythm
     * @param {type} moderation_rings
     * @param {type} top_parent_post_id
     * @param {type} client_domain
     * @param {type} client_params
     *
     * @returns {string}
     */
    var generateCacheUid = function (streams, rhythm, moderation_rings, top_parent_post_id,
        client_domain, client_params
     ) {

        var uid = '';
        if (typeof streams !== 'undefined') {
            var streams_length = streams.length;
            for(var i=0; i<streams_length; i++) {
                var stream_url = BabblingBrook.Library.makeStreamUrl(streams[i], '', true);
                uid += stream_url + '|';
            }
        }

        if (typeof rhythm !== 'undefined') {
            var rhythm_url = BabblingBrook.Library.makeRhythmUrl(rhythm, '', true);
            uid += rhythm_url + '|';
        }

        var moderation_rings_length = moderation_rings.length;
        for(var j=0; j<moderation_rings_length; j++) {
            var ring_url = moderation_rings[j].domain + '/' + moderation_rings[j].username;
            uid += ring_url + '|';
        }

        uid += top_parent_post_id + '|';

        uid += client_domain + '|';

        if (typeof client_params === 'object') {
            jQuery.each(client_params, function(k, param) {
                uid += k + '/' + param + '|';
            });
        }

        return uid;
    };

    return {

        /**
         * Append some sort results to the results object.
         *
         * If there are no results for this object then create it from scratch.
         *
         * @param {object} type Is this a stream or tree result set.
         * @param {object} stream A standard stream name object.
         * @param {object} rhythm A stardard rhythm name object
         * @param {array} moderation_rings An array of moderation rings used in this sort request.
         * @param {string|undefined} top_parent_post_id The id of the top parent post for tree sorts.
         * @param {string} client_domain The domain of the client that requested the sort_request.
         *      This is only used to store localised sort_requests such as 'sent_private'.
         * @param {object} client_params Any search paramaters that have been passed from the client.
         * @param {object} sorted_results See results object
         * @param {number} timeout The number of seconds to wait before these results timeout.
         *
         * @returns {undefined}
         */
        appendResults : function (type, streams, rhythm, moderation_rings, top_parent_post_id, client_domain,
            client_params, sorted_results, timeout
        ) {
            var cache_uid = generateCacheUid(
                streams,
                rhythm,
                moderation_rings,
                top_parent_post_id,
                client_domain,
                client_params
            );

            var posts;
            if (typeof results[type][cache_uid] === 'undefined') {
                posts = {};
                posts.cached = [];
                if (type === 'stream') {
                    posts.streams = streams;
                }
                if (type === 'tree') {
                    posts.top_parent_post_id = top_parent_post_id;
                    // @fixme Should also be caching the top parent post domain to prevent dupes from different domains.
                }
            } else {
                posts = results[type][cache_uid];
            }

            var now = Math.round(new Date().getTime() / 1000);
            posts.timestamp = now;
            posts.timeout = timeout;

            var clean_results = cleanSortResults(sorted_results);
            jQuery.each(clean_results, function (j, new_post) {
                var found = false;
                jQuery.each(posts.cached, function (i, post) {
                    if (post.domain === new_post.domain && post.post_id === new_post.post_id) {
                        found = true;
                        return false;    // Exit from .each
                    }
                });
                if (found === false) {
                    posts.cached.push(new_post);
                }
            });
        },

        /**
         * Get the cached results for a stream.
         *
         * @param {string} type The type of stream request. See BabblingBrook.Models.sortType for valid values.
         * @param {array} streams An arrya of standard stream name objects used for this request.
         * @param {object} rhythm A stardard rhythm name object
         * @param {array} moderation_rings An array of moderation rings used in this sort request.
         *      Does not matter what it contains as long as it is the same as was passed in to appendResults,
         * @param {string|undefined} top_parent_post_id The id of the top parent post for tree sorts.
         * @param {string} client_domain The domain of the client that made the sort_request.
         *      This is only used to fetch localised sort_requests such as 'sent_private'.
         *
         * @returns {array|false} An array of sorted posts or false.
         */
        getCachedResults : function(type, streams, rhythm, moderation_rings, top_parent_post_id,
            client_domain, client_params
        ) {
            var cache_uid = generateCacheUid(
                type,
                streams,
                rhythm,
                moderation_rings,
                top_parent_post_id,
                client_domain,
                client_params
            );
            var posts = results[type][cache_uid];
            if (typeof posts === 'undefined') {
                return false;
            }

            var now = Math.round(new Date().getTime() / 1000);
            if (now - posts.timestamp > posts.timeout) {
                delete results[type][cache_uid];
                return false;
            }

            resort(posts.cached);
            var cached_posts = {
                posts : jQuery.extend([], posts.cached),
                timestamp : posts.timestamp
            };
            return cached_posts;
        },

        /**
         * Removes cached results that may be effected by this post.
         *
         * Primarily used to prepend posts made by a user.
         * Also removes the cache of user posts.
         *
         * @param {object} post A standard post object.
         *
         * @return {void}
         */
        prependPost : function (stream_name, top_parent_post_id) {
            results.local_sent_private = {};
            results.global_sent_private = {};
            results.sent_all = {};

            jQuery.each(results.stream, function(i, stream) {
                if (BabblingBrook.Library.doStreamsMatch(stream, stream_name) === true) {
                    delete results.stream[i];
                }
            });
            jQuery.each(results.tree, function(j, tree) {
                if (tree.top_parent_post_id === top_parent_post_id) {
                    delete results.tree[j];
                }
            });
        },

        /**
         * Remove a post from all sort results. Primarily used when a post is deleted.
         *
         * @param {string} domain The domain of the post to remove.
         * @param {string} post_id The id of the post to remove.
         *
         * @returns {void}
         */
        removePost : function (domain, post_id) {
            jQuery.each(results, function (i, type) {
                jQuery.each(type, function (j, post) {
                    if (domain === post.domain && post_id === post.post_id) {
                        delete results[i][j];
                    }
                });
            });
        }
    };
})();
