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
 * @fileOverview Handles the deletion of posts by the user that made them.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Domus !== 'object') {
    BabblingBrook.Domus = {};
}

/**
 * @namespace Handles the deletion of posts.
 *
 * Posts need to be deleted in both the post owners domus and the domain that the post was published on.
 * This is further complicated by the need for a cooldown period, during which posts can be truely deleted
 * and the ability of a domus domain to request a true delete rather than a 'hide'.
 *
 * If the domain of the stream that holds the post is different from the domus of the user that made the post then
 * the first step is to request a secret from the the domus of the user that owns the post.
 * This is then passed to the domain that houses the stream
 * and the this domain deletes the post depending on it's delete policy and cooldown status.
 * The delete status is then returned to here and the users domus is requested to delete the post.
 *
 * If the domain of the stream that holds the post is the same as the domus of the user that made the post then
 * it is passed directly to the users domus domain.
 *
 *
 * @param {object} delete_data The data required to delete an post.
 * @param {number} delete_data.post_id The local id of the post to delete.
 * @param {string} delete_data.post_domain The domain of stream that has the post to delete.
 * @param {string} sending_domain The domain that requested this action.
 * @param {function} successCallback Used to call the client with the requested data.
 * @param {function} errorCallback Used to call the client if there is an error.
 *
 * @package JS_Domus
 *
 * @return void
 * @test Deleting posts on another stream domain.
 */
BabblingBrook.Domus.DeletePost = function (delete_data, sending_domain, successCallback, errorCallback, timeout) {
    'use strict';

    /**
     * The status of a stream delete request. This is returned to the client after deletion.
     */
    var stream_status;

    /**
     * Callback for when an post has been deleted.
     *
     * @param {object} status_data The data returned from a user delete request.
     *
     * @return void
     */
    var onDeletedFromUserStore = function (status_data) {
        var test = BabblingBrook.Test.isA([[status_data.status, 'string']]);
        if (test === false) {
            errorCallback('DeletePost_failed_private');
            return;
        }
        if (typeof stream_status === 'undefined') {
            stream_status = status_data.status;
        }
        var deleted_data = {
            success : true,
            status : stream_status
        };
        successCallback(deleted_data);

        // The post also needs to be deleted from the post cache in the scientia http domain.
        // (Deletes are done via https for added security against spoofing.)
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            delete_data.post_domain,
            'DeletePostFromCache',
            {
                post_id : delete_data.post_id
            },
            false,
            function () {},
            function () { console.error('post failed to delete from scientia cache ' . delete_data.post_id)},
            timeout
        );

        // The post also needs removing from all cached sort results.
        BabblingBrook.Domus.SortedStreamResults.removePost(delete_data.post_domain, delete_data.post_id);
    };

    /**
     * Delete an post from a the domus of the user who made the post.
     *
     * @return void
     */
    var deleteFromUser = function () {
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            BabblingBrook.Domus.User.domain,
            'DeletePost',
            {
                post_id : delete_data.post_id
            },
            true,
            onDeletedFromUserStore,
            errorCallback.bind(null, 'DeletePost_user'),
            timeout
        );
    };

    /**
     * Callback for when a stream has deleted an post.
     *
     * @param {object} status_data The data returned from a stream delete request.
     *
     * @return void
     */
    var streamDeleteCallback = function (status_data) {
        var test = BabblingBrook.Test.isA([[status_data.status, 'string']]);
        if (test === false) {
            errorCallback('DeletePost_stream');
            return;
        }
        stream_status = status_data.status;
        deleteFromUser();
    };

    /**
     * Delete an post from a stream.
     *
     * @param {string} secret Used by the stream domain to verify that the owner is genuine.
     *
     * @return void
     */
    var deleteFromStream = function (secret) {
        BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
            delete_data.post_domain,
            'DeletePost',
            {
                post_id : delete_data.post_id,
                secret : secret
            },
            true,
            streamDeleteCallback,
            errorCallback.bind(null, 'DeletePost_stream'),
            timeout
        );
    };

    /**
     * Setup the delete request.
     */
    var setup = function () {
        if (BabblingBrook.Domus.User.domain !== delete_data.post_domain) {
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'GenerateSecret',
                {
                    username : BabblingBrook.Domus.User.username
                },
                true,
                deleteFromStream,
                errorCallback.bind(null, 'DeletePost_stream'),
                timeout
            );
        } else {
            deleteFromUser();
        }

    };
    setup();


};