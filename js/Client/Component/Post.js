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
 * @fileOverview Creates post display modules.
 * @author Sky Wickenden
 */

/**
 * @namespace Object for creating and managing the display of individual posts.
 * Can be provided with a customisable format, for streams trees etc.
 *
 *  All events in this class, with the exception of the cooldown event, are in static functions.
 *  This is so that they can be accessed globaly using document wide 'on' events without having to
 *  maintain the original DisplayPost object in memory.
 *
 * @param {object} post A standard post object that is being used for display.
 *      Tree features will only be present if parent_id is present in the post.
 * @param {object} jq_location A jQuery object pointing to the location to insert the post into the page.
 *      This jQuery element will be replaced with the real post - it needs a dummy location.
 *      An child posts in ul.children will be preserved. Any other data will be removed.
 * @param {object} [jq_template] A jQuery DOM object representing the template to use when displaying an post.
 *      For a html example see the templates section of /views/layouts/main.php for the default template.
 *      The template contains three levels. The top level is a div that contains the post. It must have a
 *      class called 'post'.
 *      The second level contains a set of container divs. There can be as many of these as desired. This class is
 *      agnostic to the class names and ids of these container divs. The only constraints are that the third layer
 *      is contained in these divs.
 *      An exception to this is for the display of child posts which use a second level ul.children selector and it
 *      does not have a third layer.
 *      The third layer contains the individual items to display in the post. Such as the username. The following
 *      items are automatically included as long as the indicated tag and class is in an item element. Additional
 *      features can be included; reference them in the displayCallback.
 *          a.username The username that submitted the post. Full name is included in the title.
 *          time.time-ago Displays the time that an post was submitted.
 *          .field-<n> A specific location for a field value. The class 'field' must also be attatched.
 *              The parent div will be replaced with the widget for this field and the field will not
 *              appear in the fields tag. Any classes attatched to the parent div will be maintained.
 *              This can have any tag type. if however it is to be a link then it must be an 'a' tag.
 *              If a class named 'post-link' is included then the clicking on the field
 *              will link through to the post page.
 *              If a class named 'no-lable' is included then the fields label will not be shown.
 *          a.link-to-post A link to the main page for the post.
 *          .child-count Displays the number of child posts under this one.
 *          a.parent-post A link to the parent post. Only displayed if there is a parent post and it is not visible.
 *          a.full-thread A link to the top parent post.
 *              Only displayed if there is a top parent post and it is not visible.
 *          span.delete Creates a link that allows the post to be deleted if the creater of the post is viewing it.
 *              Needs to be link styled. Required span.deleted and span.delete-confirm to function.
 *          span.deleted Shows a message after an post has been deleted.
 *              When an post is deleted a class will be added to the top level of the template. Elements
 *              can be styled accordingly from this.
 *          span.delete-confirm Shows the confirmation request. It needs to contain two child tags
 *              span.delete-confirmed and span.delete-canceled which are displayed as links.
 *          span.cooldown Show the cooldown message for newly created posts.
 *          span.cooldown-time Show the countdown of the cooldown.
 *          span.post-loading Show the post loading image when an action is being processed.
 *          span.post-error Show any error messages that result from post actions.
 *          ul.children Show children for the current post.
 *          span.switch For showing an open close switch. All container divs have a 'switch-off' class toggled.
 *              Because it is toggled, a container can be made vissible when others are switched off.
 *              The container for the switch class is never classed with switch-off
 *              The span.switch is also toggled with a switch-flipped class.
 *          span.kindred-intro Container and intro for the viewers kindred score with the creater of the post.
 *              .kindred-score Displays the kindred score.
 *              .username The username of the post maker.
 *          span.revision The revision number for this post. Needs to be link styled.
 *          span.new-version A link to update the verison of an post when a revision is detected.
 *              Needs to be link styled.
 *          span.fields All the fields for this post that have not been specifically assigned will be attatched here
 *              Using the display order provided by the stream. The span.fields tag will be replaced with multiple
 *              widget tags for each field.
 *          span.post-rings The drop down post rings menu for making ring takes on an post.
 *              Needs to include two additional tags.
 *              span.ring-title Contains the text for the ring menu. Needs to be link styled.
 *              ul An empty ul, will contain the list of rings.
 *          span.show-new-posts A link to show new posts when they have been detected. Needs to be link styled.
 *          span.edit A link to edit an post if it is owned by the viewer. Needs to be link styled.
 *          span.post-reply A container for the link to reply to this post with a sub post. Needs to be link styled.
 *              .reply-location must be included in the template for this to work.
 *              Needs to include two additional tags.
 *              span.reply-title Contains the text for the ring menu. Needs to be link styled.
 *                  The link to click on to reply to the stream. If there is one child stream it will open the
 *                  reply form.
 *                  If there is more than one reply form it will open the reply-streams ul.
 *              ul.reply-streams An empty ul, will contain the list of child streams to select which one to reply with.
 *                  This will only be displayed if there is more than one child stream.
 *          A link to reply to this post with a sub post. Needs to be link styled.

 *
 *          span.reply-location The location the the reply form is displayed.
 *          span.hide-post A link to hide an post. Needs to be link styled.
 *          span.update A message to indicate that an post has been updated. Should include a span.show-update to
 *              display the update.
 *          img.thumbnail A place to display a thumbnail for a link.
 *          .post-thumbnail-container A container for the thumnail. The 'hide' class is removed when a thumbnail
 *              image is available.
 * @param {string} reply_path A jQuery path used to prepend replies.
 * @param {object} [jq_reply_template] A jQuery DOM object representing the template to use when
 *      displaying a reply to this post (after the post has been made).
 * @param {function} [displayCallback] This is called before the post is displayed and can be used to apply additional
 *      features to the display of the post. It accepts one paramater - the current implementation of the template.
 * @param {boolean} [update=false] Is this an update. If so then the post will be hidden. If the post already exists
 *      in the dom then the new version will be dispalyed in a hidden secondray divs until the update link is pressed -
 *      when the old ones will be deleted and the new ones displayed.
 * @param {boolean} [slide=true] Should the post slide into view.
 * @param {boolean} [show_empty=true] If a field is empty should the label be displayed
 * @param {object} [reload_images=false] Should image thumbnails be reloaded.
 * @param {function} [onReplied] A callback for after a post has been written in response to this post.
 *
 * @return void
 */
