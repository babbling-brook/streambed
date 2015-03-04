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
 * The table holds top level information about Rhythms.
 *
 * @package PHP_Models
 */
class Rhythm extends CActiveRecord
{

    /**
     * Primary key for this Rhythm.
     *
     * @var integer
     */
    public $rhythm_id;

    /**
     * The id of the user that owns this alogrithm.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The name of this Rhythm.
     *
     * @var string
     */
    public $name;


    /**
     * The category that this Rhythm is in.
     *
     * This is not a table column. It is here to Enable searching on gridviews (from table cat).
     *
     * @var string
     */
    public $category;

    /**
     * The username of the user that this Rhythm belongs to.
     *
     * This is not a table column. It is here to Enable searching on gridviews (from table user).
     *
     * @var string
     */
    public $username;

    /**
     * The domain of the site that this Rhythm belongs to.
     *
     * This is not a table column. It is here to Enable searching on gridviews (from table user.site).
     *
     * @var string
     */
    public $domain;

    /**
     * The date that this Rhythm was created.
     *
     * This is not a table column. It is here to Enable searching on gridviews (from table stream_extra).
     *
     * @var string
     */
    public $date_created;

    /**
     * The id of the status of this Rhythm.
     *
     * This is not a table column. It is here to Enable searching on gridviews (from table stream_extra).
     *
     * @var string
     */
    public $status_id;

