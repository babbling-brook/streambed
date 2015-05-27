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

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Password'));

?>

<h2>Password</h2>

<div class="content-indent" id="password_page">
    <form method="post" name="password">
        <div class="content-block-2 hide" id="remote_site_row">
            Enter your password to grant
            <strong><span id="remote_site_name"></span></strong> access  to your data store.
        </div>

        <div class="content-block-2 hide readable-text" id="local_site_row">
            Enter your password to log into your data store.
        </div>

        <div class="row">
            <label class="inline-label label" for="username">
                Username:
            </label>
            <span class="disabled internal-row" id="username"><?php echo $username; ?></span>
        </div>

        <div class="row">
            <label class="label" for="password">
                Password:
            </label>
            <div class="internal-row">
                <input type="password" id="password" name="password">
            </div>
            <?php if (isset($error) === true) { ?>
                <div id="password_error" class="error"><?php echo $error; ?></div>
            <?php } ?>
        </div>

        <div class="row">
            <label class="label inline-label" for="remember_me">
                Remember Me:
            </label>
            <?php
            $remember_me_status = '';
            if (isset($remember_me) === true && $remember_me === 'yes') {
                $remember_me_status = "checked";
            }
            ?>
            <input type="checkbox" id="remember_me" name="remember_me" value="yes" <?php echo $remember_me_status; ?>>
        </div>

        <div class="row submit">
            <input type="button" id="password_submit" class="standard-button" value="Login">
        </div>

        <div class="row">
            <?php
            $reset_querystring = '?username=' . $_GET['username'];
            if (isset($_GET['secret']) === true) {
                $reset_querystring .= '&secret=' . $_GET['secret'];
            }
            if (isset($_GET['client_domain']) === true) {
                $reset_querystring .= '&client_domain=' . $_GET['client_domain'];
            }
            ?>
            <a href="/site/passwordreset<?php echo $reset_querystring; ?>">Reset your password</a>
        </div>
    </form>
</div>

<?php // Hidden field modified by javascript to let selenium know that the constructor has run. ?>
<input type="hidden" id="password_constructor_has_run" value="false" />