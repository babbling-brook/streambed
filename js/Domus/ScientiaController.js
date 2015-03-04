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
 * @fileOverview A Singleton object to receive messages from scientia domains.
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
 * A Singleton object to receive messages from scientia domains.
 * ********** IMPORTANT These messages come from an insecure domain **********
 * ********** Nothing should be evaled from the messages received   **********
 *
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
 */
BabblingBrook.Domus.ScientiaController = (function () {
    'use strict';
    return {

        /**
         * Receives a message from a scientia iframe that it is ready.
         * @param {object} data
         * @param {object} meta_data See Module definition for more details.
         */
        actionDomainReady : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.domain, 'domain'],
                [data.protocol, 'string']
            ]);
            if (test === false) {
                meta_data.onError('DomainReady_test');
                return;
            }

            var https = false;
            if (data.protocol === 'https:') {
                https = true;
            }

            var domain_id = BabblingBrook.Domus.SendToScientiaFrame.convertDomainToId(data.domain, https);
            BabblingBrook.Domus.Loaded.setScientiaDomainLoaded(domain_id);
            meta_data.onSuccess({});
        },

        /**
         * Recieves an error from the scientia domain ready to be passed to the client domain for reporting.
         *
         * @returns {undefined}
         */
        actionError : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data.error, 'string']
            ], 'Error report from the scientia domain is incorrectly formatted.');
            if (test1 === false) {
                return;
            }

            BabblingBrook.Domus.Interact.postAMessage(
                {
                    domain : 'scientia - ' + meta_data.request_domain,
                    error : request_data.error
                },
                BabblingBrook.Domus.Controller.client_domain,
                'Error',
                function () {},
                function () {
                    console.error(
                        'actionError in the domus domain ScientiaController is ' +
                        'erroring whilst waiting for the client to repsond.'
                    );
                },
                BabblingBrook.Domus.Controller.client_https
            );
        }
    };

}());