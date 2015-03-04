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
 * @fileOverview Handles the display of messages for the user. Eg Error messages suggestions and tutorials.
 */

/**
 * @namespace Handles the display of messages for the user. Eg Error messages suggestions and tutorials.
 * @package JS_Client
 * @pipedream Think of a way to test client error messages.
 */
BabblingBrook.Client.Component.Messages = (function () {
    'use strict';
    var jq_messages;

    /**
     * @type {object[]} messages An array of message objects.
     * @type {string} messages.type The type of message. See types array for valid values.
     * @type {string} messages.message The text to display for the message above the fold.
     * @type {string} messages.full The html to display for the message below the fold.
     *      messages.message is also included below the fold.
     * @type {object[]} messages.buttons Buttons to display for actions that can be taken on the message.
     * @type {string} messages.buttons.name The name of an action.
     * @type {function} messages.buttons.callback A callback function that is called when a button is actioned.
     * @type {boolean} messages.acknowledged Has this message been interacted with.
     * @type {number} messages.priority The priority of the message. Higher is more urgent. Default is 100.
     * @type {string|undefined} message.url The url a message appears on if it
     *                                      only appears on a particular relative url.
     *                                      If undefined then message will appear on any url.
     */
    var messages = [];

    /**
     * A count of the suggestion messages queued so far.
     * @type Number
     */
    var suggestion_count = 0;

    /**
     * Are suggestions being shown.
     *
     * @type {boolean}
     */
    var suggestions_on = false;

    /**
     *  @type {object} Different types and their priorities.
     */
    var types = {
        error : 1,
        system : 2,
        notice : 3,
        tutorial : 4,
        suggestion : 5,
        ring : 6

    };

    /**
     * @type {string[]} A stack of error objects that have been reported. Used in bug reports.
     */
    var error_stack = [];

    /**
     * Stores callbacks that are waiting to run until the current message has finished processing an action.
     *
     * @type {object}
     */
    var deferred_messages;

    /**
     * loading Set from a callback by using turnOnBorderLoading.
     *
     * Shows the loading border and prevents new messages from displaying.
     * Call turnOffBorderLoading to resume normal operations.
     *
     * @type {boolean}
     */
    var loading = false;

    /**
     * @type {boolean} Prevents new messages from showing when set to on.
     */
    var completely_off = false;

    /**
     * @type {number} The minimized height of the message window is caculated from the empty message window.
     */
    var minimized_height;

    /**
     * @type {boolean} Is the message box in a minimized state.
     */
    var minimized = false;

    /**
     * @type {number} The height og the button row.
     */
    var buttons_height;

    /**
     * A rerference to the message object currently being displayed.
     *
     * @type object
     */
    var current_message;

    /**
     * Crops the title text once the post has been prepared to ensure it fits in the title area.
     *
     * @param {type} jq_post
     * @returns {void}
     */
    var cropTitle = function (jq_post) {
        var title = jq_post.find('.title>span').text();
        var jq_title = jq_post.find('.title');
        var short_title = cropMessage(title, jq_title);
        jq_post.find('.title>span').text(short_title);
    };

    /**
     * Creates a message object from an post.
     *
     * Two versions of the post are created. A compact one for the minimised message box, and a full one for the
     * maximised message box.
     *
     * @param {type} post A standard post object.
     * @param {object} jq_minimized_location A jQuery object holding the location of the compact post.
     * @param {object} jq_minimized_location A jQuery object holding the location of the full post.
     *
     * @return {void}
     */
    var createMessageFromPost = function (post, jq_minimized_location, jq_maximized_location) {
        var jq_post_template = jQuery('#snippet_template>.post').clone();
        BabblingBrook.Client.Component.Post(
            post,
            jq_minimized_location,
            jq_post_template,
            undefined,
            undefined,
            cropTitle,
            undefined,
            false,
            false
        );

        var jq_full_post_template = jQuery('#snippet_full_template>.post').clone();
        BabblingBrook.Client.Component.Post(
            post,
            jq_maximized_location,
            jq_full_post_template,
            undefined,
            undefined,
            undefined,
            undefined,
            false,
            false
        );
    };

    /**
     * Callback for when the next default message is requested.
     *
     * @param {object} post The post that is currently displayed.
     *
     * @returns {void}
     */
    var onNextClicked = function (post) {
        showDefaultMessage();
    };

    /**
     * If there are no snippets left to show, then it shows a 'no messages' message.
     *
     * @returns {void}
     */
    var showNoMessages = function () {
        var no_message_html;
        var no_more_html;

        if (suggestions_on === true) {
            no_message_html = jQuery('#no_more_suggestions_message_template').html();
            no_more_html = jQuery('#no_more_suggestions_button_template').html();
        } else {
            no_message_html = jQuery('#no_messages_template').html();
        }

        jQuery('#messages_inner', jq_messages)
            .attr('data_index', '-1')
            .html(no_message_html);
        jQuery('#messages_full', jq_messages)
            .html(no_message_html);
        jQuery('#message_buttons', jq_messages).empty();
        if (typeof no_more_html !== 'undefined') {
            var jq_buttons = jQuery('#message_buttons', jq_messages);
            jq_buttons.html(no_more_html);
            jQuery('#close_suggestions').click(function () {
                suggestions_on = false;
                jq_messages.slideUp(function(){
                    jq_messages.addClass('hide');
                });
            });
        }
        jQuery('#messages').removeClass('block-loading');
        //jQuery('#messages_full').addClass('hide');
    };

    /**
     * Show the default message when there are no more messages to show.
     */
    var showDefaultMessage = function () {
        jQuery('#messages_inner', jq_messages)
            .attr('data_index', '-1')
            .html('');
        jQuery('#messages_full').html('');
        showNoMessages();
    };

    /**
     * Crop the message to the size of the current message box.
     *
     * @param {string} message The message to crop to fir in the message box.
     * @param {object} [jq_alternate_element] An alternative element to measure for the width
     *      - This is used when a sub element wants to be measured, such as when an post
     *      is used instead of plain text..
     *
     * @returns {string}
     */
    var cropMessage = function (message, jq_alternate_element) {
        var jq_messages_inner = jQuery('#messages_inner');
        var width = jq_messages_inner.width();
        if (typeof jq_alternate_element !== 'undefined') {
            width = jq_alternate_element.width();
        }
        var font = jq_messages_inner.css('font-family');
        var font_size = jq_messages_inner.css('font-size');
        var jq_test_box = jQuery('#message_crop_template>div').clone();
        jQuery('body').prepend(jq_test_box);
        jq_test_box
            .width(width)
            .css('font-family', font)
            .css('font_size', font_size)
            .html(message);
        var new_height = jq_test_box.height();
        var shortened_text = jq_test_box.html();

        // Shrink the text until it fits
        if (new_height > minimized_height) {
            while(new_height > minimized_height) {
                shortened_text = jq_test_box.text();
                shortened_text = shortened_text.substring(0, shortened_text.length - 1);
                jq_test_box.html(shortened_text);
                new_height = jq_test_box.height();
            }

            // remove three extra characters for the three dots to indicate that there is more.
            shortened_text = shortened_text.substring(0, shortened_text.length - 3);
            var perfect_fit_text = shortened_text;

            // Remove text until a white space is found.
            var white_space = false;
            while(white_space === false) {
                var last_character = shortened_text.charAt(shortened_text.length - 1);
                if (last_character !== ' ') {
                    shortened_text = shortened_text.substring(0, shortened_text.length - 1);
                } else {
                    white_space = true;
                }
                // If there are no white spaces then use the full text.
                if (shortened_text.length < 1) {
                    shortened_text = perfect_fit_text;
                    white_space = true;
                }
            }
        }
        if (shortened_text.length !== message.length) {
            shortened_text = shortened_text + '...';
        }
        jQuery('body>#message_crop').remove();
        return shortened_text;
    };

    /**
     * The more button has been clicked; toggle the extra details.
     *
     * @returns {void}
     */
    var onMoreClicked = function () {
        jQuery('#messages_inner').toggleClass('hide');
       // jQuery('#messages_full').toggleClass('hide');
        if (jQuery('#messages_full').is(':visible')) {
            jQuery('.message-button-more').text('Less');
        } else {
            jQuery('.message-button-more').text('More');
        }
    };

    /**
     * Appends the More/Less button to a message.
     *
     * @param {object} message The message object
     * @param {string} cropped_message The message cropped to fit in the closed message box.
     * @param {type} post If the message is an post then this is the post.
     *
     * @returns {object} The modified buttons object.
     */
    var addMoreButton = function (message, cropped_message, post) {
        var last_name = '';
        if (message.buttons.length > 0) {
            last_name = message.buttons[message.buttons.length -1].name;
        }
        var top_message = '';
        if (typeof message.message === 'string') {
            top_message = message.message;
        }

        if (last_name !== 'More' && last_name !== 'Less'
            && (cropped_message.length < top_message.length
            || typeof post !== 'undefined' || typeof message.full !== 'undefined')
        ) {
            var name = 'More';
            if (jQuery('#messages_full').is(':visible')) {
                name = 'Less';
            }
            var more_button = {
                name : name,
                callback : onMoreClicked
            };
            message.buttons.push(more_button);
        }
        return message.buttons;
    };

    /**
     * Updates the displayed number of suggestions.
     *
     * @returns {void}
     */
    var recaculateSuggestionsCount = function() {
        var suggestion_count = 0;
        jQuery.each(messages, function (i, message) {
            if (message.type === 'suggestion' && message.acknowledged !== true) {
                suggestion_count++;
            }
        });
        jQuery('#suggestion_count').text('(' + suggestion_count + ')');
    };

    /**
     * Displays the buttons for a message.
     *
     * @param {array} buttons An array of buttons to display.
     * @param {string} buttons[].name The name that should appear on the button.
     * @param {funciton} buttons[].callback The function to run when the button is clicked.
     * @param {number|string} message_index The index of the message in the queue.
     *      'snippet' is used for snippets.
     *
     * @returns {void}
     */
    var displayButtons = function (buttons, message_index) {
        var jq_buttons = jQuery('#message_buttons', jq_messages);
        jq_buttons.html('');
        jQuery.each(buttons, function(i, button) {

            var button_class = 'standard-button';
            // More buttons are possitioned to the right.
            if (button.name === 'More' || button.name === 'Less') {
                button_class = 'standard-button message-button-more';
            }
            if (button.name === 'Next Snippet') {
                button_class = 'standard-button message-button-snippet';
            }
            jq_buttons.append('<button id="message_button_' + message_index + '_' + i + '"' +
                ' class="' + button_class + '">' + button.name + '</button>'
            );

            /**
            * Nest the callback so that the next message can be shown when a button is clicked.
            *
            * If the callback returns false then the next message is not shown and it needs to be called manually.
            *
            * @return void
            */
            jQuery('#message_button_' + message_index + '_' + i).click(function () {
                // The more button does not trigger a message flush.
                if (jQuery(this).hasClass('message-button-more') === true
                    || jQuery(this).hasClass('message-button-snippet') === true
                ) {
                    button.callback();
                    return;
                }

                deferred_messages = jQuery.Deferred();
                jQuery('#message_loading').removeClass('hide');
                var reset = button.callback();
                if(reset !== false) {
                    jQuery('#message_loading').addClass('hide');
                    if (message_index !== 'snippet') {
                        messages[message_index].acknowledged = true;
                    }
                    recaculateSuggestionsCount();
                    BabblingBrook.Client.Component.Messages.showNext();
                    deferred_messages.resolve();
                }
            });
        });
    };

    var onShowSuggestionMessage = function () {
        if (jq_messages.hasClass('hide') === true) {
            suggestions_on = true;
            minimized = false;
            jQuery('#report_bug').addClass('hide');
            jq_messages.slideDown();
            jq_messages.toggleClass('hide');
            BabblingBrook.Client.Component.Messages.turnOffBorderLoading();   // Also shows next message.
        } else {
            suggestions_on = false;
            if (typeof current_message === 'undefined' || current_message.type !== 'error') {
                jq_messages.slideUp(function(){
                    jq_messages.addClass('hide');
                });
            }
        }

        return false;
    };


    return {

        /**
         * Check if there is a message in the querystring to display.
         *
         * @returns void
         */
        checkForMessage : function () {
            var notice = BabblingBrook.Library.getParameterByName('notice');
            if (notice.length > '') {
                BabblingBrook.Client.Component.Messages.addMessage({
                    type : 'notice',
                    message : notice,
                });
                BabblingBrook.Client.Component.Messages.showNext();
                // Remove the notice from the url to prevent it reappearing
                // when the page is refreshed or a link is followed.
                var new_url = BabblingBrook.Library.removeParamaterFromUrl(window.location.toString(), 'notice');
                window.history.replaceState({}, document.title, new_url);
            }
        },

        /**
         * Setup the the message space in preperation to recieve messages.
         *
         * NB when a page is loaded though Ajaxurl, the message html is included server side.
         *
         * @sugestion Insert an advert that appears at the bottom of the messages if all other messages are taken.
         *            This would be inserted in the no messages html slot.
         */
        setup : function () {
            minimized = false;
            jq_messages = jQuery('#messages_template>div').clone();
            jq_messages.addClass('hide');
            jQuery('#sidebar_container').after(jq_messages);

            // This module was origional designed to have a minimised state for the messages but it is not currently
            // being used.
            jQuery('#messages_inner').addClass('hide');
            jQuery('#messages_full').removeClass('hide');
            jQuery('.message-button-more').remove();

            jQuery('#suggestions_link').on('click', onShowSuggestionMessage);

            // Set up a gloabl window error to show a reload page request to the user.
            window.onerror = function(message, url, line) {
                BabblingBrook.Client.Component.Messages.addMessage({
                    type : 'error',
                    message : 'An unknown error has occurred. Reload the page to prevent wierd stuff happening.',
                    full : ' Error Message : ' + message + ' Url : ' + url + ' Line : ' + line
                });
            };


            jQuery(window).bind('beforeunload', function(event) {
                jQuery('#messages').slideUp(250);
                BabblingBrook.Client.Component.Messages.turnOff();
            });

            BabblingBrook.Client.Component.Messages.fixHeight();
            BabblingBrook.Client.Component.Messages.showNext();

//
//            var jq_message = jQuery('#suggestion_stream_message_template>div').clone();
//            var url = 'texturl.com';
//            jQuery('a.suggestion-message', jq_message)
//                .attr('href', 'http://' + url)
//                .attr('title', url)
//                .text('test suggestion');
        },

        /**
         * Remaps the suggestion link in the nav bar. Needed by the tutorial system as
         * the link is added dynamically and is missed in the initial setup..
         *
         */
        remapSuggestionLink : function () {
            jQuery('#suggestions_link').on('click', onShowSuggestionMessage);
        },

        /**
         * The user can set the height of the message box in their settings, this ensures it stays that height.
         *
         * It needs to be called each time the message box is redrawn.
         */
        fixHeight : function () {
            var config_ready = BabblingBrook.Library.doesNestedObjectExist(
                BabblingBrook.Client,
                ['User', 'Config', 'message_box_lines']
            );
            var lines;
            if (config_ready === true) {
                lines = BabblingBrook.Client.User.Config.message_box_lines;
            } else {
                lines = BabblingBrook.Client.DefaultConfig.message_box_lines;
            }
            jQuery('#messages_inner').css('min-height', lines + '.5em');
            jQuery('#messages_inner').css('max-height', lines + '.5em');
            minimized_height = jQuery('#messages_inner').height();

            // The min height for message_buttons is missing 2 pixels due to using a mix of ems and pixels.
            buttons_height = jQuery('#message_buttons').css('min-height');
            buttons_height = buttons_height.substring(0, buttons_height.length - 2);
            buttons_height = Math.ceil(buttons_height);
            buttons_height += 2;
            jQuery('#message_buttons').css('min-height', buttons_height + 'px');
        },

        /**
         * This will acknowledge the current message and show the next one.
         *
         * This is used by error callbacks that do asynchronous stuff and can't show the next message until
         * this one has finished.
         *
         * @return {void}
         */
        acknowledgeMessage : function () {
            var current_index = parseInt(jQuery('#messages_inner').attr('data_index'));
            messages[current_index].acknowledged = true;
            jQuery('#message_loading').addClass('hide');
            deferred_messages.resolve();
        },

        /**
         * Adds a message to the message array.
         *
         * @param {object} message See the messages object for a full definition.
         *                 buttons, acknowledged, priority and url are optional.
         * @param {boolean} [ok_to_dupe=false] Allows the adding of dupplicate messages.
         *      Facilitates pushing a message to the back of the queue.
         *
         * @return void
         */
        addMessage : function (message, ok_to_dupe) {
            var ignore_set = false;
            if (BabblingBrook.Library.isArray(message.buttons) === false) {
                message.buttons = [];
            }
            // Check this isn't a duplicate.
            if (typeof ok_to_dupe === 'undefined' || ok_to_dupe === false) {
                var exit = false;
                jQuery.each(messages, function (i, queued_message) {
                    if (queued_message.acknowledged === false
                        && message.message === queued_message.message
                        && message.url === queued_message.url
                        && message.type === queued_message.type
                    ) {
                        exit = true;
                        return false;   // Exit the .each
                    }
                    return true;        // Continue with the .each
                });
                if (exit === true) {
                    return;
                }
            }

            if (typeof message.priority !== 'number') {
                message.priority = 100;
            }

            message.acknowledged = false;

            // Add a reload page button for all errors
            if (message.type === 'error') {
                // Ensure it appears before the ignore button.
                var index = 0;
                jQuery.each(message.buttons, function(i, button) {
                    if(button.name === 'Ignore') {
                        return false;    // Escape the .each
                    }
                    index = i + 1;
                    return true;        // Continue with the .each
                });
                var reload_button = {
                    name : 'Reload Page',
                    callback : function () {
                        window.location.reload(true);
                    }
                };
                message.buttons.splice(index, 0, reload_button);
                error_stack.push(message.message);
            }

            // Add a default 'ok'  for all messages that don't have any buttons.
            if (message.type === 'notice' && message.buttons.length === 0) {
                var ok_button = {
                    name : 'ok',
                    callback : function () {
                        BabblingBrook.Client.Component.Messages.showNext();
                    }
                };
                message.buttons.push(ok_button);
                ignore_set = true;
            }

            // Add an empty ignore button to the buttons array.
            jQuery.each(message.buttons, function(i, button) {
                if (button.name === 'Ignore') {
                    ignore_set = true;
                }
            });
            if (message.type === 'suggestion') {
                ignore_set = true;
            }
            if (ignore_set === false) {
                message.buttons.push({
                    name : 'Ignore',
                    callback : function () {}
                });
            }

            messages.push(message);
            if (typeof deferred_messages === 'undefined') {
                BabblingBrook.Client.Component.Messages.showNext();
            } else {
                deferred_messages.done(function () {
                    BabblingBrook.Client.Component.Messages.showNext();
                });
            }
            recaculateSuggestionsCount();
        },

        /**
         * Shows the next message in the queue for this page.
         *
         * Does not acknowledge the current message. Just checks the stack for what should be displayed and shows that.
         *
         * @return void
         */
        showNext : function() {
            // Need to restore the message selector as ajaxurl may have loaded a new page.
            jq_messages = jQuery('#messages');
            if (completely_off === true) {
                jq_messages.addClass('hide');
                return;
            }

            if (BabblingBrook.Settings.feature_switches['SUGGESTION_MESSAGES'] === false) {
                jq_messages.addClass('hide');
                return;
            }

            jQuery('#message_buttons').css('min-height', buttons_height + 'px');

            var current_index = parseInt(jQuery('#messages_inner').attr('data_index'));
            // If the index does not exist then reinstate the default.
            if (isNaN(current_index)) {
                current_index = -1;
            }

            var next_index;
            var next_message;
            var current_url = window.location.pathname;
            var first_pass = true;
            jQuery.each(messages, function(i, message) {
                // If the messsage is acknowledged then skip this one.
                if (message.acknowledged === true) {
                    return true;
                }

                if (first_pass === true) {
                    if (suggestions_on === false && message.type === 'suggestion') {
                        return true;
                    }
                    next_message = message;
                    next_index = i;
                    first_pass = false;
                    return true;
                }

                // If suggestions are on then only show suggestions or errors.
                if (suggestions_on === true && message.type !== 'suggestion' && message.type !== 'error') {
                    return true;
                }
                // suggestions are only shown when requested.
                if (suggestions_on === true || message.type !== 'suggestion') {
                    // Is this iterations message of a higher type priority than the current best.
                    if (types[message.type] <= types[next_message.type]) {
                        // Is the current iterations url valid for the current page.
                        if (typeof message.url === 'undefined' || message.url === current_url) {
                            // Is the current iterations message priority higher than the current best.
                            if (types[message.type] < types[next_message.type]
                                || next_message.priority < message.priority
                            ) {
                                next_message = message;
                                next_index = i;
                            }
                        }
                    }
                }
                return true; // Continue with the .each
            });

            if (suggestions_on === false && (typeof next_message === 'undefined'
                || (next_message.type !== 'error' && next_message.type !== 'notice'))
            ) {
                jq_messages.slideUp();
                return;
            } else {
                jq_messages.slideDown();
            }

            if (typeof next_message !== 'undefined' && next_message.type === 'error') {
                jq_messages.addClass('message-error');
                jq_messages.find('.error-details').text(next_message.error_details);
                jQuery('#report_bug').removeClass('hide');
            } else {
                jq_messages.removeClass('message-error');
                jQuery('#report_bug').addClass('hide');
            }
            if (typeof next_message === 'undefined' && loading === true) {
                // showNext is called again when the loading has finished.
                return;
            }

            // If the current index is the same as the next one then nothing needs to be done.
            if (current_index === next_index) {
                return;
            }

            // Show the new message
            if(typeof next_message !== 'undefined') {
                current_message = next_message;
                var cropped_message = next_message.message;
                if (minimized === true) {
                    cropped_message = cropMessage(next_message.message);
                }
                // If there is content in full or the message is cropped, add a more button

                next_message.buttons = addMoreButton(next_message, cropped_message);

                jQuery('#messages_inner', jq_messages)
                    .attr('data_index', next_index)
                    .html(cropped_message);
                if (typeof next_message.full === 'undefined') {
                    next_message.full = '';
                }
                jQuery('#messages_full', jq_messages).html(next_message.message + '<br><br>' + next_message.full);
                jQuery('#messages').removeClass('block-loading');

                displayButtons(next_message.buttons, next_index);

            } else {
                current_message = undefined;
                if (suggestions_on === false && jq_messages.hasClass('hide') === false) {
                    jq_messages.addClass('hide');
                    jq_messages.slideUp(function(){
                        jq_messages.addClass('hide');
                    });
                } else {
                    // Show default message
                    showDefaultMessage();
                }
            }

        },

        /**
         * Getter for the current stack of erorr messages that have been reported in javascript.
         *
         * @return {string[]}
         */
        getErorrStack : function () {
            return error_stack;
        },

        /**
         * Prevents new messages from being displayed. Even error messages
         *
         * This needs to  be done when the page is being reloaded, to prevent errors appear as the page stops.
         *
         * @return {void}
         */
        turnOff : function () {
            completely_off = true;
        },

        /**
         * Minimizes the message being displayed.
         *
         * A public function so that it can be called when the page reloads through ajax.
         *
         * @return {void}
         */
        minimizeMessage : function () {
            return;
//            jQuery('#messages_inner').removeClass('hide');
//            jQuery('#messages_full').addClass('hide');
//            jQuery('.message-button-more').text('More');
        },

        /**
         * Turns on the border loading class.
         *
         * Called by message callbacks when the callback takes time to process or involves a server call.
         *
         * @returns {void}
         */
        turnOnBorderLoading : function () {
            loading = true;
            jQuery('#messages').addClass('block-loading');
        },

        /**
         * Turns off the border loading class.
         *
         * @returns {void}
         */
        turnOffBorderLoading : function () {
            loading = false;
            jQuery('#messages').removeClass('block-loading');
            BabblingBrook.Client.Component.Messages.showNext();
        },

        /**
         * Returns a count of the number of suggestion messages.
         *
         * @returns {number}
         */
        getSuggestionCount : function () {
            return suggestion_count;
        }

    };
}());