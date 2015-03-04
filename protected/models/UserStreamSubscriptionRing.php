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
 * Model for the user_stream_subscription_ring DB table.
 * The table holds user subscriptions to rings as used to filter streams.
 *
 * @package PHP_Models
 */
class UserStreamSubscriptionRing extends CActiveRecord
{

    /**
     * The primary key of the table.
     *
     * @var integer
     */
    public $user_stream_subscription_ring_id;

    /**
     * The id of the users stream subscription.
     *
     * @var integer
     */
    public $user_stream_subscription_id;

    /**
     * The id of the ring that is being used.
     *
     * @var integer
     */
    public $ring_id;

    /**
     * @var boolean Is the subscription locked.
     */
    public $locked;

    /**
     * @var Integer The display order of the rings.
     */
    public $display_order;

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
        return 'user_stream_subscription_ring';
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
            array('user_stream_subscription_id, ring_id', 'required'),
            array('user_stream_subscription_id, ring_id', 'length', 'max' => 10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            //array('user_stream_subscription_ring_id, user_stream_subscription_id, ring_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_stream_subscription_ring_id' => 'User Stream Ring',
            'user_stream_subscription_id' => 'User Post Stream',
            'ring_id' => 'Ring',
        );
    }

    /**
     * Inserts an streams default moderation rings into a users stream subscription.
     *
     * @param integer $user_stream_subscription_id The stream subscription to insert defaults for.
     * @param integer $stream_extra_id The extra id of the stream that defaults are being inserted for.
     *
     * @return integer The number of rows inserted.
     */
    public static function insertDefaults($user_stream_subscription_id, $stream_extra_id) {
        $rings = StreamDefaultRing::getDefaults($stream_extra_id);

        foreach ($rings as $ring) {
            UserStreamSubscriptionRing::insertRing($user_stream_subscription_id, $ring['ring_id']);
        }

        return count($rings);
    }

    /**
     * Insert a moderation ring into a users stream subscription.
     *
     * @param integer $user_stream_subscription_id The stream subscription to insert a ring for.
     * @param integer $ring_id The id of the ring that is being inserted.
     *
     * @return void
     */
    public static function insertRing($user_stream_subscription_id, $ring_id) {
        $display_order = self::getLastDisplayOrder($user_stream_subscription_id);
        $display_order++;
        $model = new UserStreamSubscriptionRing;
        $model->user_stream_subscription_id = $user_stream_subscription_id;
        $model->ring_id = $ring_id;
        $model->display_order = $display_order;
        if ($model->save() === false) {
            throw new Exception("Failed to save UserStreamSubscriptionRing");
        }
        $subscription = array(
            'ring_subscription_id' => $model->getPrimaryKey(),
            'display_order' => $display_order,
        );
        return $subscription;
    }

    /**
     * Fetch the last display order in the given stream subscription.
     *
     * @param integer $user_stream_subscription_id The stream subscription to insert a ring for.
     *
     * @return Integer The Last display order value in this stream subscription.
     */
    public static function getLastDisplayOrder($user_stream_subscription_id) {
        $sql = "
            SELECT display_order
            FROM
                user_stream_subscription_ring
            WHERE
                user_stream_subscription_ring.user_stream_subscription_id = :user_stream_subscription_id
            ORDER BY display_order DESC
            LIMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $last_display_order = $command->queryScalar();
        if ($last_display_order === false) {
            $last_display_order = 0;
        }
        return $last_display_order;
    }

    /**
     * Insert the site default moderation rings to a users stream subscription.
     *
     * @param integer $user_stream_subscription_id The stream subscription to insert defaults for.
     *
     * @return void
     */
    public static function insertSiteDefaults($user_stream_subscription_id) {
        foreach (Yii::app()->params['moderation_ring_defaults'] as $ring_id) {
            UserStreamSubscriptionRing::insertRing($user_stream_subscription_id, $ring_id);
        }
    }

    /**
     * Delete the moderation rings for a uses stream subscription.
     *
     * @param integer $user_stream_subscription_id The stream subscription to remove rings from.
     *
     * @return void
     */
    public static function deleteModerationRings($user_stream_subscription_id) {
        UserStreamSubscriptionRing::model()->deleteAll(
            'user_stream_subscription_id = :user_stream_subscription_id',
            array(
                ':user_stream_subscription_id' => $user_stream_subscription_id,
            )
        );
    }

    /**
     * Get the moderation ring names for a users stream.
     *
     * @param integer $user_stream_subscription_id The stream subscription to get subscribed rings for.
     *
     * @return Array of ring names
     */
    public static function getForStream($user_stream_subscription_id) {
        $sql = "
            SELECT
                 user_stream_subscription_ring.user_stream_subscription_ring_id AS ring_subscription_id
                ,user.username
                ,site.domain
                ,user_stream_subscription_ring.locked
                ,user_stream_subscription_ring.display_order
            FROM
                user_stream_subscription_ring
                INNER JOIN ring ON user_stream_subscription_ring.ring_id = ring.ring_id
                INNER JOIN user ON ring.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
            WHERE
                user_stream_subscription_ring.user_stream_subscription_id = :user_stream_subscription_id
            ORDER BY display_order";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        $rings = array();
        foreach ($rows as $row) {
            $rings[$row['display_order']] = $row;
        }
        return $rings;
    }

    /**
     * Remove a ring from a users stream subscription.
     *
     * @param integer $user_stream_subscription_id The id of the stream subscription to remove a ring from.
     * @param string $user_stream_subscription_ring_id The id of the ring subscription to remove.
     *
     * @return boolean|string True or error message.
     */
    public static function removeRing($user_stream_subscription_id, $user_stream_subscription_ring_id) {
        // The join is to ensure that the given user owns the stream
        $sql = "
            DELETE FROM user_stream_subscription_ring
            WHERE
                user_stream_subscription_ring_id = :user_stream_subscription_ring_id
                AND user_stream_subscription_id = :user_stream_subscription_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $command->bindValue(":user_stream_subscription_ring_id", $user_stream_subscription_ring_id, PDO::PARAM_INT);
        $rows = $command->execute();
        if ($rows > 0) {
            return true;
        } else {
            return "No moderation ring found.";
        }
    }

    /**
     * Add a ring to a users stream.
     *
     * @param integer $user_stream_subscription_id The stream subscription to insert a ring for.
     * @param string $ring_name The full name of the ring.
     * @param integer $user_id The id of the user who owns this stream.
     *
     * @return boolean|string True or error message.
     */
    public static function insertRingWithCheck($user_stream_subscription_id, $ring_name, $user_id) {
        $valid_user_form = new ValidUserForm;
        $valid_user_form->url = $ring_name;
        $valid_user_form->check_ring = true;
        //@fixme if not found then need to check remote data store for details, then error if still not found.
        if ($valid_user_form->validate() === false) {
            return "Ring name is not valid.";
        }

        $ring_user_id = $valid_user_form->getUserId();
        $ring_id = Ring::getRingIdFromUserId($ring_user_id);

        $subscription_valid = UserStreamSubscription::checkSubscriptionIsValid($user_stream_subscription_id, $user_id);
        if ($subscription_valid === false) {
            return "Stream subscription id is not valid.";
        }

        UserStreamSubscriptionRing::insertRing($user_stream_subscription_id, $ring_id);

        return true;
    }

    /**
     * Fetch a users stream subscriptions for a ring.
     *
     * @param {integer} $user_id The id of the user whose subscriptions we are fetching.
     * @param {integer} $ring_id The id of the ring whose subscriptions we are fetching.
     */
    public static function getSubscriptions($user_id, $ring_id) {
        $sql = "
            SELECT
                 stream.name
                ,user.username
                ,site.domain
                ,version.major
                ,version.minor
                ,version.patch
                ,user_stream_subscription.version_type
            FROM user_stream_subscription_ring
                INNER JOIN user_stream_subscription
                    ON user_stream_subscription_ring.user_stream_subscription_id
                        = user_stream_subscription.user_stream_subscription_id
                INNER JOIN stream_extra ON user_stream_subscription.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user ON stream.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
            WHERE
                user_stream_subscription_ring.ring_id = :ring_id
                AND user_stream_subscription.user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        // Convert the absolute version numbers into a 'latest' version string.
        foreach ($rows as $key => $row) {
            $version_type = Version::makeVersionUrlFromVersionTypeId(
                $row['version_type'],
                $row['major'],
                $row['minor'],
                $row['patch']
            );
            $rows[$key]['version'] = $version_type;
            unset($rows[$key]['major']);
            unset($rows[$key]['minor']);
            unset($rows[$key]['patch']);
            unset($rows[$key]['version_type']);
        }
        return $rows;
    }

    /**
     * Deletes user_stream_subscription_ring rows with their ring_id.
     *
     * @param integer $ring_id The id of the ring whose user_stream_subscription_ring data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_stream_subscription_ring
            WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_stream_subscription_ring rows by their user_stream_subscription_id.
     *
     * @param integer $user_stream_subscription_id The id of the user_stream_subscription
     *       in user_stream_subscription_ring that is being deleted.
     *
     * @return void
     */
    public static function deleteByUserStreamSubscriptionId($user_stream_subscription_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_stream_subscription_ring
            WHERE user_stream_subscription_id = :user_stream_subscription_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of user_stream_subscription_ring data for a user_stream_subscription_id.
     *
     * @param type $user_stream_subscription_id The id of the stream subscription to select data for.
     *
     * @return array
     */
    public static function getRowsForUserStreamSubscriptionId($user_stream_subscription_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_stream_subscription_ring
                WHERE user_stream_subscription_id = :user_stream_subscription_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>