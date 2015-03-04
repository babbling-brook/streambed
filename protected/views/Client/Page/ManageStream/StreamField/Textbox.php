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

$required = (int)$model->required;

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
        <div class="error label-error" id="field_label_error_<?php echo $field_id; ?>">
        </div>
    </div>

    <div class="row field-row-label">
        <label for="field_test_type_<?php echo $field_id; ?>">
            Text Type<span class="required">*</span>
            <?php echo $help->textType(); ?>
        </label>
        <select
            class="text-type"
            data-text-type="<?php echo $model->value_type; ?>"
            id="text_type_select_<?php echo $model->stream_field_id; ?>"
        >
            <?php
            $options = LookupHelper::getDropDown("stream_field.text_type");
            foreach ($options as $value => $option) {
                $selected = '';
                if ((int)$model->text_type_id === LookupHelper::getId('stream_field.text_type', $value)) {
                    $selected = 'selected';
                }
                echo ('<option value="' . $value . '" ' . $selected . '>' . $option . '</option>');
            }
            ?>
            <option disabled>All Safe HTML</option>
            <option disabled>All Safe and CSS</option>
            <option disabled>Full HTML and CSS (locked to domus domain as client)</option>
            <option disabled>Custom</option>
        </select>
        <div class="error label-error" id="field_label_error_<?php echo $field_id; ?>">
        </div>
    </div>

    <div class="row field-row-max-length">
        <label for="field_max_<?php echo $field_id; ?>">Maximum Length</label>
        <input
            class="text-max"
            type="text"
            id="field_max_<?php echo $field_id; ?>"
            maxlength="10"
            size="10"
            value="<?php echo $model->max_size; ?>"
        >
        <div class="error max-size-error" id="field_max_size_error_<?php echo $field_id; ?>"></div>
    </div>

        <?php if ($model->display_order !== "1") { ?>
        <div class="row field-row-required">
            <label for="field_required_<?php echo $field_id; ?>">Required<span class="required">*</span></label>
            <input
                class="text-required"
                type="checkbox"
                id="field_required_<?php echo $field_id; ?>"
                <?php echo $disabled_required; ?>
                value="<?php echo $required === 1 ? 'true' : 'false' ?>"
                <?php echo $required === 1 ? 'checked="checked"' : ''; ?>
            >
            <div class="error required-error" id="field_required_error_<?php echo $field_id; ?>"></div>
        </div>


        <div class="row field-row-filter">
            <?php
            // Find the selected value and whether to show the regular expression field
            $hidden = true;
            if (is_null($model->regex) === true) {
                $selected = '';
            } else {
                $selected = $model->regex;
            }
            if ($selected === true) {
                if (StreamRegex::doesExist($model->regex) === false) {
                    $selected = "more";
                    $hidden = false;
                }
            }
            ?>
            <label for="field_filter_<?php echo $field_id; ?>">
                Filter
                <?php echo $help->filter(); ?>
            </label>
            <select id="field_filter_<?php echo $field_id; ?>" class="text-filter">

                <?php
                $options = StreamRegex::model()->findAll(array('order' => 'display_order'));
                foreach ($options as $option) {
                    $option_selected = '';
                    if ($option->name === $selected
                        || ($option->regex === '' && $option->name === 'None')
                        || ($option->regex === '' && $option->name === 'more...')
                    ) {
                        $option_selected = 'selected';
                    }
                    $value = $option->stream_regex_id;
                    if ((int)$option->stream_regex_id === 1) {
                        $value = '';
                    }
                    if ((int)$option->stream_regex_id === 2) {
                        $value = 'more';
                    }
                    echo ('<option value="' . $value . '" ' . $option_selected . '>' . $option->name . '</option>');
                }
                ?>

            </select>

            <div class="error" style="display:none" id="field_filter_error_<?php echo $field_id; ?>"></div>
        </div>

        <div class="row regex-rows <?php echo ($hidden === true ? "hide" : "") ;?>" >

            <label for="field_regex_<?php echo $field_id; ?>">Regex</label>
            <input
                class="text-regex"
                type="text"
                id="field_regex_<?php echo $field_id; ?>"
                maxlength="256"
                size="60"
                value="<?php echo $model->regex; ?>"
            >
            <div class="error regex-error" id="field_regex_error_<?php echo $field_id; ?>"></div>


        </div>

        <div class="row regex-rows <?php echo ($hidden === true ? "hide" : "") ;?>" >

            <label for="field_regex_error_<?php echo $field_id; ?>">
                Error message if the regular expression fails
            </label>
            <input
                class="text-regex-error"
                type="text"
                id="field_regex_error_<?php echo $field_id; ?>"
                maxlength="256"
                size="60"
                value="<?php echo $model->regex_error; ?>"
            >
            <div
                class="error regex-error-error"
                style="display:none"
                id="field_regex_error_error_<?php echo $field_id; ?>"
            ></div>

        </div>

    <?php } ?>

</div>
