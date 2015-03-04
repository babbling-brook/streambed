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
 * Model for the rhythm_cat DB table.
 * The table holds top level information about Rhythms.
 *
 * @package PHP_Models
 */
class RhythmCat extends CActiveRecord
{

    /**
     * The primary key for this Rhythm category.
     *
     * @var integer
     */
    public $rhythm_cat_id;

    /**
     * The name of this Rhythm category.
     *
     * @var string
     */
    public $name;

    /**
     * The description of this Rhythm category.
     *
     * @var string
     */
    public $description;

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
     * Fetch the associated database table name.
     *
     * @return string
     */
    public function tableName() {
        return 'rhythm_cat';
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
            array('rhythm_cat_id, name', 'required'),
            array('rhythm_cat_id', 'length', 'max' => 10),
            array('name', 'length', 'max' => 50),
            array('description', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('rhythm_cat_id, name, description', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'rhythm_cat_id' => 'Rhythm Cat',
            'name' => 'Name',
            'description' => 'Description',
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

        $criteria->compare('rhythm_cat_id', $this->rhythm_cat_id, true);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);

        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
            )
        );
    }

    /**
     * Fetches the id of a rhythm category.
     *
     * @param string $name The name of the rhythm category.
     *
     * @return number|false The rhythm_cat_id or false.
     */
    public static function getRhythmCatID($name) {
        $sql = '
                SELECT rhythm_cat_id
                FROM rhythm_cat
                WHERE name = :name';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':name', $name, PDO::PARAM_STR);
        $rhythm_cat_id = $command->queryScalar();
        return $rhythm_cat_id;
    }

    /**
     * Fetches the category name for a rhythm_cat_id.
     *
     * @param integer $rhythm_cat_id The id of the category to fetch.
     *
     * @return string.
     */
    public static function getCategoryFromID($rhythm_cat_id) {
        $sql = '
                SELECT name
                FROM rhythm_cat
                WHERE rhythm_cat_id = :rhythm_cat_id';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':rhythm_cat_id', $rhythm_cat_id, PDO::PARAM_STR);
        $category = $command->queryScalar();
        return $category;
    }

}

?>