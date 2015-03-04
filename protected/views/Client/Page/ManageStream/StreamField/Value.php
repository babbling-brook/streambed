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
 * View for a value stream field.
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
            maxlength="128"
            size="60"
            value="<?php echo $model->label; ?>"
        >
        <div class="error label-error" id="field_label_error_<?php echo $field_id; ?>">
        </div>
    </div>

    <?php
    $updown_selected = '';
    $linear_selected = '';
    $logarithmic_selected = '';
    $textbox_selected = '';
    $stars_selected = '';
    $button_selected = '';
    $list_selected = '';
    $type = LookupHelper::getValue($model->value_type);
    switch ($type) {
        case 'updown' :
            $updown_selected = 'selected';
            break;

        case 'linear' :
            $link_selected = 'selected';
            break;

        case 'logarithmic' :
            $checkbox_selected = 'selected';
            break;

        case 'textbox' :
            $list_selected = 'selected';
            break;

        case 'stars' :
            $openlist_selected = 'selected';
            break;

        case 'button' :
            $button_selected = 'selected';
            break;

        case 'list' :
            $list_selected = 'selected';
            break;
    }
    ?>

    <div class="row">

        <label for="value_select_<?php echo $model->stream_field_id; ?>">
            Select the type of value
        </label>

        <select
            class="value"
            data-value-id="<?php echo $model->value_type; ?>"
            id="value_select_<?php echo $model->stream_field_id; ?>"
        >
            <optgroup label="Select the type of value">
                <option
                    class="value-updown"
                    data-value-id="<?php echo LookupHelper::getID("stream_field.value_type", "updown"); ?>"
                    value="updown" <?php echo $updown_selected; ?>
                >Arrows</option>
                <?php if ($model->display_order !== '2') { ?>
                    <option
                        class="value-linear"
                        data-value-id="<?php echo LookupHelper::getID("stream_field.value_type", "linear"); ?>"
                        value="linear" <?php echo $linear_selected; ?>
                    >Linear slider</option>
                    <option
                        class="value-logarithmic"
                        data-value-id="<?php echo LookupHelper::getID("stream_field.value_type", "logarithmic"); ?>"
                        value="logarithmic" <?php echo $logarithmic_selected; ?>
                    >Logarithmic slider</option>
                    <option
                        class="value-textbox"
                        data-value-id="<?php echo LookupHelper::getID("stream_field.value_type", "textbox"); ?>"
                        value="textbox" <?php echo $textbox_selected; ?>
                    >Textbox</option>
                    <option
                        class="value-stars"
                        data-value-id="<?php echo LookupHelper::getID("stream_field.value_type", "stars"); ?>"
                        value="stars" <?php echo $stars_selected; ?>
                    >Stars</option>
                  <option
                        class="value-button"
                        data-value-id="<?php echo LookupHelper::getID("stream_field.value_type", "button"); ?>"
                        value="button" <?php echo $button_selected; ?>
                    >Button</option>
                  <option
                        class="value-button"
                        data-value-id="<?php echo LookupHelper::getID("stream_field.value_type", "list"); ?>"
                        value="list" <?php echo $list_selected; ?>
                    >List</option>
                <?php } ?>
            </optgroup>
        </select>

        <span class="title-help">
            <?php echo $help->valueScale(); ?>
        </span>
    </div>

    <div class="row field-row-value-options">
        <label for="field_value_options_<?php echo $field_id; ?>">
            Value Options
        </label>
        <select id="field_value_options_<?php echo $field_id; ?>" class="value-options">
            <?php
            if ($model->display_order === '2') {
                ?>
                echo ('<option value="17" selected>User can enter any value</option>');
                <?php
            } else {
                $options = LookupHelper::getDescriptions("stream_field.value_options");
                foreach ($options as $value => $option) {
                    $selected = '';
                    if ((int)$model->value_options === $value) {
                        $selected = 'selected';
                    }
                    echo ('<option value="' . $value . '" ' . $selected . '>' . $option . '</option>');
                }
            }
            ?>
        </select>
        <?php echo $help->valueOptions(); ?>
    </div>

    <div class="row hide value-min-row">
        <label for="field_value_min_<?php echo $field_id; ?>">
            Minimum Value
            <?php echo $help->valueMin(); ?>
        </label>
        <input
            class="value-min"
            type="text"
            id="field_value_min_<?php echo $field_id; ?>"
            maxlength="10"
            size="10"
            value="<?php echo $model->value_min; ?>"
        >
        <div class="error value-min-error"></div>
    </div>

    <div class="row hide value-max-row">
        <label for="field_value_max_<?php echo $field_id; ?>">
            Maximum Value
            <?php echo $help->valueMax(); ?>
        </label>
        <input
            class="value-max"
            type="text"
            id="field_value_max_<?php echo $field_id; ?>"
            maxlength="10"
            size="10"
            value="<?php echo $model->value_max; ?>"
        >
        <div class="error value-max-error"></div>
    </div>

    <div class="row hide value-rhythm-row">
        <label for="field_value_rhythm_<?php echo $field_id; ?>">
            Rhythm Url
            <?php echo $help->valueUrl(); ?>
        </label>
        <input
            class="value-rhythm"
            type="text"
            id="field_value_rhythm_<?php echo $field_id; ?>"
            maxlength="256"
            size="60"
            value="<?php echo $model->rhythm_check_url; ?>"
        >
        <div class="error rhythm-check-url-error"></div>
    </div>

    <div class="row hide value-list-remove-item-row">
        <label for="value_list_items_<?php echo $field_id; ?>">
            Remove items from the value list
            <?php echo $help->valueList(); ?>
        </label>
        <select
            class="value-list-items"
            multiple="multiple"
            size="4"
            id="value_list_items_<?php echo $field_id; ?>"
        >

            <?php
            $value_list = TakeValueList::getList($field_id);
            foreach ($value_list as $option) {
                echo ('<option value="' . $option->take_value_list_id . '">'
                    . $option->value . ' ' .$option->name . '</option>');
            }
            ?>

        </select>
        <input type="button" value="Remove" class="remove-value-list-item" />
    </div>

    <div class="row hide value-list-add-item-row">
        <label for="value_list_add_item_<?php echo $field_id; ?>">
            Add an item to the list
        </label>

        <input
            class="new-value-list-item"
            type="text"
            maxlength="128"
            size="30"
            id="value_list_add_item_<?php echo $field_id; ?>"
        />
        <input type="button" value="Add" class="add-value-list-item" />
        <div class="error add-value-list-item-error"></div>
    </div>

    <div class="row value-who-can-edit-row">
        <label for="who_can_take_<?php echo $field_id; ?>">
            Who can take this
        </label>

        <?php
        $anyone_selected = '';
        $owner_selected = '';
        $type = LookupHelper::getValue($model->who_can_take);
        switch ($type) {
            case 'anyone' :
                $anyone_selected = 'selected';
                break;

            case 'owner' :
                $owner_selected = 'selected';
                break;
        }

        ?>

        <select class="who-can-take" id="who_can_take_<?php echo $field_id; ?>" >
            <option value="anyone"  <?php echo $anyone_selected; ?>>Anyone</option>
            <option value="owner"  <?php echo $owner_selected; ?>>Stream Owner</option>
        </select>
    </div>

</div>




