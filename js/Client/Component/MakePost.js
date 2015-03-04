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
 * @fileOverview Enables the making of posts.
 *
 * I appologise in advance for the crapness of this module. It neds streamlining to get rid of all the
 * surpless ids and classes. Should be using a parent selector combined with child rather than unique ids.
 * What can I say, we were all young once.
 *
 * @author Sky Wickenden
 */

/**
 * @namespace Object for providing the ability for a user to make posts.
 * Usage:
 *      To display a form for a user to make an post call BabblingBrook.Client.Component.MakePost.setupNewPost(params)
 *      -- see function for paramater details.
 *      To create an post silently call  BabblingBrook.Client.Component.MakePost.setupHiddenPost(params).
 *
 *
 * @param {function} [insertCallback] A callback function to run after an post has been created.
 *      Accepts a single paramater : post - a standard post object.
 * @param {function} [cancelCallback] A callback function to call if the make is cancelled.
 *
 * @return void
 */
BabblingBrook.Client.Component.MakePost = function (insertCallback, cancelCallback) {
    'use strict';

    if (typeof insertCallback === 'undefined') {
        insertCallback = function () {};
    }
    if (typeof cancelCallback === 'undefined') {
        cancelCallback = function () {};
    }

    /**
     * @type {object} that Used to access 'this' from nested functions.
     */
    var that = this;

    var invitation_to_write = 'Write something...';
    var invitation_to_link = 'Post a link...';

    /**
     * Is this an edit of an existing post.
     * @type Boolean
     */
    var edit_mode = false;

    /**
     * @type {number} instance Identifies the post instance in the HTML.
     *      instances prototype defined below BabblingBrook.Client.Component.MakePost.
     */
    var instance = this.instances.push({}) - 1;

    /**
     * @type {object} Holds details about the stream for this instance.
     */
    var stream;

    if (this.instances[instance].stream !== undefined) {
         // Restore the stream if the instance is passed in.
        stream = this.instances[instance].stream;
    }

    // Is the form open or closed. Valid values are 'open' and 'minimised'.
    var minimised_state;
    if (this.instances[instance].minimised_state !== undefined) {
         // Restore the minimised_state if the instance is passed in.
        minimised_state = this.instances[instance].minimised_state;
    }

    // The id of the container for this instance.
    var dom_id = '#make_post_' + instance;
    // An array of post rows objects.
    var post_rows = [];
    // The post object. Populated when editing an existing post.
    var post;

    var private_addresses;

    // The maximum length of an post textbox
    var max_textbox = 200;
    // See http://stackoverflow.com/questions/417142/what-is-the-maximum-length-of-a-url
    // @protocol ensure dbase and BabblingBrook protocol document this.
    var max_url_length = 2048;
    var list_error_1 = 'Select exactly # items';
    var list_error_2 = 'Select up to # items';
    var list_error_3 = 'Select between #1 and #2 items';
    var submit_post_error = 'Please correct the error above.';
    var parent_id;
    var top_parent_id;

    var thumbnails_form_id = BabblingBrook.Client.Component.LinkThumbnails.create();

    /**
     * Instances of textareas that are using a rich text editor for display.
     * Indexed by dom_id +'_' + dom_id
     * See generateTextBox for implementation details.
     *
     * @type type
     */
    var textarea_instances = {};

    /**
     * Used to indicate that an inserted post is hidden, so that the call from
     * the domus domain after the post is created.
     * does not try to remove a form that does not exist before calling the call_back.
     * @type Boolean
     */
    var hidden_post = false;

    BabblingBrook.Client.Component.MakePost.setupEvents();
    BabblingBrook.Client.Component.MakePost.prototype.setup_run = true;

    /**
     * Test for any element errors and show/hide the full form error message.
     */
    var displayFormError = function () {
        var jq_errors = jQuery(dom_id + ' .error:visible').not('.post-submit-error').not('.thumbnails-loading-error');
        var error_text = submit_post_error;
        if (jq_errors.length > 1) {
            error_text = submit_post_error.replace('error', 'errors');
        }

        if (jq_errors.length > 0) {
            jQuery(dom_id + ' .post-submit-error').text(error_text).removeClass('hide');
        } else {
            jQuery(dom_id + ' .post-submit-error').addClass('hide');
        }
    };

    /**
     * Event for checking a text box field for errors.
     *
     * @param {object} row_id
     * @param {object} field
     * @param {object} field.regex
     * @param {object} field.regex_error
     *
     * @return void
     */
    var checkTextErrors = function (field, row_id) {

        var regex = new RegExp(field.regex);
        var jq_error = jQuery(dom_id + ' .post-field-' + row_id + '-error');
        var error = ' ';

        if (typeof textarea_instances[dom_id + '_' + row_id] !== 'undefined') {
            var value = textarea_instances[dom_id + '_' + row_id].getContent();
        } else {
            value = jQuery('#post_' + instance + '_field_' + row_id + '_text').val();
        }

        if (value.length > 0 && field.regex !== null && field.regex !== '' && regex.exec(value) === null) {
            if (field.regex_error.trim() === '') {
                error += 'The text you have entered does not pass this fields filter.';
            } else {
                error += field.regex_error;
            }
            jq_error.html(error).removeClass('hide');
        } else if (value.length > parseInt(field.max_size)) {
            jq_error.html(
                error + 'Must be less than ' + field.max_size + ' characters. Currently ' + value.length
            ).removeClass('hide');
        } else if (field.required && value.length < 1) {
            jq_error.html(error + 'This is a required field').removeClass('hide');
        } else {
            jq_error.addClass('hide');
        }

        displayFormError();
    };

    /**
     * Generate an post textbox.
     *
     * @param {number} row_id The row number of the field.
     * @param {object} field Contains details of the field obtained from the stream server.
     * @param {object} jq_element The jQuery element that the text box will be prepended into.
     */
    var generateTextBox = function (row_id, field, jq_element) {
        // If this is an edit, then set the current state.
        var text_value = '';
        var new_post = true;
        if (row_id === 1 && minimised_state === 'minimised') {
            if (stream.fields[row_id].text_type === 'just_text') {
                text_value = invitation_to_write;
            } else {
                text_value = '<p>' + invitation_to_write + '</p>';
            }
        }
        if (BabblingBrook.Library.doesNestedObjectExist(post_rows, [row_id, 'text'])) {
            text_value = post_rows[row_id].text;
            new_post = false;
        }
        var jq_container = jQuery('#make_post_textarea_template>div').clone();
        jq_container.addClass(minimised_state);
        jQuery('.post-field-title', jq_container).addClass('post-field-' + row_id + '-title');
        jQuery('.field-label', jq_container).text(field.label);
        jQuery('.error', jq_container).addClass('post-field-' + row_id + '-error');
        jQuery('textarea', jq_container)
            .attr('id', 'post_' + instance + '_field_' + row_id + '_text')
            .text(text_value);
        jq_element.prepend(jq_container);

        var jq_text_area = jQuery('#post_' + instance + '_field_' + row_id + '_text');
        jq_text_area.on('focus', function () {
            if (jQuery(dom_id + ' .input-post-detail>div').hasClass('minimised') === true) {
                openMainField();
                if (new_post === true) {
                    jq_text_area.val('');
                }
            }
        });
        if (stream.fields[row_id].text_type === 'just_text') {
            jq_text_area.data('x', 'notset');
            jq_text_area.mousedown(function (){
                if (typeof jq_text_area.data('x') === 'undefined' ||  jq_text_area.data('x') === 'notset') {
                    // Store the initial width of the textarea - so that we can detect if it is resized.
                    jq_text_area.data('x', jq_text_area.outerWidth());
                    // Store the initial width - so that we can be reset it if it is exceeded.
                    jq_text_area.data('max-x', jq_text_area.width());
                }
            });
            jq_text_area.on('blur', function () {
                checkTextErrors(stream.fields[row_id], row_id);
            });
            /**
             * Mouse up event for browsers that support a resize attribute.
             */
            jq_text_area.mouseup(function () {
                var jq_resized = jQuery(this);
                if (jq_resized.outerWidth() !== jq_text_area.data('x')) {
                    if (jq_resized.width() >  jq_text_area.data('max-x')) {
                        jq_resized.width(jq_text_area.data('max-x'));
                    }
                }
            });
            BabblingBrook.Client.Component.MakePost.ResizeTextarea(jq_text_area, true);

        } else {
            textarea_instances[dom_id + '_' + row_id] = new BabblingBrook.Client.Component.RichTextFacade(
                'post_' + instance + '_field_' + row_id + '_text'
            );
            textarea_instances[dom_id + '_' + row_id].generateStreamTextField(
                stream,
                row_id,
                dom_id,
                openMainField,
                new_post,
                checkTextErrors.bind(null, field, row_id)
            );
        }

        // Set a max size if it is missing.
        if (field.max_size === null || field.max_size === 0) {
            field.max_size = max_textbox;
        }
    };

    /**
     * Checks if a link includes a schema.
     * @param {string} link
     * @return {boolean}
     */
    var checkLink = function (link) {
        // Only check if a protocol is included.
        if (link !== '' && link.indexOf('://') === -1) {
            return false;
        }
        return true;
    };

    /**
     * Event for checking a link field for errors.
     * @param {object} event
     * @param {object} event.data
     * @param {object} event.data.row_id
     * @param {object} event.data.field
     */
    var checkLinkTitleErrors = function (event) {
        var field = event.data.field;
        var row_id = event.data.row_id;
        var jq_error = jQuery(dom_id + ' .post-field-' + row_id + '-title-error');
        var error = ' ';

        if (this.value.length > field.max_size) {
            jq_error.html(
                error + 'Must be less than ' + field.max_size + ' characters. Currently ' + this.value.length
            ).removeClass('hide');
        } else {
            jq_error.html('').addClass('hide');
        }
        displayFormError();
    };

    /**
     * Event for checking if a link title contains a link. Copies it to the link field if it does.
     * @param {object} event
     * @param {object} event.data
     * @param {object} event.data.row_id
     */
    var checkLinkTitleForLink = function (event) {
        var row_id = event.data.row_id;
        var is_link = BabblingBrook.Library.checkForLink(this.value);
        var jq_link_field = jQuery(dom_id + ' .post-field-' + row_id + '-link');
        if (is_link && jq_link_field.val().length < 1) {
            jq_link_field.val(this.value);
        }
        displayFormError();
    };

    /**
     * Event for checking a link field for errors.
     * @param {object} event
     * @param {object} event.data
     * @param {object} event.data.row_id
     * @param {object} event.data.field
     */
    var checkLinkFieldErrors = function (event) {
        var field = event.data.field;
        var row_id = event.data.row_id;
        var jq_error = jQuery(dom_id + ' .post-field-' + row_id + '-error');
        var error = ' ';
        if (this.value.length > max_url_length) {
            jq_error.html(
                error + 'Must be less than ' + max_url_length + ' characters. Currently ' + this.value.length
            ).removeClass('hide');
        } else if (field.required && this.value.length < 1) {
            jq_error.html(error + 'This is a required field').removeClass('hide');
        } else {
            jq_error.html('').addClass('hide');
        }

        // If the link is not valid include a http://
        if (event.type === 'blur' && !checkLink(this.value) && this.value.length > 0) {
            this.value = 'http://' + this.value;
        }


        var old_link = jQuery(dom_id + ' .post-field-' + row_id + '-link').attr('data-old-link');
        if (this.value !== '' && (typeof old_link === 'undefined' || old_link !== this.value)) {
            var jq_thumb_location = jQuery(dom_id + ' .post-field-' + row_id + '-link-thumbnails');
            var jq_thumb_template = jQuery('#link_thumbnail_template>div').clone();
            var original_thumb_url;
            if (typeof post === 'object') {
                var original_thumb_url = 'http://' + BabblingBrook.Client.User.domain +
                    '/images/user/' + BabblingBrook.Client.User.domain + '/' +
                    BabblingBrook.Client.User.username + '/post/thumbnails/large/' + post.post_id +
                    '/' + row_id + '.png';
            }
            BabblingBrook.Client.Component.LinkThumbnails.addRow(
                thumbnails_form_id,
                row_id,
                this.value,
                jq_thumb_location,
                jq_thumb_template,
                onThumbnailSelected,
                original_thumb_url
            );
            jQuery(dom_id + ' .post-field-' + row_id + '-link').attr('data-old-link', this.value);
        }

        // if there is no link, check there is no link title.
        var jq_link_title = jQuery('#post_' + instance + '_field_' + row_id + '_text');
        var jq_link_link = jQuery(dom_id + ' .post-field-' + row_id + '-link');
        if (jq_link_link.val().length < 1 && jq_link_title.val().length > 0) {
            jq_error.html(error + 'You have entered a link title, but no link.').removeClass('hide');
        }
        displayFormError();
    };

    /**
     * A callback sent to the LinkThumbnails module that passes back the thumbnail as three base64 strings
     *
     * @param {integer} row_id The id of the row in the form.
     * @param {string} thumb_url The url of the original image used to generate the thumnnail.
     * @param {string} small_base64 A png string of the small generated thumbnail.
     * @param {string} large_base64 A png string of the large generated thumbnail.
     *
     */
    var onThumbnailSelected = function (row_id, thumb_url, small_base64, large_base64) {
        if (typeof thumb_url === 'undefined') {
            jQuery('.post-field-' + row_id + '-link-thumbnail-url').val('');
            jQuery('.post-field-' + row_id + '-link-small-thumbnail-base16').val('');
            jQuery('.post-field-' + row_id + '-link-large-thumbnail-base16').val('');
        } else {
            jQuery('.post-field-' + row_id + '-link-thumbnail-url').val(thumb_url);
            jQuery('.post-field-' + row_id + '-link-small-thumbnail-base16').val(small_base64);
            jQuery('.post-field-' + row_id + '-link-large-thumbnail-base16').val(large_base64);
        }
    };

    /**
     * Check for a profile link and link title if it is expected.
     * This is not the normal format for entering user posts, but it needs to be accounted for here.
     * @param {object} event
     * @param {object} event.data
     * @param {object} event.data.row_id
     */
    var checkLinkUser = function (event) {
        var row_id = event.data.row_id;
        if (stream.kind === 'user') {

            // Link text should be a username
            var link_title = jQuery('#post_' + instance + '_field_' + row_id + '_text').val();
            var profile_error = false;

            var is_username = BabblingBrook.Test.isA([link_title, 'full-username'], '', false);
            if (!is_username) {
                profile_error = true;
            }

            // Link should be a profile
            var link = document.createElement('a');
            link.href = jQuery(dom_id + ' .post-field-' + row_id + '-link').val();
            var path = link.pathname;
            if (path.charAt(0) === '/') {
                path = path.substring(1);
            }
            if (path.charAt(path.length - 1) === '/') {
                path = path.substring(0, path.length - 1);
            }
            var path_parts = path.split('/');

            // Check for both remote and local profiles
            if (path_parts.length === 2) {
                if (path_parts[1] !== 'profile') {
                    profile_error = true;
                }
            } else if (path_parts.length === 4) {
                if (path_parts[0] !== 'elsewhere') {
                    profile_error = true;
                }
                if (path_parts[3] !== 'profile') {
                    profile_error = true;
                }
            } else {
                profile_error = true;
            }
            if (profile_error) {
                jQuery(dom_id + ' .post-field-' + row_id + '-error')
                    .html('This post is for rating users. The Link title needs to be a full username. '
                        + 'The link needs to be a profile link.')
                    .removeClass('hide');
            }
        }
        displayFormError();
    };

    /**
     * Generate an post link field.
     * @param {number} row_id The row number of the field.
     * @param {object} field Contains details of the field obtained from the stream server.
     * @param {object} jq_element The jQuery element that the text box will be prepended into.
     */
    var generateLinkField = function (row_id, field, jq_element) {
        // If this is an edit, then set the current state
        var link_text_value = '';
        var link_value = '';
        if (BabblingBrook.Library.doesNestedObjectExist(post_rows, [row_id, 'link_title'])) {
            link_text_value = post_rows[row_id].link_title;
        }
        if (BabblingBrook.Library.doesNestedObjectExist(post_rows, [row_id, 'link'])) {
            link_value = post_rows[row_id].link;
        }

        if (row_id === 1 && minimised_state === 'minimised' && link_text_value === '') {
            link_text_value = invitation_to_link;
        }

        var jq_linktitle = jQuery('#make_post_linkfield_template>div').clone();
        jq_linktitle.addClass(minimised_state);
        jQuery('.post-field-title-title', jq_linktitle).addClass('post-field-' + row_id + '-title');
        jQuery('.field-label', jq_linktitle).text(field.label);
        jQuery('.error-title', jq_linktitle).addClass('post-field-' + row_id + '-title-error');
        jQuery('textarea', jq_linktitle)
            .attr('id', 'post_' + instance + '_field_' + row_id + '_text')
            .val(link_text_value);
        jQuery('.post-field-title-link', jq_linktitle).addClass('post-field-' + row_id + '-link-title');
        jQuery('.error-link', jq_linktitle).addClass('post-field-' + row_id + '-error');
        jQuery('input', jq_linktitle)
            .addClass('post-field-' + row_id + '-link')
            .val(link_value);

        jQuery('.thumnails', jq_linktitle).addClass('post-field-' + row_id + '-link-thumbnails');
        jQuery('.thumnails', jq_linktitle).addClass('post-field-' + row_id + '-link-thumbnail-url');
        jQuery('.thumbnail-url', jq_linktitle)
            .addClass('post-field-' + row_id + '-link-small-thumbnail-base16');
        jQuery('.large-thumbnail-base16', jq_linktitle)
            .addClass('post-field-' + row_id + '-link-large-thumbnail-base16');

        jq_element.prepend(jq_linktitle);

        // The link field starts with the link hidden so that it doesn't show if it is the first field and the form
        // is closed. However if the minimised state is not minimised then it needs to show them.
        // If no the first row then should be shown, as this is hidden by the form container.
        if (minimised_state === 'open' || row_id > 1) {
            jQuery(dom_id + ' .post-field-' + row_id + '-link-title').removeClass('hide');
            jQuery(dom_id + ' .post-field-' + row_id + '-link').removeClass('hide');
            jQuery(dom_id + ' .post-field-' + row_id + '-link-thumbnails').removeClass('hide');
        }

        // Set a max size if it is missing.
        if (field.max_size === null || field.max_size === 0) {
            field.max_size = max_textbox;
        }

        var jq_linktitle = jQuery('#post_' + instance + '_field_' + row_id + '_text');

        var jq_link_thumbs = jQuery('.post-field-' + row_id + '-link-thumbnails', jq_linktitle.parent());

        var jq_thumbs_template = jQuery('#link_thumbnails_template>div').clone();
        jq_link_thumbs.append(jq_thumbs_template);

        jq_linktitle.on('focus', function () {
            if (jQuery(dom_id + ' .input-post-detail>div').hasClass('minimised') === true) {
                if (jq_linktitle.val() === link_text_value) {
                    jq_linktitle.val('');
                }
                openMainField();
            }
        });

        //jq_textarea.data('x', jq_textarea.outerWidth());    // Store the width to check if changed in mouse up.
        jq_linktitle
            .on(
                'change keyup',
                {
                    row_id : row_id,
                    field : field
                },
                checkLinkTitleErrors
            )
            .on(
                'blur',
                {
                    row_id : row_id,
                    field : field
                },
                checkLinkTitleForLink
            )
            .mousedown(function (){
                if (typeof jq_linktitle.data('x') === 'undefined') {
                    // Store the initial width of the textarea - so that we can detect if it is resized.
                    jq_linktitle.data('x', jq_linktitle.outerWidth());
                    // Store the initial width - so that we can be reset it if it is exceeded.
                    jq_linktitle.data('max-x', jq_linktitle.width());
                }
            })
            /**
             * Mouse up event for browsers that support a resize attribute.
             */
            .mouseup(function(){
                var jq_resized = jQuery(this);
                if (jq_resized.outerWidth() !== jq_linktitle.data('x')) {
                    if (jq_resized.width() >  jq_linktitle.data('max-x')) {
                        jq_resized.width(jq_linktitle.data('max-x'));
                    }
                    BabblingBrook.Client.Component.MakePost.ResizeTextarea(jq_linktitle, true);
                }
                // set new height/width
                jq_linktitle.data('x', jq_resized.outerWidth());
            });


        jQuery(dom_id + ' .post-link-text.post-field-' + row_id + '-link').on(
            'blur',
            {
                row_id : row_id,
                field : field
            },
            checkLinkFieldErrors
        );

        jQuery(dom_id + ' .post-field-' + row_id + '-link').on(
            'blur',
            {
                row_id : row_id
            },
            checkLinkUser
        );

    };

    /**
     * Generate an post checkbox field.
     * @param {number} row_id The row number of the field.
     * @param {object} field Contains details of the field obtained from the stream server.
     * @param {object} jq_element The jQuery element that the text box will be prepended into.
     */
    var generateCheckbox = function (row_id, field, jq_element) {

        var checked = false;
        // Set the default
        if (field.checkbox_default === true) {
            checked = true;
        }

        // If this is an edit, then set the current state
        if (BabblingBrook.Library.doesNestedObjectExist(post_rows, [row_id, 'checked'])) {
            checked = post_rows[row_id].checked;
        }
        var jq_checkbox = jQuery('#make_post_checkbox_template>div').clone()
        jq_checkbox.addClass(minimised_state);
        jQuery('div', jq_checkbox).addClass('post-field-' + row_id + '-title');
        jQuery('.field-label', jq_checkbox).text(field.label);
        jQuery('input', jq_checkbox)
            .attr('id', 'post_' + instance + '_field_' + row_id + '_checkbox')
            .prop('checked', checked);
        jq_element.prepend(jq_checkbox);
    };

    /**
     * Generate the relavent error message for lists.
     * @param {object} field The stream field used to generate the message.
     *                       This is extracted from the stream JSON object.
     * @return {string} error message
     */
    var generateListError = function (field) {
        if (field.select_qty_min === field.select_qty_max) {
            return list_error_1.replace('#', field.select_qty_min);
        } else if (field.select_qty_min === 0) {
            return list_error_2.replace('#', field.select_qty_max);
        } else if (field.select_qty_min > 0) {
            return list_error_3.replace('#1', field.select_qty_min).replace('#2', field.select_qty_max);
        }
        return 'Invalid list.';
    };

    /**
     * Show post form list errors.
     * @param {number} qty_selected The number of slected elements.
     * @param {number} row_id post element row_id.
     * @param {number} min minimum number of elements to select.
     * @param {number} max maximum number of elements to select.
     * @param {string} error_msg The Error message to show.
     * @return {boolean} In error or not (true === error).
     */
    var showListError = function (qty_selected, row_id, min, max, error_msg) {
        if (qty_selected < min || qty_selected > max) {
            jQuery('#post_' + instance + '_list_' + row_id + '_error').text(error_msg).removeClass('hide');
            displayFormError();
            return true;
        } else {
            jQuery('#post_' + instance + '_list_' + row_id + '_error').addClass('hide');
            displayFormError();
            return false;
        }
    };

    /**
     * Show errors for small lists.
     *
     * @param {number} row_id post element row_id.
     * @param {number} min minimum number of elements to select.
     * @param {number} max maximum number of elements to select
     * @param {string} error_msg The Error message to show.
     *
     * @return {boolean} In error or not (true === error).
     */
    var showSmallListError = function (row_id, min, max, error_msg) {
        var qty_selected = jQuery('#post_' + instance + '_list_' + row_id + ' input:checkbox:checked').length;
        return showListError(qty_selected, row_id, min, max, error_msg);
    };

    /**
     * Generate an post list field.
     *
     * @param {number} row_id The row number of the field.
     * @param {object} field Contains details of the field obtained from the stream server.
     * @param {object} jq_element The jQuery element that the text box will be prepended into.
     *
     * @return void
     */
    var generateList = function (row_id, field, jq_element) {
        var error_msg = generateListError(field);

        var jq_list = jQuery('#make_post_list_template>div').clone();
        jq_list.addClass(minimised_state);
        jQuery('.post-field-title', jq_list).addClass('post-field-' + row_id + '-title');
        jQuery('.field-label', jq_list).text(field.label);
        jQuery('.error', jq_list).attr('id', 'post_' + instance + '_list_' + row_id + '_error');
        jQuery('.post-list-container', jq_list).attr('id', 'post_' + instance + '_list_' + row_id);

        jQuery.each(field.list, function (i, item) {
            // If this is an edit then set the buttons state
            var list_checked = false;
            if (BabblingBrook.Library.doesNestedObjectExist(post_rows, [row_id, 'selected'])) {
                if (jQuery.inArray(item, post_rows[row_id].selected) !== -1) {
                    list_checked = true;
                }
            }

            var jq_line = jQuery('#make_post_list_item_template').clone();
            jQuery('input', jq_line)
                .attr('id', 'post_' + instance + '_field_' + row_id + '_list_' + i)
                .prop('checked', list_checked);
            jQuery('label', jq_line)
                .attr('for', 'post_' + instance + '_field_' + row_id + '_list_' + i)
                .text(item);

            jQuery('.post-list-container', jq_list).append(jq_line.contents());

        });
        jq_element.prepend(jq_list);

        jQuery('#post_' + instance + '_list_' + row_id).buttonset({});

        jQuery('#post_' + instance + '_list_' + row_id + ' input:checkbox').bind(
            'change',
            showSmallListError.bind(null, row_id, field.select_qty_min, field.select_qty_max, error_msg)
        );

    };

    /**
     * Show post form open list errors.
     * @param {number} qty_selected The number of slected elements.
     * @param {number} row_id post element row_id.
     * @param {number} min minimum number of elements to select.
     * @param {number} max maximum number of elements to select.
     * @param {string} error_msg The Error message to show.
     * @param {number} [delete_modifier=0] IF this a call from the delete handler then it is neccessary to modify the
     *                                     condition becasue it is called just before the deletion.
     * @return {boolean} In error or not (true === error).
     */
    var showOpenListError = function (row_id, min, max, delete_modifier) {

        if (typeof delete_modifier === 'undefined') {
            delete_modifier = 0;
        }

        var min_compare = parseInt(min, 10) + delete_modifier;
        var max_compare = parseInt(max, 10) + delete_modifier;

        // Count the number of tokens
        var jq_input = jQuery('#post_' + instance + '_openlist_' + row_id + '>.post-field-' + row_id + '-openlist');
        var list_array = jq_input.val().split(',');
        var count = list_array.length;

        if (count < min_compare || count > max_compare) {
            var error_msg = 'At least ' + min + ' and at most ' + max + ' list items must be entered.';
            jQuery('#post_' + instance + '_openlist_' + row_id + '_error').text(error_msg).removeClass('hide');
            displayFormError();
            return true;
        } else {
            jQuery('#post_' + instance + '_openlist_' + row_id + '_error').addClass('hide');
            displayFormError();
            return false;
        }
    };

    /**
     * Generate an post open list field.
     * @param {number} row_id The row number of the field.
     * @param {object} field Contains details of the field obtained from the stream server.
     * @param {object} jq_element The jQuery element that the text box will be prepended into.
     */
    var generateOpenList = function (row_id, field, jq_element) {
        var list_values = '';
        if (BabblingBrook.Library.doesNestedObjectExist(post_rows, [row_id, 'selected'])) {
            list_values = post_rows[row_id].selected.toString();
        }

        var jq_container = jQuery('#make_post_openlist_template>div').clone();
        jq_container.addClass(minimised_state);
        jQuery('.post-field-title', jq_container).addClass('post-field-' + row_id + '-title');
        jQuery('.field-label', jq_container).text(field.label);
        jQuery('.error', jq_container).attr('id', 'post_' + instance + '_openlist_' + row_id + '_error');
        jQuery('.openlist-container', jq_container).attr('id', 'post_' + instance + '_openlist_' + row_id);
        jQuery('.openlist-container>input', jq_container)
            .addClass('post-field-' + row_id + '-openlist')
            .val(list_values);

        jq_element.prepend(jq_container);
        var jq_openlist = jQuery('.openlist-container>input', jq_container);

        jQuery('input', jq_openlist)
            .addClass('post-field-' + row_id + '-openlist')
            .val(list_values);


        /**
         * If fetching suggestions fails, don't show the user an error, just report it to the console.
         *
         * @param {string} text_to_fetch_suggestions_for The text used to search for suggestions .
         *
         * @return void
         */
        var onFetchedSuggestionsError = function (text_to_fetch_suggestions_for) {
            console.error('Error whilst fetching suggestions for open list row_id: ' + row_id);
            console.log(stream);
            console.log(text_to_fetch_suggestions_for);
        };

        /**
         * Callback for when open list suggestions have been fetched. Pass the data on to the real callback.
         *
         * @param {function} onSuggestionsFetched The callback to pass the suggestions array to.
         * @param {object} suggestion_data The data object returned from the domus domiain.
         * @param {array} suggetsion_data.suggestions An array of suggestion strings.
         *
         * @return {void}
         */
        var onOpenListSuggestionsFetched = function (onSuggestionsFetched, suggestion_data) {
            onSuggestionsFetched(suggestion_data.suggestions);
        };

        /**
         * Callback for when suggestions need to be fetched.
         *
         * @param {string} text_to_fetch_suggestions_for The text that suggestions are being searched for.
         * @param {function} onSuggestionsFetched The callback to call after suggestions have been fetched.
         *
         * @retrun {void}
         */
        var onOpenListSuggestionsFetch = function (text_to_fetch_suggestions_for, onSuggestionsFetched) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    text_to_fetch_suggestions_for : text_to_fetch_suggestions_for,
                    stream : stream,
                    field_id : row_id
                },
                'OpenListSuggestionsFetch',
                onOpenListSuggestionsFetched.bind(null, onSuggestionsFetched),
                onFetchedSuggestionsError.bind(null, text_to_fetch_suggestions_for)
            );
        };

        BabblingBrook.Client.Component.HelpHints.attatch(
            jq_openlist,
            onOpenListSuggestionsFetch,
            function () {},
            true
        );
    };

    /**
     * Check that a value minimum field is within bounds.
     * @param {object} event
     * @param {object} event.data
     * @param {object} event.data.row_id
     * @param {object} event.data.field
     */
    var checkValueMaxMin = function (event) {
        var row_id = event.data.row_id;
        var field = event.data.field;
        var jq_max = jQuery('#post_' + instance + '_field_' + row_id + '_value_max');
        var jq_min = jQuery('#post_' + instance + '_field_' + row_id + '_value_min');
        var jq_max_error = jQuery(dom_id + ' .post-field-max-' + row_id + '-error');
        var jq_min_error = jQuery(dom_id + ' .post-field-min-' + row_id + '-error');

        jq_max_error.addClass('hide');
        jq_min_error.addClass('hide');
        if (jq_min.val().length < 1) {
            jq_min_error.html('A minimum value must be entered.').removeClass('hide');
        } else if (!BabblingBrook.Library.isInt(jq_min.val())) {
            jq_min_error.html('The value must be a whole number.').removeClass('hide');
        }

        if (jq_max.val().length < 1) {
            jq_max_error.html('A maximum value must be entered.').removeClass('hide');
        } else if (!BabblingBrook.Library.isInt(jq_max.val())) {
            jq_max_error.html('The value must be a whole number.').removeClass('hide');
        }

        // Special case for stars
        if (field.value_type === 'stars') {
            if (parseInt(jq_max.val(), 10) < 1) {
                jq_max_error.html('The value must be positive.').removeClass('hide');
            }
            jq_min_error
                .addClass('hide')
                .text('');
        }

        // Special case for log sliders. Ensure both max and min are powers of 10 or zero.
        if (field.value_type === 'logarithmic') {
            var regexp = /^0jq_|^((1|-1)0+)$/;    // zero or ( 1 or minus 1) followed by zeros - at lest one.
            if (jq_min_error.is(':visible') === false && regexp.test(jq_min.val()) === false) {
                jq_min_error
                    .html('The value must be zero or a power of 10. Eg 10, 100, 1000, -10, -100 etc')
                    .removeClass('hide');
            }
            if (jq_max_error.is(':visible') === false && regexp.test(jq_max.val()) === false) {
                jq_max_error
                    .html('The value must be zero or a power of 10. Eg 10, 100, 1000, -10, -100 etc')
                    .removeClass('hide');
            }
        }

        if (jq_min_error.is(':visible') === false && jq_max_error.is(':visible') === false
            && parseInt(jq_max.val(), 10) <= parseInt(jq_min.val(), 10)
        ) {
            jq_max_error.html('The maximum value must be greater than the minimum').removeClass('hide');
        }

    };

    /**
     * Generate an post value field.
     * @param {number} row_id The row number of the field.
     * @param {object} field Contains details of the field obtained from the stream server.
     * @param {object} jq_element The jQuery element that the text box will be prepended into.
     */
    var generateValueField = function (row_id, field, jq_element) {

        var value_min = '';
        var value_max = '';

        if (BabblingBrook.Library.doesNestedObjectExist(post_rows, [row_id, 'value_min'])) {
            value_min = post_rows[row_id].value_min;
        }
        if (BabblingBrook.Library.doesNestedObjectExist(post_rows, [row_id, 'value_max'])) {
            value_max = post_rows[row_id].value_max;
        }

        // Only display if needed
        if (field.value_options === 'maxminpost') {
            var max_label = 'Enter a maximum value that you will accept for this post';

            // star constraints only have a maximum and so the standard fields need adapting.
            var stars_hide = '';
            if (field.value_type === 'stars') {
                max_label = 'Enter the number of stars to display';
                stars_hide = 'hide';
                if (value_min === '') {
                    value_min = '0';
                }
            }

            var jq_value = jQuery('#make_post_value_template>div').clone();
            jq_value.addClass(minimised_state);
            jQuery('.post-field-title', jq_value).addClass('post-field-' + row_id + '-title');
            jQuery('.field-label', jq_value).text(field.label);
            jQuery('.min-value-label', jq_value)
                .attr('for', 'post_' + instance + '_field_' + row_id + '_value_min')
                .addClass(stars_hide);
            jQuery('.min-error', jq_value).addClass('post-field-min-' + row_id + '-error ' + stars_hide);
            jQuery('.min-value', jq_value)
                .attr('id', 'post_' + instance + '_field_' + row_id + '_value_min')
                .addClass('post-field-' + row_id + '-value-min ' + stars_hide)
                .val(value_min);
            jQuery('.max-value-label', jq_value).attr('for', 'post_' + instance + '_field_' + row_id + '_value_max');
            jQuery('.max-error', jq_value).addClass('post-field-max-' + row_id + '-error');
            jQuery('.max-value', jq_value)
                .attr('id', 'post_' + instance + '_field_' + row_id + '_value_max')
                .addClass('post-field-' + row_id + '-value-max')
                .val(value_max);



            jq_element.prepend(jq_value);

            jQuery('#post_' + instance + '_field_' + row_id + '_value_min').on(
                'blur',
                {
                    row_id : row_id,
                    field : field
                },
                checkValueMaxMin
            );
            jQuery('#post_' + instance + '_field_' + row_id + '_value_max').on(
                'blur',
                {
                    row_id : row_id,
                    field : field
                },
                checkValueMaxMin
            );


        } else if (field.value_options === 'rhythmpost') {
            console.error('rhythm post not implemented at present');
//            jQuery(id + ' .input-post-detail').prepend(' '
//                + '<div class="post-field-container ' + minimised_state + '">'
//                +     '<div class="post-field-' + row_id + '-value post-field-title">'
//                +         '<em>' + field.label + '</em>:'
//                +     '</div>'
//                +     '<label for="post_field_' + row_id + '_value_rhythm">'
//                +         'Enter an Rhythm to use for checking this value'
//                +     '</label><br/>'
//                +     '<input type="text" id="post_' + instance + '_field_' + row_id + '_value_rhythm" '
//                +             'class="post-field-' + row_id + '-value-rhythm text"><br/>'
//                +     '<div class="post-field-' + row_id + '-error error hide"></div>'
//                + '</div>');
//            // Check value is valid
//            jQuery(dom_id + ' .post-field-' + row_id + '-value-rhythm')
//                .bind('change', {row_id : row_id}, function (event) {
//                    checkRhythm(jQuery(dom_id + 'post-field-' + event.data.row_id + '-value-rhythm')
//                        .val(), jQuery(dom_id + ' .post-field-' + event.data.row_id + '-error'));
//            });
        }
    };

