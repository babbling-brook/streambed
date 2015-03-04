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
 * View for the user signup page.
 *
 * @fixme after signing up the user is forwarded to their profile page... but they are not logged in.
 *          Should forward to the first default stream subscription.
 */
$cs = Yii::app()->getClientScript();
//if (Yii::app()->user->isGuest === false) {
//    throw new Exception('User should have been redirected.');
//}

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Signup'));

if (Yii::app()->user->isGuest === true) {
    ?>

    <h2>Signup</h2>


    <div class="content-indent form">

        <div class="alpha-note readable-text">
            Cobalt Cascade is currently in early Alpha. <br/>Feel free to play, but expect things to break!
        </div>

        <?php
        $form=$this->beginWidget(
            'ActiveForm',
            array(
                'id' => 'signup_form',
                'enableAjaxValidation' => false,
                'errorMessageCssClass' => 'error',
            )
        );
        ?>

        <div class="row" id="username">
            <label class="label" for="username">
                Username *
                <span id="username_help_icon" class="help-icon smaller-icon"></span>
            </label>
           <div id="username_help" class="hide">
                <p>
                    Your username is a combination of this sites name and your chosen name and
                    looks like an email address. (In the future you will be able to use it as one.)
                    E.G. <em>yourusername&#64<?php echo Yii::app()->params['host']; ?></em>
                </p>
                <p>
                    This enables you to connect to your Datastore on any site that uses the Babbling Brook network.
                </p>
                <p>
                    If you already have a username from another site on the Babbling Brook network then you do not
                    need to sign up, just <a href="/site/login">log in</a> with that username.
                </p>
            </div>
            <div class="internal-row">
                <?php echo $form->textField($model, 'username', array('maxlength' => 128)); ?>
                @<?php echo Yii::app()->params['host']; ?>
                <?php echo $form->error($model, 'username'); ?>
            </div>
        </div>

        <div class="row">
            <label class="label" for="UserSignupForm[password]">
                Password *
            </label>
            <div class="internal-row">
                <?php echo $form->passwordField($model, 'password', array('maxlength' => 128)); ?>
                <?php echo $form->error($model, 'password'); ?>
            </div>
        </div>

        <div class="row">
            <label class="label" for="UserSignupForm[verify_password]">
                Verify Password *
            </label>
            <div class="internal-row">
                <?php
                echo $form->passwordField(
                    $model,
                    'verify_password',
                    array('maxlength' => 128)
                );
                ?>
                <?php echo $form->error($model, 'verify_password'); ?>
            </div>
        </div>

        <?php if (Yii::app()->params['use_signup_codes'] === true) { ?>
            <div class="row">
                <label class="label" for="UserSignupForm[signup_code]">
                    Activation Code *
                </label>
                <div class="internal-row">
                    <?php echo $form->textField($model, 'signup_code', array('maxlength' => 128)); ?>
                    <?php echo $form->error($model, 'signup_code'); ?>
                </div>
            </div>
        <?php } ?>


        <div class="row">
            <label class="label" for="username">
                Email
                <span id="email_help_icon" class="help-icon smaller-icon"></span>
            </label>
            <div id="email_help" class="hide">
                <p>
                    Your email is only used for password recovery and opt-in features.
                    If another Babbling Brook site requests use of your email address you will be informed before
                    you log onto their site.
                </p>
            </div>
            <div class="internal-row">
                <?php echo $form->textField($model, 'email', array('maxlength' => 128)); ?>
                <?php echo $form->error($model, 'email'); ?>
            </div>
        </div>

        <div class="row buttons">

            <?php echo $form->hiddenField($model, 'test_ok'); ?>
            <?php echo CHtml::submitButton('Signup', array('class' => 'standard-button')); ?>
        </div>

    <?php $this->endWidget(); ?>



    </div><!-- form -->


<?php $this->renderPartial('/Public/Page/Site/_old_browser'); ?>

<?php } ?>