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
 * Model for the rhythm_param DB table.
 * The table holds category or tag names for general use.
 *
 * @package PHP_Models
 */
class RhythmParam extends CActiveRecord
{

    /**
     * The primary key of this table.
     *
     * @var integer
     */
    public $rhythm_param_id;

    /**
     * The extra id of the rhythm that this parameter belongs to.
     *
     * @var integer
     */
    public $rhythm_extra_id;

    /**
     * The name of this parameter.
     *
     * @var string
     */
    public $name;

    /**
     * The hint text for this parameter.
     *
     * @var string
     */
    public $hint;

    /**
     * The display order of this parameter in this rhythm.
     *
     * @var string
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
        return '{{rhythm_param}}';
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
            array('rhythm_extra_id, name, display_order', 'required'),
            array('name', 'length', 'max' => 63),
            array(
                'name',
                'match',
                'pattern' => '/^[a-z0-9](?:\x20?[a-z0-9])*$/',
                'message' => 'Parameter name can only contain lower case letters, digits 0 to 9 and spaces.'
                       . 'It cannot start or end with a space and double spaces are not allowed.',
            ),
            array('rhythm_param_id, rhythm_extra_id, display_order', 'numerical', 'integerOnly' => true),
            array('rhythm_param_id, rhythm_extra_id, display_order', 'length', 'max' => 10),
            array('name', 'ruleIsNameUnique'),
        );
    }

    /**
     * Checks if the membership type is valid.
     *
     * @return void
     */
    public function ruleIsNameUnique() {
        $sql_not_this_one = '';
        if (empty($this->rhythm_param_id) === false) {
            $sql_not_this_one = 'AND rhythm_param_id != :rhythm_param_id';
        }
        $sql = "
            SELECT rhythm_param_id
            FROM rhythm_param
            WHERE rhythm_extra_id = :rhythm_extra_id
            AND name = :name
            " . $sql_not_this_one;
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $this->rhythm_extra_id, PDO::PARAM_INT);
        $command->bindValue(":name", $this->name, PDO::PARAM_STR);
        if (empty($this->rhythm_param_id) === false) {
            $command->bindValue(":rhythm_param_id", $this->rhythm_param_id, PDO::PARAM_INT);
        }
        $rhythm_param_id = $command->queryScalar();
        if ($rhythm_param_id !== false) {
            $this->addError('name', 'This paramater name already exists for this rhythm.');
        }
    }

    /**
     * Fetch an array of parameters for a rhythm.
     *
     * @param type $rhythm_extra_id The extra id of the rhythm to fetch parameters for.
     *
     * @return array
     */
    public static function getForRhythm($rhythm_extra_id) {
        $sql = "
            SELECT
                rhythm_param_id
               ,rhythm_extra_id
               ,name
               ,hint
               ,display_order
            FROM rhythm_param
            WHERE rhythm_extra_id = :rhythm_extra_id
            ORDER BY display_order";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }


    /**
     * Fetch an array of parameters for sending to the client for a filter rhythm.
     *
     * @param type $rhythm_extra_id The extra id of the rhythm to fetch parameters for.
     *
     * @return array
     */
    public static function getForFilter($rhythm_extra_id) {
        $sql = "
            SELECT
                name
               ,hint
            FROM rhythm_param
            WHERE rhythm_extra_id = :rhythm_extra_id
            ORDER BY display_order";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Fetches the next display order to use for a rhythm.
     *
     * @param type $rhythm_extra_id The extra id of the rhythm to fetch the next display order for.
     *
     * @return array
     */
    public static function getNextDisplayOrder($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT display_order
                FROM rhythm_param
                WHERE rhythm_extra_id = :rhythm_extra_id
                ORDER BY display_order DESC
                LIMIT 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $last_display_order = $command->queryScalar();
        if ($last_display_order === false) {
            $last_display_order = 1;
        } else {
            ++$last_display_order;
        }
        return $last_display_order;
    }

    /**
     * Removes a paramater by its name.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm to fetch the next display order for.
     * @param string $name the name of the paramater to remove.
     *
     * @return array
     */
    public static function removeParameterByName($rhythm_extra_id, $name) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM rhythm_param
                WHERE
                    rhythm_extra_id = :rhythm_extra_id
                    AND name = :name";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->bindValue(":name", $name, PDO::PARAM_STR);
        $command->execute();
    }

    /**
     * Updates a paramater by its name.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm to fetch the next display order for.
     * @param string $name The name of the parameter to update.
     * @param string $new_name The new name for the parameter that is being updated.
     * @param string $hint The new hint for the paramater that is being updated.
     *
     * @return true|array An array of error objects is returned if the row does not save.
     */
    public static function updateParameterByName($rhythm_extra_id, $name, $new_name, $hint) {
        $param_row = RhythmParam::model()->find(
            array(
                'condition' => 'name=:name AND rhythm_extra_id=:rhythm_extra_id',
                'params' => array(
                    ':name' => $name,
                    ':rhythm_extra_id' => $rhythm_extra_id,
                )
            )
        );
        $param_row->name = $new_name;
        $param_row->hint = $hint;
        if ($param_row->save() === false) {
            return $param_row->getErrors();
        } else {
            return true;
        }
    }

    /**
     * Inserts a new paramaeter for a rhythm.
     *
     * @param integer $original_rhythm_extra_id The extra id of the rhythm that has been duplicated.
     * @param integer $new_rhythm_extra_id The extra id of the new rhythm.
     *
     * @return true|array true if successful or an array of error objects.
     */
    public static function createParameter($rhythm_extra_id, $name, $hint) {
        $rhythm_param = new RhythmParam;
        $rhythm_param->rhythm_extra_id = $rhythm_extra_id;
        $rhythm_param->name = $name;
        $rhythm_param->hint = $hint;
        $display_order = self::getNextDisplayOrder($rhythm_extra_id);
        $rhythm_param->display_order = $display_order;
        if ($rhythm_param->save() === false) {
            return $rhythm_param->getErrors();
        } else {
            return true;
        }
    }

    /**
     * Duplicates the paramaeters for a new rhythm.
     *
     * @param integer $original_rhythm_extra_id The extra id of the rhythm that has been duplicated.
     * @param integer $new_rhythm_extra_id The extra id of the new rhythm.
     *
     * @return void
     */
    public static function duplicateParams($original_rhythm_extra_id, $new_rhythm_extra_id) {
        $params = self::getForRhythm($original_rhythm_extra_id);
        foreach ($params as $param) {
            self::createParameter($new_rhythm_extra_id, $param['name'], $param['hint']);
        }
    }

    /**
     * Deletes rhythm_param rows by their rhythm_extra_id.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm in rhythm_param that is being deleted.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM rhythm_param
            WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of rhythm_extra data for a rhythm_extra_id.
     *
     * @param type $rhythm_extra_id The extra id of the rhythm to select data for.
     *
     * @return array
     */
    public static function getRowsForUserRhythmExtraId($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM rhythm_param
                WHERE rhythm_extra_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }



}

?>