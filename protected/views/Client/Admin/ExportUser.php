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



$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/Admin/ExportUser.js' . $this->js_version_number);
?>
<h2>
    Export User
</h2>

<div class="content-indent">
    <p>
        <input type="text" id="export_username"></input>
        <button class="standard-button" id="export_user">Export This User</button>
        <div class="note">
            Use email format. If the domain is omitted then the local host is used.
        </div>
        <div class="error hide" id="server_error"></div>
    </p>
    <p>
        <textarea class="hide readable-text" id="exported_user">
        </textarea>
    </p>
</div>