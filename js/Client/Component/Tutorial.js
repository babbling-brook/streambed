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
 * @fileOverview Displays tutorial messages and tracks progress.
 * @author Sky Wickenden
 */

/*
 * @namespace Displays tutorial messages and tracks progress.
 * @package JS_Client
 */
BabblingBrook.Client.Component.Tutorial = (function () {
    'use strict';

    var jq_tutorial_dialog;

    /**
     * @type {boolean} Indicates if the user is waiting before doing the next tutorial.
     */
    var user_is_waiting = false;

    /**
     *
     * @type {object} Jquery deferred object that prevents race conditions on public tutorial functions.
     */
    var deferred_tutorial_loaded = jQuery.Deferred();

    /**
     * @type {object} The users real stream subscriptions.
     */
    var original_stream_subscriptions;

    var tutorial_user = {
        domain : window.location.hostname,
        username : 'sky'
    };

    var news_stream = {
        domain : window.location.hostname,
        stream_subscription_id : 4,
        filters : {
            '0' : {
                filter_subscription_id : '0',
                description : 'Sort posts by newest first',
                domain : window.location.hostname,
                locked : true,
                name : 'newest',
                params : [],
                priority : 100001,
                url : window.location.hostname + '/sky/rhythm/newest/latest/latest/latest/json',
                username : 'sky',
                version : 'latest/latest/latest'
            }
        },
        locked: false,
        moderation_rings : [],
        name : 'news',
        url : window.location.hostname + '/sky/stream/news/latest/latest/latest/json',
        user_stream_subscription_id : null,
        username : 'sky',
        version : {
            major : 'latest',
            minor : 'latest',
            patch : 'latest',
        },
        rings : {}
    };

    var babbling_brook_stream = {
        domain : window.location.hostname,
        stream_subscription_id : 3,
        filters : {
            '0' : {
                filter_subscription_id : '0',
                description : 'Sort posts by newest first',
                domain : window.location.hostname,
                locked : true,
                name : 'newest',
                params : [],
                priority : 100001,
                url : window.location.hostname + '/sky/rhythm/newest/latest/latest/latest/json',
                username : 'sky',
                version : 'latest/latest/latest'
            }
        },
        locked: false,
        moderation_rings : [],
        name : 'babbling brook',
        url : window.location.hostname + '/sky/stream/babbling+brook/latest/latest/latest/json',
        user_stream_subscription_id : null,
        username : 'sky',
        version : {
            major : 'latest',
            minor : 'latest',
            patch : 'latest',
        },
        rings : {}

    };

    var startTutorials = function () {
        closeTutorial();
        BabblingBrook.Client.User.tutorial_level_name = 'NOT_STARTED';
        BabblingBrook.Library.post(
            '/' + BabblingBrook.Client.User.username + '/starttutorials',
            {},
            function () {},
            function () {},
            'start_tutorials_error'
        );
        BabblingBrook.Library.post(
            '/' + BabblingBrook.Client.User.username + '/levelup',
            {},
            function (level_data) {
                BabblingBrook.Client.User.tutorial_level_name = level_data.level;
                displayTutorial();
            }
        );
        jQuery('#content').removeClass('hide');
    };

    /**
     * Displays the location of the link that pops up the current tutorial.
     *
     * @param {function} onAcknowledged Callback for when this message is acknowledged.
     *
     * @returns {undefiend}
     */
    var displayTutorialLocation = function (onAcknowledged) {
        var title = jQuery('#tutorial_location_title_template').text();
        var details = jQuery('#tutorial_location_template>div').clone();
        closeTutorial();
        jq_tutorial_dialog = jQuery('<div></div>');
        jq_tutorial_dialog.html('')
        jq_tutorial_dialog.dialog({
            autoOpen: true,
            title: title,
            appendTo : '#tutorial_placement',
            closeOnEscape: false,
            open: function(event, ui) {
                jQuery('#tutorial_placement .ui-dialog-titlebar-close').remove();
            },
            dragStart : function () {
                 jQuery('div').removeClass('top-dialogue');
                 jQuery(this).parent().addClass('top-dialogue');
            },
            focus : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            }
        });
        jq_tutorial_dialog
            .html(details)
            .dialog('option', 'height', 'auto')
            .dialog('option', 'closeText', '');

        jQuery('#yes_tutorial').click(function () {
            jq_tutorial_dialog.dialog('destroy');
            jq_tutorial_dialog = false;
            onAcknowledged();
        });
    };


    var exitTutorials = function () {
        closeTutorial();

        if (BabblingBrook.Client.User.tutorial_level_name === 'FINISHED') {
            confirmExitTutorials();
            return;
        }

        var title = jQuery('#turn_off_title_template').text();
        var details = jQuery('#turn_off_template>div').clone();

        jq_tutorial_dialog = jQuery('<div></div>');
        jq_tutorial_dialog.html('')
        jq_tutorial_dialog.dialog({
            autoOpen: true,
            title: title,
            appendTo : '#tutorial_placement',
            close : function () {jq_tutorial_dialog = false},
            dragStart : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            },
            focus : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            }
        });
        jq_tutorial_dialog
            .html(details)
            .dialog('option', 'height', 'auto')
            .dialog('option', 'closeText', '')
            .dialog('option', 'width', 450)
            .dialog('moveToTop');

        jQuery('#close_tutorial').click(confirmExitTutorials);
        jQuery('#restart_tutorial').click(onRedisplayTutorial);
    };

    var onRedisplayTutorial = function () {
        onStartTutorials();
    };

    var confirmExitTutorials = function () {
        if (typeof jq_tutorial_dialog.dialog === 'function') {
            jq_tutorial_dialog.dialog('destroy');
            jq_tutorial_dialog = false;
        }
        BabblingBrook.Client.User.tutorial_level_name = false;
        BabblingBrook.Library.post(
            '/' + BabblingBrook.Client.User.username + '/exittutorials',
            {},
            function () {
                window.location = window.location;
            },
            function () {},
            'exit_tutorials_error'
        );
    };

    var showInitialMessage = function () {
        closeTutorial();
        jQuery('#content').addClass('hide');
        jQuery('#streams_nav').empty();

        var title = jQuery('#level_0_title_template').text();
        var details = jQuery('#level_0_template>div').clone();

        jq_tutorial_dialog = jQuery('<div></div>');
        jq_tutorial_dialog.html('')
        jq_tutorial_dialog.dialog({
            autoOpen: true,
            title: title,
            appendTo : '#tutorial_placement',
            closeOnEscape: false,
            open: function(event, ui) { jQuery('#tutorial_placement .ui-dialog-titlebar-close').remove(); },
            dragStart : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            },
            focus : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            }
        });
        jq_tutorial_dialog
            .html(details)
            .dialog('option', 'height', 'auto')
            .dialog('option', 'closeText', '')
            .dialog('option', 'width', 450)
            .dialog('moveToTop');

        jQuery('#yes_tutorial').click(onStartTutorials);
        jQuery('#no_tutorial').click(exitTutorials);
    };

    /**
     * Start the tutorials and reload the page so that menus are turned off.
     *
     * @returns {void}
     */
    var onStartTutorials = function () {
        if (BabblingBrook.Client.User.tutorial_level_name === '') {
            BabblingBrook.Library.post(
                '/' + BabblingBrook.Client.User.username + '/starttutorials',
                {},
                function (level_data) {
                    window.location = window.location;
                },
                function () {},
                'restart_tutorials_error'
            );
        } else {
            startTutorials();
        }
    }

    var closeTutorial = function () {
        if (typeof jq_tutorial_dialog !== 'undefined') {
            if (typeof jq_tutorial_dialog.dialog !== 'undefined') {
                jq_tutorial_dialog.dialog('destroy');
            }
            jq_tutorial_dialog = false;
        }
    };

    /**
     * Shows the tutroial page now that it has loaded.
     *
     * @returns {undefined}
     */
    var onTutorialPageLoaded = function () {
        var title = jQuery('#level_' + BabblingBrook.Client.User.tutorial_level_name + '_title_template').text();
        if (typeof title === 'undefined' || title.length < 1) {
            console.error('tutorial template is missing : ' + BabblingBrook.Client.User.tutorial_level_name);
            return;
        }

        var details = jQuery('#level_' + BabblingBrook.Client.User.tutorial_level_name + '_template>div').clone();
        if (BabblingBrook.Client.User.tutorial_level_name.length > 0
            && BabblingBrook.Client.User.tutorial_level_name !== 'NOT_STARTED'
        ) {
            var turn_off_button = jQuery('#turn_off_tutorials_button_template>button').clone();
            details.append(turn_off_button);
        }

        jq_tutorial_dialog = jQuery('<div></div>');
        jq_tutorial_dialog.html('')
        jq_tutorial_dialog.dialog({
            autoOpen: true,
            title: title,
            resizable: false,
            appendTo : '#tutorial_placement',
            width : 'auto',
            dragStart : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            },
            focus : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            }
        });
        jq_tutorial_dialog
            .html(details)
            .dialog('option', 'height', 'auto')
            .dialog('option', 'closeText', '')
            .dialog('open')
            .dialog('moveToTop');
        jQuery('#tutorial_placement #close_tutorial').click(closeTutorial);
        jQuery('#tutorial_placement #turn_off_tutorials').click(exitTutorials);

        setupTutorialHooks();
    };

    /**
     * Displays the current tutorial message.
     *
     * @param {boolean} [redirect=true] Should the user be redirected to the location that the tutorial starts.
     *
     * @returns {unresolved}
     */
    var displayTutorial = function (redirect) {
        if (typeof redirect === 'undefined') {
            redirect = true;
        }

        if (window.location.pathname.indexOf('logout') > 0) {
            return;
        }

        closeTutorial();
        if (jq_tutorial_dialog) {
            return;
        }
        if (BabblingBrook.Client.User.tutorial_level_name === 'NOT_STARTED') {
            showInitialMessage();
            return;
        }

        var location = jQuery('#level_' + BabblingBrook.Client.User.tutorial_level_name + '_template .tutorial-location')
            .attr('data-location');
        if (location === '/user/streamsubscriptions') {
            location = location.replace('user', BabblingBrook.Client.User.username);
        }
        if (typeof location !== 'undefined' && redirect === true) {
            location = window.location.hostname + location;
            jQuery('#sidebar_container').empty();
            jQuery('#content_page').empty();
            BabblingBrook.Client.Core.Ajaxurl.redirect(location, onTutorialPageLoaded);
        } else {
            onTutorialPageLoaded();
        }
    };

    /**
     * Increase the users level and start the next level.
     *
     * @returns {undefined}
     */
    var levelUp = function () {
        BabblingBrook.Library.post(
            '/' + BabblingBrook.Client.User.username + '/levelup',
            {},
            function (level_data) {
                closeTutorial();
                BabblingBrook.Client.User.tutorial_level_name = level_data.level;
                BabblingBrook.Client.Core.FeatureSwitches.setupTutorial(
                    'main',
                    BabblingBrook.Client.User.tutorial_level_name
                );
                var title = jQuery('#level_completed_title_template').html();
                var details = jQuery('#level_completed_details_template>div').clone();
                jq_tutorial_dialog = jQuery('<div></div>');
                jq_tutorial_dialog.html('')
                jq_tutorial_dialog.dialog({
                    autoOpen: true,
                    title: title,
                    appendTo : '#tutorial_placement',
                    closeOnEscape: false,
                    dragStart : function () {
                        jQuery('div').removeClass('top-dialogue');
                        jQuery(this).parent().addClass('top-dialogue');
                    },
                    focus : function () {
                        jQuery('div').removeClass('top-dialogue');
                        jQuery(this).parent().addClass('top-dialogue');
                    },
                    open: function(event, ui) { jQuery('#tutorial_placement .ui-dialog-titlebar-close').remove(); }
                });
                jq_tutorial_dialog
                    .html(details)
                    .dialog('option', 'height', 'auto')
                    .dialog('option', 'closeText', '')
                    .dialog('option', 'width', 450)
                    .dialog('moveToTop');

                jQuery('#next_quest').click(function () {
                    closeTutorial();
                    reshowTutorial(true);
                });
                jQuery('#wait_here').click(onWaitHere);
            }
        );
    };

    var onWaitHere = function () {
        closeTutorial();
        user_is_waiting = true;
        var title = jQuery('#wait_here_title_template').html();
        var details = jQuery('#wait_here_details_template>div').clone();
        jq_tutorial_dialog = jQuery('<div></div>');
        jq_tutorial_dialog.html('')
        jq_tutorial_dialog.dialog({
            autoOpen: true,
            title: title,
            appendTo : '#tutorial_placement',
            closeOnEscape: false,
            dragStart : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            },
            focus : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            },
            open: function(event, ui) { jQuery('#tutorial_placement .ui-dialog-titlebar-close').remove(); }
        });
        jq_tutorial_dialog
            .html(details)
            .dialog('option', 'height', 'auto')
            .dialog('option', 'closeText', '')
            .dialog('option', 'width', 450)
            .dialog('moveToTop');

        jQuery('#wait_here_ok').click(closeTutorial);
        jQuery('#turn_off_tutorials').click(exitTutorials);
    }

    /**
     * Reshow a tutorial that has already been displayed.
     *
     * @param {boolean} redirect Should the tutorial page be reloaded.
     *
     * @returns {undefined}
     */
    var reshowTutorial = function (redirect) {
        if (BabblingBrook.Client.User.tutorial_level_name === '') {
            showInitialMessage();
        } else {
            if (user_is_waiting === true) {
                redirect = true;
            }
            user_is_waiting = false;
            displayTutorial(redirect);
        }
    };

    /**
     * Adds a top nav menu item without reloading the page.
     *
     * @param {string} item_name The item name. Used to generate the template.
     * @param {string} after_id The id of the nav item before this one.
     *
     * @returns {undefined}
     */
    var addTopNavItem = function (item_name, before_id) {
        var jq_item = jQuery('#' + item_name + '_top_nav_template>li').clone();
        jQuery('#' + before_id).after(jq_item);
    };

    /**
     * Set up hooks to other parts of the client code to detect when a tutorial has been finsihed.
     *
     * @returns {void}
     */
    var setupTutorialHooks = function() {
        switch (BabblingBrook.Client.User.tutorial_level_name){
            case 'READ_POSTS':
                setupReadPostsTutorial();
                break;

            case 'VOTE_POSTS':
                setupVotePostsTutorial();
                break;

            case 'STREAM_NAV':
                setupStreamBarTutorial();
                break;

            case 'READ_COMMENTS':
                setupReadCommentsTutorial();
                break;

            case 'VOTE_COMMENTS':
                setupVoteCommentsTutorial();
                break;

            case 'MAKE_COMMENT':
                setupMakeCommentTutorial();
                break;

            case 'EDIT_COMMENT':
                setupEditCommentTutorial();
                break;

            case 'LINK_COMMENTS':
                setupLinkCommentTutorial();
                break;

            case 'MAKE_SELF_POST':
                setupSelfPostTutorial();
                break;

            case 'STREAM_SORT':
                setupStreamSortTutorial();
                break;

            case 'SUGGESTION_MESSAGES':
                setupSuggestionMessagesTutorial();
                break;

            case 'SUBSCRIBE_LINK':
                setupStreamSubscribeTutorial();
                break;

            case 'EDIT_SUBSCRIPTIONS_LINK':
                setupEditSubscriptionsLinkTutorial();
                break;

            case 'FIND_SEARCH_STREAMS':
                setupFindSearchStreamsTutorial();
                break;

            case 'SEARCH_STREAMS':
                setupSearchStreamsTutorial();
                break;

            case 'CHANGE_STREAM_SORT_RHTYHM':
                setupChangeStreamSortRhythmTutorial();
                break;

            case 'CHANGE_STREAM_MODERATION_RING':
                setupChangeStreamModerationRingTutorial();
                break;

            case 'BUGS':
                setupBugsTutorial();
                break;

            case 'KINDRED_SCORE':
                setupKindredScoreTutorial();
                break;

            case 'VIEW_PROFILE':
                setupViewProfileTutorial();
                break;

            case 'EDIT_PROFILE':
                setupEditProfileTutorial();
                break;

            case 'PRIVATE_POSTS':
                setupPrivatePostsTutorial();
                break;

            case 'READ_PRIVATE_POSTS':
                setupReadPrivatePostsTutorial();
                break;

            case 'META_LINKS':
                setupMetaLinksTutorial();
                break;

            case 'MAKE_STREAMS':
                setupMakeStreamsTutorial();
                break;

            case 'RING_MEMBERSHIP':
                setupRingMembershipTutorial();
                break;

            case 'MODERATING_POSTS':
                setupModeratingPostsTutorial();
                break;

            case 'MAKING_RINGS':
                setupMakingRingsTutorial();
                break;

            case 'MAKE_RHYTHMS':
                setupMakeRhythmsTutorial();
                break;

            case 'SETTINGS':
                setupSettingsTutorial();
                break;

            case 'FINISHED':
                setupFinishedTutorial();
                break;

        }

    };

    /**
     * Setsup the tutorial for reading posts.
     *
     * @returns {void}
     */
    var setupReadPostsTutorial = function() {
        var posts_clicked = 0
        jQuery('body').on('mousedown', '.post-thumbnail-container, .post>.title>a', function(event) {
            // Include middle click but not right click.
            if (event.which === 3) {
                return;
            }
            // Don't include the closing of a thumbnail.
            if (jQuery(this).hasClass('large-thumbnail') === true) {
                return;
            }
            var current_stream_url = window.location.hostname + window.location.pathname;
            var current_stream_name = BabblingBrook.Library.makeStreamFromUrl(current_stream_url);
            var tutorial_stream_url =
                jQuery('#level_' + BabblingBrook.Client.User.tutorial_level_name + '_template .tutorial-location')
                    .attr('data-location');
                var tutorial_name = BabblingBrook.Library.extractName(tutorial_stream_url);
                var tutorial_username = BabblingBrook.Library.extractUsername(tutorial_stream_url);
            if (current_stream_name.name === tutorial_name
                && current_stream_name.username === tutorial_username
            ) {
                posts_clicked++;
                if (posts_clicked === 3) {
                    levelUp();
                    jQuery('body').off('click', '.post-thumbnail-container, .post>.title>a');
                }
            }
        });
    };

    /**
     * Setsup the tutorial for voting on posts.
     *
     * @returns {void}
     */
    var setupVotePostsTutorial = function() {

        var up_vote_count = 0;
        var down_vote_count = 0;
        var oldHook = BabblingBrook.Client.Component.Value.Arrows.onTakenHook;

        /**
         * Override for BabblingBrook.Client.Component.Value.Arrows.onTakenHook
         *
         * @param {type} jq_post. See BabblingBrook.Client.Component.Value.Arrows.onTakenHook for details.
         * @param {type} post_data See BabblingBrook.Client.Component.Value.Arrows.onTakenHook for details.
         * @param {integer} The id of the field that was taken.
         *
         * @returns {void}
         */
        var onVotePostsTutorialVoted = function(jq_post, post, field_id) {
            var value = post.takes[field_id].value;
            if (value > 0) {
                up_vote_count++;
            } else if (value < 0) {
                down_vote_count++;
            }
            if (up_vote_count >=3 && down_vote_count >= 1) {
                levelUp();
                BabblingBrook.Client.Component.Value.Arrows.onTakenHook = oldHook;
            }
        };

        BabblingBrook.Client.Component.Value.Arrows.onTakenHook = onVotePostsTutorialVoted;
    };

    /**
     * Setsup the tutorial for stream subscriptions
     *
     * @returns {void}
     */
    var setupStreamBarTutorial = function() {
        BabblingBrook.Client.Component.StreamNav.setup();

        var oldHook = BabblingBrook.Client.Page.Stream.Stream.onStreamLoadedHook;

        /**
         * Override for BabblingBrook.Client.Component.Value.Arrows.onTakenHook
         *
         * @param {type} jq_post. See BabblingBrook.Client.Component.Value.Arrows.onTakenHook for details.
         * @param {type} post_data See BabblingBrook.Client.Component.Value.Arrows.onTakenHook for details.
         * @param {integer} The id of the field that was taken.
         *
         * @returns {void}
         */
        var onStreamLoadedHook = function(stream) {
            if (typeof stream !== 'undefined' && stream.name !== 'beautiful') {
                levelUp();
                BabblingBrook.Client.Page.Stream.Stream.onStreamLoadedHook = oldHook;
            }
        };
        BabblingBrook.Client.Page.Stream.Stream.onStreamLoadedHook = onStreamLoadedHook;
    }

    /**
     * Sets up the tutorial for viewing comments.
     *
     * @returns {void}
     */
    var setupReadCommentsTutorial = function() {
        // Nothing here. See readCommentsHack function
    };


    /**
     * Setsup the tutorial for voting on comments.
     *
     * @returns {void}
     */
    var setupVoteCommentsTutorial = function() {
        var vote_count = 0;
        var oldHook = BabblingBrook.Client.Component.Value.Arrows.onTakenHook;

        /**
         * Override for BabblingBrook.Client.Component.Value.Arrows.onTakenHook
         *
         * @param {type} jq_post. See BabblingBrook.Client.Component.Value.Arrows.onTakenHook for details.
         * @param {type} post_data See BabblingBrook.Client.Component.Value.Arrows.onTakenHook for details.
         * @param {integer} The id of the field that was taken.
         *
         * @returns {void}
         */
        var onVoteCommentsTutorialVoted = function(jq_post, post, field_id) {
            if (post.stream_name === 'comments') {
                vote_count++
            }

            if (vote_count >=3 ) {
                levelUp();
                BabblingBrook.Client.Component.Value.Arrows.onTakenHook = oldHook;
            }
        };

        BabblingBrook.Client.Component.Value.Arrows.onTakenHook = onVoteCommentsTutorialVoted;
    };

    /**
     * Setsup the tutorial for voting on comments.
     *
     * @returns {void}
     */
    var setupMakeCommentTutorial = function() {
        var oldHook = BabblingBrook.Client.Component.MakePost.onInsertHook;

        /**
         * Override for BabblingBrook.Client.Component.MakePost.onInsertHook
         *
         * @param {object} The post that has been created.
         *
         * @returns {void}
         */
        var onMakeComment = function(post) {
            levelUp();
            BabblingBrook.Client.Component.MakePost.onInsertHook = oldHook;
        };

        BabblingBrook.Client.Component.MakePost.onInsertHook = onMakeComment;
    };


    /**
     * Sets up the tutorial for voting on comments.
     *
     * @returns {void}
     */
    var setupEditCommentTutorial = function() {
        var oldHook = BabblingBrook.Client.Component.MakePost.onInsertHook;

        /**
         * Override for BabblingBrook.Client.Component.MakePost.onInsertHook
         *
         * @param {object} The post that has been created.
         *
         * @returns {void}
         */
        var onCommentEdited = function(post) {
            if (post.revision !== '1') {
                levelUp();
                BabblingBrook.Client.Component.MakePost.onInsertHook = oldHook;
            }
        };

        BabblingBrook.Client.Component.MakePost.onInsertHook = onCommentEdited;
    };

    /**
     * Sets up the tutorial for the link to a comments post.
     *
     * @returns {void}
     */
    var setupLinkCommentTutorial = function () {
        // nothing here. See readCommentsHack below
    }

    /**
     * Sets up the tutorial for voting on comments.
     *
     * @returns {void}
     */
    var setupSelfPostTutorial = function() {
        var oldHook = BabblingBrook.Client.Component.MakePost.onInsertHook;

        /**
         * Override forBabblingBrook.Client.Component.MakePost.onInsertHook
         *
         * @param {object} The post that has been created.
         *
         * @returns {void}
         */
        var onPostMade = function(post) {
            if (post.stream_name === 'favourite places') {
                levelUp();
                BabblingBrook.Client.Component.MakePost.onInsertHook = oldHook;
            }
        };

        BabblingBrook.Client.Component.MakePost.onInsertHook = onPostMade;
    };

    /**
     * Sets up the tutorial for the user to select a different sort method.
     *
     * @returns {void}
     */
    var setupStreamSortTutorial = function () {
        var oldHook = BabblingBrook.Client.Component.StreamSideBar.onRhythmSelectedHook;

        /**
         * Override forBabblingBrook.Client.Component.StreamSideBar.onRhythmSelectedHook
         *
         * @param {object} filter_name The name of the new rhythm filter.
         *
         * @returns {void}
         */
        var onRhythmSelectedHook = function(filter_name) {
            if (filter_name.name !== 'random') {
                levelUp();
                addTopNavItem('suggestions', 'about_top_nav');
                BabblingBrook.Client.Component.Messages.remapSuggestionLink();
                BabblingBrook.Client.Component.StreamSideBar.onRhythmSelectedHook = oldHook;
            }
        };

        BabblingBrook.Client.Component.StreamSideBar.onRhythmSelectedHook = onRhythmSelectedHook;
    };

    /**
     * Callback for redirecting the user to the suggested stream.
     *
     * @param {object} suggestion The suggestion that is being redirected to.
     *
     * @returns {void}
     */
    var onViewStreamClicked = function (stream) {
        var stream_url = BabblingBrook.Library.makeStreamUrl(stream, '', false);
        BabblingBrook.Client.Core.Ajaxurl.redirect(stream_url);
        return false;
    };

    /**
     * Callback for subscribing to a stream in the suggestion tutorial.
     *
     * @param {type} stream
     *
     * @returns {undefined}
     */
    var onTurorialSubscribeStreamClicked = function (stream) {
        BabblingBrook.Client.User.StreamSubscriptions['4'] = news_stream;
        var jq_stream_nav = jQuery('#level_SUGGESTION_MESSAGES_stream_nav_template>li').clone();
        var stream_url = BabblingBrook.Library.makeStreamUrl(stream, '', false);
        jQuery('a', jq_stream_nav)
            .attr('title', stream_url)
            .attr('href', stream_url)
            .text(stream.name);
        jQuery('#streams_nav>ul>li.more').before(jq_stream_nav);
        levelUp();
        return true;
    };

    /**
     * Callback for declining or clicking 'not now' to a stream in the suggestion tutorial.
     *
     * @param {type} stream
     *
     * @returns {undefined}
     */
    var onTurorialSuggestMessage = function (template) {
        var details = jQuery('#' + template + '').html();
        jQuery(".ui-dialog-content").dialog('close');
        jq_tutorial_dialog = jQuery('<div></div>');
        jq_tutorial_dialog.html('')
        jq_tutorial_dialog.dialog({
            autoOpen: true,
            title: '',
            appendTo : '#tutorial_placement',
            closeOnEscape: false,
            open: function(event, ui) { jQuery('#tutorial_placement .ui-dialog-titlebar-close').remove(); },
            dragStart : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            },
            focus : function () {
                jQuery('div').removeClass('top-dialogue');
                jQuery(this).parent().addClass('top-dialogue');
            }
        });
        jq_tutorial_dialog
            .html(details)
            .dialog('option', 'height', 'auto')
            .dialog('option', 'closeText', '')
            .dialog('option', 'width', 450)
            .dialog('moveToTop');

        jQuery('#suggestion_tutorial_ok').click(function () {
            jq_tutorial_dialog.dialog('destroy');
            jq_tutorial_dialog = false;
        });

        return false;
    };

    /**
     * Sets up the tutorial for the user to view and act on a suggestion message.
     *
     * @returns {void}
     */
    var setupSuggestionMessagesTutorial = function () {

        var jq_message = jQuery('#suggestion_stream_message_template>div').clone();
        var stream = {
            domain : window.location.hostname,
            username : 'sky',
            name : 'news',
            version : 'latest/latest/latest'
        };
        var stream_url = window.location.hostname + '/sky/stream/news/latest/latest/latest';
        jQuery('a.suggestion-message', jq_message)
            .attr('href', 'http://' + stream_url)
            .attr('title', stream_url)
            .text(stream.name);
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'suggestion',
            message : jq_message.html(),
            buttons : [
            {
                name : 'View',
                callback : onViewStreamClicked.bind(null, stream)
            },
            {
                name : 'Subscribe',
                callback : onTurorialSubscribeStreamClicked.bind(null, stream)
            },
            {
                name : 'No Thanks',
                callback : onTurorialSuggestMessage.bind(null, 'level_SUGGESTION_MESSAGES_declined_template')
            },
            {
                name : 'Not Now',
                callback : onTurorialSuggestMessage.bind(null, 'level_SUGGESTION_MESSAGES_notnow_template')
            }
            ]
        });
    };


    /**
     * Sets up the tutorial for the user to subscribe to a stream from the sidebar.
     *
     * @returns {void}
     */
    var setupStreamSubscribeTutorial = function () {
        var oldHook = BabblingBrook.Client.Component.StreamSideBar.onSubscribeHook;

        /**
         * Override for BabblingBrook.Client.Component.StreamSideBar.onSubscribeHook
         *
         * @param {object} jq_subscribe A jQuery object representing the subscribe/unsubscribe link.
         * @param {object} stream_data Data about the stream. See BabblingBrook.models.stream for a full description.
         *
         * @returns {void}
         */
        var onSubscribeHook = function(jq_subscribe, stream_data) {
            if (stream_data.name === 'babbling brook' && stream_data.username === 'sky') {
                jQuery('a', jq_subscribe).text('Unsubscribe');
                BabblingBrook.Client.User.StreamSubscriptions[3] = babbling_brook_stream;
                var jq_stream_nav = jQuery('#level_SUGGESTION_MESSAGES_stream_nav_template>li').clone();
                var stream_url = BabblingBrook.Library.makeStreamUrl(stream_data, '', false);
                jQuery('a', jq_stream_nav)
                    .attr('title', stream_url)
                    .attr('href', stream_url)
                    .text(stream_data.name);
                jQuery('#streams_nav>ul>li.more').before(jq_stream_nav);
                levelUp();
                BabblingBrook.Client.Component.StreamSideBar.onSubscribeHook = oldHook;
            }
            return true;
        };

        BabblingBrook.Client.Component.StreamSideBar.onSubscribeHook = onSubscribeHook;
    };


    /**
     * Sets up the tutorial for the user to click on the link to their subscriptions page.
     *
     * @returns {void}
     */
    var setupEditSubscriptionsLinkTutorial = function () {
        if (typeof original_stream_subscriptions !== 'undefined') {
            BabblingBrook.Client.User.StreamSubscriptions = original_stream_subscriptions;
        }
        BabblingBrook.Client.Component.StreamNav.reshow();
        BabblingBrook.Client.Core.Loaded.onStreamSubscriptionsPageLoaded(levelUp);
    };

    /**
     * Setsup the tutorial for a users finding the search link to subscribe to a new stream
     * on their stream subscriptions page.
     *
     * @returns {void}
     */
    var setupFindSearchStreamsTutorial = function () {

        var oldHook = BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSubscribedHook;

        /**
         * Override for BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSearchOpenedHook
         *
         * @returns {void}
         */
        var onStreamSearchOpenedHook = function() {
            levelUp();
            BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSearchOpenedHook = oldHook;
            return true;
        };

        BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSearchOpenedHook = onStreamSearchOpenedHook;

    };

    /**
     * Setsup the tutorial for a users subscribing a new stream on their stream subscriptions page.
     *
     * @returns {void}
     */
    var setupSearchStreamsTutorial = function () {

        BabblingBrook.Client.Core.Loaded.onStreamSubscriptionsPageLoaded(function () {
            BabblingBrook.Client.Page.User.StreamSubscriptions.openStreamSearch();
            var oldHook = BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSubscribedHook;

            /**
             * Override for BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSubscribedHook
             *
             * @param {object} jq_subscribe A jQuery object representing the subscribe/unsubscribe link.
             * @param {object} stream_data Data about the stream. See BabblingBrook.models.stream for a full description.
             *
             * @returns {void}
             */
            var onStreamSubscribedHook = function(jq_subscribe, stream_data) {
                levelUp();
                BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSubscribedHook = oldHook;
                return true;
            };

            BabblingBrook.Client.Page.User.StreamSubscriptions.onStreamSubscribedHook = onStreamSubscribedHook;
        });
    };

    /**
     * Sets up the tutorial for slecting a new sort rhythm for a stream.
     *
     * @returns {void}
     */
    var setupChangeStreamSortRhythmTutorial = function (){
        var oldHook = BabblingBrook.Client.Page.User.StreamSubscriptions.onRhythmSubscribedHook;

        /**
         * Override for BabblingBrook.Client.Page.User.StreamSubscriptions.onRhythmSubscribedHook
         *
         * @param {object} jq_subscribe A jQuery object representing the subscribe/unsubscribe link.
         * @param {object} stream_data Data about the stream. See BabblingBrook.models.stream for a full description.
         *
         * @returns {void}
         */
        var onRhythmSubscribedHook = function() {
            levelUp();
            BabblingBrook.Client.Page.User.StreamSubscriptions.onRhythmSubscribedHook = oldHook;
            return true;
        };

        BabblingBrook.Client.Page.User.StreamSubscriptions.onRhythmSubscribedHook = onRhythmSubscribedHook;
    };


    /**
     * Sets up the tutorial for selecting a new moderation ring rhythm for a stream.
     *
     * @returns {void}
     */
    var setupChangeStreamModerationRingTutorial = function (){
        BabblingBrook.Client.Core.Loaded.onStreamSubscriptionsLoaded(function () {
            var oldHook = BabblingBrook.Client.Page.User.StreamSubscriptions.onModerationRingSubscribedHook;

            /**
             * Override for BabblingBrook.Client.Page.User.StreamSubscriptions.onModerationRingSubscribedHook
             *
             * @param {object} jq_subscribe A jQuery object representing the subscribe/unsubscribe link.
             * @param {object} stream_data Data about the stream. See BabblingBrook.models.stream for a full description.
             *
             * @returns {void}
             */
            var onModerationRingSubscribedHook = function() {
                addTopNavItem('bugs', 'login');
                BabblingBrook.Client.Component.ReportBug.rebindBugLink();
                levelUp();
                BabblingBrook.Client.Page.User.StreamSubscriptions.onModerationRingSubscribedHook = oldHook;
                return true;
            };

            BabblingBrook.Client.Page.User.StreamSubscriptions.onModerationRingSubscribedHook = onModerationRingSubscribedHook;
        });
    };

    /**
     * Setup the bugs tutorial
     *
     * @returns {undefined}
     */
    var setupBugsTutorial = function () {
        jQuery('#turtorial_error').click(function (event) {
            event.preventDefault();
            BabblingBrook.Client.Component.Messages.addMessage({
                type : 'error',
                message : 'This is an example error message.'
            });
        });

        var oldHook = BabblingBrook.Client.Component.MakePost.makeFakePostHook;

        /**
         * Override for BabblingBrook.Client.Component.MakePost.makeFakePostHook
         *
         * @param {object} post A jQuery object representing the subscribe/unsubscribe link.
         *
         * @returns {void}
         */
        var makeFakePostHook = function(post) {
            if (post.stream.name === 'bugs' && post.stream.username === 'sky') {
                levelUp();
                BabblingBrook.Client.Component.MakePost.makeFakePostHook = oldHook;
                jQuery('.ui-dialog>#bug_post_container').parent().remove();
                return true;
            }
            return false;
        };

        BabblingBrook.Client.Component.MakePost.makeFakePostHook = makeFakePostHook;

    };

    /**
     * Sets up the tutorial for the user to click on the link to their subscriptions page.
     *
     * @returns {void}
     */
    var setupKindredScoreTutorial = function () {
        //BabblingBrook.Client.Component.StreamNav.reshow();
        BabblingBrook.Client.Core.Loaded.onProfileLoaded(levelUp);
    };

    /**
     * Sets up the tutorial for the user to add a tag on the profile page
     *
     * @returns {void}
     */
    var setupViewProfileTutorial = function () {
        BabblingBrook.Client.Core.Loaded.onProfileLoaded(function () {
            var oldHook = BabblingBrook.Client.Page.User.Profile.onTaggedHook;

            /**
             * Override for BabblingBrook.Client.Page.User.Profile.onTaggedHook
             *
             * @param {object} stream A stream name object for the tag stream.
             *
             * @returns {void}
             */
            var onTaggedHook = function(stream) {
                if (stream.name === 'the tutorial made me do it') {
                    levelUp();
                    BabblingBrook.Client.Page.User.Profile.onTaggedHook = oldHook;
                }
            };
            BabblingBrook.Client.Page.User.Profile.onTaggedHook = onTaggedHook;
        });
    };

    /**
     * Sets up the tutorial for the user to add a tag on the profile page
     *
     * @returns {void}
     */
    var setupEditProfileTutorial = function () {
        if (jQuery('#top_nav #profile_top_nav_link').length < 1) {
            addTopNavItem('profile', 'suggestions_link');
        }

        BabblingBrook.Client.Core.Loaded.onEditProfileLoaded(function () {
            var oldHook = BabblingBrook.Client.Page.User.EditProfile.onDescriptionEditedHook;

            /**
             * Override for BabblingBrook.Client.Page.EditProfile.onDescriptionEditedHook
             *
             * @returns {void}
             */
            var onDescriptionEditedHook = function() {
                levelUp();
                BabblingBrook.Client.Page.User.EditProfile.onDescriptionEditedHook = oldHook;
            };

            BabblingBrook.Client.Page.User.EditProfile.onDescriptionEditedHook = onDescriptionEditedHook;
        });
    };

    /**
     * Sets up the tutorial for making private posts.
     *
     * @returns {void}
     */
    var setupPrivatePostsTutorial = function () {
        jQuery('#tutorial_private_posts_username')
            .text(BabblingBrook.Client.User.username + '@' + BabblingBrook.Client.User.domain);

        var oldHook = BabblingBrook.Client.Component.MakePost.onInsertHook;

        /**
         * Override for BabblingBrook.Client.Component.MakePost.onInsertHook
         *
         * @param {object} post The post that is on display.
         *
         * @returns {void}
         */
        var onInsertHook = function(post, private_addresses) {
            if (private_addresses[0] === BabblingBrook.Client.User.username + '@' + BabblingBrook.Client.User.domain) {
                levelUp();
                addTopNavItem('posts', 'suggestions_link');
                BabblingBrook.Client.Component.MakePost.onInsertHook = oldHook;
            }
        };

        BabblingBrook.Client.Component.MakePost.onInsertHook = onInsertHook;
    };

    /**
     * Sets up the tutorial for reading private posts.
     *
     * @returns {void}
     */
    var setupReadPrivatePostsTutorial = function () {
        var repeatTillFound = function () {
            if (typeof BabblingBrook.Client.Page.Mail === 'undefined'
                || typeof BabblingBrook.Client.Page.Mail.LocalInbox === 'undefined'
            ) {
                setTimeout(repeatTillFound, 2000);
            } else {
                 levelUp();
            }
        };
        repeatTillFound();
    };

    /**
     * Sets up the tutorial for visiting meta links.
     *
     * @returns {void}
     */
    var setupMetaLinksTutorial = function () {
        BabblingBrook.Client.Core.Loaded.onPostWithTreeLoaded(function () {

            BabblingBrook.Client.Page.Post.PostWithTree.registerOnRootPostDisplayHook(function (jq_post, post, stream) {
                if (stream.name === 'meta stream for streams') {
                    addTopNavItem('streams', 'suggestions_link');
                    levelUp();
                }
            });
        });
    };


    /**
     * Sets up the tutorial for making a stream.
     *
     * @returns {void}
     */
    var setupMakeStreamsTutorial = function () {
        BabblingBrook.Client.Core.Loaded.onCreateStreamLoaded(function () {
            var oldHook = BabblingBrook.Client.Page.ManageStream.Create.onStreamCreatedHook;
            /**
             * Override for BabblingBrook.Client.Page.ManageStream.Create.onStreamCreatedHook;
             *
             * @returns {void}
             */
            var onStreamCreatedHook = function() {
                levelUp();
                addTopNavItem('rings', 'suggestions_link');
                BabblingBrook.Client.Page.ManageStream.Create.onStreamCreatedHook = oldHook;
            };
            BabblingBrook.Client.Page.ManageStream.Create.onStreamCreatedHook = onStreamCreatedHook;
        });
    };

    /**
     * Sets up the tutorial for the user join up with a ring.
     *
     * @returns {void}
     */
    var setupRingMembershipTutorial = function () {
        BabblingBrook.Client.Core.Loaded.onProfileLoaded(function () {
            var oldHook = BabblingBrook.Client.Page.User.Profile.onJoindRingHook;

            /**
             * Override for BabblingBrook.Client.Page.User.Profile.onJoindRingHook
             *
             * @param {string} ring_name The name of a ring that the user has joined as a member.
             *
             * @returns {void}
             */
            var onJoindRingHook = function(ring_name) {
                if (ring_name === 'tutorial spam') {
                    levelUp();
                    BabblingBrook.Client.Page.User.Profile.onJoindRingHook = oldHook;
                }
            };
            BabblingBrook.Client.Page.User.Profile.onJoindRingHook = onJoindRingHook;
        });
    };

    /**
     * Tutorial for the user who just joined the turorial ring to mark something as spam.
     *
     * @returns {undefined}
     */
    var setupModeratingPostsTutorial = function () {
        var oldHook = BabblingBrook.Client.Component.PostRings.onTakenHook;

        /**
         * Override for BabblingBrook.Client.Component.PostRings.onTakenHook
         *
         * @returns {void}
         */
        var onTakenHook = function(ring_name) {
            levelUp();
            BabblingBrook.Client.Component.PostRings.onTakenHook = oldHook;
        };
        BabblingBrook.Client.Component.PostRings.onTakenHook = onTakenHook;
    }

    /**
     * Tutorial for making rings.
     *
     * @returns {undefined}
     */
    var setupMakingRingsTutorial = function () {
        BabblingBrook.Client.Core.Loaded.onRingAdminLoaded(function () {
            addTopNavItem('rhythms', 'streams_top_nav_link');
            levelUp();
        });
    };

    /**
     * Tutorial for making rhythms.
     *
     * @returns {undefined}
     */
    var setupMakeRhythmsTutorial = function () {
        BabblingBrook.Client.Core.Loaded.onRhythmListLoaded(function () {
            addTopNavItem('settings', 'profile_top_nav_link');
            levelUp();
        });
    };

    /**
     * Tutorial for loading the settings page
     *
     * @returns {undefined}
     */
    var setupSettingsTutorial = function () {
        BabblingBrook.Client.Core.Loaded.onSettingsLoaded(function () {
            levelUp();
        });
    };

    /**
     * Tutorial for finishing tutorials
     *
     * @returns {undefined}
     */
    var setupFinishedTutorial = function () {
        jQuery('#go_to_start_tutorial').click(function () {
            BabblingBrook.Library.post(
                '/' + BabblingBrook.Client.User.username + '/restarttutorials',
                {},
                function (level_data) {
                    window.location = window.location;
                }
            );
        });
    };

    return {

        construct : function () {
            // The tutorial page reloads can interfere with the loggout process.
            if (window.location.pathname === '/site/logout') {
                return;
            }

            BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
                jQuery('#show_tutorial>a').click(reshowTutorial.bind(null, false));
                if (BabblingBrook.Client.User.tutorial_set === ''
                    || BabblingBrook.Client.User.tutorial_level_name === ''
                ) {
                    return;
                }
                BabblingBrook.Client.Core.FeatureSwitches.setupTutorial(
                    'main',
                    BabblingBrook.Client.User.tutorial_level_name
                );
                deferred_tutorial_loaded.resolve();
                displayTutorial();
            });
        },

        /**
         * Called when a comment tree has loaded.
         *
         * This is a hack, should be using a hook, but can't as the diplaytree class is only loaded when it is used.
         *
         * @param {object} stream The stream that the post is in.
         * @param {object} post The root post being displayed.
         *
         * @returns {void}
         */
        readCommentsHack : function(stream, post) {

            if (BabblingBrook.Client.User.tutorial_level_name === 'READ_COMMENTS') {
                if (stream.domain === tutorial_user.domain
                    && stream.username === tutorial_user.username
                    && stream.name === 'beautiful'
                ) {
                    setTimeout(function () {
                        levelUp();
                    }, 2000);
                }
            } else if (BabblingBrook.Client.User.tutorial_level_name === 'LINK_COMMENTS') {
                if (post.parent_id !== post.post_id && typeof post.parent_id === 'string') {
                    setTimeout(function () {
                        levelUp();
                    }, 2000);
                }
            }
        },

        /**
         * Changes the current stream subscriptions for the current tutorial set and level
         *
         * @param {function} [onNavReady] Callback for when the nav data is ready.
         *
         * @returns {}
         */
        setTutorialNavBar : function (onNavReady) {
            BabblingBrook.Client.Core.Loaded.onStreamSubscriptionsLoaded(function () {
                deferred_tutorial_loaded.done(function () {
                    if (typeof original_stream_subscriptions === 'undefined') {
                        original_stream_subscriptions = BabblingBrook.Client.User.StreamSubscriptions;
                    }
                    if (BabblingBrook.Client.User.tutorial_set === 'main'
                        && BabblingBrook.Settings.feature_switches['EDIT_SUBSCRIPTIONS_LINK'] === false
                    ) {
                        BabblingBrook.Client.User.StreamSubscriptions = {
                            '0' : {
                                domain : window.location.hostname,
                                stream_subscription_id : 1,
                                filters : {
                                    '0' : {
                                        filter_subscription_id : '0',
                                        description : 'Random sorting of posts',
                                        domain : window.location.hostname,
                                        locked : true,
                                        name : 'random',
                                        params : [],
                                        priority : 100021,
                                        url : window.location.hostname + '/sky/rhythm/random/latest/latest/latest/json',
                                        username : 'sky',
                                        version : {
                                            major : 'latest',
                                            minor : 'latest',
                                            patch : 'latest',
                                        }
                                    },
                                    '1' : {
                                        filter_subscription_id : '1',
                                        description : 'Sort posts by newest first',
                                        domain : window.location.hostname,
                                        locked : true,
                                        name : 'newest',
                                        params : [],
                                        priority : 100002,
                                        url : window.location.hostname + '/sky/rhythm/newest/latest/latest/latest/json',
                                        username : 'sky',
                                        version : {
                                            major : 'latest',
                                            minor : 'latest',
                                            patch : 'latest',
                                        }
                                    },
                                    '2' : {
                                        filter_subscription_id : '2',
                                        description : 'Sort posts by oldest first',
                                        domain : window.location.hostname,
                                        locked : true,
                                        name : 'oldest',
                                        params : [],
                                        priority : 100003,
                                        url : window.location.hostname + '/sky/rhythm/oldest/latest/latest/latest/json',
                                        username : 'sky',
                                        version : {
                                            major : 'latest',
                                            minor : 'latest',
                                            patch : 'latest',
                                        }
                                    },
                                    '3' : {
                                        filter_subscription_id : '3',
                                        description : 'Sorts the latest five thousand posts by popularity.',
                                        domain : window.location.hostname,
                                        locked : true,
                                        name : 'popular recently',
                                        params : [],
                                        priority : 100004,
                                        url : window.location.hostname + '/sky/rhythm/popular+recently/latest/latest/latest/json',
                                        username : 'sky',
                                        version : {
                                            major : 'latest',
                                            minor : 'latest',
                                            patch : 'latest',
                                        }
                                    }
                                },
                                locked: false,
                                moderation_rings : [],
                                name : 'beautiful',
                                url : window.location.hostname + '/sky/stream/beautiful/latest/latest/latest/json',
                                user_stream_subscription_id : null,
                                username : 'sky',
                                version : {
                                    major : 'latest',
                                    minor : 'latest',
                                    patch : 'latest',
                                },
                                rings : {}

                            },
                            '1' : {
                                domain : window.location.hostname,
                                filters : {
                                    '0' : {
                                        filter_subscription_id : '0',
                                        description : 'Sort posts by newest first',
                                        domain : window.location.hostname,
                                        locked : true,
                                        name : 'newest',
                                        params : [],
                                        priority : 100001,
                                        url : window.location.hostname + '/sky/rhythm/newest/latest/latest/latest/json',
                                        username : 'sky',
                                        version : {
                                            major : 'latest',
                                            minor : 'latest',
                                            patch : 'latest',
                                        }
                                    }
                                },
                                locked: false,
                                moderation_rings : [],
                                name : 'favourite places',
                                url : window.location.hostname + '/sky/stream/favourite+places/latest/latest/latest/json',
                                user_stream_subscription_id : null,
                                username : 'sky',
                                version : {
                                    major : 'latest',
                                    minor : 'latest',
                                    patch : 'latest',
                                },
                                rings : {}

                            },
                            '2' : {
                                domain : window.location.hostname,
                                stream_subscription_id : 1,
                                filters : {
                                    '0' : {
                                        filter_subscription_id : '0',
                                        description : 'Sort posts by newest first',
                                        domain : window.location.hostname,
                                        locked : true,
                                        name : 'newest',
                                        params : [],
                                        priority : 100001,
                                        url : window.location.hostname + '/sky/rhythm/newest/latest/latest/latest/json',
                                        username : 'sky',
                                        version : {
                                            major : 'latest',
                                            minor : 'latest',
                                            patch : 'latest',
                                        }
                                    }
                                },
                                locked: false,
                                moderation_rings : [],
                                name : 'cobalt meta',
                                url : window.location.hostname + '/sky/stream/cobalt+meta/latest/latest/latest/json',
                                user_stream_subscription_id : null,
                                username : 'sky',
                                version : {
                                    major : 'latest',
                                    minor : 'latest',
                                    patch : 'latest',
                                },
                                rings : {}

                            },
                        };
                        // Include the news stream id the SUGGESTION_MESSAGES tutorial has been done.
                        if (BabblingBrook.Settings.feature_switches['SUBSCRIBE_LINK'] === true) {
                            BabblingBrook.Client.User.StreamSubscriptions[4] = news_stream;
                        }
                    }
                    if (typeof onNavReady === 'function') {
                        onNavReady();
                    }
                });
            });
        }
    };
}());

