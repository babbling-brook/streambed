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
 * Model for the stream_field DB table.
 * The table holds information about each field in an stream.
 *
 * @package PHP_Models
 */
class StreamField extends CActiveRecord
{

    /**
     * The primary key of this field.
     *
     * @var integer
     */
    public $stream_field_id;

    /**
     * The extra id of the stream that this field belongs to.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The type of this field. See stream_field.field_type in the lookup table for valid options.
     *
     * @var integer
     */
    public $field_type;

    /**
     * The label that introduces this field.
     *
     * @var string
     */
    public $label;

    /**
     * The type of text that a text box uses.
     *
     * THIS IS NOT A TABLE COLUMN. see text_type_id.
     *
     * @var string
     */
    public $text_type;

    /**
     * The id of the type of text that a text box uses. See lookup table.
     *
     * @var string
     */
    public $text_type_id;

    /**
     * If this is a text field, then what is its maximum length.
     *
     * @var integer
     */
    public $max_size;

    /**
     * Is this a required field.
     *
     * @var boolean
     */
    public $required;

    /**
     * An error message if this is a text field with a regex applied to it, and the check fails.
     *
     * @var string
     */
    public $regex_error;

    /**
     * What is the default value of a checkbox if this is a checkbox field.
     *
     * @var boolean
     */
    public $checkbox_default;

    /**
     * When someone takes up this post, do they by default record this value in their own data store.
     *
     * @var integer
     */
    public $taken_records;

    /**
     * The display order of the field.
     *
     * @var integer
     */
    public $display_order;

    /**
     * The minimum allowed value of the value field. If it is a value field.
     *
     * @var integer
     */
    public $value_min;

    /**
     * The maximum allowed value of the value field. If it is a value field.
     *
     * @var integer
     */
    public $value_max;

    /**
     * The id of the type of value field this is if it is a value field.
     *
     * See the lookup table stream_field.value_type for options.
     *
     * @var integer
     */
    public $value_type;

    /**
     * The options id of the type of value field this is if it is a value field.
     *
     * See the lookup table stream_field.value_options for options.
     *
     * @var integer
     */
    public $value_options;

    /**
     * The maximum number of items the user can select if this is a select list.
     *
     * @var integer
     */
    public $select_qty_max;

    /**
     * The minimum number of items the user can select if this is a select list.
     *
     * @var integer
     */
    public $select_qty_min;

    /**
     * The url of an Rhythm used to check takes made on posts before they are accepted.
     *
     * @var string
     */
    public $rhythm_check_url;

