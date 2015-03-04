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
BabblingBrook.Client.Page.Ring.Ban = (function () {
    'use strict';

    var ring_username;

    var ring_domain;

    /**
     * Reinstate a member of this ring.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     *
     * @returns {undefined}
     */
    var reinstateUser = function (domain, username, jq_row) {
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
            'ReinstateRingMember',
            onRingMemberReinstated.bind(null, domain, username, jq_row),
            onRingMemberReinstatedError.bind(null, domain, username, jq_row)
        );
    }

    /**
     * Callback for when a member has been banned.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {object} jq_row A refference to the row of the user that has been banned.
     * @param {object} response_data The response data returned fom the server. See DomusDataTests for details.
     *
     * @returns {undefined}
     */
    var onRingMemberReinstated = function (domain, username, jq_row, response_data) {
        if (response_data.success === false) {
            onRingMemberReinstatedError(domain, username, jq_row);
            return;
        }

        jq_row.removeClass('row-loading');
        jQuery('.ring-ban', jq_row).text('true');
        jQuery('.selector-action-reinstate', jq_row).addClass('hidden');
        jQuery('.selector-action-ban', jq_row).removeClass('hidden');
    }

    /**
     * Callback for when an error arose when a ring member was banned.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     *
     * @returns {undefined}
     */
    var onRingMemberReinstatedError = function (domain, username, jq_row) {
        showErrorMessage(jq_row, domain, username, '#on_ring_member_reinstated_error_template');
    };

    /**
     * Callback for when a member has been banned.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {object} jq_row A refference to the row of the user that has been banned.
     * @param {object} response_data The response data returned fom the server. See DomusDataTests for details.
     *
     * @returns {undefined}
     */
    var onRingMemberBanned = function (domain, username, jq_row, response_data) {
        if (response_data.success === false) {
            onRingMemberBannedError(domain, username, jq_row);
            return;
        }

        jq_row.removeClass('row-loading');
        jQuery('.ring-ban', jq_row).text('true');
        jQuery('.selector-action-ban', jq_row).addClass('hidden');
        jQuery('.selector-action-reinstate', jq_row).removeClass('hidden');
    };

    /**
     * Callback for when an error arose when a ring member was banned.
     *
     * @param {string} domain The users domain.
     * @param {string} username The users username.
     * @param {string} jq_row The row in the selector table for this user.
     *
     * @returns {undefined}
     */
    var onRingMemberBannedError = function (domain, username, jq_row) {
        showErrorMessage(jq_row, domain, username, '#on_ring_member_banned_error_template');
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
            onRingMemberBanned.bind(null, domain, username, jq_row),
            onRingMemberBannedError.bind(null, domain, username, jq_row)
        );
    };

    /**
     * Creates a list of members of a ring using the selector component.
     *
     * @returns {undefined}
     */
    var createMemberList = function (page) {
        var jq_member_list = jQuery('#ring_member_list');
        var actions = [
        {
            name : 'Ban',
            onClick : function (event, jq_row, row) {
                event.preventDefault();
                banUser(row.domain, row.username, jq_row);
            },
            onReady : function (jq_row, row) {
                if (row.ring_ban === 'true') {
                    jQuery('.selector-action-ban', jq_row).addClass('hidden');
                }
            }
        },
        {
            name : 'Reinstate',
            onClick : function (event, jq_row, row) {
                event.preventDefault();
                reinstateUser(row.domain, row.username, jq_row);
            },
            onReady : function (jq_row, row) {
                if (row.ring_ban === 'false') {
                    jQuery('.selector-action-reinstate', jq_row).addClass('hidden');
                }
            }
        }
        ];
        var ring_members_table = new BabblingBrook.Client.Component.Selector(
            'user',
            'ring_member_list',
            jq_member_list,
            actions,
            {
                onReady : function(current_page) {
//                    if (create_history === true) {
//                        // Add a pushstate so that the selector is reopend if the backbutton is pushed.
//                        BabblingBrook.Client.Core.Ajaxurl.changeUrl(
//                            window.location.href,
//                            'BabblingBrook.Client.Page.Ring.Menu.reconstructRingSearch',
//                            document.title,
//                            [current_page]
//                        );
//                    } else {
//                        create_history = true;
//                    }
                },
                show_fields : {
                    ring_ban : true
                },
                ring_filter : {
                    domain : window.location.host,
                    username : BabblingBrook.Library.extractUsername(window.location.href)
                }
            },
            page
        );
    };

    return {

        construct : function() {
            ring_username = BabblingBrook.Library.extractUsername(window.location.href);
            ring_domain = window.location.hostname;
            createMemberList(1);
        }
    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Ring.Ban.construct();
});