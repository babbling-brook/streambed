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
 * @fileOverview Javascript used for the The display of a list of posts.
 *
 * @author Sky Wickenden
 */

/**
 * Displays a series of posts in a linear format.
 * Used by streams, deltas and private posts to display a list of posts.
 *
 * @param {object} jq_location jQuery object pointing to the location to place the cascade.
 * @param {string} generic_post_template_path A jQuery path to the template used to create posts.
 * @param {fetchPosts} A callback used to fetch the posts too display in this cascade.
 *      Accepts one paramater - a callback used to pass the posts back.
 *      The callback needs to be called with an array of post objects. See the posts array for details.
 * @param {string} generic_reply_location_path A jQuery path for the location to display replies.
 *      This should be relative to the post being replied to.
 * @param {object} [jq_generic_reply_template] jQuery object pointing to the template to use for displaying
 *      replies. Only required if the post template has reply enabled.
 * @param {function} onGenericBeforePostDisplayed This is called before the post is displayed and can be used to apply
 *      additional features to the display of the post.
 *      It accepts one paramater - the current implementation of the post template.
 * @param {boolean} [slide=true] Should posts slide into view.
 * @param {boolean} [show_empty_fields=true] If a field is empty should the label be displayed
 * @param {function} [fetchMorePosts] If all the origional posts have been displayed, this can be defined to fetch more.
 *
 * @namespace Javascript
 * @package JS_Client
 */
