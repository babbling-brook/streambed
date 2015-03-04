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
 * View for a rings being updated by admins.
 */

$this->pageTitle = Yii::app()->name . ' - Update Ring ' . $model->name;

$this->menu_title = "Ring Admin";
$this->menu = RingController::adminMenu($admin_type, Ring::getMemberType($ring_id));
?>
<h2>Update Ring <strong><?php echo $model->name; ?></strong></h2>

<div class="alpha-note readable-text">
    <p>
        Membership by rhythm and membership by super ring are currently not working.
    </p>
    <p>
        Also the Ring Rhythm is not currently running.
    </p>
</div>

<div id="update_ring" class="content-indent">

    <?php echo $this->renderPartial('/Client/Page/Ring/_form', array('model' => $model, 'ring_id' => $ring_id)); ?>

</div>