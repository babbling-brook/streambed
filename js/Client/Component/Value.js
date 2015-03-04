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
 * @fileOverview Parent class for displaying value fields.
 * @author Sky Wickenden
 */

/**
 * @namespace Parent class for displaying value fields.
 * Contains shared functionality for the value fields.
 *
 * @return void
 */
BabblingBrook.Client.Component.Value = (function () {
    'use strict';

    /**
     * Called once the domus domain has returned the takes, or imediatly if they are already available.
     *
     * @param {string} post The post object that contains this field.
     * @param {number} field_id The id of the field in the post that the status is being set for.
     * @param {string} [status] A valid take_status string to assign. If undefined then it is not updated.
     * @param {function} callback A callback function for once the status is fetched - it may need fetching
     *       from the server.
     * @param {object} [take_data] Details of the takes fetched from the server.
     *
     * @return void
     */
    var setAndGetStatusCallback = function(post, field_id, status, callback, take_data) {
        if (typeof take_data !== 'undefined') {
            post.takes = take_data;
        }

        if (typeof status !== 'undefined') {
            BabblingBrook.Library.createNestedObjects(post, ['takes', field_id]);
            post.takes[field_id].status = status;
        } else {

            if (BabblingBrook.Library.doesNestedObjectExist(post, ['takes', field_id, 'status']) === false) {
                BabblingBrook.Library.createNestedObjects(post, ['takes', field_id]);
                if (BabblingBrook.Library.doesNestedObjectExist(post, ['takes', field_id, 'value']) === false) {
                    post.takes[field_id].value = 0;
                }
                if (parseInt(post.takes[field_id].value, 10) === 0) {
                    post.takes[field_id].status = 'untaken';
                } else {
                    post.takes[field_id].status = 'taken';
                }
            }
            status = post.takes[field_id].status;
        }
        callback(status);
    };

    /**
     * Checks if the logged in user has taken any of the values in this post.
     *
     * @param {object} post The post object to check for takes.
     * @param {function} callback The function to call with the status.
     *      Accepts one boolean paramater.
     * @param {object} [take_data] Details of the takes fetched from the server.
     *
     * @return void
     */
    var areAnyTakenCallback = function (post, callback, take_data) {
        // Only overwrite if not already present - otherwise may conflict with a new take.
        if (typeof post.takes !== 'object') {
            post.takes = take_data;
        }

        var taken = false;
        jQuery.each(post.takes, function(i, take) {
            if (parseInt(take, 10) !== 0) {
                taken = true;
            }
        });

        callback(taken);
    };

    return {

        /**
         * Sets the current take status class for the passed in field.
         *
         * Sets a defualt status if one is not passed in.
         * If no status is passed in and there is a status already set then the current status is retruned.
         *
         * @param {string} post The post object that contains this field.
         * @param {number} field_id The id of the field in the post that the status is being set for.
         * @param {string} [status] A valid take_status string to assign. If undefined then it is not updated.
         * @param {function} callback A callback function for once the status is fetched - it may need fetching
         *       from the server.
         */
        setAndGetStatus : function (post, field_id, status, callback) {
            // Ensure that the take value has loaded - if not then fetch it.
            if (typeof post.takes !== 'object') {
                BabblingBrook.Client.Core.Interact.postAMessage(
                    {
                        post_id : post.post_id,
                        post_domain : post.domain,
                        post_creation_timestamp : post.timestamp
                    },
                    'GetTakesForPost',
                    setAndGetStatusCallback.bind(null, post, field_id, status, callback)
                );
            } else {
                setAndGetStatusCallback(post, field_id, status, callback);
            }
        },

        /**
         * Checks if the logged in user has taken any of the values in this post.
         *
         * @param {object} post The post object to check for takes.
         * @param {function} The function to call with the status.
         *      Accepts one boolean paramater.
         *
         * @return void
         */
        areAnyTaken : function (post, callback) {
            // Ensure that the take value has loaded - if not then fetch it.
            if (typeof post.takes !== 'object') {
                BabblingBrook.Client.Core.Interact.postAMessage(
                    {
                        post_id : post.post_id,
                        post_domain : post.domain,
                        post_creation_timestamp : post.timestamp
                    },
                    'GetTakesForPost',
                    areAnyTakenCallback.bind(null, post, callback)
                );
            } else {
                areAnyTakenCallback(post, callback);
            }
        },

        /**
         * Getch the take value for a field in an post.
         *
         * Uses a getter rather than direct becuase if the value is zero then it might not be defined.
         * @refactor WARNING May run into problems if setAndGetStatus is not called before this.
         *
         * @param {object} post The standard post object.
         * @param {field_id} field_id The id of the field that a take is being requested for.
         *
         * @return number The value of the take.
         */
        getTakeValue : function (post, field_id) {
            var take_value = 0;
            if (BabblingBrook.Library.doesNestedObjectExist(post, ['takes', field_id]) === true) {
                take_value = post.takes[field_id].value;
            }
            if (take_value > 0) {
                take_value = '+' + take_value;
            }

            return take_value;
        },

        construct : function () {
            BabblingBrook.Client.Component.ValueSetup.Arrows();
            BabblingBrook.Client.Component.ValueSetup.Button();
            BabblingBrook.Client.Component.ValueSetup.List();
            BabblingBrook.Client.Component.ValueSetup.Slider();
            BabblingBrook.Client.Component.ValueSetup.Stars();
            BabblingBrook.Client.Component.ValueSetup.Textbox();
        }
    };
}());
