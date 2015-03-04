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
 * View for an accepted ring invite.
 */

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Ring/VetMembershipRequests'));
$this->pageTitle=Yii::app()->name . ' - Vet Ring Membership Requests';

$this->menu = RingController::adminMenu(Ring::getAdminType($ring_id), Ring::getMemberType($ring_id));
?>
<h2>Vet Membership Requests</h2>

<div id="ring_membership_request_list" class="content-indent"></div>

<p class="content-indent extra-margin-top">
    Note: Banned users will not show up on this list.
</p>

<div id="vet_membership_requests_templates" class="hide">
    <div id="on_ring_membership_request_accepted_error_template">
        There was an error whilst accepting the membership request for
        <span class="ring-request-user"></span> in the <span class="ring-request-ring"></span> ring.
    </div>

    <div id="on_ring_membership_request_declined_error_template">
        There was an error whilst declining the membership request for
        <span class="ring-request-user"></span> in the <span class="ring-request-ring"></span> ring.
    </div>

    <div id="on_membership_request_banned_error_template">
        There was an error whilst banning
        <span class="ring-request-user"></span> in the <span class="ring-request-ring"></span> ring.
    </div>
</div>
