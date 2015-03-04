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
 * @fileOverview Records Feature useage by a user so that it can be used in generating suggestions.
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
 * @namespace Records Feature useage by a user so that it can be used in generating suggestions.
 * Private methods not in prototype because only one object is instantiated.
 * @package JS_Domus
 * @class BabblingBrook.Domus.FeatureUsage
 */
BabblingBrook.Domus.FeatureUsage = (function () {
    'use strict';
    var feature_usage = {};  // A store for recording how popular features are with this user.
    var feature_usage_empty = {'stream' : {}, 'stream_post' : {}, 'filter' : {}, 'kindred' : {}};
    var stored_date = null;    // Date for the data as retrieved from loacalStorage.

    /**
     * Defers requests until the data has been restored from local storage.
     * @type @exp;jQuery@call;Deferred
     */
    var deferred_restored = jQuery.Deferred();

    /**
     * Convert the timestamp from local storage into a day in the form yyyy-mm-dd and drop surplus information.
     * @param {number} timestamp
     */
    var getDate = function (timestamp) {
        var new_date = new Date();
        new_date.setTime(timestamp * 1000);
        var date_string = BabblingBrook.Library.getDate(new_date);
        return date_string;
    };

    /**
     * Private method to check if the date has changed.
     * and if it has stored the feature usage data on the server and reset this object.
     * @param [callback] Callback to run after data is stored OR if the date has not changed. Initialy used to
     *                 allow a check on the date to run before storing new data - the data needs storing regardless.
     */
    var checkDateAndStoreOnServer = function (callback) {
        // To test add this to the date call to simulate tommorow. 86400000 + Math.round(new Date().getTime() / 1000)
        var now = new Date();
        var today = BabblingBrook.Library.getDate(now);

        var empty = true;
        if (jQuery.isEmptyObject(feature_usage.stream) === false) {
            empty = false;
        }
        if (jQuery.isEmptyObject(feature_usage.stream_post) === false) {
            empty = false;
        }
        if (jQuery.isEmptyObject(feature_usage.filter) === false) {
            empty = false;
        }
        if (jQuery.isEmptyObject(feature_usage.kindred) === false) {
            empty = false;
        }
        if (today !== stored_date && stored_date !== null && empty === false) {
            // Store on the server
            BabblingBrook.Library.post(
                '/' + BabblingBrook.Domus.User.username + '/data/storefeatureusage',
                {
                    date : stored_date,
                    feature_usage : feature_usage
                },
                /**
                 * Callback for posting feature usage data to the server.
                 * @param {object} data
                 */
                function (data) {
                    feature_usage = feature_usage_empty;
                    stored_date = today;
                    BabblingBrook.LocalStorage.store('feature-usage', feature_usage);

                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                function () {
                    console.error('Attempt to retrive feature useage data from the domus doamin failed.');
                }
            );

        } else {
            if (typeof callback === 'function') {
                callback();
            }
        }
    };

    /**
     * Constructor.
     * Restore popularity ratings from storage and set up the object.
     */
    return {

        /**
         * Restores previous data from local strorage.
         * Must be called in document.ready as need to be sure that BabblingBrook.LocalStorage has loaded.
         */
        restore : function () {
            // Check if popularity scores are stored locally.
            var stored_feature_usage = BabblingBrook.LocalStorage.fetch('feature-usage', '');
            if (stored_feature_usage === false) {
                feature_usage = feature_usage_empty;
                stored_date = null;
            } else {
                feature_usage = stored_feature_usage.data;
                stored_date = getDate(stored_feature_usage.timestamp);
            }

            // If the users scores are from a different day, then upload them to the datascore and start afresh.
            if (stored_date !== null) {
                checkDateAndStoreOnServer(function () {
                    deferred_restored.resolve();
                });
            } else {
                deferred_restored.resolve();
            }
        },

        /**
         * Public method to increment the number of times a feature has been used.
         * @param {string} feature A valid value from feature_usage_empty.
         * @param {string} url The url of the feature to be incremented.
         * @param {string} client_domain
         * @param {function} [successCallback] Called with the success data. See Module definition for more details.
         * @param {function} [errorCallback] Called if there is an error. See Module definition for more details.
         *
         * @return void
         */
        increment : function (feature, url, client_domain, successCallback, errorCallback) {
            if (typeof feature_usage_empty[feature] === 'undefined') {
                console.error('Feature is invalid.');
            }

            if (typeof successCallback !== 'function') {
                successCallback = function () {};
            }
            if (typeof errorCallback !== 'function') {
                errorCallback = function () {};
            }

            // Ensure url is urldecoded
            url = decodeURI(url);

            deferred_restored.done(function () {
                // The date may have changed since this object was initialised.
                // If so we may need to store the current data on the server before processing this request.
                var callback = function () {
                    // Add the url if it does not exist
                    if (typeof feature_usage[feature][url] === 'undefined') {
                        feature_usage[feature][url] = 1;
                    } else {
                        feature_usage[feature][url]++;
                    }
                    BabblingBrook.LocalStorage.store('feature-usage', feature_usage);
                };
                checkDateAndStoreOnServer(callback);

                successCallback({});
            });
        }

    };
}());