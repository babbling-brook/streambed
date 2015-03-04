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
 * @fileOverview Displays a textbox value for the DisplayPost class.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Component.ValueSetup !== 'object') {
    BabblingBrook.Client.Component.ValueSetup = {};
};


BabblingBrook.Client.Component.ValueSetup.Textbox = function () {


    /**
     * @namespace Displays a textbox value field for the DisplayPost class.
     *
     * @param {object} jq_field The div in the template that holds this fields value.
     * @param field_id The id of the field in the post. This is 1 based.
     * @param post Standard post object that this value field is a part of.
     * @param {object} stream The stream that this post resides in.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Textbox = function (jq_field, field_id, post, stream) {
        'use strict';

        /**
         * Constructor
         *
         * @return void
         */
        var setup = function () {
            // @refactor Cloning templates should be done via getter, so that the templates are not accidently destroyed.
            //      It would cause hard to trace bugs if they were.
            var jq_textbox_template = jQuery('#textbox_value_template').clone();
            var jq_textbox = jQuery('>.textbox', jq_textbox_template);
            jq_textbox.attr('data-field-id', field_id.toString());

            var field_class_names = jq_field.attr('class');
            var textbox_class_names = jq_textbox.attr('class');
            jq_textbox.attr('class', field_class_names + ' ' + textbox_class_names);

            BabblingBrook.Client.Component.Value.Textbox.setStatus(post, field_id, jq_textbox);
            jq_field.replaceWith(jq_textbox);
        };
        setup();
    };

    /**
     * Display the textbox value now that the status has been fetched
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
    BabblingBrook.Client.Component.Value.Textbox.statusFetched = function (post, field_id, jq_field, status) {
        'use strict';
        var take_value = BabblingBrook.Client.Component.Value.getTakeValue(post, field_id);
        if (typeof take_value === 'string' && take_value.substr(0, 1) === '+') {
            take_value = take_value.substr(1);
        }
        if (take_value === '0') {
            take_value = '';
            status = 'untaken';
        }

        jQuery('>input', jq_field)
            .removeClass('taken waiting untaken paused')
            .addClass(status)
            .val(take_value);
    };

    /**
     * Sets the title and status class for a textbox value.
     *
     * @param {string} post The post object that contains this field.
     * @param {number} field_id The id of the field in the post that the status is being set for.
     * @param {object} jq_field The jquery object representing the value field.
     * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Textbox.setStatus = function (post, field_id, jq_field, status) {
        'use strict';
        status = BabblingBrook.Client.Component.Value.setAndGetStatus(
            post,
            field_id,
            status,
            BabblingBrook.Client.Component.Value.Textbox.statusFetched.bind(null, post, field_id, jq_field)
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
    BabblingBrook.Client.Component.Value.Textbox.taken = function (jq_post, data) {
        'use strict';

        var onPostFetched = function (post) {
            if (typeof post.takes[data.field_id] === 'undefined') {
                post.takes[data.field_id] = {};
            }
            post.takes[data.field_id].value = data.value;
            post.takes[data.field_id].tmp_take = 0;

            var jq_field = jQuery('>div>div[data-field-id=' + data.field_id + ']', jq_post);
            var take_status = 'untaken';
            if (data.value !== 0) {
                take_status = 'taken';
            }
            BabblingBrook.Client.Component.Value.Textbox.setStatus(post, data.field_id, jq_field, take_status);
        };

        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : data.domain,
                post_id : data.post_id
            },
            'GetPost',
            onPostFetched,
            function () {
                console.error(jQuery('#textbox_get_post_error_template').html());
            }
        );
    };

    /**
     * Error callback for whene the stream data can not be fetched.
     */
    BabblingBrook.Client.Component.Value.Textbox.streamDataError = function() {
        'use strict';
        console.error(jQuery('#textbox_get_stream_error_template').html());
        throw jQuery('#thread_execution_stopped_template').html();
    };

    /**
     * The change event for a textfield
     *
     * The is a shell function for the real event as the stream object needs to be fetched
     * before the real event function can be called.
     *
     * @param {object} event The click event.
     * @param {object} post The post object.
     * @param {object} stream The stream object.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Textbox.TextboxEventWithStream = function (event, post, stream) {
        'use strict';
        var jq_textbox = jQuery(event.currentTarget);
        var jq_post = jq_textbox.parent().parent().parent();
        var jq_error = jQuery('.error', jq_textbox.parent());
        var field_id =  jq_textbox.parent().attr('data-field-id');
        var field = post.content[field_id];

        // Check not already waiting for response from the server and assign status.
        if (jq_textbox.hasClass('waiting') === true) {
            return;
        }

        // Clear previous errors
        jq_error.text('');

        var value = jq_textbox.val();
        if (!BabblingBrook.Library.isInt(value)) {
            jq_error.text('Value is not a whole number.');
            return;
        }

        var value_max;
        var value_min;
        if (typeof field !== 'undefined') {
            if (typeof field.value_max !== 'undefined') {
                value_max = field.value_max;
            }
            if (typeof field.value_min !== 'undefined') {
                value_min = field.value_min;
            }
        }
        if (typeof value_max === 'undefined') {
            value_max = stream.fields[field_id - 1].value_max;
        }
        if (typeof value_min === 'undefined') {
            value_min = stream.fields[field_id - 1].value_min;
        }
        if (parseInt(value, 10) > value_max) {
            jq_error.text('Value is to large. Must be less than ' + value_max);
            return;
        }
        if (parseInt(value, 10) < value_min) {
            jq_error.text('Value is to small. Must be more than ' + value_min);
            return;
        }

        // Validation passed. Post to server
        jq_textbox.removeClass('taken untaken pause').addClass('waiting');
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                post_id : post.post_id,
                field_id : field_id,
                stream_domain : post.stream_domain,
                stream_username : post.stream_username,
                stream_name : post.stream_name,
                stream_version : post.stream_version,
                value : parseInt(value, 10),
                value_type : 'textbox',
                mode : 'new'
            },
            'Take',
            BabblingBrook.Client.Component.Value.Textbox.taken.bind(null, jq_post),
            BabblingBrook.Client.Component.Post.TakeError
        );
    };

    /**
     * The change event for a textfield
     *
     * The is a shell function for the real event as the stream object needs to be fetched
     * before the real event function can be called.
     *
     * @param {object} event The click event.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Textbox.TextboxEvent = function (event) {
        'use strict';
        var jq_textbox = jQuery(event.currentTarget);
        var jq_post = jq_textbox.parent().parent().parent();
        var post_id =  jq_post.attr('data-post-id');
        var post_domain =  jq_post.attr('data-post-domain');


        var onPostFetched = function (post) {
            // Need to fetch the stream data to check for defaults.
            BabblingBrook.Client.Core.Streams.getStream(
                post.domain,
                post.stream_username,
                post.stream_name,
                post.stream_version,
                BabblingBrook.Client.Component.Value.Textbox.TextboxEventWithStream.bind(null, event, post)
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
                console.error(jQuery('#textbox_get_post_error_template').html());
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
    BabblingBrook.Client.Component.Value.Textbox.setupEvents = function() {
        'use strict';
        jQuery(document).on(
            'blur',
            '.post>div>.textbox>input',
            BabblingBrook.Client.Component.Value.Textbox.TextboxEvent
        );
    };
};