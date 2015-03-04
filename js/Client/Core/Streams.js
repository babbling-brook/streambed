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
 * @fileOverview Stores and provides accesss to stream data.
 * @author Sky Wickenden
 * @refactor This is not used in some places, post, profile. Probably more.
 */

/**
 * @namespace Stores and provides accesss to stream data.
 * @package JS_Shared
 */
BabblingBrook.Client.Core.Streams = (function () {
    'use strict';

    /**
     * Store of stream data as defined by BabblingBrook.Models.stream.
     * Indexed by :
     *      {string} domain
     *      {string} username
     *      {string} name
     *      {string} version
     *
     * If the stream is in the process of being fetched it will have a value of 'loading'
     *
     * @type {object}
     */
    var streams = {};

    /**
     * Stores callbacks to run once a stream has loaded. Uses the same indexing structure as the streams object.
     *
     * @type {object}
     */
    var deferred_streams = {};

    /**
     * Called when a stream has been fetched.
     *
     * @param {string} domain The streams domain.
     * @param {string} username The name of the user who owns the stream.
     * @param {string} name The name of the stream.
     * @param {string} version The version of the stream that is being fetched.
     * @param {object} stream_data The returned stream array. Just use the first entry for now.
     *      This will be an array of streams if the version includes an  'all'
     *
     * @return void
     */
    var onStreamFetched = function (domain, username, name, version, stream_data) {
        jQuery.each(stream_data.streams, function (i, stream) {
            BabblingBrook.Library.createNestedObjects(streams, [domain, username, name, stream.version]);
            streams[domain][username][name][stream.version] = stream;
            if (typeof deferred_streams[domain][username][name][stream.version] !== 'undefined') {
                deferred_streams[domain][username][name][stream.version].resolve();
            }
        });

        BabblingBrook.Library.createNestedObjects(streams, [domain, username, name, version]);
        streams[domain][username][name][version] = stream_data.streams[0];
        deferred_streams[domain][username][name][version].resolve();
    };

    /**
     * Callback for when a stream request returns an error.
     *
     * @param {function} onErrorCallback A callback to call if there is a 404 error.
     * @param {type} stream
     * @param {type} error_data
     * @returns {undefined}
     */
    var onStreamFetchedError = function (onErrorCallback, domain, username, name, version, error_data) {
        streams[domain][username][name][version].state = 'error';
        if (typeof error_data !== 'undefined') {
            streams[domain][username][name][version].error_data = error_data;
        }

        if (typeof onErrorCallback === 'function') {
            onErrorCallback(error_data);
        }

        deferred_streams[domain][username][name][version].resolve();
    };

    /**
     * Fetch a stream definition from its domus domain. Save it and then run the callback.
     *
     * @param {function} onErrorCallback A callback to call if there is a 404 error.
     * @param {string} domain The streams domain.
     * @param {string} username The name of the user who owns the stream.
     * @param {string} name The name of the stream.
     * @param {string} version The version of the stream that is being fetched.
     *
     * @return void
     * @fixme Test the validity of the stream data.
     */
    var getStreamFromDomus = function (errorCallback, domain, username, name, version) {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                stream : {
                    domain : domain,
                    username : username,
                    name : name,
                    version : version
                }
            },
            'FetchStream',
            onStreamFetched.bind(null, domain, username, name, version),
            onStreamFetchedError.bind(null, errorCallback, domain, username, name, version)
        );
    };

    /**
     * The default error callback.
     *
     * Sends a message to the user giving them the oppertunity to retry.
     *
    * @param {string} domain The streams domain.
    * @param {string} username The name of the user who owns the stream.
    * @param {string} name The name of the stream.
    * @param {string} version The version of the stream that is being fetched.
    * @param {function} successCallback The function to run once the stream has been fetched.
    *      It accepts one paramater - the stream object.
     *
     * @return void
     */
    var defaultError = function (domain, username, name, version, successCallback) {

        var message = 'A stream failed to load, please retry or reload the page.'

        var retry_button = {
            name : 'Retry',
            callback : function () {
                BabblingBrook.Client.Core.Streams.getStream(domain, username, name, version, successCallback);
            }
        };

        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : message,
            buttons : [retry_button]
        });
    }

    /**
     * Stores a stream in local storage.
     *
     * @param {type} stream The stream object to store.
     *
     * @returns {void}
     */
    var storeInLocalStorage = function (stream) {
        var stored_streams = BabblingBrook.LocalStorage.fetch('streams');
        if (stored_streams === false) {
            stored_streams = {};
        } else {
            stored_streams = stored_streams.data;
        }
        // Can't use the default domus domain id as streams are stored in nested objects, so have to fetch and restore
        // all of the streams together.
        BabblingBrook.Library.createNestedObjects(
            stored_streams,
            [stream.domain, stream.username, stream.name, stream.version]
        );
        stored_streams[stream.domain][stream.username][stream.name][stream.version] = stream;
        BabblingBrook.LocalStorage.store('streams', stored_streams);
    };

    return {

        /**
         * Fetch A stream definition.
         *
         * @param {string} domain The streams domain.
         * @param {string} username The name of the user who owns the stream.
         * @param {string} name The name of the stream.
         * @param {string} version The version of the stream that is being fetched.
         * @param {function} successCallback The function to run once the stream has been fetched.
         *      It accepts one paramater - the stream object.
         * @param {function} [errorCallback] The function to run if there is an error fetching the stream.
         *
         * @return object|false The relevant stream object or false.
         */
        getStream : function (domain, username, name, version, successCallback, errorCallback) {
            if (typeof domain !== 'string') {
                console.error(domain, username, name, version);
                console.error('Domain must be defined.');
                throw 'Thread execution stopped.';
            }

            if (typeof username !== 'string') {
                console.error(domain, username, name, version);
                console.error('Username must be defined.');
                throw 'Thread execution stopped.';
            }

            if (typeof name !== 'string') {
                console.error(domain, username, name, version);
                console.error('Name must be defined.');
                throw 'Thread execution stopped.';
            }

            if (typeof version !== 'string') {
                console.error(domain, username, name, version);
                console.error('Version must be defined.');
                console.trace();
                throw 'Thread execution stopped.';
            }

            if (typeof errorCallback !== 'function') {
                errorCallback = defaultError.bind(null, domain, username, name, version, successCallback);
            }

            if (BabblingBrook.Library.doesNestedObjectExist(streams, [domain, username, name, version]) === false) {
                BabblingBrook.Library.createNestedObjects(streams, [domain, username, name, version]);
            }
            var stream = streams[domain][username][name][version];
            var doesDeferredStreamExist = BabblingBrook.Library.doesNestedObjectExist(
                deferred_streams,
                [domain, username, name, version]
            );
            if (doesDeferredStreamExist === false) {
                BabblingBrook.Library.createNestedObjects(deferred_streams, [domain, username, name, version]);
                deferred_streams[domain][username][name][version] = jQuery.Deferred();
                stream.state = 'loading';
                getStreamFromDomus(errorCallback, domain, username, name, version);
            }

            deferred_streams[domain][username][name][version].done(
                function() {
                    var stream = streams[domain][username][name][version];
                    if (typeof stream === 'object' && stream.state === 'error') {
                        onStreamFetchedError(errorCallback, domain, username, name, version, stream.error_data);
                    } else {
                        successCallback(stream);
                    }
                }
            );
        },

        /**
         * Enables a stream to be stored in local storage for faster retrieval.
         *
         * Only fetches and stores the stream if it has not already been stored.
         *
         * @param {string} domain The domain of the stream to store.
         * @param {string} username The username of the stream to store.
         * @param {string} name The name of the stream to store.
         * @param {string} version The version of the stream to store.
         * @param {function} onFetchStreamError An error callback for when the stream fails to be fetched.
         *
         * @returns {void}
         */
        storeLocally : function (domain, username, name, version, onFetchStreamError) {

            var stored_streams = BabblingBrook.LocalStorage.fetch('streams');
            if (stored_streams === false) {
                stored_streams = {};
            } else {
                stored_streams = stored_streams.data;
            }
            var exists = BabblingBrook.Library.doesNestedObjectExist(
                stored_streams,
                [domain, username, name, version]
            );
            if (exists === false) {
                BabblingBrook.Client.Core.Streams.getStream(
                    domain,
                    username,
                    name,
                    version,
                    storeInLocalStorage,
                    onFetchStreamError
                );
            }

        },

        /**
         * Retrieves any streams stored in local storage.
         *
         * @returns {void}
         */
        setup : function () {
            var stream_data = BabblingBrook.LocalStorage.fetch('streams');
            if (stream_data === false) {
                streams = {};
            } else {
                streams = stream_data.data;
            }
        }
    };
})();

