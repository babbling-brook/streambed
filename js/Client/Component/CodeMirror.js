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
 * @fileOverview Used to create the CodeMirror editor for displaying the Rhythms.
 * @author Sky Wickenden
 */

/**
 * @namespace Used to create the CodeMirror editor for displaying the Rhythms.
 * @package JS_Client
 */
BabblingBrook.Client.Component.CodeMirror = (function () {
    'use strict';

    var j_code_mirror;

    return {

        create : function () {

            // If the code is shown straight away it sometimes errors when loaded through ajaxurl.
            // Not worked out what causes this.
            // As a stop gap the code is shown in a fake loading element and displayed after 1 second.
            setTimeout(function () {
                if (typeof CodeMirror.defineOption !== 'function') {
                    BabblingBrook.Client.Component.CodeMirror.construct();
                    return;
                }
                var read_only = false;
                var theme = 'default';
                var is_private = jQuery.trim(jQuery('#rhythm_status').text()) === "Private";
                var is_view_page = jQuery('#view_details').length > 0;
                var is_create_page = jQuery('#create_rhythm_form').val() === 'true';
                if ((is_private === false || is_view_page === true) && is_create_page === false) {
                    read_only = true;
                    theme = 'disabled';
                }
                var dom_editor;
                dom_editor = jQuery('#rhythm_javascript').get(0);

                CodeMirror.keyMap.tabSpace = {
                    Tab: function(cm) {
                        cm.replaceSelection("    ", "end", "+input");
                    },
                    fallthrough: ['default']
                };
                var config = {
                    value : "function myScript(){return 100;}\n",
                    lineNumbers : true,
                    matchBrackets : true,
                    tabSize : 4,
                    indentWithTabs : false,
                    readOnly : read_only,
                    theme : theme,
                    mode : 'javascript',
                    indentUnit : 4,
                    keyMap: 'tabSpace',
                    viewportMargin : Infinity
                }

                jQuery('#rhythm_javascript').removeClass('hidden').parent().removeClass('block-loading');
                j_code_mirror = CodeMirror.fromTextArea(dom_editor, config);


                /* This is necessary so that the real textbox is repopulated before ajaxurl serializes the form */
                jQuery('#save_rhythm').click(function() {
                    j_code_mirror.save();
                });

            }, 30);
        },

        /*
         * Exposed publicly so that selenium can edit it for testing.
         *
         * @return void
         */
        setValue : function(text) {
            j_code_mirror.setValue(text);
        },

        /*
         * Exposed publicly so that selenium can get it for testing.
         *
         * @return string
         */
        getValue : function() {
            return j_code_mirror.getValue();
        }

    };

}());