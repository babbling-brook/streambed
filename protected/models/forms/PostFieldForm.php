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
 * Form model to validate Post Fields.
 *
 * @package PHP_Model_Forms
 */
class PostFieldForm extends CFormModel
{

    /**
     *  The model that represents this field.
     *
     * @var StreamField
     */
    private $field_model;

    /**
     * The list options if this is a list.
     *
     * @var array
     */
    private $list_options;

    /**
     *  The contents of the textbox.
     *
     * @var string
     */
    public $textbox;

    /**
     *  The checkboxvalue if this is a checkbox.
     *
     * @var boolean
     */
    public $checkbox;

    /**
     *  The list, if this is a list.
     *
     * @var array
     */
    public $list;



    /**
     * Allows the relevent field to loaded and sets the scenario from it.
     *
     * @param integer $field_id A primary key from the stream_field table.
     */
    public function __construct($field_id) {
        $this->field_model = StreamField::getField($field_id);
        if (isset($this->field_model) === false) {
            Throw new Exception("Stream Field not found");
        }

        switch($this->field_model) {
            case LookupHelper::getID("stream_field.field_type", "textbox"):
                $this->setScenario("textbox");
                break;

            case LookupHelper::getID("stream_field.field_type", "checkbox"):
                $this->setScenario("checkbox");
                break;

            case LookupHelper::getID("stream_field.field_type", "list"):
                $this->setScenario("list");
                // Load the list
                $this->list_options = StreamList::getArray($this->field_model->stream_field_id);
                break;

            case LookupHelper::getID("stream_field.field_type", "value"):
                $this->setScenario("value");
                break;
        }

    }

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array(
                'textbox',
                'match',
                'pattern' => '/^[a-z0-9](?:\x20?[a-z0-9])*$/',
                'message' => $label . ' can only contain lower case letters, digits 0 to 9 and spaces.'
                    . 'It cannot start or end with a space and double spaces are not allowed.',
                'on' => 'textbox',
            ),
            array(
                'textbox',
                'string',
                'max' => $this->field_model->max_size,
                'on' => 'textbox',
                'message' => 'This field is too long. it must be less than '
                    . $this->field_model->max_size . ' characters.',
            ),
            array('textbox', 'ruleTextboxRequired', 'on' => 'textbox'),
            array('textbox', 'ruleFilter', 'on' => 'textbox'),
            array('checkbox', 'boolean', 'on' => 'checkbox'),
            array(
                'list',
                'range',
                'range' => $this->list_options,
                'message' => 'There are invalid items selected.',
                'on' => 'list',
            ),
            array('list', 'ruleListMaxMin', 'on' => 'list'),
        );
    }

    /**
     * Checks that the number of items selected is within bounds.
     *
     * @return void
     */
    public function ruleListMaxMin() {
        if (count($this->list) < $this->field_model->select_qty_min) {
            $this->addError('textbox', 'You need to select more items.');
        }
        if (count($this->list) > $this->field_model->select_qty_max) {
            $this->addError('textbox', 'You have selected too many items.');
        }
    }

    /**
     * Checks if a textbox contains data if it is required.
     *
     * @return void
     */
    public function ruleTextboxRequired() {
        if ((bool)$this->field_model->required === true) {
            if (empty($this->textbox) === true) {
                $this->addError('textbox', 'This field is required.');
            }
        }
    }

    /**
     * Checks if a textbox passes a filter if one is set.
     *
     * @return void
     */
    public function ruleFilter() {
        if ($this->field_model->regex === true && empty($this->field_model->regex) === false) {
            if (preg_match("/" . $this->field_model->regex . "/", $this->textbox) === 0) {
                // @fixme Stream creator needs to be able to enter an error message for filters
                $this->addError('textbox', 'The contents of this field have not passed the filter.');
            }
        }
    }
}

?>