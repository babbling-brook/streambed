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
 * @fileOverview Processes suggestion Rhythms in the kindred domain.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

/*
 * Document ready function.
 */
jQuery(function () {
    'use strict';
    BabblingBrook.Shared.Interact.setup(BabblingBrook.Suggestion, 'suggestion');
    BabblingBrook.Shared.Interact.postAMessage(
        {},
        'DomainReady',
        /**
         * An empty callback for when the domus acknowledges the ready statement.
         *
         * @return void
         */
        function () {},
        /**
         * Throw an error if one is reported.
         */
        function () {
            console.error('The domus domain is not acknowledging the SuggestionReady message.');
            throw 'Thread execution stopped.';
        }
    );

    window.onerror = function(message, url, line) {
        BabblingBrook.Shared.Interact.postAMessage(
            {
                error : 'Uncaught error : ' + message + ' : url : ' + url + ' line : ' + line
            },
            'Error',
            function () {},
            function () {
                console.error(
                    'window.onerror in the suggestion ready function is erroring whilst wating for client to respond.'
                );
            }
        );
    };
});

/**
 * @namespace Global object containing user information. See domus/user.js for details.
 * @package JS_Suggestion
 */
//BabblingBrookUser = {};

/**
 * @namespace Global object holding methods related to the rhythm domain.
 * Runs in the domus' Rhythm iframe to run Rhythms in an issolated sandbox.
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
 * Important : This should not alow access to any sensitive data.
 * @package JS_Suggestion
 */
