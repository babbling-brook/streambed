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
 * @fileOverview Singleton relating to the kindred process.
 * @author Sky Wickenden
 * @suggetion a memoizing pattern can be used in many places in this code.
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Domus !== 'object') {
    BabblingBrook.Domus = {};
}


/**
 * Module relating to the kindred process.
 */
BabblingBrook.Domus.Kindred = (function () {
    'use strict';

    /**
     * @type {boolean} Has the iframe been inserted into the page yet?
     */
    var iframe_inserted = false;

    /**
     * @type {boolean} Fetch all value data, rather than just the main one.
     * This is not currently used.
     */
    var all_values = true;

    var takes_waiting_on_server = false;

    var kindred_tags;

    /**
     * Save the kindred data in memory.
     *
     * @param {object} kindred_data Kindred data returned from the server.
     *      Index by the full username of the relationship. EG 'domain/username' = score.
     * @param {number} The value of the relationship.
     *
     * @return void
     */
    var getKindredDataCallback = function (kindred_data) {
        var json_kindred_data = {};
        jQuery.each(kindred_data, function (i, row) {
            json_kindred_data[row.domain + '/' + row.username] = row.score;
        });
        BabblingBrook.Domus.kindred_data = json_kindred_data;
        BabblingBrook.Domus.Loaded.setKindredDataLoaded();
    };

    /**
     * Fetches the kindred data from local storage or the domus domain.
     *
     * @return void
     */
    var getKindredData = function () {
        BabblingBrook.Library.post(
            '/' + BabblingBrook.Domus.User.username + '/getkindred',
            {},
            getKindredDataCallback,
            function () {
                console.error('There was a problem loading the kindred data from the server.');
            }
         );
    };

    /**
     * Callback for when the kindred data is ready to send to the client.
     *
     * @param {function} successCallback Called with the kindred data.
     *
     * @return void
     */
    var readyToSend = function (successCallback) {
        var kindred_data = {
            kindred : BabblingBrook.Domus.kindred_data
        };
        successCallback(kindred_data);
    };

    /**
     * Takes are fetched from the server at regular intervals and processed.
     *
     * @returns {undefined}
     */
    var waitToSendMoreTakes = function () {
        var wait;
        if (takes_waiting_on_server === true) {
            wait = BabblingBrook.Domus.User.short_wait_before_processing_takes;
        } else {
            wait = BabblingBrook.Domus.User.long_wait_before_processing_takes;
        }
        setTimeout(
            function () {
                BabblingBrook.Domus.Kindred.loadRhythm();
            },
            wait
        );
    };

    /**
     * Insert the filter rhythm iframe into the DOM.
     *
     * @returns {undefined}
     */
    var insertIframe = function () {
        console.log('creating kindred rhythm iframe.');
        if (iframe_inserted === false) {
            var main_domain = window.location.host.substring(6);
            jQuery('body').append(' ' +
                '<iframe style="display:none" id="kindred" name="kindred_window" ' +
                        'src="http://kindred.' + main_domain + '">' +
                '</iframe>'
            );
            iframe_inserted = true;        }
    };

    return {

        waitToSendMoreTakes : waitToSendMoreTakes,

        /**
         * Fetches the most recent bunch of unprocessed takes and sends them to the kindred rhythm.
         */
        loadRhythm : function () {
            // Only using the first rhythm until the domus doamin can reload the kindred domain between rhythms.
            var rhythm = BabblingBrook.Domus.User.kindred_rhythm[0];
            var user_rhythm_id = rhythm.user_rhythm_id;
            // Fetch the take data for processing.
            var url = '/' + BabblingBrook.Domus.User.username + '/data/gettakes';
            BabblingBrook.Library.get(
                url,
                {
                    form : {
                        all_values : all_values,
                        user_rhythm_id : user_rhythm_id
                    }
                },
                /**
                 * receives takes that have been requested from the server.
                 * @param {object} data See BabblingBrook.Models.takes for details.
                 */
                function (data) {
                    // No takes to process, Try again later
                    if (jQuery.isEmptyObject(data) === true) {
                        takes_waiting_on_server = false;
                        waitToSendMoreTakes();
                        return;

                    // If the total received is equal to the maximum then more are waiting.
                    } else if (BabblingBrook.Domus.User.max_takes_from_server === data.length) {
                        takes_waiting_on_server = true;
                    } else {
                        takes_waiting_on_server = false;
                    }

                    var user = {
                        username : BabblingBrook.Domus.User.username,
                        domain : BabblingBrook.Domus.User.domain
                    };

                    insertIframe();
                    BabblingBrook.Domus.Loaded.onKindredIframeLoaded(function () {
                        BabblingBrook.Domus.Interact.postAMessage(
                            {
                                rhythm : rhythm,
                                takes : data,
                                user : user,
                                client_domain : 'kindred rhythm needs requesting form the client to pass it.'
                            },
                            'kindred',
                            'RunRhythm',
                            /**
                             * An empty success function. Nothing needs be done.
                             *
                             * @return void
                             */
                            function () { },
                            /**
                                * Throw an error if one is reported.
                                *
                                * @param {string} error_code See BabblingBrook.Models.errorTypes for valid codes.
                                * @param {object} error_data A standard error_data object.
                                *
                                * @return void
                                */
                            function (error_code, error_data) {
                                console.log(error_data);
                                console.error(
                                    'kindred domain is reporting an error with the take data. code : ' + error_code
                                );
                                throw 'Thread execution stopped.';
                            }
                        );
                    });
                },
                'domus_get_takes_for_kindred'
            );
        },

        /**
         * Saves the results of the currently running kindred rhythm in the users domus domain.
         *
         * @param {object} scores Score data returned from the kindred domain. see KindredController for details.
         * @param {function} successCallback Called with the success data. See Module definition for more details.
         * @param {function} errorCallback Called if there is an error. See Module definition for more details.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @return void
         */
        saveResults : function (scores, successCallback, errorCallback, timeout) {
            var url = '/' + BabblingBrook.Domus.User.username + '/storekindred';
            BabblingBrook.Library.post(
                url,
                {
                    scores : scores,
                    rhythm_id : BabblingBrook.Domus.User.kindred_rhythm[0].user_rhythm_id
                },
                /**
                 * Return data from submitting a request to store kindred results.
                 * @param {object} data
                 */
                function (data) {
                    successCallback({});
                    waitToSendMoreTakes();
                },
                errorCallback.bind(null, 'StoreKindredResults_failed'),
                timeout
            );
        },

        /**
         * Send the kindred data to the client domain that is requesting it.
         *
         * @param {function} successCallback Called with the kindred data.
         * @param {function} errorCallback Called if there is an error.
         *
         * @return void
         */
        sendToClient : function (successCallback, errorCallback) {
            BabblingBrook.Domus.Loaded.onKindredDataLoaded(readyToSend.bind(null, successCallback));
        },


        /**
         * Send the kindred tag data to the client domain that is requesting it.
         *
         * @param {function} successCallback Called with the kindred data.
         * @param {function} errorCallback Called if there is an error.
         *
         * @return void
         */
        sendTagsToClient : function (successCallback, errorCallback) {
            if (typeof kindred_tags !== 'undefined') {
                successCallback(kindred_tags);
            } else {
                BabblingBrook.Library.post(
                    '/' + BabblingBrook.Domus.User.username + '/getkindredtags',
                    {},
                    function (respopnse_data) {
                        kindred_tags = respopnse_data;
                        successCallback(kindred_tags);
                    },
                    errorCallback.bind(null, 'StoreKindredTagResults_failed')
                );
            }
        },

        /**
         * Fetches the url of rhythm of the currently running kindred rhythm.
         *
         * @returns {string}
         */
        getCurrentRhythmUrl : function () {
            var rhythm = BabblingBrook.Domus.User.kindred_rhythm;
            var url = BabblingBrook.Library.makeRhythmUrl(rhythm, 'storedata');
            return url;
        },

        /**
         * Kindred constructor. Called when document ready.
         *
         * @return void
         */
        construct : function () {
            getKindredData();
            waitToSendMoreTakes();
        }
    };
}());
