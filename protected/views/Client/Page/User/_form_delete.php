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
 * Template view for user deletion.
 */
?>
<div class="form">

<?php
$form=$this->beginWidget(
    'CActiveForm',
    array(
        'id' => 'user-form',
        'enableAjaxValidation' => false,
    )
);
?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->labelEx($model, 'username'); ?>
        <?php echo $form->textField($model, 'username', array('size' => 60, 'maxlength' => 128)); ?>
        <?php echo $form->error($model, 'username'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'password'); ?>
        <?php echo $form->passwordField($model, 'password', array('size' => 60, 'maxlength' => 128)); ?>
        <?php echo $form->error($model, 'password'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'email'); ?> (Only used for password recovery)
        <?php echo $form->textField($model, 'email', array('size' => 60, 'maxlength' => 128)); ?>
        <?php echo $form->error($model, 'email'); ?>
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