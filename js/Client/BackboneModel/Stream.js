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
 * @fileOverview Shows the status flag on the tree display when a bug report is the root post.
 * @author Sky Wickenden
 */

/**
 * Prototype object for streams.
 */
'use strict';
BabblingBrook.BackboneModel.Stream = BabblingBrook.Backbone.Model.extend({  // @todo extend backbone model to include scenarios / rules and default validate that follows the rules.
//    initialize: function(){
//        var valid_modes = ['name', 'full'];
//        if (valid_modes.indexOf(this.get('mode')) === -1) {
//            throw 'Must initiate a stream model with a valid mode of: ' + valid_modes.toString();
//        }
//    },
    /**
     * Defaults defined in order to be self documenting.
     */
    defaults : {
        owner : null,
        name : null,
        version : null,
        fields : null,
        kind : null,
        description : null,
        child_streams : null,
        parent_streams : null,
        create_date : null,
        sort_rhythms : null,
        moderation_rings : null,
        submition_permission : null
    },
    scenarios : ['name', 'full'],
    rules: [
        {
            type : 'required',
            attributes : ['owner', 'name', 'version'],
        },
        {
            type : 'required',
            attributes : [
                'fields',
                'kind',
                'description',
                'child_streams',
                'parent_streams',
                'create_date',
                'sort_rhythms',
                'moderation_rings',
                'submition_permission',
            ],
            scenario : 'full'
        },
        {
            type : 'user',
            attributes : 'owner'
        },
        {
            // A stream name can have latest version numbers a full definition can't.
            type : 'version_latest',
            attributes : 'version'
        },
        {
            type : 'version',
            attributes : 'version',
            scenario : 'full'
        },
        {
            type : 'custom',
            rule_name : 'ruleFields',
            attributes : ['fields'],
            scenario : 'full'
        },
        {
            type : 'inArray',
            attributes : ['kind'],
            options : ['standard', 'user'],
            scenario : 'full'
        },
        {
            type : 'string',
            attributes : ['description'],
            scenario : 'full'
        },
        {
            type : 'custom',
            rule_name : 'ruleChildStreams',
            attributes : ['child_streams'],
            scenario : 'full'
        },
        {
            type : 'custom',
            rule_name : 'ruleParentStreams',
            attributes : ['parent_streams'],
            scenario : 'full'
        },
        {
            type : 'uint',
            attributes : ['create_date'],
            scenario : 'full'
        },
        {
            type : 'custom',
            rule_name : 'ruleSortRhythns',
            attributes : ['sort_rhythms'],
            scenario : 'full'
        },
        {
            type : 'custom',
            rule_name : 'ruleModerationRings',
            attributes : ['moderation_rings'],
            scenario : 'full'
        },
        {
            type : 'inArray',
            attributes : ['submition_permission'],
            options : ['anyone', 'owner'],
            scenario : 'full'
        }
    ],
});


