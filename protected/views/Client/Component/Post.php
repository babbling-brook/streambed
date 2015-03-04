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
<?php // Reply to an post template. ?>
<div id="post_reply_template">
    <div class="reply-post">
        <div class="reply-content"></div>
    </div>
</div>

<?php // Lines in a child stream menu. Used in posts to show the list of child streams when reply is clicked ?>
<div id="reply_line_template">
    <li class="moderation-submenu-item link"></li>
</div>

<div id="edit_post_template">
    <div class="edit-post">
        <div class="edit-title"></div>
        <div id="" class="edit-post"></div>
    </div>
</div>

<div id="reply_to_post_template">
    Reply to post by <em class="post-username"></em> in the <em class="child-stream"></em> stream
</div>