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
 * @fileOverview Login page functionality.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Public !== 'object') {
    BabblingBrook.Public = {};
}

BabblingBrook.Public.Login = (function () {
    'use strict';

    /**
     * Sets up the help icons.
     *
     * @return void
     */
    var setupHelp = function() {

        var page = document.getElementById('page');

        var dom_help_icon = document.querySelector('#page #login_help');
        var dom_help_text = document.querySelector('#page #login_help_text');
        if (dom_help_icon !== null) {
            dom_help_icon.onclick = function() {
                if(dom_help_text.className === 'hide') {
                    dom_help_text.className = 'content-block-2 readable-text';
                } else {
                    dom_help_text.className = 'hide';
                }
                return false;
            };
        }
    };

//    /**
//        * Generate a guid for requesting actions on remote domains.
//        *
//        * This is duplicated from the BabblingBrook library as it is not loaded at this point.
//        * Adapted from http://www.broofa.com/Tools/Math.uuid.js
//        */
//    var generateUUID = function () {
//
//        /*jslint bitwise: true*/
//        var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
//        var uuid = new Array(36), rnd = 0, r, i;
//        for (i = 0; i < 36; i++) {
//            if (i === 8 || i === 13 ||  i === 18 || i === 23) {
//                uuid[i] = '-';
//            } else if (i === 14) {
//                uuid[i] = '4';
//            } else {
//                if (rnd <= 0x02) {
//                    rnd = 0x2000000 + (Math.random() * 0x1000000) | 0;
//                }
//                r = (rnd & 0xf);
//                rnd = rnd >> 4;
//                uuid[i] = chars[(i === 19) ? (r & 0x3) | 0x8 : r];
//            }
//        }
//        /*jslint bitwise: true*/
//        return uuid.join('');
//    };

    var onActivationCodePostback = function (http_request, username, return_location) {
        if (http_request.readyState === 4) {
            if (http_request.status !== 200) {
                console.error('Error whilst checking an activation code is valid.');
                return;
            }
            var response_data = http_request.responseText;
            if (response_data.substr(0, 19) !== '&&&BABBLINGBROOK&&&') {
                throw 'JSON response data does not have a valid token to prevent JSON hijacking.';
            }
            var json_string = response_data.substr(19);
            var response = JSON.parse(json_string);
            if (response.valid === true) {
                redirectToDomus(username, return_location)

            } else {
                var error_element = document.getElementById('signup_code_error');
                error_element.className = 'error';
            }
        }
    };

    /**
     * This user has not yet entered a valid signup code.
     *
     * We need to get a valid one before they can be directed to their domus domain.
     *
     * @param username
     * @param return_location
     *
     * @returns {void}
     */
    var askForSignupCode = function (username, return_location) {
        document.getElementById('client_login').className = 'hide';
        document.getElementById('request_signup_code').className = 'content-indent';

        var http_request = new XMLHttpRequest();
        document.getElementById('signup_code_submit').onclick = function() {
            http_request.onreadystatechange = onActivationCodePostback.bind(
                null,
                http_request,
                username,
                return_location
            );
            http_request.open('POST', '/site/loginchecksignupcode', true);
            http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            var signup_code = document.getElementById('signup_code').value;
            http_request.send('full_username=' + username + '&signup_code=' + signup_code);
        };
    }

    /**
     * Redirect a user to their domus domain so that they can enter their password.
     *
     * Transfer to https so that a secret can be stored on the server.
     * The server will then transfer us to the users domus domain to check the passsword.
     *
     * @param username
     * @param return_location
     *
     * @returns {void}
     */
    var redirectToDomus = function (username, return_location) {
        var href = 'https://' + window.location.host
            + '/site/loginredirect?username=' + encodeURIComponent(username)
            + '&return_location=' + encodeURIComponent(return_location);
        window.location = href;
    };

    var onLoginClicked = function (form_selector) {
        var username_element = document.querySelector(form_selector + ' #username');
        username = username_element.value;
        var error_element = document.querySelector(form_selector + ' #login_error');
        var username = '';
        var http_request = new XMLHttpRequest();

        // Store the current location so that the logged in user can be redirected back to this location
        // - The login request might be from the modal login feature.
        var return_location = encodeURI(window.location.href);
        if(window.location.pathname.substr(0,11) === '/site/login') {
            return_location = '';
        }

        error_element.className = 'hide';
        username_element.className = 'textbox-loading';

        /**
         * Callback for checking a usernames validity.
         */
        http_request.onreadystatechange = function() {
            if (http_request.readyState === 4) {
                if (http_request.status !== 200) {
                    console.error('Error whilst checking a username is valid.');
                    return;
                }

                var response_data = http_request.responseText;
                if (response_data.substr(0, 19) !== '&&&BABBLINGBROOK&&&') {
                    throw 'JSON response data does not have a valid token to prevent JSON hijacking.';
                }
                var json_string = response_data.substr(19);
                var response = JSON.parse(json_string);

                if (response.exists === true) {
                    if (response.signup_code_status === false) {
                        askForSignupCode(username, return_location);
                    } else {
                        redirectToDomus(username, return_location);
                    }

                } else {
                    var error_message = 'Username not found.'
                    if (typeof response.error_message === 'string') {
                        error_message = response.error_message;
                    }
                    error_element.innerHTML = error_message;
                    username_element.className = '';
                    error_element.className = 'error';
                }
            }

        };
        http_request.open('POST', '/site/logincheckusername', true);
        http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        username = username_element.value;
        http_request.send('full_username=' + username);
    };

    /**
     * Setup the login form.
     */
    var setupLogin = function() {

        var hash = window.location.hash.substring(1);
        if (hash === 'helpopen') {
            var dom_help_text = document.querySelector('#page #login_help_text');
            dom_help_text.className = 'content-block-2 readable-text';
        }

        var dom_main_login_button = document.querySelector('#content .loggin-button');
        if (dom_main_login_button !== null) {
            dom_main_login_button.onclick = onLoginClicked.bind(null, '#content');
        }
        document.querySelector('#login-modal .loggin-button').onclick = onLoginClicked.bind(null, '#login-modal');

    };

    /**
     * Check browser compatibility requirements.
     */
    var cross_browser = function() {
        if (typeof window.XMLHttpRequest === 'undefined' || window.XMLHttpRequest === null) {
            return false;
        }
        if (typeof localStorage === 'undefined' || localStorage === null) {
            return false;
        }
        if (typeof window.postMessage === 'undefined' || window.postMessage === null) {
            return false;
        }
        // IE 11.
//        if (!(window.ActiveXObject) && "ActiveXObject" in window) {
//            return false;
//        }

        return true;
    };

    return {

        /**
         * Sets up login code.
         *
         * return void
         */
        construct : function () {
            if(cross_browser() === false) {
                var ok_browser = document.getElementById('ok_browser')
                ok_browser.parentNode.removeChild(ok_browser);
                var bad_browser = document.getElementById('bad_browser')
                bad_browser.className = '';
                return;
            }

            setupLogin();

            setupHelp();
        }
    };
}());

window.onload = function() {
    'use strict';
    if (typeof BabblingBrook.Client !== 'undefined') {
        return;
    }

    BabblingBrook.Public.Login.construct();
    BabblingBrook.Public.Resize.construct();

    // Set a hidden element to let selenium know that the constructor has run.]
    document.getElementById('login_constructor_has_run').value = 'true';
};
