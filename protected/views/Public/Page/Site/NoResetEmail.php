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
 * View for the password part of a login request.
 * @fixme remove the need for jQuery on this page. It is only needed for minor things but adds a lot to load time.
 */
$this->pageTitle=Yii::app()->name . ' - Password';

$cs = Yii::app()->getClientScript();

?>

<h2>Password Reset</h2>

<div class="content-indent">

    <div class="content-block-2" id="no_email_for_reset">
        You did not provide an email address when you signed up, so we are unable to send you a password reset.
    </div>

    <div class="row">
        <a href="/site/password<?php echo $login_querystring; ?>">Return to the password page</a>
    </div>

    <?php if (isset($_GET['client_domain']) === true) { ?>
        <div class="row">
            <a href="http://<?php echo $_GET['client_domain']; ?>">
                Return to the <?php echo $_GET['client_domain']; ?> website
            </a>
        </div>
    <?php } ?>

</div>