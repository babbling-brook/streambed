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
 * @fileOverview Displays an arrow value for the DisplayPost class.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Component.ValueSetup !== 'object') {
    BabblingBrook.Client.Component.ValueSetup = {};
};

// preload images
var img1 = new Image("/images/ui/up-arrow.png");
var img2 = new Image("/images/ui/down-arrow.png");
var img3 = new Image("/images/ui/up-arrow-waiting.png");
var img4 = new Image("/images/ui/down-arrow-waiting.png");
var img5 = new Image("/images/ui/up-arrow-paused.png");
var img6 = new Image("/images/ui/down-arrow-paused.png");
var img7 = new Image("/images/ui/up-arrow-taken.png");
var img8 = new Image("/images/ui/down-arrow-taken.png");

var img11 = new Image("/images/ui/up-arrow-white.png");
var img12 = new Image("/images/ui/down-arrow-white.png");
var img13 = new Image("/images/ui/up-arrow-waiting-white.png");
var img14 = new Image("/images/ui/down-arrow-waiting-white.png");
var img15 = new Image("/images/ui/up-arrow-paused-white.png");
var img16 = new Image("/images/ui/down-arrow-paused-white.png");
var img17 = new Image("/images/ui/up-arrow-taken-white.png");
var img18 = new Image("/images/ui/down-arrow-taken-white.png");


