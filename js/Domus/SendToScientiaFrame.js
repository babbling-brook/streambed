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
 * A singleton class for communicating with scientia domains.
 * A new iframe is created with a https location of the domain to comunicate with.
 * This domus is then passed the data using HTML5 and then posts it back to itself over HTTPS.
 * On postback a confirmation message is sent back and the iframe is destroyed.
 */
BabblingBrook.Domus.SendToScientiaFrame = (function () {
    'use strict';
    /**
     * Create an iframe to send information securely accross domains.
     * @param {string} domain The domain to create a scientia iframe for.
     * @param {string} domain_id The domain without any illeagal characters for a DOM element ID.
     * @param {boolean} https Should the iframe use an SSL.
     */
    var create = function (domain, domain_id, https) {


/************    !! IMPORTANT  !! ***************
 * This is a temporary insecure hack to prevent use of https.
 ************************************************
 */
        https = false;

        var protocol = 'http://';
        if (https) {
            protocol = 'https://';
        }

        // Check if this domain already has a scientia iframe.
        if (jQuery('iframe#' + domain_id).length === 0) {
            var rnd = Math.floor(Math.random() * 100000);
            jQuery('body').append(' '
                + '<iframe name="scientia_window" src="'
                +       protocol + domain + '/?' + rnd + '&domus=' + window.location.host + '" id="' + domain_id + '">'
                + '</iframe>');
        }
    };

    /**
     * Constructor. Include public methods here.
     */
    return {

        /**
         * Create an iframe to send information accross domains.
         * @param {string} domain The domain to create a scientia iframe for.
         * @param {string} action The name of the function to call in the receiving iframe.
         *                               Only functions on the list of permitted functions can be called.
         * @param {object} data The data that is being passed to the scientia iframe.
         * @param {boolean} https Should the iframe use an SSL.
         * @param {function} success Callback for successful return of requested data.
         * @param {function} [error] Callback for failure to return data.
         * @param {function} [timeout] A millisecnd timestamp for whne this request should timeout.
         */
        sendMessage : function (domain, action, data, https, success, error, timeout) {

/************    !! IMPORTANT  !! ***************
 * This is a temporary insecure hack to prevent use of https.
 ************************************************
 */
        https = false;
        
            if (typeof domain !== 'string') {
                console.error(domain, action, data, https);
                console.error('domain invlaid when trying to send to scientia domain.');
                throw 'Thread execution stopped.';
            }

            if (typeof error !== 'function') {
                error = function () {
                    console.error(domain, action, data, https);
                    console.error('error with loading scientia iframe - no error function connected.');
                    throw 'Thread execution stopped.';
                };
            }

            domain = 'scientia.' + domain;
            var domain_id = BabblingBrook.Domus.SendToScientiaFrame.convertDomainToId(domain, https);
            create(domain, domain_id, https);
            // Timeout is to ensure that the remote domain has loaded.
            BabblingBrook.Domus.Loaded.onScientiaDomainLoaded(domain_id, function () {
                BabblingBrook.Domus.Interact.postAMessage(
                    data,
                    domain,
                    action,
                    success,
                    error,
                    https,
                    timeout
                );
            });
        },

        /**
         * Convert a domain name to a valid css ID.
         * @param {string} domain
         * @param {boolean} https Is the iframe ussing https.
         * @return {string}
         */
        convertDomainToId : function (domain, https) {
            // replace all invalid characters with a dash
            /*jslint regexp: true*/    // This use of ^ is ok as it used to remove odd characters.
            domain = domain.replace(/[^a-zA-Z0-9]/g, '-');
            /*jslint regexp: false*/
            if (https) {
                domain = 'https-' + domain;
            } else {
                domain = 'http-' + domain;
            }
            return domain;
        }
    };
}());
