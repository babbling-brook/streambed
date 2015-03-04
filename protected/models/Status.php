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
 * Model for the status DB table.
 * The table holds the status for 'extra' tables.
 *
 * @package PHP_Models
 * @fixme merge this into the lookup table.
 */
class Status extends CActiveRecord
{

    /**
     * The primary key of this status.
     *
     * @var integer
     */
    public $status_id;

    /**
     * The value of this status.
     *
     * @var integer
     */
    public $value;

    /**
     * A description of this status type.
     *
     * @var integer
     */
    public $description;

    /**
     * The display order of this status type.
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
        return 'status';
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
            array('value, description', 'required'),
            array('value', 'length', 'max' => 127),
            array('description', 'length', 'max' => 255),
            array('display_order', 'length', 'max' => 10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('status_id, value, description, display_order', 'safe', 'on' => 'search'),
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
            'value' => 'Value',
            'description' => 'Description',
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
        $criteria->compare('status_id', $this->status_id, true);
        $criteria->compare('value', $this->value, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('display_order', $this->display_order, true);
        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
            )
        );
    }
}

?>