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
 * @fileOverview Manages the display of the list of streams that the user is subscribed to.
 * @author Sky Wickenden
 */

/**
 * @namespace Maintains the stream navigation and preloading of data.
 * @package JS_Client
 */
BabblingBrook.Client.Component.StreamNav = (function () {
    'use strict';

    /**
     * Callback for when the nav data is ready to display.
     *
     * @returns {undefined}
     */
    var onNavDataReady = function () {
        // Add priorities to the filters. first filter of each stream is applied first.
        // Followed by next filter of each stream etc.
        // The lower the priority the sooner it is executed.
        var start_priority = 100000;
        var priority_count = 1;
        var current_url = window.location.hostname + window.location.pathname;
        current_url = BabblingBrook.Library.changeUrlAction(current_url, 'json');
        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, stream) {
            jQuery.each(BabblingBrook.Client.User.StreamSubscriptions[i].filters, function (j, filter) {
                // The users stream subscription page can throw errors on the tutorial without this.
                if (typeof filter === 'undefined') {
                    return true;
                }

                // If this is the filter for the current page then boost it to the top.
                var stream_url = BabblingBrook.Library.makeStreamUrl(stream, '');
                if (stream_url === current_url) {
                    filter.priority = 50000 + priority_count;
                } else {
                    filter.priority = start_priority + priority_count;
                }
                priority_count++;
            });
        });

        var jq_more_streams = jQuery('#streams_nav>ul>li.more');
        jq_more_streams.removeClass('hide');
        jQuery('#streams_nav>ul>li#subscriptions_loading').remove();

        if (BabblingBrook.Settings.feature_switches['EDIT_SUBSCRIPTIONS_LINK'] === false) {
            jq_more_streams.addClass('hide');
        } else {
            jq_more_streams.removeClass('hide');
        }

        // remove the float right so that the more element is included in the calculation.
        jq_more_streams.removeClass('more-right');

        var jq_more = jQuery('#streams_nav .edit-stream-subscriptions');
        var more_url = jQuery('a', jq_more).attr('href');
        more_url = more_url.replace('*user*', BabblingBrook.Client.User.username);
        jQuery('a', jq_more).attr('href', more_url);

        var drop_down = false;
        var preprocessed_count = 0;
        var nav_height;

        BabblingBrook.Client.Component.StreamNav.preprocess_filters = [];
        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, stream) {
            if (preprocessed_count === 1) {
                nav_height = jQuery('#streams_nav').height();
                nav_height += 5; // Add a little leaway.
            }
            var stream_url = BabblingBrook.Library.makeStreamUrl(stream, '');
            if (drop_down === false) {
                var jq_item_template = jQuery('#stream_nav_item_template>li').clone();
                jQuery('a', jq_item_template)
                    .attr('title', stream_url)
                    .attr('href', 'http://' + stream_url)
                    .html(BabblingBrook.Client.User.StreamSubscriptions[i].name);
                jq_more_streams.before(jq_item_template);
            }

            // remove the additional nav item and replace it with more...
            var current_nav_height = jQuery('#streams_nav').height();
            var moved_subscription = false;
            if (drop_down === false && current_nav_height > nav_height) {
                var escape = 0;
                while (current_nav_height > nav_height) {
                    var jq_subscription = jQuery('#streams_nav li.more').prev();
                    jq_more.before(jq_subscription);
                    current_nav_height = jQuery('#streams_nav').height();
                    escape++;
                    moved_subscription = true;
                    if (escape > 100) {
                        break;
                    }
                }
                drop_down = true;
            }

            if (drop_down === true && moved_subscription === false) {
                var jq_more_template = jQuery('#stream_nav_more_item_template>li').clone();
                jQuery('a', jq_more_template)
                    .attr('title', stream_url)
                    .attr('href', 'http://' + stream_url)
                    .html(BabblingBrook.Client.User.StreamSubscriptions[i].name.replace(' ', '&nbsp')); // Stops line wrap
                jq_more.before(jq_more_template);
            }

            // If the sort request is the same as this page then set up the filters.

//            var this_page = false; // prevents duplication of this pages filters in the preprocess_filters array
//            if (url === window.location.hostname + window.location.pathname && filters === null) {
//                filters = BabblingBrook.Client.User.StreamSubscriptions[i].filters;
//                this_page = true;
//            }

            if (BabblingBrook.Client.Component.StreamNav.streams_to_preprocess > preprocessed_count) {
                var moderation_rings = [];
                jQuery.each(BabblingBrook.Client.User.StreamSubscriptions[i].rings, function (index, ring) {
                    var ring_url = ring.domain + '/' + ring.username;
                    moderation_rings.push({
                        url : ring_url
                    });
                });
                BabblingBrook.Client.Component.StreamNav.preprocess_filters.push({
                    stream : stream_url,
                    filters : BabblingBrook.Client.User.StreamSubscriptions[i].filters,
                    moderation_rings : moderation_rings
                });
            }
            preprocessed_count++;

        });

        // This allows the nav line to fully justify by adding an extra line to the ul.
