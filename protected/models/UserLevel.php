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
 * Model for the user_level DB table.
 * Stores info relating to the level of the current user.
 *
 * @package PHP_Models
 */
class UserLevel extends CActiveRecord
{

    /**
     * The primary key for this site.
     *
     * @var integer
     */
    public $level_id;

    /**
     * The id of the user.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The name of the tutorial set being followed (See lookup table).
     *
     * @var integer
     */
    public $tutorial_set;

    /**
     * The name of the level currently on (See lookup table).
     *
     * @var integer
     */
    public $level_name;

    /**
     * Are tutorials enabled for this user.
     *
     * @var boolean
     */
    public $enabled;

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
        return '{{user_level}}';
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
            array('user_id', 'required'),
            array('level_id, user_id, tutorial_set, level_name,', 'length', 'max' => 10),
            array('level_id, user_id, tutorial_set, level_name', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * Inserts a level row for a new user.
     *
     * @param integer $user_id The id of user a level row is being inserted for.
     *
     * @return void
     */
    public static function createUser($user_id) {
        $user_level_model = new UserLevel;
        $user_level_model->user_id = $user_id;
        if ($user_level_model->save() === false) {
            throw new Exception('Level data not saved : ' . ErrorHelper::model($user_level_model->getErrors()));
        }
    }

    /**
     * Fetches the level for a user.
     *
     * @param integer $user_id Thei id of the user to fetch a level for.
     *
     * @return array with two names values for tuotiral_set and level_name.
     */
    public static function getLevel($user_id) {
        $sql = "
            SELECT
                tutorial_set
                ,level_name
                ,enabled
            FROM user_level
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        if ($row === false) {
            self::createUser($user_id);
            return self::getLevel($user_id);
        }
        if (intval($row['enabled']) === 0) {
            return array(
                'tutorial_set' => false,
                'level_name' => false,
            );
        } else {
            return array(
                'tutorial_set' => LookupHelper::getValue($row['tutorial_set']),
                'level_name' => LookupHelper::getValue($row['level_name']),
            );
        }
    }

    public static function getLevelNumber($level_name) {
        $sql = "
            SELECT
                sort_order
            FROM lookup
            WHERE lookup_id = :level_name_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":level_name_id", $level_name, PDO::PARAM_INT);
        $sort_order = $command->queryScalar();
        return $sort_order;
    }

    /**
     * Gets a level row for a user.
     *
     * @param integer $user_id The id of the user who's level data is being fetched.
     *
     * @return Yii model
     */
    public static function getUserRow($user_id) {
        $row = UserLevel::model()->find(
            array(
                'condition' => 'user_id=:user_id',
                'params' => array(
                    ':user_id' => $user_id,
                ),
            )
        );
        // convert tinyint to true true/false
        if ($row->enabled === '0') {
            $row->enabled = false;
        } else {
            $row->enabled = true;
        }
        return $row;
    }

    /**
     * Restart the tutorial system for a user.
     *
     * @param integer $user_id The id of the user to start the tutorial system for.
     *
     * @return void
     */
    public static function startTutorials($user_id) {
        $row = self::getUserRow($user_id);
        $row->enabled = 1;
        $row->update('enabled');
    }

    /**
     * Turn tutorials off for a user.
     *
     * @param integer $user_id The id of the user that tutorials are being turned off for.
     *
     * @return void
     */
    public static function exitTutorials($user_id) {
        $row = self::getUserRow($user_id);
        $row->enabled = 0;
        $row->update('enabled');
    }

    /**
     * Turn tutorials on for a user.
     *
     * @param integer $user_id The id of the user that tutorials are being turned back on for.
     *
     * @return integer The users current level.
     */
    public static function restartTutorials($user_id) {
        $row = self::getUserRow($user_id);
        $row->enabled = 1;
        $row->level_name = 167;
        $row->update();
        return $row->level_name;
    }


    /**
     * Level a user up to the next level
     *
     * @param integer $user_id The id of the user that is being leveld up.
     *
     * @return integer The users current level.
     */
    public static function levelUp($user_id) {
        $row = self::getUserRow($user_id);

        $sort_order = self::getLevelNumber($row['level_name']);

        $sql = "
            SELECT
                lookup_id
            FROM lookup
            WHERE column_name = 'user_level.level_name'
                AND sort_order > :sort_order
            ORDER BY sort_order
            LIMIT 1";
        $command2 = Yii::app()->db->createCommand($sql);
        $command2->bindValue(":sort_order", $sort_order, PDO::PARAM_INT);
        $level_name_id = $command2->queryScalar();

        $row->level_name = $level_name_id;
        $row->update('level_name');
        return LookupHelper::getValue($level_name_id);
    }

    /**
     * Deletes user_level rows by their user_id.
     *
     * @param integer $user_id The id of the user whose user level data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_level
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select a users row of user_level data.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_level
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        return $row;
    }
}

?>