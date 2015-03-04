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
 * @namespace A singleton module that manages the caching of data requested via the scientia domain.
 * @package JS_Scientia
 */
BabblingBrook.Scientia.Cache = (function () {
    'use strict';

    /**
     * @type {array} An associative array of standard post header objects, indexed by post_id.
     */
    var posts_header = {};

    /**
     * @type {array} An associative array of standard post content objects, indexed by post_id.
     */
    var posts_content = {};

    /**
     * @type {array} An associative array of standard stream objects, indexed by stream url.
     */
    var streams = {};

    /**
     * @type {array} An associative array of standard rhythm objects, indexed by rhythm url.
     */
    var rhythms = {};

    /**
     * Selects the correct cache object for the given object type.
     *
     * @param {string} object_type Valid types are 'post', 'stream', 'rhythm'.
     *
     * @returns {object} A reference to one of the cache objects
     */
    var selectCacheObject = function (object_type) {
        var cache;
        switch (object_type) {
            case 'post_header':
                cache = posts_header;
                break;

            case 'post_content':
                cache = posts_content;
                break;

            case 'stream':
                cache = streams;
                break;

            case 'rhythm':
                cache = rhythms;
                break;

            default:
                console.error('BabblingBrook.Scientia.Cache object_type invalid: ' + object_type);
        }
        return cache;
    };

    return {

        /**
         * Caches an object.
         *
         * @param {string} object_type. Valid types are 'post_header', 'post_content', 'stream', 'rhythm'.
         *      post_header should only contains post header data, without the content array.
         *      post_content should only contain the post content without the content array.
         * @param {type} id A unique id that represents this thing in the object_type.
         *      posts : the post id.
         *      posts_content : the post id.
         *      stream : the stream url.
         *      rhythm : the rhythm url.
         * @param {string} cache_type Cache in memory of local storage. Valid values are 'memory' and 'localstorage'.
         * @param {object} thing The object to cache.
         *
         * @returns {void}
         */
        cacheItem : function (object_type, id, cache_type, thing) {
            if (typeof id !== 'string' || id.length === 0) {
                console.error('BabblingBrook.Scientia.Cache id invalid: ' + id);
            }
            var thing_clone = jQuery.extend(true, {}, thing);
            if (cache_type === 'memory') {
                var cache = selectCacheObject(object_type);
                cache[id] = thing_clone;
            } else if (cache_type === 'localstorage') {
                BabblingBrook.LocalStorage.store(object_type, thing_clone, id);
            } else {
                console.error('BabblingBrook.Scientia.Cache cache_type invalid: ' + cache_type);
            }
        },

        /**
         * Fetches a cached item.
         *
         * @param {string} object_type. Valid types are 'post_header', 'post_content', 'stream', 'rhythm'.
         * @param {type} id A unique id that represents this thing in the object_type.
         *      post_header : the post id.
         *      posts_content : the post id.
         *      stream : the stream url.
         *      rhythm : the rhythm url.
         * @returns {undefined}
         */
        getItem : function (object_type, id) {
            var thing;
            var cache = selectCacheObject(object_type);
            if (typeof cache[id] !== 'undefined') {
                thing = cache[id];
            } else {
                thing = BabblingBrook.LocalStorage.fetch(object_type, id);
            }
            if (typeof thing === 'undefined' || thing === false) {
                return false;
            } else {
                return jQuery.extend(true, {}, thing);
            }
        },

        /**
         * Removes an object from the cache.
         *
         * @param {string} object_type. Valid types are 'post', 'post_header', 'post_content', 'stream', 'rhythm'.
         *      post_header Removes post headers.
         *      post_content Removes full posts.
         *      post Removes both post headers and full posts.
         * @param {type} id A unique id that represents this thing in the object_type.
         *      posts : the post id.
         *      posts_content : the post id.
         *      stream : the stream url.
         *      rhythm : the rhythm url.
         * @param {string} cache_type Cache in memory of local storage.
         *      Valid values are 'memory' and 'localstorage' and 'all'.
         *
         * @returns {void}
         */
        removeItem : function (object_type, id, cache_type) {
            if (typeof id !== 'string' || id.length === 0) {
                console.error('BabblingBrook.Scientia.Cache id invalid: ' + id);
            }
            if (cache_type === 'memory' || cache_type === 'all') {
                if (object_type === 'post') {
                    posts_header[id] = undefined;
                    posts_content[id] = undefined;
                } else {
                    var cache = selectCacheObject(object_type);
                    cache[id] = undefined;
                }
            } else if (cache_type === 'localstorage' || cache_type === 'all') {
                BabblingBrook.LocalStorage.remove(object_type, id);
            } else {
                console.error('BabblingBrook.Scientia.Cache cache_type invalid: ' + cache_type);
            }
        },

    };
}());