// Commented out until rhythm takes are reintroduced.
//    /**
//     * Check that an Rhythm is valid.
//     * @param rhythm The full Rhythm to check
//     * @param element to show the error
//     */
//    function checkRhythm(rhythm, element) {
//        var path = window.location.pathname.split('/');
//        postProcessingOn();
//
//        var exists_url = '/' + path[1] + '/rhythm/exists?rhythm_url=' + encodeURI(rhythm)
//        jQuery.getJSON(
//            ,
//            /**
//             *    Check if an Rhythm exists.
//             *    @param {object} data
//             *    @param {boolean} data.exists
//             */
//            function (data) {
//                if (!data.exists) {
//                    element.text(value_rhythm_error).removeClass('hide');
//
//                } else {
//                    element.text('').addClass('hide');
//                }
//                postProcessingOff();
//            }
//        );
//        // Check if an Rhythm exists.
//        BabblingBrookClientInfoRequest.request(stream_url, {}, callback);
//    }

    /**
     * Callback for the domus domain returning an error after trying to make an post.
     *
     * @return void
     */
    var makePostError = function () {
        jQuery(dom_id).find('input').attr('disabled', false);
        jQuery(dom_id).find('textarea').attr('disabled', false);
        jQuery(dom_id).find('button').attr('disabled', false);
        jQuery(dom_id).find('.post-submit-domus-error').removeClass('hide');
        jQuery(dom_id).find('.post-submit-error').addClass('hide');
        jQuery(dom_id).removeClass('block-loading');
//            BabblingBrook.Client.Component.Messages.addMessage({
//                type : 'error',
//                message : 'There has been an unknown error when submitting your post. It may ha',
//                buttons : [
//                    {
//                        name : 'Retry',
//                        callback : function () {
//                            BabblingBrook.Client.Core.Interact.postAMessage(
//                                {
//                                    'post' : post,
//                                    'instance' : instance
//                                },
//                                'MakePost',
//                                madePost,
//                                makePostError
//                            );
//                        }
//                    }
//                ]
//            });
    };

    /**e
     * Callback for MakePost. Called once the domus domain returns having completed the post.
     * @param {object} data
     * @param {object} data.post See BabblingBrook.Models.posts with tree child and extensions.
     * @param {string} data.instance The client site id for this post.
     */
    var madePost = function (data) {
        jQuery(dom_id).find('.input-post-title').text(invitation_to_write);
        jQuery(dom_id).removeClass('block-loading');
        jQuery(dom_id).find('input').attr('disabled', true);
        jQuery(dom_id).find('textarea').attr('disabled', true);
        jQuery(dom_id).find('button').attr('disabled', true);
        jQuery(dom_id).find('.post-submit-domus-error').addClass('hide');
        post = data.post;
        that.clearForm();   // Also runs the callback
    };

    /**
     * Submit the post data.e
     * @param {number} post_id This is only set if the post is an edit.
     */
    var submitPost = function (post_id) {
        jQuery(dom_id).addClass('block-loading');
        jQuery(dom_id).find('input').attr('disabled', true);
        jQuery(dom_id).find('textarea').attr('disabled', true);
        jQuery(dom_id).find('button').attr('disabled', true);
        //prepare data.
        var post = {};
        post.stream = {};
        post.stream.name = stream.name;
        post.stream.domain = stream.domain;
        post.stream.username = stream.username;
        post.stream.version = stream.version;
        var fields = [];

        jQuery.each(stream.fields, function (i, field_data) {
            if (i === 0) {
                return true;    // Skip the first row, as it is 1 based.
            }

            var field = {};
            var selected;
            field.display_order = field_data.display_order;        // Acts as a key.
            switch (field_data.type) {
                case 'textbox':
                    if (typeof textarea_instances[dom_id + '_' + i] !== 'undefined') {
                        field.text = textarea_instances[dom_id + '_' + i].getContent();
                    } else {
                        field.text = jQuery('#post_' + instance + '_field_' + i + '_text').val();
                    }

                    // Special case to fix non breaking spaces. This will need adaptting to allow for full html.
                    // CKeditor puts them in.
                    field.text = field.text.replace(/&nbsp;/g, '');

                    break;

                case 'link':
                    field.link = jQuery(dom_id + ' .post-field-' + i + '-link').val();
                    field.link_title = jQuery('#post_' + instance + '_field_' + i + '_text').val();
                    field.link_thumbnail_url = jQuery(dom_id + ' .post-field-' + i + '-link-thumbnail-url').val();
                    field.link_thumbnail_small_base64 =
                        jQuery(dom_id + ' .post-field-' + i + '-link-small-thumbnail-base16').val();
                    field.link_thumbnail_large_base64 =
                        jQuery(dom_id + ' .post-field-' + i + '-link-large-thumbnail-base16').val();
                    break;

                case 'checkbox':
                    var checked = jQuery('#post_' + instance + '_field_' + i + '_checkbox').attr('checked');
                    if (typeof checked !== 'undefined' && checked !== false) {
                        field.checked = true;
                    } else {
                        field.checked = false;
                    }
                    break;

                case 'list':
                    selected = [];
                        // Small list.
                    jQuery('#post_' + instance + '_list_' + i + ' .list-item.ui-state-active span')
                        .each(function () {
                            selected.push(jQuery(this).text());
                        });
                    field.selected = selected;
                    break;

                case 'openlist':
                    selected = jQuery(dom_id + ' .post-field-' + i + '-openlist').val().split(',');
                    // remove any empty fields or the server will error.
                    for (var count = 0; count < selected.length; count++) {
                        if (selected[count].trim() === '') {
                            selected.splice(count, 1);
                            count--;
                        }
                    }
                    field.selected = selected;
                    break;

                case 'value':
                    if (field_data.value_options === 'maxminpost') {
                        field.value_min = jQuery('#post_' + instance + '_field_' + i + '_value_min').val();
                        field.value_max = jQuery('#post_' + instance + '_field_' + i + '_value_max').val();

                    } else if (field_data.value_options === 'rhythmpost') {
                        field.rhythm = jQuery(dom_id + ' .post-field-' + i + '-value-rhythm').val();
                    }
                    break;
            }
            fields[field_data.display_order - 1] = field;
            return true; // Continue with the .each
        });

        post.content = fields;
        post.parent_id = parent_id;
        post.top_parent_id = top_parent_id;
        if (typeof post_id !== 'undefined') {
            post.post_id = post_id;
        }

        // Fetch all the private addresses, if any.
        var jq_private_messages = jQuery(dom_id + ' .private-post');
        var private_checked = jQuery('.private-post-check>input', jq_private_messages).is(':checked');
        post.private_addresses = [];
        if (private_checked === true) {
            var jq_not_empty_addresses = jQuery('input.address:not(.empty)', jq_private_messages);
            jq_not_empty_addresses.each(function() {
                post.private_addresses.push(jQuery(this).val());
            });
        }
        private_addresses = post.private_addresses;


        if (BabblingBrook.Client.Component.MakePost.makeFakePostHook(post) === true) {
            return;
        }
        jQuery(dom_id).find('.input-post-title').text('Saving post...');
        // Send the post. Once placed, a message is passed to BabblingBrookClient.processMadePost
        // and this.clearForm is called.
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                post : post
            },
            'MakePost',
            madePost,
            makePostError
        );
    };

    /**
     * Run this instances callback and delete from memory.
     * @param {object} post The new post to send back with the callback.
     */
    var runCallback = function () {
        if (typeof insertCallback === 'function') {
            insertCallback(post);
            BabblingBrook.Client.Component.MakePost.onInsertHook(post, private_addresses);
        }
    };

    /**
     * Open the details of the post form.
     */
    var openMainField = function () {
        if (jQuery(dom_id + ' .input-post-detail>div').hasClass('minimised') === false) {
            return;
        }

        jQuery(dom_id + ' .input-post-actions').removeClass('minimised');
        jQuery(dom_id + ' .input-post-detail>div').removeClass('minimised');
        // Recalculate the native width of textareas as teh precaluclated value may be wrong due
        // to the field being added in an invisible state, the css width was not yet appplied.


        // remove the width constraint from open lists.
        // This was inserted by the openlist js because the element was not displayed when it was created.
        jQuery(dom_id + ' .openlist-container>ul').css({'width' : ''});
        jQuery(dom_id + ' .input-post-title').removeClass('hide');
        jQuery(dom_id + ' .post-field-1-link-title').removeClass('hide');
        jQuery(dom_id + ' .post-field-1-link').removeClass('hide');
        jQuery(dom_id + ' .post-field-1-link-thumbnails').removeClass('hide');

        if (typeof textarea_instances[dom_id + '_1'] !== 'undefined') {
            textarea_instances[dom_id + '_1'].focus();
        }
    };

    /**
     * Close the details of the post form.
     */
    var closeMainField = function () {
        jQuery(dom_id + ' .input-post-actions').addClass('minimised');
        jQuery(dom_id + ' .input-post-detail>div').addClass('minimised');
        jQuery(dom_id + ' .input-post-title').addClass('hide');
        jQuery(dom_id + ' .post-field-1-link-title').addClass('hide');
        jQuery(dom_id + ' .post-field-1-link').addClass('hide');
        jQuery(dom_id + ' .post-field-1-link-thumbnails').addClass('hide');

        if (typeof textarea_instances[dom_id + '_1'] !== 'undefined') {
            textarea_instances[dom_id + '_1'].setContent('<p>' + invitation_to_write + '</p>');
        } else {
            jQuery('#post_' + instance + '_field_1_text').val(invitation_to_write);
        }

        // CKEditor can be very slow to change the display of cke_top when loosing focus, so do it here as well.
        if (typeof stream !== 'undefined') {
            var stream_fields_length = stream.fields.length;
            for (var i=0; i<stream_fields_length; i++) {
                if (typeof textarea_instances[dom_id + '_' + i] !== 'undefined') {
                    textarea_instances[dom_id + '_' + i].blurToolbar();
                }
            }
        }

    };

    /**
     * Turn on the graphics for post processing.
     */
    var postProcessingOn = function () {
        jQuery(dom_id + ' .post-processing').addClass('ajax-loading');
    };

    /**
     * Turn off the graphics for post processing.
     */
    var postProcessingOff = function () {
        jQuery(dom_id + ' .post-processing').removeClass('ajax-loading');
    };

    /**
     * Show the stream restricted message.
     *
     * Runs when a user is not permitted to post posts to this stream.
     *
     * @return void
     */
    var showStreamRestricted = function () {
        jQuery('#make_post_' + instance).slideUp();
        BabblingBrook.Client.Component.StreamNav.showRestrictedMessage();
    };

    /**
     * Validate the form before submittion.
     *
     * @param {boolean} [retried=false] Is this an automaic retry to validate the form.
     *      Used in an internal recall of the function is usernames are not validating, incase
     *      we are waiting for the loading icon to display.
     *
     * @return void
     */
    var validatePost = function (retried) {
        // Double check for errors to ensure the capture of fields that start in an error state.
        jQuery.each(stream.fields, function (i, field) {
            if (i === 0) {
                return true;    // Skip the first row, as it is 1 based.
            }

            switch (field.type) {
                case 'textbox':
                    checkTextErrors(stream.fields[i], i);
                    break;

                case 'link':
                    jQuery('#post_' + instance + '_field_' + i + '_text').trigger('change');
                    jQuery('#post_' + instance + '_field_' + i + '_text').trigger('blur');
                    jQuery(dom_id + ' .post-field-' + i + '-link').trigger('blur');
                    break;

                // no tests for checkboxes.

                case 'list':
                    var error_msg = generateListError(field);
                    showSmallListError(i, field.select_qty_min, field.select_qty_max, error_msg);
                    break;

                // No tests for open lists.
                case 'openlist':
                    showOpenListError(i, field.select_qty_min, field.select_qty_max);
                    break;

                case 'value':
                    if (field.value_options === 'maxminpost') {
                        jQuery('#post_' + instance + '_field_' + i + '_value_min').trigger('blur');

                    } else if (field.value_options === 'rhythmpost') {
                        console.error('rhythmpost not currently implemented.');
                        //if (jQuery(dom_id + ' .post-field-' + row + '-value-rhythm').val() === '')
                        //jQuery(dom_id + ' .post-field-' + row + '-error')
                        //  .text(field.label + required_error)
                        //  .removeClass('hide');
                    }
                    break;
            }
        });

        // Check for private post errors.
        var jq_private_messages = jQuery(dom_id + ' .private-post');
        var private_checked = jQuery('.private-post-check>input', jq_private_messages).is(':checked');
        jQuery('.private-post-none-selected', jq_private_messages).addClass('hide');
        jQuery('.private-post-error', jq_private_messages).addClass('hide');
        jQuery('.private-post-waiting', jq_private_messages).addClass('hide');
        if (private_checked === true) {
            var jq_not_empty_addresses = jQuery('input.address:not(.empty)', jq_private_messages);
            if (jq_not_empty_addresses.length === 0) {
                jQuery('.private-post-none-selected', jq_private_messages).removeClass('hide');
                return;
            }

            // Check for tick rather than error because it might be in process.
            var jq_address_not_valid = jQuery('input.address:not(.textbox-tick, .empty)', jq_private_messages);
            if (jq_address_not_valid.length > 0) {
                var jq_waiting = jQuery('.label-loading:not(.hide)', jq_private_messages);
                if (jq_waiting.length > 0) {
                    jQuery('.private-post-waiting', jq_private_messages).removeClass('hide');
                    setTimeout(validatePost.bind(null, false), 250);
                    return;
                } else {
                    if (retried === false) {
                        setTimeout(validatePost.bind(null, true), 220);
                        return;
                    } else {
                        jQuery('.private-post-error', jq_private_messages).removeClass('hide');
                    }
                }
            }
        }

        var jq_loading_thumbnails = jQuery(dom_id + ' .link-thumbnail-container');
        jq_loading_thumbnails = jq_loading_thumbnails.children('.thumbnails-loading').not('.hide');
        jq_loading_thumbnails = jq_loading_thumbnails.parent();
        var thumbnail_error = false;
        jQuery(dom_id + ' .thumbnails-loading-error').addClass('hide');
        if (jq_loading_thumbnails.length > 0) {
            jQuery(dom_id + ' .thumbnails-loading-error').removeClass('hide');
            thumbnail_error = true;
        }

        // Now check if any errrors are showing and shown/don't show the generic message.
        var jq_errors = jQuery(dom_id + ' .error:visible')
            .not('.post-submit-error')
            .not('.thumbnails-loading-error')
            .not('.post-submit-domus-error');
        if (jq_errors.length > 0) {
            displayFormError();

        // submit
        } else if (thumbnail_error === false) {
            jQuery(dom_id + ' .post-submit-error').addClass('hide');
            jQuery(dom_id + '.thumbnails-loading-error').addClass('hide');

            // If this is a edit, then pass the post_id through.
            if (typeof post !== 'undefined') {
                submitPost(post.post_id);
            } else {
                submitPost();
            }
        }

    };

    /**
     * Callback with stream details.
     *
     * Continues the setting up of the make post.
     *
     * @param {string} stream_data stream data See BabblingBrook.Models.stream for details.
     */
    var generatePostForm = function (stream_data) {
        // Check the validity of the stream_data.
        BabblingBrook.Models.stream(stream_data, 'BabblingBrook.Client.Component.MakePost stream request error.');

        var is_stream_owner_the_user = false;
        if (typeof stream_data !== 'undefined' && stream_data.domain === BabblingBrook.Client.User.domain
            && stream_data.username === BabblingBrook.Client.User.username
        ) {
            is_stream_owner_the_user = true;
        }

        var is_post_owner_the_user = false;
        if (typeof post !== 'undefined' && post.domain === BabblingBrook.Client.User.domain
            && post.username === BabblingBrook.Client.User.username
        ) {
            is_post_owner_the_user = true;
        }

        // Ascertain that the current user can make posts.
        if (stream_data.post_mode === 'owner' && is_stream_owner_the_user === false) {
            showStreamRestricted();
            return;
        }

        if (typeof post !== 'undefined' && stream_data.edit_mode === 'owner' && is_post_owner_the_user === false) {
            showStreamRestricted();
            console.error("User should not be shown the edit link if they can not edit a post.");
            return;
        }

        // Assign the content to an associative array for easy access.
        if (typeof post !== 'undefined') {
            jQuery.each(stream_data.fields, function (i, row) {
                if (i === 0) {
                    return true;    // Skip the first row as it is 1 based.
                }
                if (typeof post.content[row.display_order] !== 'undefined') {
                    post_rows[row.display_order] = post.content[row.display_order];
                } else {
                    post_rows[row.display_order] = {display_order : row.display_order};
                }
            });
        }

        var jq_detail = jQuery(dom_id + ' .input-post-detail');        // The container for the rest of the fields.

        stream = stream_data;
        // Save the stream to the prototype array of streams so it can be restored later.
        that.instances[instance].stream = stream_data;

        // Defaults if the top field is a link field.
        var first_field = stream.fields[1];
        if (first_field.type === 'link') {
            // Hide the link field if the form is closed.
            if (minimised_state === 'minimised') {
                closeMainField();
            }
        }
        // If there are additional fields then add them to the make post details div.
        jQuery.each(stream.fields, function (i, row) {
            if (i === 0) {
                return true;    // Skip the first row, as it is 1 based.
            }

            // Prepend in reverse order.
            var rrow = stream.fields.length - i;
            var field = stream.fields[rrow];
            switch (field.type) {
                case 'textbox':
                    generateTextBox(rrow, field, jq_detail);
                    break;

                case 'link':
                    generateLinkField(rrow, field, jq_detail);
                    break;

                case 'checkbox':
                    generateCheckbox(rrow, field, jq_detail);
                    break;

                case 'list':
                    generateList(rrow, field, jq_detail);
                    break;

                case 'openlist':
                    generateOpenList(rrow, field, jq_detail);
                    break;

                case 'value':
                    generateValueField(rrow, field, jq_detail);
                    break;
            }
            return true;    // Continue the jQuery.each function.
        });

        jQuery(dom_id + ' button.create-post').click(validatePost.bind(null, false));

        jQuery(dom_id).removeClass('block-loading');

        that.instances[instance].onReady(instance);

        if (minimised_state === 'open') {
            jQuery('#make_post_' + instance + ' .post-link-text').trigger('blur');
            jQuery(dom_id + ' .post-text-field').each(function(index, textarea) {
                var jq_textarea = jQuery(textarea);
                jq_textarea.data('x', jq_textarea.outerWidth());
                jq_textarea.data('max-x', jq_textarea.width());
                BabblingBrook.Client.Component.MakePost.ResizeTextarea(jq_textarea, edit_mode);
            });
        }

    };

    /**
     * Attatch the private message part of the form if it is required.
     *
     * NB Change event to the address line is a global event registered in a static function of this module.
     *
     * @param {object} jq_new_post A jQuery object that holds the new post form.
     * @param {string} private_post Privacy status of the post being made. Valid options are:
     *      private - automatically shows the 'to' address line.
     *      public - does not show the private checkbox.
     *      open - A checkbox is shown to the user to indicate a private post - when checked an address line opens.
     */
    var attatchPrivate = function (jq_new_post, private_post) {
        if (BabblingBrook.Settings.feature_switches['PRIVATE_POSTS'] === false) {
            return;
        }

        if (private_post === 'public' || typeof post !== 'undefined') {
            return;
        }
        var jq_private_post = jQuery('#private_post_template>div').clone();
        var jq_to_address = jQuery('#private_post_to_template>div').clone();
        var default_message  = jQuery('#private_post_first_message').text();
        jQuery('>.address', jq_to_address).val(default_message);
        var jq_private_checkbox = jQuery('>.private-post-check>input', jq_private_post);
        jq_private_post
            .find('.private-post-to')
            .append(jq_to_address);

        // Only include the checkbox if private is optional
        if (private_post === 'private') {
            jq_private_post
                .find('.private-post-to')
                .removeClass('hide');
            jq_to_address.removeClass('hide');
            jq_private_post
                .find('.private-post-check')
                .addClass('hide')
                .end()
                .find('.private-post-title')
                .removeClass('hide');
        } else {
            jq_private_checkbox.click(function() {
                jQuery('>.private-post-to', jq_private_post).toggleClass('hide');
            });
        }

        jq_new_post
            .find('.make-post-actions')
            .before(jq_private_post);
    };

    /**
     * Setup and make a hidden post - no post displayed on the scrren.
     *
     * Used on post page to make a new rating post - with fields correctly filled in.
     *
     * @param {object} stream Holds details about the stream being used to insert an post.
     * @param {string} [post_domain] If this is an edit, then this is the post domain.
     * @param {string} [post_id] If this is an edit, then this is the post_id.
     * @param {array} [content] The content to submit for the post. (see the main 'MakePost' call for sturcture.)
     *
     * @return void
     */
    this.setupHiddenPost = function (stream, content, post_domain, post_id) {

        hidden_post = true;

        var post = {
            stream : stream,
            content : content,
            post_id : post_id,
            post_domain : post_domain,
        };
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                post : post
            },
            'MakePost',
            madePost,
            makePostError
        );
    };

    /**
     * Setup the post form.
     *
     * @param {string} stream_url
     * @param {object} jq_container jQuery object containing a div to contain the post form.
     * @param {string} state The open state of the post when initialised. Valid values: 'open', 'minimised'
     * @param {number} pid The id of the parent post if this is a sub post.
     * @param {number} top_pid The Id of the parent post if this is a sub post.
     * @param {object} [post_to_edit] A standard post object. Used when an edit version
     *                                 of BabblingBrook.Client.Component.MakePost is created
     *                to setup the fields.
     * @param {string} [private_post=open] Privacy status of the post being made. Valid options are:
     *      private - automatically shows the 'to' address line.
     *      public - does not show the private checkbox.
     *      open - A checkbox is shown to the user to indicate a private post - when checked an address line opens.
     * @param {string|undefined} title The text to use for the title of the make post form. Can contain html.
     * @param {function} onReady Called when the post form has finished being displayed.
     *      Accepts one paramater : The instance id of the form.
     * @param {string} [make_text] The text to display on the make post button.
     * @param {string} [cancel_text] The text to display on the cancel button.
     * @param {string} [make_text] The text to display on the make post button when this is an edit.
     * @param {string} [cancel_text] The text to display on the cancel button when this is an edit.
     *
     * @return void
     */
    this.setupNewPost = function (stream_url, jq_container, state, pid, top_pid,
        post_to_edit, private_post, title, onReady, make_text, cancel_text, edit_text, cancel_edit_text
    ) {
        if (typeof post_to_edit !== 'undefined') {
            edit_mode = true;
        }
        if (typeof state === 'undefined') {
            state = 'minimised';
        }
        that.instances[instance].minimised_state = state;
        minimised_state = state;

        if (typeof onReady !== 'function') {
            onReady = function () {};
        }
        that.instances[instance].onReady = onReady;

        if (typeof private_post === 'undefined') {
            private_post = 'open';
        }

        post = post_to_edit;

        parent_id = pid;
        top_parent_id = top_pid;

        var make_post_label = "Make post";
        if (typeof make_text === 'string') {
            make_post_label = make_text;
        }
        var cancel_post_label = "Cancel post";
        if (typeof cancel_text === 'string') {
            cancel_post_label = cancel_text;
        }
        if (typeof post_to_edit === 'object') {
            make_post_label = "Edit post";
            if (typeof edit_text === 'string') {
                make_post_label = edit_text;
            }
            cancel_post_label = "Cancel edit";
            if (typeof cancel_edit_text === 'string') {
                cancel_post_label = cancel_edit_text;
            }
        }

        var make_post_title = invitation_to_write;
        if (typeof title === 'string') {
            make_post_title = title;
        }

        // Create the base html for the make post form.
        var jq_new_post = jQuery('#make_post_new_post_template').clone();
        jQuery('.input-post-title', jq_new_post).html(make_post_title);
        jQuery('.create-post', jq_new_post).text(make_post_label);
        jQuery('.cancel-post', jq_new_post).text(cancel_post_label);
        jq_container.attr('id', 'make_post_' + instance)
            .addClass('make-post')
            .attr('data-instance', instance)
            .html(jq_new_post.html());
        attatchPrivate(jq_container, private_post);

        // Set the initial state of the post form.
        if (minimised_state === 'minimised') {
            closeMainField();
        } else {
            openMainField();
        }

        // Bind the click event for canceling a post.
        jQuery(dom_id + ' button.cancel-post').click(function (event) {
            that.clearForm(false, true);
        });

        // Fetch the stream details.
        var onErrorFetchingStream = function (error_data) {    // Error.
            if (error_data.error_code === '404') {
                // Do not show a user erorr, as a 404 will be displayed by the stream page.
                console.error(jQuery('#error_fetching_stream_template').html());
                return;
            }

            var jq_message = jQuery('#error_fetching_stream_message_template');
            jQuery('.stream-url', jq_message).text(stream_url);
            BabblingBrook.Client.Component.Messages.addMessage({
                type : 'error',
                message : jq_message.html(),
                buttons : [{
                    name : 'Retry',
                    callback : function () {
                        BabblingBrook.Client.Core.Interact.postAMessage(
                            data,
                            'InfoRequest',
                            generatePostForm,
                            onErrorFetchingStream
                        );
                    }
                }]
            });
        };

        BabblingBrook.Client.Core.Streams.getStream(
            BabblingBrook.Library.extractDomain(stream_url),
            BabblingBrook.Library.extractUsername(stream_url),
            BabblingBrook.Library.extractName(stream_url),
            BabblingBrook.Library.extractVersion(stream_url),
            generatePostForm,
            onErrorFetchingStream
        );

    };

    /**
     * Clears the html for an post instance and removes it if it is not the top one (eg sub comment).
     *
     * @param {boolean} [run_callback=true] Shall we run the callback or not
     * @param {boolean} [run_cancel_callback=true] Shall we run the cancel callback or not
     *
     * @return void
     */
    this.clearForm = function (run_callback, run_cancel_callback) {
        if (typeof run_callback !=='boolean') {
            run_callback = true;
        }
        if (typeof run_cancel_callback !=='boolean') {
            run_cancel_callback = false;
        }

        // Run call back immediately if this post has no form on the screen.
        if (hidden_post) {
            if (run_callback === true) {
                runCallback();
            }
            return;
        }

        // Reset the form.
        // Minimise the main field.

        // Hide all errors first so they flicker as little as possible.
        jQuery(dom_id + ' .input-post-main .error').text('').addClass('hide');
        jQuery(dom_id + ' .input-post-detail .error').text('').addClass('hide');

        jQuery(dom_id).slideUp(250, function () {
            jQuery(dom_id)
                .empty()
                .attr('id', '')
                .show();
            if (run_callback === true) {
                runCallback();
            }

            if (run_cancel_callback === true) {
                cancelCallback();
            }
        });
    };

};
// This prototype object stores instance data - so instances can be recreated.
BabblingBrook.Client.Component.MakePost.prototype.instances = [];
BabblingBrook.Client.Component.MakePost.prototype.setup_run = false;

