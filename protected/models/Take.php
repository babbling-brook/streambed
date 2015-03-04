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
 * Model for the take DB table.
 * This records takes by users against posts.
 *
 * There where two msql triggers that updated/ deleted rows in this table.
 * They have been removed but commented here incase they need reinstating.
 * user_take_delete_row :
 * -- Delete rows from the user_stream_count table to reflect when a user cancels a take in the user_take table
 * BEGIN
 *
 *     UPDATE user_stream_count SET total = total - 1
 *     WHERE user_id = OLD.take_user_id AND stream_extra_id = OLD.stream_extra_id;
 *
 * END
 *
 * user_take_insert_total :
 * --
 * -- Update the user_stream_count table to reflect the number of times a user has used this stream.
 * BEGIN
 *
 * IF (SELECT COUNT(*) FROM user_stream_count
 *     WHERE user_id = NEW.take_user_id AND stream_extra_id = NEW.stream_extra_id) THEN
 *
 *     UPDATE user_stream_count SET total = total + 1
 *          WHERE user_id = NEW.take_user_id AND stream_extra_id = NEW.stream_extra_id;
 *
 * ELSE
 *
 *     INSERT INTO user_stream_count (user_id, stream_extra_id, total)
 *          VALUES (NEW.take_user_id, NEW.stream_extra_id, 1);
 *
 * END IF;
 *
 * END
 *
 * @package PHP_Models
 */
class Take extends CActiveRecord
{

    /**
     * The primary key of this take.
     *
     * @var integer
     */
    public $take_id;

    /**
     * The date of the take.
     *
     * @var string
     */
    public $date_taken;

    /**
     * The id of the post that is being taken.
     *
     * @var integer
     */
    public $post_id;

    /**
     * The id of the user that has made this take.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The value of the take.
     *
     * @var integer
     */
    public $value;

    /**
     * The field in the stream which holds the post that this take is connected to.
     *
     * @var integer
     */
    public $field_id;

    /**
     * The extra id of the stream  which holds the post that this take is connected to.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The block id that this take is cached in.
     *
     * @var integer
     */
    public $block_id;

    /**
     * The stream block id that this take is cached in.
     *
     * @var integer
     */
    public $stream_block_id;

    /**
     * The tree block id that this take is cached in.
     *
     * @var integer
     */
    public $tree_block_id;

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
        return 'take';
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
            array('post_id, user_id, value, field_id, stream_extra_id', 'required'),
            array('post_id, take_id, user_id, value, field_id, stream_extra_id,
                   block_id, stream_block_id, tree_block_id',
                'length',
                'max' => 11,
            ),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function attributeLabels() {
        return array(
            'take_id' => 'Take',
            'date_taken' => 'Date Taken',
            'post_id' => 'Post',
            'user_id' => 'User',
            'value' => 'Value',
            'field_id' => 'Field ID',
        );
    }

    /**
     * Fetch a popularity count of the kinds of streams a user takes posts from.
     *
     * If an post already exists for the target user then its details are returned.
     *
     * @param integer $user_id The id of the user to fetch results for.
     * @param integer $page The page fetch results for.
     * @param integer $qty The quantity to fetch.
     * @param integer $target_user_id The id of the target user.
     *
     * @return array ready for JSON encoding.
     */
    public static function getPopularStreamTakes($user_id, $page, $qty) {
        $start = ($page - 1) * $qty;
        $query = "
            SELECT
                 stream.name AS stream_name
                ,user.username AS stream_username
                ,site.domain AS stream_domain
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,user_stream_count.total
            FROM user_stream_count
                INNER JOIN stream_extra
                    ON user_stream_count.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user ON stream.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
            WHERE
                user_stream_count.user_id = :user_id
            ORDER BY user_stream_count.total DESC
            LIMIT " . $start . ", " . $qty;
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $results = $command->queryAll();
        return $results;
    }

    /**
     * Fetch all the takes by a user for an post in a particular block.
     *
     * @param integer $block_number The block number to fetch takes for.
     * @param integer $stream_extra_id The id of the extra stream to fetch takes for.
     * @param integer $user_id The id of the user to fetch takes for.
     * @param integer [$field_id=2] The id of the field that takes are being fetched for.
     *      2 is the main field.
     *
     * @return array
     */
    public static function getUserStreamTakes($block_number, $stream_extra_id, $user_id, $field_id=2) {

        $query = "
            SELECT
                 post_id
                ,value
                ,UNIX_TIMESTAMP(date_taken) AS date_taken
            FROM
                take
            WHERE
                stream_block_id = :block_number
                AND stream_extra_id = :stream_extra_id
                AND user_id = :user_id
                AND field_id = :field_id
            ORDER BY date_taken DESC";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":block_number", $block_number, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":field_id", $field_id, PDO::PARAM_INT);
        $takes = $command->queryAll();
        return $takes;
    }

