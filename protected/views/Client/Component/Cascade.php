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

<?php // A template  for displaying cascades of posts. ?>
<div id="cascade_template">
    <div class="cascade">
        <div class="cascade-loading">
            Loading posts...
        </div>
        <div class="cascade-new-top hide hidden">
            <span class="link">
                <img src="/images/ui/add-fat.svg">
                show new posts
            </span>
        </div>
        <div class="cascade-body"></div>
        <div class="cascade-no-posts hide">
            There are no posts available to display.
        </div>
        <div class="cascade-no-more-posts hide">
            There are no more posts to display.
        </div>
    </div>
</div>

<?php // A template dummy cascade posts. ?>
<?php // Used as a place holder to maintain display order whilst the post data is loading. ?>
<div id="cascade_dummy_post_template">
    <div class="cascade-dummy-post block-loading hide"></div>
</div>