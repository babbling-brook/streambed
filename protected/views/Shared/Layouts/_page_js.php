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

/** Includes the js links to the page js for public/client js, with minified set or not.
 * Expects paramater $page String The name of the pages css file.
 *
 **/
$cs = Yii::app()->getClientScript();
if (Yii::app()->user->isGuest === true) {
    $type = 'Public';
} else {
    $type = 'Client';
}
if (Yii::app()->params['minify'] === true) {
    if ($type === 'Client') {
        $cs->registerScriptFile(
            Yii::app()->baseUrl . '/js/Minified/Client/Page/' . $page . '.js' . $this->js_version_number
        );
    } else {
        $cs->registerScriptFile(
            Yii::app()->baseUrl . '/js/Minified/Public/' . $page . '.js' . $this->js_version_number
        );
    }

} else {
    $js_root = realpath(Yii::app()->basePath . "/../js") . '/';
    $js_theme_root = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/js/';

    if ($type === 'Client') {
        if (file_exists($js_theme_root . 'Client/Page/' . $page . '.js') === true) {
            $cs->registerScriptFile(
                Yii::app()->theme->baseUrl . '/js/Client/Page/' . $page . '.js' . $this->js_version_number
            );
        } else if (file_exists($js_root . 'Client/Page/' . $page . '.js') === true) {
            $cs->registerScriptFile(
                Yii::app()->baseUrl . '/js/Client/Page/' . $page . '.js' . $this->js_version_number
            );
        }
    } else {
        if (file_exists($js_theme_root . 'Public/' . $page . '.js') === true) {
            $cs->registerScriptFile(
                Yii::app()->theme->baseUrl . '/js/Public/' . $page . '.js' . $this->js_version_number
            );
        } else if (file_exists($js_root . 'Public/' . $page . '.js') === true) {
            $cs->registerScriptFile(Yii::app()->baseUrl . '/js/Public/' . $page . '.js' . $this->js_version_number);
        }
    }
}
?>