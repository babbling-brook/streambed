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
 * Model for the rhythm_extra DB table.
 * The table holds the information for each version of an Rhythm.
 *
 * @package PHP_Models
 */
class RhythmExtra extends CActiveRecord
{

    /**
     * The primary key of the Rhythm.
     *
     * @var integer
     */
    public $rhythm_extra_id;

    /**
     * The primary key of the Rhythms top level table.
     *
     * @var integer
     */
    public $rhythm_id;

    /**
     * The creation date for this version of the slgorithm.
     *
     * @var string
     */
    public $date_created;

    /**
     * The description of this Rhythm.
     *
     * @var string
     */
    public $description;

    /**
     * The minified version of the Rhythm.
     *
     * @var string
     */
    public $mini;

    /**
     * The full unminified version of the Rhythm.
     *
     * @var integer
     */
    public $full;

    /**
     * A primary key of the status table that points to this Rhythms status.
     *
     * @var integer
     */
    public $status_id;

    /**
     * The primary key of the version table row holding the version information about this Rhythm.
     *
     * @var integer
     */
    public $version_id;

    /**
     * The primary key of the rhythm_cat table that points to this Rhythms category.
     *
     * @var integer
     */
    public $rhythm_cat_id;

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
        return 'rhythm_extra';
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
            array('description, full, rhythm_cat_id', 'required'),
            array('mini, full', 'length', 'max' => 65535),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array(
                'rhythm_extra_id, rhythm_id, description, mini, full, status_id, version_id, rhythm_cat_id',
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
        return array(
            'rhythm' => array(self::BELONGS_TO, 'Rhythm', 'rhythm_id', 'joinType' => 'INNER JOIN'),
            'rhythm_cat' => array(self::BELONGS_TO, 'RhythmCat', 'rhythm_cat_id', 'joinType' => 'INNER JOIN'),
            'version' => array(self::BELONGS_TO, 'Version', 'version_id', 'joinType' => 'INNER JOIN'),
            'status' => array(self::BELONGS_TO, 'Status', 'status_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'status_id' => 'Status',
            'version_id' => 'Version',
            'rhythm_cat_id' => 'Category',
            'date_created' => 'Date Created',
            'description' => 'Description',
            'status_id' => 'Status',
            'full' => 'Javascript code for the Rhythm',
        );
    }

    /**
     * Fetch the base rhythm_extra_id for an rhythm url that might include 'latest' versions.
     *
     * @param type $url  The Rhythm url.
     *
     * @return integer The rhythm_extra_id of the Rhythm.
     */
    public static function getBaseIDFromUrl($url) {

        $version = Version::replaceLatest($url);

    }

    /**
     * Update the post mode for an Rhythm.
     *
     * @param integer $rhythm_extra_id The extra id of the Rhythm whose meta_post_id is being updated.
     * @param integer $meta_post_id The post id that is used as a meta post for this rhythm_extra_id.
     *
     * @return boolean Was the operation successful.
     */
    public static function updateMetaPostId($rhythm_extra_id, $meta_post_id) {
        $query = "
            UPDATE rhythm_extra
            SET meta_post_id = :meta_post_id
            WHERE rhythm_extra_id = :rhythm_extra_id";

        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->bindValue(":meta_post_id", $meta_post_id, PDO::PARAM_INT);
        $row_count = $command->execute();
        if ($row_count === 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *  Fetch the meta post id for a rhythm.
     *
     * @param integer $rhythm_extra_id The extra id of the Rhythm whose meta_post_id is being fetched.
     *
     * @return integer|false The meta post id or false.
     */
    public static function getMetaPostId($rhythm_extra_id) {
        $query = "
            SELECT meta_post_id
            FROM rhythm_extra
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $meta_post_id = $command->queryScalar();
        return $meta_post_id;
    }

    /**
     * Get the current status of a rhythm.
     *
     * @param integer $rhythm_extra_id The status of a rhythm.
     *
     * @return string The status of the rhythm.
     */
    public static function getStatus($rhythm_extra_id) {
        $query = "
            SELECT status_id
            FROM rhythm_extra
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $status_id = $command->queryScalar();
        $status = StatusHelper::getValue($status_id);
        return $status;
    }

    /**
     * Get the user_id of the owner of this rhythm.
     *
     * @param integer $rhythm_extra_id
     *
     * @return integer
     */
    public static function getOwnerID($rhythm_extra_id) {
        $query = "
            SELECT rhythm.user_id
            FROM rhythm_extra
                INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $user_id = $command->queryScalar();
        return $user_id;
    }

    /**
     * Fetches the description for a rhythm.
     *
     * @param Integer $rhythm_extra_id The extra id of the rhythm to fetch a description for.
     *
     * @return String The description.
     */
    public static function getDescription($rhythm_extra_id) {
        $query = "
            SELECT description
            FROM rhythm_extra
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $description = $command->queryScalar();
        return $description;
    }

    /**
     * Fetch all the rhythm_extra rows for the given rhythm_id.
     *
     * @param type rhythm_id The id of the rhythm that rhythm_extra_ids are being fetched for.
     *
     * @return array
     */
    public static function getIdsForRhythmId($rhythm_id) {
        $query = "
            SELECT rhythm_extra_id
            FROM rhythm_extra
            WHERE rhythm_id = :rhythm_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_id", $rhythm_id, PDO::PARAM_INT);
        $rhythm_extra_ids = $command->queryColumn();
        return $rhythm_extra_ids;
    }

    /**
     * Delete all rhythm_extra rows by their rhythm_id
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $rhythm_id The id of the rhythm used to delete rhythm_extra rows.
     *
     * @return void
     */
    public static function deleteByRhythmId($rhythm_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM rhythm_extra
                WHERE rhythm_id = :rhythm_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":rhythm_id", $rhythm_id, PDO::PARAM_INT);
            $command->execute();
        } catch (Exception $e) {
            throw new Exception(
                'RhythmExtra::deleteByRhythmId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Delete a rhythm_extra row by its rhythm_extra_id
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm used to delete a rhythm_extra row.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM rhythm_extra
                WHERE rhythm_extra_id = :rhythm_extra_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
            $command->execute();
        } catch (Exception $e) {
            throw new Exception(
                'RhythmExtra::deleteByRhythmExtraId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Sets the meta_post of all rhythm_extra rows with a given post_id to NULL.
     *
     * @param $post_id The id of the post whose meta_post_id is being set to NULL.
     *
     * @return void
     */
    public static function setMetaPostToNullForPostId($post_id) {
        $sql = "
            UPDATE rhythm_extra
            SET meta_post_id = NULL
            WHERE meta_post_id = :post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Gets a rhtyhms id from its extra id.
     *
     * @param $rhythm_extra_id The extra id of the rhythm whoose id is being fetched.
     *
     * @return integer The rhtyh_id.
     */
    public static function getRhythmIdFromRhythmExtraId($rhythm_extra_id) {
        $sql = "
            SELECT rhythm_id
            FROM rhythm_extra
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $rhythm_id = $command->queryScalar();
        return $rhythm_id;
    }

    /**
     * Check if there are any rhythm_extra rows for a rhythm.
     *
     * @param $rhythm_id The id of the rhythm that may have some child rhythm_extra_rows.
     *
     * @return integer The rhtyh_id.
     */
    public static function doAnyExistForRhythmId($rhythm_id) {
        $sql = "
            SELECT rhythm_extra_id
            FROM rhythm_extra
            WHERE rhythm_id = :rhythm_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":rhythm_id", $rhythm_id, PDO::PARAM_INT);
        $rhythm_extra_id = $command->querySCalar();
        if ($rhythm_extra_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Select rows of rhythm_extra data for a user.
     *
     * @param type $rhythm_id The id of the rhythm to select data for.
     *
     * @return array
     */
    public static function getRowsForRhythmId($rhythm_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM rhythm_extra
                WHERE rhythm_id = :rhythm_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_id", $rhythm_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>