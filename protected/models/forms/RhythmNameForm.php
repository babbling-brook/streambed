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
 * Model to validate that a rhythm name object is valid.
 *
 * @package PHP_Model_Forms
 */
class RhythmNameForm extends CFormModel
{
    /**
     * An object that holds the rhythm name data.
     *
     * @var array $rhythm
     * string $rhythm.name The name of the rhythm.
     * string $rhythm.username The username of the rhythm.
     * string $rhythm.domain The domain of the rhythm.
     * array $rhythm.version The version of the rhythm.
     * string $rhythm.version.major The major version of the rhythm.
     * string $rhythm.version.major The minor version of the rhythm.
     * string $rhythm.version.major The patch version of the rhythm.
     */
    public $rhythm;

    /**
     * Should the rhythms home data store be checked if the rhythm is not cached locally.
     *
     * @var boolean [$rhythm=false]
     */
    public $check_remote_store=false;

    /**
     * The id of the user that owns the rhythm.
     *
     * @var integer
     */
    private $user_id;

    /**
     * The id that is generated in testing that the rhythm is valid.
     *
     * @var integer
     */
    private $rhythm_extra_id;

    /**
     * Assigns a rhythm object to the rhythm.
     *
     * Call makeRhythmObject after construction to make the rhythm object from parts.
     *
     * @param type $stream
     */
    public function __construct($rhythm=false) {
        if ($rhythm !== false) {
            $this->rhythm = $rhythm;
        }
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
            array('rhythm', 'required'),
            array('check_remote_store', 'boolean', 'trueValue' => true, 'falseValue' => false),
            array('rhythm', "ruleRequired"),
            array('rhythm', "ruleUsername"),
            array('rhythm', "ruleRhythm"),
        );
    }

    /**
     * A rule to check that the date is valid.
     *
     * @return void
     */
    public function ruleRequired() {
        if (isset($this->rhythm['domain']) === false) {
            $this->addError('rhythm', 'The rhythm domain is missing');
        }
        if (isset($this->rhythm['username']) === false) {
            $this->addError('rhythm', 'The rhythm username is missing');
        }
        if (isset($this->rhythm['name']) === false) {
            $this->addError('rhythm', 'The rhythm name is missing');
        }
        if (isset($this->rhythm['version']) === false) {
            $this->addError('rhythm', 'The rhythm version is missing');
        }
        if (isset($this->rhythm['version']['major']) === false) {
            $this->addError('rhythm', 'The major part of the rhythm version is missing');
        }
        if (isset($this->rhythm['version']['minor']) === false) {
            $this->addError('rhythm', 'The minor part of the rhythm version is missing');
        }
        if (isset($this->rhythm['version']['patch']) === false) {
            $this->addError('rhythm', 'The patch part of the rhythm version is missing');
        }
    }

    /**
     * A rule to check that the username is valid.
     *
     * @return void
     */
    public function ruleUsername() {
        if (count($this->getErrors()) === 0) {
            $full_username = $this->rhythm['domain'] . '/' . $this->rhythm['username'];
            $this->user_id = User::getIDFromUsernameAndDomain($this->rhythm['username'], $this->rhythm['domain']);
            if ($this->user_id === false) {
                $this->addError('rhythm', 'The rhythm username is invalid : ' . $full_username);
            }
        }
    }

    /**
     * A rule to check that the date is valid.
     *
     * @return void
     */
    public function ruleRhythm() {
        if (count($this->getErrors()) === 0) {
            $this->rhythm['name'] = str_replace('+', ' ', $this->rhythm['name']);
            $this->rhythm['name'] = str_replace('%20', ' ', $this->rhythm['name']);
            $this->rhythm_extra_id = Rhythm::getIDByName(
                $this->user_id,
                $this->rhythm['name'],
                $this->rhythm['version']['major'],
                $this->rhythm['version']['minor'],
                $this->rhythm['version']['patch'],
                $this->rhythm['domain']
            );
            if ($this->rhythm_extra_id === false) {
                $this->addError('rhythm', 'The rhythm has not been found');
            }
        }
    }

    /**
     * Creates the rhythm object for testing from paramaters.
     *
     * @param string $domain The rhythm domain.
     * @param string $username The rhythm username.
     * @param string $name The rhythm name.
     * @param string $major The rhythm major version.
     * @param string $minor The rhythm minor version.
     * @param string $patch The rhythm patch version.
     */
    public function makeRhythmObject($domain, $username, $name, $major, $minor, $patch) {
        $this->rhythm = array(
            'domain' => $domain,
            'username' => $username,
            'name' => $name,
            'version' => array(
                'major' => $major,
                'minor' => $minor,
                'patch' => $patch,
            ),
        );
    }

    /**
     * Returns the fetched rhythm extra id after the form has been validated.
     *
     * @return integer|false
     */
    public function getRhythmExtraId() {
        return $this->rhythm_extra_id;
    }

    /**
     * Returns the rhythm name object.
     *
     * @return array
     */
    public function getRhythmObject() {
        return $this->rhythm;
    }

    /**
     * Fetches the model of the rhythm name object.
     *
     * @return array
     */
    public function getRhythmModel() {
        $model = Rhythm::getByID($this->rhythm_extra_id);
        return $model;
    }

}

?>