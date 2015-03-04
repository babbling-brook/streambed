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
 * Model for the site DB table.
 * The table keeps a record of all items entered into an open list in a stream. Used to generate suggestions.
 *
 * @package PHP_Models
 */
class StreamOpenListItem extends CActiveRecord
{

    /**
     * The primary key for this table.
     *
     * @var integer
     */
    public $stream_open_list_item_id;

    /**
     * The extra id of the stream that the open list is in.
     *
     * @var intger
     */
    public $stream_extra_id;

    /**
     * The id of the field that the suggestion is in.
     *
     * @var intger
     */
    public $field_id;

    /**
     * The list item that was entered.
     *
     * @var string
     */
    public $item;

    /**
     * A count of the number of times this item has been used.
     *
     * @var integer
     */
    public $count;

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
        return '{{stream_open_list_item}}';
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
            array('stream_extra_id, stream_extra_id, item', 'required'),
            array('stream_extra_id, stream_extra_id, count', 'length', 'max' => 10),
            array('stream_extra_id, stream_extra_id, count', 'numerical', 'integerOnly' => true),
            array('item', 'length', 'max' => 127),
        );
    }

    /**
     * Inserts a suggestion into the DB if does not exist already.
     *
     * @param integer $stream_extra_id The extra id of the stream that an list item is being inserted for.
     * @param integer $field_id The id of the field that a list item is being inserted for.
     * @fixme This is really the display_order of the stream field, not its id.
     * @param string $item The item to insert.
     *
     * @return array A list of suggested valid domains.
     */
    public static function insertItem($stream_extra_id, $field_id, $item) {
        if (self::itemExists($stream_extra_id, $field_id, $item) === true) {
            self::itemIncrement($stream_extra_id, $field_id, $item);
        } else {
            $model = new StreamOpenListItem;
            $model->stream_extra_id = $stream_extra_id;
            $model->field_id = $field_id;
            $model->item = $item;
            if ($model->save() === false) {
                throw new Exception(
                    'Trying to save a new item in StreamOpenListItem failed. stream_extra_id :'
                        . $stream_extra_id . ' field_id : ' . $stream_extra_id . ' item : ' . $item
                );
            }
        }
    }

    /**
     * Checks if a section already exists in the database.
     *
     * @param integer $stream_extra_id The extra id of the stream that an list item is being searched for.
     * @param integer $field_id The id of the field that a list item is being searched for.
     * @param string $item The item to search for.
     *
     * @return boolean
     */
    public static function itemExists($stream_extra_id, $field_id, $item) {
        $query = "
            SELECT item
            FROM stream_open_list_item
            WHERE
                stream_extra_id = :stream_extra_id
                AND field_id = :field_id
                AND item = :item";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":field_id", $field_id, PDO::PARAM_INT);
        $command->bindValue(":item", $item, PDO::PARAM_STR);
        $item = $command->queryScalar();
        if ($item === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Increments the count of an item that is already in the db.
     *
     * @param integer $stream_extra_id The extra id of the stream that an list item is being incremented.
     * @param integer $field_id The id of the field that a list item is being incremented.
     * @param string $item The item to incremented.
     *
     * @return void
     */
    public static function itemIncrement($stream_extra_id, $field_id, $item) {
        $query = "
            UPDATE stream_open_list_item
            SET count=count+1
            WHERE
                stream_extra_id = :stream_extra_id
                AND field_id = :field_id
                AND item = :item";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":field_id", $field_id, PDO::PARAM_INT);
        $command->bindValue(":item", $item, PDO::PARAM_STR);
        $item = $command->execute();
    }

    /**
     * Takes a field of open list items and inserts them into the DB.
     *
     * @param integer $stream_extra_id The extra id of the stream that an list items that are being inserted.
     * @param integer $field_id The id of the field that a list items that are being inserted.
     * @fixme This is really the display_order of the stream field, not its id.
     * @param array $items An array of the items to be inserted.
     *
     * @param type $fields
     */
    public static function insertForPost($stream_extra_id, $field_id, $items) {
        foreach ($items as $item) {
            self::insertItem($stream_extra_id, $field_id, $item);
        }
    }

    /**
     * Increments the count of an item that is already in the db.
     *
     * @param integer $stream_extra_id The extra id of the stream that an list item is being incremented.
     * @param integer $field_id The id of the field that a list item is being incremented.
     * @param string $item The item to incremented.
     *
     * @return array
     */
    public static function findSuggestions($stream_extra_id, $field_id, $text_to_fetch_suggestions_for) {
        $query = "
            SELECT item
            FROM stream_open_list_item
            WHERE
                stream_extra_id = :stream_extra_id
                AND field_id = :field_id
                AND item LIKE :text_to_fetch_suggestions_for
            LIMIT 10";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":field_id", $field_id, PDO::PARAM_INT);
        $command->bindValue(":text_to_fetch_suggestions_for", $text_to_fetch_suggestions_for . "%", PDO::PARAM_STR);
        $items = $command->queryAll();
        return $items;
    }

    /**
     * Deletes stream_open_list_item rows by their stream_extra_id.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is used to delete these row.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM stream_open_list_item
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes stream_open_list_item rows by their stream_field_id.
     *
     * @param integer $stream_field_id The $stream_field_id that is being used to delete stream_open_list_item rows.
     *
     * @return void
     */
    public static function deleteByStreamFieldId($stream_field_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE FROM stream_open_list_item
            WHERE field_id = :stream_field_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of stream_open_list_item data for a stream_field_id.
     *
     * @param type $stream_field_id The extra id of the stream to select data for.
     *
     * @return array
     */
    public static function getRowsForStreamFieldId($stream_field_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream_open_list_item
                WHERE field_id = :stream_field_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>