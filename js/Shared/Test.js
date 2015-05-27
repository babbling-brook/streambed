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
 * @fileOverview A Module for testing data definitions are correct.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

/**
 * @namespace A library for testing the validity of variables.
 * @package JS_Shared
 */
BabblingBrook.Test = (function () {
    'use strict';
    /**
     * @type {string[]} A list of valid types to test against.
     */
    var valid_types = [
        'string',
        // Only true for a pure object. Not true for arrays.
        'object',
        'array',
        // Does not parse strings.
        'number',
        'boolean',
        'null',
        'undefined',
         // An unsigned integer. Parses strings. To test for a number type use a duplicate test for number.
        'uint',
        // An integer which may be signed. Parses strings. To test for a number type use a duplicate test for number.
        'int',
        // Does not require a schema to be present.
        'url',
        'domain',
        'username',
        // In the form domain.username.
        'full-username',
        // A valid protocol object name. A stream or rhythm name object.
        'resource-name',
        // A valid user object.
        'user',
        // A valid protocol version number. Eg major/minor/patch, where any value can be an int or 'latest'.
        'version',
        // A valid protocol version object with major, monor and patch components.
        'version-object',
        // A valid stream or rhythm object.
        'resource-object'
    ];

    /**
     * Checks if a value is an integer.
     * @param {Number|String} value The value to test.
     * @return Boolean
     */
    var isInt = function (value) {
        return BabblingBrook.Library.isInt(value);
    };

    /**
     * Checks if a value is an unsigned integer.
     * @param {Number|String} value The value to test.
     * @return Boolean
     */
    var isUInt = function (value) {
        if (!isInt(value)) {
            return false;
        }

        if (parseInt(value, 10) < 0) {
            return false;
        }

        return true;
    };

    /**
     * Is a value a valid url.
     * @param {string} value The value to test.
     * @return Boolean
     * @fixme This is a placholder in case a more comprehensive test is needed.
     */
    var isUrl = function (value) {
        if (typeof value !== 'string') {
            return false;
        }
        return true;
    };

    /**
     * Is a value a valid domain name.
     * @param {string} value The value to test.
     * @return Boolean
     * @fixme This is a placholder in case a more comprehensive test is needed.
     */
    var isDomain = function (value) {
        if (typeof value !== 'string') {
            return false;
        }
        return true;
    };

    /**
     * Is a value a valid username (without the domain).
     * @param {string} value The value to test.
     * @return Boolean
     * @fixme This is a placholder in case a more comprehensive test is needed.
     */
    var isUsername = function (value) {
        if (typeof value !== 'string') {
            return false;
        }
        return true;
    };

    /**
     * Is a value a valid full username name.
     * @param {string} value The value to test.
     * @return Boolean
     */
    var isFullUsername = function (value) {
        if (typeof value !== 'string') {
            return false;
        }
        if (value.indexOf('/') === -1 && value.indexOf('@') === -1) {
            return false;
        }
        if (value.indexOf('/') > -1) {
            if (value.indexOf('/') !== value.lastIndexOf('/')) {
                return false;
            }
            var name_array = value.split('/');
            if (isDomain(name_array[0]) === false) {
                return false;
            }
            if (isUsername(name_array[1]) === false) {
                return false;
            }
        } else {
            if (value.indexOf('@') !== value.lastIndexOf('@')) {
                return false;
            }
            var name_array = value.split('@');
            if (isDomain(name_array[1]) === false) {
                return false;
            }
            if (isUsername(name_array[1]) === false) {
                return false;
            }
        }

        return true;
    };

    /**
     * Is a value a valid stream or rhythm name.
     *
     * @param {string} value The name object to test.
     *
     * @return Boolean
     * @fixme This is a stub. Needs to be more comprehensive.
     */
    var isResourceName = function (value) {
        if (typeof value !== 'string') {
            return false;
        }
        return true;
    };

    /**
     * Is a value a valid stream or rhythm name object.
     *
     * @param {object} value The name object to test.
     * @param {string} value.domain The domain of the resource.
     * @param {string} value.username The username of the resource.
     * @param {string} value.name The name of the resource.
     * @param {object} value.version The version of the resource.
     * @param {object} value.version.major The major version of the resource.
     * @param {object} value.version.minor The minor version of the resource.
     * @param {object} value.version.patch The patch version of the resource.
     *
     * @return Boolean
     */
    var isResourceObject = function (value) {
        if (typeof value !== 'object') {
            return false;
        }
        if (typeof value.domain !== 'string') {
            return false;
        } else {
            if (isDomain(value.domain) === false) {
                return false;
            };
        }
        if (typeof value.username !== 'string') {
            return false;
        } else {
            if (isUsername(value.username) === false) {
                return false;
            };
        }
        if (typeof value.name !== 'string') {
            return false;
        } else {
            if (isResourceName(value.name) === false) {
                return false;
            };
        }
        if (typeof value.version !== 'object') {
            return false;
        } else {
            if (isVersionObject(value.version) === false) {
                return false;
            };
        }
        return true;
    };

    /**
     * Is a value a valid user object name.
     *
     * @param {string} value The value to test.
     * @param {object} value.domain The domain of the resource.
     * @param {object} value.username The username of the resource.
     *
     * @return Boolean
     */
    var isUser = function (value) {
        if (typeof value !== 'object') {
            return false;
        }
        if (typeof value.domain !== 'string') {
            return false;
        } else {
            if (isDomain(value.domain) === false) {
                return false;
            };
        }
        if (typeof value.username !== 'string') {
            return false;
        } else {
            if (isUsername(value.username) === false) {
                return false;
            };
        }
        return true;
    };

    /**
     * Is a value a valid version number.
     * @param {string} value The value to test.
     * @return Boolean
     */
    var isVersion = function (value) {
        if (typeof value !== 'string') {
            return false;
        }
        var version_array = value.split('/');
        if (version_array.length !== 3) {
            return false;
        }
        var valid = true;
        jQuery.each(version_array, function (i, version) {
            // each element should be a digit or 'latest'
            if (!version.match(/(^[0-9]+$)|(^latest$)|(^all$)/)) {
                valid = false;
            }
        });
        return valid;
    };

    /**
     * Is a value a valid version object.
     *
     * @param {object} value The version object to test. to test.
     * @param {object} value.major The major version number.
     * @param {object} value.minor The minor version number.
     * @param {object} value.patch The patch version number.
     *
     * @return {boolean}
     */
    var isVersionObject = function (value) {
        if (typeof value !== 'object') {
            return false;
        }
        if (typeof value.major === 'undefined') {
            return false;
        }
        if (typeof value.minor === 'undefined') {
            return false;
        }
        if (typeof value.patch === 'undefined') {
            return false;
        }

        if (value.major !== 'latest' && value.major !== 'all' && isUInt(value.major) === false) {
            return false;
        }
        if (value.minor !== 'latest' && value.minor !== 'all' && isUInt(value.minor) === false) {
            return false;
        }
        if (value.patch !== 'latest' && value.patch !== 'all' && isUInt(value.patch) === false) {
            return false;
        }
        return true;
    };

    return {

        /**
         * Tests if variables match the types passed in. Throws a console error if they do not.
         * @param {array[]} vars An array of arrays, each containing two values. The first is the variable to test.
         *                The second is the type to test.
         *                If testing only a single vaiable then the outer array can be ommited
         *                (unless testing for an array).
         *                See valid_types for a list of available types.
         *                Multiple types can be tested, seperate each with a pipe ( | ).
         * @param {string} [error_message] The error message to report.
         *                                 The varaible that failed the test will be appended to the message.
         * @param {boolean} [report_error=true]
         * @return {boolean} Are the objects valid.
         */
        isA : function (vars, error_message, report_error) {

            if (typeof error_message !== 'string') {
                error_message = '';
            }

            if (typeof report_error !== 'boolean') {
                report_error = true;
            }

            if (typeof vars !== 'object' || BabblingBrook.Library.isArray(vars) === false) {
                BabblingBrook.TestErrors.reportError(error_message + ' Passed in vars are not an array.');
                return false;
            }

            // Enclose a single variable check in an extra array so it can be tested in the same ways as
            // multiple checks.
            // NB arrays must be pre-nested as this will not work for them.
            if (typeof vars[0] !== 'object' || !BabblingBrook.Library.isArray(vars[0])) {
                vars = [vars];
            }

            var error = '';
            jQuery.each(vars, function (i, v) {

                // Check that the request is valid.
                if (!BabblingBrook.Library.isArray(v)) {
                    error = ' Passed in var number : ' + i + ' , is not an array.';
                    return false;    // @escape the jQuery.each function.
                }
                if (v.length !== 2) {
                    error = ' Passed in var number : ' + i + ' , does not have the correct number of arguments.';
                    return false;    // @escape the jQuery.each function.
                }
                var tests = v[1].split('|');
                var error_count = 0;
                var combined_error = '';

                jQuery.each(tests, function (j, test) {

                    var nested_error = '';
                    if (jQuery.inArray(test, valid_types) === -1) {
                        error = ' Passed in type ( ' + test + ' ) is not a valid type.';
                        return false;    // @escape the jQuery.each function.
                    }
                    // @refactor This needs reworking to include a name for the index - they will need passing in.
                    var error_part = ' Passed in item with index ( ' + i + ' ), value ( ' + v[0] + ' )'
                        + ' and type ( ' + test + ' ), is not a valid ';

                    if (test === 'string' && typeof v[0] !== 'string') {
                        nested_error += error_part + 'string. ';
                    }

                    if (test === 'object' && typeof v[0] !== 'object') {
                        nested_error += error_part + 'object. ';
                    }

                    if (test === 'object' && BabblingBrook.Library.isArray(v[0])) {
                        nested_error += error_part + 'object. Is an array.';
                    }

                    if (test === 'array' && (typeof v[0] !== 'object' || !BabblingBrook.Library.isArray(v[0]))) {
                        nested_error += error_part + 'array. ';
                    }

                    if (test === 'number' && typeof v[0] !== 'number') {
                        nested_error += error_part + 'number. ';
                    }

                    if (test === 'boolean' && typeof v[0] !== 'boolean') {
                        nested_error += error_part + 'boolean. ';
                    }

                    if (test === 'null' && v[0] !== null) {
                        nested_error += error_part + 'null. ';
                    }

                    if (test !== 'null' && v[0] === null) {
                        nested_error += error_part + test;
                    }

                    if (test === 'undefined' && typeof v[0] !== 'undefined') {
                        nested_error += error_part + 'undefined. ';
                    }

                    if (test === 'uint' && !isUInt(v[0])) {
                        nested_error += error_part + 'unsigned integer (uint). ';
                    }
                    if (test === 'int' && !isInt(v[0])) {
                        nested_error += error_part + 'integer (int). ';
                    }

                    if (test === 'url' && !isUrl(v[0])) {
                        nested_error += error_part + 'url. ';
                    }

                    if (test === 'domain' && !isDomain(v[0])) {
                        nested_error += error_part + 'domain. ';
                    }

                    if (test === 'username' && !isUsername(v[0])) {
                        nested_error += error_part + 'username. ';
                    }

                    if (test === 'full-username' && !isFullUsername(v[0])) {
                        nested_error += error_part + 'full-username. ';
                    }

                    if (test === 'resource-name' && !isResourceName(v[0])) {
                        nested_error += error_part + 'resource-username. ';
                    }

                    if (test === 'resource-object' && !isResourceObject(v[0])) {
                        nested_error += error_part + 'resource-object. ';
                    }

                    if (test === 'user' && !isUser(v[0])) {
                        nested_error += error_part + 'user. ';
                    }

                    if (test === 'version' && !isVersion(v[0])) {
                        nested_error += error_part + 'version. ';
                    }

                    if (test === 'version-object' && !isVersionObject(v[0])) {
                        nested_error += error_part + 'version-object. ';
                    }

                    if (nested_error.length !== 0) {
                        error_count++;
                        combined_error += nested_error;
                    }

                    // check if all the tests failed.
                    if (error_count === tests.length && j + 1 === tests.length) {
                        error += combined_error;
                    }
                    return true; // continue the jQuery.each function.
                });

                return true; // continue the jQuery.each function.
            });
            if (error.length !== 0) {
                if (report_error === true) {
                    BabblingBrook.TestErrors.reportError(error_message + error, vars);
                }
                return false;
            }
            return true;

        },

        /**
         * Checks if a variable is within a certain range.
         * @param {Number|Array|Object|String} The variable to test.
         *                                     If a number then the numerical value is tested for the range.
         *                                     If an array or object then the size of the variable is tested.
         *                                     If a string then the number of characters is tested.
         * @param {number} start The starting range value.
         * @param {number} end The ending range value.
         * @param {string} error_message The error message to report.
         * @return {boolean} Is the variable withing the range.
         */
        inRange : function (variable, start, end, error_message) {

            var error = '';
            var size;
            if (typeof variable === 'number') {
                if (variable < start || variable > end) {
                    error = 'The variable ( ' + variable + ' )'
                        + ' is not within the range of ( ' + start + ', ' + end + ')';
                }

            } else if (typeof variable === 'object') {
                var type;
                if (BabblingBrook.Library.isArray(variable)) {
                    size = variable.length;
                    type = 'array';
                } else {
                    size = BabblingBrook.Library.objectSize(variable);
                    type = 'object';
                }
                if (size < start || size > end) {
                    error = 'An ' + type + ' size ( ' + size + ' )'
                        + ' is not within the range of ( ' + start + ', ' + end + ')';
                }

            } else if (typeof variable === 'string') {
                size = variable.length;
                if (size < start || size > end) {
                    error = 'An string size ( ' + size + ' )is not within the range of ( ' + start + ', ' + end + ')';
                }
            }

            if (error.length !== 0) {
                BabblingBrook.TestErrors.reportError(error_message + ' A BabblingBrook.Test.inRange Error. ' + error);
                return false;
            }
            return true;

        },

        /**
         * Check if all the provided values are defined or undefined.
         * @param {number[]} values Array of values to check.
         * @param {string} error_messsage An error to report if the match is false.
         * @return {boolean}
         */
        checkAllDefinedOrUndefined : function (values, error_message) {
            var status = 'defined';
            if (typeof values[0] === 'undefined') {
                status = 'undefined';
            }
            var match = true;
            jQuery.each(values, function (i, value) {
                var each_status = 'defined';
                if (typeof value === 'undefined') {
                    each_status = 'undefined';
                }
                if (status !== each_status) {
                    match = false;
                }
            });
            if (!match) {
                BabblingBrook.TestErrors.reportError(error_message);
            }
            return match;
        }

    };

}());