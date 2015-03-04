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
 * Model to validate that a version has a valid format.
 *
 * @package PHP_Model_Forms
 */
class VersionForm extends CFormModel
{
    /**
     * An object that holds the version data.
     *
     * @var array $version
     * $version.major The major version number or 'latest'.
     * $version.minor The minor version number or 'latest'.
     * $version.patch The patch version number or 'latest'.
     */
    public $version;

    /**
     * @var integer Are 'latest' version numbers allowed.
     */
    private $allow_latest;

    /**
     * @var integer Are 'latest' version numbers allowed.
     */
    private $allow_all;

    /**
     * Constructor for the form.
     *
     * @param String $scenario What scenario should be used for this form.
     * @param boolean $allow_latest Should 'latest' version numbers be allowed.
     * @param boolean $allow_all Should 'all' version numbers be allowed.
     */
    public function __construct($scenario=null, $allow_latest=false, $allow_all=false) {
        $this->allow_latest = $allow_latest;
        parent::__construct($scenario);
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
            array('version', 'required'),
            array('version', "ruleVersion"),
        );
    }

    /**
     * A rule to check that the version is valid
     *
     * @return void
     */
    public function ruleVersion() {
        if (isset($this->version['major']) === false) {
            $this->addError('version', 'The major version attribute is missing.');
        }
        if (isset($this->version['minor']) === false) {
            $this->addError('version', 'The minor version attribute is missing.');
        }
        if (isset($this->version['patch']) === false) {
            $this->addError('version', 'The patch version attribute is missing.');
        }

        if ((ctype_digit($this->version['major']) === false)
            && ($this->allow_latest === false || $this->version['major'] !== 'latest')
            && ($this->allow_all === false || $this->version['major'] !== 'all')
        ) {
            $this->addError('version', 'The major version number is not valid.');
        }


        if ((ctype_digit($this->version['minor']) === false)
            && ($this->allow_latest === false || $this->version['minor'] !== 'latest')
            && ($this->allow_all === false || $this->version['minor'] !== 'all')
        ) {
            $this->addError('version', 'The minor version number is not valid.');
        }


        if ((ctype_digit($this->version['patch']) === false)
            && ($this->allow_latest === false || $this->version['patch'] !== 'latest')
            && ($this->allow_all === false || $this->version['patch'] !== 'all')
        ) {
            $this->addError('version', 'The patch version number is not valid.');
        }
    }

    /**
     * Gets the version is string format.
     *
     * @return String The vesion in string format.
     */
    public function getString() {
        return $this->version['major'] . '/' . $this->version['minor'] . '/' . $this->version['patch'];
    }

    /**
     * Gets the version is string format.
     *
     * @return String The vesion in string format.
     */
    public function getWithoutLatest() {
        $without_latest = $this->version;
        if ($without_latest['major'] === 'latest') {
            $without_latest['major'] = '0';
        }
        if ($without_latest['minor'] === 'latest') {
            $without_latest['minor'] = '0';
        }
        if ($without_latest['patch'] === 'latest') {
            $without_latest['patch'] = '0';
        }
        return $without_latest;
    }
}

?>