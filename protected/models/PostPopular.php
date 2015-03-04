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
 * Model for the post_popular DB table.
 * Records global popularity scores for an post.
 *
 * @package PHP_Models
 */
class PostPopular extends CActiveRecord
{

    /**
     * The primary key for this table.
     *
     * @var integer
     */
    public $post_popular_id;

    /**
     * The id of the post that a score is being recorded for.
     *
     * @var integer
     */
    public $post_id;

    /**
     * The type of popularity score. See post_popular.type in the lookup table for options.
     *
     * @var integer
     */
    public $type;

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
        return 'post_popular';
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
            array('post_id, type', 'required'),
            array('type', 'numerical', 'integerOnly' => true),
            array('post_id', 'length', 'max' => 10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('post_popular_id, post_id, type', 'safe', 'on' => 'search'),
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
            'post' => array(self::HAS_MANY, 'Post', 'post_popular_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'post_popular_id' => 'Post Popular',
            'post_id' => 'Post',
            'type' => 'Type',
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
        $criteria->compare('post_popular_id', $this->post_popular_id, true);
        $criteria->compare('post_id', $this->post_id, true);
        $criteria->compare('type', $this->type);
        return new CActiveDataProvider(
            get_class($this), array(
                'criteria' => $criteria,
            )
        );
    }

    /**
     * Remove a post from all public results.
     *
     * @param integer $post_id The id of the post to delete.
     *
     * @return void
     */
    public static function deletePost($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post_popular
            WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }
}

?>