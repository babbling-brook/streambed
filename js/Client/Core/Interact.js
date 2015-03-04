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
 * @fileOverview This is the inclientfo domain version of interact.js
 * Important :    The different domain interact.js files are seperate files despite the fact they could be
 *                consolidated in order to prent obscure security issues. Each file should remain as simple as possible
 *                in order to prevent opening up holes.
 * @author Sky Wickenden
 */

/**
 * @namespace Gloabl interaction class. Used for posting messages to the domus domain iframe.
 * @package JS_Client
 */
BabblingBrook.Client.Core.Interact = (function () {
    'use strict';

    /**
     * The frequency, in milliseconds, of how often the localstorage record of the domus domain iframe is refreshed.
     * @type {number}
     */
    var domus_refresh = 250;

    /**
     * @type {boolean} A flag to indicate that the domaus domain has been inserted so that it is only created once.
     */
    var domus_inserted = false;

    /**
     * The number of milliseconds overlap that is allowed after a refresh
     * before the primary domus domain is considered abandoned.
     * @type {number}
     */
    var domus_overlap = 2000;

    /**
     * A reference to the domus domain iframe window that is currently being used. (In this window or another).
     */
    var domus_window;

    /**
     * @type {object} callbacks success and error callbacks for each message sent.
     * @type {string} callbacks.<uuid> The uuid of a request that was sent out that is
     *                                 used to identify an incoming request.
     * @type {function} callbacks.<uuid>.success The success callback.
     * @type {function} callbacks<uuid>.error The error callback.
     * @type {strung} callbacks<uuid>.action The action name that was called in the domus domain.
     *     Used to test the returning data for errors.
     * @type {object} callbacks<uuid>.parent_data Any data to be passed to the success and error callbacks from the
     *     origional request.
     */
    var callbacks = {};

    /**
     * @type {string} The url of the users iframe domus domain.
     */
    var location;

    /*
     * Report an error message for an incoming message.
     *
     * @param {string} error The message to send.
     * @param {string} action The action name.
     * @param {object} [data] The action data.
     * @param {string} [error_code] The BabblingBrook protocol error code associated with this error.
     * @param {string} [response_uuid] The uuid associated with this request.
     */
    var reportError = function (message, action, data, error_code, response_uuid) {
        console.log('Client interact error: ' + message);
        console.trace();

        if (typeof data !== 'object') {
            data = {};
        }
        if (typeof error_code !== 'string') {
            error_code = 'domus_action_error';
            data.action = action;
        }

        if (typeof response_uuid === 'string' && typeof callbacks[response_uuid] !== 'undefined') {
            if (typeof callbacks[response_uuid].error === 'function') {
                callbacks[response_uuid].error(error_code, data, callbacks[response_uuid].parent_data);
            }
        }
        BabblingBrook.Library.logError(error_code, data, message, 'client');
        BabblingBrook.Client.Component.ReportBug.appendSubDomainError({
            domain : 'domus-communication-error',
            error : error_code + ' ' + JSON.stringify(data) + ' ' + message
        });
        return true;
    };

    /**
     * Records that the domus domain has been inserted in this browser and sets a timestamp.
     *
     *  @return void
     */
    var recordDomusInserted = function() {
        BabblingBrook.LocalStorage.store(
            'domus-location',
            {
                time : new Date().getTime()
            }
        );
    };

    /**
     * Insert the domus domain iframe.
     *
     * Waits for the domus domain to send a ready message before calling the callback.
     *
     * @return void
     */
    var insertDomus = function(callback) {
        if (domus_inserted === true) {
            BabblingBrook.Client.Core.Loaded.onDomusLoaded(callback);
            return;
        }
        domus_inserted = true;
        // May have been using an iframe in another window until now.
        var client_https = window.location.protocol === 'https:' ? true : false;
        jQuery('body').append(' ' +
            '<iframe style="display:none" id="domus" name="domus_window" ' +
            '        src="http://domus.' + BabblingBrook.Client.User.domain +
                          '?client=' + BabblingBrook.Client.ClientConfig.host +
                          '&https=' + client_https + '">' +
            '</iframe>');
        domus_window = document.getElementById('domus').contentWindow;
        recordDomusInserted();

        // Setup a timeout to regularly update the timestamp for this users browser agent.
        // Do this in a repeating setTimeout rather than a setInterval so that it stops when the user logs out.
        var record = function () {
            if(typeof BabblingBrook === 'undefined') {
                return;
            } else {
                recordDomusInserted();
                setTimeout(record, domus_refresh);
            }
        };
        record();

        // Setup a browser close event to remove this domus domain from local storage if the window closes.
        window.onunload = function(){
            BabblingBrook.LocalStorage.remove('domus-location');
            return true;
        };

        BabblingBrook.Client.Core.Loaded.onDomusLoaded(callback);
    };

    /**
        * Checks if a domus domain iframe is available. If not it inserts one.
        *
        * First of all workout if there is already a valid domus domain.
        * Do this by checking localStorage for a flag and then attempting to reference it.
        * If they don't then open a new domus domain iframe is inserted and a flag in local storage is set.
        *
        * @param {function} callback A function to call when the domus domain iframe is ready.
        *
        * @return void
        */
    var checkDomus = function (callback) {
        // If single domus domain is turned off for debugging then only check if it is already present.
        if (BabblingBrook.Client.ClientConfig.single_domus_iframe !== true) {
            if(jQuery('#domus').length > 0) {
                BabblingBrook.Client.Core.Loaded.onDomusLoaded(callback);
                return;
            } else {
                insertDomus(callback);
                return;
            }
        }

        // Fetch local storage records.
        var domus = BabblingBrook.LocalStorage.fetch('domus-location');
        var now = new Date().getTime();
        if (typeof domus !== 'object'
            || typeof domus.data.time !== 'number'
            || domus.data.time + domus_refresh + domus_overlap < now
        ) {
            // This store record is out of date. Create a new store.
            console.log('creating domus iframe.');
            insertDomus(callback);
            return;
        } else {
            if(typeof domus_window === 'undefined') {
                console.log('reloading domus iframe.');
                // Domus record is valid. Fetch a reference.
                domus_window = window.open('', 'domus_window');
                // Just in case it is not present, close instantly and reinstate iframe.
                // Do in a try, because if it is present it will error.
                try {
                    if(domus_window.location.href === 'about:blank' ) {
                        domus_window.close();
                        insertDomus(callback);
                        return;
                    }
                } catch(err) {
                }
            }
        }

        BabblingBrook.Client.Core.Loaded.onDomusLoaded(callback);
    };

    return {

        /**
         * Receive a message from another domains frame.
         *
         * The object needs to be double wrapped.
         *
         * @param {object} event
         * @param {string} event.data The string passed from the domus domain. When parsed to json it contains.
         * @param {string} [event.data.action] The name of a action to be called on object paramater to validate and
         *      utilise the data. All valid actions start with 'action', but this is
         *      not included in the action name passed in. This only required for
         *      incoming requests for data and not responses to requests.
         * @param {string} [event.data.request_uuid] The uuid that this domain origionaly sent to the domus domain.
         * @param {string} [event.data.response_uuid] The uuid that is sent with this request from this domian.
         *      The domus domain is returning it so that we can fetch the
         *      correct callbacks for the data.
         *      This is only required for returning responses.
         */
        receiveMessage : function (event) {
            var error = false;
            var origin = event.origin.substring(7);        // remove http://

            // Check if the domain is allowed access
            if ('domus.' + BabblingBrook.Client.User.domain !== origin) {        // Must be this users domus domain.
                error = reportError(
                    'The Domain sending message is not allowed. origin: ' + origin +
                    ' BabblingBrook.Client.User.domain : ' + BabblingBrook.Client.User.domain , '(unknown)'
                );
            }

            // Check if it is a logout request.
            // If it is then just redirect to the home page. The client will already have been logged out.
            // Do in a timeout to ensure that all postMessage calls are sent, as this might be the client
            // window/tab that contains the domus domain connection and we don't want to remove it before other calls
            // have finished.
            if (event.data === 'logout') {
                setTimeout(function () {
                    window.location = 'http://' + window.location.host + '/?loggedout=true';
                }, 1000)

                return;
            }

            // Convert string to JSON
            var message = BabblingBrook.Library.parseJSON(event.data);
            if (!message || typeof message !== 'object') {
                error = reportError('Incoming message did not parse to JSON', '(unknown)');
            }

            if (typeof message.request_uuid === 'undefined') {
                error = reportError('Message did not include a request_uuid', '(unknown)');
            }

            if (typeof message.response_uuid === 'undefined' && typeof message.action === 'undefined') {
                error = reportError(
                    'Returning message did not include a response_uuid (or action is missing)',
                    '(unknown)'
                );
            }

            if (typeof message.action === 'string' && typeof message.request_data !== 'object') {
                error = reportError(
                    'request_data is malformed or not a JSON object',
                    message.action,
                    {},
                    'domus_action_data_invalid',
                    message.response_uuid
                );
            }
            if (typeof message.response_uuid !== 'undefined' && typeof message.response_data !== 'object') {
                error = reportError(
                    'response_data is malformed or not a JSON object',
                    message.action,
                    {},
                    'domus_action_data_invalid',
                    message.response_uuid
                );
            }
            // Check if it is a ready request.
            if (message.action === 'DomainReady') {
                BabblingBrook.Client.Core.Loaded.setDomusLoaded();
                return;
            }

            if (typeof message.action === 'string' && typeof message.response_uuid === 'string') {
                error = reportError(
                    'Action should not be defined when sending a response. response_uuid : ' + message.response_uuid
                );
            }

            if (typeof message.request_uuid !== 'string' && typeof message.response_uuid !== 'string') {
                var action_error = '';
                if (typeof message.action === 'string') {
                    action_error = ' Action : ' + message.action;
                }
                error = reportError('No request_uuid or response_uuid is attatched to this request.' + action_error);
            }
            if (error) {
                return;
            }

            // Clears any old test errors before calling the action/callback.
            // This saves calling it in every action/callback.
            BabblingBrook.TestErrors.clearErrors();
            // If this is calling an action then check that it exists and then call it and exit.
            if (typeof message.action === 'string') {
                var fn_name = 'action' + message.action;
                if (typeof BabblingBrook.Client.Core.Controller[fn_name] !== 'function') {
                    reportError(
                        'Action is not found/invalid.',
                        message.action,
                        message.request_data,
                        'domus_unknown_action',
                        message.request_uuid
                    );
                } else {
                    if (typeof BabblingBrook.Client.Core.Controller[fn_name] !== 'function') {
                        reportError(
                            'Data is not valid: ' + BabblingBrook.TestErrors.getErrors(),
                            message.action,
                            message.request_data,
                            'domus_action_data_invalid',
                            message.request_uuid
                        );
                    } else {
                        BabblingBrook.Client.Core.Controller[fn_name](message.request_data);
                    }
                }
                return;
            }
            // This must be a returning message.
            if (typeof callbacks[message.request_uuid] !== 'object') {
                var error_message = 'Callback object not found for request_uuid : ' + message.request_uuid;
                error = reportError(error_message, undefined, undefined, undefined, message.request_uuid);
                return;
            }

            var success_callback  = callbacks[message.request_uuid].success;
            if (typeof success_callback !== 'function' && success_callback !== null) {
                error = reportError('Success callback not found for request_uuid : ' + message.request_uuid);
                return;
            }

            // Test that the data attatched to the returning message is valid.
            var action = callbacks[message.request_uuid].action;
            if (typeof message.response_data.error_code === 'undefined') {
                var testAction = BabblingBrook.Client.Core.DomusDataTests['test' + action];
                if (typeof testAction !== 'function') {
                    console.error('test function missing for action : ' +
                        action + ' uuid : ' + message.request_uuid);
                }

                /**
                 * Callback for DomusDataTests to report the success of the test.
                 *
                 * @param {type} default_data If the data is adapted by the tests then it is passed back here.
                 *
                 * @returns {undefined}
                 */
                var onTested = function (default_data) {
                    if (default_data === false) {
                        error = reportError(
                            'Data is not valid: ' + BabblingBrook.TestErrors.getErrors(),
                            '[returning from] ' + action,
                            message.response_data,
                            'domus_action_data_invalid',
                            message.request_uuid
                        );
                        return;
                    }

                    // Determines if we should use data passed back from the test - with defaults applied
                    // or the raw data received from the domus domain.
                    if (typeof default_data !== 'boolean') {
                        message.response_data = default_data;
                    }

                    success_callback(message.response_data, callbacks[message.request_uuid].parent_data);
                    delete callbacks[message.request_uuid];

                };
                testAction(message.response_data, onTested);

            } else {
                var error_callback  = callbacks[message.request_uuid].error;
                if (typeof error_callback !== 'function' && error !== null) {
                    reportError('Error callback not found for request_uuid : ' + message.request_uuid);
                }
                if (typeof message.response_data.error_data === 'undefined') {
                     message.response_data.error_data = {};
                }
                error_callback(message.response_data, callbacks[message.request_uuid].parent_data);
                delete callbacks[message.request_uuid];
            }
        },

        /*
         * Post a message to the site being used.
         * This uses a bridge pattern to uncouple domus domain action requests from the client side code.
         * @param {object} data The data to send.
         * @param {string} action The name of the action to call in the domus domain (not including 'process').
         * @param {funciton} [success] The callback to run when this domus domain action returns.
         *     Must always have the following signature :
         *         data {object} the data passed back with the timeout.
         * @param {function} [error] The callback to run if this action returns with an error or timesout.
         *     Error callback must have the following signature :
         *          response_data {object}
         *              error_code {string} The error code that the error is being called with.
         *              error_data {object} Any data being passed back with the error.
         * @param {number} [timeout] Milliseconds until this request timesout, causing the error to run.
         *     Defaults to config value.
         * @param {object} [callback_data] Data to pass to the callback. Useful if the callback is not inline and
         *     It needs so data passing to it from the calling method.
         */
        postAMessage : function (data, action, success, error, timeout, callback_data) {
            if (typeof action !== 'string') {
                console.error('Must provide an action name.');
            }

            if (typeof BabblingBrook.Client.User === 'undefined' || typeof BabblingBrook.Client.User.domain === 'undefined') {
                return;
            }

            if (typeof timeout !== 'number') {
                if (typeof BabblingBrook.Client.User.ClientConfig !== 'undefined') {
                    timeout = BabblingBrook.Client.User.ClientConfig.domus_timeout;
                } else {
                    timeout = BabblingBrook.Client.DefaultConfig.domus_timeout;
                }
            }

            if (typeof success !== 'function') {
                success =  function () {};
            }
            if (typeof error !== 'function') {
                error =  function (response_data) {
                    var error_data = '';
                    if (typeof response_data.error_data === 'object') {
                        error_data = JSON.stringify(response_data.error_data);
                    }
                    BabblingBrook.Client.Component.Messages.addMessage({
                        type : 'error',
                        message : 'There was an error whilst requesting data from the ' + action +
                            ' action from the domus domain with the following error code : ' + response_data.error_code,
                        error_details : 'response_data: \r\n' + JSON.stringify(response_data) + '\r\n' +
                            '\r\nThe original data sent was : \r\n' + JSON.stringify(data)
                    });
                };
            }

            // This uuid is sent to the remote domain and then passed back with any response.
            // It us unique enough to not only prevent accidental calling of the wrong action but also to make the
            // calls by other websites open in the users browser unable to access this window.
            var uuid = BabblingBrook.Library.generateUUID();

            // Nest the success callback so that it cancels if the page has been redirected.
            var nestedSuccess;
            (function(){
                var redirect_count = BabblingBrook.Client.Core.Ajaxurl.getRedirectCount();
                nestedSuccess = function (reqested_data, callback_data) {
                    var current_redirect_count = BabblingBrook.Client.Core.Ajaxurl.getRedirectCount();
                    if (redirect_count === current_redirect_count) {
                        success(reqested_data, callback_data);
                    } else {
                        var actionsToAlwaysComplete = [
                            'GetWaitingPostCount',
                            'FetchStreamSubscriptions',
                            'FetchStream', // Deffered objects will be waiting for them.
                        ];
                        if (jQuery.inArray(action, actionsToAlwaysComplete) === -1) {
                            console.log('Call to ' + action + ' abandoned due to page redirect or reload.');
                        } else {
                            success(reqested_data, callback_data);
                        }
                    }
                };
            })();

            callbacks[uuid] = {};
            callbacks[uuid].success = nestedSuccess;
            callbacks[uuid].error = error;
            callbacks[uuid].action = action;

            var testing = false;
            if (BabblingBrook.Library.getCookie('testing') === 'true') {
                testing = true;
            }

            callbacks[uuid].parent_data = callback_data;
            var message = {
                version : 1,
                request_data : data,
                action : action,
                request_uuid : uuid,
                // record the timestamp for when the timeout will occour rather than the time until timeout.
                timeout : parseInt(Math.round(new Date().getTime())) + parseInt(timeout),
                testing : testing
            };
            message = JSON.stringify(message);

            // Once the request has timed out run the the error callback and then delete it.
            if (action !== 'Error') {

                var requestTimeout = function() {
                    setTimeout(function () {
                        if (typeof callbacks[uuid] === 'object') {

                            // If a retry is needed this needs to be done in the error callback
                            // so that edge cases can be accounted for.
                            callbacks[uuid].error({
                                error_code : 'timeout',
                                error_data : data
                            });
                            var message = 'A timeout occoured when calling an action: ' + action + '. uuid : ' + uuid;
                            reportError(message, action, data, 'client_timeout', uuid);
                            delete callbacks[uuid];
                        }
                    }, timeout);
                };
                requestTimeout();

            }
            checkDomus(function() {
                domus_window.postMessage(message, location);
            });
        },

        construct : function() {
            // Set the location of the users domus domain.
            location = 'http://domus.' + BabblingBrook.Client.User.domain;
            // Give this window/tab a unique name so that it can be identified in the domus.
            window.name = 'salt-tab-' + BabblingBrook.Library.generateUUID();
            checkDomus(function(){});
        }
    };
}());