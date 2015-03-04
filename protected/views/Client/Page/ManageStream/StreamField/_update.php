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
 * View for an update template for stream fields that can be included on other stream field templates.
 */

// action views are named the same as their values in the lookup table
$type = LookupHelper::getValue($data->field_type);
?>
<div id="otf_container_<?php echo $data->stream_field_id; ?>" class="field-container">
    <input type="hidden" class="field-type" value="<?php echo $type; ?>" />

    <div class="oft_actions">
        <div class="delete">
            <span class="value hide"><?php echo $data->stream_field_id; ?></span>
            <?php if ($data->display_order !== "1" && $data->display_order !== "2") { ?>
                <img src="/images/ui/delete.png" title="Delete field">
            <?php } ?>
        </div>
        <div class="sort-down">
            <span class="value hide"><?php echo $data->stream_field_id; ?></span>
            <?php if ($data->display_order !== "1" && $data->display_order !== "2") { ?>
                <img src="/images/ui/down-arrow-untaken.svg"
                        title="Move the display order for the field down">
            <?php } ?>
        </div>
        <div class="sort-up">
            <span class="value hide"><?php echo $data->stream_field_id; ?></span>
            <?php if ($data->display_order !== "1" && $data->display_order !== "2") { ?>
                <img src="/images/ui/up-arrow-untaken.svg"
                        title="Move the display order for the field up">
            <?php } ?>
        </div>
    </div>
    <h4 class="stream_field_header" id="stream_field_<?php echo $data->stream_field_id; ?>">
        <?php echo $data->label; ?>
    </h4>
    <div class="inner-field-container">
        <div class="cover"></div>
        <?php
        $this->renderPartial(
            '/Client/Page/ManageStream/StreamField/' . ucwords($type),
            array(
                'model' => $data,
            )
        );
        ?>
    </div>
</div>
