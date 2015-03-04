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

require_once (dirname(__FILE__) . '/../libraries/lessphp/Less.php');

/**
 * A facade to interface with Less.php, which is used to minify css and create css source maps.
 *
 * @package PHP_Helper
 */
class LessFacade
{

    /**
     * @var Less_Parser The less parser
     */
    private static $parser;

    /**
     * @var Array The options to be passed into Less.
     */
    private static $options;

    /**
     * @var String Root path to the main css folder.
     */
    private static $css_root;

    /**
     * @var String Root path to the css folder in the theme directory.
     */
    private static $css_theme_root;

    /**
     * Minifies the theme file if it exists, otherwise it uses the default.
     *
     * @param type $file The filename to append. Includes the local path from the css folder (no starting slash).
     *
     * @return void
     */
    private static function appendMainOrThemeFile($file) {
        if (file_exists(self::$css_theme_root . $file) === true) {
            self::$parser->parseFile(self::$css_theme_root . $file, 'http://' . HOST);
        } else {
            self::$parser->parseFile(self::$css_root . $file, 'http://' . HOST);
        }
    }

    /**
     * Imports all css files in a theme directory first, then use defaults.
     *
     * @param String $folder_path the path to the css folder relative to /css/
     * @param Array [$in_array] If included then only filenames in this array are included.
     *      items must not include filename extension.
     *
     * @return void
     */
    private static function appendMainOrThemeDirectory($folder_path, $in_array) {
        $theme_folder_path = self::$css_theme_root . $folder_path;
        $main_folder_path = self::$css_root . $folder_path;

        $theme_files = array();
        if (file_exists($theme_folder_path) === true) {
            $theme_iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($theme_folder_path)
            );
            foreach ($theme_iterator as $file_object) {
                if ($file_object->isFile() === false) {
                    continue;
                }
                $filename = $file_object->getFilename();
                if (substr($filename, -4) !== '.css') {
                    continue;
                }

                $file_without_extension = substr($filename, 0, strpos($filename, '.'));
                if (isset($in_array) === true
                    && in_array('all', $in_array) === false
                    && in_array($file_without_extension, $in_array) === false
                ) {
                    continue;
                }

                $realtive_path = substr($file_object->getPathname(), strlen($theme_folder_path));
                $theme_files[] = $realtive_path;
                self::$parser->parseFile($file_object->getPathname(), 'http://' . HOST);
            }
        }