/**
 * The last partial user address that was sent as part of the private message feature.
 * Used to prevent duplicate requests being sent.
 * @type {string}
 */
BabblingBrook.Client.Component.MakePost.prototype.last_partial_request = '';

/*
 * An overridable hook that is called after an post has been created.
 */
BabblingBrook.Client.Component.MakePost.onInsertHook = function (post) {};

/**
 * A suggestion has been selected, edit the address accordingly.
 *
 * @param {object} jq_username A jQuery object representing the username input in the DOM.
 * @param {object} type IS the suggestion type a username or a domain.
 * @param {object} jq_selected A jQuery object representing the selected suggestion item in the DOM.
 *
 */
BabblingBrook.Client.Component.MakePost.privateAddressAppendSuggestion = function (jq_username, jq_selected) {
    'use strict';
    var new_username = jq_selected.text();
    jq_username.val(new_username);
    jq_username.focus();
    jQuery('#content_page .private-post-suggestions').remove();
    jq_username.blur();
};

/**
 * Callback to recieve a list of suggestions for a private message username and display them.
 *
 * Suggestions can be for domain or username.
 *
 * @param {object} jq_username A jQuery object representing the textbox for the address line in the DOM.
 * @param {object} suggestion_data The object passed back from the domus domain containg suggestion data.
 * @param {array} suggestions A List of suggested usernames based on the one submitted.
 *
 * @return void
 */
