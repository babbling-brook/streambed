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
 * Model for the stream_child DB table.
 * The table defines relationships between streams and child streams.
 * For example comments on a stream.
 *
 * @package PHP_Models
 */
class StreamChild extends CActiveRecord
{

    /**
     * Primary key for this relationship.
     *
     * @var integer
     */
    public $stream_child_id;

    /**
     * The extra id of the parent stream in this relationship.
     *
     * @var integer
     */
    public $parent_id;

    /**
     * The extra id of the child stream in this relationship.
     *
     * @var integer
     */
    public $child_id;

    /**
     * The version type of the child stream in the relationship.
     *
     * See version_type in the lookup table for valid options.
     *
     * @var integer
     */
    public $version_type;

    /**
     * The order that the child streams appear in. 1 is high.
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
        return 'stream_child';
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
            array('parent_id, child_id, version_type', 'required'),
            array('parent_id, child_id, version_type', 'length', 'max' => 10),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'stream_extra' => array(
                self::BELONGS_TO,
                'StreamExtra',
                'child_id',
                'joinType' => 'INNER JOIN',
            ),
            'stream_extra_parent' => array(
                self::BELONGS_TO,
                'StreamExtra',
                'parent_id',
                'joinType' => 'INNER JOIN',
            ),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'stream_child_id' => 'Stream Child',
            'parent_id' => 'Parent',
            'child_id' => 'Child',
            'version_type' => 'Version Type',
        );
    }

    /**
     * Retrieves a list of Streams that have been grouped.
     *
     * Used by selectfrom list to display the list of selected items.
     *
     * @param integer $parent_id The id of the parent stream to fetch a list of child types.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function listForType($parent_id) {
        $criteria=new CDbCriteria;
        $criteria->with = array(
            'stream_extra',
            'stream_extra.stream',
            'stream_extra.version',
            'stream_extra.stream.user',
            'stream_extra.stream.user.site',
        );
        //$criteria->compare('t.cat.name', $this->search_cat, true);
        // Do not display private streams unless the owner
        $criteria->addCondition(
            "(stream_extra.status_id != " . StatusHelper::getID("private")
                . " OR stream.user_id = " . Yii::app()->user->getId() . ")"
        );
        $criteria->addCondition("parent_id = " . $parent_id);

        $list_data = new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
                'pagination' => array(
                    'pageSize' => 100,
                ),
                'sort' => array(
                    'defaultOrder' => 'site.domain, '
                        . 'user.username, '
                        . 'stream.name, '
                        . 'major DESC,'
                        . 'minor DESC, '
                        . 'patch DESC',
                ),
            )
        );
        return $list_data;
    }

    /**
     * Checks if a child already exists for an stream.
     *
     * @param integer $parent_id The stream_extra_id we are checking for a child.
     * @param integer $child_id The stream_extra_id of the child we are checking.
     * @param integer $version_type The type of version. See lookup table for details.
     *
     * @return integer|boolean primary key or false.
     */
    public static function doesChildExist($parent_id, $child_id, $version_type) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     stream_child_id
                FROM stream_child
                WHERE
                    parent_id = :parent_id
                    AND child_id = :child_id
                    AND version_type = :version_type";
        $command = $connection->createCommand($sql);
        $command->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
        $command->bindValue(":child_id", $child_id, PDO::PARAM_INT);
        $command->bindValue(":version_type", $version_type, PDO::PARAM_INT);
        $primary_key = $command->queryScalar();
        return $primary_key;
    }

    /**
     * Fetch the child stream_extra_id and version_type for a parent stream_extra_id
     *
     * @param type $parent_id
     */
    public static function getChildrenForParent($parent_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     child_id
                    ,version_type
                FROM stream_child
                WHERE
                    parent_id = :parent_id
                ORDER BY stream_child_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
        $stream_child = $command->queryAll();
        return $stream_child;
    }

    /**
     * Fetch All the versions of a child stream that are attatched to a parent.
     *
     * @param type $parent_id The stream_extra_id of the parent stream to fetch child version data for.
     * @param type $child_stream_id The stream_id of the child stream to fetch version data for.
     *
     * @return array List of version types that are being used.
     */
    public static function getVersionsOfChild($parent_id, $child_stream_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     version.major
                    ,version.minor
                    ,version.patch
                    ,stream_child.version_type
                FROM
                    stream_child
                    INNER JOIN stream_extra ON stream_extra.stream_extra_id = stream_child.child_id
                    INNER JOIN version ON stream_extra.version_id = version.version_id
                WHERE
                    parent_id = :parent_id
                    AND stream_extra.stream_id = :child_stream_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
        $command->bindValue(":child_stream_id", $child_stream_id, PDO::PARAM_INT);
        $version_rows = $command->queryAll();
        return $version_rows;
    }

    /**
     * Fetch the child id from the primary key.
     *
     * @param type $stream_child_id The primary key to fetch a child stream_id from.
     *
     * @return integer A child stream_extra_id
     */
    public static function getChildId($stream_child_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     child_id
                FROM stream_child
                WHERE
                    stream_child_id = :stream_child_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_child_id", $stream_child_id, PDO::PARAM_INT);
        $child_id = $command->queryScalar();
        return $child_id;
    }


    /**
     * Fetch the child streams for a parent stream in name format with an additional row for sort order.
     *
     * @param integer $parent_stream_extra_id The extra id of the stream to fetch childstreams for.
     *
     * @return array Returned as an array of stream name arrays with an additional sort_order attribute.
     */
    public static function getChildrenInNameFormat($parent_stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     site.domain
                    ,user.username
                    ,stream.name
                    ,version.major
                    ,version.minor
                    ,version.patch
                    ,stream_child.version_type
                    ,stream_child.sort_order
                FROM
                    stream_child
                    INNER JOIN stream_extra ON stream_child.child_id = stream_extra.stream_extra_id
                    INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                    INNER JOIN version ON stream_extra.version_id = version.version_id
                    INNER JOIN user ON stream.user_id = user.user_id
                    INNER JOIN site ON user.site_id = site.site_id
                WHERE stream_child.parent_id = :parent_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":parent_id", $parent_stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        foreach ($rows as $key => $row) {
            $version_from_type = Version::makeVersionUrlFromVersionTypeId(
                $row['version_type'],
                $row['major'],
                $row['minor'],
                $row['patch']
            );
            $rows[$key]['version'] = Version::makeArrayFromString($version_from_type);
            unset($rows[$key]['major']);
            unset($rows[$key]['minor']);
            unset($rows[$key]['patch']);
            unset($rows[$key]['version_type']);
        }
        return $rows;
    }

    /**
     * Fetch the sort order of the last child in this parent stream.
     *
     * @param type $parent_stream_extra_id
     *
     * @return integer|false
     */
    public static function getLastSortOrder($parent_stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT sort_order
                FROM stream_child
                WHERE parent_id = :parent_stream_extra_id
                ORDER BY sort_order DESC
                LIMIT 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(":parent_stream_extra_id", $parent_stream_extra_id, PDO::PARAM_INT);
        $last_sort_order = $command->queryScalar();
        return $last_sort_order;
    }

    /**
     * Inserts a new child stream for a parent.
     *
     * @param type $parent_stream_extra_id The extra id of the parent stream.
     * @param type $child_stream_extra_id The extra id of the child stream.
     * @param type $version_type_id The id of the version type for the childs version.
     *
     * @return true|array true or an array of errors.
     */
    public static function addChild($parent_stream_extra_id, $child_stream_extra_id, $version_type_id) {
        $sort_order = self::getLastSortOrder($parent_stream_extra_id);
        if ($sort_order === false) {
            $sort_order = 1;
        } else {
            $sort_order++;
        }

        $model = new StreamChild;
        $model->parent_id = $parent_stream_extra_id;
        $model->child_id = $child_stream_extra_id;
        $model->version_type = $version_type_id;
        $model->sort_order = $sort_order;
        $model->save();
        if ($model->hasErrors() === true) {
            return $model->getErrors();
        } else {
            return true;
        }
    }

    /**
     * Switches the sort order for two child streams in a parent.
     *
     * @param type $parent_stream_extra_id The extra id of the parent stream.
     * @param type $stream_extra_id_1 The extra id of the first child stream.
     * @param type $version_type_1_id The id of the version type for the first childs version.
     * @param type $stream_extra_id_2 The extra id of the seconds child stream.
     * @param type $version_type_2_id The id of the version type for the seconds childs version.
     *
     * @return true|string true or an error.
     */
    public static function switchChildrensDisplayOrder($parent_stream_extra_id, $stream_extra_id_1, $version_type_1_id,
        $stream_extra_id_2, $version_type_2_id
    ) {
        $child_1_model = StreamChild::model()->find(
            array(
                'condition' => 'parent_id=:parent_stream_extra_id '
                . 'AND child_id=:stream_extra_id '
                . 'AND version_type=:version_type_id',
                'params' => array(
                    ':parent_stream_extra_id' => $parent_stream_extra_id,
                    ':stream_extra_id' => $stream_extra_id_1,
                    ':version_type_id' => $version_type_1_id,
                )
            )
        );
        if (isset($child_1_model) === false) {
            return 'Child stream not found.';
        }

        $child_2_model = StreamChild::model()->find(
            array(
                'condition' => 'parent_id=:parent_stream_extra_id '
                . 'AND child_id=:stream_extra_id '
                . 'AND version_type=:version_type_id',
                'params' => array(
                    ':parent_stream_extra_id' => $parent_stream_extra_id,
                    ':stream_extra_id' => $stream_extra_id_2,
                    ':version_type_id' => $version_type_2_id,
                )
            )
        );
        if (isset($child_2_model) === false) {
            return 'Child stream not found.';
        }

        $sort_order_1 = $child_1_model->sort_order;
        $sort_order_2 = $child_2_model->sort_order;
        $child_1_model->sort_order = $sort_order_2;
        $child_1_model->sort_order = $sort_order_1;
        $child_1_model->save();
        $child_2_model->save();
        return true;
    }

    /**
     * Get the row model for a child stream.
     *
     * @param type $parent_stream_extra_id The extra id of the parent stream.
     * @param type $child_stream_extra_id The extra id of the child stream.
     * @param type $child_stream_version_type The version type of the child stream.
     *
     * @return ChildStream
     */
    public static function getChildStreamRow($parent_stream_extra_id, $child_stream_extra_id,
        $child_stream_version_type
    ) {
        $row = StreamChild::model()->find(
            array(
                'condition' => 'parent_id=:parent_stream_extra_id '
                . 'AND child_id=:stream_extra_id '
                . 'AND version_type=:version_type_id',
                'params' => array(
                    ':parent_stream_extra_id' => $parent_stream_extra_id,
                    ':stream_extra_id' => $child_stream_extra_id,
                    ':version_type_id' => $child_stream_version_type,
                )
            )
        );
        return $row;
    }

    /**
     * Switches the sort order for two child streams in a parent.
     *
     * @param type $parent_stream_extra_id The extra id of the parent stream.
     * @param type $old_stream_extra_id The extra id of the child stream that is being replaced.
     * @param type $old_version_type_id The id of the version type for the child stream that is being replaced.
     * @param type $new_stream_extra_id The extra id of the new child stream.
     * @param type $new_version_type_id The id of the version type for the new child stream.
     *
     * @return true|array true or an array of model errors.
     */
    public static function replaceChildStream($parent_stream_extra_id, $old_stream_extra_id, $old_version_type_id,
        $new_stream_extra_id, $new_version_type_id
    ) {
        $child_model = self::getChildStreamRow($parent_stream_extra_id, $old_stream_extra_id, $old_version_type_id);
        $child_model->child_id = $new_stream_extra_id;
        $child_model->version_type = $new_version_type_id;
        $child_model->save();
        if ($child_model->hasErrors() === true) {
            return $child_model->getErrors();
        } else {
            return true;
        }
    }

    /**
     * Swaps the display order for two child streams.
     *
     * @param intger $stream_extra_id The extra id of the stream that has two child streams that are being swapped.
     * @param intger $child_stream_extra_id_1 The extra id of the first child stream.
     * @param integer $child_stream_version_type_id_1 The version type from the first child stream.
     * @param intger $child_stream_extra_id_2 The extra id of the second child_stream.
     * @param integer $child_stream_version_type_id_2 The version type from the second child_stream.
     *
     * @return void|string Nothing or an error message.
     */
    public static function swapChildStream($stream_extra_id, $child_stream_extra_id_1, $child_stream_version_type_id_1,
        $child_stream_extra_id_2, $child_stream_version_type_id_2
    ) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $row_1 = self::getChildStreamRow(
                $stream_extra_id,
                $child_stream_extra_id_1,
                $child_stream_version_type_id_1
            );
            $row_2 = self::getChildStreamRow(
                $stream_extra_id,
                $child_stream_extra_id_2,
                $child_stream_version_type_id_2
            );
            // have to save to 0 to prevent an integrity constaint problem.
            $first_sort_order = $row_1->sort_order;
            $second_sort_order = $row_2->sort_order;
            $row_1->sort_order = 0;
            $row_1->save();
            $row_2->sort_order = $first_sort_order;
            $row_2->save();
            $row_1->sort_order = $second_sort_order;
            $row_1->save();
            if ($row_1->hasErrors() === true || $row_2->hasErrors() === true) {
                $transaction->rollBack();
                $error = 'Database error whilst swaping two child stream sort orders. '
                    . ErrorHelper::model($row_1->getErrors()) . '. '
                    . ErrorHelper::model($row_2->getErrors());
                return $error;
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            return 'Database error whilst swaping two child stream sort orders.';
        }
    }

    /**
     * Deletes a child stream fromits parent.
     *
     * @param type $parent_stream_extra_id The extra id of the parent stream.
     * @param type $child_stream_extra_id The extra id of the child stream.
     * @param type $version_type_id The id of the version type for the child stream.
     *
     * @return true|string true or an error.
     */
    public static function deleteChild($parent_stream_extra_id, $child_stream_extra_id, $version_type_id) {
        $model = StreamChild::model()->find(
            array(
                'condition' => 'parent_id=:parent_stream_extra_id '
                . 'AND child_id=:stream_extra_id '
                . 'AND version_type=:version_type_id',
                'params' => array(
                    ':parent_stream_extra_id' => $parent_stream_extra_id,
                    ':stream_extra_id' => $child_stream_extra_id,
                    ':version_type_id' => $version_type_id,
                )
            )
        );
        if (isset($model) === false) {
            return 'Child stream not found.';
        }

        $deleted = $model->delete();
        if ($deleted === true) {
            return true;
        } else {
            return 'An error occurred when trying to delete the child stream.';
        }
    }

    /**
     * Inserts the site default child streams into a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that child streams are being inserted for.
     *
     * @return void
     */
    public static function insertSiteDefaults($stream_extra_id) {
        $defaults = Yii::app()->params['default_child_streams'];

        foreach ($defaults as $default) {
            $site_id = SiteMulti::getSiteID($default['domain']);
            $user_multi = new UserMulti($site_id);
            $user_id = $user_multi->getIDFromUsername($default['username']);
            $child_stream_extra_id = StreamBedMulti::getIDByName(
                $user_id,
                $default['name'],
                $default['version']['major'],
                $default['version']['minor'],
                $default['version']['patch']
            );
            $version_string = $default['version']['major'] . '/'
                . $default['version']['minor'] . '/' . $default['version']['patch'];
            $version_type_id = Version::getTypeId($version_string);
            self::addChild($stream_extra_id, $child_stream_extra_id, $version_type_id);
        }
    }

    /**
     * Deletes stream_child rows by their parent stream_extra_id.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is used to delete these row.
     *
     * @return void
     */
    public static function deleteParentsByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM stream_child
                WHERE parent_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes stream_child rows by their child stream_extra_id.
     *
     * @param integer $stream_extra_id The id of the stream_extra row that is used to delete these row.
     *
     * @return void
     */
    public static function deleteChildrenByStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM stream_child
                WHERE child_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of stream_child data for a stream parent_id.
     *
     * @param type $parent_id The extra id of the stream that is a parent to the rows to select data for.
     *
     * @return array
     */
    public static function getRowsForParentId($parent_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream_child
                WHERE parent_id = :parent_id
                ORDER BY sort_order";
        $command = $connection->createCommand($sql);
        $command->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>