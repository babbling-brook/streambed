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
 * Model to validate requests to search for rhythms.
 *
 * @package PHP_Model_Forms
 */
class RhythmSearchForm extends CFormModel
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
     * A partial name filter. Used to filter a selection by name.
     *
     * @var string
     */
    public $name_filter = '';

    /**
     * A partial version filter. Used to filter a selection by version.
     *
     * @var string
     */
    public $version_filter = '';

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
     * Should the version number be included with this selection.
     *
     * @var boolean
     */
    public $show_version = false;

    /**
     * The type of rhythm category. See rhythm_cat table for valid options.
     *
     * @var string
     */
    public $cat_type = '';

    /**
     * The status of stream.
     *
     * @var string
     */
    public $status;

    /**
     * An array of indexed sort orders.
     *
     * @var array
     */
    public $sort_order;

    /**
     * The major version number or 'major'.
     *
     * @var string
     */
    public $major;

    /**
     * The minor version number or 'minor'.
     *
     * @var string
     */
    public $minor;


    /**
     * The patch version number or 'patch'.
     *
     * @var string
     */
    public $patch;

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
     * @var type
     */
    public $exact_match;

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
            array('show_version, include_test_users', 'boolean', 'trueValue' => true, 'falseValue' => false),
            array('version_filter', 'ruleVersion'),
            array('cat_type', 'ruleCatType'),
            array('status', 'ruleStatus'),
            array('sort_order', 'ruleSortOrder'),
            array('sort_priority', 'ruleSortPriority'),
            array('exact_match', 'ruleExactMatch'),
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
        $valid_keys = array(
            'domain' => false,
            'username' => false,
            'name' => false,
        );
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
    public function ruleCatType() {
        if (isset($this->cat_type) === true && strlen($this->cat_type) > 0) {
            if (RhythmCat::getRhythmCatID($this->cat_type) === false) {
                $this->addError('cat_type', 'Category type is invalid : ' . $this->cat_type);
            }
        } else {
            $this->cat_type = '';
        }
    }

    /**
     * Rule to check if a kind is valid if it is present.
     *
     * @return void
     */
    public function ruleStatus() {
        if (isset($this->status) === true) {
            if (strlen($this->status) > 0
                && StatusHelper::getID($this->status, false) === false
            ) {
                $this->addError('status', 'status is invalid : ' . $this->status);
            }
        }
    }

    /**
     * Check that the version is valid.
     *
     * @return void
     */
    public function ruleVersion() {
        if ($this->version_filter === '') {
            return;
        }

        $version_parts = Version::makeArrayFromString($this->version_filter);
        if ($version_parts === false) {
            $this->addError('version_filter', 'version is invalid : ' . $this->version_filter);
        } else {
            $this->major = $version_parts['major'];
            $this->minor = $version_parts['minor'];
            $this->patch = $version_parts['patch'];
        }
    }

    /**
     * Check that the sort order is valid.
     *
     * @return void
     */
    public function ruleSortOrder() {
        $valid_sort_keys = array('domain', 'username', 'name', 'version', 'status');
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
        $valid_sort_columns = array('domain', 'username', 'name', 'version', 'status');
        foreach ($this->sort_priority as $value) {
            if (in_array($value, $valid_sort_columns) === false) {
                $this->addError('sort_priority', 'invalid sort priority : ' . $value);
            }
            if (isset($this->sort_order[$value]) === false) {
                $this->addError('sort_priority', 'sort priority set for missing sort order : ' . $value);
            }
        }
    }
}

?>
