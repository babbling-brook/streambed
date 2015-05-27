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
 * @namespace Collection of methods dealing with a users profile page.
 * @package JS_Client
 */
BabblingBrook.Client.Page.User.Profile = (function () {
    'use strict';
    /**
     * @type {string} The domain for the user in the profile.
     */
    var domain;

    /**
     * @type {string} The username for the profile.
     */
    var username;

    /**
     * @type {string} The default image for profiles.
     */
    var profile_src;

    /**
     * @type {number} The quantity of results to show per page.
     */
    var page_qty = 20;

    /**
     * @type {number} The quantity of global takes to load at a time.
     */
    var global_take_qty = 1000;

    /**
     * All global takes loaded to date.
     * @type {array}
     */
    var global_takes = [];

    /**
     * @type {array} All global takes that have a kindred relationship.
     */
    var kindred_tags_summed = [];

    /**
     * @type {array} A global sum of the take value for each post.
     */
    var global_tags_summed = [];

    /**
     * @type {array} An array of post tyoe fields that should be attatched to new posts that rate users.
     */
    var new_field;

    /**
     * @type {string} The url for the user in this profile.
     */
    var user_url;

    /**
     * @type {string} The json url for this profiles user profile data.
     */
    var profile_url;

    /**
     *
     * @type {array} users_tags Contains the tags that this user has made.
     * @type {string} users_tags[].stream_domain The stream domain for this tag
     * @type {string} users_tags[].stream_username The stream username for this tag
     * @type {string} users_tags[].stream_name The stream name for this tag
     * @type {string} users_tags[].stream_version The stream version for this tag
     */
    var users_tags;

    /**
     * An index of the the user_tag urls. Used to facilitate rapid lookup.
     *
     * @type user_tag_urls
     */
    var user_tag_urls = {};

    var global_takes;

    /**
     * A collection of callback funcitons to run when the user_tags have loaded.
     * @type Array
     */
    var user_tag_callbacks = [];

    var deferred_user_tags = jQuery.Deferred();


    /**
     * Error callback for a request to the domus domain for a users profile data.
     * @param {string} error_code
     * @param {object} error_data
     */
    var profileErrorCallback = function (error_code, error_data) {    // Error.
        console.trace();
        console.error('info request error : ' + error_code);
        console.error(error_data);
    };

    /**
     * Receives public profile data about a user from their domus domain.
     *
     * @param {object} data
     * @param {string} data.about Text description about this user.
     * @param {string} data.real_name This users real name.
     * @param {String|Boolan} data.ring_membership_type If this profile is a ring then this is the membership_type
     *                                                  for that ring. Otherwise false.
     *
     * @return {void}
     */
    var userProfileCallback = function (data) {
        var error_message = 'data returned from profile call to /profilejson data does not validate.';
        BabblingBrook.Test.isA(
            [
                [data.about, 'string|null'],
                [data.real_name, 'string|null']
            ],
            error_message
        );
        BabblingBrook.Models.ringMembershipType(data.ring_membership_type, error_message);

        if (typeof data.real_name === 'undefined') {
            //jQuery('#profile_error').text('Profile not found for this user.');
            // show a user friendly error message here.
            return;
        }

        if (data.real_name === null) {
            data.real_name = 'This user has elected to remain anonymous.';
        }
        if (data.about === null) {
            data.about = 'This user has not entered any details.';
        }

        jQuery('#name_content')
            .html(data.real_name)
            .removeClass('block-loading');

        jQuery('#about_content')
            .text(data.about)
            .removeClass('block-loading');;

        var meta_url = 'http://' + data.meta_url;
        jQuery('#conversation')
            .attr('href', meta_url)
            .removeClass('text-loading');

        var a_member = false;
        var an_admin = false;
        jQuery.each(BabblingBrook.Client.User.Rings, function (i, ring) {
            if (ring.domain === domain && ring.name === username && ring.member === '1') {
                a_member = true;
            }
            if (ring.domain === domain && ring.name === username && ring.admin === '1') {
                an_admin = true;
            }
        });

        if (an_admin === true && window.location.host === domain) {
            jQuery('#edit_ring_profile').removeClass('hide');
        }

        // If the ring is local and public then show the join link.
        if (a_member === false && data.ring_membership_type === 'public' && window.location.host === domain) {
            jQuery('#join_ring').removeClass('hide');
            jQuery('#join_ring a').click(function () {
                BabblingBrook.Client.Core.Interact.postAMessage(
                    {
                        domain : window.location.hostname,
                        username : username
                    },
                    'RingJoin',
                    function (response_data) {
                        if (response_data.success === true) {
                            BabblingBrook.Client.Page.User.Profile.onJoindRingHook(username);
                            BabblingBrook.Client.User.Rings.push(response_data.ring_client_data);
                            BabblingBrook.Client.Core.Ajaxurl.redirect('/' + username + '/ring/members');
                        } else {
                            jQuery('#join_ring').html(response_data.error);
                        }
                    }
                );
                return false;
            });
        }

        if (a_member === false && data.ring_membership_type === 'request') {
            jQuery('#request_ring_membership').removeClass('hide');
            jQuery('#request_ring_membership a').click(function () {
                jQuery('#request_ring_membership a').addClass('text-loading');
                BabblingBrook.Client.Core.Interact.postAMessage(
                    {
                        domain : window.location.hostname,
                        username : username
                    },
                    'RequestRingMembership',
                    function (response_data) {
                        if (response_data.success === true) {
                            jQuery('#request_ring_membership a').removeClass('text-loading');
                            var jq_message = jQuery('#on_request_ring_membership_success_template').clone();
                            jQuery('.ring-name', jq_message).text(username + '@' + domain);
                            BabblingBrook.Client.Component.Messages.addMessage({
                                type : 'notice',
                                message : jq_message.text()
                            });
                        } else {
                            onRequestRingMembershipError();
                        }
                    },
                    onRequestRingMembershipError
                );
                return false;
            });
        }
    };

    /**
     * Handles errors when the user requested ring membership.
     *
     * @returns {undefined}
     */
    var onRequestRingMembershipError = function () {
        jQuery('#request_ring_membership a')
            .addClass('error')
            .removeClass('text-loading');
        var jq_message = jQuery('#on_request_ring_membership_error_template').clone();
        jQuery('.ring-name', jq_message).text(username + '@' + domain);
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : jq_message.text()
        });
    };

    /**
     * Show the profile image
     */
    var showProfileImage = function () {
        // Load the profile image


        jQuery('#profile_image').attr(
            'src',
            '/images/user/' + BabblingBrook.Client.User.domain + '/' +
                BabblingBrook.Client.User.username + '/profile/small/profile.jpg'
        );


        jQuery('#profile_image').error(function () {
            jQuery('#profile_image').attr('src', '/images/default_user_large.png');
        }).attr('src', profile_src);
    };

    /**
     * Get and then display the profile data for this profile.
     */
    var getProfileData = function () {
        var data = {
            url : profile_url,
            data : {},
            https : false
        };
        BabblingBrook.Client.Core.Interact.postAMessage(data, 'InfoRequest', userProfileCallback, profileErrorCallback);
    };


    /**
     * Show the logged on users kindred score with this user.
     */
    var showKindredScore = function () {
        // Fetch kindred score and show edit link.
        var jq_kindred_score = jQuery('#kindred_score');
        if (BabblingBrook.Client.User.domain === domain && BabblingBrook.Client.User.username === username) {
            jq_kindred_score
                .html('&#8734;')
                .attr('title', 'Hopefully you know this person quite well.')
                .removeClass('block-loading something');
        } else {
            // Wait for kindred data to return before displaying kindred score with this user.
            BabblingBrook.Client.Core.Loaded.onKindredLoaded(function () {
                if (typeof BabblingBrook.Client.User.kindred[domain + '/' + username] === 'undefined') {
                    jq_kindred_score
                        .attr('title', 'You do not have a relationship with this user')
                        .html('0')
                        .removeClass('block-loading something');
                } else {
                    jq_kindred_score
                        .html(BabblingBrook.Client.User.kindred[domain + '/' + username])
                        .removeClass('block-loading something');
                }
            });
        }
    };

    /**
     * Show the profile edit link for this profile if the owner or a ring owner.
     */
    var showEditLink = function () {
        // Edit your profile.
        if (BabblingBrook.Client.User.username === username && BabblingBrook.Client.User.domain === domain) {
            jQuery('#editprofile').removeClass('hide');
        }
        // Also show the edit link if the user is an admin of this group profile.
        jQuery.each(BabblingBrook.Client.User.Rings, function (i, ring) {
            if (ring.name === username && ring.domain === domain && ring.admin === '1') {
                jQuery('#editprofile').removeClass('hide');
            }
        });
    };

    /**
     * Show the ring membership link if the logged on user is a member of this profile of a ring.
     */
    var showMembersLink = function () {
        jQuery.each(BabblingBrook.Client.User.Rings, function (i, ring) {
            if (ring.domain === domain && ring.name === username && ring.member === '1') {
                jQuery('#ring_members').removeClass('hide');
            }
        });
    };

    /**
     * Toggles the display of the message that tells the user that they have not made any tags.
     *
     * @returns {undefined}
     */
    var toggleNoYourTagsMessage = function () {
        var jq_your_tags = jQuery('#users_tags_for_profile .user-tag');
        if (jq_your_tags.length === 0) {
            jQuery('#users_tags_for_profile_none').removeClass('hide');
        } else {

            jQuery('#users_tags_for_profile_none').addClass('hide');
        }
    };

    /**
     * Displays a tag object
     *
     * @param {object} stream The stream that the tag is made from.
     * @param {object} jq_tag_parent The parent element to append the tag to.
     * @param {object} [post] The post made by the logged on user if they have tagged this.
     * @param {integer} [score] An optional score to display on the tag.
     * @param {string} [type] The type of tag. Should be undefined or 'kindred'.
     *
     * @returns {undefined}
     */
    var displayTag = function (stream, jq_tag_parent, post, score, type) {
        var jq_tag = jQuery('#tag_template>div').clone();
        var stream_url = stream.domain + '/' + stream.username + '/stream/' + stream.name + '/' + stream.version;
        jq_tag.attr('data-tag-stream-url', stream_url);
        if (typeof post === 'undefined' || typeof post.post_id === 'undefined') {
            jQuery('.tag-name-no-link', jq_tag)
                .attr('title', stream_url)
                .text(stream.name);
        } else {
            jQuery('.tag-name', jq_tag)
                .attr('title', stream_url + '\nClick to view comments on this user tag.')
                .text(stream.name)
                .removeClass('hide')
                .attr('href', '/postwithtree/' + post.domain + '/' + post.post_id);
            jQuery('.tag-name-no-link', jq_tag).remove();
        }

        if (typeof score !== 'undefined') {
            var title = jQuery('.tag-score', jq_tag).attr('title')
                .replace('!username!', username)
                .replace('!qty!', score);
            jQuery('.tag-score', jq_tag)
                .removeClass('hide')
                .attr('title', title)
                .text(score);

            if (typeof type === 'string' && type === 'kindred') {
                var extra_title = jQuery('#kindred_tag_extra_title_template').text().trim();
                jQuery('.tag-score', jq_tag).attr('title', title + extra_title);
            }
        }

        jq_tag_parent.append(jq_tag);

        var new_title = jQuery('.tag-icon', jq_tag).attr('title').replace('this user', username);
        jQuery('.tag-icon', jq_tag).attr('title', new_title);
        new_title = jQuery('.untag-icon', jq_tag).attr('title').replace('this user', username);
        jQuery('.untag-icon', jq_tag).attr('title', new_title);
        if (typeof user_tag_urls[stream_url] === 'undefined') {
            jQuery('.tag-icon', jq_tag)
                .removeClass('hide')
                .click(onTagUser.bind(null, stream));
        } else {
            jQuery('.untag-icon', jq_tag)
                .removeClass('hide')
                .click(onUntagUser.bind(null, stream, post));
        }
        toggleNoYourTagsMessage();
    };

    /**
     * Error callback for when a request to fetch a user tag post fails.
     *
     * @param {type} error_code
     * @param {type} error_data
     *
     * @returns {undefined}
     */
    var onGetTagPostForUserError = function (error_code, error_data) {
        console.error('info request error ' + error_code );
    };

    /**
     * Updates the display of all versions of a tag.
     *
     * @param {object} stream The stream this user has been taged with.
     * @param {object} post The post that represents the user in this tag stream.
     *
     * @returns {undefined}
     */
    var onUserTagged = function (stream, post) {
        // Display a new tag in the 'your tags' section.
        var jq_your_tags = jQuery('#users_tags_for_profile');
        var display_post = {
            post_id : post.post_id,
            take_value : 1
        }
        displayTag(stream, jq_your_tags, display_post);

        // Update other tags
        var stream_url = stream.domain + '/' + stream.username + '/stream/' + stream.name + '/' + stream.version;
        // Selector rows
        var jq_selector_tags = jQuery('.selector-action-tag img[data-tag-stream-url=\'' + stream_url + '\']');
        jq_selector_tags
            .addClass('hide')
            .parent().addClass('hide')
            .parent().parent().removeClass('block-loading');
        var jq_selector_untags = jQuery('.selector-action-untag img[data-tag-stream-url=\'' + stream_url + '\']');
        jq_selector_untags
            .removeClass('hide')
            .parent().removeClass('hide')
            .parent().parent().removeClass('block-loading');

        // All tags
        var jq_all_tags = jQuery('.user-tag[data-tag-stream-url=\'' + stream_url + '\']');
        jQuery('.tag-icon', jq_all_tags)
            .addClass('hide')
            .unbind('click');
        jQuery('.untag-icon', jq_all_tags)
            .removeClass('hide')
            .click(onUntagUser.bind(null, stream, post));

        // If the post is included the ensure that the link is visible.
        if (typeof post === 'undefined' || typeof post.post_id === 'undefined') {
            jQuery('.tag-name', jq_all_tags)
                .attr('title', stream_url + ' Click to view comments on this user tag.')
                .text(stream.name)
                .removeClass('hide')
                .attr('href', '/postwithtree/' + post.domain + '/' + post.post_id);
            jQuery('.tag-name-no-link', jq_all_tags).remove();
        }

        toggleNoYourTagsMessage();
        BabblingBrook.Client.Page.User.Profile.onTaggedHook(stream);
    };

    /**
     * Sends a take request to the domus domain.
     *
     * Can be used to both take and intake a tag
     *
     * @param {object} stream The stream to tag the user with.
     * @param {object} post The post that represents the user in this tag stream.
     * @param {function} onTaken The callback to run once the post has been taken.
     * @param {integer} take_value Valid values are 1 and 0.
     *
     * @returns {undefined}
     */
    var sendTakeRequest = function (stream, post, onTaken, take_value) {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                post_id : post.post_id,
                field_id : 2,
                stream_domain : stream.domain,
                stream_username : stream.username,
                stream_name : stream.name,
                stream_version : stream.version,
                value : take_value,
                value_type : 'updown',
                mode : 'new'//,
//                taken_user : {
//                    domain : domain,
//                    username : username
//                }
            },
            'Take',
            onTaken,
            function () {
                console.error('Error taking a new tag post.');
                console.log(stream);
                console.log(post);
            }
        );
    }

    /**
     * Tags a user by taking the post that represents the user in that tag stream.
     *
     * @param {object} stream The stream to tag the user with.
     * @param {object} post The post that represents the user in this tag stream.
     *
     * @returns {undefined}
     */
    var tagAUserPost = function (stream, post) {
        sendTakeRequest(stream, post, onUserTagged.bind(null, stream, post), 1)
    };

    /**
     * A tag post for a user has been made. Now tag the user by taking it.
     *
     * @param {object} stream The stream to tag the user with.
     * @param {object} post The post that has just been made and represents the tag.
     *
     * @return {undefined}
     */
    var onTagPostForUserMade = function (stream, post) {
        var tag_post = {
            post_id : post.post_id,
            domain : post.domain,
            take_value : 1
        }
        tagAUserPost(stream, tag_post);
    };

    /**
     * Success callback for fetching a users tag post when the logged in user is attempting to tag them.
     *
     * Creates the post if it doesn't exist.
     *
     * @param {object} stream The stream to tag the user with.
     * @param {object} post_data The post that represents the tag.
     *
     * @returns {undefined}
     */
    var onTagUserNowTagPostFetched = function (stream, post_data) {
        if (post_data.post === false) {
            var make_post = new BabblingBrook.Client.Component.MakePost(onTagPostForUserMade.bind(null, stream));
            make_post.setupHiddenPost(stream, new_field);
        } else {
            var tag_post = {
                post_id : post_data.post.post_id,
                domain : post_data.post.domain,
                take_value : 1
            }
            tagAUserPost(stream, tag_post);
        }
    };

    /**
     * Attempts to fetch a tag for a user that already exists.
     *
     * @param {object} stream The stream to tag the user with.
     * @param {function} onTagFetched A callback for when the tag has been fetched.
     *      Passed two paramterd.
     *      stream The stream object passe dhere.
     *      post_data The post data returned from the server.
     *
     * @returns {undefined}
     */
    var tryToFetchExistingTagForUser = function(stream, onTagFetched) {
        // First see if there is an already an instance of an post for this user in this stream.
        var post_user_url = BabblingBrook.Library.makeStreamUrl(stream, 'postuser');
        var data = {
            url : post_user_url,
            data : {
                username : username,
                domain : domain
            },
            https : false
        };
        BabblingBrook.Client.Core.Interact.postAMessage(
            data,
            'InfoRequest',
            onTagFetched.bind(null, stream),
            onGetTagPostForUserError
        );
    };

    /**
     * Tags a user with a stream and updates all displayed instances of the tag.
     *
     * @param {object} stream The stream to tag the user with.
     *
     * @returns {undefined}
     */
    var onTagUser = function (stream) {
        var stream_tag = {
            stream_domain : stream.domain,
            stream_username : stream.username,
            stream_name : stream.name,
            stream_version : stream.version,
        }
        users_tags.push(stream_tag);
        tryToFetchExistingTagForUser(stream, onTagUserNowTagPostFetched);
    };

    /**
     * Callback for after a user has been untagged. Updates all displays of the tag
     *
     * @param {object} stream The stream to tag the user with.
     * @param {object} post The post that is used to represent the tag.
     *
     * @returns {undefined}
     */
    var onUntagged = function (stream, post) {
        var stream_url = stream.domain + '/' + stream.username + '/stream/' + stream.name + '/' + stream.version;
        // Selector rows
        var jq_selector_tags = jQuery('.selector-action-tag img[data-tag-stream-url=\'' + stream_url + '\']');
        jq_selector_tags
            .removeClass('hide')
            .parent().removeClass('hide')
            .parent().parent().removeClass('block-loading');
        var jq_selector_untags = jQuery('.selector-action-untag img[data-tag-stream-url=\'' + stream_url + '\']');
        jq_selector_untags
            .addClass('hide')
            .parent().addClass('hide')
            .parent().parent().removeClass('block-loading');

        // Full tags
        var jq_all_tags = jQuery('.user-tag[data-tag-stream-url=\'' + stream_url + '\']');
        jQuery('.tag-icon', jq_all_tags)
            .removeClass('hide')
            .click(onTagUser.bind(null, stream));
        jQuery('.untag-icon', jq_all_tags)
            .addClass('hide')
            .unbind('click');

        // remove from the list of users tags
        var jq_your_tags = jQuery('#users_tags_for_profile .user-tag');
        jQuery.each(jq_your_tags, function (index) {
            var tag_stream_url = jQuery(this).attr('data-tag-stream-url');
            if (tag_stream_url === stream_url) {
                jQuery(this).remove();
            }
        });
        for (var i=0; i< users_tags.length; i++) {
            var tag_stream_url = users_tags[i].stream_domain + '/' + users_tags[i].stream_username
                + '/stream/' + users_tags[i].stream_name + '/' + users_tags[i].stream_version;
            if (tag_stream_url === stream_url) {
                users_tags.splice(i, 1);
            }
        }
        jQuery.each(user_tag_urls, function (url, value) {
            if (stream_url === url) {
                delete user_tag_urls[url];
                return false;   // Exit the .each
            }
        });


        toggleNoYourTagsMessage();
    };

    /**
     * Untags a user with a stream and updates all displayed instances of the tag.
     *
     * @param {object} stream The stream to tag the user with.
     * @param {object} post_data The post that represents the tag.
     *
     * @returns {undefined}
     */
    var onUntagUserNowTagPostFetched = function (stream, post_data) {
        if (post_data.post === false) {
            console.log(stream);
            console.log(post);
            throw 'A user take has failed to untag a user. Could not retrieve the take.';
        }

        var tag_post = {
            post_id : post_data.post.post_id,
            domain : post_data.post.domain,
            take_value : 0
        };
        sendTakeRequest(stream, tag_post, onUntagged.bind(null, stream, tag_post), 0);
    };

    /**
     * Untags a user
     *
     * @param {object} stream The stream to tag the user with.
     * @param {object} post The post that represents the tag.
     *
     * @returns {undefined}
     */
    var onUntagUser = function (stream, post) {
        var tag_post = {
            post_id : post.post_id,
            take_value : 0
        };
        sendTakeRequest(stream, tag_post, onUntagged.bind(null, stream, tag_post), 0);
    };

    /**
     * Callback for when an action is clicked in a tag search selector.
     *
     * @param {string} button type The type of button being displayed. Valid values are 'tag' and 'untag'.
     * @param {object} jq_row JQuery locator for the row that is being displayed.
     * @param {object} row The row of data that was displaye din the search.
     *
     * @return void
     */
    var onSearchedTagAction = function (button_type, event, jq_row, row) {
        event.preventDefault();
        jq_row.addClass('block-loading');
        var stream = {
            name : row.name,
            domain : row.domain,
            username : row.username,
            version : row.version
        };
        var stream_url = stream.domain + '/' + stream.username + '/stream/' + stream.name + '/' + stream.version;
        jQuery('.action img', jq_row).attr('data-tag-stream-url', stream_url);
        if (button_type === 'tag') {
            onTagUser(stream);

        } else {
            tryToFetchExistingTagForUser(stream, onUntagUserNowTagPostFetched);
        }
        // Need to update all instances of this tag to the new state and remove if in 'your tags' and
        // it has been deleted.

    };

    /**
     * Called when the search table has been displayed.
     *
     * @returns void
     */
    var onSearchForTagSuggestionsReady = function () {
        jQuery('#search_tags').removeClass('block-loading');
    };

    /**
    * Checks if a user has used a tag or not.
     *
     * @param {string} button type The type of button being displayed. Valid values are 'tag' and 'untag'.
     * @param {object} jq_row JQuery locator for the row that is being displayed.
     * @param {object} row The row of data that was displaye din the search.
     *
     * @returns {boolean} Has the tag been used or not.
     */
    var onReadyToDisplayTagSearchRow = function (button_type, jq_row, row) {
        // Wait for the user tags to load so we now whether to show tag or untag links.
        var found = false;
        jQuery.each(users_tags, function (i, tag) {
            if (row.domain === tag.stream_domain
                && row.username === tag.stream_username
                && row.name === tag.stream_name
                && row.version === tag.stream_version
            ) {
                found = true;
                return false;   // exit the .each.
            }
            return true;        // continue the .each.
        });
        if (button_type === 'tag' && found === false) {
            jQuery('.tag-icon', jq_row).removeClass('hide');
            jQuery('.untag-icon', jq_row).parent().addClass('hide');
        } else if (button_type === 'untag' && found === true) {
            jQuery('.untag-icon', jq_row).removeClass('hide');
            jQuery('.tag-icon', jq_row).parent().addClass('hide');
        }
    };

    /**
     * Recieves a row of search row data ready for display, but have to wait for the user_tags to load first.
     *
     * @param {string} button type The type of button being displayed. Valid values are 'tag' and 'untag'.
     * @param {object} jq_row JQuery locator for the row that is being displayed.
     * @param {object} row The row of data that was displaye din the search.
     *
     * @returns {boolean} Has the tag been used or not.
     */
    var onSearchRowFetched = function (button_type, jq_row, row) {
        deferred_user_tags.done(onReadyToDisplayTagSearchRow.bind(null, button_type, jq_row, row));
    };

    /**
     * Click events for when the user is searching for rating types.
     *
     * @returns void
     */
    var searchForTagSuggestions = function () {
        jQuery('#search_tags_on, #search_tags_hint').click(function () {
            jQuery('#search_tags_list').html('');
            jQuery('#search_tags_off').removeClass('hide');
            jQuery('#search_tags_on').addClass('hide');
            jQuery('#search_tags').addClass('content-block-3 block-loading');
            // Appends to a temporary parent to get the outer html.
            var tag_html = jQuery('<div>')
                .append(jQuery('#tag_template .tag-icon').clone())
                .html()
                .replace('this user', username);
            var untag_html = jQuery('<div>')
                .append(jQuery('#tag_template .untag-icon').clone())
                .html()
                .replace('this user', username);
            var actions = [
            {
                name : tag_html,
                class : 'tag',
                onClick : onSearchedTagAction.bind(null, 'tag'),
                onReady : onSearchRowFetched.bind(null, 'tag')
            },{
                name : untag_html,
                class : 'untag',
                onClick : onSearchedTagAction.bind(null, 'untag'),
                onReady : onSearchRowFetched.bind(null, 'untag')
            },
            ];
            var jq_list = jQuery('#search_tags_list');
            var search_table = new BabblingBrook.Client.Component.Selector(
                'stream',
                'search_rate',
                jq_list,
                actions,
                {
                    show_fields : {
                        version : false,
                        stream_kind : false
                    },
                    initial_values : {
                        stream_kind : 'user'
                    },
                    onReady : onSearchForTagSuggestionsReady,
                    loading_selector : '#search_tags',
                    additional_selector_class : 'selector-2'
                }

            );

        });
        jQuery('#search_tags_off').click(function () {
            jQuery('#search_tags_list').slideUp(250, function () {
                jQuery('#search_tags_off').addClass('hide');
                jQuery('#search_tags_on').removeClass('hide');
                jQuery('#search_tags_list')
                    .empty()
                    .show();
                jQuery('#search_tags').removeClass('content-block-3 block-loading');
            });
        });
    };

    /**
     * Removes tags in the users_tags from a list that is passed in.
     *
     * @param {array} tags A list of tag stream name objects.
     *
     * @returns {array} A list of tag stream name objects.
     */
    var removeTagsAllreadyUsed = function (tags) {
        var filtered_tags = jQuery.grep(tags, function (popular_tag) {
            var popular_tag_url = popular_tag.stream_domain + '/' + popular_tag.stream_username + '/stream/' +
                popular_tag.stream_name + '/' + popular_tag.stream_version;
            var found = false;
            jQuery.each(users_tags, function (i, tag) {
                var tag_url = tag.stream_domain + '/' + tag.stream_username +
                    '/stream/' + tag.stream_name + '/' + tag.stream_version;
                if (popular_tag_url === tag_url) {
                    found = true;
                    return false;   // exit .each.
                } else {
                    return true;    // continue .each.
                }
            });
            if (found === true) {
                return false;   // Do not include this in filter_popular
            } else {
                return true;    // Do include this in filter_popular
            }
        });
        return filtered_tags;
    };

    /**
     * Callback for setting up popular rating methods by the logged on user.
     *
     * @param {object[]} popular_tags
     * @param {string} popular_tags.stream_domain The stream domain name
     * @param {string} popular_tags.stream_name The stream name
     * @param {string} popular_tags.stream_username The stream username
     * @param {string} popular_tags.stream_version The stream version string
     *
     * @return void
     */
    var onPersonalPopularTagsFetched = function (popular_tags) {
        deferred_user_tags.done(function () {
            jQuery('#users_popular_tags').removeClass('block-loading');
            jQuery.each(popular_tags, function (i, row) {
                BabblingBrook.Test.isA(
                    [
                        [row.stream_domain, 'domain'],
                        [row.stream_name, 'resource-name'],
                        [row.stream_username, 'username'],
                        [row.stream_version, 'version'],
                    ],
                    'data returned from profile call to /popularuserstreams popular_tags does not validate.'
                );
            });

            var popular_id = 'users_popular_tags_list';
            jQuery('#' + popular_id).html('');



            var filterd_tags = removeTagsAllreadyUsed(popular_tags);

            if (filterd_tags.length < 1) {
                if (jQuery('#users_tags_for_profile .user-tag').length === 0) {
                    jQuery('#users_popular_tags_none').removeClass('hide');
                } else {
                    jQuery('#users_popular_tags_used').removeClass('hide');
                }
            } else {
                jQuery('#users_popular_tags_none').addClass('hide');
                jQuery('#users_popular_tags_used').addClass('hide');
                displayTags(filterd_tags, popular_id);
            }
        });
    };

    /**
     * Error callback for a request to set up popular rating methods by the logged on user.
     * @param {string} error_code
     * @param {object} error_data
     */
    var onPersonalPopularTagsFetchedError = function (error_code, error_data) {
        console.error('info request error');
    };

    /**
     * Setup popular rating methods by the logged on user.
     */
    var setupPersonalPopularTags = function () {
        jQuery('#users_popular_tags_off').click(function () {
            jQuery(this)
                .addClass('hide')
                .parent().addClass('content-block-3 block-loading');
            jQuery('#users_popular_tags_on').removeClass('hide');
            var jq_popular = jQuery('#users_popular_tags_list');
            jq_popular.empty();
            personalPopularTagsPage(jq_popular, 1, 20);
        });

        jQuery('#users_popular_tags_on').click(function () {
            jQuery('#users_popular_tags_list').slideUp(250, function (){
                jQuery(this)
                    .empty()
                    .show() // Needed so that they show again if it is reopened.
                    .parent().removeClass('content-block-3 block-loading');
                jQuery('#users_popular_tags_off').removeClass('hide');
                jQuery('#users_popular_tags_on').addClass('hide');
                jQuery('#users_popular_tags_none').addClass('hide');
                jQuery('#users_popular_tags_used').addClass('hide');
            });
        });
    };

    /**
     * Setup a page of popular suggestions for the logged in user.
     *
     * @param {jQuery} jq_popular The Dom object where popular suggestions are displayed.
     * @param {integer} page The page of results to display.
     * @param {integer} qty The quantity of results to display.
     */
    var personalPopularTagsPage = function(jq_popular, page, qty) {
        var make_post_url = BabblingBrook.Client.User.domain +
            '/' + BabblingBrook.Client.User.username + '/popularuserstreams';
        var make_post_data = {
            page : page,
            qty : qty
        };
        var data = {
            url : make_post_url,
            data : make_post_data,
            https : false
        };
        BabblingBrook.Client.Core.Interact.postAMessage(
            data,
            'InfoRequest',
            onPersonalPopularTagsFetched,
            onPersonalPopularTagsFetchedError,
            undefined,
            {
                jq_popular : jq_popular,
                page : page,
                qty : qty
            }
        );
    };

    /**
     * Displays rating suggestions when they are ready.
     *
     * @param {array} original_suggested_tags The suggested tag streams returned from the rhythm. Standard stream name objects.
     *
     * @return void
     */
    var onTagSuggestionsFetched = function (original_suggested_tags) {
        // Copy the suggestions so that the different versioning systems do not conflict.
        // @fixme Convert all usage of version string to version object on this page (there are a lot of them!)
        var suggested_tags = {};
        jQuery.extend(suggested_tags, original_suggested_tags);

        deferred_user_tags.done(function() {
            jQuery('#suggest_user_tags').removeClass('block-loading');
            jQuery.each(suggested_tags, function (i, row) {
                BabblingBrook.Test.isA(
                    [
                        [row.domain, 'domain'],
                        [row.name, 'resource-name'],
                        [row.username, 'username'],
                        [row.version, 'version-object|version'],
                    ],
                    'data returned from profile call to generatetag suggestions does not validate.'
                );
            });

            // Need to convert the suggested naming format to that used on this page.
            var renamed_tags = [];
            jQuery.each(suggested_tags, function (i, suggested_tag) {
                renamed_tags.push({
                    stream_domain : suggested_tag.domain,
                    stream_username : suggested_tag.username,
                    stream_name : suggested_tag.name,
                    stream_version : BabblingBrook.Library.makeVersionString(suggested_tag.version)
                });
            })
            suggested_tags = renamed_tags;

            var suggestion_id = 'suggest_user_tags_list';
            jQuery('#' + suggestion_id).html('');
            var filterd_tags = removeTagsAllreadyUsed(suggested_tags);
            if (filterd_tags.length < 1) {
                jQuery('#suggest_user_tags_none').removeClass('hide');
            } else {
                jQuery('#suggest_user_tags_none').addClass('hide');
                displayTags(filterd_tags, suggestion_id);
            }
        });
    };

    /**
     * Display suggested rating types according to the users user_type suggestion Rhythm.
     */
    var setupSuggestTags = function () {
        jQuery('#suggest_user_tags_on').click(function () {
            jQuery('#suggest_user_tags').addClass('content-block-3 block-loading');
            jQuery('#suggest_user_tags_off').removeClass('hide');
            jQuery('#suggest_user_tags_on').addClass('hide');

            BabblingBrook.Client.Core.Suggestion.fetch('user_stream_suggestion', onTagSuggestionsFetched, {});

        });

        jQuery('#suggest_user_tags_off').click(function () {
            jQuery('#suggest_user_tags_list').slideUp(250, function () {
                jQuery('#suggest_user_tags_off').addClass('hide');
                jQuery('#suggest_user_tags_on').removeClass('hide');
                jQuery('#suggest_user_tags_none').addClass('hide');
                jQuery('#suggest_user_tags_list')
                    .empty()
                    .show();
                jQuery('#suggest_user_tags').removeClass('content-block-3 block-loading');
            });
        });
    };

    /**
     * Generate the link for a profile. If the user is on a remote site it will have a different local profile.
     * @param {string} domain The domain of a user.
     * @param {string} username A users username.
     * @return {string} The url to the local profile of this user.
     */
    var profileLink = function (domain, username) {
        if (domain !== window.location.host) {
            return '/elsewhere/' + domain + '/' + username + '/profile';
        } else {
            return '/' + username + '/profile';
        }
    };

    /**
     * Callback to use when displaying posts so that the sort_value is displayed.
     *
     * Call with bind to include the sort score i.e. displaySort.bind(null, sort_value)
     *
     * @param {string} sort_value The value to display for the sort value.
     * @param {object} jq_post The jQuery object that contains the post.
     *
     * @return void
     */
    var displaySort = function (sort_value, jq_post, post) {
        if (typeof post.sort === 'undefined' || post.sort === '') {
            jQuery('div>span.sort-score-intro', jq_post).addClass('hide');
            return;
        }

        var jq_sort = jQuery('div>span.sort-score-intro>span.sort-score', jq_post);
        jq_sort.text(post.sort);
        if (parseInt(post.sort) < -2) {
            jq_post.addClass('low-sort');
        }
        if (parseInt(post.sort) > 1000000000) {
            jq_post.addClass('high-sort');
        }
    };

    /**
     * Displays tags from the various queries that fetch them.
     *
     * @param {object[]} data The JSON object containing the data to be displayed.
     * @param {string} data.stream_domain The domain of the stream for this post.
     * @param {string} data.stream_username The name of the stream for this post.
     * @param {string} data.stream_version The version of the stream for this post.
     * @param {number} [data.post_id] The post id of this post.
     * @param {number} [data.domain] The domain of this post.
     * @param {number} [data.take_value] The value of the take on this post.
     * @param {string} dom_id The id of the div to append the posts to.
     *
     * @return void
     * @refactor @protocol The requests to fetch data for this need refactoring. Not sure what to include.
     *      Only using domain and post id here.
     */
    var displayTags = function (data, dom_id) {
        var jq_tag_parent = jQuery('#' + dom_id);
        // Only clear old posts if there are new ones. Otherwise the error message will be over written.
        if(data.length > 0) {
            jq_tag_parent.html('');
        }

        jQuery.each(data, function (i, row) {
            var stream = {
                domain : row.stream_domain,
                username : row.stream_username,
                name : row.stream_name,
                version : row.stream_version
            };
            var post = {
                post_id : row.post_id,
                domain : row.domain,
                take_value : row.take_value
            };
            displayTag(stream, jq_tag_parent, post, row.score, row.type);
        });
    };

    /**
     * Validates the display data that is passed into the displayTags function
     * (arrives from multiple callback functions).
     *
     * @param {object[]} data See displayTags method for details.
     * @param {string} source The name of the callback function that called this one.
     *
     * @return {void}
     */
    var validateDisplayData = function (data, source) {
        var error_message = 'The ' + source + ' callback function data object is incorrect.';
        BabblingBrook.Test.isA([[data, 'array']], error_message);
        jQuery.each(data, function (i, row) {
            BabblingBrook.Test.isA([
                [row.stream_domain, 'domain'],
                [row.stream_username, 'username'],
                [row.stream_name, 'resource-name'],
                [row.stream_version, 'version'],
                [row.post_id, 'string'],
                [row.domain, 'domain'],
                [row.parent_id, 'null|undefined|string'],
                [row.top_parent_id, 'null|undefined|string'],
                [row.timestamp, 'uint'],
                [row.take_value, 'int|undefined'],
                [row.date_taken, 'uint']
            ], error_message);
        });

    };

    /**
     * Receives a list of takes by the logged on user for the profile user.
     *
     * @param {object[]} data See displayTags method for details.
     *
     * @return {undefined}
     */
    var onTagsByUserFetched = function (data) {
        validateDisplayData(data, 'loadUserTagsByUser');
        users_tags = data;
        for (var i=0; i< users_tags.length; i++) {
            var tag_stream_url = users_tags[i].stream_domain + '/' + users_tags[i].stream_username
                + '/stream/' + users_tags[i].stream_name + '/' + users_tags[i].stream_version;
            user_tag_urls[tag_stream_url] = true;
        }

        jQuery('#users_tags_for_profile').parent().removeClass('block-loading');
        if (data.length !== 0) {
            var id = 'users_tags_for_profile';
            displayTags(data, id);
        }

        deferred_user_tags.resolve();
    };

    /**
     * Error handler for fetching the current logged in users tags for this profile user.
     *
     * @param {string} error_code
     * @param {object} error_data
     *
     * @returns {undefined}
     */
    var onTagsByUserFetchedError = function (error_code, error_data) {    // Error.
        console.error('info request error : '  + error_code);
    };

    /**
     * Loads recent takes by the current logged on user on the user in the profile.
     *
     * THESE ARE LOADED FROM THE LOGGED IN USERS DOMAIN.
     *
     * @return {undefined}
     */
    var loadUserTagsByUser = function () {

        var url = BabblingBrook.Client.User.domain + '/' + BabblingBrook.Client.User.username + '/usertagsbyuser';
        var user_takes_data = {
            url : url,
            data : {
                profile_domain : domain,
                profile_username : username,
                start : 0,
                qty : page_qty
            },
            https : false
        };
        BabblingBrook.Client.Core.Interact.postAMessage(
            user_takes_data,
            'InfoRequest',
            onTagsByUserFetched,
            onTagsByUserFetchedError
        );
    };

    /**
     * Displays the kindred tags.
     *
     * @returns {undefined}
     */
    var displayKindredTags = function () {
        jQuery('#kindred_tag_list').parent().removeClass('block-loading');
        if (kindred_tags_summed.length !== 0) {
            var id = 'kindred_tag_list';
            displayTags(kindred_tags_summed, id);
        } else {
            jQuery('#kindred_tag_list_none').removeClass('hide');
        }
    };

    /**
     * Displays the global tags.
     *
     * @returns {undefined}
     */
    var displayGlobalTags = function () {
        jQuery('#global_tag_list').parent().removeClass('block-loading');
        if (global_tags_summed.length !== 0) {
            var id = 'global_tag_list';
            displayTags(global_tags_summed, id);
        } else {
            jQuery('#global_tag_list_none').removeClass('hide');
        }
    };

    /**
     * Callback to receive data about all users who have taken a user post with the this user.
     *
     * This also sets of the kindred takes - because kindred takes are taken from a subset of the global takes.
     *
     * @param {object[]} data
     * @param {string} data.domain
     * @param {number} data.post_id
     * @param {number} data.user_take_id
     * @param {string} data.username
     * @param {number} data.value
     *
     * @reutrn void
     */
    var onGlobalTagsFetched = function (data) {
        BabblingBrook.Client.Core.Loaded.onKindredLoaded(function () {
            validateDisplayData(data, 'loadGlobalUserTags');
            global_takes = global_takes = data;
            kindred_tags_summed = [];
            global_tags_summed = [];
            // create top global opinions and kindred
            var global_tag_count = 0;
            var kindred_tag_count = 0;
            var last_url = '';
            var last_tag;
            var global_takes_length = global_takes.length;
            jQuery.each(global_takes, function (i, row) {
                var user = row.user_domain + '/' + row.user_username;
                var is_kindred = false;
                if (typeof BabblingBrook.Client.User.kindred[user] !== 'undefined') {
                    is_kindred = true;
                }
                var current_url = row.stream_domain + '/' + row.stream_username + '/stream/' +
                    row.stream_name + '/' + row.stream_version;
                if (is_kindred === true) {
                    kindred_tag_count++;
                }

                if (i !== 0 && last_url !== current_url || global_takes_length === i-1) {
                    last_tag.score = global_tag_count;

                    global_tags_summed.push(last_tag);
                    if (kindred_tag_count > 0) {
                        var kindred_tag = jQuery.extend({}, last_tag);
                        kindred_tag.score = kindred_tag_count;
                        kindred_tag.type = 'kindred';
                        kindred_tags_summed.push(kindred_tag);
                    }
                    kindred_tag_count = 0;
                    global_tag_count = 0;
                }

                global_tag_count++;
                last_tag = {
                    stream_domain : row.stream_domain,
                    stream_username : row.stream_username,
                    stream_name : row.stream_name,
                    stream_version : row.stream_version,
                    post_id : row.post_id,
                    domain : row.domain
                };
                last_url = current_url;
            });
            // Sort the global and kindred tags by score.
            var sortTags = function (a, b) {
                return b.score - a.score;
            };
            global_tags_summed = global_tags_summed.sort(sortTags);
            global_tags_summed = global_tags_summed.sort(sortTags);

            deferred_user_tags.done(displayGlobalTags);
            deferred_user_tags.done(BabblingBrook.Client.Core.Loaded.onKindredLoaded(displayKindredTags));
        });
    };

    var globalTakesErrorCallback = function (error_code, error_data) {
        console.error('info request error');
    };

    /**
     * Load a large number of global takes against this user, kindred data will be filtered against it.
     *
     * THESE ARE LOADED FROM THE PROFILE USERS DOMAIN.     *
     */
    var fetchGlobalTags = function () {
        var url = user_url + '/usertagsglobal';
        var global_data = {
            start : 0,
            qty : global_take_qty,
            full_username : domain + '/' + username
        };
        var data = {
            url : url,
            data : global_data,
            https : false
        };
        BabblingBrook.Client.Core.Interact.postAMessage(data, 'InfoRequest', onGlobalTagsFetched, globalTakesErrorCallback);
    };

    /**
     * Show the relevent errors on the form.
     *
     * @param {object} An array of errors indexed by name. Each index contains another array of strings.
     *
     * @return void
     */
    var displayErrors = function(errors) {
        jQuery('#create_stream').removeClass('button-loading');
        for (var key in errors) {
            var errors_length = errors[key].length;
            var error_string = '';
            for(var i = 0; i < errors_length; i++) {
                error_string += errors[key][i] + '<br/>';
            }
            jQuery('#stream_' + key + '_error')
                .removeClass('hide')
                .html(error_string);
        }
    }

    /**
     * Callback for when the domus return returns from the request to make a new tag (stream).
     *
     * @param {object} response_data The data returned from the server.
     * @param {boolean} response_data.success Was the request to make a stream successful.
     * @param {object} [response_data.errors] An array of error messages, indexed by name.
     * @param {string} tag_name The name of the new stream that was submitted to the server.
     *
     * @returns {void}
     */
    var onMakeNewTagSuccess = function(tag_name, response_data) {
        jQuery('#create_tag_stream').removeClass('button-loading');

        // Silently publish the stream.
        var url = '/' + BabblingBrook.Client.User.username + '/stream/' + tag_name + '/0/0/0/changestatus';
        BabblingBrook.Library.post(
            url,
            {
                action : 'publish'
            },
            function () {},
            onMakeNewTagError
        );

        if (typeof response_data.success !== 'boolean') {
            onMakeNewTagError();
        }
        if (response_data.success === true) {
            var new_stream = {
                domain : BabblingBrook.Client.User.domain,
                username : BabblingBrook.Client.User.username,
                name : tag_name,
                version : '0/0/0'
            };
            jQuery('#new_tag_name').val('');
            jQuery('#new_tag_description').val('');
            jQuery('#the_new_tag').html('');
            displayTag(new_stream, jQuery('#the_new_tag'));
            jQuery('#make_new_tag_success').slideDown();
        } else {
            displayErrors(response_data.errors);
        }
    };

    /**
     * Callback for when the create button is pressed.
     *
     * @returns {void}
     */
    var onMakeNewTagError = function() {
        jQuery('#create_stream').removeClass('button-loading');
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : 'An unknown error occured whilst trying to save a stream.',
        });
    };

    /**
     * Create a new tag for a user.
     *
     * @returns {undefined}
     */
    var setupMakeNewTag = function () {
        jQuery('#make_new_tag>a').click(function () {
            jQuery('#make_new_tag_form').slideDown();
            jQuery('#make_new_tag').slideUp();
        });
        jQuery('#make_new_tag_form_off').click(function () {
            jQuery('#make_new_tag_form').slideUp();
            jQuery('#make_new_tag').slideDown();
        });

        jQuery('#create_tag_stream').click(function (){
            jQuery('#create_tag_stream').addClass('button-loading');
            jQuery('#make_new_tag_form .error')
                .html('')
                .addClass('hide');

            var name = jQuery('#new_tag_name').val();
            var description = jQuery('#new_tag_description').val();
            if (description.length === 0) {
                description = jQuery('#new_tag_default_description_template').text();
            }
            var kind = 'user';

            BabblingBrook.Library.post(
                '/' + BabblingBrook.Client.User.username + '/streams/make',
                {
                    name : name,
                    description : description,
                    kind : kind,
                    post_mode : 'anyone'
                },
                onMakeNewTagSuccess.bind(null, name),
                onMakeNewTagError,
                'make_stream_error'
            );
        });
    };

    /**
     * Setup the options for rating a user.
     */
    var setupNewTagOptions = function () {

        setupPersonalPopularTags();
        setupSuggestTags();
        searchForTagSuggestions();
        setupMakeNewTag();
    };

    /**
     * Sets up invitation links to the rings the logged on user adminsitrates.
     *
     * @returns {undefined}
     */
    var setupRingInvites = function() {
        var display_section = false;
        jQuery.each(BabblingBrook.Client.User.Rings, function(i, ring) {
            if (ring.member === '1' && ring.member_type === 'invitation') {
                display_section = true;

                var jq_line = jQuery('#make_ring_invite_line_template>div').clone();
                var text = jQuery('a' ,jq_line).text().replace('*ring_name*', ring.name);
                jQuery('a' ,jq_line)
                    .attr('href', '/' + BabblingBrook.Client.User.username + '/ring/index?to=' + domain + '/' + username)
                    .text(text);
                jQuery('#make_ring_invite>div').append(jq_line);
            }
        });

        if (display_section === true) {
            jQuery('#make_ring_invite').removeClass('hide');
        }

    };

    return {

        construct : function () {
            domain = jQuery.trim(jQuery('#domain').text());
            username = jQuery.trim(jQuery('#username').text());
            profile_src = 'http://' + domain + '/images/user/' + domain + '/' + username + '/profile/large/profile.jpg';
            user_url = domain + '/' + username;
            profile_url = user_url + '/profile';
            new_field = [
            {
                display_order : '1',
                link_title : domain + '/' + username,
                link : 'http://' + domain + '/' + username + '/profile'
            },
            {
                display_order : '2'
            }
            ];

            showProfileImage();
            getProfileData();
            showKindredScore();
            showEditLink();
            showMembersLink();
            setupNewTagOptions();
            setupRingInvites();

            loadUserTagsByUser();
            fetchGlobalTags();
            BabblingBrook.Client.Core.Loaded.setProfileLoaded();
        },

        /**
         * An overridable hook that is called when a user is tagged.
         *
         * @param {object} stream The stream that has been used in the tag.
         *
         * @returns {void}
         */
        onTaggedHook : function (stream) {},


        /**
         * An overridable hook that is called when a ring is joined.
         *
         * @returns {void}
         */
        onJoindRingHook : function () {}


    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.User.Profile.construct();
});