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
 * @fileOverview Shows a list of suggestions using the TableOpener.js module.
 * @author Sky Wickenden
 */

/**
 * Shows a list of suggestions using the TableOpener.js module.
 */
BabblingBrook.Client.Component.Suggestions = function (jq_opener, opener_text, closer_text, suggestion_type,
    dom_id, recreate_from_history_function, actions
) {
    'use strict';

    var jq_opener;

    var jq_opener_link;

    var jq_container;

    var jq_table;

    var table_ux;

    var onSuggestionsFetched = function (suggestions) {
        jq_table.empty();
        for (var i = 0; i < suggestions.length; i++) {
            var suggestion = suggestions[i];
            var url = BabblingBrook.Library.makeStreamUrl(suggestion, 'view');
            var jq_suggestion_row = jQuery('<tr>');
            var jq_name_column = jQuery('<td>');
            jq_name_column
                .text(suggestion.name)
                .attr('title', url)
                .addClass('suggestion-name');
            jq_suggestion_row.append(jq_name_column);

            for (var j = 0; j < actions.length; j++) {
                var action = actions[j];
                var jq_action_column = jQuery('<td>');
                var jq_action_link = jQuery('<a>');
                jq_action_link.text(action.name);
                jq_action_column.append(jq_action_link);
                jq_suggestion_row.append(jq_action_column);
                jq_action_link.click(action.onClick.bind(null, suggestion, jq_suggestion_row));
            }
            jq_table.append(jq_suggestion_row);
        }
        if (suggestions.length === 0) {
            var jq_empty_row = jQuery('#Component_Suggestions_no_results_template>tbody>tr').clone();
            jq_table.append(jq_empty_row);
        }
        table_ux.onChange(1);
    };

    /**
     * Event for when the opener link is clicked.
     */
    var onOpen = function () {
        //table_ux.onBeforeChange()
        createSuggestionsTable();
        BabblingBrook.Client.Core.Suggestion.fetch(
            suggestion_type,
            onSuggestionsFetched,
            {}
        );
    };

    var createSuggestionsTable = function () {
        jq_container.empty();
        jq_table = jQuery('#display_suggestions_table_template>table').clone();
        jq_table.attr('id', dom_id);
        jq_container.append(jq_table);
    };

    var setup = function () {
        jq_opener.addClass('closed display-suggestions');
        var jq_opener_contents = jQuery('#display_suggestions_template').children().clone();
        jq_opener.append(jq_opener_contents);
        jq_opener_link = jq_opener.children('a');
        jq_container = jq_opener.children('div');
        jq_opener_link.text(opener_text);
        dom_id = 'display_suggestions_' + dom_id;

        var table_selector = '#' + dom_id;
        table_ux = BabblingBrook.Client.Component.TableOpenerUX(
            opener_text,
            closer_text,
            jq_opener,
            jq_container,
            table_selector,
            onOpen,
            recreate_from_history_function
        );
    };
    setup();

    return {

        /**
         * Opens the susggestions as if they had been clicked on.
         */
        autoOpen : function () {
            if (table_ux.getState() === 'closed') {
                table_ux.onOpen();
            }
        }
    };
};

