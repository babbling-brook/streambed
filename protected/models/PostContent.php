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
 * Model for the rhythm DB table.
 * The table holds the content of posts. It is stored seperately to the main post table so that
 * revisions can be made without changing the origional.
 * Multiple rows of content can exist in each post - each representing a field in the stream that
 * the post was made in.
 *
 * @package PHP_Models
 */
class PostContent extends CActiveRecord
{

    /**
     * Primary key for this post content row.
     *
     * @var integer
     */
    public $post_content_id;

    /**
     * The date and time that this post content row was created.
     *
     * @var string
     */
    public $date_created;

    /**
     * The primary key of the post row that owns this content revision row.
     *
     * @var integer
     */
    public $post_id;

    /**
     * The revision number for this row.
     *
     * @var integer
     */
    public $revision;

    /**
     * The display order that this row of content has in its parent post.
     *
     * @var integer
     */
    public $display_order;

    /**
     * If this is a row of text content then this is the contents text.
     *
     * @var string
     */
    public $text;

    /**
     * If this is a row of link content then this is the link url.
     *
     * @var string
     */
    public $link;

    /**
     * If this is a row of link content then this is the title of the link.
     *
     * @var string
     */
    public $link_title;

    /**
     * If this is a row of link content then this is the link to the original image used to generate a thumbnail
     *
     * @var string
     */
    public $link_thumbnail_url;

    /**
     * If this row represents a checkbox, then this is the checked status.
     *
     * @var boolean
     */
    public $checked;

    /**
     * If this row represents a select list, then this is a comma seperated list of selected values.
     *
     * @var integer
     */
    public $selected;

    /**
     * If this row represents a value field, then this is the maximum value that the field can have.
     *
     * @var integer
     */
    public $value_max;

    /**
     * If this row represents a value field, then this is the minimum value that the field can have.
     *
     * @var integer
     */
    public $value_min;

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
        return 'post_content';
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
            array('post_id', 'required'),
            array('post_id, revision', 'length', 'max' => 10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('post_content_id, post_id, revision, content', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'post_content_id' => 'Post Content',
            'post_id' => 'Post',
            'revision' => 'Revision',
            'content' => 'Content',
        );
    }

    /**
     * Fetch the content for a revision of an post.
     *
     * @param integer $post_id The id of the post we are fetching content for.
     * @param integer $revision The revision number of the post we are fetching content for.
     * @param integer|false Only fetch the content rows upto this limit. If false then all are fetched.
     *
     * @return array The fields for this post.
     */
    public static function getPostContent($post_id, $revision, $limit=false) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 text
                ,link
                ,link_title
                ,link_thumbnail_url
                ,checked
                ,selected
                ,value_max
                ,value_min
                ,display_order
            FROM post_content
            WHERE
                post_id = :post_id
                AND revision = :revision
            ORDER BY display_order ";

        if ($limit !== false) {
            $sql.= "LIMIT " . $limit;
        }

        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":revision", $revision, PDO::PARAM_INT);
        $rows = $command->queryAll();

        $final_rows = array();    // Results need to be in a clean array so surpless information is removed.
        foreach ($rows as &$row) {
            if (is_null($row["text"]) === true) {
                unset($row["text"]);
            }
            if (is_null($row["link"]) === true) {
                unset($row["link"]);
            }
            if (is_null($row["link_title"]) === true) {
                unset($row["link_title"]);
            }
            if (is_null($row["checked"]) === true) {
                unset($row["checked"]);
            } else if ((int)$row["checked"] === 1) {
                $row["checked"] = true;
            } else if ((int)$row["checked"] === 0) {
                $row["checked"] = false;
            }
            if (is_null($row["value_max"]) === true) {
                unset($row["value_max"]);
            }
            if (is_null($row["value_min"]) === true) {
                unset($row["value_min"]);
            }
            if (is_null($row["selected"]) === true) {
                unset($row["selected"]);
            } else {
                // split a string at unescaped commas where backslash is the escape character;
                // don't match commas if preceeded by an odd number of backslashes.
                // php regex need four back slashes to represent one (rather than the normal two.
                // This is too escape both the php string backslash and the regex one.
                $splitter = "/((?:[^\\\\,]|\\\\.)*)/";
                $selected = array();
                preg_match_all($splitter, $row["selected"], $pieces, PREG_PATTERN_ORDER);
                $pieces = $pieces[1];
                foreach ($pieces as $piece) {
                    if ($piece !== "") {  // Replace all escaped characters.
                        array_push($selected, preg_replace("/\\\\(.)/s", "$1", $piece));
                    }
                }

                //$selected = preg_split('#(?<!\\\)\,#', $row["selected"]);
                $row["selected"] = $selected;
            }

            $final_rows[$row["display_order"]] = $row;
        }

        // If the array looks like a normal indexed array then it
        // forces the array to be associative so that when it is converted to json it uses object
        // syntax rather than array syntax.
        if (count($final_rows) === 0 || array_keys($final_rows) === range(0, sizeof($final_rows) - 1)) {
            $final_rows = (object)$final_rows;
        }
        return $final_rows;
    }

    /**
     * Fetch maximum and minimum value for an post that has constraints on the post.
     *
     * @param integer $post_id The id of the post we are fetching max/min values for.
     * @param integer $display_order The display order of the field that is being checked.
     *
     * @return array
     */
    public static function getMaxMin($post_id, $display_order) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 value_min
                ,value_max
            FROM post_content
            WHERE
                post_id = :post_id
                AND display_order = :display_order
            ORDER BY revision DESC
            LIMIT 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":display_order", $display_order, PDO::PARAM_INT);
        $row = $command->queryRow();
        return $row;
    }

    /**
     * Appends post content to an post object.
     *
     * @param object $post An post object as returned from Post::getPostsBlock.
     *
     * @return object
     */
    public static function appendContent($post) {
        $revision = Post::getLatestRevisionNumber($post['post_id']);
        $post['content'] = self::getPostContent($post['post_id'], $revision);
        return $post;
    }

    /**
     * Deletes post_content rows by thier post_id.
     *
     * @param integer $post_id The id of the post in post_content that is being deleted.
     *
     * @return void
     */
    public static function deleteByPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post_content
            WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of post_content data for a post.
     *
     * @param type $post_id The id of the post to select data for.
     *
     * @return array
     */
    public static function getRowsForPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM post_content
                WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }



}

?>