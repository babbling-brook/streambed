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
 * @fileOverview Javascript used on the Stream creation page.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.ManageStream !== 'object') {
    BabblingBrook.Client.Page.ManageStream = {};
}


/**
 * @namespace Creation of streams.
 * @package JS_Client
 */
BabblingBrook.Client.Page.ManageStream.Create = (function () {
    'use strict';

    /**
     * Callback for when the create button is pressed.
     *
     * @param {object} response_data The data returned from the server.
     * @param {boolean} response_data.success Was the request to make a stream successful.
     * @param {object} [response_data.errors] An array of error messages, indexed by name.
     * @param {object} name The name of the new stream that was submitted to the server.
     *
     * @returns {void}
     */
    var onMakeStreamSuccess = function(name, response_data) {
        if (typeof response_data.success !== 'boolean') {
            onMakeStreamError();
        }
        if (response_data.success === true) {
            BabblingBrook.Client.Page.ManageStream.Create.onStreamCreatedHook();
            redirectToUpdatePage(name);
        } else {
            displayErrors(response_data.errors);
        }
    };

    /**
     * Show the relevent errors on the form.
     *
     * @param {object} An array of errors indexed by name. Each index contains another array of strings.
     *
     * @return void
     */
    var displayErrors = function(errors) {
        jQuery('#create_stream').removeClass('button-loading');
        for (var key in errors) {
            var errors_length = errors[key].length;
            var error_string = '';
            for(var i = 0; i < errors_length; i++) {
                error_string += errors[key][i] + '<br/>';
            }
            jQuery('#stream_' + key + '_error')
                .removeClass('hide')
                .html(error_string);
        }
    }

    /**
     * Redirect the browser to the update page for a stream that has just been created.
     *
     * @param {string} name The name of the stream that has just been created.
     *
     * @return {void}
     */
    var redirectToUpdatePage = function(name) {
        name = encodeURIComponent(name);
        var url = '/' + BabblingBrook.Client.User.username + '/stream/' + name + '/0/0/0/update/';
        BabblingBrook.Client.Core.Ajaxurl.redirect(url);
    };

    /**
     * Callback for when the create button is pressed.
     *
     * @returns {void}
     */
    var onMakeStreamError = function() {
        jQuery('#create_stream').removeClass('button-loading');
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : 'An unknown error occured whilst trying to save a stream.',
        });
    };

    /**
     * Callback for when the create button is pressed.
     *
     * @returns {void}
     */
    var onCreateStream = function() {
        jQuery('#create_stream').addClass('button-loading');
        jQuery('#stream_form .error')
            .html('')
            .addClass('hide');

        var name = jQuery('#stream_name').val();
        var description = jQuery('#stream_description').val();
        var kind = jQuery('#kind>option:selected').text();

        BabblingBrook.Library.post(
            '/' + BabblingBrook.Client.User.username + '/streams/make',
            {
                name : name,
                description : description,
                kind : kind
            },
            onMakeStreamSuccess.bind(null, name),
            onMakeStreamError,
            'make_stream_error'
        );
    };

    return {

        construct : function () {
            jQuery('#create_stream').click(onCreateStream);

            BabblingBrook.Client.Core.Loaded.setCreateStreamLoaded();
        },

        /**
         * A hook that is called after a stream is created.
         *
         * Used by tutorial.js.
         *
         * @returns {undefined}
         */
        onStreamCreatedHook : function () { }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageStream.Create.construct();
});