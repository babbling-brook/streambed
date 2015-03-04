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
 * @fileOverview Javascript used for the version dropdown selector on rhythm and stream edit/view pages.
 * @author Sky Wickenden
 */

/**
 * @namespace Javascript used for the version dropdown selector on rhythm and stream edit/view pages.
 * @package JS_Client
 */
BabblingBrook.Client.Component.EditVersionSelect = (function () {
    return {

        construct : function () {
            jQuery('#versions').change(function(){
                var version = jQuery('#versions>option:selected').val();
                if (version.length > 0) {
                    var new_url = '';
                    var url_parts = window.location.pathname.split('/');
                    var page_name = url_parts[7];
                    if (typeof page_name === 'undefined') {
                        page_name = 'view';
                    }
                    new_url = new_url + '/' + url_parts[1] + '/' + url_parts[2] + '/' + url_parts[3];
                    new_url = new_url + '/' + version + '/' + page_name;
                    BabblingBrook.Client.Core.Ajaxurl.redirect(new_url);
                }
            });
        }

    };

}());