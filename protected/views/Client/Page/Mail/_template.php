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

/**
 * Partial view for the private post template
 */
?>
<div class="templates hide">
    <div id="post_inbox_template" class="content-block">
        <div class="post inbox-post">
            <div class="top-value hide">
                <div class="field-2 field"></div>
            </div>
            <div class="info">
                <span class="private-message">A private post</span>
                <span class="username-intro">Made by </span><a class="username"></a>.
                <time class="time-ago"></time>.
                Your kindred rating with <a class="username extra-username"></a> is <span class="kindred-score"></span>.
            </div>
            <div class="title">
                <span class="field-1 field"></span>
            </div>
            <div class="actions">
                <a class="link-to-post">link</a>
                <a class="parent-post hide">parent</a>
                <a class="full-thread hide">full thread</a>
                <span class="edit link hide">edit</span>
                <span class="post-reply moderation-submenu hide">
                    <span class="reply-title moderation-submenu-title link">reply</span>
                    <ul class="reply-streams hide"></ul>
                </span>
                <span class="hide-post link">hide</span>
                <span class="delete link hide">delete</span>
                <span class="deleted hide">This has been deleted</span>
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
            </div>
            <div>
                <span class="reply-location hide"></span>
            </div>
            <div class="post-error error">
                <span class="post-error"></span>
            </div>
            <div class="new-posts-link hidden">
                <span class="link"><img src="/images/ui/add-fat.svg" />show new posts</span>
            </div>
            <div class="post-replies"></div>
        </div>
    </div>

    <div id="post_sent_template" class="content-block">
        <div class="post inbox-post">
            <div class="top-value hide">
                <div class="field-2 field"></div>
            </div>
            <div class="info">
                <span class="private-message">A private post</span>
                <span class="username-intro">Made by </span><a class="username"></a>.
                <time class="time-ago"></time>.
                Your kindred rating with <a class="username extra-username"></a> is <span class="kindred-score"></span>.
            </div>
            <div class="title">
                <span class="field-1 field"></span>
            </div>
            <div class="actions">
                <a class="link-to-post">link</a>
                <a class="parent-post hide">parent</a>
                <span class="edit link">edit</span>
                <span class="hide-post link">hide</span>
                <span class="delete link">delete</span>
                <span class="deleted hide">This has been deleted</span>
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
            </div>
            <div class="post-error error">
                <span class="post-error"></span>
            </div>
            <div class="new-posts-link hidden">
                <span class="link"><img src="/images/ui/add-fat.svg" />show new posts</span>
            </div>
        </div>
    </div>
</div>