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
 * @fileOverview Include this on any page that has help icon popups.
 * @author Sky Wickenden
 */

/*
 * @namespace Shows help icons and popup help.
 * @package JS_Client
 *
 * Help icons should have the following format:
 *
 * <div id="help_<unique_name>" title="Title when hovering over the help icon" class="help-icon">
 *     <span id="help_title_<unique_name>" class="help-title hide">The help title</span>
 *     <span id="help_content_<unique_name>" class="help-content hide">The help content</span>
 * </div>
 *
 */
BabblingBrook.Client.Component.Help = (function () {
    'use strict';

    var loaded = false;


    var jq_help_dialog;

    return {

        /**
         * Creates a live ajax link for all help icons.
         */
        construct : function () {
            if (loaded === true) {
                return;
            }
            jq_help_dialog = jQuery('<div></div>').html('')
            jq_help_dialog.dialog({
                autoOpen: false,
                title: '',
                dragStart : function () {
                    jQuery('div').removeClass('top-dialogue');
                    jQuery(this).parent().addClass('top-dialogue');
                },
                focus : function () {
                    jQuery('div').removeClass('top-dialogue');
                    jQuery(this).parent().addClass('top-dialogue');
                }
            });
            //  Live used for ajax url
            jQuery(document).on('click', '.help-icon', function () {
                var help_id = this.id;

                help_id = help_id.substr(help_id.indexOf('_') + 1);
                jq_help_dialog
                    .html(jQuery('#help_content_' + help_id).html())
                    .dialog('option', 'title', jQuery('#help_title_' + help_id).html())
                    .dialog('option', 'height', 'auto')
                    .dialog('option', 'closeText', '')
                    .dialog('option', 'width', 450)
                    .dialog('open')
                    .dialog('moveToTop');
                // prevent the default action, e.g., following a link.
                return false;
            });

            loaded = true;
        }
    };
}());