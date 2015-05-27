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

var mocks = {
    simple_backbone_model : {
        /**
         * Defaults defined in order to be self documenting.
         */
        defaults : {
            an_int : null
        },
        name : 'test model',
        valid_scenarios : ['scenario_1', 'scenario_2'],
        rules: [],      // Appended for each test.
    },
    mock_attributes : {

    },
    mock_rules : {
        'required' : {
            type : 'required',
            attributes : [],        // Need to add these in the test.
        },
        'string' : {
            type : 'string',
            attributes : ['a_string'],
        },
        'object' : {
            type : 'object',
            attributes : ['an_object'],
        },
        'array' : {
            type : 'array',
            attributes : ['an_array'],
        },
        'boolean' : {
            type : 'boolean',
            attributes : ['a_boolean'],
        },
        'null' : {
            type : 'null',
            attributes : ['a_null'],
        },
        'undefined' : {
            type : 'undefined',
            attributes : ['an_undefined'],
        },
        'number' : {
            type : 'number',
            attributes : ['a_number'],
            max : 55.55,
            min : 44.44
        },
        'uint' : {
            type : 'uint',
            attributes : ['an_uint'],
            max : 90,
            min : 75
        },
        'int' : {
            type : 'int',
            attributes : ['an_int'],
            max : 25,
            min : -13
        },
        'url' : {
            type : 'url',
            attributes : ['an_url']
        },
        'user' : {
            type : 'user',
            attributes : ['a_user']
        },
        'username' : {
            type : 'username',
            attributes : ['a_username']
        },
        'full_username' : {
            type : 'username',
            attributes : ['a_username']
        },
        'domain' : {
            type : 'domain',
            attributes : ['a_domain']
        },
        'version' : {
            type : 'version',
            attributes : ['a_version']
        },
        'version_latest' : {
            type : 'version_latest',
            attributes : ['a_version_latest']
        },
        'version_latest_all' : {
            type : 'version_latest_all',
            attributes : ['a_version_latest_all']
        },
        'stream_name' : {
            type : 'stream_name',
            attributes : ['a_stream_name']
        },
        'stream_latest_name' : {
            type : 'stream_latest_name',
            attributes : ['a_stream_latest_name']
        },
        'stream_latest_all_name' : {
            type : 'stream_latest_all_name',
            attributes : ['a_stream_latest_all_name']
        },
        'rhythm_name' : {
            type : 'rhythm_name',
            attributes : ['a_rhythm_name']
        },
        'rhythm_name_latest' : {
            type : 'rhythm_name_latest',
            attributes : ['a_rhythm_name_latest']
        },
        'in_array' : {
            type : 'in_array',
            attributes : ['an_in_array']
        },
        'custom' : {
            type : 'custom',
            attributes : []    // Defined in the test.
        }
    }
};

