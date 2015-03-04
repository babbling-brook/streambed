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
 * @fileOverview Receives messages from the sugestions domain.
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
 * @namespace Receives messages from the sugestions domain.
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
BabblingBrook.Domus.SuggestionController = (function () {
    'use strict';
    return {

        /**
         * Receives a message from the suggestion iframe that it is ready.
         *
         * Allows this domus domain to report itself as ready.
         *
         * @param {object} data
         * @param {string} data.status Will contain 'ready' when it is ready.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionDomainReady : function (data, meta_data) {

            meta_data.onSuccess({});    // Call regardless to free up the memory.

            // When user and kindred data has loaded send through the kindred data.
            BabblingBrook.Domus.Loaded.onKindredDataLoaded(function () {
                BabblingBrook.Domus.Interact.postAMessage(
                    {
                        'kindred' : BabblingBrook.Domus.kindred_data
                    },
                    'suggestion',
                    'ReceiveKindredData',
                    /**
                     * The suggestion iframe is now ready to process suggestions.
                     *
                     * @return void
                     */
                    function () {
                        BabblingBrook.Domus.Loaded.setSuggestionIframeLoaded();
                    },
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
                        console.error('The suggestion domain is reporting an error with the kindred data.');
                    }
                );
            });
        },

        /**
         * Fetch any data that is requested by the rhythm and then pass it to the suggestion domain for processing.
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
         * Stores data for a rhythm/user between sessions in the users domus.
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

            var url = BabblingBrook.Domus.Suggestion.getCurrentRhythmUrl();
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
            var url = BabblingBrook.Domus.Suggestion.getCurrentRhythmUrl();
            BabblingBrook.Domus.Kindred.getData(url, meta_data.onSuccess, meta_data.onError);
        },

        /**
         * Recieves an error from the suggestion domain ready to be passed to the client domain for reporting.
         *
         * @returns {undefined}
         */
        actionError : function (request_data, meta_data) {
            BabblingBrook.Domus.Interact.postAMessage(
                {
                    domain : 'suggestion',
                    error : request_data.error
                },
                BabblingBrook.Domus.Controller.client_domain,
                'Error',
                function () {},
                function () {
                    console.error(
                        'actionError in the domus domain SuggestionController is ' +
                        'erroring whilst waiting for the client to repsond.'
                    );
                },
                BabblingBrook.Domus.Controller.client_https
            );
        }

    };
}());