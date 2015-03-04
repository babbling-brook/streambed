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

$cs = Yii::app()->getClientScript();
if (Yii::app()->params['minify'] === true) {
    $cs->registerCssFile(Yii::app()->baseUrl . '/css/Minified/Public/css.css');

} else {
    $this->renderPartial('/Shared/Layouts/_all_types_css');
    $this->renderPartial('/Client/Layouts/_all_component_css');

    $css_root = realpath(Yii::app()->basePath . "/../css") . '/';
    $css_theme_root = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/';

    if (file_exists($css_theme_root . 'Public/Public.css') === true) {
        $cs->registerCssFile(Yii::app()->theme->baseUrl . '/Public/Public.css');
    } else if (file_exists($css_root . 'Public/Public.css') === true) {
        $cs->registerCssFile(Yii::app()->baseUrl . '/css/Public/Public.css');
    }
}
?>