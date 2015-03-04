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
BabblingBrook.Client.Admin.SignupCodes = (function () {
    'use strict';

    var onSaveError = function () {
        jQuery('#error').removeClass('hide');
        jQuery('#create_signup_codes_form').removeClass('block-loading');
    };

    var onSaveSuccess = function (response) {
        if (typeof response.success === 'undefined') {
            jQuery.each(response, function(row_name, error){
                jQuery('#' + row_name + '_error')
                    .text(error)
                    .removeClass('hide');
            });
        } else {
            jQuery('#success').removeClass('hide');
        }
        jQuery('#create_signup_codes_form').removeClass('block-loading');
    };

    var onSaveClicked = function () {
        clearErrors();
        jQuery('#create_signup_codes_form').addClass('block-loading');
        BabblingBrook.Library.post(
            '/site/admin/signupcodes/createcodes',
            {
                primary_category : jQuery('#primary_category').val(),
                secondary_category : jQuery('#secondary_category').val(),
                qty : jQuery('#qty').val(),
            },
            onSaveSuccess,
            onSaveError,
            'save_signupcodes_error'
        );
    };

    var clearErrors = function () {
        jQuery('#error').addClass('hide');
        jQuery('#success').addClass('hide');
        jQuery('#primary_category_error').addClass('hide');
        jQuery('#secondary_category_error').addClass('hide');
        jQuery('#qty_error').addClass('hide');
    };

    var onClearClicked = function () {
        jQuery('#primary_category').val('');
        jQuery('#secondary_category').val('');
        jQuery('#qty').val('');
        clearErrors();
    };

    return {

        construct : function () {
            jQuery('#save').click(onSaveClicked);
            jQuery('#clear').click(onClearClicked);
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Admin.SignupCodes.construct();
});


