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
 * This class handles the setup of the databases when the project is first installed.
 *
 */
class Setup
{
    private static function createDB($db_name, $db_host, $db_user, $db_password) {
        try {
            $dbh = new PDO('mysql:host=' . $db_host, $db_user, $db_password);

            $dbh->exec('CREATE DATABASE ' . $db_name);

        } catch (Exception $e) {
            throw new Exception('Error creating database: ' . $db_name);
        }
    }

    private static function createMainDBTables() {

    }

    private static function runSqlFromFile($db_name, $db_host, $db_user, $db_password, $path) {
        try {
            $file_handler = fopen($path, 'r');
            $sql = fread($file_handler, filesize($path));
            $connection = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $connection->query($sql);

        } catch (Exception $e) {
            throw new Exception('Error when runngin sql from file ' . $path . '<br/>' . $e);
        }
    }

    private static function insertThisSite($db_name, $db_host, $db_user, $db_password) {
        try {
            $sql = "INSERT INTO site (site_id, domain) VALUES ('10000', '" . HOST . "')";
            $connection = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $connection->query($sql);

        } catch (Exception $e) {
            throw new Exception('Error when inserting site HOST.' . $e);
        }
    }

    public static function go() {
        self::createDB(
            Yii::app()->params['main_db_name'],
            Yii::app()->params['main_db_host'],
            Yii::app()->params['main_db_username'],
            Yii::app()->params['main_db_password']
        );
        self::runSqlFromFile(
            Yii::app()->params['main_db_name'],
            Yii::app()->params['main_db_host'],
            Yii::app()->params['main_db_username'],
            Yii::app()->params['main_db_password'],
            Yii::app()->basePath . '/data/setup.streambed.sql'
        );
        self::insertThisSite(
            Yii::app()->params['main_db_name'],
            Yii::app()->params['main_db_host'],
            Yii::app()->params['main_db_username'],
            Yii::app()->params['main_db_password']
        );
        self::runSqlFromFile(
            Yii::app()->params['main_db_name'],
            Yii::app()->params['main_db_host'],
            Yii::app()->params['main_db_username'],
            Yii::app()->params['main_db_password'],
            Yii::app()->basePath . '/data/setup.streambed.data.sql'
        );

        self::createDB(
            Yii::app()->params['log_db_name'],
            Yii::app()->params['log_db_host'],
            Yii::app()->params['log_db_username'],
            Yii::app()->params['log_db_password']
        );
        self::runSqlFromFile(
            Yii::app()->params['log_db_name'],
            Yii::app()->params['log_db_host'],
            Yii::app()->params['log_db_username'],
            Yii::app()->params['log_db_password'],
            Yii::app()->basePath . '/data/setup.streambed_log.sql'
        );

        self::createDB(
            Yii::app()->params['test_db_name'],
            Yii::app()->params['test_db_host'],
            Yii::app()->params['test_db_username'],
            Yii::app()->params['test_db_password']
        );
        self::runSqlFromFile(
            Yii::app()->params['test_db_name'],
            Yii::app()->params['test_db_host'],
            Yii::app()->params['test_db_username'],
            Yii::app()->params['test_db_password'],
            Yii::app()->basePath . '/data/setup.streambed.sql'
        );
        self::insertThisSite(
            Yii::app()->params['test_db_name'],
            Yii::app()->params['test_db_host'],
            Yii::app()->params['test_db_username'],
            Yii::app()->params['test_db_password']
        );
        self::runSqlFromFile(
            Yii::app()->params['test_db_name'],
            Yii::app()->params['test_db_host'],
            Yii::app()->params['test_db_username'],
            Yii::app()->params['test_db_password'],
            Yii::app()->basePath . '/data/setup.streambed_test.data.sql'
        );
    }

    public static function doDbsExist() {
        $db_name = Yii::app()->params['main_db_name'];
        $db_host = Yii::app()->params['main_db_host'];
        $db_user = Yii::app()->params['main_db_username'];
        $db_password = Yii::app()->params['main_db_password'];
        try {
            $connection = new PDO('mysql:host=' . $db_host, $db_user, $db_password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '"  . $db_name . "'";
            $query = $connection->query($sql);
            $query->setFetchMode(PDO::FETCH_ASSOC);
            $result = $query->fetch();
            if (isset($result['SCHEMA_NAME']) && $result['SCHEMA_NAME'] === $db_name) {
                return true;
            } else {
                return false;
            }


        } catch (Exception $e) {
            //throw new Exception('Error creating database: ' . $db_name);
            return false;
        }
    }
}
