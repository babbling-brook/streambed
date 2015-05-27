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

    var onSidebarOpenerClicked = function () {
        var dom_sidebar_opener = document.getElementById('sidebar_open');
        var dom_description = document.querySelector('#sidebar .description');
        if (dom_sidebar_opener.classList.contains('openable-button') === true) {
            dom_description.classList.add('description-open');
        } else {
            dom_description.classList.remove('description-open');
        }

        //dom_filter_details.classList.toggle('hide');
        dom_sidebar_opener.classList.toggle('openable-button');
        dom_sidebar_opener.classList.toggle('closable-button');
    };

    var isOverflowed = function(element){
        return element.scrollWidth > element.clientWidth;
    }

    var resize = function () {
        var dom_sidebar_opener = document.getElementById('sidebar_open');
        var dom_description = document.querySelector('#sidebar .description');
        var dom_sidebar_opener = document.getElementById('sidebar_open');
        if (isOverflowed(dom_description) === false) {
            dom_sidebar_opener.classList.add('hide');
        } else {
            dom_sidebar_opener.classList.remove('hide')
        }
    };

    return {

        construct : function () {

            var image_stream = document.getElementById('image_stream').value;
            if (image_stream === 'true') {
                document.getElementById('stream_container').removeAttribute("class");
                jQuery("#stream_container").justifiedGallery({
                    rowHeight : 250,
                    margins : 5,
                    captions : true
                });
            }

            var login_click_elements = [
                'up-arrow',
                'down-arrow',
                'subscribe',
                'make-post',
                'login_to_sort',
                'sorted',
            ];
            BabblingBrook.Public.Library.modalLogin(login_click_elements);

            if (sort_bar_title === null) {
                sort_bar_title = document.getElementById('sort_bar_title');
                sort_bar_options = document.getElementById('sort_bar_options');
                document.onclick = onDocumentClicked;
                sort_bar_title.onclick = onSortClicked;
            }

            var dom_sidebar_opener = document.getElementById('sidebar_open');
            dom_sidebar_opener.addEventListener('click', onSidebarOpenerClicked);

            resize();

        },

        resize : resize
    };
}());

window.onload = function() {
    BabblingBrook.Public.Stream.construct();
    if (typeof BabblingBrook.Public.Resize !== 'undefined') {
        BabblingBrook.Public.Resize.construct();
    }


}