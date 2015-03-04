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
 * @fileOverview Code to automatically apply tags and ajax updating to inputs that are marked as tags.
 * Input field must be classed with 'tag' with an attribute called thing. See lookup table for valid values (tag.thing)
 * Another attribute called thing_id must also be present with the int value of the relevent
 * row of thing that is being tagged.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.ManageStream !== 'object') {
    BabblingBrook.Client.Page.ManageStream = {};
}

BabblingBrook.Client.Page.ManageStream.Tag = (function () {
    'use strict';

    return {

        construct : function () {
            jQuery('input.tag').each(function () {
                if (jQuery(this).attr('thing') === null ||  jQuery(this).attr('thing_id') === null) {
                    return;
                }
                var thing = jQuery(this).attr('thing');
                var thing_id = jQuery(this).attr('thing_id');

                // Insert ajax loading and error elements.
                var id = jQuery(this).attr('id');
                jQuery(this).before('<div id="' + id + '_loading" class="ajax-loading inline-block"></div>');
                jQuery('#' + id + '_loading').addClass('hide');
                jQuery(this).after('<div id="' + id + '_error" class="error"></div>');
                jQuery('#' + id + '_error').addClass('hide');

                // If items deleted quickly then the graphic is removed to early.
                // This is used to check all items have returned.
                var loading_count = 0;

                jQuery(this).tokenInput('/site/tag/getlist', {
                    allowNewValues : true,
                    prePopulateFromInput : true,
                    searchingText : '',
                    hintText : '',
                    deleteFull : true,
                    onNewTag : function (tag) {
                        jQuery('#' + id + '_loading').removeClass('hide');
                        jQuery('#' + id + '_error').addClass('hide');
                        loading_count++;
                        BabblingBrook.Library.post(
                            '/site/tag/insert',
                            'tag=' + tag + '&thing=' + thing + '&thing_id=' + thing_id,
                            function (data) {
                                data = JSON.parse(data);
                                if (data.success !== true) {
                                    jQuery('#' + id + '_error').text(data.success).removeClass('hide');
                                }
                                loading_count--;
                                if (loading_count === 0) {
                                    jQuery('#' + id + '_loading').addClass('hide');
                                }
                            }
                        );
                    },
                    onDeleteTag : function (tag) {
                        jQuery('#' + id + '_loading').removeClass('hide');
                        jQuery('#' + id + '_error').addClass('hide');
                        loading_count++;
                        BabblingBrook.Library.post(
                            '/site/tag/removebyname',
                            'tag=' + tag + '&thing=' + thing + '&thing_id=' + thing_id,
                            function (data) {
                                data = JSON.parse(data);
                                if (data.success !== true) {
                                    jQuery('#' + id + '_error').html(data.success).removeClass('hide');
                                }
                                loading_count--;
                                if (loading_count === 0) {
                                    jQuery('#' + id + '_loading').addClass('hide');
                                }
                            }
                        );
                    }
                });
            });
        }

    };
}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.ManageStream.Tag.construct();
});


