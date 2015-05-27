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
 * @fileOverview A library of models that are used for testing data as it is passed between domains.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

/**
 * @namespace A library of object models.
 * Also used as a place to store detailed definitions of those objects.
 * Each public method represents an object following a standard paramater format.
 * Defaults are also applied in models and any superfluous information is removed.
 * @package JS_Shared
 */
BabblingBrook.Models = (function () {
    'use strict';
    /**
     * Remove a paramater from the base options.
     * @param {object} base The base options.
     * @param {string} name Name of the parameter to remove.
     */
    var removeParameter = function (base, name) {
        jQuery.each(base, function (i, opt) {
            if (typeof opt !== 'undefined' && opt[0] === name) {    // Not sure why opt is sometimes undefined.
                base.splice(i, 1);
            }
        });
        return base;
    };

    /**
     * Extends the base options of an object with the given extensions.
     * @param {object} options An associative array of options.
     *                         One must be 'base'. Each option is array with the first paramater.
     *                         being a variable and the second being a string representing a valid BabblingBrook.Test type(s).
     * @param {Array|undefined} extensions An array of strings each representing an extension to the base object.
     *                           Each extension must be present in the options paramater
     * @param {string} request_type The name of the function requesting this extension. Used for error messages.
     * @return {string[]} The extended base options.
     */
    var extend = function (options, extensions, request_type) {

        if (typeof extensions === 'undefined') {
            extensions = [];
        }

        var base = jQuery.extend(true, [], options.base);

        if (typeof extensions === 'undefined') {
            return base;
        }

        if (!BabblingBrook.Library.isArray(extensions)) {
            BabblingBrook.TestErrors.reportError('extensions are not an array for '  + request_type);
        }

        jQuery.each(extensions, function (i, extension_name) {
            var found = false;
            jQuery.each(options, function (option_name, option) {
                if (option_name === extension_name) {
                    jQuery.each(option, function (j, opt) {
                        if (opt[2] === 'remove') {
                            base = removeParameter(base, opt[0]);
                        } else {
                            base.push(opt);
                        }
                    });
                    found = true;
                    return false;        // escape from the jQuery.each function.
                }
                return true;            // continue with the jQuery.each function.
            });
            if (!found) {
                BabblingBrook.TestErrors.reportError(
                    'extension ( ' + extension_name + ' ) not found in options for ' + request_type
                );
            }
        });

        return base;
    };

    /**
     * Applies default options to the test object.
     * Checks to see if paramaters in the data are undefined.
     * If they are and there is a default, then the default is applied.
     * @param {object} options An associative array of options. One must be 'base'.
     *                         Each option is array with the first paramater.
     * @return {object} The data with defaults applied.
     */
    var applyDefaults = function (data) {
        jQuery.each(data, function (i, param) {
             // If the param is undefined and there is a default.
            if (typeof param[1] === 'undefined' && typeof param[3] !== 'undefined') {
                data[i][1] = param[3];
            }
        });
        return data;
    };

    /**
     * Extracts data for testing from the extended data.
     * @param {object[]} extended_data An array of data, including definitions, types and defaults.
     * @return {object[]} The extended data with the definitions and defaults removed.
     */
    var extractTestData = function (extended_data) {
        var test_data = [];
        jQuery.each(extended_data, function (i, row) {
            test_data[i] = [row[1], row[2]];
        });
        return test_data;
    };

    /**
     * Extracts data for passing to the callback from the prosses that requested it.
     *
     * @param {object[]} extended_data An array of data, including definitions, types and defaults.
     * @return {object[]} The extended data with the types and defualts removed.
     */
    var extractRealData = function (extended_data, test) {
        var real_data = {};
        jQuery.each(extended_data, function (i, row) {
            real_data[row[0]] = row[1];
        });
        return real_data;
    };

    /**
     * Public methods
     */
    return {

        /**
         * Checks if data represents a valid sort request.
         * @param {object} data The sort_request that is being validated
         * @param {string} data.type The type of results to fetch. Valid values are :
         *      'stream',               All posts in a stream.
         *                              Private posts are included for the logged in user.
         *      'tree',                 All posts in a tree.
         *                              Private posts are included for the logged in user.
         *      'local_private',        All local private posts sent to a user.
         *      'global_private',       All global private posts sent to a user.
         *      'local_sent_private',   All private posts sent by a user in a domain.
         *      'global_sent_private',  All private posts sent by a user.
         *      'sent_all',             All posts made by a user @ todo this is the same as global_all. refactor.
         *      'local_public',         All public posts for a user in a local domain.
         *                              Can be restricted to a stream or tree.
         *      'global_pubilc',        All public posts for a user.
         *                              Can be restricted to a stream or tree.
         *      'local_all',            All public and private posts for a user in a local domain.
         *      'global_all',           All public and private posts for a user.
         * @param {string} data.domain
         * @param {string} data.[streams] An array of stream objects that the posts in the request
         *      are to be fetched from.  Not required type is set to 'private'
         * @param {object[]} data.moderation_rings An array of moderation rings that are used to moderate the request.
         * @param {string} data.moderation_rings.url
         * @param {number} data.[time=null] A timestamp, upto which posts are fetched for the sort request.
         *                            null is equivilent to latest.
         * @param {object} data.filter The filter object through which the sort request is processed.
         *                             See BabblingBrook.Models.filter for full definition.
         * @param {boolean} data.[update=false] Is this an update request or not.
         * @param {boolean} data.post_id The top parent post_id used to limit results within a stream.
         *                                Only used when type is set to 'tree'.
         * @param {number} data.private_page The page number if this is request for private posts.
         * @param {number} data.cache_age The time in seconds to allow cached results to be returned.
         *                                    Used by the domus domain when deciding if it should return cached results.
         * @param {number} data.sort_id The unique id given to a sort_request by the domus domain.
         * @param {number} data.processed_time A timestamp for when the results were processed.
         * @param {number} data.refresh_frequency A time in seconds until new results are available.
         * @param {number} data.sort_qty The number of results requested by the filter.
         * @param {number} data.status The current status of the request. 0 = waiting, 1 = in process, 2 = done.
         * @param {string} [error_message]
         * @param {string[]} extensions An array of strings each representing an extension to the base object.
         *
         * @fixme this needs refactoring to account for requests being sent directly from the client.
         * seperate this different domain info into seperate objects so that they can be tested properly rather
         * than having lots of '|undefined'
         *
         * @return {Boolean|Object} false or the passed in data with defaults applied.
         */
        sortRequest : function (data, error_message, extensions) {
            if (typeof error_message === 'undefined') {
                error_message = '';
            }
            /**
             * Data structure of the sort_request. base request plus options.
             * First paramater in each option array is the name of the paramater.
             * Second paramater in each option is the passed in data.
             * Third paramater is a string of valid types seperted by pipes.
             *     Valid types are listed in BabblingBrook.Test.valid_types.
             * Fourth paramater is a default value, this is optional.
             */
            var options = {
                // Base data that is always included in a sort request object.
                base : [
                    ['type',                    data.type,                  'string'],
                    ['client_uid',              data.client_uid,            'string'],
                    ['streams',                 data.streams,               'array|undefined'],
                    ['moderation_rings',        data.moderation_rings,      'array|undefined'],
                    ['time',                    data.time,                  'uint|null', null],
                    ['filter',                  data.filter,                'object|undefined'],
                    ['update',                  data.update,                'boolean', false],
                    ['refresh_frequency',       data.refresh_frequency,     'uint|undefined'],
                    ['private_page',            data.private_page,          'uint|undefined'],
                    ['block_numbers',           data.block_numbers,         'array|undefined'],
                    ['user',                    data.user,                  'object|undefined'],
                    ['client_params',           data.client_params,         'object|undefined']
                ],
                // Aditional data included in the base request of a tree sort request.
                tree_base : [
                    ['post_id',                data.post_id,              'string']
                ],
                // Additional data that is included in the final sort request that is returned with the data.
                returned : [
                    ['cache_age',               data.cache_age,             'uint|undefined'],
                    ['processed_time',          data.processed_time,        'uint'],
                    ['sort_qty',                data.sort_qty,              'uint'],
                    ['domus_action_id',         data.domus_action_id,       'uint|undefined']
                ],
                // part of the scientia domain request.
                scientia : [
                    ['filter',                  undefined,                  'remove'],
                    ['stream',                  data.stream,                'resource-object'],
                    ['domain',                  undefined,                  'remove'],
                    ['moderation_rings',        undefined,                  'remove'],
                    ['qty',                     data.qty,                   'uint|undefined'],
                    ['sort_id',                 data.sort_id,               'uint|undefined'],
                    ['domus_action_id',         data.domus_action_id,       'uint|undefined'],
                    ['posts_to_timestamp',     data.posts_to_timestamp,   'uint|null'],
                    ['posts_from_timestamp',   data.posts_from_timestamp, 'uint|null']
                ],
                domus : [
                    ['posts_to_timestamp',     data.posts_to_timestamp,   'uint'],
                    ['posts_from_timestamp',   data.posts_from_timestamp, 'uint']
                ]
            };

            var extended_data = extend(options, extensions, 'sortRequest');
            extended_data = applyDefaults(extended_data);
            var test_data = extractTestData(extended_data);

            var valid = BabblingBrook.Test.isA(test_data, error_message);
            if (valid === false) {
                return false;
            }

            // IF not defined (typeof extended_data.filter) will equal function due to built in filter function.
            if (typeof extended_data.filter === 'object') {
                valid = BabblingBrook.Models.filter(data.filter, error_message, false);
                if (valid === false) {
                    return false;
                }
            }

            if (typeof data.moderation_rings !== 'undefined') {
                jQuery.each(data.moderation_rings, function (i, ring) {
                    valid = BabblingBrook.Test.isA([
                        [ring.url, 'url']
                    ], error_message + ' Moderation ring error. ');
                    if (valid === false) {
                        return false;    // Exit from the jQuery.each function.
                    }
                    return true;        // Continue with the jQuery.each function.
                });
                if (valid === false) {
                    return false;
                }
            }

            var valid_request_types = [
                'stream',
                'tree',
                'local_private',
                'global_private',
                'local_sent_private',
                'global_sent_private',
                'sent_all',
                'local_public',
                'global_pubilc',
                'local_all',
                'global_all',
            ];
            if (jQuery.inArray(data.type, valid_request_types) === -1) {
                valid = false;
                BabblingBrook.TestErrors.reportError(error_message + ' type is invalid : ' + data.type);
            }
            if (valid === false) {
                return false;
            }

            if ((data.type === 'stream' || data.type === 'tree')
                && BabblingBrook.Library.isArray(data.streams) === false
                && typeof extensions.domus !== 'undefined'
            ) {
                BabblingBrook.TestErrors.reportError(error_message + ' streams is required for type : ' + data.type);
            }

            if (typeof data.streams === 'array') {
                data.streams.each(function(i, stream) {
                    valid = BabblingBrook.Test.isA([
                        [stream, 'resource-object']
                    ]);
                    if (valid === false) {
                        BabblingBrook.TestErrors.reportError(
                            error_message + ' Stream array contains an entry that is not a stream name object.'
                        );
                    }
                });
            }

            if (data.type !== 'stream' && data.type !== 'tree' && typeof data.private_page === 'undefined') {
                BabblingBrook.TestErrors.reportError(error_message + ' private post requests must include a page number');
            }

            if (valid === true && data.type === 'tree') {
                valid = BabblingBrook.Test.isA([
                    [data.post_id, 'string']
                ]);
            }
            if (valid === false) {
                return false;
            }
            if (typeof data.user === 'object') {
                valid = BabblingBrook.Test.isA([
                    [data.user.domain, 'domain'],
                    [data.user.username, 'username'],
                ]);
            }
            if (valid === false) {
                return false;
            }

            // Return the sort request with defaults applied.
            var validated_data = extractRealData(extended_data, true);
            return validated_data;
        },

        /**
         * Checks if data represents a filter object, as used in sort_requests.
         * @param {object} filter A filter object
         * @param {string} filter.url The url of the filter.
         * @param {string} filter.name The name of the filter.
         * @param {number} filter.priority A uint representing the processing priority of this filter.
         * @param {string} [error_message]
         * @return {boolean} Valid or not.
         */
        filter : function (filter, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            if (!BabblingBrook.Test.isA([[filter, 'object']], error_message)) {
                return false;
            }

            var valid = BabblingBrook.Test.isA([
                [filter.url, 'url'],
                [filter.name, 'string'],
                [filter.priority, 'uint']
            ], error_message);
            return valid;
        },

        /**
         * Checks the integrtiy of  post header objects.
         *
         * @param {object} posts The post header objects to test
         * @param {string} error_message Any error message to append to errors.
         *
         * @returns {boolean}
         */
        postHeaders : function (posts, error_message) {
            if (!BabblingBrook.Test.isA([[posts, 'array']], error_message + ' Testing posts is an array.')) {
                return false;
            }
            var valid = true;
            for (var i = 0; i < posts.length; i++) {
                var valid = BabblingBrook.Models.postHeader(posts[i], error_message);
                if (valid === false) {
                    return false;
                }
            }
            return true;
        },

        /**
         * Checks the integrtiy of an post header object.
         *
         * @param {object} post The post header object to test.
         * @param {string} error_message Any error message to append to errors.
         *
         * @returns {boolean}
         */
        postHeader : function (post, error_message) {
            var valid = BabblingBrook.Test.isA(
                [
                    [post.domain,          'domain'],
                    [post.post_id,        'string'],
                    [post.score,           'int|undefined'],
                    [post.top_parent_id,   'int|undefined|null'],  // @fixme Should only be undefined or null, not both.
                    [post.parent_id,       'int|undefined|null']   // @fixme Should only be undefined or null, not both.
                ],
                error_message + ' post header content error.'
            );
            return valid;
        },

        /**
         * Checks the integrity of the posts data, removes superfluous data and applies defualts.
         *
         * @param {object[]} posts An array of post objects to test.
         * @param {number} posts.top_parent_id The top parent id of an post. Only available with tree requests.
         * @param {object[]} posts.content An array of content objects for an post.
         *      Will only contain the top field with stream requests.
         * @param {number} posts.timestamp The Creation date timestamp of an post.
         * @param {number} posts.post_id The local id of an post.
         * @param {number} posts.parent_id The id of the parent post id to this post.
         *                                  Only available with tree requests.
         * @param {string} posts.domain The home domain of the user who owns this post.
         * @param {string} posts.username The username of the user that owns this post.
         * @param {number} posts.revision The revision number of this post.
         * @param {string} posts.stream_name The name of the stream that this post belongs to.
         * @param {string} posts.stream_domain The domain of the stream that this post belongs to.
         * @param {string} posts.stream_username The username of the stream that this post belongs to.
         * @param {string} posts.stream_version The version of the stream that this post belongs to.
         *                                           In format 'major/minor/patch'.
         * @param {number} posts.depth The depth of the post in a tree. Only available on the post page
         *                              with tree requests.
         * @param {number} posts.status The status of this post. Valid values are 'public', 'private' and 'deleted'.
         * @param {string} [error_message] The error message to appened onto any errors.
         * @param {Array|undefined} extensions Extensions to the base data.
         */
        posts : function (posts, error_message, extensions) {
            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            if (!BabblingBrook.Test.isA([[posts, 'array']], error_message + ' Testing posts is an array.')) {
                return false;
            }
            // An Empty array is valid.
            if (posts.length < 1) {
                return posts;
            }
            var posts_with_defaults = [];
            var valid;
            jQuery.each(posts, function (i, post) {
                /**
                 * Data structure of each post object. base request plus options.
                 * First paramater in each option array is the name of the paramater.
                 * Second paramater in each option is the passed in data.
                 * Third paramater is a string of valid types seperted by pipes.
                 *     Valid types are listed in BabblingBrook.Test.valid_types.
                 * Fourth paramater is a default value, this is optional.
                 */
                var options = {
                    base : [            // Base data that is always included in an posts object.
                        ['timestamp',           post.timestamp,            'uint'],
                        ['post_id',            post.post_id,             'string'],
                        ['domain',              post.domain,               'domain'],
                        ['username',            post.username,             'string'],
                        ['stream_name',         post.stream_name,          'string'],
                        ['stream_domain',       post.stream_domain,        'domain'],
                        ['stream_username',     post.stream_username,      'string'],
                        ['stream_version',      post.stream_version,       'string'],
                        ['revision',            post.revision,             'uint|undefined'],
                        ['content',             post.content,              'object|undefined'],
                        ['takes',               post.takes,                'object|undefined'],
                        ['status',              post.status,               'string|undefined'],
                        ['stream_block',        post.stream_block,         'int|undefined'],
                        ['tree_block',          post.tree_block,           'int|undefined'],
                        ['top_parent_id',       post.top_parent_id,        'string|undefined|null'],
                        ['parent_id',           post.parent_id,            'string|undefined|null'],
                        ['child_count',         post.child_count,          'int|undefined']
                    ],
                    tree : [
                   //     ['stream_child_id', post.stream_child_id,  'string']
                    ],
                    sorted : [
                        ['sort',                post.sort,                 'int']
                    ],
                    single : [            // A single post after it has been submitted.
                        // @refactor to use path instead of id
                    ]
                };

                var extended_data = extend(options, extensions, 'posts');
                extended_data = applyDefaults(extended_data);

                var test_data = extractTestData(extended_data);
                valid = BabblingBrook.Test.isA(test_data, error_message + 'post index i = ' + i);
                if (valid === false) {
                    return false;    // escape the jQuery.each function.
                }

// uncomment when all post requests return the status.
//                if (jQuery.inArray(post.status, ['private', 'public', 'deleted']) === -1) {
//                    BabblingBrook.TestErrors.reportError(error_message + ' status is invalid : ' + post.status);
//                    return false;
//                }

                // Test the post contents object if applicable.
                if (typeof post.content === 'object') {
                    valid = BabblingBrook.Models.postContent(post.content, error_message + 'post index i = ' + i);
                }
                if (valid === false) {
                    return false;    // escape the jQuery.each function.
                }

                posts_with_defaults.push(extractRealData(extended_data));
                return true;        // continue the jQuery.each function.
            });
            if (valid === false) {
                return false;
            }
            return posts_with_defaults;
        },

        /**
         * Verifies the post content field of an post and the fields of a makePost.
         *
         * @param {object} content An associative array of content objects for an post.
         * @param {object} content.<display_order> An associative array of content objects for an post.
         * @param {number} content.<display_order>.display_order The display order of this content row.
         * @param {string|undefined} content.<display_order>.link If this row represents a link, waht is the link.
         * @param {string|undefined} content.<display_order>.link_title If this row represents a link,
         *                                                              what is the link title.
         * @param {boolean|undefined} content.<display_order>.checked If this row represents a checkbox, is it checked.
         * @param {number|undefined} content.<display_order>.value_min If this row represents a value,
         *                                                             what is the maximum value.
         * @param {number|undefined} content.<display_order>.value_min If this row representsa value,
         *                                                             what is the minimum value.
         * @param {string|undefined} content.<display_order>.text If this row represents a textbox, what is the text.
         * @param {string[]|undefined} content.<display_order>.selected If this row represents a list,
         *                                                              what are its selected items.
         * @param {string} [error_message] The error message to appened onto any errors.
         *
         * @return boolean
         */
        postContent : function (content, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            var valid = true;

            jQuery.each(content, function (j, row) {
                valid = BabblingBrook.Test.isA([[row, 'object']], error_message + ' post content row error. index j = ' + j);
                if (valid === false) {
                    return false;    // escape the jQuery.each function.
                }

                valid = BabblingBrook.Test.isA(
                    [
                        [row.display_order, 'uint'],
                        [row.link, 'url|undefined'],
                        [row.link_title, 'string|undefined'],
                        [row.checked, 'boolean|undefined'],
                        [row.value_max, 'int|undefined'],
                        [row.value_min, 'int|undefined'],
                        [row.text, 'string|undefined'],
                        [row.selected, 'array|undefined']
                    ],
                    error_message + ' post content error. index j = ' + j
                );
                if (valid === false) {
                    return false;    // escape the jQuery.each function.
                }
                BabblingBrook.Test.checkAllDefinedOrUndefined(
                    [
                        row.link,
                        row.link_title
                    ],
                    error_message + ' post content error. index j = ' + j
                        + ' link and link_title need to both be defined or undefined.'
                );
                if (row.selected === 'object') {
                    jQuery.each(row, function (k, selected) {
                        BabblingBrook.Test.isA(
                            [[selected, 'string']],
                            error_message + ' post content error. index j = ' + j + ' selected array index k = ' + k
                        );
                        if (valid === false) {
                            return false;    // escape the jQuery.each function.
                        }
                        return true;        // continue the jQuery.each function.
                    });
                }
                return true;        // continue the jQuery.each function.
            });
            return valid;
        },

        /**
         * Used to check the integrity of a single post.
         * @param {object} post
         * @param {object} post.stream Object containing details of the stream that this
         *      post is being submitted to.
         * @param {string} post.stream.name The name of the stream.
         * @param {string} post.stream.domain The domain of the stream.
         * @param {string} post.stream.username The username of the stream.
         * @param {string} post.stream.version The version of the stream.
         * @param {number} post.parent_id
         * @param {number} post.top_parent_id
         * @param {object} post.content See postContent for details.
         * @param {string[]} [post.private_addresses] If the post is private then this is an array of full usernames
         *      to send the post to.
         * @param {string} post.private_secret The secret that the domus domain of a private recipient of an post
         *      needs in order to check with the post owner that they genuinely own the post.
         * @param {string} post.stream_secret The secret that the domus domain of a stream needs in order to
         *      check with the domus domain of the post owner that they genuinely own the post.
         * @param {object} [post.submitting_user] Contains user details.
         * @param {object} [post.submitting_user.username] The username of the user submitting the post.
         * @param {object} [post.submitting_user.username] The domain of the user submitting the post.
         *      domus domain of the post owner that they genuinely own the post.
         * @param {string} post.parent_secret The secret that a domus domain of a parent posts user needs in
         *      order to check with the domus domain of the post owner that they genuinely own the post.
         * @param {string} [error_message] The error message to appened onto any errors.
         * @param {array|undefined} extensions Extensions to the base data. Valid values are 'submitting_user'.
         * @refactor post.stream should be tested through the stream model - current implementation would
         *      require changing other versions of stream to include an extension for the extra parts that they use.
         */
        makePost : function (post, error_message, extensions) {

            if (typeof error_message !== 'string') {
                error_message = '';
            }
            if (typeof extensions === 'undefined') {
                extensions = [];
            }

            var valid = BabblingBrook.Test.isA(
                [
                    [post.parent_id, 'string|undefined'],
                    [post.top_parent_id, 'string|undefined'],
                    [post.content, 'array'],
                    [post.stream, 'object'],
                    [post.stream_secret, 'string|undefined'],
                    [post.parent_secret, 'string|undefined'],
                    [post.private_secret, 'string|undefined'],
                    [post.private_addresses, 'array|undefined']
                ],
                error_message
            );
            if (valid === false) {
                return false;
            }

            valid = BabblingBrook.Test.isA(
                [
                    [post.stream.name, 'resource-name'],
                    [post.stream.domain, 'domain'],
                    [post.stream.username, 'username'],
                    [post.stream.version, 'version']
                ],
                error_message
            );
            if (valid === false) {
                return false;
            }

            var private_users_valid = true;
            if (typeof post.private_addresses !== 'undefined') {
                jQuery.each(post.private_addresses, function(i, address) {
                    valid = BabblingBrook.Test.isA([address, 'full-username']);
                    if (valid === false) {
                        private_users_valid = false;
                        return false;       // Exit the .each
                    }
                    return true;            // Continue the .each
                });
            }
            if (private_users_valid === false) {
                return false;
            }

            if (jQuery.inArray(extensions, ['submitting_user']) !== -1) {
                valid = BabblingBrook.Test.isA([[post.submitting_user, 'uint|undefined']], error_message);
                if (valid === false) {
                    return false;
                }
                valid = BabblingBrook.Test.isA(
                    [
                        [post.submitting_user.username, 'username'],
                        [post.submitting_user.domain, 'domain']
                    ],
                    error_message
                );
                if (valid === false) {
                    return false;
                }
            }

            valid = BabblingBrook.Models.postContent(post.content, error_message);
            return valid;
        },


        /**
         * Checks the integrity of a stream user take request, removes superfluous data and applies defualts.
         *
         * @param {object[]} An array of post objects to test.
         * @param {string} request.username The username of the user whoes takes are being requested.
         * @param {string|undefined} request.domain The domain of the user whoes takes are being requested.
         * @param {string} request.type The type of user take. Valid values are 'stream', 'tree', 'all'
         * @param {String|undefined} request.stream_url The url of the stream that takes are being requested for.
         * @param {number|undefined} request.time The time to use in looking up a block number.
         * @param {string|undefined} request.post_domain The parent post domain for a tree request.
         * @param {number|undefined} request.post_id The parent post id for a tree request.
         * @param {object[]|undefined} request.takes An array of take objects.
         * @param {number|undefined} request.takes.post_id The post_id that this take is for.
         * @param {number|undefined} request.takes.value The value of this take.
         * @param {number|undefined} request.takes.date_taken The date of this take.
         * @param {string} [error_message] The error message to appened onto any errors. (include source location).
         * @param {array|undefined} extensions Extensions to the base data.
         *
         * @return {false|request}
         */
        streamUserTakeRequest : function (request, error_message, extensions) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            if (!BabblingBrook.Test.isA([[request, 'object']], error_message + ' Testing posts is an object.')) {
                return false;
            }

            /**
             * Data structure of each post object. base request plus options.
             * First paramater in each option array is the name of the paramater.
             * Second paramater in each option is the passed in data.
             * Third paramater is a string of valid types seperted by pipes.
             *     Valid types are listed in BabblingBrook.Test.valid_types.
             * Fourth paramater is a default value, this is optional.
             */
            var options = {
                base : [                    // Base data that is always included.
                    ['username',        request.username,        'username'],
                    ['type',            request.type,            'string'],
                    ['field_id',        request.field_id,        'uint|undefined']
                ],
                time : [
                    ['time',            request.time,            'uint|null']
                ],
                domain : [                // check that the domain is present.
                    ['domain',            request.domain,        'domain']
                ],
                block_number : [            // Check that the block number is present.
                    ['block_number',    request.block_number,    'uint']
                ],
                takes : [                    // Check that the take is present.
                    ['takes',            request.takes,            'array'],
                    ['last_full_block', request.last_full_block, 'uint|undefined']
                ]
            };

            var extended_data = extend(options, extensions, 'streamUserTakeRequest');
            extended_data = applyDefaults(extended_data);
            var test_data = extractTestData(extended_data);

            var valid = BabblingBrook.Test.isA(test_data, error_message);
            if (valid === false) {
                return false;
            }

            if (jQuery.inArray(request.type, ['stream', 'tree', 'all']) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' Type is invalid : ' + request.type);
                return false;
            }

            if (request.type === 'stream') {
                BabblingBrook.Test.isA(
                    [request.stream_url,    'url'],
                    error_message + ' If type === "stream" then a stream_url must be present.'
                );
            }

            if (request.type === 'tree') {
                BabblingBrook.Test.isA(
                    [
                        [request.post_id, 'string'],
                        [request.post_domain, 'domain']
                    ],
                    error_message + ' If type === "tree" then an post_domain and post_id must be present.'
                );
            }
            if (jQuery.inArray('takes', extensions) !== -1) {
                jQuery.each(request.takes, function (i, take) {
                    var valid = BabblingBrook.Test.isA(
                        [
                            [take.post_id, 'string'],
                            [take.value, 'int'],
                            [take.date_taken, 'uint']
                        ],
                        error_message + ' take data is invalid.'
                    );
                    if (valid === false) {
                        return false;
                    }
                });
            }

            return request;
        },

        /**
         * Tests if a sort type is valid.
         *
         * @param {string} type The sort type to test.
         * @param {string} error_message The error message to iplicate the calling function.
         * @return {boolean} Is this a valid sort type.
         */
        sortType : function (type, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            if (!BabblingBrook.Test.isA([[type, 'string']], error_message + ' Type is not a string.')) {
                return false;
            }

            var valid_request_types = [
                'stream',
                'tree',
                'local_private',
                'global_private',
                'local_sent_private',
                'global_sent_private',
                'sent_all',
                'local_public',
                'global_pubilc',
                'local_all',
                'global_all'
            ];
            if (jQuery.inArray(type, valid_request_types) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' Type is invalid : ' + type);
                return false;
            }

            return true;
        },

        /**
         * Check if a suggestion type is valid.
         *
         * @param {string} type The type to check.
         * @param {string} [error_message] Any error message to append to the given one.
         *
         * @return boolean
         */
        suggestionTypes : function (type, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            var validSuggestionTypes = [
                'stream_suggestion',
                'stream_filter_suggestion',
                'user_stream_suggestion',
                'stream_ring_suggestion',
                'ring_suggestion',
                'user_suggestion',
                'meta_suggestion',
                'kindred_suggestion'
            ];

            if (!BabblingBrook.Test.isA([[type, 'string']], error_message + ' Type is not a string.')) {
                return false;
            }

            if (jQuery.inArray(type, validSuggestionTypes) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' Type is invalid : ' + type);
                return false;
            }

            return true;
        },


        /**
         * Check if the paramaters given for a suggestion type are valid.
         *
         * @param {object} paramaters The paramaters to test.
         * @param {string} type The type of the suggestion rhythm. Assumed to be valid. Use suggestionTypes to check.
         * @param {string} [error_message] Any error message to append to the given one.
         *
         * @return boolean
         */
        suggestionParamaters : function (paramaters, type, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            // The paramaters can always be empty.
            if (jQuery.isEmptyObject(paramaters) === true) {
                return true;
            }

            var valid = true;
            error_message += ' suggestion paramater object error for type ' + type + '. ';
            switch (type) {
                case 'stream_filter_suggestion' :
                    valid = BabblingBrook.Test.isA(
                        [
                            [paramaters.domain, 'domain'],
                            [paramaters.username, 'username'],
                            [paramaters.name, 'resource-name'],
                            [paramaters.version, 'version']
                        ],
                        error_message
                    );
                    break;

                case 'user_stream_suggestion' :
                    valid = BabblingBrook.Test.isA(
                        [
                            [paramaters.domain, 'domain'],
                            [paramaters.username, 'username']
                        ],
                        error_message
                    );
                    break;

                case 'stream_ring_suggestion' :
                    valid = BabblingBrook.Test.isA(
                        [
                            [paramaters.domain, 'domain'],
                            [paramaters.username, 'username'],
                            [paramaters.name, 'resource-name'],
                            [paramaters.version, 'version']
                        ],
                        error_message
                    );
                    break;

                default :
                    if (jQuery.isEmptyObject(paramaters) === false) {
                        valid = false;
                    }
                    break;
            }

            return valid;
        },


        /**
         * Check if the results given from a suggestion rhythm are valid.
         *
         * @param {array} results The results to test.
         * @param {string} type The type of the suggestion rhythm. Assumed to be valid. Use suggestionTypes to check.
         * @param {string} [error_message] Any error message to append to the given one.
         *
         * @return boolean
         */
        suggestionResults : function (results, type, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }
            error_message += ' suggestion resutls error for type ' + type + '. ';

            var valid = true;
            valid = BabblingBrook.Test.isA(
                [
                    [results, 'array']
                ],
                error_message
            );
            if (valid === false) {
                return false;
            }

            var testStream = function (row) {
                valid = BabblingBrook.Test.isA(
                    [
                        [row.domain, 'domain'],
                        [row.username, 'username'],
                        [row.name, 'resource-name'],
                        [row.version, 'version']
                    ],
                    error_message
                );
                return valid;
            };
            var testRhythm = function (row) {
                valid = BabblingBrook.Test.isA(
                    [
                        [row.domain, 'domain'],
                        [row.username, 'username'],
                        [row.name, 'resource-name'],
                        [row.version, 'version-object']
                    ],
                    error_message
                );
                return valid;
            };
            var testUser = function (row) {
                valid = BabblingBrook.Test.isA(
                    [
                        [row.domain, 'domain'],
                        [row.username, 'username']
                    ],
                    error_message
                );
                return valid;
            };

            jQuery.each(results, function(index, row) {
                switch (type) {
                    case 'stream_suggestion' :
                        valid = testStream(row);
                        break;

                    case 'stream_filter_suggestion' :
                        valid = valid = testRhythm(row);
                        break;

                    case 'user_stream_suggestion' :
                        valid = valid = testRhythm(row);
                        break;

                    case 'stream_ring_suggestion' :
                        valid = valid = testUser(row);
                        break;

                    case 'ring_suggestion' :
                        valid = valid = testUser(row);
                        break;

                    case 'user_suggestion' :
                        valid = valid = testUser(row);
                        break;

                    case 'meta_suggestion' :
                        valid = valid = testRhythm(row);
                        break;

                    case 'kindred_suggestion' :
                        valid = valid = testRhythm(row);
                        break;

                    default :
                        valid = false;
                        break;
                }
                if (valid === false) {
                    return false;   // Escape the .each
                } else {
                    return true;    // Continue the .each
                }
            });

            return valid;
        },

        /**
         * Check if an Rhythm object is valid.
         * @param {string} rhythm.user The username of the owner of the Rhythm.
         * @param {string} rhythm.name The name of the Rhythm.
         * @param {string} rhythm.version The version of the Rhythm.
         * @param {string} rhythm.date_created
         * @param {string} rhythm.status Is the Rhythm public, private or deprecated.
         * @param {string} rhythm.description The description of this Rhythm.
         * @param {string} rhythm.js The Rhythm code in a string.
         * @param {string} [error_message] The error message to append.
         */
        rhythm : function (rhythm, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            var valid = BabblingBrook.Test.isA(
                [
                    [rhythm.username, 'username'],
                    [rhythm.domain, 'domain'],
                    [rhythm.name, 'resource-name'],
                    [rhythm.version, 'version'],
                    [rhythm.date_created, 'uint'],
                    [rhythm.status, 'string'],
                    [rhythm.description, 'string'],
                    [rhythm.params, 'array'],
                    [rhythm.js, 'string']
                ],
                error_message + ' rhythm object error.'
            );

            return valid;
        },

        /**
         * Check if an Rhythm name object is valid.
         *
         * @param {string} rhythm.domain The domain of the owner of the Rhythm.
         * @param {string} rhythm.username The username of the owner of the Rhythm.
         * @param {string} rhythm.name The name of the Rhythm.
         * @param {object} rhythm.version The version of the Rhythm.
         * @param {number} rhythm.version.major The major version of the Rhythm.
         * @param {number} rhythm.version.minor The minor version of the Rhythm.
         * @param {number} rhythm.version.patch The patch version of the Rhythm.
         */
        rhythmName : function (rhythm, version_is_object, error_message) {
            var version_type = 'version-object';
            if (typeof version_is_object === 'undefined' || version_is_object === false) {
                version_type = 'version';
            }

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            var valid = BabblingBrook.Test.isA(
                [
                    [rhythm.domain, 'domain'],
                    [rhythm.username, 'username'],
                    [rhythm.name, 'resource-name'],
                    [rhythm.version, version_type],
                ],
                error_message + ' rhythm name object error.'
            );

            return valid;
        },


        /**
         * Check if an userName object is valid.
         *
         * @param {string} user.domain The domain of the user.
         * @param {string} user.username The username of the user.
         * @param {string} user.is_ring Is the user a ring.
         * @param {string} [error_message] The error message to append.
         */
        userName : function (user, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            var valid = BabblingBrook.Test.isA(
                [
                    [user.domain, 'domain'],
                    [user.username, 'username'],
                    [rhythm.is_ring, 'bollean|undefined']
                ],
                error_message + ' user name object error.'
            );

            return valid;
        },

        /**
         * Check if the value type of an posts value field is valid.
         * @param {string} type
         * @param {string} [error_message]
         * @return {boolean} Valid or not.
         */
        valueType : function (type, error_message) {

            if (typeof error_message !== 'string') {
                error_message = '';
            }

            var valid = BabblingBrook.Test.isA([type, 'string'], error_message + 'value type is not a string.');
            if (valid === false) {
                return false;
            }

            var valid_types = [
                'updown',
                'linear',
                'logarithmic',
                'textbox',
                'stars',
                'button',
                'list'
            ];
            if (jQuery.inArray(type, valid_types) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' value_type is invalid : ' + type);
                return false;
            }
            return true;
        },


        /**
         * Check if the mode of a take request is valid.
         * @param {string} mode
         * @param {string} [error_message]
         * @return {boolean} Valid or not.
         */
        takeMode : function (mode, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            var valid = BabblingBrook.Test.isA([mode, 'string'], error_message + 'mode is not a string.');
            if (valid === false) {
                return false;
            }

            var valid_modes = [
                'add',        // Add the given value to the existing one.
                'new'        // Overwrite the existing value with this one.
            ];
            if (jQuery.inArray(mode, valid_modes) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' value_type is invalid : ' + mode);
                return false;
            }
            return true;
        },

        /**
         * Check if a data returned from a request to fetch the details of a user post take is valid.
         *
         * @param {object} take_data Object containing the details of the value.
         * @param {string} take_data.value The value of the take.
         * @param {string} [error_message] Any error message to append to the generic one.
         *
         * @return {boolean} Is the data valid or not.
         */
        userPostTake : function (take_data, error_message) {
            if (typeof error_message === 'undefined') {
                error_message = '';
            }
            if (typeof take_data !== 'object') {
                console.trace();
                console.error("userPostTake does not validate.");
                return false;
            }

            var valid = BabblingBrook.Test.isA(
                [
                    [take_data.value, 'int'],
                ],
                error_message + ' userPostTake error.'
            );

            return valid;
        },

        /**
         * Checks if a feature useage request is valid.
         * @param {string} feature_name The name of the feature that is being recorded.
         * @param {string} [error_message]
         * @return {boolean} Valid or not.
         */
        featureUseage : function (feature_name, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            var valid = BabblingBrook.Test.isA([feature_name, 'string'], error_message + 'feature_name is not a string.');
            if (valid === false) {
                return false;
            }

            var valid_names = [
                'stream',
                'stream_post',
                'filter',
                'kindred'
            ];
            if (jQuery.inArray(feature_name, valid_names) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' feature_name is invalid : ' + feature_name);
                return false;
            }
            return true;
        },

        /**
         * Checks if an array of takes contains valid data.
         *
         * Checks takes array as returned from the server, nested in the post and domain objects.
         *
         * @param {object} takes A container for takes.
         * @param {object} takes[domain] The domain of nested takes.
         * @param {object} takes[domain][post_id] The id post the post containing nested takes.
         * @param {object} takes[domain][post_id][field_id] The id of a field that has been taken.
         * @param {number} takes[domain][post_id][field_id].value The value of this take.
         * @param {number} takes[domain][post_id][field_id].take_time Timestamp for when the take was made.
         * @param {number} takes[domain][post_id][field_id].status This is set in the client,
         *      not passed back from the server.
         *      Valid values are:
         *          untaken
         *          paused : Waiting to see if the user takes furhter action before processing.
         *          processing : The take is being processed by the server
         *          error : There was an error with the take process.
         *          taken
         *
         * @return booelan
         */
        takes : function (takes, error_message) {
            if (typeof takes !== 'object') {
                BabblingBrook.TestErrors.reportError(error_message + ' takes is not an object');
                return false;
            }

            var valid = true;
            jQuery.each(takes, function (i, domain) {
                if (typeof domain !== 'object') {
                    BabblingBrook.TestErrors.reportError(error_message + ' takes[domain] is not an object');
                    valid = false;
                    return false;
                }

                jQuery.each(domain, function (i, post_id) {
                    if (typeof post_id !== 'object') {
                        BabblingBrook.TestErrors.reportError(error_message + ' takes[domain][post_id] is not an object');
                        valid = false;
                        return false;
                    }

                    jQuery.each(post_id, function (i, field_id) {
                        if (typeof field_id !== 'object') {
                            var error_string = ' takes[domain][post_id][field_id] is not an object';
                            BabblingBrook.TestErrors.reportError(error_message + error_string);
                            return false;
                        }

                        var valid = BabblingBrook.Test.isA(
                            [
                                [field_id.value,        'int'],
                                [field_id.take_time,    'uint'],
                                [field_id.status,       'string|undefined']
                            ],
                            error_message + ' takes field object error.'
                        );

                        if (valid === true) {
                            return true;    // Continue with the .each
                        } else {
                            return false;   // Exit the .each
                        }
                    });

                    if (valid === true) {
                        return true;    // Continue with the .each
                    } else {
                        return false;   // Exit the .each
                    }
                });

                if (valid === true) {
                    return true;    // Continue with the .each
                } else {
                    return false;   // Exit the .each
                }
            });
            return valid;
        },

