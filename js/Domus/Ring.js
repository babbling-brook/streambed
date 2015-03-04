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
 * @fileOverview Functions related to rings and interaction with the ring domain.
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
 * @namespace Functions related to rings and interaction with the ring domain.
 * @package JS_Domus
 */
BabblingBrook.Domus.Ring = (function () {
    'use strict';
    /**
     * @type {string[]} ring_rhythms Contains the rhythm urls to process. First in - first processed.
     * @type {string} ring_rhythms.url The rhythm url.
     * @type {string} ring_rhythms.type The type of url. Valid values are 'member' and 'admin'.
     * @type {string} ring_rhythms.username The username of the ring that uses this Rhythm.
     * @type {string} ring_rhythms.domain The domain of the ring that uses this Rhythm.
     * @type {string} ring_rhythms.ring_password This users password for accessing this ring.
     */
    var ring_rhythms = [];

    /**
     * @type {} The index of ring_rhythms rhythm that is currently being processed.
     */
    var process_id = -1;        // start with -1 so the first increment takes us to 0.

    /**
     * @type {object} current_rhythm Stores details of the rhythm that is currently running.
     * @type {string} current_rhythm.domain The domain that owns the Rhythm.
     * @type {string} current_rhythm.username The username of the owner of the Rhythm.
     * @type {string} current_rhythm.name The name of the Rhythm.
     * @type {string} current_rhythm.version The version of the Rhythm.
     * @type {string} current_rhythm.dateCreated
     * @type {string} current_rhythm.status Is the Rhythm public, private or deprecated.
     * @type {string} current_rhythm.description The description of this Rhythm.
     * @type {string} current_rhythm.js The Rhythm code in a string.
     */
    var current_rhythm;

    /**
     * @type {object} A user object representing the ring that currently has a rhythm running.
     */
    var current_ring;

    var iframe_inserted = false;

    /**
     * Start processing a ring Rhythm
     *
     * @param {object} rhythm The ring Rhythm data to proccess. See current_rhythm for a full definition.
     *
     * @return void
     */
    var startRing = function (ring_rhtyhm_data, rhythm) {
        current_ring = undefined;
        current_rhythm = rhythm;
        BabblingBrook.Domus.Interact.postAMessage(
            {
                rhythm : rhythm,
                user : {
                    username : BabblingBrook.Domus.User.username,
                    domain : BabblingBrook.Domus.User.domain
                },
                client_domain : 'rings need to be passed from the client for this to be passed.'
            },
            'ring',
            'RunRhythm',
            function () {
                current_ring = ring_rhtyhm_data;
            },
            function (error_code, error_data) {
                console.error({
                    error_code : error_code,
                    error_data : error_data
                });
                console.error('There was a problem processing a ring Rhythm.');
            }
        );
    };

    /**
     * Report an error when fetching a rings detials.
     *
     * @param {string} ring_url The url of the ring that is erroring.
     *
     * @return void
     */
    var ringError = function (ring_url) {
        console.error('Fetching a rings details failed for : ' + ring_url);
    };

    /**
     * Insert the ring rhythm iframe into the DOM.
     *
     * @returns {undefined}
     */
    var insertIframe = function () {
        console.log('creating ring rhythm iframe.');
        if (iframe_inserted === false) {
            var main_domain = window.location.host.substring(6);
            jQuery('body').append(' ' +
                '<iframe style="display:none" id="ring" name="ring_window" ' +
                        'src="http://ring.' + main_domain + '">' +
                '</iframe>'
            );
            iframe_inserted = true;        }
    };

    /**
     * Public methods
     */
    return {

        getCurrent : function () {
            return current_rhythm;
        },

        /**
         * Setup the order in which to execute ring rhythms.
         */
        setup : function () {
            jQuery.each(BabblingBrook.Domus.User.member_rings, function (i, ring) {
                if (ring.url.length > 0) {
                    ring_rhythms.push({
                        url : ring.url,
                        type : 'member',
                        username : ring.username,
                        domain : ring.domain,
                        ring_password : ring.password
                    });
                }
            });
            jQuery.each(BabblingBrook.Domus.User.admin_rings, function (i, ring) {
                if (ring.url.length > 0) {
                    ring_rhythms.push({
                        url : ring.url,
                        type : 'admin',
                        username : ring.username,
                        domain : ring.domain,
                        ring_password : ring.password
                    });
                }
            });
            BabblingBrook.Domus.Ring.cycle();
        },

        /**
         * Cycles through a users rings, fetches their Rhythms and sets them running.
         */
        cycle : function () {
            if (ring_rhythms.length === 0) {
                return;
            }

            /**
             * Wait a while before running - to prevent the system being hogged.
             */
            setTimeout(function () {
                insertIframe();
                process_id++;
                var url = BabblingBrook.Library.extractDomain(ring_rhythms[process_id].url);
                BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                    url,
                    'FetchRhythm',
                    {},
                    false,
                    startRing.bind(ring_rhythms[process_id]),
                    ringError.bind(null, url)
                );

            }, BabblingBrook.Domus.User.ring_pause);

        },

        /**
         * Store the results of the currently running ring rhythm.
         *
         * @param {object} results The results generated by the ring Rhythm.
         * @param {function} successCallback Used to inform the ring domain that the results have been stored.
         * @param {function} errorCallback USed to send an error back to the ring domain.
         *
         * @returns {void}
         */
        storeResults : function (results, successCallback, errorCallback) {

            var data = {
                rhythm_domain : current_rhythm.domain,
                rhythm_username : current_rhythm.username,
                rhythm_name : current_rhythm.name,
                rhythm_version : current_rhythm.version,
                computed_data : data.computed_data,
                ring_member_username : BabblingBrook.Domus.User.username,
                ring_member_domain : BabblingBrook.Domus.User.domain,
                username : ring_rhythms[process_id].username,
                rhythm_type : ring_rhythms[process_id].type,
                ring_password : ring_rhythms[process_id].ring_password
            };

            var domain = ring_rhythms[process_id].domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                domain,
                'StoreRingResults',
                data,
                true,
                successCallback,
                errorCallback.bind(null, 'StoreRingResults_failed')
            );

            BabblingBrook.Domus.Ring.cycle();
        },

        /**
         * Returns a users password for a ring.
         *
         * @param {string} domain The domain of the ring.
         * @param {string} username The rings username.
         * @param {string} type Is this user an 'admin' or 'member' of this ring.
         *
         * @return {string} the password.
         */
        getPassword : function (domain, username, type) {
            var rings;
            if (type === 'admin') {
                rings = BabblingBrook.Domus.User.admin_rings;
            } else if (type === 'member') {
                rings = BabblingBrook.Domus.User.member_rings;
            }
            var password;
            jQuery.each(rings, function (i, ring) {
                if (ring.domain === domain && ring.username === username) {
                    password = ring.password;
                    return false;    // Exit the jQuery.each function.
                }
                return true;        // Continue the jQuery.each function.
            });
            return password;
        },

        /**
         * Fetches the url of rhythm of the currently running ring.
         *
         * @returns {string}
         */
        getCurentRhythmUrl : function () {
            var url = BabblingBrook.Library.makeRhythmUrl(current_rhythm, 'storedata');
            return url;
        },

        /**
         * Appends a new ring to the ring membership data. Called after a new ring has been joined.
         *
         * @param {object} ring_data The ring membership data.
         * @param {object} ring_data.password The users password for this ring.
         * @param {object} ring_data.domain The rings domain.
         * @param {object} ring_data.username The rings username.
         *
         * @returns {undefined}
         */
        appendNewRingMember : function (ring_data) {
            BabblingBrook.Domus.User.member_rings.push(ring_data);
        },

        /**
         * Fetches the details of the ring rhythm that is currently running.
         *
         * @returns {object} A ring_rhythm object.
         */
        getCurrentRing : function () {
            return current_ring;
        }

    };
}());