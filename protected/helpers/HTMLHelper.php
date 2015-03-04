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
 * Helper methods that are related to displaying HTML
 *
 * @package PHP_Helper
 */
class HTMLHelper
{
    /**
     * Echos links to all the files in the provided folder or theme folder if there is one.
     *
     * Recursively calls itself for subfolders.
     *
     * @param string $native_path The native path to the file. (theme path is constructed from it.)
     * @param string $type The type of file to include (valid types are css and js).
     * @param string $folder f htis is a recursive call then this is the folder name(s).
     *
     * @throws Exception If invalid type is passed.
     *
     * @returns void
     */
    public static function includeNativeOrTheme($native_path, $type, $folder='') {
        if ($type === 'js') {
            $start_tag = '    <script type="text/javascript" src="';
            $end_tag = '"></script>' . PHP_EOL;
            $extension_length = 3;
        } else if ($type === 'css') {
            $start_tag = '    <link rel="stylesheet" type="text/css" href="';
            $end_tag = '" />' . PHP_EOL;
            $extension_length = 4;
        } else {
            throw new Exception('Invalid type passed to includeNativeOrTheme : ' . $type);
        }

        $included = array();
        $active_components = Yii::app()->params['active_components'];

        if (is_dir(Yii::getPathOfAlias('webroot') . $native_path) === true) {
            // sort descending so that class files are loaded before folders with the same name.
            $files = scandir(Yii::getPathOfAlias('webroot') . $native_path, SCANDIR_SORT_DESCENDING);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || substr($file, 0, 1) === '.') {
                    continue;
                }
                if (is_dir(Yii::getPathOfAlias('webroot') . $native_path . '/' . $file) === true) {
                    self::includeNativeOrTheme($native_path . '/' . $file, $type, $folder . $file . '/');
                    continue;
                }
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if ($extension !== $type) {
                    continue;
                }
                // Special case. Don't want to make another seperate folder for just this one file.
                if (strpos($native_path, 'Shared') !== false && $file === 'Interact.js') {
                    continue;
                }

                $file_without_extension = substr(
                    $folder . '/' . $file,
                    1,
                    strlen($folder . '/' . $file) - $extension_length - 1
                );
                if (isset(Yii::app()->params['active_components']) === true) {
                    if (in_array('all', Yii::app()->params['active_components']) === true
                        || in_array($file_without_extension, $active_components) === true
                    ) {
                        $theme_file = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl .
                            '/js/Shared/' . $file;
                        echo $start_tag;
                        if (file_exists($theme_file) === true) {
                            echo Yii::app()->theme->baseUrl . $native_path . '/' . $file;
                            array_push($included, $file);
                        } else {
                            echo $native_path . '/' . $file . '?' . Yii::app()->params['javascript_version'];
                        }
                        echo $end_tag;
                    }
                }
            }
        }

        // Also echo out anything that is in the theme folder but not the native one.
        $theme_folder = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . $native_path;
        if (is_dir($theme_folder) === true) {
            // sort descending so that class files are loaded before folders with the same name.
            $theme_files = scandir($theme_folder, SCANDIR_SORT_DESCENDING);
            foreach ($theme_files as $file) {
                if ($file === '.' || $file === '..' || substr($file, 0, 1) === '.') {
                    continue;
                }
                $possible_folder = Yii::getPathOfAlias('webroot') .
                    Yii::app()->theme->baseUrl . $native_path . '/' . $file;
                if (is_dir($possible_folder) === true) {
                    self::includeNativeOrTheme($native_path . '/' . $file, $type, $folder . $file . '/');
                    continue;
                }
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if ($extension !== $type) {
                    continue;
                }
                if (in_array($file, $included) === false) {
                    $file_without_extension = substr(
                        $folder . '/' . $file,
                        1,
                        strlen($folder . '/' . $file) - $extension_length - 1
                    );
                    if (isset(Yii::app()->params['active_components']) === true) {
                        if (in_array('all', Yii::app()->params['active_components']) === true
                            || in_array(substr($file, 0, $file_without_extension), $active_components) === true
                        ) {
                            echo Yii::app()->theme->baseUrl . $native_path . '/' . $file;
                        }
                    }
                }
            }
        }
    }

}

?>