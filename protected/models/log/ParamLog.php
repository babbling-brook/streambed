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
 * Model for the param_log DB table in the log database.
 * The paramaters that were used in an action
 *
 * @package PHP_Models
 */
class ParamLog extends CActiveRecord
{

    /**
     * The primary key of this table.
     *
     * @var integer
     */
    public $param_log_id;

    /**
     * The id of the action that these paramaters where used in.
     *
     * @var string
     */
    public $action_log_id;

    /**
     * The id of the name of this parameter in the thing table.
     *
     * @var string
     */
    public $name_thing_id;

    /**
     * The id of the content of this parameter in the thing table.
     *
     * @var string
     */
    public $content_thing_id;

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
        return '{{param_log}}';
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
            array('name_thing_id, content_thing_id', 'required'),
            array('name_thing_id, content_thing_id', 'length', 'max' => 10),
        );
    }

    /**
     * Insert a paramater pair for an action.
     *
     * @param integer $action_log_id The id of the action row that this paramater is associated with.
     * @param integer $name_thing_id The thing id of the name of this paramater.
     * @param integer $content_thing_id The thing id of the content of this paramater.
     *
     * @return void
     */
    public static function insertParamater($action_log_id, $name_thing_id, $content_thing_id) {
        if (Yii::app()->params['log_db_on'] === false) {
            return;
        }
        $sql = "
            INSERT INTO param_log
            (action_log_id, name_thing_id, content_thing_id)
            VALUES
            (:action_log_id, :name_thing_id, :content_thing_id)";
        $command = Yii::app()->dblog->createCommand($sql);
        $command->bindValue(":action_log_id", $action_log_id, PDO::PARAM_INT);
        $command->bindValue(":name_thing_id", $name_thing_id, PDO::PARAM_INT);
        $command->bindValue(":content_thing_id", $content_thing_id, PDO::PARAM_INT);
        $command->execute();
    }

}

?>