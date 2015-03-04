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
 * Model for the stream_block DB table.
 * The table holds information about blocks of streams.
 * Post are connected to block numbers to make caching of the data easier.
 * These block numbers are for blocks of posts designed for trees.
 *
 * @package PHP_Models
 */
class StreamBlockTree extends CActiveRecord
{

    /**
     * The primary key of this table.
     *
     * @var integer
     */
    public $stream_block_tree_id;

    /**
     * The extra id of the stream that owns this block number.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     *  A unix timestanp representing the start time for posts created being assigned to this block.
     *
     * @var integer
     */
    public $start_time;

    /**
     * A unix timestanp representing the ending time for posts created being assigned to this block.
     *
     * @var integer
     */
    public $end_time;

    /**
     * A block number that is assigned a group of posts in an stream.
     *
     * @var integer
     */
    public $block_number;

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
        return 'stream_block_tree';
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
            array('post_id, start_time, end_time, block_number', 'required'),
            array('post_id, start_time, end_time, block_number', 'length', 'max' => 10),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'stream_block_tree_id' => 'Stream Block Tree',
            'post_id' => 'Stream Extra',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'block_number' => 'Block Number',
        );
    }

    /**
     * Get the block number for an stream and timestamp.
     *
     * @param integer $post_id The post id to fetch a tree block number for.
     * @param integer $time The time to fetch a block number from.
     *
     * @return integer|boolean Block number or false.
     */
    public static function getBlockNumber($post_id, $time) {
        $query = "
            SELECT block_number
            FROM stream_block_tree
            WHERE
                start_time < :time
                AND end_time >= :time
                AND post_id = :post_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":time", $time, PDO::PARAM_INT);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $block_number = $command->queryScalar();
        return $block_number;
    }

    /**
     * Get the nearest block number that has a timestamp that is earlier than the requested timestamp for an stream.
     *
     * @param integer $post_id The post id to fetch a tree block number for.
     * @param integer $time The time to fetch a block number from.
     *
     * @return integer|boolean Block number or false.
     */
    public static function getNearestBlockNumber($post_id, $time) {
        $query = "
            SELECT block_number
            FROM stream_block_tree
            WHERE
                end_time < :time
                AND post_id = :post_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":time", $time, PDO::PARAM_INT);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $block_number = $command->queryScalar();
        return $block_number;
    }

    /**
     * Fetch the latest block number for this stream.
     *
     * @param integer $post_id The post id to fetch a tree block number for.
     *
     * @return integer Block number or 0.
     */
    public static function getLatest($post_id) {
        $query = "
            SELECT block_number
            FROM stream_block_tree
            WHERE post_id = :post_id
            ORDER BY block_number DESC
            LIMIT 1";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $block_number = $command->queryScalar();
        if ($block_number === false) {
            $block_number = 0;
        }
        return $block_number;
    }

    /**
     * Deletes stream_block_tree rows by their post_id.
     *
     * @param integer $post_id The id of the post in stream_block_tree that is being deleted.
     *
     * @return void
     */
    public static function deleteByPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM stream_block_tree
            WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of stream_block_tree data for a post.
     *
     * @param type $post_id The id of the post to select data for.
     *
     * @return array
     */
    public static function getRowsForPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream_block_tree
                WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>