<?php
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
 */
?>
<div id="messages_template">
    <div id="messages" class="content-indent">
        <div id="report_bug"></div>
        <div id="messages_inner" data_index="-1"></div>
        <div id="messages_full" class=""></div>
        <div class="hide error-details"></div>
        <div id="message_buttons"></div>
    </div>
</div>

<?php // Template for displaying snippet posts in the message box ?>
<div id="snippet_template">
    <div class="post snippet-post">
        <div class="top-value">
            <div class="field-2 field"></div>
        </div>
        <div class="title">
            <span class="field-1 field textbox-field" data-post-link="false"></span>
        </div>
    </div>
</div>

<?php // Used to display a full post in the message box. ?>
<div id="snippet_full_template">
    <div class="post child-post">
        <div class="top-value child-arrows">
            <div class="field-2 field no-label"></div>
        </div>
        <div class="info">
            <span class="username-intro">Made by </span><a class="username"></a>
        </div>
        <div class="title">
            <span class="field-1 field" data-post-link="false"></span>
        </div>
        <div class="post-content-container">
            <span class="post-content" data-show-label="true"></span>
        </div>
        <div class="actions">
            <a class="link-to-post">
                comments
                (<span class="child-count"></span>)
            </a>
        </div>
    </div>
</div>

<?php // Used to display a a stream suggestion in the message box. ?>
<div id="suggestion_stream_message_template">
    <div>
        A new stream has been suggested :
        <a class="suggestion-message" href="" title=""></a>
    </div>
</div>

<?php // Used to display a a filter suggestion in the message box. ?>
<div id="suggestion_filter_message_template">
    <div>
        A new filter has been suggested for the <a href="" title="" class="stream-name"></a> stream :
        <a class="suggestion-message" href="" title=""></a>
    </div>
</div>

<?php // Used to display a moderation ring suggestion in the message box. ?>
<div id="suggestion_moderation_ring_message_template">
    <div>
        A new moderation ring has been suggested for the <a href="" title="" class="stream-name"></a> stream :
        <a class="suggestion-message" href="" title=""></a>
    </div>
</div>

<?php // Used to display a user suggestion in the message box. ?>
<div id="suggestion_user_message_template">
    <div>
        A new user has been suggested for you :
        <a class="suggestion-message" href="" title=""></a>
    </div>
</div>

<?php // Used to display a full posts in the message box. ?>
<div id="no_more_suggestions_message_template">
    <div>
        There are no more suggestions to show at the moment.
    </div>
</div>

<?php // Used to display a full posts in the message box. ?>
<div id="no_more_suggestions_button_template">
    <button class="standard-button" id="close_suggestions">Close Suggestions</button>
</div>

<?php // Template for the pop up bug post form. ?>
<div id="no_messages_template">
    There are no messages to display.
</div>

<?php // Template for cropping the message box text. ?>
<div id="message_crop_template">
    <div id="message_crop"></div>
</div>