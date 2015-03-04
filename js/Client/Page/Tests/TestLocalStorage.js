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
 * @fileOverview Javascript used for managing the testing cookie - that enables test stream rhythms etc to be fetched
 * and displayed.
 *
 * @author Sky Wickenden
 */

if (typeof BabblingBrook.Client.Page.Tests !== 'object') {
    BabblingBrook.Client.Page.Tests = {};
}

/**
 * Javascript used for managing the testing cookie - that enables test stream rhythms etc to be fetched
 * and displayed.
 *
 * @package JS_Client
 */
BabblingBrook.Client.Page.Tests.TestLocalStorage = (function () {
    'use strict';

    var test_data = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus placerat suscipit ' +
        'tincidunt. Ut arcu erat, efficitur et fringilla nec, imperdiet at libero. Sed pretium scelerisque ' +
        'pellentesque. Nunc ac malesuada eros. Vivamus non magna aliquet, blandit orci at, blandit purus. ' +
        'Morbi maximus lacus convallis, rhoncus lectus sed, mollis ante. Quisque aliquet sit amet ante at ' +
        'posuere. Nullam malesuada nulla ut est efficitur tincidunt auctor sit amet ligula. Etiam convallis, ' +
        'neque dignissim gravida maximus, tellus purus placerat urna, a laoreet mi felis quis ante. Nam eros ' +
        'sapien, iaculis in elementum et, viverra at diam. Sed varius purus sit amet auctor efficitur. ' +
        'Nam dignissim porta dignissim. Fusce maximus purus eget erat lobortis aliquet in in enim';

     var rows_entered;

    var testLocalStorage = function () {
        jQuery('#test_results').append('<p>Attempting to add an extra element.</p>');
        BabblingBrook.LocalStorage.store('test_extra_key', test_data);
        jQuery('#test_results').append('<p>Local storage size is now : ' + localStorage.length + '</p>');

        // The first quater of local storage results should have been removed.
        var quater = Math.floor(rows_entered / 4);
        var localstorage_length = localStorage.length;
        for (var i=0; i < localstorage_length; i++) {
            var raw_item = localStorage.getItem(localStorage.key(i));
            var item = JSON.parse(raw_item);
            if (item['t'] < quater) {
                jQuery('#test_results').append(
                    '<p class="error">An item that should have been deleted has not been : '
                        + localStorage.key(i) + ' </p>'
                );
            }
        }
        jQuery('#test_results').append('<p>Test complete.</p>');
        jQuery('#test_results').removeClass('block-loading');
    };

    var fillLocalStorage = function () {
        localStorage.clear();
        jQuery('#test_results').append('<p>Cleared local storage.</p>');
        var items_entered = 0;
        var total_size_odf_items = 0;

        var timestamp, container, string_data;
        var full = false;
        timestamp = Math.round(new Date().getTime() / 1000);
        for (var count = 0; count < 100000; count++) {
            container = {
                d : test_data,
                t : count
            }
            string_data = window.JSON.stringify(container);
            try {
                localStorage.setItem('test_data_' + count, string_data);
            } catch (exception) {
                console.log('local storage full : ' + exception);
                jQuery('#fill_results').text('localStorage filled with test data. Total rows : ' + count);
                full = true;
                rows_entered = count;
                break;
            }
            if (full === false) {
                if (count % 100 === 0) {
                    jQuery('#fill_results').text('filling : rows so far : ' + count);
                }
            }
        }
        jQuery('#test_results').append('<p>Filled local storage with test data. rowcount : ' + count + '</p>');
    };

    return {
        construct : function () {
            jQuery('#test_local_storage').click(function () {
                jQuery('#test_results').addClass('block-loading');
                jQuery('#test_results').append('<p>Starting test...</p>');
                setTimeout(function () {
                    fillLocalStorage()
                    testLocalStorage()
                }, 100);
            });

        }
    };

}());

jQuery(function () {
    'use strict';
    BabblingBrook.Client.Page.Tests.TestLocalStorage.construct();
});