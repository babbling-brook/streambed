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
 * @fileOverview Handles resizing of the screen
 * @author Sky Wickenden
 */

/**
 * @namespace Module that hanndles resizing of the screen. On detection of resize events various other modules
 * are called to update themselves.
 *
 * @package JS_Client
 */
BabblingBrook.Client.Component.Resize = (function () {
    'use strict';

    var timer;

    /**
     * When the side bar shrinks, a button appears to make it take up the full width of the screen.
     *
     * @returns {undefined}
     */
    var setupSideBarButton = function () {
        jQuery('body').on('click', '#sidebar_open', function () {
            jQuery('#sidebar_open').toggleClass('openable-button closable-button');
            jQuery('#sidebar_extra').toggleClass('sidebar-hide');
            return false;
        });
    };

    var onSideNavResized = function () {
console.debug('resize');
        if (jQuery('body').width() < 1024 && jQuery('#sidebar_open').hasClass('hide') === true) {
            jQuery('#sidebar_open').removeClass('hide');
            jQuery('#sidebar_extra').addClass('sidebar-hide');
        } else if (jQuery('body').width() >= 1024
            && jQuery('#sidebar_open').hasClass('hide') === false
            && jQuery('#sidebar').hasClass('sidebar-top') === false
        ) {
            jQuery('#sidebar_open').addClass('hide');
        } else  if (jQuery('body').width() >= 1024 && jQuery('#sidebar').hasClass('sidebar-top') === false) {
            jQuery('#sidebar_extra').removeClass('sidebar-hide');
        }
    };

    /**
     * When the top nav shrinks, a button appears to access the top nav icons.
     *
     * @returns {undefined}
     */
    var setupTopNavButton = function () {
        jQuery('body').on('click', '#small_screen_menu', function () {
            jQuery('#top_nav_list li').not('#small_screen_menu').toggleClass('small-screen-menu');
            jQuery('#top_nav_list').toggleClass('small-screen-menu');
            return false;
        });
    };



    /**
     * Calls the various resize test functions.
     *
     * @returns {undefined}
     */
    var callResizeTests = function () {
        BabblingBrook.Client.Component.StreamNav.reshow();
        onSideNavResized();
    };

    return {
        construct : function () {
            callResizeTests();
            setupSideBarButton();
            setupTopNavButton();
            jQuery(window).resize(function(){

                // Only resize once every so often to prevent slow down.
                if (typeof timer === 'number') {
                    clearTimeout(timer);
                }

                timer = setTimeout(function () {
                    callResizeTests()
                }, 25);
            });
        },

        /**
         * Retest the resize conditions.
         *
         * Called by other modules when conditions have changed without the screen haveing been resized.
         *
         * @returns {undefined}
         */
        retest : function () {
            callResizeTests();
        }
    };


})();