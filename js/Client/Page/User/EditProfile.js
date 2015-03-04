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
 * @fileOverview Profile page functionality.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.User !== 'object') {
    BabblingBrook.Client.Page.User = {};
}


/**
 * @namespace Javascript for the edit profile page.
 * @package JS_Client
 */
BabblingBrook.Client.Page.User.EditProfile = (function () {
    'use strict';

    var profile_username;

    /**
     * @type object An associative array of form fields and their values at the last point they gained focus.
     */
    var fields = {};

    var setupPofileImageUpload = function() {

        BabblingBrook.Library.wait(
            /**
            * Timeout condition. Passed when the QqFileuploader image upload class has loaded.
            */
            function () {
                /*global QqFileuploader: true */
                return (typeof QqFileuploader !== 'undefined');
            },
            /**
            * Run this when the timeout condition passes.
            */
            function () {
                var jq_uploader_template = jQuery('#edit_profile_uploader_template');
                var uploader = new QqFileuploader.FileUploader(
                    {
                        // pass the dom node (ex. $(selector)[0] for jQuery users)
                        element : jQuery('#new_profile_image')[0],
                        // path to server-side upload script
                        action : '/' + profile_username + '/newprofileimage',
                        template : jq_uploader_template.html(),
                        onSubmit : function(id, fileName){
                            jQuery('#new_profile_image .QqFileuploader-upload-list').html('');
                            jQuery('#new_profile_image').addClass('block-loading');
                        },
                        //onProgress: function(id, fileName, loaded, total){},
                        onComplete: function(id, fileName, responseJSON){
                            jQuery('#new_profile_image').removeClass('block-loading');
                            var username = profile_username.replace(/ /g, '-');
                            var domain = window.location.hostname;
                            var rand = '?' + new Date().getTime();
                            var profile_image_src = '/images/user/' + domain + '/' + username +
                                '/profile/small/profile.jpg' + rand;
                            jQuery('#profile_image').attr('src',profile_image_src);
                            // Create a copy of the large profile image so that it preloads and deletes any
                            // cached version.
                            var large_image = new Image;
                            large_image.src = '/images/user/' + domain + '/' + username +
                                '/profile/large/profile.jpg' + rand;

                        },
                        //onCancel: function(id, fileName){}
                    }
                );
            },
            function () {},
            {
                message : 'Wait function has timmed out. qq image upload'
            }
        );

    };

    var setupEditFields = function() {
        jQuery('.edit-profile-field').bind('blur', function(){

            var jq_this = jQuery(this);

            // Check if any changes have been made before proceeding.
            var old_value = fields[jq_this.attr('id')];
            var current_value = jq_this.val();
            if(old_value === current_value) {
                BabblingBrook.Library.fieldSuccess(jq_this);
                return;
            }

            jq_this.addClass('textbox-loading');

            BabblingBrook.Library.fieldWorking(jq_this);

            var edit_profile_fields_url = '/' + profile_username + '/editprofilefields';
            BabblingBrook.Library.post(
                edit_profile_fields_url,
                {
                    field : jq_this.attr('data-field_name'),
                    value : jq_this.val()
                },
                /**
                 * Callback for editing profile fields.
                 *
                 * @param {object} return_data The return data.
                 * @param {boolean} return_data.success Was the process successful.
                 * @param {object} return_data.errors A list of errors to display, indexed by field.
                 *
                 * @return void
                 */
                function(return_data){
                    jq_this.removeClass('textbox-loading');

                    if(typeof return_data.success !== 'boolean') {
                        var error_message = 'Data returned from ' + edit_profile_fields_url + ' is invalid.';
                        console.error(error_message);
                        return;
                    }

                    // Show any errors
                    var row_error = jq_this.siblings('.error');
                    row_error.addClass('hide');
                    if(typeof return_data.error === 'string') {
                        row_error.html(return_data.error).removeClass('hide');
                        BabblingBrook.Library.fieldError(jq_this);
                        return;
                    }
                    BabblingBrook.Library.fieldSuccess(jq_this);
                    if (jq_this.attr('data-field_name') === 'about') {
                        BabblingBrook.Client.Page.User.EditProfile.onDescriptionEditedHook();
                    }
                }
            );
        });
        jQuery('.edit-profile-field').bind('focus', function(){
            var jq_this = jQuery(this);
            BabblingBrook.Library.fieldEditing(jq_this);
            fields[jq_this.attr('id')] = jq_this.val();
        });
    };

    return {

        construct : function () {
            profile_username = jQuery('#username_for_profile').val();
            setupPofileImageUpload();
            setupEditFields();
            BabblingBrook.Client.Core.Loaded.setEditProfileLoaded();
        },

        /**
         * An overridable hook that is called when the description is edited.
         *
         * @returns {void}
         */
        onDescriptionEditedHook : function () {}

    };
}());


jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.User.EditProfile.construct();
});