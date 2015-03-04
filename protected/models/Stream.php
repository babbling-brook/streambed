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
 * Model for the stream DB table.
 * The table holds top level information about streams.
 *
 * @package PHP_Models
 */
class Stream extends CActiveRecord
{

    /**
     * The primary key of the stream.
     *
     * @var integer
     */
    public $stream_id;

    /**
     * The id of the user that owns the stream.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The name of the stream.
     *
     * @var string
     */
    public $name;

    /**
     * The id of the kind of this stream. See stream.kind in the lookup table for valid options.
     *
     * @var integer
     */
    public $kind;

    /**
     * The post username that owns this stream.
     *
     * This is not a table column.
     * Included here to enable searching on gridviews (from username).
     *
     * @var string
     */
    public $username;

    /**
     * The domain that owns this stream.
     *
     * This is not a table column.
     * Included here to enable searching on gridviews (from user.site).
     *
     * @var string
     */
    public $domain;

    /**
     * The creation date for the stream.
     *
     * This is not a table column.
     * Included here to enable searching on gridviews (from stream_extra).
     *
     * @var string
     */
    public $date_created;

    /**
     * The id of the status of the stream. See status table for valid options.
     *
     * This is not a table column.
     * Included here to enable searching on gridviews (from stream_extra).
     *
     * @var integer
     */
    public $status_id;

    /**
     * The stream version that the post resides on.
     *
     * Included here to enable searching on gridviews (from version - combined major/minor/patch).
     * This is not a table column.
     *
     * @var string
     */
    public $version;

