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
 * @fileOverview Javascript used for the private messaging compose page.
 *
 * @author Sky Wickenden
 */

/**
 * Allows the user to send private messages.
 *
 * @namespace Javascript used for the private messaging compose page.
 * @package JS_Client
 */
BabblingBrook.Client.Page.Mail.Compose = (function () {
    'use strict';

    var onPostMade = function(post) {
        jQuery('#recent_posts_container').removeClass('hide');

        var jq_clone = jQuery('#dummy_compose_template>div').clone();
        jQuery('#recent_posts').prepend(jq_clone);
        var jq_post_template = jQuery('#post_inbox_template>.post').clone();
        BabblingBrook.Client.Component.Post(
            post,
            jQuery('#dummy_post'),
            jq_post_template,
            undefined,
            undefined
        );
        jQuery('.make-post').empty();
        createForm();
    };

    /**
     * Called when the post form has finished loading.
     * @returns {undefined}
     */
    var onMakePostFormReady = function () {
        jQuery('#compose_post>.make-post .private-post-check>input').attr('checked', true);
        jQuery('#compose_post>.make-post').removeClass('block-loading');
    };

    var onCancelPost = function () {
        createForm();
    };

    var createForm = function() {
        var make_post = new BabblingBrook.Client.Component.MakePost(onPostMade, onCancelPost);
        // The post url is the same as page location, with /posts/ switched to json.
        var private_stream_url = BabblingBrook.Library.changeUrlAction(
            BabblingBrook.Client.User.Config.private_message_stream,
            'json'
        );
        make_post.setupNewPost(
            private_stream_url,
            jQuery('#compose_post>.make-post'),
            'open',
            undefined,
            undefined,
            undefined,
            'private',
            undefined,
            onMakePostFormReady
        );
    };

    return {

        /**
         * The BabblingBrook.Client.Page.Mail.Compose constructer.
         *
         * @return void
         */
        construct : function () {
            createForm();
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
        BabblingBrook.Client.Page.Mail.Compose.construct();
    });
});