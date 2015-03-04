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
 * Model for the user_profile DB table.
 * The table holds profile information about a user.
 *
 * @package PHP_Models
 */
class UserProfile extends CActiveRecord
{

    /**
     * The primary key of the profile table.
     *
     * @var integer
     */
    public $user_profile_id;

    /**
     * The id of the user who owns this profile.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The real name of this user.
     *
     * @var string
     */
    public $real_name;

    /**
     * A textual description of this user, by this user.
     *
     * @var string
     */
    public $rbout;

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
        return 'user_profile';
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
            array('real_name', 'length', 'max' => 255),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_profile_id' => 'User Profile',
            'user_id' => 'User ID',
            'real_name' => 'Name',
            'about' => 'About',
        );
    }

    /**
     * Return a users profile.
     *
     * @param integer $user_id Th is of the user whose profile is being fetched.
     *
     * @return UserProfile
     */
    public static function get($user_id) {
        return UserProfile::model()->find(
            "user_id = :user_id",
            array(
                ":user_id" => $user_id,
            )
        );
    }

    /**
     * Get the user id of this profile.
     *
     * @param integer $user_profile_id The id of the profile we are fetching a user_id for.
     *
     * @return integer The user_id.
     */
    public static function getUserId($user_profile_id) {
        $model = UserProfile::model()->find(
            array(
                "select" => "user_id",
                "condition" => "user_profile_id = :user_profile_id",
                array(
                    ":user_profile_id" => $user_profile_id,
                )
            )
        );

        if (is_null($model) === true) {
            throw new Exception("User profile ID does not exist.");
        }

        return $model->user_id;
    }

    /**
     * Verifies that a link is in the correct format for a profile.
     *
     * @param string $link The absolute link to check.
     *
     * @return string|boolean Error message or false.
     */
    public static function verifyLink($link) {
        $error = "Please enter the full profile URL of the user you wish to rate.";

        $url = parse_url($link);
        if ($url === false) {
            return $error;
        }

        if (isset($url['path']) === false) {
            return $error;
        }

        $path = $url['path'];
        $path = rtrim($path, "/");
        $path = ltrim($path, "/");
        $path = explode("/", $path);

        // Local profile path.
        if (count($path) === 2) {
            if ($path[1] !== "profile") {
                return $error;
            }

        } else if (count($path) === 4) {
            // Remote profile path
            if ($path[0] !== "elsewhere") {
                return $error;
            }
            if ($path[3] !== "profile") {
                return $error;
            }

        } else {
            // An error
            return $error;
        }
        return false;
    }


    /**
     * Verifies that a link title is in the correct format for a profile.
     *
     * @param string $title The title to check.
     *
     * @return string|boolean Error message or false.
     */
    public static function verifyLinkTitle($title) {

        $matches = preg_match("/^([a-z0-9\-\.]+)\/([a-z0-9]+)$/", $title);
        if ($matches === 0) {
            return "Please enter a valid username for a profile link title.";
        }

        return false;
    }

    /**
     * Converts a remote profile link into the path to it's home profile page.
     *
     * If this is not a remote profile link then it returns the passed in link.
     * Assumes that the path is a valid profile link. Call verifyLink to ensure this.
     *
     * @param string $link The absolute link to check.
     *
     * @return string|boolean Error message or false.
     */
    public static function convertRemoteProfileLink($link) {
        $url = parse_url($link);
        if ($url === false) {
            return $link;
        }

        if (isset($url['path']) === false) {
            return $link;
        }

        $path = $url['path'];
        $path = rtrim($path, "/");
        $path = ltrim($path, "/");
        $path = explode("/", $path);

        // Return the origional profile path.
        if (count($path) === 4) {
            return "http://" . $path[1] . "/" . $path[2] . "/profile";
        } else {
            return $link;
        }

    }

    /**
     * Create a user profile.
     *
     * @param integer $user_id The id of the user a profile is being created for.
     *
     * @return void
     */
    public static function createProfile($user_id) {
        $sql = "INSERT INTO user_profile (user_id) VALUES (:user_id)";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes a user_profile row by its user_id.
     *
     * @param integer $user_id The id of the user whose profile is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_profile
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select a row of profile data.
     *
     * @param type $user_id The id of the user to select a row of data for.
     *
     * @return array. Indexed by column name.
     */
    public static function getRowForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_profile
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        return $row;
    }
}

?>