BabblingBrook.Client.Component.MakePost.showSuggestions = function (jq_username, suggestions) {
    'use strict';
    if (jq_username.is(':focus') === false ) {
        return;
    }

    // Remove any other instances of address suggestions incase they havn"t closed.
    jQuery('#content_page .private-post-suggestions').remove();

    if (suggestions.length === 0) {
        return;
    }

    var jq_suggested_address = jQuery('#templates>.private-post-suggestions').clone();
    var jq_suggestion_line = jQuery('#templates>.private-post-suggestions-line').clone();

    jQuery.each(suggestions, function (i, suggestion) {
        // Only a max of ten are supposed to be reuturned, but you never know.
        if (i > 9) {
            return false;   // Exit the .each
        }
        var jq_suggestion_new_line = jq_suggestion_line.clone();
        jq_suggestion_new_line.text(suggestion);
        jq_suggested_address.append(jq_suggestion_new_line);
        return true;        // Continue with the .each
    });

    jq_username.after(jq_suggested_address);

    // Setup the click event for selecting an option.
    jQuery('.private-post-suggestions>li').click(function () {
        BabblingBrook.Client.Component.MakePost.privateAddressAppendSuggestion(jq_username, jQuery(this));
    });
};

/**
 * Arrow keys have been used to select a suggestion
 *
 * @param {object} jq_address_line jQuery object representing the address line container in the DOM.
 * @param {object} jq_username jQuery object representing the address line textbox in the DOM.
 * @param {object} event The event object for the key press.
 *
 * @return void
 */
