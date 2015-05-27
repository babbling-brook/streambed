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

require_once (dirname(__FILE__) . '/../libraries/UglifyPHP/Uglify.php');
require_once (dirname(__FILE__) . '/../libraries/UglifyPHP/JS.php');

/**
 * A facade to interface with Uglifyjsp, which is used to minify js and create js source maps.
 *
 * @package PHP_Helper
 */
class UglifyFacade
{

    /**
     * @var String The root path to the js folder.
     */
    private static $js_root;

    /**
     * @var String The root path to the themes js folder.
     */
    private static $js_theme_root;

    /**
     * @var Array A list of variable names that can be mangled.
     */
    private static $mangle_names = array(
//        'BabblingBrook' => 'bab',
//        'Client' => 'c',
//        'Domus' => 'd',
//        'Scientia' => 's',
//        'Library' => 'l'
    );

    /**
     * fetchs the path of the theme file if it exists, otherwise it uses the default.
     *
     * @param type $file The filename to use. Includes the local path from the css folder (no starting slash).
     *
     * @return string The full path to the file.
     */
    private static function generateMainOrThemeFile($file) {
        if (file_exists(self::$js_theme_root . $file) === true) {
            return self::$js_theme_root . $file;
        } else {
            return self::$js_root . $file;
        }
    }

    /**
     * Imports all js files in a theme directory first, then use defaults.
     *
     * @param String $folder_path the path to the css folder relative to /css/
     * @param Array [$in_array] If included then only filenames in this array are included.
     *      items must not include filename extension.
     * @param Array [$not_array] An array of items not to include.
     *      items must not include filename extension.
     *
     * @return array An array of file names with full paths.
     */
    private static function generateFileListForMainOrThemeDirectory($folder_path, $in_array, $not_array) {
        $theme_folder_path = self::$js_theme_root . $folder_path;
        $main_folder_path = self::$js_root . $folder_path;
echo ($folder_path . '<br>');
        $theme_files = array();
        $js_files = array();
        if (file_exists($theme_folder_path) === true) {
            $theme_iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($theme_folder_path)
            );
            foreach ($theme_iterator as $file_object) {
                if ($file_object->isFile() === false) {
                    continue;
                }
                $filename = $file_object->getFilename();
                if (substr($filename, -3) !== '.js') {
                    continue;
                }

                $file_without_extension = substr($filename, 0, strpos($filename, '.'));
                if (isset($in_array) === true
                    && in_array('all', $in_array) === false
                    && in_array($file_without_extension, $in_array) === false
                ) {
                    continue;
                }
                if (isset($not_array) === true && in_array($file_without_extension, $in_array) === true) {
                    continue;
                }

                $realtive_path = substr($file_object->getPathname(), strlen($theme_folder_path));
                $theme_files[] = $realtive_path;
                $js_files[] = $file_object->getPathname();
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
            if (substr($filename, -3) !== '.js') {
                continue;
            }

            // Skip backbone files for now.
            if (strpos($file_object->getPathname(), 'Backbone') > 0) {
echo ('!!');
                continue;
            }

            $file_without_extension = substr($filename, 0, strpos($filename, '.'));
            if (isset($not_array) === true && in_array($file_without_extension, $not_array) === true) {
                continue;
            }

            $realtive_path = substr($file_object->getPathname(), strlen($main_folder_path));
            if (in_array($realtive_path, $theme_files) === false) {
                $js_files[] = $file_object->getPathname();
            }
        }
        return $js_files;
    }

