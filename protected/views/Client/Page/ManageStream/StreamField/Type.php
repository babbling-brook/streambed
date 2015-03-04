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
 * View for the drop down selector for the type of stream field.
 */

?>
<?php //echo $form->hiddenField($model, 'stream_field_id'); ?>
<?php
echo CHtml::hiddenField('type_changed');

$textbox_selected = '';
$link_selected = '';
$checkbox_selected = '';
$list_selected = '';
$openlist_selected = '';
$value_selected = '';
$type = LookupHelper::getValue($model->field_type);
switch ($type) {
    case 'textbox' :
        $textbox_selected = 'selected';
        break;

    case 'link' :
        $link_selected = 'selected';
        break;

    case 'checkbox' :
        $checkbox_selected = 'selected';
        break;

    case 'list' :
        $list_selected = 'selected';
        break;

    case 'openlist' :
        $openlist_selected = 'selected';
        break;

    case 'value' :
        $value_selected = 'selected';
        break;

}
?>

<div class="row">

    <label for="field_select_<?php echo $model->stream_field_id; ?>">
        Select the type of field
    </label>

    <select class="type" id="field_select_<?php echo $model->stream_field_id; ?>">
        <?php // values are from lookup db table: column = stream_field.field_type ?>
        <?php if ($model->display_order !== "2") { // Dont show on main value ?>
            <?php // Dont show if stream.kind is 'user' ?>
            <?php $kind_id = StreamField::getKind($model->stream_field_id); ?>
            <?php if ('user' !== LookupHelper::getValue($kind_id) || $model->display_order > '1') { ?>
                <option class="field-textbox" value="textbox" <?php echo $textbox_selected; ?>>
                    Textbox
                </option>
            <?php } ?>

            <option class="field-link" value="link" <?php echo $link_selected; ?>>
               Link
            </option>

            <?php if ($model->display_order !== '1') { // Dont show on main title or value?>
                <option class="field-checkbox" value="checkbox" <?php echo $checkbox_selected; ?>>
                    Checkbox
                </option>
                <option class="field-list" value="list" <?php echo $list_selected; ?>>
                    List
                </option>
                <option class="field-openlist" value="openlist" <?php echo $openlist_selected; ?>>
                    Open List
                </option>
            <?php } ?>
        <?php } ?>
        <?php if ($model->display_order !== '1') { // Dont show on main title?>
            <option class="field-value" value="value" <?php echo $value_selected; ?>>
                Value
            </option>
        <?php } ?>
    </select>

    <span class="title-help">
        <?php
        switch($model->field_type) {
            case Lookuphelper::getID('stream_field.field_type', 'textbox'):
                if ($model->display_order === '1') {
                    echo $help->title();
                } else {
                    echo $help->textbox();
                }
                break;

            case Lookuphelper::getID('stream_field.field_type', 'link'):
                echo $help->link();
                break;

            case Lookuphelper::getID('stream_field.field_type', 'checkbox'):
                echo $help->checkbox();
                break;

            case Lookuphelper::getID('stream_field.field_type', 'list'):
                echo $help->listField();
                break;

            case Lookuphelper::getID('stream_field.field_type', 'openlist'):
                echo $help->openlist();
                break;

            case Lookuphelper::getID('stream_field.field_type', 'value'):
                if ($model->display_order === '2') {
                    echo $help->mainValue();
                } else {
                    echo $help->value();
                }
                break;
        }
        ?>
    </span>
</div>