describe("A suite to test the Backbone models extended model.", function() {

    it("Expects the simplest of models to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        model_mock.rules.push(mocks.mock_rules.string);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            a_string : 'a string'
        });
        var result = test_model_instance.validate();

        expect(result).toBe(true);
    });

    it("Expects required rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var required_rules_mock = jQuery.extend({}, mocks.mock_rules.required);
        required_rules_mock.attributes = ['test_string'];
        model_mock.rules.push(required_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            test_string : 'test string'
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the required attribute is missing.
        var test_model_instance_2 = new test_model({
            another_string : 'another string'
        });
        var result2 = test_model_instance_2.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'test_string')).toEqual(true);
        expect(_.isArray(result2.test_string)).toEqual(true);
        expect(_.isString(result2.test_string[0])).toEqual(true);
    });

    it("Expects string rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var string_rules_mock = jQuery.extend({}, mocks.mock_rules.string);
        model_mock.rules.push(string_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            a_string : 'a string'
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the string is not a string.
        var test_model_instance = new test_model({
            a_string : 42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'a_string')).toEqual(true);
        expect(_.isArray(result2.a_string)).toEqual(true);
        expect(_.isString(result2.a_string[0])).toEqual(true);
    });

    it("Expects object rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var object_rules_mock = jQuery.extend({}, mocks.mock_rules.object);
        model_mock.rules.push(object_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            an_object : {'a_key' : 'content'}
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            an_object : 42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'an_object')).toEqual(true);
        expect(_.isArray(result2.an_object)).toEqual(true);
        expect(_.isString(result2.an_object[0])).toEqual(true);
    });

    it("Expects array rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var array_rules_mock = jQuery.extend({}, mocks.mock_rules.array);
        model_mock.rules.push(array_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            an_array : [1,2,3]
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            an_array : 42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'an_array')).toEqual(true);
        expect(_.isArray(result2.an_array)).toEqual(true);
        expect(_.isString(result2.an_array[0])).toEqual(true);
    });

    it("Expects boolean rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var boolean_rules_mock = jQuery.extend({}, mocks.mock_rules.boolean);
        model_mock.rules.push(boolean_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            a_boolean : false
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            a_boolean : 42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'a_boolean')).toEqual(true);
        expect(_.isArray(result2.a_boolean)).toEqual(true);
        expect(_.isString(result2.a_boolean[0])).toEqual(true);
    });

    it("Expects null rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var null_rules_mock = jQuery.extend({}, mocks.mock_rules.null);
        model_mock.rules.push(null_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            a_null : null
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            a_null : 42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'a_null')).toEqual(true);
        expect(_.isArray(result2.a_null)).toEqual(true);
        expect(_.isString(result2.a_null[0])).toEqual(true);
    });

    it("Expects undefined rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var undefined_rules_mock = jQuery.extend({}, mocks.mock_rules.undefined);
        model_mock.rules.push(undefined_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            an_undefined : undefined
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            an_undefined : 42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'an_undefined')).toEqual(true);
        expect(_.isArray(result2.an_undefined)).toEqual(true);
        expect(_.isString(result2.an_undefined[0])).toEqual(true);
    });

    it("Expects number rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var number_rules_mock = jQuery.extend({}, mocks.mock_rules.number);
        model_mock.rules.push(number_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            a_number : 50.21
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            a_number : 'not a number'
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'a_number')).toEqual(true);
        expect(_.isArray(result2.a_number)).toEqual(true);
        expect(_.isString(result2.a_number[0])).toEqual(true);

        // A number that is too large.
        var test_model_instance = new test_model({
            a_number : 60.4
        });
        var result3 = test_model_instance.validate();
        expect(_.isObject(result3)).toEqual(true);
        expect(_.has(result3, 'a_number')).toEqual(true);
        expect(_.isArray(result3.a_number)).toEqual(true);
        expect(_.isString(result3.a_number[0])).toEqual(true);

        // A number that is too small.
        var test_model_instance = new test_model({
            a_number : 20.1
        });
        var result4 = test_model_instance.validate();
        expect(_.isObject(result4)).toEqual(true);
        expect(_.has(result4, 'a_number')).toEqual(true);
        expect(_.isArray(result4.a_number)).toEqual(true);
        expect(_.isString(result4.a_number[0])).toEqual(true);
    });

    it("Expects int rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var int_rules_mock = jQuery.extend({}, mocks.mock_rules.int);
        model_mock.rules.push(int_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            an_int : -2
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            an_int : 42.42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'an_int')).toEqual(true);
        expect(_.isArray(result2.an_int)).toEqual(true);
        expect(_.isString(result2.an_int[0])).toEqual(true);

        // An int that is too large.
        var test_model_instance = new test_model({
            an_int : 142
        });
        var result3 = test_model_instance.validate();
        expect(_.isObject(result3)).toEqual(true);
        expect(_.has(result3, 'an_int')).toEqual(true);
        expect(_.isArray(result3.an_int)).toEqual(true);
        expect(_.isString(result3.an_int[0])).toEqual(true);

        // An int that is too small.
        var test_model_instance = new test_model({
            an_int : -42
        });
        var result4 = test_model_instance.validate();
        expect(_.isObject(result4)).toEqual(true);
        expect(_.has(result4, 'an_int')).toEqual(true);
        expect(_.isArray(result4.an_int)).toEqual(true);
        expect(_.isString(result4.an_int[0])).toEqual(true);
    });

    it("Expects uint rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var uint_rules_mock = jQuery.extend({}, mocks.mock_rules.uint);
        model_mock.rules.push(uint_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            an_uint : 80
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the uint is not a uint.
        var test_model_instance = new test_model({
            an_uint : -2
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'an_uint')).toEqual(true);
        expect(_.isArray(result2.an_uint)).toEqual(true);
        expect(_.isString(result2.an_uint[0])).toEqual(true);

        // An uint that is too large.
        var test_model_instance = new test_model({
            an_uint : 142
        });
        var result3 = test_model_instance.validate();
        expect(_.isObject(result3)).toEqual(true);
        expect(_.has(result3, 'an_uint')).toEqual(true);
        expect(_.isArray(result3.an_uint)).toEqual(true);
        expect(_.isString(result3.an_uint[0])).toEqual(true);

        // An uint that is too small.
        var test_model_instance = new test_model({
            an_uint : 10
        });
        var result4 = test_model_instance.validate();
        expect(_.isObject(result4)).toEqual(true);
        expect(_.has(result4, 'an_uint')).toEqual(true);
        expect(_.isArray(result4.an_uint)).toEqual(true);
        expect(_.isString(result4.an_uint[0])).toEqual(true);
    });

    it("Expects url rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var url_rules_mock = jQuery.extend({}, mocks.mock_rules.url);
        model_mock.rules.push(url_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            an_url : 'http://example.com'
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            an_url : 42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'an_url')).toEqual(true);
        expect(_.isArray(result2.an_url)).toEqual(true);
        expect(_.isString(result2.an_url[0])).toEqual(true);
    });

    // This needs testing in the test.IsA module
    it("Expects strings that are not urls to fail validation for the url type.");

    it("Expects user rules to validate.", function() {
        var model_mock = jQuery.extend(true, {}, mocks.simple_backbone_model);
        var user_rules_mock = jQuery.extend({}, mocks.mock_rules.user);
        model_mock.rules.push(user_rules_mock);
        var test_model = BabblingBrook.Backbone.Model.extend(model_mock);
        var test_model_instance = new test_model({
            a_user : {
                domain : 'example.com',
                username : 'username'
            }
        });
        var result = test_model_instance.validate();
        expect(result).toBe(true);

        // Check if the object is not an object.
        var test_model_instance = new test_model({
            a_user : 42
        });
        var result2 = test_model_instance.validate();
        expect(_.isObject(result2)).toEqual(true);
        expect(_.has(result2, 'a_user')).toEqual(true);
        expect(_.isArray(result2.a_user)).toEqual(true);
        expect(_.isString(result2.a_user[0])).toEqual(true);
    });

    // These need testing in the test.IsA module
    it("Expects domains in users to match the protocol definition of a username.");
    it("Expects usernames in users to match the protocol definition of a username.");


    it("Expects username rules to validate.");
    it("Expects domain rules to validate.");
    it("Expects version rules to validate.");
    it("Expects version_latest rules to validate.");
    it("Expects version_latest_all rules to validate.");
    it("Expects stream_name rules to validate.");
    it("Expects stream_latest_name rules to validate.");
    it("Expects stream_latest_all_name rules to validate.");
    it("Expects rhythm_name rules to validate.");
    it("Expects rhythm_name_latest rules to validate.");
    it("Expects in_array rules to validate.");
    it("Expects custom rules to validate.");

    it("Expects multiple attributes to validate for a single rule");

    it("Expects errors to be returned in an object indexed by attribute, with an array of errors for each attribute");

    it("Expects the scenario to be settable", function() {
        var test_model = BabblingBrook.Backbone.Model.extend(mocks.simple_backbone_model);
        var test_model_instance = new test_model({
            an_int : '123'
        });
        expect(test_model_instance.scenario).toBe(null);

        test_model_instance.setScenario('scenario_1');
        expect(test_model_instance.scenario).toBe('scenario_1');

        test_model_instance.setScenario('scenario_2');
        expect(test_model_instance.scenario).toBe('scenario_2');
    });

    it("Expects the rules to change when the scenario is changed.");
});