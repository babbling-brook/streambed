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
 * Model for the take_value_list DB table.
 * List items associated with a take field with a value_type of list
 *
 * @package PHP_Models
 */
class TakeValueList extends CActiveRecord
{

    /**
     * The primary key for this kindred take.
     *
     * @var integer
     */
    public $take_value_list_id;

    /**
     * The primary key for this kindred take.
     *
     * @var integer
     */
    public $stream_field_id;

    /**
     * The primary key for this kindred take.
     *
     * @var string
     */
    public $name;

    /**
     * The primary key for this kindred take.
     *
     * @var integer
     */
    public $value;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return TakeValueList the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'take_value_list';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('stream_field_id, name, value', 'required'),
            array('value', 'numerical', 'integerOnly' => true),
            array('stream_field_id', 'length', 'max' => 10),
            array('name', 'length', 'max' => 255),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'take_value_list_id' => 'Take Value List',
            'stream_field_id' => 'Stream Field',
            'name' => 'Name',
            'value' => 'Value',
        );
    }

    /**
     * Fetch a list of take values for a value field in a stream
     *
     * @param integer $stream_field_id The id of the field in the stream that we are fetching a list for.
     *
     * @return An array of TakeValueList rows.
     */
    public static function getList($stream_field_id) {
        return TakeValueList::model()->findAll(
            array(
                "condition" => ":stream_field_id = stream_field_id",
                "order" => "take_value_list_id",
                "params" => array(
                    ":stream_field_id" => $stream_field_id,
                ),
            )
        );
    }

    /**
     * Fetch a list of take values for a value field in a stream
     *
     * @param integer $stream_field_id The id of the field in the stream that we are fetching a list for.
     * @param string $name The name of the value being checked.
     *
     * @return boolean
     */
    public static function rowExists($stream_field_id, $name) {
        $sql = "
            SELECT take_value_list_id
            FROM take_value_list
            WHERE
                stream_field_id = :stream_field_id
                AND name = :name";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $command->bindValue(":name", $name, PDO::PARAM_STR);
        $take_value_list_id = $command->queryScalar();
        if ($take_value_list_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Calculates the next value in the list and inserts the given name with that value.
     *
     * @param integer $stream_field_id The id of the field in the stream that we are inserting a new value for.
     * @param string $name The name of the value being inserted.
     *
     * @return TakeValueList The newly inserted value.
     */
    public static function insertNewValue($stream_field_id, $name) {
        $value = self::getNextValue($stream_field_id);

        $model = new TakeValueList;
        $model->stream_field_id = $stream_field_id;
        $model->name = $name;
        $model->value = $value;
        $model->save();
        return $model;
    }

    /**
     * Fetches the next value for a value field.
     *
     * @param integer $stream_field_id The id of the field in the stream that we are fetching the next value for.
     *
     * @return integer
     */
    public static function getNextValue($stream_field_id) {
        $sql = "
            SELECT value
            FROM take_value_list
            WHERE
                stream_field_id = :stream_field_id
            ORDER BY value DESC
            LIMIt 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $value = $command->queryScalar();
        if ($value === false) {
            return 1;
        } else {
            return intval($value) + 1;
        }
    }

    /**
     * Deletes a take value row for a field.
     *
     * Requires both the primary key and the stream_field_id, as th stream_field_id can be checked for ownership
     * before calling this function.
     *
     * @param integer $take_value_list_id The primary key of the row to delete.
     * @param integer $stream_field_id The id of the field in the stream that is being deleted.
     *
     * @return void
     */
    public static function deleteRow($take_value_list_id, $stream_field_id) {
        $sql = "
            DELETE
            FROM take_value_list
            WHERE
                take_value_list_id = :take_value_list_id
                AND stream_field_id = :stream_field_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $command->bindValue(":take_value_list_id", $take_value_list_id, PDO::PARAM_INT);
        $value = $command->execute();
    }


    /**
     * Fetch a value list for a stream field, ready to be exported to json
     *
     * @param integer $stream_field_id The primary key of the extra verison of the stream.
     *
     * @return array
     */
    public static function getListForJson($stream_field_id) {
        $sql = "
            SELECT
                 value
                ,name
            FROM take_value_list
            WHERE stream_field_id = :stream_field_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Deletes take_value_list rows by their stream_field_id.
     *
     * @param integer $stream_field_id The $stream_field_id that is being used to delete take_value_list rows.
     *
     * @return void
     */
    public static function deleteByStreamFieldId($stream_field_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE FROM take_value_list
            WHERE stream_field_id = :stream_field_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of take_value_list data for a stream_field_id.
     *
     * @param type $stream_field_id The extra id of the stream to select data for.
     *
     * @return array
     */
    public static function getRowsForStreamFieldId($stream_field_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM take_value_list
                WHERE stream_field_id = :stream_field_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }


}