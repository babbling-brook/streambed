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
 * @fileOverview Processing of filter aglorithms in the filter domain.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

/**
 * Ready function. Called on document load
 */
$(function () {
    'use strict';
    BabblingBrook.Shared.Interact.setup(BabblingBrook.Filter, 'filter');
    BabblingBrook.Shared.Interact.postAMessage(
        {},
        'DomainReady',
        /**
         * An empty callback for when the domus domain acknowledges the ready statement.
         *
         * @return void
         */
        function () {},
        /**
          * Throw an error if one is reported.
          *
          * @return void
          */
        function () {
            console.error('The domus domain domain is not acknowledging the FilterReady message.');
            throw 'Thread execution stopped.';
        }
    );

    window.onerror = function(message, url, line) {
        BabblingBrook.Shared.Interact.postAMessage(
            {
                error : 'Uncaught error : ' + message + ' : url : ' + url + ' line : ' + line
            },
            'Error',
            function () {},
            function () {
                console.error(
                    'window.onerror in the filter ready function is erroring whilst wating for client to respond.'
                );
            }
        );
    };
});

/**
 * @namespace Global object holding methods related to the rhythm domain.
 * @package JS_Filter
 * Runs in the domus domains Rhythm iframe to run Rhythms in an issolated sandbox.
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
 * Important : This should not alow access to any sensitive data.
 */
