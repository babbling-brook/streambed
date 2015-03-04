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
 * @fileOverview Javascript used on the Rhythm update page.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.ManageRhythm !== 'object') {
    BabblingBrook.Client.Page.ManageRhythm = {};
}

/**
 * @namespace Used by the Rhythm update page to allow users to update their Rhythms.
 * @package JS_Client
 */
BabblingBrook.Client.Page.ManageRhythm.Update = (function () {
    'use strict';

    /**
     * Call back for a publish request.
     * @param {object} data JSON OBJECT Data returned by the request
     */
    var publishCallback = function (data) {
        // Turn off publish and delete. Turn on Deprecate.
        jQuery('#rhythm_status_actions .publish').addClass('hide');
        jQuery('#rhythm_status_actions .deprecate').removeClass('hide');
        jQuery('#rhythm_status_actions .delete').addClass('hide');
        jQuery('#rhythm_status').text('Public');
        jQuery('#RhythmExtra_description').attr('disabled', 'disabled');
        jQuery('#rhythm_cats').attr('disabled', 'disabled');
        jQuery('.cm-s-default').removeClass('cm-s-default').addClass('cm-s-disabled');
        setupHideIfNotPrivate();
    };

    /**
     * Call back for a deprecate request.
     * @param {object} data JSON OBJECT Data returned by the request
     */
    var deprecateCallback = function (data) {
        // Turn off Deprecate. Turn on Publish.
        jQuery('#rhythm_status_actions .publish').removeClass('hide');
        jQuery('#rhythm_status_actions .deprecate').addClass('hide');
        jQuery('#rhythm_status_actions .edit').addClass('hide');
        jQuery('#rhythm_status').text('Deprecated');
        setupHideIfNotPrivate();
    };

    /**
     * Call back for a delete request.
     * @param {object} data JSON OBJECT Data returned by the request
     */
    var deleteCallback  = function (data) {
        var jq_rhythm_deleted = jQuery('#rhythm_deleted_template').clone();
        jQuery('.deleted-rhythm-name', jq_rhythm_deleted).text(data.name)
        jQuery('#sidebar #versions').prev().remove();
        jQuery('#sidebar #versions').remove();
        jQuery('#sidebar_update_rhythm').remove();
        jQuery('#sidebar_view_rhythm').remove();
        jQuery('#rhythm_form').html(jq_rhythm_deleted.html());
    };

    /**
     * Make an ajax request for a pubish/deprecate/delete button.
     * @param {string} this_button DOM identifier for the button.
     * @param {string} action 'pubish' 'deprecate' or 'delete'.
     * @notice Should be converted  to BabblingBrook.Library.post (),
     *         but this is due for conversion so that it can be done via the protocol.
     */
    var buttonAjax = function (this_button, action) {
        jQuery('#ajax_load').addClass('ajax-loading');
        var row_id = jQuery('#RhythmExtra_rhythm_extra_id').val();
        BabblingBrook.Library.post(
            jQuery('#ajax_url').val(),
            {
                rhythm_extra_id : row_id,
                action : action
            },
            function (data) {
                jQuery('#ajax_load').removeClass('ajax-loading');
                if (data === null || typeof data !== 'object') {
                    console.error('Error publishing Rhythm: No data returned');
                    return;
                } else {
                    if (typeof data.error !== 'undefined') {
                        console.error('Error ' + action + ' Rhythm: ' + data.error);
                        return;
                    }
                    // Action Succesful
                    switch (action) {
                        case 'publish':
                            publishCallback(data);
                            break;
                        case 'deprecate':
                            deprecateCallback(data);
                            break;
                        case 'delete':
                            deleteCallback(data);
                            break;
                    }
                }
            }
        );
    };

    /**
     * Setup publish buttons in the grid.
     */
    var setUpPublishButton = function () {
        jQuery('#rhythm_status_actions .publish').on('click', function () {
            buttonAjax(this, 'publish');
        });
    };

    /**
     * Setup deprecate buttons in the grid.
     */
    var setUpDeprecateButton = function () {
        jQuery('#rhythm_status_actions .deprecate').on('click', function () {
            buttonAjax(this, 'deprecate');
        });
    };

    /**
     * Setup Delete buttons in the grid.
     */
    var setUpDeleteButton = function () {
        jQuery('#rhythm_status_actions .delete').on('click', function () {
            if (confirm('Are you sure? Deleted Rhythms are not recoverable.')) {
                buttonAjax(this, 'delete');
            }
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
                    'duplicate' : true,
                    'duplicate_name' : jQuery('#duplicate_name').val()
                },
                /**
                 * Callback for a request to duplicate an Rhythm
                 *
                 * @param {object} duplicate_data The returned data.
                 * @param {string} [duplicate_data.error] The error, if there is one.
                 * @param {string} dta.url The url of the new Rhythm.
                 *
                 * @return void
                 */
                function (duplicate_data) {
                    if (typeof duplicate_data.error !== 'undefined') {
                        jQuery('#duplicate_error').html(duplicate_data.error).removeClass('hide');
                        jQuery('#duplicate_success').addClass('hide');
                        return;
                    }
                    if (duplicate_data.url !== null) {
                        var jq_duplicate = jQuery('#rhythm_duplicated_template').clone();
                        jQuery('.duplicated-rhythm-url', jq_duplicate).attr('href', duplicate_data.url);
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
     * Click event for creating a new version.
     */
    var setupNewVersion = function () {
        jQuery('#new_version').click(function () {
            jQuery('#new_version_loading').css({'visibility' : 'visible'});
            // Currently a client request but due for conversion to a domus doamin
            // request when streams are managed through the domus domain.
            BabblingBrook.Library.post(
                window.location.pathname,
                {
                    'new_version' : jQuery('#version option:selected').val()
                },
                /**
                 * Callback for setting up a new version.
                 *
                 * @param {object} callback_data Data returned with the request for a new version.
                 * @param {string} callback_data.error An error message to display to the user.
                 * @param {string} callback_data.url The url of the new version.
                 *
                 * @return void
                 */
                function (callback_data) {
                    if (typeof callback_data.error !== 'undefined') {
                        jQuery('#new_version_error').html(callback_data.error).removeClass('hide');
                        jQuery('#new_version_success').addClass('hide');
                    }
                    if (typeof callback_data.url !== 'undefined') {
                        var jq_new_version = jQuery('#new_rhythm_version_template').clone();
                        jQuery('.new-rhythm-version-url', jq_new_version).attr('href', callback_data.url);
                        jQuery('#new_version_success').html(jq_new_version.html()).removeClass('hide');
                        jQuery('#new_version_error').addClass('hide');
                    }
                    jQuery('#new_version_loading').css({'visibility' : 'hidden'});
                },
                'json'
            );
                return false;
        });
    };

    /**
     * Callback for request to add a new parameter.
     *
     * @param {string} name The name of the parameter.
     * @param {string} hint The hint text for the parameter.
     * @param {object} success_data The data returned from the server.
     * @param {object} success_data.[errors] If there are any errors then this will contain an array of error messages.
     *
     * @return {void}
     */
    var onAddNewParamReturned = function (name, hint, success_data) {
        if (typeof success_data.errors !== 'undefined') {
            jQuery('#add_parameter_error>td').empty();
            jQuery.each(success_data.errors, function (i, error) {
                jQuery('#add_parameter_error>td').append('<div>' + error + '</div>');
            });
            jQuery('#add_parameter_error').removeClass('hide');
        } else {
            jQuery('#add_parameter_error').addClass('hide');
            var jq_new_param = jQuery('#parameter_row_template>tbody>tr').clone();
            jQuery('.rhythm-param-name>input', jq_new_param).val(name);
            jQuery('.rhythm-param-hint>textarea', jq_new_param).val(hint);
            jQuery('#rhythm_parameters>tbody').append(jq_new_param);
            jQuery('#new_param_name').val('');
            jQuery('#new_param_hint').val('');
            jq_new_param.find('.rhythm-param-original-name>input').val(name);
        }
        jQuery('#add_new_parameter').removeClass('block-loading');
    };

    /**
     * Opens the section to enable the adding of new paramaters.
     *
     * @returns {void}
     */
    var onClickAddNewParameter = function () {
        var url = BabblingBrook.Library.changeUrlAction(window.location.href, 'addparameter');
        url = BabblingBrook.Library.extractPath(url);
        jQuery('#add_new_parameter').addClass('block-loading');
        BabblingBrook.Library.post(
            url,
            {
                name : jQuery('#new_param_name').val(),
                hint : jQuery('#new_param_hint').val()
            },
            onAddNewParamReturned.bind(null, jQuery('#new_param_name').val(), jQuery('#new_param_hint').val())
        );
    };

    /**
     * Opens the section to enable the adding of new paramaters.
     *
     * @returns {void}
     */
    var onClickOpenNewParameter = function () {
        jQuery('#no_rhythm_parameters').addClass('hide');
        jQuery('#rhythm_parameters').removeClass('hide');
        jQuery('#open_new_parameter').addClass('hide');
    };

    /**
     * Callback for a request to remove a parameter.
     *
     * @param {object} jq_row A jQuery object pointing to the paramater row that is being removed.
     * @param {object} removed_data The status data returned from the server.
     * @param {object} removed_data.[errors] If there are any errors then this will contain an array of error messages.
     *
     * @returns {void}
     */
    var onParamRemovedReturned = function (jq_row, removed_data) {
        if (typeof removed_data.errors !== 'undefined') {
            showParamaterRowError(jq_row, removed_data);
        } else {
            jQuery('td', jq_row).fadeOut(250);
            if (jq_row.next().hasClass('parameter-row-error') === true) {
                jq_row.next().fadeOut(250);
            }
        }
        jQuery(this).removeClass('block-loading');
    }

    /**
     * Handles click events on remove parameter buttons.
     *
     * @returns {void}
     */
    var onClickRemoveParameter = function () {
        var url = BabblingBrook.Library.changeUrlAction(window.location.href, 'removeparameter');
        url = BabblingBrook.Library.extractPath(url);
        jQuery(this).addClass('block-loading');
        var jq_row = jQuery(this).parent().parent();
        BabblingBrook.Library.post(
            url,
            {
                name : jq_row.find('.rhythm-param-name>input').val()
            },
            onParamRemovedReturned.bind(null, jq_row)
        );
    };


    var showParamaterRowError = function (jq_row, error_data) {
        if (jq_row.next().hasClass('parameter-row-error') === true) {
            jq_row.next().remove();
        }
        var jq_new_error = jQuery('#parameter_row_error_template>tbody>tr').clone();
        jQuery.each(error_data.errors, function (i, error) {
            jQuery('td', jq_new_error).append('<div>' + error + '</div>');
        });
        jq_row.after(jq_new_error);
    };

    /**
     * Callback for a request to update a parameter.
     *
     * @param {object} jq_row A jQuery object pointing to the paramater row that has been updated.
     * @param {stirng} new_name The new name for the parameter.
     * @param {object} update_data The status data returned from the server.
     * @param {object} update_data.[errors] If there are any errors then this will contain an array of error messages.
     *
     * @returns {void}
     */
    var onParamUpdateReturned = function (jq_row, new_name, update_data) {
        if (typeof update_data.errors !== 'undefined') {
            showParamaterRowError(jq_row, update_data);
        } else {
            if (jq_row.next().hasClass('parameter-row-error') === true) {
                jq_row.next().remove();
            }
            jq_row.find('.rhythm-param-original-name>input').val(new_name);
        }
        jQuery('.rhythm-param-name>input, .rhythm-param-hint>textarea', jq_row).removeClass('block-loading');
    };

    /**
     * Updates a paramater when its name or hint has been changed.
     *
     * @returns {void}
     */
    var onChangedParameter = function () {
        var jq_row = jQuery(this).parent().parent();
        var url = BabblingBrook.Library.changeUrlAction(window.location.href, 'updateparameter');
        url = BabblingBrook.Library.extractPath(url);
        jQuery('.rhythm-param-name>input, .rhythm-param-hint>textarea', jq_row).addClass('block-loading');
        BabblingBrook.Library.post(
            url,
            {
                original_name : jq_row.find('.rhythm-param-original-name>input').val(),
                name : jq_row.find('.rhythm-param-name>input').val(),
                hint : jq_row.find('.rhythm-param-hint>textarea').val(),
            },
            onParamUpdateReturned.bind(null, jq_row, jq_row.find('.rhythm-param-name>input').val())
        );
    };

    /**
     * Sets up the paramaters section of the update form.
     *
     * @returns {void}
     */
    var setupParameters = function () {
        jQuery('#open_new_parameter').click(onClickOpenNewParameter);
        jQuery('#add_new_parameter').click(onClickAddNewParameter);
        jQuery('#rhythm_parameters tbody').on('click', '.remove-parameter', onClickRemoveParameter);
        jQuery('#rhythm_parameters tbody').on('change', '.rhythm-param-name>input', onChangedParameter);
        jQuery('#rhythm_parameters tbody').on('change', '.rhythm-param-hint>textarea', onChangedParameter);
    };

    var onSaveClicked = function () {
        jQuery('.error').addClass('hide');
        jQuery('#save_rhythm').addClass('button-loading');
        var url = BabblingBrook.Library.changeUrlAction(window.location.pathname, 'updatejson');
        BabblingBrook.Library.post(
            url,
            {
                description : jQuery('#rhythm_description').val(),
                category : jQuery('#rhythm_category').val(),
                javascript : BabblingBrook.Client.Component.CodeMirror.getValue()
            },
            function (response_data) {
                if (response_data.success !== true) {
                    jQuery.each(response_data.errors, function (error_name, error) {
                        jQuery('#rhythm_' + error_name + '_error').text(error).removeClass('hide');
                    });
                }
                jQuery('#save_rhythm').removeClass('button-loading');
            }
        );
    };

    /**
     * Hides the update fields if this rhythm is not private.
     *
     * @returns {undefined}
     */
    var setupHideIfNotPrivate = function () {
        if (jQuery('#rhythm_status').text() !== 'Private') {
            jQuery('.only-private').addClass('hide');
            jQuery('#no_editing').removeClass('hide');
        }
    };

    return {

        construct : function () {
            setUpPublishButton();
            setUpDeprecateButton();
            setUpDeleteButton();
            setupDuplicate();
            setupNewVersion();
            setupParameters();
            setupHideIfNotPrivate();
            jQuery('#save_rhythm').click(onSaveClicked);

            BabblingBrook.Client.Component.CodeMirror.create();
        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageRhythm.Update.construct();
});