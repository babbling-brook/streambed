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
 * @fileOverview The controller to recieve messages from client domains.
 * @author Sky Wickenden
 * @suggetion a memoizing pattern can be used in many places in this code.
 */
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Domus !== 'object') {
    BabblingBrook.Domus = {};
}
/**
 * Global object holding methods related to the domus domain.
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
 * @package BabblingBrookDomusJS
 * @class BabblingBrook.Domus.Controller
 */
BabblingBrook.Domus.Controller = (function () {
    'use strict';
    return {
        /**
         * @type {boolean} Stores the host name of the parent window. This is passed in from the parent at load time.
         */
        client_domain : null,

        /**
         * @type {boolean} Is the main client window a https connection.
         */
        client_https : null,

        /**
         * If there are no more takes waiting on the server then this is set to false and the longer wait period between
         * requests for more data is used.
         * @type Boolean
         */
        takes_waiting_on_server : true,

        /**
         * A request to fetch the users kindred data.
         *
         * @param {string} data An empty object.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetKindred : function (data, meta_data) {
            BabblingBrook.Domus.Kindred.sendToClient(meta_data.onSuccess, meta_data.onError);
        },

        /**
         * A request to fetch the users kindred tag data.
         *
         * @param {string} data An empty object.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetKindredTags : function (data, meta_data) {
            BabblingBrook.Domus.Kindred.sendTagsToClient(meta_data.onSuccess, meta_data.onError);
        },

//        /**
//         * Receives an error from the client domain
//         * @param {object} data
//         * @param {object} meta_data See Module definition for more details.
//         */
//        actionError : function (data, meta_data) {
//            BabblingBrook.TestErrors.clearErrors();
//            var test = BabblingBrook.Test.isA([
//                    [data.type, 'string'],
//                    [data.message, 'string|undefined'],
//                    [data.data, 'object']
//                ]);
//            if (test === false) {
//                meta_data.onError('domus_client_error_test');
//                return;
//            }
//
//            meta_data.onSuccess({});
//
//        },

        /**
         * Add a sort request to the queue.
         * @param {object} data
         * @param {object} data.sort_request. See BabblingBrook.Models.sortRequest for definition.
         *      (base with possible tree extension).
         * @param {object} meta_data See Module definition for more details.
         */
        actionSortRequest : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            // Add the origin to the sort request. Not passed through with the object to avoid spoofing.
            data.sort_request.domain = meta_data.request_domain;

            var test1 = BabblingBrook.Test.isA([
                [data, 'object'],
                [data.sort_request, 'object']
            ], 'Is sort request an object.');
            var test2 = BabblingBrook.Test.isA(
                [
                    [data.sort_request.type, 'string']
                ],
                'sort request type test.'
            );
            var extensions = [];
            if (data.sort_request.type === 'tree') {
                extensions = ['tree_base'];
            }
            var sort_request = BabblingBrook.Models.sortRequest(
                data.sort_request,
                'Sort request model test.',
                extensions
            );
            if (test1 === false || test2 === false || sort_request === false) {
                meta_data.onError('SortRequest_test');
                return;
            }

            BabblingBrook.Domus.Filter.sortRequested(
                sort_request,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.request_domain,
                meta_data.timeout
            );
        },

        /**
         * Receives a message from the client about a feature that has been used.
         *
         * Passes it on to the suggestions class for processing.
         *
         * @param {object} data
         * @param {string} data.feature Name of the feature used.
         * @param {string} data.url Url to record as using the feature.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionRecordFeatureUsed : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test1 = BabblingBrook.Test.isA([data.url, 'url']);
            var test2 = BabblingBrook.Models.featureUseage(data.feature, '');
            if (test1 === false || test2 === false) {
                meta_data.onError('RecordFeatureUsed_test');
                return;
            }

            BabblingBrook.Domus.FeatureUsage.increment(
                data.feature,
                data.url,
                meta_data.request_domain,
                meta_data.onSuccess,
                meta_data.onError
            );
        },

        /**
         * Make a new post. See BabblingBrook.Domus.MakePost for full details.
         *
         * @param {object} data
         * @param {string} data.post The post to make. See BabblingBrook.Models.post for details.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionMakePost : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var valid = BabblingBrook.Models.makePost(data.post);
            if (valid === false) {
                meta_data.onError('MakePost_test');
            }
            new BabblingBrook.Domus.MakePost(data.post, meta_data.onSuccess, meta_data.onError, meta_data.timeout);
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
                meta_data.onError('domus_take_test');
                return;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'Take',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Fetch a users takes for an post.
         *
         * @param {object} data
         * @param {string} data.post_domain The domain of the post to fetch takes for.
         * @param {number} data.post_id The id of the post to get takes for - relative to the domain
         * @param {number} data.post_creation_timestamp Timestamp for when the post was created.
         * @param {string} data.mode The mode of the take. See BabblingBrook.Models.takeMode.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetTakesForPost : function (data, meta_data) {

            var takesFetched = function (take_data) {
                var take_test = BabblingBrook.Models.takes(take_data);
                if (take_test === false) {
                    meta_data.onError('GetTakes_test');
                }
                meta_data.onSuccess(take_data[data.post_domain][data.post_id]);
            };

            BabblingBrook.Domus.ManageTakes.getTakesForPost(
                data.post_domain,
                data.post_id,
                data.post_creation_timestamp,
                takesFetched,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Fetch the stream, post details and sub posts, sort them and return to the client.
         * @param {object} data
         * @param {number} data.post_id
         * @param {string} data.domain The domain that owns the post.
         * @param {object} meta_data See Module definition for more details.
         */
        actionGetPost : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.domain, 'domain'],
                [data.post_id, 'string'],
                [data.revision, 'int|undefined']
            ]);
            if (data.domain === 'deleted') {
                meta_data.onError('GetPost_deleted');
                return;
            }

            if (test === false) {
                meta_data.onError('GetPost_test');
                return;
            }

            /**
             * Callback to send posts back to the client.
             *
             * Fetches the users takes for this post before posting it back.
             *
             * @param {object} post post data. See BabblingBrook.Models.posts with the tree extension for details.
             * @param {object} takes The takes made by the logged on user for this post.
             *
             * @return void
             */
            var takesFetchedCallback = function (post, takes) {
                post.takes = takes[post.domain][post.post_id];
                var post_data = {
                    post : post
                };
                meta_data.onSuccess(post_data);
            };

            /**
             * Callback to send posts back to the client.
             *
             * @param {object} post post data. See BabblingBrook.Models.posts with the tree extension for details.
             * @param {string} domain
             *
             * @return void
             */
            var postFetchedCallback = function (post, domain) {
                if (typeof post.post_id === 'undefined') {
                    meta_data.onError('GetPost_not_found');
                    return;
                }
                BabblingBrook.Domus.ManageTakes.getTakesForPost(
                    post.domain,
                    post.post_id,
                    post.timestamp,
                    takesFetchedCallback.bind(null, post),
                    meta_data.onError,
                    meta_data.timeout
                );
            };

            /**
             * An error callack whenn retrieving a private post.
             *
             * @param {string} stream_error_code The error code returned from the stream domain
             * @param {string} private_error_code The error code returned from this domain.
             *
             * @return void
             */
            var privateErrorCallback = function (stream_error_code, private_error_code) {
                meta_data.onError(private_error_code);
            };

            /**
             * Failed to fetch the post from the stream domain, see if there is a private post available.
             *
             * This is attempted even if the domains are the same because the scientia domain won't fetch a private post.
             *
             * @param {string} error_code
             *
             * @return void
             */
            var streamErrorCallback = function (error_code) {
                BabblingBrook.Library.post(
                    '/post/' + data.domain + '/' + data.post_id + '/json',
                    {
                        'private' : true
                    },
                    postFetchedCallback,
                    meta_data.onError.bind(null, error_code),
                    'GetPost_failed',
                    meta_data.timeout
                );
            };


            var remote_data = {
                post_id : data.post_id,
                revision : data.revision
            };
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                data.domain,
                'GetPost',
                remote_data,
                true,   // using https because edits are always made on https and caching does not work across sub domains.
                postFetchedCallback,
                streamErrorCallback,
                meta_data.timeout
            );
        },

        /**
         * Receives a request from the client to fetch some suggestions.
         * Checks if they are already stored. Returns them if they are.
         * If they are not stored then a request is Sent to the rhythm domain to generate them.
         * Suggestions are returned to the clients 'SuggestionsGenerated' function.
         * @param {string} data.type The type of object suggestions are needed for.
         *                           See BabblingBrook.Models.suggestionTypes for details.
         * @param {object} data.paramaters The paramaters that are needed for this type.
         *                                 See BabblingBrook protocol for details.
         * @param {string} data.rhythm_url The url of the rhythm to run.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetSuggestions : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test1 = BabblingBrook.Test.isA([
                [data.type, 'string'],
                [data.paramaters, 'object'],
                [data.rhythm_url, 'url']
            ]);
            if (test1 ===false) {
                meta_data.onError('GetSuggestions_test');
                return;
            }
            if (BabblingBrook.Models.suggestionTypes(data.type) === false) {
                meta_data.onError('domus_test_start_rhythm_suggestion_type_invalid');
                return;
            }
            if (BabblingBrook.Models.suggestionParamaters(data.paramaters, data.type) === false) {
                meta_data.onError('domus_test_start_rhythm_paramaters_invalid');
                return;
            }

            BabblingBrook.Domus.Suggestion.fetchSuggestions(
                data,
                meta_data.request_domain,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Sends a take request for a ring member to the rings scientia domain.
         *
         * @param {object} data
         * @param {number} data.post_id The domain specific id for this post.
         * @param {string} data.post_domain The domain of the domus where the post is hosted.
         * @param {boolean} data.untake Set to true if the take is being cancelled.
         * @param {string} data.ring The domain and name of the ring seperated with a forward slash.
         * @param {string} data.take_name The take name that is being used in this ring take.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionTakeRingPost : function (data, meta_data) {

            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.post_domain, 'domain'],
                [data.post_id, 'string'],
                [data.ring_name, 'username'],
                [data.ring_domain, 'domain'],
                [data.take_name, 'string'],
                [data.untake, 'boolean']
            ]);
            if (test === false) {
                meta_data.onError('TakeRing_test');
                return;
            }

            var ring_password = BabblingBrook.Domus.Ring.getPassword(data.ring_domain, data.ring_name, 'member');
            var test2 = BabblingBrook.Test.isA([ring_password, 'string']);
            if (test2 === false) {
                meta_data.onError('TakeRing_password');
                return;
            }

            /**
             * Receives the status of a request to take a ring take and forwards it to the client.
             *
             * @param {object} callback_data
             * @param {number} callback_data.post_id The domain specific id for this post.
             * @param {string} callback_data.post_domain The domain of the domus where the post is hosted.
             * @param {string} callback_data.ring_name The domain and name of the ring seperated with a forward slash.
             * @param {string} callback_data.take_name The take name that is being used in this ring take.
             * @param {boolean} callback_data.status Set to true if taken, false if untaken.
             * @param {string} domain
             */
            var takenCallback = function (callback_data, domain) {

                BabblingBrook.Test.isA([
                    [callback_data.post_domain, 'domain'],
                    [callback_data.post_id, 'string'],
                    [callback_data.ring_name, 'string'],
                    [callback_data.ring_domain, 'string'],
                    [callback_data.take_name, 'string'],
                    [callback_data.status, 'boolean']
                ]);
                if (test === false) {
                    meta_data.onError('TakeRing_failed');
                    return;
                }
                meta_data.onSuccess(callback_data);
            };

            var send_data = {
                take_name : data.take_name,
                ring_password : ring_password,
                post_id : data.post_id,
                post_domain : data.post_domain,
                ring_name : data.ring_name,
                username : BabblingBrook.Domus.User.username,
                user_domain : BabblingBrook.Domus.User.domain,
                untake : data.untake
            };

            // Send the take request to the ring domain, using this users take password
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                data.ring_domain,
                'RingTake',
                send_data,
                true,
                takenCallback,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Fetch the take status for a ring on a particular take.
         * This data is private so it needs to be sent to the scientia domain for the ring domain to request it.
         * @param {object} data
         * @param {string} data.post_domain The domain of the post_id for the ring we are getting a take status for.
         * @param {number} data.post_id
         * @param {string} data.ring_domain
         * @param {string} data.ring_name
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetRingTakeStatus : function (data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [data.post_domain, 'domain'],
                [data.post_id, 'string'],
                [data.ring_name, 'username'],
                [data.ring_domain, 'domain'],
                [data.field_id, 'uint']
            ]);
            if (test === false) {
                meta_data.onError('GetRingTakeStatus_test');
                return;
            }

            var ring_password = BabblingBrook.Domus.Ring.getPassword(data.ring_domain, data.ring_name, 'member');
            var test2 = BabblingBrook.Test.isA([ring_password, 'string']);
            if (test2 === false) {
                meta_data.onError('GetRingTakeStatus_password');
                return;
            }

            /**
             * Receives the status of a rings take_names for a user and forwards to the client.
             *
             * @param {object} take_Status_data
             * @param {string} take_Status_data.take_status
             * @param {string} take_Status_data.take_status.<name> Each value is either 1 or 0 for taken or not.
             * @param {string} take_Status_data.post_domain The domain of the post_id for the
             *                                   ring we are getting a take status for.
             * @param {number} take_Status_data.post_id
             * @param {string} take_Status_data.ring_domain
             * @param {string} take_Status_data.ring_name
             * @param {string} domain
             *
             * @return void
             */
            var statusFetchedCallback = function (take_Status_data) {
                BabblingBrook.TestErrors.clearErrors();
                var test1 = BabblingBrook.Test.isA([
                    [take_Status_data.post_domain, 'domain'],
                    [take_Status_data.post_id, 'string'],
                    [take_Status_data.ring_name, 'string'],
                    [take_Status_data.ring_domain, 'string'],
                    [take_Status_data.take_status, 'object']
                ], 'Ring tests.');

                var test2 = true;
                jQuery.each(take_Status_data.take_status, function (take_name, status) {
                    var test2 = BabblingBrook.Test.isA([
                        [take_name, 'string'],
                        [status, 'uint']
                    ], 'Status Tests.');
                    if (test2 === false) {
                        return false;        // Exit from jQuery.each function.
                    }
                    return true;            // Continue with jQuery.each function.
                });
                if (test1 === false || test2 === false) {
                    meta_data.onError('GetRingTakeStatus_failed');
                    return;
                }

                meta_data.onSuccess(take_Status_data);
            };

            var ring_domain = data.ring_domain;
            delete data.ring_domain;
            data.ring_password = ring_password;
            data.user_domain = BabblingBrook.Domus.User.domain;
            data.username = BabblingBrook.Domus.User.username;
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                ring_domain,
                'GetRingTakeStatus',
                data,
                true,
                statusFetchedCallback,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Receive a request to fetch data from an scientia domain.
         * @param {object} request_data
         * @param {string} request_data.url The url to request data from.
         * @param {object} request_data.data The data to send with the ajax post request.
         * @param {string} request_data.client_instance The client id that matches this request.
         * @param {string} request_data.domain The client domain that sent this request.
         * @param {Boolean|Undefined} data.https Should the request be made over https.
         * @param {object} meta_data See Module definition for more details.
         */
        actionInfoRequest : function (request_data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test = BabblingBrook.Test.isA([
                [request_data.url, 'url'],
                [request_data.data, 'object'],
                [request_data.https, 'boolean|undefined']
            ]);
            if (test === false) {
                meta_data.onError('InfoRequest_test');
                return;
            }

            /**
             * The callback to call when the data has been fetched from the scientia domain.
             * Returns the data to the client domain.
             * @param {object} data The data to return to the client.
             */
            var getInfoCallback = function (data) {
                BabblingBrook.TestErrors.clearErrors();
                var test = BabblingBrook.Test.isA([
                    [data, 'object|array']
                ]);
                if (test === 'false') {
                    meta_data.onError('InfoRequest_return_data_failed');
                    return;
                }
                meta_data.onSuccess(data);
            };

            var scientia_domain = BabblingBrook.Library.extractDomain(request_data.url);
            var scientia_data = {
                url : request_data.url,
                data : request_data.data,
                client_domain : meta_data.request_domain
            };
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                scientia_domain,
                'FetchData',
                scientia_data,
                request_data.https,
                getInfoCallback,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Receives a request to log out the domus.
         *
         * Sends a logout request to all known client window/tabs.
         * Logs out the domus.
         * Sends a report back to the origional client with the status of the logout request.
         *
         * @param {object} logout_data An empty object. Required to follow action paramater order.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         * @refactor This needs to be reconsidered in light of the new meta_data.onSuccess, meta_data.onError system.
         */
        actionLogout : function (logout_data, meta_data) {
            BabblingBrook.Library.post(
                '/site/domuslogout',
                {
                    client_domain : meta_data.request_domain
                },
                function (returned_data){
                    if (returned_data.success === true) {

                        // Send a message to all client windows/tabs that they need to redirect to the home page.
                        var processed = [];
                        var windows = BabblingBrook.Domus.Interact.getWindows();
                        jQuery.each(windows, function(i, window) {
                            if(jQuery.inArray(window, processed)) {
                                window.postMessage('logout', 'http://' + meta_data.request_domain);
                                processed.push(window);
                            }
                        });

                        meta_data.onSuccess({});
                    } else {
                        meta_data.onError('Logout_failed');
                    }
                },
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Receives a request to delete an post that the logged in user owns.
         *
         * Requests a delete from the users domus.
         * The users data store returns the type of deletion made and a verification secret.
         * The stream and parent post domains are then informed.
         *
         * @param {object} delete_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionDeletePost : function(delete_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [delete_data.post_id, 'string'],
                [delete_data.post_domain, 'domain']
            ]);
            if (test === false) {
                meta_data.onError('DeletePost_test');
                return;
            }
            new BabblingBrook.Domus.DeletePost(
                delete_data,
                meta_data.request_domain,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Receives a request to fetch valid saltNEt domain suggestions.
         *
         * @param {object} request_data Contains the partial domain used to generate suggestions.
         * @param {string} request_data.partial_domain The partial domain used to generate suggestions.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchDomainSuggestions : function(request_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [request_data.partial_domain, 'string']
            ]);
            if (test === false) {
                meta_data.onError('DomainSuggestions_test');
                return;
            }

            BabblingBrook.Library.post(
                '/data/getdomainsuggestions',
                {
                    partial_domain : request_data.partial_domain
                },
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },


        /**
         * Receives a request to fetch valid username + domain suggestions from the users datastore.
         *
         * @param {object} request_data Contains the partial domain used to generate suggestions.
         * @param {string} request_data.partial_domain The partial domain used to generate suggestions.
         * @param {string} request_data.partial_username The partial username used to generate suggestions.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchUsernameAndDomainSuggestions : function(request_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [request_data.partial_domain, 'string'],
                [request_data.partial_username, 'string']
            ]);
            if (test === false) {
                meta_data.onError('domus_fetch_username_and_domain_suggestions_test');
                return;
            }

            BabblingBrook.Library.post(
                '/data/getdomainandusernamesuggestions',
                {
                    partial_domain : request_data.partial_domain,
                    partial_username : request_data.partial_username
                },
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Checks that a domain name is a valid BabblingBrook domain name.
         *
         * Loads the relevant scientia iframe to check it is working
         *
         * @param {object} domain_data Object contianing the domain to check.
         * @param {string} stream_data.domain The stream to fetch. See BabblingBrook.Models.streamName for details.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         * @refactor Change to ValidateDomain
         */
        actionFetchStream : function(stream_data, meta_data) {
            var test = BabblingBrook.Models.streamName(stream_data.stream);
            if (test === false) {
                meta_data.onError('domus_test_get_stream');
                return;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                stream_data.stream.domain,
                'FetchStream',
                stream_data,
                false,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Checks that a domain name is a valid BabblingBrook domain name.
         *
         * Loads the relevant scientia iframe to check it is working
         *
         * @param {object} domain_data Object contianing the domain to check.
         * @param {string} domain_data.domain The domain to check.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         * @refactor Change to ValidateDomain
         */
        actionCheckDomainValid : function(domain_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [domain_data.domain, 'domain']
            ]);
            if (test === false) {
                meta_data.onError('CheckDomainValid_test');
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                domain_data.domain,
                'ReadyRequest',
                {},
                false,
                function () {
                    var vaid_data = {
                        valid : true
                    };
                    meta_data.onSuccess(vaid_data);
                },
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Requests suggested usernames from a BabblingBrook scientia domain.
         *
         * @param {object} username_data Object containing the partial username to check.
         * @param {string} username_data.domain The domain to use when looking for username sugggestions.
         * @param {string} username_data.partial_username The partial username
         *       to use when looking for username sugggestions.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchUsernameSuggestions : function(username_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [username_data.domain, 'domain'],
                [username_data.partial_username, 'string']
            ]);
            if (test === false) {
                meta_data.onError('UsernameSuggestions_test');
                return;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                username_data.domain,
                'UsernameSuggestions',
                {
                    partial_username : username_data.partial_username
                },
                false,
                function (username_data) {
                    meta_data.onSuccess(username_data);
                },
                meta_data.onError,
                meta_data.timeout
            );
        },


        /**
         * A request to validate a username.
         *
         * Forwards the request to the relevant scientia domain.
         *
         * @param {object} username_data Object containing the username to check.
         * @param {string} username_data.domain The domain to use when validating the username.
         * @param {string} username_data.username The username to validate.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         * @refactor Change to ValidateUsername
         */
        actionCheckUsernameValid : function(username_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [username_data.domain, 'domain'],
                [username_data.username, 'username']
            ]);
            if (test === false) {
                meta_data.onError('CheckUsernameValid_test');
                return;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                username_data.domain,
                'CheckUsernameValid',
                {
                    username : username_data.username
                },
                false,
                function (valid_data) {
                    meta_data.onSuccess(valid_data);
                },
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Receives a request to fetch the 'waiting message count' from the users domus domain.
         *
         * @param {object} request_data Empty.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionGetWaitingPostCount : function(request_data, meta_data) {
            BabblingBrook.Library.post(
                '/data/getwaitingpostcount',
                {
                    client_domain : meta_data.request_domain
                },
                meta_data.onSuccess,
                meta_data.onError,
                'domaus_waitingpostcount',
                meta_data.timeout
            );
        },

        /**
         * Receives a request to set the 'waiting message count' for a users inbox.
         *
         * @param {object} request_data The data used in the request.
         * @param {number} request_data.time_viewed The time to use to set the inbox viewed.
         * @param {boolean} request_data.global Is this a global inbox that is having its viewed time set.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionSetWaitingPostCount : function(request_data, meta_data) {

            var test = BabblingBrook.Test.isA([
                [request_data.time_viewed, 'uint'],
                [request_data.global, 'boolean'],
                [request_data.type, 'string']
            ]);
            if (test === false) {
                meta_data.onError('domus_SetWaitingPostCount_validation');
                return;
            }
            var types = [
                'private',
                'public'
            ];
            if (jQuery.inArray(request_data.type, types) === -1) {
                meta_data.onError('domus_SetWaitingPostCount_type');
                return;
            }

            BabblingBrook.Library.post(
                '/data/setwaitingpostcount',
                {
                    client_domain : meta_data.request_domain,
                    time_viewed : request_data.time_viewed,
                    global : request_data.global,
                    type : request_data.type
                },
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Receives a declined suggestion from a client site.
         *
         * @param {object} decline_data The data used in the request. See BabblingBrook.Models.declinedSuggestion
         *      for a full definition.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionDeclineSuggestion : function(decline_data, meta_data) {
            var test = BabblingBrook.Models.declinedSuggestion(
                decline_data.type,
                decline_data.stream,
                decline_data.rhythm,
                decline_data.user
            );
            if (test !== true) {
                meta_data.onError('declined_suggestion_data_error');
                return;
            }

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Domus.User.username + '/clientdata/declinesuggestion',
                {
                    type : decline_data.type,
                    client_domain : meta_data.request_domain,
                    stream : decline_data.stream,
                    rhythm : decline_data.rhythm,
                    user : decline_data.user
                },
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request to fetch suggestions for an open list field in a stream.
         *
         * Passes the request onto the scientia domain for the stream.
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

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                open_list_data.stream.domain,
                'OpenListSuggestionsFetch',
                open_list_data,
                false,
                /**
                 * Success callback for the fetched suggestions.
                 *
                 * @param {object} suggestion_data The returned suggestion data.
                 * @param {array} suggestion_data.sugggestions An array of suggestions strings.
                 *
                 * @return {void}
                 */
                function (suggestion_data) {
                    var test = BabblingBrook.Test.isA([
                        [suggestion_data.suggestions, 'array'],
                    ]);
                    if (test !== true) {
                        meta_data.onError('domus_fetch_open_list_suggestions_return_data_error');
                        return;
                    }
                    for (var i = 0; i < suggestion_data.suggestions.length; i++) {
                        test = BabblingBrook.Test.isA([[suggestion_data.suggestions[i], 'string']]);
                        if (test === false) {
                            meta_data.onError('domus_fetch_open_list_suggestions_return_data_error');
                        }
                    }
                    meta_data.onSuccess(suggestion_data);
                },
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request for a rhythm from the client domain.
         *
         * Passes the request onto the scientia domain for the rhythm.
         *
         * @param {object} request_data The data used for the request.
         * @param {object} open_list_data.rhythm  The rhythm to fetch .
         *      See BabblingBrook.Models.rhythmName for a full definition.
         * @param {object} meta_data See Module definition for more details.
         *
         * @return void
         */
        actionFetchRhythm : function(request_data, meta_data) {
            var test = BabblingBrook.Models.rhythmName(request_data);
            if (test === false) {
                meta_data.onError('domus_rhythm_fetch_data_error');
                return;
            }
            var rhythm_url = BabblingBrook.Library.makeRhythmUrl(request_data, 'json', true);
            var scientia_data = {url : rhythm_url};
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.domain,
                'FetchRhythm',
                scientia_data,
                false,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to search for streams.
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionStreamSearch : function (request_data, meta_data) {
            var test = BabblingBrook.Models.searchStreamRequest(request_data);
            if (test === false) {
                meta_data.onError('domus_search_stream_data_error');
                return;
            }

            // Send to the requested domain if it really looks like a domain. Else send it to the
            // users domain.
            var domain = BabblingBrook.Domus.User.domain;
            if (request_data.domain_filter.length > 0
                && BabblingBrook.Test.isA([request_data.domain_filter, 'domain'])
            ) {
                domain = request_data.domain_filter;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                domain,
                'StreamSearch',
                request_data,
                false,
                meta_data.onSuccess,
                function (error_code) {
                    if (typeof error_code === 'undefined') {
                        error_code = 'domus_search_stream_returned_data_error';
                    }
                    meta_data.onError(error_code);
                },
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to search for streams.
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionRhythmSearch : function (request_data, meta_data) {
            var test = BabblingBrook.Models.searchRhythmRequest(request_data);
            if (test === false) {
                meta_data.onError('domus_search_rhythm_data_error');
                return;
            }

            // Send to the requested domain if it really looks like a domain. Else send it to the
            // users domain.
            var domain = BabblingBrook.Domus.User.domain;
            if (request_data.domain_filter.length > 0
                && BabblingBrook.Test.isA([request_data.domain_filter, 'domain'])
            ) {
                domain = request_data.domain_filter;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                domain,
                'RhythmSearch',
                request_data,
                false,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },


        /**
         * Recieves a request from the client to search for users.
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionUserSearch : function (request_data, meta_data) {
            var test = BabblingBrook.Models.searchUserRequest(request_data);
            if (test === false) {
                meta_data.onError('domus_user_search_data_error');
                return;
            }

            // Send to the requested domain if it really looks like a domain. Else send it to the
            // users domain.
            var domain = BabblingBrook.Domus.User.domain;
            if (request_data.domain_filter.length > 0
                && BabblingBrook.Test.isA([request_data.domain_filter, 'domain'])
            ) {
                domain = request_data.domain_filter;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                domain,
                'UserSearch',
                request_data,
                false,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to store some client user data
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionStoreClientUserData : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.key,  'string'],
                [request_data.data, 'object|string']
            ]);
            if (test === false) {
                meta_data.onError('domus_store_client_user_data_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;
            request_data.username = BabblingBrook.Domus.User.username;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'StoreClientUserData',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,//.bind(null, 'domus_store_client_user_data_iframe'),
                meta_data.timeout
            );
        },


        /**
         * Recieves a request from the client to store some client user data
         *
         * @param {type} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchClientUserData : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.key,  'string']
            ]);
            if (test === false) {
                meta_data.onError('domus_fetch_client_user_data_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;
            request_data.username = BabblingBrook.Domus.User.username;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'FetchClientUserData',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to subscribe a stream to the users account on the client website.
         *
         * @param {object} request_data
         * @param {object} request_data.stream A standard stream name object identifying the stream to subscribe.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionSubscribeStream : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream,  'resource-object']
            ]);
            if (test === false) {
                meta_data.onError('domus_subscribe_stream_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'SubscribeStream',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to unsubscribe a stream from the users account on the client website.
         *
         * @param {object} request_data
         * @param {string} request_data.subscription_id The id of the stream  subscription to unsubscribe.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionUnsubscribeStream : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.subscription_id,  'string']
            ]);
            if (test === false) {
                meta_data.onError('domus_unsubscribe_stream_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'UnsubscribeStream',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to subscribe a filter to a users stream subscription for
         * the users account on the client website.
         *
         * @param {object} request_data
         * @param {string} request_data.stream_subscription_id The id of the stream subscription that a
         *      filter is being subscribed to.
         * @param {object} request_data.rhythm A standard rhythm name object identifying the filter to unsubscribe.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionSubscribeStreamFilter : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,  'string'],
                [request_data.rhythm,  'resource-object']
            ]);
            if (test === false) {
                meta_data.onError('domus_subscribe_stream_filter_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'SubscribeStreamFilter',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to unsubscribe a filter from a users stream subscription for
         * the users account on the client website.
         *
         * @param {object} request_data
         * @param {string} request_data.stream_subscription_id The id of the stream subscription that a
         *      filter is being unsubscribed from.
         * @param {string} request_data.filter_subscription_id The id of the filter to unsubscribe.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionUnsubscribeStreamFilter : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,  'string'],
                [request_data.filter_subscription_id,  'string'],
            ]);
            if (test === false) {
                meta_data.onError('domus_unsubscribe_stream_filter_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'UnsubscribeStreamFilter',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to subscribe a ring to a users stream subscription for
         * the users account on the client website.
         *
         * @param {object} request_data
         * @param {string} request_data.stream_subscription_id The id of the stream subscription that a
         *      moderation ring is being subscribed to.
         * @param {object} request_data.ring A standard user object identifying the ring to subscribe.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionSubscribeStreamRing : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,  'string'],
                [request_data.ring,                    'user'],
            ]);
            if (test === false) {
                meta_data.onError('domus_subscribe_stream_ring_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'SubscribeStreamRing',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to unsubscribe a ring from a users stream subscription for
         * the users account on the client website.
         *
         * @param {object} request_data
         * @param {string} request_data.stream_subscription_id The id of the stream subscription that a
         *      moderation ring is being unsubscribed from.
         * @param {string} request_data.ring_subscription_id The id of the ring that a
         *      moderation ring is being unsubscribed from.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionUnsubscribeStreamRing : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,   'string'],
                [request_data.ring_subscription_id,  'string'],
            ]);
            if (test === false) {
                meta_data.onError('domus_unsubscribe_stream_ring_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'UnsubscribeStreamRing',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to change the version of a stream subscription.
         *
         * @param {object} request_data
         * @param {string} request_data.stream_subscription_id The id of the stream subscription that
         *      is having its version changed.
         * @param {string} request_data.new_version A version object representing the new version.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionChangeStreamSubscriptionVersion : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,   'string'],
                [request_data.new_version,              'version-object'],
            ]);
            if (test === false) {
                meta_data.onError('domus_change_stream_subscription_version_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'ChangeStreamSubscriptionVersion',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Recieves a request from the client to change the version of a stream subscription.
         *
         * @param {object} request_data
         * @param {string} request_data.stream_subscription_id
         *      The id of the stream subscription that owns the filter to change the version for.
         * @param {string} request_data.filter_subscription_id The id of the filter to change a version for.
         * @param {string} request_data.new_version A version object representing the new version.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionChangeStreamSubscriptionFilterVersion : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data.stream_subscription_id,   'string'],
                [request_data.filter_subscription_id,   'string'],
                [request_data.new_version,              'version-object'],
            ]);
            if (test === false) {
                meta_data.onError('domus_change_stream_filter_subscription_version_test');
                return;
            }

            request_data.client_domain = meta_data.request_domain;

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'ChangeStreamFilterSubscriptionVersion',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Fetches a users stream subscriptions for a client website.
         *
         * @param {object} request_data
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchStreamSubscriptions : function (request_data, meta_data) {
            request_data.client_domain = meta_data.request_domain;
            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                BabblingBrook.Domus.User.domain,
                'FetchStreamSubscriptions',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Fetches a list of all the versions of a rhythm.
         *
         * @param {type} request_data A standard stream name object.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchRhythmVersions : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data, 'resource-object']
            ]);
            if (test === false) {
                meta_data.onError('domus_fetch_rhythm_versions_test');
                return;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.domain,
                'FetchRhythmVersions',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Fetches a list of all the versions of a stream.
         *
         * @param {type} request_data A standard stream name object.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchStreamVersions : function (request_data, meta_data) {
            var test = BabblingBrook.Test.isA([
                [request_data, 'resource-object']
            ]);
            if (test === false) {
                meta_data.onError('domus_fetch_stream_versions_test');
                return;
            }

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.domain,
                'FetchStreamVersions',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
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
                [request_data.username, 'username']
            ]);
            if (test === false) {
                meta_data.onError('domus_join_ring_test');
                return;
            }

            request_data.user = {
                domain : BabblingBrook.Domus.User.domain,
                username : BabblingBrook.Domus.User.username
            };

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.domain,
                'RingJoin',
                request_data,
                true,
                function (response_data) {
                    if (response_data.success === true) {
                        BabblingBrook.Domus.Ring.appendNewRingMember({
                            domain : request_data.domain,
                            username : request_data.username,
                            password : response_data.ring_domus_password
                        });
                        // !! Important. Must not pass the password to the client site.
                        delete response_data.ring_domus_password;
                    }
                    meta_data.onSuccess(response_data);
                },
                meta_data.onError,
                meta_data.timeout
            );

//                BabblingBrook.Library.post(
//                    '/' + username + '/ring/join',
//                    {},
//
//                );
        },

        /**
         * Directly fetch some posts from a stream. (Does not send them to a sort rhytm)
         *
         * @param {type} request_data See Domus.Filter.fetchStreamAndTreePosts for detials.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionGetPosts : function (request_data, meta_data) {
            BabblingBrook.TestErrors.clearErrors();
            var test1 = BabblingBrook.Test.isA([
                [request_data.sort_request, 'object']
            ], 'sort request isA object');
            var test2 = BabblingBrook.Test.isA([
                [request_data.sort_request.type, 'string']
            ], 'sort request type isA string');
            var extensions = ['domus'];
            if (request_data.sort_request.type === 'tree') {
                extensions.push('tree_base');
            }
            var sort_request = BabblingBrook.Models.sortRequest(request_data.sort_request, 'sortRequest', extensions);
            if (!test1 || !test2 || !sort_request) {
                meta_data.onError('domus_get_posts_test');
                return;
            }
            if (typeof request_data.with_content !== 'boolean') {
                meta_data.onError('domus_get_posts_test');
                return;
            }
            var test3 = BabblingBrook.Test.isA([
                [request_data.search_phrase, 'string|undefined'],
                [request_data.search_title, 'boolean|undefined'],
                [request_data.search_other_fields, 'boolean|undefined']
            ]);
            if (test3 === false) {
                meta_data.onError('domus_get_posts_test');
                return;
            }

            var stream_domain = BabblingBrook.Library.extractDomain(request_data.sort_request.stream_url);

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                stream_domain,
                'GetPosts',
                request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Fetches the number of users waiting to be vetted for membership in a ring.
         *
         * Request must be made by an administrator of the ring.
         *
         * @param {object} request_data See Module definition for more details.
         * @param {string} request_data.domain The domain of the ring to fetch qty of users waiting to be vetted.
         * @param {string} request_data.username The username of the ring to fetch qty of users waiting to be vetted.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionFetchRingUsersWaitingToBeVetted : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data, 'user']
            ]);
            if (test1 === false) {
                meta_data.onError('domus_fetch_ring_users_waiting_to_be_vetted_test');
                return;
            }

            var password = BabblingBrook.Domus.Ring.getPassword(request_data.domain, request_data.username, 'admin');
            if (typeof password === 'undefined') {
                meta_data.onError('domus_fetch_ring_users_waiting_to_be_vetted_not_admin');
                return;
            }
            var new_request_data = {
                ring_username : request_data.username
            };
            new_request_data.admin_password = password;
            new_request_data.admin_user = {
                username : BabblingBrook.Domus.User.username,
                domain : BabblingBrook.Domus.User.domain
            };

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.domain,
                'FetchRingUsersWaitingToBeVetted',
                new_request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Lodges a request by a user to join a ring.
         *
         * @param {string} request_data A standard user object of the ring to request membership from.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionRequestRingMembership : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data, 'user']
            ]);
            if (test1 === false) {
                meta_data.onError('domus_request_ring_membership_test');
                return;
            }

            var new_request_data = {
                ring_username : request_data.username
            };
            new_request_data.membership_request_user = {
                username : BabblingBrook.Domus.User.username,
                domain : BabblingBrook.Domus.User.domain
            };

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.domain,
                'RequestRingMembership',
                new_request_data,
                false,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Proccess a request to accept a ring membership request from a user.
         *
         * An admin of the ring must make this request.
         *
         * @param {object} request_data The request data sent to this action.
         * @param {object} request_data.user A standard user object for the user whose
         *      membership request has been accepted.
         * @param {object} request_data.ring A standard user object for the ring the the user has requested to join.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionAcceptRingMembershipRequest : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data.user, 'user'],
                [request_data.ring, 'user']
            ]);
            if (test1 === false) {
                meta_data.onError('domus_accept_membership_request_test');
                return;
            }

            var password = BabblingBrook.Domus.Ring.getPassword(
                request_data.ring.domain,
                request_data.ring.username,
                'admin'
            );
            if (typeof password === 'undefined') {
                meta_data.onError('domus_accept_membership_request_test_not_admin');
                return;
            }
            var new_request_data = {
                ring_username : request_data.ring.username,
                user : request_data.user
            };
            new_request_data.admin_password = password;
            new_request_data.admin_user = {
                username : BabblingBrook.Domus.User.username,
                domain : BabblingBrook.Domus.User.domain
            };

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.ring.domain,
                'AcceptRingMembershipRequest',
                new_request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Proccess a request to decline a ring membership request from a user.
         *
         * An admin of the ring must make this request.
         *
         * @param {object} request_data The request data sent to this action.
         * @param {object} request_data.user A standard user object for the user whose
         *      membership request has been accepted.
         * @param {object} request_data.ring A standard user object for the ring the the user has requested to join.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionDeclineRingMembershipRequest : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data.user, 'user'],
                [request_data.ring, 'user']
            ]);
            if (test1 === false) {
                meta_data.onError('domus_decline_membership_request_test');
                return;
            }

            var password = BabblingBrook.Domus.Ring.getPassword(
                request_data.ring.domain,
                request_data.ring.username,
                'admin'
            );
            if (typeof password === 'undefined') {
                meta_data.onError('domus_decline_membership_request_test_not_admin');
                return;
            }
            var new_request_data = {
                ring_username : request_data.ring.username,
                user : request_data.user
            };
            new_request_data.admin_password = password;
            new_request_data.admin_user = {
                username : BabblingBrook.Domus.User.username,
                domain : BabblingBrook.Domus.User.domain
            };

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.ring.domain,
                'DeclineRingMembershipRequest',
                new_request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Ban a member of a ring.
         *
         * An admin of the ring must make this request.
         *
         * @param {object} request_data The request data sent to this action.
         * @param {object} request_data.user A standard user object for the user who is being banned.
         * @param {object} request_data.ring A standard user object for the ring the the user is being banned from.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionBanRingMember : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data.user, 'user'],
                [request_data.ring, 'user']
            ]);
            if (test1 === false) {
                meta_data.onError('domus_ban_ring_member_test');
                return;
            }
            var password = BabblingBrook.Domus.Ring.getPassword(
                request_data.ring.domain,
                request_data.ring.username,
                'admin'
            );
            if (typeof password === 'undefined') {
                meta_data.onError('domus_ban_ring_member_not_admin');
                return;
            }
            var new_request_data = {
                ring_username : request_data.ring.username,
                user : request_data.user
            };
            new_request_data.admin_password = password;
            new_request_data.admin_user = {
                username : BabblingBrook.Domus.User.username,
                domain : BabblingBrook.Domus.User.domain
            };

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.ring.domain,
                'BanRingMember',
                new_request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        },

        /**
         * Reinstate a banned member of a ring.
         *
         * An admin of the ring must make this request.
         *
         * @param {object} request_data The request data sent to this action.
         * @param {object} request_data.user A standard user object for the user who is being reinstated.
         * @param {object} request_data.ring A standard user object for the ring the the user is being reinstated to.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns {undefined}
         */
        actionReinstateRingMember : function (request_data, meta_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data.user, 'user'],
                [request_data.ring, 'user']
            ]);
            if (test1 === false) {
                meta_data.onError('domus_reinstate_ring_member_test');
                return;
            }
            var password = BabblingBrook.Domus.Ring.getPassword(
                request_data.ring.domain,
                request_data.ring.username,
                'admin'
            );
            if (typeof password === 'undefined') {
                meta_data.onError('domus_reinstate_ring_member_not_admin');
                return;
            }
            var new_request_data = {
                ring_username : request_data.ring.username,
                user : request_data.user
            };
            new_request_data.admin_password = password;
            new_request_data.admin_user = {
                username : BabblingBrook.Domus.User.username,
                domain : BabblingBrook.Domus.User.domain
            };

            BabblingBrook.Domus.SendToScientiaFrame.sendMessage(
                request_data.ring.domain,
                'ReinstateRingMember',
                new_request_data,
                true,
                meta_data.onSuccess,
                meta_data.onError,
                meta_data.timeout
            );
        }
    };
}());