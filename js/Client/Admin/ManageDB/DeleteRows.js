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
 * @fileOverview Code associated with the deleting of datbase rows.
 * @author Sky Wickenden
 */

BabblingBrook.Client.Admin.DeleteRows = (function () {
    'use strict';

    var seconds_to_count = 3;

    var seconds_left = seconds_to_count;

    // Used to prevent double clicks causing multiple settimouts; resulting in the  timmer going too fast.
    var timmer_id = 0;

    var displaySecondsLeft = function (current_timmer_id) {
        jQuery('#confirm_seconds').text(seconds_left);
        setTimeout(function () {
            if (timmer_id !== current_timmer_id) {
                return;
            }

            // Encloding in an if prevents double clicks causing values below 0 being displayed.
            if (seconds_left > 0) {
                seconds_left--;
            }
            jQuery('#confirm_seconds').text(seconds_left);
            if (seconds_left <= 0) {
                jQuery('#confirm_delete_row').attr('disabled', false);
            } else {
                displaySecondsLeft(current_timmer_id);
            }
        }, 1000);
    };

    var onDeleteRowsClicked = function () {
        var jq_this = jQuery(this);
        jQuery('#confirm_delete_row').attr('disabled', true);
        var qualifier = jq_this.parent().find('input').val();
        jQuery('#confirm_delete_row').data({
            qualifier : qualifier,
            url : jq_this.attr('data-url')
        });

        jQuery('#confirmation_container').removeClass('hide');
        seconds_left = seconds_to_count;
        jQuery('#confirm_description').text(jq_this.parent().parent().find('label').text());
        timmer_id++;
        displaySecondsLeft(timmer_id);
    };

    var onDeletedRows = function (response_data) {
        console.debug(response_data);
    };

    var onDeleteConfirmed = function() {
        BabblingBrook.Library.post(
            jQuery('#confirm_delete_row').data('url'),
            {
                qualifier : jQuery('#confirm_delete_row').data('qualifier')
            },
            onDeletedRows
        );
    };

    return {

        construct : function () {
            jQuery('.delete-rows').click(onDeleteRowsClicked);
            jQuery('#confirm_delete_row').click(onDeleteConfirmed);
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Admin.DeleteRows.construct();
});


