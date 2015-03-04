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
 * @namespace A singleton module that manages the fetching of single posts.
 * @package JS_Scientia
 */
BabblingBrook.Scientia.FetchPost = (function () {
    'use strict';

    /**
     * @string The local domain used in post urls.
     */
    var domain;

    return {

        construct : function () {
            domain = window.location.host;
            if (domain.substr(0, 8) === 'scientia') {
                domain = domain.substr(9);
            }
        },

        /**
         * Get the details of a single post.
         *
         * @param {number} post_id The local post id.
         * @param {string} successCallback Called with the success data. See the Controller definition for more details.
         * @param {string} errorCallback Called if there is an error. See Controller definition for more details.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         * @param {number} [revision] A specific revision number. Defaults to latest.
         *
         * @return void
         */
        get : function (post_id, successCallback, errorCallback, revision, timeout) {
            var stream_block;
            var tree_block;
            if(post_id.indexOf('-') > 0) {
                var post_parts = post_id.split('-');
                post_id = post_parts[0];
                stream_block = [1];
                tree_block = [2];
            }

            var cache_id = post_id;
            if (typeof revision !== 'undefined') {
                cache_id = post_id + '-r' + revision;
            }
            var cached_post = BabblingBrook.Scientia.Cache.getItem('post_header', cache_id);
            var cached_content = BabblingBrook.Scientia.Cache.getItem('post_content', cache_id);
            if (cached_post !== false && cached_content !== false) {
                cached_post.content = cached_content;
                successCallback(cached_post);
                return;
            }

            var get_data = {};
            if (typeof revision !== 'undefined') {
                get_data.revision = revision;
            }
            BabblingBrook.Library.get(
                '/post/' +  domain + '/' + post_id + '/json',
                get_data,
                /**
                 * Callback for fetching post details
                 * @param {object} post_data
                 * @param {object} post_data.post A standard post object. See BabblingBrook.Models.posts.
                 */
                function (post_data) {
                    var post;
                    if (post_data.success === true) {
                        post = post_data.post;
                    }
                    var post_header = jQuery.extend(true, {}, post);
                    post_header.content = undefined;
                    BabblingBrook.Scientia.Cache.cacheItem('post_header', cache_id, 'memory', post_header);
                    BabblingBrook.Scientia.Cache.cacheItem('post_content', cache_id, 'memory', post.content);
                    successCallback(post_data.post);
                },
                errorCallback,
                'GetPost_failed',
                timeout
            );
        }

    };
}());


jQuery(function () {
    'use strict';
    BabblingBrook.Scientia.FetchPost.construct();
});