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
 * @fileOverview Processes requests from the kindred domain.
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
 * @namespace Processes requests from the kindred domain.
 *
 * All methods starting with 'action' are actions that are called by the interact class
 * They all have the same call signature.
 *      {object} data Some data sent with the request.
 *      {object} meta_data Meta data about the request
 *      {function} meta_data.onSuccess The function to call with the requested data.
 *          It accepts one paramater, a data object.
 *      {function} meta_data.onError The function to call if there is an error.
 *          It accepts two paramaters.
 *          The first is an error_code string as defined in saltNe.Models.errorTypes
 *          This is required.
 *          The second is an error data object, which can contain any relevant data.
 *      {string} request_domain The domain that sent this request.
 *      {number} timeout A millisecond timeout for when this request will timeout.
 *
 * @package JS_Domus
 */
BabblingBrook.Domus.KindredController = (function () {
    'use strict';

    return {

        /**
         * Receives a message from the Kindred iframe that it is ready.
         *
         * @param {object} data
         * @param {string} data.status Will contain 'ready' when it is ready.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionDomainReady : function (data, meta_data) {
            meta_data.onSuccess({});    // Call regardless to free up the memory.

            BabblingBrook.Domus.Loaded.onKindredDataLoaded(function () {
                BabblingBrook.Domus.Interact.postAMessage(
                    {
                        'kindred' : BabblingBrook.Domus.kindred_data
                    },
                    'kindred',
                    'ReceiveKindredData',
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
                        console.error(error_code, error_data);
                        console.error('The kindred domain is reporting an error with the kindred data.');
                    }
                );
                BabblingBrook.Domus.Loaded.setKindredIframeLoaded();
            });
        },

        /**
         * Store the results from processing the Kindred rhythms.
         *
         * @param {object} data
         * @param {object} data.scores
         * @param {string} data.scores[full_username] The full username of the user the score is for.
         * @param {number} data.scores[full_username].take_id The server side id of the take
         *      that the score was made for.
         * @param {number} data.scores[full_username].score The score to record.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionSaveKindredResults : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test1 = BabblingBrook.Test.isA([[data.scores, 'object']]);
            if (test1 === false) {
                meta_data.onError('SaveKindredResults_main_object_test');
            }

            var test2 = true;
            jQuery.each(data.scores, function (server_take_id, score_data) {
                // Emtpy scores are valid - the take_id still needs marking as processed.
                if (jQuery.isEmptyObject(score_data) === true) {
                    return true;    // Continue with .each.
                }
                var test2 = BabblingBrook.Test.isA([
                    [server_take_id, 'uint'],
                    [score_data.full_username, 'full-username'],
                    [score_data.score, 'int']
                ]);
                if (test2 === false) {
                    meta_data.onError('SaveKindredResults_data_test');
                    return false;   // exit .each
                }
            });
            if (test2 === false) {
                return;
            }

            BabblingBrook.Domus.Kindred.saveResults(
                data.scores, meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Fetch any data that is requested by the rhythm and then pass it to the kindred domain for processing.
         *
         * @param {object} data
         * @param {object} data.url The url to fetch data from. Must be accessible via a scientia domain.
         * @param {string} domain The domain that the message has been sent from.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetMiscData : function (data, meta_data) {

            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.url, 'url']
            ]);
            if (test === false) {
                meta_data.onError('GetData_test');
            }

            BabblingBrook.Domus.SharedRhythm.getMiscData(
                data.url,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Stores data for a rhythm/user between sessions in the users domus domain.
         *
         * @param {object} data Data container object.
         * @param {string} data.data The data that a rhythm is requesting be stored.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {void}
         */
        actionStoreData : function (data, meta_data) {

            var test = BabblingBrook.Test.isA([[data.data, 'string']]);
            if (test === false) {
                meta_data.onError('rhythm_domus_data_data_not_string');
                return;
            }

            var url = BabblingBrook.Domus.Kindred.getCurrentRhythmUrl();
            BabblingBrook.Domus.SharedRhythm.storeData(
                data.data,
                url,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Stores data for a rhythm/user between sessions.
         *
         * @param {object} data Empty data object
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {void}
         */
        actionGetStoredData : function (data, meta_data) {
            var url = BabblingBrook.Domus.Kindred.getCurrentRhythmUrl();
            BabblingBrook.Domus.Kindred.getData(url, meta_data.onSuccess, meta_data.onError);
        },

        /**
         * Recieves an error from the kindred domain ready to be passed to the client domain for reporting.
         *
         * @returns {undefined}
         */
        actionError : function (request_data, meta_data) {
            BabblingBrook.Domus.Interact.postAMessage(
                {
                    domain : 'kindred',
                    error : request_data.error
                },
                BabblingBrook.Domus.Controller.client_domain,
                'Error',
                function () {},
                function () {
                    console.error(
                        'actionError in the domus domain KindredController is ' +
                        'erroring whilst waiting for the client to repsond.'
                    );
                },
                BabblingBrook.Domus.Controller.client_https
            );
        }

    };
}());
