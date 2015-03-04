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

    BabblingBrook.Shared.Interact.setup(BabblingBrook.Scientia.Controller, 'scientia');
    BabblingBrook.Shared.Interact.postAMessage(
        {
            domain : window.location.host,
            protocol : window.location.protocol
        },
        'DomainReady',
        /**
         * An empty callback for when the domus domain acknowledges the ready statement.
         *
         * @return void
         */
        function () {},
        /**
          * Throw an error if one is reported.
          *
          * @return void
          */
        function () {
            console.error('The domus domain is not acknowledging an scientia DomainReady message');
            throw 'Thread execution stopped.';
        }
    );

    window.onerror = function(message, url, line) {
        BabblingBrook.Shared.Interact.postAMessage(
            {
                error : 'Uncaught error : ' + message + ' : url : ' + url + ' line : ' + line
            },
            'Error',
            function () {},
            function () {
                console.error(
                    'window.onerror in the scinetia ready function is erroring whilst wating for client to respond.'
                );
            }
        );
    };
});
