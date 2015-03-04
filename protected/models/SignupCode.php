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
 * Model for the SignupCode DB table.
 * Signup validation codes that allow a user to signup (If option is turned on in the config.)
 *
 * @package PHP_Models
 */
class SignupCode extends CActiveRecord
{

    /**
     * The primary key of the table.
     *
     * @var integer
     */
    public $signup_code_id;

    /**
     * The uuid that is required for a user to sign up.
     *
     * @var string
     */
    public $code;


    /**
     * A category to identify the source of this code.
     *
     * @var string
     */
    public $primary_category;


    /**
     * A second category to identify the source of this code.
     *
     * @var string
     */
    public $secondary_category;

    /**
     * The user_id of the user who signed up with this code.
     *
     * @var string
     */
    public $used_user_id;


    /**
     * If the user is logging in for the first time this is their domain name.
     *
     * These three temp columns are used to reserve a code for a user whilst they are redirected to their
     * home data store. It prevents spaming of the same code, preventing more than one username to use it.
     *
     * @var string
     */
    public $hold_for_domain;

    /**
     * If the user is logging in for the first time this is their username.
     *
     * @var string
     */
    public $hold_for_username;

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
        return '{{signup_code}}';
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
            array('code', 'required'),
            array('code', 'length', 'max' => 40),
            array('primary_category, secondary_category, hold_for_domain, hold_for_username', 'length', 'max' => 255),
            array('used_user_id', 'length', 'max' => 10),
            array('used_user_id, signup_code_id', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * Checks if a signup code is active or not.
     *
     * @param String $code The code to check.
     *
     * @return Boolean
     */
    public static function isValid($code) {
        $sql = "SELECT signup_code_id
                FROM signup_code
                WHERE
                    code = :code
                    AND used_user_id IS NULL";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $command->bindValue(":code", $code, PDO::PARAM_STR);
        $signup_code_id = $command->queryScalar();
        if ($signup_code_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Mark a signup code as having been used so that it can't be used a second time.
     *
     * @param String $code The code to mark as used.
     * @param Integer $user_id The id of the user who used this code.
     *
     * @return void
     */
    public static function markUsed($code, $user_id) {
        $sql = "UPDATE signup_code
                SET
                     used_user_id = :user_id
                    ,hold_for_domain = NULL
                    ,hold_for_username = NULL
                WHERE code = :code ";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":code", $code, PDO::PARAM_STR);
        $command->execute();
    }

    /**
     * Place a hold on a code whilst a user is redirected to their remote site to login.
     *
     * This prevents it from being used by other users.
     *
     * @param type $domain The domain of the user to use for the hold.
     * @param type $username The username of the user to use for the hold.
     * @param type $code The code to place a hold on.
     */
    public static function hold($domain, $username, $code) {
        $sql = "UPDATE signup_code
                SET
                    hold_for_domain = :domain
                    ,hold_for_username = :username
                WHERE
                    code = :code
                    AND used_user_id IS NULL
                ";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $command->bindValue(":domain", $domain, PDO::PARAM_STR);
        $command->bindValue(":username", $username, PDO::PARAM_STR);
        $command->bindValue(":code", $code, PDO::PARAM_STR);
        $command->execute();
    }

    /**
     * Checks if a user has already signed up with a valid code.
     *
     * @param type $domain
     * @param type $username
     *
     * @return boolean
     */
    public static function hasUserAlreadySignedUp($user_id) {
        if ($user_id === false) {
            return false;
        }

        $sql = "SELECT signup_code_id
                FROM signup_code
                WHERE used_user_id = :user_id";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $signup_code_id = $command->queryScalar();
        if ($signup_code_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Fetch an on hold code by its hold_for username and domain.
     *
     * @param string $username The username used to hold the code.
     * @param string $domain The domain used to hold the code.
     *
     * @return string|false The code that is on hold or false.
     */
    public static function getOnHold($username, $domain) {
        $sql = "SELECT code
                FROM signup_code
                WHERE
                    hold_for_domain = :domain
                    AND  hold_for_username = :username";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $command->bindValue(":domain", $domain, PDO::PARAM_STR);
        $command->bindValue(":username", $username, PDO::PARAM_STR);
        $code = $command->queryScalar();
        return $code;
    }

    /**
     * Deletes signup_code rows by their user_id.
     *
     * @param integer $user_id The id of the user whose signup codes are being deleted.
     *
     * @return void
     */
    public static function deleteByUsedUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM signup_code
            WHERE used_user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select a users row of signup data.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM signup_code
                WHERE used_user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        return $row;
    }
}

?>
