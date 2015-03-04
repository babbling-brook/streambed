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
<div id="list_selector_template" class="hide">
    <table class="list-selector block-loading">
        <thead>
            <tr>
                <td>
                    Location
                </td>
                <td>

                </td>
                <td>
                    Remove
                </td>
                <td>
                    Order
                </td>
            </tr>
        </thead>
        <tbody>

        </tbody>
        <tfoot>
            <tr class="last-row">
                <td>
                    <input type="text"
                           data-default-value="Add a location here or click the 'open search' button"
                           class="list-selector-add-location"
                           value="" />
                </td>
                <td colspan="3">
                    <a class="open-search" href="">Open Search</a>
                    <a class="close-search hide" href="">Close Search</a>
                </td>
            </tr>
            <tr class="error hide">
                <td colspan="4"></td>
            </tr>
            <tr class="list-selector-search-row hide">
                <td colspan="4">
                    <div class="list-selector-search"></div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<table id="list_selector_location_row_template" class="hide">
    <tbody>
        <tr>
            <td data-location="" class="list-selector-location">
                <input type="text">
            </td>
            <td class="list-selector-change-version">
                <a class="hide">Change version</a>
                <span class="hide">
                    <select>
                        <option value="">Switch versions:</option>
                    </select>
                </span>
            </td>
            <td class="list-selector-remove">
                <img class="grid_icon image-button" src="/images/ui/delete.png" title="Remove" >
            </td>
            <td class="list-selector-sort">
               <img class="move-icon move-up" src="/images/ui/up-arrow-untaken.svg" title="Move up">
               <img class="move-icon move-down" src="/images/ui/down-arrow-untaken.svg" title="Move down">
            </td>
        </tr>
    </tbody>
</table>

<select id="list_selector_version_row_template" class="hide">
    <option></option>
</select>