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
 * @fileOverview Code relating to the index page for Rings.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Ring !== 'object') {
    BabblingBrook.Client.Page.Ring = {};
}

/**
 *  @namespace Sets up the client index page for managing a users ring subscriptions.
 */
BabblingBrook.Client.Page.Ring.Menu = (function () {
    'use strict';

    // Load additional ring data needed to display super_ring invites.
    var super_invites = null;

    var url_vars;

    /**
     * Get the query string paramaters from an url.
     */
    var getUrlVars = function () {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        var i;
        for (i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    };

    /**
     * Upgrades the membership or admin type of a dedupped ring.
     *
     * @param {array} rings The array of rings that contains the ring to upgrade.
     * @param {string} domain The domain of the ring to upgrade.
     * @param {string} username The username of the ring to upgrade.
     * @param {strng} type 'member' or 'admin'.
     *
     * @returns {void}
     */
    var upgradeMembershipType = function (rings, domain, username, type) {
        for (var i = 0; i < rings.length; i++) {
            if (rings[i].name === username && rings[i].domain === domain) {
                rings[i][type] = '1';
                rings[i].super_ring = true;
            }
        }
    };

    /**
     * Removes duplicate rings from the passed in array and sorts the results.
     *
     * @param {object} rings
     *
     * @return {object}
     */
    var removeDuplicateRings = function (rings) {
        var unique_rings = [];
        for (var i = 0; i < rings.length; i++) {
            var found = false;
            for (var j = 0; j <= i; j++) {
                if (i !== j && rings[i].name === rings[j].name && rings[i].domain === rings[j].domain) {
                    found = true;
                    // There might be duplicates with higher membership settings. MAke sure the highest is kept.
                    if (rings[i].member === '0' && rings[j].member === '1'
                        || rings[i].member === '1' && rings[j].member === '0'
                    ) {
                        upgradeMembershipType(unique_rings, rings[i].domain, rings[i].name, 'member');
                    }
                    if (rings[i].admin === '0' && rings[j].admin === '1'
                        || rings[i].admin === '1' && rings[j].admin === '0'
                    ) {
                        upgradeMembershipType(unique_rings, rings[i].domain, rings[i].name, 'admin');
                    }
                }
            }
            if (found === false) {
                unique_rings.push(rings[i]);
            }
        }
        var sortRings = function(a, b) {
            if (a.domain < b.domain) {
                return -1;
            } else if (a.domain > b.domain) {
                return 1;
            } else {
                if (a.name < b.name) {
                    return -1;
                } else {
                    return 1;
                }
            }
        };
        unique_rings.sort(sortRings);
        return unique_rings;
    };

    var displaySuperInvites = function () {
        // Merge the super invites with the rings in BabblingBrook.Client.User.
        // Copy the array so that the result is not permanent.
        var full_rings = BabblingBrook.Client.User.Rings.slice(0);
        jQuery.merge(full_rings, super_invites.member_super_rings);
        jQuery.merge(full_rings, super_invites.admin_super_rings);
        full_rings = removeDuplicateRings(full_rings);
        jQuery('#member_rings_loading').addClass('hide');
        jQuery('#admin_rings_loading').addClass('hide');
        jQuery.each(full_rings, function (i, ring) {
            if (ring.admin === '1') {
                appendAdminLine(ring);
            }

            if (ring.member === '1') {
                appendMemberLine(ring);
            }
        });
    };

    /**
     * Callback for when a users invitations have been fetched.
     *
     * @returns {undefined}
     */
    var onInvitationsFetchedError = function () {
        jQuery('#invitations_loading').addClass('hide');
        var message = jQuery('#on_fetching_invitations_error_template').text();
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : message
        });
    };

    /**
     * Callback for when the number of users waiting to be vetted has returned from the domus domain.
     *
     * @param {object} jq_row A jQuery reference to the table row in the 'Rings you administer' table.
     * @param {object} ring The ring whoose membership request data has been fetched.
     *
     * @returns {undefined}
     */
    var onFetchedRingUsersWaitingToBeVetted = function (jq_row, ring, response_data) {
        if (response_data.success === false) {
            onFetchedRingUsersWaitingToBeVettedError(jq_row, ring);
            return;
        }

        var link = 'http://' + ring.domain + '/' + ring.name + '/ring/vetmembershiprequests';
        jQuery('.vet-users', jq_row).attr('href', link);
        jQuery('.vet-users-content', jq_row)
            .removeClass('text-loading')
            .find('.vet-users-qty').text(response_data.qty);
        // Removes the span tag from inside the a tag (otherwise ajaxurl treats the click as a span).
        jQuery('.vet-users', jq_row).text(jQuery('.vet-users', jq_row).text());
    };

    /**
     * Error callback for when the domus fails to return the number of users waiting to be vetted for a ring.
     *
     * @param {object} jq_row A jQuery reference to the table row in the 'Rings you administer' table.
     * @param {object} ring The ring whoose data has failed to be fetched.
     *
     * @returns {undefined}
     */
    var onFetchedRingUsersWaitingToBeVettedError = function (jq_row, ring) {
        var jq_message = jQuery('#on_fetching_ring_users_waiting_to_be_vetted_error_template').clone();
        jQuery('.waiting-to-be-vetted-ring', jq_message).text(ring.name + '@' + ring.domain);
        jQuery('.vet-users-content', jq_row).removeClass('text-loading').addClass('error');
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : jq_message.text()
        });
    };

    /**
     * Appends an admin line to the ring admin table.
     *
     * @param {object} ring The ring that is being appended. See BabblingBrook.Client.User.Rings for details.
     *
     * @returns {undefined}
     */
    var appendAdminLine = function (ring) {
        var jq_table = jQuery('#admin_rings>table>tbody');
        jQuery('#admin_rings table').removeClass('hide');
        jQuery('#no_admin_rings').addClass('hide');

        var jq_row = jQuery('#admin_row_template>tbody>tr').clone();
        var ring_link = 'http://' + ring.domain + '/' + ring.name + '/';
        jQuery('.edit-profile', jq_row).attr('href', ring_link + 'editprofile');
        jQuery('.admin-page', jq_row).attr('href', ring_link + 'ring/update');
        jQuery('.admin-invitation', jq_row)
            .attr('href', ring_link + 'ring/invite?menu_type=admin&type=admin&to=' + url_vars.to);
        if (ring.member_type === 'request') {
            jQuery('.vet-users-content', jq_row).removeClass('hide');
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    domain : ring.domain,
                    username : ring.name
                },
                'FetchRingUsersWaitingToBeVetted',
                onFetchedRingUsersWaitingToBeVetted.bind(null, jq_row, ring),
                onFetchedRingUsersWaitingToBeVettedError.bind(null, jq_row, ring)
            );
        }

        jQuery('.ring-name', jq_row)
            .attr('title', ring.domain + '/' + ring.name)
            .text(ring.name);

        if (typeof ring.super_ring !== 'undefined') {
            jQuery('.ring-name', jq_row)
                .text(ring.name + ' (super ring grants access)');
        }

        jQuery('.profile-page', jq_row).attr('href', ring_link);
        jQuery('.member-invitation', jq_row)
            .attr('href', ring_link + 'ring/invite?menu_type=admin&type=member&to=' + url_vars.to);

        if (ring.member_type === 'admin_invitation') {
            jQuery('.member-invitation', jq_row).removeClass('hide');
        }
        if (ring.admin_type === 'invitation') {
            jQuery('.admin-invitation', jq_row).removeClass('hide');
        }
        jQuery(jq_table).append(jq_row);
        if (jQuery('.admin-invitation', jq_row).is(':visible') === true
            && jQuery('.member-invitation', jq_row).is(':visible') === true
        ) {
            jQuery('.member-invitation', jq_row).after('<br />');
        }
    };

    /**
     * Appends a membership line to the ring members table.
     *
     * @param {object} ring The ring that is being appended. See BabblingBrook.Client.User.Rings for details.
     *
     * @returns {undefined}
     */
    var appendMemberLine = function (ring) {
        var jq_table = jQuery('#member_rings>table>tbody');
        jQuery('#member_rings table').removeClass('hide');
        jQuery('#no_membership_rings').addClass('hide');

        var ring_link = 'http://' + ring.domain + '/' + ring.name + '/';
        var jq_row = jQuery('#member_row_template>tbody>tr').clone();
        jQuery('.members-area', jq_row).attr('href', ring_link + 'ring/members' + url_vars.to);

        jQuery('.ring-name', jq_row)
            .attr('title', ring.domain + '/' + ring.name)
            .text(ring.name);
        jQuery('.profile-page', jq_row).attr('href', ring_link);
        jQuery('.member-invitation', jq_row)
            .attr('href', ring_link + 'ring/invite?menu_type=member&type=member&to=' + url_vars.to);

        if (ring.member_type === 'invitation') {
            jQuery('.member-invitation', jq_row).removeClass('hide');
        }
        jQuery(jq_table).append(jq_row);
    };

    /**
     * Callback for when a users invitations have been fetched.
     *
     * @param {object} invitation_data Invitations for the user.
     *
     * @returns {undefined}
     */
    var onInvitationsFetched = function (invitation_data) {
        jQuery('#invitations_loading').addClass('hide');
        if (invitation_data.invitations.length === 0) {
            jQuery('#invitations_none').removeClass('hide');
            return;
        }
        jQuery.each(invitation_data.invitations, function(index, invite) {
            var jq_row = jQuery('#invites_row_template>tbody>tr').clone();
            var line_details;
            if (invite.type === 'member') {
                line_details = jQuery('#join_as_member_invitation_template>div').clone();
            } else {
                line_details = jQuery('#join_as_admin_invitation_template>div').clone();
            }
            var jq_line_details = jQuery(line_details);
            jQuery('.ring-name', jq_line_details)
                .text(invite.ring_username)
                .attr('title', invite.ring_domain + '/' + invite.ring_username);
            jQuery('.from-user', jq_line_details)
                .text(invite.from_username)
                .attr('title', invite.from_domain + '/' + invite.from_username);

            jQuery('.invite-details', jq_row).append(jq_line_details);
            jQuery('.invite-join', jq_row).attr('data-ring-username', invite.ring_username);
            jQuery('.invite-join', jq_row).attr('data-ring-domain', invite.ring_domain);
            jQuery('.invite-join', jq_row).attr('data-type', invite.type);
            jQuery('#invitations_table>tbody').append(jq_row);

            jQuery('.invite-join', jq_row).click(onInviteAcceptedClick);

        });
        jQuery('#invitations_table').removeClass('hide');
    };

    /**
     * Fetches a users ring invitations.
     *
     * @returns {undefined}
     */
    var fetchInvitations = function () {
        BabblingBrook.Library.get(
            '/' + BabblingBrook.Client.User.username + '/ring/invitations',
            {},
            onInvitationsFetched,
            onInvitationsFetchedError
        );
    };

    /**
     * Handler for errors when posting an accepted invite to the server.
     *
     * @returns {undefined}
     */
    var onInviteAcceptedError = function (message) {
        if (typeof message !== 'string') {
            message = jQuery('#on_invite_accepted_generic_error_template').text();
        }
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : message
        });

    };

    /**
     * Handler for when an invitation has been accepted.
     *
     * @param {string} type Is this a member or admin invitation.
     * @param {string} ring_domain The domain of the ring that an invitation has been accepted for.
     * @param {string} ring_username The username of the ring that an invitation has been accepted for.
     * @param {object} jq_invite_row A jQuery object representing the row in the invite table.
     * @param {object} response_data The data reutnred from the server.
     * @param {object} [response_data.error] An error message if there is one.
     * @param {object} response_data.ring Data for the ring that has been subscribed to
     *    (matches a line in BabblingBrook.Client.User.Rings)
     *
     * @returns {undefined}
     */
    var onInviteAccepted = function (type, jq_invite_row, response_data) {
        if (typeof response_data.error === 'string') {
            onInviteAcceptedError(response_data.error);
            return;
        }

        BabblingBrook.Client.User.Rings.push(response_data.ring);

        if (type === 'member') {
            appendMemberLine(response_data.ring);
        } else {
            appendAdminLine(response_data.ring);
        }
        jq_invite_row.slideUp();
    };

    /**
     * Handler for click events on join messages.
     *
     * @returns {undefined}
     */
    var onInviteAcceptedClick = function () {
        var jq_link = jQuery(this);
        var jq_row = jq_link.parent().parent();
        var ring_username = jq_link.attr('data-ring-username');
        var ring_domain = jq_link.attr('data-ring-domain');
        jq_link.addClass('text-loading');
        var type = jq_link.attr('data-type');
        BabblingBrook.Library.post(
            '/' + ring_username + '/ring/acceptinvitation',
            {
                ring_domain : ring_domain,
                ring_username : ring_username,
                type : type
            },
            onInviteAccepted.bind(null, type, jq_row),
            onInviteAcceptedError
        );
        return false;
    };

    /**
     * Creates the selector to select rings to view.
     *
     * @param {number} page The page number to open the selector on.
     *      Enables the selector to be opend where it was left when the back button is pressed.
     *
     * @returns {false} Cancels click event.
     */
    var createRingSearchSelector = function (page, create_history) {
        if (jQuery('#search_rings').hasClass('hide') === false) {
            jQuery('#selector_member_ring_user').slideUp(250, function () {
                jQuery('#join_rings').text('Search for rings to join');
                jQuery('#search_rings')
                    .empty()
                    .addClass('hide');
            });
            return false;
        }

        var jq_join_user_search = jQuery('#search_rings');
        var actions =[
        {
            name : 'View',
            onReady : function(jq_row, row) {
                var url;
                if (row.domain !== window.location.host) {
                    url = 'http://' + row.domain + '/' + row.username + '/profile'
                } else {
                    url = '/' + row.username + '/profile'
                }
                jQuery('.selector-action-view', jq_row).attr('href', url);
            }
        }
        ];
        var join_user_search_table = new BabblingBrook.Client.Component.Selector(
            'user',
            'member_ring_user',
            jq_join_user_search,
            actions,
            {
                onReady : function(current_page) {
                    if (create_history === true) {
                        // Add a pushstate so that the selector is reopend if the backbutton is pushed.
                        BabblingBrook.Client.Core.Ajaxurl.changeUrl(
                            window.location.href,
                            'BabblingBrook.Client.Page.Ring.Menu.reconstructRingSearch',
                            document.title,
                            [current_page]
                        );
                    } else {
                        create_history = true;
                    }
                },
                user_type : 'ring',
                only_joinable_rings : true
            },
            page
        );

        jq_join_user_search.slideDown(250, function () {
            jq_join_user_search.removeClass('hide');
            jQuery('#join_rings').text('Close ring search');
        });


        return false;   // cancels the click event.
    };

    return {

        /**
         * Reconstructs the ring search selector when the back button is pressed.
         *
         * @returns {undefined}
         */
        reconstructRingSearch : function (page) {
            createRingSearchSelector(page, false);
        },

        construct : function () {

            url_vars = getUrlVars();
            // ensure 'to' paramater is set so that we don't have to check later.
            // 'to' contains the name of the a user to send an invitation to.
            // It is passed in here so that the user can select the
            // ring to send an invitation from.
            if (typeof url_vars.to === 'undefined') {
                url_vars.to = '';
            }
            BabblingBrook.Library.post(
                'superinvitors',
                {},
                /**
                * Callback for superinvitors request.
                * @var {object} super_invites
                * @var {object} super_invites.member_super_rings
                * @var {object} super_invites.admin_super_rings
                */
                function (response_data) {
                    super_invites = response_data;
                    displaySuperInvites();
                }
            );

            fetchInvitations();

            jQuery('#join_rings').click(createRingSearchSelector.bind(null, 1, true));
        }
    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Ring.Menu.construct();
});