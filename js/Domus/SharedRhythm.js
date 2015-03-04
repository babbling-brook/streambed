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
 * @fileOverview Functionality that is shared between more than one rhythm.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

// Create the BabblingBrook namespace
if (typeof BabblingBrook.Domus !== 'object') {
    BabblingBrook.Domus = {};
}

/**
 * @namespace Functionality that is shared between more than one rhythm.
 */
BabblingBrook.Domus.SharedRhythm = (function () {
    'use strict';

    return {

        /**
         * Fetch miscellaneous data for a rhythm.
         *
         * @param {string} url The url to fetch data from.
         * @param {function} successCallback Returns the requested data to the rhythm domain.
         * @param {function} errorCallback Returns an error to the rhythm domain.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @return void
         */
        getMiscData : function (url, successCallback, errorCallback, timeout) {
            var scientia_domain = BabblingBrook.Library.extractDomain(url);
            var scientia_data = {
                url : url,
                data : {}
            };
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                scientia_domain,
                'FetchData',
                scientia_data,
                false,
                successCallback,
                errorCallback.bind(null, 'GetData_failed'),
                timeout
            );
        },

        /**
         * Called by the currently running filter to store some data between sessions.
         *
         * @param {string} data The Data to be stored.
         * @param {string} rhythm_url The url of the rhythm that data is being stored for.
         * @param {type} onSuccess Callback for when the data has been stored.
         * @param {type} onError Callback for when an error occurs.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @returns {void}
         */
        storeData : function (data, rhythm_url, onSuccess, onError, timeout) {

            BabblingBrook.Library.post(
                rhythm_url,
                {
                    rhythm_data : data
                },
                /**
                 * Callback for a request to store data for a rhythm/user.
                 *
                 * @param {object} callback_data The returned data.
                 * @param {boolean} callback_data.success Was the request successful.
                 *
                 * @return void
                 */
                function (callback_data) {
                    if (typeof callback_data === 'object' && callback_data.success === true) {
                        onSuccess({});
                    }
                },
                onError,
                'StoreRhythmData_rhythm_post_failed',
                timeout
            );
        },


        /**
         * Called by the currently running filter to fetch some data that was stored in a previous session.
         *
         * @param {string} rhythm_url The url of the rhythm that data is being stored for.
         * @param {type} onSuccess Callback for when the data has been stored.
         * @param {type} onError Callback for when an error occurs.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @returns {void}
         */
        getData : function (rhythm_url, onSuccess, onError, timeout) {
            BabblingBrook.Library.get(
                rhythm_url,
                {},
                /**
                 * Callback for a request to store data for a rhythm/user.
                 *
                 * @param {object} callback_data The returned data.
                 * @param {boolean} callback_data.success Was the request successful.
                 *
                 * @return void
                 */
                function (callback_data) {
                    if (typeof callback_data === 'object'
                        && typeof callback_data.data !== 'undefined'
                        && callback_data.data !== false
                    ) {
                        onSuccess(callback_data);
                    } else {
                        onError('getData_rhythm_get_empty');
                    }
                },
                onError,
                'getData_rhythm_get_failed',
                timeout
            );
        }
    }

}());