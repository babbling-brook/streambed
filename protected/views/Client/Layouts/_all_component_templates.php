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

// Includes all the activated javascript templates in the components and user folders.


$all_files_to_include = array();
/**
 * Recursive inline function to fetch all the available files from both vanilla and theme folders.
 */
$getAllFiles =  function ($path, $extra_path) use (&$all_files_to_include, &$getAllFiles) {
    if (is_dir($path) === false) {
        return;
    }
    $files = scandir($path, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || substr($file, 0, 1) === '.') {
            continue;
        }
        if (is_dir($path . '/' . $file) === true) {
            $original_extra_path = $extra_path;
            $extra_path .= $file . '/';
            $getAllFiles($path . '/' . $file, $extra_path);
            $extra_path = $original_extra_path;
        } else {
            $view = substr($file, 0, strlen($file) - 4);
            array_push($all_files_to_include, $extra_path . $view);
        }
    }
};

if (Yii::app()->params['active_components'][0] === 'all') {
    $getAllFiles(Yii::getPathOfAlias('webroot') . '/protected/views/Client/Component', '');
    $getAllFiles(Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/views/Client/Component', '');
    $active_components = array_unique($all_files_to_include);

} else {
    $active_components = Yii::app()->params['active_components'];
}

foreach ($active_components as $file) {
    $this->renderPartial('/Client/Component/' . $file);
}
?>
