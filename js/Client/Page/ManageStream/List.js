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
 * @fileOverview Javascript used on the Stream list page.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.ManageStream !== 'object') {
    BabblingBrook.Client.Page.ManageStream = {};
}

/**
 * @namespace Displays a list of streams owned by a user.
 * @package JS_Client
 */
BabblingBrook.Client.Page.ManageStream.List = (function () {
    'use strict';

    /**
     * Call back for after the server has responded to a request to chae a streams status.
     *
     * @param {string} action 'publish' 'deprecate' or 'delete'.
     * @param {object} jq_row The row identifying which stream is having its status changed.
     * @param {object} row_data The data associated with this row.
     * @param {object} response_data The data sent back from the server
     *      after the request to change the status of a stream.
     * @param {boolean} response_data.deletable Is the stream deletable or not.
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
            var jq_revert = jQuery('img.revert', jq_row);
            if (response_data.deletable === true) {
                jq_revert.removeClass('hidden');
                jq_delete.removeClass('hidden');
            } else {
                jq_revert.addClass('hidden');
                jq_delete.addClass('hidden');
            }
            switch (action) {
                case 'publish':
                    jq_publish.addClass('hidden');
                    jq_deprecate.removeClass('hidden');
                    jQuery('.status', jq_row).text('public');
                    break;

                case 'deprecate':
                    jq_publish.removeClass('hidden');
                    jq_deprecate.addClass('hidden');
                    jQuery('.status', jq_row).text('deprecated');
                    break;

                case 'revert':
                    jq_publish.removeClass('hidden');
                    jq_revert.addClass('hidden');
                    jq_deprecate.addClass('hidden');
                    jQuery('.status', jq_row).text('private');
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
     * Callback for when there is an error requesting the deletable status of a stream.
     *
     * @param {string} error An error message.
     */
    var onGetDeletableStatusError = function (error) {
        throw error;
    };

    /**
     * Change the status of a stream.
     *
     * @param {string} action 'publish' 'deprecate' or 'delete'.
     * @param {object} jq_row The row identifying which stream is having its status changed.
     * @param {object} row_data The data associated with this row.
     *
     * @return void
     */
    var changeStatus = function (action, jq_row, row_data) {
        jq_row.addClass('row-loading');
        var url = '/' + row_data.username + '/stream/' + row_data.name + '/' + row_data.version + '/changestatus';
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
     * Setup the stream search table forthis user.
     *
     * @returns {undefined}
     */
    var setupUsersStreamSearch = function () {
        var publish_html = jQuery('#stream_list_publish_button_template').html();
        var delete_html = jQuery('#stream_list_delete_button_template').html();
        var deprecate_html = jQuery('#stream_list_deprecate_button_template').html();
        var revert_html = jQuery('#stream_list_revert_button_template').html();
        var actions = [
        {
            name : revert_html,
            class : 'revert',
            onClick : function (event, jq_row, row_data) {
                event.preventDefault();
                changeStatus('revert', jq_row, row_data);
            }
            // onReady is handled in the delete button.
        },
        {
            name : publish_html,
            onClick : function (event, jq_row, row_data) {
                event.preventDefault();
                var status = jQuery.trim(jq_row.find('.status').text());
                if (status === 'private') {
                    var message = 'Are you sure? You will not be able to edit the stream once a ' +
                       'user has made a post in your stream editing the stream. (You will be able to edit your ' +
                       'stream by creating a new version.)' ;
                    if (!confirm(message)) {
                        return;
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
                var messsage = jQuery('#stream_list_delete_button_confirm_template').html();
                if (confirm(messsage) === true) {
                    changeStatus('delete', jq_row, row_data);
                }
            },
            onReady: function (jq_row, row_data){
                if (row_data.status === 'private') {
                    jQuery('img.delete', jq_row).removeClass('hidden');
                } else {
                    var url = '/' + row_data.username + '/stream/' +
                    row_data.name + '/' + row_data.version + '/getdeletablestatus';
                    BabblingBrook.Library.post(
                        url,
                        {},
                        function (response_data) {
                            if (response_data.success === false) {
                                onGetDeletableStatusError(
                                    'Server failed to fetch deletable status of a stream : ' + url
                                );
                            } else {
                                if (response_data.deletable === true) {
                                    jQuery('img.delete', jq_row).removeClass('hidden');
                                    if (row_data.status !== 'private') {
                                        jQuery('img.revert', jq_row).removeClass('hidden');
                                    }
                                }
                            }
                        },
                        onGetDeletableStatusError.bind(null, 'Error fetching deletable status for ' + url)
                    );
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
        var list_username = jQuery('#users_stream_search_container').attr('data-users-username');
        var list_domain = jQuery('#users_stream_search_container').attr('data-users-domain');
        if (list_username !== BabblingBrook.Client.User.username || list_domain !== BabblingBrook.Client.User.domain) {
            actions = [];
        }
        var options = {
            show_fields : {
                domain : false,
                date_created : true,
                status : true,
                stream_kind : true,
                username : false
            },
            initial_values : {
                domain : list_domain,
                username : list_username
            },
            exact_match : {
                domain : true,
                username : true
            },
            onReady : function () {
                var jq_rows = jQuery('#users_stream_search_container>table>tbody>tr');
                jQuery.each(jq_rows, function(index, row){
                    var jq_row = jQuery(row);
                    var username = jQuery('#users_stream_search_container').attr('data-users-username');
                    var name = jQuery('.name', jq_row).text();
                    var version = jQuery('.version', jq_row).text();
                    var link = '/' + username + '/stream/' + name + '/' + version + '/view';
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
            'stream',
            'users_stream_search',
            jQuery('#users_stream_search_container'),
            actions,
            options
        );
    };

    return {

        construct : function () {
            setupUsersStreamSearch();
        }

    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageStream.List.construct();
});