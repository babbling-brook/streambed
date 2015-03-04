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
 * Transaction class for making a new stream.
 *
 * @package PHP_Transactions
 */
class NewStream
{
    /**
     * @var CDbTransaction
     */
    private $transaction;

    /**
     * @var boolean
     */
    private $success = false;

    /**
     * @var array Errors that should be reported to the original request.
     */
    private $errors = array();

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
     * @var StreamField
     */
    private $first_field_model;

    /**
     * @var StreamField
     */
    private $value_field_model;

    /**
     * Creates a new stream in a transaction.
     *
     * If there are any errors then the insertion is aborted and the errors recorded.
     *
     * @param string $name The name of this stream.
     * @param string $description The description for this stream.
     * @param string $kind The name of the kind of stream this is.
     * @param string $post_mode The initial type of post mode for this stream.
     *
     * @return void
     */
    public function __construct($name, $description, $kind, $post_mode) {
        $this->transaction = Yii::app()->db->beginTransaction();
        try {
            $this->stream_model = new Stream('create');
            $this->stream_extra_model = new StreamExtra;
            $this->version_model = new Version;

            if ($this->createStreamRow($name, $kind) === false) {
                return false;
            }

            $this->createVersionRow();
            $this->createMetaPost($name, $description);

            if ($this->createExtraRow($description, $post_mode) === false) {
                return false;
            }
            $this->createFirstField($kind);
            $this->createValueField($kind);
            StreamDefaultRhythm::insertSiteDefaults($this->stream_extra_model->stream_extra_id);
            StreamDefaultRing::insertSiteDefaults($this->stream_extra_model->stream_extra_id);
            StreamChild::insertSiteDefaults($this->stream_extra_model->stream_extra_id);

            $this->success = true;
            $this->transaction->commit();
        } catch (Exception $e) {
            $this->revert();
            throw new Exception('There was an exception when creating a new stream. ' . $e);
        }
    }

    public function getSuccess() {
        return $this->success;
    }

    /**
     * Returns errors that should be reported to the origional request.
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Revert the transaction
     *
     * @return void
     */
    private function revert() {
        $this->transaction->rollBack();
    }

    /**
     * Create a row in the stream table.
     *
     * @param string $name The name of this stream.
     * @param string $kind The name of the kind of stream this is.
     *
     * @return boolean
     */
    private function createStreamRow($name, $kind) {
        $this->stream_model->name = $name;
        $this->stream_model->kind = LookupHelper::getID('stream.kind', $kind);
        $this->stream_model->user_id = Yii::app()->user->getId();

        if ($this->stream_model->save() === false) {
            $errors = $this->stream_model->getErrors();
            if (isset($errors['name']) === true) {
                $this->errors['name'] = $errors['name'];
                $this->revert();
            } else {
                throw new Exception(
                    'stream row not inserted : ' . ErrorHelper::model($this->stream_model->getErrors())
                );
            }
            return false;
        }

        return true;
    }

    /**
     * Create a row in the version table.
     *
     * @return boolean
     */
    private function createVersionRow() {
        $this->version_model->family_id = $this->stream_model->stream_id;
        $this->version_model->type = LookupHelper::getID('version.type', 'stream');
        $this->version_model->major = 0;
        $this->version_model->minor = 0;
        $this->version_model->patch = 0;

        if ($this->version_model->save() === false) {
            throw new Exception('Version row not inserted : ' . ErrorHelper::model($this->version_model->getErrors()));
        }
        return true;
    }

    /**
     * Create a row in the version table.
     *
     * @return boolean
     */
    private function createExtraRow($description, $post_mode) {

        $post_mode_id = LookupHelper::getID('stream_extra.post_mode', $post_mode, false);
        if (isset($post_mode_id) === false) {
            throw new Exception('The post mode given is not valid. ' . $post_mode);
        }
        $this->stream_extra_model->post_mode = $post_mode_id;

        $this->stream_extra_model->stream_id = $this->stream_model->getPrimaryKey();
        $this->stream_extra_model->description = $description;
        $this->stream_extra_model->version_id = $this->version_model->version_id;
        $this->stream_extra_model->status_id = StatusHelper::getID('private');

        if ($this->stream_extra_model->save() === false) {
            $errors = $this->stream_extra_model->getErrors();
            if (isset($errors['description']) === true) {
                $this->errors['description'] = $errors['description'];
                $this->revert();
            } else {
                throw new Exception(
                    'stream extra row not inserted : ' . ErrorHelper::model($this->stream_extra_model->getErrors())
                );
            }
            return false;
        }
        return true;
    }

