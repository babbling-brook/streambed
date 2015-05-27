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
 * View for testing the sites javascript.
 */

$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/Tests/All.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/BackboneModel/Test.js');

?>
<h2 >JavaScript Tests </h2>
<div id="sandbox"></div>
<div class="content-indent">
    <ul>
        <li>
            <a class="open_blank_target" href="/site/tests/javascriptall">Run all tests</a>
        </li>
    </ul>

</div>
