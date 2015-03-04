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
 * Model to validate a change in a user config option.
 *
 * @package PHP_Model_Forms
 */
class UserConfigChangeForm extends CFormModel
{

    /**
     * The code for this user config option.
     *
     * @var string
     */
    public $code;

    /**
     * The value to change this cofig option to.
     *
     * @var string
     */
    public $value;

    /**
     * The id of the user who owns this config option (This is not validated.)
     *
     * @var string
     */
    public $user_id;

    /**
     * The type of config option being used.
     *
     * This is calculated from the given code.
     *
     * @var string
     */
    private $type;

    /**
     * Is this the defualt value for this option.
     *
     * Set when the form is saved.
     *
     * @var boolean
     */
    public $default;


    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('code', 'required'),
            array('code', 'ruleCalculateType'),
            array('code', 'ruleSwitchType'),
        );
    }

    /**
     * Rule to check the code is valid and assign the type.
     *
     * @return void
     */
    public function ruleCalculateType() {
        $type_id = UserConfigDefault::getTypeId($this->code);
        if ($type_id === false) {
            $this->addError('code', 'The code is not valid ' . $this->code);
        }
        $this->type = LookupHelper::getValue($type_id);
    }

    /**
     * Rule to select the correct set of rules to check for the type of code this is.
     *
     * @return void
     */
    public function ruleSwitchType() {
        // Empty values are reset to their defaults.
        if (empty($this->value) === true) {
            return;
        }

        switch ($this->type) {
            case 'rhythm_url':
                $this->ruleCheckValueIsRhythm();
                break;

            case 'stream_url':
                $this->ruleCheckValueIsStream();
                break;

            case 'uint':
                $this->ruleCheckValueIsUint();
                break;

            default:
                $this->addError('code', 'The type (' . $this->type . ') is invalid for code : ' . $this->code);
        }
    }

    /**
     * Rule to select the correct set of rules to check for the type of code this is.
     *
     * @return void
     */
    public function ruleCheckValueIsUint() {
        if (ctype_digit($this->value) === false) {
            $this->addError('value', 'The is not a valid positive whole number');
        }
    }

    /**
     * Rule to select the correct set of rules to check for the type of code this is.
     *
     * @return void
     */
    public function ruleCheckValueIsRhythm() {
        $rhythm_cat = false;
        switch ($this->code) {
            case 'stream_rhythm_suggestion_url':
                $rhythm_cat = 'stream_suggestion';
                break;

            case 'stream_filter_rhythm_suggestion_url':
                $rhythm_cat = 'stream_filter_suggestion';
                break;

            case 'user_stream_rhythm_suggestion_url':
                $rhythm_cat = 'user_stream_suggestion';
                break;

            case 'stream_ring_rhythm_suggestion_url':
                $rhythm_cat = 'stream_ring_suggestion';
                break;

            case 'user_rhythm_suggestion_url':
                $rhythm_cat = 'user_suggestion';
                break;

            case 'meta_rhythm_suggestion_url':
                $rhythm_cat = 'meta_suggestion';
                break;

            case 'kindred_rhythm_suggestion_url':
                $rhythm_cat = 'kindred suggestion';
                break;

            case 'kindred_rhythm_url':
                $rhythm_cat = 'kindred';
                break;

            default:
                $this->addError('code', 'The type is invalid for code : ' . $this->code);
        }

        $valid = Rhythm::checkValidUrl($this->value, $rhythm_cat);
        if ($valid === false) {
            $this->addError(
                'value', $this->value . ' is not a valid rhythm url for the rhythm category ' . $rhythm_cat
            );
        }

    }


    /**
     * Rule to select the correct set of rules to check for the type of code this is.
     *
     * @return void
     */
    public function ruleCheckValueIsStream() {
        $stream_id = StreamBedMulti::getIdFromUrl($this->value);
        if (ctype_digit($stream_id) === false) {
            $this->addError('value', $this->value . ' is not a valid stream url');
        }

    }

    /**
     * Saves a pre-validated config option.
     *
     * @return boolean
     */
    public function saveConfig() {
        $valid = $this->validate();
        if ($valid === false) {
            return false;
        }

        // If the value is empty then the setting is deleted, resetting it to the default.
        if (empty($this->value) === true) {
            UserConfig::deleteRow($this->user_id, $this->code);
            $this->value = UserConfigDefault::getValueFromCode($this->code);
            $this->default = true;
            // Kindred rhythms are a special case as they are stored seperatly.
            if ($this->code === 'kindred_rhythm_url') {
                UserRhythm::resetKindredRhythm($this->user_id);
            }
            return true;
        }

        $user_config_id = UserConfig::getUserConfigId($this->user_id, $this->code);

        $user_config_model = new UserConfig;
        $user_config_model->user_id = $this->user_id;
        $user_config_model->code = $this->code;
        $user_config_model->value = $this->value;
        if ($user_config_id !== false) {
            $user_config_model->user_config_id = $user_config_id;
            $user_config_model->isNewRecord = false;
        }
        $saved = $user_config_model->save();
        if ($saved === false) {
            throw Exception('saveConfig did not succeed to save a config row in the user_config table.');
            return false;
        }

        // The kindred rhythm is a special case as the user_rhythm table also needs updating.
        if ($this->code === 'kindred_rhythm_url') {
            UserRhythm::updateKindredRhythmFromUrl($this->value, $this->user_id);
        }

        $this->default = false;
        return true;
    }
}

?>
