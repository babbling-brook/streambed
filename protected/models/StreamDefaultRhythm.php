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
 * Model for the stream_default_rhythm DB table.
 *
 * Stores the default rhythm subscriptions for a stream.
 *
 * @package PHP_Models
 */
class StreamDefaultRhythm extends CActiveRecord
{

    /**
     * The primary key of this row.
     *
     * @var integer
     */
    public $stream_default_rhythm_id;

    /**
     * The extra id of the stream that has a default rhythm.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The extra of of the rhythm that is a default for this stream.
     *
     * @var integer
     */
    public $rhythm_extra_id;

    /**
     * The type of version that is used for this rhythm. See lookup table for details.
     *
     * @var integer
     */
    public $version_type;

    /**
     * The order these defaults appear in.  1 is high.
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
        return 'stream_default_rhythm';
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
            array('stream_extra_id, rhythm_extra_id, version_type, sort_order', 'required'),
            array('stream_extra_id, rhythm_extra_id, version_type, sort_order', 'numerical', 'integerOnly' => true),
            array(
                'stream_extra_id+rhythm_extra_id+version_type',
                'application.extensions.uniqueMultiColumnValidator',
                'message' => 'This rhythm is already a default',
            ),
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
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'stream_default_rhythm_id' => 'stream_default_rhythm_id',
            'stream_extra_id' => 'stream_extra_id',
            'rhythm_extra_id' => 'rhythm_extra_id',
            'version_type' => 'version_type',
            'sort_order' => 'sort_order',
        );
    }

    /**
     * Fetches the last sort order for the current default rhythms in a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that a default rhythm is being inserted for.
     *
     * @return integer
     */
    public static function fetchLastSortOrder($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT sort_order
                FROM stream_default_rhythm
                WHERE stream_extra_id = :stream_extra_id
                ORDER BY sort_order DESC
                LIMIT 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $sort_order = $command->queryScalar();
        return $sort_order;
    }

    /**
     * Insert a default rhythm for a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that a default rhythm is being inserted for.
     * @param integer $rhythm_extra_id The extra id of the rhythm that is being inserted as a default.
     * @param string $version_type_id The type of version that the rhythm default has. (alows use of 'latest')
     *
     * @return integer|string The primary key or an error message.
     */
    public static function insertDefault($stream_extra_id, $rhythm_extra_id, $version_type_id) {
        $sort_order = self::fetchLastSortOrder($stream_extra_id) + 1;

        $default_model = new StreamDefaultRhythm;
        $default_model->stream_extra_id = $stream_extra_id;
        $default_model->rhythm_extra_id = $rhythm_extra_id;
        $default_model->version_type = $version_type_id;
        $default_model->sort_order = $sort_order;

        $default_model->save();
        if ($default_model->hasErrors() === true) {
            $errors = ErrorHelper::model($default_model->getErrors());
            return $errors;
        } else {
            return intval($default_model->getPrimaryKey());
        }
    }

    /**
     * Removes a default rhtyhm for a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that a default rhythm is being deleted for.
     * @param integer $rhythm_extra_id The extra id of the rhythm that is being removed as a default.
     * @param string $version_type The type of version that the rhythm default has.
     *
     * @return string|true An error message or true
     */
    public static function deleteDefault($stream_extra_id, $rhythm_extra_id, $version_type_id) {

        // Business logic dictates that there must always be at least one default.
        $rows = self::getDefaults($stream_extra_id);
        if (count($rows) < 2) {
            return 'There must always be at least one default rhythm.';
        }

        $connection = Yii::app()->db;
        $sql = "DELETE
                FROM stream_default_rhythm
                WHERE stream_extra_id = :stream_extra_id
                    AND rhythm_extra_id = :rhythm_extra_id
                    AND version_type = :version_type";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->bindValue(":version_type", $version_type_id, PDO::PARAM_INT);
        $rows_affected = $command->execute();
        if ($rows_affected < 1) {
            return 'Default Rhythm not found';
        } else {
            return true;
        }
    }

    /**
     * Fetch the sort rhythm defaults for a stream.
     *
     * @param type $stream_extra_id The extra id of the stream to fetch default rhythms for.
     *
     * @return array Returned as an arrayrhythm name arrays with an additional sort_order attribute.
     */
    public static function getDefaults($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     site.domain
                    ,user.username
                    ,rhythm.name
                    ,version.major
                    ,version.minor
                    ,version.patch
                    ,stream_default_rhythm.version_type
                    ,stream_default_rhythm.sort_order
                FROM
                    stream_default_rhythm
                    INNER JOIN rhythm_extra ON stream_default_rhythm.rhythm_extra_id = rhythm_extra.rhythm_extra_id
                    INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id
                    INNER JOIN version ON rhythm_extra.version_id = version.version_id
                    INNER JOIN user ON rhythm.user_id = user.user_id
                    INNER JOIN site ON user.site_id = site.site_id
                WHERE stream_default_rhythm.stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        foreach ($rows as $key => $row) {
            $version_from_type = Version::makeVersionUrlFromVersionTypeId(
                $row['version_type'],
                $row['major'],
                $row['minor'],
                $row['patch']
            );
            $rows[$key]['version'] = Version::makeArrayFromString($version_from_type);
            unset($rows[$key]['major']);
            unset($rows[$key]['minor']);
            unset($rows[$key]['patch']);
            unset($rows[$key]['version_type']);
        }
        return $rows;
    }

