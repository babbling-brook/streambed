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
 * @fileOverview Processes kindred Rhythms in the kindred domain.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

/**
 * Ready function. Called on document load
 */
$(function () {
    'use strict';
    BabblingBrook.Shared.Interact.setup(BabblingBrook.Kindred, 'kindred');
    BabblingBrook.KindredLoaded.kindred = true;
    BabblingBrook.Shared.Interact.postAMessage(
        {},
        'DomainReady',
        /**
         * An empty callback for when the domus domain acknowledges the ready statement.
         *
         * @return void
         */
        function () {},
        /**
          * Throw an error if one is reported.
          *
          * @return void
          */
        function () {
            console.error('The domus domain is not acknowledging the KindredReady message.');
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
                    'window.onerror in the kindred ready function is erroring whilst wating for client to respond.'
                );
            }
        );
    };

    throw 'sdsd';
});

/**
 * @namespace A global object to indicate if all dependant data has loaded.
 * @package JS_Kindred
 */
BabblingBrook.KindredLoaded = {
    kindred : false              // Set to true when the kindred iframe reports it has loaded. Or it is not present.
};

/**
 * @namespace Kindred Rhythm shell.
 * @package JS_Kindred
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
 * Runs in the kindred Rhythm iframe to run Rhythms in an issolated sandbox.
 * IMPORTANT : This should not allow access to any sensitive data.
 */
