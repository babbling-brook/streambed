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
 * Model to validate that a ring name object is valid.
 *
 * @package PHP_Model_Forms
 */
class RingNameForm extends CFormModel
{
    /**
     * An object that holds the ring name data.
     *
     * @var array $ring
     * string $ring.username The username of the rhythm.
     * string $ring.domain The domain of the rhythm.
     */
    public $ring;

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
     * @var integer The id of the home domain of the ring.
     */
    private $site_id;

    /**
     * @var integer The id of the ring
     */
    private $ring_id;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('ring', 'required'),
            array('check_remote_store', 'boolean', 'trueValue' => true, 'falseValue' => false),
            array('ring', "ruleDomain"),
            array('ring', "ruleUsername"),
            array('ring', "ruleRing", 'on' => 'need_ring_id'),
        );
    }

    /**
     * A rule to check that the domain is valid
     *
     * @return void
     */
    public function ruleDomain() {
        if (isset($this->ring['domain']) === false) {
            $this->addError('ring', 'The rings domain attribute is missing');
        }

        $this->site_id = SiteMulti::getSiteID($this->ring['domain'], true, true);
        if ($this->site_id === false) {
            $this->addError('ring', 'The rings home domain does not exist or is not responding.');
        }
    }

    /**
     * A rule to check that the username is valid.
     *
     * @return void
     */
    public function ruleUsername() {
        if (isset($this->ring['username']) === false) {
            $this->addError('ring', 'The rings username attribute is missing');
        }
        $user_multi = new UserMulti($this->site_id);
        $this->user_id = $user_multi->getIDFromUsername($this->ring['username'], false, true);
        if ($this->user_id === false) {
            $this->addError('ring', 'The rings username does not exist or the ring domain is not responding.');
        }
    }

    /**
     * A rule to check that the username is valid.
     *
     * @return void
     */
    public function ruleRing() {
        $ring = Ring::getFromUserID($this->user_id);
        if ($ring === false) {
            if ($this->user_id === false) {
                $this->addError('ring', 'The user details for the ring are a user, but not a ring.');
            }
        }
        $this->ring_id = $ring['ring_id'];
    }

    /**
     * Fetches the site id after the form has been validated.
     *
     * @return integer The site_id of the ring.
     */
    public function getSiteId() {
        return $this->site_id;
    }

    /**
     * Fetches the user id after the form has been validated.
     *
     * @return integer The user_id of the ring.
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * Fetches the ring id after the form has been validated.
     *
     * @return integer The ring_id of the ring.
     */
    public function getRingId() {
        return $this->ring_id;
    }
}

?>