BabblingBrook.Client.Component.MakePost.privateSuggestionsSelected = function (jq_address_line, jq_username, event) {
    'use strict';
    var jq_suggestions = jQuery('>.private-post-suggestions', jq_address_line);
    if (jq_suggestions.length > 0) {
        var jq_next_suggestion;
        if (event.keyCode === 38 || event.keyCode === 40) {
            var jq_current_suggestion = jQuery('>.selected', jq_suggestions);
            var last_row_index = jQuery('li:last', jq_suggestions).index();
            // Up arrow key.
            if (event.keyCode === 38) {
                // not selected or at the top of the list.
                if (jq_current_suggestion.length === 0 || jq_suggestions.row_index === 0) {
                    jq_next_suggestion = jQuery('li:last', jq_suggestions);
                    jq_next_suggestion = jQuery('li:nth(' + (jq_current_suggestion.index() - 1) + ')', jq_suggestions);
                }
            }

            // Down arrow key.
            if (event.keyCode === 40) {
                // not selected or at the bottom of the list.
                if (jq_current_suggestion.length === 0 || jq_current_suggestion.row_index === last_row_index) {
                    jq_next_suggestion = jQuery('li:first', jq_suggestions);

                } else {
                    jq_next_suggestion = jQuery('li:nth(' + (jq_current_suggestion.index() + 1) + ')', jq_suggestions);
                }
            }
        }
        jQuery('>li', jq_suggestions).removeClass('selected');
        jq_next_suggestion.addClass('selected');
        jq_username.text(jq_next_suggestion.text());
        return;
    }
};

