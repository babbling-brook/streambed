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
 * View for a list of streams.
 */

$cs = Yii::app()->getClientScript();
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageStream/Edit'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageStream/Tag'));

// @todo minify token-input and bbq
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery_pluggins/jquery.tokeninput.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery_pluggins/salt.jquery.ba-bbq.js');

$help = new StreamHelp();
?>

<div id="edit_stream_plane">

    <?php if (intval($model->extra->status_id) !== 1) { ?>
        <div class="no-editing" class="row">
            <p>
                Stream details are only editable if the stream is private.
                If no one else has made any posts then you can revert a published stream
                back to private on the <a href="update">actions tab</a>.
            </p>
            <p>
                Alternatively create a new version or duplicate to return to private mode.
            </p>
        </div>
    <?php }  else { ?>


        <h3 class="form">Edit Stream</h3>

        <div class="row">
            <?php
            $loading = CHtml::Tag('span', array('class' => 'label-loading hidden', 'id' => 'description_loading'), "");
            echo CHtml::Label(
                'Description' . $help->description() . $loading,
                'description',
                array('class' => 'inline-label')
            );
            echo CHtml::TextArea('description', $model->extra->description, array('rows' => 6, 'cols' => 50));
            echo CHtml::Tag('div', array('class' => 'error', 'id' => 'description_error'), "");
            ?>
        </div>



        <div class="row">
            <?php
                echo CHtml::label(
                    'Presentation type' . $help->presentationType(),
                    'presentation_type',
                    array('class' => 'inline-label')
                );
                $presentation_types = LookupHelper::getDropDown('stream_extra.presentation_type_id');
                echo CHtml::dropDownList(
                    'presentation_type',
                    LookupHelper::getValue($model->extra->presentation_type_id),
                    $presentation_types
                );
            echo CHtml::Tag('div', array('class' => 'error', 'id' => 'presentation_type_error'), "");
            ?>
        </div>

        <div class="row">
            <?php
            echo Html::label(
                'Child Streams',
                'child_streams',
                array('class' => 'inline-label'), $help->children()
            );
            ?>
            <div id="child_streams_container"></div>
        </div>


        <div class="row">
            <?php
            echo Html::label(
                'Default Sort Rhythms',
                'default_sort_rhythms',
                array('class' => 'inline-label'), $help->defaultSortRhythms()
            );
            ?>
            <div id="default_sort_rhythms_container"></div>
        </div>

        <div class="row">
            <?php
            echo Html::label(
                'Default Moderation Rings',
                'moderation_rings',
                array('class' => 'inline-label'), $help->defaultModerationRings()
            );
            ?>
            <div id="moderation_rings_container"></div>
        </div>

        <?php } ?>
    </div>