        $main_iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($main_folder_path)
        );
        foreach ($main_iterator as $file_object) {
            if ($file_object->isFile() === false) {
                continue;
            }
            $filename = $file_object->getFilename();
            $t = substr($filename, -4);
            if (substr($filename, -4) !== '.css') {
                continue;
            }

            $file_without_extension = substr($filename, 0, strpos($filename, '.'));
            if (isset($in_array) === true
                && in_array('all', $in_array) === false
                && in_array($file_without_extension, $in_array) === false
            ) {
                continue;
            }

            $realtive_path = substr($file_object->getPathname(), strlen($main_folder_path));
            if (in_array($realtive_path, $theme_files) === false) {
                self::$parser->parseFile($file_object->getPathname(), 'http://' . HOST);
            }
        }
    }

    /**
     * Minifies all of the public css files.
     *
     * @return void
     */
    private static function publicCSS() {
        self::$options['sourceMapWriteTo'] = realpath(Yii::app()->basePath . "/../css") . '/Minified/Public/source.map';
        self::$options['sourceMapURL'] = '/css/Minified/Public/source.map';
        self::$parser = new Less_Parser(self::$options);

        self::appendMainOrThemeFile('Shared/Reset.css');
        self::appendMainOrThemeFile('Shared/Main.css');
        self::appendMainOrThemeFile('Shared/Theme.css');
        self::appendMainOrThemeFile('Shared/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css');
        self::appendMainOrThemeDirectory('Shared/Component', Yii::app()->params['active_components']);

        self::appendMainOrThemeFile('Public/Public.css');
        self::appendMainOrThemeFile('Public/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css');
        self::appendMainOrThemeDirectory('Public/Component', Yii::app()->params['active_components']);

        $css = self::$parser->getCss();
        file_put_contents(self::$css_root . 'Minified/Public/css.css', $css);
    }

    /**
     * Returns an array of all the files in the given full path. Also searches subdirectories.
     *
     * @return array An array of all the files. If the file is in a sub directory then it includes the relative path
     *      to the file.
     */
    private static function getPages($path) {
        $css_files = array();
        if (file_exists($path) === true) {
            $theme_iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            );
            foreach ($theme_iterator as $file_object) {
                if ($file_object->isFile() === false) {
                    continue;
                }
                $filename = $file_object->getFilename();
                if (substr($filename, -4) !== '.css') {
                    continue;
                }

                $css_files[] = substr($file_object->getPathname(), strlen($path));
            }
        }
        return $css_files;
    }

    /**
     * Recursively create a long directory path
     *
     * @return boolean USed as a flag to escape the recursion.
     */
    private static function createPath($path) {
        if (is_dir($path) === true) {
            return true;
        }
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);
        $return = self::createPath($prev_path);
        if ($return === true && is_writable($prev_path) === true) {
            return mkdir($path);
        } else {
            return false;
        }
    }

    /**
     * Minifies all of the page css files independently.
     *
     * @param String $type Are we minifying 'Public' or 'Client' css files.
     *
     * @return void
     */
    private static function pageCSS($type) {
        $pages = array();
        $pages = array_merge($pages, self::getPages(self::$css_root . 'Shared/Page'));
        $pages = array_merge($pages, self::getPages(self::$css_root . $type . '/Page'));
        $pages = array_merge($pages, self::getPages(self::$css_theme_root . 'Shared/Page'));
        $pages = array_merge($pages, self::getPages(self::$css_theme_root . $type . '/Page'));
        $pages = array_unique($pages);

        foreach ($pages as $file) {
            $url_file = str_replace('\\', '/', $file);
            self::$options['sourceMapWriteTo'] = self::$css_root . 'Minified/' . $type . '/Page/' . $file . '.map';
            self::$options['sourceMapURL'] = '/css/Minified/' . $type . '/Page' . $url_file . '.map';
            self::$parser = new Less_Parser(self::$options);
            if (file_exists(self::$css_theme_root . 'Shared/Page' . $file) === true) {
                self::$parser->parseFile(self::$css_theme_root . 'Shared/Page' . $file, 'http://' . HOST);
            } else if (file_exists(self::$css_root . 'Shared/Page' . $file) === true) {
                self::$parser->parseFile(self::$css_root . 'Shared/Page' . $file, 'http://' . HOST);
            }
            if (file_exists(self::$css_theme_root . $type . '/Page' . $file) === true) {
                self::$parser->parseFile(self::$css_theme_root . $type . '/Page' . $file, 'http://' . HOST);
            } else if (file_exists(self::$css_root . $type . '/Page' . $file) === true) {
                self::$parser->parseFile(self::$css_root . $type . '/Page' . $file, 'http://' . HOST);
            }

            $path = 'Minified/' . $type . '/Page' . $file;
            $path = str_replace('\\', '/', $path);
            $path = substr($path, 0, strrpos($path, '/'));
            self::createPath(self::$css_root . $path);
            $css = self::$parser->getCss();
            file_put_contents(self::$css_root . 'Minified/' . $type . '/Page' . $file, $css);
        }
    }

    /**
     * Minifies all of the client css files for when a user has logged in.
     *
     * @return void
     */
    private static function clientCSS() {
        self::$options['sourceMapWriteTo'] = realpath(Yii::app()->basePath . "/../css") . '/Minified/Client/source.map';
        self::$options['sourceMapURL'] = '/css/Minified/Client/source.map';
        self::$parser = new Less_Parser(self::$options);

        self::appendMainOrThemeFile('Shared/Reset.css');
        self::appendMainOrThemeFile('Shared/Main.css');
        self::appendMainOrThemeFile('Shared/Theme.css');
        self::appendMainOrThemeFile('Shared/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css');
        self::appendMainOrThemeDirectory('Shared/Component', Yii::app()->params['active_components']);

        self::appendMainOrThemeFile('Client/Client.css');
        self::appendMainOrThemeFile('Client/Layouts/ClientType/' . CLIENT_TYPE . '/SiteFurniture.css');
        self::appendMainOrThemeDirectory('Client/Component', Yii::app()->params['active_components']);

        self::appendMainOrThemeFile('Libraries/jquery.token-input.css');

        $css = self::$parser->getCss();
        file_put_contents(self::$css_root . 'Minified/Client/css.css', $css);
    }

    /**
     * Minify all css files and generate their source maps.
     *
     * @return void
     */
    public static function minify() {
        self::$options = array(
            'compress' => true,
            'sourceMap' => true,
        );

        self::$css_root = realpath(Yii::app()->basePath . "/../css") . '/';
        self::$css_theme_root = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/css/';

        self::publicCSS();
        self::clientCSS();

        self::pageCSS('Public');
        self::pageCSS('Client');
    }
}

?>