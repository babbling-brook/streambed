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
 * Model for the user_ring_password DB table.
 * The passwords for a users access to rings, both local and remote
 *
 * @package PHP_Models
 */
class UserRingPassword extends CActiveRecord
{

    /**
     * The primary key of the table.
     *
     * @var integer
     */
    public $user_ring_password_id;

    /**
     * The id of the user whoose password is stored here.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The id of the user that represents the ring that this password grants access to.
     *
     * @var integer
     */
    public $ring_user_id;

    /**
     * The password.
     *
     * @var string
     */
    public $password;

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
        return 'user_ring_password';
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
            array('user_id, ring_user_id, password', 'required'),
            array('user_id, ring_user_id', 'length', 'max' => 10),
            array('password', 'length', 'max' => 255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            //array('user_ring_password_id, user_id, ring_user_id, password', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_ring_password_id' => 'User Ring Password',
            'user_id' => 'User',
            'ring_user_id' => 'Ring User',
            'password' => 'Password',
        );
    }

    /**
     * Insert a new password for a users membership of a ring.
     *
     * @param integer $user_id The id of the user to insert a password for.
     * @param integer $ring_user_id The id of the user that represents the ring that this password grants access to.
     * @param string $password The password.
     *
     * @return void
     */
    public static function insertPassword($user_id, $ring_user_id, $password) {
        $sql = "
            INSERT INTO user_ring_password
            (
                 user_id
                ,ring_user_id
                ,password
            )VALUES(
                :user_id
                ,:ring_user_id
                ,:password
            )";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $command->bindValue(":password", $password, PDO::PARAM_STR);
        $command->execute();
    }

    /**
     * Check if a users ring password is valid.
     *
     * @param integer $user_id The id of the user to check a password for.
     * @param integer $ring_user_id The id of the user that represents the ring that this password grants access to.
     * @param string $password The password.
     *
     * @return boolean
     * @fixme should be hashing the passwords for storage and checking the hash.
     */
    public static function isPasswordValid($user_id, $ring_user_id, $password) {
        $sql = "
            SELECT  user_ring_password_id
            FROM user_ring_password
            WHERE
                ring_user_id = :ring_user_id
                AND user_id = :user_id
                AND password = :password";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $command->bindValue(":password", $password, PDO::PARAM_STR);
        $user_ring_password_id = $command->queryScalar();
        if ($user_ring_password_id === 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Fetches a users password to access a ring.
     *
     * @param integer $user_id The id of the user who is a member of a ring and whose password is being fetched.
     * @param integer $ring_user_id The user id of the ring that has a member whose password is being fetched.
     *
     * @return string
     */
    public static function getPassword($user_id, $ring_user_id) {
        $sql = "
            SELECT password
            FROM user_ring_password
            WHERE
                ring_user_id = :ring_user_id
                AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $password = $command->queryScalar();
        return $password;
    }

    /**
     * Deletes user_ring_password rows by their user_id.
     *
     * @param integer $user_id The id of the user whose user_ring_password data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_ring_password
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_ring_password rows by the ring_user_id.
     *
     * @param integer $ring_user_id The id of the user for the ring whose user_ring_password data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingUserId($ring_user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_ring_password
            WHERE ring_user_id = :ring_user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of user_ring_password data for a user_id.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_ring_password
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>