BabblingBrook.Kindred = (function () {
    'use strict';

    /**
     * @type {number} The index of the take currently being processed.
     *      Starts at minus 1 so that the first run of processNextTake increments it to 0.
     */
    var current_take_id = -1;

    /**
     * @type {object[]} takes Takes that are waiting to be processed.
     * @type {number} takes[].take_id The ID of the current take.
     * @type {number} takes[].timestamp_taken The timestamp of the current take.
     * @type {number} takes[].value The value of the current take.
     * @type {number} takes[].field_id The ifd of the field that was taken.
     * @type {string} takes[].stream_name The name of the stream that the take is from.
     * @type {string} takes[].stream_username The name of the user that owns the strean
     *      that the take is from.
     * @type {string} takes[].stream_domain The domain of the stream that the take is from.
     * @type {string} takes[].post_username The username of the owner of the post that was taken.
     * @type {string} takes[].post_domain The domain of the owner of the post that was taken.
     * @type {string} takes[].local_id The post id local to the user running the rhythm.
     * @type {string} takes[].timestamp_processed If there is already a take value for
     *      this rhythm and take, then this is the date it was made.
     * @type {string} takes[].score If there is already a take value for
     *      this rhythm and take, then this is it.
     * @type {string} takes[].user_rhythm_id The id of the rhythm that this score belongs to.
     */
    var takes = [];

    /**
     * @type {number{}} scores An array of scores against users caluclated for this rhythm.
     *                               Indexed by the servers take_id.
     * @type {number} scores[take_id].full_username The full username that the take is being made against.
     * @type {number} scores[take_id].score The value of the score.
    */
    var rhythm_scores = {};

    /**
     * @type {function} onError Process an error in the Rhythm. Inform the client and the rhythm writter.
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
     * @type {object} rhythm The kindred rhythm object sent from the domus domain.
     * @type {string} rhythm.js The javascript of the rhythm that is being executed.
     *      Once this has been evalled it should return three encapsulated public functions,
     *      init() and main(take) and final(posts). See protocol documentation for full definitions.
     */
    var rhythm_data = null;

    /**
     * @type {object[]|null} kindred The current users kindred data.
     * @type {string} kindred[].full_username The full username of the kindred user.
     * @type {number} kindred[].score The value of this relationship.
     */
    var kindred = null;

    /**
     * Sets the scores made by the rhythm for the current take_id.
     *
     * Should only be called once per take_id.
     *
     * @param {object[]} [scores] An array of score objects to make against the current take_id.
     * @param {string} scores[].full_username The full username of the user the score is against.
     * @param {number} scores.score The value of the score.
     *
     * @return void
     */
    var setScores = function (scores) {
        // An empty or undefined score object is allowed so that the current take can be marked as processed.
        if (typeof scores === 'undefined') {
            return;
        }
        if (typeof scores !== 'object') {
            onError('kindred_setScore_not_object_error');
            return false;
        }
        if (jQuery.isEmptyObject(takes) === true) {
            return;
        }

        var test = true;
        jQuery.each(scores, function(id, score_data) {
            var test = BabblingBrook.Test.isA(
                [score_data.full_username, 'full-username'],
                [score_data.int, 'score']
            );
            if (test === false) {
                onError('kindred_setScore_error');
                return false;
            }
            var server_take_id = takes[current_take_id].take_id;
            BabblingBrook.Library.createNestedObjects(rhythm_scores, [server_take_id]);
            rhythm_scores[server_take_id].full_username = score_data.full_username;
            rhythm_scores[server_take_id].score = score_data.score;
        });
        if (test === false) {
            return;
        }
    };

    /**
     * Run the rhythms final function.
     *
     * @returns {void}
     */
    var runFinal = function() {
        try {
            BabblingBrook.Rhythm.final(takes, rhythm_scores);
        } catch (exception) {
            console.error('rhythm final function raised an error.');
            console.log(exception);
            console.log(rhythm_data);
            error_object.error_message = exception.message;
            onError('kindred_rhythm_final', error_object);
        }
    };

    return {

        /**
         * Receives the kindred data via the domus domain domain in case it is needed by rhythms for processing.
         *
         * @param {object} data
         * @param {object} data.kindred See main definition above for details.
         * @param {object} meta_data See Module definition for more details.
         *
         * @reutrn void
         */
        actionReceiveKindredData : function (data, meta_data) {
            var test = BabblingBrook.Test.isA([data.kindred, 'object'], 'kindred data is not valid. ');
            if (test === false) {
                meta_data.onError('filter_test_kindred_data');
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
                meta_data.onError('filter_test_kindred_data');
                return;
            }
            kindred = data.kindred;
            BabblingBrook.KindredLoaded.kindred = true;
            meta_data.onSuccess({});
        },

        /**
         * Receives rhythm data from the domus domain.
         *
         * It then sets up the rhythm, and runs their init methods.
         * The rhythms init methods then fetch any extra data and when ready runs processNextTake() to start processing
         * the takes.
         *
         * @param {object} data
         * @param {object} data.user
         * @param {object} data.takes Contains deatils of unprocessed takes ready to be passed to the kindred algorithm.
         * @param {number} data.takes.take_id The id of the take being processed.
         * @param {number} data.takes.timestamp_taken The time the take was made.
         * @param {number} data.takes.value The value of the take.
         * @param {number} data.takes.field_id The display order of the field in the post.
         * @param {string} data.takes.stream_name The name of the stream the post that was taken is in.
         * @param {string} data.takes.stream_username The username of the stream the post that was taken is in.
         * @param {string} data.takes.stream_domain The domain of the stream the post that was taken is in.
         * @param {string} data.takes.stream_version The version of the stream the post that was taken is in.
         * @param {number} data.takes.post_user_id The id of the user who made the post that was taken.
         * @param {string} data.takes.post_username  The username of the user who made the post that was taken.
         * @param {string} data.takes.post_domain  The domain of the user who made the post that was taken.
         * @param {number} data.takes.o_local_id The id of the post local to this user of the post that was taken.
         * @param {object} data.user The user whose domus domain is running this rhythm.
         * @param {object} data.user.username The username of the user whose domus domain is running this rhythm.
         * @param {object} data.user.domain The domain of the user whose domus domain is running this rhythm.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionRunRhythm : function (data, meta_data) {
            var test = BabblingBrook.Test.isA([data.rhythm, 'object'], 'kindred.actionReceiveUser error');
            if (test === false) {
                meta_data.onError('kindred_test_receive_user');
                return;
            }
            if (typeof data.rhythm.js !== 'string') {
                meta_data.onError('kindred_rhythm_js_missing');
                return false;
            }
            var test = BabblingBrook.Test.isA([[data.takes, 'array']], 'kindred.actionReceiveTakes error');
            if (test === false) {
                var error_data = {
                    original_data : data
                };
                meta_data.onError('kindred_test_receive_takes', error_data);
                return;
            }
            // If there are no takes to process
            if (jQuery.isEmptyObject(data.takes) === true) {
                var error_data = {
                    original_data : data
                };
                meta_data.onError('kindred_test_takes_empty', error_data);
                return;
            }

            client_domain = data.client_domain;
            user = data.user;

            onError = meta_data.onError;

            rhythm_data = data.rhythm;
            takes = data.takes;

            var code = 'BabblingBrook.Rhythm = (' + rhythm_data.js + '());';
            try {
                /*jslint evil: true*/
                eval(code);
                /*jslint evil: false*/
            } catch (exception) {
                console.error("Error whilst evaling rhythm js code.");
                console.log(exception);
                console.log(rhythm_data);
                var error_object = {};
                error_object.error_message = exception.message;
                onError('kindred_rhythm_eval_exception', error_object);
                return false;
            }

            if (typeof BabblingBrook.Rhythm !== 'object') {
                console.error("Error whilst evaling rhythm js code. Not rhythm object.");
                console.log(rhythm_data);
                onError('kindred_rhythm_not_an_object');
                return false;
            }
            if (typeof BabblingBrook.Rhythm.main !== 'function') {
                console.error('Error whilst evaling rhythm js code. main function is not defined.');
                console.log(rhythm_data);
                onError('kindred_rhythm_main_not_a_function');
                return false;
            }
            // Setup default init and final functions if they are not defined.
            if (typeof BabblingBrook.Rhythm.init !== 'function') {
                BabblingBrook.Rhythm.init = function () {
                    BabblingBrook.Kindred.processNextTake();
                };
            }
            if (typeof BabblingBrook.Rhythm.final !== 'function') {
                BabblingBrook.Rhythm.final = function (takes, scores) {
                    BabblingBrook.Kindred.finishTakes(scores);
                };
            }
            BabblingBrook.Kindred.current_scores = {};

            try {
                BabblingBrook.Rhythm.init();
            } catch (exception) {
                console.error('rhythm init function raised an error.');
                console.log(exception);
                console.log(rhythm_data);
                var error_object = {};
                error_object.error_message = exception.message;
                onError('kindred_rhythm_init', error_object);
            }

            meta_data.onSuccess({});    // This does nothing but need to call it to prevent a timeout error.
        },

        /**
         * Processes the next take in the queue for this rhythm.
         *
         * This is called by the rhythm when it is ready to proccess a take.
         * It first records any scores for the current take and then
         * it passes the next take to the main function of the rhythm
         *
         * @param {object[]} [scores] An array of score objects to make against the current take_id.
         * @param {string} scores[].full_username The full username of the user the score is against.
         * @param {number} scores.score The value of the score.
         *
         * @returns {undefined}
         */
        processNextTake : function (scores) {
            if (current_take_id >= 0) { // Don't process for the first run as there is no score data.
                setScores(scores);
            }
            current_take_id++
            // If there are no takes left then the rhythms final method is called, that in turn calls finishTakes.
            if (typeof takes[current_take_id] === 'undefined') {
                runFinal();
                return;
            }
            try {
                BabblingBrook.Rhythm.main(takes[current_take_id]);
            } catch (exception) {
                console.error('rhythm main function raised an error.');
                console.log(exception);
                console.log(rhythm_data);
                var error_object = {};
                error_object.error_message = exception.message;
                onError('kindred_rhythm_main', error_object);
            }
        },

        /**
         * Called by the rhythm when it has finished the final function.
         *
         * Sends the scores back to the domus domain for saving.
         *
         * @param {object[]} scores An array of scores. See the main definition above.
         *
         * @returns {undefined}
         */
        finishTakes : function (scores) {
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    scores : scores
                },
                'SaveKindredResults',
                /**
                 * An empty success function as no action needs to be takens
                 *
                 */
                function() {},
                /**
                 * Throw an error if the domus domain fails.
                 *
                 * @param {string} error_code See BabblingBrook.Models.errorTypes for valid codes.
                 * @param {object} error_data A standard error_data object.
                 *
                 * @return void
                 */
                function(error_code, error_data) {
                    console.error(error_code, error_data);
                    console.error('Domus domain returned an error when kindred domain tried to StoreKindredResults.');
                    throw 'Thread execution stopped.';
                }
            );
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
         * @param {function} onFetched The function to call when the data has been fetched.
         * @param {function} [onError] Callback for catching errors.
         *
         * @return void
         */
        getMiscData : function (url, onFetched, onDataError) {

            if (typeof onDataError === 'undefined') {
                onDataError = function () {};
            }

            onErrorFull = function () {
                onDataError();
                onError('rhythm_getMiscData_error');
            };
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    url : url
                },
                'GetMiscData',
                onFetched,
                onErrorFull
            );
        },

        /**
         * Public function for the rhythm to store some data in the users domus domain between sessions.
         *
         * @param {string|object} data The data should be a string. JSON data will be automatically converted to
         *      a string.
         * @param {function} onStored The success callback function.
         * @param {function} [onError] The error callback. If there is an error and this is defined then an
         *      error will not be raised as it is assumed to be handled by the rhythm. If there is no error
         *      callback then an sort error will be raised.
         *
         * @returns {void}
         */
        storeData : function (data, onStored, onDataError) {
            if (typeof data === 'object') {
                data = JSON.stringify(data);
            }

            if (typeof onDataError !== 'function') {
                onDataError = function () {};
            }

            if (typeof data !== 'string') {
                onDataError('rhythm_store_data_data_incorrect_format');
                onError('rhythm_store_data_data_incorrect_format');
                return;
            }

            var onFullError = function () {
                onDataError('rhythm_store_data_failed');
                onError('rhythm_store_data_failed');
            };

            BabblingBrook.Shared.Interact.postAMessage(
                {
                    data : data
                },
                'StoreData',
                onStored,
                onFullError
            );

        },

        /**
         * Public function for the rhythm to fetch some data in the users domus domain that was placed there
         * in an earlier sesssion.
         *
         * @param {function} onStored The success callback function.
         * @param {function} [onError] The error callback. If there is an error and this is defined then an
         *      error will not be raised as it is assumed to be handled by the rhythm. If there is no error
         *      callback then an sort error will be raised.
         *
         * @returns {void}
         */
        getStoredData : function (onFetched, onError) {
            if (typeof onError !== 'function') {
                onError = function () {
                    error('rhythm_get_data_failed');
                };
            }

            BabblingBrook.Shared.Interact.postAMessage(
                {},
                'GetStoredData',
                onFetched,
                onError
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



