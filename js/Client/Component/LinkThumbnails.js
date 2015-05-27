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
 * @fileOverview Handles the generation of link thumbnails for new posts.
 * @author Sky Wickenden
 */

/*
 * @namespace
 * @package JS_Client
 *
 * Help icons should have the following format:
 * <div id="help_<unique_name>" title="Title when hovering over the help icon" class="help-icon">
 *     <span id="help_title_<unique_name>" class="help-title hide">The help title</span>
 *     <span id="help_content_<unique_name>" class="help-content hide">The help content</span>
 * </div>
 *
 */
BabblingBrook.Client.Component.LinkThumbnails = (function () {
    'use strict';

    var post_form_count = -1;
    var post_forms = [];

    var small_thumbnail_width = 107;
    var small_thumbnail_height = 80;
    var medium_thumbnail_width = 333;
    var medium_thumbnail_height = 250;
    var large_thumbnail_width = 500;
    var large_thumbnail_height = 375;

    // The number of pixels, below which an image is considered to be small.
    var small_width = 150;
    var small_height = 150;

    var too_small_width = 15;
    var too_small_height = 15;

    var thumbs_per_page = 5;

    /**
     * Callback  for when YQL has returned from scraping the url.
     *
     * @param {integer} post_row The row of the post that thumbs are being fetched for.
     * @param {yql_data} yql_data The data that has been returned from YQL.
     *
     * @return {void}
     */
    var onFetchedYQLThumbnails = function (post_row, yql_data) {
        jQuery('.thumbnails-loading', post_row.jq_location).addClass('hide');
        jQuery('.all-thumbs', post_row.jq_location).removeClass('hide');

//
//            noThumbsFound(post_row, yql_data.error);
//            return;
//        }
//        if () {
//            fetchThumbs(post_row, false);
//            return;
//        }

        var urls  = [];
        if (typeof post_row.original_url !== 'undefined') {
            urls.push(post_row.original_url);
        }
        urls.push(post_row.url);

        if (typeof yql_data.error !== 'object' && yql_data.query.results !== null) {
            if (typeof yql_data.query.results.meta !== 'undefined') {
                if (BabblingBrook.Library.isArray(yql_data.query.results.meta) === false) {
                    if (typeof yql_data.query.results.meta.content === 'string') {
                        urls.push(yql_data.query.results.meta.content);
                    }
                } else {
                    for(var row in yql_data.query.results.meta) {
                        if (typeof row.content === 'string') {
                            urls.push(row.content);
                        }
                    }
                }
            }
            if (typeof yql_data.query.results.img !== 'undefined') {
                if (BabblingBrook.Library.isArray(yql_data.query.results.img) === false) {
                    if (typeof yql_data.query.results.img.src === 'string') {
                        urls.push(yql_data.query.results.meta.src);
                    }
                } else {
                    for(var i in yql_data.query.results.img) {
                        if (typeof yql_data.query.results.img[i].src === 'string') {
                            urls.push(yql_data.query.results.img[i].src);
                        }
                    }
                }
            }
        }
        post_row.all_thumb_urls = urls;
        fetchThumbs(post_row, false);
    };

    var noThumbsFound = function (post_row, error) {
        jQuery('.no-images-found', post_row.jq_location).removeClass('hide');
        post_row.selected = null;
        post_row.onSelected(post_row.selected);
    };

    var convertImageToCroppedCanvas = function (image, thumb_width, thumb_height) {
        // Work out if we need to crop vertically or horizontally.
        var aspect = thumb_width / thumb_height;
        var source_x = 0;
        var source_y = 0;
        var source_width = image.width;
        var source_height = image.height;
        var scale;
        if (image.width > image.height * aspect) {
            // Use the full height, crop the width.
            scale = image.height / thumb_height;
            source_width = thumb_width * scale;
            source_x = (image.width / 2) - (source_width / 2);
        } else {
            // Use the full width, crop the height.
            scale = image.width / thumb_width;
            source_height = thumb_height * scale;
            source_y = (image.height / 2) - (source_height / 2);
        }

        var canvas = document.createElement("canvas");
        canvas.width = thumb_width;
        canvas.height = thumb_height;
        var context = canvas.getContext("2d");
        context.drawImage(
            image,
            source_x,
            source_y,
            source_width,
            source_height,
            0,
            0,
            thumb_width,
            thumb_height
        );
        return canvas;
    };

    var createCroppedImage = function (image) {
        var canvas = convertImageToCanvas(image);

    };

    var fetchThumbs = function (post_row, show_back) {
        post_row.current_total_images = 0;
        post_row.thumbs_showing = 0;
        post_row.all_images_requested === false;
        if (show_back === true) {
            var jq_back_button = jQuery('#link_thumbnail_back_button_template').html();
            jQuery('.all-thumbs', post_row.jq_location).append(jq_back_button);
            jQuery('.back-thumbs', post_row.jq_location).click(onBackClicked.bind(null, post_row));
        }
        for (var i = 0; i < thumbs_per_page; i++) {
            fetchThumb(post_row);
        }
        post_row.all_images_requested = true;
    };

    var fetchThumb = function (post_row) {
        if (post_row.current_all_index >= post_row.all_thumb_urls.length) {
            fetchSmallThumb(post_row);
            return;
        }
        post_row.current_total_images++;
        var img = new Image();

        post_row.all_images[post_row.current_all_index] = img;
        img.setAttribute('crossOrigin', 'anonymous');

        var url = post_row.all_thumb_urls[post_row.current_all_index];
        if (url.substring(0, 5) === 'https') {
            url = 'http' + url.substring(5);
        }

        img.onload = displayLinkThumbnail.bind(null, post_row, post_row.current_all_index, false);
        img.onerror = onImageError.bind(null, url);
        img.src = url;

        post_row.current_all_index++;
    };

    var fetchSmallThumb = function (post_row) {
        if (post_row.current_small_index >= post_row.small_image_ids.length) {
            return;
        }

        post_row.current_total_images++;
        var all_index = post_row.small_image_ids[post_row.current_small_index];
        var img = new Image();
        post_row.all_images[all_index] = img;
        img.setAttribute('crossOrigin', 'anonymous');
        img.onload = displayLinkThumbnail.bind(null, post_row, all_index, true);
        img.onerror = onImageError.bind(null, post_row.all_thumb_urls[post_row.current_all_index]);
        img.src = post_row.all_thumb_urls[all_index];
    };

    var onImageError = function (url) {
        console.log('image failed to load ' + url);
    }

    var displayLinkThumbnail = function (post_row, current_index, small) {
        // Push small images into a seperate list to be displayed after the large images.
        // When small is true then the large images have all been displayed.
        // Discard images that are too small.
        if (small === false
            && (post_row.all_images[current_index].naturalHeight < small_height
            || post_row.all_images[current_index].naturalWidth < small_width)
        ) {
            if (post_row.all_images[current_index].naturalHeight > too_small_height
                && post_row.all_images[current_index].naturalWidth > too_small_width
            ) {
                post_row.small_image_ids.push(current_index);
                fetchThumb(post_row);
            }
        } else {
            var jq_thumb = post_row.jq_thumb_template.clone();
            jq_thumb.attr('data-id', current_index);
            var canvas = convertImageToCroppedCanvas(
                post_row.all_images[current_index],
                small_thumbnail_width,
                small_thumbnail_height
            );
            var jq_canvas = jQuery(canvas);
            jq_canvas.addClass('thumb-location');
            jQuery('.thumb-location', jq_thumb).replaceWith(jq_canvas);
            jq_canvas.click(onThumbSelected.bind(null, post_row, current_index));
            jQuery('.all-thumbs', post_row.jq_location).append(jq_thumb);

            if (post_row.selected === null) {
                post_row.selected  = current_index;
                jQuery('.default_thumnail', post_row.jq_location).removeClass('selected-thumb');
                selectThumbnail(post_row);
            }
            if (current_index === post_row.selected) {
                jq_thumb.addClass('selected-thumb');
            }

            post_row.thumbs_showing++;

        }


        // Called when the last thumb is shown.
        if (post_row.thumbs_showing === thumbs_per_page) {
            if (post_row.current_all_index < post_row.all_thumb_urls.length
                || post_row.current_small_index < post_row.small_image_ids.length
            ) {
                var jq_more_button = jQuery('#link_thumbnail_more_button_template').html();
                jQuery('.all-thumbs', post_row.jq_location).append(jq_more_button);
                jQuery('.more-thumbs', post_row.jq_location).click(onMoreClicked.bind(null, post_row));
            }
        }

    };

    /**
     * Callback for when a thumbnail is selected.
     *
     * @param {type} post_row Contains details of the current thumbnail request.
     * @param {type} select_index The index of the selected thumbnail
     * @returns {undefined}
     */
    var onThumbSelected = function (post_row, select_index) {
        post_row.selected = select_index;
        jQuery('.thumbnail-container', post_row.jq_location).removeClass('selected-thumb');
        jQuery('.default_thumnail', post_row.jq_location).removeClass('selected-thumb');
        post_row.jq_location.find('[data-id="' + select_index + '"]').addClass('selected-thumb');
        selectThumbnail(post_row);
    };


    var convertImageToProportionalCanvas = function (image, thumb_height) {
        // Work out if we need to crop vertically or horizontally.
        var scale;
        scale = image.height / thumb_height;
        var thumb_width = image.width / scale;

        var canvas = document.createElement("canvas");
        canvas.width = thumb_width;
        canvas.height = thumb_height;
        var context = canvas.getContext("2d");
        context.drawImage(
            image,
            0,
            0,
            thumb_width,
            thumb_height
        );
        return canvas;
    };

    /**
     * Calls the callback to select a thumbnail.
     *
     * Sends three base64 thumnbails with the callback as defined by the Babbling Brook protocol.
     * See the addRow function for details.
     *
     * @param {object} post_row Contains details of the current thumbnail request.
     *
     * @returns {void}
     */
    var selectThumbnail = function (post_row) {

        var image = post_row.all_images[post_row.selected];
        var small_canvas = convertImageToCroppedCanvas(image, small_thumbnail_width, small_thumbnail_height);
        var large_canvas = convertImageToCroppedCanvas(image, large_thumbnail_width, large_thumbnail_height);
        var proportional_large_canvas = convertImageToProportionalCanvas(image, large_thumbnail_height);

        var small_base64 = small_canvas.toDataURL("image/png");
        small_base64 = small_base64.substring(small_base64.indexOf(','));
        var large_base64 = large_canvas.toDataURL("image/png");
        large_base64 = large_base64.substring(large_base64.indexOf(','));
        var proportional_large_base64 = proportional_large_canvas.toDataURL("image/png");
        proportional_large_base64 = proportional_large_base64.substring(proportional_large_base64.indexOf(','));

        var url = post_row.all_thumb_urls[post_row.selected];
        if (url.substring(0,5) === 'data:') {
            url = 'data';
        }

        post_row.onSelected(
            post_row.row_id,
            url,
            small_base64,
            large_base64,
            proportional_large_base64
        );
    }

    var onBackClicked = function (post_row) {
        post_row.current_all_index = 0;
        post_row.current_small_index = 0;
        post_row.thumbs_showing = 0;
        jQuery('.all-thumbs', post_row.jq_location).empty();
        fetchThumbs(post_row, false);
    };

    var onMoreClicked = function (post_row) {
        jQuery('.all-thumbs', post_row.jq_location).empty();
        fetchThumbs(post_row, true);
    };

    return {

        create : function () {
            post_form_count++;
            post_forms[post_form_count] = {};
            return post_form_count;
        },

        /**
         * Adds a field row into a form.
         *
         * @param {string} post_form_id The id of the make post form.
         * @param {integer} row_id The id of the row in the post.
         * @param {string} url The url of the link that thumbnails need fetching for.
         * @param {object} jq_location A jQuery object pointing to the location to display the thumbnails.
         * @param {string} jq_thumb_template A clone of the templat to be used to display the thumbnails.
         *      Must have an <img class='thumb-location' /> element.
         * @param {string} onSelected A callback for when a new thumbnail is selected.
         *      Three base64 strings of the thumbmail are passed as paramaters as defined by the
         *      Babbling Brook protocol. 107 * 80, 333 * 250, 666 * 500
         * @param {string} [original_url] If this is an edit then this is the url to the original thumbnail.
         *      (large version)
         *
         * @return
         */
        addRow : function (post_form_id, row_id, url, jq_location, jq_thumb_template, onSelected, original_url) {
            post_forms[post_form_id][row_id] = {
                row_id : row_id,
                url : url,
                jq_location : jq_location, // The location for the thumbs to be displayed
                                        // Must contain an element with the class 'all-thumbs'. where the thumb locations will be showm.
                                        // Must contain an element with a class 'more-thumbs hide'. Where the more link is.
                jq_thumb_template : jq_thumb_template, // The template to be used to display each thumb.
                all_thumb_urls : [],
                all_images : [],
                small_image_ids : [], // a list of ids, pointing to the all_thumb_urls that are small.
                currently_showing : [], // An array of all_thumb_urls that are currently being displayed.
                current_all_index : 0,  // The index in all_thumb_urls for currently shown urls.
                current_small_index : 0,  // The current index of the small_images that have been shown.
                current_total_images : 0, // The total number of images that are being shown in this row.
                all_images_requested : false, // Have all the images in this row been requested (is current_total_images set)
                thumbs_showing : 0,      // An count of the number of thumbs currently showing.
                selected : null,         // index from all_images of the currently selected image.
                onSelected : onSelected,  // A callback for when an image has been selected.
                original_url : original_url
            };

            jQuery('.thumbnails-loading', jq_location).removeClass('hide');
            jQuery('.link-thumbnail-initial_message', jq_location).addClass('hide');

            jQuery('.link-thumbnail-container .all-thumbs', jq_location).empty();
            jQuery('.link-thumbnail-container .more-thumbs', jq_location).addClass('hide');
            jQuery('.link-thumbnail-container .back-thumbs', jq_location).addClass('hide');
            var post_row = post_forms[post_form_id][row_id];

            // Scape the page using yahoo. See :
            // http://www.s-anand.net/blog/client-side-scraping/
            // http://developer.yahoo.com/yql/
            $.getJSON(
                'http://query.yahooapis.com/v1/public/yql?callback=?',
                {
                    q: 'select * from html where url="' + url + '"' +
                        ' AND compat="html5"' +
                        ' AND (' +
                        ' xpath="//img" OR' +
                        ' xpath="//meta[contains(@property,\'og:image\')]" OR' +
                        ' xpath="//img[contains(@property,\'twitter:image\')]" OR' +
                        ' xpath="//img[contains(@property,\'shareaholic:image\')]"' +
                        ' )',
                    format: 'json'
                },
                onFetchedYQLThumbnails.bind(null, post_row)
            );

            jQuery('.default_thumnail', jq_location).click(function () {
                post_row.selected = null;
                jQuery('.default_thumnail', jq_location).addClass('selected-thumb');
                jQuery('.thumbnail-container', jq_location).removeClass('selected-thumb');
                post_row.onSelected(
                    post_row.row_id
                );
            });
        },

        /**
         * removes cached images for an post that is being made.
         *
         * @param {integer} create_id The post_form_count id assigned to the post form that is being cleared.
         *
         * @returns {void}
         */
        clearup : function (create_id) {
            post_forms[create_id] = undefined;
        }
    };
}());