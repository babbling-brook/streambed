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
 * Form Model to validate a request to store results (see RingController).
 *
 * @package PHP_Model_Forms
 */
class RingStoreResults extends CFormModel
{
    /**
     * The username of the user who processed this Rhythm.
     *
     * @var string
     */
    public $username;

    /**
     * The domain of the user who processed this Rhythm.
     *
     * @var integer
     */
    public $domain;

    /**
     * The results of the Rhythm.
     *
     * @var string
     */
    public $computed_data;

    /**
     * Are the results from a 'member' or 'admin' Rhythm.
     *
     * @var string
     */
    public $rhythm_type;

    /**
     * The username of the ring.
     *
     * @var string
     */
    public $ring_username;

    /**
     * The domain of the ring.
     *
     * @var string
     */
    public $ring_domain;

    /**
     * The ring password of the user who has calculated these results.
     *
     * @var string
     */
    public $ring_password;

    /**
     * The username of the Rhythm used to caluculate results.
     *
     * @var string
     */
    public $rhythm_username;

    /**
     * The domain of the Rhythm used to calculate results.
     *
     * @var string
     */
    public $rhythm_domain;

    /**
     * The name of the Rhythm that was used to calculate these results.
     *
     * @var string
     */
    public $rhythm_name;

    /**
     * The version of the Rhythm that was used to calculate these results.
     *
     * @var string
     */
    public $rhythm_version;

    /**
     * The id of the user who produced these results. This is generated when the user is validated.
     *
     * @var integer
     */
    private $user_id;

    /**
     * The id of the ring. This is generated when the ring user is validated.
     *
     * @var integer
     */
    private $ring_user_id;

    /**
     * The extra id of the rhythm that was used to generate these results.
     *
     * This is generated when the rhythm user is validated.
     *
     * @var integer
     */
    private $rhythm_extra_id;

    /**
     * The id of the ring. This is generated when user is check to be a member of this ring.
     *
     * @var integer
     */
    private $ring_id;