    private static function runUgly($paths, $minified_path, $js_path, $source_map_filename, $source_map_url,
        $mangle_top_level_names=true
    ) {
        $js = new \UglifyPHP\JS($paths);

        // If this is windows then change all the paths to forward slash (on unix they allready will be).
        $windows = false;
        if (strpos($js_path, '\\') > 0) {
            $windows = true;
        }
        foreach ($paths as $key => $path) {
            $js_path = str_replace('\\', '/', $js_path);
        }
        $paths[$key] = str_replace('\\', '/', $path);
        $minified_path = str_replace('\\', '/', $minified_path);

        $result = $js->minify(
            $minified_path,
            array(
                'source-map' => $js_path . $source_map_filename,
                'source-map-url' => $source_map_url,
                'mangle' => true,
                'mangle' => true,
                'compress' => true,
//                'source-map-root' => '',
//                'prefix' => '3'
            )
        );
        if ($result === false) {
            throw new Exception('There was an error when minifying the JS code');
        }

        // replace absolute source map urls with relative ones.
        $replace_string = $js_path;
        if ($windows === true) {
            $replace_string = str_replace("/", "\\\\", $js_path);
        }

        $path_to_file = $js_path . $source_map_filename;
        $file_contents = file_get_contents($path_to_file);
        $file_contents = str_replace($replace_string, "../", $file_contents);
        $file_contents = str_replace('\\\\', "/", $file_contents);
        if ($mangle_top_level_names === true) {
            foreach (self::$mangle_names as $key => $name) {
                $file_contents = str_replace($key, $name, $file_contents);
            }
        }
        file_put_contents($path_to_file, $file_contents);

        if ($mangle_top_level_names === true) {
            $file_contents = file_get_contents($minified_path);
            foreach (self::$mangle_names as $key => $name) {
                $file_contents = str_replace($key, $name, $file_contents);
            }
            file_put_contents($minified_path, $file_contents);
        }
    }

    /**
     * Generate the minified client javascript.
     *
     * @return void
     */
    private static function generateClientJS() {
        $js_files = array();

        $js_files[] = self::generateMainOrThemeFile('resources/jquery.js');
        $js_files[] = self::generateMainOrThemeFile('resources/json2.js');
        $js_files[] = self::generateMainOrThemeFile('jquery_pluggins/jquery-ui.js');
        $js_files[] = self::generateMainOrThemeFile('jquery_pluggins/autoresize.jquery.js');
        $shared_files = self::generateFileListForMainOrThemeDirectory('Shared', array(), array('Interact'));
        $js_files = array_merge($js_files, $shared_files);
        $core_files = self::generateFileListForMainOrThemeDirectory('Client/Core', array(), array());
        $js_files = array_merge($js_files, $core_files);
        $component_files = self::generateFileListForMainOrThemeDirectory(
            'Client/Component',
            Yii::app()->params['active_components'],
            array()
        );
        $js_files = array_merge($js_files, $component_files);
        $js_files[] = self::generateMainOrThemeFile('Client/ready.js');

        $js_path = realpath(Yii::app()->basePath . "/../js");
        $source_map_filename = '/Minified/client.js.map';
        $source_map_url = '/js/Minified/client.js.map';

        self::runUgly(
            $js_files,
            self::$js_root . 'Minified/client.js',
            $js_path,
            $source_map_filename,
            $source_map_url
        );
    }

