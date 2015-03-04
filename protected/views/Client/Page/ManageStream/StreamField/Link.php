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
 * View for a editing a link stream field.
 */

$help = new StreamFieldHelp();

$required = $model->required;
$disabled_required = '';
if ($model->display_order === 1) {
    $required = 1;
    $disabled_required= 'disabled';
}
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
            maxlength="128"
            size="60"
            value="<?php echo $model->label; ?>"
        >
        <div class="error label-error" id="field_label_error_<?php echo $field_id; ?>"></div>
    </div>

    <div class="row field-row-required">
        <label for="field_required_<?php echo $field_id; ?>">Required<span class="required">*</span></label>
        <input
            class="link-required"
            type="checkbox"
            id="field_required_<?php echo $field_id; ?>"
            <?php echo $disabled_required; ?>
            value="<?php echo $required === 1 ? 'true' : 'false'; ?>"
            <?php
            if ($model->display_order === '1') {
                echo ('disabled="disabled" checked="checked"');
            } else {
                echo $required === 1 ? 'checked="checked"' : '';
            }
            ?>
        >
        <div class="error required-error" id="field_required_error_<?php echo $field_id; ?>"></div>
    </div>

</div>