BabblingBrook.Filter = (function () {
    'use strict';
    /**
     * @type {function} The callback that is registered with the init request and is called when the sort is finished.
     */
    var onSuccess;

    /**
     * @type {function} The callback that is registered with the init request and is called if there is an error.
     */
    var onError;

    /**
     * @type Array Holds the final array of posts, fully sorted, before return to the user.
     */
    var final_posts = [];

    /**
     * @type {number} The index of the post that is currently being processed.
     */
    var current_post_index = -1;

    /**
     * @type {number} The quantity of posts to fetch for sorting. Can be set by the rhythms init rhythm.
     */
    var sort_qty = 1000;

    /**
     * @type {string} The type of sort being requested. Valid values are 'stream' and 'tree'.
     */
    var sort_type = null;

    /**
     * @type {number} Unix timestamp of a date to start fetching posts from.
     *      If not set then the sort_qty are fetched from the current date.
     */
    var posts_from_timestamp = null;

    /**
     * @type {number} Unix timestamp of a date to end fetching posts from.
     *      If not set then the sort_qty are fetched from the current date.
     */
    var posts_to_timestamp = null;


    /**
     * The name of the client website that is requesting this rhythm to run.
     *
     * @type {string}
     */
    var client_domain;

    /**
     * The user who is running this rhyhtm.
     *
     * @type {object} user
     * @type {string} user.username
     * @type {string} user.domain
     */
    var user;

    /**
     * @type {string} search_phrase The search phrase to use when fetching posts. (optional).
     */
    var posts_search_phrase;

    /**
     * @type {boolean} posts_search_title If posts_search_phrase is set, this decides if the title fields should be searched.
     */
    var posts_search_title;

    /**
     * @type {boolean} posts_search_other_fields If posts_search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     */
    var posts_search_other_fields;

    /**
     * @type {object} rhythm The kindred rhythm object sent from the domus domain.
     * @type {string} rhythm.js The javascript of the rhythm that is being executed.
     *      Once this has been evalled it should return three encapsulated public functions,
     *      init() and main(take) and final(posts). See protocol documentation for full definitions.
     */
    var rhythm_data = null;

    /**
     * An array containing the posts that are being sorted. Indexed with the posts stream url.
     *
     * @type {object[]}
     * @type {number} posts[].post_id The local id of the post local to the stream it was made in.
     * @type {number} posts[].timestamp When the post was made.
     * @type {string} posts[].domain The domain of the user that made the post.
     * @type {string} posts[].username The username of the user that made the post.
     * @type {string} posts[].stream_name The name of the stream the post was made in.
     * @type {string} posts[].stream_domain The domain of the stream the post was made in.
     * @type {string} posts[].stream_username The username of the stream the post was made in.
     * @type {string} posts[].stream_version The version of the stream the post was made in.
     * @type {string} posts[].stream_post_mode The post mode that the stream has.
     *      Valid values are 'anyone' and 'owner'.
     * @param {number|undefined} post.sort The sort value of the post as given by the rhythm.
     */
    var posts = null;

    /**
     * @type {array} An array of stream objects that are being sorted.
     */
    var streams;

    /**
     * @type {Number|null} top_parent_post_id The top parent post id for a tree sort.
     */
    var top_parent_post_id = null;

    /**
     * @type {object[]|null} kindred The current users kindred data.
     * @type {string} kindred[].full_username The full username of the kindred user.
     * @type {number} kindred[].score The value of this relationship.
     */
    var kindred = null;

    var posts_with_content = false;

    /**
     * Rhythms can set client paramaters that can be entered by the user on the client website.
     *
     * They are stored here as nam/value pairs.
     *
     * @type object
     */
    var client_params = {};

    var deferred_kindred = jQuery.Deferred();

    /**
     * Creates a final array out of all the sorted streams. It then sorts the array using the sort attribute.
     * posts that do not have a sort element are not included.
     *
     * @return {void}
     */
    var lastSort = function () {

        // Clear any previous results.
        final_posts = [];

        $.each(posts, function (index, post) {
            if (post.hasOwnProperty('sort')) {
                final_posts.push(posts[index]);
            }
        });

        // Sort the final result.
        final_posts.sort(function (a, b) {
            return b.sort - a.sort;
        });

    };

    /**
     * Runs the final function in the rhythm.
     *
     * Passes it the posts object.
     * When final has finished it calls finishSort to send the posts back to the domus domain.
     *
     * @returns {void}
     */
    var runFinal = function () {
        try {
            BabblingBrook.Rhythm.final(posts);
        } catch (exception) {
            console.error('filter final function raised an error.');
            console.log(exception);
            console.log(rhythm_data);
            var error_object = {};
            error_object.error_message = exception.message;
            onError('filter_rhythm_final', error_object);
        }
    }

    /**
     * Runs the main function of a rhythm for the next post in the queue.
     *
     * @returns {void}
     */
    var filterPost = function () {
        current_post_index++;
        // If there are no posts left then it is time to finish this.
        if (typeof posts[current_post_index] === 'undefined') {
            runFinal();
            return;
        }

        try {
            BabblingBrook.Rhythm.main(posts[current_post_index]);
        } catch (exception) {
            console.error('filter main function raised an error.');
            console.log(exception);
            console.log(rhythm_data);
            var error_object = {};
            error_object.error_message = exception.message;
            onError('filter_rhythm_main', error_object);
        }

    }

    /**
     * Callback function that receives requested data from the domus domain and begins processing it.
     *
     * @param {object} data
     * @param {object} data.posts See BabblingBrook.Models.posts for details.
     *
     * @Return void
     */
    var receivePosts = function (data) {
        jQuery.each(data.posts, function(i, post) {
            post.post_id = post.post_id;
        });



        // posts are tested in the domus domain before sending. don't need to do it again here.
        posts = data.posts;

        // Start the main sort process.
        filterPost();
    };

    /**
     * onError callback for GetPosts request
     *
     * GetPost error codes are converted into SortRequest error codes and returned to the domus domain.
     *
     * @param {string} error_code The error code sent from the domus domain.
     * @param {object} error_data any data asociated with the error.
     *
     * @Return void
     */
    var receivePostsError = function (error_code, error_data) {
        if (error_code === 'GetPosts_failed') {
            console.error('GetPosts_failed');
            onError('SortRequest_stream', error_data);
        } else if (error_code === 'GetPosts_private') {
            console.error('GetPosts_private');
            onError('SortRequest_private', error_data);
        } else if (error_code === 'GetPosts_moderation') {
            console.error('GetPosts_moderation');
            onError('SortRequest_moderation', error_data);
        } else if (error_code === 'GetPosts_failed_block_number_missing') {
            console.trace();
            console.error('GetPosts_failed_block_number_missing');
            onError('SortRequest_stream', error_data);
        } else {
            onError('SortRequest_stream', error_data);
        }
    };

    /**
     * Get the posts that require sorting.
     *
     * @return void
     */
    var getPosts = function () {
        // If a search_phrase is set then with_content defaults to true.
        if (typeof search_phrase === 'string') {
            posts_with_content = true;
        }

        BabblingBrook.Shared.Interact.postAMessage(
            {
                sort_qty : sort_qty,
                posts_from_timestamp : posts_from_timestamp,
                posts_to_timestamp : posts_to_timestamp,
                with_content : posts_with_content,
                search_phrase : posts_search_phrase,
                search_title : posts_search_title,
                search_other_fields : posts_search_other_fields,
            },
            'GetPosts',
            receivePosts,
            receivePostsError
         );
    };

    return {

        /**
         * Receives the kindred data via the domus domain domain in case it is needed by rhythms for processing.
         *
         * @param {object} data
         * @param {object} data.kindred See main definition above for details.
         * @param {object} meta_data See Module definition for more details.
         *
         * @reutrn void
         */
        actionReceiveKindredData : function (data, meta_data) {
            var test = BabblingBrook.Test.isA([data.kindred, 'object'], 'kindred data is not valid. ');
            if (test === false) {
                meta_data.onError('filter_test_kindred_data');
                return;
            }

            jQuery.each(data.kindred, function (full_username, score) {
                test = BabblingBrook.Test.isA(
                    [
                        [full_username, 'full-username'],
                        [score, 'int']
                    ],
                    'Kindred row data is not valid.'
                );
                if (test === false) {
                    return false;   // Exit the .each
                }
                return true;        // Continue the .each
            });
            if (test === false) {
                meta_data.onError('filter_test_kindred_data');
                return;
            }
            kindred = data.kindred;
            deferred_kindred.resolve();
            meta_data.onSuccess({});
        },

        /**
         * Initialises a sort Rhythm.
         *
         * Called by the domus domain when an Rhythm needs to run.
         *
         * @param {object} data
         * @param {string} data.sort_type The type of sort to perform. Valid values are 'stream' and 'tree'.
         * @param {object} data.rhythm See BabblingBrook.Models.rhythm for details.
         * @param {object} data.stream Details of the stream.
         * @param {string} data.stream.url The url of the stream.
         * @param {number} data.stream.posts_to_timestamp
         *      Timestamp of the point to fetch posts from. Null represents an era lost in time.
         * @param {number} data.stream.posts_to_timestamp
         *      Timestamp of the point to fetch posts upto. Null represents now.
         * @param {object} data.user The user whose domus domain is running this rhythm.
         * @param {object} data.user.username The username of the user whose domus domain is running this rhythm.
         * @param {object} data.user.domain The domain of the user whose domus domain is running this rhythm.
         * @param {object} data.client_params Paramaters for this rhythm passed through from the client domain.
         * @param {object} meta_data See Module definition for more details.
         *
         * @returns void
         */
        actionRunRhythm : function (data, meta_data) {
            onSuccess = meta_data.onSuccess;
            onError = meta_data.onError;
            var test1 = BabblingBrook.Test.isA([
                [data.rhythm, 'object'],
                [data.streams, 'array|undefined'],
                [data.type, 'string']
            ], 'data test.');
            var test2, test3 = true;
            if (data.type === 'tree' || data.type === 'stream') {
                test2 = BabblingBrook.Test.isA([
                    [data.posts_to_timestamp, 'uint|null'],
                    [data.posts_from_timestamp, 'uint|null'],
                    [data.streams, 'array']
                ], 'stream test.');
            }
            var test3 = BabblingBrook.Models.rhythm(data.rhythm, 'rhythm test.');
            if (test1 === false || test2 === false || test3 === false) {
                onError('filter_test_init');
                return;
            }

            if (typeof data.client_params !== 'undefined') {
                client_params = data.client_params;
            }

            client_domain = data.client_domain;
            user = data.user;

            posts_from_timestamp = data.posts_from_timestamp;
            posts_to_timestamp = data.posts_to_timestamp;

            // reset the public data.
            // see @task 10059
            sort_type = null;
            sort_qty = 1000;
            final_posts = [];
            current_post_index = -1;
            posts_from_timestamp = null;
            posts_to_timestamp = null;
            rhythm_data = {};
            posts = null;
            top_parent_post_id = null;
            streams = null;

            // Only start sorting if kindred data has loaded.
            deferred_kindred.done(function () {        // Timeout callback.
                // Start sorting the Rhythm
                //BabblingBrook.Filter.posts_header = data.header;
                sort_type = data.type;
                streams = data.streams;
                rhythm_data = data.rhythm;
                top_parent_post_id = data.post_id;

                var error_object = {};
                try {
                    /*jslint evil: true*/
                    var eval_code = 'BabblingBrook.Rhythm = (' + data.rhythm.js + '());';
                    eval(eval_code);
                    /*jslint evil: false*/
                } catch (eval_exception) {
                    console.error("Error whilst evaling rhythm js code.");
                    console.error(eval_exception);
                    console.log(rhythm_data);
                    error_object.error_message = eval_exception.message;
                    onError('filter_rhythm_eval_exception', error_object);
                    return;
                }

                if (typeof BabblingBrook.Rhythm.main !== 'function') {
                    console.error('filter main function is not defined.');
                    console.log(rhythm_data);
                    onError('filter_rhythm_evaljs_main_missing');
                    return;
                }

                // Create defaults for init and final
                if (typeof BabblingBrook.Rhythm.init !== 'function') {
                    BabblingBrook.Rhythm.init = function () {
                        BabblingBrook.Filter.processNextPost();
                    };
                }
                if (typeof BabblingBrook.Rhythm.final !== 'function') {
                    BabblingBrook.Rhythm.final = function (final_posts) {
                        BabblingBrook.Filter.finishSort(final_posts);
                    };
                }
                try {
                    BabblingBrook.Rhythm.init();
                } catch (exception) {
                    console.error('filter init function raised an error.');
                    console.log(exception);
                    console.log(rhythm_data);
                    error_object.error_message = exception.message;
                    onError('filter_rhythm_init', error_object);
                    return;
                }
            });
        },

        /**
         * Recieves a request from the rhythm to process the next post.
         *
         * @param {number} sort The sort value to give the current post before moving onto the next one.
         *
         */
        processNextPost : function (sort) {
            if (current_post_index < 0) { // Don't process for the first run as there is no post
                getPosts();
                return;
            }
            posts[current_post_index].sort = sort;
            filterPost();
        },


        /**
         * Recieves a request from the rhythm to finish sorting of posts and send them back to the domus domain.
         *
         * @returns {void}
         */
        finishSort : function (finished_posts) {
            posts = finished_posts;
            lastSort();
            var final_sorted_posts = {
                posts : final_posts,
                type : sort_type,
                post_id : top_parent_post_id
            };
            onSuccess(final_sorted_posts);
        },

        /**
         * Used by the rhythms init method to set the number of posts to fetch for sorting.
         *
         * @param {number} qty The quantity of posts to sort.
         *
         * @return boolean Has the qty been sucessfully set.
         */
        setSortQty : function (qty) {
            var test1 = BabblingBrook.Test.isA([
                [qty, 'uint']
            ]);
            if (test1 === false) {
                onError('filter_rhythm_set_sort_qty_value_invalid');
                return false;
            } else {
                sort_qty = qty;
                return true;
            }
        },

        /**
         * Used by the rhythm to fetch the type of sort that has been requested.
         *
         * @return string Valid responses are 'tree' and 'stream'.
         */
        getSortType : function () {
            return sort_type;
        },

        /**
         * Used by the rhythms init method to set a timestamp to start fetching posts from.
         *
         * @param {number} timestamp The time to fetch posts from.
         *
         * @return boolean Has the timestamp been sucessfully set.
         */
        setFromTimestamp : function (timestamp) {
            var test1 = BabblingBrook.Test.isA([
                [timestamp, 'uint']
            ]);
            if (test1 === false) {
                onError('filter_rhythm_set_from_timestamp_value_invalid');
                return false;
            } else {
                posts_from_timestamp = timestamp;
                return true;
            }
        },

        /**
         * The returns the time set to start fetching posts from.
         *
         * @returns {number|null}
         */
        getFromTimestamp : function () {
            return posts_from_timestamp;
        },

        /**
         * Used by the rhythms init method to set a timestamp to end fetching posts.
         *
         * @param {number} timestamp The time to fetch posts to.
         *
         * @return boolean Has the timestamp been sucessfully set.
         */
        setToTimestamp : function (timestamp) {
            var test1 = BabblingBrook.Test.isA([
                [timestamp, 'uint']
            ]);
            if (test1 === false) {
                onError('filter_rhythm_set_to_timestamp_value_invalid');
                return false;
            } else {
                posts_to_timestamp = timestamp;
                return true;
            }
        },

        /**
         * The returns the time set to end fetching posts.
         *
         * @returns {number|null}
         */
        getToTimestamp : function () {
            return posts_to_timestamp;
        },

        /**
         * Getter for Rhythms to fetch the url of the stream that the posts are in.
         *
         * @returns {string}
         */
        getStreams : function () {
            return streams;
        },

        /**
         * Get all the takes for a user for the posts fetched.
         *
         * Internally it fetches them for each stream in the sort_request.streams array, but it does not inform the
         * user of that.
         *
         * @param {object} user A standard user object representing the user that takes are being fetched for.
         * @param {function} onTakesFetched A callback for when the takes have been fetched.
         *      Accepts one paramater: An nested streams object containing a 'user_takes' object.
         * @param {number} [field_id=2] The id of the field to fetch takes for. Defaults to 2.
         *
         * @returns {undefined}
         */
        getTakesForUser : function (user, onTakesFetched, field_id) {
            if (typeof field_id === 'undefined') {
                field_id = 2;
            }
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    user : user,
                    field_id : field_id
                },
                'FetchTakesForUser',
                onTakesFetched,
                onError.bind(null, 'rhythm_fetch_takes_for_user_error')
            );
        },


        /**
         * Gets all takes for the posts that the rhythm is sorting.
         *
         * Internally it fetches them for each stream in the sort_request.streams array.
         *
         * @param {function} onTakesFetched A callback for when the takes have been fetched.
         *      Accepts one paramater: An nested streams object containing 'user_takes' objects.
         * @param {number} [field_id=2] The id of the field to fetch takes for. Defaults to 2.
         *
         * @returns {undefined}
         */
        getAllTakes : function (onTakesFetched, field_id) {
            if (typeof field_id === 'undefined') {
                field_id = 2;
            }
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    user : user,
                    field_id : field_id
                },
                'FetchTakes',
                onTakesFetched,
                onError.bind(null, 'rhythm_fetch_takes_error')
            );
        },

        /**
         * Getter for Rhythms to fetch the top parent post id for tree sorts.
         * If the sort request is for a <em>tree</em> - sorting all the children of
         * a parent post - then this fetches the ID of the top post.
         *
         * @returns {array}
         */
        getTopParentPostID : function () {
            return top_parent_post_id;
        },

        /**
         * Getter for Rhythms to fetch the currnet users kindred relationships.
         *
         * @returns {array}
         */
        getKindred : function () {
            return kindred;
        },

        /**
         * Fetches data for the Rhythm. Called by the Rhythm when it needs the data.
         *
         * @param {string} url The url to fetch data from.
         *      This must be accessable via the BabblingBrook protocol, using a scientia domain extension.
         * @param {function} rhythmCallback The function to call when the data has been fetched.
         *
         * @return void
         */
        getMiscData : function (url, onFetched) {
            BabblingBrook.Shared.Interact.postAMessage(
                {
                    url : url
                },
                'GetMiscData',
                onFetched,
                onError.bind(null, 'rhythm_getMiscData_error')
            );
        },

        /**
         * public function for the rhythm to store some data in the users domus domain between sessions.
         *
         * @param {string|object} data The data should be a string. JSON data will be automatically converted to
         *      a string.
         * @param {function} onStored The success callback function.
         * @param {function} [onError] The error callback. If there is an error and this is defined then an
         *      error will not be raised as it is assumed to be handled by the rhythm. If there is no error
         *      callback then an sort error will be raised.
         *
         * @returns {void}
         */
        storeData : function (data, onStored, onError) {

            if (typeof data === 'object') {
                data = JSON.stringify(data);
            }

            if (typeof onError !== 'function') {
                onError = function () {
                    onError('rhythm_store_data_failed');
                };
            }

            if (typeof data !== 'string') {
                onError('rhythm_store_data_data_incorrect_format');
                return;
            }

            BabblingBrook.Shared.Interact.postAMessage(
                {
                    data : data
                },
                'StoreData',
                onStored,
                onError
            );

        },

        /**
         * Public function for the rhythm to fetch some data in the users domus domain that was placed there
         * in an earlier sesssion.
         *
         * @param {function} onStored The success callback function.
         * @param {function} [onError] The error callback. If there is an error and this is defined then an
         *      error will not be raised as it is assumed to be handled by the rhythm. If there is no error
         *      callback then an sort error will be raised.
         *
         * @returns {void}
         */
        getStoredData : function (onFetched, onError) {
            if (typeof onError !== 'function') {
                onError = function () {
                    onError('rhythm_get_data_failed');
                };
            }

            BabblingBrook.Shared.Interact.postAMessage(
                {},
                'GetStoredData',
                onFetched,
                onError
            );
        },

        /**
         * Getter for rhythms to fetch the user object.
         *
         * @returns {object}
         */
        getUser : function () {
            return user;
        },

        /**
         * Getter for rhythms to fetch the client_domain
         *
         * @returns {object}
         */
        getClientDomain : function () {
            return client_domain;
        },

        /**
         * Setter to decide if posts are fetched with content or without.
         *
         * @param {boolean} with_content If set tot true then posts will be fetched in full.
         *      Of false then only header information for each post wil be fetched.
         *
         * @returns {object}
         */
        setPostsWithContent : function (with_content) {
            if (typeof with_content !== 'boolean') {
                onError('SortRequest_filter_with_content');
                return;
            }
            posts_with_content = with_content;
        },

        /**
         * Sets the terms for a full text search of the posts.
         *
         * @param {string} search_phrase The phrase to search for.
         *
         * @param {boolean} title If true then the title field is searched.
         * @param {boolean} other_fields If true then all other fields are searched.
         *
         * @returns {undefined}
         */
        setFulltTextSearch : function (search_phrase, title, other_fields) {
            if (typeof search_phrase !== 'string') {
                console.error ('setFulltTextSearch called with an invalid "search_phrase". Should be a string.');
                console.log(search_phrase);
                throw 'execution halted.';
            }
            if (typeof other_fields !== 'boolean') {
                console.error ('setFulltTextSearch called with an invalid "title" paramater. Should be a boolean.');
                console.log(title);
                throw 'execution halted.';
            }
            if (typeof other_fields !== 'boolean') {
                console.error (
                    'setFulltTextSearch called with an invalid "other_fields" paramater Should be a boolean.'
                );
                console.log(other_fields);
                throw 'execution halted.';
            }
            posts_search_phrase = search_phrase;
            posts_search_title = title;
            posts_search_other_fields = other_fields;

        },

        /**
         * Getter for rhythms to fetch the client_domain
         *
         * @returns {object}
         */
        getClientParams : function () {
            return client_params;
        }
    };
}());