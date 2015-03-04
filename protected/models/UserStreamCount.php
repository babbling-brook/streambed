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
 * Model for the user_stream_count DB table.
 * The number of times a user has taken an stream with a kind=user.
 * Provides pre calculated results for popularity of streams on profile pages.
 *
 * @package PHP_Models
 */
class UserStreamCount extends CActiveRecord
{
    /**
     * The primary key of the table.
     *
     * @var integer
     */
    public $user_stream_count_id;

    /**
     * The id of the user whose takes we being counted.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The extra id of the stream that is being counted.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The total count of the takes using this stream.
     *
     * @var integer
     */
    public $total;

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
        return 'user_stream_count';
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
            array('user_id, stream_extra_id, total', 'required'),
            array('user_id, stream_extra_id, total', 'length', 'max' => 10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            //array('user_stream_count_id, user_id, stream_extra_id, count', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_stream_count_id' => 'User Stream Count',
            'user_id' => 'User',
            'stream_extra_id' => 'Stream Extra',
            'total' => 'Total',
        );
    }

    /**
     * Decrement the total number of times an stream has been used by a user.
     *
     * @param type $user_id The id of the user whose usage is being decremented.
     * @param type $stream_extra_id The extra id of the stream that is being decremented for a user.
     *
     * @return void
     */
    public static function decrement($user_id, $stream_extra_id) {
        $total = self::getRow($user_id, $stream_extra_id);

        if ($total === 1) {
            self::deleteRow($user_id, $stream_extra_id);
        } else {
            self::updateRow($user_id, $stream_extra_id, $total - 1);
        }
    }

    /**
     * Increment the total number of times an stream has been used by a user.
     *
     * @param type $user_id The id of the user whose usage is being incremented.
     * @param type $stream_extra_id The extra id of the stream that is being incremented for a user.
     *
     * @return void
     */
    public static function increment($user_id, $stream_extra_id) {
        $total = self::getRow($user_id, $stream_extra_id);

        if ($total === false || $total === 0) {
            self::insertRow($user_id, $stream_extra_id);
        } else {
            self::updateRow($user_id, $stream_extra_id, $total + 1);
        }
    }

    /**
     * Update the total number of times an stream has been used by a user.
     *
     * @param integer $user_id The id of the user whose usage is being updated.
     * @param integer $stream_extra_id The extra id of the stream that is being updated for a user.
     * @param integer $total The total value of the count.
     *
     * @return boolean
     */
    private static function updateRow($user_id, $stream_extra_id, $total) {
        $sql = "
            UPDATE user_stream_count
            SET total = :total
            WHERE
                user_id = :user_id
                AND stream_extra_id = :stream_extra_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":total", $total, PDO::PARAM_INT);
        return $command->execute();
    }

    /**
     * Insert the first usage of an stream by a user.
     *
     * @param type $user_id The id of the user whose usage is being inserted.
     * @param type $stream_extra_id The extra id of the stream that is being inserted for a user.
     *
     * @return boolean
     */
    private static function insertRow($user_id, $stream_extra_id) {
        $sql = "
            INSERT INTO user_stream_count
            (
                 user_id
                ,stream_extra_id
                ,total
            ) VALUES (
                 :user_id
                ,:stream_extra_id
                ,1
            )";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        return $command->execute();
    }

    /**
     * Fetch the total number of times an stream has been used by a user.
     *
     * @param type $user_id The id of the user whose usage is being fetched.
     * @param type $stream_extra_id The extra id of the stream that is being fetched for a user.
     *
     * @return integer
     */
    private static function getRow($user_id, $stream_extra_id) {
        $sql = "
            SELECT
                 total
            FROM user_stream_count
            WHERE
                user_id = :user_id
                AND stream_extra_id = :stream_extra_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        return (int)$command->queryScalar();
    }

    /**
     * Deletes user_stream_count rows by their stream_extra_id.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is used to delete these rows.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM user_stream_count
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_stream_count rows by their user_id.
     *
     * @param integer $user_id The id of the user whose user_stream_count data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_stream_count
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of user_stream_count data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_stream_count
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>