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
 * Model for the user_stream_subscription_filter DB table.
 * Lists the filters that a user has subscribed to for a stream subscription.
 *
 * @package PHP_Models
 */
class UserStreamSubscriptionFilter extends CActiveRecord
{

    /**
     * The primary key of the user filter link.
     *
     * @var integer
     */
    public $user_stream_subscription_filter_id;

    /**
     * The id of the users stream subscription that this filter is for.
     *
     * @var integer
     */
    public $user_stream_subscription_id;

    /**
     * The id of filter that is subscribed to.
     *
     * @var integer
     */
    public $rhythm_extra_id;

    /**
     * The type of the version that is applied to this filter.
     *
     * See version_type in the lookup table for valid options.
     *
     * @var integer
     */
    public $version_type;

    /**
     * The display order of the filters within the stream they are attatched to.
     *
     * @var integer
     */
    public $display_order;

    /**
     * Can the user unsubscribe from this filter..
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
        return 'user_stream_subscription_filter';
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
            array('user_stream_subscription_id, rhythm_extra_id, version_type, display_order', 'required'),
            array('user_stream_subscription_id, rhythm_extra_id, version_type, display_order', 'length', 'max' => 10),
            array('locked', 'boolean', 'trueValue' => true, 'falseValue' => false),
            array(
                'user_stream_subscription_id+display_order',
                'application.extensions.uniqueMultiColumnValidator',
                'message' => 'This stream is already subscribed with the same display order',
            ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array(
                'user_stream_subscription_filter_id, user_stream_subscription_id, '
                    . 'rhythm_extra_id, version_type, display_order',
                'safe',
                'on' => 'search',
            ),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user_stream_subscription' => array(
                self::BELONGS_TO,
                'UserStreamSubscription',
                'user_stream_subscription_id',
                'joinType' => 'INNER JOIN',
            ),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_stream_subscription_filter_id' => 'User Filter',
            'user_stream_subscription_id' => 'User Post Stream',
            'rhythm_extra_id' => 'Rhythm Extra',
            'version_type' => 'Version Type',
            'display_order' => 'Display Order',
        );
    }

    /**
     * Get the primary key for the given values.
     *
     * @param integer $user_stream_subscription_id The subscription id for this row.
     * @param integer $rhythm_extra_id The extra id of the filter that is used for this subscription.
     * @param intger  $version_type_id The id of the version type that is used for this subscription.
     *                                 See lookup table for valid values.
     *
     * @return integer|boolean The primary key or false.
     */
    public static function getId($user_stream_subscription_id, $rhythm_extra_id, $version_type_id) {
        $query = "
            SELECT
                 user_stream_subscription_filter_id
            FROM
                user_stream_subscription_filter
            WHERE
                user_stream_subscription_id = :user_stream_subscription_id
                AND rhythm_extra_id = :rhythm_extra_id
                AND version_type = :version_type_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->bindValue(":version_type_id", $version_type_id, PDO::PARAM_INT);
        $user_stream_subscription_filter_id = $command->queryScalar();

        return $user_stream_subscription_filter_id;
    }

