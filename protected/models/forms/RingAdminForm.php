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
 * Checks if a ring admin is really an admin of a ring.
 *
 * @package PHP_Model_Forms
 */
class RingAdminForm extends CFormModel
{

    /**
     * A user object for the admin user.
     *
     * @var array
     */
    public $admin_user;

    /**
     * A user object for the ring.
     *
     * @var array
     */
    public $ring_user;

    /**
     * The admins password
     *
     * @var string
     */
    public $admin_password;

    /**
     * Should the admins password be checked.
     * @var boolean
     */
    public $check_password = true;

    /**
     * @var integer The user id of the admin user.
     */
    private $admin_user_id;

    /**
     * @var integer The user id of the ring user.
     */
    private $ring_user_id;

    /**
     * @var integer The id of the ring.
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
            array('ring_user, admin_user', 'required'),
            array('admin_user', 'ruleAdminUser'),
            array('ring_user', 'ruleRingUser'),
            array('admin_user', 'ruleUserIsAdmin'),
            array('admin_password', 'ruleAdminPassword'),
        );
    }

    /**
     * Checks if the admin user exists.
     *
     * @return void
     */
    public function ruleAdminUser() {
        $user_name_form = new UserNameForm($this->admin_user);
        $user_valid = $user_name_form->validate();
        if ($user_valid === false) {
            $this->addError('admin_user', 'Admin user is not a valid user.');
        }
        $this->admin_user_id = $user_name_form->getUserId();
    }

    /**
     * Checks if the admin user exists.
     *
     * @return void
     */
    public function ruleRingUser() {
        $this->ring_user['is_ring'] = true;
        $user_name_form = new UserNameForm($this->ring_user);
        $user_valid = $user_name_form->validate();
        if ($user_valid === false) {
            $this->addError('ring_user', 'This ring does not exist.');
        }
        $this->ring_user_id = $user_name_form->getUserId();
        $this->ring_id = Ring::getRingIdFromRingUserId($this->ring_user_id);
    }

    /**
     * Checks if the admin user exists.
     *
     * @return void
     */
    public function ruleUserIsAdmin() {
        $valid = UserRing::isAdmin($this->ring_id, $this->admin_user_id);
        if ($valid === false) {
            $this->addError('admin_user', 'The admin user is not an admin of this ring. Or password is wrong.');
        }
    }

    /**
     * Checks if the admin user password is correct.
     *
     * @return void
     */
    public function ruleAdminPassword() {
        $valid = UserRingPassword::isPasswordValid($this->admin_user_id, $this->ring_user_id, $this->admin_password);
        if ($valid === false) {
            $this->addError('admin_user', 'The admin user is not an admin of this ring. Or password is wrong.');
        }
    }

    /**
     * Getter for the ring id.
     *
     * @return integer The ring id.
     */
    public function getRingId() {
        return $this->ring_id;
    }
}

?>