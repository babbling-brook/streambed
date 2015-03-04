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
 * Model for the stream_public_rhytnm DB table.
 * Lists stream rhythms that have public results stored for them.
 *
 * @package PHP_Models
 */
class StreamPublicRhythm extends CActiveRecord
{

    /**
     * The primary key of this item.
     *
     * @var integer
     */
    public $stream_public_rhythm_id;

    /**
     * The extra id of the stream that results have been generated for.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The extra id of the  rhythm that was used to generate results. Null is for generic results.
     *
     * @var integer
     */
    public $rhythm_extra_id;

    /**
     * Timestamp for when the results were generated.
     *
     * @var integer
     */
    public $date_generated;

    /**
     * If this is a tree sort then this is the top parent id for the tree.
     *
     * @var integer
     */
    public $top_parent_id;

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
        return 'stream_public_rhythm';
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
            array('stream_public_rhythm_id, stream_extra_id, rhythm_extra_id', 'length', 'max' => 11),
            array('stream_extra_id', 'required'),
            array('stream_public_rhythm_id, stream_extra_id, rhythm_extra_id', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array();
    }

    /**
     * Fetches a list of rhythm details that have public results stored for them.
     *
     * Only returns the top ten results that the user is subscribed to (plus the popular results)
     * Returns an empty row for default results (not an empty array).
     *
     * @param type $stream_extra_id the extra id of the stream that rhythms are being fetched for.
     * @param string [$top_parent_id] If a tree sort is being fetched, then this is the top parent of the tree.
     *
     * @return array an array of rhyhtm data rows.
     */
    public static function getRhythmsStoredForStream($stream_extra_id, $top_parent_id=null) {
        // Simply using a predefined list for now. (faster)
        return Yii::app()->params['public_stream_rhythms'];


//        $connection = Yii::app()->db;
//        $sql = "SELECT DISTINCT
//                      rhythm_extra.rhythm_extra_id
//                     ,site.domain
//                     ,user.username
//                     ,rhythm.name
//                     ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS version
//                FROM
//                    stream_public_rhythm
//                    LEFT JOIN rhythm_extra ON stream_public_rhythm.rhythm_extra_id = rhythm_extra.rhythm_id
//                    LEFT JOIN rhythm ON rhythm.rhythm_id = rhythm_extra.rhythm_id
//                    LEFT JOIN version ON rhythm_extra.version_id = version.version_id
//                    LEFT JOIN user ON rhythm.user_id = user.user_id
//                    LEFT JOIN site ON user.site_id = site.site_id
//
//					LEFT JOIN stream_extra ON stream_public_rhythm.stream_extra_id = stream_extra.stream_extra_id
//                    LEFT JOIN stream
//						      ON stream_extra.stream_id = stream.stream_id
//                    LEFT JOIN user_stream_subscription
//                        ON stream_public_rhythm.stream_extra_id = user_stream_subscription.stream_extra_id
//                    		AND user_stream_subscription.user_id = stream.user_id
//                    LEFT JOIN user_stream_subscription_filter
//                        ON user_stream_subscription.user_stream_subscription_id
//                            = user_stream_subscription_filter.user_stream_subscription_id
//                     	AND user_stream_subscription_filter.rhythm_extra_id = rhythm_extra.rhythm_extra_id
//                WHERE
//                    stream_public_rhythm.stream_extra_id = :stream_extra_id
//                    AND (stream_public_rhythm.top_parent_id = :top_parent_id
//                        OR(stream_public_rhythm.top_parent_id IS NULL AND :top_parent_id IS NULL))
//                ORDER BY
//                    user_stream_subscription_filter.display_order IS NULL ASC,
//                    user_stream_subscription_filter.display_order
//                LIMIT 20";
//        $command = $connection->createCommand($sql);
//        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
//        if (isset($top_parent_id) === false) {
//            $command->bindValue(":top_parent_id", null, PDO::PARAM_NULL);
//        } else {
//            $command->bindValue(":top_parent_id", $top_parent_id, PDO::PARAM_INT);
//        }
//        $rhythm_rows = $command->queryAll();
//        return $rhythm_rows;
    }

    /**
     * Removes the record of a rhythms results being stored.
     *
     * @param integer $stream_extra_id The extra id of the stream that results where removed from.
     * @param integer [$rhythm_extra_id] The extra id of the rhythm that was used to generate the results.
     *      null is used for default results.
     * @param string [$top_parent_id] If a tree sort is being cleared, then this is the top parent of the tree.
     *
     * @return void
     */
    public static function removeRhythm($stream_extra_id, $rhythm_extra_id=null, $top_parent_id=null) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM stream_public_rhythm
                WHERE
                    (rhythm_extra_id = :rhythm_extra_id
                        OR rhythm_extra_id IS NULL AND :rhythm_extra_id IS NULL)
                    AND stream_extra_id = :stream_extra_id
                    AND (top_parent_id = :top_parent_id
                        OR( top_parent_id IS NULL AND :top_parent_id IS NULL))";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        if ($rhythm_extra_id === false) {
            $command->bindValue(":rhythm_extra_id", null, PDO::PARAM_NULL);
        } else {
            $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        }
        if (isset($top_parent_id) === false) {
            $command->bindValue(":top_parent_id", null, PDO::PARAM_NULL);
        } else {
            $command->bindValue(":top_parent_id", $top_parent_id, PDO::PARAM_INT);
        }
        $command->execute();
    }

    /**
     * Adds the record of a rhythms results being stored.
     *
     * @param integer $stream_extra_id The extra id of the stream that results where added to.
     * @param integer [$rhythm_extra_id] The extra id of the rhythm that was used to generate the results.
     *      null is used for default results.
     * @param string [$top_parent_id] If a tree sort is being generated, then this is the top parent of the tree.
     *
     * @return void
     */
    public static function addRhythm($stream_extra_id, $rhythm_extra_id=null, $top_parent_id=null) {
        self::removeRhythm($stream_extra_id, $rhythm_extra_id);
        $model = new StreamPublicRhythm;
        $model->stream_extra_id = $stream_extra_id;
        $model->rhythm_extra_id = $rhythm_extra_id;
        $model->top_parent_id = $top_parent_id;

        if ($model->save() === false) {
            throw new Exception('Faild to add a rhythm to public stream results.');
        }
    }

    /**
     * Deletes stream_public_rhythm rows by their stream_extra_id.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is used to delete these rows.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM stream_public_rhythm
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes stream_public_rhythm rows by their rhythm_extra_id.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm in stream_public_rhythm that is being deleted.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM stream_public_rhythm
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->execute();
    }
}

?>
