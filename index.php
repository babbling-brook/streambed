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
 * Base Yii file
 *
 * @license BSD License http://www.yiiframework.com/license/
 * @link http://www.yiiframework.com/
 */

// change the following paths if necessary
$yii=dirname(__FILE__) . '/protected/yii/yii-1.1.16.bca042/framework/yii.php';


/*** !!!!!!!    Be sure to change this to your domains host name !!!!!! ***/
define("HOST", "streambed.localhost");
define("CLIENT_TYPE", "cascade");




/** DO NOT EDIT FOR CUSTOM INSTALLATIONS BELOW HERE **/

$sub_domain = '';
$config_subdomain = '';
$config_client_type = array();
if (substr($_SERVER['HTTP_HOST'],0, 6) == 'domus.') {
    $sub_domain = 'domus';
    $config_subdomain = require_once( dirname( __FILE__ ) . '/protected/config/domus.php');
} else if (substr($_SERVER['HTTP_HOST'],0, 9) == 'scientia.') {
    $sub_domain = 'scientia';
    $config_subdomain = require_once( dirname( __FILE__ ) . '/protected/config/scientia.php');
} else if (substr($_SERVER['HTTP_HOST'],0, 5) == 'ring.') {
    $sub_domain = 'ring';
    $config_subdomain = require_once( dirname( __FILE__ ) . '/protected/config/ring.php');
} else if (substr($_SERVER['HTTP_HOST'],0, 7) == 'filter.') {
    $sub_domain = 'filter';
    $config_subdomain = require_once( dirname( __FILE__ ) . '/protected/config/filter.php');
} else if (substr($_SERVER['HTTP_HOST'],0, 11) == 'suggestion.') {
    $sub_domain = 'suggestion';
    $config_subdomain = require_once( dirname( __FILE__ ) . '/protected/config/suggestion.php');
} else if (substr($_SERVER['HTTP_HOST'],0, 8) == 'kindred.') {
    $sub_domain = 'kindred';
    $config_subdomain = require_once( dirname( __FILE__ ) . '/protected/config/kindred.php');
} else if (HOST === $_SERVER['HTTP_HOST']) {
    $sub_domain = 'client';
    $config_subdomain = require_once( dirname( __FILE__ ) . '/protected/config/client.php');
    $config_client_type = require_once( dirname( __FILE__ )
        . '/protected/config/type/' . CLIENT_TYPE . '.php');
} else {
    throw new Exception('This subdomain is not configured to run on this server.');
}

$config_main = require_once( dirname( __FILE__ ) . '/protected/config/main.php');

$config_server = require_once( dirname( __FILE__ ) . '/protected/config/server.php');
$db_host = $config_server['components']['db']['host'];
$db_name = $config_server['components']['db']['dbname'];
$config_server['components']['db']['connectionString'] = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
$config_server['params']['main_db_host'] = $db_host;
$config_server['params']['main_db_name'] = $db_name;
$config_server['params']['main_db_username'] = $config_server['components']['db']['username'];
$config_server['params']['main_db_password'] = $config_server['components']['db']['password'];
unset($config_server['components']['db']['host']);
unset($config_server['components']['db']['dbname']);

$db_host = $config_server['components']['dblog']['host'];
$db_name = $config_server['components']['dblog']['dbname'];
$config_server['components']['dblog']['connectionString'] = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
$config_server['params']['log_db_host'] = $db_host;
$config_server['params']['log_db_name'] = $db_name;
$config_server['params']['log_db_username'] = $config_server['components']['dblog']['username'];
$config_server['params']['log_db_password'] = $config_server['components']['dblog']['password'];
unset($config_server['components']['dblog']['host']);
unset($config_server['components']['dblog']['dbname']);

$db_host = $config_server['components']['dbtest']['host'];
$db_name = $config_server['components']['dbtest']['dbname'];
$config_server['components']['dbtest']['connectionString'] = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
$config_server['params']['test_db_host'] = $db_host;
$config_server['params']['test_db_name'] = $db_name;
$config_server['params']['test_db_username'] = $config_server['components']['dbtest']['username'];
$config_server['params']['test_db_password'] = $config_server['components']['dbtest']['password'];
unset($config_server['components']['dbtest']['host']);
unset($config_server['components']['dbtest']['dbname']);


// remove the following lines when in production mode
defined('YII_DEBUG') || define('YII_DEBUG', true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') || define('YII_TRACE_LEVEL', 3);

require_once ($yii);

$map = new CMap($config_server);
$map->mergeWith($config_client_type);
$map->mergeWith($config_subdomain);
$map->mergeWith($config_main);
$config = $map->toArray();

require_once(dirname(__FILE__).'/protected/extendedyii/WebApplication.php');
$app = new WebApplication($config);
$app->run();

