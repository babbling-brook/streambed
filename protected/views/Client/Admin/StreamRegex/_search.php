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
 * View for a search form template for regex views.
 */
?>

<div class="wide form">

<?php
$form=$this->beginWidget(
    'CActiveForm',
    array(
        'action' => Yii::app()->createUrl($this->route),
        'method' => 'get',
    )
);
?>

    <div class="row">
        <?php echo $form->label($model, 'stream_regex_id'); ?>
        <?php echo $form->textField($model, 'stream_regex_id'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'name'); ?>
        <?php echo $form->textField($model, 'name', array('size' => 60, 'maxlength' => 128)); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'regex'); ?>
        <?php echo $form->textField($model, 'regex', array('size' => 60, 'maxlength' => 256)); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'display_order'); ?>
        <?php echo $form->textField($model, 'display_order'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton('Search', array('class' => 'standard-button')); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->