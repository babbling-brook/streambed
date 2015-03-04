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
 * @fileOverview Integrates ckeditor with Babbling Brook.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Component.RichTextAdapters !== 'object') {
    BabblingBrook.Client.Component.RichTextAdapters = {};
}

/**
 * Integrates ckeditor with Babbling Brook.
 *
 * @param {string} textarea_id The id of the textarea that is being replaced with a CKEditor instance.
 */
BabblingBrook.Client.Component.RichTextAdapters.CKEditorAdapter = function (textarea_id) {
    'use strict';

    var ckeditor_valid_html = '';
    var ckeditor_items = [];
    var toolbar;
    var ckeditor_instance;
    /**
     * @type {string} An id for the container in the dom that holds this ckeditor instance.
     */
    var container_id;

    /**
     * Generates the values for ckeditor valid html and the toolbar from the Babbling Brook formated data.
     *
     * @param {type} rules The Babbling Brook rules set that needs converting to CKEditor format.
     * @param {boolean} create_toolbar Should a toolbar instance be populated.
     * @returns {undefined}
     */
    var convertBabblingBrookRulesToCkeditor = function(rules, create_toolbar) {
        var ckeditor_styles = '';

        var ol_found = false;
        var ul_found = false;
        var li_found = false;
        jQuery.each(rules.elements, function (element_name, attributes) {

            var generateValidAttributeCode = function (attributes) {
                var attributes_code = '[';
                jQuery.each(attributes, function (i, attribute) {
                    if (attribute.required === true) {
                        attributes_code += '!';
                    }
                    attributes_code += attribute.attribute + ',';
                });
                attributes_code += ']';
                if (attributes_code === '[]') {
                    attributes_code = '';
                }
                return attributes_code;
            };

            var attributes_code = generateValidAttributeCode(attributes);
            ckeditor_valid_html += element_name + attributes_code + '; ';

            if (create_toolbar === true) {
                switch (element_name) {

                    case 'strong':
                        ckeditor_items.push('Bold');
                        break;

                    case 'em':
                            // @todo case 'i'
                        ckeditor_items.push('Italic');
                        break;

                    case 'a':
                        ckeditor_items.push('LinkSimple');
                        ckeditor_items.push('UnlinkSimple');
                        break

                    case 's':
                        ckeditor_items.push('Strike');
                        break;

                    case 'blockquote':
                    //    ckeditor_items.push('Blockquote');
                        break;

                    case 'li':
                        li_found = true;
                        break;

                    case 'ol':
                        ol_found = true;
                        break;

                    case 'ul':
                        ul_found = true;
                        break;
                }
            }
        });

        if (create_toolbar === true) {
            if (ul_found === true && li_found === true) {
                ckeditor_items.push('BulletedList');
            }
            if (ol_found === true && li_found === true) {
                ckeditor_items.push('NumberedList');
            }
        }

        // Apply valid styles to all elements.
        jQuery.each(rules.styles, function (i, style) {
            ckeditor_styles += style + ',';
        });

        ckeditor_valid_html + '*{' + ckeditor_styles + '}';

        if (create_toolbar === true) {
            toolbar = [
            {
                name : 'styles',
                items : ckeditor_items
            }
            ];


            if (BabblingBrook.Library.getCookie('testing') === 'true') {
                toolbar[0].items.push('Source');
            }
        }
    };

    /**
     * Creates the CKEditor instance.
     *
     * @returns {undefined}
     */
    var createCKEditorInstance = function() {
        ckeditor_instance = CKEDITOR.replace(textarea_id, {
            // Format for allowedContent: element[attributes]{styles}(classes);
            allowedContent : ckeditor_valid_html,
            toolbar : toolbar,
            height: 24,
            disableNativeSpellChecker : false,
            plugins : 'autogrow,autolink,basicstyles,dialog,,linksimple,list,wysiwygarea,button,toolbar',
            //extraPlugins : 'linksimple,autogrow,autolink',
            removePlugins : 'elementspath,liststyle,tabletools,contextmenu,resize,link',
            autoGrow_minHeight : 27,     // this is the min height that the autoGrow plugin allows without flicker.
            autoGrow_onStartup : true,
            autoGrow_bottomSpace : 27,
            resize_enabled : false,
            skin : 'cobalt',

            // Temporary whilst developing plugin.  see docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
            // allowedContent : true,
        });
    }

    return {

        /**
         * Generate a CKeditor instance for a StreamTextField.
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
            container_id = post_dom_id;
            convertBabblingBrookRulesToCkeditor(stream.fields[row_id].valid_html, true);
            createCKEditorInstance();

            if (row_id === 1) {
                ckeditor_instance.on('focus', function (event) {
                    if (jQuery(container_id + ' .input-post-detail>div').hasClass('minimised') === true) {
                        openMainField();
                        if (new_post === true) {
                            event.editor.setData('<p></p>', function () {
                                event.editor.focus();
                            });
                        }
                    }
                });
            }

            ckeditor_instance.on('blur', onBlur);
        },

        /**
         * Used to fetch the content of the CKeditor instance.
         *
         * @returns {undefined}
         */
        getContent : function () {
            return ckeditor_instance.getData();
        },

        /**
         * Sets the content of the CKEditor instance.
         * @param {string} content The content to set.
         *
         * @returns {undefined}
         */
        setContent : function (content) {
            ckeditor_instance.setData(content);
        },

        /**
         * Hides the toolbar for the CKEditor instance.
         *
         * @returns {undefined}
         */
        blurToolbar : function () {
            jQuery(container_id + ' .cke_chrome').removeClass('cke_focus');
        },

        /**
         * Pass a focus event to the ckeditor instance.
         *
         * @returns {undefined}
         */
        focus : function () {
            ckeditor_instance.focus();
        },

        /**
         * Triggers a blur event for this instance. (Causes blur handlers to run. Such as validators.)
         *
         * @returns {undefined}
         */
        triggerBlur : function () {
            ckeditor_instance.focusManager.blur(true);
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
            convertBabblingBrookRulesToCkeditor(rules, true);
            var ck_filter = new CKEDITOR.filter(ckeditor_valid_html);
            var ck_fragment = CKEDITOR.htmlParser.fragment.fromHtml(fragment);
            var ck_writer = new CKEDITOR.htmlParser.basicWriter();
            ck_filter.applyTo(ck_fragment, false, false, 1);
            ck_fragment.writeHtml(ck_writer);
            var result = ck_writer.getHtml();

            // Special case for p tags as CKEditor always includes it by default.
            // No way to have absolutly no element tags.
            if (typeof rules.elements.p === 'undefined') {
                ck_fragment = CKEDITOR.htmlParser.fragment.fromHtml(result);
                ck_filter.applyTo(ck_fragment, false, false, 2);    // use br instead of p as line break.
                ck_writer = new CKEDITOR.htmlParser.basicWriter();
                ck_fragment.writeHtml(ck_writer);
                result = ck_writer.getHtml();
            }

            return result;
        }

    };
};