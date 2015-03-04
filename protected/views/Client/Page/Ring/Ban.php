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
 * View for banning an invite.
 */

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Ring/Ban'));
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Ring/Ban'));

$this->pageTitle=Yii::app()->name . ' - Ban users from a Ring';

$this->menu_title = "Ring Admin";
$this->menu = RingController::adminMenu(Ring::getAdminType($ring_id), Ring::getMemberType($ring_id));
?>
<h2>Ban users from the <strong><?php echo $ring_name; ?></strong> Ring</h2>

<div id="ring_member_list" class="content-indent"></div>

<div id="ring ban_members_templates" class="hide">
    <div id="on_ring_member_banned_error_template">
        There was an error whilst banning
        <span class="ring-request-user"></span> in the <span class="ring-request-ring"></span> ring.
    </div>

    <div id="on_ring_member_reinstated_error_template">
        There was an error whilst reinstating
        <span class="ring-request-user"></span> to the <span class="ring-request-ring"></span> ring.
    </div>
</div>