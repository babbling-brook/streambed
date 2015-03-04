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
 * Partial view for navigation between different messaging pages.
 */
?>
<div id="sidebar">
    <ul id="sidebar_extra">
        <li class="title">
            <h3>
                Posts
            </h3>
        </li>
        <li class="sidebar-link">
            <a href="/<?php echo Yii::app()->user->getName()?>/post/localpost">
                Local Inbox
                <span id="local_message_count" class="hide checking"></span>
            </a>
        </li>
        <?php if (Yii::app()->user->getSiteID() === Yii::app()->params['site_id']) { ?>
            <li class="sidebar-link">
                <a href="/<?php echo Yii::app()->user->getName()?>/post/globalpost">
                    Global Inbox
                    <span id="global_message_count" class="hide checking"></span>
                </a>
            </li>
        <?php } ?>
        <li class="sidebar-link">
            <a href="/<?php echo Yii::app()->user->getName()?>/post/localsent">Local Sent Items</a>
        </li class="sidebar-link">
        <?php if (Yii::app()->user->getSiteID() === Yii::app()->params['site_id']) { ?>
            <li class="sidebar-link">
                <a href="/<?php echo Yii::app()->user->getName()?>/post/globalsent">Global Sent Items</a>
            </li>
        <?php } ?>
        <li class="sidebar-link">
            <a href="/<?php echo Yii::app()->user->getName()?>/post/compose">Compose</a>
        </li>
    </ul>
</div>