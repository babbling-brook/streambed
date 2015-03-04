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
 * @fileOverview Deals with validating usernames both local and remote.
 * @author Sky Wickenden
 */

/**
 * Deals with validating usernames both local and remote.
 */
BabblingBrook.Client.Core.LookupUser = (function () {
    'use strict';

    var onUsernamesFetched = function (domain, onSuccess, suggestions) {
        var suggestions_length = suggestions.usernames.length;
        for(var i=0; i < suggestions_length; i++) {
            suggestions.usernames[i] = suggestions.usernames[i] + '@' + domain;
        }
        onSuccess(suggestions.usernames);
    }

    /**
     * Fetch suggestions from a partial username.
     *
     * Sends the request to the scientia iframe of the given domain.
     *
     * @param {string} domain The domain name to check the usernames in.
     * @param {string} partial_username The username to generate suggestions from.
     * @param {function} onSuccess The function to call with the suggestions.
     *      It takes an array paramater conting a list of suggested valid domains.
     * @param {function} onError The function to call if the suggestions process fails.
     *
     * @return void
     */
    var fetchUsernameSuggestions = function(domain, partial_username, onSuccess, onError) {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : domain,
                partial_username : partial_username
            },
            'FetchUsernameSuggestions',
            onUsernamesFetched.bind(null, domain, onSuccess),
            onError
        );
    };

    /**
     * Assert that a domain name is a valid saltnet domus domain.
     *
     * Sends the request to the domus domain - which attempts to load the scientia iframe of that domain.
     *
     * @param {string} domain The domain name to check.
     * @param {function} successCallback The function to call if the domain is valid.
     * @param {function} errorCallback The function to call if the domain is not valid.
     *
     *
     * @return void
     */
    var assertDomainValid = function(domain, successCallback, errorCallback) {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : domain
            },
            'CheckDomainValid',
            successCallback,
            errorCallback
        );
    };

    /**
     * Suggestions with both usernames and suggestions have been fetched.
     *
     * @param {funciton} onSuccess The function to call when suggestions have been generated.
     * @param {object} response_data The data returned from the server.
     * @parma {string[]} response_data.suggestions An array of username suggetsions.
     *
     * @returns {undefined}
     */
    var onUsernameAndDomainSuggestionsFetched = function (onSuccess, response_data) {
        onSuccess(response_data.suggestions);
    };

    /**
     * Suggestions have been fetcheed from the users domain. If the first matches then get resutls from there
     * if it doesn't then search the users store for partial username and domain matches.
     *
     * @param {type} username
     * @param {type} partial_domain
     * @param {type} onSuccess
     * @param {type} onError
     * @param {type} suggestions
     *
     * @returns {undefined}
     */
    var onDomainSuggestionsFetched = function (username, partial_domain, onSuccess, onError, suggestions) {
        var suggestions_length = suggestions.domains.length;
        var matching_domain = false;
        for(var i=0; i < suggestions_length; i++) {
            if (suggestions.domains[i] === partial_domain) {
                matching_domain = suggestions.domains[i];
            }
        }

        if (matching_domain !== false) {
            fetchUsernameSuggestions(
                matching_domain,
                username,
                onSuccess,
                onError
            );
        } else {
            BabblingBrook.Client.Core.Interact.postAMessage(
                {
                    partial_domain : partial_domain,
                    partial_username : username
                },
                'FetchUsernameAndDomainSuggestions',
                onUsernameAndDomainSuggestionsFetched.bind(null, onSuccess),
                onError
            );
        }
    };

    /**
     * Fetch domain suggestions from the logged on users domus domain.
     *
     * @param {string} username The username that is being searched for.
     * @param {string} partial_domain A partial domain name to match the username.
     * @param {function} onSuccess The function to call with the suggestions.
     *      It takes an array paramater conting a list of suggested valid domains.
     * @param {function} onError The function to call if the suggestions process fails.
     *
     * @return void
     */
    var fetchDomainSuggestions = function (username, partial_domain, onSuccess, onError) {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                partial_domain : partial_domain
            },
            'FetchDomainSuggestions',
            onDomainSuggestionsFetched.bind(null, username, partial_domain, onSuccess, onError),
            onError
        );
    };

    /**
     * Validates if a username is valid or not.
     *
     * @param domain The domain to use when checkig the username.
     * @param username The username to check
     * @param validCallback The callback to use if the domain is valid.
     * @param invalidCallback The callback to use id the domain is not valid.
     *
     * @return void
     */
    var validateUsername = function (domain, username, validCallback, invalidCallback) {
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                domain : domain,
                username : username
            },
            'CheckUsernameValid',
            validCallback,
            invalidCallback
        );
    };

    /**
     * Callback after a request to check if a username is valid.
     *
     * @param {object} valid_data Container object.
     * @param {boolean} valid_data.valid Is the username valid or not.
     * @param {function} original_callback The callback that was registered with this validation request.
     *      See public valid function for details.
     *
     * @return void
     */
    var validUsernameCallback = function (original_callback, valid_data) {
        if(valid_data.valid === true) {
            original_callback(true);
        } else {
            original_callback(false, 'username');
        }
    };

    return {

        /**
         * Asserts if a full username is valid or not.
         *
         * @param {string} The full username to check.
         * @param {function} callback The callback function.
         *      Accepts two paramaters
         *          boolean valid
         *          string error_type
         *              Two valid value - 'username' and 'domain'
         *
         * @return void
         */
        valid : function (full_username, callback) {
            if (BabblingBrook.Test.isA([full_username, 'full-username'], '', false) === false) {
                callback(false, 'username');
                return;
            }

            var invalidUsernameCallback = callback.bind(null, false, 'username');
            var validCallback = validUsernameCallback.bind(null, callback);

            var domain = BabblingBrook.Library.extractDomainFromFullUsername(full_username);
            var username = BabblingBrook.Library.extractUsernameFromFullUsername(full_username);
            assertDomainValid(
                domain,
                validateUsername.bind(null, domain, username, validCallback, invalidUsernameCallback),
                callback.bind(null, false, 'domain')
            );
            // check domain
            // check username
            // ... display tick or error message.
        },


        /**
         * Fetches suggestions for a partial full username.
         *
         * If there is no @ in the username then it searches for local usernames logged on users domus domain.
         * If there is an @ then it first checks if the domain is complete. If it is then it searches
         * for matching usernames at that domain. If it is not then searches for partial username/domains
         * in the user store db.
         *
         * @param {string} partial_username The partial username of the user to lookup.
         * @param {function} onSuggestionsFetched The callback function. It must accept two paramaters
         *      The first is an array of suggestions.
         *      The second is an error flag indicating that the domain was not found.
         * @param {function} callback The callback function. If the domain is not found then this is called.
         *      It requires one paramater - the domain that is in error.
         *
         * @return void
         */
        suggest : function (partial_username, onSuggestionsFetched, onError) {
            if (partial_username.indexOf('@') > 0) {
                var username_parts = partial_username.split('@');
                fetchDomainSuggestions(username_parts[0], username_parts[1], onSuggestionsFetched, onError);
            } else {
                fetchUsernameSuggestions(
                    BabblingBrook.Client.User.domain,
                    partial_username,
                    onSuggestionsFetched,
                    onError
                );
            }
        }

    };
}());