BabblingBrook.Client.Component.Post = function (post, jq_location, jq_template, reply_path,
        jq_reply_template, displayCallback, update, slide, show_empty, reload_images, onReplied
) {
    'use strict';
    /**
     * A clone of jq_template or the default template.
     * Used to implement this posts instance of the template
     *
     * @type {object}
     */
    var jq_post;
    /**
     * Is this post owned by the logged on user.
     *
     * @type {boolean}
     */
    var is_owned = false;

    /**
     * An array of field ids that are defined seperatly in the template
     *
     * These should not be created in the main content area.
     *
     * @type {array}
     */
    var extracted_fields = [];

    /**
     * @type {object} The stream object that holds this post.
     */
    var stream;

    var jq_hide;
    var jq_username;
    var jq_time_ago;
    var jq_individual_fields;
    var jq_link_to_post;
    var jq_child_count;
    var jq_link_to_parent;
    var jq_link_to_full_thread;
    var jq_delete;
    var jq_deleted;
    var jq_cooldown;
    var jq_cooldown_time;
    var jq_post_loading;
    var jq_post_error;
    var jq_kindred_score;
    var jq_kindred_intro;
    var jq_revision;
    var jq_post_content;
    var jq_post_rings;
    var jq_edit_post;
    var jq_reply;
    var jq_stream;
    var jq_thumbnail;
    var jq_thumbnail_container;

    /**
     * @type {string} The url of the stream that the post resides in. Created from the post data.
     */
    var stream_url;

    /**
     * Callback for the cooldown. Called every second to update the time until it has runout.
     *
     * @param {boolean} [first=false] Is this the first time the callback is called for this post.
     *      Used for first run setup.
     *
     * @return void
     */
    var cooldownCallback = function (first) {
        if (first === true) {
            jq_cooldown.removeClass('hide');
            jq_cooldown_time.removeClass('hide');
        }
        var now = Math.round(new Date().getTime() / 1000);
        // Subtract 10 seconds from the cooldown to prevent last moment deletions getting stuck in traffic.
        var cooldown_time = stream.cooldown - 10;
        var cooldown_minutes = Math.floor(cooldown_time/60);
        var cooldown_seconds = cooldown_time - (cooldown_minutes * 60);
        var seconds_passed = now - post.timestamp;
        var seconds_in_first_minute = cooldown_time % 60;
        if (seconds_passed > cooldown_time - 1) {   // Stop at 1 second, not zero.
            jq_cooldown.remove();
            jq_cooldown_time.remove();
        } else {
            // The first part of the countdown might not be a wholeminute so it has to be accounted for seperately.
            var minutes_passed = Math.floor((seconds_passed - seconds_in_first_minute) / 60);
            if (minutes_passed < 0) {
                minutes_passed = 0;
            }
            if (seconds_passed > seconds_in_first_minute) {
                minutes_passed ++;
            }
            var seconds_in_minute = seconds_passed - (minutes_passed * 60);
            var minutes = cooldown_minutes - minutes_passed;
            var seconds = cooldown_seconds - seconds_in_minute;
            if (seconds.toString().length === 1) {
                seconds = '0' + seconds.toString();
            }
            jq_cooldown_time.text(minutes + ':' + seconds);
            setTimeout(cooldownCallback, 1000);
        }
    };

    /**
     * Prepare the username element in the template.
     *
     * @return void
     */
    var prepareUsername = function () {
        jq_username
            .attr('href', '/' + post.username)
            .attr('title', 'Made by : ' + post.domain + '/' + post.username)
            .html(post.username);
    };

    /**
     * Prepare the username element in the template.
     *
     * @return void
     */
    var prepareTimeAgo = function () {
        var post_date = new Date(post.timestamp * 1000);
        jq_time_ago
            .attr('title', post_date.toString())
            .text(BabblingBrook.Library.timeAgoDate(post.timestamp));
    };

    /**
     * Prepare the title element in the template.
     *
     * @param {object} jq_field The div in the template that holds this textbox value.
     * @param {number} field The id of the field in the post.
     *
     * @return void
     */
    var prepareValueField = function (jq_field, field_id) {
        if (BabblingBrook.Settings.feature_switches['VOTE_POSTS'] === false) {
            jq_field.addClass('hide');
        }
        if (BabblingBrook.Settings.feature_switches['VOTE_COMMENTS'] === false
            && stream.name !== 'beautiful'
        ) {
            jq_field.addClass('hide');
        }

        switch (stream.fields[field_id].value_type) {
            case 'updown':
                new BabblingBrook.Client.Component.Value.Arrows(jq_field, field_id, post);
                break;

            case 'textbox':
                new BabblingBrook.Client.Component.Value.Textbox(jq_field, field_id, post, stream);
                break;

            case 'button':
                new BabblingBrook.Client.Component.Value.Button(jq_field, field_id, post);
                break;

            case 'linear':
                new BabblingBrook.Client.Component.Value.Slider(jq_field, field_id, post, stream);
                break;

            case 'logarithmic':
                new BabblingBrook.Client.Component.Value.Slider(jq_field, field_id, post, stream);
                break;

            case 'stars':
                new BabblingBrook.Client.Component.Value.Stars(jq_field, field_id, post, stream);
                break;

            case 'list':
                new BabblingBrook.Client.Component.Value.List(jq_field, field_id, post, stream);
                break;
        }
    };

    /**
     * Prepare a textbox field element in the template.
     *
     * @param {object} jq_field The div in the template that holds this textbox value.
     * @param {number} field_id The id of the field in the post.
     * @param {boolean} post_link Does this field link to the post.
     *
     * @return void
     */
    var prepareTextboxField = function (jq_field, field_id, post_link) {
        var content = post.content[field_id].text;
        if (field_id === 1) {
            jQuery('.title>a', jq_post).removeClass('block-loading');
            if (parseInt(stream.fields[1].max_size) > 200) {
                var jq_title = jQuery('.title>a', jq_post);
                var title_content = jq_title.html();
                //jq_field.replaceWith('<span />').html(title_content);
            }
        } else {
            // Replace newlines with br tags in content if this is not the title.
            content = content.replace(/\n/g, '<br />');
        }

        // remove all html from the content if it is being displayed as a link.
        if (field_id === 1 && parseInt(stream.fields[1].max_size) <= 200) {
            var empty_ruleset = {
                elements : {},
                styles : {}
            }
            var rich_text_instance = BabblingBrook.Client.Component.RichTextFacade();
            content = rich_text_instance.testHtmlFragment(content, empty_ruleset);
        }

        jq_field
            .html(content)
            .addClass('textbox-field');
        if (post_link === true) {
            jq_field.attr('href', '/post/' + post.stream_domain + '/' + post.post_id);
        }

        // If the first field is longer than 200 characters then display it as text rather than a link to the post
        // Also make the text smaller and a fixed width.
        if (field_id === 1) {
            if (parseInt(stream.fields[1].max_size) > 200) {
                var title_content = jq_field.html();
                title_content = title_content.replace(/\n/g, '<br />');
                var attributes = jq_field.prop("attributes");
                jq_field.replaceWith('<div>' + title_content + '</div>');
                jQuery.each(attributes, function() {
                    jQuery('.title>div', jq_post).attr(this.name, this.value);
                });
                jQuery('.title>div', jq_post).addClass('blocktext');
            }
        }

    };

    /**
     * Prepare a link field element in the template.
     *
     * @param {object} jq_field The div in the template that holds this textbox value.
     * @param {number} field_id The id of the field in the post.
     *
     * @return void
     */
    var prepareLink = function (jq_field, field_id) {
        var jq_new_field = jQuery('<a class="field-' + field_id + ' field"></a>')
            .attr('href', post.content[field_id].link)
            .html(post.content[field_id].link_title);
        var link_domain = BabblingBrook.Library.extractDomain(post.content[field_id].link);
        if (link_domain !== window.location.hostname) {
            jq_new_field.attr('target', '_blank');
        }
        jq_field.replaceWith(jq_new_field);
        if (typeof post.content[field_id].link_thumbnail_url === 'string'
            && post.content[field_id].link_thumbnail_url.length > ''
        ) {
            var image_reload_extension = '';
            if (typeof reload_images === 'boolean' && reload_images === true) {
                image_reload_extension = '?' + Math.floor(Math.random() * 10000);
            }
            var src = 'http://' + post.domain + '/images/user/' + post.domain + '/' + post.username +
                '/post/thumbnails/small/' + post.post_id + '/' + field_id + '.png' + image_reload_extension;
            jq_thumbnail.attr('src', src);

            jq_thumbnail_container
                .attr('href', post.content[field_id].link)
                .removeClass('hide');
            jq_thumbnail.error(function () {
                jq_thumbnail.attr('src', '/images/ui/post-thumbnail-not-found.png');
            });
        }

        var thumb_size = 'small';
        jQuery('.post-thumbnail', jq_post).click(function (event) {
            if (thumb_size === 'small') {
                thumb_size = 'large';
                var jq_thumbnail = jQuery(this);
                jq_thumbnail.parent().addClass('large-thumbnail block-loading');
                jq_thumbnail
                    .attr('src', jq_thumbnail
                    .attr('src').replace('/small/','/large/'))
                    .load(function () {
                        jq_thumbnail.parent().removeClass('block-loading');
                    });
            }else {
                thumb_size = 'small';
                var jq_thumbnail = jQuery(this);
                jq_thumbnail.parent().removeClass('large-thumbnail').addClass('block-loading');
                jq_thumbnail
                    .attr('src', jq_thumbnail
                    .attr('src').replace('/large/','/small/'))
                    .load(function () {
                        jq_thumbnail.parent().removeClass('block-loading');
                    });
            }

            event.stopPropagation();
            return false;
        });
    };

    /**
     * Prepare a checkbox field element in the template
     *
     * @param {object} jq_field The div in the template that holds this textbox value.
     * @param {number} field_id The id of the field in the post.
     *
     * @return void
     */
    var prepareCheckbox = function (jq_field, field_id) {
        var checked = 'No';
        if (post.content[field_id].checked === true) {
            checked = 'Yes';
        }
        jq_field.html(checked);
    };

    /**
     * Prepare a list field element in the template
     *
     * @param {object} jq_field The div in the template that holds this textbox value.
     * @param {number} field_id The id of the field in the post.
     *
     * @return void
     */
    var prepareList = function (jq_field, field_id) {
        var list_type = stream.fields[field_id].type;
        jq_field.html('<ul class="list ' + list_type + '"></ul>');
        var jq_list = jQuery('ul', jq_field);
        if (typeof post.content[field_id] !== 'undefined' && typeof post.content[field_id].selected !== 'undefined') {
            jQuery.each(post.content[field_id].selected, function(index, list_item) {
                jq_list.append('<li>' + list_item + '</li>');
            });
        }
    };

    /**
     * Show the label for a field.
     *
     * @param {object} jq_field The container for this field.
     * @param {number} field_id The id of the field in the stream that is being prepared. 1 based.
     *
     * @return void
     */
    var showFieldLabel = function(jq_field, field_id) {
        if (show_empty === false
            && typeof post.content[field_id].text !== 'undefined'
            && post.content[field_id].text.length === 0
        ) {
            return;
        }

        var label = stream.fields[field_id].label;
        jq_field.before('<span class="field-label field-label-' + field_id + '">' + label + '</span>');
    };

    /**
     * Work out which kind of field is being prepared and prepare it accordingly.
     *
     * @param {object} jq_field The container for this field.
     * @param {number} field_id The id of the field in the stream that is being prepared. 1 based.
     * @param {object} [options] Additional option settings.
     * @param {boolean} [options.post_link=false] If this is a text field, does it link to the post.
     * @param {boolean} [options.show_label=true] Should we show the label for this field.
     *
     * @return void
     */
    var prepareField = function (jq_field, field_id, options) {

        // Only display a value field to the owner if the stream specifies it.
        if (stream.fields[field_id].type === 'value' && stream.fields[field_id].who_can_take === 'owner') {
            if (stream.username !== BabblingBrook.Client.User.username
                || stream.domain !== BabblingBrook.Client.User.domain
            ) {
                return;
            }
        }

        var has_content = true;
        var content = post.content[field_id];
        if (typeof post.content[field_id] === 'undefined') {
            has_content = false;
        } else if ((stream.fields[field_id].type === 'textbox' && content.text.length < 1)
            || (stream.fields[field_id].type === 'link' && content.link.length < 1)
        ) {
            jq_field.addClass('hide');
            has_content = false;
            return;
        }

        if (typeof options === 'undefined') {
            options = {};
        }
        if (typeof options.post_link !== 'boolean') {
            options.post_link = false;
        }
        if (typeof options.show_label !== 'boolean') {
            options.show_label = true;
        }
        if (options.show_label === true) { // && has_content === true) {
            showFieldLabel(jq_field, field_id);
        }
        extracted_fields.push(field_id);
        switch (stream.fields[field_id].type) {
            case 'textbox':
                prepareTextboxField(jq_field, field_id, options.post_link);
                break;

            case 'link':
                prepareLink(jq_field, field_id);
                break;

            case 'checkbox':
                prepareCheckbox(jq_field, field_id);
                break;

            case 'list':
                prepareList(jq_field, field_id);
                break;

            case 'openlist':
                prepareList(jq_field, field_id);
                break;

            case 'value':
                prepareValueField(jq_field, field_id);
                break;
        }

    };

    /**
     * Checks if a field is in the post - as long as it is not a value field.
     *
     * @parm {number} field_id The index of the field.
     *
     * @return boolean
     */
    var checkIfFieldInPost = function(field_id) {
        var type = stream.fields[field_id].type;
        if (type === 'value') {
            return true;
        }
        if (typeof post.content[field_id] === 'undefined') {
            return false;
        }
        return true;
    };

    /**
     * Prepare any individually defined fields in the template.
     *
     * @return void
     */
    var prepareIndividualFields = function () {
        jq_individual_fields.each(function (i, field) {
            var jq_field = jQuery(field);
            // Iterate through the attatched classes to find the field.
            var class_list = jq_field.attr('class').split(/\s+/);
            var post_link = false;
            var show_label = false;
            var field_id;
            jQuery.each(class_list, function(j, class_name) {
                if (class_name.substr(0,6) === 'field-') {
                    field_id = class_name.substr(6);
                }
            });
            if (typeof jq_field.attr('data-post-link') !== 'undefined') {
                post_link = true;
            }
            if (typeof jq_field.attr('data-show-label') !== 'undefined') {
                show_label = true;
            }

            if (BabblingBrook.Library.isInt(field_id) === false) {
                console.error(
                    'Any assigning class names in post templates must be named field-n where n is the field number'
                );
                throw 'Thread execution stopped.';
            }
            field_id = parseInt(field_id, 10);

            // Check the field exists in the stream.
            if (typeof stream.fields[field_id] === 'undefined') {
                console.error(
                    'Field number ' + field_id + ' in the template does not exist in the stream.'
                );
                throw 'Thread execution stopped.';
            }

            var field_exists = checkIfFieldInPost(field_id);
            if(field_exists === false) {
                console.log("Field does not exist for post");
                return;
            }

            var options = {
                post_link : post_link,
                show_label : show_label
            };
            prepareField(jq_field, field_id, options);
        });

    };

    /**
     * Is this field one of the extracted fields.
     *
     * @param {number} field_id The field to check. 1 based.
     *
     * @return boolean
     */
    var isInExtractedFields = function (field_id) {
        var extracted = false;
        jQuery.each(extracted_fields, function(index, extracted_id) {
            if (field_id === extracted_id) {
                extracted = true;
                return false;   // exit the .each
            }
            return true;        // Continue the .each
        });
        return extracted;
    };

    /**
     * Prepare the individual fields for the post template.
     *
     * Skips fields that have been displayed elsewhere.
     *
     * @return void
     */
    var prepareFields = function () {

        var jq_parent_content = jq_post_content.parent();
        jq_parent_content.remove('>.post-content');
        if (jq_parent_content.length < 1) {
            return;
        }

        jQuery.each(stream.fields, function (index) {
            if (index === 0) {
                return  true; // Skip the first row as the array is 1 based.
            }

            var isExtracted = isInExtractedFields(index);
            if (isExtracted === true) {
                return true;        // Continue with the next .each
            }

            jq_parent_content.append('<div class="field-' + (index) + ' field"></div>');
            var jq_field = jQuery('>div.field:last', jq_parent_content);

            prepareField(jq_field, index, {});

            return true;        // Continue with the .each
        });

    };

    /**
     * Prepare to show all the post fields that have not yet been displayed.
     *
     * Check to see if the full post object has been loaded. If it hasn't then load it before displaying.
     *
     * @param {boolean} retest Is this function being called after the post has been reloaded.
     *
     * @return void
     *
     * Need to catch these on the server when the data is generated.
     */
    var preparePostContent = function (retest) {
        if (typeof retest === 'undefined') {
            retest = false;
        }
        if (jq_post_content.length === 0) {
            return;
        }
        if (post.status === 'deleted') {
            return;
        }

        prepareFields();
    };

    /**
     * Prepare the link to the post element in the template.
     *
     * @return void
     */
    var prepareLinkToPost = function () {
        if (BabblingBrook.Settings.feature_switches['READ_COMMENTS'] === false) {
            jq_link_to_post.addClass('hide');
        }
        if (jq_link_to_post.text() === 'link' && BabblingBrook.Settings.feature_switches['LINK_COMMENTS'] === false) {
            jq_link_to_post.addClass('hide');
        }
        jq_link_to_post.attr('href', '/post/' + post.domain + '/' + post.post_id);
    };

    /**
     * Prepare the link to the post element in the template.
     *
     * @return void
     */
    var prepareChildCount = function () {
        jq_child_count.text(post.child_count);
    };

    /**
     * Setup the delete link element in the template.
     *
     * @return void
     */
    var prepareDeleteLink = function () {
        // If an post is private it can be deleted by the receipient as well as the sender.
        if (is_owned === true || post.status === 'private') {
            jq_delete.removeClass('hide');
        } else {
            jq_delete.addClass('hide');
        }
    };

    /**
     * Setup the deleted message element in the template.
     *
     * @return void
     */
    var prepareDeleted = function () {
        jq_deleted.addClass('hide');
    };

    /**
     * Setup the cooldown message element in the template.
     *
     * This displays a cooldown countdown if the post is new
     * during which the user is guaranteed to be able to delete the post.
     *
     * @return void
     */
    var prepareCooldown = function () {
        jq_cooldown.addClass('hide');
        if (is_owned === true) {
            // this will automatically escape if cooldown has already passed.
            cooldownCallback(true);
        } else {
            jq_cooldown_time.remove();
            jq_cooldown.remove();
        }
    };

    /**
     * Setup the post loading element in the template.
     *
     * @return void
     */
    var preparePostLoading = function () {
        jq_post_loading.addClass('hide');
    };

    /**
     * Setup the post error element in the template.
     *
     * @return void
     */
    var preparePostError = function () {
        jq_post_error.addClass('hide');
    };

    /**
     * Callback to show the kindred score once the kindred data has loaded.
     */
    var showKindredScore = function () {
        if (BabblingBrook.Settings.feature_switches['KINDRED_SCORE'] === false) {
            jq_kindred_intro.addClass('tutorial-hide');
            return;
        }

        BabblingBrook.Client.Core.Loaded.onKindredLoaded(function () {
            var score = 0;
            if (typeof BabblingBrook.Client.User.kindred[post.domain + '/' + post.username] !== 'undefined') {
                score = BabblingBrook.Client.User.kindred[post.domain + '/' + post.username];
            }

            jQuery('.username', jq_kindred_intro)
                .text(post.username)
                .attr('title', post.domain + '/' + post.username);
            jq_kindred_score.html(score.toString());
        });
    };

    /**
     * Setup the post error element in the template.
     *
     * @return void
     */
    var prepareKindredScore = function () {
        if (jq_kindred_score.length < 1 || jq_kindred_intro.length < 1) {
            return;
        }
        if (post.username === BabblingBrook.Client.User.username
            && post.domain === BabblingBrook.Client.User.domain
        ) {
            jq_kindred_intro.addClass('hide');
            return;
        }

        BabblingBrook.Client.Core.Loaded.onKindredLoaded(showKindredScore);
    };

    /**
     * Setup the ring menu in the template.
     *
     * @return void
     */
    var prepareRingMenu = function () {
        if (BabblingBrook.Settings.feature_switches['MODERATING_POSTS'] === false) {
            jq_post_rings.remove();
            return;
        }

        if (post.status === 'deleted') {
            jq_post_rings.remove();
            return;
        }
        if (jq_post_rings.length < 1) {
            return;
        }
    };

    /**
     * Setup the prepare revision element in the template.
     *
     * jq_revision needs to cotain a child span classed revision-content. This is where the revision content
     * will appear.
     * The revision tag is only shown if an post has been revised.
     *
     * @return void
     */
    var prepareRevision = function () {
        var jq_content = jQuery('>.revision-content', jq_revision);
        jq_content.html(post.revision);
        if (parseInt(post.revision, 10) !== 1 && jq_revision.length > 1) {
            jq_revision.removeClass('hide');
        }
    };

    /**
     * Setup a link to edit the post if it is owned by the logged in user.
     *
     * Click event is global - see BabblingBrook.Client.Component.Post.editEvent
     *
     * @return void
     */
    var prepareEdit = function () {
        if (jq_edit_post.length < 1) {
            return;
        }

        var is_viewer_the_owner = false;
        if (BabblingBrook.Client.User.username === post.username
            && BabblingBrook.Client.User.domain === post.domain
        ) {
            is_viewer_the_owner = true;
        }

        // Show the edit link to everyone if the stream is set to allow anyone to edit.
        if (stream.edit_mode !== 'anyone' && is_viewer_the_owner === false || post.status === 'deleted') {
            jq_edit_post.addClass('hide');
        } else {
            jq_edit_post.removeClass('hide');
        }

    };

    /**
     * Setup a link to reply to the current post.
     *
     * Click event is global - see BabblingBrook.Client.Component.Post.editEvent
     *
     * @return void
     */
    var prepareReply = function () {
        if (BabblingBrook.Settings.feature_switches['MAKE_COMMENT'] === false) {
            jq_reply.remove();
            return;
        }

        if (jq_reply.length < 1) {
            return;
        }
        if (post.status === 'deleted') {
            return;
        }
        jq_reply.removeClass('hide');
    };

    /**
     * Setup a link to the stream that the post is in.
     *
     * @return void
     */
    var prepareStream = function () {
        if (jq_stream.length < 1) {
            return;
        }
        var stream_url = BabblingBrook.Library.makeStreamUrl(stream, 'posts');
        jq_stream
            .attr('href', 'http://' + stream_url)
            .attr('title', stream_url)
            .text(stream.name);
    };

    /**
     * Setup the status class for the post.
     *
     * @return void
     */
    var prepareStatus = function() {
        jq_post.addClass('status-' + post.status);
    };

    var prepareParentLink = function() {
        if (typeof post.parent_id !== 'undefined' && post.parent_id !== null) {
            var jq_parent_post = jq_location.parents('.post[data-post-id=' + post.parent_id + ']');

            // Only show the link if the parent is not already present.
            if (jq_parent_post.length < 1) {
                jq_link_to_parent
                    .attr('href','/post/' + post.domain + '/' + post.parent_id)
                    .removeClass('hide');
            } else {
                jq_link_to_parent.addClass('hide');
            }
        } else {
            jq_link_to_parent.addClass('hide');
        }
    };

    var prepareFullThreadLink = function() {
        if (typeof post.top_parent_id !== 'undefined' && post.top_parent_id !== null) {
            var jq_top_parent_post = jq_location.parents('.post[data-post-id=' + post.top_parent_id + ']');

            // Only show the link if the top parent is not already present.
            if (jq_top_parent_post.length < 1) {
                jq_link_to_full_thread
                    .attr('href','/post/' + post.domain + '/' + post.top_parent_id)
                    .removeClass('hide');
            } else {
                jq_link_to_full_thread.addClass('hide');
            }
        } else {
            jq_link_to_full_thread.addClass('hide');
        }
    };

    var prepareHideLink = function () {
        // This feature is turned off for now. (It needs to remember what was hidden.)
        jq_hide.addClass('hide');
    };

    /**
     * Called both before and after post and stream data fetched to prepare the post for display.
     *
     * @return {void}
     */
    var preparePost = function () {
        prepareStatus();
        prepareUsername();
        prepareTimeAgo();
        prepareHideLink();
        prepareLinkToPost();
        prepareChildCount();
        prepareParentLink();
        prepareFullThreadLink();
        prepareDeleteLink();
        prepareDeleted();
        prepareCooldown();
        // Cooldown time handled by prepareCooldown
        preparePostLoading();
        preparePostError();
        prepareKindredScore();
        prepareRingMenu();
        prepareRevision();
        prepareEdit();
        prepareReply();
        prepareStream();
        prepareIndividualFields();
        preparePostContent();
    };

    /**
     * Prepares the html from the template and post data.
     */
    var prepareHTML = function () {
        jq_username = jQuery('a.username', jq_post);
        jq_time_ago = jQuery('div>time.time-ago', jq_post);
        jq_individual_fields = jQuery('div>.field', jq_post);
        jq_link_to_post = jQuery('div>a.link-to-post', jq_post);
        jq_child_count = jQuery('.child-count', jq_post);
        jq_link_to_parent = jQuery('div>a.parent-post', jq_post);
        jq_link_to_full_thread = jQuery('div>a.full-thread', jq_post);
        jq_delete = jQuery('div>span.delete', jq_post);
        jq_deleted = jQuery('div>span.deleted', jq_post);
        jq_cooldown = jQuery('div>span.cooldown', jq_post);
        jq_cooldown_time = jQuery('div>span.cooldown-time', jq_post);
        jq_post_loading = jQuery('div>span.post-loading', jq_post);
        jq_post_error = jQuery('div>post-error', jq_post);
        jq_kindred_intro = jQuery('div>span.kindred-intro', jq_post);
        jq_kindred_score = jQuery('.kindred-score', jq_kindred_intro);
        jq_revision = jQuery('div>span.revision', jq_post);
        jq_post_content = jQuery('div>.post-content', jq_post);
        jq_post_rings = jQuery('div>span.post-rings', jq_post);
        jq_edit_post = jQuery('div>span.edit', jq_post);
        jq_reply = jQuery('div>span.post-reply', jq_post);
        jq_stream = jQuery('div>a.stream', jq_post);
        jq_thumbnail = jQuery('.post-thumbnail', jq_post);
        jq_thumbnail_container = jQuery('.post-thumbnail-container', jq_post);
        jq_hide = jQuery('.hide-post', jq_post);

        // Prepare the hidden data attatched to the post.
        jq_post
            .attr('data-post-domain', post.domain)
            .attr('data-post-id', post.post_id);
    };


    /**
     * Displays an post on a stream or tree that has already been rendered.
     *
     * @return void
     */
    var appendPost = function () {
        // Is this a revision for an post that is already on display.
        // Use the title rather than the container as a dummy placeholder may already be on the page.
        if (jQuery('>.title', jq_location).length > 0) {
            // Check that this particular update is not already on display.
            var current_version = jQuery('>div>.revision>.revision-content', jq_location).text();
            if (current_version !== post.revision.toString()) {
                // Append the new post to the existing one and hide it.
                // First delete any other pre-existing updates.
                var jq_next_post = jq_location.next();
                if (jq_next_post.hasClass('hide-new-revision') === true) {
                    jq_next_post.remove();
                }
                // Hide the new post.
                jq_post.addClass('hide-new-revision');
                jq_post.attr('data-post-id', 'revised-' + jq_post.attr('data-post-id'));
                // Display the link to show the new version.
                jQuery('>div>.update', jq_location).removeClass('hide');
                // Append the new hidden version.
                jq_location.after(jq_post);

            } else {
                // Post is already on display, abandon the update.
                return;
            }

        // Or is this an post that is not yet on the page.
        } else {
            // Insert the new post, and hide it.
            jq_post.addClass('hide');
            jq_location.replaceWith(jq_post);
            // See if there is a parent post. If so, use that to display the more link.
            // Otherwise try a siblling first.
            var jq_parent_post = jq_post.parent().parent();
            var found = false;
            if (jq_parent_post.attr('data-post-id') === undefined) {
                var jq_sibling = jq_post.prevUntil().filter(':visible');
                if (jq_sibling.length > 0) {
                    jQuery('>div>.show-new-posts', jq_sibling[0])
                        .fadeIn(2500, function () {
                            // Have to manaully remove the inline display or it covers up the hide class
                            jQuery(this)
                                .removeClass('hide')
                                .css('display', '');
                        });
                    found = true;
                }
            }
            // Sibling not found - use the parent post.
            if (found === false) {
                jq_post.parent().parent().find('>div>.show-new-posts:first')
                    .fadeIn(2500, function () {
                        // Have to manaully remove the inline display or it covers up the hide class
                        jQuery(this)
                            .removeClass('hide')
                            .css('display', '');
                    });
            }
        }
    };

    /**
     * Display the generated post.
     *
     * @return void
     */
    var displayPost = function () {

        // Is this post being inserted into an already rendered stream or tree.
        if (update === true) {
            appendPost();
            return;
        }
        // Preserve any children - the child ul is appended before the post is actually created.
        var jq_children = jQuery('>ul.children>li', jq_location);

        // If the placeholder is hidden then so should the post. It will be displayed by the parent module.
        if (jq_location.hasClass('hide') === true) {
            jq_post.addClass('hide');
        }

        jq_location.replaceWith(jq_post);

        // Restore any preexisting children
        if (jq_children.length > 0) {
            jQuery('>ul.children', jq_post).append(jq_children);
        }

        displayCallback(jq_post, post);
    };

    /**
     * Called after the stream data has been fetched. We now have the data needed to display the post.
     *
     * @param {object} stream The stream data as defined in BabblingBrook.Client.Core.Streams
     *
     * @return void
     */
    var onStreamFetched = function(stream_data) {
        stream = stream_data;
        // store the reply path
        if (typeof reply_path !== 'undefined') {
            var reply_paths = BabblingBrook.Client.Component.Post.reply_paths;
            reply_paths.push(reply_path);
            var reply_path_index = reply_paths.length - 1;
            jq_post.attr('data-reply-path-id', reply_path_index);
        }
        storeReplyTemplate();
        preparePost();
        displayPost();
    };

    /**
     * Error callback for whene the stream data can not be fetched.
     */
    var onStreamFetchedError = function () {
        console.error(
            'Could not display post: Error fetching its stream data.'
        );
        throw 'Thread execution stopped.';
    };

    /**
     * Store the reply template for future use.
     */
    var storeReplyTemplate = function () {
        if (typeof jq_reply_template === 'undefined') {
            return;
        }

        var error1 = typeof jq_reply_template === 'undefined' && typeof jq_reply !== 'undefined';
        var error2 = typeof jq_reply_template !== 'undefined' && typeof jq_reply === 'undefined';
        if (error1 === true || error2 === true) {
            console.error(jq_reply, jq_reply_template);
            console.error(
                'Cannot set up display post reply template due to missing jq_reply or jq_reply_template'
            );
            throw 'Thread execution stopped.';
        }

        var reply_templates = BabblingBrook.Client.Component.Post.reply_templates;
        var reply_html = jq_reply_template.html();
        var reply_index;

        jQuery.each(reply_templates, function (index, jq_stored_template) {
            if (jq_stored_template.html() === reply_html) {
                reply_index = index;
                return false;       // Exit the .each
            }
            return true;            // Continue the .each
        });

        if (typeof reply_index === 'undefined') {
            reply_templates.push(jq_reply_template);
            reply_index = reply_templates.length - 1;
        }

        jq_post.attr('data-reply-template-id', reply_index);
    };


    /**
     * Show an error if an post fails to reload.
     *
     * @param {function} loadPostWithCallback The loadPost function with the original callback bound to it.
     *
     * @return void
     */
    var loadPostError = function (loadPostWithCallback) {
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : 'An error occured whilst trying to fetch details about an post.',
            buttons : [{
                name : 'Retry',
                callback : loadPostWithCallback
            }]
        });
    };

    /**
     * Reload an post.
     *
     * @param onLoaded The callback to run after the post has loaded.
     *
     * @return void
     */
    var loadPost = function (onLoaded) {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : post.domain,
                post_id : post.post_id,
                revision : post.revision
            },
            'GetPost',
            onLoaded,
            loadPostError.bind(null, loadPost.bind(null, onLoaded))
        );
    };

    /**
     * Continue setup after the post has loaded and then load the stream.
     *
     * @param {object} loaded_post A standard post object.
     *
     * @return {void}
     */
    var onPostLoaded = function(loaded_post) {
        jQuery.extend(post, loaded_post);
        if (post.domain === BabblingBrook.Client.User.domain
            && post.username === BabblingBrook.Client.User.username
        ) {
            is_owned = true;
        }

        // Check to see if a callback has been stored.
        var callbacks = BabblingBrook.Client.Component.Post.supplemental_callbacks;
        var callback_index =  jq_location.attr('data-callback-id');
        if (typeof callback_index !== 'undefined') {
            displayCallback = callbacks[callback_index];
        }
        // Store the callback function.
        if (typeof displayCallback === 'function') {
            callbacks.push(displayCallback);
            callback_index = callbacks.length - 1;
            jq_post.attr('data-callback-id', callback_index);
        }
        // If no callback used then create a dummy to make execution easier.
        if (typeof displayCallback === 'undefined') {
            displayCallback = function () {};
        }

        BabblingBrook.Client.Core.Streams.getStream(
            post.stream_domain,
            post.stream_username,
            post.stream_name,
            post.stream_version,
            onStreamFetched,
            onStreamFetchedError
        );
    }

    /**
     * Class constructor.
     */
    var setup = function () {
        if (typeof update !== 'boolean') {
            update = false;
        }

        if (typeof slide !== 'boolean') {
            slide = true;
        }

        if (typeof show_empty !== 'boolean') {
            show_empty = true;
        }

        if (typeof onReplied !== 'funciton') {
            onReplied = function () {};
        }
        var onreplied_callbacks = BabblingBrook.Client.Component.Post.onreplied_callbacks;
        var onreplied_index = onreplied_callbacks.push(onReplied) - 1;
        jq_location.attr('data-onreplied-id', onreplied_index);

        BabblingBrook.Client.Component.Post.setupEvents();
        // Check to see if there is a stored template
        var templates = BabblingBrook.Client.Component.Post.templates;
        var template_index =  jq_location.attr('data-template-id');
        if (typeof template_index !== 'undefined') {
            jq_template = templates[template_index];
        }
        // Clone the template so that the original is not edited.
        jq_post = jq_template.clone();
        // Store the template
        templates.push(jq_template);
        template_index = templates.length - 1;
        jq_post.attr('data-template-id', template_index);
        prepareHTML();
        // Refetch the post to ensure that we have a full post object.
        if (typeof post.content === 'undefined') {
            loadPost(onPostLoaded);
        } else {
            onPostLoaded(post);
        }
    };
    setup();

};

