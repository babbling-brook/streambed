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
 * @fileOverview Code used for showing suggestions in the message box.
 * @author Sky Wickenden
 */

/**
 * Code used to run suggestion rhythms for showing suggestions in the message box.
 *
 * Every set period of time a suggestion is displayed to the user,
 * If there are none of the required type then new ones are generated.
 * Each type is run in turn.
 * If a suggestion rhythm returns nothing then the next rhythm is run.
 */
BabblingBrook.Client.Component.SuggestionMessage = (function () {
    'use strict';


    var time_last_ran = Math.round(new Date().getTime() / 1000);

    /**
     * The suggestion_type to start with.
     *
     * The multiple should be the same as the number of items in suggestion_type_order.
     *
     * @type {number}
     */
    var current_suggestion_type_id = 3;//Math.floor(Math.random() * 4);

    var suggestion_type_order = [
        'stream_suggestion',
        'stream_filter_suggestion',
        'stream_ring_suggestion',
        'user_suggestion'
    ];

    /**
     * A wait condition for when it is time to run onSuggestionReadyToRun.
     *
     * @returns {boolean} true if it is time to run onSuggestionReadyToRun.
     */
    var onSuggestionWaitCondition = function () {
        var now = Math.round(new Date().getTime() / 1000);
        if (time_last_ran + parseInt(BabblingBrook.Client.User.Config.suggestion_message_rate) < now) {
            return true;
        } else {
            return false;
        }
    };

    /**
     * Generates the paramaters for filter and moderation ring suggestions.
     *
     * If the current url includes a stream then return it as a paramter object, otherwise
     * use a random subscribed stream.
     *
     * @returns {object} The paramater object./
     */
    var getParamsForFilterOrRing = function () {
        var location = window.location.host + window.location.pathname;
        var resource_type = BabblingBrook.Library.extractResource(location);
        var params = {};
        if (resource_type === 'stream') {
            params.name = BabblingBrook.Library.extractName(location);
            params.username = BabblingBrook.Library.extractUsername(location);
            params.domain = BabblingBrook.Library.extractDomain(location);
            params.version = BabblingBrook.Library.extractVersion(location);
        } else {
            var stream = BabblingBrook.Library.pickRandomProperty(BabblingBrook.Client.User.StreamSubscriptions);
            params.name = stream.name;
            params.username = stream.username;
            params.domain = stream.domain;
            params.version = stream.version;
        }
        return params;
    };

    /**
     * redirects the user to the suggested stream.
     *
     * @param {object} suggestion The suggestion that is being redirected to. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onViewStreamClicked = function (suggestion) {
        var stream_url = BabblingBrook.Library.makeStreamUrl(suggestion);
        BabblingBrook.Client.Core.Ajaxurl.redirect(stream_url);
        return false;  // prevent the message from changing when the page reloads.
    };

    /**
     * Subscribe the suggested stream.
     *
     * @param {object} suggestion The suggestion that has been accepted. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onSubscribeStreamClicked = function (suggestion) {
        BabblingBrook.Client.Component.Messages.turnOnBorderLoading();
        BabblingBrook.Client.Core.StreamSubscriptions.subscribeStream(
            {
                name : suggestion.name,
                domain : suggestion.domain,
                username : suggestion.username,
                version : BabblingBrook.Library.makeVersionObject(suggestion.version)
            },
            /**
            * Callback for the attempt to subscribe the stream.
            */
            function (data) {
                BabblingBrook.Client.Component.Messages.turnOffBorderLoading();
            }
        );
    };

    /**
     * Subscribe the suggested stream filter.
     *
     * @param {object} suggestion The suggestion that has been accepted. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onSubscribeFilterSuggestionClicked = function (suggestion, stream) {
        BabblingBrook.Client.Component.Messages.turnOnBorderLoading();
        stream = BabblingBrook.Library.convertResourceObjectVersion(stream);
        var subscription_id = BabblingBrook.Client.Core.StreamSubscriptions.getStreamSubscriptionIDFromStream(stream);
        BabblingBrook.Client.Core.StreamSubscriptions.subscribeStreamFilter(
            subscription_id,
            stream,
            suggestion,
            /**
            * Callback for the attempt to subscribe the moderation ring to the stream.
            */
            function () {
                BabblingBrook.Client.Component.Messages.turnOffBorderLoading();
            }
        );
    };

    /**
     * Subscribe the suggested stream filter.
     *
     * @param {object} suggestion The suggestion that has been declined. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onSubscribeModerationRingClicked = function (suggestion, stream) {
        BabblingBrook.Client.Component.Messages.turnOnBorderLoading();
        stream = BabblingBrook.Library.convertResourceObjectVersion(stream);
        var subscription_id = BabblingBrook.Client.Core.StreamSubscriptions.getStreamSubscriptionIDFromStream(stream);
        BabblingBrook.Client.Core.StreamSubscriptions.subscribeStreamModerationRing(
            subscription_id,
            stream,
            suggestion,
            /**
            * Callback for the attempt to subscribe the moderation ring to the stream.
            */
            function () {
                BabblingBrook.Client.Component.Messages.turnOffBorderLoading();
            }
        );
    };

    /**
     * Redirects the page to the suggested ring profile wihtout changing the suggestion message.
     *
     * @param {string} url The url of the user to redirect to.
     *
     * @returns {void}
     */
    var onVisitModerationClicked = function (url) {
        BabblingBrook.Client.Core.Ajaxurl.redirect(url);
        return false; // prevent the message from changing.
    };

    /**
     * Redirects the page to the suggested stream filter meta post wihtout changing the suggestion message.
     *
     * @param {string} url The url of the user to redirect to.
     *
     * @returns {void}
     */
    var onVisitStreamFilterClicked = function (url) {
        BabblingBrook.Client.Core.Ajaxurl.redirect(url);
        return false; // prevent the message from changing.
    };

    /**
     * Link to the suggested user
     *
     * @param {object} suggestion The suggestion that has been declined. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onVisitUserClicked = function (suggestion) {
        var url = 'http://' + suggestion.domain + '/' + suggestion.username + '/profile';
        BabblingBrook.Client.Core.Ajaxurl.redirect(url);
        return false; // prevent the message from changing.
    };

    /**
     * Callback for if a domus domain reports back an error when declining a suggestion.
     *
     * Not important enough to bother the user with.
     *
     * @returns {void}
     */
    var onSuggestionDeclinedError = function (error_data, suggestion) {
        console.log(error_data);
        console.log(suggestion);
        BabblingBrook.Client.Component.Messages.turnOffBorderLoading();
        throw 'An error occured whilst declining a suggestion.';
    };

    /**
     * Callback for after a domus domain reports back that it has declined a suggestion.
     *
     * @returns {void}
     */
    var onSuggestionDeclined = function () {
        BabblingBrook.Client.Component.Messages.turnOffBorderLoading();
    };

    /**
     * Decline a suggestion so that it is not shown again.
     *
     * @param {object} suggestion The suggestion that has been declined. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onDeclinedStreamClicked = function (suggestion) {
        BabblingBrook.Client.Component.Messages.turnOnBorderLoading();
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                type : 'stream_suggestion',
                stream : {
                    domain : suggestion.domain,
                    username : suggestion.username,
                    name : suggestion.name,
                    version : BabblingBrook.Library.makeVersionObject(suggestion.version)
                }
            },
            'DeclineSuggestion',
            onSuggestionDeclined,
            onSuggestionDeclinedError.bind(null, suggestion)
        );
    };

    /**
     * Decline a suggestion so that it is not shown again.
     *
     * @param {object} suggestion The suggestion that has been declined. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onDeclinedFilterSuggestionClicked = function (suggestion, params) {
        BabblingBrook.Client.Component.Messages.turnOnBorderLoading();
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                type : 'stream_filter_suggestion',
                rhythm : {
                    domain : suggestion.domain,
                    username : suggestion.username,
                    name : suggestion.name,
                    version : suggestion.version
                }
            },
            'DeclineSuggestion',
            onSuggestionDeclined,
            onSuggestionDeclinedError.bind(null, suggestion)
        );
    };

    /**
     * Decline a suggestion so that it is not shown again.
     *
     * @param {object} suggestion The suggestion that has been declined. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onDeclinedModerationRingClicked = function (suggestion, params) {
        BabblingBrook.Client.Component.Messages.turnOnBorderLoading();
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                type : 'stream_ring_suggestion',
                user : {
                    domain : suggestion.domain,
                    username : suggestion.username
                }
            },
            'DeclineSuggestion',
            onSuggestionDeclined,
            onSuggestionDeclinedError.bind(null, suggestion)
        );
    };

    /**
     * Decline a suggestion so that it is not shown again.
     *
     * @param {object} suggestion The suggestion that has been declined. See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onDeclinedUserClicked = function (suggestion) {
        BabblingBrook.Client.Component.Messages.turnOnBorderLoading();
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                type : 'user_suggestion',
                user : {
                    domain : suggestion.domain,
                    username : suggestion.username
                }
            },
            'DeclineSuggestion',
            onSuggestionDeclined,
            onSuggestionDeclinedError.bind(null, suggestion)
        );
    };

    /**
     * Show a message informing the user that there are no more suggestions at present.
     *
     * Called when a user clicks on the 'Another' button.
     *
     * @returns {void}
     */
    var showNoMoreSuggestionsMessage = function () {
        var jq_message = jQuery('#no_more_suggestions_message_template>div').clone();
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'suggestion',
            message : jq_message.html(),
            buttons : [{
                name : 'OK',
                callback : function () {}
            }]
        });
    };

    /**
     * Create a message to display a stream suggestion to the user.
     *
     * @param {array} suggestions The array of suggestions.
     * @param {string} suggestions[].domain The domain of the stream to suggest.
     * @param {string} suggestions[].username The username of the stream to suggest.
     * @param {string} suggestions[].name The name of the stream to suggest.
     * @param {string} suggestions[].version The version of the stream to suggest.
     * @param {number} index The index of the suggestions array to display.
     *
     * @returns {void}
     */
    var generateStreamMessage = function (suggestion, duplicate) {
        if (typeof duplicate !== 'boolean') {
            duplicate = false;
        }

        var jq_message = jQuery('#suggestion_stream_message_template>div').clone();
        var url = BabblingBrook.Library.makeStreamUrl(suggestion, 'posts');
        jQuery('a.suggestion-message', jq_message)
            .attr('href', 'http://' + url)
            .attr('title', url)
            .text(suggestion.name);

        var buttons = [{
            name : 'View Stream',
            callback : onViewStreamClicked.bind(null, suggestion)
        },
        {
            name : 'Subscribe',
            callback : onSubscribeStreamClicked.bind(null, suggestion)
        },
        {
            name : 'No Thanks',
            callback : onDeclinedStreamClicked.bind(null, suggestion)
        }
        ];
        if (duplicate === false) {
            buttons.push(
                {
                    name : 'Show Another',
                    callback : generateStreamMessage.bind(null, suggestion, true)
                }
            );
        }
        buttons.push({
            name : 'Not Now',
            callback : function () {}
        });

        BabblingBrook.Client.Component.Messages.addMessage(
            {
                type : 'suggestion',
                message : jq_message.html(),
                buttons : buttons
            },
            duplicate
        );
    };

    /**
     * The meta post id for the suggested rhythm has been fetched. Now display the sugggestion message.
     *
     * @param {object} suggestion See generateFilterMessage for definition.
     * @param {object} stream See generateFilterMessage for definition.
     * @param {object} meta_data The meta_post_id data that was requested.
     * @param {number} meta_data.meta_post_id The meta_post_id that was requested.
     *
     * @returns {void}
     */
    var onMetaPostIdForRhythmFetched = function (suggestion, stream, meta_data, duplicate) {
        if (typeof duplicate !== 'boolean') {
            duplicate = false;
        }

        var jq_message = jQuery('#suggestion_filter_message_template>div').clone();
        var stream_url = BabblingBrook.Library.makeStreamUrl(stream, 'posts');
        jQuery('a.stream-name', jq_message)
            .attr('href', 'http://' + stream_url)
            .attr('title', stream_url)
            .text(stream.name);
        var url = suggestion.domain + '/post/' + suggestion.domain + '/' + meta_data.meta_post_id;
        jQuery('a.suggestion-message', jq_message)
            .attr('href', 'http://' + url)
            .attr('title', url)
            .text(suggestion.name);

        var buttons = [{
            name : 'Visit',
            callback : onVisitStreamFilterClicked.bind(null, url)
        },
        {
            name : 'Subscribe',
            callback : onSubscribeFilterSuggestionClicked.bind(null, suggestion, stream)
        },
        {
            name : 'No Thanks',
            callback : onDeclinedFilterSuggestionClicked.bind(null, suggestion, stream)
        }];
        if (duplicate === false) {
            buttons.push({
                name : 'Show Another',
                callback : onMetaPostIdForRhythmFetched.bind(null, suggestion, stream, meta_data, true)
            });
        }
        buttons.push({
            name : 'Not Now',
            callback : function () {}
        });

        BabblingBrook.Client.Component.Messages.addMessage(
            {
                type : 'suggestion',
                message : jq_message.html(),
                buttons : buttons
            },
            duplicate
        );
    };

    /**
     * An error occured fetching the rhythm data for a suggestion.
     *
     * Auto decline the suggestion to prevent it from causing future errors.
     *
     * @param {array} suggestion See generateStreamMessage for definition.
     *
     * @returns {void}
     */
    var onMetaPostIdForRhythmFetchedError = function (suggestion) {
        onDeclinedStreamClicked(suggestion);
    }

    /**
     * Create a message to display a stream filter suggestion to the user.
     *
     * @param {string} suggestion.domain The domain of the stream to suggest.
     * @param {string} suggestion.username The username of the stream to suggest.
     * @param {string} suggestion.name The name of the stream to suggest.
     * @param {string} suggestion.version The version of the stream to suggest.
     * @param {number} index The index of the suggestions array to display.
     * @param {object} params The paramaters that were sent to the suggestion rhythm.
     *
     * @returns {void}
     */
    var generateFilterMessage = function (suggestion, params) {
        var rhythm_data_url = BabblingBrook.Library.makeRhythmUrl(suggestion, 'getmetapostid');
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                url : rhythm_data_url,
                data : {},
                https : false
            },
            'InfoRequest',
            onMetaPostIdForRhythmFetched.bind(null, suggestion, params),
            onMetaPostIdForRhythmFetchedError.bind(null, suggestion)
        );

    }

    /**
     * Create a message to display a stream moderation ring to the user.
     *
     * @returns {void}
     */
    var generateModerationRingMessage = function (suggestion, params, duplicate) {
        if (typeof duplicate !== 'boolean') {
            duplicate = false;
        }

        var jq_message = jQuery('#suggestion_moderation_ring_message_template>div').clone();
        var stream_url = BabblingBrook.Library.makeStreamUrl(params, 'posts');
        jQuery('a.stream-name', jq_message)
            .attr('href', 'http://' + stream_url)
            .attr('title', stream_url)
            .text(params.name);
        var url = suggestion.domain + '/' + suggestion.username;
        jQuery('a.suggestion-message', jq_message)
            .attr('href', 'http://' + url)
            .attr('title', url)
            .text(url);

        var buttons = [{
            name : 'Visit',
            callback : onVisitModerationClicked.bind(null, url)
        },
        {
            name : 'Subscribe',
            callback : onSubscribeModerationRingClicked.bind(null, suggestion, params)
        },
        {
            name : 'No Thanks',
            callback : onDeclinedModerationRingClicked.bind(null, suggestion, params)
        }];
        if (duplicate === false) {
            buttons.push({
                name : 'Show Another',
                callback : generateModerationRingMessage.bind(null, suggestion, params, true)
            });
        }
        buttons.push({
            name : 'Not Now',
            callback : function () {}
        });
        BabblingBrook.Client.Component.Messages.addMessage(
            {
                type : 'suggestion',
                message : jq_message.html(),
                buttons : buttons
            },
            duplicate
        );
    };

    /**
     * Create a message to display a stream moderation ring to the user.
     *
     * @param suggestion
     * @param {boolean} [duplicate=false] Should this be added as a duplicate message.
     *      (Pushes the message to the back of the queue.)
     *
     * @returns {void}
     */
    var generateUserMessage = function (suggestion, duplicate) {
        if (typeof duplicate !== 'boolean') {
            duplicate = false;
        }

        var jq_message = jQuery('#suggestion_user_message_template>div').clone();
        var url = suggestion.domain + '/' + suggestion.username;
        jQuery('a.suggestion-message', jq_message)
            .attr('href', 'http://' + url)
            .attr('title', url)
            .text(suggestion.username);

        var buttons = [{
            name : 'Visit',
            callback : onVisitUserClicked.bind(null, suggestion)
        },
        {
            name : 'No Thanks',
            callback : onDeclinedUserClicked.bind(null, suggestion)
        }];
        if (duplicate === false) {
            buttons.push(
                {
                    name : 'Show Another',
                    callback : generateUserMessage.bind(null, suggestion, true)
                }
            );
        }
        buttons.push({
            name : 'Not Now',
            callback : function () {}
        });

        BabblingBrook.Client.Component.Messages.addMessage(
            {
                type : 'suggestion',
                message : jq_message.html(),
                buttons : buttons
            },
            duplicate
        );
    }

    /**
     * Callback for when the requested suggestions have been generated.
     *
     * @param {string} suggestion_type The type of suggestion that has been fetched.
     * @param {object} params The stream name that was sent to the suggestion rhythm.
     * @param {object} suggestions The returned suggestions.
     *
     * @returns {void}
     */
    var onSuggestionsFetched = function (suggestion_type, stream, suggestions) {
        for(var i=0; i<suggestions.length; i++) {
            switch(suggestion_type) {
                case 'stream_suggestion':
                    generateStreamMessage(suggestions[i]);
                    break;

                case 'stream_filter_suggestion':
                    generateFilterMessage(suggestions[i], stream);
                    break;

                case 'stream_ring_suggestion':
                    generateModerationRingMessage(suggestions[i], stream);
                    break;

                case 'user_suggestion':
                    generateUserMessage(suggestions[i], stream);
                    break;
            }
        }
        time_last_ran =  Math.round(new Date().getTime() / 1000);
        main();
    }

    /**
     * Fetch a suggestion from the next suggestion rhythm.
     *
     * @returns {boolean} true if it is time to run onSuggestionReadyToRun.
     */
    var onSuggestionReadyToRun = function () {
        var suggestion_type = suggestion_type_order[current_suggestion_type_id];
        current_suggestion_type_id++;
        if (current_suggestion_type_id + 1 > suggestion_type_order.length) {
            current_suggestion_type_id = 0;
        }

        var params = {};
        var stream;
        if (suggestion_type === 'stream_filter_suggestion' || suggestion_type === 'stream_ring_suggestion') {
            stream = getParamsForFilterOrRing();
        }
        BabblingBrook.Client.Core.Suggestion.fetch(
            suggestion_type,
            onSuggestionsFetched.bind(null, suggestion_type, stream),
            params
        );
    };

    /**
     * Fetch a suggestion for the next suggestion rhythm.
     *
     * @returns {boolean} true if it is time to run onSuggestionReadyToRun.
     */
    var onSuggestionWaitError = function () {
        throw 'SuggestionMessage is never ready to run.';
    };

    /**
     * This method runs after each message suggestion routine runs to set it up to run again.
     *
     * @returns {void}
     */
    var main = function () {
        BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
            BabblingBrook.Library.wait(
                onSuggestionWaitCondition,
                onSuggestionReadyToRun,
                onSuggestionWaitError,
                { 'error' : 'SuggestionMessage timeout'},
                1000,
                (BabblingBrook.Client.User.Config.suggestion_message_rate * 1000) + 2000
            );
        });
    };

    return {

        construct : function () {
            main();
        }

    };
}());

