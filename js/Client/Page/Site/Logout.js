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
 * @fileOverview Logout page functionality.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Site !== 'object') {
    BabblingBrook.Client.Page.Site = {};
}

/**
 * Logs a user out of this client site. Logs out all other windows/tabs that are open.
 */
BabblingBrook.Client.Page.Site.Logout = (function () {
    'use strict';

    return {

        /**
         * Call Server method to log out remote sites.
         */
        construct : function () {
            // Log off remote sites.
            jQuery('#ajax_load').addClass('ajax-loading');

            BabblingBrook.Client.Core.Loaded.onDomusLoaded(function() {
                BabblingBrook.Library.post(
                    '/site/locallogout',
                    {},
                    /**
                        * Success callback for local logout
                        *
                        * @param {object} returned_data
                        *
                        * @return void
                        */
                    function (returned_data) {
                        if (returned_data.success === true) {

                            jQuery('#logout_message').html('You have been logged out locally. ' +
                                'Now logging you out of your data store...');

                            // Any other open tags/ windows also need to be logged out
                            // and the domus domain iframe removed.
                            // Do this by sending a message to the domus domain,
                            // which will then send a message to
                            // all windows in its session.
                            var failedCallback = function () {
                                var logoutall_url = 'http://' + BabblingBrook.Client.User.domain + '/site/logoutall';
                                var jq_error_template = jQuery('#logout_failed_template');
                                jQuery('a', jq_error_template).attr('href', logoutall_url);
                                jQuery('#logout_message').html(jq_error_template.html());
                                // Capture the logout all click and redirect. This avoids problems with
                                // Ajax url interfering with the link if the problem was local.
                                jQuery('#logoutall').click(function () {
                                    window.location = logoutall_url;
                                    return false;
                                });

                                jQuery('#ajax_load').removeClass('ajax-loading');
                            };

                            /**
                             * This only needs to do anything if the success message is false.
                             * This window/tab will be called independantly by the domus domain to
                             * ask it to redirect to the homepage.                                 *
                             */
                            var successCallback = function (logout_data) {
                                // Finish the local logout.
                                if(logout_data.success === false) {
                                    failedCallback();
                                }
                            };

                            BabblingBrook.Client.Core.Interact.postAMessage(
                                {},
                                'Logout',
                                successCallback,
                                failedCallback
                            );

                        } else {
                            jQuery('#logout_message').append('Error. Failed to log out locally.');
                            jQuery('#ajax_load').removeClass('ajax-loading');
                        }
                    }
                );

            });
        }
    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Site.Logout.construct();
});