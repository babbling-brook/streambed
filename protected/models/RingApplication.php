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
 * Model for the ring_application DB table.
 * Holds records of users applying for membership of a ring.
 *
 * @package PHP_Models
 */
class RingApplication extends CActiveRecord
{

    /**
     * The primary key.
     *
     * @var integer
     */
    public $ring_application_id;

    /**
     * The ring that a user is applying to join.
     *
     * @var integer
     */
    public $ring_id;

    /**
     * The user who is applying to join this ring.
     *
     * @var integer
     */
    public $user_id;

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
        return 'ring_application';
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
            array('ring_id, user_id', 'required'),
        );
    }

    /**
     * Returns the primary key of a ring membership application.
     *
     * @param integer $ring_id The id of the ring that membership has been requested of.
     * @param integer $user_id The id of the user that is requesting membership.
     *
     * @returns integer|false The ring application id.
     */
    public static function getApplication($ring_id, $user_id) {
        $sql = "
            SELECT ring_application_id
            FROM ring_application
            WHERE
                ring_id = :ring_id
                AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $ring_application_id = $command->queryScalar();
        return $ring_application_id;
    }

    /**
     * Deletes an application for membership of a ring.
     *
     * @param integer $ring_id The id of the ring that a membership application is being deleted for.
     * @param integer $user_id The id of the user that a membership application is being deleted for.
     *
     * @returns integer The number of applications that have been deleted.
     */
    public static function deleteApplication($ring_id, $user_id) {
        $sql = "
            DELETE
            FROM ring_application
            WHERE
                ring_id = :ring_id
                AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $row_count = $command->execute();
        return $row_count;
    }

    /**
     * Gets the number of applicants that are awaiting validation for this ring.
     *
     * Does not count banned users that have reapplied.
     *
     * @param type $ring_id
     */
    public static function getApplicantCountForRing($ring_id) {
        $sql = "
            SELECT COUNT(ring_application.ring_application_id)
            FROM
                ring_application
                LEFT JOIN user_ring ON ring_application.ring_id = user_ring.ring_id AND user_ring.ban = 1
            WHERE
                ring_application.ring_id = :ring_id
                AND user_ring.user_ring_id IS NULL";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $row_count = $command->queryScalar();
        return $row_count;
    }

    /**
     * Inserts a users application to join a ring.
     *
     * @param integer $ring_user_id The id of the rings user.
     * @param integer $user_id The id of the user that wants to join the ring.
     *
     * @return void
     */
    public static function insertApplication($ring_user_id, $user_id) {
        $ring_id = Ring::getRingIdFromUserId($ring_user_id);

        $ring_application_id = self::getApplication($ring_id, $user_id);
        if ($ring_application_id !== false) {
            return;
        }

        $ring_application_model = new RingApplication;
        $ring_application_model->ring_id = $ring_id;
        $ring_application_model->user_id = $user_id;
        if ($ring_application_model->save() === false) {
            throw new Exception(
                'Ring aplication failed to save.' . ErrorHelper::model($ring_application_model->getErrors())
            );
        }
    }

    /**
     * Deletes ring_application rows by their ring_id.
     *
     * @param integer $ring_id The id of the ring whose ring_application data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM ring_application
            WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes ring_application rows by their user_id.
     *
     * @param integer $user_id The id of the user whose ring_application data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM ring_application
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of ring_application data for a ring.
     *
     * @param integer $ring_id The id of the ring to select data for.
     *
     * @return array
     */
    public static function getRowsForRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM ring_application
                WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>