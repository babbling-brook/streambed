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
 * The table holds data for a rhythm that a user is subscribed to.
 *
 * @package PHP_Models
 */
class RhythmUserData extends CActiveRecord
{

    /**
     * The primary key of the config table.
     *
     * @var integer
     */
    public $rhythm_user_data_id;


    /**
     * The extra id of the rhythm that has stored this data.
     *
     * @var integer
     */
    public $rhythm_extra_id;


    /**
     * The id of the user that owns this data.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The JSON data that is being stored.
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
        return 'rhythm_user_data';
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
            array('rhythm_extra_id, user_id, data', 'required'),
            array('rhythm_extra_id, user_id', 'length', 'max' => 10),
            array('data', 'length', 'max' => 10000),
        );
    }

    /**
     * Store a data row for a rhythm_extra_id and user_id.
     *
     * @param integer $rhythm_extra_id The id of the rhythm that data is being fetched for.
     * @param integer $user_id The id of the user that data is being fetched for.
     * @param string $data The string of JSON data to be stored.
     *
     * @return void
     */
    public static function storeData($rhythm_extra_id, $user_id, $data) {
        $model = new RhythmUserData;

        // Update an existing row if one already exists for this rhythme/user combo.
        $rhythm_user_data_id = self::getId($rhythm_extra_id, $user_id);
        if ($rhythm_user_data_id !== false) {
            $model->rhythm_user_data_id = $rhythm_user_data_id;
            $model->isNewRecord = false;

        }

        $model->rhythm_extra_id = $rhythm_extra_id;
        $model->user_id = $user_id;
        $model->data = $data;
        $model->save();
    }

    /**
     * Fetch a data row from its rhythm_extra_id and user_id
     *
     * @param integer $rhythm_extra_id The id of the rhythm that data is being fetched for.
     * @param integer $user_id The id of the user that data is being fetched for.
     *
     * @return string|false
     */
    public static function getData($rhythm_extra_id, $user_id) {
        $query = "
            SELECT data
            FROM rhythm_user_data
            WHERE
                rhythm_extra_id = :rhythm_extra_id
                AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $data = $command->queryScalar();
        return $data;
    }

    /**
     * Fetch the id of a row from its rhythem and user ids.
     *
     * @param integer $rhythm_extra_id The id of the rhythm that an id is being fetched for.
     * @param integer $user_id The id of the user that an id is being fetched for.
     *
     * @return integer|false
     */
    public static function getId($rhythm_extra_id, $user_id) {
        $query = "
            SELECT rhythm_user_data_id
            FROM rhythm_user_data
            WHERE
                rhythm_extra_id = :rhythm_extra_id
                AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rhythm_user_data_id = $command->queryScalar();
        return $rhythm_user_data_id;
    }

    /**
     * Deletes rhythm_user_data rows by their user_id.
     *
     * @param integer $user_id The id of the user whose rhythm_user_data data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM rhythm_user_data
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes rhythm_user_data rows by their rhythm_extra_id.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm in rhythm_user_data that is being deleted.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM rhythm_user_data
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of rhythm_user_data data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM rhythm_user_data
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>