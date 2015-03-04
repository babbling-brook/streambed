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
 * @fileOverview Initialisation of a domus domain users data.
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
 *  A global object to indicate if all dependant data has loaded
 */
BabblingBrook.Domus.Loaded = (function () {
    'use strict';

    var deferred_filter = jQuery.Deferred();

    var deferred_kindred_data = jQuery.Deferred();

    var deferred_kindred_iframe = jQuery.Deferred();

    var deferred_ring_iframe = jQuery.Deferred();

    var deferred_suggestion_iframe = jQuery.Deferred();

    var deferred_client = jQuery.Deferred();

    var deferred_user = jQuery.Deferred();

    var deferred_site_access =  jQuery.Deferred();

    var deferred_scientia_domains = {};

    var user_loaded = false;

    var site_access_loaded = false;

    return {

        /**
         * @type Array A list of scientia domain iframes that have been loaded.
         */
        remote_domains : [],

        /**
         * Checks if the user and site_access data have loaded.
         *
         * @param {function} The function to call when the kindred data has loaded.
         *
         * @return boolean
         */
        ifUserAndAccessLoaded : function() {
            if (user_loaded === true && site_access_loaded === true) {
                return true;
            } else {
                return false;
            }
        },

        /**
         * Fires any waiting callbacks when the kindred data has loaded.
         *
         * @param {function} The function to call when the kindred data has loaded.
         *
         * @return boolean
         */
        onKindredDataLoaded : function (onDone) {
            deferred_kindred_data.done(onDone);
        },

        /**
         * Fires any waiting callbacks when the kindred iframe has loaded.
         *
         * @param {function} The function to call when the kindred data has loaded.
         *
         * @return boolean
         */
        onKindredIframeLoaded : function (onDone) {
            deferred_kindred_iframe.done(onDone);
        },

        /**
         * Fires any waiting callbacks when the filter iframe has loaded.
         *
         * @param {function} The function to call when the filter iframe has loaded.
         *
         * @return boolean
         */
        onFilterLoaded : function (onDone) {
            deferred_filter.done(onDone);
        },

        /**
         * Fires any waiting callbacks when the ring iframe has loaded.
         *
         * @param {function} The function to call when the ring iframe has loaded.
         *
         * @return boolean
         */
        onRingIframeLoaded : function (onDone) {
            deferred_ring_iframe.done(onDone);
        },

        /**
         * Fires any waiting callbacks when the suggestion iframe has loaded.
         *
         * @param {function} The function to call when the suggestion iframe has loaded.
         *
         * @return boolean
         */
        onSuggestionIframeLoaded : function (onDone) {
            deferred_suggestion_iframe.done(onDone);
        },

        /**
         * Fires any waiting callbacks when the client domain has loaded.
         *
         * @param {function} The function to call when the client domain has loaded.
         *
         * @return boolean
         */
        onClientLoaded : function (onDone) {
            deferred_client.done(onDone);
        },

        /**
         * Fires any waiting callbacks when the user data has loaded.
         *
         * @param {function} The function to call when the user data has loaded.
         *
         * @return boolean
         */
        onUserLoaded : function (onDone) {
            deferred_user.done(onDone);
        },

        /**
         * Fires any waiting callbacks when the site_access data has loaded.
         *
         * @param {function} The function to call when the site_access data has loaded.
         *
         * @return boolean
         */
        onSiteAccessLoaded : function (onDone) {
            deferred_site_access.done(onDone);
        },

        /**
         * Fires any waiting callbacks when the site_access data has loaded.
         *
         * @param {function} The function to call when the site_access data has loaded.
         *
         * @return boolean
         */
        onScientiaDomainLoaded : function (domain_id, onDone) {
            if (typeof deferred_scientia_domains[domain_id] === 'undefined') {
                deferred_scientia_domains[domain_id] = jQuery.Deferred();
            }
            deferred_scientia_domains[domain_id].done(onDone);
        },

        /**
         * Sets the client as haaving loaded.
         *
         * @returns {undefined}
         */
        setFilterLoaded : function () {
            deferred_filter.resolve();
        },

        /**
         * Sets the kindred data as having loaded.
         *
         * @returns {undefined}
         */
        setKindredDataLoaded : function () {
            deferred_kindred_data.resolve();
        },

        /**
         * Sets the kindred iframe as having loaded.
         *
         * @returns {undefined}
         */
        setKindredIframeLoaded : function () {
            deferred_kindred_iframe.resolve();
        },

        /**
         * Sets the ring iframe as having loaded.
         *
         * @returns {undefined}
         */
        setRingIframeLoaded : function () {
            deferred_ring_iframe.resolve();
        },

        /**
         * Sets the suggestion iframe as having loaded.
         *
         * @returns {undefined}
         */
        setSuggestionIframeLoaded : function () {
            deferred_suggestion_iframe.resolve();
        },

        /**
         * Sets the client domain as having loaded.
         *
         * @returns {undefined}
         */
        setClientLoaded : function () {
            deferred_client.resolve();
        },

        /**
         * Sets the client domain as having loaded.
         *
         * @returns {undefined}
         */
        setUserLoaded : function () {
            user_loaded = true;
            deferred_user.resolve();
        },

        /**
         * Sets the site_access data as having loaded.
         *
         * @returns {undefined}
         */
        setSiteAccessLoaded : function () {
            site_access_loaded = true;
            deferred_site_access.resolve();
        },

        /**
         * Sets the a scientia domain as having loaded.
         *
         * @returns {undefined}
         */
        setScientiaDomainLoaded : function (domain_id) {
            if (typeof deferred_scientia_domains[domain_id] === 'undefined') {
                deferred_scientia_domains[domain_id] = jQuery.Deferred();
            }
            deferred_scientia_domains[domain_id].resolve();
        }



    };
}());

