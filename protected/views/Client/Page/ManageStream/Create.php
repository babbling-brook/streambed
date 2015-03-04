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
 * View for the creating of streams.
 */
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'ManageStream/Update'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageStream/Create'));

$this->menu = $this->operationsMenu("");
$help = new StreamHelp();
?>

<h2>Create Stream</h2>

<div id="stream_form" class="form content-indent">
    <p>
        See the <a href="http://www.babblingbrook.net/page/docs/streams" target="_blank">Babbling Brook</a>
        documentation for details on how streams are constructed.
    </p>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <div class="row">
        <label class="inline-label" for="stream_name">
            Name <span class="required">*</span>
            <?php echo $help->name(); ?>
        </label>
        <input type="text" value="" id="stream_name" maxlength="128" size="60">
        <div id="stream_name_error" class="error hide"></div>
    </div>

    <div class="row">
        <label class="inline-label" for="stream_description">
            Description <span class="required">*</span>
            <?php echo $help->description(); ?>
        </label>
        <textarea id="stream_description" cols="50" rows="6"></textarea>
        <div id="stream_description_error" class="error hide"></div>
    </div>


    <div class="row">
        <label class="inline-label" for="stream_name">
            Select the kind of Stream <span class="required">*</span>
            <?php echo $help->kind(); ?>
        </label>
        <?php
            echo CHtml::dropDownList(
                'kind',
                '',
                array_flip(LookupHelper::getValues('stream.kind'))
            );
        ?>
    </div>

    <div class="row">
        <div class="inline-label">
            Fields
        </div>
        Fields can be added to the stream once it has been created.
    </div>

    <div class="row buttons">
        <input type="button" value="Create" id="create_stream" class="standard-button">
    </div>

</div>