    /**
     * Fetch all the takes by a user for an post in a particular block.
     *
     * @param integer $stream_extra_id The extra id of the stream that we are fetching a block number for.
     * @param integer $user_id The id of the user we are fetching a block number for.
     *
     * @return array
     */
    public static function getLastFullStreamBlockNumber($stream_extra_id, $user_id) {
        $query = "
            SELECT
                 stream_block_id
            FROM
                take
            WHERE
                stream_extra_id = :stream_extra_id
                AND user_id = :user_id
            ORDER BY stream_block_id DESC
            LIMIT 1";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $block_number = $command->queryScalar();
        if (ctype_digit($block_number) === false) {
            $block_number = 0;
        }
        return $block_number;
    }


    /**
     * Fetch all the take values for a tree block by user_id.
     *
     * @param integer $block_number The block number to fetch takes for.
     * @param integer $user_id The id of the usre to fetch takes for.
     * @param integer $post_id The id of the post to fetch takes for.
     * @param integer [$field_id=2] The id of the field that takes are being fetched for.
     *      2 is the main field.
     *
     * @return array
     */
    public static function getUserTreeTakes($block_number, $user_id, $post_id, $field_id=2) {
        $query = "
            SELECT
                 post_id
                ,value
            FROM
                take
            WHERE
                tree_block_id = :block_number
                AND post_id = :post__id
                AND user_id = :user_id
                AND field_id = :field_id
            ORDER BY date_taken DESC";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":block_number", $block_number, PDO::PARAM_INT);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":field_id", $field_id, PDO::PARAM_INT);
        $takes = $command->queryAll();
        return $takes;
    }

    /**
     * Get the stream block number that covers this this stream, user and time.
     *
     * @param integer $stream_extra_id The extra id of the stream we are getting a block number for.
     * @param integer $user_id The id of the user who made the takes that are being fetched.
     * @param integer $time Unix time stamp that is used to fetch the nearest block number.
     *
     * @return integer
     */
    public static function getStreamBlockNumber($stream_extra_id, $user_id, $time) {
        $query = "SELECT stream_block_id
                  FROM take
                  WHERE
                      date_taken <= FROM_UNIXTIME(:time)
                      AND stream_extra_id = :stream_extra_id
                      AND user_id = :user_id
                  ORDER BY date_taken DESC
                  LIMIT 1";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":time", $time, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $block_number = $command->queryScalar();
        return $block_number;
    }

    /**
     * Get the nearest block number after the given time.
     *
     * @param integer $stream_extra_id The extra id of the stream we are getting a block number for.
     * @param integer $user_id The id of the user who made the takes that are being fetched.
     * @param integer $time Unix time stamp that is used to fetch the nearest block number.
     *
     * @return integer
     */
    public static function getNearestStreamBlockNumber($stream_extra_id, $user_id, $time) {
        $query = "SELECT stream_block_id
                  FROM take
                  WHERE
                      date_taken > :time
                      AND stream_extra_id = :stream_extra_id
                      AND user_id = :user_id
                  ORDER BY date_taken
                  LIMIT 1";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":time", $time, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $block_number = $command->queryScalar();
        return $block_number;
    }

