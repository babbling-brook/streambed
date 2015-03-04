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
 * Model to validate that a Babbling Brook stream filed object is valid.
 * Used to convert the Babbling Brook data into the relational format used in the DB.
 *
 * @package PHP_Model_Forms
 */
class StreamFieldForm extends CFormModel
{

    /**
     * @var integer
     */
    public $stream_extra_id;

    /**
     * @var string
     */
    public $field_type;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $max_size;

    /**
     * @var string
     */
    public $required;

    /**
     * @var string
     */
    public $regex;

    /**
     * @var string
     */
    public $regex_error;

    /**
     * @var string
     */
    public $checkbox_default;

    /**
     * @var string
     */
    public $taken_records;

    /**
     * @var string
     */
    public $display_order;

    /**
     * @var string
     */
    public $value_min;

    /**
     * @var string
     */
    public $value_max;

    /**
     * @var string
     */
    public $value_type;

    /**
     * @var string
     */
    public $value_options;

    /**
     * @var string
     */
    public $select_qty_max;

    /**
     * @var string
     */
    public $select_qty_min;

    /**
     * @var string
     */
    public $rhythm_check_url;

    /**
     * @var string
     */
    public $who_can_take;

    /**
     * @var string
     */
    private $field_model;

    public function beforeValidate() {
        $this->field_model = new StreamField;
        $this->field_model->stream_extra_id = $this->stream_extra_id;
        return parent::beforeValidate();
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
            array('field_type, label, max_size, required, display_order, who_can_take', 'required'),
            array('domain', "ruleType"),
        );
    }

    /**
     * A rule to check that the fields type is valid.
     *
     * Calls additional rules depenedent on the filed type.
     *
     * @return void
     */
    public function ruleType() {
        $valid_type = LookupHelper::getID('stream_field.field_type', $this->field_type, false);
        if ($valid_type === false) {
            $this->addError('domain', 'The field type is not valid. ' . $this->field_type);
            return;
        }
        $this->field_model->setFieldTypeFromText($this->field_type);
        $this->field_model->label = $this->label;
        $this->field_model->display_order = $this->display_order;

        switch ($this->field_type) {
            case 'textbox':
                $this->ruleTextField();
                break;

            case 'link':
                $this->ruleLinkField();
                break;

            case 'checkbox':
                $this->ruleCheckboxField();
                break;

            case 'list':
                $this->ruleListField();
                break;

            case 'openlist':
                $this->ruleOpenListField();
                break;

            case 'value':
                $this->ruleValueField();
                break;
        }
    }

    /**
     * A rule to check that a text field contains vlaid data.
     *
     * @return void
     */
    public function ruleTextField() {
        $this->field_model->setScenario('textbox_update');
        $this->field_model->max_size = $this->max_size;
        $this->field_model->required = $this->required;

        $model->regex = $this->regex;
        $model->regex_error = $this->regex_error;
    }

    /**
     * A rule to check that a text field contains vlaid data.
     *
     * @return void
     */
    public function ruleLinkField() {
        $this->field_model->setScenario('link_update');
        $this->field_model->required = $this->required;
    }

    /**
     * A rule to check that a text field contains vlaid data.
     *
     * @return void
     */
    public function ruleCheckboxField() {
        $this->field_model->setScenario('checkbox_update');
        $this->field_model->checkbox_default = $this->checkbox_default;
    }

    /**
     * A rule to check that a text field contains vlaid data.
     *
     * @return void
     */
    public function ruleListField() {
        $this->field_model->setScenario('list_update');
        $this->field_model->select_qty_min = $this->select_qty_min;
        $this->field_model->select_qty_max = $this->select_qty_max;
    }

    /**
     * A rule to check that a text field contains vlaid data.
     *
     * @return void
     */
    public function ruleOpenListField() {
        $this->field_model->setScenario('openlist_update');
        $this->field_model->select_qty_min = $this->select_qty_min;
        $this->field_model->select_qty_max = $this->select_qty_max;
    }

    /**
     * A rule to check that a text field contains vlaid data.
     *
     * @return void
     */
    public function ruleValueField() {
        $this->field_model->setScenario('value_update');
        $this->field_model->setValueTypeFromText($this->value_type);
        $this->field_model->setValueOptionsFromText($this->value_options);
        $this->field_model->value_max = $this->value_max;
        $this->field_model->value_min = $this->value_min;
        $this->field_model->rhythm_check_url = $this->value_rhythm;
        $this->field_model->setWhoCanTakeFromText($this->who_can_take);
    }

    public function insertField() {
        if ($this->field_model->save() === false) {
            throw new Exception('Stream field failed to save. ' . ErrorHelper::model($this->field_model->getErrors()));
        }
        return true;
    }
}

?>


