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
 * @fileOverview Public stream page functionality
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Public !== 'object') {
    BabblingBrook.Public = {};
}

BabblingBrook.Public.Stream = (function () {
    'use strict';

    var sort_drop_down_state = 'closed';
    var sort_bar_options;
    var sort_bar_title;

    var onSortClicked = function (event) {
        if (sort_drop_down_state === 'closed') {
            sort_bar_options.removeAttribute('class');
            sort_bar_title.setAttribute('class', 'active');
            sort_drop_down_state = 'open';
        } else {
            sort_bar_options.setAttribute('class', 'hide');
            sort_bar_title.removeAttribute('class');
            sort_drop_down_state = 'closed';
        }
        event.stopPropagation();
    };

    var onDocumentClicked = function () {
        if (sort_drop_down_state === 'open') {
            sort_bar_options.setAttribute('class', 'hide');
            sort_bar_title.removeAttribute('class');
            sort_drop_down_state = 'closed';
        }
    };

    return {

        construct : function () {

            var login_click_elements = [
                'up-arrow',
                'down-arrow',
                'subscribe',
                'make-post',
                'login_to_sort',
            ];
            BabblingBrook.Public.Library.modalLogin(login_click_elements);

            sort_bar_title = document.getElementById('sort_bar_title');
            sort_bar_options = document.getElementById('sort_bar_options');
            document.onclick = onDocumentClicked;
            document.getElementById('sort_bar_title').onclick = onSortClicked;

        }
    };
}());

window.onload = function() {
    BabblingBrook.Public.Stream.construct();
    BabblingBrook.Public.Resize.construct();
}