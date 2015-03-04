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
 * View for a user to compose a new message.
 */
$this->pageTitle=Yii::app()->name . ' - Compose a new Message';

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Mail'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Mail/Compose'));

$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Mail/Inbox'));
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Mail/Compose'));

 $help = new MessagingHelp();

$this->menu_extra = $this->renderPartial('/Client/Page/Mail/_navigation', array(), true);
?>

<h2>Compose</h2>
<div class="content-indent private-posts">

    <div id="compose_post">
        <div class="make-post readable-text block-loading"></div>
    </div>

    <div id="recent_posts_container" class="hide">
        <h3>Sent</h3>
        <div id="recent_posts">
        </div>
    </div>

    <?php $this->renderPartial('/Client/Page/Mail/_template'); ?>

</div>

<div id="compose_templates" class="hide">
    <div id="dummy_compose_template">
        <div id="dummy_post"></div>
    </div>
</div>