/**
 * A private message address line has gained focus.
 *
 * Remove the default message from it.
 *
 * @return void
 */
BabblingBrook.Client.Component.MakePost.privateAddressinFocus = function () {
    'use strict';
    var jq_username =  jQuery(this);
    var default_message  = jQuery('#private_post_first_message').text();
    var additional_message  = jQuery('#private_post_additional_message').text();
    if (jq_username.val() === default_message || jq_username.val() === additional_message) {
        jq_username.val('');
    }
    jq_username.removeClass('empty');
    jq_username.removeClass('textbox-tick textbox-cross');
};

/**
 * An address to send an post to has changed.
 *
 * @param {object} event The event object.
 *
 * @return void
 */
BabblingBrook.Client.Component.MakePost.privateAddressChanged = function (event) {
    'use strict';

    var jq_username =  jQuery(this);
    var that = this;
    var username = jq_username.val();

    var jq_address_line = jq_username.parent();

    // Add a new address line if the last one is in use.
    var default_message  = jQuery('#private_post_first_message').text();
    var additional_message  = jQuery('#private_post_additional_message').text();
    var last_line = true;

    if (jq_address_line.next().hasClass('private-post-to-line') === true) {
        last_line = false;
    }

    if (last_line === true && username !== default_message && username !== additional_message ) {
        var jq_extra_to_address = jQuery('#private_post_to_template>div').clone();
        jQuery('input.address', jq_extra_to_address).val(additional_message);
        jq_address_line.after(jq_extra_to_address);
    }

    // If this is an up arrow, or down arrow and suggestions are visible - then select the selection.
    if (event.keyCode === 38 || event.keyCode === 40) {
        BabblingBrook.Client.Component.MakePost.privateSuggestionsSelected(jq_address_line, jq_username, event);
        return;
    }
    // If return is pressed then select the current selected item if available and validate.
    if (event.keyCode === 13) {
        var jq_suggestion_list = jQuery('>.private-post-suggestions', jq_address_line);
        var type = 'domain';
        if (jq_suggestion_list.hasClass('username') === true) {
            type = 'username';
        }
        var jq_selected = jQuery('>.private-post-suggestions>li.selected', jq_address_line);
        if (jq_selected.length > 0) {
            BabblingBrook.Client.Component.MakePost.privateAddressAppendSuggestion(jq_username, jq_address_line, type, jq_selected);
        }
        return;
    }

    // Only proceed if the username has actually changed. Otherwise duplicate requests are generated.
    if (BabblingBrook.Client.Component.MakePost.prototype.last_partial_request === username) {
        return;
    }
    BabblingBrook.Client.Component.MakePost.prototype.last_partial_request = username;

    // Lookup the username to see if it has been found.
    // In a set timeout so that requests are only generated during a pause in the typing.
    setTimeout(function() {
        var current_username =  jQuery(that).val();
        // If there has been another event then escape this one and wait for the next.
        if (current_username !== username) {
            return;
        }

        BabblingBrook.Client.Core.LookupUser.suggest(
            username,
            BabblingBrook.Client.Component.MakePost.showSuggestions.bind(null, jq_username),
            function () {}  // Ignore errors - they will be caught when the address line looses focus.
        );
    }, 200);

};

