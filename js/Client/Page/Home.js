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
 * @fileOverview Display a stream of posts.
 * @author Sky Wickenden
 */

/**
 * @namespace Displays a list of posts.
 * @package JS_Client
 */
BabblingBrook.Client.Page.Home = (function () {
    'use strict';
    return {

        construct : function () {
            var stream_url = BabblingBrook.Client.User.Config.home_page_stream;
            var stream_name = BabblingBrook.Library.makeStreamFromUrl(stream_url);
            BabblingBrook.Client.Page.Stream.Stream.construct(stream_name);
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
        BabblingBrook.Client.Page.Home.construct();
    });
});