    /**
     * Check if this filter subscription is owned by the supplied user.
     *
     * @param integer $user_stream_subscription_filter_id The id of the filter to check ownership for.
     * @param integer $user_id The id of the user to check if it owns the filter.
     *
     * @return boolean
     */
    public static function checkOwner($user_stream_subscription_filter_id, $user_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 user_stream_subscription.user_id
            FROM user_stream_subscription_filter
                INNER JOIN user_stream_subscription
                    ON user_stream_subscription_filter.user_stream_subscription_id =
                        user_stream_subscription.user_stream_subscription_id
            WHERE
                user_stream_subscription_filter.user_stream_subscription_filter_id =
                    :user_stream_subscription_filter_id";

        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_filter_id", $user_stream_subscription_filter_id, PDO::PARAM_INT);
        $owner_id = $command->queryScalar();
        if ($owner_id === false) {
            return false;
        }
        if (intval($owner_id) !== $user_id) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Fetch the rhythm_extra_id for a filter.
     *
     * @param integer $user_stream_subscription_filter_id The id of the user filter to fetch an extra id for.
     *
     * @return integer
     */
    public static function getRhythmExtraIDFromFilterID($user_stream_subscription_filter_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 rhythm_extra_id
            FROM
                user_stream_subscription_filter
            WHERE
                user_stream_subscription_filter_id = :user_stream_subscription_filter_id";

        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_filter_id", $user_stream_subscription_filter_id, PDO::PARAM_INT);
        $rhythm_extra_id = $command->queryScalar();
        return $rhythm_extra_id;
    }


    /**
     * Updates the version of a filter that a user is subscribed to.
     *
     * @param integer $user_stream_subscription_filter_id  The id of the user filter whose version is being updated.
     * @param integer $user_id The user who is supposed to own this stream - used to check.
     * @param string $partial_version A full or partial version.
     *
     * @return boolean Success
     */
    public static function updateVersion($user_stream_subscription_filter_id, $user_id, $partial_version) {
        if (UserStreamSubscriptionFilter::checkOwner($user_stream_subscription_filter_id, $user_id) === false) {
            throw new Exception("User does not own this stream");
        }

        // Work out the version type for the submitted partial version
        $version_type = Version::getTypeId($partial_version);

        $rhythm_extra_id = UserStreamSubscriptionFilter::getRhythmExtraIDFromFilterID(
            $user_stream_subscription_filter_id
        );

        // Validate that new partial version is valid.
        $zero_version = str_replace("latest", "0", $partial_version);
        $zero_version_array = explode("/", $zero_version);
        $rhythm_extra_id = Rhythm::getExtraIDFromVersion($zero_version_array, $rhythm_extra_id, false);
        //@fixme if not found and not local then need to fetch from remote data store before failing.
        if ($rhythm_extra_id === false) {
            return false;
        }

        $count = $model = UserStreamSubscriptionFilter::model()->updateByPk(
            $user_stream_subscription_filter_id,
            array(
                'rhythm_extra_id' => $rhythm_extra_id,
                'version_type' => $version_type,
            ),
            "",
            array()
        );

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function getNextDisplayOrder($user_stream_subscription_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT display_order
            FROM user_stream_subscription_filter
            WHERE
                user_stream_subscription_id = :user_stream_subscription_id
            ORDER BY user_stream_subscription_filter.display_order DESC
            LIMIT 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $display_order = $command->queryScalar();
        if ($display_order === false) {
            return 1;
        } else {
            return $display_order + 1;
        }
    }

    /**
     * Insert a filter to a users account from its ID.
     *
     * @param integer $user_stream_subscription_id The primary key of the stream we are inserting a filter for.
     * @param integer $rhythm_extra_id The extra id of the Rhythm for the filter we are inserting.
     * @param integer $version_type_id The id of the type of version we are using for this Rhythm.
     * @param integer $display_order The display order for the new filter.
     * @param boolean $locked Is this rhythm subscription locked.
     *
     * @return void
     */
    public static function insertFilter($user_stream_subscription_id, $rhythm_extra_id,
        $version_type_id, $display_order, $locked
    ) {
        $user_filter = new UserStreamSubscriptionFilter;
        $user_filter->user_stream_subscription_id = $user_stream_subscription_id;
        $user_filter->rhythm_extra_id = $rhythm_extra_id;
        $user_filter->version_type = $version_type_id;
        $user_filter->display_order = $display_order;
        $user_filter->locked = $locked;

        if ($user_filter->validate() === false) {
            throw new Exception(
                "Stream filter insert does not validate: " . ErrorHelper::model($user_filter->getErrors())
            );
        }

        $user_filter->save();
        return $user_filter->getPrimaryKey();
    }

    /**
     * Delete all the filters for a users stream subscription.
     *
     * Used after a stream subscription has been deleted.
     * Does not check for ownership - assumed this has already been taken care of.
     *
     * @param integer $user_stream_subscription_id The id of the stream subscription
     *      that filters are being deleted for.
     *
     * @return boolean true if successful
     */
    public static function deleteFilters($user_stream_subscription_id) {
        $row_count = UserStreamSubscriptionFilter::model()->deleteAll(
            "user_stream_subscription_id = :user_stream_subscription_id",
            array(
                ":user_stream_subscription_id" => $user_stream_subscription_id,
            )
        );
        if ($row_count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete a filter for a users stream subscription.
     *
     * Does not check for ownership - assumed this has already been taken care of.
     *
     * @param integer $user_stream_subscription_id The id of the stream subscription
     *      that a filter is being deleted for.
     * @param integer $user_stream_subscription_filter_id The id of the filter to delete.
     *
     * @return void
     */
    public static function deleteFilter($user_stream_subscription_id, $user_stream_subscription_filter_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE FROM  user_stream_subscription_filter
            WHERE
                user_stream_subscription_id = :user_stream_subscription_id
                AND user_stream_subscription_filter_id = :user_stream_subscription_filter_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $command->bindValue(":user_stream_subscription_filter_id", $user_stream_subscription_filter_id, PDO::PARAM_INT);
        $display_order = $command->execute();
    }

    /**
     * Fetch all the filter rhythms for a stream subscription.
     *
     * @param integer $user_stream_subscription_id The id of the stream subscription
     *      that filters are being fetched for.
     *
     * @return array
     */
    public static function getForStream($user_stream_subscription_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 user_stream_subscription_filter.user_stream_subscription_filter_id AS subscription_id
                ,user_stream_subscription_filter.display_order
                ,user_stream_subscription_filter.locked
                ,user_stream_subscription_filter.version_type
                ,site.domain
                ,user.username
                ,rhythm.name
                ,rhythm_extra.description
                ,version.major
                ,version.minor
                ,version.patch
                ,version.type
            FROM
                user_stream_subscription_filter
                INNER JOIN rhythm_extra
                    ON user_stream_subscription_filter.rhythm_extra_id = rhythm_extra.rhythm_extra_id
                INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id
                INNER JOIN version ON rhythm_extra.version_id = version.version_id
                INNER JOIN user ON rhythm.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN lookup AS version_type_lookup
                    ON user_stream_subscription_filter.version_type = version_type_lookup.lookup_id
            WHERE
                user_stream_subscription_id = :user_stream_subscription_id
            ORDER BY user_stream_subscription_filter.display_order";

        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }


    /**
     * Change the version of a filter subscription.
     *
     * @param integer $filter_subscription_id The id of the filter subscription.
     * @param integer $new_rhythm_extra_id The extra id of the filter that holds the new stream version.
     * @param integer $new_version_type_id The version type of the new rhythm version.
     *
     * @return Boolean Successful or not.
     */
    public static function changeVersion($filter_subscription_id, $new_rhythm_extra_id, $new_version_type_id) {
        $result = UserStreamSubscriptionFilter::model()->updateByPk(
            $filter_subscription_id,
            array(
                'new_rhythm_extra_id' => $new_rhythm_extra_id,
                'version_type' => $new_version_type_id,
            )
        );
        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes user_stream_subscription_filter rows by their rhythm_extra_id.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm in
     *      user_stream_subscription_filter that is being deleted.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_stream_subscription_filter
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_stream_subscription_filter rows by their user_stream_subscription_id.
     *
     * @param integer $user_stream_subscription_id The id of the user_stream_subscription
     *       in user_stream_subscription_filter that is being deleted.
     *
     * @return void
     */
    public static function deleteByUserStreamSubscriptionId($user_stream_subscription_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_stream_subscription_filter
            WHERE user_stream_subscription_id = :user_stream_subscription_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of user_stream_subscription_filter data for a user_stream_subscription_id.
     *
     * @param type $user_stream_subscription_id The id of the stream subscription to select data for.
     *
     * @return array
     */
    public static function getRowsForUserStreamSubscriptionId($user_stream_subscription_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_stream_subscription_filter
                WHERE user_stream_subscription_id = :user_stream_subscription_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_stream_subscription_id", $user_stream_subscription_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>