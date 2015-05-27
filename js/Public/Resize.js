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
 * @fileOverview Public page resize functionality.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Public !== 'object') {
    BabblingBrook.Public = {};
}

BabblingBrook.Public.Resize = (function () {
    'use strict';

    var timer;

    var callResizeTests = function () {
        if (typeof BabblingBrook.Public.Stream !== 'undefined') {
             BabblingBrook.Public.Stream.resize();
        }
        if (typeof BabblingBrook.Public.Post !== 'undefined') {
             BabblingBrook.Public.Post.resize();
        }
    };

    /**
     * When the top nav shrinks, a button appears to access the top nav icons.
     *
     * @returns {undefined}
     */
    var setupTopNavButton = function () {
        var small_screen_menu = document.getElementById('small_screen_menu');
        if (small_screen_menu !== null) {
            small_screen_menu.onclick = function () {
                document.getElementById('top_nav_list').classList.toggle('small-screen-menu');
                var list_items = document.getElementById('top_nav_list');
                for (var i = 0; i < list_items.childNodes.length; i++) {
                    if (list_items.childNodes[i].tagName === 'LI' && list_items.childNodes[i].id !== 'small_screen_menu') {
                        list_items.childNodes[i].classList.toggle('small-screen-menu');
                    }
                }
                return false;
            };
        }
    };

    return {

        construct : function () {
            setupTopNavButton();

            window.onresize = function(event) {
                // Only resize once every so often to prevent slow down.
                if (typeof timer === 'number') {
                    clearTimeout(timer);
                }

                timer = setTimeout(function () {
                    callResizeTests()
                }, 25);
            }
        }
    };
}());