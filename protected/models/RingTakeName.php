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
 * Model for the ring_take_name DB table.
 * The table holds the information about takes that can be made in the name of rings by its members.
 *
 * @package PHP_Models
 */
class RingTakeName extends CActiveRecord
{

    /**
     * The primary key of this take name.
     *
     * @var integer
     */
    public $ring_take_name_id;

    /**
     * The id of the ring that this take name belongs to.
     *
     * @var integer
     */
    public $ring_id;

    /**
     * The name that appears on a users ring taken menu for applying this take.
     *
     * @var string
     */
    public $name;

    /**
     * The amount that this field can be taken by.
     *
     * @var integer
     */
    public $amount;

    /**
     * The id of the stream that can be taken. @fixme not sure if this should be stream_extra_id.
     *
     * @var integer
     */
    public $stream_id;

    /**
     * The type id for the version of the stream that can be taken.
     *
     * See version_type in the lookup table for valid options.
     *
     * @var integer
     */
    public $stream_version;

    /**
     * The id of the field in an stream that can be taken.
     *
     * @var integer
     */
    public $field_id;

    /**
     * The stream url.
     *
     * It is inserted and then convert to an id and version string before the row is inserted/updated.
     * This is not a table column.
     *
     * @var string
     */
    public $stream_url;

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
        return 'ring_take_name';
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
            array('ring_id, name, amount', 'required'),
            array('amount, ring_id', 'numerical', 'integerOnly' => true),
            array('ring_id, stream_id', 'length', 'max' => 10),
            array('amount', 'length', 'max' => 10),
            array('name, stream_version', 'length', 'max' => 50),
            array('stream_id', 'ruleStream'),
            array('stream_version', 'ruleVersionValid')
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            //array('ring_take_name_id, ring_id, name, amount', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Ensure that stream is set if stream_version is.
     *
     * @return void
     */
    public function ruleStream() {
        if (empty($this->stream_version) === false && empty($this->stream_id) === true) {
            $this->addError('stream_version', 'Not a valid stream.');
        }
    }

    /**
     * Check that a version string represents a valid version.
     *
     * @return void
     */
    public function ruleVersionValid() {
        if (empty($this->stream_version) === false) {
            if (Version::checkValidLatestVersion($this->stream_version, "/") === false) {
                $this->addError('stream_version', 'Not a valid version.');
            }
        }
    }

    /**
     * Work out $this->stream_id and $this->stream_version from the stream_url before validating.
     *
     * @return boolean
     */
    public function beforeValidate() {
        $stream_id = null;
        $this->stream_version = null;
        if (empty($this->stream_url) === false) {
            $stream_id = StreamBedMulti::getIdFromUrl($this->stream_url);
        }

        if ($stream_id === "url broken") {
            $this->addError('stream', 'Not a valid Stream url.');
        }

        if ($stream_id === false) {
            // @fixme if stream not found then try a remote fetch
            $tmp = 'tmp var so that the empty `if` statment does not fail the code sniffer.';
        } else {
            $this->stream_id = $stream_id;
        }

        if (isset($stream_id) === true) {
            $version = Version::getFromUrl($this->stream_url);
            $this->stream_version = $version;
        }

        return parent::beforeValidate();
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'ring_take_name_id' => 'Ring Take Name',
            'ring_id' => 'Ring',
            'name' => 'Name',
            'amount' => 'Amount',
        );
    }

    /**
     * Get the details of all take names for a ring.
     *
     * @param integer $ring_id The id of the ring to fetch take names for.
     *
     * @return array
     */
    public static function getForRing($ring_id) {
        $sql = "
            SELECT
                 ring_take_name_id
                ,name
                ,amount
                ,stream_id
                ,stream_version
            FROM
                ring_take_name
            WHERE ring_id = :ring_id
            ORDER BY name";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $take_names = $command->queryAll();

        foreach ($take_names as $key => $take) {
            $take_names[$key]['stream_url'] = '';
            if (isset($take_names[$key]['stream_id']) === true) {
                $url = StreamBedMulti::getUrlFromStreamID($take_names[$key]['stream_id']);
                $take_names[$key]['stream_url'] =  $url . "/" . $take_names[$key]['stream_version'];
            }
        }

        return $take_names;
    }

    /**
     * Load the model from primary key.
     *
     * @param integer $ring_take_name_id The id of the model to load.
     *
     * @return RingTakeName
     */
    public static function load($ring_take_name_id) {
        return RingTakeName::model()->findByPk($ring_take_name_id);
    }

    /**
     * Delete a take_name row by its primary key.
     *
     * @param integer $ring_take_name_id The primary key of the row to delete.
     *
     * @return void
     */
    public static function deleteRow($ring_take_name_id) {
        RingTakeName::model()->deleteByPk($ring_take_name_id);
    }

    /**
     * Gets a ring_take_name row from its take name and ring id.
     *
     * @param string $take_name The take name.
     * @param integer $ring_id The id of the ring that owns this take name.
     *
     * @return array
     */
    public static function getFromName($take_name, $ring_id) {
        $sql = "
            SELECT
                 ring_take_name_id
                ,ring_id
                ,name
                ,amount
                ,stream_id
                ,stream_version
            FROM
                ring_take_name
            WHERE
                ring_id = :ring_id
                AND name = :take_name";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":take_name", $take_name, PDO::PARAM_STR);
        $row = $command->queryRow();
        return $row;
    }

    /**
     * Deletes ring_Take_name rows by their ring_id.
     *
     * @param integer $ring_id The id of the ring whose ring_take_name data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM ring_take_name
            WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Updates all instances of a stream_extra_id to NULL.
     *
     * Used when the stream is deleted.
     *
     * @param integer $stream_extra_id The extra id of the stream that is being set to NULL.
     *
     * @return void
     */
    public static function updateStreamExtraIdToNull($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            UPDATE ring_take_name
            SET stream_id = NULL
            WHERE stream_id = :stream_extra_id";    // This is correct. ring_take_name.stream_id is incorrectly named.
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Returns all the ring_take_name_ids for a ring.
     *
     * @param integer $ring_id The id of the ring whose ring_take_name_id data is being fetched.
     *
     * @return void
     */
    public static function getRingTakeNameIds($ring_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT ring_take_name_id
            FROM ring_take_name
            WHERE ring_id = :ring_id";    // This is correct. ring_take_name.stream_id is incorrectly named.
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $ring_take_name_ids = $command->queryColumn();
        return $ring_take_name_ids;
    }


}

?>