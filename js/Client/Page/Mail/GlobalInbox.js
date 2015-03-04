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
 * @fileOverview Javascript used for the private messaging inbox page.
 * @author Sky Wickenden
 */

/**
 * This is singleton belongs to the BabblingBrook.Client.Page.Mail object and that must be included first or the namespace
 * will not exist.
 *
 *
 * @namespace Javascript used for the private messaging inbox page.
 * @package JS_Client
 */
BabblingBrook.Client.Page.Mail.GlobalInbox = (function () {
    'use strict';

    return {

        construct : function () {
            BabblingBrook.Client.Page.Mail.changeType('global_all');
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Mail.GlobalInbox.construct();
});