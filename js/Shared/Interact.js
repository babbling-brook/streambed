// ****************  This is the new shared interact class where success and error callbacks are used.

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
 * @fileOverview This is the interact version to use for all rhythm domains.
 * Important :    The different domain interact.js files are seperate files despite the fact they could be
 *                consolidated in order to prevent obscure security issues.
 *                Each file should remain as simple as possible in order to prevent opening up holes.
 *    The main rhythm js file must define BabblingBrook.SharedError.Controller
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Shared !== 'object') {
    BabblingBrook.Shared = {};
}

jQuery(function () {
    'use strict';
    window.addEventListener('message', BabblingBrook.Shared.Interact.receiveMessage, false);
});

/**
 * @namespace Gloabl interaction class. Used for posting messages back to the domus domain.
 * @package JS_Suggestion
 */
BabblingBrook.Shared.Interact = (function () {
    'use strict';
    /**
     * @type {object} This is a pointer to the controller object that contains the actions for this rhythm domain.
     */
    var controller;

    /**
     * @type {string} The subdomain that this is running in.
     */
    var subdomain;

    /**
     * Callbacks registered by this domain for receiving responses from the domus domain.
     *
     * @type {object} callbacks success and error callbacks for each message sent.
     * @type {string} callbacks.<action_uuid> The UID of a request that was sent out that is used to identify
     *                                       an incoming request.
     * @type {function} callbacks.<action_uuid>.success The success callback.
     *      These can accept two paramaters.
     *      The first is the request data object.
     *      The second is the domain of origin of the request.
     * @type {function} callbacks<action_uuid>.error The error callback.
     *      These can accept three paramaters.
     *      The first is the error_code.
     *      The second is an optional error_data object.
     *      The third is the domain of origin of the request.
     */
    var callbacks = {};

    /**
     * Generate a uuid to be used when sending a request to the domus domain.
     *
     * Also save the callbacks to use when the request returns.
     *
     * @param {function} successCallback The function to call when the request returns successfully.
     *      It should accept a single data paramater which will contain the requested data.
     * @param {function} [errorCallback] The function to call when the request is unsucssful.
     *      It should contain two paamaters. The first is required and is the error_code.
     *      The second is option and is generic error_data object.
     *      See the protocol definition for documented error objects.
     *      A genreic error callback is generated if one is not provided.
     *
     * @return string The new uuid.
     */
    var generateUUID = function (successCallback, errorCallback, timeout, action) {
        // This uuid is sent to the domus domain and then passed back with any response.
        var request_uuid = BabblingBrook.Library.generateUUID();
        callbacks[request_uuid] = {};

        if (typeof successCallback !== 'function') {
            console.error(
                'No success function defined when requesting ' + action +
                ' from the ' + window.location.host + ' domain.'
            );
            throw 'Thread execution stopped.';
        }
        callbacks[request_uuid].success = successCallback;
        if (typeof errorCallback !== 'function') {
            errorCallback = function (error_code, error_data) {
                console.error(error_code, error_data);
                console.error(
                    'No error function defined when requesting ' + action +
                    ' from the ' + window.location.host + ' domain.'
                );
                throw 'Thread execution stopped.';
            };
        }
        callbacks[request_uuid].error = errorCallback;

        // Once the request has timed out run the the error callback and then delete this request.
        if (typeof timeout !== 'number') {
            timeout = 30000;
        }
        setTimeout(function () {
            if (typeof callbacks[request_uuid] !== 'undefined') {
                callbacks[request_uuid].error(subdomain + '_request_timed_out');
                delete callbacks[request_uuid];
            }
        }, timeout);

        return request_uuid;
    };

    /**
     * Post a message to the domus domain.
     *
     * @param {object} data The data to send.
     * @param {string} [request_uuid] The uuid that the domus domain uses to identify this return of requested data.
     *      Only required if this is a return message.
     * @param {string} [action] The name of the action to call in the object (not including 'action' at the start).
     *      Only required if making a request.
     * @param {function} successCallback The function to call when the request returns successfully.
     *      It should accept a single data paramater which will contain the requested data.
     * @param {function} errorCallback The function to call when the request is unsucssful.
     *      It should contain two paamaters. The first is required and is the error_code.
     *      The second is option and is generic error_data object.
     *      See the protocol definition for documented error objects.
     * @param {number} [timeout = 30000] The number of milliseconds to wait before timing out a request.
     *
     * @return void
     */
    var postMessageToDomus = function (data, request_uuid, action, successCallback, errorCallback, timeout) {
        if (typeof timeout === 'undefined') {
            timeout = 30000;
        }
        var parent_window, domus_location, message;

        if (typeof action === 'string') {
            request_uuid = generateUUID(successCallback, errorCallback, timeout, action);
            var testing = false;
            if (BabblingBrook.Library.getCookie('testing') === 'true') {
                testing = true;
            }
            message = {
                version : 1,
                request_data : data,
                action : action,
                request_uuid : request_uuid,
                // record the timestamp for when the timeout will occour rather than the time until timeout.
                timeout : parseInt(Math.round(new Date().getTime())) + timeout,
                testing : testing
            };

        } else {
            var response_uuid = BabblingBrook.Library.generateUUID();
            message = {
                response_data : data,
                request_uuid : request_uuid,
                response_uuid : response_uuid
            };
        }
        message = JSON.stringify(message);

        parent_window = window.parent;
        domus_location = BabblingBrook.Library.getParameterByName('domus');
        if (domus_location.length === 0) {
            domus_location = 'domus' + document.domain.slice(subdomain.length);
        }
        domus_location = 'http://' + domus_location;
        parent_window.postMessage(message, domus_location);
    };

    /**
     * The standard error callback that is attatched to all action requests from the domus domain.
     *
     * @param {string} request_uuid The uuid that was sent with the request from the domus domain and is
     *      returned to the domus domain with the response so that it can be identified.
     *      This is not sent by the callback caller, but bound to the callback in this module.
     * @param {string} origin The domain that the request originated from.
     *      This is not sent by the callback caller, but bound to the callback in this module.
     * @param {boolean} https Should the response be sent over https
     *      This is not sent by the callback caller, but bound to the callback in this module.
     * @param {string} The name of the action that was called.
     * @param {object} Any data that was sent with the request.
     * @param {string} error_code The error code that is being reported with this error.
     *      See BabblingBrook.Models.errorTypes for valid values.
     * @param {object} error_data A generic data object for supplemental data specific to the error.
     *      See the protocol definition for documented error objects.
     *
     * @return void
     */
    var returnErrorCallback = function(request_uuid, origin, action, request_data, error_code, error_data) {
        var response_data = {
            error_code : error_code,
            error_data : error_data
        };
        postMessageToDomus(response_data, request_uuid);
        var error_message = 'logging error being sent back to domus.';
        BabblingBrook.Library.logError(error_code, error_data, error_message, subdomain);
    };

    /**
     * The standard success callback that is attatched to all action requests from the domus domain.
     *
     * @param {string} request_uuid The uuid that was sent with the request from the domus domain and is
     *      returned to the domus with the response so that it can be identified.
     *      This is not sent by the callback caller, but bound to the callback in this module.
     * @param {object} response_data A generic data object containg the response to the request.
     *      See the relevant domus test module for valid structures.
     *
     * @return void
     * @refactor rename all uuid refs as uuid.
     * @refactor use console.error rather than throw, as it includes a trace. throw or return in addition
     *      if need to halt execution.
     */
    var returnSuccessCallback = function(request_uuid, response_data) {
        if (typeof response_data !== 'object') {
            console.error('A success callback in the ' + subdomain + ' does not contain a valid data object.');
            console.trace();
            throw 'Thread execution stopped.';
        }
        postMessageToDomus(response_data, request_uuid);
    };

    /*
     * Report an error message for an incoming message.
     *
     * IMPORTANT Do not send error reports back to the domus, this can result in infinite recursion loops.
     * The domus should handle it in a timeout.
     *
     * @param {string} error_message A message about the error.
     * @param {string} event The event object that was generated by the message from the domus domain.
     *
     * @return void
     */
    var reportError = function (error_message, event) {
        console.log(error_message);
        console.log(event);
        console.log(subdomain);
        console.trace();
        throw 'Thread execution stopped.';
    };

    return {

        /**
         * Set up the controller and subdomain for this domain.
         * @param {object} controller_object
         */
        setup : function (a_controller, a_subdomain) {
            controller = a_controller;
            subdomain = a_subdomain;
        },

        /**
         * Receive a message from another domains frame.
         * The object needs to be double wrapped.
         * @param {object} event
         * @param {string} event.data The string passed from the domus domain. When parsed to json it contains.
         * @param {string} [event.data.action] The name of a action to be called on object paramater to validate
         *                                       and utilise the data.
         *                                       All valid actions start with 'action', but this is not included in
         *                                       the action name passed in.
         *                                       This only required for incoming requests for data and not
         *                                       responses to requests.
         * @param {string} [event.data.request_uuid] If this is a new request, then this is the uuid used by the
         *      requesting domain to identify the request.
         *      If this is a response to a request, then this is the uuid that was sent
         *      to the domus domain with the request. This is passed back to this domain with the results
         *      so that they can be matched to the request.
         * @param {string} [event.data.response_uuid] If this is a returning request, then this is the uuid generated
         *      by the other domain.
         */
        receiveMessage : function (event) {
            var fn_name, origin, domus_domain, message;

            if (typeof controller === 'undefined') {
                console.error(
                    'The main document ready function needs to set the controller ' +
                    'object before any messages are received.'
                );
                throw 'Thread execution stopped.';
            }

            origin = event.origin.substring(7);        // remove http://

            // Convert string to JSON
            message = BabblingBrook.Library.parseJSON(event.data);
            if (!message) {
                reportError('Incoming message did not parse to JSON', event);
            }

            // Check the necessary fields are present.
            if (typeof message.action !== 'string' && typeof message.response_uuid !== 'string') {
                reportError('Action name is not defined or response_uuid is missing', event);
            }
            if (typeof message.response_uuid === 'undefined' && typeof message.request_data !== 'object') {
                reportError('Data is malformed or not a JSON object', event);
            }

            if (typeof message.response_uuid === 'undefined'  && typeof message.timeout === 'undefined') {
                reportError('Timeout is missing from message data.', event);
            }
            // Remove one second from the timeout so that errors are reported here before in the requesting domain.
            message.timeout = parseInt(message.timeout) - 1000;

            // Check if the domain is allowed access.
            // Ensure that the only domain that can post to here is the users domus domain.
            domus_domain = 'domus' + document.domain.slice(subdomain.length);
            if (domus_domain !== origin && subdomain !== 'scientia') {
                reportError('Request did not originate in the domus domain. Origin: ' + origin, event);
            }

            if (typeof message.testing !== 'undefined') {
                BabblingBrook.Library.setCookie('testing', message.testing.toString());
            }

            // Ensure there is a valid uuid
            // This is a new request from the domus.
            if (typeof message.action === 'string') {
                // Call the relevent action to process the message.
                fn_name = 'action' + message.action;
                if (typeof controller[fn_name] === 'function') {

                    controller[fn_name](
                        message.request_data,
                        {
                            onSuccess : returnSuccessCallback.bind(null, message.request_uuid),
                            onError : returnErrorCallback.bind(
                                null,
                                message.request_uuid,
                                origin,
                                message.action,
                                message.request_data
                            ),
                            request_domain : origin,
                            timeout : message.timeout
                        }
                    );
                } else {
                    returnErrorCallback(
                        message.request_uuid,
                        origin,
                        message.action,
                        message.request_data,
                        'scientia_action_not_found'
                    );
                }
            // This is a returning request that this domain sent to the domus.
            } else if (typeof message.response_uuid === 'string') {
                var callback_container = callbacks[message.request_uuid];
                if (typeof callback_container !== 'object' || typeof callback_container.success !== 'function') {
                    reportError(
                        'Returning request had a uuid but it is not registered. ' +
                                'It is either malformed or a duplicated that has already returned.',
                        event
                    );
                }
                // Is this an error returning or success?
                if (typeof message.response_data.error_code === 'string') {
                    callback_container.error(message.response_data.error_code, message.response_data.error_data, origin);
                } else {
                    callback_container.success(message.response_data, origin);
                }
                delete callbacks[message.request_uuid];

            // There is an error.
            } else {
                reportError('Request did not have a request_uuid.', event);
            }

        },

        /**
         * Post a message to the domus domain.
         *
         * @param {object} request_data Any data that is required to be sent with the request.
         * @param {string} action The name of the action to call in the object (not including 'action' at the start).
         * @param {function} success The success callback.
         * @param {function} [error] The error callback.
         * @param {number} [timeout = 30000] The number of milliseconds to wait before timing out a request.
         *
         * @return void
         */
        postAMessage : function (request_data, action, success, error, timeout) {
            postMessageToDomus(request_data, undefined, action, success, error, timeout);
        }

    };
}());