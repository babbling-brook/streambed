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
 * View for creating Rhythms.
 */
$cs = Yii::app()->getClientScript();
// @minfiy codemirror css
$cs->registerCssFile(Yii::app()->baseUrl . '/js/resources/codemirror/lib/codemirror.css');

$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'ManageRhythm/Update'));

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ManageRhythm/Create'));

$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/lib/codemirror.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/addon/edit/matchbrackets.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/addon/comment/continuecomment.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/codemirror/mode/javascript/javascript.js');

$this->menu = $this->operationsMenu("create");
$help = new RhythmHelp();
?>
<h2>Create Rhythm</h2>


<div class="form content-indent" id="rhythm_form">

    <p>
        See the <a href="http://www.babblingbrook.net/page/docs/rhythms" target="_blank">Babbling Brook</a>
        documentation for details on how rhythms are constructed.
    </p>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <input type="hidden" id="create_rhythm_form" value="true" />

    <div class="row">
        <label for="rhythm_name" class="inline-label required">
            Name <span class="required">*</span>
            <?php echo $help->name(); ?>
         </label>
        <input type="text" id="rhythm_name" maxlength="128" size="80" />
        <div id="rhythm_name_error" class="error internal-row hide"></div>
    </div>

    <div class="row">
        <label for="rhythm_description" class="inline-label required">
            Description <span class="required">*</span>
            <?php echo $help->description(); ?>
         </label>
        <textarea id="rhythm_description" cols="70" rows="4"></textarea>
        <div id="rhythm_description_error" class="error internal-row hide"></div>
    </div>

    <div class="row">
        <label for="rhythm_category" class="inline-label required">
            Category
            <?php echo $help->category(); ?>
         </label>
        <?php echo Rhythm::getRhythmCatDropDownList($model->extra->rhythm_cat_id); ?>
        <div id="rhythm_category_error" class="error internal-row hide"></div>
    </div>

    <div class="alpha-note readable-text">
        <p>Code mirror - the plugin used to display the JavaScript code - is not as stable as it should be.</p>
        <p>Reloading the page resolves problems.</p>
        <p>Recomend editing elsewhere and copy/pasting for now.</p>
    </div>

    <div class="row">
        <label for="rhythm_javascript" class="inline-label required">
            JavaScript code for the Rhythm <span class="required">*</span>
            <?php echo $help->javascript(); ?>
         </label>
        <textarea id="rhythm_javascript" cols="70" rows="20"></textarea>
        <div id="rhythm_javascript_error" class="error internal-row hide"></div>
    </div>

    <div class="row buttons">
        <input type="button" value="Create" id="create_rhythm" class="standard-button">
    </div>

</div>
