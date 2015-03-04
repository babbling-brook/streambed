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
 * View for the viewing of posts.
 */

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Post/PostWithTree'));
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Post/PostWithTree'));

$this->renderPartial('/Shared/Page/Stream/_side_description');

//$this->menu = $this->operationsMenu("take");
echo CHtml::hiddenField("user_id", Yii::app()->user->getId());
?>

<div id="loading_main_post" class="block-loading">
    Loading post
</div>

<?php // Templates used to display trees. ?>
<div class="hide">
    <?php // Template for a branch in a tree of posts. ?>
    <div id="tree_branch_template">
        <ul id="tree_root">
            <li class="post"></li>
            <div id="loading_children" class="block-loading">Loading children</div>
        </ul>
    </div>
    <?php // Used to display the root post in post trees. ?>
    <div id="post_tree_root_template">
        <li class="post root-post">
            <div class="top-value">
                <div class="field-2 field"></div>
            </div>
            <a class="post-thumbnail-container hide" target="_blank" href="">
                <img class="post-thumbnail" src="" />
            </a>
            <div class="title">
                <a class="field-1 field" data-post-link="true"></a>
            </div>
            <div class="info">
                <span class="username-intro">Made by</span> <a class="username"></a>.
                <time class="time-ago"></time>.
                <span class="kindred-intro">
                    Your kindred rating with <a class="username"></a> is <span class="kindred-score"></span>.
                </span>
                <span class="child-count"></span> comments.
                <span class="revision hide">
                    <span class="revision-title">Revision</span>
                    <span class="revision-content"></span>
                </span>
            </div>
            <div class="post-content-container">
                <span class="post-content" data-show-label="true"></span>
            </div>
            <div class="actions">
                <a class="parent-post hide">parent</a>
                <a class="full-thread hide">full thread</a>
                <span class="edit link hide">edit</span>
                <span class="post-rings moderation-submenu">
                    <span class="ring-title link">rings</span>
                    <ul class="hide"></ul>
                </span>
                <span class="delete link hide">delete</span>
                <span class="delete-confirm hide">
                    Confirm deletion
                    <span class="delete-confirmed link">Yes</span> /
                    <span class="delete-canceled link">No</span>
                </span>
                <span class="cooldown hide">cooldown</span>
                <span class="cooldown-time"></span>
                <span class="hide-post link">hide</span>
                <span class="post-reply moderation-submenu hide">
                    <span class="reply-title moderation-submenu-title link">reply</span>
                    <ul class="reply-streams hide"></ul>
                </span>
                <span class="deleted hide">This has been deleted</span>
                <span class="post-loading hide"></span>
            </div>
            <div>
                <span class="reply-location hide"></span>
            </div>
            <div class="post_error error"></div>
            <div class="new-posts-link">
                <span class="show-new-posts hide link"><img src="/images/ui/add-fat.svg" />show new posts</span>
            </div>
            <ul class="children post"></ul>
        </li>
    </div>

    <?php // Used to display child posts in trees. ?>
    <div id="post_tree_child_template">
        <li class="post child-post">
            <div class="top-value child-arrows">
                <div class="field-2 field no-label"></div>
            </div>
            <div class="info">
                <span class="switch"></span>
                <span class="username-intro">Made by </span><a class="username"></a>.
                <time class="time-ago"></time>.
                <span class="kindred-intro">
                    Your kindred rating with <a class="username"></a> is <span class="kindred-score"></span>.
                </span>
                <span class="revision hide">
                    <span class="revision-title">Revision</span>
                    <span class="revision-content"></span>
                </span>
                <span class="post-loading hide"></span>
            </div>
            <div class="title">
                <span class="field-1 field" data-post-link="false"></span>
            </div>
            <div class="post-content-container">
                <span class="post-content" data-show-label="true"></span>
            </div>
            <div class="actions">
                <a class="link-to-post">link</a>
                <a class="parent-post hide">parent</a>
                <a class="full-thread hide">full thread</a>
                <span class="edit link hide">edit</span>
                <span class="post-rings moderation-submenu">
                    <span class="ring-title link">rings</span>
                    <ul class="hide"></ul>
                </span>
                <span class="delete link hide">delete</span>
                <span class="delete-confirm hide">
                    Confirm deletion
                    <span class="delete-confirmed link">Yes</span> /
                    <span class="delete-canceled link">No</span>
                </span>
                <span class="cooldown hide">cooldown</span>
                <span class="cooldown-time hide"></span>
                <span class="hide-post link">hide</span>
                <span class="post-reply hide moderation-submenu">
                    <span class="reply-title moderation-submenu-title link">reply</span>
                    <ul class="reply-streams hide"></ul>
                </span>
                <span class="deleted hide">This has been deleted</span>
                <span class="update hide">
                    <span class="show-update link">
                        Show revision
                    </span>
                </span>
            </div>
            <div>
                <span class="reply-location hide"></span>
            </div>
            <div class="post_error error"></div>
            <div class="new-posts-link">
                <span class="show-new-posts hide link"><img src="/images/ui/add-fat.svg" />show new posts</span>
            </div>
            <ul class="children post"></ul>
        </li>
    </div>

    <?php // Used to make a place holder for the posts after it has been sorted but before its details are fetched. ?>
    <div id="post_tree_dummy_post">
        <li class="post">
            <ul class="children"></ul>
        </li>
    </div>

    <?php // Used to add a status update to the root post in the bugs stream. ?>
    <div id="bug_status_template">
        <span class="field-label field-label-7">Status</span>
        <div class="field-7 field textbox-field"></div>
    </div>

</div>