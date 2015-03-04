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
 * View for a users profile. Used if the viewer is not logged on. See profileshell for profile view when logged on.
 */

$this->pageTitle=Yii::app()->name . ' - ' . $username . ' Profile';

$this->renderPartial('/Shared/Layouts/_page_css', array('page' => 'User/Profile'));

$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Library'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'ModalLogin'));
$this->renderPartial('/Shared/Layouts/_page_js', array('page' => 'Profile'));
?>

<h2>Profile for <em><?php echo ($username); ?></em></h2>
<div id="profile" class="content-indent">
    <div class="profile-photo">
        <?php
        $profile_domain = HOST;
        $profile_image = '/images/user/' . $profile_domain . '/' . $username . '/profile/large/profile.jpg';
        if (file_exists(realpath(Yii::app()->basePath . "/../") . $profile_image) === false) {
            $profile_image = '/images/default_user_large.png';
        }
        ?>
        <img class="profile-photo" src='<?php echo $profile_image ?>' />
    </div>

    <h3 id="real_name" class="row">
        Name:
    </h3>
    <div id="name_content" class="end">
        <?php
        if ($user_model->real_name === null) {
            echo "This user has elected to remain anonymous.";
        } else {
            echo $user_model->real_name;
        }
        ?>
    </div>

    <h3 id="about"  class="row">
        About Me:
    </h3>
    <div id="about_content" class="end">
        <?php
        if ($user_model->real_name === null) {
            echo "This user has not entered any details.";
        } else {
            echo $user_model->about;
        }
        ?>
    </div>

    <div id="details" class="clear">

        <a href="/site/login">Login</a> to see relationships.

    </div>
</div>