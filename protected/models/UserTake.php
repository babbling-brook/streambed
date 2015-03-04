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
 * Model for the user_take DB table.
 * The table records a users takes against other users (when stream.kind=user).
 * This is to enable rapid calulation of popular takes against users.
 *
 * @package PHP_Models
 */
class UserTake extends CActiveRecord
{

    /**
     * The primary key of the table.
     *
     * @var integer
     */
    public $user_take_id;

    /**
     * The user who has been taken.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The extra id of the stream that owns the post that was taken.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The id of the take for this user take.
     *
     * @var integer
     */
    public $take_id;

    /**
     * The id of the post that represents the user in this user take.
     *
     * @var integer
     */
    public $post_id;

    /**
     * The id of the user who has made this take.
     *
     * @var integer
     */
    public $take_user_id;

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
        return 'user_take';
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
            array('user_id, stream_extra_id, take_id', 'required'),
            array('user_id, stream_extra_id, take_id, post_id', 'numerical', 'integerOnly' => true),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            //array('user_take_id, user_id, stream_extra_id, take_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_take_id' => 'User Take',
            'user_id' => 'User',
            'stream_extra_id' => 'Stream Extra',
            'take_id' => 'Take',
        );
    }

    /**
     * Return an array ready for JSON conversion containing takes against a user by another user.
     *
     * @param integer $user_id The user ID of the profile.
     * @param integer $start Paging start value.
     * @param integer $qty Qty of items to include on a page.
     * @param integer $take_user_id The user ID of the person viewing the page.
     *
     * @return array
     */
    public static function getForUserByUser($user_id, $start, $qty, $take_user_id) {
        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post_site.domain AS domain
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,post.child_count
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,stream_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,stream_user.username AS stream_username
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,take.value AS take_value
                ,UNIX_TIMESTAMP(take.date_taken) AS date_taken
            FROM
                post
                INNER JOIN site as post_site ON post.site_id = post_site.site_id
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as stream_user ON stream.user_id = stream_user.user_id
                INNER JOIN site as stream_site ON stream_user.site_id = stream_site.site_id
                INNER JOIN user as post_user ON post.user_id = post_user.user_id
                INNER JOIN site as post_user_site ON post_user.site_id = post_user_site.site_id
                INNER JOIN user_take ON post.post_id = user_take.post_id
                INNER JOIN take ON user_take.take_id = take.take_id
            WHERE
                user_take.take_user_id = :take_user_id
                AND user_take.user_id = :user_id
                AND stream.kind = :kind
            ORDER BY post.date_created
            LIMIT :start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":take_user_id", $take_user_id, PDO::PARAM_INT);
        $command->bindValue(":kind", LookupHelper::getID("stream.kind", "user"), PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        $command->bindValue(":start", intval($start), PDO::PARAM_INT);
        $results = $command->queryAll();
        return $results;
    }

    /**
     * Return an array ready for JSON conversion containing global takes against a user.
     *
     * @param integer $user_id The user ID of the profile.
     * @param integer $start Pageing start value.
     * @param integer $qty Qty of items to include on a page.
     *
     * @return array
     */
    public static function getForUserByGlobal($user_id, $start, $qty) {
        $query = "
            SELECT
                 post.post_id AS post_id
                ,post_site.domain AS domain
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,post.child_count
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,stream_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,stream_user.username AS stream_username
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,take.value AS take_value
                ,UNIX_TIMESTAMP(take.date_taken) AS date_taken
                ,post_site.domain as user_domain
                ,post_user.username as user_username
            FROM
                post
                INNER JOIN site as post_site ON post.site_id = post_site.site_id
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as stream_user ON stream.user_id = stream_user.user_id
                INNER JOIN site as stream_site ON stream_user.site_id = stream_site.site_id
                INNER JOIN user as post_user ON post.user_id = post_user.user_id
                INNER JOIN site as post_user_site ON post_user.site_id = post_user_site.site_id
                INNER JOIN user_take ON post.post_id = user_take.post_id
                INNER JOIN take ON user_take.take_id = take.take_id
            WHERE
                user_take.user_id = :user_id
            ORDER BY
                stream_site.domain,
                stream_user.username,
                stream.name,
                version.major,
                version.minor,
                version.patch,
                post.date_created
            LIMIT " . $start . ", " . $qty;

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $results = $command->queryAll();

        return $results;
    }

    /**
     * Fetch detials for some UserTake rows.
     *
     * @param string $rows A list of primary keys to get details for. Seperated with commas.
     * @param integer $take_user_id The local id of the user whose take values we want to return with these results.
     *
     * @return array
     */
    public static function getDetail($rows, $take_user_id) {
        if ($rows === "") {
            return array();
        }

        $row_string = implode(',', array_fill(0, count($rows), '?'));
        $row_string = "(" . $row_string . ")";

        $query = "
            SELECT
                 user_take.user_take_id
                ,type_site.domain AS type_domain
                ,type_user.username AS type_username
                ,stream.name AS type_name
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS type_version
                ,user_take.take_id
                ,user_take.post_id AS post_id
                ,post_content.text AS post_title
                ,post_content.link_title
                ,post_content.link
                ,take_site.domain AS take_domain
                ,take_user.username AS take_username
                ,take.value as take_value
                ,(SELECT count(post_id) FROM post AS sub_post WHERE sub_post.top_parent = post.post_id)AS comments
                ,UNIX_TIMESTAMP(take.date_taken) AS date_taken
            FROM user_take
                INNER JOIN stream_extra
                    ON user_take.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user AS type_user ON stream.user_id = type_user.user_id
                INNER JOIN user AS take_user ON user_take.take_user_id = take_user.user_id
                INNER JOIN site AS type_site ON type_user.site_id = type_site.site_id
                INNER JOIN site AS take_site ON take_user.site_id = take_site.site_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN post
                    ON user_take.post_id = post.post_id
                        AND post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN post_content
                    ON post_content.post_id = post.post_id
                        AND post_content.revision =
                            (
                                SELECT revision
                                FROM post_content
                                WHERE post_id = post.post_id
                                ORDER BY revision DESC LIMIT 1
                            )
                        AND display_order = 1
                LEFT JOIN take
                    ON user_take.take_id = take.take_id
                        AND user_take.take_user_id = ?
            WHERE
                user_take.user_take_id IN " . $row_string;

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(1, $take_user_id, PDO::PARAM_INT);
        foreach ($rows as $key => $user_take_id) {
            $command->bindValue(($key + 2), $user_take_id);
        }
        $results = $command->queryAll();

        return $results;
    }

    /**
     * Get details of a take by one user for another.
     *
     * @param integer $stream_extra_id The id of the stream that was used to take a user.
     * @param integer $taken_user_id The id of the user who was taken.
     * @param integer $take_user_id  The id of the user who did the taking.
     *
     * @return array
     */
    public static function getDetailsFromStream($stream_extra_id, $taken_user_id, $take_user_id) {
        $query = "
            SELECT
                 user_take.user_take_id
                ,user_take.take_id
                ,user_take.post_id AS post_id
                ,take.value as take_value
                ,UNIX_TIMESTAMP(take.date_taken) AS date_taken
            FROM
                user_take
                INNER JOIN take ON user_take.take_id = take.take_id
            WHERE
                user_take.user_id = :taken_user_id
                AND user_take.stream_extra_id = :stream_extra_id
                AND user_take.take_user_id = :take_user_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":take_user_id", $take_user_id, PDO::PARAM_INT);
        $command->bindValue(":taken_user_id", $taken_user_id, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $results = $command->queryRow();
        return $results;
    }

    /**
     * Delete a User Take.
     *
     * @param integer $user_take_id The primary key of the row to delete.
     * @param integer $stream_extra_id The extra id of the stream that owns the post that was taken.
     * @param integer $take_user_id The id of the user that has taken the other user.
     *
     * @return void
     */
    public static function deleteTake($user_take_id, $stream_extra_id, $take_user_id) {
        $query = "DELETE FROM user_take
                  WHERE user_take_id = :user_take_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_take_id", $user_take_id, PDO::PARAM_INT);
        $command->execute();

        UserStreamCount::decrement($take_user_id, $stream_extra_id);
    }

    /**
     * Insert a user take.
     *
     * Primarily used when a takes value reches zero
     * - as we don't want to register the stream as being used in this case.
     *
     * @param integer $user_id The id of the user who is being taken.
     * @param integer $stream_extra_id The extra id of the stream that owns the post being taken.
     * @param integer $take_id The id of the take that matches this user take.
     * @param integer $post_id The id of the post that represents the user that is being taken.
     * @param integer $take_user_id The id of the user that is taking the other user.
     *
     * @return void
     */
    public static function insertTake($user_id, $stream_extra_id, $take_id, $post_id, $take_user_id) {
        $query = "
            INSERT INTO user_take
            (
                user_id
                ,stream_extra_id
                ,take_id
                ,post_id
                ,take_user_id
            ) VALUES (
                :user_id
                ,:stream_extra_id
                ,:take_id
                ,:post_id
                ,:take_user_id
            )";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":take_id", $take_id, PDO::PARAM_INT);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":take_user_id", $take_user_id, PDO::PARAM_INT);
        $command->execute();

        UserStreamCount::increment($take_user_id, $stream_extra_id);
    }

    /**
     * Fetch a primary key of a take from its contents.
     *
     * @param integer $user_id The id of the user who has been taken.
     * @param integer $stream_extra_id The extra id of the stream that owns the post that has been taken.
     * @param integer $take_id The id of the take that matches this user take.
     * @param integer $post_id The id of the post that represents the user that has been taken.
     * @param integer $take_user_id The id of the user that has taken the other user.
     *
     * @return integer|boolean Primary key or false.
     * @fixme this is overly complicated. take_id should be unique in this table and could act as a primary key.
     */
    public static function getId($user_id, $stream_extra_id, $take_id, $post_id, $take_user_id) {
        $query = "SELECT user_take_id
                  FROM user_take
                  WHERE
                      user_id = :user_id
                      AND stream_extra_id = :stream_extra_id
                      AND take_id = :take_id
                      AND post_id = :post_id
                      AND take_user_id = :take_user_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":take_id", $take_id, PDO::PARAM_INT);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":take_user_id", $take_user_id, PDO::PARAM_INT);
        $user_take_id = $command->queryScalar();
        if (isset($user_take_id) === false) {
            return false;
        } else {
            return $user_take_id;
        }
    }

    /**
     * Deletes user_take rows by their stream_extra_id.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is used to delete these row.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM user_take
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_take rows by their user_id.
     *
     * @param integer $user_id The id of the user whose user_take data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_take
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_take rows by their post_id.
     *
     * @param integer $post_id The id of the post in user_take that is being deleted.
     *
     * @return void
     */
    public static function deleteByPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_take
            WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_take rows by their take_id.
     *
     * @param integer $take_id The id of the take in user_take that is being deleted.
     *
     * @return void
     */
    public static function deleteByTakeId($take_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_take
            WHERE take_id = :take_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":take_id", $take_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_take rows by their take_id.
     *
     * @param integer $take_id The id of the take in user_take that is being deleted.
     * @param integer $take_user_id The id of the user who owns this take.
     *
     * @return void
     */
    public static function deleteByTakeIdAndTakeUserId($take_id, $take_user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_take
            WHERE
                take_id = :take_id
                AND take_user_id = :take_user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":take_id", $take_id, PDO::PARAM_INT);
        $command->bindValue(":take_user_id", $take_user_id, PDO::PARAM_INT);
        $command->execute();
    }

}

?>