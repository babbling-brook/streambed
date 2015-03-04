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

if (typeof BabblingBrook.Client.Admin !== 'object') {
    BabblingBrook.Client.Admin = {};
}

/**
 * Shows the bug report form.
 */
BabblingBrook.Client.Admin.ExportUser = (function () {
    'use strict';

    var hideMessages = function () {
        jQuery('#exported_user').addClass('hide');
    };


    var onGetUserDataSubmitted = function (response_data) {
        jQuery('#export_user').removeClass('button-loading');
        if (response_data.success === false) {
            jQuery('#server_error')
                .text(response_data.error)
                .removeClass('hide');
        } else {
            jQuery('#exported_user')
                .text(JSON.stringify(response_data.user_data))
                .removeClass('hide');
        }
    };

    var onExportUserClicked = function () {
        jQuery('#export_user').addClass('button-loading');
        hideMessages();
        BabblingBrook.Library.post(
            '/site/admin/getuserdata',
            {
                username : jQuery('#export_username').val()
            },
            onGetUserDataSubmitted
        );
    };

    return {

        construct : function () {
            jQuery('#export_user').click(onExportUserClicked);
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Admin.ExportUser.construct();
});


