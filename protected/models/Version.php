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
 * Model for the version DB table.
 * The table holds version information for any other table.
 *
 * @package PHP_Models
 */
class Version extends CActiveRecord
{

    /**
     * The primary key of the version table.
     *
     * @var integer
     */
    public $version_id;

    /**
     * The primary key of the thing that is being versioned.
     *
     * @var integer
     */
    public $version_family;

    /**
     * The major version number.
     *
     * @var integer
     */
    public $major;

    /**
     * The minor version number.
     *
     * @var integer
     */
    public $minor;

    /**
     * The patch version number.
     *
     * @var integer
     */
    public $patch;

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
        return 'version';
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
            array('family_id', 'required', 'except' => 'composite'),
            array('type, major, minor, patch', 'required'),
            array('family_id, type, major, minor, patch', 'length', 'max' => 10),
            array('major, minor, patch', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'stream_extra' => array(self::HAS_MANY, 'StreamExtra', 'version_id', 'joinType' => 'INNER JOIN'),
            'rhythm_extra' => array(self::HAS_MANY, 'RhythmExtra', 'version_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'major' => 'Major',
            'minor' => 'Minor',
            'patch' => 'Patch',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('version_id', $this->version_id, true);
        $criteria->compare('version_master', $this->version_master, true);
        $criteria->compare('major', $this->major, true);
        $criteria->compare('minor', $this->minor, true);
        $criteria->compare('patch', $this->patch, true);

        return new CActiveDataProvider(
            get_class($this),
            array(
                'criteria' => $criteria,
            )
        );
    }

    /**
     * Return the version from the end of an url.
     *
     * Assumes the version has all three parts present.
     *
     * @param string $url The url to fetch a version from.
     *
     * @return string
     */
    public static function splitFromEndOfUrl($url) {
        $url = UrlHelper::removeProtocol($url);

        $url_parts = explode('/', $url);
        $version = $url_parts[4] . '/' . $url_parts[5] . '/' . $url_parts[6];

        return $version;
    }

    /**
     * Checks if a version string looks valid.
     *
     * Asserts that all three version numbers are present and are whole numbers or 'latest'.
     *
     * @param string $version The version string to check.
     * @param boolean $full If set to false then a partial version is accepted. Eg 1.0
     *
     * @return boolean
     */
    public static function isLookValid($version, $full=true) {

        $parts = explode('/', $version);
        if ($full !== false && count($parts) !== 3) {
            return false;
        }

        if (empty($version) === true) {
            return true;
        }

        foreach ($parts as $part) {
            $valid = Version::isNumberValid($part);
            if ($valid === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a version number is a whole number or 'latest'.
     *
     * @param string $number The version number to check.
     *
     * @return boolean
     */
    protected static function isNumberValid($number) {
        if (ctype_digit($number) === false && $number !== 'latest') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Replace the latest parts of a version in a standard resourse url .
     *
     * @param string $url The full resource url. EG stream url or rhythm url.
     * @param boolean $full Does the url include the domain name.
     *
     * @return string The url with the latest version numnbers replaced with zeros.
     */
    public static function replaceLatest($url, $full=true) {
        $url = UrlHelper::removeProtocol($url);

        $url_parts = explode('/', $url);
        for ($i = 5; $i <= 7; $i++) {
            if ($url_parts[$i] === 'latest') {
                $url_parts[$i] = '0';
            }
        }

        $url = implode("/", $url_parts);
        return $url;
    }

    /**
     * Updates the type and family id for a version.
     *
     * @param integer $version_id The primary key of the version we are updating the type for.
     * @param integer $type The new type. See the lookup table for valid values.
     * @param integer $family_id The Family id of the version type that is being updated.
     *
     * @return void
     */
    public static function updateType($version_id, $type, $family_id) {
        Version::model()->updateByPk(
            $version_id,
            array(
                'family_id' => $family_id,
                'type' => $type,
            )
        );
    }

    /**
     * Check if a particular version already exists.
     *
     * @param integer $family_id The family indentity of the thing who we are checking has a particular verison.
     * @param integer $type The type of family_id who this version belongs to. See lookup table for valid options.
     * @param integer $major A major version number.
     * @param integer $minor A minor version number.
     * @param integer $patch A patch version number.
     *
     * @return boolean
     */
    public static function doesExist($family_id, $type, $major, $minor, $patch) {
        return Version::model()->exists(
            array(
                'condition' => 'family_id=:family_id '
                    . 'AND type=:type '
                    . 'AND major=:major '
                    . 'AND minor=:minor '
                    . 'AND patch=:patch',
                'params' => array(
                    ':family_id' => $family_id,
                    ':type' => $type,
                    ':major' => $major,
                    ':minor' => $minor,
                    ':patch' => $patch,
                )
            )
        );
    }

    /**
     * Find the next major version number for this version family.
     *
     * @param integer $family_id The id of the family that we are creating a new major verison for.
     * @param integer $type The type of family_id who this version belongs to. See lookup table for valid options.
     * @param boolean $throw_error Should an error be thrown or 0 (new version) returned.
     *
     * @return integer Major version number.
     */
    public static function getNextMajor($family_id, $type, $throw_error=true) {
        $version = Version::model()->find(
            array(
                'select' => 'major',
                'condition' => 'family_id=:family_id '
                    . 'AND type=:type ',
                'order' => 'major DESC',
                'params' => array(
                    ':family_id' => $family_id,
                    ':type' => $type,
                )
            )
        );
        if (isset($version) === false) {
            if ($throw_error === true) {
                throw new Exception("Family not found: " . $family_id);
            } else {
                return 0;
            }
        }

        return $version->major + 1;
    }

    /**
     * Check that the version presented is a valid next version.
     *
     * @param integer $family_id The indentity of the thing that is being versioned.
     * @param integer $type Type of family_id.
     * @param string $version Version number.
     *
     * @return boolean True if valid
     */
    public static function checkValidNext($family_id, $type, $version) {
        $version_array = explode("/", $version);
        $major = Version::getNextMajor($family_id, $type);
        if ($version ===  $major . "/0/0") {
            return true;
        }

        $minor = Version::getNextMinor($family_id, $type, $version_array[0]);
        if ($version ===  $version_array[0] . "/" . $minor . "/0") {
            return true;
        }

        $patch = Version::getNextPatch($family_id, $type, $version_array[0], $version_array[1]);
        if ($version ===  $version_array[0] . "/" . $version_array[1] . "/" . $patch) {
            return true;
        }

        return false;
    }

    /**
     * Shortcut function to insert a version from a joined string eg 12.4.2.
     *
     * @param integer $family_id The id of the verison family we are inserting a new version for.
     * @param integer $type The type of family_id. See lookup table for valid options.
     * @param string $version Version in 'major/minor/patch' format.
     *
     * @return integer|string Primary key of new version or error message.
     */
    public static function insertNewFromString($family_id, $type, $version) {
        $version_array = explode("/", $version);
        return Version::insertNew($type, $family_id, $version_array[0], $version_array[1], $version_array[2]);
    }


    /**
     * Inserts a new version of a thing.
     *
     * If an aspect of a version is not passed in then the next available is passed in.
     *
     * @param integer $type The type of family_id we are inserting a version for. See lookup table for valid options.
     * @param integer $family_id The id of the verison family we are inserting a new version for.
     * @param integer $major A major version number.
     * @param integer $minor A minor version number.
     * @param integer $patch A patch version number.
     *
     * @return integer|string Primary key of new version or error message.
     */
    public static function insertNew($type, $family_id=null, $major=null, $minor=null, $patch=null) {
        $version = new Version;

        if ($family_id === null) {
            $major = $minor = $patch = 0;
        }

        if ($major === null) {
            $major = Version::getNextMajor($family_id, $type, false);
        }

        if ($minor === null) {
            $minor = Version::getNextMinor($family_id, $type, $major, false);
        }

        if ($patch === null) {
            $patch = Version::getNextPatch($family_id, $type, $major, $patch, false);
        }

        if (is_null($family_id) === false) {
            if (Version::doesExist($family_id, $type, $major, $minor, $patch) === true) {
                throw new Exception(
                    "Version already exists: " . $family_id . "/" . $major . "/" . $minor . "/" . $patch
                );
            }
        } else {
            $version->scenario = 'composite';
        }

        $version->type = $type;
        $version->family_id = $family_id;
        $version->major = $major;
        $version->minor = $minor;
        $version->patch = $patch;
        $version->save('composite');
        $t = $version->getErrors();
        return $version->getPrimaryKey();
    }

    /**
     * Find the next minor version number for this version family.
     *
     * @param integer $family_id The id of the family that we are creating a new minor verison for.
     * @param integer $type The type of family_id who this version belongs to. See lookup table for valid options.
     * @param integer $major Major version number.
     * @param boolean $throw_error Should an error be thrown or 0 (new version) returned.
     *
     * @return integer Minor version number.
     */
    public static function getNextMinor($family_id, $type, $major, $throw_error=true) {
        $version = Version::model()->find(
            array(
                'select' => 'minor',
                'condition' => 'family_id=:family_id '
                    . 'AND type=:type '
                    . 'AND major=:major',
                'order' => 'minor DESC',
                'params' => array(
                    ':family_id' => $family_id,
                    ':type' => $type,
                    ':major' => $major,
                )
            )
        );
        if (isset($version) === false) {
            if ($throw_error === true) {
                throw new Exception("Family or major version not found: " . $family_id . "/" . $major);
            } else {
                return 0;
            }
        }

        return $version->minor + 1;
    }

    /**
     * Find the next patch version number for this version family.
     *
     * @param integer $family_id The id of the family that we are creating a new patch verison for.
     * @param integer $type The type of family_id who this version belongs to. See lookup table for valid options.
     * @param integer $major Major version number.
     * @param integer $minor Minor version number.
     * @param boolean $throw_error Should an error be thrown or 0 (new version) returned.
     *
     * @return integer Patch version number.
     */
    public static function getNextPatch($family_id, $type, $major, $minor, $throw_error=true) {
        $version = Version::model()->find(
            array(
                'select' => 'patch',
                'order' => 'patch DESC',
                'condition' => 'family_id=:family_id '
                    . 'AND type=:type '
                    . 'AND major=:major '
                    . 'AND minor=:minor',
                'params' => array(
                    ':family_id' => $family_id,
                    ':type' => $type,
                    ':major' => $major,
                    ':minor' => $minor,
                )
            )
        );
        if (isset($version) === false) {
            if ($throw_error === true) {
                throw new Exception(
                    "Family, major or minor version not found: " . $family_id . "/" . $major . "/" . $minor
                );
            } else {
                return 0;
            }
        }

        return $version->patch + 1;
    }


    /**
     * Select the potential next versions for this family and version.
     *
     * @param integer $family_id The id of the family that we are fetching the next possible versions for.
     * @param integer $type The type of family_id who this version belongs to. See lookup table for valid options.
     * @param integer $major A major version number.
     * @param integer $minor A minor version number.
     * @param integer $patch A patch version number.
     * @param boolean $private If set to true then the current version is included.
     *
     * @return array of version numbers.
     */
    public static function getNextVersions($family_id, $type, $major, $minor, $patch, $private=false) {
        $new_patch = $major . "/" . $minor . "/" . Version::getNextPatch($family_id, $type, $major, $minor);
        $new_minor = $major . "/" . Version::getNextMinor($family_id, $type, $major) . "/0";
        $new_major = Version::getNextMajor($family_id, $type) . "/0/0";
        $versions = array(
            $new_patch => $new_patch,
            $new_minor => $new_minor,
            $new_major => $new_major,
        );
        if ($private === true) {
            $current = $major . "/" . $minor . "/" . $patch;
            $current_array = array("No change" => $current . " - No change");
            $versions = array_merge($current_array, $versions);
        }
        return $versions;
    }

    /**
     * Given a version_id, fetch all other versions from the same family.
     *
     * @param integer $version_id The version_id to fetch all familial versions from.
     * @param integer $type The type of family_id who this version belongs to. See lookup table for valid options.
     *
     * @return array of version strings.
     */
    public static function getFamilyVersions($version_id, $type) {
        $family_id = Version::getFamilyID($version_id);

        $rows = Version::model()->findAll(
            array(
                'select' => 'major, minor, patch',
                'order' => 'major DESC, minor DESC, patch DESC',
                'condition' => 'family_id=:family_id AND type=:type',
                'params' => array(
                    ':family_id' => $family_id,
                    ':type' => $type,
                )
            )
        );
        $versions = array();
        foreach ($rows as $row) {
            $version = $row->major . "/" . $row->minor . "/" . $row->patch;
            $versions[$version] = $version;
        }
        return $versions;
    }


    /**
     * Given a version_id, fetch all other versions from the same family that are public or owned by the current user.
     *
     * @param integer $family_id The version_id to fetch all familial public versions from.
     * @param string $type The type of family_id who this version belongs to. See lookup table for valid options.
     *
     * @return array An array of version strings.
     */
    public static function getPublicVersions($family_id, $type) {
        $type_extra = $type . "_extra";

        $rows = Version::model()->with($type_extra, $type_extra . "." .$type)->findAll(
            array(
                'select' => 'major, minor, patch',
                'order' => 'major DESC, minor DESC, patch DESC',
                'condition' => 't.family_id=:family_id '
                    . 'AND t.type=:type '
                    . 'AND ((status_id=:public OR status_id=:deprecated) OR user_id=:user_id)',
                'params' => array(
                    ':family_id' => $family_id,
                    ':type' => LookupHelper::getID("version.type", $type),
                    ':public' => StatusHelper::getID("public"),
                    ':deprecated' => StatusHelper::getID("deprecated"),
                    ':user_id' => Yii::app()->user->getId(),
                )
            )
        );
        $versions = array();
        foreach ($rows as $row) {
            $version = $row->major . "/" . $row->minor . "/" . $row->patch;
            $versions[$version] = $version;
        }
        return $versions;
    }

    /**
     * Create partial versions from array of full versions.
     *
     * @param array $versions Each version is a string in format major/minor/patch.
     *
     * @return array
     */
    public static function getPartialVersions($versions) {
        $v = array();    // Full and partial versions
        $v["latest/latest/latest"] = "latest/latest/latest";
        foreach ($versions as $version) {
            $parts = explode("/", $version);
            $v[$parts[0] . "/latest/latest"] = $parts[0] . "/latest/latest";
            $v[$parts[0] . "/" . $parts[1] . "/latest"] = $parts[0] . "/" . $parts[1] . "/latest";
            $v[$parts[0] . "/" . $parts[1] . "/" . $parts[2]] = $parts[0] . "/" . $parts[1] . "/" . $parts[2];
        }
        return $v;
    }
    /**
     * Get a family_id given a version_id.
     *
     * @param integer $version_id The id of a version we are fetching a family id for.
     *
     * @return integer Version family ID.
     */
    public static function getFamilyID($version_id) {
        $family = Version::model()->findByPk(
            $version_id,
            array(
                'select' => 'family_id',
            )
        );
        return $family->family_id;
    }

    /**
     * Delete a version.
     *
     * @param integer $version_id The id of the version to delete.
     *
     * @return boolean
     */
    public static function deleteByVersionId($version_id) {
        return Version::model()->deleteByPk($version_id);
    }

    /**
     * Get the version model for this version id.
     *
     * @param integer $version_id Teh id of the verison to fetch.
     *
     * @return Version
     */
    public static function getByVersionId($version_id) {
        $version = Version::model()->findByPk($version_id);
        if (isset($version) === false) {
            throw new Exception("Version not found : " . $version_id);
        }
        return $version;
    }

    /**
     * Return the base version of a partial version.
     *
     * @param string $partial The partial version. Eg major/minor/latest.
     *
     * @return string A version.
     */
    public static function getBaseVersion($partial) {
        $full_version = str_replace("latest", "0", $partial);
        // Add additional minor and patch versions if needed.
        if (substr_count($partial, "/") === 0) {
            $full_version .= "/0/0";
        }
        if (substr_count($partial, "/") === 1) {
            $full_version .= "/0";
        }
        return $full_version;
    }

    /**
     * Return the versions type.
     *
     * @param string $partial A partial version number. Eg '4/6/latest'.
     *
     * @return int Version type.
     */
    public static function getTypeId($partial) {
        $version_ary = explode("/", $partial);
        if ($version_ary[0] !== "latest" && $version_ary[0] !== "all") {
            $version_ary[0] = "major";
        }
        if ($version_ary[1] !== "latest" && $version_ary[1] !== "all") {
            $version_ary[1] = "minor";
        }
        if ($version_ary[2] !== "latest" && $version_ary[2] !== "all") {
            $version_ary[2] = "patch";
        }
        $version_type = implode("/", $version_ary);
        $version_type_id = LookupHelper::getID("version_type", $version_type, false);
        return $version_type_id;
    }

    /**
     * Extract the version from a BabblingBrook resource url.
     *
     * @param string $url A full url.
     *
     * @return string A version string.
     */
    public static function getFromUrl($url) {
        // break url into compontents
        // remove http if present
        if (strpos($url, "http://") !== false && strpos($url, "http://") === 0 ) {   // Present AND start of string
            $url = substr($url, 7);
        }
        $url_array = explode("/", $url);

        // !!! this part has been added to handle 'latest' needs editing when all calling methods are
        // converted to use 'latest' instead of an empty string
        if (isset($url_array[5]) === false || $url_array[4] === "") {
            $url_array[5] = "latest";
        }
        if (isset($url_array[6]) === false || $url_array[5] === "") {
            $url_array[6] = "latest";
        }
        if (isset($url_array[7]) === false || $url_array[6] === "") {
            $url_array[7] = "latest";
        }

        // Check if versions numbers exist and are numeric integers
        $major = null;
        if (ctype_digit($url_array[4]) === true || $url_array[4] === "latest") {
            $major = $url_array[4];
        } else {
            return "Major version must be a whole number or 'latest'";
        }
        $minor = null;
        if (ctype_digit($url_array[5]) === true || $url_array[5] === "latest") {
            $minor = $url_array[5];
        } else {
            return "Minor version must be a whole number or 'latest'";
        }
        $patch = null;
        if (ctype_digit($url_array[6]) === true || $url_array[6] === "latest") {
            $patch = $url_array[6];
        } else {
            return "Patch version must be a whole number or 'latest'";
        }

        $version = $major . "/" . $minor . "/" . $patch;
        return $version;
    }

    /**
     * Gets the latest version number for a version id and a type of version.
     *
     * @param integer $version_id The id of the version to get the latest version for.
     * @param string $type The type of 'latest' version to fetch.
     *                     EG major/minor/latest would fetch the latest patch version for this versions major/minor.
     * @param integer $version_type The type of version family.
     *
     * @return The latest version_id for the given type.
     */
    public static function getLatestVersionID($version_id, $type, $version_type) {
        if ($type === "major/minor/patch") {
            return $version_id;
        }

        $version = Version::getByVersionId($version_id);
        if ($type === "major/minor/latest") {
            $latest_version =  Version::getLatestPatchVersion(
                $version->family_id,
                $version->major,
                $version->minor,
                $version_type
            );
        }
        if ($type === "major/latest/latest") {
            $latest_version = Version::getLatestMinorVersion($version->family_id, $version->major, $version_type);
        }
        if ($type === "latest/latest/latest") {
            $latest_version = Version::getLatestMajorVersion($version->family_id, $version_type);
        }
        if (isset($latest_version) === false) {
            throw new Exception("Latest version not found.");
        }

        return $latest_version->version_id;
    }

    /**
     * Fetch the latest patch version for this family, major and minor version.
     *
     * @param integer $family_id The id of the version family from which a patch version is being fetched.
     * @param integer $major A major version number. Used to restrict the search to this version.
     * @param integer $minor A major version number. Used to restrict the search to this version.
     * @param integer $version_type The type of family_id who this version belongs to.
     *                              See lookup table for valid options.
     *
     * @return Version
     */
    public static function getLatestPatchVersion($family_id, $major, $minor, $version_type) {
        $version = Version::model()->find(
            array(
                "condition" => "family_id=:family_id AND major=:major AND minor=:minor AND type=:version_type",
                "order" => "major DESC, minor DESC, patch DESC",
                "params" => array(
                    ":family_id" => $family_id,
                    ":major" => $major,
                    ":minor" => $minor,
                    ":version_type" => $version_type,
                )
            )
        );
        if (isset($version) === false) {
            throw new Exception("Latest patch version not found.");
        }
        return $version;
    }

    /**
     * Fetch the latest minor version for this family and major version.
     *
     * @param integer $family_id The id of the version family from which a minor version is being fetched.
     * @param integer $major A major version number. Used to restrict the search to this version.
     * @param integer $version_type The type of family_id who this version belongs to.
     *                              See lookup table for valid options.
     *
     * @return Version
     */
    public static function getLatestMinorVersion($family_id, $major, $version_type) {
        $version = Version::model()->find(
            array(
                "condition" => "family_id=:family_id AND major=:major AND type=:version_type",
                "order" => "major DESC, minor DESC, patch DESC",
                "params" => array(
                    ":family_id" => $family_id,
                    ":major" => $major,
                    ":version_type" => $version_type,
                )
            )
        );
        if (isset($version) === false) {
            throw new Exception("Latest minor version not found.");
        }
        return $version;
    }

    /**
     * Fetch the latest major version for this family.
     *
     * @param integer $family_id The id of the version family from which a patch version is being fetched.
     * @param integer $version_type The type of family_id who this version belongs to.
     *                              See lookup table for valid options.
     *
     * @return Version
     */
    public static function getLatestMajorVersion($family_id, $version_type) {
        $version = Version::model()->find(
            array(
                "condition" => "family_id=:family_id AND type=:version_type",
                "order" => "major DESC, minor DESC, patch DESC",
                "params" => array(
                    ":family_id" => $family_id,
                    ":version_type" => $version_type,
                )
            )
        );
        if (isset($version) === false) {
            throw new Exception("Latest major version not found.");
        }
        return $version;
    }

    /**
     * Fetches the latest version numbers From a partial verison string.
     *
     * Eg '1/latest/latest will return 1/[the latest minor version number]/[the latest patch version number]
     *
     * @param array Contains 'major', 'minor' and 'patch' version numbers.
     */
    public static function getLatestVersionFromString($version_string, $family_id, $version_type) {
        $version_parts = explode('/', $version_string);
        $version_array = array();
        if ($version_parts[0] === 'latest') {
             $version_model = Version::getLatestMajorVersion($family_id, $version_type);
             $version_array['major'] = $version_model->major;
        } else {
            $version_array['major'] = $version_parts[0];
        }
        if ($version_parts[1] === 'latest') {
            $version_model = Version::getLatestMinorVersion($family_id, $version_array['major'], $version_type);
            $version_array['minor'] = $version_model->minor;
        } else {
            $version_array['minor'] = $version_parts[1];
        }
        if ($version_parts[2] === 'latest') {
            $version_model = Version::getLatestPatchVersion(
                $family_id,
                $version_array['major'],
                $version_array['minor'],
                $version_type
            );
            $version_array['patch'] = $version_model->patch;
        } else {
            $version_array['patch'] = $version_parts[2];
        }

        return $version_array;
    }

    /**
     * Recreates the version part of the url from a version_type_id and the three parts of the verison.
     *
     * @param integer $version_type_id The id of the type of family_id who this version belongs to.
     *                                 See lookup table for valid options.
     * @param integer $major The major part of this version number.
     * @param integer $minor The minor part of this version number.
     * @param integer $patch The patch part of this version number.
     *
     * @return string A version string.
     */
    public static function makeVersionUrlFromVersionTypeId($version_type_id, $major, $minor, $patch) {
        $version = LookupHelper::getValue($version_type_id);

        $version = str_replace("major", $major, $version);
        $version = str_replace("minor", $minor, $version);
        $version = str_replace("patch", $patch, $version);

        return $version;
    }


    /**
     * Recreates the version part of the url from a version_type_id and the three parts of the verison.
     *
     * @param integer $version_type_id The id of the type of family_id who this version belongs to.
     *                                 See lookup table for valid options.
     * @param integer $major The major part of this version number.
     * @param integer $minor The minor part of this version number.
     * @param integer $patch The patch part of this version number.
     *
     * @return string A version string.
     */
    public static function makeVersionFromVersionTypeId($version_type_id, $major, $minor, $patch) {
        $version_string = Version::makeVersionUrlFromVersionTypeId($version_type_id, $major, $minor, $patch);
        $version_parts = explode('/', $version_string);
        $version = array(
            'major' => $version_parts[0],
            'minor' => $version_parts[1],
            'patch' => $version_parts[2],
        );
        return $version;
    }

    /**
     * Recreates the version from a version_type_id and the three parts of the verison.
     *
     * @param integer $version_type_id The id of the type of family_id who this version belongs to.
     *                                 See lookup table for valid options.
     * @param array $version The version to update.
     *
     * @return array The modified version
     */
    public static function makeVersionFromVersionTypeIdAndVersionArray($version_type_id, $version) {
        $version_string = Version::makeVersionUrlFromVersionTypeId(
            $version_type_id,
            $version['major'],
            $version['minor'],
            $version['patch']
        );
        $version_parts = explode('/', $version_string);
        $modified_version = array(
            'major' => $version_parts[0],
            'minor' => $version_parts[1],
            'patch' => $version_parts[2],
        );
        return $modified_version;
    }

    /**
     * Converts a string in the from of major/minor/patch to an array containing major, minor, patch.
     *
     * @param string $version The version string to convert to an array.
     *
     * @return array|false A version array indexed by major, minor and patch. Or false if there is an error.
     */
    public static function makeArrayFromString($version) {
        $exploded_version = explode('/', $version);
        if (count($exploded_version) !== 3) {
            return false;
        }
        if ($exploded_version[0] !== 'major'
            && $exploded_version[0] !== 'latest'
            && ctype_digit($exploded_version[0]) === false
        ) {
            return false;
        }
        if ($exploded_version[1] !== 'minor'
            && $exploded_version[1] !== 'latest'
            && ctype_digit($exploded_version[1]) === false
        ) {
            return false;
        }
        if ($exploded_version[2] !== 'patch'
            && $exploded_version[2] !== 'latest'
            && ctype_digit($exploded_version[2]) === false
        ) {
            return false;
        }

        $version_array = array(
            'major' => $exploded_version[0],
            'minor' => $exploded_version[1],
            'patch' => $exploded_version[2],
        );
        return $version_array;
    }

    /**
     * Splits a version string into components. Missing elements are represented with an empty string.
     *
     * @param string $version The version string we are splitting.
     *
     * @return array cotaining 'major' , 'minor', and 'patch'.
     */
    public static function splitPartialVersionString($version) {
        $ary = explode("/", $version);
        $versions = array();
        if (isset($ary[0]) === true) {
            $versions['major'] = $ary[0];
        } else {
            $versions['major'] = '';
        }
        if (isset($ary[1]) === true) {
            $versions['minor'] = $ary[1];
        } else {
            $versions['minor'] = '';
        }
        if (isset($ary[2]) === true) {
            $versions['patch'] = $ary[2];
        } else {
            $versions['patch'] = '';
        }
        return $versions;
    }

    /**
     * Check if a version string is valid. Can include 'latest' elements.
     *
     * @param string $version The version we are checking is the latest version.
     * @param string $delimiter The version delimiter. Usual values are '.' and '/'.
     *
     * @return boolean
     */
    public static function checkValidLatestVersion($version, $delimiter=".") {
        if (empty($version) === true) {
            return true;
        }

        $version_parts = explode($delimiter, $version);

        if (count($version_parts) > 3) {
            return false;
        }

        foreach ($version_parts as $part) {
            if (ctype_digit($part) === false && $part !== "latest") {
                return false;
            }
        }
        return true;
    }

    /**
     * Generates a drop down list to switch between versions.
     *
     * @param string $action Which action is this for (view|update).
     * @param integer $version_id The base version id to fetch next versions for.
     * @param string $type Text representation of the type.
     *
     * @return CHtml::dropdownList
     */
    public static function switchVersions($action, $version_id, $type) {
        $type = LookupHelper::getID("version.type", $type);    // Convert to type id
        $versions = Version::getVersions($version_id, $type);

        $html = '<select id="versions"><option value="">Switch versions:</option>';
        foreach ($versions as $version) {
            $html .= '<option value="' . $version . '">' . $version . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Given a version_id, fetch all other versions from the same family.
     *
     * @param integer $version_id The version_id to fetch all familial versions from.
     * @param integer $type The type of family_id who this version belongs to. See lookup table for valid options.
     *
     * @return array of version strings.
     */
    public static function getVersions($version_id, $type) {
        $family_id = Version::getFamilyID($version_id);

        $rows = Version::model()->findAll(
            array(
                'select' => 'major, minor, patch',
                'order' => 'major DESC, minor DESC, patch DESC',
                'condition' => 'family_id=:family_id AND type=:type',
                'params' => array(
                    ':family_id' => $family_id,
                    ':type' => $type,
                )
            )
        );
        $versions = array();
        foreach ($rows as $row) {
            $version = $row->major . "/" . $row->minor . "/" . $row->patch;
            $versions[$version] = $version;
        }
        return $versions;
    }

}

?>