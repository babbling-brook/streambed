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
 * View for a user to view their messaging inbox.
 *
 * Variables that are required for this view:
 * $global bollean Is this a global or local inbox.
 */
$this->pageTitle=Yii::app()->name . ' - Local Inbox';

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Mail'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Mail/GlobalInbox'));
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Mail/Inbox'));

$this->menu_extra = $this->renderPartial('/Client/Page/Mail/_navigation', array(), true);
?>

<h2>Global Inbox</h2>

<div class="content-indent post-box">

    <div class="content-block">
        Posts that have been sent from other users on all sites in the BabblingBrook network.
    </div>

    <div id="post_list" class="block-loading"></div>

    <?php $this->renderPartial('/Client/Page/Mail/_template'); ?>

</div>