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
 * Model for the stream_list DB table.
 * The table holds list items for list fields in streams.
 *
 * @package PHP_Models
 */
class StreamList extends CActiveRecord
{

    /**
     * The primary key of this list item.
     *
     * @var integer
     */
    public $stream_list_id;

    /**
     * The primary key of the field that this list item belongs to.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The name of the list item.
     *
     * @var string
     */
    public $name;

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
        return 'stream_list';
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
            array('stream_field_id, name', 'required'),
            array('stream_field_id', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 127),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('stream_list_id, stream_field_id, name', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'stream_field' => array(
                self::BELONGS_TO,
                'StreamField',
                'stream_field_id',
                'joinType' => 'INNER JOIN',
            ),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'stream_list_id' => 'Stream List',
            'stream_field_id' => 'Stream Field',
            'name' => 'List Item',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('stream_list_id', $this->stream_list_id);
        $criteria->compare('stream_field_id', $this->stream_field_id);
        $criteria->compare('name', $this->name, true);

        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
            )
        );
    }

    /**
     * Count the number of items in a list.
     *
     * @param integer $stream_field_id The id of the field that the list belongs to.
     *
     * @return integer
     */
    public static function countItems($stream_field_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     COUNT(stream_field_id) AS qty
                FROM stream_list
                WHERE stream_field_id = :stream_field_id ";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $qty = $command->queryScalar();
        return $qty;
    }

    /**
     * Deletes stream_list rows by their stream_field_id.
     *
     * @param integer $stream_field_id The $stream_field_id that is being used to delete stream_list rows.
     *
     * @return void
     */
    public static function deleteByStreamFieldId($stream_field_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE FROM stream_list
            WHERE stream_field_id = :stream_field_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $command->execute();
    }


    /**
     * Gets all items in a list. Sorted alphabeticly.
     *
     * @param integer $field_id The stream field ID to select the list for.
     *
     * @return Array of StreamList
     */
    public static function getList($field_id) {
        return StreamList::model()->findAll(
            array(
                "condition" => ":stream_field_id = stream_field_id",
                "order" => "name",
                "params" => array(
                    ":stream_field_id" => $field_id,
                ),
            )
        );
    }

    /**
     * Does a list item exist.
     *
     * @param string $name The name of the list item we are checking.
     * @param integer $field_id The stream field ID to select the list for.
     *
     * @return boolean
     */
    public static function doesItemExist($name, $field_id) {
        return StreamList::model()->exists(
            array(
                "condition" => ":stream_field_id=stream_field_id AND :name=name",
                "params" => array(
                    ":name" => $name,
                    ":stream_field_id" => $field_id,
                )
            )
        );
    }

    /**
     * Insert a new list item.
     *
     * @param string $name The name of the list item we are inserting.
     * @param integer $field_id The id of the field that we are inserting the list item into.
     * @param StreamList $model Created if not passed in.
     *
     * @return StreamList model
     */
    public static function insertItem($name, $field_id, $model=null) {
        if ($model === null) {
            $model = new StreamList;
        }

        $model->name = $name;
        $model->stream_field_id = $field_id;
        $model->save();
        return $model;
    }

    /**
     * Deletes a list entry.
     *
     * @param string $name The name of the list item we are deleting.
     * @param integer $field_id The id of the field we are deleting the list item from.
     *
     * @return void
     */
    public static function deleteItem($name, $field_id) {
        StreamList::model()->deleteAll(
            array(
                "condition" => ":stream_field_id=stream_field_id AND :name=name",
                "params" => array(
                    ":name" => $name,
                    ":stream_field_id" => $field_id,
                )
            )
        );
    }

    /**
     * Copy a list from an old stream_field_id to a new one.
     *
     * @param integer $old_id The id of the stream field that we are copying a list from.
     * @param integer $new_id The id of the stream field that are copying a list to.
     *
     * @return void
     */
    public static function copyList($old_id, $new_id) {
        $old_rows  = StreamList::model()->findAll(
            array(
                "condition" => ":stream_field_id=stream_field_id",
                "params" => array(
                    ":stream_field_id" => $old_id,
                ),
            )
        );

        foreach ($old_rows as $row) {
            $row->isNewRecord = true;
            $row->stream_field_id = $new_id;
            $row->stream_list_id = null;
            $row->save();
        }
    }

    /**
     * Get a list and convert it to an array of options.
     *
     * @param integer $field_id The stream field ID to select the list for.
     *
     * @return @array
     */
    public static function getArray($field_id) {
        $models = StreamList::getList($field_id);

        if (isset($model) === false) {
            return array();
        }

        $arr = array();
        foreach ($model as $model) {
            $arr[$model->stream_list_id] = $model->name;
        }
        return $arr;
    }

    /**
     * Select rows of stream_list data for a stream_field_id.
     *
     * @param type $stream_field_id The extra id of the stream to select data for.
     *
     * @return array
     */
    public static function getRowsForStreamFieldId($stream_field_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream_list
                WHERE stream_field_id = :stream_field_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>