/**
 * @type {boolean} Prototype variable for recording if the global events have been setup.
 */
BabblingBrook.Client.Component.Post.prototype.setupRun = false;

/**
 * Event handler for all hide links on posts.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/ .
 *
 * @return void;
 */
BabblingBrook.Client.Component.Post.hideEvent = function(event) {
    var jq_hide_link = jQuery(event.currentTarget);
    jq_hide_link.parent().parent().slideUp();
};

/**
 * The success callback for when an post has been deleted.
 *
 * @param {object} jq_post A jquery object containing the post object.
 * @param {object} success_data Contains details of the delete action.
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.deleteSuccessCallback = function (jq_post, success_data) {
    jQuery('>div>.deleted', jq_post).removeClass('hide');
    jq_post.addClass('status_deleted');
    jQuery('>div>span.delete', jq_post).removeClass('text-loading');
    jQuery('>div>.cooldown, >div>.cooldown-time', jq_post).remove();
    jQuery('>div.actions>:not(".hide-post"):not(".deleted")', jq_post).remove();
};

/**
 * The failure callback for when an post has been deleted.
 *
 * @param {object} jq_post A jquery object containing the post object.
 * @param {object} error_data Contains details of the error.
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.deleteErrorCallback = function (jq_post, error_data) {
    var public_error_message = 'Failed to delete post. Please refresh the page to try again.';
    jQuery('div>span.delete', jq_post).removeClass('text-loading').addClass('ajax-error');
    jQuery('>div>.post-error', jq_post)
        .html(public_error_message)
        .removeClass('hide');
};

/**
 * The user has cancelled the delete request.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.deleteCanceled = function (event) {
    var jq_cancel_link = jQuery(event.currentTarget);
    var jq_confirm = jq_cancel_link.parent();
    jq_confirm.addClass('hide');
    jQuery('span', jq_confirm).off('click');
};

/**
 * The user has confirmed the delete request.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.deleteConfirmed = function (event) {
    var jq_confirm_link = jQuery(event.currentTarget);
    var jq_post = jq_confirm_link.parent().parent().parent();
    var post_id = jq_post.attr('data-post-id');
    var post_domain = jq_post.attr('data-post-domain');

    jQuery('>div>span.delete', jq_post).addClass('text-loading');

    BabblingBrook.Client.Core.Interact.postAMessage(
        {
            post_id : post_id,
            post_domain : post_domain
        },
        'DeletePost',
        BabblingBrook.Client.Component.Post.deleteSuccessCallback.bind(null, jq_post),
        BabblingBrook.Client.Component.Post.deleteErrorCallback.bind(null, jq_post)
    );
    var jq_confirm = jq_confirm_link.parent();
    jq_confirm.addClass('hide');
    jQuery('span', jq_confirm).off('click');
};

/**
 * Handler for a delete post click event.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/
 *
 * @return void
 * @test Make sure this is tested - both a valid deletion and a fake one - insert the html to make the link.
 */
