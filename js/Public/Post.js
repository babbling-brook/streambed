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
 * @fileOverview Public post page functionality.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Public !== 'object') {
    BabblingBrook.Public = {};
}

BabblingBrook.Public.Post = (function () {
    'use strict';

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

    var isOverflowed = function(element){
        return element.scrollWidth > element.clientWidth;
    }

    return {

        construct : function () {

            BabblingBrook.Public.Library.classClick(['switch'], function() {
                var leaf_node = this.parentNode.parentNode;
                var parent_class = leaf_node.className;
                if (parent_class.indexOf(' closed') === -1) {
                    leaf_node.className += ' closed';
                } else {
                    leaf_node.className = leaf_node.className.replace(' closed', '');
                }
            });

            var login_click_elements = [
                'up-arrow',
                'down-arrow',
                'reply',
                'post-text-field',
                'open-post',
                'subscribe',
                'text-value',
                'button-value',
                'linear',
                'star',
                'sort_bar',
                'sorted'
            ];
            BabblingBrook.Public.Library.modalLogin(login_click_elements);

            resize();
        },

        resize : resize
    };
}());

window.onload = function() {
    BabblingBrook.Public.Post.construct();
    if (typeof BabblingBrook.Public.Resize !== 'undefined') {
        BabblingBrook.Public.Resize.construct();
    }
}