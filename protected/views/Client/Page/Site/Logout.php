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
 * View for users to logout.
 */
$this->pageTitle=Yii::app()->name . ' - Logout';
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Site/Logout'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Site/Logout'));

?>

<h2>Logout</h2>

<div class="content-indent">

    <div id="logout_message" class="content-block">
        Logout in process...
    </div>

</div>

<div id="logout_failed_template" class="hide">
    Error. Data store has failed to log out.
    <a id="logoutall" href="">
    Go to your data store</a> to logout.
</div>