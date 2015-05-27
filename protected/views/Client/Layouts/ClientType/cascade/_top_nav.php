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
 * View for the main menu layout template.
 */
$fh = new FeatureHelper;
$this->widget(
    'zii.widgets.CMenu',
    array(
        'encodeLabel' => false,
        'htmlOptions' => array('id' => 'top_nav_list'),
        'items' => array(
            array(
                'label' => '',
                'url' => '',
                'itemOptions' => array('id' => 'small_screen_menu'),
            ),
            array(
                'label' => 'Tutorials',
                'url' => '',
                'itemOptions' => array('id' => 'show_tutorial'),
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'About',
                'url' => '/site/about',
                'itemOptions' => array('id' => 'about_top_nav'),
                'visible' => true,
            ),
            array(
                'label' => 'Suggestions<span id="suggestion_count" class=""></span>',
                'url' => '/',
                'itemOptions' => array('id' => 'suggestions_link'),
                'visible' => !Yii::app()->user->isGuest && $fh->isAtLevel('SUGGESTION_MESSAGES'),
            ),
            array(
                'label' => 'Streams',
                'url' => '/' . Yii::app()->user->getName() . '/streams',
                'itemOptions' => array('id' => 'streams_top_nav_link'),
                'visible' => !Yii::app()->user->isGuest && $fh->isAtLevel('MAKE_STREAMS'),
            ),
            array(
                'label' => 'Rhythms',
                'url' => '/' . Yii::app()->user->getName() . '/rhythms',
                'visible' => !Yii::app()->user->isGuest && $fh->isAtLevel('MAKE_RHYTHMS'),
            ),
            array(
                'label' => 'Rings',
                'url' => '/' . Yii::app()->user->getName() . '/ring/index',
                'visible' => !Yii::app()->user->isGuest && $fh->isAtLevel('RING_MEMBERSHIP'),
            ),
            array(
                'label' => 'Posts<span id="message_count" class="hide checking"></span>',
                'url' => '/' . Yii::app()->user->getName() . '/post',
                'visible' => !Yii::app()->user->isGuest && $fh->isAtLevel('READ_PRIVATE_POSTS'),
            ),
            array(
                'label' => 'Profile',
                'url' => '/' . Yii::app()->user->getName() . '/profile',
                'itemOptions' => array('id' => 'profile_top_nav_link'),
                'visible' => !Yii::app()->user->isGuest  && $fh->isAtLevel('EDIT_PROFILE'),
            ),
            array(
                'label' => 'Settings',
                'url' => '/' . Yii::app()->user->getName() . '/settings',
                'visible' => !Yii::app()->user->isGuest  && $fh->isAtLevel('SETTINGS'),
            ),
            array(
                'label' => 'Login',
                'itemOptions' => array('id' => 'login'),
                'url' => 'https://' . HOST . '/site/login',
                'visible' => Yii::app()->user->isGuest,
            ),
            // @fixme Extend CWebUser to include domain so it can be displayed as needed
            array(
                'label' => 'Logout (' . Yii::app()->user->name . ')',
                'itemOptions' => array('id' => 'login'),
                'url' => '/site/logout',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Signup',
                'url' => 'https://' . HOST . '/site/signup',
                'visible' => Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Report Bug',
                //'url' => array('#'),
                'itemOptions' => array('id' => 'small_bug'),
                'visible' => !Yii::app()->user->isGuest && $fh->isAtLevel('BUGS'),
                'linkOptions' => array('href' => '#', 'class' => 'link'),
            ),
        ),
    )
);
?>
