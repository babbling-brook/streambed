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
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/Admin/DeleteTestUsers.js' . $this->js_version_number);
?>
<h2>
    Delete test accounts
</h2>

<div class="content-indent">
    <p>
        <button class="standard-button" id="delete_all_test_users">Delete all test users</button>

        <div class="success hide" id="test_all_users_success">
            All Test users have been deleted.
        </div>
    </p>
    <p>
        <br/>
    </p>
    <p>
        <input type="text" name="test_user" id="test_username"></input>
        <button class="standard-button" id="delete_one_test_user">Delete this test user name</button>
        <div class="error hide" id="no_test_user_error">
            Please enter a test user.
        </div>
        <div class="error hide" id="test_user_error"></div>
        <div class="success hide" id="test_user_success">
            Test user has been deleted.
        </div>
    </p>
</div>