BabblingBrook.Client.Component.Post.deleteEvent = function (event) {
    var jq_delete_link = jQuery(event.currentTarget);
    var jq_post = jq_delete_link.parent().parent();
    jQuery('>div>.delete-confirm', jq_post).removeClass('hide');
    jQuery('>div>.delete-confirm>.delete-confirmed', jq_post).on('click', BabblingBrook.Client.Component.Post.deleteConfirmed);
    jQuery('>div>.delete-confirm>.delete-canceled', jq_post).on('click', BabblingBrook.Client.Component.Post.deleteCanceled);
};

/**
 * Callback for redisplaying the post after it has been edited.
 *
 * @param {object} jq_post jQuery object holding the location of the post in the DOM
 * @param {object} jq_edit_post jQuery object holding the location of edit post tag in the DOM.
 * @param {object} post A standard post object for the edited post.
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.edited = function (jq_post, jq_edit_post, post) {
    jq_edit_post.remove();
    var reply_path_id = jq_post.attr('data-reply-path-id');
    var reply_path;
    var jq_reply_template;

    if (typeof reply_path_id !== 'undefined') {
        reply_path = BabblingBrook.Client.Component.Post.reply_paths[reply_path_id];
        var reply_template_id = jq_post.attr('data-reply-template-id');
        jq_reply_template = BabblingBrook.Client.Component.Post.reply_templates[reply_template_id].clone();
    }
    BabblingBrook.Client.Component.Post(
        post,
        jq_post,
        undefined,
        reply_path,
        jq_reply_template,
        undefined,
        undefined,
        undefined,
        undefined,
        true
    );
};


/**
 * Callback for redisplaying the orrigional post if it had been cancelled.
 *
 * @param {object} jq_post jQuery object holding the location of the post in the DOM
 * @param {object} jq_edit_post jQuery object holding the location of edit post tag in the DOM.
 * @param {object} post A standard post object for the edited post.
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.editCancelled = function (jq_post, jq_edit_post, post) {
    jq_edit_post.remove();
    // remove this post and hide container
    jQuery('>div,>span', jq_post)
        .removeClass('edit-hide');
};

/**
 * Handler for editing an post click event.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.editEvent = function (event) {

    var jq_edit_link = jQuery(event.currentTarget);
    var jq_post = jq_edit_link.parent().parent();
    var post_id = jq_post.attr('data-post-id');
    var post_domain = jq_post.attr('data-post-domain');

    var onPostFetched = function (post) {
        var stream_url = BabblingBrook.Library.makeStreamUrl(
            {
                domain : post.stream_domain,
                username : post.stream_username,
                name : post.stream_name,
                version : post.stream_version
            },
            'json'
        );

        var jq_edit_template = jQuery('#edit_post_template>div').clone();
        jQuery('.edit-post', jq_edit_template).attr('id', 'edit_post_' + post_id);
        jq_post.before(jq_edit_template);
        var jq_edit_post = jQuery('#edit_post_' + post_id, jq_post.parent());

        // Instantiate the post.
        var make_post = new BabblingBrook.Client.Component.MakePost(
            BabblingBrook.Client.Component.Post.edited.bind(null, jq_post, jq_edit_post),
            BabblingBrook.Client.Component.Post.editCancelled.bind(null, jq_post, jq_edit_post)
        );

        make_post.setupNewPost(
            stream_url,
            jq_edit_post,
            'open',
            post.parent,
            post.top_parent,
            post,
            undefined,
            'Edit post'
        );

        // remove this post and hide container
        jQuery('>div, >span, >a', jq_post)
            .addClass('edit-hide');
    };

    BabblingBrook.Client.Core.Interact.postAMessage(
        {
            domain : post_domain,
            post_id : post_id
        },
        'GetPost',
        onPostFetched,
        function () {
            console.error('Error loading post for post edit event.');
        }
    );

};

/**
 * Callback for after a post edit is cancelled.
 *
 * @param {object} jq_post jQuery object holding the location of the post in the DOM
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.replyCancelled = function (jq_post) {
    jQuery('>div>.reply-location', jq_post)
        .html('')
        .addClass('hide');
};

/**
 * Callback for after a post has been replied to.
 *
 * @param {object} jq_post jQuery object holding the location of the post in the DOM
 * @param {object} post A standard post object for the edited post.
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.replied = function (jq_post, post) {
    var onreply_id = jq_post.attr('data-onreplied-id');
    if (typeof onreply_id !== 'undefined') {
        var onReply = BabblingBrook.Client.Component.Post.onreplied_callbacks[onreply_id];
        if (typeof onReply === 'function') {
            onReply(jq_post, post);
        }
    }

    var reply_path_id = jq_post.attr('data-reply-path-id');
    if (typeof reply_path_id === 'undefined') {
        return;
    }
    var reply_path = BabblingBrook.Client.Component.Post.reply_paths[reply_path_id];
    var jq_reply_location = jQuery(reply_path, jq_post);
    var reply_template_id = jq_post.attr('data-reply-template-id');

    var jq_reply_template = BabblingBrook.Client.Component.Post.reply_templates[reply_template_id].clone();
    jq_reply_location.prepend('<div class="new-post"></div>');
    jq_reply_location = jQuery('>.new-post:first', jq_reply_location);
    jQuery('>div>.reply-location', jq_post)
        .html('')
        .addClass('hide');
    BabblingBrook.Client.Component.Post(post, jq_reply_location, jq_reply_template, reply_path, jq_reply_template);
};

/**
 * Called when the stream data for the current post has loaded, with child stream name data  contained.
 *
 * @param {object} post The post object for the post that is being replied to.
 * @param {object} jq_post A Jquery object for the post being replied to.
 * @param {object} jq_reply_location A Jquery object for the location of the reply form.
 * @param {object} child_stream The stream of the post that is being replied to.
 * @returns {void}
 */
