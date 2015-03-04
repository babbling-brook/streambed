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
 * @fileOverview Code relating to the a member unsubscribing from a ring
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Ring !== 'object') {
    BabblingBrook.Client.Page.Ring = {};
}

/**
 *  @namespace Javascript singleton for the page dealing with a member unsubscribing from a ring.
 */
BabblingBrook.Client.Page.Ring.Leave = (function () {
    'use strict';

    /**
     * Click event for the 'Leave Ring' button.
     *
     * @returns false
     */
    var onLeaveRing = function () {
        BabblingBrook.Library.post(
            window.location + 'confirmed',
            {},
            /**
             * Callback for adding a new item to a list.
             *
             * @param {object} return_data The return data.
             * @param {object} return_data.success Was the process successful.
             * @param {object} return_data.errors A list of errors to display, indexed by error code.
             *
             * @return void
             */
            function(return_data){
                if(typeof return_data.success !== 'boolean') {
                    console.error('error posting leave ring confirmation to server.');
                    return;
                }
                BabblingBrook.Client.Core.UserSetup.removeRingMembership(
                    jQuery('#ring_name').val(), jQuery('#ring_domain').val()
                );

                var ring_name = encodeURIComponent(jQuery('#ring_name').val());
                BabblingBrook.Client.Core.Ajaxurl.redirect(
                    '/' + BabblingBrook.Client.User.username + '/ring/index?leave_ring=' + ring_name
                );
            }
        );
        return false;
    };

    return {

        construct : function() {
            jQuery('#confirm_leave_button').click(onLeaveRing);
        }

    };
})();

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Ring.Leave.construct();
});

