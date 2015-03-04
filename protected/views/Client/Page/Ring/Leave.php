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
 * View for a user to leave a ring.
 */

$this->pageTitle=Yii::app()->name . ' - Leave the ' . $ring_name . ' Ring';

$cs = Yii::app()->getClientScript();
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Ring/Leave'));

$this->menu_title = "Member pages";
$this->menu = RingController::memberMenu($type);
?>

<h2>Leave the <strong><?php echo $ring_name; ?></strong> Ring</h2>

<div class="content-indent">

    <form method="post">
        <div class="form">

            <div class="row">
                Please confirm that you want to cancel your membership for
                the <strong><?php echo $ring_name; ?></strong> Ring.
            </div>

            <div class="row">
                <input id="ring_name" type="hidden" id="ring_name" value="<?php echo $ring_name; ?>" />
                <input id="ring_domain" type="hidden" id="ring_domain" value="<?php echo $ring_domain; ?>" />
                <input id="confirm_leave_button" type="button" class="standard-button" value="Confirm" />
            </div>

        </div>
    </form>

</div>