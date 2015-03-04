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
 * Model for the user_config_default DB table.
 * The table holds user default configurations for users who don't have custom values.
 *
 * @package PHP_Models
 */
class UserConfigDefault extends CActiveRecord
{

    /**
     * The primary key of the config table.
     *
     * @var integer
     */
    public $user_config_default_id;

    /**
     * The order this config item appears on the setup page.
     *
     * @var integer
     */
    public $display_order;

    /**
     * The code that is used to identify this config item in the codebase.
     *
     * @var string
     */
    public $code;

    /**
     * The name displayed on the config page.
     *
     * @var string
     */
    public $name;

    /**
     * The description, shown in the help popup on the config page.
     *
     * @var string
     */
    public $description;

    /**
     * The type of config option.
     *
     * Used to define which action is used to update it on the config page. See lookup table for options.
     *
     * @var integer
     */
    public $type_id;

    /**
     * The value a user gets if they don't have a value set in user_config.
     *
     * @var string
     */
    public $default_value;

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
        return 'user_config_default';
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
            array('display_order, code, name, description, type_id, default_value', 'required'),
            array('user_config_default_id, display_order, type_id', 'length', 'max' => 10),
            array('code', 'length', 'max' => 128),
        );
    }

    /**
     * Get all the user_config_default rows.
     *
     * @return array
     */
    public static function getAll() {
        $query = "
            SELECT
                display_order
                ,code
                ,name
                ,description
                ,type_id
                ,default_value AS value
                ,extra_data
            FROM user_config_default
            ORDER BY display_order, user_config_default_id";
        $command = Yii::app()->db->createCommand($query);
        $config = $command->queryAll();
        return $config;
    }

    /**
     * Get all the user_config_default rows for the settings page
     *
     * @return array
     */
    public static function getForSettingsPage() {
        $query = "
            SELECT
                user_config_default.code
                ,user_config_default.name
                ,user_config_default.description
                ,lookup.value AS type
                ,user_config_default.default_value AS value
                ,user_config_default.extra_data
            FROM
                user_config_default
                INNER JOIN lookup ON user_config_default.type_id = lookup.lookup_id
            ORDER BY display_order, user_config_default_id";
        $command = Yii::app()->db->createCommand($query);
        $config = $command->queryAll();
        return $config;
    }

    /**
     * Get the type_id of for a config code.
     *
     * @param string $code The config code to fetch a type_id for.
     *
     * @return integer|false
     */
    public static function getTypeId($code) {
        $query = "
            SELECT type_id
            FROM user_config_default
            WHERE code = :code";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":code", $code, PDO::PARAM_STR);
        $type_id = $command->queryScalar();
        return $type_id;
    }

    /**
     * Get the default value for a config code.
     *
     * @param string $code The config code to fetch a value for.
     *
     * @return string|false
     */
    public static function getValueFromCode($code) {
        $query = "
            SELECT default_value
            FROM user_config_default
            WHERE code = :code";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":code", $code, PDO::PARAM_STR);
        $value = $command->queryScalar();
        return $value;
    }

    /**
     * Fetch the config data ready for passing to the javascript client.
     *
     * @return array An associative array of code => value pairs.
     */
    public static function getForUse() {
        // Covert the array into a format usable by json - the code is the index.
        $json_array = array();
        $user_config = UserConfigDefault::getAll();
        foreach ($user_config as $option) {
            $json_array[$option['code']] = $option['value'];
        }
        return $json_array;
    }
}

?>