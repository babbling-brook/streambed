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

<?php // Template for the private section of a new post. ?>
<div id="private_post_template">
    <div class="private-post">
        <div class="private-post-check">
            <input type="checkbox" name="private-post" value="true">
            <label>Make this post private.</label>
        </div>
        <div class="private-post-title hide">
            Send this private post to :
        </div>
        <div class="private-post-to hide"></div>
        <div class="private-post-error error hide">
            One of the users this post is being sent to is not valid.
        </div>
        <div class="private-post-waiting error hide">
            Validating usernames. Please wait.
        </div>
        <div class="private-post-none-selected error hide">
            No usernames have been entered to send the post to.
        </div>
    </div>
</div>

<?php // Template for the 'to' address in the private section of a new post. ?>
<div id="private_post_to_template">
    <div class="private-post-to-line">
        <div class="delete"></div>
        <input type="text" class="address empty" value ="" />
        <span class="tick hide" title="Username is valid"></span>
        <span class="label-loading hide"></span>
        <div class="address-error error hide"></div>
    </div>
</div>

<?php // Content message for first private message address. ?>
<span id="private_post_first_message">Enter the username@domain to send this post to</span>

<?php // Content message for additional private message address. ?>
<span id="private_post_additional_message">Enter another username@domain to send this post to</span>

<?php // Error message if the username is not found. ?>
<span id="private_post_username_error">Username not found</span>

<?php // Content message for additional private message address. ?>
<span id="private_post_domain_error">
    The Domain in this username is not a BabblingBrook domain or is not responding
</span>

<?php // Template for suggested addresses in the private section of a new post. ?>
<ul class="private-post-suggestions"></ul>

<?php // Template for a suggested addresses line in the private section of a new post. ?>
<li class="private-post-suggestions-line"></li>


<?php // The template for link thumbnail selection when making a post ?>
<div id="link_thumbnails_template">
    <div class="link-thumbnail-container">
        <div class="link-thumbnail-initial_message">
            Enter a link to select a thumbnail
        </div>
        <div class="default_thumnail selected-thumb">
            <img src="/images/ui/no-thumbnail.png"/>
        </div>
        <div class="thumbnails-loading hide block-loading">
            loading thumbnails
        </div>
        <div class="all-thumbs hide">
        </div>
    </div>
</div>

<?php // The template for displaying a single thumbnail. ?>
<div id="link_thumbnail_template">
    <div class="thumbnail-container">
        <canvas class="thumb-location"></canvas>
    </div>
</div>

<div id="make_post_textarea_template">
    <div class="post-field-container">
        <div class="post-field-title">
        <em class="field-label"></em>
        <div class="error hide"></div>
        </div>
        <textarea class="post-text-field">
        </textarea>
    </div>
</div>

<div id="make_post_linkfield_template">
    <div class="post-field-container">
        <div class="post-field-title post-field-title-title">
            A title for <em class="field-label"></em>
        <div class="error hide error-title"></div>
        </div>
        <textarea id="" class="post-text-field link-name"></textarea>
        <div class="post-field-title post-field-title-link hide">
            <em class="field-label"></em>
        <div class="error hide error-link"></div>
        </div>
        <input type="text" class="post-link-text hide" value="" />
        <div class="thumnails hide">
             <input class="thumbnail-url" type="hidden">
             <input class="small-thumbnail-base16" type="hidden">
             <input class="large-thumbnail-base16" type="hidden">
             <input class="large-proportional-thumbnail-base16" type="hidden">
        </div>
    </div>
</div>

<div id="make_post_checkbox_template">
    <div class="post-field-container">
        <div><em class="field-label"></em></div>
        <input type="checkbox" id="">
    </div>
</div>

<div id="make_post_list_template">
    <div class="post-field-container small-list">
        <div class="post-field-title">
            <em class="field-label"></em>
            <div class="error hide"></div>
        </div>
        <div class="post-list-container">
        </div>
    </div>
</div>

<div id="make_post_list_item_template">
    <input type="checkbox" />
    <label class="list-item" for=""></label>
</div>


<div id="make_post_openlist_template">
    <div class="post-field-container">
        <div class="post-field-title">
            <em class="field-label"></em>
            <div class="error hide"></div>
        </div>
        <div class="openlist-container">
            <input type="text" class="readable-text" value="">
        </div>
    </div>
</div>


<div id="make_post_value_template">
    <div class="post-field-container">
        <div class="post-field-title">
            <em class="field-label"></em>
        </div>
        <label for="" class="min-value-label">
            Enter a minimum value that you will accept for this post
        </label>
        <div class="error min-error hide"></div>
        <input type="text" class="min-value" value="">
        <label for="" class="max-value-label">
        </label>
        <div class="error max-error hide"></div>
        <input type="text" class="max-value" value="">
    </div>
</div>

<div id="make_post_new_post_template">
    <div class="input-post-title"></div>
    <div class="input-post-detail">
        <div class="make-post-actions">
            <button class="create-post standard-button" type="button"></button>
            <button class="cancel-post standard-button" type="button"></button>
        </div>
        <div class="make-post-errors">
            <span class="post-processing"></span>
            <div class="post-submit-error error hide"></div>
            <div class="post-submit-domus-error error hide">
                There was a problem when submitting your post. Please try again.
            </div>
            <div class="thumbnails-loading-error error hide">
                Please wait for thumbnail links to finish loading.
            </div>
        </div>
    </div>
</div>

<div id="error_fetching_stream_template">
    404 Stream not found when trying to make post form for a stream.
</div>

<div id="error_fetching_stream_message_template">
    Unable to display the form for creating posts in the <em class="stream-url"></em> stream.
</div>