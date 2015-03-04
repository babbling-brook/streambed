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
 * @fileOverview Javascript used for managing the testing cookie - that enables test stream rhythms etc to be fetched
 * and displayed.
 *
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Tests !== 'object') {
    BabblingBrook.Client.Page.Tests = {};
}

/**
 * Javascript used for managing the testing cookie - that enables test stream rhythms etc to be fetched
 * and displayed.
 *
 * @package JS_Client
 */
BabblingBrook.Client.Page.Tests.SetupTestCookie = (function () {
    'use strict';

    var testing;

    var loadTestCookie = function () {
        var test_cookie_data = BabblingBrook.Library.getCookie('testing');
        if (typeof test_cookie_data === null) {
            BabblingBrook.Library.setCookie('testing', 'false');
            testing = false;
        } else if (test_cookie_data === 'true') {
            testing = true;
        } else {
            testing = false;
        }
    };

    var onSwitchCookieStateClicked = function () {
        testing === true ? testing = false : testing = true;
        BabblingBrook.Library.setCookie('testing', testing.toString());
        jQuery('#test_cookie_status').text(testing);
        return false;
    }

    return {
        construct : function () {
            loadTestCookie();
            jQuery('#test_cookie_status').text(testing);

            jQuery('#switch_cookie_state').click(onSwitchCookieStateClicked);
        }
    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Tests.SetupTestCookie.construct();
});