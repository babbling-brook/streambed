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
 * Form to validate data to create new signup codes.
 *
 * @package PHP_Model_Forms
 */
class CreateSignupCodesForm extends CFormModel
{
    /**
     * The primary category of the codes to create.
     *
     * @var string $primary_category
     */
    public $primary_category;

    /**
     * The secondary category of the codes to create.
     *
     * @var string $secondary_category
     */
    public $secondary_category;


    /**
     * The quantity of codes to create.
     *
     * @var string $secondary_category
     */
    public $qty;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('primary_category, qty', 'required'),
            array('primary_category, secondary_category', 'length', 'max' => 256),
            array('qty', 'numerical', 'integerOnly' => true, 'min' => 1),
        );
    }

    /**
     * Creates the codes and saves them.
     */
    public function createCodes() {
        if ($this->validate() === false) {
            throw new Exception('Create signups validation failure has not been handled.');
        }
        for ($i = 0; $i < $this->qty; $i++) {
            $model = new SignupCode;
            $model->primary_category = $this->primary_category;
            $model->secondary_category = $this->secondary_category;
            $guid = CryptoHelper::makeGuid();
            $model->code = $guid;
            $model->save();
        }
    }

}

?>