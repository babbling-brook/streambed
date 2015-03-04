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
 * @fileOverview Code used to show the bug report form.
 * @author Sky Wickenden
 */

/**
 * Shows the bug report form.
 */
BabblingBrook.Client.Component.ReportBug = (function () {
    'use strict';

    var jq_bug_dialog;


    /**
     * An array of errors generated in subdomains. Attatched to error reports.
     * Indexed by timestamp.
     * @type {object}
     */
    var sub_domain_errrors = {};

    /**
     * Callback for when the bug form has been displayed.
     *
     * @returns {undefined}
     */
    var onBugPostFormCreated = function (form_id) {
        var error_message = jQuery.trim(jQuery('#content>#messages>#messages_full').text());
        var error_details = jQuery.trim(jQuery('#content>#messages>.error-details').text());
        if (error_message.length > 0 && jQuery('#content>#messages>#messages_full').is(':visible')) {
            jQuery('#post_' + form_id + '_field_1_text').val(error_message);
        }
        if (error_details.length > 0 && jQuery('#content>#messages>#messages_full').is(':visible')) {
            jQuery('#post_' + form_id + '_field_3_text').val(error_details);
        }
        jQuery('#post_' + form_id + '_field_5_text').val(window.navigator.userAgent);

        var errors = BabblingBrook.Client.Component.Messages.getErorrStack();
        var error_string = errors.reverse().join('\n');
        if (Object.keys(sub_domain_errrors).length > 0) {
            error_string += '\n\nSubdomain Errors:\n';
        }
        jQuery.each(sub_domain_errrors, function (timestamp, error) {
            error_string += timestamp + ' : ' + error +'\n';
        });
        jQuery('#post_' + form_id + '_field_6_text').val(error_string);

        jQuery('#post_' + form_id + '_field_7_text').val(window.location.href);
        // Doing this in a timeout
        // Otherwise the style has not been applied when the code runs in firefox.
        // a 1 ms timout works, but leaving 30 to be on the safe side.
        // Not sure why this is happening.
        setTimeout(function(){
            BabblingBrook.Client.Component.MakePost.ResizeTextarea(jQuery('#post_' + form_id + '_field_5_text'), true);
            BabblingBrook.Client.Component.MakePost.ResizeTextarea(jQuery('#post_' + form_id + '_field_6_text'), true);
            BabblingBrook.Client.Component.MakePost.ResizeTextarea(jQuery('#post_' + form_id + '_field_7_text'), true);
        }, 30);
    };

    /**
     * Callback for when the bug post has been created.
     *
     * @param {object} jq_bug_dialog Jquery object holding the dialogue that the bug form was displayed in.
     * @param {object} post The bug post that has been submitted.
     *
     * @returns {undefined}
     */
    var onBugPostCreated = function (jq_bug_dialog, post) {
        jQuery('.ui-dialog #bug_post_container .make-post').remove();
        var jq_bug_submitted = jQuery('#bug_submitted_template').html();
        var new_post_link = '/post/' + post.domain + '/' + post.post_id;
        jQuery('.ui-dialog #bug_post_container').append(jq_bug_submitted);
        jQuery('.ui-dialog #bug_post_container #submitted_bug_link').attr('href', new_post_link)
    };

    /**
     * Callback for when the bug report form is canceled.
     *
     * @returns {undefined}
     */
    var onBugPostCanceled = function () {
        jq_bug_dialog.dialog('close');
    };

    /**
     * Callback for when the bug button is clicked.
     *
     * @returns {undefined}
     */
    var onOpenBugForm = function () {
        jq_bug_dialog = jQuery('#bug_post_template>div').clone();

        jq_bug_dialog.dialog({
            autoOpen : true,
            title : 'Report a bug',
            width : 600,
            closeText : '',
            position : {my : 'top+40px', at : 'top', of : '#bug_form_placement'},
            focus : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            },
            dragStart : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            },
            create : function (event) {
                // Move the dialog to the top of the body, or loading content will push it down.
                jQuery('body')
                    .prepend(jQuery('.ui-dialog[aria-labelledby=ui-dialog-title-bug_post_container]'));

                jq_bug_dialog
                   .dialog('open');

                var bug_post = new BabblingBrook.Client.Component.MakePost(
                    onBugPostCreated.bind(null, jq_bug_dialog),
                    onBugPostCanceled
                );
                var bug_url = BabblingBrook.Library.makeStreamUrl(
                    BabblingBrook.Client.ClientConfig.bug_stream,
                    'json'
                );
                bug_post.setupNewPost(
                    bug_url,
                    jQuery('#bug_post_form', jq_bug_dialog),
                    'open',
                    undefined,
                    undefined,
                    undefined,
                    'public',
                    '',
                    onBugPostFormCreated,
                    'Submit bug report',
                    'Cancel bug report'
                );
            }
        });

        return false;
    };

    return {

        construct : function () {
            jQuery(document).on('click', '#report_bug', onOpenBugForm);
            jQuery('#small_bug').unbind('click').click(function(event) {
                onOpenBugForm();
            });
        },

        /**
         * Rebinds the top nav bug link (It is added durirng a tutorial.)
         * @returns {undefined}
         */
        rebindBugLink : function () {
            jQuery('#small_bug').unbind('click').click(function(event) {
                onOpenBugForm();
            });
        },

        /**
         * Appends a subdomain error to the erorr stack.
         */
        appendSubDomainError : function (error_data) {
            var now = Math.round(new Date().getTime());
            sub_domain_errrors[now] = error_data.domain + ' : ' + error_data.error;
        }
    };
}());


