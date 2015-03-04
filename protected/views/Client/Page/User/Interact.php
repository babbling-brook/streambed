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
 * Template view for users to interact with their domus via an iframe.
 *
 * @fixme is this still needed?
 */
$this->layout='empty';
$this->pageTitle=Yii::app()->name . ' - Iframe to interact with users domus';

$cs = Yii::app()->getClientScript();
$cs->registerScript(
    "saltInteractData",
    "saltInteractData = { "
        . "username : '$user', "
        . "domus_username : '" . Yii::app()->request->hostInfo . "/" . $user . "', "
        . "allowed_site : $allowed_site, "
        . "};",
    CClientScript::POS_HEAD
);
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/interact.js');
?>