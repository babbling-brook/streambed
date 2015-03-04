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
 * Model for the post_private_recipient DB table.
 * The table is link a table for users who have access to private posts.
 *
 * @package PHP_Models
 */
class PostPrivateRecipient extends CActiveRecord
{

    /**
     * Primary key for this link.
     *
     * @var integer
     */
    public $post_private_recipient_id;


    /**
     * The id of the private post that a user has access to
     *
     * @var integer
     */
    public $post_id;

    /**
     * The id of the user that has access to this post.
     *
     * @var integer
     */
    public $user_id;


    /**
     * Has the recipient of this message deleted this message.
     *
     * @var boolean
     */
    public $deleted;

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
        return '{{post_private_recipient}}';
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
            array('post_id, user_id', 'required'),
            array('post_id, user_id', 'length', 'max' => 10),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'post_id' => 'Post ID',
            'user_id' => 'User ID',
        );
    }

    /**
     * Sets the deleted flag for a link.
     *
     * Used to indicate that the recipient of an post has deleted it.
     *
     * @param integer $post_id The id of the post that owns the link.
     * @param integer $user_id The id of the user that we are looking for a link for.
     *
     * @return array customized attribute labels (name=> label).
     */
    public static function setDeleted($post_id, $user_id) {
        $sql = "UPDATE post_private_recipient
                SET deleted = 1
                WHERE
                    post_id = :post_id
                    AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_id = $command->execute();
    }

    /**
     * Returns if an a private post recipient has delted their link.
     *
     * @param integer $post_id The id of the post that owns the link.
     * @param integer $user_id The id of the user that we are looking for a link for.
     *
     * @return boolean Also returns true if the link does not exist.
     */
    public static function getDeleted($post_id, $user_id) {
        $sql = "SELECT deleted
                FROM post_private_recipient
                WHERE
                    post_id = :post_id
                    AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $deleted = $command->queryScalar();
        if ($deleted === false || $deleted === '1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns if an a private post has been sent to a user.
     *
     * @param integer $post_id The id of the post that owns the link.
     * @param integer $user_id The id of the user that we are looking for a link for.
     *
     * @return boolean
     */
    public static function isRecipient($post_id, $user_id) {
        $sql = "SELECT post_private_recipient_id
                FROM post_private_recipient
                WHERE
                    post_id = :post_id
                    AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $post_private_recipient_id = $command->queryScalar();
        if ($post_private_recipient_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Deletes post_private_recipient rows by their user_id
     *
     * @param integer $user_id The id of the user whose post_private_recipient data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post_private_recipient
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes post_private_recipient rows by their post_id
     *
     * @param integer $post_id The id of the post in post_private_recipient that is being deleted.
     *
     * @return void
     */
    public static function deleteByPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post_private_recipient
            WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of post_private_recipient data for a post_id.
     *
     * @param type $post_id The id of the post to select data for.
     *
     * @return array
     */
    public static function getRowsForPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM post_private_recipient
                WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Select rows of post_private_recipient data for a user_id.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM post_private_recipient
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>