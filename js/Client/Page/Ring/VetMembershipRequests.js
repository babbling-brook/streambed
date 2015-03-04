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
 * @fileOverview Code relating to the banning of ring users.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Ring !== 'object') {
    BabblingBrook.Client.Page.Ring = {};
}

/**
 *  @namespace Javascript singleton that works with the ring admin page.
 */
BabblingBrook.Client.Page.Ring.VetRingMembershipRequests = (function () {
    'use strict';

    var ring_username;

    var ring_domain;

    /**
     * Callback for when a ring membership has been accepted.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     * @param {object} response_data The response data returned fom the server. See DomusDataTests for details.
     *
     * @returns {undefined}
     */
    var onRingMembershipRequestAccepted = function (jq_row, domain, username, response_data) {
        if (response_data.success === false) {
            onRingMembershipRequestAcceptedError(jq_row, domain, username);
            return;
        }

        jq_row.slideUp(250, function () {
            jq_row.remove();
        });
    };

    /**
     * Callback for when an error arose when a  ring membership was beeing accepted.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     *
     * @returns {undefined}
     */
    var onRingMembershipRequestAcceptedError = function (jq_row, domain, username) {
        showErrorMessage(jq_row, domain, username, '#on_ring_membership_request_accepted_error_template');
    };

    /**
     * Accept a user as a member of this ring.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     *
     * @returns {undefined}
     */
    var acceptUser = function (domain, username, jq_row) {
        jq_row.addClass('row-loading');
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                user : {
                    domain : domain,
                    username : username
                },
                ring : {
                    domain : ring_domain,
                    username : ring_username
                }
            },
            'AcceptRingMembershipRequest',
            onRingMembershipRequestAccepted.bind(null, jq_row, domain, username),
            onRingMembershipRequestAcceptedError.bind(null, jq_row, domain, username)
        );
    }

    /**
     * Callback for when a ring membership has been declined.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     * @param {object} response_data The response data returned fom the server. See DomusDataTests for details.
     *
     * @returns {undefined}
     */
    var onRingMembershipRequestDeclined = function (jq_row, domain, username, response_data) {
        if (response_data.success === false) {
            onRingMembershipRequestAcceptedError(jq_row, domain, username);
            return;
        }

        jq_row.slideUp(250, function () {
            jq_row.remove();
        });
    };

    /**
     * Callback for when an error arose when a  ring membership was beeing declined.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     *
     * @returns {undefined}
     */
    var onRingMembershipRequestDeclinedError = function (jq_row, domain, username) {
        showErrorMessage(jq_row, domain, username, '#on_ring_membership_request_declined_error_template');
    };

    /**
     * Shows an error message for an action on the selector.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     * @param {string} error_template jQuery selector for the error template to use.
     *
     * @returns {undefined}
     */
    var showErrorMessage = function (jq_row, domain, username, error_template) {
        jq_row
            .removeClass('row-loading')
            .addClass('row-error');
        var jq_message = jQuery(error_template).clone();
        jQuery('.ring-request-user', jq_message).text(username + '@' + domain);
        jQuery('.ring-request-ring', jq_message).text(ring_username + '@' + ring_domain);
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : jq_message.text()
        });
    };

    /**
     * Decline a user as a member of this ring.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     *
     * @returns {undefined}
     */
    var declineUser = function (domain, username, jq_row) {
        jq_row.addClass('row-loading');
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                user : {
                    domain : domain,
                    username : username
                },
                ring : {
                    domain : ring_domain,
                    username : ring_username
                }
            },
            'DeclineRingMembershipRequest',
            onRingMembershipRequestDeclined.bind(null, jq_row, domain, username),
            onRingMembershipRequestDeclinedError.bind(null, jq_row, domain, username)
        );
    };

    /**
     * Ban a member of this ring.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     *
     * @returns {undefined}
     */
    var banUser = function (domain, username, jq_row) {
        jq_row.addClass('row-loading');
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                user : {
                    domain : domain,
                    username : username
                },
                ring : {
                    domain : ring_domain,
                    username : ring_username
                }
            },
            'BanRingMember',
            onRingUserBanned.bind(null, domain, username, jq_row),
            onRingMemberBannedError.bind(null, domain, username, jq_row)
        );
    };

    /**
     * Callback for when a ring membership has been declined.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     * @param {object} response_data The response data returned fom the server. See DomusDataTests for details.
     *
     * @returns {undefined}
     */
    var onRingUserBanned = function (domain, username, jq_row, response_data) {
        if (response_data.success === false) {
            onRingMemberBannedError(domain, username, jq_row);
            return;
        }

        jq_row.slideUp(250, function () {
            jq_row.remove();
        });
    };

    /**
     * Callback for when an error arose when a  ring membership was beeing declined.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     *
     * @returns {undefined}
     */
    var onRingMemberBannedError = function (domain, username, jq_row) {
        showErrorMessage(jq_row, domain, username, '#on_membership_request_banned_error_template');
    };

    /**
     * Creates a list of users that have requested membership of this ring.
     *
     * @returns {undefined}
     */
    var createMembershipRequestsList = function (page) {
        var jq_member_list = jQuery('#ring_membership_request_list');
        var actions = [
        {
            name : 'View Profile',
            onReady : function(jq_row, row) {
                var url = row.domain + '/' + row.username + '/profile';
                jQuery('.selector-action-view', jq_row).attr('href', 'http://' + url);
            }
        },
        {
            name : 'Accept',
            onClick : function (event, jq_row, row) {
                event.preventDefault();
                acceptUser(row.domain, row.username, jq_row);
            }
        },
        {
            name : 'Decline',
            onClick : function (event, jq_row, row) {
                event.preventDefault();
                declineUser(row.domain, row.username, jq_row);
            }
        },
        {
            name : 'Ban',
            onClick : function (event, jq_row, row) {
                event.preventDefault();
                banUser(row.domain, row.username, jq_row);
            }
        }
        ];
        var ring_members_table = new BabblingBrook.Client.Component.Selector(
            'user',
            'ring_membership_request_list',
            jq_member_list,
            actions,
            {
                users_to_vet_for_ring : {
                    domain : ring_domain,
                    username : ring_username
                }
            },
            page
        );
    };

    return {

        construct : function() {
            ring_username = BabblingBrook.Library.extractUsername(window.location.href);
            ring_domain = window.location.hostname;
            createMembershipRequestsList(1);
        }
    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Ring.VetRingMembershipRequests.construct();
});