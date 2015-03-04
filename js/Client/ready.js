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
 * @fileOverview The client ready function.
 * @author Sky Wickenden
 *
 * Note: Some client javascript is generated and loaded inline.
 * See the main.php ciew template for details.
 */

jQuery(function () {
    'use strict';

    // Ensure that these urls are redirected as they should not be accessed once logged in.
    if (window.location.protocol === 'https:') {
        var url = window.location.href.replace('https:', 'http:').toLowerCase();
        if (window.location.pathname.substr(0, 11) === '/site/login'
            || window.location.pathname.substr(0, 14) === '/site/password'
            || window.location.pathname.substr(0, 12) === '/site/signup'
            || window.location.pathname.substr(0, 19) === '/site/passwordreset'
        ) {
            url = 'http://' + window.location.hostname + '/';
        }
        jQuery('#content_page').empty();
        window.location = url;
    }

    // Jquery UI focus event seems a little buggy.
    // this ensures that when a dialogue is clicked on it goes to the top.
    jQuery('body').on('click', '.ui-dialog' , function(){
        jQuery('div').removeClass('top-dialogue');
        if (jQuery(this).hasClass('ui-dialog') === true) {
            jQuery(this).addClass('top-dialogue');
        } else {
            jQuery(this).parents('.ui-dialog').addClass('top-dialogue');
        }
    });

    // Ajaxurl setup
    if(BabblingBrook.Client.ClientConfig.ajaxurl === true) {
        BabblingBrook.Client.Core.Ajaxurl.registerGlobalClickEvent();
        BabblingBrook.Client.Core.Loaded.onUserLoaded(function () {
            BabblingBrook.Client.Core.Ajaxurl.setup(
                {
                    host: window.location.host
                },
                jQuery('body')
            );
        });
    }

    BabblingBrook.Client.Core.FeatureSwitches.construct();

    window.addEventListener('message', BabblingBrook.Client.Core.Interact.receiveMessage, false);
    BabblingBrook.Client.Core.Interact.construct();

    BabblingBrook.Client.Core.Streams.setup();

    BabblingBrook.Client.Core.UserSetup.construct();

    BabblingBrook.Client.Core.Loaded.setClientLoaded();

    if (typeof BabblingBrook.Client.Component.Value.construct === 'function') {
        BabblingBrook.Client.Component.Value.construct();
    }

    if (typeof BabblingBrook.Client.Component.BugTree.construct === 'function') {
        BabblingBrook.Client.Component.BugTree.construct();
    }
    if (typeof BabblingBrook.Client.Component.EditVersionSelect.construct === 'function') {
        BabblingBrook.Client.Component.EditVersionSelect.construct();
    }
    if (typeof BabblingBrook.Client.Component.Help.construct === 'function') {
        BabblingBrook.Client.Component.Help.construct();
    }

    if (typeof BabblingBrook.Client.Component.PostRings.construct === 'function') {
        BabblingBrook.Client.Component.PostRings.construct();
    }
    if (typeof BabblingBrook.Client.Component.PostsWaiting.construct === 'function') {
        BabblingBrook.Client.Component.PostsWaiting.construct();
    }
    if (typeof BabblingBrook.Client.Component.ReportBug.construct === 'function') {
        BabblingBrook.Client.Component.ReportBug.construct();
    }
    if (typeof BabblingBrook.Client.Component.Resize.construct === 'function') {
        BabblingBrook.Client.Component.Resize.construct();
    }
    if (typeof BabblingBrook.Client.Component.SuggestionMessage.construct === 'function') {
        BabblingBrook.Client.Component.SuggestionMessage.construct();
    }
    if (typeof BabblingBrook.Client.Component.Tutorial.construct === 'function') {
        BabblingBrook.Client.Component.Tutorial.construct();
    }
    if (typeof BabblingBrook.Client.Component.Messages.setup === 'function') {
        BabblingBrook.Client.Component.Messages.setup();
    }

    // Preload any images.
    var images = [];
    var preload = function (image_urls) {
        for (var i=0; i<image_urls.length; i++) {
            images[i] = new Image();
            images[i].src = image_urls[i];
        }
    };
    preload([
        "/images/ui/up-arrow-taken.png",
        "/images/ui/up-arrow-paused.png",
        "/images/ui/up-arrow-untaken.png",
        "/images/ui/up-arrow-waiting-top.png",
        "/images/ui/down-arrow-taken.png",
        "/images/ui/down-arrow-paused.png",
        "/images/ui/down-arrow-untaken.png",
        "/images/ui/down-arrow-waiting-top.png",
        "/images/ui/down-arrow-waiting-top-blue.png"
    ]);
});