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
 * Model for the take_kindred DB table.
 * Takes that have been processed by users kindred rhythms
 *
 * @package PHP_Models
 */
class TakeKindred extends CActiveRecord
{

    /**
     * The primary key for this kindred take.
     *
     * @var integer
     */
    public $take_kindred_id;

    /**
     * The date that this kindred take score was created.
     *
     * @var string
     */
    public $date_processed;

    /**
     * The id of the take that has a score against it.
     *
     * @var integer
     */
    public $take_id;

    /**
     * The id of the user that this take score belongs to.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The kindred rhythm that is being used to process takes.
     *
     * May be a partial version
     *      - hence the link to user_rhythm_id rather than rhythm_extra_id.
     *
     * @var integer
     */
    public $user_rhythm_id;

    /**
     * The score that this Rhythm generates.
     *
     * @var integer
     */
    public $score;

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
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function tableName() {
        return 'take_kindred';
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
            array('user_id, user_rhythm_id, score, take_id', 'required'),
            array('user_id, user_rhythm_id, take_id', 'length', 'max' => 10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('take_kindred_id, user_id, rhythm_id, start_date, end_date', 'safe', 'on' => 'search'),
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
            'user' => array(self::BELONGS_TO, 'User', 'scored_user_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'take_kindred_id' => 'Take Kindred',
            'user_id' => 'User',
            'rhythm_id' => 'Rhythm',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
        );
    }

    /**
     * Insert a score for a take against a user as calculated by an Rhythm.
     *
     * @param integer $take_id The id of the take that the Rhythm used to generate this score.
     * @param integer $user_rhythm_id The primary key of the user_rhythm that was used to generate this score.
     * @param integer $this_user_id The id of the user who the score is being recorded for.
     * @param integer $scored_user_id The id of the user who is being scored.
     * @param integer $score The score.
     *
     * @return void
     */
    public static function insertRhythmScore($take_id, $user_rhythm_id, $this_user_id, $scored_user_id, $score) {
        // Fetch any existing result for updating
        $take_kindred_id = self::getScore($take_id, $user_rhythm_id, $this_user_id, $scored_user_id);
        self::insertScore($take_id, $user_rhythm_id, $this_user_id, $scored_user_id, $score, $take_kindred_id);
    }

    /**
     * Gets the primary key for a specific user/that_user/rhythm combination.
     *
     * @param integer $take_id The id of the take that the Rhythm used to generate this score.
     * @param integer $user_rhythm_id The primary key of the user_rhythm that was used to generate this score.
     * @param integer $this_user_id The id of the user who the score is being recorded for.
     * @param integer $scored_user_id The id of the user who is being scored.
     *
     * @return integer|false
     */
    protected static function getScore($take_id, $user_rhythm_id, $this_user_id, $scored_user_id) {
        $sql = "
            SELECT take_kindred_id
            FROM take_kindred
            WHERE
                take_id = :take_id
                AND user_id = :user_id
                AND scored_user_id = :scored_user_id
                AND user_rhythm_id = :user_rhythm_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":take_id", $take_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $this_user_id, PDO::PARAM_INT);
        $command->bindValue(":scored_user_id", $scored_user_id, PDO::PARAM_INT);
        $command->bindValue(":user_rhythm_id", $user_rhythm_id, PDO::PARAM_INT);
        $take_kindred_id = $command->queryScalar();
        return $take_kindred_id;
    }


    /**
     * Insert a kindred rhythm score for a user.
     *
     * @param integer $take_id The id of the take that the Rhythm used to generate this score.
     * @param integer $user_rhythm_id The primary key of the user_rhythm that was used to generate this score.
     * @param integer $this_user_id The id of the user who the score is being recorded for.
     * @param integer $scored_user_id The id of the user who is being scored.
     * @param integer $score The score being inserted.
     * @param integer|false $take_kindred_id The id of the score row if the row is being updated. Otherwise false.
     *
     * @return void
     */
    protected static function insertScore($take_id, $user_rhythm_id, $this_user_id, $scored_user_id,
        $score, $take_kindred_id
    ) {
        $model = new TakeKindred;
        if ($take_kindred_id !== false) {
            $model->take_kindred_id = $take_kindred_id;
            $model->isNewRecord = false;
        }
        $model->take_id = $take_id;
        $model->user_id = $this_user_id;
        $model->user_rhythm_id = $user_rhythm_id;
        $model->score = $score;
        $model->scored_user_id = $scored_user_id;
        if ($model->save() === false) {
            throw new Exception("Error saving take_kindred score" . ErrorHelper::model($model->getErrors()));
        }
    }

    /**
     * Deletes take_kindred rows by their user_rhythm_id.
     *
     * @param integer $user_rhythm_id The id of the user_rhythm whose take_kindred data is being deleted.
     *
     * @return void
     */
    public static function deletebyUserRhythmId($user_rhythm_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM take_kindred
            WHERE user_rhythm_id = :user_rhythm_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_rhythm_id", $user_rhythm_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes take_kindred rows by their user_id.
     *
     * @param integer $user_id The id of the user whose take_kindred data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM take_kindred
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes take_kindred rows by their take_id.
     *
     * @param integer $take_id The id of the take in take_kindred that is being deleted.
     *
     * @return void
     */
    public static function deleteByTakeId($take_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM take_kindred
            WHERE take_id = :take_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":take_id", $take_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of take_kindred data for a user_rhythm_id.
     *
     * @param type $user_rhythm_id The id of the user_rhythm to select data for.
     *
     * @return array
     */
    public static function getRowsForUserRhythmId($user_rhythm_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM take_kindred
                WHERE user_rhythm_id = :user_rhythm_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_rhythm_id", $user_rhythm_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>