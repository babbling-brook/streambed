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
 * @fileOverview The Scientia domain controller for receiving messages from domus domains.
 * @author Sky Wickenden
 */
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Scientia !== 'object') {
    BabblingBrook.Scientia = {};
}

/**
 * @namespace Receives and processes requests from the domus domain.
 *
 * All methods starting with 'action' are actions that are called by the interact class
 * They all have the same call signature.
 *      {object} data Some data sent with the request.
 *      {object} meta_data Meta data about the request
 *      {function} meta_data.onSuccess The function to call with the requested data.
 *          It accepts one paramater, a data object.
 *      {function} meta_data.onError The function to call if there is an error.
 *          It accepts two paramaters.
 *          The first is an error_code string as defined in saltNe.Models.errorTypes
 *          This is required.
 *          The second is an error data object, which can contain any relevant data.
 *      {string} request_domain The domain that sent this request.
 *      {number} timeout A millisecond timeout for when this request will timeout.
 *
 * @package JS_Scientia
 */
BabblingBrook.Scientia.Controller = (function () {
    'use strict';
    return {

        /**
         * Checks if this iframe is on an SSL connection.
         * @refactor move this to the library.
         */
        isHttps : function () {
            if (window.location.protocol !== 'https:') {

                console.error('This scientia iframe function must not be called unless in a https iframe.');
                return false;
            }
            return true;
        },

        /**
         * Checks that the scientia domain exists and is ready.
         *
         * Simply sends a return message back to the domus domain that requested it.
         *
         * @param {object} ready_data An empty object - not used.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionReadyRequest : function (ready_data, meta_data) {
            var return_data = {
                valid : true
            };
            meta_data.onSuccess(return_data);
        },

        /**
         * Fetches and returns suggested usernames for this domain.
         *
         * @param {object} username_data Object containing the partial username to check.
         * @param {string} username_data.partial_username The partial username
         *       to use when looking for username sugggestions.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionUsernameSuggestions : function(username_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [username_data.partial_username, 'string']
            ]);
            if (test === false) {
                meta_data.onError('scientia_test_username_suggestions');
                return;
            }

            BabblingBrook.Library.post(
                '/data/usernamesuggestions',
                {
                    partial_username : username_data.partial_username
                },
                /**
                 * Callback for passing the suggestions back to the client.
                 *
                 * @param {object} callback_data Container object.
                 * @param {string[]} callback_data.usernames The suggested usernames.
                 *
                 * @return void
                 */
                function (callback_data) {
                    var return_data = {
                        usernames : callback_data.usernames
                    };
                    meta_data.onSuccess(return_data);
                },
                meta_data.onError,
                'scientia_server_username_suggestions',
                meta_data.timeout
            );
        },

        /**
         * A request to validate a username.
         *
         * @param {object} username_data Object containing the username to check.
         * @param {string} username_data.username The username to validate.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionCheckUsernameValid : function(username_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [username_data.username, 'username']
            ]);
            if (test === false) {
                meta_data.onError('scientia_test_check_username_valid');
                return;
            }

            BabblingBrook.Library.get(
                '/' + username_data.username + '/valid',
                {
                    partial_username : username_data.partial_username
                },
                /**
                 * Callback for passing the username validity back to the client.
                 *
                 * @param {object} callback_data Container object.
                 * @param {string[]} callback_data.usernames The suggested usernames.
                 *
                 * @return void
                 */
                function (valid_data) {
                    meta_data.onSuccess(valid_data);
                },
                meta_data.onError,
                'scientia_server_check_username_valid',
                meta_data.timeout
            );
        },

        /**
         * Catch an error sent from the domus domain.
         *
         * @param {object} data
         * @param {string} data.type The type of error see BabblingBrook.ClientError.trigger for a list of possible errors.
         * @param {object} data.error_data Any data associated with the error.
         * @param {string} data.message A forwarded error.
         * @param {object} data.log_data Any data associated with the error.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionError : function (data, meta_data) {

            BabblingBrook.TestErrors.clearErrors();
            var error = 'BabblingBrookClient.actionError error.';
            var test = BabblingBrook.Test.isA([
                    [data.type, 'string'],
                    [data.error_data, 'object'],
                    [data.message, 'string|undefined'],
                    [data.log_data, 'object']
                ], error);
            if (test === false) {
                // Don't send error back to the domus domain,
                // it may cause an infinite recursion loop and hang the browser.
                console.error(data);
                console.error('An error sent from the domus domain did not validate.');
                throw 'Thread execution stopped.';
            }
        },

        /**
         * Generate a secret via https and return it to the domus domain.
         *
         * @param {object} request_data Contains the data needed to request the secret.
         * @param {string} request_data.username The username to generate the secret for.
         *      This is only needed to create the correct url, the actual user secret generated is based
         *      on the logged on user.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGenerateSecret : function (request_data, meta_data) {

            if (BabblingBrook.Scientia.Controller.isHttps === false) {
                meta_data.onError('https_not_on');
                return;
            }

            var test = BabblingBrook.Test.isA([
                [request_data.username, 'username']
            ]);
            if (test === false) {
                meta_data.onError('scientia_test_generate_secret');
                return;
            }

            BabblingBrook.Library.post(
                '/' + request_data.username + '/generatesecret',
                {},
                /**
                 * Call back for creating a new post.
                 * @param {object} data
                 * @param {secret} data.secret The generated secret.
                 */
                function (secret_data) {
                    meta_data.onSuccess(secret_data);
                },
                meta_data.onError,
                'scientia_server_generate_secret',
                meta_data.timeout
            );

        },

        /**
         * Make an post.
         *
         * This is part of the make post action that recieves the post from the domus domain and passes it to the
         * server for saving. The post is passed to multiple domains when created and so this will be called mulitple
         * times with slightly differnt data. The secrets define whether this is being saved for a stream,
         * user or parent post.
         *
         * @param {object} post_data The data object sent from the domus domain.
         * @param {object} post_data.post The post object. See BabblingBrook.models.makePost for details.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         * @refactor When validation fails in all actions, it would be better if the guid was used to send back an error
         *      rather than rely on the timeout to bring up an error.
         */
        actionMakePost : function (post_data, meta_data) {
            if (BabblingBrook.Scientia.Controller.isHttps === false) {
                meta_data.onError('https_not_on');
                return;
            }
            var valid = BabblingBrook.Test.isA([[post_data, 'object']], 'actionMakePost object.');
            if (valid === false) {
                meta_data.onError('scientia_test_make_post');
                return;
            }
            if (BabblingBrook.Models.makePost(post_data.post, 'scientia actionMakePost.', ['submitting_user']) === false) {
                meta_data.onError('scientia_test_make_post');
                return;
            }

            // @refactor we have the stream url details, should use them. Only problem is that this domain
            // may not have the stream data. In which case it will need to not error, and fetch it.
            BabblingBrook.Library.post(
                '/data/makepost',
                post_data.post,
                /**
                 * Call back for creating a new post.
                 * @param {object} callback_data The data object passed back from the request.
                 * @param {object} callback_data.post See BabblingBrook.Models.posts with tree child and extensions.
                 * @param {String|Boolean} data.error Error message or false.
                 */
                function (post_data) {
                    var post = post_data.post;
                    BabblingBrook.Scientia.Cache.cacheItem('post_content', post.post_id, 'memory', post);
                    BabblingBrook.Scientia.FetchPosts.appendNewPostToStream(post);
                    meta_data.onSuccess(post_data);
                },
                meta_data.onError,
                'scientia_server_make_post',
                meta_data.timeout
            );
        },

        /**
         * Deletes an post from the cache.
         *
         * This is primarily called when an post has been deleted via https and needs removing from the
         * cache here in the http domain.
         *
         * @param {object} post_data The data object sent from the domus domain.
         * @param {object} post_data.post_id The post id that should be removed from the cache.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return {void}
         */
        actionDeletePostFromCache : function (post_data, meta_data) {
            BabblingBrook.Scientia.Cache.removeItem('post', post_data.post_id, 'all');
            meta_data.onSuccess({});
        },

        /**
         * Delete an post.
         *
         * There are several versions of post deletion - all are called through this action.
         * 1. Delete an post from a stream. To do this, populate delete_data.secret.
         * 3. Deletion in the domus domain of the user that made the post. Will also delete in the stream if it is the
         * same domus domain. To do this ensure delete_data.secret is undefined.
         *
         * A status is returned from the server :
         *  'full' The post content was completely deleted and the reference to the user id removed.
         *  'hide' The stream has been changed to 'private'.
         *
         * @param {object} delete_data The data object sent from the domus domain.
         * @param {object} delete_data.post_id The post id that is local to the stream.
         * @param {string} delete_data.secret A secret used to verify the owner of the post.
         *      Use an empty string if this is the users domus domain.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionDeletePost : function (delete_data, meta_data) {

            BabblingBrook.TestErrors.clearErrors();
            var error = 'BabblingBrookClient.actionError error.';
            var test = BabblingBrook.Test.isA(
                [
                    [delete_data.post_id, 'string'],
                    [delete_data.secret, 'string|undefined']
                ],
                error
            );
            if (test === false) {
                meta_data.onError('scientia_test_delete_post');
                return;
            }

            BabblingBrook.Library.post(
                '/data/deletepost',
                delete_data,
                /**
                 * Callback for deleting a new post.
                 *
                 * @param {object} callback_data The data object passed back from the request.
                 * @param {boolean} callback_data.success Was the request successful.
                 *
                 * @return void
                 */
                function (callback_data) {
                    BabblingBrook.Scientia.Cache.removeItem('post', delete_data.post_id, 'all');
                    meta_data.onSuccess(callback_data);
                },
                meta_data.onError,
                'scientia_server_delete_post',
                meta_data.timeout
            );
        },

        /**
         * Receives a request for a take in the name of a ring.
         *
         * @param {object} data
         * @param {string} data.post_domain
         * @param {string} data.post_id
         * @param {string} data.ring_name
         * @param {string} data.ring_password
         * @param {string} data.take_name
         * @param {string} data.user_domain
         * @param {string} data.username
         * @param {string} data.untake
         * @param {object} meta_data See Module definition for more details.
         */
        actionRingTake : function (data, meta_data) {

            if (BabblingBrook.Scientia.Controller.isHttps === false) {
                meta_data.onError('https_not_on');
                return;
            }

            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.post_domain, 'domain'],
                [data.post_id, 'string'],
                [data.ring_name, 'string'],
                [data.ring_password, 'string'],
                [data.username, 'username'],
                [data.take_name, 'string'],
                [data.user_domain, 'domain'],
                [data.untake, 'boolean']
            ], window.location.host + ' scientia.actionRingTake data does not validate.');
            if (test === false) {
                meta_data.onError('scientia_test_ring_take');
                return;
            }

            var url = '/' + data.ring_name + '/ring/take';
            BabblingBrook.Library.post(
                url,
                {
                    post_domain :  data.post_domain,
                    post_id :  data.post_id,
                    ring_password :  data.ring_password,
                    take_name :  data.take_name,
                    user_domain :  data.user_domain,
                    username :  data.username,
                    untake :  data.untake
                },
                /**
                 * Callback for a ring take server request.
                 *
                 * @param {object} data
                 * @param {boolean} data.success
                 *
                 * @return void
                 */
                function (callback_data) {

                    if (callback_data.success === false) {
                        meta_data.onError('scientia_server_ring_take');
                        return;
                    }

                    var domain = window.location.host;
                    if (domain.substr(0, 8) === 'scientia') {
                        domain = domain.substr(9);
                    }
                    var ring_data = {
                        post_domain :  data.post_domain,
                        post_id :  data.post_id,
                        ring_domain : domain,
                        ring_name : data.ring_name,
                        take_name : data.take_name,
                        status : !data.untake
                    };
                    meta_data.onSuccess(ring_data);
                },
                meta_data.onError,
                'scientia_server_ring_take',
                meta_data.timeout
            );

        },

        /**
         * Fetch a users take status for a ring on a particular take.
         *
         * @param {object} message Data sent from the domus domain.
         * @param {string} data.post_domain The domain of the post_id for the ring we are getting a take status for.
         * @param {number} data.post_id
         * @param {string} data.ring_name
         * @param {string} data.username
         * @param {string} data.user_domain
         * @param {string} data.ring_password
         * @param {object} meta_data See Module definition for more details.
         */
        actionGetRingTakeStatus : function (data, meta_data) {

            if (BabblingBrook.Scientia.Controller.isHttps === false) {
                meta_data.onError('https_not_on');
                return;
            }

            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.post_domain, 'domain'],
                [data.post_id, 'string'],
                [data.field_id, 'uint'],
                [data.ring_name, 'string'],
                [data.username, 'string'],
                [data.user_domain, 'domain'],
                [data.ring_password, 'string']
            ],  window.location.host + 'scientia.actionRingTake data does not validate.');
            if (test === false) {
                meta_data.onError('scientia_test_get_ring_take_status');
                return;
            }

            BabblingBrook.Library.post(
                '/' + data.ring_name + '/ring/takestatus',
                {
                    post_domain :  data.post_domain,
                    post_id :  data.post_id,
                    field_id : data.field_id,
                    user_domain :  data.user_domain,
                    username :  data.username,
                    ring_password : data.ring_password
                },
                /**
                 * Callback for a takestatus server request.
                 *
                 * @param {object} data
                 *
                 * @return void
                 */
                function (callback_data) {
                    delete callback_data.errors;
                    callback_data.post_domain = data.post_domain;
                    callback_data.post_id = data.post_id;
                    callback_data.ring_name = data.ring_name;
                    var domain = window.location.host;
                    if (domain.substr(0, 8) === 'scientia') {
                        domain = domain.substr(9);
                    }
                    callback_data.ring_domain = domain;
                    meta_data.onSuccess(callback_data);
                },
                meta_data.onError,
                'scientia_server_get_ring_take_status',
                meta_data.timeout
            );
        },

        /**
         * Fetch the block numbers for a stream between two timestamps.
         *
         * @param {object} data
         * @param {object} data.from_timestamp The timestamp to fetch block numbers from.
         * @param {object} data.to_timestamp The timestamp to fetch block numbers to.
         * @param {object} data.stream A standard stream name object for the stream to fetch block numbers for.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchStreamBlockNumbers : function (data, meta_data) {
            var test = BabblingBrook.Models.streamName(data.stream, true);
            var test2 = BabblingBrook.Test.isA([
                [data.from_timestamp, 'uint'],
                [data.to_timestamp, 'uint'],
            ]);
            if (test === false || test2 === false) {
                meta_data.onError('scientia_fetch_stream_block_numbers_test');
                return;
            }
            var stream_url = BabblingBrook.Library.makeStreamUrl(data.stream, 'getstreamblocknumbers', false);

            BabblingBrook.Library.get(
                stream_url,
                {
                    to_timestamp :  data.to_timestamp,
                    from_timestamp :  data.from_timestamp
                },
                /**
                 * Server response for request a streams block numbers.
                 *
                 * @param {object} response_data
                 *
                 * @return void
                 */
                function (response_data) {
                    if (response_data.success === false) {
                        meta_data.onError('scientia_fetch_stream_block_numbers_server');
                        return;
                    }
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_fetch_stream_block_numbers_ajax',
                meta_data.timeout
            );
        },

        /**
         * Fetch all takes for a stream block number.
         *
         * @param {object} data
         * @param {object} data.stream A standard stream name object for the stream to fetch block numbers for.
         * @param {object} data.block_number
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchStreamTakes : function (data, meta_data) {
            var test = BabblingBrook.Models.streamName(data.stream, true);
            var test2 = BabblingBrook.Test.isA([
                [data.block_number, 'uint'],
                [data.field_id, 'uint']
            ]);
            if (test === false || test2 === false) {
                meta_data.onError('scientia_fetch_stream_takes_test');
                return;
            }
            var stream_url = BabblingBrook.Library.makeStreamUrl(data.stream, 'getstreamtakes', false);

            BabblingBrook.Library.get(
                stream_url,
                {
                    block_number :  data.block_number,
                    field_id :  data.field_id
                },
                /**
                 * Server response for request a streams block numbers.
                 *
                 * @param {object} response_data
                 *
                 * @return void
                 */
                function (response_data) {
                    if (response_data.success === false) {
                        meta_data.onError('scientia_fetch_stream_takes_test_server');
                        return;
                    }
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_fetch_stream_takes_test_ajax',
                meta_data.timeout
            );
        },

        /**
         * Fetch as many posts as required for a stream or tree request and return them to the users domus domain.
         *
         * @param {object} data
         * @param {object} data.sort_request See BabblingBrook.Models.sortRequest for a full definition.
         *                                   (with scientia and possibly tree_base extensions).
         * @param {boolean} data.with_content Should the posts be fetched with full content rows.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetPosts : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test1 = BabblingBrook.Test.isA([
                [data.sort_request, 'object']
            ], 'sort request isA object');
            var test2 = BabblingBrook.Test.isA([
                [data.sort_request.type, 'string']
            ], 'sort request type isA string');
            var extensions = ['scientia'];
            if (data.sort_request.type === 'tree') {
                extensions.push('tree_base');
            }

            var sort_request = BabblingBrook.Models.sortRequest(data.sort_request, 'sortRequest', extensions);
            if (!test1 || !test2 || !sort_request) {
                meta_data.onError('scientia_test_get_posts');
                return;
            }
            if (typeof data.with_content !== 'boolean') {
                meta_data.onError('GetPosts_scientia_with_content');
                return;
            }
            var test3 = BabblingBrook.Test.isA([
                [data.search_phrase, 'string|undefined'],
                [data.search_title, 'boolean|undefined'],
                [data.search_other_fields, 'boolean|undefined']
            ]);
            if (test3 === false) {
                meta_data.onError('GetPosts_scientia_search_phrase');
                return;
            }

            // For normal request use caching.
            if (typeof data.search_phrase === 'undefined') {
                BabblingBrook.Scientia.FetchPosts.get(
                    sort_request,
                    data.with_content,
                    meta_data.onSuccess,
                    meta_data.onError,
                    meta_data.timeout
                );

            // Otherwise just fetch directly.
            } else {
                var url = BabblingBrook.Library.makeStreamUrl(sort_request.stream, 'getpostssearch');
                BabblingBrook.Library.post(
                    url,
                    {
                        from_timestamp : sort_request.posts_from_timestamp,
                        to_timestamp : sort_request.posts_to_timestamp,
                        post_id : sort_request.post_id,
                        type : sort_request.type,
                        search_phrase : data.search_phrase,
                        search_title : data.search_title,
                        search_other_fields : data.search_other_fields
                    },
                    /**
                     * Callback for the getpostsblock data request.
                     *
                     * @param {object} response_data Data returned from the server.
                     *
                     * @returns {void}
                     */
                    function (response_data) {
                        if (response_data.success === false) {
                            meta_data.onError('scientia_get_posts_search_response');
                        } else {
                            meta_data.onSuccess(response_data);
                        }

                    },
                    meta_data.onError.bind(null, 'scientia_get_posts_search_ajax'),
                    sort_request.timeout
                );
            }
        },

        /**
         * Fetch the details for an post.
         *
         * @param {object} data
         * @param {number} data.post_id The local post id.
         * @param {number} data.revision The revision number to request. Defaults to the latest version.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetPost : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.post_id, 'string'],
                [data.revision, 'uint|undefined']
            ]);
            if (!test) {
                meta_data.onError('GetPost_test');
                return;
            }

            BabblingBrook.Scientia.FetchPost.get(
                data.post_id,
                meta_data.onSuccess,
                meta_data.onError,
                data.revision,
                meta_data.timeout
            );
        },

        /**
         * Get a block of user takes.
         *
         * @param {object} data See BabblingBrook.Models.streamUserTakeRequest for details.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchUserTakesBlockNumber : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var request = BabblingBrook.Models.streamUserTakeRequest(data, '', ['time']);
            if (request === false) {
                meta_data.onError('scientia_test_get_user_takes_block_number');
                return;
            }
            BabblingBrook.FetchUserTakes.getBlockNumber(
                request,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Get a block of user takes.
         *
         * @param {object} data See BabblingBrook.Models.streamUserTakeRequest for details
         *      (with the block_number extension).
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchUserTakesBlock : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var request = BabblingBrook.Models.streamUserTakeRequest(data, '', ['block_number']);
            if (request === false) {
                meta_data.onError('scientia_test_get_user_takes_block');
                return;
            }
            BabblingBrook.FetchUserTakes.getBlock(request, meta_data.onSuccess, meta_data.onError, meta_data.timeout);
        },

        /**
         * A method for fetching miscellaneous data from any domus domain.
         *
         * @param {object} data
         * @param {string} data.url The url to fetch data from.
         * @param {object} data.data The data to send with the ajax post request.
         * @param {number} [data.instance] The unique id that identifies this request to the domus domain.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchData : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.url, 'url'],
                [data.data, 'object'],
                [data.instance, 'uint|undefined']
            ]);
            if (!test) {
                meta_data.onError('scientia_test_fetch_data');
                return;
            }

            // All scientia requests have the client domain attached from the domus domain data to prevent spoofing.
            data.data.client_domain = data.client_domain;

            var url = BabblingBrook.Library.removeProtocol(data.url);
            if (url.substr(0, 5) !== 'scientia.') {
                url = 'scientia.' + url;
            }
            url = window.location.protocol + '//' + url;
            BabblingBrook.Library.get(
                url,
                data.data,
                /**
                 * Callback for requesting miscellaneous data.
                 *
                 * @param {object} response_data The returned data.
                 *
                 * @return void
                 */
                function (response_data) {
                    if(typeof response_data.error !== 'undefined') {
                        meta_data.onError(response_data.error);
                    } else {
                        meta_data.onSuccess(response_data);
                    }
                },
                meta_data.onError,
                'scientia_server_fetch_data',
                meta_data.timeout
            );
        },

        /**
         * A method for fetching streams
         *
         * @param {object} data
         * @param {object} data.url The url of the Rhythm to fetch.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return {void}
         */
        actionFetchStream : function (data, meta_data) {
            var test = BabblingBrook.Models.streamName(data.stream);
            if (test === false) {
                meta_data.onError('scientia_test_get_stream');
                return;
            }
            var stream_url = BabblingBrook.Library.makeStreamUrl(
                {
                    domain : data.stream.domain,
                    username : data.stream.username,
                    name : data.stream.name,
                    version : data.stream.version
                },
                'json',
                false
            );

            var cached_stream_object = BabblingBrook.Scientia.Cache.getItem('stream', stream_url);
            if (cached_stream_object !== false) {
                var cached_stream = cached_stream_object.data;
                meta_data.onSuccess(cached_stream);
                return;
            }
            BabblingBrook.Library.get(
                stream_url,
                {},
                /**
                 * Callback for requesting a stream object.
                 *
                 * @param {object} stream_data The returned information about the Stream.
                 *                      See BabblingBrook.Models.stream for a full definition.
                 *
                 * @return void
                 */
                function (stream_data) {
                    BabblingBrook.Scientia.Cache.cacheItem('stream', stream_url, 'localstorage', stream_data);
                    meta_data.onSuccess(stream_data);
                },
                 meta_data.onError,
                'scientia_server_get_stream',
                meta_data.timeout
            );

        },

        /**
         * Fetches exact versions of a stream to represent the 'latest' or 'all' version provided.
         *
         * @param {object} data
         * @param {object} data.stream A stream name object representing the stream to fetch exact versions for.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return {void}
         */
        actionFetchExactStreamVersions :  function (data, meta_data) {
            var test = BabblingBrook.Models.streamName(data.stream, true);
            if (test === false) {
                meta_data.onError('scientia_test_fetch_exact_stream_version');
                return;
            }
            var stream_url = BabblingBrook.Library.makeStreamUrl(data.stream, 'getexactversions', false);
            BabblingBrook.Library.get(
                stream_url,
                {},
                /**
                 * Response for requesting an exact version of a stream name.
                 *
                 * @param {object} response_data The returned from the server.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {obbject} response_data.streams A standard stream name object.
                 *
                 * @return void
                 */
                function (response_data) {
                    if (response_data.success === false) {
                        meta_data.onError('scientia_ajaxerror_fetch_exact_stream_version');
                        return;
                    }
                    meta_data.onSuccess(response_data);
                },
                 meta_data.onError,
                'scientia_ajax_fetch_exact_stream_version',
                meta_data.timeout
            );
        },

        /**
         * A method for fetching Rhythms.
         *
         * @param {object} data
         * @param {object} data.url The url of the Rhythm to fetch.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchRhythm : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Models.protocolUrl(data.url, ['json', 'minijson'], 'rhythm', '');
            var url = BabblingBrook.Library.extractPath(data.url);
            if (test === false || url === false) {
                meta_data.onError('scientia_test_get_rhythm');
                return;
            }
console.debug(url);
            // Use a cached rhythm if a full version is requested. Otherwise only use the
            // cached rhythm if it is recent.
            var cached_rhythm_object = BabblingBrook.Scientia.Cache.getItem('rhythm', url);
            if (cached_rhythm_object !== false) {
                var version = BabblingBrook.Library.extractVersionParts(data.url);
                if (version[0] === 'latest' || version[1] === 'latest' || version[2] === 'latest') {
                    var one_day = Math.round(new Date().getTime() / 1000) - (24 * 60 * 60);
                    if (cached_rhythm_object.timestamp < one_day) {
                        meta_data.onSuccess(cached_rhythm_object.data);
                        return;
                    }
                } else {
                    meta_data.onSuccess(cached_rhythm_object.data);
                    return;
                }
            }

            BabblingBrook.Library.get(
                url,
                {},
                /**
                 * Callback for requesting miscellaneous urk data.
                 *
                 * @param {object} rhythm The returned information about the Rhythm.
                 *                      See BabblingBrook.Models.rhythm for a full definition.
                 *
                 * @return void
                 */
                function (callback_data) {
                    var requested_data = {
                        rhythm : callback_data,
                        url : data.url
                    };
                    BabblingBrook.Scientia.Cache.cacheItem('rhythm', url, 'localstorage', requested_data);
                    meta_data.onSuccess(requested_data);
                },
                meta_data.onError,
                'scientia_server_get_rhythm',
                meta_data.timeout
            );
        },

        /**
         * Store the results from a ring Rhythm.
         *
         * @param {object} data
         * @param {object} data.rhythm The Rhythm object. See BabblingBrook.Models.rhythm for a full definition.
         * @param {object} data.results The results (Exact format is defined by the ring).
         * @param {object} data.user The user that ran the Rhythm.
         * @param {string} data.user.username
         * @param {string} data.user.domain
         * @param {string} data.ring_domain
         * @param {string} data.ring_username
         * @param {string} data.rhythm_type
         * @param {string} data.ring_password
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionStoreRingResults : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test1 = BabblingBrook.Test.isA([
                [data.rhythm_domain, 'domain'],
                [data.rhythm_username, 'username'],
                [data.rhythm_name, 'resource-name'],
                [data.rhythm_version, 'version'],
                [data.computed_data, 'string'],
                [data.ring_member_domain, 'domain'],
                [data.ring_username, 'username'],
                [data.rhythm_type, 'string'],
                [data.ring_password, 'string']
            ]);

            var url = data.ring_domain + '/' + data.ring_username + '/ring/storerhythmdata';
            BabblingBrook.Library.post(
                url,
                {
                    ring_member_username : data.ring_member_username,
                    ring_member_domain : data.ring_member_domain,
                    ring_password : data.ring_password,
                    rhythm_domain : data.rhythm_domain,
                    rhythm_username : data.rhythm_username,
                    rhythm_name : data.rhythm_name,
                    rhythm_version : data.rhythm_version,
                    rhythm_type : data.rhythm_type,
                    computed_data : data.computed_data
                },
                /**
                 * On success send back to the sotre domain.
                 *
                 * @param {object} callback_data Callback object.
                 * @param {string} [callback_data.success] If present, then the request was successful.
                 * @param {string} [callback_data.error] If present, then there was an error.
                 *
                 * @return void
                 */
                function (callback_data) {
                    if (typeof callback_data.success !== 'undefined') {
                        meta_data.onSuccess();
                    } else {
                        meta_data.onError('scientia_store_ring_results_server_error');
                    }
                },
                meta_data.onError,
                'scientia_test_store_ring_results',
                meta_data.timeout
            );

        },
        /**
         * Stores the sort results for a stream for the owner of that stream
         *
         * These are used to display results to loggedout users.
         *
         * @param {object} data
         * @param {object} data.posts See BabblingBrook.Models.posts for a full definition.
         * @param {object} data.stream A standard stream name object.
         * @param {string} data.parent_post_id The id of the top parent post if this is a tree sort.
         *      See BabblingBrook.Models.streamName for a full definition.
         * @param {object} data.filter_rhythm A standard rhythm name obejct.
         *      See BabblingBrook.Models.rhythmName for a full definition.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionStoreOwnersStreamSortResults : function (data, meta_data) {
            // not testing the data as it has come from the domus domain of the same site as this one.
            var stream_url = BabblingBrook.Library.makeStreamUrl(data.stream, 'storeownersstreamsortresults', false);

            BabblingBrook.Library.post(
                stream_url,
                {
                    posts : data.posts,
                    top_parent_id : data.top_parent_id,
                    filter_rhythm : data.filter_rhythm
                },
                function (callback_data) {
                    if (typeof callback_data.success !== 'undefined') {
                        meta_data.onSuccess({});
                    } else {
                        meta_data.onError('StoreOwnersStreamSortResults_server');
                    }
                },
                meta_data.onError,
                'StoreOwnersStreamSortResults_post_failed',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request to fetch suggestions for an open list field in a stream.
         *
         * @param {object} open_list_data The data used for the request.
         * @param {object} open_list_data stream The stream to fetch the suggestions from.
         *      See BabblingBrook.Models.streamName for a full deffinition.
         * @param {number} field_id The id of the field in the stream that suggestions are to be fetched for.
         * @param {string} text_to_fetch_suggestions_for  The text to use in searching for suggestions.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionOpenListSuggestionsFetch : function (open_list_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [open_list_data.stream, 'object'],
                [open_list_data.field_id, 'uint'],
                [open_list_data.text_to_fetch_suggestions_for, 'string']
            ]);
            if (test !== true) {
                meta_data.onError('domus_fetch_open_list_suggestions_data_error');
                return;
            }
            test = BabblingBrook.Models.streamName(open_list_data.stream);
            if (test === false) {
                meta_data.onError('domus_fetch_open_list_suggestions_stream_error');
                return;
            }

            var stream_url = BabblingBrook.Library.makeStreamUrl(open_list_data.stream, 'openlistsuggestionsfetch')
            BabblingBrook.Library.get(
                'http://scientia.' + stream_url,
                {
                    field_id : open_list_data.field_id,
                    text_to_fetch_suggestions_for : open_list_data.text_to_fetch_suggestions_for
                },
                /**
                 * Callback for requesting suggestions
                 *
                 * @param {array} suggestions An array of suggestions.
                 *
                 * @return void
                 */
                function (suggestions_object) {
                    var suggestions = [];
                    for(var i = 0; i < suggestions_object.suggestions.length; i++) {
                        suggestions.push(suggestions_object.suggestions[i].item);
                    }

                    var suggestion_data = {
                        suggestions : suggestions
                    };
                    meta_data.onSuccess(suggestion_data);
                },
                meta_data.onError,
                'scientia_server_get_rhythm',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the domus to search for streams.
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionStreamSearch : function (request_data, meta_data) {
            var test = BabblingBrook.Models.searchStreamRequest(request_data);
            if (test === false) {
                meta_data.onError('scientia_search_stream_data_error');
                return;
            }

            BabblingBrook.Library.get(
                '/data/streamsearch',
                request_data,
                /**
                 * Callback for requesting suggestions
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message.
                 * @param {array} [response_data.streams] An array of stream data.
                 *
                 * @return void
                 */
                function (response_data) {
                    if (response_data.success === true) {
                        meta_data.onSuccess(response_data.streams);
                    } else {
                        meta_data.onError('server_search_stream_data_error');
                    }
                },
                meta_data.onError,
                'server_search_stream_uncaught_error',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the domus to search for rhythms.
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionRhythmSearch : function (request_data, meta_data) {
            var test = BabblingBrook.Models.searchRhythmRequest(request_data);
            if (test === false) {
                meta_data.onError('scientia_search_rhythm_data_error');
                return;
            }

            BabblingBrook.Library.get(
                '/data/rhythmsearch',
                request_data,
                /**
                 * Callback for requesting suggestions
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message.
                 * @param {array} [response_data.streams] An array of rhythm data.
                 *
                 * @return void
                 */
                function (response_data) {
                    if (response_data.success === true) {
                        meta_data.onSuccess(response_data.rhythms);
                    } else {
                        meta_data.onError('server_search_rhythm_data_error');
                    }
                },
                meta_data.onError,
                'server_search_rhythm_uncaught_error',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the domus to search for rhythms.
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionUserSearch : function (request_data, meta_data) {
            var test = BabblingBrook.Models.searchUserRequest(request_data);
            if (test === false) {
                meta_data.onError('scientia_user_search_data_error');
                return;
            }

            BabblingBrook.Library.get(
                '/data/usersearch',
                request_data,
                /**
                 * Callback for requesting suggestions
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message.
                 * @param {array} [response_data.streams] An array of rhythm data.
                 *
                 * @return void
                 */
                function (response_data) {
                    if (response_data.success === true) {
                        meta_data.onSuccess(response_data.users);
                    } else {
                        meta_data.onError('server_user_search_data_error');
                    }
                },
                meta_data.onError,
                'server_user_search_uncaught_error',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the domus to store some client user data
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionStoreClientUserData : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.username, 'username'],
                [request_data.client_domain, 'domain'],
                [request_data.key,  'string'],
                [request_data.data, 'object|string']
            ]);
            if (test === false) {
                meta_data.onError('scientia_store_client_user_data_error_test');
                return;
            }
            var url = '/' + request_data.username + '/storeuserclientdata';
            delete request_data.username;

//            // data is converted to a string so that the type information can be preserved.
//            if (typeof request_data.data === 'string') {
//                request_data.data = BabblingBrook.Library.parseJSON(request_data.data);
//                if (request_data.data === false) {
//                    meta_data.onError('scientia_store_client_user_data_error_parsejson');
//                    return;
//                }
//            }

            // request_data.type_array = BabblingBrook.Library.makeNestedTypeArray(request_data.data);
            request_data.data = JSON.stringify(request_data.data);

            BabblingBrook.Library.post(
                url,
                request_data,
                /**
                 * Callback for storing some user data for a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message.
                 *
                 * @return void
                 */
                function (response_data) {
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_store_client_user_data_error_ajax',
                meta_data.timeout
            );
        },


        /**
         * Recieves a request from the domus to store some client user data
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchClientUserData : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.username, 'username'],
                [request_data.client_domain, 'domain'],
                [request_data.key,  'string']
            ]);
            if (test === false) {
                meta_data.onError('scientia_fetch_client_user_data_test');
                return;
            }
            // Old php url.
            var url = '/' + request_data.username + '/getuserclientdata';

            // New node url.
           //var url = 'scientia/' + request_data.username + '/client/sitedata';

            delete request_data.username;

            BabblingBrook.Library.post(
                url,
                request_data,
                /**
                 * Callback for fetching some user data for a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message.
                 *
                 * @return void
                 */
                function (response_data) {
                    if (response_data.success === true) {
                        var json_data = {};
                        var data = response_data.data;
                        for(var i=0; i < data.length; i++) {
                            if (data[i].data !== null) {
                                var key_parts = data[i].depth_key.split(".");
                                switch (data[i].data_type) {
                                    case 'integer':
                                        data[i].data = parseInt(data[i].data);
                                        break;

                                    case 'boolean':
                                        data[i].data = (data[i].data === 'true');
                                        break;
                                }
                                BabblingBrook.Library.getNestedObjectByName(json_data, key_parts, data[i].data, true);
                            }
                        }
                        response_data.data = json_data;
                    }
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_fetch_client_user_data_error_ajax',
                meta_data.timeout
            );
        },


        /**
         * Recieves a request to subscribe a stream to the users account on a client website.
         *
         * @param {type} request_data
         * @param {type} request_data.stream A standard stream name object identifying the stream to subscribe.
         * @param {type} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionSubscribeStream : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream,           'resource-object'],
                [request_data.client_domain,   'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_subscribe_stream_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/subscribestream',
                request_data,
                /**
                 * Callback for subscribing a stream to a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {boolean} [response_data.subscription] A subscription object.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                    if (response_data.subscription === 'object') {
                        var converted_filters = BabblingBrook.Scientia.DataConversion.convertStreamFilterSubscriptions(
                            response_data.subscription.filters
                        );
                        response_data.subscription.filters = converted_filters;
                    }
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_subscribe_stream_ajax',
                meta_data.timeout
            );

        },

        /**
         * Recieves a request to unsubscribe a stream from the users account on a client website.
         *
         * @param {type} request_data
         * @param {string} request_data.subscription_id The id of the subscription to unsubscribe.
         * @param {type} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionUnsubscribeStream : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.subscription_id, 'string'],
                [request_data.client_domain,   'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_unsubscribe_stream_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/unsubscribestream',
                request_data,
                /**
                 * Callback for subscribing a stream to a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_unsubscribe_stream_ajax',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request to subscribe a filter to a users stream subscription for
         * the users account on a client website.
         *
         * @param {type} request_data
         * @param {string} request_data.subscription_id The id of the subscription to subscribe a filter to.
         * @param {type} request_data.rhythm A standard rhythm name object identifying the filter to subscribe.
         * @param {type} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionSubscribeStreamFilter : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,  'string'],
                [request_data.rhythm,  'resource-object'],
                [request_data.client_domain,   'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_subscribe_stream_filter_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/subscribestreamfilter',
                request_data,
                /**
                 * Callback for adding a filter to a stream subscription for a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_subscribe_stream_filter_ajax',
                meta_data.timeout
            );

        },

        /**
         * Recieves a request from to unsubscribe a filter from a users stream subscription for
         * the users account on a client website.
         *
         * @param {type} request_data
         * @param {string} request_data.subscription_id The id of the subscription to unsubscribe a filter from.
         * @param {string} request_data.filter_subscription_id The id of the filter to unsubscribe.
         * @param {string} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionUnsubscribeStreamFilter : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,  'string'],
                [request_data.filter_subscription_id,  'string'],
                [request_data.client_domain,           'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_unsubscribe_stream_filter_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/unsubscribestreamfilter',
                request_data,
                /**
                 * Callback for removing a filter for a stream subscription for a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_unsubscribe_stream_filter_ajax',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request to subscribe a ring to a users stream subscription for
         * the users account on a client website.
         *
         * @param {type} request_data
         * @param {string} request_data.stream_subscription_id The id of the stream subscription to subscribe a ring to.
         * @param {type} request_data.ring A standard user object identifying the ring to subscribe.
         * @param {type} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionSubscribeStreamRing : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,  'string'],
                [request_data.ring,             'user'],
                [request_data.client_domain,   'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_subscribe_stream_ring_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/subscribestreamring',
                request_data,
                /**
                 * Callback for removing a filter for a stream subscription for a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {boolean} response_data.subscription Data object containing the subscription details.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_subscribe_stream_ring_ajax',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request to unsubscribe a ring from a users stream subscription for
         * the users account on a client website.
         *
         * @param {type} request_data
         * @param {string} request_data.stream_subscription_id
         *      The id of the stream subscription to unsubscribe a ring from.
         * @param {string} request_data.ring_subscription_id The id of the ring subscription to unsubscribe.
         * @param {type} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionUnsubscribeStreamRing : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,   'string'],
                [request_data.ring_subscription_id,     'string'],
                [request_data.client_domain,           'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_unsubscribe_stream_ring_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/unsubscribestreamring',
                request_data,
                /**
                 * Callback for removing a filter for a stream subscription for a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_unsubscribe_stream_ring_ajax',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from to change the version of a stream subscription for a user on a client website
         *
         * @param {type} request_data
         * @param {string} request_data.stream_subscription_id
         *      The id of the stream subscription to change the version for.
         * @param {string} request_data.new_version A version object representing the new version.
         * @param {type} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionChangeStreamSubscriptionVersion : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,   'string'],
                [request_data.new_version,              'version-object'],
                [request_data.client_domain,           'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_change_stream_subscription_version_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/changestreamversion',
                request_data,
                /**
                 * Callback for removing a filter for a stream subscription for a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_change_stream_subscription_version_ajax',
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from to change the version of a stream subscription for a user on a client website
         *
         * @param {type} request_data
         * @param {string} request_data.stream_subscription_id
         *      The id of the stream subscription that owns the filter to change the version for.
         * @param {string} request_data.filter_subscription_id The id of the filter to change a version for.
         * @param {string} request_data.new_version A version object representing the new version.
         * @param {type} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionChangeStreamFilterSubscriptionVersion : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,   'string'],
                [request_data.filter_subscription_id,   'string'],
                [request_data.new_version,              'version-object'],
                [request_data.client_domain,           'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_change_stream_filter_subscription_version_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/changefilterversion',
                request_data,
                /**
                 * Callback for removing a filter for a stream subscription for a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_change_stream_filter_subscription_version_ajax',
                meta_data.timeout
            );
        },

        /**
         * Fetches a users stream subscriptions for a client website.
         *
         * @param {type} request_data
         * @param {type} request_data.client_domain The client website that made the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchStreamSubscriptions : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.client_domain, 'domain']
            ]);
            if (test === false) {
                meta_data.onError('scientia_fetch_stream_subscriptions_test');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Scientia.User.username + '/streamsubscription/getsubscriptions',
                request_data,
                /**
                 * Callback for fetching a users stream subscriptions on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {object} response_data.subscriptions The users subscriptions.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                    // Need to ensure that all ring and filter containers are objects and not arrays due
                    // to a query of empty php arrays being converted to json arrays rather than objects.
                    if (response_data.success === true) {
                        if (BabblingBrook.Library.isArray(response_data.subscriptions) === true) {
                            if (response_data.subscriptions.length === 0) {
                                response_data.subscriptions = {};
                            }
                        }
                        jQuery.each(response_data.subscriptions, function (i, subscription) {
                            if (BabblingBrook.Library.isArray(subscription.filters) === true) {
                                if (subscription.filters.length === 0) {
                                    response_data.subscriptions[i].filters = {};
                                }
                            }
                            if (BabblingBrook.Library.isArray(subscription.rings) === true) {
                                if (subscription.rings.length === 0) {
                                    response_data.subscriptions[i].rings = {};
                                }
                            }
                        });
                    }
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_fetch_stream_subscriptions_ajax',
                meta_data.timeout
            );
        },

        /**
         * Fetches a list of all the versions of a rhythm.
         *
         * @param {type} request_data A standard rhythm name object
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchRhythmVersions : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data, 'resource-object']
            ]);
            if (test === false) {
                meta_data.onError('scientia_fetch_rhythm_versions_test');
                return;
            }
            var url = BabblingBrook.Library.makeRhythmUrl(request_data, 'getversions', false);

            BabblingBrook.Library.post(
                url,
                {},
                /**
                 * Callback for removing a filter for a stream subscription for a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {object} response_data.versions An array of versions of this rhythm.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_fetch_rhythm_versions_ajax',
                meta_data.timeout
            );
        },

        /**
         * Fetches a list of all the versions of a stream.
         *
         * @param {type} request_data A standard stream name object
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchStreamVersions : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data, 'resource-object']
            ]);
            if (test === false) {
                meta_data.onError('scientia_fetch_stream_versions_test');
                return;
            }
            var url = BabblingBrook.Library.makeStreamUrl(request_data, 'getversions', false);

            BabblingBrook.Library.post(
                url,
                {},
                /**
                 * Callback for removing a filter for a stream subscription for a users account on a client website.
                 *
                 * @param {object} response_data The data returned from the request.
                 * @param {boolean} response_data.success Was the request successful.
                 * @param {object} response_data.versions An array of versions of this stream.
                 * @param {string} [response_data.error] An error message. Only if there is an error.
                 *
                 * @return void
                 */
                function (response_data) {
                     meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_fetch_stream_versions_ajax',
                meta_data.timeout
            );
        },

        /**
         * Process a take request and register for processing by relationship rhythm.
         * @param {number} request_data.post_id The local post_id of for the post that is being taken.
         * @param {number} request_data.field_id The Id of the field in the post that is being taken.
         * @param {string} request_data.stream_domain The domain of the stream that that
         *                                        holds the post that is being taken.
         * @param {string} request_data.stream_username The user of the stream that holds the post that is being taken.
         * @param {string} request_data.stream_name The name of the stream that holds the post that is being taken.
         * @param {string} request_data.stream_version The version of the stream that
         *                                         holds the post that is being taken.
         * @param {string} request_data.value_type The type of value that is being taken. See BabblingBrook.Models.value_type.
         * @param {number} request_data.value The amount that is being taken.
         * @param {string} request_data.mode The mode of the take. See BabblingBrook.Models.takeMode.
         * @param {object} meta_data See Module definition for more details.
         */
        actionTake : function (request_data, meta_data) {

            BabblingBrook.TestErrors.clearErrors();
            var test1 = BabblingBrook.Test.isA([
                [request_data.post_id, 'string'],
                [request_data.field_id, 'uint'],
                [request_data.stream_domain, 'domain'],
                [request_data.stream_username, 'username'],
                [request_data.stream_name, 'resource-name'],
                [request_data.stream_version, 'version'],
                [request_data.value, 'int']
            ]);

            var test2 = BabblingBrook.Models.valueType(request_data.value_type, '');
            var test3 = BabblingBrook.Models.takeMode(request_data.mode, '');
            if (test1 === false || test2 === false || test3 === false) {
                meta_data.onError('scientia_take_test');
                return;
            }

            // Send a take submission via the users domain. The server will forward it to the stream domain.
            // This is so that the stream domain can be sure that the take was requested by this user.
            var stream_url = BabblingBrook.Library.makeStreamUrl(
                {
                    domain : request_data.stream_domain,
                    username : request_data.stream_username,
                    name : request_data.stream_name,
                    version : request_data.stream_version
                },
                'take',
                false
            );
            stream_url += '/' + request_data.post_id;
            BabblingBrook.Library.post(
                stream_url,
                {
                    value : request_data.value,
                    field_id : request_data.field_id,
                    mode : request_data.mode
                },
                /**
                 * Callback for a take action.
                 * @param {number} value The current value of the take.
                 * @param {boolean} status Was the take successful.
                 */
                function (callback_data) {
                    var taken_data  = {
                        status : callback_data.status,
                        post_id : request_data.post_id,
                        domain : request_data.stream_domain,
                        field_id : request_data.field_id,
                        value : callback_data.value,
                        value_type : request_data.value_type
                    };
                    meta_data.onSuccess(taken_data);
                },
                meta_data.onError,
                'scientia_take_ajax',
                meta_data.timeout
            );
        },

        /**
         * Joins a user to a ring.
         *
         * @param {type} request_data A standard stream name object.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionRingJoin : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.domain, 'domain'],
                [request_data.username, 'username'],
                [request_data.user, 'user'],
            ]);
            if (test === false) {
                meta_data.onError('scientia_join_ring_test');
                return;
            }

            var url = '/' + request_data.username + '/ring/join';
            BabblingBrook.Library.post(
                url,
                request_data,
                function (response_data) {
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_join_ring_ajax',
                meta_data.timeout
            );
        },

        /**
         * Enables a ring admin to accept membership of a user who has requested membership of a ring.
         *
         * @param {object} request_data The request object.
         * @param {string} request_data.ring_username The username of the ring that membership is being requested for.
         *      (Domain is the this scientia domaain).
         * @param {object} request_data.user A user object for the user who is being granted membership.
         * @param {string} request_data.ring_passsword The admins password that the ring.
         * @param {object} request_data.admin_user A user object for the admin that is accepting the request.
         *
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionAcceptRingMembershipRequest : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.admin_password, 'string'],
                [request_data.ring_username, 'username'],
                [request_data.user, 'user'],
                [request_data.admin_user, 'user'],
            ]);
            if (test === false) {
                meta_data.onError('scientia_accept_ring_membership_request_test');
                return;
            }

            var url = '/' + request_data.ring_username + '/ring/acceptringmembership';
            BabblingBrook.Library.post(
                url,
                {
                    admin_passsword : request_data.admin_password,
                    user : request_data.user,
                    admin_user : request_data.admin_user
                },
                function (response_data) {
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_accept_ring_membership_request_ajax',
                meta_data.timeout
            );
        },

        /**
         * Enables a ring admin to decline membership of a user who has requested membership of a ring.
         *
         * @param {object} request_data The request object.
         * @param {string} request_data.ring_username The username of the ring that membership is being declined for.
         *      (Domain is the this scientia domaain).
         * @param {object} request_data.user A user object for the user who is being declined membership.
         * @param {string} request_data.ring_passsword The admins password that the ring.
         * @param {object} request_data.admin_user A user object for the admin that is declining the request.
         *
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionDeclineRingMembershipRequest : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.admin_password, 'string'],
                [request_data.ring_username, 'username'],
                [request_data.user, 'user'],
                [request_data.admin_user, 'user'],
            ]);
            if (test === false) {
                meta_data.onError('scientia_decline_ring_membership_request_test');
                return;
            }

            var url = '/' + request_data.ring_username + '/ring/declineringmembership';
            BabblingBrook.Library.post(
                url,
                {
                    admin_passsword : request_data.admin_password,
                    user : request_data.user,
                    admin_user : request_data.admin_user
                },
                function (response_data) {
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_decline_ring_membership_request_ajax',
                meta_data.timeout
            );
        },

        /**
         * Bans a member of a ring.
         *
         * @param {object} request_data The request object.
         * @param {string} request_data.ring_username The username of the ring that a member is being banned from.
         *      (Domain is this scientia domaain).
         * @param {object} request_data.user A user object for the user who is being banned.
         * @param {string} request_data.ring_passsword The admins password that the ring.
         * @param {object} request_data.admin_user A user object for the admin that is banning a user.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionBanRingMember : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.admin_password, 'string'],
                [request_data.ring_username, 'username'],
                [request_data.user, 'user'],
                [request_data.admin_user, 'user'],
            ]);
            if (test === false) {
                meta_data.onError('scientia_ban_ring_member_test');
                return;
            }

            var url = '/' + request_data.ring_username + '/ring/banmember';
            BabblingBrook.Library.post(
                url,
                {
                    admin_passsword : request_data.admin_password,
                    user : request_data.user,
                    admin_user : request_data.admin_user
                },
                function (response_data) {
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_ban_ring_member_ajax',
                meta_data.timeout
            );
        },
        /**
         * Reinstates a member of a ring.
         *
         * @param {object} request_data The request object.
         * @param {string} request_data.ring_username The username of the ring that a member is being reinstated to.
         *      (Domain is this scientia domaain).
         * @param {object} request_data.user A user object for the user who is being reinstated.
         * @param {string} request_data.ring_passsword The admins password that the ring.
         * @param {object} request_data.admin_user A user object for the admin that is banning a user.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionReinstateRingMember : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.admin_password, 'string'],
                [request_data.ring_username, 'username'],
                [request_data.user, 'user'],
                [request_data.admin_user, 'user'],
            ]);
            if (test === false) {
                meta_data.onError('scientia_reinstate_ring_member_test');
                return;
            }

            var url = '/' + request_data.ring_username + '/ring/reinstatemember';
            BabblingBrook.Library.post(
                url,
                {
                    admin_passsword : request_data.admin_password,
                    user : request_data.user,
                    admin_user : request_data.admin_user
                },
                function (response_data) {
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_reinstate_ring_member_ajax',
                meta_data.timeout
            );
        },

        /**
         * Fetches the number of users waiting to be vetted for membership in a ring.
         *
         * @param {object} request_data See Domus.Filter.fetchStreamAndTreePosts for details.
         * @param {string} request_data.ring_username The username of the ring to fetch qty of users waiting to be vetted.
         * @param {object} request_data.admin_user A user object for the admin user that is making the request.
         * @param {string} request_data.admin_password The password of the admin user that is making the request.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchRingUsersWaitingToBeVetted : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data.ring_username, 'username'],
                [request_data.admin_user, 'user'],
                [request_data.admin_password, 'string']
            ]);
            if (test1 === false) {
                meta_data.onError('scientia_fetch_ring_users_waiting_to_be_vetted_test');
                return;
            }

            var url = '/' + request_data.ring_username + '/ring/getringuserswaitingtobevetted';
            BabblingBrook.Library.post(
                url,
                request_data,
                function (response_data) {
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_fetch_ring_users_waiting_to_be_vetted_ajax',
                meta_data.timeout
            );
        },

        /**
         * Fetches the number of users waiting to be vetted for membership in a ring.
         *
         * @param {object} request_data See Domus.Filter.fetchStreamAndTreePosts for details.
         * @param {string} request_data.ring_username The username of the ring to request membership from.
         * @param {object} request_data.admin_user The user object of the user who is requesting membership.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionRequestRingMembership : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data.ring_username, 'username'],
                [request_data.membership_request_user, 'user']
            ]);
            if (test1 === false) {
                meta_data.onError('scientia_request_ring_membership_test');
                return;
            }

            var url = '/' + request_data.ring_username + '/ring/requestringmembership';
            BabblingBrook.Library.post(
                url,
                request_data,
                function (response_data) {
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                'scientia_request_ring_membership_ajax',
                meta_data.timeout
            );
        }
    };

}());