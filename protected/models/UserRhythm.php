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
 * Model for the user_rhythm DB table.
 * The table links Rhythms to the users that are subscribed to them.
 *
 * @package PHP_Models
 */
class UserRhythm extends CActiveRecord
{

    /**
     * The primary key of the user Rhythm table.
     *
     * @var integer
     */
    public $user_rhythm_id;

    /**
     * The user who owns this Rhythm.
     *
     * @var integer
     */
    public $user_id;


    /**
     * The extra id of the Rhythm this user is subscribed to.
     *
     * @var integer
     */
    public $rhythm_extra_id;

    /**
     * The version type of the linked Rhythm.
     *
     * See version_type in the lookup table for valid options.
     *
     * @var integer
     */
    public $version_type;

    /**
     * The use and display order of this Rhythm for this user.
     *
     * @var integer
     */
    public $order;

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
        return 'user_rhythm';
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
            array('user_id, rhythm_extra_id, version_type, order', 'required'),
            array('user_id, rhythm_extra_id, version_type, order', 'length', 'max' => 10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('user_rhythm_id, user_id, rhythm_extra_id, version_type, order', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'rhythm_extra' => array(self::BELONGS_TO, 'RhythmExtra', 'rhythm_extra_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_rhythm_id' => 'User Rhythm',
            'user_id' => 'User',
            'rhythm_extra_id' => 'Rhythm Extra',
            'version_type' => 'Version Type',
            'order' => 'Order',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('user_rhythm_id', $this->user_rhythm_id, true);
        $criteria->compare('user_id', $this->user_id, true);
        $criteria->compare('rhythm_extra_id', $this->rhythm_extra_id, true);
        $criteria->compare('version_type', $this->version_type, true);
        $criteria->compare('order', $this->order, true);

        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
            )
        );
    }

