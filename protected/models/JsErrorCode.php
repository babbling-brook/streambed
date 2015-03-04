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
 * Model for the cat DB table.
 * The table holds category or tag names for general use.
 *
 * @package PHP_Models
 */
class JsErrorCode extends CActiveRecord
{

    /**
     * A js error code. Also the primary key
     *
     * @var string
     */
    public $code;


    /**
     * The description for this error code.
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
     * Getter for the tables name.
     *
     * @return string the associated database table name.
     */
    public function tableName() {
        return '{{js_error_code}}';
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
            array('code', 'required'),
            array('code', 'length', 'max' => 255),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array();
    }

    /**
     * Does an error code exist.
     *
     * @param type $error_code The error code to check for.
     *
     * @return boolean
     */
    public static function doesExist($error_code) {
        $query = "
            SELECT code
            FROM js_error_code
            WHERE
                code = :code";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":code", $error_code, PDO::PARAM_STR);
        $value = $command->queryScalar();
        if ($value === $error_code) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Does an error code exist.
     *
     * @param type $error_code The error code to check for.
     *
     * @return boolean
     */
    public static function insertCode($error_code) {
        $model = new JsErrorCode;
        $model->code = $error_code;
        if ($model->save() === false) {
            $errors = ErrorHelper::model($model->getErrors());
            throw new Exception("Error code not inserting into DB " . $error_code . ' : ' . $errors);
        }
    }

}

?>