    private static function generateClientPageJS() {
        $page_folder_path = self::$js_root . 'Client/Page';
        $js_path = realpath(Yii::app()->basePath . "/../js");

        $main_iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($page_folder_path)
        );
        foreach ($main_iterator as $file_object) {
            if ($file_object->isFile() === false) {
                continue;
            }
            $filename = $file_object->getFilename();
            if (substr($filename, -3) !== '.js') {
                continue;
            }
            $realtive_path = substr($file_object->getPathname(), strlen($page_folder_path) + 1);
            $realtive_path = str_replace('\\', '/', $realtive_path);

            $source_map_filename = '/Minified/Client/Page/' . $realtive_path . '.map';
            $source_map_url = '/js/Minified/Client/Page/' . $realtive_path . '.map';


            $path_parts = explode('/', $realtive_path);
            $dir_path = $js_path . '/Minified/Client/Page/' . $path_parts[0];
            if (count($path_parts) > 1 && file_exists($dir_path) === false) {
                mkdir($dir_path);
            }

            self::runUgly(
                array($file_object->getPathname()),
                self::$js_root . 'Minified/Client/Page/' . $realtive_path,
                $js_path,
                $source_map_filename,
                $source_map_url
            );

        }
    }

    /**
     * Minify all js files for the public client site.
     *
     * @return void
     */
    private static function generatePublicPageJS() {
        $public_folder_path = self::$js_root . 'Public';
        $js_path = realpath(Yii::app()->basePath . "/../js");

        $main_iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($public_folder_path)
        );
        foreach ($main_iterator as $file_object) {
            if ($file_object->isFile() === false) {
                continue;
            }
            $filename = $file_object->getFilename();
            if (substr($filename, -3) !== '.js') {
                continue;
            }

            $realtive_path = substr($file_object->getPathname(), strlen($public_folder_path) + 1);
            $realtive_path = str_replace('\\', "/", $realtive_path);

            $source_map_filename = '/Minified/Public/' . $realtive_path . '.map';
            $source_map_url = '/js/Minified/Public/' . $realtive_path . '.map';
            $path_parts = explode('/', $realtive_path);
            $dir_path = $js_path . '/Minified/Public/' . $path_parts[0];
            if (count($path_parts) > 1 && file_exists($dir_path) === false) {
                mkdir($dir_path);
            }

            self::runUgly(
                array($file_object->getPathname()),
                self::$js_root . 'Minified/Public/' . $realtive_path,
                $js_path,
                $source_map_filename,
                $source_map_url
            );

        }
    }

    public static function generateDomusJS() {
        $js_files = array();

        $js_files[] = self::generateMainOrThemeFile('resources/jquery.js');
        $js_files[] = self::generateMainOrThemeFile('resources/json2.js');
        $shared_files = self::generateFileListForMainOrThemeDirectory('Shared', array(), array('Interact'));
        $js_files = array_merge($js_files, $shared_files);
        $domus_files = self::generateFileListForMainOrThemeDirectory('Domus', array(), array());
        $js_files = array_merge($js_files, $domus_files);

        $js_path = realpath(Yii::app()->basePath . "/../js");
        $source_map_filename = '/Minified/domus.js.map';
        $source_map_url = '/js/Minified/domus.js.map';

        self::runUgly($js_files, self::$js_root . 'Minified/domus.js', $js_path, $source_map_filename, $source_map_url);
    }

    public static function generateSubDomainJS($subdomain, $mangle_top_level_names) {
        $js_files = array();

        $js_files[] = self::generateMainOrThemeFile('resources/jquery.js');
        $js_files[] = self::generateMainOrThemeFile('resources/json2.js');
        $shared_files = self::generateFileListForMainOrThemeDirectory('Shared', array(), array());
        $js_files = array_merge($js_files, $shared_files);
        $subdomain_files = self::generateFileListForMainOrThemeDirectory(ucwords($subdomain), array(), array());
        $js_files = array_merge($js_files, $subdomain_files);

        $js_path = realpath(Yii::app()->basePath . "/../js");
        $source_map_filename = '/Minified/' . $subdomain . '.js.map';
        $source_map_url = '/js/Minified/' . $subdomain . '.js.map';
        $minified_path = self::$js_root . 'Minified/' . $subdomain . '.js';

        self::runUgly(
            $js_files,
            $minified_path,
            $js_path,
            $source_map_filename,
            $source_map_url,
            $mangle_top_level_names
        );
    }

    /**
     * Minify all js files and generate their source maps.
     *
     * @return void
     */
    public static function minify() {
        set_time_limit(360); // six min

        self::$js_root = realpath(Yii::app()->basePath . "/../js") . '/';
        self::$js_theme_root = Yii::getPathOfAlias('webroot') . Yii::app()->theme->baseUrl . '/js/';

        $client_folder = self::$js_root . 'Minified/Client';
        if (file_exists($client_folder) === false) {
            mkdir($client_folder);
            $client_page_folder = $client_folder . '/Page';
            if (file_exists($client_page_folder) === false) {
                mkdir($client_page_folder);
            }
        }
        $public_folder = self::$js_root . 'Minified/Public';
        if (file_exists($public_folder) === false) {
            mkdir($public_folder);
        }


        if (\UglifyPHP\JS::installed() === false) {
            throw new Exception('Uglify is not installed as a comand line app.');
        }

        self::generateClientJS();
        self::generateClientPageJS();

        self::generatePublicPageJS();

        self::generateDomusJS();

        self::generateSubDomainJS('scientia', true);
        self::generateSubDomainJS('filter', false);
        self::generateSubDomainJS('kindred', false);
        self::generateSubDomainJS('ring', false);
        self::generateSubDomainJS('suggestion', false);
    }
}

?>