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
 * Form Model to validate a user or ring url is valid.
 *
 * @package PHP_Model_Forms
 */
class ValidUserForm extends CFormModel
{
    /**
     * The url that represents this user.
     *
     * @var string
     */
    public $url;

    /**
     * Check if the user is a ring or not.
     *
     * @var boolean
     */
    public $check_ring = false;

    /**
     * The domain that the user belongs to.
     *
     * @var integer
     */
    protected $domain;

    /**
     * The username of this user.
     *
     * @var string
     */
    protected $username;

    /**
     * The site_id of this user.
     *
     * @var integer
     */
    protected $site_id;

    /**
     * The user_id of this user.
     *
     * @var integer
     */
    protected $user_id;

    /**
     * Prevents further rules from running.
     *
     * @var boolean
     */
    protected $cancel_rules = false;


    /**
     * Getter for $site_id.
     *
     * @return integer
     */
    public function getSiteId() {
        return $this->site_id;
    }

    /**
     * Getter for $user_id.
     *
     * @return integer
     */
    public function getUserId() {
        return $this->user_id;
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
            array('url', 'required'),
            array('url', 'ruleUrlValid'),           // Generated username and domain
            array('url', 'ruleDomainValid'),        // Generates site_id
            array('url', 'ruleUsernameValid'),      // Generates user_id
            array('url', 'ruleIsRing'),
        );
    }

    /**
     * Checks if the user url is valid.
     *
     * @return void
     */
    public function ruleUrlValid() {
        $url = $this->url;
        // Remove http:// if present at the start of the url
        if (strpos($url, "http://") !== false && strpos($url, "http://") === 0) {
            $url = substr($url, 7);
        }

        $url_parts = explode("/", $url);

        if (count($url_parts) !== 2) {
            $this->addError('url', 'The user url is not valid. It should be in the form domain/username.');
            $this->cancel_rules = true;
            return;
        }

        $this->domain = $url_parts[0];
        $this->username = $url_parts[1];
    }

    /**
     * Checks if a username is valid.
     *
     * @return void
     */
    public function ruleDomainValid() {
        if ($this->cancel_rules === true) {
            return;
        }

        $site_id = SiteMulti::getSiteID($this->domain, true, true);
        if ($site_id === false) {
            $this->addError('url', 'The domain in this user url is not a valid Saltnet site.');
            $this->cancel_rules = true;
        }
        $this->site_id = $site_id;
    }

    /**
     * Checks if a username is unique.
     *
     * @return void
     */
    public function ruleUsernameValid() {
        if ($this->cancel_rules === true) {
            return;
        }

        $user_multi = new UserMulti($this->site_id);
        $user_id= $user_multi->getIDFromUsername($this->username, false, true);
        if ($user_id === false) {
            $this->addError('url', 'The username in this url is not a valid BabblingBrook user.');
            $this->cancel_rules = true;
        }
        $this->user_id = $user_id;
    }

    /**
     * Checks if this user is a ring. Only checks if requested to.
     *
     * @return void
     */
    public function ruleIsRing() {
        if ($this->cancel_rules === true) {
            return;
        }

        if ($this->check_ring === false) {
            return;
        }

        $is_ring= User::isRing($this->domain, $this->username);
        if ($is_ring === false) {
            $this->addError('url', 'This username in not a valid ring.');
            $this->cancel_rules = true;
        }
    }

}

?>
