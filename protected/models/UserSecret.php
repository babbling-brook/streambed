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
 * Model for the user_secret DB table.
 * Stores secrets for users. Used by other stores to verify that a user generated a secret.
 *
 * @package PHP_Models
 */
class UserSecret extends CActiveRecord
{

    /**
     * The primary key for this secret.
     *
     * @var integer
     */
    public $user_secret_id;

    /**
     * The id of the user that the secret is for.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The secret.
     *
     * @var string
     */
    public $secret;

    /**
     * The date that this secret was created.
     *
     * @var string
     */
    public $date_created;

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
        return '{{user_secret}}';
    }

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('user_id, secret', 'required'),
            array('user_id', 'length', 'max' => 10),
        );
    }

    /**
     * Create a new secret
     *
     * Generates a new secret for a user, saves it in the DB and then returns it.
     *
     * @param integer $user_id The id of the user to generate a secret for.
     *
     * @return integer The new secret.
     */
    public static function createSecret($user_id) {

        $secret = CryptoHelper::makeGuid();

        $user_secret_model = new UserSecret;
        $user_secret_model->user_id = $user_id;
        $user_secret_model->secret = $secret;
        if ($user_secret_model->save() === false) {
            throw new Exception('Failed to generate a new user secret : ' . $secret . ' for user_id : ' . $user_id);
        }

        return $secret;
    }

    /**
     * Verify that a secret is valid.
     *
     * @param string $username The username to check against a secret.
     * @param string $secret The secret to verify.
     *
     * @return boolean
     */
    public static function verifySecret($username, $secret) {
        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername($username, false);
        if ($user_id === false) {
            return false;
        }

        $sql = "
            SELECT user_secret_id
            FROM user_secret
            WHERE
                user_id = :user_id
                AND secret = :secret";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":secret", $secret, PDO::PARAM_STR);
        $user_secret_id = $command->queryScalar();
        if (user_secret_id !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes user_secret rows by their user_id
     *
     * @param integer $user_id The id of the user whose user secrets that are being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_secret
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of user_secret data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_secret
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>