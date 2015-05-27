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
 * @fileOverview Displays a photowall of posts.
 *
 * @author Sky Wickenden
 */

/**
 * Displays a photowall of posts.
 * Used by streams, deltas and private posts to display a photowall of posts.
 *
 * @param {object} jq_location jQuery object pointing to the location to place the photowall.
 * @param {string} post_template_path A jQuery path to the template used to create posts.
 * @param {fetchPosts} A callback used to fetch the posts too display in this cascade.
 *      Accepts one paramater - a callback used to pass the posts back.
 *      The callback needs to be called with an array of post objects. See the posts array for details.
 * @param {function} [fetchMorePosts] If all the origional posts have been displayed, this can be defined to fetch more.
 *
 * @namespace Javascript
 * @package JS_Client
 */
BabblingBrook.Client.Component.Photowall = function (jq_location, post_template_path, fetchPosts, fetchMorePosts
) {
    'use strict';

    var post_ready_count = 0;

    var makeJustifiedGalery = function () {
        jq_location.justifiedGallery({
            rowHeight : 250,
            margins : 5,
            captionSettings : {
                animationDuration : 500,
                visibleOpacity : 0.8,
                nonVisibleOpacity : 0.0
            }
        }).on('jg.complete', function (e) {
        });
        var container_right_margin = jq_location.css('margin-left');
        container_right_margin = container_right_margin.substr(0, container_right_margin.length -2);
        container_right_margin = container_right_margin - 4;
        jq_location.css('margin-left', container_right_margin + 'px');

    };

    var waitForPostsToLoad = function (new_posts_length) {
        setTimeout(function () {
            if (post_ready_count === new_posts_length) {
                makeJustifiedGalery();
            } else {
                waitForPostsToLoad(new_posts_length);
            }
        }, 100);
    };

    var insertPost = function (jq_dummy_post, post) {
        BabblingBrook.Client.Component.Post(
            post,
            jq_dummy_post,
            jQuery(post_template_path).clone(),
            undefined,
            undefined,
            function () {
                post_ready_count++;
            },
            false,
            false,
            false,
            false,
            undefined,
            'proportional'
        );
    };

    /**
     * Callback for the fetchPosts callback to pass new posts back to this module.
     *
     * @param {array} new_posts See the posts array for a definition.
     *
     * @return {void}
     */
    var onPostsFetched = function(new_posts) {
        for(var i=0; i < new_posts.length; i++) {
            var post = new_posts[i];
//            var jq_new_post = jQuery(generic_post_template_path).clone();
  //          jq_new_post.find('.title>a').text(post.)

            var jq_dummy_post = jQuery('<div>');
            jq_location.append(jq_dummy_post);
            insertPost(jq_dummy_post, post);

        }
        waitForPostsToLoad(new_posts.length);
    };

    fetchPosts(onPostsFetched);

    return {

        insertPost : function (post) {
            var jq_dummy_post = jQuery('<div>');
            jq_location.prepend(jq_dummy_post);
            insertPost(jq_dummy_post, post);
            makeJustifiedGalery();
        },

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
        }

    };
};