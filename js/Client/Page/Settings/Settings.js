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
 * @fileOverview Code used to support the users config page.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Settings !== 'object') {
    BabblingBrook.Client.Page.Settings = {};
}

/**
 * Shows the bug report form.
 */
BabblingBrook.Client.Page.Settings.Settings = (function () {
    'use strict';

    /**
     * @type {object} The users cofig options. See onFetchedConfigData for a full definition.
     */
    var user_config;

    /**
     * There is an error in the extra data of a rhythm config row.
     *
     * Rhtyhm rows must contain an rhythm_cat row in the extra_data.
     *
     * @param {object} option The details of this config option. See onFetchedConfigData for full description.
     *
     * @returns {object} A jquery object holding a clone of the row template.
     */
    var errorInRhythmExtraData = function(option) {
        console.error('There is an error in the users config settings. The extra_data is missing the rhythm_cat data.');
        console.log(option);
    };

    /**
     * Nests the contents of a config row inside a standard row template.
     *
     * @param {object} jq_contents A jQuery object containing the contents of the row.
     *
     * @returns {object} A jquery object holding a clone of the row template.
     */
    var nestInRow = function(jq_contents) {
        var jq_row = jQuery('#config_row_template').children().clone();
        jq_row.append(jq_contents);
        return jq_row;
    };

    /**
     * Sets up elements that are shared between all config rows.
     *
     * @param {object} option The details of this config option. See onFetchedConfigData for full description.
     * @param {object} jq_row A jQuery object representing the config row.
     *
     * @returns {void}
     */
    var setupSharedRow = function (option, jq_row) {
        jQuery('label', jq_row).text(option.name);
        jQuery('.config-value', jq_row).val(BabblingBrook.Client.User.Config[option.code]);
        jQuery('.help-icon', jq_row).attr('id','help_config' + option.code);
        jQuery('.help-title', jq_row)
            .attr('id','help_title_config' + option.code)
            .text(option.name);
        jQuery('.help-content', jq_row)
            .attr('id','help_content_config' + option.code)
            .text(option.description);
        jQuery('.config-display-order', jq_row).val(option.code);
        jQuery('.config-code', jq_row).val(option.code);
        if (typeof BabblingBrook.Client.User.Config[option.code] !== 'undefined'
            && BabblingBrook.Client.User.Config[option.code].toString() !== option.value.toString()
        ) {
            jQuery('.config-value', jq_row).addClass('custom');
        }

        jQuery('#config_options').append(jq_row);
    };

    /**
     * Sets up a config option that has a type of uint
     *
     * @param {option} option The details of this config option. See onFetchedConfigData for full description.
     *
     * @returns {void}
     */
    var setupUintRow = function(option) {
        var jq_rhythm_row = jQuery('#config_row_uint_template').children().clone();
        var jq_row = nestInRow(jq_rhythm_row);

        setupSharedRow(option, jq_row);
    };

    /**
     * Sets up a config option that has a type of rhythm_url
     *
     * @param {option} option The details of this config option. See onFetchedConfigData for full description.
     *
     * @returns {void}
     */
    var setupStreamUrlRow = function(option) {

        var jq_stream_row = jQuery('#config_row_stream_template').children().clone();
        var jq_row = nestInRow(jq_stream_row);
        jq_row.addClass('stream-url');

        setupSharedRow(option, jq_row);
    };

    /**
     * Sets up the search button for all config rows with a type of rhythm_url.
     *
     * @returns {void}
     */
    var setupStreamSearch = function() {
        jQuery('.stream-url>.search>a').click(function () {
            var jq_search = jQuery(this);
            var jq_stream_selector = jq_search.parent().parent().find('.stream-selector');
            var jq_input = jq_search.parent().parent().find('.config-value');
            if (jq_stream_selector.is(':visible')) {
                jq_stream_selector.slideUp(250, function () {
                    jq_search.text("search");
                    jq_stream_selector.empty();

                });
                return false;
            }

            var display_order = jq_search.parent().parent().find('.config-display-order').val();

            var actions = [
            {
                name : 'Select',
                onClick : function (event, jq_row, row) {
                    event.preventDefault();
                    var stream_url = BabblingBrook.Library.makeStreamUrl(row, 'json');
                    jq_input.val(stream_url);
                    jq_stream_selector.slideUp(250, function (){
                        jq_search.text("search");
                        jq_stream_selector.empty();
                        jq_input.trigger('blur');
                    });
                }
            }
            ];
            var stream_search_table = new BabblingBrook.Client.Component.Selector(
                'stream',
                'config_stream_' + display_order,
                jq_stream_selector,
                actions,
                {

                }
            );

            jq_search.text("close search");
            jq_stream_selector
                .slideDown(250)
                .removeClass('hide');
            return false;
        });
    };

    /**
     * Sets up a config option that has a type of uint
     *
     * @param {option} option The details of this config option. See onFetchedConfigData for full description.
     *
     * @returns {void}
     */
    var setupUintRow = function(option) {
        var jq_uint_row = jQuery('#config_row_uint_template').children().clone();
        var jq_row = nestInRow(jq_uint_row);

        setupSharedRow(option, jq_row);
    };

    /**
     * Sets up a config option that has a type of string
     *
     * @param {option} option The details of this config option. See onFetchedConfigData for full description.
     *
     * @returns {void}
     */
    var setupStringRow = function(option) {
        var jq_string_row = jQuery('#config_row_string_template').children().clone();
        var jq_row = nestInRow(jq_string_row);

        setupSharedRow(option, jq_row);
    };

    /**
     * Sets up a config option that has a type of rhythm_url
     *
     * @param {option} option The details of this config option. See onFetchedConfigData for full description.
     *
     * @returns {void}
     */
    var setupRhythmUrlRow = function(option) {
        if (option.extra_data === null || typeof option.extra_data.rhythm_cat === 'undefined') {
            errorInRhythmExtraData(option);
            return;
        }

        var jq_rhythm_row = jQuery('#config_row_rhythm_template').children().clone();
        var jq_row = nestInRow(jq_rhythm_row);
        jq_row.addClass('rhythm-url');

        setupSharedRow(option, jq_row);
    };

    var getExtraData = function (config_code) {
        var extra_data;
        jQuery.each(user_config, function (index, row) {
            if (row.code === config_code) {
                extra_data = row.extra_data;
                return;
            }
        });
        return extra_data;
    };

    var getDefault = function (config_code) {
        var default_value;
        jQuery.each(user_config, function (index, row) {
            if (row.code === config_code) {
                default_value = row.value;
                return;
            }
        });
        return default_value;
    };

    /**
     * Sets up the search button for all config rows with a type of rhythm_url.
     *
     * @returns {void}
     */
    var setupRhythmSearch = function() {
        jQuery('.rhythm-url>.search>a').click(function () {

            var jq_search = jQuery(this);
            var jq_rhythm_selector = jq_search.parent().parent().find('.rhythm-selector');
            var jq_input = jq_search.parent().parent().find('.config-value');

            if (jq_rhythm_selector.is(':visible')) {
                jq_rhythm_selector.slideUp(250, function () {
                    jq_search.text("search");
                    jq_rhythm_selector.empty();

                });
                return false;
            }

            var config_code = jq_search.parent().parent().find('.config-code').val();
            var extra_data = getExtraData(config_code);
            var option = BabblingBrook.Client.User.Config[config_code];

            var actions = [
            {
                name : 'Select',
                onClick : function (event, jq_row, row) {
                    event.preventDefault();
                    var rhythm_url = BabblingBrook.Library.makeRhythmUrl(row, 'json');
                    jq_input.val(rhythm_url);
                    jq_rhythm_selector.slideUp(250, function (){
                        jq_search.text("search");
                        jq_rhythm_selector.empty();
                        jq_input.trigger('blur');
                    });
                }
            }
            ];
            var rhythm_cat = extra_data.rhythm_cat;

            var rhythm_search_table = new BabblingBrook.Client.Component.Selector(
                'rhythm',
                'config_rhythm_' + config_code,
                jq_rhythm_selector,
                actions,
                {
                    show_fields : {
                        domain : false
                    },
                    initial_values : {
                        rhythm_category : rhythm_cat
                    }
                }
            );

            jq_search.text("close search");
            jq_rhythm_selector
                .slideDown(250)
                .removeClass('hide');
            return false;
        });
    };

    /**
     * Reports an error when the option.type is not recognised.
     *
     * @param {object} option The config option. See onFetchedConfigData for full description.
     *
     * @param {string} type The invalid type.
     *
     * @returns {void}
     */
    var optionTypeError = function(type) {
        console.error("There is an error in the users config settings. An invalid type is present : " + type);
    };

    /**
     * Sets up a config option for editing.
     *
     * @param {number} index The index of this option in user_config.
     * @param {object} option The config option. See onFetchedConfigData for full description.
     *
     * @returns {void}
     */
    var setupConfigOption = function (index, option) {
        if (BabblingBrook.Client.Settings.settings_to_show.indexOf(option.code) === -1) {
            return;
        }
        switch (option.type) {
            case 'rhythm_url':
                setupRhythmUrlRow(option);
                break;

            case 'stream_url':
                setupStreamUrlRow(option);
                break;

            case 'uint':
                setupUintRow(option);
                break;

            case 'string':
                setupStringRow(option);
                break;

            default:
                optionTypeError(option.type);
        }
    };

    /**
     * Reports an error when an attempt to change a config row fails.
     *
     * @param {object} jq_row A jQuery object representing the row that has failed to change.
     *
     * @returns {void}
     */
    var onChangeRowError = function(jq_row, error_data) {
        var error_message = '';
        if (typeof error_data === 'string') {
            error_message = 'A undefined error occured whilst updating this row. ' + error_data
        } else {
            jQuery.each(error_data, function (index, error_line) {
                error_message += error_line + '<br>';
            });
        }
        jQuery('.config-row-error', jq_row)
            .html(error_message)
            .removeClass('hide');

        jq_row.find('.config-value').removeClass('block-loading');
    };

    /**
     * A config row has successfuly been changed.
     *
     * @param {object} jq_row A jQuery object representing the row that has changed.
     * @param {object} success_data The data returned from the server.
     * @param {boolean} [success_data.value] The value that was set. This may have been changed to a standardised
     *      form from the one that was submitted.
     * @param {object} [success_data.errors] Error list if the request failed.
     *      See onChangeRowError for full description.
     *
     * @returns {void}
     */
    var onConfigChangedSuccess = function(jq_row, code, value, sucess_data) {
        if (typeof sucess_data.error !== 'undefined') {
            onChangeRowError(jq_row, sucess_data.error);
            return;
        }

        jq_row.find('.config-value')
            .removeClass('block-loading');



        var default_value = getDefault(code);
        if (typeof BabblingBrook.Client.User.Config[code] !== 'undefined'
            && BabblingBrook.Client.User.Config[code] === default_value
        ) {
            jq_row.find('.config-value').removeClass('custom');
        } else {
            jq_row.find('.config-value').addClass('custom');
        }
    };

    /**
     * Blur event for when a config row has been edited.
     *
     * @returns {undefined}
     */
    var onConfigChanged = function() {
        var jq_config_value = jQuery(this);
        var value = jq_config_value.val();
        var jq_row = jq_config_value.parent();
        var code = jq_row.find('.config-code').val();

        if (value === '') {
            value = getDefault(code);
            jq_config_value.val(value);

        }

        jQuery('.config-row-error', jq_row).addClass('hide');
        jq_config_value.addClass('block-loading');
        BabblingBrook.Client.User.Config[code] = value;
        BabblingBrook.Client.Core.Interact.postAMessage(
            {
                key : 'user_config.' + code,
                data : value
            },
            'StoreClientUserData',
            onConfigChangedSuccess.bind(null, jq_row, code, value),
            onChangeRowError.bind(null, jq_row)
        );
    };

    /**
     * Callback to recieve the config data. Triggers the setting up of the page.
     *
     * @param {object} config_data The config data indexed by the order to display.
     * @param {string} config_data.code The code that represents this config option.
     * @param {string} config_data.name The name to display for this config option.
     * @param {string} config_data.description The description to display in the help text for this config option.
     * @param {string} config_data.type The type of config option that this is.
     *
     * @returns {void}
     */
    var onFetchedConfigData = function (config_data) {
        jQuery.each(config_data, function (index, option) {
            if (option.extra_data !== null) {
                var extra_json = BabblingBrook.Library.parseJSON(option.extra_data);
                config_data[index].extra_data = extra_json;
            }
        });
        user_config = config_data;
        jQuery.each(config_data, setupConfigOption);

        setupRhythmSearch();
        setupStreamSearch();
        jQuery('.config-value').blur(onConfigChanged);
    };

    /**
     * If the users settings fail to load then show a general error.
     *
     * @returns {void}
     */
    var onGetConfigDataError = function () {
        BabblingBrook.Client.Component.Messages.addMessage({
            type : 'error',
            message : 'An error occured whilst trying to fetch your user settings.',
        });
    };

    return {

        construct : function () {
            BabblingBrook.Library.get(
                '/' + BabblingBrook.Client.User.username + '/settings/get',
                {},
                onFetchedConfigData,
                onGetConfigDataError,
                'get_config_data_error'
            );

            BabblingBrook.Client.Core.Loaded.setSettingsLoaded();
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
        BabblingBrook.Client.Page.Settings.Settings.construct();
    });
});


