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
 * Model for the login DB table.
 * The table holds login secrets and return urls for users who are in the process of logging in.
 * The constaints on the table columns are purposfully lax. Errors should be caught as part of the login
 * process and not in this temporary table.
 *
 * @package PHP_Models
 */
class Login extends CActiveRecord
{

    /**
     * The primary key of the category table.
     *
     * @var integer
     */
    public $login_id;

    /**
     * The username of the user who is loggin in.
     *
     * @var string
     */
    public $username;

    /**
     * The domain of the user who is logging in.
     *
     * @var string
     */
    public $domain;

    /**
     * The temporary login secret that is passed to the domus domain and then returned to prevent spoofing.
     *
     * @var string
     */
    public $secret;

    /**
     * The url to return the user to after they have logged in.
     *
     * @var string
     */
    public $return_location;

    /**
     * Timestamp for when this row was created.
     *
     * @var string
     */
    public $create_date;

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
        return '{{login}}';
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
            array('username, domain, secret, return_location', 'required'),
            array('username', 'length', 'max' => 128),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array();
    }

    /**
     * Stores a row of login data.
     *
     * Every 100 rows this also performs a truncate on the table to delete stale data.
     *
     * @param string $username The username of the user who is loggin in.
     * @param string $domain The domain of the user who is loggin in.
     * @param string $secret The login secret of the user who is loggin in.
     * @param string $return_location The return location for the user who is loggin in.
     *
     * @return integer The primary key of the table
     */
    public static function setRow($username, $domain, $secret, $return_location) {

        // Ensure any stale rows for this user are removed before inserting a new one.
        self::deleteRowByUsername($username, $domain);

        $login_model = new Login;
        $login_model->username = $username;
        $login_model->domain = $domain;
        $login_model->secret = $secret;
        $login_model->return_location = $return_location;
        if ($login_model->validate() === false) {
            throw new Exception(
                'Failed to store login data in login table.' . ErrorHelper::model($login_model->getErrors())
            );
        }
        $login_id = $login_model->save();

        // If the id is divisible by 100 then truncate any stale rows.
        if ($login_id % 100 === 0) {
            self::truncateStaleRows();
        }

        return $login_id;
    }

    /**
     * Fetches a row of data from its secret.
     *
     * @param string $secret The secret that was passed to the data store and back.
     *
     * @return object Associative array of row data.
     */
    public static function getRowBySecret($secret) {
        $sql = "SELECT
                     username
                    ,domain
                    ,return_location FROM login WHERE secret = :secret";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":secret", $secret, PDO::PARAM_STR);
        $row = $command->queryRow();
        return $row;
    }


    /**
     * Removes stale rows from the login table to prevent it growing out of control.
     *
     * @return void
     */
    public static function truncateStaleRows() {
        $sql = "DELETE FROM login WHERE create_date < NOW() - :login_timeout";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":login_timeout", Yii::app()->params['login_timeout'], PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Delete the login record for a user. Called once login process has been completed.
     *
     * @param string $username The username of the user who has logged in.
     * @param string $domain The domain of the user who has logged in.
     *
     * @return void
     */
    public static function deleteRowByUsername($username, $domain) {
        $sql = "DELETE FROM login WHERE username = :username AND domain = :domain";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":username", $username, PDO::PARAM_STR);
        $command->bindValue(":domain", $domain, PDO::PARAM_STR);
        $command->execute();
    }

}

?>