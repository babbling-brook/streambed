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
 * @fileOverview Displays a star value for the DisplayPost class.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Component.ValueSetup !== 'object') {
    BabblingBrook.Client.Component.ValueSetup = {};
};


BabblingBrook.Client.Component.ValueSetup.Stars = function () {


    /**
     * @namespace Displays an star value for the DisplayPost class.
     *
     * @param {object} jq_field The div in the post template that holds this star value.
     * @param {number} field_id The id of the field in the post. This is 1 based.
     * @param {object} post Standard post object that this star value field is a part of.
     * @param {object} stream The stream that this post resides in.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Stars = function (jq_field, field_id, post, stream) {
        'use strict';
        /**
         * @type {object} A template for a star in the star list.
         */
        var jq_star;

        /**
         * @type {object} Holds the jQuery elment for the stars container
         */
        var jq_stars;

        /**
         * @type {object} Holds the jQuery elment for the stars.
         */
        var jq_stars_list;

        var createStars = function () {
            var value_max;
            var field = post.content[field_id];
            if (typeof field !== 'undefined' && typeof field.value_max !== 'undefined') {
                value_max = field.value_max;
            }
            if (typeof value_max === 'undefined') {
                value_max = stream.fields[field_id].value_max;
            }
            value_max = parseInt(value_max, 10);
            for (var i=1; i <= value_max; i++) {
                var jq_star_clone = jq_star.clone().wrap('<p>').parent();  // Long winded way of getting outer html.
                jQuery('.star', jq_star_clone).attr('data-star-id', i);
                jq_stars_list.append(jq_star_clone.html());
            }
        }

        /**
         * Constructor
         *
         * @return void
         */
        var setup = function () {
            // @refactor Cloning templates should be done via getter, so that the templates are not accidently destroyed.
            //      It would cause hard to trace bugs if they were.
            var jq_star_template = jQuery('#stars_value_template').clone();
            jq_stars = jQuery('>.stars', jq_star_template);

            jq_stars.attr('data-field-id', field_id.toString());

            var field_class_names = jq_field.attr('class');
            var stars_class_names = jq_stars.attr('class');
            jq_stars.attr('class', field_class_names + ' ' + stars_class_names);

            jq_field.replaceWith(jq_stars);

            jq_stars_list = jQuery('>.stars-list', jq_stars);
            jq_star = jQuery('#stars_value_template .star').clone();
            jq_stars_list.find('.star').remove();   // Remove the star template from the stars clone.
            createStars();
            BabblingBrook.Client.Component.Value.Stars.setStatus(post, field_id, jq_stars);
        }
        setup();
    };

    /**
     * Display the stars now that the status has been fetched
     *
     * In a callback because the take value may have needing fetching from the server.
     *
     * @param {string} post The post object that contains this field.
     * @param {number} field_id The id of the field in the post that the status is being set for.
     * @param {object} jq_field The jquery object representing the value field.
     * @param {number} take_value A value to override the start display with. Used to display the new value while waiting
     *      for the server to respond.
     * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Stars.statusFetched = function (post, field_id, jq_field, take_value, status) {
        'use strict';
        if (typeof take_value === 'undefined') {
            take_value = BabblingBrook.Client.Component.Value.getTakeValue(post, field_id);
        }

        var jq_stars_list = jQuery('>.stars-list', jq_field);

        jq_stars_list
            .removeClass('taken waiting untaken paused')
            .addClass(status);
        jq_stars_list.attr('title', take_value);
        jQuery('.star-value', jq_field).val(parseInt(take_value));

        jQuery('>.stars-list>.star', jq_field).each(function (index, dom_star) {
            var jq_star = jQuery(dom_star);
            var star_status = 'star-off';
            if (index < take_value) {
                star_status = 'star-on';
            }
            jq_star
                .removeClass('star-on star-off')
                .addClass(star_status);
        });
    };

    /**
     * Sets the title and status class for a star value.
     *
     * @param {string} post The post object that contains this field.
     * @param {number} field_id The id of the field in the post that the status is being set for.
     * @param {object} jq_field The jquery object representing the value field.
     * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
     * @param {number} take_value A value to override the start display with. Used to display the new value while waiting
     *      for the server to respond.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Stars.setStatus = function (post, field_id, jq_field, status, take_value) {
        'use strict';
        status = BabblingBrook.Client.Component.Value.setAndGetStatus(
            post,
            field_id,
            status,
            BabblingBrook.Client.Component.Value.Stars.statusFetched.bind(null, post, field_id, jq_field, take_value)
        );
    };

    /**
     * Process an post as having been taken.
     *
     * @param {object} jq_post A jquery object containing the post object.
     * @param {object} data
     * @param {number} data.post_id The local post_id of the post that has been taken.
     * @param {string} data.domain The domain of the stream where the post has been taken.
     * @param {number} data.field_id The id of the field in the post that has been taken.
     * @param {number} data.value The amount that has been taken.
     * @param {string} data.value_type The type of value that has been taken. See BabblingBrook.Models.value_type.
     */
    BabblingBrook.Client.Component.Value.Stars.taken = function (jq_post, data) {

        var onPostFetched = function (post) {
            if (typeof post.takes[data.field_id] === 'undefined') {
                post.takes[data.field_id] = {};
            }
            post.takes[data.field_id].value = data.value;
            post.takes[data.field_id].tmp_take = 0;

            var take_status = 'untaken';
            if (data.value !== 0) {
                take_status = 'taken';
            }

            BabblingBrook.Client.Component.Value.Stars.setStatus(post, data.field_id, jq_post, take_status);
        };

        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : data.domain,
                post_id : data.post_id
            },
            'GetPost',
            onPostFetched,
            function () {
                console.error(jQuery('#stars_get_post_error_template').html());
            }
        );
    }

    /**
     * The click event for a star button.
     *
     * @param {object} event The click event.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Stars.StarEvent = function (event) {
        var jq_star = jQuery(event.currentTarget);
        var jq_stars = jq_star.parent().parent();
        var jq_value = jQuery('.star-value', jq_stars);
        var star_id = jq_star.attr('data-star-id');
        var value = jq_value.val();
        if (value === '') {
            value = '0';
        }
        var jq_post = jq_stars.parent().parent();
        var post_id =  jq_post.attr('data-post-id');
        var post_domain =  jq_post.attr('data-post-domain');
        var field_id =  jq_star.parent().parent().attr('data-field-id');

        // Check not already waiting for response from the server and assign status.
        if (jq_stars.hasClass('waiting') === true) {
            return;
        }

        // If this is a click on the same star set to the current value then set the value to  0.
        if (value === star_id) {
            value = 0;
        } else {
            value = star_id;
        }

        var onPostFetched = function (post) {
            BabblingBrook.Client.Component.Value.Stars.setStatus(post, field_id, jq_stars, 'waiting', value);
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    post_id : post_id,
                    field_id : field_id,
                    stream_domain : post.stream_domain,
                    stream_username : post.stream_username,
                    stream_name : post.stream_name,
                    stream_version : post.stream_version,
                    value : parseInt(value, 10),
                    value_type : 'stars',
                    mode : 'new'
                },
                'Take',
                BabblingBrook.Client.Component.Value.Stars.taken.bind(null, jq_stars),
                BabblingBrook.Client.Component.Post.TakeError
            );
        };

        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : post_domain,
                post_id : post_id
            },
            'GetPost',
            onPostFetched,
            function () {
                console.error(jQuery('#stars_get_post_error_template').html());
            }
        );

    };

    /**
     * Setup click event handlers.
     *
     * In a static function because these are live document level events.
     * This is so that post objects do not need to be maintained after creation.
     * This needs call once on each page that displys posts.
     *
     * @return void;
     */
    BabblingBrook.Client.Component.Value.Stars.setupEvents = function() {
        jQuery(document).on(
            'click',
            '.post>div>div.stars>div.stars-list>.star',
            BabblingBrook.Client.Component.Value.Stars.StarEvent
        );
    };
};