BabblingBrook.Client.Component.Cascade = function (jq_location, generic_post_template_path, fetchPosts,
    generic_reply_location_path, jq_generic_reply_template, onGenericBeforePostDisplayed, slide, show_empty_fields,
    fetchMorePosts
    ) {
    'use strict';
    /**
     * @type {object} JQuery object holding the location of the cascade.
     */
    var jq_cascade;

    /**
     * JQuery object holding a clone of a dummy post.
     * Used as a place holder to maintain display order whilst the post data is loading.
     *
     * @type {object}
     */
    var jq_dummy_post_template;

    /**
     * An array of post objects waiting to be displayed.
     *
     * @type {array} posts An array of post objects.
     * @type {string} posts[].domain The domain of a post.
     * @type {string} posts[].post_id The id of a post.
     * @type {string} posts[].[post_template_path] Path to the template to be used for displayin the post.
     *      If not provided then generic_post_template_path will be used.
     * @type {string} posts[].reply_location_path A jQuery path for the location to display replies.
     *      This should be relative to the post being replied to.
     * @type {object} posts[].[jq_reply_template] A reply template to use for this post.
     *      If not provided then jq_generic_reply_template will be used for this post.
     * @type {function} onBeforePostDisplayed This is called before the post is displayed and can be used to
     *      apply additional features to the display of the post.
     *      It accepts one paramater - the current implementation of the post template.
     */
    var posts = [];

    /**
     * @type {object} Posts that have been displayed. Same structure as the posts object.
     */
    var posts_displayed = [];

    /**
     * @type {boolean }A flag to prevent unneccessary callbacks.
     */
    var all_done = false;

    /**
     * Has a new sort request all ready started. Prevents repeat requests from firing.
     */
    var fetching = true;

    if (typeof onGenericBeforePostDisplayed !== 'function') {
        onGenericBeforePostDisplayed = function() {};
    }

    /**
     * Checks if the bottom of the posts container has scrolled into view.
     *
     * @return boolean
     */
    var areMessagesEndInView = function() {
        var doc_view_top = jQuery(window).scrollTop();
        var doc_view_bottom = doc_view_top + jQuery(window).height();

        var elem_top = jQuery(jq_cascade).offset().top;
        var elem_bottom = elem_top + jQuery(jq_cascade).height();
        return elem_bottom <= doc_view_bottom;
    };

    /**
     * Callback for the fetchPosts callback to pass new posts back to this module.
     *
     * @param {array} new_posts See the posts array for a definition.
     *
     * @return {void}
     */
    var onPostsFetched = function(new_posts) {
        jQuery('.cascade-loading', jq_cascade).addClass('hide');
        jQuery('.cascade-new-top', jq_cascade).removeClass('hide');
        if (new_posts.length < 1) {
            if (posts_displayed.length < 1) {
                jQuery('.cascade-no-posts', jq_cascade).removeClass('hide');
            } else {
                jQuery('.cascade-no-more-posts', jq_cascade).removeClass('hide');
            }
            all_done = true;
            return;
        };

        posts = posts.concat(new_posts);
        fetching = false;
        if (areMessagesEndInView() === true) {
            displayNextPost();
        }
    };

    /**
     * Callback to use when the post is ready to display and any amendments need to be made.
     *
     * @param {function} onBeforeStreamPostDisplayed The same callback as this one registered by instantiating class.
     * @param {object} jq_post The jQuery object that contains the post.
     * @param {object} post The BabblingBrook.Models.post object used to display the post.
     *
     * @return void
     */
    var onBeforeCascadePostDisplayed = function (onBeforeStreamPostDisplayed, is_new, jq_post, post) {
        if (typeof is_new === 'boolean' && is_new === true) {
            jq_post.addClass('new-post');
        }
        onBeforeStreamPostDisplayed(jq_post, post);
    }

    /**
     * Prepare a post for sending to DisplayPost.
     *
     * @param {object} post The post to display.
     * @param {object} jq_dummy_post The placeholder in the cascade for displaying this post.
     * @param {boolean} is_update Is this an update to an already displayed post.
     * @param {boolean} [is_new] Should this post be flagged with the new-post class.
     *
     * @return {void}
     */
    var preparePostForDisplay = function (post, jq_dummy_post, is_update, is_new) {
        var post_template_path = generic_post_template_path;
        if (typeof post.post_template_path === 'string') {
            post_template_path = post.post_template_path;
        }

        var jq_post_template = jQuery(post_template_path).clone();

        var reply_location_path = generic_reply_location_path;
        if (typeof post.reply_location_path === 'string') {
            reply_location_path = post.reply_location_path;
        }

        var jq_reply_template = jq_generic_reply_template;
        if (typeof post.jq_reply_template === 'object') {
            jq_reply_template = post.jq_reply_template;
        }

        var onBeforeStreamPostDisplayed = onGenericBeforePostDisplayed;
        if (typeof post.onBeforePostDisplayed === 'function') {
            onBeforeStreamPostDisplayed = post.onBeforePostDisplayed;
        }

        BabblingBrook.Client.Component.Post(
            post,
            jq_dummy_post,
            jq_post_template,
            reply_location_path,
            jq_reply_template,
            onBeforeCascadePostDisplayed.bind(null, onBeforeStreamPostDisplayed, is_new),
            is_update,
            slide,
            show_empty_fields
        );
    };

    /**
     * Display the next waiting post.
     *
     * If there are not posts left to display then it trys to fetch more
     * and failing that displays an end message.
     *
     * @return {void}
     */
    var displayNextPost = function() {
        if (fetching === true) {
            return;
        }
        if (posts.length < 1) {
            fetching = true;
            fetchMorePosts(onPostsFetched);
            return;
        }

        var next_post = posts.splice(0, 1);
        next_post = next_post[0];
        posts_displayed.push(next_post);

        if (parseInt(next_post.sort) < -9999) {
            displayNextPost();
            return;
        }

        var jq_dummy_post = jq_dummy_post_template.clone();

        jQuery('.cascade-body', jq_cascade).append(jq_dummy_post);
        // Using a normal slide down the animation sometimes halts
        // This fixes it. Not sure why it happens. Fail never seems to get called even when it freezes.
        jq_dummy_post.slideDown({
            duration : 100,
            fail : function (p) {
                console.log('failed to slide down');
                console.log(p);
                console.trace();
            },
            always : function () {
                jq_dummy_post.removeClass('hide');

                preparePostForDisplay(next_post, jq_dummy_post, false);

                if (areMessagesEndInView() === true) {
                    displayNextPost();
                }
            }
        });
    };

    var setupScrollEvent = function() {
        // If scrolled to the bottom of the posts, then load more.
        jQuery(window).scroll(function() {
            if (all_done === true || fetching === true) {
                return false;
            }
            if (posts.length === 0) {
                fetching = true;
                fetchMorePosts(onPostsFetched);
                return;
            }

            if (areMessagesEndInView() === true) {
                displayNextPost();
            }
        });
    };

    var setup = function() {
        BabblingBrook.Client.Component.Cascade.setupEvents();

        jq_cascade = jQuery('#cascade_template>div').clone();
        jq_location.addClass('block-loading')
        jq_location.empty();
        jq_location.append(jq_cascade);
        jq_dummy_post_template = jQuery('#cascade_dummy_post_template>div').clone();

        if (typeof slide === 'undefined') {
            slide = true;
        }
        if (typeof show_empty_fields === 'undefined') {
            show_empty_fields = true;
        }

        if (typeof fetchMorePosts === 'undefined') {
            fetchMorePosts = function () {
                onPostsFetched([]);
            };
        }

        setupScrollEvent();

        fetchPosts(onPostsFetched);
    };
    setup();

    /**
     * Redisplay an updated post.
     *
     * @param {object} post The post to redisplay.
     *
     * @return {void}
     */
    var redisplayPost = function(post) {
        var jq_origional_post;
        var jq_posts = jQuery('.cascade-body>.post', jq_cascade);
        jq_posts.each(function (i, dom_post) {
            var jq_post = jQuery(dom_post);
            if (jq_post.attr('data-post-domain') === post.domain
                && jq_post.attr('data-post-id') === post.post_id
            ) {
                jq_origional_post = jq_post;
                return false;   // Escape from the .each
            }
            return true;        // Continue with the .each
        });
        preparePostForDisplay(post, jq_origional_post, true);
    };

    /**
     * Setup the new posts, but keep them hidden. Display the new-posts link in the preceeding post to open them up.
     *
     * @prarm {object} preceeding_post The post before this one. If undefined then the new post will be appended to the top
     *      of the stream.
     * @param {object} new_post The post that is being displayed.
     * @param {boolean} [auto_show=false] Should the post be shown in full.
     *      Primarily used to show posts that have just been created.
     *
     * @return {void}
     */
    var displayNewPostsLink = function (preceeding_post, new_post, auto_show) {
        if (typeof auto_show === 'undefined') {
            auto_show = false;
        }

        var jq_posts = jQuery('.cascade-body>.post', jq_cascade);
        var jq_dummy_post = jq_dummy_post_template.clone();

        // If the preceeding_post is undefined then the new post should be at the top of the cascade.
        if (typeof(preceeding_post) === 'undefined') {
            jQuery('.cascade-body', jq_cascade).prepend(jq_dummy_post);
            if (typeof auto_show === 'undefined' || auto_show === false) {
                jQuery('.cascade-new-top', jq_cascade).removeClass('hidden');
            } else {
                jq_dummy_post.removeClass('hide');
            }

        } else {
            jq_posts.each(function (i, dom_post) {
                var jq_post = jQuery(dom_post);
                if (jq_post.attr('data-post-domain') === preceeding_post.domain
                    && jq_post.attr('data-post-id') === preceeding_post.post_id
                ) {
                    jq_post.after(jq_dummy_post);
                    jQuery('.new-posts-link', jq_post).removeClass('hidden');
                    return false;   // Escape from the .each
                }
                return true;        // Continue with the .each
            });
        }

        // Need to insert the data-post-domain and data-post-id attributes now as otherwise the rest of the updated
        // posts may miss their insert points.
        jq_dummy_post
            .addClass('post')
            .attr('data-post-domain', new_post.domain)
            .attr('data-post-id', new_post.post_id);
        preparePostForDisplay(new_post, jq_dummy_post, false, true);

        posts_displayed.push(new_post);
    };

    /**
     * Should a new post be displayed. Either as an update or an insert.
     *
     * @param {object} new_post A BabblingBrook.Models.Post object represening the new post.
     *
     * @return {boolean}
     */
    var shouldNewPostBeDisplayed = function (new_post) {
        for (var i = 0; i < posts_displayed.length; i++) {
            if (new_post.domain === posts_displayed[i].domain
                && new_post.post_id === posts_displayed[i].post_id
            ) {
                if (typeof new_post.revision !== 'undefined' && typeof posts_displayed[i].revision !== 'undefined'
                    && parseInt(new_post.revision) !== parseInt(posts_displayed[i].revision)
                ) {
                    posts_displayed[i] = new_post;
                    redisplayPost(posts_displayed[i]);
                    return true;
                } else {
                    return false;
                }
            }
        }

        // First need to check if the post is already waiting to be displayed.
        // It may have a different sort score than before. If so then we keep the original.
        for (var i = 0; i < posts.length; i++) {
            if (new_post.domain === posts[i].domain
                && new_post.post_id === posts[i].post_id
            ) {
                return false;
            }
        }

        // Not yet displayed. Check to see if the post should be displayed in a link before
        // the currently visible posts.
        for (var i = 0; i < posts_displayed.length; i++) {
            if (parseInt(new_post.sort) > parseInt(posts_displayed[i].sort)) {
                posts_displayed.splice(i, 0, new_post);
                displayNewPostsLink(posts_displayed[i - 1], posts_displayed[i]);
                return true;
            }
        }
    };

    /**
     * Inserts a new post into the queue of posts waiting to be displayed.
     *
     * @param {object} new_post The new post to add to the queue.
     *
     * @return {void}
     */
    var insertNewPostIntoQueue = function (new_post) {
        for (var i = 0; i < posts.length; i++) {
            if (new_post.sort > posts[i].sort) {
                posts_displayed.splice(i, 0, new_post);
                return;
            }
        }
    };

    /**
     * Returns true if a post is already on display.
     *
     * @param {object} post The post to check.
     *
     * @returns {boolean} true if the post is on display.
     */
    var isOnDisplay = function (post) {
        for (var i = 0; i < posts_displayed.length; i++) {
            if (post.domain === posts_displayed[i].domain
                && post.post_id === posts_displayed[i].post_id
            ) {
                return true;
            }
        }
        return false;
    }

    return {

        /**
         * Receives an updated list of posts to display.
         *
         * Some may be updates to posts already displayed, others may be new.
         *
         * @param {array} new_posts The new posts to display.
         * @param {boolean} [jump_to_top=false] Should the new posts be automatically shown in full at the top.
         *      Primarily used to show posts that have just been created.
         *
         * @return {void}
         */
        update : function(new_posts, jump_to_top) {
            if (typeof jump_to_top === 'undefined') {
                jump_to_top = false;
            }

            if (jump_to_top === true) {
                if (isOnDisplay(new_posts[0]) === true) {
                    return;
                }
            }

            for (var i = 0; i < new_posts.length; i++) {
                if (jump_to_top === true) {

                    displayNewPostsLink(undefined, new_posts[i], true);
                } else if (shouldNewPostBeDisplayed(new_posts[i]) === false) {
                    insertNewPostIntoQueue(new_posts[i], jump_to_top);
                }
            }
        }

    };
};

