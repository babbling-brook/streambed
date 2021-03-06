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
 * View for an insert/update form template for regex views.
 */
?>

<div class="form">

<?php
$form=$this->beginWidget(
    'CActiveForm',
    array(
        'id' => 'post-type-regex-form',
        'enableAjaxValidation' => false,
    )
);
?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->labelEx($model, 'name'); ?>
        <?php echo $form->textField($model, 'name', array('size' => 60, 'maxlength' => 128)); ?>
        <?php echo $form->error($model, 'name'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'regex'); ?>
        <?php echo $form->textField($model, 'regex', array('size' => 60, 'maxlength' => 256)); ?>
        <?php echo $form->error($model, 'regex'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'display_order'); ?>
        <?php echo $form->textField($model, 'display_order'); ?>
        <?php echo $form->error($model, 'display_order'); ?>
    </div>

    <div class="row buttons">
        <?php
        echo CHtml::submitButton(
            $model->isNewRecord === true ? 'Create' : 'Save',
            array('class' => 'standard-button')
        );
        ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->