    /**
     * The id of the type. Representing if this are results for a member or admin rhythm.
     *
     * @var integer
     */
    private $type_id;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array(
                'username, domain, computed_data, rhythm_type, ring_username, ring_domain, ring_password,
                    rhythm_username, rhythm_domain, rhythm_name, rhythm_version',
                'required',
            ),
            array(
                'username, ring_username, rhythm_username',
                'match',
                'pattern' => '/^[a-z0-9](?:\x20?[a-z0-9])*$/',
                'message' => 'Can only contain lower case letters, digits 0 to 9 and spaces.'
                    . 'It cannot start or end with a space and double spaces are not allowed.',
            ),
            array('rhythm_type', 'ruleRhythmType'),
            array('username', 'ruleUserValid'),                     // Also generates $user_id
            array('ring_username', 'ruleRingUserValid'),            // Also generates $ring_user_id
            array('rhythm_username', 'ruleRhythmValid'),                // Also generates $rhythm_extra_id
            array('username', 'ruleIsUserMemberOfRing'),            // Checks member or admin depending on $rhythm_type
            array('computed_data', 'ruleData'),
            array('ring_password', 'rulePassword'),
        );
    }


    /**
     * Checks that the members password is correct.
     *
     * The results may have been generated on a different domain, we need to be sure they are not being spoofed.
     *
     * @return void
     */
    public function rulePassword() {
        $valid = UserRing::checkPassword($this->ring_id, $this->user_id, $this->ring_password);
        if ($valid === false) {
            $this->addError('ring_password', 'This users ring password is invalid.');
        }
    }

    /**
     * Checks that the data is the corect length.
     *
     * @return void
     */
    public function ruleData() {
        if ($this->rhythm_type === 'admin') {
            if (strtlen($this->data) > Yii::app()->params['max_ring_admin_data_length']) {
                $this->addError(
                    'computed_data',
                    'Invalid data. Length must be less than '
                        . Yii::app()->params['max_ring_admin_data_length'] . ' characters.'
                );
            }
        } else if ($this->rhythm_type === 'member') {
            if (strtlen($this->data) > Yii::app()->params['max_ring_member_data_length']) {
                $this->addError(
                    'computed_data',
                    'Invalid data. Length must be less than '
                        . Yii::app()->params['max_ring_member_data_length'] . ' characters.'
                );
            }
        }
    }

    /**
     * Checks if a rhythm_type is valid.
     *
     * @return void
     */
    public function ruleRhythmType() {
        if ($this->rhythm_type !== "member" && $this->rhythm_type !== "admin") {
            $this->addError("rhythm_type", "rhythm_type must be 'member' or 'admin'");
        }
    }

    /**
     * Checks if a membership type is valid.
     *
     * @return void
     */
    public function ruleUserValid() {
        $user_id = User::getIDFromFullName($this->username . "/" . $this->domain);
        if ($user_id > 0) {
            $this->user_id = $user_id;
        } else {
            $this->addError("username", "User does not exist.");
        }
    }

    /**
     * Checks if the ring user is valid.
     *
     * @return void
     */
    public function ruleRingUserValid() {
        $ring_user_id = User::getIDFromFullName($this->ring_username . "/" . $this->ring_domain);
        if ($ring_user_id > 0) {
            $this->ring_user_id = $ring_user_id;
        } else {
            $this->addError("ring_username", "Ring user does not exist.");
        }
    }

    /**
     * Checks if the Rhythm is valid.
     *
     * @return void
     */
    public function ruleRhythmValid() {
        $url = $this->rhythm_domain . "/" . $this->rhythm_username
            . "/rhythm/json/" . $this->rhythm_name . "/" . $this->rhythm_version;
        $rhythm_extra_id = Rhythm::getIDFromUrl($url);

        if ($rhythm_extra_id > 0) {
            $this->rhythm_extra_id = $rhythm_extra_id;
        } else {
            $this->addError("rhythm_username", "Ring Rhythm does not exist.");
        }
    }


    /**
     * Checks if the user is a member/admin of this ring with permision to run this Rhythm.
     *
     * @return void
     */
    public function ruleIsUserMemberOfRing() {

        $ring = Ring::getFromUserID($this->ring_user_id);

        if ($ring === false) {
            $this->addError('ring_username', 'Ring not found.');
            return;
        }

        $this->ring_id = $ring['ring_id'];

        if ($this->rhythm_type === "member") {
            if ($ring['membership_rhythm_id'] !== $this->rhythm_extra_id) {
                $this->addError('rhythm_name', 'This is not the membership Rhythm of this ring.');
            }

            $valid = $user_ring = UserRing::checkIfMember($ring['ring_id'], $this->user_id);
            if ($valid === false) {
                $this->addError('username', 'This user is not a member of this ring.');
            }

        } else if ($this->rhythm_type === "admin") {
            if ($ring['ring_rhythm_id'] !== $this->rhythm_extra_id) {
                $this->addError('rhythm_name', 'This is not the admin Rhythm of this ring.');
            }

            $valid = UserRing::checkIfAdmin($ring['ring_id'], $this->user_id);
            if ($valid === false) {
                $this->addError('username', 'This user is not an admin of this ring.');
            }
        }
    }

    /**
     * Save this form in the RingRhythmData table.
     */
    public static function save() {
        $model = new RingRhythmData();
        $model->ring_id = $this->ring_id;
        $model->user_id = $this->user_id;
        $model->type_id = $this->type_id;
        $model->data = $this->computed_data;

        $ring_rhythm_data_id = RingRhythmData::getId($this->ring_id, $this->user_id);
        if ($ring_rhythm_data_id !== false) {
            $model->ring_rhythm_data_id = $ring_rhythm_data_id;
            $model->setIsNewRecord(false);
        }
        $model->save();
    }

}

?>