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

// Used by an admin user to create aditional signup codes.

$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/Admin/SignupCodes/create.js' . $this->js_version_number);

$this->menu=array(
    array('label' => 'Create Signup Codes', 'url' => '/site/admin/signupcodes/create'),
);
?>
<h2>Create Signup Codes</h2>

<div class="form content-indent padded-content-block" id="create_signup_codes_form">

    <div class="row">
        <label class="block-label">Primary Category</label>
        <input id="primary_category" type="text" />
        <div class="error hide" id="primary_category_error"></div>
    </div>

    <div class="row">
        <label class="block-label">Secondary Category</label>
        <input id="secondary_category" type="text" />
        <div class="error hide" id="secondary_category_error"></div>
    </div>

    <div class="row">
        <label class="block-label">Quantity</label>
        <input id="qty" type="text" />
        <div class="error hide" id="qty_error"></div>
    </div>

    <div class="row">
        <input id="save" class="standard-button" type="button" value="Save" />
        <input id="clear" class="standard-button" type="button" value="Clear Form" />
    </div>

    <div id="success" class="row success hide">
        The new codes have been added to the database.
    </div>

    <div id="error" class="row error hide">
        There was an error saving the new signup codes.
    </div>

</div>