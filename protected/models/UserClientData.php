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
 * Model for the UserClientData DB table.
 * Data stored by a client website in the users account.
 *
 * @package PHP_Models
 */
class UserClientData extends CActiveRecord
{

    /**
     * The id of the user data is being stored for.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The id of the client site that data is being stored for.
     *
     * @var integer
     */
    public $site_id;

    /**
     * A key given to the data by the client website
     *
     * @var string
     */
    public $client_key;

    /**
     * The key for this item in the tree.
     *
     * @var string
     */
    public $depth_key;


    /**
     * The type of data that is stored on this row.
     *
     * @var string
     */
    public $data_type;

    /**
     * The string of stored data. Null if this is a container in a tree.
     *
     * @var string
     */
    public $data;

    /**
     * The left hand boundary of this sub group in tree data (Nested Set tree).
     *
     * @var string
     */
    public $lft;

    /**
     * The right hand boundary of this sub group (Nested Set tree).
     *
     * @var string
     */
    public $rgt;

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
        return '{{user_client_data}}';
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
            array('user_id, site_id, client_key, depth_key, rgt, lft, data_type', 'required'),
            array('client_key', 'length', 'max' => 63),
            array('depth_key', 'length', 'max' => 255),
            array('data_type', 'length', 'max' => 63),
            array('user_id, site_id, rgt, lft', 'numerical', 'integerOnly' => true),
        );
    }

    /*
     ************************************************************************************************
     **********************               !IMPORTANT           **************************************
     ************************************************************************************************
     * All modification of data in this table should take place from the UserClientDataInsert class
     * in order to ensure the lft and rgt values are updated correctly.
     *
     */

    /**
     * Fetch some user data for a client website.
     *
     * @param integer $user_id The id of the user that has some data stored for it.
     * @param integer $site_id The id of the site that has stored some user data.
     * @param string $key The key given to the data by the client domain.
     *
     * @return Array An array of columns
     */
    public static function getRow($user_id, $site_id, $client_key, $depth_key) {
        $query = "
            SELECT
                data,
                lft,
                rgt
            FROM user_client_data
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND client_key = :client_key
                AND depth_key = :depth_key";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":client_key", $client_key, PDO::PARAM_STR);
        $command->bindValue(":depth_key", $depth_key, PDO::PARAM_STR);
        $row = $command->queryRow();
        return $row;
    }


    /**
     * Fetch some user data for a client website.
     *
     * @param integer $user_id The id of the user that has some data stored for it.
     * @param integer $site_id The id of the site that has stored some user data.
     * @param string $key The key given to the data by the client domain.
     *
     * @return Array An array of columns
     */
    public static function getRows($user_id, $site_id, $depth_key) {
        $key_parts = explode(".", $depth_key);
        $client_key = $key_parts[0];

        $query = "
            SELECT
                lft,
                rgt
            FROM user_client_data
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND client_key = :client_key
                AND depth_key = :depth_key";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":client_key", $client_key, PDO::PARAM_STR);
        $command->bindValue(":depth_key", $depth_key, PDO::PARAM_STR);
        $row = $command->queryRow();
        if ($row === false) {
            return false;
        }

        $query = "
            SELECT
                 data
                ,depth_key
                ,data_type
            FROM user_client_data
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND client_key = :client_key
                AND lft >=:lft
                AND rgt <= :rgt
            ORDER BY lft";
        $command2 = Yii::app()->db->createCommand($query);
        $command2->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command2->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command2->bindValue(":client_key", $client_key, PDO::PARAM_STR);
        $command2->bindValue(":lft", $row['lft'], PDO::PARAM_STR);
        $command2->bindValue(":rgt", $row['rgt'], PDO::PARAM_STR);
        $rows = $command2->queryAll();
        return $rows;
    }

    /**
     * Deletes user_client_data rows by thier user_id.
     *
     * @param integer $user_id The id of the user whose client data records are being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_client_data
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of user_client_data data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_client_data
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>