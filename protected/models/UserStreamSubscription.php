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
 * Model for the user_stream_subscription DB table.
 * The table holds user subscriptions to streams.
 *
 * @package PHP_Models
 */
class UserStreamSubscription extends CActiveRecord
{

    /**
     * The primary key of the users post stream.
     *
     * @var integer
     */
    public $user_stream_subscription_id;

    /**
     * The id of the user who has subscribed to this stream.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The id of the client website that these subscriptions are for.
     *
     * @var integer
     */
    public $site_id;

    /**
     * The extra id of the stream that is being subscribed to. (If the subscription is for an stream).
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The version type of the linked stream.
     *
     * See version_type in the lookup table for valid options.
     *
     * @var integer
     */
    public $version_type;

    /**
     * The display order of this stream in the users list of streams.
     *
     * @var integer
     */
    public $display_order;

    /**
     * Can the user unsubscribe from this stream.
     *
     * @var integer
     */
    public $locked;

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
        return 'user_stream_subscription';
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
            array('user_id, site_id, stream_extra_id, version_type, display_order, ', 'required'),
            array('user_id, site_id, stream_extra_id, version_type, display_order', 'length', 'max' => 10),
            array('locked', 'boolean', 'trueValue' => true, 'falseValue' => false),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_stream_id' => 'User Stream',
            'user_id' => 'User',
            'stream_extra_id' => 'Stream',
            'display_order' => 'Display Order',
        );
    }

    /**
     * Return the primary key of the subscription row that matches the input.
     *
     * @param type $user_id The id of the user who owns this subscription row.
     * @param type $stream_extra_id The extra id of the stream for this row.
     * @param type $version_type_id The type of version that this subscription has. See lookup table for valid values.
     *
     * @return integer|boolean The primary key or false.
     */
    public static function getId($user_id, $stream_extra_id, $version_type_id) {
        $query = "
            SELECT
                 user_stream_subscription_id
            FROM
                user_stream_subscription
            WHERE
                user_id = :user_id
                AND stream_extra_id = :stream_extra_id
                AND version_type = :version_type_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":version_type_id", $version_type_id, PDO::PARAM_INT);
        $user_stream_subscription_id = $command->queryScalar();

        return $user_stream_subscription_id;
    }

       /**
        * Validates that a stream subscription id is actuallya valid stream subscription id.
        *
        * @param integer $user_stream_subscription_id
        * @param integer $user_id
        *
        * @return integer
        */
    public static function checkSubscriptionIsValid($user_stream_subscription_id, $user_id) {
        $query = "
            SELECT
                 user_stream_subscription_id
            FROM
                user_stream_subscription
            WHERE
                user_id = :user_id
                AND user_stream_subscription_id = :user_stream_subscription_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $user_stream_subscription_id = $command->queryScalar();
        if ($user_stream_subscription_id === false) {
            return false;
        } else {
            return true;
        }
    }

       /**
        * Returns the stream extra id for a subscription id.
        *
        * Also validates that the subscription belongs to the given user and client website.
        *
        * @param integer $user_stream_subscription_id The id of the subscription.
        * @param integer $user_id The id of the user that owns the subscription.
        * @param integer $client_site_id The id of the client website that the subscription is on.
        *
        * @return integer
        */
    public static function getStreamExtraId($user_stream_subscription_id, $user_id, $client_site_id) {
        $query = "
            SELECT
                 stream_extra_id
            FROM
                user_stream_subscription
            WHERE
                user_stream_subscription_id = :user_stream_subscription_id
                AND user_id = :user_id
                AND site_id = :client_site_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":client_site_id", $client_site_id, PDO::PARAM_INT);
        $user_stream_subscription_id = $command->queryScalar();
        return $user_stream_subscription_id;
    }


    /**
     * Returns the first subscrition id that a user has
     *
     * Used to check that the user has some subscriptions.
     *
     * @param type $user_id
     */
    public static function getFirstId($user_id) {
        $query = "
            SELECT user_stream_subscription_id
            FROM user_stream_subscription
            WHERE
                user_id = :user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_stream_subscription_id = $command->queryScalar();
        return $user_stream_subscription_id;
    }


    /**
     * Inserts the default streams and filters for a new user.
     *
     * @param integer $user_id The id of the user who has signed up.
     *
     * @return void
     */
    public static function insertDefaults($user_id) {
        $user_multi = new UserMulti();
        $defaults = Yii::app()->params['default_subscriptions'];
        foreach ($defaults as $stream_name) {
            $stream_subscription_form = new StreamSubscriptionForm('subscribe');
            $stream_subscription_form->stream_name = $stream_name;
            $stream_subscription_form->client_domain = HOST;
            $stream_subscription_form->user_id = $user_id;
            $stream_subscription_form->locked = $stream_name['locked'];
            if ($stream_subscription_form->validate() === false) {
                throw new Exception(
                    'Erorr validating a default stream: ' . ErrorHelper::model($stream_subscription_form->getErrors())
                );
            }

            $stream_result = $stream_subscription_form->subscribeStream(false);
            if (is_string($stream_result) === true) {
                throw new Exception('Erorr inserting a default stream.');
            }

            foreach ($stream_name['filters'] as $filter_name) {
                $filter_result = $stream_subscription_form->subscribeFilter(
                    $stream_result['stream_subscription_id'],
                    $filter_name
                );
                if (is_string($filter_result) === true) {
                    throw new Exception('Erorr inserting a default rhythm.');
                }
            }
        }
    }

    /**
     * Check if a stream is deletable or not.
     *
     * @param integer $user_stream_subscription_id The id of the user stream we are deleting.
     * @param integer $user_id The id of the user who owns the stream link we are deleting.
     * @param integer $client_site_id The id of the client domain that the subscrition is on.
     *
     * @return boolean|string true or reason for not being able to delete.
     */
    public static function checkDeleteable($user_stream_subscription_id, $user_id, $client_site_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT locked+0
            FROM user_stream_subscription
            WHERE
                user_stream_subscription.user_stream_subscription_id = :user_stream_subscription_id
                AND user_id = :user_id
                AND site_id = :client_site_id";

        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":client_site_id", $client_site_id, PDO::PARAM_INT);
        $locked = $command->queryScalar();
        if ($locked === false) {
            return 'not_found';
        } else if ($locked === '0') {
            return true;
        } else if ($locked === '1') {
            return 'locked';
        }
    }

    /**
     * Delete a stream link from a users account.
     *
     * @param integer $user_stream_subscription_id The id of the user stream we are deleting.
     * @param integer $user_id The id of the user who owns the stream link we are deleting.
     *
     * @return boolean true if successful
     */
    public static function deleteSubscription($user_stream_subscription_id, $user_id, $client_site_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_stream_subscription
            WHERE
                user_stream_subscription.user_stream_subscription_id = :user_stream_subscription_id
                AND user_id = :user_id
                AND site_id = :client_site_id
                AND locked = 0";

        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":client_site_id", $client_site_id, PDO::PARAM_INT);
        $row_count = $command->execute();
        if ($row_count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Change the version type of a stream subscription.
     *
     * @param integer $stream_subscription_id The id of the stream subscription.
     * @param integer $new_stream_extra_id The extra id of the stream that holds the new stream version.
     * @param integer $new_version_type_id The version type of the new stream version.
     *
     * @return Boolean Successful or not.
     */
    public static function changeVersion($stream_subscription_id, $new_stream_extra_id, $new_version_type_id) {
        $result = UserStreamSubscription::model()->updateByPk(
            $stream_subscription_id,
            array(
                'stream_extra_id' => $new_stream_extra_id,
                'version_type' => $new_version_type_id,
            )
        );
        return true;
    }

    /**
     * Return an array stream subscription rows that include filter and moderation ring data.
     *
     * The +0 on locked fields is to convert a bit field to an int. (some verisons of mysql don't print bit fields.)
     *
     * @param Integer $user_id The id of the user we are fetching streams for.
     * @param Integer $site_id The id of the client website that the subscription is for.
     *
     * @return array Rows of data.
     */
    public static function getUsersSubscriptions($user_id, $site_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 user_stream_subscription.user_stream_subscription_id AS stream_subscription_id
                ,user_stream_subscription.version_type AS stream_version_type_id
                ,user_stream_subscription.display_order AS stream_display_order
                ,user_stream_subscription.locked+0 AS stream_locked
                ,stream.name AS stream_name
                ,stream_user.username AS stream_username
                ,stream_site.domain AS stream_domain
                ,stream_version.major AS stream_major
                ,stream_version.minor AS stream_minor
                ,stream_version.patch AS stream_patch
                ,stream_extra.description AS stream_description

                ,user_stream_subscription_filter.user_stream_subscription_filter_id AS filter_subscription_id
                ,rhythm.name AS rhythm_name
                ,rhythm_user.username AS rhythm_username
                ,rhythm_site.domain AS rhythm_domain
                ,rhythm_version.major AS rhythm_major
                ,rhythm_version.minor AS rhythm_minor
                ,rhythm_version.patch AS rhythm_patch
                ,rhythm_extra.description AS rhythm_description
                ,user_stream_subscription_filter.version_type AS filter_version_type_id
                ,user_stream_subscription_filter.display_order AS filter_display_order
                ,user_stream_subscription_filter.locked+0 AS filter_locked
                ,rhythm_param.name AS param_name
                ,rhythm_param.hint AS param_hint

                ,user_stream_subscription_ring.user_stream_subscription_ring_id AS ring_subscription_id
                ,ring_user.username AS ring_username
                ,ring_site.domain AS ring_domain
                ,user_stream_subscription_ring.display_order AS ring_display_order
                ,user_stream_subscription_ring.locked+0 AS ring_locked

            FROM
                user_stream_subscription
                INNER JOIN stream_extra
                    ON user_stream_subscription.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream.stream_id = stream_extra.stream_id
                INNER JOIN user AS stream_user ON stream.user_id = stream_user.user_id
                INNER JOIN site AS stream_site ON stream_user.site_id = stream_site.site_id
                INNER JOIN version AS stream_version ON stream_extra.version_id = stream_version.version_id

                LEFT JOIN user_stream_subscription_filter
                    ON user_stream_subscription_filter.user_stream_subscription_id =
                        user_stream_subscription.user_stream_subscription_id
                LEFT JOIN rhythm_extra ON user_stream_subscription_filter.rhythm_extra_id = rhythm_extra.rhythm_extra_id
                LEFT JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id
                LEFT JOIN user AS rhythm_user ON rhythm.user_id = rhythm_user.user_id
                LEFT JOIN site AS rhythm_site ON rhythm_user.site_id = rhythm_site.site_id
                LEFT JOIN version AS rhythm_version ON rhythm_extra.version_id = rhythm_version.version_id
                LEFT JOIN rhythm_param ON rhythm_extra.rhythm_extra_id = rhythm_param.rhythm_extra_id

                LEFT JOIN user_stream_subscription_ring
                    ON user_stream_subscription_ring.user_stream_subscription_id =
                        user_stream_subscription.user_stream_subscription_id
                LEFT JOIN ring ON user_stream_subscription_ring.ring_id = ring.ring_id
                LEFT JOIN user AS ring_user ON ring.user_id = ring_user.user_id
                LEFT JOIN site AS ring_site ON ring_site.site_id = ring_user.site_id

            WHERE
                user_stream_subscription.user_id = :user_id
                AND user_stream_subscription.site_id = :site_id
            ORDER BY
                 user_stream_subscription.display_order
                ,user_stream_subscription_filter.display_order
                ,rhythm_param.display_order
                ,user_stream_subscription_ring.display_order";

        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $subscriptions = $command->queryAll();
        return $subscriptions;
    }

    /**
     * Fetches all the user_stream_subscription_id values for a user.
     *
     * @param integer $user_id The id of the user that user_stream_subscription_id values are being fetched for.
     *
     * @return array
     */
    public static function getUserStreamSubscriptionIdsForUser($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT user_stream_subscription_id
                FROM user_stream_subscription
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_stream_subscription_ids = $command->queryColumn();
        return $user_stream_subscription_ids;
    }

    /**
     * Fetches all the user_stream_subscription_id values for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that
     *      user_stream_subscription_id values are being fetched for.
     *
     * @return array
     */
    public static function getUserStreamSubscriptionIdsForStreamExtraID($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT user_stream_subscription_id
                FROM user_stream_subscription
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $user_stream_subscription_ids = $command->queryColumn();
        return $user_stream_subscription_ids;
    }

    /**
     * Deletes user_stream_subscription rows by their user_id.
     *
     * NOTE: This needs to be called from DeleteMulti to ensure the deletion of all dependent data.
     *
     * @param integer $user_id The id of the user that is being used to delete user_stream_subscription rows.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE FROM user_stream_subscription
                WHERE user_id = :user_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            $command->execute();
        } catch (Exception $e) {
            throw new Exception(
                'UserStreamSubscription::deleteByUserId should only be called from DeleteMulti to '
                    . 'enable deletion of relevent child rows connected with a foriegn key. ' . $e
            );
        }
    }

    /**
     * Deletes user_stream_subscription rows by their stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream
     *       that is being used to delete user_stream_subscription rows
     *
     * @return void
     */
    public static function deleteByStreamExtraID($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_stream_subscription
            WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of user_stream_subscription data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_stream_subscription
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>