// @fixme This wrapper is a cludge to prevent the Value class from being overwritten.
// Needs a more elegant solution (prototypes)
BabblingBrook.Client.Component.ValueSetup.Arrows = function () {

    /**
     * @namespace Displays an arrow value for the DisplayPost class.
     *
     * @param {object} jq_field The div in the template that holds this arrow value.
     * @param field_id The id of the field in the post. This is 1 based.
     * @param post Standard post object that this arrow value field is a part of.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Arrows = function (jq_field, field_id, post) {
        'use strict';

        /**
         * Constructor
         *
         * @return void
         */
        var setup = function () {
            // @refactor Cloning templates should be done via getter, so that the templates are not accidently destroyed.
            //      It would cause hard to trace bugs if they were.
            var jq_arrow_template = jQuery('#up_down_value_template').clone();
            var jq_arrows = jQuery('>.updown', jq_arrow_template);
            jq_arrows.attr('data-field-id', field_id.toString());

            var field_class_names = jq_field.attr('class');
            var arrows_class_names = jq_arrows.attr('class');
            jq_arrows.attr('class', field_class_names + ' ' + arrows_class_names);
            BabblingBrook.Client.Component.Value.Arrows.setStatus(post, field_id, jq_arrows);
            jq_field.replaceWith(jq_arrows);
        };
        setup();

        return {

        }
    };

    /**
     * Display the arrows now that the status has been fetched
     *
     * In a callback because the take value may have needing fetching from the server.
     *
     * @param {string} post The post object that contains this field.
     * @param {number} field_id The id of the field in the post that the status is being set for.
     * @param {object} jq_field The jquery object representing the value field.
     * @param {string} direction Which arrow has been clicked, 'up' or 'down'.
     * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Arrows.statusFetched = function (post, field_id, jq_field, direction, status) {
        'use strict';
        var take_value = BabblingBrook.Client.Component.Value.getTakeValue(post, field_id);
        var up_class;
        var down_class;
        if (status === 'untaken') {
            up_class = 'up-untaken';
            down_class = 'down-untaken';
        }
console.debug(status);
        if (take_value > 0) {
            up_class = 'up-taken';
            down_class = 'down-untaken';
        } else if (take_value < 0) {
            up_class = 'up-untaken';
            down_class = 'down-taken';
        } else if (take_value === 0) {
            up_class = 'up-untaken';
            down_class = 'down-untaken';
        }

        if (status === 'paused' || status === 'waiting') {
            if (direction === 'up') {
                up_class = 'up-' + status;
            } else if (direction === 'down') {
                down_class = 'down-' + status;
            }
        }

        if (typeof up_class === 'undefined') {
            up_class = 'up-untaken';
        }
        if (typeof down_class === 'undefined') {
            down_class = 'down-untaken';
        }

        jQuery('>.up-arrow', jq_field)
            .removeClass('up-taken up-waiting up-untaken up-paused')
            .addClass(up_class);
        jQuery('>.down-arrow', jq_field)
            .removeClass('down-taken down-waiting down-untaken down-paused')
            .addClass(down_class);
        jq_field.attr('title', take_value);
    };

    /**
     * Sets the title and arrow class for an updown arrow value.
     *
     * @param {string} post The post object that contains this field.
     * @param {number} field_id The id of the field in the post that the status is being set for.
     * @param {object} jq_field The jquery object representing the value field.
     * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
     * @param {string} direction Which arrow has been clicked, 'up' or 'down'.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Arrows.setStatus = function (post, field_id, jq_field, status, direction) {
        'use strict';
        status = BabblingBrook.Client.Component.Value.setAndGetStatus(
            post,
            field_id,
            status,
            BabblingBrook.Client.Component.Value.Arrows.statusFetched.bind(null, post, field_id, jq_field, direction)
        );
    };

    /**
     * An overridable hook method that can be set elewhere and is called after an arrow is taken.
     *
     * @param jq_post JQuery object Pointing to the post in the DOM.
     * @param post_data object Details of the post. See takenArrow function for details.
     * @returns {undefined}
     */
    BabblingBrook.Client.Component.Value.Arrows.onTakenHook = function (jq_post, post_data) {};


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
    BabblingBrook.Client.Component.Value.Arrows.takenArrow = function (jq_post, data) {
        'use strict';

        var onPostFetched = function (post) {
            if (typeof post.takes[data.filed_id] === 'undefined') {
                post.takes[data.field_id] = {};
            }
            post.takes[data.field_id].value = data.value;
            post.takes[data.field_id].tmp_take = 0;
            var jq_field = jQuery('>div>div[data-field-id=' + data.field_id + ']', jq_post);
console.debug(jq_field.length);
            // Photowall posts
            if (jq_field.length === 0) {
                jq_field = jQuery('>div>div>div[data-field-id=' + data.field_id + ']', jq_post);
            }
            var take_status = 'untaken';
            if (data.value !== 0) {
                take_status = 'taken';
            }
            BabblingBrook.Client.Component.Value.Arrows.setStatus(post, data.field_id, jq_field, take_status);
            BabblingBrook.Client.Component.Value.Arrows.onTakenHook(jq_post, post, data.field_id);
        };

        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : data.domain,
                post_id : data.post_id
            },
            'GetPost',
            onPostFetched,
            function () {
                console.error(jQuery('#up_down_get_post_error_template').html());
            }
        );
    };

    /**
     * The click event for an updown value button.
     *
     * @param {string} value The amount to change the take by. +1 for up, -1 for down.
     * @param {boolean} has_caption Arrows in captions have an additional div layer.
     * @param {object} event The click event.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Arrows.ArrowEvent = function (value, has_caption, event) {
        'use strict';
        var jq_arrow = jQuery(event.currentTarget);
        var direction = 'up';
        if (value < 0) {
            direction = 'down';
        }
        // Check not already waiting for response from the server and assign status.
        if (jq_arrow.hasClass(direction + '-waiting') === true) {
            return;
        }
        var jq_post = jq_arrow.parent().parent().parent();
        if (typeof has_caption !== "undefined" && has_caption === true) {
            jq_post = jq_post.parent();
        }
        var post_id =  jq_post.attr('data-post-id');
        var post_domain =  jq_post.attr('data-post-domain');
        var field_id =  jq_arrow.parent().attr('data-field-id');
        var onPostFetched = function (post) {

            if (post.status === 'deleted') {
                return;
            }

            if (typeof post.takes[field_id] === 'undefined') {
                post.takes[field_id] = {};
            }
            if (typeof post.takes[field_id].tmp_take === 'undefined') {
                post.takes[field_id].tmp_take = value;
            } else {
                post.takes[field_id].tmp_take += value;
            }
            var static_tmp_value = post.takes[field_id].tmp_take;

            BabblingBrook.Client.Component.Value.Arrows.setStatus(post, field_id, jq_arrow.parent(), 'paused', direction);

            // Give the user a second to click again before storing.
            setTimeout(function () {
                // If the post has just loaded, then the empty field object may be deleted by the loading of user takes
                // - so we need to check if it needs recreating.
                // @refactor When posts and takes are loaded create empty field objects for all value fields.
                if (typeof post.takes[field_id] === 'undefined') {
                    post.takes[field_id] = {};
                }

                // If the temp_value has not changed then submit, otherwise abort - the next click will handle it.
                if (static_tmp_value !== post.takes[field_id].tmp_take) {
                    return;
                }

                BabblingBrook.Client.Component.Value.Arrows.setStatus(
                    post,
                    field_id,
                    jq_arrow.parent(),
                    'waiting', direction
                );

                BabblingBrook.Client.Core.Interact.postAMessage(
                    {
                        post_id : post_id,
                        field_id : field_id,
                        stream_domain : post.stream_domain,
                        stream_username : post.stream_username,
                        stream_name : post.stream_name,
                        stream_version : post.stream_version,
                        value : post.takes[field_id].tmp_take,
                        value_type : 'updown',
                        mode : 'add'
                    },
                    'Take',
                    BabblingBrook.Client.Component.Value.Arrows.takenArrow.bind(null, jq_post),
                    BabblingBrook.Client.Component.Post.TakeError
                );

            }, 1000);
        };

        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : post_domain,
                post_id : post_id
            },
            'GetPost',
            onPostFetched,
            function () {
                console.error(jQuery('#up_down_get_post_error_template').html());
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
    BabblingBrook.Client.Component.Value.Arrows.setupEvents = function() {
        'use strict';
        jQuery(document).on(
            'click',
            '.post>div>.updown>.up-arrow',
            BabblingBrook.Client.Component.Value.Arrows.ArrowEvent.bind(null, 1, false)
        );
        jQuery(document).on(
            'click',
            '.post>div>.updown>.down-arrow',
            BabblingBrook.Client.Component.Value.Arrows.ArrowEvent.bind(null, -1, false)
        );
        jQuery(document).on(
            'click',
            '.post>div.caption>div>.updown>.up-arrow',
            BabblingBrook.Client.Component.Value.Arrows.ArrowEvent.bind(null, 1, true)
        );
        jQuery(document).on(
            'click',
            '.post>div.caption>div>.updown>.down-arrow',
            BabblingBrook.Client.Component.Value.Arrows.ArrowEvent.bind(null, -1, true)
        );
    };

};