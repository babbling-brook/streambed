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
 * @fileOverview Shows the status flag on the tree display when a bug report is the root post.
 * @author Sky Wickenden
 */

/**
 * Shows the status flag on the tree display when a bug report is the root post.
 *
 * Must be included immediately after the DisplayTree.js file
 */
BabblingBrook.Client.Component.BugTree = (function () {
    'use strict';

    /**
     * @type {boolean} Enusre this process is only run once.
     */
    var loaded = false;

    /**
     * Callback for when the bug status take data has been fetched.
     *
     * @param {object} jq_post jQuery object representing the post being displayed.
     * @param {object} post A standard post object for the post being displayed.
     * @param {object} stream A standard stream object that is the home of the post being displayed.
     * @param {object} take_data A standard take status object containg the take status of the
     *
     * @returns {void}
     */
    var onStatusFetched = function (jq_post, post, stream, take_data) {
        var test = BabblingBrook.Models.userPostTake(take_data);
        if (test === false) {
            onErrorFetchingStatus();
        }

        var value_list = stream.fields[8].value_list;
        var value_list_length = value_list.length;
        var status = 'Unconfirmed';
        for(var i = 0; i < value_list_length; i++) {
            if (parseInt(value_list[i].value) === parseInt(take_data.value)) {

                status = value_list[i].name;
            }
        }

        jQuery('>div.post-content-container>.field-7>.line-loading', jq_post)
            .removeClass('line-loading block-loading')
            .text(status);
    };

    /**
     * Handle an error whilst fetching the take data for status of a bug.
     *
     * @param {type} error_data
     *
     * @returns {void}
     */
    var onErrorFetchingStatus = function (error_data) {
        console.error("onErrorFetchingStatus error");
    };

    /**
     * Callback to be called when a bug report post is ready to be displayed.
     *
     * Used to attatch the current status.
     *
     * @param {object} jq_post jQuery object representing the post.
     * @param {object} post A standard post object for the post being displayed.
     * @param {object} stream A standard stream object that is the home of the post being displayed.
     *
     * @returns {void}
     */
    var onPostReadForStatusFlag = function (jq_post, post, stream) {
        if (stream.domain === BabblingBrook.Client.CustomConfig.bug_stream_domain
            && stream.username === BabblingBrook.Client.CustomConfig.bug_stream_username
            && stream.name === BabblingBrook.Client.CustomConfig.bug_stream_name
        ) {
            var jq_bug_status = jQuery('#bug_status_template').clone();
            var jq_line_loading = jQuery('#line_loading_template').clone();
            jq_line_loading.children().addClass('block-loading');
            jQuery('>div.post-content-container>.field-7', jq_post).replaceWith(jq_bug_status.children());
            jQuery('.field-7' ,jq_post).html(jq_line_loading.children());

            var user_post_take_url = post.domain + '/postwithtree/' + post.domain + '/' +
                    post.post_id + '/usertake/' +
                    BabblingBrook.Client.CustomConfig.bug_stream_domain +
                        '/' + BabblingBrook.Client.CustomConfig.bug_stream_username;
            var info_data = {
                url : user_post_take_url,
                data : {},
                https : false
            };
            BabblingBrook.Client.Core.Interact.postAMessage(
                info_data,
                'InfoRequest',
                onStatusFetched.bind(null, jq_post, post, stream),
                onErrorFetchingStatus
            );

        }
    };

    return {

        construct : function () {
            if (loaded === true) {
                return;
            }
            loaded = true;

            BabblingBrook.Client.Core.Loaded.onPostWithTreeLoaded(function () {
                    BabblingBrook.Client.Page.Post.PostWithTree.registerOnRootPostDisplayHook(onPostReadForStatusFlag);
            });
        }

    };
}());
