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
 * Model for the test_data DB table.
 * Stores the data for tests to complete successfully
 *
 * @package PHP_Models
 */
class TestData extends CActiveRecord
{

    /**
     * The primary key of the test data.
     *
     * @var integer
     */
    public $test_data_id;

    /**
     * The bame of the table that the test data will be applied to.
     *
     * @var string
     */
    public $table;

    /**
     * A row of test data or a sql command.
     *
     * Each column is represent by an object with three paramaters,
     * The first of which is always either the primary key or a 'delete' : true.
     * 'name' = the column name.
     * 'value' = the rows value.
     * 'string' = a boolean. true = the value should be quoted.
     * When delete is set to true then all rows in the table are
     * deleted that match the critereia given.
     *
     * @var string
     */
    public $row;

    /**
     * The order in which the test data rows are replaced. 0 = off. 1 = first.
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
        return 'test_data';
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
            array('table, row', 'required'),
            array('table', 'length', 'max' => 255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('test_data_id, table, row', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'test_data_id' => 'Test Data',
            'table' => 'Table',
            'row' => 'Row',
            'display_order' => 'Display Order',
        );
    }

    /**
     * Deletes all the test data.
     *
     * This should always be run on the live site after test have been run.
     * Note that the test user table is not deleted as it is necessary to
     * log in as a system account to restore the test data and testing would take a lot longer
     * if having to repeatedly change to another system account to login and restore the test data.
     *
     * @return void
     */
    public static function deleteTestData() {
        self::restore(array('delete' => true));
    }

    /**
     * Restore all the test data.
     *
     * There are two kinds of entries in test data.
     * The difference is defined on the first row of the json object in the row column:
     * {"sql" : "sql statment"} is a sql statment to be executed as is. This is used for delete statements .
     * This ignores the table column, but it should still be filled in for reference.
     * All delete statements  are run before updates and must have a display_order of less than 50.
     *
     * {"name" : <data>} are inserts/updates where each object is a single column in a row to be updated/inserted.
     *
     * @param array $options Any extra options to be passed in.
     * @param boolean $options Extra options :
     *                         delete  : Only run delete statements . Only statements  with a
     *                                   display order of less than 100 are executed.
     *
     * @return void
     */
    public static function restore($options=array()) {
        $query = "
            SELECT
                 `test_data_id`
                ,`table`
                ,`row`
            FROM test_data
            WHERE display_order != 0 ";
        if (isset($options['delete']) === true && $options['delete'] === true) {
            $query .= "AND display_order < 100 ";
        }
        $query .= "ORDER BY display_order, test_data_id";

        $command = Yii::app()->db->createCommand($query);
        $lines = $command->queryAll();
        foreach ($lines as $line) {
            $row = CJSON::decode($line['row']);
            if (isset($row) === false) {
                throw new Exception(
                    "Error inserting/updating test data. Row data is invalid : \n\n"
                        . "test_data_id : " . $line['test_data_id'] . " \n\nsql : " . $line['row']
                );
            }

            $id = $row[0];

            // sql queries
            if (isset($id['sql']) === true) {
                try{
                    $query = $id['sql'];
                    $command = Yii::app()->db->createCommand($query);
                    $command->execute();
                }catch(Exception $e) {
                    throw new Exception(
                        "Error exequting sql test data. \n\ntest_data_id : "
                            . $line['test_data_id'] . " \n\nsql : " . $query . " \n\nException : " . $e . "\n\n"
                    );
                }

            } else if (isset($id['delete']) === true && $id['delete'] === true) {
                // delete statment.
                $where = "";
                foreach ($row as $item) {
                    if (isset($item['delete']) === true) {
                        continue;
                    }

                    $quote = "";
                    if ($item['string'] === true) {
                        $quote = "'";
                    }

                    $where .= "`" . $item['name'] . "` = " . $quote . $item['value'] . $quote . " AND ";

                }
                $where = substr($where, 0, -4);    // remove the AND
                $query = "DELETE FROM " . $line['table'] . " WHERE " . $where;
                try{
                    $command = Yii::app()->db->createCommand($query);
                    $command->execute();
                }catch(Exception $e) {
                    throw new Exception("Error deleting test data. " . $query);
                }

            } else {
                // Inserts and Updates.
                // Check if line exits.
                $query = "SELECT " . $id['name'] . " FROM " . $line['table']
                    . " WHERE " . $id['name'] . " = " . $id['value'];
                try{
                    $command = Yii::app()->db->createCommand($query);
                    $id_value = $command->queryScalar();
                }catch(Exception $e) {
                    throw new Exception(
                        "Error inserting/updating test data. " . $query
                            . ". test_data_id = " . $line['test_data_id'] . " " . $e
                    );
                }

                if ($id_value === $id['value']) {        // Update query

                    $query = "UPDATE " . $line['table'] . " SET ";
                    foreach ($row as $item) {
                        $quote = "";
                        if ($item['string'] === true) {
                            $quote = "'";
                        }

                        $query .= "`" . $item['name'] . "` = " . $quote . $item['value'] . $quote . ", ";
                    }
                    $query = substr($query, 0, -2);    // remove the last comma
                    $query .= " WHERE `" . $id['name'] . "` = " . $id['value'];

                } else {                                // Insert query

                    $names = "";
                    $values = "";
                    foreach ($row as $item) {
                        $quote = "";
                        if ($item['string'] === true) {
                            $quote = "'";
                        }

                        $names .=  "`" . $item['name'] . "`, ";
                        $values .= $quote . $item['value'] . $quote . ", ";
                    }
                    $names = substr($names, 0, -2);    // remove the last comma
                    $values = substr($values, 0, -2);    // remove the last comma

                    $query = "INSERT INTO " . $line['table'] . " ( " . $names . " ) VALUES ( " . $values . " ) ";

                }
            }

            try{
                $command = Yii::app()->db->createCommand($query);
                $command->execute();
            }catch(Exception $e) {
                throw new Exception(
                    "Error inserting/updating test data."
                        . " \n\nException : " . $e
                        . "\n\ntest_data_id : " . $line['test_data_id']
                        . " \n\nsql : " . $query . "\n\n"
                );
            }
        }
    }