BabblingBrook.Suggestion = (function () {
    'use strict';

    /**
     * @type {function} The success function to call when the ring has finished processing.
     */
    var onSuccess;

    /**
     * @type {function} The error function to call when the ring has finished processing.
     */
    var onError;

    /**
     * The name of the client website that is requesting this rhythm to run.
     *
     * @type {string}
     */
    var client_domain;

    /**
     * The user who is running this rhyhtm.
     *
     * @type {object} user
     * @type {string} user.username
     * @type {string} user.domain
     */
    var user;

    /**
     * @type {object[]|null} kindred The current users kindred data.
     * @type {string} kindred[].full_username The full username of the kindred user.
     * @type {number} kindred[].score The value of this relationship.
     */
    var kindred = null;

    /**
     * @type String The type of suggestion Rhythm that is being run.
     */
    var suggestion_type = null;

    /**
     * @type object Paramaters that have been sent from the client to narrow down the suggestion search.
     */
    var paramaters = {};

    /**
     * Clears the current suggestion data.
     */
    var reset = function () {
        suggestion_type = null;
        paramaters = {};
    };

    /*
     * @type {object} rhythm The kindred rhythm object sent from the domus domain.
     * @type {string} rhythm.js The javascript of the rhythm that is being executed.
     *      Once this has been evalled it should return two encapsulated public functions,
     *      init() and main(). See protocol documentation for full definitions.
     */
    var rhythm_data;

    var deferred_kindred = jQuery.Deferred();

    return {

        /**
         * Receives the kindred data via the domus domain in case it is needed by rhythms for processing.
         *
         * @param {object} data
         * @param {object} meta_data See Module definition for more details.
         *
         * @reutrn void
         */
        actionReceiveKindredData : function (data, meta_data) {
            var test = BabblingBrook.Test.isA([data.kindred, 'object'], 'kindred data is not valid. ');
            if (test === false) {
                meta_data.onError('suggestion_test_kindred_data');
                return;
            }

            jQuery.each(data.kindred, function (full_username, score) {
                test = BabblingBrook.Test.isA(
                    [
                        [full_username, 'full-username'],
                        [score, 'int']
                    ],
                    'Kindred row data is not valid.'
                );
                if (test === false) {
                    return false;   // Exit the .each
                }
                return true;        // Continue the .each
            });
            if (test === false) {
                meta_data.onError('suggestion_test_kindred_data');
                return;
            }

            kindred = data.kindred;
            deferred_kindred.resolve();
            meta_data.onSuccess({});
        },

        /**
         * Receives a request from the domus domain to generate some suggestions using an Rhythm.
         *
         * Suggestion Rhythms have an init method which stores a series of json urls
         * in BabblingBrook.Suggestion.suggestion_urls
         * These urls are then passed to the domus domain, where they are fetched.
         * This is so that any private data can be retrieved.
         * The retrieved json data is then passed back to this class by calling actionSuggestionDataReceived.
         * The data is loaded and the main method of the Rhythm is called, which generates suggestions and
         * stores them in BabblingBrook.Suggestion.suggestions.
         * When the Rhythm is finished it calls BabblingBrook.Suggestion.suggestionsGenerated which passes
         * the suggestions back to the domus domain where they are cached and then passed back to the client.
         * The main method of the Rhythm is then called.
         *
         * @param {object} data
         * @param {string} data.type The type of object suggestions are needed for.
         *                           See BabblingBrook.Models.suggestionTypes for details.
         * @param {object} data.paramaters The paramaters that are needed for this type.
         *                                 See BabblingBrook protocol documentation for details.
         * @param {object} data.user The user whose domus is running this rhythm.
         * @param {object} data.user.username The username of the user whose domus is running this rhythm.
         * @param {object} data.user.domain The domain of the user whose domus is running this rhythm.
         * @param {object} data.client_domain The client website that is requesting this rhythm to run.
         * @param {object} data.rhythm An array contining details of the Rhythm to be used.
         * @param {string} data.rhythm.js The Rhythm code.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionRunRhythm : function (data, meta_data) {
            suggestion_type = data.type;
            rhythm_data = data.rhythm;
            paramaters = data.paramaters;
            onSuccess = meta_data.onSuccess;
            onError = meta_data.onError;

            client_domain = data.client_domain;
            user = data.user;

            // Only start sorting if kindred data has loaded.
            deferred_kindred.done(function () {
                var code = 'BabblingBrook.Rhythm = (' + data.rhythm.js + '());';
                try {
                    /*jslint evil: true*/
                    eval(code);
                    /*jslint evil: false*/
                } catch (exception) {
                    var error_object = {};
                    console.error("Error whilst evaling rhythm js code.");
                    console.error(exception);
                    console.log(rhythm_data);
                    error_object.error_message = exception.message;
                    onError('suggestion_rhythm_eval_exception', error_object);
                    return false;
                }

                if (typeof BabblingBrook.Rhythm.main !== 'function') {
                    console.error('suggestion main function is not defined.');
                    console.log(rhythm_data);
                    onError('suggestion_rhythm_evaljs_main_missing');
                    return;
                }

                // Create default for init.
                if (typeof BabblingBrook.Rhythm.init !== 'function') {
                    BabblingBrook.Rhythm.init = function () {
                        BabblingBrook.Suggestion.processSuggestions();
                    };
                }

                try {
                    BabblingBrook.Rhythm.init();
                } catch (exception) {
                    var error_object = {};
                    console.error('suggestion init function raised an error.');
                    console.log(exception);
                    console.log(rhythm_data);
                    error_object.error_message = exception.message;
                    onError('suggestion_rhythm_init', error_object);
                    return;
                }

            });
        },

        /**
         * Called by the rhythm when it is ready to start generating suggestions.
         */
        processSuggestions : function () {
            try {
                BabblingBrook.Rhythm.main();
            } catch (exception) {
                console.error('rhythm main function raised an error.');
                console.log(exception);
                console.log(rhythm_data);
                var error_object = {};
                error_object.error_message = exception.message;
                onError('suggestion_rhythm_main', error_object);
            }
        },

        /**
         * Called by the suggestions rhythm when it has finished running.
         *
         * Passes the data back to the domus.
         *
         * @param {array} [suggestions] The generated suggestions.
         *
         * @return void
         */
        suggestionsGenerated:  function (suggestions) {
            if (typeof suggestions === 'undefined') {
                suggestions = [];
            }

            var suggestion_data = {
                type : suggestion_type,
                name : BabblingBrook.Suggestion.suggestion_name,
                suggestions : suggestions
            };
            onSuccess(suggestion_data);
        },

        /**
         * Fetches the paramaters that have been passed in to this suggestion Rhythm.
         *
         * @returns {number|null}
         */
        getParamaters : function () {
            return paramaters;
        },

        /**
         * Fetches the type of suggestion that is expected to be generated.
         *
         * @returns {number|null}
         */
        getSuggestionType : function () {
            return suggestion_type;
        },

        /**
         * Getter for Rhythms to fetch the currnet users kindred relationships.
         *
         * @returns {array}
         */
        getKindred : function () {
            return kindred;
        },

        /**
         * Fetches data for the Rhythm. Called by the Rhythm when it needs the data.
         *
         * @param {string} url The url to fetch data from.
         *                     This must be accessable via the BabblingBrook protocol, using a scientia domain extension.
         * @param {function} rhythmCallback The function to call when the data has been fetched.
         *
         * @return void
         */
        getMiscData : function (url, onFetched) {
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    url : url
                },
                'GetMiscData',
                onFetched,
                onError.bind(null, 'rhythm_getMiscData_error')
            );
        },


        /**
         * Public function for the rhythm to store some data in the users domus domain between sessions.
         *
         * @param {string|object} data The data should be a string. JSON data will be automatically converted to
         *      a string.
         * @param {function} onStored The success callback function.
         * @param {function} [onStoredError] The error callback. If there is an error and this is defined then an
         *      error will not be raised as it is assumed to be handled by the rhythm. If there is no error
         *      callback then an sort error will be raised.
         *
         * @returns {void}
         */
        storeData : function (data, onStored, onStoredError) {

            if (typeof data === 'object') {
                data = JSON.stringify(data);
            }

            if (typeof onStoredError !== 'function') {
                onStoredError = function () {
                    onError('rhythm_domus_data_failed');
                };
            }

            if (typeof data !== 'string') {
                onError('rhythm_domus_data_data_incorrect_format');
                return;
            }

            BabblingBrook.Shared.Interact.postAMessage(
                {
                    data : data
                },
                'StoreData',
                onStored,
                onStoredError
            );

        },

        /**
         * Public function for the rhythm to fetch some data in the users domus domain that was placed there
         * in an earlier sesssion.
         *
         * @param {function} onStored The success callback function.
         * @param {function} [onFetchedError] The error callback. If there is an error and this is defined then an
         *      error will not be raised as it is assumed to be handled by the rhythm. If there is no error
         *      callback then an sort error will be raised.
         *
         * @returns {void}
         */
        getStoredData : function (onFetched, onFetchedError) {
            if (typeof onFetchedError !== 'function') {
                onFetchedError = function () {
                    onError('rhythm_get_data_failed');
                };
            }

            BabblingBrook.Shared.Interact.postAMessage(
                {},
                'GetData',
                onFetched,
                onFetchedError
            );
        },

        /**
         * Getter for rhythms to fetch the user object.
         *
         * @returns {object}
         */
        getUser : function () {
            return user;
        },

        /**
         * Getter for rhythms to fetch the client_domain
         *
         * @returns {object}
         */
        getClientDomain : function () {
            return client_domain;
        }

    };
}());