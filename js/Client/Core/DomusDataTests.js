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
 * @fileOverview A collection of tests for validating data that is returning from the domus domain.
 */

/**
 * @namespace A collection of tests for validating data that is returning from the domus domain.
 * All actions receive a data item that needs validating.
 * Each test is names the same as the domus domain action whose data it is testing, only preceded by 'test'.
 * They must return either a boolean - representing that validation has succeeded or not,
 * or a modified data item - which also indicats that validation succeded.
 * The only reason for modifying the data should be to apply defaults,
 * all further work should be done in the action callback.
 * @package JS_Client
 */
BabblingBrook.Client.Core.DomusDataTests = (function () {
    'use strict';

        /**
         * A standardised test for when a domus request sends back a response that has a success boolean and
         * an optional error message.
         *
         * @param {object} response_data The data returned from the domus domain.
         *
         * @returns {boolean}
         */
    var standardSuccessTest = function (response_data) {
        var test = BabblingBrook.Test.isA([response_data.success, 'boolean']);
        if (test === false) {
            return false;
        }
        var test2;
        if (response_data.success === true) {
            return true;
        } else {
            test2 = BabblingBrook.Test.isA([response_data.error, 'string']);
        }
        if (test2 === false) {
            return false;
        }
        return true;
    };

    var onStreamFetchedError = function () {
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : 'There was an error whilst fetching a stream to validate a posts contents'
        });
    }

    /**
     * Removes any illeagal html from a posts fields.
     *
     * @param {type} post The post to process.
     * @param {type} stream The stream that the post is in.
     *
     * @returns {object} The post with any illeagal content removed.
     */
    var removeIllegallHtmlFromPost = function (post, stream) {
        var rich_text_instance = BabblingBrook.Client.Component.RichTextFacade();
        var empty_ruleset = {
            elements : {},
            styles : {}
        }
        jQuery.each(stream.fields, function (i, field) {
            // skip the first row.
            if (i === 0) {
                return true;
            }
            // Skip if the content is missing for this row (SortRequests can return just headers)
            if (typeof post.content[i] === 'undefined') {
                return true;
            }
            var html_fragment;
            var rules;
            switch (field.type) {
                case 'textbox':
                    rules = field.valid_html;
                    post.content[i].text = rich_text_instance.testHtmlFragment(post.content[i].text, rules);
                    break;

                case 'link':
                    html_fragment = post.content[i].link_title;
                    rules = empty_ruleset;
                    post.content[i].link_title = rich_text_instance.testHtmlFragment(post.content[i].link_title, rules);
                    // remove any apostrophes that may allow code to hide in a link.
                    post.content[i].link = post.content[i].link.replace('\'', '');
                    post.content[i].link = post.content[i].link.replace('"', '');
                    if (typeof post.content[i].link_thumbnail_url === 'string') {
                        post.content[i].link_thumbnail_url = post.content[i].link_thumbnail_url.replace('\'', '');
                        post.content[i].link_thumbnail_url = post.content[i].link_thumbnail_url.replace('"', '');
                    }
                    break;

                case 'list':
                case 'openlist':
                    rules = empty_ruleset;
                    var selected_length = 0;
                    if (typeof post.content[i].selected !== 'undefined') {
                        selected_length = post.content[i].selected.length;
                    }
                    for (var j=0; j<selected_length; j++) {
                        var new_selected = rich_text_instance.testHtmlFragment(post.content[i].selected[j], rules);
                        post.content[i].selected[j] = new_selected;
                    }
                    break;
            }
        });

        return post;
    };

    return {

        /**
         * Tests data from an info request.
         * @param {object} data Miscellaneous data as requested. IMPORTANT: Must check validity in the callback.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testInfoRequest : function (data, onTested) {
            var test = BabblingBrook.Test.isA([
                [data, 'object|array']
            ]);
            onTested(test);
        },

        /**
         * Tests data from a request to fetch a stream.
         *
         * @param {object} stream_data The stream data.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchStream : function (stream_data, onTested) {
            var test = BabblingBrook.Test.isA([
                [stream_data.streams, 'array']
            ]);
            if (test === true) {
                test = BabblingBrook.Models.streams(stream_data.streams);
            }
            onTested(test);
        },

        /**
         * Receives this users kindred data from the domus domain domain.
         * @param {object} data
         * @param {object} data.kindredtags An array of scores indexed by full username.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testGetKindredTags : function (data, onTested) {
            // @fixme
            onTested(true);
        },

        /**
         * Receives this users kindred data from the domus domain domain.
         * @param {object} data
         * @param {object} data.kindred An array of scores indexed by full username.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testGetKindred : function (data, onTested) {
            var test1 = BabblingBrook.Test.isA([[data.kindred, 'object']]);
            var test2 = true;
            if (BabblingBrook.Library.objectSize(data.kindred) > 0) {
                jQuery.each(data.kindred, function (full_username, score) {
                    test2 = BabblingBrook.Test.isA([
                        [full_username, 'full-username'],
                        [score, 'int']
                    ]);
                    if (!test2) {
                        return false;    // Exit the jQuery.each function.
                    }
                    return true;        // Continue the jQuery.each function.
                });
            }

            var final_test = test1 && test2;
            onTested(final_test);
        },

        /**
         * Tests data returned from GetSuggestions requests.
         * @param {object} data
         * @param {object} data.type
         * @param {object} data.name
         * @param {string} data.suggestions The suggestion data.
         *                                  Structure is different depending on the suggestion type.
         *                                  See protocol for details.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testGetSuggestions : function (data, onTested) {
            var test1 = BabblingBrook.Test.isA([
                [data.suggestions, 'array']
            ]);
            var test2 = BabblingBrook.Models.suggestionTypes(data.type);

            var final_test = test1 && test2;
            onTested(final_test);
        },

        /**
         * Receives status from a take request from the users domus domain.
         * @param {object} data
         * @param {boolean} data.status Was the take successful.
         * @param {number} data.post_id The local post_id of the post that has been taken.
         * @param {string} data.domain The domain of the stream where the post has been taken.
         * @param {number} data.field_id The Id of the field in the post that has been taken.
         * @param {number} data.value The amount that has been taken.
         * @param {string} data.value_type The type of value that has been taken. See BabblingBrook.Models.value_type.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testTake : function (data, onTested) {
            var test1 = BabblingBrook.Test.isA([
                    [data.status, 'boolean'],
                    [data.post_id, 'string'],
                    [data.domain, 'domain'],
                    [data.field_id, 'uint'],
                    [data.value, 'int']
                ]);
            var test2 = BabblingBrook.Models.valueType(data.value_type);

            var final_test = test1 && test2;
            onTested(final_test);
        },

        /**
         * An post has been entered into the domus domain. Test the returned posts integrity.
         *
         * @param {object} post_data
         * @param {object} post_data.post See BabblingBrook.Models.posts with tree child and extensions.
         * @param {string} post_data.instance The client site id for this post.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testMakePost : function (post_data, onTested) {
            var test_array = ['single'];
            // If there is a parent id then treat this an post in a tree so that we don't loose parent and
            // top parent details.
            if (typeof post_data.post.parent_id !== 'undefined' && post_data.post.parent_id !== null) {
                test_array.push('tree');
            }
            // N.B post is placed in an array for testing.
            var post_with_defaults = BabblingBrook.Models.posts([post_data.post], 'posts test.', test_array);

            if (post_with_defaults === false) {
                onTested(false);
                return;
            } else {
                post_data.post = post_with_defaults[0];
            }

            // Fetch the stream so that the post contents can be checked for code injection.
            var onStreamFetched = function (stream) {
                post_data.post = removeIllegallHtmlFromPost(post_data.post, stream);

                onTested(post_data);
            };

            BabblingBrook.Client.Core.Streams.getStream(
                post_with_defaults[0].stream_domain,
                post_with_defaults[0].stream_username,
                post_with_defaults[0].stream_name,
                post_with_defaults[0].stream_version,
                onStreamFetched,
                onStreamFetchedError
            );
        },

        /**
         * Receives data about an post from the domus domain.
         * @param {object} data
         * @param {object} data.post post data. See BabblingBrook.Models.posts with the tree extension for details.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.

         *
         * @return {undefined}
         */
        testGetPost : function (data, onTested) {
            var tested_post = BabblingBrook.Models.posts([data.post], 'posts model test.', ['single']);
            if (tested_post === false) {
                onTested(false);
            }

            // Fetch the stream so that the post contents can be checked for code injection.
            var onStreamFetched = function (stream) {
                // The post will be nested in an array - need to select first element.
                tested_post[0] = removeIllegallHtmlFromPost(tested_post[0], stream);
                onTested(tested_post[0]);
            };

            BabblingBrook.Client.Core.Streams.getStream(
                tested_post[0].stream_domain,
                tested_post[0].stream_username,
                tested_post[0].stream_name,
                tested_post[0].stream_version,
                onStreamFetched,
                onStreamFetchedError
            );
        },

        /**
         * Receives data about an post from the domus domain.
         *
         * @param {object} data
         * @param {object} data.post post data. See BabblingBrook.Models.posts with the tree extension for details.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testGetTakes : function (data, onTested) {
            onTested(true);
        },

        /**
         * Receives data about an post from the domus domain.
         *
         * @param data
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testGetTakesForPost : function (data, onTested) {
            onTested(true);
        },

        /**
         * Shell function to test a feature use has been recorded.
         *
         * @param data
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.

         *
         * @return {undefined}
         */
        testRecordFeatureUsed : function (data, onTested) {
            onTested(true);
        },


        /**
         * Called by the domus domain to pass back a sorted set of posts that were requested with a 'SortRequest'
         * @param {object} data
         * @param {object} data.sort_request See BabblingBrook.Models.sortRequest
         *                                    with 'returned' and possiblly 'tree_base' extensions for details.
         * @param {object} data.posts See BabblingBrook.Models.posts for details
         *                             with 'sorted' and posssiblly 'tree' extensions for detilas.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testSortRequest : function (data, onTested) {
            var test1 = BabblingBrook.Test.isA(
                [
                    [data, 'object'],
                    [data.sort_request, 'object'],
                    [data.posts, 'array']
                ],
                'Error with processSorted data : '
            );
            var test2 = BabblingBrook.Test.isA([data.sort_request.type, 'string']);
            var test3, test4;
            if (data.sort_request.type === 'tree') {
                test3 = BabblingBrook.Models.sortRequest(
                    data.sort_request,
                    'Error with returned sortRequest.',
                    ['tree_base', 'returned']
                );
            } else {
                test3 = BabblingBrook.Models.sortRequest(
                    data.sort_request,
                    'Error with returned sortRequest.',
                    ['returned']
                );
            }
            test4 = BabblingBrook.Models.postHeaders(data.posts, 'Error with returned postHeaders.');

            if (test1 && test2 && test3 && test4) {
                var posts_length = data.posts.length;
                var rows_tested = 0;

                // Fetch the stream so that the post contents can be checked for code injection.
                var onStreamFetched = function (data_row_count, stream) {
                    // The post will be nested in an array - need to select first element.
                    data.posts[data_row_count] = removeIllegallHtmlFromPost(data.posts[data_row_count], stream);
                    rows_tested++;
                    ifFinished();
                };

                var ifFinished = function() {
                    if (rows_tested === posts_length) {
                        onTested(data);
                    }
                }

                if (posts_length === 0) {
                    onTested(data);
                    return;
                }
                for (var i=0; i<posts_length; i++) {
                    if (typeof data.posts[i].content !== 'undefined') {
                        BabblingBrook.Client.Core.Streams.getStream(
                            data.posts[i].stream_domain,
                            data.posts[i].stream_username,
                            data.posts[i].stream_name,
                            data.posts[i].stream_version,
                            onStreamFetched.bind(null, i),
                            onStreamFetchedError
                        );
                    } else {
                        rows_tested++;
                        ifFinished();
                    }
                }
            } else {
                onTested(false);
            }
        },

        /**
         * Receives the status of a rings take_names for a user and forwards
         * to the BabblingBrook.Client.Component.PostRings object for processing.
         * @param {object} data
         * @param {string} data.take_status
         * @param {string} data.take_status.<name> Each value is either 1 or 0 for taken or not.
         * @param {string} data.post_domain The domain of the post_id for the ring we are getting a take status for.
         * @param {number} data.post_id
         * @param {string} data.ring_domain
         * @param {string} data.ring_name
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testGetRingTakeStatus : function (data, onTested) {
            var test1 = BabblingBrook.Test.isA(
                [
                    [data.post_domain, 'domain'],
                    [data.post_id, 'string'],
                    [data.ring_name, 'string'],
                    [data.ring_domain, 'string'],
                    [data.take_status, 'object']
                ],
                'Ring data.'
            );

            var test2 = true;
            jQuery.each(data.take_status, function (take_name, status) {
                test2 = BabblingBrook.Test.isA([
                    [take_name, 'string'],
                    [status, 'uint']
                ], 'Take status.');
                if (!test2) {
                    return false;        // Exit from jQuery.each function.
                }
                return true;            // Continue with jQuery.each function.
            });

            var final_test = test1 && test2;
            onTested(final_test);
        },


        /**
         * Receives the status of a request to take a ring take.
         * @param {object} data
         * @param {number} data.post_id The domain specific id for this post.
         * @param {string} data.post_domain The domain of the domus where the post is hosted.
         * @param {string} data.ring_name The domain and name of the ring seperated with a forward slash.
         * @param {string} data.take_name The take name that is being used in this ring take.
         * @param {boolean} data.status Set to true if taken, false if untaken.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testTakeRingPost : function (data, onTested) {
            var test = BabblingBrook.Test.isA(
                [
                    [data.post_domain, 'domain'],
                    [data.post_id, 'string'],
                    [data.ring_name, 'string'],
                    [data.ring_domain, 'string'],
                    [data.take_name, 'string'],
                    [data.status, 'boolean']
                ],
                'Ring take tests.'
            );
            onTested(test);
        },

        /**
         * Tests the return of a logout request. This is a shell function.
         *
         * @param {object} logout_data An empty object
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testLogout : function (logout_data, onTested) {
            onTested(true);
        },

        /**
         * Tests the return of a request to delete an post.
         *
         * @param {object} delete_data The data object returned from the delete request.
         * @param {boolean} delete_data.status The status of the post after deletion.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testDeletePost : function (delete_data, onTested) {
            var test = BabblingBrook.Test.isA([[delete_data.status, 'string']]);
            onTested(test);
        },

        /**
         * Tests the return of a request to delete an post.
         *
         * @param {object} suggestion_data The data object returned from the domain suggestions request.
         * @param {string[]} suggestion_data.suggestions A list of domain suggestions.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchDomainSuggestions : function (suggestion_data, onTested) {
            var test1 = BabblingBrook.Test.isA([[suggestion_data, 'object']]);
            var test2 = BabblingBrook.Test.isA([[suggestion_data.domains, 'array']]);
            if (test1 === true && test2 === true) {
                onTested(true);
            } else {
                onTested(false);
            }
        },

        /**
         * Tests the return of a request to delete an post.
         *
         * @param {object} suggestion_data The data object returned from the domain suggestions request.
         * @param {string[]} suggestion_data.suggestions A list of domain suggestions.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchUsernameAndDomainSuggestions : function (suggestion_data, onTested) {
            var test1 = BabblingBrook.Test.isA([[suggestion_data, 'object']]);
            var test2 = BabblingBrook.Test.isA([[suggestion_data.success, 'boolean']]);
            var test3 = BabblingBrook.Test.isA([[suggestion_data.suggestions, 'array']]);
            if (test1 === true && test2 === true && test3 === true) {
                onTested(true);
            } else {
                onTested(false);
            }
        },

        /**
         * Tests the return of a request to delete an post.
         *
         * @param {object} suggestion_data The data object returned from the username suggestions request.
         * @param {string[]} suggestion_data.suggestions A list of username suggestions.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchUsernameSuggestions : function (suggestion_data, onTested) {
            var test1 = BabblingBrook.Test.isA([[suggestion_data, 'object']]);
            var test2 = BabblingBrook.Test.isA([[suggestion_data.usernames, 'array']]);
            if (test1 === true && test2 === true) {
                onTested(true);
            } else {
                onTested(false);
            }
        },

        /**
         * Tests the return of a request to see if a domain is a valid BabblingBrook domain.
         *
         * @param {object} valid_data The data object returned from the validity request.
         * @param {boolean} valid_data.valid Is the domain a valid BabblingBrook site.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testCheckDomainValid : function (valid_data, onTested) {
            var test = BabblingBrook.Test.isA([[valid_data.valid, 'boolean']]);
            onTested(test);
        },


        /**
         * Tests the return of a request to see if a username is valid.
         *
         * @param {object} valid_data The data object returned from the validity request.
         * @param {boolean} valid_data.valid Is the username valid.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testCheckUsernameValid : function (valid_data, onTested) {
            var test = BabblingBrook.Test.isA([[valid_data.valid, 'boolean']]);
            onTested(test);
        },

        /**
         * Tests the return of a request to fetch the number of posts that are waiting in a users inboxes.
         *
         * @param {object} count_data The data object returned from the validity request.
         * @param {number} count_data.gloabl The number of posts waiting in the global inbox.
         * @param {number} count_data.gloabl The number of posts waiting in the local inbox.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testGetWaitingPostCount : function (count_data, onTested) {
            var test = BabblingBrook.Test.isA([
                [count_data.private_client,     'object'],
                [count_data.private_global,     'object'],
                [count_data.public_client,      'object'],
                [count_data.public_global,      'object']
            ]);
            if (test === false) {
                onTested(false);
                return;
            }
            var test2 = BabblingBrook.Test.isA([
                [count_data.private_client.qty,         'uint'],
                [count_data.private_global.qty,         'uint'],
                [count_data.public_client.qty,          'uint'],
                [count_data.public_global.qty,          'uint'],
                [count_data.private_client.timestamp,   'uint'],
                [count_data.private_global.timestamp,   'uint'],
                [count_data.public_client.timestamp,    'uint'],
                [count_data.public_global.timestamp,    'uint']
            ]);
            onTested(test2);
        },

        /**
         * Tests the return of a request to see if a username is valid.
         *
         * @param {object} success_data The data object returned from the request to set the viewed time of an inbox.
         * @param {boolean} success_data.success Is the username valid.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testSetWaitingPostCount : function (success_data, onTested) {
            var test = BabblingBrook.Test.isA([[success_data.success, 'boolean']]);
            onTested(test);
        },

        /**
         * Tests the return of a request to decline a suggestion.
         *
         * @param {object} success_data The data object returned from the request to set the viewed time of an inbox.
         * @param {boolean} success_data.success Is the username valid.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testDeclineSuggestion : function (success_data, onTested) {
            var test = BabblingBrook.Test.isA([[success_data.success, 'boolean']]);
            onTested(test);
        },

        /**
         * Tests the callback data for the fetched suggestions.
         *
         * @param {object} suggestion_data The returned suggestion data.
         * @param {array} suggestion_data.sugggestions An array of suggestions strings.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testOpenListSuggestionsFetch : function (suggestion_data, onTested) {
            var test = BabblingBrook.Test.isA([
                [suggestion_data.suggestions, 'array'],
            ]);
            if (test !== true) {
                onTested(false);
                return;
            }
            for (var i = 0; i < suggestion_data.suggestions.length; i++) {
                test = BabblingBrook.Test.isA([[suggestion_data.suggestions[i], 'string']]);
                if (test === false) {
                    onTested(false);
                    return;
                }
            }
            onTested(true);
        },

        /**
         * Tests that a fetched rhythm contains all the elements its should have.
         *
         * @param {object} rhythm_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchRhythm : function (rhythm_data, onTested) {
            onTested(true);
        },

        /**
         * Tests that a stream search is valid
         *
         * @param {object} stream_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testStreamSearch : function (stream_data, onTested) {
            // @fixme
            onTested(true);
        },

        /**
         * Tests that a rhythm search is valid.
         *
         * @param {object} rhythm_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testRhythmSearch : function (rhythm_data, onTested) {
            // @fixme
            onTested(true);
        },

        /**
         * Tests that a user search is valid.
         *
         * @param {object} user_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testUserSearch : function (user_data, onTested) {
            // @fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to fetch user client data looks valid.
         *
         * @param {object} user_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchClientUserData : function (user_data, onTested) {
            var standard_Test = standardSuccessTest(user_data);
            if (standard_Test === false) {
                onTested(false);
                return;
            }
            var test2 = BabblingBrook.Test.isA([user_data.data, 'object|string|undefined']);
            if (test2 === false) {
                onTested(false);
                return;
            }
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to fetch user client data looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testStoreClientUserData : function (response_data, onTested) {
            var test = BabblingBrook.Test.isA([response_data.success, 'boolean']);
            if (test === false) {
                onTested(false);
                return;
            }
            var test2;
            if (response_data.success === true) {
                onTested(true);
                return;
            } else {
                test2 = BabblingBrook.Test.isA([response_data.error, 'string']);
            }
            if (test2 === false) {
                onTested(false);
                return;
            }
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to fetch stream subscriptions looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchStreamSubscriptions : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to fetch stream versions looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         *
         * @return {undefined}
         */
        testFetchStreamVersions : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to fetch rhythm versions looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchRhythmVersions : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to fetch stream subscription versions looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testChangeStreamSubscriptionVersion : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to fetch stream subscription filter versions looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testChangeStreamSubscriptionFilterVersion : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to subscribe a stream looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testSubscribeStream : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to unsubscribe from a stream looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testUnsubscribeStream : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to subscribe a stream filter looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testSubscribeStreamFilter : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to unsubscribe a stream filter looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testUnsubscribeStreamFilter : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to subscribe a stream ring looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testSubscribeStreamRing : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to unsubscribe a stream ring looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testUnsubscribeStreamRing : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to join a ring looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testRingJoin : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },

        /**
         * Tests that the data returned from a request to get some posts looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testGetPosts : function (response_data, onTested) {
            //@fixme
            onTested(true);
        },


        /**
         * Tests that the data returned from a request to fetch ring users waiting to be vetted looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testFetchRingUsersWaitingToBeVetted : function (response_data, onTested) {
            var test1 = BabblingBrook.Test.isA([response_data.success, 'boolean']);
            if (test1 === false) {
                onTested(false);
                return;
            } else {
                var test2;
                if (response_data.success === true) {
                    test2 = BabblingBrook.Test.isA([response_data.qty, 'int']);
                } else {
                    test2 = BabblingBrook.Test.isA([response_data.error, 'string']);
                }
                if (test2 === false) {
                    onTested(false);
                    return;
                }
            }
            onTested(true);
        },

        /**
         * Tests that the data returned from a request for a user request for ring membership looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testRequestRingMembership : function (response_data, onTested) {
            var test1 = BabblingBrook.Test.isA([response_data.success, 'boolean']);
            if (test1 === false) {
                onTested(false);
            } else {
                onTested(true);
            }
        },

        /**
         * Tests that the data returned from a request to accept a ring membership request is valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testAcceptRingMembershipRequest : function (response_data, onTested) {
            onTested(standardSuccessTest(response_data));
        },

        /**
         * Tests that the data returned from a request to decline a ring membership request looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testDeclineRingMembershipRequest : function (response_data, onTested) {
            onTested(standardSuccessTest(response_data));
        },

        /**
         * Tests that the data returned from a request to ban a ring member looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testBanRingMember : function (response_data, onTested) {
            onTested(standardSuccessTest(response_data));
        },

        /**
         * Tests that the data returned from a request to reinstate a ring member looks valid.
         *
         * @param {object} response_data Data returned from the request.
         * @param {function} onTested Callback for after the test has run. Expects two paramaters:
         *      either be a boolean to indicate success or an object to replace the returned data.
         *
         * @return {undefined}
         */
        testReinstateRingMember : function (response_data, onTested) {
            onTested(standardSuccessTest(response_data));
        }
    };
}());
