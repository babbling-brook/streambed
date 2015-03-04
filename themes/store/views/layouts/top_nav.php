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

$this->widget(
    'zii.widgets.CMenu',
    array(
        'encodeLabel'=>false,
        'items' => array(

            array('label' => 'Signup', 'url' => array('/site/signup'), 'visible' => Yii::app()->user->isGuest),
            array(
                'label' => 'Logout (' . Yii::app()->user->name . ')',
                'itemOptions' => array('id' => 'login'),
                'url' => '/site/logout',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Login',
                'itemOptions' => array('id' => 'login'),
                'url' => array('/site/login'),
                'visible' => Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Client Sites',
                'url' => '/' . Yii::app()->user->getName() . '/ring/index',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Account',
                'url' => '/' . Yii::app()->user->getName() . '/ring/index',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Rings',
                'url' => '/' . Yii::app()->user->getName() . '/ring/index',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Rhythms',
                'url' => '/' . Yii::app()->user->getName() . '/rhythms',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Streams',
                'url' => '/' . Yii::app()->user->getName() . '/streams',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Profile',
                'url' => '/' . Yii::app()->user->getName() . '/profile',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Posts<span id="message_count" class="hide checking"></span>',
                'url' => '/' . Yii::app()->user->getName() . '/post',
                'visible' => !Yii::app()->user->isGuest,
            ),
            array(
                'label' => 'Messages<span id="message_count" class="hide checking"></span>',
                'url' => '/' . Yii::app()->user->getName() . '/post',
                'visible' => !Yii::app()->user->isGuest,
            ),
        ),
    )
);
?>
