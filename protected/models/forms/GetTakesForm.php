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
 * Model to validate getTake requests (see UserDataController).
 *
 * @package PHP_Model_Forms
 */
class GetTakesForm extends CFormModel
{

    /**
     * Do we want to include all the values or just the main one.
     *
     * @var boolean
     */
    public $all_values = false;

    /**
     * The user_rhythem_id of the rhythem that takes are being fetched for.
     *
     * @var integer
     */
    public $user_rhythm_id;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('all_values, user_rhythm_id', 'required'),
            array('all_values', 'boolean', 'trueValue' => 'true', 'falseValue' => 'false'),
            array('user_rhythm_id', 'numerical', 'integerOnly' => true, 'min' => 0),
        );
    }
}

?>