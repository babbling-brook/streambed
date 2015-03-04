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
 * View for a ring admin to manage the rings take names.
 */

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Ring/ManageTakeNames'));
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Ring/ManageTakeNames'));

$this->pageTitle=Yii::app()->name . ' - Manage take names for ' . $ring_name;

$this->menu_title = "Ring Admin";
$this->menu = RingController::adminMenu($admin_type, Ring::getMemberType($ring_id));

$ring_help = new RingHelp;
?>

<h2>Manage take names for <strong><?php echo $ring_name; ?></strong> Ring</h2>

    <p class="content-indent blocktext">
        These names appear on the post ring menu for all members of the ring,
        they are used to take the post in the name of the ring.
        Multiple <em>take names</em> can be created with different values and they will all appear on the ring.
    </p>
    <p class="content-indent blocktext">
        If the stream field is filled in then the take name will only appear on that stream.
    </p>

    <div class="alpha-note content-indent readable-text">
        You will need to reload the page before seeing the take name appear on the post ring menu.
    </div>
    <form method="post">

        <div class="form content-indent padded-content-block" id="create_take_name">

            <div class="row">
                <?php
                echo CHtml::label('Take Name', 'take_name');
                echo CHtml::textField('take_name', "", array('size' => 70, 'maxlength' => 50));
                echo $ring_help->takeName();
                echo CHtml::Tag('div', array('class' => 'error', 'id' => 'take_name_error'), "");
                ?>
            </div>

            <div class="row">
                <?php
                echo CHtml::label('Amount', 'amount');
                echo CHtml::textField('amount', "", array('size' => 70, 'maxlength' => 10));
                echo $ring_help->takeAmount();
                echo CHtml::Tag('div', array('class' => 'error', 'id' => 'amount_error'), "");
                ?>
            </div>

            <div class="row">
                <?php
                echo CHtml::label('Stream', 'stream');
                echo CHtml::textField('stream', "", array('size' => 70, 'maxlength' => 1024));
                echo $ring_help->takeStream();
                echo CHtml::Tag('div', array('class' => 'error', 'id' => 'stream_error'), "");
                echo CHtml::Tag('div', array('id' => 'select_div'), "<a id='select_stream' href=''>Search</a>");
                echo CHtml::Tag('div', array('class' => 'hide', 'id' => 'select_stream_selector'), "");
                ?>
            </div>

            <div class="row">
                <?php echo CHtml::hiddenField('ring_take_name_id', ''); ?>
                <?php echo CHtml::submitButton('Create', array('id' => 'create', 'class' => 'standard-button')); ?>
                <?php echo CHtml::submitButton('Clear', array('id' => 'clear', 'class' => 'standard-button')); ?>
                <?php echo CHtml::submitButton('Delete', array('id' => 'delete', 'class' => 'hide standard-button')); ?>
            </div>

        </div>

        <div id="edit_take_names" class="content-indent">

            <h3 class="no-top">Edit existing take names</h3>

            <ul>
                <?php foreach($take_names as $take_name) { ?>
                    <li>
                        <span class="hide edit-ring-take-name-id"><?php echo $take_name['ring_take_name_id']; ?></span>
                        <span class="hide edit-amount"><?php echo $take_name['amount']; ?></span>
                        <span class="hide edit-stream"><?php echo $take_name['stream_url']; ?></span>
                        <span class="edit-take-name link"><?php echo $take_name['name']; ?></span>
                    </li>
                <?php } ?>
            </ul>
        </div>

    </form>
</div>

<div id="manage_take_names_templates" class="hide">
    <ul id="take_name_line_template">
        <li>
            <span class="hide edit-ring-take-name-id"></span>
            <span class="hide edit-stream"></span>
            <span class="hide edit-amount"></span>
            <span class="edit-take-name link"></span>
        </li>
    </ul>
</div>