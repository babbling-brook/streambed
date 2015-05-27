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
 * View for post pages.
 */

$cs = Yii::app()->getClientScript();
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Stream/Stream'));
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Stream/Stream'));


$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(
    Yii::app()->baseUrl . '/js/Minified/resources/jquery.justifiedGallery.min.js' . $this->js_version_number
);
//$cs->registerScriptFile(
//    Yii::app()->baseUrl . '/js/resources/Justified-Gallery-3.5.4/js/justifiedGallery.js' . $this->js_version_number
//);
$cs->registerCssFile(Yii::app()->baseUrl . '/css/Minified/Libraries/justifiedGallery.min.css');

$this->renderPartial('/Shared/Page/Stream/_side_description');

//$this->menu = $this->operationsMenu("take");

echo CHtml::hiddenField("user_id", Yii::app()->user->getId());
//@fixme Should not be using a user_id here on the client.

//Whatever is entered here is automaticly picked up and sent to domus domain for sorting
?>

<?php //Causes the stream constructor to be fired straight away. ?>
<?php if (isset($not_on_stream_page) === false) { ?>
    <input id="on_stream_page" type="hidden" value="true" />
<?php } ?>


<div class="make-post readable-text block-loading make-stream-post content-indent hide"></div>

<div id="stream_container" class="block-loading content-indent"></div>


<div class="hide">
    <div id="stream_not_found_template">
        <div>
            <h2 class="error">
               Error 404. Stream not found.
            </h2>
        </div>
    </div>
</div>
