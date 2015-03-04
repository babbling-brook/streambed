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
 * View for a view template for regex views.
 */
?>
<div class="view">

    <b><?php echo CHtml::encode($data->getAttributeLabel('stream_regex_id')); ?>:</b>
    <?php
        echo CHtml::link(CHtml::encode($data->stream_regex_id), array('view', 'id' => $data->stream_regex_id));
    ?>
    <br />

    <b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
    <?php echo CHtml::encode($data->name); ?>
    <br />

    <b><?php echo CHtml::encode($data->getAttributeLabel('regex')); ?>:</b>
    <?php echo CHtml::encode($data->regex); ?>
    <br />

    <b><?php echo CHtml::encode($data->getAttributeLabel('display_order')); ?>:</b>
    <?php echo CHtml::encode($data->display_order); ?>
    <br />


</div>