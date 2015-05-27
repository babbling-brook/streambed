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
 * The index view for this iframe.
 */
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Tests/SetupTestCookie'));

$this->pageTitle=Yii::app()->name . ' - Test';
?>

<h2>Tests</h2>

<div class='content-indent'>
    <p>
        <div>Testing cookie is currently set to : <span id='test_cookie_status'></span>.</div>
        <div><a id="switch_cookie_state" href="#">Switch cookie testing state</a></div>
    </p>
    <p>
        <div>Reset Test Data</div>
        <div>Copy the url. A link would be picked up by ajax url.
            <code>
http://<?php echo HOST; ?>/tests/Restore
            </code>
    </p>
    <ul>
        <li>
            <a href="/site/tests/javascript">Javascript Tests</a>
        </li>
    </ul>
</div>
