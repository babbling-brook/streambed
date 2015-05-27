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
 */

if (typeof BabblingBrook.Client.Tests !== 'object') {
    BabblingBrook.Client.Tests = {};
}

/**
 * Index page for JavaScript tests.
 *
 * @package JS_Client
 */
BabblingBrook.Client.Tests.All = (function () {
    'use strict';

    return {
        
        construct : function() {
            jQuery('.open_blank_target').click(function(){
                window.open(jQuery(this).attr('href'), 'Javascript Tests');
                return false;
            });
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Tests.All.construct();
});