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
 * View for an openlist stream field.
 */
$help = new StreamFieldHelp();

$field_id = $model->stream_field_id;
?>

<div class="post-type-field form">

    <?php
        $this->renderPartial(
            '/Client/Page/ManageStream/StreamField/Type',
            array(
                'help' => $help,
                'model' => $model,
            )
        );
    ?>

    <div class="row field-row-label">
        <label for="field_label_<?php echo $field_id; ?>">Label<span class="required">*</span></label>
        <input
            class="field-label"
            type="text"
            id="field_label_<?php echo $field_id; ?>"
            maxlength="256"
            size="60"
            value="<?php echo $model->label; ?>"
        >
        <div class="error label-error" id="field_label_error_<?php echo $field_id; ?>">
        </div>
    </div>

    <div class="row field-row-list-min">
        <label for="field_select_qty_min_<?php echo $field_id; ?>">
            Minimum select quantity<span class="required">*</span>
        </label>
        <input
            class="list-select-qty-min"
            type="text"
            id="field_select_qty_min_<?php echo $field_id; ?>"
            maxlength="10"
            size="10"
            value="<?php echo $model->select_qty_min; ?>"
        >
        <div class="error select-qty-min-error" id="field_select_qty_min_error_<?php echo $field_id; ?>"></div>
    </div>


    <div class="row field-row-list-max">
        <label for="field_select_qty_max_<?php echo $field_id; ?>">
            Maximum select quantity<span class="required">*</span>
        </label>
        <input
            class="list-select-qty-max"
            type="text"
            id="field_select_qty_max_<?php echo $field_id; ?>"
            maxlength="10"
            size="10"
            value="<?php echo $model->select_qty_max; ?>"
        >
        <div class="error select-qty-max-error" id="field_select_qty_max_error_<?php echo $field_id; ?>"></div>
    </div>

</div>