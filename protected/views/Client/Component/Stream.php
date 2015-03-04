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

<?php // Used to display posts in streams. Also the default template for the DisplayPost class. ?>
<div id="post_stream_template">
    <div class="post stream-post">

        <div class="top-value">
            <div class="field-2 field"></div>
        </div>
        <a class="post-thumbnail-container hide" target="_blank" href="">
            <img class="post-thumbnail" src="" />
        </a>
        <div class="title">
            <a class="field-1 field block-loading" target="_blank" data-post-link="true"></a>
        </div>
        <div class="info">
            <span class="username-intro">Made by</span> <a class="username"></a>.
            <time class="time-ago"></time>.
            <span class="kindred-intro">
                Your kindred rating with <a class="username"></a> is <span class="kindred-score"></span>.
            </span>
            <span class="sort-score-intro">The sort score is <span class="sort-score"></span>.</span>
        </div>
        <div class="actions">
            <a class="link-to-post">
                comments
                (<span class="child-count"></span>)
            </a>
            <span class="post-rings moderation-submenu">
                <span class="ring-title link">rings</span>
                <ul class="hide"></ul>
            </span>
            <span class="hide-post link">hide</span>
            <span class="delete link">delete</span>
            <span class="deleted hide">This post has been deleted</span>
            <span class="delete-confirm hide">
                Confirm deletion
                <span class="delete-confirmed link">Yes</span> /
                <span class="delete-canceled link">No</span>
            </span>
            <span
                class="cooldown hide"
                title="This post will not be made public until the cooldown has finished">
                cooldown
            </span>
            <span class="cooldown-time"></span>

            <span class="revision hide">
                <span class="revision-title hide">Revision</span>
                <span class="revision-content hide"></span>
            </span>
            <span class="update link hide">
                <span class="show-update">show update</span>
            </span>
            <span class="post-loading hide"></span>
        </div>
        <div class="post-error error">
            <span class="post-error"></span>
        </div>
        <div class="new-posts-link hidden">
            <span class="link"><img src="/images/ui/add-fat.svg" />show new posts</span>
        </div>
    </div>
</div>