/**
 * An address to send an post to has finished being edited. Ensure that the address is correct.
 *
 * @param {object} event The event object.
 *
 * @return void
 */
BabblingBrook.Client.Component.MakePost.privateAddressFinished = function (event) {
    'use strict';
    var jq_username =  jQuery(this);
    var jq_address_line = jq_username.parent();

    // Pause so that clicks on the suggestion box can be detected.
    setTimeout(function() {

        // Is it really finished or has the suggestion box been clicked.
        if (jQuery(event.currentTarget).is(':focus') === true) {
            return;
        }

        jQuery('#content_page .private-post-suggestions', jq_address_line).remove();
        jq_username.removeClass('textbox-tick textbox-cross');
        jQuery('>.address-error', jq_address_line).addClass('hide');

        // Restore default message if it is needed.
        if (jq_username.val() === '') {
            var default_message  = jQuery('#private_post_first_message').text();
            var additional_message  = jQuery('#private_post_additional_message').text();
            var message;
            if (jq_address_line.index() === 0) {
                message = default_message;
            } else {
                message = additional_message;
            }
            jq_username
                .val(message)
                .addClass('empty');
            return;
        }

        var username = jq_username.val();
        jQuery('.label-loading', jq_address_line).removeClass('hide');
        BabblingBrook.Client.Core.LookupUser.valid(username, function (valid, error_type) {
            jQuery('.label-loading', jq_address_line).addClass('hide');
            if (valid === false) {
                var error_message;
                if (error_type === 'domain') {
                    error_message = jQuery('#private_post_domain_error').text();
                } else {
                    error_message = jQuery('#private_post_username_error').text();
                }
                jQuery('>.address-error', jq_address_line)
                    .removeClass('hide')
                    .text(error_message);
                jq_username.addClass('textbox-cross');
            } else {
                jq_username.addClass('textbox-tick');
            }
        });

    }, 200);
};

