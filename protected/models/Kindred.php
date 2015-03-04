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
 * Model for the kindred DB table.
 * The table Records all the kindred scores for users.
 *
 * @package PHP_Models
 */
class Kindred extends CActiveRecord
{

    /**
     * The primary key.
     *
     * @var integer
     */
    public $kindred_id;

    /**
     * The id of the user whose kindred are indexed.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The id of the kindred user.
     *
     * @var integer
     */
    public $kindred_user_id;

    /**
     * The total score of this kindred relaitonship.
     *
     * @var integer
     */
    public $score;

    /**
     * The rhythm that generated this score. Links to user_rhythm becaus it may be a partial version.
     *
     * @var integer
     */
    public $user_rhythm_id;

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
        return 'tag';
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
            array('user_id, kindred_user_id, score, user_rhythm_id', 'required'),
            array('kindred_id, user_id, kindred_user_id, score, user_rhythm_id', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * Deletes all the totals for a users rhythm
     *
     * @param type $user_id The id of the user whose kindred rhythm has run.
     * @param type $user_rhythm_id The id of the user rhythm that generated the kindred results.
     *
     * @return void
     */
    public static function deleteTotalsForUserRhythm($user_id, $user_rhythm_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE FROM kindred
            WHERE
                user_id = :user_id
                AND user_rhythm_id = :user_rhythm_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":user_rhythm_id", $user_rhythm_id, PDO::PARAM_INT);
        $command->execute();
    }


    /**
     * Get a users kindred data.
     *
     * @param integer $user_id The id of the user we are fetching kindred data for.
     *
     * @return array
     */
    public static function getKindredForUser($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 score
                ,user.username
                ,site.domain
            FROM kindred
                INNER JOIN user ON kindred.kindred_user_id = user.user_id
                INNER JOIN site on user.site_id = site.site_id
            WHERE
                kindred.user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Deletes kindred rows by their user_id
     *
     * @param integer $user_id The id of the user whose kindred data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM kindred
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes kindred rows by their kindred_user_id
     *
     * @param integer $user_id The id of the user whose kindred data for the kindred_user_id is being deleted.
     *
     * @return void
     */
    public static function deleteByKindredUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM kindred
            WHERE kindred_user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes kindred rows by their user_rhythm_id
     *
     * @param integer $user_rhythm_id The id of the user_rhythm in the kindred that is being deleted.
     *
     * @return void
     */
    public static function deleteByUserRhythmId($user_rhythm_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM kindred
            WHERE user_rhythm_id = :user_rhythm_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_rhythm_id", $user_rhythm_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of kindred data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM kindred
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>