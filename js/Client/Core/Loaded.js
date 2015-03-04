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
 * @fileOverview Tracks which components have loaded.
 *
 * @author Sky Wickenden
 */

/**
 * @namespace A global object to indicate if all dependant data has loaded.
 * @package JS_Client
 */
BabblingBrook.Client.Core.Loaded = (function () {
    'use strict';

    var deferred_kindred = jQuery.Deferred();

    var deferred_client = jQuery.Deferred();

    var deferred_user = jQuery.Deferred();

    var deferred_domus = jQuery.Deferred();

    var deferred_stream_subscriptions = jQuery.Deferred();

    var deferred_stream_subscriptions_page = jQuery.Deferred();

    var deferred_profile = jQuery.Deferred();

    var deferred_display_tree = jQuery.Deferred();

    var deferred_edit_profile = jQuery.Deferred();

    var deferred_create_stream = jQuery.Deferred();

    var deferred_ring_admin = jQuery.Deferred();

    var deferred_rhythm_list = jQuery.Deferred();

    var deferred_settings = jQuery.Deferred();

    return {

        /**
         * Checks if the kindred data has loaded.
         *
         * @param {function} The function to call when the kindred data has loaded.
         *
         * @return boolean
         */
        onKindredLoaded : function (onDone) {
            deferred_kindred.done(onDone);
        },

        /**
         * Runs the passed in function when the client user data has loaded.
         *
         * @param {function} The function to call when the client user data has loaded.
         *
         * @return boolean
         */
        onClientLoaded : function (onDone) {
            deferred_client.done(onDone);
        },

        /**
         * Runs the passed in function when the domus domain has loaded.
         *
         * @param {function} The function to call when the domas domain has loaded.
         *
         * @return boolean
         */
        onDomusLoaded : function (onDone) {
            deferred_domus.done(onDone);
        },

        /**
         * Runs the passed in function when the stream subscriptions have loaded.
         *
         * @param {function} The function to call when the stream subscriptions have loaded.
         *
         * @return boolean
         */
        onStreamSubscriptionsLoaded : function (onDone) {
            deferred_stream_subscriptions.done(onDone);
        },

        /**
         * Runs the passed in function when the stream subscriptions page has loaded.
         *
         * @param {function} The function to call when the stream subscriptions page has loaded.
         *
         * @return boolean
         */
        onStreamSubscriptionsPageLoaded : function (onDone) {
            deferred_stream_subscriptions_page.done(onDone);
        },

        /**
         * Runs the passed in function when the profile page has loaded.
         *
         * @param {function} The function to call when the page has loaded.
         *
         * @return boolean
         */
        onProfileLoaded : function (onDone) {
            deferred_profile.done(onDone);
        },

        /**
         * Runs the passed in function when the edit profile page has loaded.
         *
         * @param {function} The function to call when the page has loaded.
         *
         * @return boolean
         */
        onEditProfileLoaded : function (onDone) {
            deferred_edit_profile.done(onDone);
        },

        /**
         * Runs the passed in function when the display tree page has loaded.
         *
         * @param {function} The function to call when the page has loaded.
         *
         * @return boolean
         */
        onPostWithTreeLoaded : function (onDone) {
            deferred_display_tree.done(onDone);
        },

        /**
         * Runs the passed in function when the create stream page has loaded.
         *
         * @param {function} The function to call when the page has loaded.
         *
         * @return boolean
         */
        onCreateStreamLoaded : function (onDone) {
            deferred_create_stream.done(onDone);
        },

        /**
         * Runs the passed in function when the ring admin page has loaded.
         *
         * @param {function} The function to call when the ring admin page has loaded.
         *
         * @return boolean
         */
        onRingAdminLoaded : function (onDone) {
            deferred_ring_admin.done(onDone);
        },

        /**
         * Runs the passed in function when the rhythm list page has loaded.
         *
         * @param {function} The function to call when the ring admin page has loaded.
         *
         * @return boolean
         */
        onRhythmListLoaded : function (onDone) {
            deferred_rhythm_list.done(onDone);
        },

        /**
         * Runs the passed in function when the settings page has loaded.
         *
         * @param {function} The function to call when the settings page has loaded.
         *
         * @return boolean
         */
        onSettingsLoaded : function (onDone) {
            deferred_settings.done(onDone);
        },

        /**
         * Runs the passed in function when the users config data has loaded.
         *
         * @param {function} The function to call when the data has loaded.
         *
         * @return boolean
         */
        onUserLoaded : function (onDone) {
            deferred_user.done(onDone);
        },

        /**
         * Sets the user data as loaded.
         *
         * @returns {undefined}
         */
        setUserLoaded : function () {
            deferred_user.resolve();
        },

        /**
         * Sets the domus domain as loaded.
         *
         * @returns {undefined}
         */
        setDomusLoaded : function () {
            deferred_domus.resolve();
        },

        /**
         * Sets the client site as loaded.
         *
         * @returns {undefined}
         */
        setClientLoaded : function () {
            deferred_client.resolve();
        },

        /**
         * Sets the kindred data as loaded.
         *
         * @returns {undefined}
         */
        setKindredLoaded : function () {
            deferred_kindred.resolve();
        },

        /**
         * Sets the stream subscriptions as having loaded.
         *
         * @returns {undefined}
         */
        setStreamSubscriptionsLoaded : function () {
            deferred_stream_subscriptions.resolve();
        },

        /**
         * Sets the stream subscriptions page as loaded.
         *
         * @returns {undefined}
         */
        setStreamSubscriptionsPageLoaded : function () {
            deferred_stream_subscriptions_page.resolve();
        },

        /**
         * Sets the profile page as loaded.
         *
         * @returns {undefined}
         */
        setProfileLoaded : function () {
            deferred_profile.resolve();
        },

        /**
         * Sets the edit profile page as loaded.
         *
         * @returns {undefined}
         */
        setEditProfileLoaded : function () {
            deferred_edit_profile.resolve();
        },

        /**
         * Sets the display tree class as loaded.
         *
         * @returns {undefined}
         */
        setPostWithTreeLoaded : function () {
            deferred_display_tree.resolve();
        },

        /**
         * Sets the create stream class as loaded.
         *
         * @returns {undefined}
         */
        setCreateStreamLoaded : function () {
            deferred_create_stream.resolve();
        },

        /**
         * Sets the ring admin page as loaded.
         *
         * @returns {undefined}
         */
        setRingAdminLoaded : function () {
            deferred_ring_admin.resolve();
        },

        /**
         * Sets the rhythm list page as loaded.
         *
         * @returns {undefined}
         */
        setRhythmListLoaded : function () {
            deferred_rhythm_list.resolve();
        },

        /**
         * Sets the settings page as loaded.
         *
         * @returns {undefined}
         */
        setSettingsLoaded : function () {
            deferred_settings.resolve();
        }


    };

}());