BabblingBrook.Client.Component.Post.setupReplyForm = function (
    post, jq_post, jq_reply_location, child_stream
) {
    var stream_url = BabblingBrook.Library.makeStreamUrl(child_stream, 'json');
    var jq_reply_template = jQuery('#post_reply_template>.reply-post').clone();
    jq_reply_location
        .append(jq_reply_template)
        .removeClass('hide');

    // Instantiate the post.
    var make_post = new BabblingBrook.Client.Component.MakePost(
        BabblingBrook.Client.Component.Post.replied.bind(null, jq_post),
        BabblingBrook.Client.Component.Post.replyCancelled.bind(null, jq_post)
    );
    var top_post_id = post.post_id;
    if (typeof post.top_parent_id === 'string' || typeof post.top_parent_id === 'number') {
        top_post_id = post.top_parent_id;
    }

    var jq_reply_message = jQuery('#reply_to_post_template').clone();
    jQuery('.post-username', jq_reply_message).text(post.username);
    jQuery('.child-stream', jq_reply_message).text(child_stream.child_stream);

    make_post.setupNewPost(
        stream_url,
        jQuery('>.reply-content', jq_reply_template),
        'open',
        post.post_id,
        top_post_id,
        undefined,
        undefined,
        jq_reply_message.html()
    );
};