    /**
     * Does an post have any takes.
     *
     * @param integer $post_id The id of the post to check for takes.
     *
     * @return boolean
     */
    public static function doesPostHaveTakes($post_id) {
        $query = "SELECT take_id
                  FROM take
                  WHERE post_id = :post_id
                  LIMIT 1";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $take_id = $command->queryScalar();
        if ($take_id !== false) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Gets the latest takes for a user, where latest is defined by the post_id and not the take time.
     *
     * @param integer $qty The quantity of takes to fetch.
     * @param integer $user_id The id of the user to fetch the takes for.
     *
     * @return array An array of take data.
     */
    public static function getLatestTakesByPost($qty, $user_id) {
        $query = "
            SELECT
                 take.date_taken AS take_time
                ,post.date_created AS post_time
                ,post.site_post_id
                ,site.domain
                ,take.value
                ,take.field_id
            FROM
                take
                INNER JOIN post ON take.post_id = post.post_id
                INNER JOIN site ON post.site_id = site.site_id
            WHERE take.user_id = :user_id
            ORDER BY post.date_created DESC, take.field_id
            LIMIT :qty";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $takes = $command->queryAll();
        return $takes;
    }

    /**
     * Fetch takes for all fields on an post for a specific user.
     *
     * @param type $domain The domain of the post that takes are being fetched for.
     * @param type $site_post_id The post_id - local to the domain - of the post takes are being fetched for.
     * @param type $user_id The id of the user that takes are being fetched for.
     *
     * @return array An array of take data.
     */
    public static function getTakesForPost($domain, $site_post_id, $user_id) {
        $query = "
            SELECT
                 take.date_taken AS take_time
                ,post.date_created AS post_time
                ,post.site_post_id
                ,site.domain
                ,take.value
                ,take.field_id
            FROM
                take
                INNER JOIN post ON take.post_id = post.post_id
                INNER JOIN site ON post.site_id = site.site_id
            WHERE
                take.user_id = :user_id
                AND site.domain = :domain
                AND post.site_post_id = :site_post_id
            ORDER BY take.field_id DESC";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":domain", $domain, PDO::PARAM_STR);
        $command->bindValue(":site_post_id", $site_post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $takes = $command->queryAll();
        return $takes;
    }

    /**
     * Has a user taken an post.
     *
     * @param type $user_id The id of the user that might have taken this post.
     * @param type $post_id The id of the post that might have been taken.
     *
     * @return boolean
     */
    public static function hasUserTaken($user_id, $post_id) {
        $query = "
            SELECT take_id
            FROM take
            WHERE
                take.user_id = :user_id
                AND post_id = :post_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $take_id = $command->queryScalar();
        if ($take_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get all the take ids for a post.
     *
     * @param integer $post_id The id of the post that takes are being fetched for.
     *
     * @return array
     */
    public static function getTakeIdsForPost($post_id) {
        $query = "
            SELECT take_id
            FROM take
            WHERE post_id = :post_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $take_ids = $command->queryColumn();
        return $take_ids;
    }

    /**
     * Get all the take ids for a user_id.
     *
     * @param integer $user_id The id of the user that takes are being fetched for.
     *
     * @return array
     */
    public static function getTakeIdsForUser($user_id) {
        $query = "
            SELECT take_id
            FROM take
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $take_ids = $command->queryColumn();
        return $take_ids;
    }

    /**
     * Get all the take ids for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that takes are being fetched for.
     *
     * @return array
     */
    public static function getTakeIdsForStreamExtraId($stream_extra_id) {
        $query = "
            SELECT take_id
            FROM take
            WHERE stream_extra_id = :stream_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $take_ids = $command->queryColumn();
        return $take_ids;
    }

    /**
     * Delete all take rows by their take_id
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $take_id The id of the take used to delete this take.
     *
     * @return void
     */
    public static function deleteByTakeId($take_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM take
                WHERE take_id = :take_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":take_id", $take_id, PDO::PARAM_INT);
            $command->execute();

        } catch (Exception $e) {
            throw new Exception(
                'Take::deleteByTakeId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Deletes take rows by their stream_field_id.
     *
     * @param integer $stream_extra_id The stream_extra_id that is being used to delete take rows.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE FROM take
            WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Update the main value for a main take.
     *
     * @param integer $post_id The id of the post that has a take that is being updated.
     * @param integer $value The new value for the take.
     * @param integer $user_id The id of the user who made the take - andis making the update.
     * @param integer $field_id The id of the post value field that is being retaken.
     *
     * @return integer value
     */
    public static function updateTake($post_id, $value, $user_id, $field_id) {
        Take::model()->updateAll(
            array(
                'value' => $value,
            ),
            array(
                'condition' => 'post_id=:post_id AND user_id=:user_id AND field_id=:field_id',
                'params' => array(
                    ':post_id' => $post_id,
                    ':user_id' => $user_id,
                    ':field_id' => $field_id,
                )
            )
        );
        return $value;
    }

    /**
     * Insert the main value for a main take.
     *
     * @param integer $post_id The id of the post that is being taken.
     * @param integer $value The value that the post is being taken for.
     * @param integer $user_id The id of the user that is takeing the post.
     * @param integer $field_id The id of the posts value field that is being taken.
     * @param integer $stream_extra_id The extra id of the stream that holds the post that is being taken.
     *
     * @return integer|boolean value or false.
     */
    public static function insertTake($post_id, $value, $user_id, $field_id, $stream_extra_id) {
        $model = new Take;
        $model->post_id = $post_id;
        $model->value = $value;
        $model->user_id = $user_id;
        $model->field_id = $field_id;
        $model->stream_extra_id = $stream_extra_id;
        if ($model->save() === true) {
            return $value;
        }
        Throw new Exception("Take not saved : " . ErrorHelper::model($model->getErrors()));
    }

    /**
     * Return the value of a users take. Returns false if not found.
     *
     * @param integer $post_id The if of the post we are fetching take data for.
     * @param integer $user_id The id of the user who owns the take we are fetching data for.
     * @param integer $field_id The id of the post value field we are fetching data for.
     *
     * @return integer|boolean
     */
    public static function getTake($post_id, $user_id, $field_id) {
        $model = Take::model()->find(
            array(
                "select" => "value",
                "condition" => "post_id = :post_id AND user_id = :user_id AND field_id = :field_id",
                "params" => array(
                    ":post_id" => $post_id,
                    ":user_id" => $user_id,
                    ':field_id' => $field_id,
                )
            )
        );
        if (isset($model) === true) {
            return $model->value;
        }
        return false;
    }

    /**
     * Fetch the ID for a take from its unique combination of post_id user_id and field_id.
     *
     * @param integer $post_id The id of the post we are fetching a take_id for.
     * @param integer $user_id The id of the user who owns the take data we are fetching.
     * @param integer $field_id The id of the post value field we are fetching take data for.
     *
     * @return integer
     */
    public static function getTakeID($post_id, $user_id, $field_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 take_id
            FROM take
            WHERE
                post_id = :post_id
                AND user_id = :user_id
                AND field_id = :field_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":field_id", $field_id, PDO::PARAM_INT);
        $take_id = $command->queryScalar();
        if (isset($take_id) === false) {
            throw new Exception("Take ID not found");
        }
        return $take_id;
    }


    /**
     * Delete a take.
     *
     * @param integer $post_id The id of the post that we are deleting a take from.
     * @param integer $user_id The id of the user who owns the take we are deleting.
     * @param integer $field_id The id of the post value field that we are deleting a take from.
     * @param boolean [$delete_remote=true] Should remote takes be deleted.
     *
     * @return void
     */
    public static function deleteTake($post_id, $user_id, $field_id, $delete_remote=false) {

        if ($delete_remote === true) {
            if (Post::isLocal($post_id) === false) {
                return;
            }
        }

        // Ensure any user take record is also deleted0
        $kind_id = Stream::getKindFromPostID($post_id);
        if (LookupHelper::getValue($kind_id) === "user") {
            $post_row =  PostMulti::getPostRow($post_id);
            $post_user_id = PostUser::getUserId($post_id);    // The user_id of the person the post is for.
            $take_id = Take::getTakeID($post_id, $user_id, $field_id);
        }

        Take::model()->deleteAll(
            array(
                "condition" => "post_id = :post_id AND user_id = :user_id AND field_id = :field_id",
                "params" => array(
                    ":post_id" => $post_id,
                    ":user_id" => $user_id,
                    ':field_id' => $field_id,
                )
            )
        );
    }

    /**
     * Get the take value for a single user take of an post.
     *
     * @param integer $user_id The id of the user that might have taken this post.
     * @param integer $user_site_id The id of the domain that the user belongs to.
     *      Used to check the remote site if no data found and the user is remote.
     * @param integer $post_id The id of the post that might have been taken.
     *
     * @return integer Zero if not taken
     */
    public static function getTakeByUser($user_id, $user_site_id, $post_id) {
        $value = Take::getTakeByUserIdAndPostId($user_id, $post_id);
        return $value;
    }

    /**
     * Get the take value for a single user take of a post.
     *
     * To ensure checking for remote domains use TakeBahavior::getUserPostTake, which calls this.
     *
     * @param integer $user_id The id of the user that might have taken this post.
     * @param integer $post_id The id of the post that might have been taken.
     *
     * @return integer Zero if not taken
     */
    public static function getTakeByUserIdAndPostId($user_id, $post_id) {
        $query = "
            SELECT value
            FROM take
            WHERE
                take.user_id = :user_id
                AND post_id = :post_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $value = $command->queryScalar();
        if ($value === false) {
            return 0;
        } else {
            return intval($value);
        }
    }

    /**
     * Get the post_id of a take from its id.
     *
     * @param integer $take_id The id of the take that is used to fetch a post_id.
     *
     * @return integer
     */
    public static function getPostIdFromTakeId($take_id) {
        $query = "
            SELECT post_id
            FROM take
            WHERE take_id = :take_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":take_id", $take_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        return $post_id;
    }

    /**
     * Select rows of take data for a user_id.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM take
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Select rows of take data for a post_id.
     *
     * @param type $post_id The id of the post to select data for.
     *
     * @return array
     */
    public static function getRowsForPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM take
                WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }


}

?>