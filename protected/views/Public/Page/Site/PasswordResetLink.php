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
 *
 * @fixme remove the need for jQuery on this page. It is only needed for minor things but adds a lot to load time.
 */
$this->pageTitle=Yii::app()->name . ' - Password';

$cs = Yii::app()->getClientScript();

?>

<h2>Password Reset Sent</h2>

<div class="content-indent">

    <div class="row">
        Please enter a new password for username <?php echo $_GET['username'] ?>.
    </div>

    <form method="post">

        <div class="row">
            <label for="new_password" >
                New Password
            </label>
            <input name="new_password" type="text" value="<?php echo $new_password; ?>" />
        </div>

        <div class="row">
            <label for="verify_new_password">
                Verify New Password
            </label>
            <input name="verify_new_password" type="text" value="<?php echo $verify_new_password; ?>" />
        </div>

        <?php if ($error !== false) { ?>
            <div class="row error">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <div class="row">
            <input type="submit" class="standard-button" value="Change password" />
        </div>

    </form>

</div>