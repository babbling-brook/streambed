<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../../dependencies/yii-1.1.15.022a51/framework/yiit.php';

$host = 'cobaltcascade.localhost';

$config_main = require_once( dirname( __FILE__ ) . '/../config/main.php');
$config_server = require_once( dirname( __FILE__ ) . '/../config/server.php');
//$config_test = require_once( dirname( __FILE__ ) . '/../config/server.php');

require_once($yiit);

$config = CMap::mergeArray($config_main, $config_server);

Yii::createWebApplication($config);

require_once(dirname(__FILE__).'/WebTestCase.php');
