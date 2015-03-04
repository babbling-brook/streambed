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
 * Model for the post_user DB table.
 * The table holds the user_id of posts that belong to an stream with kind set to 'user'.
 *
 * @package PHP_Models
 */
class PostUser extends CActiveRecord
{

    /**
     * The primary key of this link.
     *
     * @var integer
     */
    public $post_user_id;

    /**
     * The id of the post that has a user attatched.
     *
     * @var integer
     */
    public $post_id;

    /**
     * The id of the user that is attached to an post.
     *
     * @var integer
     */
    public $user_id;

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
        return 'post_user';
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
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            //array('post_user_id, post_id, user_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'post_user_id' => 'Post User',
            'stream_extra_id' => 'Post ID',
            'user_id' => 'User',
        );
    }

    /**
     * Checks if the user has this post already made for them.
     *
     * @param string $domain Domain of the user.
     * @param string $username User to check.
     * @param integer $stream_extra_id The extra id of the stream the post belongs to.
     *
     * @return array|boolean post object or false.
     */
    public static function getPostForUser($domain, $username, $stream_extra_id) {
        $site_id = SiteMulti::getSiteID($domain, false);
        if ($site_id === false) {
            return false;
        }

        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($username, false);
        if ($user_id === false) {
            return false;
        }

        $query = "
            SELECT
                 post_user.post_id AS post_id
            FROM
                post_user
                INNER JOIN post ON post_user.post_id = post.post_id
            WHERE
                post.stream_extra_id = :stream_extra_id
                AND post_user.user_id = :user_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();

        if ($post_id !== false) {
            $json_array = array();

            $json_array['post'] = PostMulti::getPost($post_id);

            // Get the take value if this has already been taken.
            $json_array['take_value'] = Take::getTake($post_id, Yii::app()->user->getId(), 2);

            return $json_array;
        }
        return false;

    }

    /**
     * Insert a new post row.
     *
     * @param integer $post_id The post id to insert a user against.
     * @param object $first_field The first field of the post, which should be of a link type with the
     *                            link title equal to the user name of the person the post is against.
     *                            Assumed to have been validated with the post insert.
     *
     * @return void
     */
    public static function insertPost($post_id, $first_field) {
        $name_parts = User::getNamePartsFromFullName($first_field['link_title'], false);
        if ($name_parts === false) {
            throw new Exception(
                "When submitting an post with an stream with a kind='user', then the "
                    . "link title of the first field must be a valid username."
            );
        }

        // @fixme Would be best to check if the site really exists using CURL,
        // to prevent invalid domains getting into the DB
        $site_id = SiteMulti::getSiteID($name_parts[0]);
        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($name_parts[1]);

        $post_user = new PostUser;
        $post_user->post_id = $post_id;
        $post_user->user_id = $user_id;
        if ($post_user->save() === false) {
            throw new Exception(
                "user_post row not saved. post_id = " . $post_id . " , user_id = " . $user_id
                    . "validation error : " . ErrorHelper::model($post_user->getErrors())
            );
        }
    }

    /**
     * Get the user_id for an post_id.
     *
     * @param integer $post_id The id of the post to fetch an associated user_id from.
     *
     * @return integer
     */
    public static function getUserId($post_id) {
        $query = "
            SELECT user_id
            FROM post_user
            WHERE post_id = :post_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $user_id = $command->queryScalar();
        if (isset($user_id) === false) {
            throw new Exception("User ID not found.");
        }
        return $user_id;
    }


    /**
     * Deletes post_user rows by their user_id
     *
     * @param integer $user_id The id of the user post_user rows are being deleted for.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post_user
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes post_user rows by their post_id
     *
     * @param integer $post_id The id of the post in post_user that is being deleted.
     *
     * @return void
     */
    public static function deleteByPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post_user
            WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of post_user data for a post.
     *
     * @param type $post_id The id of the post to select data for.
     *
     * @return array
     */
    public static function getRowsForPost($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM post_user
                WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Select rows of post_user data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM post_user
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>