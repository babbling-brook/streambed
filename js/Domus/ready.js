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
 * @fileOverview The document ready function for the domus domain.
 * @author Sky Wickenden
 */

jQuery(function () {
    'use strict';

    if (BabblingBrook.Domus.Controller.client_domain === '') {
        throw('client paramater is missing from the domus url');
    }
    BabblingBrook.Domus.Controller.client_domain = BabblingBrook.Library.getParameterByName('client');
    BabblingBrook.Domus.Controller.client_https = BabblingBrook.Library.getParameterByName('https');
    if (BabblingBrook.Domus.Controller.client_https === '') {
        throw('client https paramter is missing from the domus url');
    }

    // Set up the rhythm iframes
    BabblingBrook.Domus.Filter.construct();
    BabblingBrook.Domus.Kindred.construct();
    BabblingBrook.Domus.Suggestion.construct();
    BabblingBrook.Domus.Ring.setup();

    // Restore local feature usage from localstorage
    BabblingBrook.Domus.FeatureUsage.restore();
    BabblingBrook.Domus.Loaded.setClientLoaded();
    BabblingBrook.Domus.Loaded.setUserLoaded();
    BabblingBrook.Domus.Loaded.setSiteAccessLoaded();
    // Setup the Interact class to recieve cross domain messages.
    window.addEventListener('message', BabblingBrook.Domus.Interact.receiveMessage, false);
    BabblingBrook.Domus.Interact.postAMessage(
        {},
        BabblingBrook.Domus.Controller.client_domain,
        'DomainReady',
        function () {},
        function () {
            'Client domain not responding to ready message from domus.'
        },
        BabblingBrook.Domus.Controller.client_https
    );

    window.onerror = function(message, url, line) {
        BabblingBrook.Domus.Interact.postAMessage(
            {
                domain : 'domus',
                error : 'Uncaught error : ' + message + ' : url : ' + url + ' line : ' + line
            },
            BabblingBrook.Domus.Controller.client_domain,
            'Error',
            function () {},
            function () {
                console.error(
                    'window.onerror in the domus ready function is erroring whilst wating for client to respond.'
                );
            },
            BabblingBrook.Domus.Controller.client_https
        );
    };
});