    /**
     * Returns the parent model.
     *
     * @param type $className The name of this class.
     *
     * @return Model
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * Getter for the tables name.
     *
     * @return string the associated database table name.
     */
    public function tableName() {
        return 'stream_field';
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
            //array('stream_extra_id, field_type, taken_records, label', 'required'),

            // Shared updates rules
            array(
                'label',
                'length',
                'max' => 255,
                'min' => 1,
                'on' => 'textbox_update, link_update, checkbox_update, list_update, openlist_update, value_update'
                        . 'textbox_create, link_create, checkbox_create, list_create, openlist_create, value_create',
            ),
            array(
                'field_type',
                'ruleFieldType',
                'on' => 'textbox_update, link_update, checkbox_update, list_update, openlist_update, value_update'
                        . 'textbox_create, link_create, checkbox_create, list_create, openlist_create, value_create',
            ),
            array(
                'stream_field_id, label',
                'required',
                'on' => 'textbox_update, link_update, checkbox_update, list_update, openlist_update, value_update',
            ),
            array(
                'stream_extra_id, field_type',
                'numerical',
                'integerOnly' => true,
                'on' => 'textbox_update, link_update, checkbox_update, list_update, openlist_update, value_update'
                        . 'textbox_create, link_create, checkbox_create, list_create, openlist_create, value_create',
            ),

            // Textbox
            array('text_type', 'ruleTextType', 'on' => 'textbox_update, textbox_create'),
            array('max_size', 'ruleMaxLength', 'on' => 'textbox_update, textbox_create'),
            array('required', 'ruleFirstField', 'on' => 'textbox_update, textbox_create'),
            array('required', 'ruleRequired', 'on' => 'textbox_update, textbox_create'),
            //array('required', 'required', 'on' => 'textbox_update, textbox_create'),
            array('regex, regex_error', 'length', 'max' => 255, 'min' => 0, 'on' => 'textbox_update, textbox_create'),

            // Link updates
            array('required', 'ruleRequired', 'on' => 'link_update, link_create'),

            // Checkbox updates
            array('checkbox_default', 'required', 'on' => 'checkbox_update, checkbox_create'),
            array(
                'checkbox_default',
                'boolean',
                'trueValue' => true,
                'falseValue' => false,
                'on' => 'list_update, openlist_update, list_create, openlist_create',
            ),

            // List and Openlist updates
            array(
                'select_qty_min, select_qty_max',
                'required',
                'on' => 'list_update, openlist_update, list_create, openlist_create',
            ),
            array(
                'select_qty_min',
                'numerical',
                'message' => 'Must be a whole number greater than or equal to zero.',
                'integerOnly' => true,
                'min' => 0,
                'on' => 'list_update, openlist_update, list_create, openlist_create',
            ),
            array(
                'select_qty_max',
                'numerical',
                'message' => 'Must be a whole number greater than zero.',
                'integerOnly' => true,
                'min' => 1,
                'on' => 'list_update, openlist_update, list_create, openlist_create',
            ),
            array('select_qty_min', 'ruleSelectWithinRange', 'on' => 'list_update, list_create'),
            array(
                'select_qty_max',
                'ruleSelectMaxGreaterThanMin',
                'on' => 'list_update, openlist_update, list_create, openlist_create',
            ),

            // Value updates
            array(
                'value_type, value_options',
                'numerical',
                'integerOnly' => true,
                'on' => 'value_update, value_create',
            ),
            array('value_type', 'ruleValueType', 'on' => 'value_update, value_create'),
            array('value_options', 'ruleValueOptions', 'on' => 'value_update, value_create'),
        );
    }

    /**
     * Converts the strings 'true' and 'false' to the db values 1 and 0.
     */
    public function ruleRequired() {
        if ($this->required === 'true' || $this->required === true) {
            $this->required = 1;
        } else if ($this->required === 'false' || $this->required === false) {
            $this->required = 0;
        } else if ($this->required !== 1 && $this->required !== 0) {
            $this->addError('required', 'Required must be "true" or "false".');
        }
    }

    /**
     * Ensure defaults are set for the first field.
     */
    public function ruleFirstField() {

        if ($this->display_order === '1') {

            $this->required = 'true';
            $this->regex = "";
            $this->regex_error = "";
        }
    }

    /**
     * Checks that the text type value is correct for textboxes.
     *
     * Also converts it from the textvalue to the
     *
     * @return void
     */
    public function ruleTextType() {
        if (LookupHelper::valid('stream_field.text_type', $this->text_type) === false) {
            $this->addError('text_type', 'Text type is not valid');
        } else {
            $this->text_type_id = LookupHelper::getID('stream_field.text_type', $this->text_type);
        }
    }

    /**
     * Checks that the max size is a positive number.
     *
     * @return void
     */
    public function ruleMaxLength() {
        if ($this->max_size === '' || ctype_digit($this->max_size) === false) {
            $this->addError('max_size', 'Maximum length must be numeric and greater than 0');
        }
    }

    /**
     * Converts an rhythm check url into an id.
     *
     * @return void
     */
    public function ruleRhythmCheck() {

        if (isset($this->rhythm_check_url) === false || empty($this->rhythm_check_url) === true) {
            $this->addError('rhythm_check_url', 'An Rhythm URL is required.');
            return;
        }

        $check = Rhythm::getIDFromUrl($this->rhythm_check_url);
        if (is_numeric($check) === false || $check === 0) {
            $this->addError('rhythm_check_url', 'The Rhythm URL is not valid : ' . $check);
        }
    }

    /**
     * If this is a stars value, then ensure that the min value is zero.
     *
     * @return void
     */
    public function ruleValueLogarithmic() {
        $max = false;
        $min = false;
        for ($i = 1; $i < 13; $i++) {
            if ($this->value_max === pow(10, $i)) {
                $max = true;
            }
            if ($this->value_min === -pow(10, $i)) {
                $min = true;
            }
        }
        if ($max === false) {
            $this->addError('value_max', 'Maximum value has to be a power of 10. eg 10, 100, 1000 etc');
        }
        if ($min === false) {
            $this->addError(
                'value_min',
                'Minimum value has to be zero or a negative power of 10. eg -10, -100, -1000 etc'
            );
        }
    }

    /**
     * Checks the value type is valid.
     *
     * @return void
     */
    public function ruleValueType() {
        if (StreamField::isValueTypeValid($this->value_type) === false) {
            $this->addError('value_type', 'Value type is not valid.');
        }
    }

    /**
     * If this is a value filed, then ensure that the options are correct for the value_type.
     *
     * @return void
     */
    public function ruleValueOptions() {

        // Set defaults for min value and user value options depending on the type selected.
        $min = true;
        $user_value = true;
        $value_option = LookupHelper::getValue($this->value_options, false);
        switch (LookupHelper::getValue($this->value_type, false)) {
            case "updown":
                $min = true;
                $user_value = true;
                break;

            case "linear":
                $min = true;
                $user_value = false;
                break;

            case "logarithmic":
                $min = true;
                $user_value = false;
                if ($value_option === "maxminglobal") {
                    $this->ruleValueLogarithmic();
                }
                break;

            case "textbox":
                $min = true;
                $user_value = true;
                break;

            case "stars":
                $min = false;
                $user_value = false;
                break;

            case "button":
                $min = false;
                $user_value = false;
                break;

            default:
                $this->addError('value_type', 'Value type not found.');
        }

        $value_option_any = LookupHelper::getID("stream_field.value_options", "any");
        if ($user_value === false && $this->value_options === $value_option_any) {
            $this->addError('value_options', 'Value type can not have an option of "any" value.');
        }

        switch ($value_option) {
            case "any":
                $this->value_min = null;
                $this->value_max = null;
                $this->rhythm_check_url = null;
                break;

            case "maxminglobal":
                $this->ruleMaxGreaterThanMin($min);
                $this->rhythm_check_url = null;
                break;

            case "maxminpost":
                $this->value_min = null;
                $this->value_max = null;
                $this->rhythm_check_url = null;
                break;

            case "rhythmglobal":
                $this->value_min = null;
                $this->value_max = null;
                $this->ruleRhythmCheck();
                break;

            case "rhythmpost":
                $this->value_min = null;
                $this->value_max = null;
                $this->rhythm_check_url = null;
                break;

            default:
                $this->addError('value_options', 'Value options not found.');
        }

    }

    /**
     * Rules for max and min values when the field type is a value field.
     *
     * @param {boolean} $min_present Is there a minimum value.
     *
     * @return void
     */
    public function ruleMaxGreaterThanMin($min_present) {
        if (isset($this->value_max) === false) {
            $this->addError('value_max', 'Maximum value is required.');
            return;
        }
        if ($min_present === true && isset($this->value_min) === false) {
            $this->addError('value_min', 'Minimum value is required.');
            return;
        }
        if ($min_present === true && preg_match('/^-?[0-9]+$/', (string)$this->value_min) === 0) {
            $this->addError('value_min', 'Minimum value must be a whole number.');
            return;
        }
        if (preg_match('/^-?[0-9]+$/', (string)$this->value_max) === 0) {
            $this->addError('value_max', 'Maximum value must be a whole number.');
            return;
        }
        if ($min_present === true && $this->value_max <= $this->value_min) {
            $this->addError('value_max', 'Maximum value must be greater than the minimum value.');
        }
        if ($min_present === false && $this->value_max <= 0) {
            $this->addError('value_max', 'Maximum value must be greater than zero.');
        }
    }

    /**
     * Ensure that max and min select quantity values are within the range of items selected.
     *
     * @return void
     */
    public function ruleSelectWithinRange() {
        $qty = StreamList::CountItems($this->stream_field_id);
        if ($this->select_qty_min > $qty - 1) {
            $this->addError(
                'select_qty_min',
                'Minimum value must be at least one less than the number of items in the list (' . $qty . ').'
            );
        }
        if ($this->select_qty_max > $qty) {
            $this->addError(
                'select_qty_max',
                'Maximum value must be less than the number of items in the list (' . $qty . ').'
            );
        }
    }


    /**
     * Rule to ensure that the select list maximum is greater than the minimum.
     *
     * @return void
     */
    public function ruleSelectMaxGreaterThanMin() {
        if ($this->select_qty_max < $this->select_qty_min) {
            $this->addError('select_qty_max', 'Maximum value must greater than or equal to minimum.');
        }
    }


    /**
     * Checks the field type is valid.
     *
     * @return void
     */
    public function ruleFieldType() {
        if (StreamField::isFieldTypeValid($this->field_type) === false) {
            $this->addError('field_type', 'Field type is not valid.');
        }
    }

    /**
     * Sets the type of a field from its Babbling Brook textual value.
     *
     * @param string $type The type of field. Eg textbox, link etc.
     *
     * @return void
     */
    public function setFieldTypeFromText($type) {
        $field_type = LookupHelper::getID('stream_field.field_type', $type, false);
        if ($field_type === false) {
            $this->addError('field_type', 'Field Type is not a valid Babbling Brook value. ' . $type);
        } else {
            $this->field_type = $field_type;
        }
    }

    /**
     * Sets the type of a value field from its Babbling Brook textual value.
     *
     * @param string $value_type The type of value this field is. Eg arrows, button etc.
     *
     * @return void
     */
    public function setValueTypeFromText($value_type) {
        $value_type_id = LookupHelper::getID('stream_field.value_type', $value_type, false);
        if ($value_type_id === false) {
            $this->addError('value_type', 'Value Type is not a valid Babbling Brook value. ' . $value_type);
        } else {
            $this->value_type = $value_type_id;
        }
    }

    /**
     * Sets the option type of a value field from its Babbling Brook textual value.
     *
     * @param string $value_options The value options value for this field. Eg any, maxminglobal etc.
     *
     * @return void
     */
    public function setValueOptionsFromText($value_options) {
        $value_options_id = LookupHelper::getID('stream_field.value_options', $value_options, false);
        if ($value_options_id === false) {
            $this->addError('value_options', 'Field Type is not a valid Babbling Brook value. ' . $value_options);
        } else {
            $this->value_options = $value_options_id;
        }
    }

    /**
     * Sets the who_can_take value field from its Babbling Brook textual value.
     *
     * @param string $who_can_take The who_can_take value this field. Eg anyone, owner etc.
     *
     * @return void
     */
    public function setWhoCanTakeFromText($who_can_take) {
        $who_can_take_id = LookupHelper::getID('stream_field.who_can_take', $who_can_take, false);
        if ($who_can_take_id === false) {
            $this->addError('who_can_take', 'Who Can Take is not a valid Babbling Brook value. ' . $who_can_take);
        } else {
            $this->who_can_take = $who_can_take_id;
        }
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'stream_extra' => array(
                self::BELONGS_TO,
                'StreamExtra',
                'stream_extra_id',
                'joinType' => 'INNER JOIN',
            ),
            'lookup_type' => array(self::BELONGS_TO, 'Lookup', 'field_type', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Checks to do before before saving.
     *
     * @return boolean Whether to save or not.
     */
    public function beforeSave() {
        // Ensure that regex value is always null instead of empty
        if ($this->regex === "") {
            $this->regex = null;
        }
        return true;

        // Ensure select_qty_max defaults to 1
        //if ($this->select_qty_max === "" || $this->select_qty_max)
        //    $this->select_qty_max = 1;
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'stream_field_id' => 'Stream Field',
            'stream__extra_id' => 'Stream',
            'field_type' => 'Field Type',
            'label' => 'Label',
            'max_size' => 'Maximum length',
            'required' => 'Required',
            'regex' => 'Regex',
            'regex_error' => 'Error message if the regular expression fails',
            'checkbox_default' => 'Default',
            'taken_records' => 'Taken Records',
            'value_min' => 'Minimum permited value',
            'value_max' => 'Maximum permited value',
            'select_qty_min' => 'Minimum select quantiy',
            'select_qty_max' => 'Maximum select quantity',
            'rhythm_check' => 'Post Take Check Rhythm',
        );
    }

    /**
     * Updates the type of value for a field type.
     *
     * @param integer $stream_field_id The primary key of the field to update.
     * @param integer $value_type_id The lookup id of the value type to update a field to.
     *
     * @return void
     */
    public static function updateValueType($stream_field_id, $value_type_id) {
        StreamField::model()->updateByPk(
            $stream_field_id,
            array(
                'value_type' => $value_type_id,
            )
        );
    }

    /**
     * Updates the value_options of value for a field type.
     *
     * @param integer $stream_field_id The primary key of the field to update.
     * @param integer $value_options_id The lookup id of the value options to update a field to.
     *
     * @return void
     */
    public static function updateValueOptions($stream_field_id, $value_options_id) {
        StreamField::model()->updateByPk(
            $stream_field_id,
            array(
                'value_options' => $value_options_id,
            )
        );
    }

    /**
     * Fetches the kind of stream that field belongs to.
     *
     * @param integer $field_id  The id of the field to fetch a kind value for.
     *
     * @return integer
     */
    public static function getKind($field_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     stream.kind
                FROM stream_field
                    INNER JOIN stream_extra
                        ON stream_field.stream_extra_id = stream_extra.stream_extra_id
                    INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                WHERE stream_field.stream_field_id = :stream_field_id ";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $field_id, PDO::PARAM_INT);
        $kind_id = $command->queryScalar();
        if ($kind_id === false) {
            throw new Exception("Stream kind not found. stream_field_id = " . $field_id);
        }
        return $kind_id;
    }

    /**
     * Fetches the status of the parent stream.
     *
     * @param integer $field_id  The id of the field to fetch a kind value for.
     *
     * @return string
     */
    public static function getStreamStatus($field_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     stream_extra.status_id
                FROM stream_field
                    INNER JOIN stream_extra
                        ON stream_field.stream_extra_id = stream_extra.stream_extra_id
                WHERE stream_field.stream_field_id = :stream_field_id ";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $field_id, PDO::PARAM_INT);
        $status_id = $command->queryScalar();
        if ($status_id === false) {
            throw new Exception("Stream kind not found. stream_field_id = " . $field_id);
        }
        $status = StatusHelper::getValue($status_id);
        return $status;
    }

    /**
     * Updates the who_can_take value for a field.
     *
     * Will not update the main value field.
     *
     * @param integer $stream_field_id  The id of the field to update.
     * @param string $who_can_take  What value to update the who_can_take value to.
     *
     * @return string
     */
    public static function updateWhoCanTake($stream_field_id, $who_can_take) {
        $who_can_take_id = LookupHelper::getId('stream_field.who_can_edit', $who_can_take);

        $connection = Yii::app()->db;
        $sql = "UPDATE stream_field
                SET who_can_take = :who_can_take_id
                WHERE
                    stream_field_id = :stream_field_id
                    AND display_order > 2";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $command->bindValue(":who_can_take_id", $who_can_take_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Fetches the value for who_can_take a stream_field value row.
     *
     * @param integer $stream_extra_id The extra id of the stream that the post being fetched resides in.
     * @param string $display_order The display order value of the field being checked.
     * @param string $display_order
     *
     * @return string
     */
    public static function getWhoCanTake($stream_extra_id, $display_order) {
        $connection = Yii::app()->db;
        $sql = "SELECT who_can_take
                FROM stream_field
                WHERE
                    stream_extra_id = :stream_extra_id
                    AND display_order = :display_order";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":display_order", $display_order, PDO::PARAM_INT);
        $who_can_take_id = $command->queryScalar();
        $who_can_take = LookupHelper::getValue($who_can_take_id);
        return $who_can_take;
    }

    /**
     * Deletes all the fields in a stream.
     *
     * @param type $stream_extra_id The extra id of the stream to delete fields for.
     *
     * @return void
     */
    public static function deleteFieldsInStream($stream_extra_id) {
        $fields = self::getStreamFields($stream_extra_id);
        foreach ($fields as $field) {
            if ($field->field_type === LookupHelper::getID('stream_field.field_type', 'list')) {
                StreamList::deleteByStreamFieldId($field->stream_field_id);
            }
        }

        $connection = Yii::app()->db;
        $sql = "DELETE
                FROM stream_field
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->execute();
    }


    /**
     * Fetches a streams fields in Yii model format.
     *
     * @param integer $stream_extra_id The Stream ID to return the fields for.
     *
     * @return array An array of StreamField models.
     */
    public static function getStreamFields($stream_extra_id) {
        $rows = StreamField::model()->findAll(
            array(
                'condition' => 'stream_extra_id=:stream_extra_id',
                'order' => 'display_order',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                ),
            )
        );
        return $rows;
    }

    /**
     * Returns an array of valid html/attributes/styles for a text field.
     */
    public static function getTextTypeHtml($stream_field_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT who_can_take
                FROM stream_field
                WHERE
                    stream_extra_id = :stream_extra_id
                    AND display_order = :display_order";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":display_order", $display_order, PDO::PARAM_INT);
        $who_can_take_id = $command->queryScalar();
        $who_can_take = LookupHelper::getValue($who_can_take_id);
        return $who_can_take;
    }

    /**
     * Fetches all the stream_field_id values for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that stream_field_ids are being fetched for.
     *
     * @return array
     */
    public static function getStreamFieldIdsForStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT stream_field_id
                FROM stream_field
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $stream_field_ids = $command->queryColumn();
        return $stream_field_ids;
    }

    /**
     * Delete a stream_field by its stream_extra_id.
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $stream_extra_id The extra id of the stream used to delete these rows.
     *
     * @return void
     */
    public static function deleteByStreamExtraId($stream_extra_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM stream_field
                WHERE stream_extra_id = :stream_extra_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
            $command->execute();

        } catch (Exception $e) {
            throw new Exception(
                'StreamField::deleteByStreamExtraId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Inserts a standard empty field.
     *
     * @param integer $stream_extra_id The Stream ID to attatch the new field to.
     *
     * @return StreamField The inserted model
     */
    public static function insertNew($stream_extra_id) {
        // Check stream id exists
        if (StreamBedMulti::exists($stream_extra_id) === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        //get last field display_order in this stream
        $display_order = 1 + StreamField::getLastDisplayOrder($stream_extra_id);

        $model = new StreamField('textbox_create');
        $model->stream_extra_id = $stream_extra_id;
        $model->field_type = 2;
        $model->text_type = 'just_text';
        $model->text_type_id = LookupHelper::getId('stream_field.text_type', 'just_text');
        $model->label = "New field";
        $model->required = 'false';
        $model->display_order = $display_order;
        $model->taken_records = 0;
        $model->max_size = Yii::app()->params['default_max_stream_text_field_size'];
        if ($model->save() === false) {
            throw 'Error saving new field : ' . ErrorHelper::model($model->getErrors());
        } else {
            return $model;
        }
    }

    /**
     * Get the last diplay order value for an stream.
     *
     * @param integer $stream_extra_id The Stream ID to attatch the new field to.
     *
     * @return integer primary key
     */
    public static function getLastDisplayOrder($stream_extra_id) {
        $row =  StreamField::model()->find(
            array(
                'select' => 'display_order',
                'condition' => 'stream_extra_id=:stream_extra_id',
                'order' => 'display_order DESC',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                )
            )
        );

        if (isset($row) === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $row->display_order;
    }

    /**
     * Fetches all fields for a field from its primary key.
     *
     * @param integer $stream_field_id The primary key of the field to fetch.
     *
     * @return StreamField
     */
    public static function getField($stream_field_id) {
        return StreamField::model()->findByPk($stream_field_id);
    }

    /**
     * Update a fields type.
     *
     * @param integer $type_id The id of the type that we are updating. See the lookup table for options.
     * @param integer $stream_field_id The primary key of the field we are updating.
     *
     * @return void
     */
    public static function updateType($type_id, $stream_field_id) {
        // Check type is valid
        if (Lookup::checkIDValid("stream_field.field_type", $type_id) === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        // Check type is allowed to change.
        $display_order_check = StreamField::model()->findByPk(
            $stream_field_id
        );
        if (isset($display_order_check) === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        if ($display_order_check->display_order === '2') {   // Second item must be the main value
            throw new CHttpException(400, 'Bad data. The second field in an stream must be of type "value"');
        }

        // First item must be a link or a text field
        if ($display_order_check->display_order === '1') {
            // stream.kind = 'user' must have a link field at the top.
            $kind_id = StreamField::getKind($stream_field_id);
            if ("user" === LookupHelper::getValue($kind_id) && LookupHelper::getValue($type_id) !== "link") {
                throw new CHttpException(400, "Bad data. stream.kind = 'user' must have a link field at the top.");
            }

            if ($type_id !== LookupHelper::getID("stream_field.field_type", "textbox")
                && $type_id !== LookupHelper::getID("stream_field.field_type", "link")
            ) {
                throw new CHttpException(
                    400,
                    'Bad data. The second field in an stream must be of type "textbox" or "link"'
                );
            }
        }

        // Update the DB. Set the deafult values for max and min selects on lists.
        $list_id = LookupHelper::getID('stream_field.field_type', 'list');
        $openlist_id = LookupHelper::getID('stream_field.field_type', 'openlist');
        if ($type_id === $list_id || $type_id === $openlist_id) {
            StreamField::model()->updateByPk(
                $stream_field_id,
                array(
                    'field_type' => $type_id,
                    'select_qty_min' => 0,
                    'select_qty_max' => 1,
                )
            );
        } else {
            StreamField::model()->updateByPk(
                $stream_field_id,
                array(
                    'field_type' => $type_id,
                )
            );
        }

        StreamField::model()->updateByPk(
            $stream_field_id,
            array(
                'field_type' => $type_id,
            )
        );

        // Set the deafult value if this is a value type.
        if ($type_id === LookupHelper::getID('stream_field.field_type', 'value')) {
            StreamField::updateValueType(
                $stream_field_id,
                LookupHelper::getID('stream_field.value_type', 'updown')
            );
            StreamField::updateValueOptions(
                $stream_field_id,
                LookupHelper::getID('stream_field.value_options', 'any')
            );
        }

    }

    /**
     * Check if this field is owned by the user.
     *
     * @param integer $stream_field_id The primary key of the field we are checking.
     * @param integer $user_id The id of the user we are asserting has ownership of this field.
     *
     * @return boolean
     */
    public static function checkOwner($stream_field_id, $user_id) {
        $row = StreamField::model()->with("stream_extra", "stream_extra.stream")->findByPk(
            $stream_field_id,
            array(
                "select" => "stream_field_id",
                "condition" => ":user_id = user_id",
                "params" => array(
                    ":user_id" => $user_id,
                )
            )
        );
        return isset($row);
    }

    /**
     * Checks if the submited field type is valid.
     *
     * @param integer $type_id The id of the type that we are checking is valid.
     *
     * @return boolean
     */
    public static function isFieldTypeValid($type_id) {
        return Lookup::checkIDValid('stream_field.field_type', $type_id);
    }

    /**
     * Checks if the submited value type is valid.
     *
     * @param integer $value_id The id of the value type we are checking is valid.
     *
     * @return boolean
     */
    public static function isValueTypeValid($value_id) {
        return Lookup::checkIDValid('stream_field.value_type', $value_id);
    }

    /**
     * Moves this field up in the display order.
     *
     * @param integer $stream_field_id The primary key of the field we are moving.
     * @param integer $move_count The number to move this item. -1 moves up one, 1 moves down 1.
     *
     * @return integer|boolean The stream_field_id of the field whose display_order
     *                         has been switched with this one. Or FALSE
     */
    public static function moveDisplayOrder($stream_field_id, $move_count=-1) {
        $row = StreamField::model()->findByPk(
            $stream_field_id,
            array(
                "select" => "display_order, stream_extra_id",
            )
        );

        // If no result then the row does not exist.
        if (isset($row) === false) {
            throw new CHttpException(400, 'This row does not exist.');
        }

        if ($row->display_order === "1" || $row->display_order === "2") {
            throw new CHttpException(400, 'This row is not allowed to be moved.');
        }

        $switch_row  = StreamField::model()->find(
            array(
                "select" => "stream_field_id",
                // ' display_order > 2' Title and main value can not move.
                "condition" => ":stream_extra_id=stream_extra_id "
                    . "AND :display_order=display_order AND display_order > 2",
                "order" => "display_order DESC",
                "params" => array(
                    ":display_order" => $row->display_order + $move_count,
                    ":stream_extra_id" => $row->stream_extra_id,
                ),
            )
        );

        // If no result then the row can not be moved.
        if (isset($switch_row) === false) {
            return false;
        }

        // Move the origional row
        StreamField::model()->updateByPk(
            $stream_field_id,
            array(
                'display_order' => $row->display_order + $move_count,
            )
        );

        // Move the switched row
        StreamField::model()->updateByPk(
            $switch_row->stream_field_id,
            array(
                'display_order' => $row->display_order,
            )
        );

        return $switch_row->stream_field_id;
    }

    /**
     * Resets the display order of the fields in an stream.
     *
     * Used after a field has been deleted and there is a gap.
     *
     * @param {integer} $stream_extra_id The extra id of the stream that is being reset.
     *
     * @return {void}
     */
    public static function resetDisplayOrder($stream_extra_id) {
        $query = "
            SELECT stream_field_id
            FROM stream_field
            WHERE stream_extra_id = :stream_extra_id
            ORDER BY display_order";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        if (empty($rows) === true) {
            throw new Exception("No rows found for : " . $stream_extra_id);
        }
        $row_count = 1;
        foreach ($rows as $row) {
            $query = "
                UPDATE stream_field
                SET  display_order = :row_count
                WHERE stream_field_id = :stream_field_id";
            $command = Yii::app()->db->createCommand($query);
            $command->bindValue(":stream_field_id", $row['stream_field_id'], PDO::PARAM_INT);
            $command->bindValue(":row_count", $row_count, PDO::PARAM_INT);
            $command->execute();
            ++$row_count;
        }
    }

    /**
     * Fetches the stream extra id from one of its field ids.
     *
     * @param {integer} $version_id The id of the version we are looking up an stream extra id for.
     *
     * @return {integer} The stream_extra_id
     */
    public static function getStreamExtraIdFromFieldID($field_id) {
        $query = "
            SELECT stream_extra_id
            FROM stream_field
            WHERE stream_field_id = :stream_field_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_field_id", $field_id, PDO::PARAM_INT);
        $stream_extra_id = $command->queryScalar();
        if ($stream_extra_id === false) {
            throw new Exception("stream_extra_id not found for field_id : " . $field_id);
        }
        return $stream_extra_id;
    }

    /**
     * Copy fields from old stream to new one.
     *
     * @param integer $old_id Old stream_extra_id to copy fields from.
     * @param integer $new_id New stream_extra_id to copy fields to.
     *
     * @return void
     */
    public static function copyFields($old_id, $new_id) {
        $old_rows  = StreamField::model()->findAll(
            array(
                "condition" => ":stream_extra_id=stream_extra_id",
                "params" => array(
                    ":stream_extra_id" => $old_id,
                ),
            )
        );

        foreach ($old_rows as $row) {
            $old_field_id = $row->stream_field_id;
            $row->isNewRecord = true;
            $row->stream_extra_id = $new_id;
            $row->stream_field_id = null;
            if ($row->save() === false) {
                throw new Exception("Stream row not duplicated: " . print_r($row->getErrors(), true));
            }
            // Also need to duplicate list items
            StreamList::copyList($old_field_id, $row->stream_field_id);
        }

    }

    /**
     * Fetch stream fields.
     *
     * @param integer $stream_extra_id The extra id of the stream we are fetching fields for.
     *
     * @return array of StreamField
     */
    public static function getFields($stream_extra_id) {
        $query = "
            SELECT *
            FROM stream_field
            WHERE stream_extra_id = :stream_extra_id
            ORDER BY display_order";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        if (empty($rows) === true) {
            throw new exception("No fields found for stream : " . $stream_extra_id);
        }

        return $rows;
    }

    /**
     * Deletes a stream field by its id.
     *
     * IMPORTANT: This should be called from DeleteMulti to ensure the deltetion of dependent child data.
     *
     * @param $stream_field_id The id of the stream field that is being deleted.
     *
     * @return boolean
     */
    public static function deleteByStreamFieldId($stream_field_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "DELETE
                    FROM stream_field
                    WHERE stream_field_id = :stream_field_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
            $row_count = $command->execute();
            if ($row_count < 1) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            throw new Exception(
                'StreamField::deleteByStreamFieldId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Fetches the display order for a stream field.
     *
     * @param integer $stream_field_id The id of the stream field to fetch a display order for.
     *
     * @return integer
     */
    public static function getDisplayOrder($stream_field_id) {
        $query = "
            SELECT display_order
            FROM stream_field
            WHERE stream_field_id = :stream_field_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":stream_field_id", $stream_field_id, PDO::PARAM_INT);
        $display_order = $command->queryScalar();
        return (int)$display_order;
    }

    /**
     * Select rows of stream_field data for a stream_extra_id.
     *
     * @param type $stream_extra_id The extra id of the stream to select data for.
     *
     * @return array
     */
    public static function getRowsForStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM stream_field
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }



}

?>