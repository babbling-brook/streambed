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
 * @fileOverview Converts data from the quick and easy row bu row format provided by the DB into the
 * nested tree data of Babbling Brook.
 * @author Sky Wickenden
 */
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}
if (typeof BabblingBrook.Scientia !== 'object') {
    BabblingBrook.Scientia = {};
}

/**
 * @namespace Converts data from the quick and easy row bu row format provided by the DB into the
 * nested tree data of Babbling Brook.
 * @package JS_Scientia
 */
BabblingBrook.Scientia.DataConversion = (function () {
    'use strict';

    var getVersionObjectFromPartsAndType = function(version_type, major, minor, patch) {
        var version_parts = version_type.split('/');
        if (version_parts[0] === 'latest') {
            major = 'latets';
        }
        if (version_parts[1] === 'latest') {
            minor = 'latets';
        }
        if (version_parts[2] === 'latest') {
            patch = 'latets';
        }
        var version = {
            major : major,
            minor : minor,
            patch : patch
        };
        return
    }

    return {

        convertStreamFilterSubscriptions : function(filters) {
            var length = filters.length;
            var sorted_filters = {};
            for (var i = 0; i < length; i++) {
                var filter = filters[i];
                var version = getVersionObjectFromPartsAndType(
                    filter.version_type,
                    filter.major,
                    filter.minor,
                    filter.patch
                );
                var row = {
                    subscription_id : filter.subscription_id,
                    locked : filter.locked,
                    description : description,
                    rhythm : {
                        domain : filter.domain,
                        username : filter.username,
                        name : filter.name,
                        version : version
                    },
                };
                sorted_filters[filter.display_order] = filter.display_order;
            }
            return sorted_filters;
        }

    };
}());