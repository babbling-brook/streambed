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
 * View for a editing a checkbox stream field.
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

    <div class="row field-row-default">
        <label for="field_required_<?php echo $field_id; ?>">Default<span class="required">*</span></label>
        <select id="field_checkbox_default_<?php echo $field_id; ?>" class="checkbox-default">
            <?php
            if ((int)$model->checkbox_default === 0) {
                $off = 'selected';
                $on = '';
            } else {
                $off = '';
                $on = 'selected';
            }
            ?>
            <option value="0" <?php echo $off; ?>>Off</option>
            <option value="1" <?php echo $on; ?>>On</option>
        </select>
        <div class="error checkbox-default-error" id="field_checkbox_default_error_<?php echo $field_id; ?>"></div>
    </div>


</div>