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
 * Model to validate requests to search for users.
 *
 * @package PHP_Model_Forms
 */
class UserSearchForm extends CFormModel
{

    /**
     * A partial domain filter. Used to filter a selection by site.
     *
     * @var string
     */
    public $domain_filter = '';

    /**
     * A partial username filter. Used to filter a selection by user.
     *
     * @var string
     */
    public $username_filter = '';

    /**
     * Teh page of results this selection represents.
     *
     * @var integer
     */
    public $page;

    /**
     * The number of rows in a page of this selection.
     *
     * @var integer
     */
    public $row_qty;

    /**
     * The type of user to search for. Valid values are 'user', 'ring'.
     *
     * @var string
     */
    public $user_type = '';

    /**
     * Only return results for rings that allow users to request membership.
     *
     * @var string
     */
    public $only_joinable_rings;

    /**
     * @var boolean Only returns restults for users who are waiting to be vetted by this ring.
     */
    public $users_to_vet_for_ring = false;

    /**
     * An array of indexed sort orders.
     *
     * @var array
     */
    public $sort_order;

    /**
     * An array of columns indexed by sort priority.
     *
     * @var array
     */
    public $sort_priority;

    /**
     * Should test users be included in results.
     *
     * @var boolean
     */
    public $include_test_users = false;

    /**
     * An array of columns. indexed by column name and containing a boolean for if the column search should be an
     * exact match or not.
     *
     * @var type
     */
    public $exact_match;

    /**
     * Resticts the results to just members of this ring.
     *
     * Also causes the 'ban' data to be included in the results.
     *
     * @var string
     */
    public $ring_username;

    /**
     * Resticts the results to just members of this ring.
     *
     * Also causes the 'ban' data to be included in the results.
     *
     * @var string
     */
    public $ring_domain;

    /**
     * Resticts the results to just members of this ring that are 'all', 'banned' or 'memebers'.
     *
     * Also causes the 'ban' data to be included in the results.
     *
     * @var string
     */
    public $ring_ban_filter;

    /**
     * The id of the ring to filter rsults by.
     *
     * This is fetched from the contents of ring_username and ring_domain.
     *
     * @var integer
     */
    public $ring_filter_id;

