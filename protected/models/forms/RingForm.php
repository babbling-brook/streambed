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
 * Form Model to validate rings.
 *
 * @package PHP_Model_Forms
 */
class RingForm extends CFormModel
{
    /**
     * This is the name of the group and also the username of the user that represents the group.
     *
     * @var string
     */
    public $name;

    /**
     * The type of membership that this group has.
     *
     * @var integer
     */
    public $membership;

    /**
     * The full name of the Rhythm to use to decide membership. Only used if $membership = Rhythm.
     *
     * @var string
     */
    public $membership_rhythm;

    /**
     * The username of the ring that can grant membership privilages to this ring.
     *
     * @var string
     */
    public $membership_super_ring;

    /**
     * The type of administration that this group has.
     *
     * @var integer
     */
    public $admin_type;

    /**
     * The username of the supergroup that is used to manage this group.
     *
     * Supergroup must be on the same domain.
     *
     * @var string
     */
    public $admin_super_ring;

    /**
     * The full url of the Rhythm that all members run.
     *
     * @var string
     */
    public $ring_rhythm;

    /**
     * Is this a new ring or an update.
     *
     * @var boolean
     */
    public $new = true;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('name', 'required'),
            array('membership, admin_type', 'length', 'max' => 10),
            array(
                'name, admin_super_ring, membership_super_ring',
                'match',
                'pattern' => '/^[a-z0-9](?:\x20?[a-z0-9])*$/',
                'message' => 'Can only contain lower case letters, digits 0 to 9 and spaces.'
                    . 'It cannot start or end with a space and double spaces are not allowed.',
            ),
            array('membership', 'ruleMembershipValid'),
            array('membership_rhythm', 'ruleMembershipRhythmValid'),
            array('ring_rhythm', 'ruleRingRhythmValid'),
            array('membership_super_ring', 'ruleMembershipSuperRingValid'),
            array('username', 'ruleIsUserUnique'),
            array('admin_type', 'ruleAdminTypeValid'),
            array('admin_super_ring', 'ruleAdminSuperRingValid'),
        );
    }

    /**
     * Checks if a username is unique.
     *
     * @return void
     */
    public function ruleIsUserUnique() {
        if ($this->hasErrors() === false && $this->new === true) {  // we only want to authenticate when no input errors
            $user_multi = new UserMulti;
            $user_id = $user_multi->getIDFromUsername($this->name, false);
            if ($user_id !== false) {
                $this->addError('name', 'Name is already in use. Please try another.');
            }
        }
    }

    /**
     * Checks if a membership type is valid.
     *
     * @return void
     */
    public function ruleMembershipValid() {
        if (LookupHelper::validId("ring.membership_type", $this->membership, false) === false) {
            $this->addError('membership', 'Please select a membership type.');
        }
    }

    /**
     * Checks if an admin type is valid.
     *
     * @return void
     */
    public function ruleAdminTypeValid() {

        if (LookupHelper::validId("ring.admin_type", $this->admin_type, false) === false) {
            $this->addError('admin_type', 'Please select an Admin Type.');
        }
    }

    /**
     * Checks if an Rhythm is valid for use for membership.
     *
     * @return void
     */
    public function ruleMembershipRhythmValid() {
        if ((int)$this->membership !== LookupHelper::getID('ring.membership_type', 'request')) {
            return;
        }

        if (empty($this->membership_rhythm) === false) {
            $rhythm_id = Rhythm::getIDFromUrl($this->membership_rhythm);
            if (is_int($rhythm_id) === false) {
                $this->addError('membership_rhythm', 'Membership Rhythm is not valid');
            } else {
                $category = Rhythm::getRhythmCat($rhythm_id);
                if ($category !== "ring") {
                    $this->addError(
                        'membership_rhythm',
                        'Membership Rhythm is not valid - needs to be of category "ring".'
                    );
                }
            }
        }
        if (empty($this->membership) === false
            && LookupHelper::getValue($this->membership) === "rhythm"
            && empty($this->membership_rhythm) === true
        ) {
            $this->addError('membership_rhythm', 'Membership Rhythm is empty.');
        }
    }


    /**
     * Checks if an Rhythm is valid for use for membership.
     *
     * @return void
     */
    public function ruleRingRhythmValid() {

        if (empty($this->ring_rhythm) === false) {
            $rhythm_id = Rhythm::getIDFromUrl($this->ring_rhythm);
            if (is_int($rhythm_id) === false) {
                $this->addError('ring_rhythm', 'Ring Rhythm is not valid');
            } else {
                $category = Rhythm::getRhythmCat($rhythm_id);
                if ($category !== "ring") {
                    $this->addError('ring_rhythm', 'Ring Rhythm is not valid - needs to be of category "ring".');
                }
            }
        }
    }

    /**
     * Checks if a membership super ring is valid.
     *
     * @return void
     */
    public function ruleMembershipSuperRingValid() {
        if ((int)$this->membership !== LookupHelper::getID('ring.membership_type', 'super_ring')) {
            return;
        }

        if (empty($this->membership_super_ring) === false) {
            $user_multi = new UserMulti;
            $exists = $user_multi->userExists($this->membership_super_ring);
            if ($exists === false) {
                $this->addError('membership_super_ring', 'Not a valid user');
            }
        }
        if (empty($this->membership) === false
            && LookupHelper::getValue($this->membership) === "super_ring"
            && empty($this->membership_super_ring) === true
        ) {
            $this->addError('membership_super_ring', 'Membership super ring is empty.');
        }
    }

    /**
     * Checks if an admin super ring user is valid.
     *
     * @return void
     */
    public function ruleAdminSuperRingValid() {
        if ((int)$this->admin_type !== LookupHelper::getID('ring.admin_type', 'super_ring')) {
            return;
        }
        if (empty($this->admin_super_ring) === false) {
            $user_multi = new UserMulti;
            $exists = $user_multi->userExists($this->admin_super_ring);
            if ($exists === false) {
                $this->addError('admin_super_ring', 'Not a valid user');
            }
        }
        if (empty($this->admin_type) === false
            && LookupHelper::getValue($this->admin_type) === "super_ring"
            && empty($this->admin_super_ring) === true
        ) {
            $this->addError('admin_super_ring', 'Admin super ring is empty.');
        }
    }

    public function __construct($scenario='') {
        $this->membership = LookupHelper::getID('ring.membership_type', 'invitation');
        $this->admin_type = LookupHelper::getID('ring.admin_type', 'only_me');
        parent::__construct($scenario);
    }

    /**
     * Loads the RingForm from its name.
     *
     * @param string $name The rings username.
     * @param integer|null $site_id Site id of the Ring user. If not given then asumes it is local.
     *
     * @return RingForm
     */
    public static function load($name, $site_id=null) {
        if (isset($site_id) === false) {
            $site_id = Yii::app()->params['site_id'];
        }

        // Load the data
        $sql = "
            SELECT
                 ring.membership_type
                ,ring.membership_rhythm_id
                ,ring.membership_rhythm_version_type
                ,ring.membership_super_ring_user_id
                ,ring.admin_type
                ,ring.admin_super_ring_user_id
                ,ring.ring_rhythm_id
                ,ring.ring_rhythm_version_type
                ,user.username
            FROM ring
                INNER JOIN user ON ring.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
            WHERE
                user.username = :name
                AND site.site_id = :site_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":name", $name, PDO::PARAM_STR);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $ring = $command->queryRow();
        if (isset($ring) === false || $ring === 0) {
            return false;
        }

        // Setup the form
        $ring_from = new RingForm;
        $ring_from->name = $ring['username'];
        $ring_from->membership = $ring['membership_type'];
        $ring_from->admin_type = $ring['admin_type'];

        // Lookup membership Rhythm
        if (LookupHelper::getValue($ring['membership_type']) === "rhythm"
            && empty($ring['membership_rhythm_id']) === false
        ) {
            $rhythm_name = Rhythm::getFullNameFromID(
                $ring['membership_rhythm_id'],
                $ring['membership_rhythm_version_type']
            );
            if ($rhythm_name !== false) {
                $ring_from->membership_rhythm = $rhythm_name;
            }
        }

        // Lookup membership super user username
        $membership_type = LookupHelper::getValue($ring['membership_type']);
        if ($membership_type === "super_ring" && empty($ring['membership_super_ring_user_id']) === false) {
            $user_multi = new UserMulti;
            $username = $user_multi->getUsernameFromID($ring['membership_super_ring_user_id']);
            if ($username !== false) {
                $ring_from->membership_super_ring = $username;
            }
        }

        // Lookup super user username
        if (isset($ring['admin_super_ring_user_id']) === true) {
            $user_multi = new UserMulti;
            $username = $user_multi->getUsernameFromID($ring['admin_super_ring_user_id']);
            if ($username !== false) {
                $ring_from->admin_super_ring = $username;
            }
        }

        // Lookup ring Rhythm
        if (empty($ring['ring_rhythm_id']) === false) {
            $rhythm_name = Rhythm::getFullNameFromID($ring['ring_rhythm_id'], $ring['ring_rhythm_version_type']);
            if ($rhythm_name !== false) {
                $ring_from->ring_rhythm = $rhythm_name;
            }
        }

        $ring_from->new = false;

        return $ring_from;
    }

}

?>
