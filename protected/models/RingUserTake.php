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
 * Model for the ring_user_take DB table.
 * The table holds the information about takes by users in the name of a ring.
 *
 * @package PHP_Models
 */
class RingUserTake extends CActiveRecord
{

    /**
     * The primary key of this take by a user in the name of a ring.
     *
     * @var integer
     */
    public $ring_user_take_id;

    /**
     * The id of the user that made this take.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The id of the take name tat was used to take this post.
     *
     * @var integer
     */
    public $ring_take_name_id;

    /**
     * The id of the post that was taken.
     *
     * @var integer
     */
    public $post_id;

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
        return 'ring_user_take';
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
            array('user_id, ring_take_name_id, post_id', 'required'),
            array('user_id, ring_take_name_id, post_id', 'numerical', 'integerOnly' => true),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            //array('ring_user_take_id, user_id, ring_take_name_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'ring_user_take_id' => 'Ring User Take',
            'user_id' => 'User',
            'ring_take_name_id' => 'Ring Take Name',
            'post_id' => 'Post ID',
        );
    }

    /**
     * Check if a user has already taken with this take name.
     *
     * @param integer $ring_take_name_id The id of the take name to check.
     * @param integer $user_id The id of the user to check.
     * @param integer $post_id The id of hte post to check.
     *
     * @return boolean Is it taken or not.
     */
    public static function isTaken($ring_take_name_id, $user_id, $post_id) {
        $sql = "
            SELECT  ring_user_take_id
            FROM
                ring_user_take
            WHERE
                ring_take_name_id = :ring_take_name_id
                AND user_id = :user_id
                AND post_id = :post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_take_name_id", $ring_take_name_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $ring_user_take_id = $command->queryScalar();
        if ($ring_user_take_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Mark an post as taken by a user for a ring.
     *
     * @param integer $ring_take_name_id The id of the take name to use in making this take.
     * @param integer $user_id The id of th euser who is making this take.
     * @param integer $post_id The id of the post that is being taken.
     *
     * @return void
     */
    public static function take($ring_take_name_id, $user_id, $post_id) {
        $query = "
            INSERT INTO ring_user_take
            (
                 user_id
                ,ring_take_name_id
                ,post_id
            ) VALUES (
                 :user_id
                ,:ring_take_name_id
                ,:post_id
            )";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_take_name_id", $ring_take_name_id, PDO::PARAM_INT);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Remove an post being marked as taked for a ring by a user.
     *
     * @param integer $ring_take_name_id The take name to delete a take from.
     * @param integer $user_id The id of the user who made the take that is being deleted.
     * @param integer $post_id The id of the post that was taken.
     *
     * @return void
     */
    public static function untake($ring_take_name_id, $user_id, $post_id) {
        $query = "
            DELETE
            FROM
                ring_user_take
            WHERE
                user_id = :user_id
                AND ring_take_name_id = :ring_take_name_id
                AND post_id = :post_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_take_name_id", $ring_take_name_id, PDO::PARAM_INT);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Gets the take status for a rings take_names for a user.
     *
     * @param RingTakeStatusForm $form A validated form containing details of the take to fetch the status for.
     *
     * @return array Associative array indexed by take name with value being 1 or 0.
     */
    public static function getStatus($form) {
        // This query has a sub select so that takes by the same user
        // for a different post are not mixed up with the current request.
        $sql = "SELECT
                     ring_take_name.name AS take_name
                    ,tb.ring_user_take_id
                    ,tb.user_id
                FROM
                    ring_take_name
                    LEFT JOIN (
                        SELECT
                            ring_take_name_id
                           ,ring_user_take_id
                            ,post_id
                            ,user_id
                        FROM ring_user_take
                        WHERE
                            ring_user_take.user_id = :user_id
                            AND ring_user_take.post_id = :post_id
                    ) AS tb ON ring_take_name.ring_take_name_id = tb.ring_take_name_id
                WHERE
                    (tb.user_id = :user_id OR tb.user_id IS NULL)
                    AND ring_take_name.ring_id = :ring_id
                    AND (tb.post_id = :post_id OR tb.post_id IS NULL)
                    AND ring_take_name.field_id = :field_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $form->ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $form->user_id, PDO::PARAM_INT);
        $command->bindValue(":post_id", $form->site_post_id, PDO::PARAM_INT);
        $command->bindValue(":field_id", $form->field_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        if (empty($rows) === true) {
            return $rows;
        }

        $results = array();
        foreach ($rows as $row) {
            $results[$row['take_name']] = (isset($row['ring_user_take_id']) === true) ? 1 : 0;
        }
        return $results;
    }

    /**
     * Deletes ring_user_take rows by their user_id.
     *
     * @param integer $user_id The id of the user whose ring user take data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM ring_user_take
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes ring_user_take rows by their ring_take_name_id.
     *
     * @param integer $ring_take_name_id The id of the ring_take_name whhose ring_user_take data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingTakeNameId($ring_take_name_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM ring_user_take
            WHERE ring_take_name_id = :ring_take_name_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_take_name_id", $ring_take_name_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes ring_user_take rows by their post_id.
     *
     * @param integer $post_id The id of the post in ring_user_take that is being deleted.
     *
     * @return void
     */
    public static function deleteByPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM ring_user_take
            WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of ring_user_take data for a user_id.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM ring_user_take
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>