/**
 * A child stream has been selected from the reply list. Display the reply form.
 *
 * @param {object} post The post object for the post that is being replied to.
 * @param {object} jq_post A Jquery object for the post being replied to.
 * @param {object} jq_reply_location A Jquery object for the location of the reply form.
 * @param {object} child_stream The stream of the post that is being replied to.
 *
 * @returns {void}
 */
BabblingBrook.Client.Component.Post.onReplyChildSelected = function (
    post, jq_post, jq_reply_location, jq_reply_container, child_stream
) {
    jq_reply_container.removeClass('open');
    var jq_reply_streams = jQuery('>.reply-streams', jq_reply_container);
    jq_reply_streams.addClass('hide');
    BabblingBrook.Client.Component.Post.setupReplyForm(
        post,
        jq_post,
        jq_reply_location,
        child_stream
    );
};

/**
 * Called when the stream data for the current post has loaded, with child stream name data  contained.
 *
 * @param {object} post The post object for the post that is being replied to.
 * @param {object} jq_post A Jquery object for the post being replied to.
 * @param {object} jq_reply_location A Jquery object for the location of the reply form.
 * @param {object} jq_reply_container A Jquery object for the container of the reply link and child list.
 * @param {object} child_stream The stream of the post that is being replied to.
 * @returns {void}
 */