/**
 * Set or reset the autoResize feature for textareas.
 *
 * @param {object} jq_textarea The jQuery object representing the textarea to apply autoresize to.
 * @param {boolean} trigger_keyup Should the keyup trigger be pressed. This will reformat the textarea
 *      but it will also trigger error checking.
 *
 * @return void
 */
BabblingBrook.Client.Component.MakePost.ResizeTextarea = function (jq_textarea, trigger_keyup) {
    jq_textarea.autoResize({extraSpace : 16});
    if (trigger_keyup === true) {
        jq_textarea.trigger('keyup');
    }
}

/**
 * Setup click event handlers.
 *
 * In a static function because these are live document level events.
 * This is so that make post objects do not need to be maintained after creation.
 *
 * @return void;
 */
BabblingBrook.Client.Component.MakePost.setupEvents = function() {
    'use strict';
    if (BabblingBrook.Client.Component.MakePost.prototype.setup_run === true) {
        return;
    }

    jQuery(document).on(
        'keyup focusout',
        '.private-post-to-line>.address',
        BabblingBrook.Client.Component.MakePost.privateAddressChanged
    );
    jQuery(document).on(
        'focusin',
        '.private-post-to-line>.address',
        BabblingBrook.Client.Component.MakePost.privateAddressinFocus
    );
    jQuery(document).on(
        'focusout',
        '.private-post-to-line>.address',
        BabblingBrook.Client.Component.MakePost.privateAddressFinished
    );

    // Rerun the textbox autoresize when the window resizes.
    var resize_timer;
    jQuery(window).resize(function () {
        // clear any recently generated timouts to prevent firing too rapidly during a window drag.
        clearTimeout(resize_timer);
        resize_timer = setTimeout(function(){
            var jq_textareas = jQuery('#content_page .post-text-field:visible').not('.resize-clone');
            jq_textareas.each(function(index, textarea) {
                var jq_textarea = jQuery(textarea);
                // Reset the default widths for the textarea.
                jq_textarea.width('');
                jq_textarea.data('x', jq_textarea.outerWidth());
                jq_textarea.data('max-x', jq_textarea.width());
                BabblingBrook.Client.Component.MakePost.ResizeTextarea(jq_textarea, true);
            })
        }, 100);
    });
};

/**
 * A hook that can be overridden to prevent the post from being submitted. Return true when overriding.
 *
 * @param {type} post
 *
 * @returns {Boolean}
 */
BabblingBrook.Client.Component.MakePost.makeFakePostHook = function (post) {
    return false;
}

jQuery(function () {
    'use strict';
    // Global click event to hide open private post address suggestions.
    jQuery('body').on('click', ':not(.private-post-suggestions>li)', function () {
        jQuery('#content_page .private-post-suggestions').remove();
    });

});