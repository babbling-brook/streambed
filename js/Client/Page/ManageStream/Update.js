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
 * @fileOverview Javascript used on the Stream update page.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.ManageStream !== 'object') {
    BabblingBrook.Client.Page.ManageStream = {};
}


/**
 * @namespace Editing of streams.
 * @package JS_Client
 */
BabblingBrook.Client.Page.ManageStream.Update = (function () {
    'use strict';

    /**
     * Click event for creating a new version.
     */
    var setupNewVersion = function () {
        jQuery('#new_version').click(function () {
            jQuery('#new_version_loading').css({'visibility' : 'visible'});
            // Currently a client request but due for conversion to a domus domain
            // request when streams are managed through the domus domain.
            var url = BabblingBrook.Library.changeUrlAction(window.location.pathname, 'newversion');
            var new_version = jQuery('#version option:selected').val();
            BabblingBrook.Library.post(
                url,
                {
                    new_version : new_version
                },
                function (data) {
                    if (typeof data.error !== 'undefined') {
                        jQuery('#new_version_error').html(data.error).removeClass('hide');
                        jQuery('#new_version_success').addClass('hide');
                    }
                    if (typeof data.url !== 'undefined') {
                        var jq_new_version = jQuery('#new_stream_version_template').clone();
                        jQuery('.new-stream-version-url', jq_new_version).attr('href', data.url);
                        jQuery('#new_version_success').html(jq_new_version.html()).removeClass('hide');
                        jQuery('#new_version_error').addClass('hide');
                        var jq_option = jQuery('<option>');
                        jq_option
                            .val(new_version)
                            .text(new_version);
                        jQuery('#sidebar select#versions').append(jq_option)
                    }
                    jQuery('#new_version_loading').css({'visibility' : 'hidden'});
                },
                'json'
            );
        });
    };

    /**
     * Click event for duplication.
     */
    var setupDuplicate = function () {
        jQuery('#duplicate').click(function () {
            jQuery('#duplicate_loading').css({'visibility' : 'visible'});
            // Currently a client request but due for conversion to a domus domain
            // request when streams are managed through the domus domain.
            BabblingBrook.Library.post(
                window.location.pathname,
                {
                    'duplicate_name' : jQuery('#duplicate_name').val()
                },
                function (data) {
                    if (typeof data.error !== 'undefined') {
                        jQuery('#duplicate_error').html(data.error).removeClass('hide');
                        jQuery('#duplicate_success').addClass('hide');
                        return;
                    }
                    if (data.url !== null) {
                        var jq_duplicate = jQuery('#stream_duplicated_template').clone();
                        jQuery('.duplicated-stream-url', jq_duplicate).attr('href', data.url);
                        jQuery('#duplicate_success').html(jq_duplicate.html()).removeClass('hide');
                        jQuery('#duplicate_error').addClass('hide');
                    }

                    jQuery('#duplicate_loading').css({'visibility' : 'hidden'});
                },
                'json'
            );
        });
    };

    /**
     * Call back for after the server has responded to a request to change a streams status.
     *
     * @param {string} action 'publish' 'deprecate' or 'delete'.
     * @param {object} response_data The data sent back from the server
     *      after the request to change the status of a stream.
     * @param {boolean} response_data.deletable Is the stream deletable or not.
     *
     * @return void
     */
    var onChangedStatus = function (action, response_data) {
        if (response_data.success === false) {
            onChangedStatusError(response_data.error);
        } else {
            if (response_data.deletable === true) {
                jQuery('.revert').removeClass('hide');
                jQuery('.delete').removeClass('hide');
            } else {
                jQuery('.revert').addClass('hide');
                jQuery('.delete').addClass('hide');
            }
            switch (action) {
                case 'publish':
                    jQuery('.stream_status').text('Public');
                    jQuery('.publish').addClass('hide');
                    jQuery('.deprecate').removeClass('hide');
                    jQuery('.stream_status').text('public');
                     // Remove the no change option from next version.
                    jQuery('#version option[value="No change"]').remove();
                    // Fix elements that can't be edited once public.
                    jQuery('#ajax_load').removeClass('ajax-loading');
                    break;

                case 'deprecate':
                    jQuery('.stream_status').text('Deprecated');
                    jQuery('.publish').removeClass('hide');
                    jQuery('.deprecate').addClass('hide');
                    jQuery('.stream_status').text('deprecated');
                    jQuery('#ajax_load').removeClass('ajax-loading');
                    break;

                case 'delete':
                    // Delete succesful.
                    jQuery('#stream_form').html('');
                    jQuery('#stream_details_operation').remove();
                    jQuery('#update_stream_operation').remove();
                    jQuery('#edit_stream_operation').remove();
                    jQuery('#edit_stream_fields_operation').remove();
                    jQuery('#stream_posts_operation').remove();
                    jQuery('#stream_spacer_operation').remove();
                    jQuery('#switch_version_operation').remove();
                    jQuery('#stream_overview').html('This stream has been deleted.');
                    jQuery('#ajax_load').removeClass('ajax-loading');
                    break;

                case 'revert':
                    // Delete succesful.
                    jQuery('.stream_status').text('Private');
                    jQuery('.publish').removeClass('hide');
                    jQuery('.deprecate').addClass('hide');
                    jQuery('.revert').addClass('hide');
                    jQuery('.stream_status').text('private');
                    break;
            }
        }
    };

    var changeStatus = function (action){
        var url = BabblingBrook.Library.changeUrlAction(window.location.pathname, 'changestatus');
        BabblingBrook.Library.post(
            url,
            {
                action : action
            },
            onChangedStatus.bind(null, action),
            onChangedStatusError.bind(null, 'Error requesting status change for ' + url)
        );
    };

    /**
     * Call back for when there is an error requesting a status change.
     *
     * @param {string} error An error message.
     */
    var onChangedStatusError = function (error) {
        throw error;
    };

    /**
     * Setup the status buttons.
     */
    var setupStatusButtons = function () {
        // Publish button
        jQuery('#stream_status_actions .publish').click(function () {
            var current_status = jQuery.trim(jQuery('.stream_status').text());
            if (current_status === 'private') {
                var message = 'Are you sure? You will not be able to edit the stream once a ' +
                   'user has made a post in your stream. You can make test posts without publishing by clicking on ' +
                   'the \'posts\' link  to the right. (You will be able to edit your stream by creating a new version.)' ;
                if (!confirm(message)) {
                    return;
                }
            }
            jQuery('#ajax_load').addClass('ajax-loading');
            changeStatus('publish');
        });
        // Delete button.
        jQuery('#stream_status_actions .delete').click(function () {
            jQuery('#ajax_load').addClass('ajax-loading');
            if (!confirm('Are you sure? Deleted Streams are not recoverable.')) {
                return;
            }
            changeStatus('delete');
        });
        // Deprecate button.
        jQuery('#stream_status_actions .deprecate').click(function () {
            jQuery('#ajax_load').addClass('ajax-loading');
            changeStatus('deprecate');
        });
        // Revert button.
        jQuery('#stream_status_actions .revert').click(function () {
            jQuery('#ajax_load').addClass('ajax-loading');
            changeStatus('revert');
        });
    };

    var setupPostModeDropDown = function () {
        jQuery('#post_mode').change(function(){
            var selected = jQuery(this).val();
            jQuery('#post_mode').addClass('textbox-loading');
            BabblingBrook.Library.post(
                jQuery('#update_url').val() + 'postmode',
                {
                    post_mode_id : selected,
                    stream_extra_id : jQuery('#StreamExtra_stream_extra_id').val()
                },
                /**
                 * Success callback for updating the post mode of an stream.
                 *
                 * @param {object} callback_data Any data sent back from the request.
                 * @param {boolean} callback_data.success Did the request succeed.
                 *
                 * @return void
                 */
                function (callback_data) {
                    if (callback_data.success === true) {
                        jQuery('#post_mode').removeClass('textbox-loading');
                    }
                }
            );
        });
    };

    return {

        construct : function () {
            setupStatusButtons();
            setupDuplicate();
            setupNewVersion();
            setupPostModeDropDown();
        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageStream.Update.construct();
});