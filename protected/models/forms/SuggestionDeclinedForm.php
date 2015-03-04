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
 * Model to validate a declined suggestion and to fetch the numeric
 * data and insert it into the SuggestionDeclined model.
 *
 * @package PHP_Model_Forms
 */
class SuggestionDeclinedForm extends CFormModel
{

    /**
     * The category of suggestion that has been declined
     *
     * @var string
     */
    public $cat;

    /**
     * The id of the user who declined this suggestion.
     *
     * @var number
     */
    public $user_id;

    /**
     * The domain of the client website that this suggestion was declined on.
     *
     * @var number
     */
    public $client_domain;

    /**
     * If this is a declined stream_suggestion, then this is the stream.
     *
     * @var array [$stream]
     * string [$stream.name] The name of the declined stream.
     * string [$stream.username] The username of the declined stream.
     * string [$stream.domain] The domain of the declined stream.
     * array [$stream.version] The version of the declined stream.
     * string [$stream.version.major] The major version of the declined stream.
     * string [$stream.version.major] The minor version of the declined stream.
     * string [$stream.version.major] The patch version of the declined stream.
     */
    public $stream = '';

    /**
     * If this is a declined rhythm suggestion, then this is the rhythm.
     *
     * @var array [$rhythm]
     * string [$rhythm.name] The name of the declined rhythm.
     * string [$rhythm.username] The username of the declined rhythm.
     * string [$rhythm.domain] The domain of the declined rhythm.
     * array [$rhythm.version] The version of the declined rhythm.
     * string [$rhythm.version.major] The major version of the declined rhythm.
     * string [$rhythm.version.major] The minor version of the declined rhythm.
     * string [$rhythm.version.major] The patch version of the declined rhythm.
     */
    public $rhythm = '';

    /**
     * If this is a declined user_suggestion, then this is the user.
     *
     * @var array [$user]
     * string [$user.username] The username of the declined user.
     * string [$user.domain] The domain of the declined user.
     */
    public $user = '';

    /**
     * The type of version eg 2/3/latest, for rhythms and streams. Converted into an id.
     *
     * If not provided, it defaults to the full version.
     *
     * @var [string]
     */
    public $version_type;

    /**
     * Retrieved from the $cat variable. Inserted into suggestions_declined.
     *
     * @var integer
     */
    private $rhythm_cat_id;

    /**
     * Retrieved from the $cat variable. Inserted into suggestions_declined.
     *
     * @var integer
     */
    private $declined_rhythm_extra_id;
    /**
     * Retrieved from the $cat variable. Inserted into suggestions_declined.
     *
     * @var integer
     */
    private $declined_stream_extra_id;

    /**
     * Retrieved from the $cat variable. Inserted into suggestions_declined.
     *
     * @var integer
     */
    private $declined_user_id;

    /**
     * Calculated from the $version_type variable. Inserted into suggestions_declined.
     *
     * @var integer
     */
    private $version_type_id;

    /**
     * Getter for the rhythm_cat_id after the form has been validated.
     */
    public function getRhythmCatId() {
        return $this->rhythm_cat_id;
    }

    /**
     * Getter for $declined_rhythm_extra_id.
     *
     * @returns integer
     */
    public function getDeclinedRhythmExtraId() {
        return $this->declined_rhythm_extra_id;
    }

    /**
     * Getter for $declined_stream_extra_id.
     *
     * @returns integer
     */
    public function getDeclinedStreamExtraId() {
        return $this->declined_stream_extra_id;
    }

    /**
     * Getter for $declined_user_id.
     *
     * @returns integer
     */
    public function getDeclinedUserId() {
        return $this->declined_user_id;
    }