    /**
     * The id of the ring that is used to filter the results to just the users waiting to be vetted to join a ring.
     *
     * This is fetched from the contents of $users_to_vet_for_ring
     *
     * @var integer
     */
    public $ring_vet_id;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('page, row_qty', 'required'),
            array('page, row_qty', 'numerical', 'message' => 'Please provide a whole number', 'integerOnly' => true),
            array('include_test_users', 'boolean', 'trueValue' => true, 'falseValue' => false),
            array('user_type', 'ruleUserType'),
            array('only_joinable_rings', 'ruleOnlyJoinableRings'),
            array('sort_order', 'ruleSortOrder'),
            array('exact_match', 'ruleExactMatch'),
            array('user_type', 'ruleRingFilter'),
            array('ring_ban_filter', 'ruleRingBanFilter'),
            array('users_to_vet_for_ring', 'ruleRingVetFilter'),
        );
    }

    /**
     * Converts all exact_match values from strings to booleans.
     *
     * Makes an error if the value is not convertable to a boolean.
     *
     * @return void
     */
    public function ruleExactMatch() {
        $valid_keys = array('domain' => false, 'username' => false);
        foreach ($this->exact_match as $key => $value) {
            if (isset($valid_keys[$key]) === false) {
                $this->addError('exact_match', 'Not a valid column name : ' . $this->$key);
            } else {
                $valid_keys[$key] = true;
                if ($this->exact_match[$key] === 'true') {
                    $this->exact_match[$key] = true;
                } else if ($this->exact_match[$key] === 'false') {
                    $this->exact_match[$key] = false;
                } else {
                    $this->addError('exact_match', 'Not a valid boolean string : ' . $value);
                }
            }
        }
        foreach ($valid_keys as $key => $value) {
            if ($value === false) {
                $this->addError('exact_match', 'missing value for : ' . $key);
            }
        }
    }

    /**
     * Rule to check if a category type is valid if it is present.
     *
     * @return void
     */
    public function ruleUserType() {
        if (isset($this->user_type) === true && strlen($this->user_type) > 0) {
            if ($this->user_type !== 'user' && $this->user_type !== 'ring') {
                $this->addError('addError', 'User type is invalid : ' . $this->user_type);
            }
        } else {
            $this->user_type = 'all';
        }
    }


    /**
     * Rule to check if a membership type is valid.
     *
     * @return void
     */
    public function ruleOnlyJoinableRings() {
        if ($this->user_type === 'ring') {
            if (isset($this->only_joinable_rings) === true) {
                if ($this->only_joinable_rings === 'true') {
                    $this->only_joinable_rings = true;
                } else if ($this->only_joinable_rings === 'false') {
                    $this->only_joinable_rings = false;
                } else {
                    $this->addError(
                        'only_joinable_rings', 'only_joinable_rings must be \'true\' or \'false\' : '
                            . $this->only_joinable_rings
                    );
                }
            } else {
                $this->only_joinable_rings = false;
            }
        }
    }

    /**
     * Check that the sort order is valid.
     *
     * @return void
     */
    public function ruleSortOrder() {
        $valid_sort_keys = array('domain', 'username', 'user_type', 'ring_ban');
        $keys_processed = array();
        foreach ($this->sort_order as $key => $value) {
            if (in_array($key, $valid_sort_keys) === false) {
                $this->addError('sort_order', 'invalid sort order key : ' . $key);
            }
            if ($value !== 'ascending' && $value !== 'descending') {
                $this->addError('sort_order', 'invalid sort order value : ' . $value);
            }
            array_push($keys_processed, $key);
        }

        foreach ($valid_sort_keys as $value) {
            if (in_array($value, $keys_processed) === false) {
                $this->addError('sort_order', 'sort_order key is missing : ' . $value);
            }
        }
    }

    /**
     * Check that the sort priority is valid.
     *
     * @return void
     */
    public function ruleSortPriority() {
        $valid_sort_columns = array('domain', 'username', 'user_type', 'ring_ban');
        foreach ($this->sort_priority as $value) {
            if (in_array($value, $valid_sort_columns) === false) {
                $this->addError('sort_priority', 'invalid sort priority : ' . $value);
            }
            if (isset($this->sort_order[$value]) === false) {
                $this->addError('sort_priority', 'sort priority set for missing sort order : ' . $value);
            }
        }
    }

    /**
     * Asserts that the include_test_users value is actually a boolean.
     */
    public function ruleRingFilter() {
        if (strlen($this->ring_username) > 0 && strlen($this->ring_domain) === 0) {
                $this->addError('ring_domain', 'ring_domain must be included if ring_username is.');
        };
        if (strlen($this->ring_domain) > 0 && strlen($this->ring_username) === 0) {
                $this->addError('ring_username', 'ring_username must be included if ring_domain is.');
        };

        if (strlen($this->ring_username) > 0 && strlen($this->ring_domain) > 0) {
            if ($this->ring_domain !== Yii::app()->params['host']) {
                $this->addError(
                    'ring_domain',
                    'Can only use a ring filter if the search is performed on the home domain of the ring..'
                );
            } else {
                $ring_id = Ring::getId($this->ring_username);
                if ($ring_id === false) {
                    $this->addError('ring_username', 'ring not found.');
                } else {
                    $this->ring_filter_id = $ring_id;
                }
            }

        };
    }

    /**
     * Asserts that the ring_ban_filter is valid.
     *
     * @return void
     */
    public function ruleRingBanFilter() {
        if (isset($this->ring_filter_id) === true) {
            $valid_values = array('all', 'members', 'banned');
            if (in_array($this->ring_ban_filter, $valid_values) === false) {
                $this->addError(
                    'ring_ban_filter',
                    'ring_ban_filter value is invalid must be one of \'all\', \'members\' or \'banned\'.'
                );
            }
        }
    }


    /**
     * Asserts that the ring_ban_filter is valid.
     *
     * @return void
     */
    public function ruleRingVetFilter() {
        if ($this->users_to_vet_for_ring !== false) {
            $this->users_to_vet_for_ring['is_ring'] = true;
            $user_name_form = new UserNameForm($this->users_to_vet_for_ring);
            if ($user_name_form->validate() === false) {
                $this->addError(
                    'users_to_vet_for_ring',
                    'users_to_vet_for_ring does not contain a valid ring user object.'
                );
            } else {
                $this->ring_vet_id = Ring::getRingIdFromRingUserId($user_name_form->getUserId());
            }
        }
    }
}

?>
