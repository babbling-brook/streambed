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
 * Model for the js_error DB table.
 * The table logs errors reported by javascript.
 *
 * @package PHP_Models
 */
class JsError extends CActiveRecord
{
    /**
     * The primary key of the error report.
     *
     * @var integer
     */
    public $js_error_id;

    /**
     * The creation date and time of the error.
     *
     * @var string
     */
    public $create_date;

    /**
     * The id of the location type of the error. See js_error.location in the lookup table for valid error types.
     *
     * @var integer
     */
    public $location;

    /**
     * The code for the type of error. see js_error_codes table for options.
     *
     * @var integer
     */
    public $type;

    /**
     * The error message that is being recorded.
     *
     * @var string
     */
    public $message;

    /**
     * Data associated with the error in JSON format.
     *
     * @var string
     */
    public $data;

    /**
     * The name of the location.
     *
     * This is not a table column.
     *
     * @var string.
     */
    public $location_name;

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
        return 'js_error';
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
            array('location, type,', 'required'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('create_date, location, type, message, data', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'create_date' => 'Create Date',
            'location' => 'Location',
            'type' => 'Error Code',
            'message' => 'Message',
            'data' => 'Data',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria=new CDbCriteria;
        $criteria->compare('create_date', $this->create_date, true);
        $criteria->compare('location', $this->location);
        $criteria->compare('type', $this->type);
        $criteria->compare('message', $this->message, true);
        $criteria->compare('data', $this->data, true);

        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
            )
        );
    }

    /**
     * Lookup and insert the type and location ids before validating.
     *
     * @return boolean
     */
    public function beforeValidate() {
        $this->location = LookupHelper::getID("js_error.location", $this->location_name);
        if (JsErrorCode::doesExist($_POST['type']) === false) {
            JsErrorCode::insertCode($_POST['type']);
        }
        return true;
    }

    /**
     * Delete old log data.
     *
     * Run this randomly once every 36000 requests -
     * This is acheived by checking the current second in this hour, is equal to a random number.
     * This allows error logs to be truncated regularly if the errors mount up,
     * but without a call every time a request is made.
     *
     * @return void
     */
    public function afterSave() {
        $second = date("s");
        $minute = date("i");
        $second_in_hour = $second * $minute;
        $rand_time = rand(0, 34810); // 3481 = 59 * 59 * 10
        if ($second_in_hour === $rand_time) {
            $connection = Yii::app()->db;
            $sql = "DELETE
                    FROM js_error
                    WHERE create_date < '" . date("Y-m-d H:i:s", time() - Yii::app()->params['delete_logs_time']) . "'";
            $command = $connection->createCommand($sql);
            $command->execute();
        }
    }
}

?>