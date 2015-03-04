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
 * @fileOverview Javascript used on the Rhythm update page.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.ManageRhythm !== 'object') {
    BabblingBrook.Client.Page.ManageRhythm = {};
}

/**
 * @namespace Used by the Rhythm update page to allow users to update their Rhythms.
 * @package JS_Client
 */
BabblingBrook.Client.Page.ManageRhythm.View = (function () {
    'use strict';

    return {

        construct : function () {
            BabblingBrook.Client.Component.CodeMirror.create();
        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageRhythm.View.construct();
});