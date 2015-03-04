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
 * Form Model to validate take requests by ring members.
 *
 * @package PHP_Model_Forms
 */
class RingTakeStatusForm extends CFormModel
{
    /**
     * The domain of the post that is being taken.
     *
     * @var string
     */
    public $post_domain;

    /**
     * The id (local to the post_domain) of the post that is being taken.
     *
     * @var integer
     */
    public $site_post_id;

    /**
     * The field id of the take.
     *
     * @var integer
     */
    public $field_id;

    /**
     * The name of the ring that is taking the post.
     *
     * @var string
     */
    public $ring_name;

    /**
     * The ring password for this member to take this post for this ring.
     *
     * @var string
     */
    public $ring_password;


    /**
     * The domain of the ring member who is requesting a take status.
     *
     * @var string
     */
    public $user_domain;

    /**
     * The username of the ring member who is requesting a take status.
     *
     * @var string
     */
    public $username;

    /**
     * The id of the ring that is taking the post. This is generated.
     *
     * @var integer
     */
    public $ring_id;

    /**
     * The user_id of the ring that is taking the post. This is generated.
     *
     * @var integer
     */
    public $ring_user_id;

    /**
     * The local user_id for the user who is requesting a take.
     *
     * This is generated from the $user_domain and $username.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The local post_id for the post that is being taken. This is generated.
     *
     * @var integer
     */
    public $post_id;

    /**
     * The stream_extra_id that the post was made under. This is generated.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('post_domain, site_post_id, ring_name, ring_password, user_domain, username', 'required'),
            array(
                'ring_name, username',
                'match',
                'pattern' => '/^[a-z0-9](?:\x20?[a-z0-9])*$/',
                'message' => 'Can only contain lower case letters, digits 0 to 9 and spaces.'
                    . 'It cannot start or end with a space and double spaces are not allowed.',
            ),
            array('post_domain', 'ruleGetLocalPostId'),
            array('username', 'ruleGetUserId'),
            array('ring_name', 'ruleGetRingId'),
            array('username', 'ruleCheckPassword'),            // Must appear after ring_user_id and user_id rules
            // these checks need to be after the custom checks, as the attributes may be altered by earlier rules
            // post_id not included here because it is generated in ruleGetLocalPostId and causes a
            // confusing error message if included and ruleGetLocalPostId fails.
            array('ring_id, ring_user_id, user_id, field_id, stream_extra_id', 'required'),
            array(
                'post_id,
                 field_id,
                 stream_extra_id,
                 user_id,
                 ring_id,
                 ring_user_id,
                 site_post_id,',
                'length',
                'max' => 11,
            ),
            array(
                'post_id,
                 field_id,
                 stream_extra_id,
                 user_id,
                 ring_id,
                 ring_user_id,
                 site_post_id',
                'numerical',
                'integerOnly' => true,     // @fixme a lot of models are missing this rule
            ),
        );
    }

    /**
     * Checks if an admin super ring user is valid.
     *
     * @return void
     */
    public function ruleGetLocalPostId() {
        $row = Post::getPostAndTypeId($this->post_domain, $this->site_post_id);
        if (empty($row) === true) {
            $this->addError('post_domain', 'This is not a valid post');
        } else {
            $this->post_id = $row['post_id'];
            $this->stream_extra_id = $row['stream_extra_id'];
        }
    }

    /**
     * Converts the username and user_domain into a local user_id.
     *
     * @return void
     */
    public function ruleGetUserId() {
        $site_id = SiteMulti::getSiteID($this->user_domain, true, true);
        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($this->username, false);
        if ($user_id === false) {
            $this->addError('username', 'Username not found.');
        } else {
            $this->user_id = $user_id;
        }
    }

    /**
     * Fetches the ring id from the ring name and checks it is valid.
     *
     * @return void
     */
    public function ruleGetRingId() {
        $ring_id = Ring::getId($this->ring_name);
        if ($ring_id === 0) {
            $this->addError('ring_name', 'Ring not found.');
        } else {
            $this->ring_id = $ring_id;
            $this->ring_user_id = Ring::getRingUserId($ring_id);
        }
    }

    /**
     * Checks that the password is valid.
     *
     * @return void
     */
    public function ruleCheckPassword() {
        $valid = UserRingPassword::isPasswordValid($this->user_id, $this->ring_user_id, $this->ring_password);
        if ($valid === false) {
            $this->addError('ring_password', 'Ring password does not match.');
        }
    }
}

?>