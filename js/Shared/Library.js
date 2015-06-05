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
 * @fileOverview A library of generic helper functions.
 * @author Sky Wickenden
 */

// Create the BabblingBrook namespace
if (typeof BabblingBrook !== 'object') {
    BabblingBrook = {};
}

/**
 * @namespace Singleton class containing library functions for use by a BabblingBrookSite
 * @package JS_Shared
 */
BabblingBrook.Library = (function () {
    'use strict';
    return {

        /**
         * Creates an interface to ensure that a class contains the passed in methods.
         *
         * Throws an error if the required methods are not in the class.
         *
         * @param {array} methods the methods to check exist in the class.
         * @param {object} cls An object that is being checked for methods.
         *
         * @returns {void} Always return
         */
        interface : function (methods, cls, class_name) {
            var methods_length =  methods.length;
            for(var i=0; i<methods_length; i++) {
                if (typeof cls[methods[i]] !== 'function') {
                    throw 'Class "' + class_name + '" does not contain a method "' + methods[i] + '"';
                }
            }
        },

        /**
         * Genreates a hash code based on the passed in string.
         *
         * @param {string} str The string to generate a hash for.
         *
         * @returns {string}
         */
        generateHashCode : function(str) {
            var hash = 0;
            var i;
            var chr;
            var len;
            if (str.length === 0) {
                return hash;
            }
            for (i = 0, len = str.length; i < len; i++) {
                chr   = str.charCodeAt(i);
                hash  = ((hash << 5) - hash) + chr;
                hash |= 0; // Convert to 32bit integer
            }
            return 'id' + hash.toString();
        },

        /**
         * Checks if two objects are deeply equivilant to each other.
         * @param {object) obj_1 The first object to compare.
         * @param {object) obj_2 The second object to compare.
         * @return boolean
         */
        deepEquals : function (obj_1, obj_2) {

            if(typeof(obj_1) !== 'object' || typeof(obj_2) !== 'object') {
                if(obj_1 === obj_2) {
                    return true;
                } else {
                    return false;
                }
            }

            var property;
            for (property in obj_1) {
                if (typeof (obj_1[property]) !== 'undefined' && typeof (obj_2[property]) === 'undefined') {
                    return false;
                }
            }

            for (property in obj_2) {
                if (typeof (obj_2[property]) !== 'undefined' && typeof (obj_1[property]) === 'undefined') {
                    return false;
                }
            }

            for (property in obj_1) {
                if (obj_1.hasOwnProperty(property)) {
                    switch (typeof (obj_1[property])) {
                        case 'object':
                            if (BabblingBrook.Library.deepEquals(obj_1[property], obj_2[property]) === false) {
                                return false;
                            }
                            break;

                        case 'function':
                            if (typeof(obj_2[property]) === 'undefined'
                                || (property !== 'equals' && obj_1[property].toString() !== obj_2[property].toString())
                            ) {
                                return false;
                            }
                            break;

                        default:
                            if (obj_1[property] !== obj_2[property]) {
                                return false;
                            }
                            break;
                    }
                }
            }

            return true;
        },

        /**
         * Checks if the children of a DOM element have overflown its boundaries.
         *
         * @param jq_element A jQuery object representing the parent element to check.
         *
         * @return boolean
         */
        hasOverflow : function(jq_element) {
            var jq_children = jq_element.find('*').filter(':visible');
            var qty = jq_children.length;

            if (qty > 0) {
                var max_width = 0;
                var max_height = 0;
                jq_children.map(function(){
                    var jq_child = jQuery(this);
                    max_width = Math.max(max_width, jq_child.outerWidth(true));
                    max_height = Math.max(max_height, jq_child.outerHeight(true));
                });
                return max_width > jq_element.width() || max_height > jq_element.height();
            }

            return false;
        },

        /**
         * Add a changed class to a field element. Also removed any other state classes.
         *
         * @param object A jQuery element to add the class to.
         *
         * @return void
         */
        fieldEditing : function(jq_element) {
            jq_element.removeClass('error success working').addClass('editing');
        },

        /**
         * Add a changed class to a field element. Also removed any other state classes.
         *
         * @param object A jQuery element to add the class to.
         *
         * @return void
         */
        fieldError : function(jq_element) {
            jq_element.removeClass('editing success working').addClass('error');
        },

        /**
         * Add a changed class to a field element. Also removed any other state classes.
         *
         * @param object A jQuery element to add the class to.
         *
         * @return void
         */
        fieldSuccess : function(jq_element) {
            jq_element.removeClass('editing error working').addClass('success');
        },

        /**
         * Add a changed class to a field element. Also removed any other state classes.
         *
         * @param object A jQuery element to add the class to.
         *
         * @return void
         */
        fieldWorking : function(jq_element) {
            jq_element.removeClass('editing success error').addClass('working');
        },

        /**
         * A correct boolean to string conversion.
         *
         * @param string value The string to convert to a boolean.
         *
         * @return boolean
         */
        stringToBoolean: function(value){
            switch(value.toLowerCase()){
                case 'true': case 'yes': case '1':
                    return true;
                case 'false': case 'no': case '0': case null:
                    return false;
                default:
                    return Boolean(value);
            }
        },

        /**
         * Changes the action of an url for streams and Rhythms.
         *
         * Also removes the protocol from the url if it is present.
         *
         * @param {string} url The base url that is used with the new action.
         * @param {string} action The new action.
         *
         * @return {string} The new url.
         *
         * The current format is domain/username/featurename/objectname/major/minor/patch/action
         * The new format will be:
         * domain/username[~domain]/action/featurename/objectname/major|latest/minor|latest/patch|latest/action/extra
         */
        changeUrlAction : function (url, action) {
            BabblingBrook.Test.isA([url, 'url'], 'ChangeUrlAction failed.');

            url = BabblingBrook.Library.removeProtocol(url);
            var url_parts = url.split('/');
            var new_url = url_parts[0] + '/' + url_parts[1] + '/' + url_parts[2] + '/' + url_parts[3];
            if (typeof url_parts[4] !== 'undefined') {
                new_url += '/' + url_parts[4];
            } else {
                new_url += '/latest';
            }
            if (typeof url_parts[5] !== 'undefined') {
                new_url += '/' + url_parts[5];
            } else {
                new_url += '/latest';
            }
            if (typeof url_parts[6] !== 'undefined') {
                new_url += '/' + url_parts[6];
            } else {
                new_url += '/latest';
            }
            new_url += '/' + action;
            return new_url;
        },

        /**
         * Extract a domain from an url.
         *
         * @param {string} url The url to extrac the domain from.
         * @param {boolean} [use_username_domain=false] Should the domain be taken from the username if it is present.
         * @return {string}
         */
        extractDomain : function (url, use_username_domain) {
            BabblingBrook.Test.isA([url, 'url'], 'extractDomain failed.');
            url = BabblingBrook.Library.removeProtocol(url);
            var url_parts = url.split('/');
            if (typeof url_parts[0] !== 'string' || url_parts[0].length === 0) {
                BabblingBrook.TestErrors.reportError('Domain is invalid');
            }

            var domain;
            if (typeof use_username_domain !== 'undefined' && use_username_domain === true) {
                var user_domain = BabblingBrook.Library.extractDomainFromFullUsername(url_parts[1], false);
                if (typeof user_domain === 'string') {
                    domain = user_domain;
                }
            }

            if (typeof domain === 'undefined') {
                BabblingBrook.Test.isA([url_parts[0], 'domain'], 'extractDomain failed. Domain is not a valid domain.');
                domain = url_parts[0];
            }

            return domain;
        },

        /**
         * removes the schema and domain from an url.
         */
        extractPath : function (url) {
            BabblingBrook.Test.isA([url, 'url'], 'extractPath failed.');
            url = BabblingBrook.Library.removeProtocol(url);
            var domain = BabblingBrook.Library.extractDomain(url);
            var reg = new RegExp('^' + domain, 'g');
            var path = url.replace(reg, '');
            return path;
        },

        /**
         * Extract a username from an url.
         *
         * @param {string} url
         * @return {string}
         */
        extractUsername : function (url) {
            BabblingBrook.Test.isA([url, 'url'], 'extractUsername failed.');
            url = BabblingBrook.Library.removeProtocol(url);
            var url_parts = url.split('/');
            if (typeof url_parts[1] !== 'string' || url_parts[1].length === 0) {
                BabblingBrook.TestErrors.reportError('Username is invalid');
            }
            var username = url_parts[1];
            if (username.indexOf('@') > 0) {
                username = username.substring(0, username.indexOf('@'));
            }
            BabblingBrook.Test.isA(
                [username, 'username'],
                'extractUsername failed. Usermane is not a valid username.'
            );
            return decodeURI(username);
        },

        /**
         * Extract the name of the BabblingBrook feature from an url. EG an stream name.
         *
         * @param {string} url The url to extract a name from.
         *
         * @return string
         */
        extractName : function (url) {
            BabblingBrook.Test.isA([url, 'url'], 'extractUsername failed.');
            url = BabblingBrook.Library.removeProtocol(url);
            var url_parts = url.split('/');
            if (typeof url_parts[3] !== 'string' || url_parts[4].length === 0) {
                BabblingBrook.TestErrors.reportError('Feature name is invalid');
            }
            var name = url_parts[3];
            name = decodeURIComponent(name);
            return name;
        },

        /**
         * Extract the action of the BabblingBrook feature from an url.
         *
         * @param {string} url The url to extract a name from.
         *
         * @return string
         */
        extractAction : function (url) {
            BabblingBrook.Test.isA([url, 'url'], 'extractAction failed.');
            url = BabblingBrook.Library.removeProtocol(url);
            var url_parts = url.split('/');
            if (typeof url_parts[7] !== 'string' || url_parts[7].length === 0) {
                return '';
            }
            var action = url_parts[7];
            action = decodeURIComponent(action);
            return action;
        },

        /**
         * Extract the value of an item in the path based on its index.
         *
         * @param {string} url The url to extract a name from.
         * @@param {number} index The index of the item to extract.
         *      one based to account for starting with a forward slash.
         *
         * @return string|false The path item or false if not found.
         */
        extractPathItem : function (url, index) {
            BabblingBrook.Test.isA([url, 'url'], 'extractAction failed.');
            url = BabblingBrook.Library.removeProtocol(url);
            var path = BabblingBrook.Library.extractPath(url);
            var path_parts = path.split('/');
            if (typeof path_parts[index] !== 'string' || path_parts[index].length === 0) {
                return false;
            }
            var path_item = decodeURIComponent(path_parts[index]);
            return path_item;
        },

        /**
         * Extract a version string from an url.
         *
         * @param {string} url The url to extract a version from.
         *
         * @return {string} The version in the format major/minor/patch
         */
        extractVersion : function (url) {
            BabblingBrook.Test.isA([url, 'url'], 'extractVersion failed.');
            url = BabblingBrook.Library.removeProtocol(url);
            var url_parts = url.split('/');
            var version = url_parts[4] + '/' + url_parts[5] + '/' + url_parts[6];
            return version;
        },

        /**
         * Extract the version parts from an url. Assume it is the last three sections.
         *
         * @param {string} url The url to extract a version from.
         *
         * @return {object} A version object with major, minor and parts components.
         */
        extractVersionParts : function (url) {
            BabblingBrook.Test.isA([url, 'url'], 'extractVersionParts failed.');
            var version = BabblingBrook.Library.extractVersion(url);
            var version_split = version.split('/');
            var version_parts = {
                major : version_split[0],
                minor : version_split[1],
                patch : version_split[2]
            };
            return version_parts;
        },

        /**
         * Takes a version object and returns a string of that version.
         *
         * @param {object} version A standard version object as used in resource name objects.
         *
         * @returns {string} A string that represents the version in the format 'major/minor/patch.
         */
        makeVersionString : function(version) {
            if (typeof version === 'undefined') {
                console.error('version is undefined');
            }
            if (typeof version.major === 'undefined') {
                console.error('version does not have a major component');
            }
            if (typeof version.minor === 'undefined') {
                console.error('version does not have a minor component');
            }
            if (typeof version.patch === 'undefined') {
                console.error('version does not have a patch component');
            }
            return version.major + '/' + version.minor + '/' + version.patch;
        },

        /**
         * Makes a version object from a version string.
         *
         * @param {string} version_string The version in string format of major/minor/patch.
         *
         * @return {object} The version object.
         */
        makeVersionObject : function (version_string) {
            var test = BabblingBrook.Test.isA([version_string, 'version'], '', false);
            if (test === false) {
                throw 'makeVersionObject was passed a string that is not a version string: ' + version_string;
            }

            var version_parts = version_string.split('/');
            var version_obejct = {
                major : version_parts[0],
                minor : version_parts[1],
                patch : version_parts[2]
            }
            return version_obejct;
        },

        /**
         * Extract the resource type from an url. Eg stream or rhythm.
         *
         * Assumes it is in the third section of the path.
         *
         * @param {string} url
         *
         * @return {string}
         */
        extractResource : function (url) {
            BabblingBrook.Test.isA([url, 'url'], 'extractUsername failed.');
            url = BabblingBrook.Library.removeProtocol(url);
            var url_parts = url.split('/');
            if (url_parts.length < 3) {
                return '';
            } else {
                return url_parts[2];
            }
        },

        /**
         * Given a stream url, makes a standard BabblingBrook.Models.streamName object.
         *
         * @param {string} url The url of the stream. If there is an action it is ignored.
         * @param {boolean} [use_username_domain=false] Should the domain be taken from the username if it is present.
         *
         * @return {object} A BabblingBrook.Models.streamName object.
         */
        makeStreamFromUrl : function (url, use_username_domain) {
            var result = BabblingBrook.Models.streamUrl(url);
            if (result === true) {
                return BabblingBrook.Library.makeResourceFromUrl(url, use_username_domain);
            } else {
                return result;
            }
        },

        /**
         * Given a rhythm url, makes a standard BabblingBrook.Models.rhythmName object.
         *
         * @param {string} url The url of the rhythm. If there is an action it is ignored.
         *
         * @return {object} A BabblingBrook.Models.rhythmName object.
         */
        makeRhythmFromUrl : function (url) {
            var result = BabblingBrook.Models.rhythmUrl(url);
            if (result === true) {
                return BabblingBrook.Library.makeResourceFromUrl(url);
            } else {
                return result;
            }
        },

        /**
         * Given a rhythm or stream url, makes a standard BabblingBrook.Models.rhythmName/streamName object.
         *
         * @param {string} url The url of the rhythm or stream. If there is an action it is ignored.
         * @param {boolean} [use_username_domain=false] Should the domain be taken from the username if it is present.
         *
         * @return {object} A BabblingBrook.Models.rhythmName/strteamName object.
         */
        makeResourceFromUrl : function (url, use_username_domain) {
            var resource = {
                domain : BabblingBrook.Library.extractDomain(url, use_username_domain),
                username : BabblingBrook.Library.extractUsername(url),
                name : BabblingBrook.Library.extractName(url),
                version : BabblingBrook.Library.extractVersionParts(url)
            };
            return resource;
        },

        /**
         * Given a user url, makes a standard BabblingBrook.Models.userName object.
         *
         * @param {string} url The url of the user. If there is an action it is ignored.
         *
         * @return {object} A BabblingBrook.Models.userName object or an error message.
         */
        makeUserFromUrl : function (url) {
            var result = BabblingBrook.Models.userUrl(url);
            if (result === true) {
                var user = {
                    domain : BabblingBrook.Library.extractDomain(url),
                    username : BabblingBrook.Library.extractUsername(url)
                };
                return user;
            } else {
                return result;
            }
        },

        /**
         * Given a rhythmName object, makes a full url.
         *
         * @param {object} rhythm_name The rhythmName object to make the rhythm url from.
         *      For backwards compatibility the version can be an object or a string.
         * @param {string} action The name of the action to use in the url.
         * @param {boolean} [absolute=true] Should the url be relative or absolute.
         *
         * @return {string} The rhythm url
         */
        makeRhythmUrl : function (rhythm_name, action, absolute) {
            return BabblingBrook.Library.makeResourceUrl(rhythm_name, action, 'rhythm', absolute);
        },

        /**
         * Given a streamName object, makes a full url.
         *
         * @param {object} stream_name The streamName object to make the stream url from.
         *      For backwards compatibility the version can be an object or a string.
         * @param {string} action The name of the action to use in the url.
         * @param {boolean} [absolute=true] Should the url be relative or absolute.
         *
         * @return {string} The stream url
         */
        makeStreamUrl : function (stream_name, action, absolute) {
            return BabblingBrook.Library.makeResourceUrl(stream_name, action, 'stream', absolute);
        },

        /**
         * Given a streamName of rhythmName object, make a standard resource url.
         *
         * @param {object} resource The streamName or rhythmName object to make the url from.
         *      For backwards compatibility the version can be an object or a string.
         * @param {string|undefined} action The name of the action to use in the url.
         * @param {string} type The type of resource. IE stream or rhythm.
         * @param {boolean} [absolute=true] Should the url be relative or absolute.
         *
         * @return {string} The url
         */
        makeResourceUrl : function (resource, action, type, absolute) {
            var url = '';
            if (absolute !== false) {
                url = resource.domain;
            }
            url += '/' + resource.username;
            url += '/' + type;
            url += '/' + resource.name;
            var version = resource.version;
            if (typeof resource.version === 'object') {
                version = resource.version.major + '/' + resource.version.minor  + '/' + resource.version.patch ;
            }
            url += '/' + version;
            if (typeof action === 'string') {
                url += '/' + action;
            }
            return url;
        },

        /**
         * Given a userName object, makes a full url.
         *
         * @param {object} user_name The userName object to make the user url from.
         *
         * @return {string} The user url
         */
        makeUserUrl : function (user_name) {
            var url = '';
            url += user_name.domain;
            url += '/' + user_name.username;
            return url;
        },

        /**
         * Changes the version on the end of a rhythm url to a new one.
         *
         * @param {string} original_url The url to change.
         * @param {string} new_version The new version in major/minor/patch format.
         *
         * @returns {string} The new url
         */
        changeRhythmUrlVersion : function (original_url, new_version)  {
            return BabblingBrook.Library.changeResourceUrlVersion(original_url, new_version);
        },

        /**
         * Changes the version on the end of a stream url to a new one.
         *
         * @param {string} original_url The url to change.
         * @param {string} new_version The new version in major/minor/patch format.
         *
         * @returns {string} The new url
         */
        changeStreamUrlVersion : function (original_url, new_version)  {
            return BabblingBrook.Library.changeResourceUrlVersion(original_url, new_version);
        },

        /**
         * Changes the version on the end of a stream url to a new one.
         *
         * @param {string} original_url The url to change.
         * @param {string} new_version The new version in major/minor/patch format.
         *
         * @returns {string} The new url
         */
        changeResourceUrlVersion : function (original_url, new_version)  {
            var new_url = BabblingBrook.Library.extractDomain(original_url) + '/' +
                BabblingBrook.Library.extractUsername(original_url) + '/' +
                BabblingBrook.Library.extractResource(original_url) + '/' +
                BabblingBrook.Library.extractName(original_url) + '/' +
                new_version;
            return new_url;
        },

        /**
         * Checks if too resource name objects match.
         *
         * @param {object} resource1 A standard resource name object
         * @param {object} resource2 A standard resource name object
         *
         * @returns {boolean}
         */
        doResourcesMatch : function (resource1, resource2) {
            if (BabblingBrook.Test.isA([resource1, 'resource-object'], '', false) === false) {
                console.log(resource1);
                throw 'Not a valid resource name object';
            }
            if (BabblingBrook.Test.isA([resource2, 'resource-object'], '', false) === false) {
                console.log(resource2);
                throw 'Not a valid resource name object';
            }

            resource1.name = resource1.name.replace(/\+/g, ' ');
            resource2.name = resource2.name.replace(/\+/g, ' ');

            if (resource1.domain === resource2.domain
                && resource1.username === resource2.username
                && resource1.name === resource2.name
            ) {
                if ((typeof resource1.version === 'string' && typeof resource1.version !== 'string')
                    || (typeof resource1.version !== 'string' && typeof resource1.version === 'string')
                ) {
                    console.log('Warning Comparing a resource with a string version to one with an object version');
                    return false;
                }
                if (typeof resource1.version === 'string') {
                    if (resource1.version === resource2.version) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if (resource1.version.major === resource2.version.major
                        && resource1.version.minor === resource2.version.minor
                        && resource1.version.patch === resource2.version.patch
                    ) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        },

        /**
         * Checks if too stream name objects match.
         *
         * @param {object} stream1 A standard stream name object
         * @param {object} stream2 A standard stream name object
         *
         * @returns {boolean}
         */
        doStreamsMatch : function (stream1, stream2) {
             return BabblingBrook.Library.doResourcesMatch(stream1, stream2);
        },

        /**
         * Checks if too stream name objects match.
         *
         * @param {object} user1 A standard user object
         * @param {object} user2 A standard user object
         *
         * @returns {boolean}
         */
        doUsersMatch : function (user1, user2) {
            if (BabblingBrook.Library.Test.isA([user1, 'user'], '', false) === false) {
                console.log(user1);
                throw 'Not a valid user object';
            }
            if (BabblingBrook.Library.Test.isA([user2, 'user'], '', false) === false) {
                console.log(user2);
                throw 'Not a valid user object';
            }
            if (user1.domain === user2.domain
                && user1.username === user2.username
            ) {
                    return true;
            } else {
                    return false;
            }
        },


        /**
         * Checks if too rhythm name objects match.
         *
         * @param {object} rhythm1 A standard rhythm name object
         * @param {object} rhythm2 A standard rhythm name object
         *
         * @returns {boolean}
         */
        doRhythmsMatch : function (rhythm1, rhythm2) {
            return BabblingBrook.Library.doResourcesMatch(rhythm1, rhythm2);
        },

        /**
         * Normalises stream and rhythm resourse names. (Changes + signs for spaces).
         *
         * @param {string} name The name of the stream or rhythm (Not a resource object).
         *
         * @returns {string} The normalised resource name.
         */
        normaliseResourceName : function (name) {
            return name.replace(/\+/g, ' ');
        },

        /**
         * Converts the version string in a resource object so that it is version object.
         *
         * If the version is already an object then it simply returns it.
         *
         * @returns {object} The passed in resource object with the converted version.
         */
        convertResourceObjectVersion : function (resource) {
            if (typeof resource.version === 'object') {
                return resource;
            }
            resource.version = BabblingBrook.Library.makeVersionObject(resource.version);
            return resource;
        },

        /**
         * remove the protocol from an url.
         *
4        * @param {string} url The url to remove the protocol from.
         *
         * @return {string} The url without the protocol.
         */
        removeProtocol : function (url) {
            if (url.substr(0, 8) === 'https://') {
                url =  url.substr(8);
            } else if (url.substr(0, 7) === 'http://') {
                url = url.substr(7);
            }
            return url;
        },

        /**
         * Parses a string containing json data, converting it into an object.
         *
         * @param {string} data_string the string to convert into an object.
         * @param {string} [error] The error message to display if conversion fails.
         *      False is returned if this is ommited.
         *
         * @return {object}
         */
        parseJSON : function (data_string, error) {

            var data;
            try {
                data = JSON.parse(data_string);
                return data;
            } catch (e) {
                if (typeof error !== 'undefined') {
                    console.group('parseJSON error on  ' + window.location.host + ' domain.');
                    if (typeof error === 'string') {
                        console.log(error);
                    }
                    console.error(e);
                    console.groupEnd();
                    throw jQuery('#thread_execution_stopped_template').html();
                }
                return false;
            }
        },

        /**
         * Execute a function by its name and arguments.
         *
         * The function needs to be in the globalscope, but can include a namepace.
         *
         * @param {string} functionName The should include the full namespace in the usual form, eg:
         *      'BabblingBrook.Client.SomeModule.publicFunction'.
         * @param {object} context The context in which the function is being called. Eg window.
         * @param {array} args An array of arguments to pass to the function
         *
         * @return {mixed} the result of calling the function.
         */
        executeFunctionByName : function (functionName, context, args) {
            var namespaces = functionName.split(".");
            var func = namespaces.pop();
            for(var i = 0; i < namespaces.length; i++) {
                context = context[namespaces[i]];
            }
            return context[func].apply(this, args);
        },

        /**
         * Tests if an object is an array.
         * @param {object} obj
         * @return {boolean}
         */
        isArray : function (obj) {
            if (typeof obj !== 'object' || obj === null) {
                return false;
            }
            return obj.constructor === Array;
        },

        /**
         * Checks if a nested object exists.
         *
         * @param {object} nested_obj The object to search.
         * @param {string[]} names An array of names of nested objects to check for.
         *
         * @return {boolean}
         */
        doesNestedObjectExist : function (nested_obj, names) {
            if(typeof nested_obj === 'undefined') {
                return false;
            }
            var exists = true;
            jQuery.each(names, function (i, name) {
                if (typeof nested_obj[name] === 'undefined') {
                    exists = false;
                    return false;        // Escape from the jQuery.each function.
                }
                nested_obj = nested_obj[name];
                return true;        // Continue the jQuery.each function.
            });
            return exists;
        },

        /**
         * Checks if a nested objects structure is correct.
         *
         * @param {object} obj The object to search.
         * @param {object} names An object whoose structure matches obj but
         *                       whose attributes are the types of variable contained in obj.
         *                         EG for data =   { elem1 : nest { bar : '1', foo : false}, elem2 : 3}
         *                         is described by { elem : nest { bar : 'string', foo : boolean }, elem2 : 'number'}
         * @return {boolean}
         */
        doNestedObjectsExist : function (obj, names) {
            var obj_length = BabblingBrook.Library.objectSize(obj);
            var names_length = BabblingBrook.Library.objectSize(names);
            if (typeof obj !== 'object' || typeof names !== 'object') {
                return false;
            }
            if (obj_length !== names_length) {
                return false;
            }

            var exists = false;
            var i;

            jQuery.each(obj, function (obj_name, sub_obj) {
                jQuery.each(names, function (name_name, sub_name) {
                    if (name_name === obj_name) {
                        if (typeof sub_obj === 'object') {
                            exists = BabblingBrook.Library.doNestedObjectsExist(sub_obj, sub_name);
                        } else {
                            exists = (typeof sub_obj === sub_name);
                        }
                        return false; // Escape from the jQuery.each function.
                    }
                    return true;    // Continue from the jQuery.each function.
                });

                if (!exists) {
                    exists = false;
                    return false;    // Escape from the jQuery.each function.
                }
                return true;        // Continue with the jQuery.each function.

            });
            return exists;
        },

        /**
         * Creates a nested object if it does not exist, including all sub objects.
         *
         * @param {object} obj The object to create a nested object within.
         * @param {object[]} names An array of names of nested objects to create.
         * @param {string} type Create an 'object' or 'array' at the end of the nesting. Defaults to object.
         *
         * @return {object}
         */
        createNestedObjects : function (obj, names, type) {
            if (typeof type === 'undefined') {
                type = 'object';
            }

            var nested_obj = obj;
            var last_name = names[names.length - 1];
            jQuery.each(names, function (i, name) {
                if (typeof nested_obj[name] === 'undefined') {
                    if (type === 'object' || last_name !== name) {
                        nested_obj[names[i].toString()] = {}; // Ensure the index is a string even if a number is given.
                    } else if (type === 'array') {
                        nested_obj[name] = [];
                    }
                }
                nested_obj = nested_obj[name];
            });
            return obj;
        },

        /**
         * Converts a resource name object (stream or rhythm) and returns an array suitable for
         * passing to createNestedObjects and doesNestedObjectExist.
         *
         * @param {object} resource A standard resource name object (stream or rhythm).
         *
         * @return {array}
         */
        formatResourceAsArray : function (resource) {
            var resource_array = [
                resource.domain,
                resource.username,
                resource.name,
                resource.version.major,
                resource.version.minor,
                resource.version.patch
            ];
            return resource_array;
        },

        /**
         * Returns a reference to a nested object.
         *
         * @param {object} obj The root of the nested object.
         * @param {array} namespace The namespace of the nested object.
         *      Each step down the nested object is another row in the namespace array.
         *
         * @return {object}
         */
        getNestedObject : function (obj, namespace) {
            if (typeof namespace[0] === 'undefined') {
                return obj;
            }
            if (typeof obj[namespace[0]] === 'undefined') {
                throw 'getNestedObject is being passed a namespace that does not exist in the object';
            }
            var nestedobject = obj[namespace[0]];
            namespace.splice(0, 1);
            return BabblingBrook.Library.getNestedObject(nestedobject, namespace);
        },

        /**
         * Returns a reference to a nested object when given the paretn object and an array of nested namespaces.
         *
         * @param {object} obj The ancestor object.
         * @param {array} names An array of sub object namespaces.
         * @param {any} value The value to place in the nested object.
         * @param {boolean} [create] Should the nested object be created if it does not exist.
         * @param {string} [type='object'] Is the nested object an 'array' or an 'object'.
         *
         * @return A reference to the nested object.
         */
        getNestedObjectByName : function (obj, names, value, create, type) {
            if (typeof create !== 'undefined' && create === true) {
                BabblingBrook.Library.createNestedObjects(obj, names, type);
            } else {
                if (BabblingBrook.Library.doesNestedObjectExist(obj, names) === false) {
                    return false;
                }
            }
            var tmp = obj;
            var key_length = names.length -1;
            for (var j=0; j < key_length; j++) {
                tmp = tmp[names[j]];
            }
            tmp[names[key_length]] = value;
            return tmp[names[key_length]];
        },

        /**
         * Returns an array of type definieitons that match the passed in a object types
         *
         * @param {object|array} obj The Object to generate an array of types for.
         *
         * @returns {object} An object or array that is identicaly structured to the passed in object but contains
         * the types of the rows rather than the contents.
         */
        makeNestedTypeObject : function (obj) {
            var types;
            if (typeof obj === 'array') {
                types = [];
            } else if (typeof obj === 'object') {
                types = {};
            } else {
                throw 'makeNestedTypeObject failed. passed in object is not an array or object.';
            }

            jQuery.each(obj, function(index, row) {
                if (typeof row === 'object') {}
            });

        },

        /**
         * Return the size of an object.
         * @param {object} obj The object to get the size of.
         * @return {number} The size.
         */
        objectSize : function (obj) {
            var size = 0, key;
            for (key in obj) {
                if (obj.hasOwnProperty(key)) {
                    size++;
                }
            }

            return size;
        },

        /**
         * Chooses a random propery from an object.
         *
         * @param {object} obj
         *
         * @returns {any}
         */
        pickRandomProperty : function (obj) {
            var keys = Object.keys(obj)
            var random_key = Math.floor(keys.length * Math.random());
            return obj[keys[random_key]];
        },

        /**
         * Get a paramter from the current urls query string.
         *
         * @param {string} name the name of the paramater to fetch.
         *
         * @return {string}
         */
        getParameterByName : function (name) {
            // jsLint error; was : name = name.replace(/[\[]/, '\\\[').replace(/[\]]/, '\\\]');
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var reg = '[\\?&]' + name + '=([^&#]*)';
            var regex = new RegExp(reg);
            var results = regex.exec(window.location.href);
            if (results === null) {
                return '';
            } else {
                return decodeURIComponent(results[1].replace(/\+/g, ' '));
            }
        },

        /**
         * Removes a paramater from the urls query string.
         *
         * @param string url The url to remove a paramater from.
         * @param string param The name of the paramater to remove.
         *
         * @return string The url without the paramater.
         */
        removeParamaterFromUrl : function (url, param) {
            var urlparts = url.split('?');

            if (urlparts.length >= 2) {
                var urlBase = urlparts.shift();       //get first part, and remove from array
                var queryString = urlparts.join("?"); //join it back up

                var prefix = encodeURIComponent(param) + '=';
                var params = queryString.split(/[&;]/g);
                for (var i = params.length - 1; i >= 0; i--) {
                    if (params[i].lastIndexOf(prefix, 0) !== -1) {
                        params.splice(i, 1);
                    }
                }
                url = urlBase;
                if (params.length > 0) {
                    url = url + '?' + params.join('&');
                }
            }
            return url;
        },

        /**
         * Returns a string representing a date in the BabblingBrook format of YYYY-MM-DD.
         * @param {Date} dte The date to parse.
         * @return {string}
         */
        getDate : function (dte) {
            var year = dte.getUTCFullYear();
            var month = String(dte.getUTCMonth() + 1);
            if (month.length === 1) {
                month = '0' + month;
            }
            var day = String(dte.getUTCDate());
            if (day.length === 1) {
                day = '0' + day;
            }
            return year + '-' + month + '-' + day;
        },

        /**
         * Checks if two urls match.
         *
         * Will try encoding both urls to get a match in case one version is not encoded and the other is.
         *
         * @param {string} first_url The first version of the url to test.
         * @param {string} second_url The second version of the url to test.
         *
         * @return boolean
         */
        urlMatch : function (first_url, second_url) {
            if (first_url === second_url) {
                return true;
            }

            if (encodeURI(first_url) === second_url) {
                return true;
            }

            if (first_url === encodeURI(second_url)) {
                return true;
            }

            return false;

        },

        /**
         * Checks if the submitted text is a link.
         * @param {string} text.
         * @return {boolean}
         */
        checkForLink : function (text) {
            // This regex is generous rather than strict in its matching links.
            if (text.match(/^[a-z]+:\/\/[a-z0-9-_]+\.[a-z0-9-_]+/) || text.match(/^www\.[a-z0-9-_]+\.[a-z0-9-_]+/)) {
                return true;
            } else {
                return false;
            }
        },

        /**
         * Exctract the domain from a full username.
         *
         * Can be in either domain/username or username@domain formats
         *
         * @param {string} full_username The full username.
         * @param {boolean} [throw_erorr=true] Should an error be thrown if the name is invalid.
         *
         * @return string|false
         */
        extractDomainFromFullUsername : function (full_username, throw_error) {
            if (typeof throw_error === 'undefined') {
                throw_error = true;
            }
            if (BabblingBrook.Test.isA([full_username, 'full-username'], '', false) === false) {
                if (throw_error === true) {
                    console.error(
                        'extractDomainFromFullUsername expects a full username to be submitted. ' +
                        full_username + ' submitted.'
                    );
                    throw 'Thread execution stopped.';
                } else {
                    return false;
                }
            }

            var domain;
            if (full_username.indexOf('/') > 0) {
                domain = full_username.substring(0, full_username.indexOf('/'));
            } else if (full_username.indexOf('@') > 0) {
                domain = full_username.substring(full_username.indexOf('@') + 1);
            }
            return domain;
        },

        /**
         * Decodes an url componentent according to Babbling Brook protocol rules.
         *
         * @param {string} str The url component to decode.
         *
         * @returns {string} The decoded component.
         */
        decodeUrlComponent : function (str) {
            return str.replace('+', ' ','g');
        },

        /**
         * Encodes an url componentent according to Babbling Brook protocol rules.
         *
         * @param {string} str The url component to decode.
         *
         * @returns {string} The decoded component.
         */
        encodeUrlComponent : function (str) {
            return str.replace(' ', '+','g');
        },

        /**
         * Exctract the usernane from a full username.
         *
         * @param {string} full_username The full username.
         * @param {boolean} [throw_erorr=true] Should an error be thrown if the name is invalid.
         *
         * @return string
         */
        extractUsernameFromFullUsername : function (full_username, throw_error) {
            if (typeof throw_error === 'undefined') {
                throw_error = true;
            }
            if (BabblingBrook.Test.isA([full_username, 'full-username'], '', false) === false) {
                if (throw_error === true) {
                    console.error(full_username);
                    console.error('extractUsernameFromFullUsername expects a full username to be submitted.');
                    throw 'Thread execution stopped.';
                } else {
                    return false;
                }
            }

            var username;
            if (full_username.indexOf('/') > 0) {
                username = full_username.substring(full_username.indexOf('/') + 1);
            } else if (full_username.indexOf('@') > 0) {
                username = full_username.substring(0, full_username.indexOf('@'));
            }
            return username;
        },

        /**
         * Checks if a value is an integer.
         * @param {Number|String} value The value to test.
         * @return Boolean
         */
        isInt : function (value) {
            if ((parseFloat(value) === parseInt(value, 10)) && !isNaN(value)) {
                return true;
            } else {
                return false;
            }
        },

        /**
         * Presents a date in an 'Ago' format, eg 2 hours ago.
         *
         * Adapted from http://ejohn.org/blog/javascript-pretty-date/#comment-297470
         *
         * @param {number} timestamp A unix timestamp to use in calculating how long ago the date was.
         *
         * @returns {String}
         */
        timeAgoDate : function (timestamp) {
            var now = Math.round(new Date().getTime() /1000);
            var seconds = now - timestamp;
            var token = 'ago';
            var list_choice = 1;
            if (seconds < 0) {
                seconds = Math.abs(seconds);
                token = 'from now';
                list_choice = 2;
            }

            var time_formats = [
                [60, 'seconds', 1], // 60
                [120, '1 minute ago', '1 minute from now'], // 60*2
                [3600, 'minutes', 60], // 60*60, 60
                [7200, '1 hour ago', '1 hour from now'], // 60*60*2
                [86400, 'hours', 3600], // 60*60*24, 60*60
                [172800, 'yesterday', 'tomorrow'], // 60*60*24*2
                [604800, 'days', 86400], // 60*60*24*7, 60*60*24
                [1209600, 'last week', 'next week'], // 60*60*24*7*4*2
                [2419200, 'weeks', 604800], // 60*60*24*7*4, 60*60*24*7
                [4838400, 'last month', 'next month'], // 60*60*24*7*4*2
                [29030400, 'months', 2419200], // 60*60*24*7*4*12, 60*60*24*7*4
                [58060800, 'last year', 'next year'], // 60*60*24*7*4*12*2
                [2903040000, 'years', 29030400], // 60*60*24*7*4*12*100, 60*60*24*7*4*12
                [5806080000, 'last century', 'next century'], // 60*60*24*7*4*12*100*2
                [58060800000, 'centuries', 2903040000] // 60*60*24*7*4*12*100*20, 60*60*24*7*4*12*100
            ];

            var i = 0;
            while (seconds > time_formats[i][0]) {
                i++;
                if (i >= time_formats.length) {
                    return new Date(timestamp * 1000).toString();
                }
            };
            var format = time_formats[i];

            var time_string;
            if (typeof format[2] === 'string') {
                time_string = format[list_choice];
            } else {
                time_string = Math.floor(seconds / format[2]) + ' ' + format[1] + ' ' + token;
            }

            // capitalise the first letter.
            time_string = time_string.charAt(0).toUpperCase() + time_string.slice(1);

            return time_string;
        },

        /**
         * Waits until a condition is met before running a callback.
         *
         * If the time runs out then an error callback is called.
         *
         * @param {function} condition A callback function that evaluates a condition in the origional scope.
         *      It must return true or false.
         * @param {function} success The function to run when the condition is met.
         * @param {function} error A function to call on timeout. If not given then an error will be raised.
         *      If an error code is required, it should be bound to the callback.
         * @param {object|string} [error_object] These objects are reported if there is an error.
         *      They are not passed to the error callback.
         *      These help in tracking down errors.
         * @param {number} [timeout=30] timeout Time to wait in milliseconds before rerunning the timeout.
         * @param {number} [timeout=10000] timeout_time Time to wait in milliseconds before timing out and
         *                                              reporting an error.
         *
         * @return void
         */
        wait : function (condition, success, error, error_object, timeout, timeout_time) {
            if (typeof timeout !== 'number') {
                timeout = 150;
            }

            if (typeof condition !== 'function') {
                console.error('BabblingBrook.Library.wait function requires a condition callback');
                throw 'Thread execution stopped.';
            }

            if (typeof success !== 'function') {
                console.error('BabblingBrook.Library.wait function requires a success callback');
                throw 'Thread execution stopped.';
            }

            if (typeof error !== 'function') {
                console.error('BabblingBrook.Library.wait function requires an error callback');
                throw 'Thread execution stopped.';
            }


            if (typeof BabblingBrook.Client === 'object' && typeof timeout_time === 'undefined') {
                timeout_time = BabblingBrook.Client.User.settimeout_timeout;
            }
            if (typeof BabblingBrook.DomusUser === 'object' && typeof timeout_time === 'undefined'
                && BabblingBrook.Library.doesNestedObjectExist(BabblingBrook.DomusUser, 'settimeout_timeout')
            ) {
                timeout_time = BabblingBrook.DomusUser.settimeout_timeout;
            }
            if (typeof timeout_time === 'undefined') {
                timeout_time = 55000;
            }

            var start_time = new Date().getTime();
            var localTimeout = function () {
                var current_time = new Date().getTime();
                if (condition()) {
                    success();
                } else if (current_time - start_time > timeout_time) {
                    if (typeof error_object !== 'undefined') {
                        console.dir(error_object);
                        console.error('A library wait function timed out.');
                    }
                    error();
                } else {
                    setTimeout(localTimeout, timeout);
                }
            };
            localTimeout(); // Do first try straight away.
        },

        /**
         * Send an error to the server for logging. Ands then forward it to the client for the bug report form.
         *
         * @param {string} type The type of error.
         * @param {object} data Any data associated with the error.
         * @param {string} message Any message associated with the error.
         * @param {string} location One of client|domus|scientia
         */
        logError : function (type, data, message, location) {
            try {
                var url = '/site/jserror';
                BabblingBrook.Library.post(
                    url,
                    {
                        type : type,
                        data : JSON.stringify(data),
                        message : message,
                        location : location
                    },
                    function (data) {
                        if (typeof data.error === 'string' && data.error.legnth > 0) {
                            console.error(location + ' domain error not sent to server. Error message : ' + data.error);
                        } else {
                            var error_message = 'An error was logged in the db -> id : ' + data.id +
                                ' type : ' + type + ' data : ' +  JSON.stringify(data) +
                                ' message : ' + message + ' location : ' + location;
                            if (typeof BabblingBrook.Client !== 'undefined') {
                                BabblingBrook.Client.Component.ReportBug.appendSubDomainError({
                                    domain : 'client-jserror',
                                    error : error_message
                                });
                            } else if (typeof BabblingBrook.Domus !== 'undefined') {
                                BabblingBrook.Domus.Interact.postAMessage(
                                    {
                                        domain : 'domus',
                                        error : error_message
                                    },
                                    BabblingBrook.Domus.Controller.client_domain,
                                    'Error',
                                    function () {},
                                    function () {
                                        console.error(
                                            'logError in the domus Library is ' +
                                            'erroring whilst wating for client to respond.'
                                        );
                                    },
                                    BabblingBrook.Domus.Controller.client_https
                                );
                            } else {
                                BabblingBrook.Shared.Interact.postAMessage(
                                    {
                                        error : error_message
                                    },
                                    'Error',
                                    function () {},
                                    function () {
                                        console.error(
                                            'logError in the ' + location + ' Library is ' +
                                            'erroring whilst wating for domus to respond.'
                                        );
                                    }
                                );
                            }
                        }
                    }
                );

            } catch (exception) {
                console.error('Caught an error whilst logging an error. Ahhh. It is regression hell.');
                console.error(exception);
            }
        },

        /**
         * Send an ajax post request. See BabblingBrook.Library.ajax for full details.
         *
         * @param {string} url
         * @param {object} request_data Data to be sent with the request.
         * @param {function} successCallback The success callback function.
         *      Accepts a single data object paramater
         * @param {function} [errorCallback] The error callback function.
         *      Can accept an error_code paramater, which will pass an error code on from the server.
         * @param {string} [error_code] The BabblingBrook error code to use.
         *      This is overridden if the server returns one.
         * @param {number} timeout A millisecond unix timestamp for when this request should timeout.
         * @param {object} [settings = {}] Additional ajax settings as per the jquery specification.
         * @see http://api.jquery.com/jQuery.ajax/
         *
         * @return void
         */
        post : function (url, request_data, successCallback, errorCallback, error_code, timeout, settings) {
            if (typeof settings !== 'object') {
                settings = {};
            }
            if (typeof errorCallback !== 'function') {
                errorCallback = function () {
                    console.error(url, request_data);
                    console.error('An error occurred whilst posting to ' + url + '. No error callback was defined.');
                    throw 'Thread execution stopped.';
                };
            }

            request_data.csfr_token = BabblingBrook.csfr_token;

            jQuery.extend(settings, {
                type : 'post',
                data : request_data,
                success : successCallback,
                error : errorCallback
            });
            BabblingBrook.Library.ajax(url, settings, error_code, timeout);
        },

        /**
         * Send an ajax get request. See BabblingBrook.Library.ajax for full details.
         *
         * @param {string} url
         * @param {object} request_data Data to be sent with the request.
         * @param {function} successCallback The success callback function.
         *      Accepts a single data object paramater
         * @param {function} [errorCallback] The error callback function.
         *      Can accept an error_code paramater, which will pass an error code on from the server.
         * @param {string} [error_code] The BabblingBrook error code to use.
         *      This is overridden if the server returns one.
         * @param {number} timeout A millisecond unix timestamp for when this request should timeout.
         * @param {object} [settings = {}] Additional ajax settings as per the jquery specification.
         * @see http://api.jquery.com/jQuery.ajax/
         *
         * @return void
         */
        get : function (url, request_data, successCallback, errorCallback, error_code, timeout, settings) {
            if (typeof settings !== 'object') {
                settings = {};
            }
            jQuery.extend(settings, {
                type : 'get',
                data : request_data,
                success : successCallback,
                error : errorCallback,
                cache : true
            });
            BabblingBrook.Library.ajax(url, settings, error_code, timeout);
        },

        /**
         * Send an ajax request.
         *
         * This maps to a jQuery ajax request with default settings.
         * In addition to this the error handler function can be manually called by calling error()
         *
         * @param {string} url
         * @param {object} request_data Data to be sent with the request.
         *      Accepts a single data object paramater
         * @param {object} [settings = {}] Additional ajax settings as per the jquery specification.
         * @see http://api.jquery.com/jQuery.ajax/
         * @param {string} [error_code] The BabblingBrook error code to use.
         *      This is overridden if the server returns one.
         * @param {number} timeout A millisecond timestamp for when this request should timeout.
         *
         *      Then call success and error callbacks as required.
         */
        ajax : function (url, settings, error_code, timeout) {
            if (typeof settings !== 'object') {
                settings = {};
            }

            if (typeof settings.error !== 'function') {
                settings.error = function () {
                    console.error(url, settings);
                    console.error('An error occurred whilst ' + settings.type + 'ing.  No error callback was defined.');
                    throw 'Thread execution stopped.';
                };
            }
            if (typeof settings.success !== 'function') {
                console.error(url, settings);
                console.error('An ajax function calling .' + url + 'does not have a success callback');
                throw 'Thread execution stopped.';
            }
            if (typeof timeout !== 'undefined') {
                settings.timeout = parseInt(timeout) - parseInt(Math.round(new Date().getTime()));
                if (settings.timeout < 1000) {
                    settings.error(
                        'not_enough_time_for_ajax_request',
                        {
                            url : url
                        }
                    );
                    return;
                }
            } else if (typeof BabblingBrook.Client !== 'undefined'
                && typeof BabblingBrook.Client.User !== 'undefined'
                && typeof BabblingBrook.Client.User.Config !== 'undefined'
                && typeof BabblingBrook.Client.User.Config.ajax_timeout !== 'undefined'
            ) {
                settings.timeout = BabblingBrook.Client.User.Config.ajax_timeout;
            } else {
                settings.timeout = 30000;
            }

            /**
             * Add extra error details to the error handler.
             *
             * @param {object} jqxhr Defined by jQuery, but not used here.
             * @param {string} text_status The ajax status.
             * @param {string} error_thrown Any error that results from the ajax call.
             * @param {string} [error_message] Any predefined error message to use.
             * @param {string} [server_error_code] A BabblingBrook error code returned from the server.
             *
             * @return void
             */
            var originalErrorCallback = settings.error;
            var fullErrorCallback = function (jqxhr, text_status, error_thrown, error_message, server_error_code) {
                if (typeof server_error_code === 'string') {
                    error_code = server_error_code;
                }else if (typeof error_code !== 'string') {
                    error_code = 'default_ajax_error';
                }

                if (typeof error_message !== 'string') {
                    error_message = '';
                }
                if (typeof text_status === 'string') {
                    error_message += ' ajax request text status : ' + text_status + '.';
                }
                if (typeof error_thrown === 'string') {
                    error_message += ' ajax request error thrown : ' + error_thrown + '.';
                    error_code = '404';
                }
                if (typeof jqxhr !== 'undefined' && jqxhr.status === '404') {
                    error_code = '404';
                }
                error_message = 'Attempt to retrieve data from the server failed. URL : '
                    + url + ' Domain : ' + window.location.host + ' ' + error_message;
                var error_data = {
                    error_message : error_message
                };
                originalErrorCallback(error_code, error_data);
            };
            settings.error = fullErrorCallback;

            var originalSuccess = settings.success;
            /**
             * Extend the success callback so that it escapes if the data object is set to false.
             *
             * @param {object} data The data passed back from the server.
             * @param {string} text_status The ajax status.
             * @param {object} jqxhr Defined by jQuery, but not used here.
             *
             * @return void
             */
            settings.success = function (data, text_status, jqxhr) {
                if (data === false) {
                    return;
                } else {
                    originalSuccess(data, text_status, jqxhr);
                }
            };

            var defaults = {
                dataType : 'text',    // It's really JSON, but we dont want jquery messing around with it.
                                      // The dataFilter will convert and catch any errors.
                type : 'get',
                /**
                 * Preprocess the ajax response before calling the callbacks.
                 *
                 * This is done to remove the JSON antihijacking token.
                 *
                 * @param {string} json_string The string to be converted into a javascript object.
                 *
                 * @return {object|string} The resulting javascript object, an error object
                 *      or a string if no conversion required.
                 */
                dataFilter : function (json_string) {
                    // If the dataType is set to string then return it as text rather than json.
                    if(typeof settings.dataType === 'string' && settings.dataType === 'text') {
                        return json_string;
                    }

                    // Remove the &&&BABBLINGBROOK&&& from the json object that prevents data hijacking.
                    if (json_string.substr(0, 19) !== '&&&BABBLINGBROOK&&&') {
                        throw 'JSON response data does not have a valid token to prevent JSON hijacking.';
                    }
                    var json_data = json_string.substr(19);
                    var json_data = BabblingBrook.Library.parseJSON(json_data);
                    if (typeof json_data !== 'object') {
                        var message = ' Returned data for "' + url + '" is not parsing to JSON.';
                        settings.error(undefined, undefined, undefined, message);
                        return false;
                    } else if (typeof json_data.error_code === 'string') {
                        if (typeof json_data.error_message !== 'string') {
                            json_data.error_message = 'No details provided.';
                        }
                        settings.error(undefined, undefined, undefined, json_data.error_message, json_data.error_code);
                        return false;
                    }
                    // Return the data for the success function.
                    return json_data;
                }

            };

            if (BabblingBrook.Library.getCookie('testing') === 'true') {
                settings.data.testing = true;
            }

            jQuery.extend(defaults, settings);
            jQuery.ajax(url, defaults);
        },

        /**
         * Generate a guid for requesting actions on remote domains.
         * Adapted from http://www.broofa.com/Tools/Math.uuid.js
         */
        generateUUID : function () {

            /*jslint bitwise: true*/
            var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            var uuid = new Array(36), rnd = 0, r, i;
            for (i = 0; i < 36; i++) {
                if (i === 8 || i === 13 ||  i === 18 || i === 23) {
                    uuid[i] = '-';
                } else if (i === 14) {
                    uuid[i] = '4';
                } else {
                    if (rnd <= 0x02) {
                        rnd = 0x2000000 + (Math.random() * 0x1000000) | 0;
                    }
                    r = (rnd & 0xf);
                    rnd = rnd >> 4;
                    uuid[i] = chars[(i === 19) ? (r & 0x3) | 0x8 : r];
                }
            }
            /*jslint bitwise: true*/
            return uuid.join('');
        },

        /**
         * Get a cookie. Taken from http://mdn.beonex.com/en/DOM/document.cookie.html
         */
        getCookie: function (sKey) {
            if (!sKey || !this.hasItem(sKey)) { return null; }
            return unescape(
                document.cookie.replace(
                    new RegExp("(?:^|.*;\\s*)" +
                        escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") +
                        "\\s*\\=\\s*((?:[^;](?!;))*[^;]?).*"
                    ),
                    "$1"
                )
            );
        },

        /**
         * Sets a cookie. Taken from http://mdn.beonex.com/en/DOM/document.cookie.html
         *
         * docCookies.setItem(sKey, sValue, vEnd, sPath, sDomain, bSecure)
         *
         * @argument sKey (String): the name of the cookie;
         * @argument sValue (String): the value of the cookie;
         * @optional argument vEnd (Number, String, Date Object or null): the max-age in seconds (e.g., 31536e3 for a year) or the
         *  expires date in GMTString format or in Date Object format; if not specified it will expire at the end of session;
         * @optional argument sPath (String or null): e.g., "/", "/mydir"; if not specified, defaults to the current path of the current document location;
         * @optional argument sDomain (String or null): e.g., "example.com", ".example.com" (includes all subdomains) or "subdomain.example.com"; if not
         * specified, defaults to the host portion of the current document location;
         * @optional argument bSecure (Boolean or null): cookie will be transmitted only over secure protocol as https;
         * @return undefined;
         **/
        setCookie: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
            if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/.test(sKey)) {
                return;
            }
            var sExpires = "";
            if (vEnd) {
                switch (typeof vEnd) {
                    case "number":
                        sExpires = "; max-age=" + vEnd;
                        break;
                    case "string":
                        sExpires = "; expires=" + vEnd;
                        break;
                    case "object":
                        if (vEnd.hasOwnProperty("toGMTString")) {
                            sExpires = "; expires=" + vEnd.toGMTString();
                        }
                        break;
                }
            }
            document.cookie = escape(sKey) + "=" +
                escape(sValue) +
                sExpires +
                (sDomain ? "; domain=" + sDomain : "") +
                (sPath ? "; path=" + sPath : "") +
                (bSecure ? "; secure" : "");
        },

        /**
         * Deletes a cookie. Taken from http://mdn.beonex.com/en/DOM/document.cookie.html
         */
        removeCookie: function (sKey) {
            if (!sKey || !this.hasItem(sKey)) {
                return;
            }
            var oExpDate = new Date();
            oExpDate.setDate(oExpDate.getDate() - 1);
            document.cookie = escape(sKey) + "=; expires=" + oExpDate.toGMTString() + "; path=/";
        },

        /**
         *
         */
        hasItem : function (sKey) {
            return (new RegExp("(?:^|;\\s*)" + escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\="))
                .test(document.cookie);
        }
    }
}());