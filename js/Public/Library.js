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
 * @fileOverview Library of functions available on public pages.
 *
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Public !== 'object') {
    BabblingBrook.Public = {};
}

BabblingBrook.Public.Library = (function () {
    'use strict';


    /**
     * Create click events for all classes passed in.
     *
     * This is used by newer browsers that have getElementsByClassName.
     * Calls the click event with the clicked on DOM object as the this object
     *
     * @param {array} class_names The class names to attatch click events to.
     * @param {function} The callback to handle the click event.
     *
     * @return void
     */
    var classNameClick = function(class_names, clickEvent) {
        for(var i = 0; i < class_names.length; i++) {
            var elements = document.getElementsByClassName(class_names[i]);
            for(var j = 0; j < elements.length; j++) {
                elements[j].onclick = function() {
                    clickEvent.apply(this);
                    return false;   // Prevents links from being followed.
                };
            }
        }
    };

    /**
     * Loops through all DOM elements to find classes to attatch click events to.
     *
     * This is used by older browsers that don't have getElementsByClassName
     * Calls the click event with the clicked on DOM object as the this object
     *
     * @param {array} class_names The class names to attatch click events to.
     * @param {function} The callback to handle the click event.
     *
     * @return void
     */
    var classTagClick = function(class_names, clickEvent) {
        var elements = document.getElementsByTagName('*');
        for(var i = 0; i < elements.length; i++) {
            var element = elements[i];
            for(var j = 0; j < class_names.length; j++) {
                if(element.className.indexOf(class_names[j]) >= 0) {
                    element.onclick = function() {
                        clickEvent();
                        return false;   // Prevents links from being followed.
                    };
                }
            }
        }
    };

    var xmlhttp;

    /**
     * Callback for when a modal login node is clicked.
     *
     * @return void
     */
    var loginCallback = function() {
        var login_modal = document.getElementById('login-modal');
        login_modal.className = 'login-modal-show';

        document.querySelector('.ui-dialog-titlebar-close').onclick = function () {
            login_modal.className = 'hide';
        };

        var fileref = document.createElement('script');
        fileref.setAttribute('type','text/javascript');
        fileref.setAttribute('src', '/js/Public/Login.js?4');
        document.getElementsByTagName('head')[0].appendChild(fileref);

        // When the script has loaded we need to run the constructor as the window onload event has
        // already fired.
        var login_html = '';
        var onLoad = function () {
            if (login_html.length > 0 && typeof BabblingBrook.Public.Login !== 'undefined') {
                document.getElementById('modal_content').innerHTML = login_html;
                BabblingBrook.Public.Login.construct();
            } else {
                setTimeout(onLoad, 10);
            }
        };
        onLoad();

        /**
         * Callback for Loading the login form.
         */
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState === 4) {
                if (xmlhttp.status !== 200) {
                    console.error('Error whilst loading login form.');
                    return false;
                }
                document.getElementById('modal_content').className = '';
                login_html = xmlhttp.responseText;
            }
        };
        xmlhttp.open('POST', '/site/modallogin', true);
        xmlhttp.send();
        return false;
    }

    return {

        /**
         * Loops through all DOM elements to find classes to attatch click events to.
         *
         * @param {array} class_names The class names to attatch click events to.
         * @param {function} The callback to handle the click event.
         *
         * @return void
         */
        classClick : function(class_names, clickEvent) {
            if (typeof document.getElementsByClassName !== 'undefined') {
                classNameClick(class_names, clickEvent);
            } else {
                classTagClick(class_names, clickEvent);
            }
        },

        /**
         * Sets up the modal login pop up
         *
         * @param {array} An array of classes to use to capture click events.
         *
         * @return void
         */
        modalLogin : function(click_classes) {
            // Detect which kind of XMLHttpRequest object is available.
            // code for IE7+, Firefox, Chrome, Opera, Safari
            if (typeof window.XMLHttpRequest !== 'undefined' && window.XMLHttpRequest !== null) {
                xmlhttp = new XMLHttpRequest();
            } else { // code for IE6
                xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
            }

            // Attatch click events to all modal login triggers.
            // Use The fast method for modern brrowsers.
            BabblingBrook.Public.Library.classClick(click_classes, loginCallback);
        }

    };
}());