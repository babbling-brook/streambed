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
 * @fileOverview Javascript used on the Rhythm list page.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.ManageRhythm !== 'object') {
    BabblingBrook.Client.Page.ManageRhythm = {};
}

/**
 * @namespace Displays a list of Rhythms owned by a user.
 * @package JS_Client
 */
BabblingBrook.Client.Page.ManageRhythm.List = (function () {
    'use strict';

    /**
     * Call back for after the server has responded to a request to chae a rhythms status.
     *
     * @param {string} action 'publish' 'deprecate' or 'delete'.
     * @param {object} jq_row The row identifying which rhythm is having its status changed.
     * @param {object} row_data The data associated with this row.
     * @param {object} response_data The data sent back from the server
     *      after the request to change the status of a rhythm.
     * @param {boolean} response_data.deletable Is the rhythm deletable or not.
     *
     * @return {void}
     */
    var onChangedStatus = function (action, jq_row, row_data, response_data) {
        if (response_data.success === false) {
            onChangedStatusError(response_data.error);
        } else {
            jq_row.removeClass('row-loading');
            var jq_publish = jQuery('img.publish', jq_row);
            var jq_delete = jQuery('img.delete', jq_row);
            var jq_deprecate = jQuery('img.deprecate', jq_row);
            if (response_data.deletable === true) {
                jq_delete.removeClass('hidden');
            } else {
                jq_delete.addClass('hidden');
            }
            switch (action) {
                case 'publish':
                    jq_publish.addClass('hidden');
                    jq_delete.addClass('hidden');
                    jq_deprecate.removeClass('hidden');
                    jQuery('.status', jq_row).text('public');
                    break;

                case 'deprecate':
                    jq_publish.removeClass('hidden');
                    jq_deprecate.addClass('hidden');
                    jQuery('.status', jq_row).text('deprecated');
                    break;

                case 'delete':
                    jq_row.slideUp();
                    break;
            }
        }
    };

    /**
     * Call back for when there is an error requesting a status change.
     *
     * @param {string} error An error message.
     */
    var onChangedStatusError = function (error) {
        throw error;
    };

    /**
     * Callback for when there is an error requesting the deletable status of a rhythm.
     *
     * @param {string} error An error message.
     */
    var onGetDeletableStatusError = function (error) {
        throw error;
    };

    /**
     * Change the status of a rhythm.
     *
     * @param {string} action 'publish' 'deprecate' or 'delete'.
     * @param {object} jq_row The row identifying which rhythm is having its status changed.
     * @param {object} row_data The data associated with this row.
     *
     * @return void
     */
    var changeStatus = function (action, jq_row, row_data) {
        jq_row.addClass('row-loading');
        var url = '/' + row_data.username + '/rhythm/' + row_data.name + '/' + row_data.version + '/changestatus';
        BabblingBrook.Library.post(
            url,
            {
                action : action
            },
            onChangedStatus.bind(null, action,  jq_row, row_data),
            onChangedStatusError.bind(null, 'Error requesting status change for ' + url)
        );
    };


    /**
     * Displays the users list of rhythms.
     *
     * @returns {undefined}
     */
    var setupList = function () {
        var publish_html = jQuery('#rhythm_list_publish_button_template').html();
        var delete_html = jQuery('#rhythm_list_delete_button_template').html();
        var deprecate_html = jQuery('#rhythm_list_deprecate_button_template').html();
        var actions = [
        {
            name : publish_html,
            class : 'publish',
            onClick : function (event, jq_row, row_data) {
                event.preventDefault();
                var status = jQuery.trim(jq_row.find('.status').text());
                if (status === 'private') {
                    if (jQuery('img.deprecate', jq_row).hasClass('hidden') === true) {
                        var message = 'Are you sure? You will not be able to edit or delete the rhythm once ' +
                            'it has been published (You will be able to edit your rhythm by creating a new version.)';
                        if (!confirm(message)) {
                            return;
                        }
                    }
                }
                changeStatus('publish', jq_row, row_data);
            },
            onReady: function (jq_row, row_data){
                if (row_data.status === 'public') {
                    jQuery('img.publish', jq_row).addClass('hidden');
                }
            }
        },
        {
            name : delete_html,
            class : 'delete',
            onClick : function (event, jq_row, row_data) {
                event.preventDefault();
                var messsage = jQuery('#rhythm_list_delete_button_confirm_template').html();
                if (confirm(messsage) === true) {
                    changeStatus('delete', jq_row, row_data);
                }
            },
            onReady: function (jq_row, row_data){
                if (row_data.status === 'private') {
                    jQuery('img.delete', jq_row).removeClass('hidden');
                }
            }
        },
        {
            name : deprecate_html,
            class : 'deprecate',
            onClick : function (event, jq_row, row_data) {
                event.preventDefault();
                changeStatus('deprecate', jq_row, row_data);
            },
            onReady: function (jq_row, row_data){
                if (row_data.status !== 'public') {
                    jQuery('img.deprecate', jq_row).addClass('hidden');
                }
            }
        }
        ];
        var list_username = jQuery('#users_rhythm_search_container').attr('data-users-username');
        var list_domain = jQuery('#users_rhythm_search_container').attr('data-users-domain');
        if (list_username !== BabblingBrook.Client.User.username || list_domain !== BabblingBrook.Client.User.domain) {
            actions = [];
        }
        var options = {
            show_fields : {
                domain : false,
                date_created : true,
                status : true,
                rhythm_category : true,
                username : false
            },
            initial_values : {
                domain : jQuery('#users_rhythm_search_container').attr('data-users-domain'),
                username : jQuery('#users_rhythm_search_container').attr('data-users-username')
            },
            exact_match : {
                domain : true,
                username : true
            },
            onReady : function () {
                var jq_rows = jQuery('#users_rhythm_search_container>table>tbody>tr');
                jQuery.each(jq_rows, function(index, row){
                    var jq_row = jQuery(row);
                    var username = jQuery('#users_rhythm_search_container').attr('data-users-username');
                    var name = jQuery('.name', jq_row).text();
                    var version = jQuery('.version', jq_row).text();
                    var link = '/' + username + '/rhythm/' + name + '/' + version + '/view';
                    var jq_link = jQuery('<a>');
                    jq_link
                        .attr('href', link)
                        .text(name);
                    jQuery('.name', jq_row).html(jq_link);
                });
                //.removeClass('hide');
                //jq_search.slideDown();
            }
        };
        var search_table = new BabblingBrook.Client.Component.Selector(
            'rhythm',
            'users_rhythm_search',
            jQuery('#users_rhythm_search_container'),
            actions,
            options
        );
    };

    return {

        construct : function () {
            setupList();

            if (window.location.pathname.substring(0, 4) !== '/sky') {
                BabblingBrook.Client.Core.Loaded.setRhythmListLoaded();
            }
        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageRhythm.List.construct();
});