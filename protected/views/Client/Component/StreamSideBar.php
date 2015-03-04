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
<?php // A template to display client parameters for filter rhythms on the side bar. ?>
<div id="client_params_sidebar_template">
    <div class="client-param-row">
        <label class="client-param-label"></label>
        <div class="sidebar-params">
            <input type="text" />
            <div title="Click for help with this filter" class="help-icon param-help-icon">
                <span class="help-title hide">title</span>
                <span class="help-content hide">content</span>
            </div>
        </div>
    </div>
</div>

<?php // A template to display client apply filter button. ?>
<div id="client_params_apply_template">
    <div>
        <input type="button" class="standard-button client-param-apply" value="Apply Filters" />
    </div>
</div>