    /**
     * Create the first field for this stream depending on the kind of stream that it is.
     *
     * @param type $kind The kind of stream that is being created.
     *
     * @return void
     */
    private function createFirstField($kind) {
        if ($kind === 'user') {
            $this->createLinkField();
        } else {
            $this->createTitleField();
        }
    }

    /**
     * Create a link field for this stream.
     *
     * @return void
     */
    private function createLinkField() {
        $this->first_field_model = new StreamField('link_create');
        $this->first_field_model->stream_extra_id = $this->stream_extra_model->stream_extra_id;
        $this->first_field_model->field_type = LookupHelper::getID('stream_field.field_type', 'link');
        $this->first_field_model->label = 'Link title';
        $this->first_field_model->required = true;
        $this->first_field_model->display_order = 1;
        $this->first_field_model->taken_records = 0;
        $this->first_field_model->max_size = 200;
        if ($this->first_field_model->save() === false) {
            throw new Exception(
                'Title field not inserted : ' . ErrorHelper::model($this->first_field_model->getErrors())
            );
        }
    }

    /**
     * Create a title field for this stream.
     *
     * @return void
     */
    private function createTitleField() {
        $this->first_field_model = new StreamField('textbox_create');
        $this->first_field_model->stream_extra_id = $this->stream_extra_model->stream_extra_id;
        $this->first_field_model->field_type = LookupHelper::getID('stream_field.field_type', 'textbox');
        $this->first_field_model->label = 'Title';
        $this->first_field_model->max_size = '200';
        $this->first_field_model->text_type = 'just_text';
        $this->first_field_model->required = true;
        $this->first_field_model->display_order = 1;
        $this->first_field_model->taken_records = 0;
        if ($this->first_field_model->save() === false) {
            throw new Exception(
                'Title field not inserted : ' . ErrorHelper::model($this->first_field_model->getErrors())
            );
        }
    }

    /**
     * Create a value field for this stream.
     *
     * @return void
     */
    private function createValueField($kind) {
        $this->value_field_model = new StreamField('value_create');
        $this->value_field_model->stream_extra_id = $this->stream_extra_model->stream_extra_id;
        $this->value_field_model->field_type = LookupHelper::getID('stream_field.field_type', 'value');
        $this->value_field_model->label = 'Main Value';
        $this->value_field_model->display_order = 2;
        $this->value_field_model->taken_records = 0;
        $this->value_field_model->value_type = LookupHelper::getID('stream_field.value_type', 'updown');
        $this->value_field_model->value_options = LookupHelper::getID('stream_field.value_options', 'any');
        if ($kind === 'user') {
            $this->value_field_model->value_min = 0;
            $this->value_field_model->value_max = 1;
        }
        if ($this->value_field_model->save() === false) {
            throw new Exception(
                'Main Value field not inserted : ' . ErrorHelper::model($this->value_field_model->getErrors())
            );
        }
    }

    /**
     * Creates a meta post for this stream.
     *
     * @param String $name The name of this stream.
     * @param String $description The description of this stream.
     *
     * @returns void
     */
    private function createMetaPost($name, $description) {

        $fake_extra_model = (object)array(
            'description' => $description,
            'version' => $this->version_model,
        );

        $fake_stream_model = (object)array(
            'name' => $name,
            'user' => $this->stream_model->user,
            'extra' => $fake_extra_model,
        );

        $post_id = StreamMulti::createMetaPost($fake_stream_model, false);

        $this->stream_extra_model->meta_post_id = $post_id;
    }
}

?>