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
 * @fileOverview A collection of tests for validating data that is returning from the domus domain.
 */

/**
 * Client controller for recieving requests from the domus domain.
 */

/**
 * @namespace Receives action requests from from the domus domain.
 * All actions receive a data item that needs validating and a domus_guid.
 * They must return a boolean that indicates success of the action.
 * Any errors should be set in BabblingBrook.TestErrors, where they will be harvested by the interact class.
 */
BabblingBrook.Client.Core.Controller = (function () {
    'use strict';

    return {
        /**
         * Defines the type of page that is being used to display posts.
         *        posts : Page with multiple root level summaries of posts
         *        post : An post detail page with all its sub posts
         */
        page_type : null,

        actionError : function (request_data) {
            var test1 = BabblingBrook.Test.isA([
                [request_data.domain, 'string'],
                [request_data.error, 'string']
            ], 'Error report from the domus domain is incorrectly formatted.');
            if (test1 === false) {
                return;
            }

            BabblingBrook.Client.Component.ReportBug.appendSubDomainError({
                domain : request_data.domain,
                error : request_data.error
            });
        }

    };
}());
