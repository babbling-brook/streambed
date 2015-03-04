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
 * Model for the lookup DB table.
 * This table assigns primary keys to commonly used strings that are used in other parts of the project.
 *
 * @package PHP_Models
 */
class Lookup extends CActiveRecord
{

    /**
     * The primary key of this lookup item.
     *
     * @var integer
     */
    public $lookup_id;

    /**
     * The column name of the thing that is being looked up.
     *
     * This is the format table_name.column_name.
     * If a general string is being stored rather than a link
     * to a table column then use lowercase_underscore format.
     *
     * @var string
     */
    public $column_name;

    /**
     * The string that is being stored.
     *
     * By convention the string being stored is in lowercase underscore format.
     *
     * @var string
     */
    public $value;

    /**
     * The description of this lookup item.
     *
     * @var integer
     */
    public $description;

    /**
     * The sort order for this column name.
     *
     * Defaults to 0
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
        return 'lookup';
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
            array('column_name, value', 'required'),
            array('sort_order', 'numerical', 'integerOnly' => true),
            array('column_name, value', 'length', 'max' => 127),
            array('description', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('lookup_id, column_name, value, description, sort_order', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'lookup_id' => 'Lookup',
            'column_name' => 'Column Name',
            'value' => 'Value',
            'description' => 'Description',
            'sort_order' => 'Sort Order',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria=new CDbCriteria;
        $criteria->compare('lookup_id', $this->lookup_id);
        $criteria->compare('column_name', $this->column, true);
        $criteria->compare('value', $this->value, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('sort_order', $this->sort_order);

        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
            )
        );
    }

    /**
     * Check if an id is a valid column type.
     *
     * @param string $column Name of the column to lookup in format tablename_columnname.
     * @param integer $lookup_id Primary key of Lookup model.
     *
     * @return boolean
     */
    public static function checkIDValid($column, $lookup_id) {
        return Lookup::model()->exists(
            array(
                "condition" => "column_name=:column_name AND lookup_id=:lookup_id",
                "params" => array(
                    ":column_name" => $column,
                    ":lookup_id" => $lookup_id,
                )
            )
        );
    }
}

?>