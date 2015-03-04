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
 * Model for the tag DB table.
 * Povides the last time a user accessed their inbox.
 *
 * @package PHP_Models
 */
class WaitingPostTime extends CActiveRecord
{

    /**
     * The primary key of this table.
     *
     * @var integer
     */
    public $waiting_post_time_id;

    /**
     * The id of the user whos message count is recorded.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The site id of the local inbox. If null then this is the global inbox.
     *
     * @var integer
     */
    public $site_id;

    /**
     * Timestamp for the last time this user accessed this inbox.
     *
     * @var integer
     */
    public $time_updated;

    /**
     * The type of wait time. See Lookup table for valid values.
     *
     * @var integer $type_id
     */
    public $type_id;

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
        return 'waiting_post_time';
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
            array('user_id, type_id', 'required'),
            array('waiting_post_time_id, user_id, site_id, type_id', 'length', 'max' => 10),
            array('time_updated', 'safe'),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array();
    }

    /**
     * Store the time for when a user accessed their inbox.
     *
     * Inserts a new record if it does not exist yet.
     *
     * @param integer $user_id The id of the user whose record is being updated.
     * @param integer $time_updated Unix timestamp to insert.
     * @param string [$site_id] The id of the client site to store a time for.
     *      If blank then assumed to be the global inbox.
     * @param string $type The type of wait time. See Lookup table for valid values.
     *
     * @return void
     */
    public static function storeTime($user_id, $time_updated, $site_id=null, $type='private') {
        $old_time_updated = self::fetchTime($user_id, $site_id, $type);
        if ($old_time_updated === 0) {
            self::insertTime($user_id, $time_updated, $site_id, $type);
        } else {
            self::updateTime($user_id, $time_updated, $site_id, $type);
        }
    }

    /**
     * Insert a new time for when a user accessed their inbox.
     *
     * @param integer $user_id The id of the user whose record is being updated.
     * @param integer $time_updated Unix timestamp to insert.
     * @param string [$site_id] The id of the client site to store a time for.
     *      If blank then assumed to be the global inbox.
     * @param string $type The type of wait time. See Lookup table for valid values.
     *
     * @return void
     */
    public static function insertTime($user_id, $time_updated, $site_id=null, $type='private') {
        $type_id = LookupHelper::getId('waiting_post_time.type_id', $type);
        $model = new WaitingPostTime;
        $model->user_id = $user_id;
        $model->time_updated = date('Y-m-d H:i:s', $time_updated);
        $model->site_id = $site_id;
        $model->type_id = $type_id;
        if ($model->save() === false) {
            throw new Exception("New WaitingPostTime model did not save. " . ErrorHelper::model($model->getErrors()));
        }
    }

    /**
     * Updates the time for when a user accessed their inbox.
     *
     * @param integer $user_id The id of the user whose record is being updated.
     * @param integer $time_updated Unix timestamp to insert.
     * @param string [$site_id] The id of the client site to store a time for.
     *      If blank then assumed to be the global inbox.
     * @param string $type The type of wait time. See Lookup table for valid values.
     *
     * @return void
     */
    public static function updateTime($user_id, $time_updated, $site_id=null, $type='private') {
        $type_id = LookupHelper::getId('waiting_post_time.type_id', $type);
        $condition = 'user_id = :user_id AND type_id = :type_id';
        $params = array(
            ':user_id' => $user_id,
            ':type_id' => $type_id,
        );
        if (isset($site_id) === false) {
            $condition .= ' AND site_id IS NULL';
        } else {
            $condition .= ' AND site_id = :site_id';
            $params[':site_id'] = $site_id;
        }
        $model = WaitingPostTime::model()->find($condition, $params);
        $model->time_updated = date('Y-m-d H:i:s', $time_updated);
        if ($model->save() === false) {
            throw new Exception(
                "Update WaitingPostTime model did not save. " . ErrorHelper::model($model->getErrors())
            );
        }
    }

    /**
     * Returns a stored timestamp for an inbox
     *
     * @param integer $user_id The id of the user whose record is being updated.
     * @param string [$site_id] The id of the client site that a time is being fetched for.
     *      If blank then assumed to be the global inbox.
     * @param string $type The type of wait time. See Lookup table for valid values.
     *
     * @return integer Returns zero if no record found.
     */
    public static function fetchTime($user_id, $site_id=null, $type='private') {
        if ($site_id === null) {
            return self::fetchTimeForGlobal($user_id, $type);
        } else {
            return self::fetchTimeForSite($user_id, $site_id, $type);
        }
    }

    /**
     * Returns a stored timestamp for an inbox
     *
     * @param integer $user_id The id of the user whose record is being updated.
     * @param string $site_id The id of the client site that a time is being fetched for.
     *      If blank then assumed to be the global inbox.
     * @param string $type The type of wait time. See Lookup table for valid values.
     *
     * @return integer Returns zero if no record found.
     */
    private static function fetchTimeForSite($user_id, $site_id, $type) {
        $type_id = LookupHelper::getId('waiting_post_time.type_id', $type);
        $sql = "
            SELECT UNIX_TIMESTAMP(time_updated)
            FROM waiting_post_time
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND type_id = :type_id";
        $command = Yii::app()->db->createCommand($sql);
        // @fixme need to refactor fetching for a time - needs to be between two times.
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":type_id", $type_id, PDO::PARAM_INT);

        $timestamp = $command->queryScalar();
        if ($timestamp === false) {
            $timestamp = 0;
        }
        return $timestamp;
    }

    /**
     * Returns a stored timestamp for an inbox
     *
     * @param integer $user_id The id of the user whose record is being updated.
     * @param string $type The type of wait time. See Lookup table for valid values.
     *
     * @return integer Returns zero if no record found.
     */
    private static function fetchTimeForGlobal($user_id, $type) {
        $type_id = LookupHelper::getId('waiting_post_time.type_id', $type);
        $sql = "
            SELECT UNIX_TIMESTAMP(time_updated)
            FROM waiting_post_time
            WHERE
                user_id = :user_id
                AND site_id IS NULL
                AND type_id = :type_id";
        $command = Yii::app()->db->createCommand($sql);
        // @fixme need to refactor fetching for a time - needs to be between two times.
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":type_id", $type_id, PDO::PARAM_INT);
        $timestamp = $command->queryScalar();
        if ($timestamp === false) {
            $timestamp = 0;
        }
        return $timestamp;
    }

    /**
     * Deletes waiting_post_time rows by their user_id.
     *
     * @param integer $user_id The id of the user whose waiting post time data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM waiting_post_time
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of waiting_post_time data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM waiting_post_time
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>