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
 * @fileOverview Displays a slider value for the DisplayPost class.
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Component.ValueSetup !== 'object') {
    BabblingBrook.Client.Component.ValueSetup = {};
};


BabblingBrook.Client.Component.ValueSetup.Slider = function () {


    /**
     * @namespace Displays a slider value field for the DisplayPost class.
     *
     * @param {object} jq_field The div in the template that holds this fields value.
     * @param {number} field_id The id of the field in the stream. This is 0 based. (Use +1 for the post)
     * @param {object} post Standard post object that this value field is a part of.
     * @param {object} stream The stream that the post belongs to.
     *
     * @return void
     */
    BabblingBrook.Client.Component.Value.Slider = function (jq_field, field_id, post, stream) {
        'use strict';
        var type = stream.fields[field_id].value_type;

        /**
         * @type {object} A jQuery reference to the slider inside jq_field.
         */
        var jq_slider;

        /**
         * @type {object} A jQuery reference to the value of the field in an input box.
         */
        var jq_value;

        var value_max;
        var value_min;

        // The total number of negative segments. Each segment is an order of magnitude, eg 1 - 9, or 100 to 999.
        // Only used in logarithmic sliders.
        var neg_segments = 0;
        // The total number of positive segments. Only used in logarithmic sliders.
        var pos_segments = 0;
        // The total number of segments. This is negative segments + positive segments + 1 for zero.
        // Only used in logarithmic sliders.
        var total_segments;
        // The width of each segment in the slider.  Only used in logarithmic sliders.
        var segment_width;
        // The starting point from which to work out the possition of the slider.
        var initial_possition = 0;
        // The number of segments from zero before reaching the segment withthe current value.
        var init_segments;
        // The percentage of the last segment to display.
        var segment_percent;
        // The value of the end of the segment that contains the current value.
        var last_segment_end;
        // fetch from html and remove the plus or minus sign.
        var initial_value;
        // number of pixels in the slider.
        var pixels;
        // The number of pixels that are used for the scale in a linear scale. (excludes the pixels used for zero).
        var working_pixels;
        // How many segments to offset due to a positive minimum or a negative maximum.
        var segment_offset = 0;
        // The total value range.
        var abs_value;

        /**
         * Event for handling a slider sliding.
         *
         * @return void
         */
        var sliderSlide = function (event, ui) {
            // Number of pixels to the right of the start of the slider.
            var value = jq_slider.slider('value');
            // The calculated value of the slider that is usedc for the take.
            var real_value;
            // Not true logarithmic. Uses incremental sections of base 10 to allow an exponentially increasing value.
            if (type === 'logarithmic') {
                // The value of the slider at the stat of the current segment.
                var segment_start_value;
                var end_seg_value, last_seg_pixels, percent_last;
                // Number of segemnts from the left hand side to the start of the current one ( from the left).
                var segment = Math.ceil(value / segment_width);
                if (segment === neg_segments + 1 && neg_segments > 0) {
                    real_value = 0;
                } else if (neg_segments === 0 && segment === 1) {
                    real_value = 0;
                } else if (segment <= neg_segments || segment_offset < 0) {
                    // Work out the value at the end of the current segment.
                    end_seg_value = Math.floor(Math.pow(10, neg_segments - segment + 1 - segment_offset));
                    // How many pixels along in the last segment are we.
                    last_seg_pixels = value - ((segment - 1) * segment_width);
                    // Percentage of the last segment. Reversed, due to negative value.
                    percent_last = 1 - last_seg_pixels / segment_width;
                    segment_start_value = end_seg_value / 10;
                    // percentage of the last segment times by the value of the end of the segment.
                    // Reverse the value due to being negative.
                    real_value = -(Math.ceil(percent_last  * end_seg_value) + segment_start_value);

                } else if (segment > neg_segments + 1 || segment_offset > 0) {
                    // A positive offset for min value. Need a zero space then the start of the offset.
                    if (segment_offset > 0) {
                        // Work out the value at the end of the current segment. Minus 1 for the zero segment.
                        end_seg_value = Math.floor(Math.pow(10, segment_offset + segment - 1));
                    // A zero or negative start min value.
                    } else {
                        // Work out the value at the end of the current segment.
                        end_seg_value = Math.floor(Math.pow(10, pos_segments - (total_segments - segment)));
                    }
                    // How many pixels along in the last segment are we.
                    last_seg_pixels = value - ((segment - 1) * segment_width);
                    // Percentage of the last segment.
                    percent_last = last_seg_pixels / segment_width;
                    segment_start_value = end_seg_value / 10;
                    // Percentage of the last segment times by the value of the end of the segment.
                    real_value =  Math.ceil(percent_last  * end_seg_value) + segment_start_value;
                }
            } else if (type === 'linear') {
                var unit = abs_value / working_pixels;            // The value of one pixel.
                if (value_min > 0) {            // The whole range is positive and starts above zero.
                    if (value <= 10) {        // Check if in the zero space.
                        real_value = 0;
                    } else {
                        real_value = Math.round(value_min + (unit * (value - 10)));
                    }
                } else if (value_max < 0) {    // The whole range is negative and starts below zero.
                    if (value >= working_pixels) {        // Check if in the zero space.
                        real_value = 0;
                    } else {
                        real_value = Math.round(value_min + (unit * value));
                    }
                } else {                    // The whole range is negative and starts below zero.
                    real_value = Math.round(value_min + (unit * value));
                    if (real_value > 0) {                    // Need to account for the zero space.
                        real_value -= Math.round(unit * 10);
                        if (real_value < 0) {
                            real_value = 0;
                        }
                    }
                }

            }
            // Override the start of the slider to be the max negative number. Unless it is reserved as zero space.
            if (value <= 3 && value_min < 0) {
                real_value = value_min;
            }
            // Override the end of the slider to be the max positive number. Unless it is reserved as zero space.
            if (value >= pixels - 3 && value_max > 0) {
                real_value = value_max;
            }
            jq_slider.attr('title', real_value);
        };


        /**
        * Display the slider now that the status has been fetched
        *
        * In a callback because the take value may have needing fetching from the server.
        *
        * @param {string} post The post object that contains this field.
        * @param {number} field_id The id of the field in the post that the status is being set for.
        * @param {object} jq_field The jquery object representing the value field.
        * @param {boolean} [setup=false] Indicates that the initialPossition function should run once we have the status.
        * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
        *
        * @return void
        */
        var statusFetched = function (post, field_id, jq_field, setup, status) {
            var take_value = BabblingBrook.Client.Component.Value.getTakeValue(post, field_id);
            jq_field
                .removeClass('taken waiting untaken paused')
                .addClass(status)
                .attr('title', take_value);
            if (setup === true) {
                setupInitialPossition();
            }
        };

        /**
        * Sets the title and slider status class for a slider value.
        *
        * @param {string} post The post object that contains this field.
        * @param {number} field_id The id of the field in the post that the status is being set for.
        * @param {object} jq_field The jquery object representing the value field.
        * @param {string} status A valid take_status string to assign. If undefined then it is not updated.
        * @param {boolean} [setup=false] Indicates that the initialPossition function should run once we have the status.
        *
        * @return void
        */
        var setStatus = function (post, field_id, jq_field, status, setup) {
            status = BabblingBrook.Client.Component.Value.setAndGetStatus(
                post,
                field_id,
                status,
                statusFetched.bind(null, post, field_id, jq_field, setup)
            );
        };

        /**
         * Callback after an post has successfuly been taken.
         *
         * @param {object} take_data The data returned from a successful take.
         *
         * @return void
         */
        var taken = function (take_data) {
            post.takes[take_data.field_id].value = take_data.value;
            post.takes[take_data.field_id].tmp_take = 0;

            var take_status = 'untaken';
            if (take_data.value.toString() !== '0') {
                take_status = 'taken';
            }

            setStatus(post, field_id, jq_slider, take_status);
        };

        /**
         * Event for handling a slider stoping.
         *
         * @return void
         */
        var sliderStop = function () {
            var real_value = parseInt(jq_slider.attr('title'), 10);

            // Check not already waiting for response from the server and assign status.
            if (jq_slider.hasClass('waiting') === true) {
                return;
            }

            if (typeof post.takes[field_id] === 'undefined') {
                post.takes[field_id] = {};
            }
            post.takes[field_id].tmp_take = real_value;
            var static_tmp_value = real_value;
            setStatus(post, field_id, jq_slider, 'paused');

            setTimeout(function () {
                // If the temp_value has not changed then submit, otherwise abort - the next click will handle it.
                if (static_tmp_value !== post.takes[field_id].tmp_take) {
                    return;
                }
                setStatus(post, field_id, jq_slider, 'waiting');

                BabblingBrook.Client.Core.Interact.postAMessage(
                    {
                        post_id : post.post_id,
                        field_id : field_id,
                        stream_domain : post.stream_domain,
                        stream_username : post.stream_username,
                        stream_name : post.stream_name,
                        stream_version : post.stream_version,
                        value : static_tmp_value,
                        value_type : type,
                        mode : 'new'
                    },
                    'Take',
                    taken,
                    BabblingBrook.Client.Component.Post.TakeError
                );

            }, 1000);
        };

        /**
         * Setup the original possition of the slider.
         *
         * @param {object} Jquery object representing the slider.
         *
         * @return void
         */
        var setupInitialPossition = function () {
            initial_value = jq_slider.attr('title');
            if (initial_value === '') {
                initial_value = 0;
            } else {
                initial_value = parseInt(initial_value, 10);
            }       field_id
            pixels = parseInt(jq_slider.width(), 10);

            working_pixels = pixels - 10;

            if (type === 'logarithmic') {
                if (value_min < 0) {
                    // Total negative segments counted from zero.
                    neg_segments = String(Math.abs(value_min)).length - 1;
                }
                if (value_max > 0) {
                    // Total positive segments counted from zero.
                    pos_segments = String(Math.abs(value_max)).length - 1;
                }
                if (value_min > 0) {
                    // calculate the offset due to a positive minimum.
                    segment_offset = String(Math.abs(value_min)).length - 1;
                    // Remove positive segments to account for a possitive minimum.
                    pos_segments -= String(Math.abs(value_min)).length - 1;
                }
                if (value_max < 0) {
                    // calculate the offset due to a negative maximum. minus 2 to accoutn for the minus sign.
                    segment_offset = -String(Math.abs(value_max)).length + 1;
                    // Remove negative segments to account for a negative maximum.
                    neg_segments += segment_offset;
                }
                // extra one for the zero value.
                total_segments = neg_segments + pos_segments + 1;
                segment_width = (pixels / total_segments);
            }

            // Work out initial position.
            if (type === 'logarithmic') {
                if (initial_value === 0) {
                    if (segment_offset > 0 || neg_segments === 0) {
                        initial_possition = 0;
                    } else if (segment_offset > 0) {
                        initial_possition = pixels;
                    } else {
                        initial_possition = pixels * (neg_segments / total_segments) + 5;
                    }
                // Take to zero and count backwards.
                } else if (initial_value < 0) {
                    // take to start of negative scale.
                    initial_possition = segment_width * neg_segments;
                    // How many segments to the segment containing the current value.
                    init_segments = String(Math.abs(initial_value)).length - 1;
                    // count back to start of the current segment.
                    initial_possition -= (init_segments + segment_offset) * segment_width;
                    // Work out the value of the end of the segment that contains the current value.
                    last_segment_end = Math.pow(10, init_segments + 1);
                    // What percentage of the final segment should be used.
                    segment_percent = Math.abs(initial_value) / last_segment_end;
                    // move back to the place in the current segment.
                    initial_possition -= segment_percent * segment_width;
                } else {
                    // Start of positive scale ( + 1 to account for zero).
                    initial_possition = segment_width * (neg_segments + 1);
                    // How many segments to the segment containing the current value.
                    init_segments = parseInt(String(Math.abs(initial_value)).length - 1, 10);
                    init_segments -= segment_offset;
                    // count forward to start of the current segment.
                    initial_possition += init_segments * segment_width;
                    // Work out the value of the end of the segment that contains the current value.
                    last_segment_end = Math.pow(10, init_segments + 1 + segment_offset);
                    // What percentage of the final segment should be used.
                    segment_percent = Math.abs(initial_value) / last_segment_end;
                    // move back to the place in the current segment.
                    initial_possition += segment_percent * segment_width;
                }
            } else {
                // Linear.
                // The whole range is positive and starts above zero.
                if (value_min > 0) {
                    abs_value = value_max - value_min;
                // The whole range is negative and starts below zero.
                } else if (value_max < 0) {
                    abs_value = (Math.abs(value_min) - Math.abs(value_max));
                // The range is negative to positive (or starts/ends at zero).
                } else {
                    abs_value = (Math.abs(value_max) + Math.abs(value_min));
                }
                var pixels_per_value = working_pixels / abs_value;
                // Take real min away as it is a positive offset.
                if (value_min > 0) {
                    initial_possition = (initial_value * pixels_per_value) - (value_min * pixels_per_value);
                // Add real min as it needs to be accounted for before the positive values.
                } else {
                    initial_possition = (Math.abs(value_min) * pixels_per_value) + initial_value * pixels_per_value;
                }
                // Add the zero space if the value is above zero.
                if (initial_value > 0) {
                    initial_possition += 10;
                }
                // If at zero then place in the middle of the zero space.
                if (initial_value === 0) {
                    initial_possition += 5;
                }
            }

            // Make the slider.
            jq_slider.slider(
                {
                    max : pixels,
                    min : 0,        // This is the number of pixels that are used for the slider, not the value.
                    value : initial_possition,
                    slide : sliderSlide,
                    stop : function () {
                        sliderSlide();
                        sliderStop();
                    }
                }
            );
        };

        /**
         * Setsup the max and min values of the field.
         *
         * Uses the customised version if it is in the post, therwise falls back on the stream version.
         *
         * @return void
         */
        var setupFieldMaxAndMinValues = function () {
            var field = post.content[field_id];
            if (typeof field !== 'undefined') {
                if (typeof field.value_max !== 'undefined') {
                    value_max = field.value_max;
                }
                if (typeof field.value_min !== 'undefined') {
                    value_min = field.value_min;
                }
            }
            if (typeof value_max === 'undefined') {
                value_max = stream.fields[field_id].value_max;
            }
            if (typeof value_min === 'undefined') {
                value_min = stream.fields[field_id].value_min;
            }
            value_max = parseInt(value_max, 10);
            value_min = parseInt(value_min, 10);
        };

        /**
         * Constructor
         *
         * @return void
         * @refactor Most of this could be in a generic version in the DisplayValue object
         */
        var setup = function () {
            var jq_slider_template;
            if (type === 'linear') {
                jq_slider_template = jQuery('#linear_slider_value_template').clone();
            } else if (type === 'logarithmic') {
                jq_slider_template = jQuery('#logarithmic_slider_value_template').clone();
            }
            jq_slider = jQuery('>.slider', jq_slider_template);
            jq_slider.attr('data-field-id', field_id.toString());
            var field_class_names = jq_field.attr('class');
            var slider_class_names = jq_slider.attr('class');
            jq_slider.attr('class', field_class_names + ' ' + slider_class_names);
            jq_field.replaceWith(jq_slider);
            jq_slider = jQuery('>.' + type, jq_slider);
            // Needs to be in a settimeout due to some kind of race condition after the slider is inserted.
            // Only effects some browsers (Chrome, Safari)
            setTimeout(function() {
                setupFieldMaxAndMinValues();
                setStatus(post, field_id, jq_slider, undefined, true);
                // initialPossition runs when the status has been fetched.
            }, 1);
        };
        setup();
    };
};