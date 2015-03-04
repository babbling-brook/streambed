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
 * Model for the ring_rhythm_data DB table.
 * Data stored by a rings rhythms after its admins and members have run them.
 *
 * @package PHP_Models
 */
class RingRhythmData extends CActiveRecord
{

    /**
     * The priamry key for this table.
     *
     * @var integer
     */
    public $ring_rhythm_data_id;

    /**
     * Id of the ring this data belongs to.
     *
     * @var integer
     */
    public $ring_id;

    /**
     * Id of the user who is a member of the ring that generated this data.
     *
     * @var integer
     */
    public $user_id;

    /**
     * Date the data was created or updated.
     *
     * @var string
     */
    public $date_created;

    /**
     * The type of rhythm that was running. See lookup table for values.
     *
     * @var integer
     */
    public $type_id;

    /**
     * The data being stored.
     *
     * @var string
     */
    public $data;

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
        return 'ring_rhythm_data';
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
            array('ring_id, user_id, type_id, data', 'required'),
            array('ring_rhythm_data_id', 'ring_id, user_id, type_id', 'numerical', 'integerOnly' => true),
            array('data', 'ruleData'),
        );
    }

    /**
     * Checks that the data is the corect length.
     *
     * @return void
     */
    public function ruleData() {
        $type = LookupHelper::validId("ring_rhythm_data.type", $this->type_id);
        if ($type === 'admin') {
            if (strtlen($this->data) > Yii::app()->params['max_ring_admin_data_length']) {
                $this->addError(
                    'data',
                    'Invalid data. Length must be less than '
                        . Yii::app()->params['max_ring_admin_data_length'] . ' characters.'
                );
            }
        } else if ($type === 'member') {
            if (strtlen($this->data) > Yii::app()->params['max_ring_member_data_length']) {
                $this->addError(
                    'data',
                    'Invalid data. Length must be less than '
                        . Yii::app()->params['max_ring_member_data_length'] . ' characters.'
                );
            }
        }
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * Get the primary key of a row from a ring/user combination
     *
     * @param type $ring_id The id of the ring to check.
     * @param type $user_id Teh id of the user to check.
     *
     * @return integer|false The primary key or false.
     */
    public static function getId($ring_id, $user_id) {
        $sql = "
            SELECT ring_rhythm_data_id
            FROM ring_rhythm_data
            WHERE ring_rhythm_data
                ring_id = :ring_id
                AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        // @fixme need to refactor fetching for a time - needs to be between two times.
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $ring_rhythm_data_id = $command->queryScalar();
        return $ring_rhythm_data_id;
    }

    /**
     * Fetch the data for a ring/user combination
     *
     * @param type $ring_id
     * @param type $user_id
     *
     * @return array|false A data row containing the date_created, type_id and data requested.
     */
    public static function getData($ring_id, $user_id) {
        $sql = "
            SELECT
                 date_created
                ,type_id
                ,data
            FROM ring_rhythm_data
            WHERE ring_rhythm_data
                ring_id = :ring_id
                AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        // @fixme need to refactor fetching for a time - needs to be between two times.
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        return $row;
    }

    /**
     * Deletes ring_rhtyhm_data rows by their ring_id.
     *
     * @param integer $ring_id The id of the ring whose ring_rhythm_data data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM ring_rhythm_data
            WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes ring_rhtyhm_data rows by their user_id.
     *
     * @param integer $user_id The id of the user whose ring_rhythm data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM ring_rhythm_data
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of ring_rhythm_data data for a ring.
     *
     * @param integer $ring_id The id of the ring to select data for.
     *
     * @return array
     */
    public static function getRowsForRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM ring_rhythm_data
                WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>