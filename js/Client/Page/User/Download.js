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
 * @fileOverview Download user data page functionality.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.User !== 'object') {
    BabblingBrook.Client.Page.User = {};
}


/**
 * @namespace
 * @package JS_Client
 */
BabblingBrook.Client.Page.User.Download = (function () {
    'use strict';

    var onDownloadClicked = function () {
        BabblingBrook.Library.post(
            'downloadjson',
            {},
            function (response_data) {
                if (response_data.success === true) {
                    jQuery('#downloaded_data')
                        .text(response_data.user_data)
                        .removeClass('hide');

                } else {
                    BabblingBrook.Client.Component.Messages.addMessage({
                        type : 'error',
                        message : 'An error occured whilst trying to fetch your account details.',
                        buttons : [
                        {
                            name : 'Retry',
                            callback : onDownloadClicked
                        }
                        ]
                    });
                }
            }
        );
    };

    return {
        construct : function () {
            jQuery('#download_data').click(onDownloadClicked);
        }
    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.User.Download.construct();
});