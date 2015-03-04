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
 * Model for the post_descendent DB table.
 * The table links posts to all their descendents
 *
 * @package PHP_Models
 */
class PostDescendent extends CActiveRecord
{

    /**
     * Primary key for this row
     *
     * @var integer
     */
    public $post_descendent_id;

    /**
     * The id of the post that is being indexed.
     *
     * @var integer
     */
    public $ancestor_post_id;

    /**
     * The id of the post that is a descendent of $ancestor_post_id.
     *
     * @var integer
     */
    public $descendent_post_id;

    /**
     * How many levels deep is this descendent from the indxed ancestor.
     *
     * @var integer
     */
    public $level;


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
        return 'post_descendent';
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
            array('ancestor_post_id, descendent_post_id, level', 'required'),
            array('post_descendent_id, ancestor_post_id, descendent_post_id, level', 'length', 'max' => 10),
        );
    }

    /**
     * Recreates all the indexes in the table. Used to setup from old table design.
     */
    public static function recreateAll() {
        self::deleteAllRows();

        $connection = Yii::app()->db;
        $sql = "
            SELECT post_id AS descendent_post_id, parent AS ancestor_post_id FROM post";
        $command = $connection->createCommand($sql);
        $rows = $command->queryAll();
        self::recreateLevel($rows, 1);
    }

    private static function recreateLevel($rows, $level) {
        foreach ($rows as $row) {
            if (is_null($row["ancestor_post_id"]) === false) {
                $post_descendent_model = new PostDescendent;
                $post_descendent_model->ancestor_post_id = $row['ancestor_post_id'];
                $post_descendent_model->descendent_post_id = $row['descendent_post_id'];
                $post_descendent_model->level = $level;
                $post_descendent_model->save();
            }
        }

        if (sizeof($rows) > 0) {
            $connection = Yii::app()->db;
            $sql = "
                SELECT
                     post.parent AS ancestor_post_id
                    ,post_descendent.descendent_post_id
                FROM post_descendent
                    INNER JOIN post
                        ON post_descendent.ancestor_post_id = post.post_id
                WHERE
                    post.parent IS NOT NULL
                    AND level = :level";
            $command = $connection->createCommand($sql);
            $command->bindValue(":level", $level, PDO::PARAM_INT);
            $rows = $command->queryAll();
            self::recreateLevel($rows, $level + 1);
        }
    }

    /**
     * Fetch maximum and minimum value for an post that has constraints on the post.
     *
     * @return void
     */
    private static function deleteAllRows() {
        $connection = Yii::app()->db;
        $sql = "
            DELETE FROM post_descendent";
        $command = $connection->createCommand($sql);
        $command->execute();
    }

    /**
     * Insert all the anscetors for this post.
     *
     * @return void
     */
    public static function insertAncestors($post_id, $parent_id) {

        // Copy all the ancestors for the parent but with new levels.
        $connection = Yii::app()->db;
        $sql = "SELECT ancestor_post_id, level FROM post_descendent WHERE descendent_post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $parent_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        $highest_level = 1;
        foreach ($rows as $row) {
            $post_descendent_model = new PostDescendent;
            $post_descendent_model->ancestor_post_id = $row['ancestor_post_id'];
            $post_descendent_model->descendent_post_id = $post_id;
            $new_level = intval($row['level']);
            $post_descendent_model->level = $new_level;
            $post_descendent_model->save();

            if ($new_level > $highest_level) {
                $highest_level = $new_level;
            }
        }

        // Add the new post at the bottom
        $post_descendent_model = new PostDescendent;
        $post_descendent_model->ancestor_post_id = $parent_id;
        $post_descendent_model->descendent_post_id = $post_id;
        $post_descendent_model->level = $highest_level + 1;
        $post_descendent_model->save();
    }

    /**
     * Fetches a count of the number of comments that are children of this one.
     */
    public static function getChildCount($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT COUNT(*)
            FROM post_descendent
            WHERE ancestor_post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $count = $command->queryScalar();
        return $count;
    }

    /**
     * Returns all the ancestor ids for a parent id.
     *
     * @param type $post_id
     *
     * @return array of rows containing post_id
     */
    public static function getAllAncestorIds($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT ancestor_post_id
            FROM post_descendent
            WHERE descendent_post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Deletes post_descendent rows by their ancestor_post_id
     *
     * @param integer $post_id The id of the posts ancestor in post_descendent that is being deleted.
     *
     * @return void
     */
    public static function deleteDescendentByAncestorPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post_descendent
            WHERE ancestor_post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes post_descendent rows by their descendent_post_id
     *
     * @param integer $post_id The id of the posts descendent in descendent_post_id that is being deleted.
     *
     * @return void
     */
    public static function deleteDescendentByDescendentPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post_descendent
            WHERE descendent_post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }
}

?>