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
 * @fileOverview UX for Opening and closing tables.
 * @author Sky Wickenden
 */

/**
 * @namespace Javascript singleton that works with the ring admin page.
 *
 * @param {string} opener_text The text for the link that is used to open the table.
 * @param {string} closer_text The text for the link that is used to close the table.
 * @param {object} jq_opener JQuery object pointing to the container that holds both the
 *      opener text link and the table container.
 * @param {object} jq_container The jQuery object pointing to the table container.
 * @param {string} table_dom_selector A jQuery select statment for selecting the table that is being opened/closed.
 * @param {function} onOpen A callback that is run when the table is opened.
 * @param {string} recreate_from_history_function If a history state is to be created then this should contain
 *      a string that names the function in the module that should be called when the page reloads.
 * @return {object} Public funcitons that can be called for this module.
 */
BabblingBrook.Client.Component.TableOpenerUX = function (opener_text, closer_text, jq_opener, jq_container,
    table_dom_selector, onOpen, recreate_from_history_function
    ) {
    'use strict';

    var duration = 200;

    var jq_opener_link = jq_opener.children('a');

    var state = 'closed';

    /**
     * Jquery selector for the temporary display of the previous table over the top of the new one
     * (The new one needs to be created so its size can be worked out before the old one can close.)
     */
    var jq_table_previous;

    var openContainer = function () {
        jq_opener_link
            .text(closer_text)
        jq_opener
            .addClass('block-loading')
            .addClass('open', 200)
            .removeClass('closed', 200);
        onOpen();
        state = 'open';
    };

    var closeContainer = function () {
        var jq_table = jQuery(table_dom_selector);
        jq_table.animate(
            {
                opacity : 0
            },
            {
                duration : duration,
                complete : function () {
                    var jq_div = jQuery('<div>');
                    jq_div.height(jq_table.outerHeight());
                    jq_div.width(jq_table.outerWidth());
                    jq_table.replaceWith(jq_div);
                    jq_opener.removeClass('block-loading');
                    jq_container.slideUp(200, function () {
                        jq_table.remove();
                        jq_opener
                            .addClass('closed', 200)
                            .removeClass('open', 200);
                        jq_container
                            .removeAttr('style')
                            .addClass('hide', 200);
                        jq_div.remove();
                    });
                }
            }
        );
        state = 'closed';
    };

    var onOpenerClicked = function (event) {
        event.preventDefault();
        if (state === 'closed') {
            openContainer();
        } else {
            jq_opener_link.text(opener_text);
            closeContainer();
        }
        return false;
    };

    /**
     * The old search table has been hidden. time to display the new one.
     *
     * @returns {undefined}
     */
    var onDisplayNewTable = function (jq_table, current_page) {
        jq_table.css(
            {
                position: 'absolute',
                visibility : 'hidden'
            }
        );
        jq_container.removeClass('hide');
        var height = jq_table.outerHeight();
        var width = jq_table.outerWidth();
        jq_table
            .addClass('hide')
            .removeAttr('style');
        jq_container
            .animate(
                {
                    height : height,
                    width : width
                },
                {
                    duration : duration,
                    complete : function () {
                        jq_table
                            .removeClass('hide')
                            .css({opacity : 0})
                            .animate(
                                {
                                    opacity : 1
                                },
                                {
                                    duration : duration,
                                    complete : function () {
                                        jq_table.removeAttr('style');
                                        jq_container.css({'height' : ''});
                                        jq_opener.removeClass('block-loading');
                                       // jq_search.removeAttr('style');
                                    }
                                }
                            );
                    }
                }
            );
        if (typeof recreate_from_history_function === 'string') {
            // Add a pushstate so that the selector is reopend if the backbutton is pushed.
            BabblingBrook.Client.Core.Ajaxurl.changeUrl(
                window.location.href,
                recreate_from_history_function,
                document.title,
                [current_page]
            );
        }
    };

    var setup = function () {
        jq_opener_link
            .text(opener_text)
            .click(onOpenerClicked);
    };
    setup();

    return {
        /**
         * Enables the table to be opened externaly.
         */
        onOpen : function () {
            openContainer();
        },

        /**
         * Enables the table to be closed externaly.
         */
        onClose : function () {
            closeContainer();
        },

        /**
         * Event that should be called before the table is changed.
         */
        onBeforeChange : function () {
            var jq_table = jQuery(table_dom_selector);
            if (jq_table.length > 0) {
                jq_container.css('height', jq_container.height() + 'px');
                jq_table_previous = jq_table.clone();
                jq_table_previous
                    .css({position:'absolute', zIndex : '1000'})
                    .attr('id', '')
                    .removeClass('block-loading');
                jq_table.css(
                    {
                        position: 'absolute',
                        visibility : 'hidden'
                    }
                );
                jq_table.before(jq_table_previous);
            }
        },

        /**
         * Event to call when the data has been changed.
         */
        onChange : function (current_page) {
            // If reloading from a history state then ensure everything is open.
            state = 'open';
            jq_opener
                .removeClass('closed')
                .addClass('open');
            jq_opener_link.text(closer_text);

            var jq_table = jQuery(table_dom_selector);
            if (typeof jq_table_previous === 'undefined' || jq_table_previous.length < 1) {
                onDisplayNewTable(jq_table, current_page);
            } else {
                //var start_height = jq_table_previous.height();
                jq_table_previous.animate(
                    {
                        opacity : 0
                    },
                    {
                        duration : duration,
                        complete : function () {
                            jq_table_previous.remove();
                            onDisplayNewTable(jq_table, current_page);
                        }
                    }
                );
            }
        },

        /**
         * Returns the current open state of the table.
         *
         * @returns {string} 'open' or 'closed'.
         */
        getState : function () {
            return state;
        }
    };
};