BabblingBrook.Client.Component.Post.replyMultipleChildren = function (
    post, jq_post, jq_reply_location, jq_reply_container, child_streams
) {
    var jq_reply_streams = jQuery('>.reply-streams', jq_reply_container);
    jq_reply_streams.empty();
    if (jq_reply_container.hasClass('open') === true) {
        jq_reply_container.removeClass('open');
        jq_reply_streams.addClass('hide');
        return;
    }

    for (var i=0; i < child_streams.length; i++) {
        var jq_reply_line = jQuery('#reply_line_template>li').clone();
        jq_reply_line.append(child_streams[i].name);
        var child_stream_url = BabblingBrook.Library.makeStreamUrl(child_streams[i]);
        jq_reply_line.attr('title', child_stream_url)
        jq_reply_streams.append(jq_reply_line);

        jq_reply_line.unbind('click');
        jq_reply_line.click(BabblingBrook.Client.Component.Post.onReplyChildSelected.bind(
            null,
            post,
            jq_post,
            jq_reply_location,
            jq_reply_container,
            child_streams[i]
        ));
    }
    jq_reply_streams.removeClass('hide');
    jq_reply_container.addClass('open');
};

/**
 * Called when the stream data for the current post has loaded, with child stream name data  contained.
 *
 * @param {object} post The post object for the post that is being replied to.
 * @param {object} jq_post A Jquery object for the post being replied to.
 * @param {object} jq_reply_location A Jquery object for the location of the reply form.
 * @param {object} jq_reply_container A Jquery object for the container of the reply link and child list.
 * @param {object} current_stream The stream of the post that is being replied to.
 * @returns {void}
 */
