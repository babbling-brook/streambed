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
 * Model to validate that a Babbling Brook stream object is valid (including its extra details, version and fields)
 * Used to convert the Babbling Brook data into the relational format used in the DB.
 *
 * @package PHP_Model_Forms
 */
class StreamForm extends CFormModel
{

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $major;

    /**
     * @var string
     */
    public $minor;

    /**
     * @var string
     */
    public $patch;

    /**
     * @var string
     */
    public $post_mode;

    /**
     * @var string
     */
    public $edit_mode;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $group_period;

    /**
     * @var string
     */
    public $meta_url;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $cooldown;

    /**
     * @var string
     */
    public $date_created;

    /**
     * @var string
     */
    public $kind;

    /**
     * @var array An array of StreamFieldForm in Babbling Brook textual format.
     */
    public $field_forms = array();

    // The are the DB models that are generated for insertion into the DB.

    /**
     * @var Site
     */
    private $site_id;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var Stream
     */
    private $stream_model;

    /**
     * @var StreamExtra
     */
    private $stream_extra_model;

    /**
     * @var Version
     */
    private $version_model;

    /**
     * @var boolean Flag set to true when any model fails to validate.
     */
    private $is_validation_error = false;

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('
                domain, username, name, major, minor, patch, post_mode, edit_mode,
                status, description, cooldown, date_created, field_forms',
                'required',
            ),
            array('domain', "ruleSite"),
            array('domain', "ruleUser"),
            array('domain', "ruleStreamModel"),
            array('domain', "ruleVersionModel"),
            array('domain', "ruleStreamExtraModel"),
            array('domain', "ruleFieldsArray"),
        );
    }

    /**
     * A rule to check that the streams domain is valid
     *
     * @return void
     */
    public function ruleSite() {
        $site_id = SiteMulti::getSiteID($this->domain, true, true);
        if ($site_id === false) {
            $this->addError('domain', 'The domain is not a valid Babbling Brook domain' . $this->domain);
        } else {
            $this->site_id = $site_id;
        }
    }

    /**
     * A rule to check that the streams username is valid
     *
     * @return void
     */
    public function ruleUser() {
        if (isset($this->site_id) === false) {
            $this->addError('username', 'Cannot validate username without a valid domain. ' . $this->username);
            return;
        }

        $user_multi = new UserMulti($this->site_id);
        $user_id = $user_multi->getIDFromUsername($this->username, false, true);
        if ($user_id === false) {
            $this->addError('username', 'The username is not a valid Babbling Brook domain. ' . $this->username);
        } else {
            $this->user_id = $user_id;
        }
    }

    /**
     * A rule to check that the streams domain is valid
     *
     * @return void
     */
    public function ruleStreamModel() {
        if (isset($this->user_id) === false) {
            $this->addError('name', 'Cannot validate stream name without a valid user. ' . $this->name);
            return;
        }

        $this->stream_model = new Stream;
        $this->stream_model->name = $this->name;
        $this->stream_model->user_id = $this->user_id;
        $this->stream_model->setKindFromText($this->kind);

        if ($this->stream_model->validate() === false) {
            $this->addError(
                'name',
                'Stream is not validating : ' . ErrorHelper::model($this->stream_model->getErrors())
            );
            $this->is_validation_error = true;
        }
    }

    /**
     * A rule to check that the streams domain is valid
     *
     * @return void
     */
    public function ruleVersionModel() {
        $this->version_model = new Version('composite');
        $this->version_model->type = LookupHelper::getID('version.type', 'stream');
        $this->version_model->major = $this->major;
        $this->version_model->minor = $this->minor;
        $this->version_model->patch = $this->patch;

        if ($this->version_model->validate() === false) {
            $this->addError(
                'major',
                'Version is not validating : ' . ErrorHelper::model($this->version_model->getErrors())
            );
            $this->is_validation_error = true;
        }
    }

    /**
     * A rule to check that the streams domain is valid
     *
     * @return void
     */
    public function ruleStreamExtraModel() {
        $this->stream_extra_model = new StreamExtra('composite');
        $this->stream_extra_model->description = $this->description;
        $this->stream_extra_model->date_created = $this->date_created;
        $this->stream_extra_model->setStatusFromText($this->status);
        $this->stream_extra_model->setGroupPeriodFromText($this->group_period);
        $this->stream_extra_model->setPostModeFromText($this->post_mode);
        $this->stream_extra_model->setEditModeFromText($this->edit_mode);

        if ($this->stream_extra_model->validate() === false) {
            $this->addError(
                'description',
                'Stream is not validating : ' . ErrorHelper::model($this->stream_extra_model->getErrors())
            );
            $this->is_validation_error = true;
        }
    }

    /**
     * A rule to check that the streams domain is valid
     *
     * @return void
     */
    public function ruleFieldsArray() {
        foreach ($this->field_forms as $field) {
            if ($field->validate() === false) {
                $this->addError(
                    'fields',
                    'A field is not validating : ' . ErrorHelper::model($field->getErrors())
                );
                $this->is_validation_error = true;
            }
        }
    }

    /**
     * If the stream already exists then it is updated. Otherwise it is inserted.
     *
     * @return integer the local id of the stream extra id.
     */
    public function insertOrUpdateStream() {
        $stream_id = Stream::getStreamID($this->user_id, $this->name);
        if ($stream_id !== false) {
            $this->stream_model->stream_id = $stream_id;
            $this->stream_model->isNewRecord = false;
        }
        if ($this->stream_model->save() === false) {
            throw new Exception('Stream failed to update. ' . ErrorHelper::model($this->stream_model->getErrors()));
        }

        $full_stream_model = StreamBedMulti::getByName(
            $this->user_id,
            $this->name,
            $this->major,
            $this->minor,
            $this->patch
        );
        if (isset($full_stream_model) === true) {
            $stream_extra_id = $this->updateStream($stream_model);
        } else {
            $stream_extra_id = $this->insertStream();
        }

        StreamField::deleteFieldsInStream($stream_extra_id);
        foreach ($this->field_forms as $field) {
            $field->stream_extra_id = $stream_extra_id ;
            $field->insertField();
        }
    }

    /**
     * Updates a stream with the information in this form.
     *
     * @param type $stream_model A model with the stream to update. Includes child models.
     *
     * @ return integer The local id of the stream extra id.
     */
    private function updateStream($stream_model) {
        $this->version_model->version_id = $stream_model->version->version_id;
        if ($this->version_model->save() === false) {
            $this->version_model->isNewRecord = false;
            $errors = ErrorHelper::model($this->version_model->getErrors());
            throw new Exception('Stream version failed to update. ' . $errors);
        }
        $this->stream_extra_model->stream_extra_id = $stream_model->extra->stream_extra_id;
        if ($this->stream_extra_model->save() === false) {
            $this->stream_extra_model->isNewRecord = false;
            $errors = ErrorHelper::model($this->stream_extra_model->getErrors());
            throw new Exception('Stream extra details failed to update. ' . $errors);
        }
        return $this->stream_extra_model->stream_extra_id;
    }

    /**
     * Inserts a stream with the information in this form.
     *
     * @ return integer The local id of the stream extra id.
     */
    private function insertStream() {
        if ($this->stream_model->save() === false) {
            throw new Exception('Stream failed to insert. ' . ErrorHelper::model($this->stream_model->getErrors()));
        }
        $this->version_model->family_id = $this->stream_model->stream_id;
        if ($this->version_model->save() === false) {
            $errors = ErrorHelper::model($this->version_model->getErrors());
            throw new Exception('Stream version failed to insert. ' . $errors);
        }
        $this->stream_extra_model->stream_id = $this->stream_model->stream_id;
        $this->stream_extra_model->version_id = $this->version_model->version_id;
        if ($this->stream_extra_model->save() === false) {
            $errors = ErrorHelper::model($this->stream_extra_model->getErrors());
            throw new Exception('Stream extra details failed to update. ' . $errors);
        }
        return $this->stream_extra_model->stream_extra_id;
    }
}

?>
