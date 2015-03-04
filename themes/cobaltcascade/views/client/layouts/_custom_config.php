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
 * Layout view for all custom client domain config data.
 *
 * This is placed into BabblingBrook.Client.CustomConfig
 */

$config = array(
    'bug_stream_username' => Yii::app()->params['bug_stream_username'],
    'bug_stream_domain' => Yii::app()->params['bug_stream_domain'],
    'bug_stream_name' => Yii::app()->params['bug_stream_name'],
);
echo (CJSON::encode($config));
?>