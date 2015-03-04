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
 * View for viewing stream fields that can be included on other stream field templates.
 */

?>
<div class="view">

    <b><?php echo CHtml::encode($data->getAttributeLabel('label')); ?>:</b>
    <?php echo CHtml::encode($data->label); ?>
    <br />

    <?php if (intval($data->field_type) === 2) {  // textbox - see lookup table?>

        <b>Field Type:</b> Textbox<br />

        <b><?php echo CHtml::encode($data->getAttributeLabel('max_size')); ?>:</b>
        <?php echo $data->max_size === null ? "No Limit" : $data->max_size; ?>
        <br />

        <b><?php echo CHtml::encode($data->getAttributeLabel('required')); ?>:</b>
        <?php echo (bool)$data->required === false ? "No" : "Yes"; ?>
        <br />

        <!-- @fixme Check if this is in the regex table and show a filter:name if it is -->
        <b>Filter:</b>
        <?php
        if ($data->regex === null) {
            echo ("None");
        } else {
            $regex_name = StreamRegex::getName($data->regex);
            if (strlen($regex_name) > 0) {
                echo ( CHtml::encode($regex_name));
            } else {
                echo ("Regular expresion: " .  CHtml::encode($data->regex));
            }
        }
        ?>
        <br />

    <?php } ?>


    <?php if (intval($data->field_type) === 3) {  // checkbox - see lookup table?>

        <b>Field Type:</b> Checkbox<br />

        <b><?php echo CHtml::encode($data->getAttributeLabel('checkbox_default')); ?>:</b>
        <?php echo $data->checkbox_default === 0 ? "Off" : "On"; ?>
        <br />

    <?php } ?>

    <?php if (intval($data->field_type) === 36) {  // link - see lookup table?>

        <b>Field Type:</b> Link<br />

    <?php } ?>


    <?php if (intval($data->field_type) === 37) {  // openlist - see lookup table ?>

        <b>Field Type:</b> Openlist<br />

    <?php } ?>

    <?php if (intval($data->field_type) === 4) {  // list - see lookup table?>

        <b>Field Type:</b> List<br />

        <b><?php echo CHtml::encode($data->getAttributeLabel('select_qty_min')); ?>:</b>
        <?php echo CHtml::encode($data->select_qty_min); ?>
        <br />

        <b>Items:</b>
        <?php
        $list_array = StreamList::getList($data->stream_field_id);
        $list = "";
        foreach ($list_array as $item) {
            $list .= $item->name . ", ";
        }
        $list = substr($list, 0, -2);
        echo (CHtml::encode($list));
        ?>
        <br />

        <b><?php echo CHtml::encode($data->getAttributeLabel('select_qty_max')); ?>:</b>
        <?php echo CHtml::encode($data->select_qty_max); ?>
        <br />

    <?php } ?>

    <?php if (intval($data->field_type) === 12) {  // value - see lookup table?>

        <b>Field Type:</b> Value<br />

        <b>Value Type:</b>
        <?php  // see lookup table stream_field.value_type for values match
        switch ($data->value_type) {
            case 13:
                echo ("Up and down arrows");
                break;

            case 14:
                echo ("Linear scale");
                break;

            case 15:
                echo ("Logarithmic scale");
                break;

            case 16:
                echo ("Textbox");
                break;
        }
        ?>
        <br />

        <b><?php echo CHtml::encode($data->getAttributeLabel('value_min')); ?>:</b>
        <?php echo $data->value_min === null ? "Not set" : $data->value_min; ?>
        <br />

        <b><?php echo CHtml::encode($data->getAttributeLabel('value_max')); ?>:</b>
        <?php echo $data->value_max === null ? "Not set" : $data->value_max; ?>
        <br />

    <?php } ?>

</div>