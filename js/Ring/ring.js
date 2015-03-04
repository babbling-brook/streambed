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
 * @fileOverview Processes ring Rhythms in the kindred domain.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

/*
 * Document ready function for the ring domain.
 */
jQuery(function () {
    'use strict';
    BabblingBrook.Shared.Interact.setup(BabblingBrook.Ring, 'ring');
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
            console.error('The domus domain is not acknowledging the RingReady message.');
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
                    'window.onerror in the ring ready function is erroring whilst wating for client to respond.'
                );
            }
        );
    };
});

/**
 * @namespace A global object to indicate if all dependant data has loaded.
 * @package JS_Ring
 */
BabblingBrook.RingLoaded = {
    kindred : false                 // Set to true when the kindred iframe reports it has loaded. Or it is not present.
};

/**
 * @namespace Global object holding methods related to the ring domain.
 * @package JS_Ring
 * Runs in the ring Rhythm iframe to run Rhythms in an issolated sandbox.
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
 * Important : This should not allow access to any sensitive data.
 */
BabblingBrook.Ring = (function () {
    'use strict';

    /**
     * @type function The success function to call when the ring has finished processing.
     */
    var onSuccess;

    /**
     * @type function The error function to call when the ring has finished processing.
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

    /*
     * @type {object} rhythm The kindred rhythm object sent from the domus domain.
     * @type {string} rhythm.js The javascript of the rhythm that is being executed.
     *      Once this has been evalled it should return three encapsulated public functions,
     *      init() and main(take) and final(takes, scores). See protocol documentation for full definitions.
     */
    var rhythm_data;

    return {

        /**
         * @type {object} rhythm The kindred rhythm object sent from the domus domain.
         * @type {string} rhythm.js The javascript of the rhythm that is being executed.
         *      Once this has been evalled it should return three encapsulated public functions,
         *      init() and main(take) and final(posts). See protocol documentation for full definitions.
         */
        rhythm_data : null,


        /*
         * Receives user data from the domus domain, which includes takes waiting for processing.
         *
         * It then sets up the rhythms (which are stored in the user data), runs their main methods.
         *
         * @param {object} data
         * @param {object} data.rhythm The Rhythm.
         * @param {string} data.rhythm.js The Rhythm code
         * @param {string} data.rhythm.user The username of the owner of the Rhythm.
         * @param {string} data.rhythm.name The name of the Rhythm.
         * @param {string} data.rhythm.version The version of the Rhythm.
         * @param {string} data.rhythm.dateCreated
         * @param {string} data.rhythm.status Is the Rhythm public, private or deprecated.
         * @param {string} data.rhythm.description The description of this Rhythm.
         * @param {string} data.rhythm.js The Rhythm code in a string.
         * @param {object} data.user The user whose domus domain is running this rhythm.
         * @param {object} data.user.username The username of the user whose domus domain is running this rhythm.
         * @param {object} data.user.domain The domain of the user whose domus domain is running this rhythm.
         * @param {object} meta_data See Module definition for more details.
         */
        actionRunRhythm : function (data, meta_data) {

            onSuccess = meta_data.onSuccess;
            onError = meta_data.onError;

            var test = BabblingBrook.Test.isA(
                [
                    [data.rhythm, 'object'],
                    [data.rhythm.js, 'object'],
                    [data.rhythm.user, 'object'],
                    [data.rhythm.user, 'username'],
                    [data.rhythm.js, 'domain']
                ],
                'BabblingBrook.Ring.actionStartRhythm error'
            );
            if (test === false) {
                onError('ring_test_start_rhythm');
                return;
            }

            client_domain = data.client_domain;
            user = data.user;

            // Load and run the rhythm.
            rhythm_data = data.rhythm;
            var code = 'BabblingBrook.Rhythm = (' + data.rhythm.js + '());';
            try {
                /*jslint evil: true*/
                eval(code);
                /*jslint evil: false*/
            } catch (exception) {
                console.error("Error whilst evaling rhythm js code.");
                console.error(exception);
                console.log(rhythm_data);
                var error_object = {};
                error_object.error_message = exception.message;
                onError('ring_rhythm_eval_exception', error_object);
                return;
            }

            try {
                BabblingBrook.Ring.main();
            } catch (exception) {
                console.error('ring init function raised an error.');
                console.log(exception);
                console.log(rhythm_data);
                error_object.error_message = exception.message;
                onError('ring_rhythm_main', error_object);
                return;
            }
        },

        /**
         * Called by the Rhythm when it has finished processing.
         *
         * @reutrn void
         */
        finishedRhythm : function () {
            onSuccess();
        },

//        banUser : function () {
//            console.error('actionBanUser not written yet');
//        },
//
//        inviteUser : function () {
//            console.error('actionInviteUser not written yet');
//        },

        /**
         * Fetches data for the Rhythm. Called by the Rhythm when it needs the data.
         *
         * @param {string} url The url to fetch data from.
         *      This must be accessable via the BabblingBrook protocol, using a Scientia domain extension.
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
         * Store some data in the rings domain between sessions.
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
        storeRhythmData : function (computed_data, onStored, onStoredError) {

            if (typeof data === 'object') {
                computed_data = JSON.stringify(computed_data);
            }

            if (typeof onStoredError !== 'function') {
                onStoredError = function () {
                    onError('rhythm_store_rhythm_data_failed');
                };
            }

            if (typeof computed_data !== 'string') {
                onStoredError('rhythm_store_rhythm_data__incorrect_format');
                return;
            }

            BabblingBrook.Shared.Interact.postAMessage(
                {
                    computed_data : computed_data
                },
                'StoreRhythmData',
                onStored,
                onStoredError
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
                    onError('rhythm_store_data_failed');
                };
            }

            if (typeof data !== 'string') {
                onStoredError('rhythm_store_data_data_incorrect_format');
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
         * @param {function} [onFetchError] The error callback. If there is an error and this is defined then an
         *      error will not be raised as it is assumed to be handled by the rhythm. If there is no error
         *      callback then an sort error will be raised.
         *
         * @returns {void}
         */
        getStoredData : function (onFetched, onFetchError) {
            if (typeof onFetchError !== 'function') {
                onFetchError = function () {
                    onError('rhythm_get_data_failed');
                };
            }

            BabblingBrook.Shared.Interact.postAMessage(
                {},
                'GetData',
                onFetched,
                onFetchError
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
        },

        /**
         * Enables a Ring Rhythm to ok an request for membership.
         *
         * @param {object} user A user object representing the user whose membership is being accepted.
         *
         * @return {undefined}
         */
        acceptMembershipRequest : function (user, onAccepted, onAcceptedError) {
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    user : user
                },
                'AcceptMembershipRequest',
                onAccepted,
                onAcceptedError
            );
        },

        /**
         * Enables a Ring Rhythm to ban a ring member
         *
         * @param {object} user A user object representing the user whose membership is being banned.
         *
         * @return {undefined}
         */
        banMember : function (user, onAccepted, onAcceptedError) {
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    user : user
                },
                'BanMember',
                onAccepted,
                onAcceptedError
            );
        },

        /**
         * Enables a Ring Rhythm to ban a ring member
         *
         * @param {object} user A user object representing the user whose membership is being banned.
         *
         * @return {undefined}
         */
        reinstateMember : function (user, onAccepted, onAcceptedError) {
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    user : user
                },
                'ReinstateMember',
                onAccepted,
                onAcceptedError
            );
        }
    };
}());