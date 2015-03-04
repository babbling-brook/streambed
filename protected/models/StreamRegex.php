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
 * Model for the stream_regex DB table.
 * The table holds the details about default regular expressions that are available to stream text fields.
 *
 * @package PHP_Models
 */
class StreamRegex extends CActiveRecord
{

    /**
     * The primary key of this regular expression.
     *
     * @var integer
     */
    public $stream_regex_id;

    /**
     * A name for the regular expression.
     *
     * @var string
     */
    public $name;

    /**
     * The regular expression.
     *
     * @var string
     */
    public $regex;

    /**
     * The error to display if the text box contents do not pass the regular expression.
     *
     * @var string
     */
    public $error;

    /**
     * The display ofer fo the regular expression.
     *
     * @var integer
     */
    public $display_order;

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
        return '{{stream_regex}}';
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
            array('name, regex, error', 'required'),
            array('display_order', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 127),
            array('regex,error', 'length', 'max' => 255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('stream_regex_id, name, regex, error, display_order', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'stream_regex_id' => 'Stream Regex',
            'name' => 'Name',
            'regex' => 'Regex',
            'error' => 'Error Message',
            'display_order' => 'Display Order',
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

        $criteria->compare('stream_regex_id', $this->stream_regex_id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('regex', $this->regex, true);
        $criteria->compare('error', $this->error, true);
        $criteria->compare('display_order', $this->display_order);

        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
            )
        );
    }

    /**
     * Fetch the error message for a regex string.
     *
     * @param string $regex The regular expression to find an error message for.
     *
     * @return string|boolean The error message or false.
     */
    public static function matchError($regex) {
        $query = "SELECT error FROM stream_regex WHERE regex = :regex";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":regex", $regex, PDO::PARAM_STR);
        $results = $command->queryScalar();
        return $results;
    }

    /**
     * Checks if a regular expression exists.
     *
     * @param string $regex The regular expression to search for.
     *
     * @return boolean
     */
    public static function doesExist($regex) {
        return StreamRegex::model()->exists(
            ":regex=regex",
            array(
                ":regex" => $regex,
            )
        );
    }

    /**
     * Returns the name of a regex if it exists in the table.
     *
     * @param string $regex The regular expression to search for.
     *
     * @return string|boolean The regex name or false.
     */
    public static function getName($regex) {
        $row = StreamRegex::model()->find(
            ":regex=regex",
            array(
                ":regex" => $regex,
            )
        );
        if (isset($row) === true) {
            return $row->name;
        }
        return false;
    }
}

?>