//        if (drop_down === true) {
//            jQuery('#streams_nav>ul').append('<li class="stretch"></li>');
//        }

        // reapply the float right to the more link so that it is on the right hand side.
        jq_more_streams.addClass('more-right');

        // Open and close the 'more' nav
        jQuery('#streams_nav li.more').click(function () {
            if (jQuery('#streams_more').is(':visible') === false) {
                jQuery('#streams_more').removeClass('hide');
            } else {
                jQuery('#streams_more').addClass('hide');
            }
        });
        jQuery(document).click(function (element) {
            if (jQuery(element.target).is('#streams_nav .more') === false
                && jQuery(element.target).parent().is('#streams_nav .more') === false
            ) {
                jQuery('#streams_more').addClass('hide');
            }
        });
    };

    return {
        // This variable sets the number of streams whoose posts are filtered in advance of the stream being viewed.
        streams_to_preprocess : 30,

        /**
         * @var {object} preprocess_filters
         * @var {string} preprocess_filters.stream Url of the stream.
         * @var {object} preprocess_filters.filters
         */
        preprocess_filters : [],

        /**
         * The following code builds the stream nav by adding elements until a second line starts,
         * it then removes the last element appended before continuing.
         * To complicate matters, the more link is at the end, and so streams are added before this link.
         */
        setup : function () {
            if (BabblingBrook.Settings.feature_switches['STREAM_NAV'] === false) {
                jQuery('#streams_nav').addClass('hide');
            } else {
                jQuery('#streams_nav').removeClass('hide');
            }

            jQuery('#streams_nav').empty();

            var jq_nav_template = jQuery('#stream_nav_template>ul').clone();
            jQuery('#streams_nav').html(jq_nav_template);

            BabblingBrook.Client.Core.Loaded.onStreamSubscriptionsLoaded(function () {
                if (BabblingBrook.Client.User.tutorial_set !== '') {
                    BabblingBrook.Client.Component.Tutorial.setTutorialNavBar(onNavDataReady);
                } else {
                    onNavDataReady();
                }
            });
        },

        /**
         * Redisplays the stream nav. Should be called whenever the stream nav is edited.
         *
         * @return void
         */
        reshow : function () {
            BabblingBrook.Client.Component.StreamNav.setup();
        },

        loaded : function (sort_request, posts) {
            if (sort_request.update === false && posts.length > 0) {
                // Highlight the stream bar link
                var stream_url = BabblingBrook.Library.changeUrlAction(decodeURI(sort_request.stream_url), 'posts');
                jQuery('#streams a[title="' + stream_url + '"]')
                    .css('backgroundColor', '#337733')
                    .animate({backgroundColor: '#ddeedd'}, 30000);
            }
        },

        showRestrictedMessage : function () {
            jQuery('#sidebar_extra .restricted').removeClass('hide');
        },

        /**
         * Return any moderation rings for the requested stream and filter.
         * @param {string} stream_url
         * @return {object[]} moderation_rings The moderation rings.
         * @return {object} moderation_rings
         * @return {string} moderation_rings.url
         */
        getModerationRings : function (stream_url) {

            if (typeof BabblingBrook.Client.User.StreamSubscriptions === 'undefined') {
                console.error('Calling getModerationRings before BabblingBrook.Client.User.StreamSubscriptions has loaded.');
            }
            stream_url = BabblingBrook.Library.changeUrlAction(stream_url, 'json');
            var moderation_rings = [];
            jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, stream) {
                if (stream.url === stream_url) {
                    jQuery.each(stream.moderation_rings, function (j, ring) {
                        moderation_rings.push({
                            url : ring
                        });
                    });
                }
            });
            return moderation_rings;
        }
    };
}());