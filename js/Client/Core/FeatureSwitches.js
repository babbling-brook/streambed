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
 * @fileOverview Code used to switch features on or off.
 * @author Sky Wickenden
 */

/**
 * Shows the bug report form.
 */
BabblingBrook.Client.Core.FeatureSwitches = (function () {
    'use strict';

    var main_tutorial = [
        'NOT_STARTED',
        'READ_POSTS',
        'VOTE_POSTS',
        'STREAM_NAV',
        'READ_COMMENTS',
        'VOTE_COMMENTS',
        'MAKE_COMMENT',  // include cooldown.
        'EDIT_COMMENT',
        'LINK_COMMENTS',
        'MAKE_SELF_POST',
        'STREAM_SORT',
        'SUGGESTION_MESSAGES',
        'SUBSCRIBE_LINK',
        'EDIT_SUBSCRIPTIONS_LINK',
        'FIND_SEARCH_STREAMS',
        'SEARCH_STREAMS',
        'CHANGE_STREAM_SORT_RHTYHM',
        'CHANGE_STREAM_MODERATION_RING',
        'BUGS',
        'KINDRED_SCORE',
        'VIEW_PROFILE',
        'EDIT_PROFILE',
        'PRIVATE_POSTS',
        'READ_PRIVATE_POSTS',
        'META_LINKS',
        'RING_MEMBERSHIP',
        'MODERATING_POSTS',
        'MAKING_RINGS',
        'SETTINGS',
        'TUTORIALS_OFF',
        'APPRENTICESHIP_DONE',
        'POWER_LEVELS_STARTED',
        'MAKE_STREAMS',
        'MAKE_RHYTHMS',
        'FINISHED'
    ];

    /**
     * Switches all options on or off.
     *
     * @param {boolean} direction True = turn all on. False = turn all off.
     *
     * @returns {void}
     */
    var switchAll = function (direction) {
        for(var key in BabblingBrook.Settings.feature_switches) {
            if (BabblingBrook.Settings.feature_switches.hasOwnProperty(key) === true) {
                BabblingBrook.Settings.feature_switches[key] = direction;
            }
        }
    };

    return {

        construct : function () {
        },

        /**
         * Turns features to the correct state for a turorial.
         *
         * @param {string} type the name of the tutorial set.
         * @param {string} level The name of the current tutorial level.
         *
         * @returns {undefined}
         */
        setupTutorial : function(type, level) {
            if (level === false) {
                return;
            }

            var tutorial_set;
            switch (type) {
                case 'main':
                    tutorial_set = main_tutorial;
            }

            var level_index = tutorial_set.indexOf(level);
            if (level_index === -1) {
                throw 'Tutorial name does not exist ' + level + ' for type ' + type;
            }

            switchAll(false);
            for (var i = 0; i <= level_index; i++) {
                BabblingBrook.Settings.feature_switches[tutorial_set[i]] = true;
            }

        }

    };
}());


