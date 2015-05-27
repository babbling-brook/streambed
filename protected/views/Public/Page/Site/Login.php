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
 * View for the user login page.
 */
$this->pageTitle=Yii::app()->name . ' - Login';
$cs = Yii::app()->getClientScript();
?>

<h2>Login</h2>

<div id="client_login" class="content-indent">


    <div id="ok_browser">

        <div class="content-block-2 readable-text" id="login_info">
            Enter your Babbling Brook Username
            <a id="login_help" href="/site/login#helpopen" class="small-help-icon" src="/images/ui/help.svg"></a>
        </div>

        <div id="login_help_text" class="hide">
            <p>
                The <a href="http://www.babblingbrook.net" target="_blank">Babbling Brook</a>
                network is an open social network that allows you to store and use your social
                data wherever you want. If you don't have a Babbling Brook account, then
                <a href="/site/signup">create one</a>.
            </p>
            <p>
                If your Babbling Brook network account is on this site then you can login with the short version
                of your username.
                For example, if your full account name is <em>yourname@<?php echo Yii::app()->params['host']; ?></em>
                then your
                short name is just <em>yourname</em>. You will then be prompted for your password.
            </p>
            <p>
                If your Babbling Brook network account is on another site,
                then enter your full username and you will be redirected there
                so that you can enter your password. You will then be automatically returned to this site.
            </p>
        </div>

        <div class="row">
            <label class="label" for="username">
                Username:
            </label>
            <div class="internal-row">
                <input type="text" id="username">
            </div>
            <div id="login_error" class="hide error"></div>
        </div>

        <div class="row submit">
            <input type="button" id="login_submit" class="standard-button loggin-button" value="Login">
        </div>

        <div class="row">
            Not a member? <a href='https://<?php echo HOST;?>/site/signup'>Signup here</a>
        </div>

    </div>

    <?php $this->renderPartial('/Public/Page/Site/_old_browser'); ?>

</div>

<div id="request_signup_code" class="hide">
    <div class="row larger">
        This site is closed to new users unless you have an activation code.
    </div>

    <div class="row">
        <label class="label" for="signup_code">
            Activation Code:
        </label>
        <div class="internal-row">
            <input type="text" id="signup_code">
        </div>
        <div id="signup_code_error" class="hide error">Activation code not found.</div>
    </div>

    <div class="row submit">
        <input type="button" id="signup_code_submit" class="standard-button" value="Login">
    </div>
</div>

<?php // Hidden field modified by javascript to let selenium know that the constructor has run. ?>
<input type="hidden" id="login_constructor_has_run" value="false" />