//        /**
//         * Checks if an array of takes contains valid data.
//         * @param {object[]} takes
//         * @param {number} takes.take_id
//         * @param {number} takes.data_taken
//         * @param {number} takes.value
//         * @param {number} takes.field_id
//         * @param {string} takes.stream_name
//         * @param {string} takes.stream_username
//         * @param {string} takes.stream_domain
//         * @param {string} takes.stream_version
//         * @param {number} takes.post_user_id
//         * @param {string} takes.post_username
//         * @param {string} takes.post_domain
//         * @param {string} [error_message]
//         */
//        takes : function (takes, error_message) {
//
//            if (typeof error_message === 'undefined') {
//                error_message = '';
//            }
//
//            var valid = BabblingBrook.Test.isA([[takes, 'array']], error_message);
//            if (valid === false) {
//                return false;
//            }
//            jQuery.each(takes, function (i, take) {
//                valid = BabblingBrook.Test.isA(
//                    [
//                        [take.take_id, 'uint'],
//                        [take.date_taken, 'uint'],
//                        [take.value, 'int'],
//                        [take.field_id, 'uint'],
//                        [take.stream_name, 'resource-name'],
//                        [take.stream_username, 'username'],
//                        [take.stream_domain, 'domain'],
//                        [take.stream_version, 'version'],
//                        [take.post_user_id, 'uint'],
//                        [take.post_username, 'username'],
//                        [take.post_domain, 'domain']
//                    ],
//                    error_message
//                );
//                if (valid === false) {
//                    return false;    // Exit from the jQuery.each function.
//                }
//                return true;        // Continue with the jQuery.each function.
//            });
//            return valid;
//        },

        /**
         * Test if a stream url looks ok. (does not test if the stream actually exists).
         *
         * @param {string} stream_url The url to check.
         *
         * @returns {boolean}
         */
        streamUrl : function (stream_url) {
            return BabblingBrook.Models.resourceUrl(stream_url, 'stream');
        },

        /**
         * Test if a rhythm url looks ok. (does not test if the rhythm actually exists).
         *
         * @param {string} rhythm_url The url to check.
         *
         * @returns {boolean}
         */
        rhythmUrl : function (rhythm_url) {
            return BabblingBrook.Models.resourceUrl(rhythm_url, 'rhythm');
        },

        /**
         * Test if a standard resource url (stream or rhythm) looks ok. (does not test if it actually exists).
         *
         * @param {string} url The url to check.
         * @param {string} type The type of resource. 'stream', or 'rhythm'
         *
         * @returns {boolean}
         */
        resourceUrl : function (url, type) {
            var old_error_mode = BabblingBrook.TestErrors.getErrorMode();
            BabblingBrook.TestErrors.setErrorMode('return');

            var test  = BabblingBrook.Test.isA([url, 'url']);
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                console.trace();
                return 'Not a valid url.';
            }

            url = BabblingBrook.Library.removeProtocol(url);
            var url_parts = url.split('/');
            if (url_parts.length < 7) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'Not a valid ' + type + ' url';
            }

            test = BabblingBrook.Test.isA([url_parts[0], 'domain']);
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'The domain is invalid';
            }

            test = BabblingBrook.Test.isA([url_parts[1], 'username']);
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'The username is invalid';
            }

            if (type === 'stream') {
                test = url_parts[2] === 'stream' ? true : false;
            } else if (type === 'rhythm') {
                test = url_parts[2] === 'rhythm' ? true : false;
            } else {
                test = false;
            }
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'This is not a valid ' + type + ' url.';
            }

            test = BabblingBrook.Test.isA([url_parts[3], 'resource-name']);
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'The ' + type + ' name is invalid.';
            }
            var version = url_parts[4] + '/' + url_parts[5] + '/' + url_parts[6];
            test =  BabblingBrook.Test.isA([version, 'version']);
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'The version is invalid.';
            }

            BabblingBrook.TestErrors.setErrorMode(old_error_mode);
            return true;
        },


        /**
         * Test if a user url looks ok. (does not test if the user actually exists).
         *
         * @param {string} user_url The url to check.
         *
         * @returns {boolean}
         */
        userUrl : function (user_url) {
            var old_error_mode = BabblingBrook.TestErrors.getErrorMode();
            BabblingBrook.TestErrors.setErrorMode('return');

            var test  = BabblingBrook.Test.isA([user_url, 'url']);
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                console.trace();
                return 'Not a valid url.';
            }

            user_url = BabblingBrook.Library.removeProtocol(user_url);
            var url_parts = user_url.split('/');
            if (url_parts.length < 2) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'Not a valid user url';
            }

            test = BabblingBrook.Test.isA([url_parts[0], 'domain']);
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'The domain is invalid';
            }

            test = BabblingBrook.Test.isA([url_parts[1], 'username']);
            if (test === false) {
                BabblingBrook.TestErrors.setErrorMode(old_error_mode);
                return 'The username is invalid';
            }

            BabblingBrook.TestErrors.setErrorMode(old_error_mode);
            return true;
        },

        /**
         * Test that an array contains stream models.
         * @param {array} streams See the stream model for details of each row.
         * @param {string} [error_message]
         *
         * @return boolean
         */
        streams : function (streams, error_message) {
            if (BabblingBrook.Library.isArray(streams) === false) {
                return false;
            }
            for(var i=0; i < streams.length; i++) {
                var is_stream = BabblingBrook.Models.stream(streams[i]);
                if (is_stream === false) {
                    return false;
                }
            }
            return true;
        },

        /**
         * Test if an stream is valid.
         *
         * @param {object} stream
         * @param {string} stream.domain Domain of the child stream.
         * @param {string} stream.username Username of the owner of the child stream.
         * @param {string} stream.name Name of the child stream.
         * @param {string} stream.version Version of the child stream.
         * @param {string} stream.kind What kind of stream is it. valid values are 'standard' and 'user'
         * @param {number} stream.timestamp Creation timestamp.
         * @param {string} stream.description Text description of this stream.
         * @param {string} stream.status Valid values are 'private', 'public' and 'deprecated'.
         * @param {string} stream.post_mode Describes who can submit posts to this stream.
         * @param {object[]} stream.fields Fields of the stream. See BabblingBrook.Models.streamFields for details.
         * @param {string} stream.presentation_type The recomented presentation method for this stream.
         *      Valid values are 'photowall' and 'list'.
         * @param {string} [error_message]
         *
         * @return boolean
         */
        stream : function (stream, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            if (typeof stream !== 'object') {
                return false;
            }

            var valid = BabblingBrook.Test.isA(
                [
                    [stream, 'object'],
                    [stream.domain, 'domain'],
                    [stream.username, 'username'],
                    [stream.name, 'resource-name'],
                    [stream.version, 'version'],
                    [stream.kind, 'string'],
                    [stream.timestamp, 'uint'],
                    [stream.description, 'string'],
                    [stream.status, 'string'],
                    [stream.post_mode, 'string'],
                    [stream.fields, 'array'],
                    [stream.presentation_type, 'string']
                ],
                error_message
            );
            if (valid === false) {
                return false;
            }

            var kind = [
                'standard',
                'user'
            ];
            if (jQuery.inArray(stream.kind, kind) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' stream.kind not valid : ' + stream.kind);
                return false;
            }

            var status = [
                'private',
                'public',
                'deprecated'
            ];
            if (jQuery.inArray(stream.status, status) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' stream.status not valid : ' + stream.status);
                return false;
            }

            var post_mode = [
                'anyone',
                'owner'
            ];
            if (jQuery.inArray(stream.post_mode, post_mode) === -1) {
                BabblingBrook.TestErrors.reportError(
                    error_message + ' stream.post_mode not valid : ' + stream.post_mode
                );
                return false;
            }

            var presentation_types = [
                'list',
                'photowall'
            ];
            if (jQuery.inArray(stream.presentation_type, presentation_types) === -1) {
                BabblingBrook.TestErrors.reportError(
                    error_message + ' stream.presentation_mode not valid : ' + stream.presentation_type
                );
                return false;
            }

            valid = BabblingBrook.Models.streamFields(stream.fields, error_message + ' field error ');
            if (valid === false) {
                return false;
            }

            return true;
        },

        /**
         * Checks the integrity of fields data.
         *
         * @param {object[]} fields
         * @param {string} fields.label The field label.
         * @param {string} fields.type The field type.
         * @param {number} fields.display_order The field display order.
         * @param {number} data.fields.max_size Only available if field_type = 'textbox' or 'link'.
         * @param {number} data.fields.required Only available if field_type = 'textbox' or 'link'.
         * @param {number} data.fields.regex Only available if field_type = 'textbox'.
         * @param {number} data.fields.regex_error Only available if field_type = 'textbox'.
         * @param {boolean} data.fields.checkbox_default Only available if field_type = 'checkbox'.
         * @param {number} data.fields.select_qty_max Only available if field_type = 'list' or 'openlist'.
         * @param {number} data.fields.select_qty_min Only available if field_type = 'list' or 'openlist'.
         * @param {string[]} data.fields.list Array of list names Only available if field_type = 'list'.
         * @param {number} fields.value_min The minimum allowed value for this field.
         *                                  Only available if field_type = 'value'.
         * @param {number} fields.value_max The maximum allowed value for this field.
         *                                  Only available if field_type = 'value'.
         * @param {number} fields.value_type The value type for this field. Only available if field_type = 'value'.
         * @param {string} fields.value_options The value options for this field.
         *                                      Only available if field_type = 'value'.
         * @param {number} data.fields.rhythm_check_url Only available if field_type = 'value'.
         * @param {string} [error_message]
         *
         * @returns {boolean}
         */
        streamFields : function (fields, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            // First row is null to make the fields 1 based.
            var valid = BabblingBrook.Test.isA([[fields, 'array|null']], error_message);
            if (valid === false) {
                return false;
            }

            jQuery.each(fields, function (index, field) {
                // Skip the first row, it will be null
                if (index === 0) {
                    return true;
                }

                valid = BabblingBrook.Test.isA([[index, 'uint']], error_message);
                if (valid === false) {
                    return false; // Escape the jQuery.each function.
                }

                valid = BabblingBrook.Test.isA(
                    [
                        [field, 'object'],
                        [field.label, 'resource-name'],
                        [field.type, 'string'],
                        [field.display_order, 'uint']
                    ],
                    error_message
                );
                if (valid === false) {
                    return false; // Escape the jQuery.each function.
                }

                var field_types = [
                    'textbox',
                    'checkbox',
                    'list',
                    'openlist',
                    'value',
                    'link'
                ];
                if (jQuery.inArray(field.type, field_types) === -1) {
                    BabblingBrook.TestErrors.reportError(
                        error_message + ' fields[' + index + '].type is invalid : ' + field.type
                    );
                    return false;  // Escape the jQuery.each function.
                }

                // Only test for textbox fields if this field type is 'textbox'
                if (field.type === 'textbox') {
                    valid = BabblingBrook.Test.isA(
                        [
                            [field.max_size,            'uint'],
                            [field.required,            'boolean'],
                            [field.regex,               'string|null'],
                            [field.regex_error,         'string|null'],
                            [field.valid_html,          'object'],
                            [field.valid_html.elements, 'object|array'],
                            [field.valid_html.styles,   'object|array'],
                        ],
                        'textbox error ' + error_message
                    );
                    if (valid === false) {
                        return false;  // Escape the jQuery.each function.
                    }

                    // check that the valid_html object is valid.
                    jQuery.each(field.valid_html.elements, function(i, element) {
                        valid = BabblingBrook.Test.isA([[element, 'object|array']]);
                        if (valid === false) {
                            return false;  // Escape the jQuery.each function.
                        }
                        jQuery.each(element, function(j, attribute) {
                            valid = BabblingBrook.Test.isA([[attribute, 'object']]);
                            if (valid === false) {
                                return false;  // Escape the jQuery.each function.
                            }
                            if (Object.keys(attribute).length !== 2) {
                                valid = false;
                                return false;  // Escape the jQuery.each function.
                            }
                            BabblingBrook.Test.isA([[attribute.attribute, 'string']]);
                            if (valid === false) {
                                return false;  // Escape the jQuery.each function.
                            }
                            BabblingBrook.Test.isA([[attribute.required, 'boolean']]);
                            if (valid === false) {
                                return false;  // Escape the jQuery.each function.
                            }
                        });
                        if (valid === false) {
                             return false;  // Escape the jQuery.each function.
                        }
                    });
                    if (valid === false) {
                        return false;  // Escape the jQuery.each function.
                    }
                }

                // Only test for textbox fields if this field type is 'textbox'
                if (field.type === 'link') {
                    valid = BabblingBrook.Test.isA(
                        [
                            [field.max_size,    'uint'],
                            [field.required,    'boolean']
                        ],
                        'link error ' + error_message
                    );
                }
                if (valid === false) {
                    return false;  // Escape the jQuery.each function.
                }

                // Only test for checkbox fields if this field type is 'textbox'
                if (field.type === 'checkbox') {
                    valid = BabblingBrook.Test.isA(
                        [[field.checkbox_default, 'boolean']],
                        'checkbox error ' + error_message
                    );
                }
                if (valid === false) {
                    return false;  // Escape the jQuery.each function.
                }

                // Only test for textbox fields if this field type is 'textbox'
                if (field.type === 'list') {
                    valid = BabblingBrook.Test.isA(
                        [
                            [field.select_qty_max,    'uint'],
                            [field.select_qty_min,    'uint'],
                            [field.list,            'array']
                        ],
                        'list error ' + error_message
                    );
                    if (valid === false) {
                        return false;  // Escape the jQuery.each function.
                    }

                    jQuery.each(field.list, function (i, item) {
                        valid = BabblingBrook.Test.isA([item,    'string'], 'list item error' + error_message);
                        if (valid === false) {
                            return false;    // Escape the jQuery.each function.
                        }
                        return true;        // continue the jQuery.each function.
                    });
                }
                if (valid === false) {
                    return false;  // Escape the jQuery.each function.
                }

                // Only test for textbox fields if this field type is 'textbox'
                if (field.type === 'openlist') {
                    valid = BabblingBrook.Test.isA(
                        [
                            [field.select_qty_max,    'uint'],
                            [field.select_qty_min,    'uint']
                        ],
                        'openlist error ' + error_message
                    );
                }
                if (valid === false) {
                    return false;  // Escape the jQuery.each function.
                }

                // Only test value fields if they are supposed to be present.
                if (field.type === 'value') {
                    valid = BabblingBrook.Test.isA(
                        [
                            [field.value_options,       'string'],
                            [field.value_min,           'int|null'],
                            [field.value_max,           'int|null'],
                            [field.value_type,          'string'],
                            [field.who_can_take,        'string'],
                            [field.rhythm_check_url,    'url|null'],
                            [field.value_list,          'array|undefined']
                        ],
                        'value error ' + error_message
                    );

                    valid = BabblingBrook.Models.valueType(field.value_type, error_message);
                    if (valid === false) {
                        return false; // Escape the jQuery.each function.
                    }

                    var value_options = [
                        'any',
                        'maxminglobal',
                        'maxminpost',
                        'rhythmglobal',
                        'rhythmpost'
                    ];
                    if (jQuery.inArray(field.value_options, value_options) === -1) {
                        BabblingBrook.TestErrors.reportError(
                            error_message + ' fields[' + index + '].options is invalid : ' + field.value_options
                        );
                        return false;    // Escape the jQuery.each function.
                    }
                    if (valid === false) {
                        return false; // Escape the jQuery.each function.
                    }

                    var who_can_take = [
                        'anyone',
                        'owner'
                    ];
                    if (jQuery.inArray(field.who_can_take, who_can_take) === -1) {
                        BabblingBrook.TestErrors.reportError(
                            error_message + ' fields[' + index + '].who_can_take is invalid : ' + field.who_can_take
                        );
                        return false;    // Escape the jQuery.each function.
                    }
                    if (valid === false) {
                        return false; // Escape the jQuery.each function.
                    }

                    if (field.value_type === 'list') {
                        valid = BabblingBrook.Test.isA(
                            [[field.value_list,          'array']],
                            'value error ' + error_message
                        );
                        if (valid === false) {
                            return false; // Escape the jQuery.each function.
                        }

                        jQuery.each(field.value_list, function(list_index, row) {
                            valid = BabblingBrook.Test.isA(
                               [
                                   [row.value,      'int'],
                                   [row.name,       'string']
                               ],
                               'value error ' + error_message
                            );
                            if (valid === false) {
                                return false; // Escape the jQuery.each function.
                            }
                        });
                    }
                }
                if (valid === false) {
                    return false; // Escape the jQuery.each function.
                }

                return true;    // Continue with jQuery.each function.
            });
            return valid;
        },

        /**
         * Test if an stream is valid.
         * @param {string} type Domain of the child stream.
         * @param {string} [error_message]
         */
        ringMembershipType : function (type, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            var valid = BabblingBrook.Test.isA([[type, 'string|boolean']], error_message);
            if (valid === false) {
                return false;
            }

            if (typeof type === 'boolean' && type === true) {
                return false;
            } else if (typeof type === 'boolean' && type === false) {
                return true;
            }

            var type_options = [
                'public',
                'invitation',
                'admin_invitation',
                'request',
                'super_ring'
            ];
            if (jQuery.inArray(type, type_options) === -1) {
                BabblingBrook.TestErrors.reportError(error_message + ' type not  :' + type);
                return false;    // Escape the jQuery.each function.
            }
            return true;
        },

        /**
         * Checks if an url represents a valid rhythm url.
         *
         * @param {string} url The url to check.
         * @param {string[]} actions An array of action names. The url must contain one of these.
         *                           If the first element is an asterisk then any action will be accepted.
         * @param {string} thing The thing that this is an url for. Eg stream or rhythm.
         * @param {string} [error_message]
         *
         * @return boolean
         */
        protocolUrl : function (url, actions, thing, error_message) {

            if (typeof error_message === 'undefined') {
                error_message = '';
            }

            url = BabblingBrook.Library.removeProtocol(url);
            var valid = BabblingBrook.Test.isA([[url, 'url']], error_message);
            if (valid === false) {
                return false;
            }

            var error = '';
            var url_array = url.split('/');
            if (!BabblingBrook.Test.isA([[url_array[1], 'username']], error_message)) {
                error = 'username is invalid.';
            }
            if (url_array[2] !== thing) {
                error = 'thing is not a ' + thing + '.';
            }
            if (!BabblingBrook.Test.isA([[url_array[3], 'resource-name']], error_message)) {
                error = 'thing name is not valid.';
            }
            var is_version = BabblingBrook.Test.isA(
                [[url_array[4] + '/' + url_array[5] + '/' + url_array[6], 'version']],
                error_message
            );
            if (jQuery.inArray(url_array[7], actions) === -1 && actions[0] !== '*') {
                error = 'action is not valid : ' + url_array[7];
            }
            if (!is_version) {
                error = 'thing version is not valid.';
            }

            if (error.length > 0) {
                BabblingBrook.TestErrors.reportError(error_message + ' url ( ' + url + ' ) is not valid. ' + error);
                return false;
            }

            return true;
        },

        /**
         * Tests if the passed in type is a valid error type.
         * @param {string} type
         * @return {boolean}
         */
        errorTypes : function (type) {

            var type_options = [
//                'client_error_error',
//                'client_domus_error_error',
//                'client_domus_fetch_suggestions',
//                'client_domus_get_post',
//                'client_domus_scientia_request',
//                'client_domus_make_post',
//                'client_domus_ring_status',
//                'client_domus_ring_take',
//                'client_domus_sort_request',
//                'client_domus_take',
//                'filter_domus_get_posts',
//                'filter_domus_sort_finished',
//                'generic',
//                'iframe_not_loading',
//                'scientia_error_error',
//                'scientia_get_rhythm',
//                'scientia_get_data',
//                'scientia_get_post',
//                'scientia_get_posts_block',
//                'scientia_get_posts_block_number',
//                'scientia_get_posts_update',
//                'scientia_ring_take',
//                'scientia_ring_take_status',
//                'scientia_domus_rhythm',
//                'scientia_domus_data_fetched',
//                'scientia_domus_domain_ready',
//                'scientia_domus_post',
//                'scientia_domus_posts',
//                'scientia_domus_ring_taken',
//                'scientia_domus_ring_take_status',
//                'scientia_domus_user_take_block_number',
//                'scientia_user_take_block',
//                'kindred_domus_receive_results',
//                'kindred_domus_request_data',
//                'ring_domus_request_data',
//                'ring_domus_revceive_results',
//                'domus_error_error',
//                'domus_fetch_data',
//                'domus_guid_invalid',
//                'domus_scientia_get_rhythm',
//                'domus_scientia_get_data',
//                'domus_scientia_get_post',
//                'domus_scientia_get_posts',
//                'domus_scientia_get_ring_take_status',
//                'domus_scientia_get_user_takes_block',
//                'domus_scientia_get_user_takes_block_number',
//                'domus_scientia_ring_take',
//                'domus_scientia_save_ring_results',
//                'domus_kindred_data',
//                'domus_make_post',
//                'domus_suggestion_url',
//                'domus_take',
//                'domus_user_data',
//                'suggestion_domus_generated',
//                'suggestion_domus_get_data',
//                'domus_get_takes_user',
//                'domus_action_data_invalid',
//                'timeout',
//                'domus_action_error',
//                'filter_error_error',
//                'domus_filter_init',
//
//                'scientia_domain',              // A scientia domain is not responding
//                'scientia_getrhythm_data',        // scientia domain is reporting that the request data
//                                            // sent to the getRhythm action is failing validation.
//                'scientia_getrhythm',             // A request to an scientia domain for an Rhythm does not
//                                            // return a valid response.
//                'filter_init',              // The intialisation of a filter in the filter sub domain failed.
//                                            // Usually caused by the filter data failing the tests.
//                'filter_unknown',           // An unknown error has been raised for a filter.
//                'filter_domain',            // The domain of a filter is not responding.
//                'filter_rhythm_syntax',       // An error whilst evaling a filter Rhythm.
//                'filter_rhythm_init',         // An error whilst running the init function of a filter rhythm.
//                'filter_rhythm_main',         // An error whilst running the main function of a filter rhythm.
//                'filter_rhythm_custom',       // A custom error raised by an Rhythm.
//                'filter_posts',            // The data representing posts requested by a filter is invalid.
//                'scientia_get_user_take_block_number',  // There was an error in the ajax request for a user take block
//                                                    // number request. Or an error in the data validation.
//                'scientia_get_user_take_block',         // There was an error in the ajax request for a user take block
//                                                    // request. Or an error in the data validation.
//                'scientia_fetch_data_validation',       // The data provided for a fetch data request in scientia info domain
//                                                    // is not validating.
//                'scientia_fetch_data',                  // The server response to a scientia domain fetch data request
//                                                    // is invalid.
//                'scientia_stream_validation',           // The scientia domain cannot validate data for a stream json request.
//                                                    // Derived in the client from scientia_fetch_data_validation.
//                'stream_validation',                // Stream json data is not validating.
//                'scientia_stream_json',                 // The server response to a scientia domain fetch stream json data
//                                                    // is invalid. Derived in the client from info_fetch_data.
//                'stream_unknown',                   // An unknown error has occured during a stream json request.
//                                                    // Derived in the client if the error code is not recognised.
//                'scientia_post_validation',            // An post object returned by an scientia domain is not validating.
//                                                    // Derived in the client if the error code is not recognised.
//                'post-mode',                       // An error whilst updating the post mode of an stream.
//                                                    // This is a client side error.
//                'store_send_private_message',       // The data received by store.js actionSendPrivateMessage
//                                                    // is invalid.
//                'scientia_send_private_message',        // The data received by scientia.js actionSendPrivateMessage
//                                                    // is invalid.
//                'scientia_server_send_private_message', // There is a problem with the data received by the scientia.js
//                                                    // server post to /<username>/Messaging/send.
//                'scientia_make_post',                  // A problem with the post to the server for an scientia domain
//                                                    // actionMakePost request.
//                'scientia_generate_secret',             // An error occurred whilst generating a secret for a user.
//                'scientia_delete_post',                // Data sent from the domus domain to the scientia domain
//                                                    // DeleteNewPost action does not validate.
//                'scientia_delete_post_callback',       // An scientia domain callback from a request to delete a new
//                                                    // post is erroring.
//                'domus_delete_post'                // Data sent from the client to the domus domain to request the
//                                                    // deletion of an post does not validate.
                'sortRequest_stream',                 // A problem with the stream in a sort request.
                'sortRequest_rhythm'                    // A problem with the sort filter Rhythm in a sort request.
            ];
            if (jQuery.inArray(type, type_options) === -1) {
                return false;
            }
            return true;
        },


        /**
         * Valid errors for the domus domain to pass back to the client
         * if there is an error in a request from the client.
         *
         * Reports if an error code is valid.
         * They all use the following naming convention.
         * <NameOfControllerAction>_<error_type>
         *
         * @param {string} error_code The error code to check.
         * @return {boolean}
         *
         * @refactor This needs refactoring to include the option to call an error code in object notation.
         *      Also should be moved into a seperate class that is only loaded by the domus domain and the client.
         */
        clientErrors : function (error_code) {

            var error_codes = [
                'GetKindred_not_loaded',            // The Kindred Data is not available to send to the client.
                'SortRequest_test',                 // The data sent to the domus domain from the client was invalid.
                'SortRequest_stream',               // A problem with the stream in a sort request.
                                                    // This error is forwarded from the filter domain after being
                                                    // generated by the Filter.getPosts process in the domus domain.
                'SortRequest_rhythm',                 // A problem with the sort filter Rhythm in a sort request.
                'SortRequest_private',              // A problem with fetching private posts.
                'SortRequest_moderation',           // A error whilst fetching moderation data for a sort request.
                                                    // Requires two paramaters in the error data:
                                                    //      ring_domain
                                                    //      ring_username
                'RecordFeatureUsed_test',           // The data sent to the domus domain from the client was invalid.
                'MakePost_test',                   // The data sent to the domus domain from the client was invalid.
                'MakePost_failed',                 // The make post process failed.
                'MakePost_delete_failed',          // The make post process was only partially successful
                                                    // - the attempt to tidy up and delete the created posts
                                                    // then failed.
                'Take_test',                        // The data sent to the domus domain from the client was invalid.
                'Take_failed',                      // A take request failed to process.
                'GetTakes_test',                    // The data sent to the domus domain from the client was invalid.
                'GetTakes_failed',                  // A request to fetch take data failed.
                'GetPost_test',                    // The data sent to the domus domain from the client was invalid.
                'GetPost_not_found',               // The post was not found.
                'GetPost_takes_failed',            // Failed to fetch the takes for an post.
                'GetPost_failed',                  // Failed to fetch the post.
                'GetSuggestions_test',              // The data sent to the domus domain from the client was invalid.
                'GetSuggestions_rhythm',              // A general error in the suggestion sub domain.
                'GetSuggestions_rhythm_not_found',    // The Rhythm used in a suggestion request has not been found
                                                    // or is erroring in the scientia domain.
                'TakeRing_test',                    // The data sent to the domus domain from the client was invalid.
                'TakeRing_password',                // The users ring password has not been found.
                'TakeRing_failed',                  // The ring server failed to take an post using a ring account.
                'GetRingTakeStatus_test',           // The data sent to the domus domain from the client was invalid.
                'GetRingTakeStatus_failed',         // The ring server failed to respond to a request to
                                                    // fetch the take status for an post by the ring member.
                'GetRingTakeStatus_password',       // The users ring password has not been found.
                'InfoRequest_test',                 // The data sent to the domus domain from the client was invalid.
                'DeletePost_test',                 // The data sent to the domus domain from the client was invalid.
                'DeletePost_stream',               // The post failed to delete from the stream or user domains
                'DeletePost_user',                 // The post failed to delete from the users domain.
                                                    // If the users domain is the stream domain then the post
                                                    // also failed to delete from the stream domain
                 'DomainSuggestions_test',          // The data sent to the domus domain from the client was invalid.
                 'DomainSuggestions_failed',        // A request to fetch domain suggestions failed.
                 'CheckDomainValid_test',           // The data sent to the domus domain from the client was invalid.
                 'CheckDomainValid_failed',         // A request to validate a domain failed.
                 'UsernameSuggestions_test',        // The data sent to the domus domain from the client was invalid.
                 'UsernameSuggestions_failed',      // A request to suggest usernames failed.
                 'CheckUsernameValid_test',         // The data sent to the domus domain from the client was invalid.
                 'CheckUsernameValid_failed'        // A request to validate a username failed.
            ];
            if (jQuery.inArray(error_code, error_codes) === -1) {
                return false;
            }
            return true;
        },

        /**
         * Valid errors for the domus domain to pass back to the filter domain
         *
         * Used if there is an error in a request from the filter domain.
         * Reports if an error code is valid.
         * They all use the following naming convention.
         * <NameOfControllerAction>_<error_type>
         *
         * @param {string} error_code The error code to check.
         *
         * @return {boolean}
         *
         * @refactor This needs refactoring to include the option to call an error code in object notation.
         *      Also should be moved into a seperate class that is only loaded by domus domain and filter domains.
         */
        filterErrors : function (error_code) {

            var error_codes = [
                'FilterReady_test',            // The data sent to the domus domain from the filter domain was invalid.
                'FilterReady_kindred',         // The filter domain is fine,
                                               // but the wait for the kindred data is erroring
                                               // is timing out before the kindred data is reporting itself as ready.
                'InitSort_kindred',            // An initSort has been requested but the kindred datas is not available.
                'GetPosts_test',              // The data sent to the domus domain from the filter domain was invalid.
                'GetPosts_failed',            // A request to get posts failed.
                                               // The filter domain converts this error into a SortRequest_stream
                                               // error.
                'GetPosts_moderation',        // The moderation data for some posts failed to return correctly.
                                               // The filter domain converts this error into a SortRequest_moderation
                                               // error.
                'GetPosts_private'            // The fetching of private posts failed.
                                               // The filter domain converts this error into a SortRequest_private
                                               // error.

            ];
            if (jQuery.inArray(error_code, error_codes) === -1) {
                return false;
            }
            return true;
        },

        /**
         * Valid errors for the domus domain to pass back to the kindred domain
         *
         * Used if there is an error in a request from the kindred domain.
         * Reports if an error code is valid.
         * They all use the following naming convention.
         * <NameOfControllerAction>_<error_type>
         *
         * @param {string} error_code The error code to check.
         *
         * @return {boolean}
         *
         * @refactor This needs refactoring to include the option to call an error code in object notation.
         *      Also should be moved into a seperate class that is only loaded by domus and filter domains.
         */
        kindredErrors : function (error_code) {

            var error_codes = [
                'KindredReady_test',         // The data sent to the domus domain from the kindred domain was invalid.
                'GetData_test',              // The data sent to the domus domain from the kindred domain was invalid.
                'GetData_failed',            // A request to fetch data for the kindred domain failed.
                'StoreKindredResults_test',  // The data sent to the domus domain from the kindred domain was invalid.
                'StoreKindredResults_failed' // A request to save kindred results has failed.
            ];
            if (jQuery.inArray(error_code, error_codes) === -1) {
                return false;
            }
            return true;
        },

        /**
         * Valid errors for the domus domain to pass back to the ring domain
         *
         * Used if there is an error in a request from the ring domain.
         * Reports if an error code is valid.
         * They all use the following naming convention.
         * <NameOfControllerAction>_<error_type>
         *
         * @param {string} error_code The error code to check.
         *
         * @return {boolean}
         *
         * @refactor This needs refactoring to include the option to call an error code in object notation.
         *      Also should be moved into a seperate class that is only loaded by domus and filter domains.
         */
        ringErrors : function (error_code) {

            var error_codes = [
                'RingReady_test',            // The data sent to the domus domain from the kindred domain was invalid.
                'RequestData_test',          // The data sent to the domus domain from the kindred domain was invalid.
                'RequestData_failed',        // Failed to retrieve the requested data for the ring domain.
                'StoreRingResults_test',     // The data sent to the domus domain from the kindred domain was invalid.
                'StoreRingResults_failed'    // Failed to store a rings results.
            ];
            if (jQuery.inArray(error_code, error_codes) === -1) {
                return false;
            }
            return true;
        },

        /**
         * Valid errors for the domus domain domain to pass back to the ring domain
         *
         * Used if there is an error in a request from the ring domain.
         * Reports if an error code is valid.
         * They all use the following naming convention.
         * <NameOfControllerAction>_<error_type>
         *
         * @param {string} error_code The error code to check.
         *
         * @return {boolean}
         *
         * @refactor This needs refactoring to include the option to call an error code in object notation.
         *      Also should be moved into a seperate class that is only loaded by domus and filter domains.
         */
        suggestionErrors : function (error_code) {

            var error_codes = [
                'SuggestionReady_test',      // The data sent to the domus domain from the kindred domain was invalid.
                'SuggestionReady_kindred',   // The filter domain is fine,
                                             // but the wait for the kindred data is erroring
                'FetchData_test',    // The data sent to the domus domain from the kindred domain was invalid.
                'FetchData_failed'   // Data requested by a suggestion rhythm failed to return.
            ];
            if (jQuery.inArray(error_code, error_codes) === -1) {
                return false;
            }
            return true;
        },

        /**
         * Valid errors for the domus domain to pass back to an scientia domain
         *
         * Used if there is an error in a request from the scientia domain.
         * Reports if an error code is valid.
         * They all use the following naming convention.
         * <NameOfControllerAction>_<error_type>
         *
         * @param {string} error_code The error code to check.
         *
         * @return {boolean}
         */
        scientiaErrors : function (error_code) {

            var error_codes = [
                'DomainReady_test',           // The data sent to the domus domain from the scientia domain was invalid.
                'GetPost_not_found'          // A requested post has not been found.
            ];
            if (jQuery.inArray(error_code, error_codes) === -1) {
                return false;
            }
            return true;
        },

        /**
         * Validates that a rhythm cat is valid.
         *
         * @param {string} cat The name of the category.
         * @param {boolean} [is_suggestion] Used to refine the test down to just suggestion categories.
         * @param {string} error_code The error code to check.
         *
         * @return {boolean}
         */
        rhythmCat : function (cat, is_suggestion, error_code) {

            var suggestion_categories = [
                'stream_suggestion',
                'stream_filter_suggestion',
                'user_stream_suggestion',
                'stream_ring_suggestion',
                'ring_suggestion',
                'user_suggestion',
                'meta_suggestion',
                'kindred suggestion'
            ];

            var other_cateogries = [
                'sort',
                'kindred',
                'ring'
            ];

            var found = false;
            if (jQuery.inArray(cat, suggestion_categories) >= 0) {
                found = true;
            }

            if (typeof is_suggestion !== 'boolean' || is_suggestion === false) {
                if (jQuery.inArray(cat, other_cateogries) >= 0) {
                    found = true;
                }
            }
            return found;
        },

        /**
         * Validates that a declined suggestion is valid
         *
         * @param {string} cat The name of the category of the suggestion rhythm that the suggestion came from.
         * @param {object} [stream] If the type of stream that has been declined is a stream
         *      then this is that stream.
         * @param {string} [stream.name] The name of the declined stream.
         * @param {string} [stream.username] The username of the declined stream.
         * @param {string} [stream.domain] The domain of the declined stream.
         * @param {object} [stream.version] The version of the declined stream.
         * @param {number|'latest'} [stream.version.major] The major version of the declined stream.
         * @param {number|'latest'} [stream.version.major] The minor version of the declined stream.
         * @param {number|'latest'} [stream.version.major] The patch version of the declined stream.
         * @param {number} [rhythm] If the type of stream that has been declined is a rhythm
         *      then this is that rhythm.
         * @param {string} [rhythm.name] The name of the declined rhythm.
         * @param {string} [rhythm.username] The username of the declined rhythm.
         * @param {string} [rhythm.domain] The domain of the declined rhythm.
         * @param {object} [rhythm.version] The version of the declined rhythm.
         * @param {number|'latest'} [rhythm.version.major] The major version of the declined rhythm.
         * @param {number|'latest'} [rhythm.version.major] The minor version of the declined rhythm.
         * @param {number|'latest'} [rhythm.version.major] The patch version of the declined rhythm.
         * @param {object} [user] If the type of stream that has been declined is a user
         *      then this is that user.
         * @param {string} [user.username] The username of the declined user.
         * @param {string} [user.domain] The domain of the declined user.
         *
         * @return {boolean}
         */
        declinedSuggestion : function (cat, stream, rhythm, user) {
            var valid = false;
            switch(cat) {
                case 'stream_suggestion':
                    valid = BabblingBrook.Models.streamName(stream, true);
                    break;

                case 'stream_filter_suggestion':
                    valid = BabblingBrook.Models.rhythmName(rhythm, true);
                    break;

                case 'user_stream_suggestion':
                    valid = BabblingBrook.Models.streamName(stream);
                    break;

                case 'stream_ring_suggestion':
                    valid = BabblingBrook.Models.user(user);
                    break;

                case 'ring_suggestion':
                    valid = BabblingBrook.Models.user(user);
                    break;

                case 'user_suggestion':
                    valid = BabblingBrook.Models.user(user);
                    break;

                case 'meta_suggestion':
                    valid = BabblingBrook.Models.rhythmName(rhythm, true);
                    break;

                case 'kindred suggestion':
                    valid = BabblingBrook.Models.rhythmName(rhythm, true);
                    break;

                default:
                    valid = false;
            }
            return valid;
        },

        /**
         * Validates a full stream name.
         *
         * @param {object} [stream] If the type of stream that has been declined is a stream
         *      then this is that stream.
         * @param {string} [stream.name] The name of the stream.
         * @param {string} [stream.username] The username of the stream.
         * @param {string} [stream.domain] The domain of the stream.
         * @param {object} [decline_data.stream.version] The version of the stream.
         * @param {number|'latest'} [stream.version.major] The major version of the stream.
         * @param {number|'latest'} [stream.version.major] The minor version of the stream.
         * @param {number|'latest'} [stream.version.major] The patch version of the stream.
         * @param {boolean} [version_is_object=false] Is the version represent by an object (or a string).
         *
         * @return {boolean}
         */
        streamName : function (stream, version_is_object) {
            var version_type = 'version-object';
            if (typeof version_is_object === 'undefined' || version_is_object === false) {
                version_type = 'version';
            }

            var valid = BabblingBrook.Test.isA(
                [
                    [stream.domain, 'domain'],
                    [stream.username, 'username'],
                    [stream.name, 'resource-name'],
                    [stream.version, version_type],
                ]
            );
            return valid;
        },

        /**
         * Validates a full user object.
         *
         * @param {object} user The user object.
         * @param {string} user.username The username of the user.
         * @param {string} user.domain The domain of the user.
         *
         * @return {boolean}
         */
        user : function (user) {
            var valid = BabblingBrook.Test.isA(
                [
                    [user.domain, 'domain'],
                    [user.username, 'username']
                ]
            );
            return valid;
        },

        /**
         * Validates a request to search for streams.
         *
         * @param {object} request_data The search request.
         * @param {string} request_data.domain_filter A full or partial domain to wither streams with.
         * @param {string} request_data.username_filter A full or partial username to filter streams with.
         * @param {string} request_data.name_filter a full or partial name to filter streams with.
         * @param {string} request_data.version_filter a partial version number in the form majo/minor/patch
         *      to filter results. Can inlude a partial version such as 2/3/patch will fetch all patch versions
         *      in 2/3.
         * @param {string} request_data.status The published status of the streams.
         * @param {string} request_data.kind The kind of stream to search for. Can be empty.
         * @param {string} request_data.include_versions Should different versions of the same rhythm be included.
         * @param {string} request_data.page The page of results to fetch.
         * @param {string} request_data.row_qty The quantity of rows per page.
         *
         * @returns {boolean}
         */
        searchStreamRequest : function (request_data) {
            var valid = BabblingBrook.Test.isA(
                [
                    [request_data.domain_filter,       'string'],
                    [request_data.username_filter,     'string'],
                    [request_data.name_filter,         'string'],
                    [request_data.version_filter,      'string'],
                    [request_data.status,              'string'],
                    [request_data.kind,                'string'],
                    [request_data.include_versions,    'boolean'],
                  //  [request_data.date_created,        'string'],
                    [request_data.page,                'uint'],
                    [request_data.row_qty,             'uint'],
                    [request_data.sort_order,          'object'],
                    [request_data.sort_priority,       'array'],
                    [request_data.exact_match,         'object']
                ]
            );
            return valid;
        },

        /**
         * Validates a request to search for rhythms.
         *
         * @param {object} request_data The search request.
         * @param {string} request_data.domain_filter A full or partial domain to wither rhythms with.
         * @param {string} request_data.username_filter A full or partial username to filter rhythms with.
         * @param {string} request_data.name_filter a full or partial name to filter rhytyhms with.
         * @param {string} request_data.version_filter a partial version number in th eform majo/minor/patch
         *      to filter results. Can inlude a partial version such as 2/3/patch will fetch all patch versions
         *      in 2/3.
         * @param {string} request_data.status The published status of the rhythm.
         * @param {string} request_data.cat_type The category type of the rhythm to search for. Can be empty.
         * @param {string} request_data.include_versions Should different versions of the same rhythm be included.
         * @param {string} request_data.page The page of results to fetch.
         * @param {string} request_data.row_qty The quantity of rows per page.
         *
         * @returns {boolean}
         */
        searchRhythmRequest : function (request_data) {
            var valid = BabblingBrook.Test.isA(
                [
                    [request_data.domain_filter,       'string'],
                    [request_data.username_filter,     'string'],
                    [request_data.name_filter,         'string'],
                    [request_data.version_filter,      'string'],
                    [request_data.status,              'string'],
                    [request_data.cat_type,            'string'],
                    [request_data.include_versions,    'boolean'],
                  //  [request_data.date_created,        'string'],
                    [request_data.page,                'uint'],
                    [request_data.row_qty,             'uint'],
                    [request_data.sort_order,          'object'],
                    [request_data.sort_priority,       'array'],
                    [request_data.exact_match,         'object']
                ]
            );
            return valid;
        },

        /**
         * Validates a request to search for users.
         *
         * @param {object} request_data The search request.
         * @param {string} request_data.domain_filter A full or partial domain to wither rhythms with.
         * @param {string} request_data.username_filter A full or partial username to filter rhythms with.
         * @param {string} request_data.user_type The type of user to search for.
         *      Valid types are 'ring', 'user' and 'all'
         * @param {string} request_data.page The page of results to fetch.
         * @param {string} request_data.row_qty The quantity of rows per page.
         * @param {string} request_data.sort_order Object index by columns indicating which way they should be sorted.
         * @param {string} request_data.sort_order.domain Valid values are 'ascending' and 'descending'.
         * @param {string} request_data.sort_order.username Valid values are 'ascending' and 'descending'.
         * @param {string} request_data.sort_order.user_type Valid values are 'ascending' and 'descending'.
         * @param {string} request_data.sort_order.ring_ban Valid values are 'ascending' and 'descending'.
         * @param {string} request_data.sort_priority The order in which the sort_order properties should be applied.
         * @param {string} request_data.exact_match Should the results be an exact match for the filters in this object.
         * @param {string} request_data.exact_match.domain Should the domain filter be an exact match.
         * @param {string} request_data.exact_match.username Should the username filter be an exact match.
         * @param {string} request_data.exact_match.name Should the name filter be an exact match.
         * @param {string} request_data.ring_username if not an empty string then it restricts results to members
         *      in this ring.
         * @param {string} request_data.ring_domain if not an empty string then it restricts results to members
         *      in this ring.
         * @param {string} request_data.ring_ban_filter If search for members of a ring, restricts restuls
         *      to just those who have been 'all', 'banned' or 'members'.
         * @param {string} request_data.only_joinable_rings If searching for rings, restricts results to those a user
         *      can request to join. ('public' at the moment, later will include 'by_request')
         *
         * @returns {boolean}
         */
        searchUserRequest : function (request_data) {
            var valid = BabblingBrook.Test.isA(
                [
                    [request_data.domain_filter,       'string'],
                    [request_data.username_filter,     'string'],
                    [request_data.user_type,           'string'],
                    [request_data.page,                'uint'],
                    [request_data.row_qty,             'uint'],
                    [request_data.sort_order,          'object'],
                    [request_data.sort_priority,       'array'],
                    [request_data.exact_match,         'object'],
                    [request_data.ring_username,        'string'],
                    [request_data.ring_domain,          'string'],
                    [request_data.ring_ban_filter,      'string'],
                    [request_data.only_joinable_rings, 'boolean'],
                    [request_data.users_to_vet_for_ring, 'user|boolean']
                ]
            );
            return valid;
        }

    };

}());