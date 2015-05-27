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
 * Model for the stream_default_ring DB table.
 * The table holds the information for each version of an stream.
 *
 * @package PHP_Models
 */
class StreamExtra extends CActiveRecord
{

    /**
     * The primary key of this version of this stream.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The primary key of the streams top level table.
     *
     * @var string
     */
    public $stream_id;

    /**
     * The creation date for this version of the stream.
     *
     * @var string
     */
    public $date_created;

    /**
     * The description for this version of the stream.
     *
     * @var string
     */
    public $description;

    /**
     * The primary key of the version table row holding the version information about this stream.
     *
     * @var integer
     */
    public $version_id;

    /**
     * A primary key of the status table that points to this streams status.
     *
     * @var integer
     */
    public $status_id;

    /**
     * The id of a timespan indicating how long a gap to timegap to leave between block numbers.
     *
     * @var integer
     */
    public $group_period;

    /**
     * Who has the ability to make posts. See lookup table for options.
     *
     * @var integer
     */
    public $post_mode;

    /**
     * Links to an post_id where people can vote and comment on this stream.
     *
     * @var integer
     */
    public $meta_post_id;

    /**
     * What is the preferred way in which this stream should be presented. See lookup table for details
     *
     * @var integer
     */
    public $presentation_type_id;

