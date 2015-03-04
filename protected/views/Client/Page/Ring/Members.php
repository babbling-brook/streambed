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
 * View for a ring members options menu.
 */
$this->pageTitle=Yii::app()->name . ' - ' . $ring_name . ' Ring members area';

$this->menu_title = "Member pages";
$this->menu = RingController::memberMenu($type);
?>

<h2>Members area for the <strong><?php echo $ring_name; ?></strong> Ring</h2>

<div class="content-indent larger blocktext">
    <?php echo $user_profile_model->about; ?>
</div>

<div class="content-indent">
    <p>
        As a member of this ring you can now take posts in the name of this ring. On any post, click on the
        'ring' link and a menu will pop up where you can select which 'take' you want to make.
    </p>
</div>