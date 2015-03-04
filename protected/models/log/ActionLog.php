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
 * Model for the action_log DB table in the log database.
 * A log of actions a user has called
 *
 * @package PHP_Models
 */
class ActionLog extends CActiveRecord
{

    /**
     * The primary key of this table.
     *
     * @var integer
     */
    public $action_log_id;

    /**
     * The id of the user that called the action.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The id of the action in the thing table.
     *
     * @var integer
     */
    public $action_thing_id;

    /**
     * The number of characters in the repsonse.
     *
     * @var integer
     */
    public $response_size;

    /**
     * How long the controller spent on theis action - in milliseconds.
     *
     * @var integer
     */
    public $response_time;


    /**
     * The id of the sub domain that this action took place in.
     *
     * @var integer
     */
    public $subdomain_id;

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
        return '{{action_log}}';
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
            array('action_thing_id, response_size, response_time, subdomain_id', 'required'),
            array('user_id, action_thing_id, response_size, response_time, subdomain_id', 'length', 'max' => 10),
        );
    }

    /**
     * Logs an action by a user.
     *
     * @param string $controller_name The name of the controller that has being called.
     * @param string $action_name The name of the action that has being called.
     * @param array $params An array of name=>content paramater pairs.
     * @param integer $response_size The number of characters in the response for this action.
     * @param integer $response_time The time it took to generate this response in milliseconds.
     * @param integer $subdomain_id The id of the subdomain that the action was requested in.
     * @param integer [$user_id] The id of the logged in user when this action was called.
     *
     * @return void
     */
    public static function logAction($controller_name, $action_name, $params,
        $response_size, $response_time, $subdomain_id, $user_id=null
    ) {
        if (Yii::app()->params['log_db_on'] === false) {
            return;
        }
        $controller_thing_id = Thing::insertThing($controller_name);
        $action_thing_id = Thing::insertThing($action_name);
        $sql = "
            INSERT INTO action_log
            (controller_thing_id, action_thing_id, response_size, response_time, subdomain_id, user_id)
            VALUES
            (:controller_thing_id, :action_thing_id, :response_size, :response_time, :subdomain_id, :user_id)";
        $command = Yii::app()->dblog->createCommand($sql);
        $command->bindValue(":controller_thing_id", $controller_thing_id, PDO::PARAM_INT);
        $command->bindValue(":action_thing_id", $action_thing_id, PDO::PARAM_INT);
        $command->bindValue(":response_size", $response_size, PDO::PARAM_INT);
        $command->bindValue(":response_time", $response_time, PDO::PARAM_INT);
        $command->bindValue(":subdomain_id", $subdomain_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
        $new_action_id = Yii::app()->dblog->getLastInsertId();

        foreach ($params as $param_name => $param_content) {
            $param_content = TextHelper::nestedImplode($param_content);
            $param_name_thing_id = Thing::insertThing($param_name);
            $param_content_thing_id = Thing::insertThing($param_content);
            ParamLog::insertParamater($new_action_id, $param_name_thing_id, $param_content_thing_id);
        }
    }

}

?>