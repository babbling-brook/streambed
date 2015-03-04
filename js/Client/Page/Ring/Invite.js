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
 * @fileOverview Code relating to the sending of ring invitations.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Ring !== 'object') {
    BabblingBrook.Client.Page.Ring = {};
}

/**
 *  @namespace Javascript singleton that works with the ring invitation page.
 */
BabblingBrook.Client.Page.Ring.Invite = (function () {
    'use strict';

    /**
     * Setsup the select.js form for sending invitations to users.
     *
     * @returns {undefined}
     */
    var setupUserSearch = function () {
        // Show a user selector when requestd.
        var jq_member_user_search = jQuery('#user_selector');
        jQuery('#select_user').click(function () {

            if (jq_member_user_search.is(':visible')) {
                return false;
            }

            var actions = [
            {
                name : 'Select',
                onClick : function (event, jq_row) {
                    event.preventDefault();
                    var username = jQuery('.username', jq_row).text();
                    var domain = jQuery('.domain', jq_row).text();
                    jQuery('#invite').val(domain + '/' + username);
                    jq_member_user_search.slideUp(250);
                }
            }
            ];
            var member_user_search_table = new BabblingBrook.Client.Component.Selector(
                'user',
                'member_ring_user',
                jq_member_user_search,
                actions
            );
            jq_member_user_search
                .slideDown(250)
                .removeClass('hide');
            return false;

        });
    };

    /**
     * Displays an error in the username.
     *
     * @param string message The error message.
     *
     * @returns {undefined}
     */
    var showError = function(message) {
        jQuery('#user_error')
            .text(message)
            .removeClass('hide');
    };

    /**
     * Callback for when the invitation has been sent.
     *
     * @param {string} to_user
     *
     * @returns {undefined}
     */
    var onInvitationSent = function (to_user, response_data) {
        jQuery('#send_invite').removeClass('button-loading');
        if (typeof response_data.error === 'string') {
            showError(response_data.error);
        }
        jQuery('#invite').val('');
        jQuery('#sent_to_username').text(to_user);
        jQuery('#user_sent_message').removeClass('hide');
    };

    /**
     * Error callback for sending invitations to the server.
     *
     * @returns {undefined}
     */
    var onInvitationSentError = function (message) {
        jQuery('#send_invite').removeClass('button-loading');
        if (typeof message === 'undefined') {
            message = 'There was an error communicating with the server. Please try again.';
        }
        jQuery('#send_invite').removeClass('button-loading');
        showError(message);
    };

    /**
     * Setsup the form to send the invitation.
     *
     * @returns {undefined}
     */
    var onSendInvitation = function () {
        jQuery('#user_sent_message').addClass('hide');
        var to_user = jQuery('#invite').val();
        if (to_user.indexOf('/') === -1) {
            showError('Not a valid username. Ensure both the domain and the username are entered.');
            return;
        }
        jQuery('#send_invite').addClass('button-loading');
        var type = BabblingBrook.Library.getParameterByName('type');
        if (typeof type === 'undefined' || (type !== 'member' && type !== 'admin') ) {
            onInvitationSentError('No valid type present in the url.');
            return;
        }
        var to_user_parts = to_user.split('/');
        jQuery('#user_error')
            .addClass('hide');
        BabblingBrook.Library.post(
            '/' + jQuery('#ring_name').val() + '/ring' + '/sendinvitation',
            {
                to : {
                    username : to_user_parts[1],
                    domain : to_user_parts[0]
                },
                type : type
            },
            onInvitationSent.bind(null, to_user),
            onInvitationSentError
        );
    };

    return {

        /**
         * Constructor for ring invitations.
         */
        construct : function() {

            setupUserSearch();
            jQuery('#send_invite').click(onSendInvitation);
        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Ring.Invite.construct();
});