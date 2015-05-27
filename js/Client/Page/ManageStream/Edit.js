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
BabblingBrook.Client.Page.ManageStream.Edit = (function () {
    'use strict';

    /**
     * Click event for updating the description.
     */
    var setupUpdateDescription = function () {
        jQuery('#description').change(function () {
            jQuery('#description').addClass('textbox-loading');
            // Currently a client request but due for conversion to a domus domain
            // request when streams are managed through the domus domain.
            var url = BabblingBrook.Library.changeUrlAction(window.location.pathname, 'UpdateDescription');
            BabblingBrook.Library.post(
                url,
                {
                    description : jQuery('#description').val()
                },
                function (data) {
                    if (data.error !== false) {
                        jQuery('#description_error').html(data.error).removeClass('hide');
                    } else {
                        jQuery('#description_error').html('').addClass('hide');
                    }
                    jQuery('#description').removeClass('textbox-loading');
                }
            );
        });
    };

    /**
     * Click event for updating the description.
     */
    var setupUpdatePresentationType = function () {
        jQuery('#presentation_type').change(function () {
            jQuery('#presentation_type').addClass('textbox-loading');
            // Currently a client request but due for conversion to a domus domain
            // request when streams are managed through the domus domain.
            var url = BabblingBrook.Library.changeUrlAction(window.location.pathname, 'UpdatePresentationType');
            BabblingBrook.Library.post(
                url,
                {
                    presentation_type : jQuery('#presentation_type').val()
                },
                function (data) {
                    if (typeof data.error !== 'undefined') {
                        jQuery('#presentation_type_error').html(data.error).removeClass('hide');
                    } else {
                        jQuery('#presentation_type_error').html('').addClass('hide');
                    }
                    jQuery('#presentation_type').removeClass('textbox-loading');
                }
            );
        });
    };

    /**
     * Setup the form for amending the default filter rhythms in a stream.
     *
     * @returns {void}
     */
    var setupDefaultSortRhythms = function () {
        var jq_default_sort_rhythms_container = jQuery('#default_sort_rhythms_container');
        var base_url = BabblingBrook.Library.changeUrlAction(window.location.pathname, '');

        BabblingBrook.Client.Component.ListSelector(
            'rhythm',
            'default_rhythms',
            jq_default_sort_rhythms_container,
            base_url + 'GetDefaultRhythms',
            base_url + 'ReplaceDefaultRhythm',
            base_url + 'AddDefaultRhythm',
            base_url + 'DeleteDefaultRhythm',
            base_url + 'SwapDefaultRhythm',
            'sort',
            undefined,
            {
                version : false
            }
        );
    };

    /**
     * Setup the form for amending the default filter rhythms in a stream.
     *
     * @returns {void}
     */
    var setupChildStreams = function () {
        var jq_default_child_streams_container = jQuery('#child_streams_container');
        var base_url = BabblingBrook.Library.changeUrlAction(window.location.pathname, '');

        BabblingBrook.Client.Component.ListSelector(
            'stream',
            'child_streams',
            jq_default_child_streams_container,
            base_url + 'GetChildStreams',
            base_url + 'ReplaceChildStream',
            base_url + 'AddChildStream',
            base_url + 'DeleteChildStream',
            base_url + 'SwapChildStream',
            undefined,
            'standard'
            ,{
                version : false
            }
        );
    };

    /**
     * Sets up the list selector for the default moderation rings.
     *
     * @returns {void}
     */
    var setupDefaultModerationRings = function () {
        var jq_default_moderation_rings_container = jQuery('#moderation_rings_container');
        var base_url = BabblingBrook.Library.changeUrlAction(window.location.pathname, '');

        BabblingBrook.Client.Component.ListSelector(
            'user',
            'default_moderation_rings',
            jq_default_moderation_rings_container,
            base_url + 'GetDefaultModerationRings',
            base_url + 'ReplaceDefaultModerationRing',
            base_url + 'AddDefaultModerationRing',
            base_url + 'DeleteDefaultModerationRing',
            base_url + 'SwapDefaultModerationRing',
            undefined,
            undefined,
            {},
            'ring'
        );
    };

    return {

        construct : function () {
            setupUpdateDescription();
            setupUpdatePresentationType();
            setupChildStreams();
            setupDefaultSortRhythms();
            setupDefaultModerationRings();
        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageStream.Edit.construct();
});