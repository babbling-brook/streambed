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
 * Model to validate that a user object is valid.
 *
 * @package PHP_Model_Forms
 */
class UserNameForm extends CFormModel
{
    /**
     * An object that holds the user name data.
     *
     * @var array $user
     * $user.username The username of the user.
     * $user.domain The domain of the user.
     * $user.is_ring Is the user a ring.
     */
    public $user;

    /**
     * Should the users home data store be checked if the user is not cached locally.
     *
     * @var boolean [$user=false]
     */
    public $check_remote_store=false;

    /**
     * The id of the user.
     *
     * @var integer
     */
    private $user_id;

    /**
     * Assigns a stram object to the user.
     *
     * Call makeUserObject after construction to make the user object from parts.
     *
     * @param array [$user=false] A user name object.
     */
    public function __construct($user=false) {
        if ($user !== false) {
            $this->user = $user;
        }
        if (isset($this->user['is_ring']) === false) {
            $this->user['is_ring'] = false;
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
            array('user', 'required'),
            array('check_remote_store', 'boolean', 'trueValue' => true, 'falseValue' => false),
            array('user', "ruleUsername"),
            array('user', "ruleIsRing"),
        );
    }

    /**
     * A rule to check that the user username is valid
     *
     * @return void
     */
    public function ruleUsername() {
        if (isset($this->user['domain']) === false) {
            $this->addError('user', 'The users domain is invalid');
        }
        if (isset($this->user['username']) === false) {
            $this->addError('user', 'The users username is invalid.');
        }

        $this->user_id = User::getIDFromUsernameAndDomain(
            $this->user['username'],
            $this->user['domain'],
            $this->check_remote_store
        );
        if ($this->user_id === false) {
            $this->addError('user', 'User not found.');
        }
    }

    /**
     * A rule to check that the user is a ring or not.
     *
     * @return void
     */
    public function ruleIsRing() {
        if ($this->hasErrors()=== true) {
            return;
        }

        $is_ring = User::isRing($this->user['domain'], $this->user['username']);
        if ((bool)$this->user['is_ring'] !== $is_ring) {
            if ((bool)$this->user['is_ring'] === true) {
                $this->addError('user', 'This user account is not a ring.');
            } else {
                $this->addError('user', 'The user account is a ring.');
            }
        }
    }

    /**
     * Creates the user object for testing from paramaters.
     *
     * @param string $domain The user domain.
     * @param string $username The user username.
     * @param boolean $is_ring Is the user a ring.
     */
    public function makeStreamObject($domain, $username, $is_ring) {
        $this->stream = array(
            'domain' => $domain,
            'username' => $username,
            'is_ring' => $is_ring,
        );
    }

    /**
     * Returns the fetched stream extra id after the form has been validated.
     *
     * @return integer|false
     */
    public function getUserId() {
        return $this->user_id;
    }

}

?>