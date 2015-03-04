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
 * Template view for users to edit their profile.
 */

$cs = Yii::app()->getClientScript();
// @todo minify qqfileuploader css and js
$cs->registerCssFile(Yii::app()->baseUrl . '/css/libraries/QqFileuploader.css');
$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'Profile/Edit'));

$cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery_pluggins/QqFileuploader.js');
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'User/EditProfile'));

$help = new EditProfileHelp();

if ($ring_id !== false) {
    $this->menu_title = "Ring Admin";
    $this->menu = RingController::adminMenu(Ring::getAdminType($ring_id), Ring::getMemberType($ring_id));
}
?>

<h2>Edit Profile</h2>
<div class="form content-indent" id="edit_profile">
    <input type="hidden" id="username_for_profile" value="<?php echo $_GET['user'];?>" />

    <?php
    $form = $this->beginWidget(
        'ActiveForm',
        array(
            'id' => 'profile_form',
            'enableAjaxValidation' => false,
        )
    );
    ?>

    <div id="view_profile" class="content-block-2 larger">
        <a href='profile'>View your profile</a>
    </div>

    <div id="profile_username" class="row">
        Username: <?php echo $_GET['user']; ?>
    </div>

    <div id="select_image" class="row">
        <?php
        $profile_username = str_replace(' ', '-', $_GET['user']);
        $profile_domain = HOST;
        $rand = '?' . rand(1, 10000);
        $profile_image = '/images/user/' . $profile_domain . '/' . $profile_username . '/profile/small/profile.jpg';
        $image_path = realpath(Yii::app()->basePath . "/../") . $profile_image;
        if (file_exists($image_path) === false) {
            $profile_image = '/images/default_user_large.png';
        }
        ?>
        <div class="internal-row">
            <label for='UserProfile_profile_image'>
                Profile image
                <?php echo $help->profileImage(); ?>
            </label>
        </div>
        <div class="internal-row">
            <img id="profile_image" class="profile-photo left" src='<?php echo $profile_image . $rand; ?>' />
            <div id="new_profile_image" class="left" href=""></div>
        </div>
    </div>

    <?php
    $hide = "";
    if ($ring_id !== false) {
        $hide = "hide";
    }
    ?>
    <div id="real_name" class="row clear <?php echo $hide; ?>">
        <div class="internal-row">
            <label for='UserProfile_profile_image'>
                Name
                <?php echo $help->realName(); ?>
            </label>
        </div>
        <div class="internal-row">
            <?php
            echo CHtml::activeTextField(
                $model,
                'real_name',
                array('class' => 'edit-profile-field', 'data-field_name' => 'real_name')
            );
            ?>
        </div>
        <div class="error internal-row hide"></div>
    </div>

    <div id="about" class="row">
        <div class="internal-row">
            <label for='UserProfile_profile_image'>
                About
                <?php echo $help->about(); ?>
            </label>
        </div>
        <div class="internal-row">
            <?php
            echo CHtml::activeTextArea(
                $model,
                'about',
                array('rows' => 10, 'cols' => 70, 'class' => 'edit-profile-field', 'data-field_name' => 'about')
            );
            ?>
        </div>
        <div class="error internal-row hide"></div>
    </div>

    <?php $this->endWidget(); ?>
</div>

<div id="edit_profile_templates" class="hide">
    <div id="edit_profile_uploader_template">
        <div class="QqFileuploader-uploader">
            <div class="QqFileuploader-upload-drop-area">
                <span>Drop files here to upload</span>
            </div>
            <div class="QqFileuploader-upload-button">Upload a profile image - click or drag</div>
            <ul class="QqFileuploader-upload-list"></ul>
        </div>
    </div>
</div>