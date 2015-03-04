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
 * View for testing the domus subdomain.
 */

$this->layout='blank';
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/salt.jquery.js' . $this->js_version_number);
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/json2.js' . $this->js_version_number);
// Creates an empty console object. Removing console messages.
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/tests/qunit.js' . $this->js_version_number);
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/tests/test_domus.js' . $this->js_version_number);
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/Interact.js' . $this->js_version_number);
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/LocalStorage.js' . $this->js_version_number);
$cs->registerCssFile(Yii::app()->baseUrl . '/css/tests/qunit.css');
?>

<h1 id="qunit-header">Domus IFrame Tests</h1>
<h2 id="qunit-banner"></h2>
<div id="qunit-testrunner-toolbar"></div>
<h2 id="qunit-userAgent"></h2>
<ol id="qunit-tests"></ol>