/**
 * @type {boolean} Prototype variable for recording if the global events have been setup.
 */
BabblingBrook.Client.Component.Cascade.prototype.setupRun = false;

/**
 * Setup click event handlers.
 *
 * In a static function because these are live document level events.
 * This is so that cascade objects do not need to be maintained after creation.
 * This needs called once on each page that displys cascades.
 *
 * @return void;
 */
BabblingBrook.Client.Component.Cascade.setupEvents = function () {
    if (BabblingBrook.Client.Component.Cascade.prototype.setupRun === true) {
        return;
    }

    jQuery(document).on(
        'click',
        '.cascade>.cascade-new-top>.link, .cascade .new-posts-link>.link',
        BabblingBrook.Client.Component.Cascade.showNewPosts
    );

    BabblingBrook.Client.Component.Cascade.prototype.setupRun = true;
};

/**
 * Slide down all the hidden posts waiting to be displayed and turn off the 'show new posts' links.
 *
 * @returns {void}
 */
BabblingBrook.Client.Component.Cascade.showNewPosts = function () {
    var jq_posts = jQuery('.cascade .post:not(.hide)').removeClass('new-post');
    var jq_posts = jQuery('.cascade .post.hide');
    var start_scroll_top = jQuery(window).scrollTop();
    var jq_link_clicked = jQuery(this);
    var click_element_offset = jq_link_clicked.offset().top;
    jq_posts.slideDown({
        duration : 250,
        progress : function () {
            var new_click_element_offset = jq_link_clicked.offset().top;
            var difference = new_click_element_offset - click_element_offset;
            jQuery(window).scrollTop(start_scroll_top + difference);
        },
        complete : function () {
            jq_posts.removeClass('hide');
            jQuery('.cascade>.cascade-new-top, .cascade .new-posts-link').addClass('hidden');
        }
    });
};