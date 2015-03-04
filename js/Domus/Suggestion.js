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
 * @fileOverview Functions related to suggestions and interaction with the suggestion domain.
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
 * @namespace Functions related to suggestions and interaction with the suggestion domain.
 * @package JS_Domus
 */
BabblingBrook.Domus.Suggestion = (function () {
    'use strict';

    /**
     * @type {boolean} Has the iframe been inserted into the page yet?
     */
    var iframe_inserted = false;

    /**
     * @type {string} Url of the current suggestions Rhythm.
     */
    var current_suggestion_rhythm;

    /**
     * Receives suggestions from the suggestion domain and forwards them to the client domain.
     *
     * @param object request The request passed from the client domain. See fetchSuggestions for details.
     * @param {function} successCallback Called to send suggestion data back to the client.
     * @param {function} errorCallback Called to send an error back to the client.
     * @param {object} suggestion_data
     * @param {object} suggestion_data.type
     * @param {string} suggestion_data.suggestions The suggestion data.
     *                                  Structure is different depending on the suggestion type.
     *                                  See protocol for details.
     *
     * @return void
     */
    var receiveSuggestions = function (request, successCallback, errorCallback, suggestion_data) {
        if (BabblingBrook.Models.suggestionResults(suggestion_data.suggestions, request.type) === false) {
            errorCallback('domus_test_results_suggestion_rhythm_invalid');
        } else {
             successCallback(suggestion_data);
        }
    };

    /**
     * The Rhythm needs to be fetched before sending to the suggestions domain, so do this in a callback.
     *
     * @param {object} request The request passed from the client domain. See fetchSuggestions for details.
     * @param {string} client_domain The domain of the client website that sent this request.
     * @param {function} successCallback Called when the suggestion data has been generated.
     * @param {function} errorCallback Called if there is an error in the process.
     * @param {number} timeout Timestamp for when the client request will timeout.
     * @param {object} rhythm_data Data bout the suggestion rhythm that is going to be run.
     *
     * @returns {void}
     */
    var onRhythmFetched = function (request, client_domain, successCallback, errorCallback, timeout, rhythm_data) {
        if (BabblingBrook.Models.rhythm(rhythm_data.rhythm) === false) {
            errorCallback('domus_suggestion_rhythm_invalid');
            return;
        }

        var user = {
            username : BabblingBrook.Domus.User.username,
            domain : BabblingBrook.Domus.User.domain
        };

        BabblingBrook.Domus.Loaded.onSuggestionIframeLoaded(function () {
            BabblingBrook.Domus.Interact.postAMessage(
                {
                    type : request.type,
                    paramaters : request.paramaters,
                    user : user,
                    client_domain : client_domain,
                    rhythm : rhythm_data.rhythm
                },
                'suggestion',
                'RunRhythm',
                receiveSuggestions.bind(null, request, successCallback, errorCallback),
                errorCallback.bind(null, 'GetSuggestions_rhythm')
            );
        });
    };

    /**
     * Insert the filter rhythm iframe into the DOM.
     *
     * @returns {undefined}
     */
    var insertIframe = function () {
        console.log('creating suggestion rhythm iframe.');
        if (iframe_inserted === false) {
            var main_domain = window.location.host.substring(6);
            jQuery('body').append(' ' +
                '<iframe style="display:none" id="suggestion" name="suggestion_window" ' +
                        'src="http://suggestion.' + main_domain + '">' +
                '</iframe>'
            );
            iframe_inserted = true;
        }
    };

    return {

        /**
         * Handles a request from the client to Fetch suggestions.
         *
         * @param {object} request Contains details about the suggestion request.
         * @param {string} request.type The type of suggestion rhythm that is going to run.
         * @param {object} request.parameters The paramaters to pass to the rhythm.
         * @param {string} request.rhythm_url The url of the rhythm to run.
         * @param {String} client_domain The domain of the client that the request came from.
         * @param {function} successCallback Called to send suggestion data back to the client.
         * @param {function} errorCallback Called to send an error back to the client.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @return void
         */
        fetchSuggestions : function (request, client_domain, successCallback, errorCallback, timeout) {
            var rhythm_domain = BabblingBrook.Library.extractDomain(request.rhythm_url);
            var scientia_data = {url : request.rhythm_url};
            insertIframe();
            BabblingBrook.Domus.Loaded.onSuggestionIframeLoaded(function () {
                BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                    rhythm_domain,
                    'FetchRhythm',
                    scientia_data,
                    false,
                    onRhythmFetched.bind(null, request, client_domain, successCallback, errorCallback, timeout),
                    errorCallback.bind('GetSuggestions_rhythm_not_found'),
                    timeout
                );
            });
        },

        /**
         * Fetches the url of rhythm of the currently running ring.
         *
         * @returns {string}
         */
        getCurentRhythmUrl : function () {
            var url = BabblingBrook.Library.makeRhythmUrl(current_suggestion_rhythm, 'storedata');
            return url;
        },

        /**
         * Suggestion constructor. Called when document ready.
         *
         * @return void
         */
        construct : function () {

        }

    };

}());