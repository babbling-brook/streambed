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
 * Model for the user_feature_usage DB table.
 * The table is a log of which features are being used by users.
 *
 * @package PHP_Models
 */
class UserFeatureUsage extends CActiveRecord
{

    /**
     * The primary key of the feature usage table. table.
     *
     * @var integer
     */
    public $user_feature_usage_id;

    /**
     * The date this feature was used.
     *
     * @var string
     */
    public $date_used;

    /**
     * The id of the user who has used a feature.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The number of times this feature has been used.
     *
     * @var integer
     */
    public $qty;

    /**
     * The id of the feature being used. See user_feature_useage.feature in the lookup table for options.
     *
     * @var integer
     */
    public $feature;

    /**
     * The extra id of the feature being used. E.G. rhythm_extra_id or stream_extra_id.
     *
     * @var integer
     */
    public $extra_id;

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
        return 'user_feature_usage';
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
            array('user_id, feature, extra_id, date_used, qty', 'required'),
            array('user_id, feature, extra_id, qty', 'length', 'max' => 10),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_feature_usage_id' => 'User Feature Usage',
            'user_id' => 'User',
            'feature' => 'Feature',
            'site_id' => 'Site',
            'feature_user_id' => 'Feature User',
            'extra_id' => 'Extra',
        );
    }

    /**
     * Deletes user_feature_usage rows by their user_id
     *
     * @param integer $user_id The id of the user whose feature usage records are being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_feature_usage
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Insert Feature usage for a user.
     *
     * @param integer $user_id The id of the user who has used a feature.
     * @param UserFeatureUsage $fmodel Model containing the (already validated) feature usage data to insert.
     *
     * @return boolean true if method completes.
     */
    public static function insertRowsByUserId($user_id, $fmodel) {
        $features = $fmodel->getFeatures();
        foreach ($features as $feature) {
            $model = new UserFeatureUsage;
            $model->date_used = $fmodel->date;
            $model->user_id = $user_id;
            $model->qty = $feature['qty'];
            $model->feature = $feature['feature'];
            $model->extra_id = $feature['extra_id'];

            if ($model->save() === false) {
                throw new Exception("UserFeatureUsage data not validating : " . print_r($fmodel->getErrors(), true));
            }
        }
        return true;
    }

    /**
     * Select rows of user_feature_usage data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_feature_usage
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>