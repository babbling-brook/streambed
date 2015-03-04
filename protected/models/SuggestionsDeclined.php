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
 * Model for the suggestions_declined DB table.
 * A list of suggestions that a user has declined for a client website.
 *
 * @package PHP_Models
 */
class SuggestionsDeclined extends CActiveRecord
{

    /**
     * The primary key for this row.
     *
     * @var integer
     */
    public $sugestions_declined_id;

    /**
     * The id of the user who declined the suggestion.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The id of the client site that the suggestion was declined on.
     *
     * @var integer
     */
    public $site_id;

    /**
     * The id of the category for this declined suggestion.
     *
     * @var integer
     */
    public $rhythm_cat_id;

    /**
     * If this is a declined rhythm then this is its id.
     *
     * @var integer
     */
    public $declined_rhythm_extra_id;

    /**
     * If this is a declined stream then this is its id.
     *
     * @var integer
     */
    public $declined_stream_extra_id;

    /**
     * If this is a declined user then this is its id.
     *
     * @var integer
     */
    public $declined_user_id;

    /**
     * If this resource has a version type (stream or rhythm) then this is it.
     *
     * @var integer
     */
    public $version_type;

    /**
     * The time that the suggestion was declined.
     *
     * @var integer
     */
    public $date_declined;



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
        return '{{suggestions_declined}}';
    }

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('rhythm_cat_id, user_id, site_id', 'required'),
            array(
                'suggestions_declined_id,
                 user_id,
                 site_id,
                 rhythm_cat_id,
                 declined_rhythm_extra_id,
                 declined_stream_extra_id,
                 user_id,
                 version_type
                 date_declined',
                'length',
                'max' => 11,
            ),
        );
    }


    /**
     * Get all declined suggestions for a user on a client site.
     *
     * @param string $user_id The id of the user to fetch declined suggestions for.
     * @param string $site_id The id of the client site to fetch declined suggestions for.
     * @param string $rhythm_cat_id The id of the rhythm category to fetch declined suggestions for.
     *
     * @return array
     */
    public static function getForUserAndSite($user_id, $site_id, $rhythm_cat_id) {
        // @fixme when converting rhythm_cat (merge into lookup table) change these to the textual names.
        switch($rhythm_cat_id) {
            case 5:
                $suggestions = self::getForStream($user_id, $site_id, $rhythm_cat_id);
                break;

            case 6:
                $suggestions = self::getForRhythm($user_id, $site_id, $rhythm_cat_id);
                break;

            case 7:
                $suggestions = self::getForStream($user_id, $site_id, $rhythm_cat_id);
                break;

            case 9:
                $suggestions = self::getForUser($user_id, $site_id, $rhythm_cat_id);
                break;

            case 10:
                $suggestions = self::getForUser($user_id, $site_id, $rhythm_cat_id);
                break;

            case 11:
                $suggestions = self::getForUser($user_id, $site_id, $rhythm_cat_id);
                break;

            case 12:
                $suggestions = self::getForRhythm($user_id, $site_id, $rhythm_cat_id);
                break;

            case 13:
                $suggestions = self::getForRhythm($user_id, $site_id, $rhythm_cat_id);
                break;

            default:
                throw new Exception('Not a valid rhythm_cat suggetion ' . $rhythm_cat_id);
        }
        return $suggestions;
    }

    /**
     * Get all declined suggestions for a user on a client site for a suggestion type.
     *
     * @param string $user_id The id of the user to fetch declined suggestions for.
     * @param string $site_id The id of the client site to fetch declined suggestions for.
     * @param string $rhythm_cat_id The id of the rhythm category to fetch declined suggestions for.
     *
     * @return boolean
     */
    public static function getForStream($user_id, $site_id, $rhythm_cat_id) {
        $sql = "
            SELECT
                 stream.name
                ,user.username
                ,site.domain
                ,version.major
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS version
                ,suggestions_declined.version_type
            FROM
                suggestions_declined
                INNER JOIN stream_extra
                    ON suggestions_declined.declined_stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user ON stream.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
            WHERE
                suggestions_declined.user_id = :user_id
                AND suggestions_declined.site_id = :site_id
                AND rhythm_cat_id = :rhythm_cat_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":rhythm_cat_id", $rhythm_cat_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Get all declined suggestions for a user on a client site for a suggestion type.
     *
     * @param string $user_id The id of the user to fetch declined suggestions for.
     * @param string $site_id The id of the client site to fetch declined suggestions for.
     * @param string $rhythm_cat_id The id of the rhythm category to fetch declined suggestions for.
     *
     * @return boolean
     */
    public static function getForRhythm($user_id, $site_id, $rhythm_cat_id) {
        $sql = "
            SELECT
                 rhythm.name
                ,user.username
                ,site.domain
                ,version.major
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS version
                ,suggestions_declined.version_type
            FROM
                suggestions_declined
                INNER JOIN rhythm_extra
                    ON suggestions_declined.declined_rhythm_extra_id = rhythm_extra.rhythm_extra_id
                INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id
                INNER JOIN user ON rhythm.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                INNER JOIN version ON rhythm_extra.version_id = version.version_id
            WHERE
                suggestions_declined.user_id = :user_id
                AND suggestions_declined.site_id = :site_id
                AND suggestions_declined.rhythm_cat_id = :rhythm_cat_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":rhythm_cat_id", $rhythm_cat_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Get all declined suggestions for users for a suggestion type.
     *
     * @param string $user_id The id of the user to fetch declined suggestions for.
     * @param string $site_id The id of the client site to fetch declined suggestions for.
     * @param string $rhythm_cat_id The id of the rhythm category to fetch declined suggestions for.
     *
     * @return boolean
     */
    public static function getForUser($user_id, $site_id, $rhythm_cat_id) {
        $sql = "
            SELECT
                 user.username
                ,site.domain
            FROM
                suggestions_declined
                INNER JOIN user ON suggestions_declined.declined_user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
            WHERE
                suggestions_declined.user_id = :user_id
                AND suggestions_declined.site_id = :site_id
                AND rhythm_cat_id = :rhythm_cat_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":rhythm_cat_id", $rhythm_cat_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Accepts a named suggestion and converts it to an id to decline it.
     *
     * Only one of $stream, $rhythm or $user should be filled in.
     *
     * @param type $user_id The id of the user who has declined a suggestion.
     * @param type $client_domain The domain of the client site the suggestion was shown on.
     * @param type $cat
     * @param type $stream
     * @param type $rhythm
     * @param type $user
     *
     * @return array|true An array of errors or true.
     */
    public static function saveByName($user_id, $client_domain, $cat, $stream=null, $rhythm=null, $user=null) {

        $sdf = new SuggestionDeclinedForm;
        $sdf->user_id = $user_id;
        $sdf->client_domain = $client_domain;
        $sdf->cat = $cat;
        $sdf->stream = $stream;
        $sdf->rhythm = $rhythm;
        $sdf->user = $user;
        if ($sdf->validate() === true) {
            $model = new SuggestionsDeclined;
            $model->user_id = $user_id;
            $client_site_id = SiteMulti::getSiteID($client_domain, true, true);
            $model->site_id = $client_site_id;
            $model->rhythm_cat_id = $sdf->getRhythmCatId();
            $model->declined_rhythm_extra_id = $sdf->getDeclinedRhythmExtraId();
            $model->declined_stream_extra_id = $sdf->getDeclinedStreamExtraId();
            $model->declined_user_id = $sdf->getDeclinedUserId();
            $model->version_type = $sdf->getVersionTypeId();
            if ($model->save() === false) {
                return $model->getErrors();
            }
        } else {
            return $sdf->getErrors();
        }
        return true;
    }

    /**
     * Deletes suggestions_declined rows by their declined_stream_extra_id.
     *
     * @param integer $declined_stream_extra_id The id of the stream_extra row that is used to delete these rows.
     *
     * @return void
     */
    public static function deleteByDeclinedStreamExtraId($declined_stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "DELETE FROM suggestions_declined
                WHERE declined_stream_extra_id = :declined_stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":declined_stream_extra_id", $declined_stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes suggestions_declined rows by their user_id.
     *
     * @param integer $user_id The id of the user whose suggestions_declined data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM suggestions_declined
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes suggestions_declined rows by their declined_user_id.
     *
     * @param integer $user_id The id of the user whose suggestions_declined data  for the declined_user_id
     *      is being deleted.
     *
     * @return void
     */
    public static function deleteByDeclinedUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM suggestions_declined
            WHERE declined_user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes suggestions_declined rows by their rhythm_extra_id.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm in suggestions_declined that is being deleted.
     *
     * @return void
     */
    public static function deleteByRhythmExtraId($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM suggestions_declined
            WHERE declined_rhythm_extra_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of suggestions_declined data for a user.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM suggestions_declined
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>