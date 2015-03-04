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
 * @fileOverview Setsup the users client data.
 * @author Sky Wickenden
 */

/**
 * @namespace Initialise the BabblingBrook.Client.User module.
 * This is loaded from the users data store.
 *
 * @package JS_Client
 */
BabblingBrook.Client.Core.UserSetup = (function () {
    'use strict';

    var already_tried_to_setup = false;

    var fetchKindred = function() {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {},
            'GetKindred',
            function (data) {                        // Success.
                if (typeof data.kindred === 'undefined') {
                    BabblingBrook.Client.Component.Messages.addMessage({
                        type : 'error',
                        message : 'There was a problem loading your kindred data.',
                    });
                    return;
                }
                BabblingBrook.Client.User.kindred = data.kindred;
                BabblingBrook.Client.Core.Loaded.setKindredLoaded();
            },
            function (error_code, error_data) {    // Error.
                console.error('GetKindred error');
            }
        );
    };

    /**
     * Displays a confirmation message when the user requests to reset their account.
     *
     * @returns {undefined}
     */
    var onRebootAccountClicked = function () {
        var message = jQuery('#client_data_error_confirm_template').text();
        if (confirm(message) === true) {
            BabblingBrook.Client.Core.UserSetup.setupAccount();
        }
    }

    /**
     * Displays an error message when a users config data can not be retrieved.
     *
     * @returns {undefined}
     */
    var displayErrorLoadingUserData = function () {
        var buttons = [
        {
            name : 'Reset Account',
            callback : onRebootAccountClicked
        }
        ];

        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : jQuery('#client_data_error_template').html(),
            buttons : buttons
        });
    };

    /**
     * Display an error if a new default config option is not added correctly.
     *
     * @returns {undefined}
     */
    var onErrorAddingNewConfigOption = function () {
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : jQuery('#client_data_new_option_template').html(),
        });
    }

    /**
     * Callback for when a users config data has returned.
     *
     * @param {object} response_data The data returned from the domus domain.
     * @param {boolean} response_data.success Was the request successful.
     * @param {string} [response_data.data] The users config data.
     * @param {string} [response_data.error] An error message.
     *
     * @returns {undefined}
     */
    var onUserDataFetched = function (response_data) {
        if (response_data.success === false || Object.keys(response_data.data).length === 0) {
            if (already_tried_to_setup === true) {
                throw 'Stuck in a loop failing to setup the users config data.';
            }
            already_tried_to_setup = true;
            BabblingBrook.Client.Core.UserSetup.setupAccount();
        } else {
            jQuery.each(response_data.data.user_config, function(i, row){
                BabblingBrook.Client.User.Config[i] = row;
            });

            jQuery.each(BabblingBrook.Client.DefaultConfig, function(i, default_row){
                var found = false;
                jQuery.each(response_data.data.user_config, function(j, row){
                    if (i === j) {
                        found = true;
                    }
                });
                if (found === false) {
                    BabblingBrook.Client.User[i] = default_row;
                    BabblingBrook.Client.Core.Interact.postAMessage(
                        {
                            key : 'user_config.' + i,
                            data : default_row//JSON.stringify(default_row)
                        },
                        'StoreClientUserData',
                        function(){},
                        onErrorAddingNewConfigOption
                    );
                }
            });
            BabblingBrook.Client.Core.Loaded.setUserLoaded();

        }

    };

    /**
     * Fetch a users config data from their data store.
     *
     * @return {undefined}
     */
    var fetchUsersConfigData = function () {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                key : 'user_config'
            },
            'FetchClientUserData',
            onUserDataFetched,
            displayErrorLoadingUserData
        );
    };

    /**
     * Sets up the users stream subscriptions once the config data has loaded.
     *
     * @returns {undefined}
     */
    var setupStreamSubscriptions = function() {
        if (jQuery.inArray('all', BabblingBrook.Client.ClientConfig.active_components) !== -1
            || jQuery.inArray('StreamNav', BabblingBrook.Client.ClientConfig.active_components) !== -1
        ) {
            BabblingBrook.Client.Core.StreamSubscriptions.fetchStreamSubscriptions(
                BabblingBrook.Client.Component.StreamNav.setup
            );
        }
    };

    return {
        /**
         * Initialise the BabblingBrook.Client.Core.UserSetup. Normalises url data.
         */
        construct : function () {
            fetchUsersConfigData();
            setupStreamSubscriptions();
            BabblingBrook.Client.Core.Loaded.onClientLoaded(fetchKindred);
            BabblingBrook.Client.Core.Loaded.onUserLoaded();
        },

        setupAccount : function () {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    key : 'user_config',
                    data : BabblingBrook.Client.DefaultConfig
                },
                'StoreClientUserData',
                fetchUsersConfigData,
                displayErrorLoadingUserData
            );
        },

        /**
         * Removes a users membership subscription to a ring.
         *
         * @param string ring_name The name of the ring.
         * @param string ring_domain The domain that hosts the ring.
         * @returns void
         */
        removeRingMembership : function(ring_name,ring_domain) {
            var rings = BabblingBrook.Client.User.Rings;
            var ring_length = rings.length;
            for(var i = 0; i < ring_length; i++) {
                if (rings[i].name === ring_name && rings[i].domain === ring_domain && rings[i].member === '1') {
                    rings.splice(i, 1);
                    return;
                }
            }
        },

        /**
         * Reloads the BabblingBrook.Client.User data.
         *
         * @param function [onLoaded] Callback to be run once the data has reloaded.
         *
         * @returns void
         */
        reloadUserData : function(onLoaded) {
            if (typeof onLoaded !== 'function') {
                onLoaded = function () {};
            }

            jQuery.getJSON(
                '/data/clientuser',
                function (data) {
                    if (data !== null) {
                        BabblingBrook.Client.User = data;
                        onLoaded();
                    }
                }
            );
        },

        /**
         * Fetches the users kindred tag data from the server.
         *
         * @param {function} onLoaded This is called with the kindred tags once they have loaded.
         *
         * @returns {undefined}
         */
        fetchKindredTagsData : function (onLoaded) {
            if (typeof BabblingBrook.Client.User.kindred_tags !== 'undefined') {
                onLoaded(BabblingBrook.Client.User.kindred_tags);
            } else {
                BabblingBrook.Client.Core.Interact.postAMessage(
                    {},
                    'GetKindredTags',
                    function (tag_data) {                        // Success.
                        BabblingBrook.Client.User.kindred_tags = tag_data;
                        onLoaded(BabblingBrook.Client.User.kindred_tags);
                    },
                    function (error_code, error_data) {    // Error.
                        console.error('GetKindred error');
                    }
                );
            }
        }

    };
}());
