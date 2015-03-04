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
 * @fileOverview Integrates rich text editors with Babbling Brook.
 * @author Sky Wickenden
 */

/**
 * Integrates rich text editors with Babbling Brook.
 *
 * Babbling Brook uses this class to interface with rich text editors.
 * Simply create an adapter class in th e/js/Client/Component/RichTextAdapters folder.
 * (see CKEditorAdapter for an example) and then set the RichTextEditorClass config to use that file.
 * The adapter class must include the same public methods as this one.
 *
 * @param {string} textarea_id The id of the textarea that is being replaced with a rich text editor instance.

 */
BabblingBrook.Client.Component.RichTextFacade = function (textarea_id) {
    'use strict';
    var Client = BabblingBrook.Client;
    if (typeof Client.Component.RichTextAdapters[Client.User.Config.rich_text_editor_adapter] !== 'function') {
        throw 'RichTextFacade is not valid : ' + Client.User.Config.rich_text_editor_adapter;
    }

    var rich_text_editor = Client.Component.RichTextAdapters[Client.User.Config.rich_text_editor_adapter](textarea_id);

    return {

        /**
         * Generate a rich text area instance for a StreamTextField.
         *
         * @param {object} stream A standard stream object.
         *      Used to retrieve the html options available for this instance.
         * @param {number} row_id The id of the row in the stream that this instance is being created for.
         * @param {string} post_dom_id The id of the container for the stream in the dom.
         * @param {function} openMainField A callback to call when the stream is opened.
         * @param {boolean} new_post Is this a new post or an edited post?
         * @param {function} onBlur The callback to run when the CKEditor instance looses focus.
         *
         * @returns {undefined}
         */
        generateStreamTextField : function (stream, row_id, post_dom_id, openMainField, new_post, onBlur) {
            rich_text_editor.generateStreamTextField(stream, row_id, post_dom_id, openMainField, new_post, onBlur);
        },

        /**
         * Used to fetch the content of the rich text instance.
         *
         * @returns {undefined}
         */
        getContent : function () {
            return rich_text_editor.getContent();
        },

        /**
         * Sets the content of the rich text instance.
         * @param {string} content The content to set.
         *
         * @returns {undefined}
         */
        setContent : function (content) {
            rich_text_editor.setContent(content);
        },

        /**
         * Hides the toolbar for the rich text instance.
         *
         * @returns {undefined}
         */
        blurToolbar : function () {
            rich_text_editor.blurToolbar();
        },

        /**
         * Pass a focus event to the rich text instance.
         *
         * @returns {undefined}
         */
        focus : function () {
            rich_text_editor.focus();
        },

        /**
         * Triggers a blur event for this instance. (Causes blur handlers to run. Such as validators.)
         *
         * @returns {undefined}
         */
        triggerBlur : function () {
            rich_text_editor.triggerBlur();
        },

        /*
         * Tests a fragment of html against a ruleset to ensure that no illegal tags are in it.
         *
         * Does not create a text area instance.
         *
         * @param {string} fragment A fragment of html to test.
         * @param {object} rules A set of Babbling Brook rules for checking html.
         *      See Stream.Fields[].valid_html for an example.
         *
         * @return {string} The fragment with any illegal html removed.
         */
        testHtmlFragment : function (fragment, rules) {
            return rich_text_editor.testHtmlFragment(fragment, rules);
        }
    };
};