    /**
     * The string version of presentation_type_id. This is not a table column.
     *
     * Converted to presentation_type_id during validation.
     *
     * @var integer
     */
    public $presentation_type;

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
        return 'stream_extra';
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
            array('stream_id, version_id', 'required', 'except' => 'composite'),
            array('description, status_id, edit_mode', 'required'),
            array(
                'version_id, status_id, group_period, post_mode, meta_post_id, edit_mode, presentation_type_id',
                'length',
                'max' => 10
            ),
            array('status_id', 'ruleStatus'),
            array('status_id', 'ruleGroupPeriod'),
            array('status_id', 'rulePostMode'),
            array('status_id', 'ruleEditMode'),
            array('presentation_type_id', 'rulePresentationType'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('stream_extra_id, date_created, description, version_id, status_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * A rule to check that the status id value is valid.
     *
     * @return void
     */
    public function ruleStatus() {
        $valid = StatusHelper::getValue($this->status_id, false);
        if ($valid === false) {
            $this->addError('status_id', 'Status id is not a valid value. ' . $this->status_id);
        }
    }

    /**
     * A rule to check that the group period value is valid.
     *
     * @return void
     */
    public function ruleGroupPeriod() {
        if (isset($this->group_period) === true && $this->group_period > 0) {
            $valid = LookupHelper::validId('stream_extra.group_period', $this->group_period, false);
            if ($valid === false) {
                $this->addError('group_period', 'Group Period is not a valid value. ' . $this->group_period);
            }
        }
    }

    /**
     * A rule to check that the post mode value is valid.
     *
     * @return void
     */
    public function rulePostMode() {
        if (isset($this->post_mode) === false) {
             $this->post_mode = LookupHelper::getID('stream_extra.post_mode', 'owner');
        }
        $valid = LookupHelper::validId('stream_extra.post_mode', $this->post_mode, false);
        if ($valid === false) {
            $this->addError('post_mode', 'Post Mode is not a valid value. ' . $this->post_mode);
        }
    }

    /**
     * A rule to check that the edit mode value is valid.
     *
     * @return void
     */
    public function ruleEditMode() {
        $valid = LookupHelper::validId('stream_extra.edit_mode', $this->edit_mode, false);
        if ($valid === false) {
            $this->addError('edit_mode', 'Edit Mode is not a valid value. ' . $this->edit_mode);
        }
    }

    /**
     * A rule to check that the edit mode value is valid.
     *
     * @return void
     */
    public function rulePresentationType() {
        $is_valid_type = LookupHelper::valid('stream_extra.presentation_type_id', $this->presentation_type);
        if ($is_valid_type === false) {
             $this->addError('presentation_type_id', 'Not a valid presentation type.');
        } else {
            $this->presentation_type_id = LookupHelper::getID(
                'stream_extra.presentation_type_id',
                $this->presentation_type
            );
        }
    }

    /**
     * Sets the status id value from its textutal value defined in the Babbling Brook protocol.
     *
     * @param string $status A Babbling Brook stream.kind value.
     *
     * @return void
     */
    public function setStatusFromText($status) {
        $status_id = StatusHelper::getID($status, false);
        if ($status_id === false) {
            $this->addError('status', 'Status is not a valid Babbling Brook value. ' . $status);
        } else {
            $this->status_id = $status_id;
        }
    }

    /**
     * Sets the group period value from its textutal value defined in the Babbling Brook protocol.
     *
     * @param string $group_period A Babbling Brook stream_extra.group_period value
     *
     * @return void
     */
    public function setGroupPeriodFromText($group_period) {
        $group_period_id = LookupHelper::getID('stream_extra.group_period', $group_period, false);
        if ($group_period_id === false) {
            $this->addError('group period', 'Group Period is not a valid Babbling Brook value. ' . $group_period);
        } else {
            $this->group_period = $group_period_id;
        }
    }

    /**
     * Sets the post mode value from its textutal value defined in the Babbling Brook protocol.
     *
     * @param string $post_mode A Babbling Brook stream_extra.post_mode value
     *
     * @return void
     */
    public function setPostModeFromText($post_mode) {
        $post_mode_id = LookupHelper::getID('stream_extra.post_mode', $post_mode, false);
        if ($post_mode_id === false) {
            $this->addError('post_mode', 'Post Mode is not a valid Babbling Brook value. ' . $post_mode);
        } else {
            $this->post_mode = $post_mode_id;
        }
    }


    /**
     * Sets the edit mode value from its textutal value defined in the Babbling Brook protocol.
     *
     * @param string $edit_mode A Babbling Brook stream_extra.edit_mode value
     *
     * @return void
     */
    public function setEditModeFromText($edit_mode) {
        $edit_mode_id = LookupHelper::getID('stream_extra.edit_mode', $edit_mode, false);
        if ($edit_mode_id === false) {
            $this->addError('edit_mode', 'Post Mode is not a valid Babbling Brook value. ' . $edit_mode);
        } else {
            $this->edit_mode = $edit_mode_id;
        }
    }


    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'stream' => array(self::BELONGS_TO, 'Stream', 'stream_id', 'joinType' => 'INNER JOIN'),
            'version' => array(self::BELONGS_TO, 'Version', 'version_id', 'joinType' => 'INNER JOIN'),
            'status' => array(self::BELONGS_TO, 'Status', 'status_id', 'joinType' => 'INNER JOIN'),
            'post' => array(self::HAS_MANY, 'Post', 'stream_extra_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'date_created' => 'Date Created',
            'description' => 'Description',
            'version_id' => 'Version',
            'status_id' => 'Status',
        );
    }

    /**
     * Update the post mode for an stream.
     *
     * @param integer $stream_extra_id The extra id of the stream whose mode is being updated.
     * @param integer $post_mode_id The id of the post mode that is being updated.
     *                               See lookup table for valid options.
     *
     * @return boolean Was the operation successful.
     */
    public static function updatePostMode($stream_extra_id, $post_mode_id) {
        $query = "
            UPDATE stream_extra
            SET post_mode = :post_mode
            WHERE stream_extra_id = :stream_extra_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":post_mode", $post_mode_id, PDO::PARAM_INT);
        $row_count = $command->execute();
        if ($row_count === 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Fetch the post_mode for an stream.
     *
     * @param type $stream_extra_id The extra id of the stream to fetch an post mode for.
     *
     * @return string The name of the post mode.
     */
    public static function getPostMode($stream_extra_id) {
        $query = "
            SELECT post_mode
            FROM stream_extra
            WHERE stream_extra_id = :stream_extra_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $post_mode_id = $command->queryScalar();
        return LookupHelper::getValue($post_mode_id);
    }

    /**
     * Fetch the edit_mode for an stream.
     *
     * @param type $stream_extra_id The extra id of the stream to fetch an edit mode for.
     *
     * @return string The name of the post mode.
     */
    public static function getEditMode($stream_extra_id) {
        $query = "
            SELECT edit_mode
            FROM stream_extra
            WHERE stream_extra_id = :stream_extra_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $edit_mode_id = $command->queryScalar();
        return LookupHelper::getValue($edit_mode_id);
    }

    /**
     * Update the post mode for an stream.
     *
     * @param integer $stream_extra_id The extra id of the stream whose meta_post_id is being updated.
     * @param integer $meta_post_id The post id that that is used as a meta post for this stream_extra_id.
     *
     * @return boolean Was the operation successful.
     */
    public static function updateMetaPostId($stream_extra_id, $meta_post_id) {
        $query = "
            UPDATE stream_extra
            SET meta_post_id = :meta_post_id
            WHERE stream_extra_id = :stream_extra_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":meta_post_id", $meta_post_id, PDO::PARAM_INT);
        $row_count = $command->execute();
        if ($row_count === 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the version_id for an stream_extra_id
     *
     * @param integer $stream_extra_id The extra id of the stream to get a version for.
     *
     * @return void
     */
    public static function getVersionID($stream_extra_id) {
        $query = "
            SELECT version_id
            FROM stream_extra
            WHERE stream_extra_id = :stream_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':stream_extra_id', $stream_extra_id, PDO::PARAM_INT);
        $version_id = $command->queryScalar();
        if ($version_id === false) {
            throw new Exception('stream_extra_id is not found in stream_extra ' . $stream_extra_id);
        }
        return $version_id;
    }

    /**
     * Returns the first stream_extra_id for a given stream_id.
     *
     * @param integer stream_id The stream_id used to fetch a stream_extra_id.
     *
     * @return integer The first stream_extra_id for this stream.
     */
    public static function getFirstExtraID($stream_id) {
        $query = "
            SELECT stream_extra_id
            FROM stream_extra
            WHERE stream_id = :stream_id
            ORDER BY stream_extra_id
            LIMIT 1";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_id", $stream_id, PDO::PARAM_INT);
        $stream_extra_id= $command->queryScalar();
        if ($stream_extra_id === false) {
            throw new Exception("stream_extra_id not found for stream_id : " . $stream_id);
        }
        return $stream_extra_id;
    }

    /**
     * Get the user_id of the owner of this stream.
     *
     * @param integer $stream_extra_id
     *
     * @return integer
     */
    public static function getOwnerID($stream_extra_id) {
        $query = "
            SELECT stream.user_id
            FROM stream_extra
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
            WHERE stream_extra_id = :stream_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $user_id = $command->queryScalar();
        return $user_id;
    }

    /**
     * Get the the table row for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that a row is being fetched for.
     *
     * @return array The stream row
     */
    public static function getRow($stream_extra_id) {
        $query = "
            SELECT *
            FROM stream_extra
            WHERE stream_extra_id = :stream_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        return $row;
    }

    /**
     * Get a count of the number of versions of a stream that there are.
     *
     * @param integer $stream_id The family id of the stream.
     *
     * @return integer The number of versions of this stream.
     */
    public static function getVersionCount($stream_id) {
        $query = "
            SELECT COUNT(stream_id)
            FROM stream_extra
            WHERE stream_id = :stream_id
            GROUP BY stream_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_id", $stream_id, PDO::PARAM_INT);
        $count = $command->queryScalar();
        return intval($count);
    }

    /**
     * Delete a stream row.
     *
     * @param integer $stream_extra_id The primary key of the row to delete.
     *
     * @return void
     */
    public static function deleteRow($stream_extra_id) {
        StreamExtra::model()->deleteByPk($stream_extra_id);
    }

    /**
     * Get the current status of a stream.
     *
     * @param integer $stream_extra_id The status of a stream.
     *
     * @return string The status of the stream.
     */
    public static function getStatus($stream_extra_id) {
        $query = "
            SELECT status_id
            FROM stream_extra
            WHERE stream_extra_id = :stream_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $status_id = $command->queryScalar();
        $status = StatusHelper::getValue($status_id);
        return $status;
    }

    /**
     * Fetches all the stream_exta_id values for a stream.
     *
     * @param integer $stream_id The id of the stream that stream_extra_id values are being fetched for.
     *
     * @return array An simple array of stream_extra_ids values.
     */
    public static function getStreamExtraIdsForStream($stream_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT stream_extra_id
                FROM stream_extra
                WHERE stream_id = :stream_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_id", $stream_id, PDO::PARAM_INT);
        $stream_extra_ids = $command->queryColumn();
        return $stream_extra_ids;
    }

    /**
     * Deletes a stream_extra row by its id.
     *
     * NOTE: This needs to be called from DeleteMulti to ensure the deletion of all dependent data.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is being deleted.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "DELETE FROM stream_extra
                    WHERE stream_extra_id = :stream_extra_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
            $command->execute();

        } catch (Exception $e) {
            throw new Exception(
                'StreamExtra::deleteByStreamExtraId should only be called from DeleteMulti to enable deletion of'
                    . 'relevent child rows connected with a foriegn key. ' . $e
            );
        }
    }

    /**
     * Sets the meta_post of all stream_extra rows with a given post_id to NULL.
     *
     * @param $post_id The id of the post whose meta_post_id is being set to NULL.
     *
     * @return void
     */
    public static function setMetaPostToNullForPostId($post_id) {
        $sql = "
            UPDATE stream_extra
            SET meta_post_id = NULL
            WHERE meta_post_id = :post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Fetches the stream id for a stream_extra_id
     *
     * @param integer $stream_extra_id The id of the stream that a stream_id is being fetched for.
     *
     * @return integer The stream_id.
     */
    public static function getStreamID($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT stream_id
                FROM stream_extra
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $stream_extra_id = $command->queryScalar();
        return $stream_extra_id;
    }

    /**
     * Select rows of stream_extra data for a user.
     *
     * @param type $stream_id The id of the stream to select data for.
     *
     * @return array
     */
    public static function getRowsForStreamId($stream_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream_extra
                WHERE stream_id = :stream_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_id", $stream_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }


}

?>