BabblingBrook.Client.Component.Post.onCurrentStreamLoadedForReply = function (
    post, jq_post, jq_reply_location, jq_reply_container, current_stream
) {
    var child_streams = current_stream.child_streams;
    for (var i=0; i < child_streams.length; i++) {
        if (child_streams[i].post_mode === 'owner'
            && (child_streams[i].domain !== BabblingBrook.Client.User.domain
            || child_streams[i].username !== BabblingBrook.Client.User.username)
        ) {
            child_streams.splice(i, 1);
        }
    }

    if (child_streams.length === 1) {
        BabblingBrook.Client.Component.Post.setupReplyForm(post, jq_post, jq_reply_location, child_streams[0]);
    } else {
        BabblingBrook.Client.Component.Post.replyMultipleChildren(
            post,
            jq_post,
            jq_reply_location,
            jq_reply_container,
            child_streams
        );
    }
};

/**
 * Handler for handling a post reply click event.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.replyEvent = function (event) {
    var jq_reply_link = jQuery(event.currentTarget);
    var jq_reply_container = jq_reply_link.parent();
    var jq_post = jq_reply_container.parent().parent();
    // Ensure only one reply post can be made at a time.
    if (jQuery('>div>.reply-location>.reply-post:nth(0)', jq_post).length > 0) {
        return;
    }

    var post_id = jq_post.attr('data-post-id');
    var post_domain = jq_post.attr('data-post-domain');


    var onPostFetched = function (post) {
        var jq_reply_location = jQuery('>div>.reply-location', jq_post);
        if (jq_reply_location.length === 0) {
            console.error('reply-location is missing in the post template');
            console.error(jq_post, post);
            throw 'Thread execution stopped.';
        }
        // fetch the stream for this post.
        BabblingBrook.Client.Core.Streams.getStream(
            post.domain,
            post.stream_username,
            post.stream_name,
            post.stream_version,
            BabblingBrook.Client.Component.Post.onCurrentStreamLoadedForReply.bind(
                null,
                post,
                jq_post,
                jq_reply_location,
                jq_reply_container
            )
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
            console.error('Error loading post for post reply event.');
        }
    );
};

/**
 * Handler for editing an post switch event.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.switchEvent = function (event) {
    var jq_switch_link = jQuery(event.currentTarget);
    var jq_post = jq_switch_link.parent().parent();
    jq_post.children().not('.info').slideToggle(250);
    jq_switch_link.toggleClass('switch-flipped');
};

/**
 * Handler for an event to update an post to a new version.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.updateEvent = function (event) {
    var jq_update_link = jQuery(event.currentTarget);
    var jq_post = jq_update_link.parent().parent();
    var post_id = jq_post.attr('data-post-id');
    var jq_new_revision = jQuery('.post[data-post-id=revised-' + post_id + ']');
    jq_new_revision.attr('data-post-id', post_id);

    // Copy childen across to the new post
    var jq_children = jQuery('>ul.children', jq_post);
    jQuery('ul.children', jq_new_revision).html(jq_children.children());
    jq_post.slideUp(125, function() {
        jq_new_revision.children().removeClass('hide-new-revision');
        jQuery('>div>.update', jq_new_revision).addClass('hide');
        jq_post.remove();
        jq_new_revision
            .slideDown(125)
            .removeClass('hide-new-revision');
    })
};

/**
 * Event handler for showing new posts when a show-new-post link is clicked.
 *
 * Shows any chain of hidden unbroken siblings and children following this one.
 *
 * @param {object} event A jQuery event object. @see http://api.jquery.com/on/
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.showNewPostsEvent = function (event) {
    var jq_show_link = jQuery(event.currentTarget);
    var jq_post = jq_show_link.parent().parent();
    var jq_children = jQuery('li.post', jq_post);

    // If there are no children then try for siblings - this is used on stream display of posts.
    if (jq_children.length === 0) {
        jq_post.nextUntil(':visible', '.post')
            .slideDown(250)
            .removeClass('hide');

    // Otherwise use the children - this is used on tree displays of posts.
    } else {
        jq_children.each(function() {
            var jq_child = jQuery(this);
            if (jq_child.hasClass('hide')) {
                jq_child
                    .slideDown(250)
                    .removeClass('hide');
                return true;    // continue reveiling posts.
            } else {
                return false;   // This post is already visible, don't reveil any more.
            }
        });
    }

    jq_show_link.addClass('hide');
};

/**
 * Display a take error.
 *
 * @return void
 */
BabblingBrook.Client.Component.Post.TakeError = function () {
    BabblingBrook.Client.Component.Messages.addMessage({
        type : 'error',
        message : 'An error occured whilst trying to take an post.'
    });
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
BabblingBrook.Client.Component.Post.setupEvents = function() {
    if (BabblingBrook.Client.Component.Post.prototype.setupRun === true) {
        return;
    }
    jQuery(document).on('click', '.post>div>.hide-post', BabblingBrook.Client.Component.Post.hideEvent);
    jQuery(document).on('click', '.post>div>.delete', BabblingBrook.Client.Component.Post.deleteEvent);
    jQuery(document).on('click', '.post>div>.edit', BabblingBrook.Client.Component.Post.editEvent);
    jQuery(document).on('click', '.post>div>.post-reply>.reply-title', BabblingBrook.Client.Component.Post.replyEvent);
    jQuery(document).on('click', '.post>div>.switch', BabblingBrook.Client.Component.Post.switchEvent);
    jQuery(document).on('click', '.post>div>.update', BabblingBrook.Client.Component.Post.updateEvent);
    jQuery(document).on('click', '.post>div>.show-new-posts', BabblingBrook.Client.Component.Post.showNewPostsEvent);
    BabblingBrook.Client.Component.Value.Arrows.setupEvents();
    BabblingBrook.Client.Component.Value.Textbox.setupEvents();
    BabblingBrook.Client.Component.Value.Button.setupEvents();
    // Slider events are individually assigned.
    BabblingBrook.Client.Component.Value.Stars.setupEvents();
    BabblingBrook.Client.Component.Value.List.setupEvents();

    BabblingBrook.Client.Component.Post.prototype.setupRun = true;

    // closes all open post sub menus if the user clicks elsewhere on the page.
    jQuery(document).click(function (event) {
        var jq_target = jQuery(event.target);
        if (jq_target.hasClass('moderation-submenu-item') === false          // Close if not clicked on menu.
            && (jq_target.hasClass('moderation-submenu-title') === false)     // and not clicked on menu title.
        ) {
            jQuery('.moderation-submenu>ul').addClass('hide');
            jQuery('.moderation-submenu').removeClass('open');
        }
    });

};

/**
 * When an post is edited, it needs to be able to rerun the setup after the edit is finished,
 * This means that it requires access to the callback function that was called to generate it.
 * They are stored in this array and a reference stored in the data-callback-id posts main tag
 */
BabblingBrook.Client.Component.Post.supplemental_callbacks = [];

/**
 * When an post is replied to, it needs to be able to call the reply callback that was pass in when
 * the post was created.
 * They are stored in this array and a reference stored in the data-onreplied-id posts main tag
 */
BabblingBrook.Client.Component.Post.onreplied_callbacks = [];

/**
 * When an post is edited, it needs to be able to rerun the setup after the edit is finished,
 * This means that it requires the template used to create it. They are stored here.
 */
BabblingBrook.Client.Component.Post.templates = [];

/**
 * When an post is replied to, it needs to know where to insert the new post.
 * This means that it requires the path to this location. This needs storing when the post
 * is created as the creation of a reply will happen at a later date.
 */
BabblingBrook.Client.Component.Post.reply_paths = [];

/**
 * When an post is replied to, it needs to know how it should display the reply.
 * In order to facilitate this, when the post is created a template is passed in
 * and it is stored here. Only one copy of each template is stored.
 */
BabblingBrook.Client.Component.Post.reply_templates = [];