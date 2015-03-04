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
 * @fileOverview Code for accessing a users ring memberships on stream and post pages.
 * @author Sky Wickenden
 */

/**
 * @namespace Generate a ring menu to be displayed on Posts.
 * @package JS_Client
 */
BabblingBrook.Client.Component.PostRings = (function () {
    'use strict';

    /**
     * @type {boolean} Used in the document click event to prevent unneccessary DOM navigation when closing menues.
     */
    var menu_open = false;

    /**
     * Marks the take names in a ring take menu as taken or not.
     *
     * @param {string} post_domain
     * @param {string} post_id ID of the post local to the post_domain.
     * @param {string} ring_domain
     * @param {string} ring_name
     * @param {string} take_name
     * @param {boolean} waiting
     *
     * @return void
     */
    var markTaken = function (post_domain, post_id, ring_domain, ring_name, take_name, taken, waiting) {
        // Select the correct ring take line. Use a combination of the post-container and the ring take.
        var jq_post = jQuery('.post[data-post-id = "' + post_id + '"][data-post-domain = "' + post_domain + '"]');
        var jq_ring_list = jQuery('>div>.post-rings>ul', jq_post);
        var ring_selector = 'data-ring-name = "' + ring_domain + '/' + ring_name + '"';
        var take_selector = 'data-take-name = "' + take_name + '"';
        var jq_line = jQuery('li[' + ring_selector + '][' + take_selector + ']', jq_ring_list);
        jq_line.removeClass('ring-waiting ring-taken ring-untaken');
        if (waiting) {
            jq_line.addClass('ring-waiting');
        } else {
            if (taken === false) {
                jq_line.addClass('ring-untaken');
            } else {
                jq_line.addClass('ring-taken');
            }

        }
    };

    /**
     * Receives the status of a rings take_names for a user,
     *
     * Places them in BabblingBrook.Client.User.RingTakes and then processes them.
     *
     * @param {object} data
     * @param {string} data.post_domain
     * @param {string} data.post_id ID of the post local to the post_domain.
     * @param {string} data.ring_domain
     * @param {string} data.ring_name
     * @param {string} data.take_status Each value is either 1 or 0 for taken or not.
     *
     * @return void
     */
    var receiveRingStatus = function (data) {
        BabblingBrook.Library.createNestedObjects(
            BabblingBrook.Client.User.RingTakes,
            [data.post_domain, data.post_id, data.ring_domain,
                data.ring_name]
        );
        BabblingBrook.Client.User.RingTakes[data.post_domain][data.post_id][data.ring_domain][data.ring_name] = data.take_status;
        jQuery.each(data.take_status, function (take_name, take_status) {
            take_status = take_status === 0 ? false : true;
            markTaken(
                data.post_domain,
                data.post_id,
                data.ring_domain,
                data.ring_name,
                take_name,
                take_status,
                false
            );
        });
    };

    /**
     * Report an error to the user if this process fails.
     */
    var receiveRingStatusError = function (data) {
        console.error('receiveRingStatusError error');
    };

    /**
     * Checks if a ring take_name status is already stored locally. If not a request is sent for it.
     *
     * @param {string} post_domain
     * @param {number} post_id
     * @param {string} ring_domain
     * @param {string} ring_name
     *
     * @return void
     */
    var alreadyTaken = function (post_domain, post_id, ring_domain, ring_name, take_name) {
        var taken;
        var does_take_name_exist = BabblingBrook.Library.doesNestedObjectExist(
            BabblingBrook.Client.User.RingTakes,
            [post_domain, post_id, ring_domain, ring_name, take_name]
        );
        var does_ring_name_exist = BabblingBrook.Library.doesNestedObjectExist(
            BabblingBrook.Client.User.RingTakes,
            [post_domain, post_id, ring_domain, ring_name]
        );
        if (does_take_name_exist) {
            taken = BabblingBrook.Client.User.RingTakes[post_domain][post_id][ring_domain][ring_name][take_name];
            markTaken(post_domain, post_id, ring_domain, ring_name, take_name, taken, false);

        // Only request if no other take names have been requested from this post/ring combo
        // - as they will already have been requested.
        } else if (!does_ring_name_exist) {
            // Create the empty object so that it is not requested more than once.
            BabblingBrook.Library.createNestedObjects(
                BabblingBrook.Client.User.RingTakes,
                [post_domain, post_id, ring_domain, ring_name, take_name]
            );
            BabblingBrook.Client.User.RingTakes[post_domain][post_id][ring_domain][ring_name][take_name] = 0;
            // Mark the row as waiting for data

            markTaken(post_domain, post_id, ring_domain, ring_name, take_name, false, true);
            // Request the data
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    post_domain : post_domain,
                    post_id : post_id,
                    field_id : 1,
                    ring_domain : ring_domain,
                    ring_name : ring_name
                },
                'GetRingTakeStatus',
                receiveRingStatus,
                receiveRingStatusError
            );

        // Mark the row as waiting for data
        } else {
            markTaken(post_domain, post_id, ring_domain, ring_name, take_name, false, true);
        }

    };

    /**
     * Receives the status of a request to take a rings take_name.
     *
     * @param {object} data
     * @param {string} data.post_domain
     * @param {string} data.post_id ID of the post local to the post_domain.
     * @param {string} data.ring_domain
     * @param {string} data.ring_name
     * @param {string} data.take_name
     * @param {string} data.status Each value is either 1 or 0 for taken or not.
     */
    var receiveTakeRing = function (data) {
        var does_take_name_exist = BabblingBrook.Library.doesNestedObjectExist(
            BabblingBrook.Client.User.RingTakes,
            [data.post_domain, data.post_id, data.ring_domain, data.ring_name, data.take_name]
        );
        if (!does_take_name_exist) {
            BabblingBrook.Library.createNestedObjects(
                BabblingBrook.Client.User.RingTakes,
                [data.post_domain, data.post_id, data.ring_domain, data.ring_name, data.take_name]
            );
        }
        var d = data;   // Short vairable name to keep assignment within line length limit.
        BabblingBrook.Client.User.RingTakes[d.post_domain][d.post_id][d.ring_domain][d.ring_name][d.take_name] = d.status;

        markTaken(
            data.post_domain,
            data.post_id,
            data.ring_domain,
            data.ring_name,
            data.take_name,
            data.status,
            false
        );
        BabblingBrook.Client.Component.PostRings.onTakenHook();
    };

    /**
     * Report an error to the user if this process fails.
     */
    var receiveTakeRingError = function (data) {
        console.error('receiveTakeRingError error');
    };

    /**
     * A take option on a ring menu has been clicked.
     *
     * @param {object} event_this The this object for the event.
     * @param {object} event The jQuery event object.
     * @param {string} post_domain The domain of the post that the ring menu belongs to.
     * @param {string} post_id The id of the post that the ring menu belongs to.
     *
     * @returns void
     */
    var onRingTake = function (event_this, event, post_domain, post_id) {
        event.stopPropagation();    // Prevent the document level event that clears menus from firing.
        var untake = false;
        var jq_line = jQuery(event_this);

        // Don't attempt a take/untake if we are still awaiting the last action. It may error.
        if (jq_line.hasClass('ring-waiting')) {
            return;
        }

        if (jq_line.hasClass('ring-taken')) {
            untake = true;
        }

        jq_line.removeClass('ring-waiting ring-taken ring-untaken');
        jq_line.addClass('ring-waiting');

        var ring = jq_line.attr('data-ring-name');
        var ring_array = ring.split('/');
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                post_id : post_id,
                post_domain : post_domain,
                untake : untake,
                ring_name : ring_array[1],
                ring_domain : ring_array[0],
                take_name : jq_line.text()
            },
            'TakeRingPost',
            receiveTakeRing,
            receiveTakeRingError
        );
    };

    /**
     * The ring menu on an post has been click for opening/closing.
     *
     * @param {object} event_this The this object for the event.
     * @param {object} event The jQuery event object.
     *
     * @returns {undefined}
     */
    var onRingMenuSwitched = function (event_this, event) {
        event.stopPropagation();    // Prevent the document level event that clears menus from firing.
        var jq_rings = jQuery(event_this);
        var jq_rings_parent = jq_rings.parent();
        var jq_post = jq_rings_parent.parent().parent();
        var post_id = jq_post.attr('data-post-id');
        var post_domain = jq_post.attr('data-post-domain');

        var onPostFetched = function (post) {
            var stream_url = BabblingBrook.Library.makeStreamUrl({
                domain : post.stream_domain,
                username : post.stream_username,
                name : post.stream_name,
                version : post.stream_version
            });

            var jq_ring_list = jQuery('ul', jq_rings_parent);

            if (jq_rings_parent.hasClass('open') === false) {
                menu_open = true;
                // hide any open menus
                jq_ring_list.addClass('hide');
                jQuery('.post-rings').removeClass('open');

                jq_ring_list.removeClass('hide');
                jq_rings_parent.addClass('open');

                jq_ring_list.empty();
                jQuery.each(BabblingBrook.Client.User.Rings, function (i, ring) {
                    if (ring.member === '0') {
                        return true; // skip this row, continue with the ,each.
                    }

                    jQuery.each(ring.take_names, function (j, take_name) {
                        if (take_name.stream_url !== '' && take_name.stream_url !== stream_url) {
                            return true;    // Continue the jQuery.each function.
                        }

                        var full_name = ring.domain + '/' + ring.name;
                        var jq_list_item = jQuery('<li>');
                        jq_list_item
                            .addClass('ring-item link')
                            .attr('data-ring-name', full_name)
                            .attr('data-take-name', take_name.name)
                            .attr('title', full_name)
                            .text(take_name.name);
                        jq_ring_list.append(jq_list_item);
                        alreadyTaken(post.stream_domain, post_id, ring.domain, ring.name, take_name.name);
                        return true;    // Continue the jQuery.each function.
                    });
                });
                if (jQuery("li", jq_ring_list).length === 0) {
                     jq_ring_list.append('<li class="no-rings ring-item">No ring memberships</li>');
                }

                jQuery('li.ring-item', jq_ring_list).click(function (event) {
                    onRingTake(this, event, post_domain, post_id);
                });

            } else {
                menu_open = false;
                jq_ring_list.addClass('hide');
                jq_rings_parent.removeClass('open');
            }
        };

        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : post_domain,
                post_id : post_id
            },
            'GetPost',
            onPostFetched,
            function () {
                console.error('Error loading post for postrings onRingMenuSwitched.');
            }
        );

    };

    /**
     * Sets up the click events for all ring menus in posts on the page.
     *
     * Uses a defered on event so that menus can be added/removed at any time
     *
     * @return void
     */
    var setupClickEvents = function () {
        jQuery('body').on('click', '.ring-title', function (event) {
            onRingMenuSwitched(this, event);
        });

        // closes all open menus if the user clicks elsewhere on the page.
        jQuery(document).click(function () {
            if (menu_open === true) {
                jQuery('.moderation-submenu>ul').addClass('hide');
                jQuery('.post-rings').removeClass('open');
            }
        });

    };

    return {
        construct : function () {
            setupClickEvents();
        },

        /**
         * Is the user a member of this ring.
         *
         * @param {string} domain The domain of the ring.
         * @param {string} username The username of the ring.
         *
         * @returns {boolean}
         */
        isARingMember : function(domain, username) {
            var isAMember = false;
            jQuery.each(BabblingBrook.Client.User.Rings, function (i, ring) {
                if (domain === ring.domain && username === ring.name) {
                    isAMember = true;
                    return false;   // Escape the .each
                }
            });
            return isAMember;
        },

        /**
         * A hook that is called when a ring take is made.
         *
         * @returns {undefined}
         */
        onTakenHook : function () {}

    };
}());