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

/*
 * Loads the javascript that redirects back to the client website after login.
 * Needs to happen in JS so that the rememberme cookie can be saved.
 */

$this->pageTitle=Yii::app()->name . ' - Redirecting after login';
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Public/Redirect.js');
?>
Logged in. Sending back to <?php echo $client_domain; ?>.
<input type="hidden" id="secret" value="<?php echo $secret ?>" />
<input type="hidden" id="client_domain" value="<?php echo $client_domain ?>" />
<input type="hidden" id="remember_time" value="<?php echo $remember_time ?>" />