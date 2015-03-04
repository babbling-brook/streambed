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
 * Model for the thing DB table in the log database.
 * The names of things that are being logged.
 * Includes action names, paramater names, paramater contents
 * If the content is too long, then it will be truncated.
 *
 * @package PHP_Models
 */
class Thing extends CActiveRecord
{

    /**
     * The primary key of the thing table.
     *
     * @var integer
     */
    public $thing_id;

    /**
     * The content of this thing.
     *
     * @var string
     */
    public $content;

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
        return '{{thing}}';
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
            array('content', 'required'),
            array('content', 'length', 'max' => 100),
        );
    }

    /**
     * Fetches the id for a thing from its content.
     *
     * @param $content The content held in this thing.
     *
     * @returns integer|false The thing_id or false.
     */
    public static function getThingId($content) {
        if (Yii::app()->params['log_db_on'] === false) {
            return;
        }
        // If the content is to large for the table then it is truncated.
        $content = substr($content, 0, 100);
        $sql = "
            SELECT thing_id
            FROM thing
            WHERE content = :content";
        $command = Yii::app()->dblog->createCommand($sql);
        $command->bindValue(":content", $content, PDO::PARAM_STR);
        $thing_id = $command->queryScalar();
        return $thing_id;
    }

    /**
     * Insert a new thing and return its id
     *
     * @param $content The content held in this thing.
     *
     * @returns integer|false The thing_id or false.
     */
    public static function insertThing($content) {
        if (Yii::app()->params['log_db_on'] === false) {
            return;
        }
        $thing_id = self::getThingId($content);
        if ($thing_id !== false) {
            return $thing_id;
        }

        // If the content is to large for the table then it is truncated.
        $content = substr($content, 0, 100);
        $sql = "
            INSERT INTO thing
            (content)
            VALUES
            (:content)";
        $command = Yii::app()->dblog->createCommand($sql);
        $command->bindValue(":content", $content, PDO::PARAM_STR);
        $command->execute();
        $new_thing_id = Yii::app()->dblog->getLastInsertId();
        return $new_thing_id;
    }

}

?>