    /**
     * The version string of this Rhythm.
     *
     * This is not a table column. It is here to Enable searching on gridviews.
     * (collated from table stream_extra.version major,minor and patch columns in slash format).
     *
     * @var string
     */
    public $version;

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
        return '{{rhythm}}';
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id', 'joinType' => 'INNER JOIN'),
            'extra' => array(self::HAS_ONE, 'RhythmExtra', 'rhythm_id', 'joinType' => 'INNER JOIN'),
        );
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
            array(
                'name',
                'required',
                'message' => 'Please provide a name for this Rhythm',
            ),
            array(
                'name',
                'match',
                'pattern' => '/^[a-z0-9](?:\x20?[a-z0-9])*$/',
                'message' => 'Name can only contain lower case letters, digits 0 to 9 and spaces. '
                    . 'It cannot start or end with a space and double spaces are not allowed.',
            ),
            array(
                'rhythm_id, user_id',
                'numerical',
                'message' => 'Please provide a whole number',
                'integerOnly' => true,
            ),
            array('name', 'length', 'max' => 127, 'min' => '1'),
            array(
                'name, description, date_created, category, username, domain, version, status_id',
                'safe',
                'on' => 'search',
            ),
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
            if (Rhythm::isNameUnique(Yii::app()->user->getId(), $this->name) === true) {
                return;
            }
        }
        $this->addError('name', 'You already have an Rhythm with that name. Please try another.');
    }

    /**
     * Before inserting a new record, set the Rhythm user_id to be that of the logged in user.
     *
     * @return boolean Gohead with save or not.
     */
    public function beforeSave() {
        if (parent::beforeSave() === true) {
            // Ensure that the user id is the logged on user
            $this->user_id = Yii::app()->user->getId();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'name' => 'Name',
            'date_created' => 'Date Created',
            'description' => 'Description',
            'status_id' => 'Status',
            'full' => 'Javascript code for the Rhythm',
        );
    }

    /**
     * Get the url to view this model.
     *
     * @param string $version The part of the url that refers to the version.
     *
     * @return string The URL that shows the detail of the Rhythm.
     */
    public function getViewUrl($version='') {
        return $this->getUrl('view', $version);
    }

    /**
     * Get the url to update this model.
     *
     * @param string $version The part of the url that refers to the version.
     *
     * @return string the URL that allows editing the Rhythm.
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
     * @return string the URL that allows editing the Rhythm.
     */
    protected function getUrl($action, $version) {
        $user_multi = new UserMulti();
        $username = $user_multi->getUsernameFromID($this->user_id);
        return Yii::app()->createUrl(
            $username .
            '/rhythm/' .
            $action . '/' .
            urlencode($this->name) .
            '/' . $version
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @param integer $user_id The id of the user we are using to generate a list.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function listByUser($user_id) {
        $criteria=new CDbCriteria;
        $criteria->with = array('rhythm', 'rhythm_cat', 'version', 'rhythm.user');
        $criteria->addCondition("rhythm.user_id = " . $user_id);
        $criteria->compare('t.date_created', $this->date_created, true);
        $criteria->compare('rhythm.name', $this->name, true);
        $criteria->compare('t.status_id', $this->status_id);
        $criteria->compare('rhythm_cat.rhythm_cat_id', $this->category);

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

        // NOTE: This is returning RhythmExtra not Rhythm
        return new CActiveDataProvider(
            new RhythmExtra,
            array(
                'criteria' => $criteria,
                'sort' => array(
                    'defaultOrder' => 'rhythm.name, major DESC, minor DESC, patch DESC',
                    'attributes' => array(
                        'name' => array(
                            'asc' => 'rhythm.name, major, minor, patch',
                            'desc' => 'rhythm.name DESC, major, minor, patch',
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
                            'asc' => 'status_id, rhythm.name, major, minor, patch',
                            'desc' => 'status_id DESC, rhythm.name, major, minor, patch',
                        ),
                        'category' => array(
                            'asc' => 'rhythm_cat.name, rhythm.name, major, minor, patch',
                            'desc' => 'rhythm_cat.name DESC, rhythm.name, major, minor, patch',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Checks if an Rhythm url is a valid Rhythm.
     *
     * @param string $url The url to check.
     * @param string|null $category The category of the Rhythm. If set to null then checks all categories.
     *
     * @return boolean
     * @fixme include an option to fetch from remote data store.
     */
    public static function checkValidUrl($url, $category=null) {
        $rhythm_parts = explode("/", $url);
        if (count($rhythm_parts) < 5 || count($rhythm_parts) > 8) {
            return false;
        }

        $rhythm_site_id = SiteMulti::getSiteID($rhythm_parts[0], false);
        if ($rhythm_site_id === false) {
            return false;
        }

        $rhythm_ub = new UserMulti($rhythm_site_id);
        $rhythm_user_id = $rhythm_ub->getIDFromUsername($rhythm_parts[1], false);
        if ($rhythm_user_id === false) {
            return false;
        }

        // Due a mysql issue where any string is equal to 0 if the column is an integer
        // Have to manually check that the version numbers are numbers.
        if (ctype_digit($rhythm_parts[4]) === false
            || ctype_digit($rhythm_parts[5]) === false
            || ctype_digit($rhythm_parts[6]) === false
        ) {
            return false;
        }

        if (isset($rhythm_parts[4]) === true && strlen($rhythm_parts[4]) > 0) {
            $major = $rhythm_parts[4];
        } else {
            $major = null;
        }
        if (isset($rhythm_parts[5]) === true && strlen($rhythm_parts[5]) > 0) {
            $minor = $rhythm_parts[5];
        } else {
            $minor = null;
        }
        if (isset($rhythm_parts[6]) === true &&  strlen($rhythm_parts[6]) > 0) {
            $patch = $rhythm_parts[6];
        } else {
            $patch = null;
        }
        $name = UrlHelper::replacePluses($rhythm_parts[3]);
        $rhythm = Rhythm::getByName($rhythm_user_id, $name, $major, $minor, $patch);
        if (isset($rhythm) === false) {
            if ($rhythm_site_id !== Yii::app()->params['site_id']) {
                return false;
                // @fixme check remote site
            } else {
                return false;
            }
        }
        $t = $rhythm->extra->rhythm_cat->name;
        if (isset($category) === true) {
            if ($rhythm->extra->rhythm_cat->name !== $category) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get an alogrithms full name from its id.
     *
     * @param integer $rhythm_extra_id The extra id for the Rhythm to check.
     * @param integer $version_type The id of the version type to use in fetching the full name.
     *                              See version_type in the lookup table.
     *
     * @return string|boolean False if not found.
     * @fixme this needs moving to rhythm_extra model.
     */
    public static function getFullNameFromID($rhythm_extra_id, $version_type) {
        if (isset($version_type) === false) {
            $version_type = LookupHelper::getID("version_type", "major/minor/patch");
        }

        $rhythm = Rhythm::getByIDWithSite($rhythm_extra_id);
        if (isset($rhythm) === false) {
            return false;
        }

        $version = Version::makeVersionUrlFromVersionTypeId(
            $version_type,
            $rhythm->version->major,
            $rhythm->version->minor,
            $rhythm->version->patch
        );
        $rhythm_name = $rhythm->rhythm->user->site->domain . "/"
            . $rhythm->rhythm->user->username
            . "/rhythm/json/"
            . $rhythm->rhythm->name . "/"
            . $version;
        return $rhythm_name;
    }

    /**
     * Fetch all the rhythm_id rows for the given user_id.
     *
     * @param type $user_id The id of the user that rhythm ids are being fetched for.
     *
     * @return array
     */
    public static function getRhythmsForUserId($user_id) {
        $query = "
            SELECT rhythm_id
            FROM rhythm
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rhythm_ids = $command->queryColumn();
        return $rhythm_ids;
    }

    /**
     * Delete all rhythm rows by their user_id
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $user_id The id of the user used to delete rhythm rows.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM rhythm
                WHERE user_id = :user_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            $command->execute();
        } catch (Exception $e) {
            throw new Exception(
                'Rhythm::deleteByUserId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Check if an Rhythm is owned by provided user.
     *
     * @param integer $rhythm_extra_id The extra id for this Rhythm.
     * @param integer $user_id The user id for the owner of this Rhythm.
     *
     * @return boolean True if Rhythm is owned by this user.
     */
    public static function checkOwner($rhythm_extra_id, $user_id) {
        $model = Rhythm::model()->with('extra')->find(
            array(
                'condition' => 'rhythm_extra_id=:rhythm_extra_id AND user_id=:user_id',
                'params' => array(
                    ':rhythm_extra_id' => $rhythm_extra_id,
                    ':user_id' => $user_id,
                )
            )
        );
        if (isset($model) === true) {
            return true;
        }
        return false;
    }

    /**
     * Set the status of the Rhythm (Updates RhythmExtra).
     *
     * @param integer $rhythm_extra_id The extra id for this Rhythm.
     * @param integer $status_id The status id to update this alogrithm to.
     *
     * @return void
     */
    public static function updateStatus($rhythm_extra_id, $status_id) {
        RhythmExtra::model()->updateAll(
            array(
                'status_id' => $status_id,
            ),
            array(
                'condition' => 'rhythm_extra_id = :rhythm_extra_id',
                'params' => array(
                    ':rhythm_extra_id' => $rhythm_extra_id,
                )
            )
        );
    }

    /**
     * Fetch an Rhythm from its extra_id.
     *
     * @param integer $rhythm_extra_id The extra id for the Rhythm being searched for.
     *
     * @return Rhythm Result rows
     */
    public static function getByID($rhythm_extra_id) {
        return RhythmExtra::model()->with('rhythm', 'rhythm_cat', 'version')->findByPk($rhythm_extra_id);
    }

    /**
     * Fetch an Rhythm from its extra_id.
     *
     * @param integer $rhythm_extra_id The extra id of the Rhythm being fetched.
     *
     * @return Rhythm Result rows
     */
    public static function getByIDWithSite($rhythm_extra_id) {
        return RhythmExtra::model()->with(
            'rhythm',
            'rhythm_cat',
            'version',
            'rhythm.user',
            'rhythm.user.site'
        )->findByPk($rhythm_extra_id);
    }

    /**
     * Check if the name has been edited for this Rhythm.
     *
     * @param integer $rhythm_id The Rhythm id for the alogrithm we are checking.
     * @param string $name Name of the Rhythm to look up.
     *
     * @return boolean True if name has changed otherwise false.
     * @fixme replace all check method names with 'is' or 'has'.
     */
    public static function checkNameChanged($rhythm_id, $name) {
        $params = array(
            ':rhythm_id' => $rhythm_id,
            ':name' => $name,
        );
        return !Rhythm::model()->exists(
            array(
                'condition' => 'rhythm_id=:rhythm_id AND name=:name',
                'params' => $params,
            )
        );
    }


    /**
     * Find out if this Rhythm unique to this user.
     *
     * @param integer $user_id The id of the user whose Rhythms we are checking.
     * @param string $name Name of the Rhythm to look up.
     *
     * @return boolean True if name does not exist yet.
     */
    public static function isNameUnique($user_id, $name) {
        $params = array(
            ':name' => $name,
            ':user_id' => $user_id,
        );
        return !Rhythm::model()->exists(
            array(
                'condition' => 'name=:name AND user_id=:user_id',
                'params' => $params,
            )
        );
    }


    /**
     * Get an rhythm as long as it owned by the user who is passed in.
     *
     * @param integer $rhythm_id The id of the Rhythm we are fetching.
     * @param integer $user_id The id of the user we are fetching.
     *
     * @return Rhythm Model
     */
    public static function getForUser($rhythm_id, $user_id) {
        return Rhythm::model()->with('cat')->with('version')->find(
            array(
                'condition' => 'rhythm_id=:rhythm_id AND user_id=:user_id',
                'params' => array(
                    ':rhythm_id' => $rhythm_id,
                    ':user_id' => $user_id,
                )
            )
        );
    }

    /**
     * Inserts a new alogrithm (Or a new version of an existing one).
     *
     * @param integer $user_id Owner of the alogrithm.
     * @param string $name Name of the Rhythm.
     * @param string $description Description of the Rhythm.
     * @param string $full Original version of the Rhythm.
     * @param string $mini Minified version of the Rhythm.
     * @param integer $version_id Link to version table.
     * @param integer $status_id Public status of the Rhythm.
     * @param integer $rhythm_cat_id The id of the Rhythm category we are inserting.
     *
     * @return integer|Rhythm New rhythm_id or model with embeded error messages.
     */
    public static function insertRhythm($user_id, $name, $description, $full, $mini,
        $version_id, $status_id, $rhythm_cat_id
    ) {
        $rhythm = new Rhythm;
        $rhythm->user_id = $user_id;
        $rhythm->name = $name;
        $rhythm->description = $description;
        $rhythm->mini = $mini;
        $rhythm->full = $full;
        $rhythm->status_id = $status_id;
        $rhythm->version_id = $version_id;
        $rhythm->rhythm_cat_id = $rhythm_cat_id;
        if ($rhythm->validate() === true) {
            $rhythm->save();
            return $rhythm->getPrimaryKey();
        } else {
            return $rhythm;
        }
    }

    /**
     * Returns an alogrithm, along with its details in an array.
     *
     * @param integer $type The type of data to return :
     *                      0 = only header, 1 = full, 2 = minified with no description in header.
     * @param string $site  Domain of the domus for the owner of the alogrithm.
     * @param string $user Owner of the alogrithm.
     * @param string $name Name of the Rhythm.
     * @param integer $major Major version number.
     * @param integer $minor Minor version number.
     * @param integer $patch Patch version number.
     *
     * @return void|string An array Ready to be converted to JSON or void if not found.
     */
    public static function getJSON($type, $site, $user, $name, $major, $minor, $patch) {
        $site_id =SiteMulti::getSiteID($site);

        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($user);

        $model = Rhythm::getByName(
            $user_id,
            $name,
            $major,
            $minor,
            $patch
        );

        if (is_null($model) === true) {
            return;
        }

        $json = array(
            'domain' => $site,
            'username' => $user,
            'name' => $model->name,
            'version' => $model->extra->version->major
                . '/' . $model->extra->version->minor
                . '/' .$model->extra->version->patch,
            'date_created' => strtotime($model->extra->date_created),
            'status' => StatusHelper::getValue($model->extra->status_id),
            'params' => RhythmParam::getForFilter($model->extra->rhythm_extra_id),
        );

        if ($type === 0 || $type === 1) {
            $json['description'] = $model->extra->description;
        }

        if ($type === 1) {
            $json['js'] = $model->extra->full;
        }

        if ($type === 2) {
            $json['js'] = $model->extra->mini;
        }

        return $json;
    }

    /**
     * Get an Rhythm from its name and version.
     *
     * @param integer $user_id Owner of the alogrithm.
     * @param string $name Name of the Rhythm.
     * @param integer $major Major version number.
     * @param integer $minor Minor version number.
     * @param integer $patch Patch version number.
     *
     * @return Rhythm
     */
    public static function getByName($user_id, $name, $major, $minor, $patch) {
        $criteria = new CDbCriteria;
        $criteria->order = 'major DESC, minor DESC, patch DESC';
        $criteria->addCondition('t.user_id=:user_id');
        $criteria->addCondition('t.name=:name');
        $criteria->addCondition('version.family_id=t.rhythm_id');
        $criteria->addCondition('version.type=:version_type');
        $criteria->addCondition('(version.major=:major OR :major IS NULL)');
        $criteria->addCondition('(version.minor=:minor OR :minor IS NULL)');
        $criteria->addCondition('(version.patch=:patch OR :patch IS NULL)');
        $criteria->params = array(
            ':user_id' => $user_id,
            ':name' => $name,
            ':version_type' => LookupHelper::getID('version.type', 'rhythm'),
            ':major' => $major,
            ':minor' => $minor,
            ':patch' => $patch,
        );
        return Rhythm::model()->with('extra', 'extra.rhythm_cat', 'extra.version', 'user')->find($criteria);
    }

    /**
     * Get an Rhythm extra ID from the Rhythms full name.
     *
     * @param integer $user_id The id of the user who owns the Rhythm we are fetching.
     * @param string $name The name of the Rhythm we are fetching.
     * @param integer $major The major version number of the Rhythm we are fetching.
     * @param integer $minor The minor version number of the Rhythm we are fetching.
     * @param integer $patch The patch version number of the Rhythm we are fetching.
     * @param boolean [$domain=false] If this is set an attempt to fetch the stream from its home domain
     *      will be made (If the stream is not found here and this is not the home domain).
     *
     * @return integer|boolean rhythm_extra_id or false
     */
    public static function getIDByName($user_id, $name, $major, $minor, $patch, $domain=false) {
        if ($major === 'latest') {
            $major = false;
        }
        if ($minor === 'latest') {
            $minor = false;
        }
        if ($patch === 'latest') {
            $patch = false;
        }

        $sql = "
            SELECT rhythm_extra.rhythm_extra_id
            FROM
                rhythm
                INNER JOIN rhythm_extra ON rhythm.rhythm_id = rhythm_extra.rhythm_id
                INNER JOIN version ON rhythm_extra.version_id = version.version_id
            WHERE
                rhythm.user_id = :user_id
                AND rhythm.name = :name
                AND version.family_id = rhythm.rhythm_id
                AND version.type = :version_type
                AND (version.major=:major OR :major IS NULL)
                AND (version.minor=:minor OR :minor IS NULL)
                AND (version.patch=:patch OR :patch IS NULL) ";

        if ($user_id !== intval(Yii::app()->user->getId())) {
            $sql .= "AND rhythm_extra.status_id != :status_private ";
        }

        $sql .= "ORDER BY major DESC, minor DESC, patch DESC";

        $command = Yii::app()->db->createCommand($sql);

        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":name", $name, PDO::PARAM_STR);
        if ($major === false) {
            $command->bindValue(":major", null, PDO::PARAM_NULL);
        } else {
            $command->bindValue(":major", $major, PDO::PARAM_INT);
        }
        if ($minor === false) {
            $command->bindValue(":minor", null, PDO::PARAM_NULL);
        } else {
            $command->bindValue(":minor", $minor, PDO::PARAM_INT);
        }
        if ($patch === false) {
            $command->bindValue(":patch", null, PDO::PARAM_NULL);
        } else {
            $command->bindValue(":patch", $patch, PDO::PARAM_INT);
        }
        $command->bindValue(":version_type", LookupHelper::getID('version.type', 'rhythm'), PDO::PARAM_INT);
        $command->bindValue(":status_private", StatusHelper::getID('private'), PDO::PARAM_INT);
        $rhythm_extra_id = $command->queryScalar();
//        if ($domain !== false && $rhythm_extra_id === false) {
//            // !!! fetch from remote domain.
//        }
        if ($rhythm_extra_id === false) {
            return false;
        }
        return (int)$rhythm_extra_id;
    }

    /**
     * Get the version family id for an Rhythm.
     *
     * @param integer $user_id The id of the user that owns the rhythm we are fetching family_id for..
     * @param string $name The name of the Rhythm we are fetching the family version for.
     *
     * @return integer version.family_id
     */
    public static function getVersionFamily($user_id, $name) {
        $sql = "
                SELECT rhythm_id
                FROM rhythm
                WHERE
                    name = :name
                    AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $command->bindValue(':name', $name, PDO::PARAM_STR);
        $rhythm_id = $command->queryScalar();
        if ($rhythm_id === false) {
            throw new Exception('Rhythm Type Family not found');
        }
        return $rhythm_id;
    }

    /**
     * Cache a raw rhythm that is stored in javascript.
     *
     * @param string $json A full JSON reprresentation of the Rhythm.
     *
     * @return integer|string New rhythm_id or error message.
     */
    public static function cacheRhythm($json) {
        // Check this is valid JSON
        $rhythm_array = CJavaScript::jsonDecode($json);
        if (is_null($json_array['error']) === false) {
            return 'Retrieved Rhythm is not in valid JSON format.';
        }
        if (isset($json_array['status']) === false) {
            return 'No status detected in retrieved Rhythm.';
        }
        if ($json_array['status'] !== 'Public' || $json_array['status'] !== 'Deprecated') {
            return 'Retrieved Rhythm status is invalid.';
        }
        if (preg_match('/^(\d+\.)(\d+\.)(\d)?$/', $json_array['version']) === false) {
            return 'Retrieved Rhythm version is invalid.';
        }
        if (isset($json_array['description']) === false) {
            return 'Description is missing in retrieved Rhythm.';
        }
        if (isset($json_array['full']) === false) {
            return 'Rhythm code is missing in the retrieved Rhythm.';
        }
        if (isset($json_array['cat']) === false) {
            return 'Category is missing in the retrieved Rhythm.';
        }
        $user_multi = new UserMulti($site_id);
        if ($user_multi->userExists($user) === true) {
            $user_id = $user_multi->insertRemoteUser($user);
        } else {
            $user_id = $user_multi->getIDFromUsername($user);
        }

        $version_array = explode('/', $json_array['version']);
        $version_id = Version::insertNew(
            LookupHelper::getID('version.type', 'rhythm'),
            null,
            $version_array[0],
            $version_array[1],
            $version_array[2]
        );

        Yii::import('application.components.jsmin');
        $min = JSMin::minify($json_array['full']);

        $model = new Rhythm;
        $model->user_id = $user_id;
        $model->name = $json_array['name'];
        $model->description = $json_array['description'];
        $model->mini = $min;
        $model->full = $json_array['full'];
        $model->status_id = StatusHelper::getID($json_array['status']);
        $model->version_id = $version_id;
        $model->rhythm_cat_id = $rhythm_cat_id;
        $model->save();
        if ($new_rhythm->getErrors() === array()) {
            return $new_rhythm->rhythm_id;
        }
        return 'Unable to store retrieved alogrithm; it is malformed.';
    }

    /**
     * Get any version ID for an Rhythm via its name. (Used to look up version family).
     *
     * @param integer $user_id The id of the user we are geting an Rhythm for.
     * @param string $name The name of the Rhythm we are fetching.
     *
     * @return integer A version_id
     */
    public static function getAnyVersionID($user_id, $name) {
        $row =  Rhythm::model()->find(
            array(
                'select' => 'version_id',
                'condition' => 'name=:name AND user_id=:user_id',
                'params' => array(
                    ':name' => $name,
                    ':user_id' => $user_id,
                )
            )
        );
        return $row->version_id;
    }

    /**
     * Retrieve an Rhythm from its version_id.
     *
     * Note: It is probably possible to remove rhythm_id entirely and use version_id as the PK
     *         but this would make inserting complicated as pk could not auto increment - have to get version first.
     *
     * @param integer $version_id The id of the version we are fetching an Rhythm from.
     *
     * @return integer rhythm_id
     */
    public static function getIDFromVersion($version_id) {
        $rhythm = Rhythm::model()->find(
            array(
                'select' => 'rhythm_id',
                'condition' => 'version_id=:version_id',
                'params' => array(
                    ':version_id' => $version_id,
                )
            )
        );
        if (isset($rhythm) === true) {
            return $rhythm->rhythm_id;
        }
    }

    /**
     * Get an Rhythms ID (rhythm_extra_id) from its url.
     *
     * If neccary, fetch the Rhythm from source
     *
     * @param string $url The url we are fetching an Rhythm id from.
     *
     * @return integer|string Rhythms ID or an error string.
     */
    public static function getIDFromUrl($url) {
        $url = UrlHelper::removeProtocol($url);

        $url_array = explode('/', $url);
        if (count($url_array) < 5 || count($url_array) > 8) {
            return 'Url is malformed.';
        }
        if ($url_array[2] !== 'rhythm') {
            return 'This is not an Rhythm URL.';
        }
        if (isset($url_array[7]) === true && $url_array[7] !== 'json' ) {
            return 'The action must be blank or reference the "json" version of the Rhythm.';
        }

        if (isset($url_array[4]) === false) {
            return 'Major version must be a whole number or "latest"';
        }
        if (isset($url_array[5]) === false) {
            return 'Minor version must be a whole number or "latest"';
        }
        if (isset($url_array[6]) === false) {
            return 'Patch version must be a whole number or "latest"';
        }

        // Check if versions numbers exist and are numeric integers
        // Use zero version for latest. This needs to be tied to a version type to recreate the full version.
        $major = 'latest';
        if ($url_array[4] !== 'latest') {
            if (ctype_digit($url_array[4]) === true) {
                $major = $url_array[4];
            } else {
                return 'Major version must be a whole number or "latest"';
            }
        }
        $minor = 'latest';
        if ($url_array[5] !== 'latest') {
            if (ctype_digit($url_array[5]) === true) {
                $minor = $url_array[5];
            } else {
                return 'Minor version must be a whole number or "latest"';
            }
        }
        $patch = 'latest';
        if ($url_array[6] !== 'latest') {
            if (ctype_digit($url_array[6]) === true) {
                $patch = $url_array[6];
            } else {
                return 'Patch version must be a whole number or "latest"';
            }
        }

        $user = $url_array[1];
        $name = UrlHelper::replacePluses($url_array[3]);

        $site = $url_array[0];
        $site_id = SiteMulti::getSiteID($site, false);
        if ($site_id > 0) {
            $user_multi = new UserMulti($site_id);
            if ($user_multi->userExists($user) === true) {
                $user_id = $user_multi->getIDFromUsername($user);
                $rhythm_id = Rhythm::getIDByName($user_id, $name, $major, $minor, $patch);
                if ($rhythm_id !== 0) {
                    return $rhythm_id;
                } else {
                    return 'Rhythm does not exist.';
                }

            } else {
                return 'User does not exist.';
            }
        }

        if ($site_id === Yii::app()->params['site_id']) {
            return 'Domain is not a BabblingBrook domain.';
        }

        // Rhythm not found locally, try to fetch and cache it.
        $helper = new OtherDomainsHelper($site);
        $full_rhythm = $helper->getRhythm(1, $site, $user, $name, $major, $minor, $patch);

        if ($full_rhythm !== false) {
            Rhythm::cacheRhythm($full_rhythm);
        }

        return 'Unable to retrieve Rhythm from remote site.';
    }

    /**
     * Fetch a drop down list of partial versions for an Rhythm.
     *
     * @param integer $rhythm_extra_id The extra id of the Rhythm we are getting a partial drop down list for.
     *
     * @return string Drop down list.
     */
    public static function getPartialVersionsDropDown($rhythm_extra_id) {
        $rhythm = Rhythm::getByID($rhythm_extra_id);
        $rhythm_versions = Version::getPublicVersions($rhythm->version_id, 'rhythm');
        $rhythm_versions = Version::getPartialVersions($rhythm_versions);
        return CHtml::dropDownList(
            'rhythm_partial_versions',
            '',
            $rhythm_versions,
            array(
                'prompt' => 'Switch versions:',
                'class' => 'version-changed',
            )
        );
    }


    /**
     * Fetch the rhythm_extra_id for this version array and rhythm.
     *
     * @param array(integer) $version_ary An indexed array of version information we using to fetch the Rhythm.
     *                           Contains 3 elements, major, minor and patch version numbers.
     * @param integer $rhythm_id The id of the Rhythm we are fetching.
     * @param boolean $error Should an error be thrown.
     *
     * @return integer rhythm_extra_id
     */
    public static function getExtraIDFromVersion($version_ary, $rhythm_id, $error=true) {
        $model = RhythmExtra::model()->with('version')->find(
            array(
                'condition' => 'version.family_id = :family_id '
                    . 'AND version.major = :major '
                    . 'AND version.minor = :minor '
                    . 'AND version.patch = :patch',
                'params' => array(
                    ':family_id' => $rhythm_id,
                    ':major' => $version_ary[0],
                    ':minor' => $version_ary[1],
                    ':patch' => $version_ary[2],
                )
            )
        );
        if (isset($model) === false) {
            if ($error === true) {
                throw new Excption('No version found.');
            } else {
                return false;
            }
        }
        return $model->rhythm_extra_id;
    }

    /**
     * Returns the family id from the extra ID.
     *
     * @param integer $rhythm_extra_id The extra id of the Rhythm we are getting the family id for.
     *
     * @return integer
     */
    public static function getFamilyID($rhythm_extra_id) {
        $model = RhythmExtra::model()->find(
            array(
                'select' => 'rhythm_id',
                'condition' => 'rhythm_extra_id=:rhythm_extra_id ',
                'params' => array(
                    ':rhythm_extra_id' => $rhythm_extra_id,
                )
            )
        );
        if (isset($model) === false) {
            throw new Exception('Rhythm no found.');
        }
        return $model->rhythm_id;
    }

    /**
     * Is the Rhythm category name valid.
     *
     * @param string $name The name of the category we are checking.
     *
     * @return boolean
     */
    public static function isRhythmCatValid($name) {
        $rhythm_cat = RhythmCat::model()->find(
            array(
                'select' => 'rhythm_cat_id',
                'condition' => 'name=:name',
                'params' => array(
                    ':name' => $name,
                )
            )
        );
        if (isset($rhythm_cat) === false) {
            return false;
        }
        return true;
    }

    /**
     * Fetches a dropdown list of available rhythm categories.
     *
     * @param integer $selected_id The id of the currently selected category.
     * @param boolean $disabled Set to true if the drop down is disabled.
     *
     * @return string
     */
    public static function getRhythmCatDropDownList($selected_id, $disabled=false) {
        $cats = Rhythm::getRhythmCats();
        $select = array(
            'prompt' => 'Choose a category:',
            'class' => 'rhythm-cats',
        );
        if ($disabled === true) {
            $select['disabled'] = 'disabled';
        }
        return CHtml::dropDownList(
            'rhythm_category',
            $selected_id,
            CHtml::listData($cats, 'rhythm_cat_id', 'name'),
            $select
        );
    }

    /**
     * Fetches an array of rhythm_cats.
     *
     * @return RhythmCat
     */
    public static function getRhythmCats() {
        return RhythmCat::model()->findAll(
            array(
                'select' => 'rhythm_cat_id, name',
            )
        );
    }

    /**
     * Fetches the category of an Rhythm.
     *
     * @param integer $rhythm_extra_id The extra id of the Rhythm whoose category we are fetching.
     *
     * @return string
     */
    public static function getRhythmCat($rhythm_extra_id) {
        $sql = '
                SELECT rhythm_cat.name
                FROM rhythm_cat
                    INNER JOIN rhythm_extra ON rhythm_extra.rhythm_cat_id = rhythm_cat.rhythm_cat_id
                WHERE rhythm_extra.rhythm_extra_id = :rhythm_extra_id';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':rhythm_extra_id', $rhythm_extra_id, PDO::PARAM_INT);
        $cat_name = $command->queryScalar();
        return $cat_name;
    }

    /**
     * Get an array of rhythm cat names with rhythm_cat_id as the index.
     *
     * @return array
     */
    public static function getRhythmCatArray() {
        $cats = Rhythm::getRhythmCats();
        $ary = array();
        foreach ($cats as $cat) {
            $ary[$cat->rhythm_cat_id] = $cat->name;
        }
        return $ary;
    }

    /**
     * Return the user kindred rhythms in a json compatible format.
     *
     * @param intger $user_id The id of the user whoose kindred Rhythm we are fetching.
     *
     * @return array
     */
    public static function getUserKindredRhythm($user_id) {
        $sql = '
                SELECT
                     user_rhythm.rhythm_extra_id
                    ,user_rhythm.version_type
                    ,rhythm_extra.version_id
                    ,user_rhythm.user_rhythm_id
                FROM
                    user_rhythm
                    INNER JOIN rhythm_extra ON user_rhythm.rhythm_extra_id = rhythm_extra.rhythm_extra_id
                WHERE
                    user_rhythm.user_id = :user_id
                    AND rhythm_extra.rhythm_cat_id = 2';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $rhythm_versions = $command->queryAll();

        if (isset($rhythm_versions) === false) {
            throw new Exception('No Kindred Rhythm found.');
        }

        $rhythm_array = array();
        foreach ($rhythm_versions as $rhythm_version) {
            $current_version_id = Version::getLatestVersionID(
                $rhythm_version['version_id'],
                LookupHelper::getValue($rhythm_version['version_type']),
                LookupHelper::getID('version.type', 'rhythm')
            );

            $rhythm = RhythmExtra::model()->with(
                array(
                    'rhythm' => array(
                        'select' => 'rhythm_id, rhythm.name',
                    ),
                    'rhythm.user' => array(
                        'select' => 'username',
                    ),
                    'rhythm.user.site' => array(
                        'select' => 'domain',
                    ),
                    'version' => array(
                        'select' => 'major, minor, patch',
                    ),
                )
            )->find(
                array(
                    'select' => 'mini',
                    'condition' => 't.version_id=:version_id',
                    'params' => array(
                        ':version_id' => $current_version_id,
                    )
                )
            );
            if (isset($rhythm) === false) {
                throw new Exception('No Kindred Rhythm found for this version_id : ' . $current_version_id);
            }
            $result = array();
            $result['js'] = $rhythm->mini;
            $result['id'] = $rhythm_version['rhythm_extra_id'];
            $result['user_rhythm_id'] = $rhythm_version['user_rhythm_id'];
            $result['name'] = $rhythm->rhythm->name;
            $result['username'] = $rhythm->rhythm->user->username;
            $result['domain'] = $rhythm->rhythm->user->site->domain;
            $version = Version::makeVersionUrlFromVersionTypeId(
                $rhythm_version['version_type'],
                $rhythm->version->major,
                $rhythm->version->minor,
                $rhythm->version->patch
            );
            $result['version'] = $version;
            $rhythm_array[] = $result;
        }
        return $rhythm_array;
    }


    /**
     * Get the full versions for a Rhythm.
     *
     * @param string $name The name of the Rhythm we are fetching versions for.
     * @param string $domain The domain of the user who owns this family of Rhythms.
     * @param string $username The username of owner of this family of Rhythms.
     *
     * @return array Ready for encoding to JSON.
     */
    public static function getVersions($name, $domain, $username) {
        $rhythm_versions = Rhythm::model()->with(
            array(
                'extra' => array(
                    'select' => 'version_id',
                ),
                'extra.version' => array(
                    'select' => 'major, minor, patch',
                ),
                'user' => array(
                    'select' => 'user_id',
                ),
                'user.site' => array(
                    'select' => 'site_id',
                ),
            )
        )->findAll(
            array(
                'select' => 'rhythm_id',
                'condition' => 't.name=:name AND user.username=:username AND site.domain=:domain',
                'order' => 'major, minor, patch',
                'params' => array(
                    ':name' => $name,
                    ':username' => $username,
                    ':domain' => $domain,
                )
            )
        );

        if (isset($rhythm_versions) === false) {
            throw new Exception('Rhythm not found');
        }

        $versions_ary = array();
        foreach ($rhythm_versions as $version) {
            $versions_ary[]
                = $version->extra->version->major
                . '/' . $version->extra->version->minor
                . '/' . $version->extra->version->patch;
        }


        return $versions_ary;
    }

    /**
     * Get the full and partial versions for a Rhythm.
     *
     * @param string $name The name of the Rhythm we are fetching partial versions for.
     * @param string $domain The domain of the user who owns this family of Rhythms.
     * @param string $username The username of owner of this family of Rhythms.
     *
     * @return array Ready for encoding to JSON.
     */
    public static function getPartialVersions($name, $domain, $username) {

        $versions = Rhythm::getVersions($name, $domain, $username);

        $partial_versions = Version::getPartialVersions($versions);

        return $partial_versions;
    }

    /**
     * Fetch Rhythm search results and export in an array ready for JSON encoding.
     *
     * @param GetSelectionForm $fmodel The form model containing the Rhythm search request.
     *
     * @return array
     */
    public static function generateRhythmSearch($fmodel) {
        if ($fmodel->rhythm_cat_type > '') {
            $rhythm_cat = RhythmCat::model()->find(
                array(
                    'select' => 'rhythm_cat_id',
                    'condition' => 'name=:name',
                    'params' => array(
                        'name' => $fmodel->rhythm_cat_type,
                    )
                )
            );
        }

        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 '' AS type
                ,rhythm.rhythm_id AS id
                ,site.domain AS domain
                ,user.username AS username
                ,rhythm.name AS name ";


        if ($fmodel->show_version === true) {
            $sql .= ",version.major, version.minor, version.patch ";
        }

        $sql .= "FROM rhythm
                INNER JOIN user ON rhythm.user_id = user.user_id
                INNER JOIN site ON site.site_id = user.site_id
                INNER JOIN rhythm_extra ON rhythm.rhythm_id = rhythm_extra.rhythm_id ";

        if ($fmodel->show_version === true) {
            $sql .= "INNER JOIN version ON rhythm_extra.version_id = version.version_id ";
        }

        $sql .= "WHERE
                (rhythm.name LIKE :name OR :emptyname = '')
                AND (user.username LIKE :username OR :emptyusername = '')
                AND (site.domain LIKE :domain OR :emptydomain = '') ";

        if ($fmodel->show_version === true) {
            $sql .= "AND (version.major = :major OR :major = '')
                     AND (version.minor = :minor OR :minor = '')
                     AND (version.patch = :patch OR :patch = '') ";
        }

        if (isset($rhythm_cat) === true) {
            $sql .= ' AND rhythm_cat_id = ' . $rhythm_cat->rhythm_cat_id . ' ';
        }

        $sql .= 'GROUP BY rhythm_extra.rhythm_extra_id ';
        $sql .= 'ORDER BY rhythm.name, user.username, site.domain ';

        if ($fmodel->show_version === true) {
            $sql .= ", version.major DESC, version.minor DESC, version.patch DESC ";
        }
        $sql .= 'LIMIT ' . ($fmodel->page - 1) * $fmodel->rows . ', ' . $fmodel->rows . ';';

        $command = $connection->createCommand($sql);
        $name_filter = '%' . $fmodel->name_filter . '%';
        $user_filter = '%' . $fmodel->user_filter . '%';
        $site_filter = '%' . $fmodel->site_filter . '%';
        $command->bindValue(':name', $name_filter, PDO::PARAM_STR);
        $command->bindValue(':domain', $site_filter, PDO::PARAM_STR);
        $command->bindValue(':username', $user_filter, PDO::PARAM_STR);
        $command->bindValue(':emptyname', $fmodel->name_filter, PDO::PARAM_STR);
        $command->bindValue(':emptydomain', $fmodel->site_filter, PDO::PARAM_STR);
        $command->bindValue(':emptyusername', $fmodel->user_filter, PDO::PARAM_STR);
        if ($fmodel->show_version === true) {
            // If the version is invalid then return an empty result set.
            // This filters out strings in the version filter which otherwise get converted to 0 - causing a
            // match with all 0 versions.
            if (Version::isLookValid($fmodel->version_filter, false) === false) {
                return array();
            }
            $versions = Version::splitPartialVersionString($fmodel->version_filter);
            $command->bindValue(":major", $versions['major'], PDO::PARAM_INT);
            $command->bindValue(":minor", $versions['minor'], PDO::PARAM_INT);
            $command->bindValue(":patch", $versions['patch'], PDO::PARAM_INT);
        }
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Creates an post in the Rhythm meta stream to enable conversation about this Rhythm.
     *
     * @param Stream $model A model of the Rhythm that a meta post is being created for
     *                         Contains exta, extra->version and user sub models.
     */
    public static function createMetaPost($model) {
        $view_title = 'http://' . $model->user->site->domain . '/' . $model->user->username . '/rhythm/view/'
            . $model->name . '/' . $model->extra->version->major . '/'. $model->extra->version->minor
            . '/'. $model->extra->version->patch;
        $view_link = 'http://' . $model->user->site->domain .'/' . rawurlencode($model->user->username)
            . '/rhythm/view/' . rawurlencode($model->name) . '/'. $model->extra->version->major
            . '/'. $model->extra->version->minor . '/'. $model->extra->version->patch;

        $result = PostMulti::insertPost(
            Yii::app()->params['meta_rhythm_extra_id'],
            array(
                array(
                    'display_order' => '1',
                    'text' => 'Discussion about the ' . $model->name . ' Rhythm',
                ),
                array(
                    'display_order' => '2',
                ),
                array(
                    'display_order' => '3',
                    'link_title' => $view_title,
                    'link' => $view_link,
                    'field_type' => LookupHelper::getID('stream_field.field_type', 'link'),
                ),
                array(
                    'display_order' => '4',
                    'text' => $model->extra->description,
                    'field_type' => LookupHelper::getID('stream_field.field_type', 'textbox'),
                ),
            ),
            Yii::app()->user->getId()
        );
        if (is_array($result) === true) {
            throw new Exception("Meta post for Rhythm not submitting. " . ErrorHelper::ary($result));
        } else if ($result === false) {
            throw new Exception("Remote site not accepting new post.  Should never happen as it should be local.");
        } else {
            RhythmExtra::updateMetaPostId($model->extra->rhythm_extra_id, $result->post_id);
        }
    }

    /**
     * Returns a rhythms description from its full name.
     *
     * @param string $domain The rhythms domain.
     * @param string $username The rhythms username.
     * @param string $name The rhythms name.
     * @param array $version Container for the rhythms version numbers.
     * @param string $version.minor The rhythms major version number.
     * @param string $version.minor The rhythms minor domain.
     * @param string $version.patch The rhythms patch domain.
     *
     * @return type
     */
    public static function getDescriptionByName($domain, $username, $name, $version) {
        $sql = "
            SELECT rhythm_extra.description
            FROM
                rhythm
                INNER JOIN rhythm_extra ON rhythm.rhythm_id = rhythm_extra.rhythm_id
                INNER JOIN version ON rhythm_extra.version_id = version.version_id
                INNER JOIN user ON rhythm.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
            WHERE
                user.username = :username
                AND site.domain = :domain
                AND rhythm.name = :name
                AND version.family_id = rhythm.rhythm_id
                AND version.type = :version_type
                AND (version.major=:major OR :major IS NULL)
                AND (version.minor=:minor OR :minor IS NULL)
                AND (version.patch=:patch OR :patch IS NULL) ";

        if ($username !== Yii::app()->user->getName() && $domain !== Yii::app()->user->getDomain()) {
            $sql .= "AND rhythm_extra.status_id != :status_private ";
        }

        $sql .= "ORDER BY major DESC, minor DESC, patch DESC";

        $command = Yii::app()->db->createCommand($sql);

        $command->bindValue(":domain", $domain, PDO::PARAM_STR);
        $command->bindValue(":username", $username, PDO::PARAM_STR);
        $command->bindValue(":name", $name, PDO::PARAM_STR);
        $command->bindValue(":major", $version['major'], PDO::PARAM_INT);
        $command->bindValue(":minor", $version['minor'], PDO::PARAM_INT);
        $command->bindValue(":patch", $version['patch'], PDO::PARAM_INT);
        $command->bindValue(":version_type", LookupHelper::getID('version.type', 'rhythm'), PDO::PARAM_INT);
        $command->bindValue(":status_private", StatusHelper::getID('private'), PDO::PARAM_INT);
        $description = $command->queryScalar();
//        if ($description === false && $domain !== Yii::app()->params['host']) {
//            // !!! fetch remote.
//        }
        return $description;
    }


    /**
     * Delete a rhythm row by its rhythm_id
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $rhythm_id The id of the rhythm used to delete a rhythm row.
     *
     * @return void
     */
    public static function deleteByRhythmId($rhythm_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM rhythm
                WHERE rhythm_id = :rhythm_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":rhythm_id", $rhythm_id, PDO::PARAM_INT);
            $command->execute();
        } catch (Exception $e) {
            throw new Exception(
                'Rhythm::deleteByRhythmId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Select rows of rhythm data for a user.
     *
     * @param integer $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM rhythm
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>