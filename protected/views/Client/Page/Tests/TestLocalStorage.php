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
 * View for testing the suggestion subdomain.
 */

$this->pageTitle=Yii::app()->name . ' - Test Local Stroage';

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Tests/TestLocalStorage'));
?>

<h2 id="qunit-header">Test Local Storage</h2>

<div class="content-indent">
    <p>
        <span id="test_local_storage" class="link">Test Local Storage</span>
    </p>
    <div id="test_results"></div>
</div>