    /**
     * Getter for $version_type_id.
     *
     * @returns integer
     */
    public function getVersionTypeId() {
        return $this->version_type_id;
    }

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('cat', 'required'),
            array('type', 'ruleCategory'),
            array('type', 'ruleWhichType'),
            array('type', 'ruleVersionType'),
        );
    }

    /**
     * Rule to check that the category of suggestion is valid.
     *
     * @return void
     */
    public function ruleCategory() {
        $this->rhythm_cat_id = RhythmCat::getRhythmCatID($this->cat);
        if ($this->rhythm_cat_id === false) {
            $this->addError('cat', 'The suggestion rhythm category is invalid : ' . $this->cat);
        }
    }

    /**
     * Runs extra rules depending on the type of the category.
     *
     * @return void
     */
    public function ruleWhichType() {
        switch($this->cat) {
            case 'stream_suggestion':
                $this->ruleStream();
                break;

            case 'stream_filter_suggestion':
                $this->ruleRhythm();
                break;

            case 'user_stream_suggestion':
                $this->ruleStream();
                break;

            case 'stream_ring_suggestion':
                $this->ruleUser();
                break;

            case 'ring_suggestion':
                $this->ruleUser();
                break;

            case 'user_suggestion':
                $this->ruleUser();
                break;

            case 'meta_suggestion':
                $this->ruleRhythm();
                break;

            case 'kindred suggestion':
                $this->ruleRhythm();
                break;

            default:
                throw new Exception('Not a valid rhythm_cat suggetion');
                break;
        }
    }


    /**
     * Rule to check that the rhythm data is valid and convert it into id data.
     *
     * @return void
     */
    public function ruleRhythm() {
        $rhythm_name_form = new RhythmNameForm;
        $rhythm_name_form->rhythm = $this->rhythm;
        if ($rhythm_name_form->validate() === false) {
            $this->addErrors($rhythm_name_form->getErrors());
            return;
        }
        $this->declined_rhythm_extra_id = $rhythm_name_form->getRhythmExtraId();
        if (isset($this->version_type) === false) {
            $this->version_type = $this->rhythm['version']['major'] . '/' . $this->rhythm['version']['major']
                . '/' . $this->rhythm['version']['patch'];
        }
    }

    /**
     * Rule to check that the stream data is valid and convert it into id data.
     *
     * @return void
     */
    public function ruleStream() {
        $stream_name_form = new StreamNameForm($this->stream);
        if ($stream_name_form->validate() === false) {
            $this->addErrors($stream_name_form->getErrors());
            return;
        }
        $this->declined_stream_extra_id = $stream_name_form->getFirstStreamExtraId();
        if (isset($this->version_type) === false) {
            $this->version_type = $this->stream['version']['major'] . '/' . $this->stream['version']['major']
                . '/' . $this->stream['version']['patch'];
        }
    }


    /**
     * Rule to check that the user data is valid and convert it into id data.
     *
     * @return void
     */
    public function ruleUser() {
        if (isset($this->user['domain']) === false) {
            $this->addError('user', 'The user domain is invalid : ' . $this->user['domain']);
        }
        if (isset($this->user['username']) === false) {
            $this->addError('user', 'The user username is invalid : ' . $this->user['username']);
        }
        $this->declined_user_id = User::getIDFromUsernameAndDomain($this->user['username'], $this->user['domain']);
        if ($this->declined_user_id === false) {
            $full_username = $this->user['domain'] . '/' . $this->user['username'];
            $this->addError('username', 'The username is not found : ' . $this->user['username']);
        }
    }

    /**
     * Rule to check the version type is valid and convert it into an id.
     *
     * Only streams and rhythms have versions.
     *
     * @return void
     */
    public function ruleVersionType() {
        if (isset($this->declined_rhythm_extra_id) !== true && isset($this->declined_stream_extra_id) !== true) {
            return;
        }

        $this->version_type_id = Version::getTypeId($this->version_type);
        if (isset($this->version_type_id) === false) {
            $this->addError('version_type', 'The version_type is invalid : ' . $this->version_type);
        }
    }
}

?>
