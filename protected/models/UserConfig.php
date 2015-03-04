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
 * Model for the user_config DB table.
 * The table holds user configurations that override the defaults defined in the main.php config file.
 *
 * @package PHP_Models
 */
class UserConfig extends CActiveRecord
{

    /**
     * The primary key of the config table.
     *
     * @var integer
     */
    public $user_config_id;

    /**
     * The id of the user that owns this config setting.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The name of the config setting that is used in the codebase.
     *
     * @var string
     */
    public $code;

    /**
     * The value of the config setting.
     *
     * @var string
     */
    public $value;

    /**
     * Returns the parent model.
     *
     * @param type $className The name of this class.
     *
     * @return Model
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * Getter for the tables name.
     *
     * @return string the associated database table name.
     */
    public function tableName() {
        return 'user_config';
    }

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, code, value', 'required'),
            array('user_id', 'length', 'max' => 10),
            array('code', 'length', 'max' => 128),
        );
    }

//    /**
//     * Fetch the config options for a user
//     *
//     * @param integer $user_id The id of the user to fetch config options for.
//     *
//     * @return array
//     */
//    public static function getForUser ($user_id) {
//        $query = "SELECT code,value FROM user_config WHERE user_id = :user_id";
//        $command = Yii::app()->db->createCommand($query);
//        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
//        $user_config = $command->queryAll();
//        return $user_config;
//    }

    /**
     * Fetch a config row for a user
     *
     * @param integer $user_id The id of the user to fetch a config id for.
     * @param integer $code The config code to fetch a config id for.
     *
     * @return integer|false The user_config_id or false.
     */
    public static function getUserConfigId($user_id, $code) {
        $query = "
            SELECT user_config_id
            FROM user_config
            WHERE
                user_id = :user_id
                AND code = :code";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":code", $code, PDO::PARAM_STR);
        $user_config_id = $command->queryScalar();
        return $user_config_id;
    }

    /**
     * Delete a users config setting, effectively resetting it to the default setting.
     *
     * @param integer $user_id The id of the user to delete a row for.
     * @param integer $code The config code to identify the row that is to be deleted.
     *
     * @return void
     */
    public static function deleteRow($user_id, $code) {
        $query = "
            DELETE
            FROM user_config
            WHERE
                user_id = :user_id
                AND code = :code";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":code", $code, PDO::PARAM_STR);
        $command->execute();
    }

    /**
     * Delete all custom config settings for a user, effectively resetting them to the default setting.
     *
     * @param integer $user_id The id of the user to delete a row for.
     *
     * @return void
     */
    public static function deleteAllForUser($user_id) {
        $query = "
            DELETE
            FROM user_config
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    public static function updateAllUserConfigActionLocation() {
        $rows = UserConfig::model()->findAll();
        foreach ($rows as $row) {
            $url = $row->value;
            $url_parts = explode('/', $url);
            if (count($url_parts) > 1) {
                $new_url = $url_parts[0] . '/' . $url_parts[1] . '/' . $url_parts[2] . '/' . $url_parts[4] . '/'
                    . $url_parts[5] . '/' . $url_parts[6] . '/' . $url_parts[7] . '/' . $url_parts[3];
                $row->value = $new_url;
                $row->save();
            }
        }
    }

    /**
     * Fetch the config options for a user
     *
     * @param integer $user_id The id of the user to fetch config options for.
     *
     * @return array
     */
    public static function getConfigRow($user_id, $code) {
        $query = "SELECT value FROM user_config WHERE user_id = :user_id AND code = :code";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":code", $code, PDO::PARAM_STR);
        $value = $command->queryScalar();
        if ($value === false) {
            $value = UserConfigDefault::getValueFromCode($code);
        }
        return $value;
    }

    /**
     * Deletes user_config rows by their user_id
     *
     * @param integer $user_id The id of the user whose user config settings are being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_config
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }


//    /**
//     * Merge the default settings into the users settings, ensureing that a user has a a value for every setting.
//     *
//     * @return void
//     */
//    private function mergeDefaults() {
//        // Copy the user config options into the default array.
//        $default_config = UserConfigDefault::getAll();
//        foreach ($default_config as $key => $default_option) {
//
//            // Convert the type_id to its string value.
//            $default_config[$key]['type'] = LookupHelper::getValue($default_option['type_id']);
//            unset($default_config[$key]['type_id']);
//
//            foreach ($this->user_config as $user_option) {
//                if ($user_option['code'] === $default_option['code']) {
//                    $default_config[$key]['value'] = $user_option['value'];
//                    $default_config[$key]['custom'] = true;
//                    break;
//                }
//            }
//        }
//        $this->user_config = $default_config;
//    }
//
//    /**
//     * Fetch the users config data and merge it with the defaults.
//     *
//     * @param type $user_id The id of the user whose config data we are working with.
//     *
//     * @return void
//     */
//    public function __construct($user_id) {
//        //$this->user_config = UserConfig::getForUser($user_id);
//        //$this->mergeDefaults();
//    }
//
//    /**
//     * Fetch the config data ready for passing to the javascript client.
//     *
//     * @return array An associative array of code => value pairs.
//     */
//    public function getForUse () {
//        // Covert the array into a format usable by json - the code is the index.
//        $json_array = array();
//        foreach ($this->user_config as $option) {
//            $json_array[$option['code']] = $option['value'];
//        }
//        return $json_array;
//    }
//
//    /**
//     * Fetch the full config object, including description display order and name data.
//     *
//     * @return array An array of config objects indexed by the order to display them.
//     *      See UserConfigDefault::getAll for a list of contents.
//     */
//    public function getFull () {
//        // the extra_data needs converting to a php array so that when the whole config
//        // is converted to json the extra_data becomes json and not a string.
//        $config = $this->user_config;
//        foreach ($config as $key => $option) {
//            if (empty($option['extra_data']) === false) {
//                $extra = CJSON::decode($option['extra_data']);
//                $config[$key]['extra_data'] = $extra;
//            }
//        }
//        return $config;
//    }

    /**
     * Select rows of user_config data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_config
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>