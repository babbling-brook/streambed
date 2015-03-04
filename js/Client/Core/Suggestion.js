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
 * @fileOverview Handles requests for suggestion data from suggestion Rhythms.
 */

/**
 * @namespace Handles requests for suggestion data from suggestion Rhythms.
 * @package JS_Client
 */
BabblingBrook.Client.Core.Suggestion = (function () {
    'use strict';
    /**
     * An array of stored suggestion data indexed by the type of the item suggestions are for.
     * BabblingBrook.Client.Core.SuggestionData.type.name{ suggestion data }.
     * The structure of the suggestion data is dependant on the suggestion type. See protocol for details.
     * @var {object}
     */
    var suggestions = {};

    /**
     * Callback that stores suggestions sent from the domus domain.
     *
     * @param {string} type The type of suggestion Rhythm being used. See protocol for details.
     * @param {function} onSuccess Callback for when the suggestions have been generated.
     * @param {string} [paramaters] The paramaters to be made available to the Rhythm.
     *                            Object structure is dependent on type. See protocol for details.
     * @param {object} suggestion_data.type
     * @param {string} suggestion_data.suggestions The suggestion data.
     *      Structure is different depending on the suggestion type. See protocol for details.
     */
    var cacheSuggestions = function (type, onSuccess, paramaters, suggestion_data) {
        if (typeof paramaters === 'undefined') {
            paramaters = {};
        }
        var string_params = paramaters = JSON.stringify(paramaters);
        BabblingBrook.Library.createNestedObjects(suggestions, [type, string_params]);
        suggestions[type][string_params] = suggestion_data.suggestions;
        onSuccess(suggestion_data.suggestions);
    };

    /**
     * Checks the cache and returns any stored suggestions.
     *
     * @param {string} type The type of suggestion Rhythm being used. See protocol for details.
         * @param {string} [paramaters] The paramaters to be made available to the Rhythm.
         *                            Object structure is dependent on type. See protocol for details.
     *
     * @returns {object|false} The suggestions or false.
     */
    var checkCache = function (type, paramaters) {
        var string_params = JSON.stringify(paramaters);
        if (BabblingBrook.Library.doesNestedObjectExist(suggestions, [type, string_params]) === true) {
            return suggestions[type][string_params];
        } else {
            return false;
        }
    };

    /**
     * Selects the correct cofig url for this type of suggestion.
     *
     * @param {string} type The type of suggestion being requested.
     *
     * @returns {string} The url for this suggestion.
     */
    var getUrlFromType = function (type) {
        switch(type) {
            case 'stream_suggestion':
                return BabblingBrook.Client.User.Config.stream_rhythm_suggestion_url;
                break;

            case 'stream_filter_suggestion':
                return BabblingBrook.Client.User.Config.stream_filter_rhythm_suggestion_url;
                break;

            case 'user_stream_suggestion':
                return BabblingBrook.Client.User.Config.user_stream_rhythm_suggestion_url;
                break;

            case 'stream_ring_suggestion':
                return BabblingBrook.Client.User.Config.stream_ring_rhythm_suggestion_url;
                break;

            case 'ring_suggestion':
                return BabblingBrook.Client.User.Config.ring_rhythm_suggestion_url;
                break;

            case 'user_suggestion':
                return BabblingBrook.Client.User.Config.user_rhythm_suggestion_url;
                break;

            case 'meta_suggestion':
                return BabblingBrook.Client.User.Config.meta_rhythm_suggestion_url;
                break;

            case 'kindred_suggestion':
                return BabblingBrook.Client.User.Config.kindred_rhythm_suggestion_url;
                break;

            default:
                throw 'suggestion type is invalid : ' + type;
        }
    };

    return {

        /**
         * Runs a suggestion Rhythm to generate suggestions.
         *
         * To fetch the results suggestions needs polling until they are present.
         *
         * @param {string} type The type of suggestion Rhythm being used. See protocol for details.
         * @param {function} onSuccess Callback for when the suggestions have been generated.
         * @param {string} [paramaters] The paramaters to be made available to the Rhythm.
         *                            Object structure is dependent on type. See protocol for details.
         */
        fetch : function (type, onSuccess, paramaters) {
            if (typeof paramaters === 'undefined') {
                paramaters = {};
            }
            var cached = checkCache(type, paramaters);
            if (cached !== false) {
                onSuccess(cached);
                return;
            }

            var url = getUrlFromType(type);
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    type : type,
                    paramaters : paramaters,
                    rhythm_url : url
                },
                'GetSuggestions',
                cacheSuggestions.bind(null, type, onSuccess, paramaters)
            );
            return true;
        }
    };

}());
