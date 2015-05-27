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



jQuery(function () {
    'use strict';

    if (typeof BabblingBrook.Backbone !== 'object') {
        BabblingBrook.Backbone = {};
    };

    /**
     * Extends Backbone model with extra Babbling Brook functionality.
     *
     * Adds a scenarios attribute that is used to define which rules should be used when validating a model.
     * Adds a rules attribute that is used to define validation rules.
     * Adds a default validation method that validates the rules.
     * If this is overridden then the new validate function
     * must call this one in order for the rules to be validated.
     *
     */
    BabblingBrook.Backbone.Model = Backbone.Model.extend({
        name : null, // Used in error reporting.
        valid_scenarios : [],
        scenario : null,
        // @todo document valid rules.
        rules : [],
        // errors are always returned as an object indexed by attribute.
        // Each attribute contains an array of error messages.
        validationError : {},
        /**
         * Default validation function tests the model data against the rules and scenario.
         * @returns {undefined}
         */
        validate : function() {
            if (typeof this.name !== 'string') {
                throw 'A Backbone modelmust have a valid name attribute.';
            }

            this.validationError = {};
            var error;
            var rules_length = this.rules.length;
            for (var i=0; i < rules_length; i++) {
                var rule = this.rules[i];
                if (typeof rule !== 'object') {
                    throw 'A Backbone model does not have a valid rule object: ' + this.name;
                }

                if (this.isRuleInScenario(rule) === false) {
                    continue;
                }

                var attributes_length = rule.attributes.length;
                for (var i=0; i < attributes_length; i++) {
                    error = this.validateRule(rule, rule.attributes[i]);
                    if (typeof error === 'string') {
                        if (typeof this.validationError[rule.attributes[i]] === 'undefined') {
                            this.validationError[rule.attributes[i]] = [];
                        }
                        this.validationError[rule.attributes[i]].push(error);
                    }
                }

            }
            if (_.isEmpty(this.validationError) === true) {
                return true;
            } else {
                return this.validationError;
            }
        },

        validateRule : function (rule, attribute) {
            var error;
console.debug(attribute);
console.debug(rule.type);
            switch (rule.type) {
                case 'required':
                    error = this.validateRequired(rule, attribute);
                    break;

                case 'string':
                    error = this.validateString(rule, attribute);
                    break;

                case 'object':
                    error = this.validateObject(rule, attribute);
                    break;

                case 'array':
                    error = this.validateArray(rule, attribute);
                    break;

                case 'boolean':
                    error = this.validateBoolean(rule, attribute);
                    break;

                case 'null':
                    error = this.validateNull(rule, attribute);
                    break;

                case 'undefined':
                    error = this.validateUndefined(rule, attribute);
                    break;
                    break;

                case 'number':  // includes max and min attribute.
                    error = this.validateNumber(rule, attribute);
                    break;

                case 'uint':    // includes max and min attribute.
                    error = this.validateUint(rule, attribute);
                    break;

                case 'int':     // includes max and min attribute.
                    error = this.validateInt(rule, attribute);
                    break;

                case 'url':
                    error = this.validateUrl(rule, attribute);
                    break;

                case 'user':
                    error = this.validateUser(rule, attribute);
                    break;

                case 'username':

                    break;

                case 'full_username':

                    break;

                case 'domain':

                    break;

                case 'version':

                    break;

                case 'version_latest':

                    break;

                case 'version_latest_all':

                    break;

                case 'stream_name':

                    break;

                case 'stream_latest_name':

                    break;

                case 'stream_latest_all_name':

                    break;

                case 'rhythm_name':

                    break;

                case 'rhythm_name_latest':

                    break;

                case 'in_array':    // Includes options attribute

                    break;

                case 'custom':

                    break;

                default:
                    throw 'The `' + this.name + '` Backbone model has defined an invalid rule type `' + rule.type + '`';

            }
            return error;
        },

        setScenario : function (scenario) {
            if (_.contains(this.valid_scenarios, scenario) === false) {
                throw 'Trying to set an invalid scenario (' + scenario + ') in a backbone mode: ' + this.name;
            }
            this.scenario = scenario;
        },

        isRuleInScenario : function (rule) {
            if (this.scenario === null) {
                return true;
            }

            if (_.contains(rule.scenarios, this.scenatio) === true) {
                return true;
            }
        },

        validateRequired : function (rule, attribute) {
            if (typeof this.get(attribute) === 'undefined') {
                return 'This is required.';
            }
        },

        validateString : function (rule, attribute) {
            if (typeof this.get(attribute) !== 'string') {
                return 'Needs to be text.';
            }
        },

        validateObject : function (rule, attribute) {
            if (typeof this.get(attribute) !== 'object') {
                return 'Needs to be an object.';
            }
        },

        validateArray : function (rule, attribute) {
            if (_.isArray(this.get(attribute)) === false) {
                return 'Needs to be an array.';
            }
        },

        validateBoolean : function (rule, attribute) {
            if (typeof this.get(attribute) !== 'boolean') {
                return 'Needs to be a boolean.';
            }
        },

        validateNull : function (rule, attribute) {
            if (_.isNull(this.get(attribute)) === false) {
                return 'Needs to be a null value.';
            }
        },

        validateUndefined : function (rule, attribute) {
            if (_.isUndefined(this.get(attribute)) === false) {
                return 'Needs to be an undefined value.';
            }
        },

        validateNumber : function (rule, attribute) {
            if (_.isNumber(this.get(attribute)) === false) {
                return 'Not a valid number.';
            }

            var result;
            if (typeof rule.min !== 'undefined') {
                result = this.validateMinimum(rule, attribute);
                if (typeof result !== 'undefined') {
                    return result;
                }
            }
            if (typeof rule.max !== 'undefined') {
                result = this.validateMaximum(rule, attribute);
                if (typeof result !== 'undefined') {
                    return result;
                }
            }
        },

        validateInt : function (rule, attribute) {
            var valid = BabblingBrook.Test.isA([this.get(attribute), 'int'], '', false);
            if (valid === false) {
                return 'Not a valid integer (whole number).';
            }

            var result;
            if (typeof rule.min !== 'undefined') {
                result = this.validateMinimum(rule, attribute);
                if (typeof result !== 'undefined') {
                    return result;
                }
            }
            if (typeof rule.max !== 'undefined') {
                result = this.validateMaximum(rule, attribute);
                if (typeof result !== 'undefined') {
                    return result;
                }
            }
        },

        validateUint : function (rule, attribute) {
            var valid = BabblingBrook.Test.isA([this.get(attribute), 'uint'], '', false);
            if (valid === false) {
                return 'Not a valid unsigned integer (positive whole number).';
            }

            var result;
            if (typeof rule.min !== 'undefined') {
                result = this.validateMinimum(rule, attribute);
                if (typeof result !== 'undefined') {
                    return result;
                }
            }
            if (typeof rule.max !== 'undefined') {
                result = this.validateMaximum(rule, attribute);
                if (typeof result !== 'undefined') {
                    return result;
                }
            }
        },

        validateMaximum : function (rule, attribute) {
            if (typeof rule.max !== 'number') {
                throw 'The max attribute of the `' + this.name + '` Backbone model is not a number (' + rule.max + ').';
            }

            if (this.get(attribute) > rule.max) {
                return 'Larger than the maximum allowed value (' + rule.max + ').';
            }
        },

        validateMinimum : function (rule, attribute) {
            if (typeof rule.min !== 'number') {
                throw 'The min attribute of the `' + this.name + '` Backbone model is not a number (' + rule.min + ').';
            }

            if (this.get(attribute) < rule.min) {
                return 'Smaller than the minimum allowed value (' + rule.min + ').';
            }
        },

        validateUrl : function (rule, attribute) {
            var valid = BabblingBrook.Test.isA([this.get(attribute), 'url'], '', false);
            if (valid === false) {
                return 'Not a valid url.';
            }
        },

        validateUser : function (rule, attribute) {
            var valid = BabblingBrook.Test.isA([this.get(attribute), 'user'], '', false);
            if (valid === false) {
                return 'Not a valid user.';
            }
        }
    });

});