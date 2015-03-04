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

BabblingBrook.Public.Signup = (function () {
    'use strict';

    /**
     * Setup an individual help icon.
     *
     * @param {string} icon_name The name of the help element to setup.
     *
     * @return void
     */
    var setupHelpIcon = function(icon_name) {
        document.getElementById(icon_name + '_help_icon').onclick = function() {
            var help_text = document.getElementById(icon_name + '_help');
            if(help_text.className === 'hide') {
                help_text.className = 'content-block-2 less-content-margin readable-text';
            } else {
                help_text.className = 'hide';
            }
        };
    };

    /**
     * Sets up the help icons.
     *
     * @return void
     */
    var setupHelp = function() {
        setupHelpIcon('username');
        setupHelpIcon('email');
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
         * Sets up signup code.
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

            setupHelp();
        }
    };
}());

window.onload = function() {
    'use strict';
    BabblingBrook.Public.Signup.construct();
    BabblingBrook.Public.Resize.construct();
}