    /**
     * Stored to prevent repeat access to the DB to obtain it for rules.
     *
     * This is not a table column.
     *
     * @var string
     */
    protected $url_username = "";

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
        return '{{stream}}';
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
            array('name, kind', 'required'),
            array('name', 'length', 'max' => 128),
            array(
                'name',
                'match',
                'pattern' => '/^[a-z0-9](?:\x20?[a-z0-9])*$/',
                'message' => 'Name can only contain lower case letters, digits 0 to 9 and spaces.'
                    . 'It cannot start or end with a space and double spaces are not allowed.',
            ),
            array('kind', 'ruleKind'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('name, date_created, username, domain, version, status_id', 'safe', 'on' => 'search'),
            array('name', 'ruleDuplicate', 'on' => 'duplicate, create'),
        );
    }

    /**
     * Ensure that a name does not already exist.
     *
     * @return void
     */
    public function ruleDuplicate() {
        if (isset($this->name) === true) {
            if (StreamBedMulti::isNameUnique(Yii::app()->user->getId(), $this->name) === true) {
                return;
            }
        }
        $this->addError('name', 'You already have an stream with that name. Please try another.');
    }

    /**
     * A rule to check that the kind id value is valid.
     *
     * @return void
     */
    public function ruleKind() {
        $valid = LookupHelper::validId('stream.kind', $this->kind);
        if ($valid === false) {
            $this->addError('kind', 'Kind is not a valid value. ' . $this->kind);
        }
    }

    /**
     * Sets the kind id value from its textutal value defined in the Babbling Brook protocol.
     *
     * @param string $kind A Babbling Brook stream.kind value
     *
     * @return void
     */
    public function setKindFromText($kind) {
        $kind_id = LookupHelper::getID('stream.kind', $kind, false);
        if ($kind_id === false) {
            $this->addError('kind', 'Kind is not a valid Babbling Brook value. ' . $kind);
        } else {
            $this->kind = $kind_id;
        }
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
            'user' => array(self::BELONGS_TO, 'User', 'user_id', 'joinType' => 'INNER JOIN'),
            'extra' => array(self::HAS_ONE, 'StreamExtra', 'stream_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'stream_id' => 'Stream',
            'name' => 'Name',
            'user_id' => 'User',
            'status_id' => 'Status',        // taken from stream_extra
            'kind' => 'Select the kind of stream',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @param integer $user_id The id of the user used to restrict search results.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function listByUser($user_id) {
        $criteria=new CDbCriteria;
        $criteria->with = array('stream', 'version', 'stream.user');
        $criteria->addCondition("stream.user_id = " . $user_id);
        $criteria->compare('date_created', $this->date_created, true);
        $criteria->compare('stream.name', $this->name, true);
        $criteria->compare('status_id', $this->status_id);

        // Version comparision
        if (isset($this->version) === true) {
            $version_array = explode("/", $this->version);
            $numeric = true;
            // Test if version enetered is valid
            if (count($version_array) > 3) {
                $numeric = false;
                $this->version = "";
            }
            foreach ($version_array as $element) {
                if (is_numeric($element) === false) {
                    $numeric = false;
                    $this->version = "";
                }
            }
            // Compare each element of the version seperately
            if ($numeric === true) {
                if (count($version_array) >= 1) {
                    $criteria->compare('major', $version_array[0]);
                }
                if (count($version_array) >= 2) {
                    $criteria->compare('minor', $version_array[1]);
                }
                if (count($version_array) >= 3) {
                    $criteria->compare('patch', $version_array[2]);
                }
            }
        }

        // Do not display private streams unless the owner
        if ($user_id !== Yii::app()->user->getId()) {
            $criteria->addCondition("status_id != " . StatusHelper::getID("private"));
        }

        return new CActiveDataProvider(
            new StreamExtra,
            array(
                'criteria' => $criteria,
                'sort' => array(
                    'defaultOrder' => 'stream.name, major DESC, minor DESC, patch DESC',
                    'attributes' => array(
                        'name' => array(
                            'asc' => 'stream.name, major, minor, patch',
                            'desc' => 'stream.name DESC, major, minor, patch',
                        ),
                        'version' => array(
                            'asc' => 'major, minor, patch',
                            'desc' => 'major DESC, minor DESC, patch DESC',
                        ),
                        'date_created' => array(
                            'asc' => 'date_created',
                            'desc' => 'date_created DESC',
                        ),
                        'status_id' => array(
                            'asc' => 'status_id, stream.name, major, minor, patch',
                            'desc' => 'status_id DESC, stream.name, major, minor, patch',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Retrieves a list of Streams that can be used in an post group.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function listForType() {
        $criteria=new CDbCriteria;
        $criteria->group = "t.stream_id";
        $criteria->with = array(
            'extra' => array(
                'select' => 'stream_id',
                'joinType' => 'INNER JOIN',
            ),
            'user' => array(
                'select' => 'username',
                'joinType' => 'INNER JOIN',
            ),
            'user.site' => array(
                'select' => 'domain',
                'joinType' => 'INNER JOIN',
            ),
        );
        $criteria->select = array(
            't.stream_id',
            //'extra.date_created',
            't.name',
            't.user_id',
            //'extra.status_id',
        );
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('site.domain', $this->domain, true);
        $criteria->compare('user.username', $this->username, true);
        // Do not display private streams unless the owner
        $criteria->addCondition(
            "(extra.status_id != " . StatusHelper::getID("private")
                . " OR t.user_id = " . Yii::app()->user->getId() . ")"
        );

        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
                'pagination' => array(
                    'pageSize' => 5,
                ),
                'sort' => array(
                    'defaultOrder' => 't.name',
                    'attributes' => array(
                        'domain' => array(
                            'asc' => 'domain',
                            'desc' => 'domain DESC',
                        ),
                        'username' => array(
                            'asc' => 'user.username',
                            'desc' => 'user.username DESC',
                        ),
                        'name' => array(
                            'asc' => 't.name',
                            'desc' => 't.name DESC',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Get the url to view this model.
     *
     * @param string $version The part of the url that refers to the version.
     *
     * @return string the URL that shows the detail of the Stream.
     */
    public function getViewUrl($version='') {
        return $this->getUrl('view', $version);
    }

    /**
     * Get the url to update this model.
     *
     * @param string $version The part of the url that refers to the version.
     *
     * @return string the URL that shows the detail of the Stream.
     */
    public function getUpdateUrl($version='') {
        return $this->getUrl('update', $version);
    }

    /**
     * Get an url for this model.
     *
     * @param string $action The action to call with this URL.
     * @param string $version The part of the url that refers to the version.
     *
     * @return string the URL that allows editing the Stream.
     */
    protected function getUrl($action, $version) {
        $user_multi = new UserMulti();
        if ($this->url_username === "") {
            $this->url_username = $user_multi->getUsernameFromID($this->user_id);
        }
        return Yii::app()->createUrl(
            $this->url_username
                . '/stream/'
                . $action . '/'
                . urlencode($this->name)
                . '/' . $version
        );
    }

    /**
     * Get an streams kind value from an post id.
     *
     * @param integer $post_id The id of the post that we are fetching a kind_id for.
     *
     * @return integer
     */
    public static function getKindFromPostID($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     stream.kind
                FROM post
                    INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                    INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                WHERE post.post_id = :post_id ";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $kind_id = $command->queryScalar();
        if (isset($kind_id) === false) {
            throw new Exception("Kind ID not found");
        }
        return $kind_id;
    }

    /**
     * Gets the id of a stream from its user_id and name.
     *
     * @param integer $user_id The id of the user who owns this stream.
     * @param string $name The name of the stream.
     *
     * @return integer|false The stream id or false.
     */
    public static function getStreamID($user_id, $name) {
        $connection = Yii::app()->db;
        $sql = "SELECT stream_id
                FROM stream
                WHERE user_id = :user_id AND name = :name";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":name", $name, PDO::PARAM_STR);
        $stream_id = $command->queryScalar();
        return $stream_id;
    }


    /**
     * Gets a stream row from its id.
     *
     * @param integer $stream_id The id of the stream family.
     *
     * @return array The stream row.
     */
    public static function getRow($stream_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream
                WHERE stream_id = :stream_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_id", $stream_id, PDO::PARAM_INT);
        $stream_id = $command->queryRow();
        return $stream_id;
    }

    /**
     * Fetches all the stream_id values for a user.
     *
     * @param integer $user_id The id of the user that stream_id values are being fetched for.
     *
     * @return array An simple array of stream_id values.
     */
    public static function getStreamIdsForUser($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT stream_id
                FROM stream
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stream_ids = $command->queryColumn();
        return $stream_ids;
    }

    /**
     * Deletes a stream by its id.
     *
     * NOTE: This needs to be called from DeleteMulti to ensure the deletion of all dependent data.
     *
     * @param integer $stream_id The id of the stream that is being deleted.
     *
     * @return void
     */
    public static function deleteByStreamId($stream_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "DELETE FROM stream
                    WHERE stream_id = :stream_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":stream_id", $stream_id, PDO::PARAM_INT);
            $command->execute();
        } catch (Exception $e) {
            throw new Exception(
                'Stream::deleteByStreamId should only be called from DeleteMulti to enable deletion of'
                    . 'relevent child rows connected with a foriegn key. ' . $e
            );
        }
    }

    /**
     * Select rows of stream data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>