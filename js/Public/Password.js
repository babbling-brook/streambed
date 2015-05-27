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

BabblingBrook.Public.Password = (function () {
    'use strict';

    /**
     * Get an url paramater from the current page url.
     *
     * @param {string} name The name of the paramater to fetch a value for.
     *
     * @return string|boolean The paramater value or false.
     */
    var getUrlParameter = function(name) {
        var searchString = window.location.search.substring(1);
        var params = searchString.split('&');
        var i, val;

        for (i = 0; i < params.length; i++) {
            val = params[i].split('=');
            if (val[0] === name) {
                return val[1];
            }
        }
        return false;
    };


    return {

        /**
         * Sets up login code.
         *
         * return void
         */
        construct : function () {

            var client_domain = getUrlParameter('client_domain');
            if(client_domain === false) {
                alert('Error: Site requesting login is not sending its domain.');
                return;
            }
            var secret = getUrlParameter('secret');
            if(secret === false) {
                alert('Error: Site requesting login is not sending a login secret.');
                return;
            }

            if(client_domain === window.location.host) {
                document.getElementById('local_site_row').classList.remove('hide');
            } else {
                document.getElementById('remote_site_name').innerHTML(client_domain);
                document.getElementById('remote_site_row').classList.remove('hide');
            }

            document.getElementById('password_submit').onclick = function() {
                document.forms["password"].submit();
//                var dom_password = document.getElementById('password');
//                var http_request = new XMLHttpRequest();
//                var remember_me, username, password;
//                dom_password.className = 'textbox-loading';
//
//                /**
//                 * Callback to handle login callback requests.
//                 *
//                 * @return void
//                 */
//                http_request.onreadystatechange = function() {
//                    if (http_request.readyState === 4) {
//                        if (http_request.status !== 200) {
//                             console.error('Error processing password');
//                             return;
//                        } else {
//                            var response = JSON.parse(http_request.responseText);
//                            if (response.success === true) {
//                                var href = 'https://' + client_domain + '/site/loggedin?secret='
//                                    + secret
//                                    + '&remember_time=' + response.remember_time;
//
//                                window.location = href;
//                                return;
//                            } else {
//                                var error_element = document.getElementById('password_error');
//                                error_element.className = 'error';
//                                error_element.innerHTML = response.error;
//                            }
//                        }
//
//                        dom_password.className = '';
//                    }
//                };
//                http_request.open('POST', '/site/passwordcheck', true);
//                http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
//                username = encodeURIComponent(document.getElementById('username').innerHTML);
//                password = encodeURIComponent(document.getElementById('password').value);
//                remember_me = document.getElementById('remember_me').checked;
//                http_request.send('username=' + username
//                    + '&password=' + password
//                    + '&remember_me=' + remember_me
//                    + '&client_domain=' + client_domain);
            };

        }
    };
}());

window.onload = function() {
    BabblingBrook.Public.Password.construct();
    BabblingBrook.Public.Resize.construct();

    // Set a hidden element to let selenium know that the constructor has run.
    document.getElementById('password_constructor_has_run').value = 'true';
}