    /**
     * Fetch the primary key from an rhythm_extra_id and the version type.
     *
     * @param integer $rhythm_extra_id The extra id of the Rhythm we are searching for.
     * @param integer $version_type The id of the version_type that the rhythm uses.
     * @param integer $user_id The id of the user we are fetching an rhythm for.
     *
     * @return integer|boolean user_rhythm_id or false
     */
    public static function getUserRhythmId($rhythm_extra_id, $version_type, $user_id) {
        $query = "
            SELECT user_rhythm_id
            FROM user_rhythm
            WHERE
                rhythm_extra_id = :rhythm_extra_id
                AND version_type = :version_type
                AND user_id = :user_id";

        $command = Yii::app()->db->createCommand($query);

        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->bindValue(":version_type", $version_type, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_rhythm_id = $command->queryScalar();
        return $user_rhythm_id;
    }

    /**
     * Updates a users kindred rhythm in this table.
     *
     * @param string $rhythm_url The full url of the rhythm to update
     * @param integer $user_id The id of the user we are fetching an rhythm for.
     *
     * @return void
     */
    public function updateKindredRhythmFromUrl($rhythm_url, $user_id) {
        $rhythm_extra_id = Rhythm::getIdFromUrl($rhythm_url);
        if ($rhythm_extra_id === false) {
            throw new Exception('Rhtyhm url is not found. ' . $rhythm_url);
        }
        if (is_numeric($rhythm_extra_id) === false) {
            throw new Exception('Rhtyhm url is not valid. ' . $rhythm_url);
        }

        $version_type_id = Version::getTypeId(Version::getFromUrl($rhythm_url));

        $model = UserRhythm::model()->find(
            array(
                'condition' => 'user_id=:user_id',
                'params' => array(
                    ':user_id' => $user_id,
                )
            )
        );

        if (is_null($model) === true) {
            throw new Exception('user_rhythm kindred_rhtyhm does not exist for user. ' . $user_id);
        }

        $model->rhythm_extra_id = $rhythm_extra_id;
        $model->version_type = $version_type_id;
        if ($model->save() === false) {
            throw new Exception('Failed to save new user_rhtyhm for user. ' . $rhythm_url . ' ' . $user_id);
        }

    }

    /**
     * Create a default kindred rhythm row for a new user.
     *
     * @param integer $user_id The users id.
     *
     * @return void
     */
    public static function createDefaultKindredRhyhtm($user_id) {
        $rhythm_url = UserConfigDefault::getValueFromCode('kindred_rhythm_url');
        $rhythm_extra_id = Rhythm::getIdFromUrl($rhythm_url);
        $version_type_id = Version::getTypeId(Version::getFromUrl($rhythm_url));

        $model = new UserRhythm;
        $model->user_id = $user_id;
        $model->rhythm_extra_id = $rhythm_extra_id;
        $model->version_type = $version_type_id;
        $model->order = 1;
        $model->save();
    }

    /**
     * Resets a user kindred rhythm to the default.
     *
     * @param integer $user_id The users id.
     *
     * @return void
     */
    public function resetKindredRhythm($user_id) {
        $rhythm_url = UserConfigDefault::getValueFromCode('kindred_rhythm_url');
        $rhythm_extra_id = Rhythm::getIdFromUrl($rhythm_url);
        $version_type_id = Version::getTypeId(Version::getFromUrl($rhythm_url));

        $model = UserRhythm::model()->find(
            array(
                'condition' => 'user_id=:user_id',
                'params' => array(
                    ':user_id' => $user_id,
                )
            )
        );
        $model->rhythm_extra_id = $rhythm_extra_id;
        $model->version_type = $version_type_id;
        $model->save();
    }

    /**
     * Fetch all the user_rhythm_id rows for the given user_id.
     *
     * @param type $user_id The id of the user that user_rhythm ids are being fetched for.
     *
     * @return array An array of rows each containing an array with a single user_rhythm_id row.
     */
    public static function getIdsForUser($user_id) {
        $query = "
            SELECT user_rhythm_id
            FROM user_rhythm
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_rhythm_ids = $command->queryColumn();
        return $user_rhythm_ids;
    }

    /**
     * Fetch all the user_rhythm_id rows for the given rhythm_extra_id.
     *
     * @param type $rhythm_extra_id The extra id of the rhythm that user_rhythm ids are being fetched for.
     *
     * @return array
     */
    public static function getIdsForRhythmExtraId($rhythm_extra_id) {
        $query = "
            SELECT user_rhythm_id
            FROM user_rhythm
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $user_rhythm_ids = $command->queryColumn();
        return $user_rhythm_ids;
    }

    /**
     * Delete all user_rhythm rows by their rhythm_extra_id
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $rhythm_extra_id The extra id of the user_rhythm to delete.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM user_rhythm
                WHERE rhythm_extra_id = :rhythm_extra_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
            $command->execute();

        } catch (Exception $e) {
            throw new Exception(
                'UserRhythm::deleteByRhythmExtraId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Delete all user_rhythm rows by their user_id
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $user_id The  id of the user to delete user_rhythm rows for.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM user_rhythm
                WHERE user_id = :user_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            $command->execute();

        } catch (Exception $e) {
            throw new Exception(
                'UserRhythm::deleteByUserId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Select a row of user_rhythm data.
     *
     * @param type $user_rhythm_id The id of the row to select.
     *
     * @return array. Indexed by column name.
     */
    public static function getRow($user_rhythm_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_rhythm
                WHERE user_rhythm_id = :user_rhythm_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_rhythm_id", $user_rhythm_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        return $row;
    }


    /**
     * Select a row of user_rhythm data.
     *
     * @param type $user_rhythm_id The id of the row to select.
     *
     * @return array. Indexed by column name.
     */
    public static function getRhythmNameArray($user_rhythm_id) {
        $row = UserRhythm::getRow($user_rhythm_id);

        $rhythm = RhythmMulti::getRhythmNameArray($row['rhythm_extra_id']);
        $rhythm['version'] = Version::makeVersionFromVersionTypeIdAndVersionArray(
            $row['version_type'],
            $rhythm['version']
        );
        return $rhythm;
    }

    /**
     * Select rows of user_rhythm data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_rhythm
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }


}

?>