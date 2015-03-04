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
 * @fileOverview Displays a button value for the DisplayPost class.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Component.ValueSetup !== 'object') {
    BabblingBrook.Client.Component.ValueSetup = {};
};


BabblingBrook.Client.Component.ValueSetup.Button = function () {

    /**
     * @namespace Displays a button value field for the DisplayPost class.
     *
     * @param {object} jq_field The div in the template that holds this fields value.
     * @param field_id The id of the field in the post. This is 1 based.
     * @param post Standard post object that this value field is a part of.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Button = function (jq_field, field_id, post) {
        'use strict';

        /**
         * Constructor
         *
         * @return void
         * @refactor Most of this could be in a generic version in the DisplayValue object
         */
        var setup = function () {
            // @refactor Cloning templates should be done via getter, so that the templates are not accidently destroyed.
            //      It would cause hard to trace bugs if they were.
            var jq_button_template = jQuery('#button_value_template').clone();
            var jq_button = jQuery('>.button', jq_button_template);
            jq_button.attr('data-field-id', field_id.toString());

            var field_class_names = jq_field.attr('class');
            var button_class_names = jq_button.attr('class');
            jq_button.attr('class', field_class_names + ' ' + button_class_names);

            BabblingBrook.Client.Component.Value.Button.setStatus(post, field_id, jq_button);
            jq_field.replaceWith(jq_button);
        };
        setup();
    };

    /**
     * Display the button now that the status has been fetched
     *
     * In a callback because the take value may have needing fetching from the server.
     *
     * @param {string} post The post object that contains this field.
     * @param {number} field_id The id of the field in the post that the status is being set for.
     * @param {object} jq_field The jquery object representing the value field.
     * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Button.statusFetched = function (post, field_id, jq_field, status) {
        'use strict';
        var take_value = BabblingBrook.Client.Component.Value.getTakeValue(post, field_id);
        jQuery('>.button-value', jq_field)
            .removeClass('taken waiting untaken paused')
            .addClass(status)
            .attr('title', take_value);
    };

    /**
     * Sets the title and button class for a button value.
     *
     * @param {string} post The post object that contains this field.
     * @param {number} field_id The id of the field in the post that the status is being set for.
     * @param {object} jq_field The jquery object representing the value field.
     * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Button.setStatus = function (post, field_id, jq_field, status) {
        'use strict';
        status = BabblingBrook.Client.Component.Value.setAndGetStatus(
            post,
            field_id,
            status,
            BabblingBrook.Client.Component.Value.Button.statusFetched.bind(null, post, field_id, jq_field)
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
     * @refactor A generic version of this could be in the DisplayValue object
     */
    BabblingBrook.Client.Component.Value.Button.taken = function (jq_post, data) {

        var onPostFetched = function (post) {
            if (typeof post.takes[data.field_id] === 'undefined') {
                post.takes[data.field_id] = {};
            }
            post.takes[data.field_id].value = data.value;
            post.takes[data.field_id].tmp_take = 0;

            var jq_field = jQuery('>div>div[data-field-id=' + data.field_id + ']', jq_post);
            var take_status = 'untaken';
            if (data.value.toString() !== '0') {
                take_status = 'taken';
            }
            BabblingBrook.Client.Component.Value.Button.setStatus(post, data.field_id, jq_field, take_status);
        }

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
     * Error callback for whene the stream data can not be fetched.
     */
    BabblingBrook.Client.Component.Value.Button.streamDataError = function() {
        throw 'Could not display post: Error fetching its stream data.';
    };
    /**
     * Event callback for when the button value is clicked.
     */
    BabblingBrook.Client.Component.Value.Button.buttonEvent = function (event) {
        var jq_button = jQuery(event.currentTarget);
        var jq_post = jq_button.parent().parent().parent();
        var post_id =  jq_post.attr('data-post-id');
        var post_domain =  jq_post.attr('data-post-domain');
        var field_id =  jq_button.parent().attr('data-field-id');
        // Check not already waiting for response from the server and assign status.
        if (jq_button.hasClass('waiting') === true) {
            return;
        }
        var value = 1;
        if (jq_button.hasClass('taken')) {
            value = 0;
        }
        jq_button.removeClass('taken untaken pause').addClass('waiting');

        var onPostFetched = function (post) {
            // Validation passed. Post to server
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    post_id : post_id,
                    field_id : field_id,
                    stream_domain : post.stream_domain,
                    stream_username : post.stream_username,
                    stream_name : post.stream_name,
                    stream_version : post.stream_version,
                    value : parseInt(value, 10),
                    value_type : 'button',
                    mode : 'new'
                },
                'Take',
                BabblingBrook.Client.Component.Value.Button.taken.bind(null, jq_post),
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
    BabblingBrook.Client.Component.Value.Button.setupEvents = function() {
        jQuery(document).on(
            'click',
            '.post>div>.button>span',
            BabblingBrook.Client.Component.Value.Button.buttonEvent
        );
    };

};