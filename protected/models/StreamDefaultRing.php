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
 * The table defines rings that have been assigned to attatched to streams by default.
 *
 * @package PHP_Models
 */
class StreamDefaultRing extends CActiveRecord
{

    /**
     * Primary key for this row.
     *
     * @var integer
     */
    public $stream_default_ring_id;

    /**
     * The extra id of the stream that is having a default ring assigned.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The user_id of the ring that is being assigned.
     *
     * @var integer
     */
    public $ring_user_id;

    /**
     * The order that the default rings appear in. 1 is high.
     *
     * @var integer
     */
    public $sort_order;

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
        return 'stream_default_ring';
    }

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('stream_extra_id, ring_user_id', 'required'),
            array('stream_extra_id, ring_user_id', 'length', 'max' => 10),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'stream_default_ring_id' => 'Stream Default Ring',
            'stream_extra_id' => 'stream extra id',
            'ring_user_id' => 'Ring User',
        );
    }

    /**
     * Gets the sort order of the last row in a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that a sort order is being fetched for.
     *
     * @return Array An array of urls.
     */
    public static function getLastSortOrder($stream_extra_id) {
        $sql = "
            SELECT sort_order
            FROM stream_default_ring
            WHERE stream_extra_id = :stream_extra_id
            ORDER BY sort_order DESC
            LiMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $sort_order = $command->queryScalar();
        return $sort_order;
    }

    /**
     * Inserts a new default Ring.
     *
     * @param integer $stream_extra_id The stream_extra_id of the stream that we are inserting a ring into.
     * @param integer $user_id The user_id of the ring to insert.
     *
     * @return Integer Primary key.
     */
    public static function insertRing($stream_extra_id, $user_id) {
        $exists = StreamDefaultRing::doesRingExist($stream_extra_id, $user_id);
        if ($exists === true) {
            return 'This ring is already listed.';
        }

        $sort_order = self::getLastSortOrder($stream_extra_id);
        if ($sort_order === false) {
            $sort_order = 1;
        } else {
            $sort_order++;
        }

        $model = new StreamDefaultRing;
        $model->stream_extra_id = $stream_extra_id;
        $model->sort_order = $sort_order;
        $model->ring_user_id = $user_id;
        $model->save();
        if ($model->hasErrors() === true) {
            $errors = ErrorHelper::model($model->getErrors());
            return $errors;
        } else {
            return intval($model->getPrimaryKey());
        }
    }

    /**
     * Return all the default rings for this stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that dfaults are being fetched for.
     *
     * @return Array An array of urls.
     */
    public static function getDefaults($stream_extra_id, $include_ring_id=true) {
        $sql = "
            SELECT
                 site.domain
                ,user.username
                ,stream_default_ring.sort_order";

        if ($include_ring_id === true) {
            $sql .= ",ring.ring_id";
        }

        $sql .= "
            FROM stream_default_ring
                INNER JOIN user ON stream_default_ring.ring_user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN ring ON stream_default_ring.ring_user_id = ring.user_id
            WHERE stream_default_ring.stream_extra_id = :stream_extra_id
            ORDER BY sort_order";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }


    /**
     * Return all the default rings ids for a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that defaults are being fetched for.
     *
     * @return Array An array of user_ids.
     */
    public static function getIdsForStream($stream_extra_id) {
        $sql = "
            SELECT ring_user_id
            FROM stream_default_ring
            WHERE stream_extra_id = :stream_extra_id
            ORDER BY sort_order";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Remove a default ring from a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that is having a default ring removed.
     * @param integer $stream_default_ring_id The primary key of the row being removed.
     *
     * @return Boolean Success.
     */
    public static function removeRing($stream_extra_id, $user_id) {
        // Needs the stream_extra_id in this query to ensure that the row is owned by that stream
        $rows = StreamDefaultRing::model()->deleteAll(
            'stream_extra_id = :stream_extra_id AND ring_user_id = :ring_user_id',
            array(
                ':stream_extra_id' => $stream_extra_id,
                ':ring_user_id' => $user_id,
            )
        );
        if ($rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Switch two default moderation rings for a stream. Removing the first and adding the second.
     *
     * @param integer $stream_extra_id The extra id of the ring to check if it is already a default for this user.
     * @param integer $ring_user_id The id of the user to check if they have this default.
     *
     * @return boolean
     */
    public static function switchRings($stream_extra_id, $old_user_id, $new_user_id) {
        $model = StreamDefaultRing::model()->find(
            'stream_extra_id = :stream_extra_id AND ring_user_id = :old_user_id',
            array(
                ':stream_extra_id' => $stream_extra_id,
                ':old_user_id' => $old_user_id,
            )
        );

        $model->ring_user_id = $new_user_id;

        $model->save();
        if ($model->hasErrors() === true) {
            $errors = ErrorHelper::model($model->getErrors());
            return $errors;
        } else {
            return true;
        }
    }

    /**
     * Get the row model for a default ring.
     *
     * @param type $stream_extra_id The extra id of the stream.
     * @param type $ring_user_id The user id of the ring.
     *
     * @return StreamDefaultRing
     */
    public static function getDefaultRingRow($stream_extra_id, $ring_user_id) {
        $row = StreamDefaultRing::model()->find(
            array(
                'condition' => 'stream_extra_id=:stream_extra_id '
                . 'AND ring_user_id=:ring_user_id ',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                    ':ring_user_id' => $ring_user_id,
                )
            )
        );
        return $row;
    }

    /**
     * Swaps the display order for two default rings.
     *
     * @param intger $stream_extra_id The extra id of the stream that has two default rings that are being swapped.
     * @param intger $default_ring_user_id_1 The extra id of the first default_ring default_ring.
     * @param intger $default_ring_user_id_2 The extra id of the second default_ring.
     *
     * @return void|string Nothing or an error message.
     */
    public static function swapDefaultRings($stream_extra_id, $default_ring_user_id_1, $default_ring_user_id_2) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $row_1 = self::getDefaultRingRow($stream_extra_id, $default_ring_user_id_1);
            $row_2 = self::getDefaultRingRow($stream_extra_id, $default_ring_user_id_2);
            // have to save to 0 to prevent an integrity constaint problem.
            $first_sort_order = $row_1->sort_order;
            $second_sort_order = $row_2->sort_order;
            $row_1->sort_order = 0;
            $row_1->save();
            $row_2->sort_order = $first_sort_order;
            $row_2->save();
            $row_1->sort_order = $second_sort_order;
            $row_1->save();
            if ($row_1->hasErrors() === true || $row_2->hasErrors() === true) {
                $transaction->rollBack();
                $error = 'Database error whilst swaping two default ring sort orders. '
                    . ErrorHelper::model($row_1->getErrors()) . '. '
                    . ErrorHelper::model($row_2->getErrors());
                return $error;
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            return 'Database error whilst swaping two default ring sort orders.';
        }
    }

    /**
     * Check if a default ring exists.
     *
     * @param integer $stream_extra_id The extra id of the ring to check if it is already a default for this user.
     * @param integer $ring_user_id The id of the user to check if they have this default.
     *
     * @return boolean
     */
    public static function doesRingExist($stream_extra_id, $ring_user_id) {
        $sql = "
            SELECT
                 stream_default_ring_id
            FROM
                stream_default_ring
            WHERE
                stream_extra_id = :stream_extra_id
                AND ring_user_id = :ring_user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $stream_default_ring_id = $command->queryScalar();
        if (ctype_digit($stream_default_ring_id) === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Duplicate the rows for an existing stream for a new one.
     *
     * @param integer $original_stream_extra_id The extra id of the stream the rows are being copied from.
     * @param integer $new_stream_extra_id Theextra id of the new stream the rows are being copied to.
     *
     * @return void
     */
    public static function duplicateForNewStream($original_stream_extra_id, $new_stream_extra_id) {
        $sql = "
            SELECT
                 ring_user_id
                ,sort_order
            FROM stream_default_ring
            WHERE stream_extra_id = :stream_extra_id
            ORDER BY sort_order";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $original_stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        foreach ($rows as $row) {
            $new_model = new StreamDefaultRing;
            $new_model->stream_extra_id = $new_stream_extra_id;
            $new_model->ring_user_id = $row['ring_user_id'];
            $new_model->sort_order = $row['sort_order'];
            $new_model->save();
        }
    }

    /**
     * Insert the site default rings for this rhythm.
     *
     * @param integer $stream_extra_id The extra id for the stream that default rings are being inserted for.
     *
     * @return void
     */
    public static function insertSiteDefaults($stream_extra_id) {
        $defaults = Yii::app()->params['default_moderation_rings'];

        foreach ($defaults as $default) {
            $site_id = SiteMulti::getSiteID($default['domain']);
            $user_multi = new UserMulti($site_id);
            $user_id = $user_multi->getIDFromUsername($default['username']);

            self::insertRing($stream_extra_id, $user_id);
        }
    }

    /**
     * Deletes stream_default_ring rows by their stream_extra_id.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is used to delete these rows.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM stream_default_ring
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes stream_default_ring rows by their ring_user_id.
     *
     * @param integer $ring_user_id The id of the ring user whose stream_default_ring data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingUserId($ring_user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM stream_default_ring
            WHERE ring_user_id = :ring_user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of stream_default_ring data for a stream_extra_id.
     *
     * @param type $stream_extra_id The extra id of the stream to select data for.
     *
     * @return array
     */
    public static function getRowsForStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream_default_ring
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>