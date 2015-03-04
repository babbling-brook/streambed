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
 * @fileOverview Manages the fetching of users take data.
 * @author Sky Wickenden
 */
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Scientia !== 'object') {
    BabblingBrook.Scientia = {};
}

/**
 * @namespace Manages the fetching of users take data.
 * @package JS_Scientia
 * @refactor This could probably be moved back into the controller.
 *      It's not doing anything that other basic controller actions are doing.
 */
BabblingBrook.FetchUserTakes = (function () {
    'use strict';
    return {

        /**
         * Get the block number that matches the request.
         *
         * @param {object} request see BabblingBrook.Models.userTakes for details.
         * @param {string} successCallback Called with the success data. See Controller definition for more details.
         * @param {string} errorCallback Called if there is an error. See Controller definition for more details.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @return void
         */
        getBlockNumber : function (request, successCallback, errorCallback, timeout) {

            var test = BabblingBrook.Models.protocolUrl(
                request.stream_url,
                ['json', 'minijson'],
                'stream',
                'BabblingBrook.FetchUserTakes.getBlockNumber url error.'
            );
            if (test === false) {
                errorCallback('scientia_test_get_user_takes_block_number');
                return;
            }

            var url = BabblingBrook.Library.changeUrlAction(request.stream_url, 'GetUserTakeBlockNumber');
            url = window.location.protocol + '//scientia.' + url;
            BabblingBrook.Library.get(
                url,
                {
                    username : request.username,
                    post_id : request.post_id,
                    time : request.time,
                    type : request.type
                },
                /**
                 * Callback for block number requests.
                 * @param {string} ring_data. The following paramaters are part of this string until parsed.
                 * @param {object} ring_data.takes
                 */
                function (data) {
                    var domain = window.location.host;
                    domain = domain.substr(9);

                    var requested_data = {
                        stream_url : request.stream_url,
                        username : request.username,
                        domain : domain,
                        post_id : request.post_id,
                        time : request.time,
                        type : request.type,
                        block_number : data.block_number
                    };
                    successCallback(requested_data);
                },
                errorCallback,
                'scientia_server_get_user_takes_block_number',
                timeout
            );
        },

        /**
         * Get the block of takes that matches the request.
         *
         * @param {object} request see BabblingBrook.Models.userTakes for details.
         * @param {string} successCallback Called with the success data. See Controller definition for more details.
         * @param {string} errorCallback Called if there is an error. See Controller definition for more details.
         * @param {number} timeout Timestamp in milliseconds for when this request will timeout.
         *
         * @return void
         */
        getBlock : function (request, successCallback, errorCallback, timeout) {

            var test = BabblingBrook.Models.protocolUrl(
                request.stream_url,
                ['json', 'minijson'],
                'stream',
                'BabblingBrook.FetchUserTakes.getBlock url error.'
            );
            if (test === false) {
                errorCallback('scientia_test_get_user_takes_block');
                return;
            }

            var url = BabblingBrook.Library.changeUrlAction(request.stream_url, 'getusertakes');
            url = window.location.protocol + '//scientia.' + url;

            BabblingBrook.Library.get(
                url,
                {
                    username : request.username,
                    block_number : request.block_number,
                    post_id : request.post_id,
                    type : request.type,
                    field_id : request.field_id
                },
                /**
                 * Callback for user take requests.
                 * @param {string} ring_data. The following paramaters are part of this string until parsed.
                 * @param {object} ring_data.takes
                 */
                function (data) {

                    var domain = window.location.host;
                    domain = domain.substr(9);

                    var requested_data = {
                        stream_url : request.stream_url,
                        domain : domain,
                        username : request.username,
                        block_number : request.block_number,
                        post_domain : request.post_domain,
                        post_id : request.post_id,
                        type : request.type,
                        takes : data.takes,
                        last_full_block : data.last_full_block
                    };
                    successCallback(requested_data);
                },
                errorCallback,
                'scientia_server_get_user_takes_block',
                timeout
            );
        }

    };

}());
