<?php
/**
 *
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
 * config/server.php
 *
 * This is the config file for the server.
 * It includes configuration options that might vary between servers, such as database connections.
 */
return
    array(
        // preloading 'log' component
        'preload' => array('log'),

        'modules' => array(
            'gii' => array(
                'class' => 'system.gii.GiiModule',
                'password' => 'fghw452uwhrthgfgd',  // !! This must be updated for each install.
            ),
        ),

        // application components
        'components' => array(
            'session' => array(
                'savePath' => dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . 'cookiepath',
                'cookieMode' => 'allow',
                'cookieParams' => array(
                    'path' => '/',
                    // There is a period before the domain name so that the cookie will work on all subdomains.
                    'domain' => '.' . HOST,
                    'httpOnly' => true,
                ),
            ),
            'db' => array(
                'host' => 'localhost',
                'dbname' => 'streambed',
                'emulatePrepare' => true,
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8',
                'tablePrefix' => '',
                'enableParamLogging' => true,
                'enableProfiling' => false,

                // The connection string is generated in index.php
                'connectionString' => false,
            //    'schemaCachingDuration' => 100,
            ),
            'dblog' => array(
                'host' => 'localhost',
                'dbname' => 'streambed_log',
                'emulatePrepare' => true,
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8',
                'tablePrefix' => '',
                'enableParamLogging' => false,
                'enableProfiling' => true,
                'class' => 'CDbConnection',  // This is needed for additional DB connections.

                // The connection string is generated in index.php
                'connectionString' => false,
            ),
            'dbtest' => array(
                'host' => 'localhost',
                'dbname' => 'streambed_test',
                'emulatePrepare' => true,
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8',
                'tablePrefix' => '',
                'enableParamLogging' => true,
                'enableProfiling' => true,
                'class' => 'CDbConnection',  // This is needed for additional DB connections.

                // The connection string is generated in index.php
                'connectionString' => false,
            ),
            'errorHandler' => array(
                // use 'site/error' action to display errors
                'errorAction' => 'Site/Error',
            ),
            'log' => array(
                'class' => 'CLogRouter',
                'routes' => array(
                    array(
                        'class' => 'CFileLogRoute',
                        'levels' => 'trace',
                        'categories' => 'system.db.*',
                        'logPath' =>
                            dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . 'log',
                        'logFile' => 'sql.log',
                    ),
                ),
            ),
        ),

        'params' => array(


            'minify' => false,

            // If this is set then local storage will be flushed on page load.
            'flush_localstorage_version' => time(),  // use time() to flush on every refresh

            // If this is changed then all javascript files will be redownloaded.
            'javascript_version' => time(),  // use time() to flush on every refresh

            // Is only one domus iframe loaded and other windows hook up with it.
            // Can cause confusing iframe debug messages.
            // If something is tricky to debug then turn it off for debuging.
            'single_domus_iframe' => true,

            // ************   This should be set to true when in production ********
            'fake_firebug' => false,

            // Is the ajaxurl feature turned on. If something is tricky to debug then turn it off for debuging.
            // Causes the domus to be only loaded once and new content loaded without refreshing the page.
            'ajaxurl' => true,

            // If this is set to a number larger than 0 then all requests will be delayed by that number
            // of milliseconds. Used to simulated the delay in a hosted environment.
            // This is very usefull for selenium tests.
            'local_host_delay' => 0,

            // Is the log database turned on or off.
            // Takes a lot resources. Only turn on when needed.
            'log_db_on' => true,

            // This values are dynamically generated in /index.php from the settings above in the db section.
            'main_db_host' => false,
            'main_db_name' => false,
            'main_db_username' => false,
            'main_db_password' => false,
            'test_db_host' => false,
            'test_db_name' => false,
            'test_db_username' => false,
            'test_db_password' => false,
            'log_db_host' => false,
            'log_db_name' => false,
            'log_db_username' => false,
            'log_db_password' => false,

            // The id of the post used in the tutorial.
            'tutorial_post_id' => '11056',
        ),
    );
