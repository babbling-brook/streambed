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
 * @fileOverview Handles interactions with the users StreamSubscriptions
 * @author Sky Wickenden
 */

/**
 * @namespace Handles interactions with the users StreamSubscriptions
 * @package JS_Client
 */
BabblingBrook.Client.Core.StreamSubscriptions = (function () {
    'use strict';

    /**
     * Callback for receiving stream subscriptions from the sientia domain.
     *
     * @param {function} [onFetched] A callback to call when stream subscriptions have been fetched.
     * @param {object} response_data The data passed back from the scientia domain.
     *
     * @return {undefined}
     */
    var onFetchStreamSubscriptionsSuccess = function (onFetched, response_data) {
        if (response_data.success === true) {
            BabblingBrook.Client.User.StreamSubscriptions = response_data.subscriptions;
            BabblingBrook.Client.Core.Loaded.setStreamSubscriptionsLoaded();
            if (typeof onFetched === 'function') {
                onFetched();
            }
        } else {
            onFetchStreamSubscriptionsError(response_data.error);
        }
    };

    /**
     * Report an error to the user if there is an error when fetching their stream subscriptions.
     */
    var onFetchStreamSubscriptionsError = function (error) {
        if (typeof error === 'object') {
            error = JSON.stringify(error);
        }
        var message = jQuery('#Core_StreamSubscriptions_fetch_error_template').text();
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : message,
            error_details : error
        });
        console.error(error);
    };

    /**
     * Show an error message when a custom error handler has not been passed in.
     */
    var onFetchError = function (onError, type, stream, rhythm, ring) {
        if (typeof onError === 'function') {
            onError(type);
            return;
        }
        var message;
        switch (type) {
            case 'subscribe_stream':
                message = jQuery('#Core_StreamSubscriptions_stream_error_template').text();
                message += ' (' + stream.name + ') ';
                break;
            case 'subscribe_rhythm':
                message = jQuery('#Core_StreamSubscriptions_rhythm_error_template').text();
                message += ' (' + rhythm.name + ') ';
                break;
            case 'subscribe_ring':
                message = jQuery('#Core_StreamSubscriptions_ring_error_template').text();
                message += ' (' + ring.username + ') ';
                break;
            case 'unsubscribe_stream':
                message = jQuery('#Core_StreamSubscriptions_unsubscribe_stream_error_template').text();
                message += ' (' + stream.name + ') ';
                break;
            case 'unsubscribe_rhythm':
                message = jQuery('#Core_StreamSubscriptions_unsubscribe_rhythm_error_template').text();
                message += ' (' + ring.username + ') ';
                break;
            case 'change_stream_version':
                message = jQuery('#Core_StreamSubscriptions_change_stream_version_error_template').text();
                message += ' (' + stream.username + ') ';
                break;
            case 'change_filter_version':
                message = jQuery('#Core_StreamSubscriptions_change_filter_version_error_template').text();
                message += ' (' + rhythm.username + ') ';
                break;
        }

        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : message
        });

    };

    /**
     * Success callback for when a stream has been subscribed.
     *
     * @param {object} stream A standard stream name object for the stream that has been subscribed.
     * @param {function} [onSubscribed] A callback to call when the stream has been subscribed.
     * @param {function} onError A callback to call when the scientia domain passes an error back.
     * @param {object} response_data The data returned from the scientia domain.
     * @param {array} response_data.filters An array of default filter subscriptions.
     * @param {array} response_data.moderation_rings An array of default moderation rings.
     *
     * @return void
     */
    var onSubscribeStreamSuccess = function (stream, onSubscribed, onError, response_data) {
        if (response_data.success === false) {
            onFetchError(onError, 'subscribe_stream', stream);
            return;
        }
        var subscription = response_data.subscription;
        BabblingBrook.Client.User.StreamSubscriptions[subscription.display_order] = subscription;
        BabblingBrook.Client.Component.StreamNav.reshow();
        if (typeof onSubscribed === 'function') {
            onSubscribed(response_data);
        }
    };

    /**
     * Success callback for when a stream has been unsubscribed.
     *
     * @param {object} stream A standard stream name object for the stream that has been unsubscribed.
     * @param {function} [onUnsubscribed] A callback to call when the stream has been unsubscribed.
     * @param {function} onError A callback to call when the scientia domain passes an error back.
     * @param {object} response_data The data returned from the scientia domain.
     *
     * @return void
     */
    var onUnsubscribeStreamSuccess = function (stream_subscription_id, stream, onUnsubscribed, onError, response_data) {
        if (response_data.success === false) {
            onFetchError(onError, 'subscribe_stream', stream);
            return;
        }

        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, subscription) {
            if (subscription.stream_subscription_id === stream_subscription_id) {
                delete BabblingBrook.Client.User.StreamSubscriptions[i];
            }
        });
        BabblingBrook.Client.Component.StreamNav.reshow();
        if (typeof onUnsubscribed === 'function') {
            onUnsubscribed(response_data);
        }
    };

    /**
     * Success callback for when a stream filter has been subscribed.
     *
     * @param {object} stream A standard stream name object for the stream to subscribe a filter to.
     * @param {object} rhythm A standard rhythm name object for the filter to subscribe.
     * @param {function} [onSubscribed] A callback to call when the filter has been subscribed.
     * @param {function} onError A callback to call when the scientia domain passes an error back.
     * @param {object} response_data The data returned from the scientia domain.
     *
     * @return void
     */
    var onSubscribeFilterSuccess = function (stream_subscription_id, stream, rhythm,
        onSubscribed, onError, response_data
    ) {
        if (response_data.success === false) {
            onFetchError(onError, 'subscribe_stream', stream, rhythm);
            return;
        }

        var stream_subscription = BabblingBrook.Client.Core.StreamSubscriptions.getStreamSubscriptionFromId(
            stream_subscription_id
        );

        stream_subscription.filters[response_data.subscription.filter_subscription_id] = response_data.subscription;

        BabblingBrook.Client.Component.StreamNav.reshow();
        if (typeof onSubscribed === 'function') {
            onSubscribed(response_data);
        }
    };


    /**
     * Success callback for when a stream filter has been unsubscribed.
     *
     * @param {number} stream_subscription_id The id of the stream subscription that a filter is being removed from.
     * @param {number} filter_subscription_id The id of the filter that is being removed from a stream subscription.
     * @param {object} stream A standard stream name object for the stream to unsubscribe a filter from.
     * @param {object} rhythm A standard rhythm name object for the filter to unsubscribe.
     * @param {function} [onUnsubscribed] A callback to call when the filter has been unsubscribed.
     * @param {function} onError A callback to call when the scientia domain passes an error back.
     * @param {object} response_data The data returned from the scientia domain.
     *
     * @return void
     */
    var onUnsubscribeFilterSuccess = function (stream_subscription_id, filter_subscription_id, stream, rhythm,
        onUnsubscribed, onError, response_data
    ) {
        if (response_data.success === false) {
            onFetchError(onError, 'subscribe_stream', stream, rhythm);
            return;
        }

        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, sub) {
            if (sub.stream_subscription_id === stream_subscription_id) {
                delete BabblingBrook.Client.User.StreamSubscriptions[i].filters[filter_subscription_id];
                return false; // Exit the .each
            }
        });

        BabblingBrook.Client.Component.StreamNav.reshow();
        if (typeof onUnsubscribed === 'function') {
            onUnsubscribed(response_data);
        }
    };

    /**
     * Success callback for when a stream ring has been subscribed.
     *
     * @param {number} stream_subscription_id The id of the stream subscription that a ring is added to.
     * @param {object} stream A standard stream name object for the stream to subscribe a ring to.
     * @param {object} ring A standard ring name object for the ring to subscribe.
     * @param {function} [onSubscribed] A callback to call when the ring has been subscribed.
     * @param {function} onError A callback to call when the scientia domain passes an error back.
     * @param {object} response_data The data returned from the scientia domain.
     *
     * @return void
     */
    var onSubscribeRingSuccess = function (stream_subscription_id, stream, ring, onSubscribed, onError, response_data) {
        if (response_data.success === false) {
            onFetchError(onError, 'subscribe_stream', stream, undefined, ring);
            return;
        }
        var stream_subscription = BabblingBrook.Client.Core.StreamSubscriptions.getStreamSubscriptionFromId(
            stream_subscription_id
        );
        stream_subscription.rings[response_data.subscription.ring_subscription_id] = response_data.subscription;
        BabblingBrook.Client.Component.StreamNav.reshow();
        if (typeof onSubscribed === 'function') {
            onSubscribed(response_data);
        }
    };

    /**
     * Success callback for when a stream ring has been unsubscribed.
     *
     * @param {number} stream_subscription_id The id of the stream subscription that a ring is being removed from.
     * @param {number} ring_subscription_id The id of the ring that is being removed from a stream subscription.
     * @param {object} stream A standard stream name object for the stream to unsubscribe a ring from.
     * @param {object} ring A standard ring name object for the ring to unsubscribe.
     * @param {function} [onUnsubscribed] A callback to call when the ring has been unsubscribed.
     * @param {function} onError A callback to call when the scientia domain passes an error back.
     * @param {object} response_data The data returned from the scientia domain.
     *
     * @return void
     */
    var onUnsubscribeRingSuccess = function (stream_subscription_id, ring_subscription_id, stream,
        ring, onUnsubscribed, onError, response_data
    ) {
        if (response_data.success === false) {
            onFetchError(onError, 'subscribe_stream', stream, undefined, ring);
            return;
        }
        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, sub) {
            if (sub.stream_subscription_id === stream_subscription_id) {
                delete BabblingBrook.Client.User.StreamSubscriptions[i].rings[ring_subscription_id];
                return false; // Exit the .each
            }
        });
        BabblingBrook.Client.Component.StreamNav.reshow();
        if (typeof onUnsubscribed === 'function') {
            onUnsubscribed(response_data);
        }
    };


    /**
     * Success callback for when a stream subscription has had its version changed.
     *
     * @param {object} stream A standard stream name object for the stream that has had its filter version changed.
     * @param {object} new_version A standard version object representing the new version.
     * @param {function} [onChanged] A callback to call when the filter version has been changed.
     * @param {function} onError A callback to call when the scientia domain passes an error back.
     * @param {object} response_data The data returned from the scientia domain.
     *
     * @return void
     */
    var onStreamVersionChangedSuccess = function (stream, new_version, onChanged, onError, response_data) {
        if (response_data.success === false) {
            onFetchError(onError, 'change_stream_version', stream);
            return;
        }
        jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, subscription) {
            if (BabblingBrook.Library.doStreamsMatch(stream, subscription) === true) {
                BabblingBrook.Client.User.StreamSubscriptions[i].version = new_version;
            }
        });
        BabblingBrook.Client.Component.StreamNav.reshow();
        if (typeof onChanged === 'function') {
            onChanged(response_data);
        }
    };

    /**
     * Success callback for when a stream filter rhythm has had its version changed.
     *
     * @param {number} stream_subscription_id The id of the stream subscription that has a filter whose
     *      version is being changed.
     * @param {number} filter_subscription_id The id of the filter whose version is being changed.
     * @param {object} stream A standard stream name object for the stream that owns the filter
     *      that has had its filter version changed.
     * @param {object} rhythm A standard rhythm name object for the rhythm that has had its filter version changed.
     * @param {object} new_version A standard version object representing the new version.
     * @param {function} [onChanged] A callback to call when the filter version has been changed.
     * @param {function} onError A callback to call when the scientia domain passes an error back.
     * @param {object} response_data The data returned from the scientia domain.
     *
     * @return void
     */
    var onFilterVersionChangedSuccess = function (stream_subscription_id, filter_subscription_id, stream, rhythm,
        new_version, onChanged, onError, response_data
    ) {
        if (response_data.success === false) {
            onFetchError(onError, 'change_filter_version', stream, rhythm);
            return;
        }

        var stream_subscription = BabblingBrook.Client.Core.StreamSubscriptions.getStreamSubscriptionFromId(
            stream_subscription_id
        );
        stream_subscription.filters[filter_subscription_id].version = new_version;

        BabblingBrook.Client.Component.StreamNav.reshow();
        if (typeof onChanged === 'function') {
            onChanged(response_data);
        }
    };

    return {

        /**
         * Subscribe a new stream to this users account.
         *
         * @param {object} stream A standard stream name object.
         * @param {function} [onSubscribed] A callback to call when the stream has been subscribed.
         * @param {function} [onError] A callback to call when there is an error.
         *
         * @return {undefined}
         */
        subscribeStream : function (stream, onSubscribed, onError) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    stream : stream
                },
                'SubscribeStream',
                onSubscribeStreamSuccess.bind(null, stream, onSubscribed, onError),
                onFetchError.bind(null, onError, 'subscribe_stream', stream)
            );
        },

        /**
         * Unsubscribe a new stream from this users account.
         *
         * @param {object} stream A standard stream name object.
         * @param {string} subscription_id The id associated with this subscription.
         * @param {function} [onUnSubscribed] A callback to call when the stream has been unsubscribed.
         * @param {function} [onError] A callback to call when there is an error.
         *
         * @return {undefined}
         */
        unsubscribeStream : function (stream, subscription_id, onUnsubscribed, onError) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    subscription_id : subscription_id
                },
                'UnsubscribeStream',
                onUnsubscribeStreamSuccess.bind(null, subscription_id, stream, onUnsubscribed, onError),
                onFetchError.bind(null, onError, 'unsubscribe_stream', stream)
            );
        },

        /**
         * Subscribe a new filter to a stream in this users account.
         *
         * @param {object} stream A standard stream name object for the stream to subscribe a filter to.
         * @param {object} rhythm A standard rhythm name object for the filter to subscribe.
         * @param {function} [onUnSubscribed] A callback to call when the stream has been unsubscribed.
         * @param {function} [onError] A callback to call when there is an error.
         *
         * @return {undefined}
         */
        subscribeStreamFilter : function (stream_subscription_id, stream, rhythm, onSubscribed, onError) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    stream_subscription_id : stream_subscription_id,
                    rhythm : rhythm
                },
                'SubscribeStreamFilter',
                onSubscribeFilterSuccess.bind(null, stream_subscription_id, stream, rhythm, onSubscribed, onError),
                onFetchError.bind(null, onError, 'subscribe_rhythm', stream, rhythm)
            );
        },

        /**
         * Unsubscribe a filter rhythm from a stream subscription from this users account.
         *
         * @param {string} stream_subscription_id The id that identifies the stream subscription.
         * @param {string} filter_subscription_id The id that identifies the filter subscription.
         * @param {object} stream A standard stream name object for the subscribed stream.
         * @param {object} rhythm A standard rhythm name object for the filter that is being unsubscribed.
         * @param {function} [onUnSubscribed] A callback to call when the stream has been unsubscribed.
         * @param {function} [onError] A callback to call when there is an error.
         *
         * @return {undefined}
         */
        unSubscribeStreamFilter : function (stream_subscription_id, filter_subscription_id ,stream,
            rhythm, onUnsubscribed, onError
        ) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    stream_subscription_id : stream_subscription_id,
                    filter_subscription_id : filter_subscription_id
                },
                'UnsubscribeStreamFilter',
                onUnsubscribeFilterSuccess.bind(
                    null,
                    stream_subscription_id,
                    filter_subscription_id,
                    stream,
                    rhythm,
                    onUnsubscribed,
                    onError
                ),
                onFetchError.bind(null, onError, 'unsubscribe_rhythm', stream, rhythm)
            );
        },

        /**
         * Subscribe a moderation ring for a stream subscription for the current users account.
         *
         * @param {string} stream_subscription_id The id that identifies the stream subscription.
         * @param {object} stream A standard stream name object for the subscribed stream.
         * @param {object} ring A standard ring name object for the ring that is being subscribed.
         * @param {function} [onSubscribed] A callback to call when the ring has been subscribed.
         * @param {function} [onError] A callback to call when there is an error.
         *
         * @return {undefined}
         */
        subscribeStreamModerationRing : function (stream_subscription_id, stream, ring, onSubscribed, onError) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    stream_subscription_id : stream_subscription_id,
                    ring : ring
                },
                'SubscribeStreamRing',
                onSubscribeRingSuccess.bind(null, stream_subscription_id, stream, ring, onSubscribed, onError),
                onFetchError.bind(null, onError, 'subscribe_ring', stream, undefined, ring)
            );
        },

        /**
         * Unsubscribe a moderation ring for a stream subscription for the current users account.
         *
         * @param {string} stream_subscription_id The id that identifies the stream subscription.
         * @param {string} ring_subscription_id The id that identifies the ring subscription.
         * @param {object} stream A standard stream name object for the subscribed stream.
         * @param {object} ring A standard ring name object for the ring that is being subscribed.
         * @param {function} [onSubscribed] A callback to call when the ring has been subscribed.
         * @param {function} [onError] A callback to call when there is an error.
         *
         * @return {undefined}
         */
        unsubscribeStreamModerationRing : function (stream_subscription_id, ring_subscription_id,
            stream, ring, onUnsubscribed, onError
        ) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    stream_subscription_id : stream_subscription_id,
                    ring_subscription_id : ring_subscription_id
                },
                'UnsubscribeStreamRing',
                onUnsubscribeRingSuccess.bind(
                    null,
                    stream_subscription_id,
                    ring_subscription_id,
                    stream,
                    ring,
                    onUnsubscribed,
                    onError
                ),
                onFetchError.bind(null, onError, 'unsubscribe_ring', stream, undefined, ring)
            );
        },

        /**
         * Unsubscribe a moderation ring for a stream subscription for the current users account.
         *
         * @param {string} stream_subscription_id The id that identifies the stream subscription.
         * @param {object} new_version A standard version object representing the new version.
         * @param {object} stream A standard stream name object for the subscribed stream.
         * @param {function} [onSubscribed] A callback to call when the ring has been subscribed.
         * @param {function} [onError] A callback to call when there is an error.
         *
         * @return {undefined}
         */
        changeStreamVersion : function (stream_subscription_id, new_version, stream, onChanged, onError) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    stream_subscription_id : stream_subscription_id,
                    new_version : new_version
                },
                'ChangeStreamSubscriptionVersion',
                onStreamVersionChangedSuccess.bind(null, stream, new_version, onChanged, onError),
                onFetchError.bind(null, onError, 'change_filter_version', stream, undefined)
            );
        },

        /**
         * Unsubscribe a moderation ring for a stream subscription for the current users account.
         *
         * @param {string} stream_subscription_id The id that identifies the stream subscription.
         * @param {string} filter_subscription_id The id that identifies the filter subscription.
         * @param {object} new_version A standard version object representing the new version.
         * @param {object} stream A standard stream name object for the subscribed stream.
         * @param {object} rhythm A standard rhythm name object for the filter that is being unsubscribed.
         * @param {function} [onSubscribed] A callback to call when the ring has been subscribed.
         * @param {function} [onError] A callback to call when there is an error.
         *
         * @return {undefined}
         */
        changeFilterVersion : function (stream_subscription_id, filter_subscription_id, new_version,
            stream, rhythm, onChanged, onError
        ) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    stream_subscription_id : stream_subscription_id,
                    filter_subscription_id : filter_subscription_id,
                    new_version : new_version
                },
                'ChangeStreamSubscriptionFilterVersion',
                onFilterVersionChangedSuccess.bind(
                    null,
                    stream_subscription_id,
                    filter_subscription_id,
                    stream, rhythm,
                    new_version,
                    onChanged,
                    onError
                ),
                onFetchError.bind(null, onError, 'change_filter_version', stream, undefined, rhythm)
            );
        },

        /**
         * Fetch the users stream subscriptions
         *
         * @param {function} onFetched A callback to run after the subscriptions have been fetched.
         *
         * @return {undefined}
         */
        fetchStreamSubscriptions : function (onFetched) {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {},
                'FetchStreamSubscriptions',
                onFetchStreamSubscriptionsSuccess.bind(null, onFetched),
                onFetchStreamSubscriptionsError
            );
        },

        /**
         * Retrun a stream subscription from its id.
         *
         * @param {string} subscription_id The stream subscription id.
         *
         * @returns {object|undefinend} A stream subscription or undefined.
         */
        getStreamSubscriptionFromId : function (subscription_id){
            var subscription;
            jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, sub) {
                if (sub.stream_subscription_id === subscription_id) {
                    subscription = sub;
                    return false; // Exit the .each
                }
            });
            return subscription;
        },

        /**
         * Retrun a stream subscription from its id.
         *
         * @param {string} subscription_id The stream subscription id.
         *
         * @returns {object|undefinend} A stream subscription or undefined.
         */
        getStreamSubscriptionIDFromStream : function (stream){
            var subscription_id;
            jQuery.each(BabblingBrook.Client.User.StreamSubscriptions, function (i, sub) {
                if (BabblingBrook.Library.doStreamsMatch(sub, stream) === true) {
                    subscription_id = sub.stream_subscription_id;
                    return false; // Exit the .each
                }
            });
            return subscription_id;
        }
    };
}());