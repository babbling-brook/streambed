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

<?php // A template for the container to display open list suggestions. ?>
<div id="open_list_suggestions_template">
    <div class="suggestion-hints-container">
        <ul class="open-list-selected"></ul>
        <div class="open-list-multi-container">
            <ul class="open-list-suggestions hide"></ul>
            <input class="open-list-visible-input" type="text"/>
        </div>
    </div>
</div>

<?php // A template for a line for displaying an open list suggestion. ?>
<div id="open_list_suggestions_line_template">
    <li>
    </li>
</div>

<?php // A template for a line for displaying an open list suggestion. ?>
<div id="open_list_suggestions_selected_line_template">
    <li>
    </li>
</div>