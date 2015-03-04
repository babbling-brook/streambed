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
BabblingBrook.Client.Admin.DeleteTestUsers = (function () {
    'use strict';

    var onTestUserSubmitted = function (response_data) {
        jQuery('#delete_one_test_user').removeClass('button-loading');
        if (response_data.success === false) {
            jQuery('#test_user_error')
                .text(response_data.error)
                .removeClass('hide');
            for (var i=0; i<response_data.stack.length; i++) {
                var stack_row = response_data.stack[i];
                jQuery('#test_user_error').append('<br/><br/>');
                jQuery.each(stack_row, function(j, value) {
                    jQuery('#test_user_error').append(j + ' : ' + value + '<br/>');
                });
            }
        } else {
            jQuery('#test_username').val('');
            jQuery('#test_user_success').removeClass('hide');
        }
    };

    var hideMessages = function () {
        jQuery('#no_test_user_error').addClass('hide');
        jQuery('#test_user_error').addClass('hide');
        jQuery('#test_user_success').addClass('hide');
        jQuery('#test_all_users_success').addClass('hide');
    };

    var onDeleteOneClcked = function () {
        jQuery('#delete_one_test_user').addClass('button-loading');
        hideMessages();
        var username = jQuery('#test_username').val();
        if (username.length < 1) {
            jQuery('#no_test_user_error').removeClass('hide');
        } else {
            BabblingBrook.Library.post(
                '/site/admin/deleteonetestuser',
                {
                    test_username : username
                },
                onTestUserSubmitted
            );
        }
    };

    var onDeleteAllSubmitted = function (response_data) {
        jQuery('#delete_all_test_users').removeClass('button-loading');
        if (response_data.success === false) {
            jQuery('#test_user_error')
                .text(response_data.error)
                .removeClass('hide');
            for (var i=0; i<response_data.stack.length; i++) {
                var stack_row = response_data.stack[i];
                jQuery('#test_user_error').append('<br/><br/>');
                jQuery.each(stack_row, function(j, value) {
                    jQuery('#test_user_error').append(j + ' : ' + value + '<br/>');
                });
            }
        } else {
            jQuery('#test_all_users_success').removeClass('hide');
        }
    };

    var onDeleteAllClicked = function () {
        jQuery('#delete_all_test_users').addClass('button-loading');
        hideMessages();
        BabblingBrook.Library.post(
            '/site/admin/deletealltestusers',
            {},
            onDeleteAllSubmitted
        );
    };

    return {

        construct : function () {
            jQuery('#delete_all_test_users').click(onDeleteAllClicked);
            jQuery('#delete_one_test_user').click(onDeleteOneClcked);
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Admin.DeleteTestUsers.construct();
});