    /**
     * Sets up the test data in preperation for a stream update test. Temporarily removes some test data.
     *
     * @return void
     */
    public static function setupStreamUpdateTest() {
        $query = "  UPDATE post SET stream_extra_id = 0 WHERE post_id=22;
                    UPDATE post SET stream_extra_id = 0 WHERE post_id=24;
                    UPDATE post SET stream_extra_id = 0 WHERE post_id=26;
                    UPDATE post SET stream_extra_id = 0 WHERE post_id=27;
                    UPDATE post SET stream_extra_id = 0 WHERE post_id=36;
                    UPDATE post SET stream_extra_id = 0 WHERE post_id=37;";
        $command = Yii::app()->db->createCommand($query);
        $command->execute();
    }

    /**
     * Triggers the stream update test by inserting posts ready for download as an update.
     *
     * @return void
     */
    public static function triggerStreamUpdateTest() {
        $query = "  UPDATE post SET stream_extra_id = 1, date_created = '2011-11-21 12:00:00' WHERE post_id=22;
                    UPDATE post SET stream_extra_id = 1, date_created = '2011-11-21 12:04:00' WHERE post_id=24;
                    UPDATE post SET stream_extra_id = 1, date_created = '2011-11-21 12:06:00' WHERE post_id=26;
                    UPDATE post SET stream_extra_id = 1, date_created = '2011-11-21 12:07:00' WHERE post_id=27;
                    UPDATE post SET stream_extra_id = 1, date_created = '2011-11-21 12:16:00' WHERE post_id=36;
                    UPDATE post SET stream_extra_id = 1, date_created = '2011-11-21 12:17:00' WHERE post_id=37;";
        $command = Yii::app()->db->createCommand($query);
        $command->execute();
    }


    /**
     * Triggers the stream update test by inserting posts ready for download as an update.
     *
     * @return void
     */
    public static function triggerCommentsUpdateTest() {
        $query = "  UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 14:01:00' WHERE post_id=38;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 11:00:00' WHERE post_id=39;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 17:00:00' WHERE post_id=40;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 15:05:00' WHERE post_id=41;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 15:15:00' WHERE post_id=42;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 15:25:00' WHERE post_id=43;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 15:26:00' WHERE post_id=44;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 15:35:00' WHERE post_id=45;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 15:40:00' WHERE post_id=46;
                    UPDATE post SET stream_extra_id = 3, date_created = '2011-11-13 15:52:00' WHERE post_id=49;";
        $command = Yii::app()->db->createCommand($query);
        $command->execute();
    }


    /**
     * Triggers a tree update to show the 'show revision' link to a user.
     *
     * @return void
     */
    public static function triggerCommentsEditTest() {
        $query = "  INSERT INTO post_content
                    (date_created, post_id, revision, display_order, text)
                    VALUES
                    ('2011-11-13 15:15:00', 14, 2, 1, 'edited text for test 14');";
        $command = Yii::app()->db->createCommand($query);
        $command->execute();
    }

}

?>