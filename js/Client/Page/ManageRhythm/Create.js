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
BabblingBrook.Client.Page.ManageRhythm.Create = (function () {
    'use strict';

    var onCreateClicked = function () {
        jQuery('.error').addClass('hide');
        BabblingBrook.Library.post(
            '/' + BabblingBrook.Client.User.username + '/rhythms/make',
            {
                name : jQuery('#rhythm_name').val(),
                description : jQuery('#rhythm_description').val(),
                category : jQuery('#rhythm_category').val(),
                javascript : BabblingBrook.Client.Component.CodeMirror.getValue()
            },
            function (response_data) {
                if (response_data.success !== true) {
                    jQuery.each(response_data.errors, function (error_name, error) {
                        jQuery('#rhythm_' + error_name + '_error').text(error).removeClass('hide');
                    });
                } else {
                    var new_url = '/' + BabblingBrook.Client.User.username + '/rhythm/' +
                        jQuery('#rhythm_name').val() + '/0/0/0/update';
                    BabblingBrook.Client.Core.Ajaxurl.redirect(new_url);
                }
            }
        );
    };

    return {

        construct : function () {
            jQuery('#create_rhythm').click(onCreateClicked);
            BabblingBrook.Client.Component.CodeMirror.create();
        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageRhythm.Create.construct();
});