    /**
     * Fetches a defualt rhythm row for a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that has a default rhythm.
     * @param integer $rhythm_extra_id The extra id of the rhythm that whose sort order is being updated.
     * @param string $version_type_id The type of version that the rhythm default has.
     *
     * @return StreamDefaultRhythm
     */
    private static function getDefault($stream_extra_id, $rhythm_extra_id, $version_type_id) {
        $default_model = StreamDefaultRhythm::model()->find(
            array(
                'condition' => 'stream_extra_id=:stream_extra_id '
                    . 'AND rhythm_extra_id=:rhythm_extra_id '
                    . 'AND version_type = :version_type_id',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                    ':rhythm_extra_id' => $rhythm_extra_id,
                    ':version_type_id' => $version_type_id,
                )
            )
        );
        return $default_model;
    }

    /**
     * Fetches a defualt rhythms equal to or below the given sort order for a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream whose default rhythms are being moved.
     * @param integer $sort_order The sort order to fetch rhythms from.
     *
     * @return StreamDefaultRhythm
     */
    private static function getAllBelowSortOrder($stream_extra_id, $sort_order) {
        $default_models = StreamDefaultRhythm::model()->findAll(
            array(
                'condition' => 'stream_extra_id=:stream_extra_id '
                    . 'AND sort_order >= :sort_order',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                    ':sort_order' => $sort_order,
                )
            )
        );
        return $default_models;
    }

    /**
     * Updates the sort order of a default rhtyhm for a stream.
     *
     * Pushes down all defaults that are below this one.
     *
     * @param integer $stream_extra_id The extra id of the stream that has a default rhythm.
     * @param integer $rhythm_extra_id The extra id of the rhythm that whose sort order is being updated.
     * @param integer $version_type_id The type of version that the rhythm default has.
     */
    public static function ladderSortOrder($stream_extra_id, $rhythm_extra_id, $version_type_id, $new_possition) {
        $row_to_move = self::getDefault($stream_extra_id, $rhythm_extra_id, $version_type_id);
        if (isset($row_to_move) === false) {
            return 'Default stream rhythm does not exist.';
        }
        $rows_to_move_down = self::getAllBelowSortOrder($stream_extra_id, $new_possition);
        $count = $new_possition + 1;
        foreach ($rows_to_move_down as $row) {
            if ($rows_to_move_down->sort_order === $count) {
                $rows_to_move_down->sort_order = $count + 1;
            } else {
                break;
            }
            $count++;
        }
        return true;
    }

    /**
     * Switches a prexisting rhythm extra id with a new one.
     *
     * @param intger $stream_extra_id The extra id of the stream that has a rhythm that is being switched.
     * @param intger $old_rhythm_extra_id The extra id of the rhythm that is being replaced.
     * @param integer $old_rhythm_version_type_id The version type from the old rhythm.
     * @param intger $new_rhythm_extra_id The extra id of the new rhythm.
     * @param integer $old_rhythm_version_type_id The version type from the new rhythm.
     *
     * @return void|string Nothing or an error message.
     */
    public static function switchRhythmExtraId($stream_extra_id, $old_rhythm_extra_id, $old_rhythm_version_type_id,
        $new_rhythm_extra_id, $new_rhythm_version_type_id
    ) {
        $row = self::getDefault($stream_extra_id, $old_rhythm_extra_id, $old_rhythm_version_type_id);
        $row->rhythm_extra_id = $new_rhythm_extra_id;
        $row->version_type = $new_rhythm_version_type_id;
        $row->save();
        if ($row->hasErrors() === true) {
            $error = 'Error updating default rhythm for stream' . ErrorHelper::model($row->getErrors());
            return $error;
        } else {
            return;
        }
    }

    /**
     * Swaps the display order for two default rhythms
     *
     * @param intger $stream_extra_id The extra id of the stream that has two rhythms that are being swapped.
     * @param intger $rhythm_extra_id_1 The extra id of the first rhythm.
     * @param integer $rhythm_version_type_id_1 The version type from the first rhythm.
     * @param intger $rhythm_extra_id_2 The extra id of the second rhythm.
     * @param integer $rhythm_version_type_id_2 The version type from the second rhythm.
     *
     * @return void|string Nothing or an error message.
     */
    public static function swapRhythms($stream_extra_id, $rhythm_extra_id_1, $rhythm_version_type_id_1,
        $rhythm_extra_id_2, $rhythm_version_type_id_2
    ) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $row_1 = self::getDefault($stream_extra_id, $rhythm_extra_id_1, $rhythm_version_type_id_1);
            $row_2 = self::getDefault($stream_extra_id, $rhythm_extra_id_2, $rhythm_version_type_id_2);
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
                $error = 'Database error whilst swaping two default rhythm sort orders. '
                    . ErrorHelper::model($row_1->getErrors()) . '. '
                    . ErrorHelper::model($row_2->getErrors());
                return $error;
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            return 'Database error whilst swaping two default rhythm sort orders.';
        }
    }

    /**
     * An admin process that itterates through all streams and assigns default rhtyhms to them if thye dont have one.
     *
     * @return void
     */
    public static function ensureAllStreamsHaveADefaultRhtyhm() {
        $connection = Yii::app()->db;
        $sql = "SELECT stream_extra.stream_extra_id
                FROM stream_extra
                LEFT JOIN stream_default_rhythm ON stream_extra.stream_extra_id = stream_default_rhythm.stream_extra_id
                WHERE stream_default_rhythm.stream_extra_id IS NULL";
        $command = $connection->createCommand($sql);
        $rows = $command->queryAll();

        $default_rhythm_ids = self::generateDefaults();

        foreach ($rows as $row) {
            foreach ($default_rhythm_ids as $rhythm_row) {
                self::insertDefault($row['stream_extra_id'], $rhythm_row['rhythm_id'], $rhythm_row['version_type_id']);
            }
        }
    }

    /**
     * Generates an array rhythm_extra_ids and version_type_ids to represent the site defaults,
     *
     * @return array each row indexed by rhythm_extra_id and version_type_id
     */
    private static function generateDefaults() {
        $defaults = Yii::app()->params['default_sort_filters'];

        $default_rhythm_ids = array();
        foreach ($defaults as $default) {
            $site_id = SiteMulti::getSiteID($default['domain']);
            $user_multi = new UserMulti($site_id);
            $user_id = $user_multi->getIDFromUsername($default['username']);

            $rhythm_extra_id = Rhythm::getIDByName(
                $user_id,
                $default['name'],
                $default['version']['major'],
                $default['version']['minor'],
                $default['version']['patch']
            );
            $version_string = $default['version']['major'] . '/'
                . $default['version']['minor'] . '/' . $default['version']['patch'];
            $version_type_id = Version::getTypeId($version_string);
            array_push(
                $default_rhythm_ids,
                array(
                    'rhythm_id' => $rhythm_extra_id,
                    'version_type_id' => $version_type_id,
                )
            );
        }
        return $default_rhythm_ids;
    }

    /**
     * Insert the site defaults into a single stream.
     *
     * @return void
     */
    public static function insertSiteDefaults($stream_extra_id) {
        $default_rhythm_ids = self::generateDefaults();

        foreach ($default_rhythm_ids as $rhythm_row) {
            self::insertDefault($stream_extra_id, $rhythm_row['rhythm_id'], $rhythm_row['version_type_id']);
        }
    }

    /**
     * Fetches the default rhythm ids for a stream
     *
     * @return array
     */
    public static function getForStream($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     rhythm_extra_id
                    ,version_type
                    ,sort_order
                FROM stream_default_rhythm
                WHERE stream_extra_id = :stream_extra_id
                ORDER BY sort_order";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Duplicate the rows for an existing stream for a new one.
     *
     * @param integer $original_stream_extra_id The extra id of the stream the rows are being copied from.
     * @param integer $new_stream_extra_id Theextra id of the new stream the rows are being copied to.
     *
     * @return void
     */
    public static function defaultForNewRhythm($original_stream_extra_id, $new_stream_extra_id) {
        $sql = "
            SELECT
                 rhythm_extra_id
                ,version_type
                ,sort_order
            FROM stream_default_rhythm
            WHERE stream_extra_id = :stream_extra_id
            ORDER BY sort_order";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $original_stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        foreach ($rows as $row) {
            $new_model = new StreamDefaultRhythm;
            $new_model->stream_extra_id = $new_stream_extra_id;
            $new_model->rhythm_extra_id = $row['rhythm_extra_id'];
            $new_model->version_type = $row['version_type'];
            $new_model->sort_order = $row['sort_order'];
            $new_model->save();
        }
    }

    /**
     * Deletes stream_default_rhythm rows by their stream_extra_id.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is used to delete these rows.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM stream_default_rhythm
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes stream_default_rhythm rows by their top_parent_id.
     *
     * @param integer $top_parent_id The id of the top_parent post in stream_public_rhythm that is being deleted.
     *
     * @return void
     */
    public static function deleteByTopParentId($top_parent_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM stream_public_rhythm
            WHERE top_parent_id = :top_parent_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":top_parent_id", $top_parent_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes stream_default_rhythm rows by their rhythm_extra_id.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm in stream_default_rhythm that is being deleted.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM stream_default_rhythm
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of stream_default_rhythm data for a stream_extra_id.
     *
     * @param type $stream_extra_id The extra id of the stream to select data for.
     *
     * @return array
     */
    public static function getRowsForStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream_default_rhythm
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>