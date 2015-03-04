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
 * @fileOverview A single object for processing local storage processes to avoid namespace clashes.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

/**
 * @namespace A single object for processing local storage processes to avoid namespace clashes.
 * @package JS_Store
 * @class BabblingBrook.LocalStorage
 */
BabblingBrook.LocalStorage = (function () {
    'use strict';

    /**
     * Clears some space in local storage.
     * Removes approx 1/4 of the oldest data.
     *
     * @returns {undefined}
     */
    var clearSomeSpace = function () {
        var local_data = [];
        var total_size = 0;
        var localstorage_length = localStorage.length;
        for (var i=0; i < localstorage_length; i++) {
            var raw_item = localStorage.getItem(localStorage.key(i));
            var item = JSON.parse(raw_item);
            total_size += raw_item.length;
            local_data.push({
                time : item['t'],
                key : localStorage.key(i),
                size : raw_item.length
            });
        }
        local_data.sort(function (a, b) {
            return a.time - b.time;
        });
        var delete_size = Math.floor(total_size / 4);
        var deleted_so_far = 0;
        while (deleted_so_far < delete_size) {
            var deleting_item = local_data.shift();
            localStorage.removeItem(deleting_item.key);
            deleted_so_far += deleting_item.size;
        }
    };

    /**
     * Constructor
     */
    return {

        /**
         * Stores an item in local storage.
         *
         * Items are stored in categories, Which can either contain a flat string, or can be subdivided into
         * objects, each with an id which must be unique to the category.
         *
         * @param {string} cat The category name of the item that is being stored.
         *                     Must not contain the underscore character.
         *                     Categories being used are:
         *                         feature-usage
         * @param {object|string} data An object containing data to store.
         * @param {string} id A unique string within the category that identifies this item of data.
         */
        store : function (cat, data, id) {
            if (typeof data !== 'object' && typeof data !== 'string') {
                console.error('Can only store objects or strings in localstorage.');
            }

            var container;
            var timestamp = Math.round(new Date().getTime() / 1000);    // Unix timestamp in seconds.
            if (typeof id !== 'undefined') {
                container = BabblingBrook.LocalStorage.fetch(cat);
                if (container === false) {
                    container = {  // Use short form d and t to save on space. Converted to data and timestamp on retrieval.
                        d : {},
                        t : timestamp
                    }
                } else {
                    // Convert the container names back to their short forms.
                    container.d = container.data;
                    container.t = container.timestamp;
                    delete container.data;
                    delete container.timestamp;
                }

                container.d[id] = data;
                container.t = timestamp;
            } else {
                container = {  // Use short form d and t to save on space. Converted to data and timestamp on retrieval.
                    d : data,
                    t : timestamp
                }
            }
            var string_data = window.JSON.stringify(container);
            try {
                localStorage.setItem(cat, string_data);
            } catch (exception) {
                clearSomeSpace();
                try {
                    localStorage.setItem(cat, string_data);
                } catch (exception){
                    BabblingBrook.Client.Component.Messages.addMessage({
                        type : 'error',
                        message : 'Error when attemting to store data in localStorage. Even after clearing some space',
                        full : ' cat : ' + cat + ' string_data : ' + JSON.stringify(string_data)
                    });
                }
            }

        },

        /**
         * Gets an item from local storage.
         *
         * @param {string} cat The category name of the item that is being fetched.
         * @param {string} [id] A unique string within the category that identifies this item of data.
         * @return {object|false} A container object, containing a sub object called `data` - containing the actual data
         *                        and another called `timestamp`, which marks when the data was stored.
         *                        OR false if nothing found.
         *
         * @returns {object|false}
         */
        fetch : function (cat, id) {
            var string_data = localStorage.getItem(cat);
            if (string_data === null) {
                return false;
            }

            var data;
            try {
                data = BabblingBrook.Library.parseJSON(string_data);
            } catch (e) {
                return false;
            }
            var requested_data;
            if (typeof id === 'undefined') {
                requested_data = data.d;
            } else {
                if (typeof data.d[id] !== 'undefined') {
                    requested_data = data.d[id];
                } else {
                    return false;
                }
            }

            // Convert container names from short form to longform.
            var return_data = {
                data : requested_data,
                timestamp : data.t
            };
            return return_data;
        },

        /**
         * Remove an item from local storage.
         *
         * @param {string} cat The category name of the itme that is being stored.
         * @param {string} [id] A unique string within the category that identifies this item of data.
         *
         * @return {void}
         */
        remove : function (cat, id) {
            if(typeof id === 'undefined') {
                localStorage.removeItem(cat);

            // sub items retrieve the cat and delete the item and then resave the whole category.
            } else {
                var stored_data = BabblingBrook.LocalStorage.fetch(cat);
                stored_data = stored_data.data;
                if (typeof stored_data[id] !== 'undefined') {
                    delete stored_data[id];
                    BabblingBrook.LocalStorage.store(cat, stored_data);
                }
            }
        },

        /**
         * Runs when the file loads.
         *
         * @returns {void}
         */
        construct : function () {
            var flush = BabblingBrook.LocalStorage.fetch('flush_localstorage_version');
            var flush_localstorage_version = flush.data;
            if (BabblingBrook.Settings.flush_localstorage_version.toString() !== flush_localstorage_version) {
                localStorage.clear();
                console.log('cleared localstorage');
                BabblingBrook.LocalStorage.store(
                    'flush_localstorage_version',
                    BabblingBrook.Settings.flush_localstorage_version.toString()
                );
            }
        }

    };
}());


jQuery(function () {
    'use strict';
    BabblingBrook.LocalStorage.construct();
});