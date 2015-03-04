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
 * Layout view for all generic client domain config data.
 *
 * This is placed into BabblingBrook.Client.ClientConfig
 */

$config = array(
    "host" => Yii::app()->params['host'],
    'ajaxurl' => Yii::app()->params['ajaxurl'],
    // @fixme this needs moving to the domus config
    'single_domus_iframe' => Yii::app()->params['single_domus_iframe'],
    'active_components' => Yii::app()->params['active_components'],
    'bug_stream' => array(
        'domain' => HOST,
        'username' => 'sky',
        'name' => 'bugs',
        'version' => array(
            'major' => 'latest',
            'minor' => 'latest',
            'patch' => 'latest',
        ),
    ),
    'default_sort_filters' => Yii::app()->params['default_sort_filters'],
);
echo (CJSON::encode($config));
?>