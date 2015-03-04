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
 * @fileOverview Fetches suggestions or hints for a textbox and shows them in a list below the textbox.
 * @author Sky Wickenden
 */

BabblingBrook.Client.Component.HelpHints = (function () {
    'use strict';

    /**
     * Called before a suggestion is selected.
     *
     * Copies the content of the visible input to the real hidden input and
     * displays the new selection in the list if multiple is on.
     *
     * @param {object} jq_input See attatch method.
     * @param {object} jq_container See attatch method.
     *
     * @returns {undefined}
     */
    var onBeforeSelected = function (jq_input, jq_container, multiple) {
        if (multiple === true) {
            var jq_selected = jQuery('ul.open-list-selected>li', jq_container);
            var list = '';
            jq_selected.each(function(i, item) {
                if (list !== '') {
                    list += ','
                }
                list += jQuery(item).text();
            });
            jq_input.val(list);
        } else {
            var jq_hint_input = jQuery('input', jq_container);
            jq_input.val(jq_hint_input.val());
        }
    };

    /**
     * Adds the current content of the hit textbox to the tag list.
     *
     * @param {type} jq_container See attatch method.
     * @param {type} multiple See attatch method.
     *
     * @returns {void}
     */
    var addSelection = function (jq_container, multiple) {
        if (multiple === true) {
            var jq_hint_input = jQuery('input', jq_container);
            var jq_selected = jQuery('.open-list-selected', jq_container);
            var selected_text = jq_hint_input.val();
            if (selected_text.substr(selected_text.length - 1) === ',') {
                selected_text = selected_text.substr(0, selected_text.length - 1);
            }
            if (selected_text.length > 0) {
                var jq_selected_line = jQuery('#open_list_suggestions_selected_line_template>li').clone();
                jq_selected_line.text(selected_text);
                jq_selected.append(jq_selected_line);
            }
            jq_hint_input.val('');
        }
    };

    /**
     * Callback for when some suggestions have been fetched, so that the list can be displayed and selected.
     *
     * @param {object} jq_container See attatch method.
     * @param {array} suggestions An array of suggestions strings
     *
     * @returns {void}
     */
    var onFetchedSuggestions = function (jq_container, suggestions) {
        var jq_line;
        var jq_suggestions = jQuery('ul.open-list-suggestions', jq_container);
        jq_suggestions.empty().addClass('hide');

        if (suggestions.length > 0) {
            jq_suggestions.removeClass('hide');
        } else {
            return;
        }

        for (var i=0; i < suggestions.length; i++) {
            jq_line = jQuery('#open_list_suggestions_line_template>li').clone();
            jq_line.text(suggestions[i]);
            jq_suggestions.append(jq_line);
        }

        // Select using cursor keys
        jq_suggestions.addClass('mouse-hover');
    };

    /**
     * Converts existing tags on the input field into clickable labels.
     *
     * Works by simulating the existing tags being manually enetered.
     *
     * @param {type} jq_input See attatch method.
     * @param {type} jq_container See attatch method.
     * @param {type} multiple See attatch method.
     *
     * @returns {void}
     */
    var applyExistingTags = function (jq_input, jq_container, multiple) {
        if (multiple === true) {
            var jq_hint_input = jQuery('input', jq_container);
            var list = jq_input.val();
            var list_array = list.split(',');
            for (var i = 0; i < list.length; i++) {
                jq_hint_input.val(list_array[i]);
                addSelection(jq_container, multiple);
            }
        }
    };

    return {

        /**
         * Attatches a listner onto textbox to show suggestions when the content of the textbox changes.
         *
         * @param {object} jq_input JQuery object poiniting to the input field that is being monitored.
         * @param {function} onSelected A callback function to call when a new option has been selected.
         * @param {function} onContentChanged A function to call to fetch the list of suggestions.
         *      It needs to accept two paramaters. The first contains the content of the textbox/
         *      The second is the function to call with the suggestions, which should be an array of strings.
         * @param {function} onSelected The callback to call when an item has been selected.
         *      Accepts one paramater - the text that was selected.
         * @returns {undefined}
         */
        attatch : function (jq_input, onContentChanged, onSelected, multiple) {
            jq_input.addClass('open-list-input');
            var jq_container = jQuery('#open_list_suggestions_template>div').clone();
            applyExistingTags(jq_input, jq_container, multiple);
            jq_input
                .after(jq_container)
                .addClass('hide');
            var jq_hint_input = jQuery('input', jq_container);
            var jq_selected;

            jq_hint_input.on(
                'keyup',
                function (event) {
                    var jq_suggestions = jQuery('ul.open-list-suggestions', jq_container);

                    // Return or right arrow pressed when there is a selected item.
                    if ((event.which === 13 || event.which === 39)&& typeof jq_selected !== 'undefined') {
                        jq_hint_input.val(jq_selected.text());
                        addSelection(jq_container, multiple);
                        onBeforeSelected(jq_input, jq_container, multiple);
                        onSelected();
                        jq_suggestions.empty().addClass('hide');
                        jq_selected = undefined;

                    // Up or down arrow pressed.
                    } else if (event.which === 38) {
                        if (typeof jq_selected !== 'undefined') {
                            jq_selected.removeClass('hover');
                        }
                        jq_suggestions.removeClass('mouse-hover');
                        if (typeof jq_selected === 'undefined') {
                            jq_selected = jq_suggestions.find(':last-child');
                        } else if (jq_selected.prev().length > 0) {
                            jq_selected = jq_selected.prev();
                        } else {
                            jq_selected = jq_suggestions.find(':last-child');
                        }
                        jq_selected.addClass('hover');
                    } else if (event.which === 40) {
                        if (typeof jq_selected !== 'undefined') {
                            jq_selected.removeClass('hover');
                        }
                        jq_suggestions.removeClass('mouse-hover');
                        if (typeof jq_selected === 'undefined') {
                            jq_selected = jq_suggestions.find(':first-child');
                        } else if (jq_selected.next().length > 0) {
                            jq_selected = jq_selected.next();
                        } else {
                            jq_selected = jq_suggestions.find(':first-child');
                        }
                        jq_selected.addClass('hover');

                    // comma or return pressed - enter a new tag if multiple is selected.
                    } else if ((event.which === 188 || event.which === 13) && multiple === true) {
                        addSelection(jq_container, multiple);
                        onBeforeSelected(jq_input, jq_container, multiple);
                        onSelected();
                        jq_suggestions.empty().addClass('hide');
                        jq_selected = undefined;
                        // prevent return from submitting the form.
                        event.stopPropagation();

                    // Fetch the suggestions.
                    } else {
                        onContentChanged(jq_hint_input.val(), onFetchedSuggestions.bind(null, jq_container));
                        jq_selected = undefined;
                    }

                }
            );

            // mousedown rather than click because click works on mouseup, which fires later than the blur event.
            jQuery('ul.open-list-suggestions', jq_container).on('mousedown' , 'li', function () {
                jq_hint_input.val(jQuery(this).text());
                addSelection(jq_container, multiple);
                onBeforeSelected(jq_input, jq_hint_input, multiple);
                onSelected();
                jQuery('ul.open-list-suggestions', jq_container).empty().addClass('hide');
            });
            jQuery('ul.open-list-suggestions', jq_container).on('mouseover', function () {
                jQuery('ul.open-list-suggestions', jq_container).addClass('mouse-hover');
                jQuery(jQuery('ul.open-list-suggestions>li', jq_container)).removeClass('hover');
            })

//            jq_hint_input.on('blur', function() {
//                // In a set timeout to ensure any clicks on the suggestions have time to register.
//                setTimeout(function () {
//                    addSelection(jq_container, multiple);
//                    onBeforeSelected(jq_input, jq_container, multiple);
//                    onSelected();
//                    jQuery('ul.open-list-suggestions', jq_container).empty().addClass('hide');
//                }, 30);
//            });

            jQuery('.open-list-selected', jq_container).on('click', 'li' , function () {
                jQuery(this).remove();
                onBeforeSelected(jq_input, jq_container, multiple);
                onSelected();
            });

        }

    };
}());


