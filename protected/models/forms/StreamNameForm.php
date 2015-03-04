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
 * Model to validate that a stream object is valid.
 *
 * @package PHP_Model_Forms
 */
class StreamNameForm extends CFormModel
{
    /**
     * An object that holds the stream name data.
     *
     * @var array $stream
     * string $stream.name The name of the stream.
     * string $stream.username The username of the stream.
     * string $stream.domain The domain of the stream.
     * array $stream.version The version of the stream.
     * string $stream.version.major The major version of the stream.
     * string $stream.version.minor The minor version of the stream.
     * string $stream.version.patch The patch version of the stream.
     */
    public $stream;

    /**
     * Should the streams home data store be checked if the stream is not cached locally.
     *
     * @var boolean [$stream=false]
     */
    public $check_remote_store = false;

    /**
     * Should private streams not owned by the current user be validated.
     *
     * @var type
     */
    public $all_private = false;

    /**
     * The id of the user that owns the stream.
     *
     * @var integer
     */
    private $user_id;

    /**
     * An array of extra ids for the streams in this form. These are generated when the stream is validated.
     *
     * @var array
     */
    private $stream_extra_ids;

    /**
     * Assigns a stram object to the stream.
     *
     * Call makeStreamObject after construction to make the stream object from parts.
     *
     * @param type $stream
     */
    public function __construct($stream=false) {
        if ($stream !== false) {
            $this->stream = $stream;
        }
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
            array('stream', 'required'),
            array('check_remote_store', 'boolean', 'trueValue' => true, 'falseValue' => false),
            array('stream', "ruleUsername"),
            array('stream', "ruleStream"),
        );
    }

    /**
     * A rule to check that the stream username is valid
     *
     * @return void
     */
    public function ruleUsername() {
        $full_username = $this->stream['domain'] . '/' . $this->stream['username'];
        if (isset($this->stream['domain']) === false) {
            $this->addError('stream', 'The stream domain is invalid : ' . $full_username);
        }
        if (isset($this->stream['username']) === false) {
            $this->addError('stream', 'The stream username is invalid : ' . $full_username);
        }
        $this->user_id = User::getIDFromUsernameAndDomain($this->stream['username'], $this->stream['domain']);
        if ($this->user_id === false) {
            $this->addError('stream', 'The stream username is invalid : ' . $full_username);
        }
    }

    /**
     * A rule to check that the stream is valid.
     *
     * @return void
     */
    public function ruleStream() {
        $this->stream['name'] = str_replace('+', ' ', $this->stream['name']);
        $this->stream['name'] = str_replace('%20', ' ', $this->stream['name']);

        $this->stream_extra_ids = StreamMulti::getAllStreamExtraIds(
            $this->user_id,
            $this->stream['name'],
            $this->stream['version']['major'],
            $this->stream['version']['minor'],
            $this->stream['version']['patch'],
            $this->all_private
        );

        if (count($this->stream_extra_ids) === 0) {
            $this->addError('stream', 'The stream has not been found');
        }
    }

    /**
     * Creates the stream object for testing from paramaters.
     *
     * @param string $domain The stream domain.
     * @param string $username The stream username.
     * @param string $name The stream name.
     * @param string $major The stream major version.
     * @param string $minor The stream minor version.
     * @param string $patch The stream patch version.
     */
    public function makeStreamObject($domain, $username, $name, $major, $minor, $patch) {
        $this->stream = array(
            'domain' => $domain,
            'username' => $username,
            'name' => $name,
            'version' => array(
                'major' => $major,
                'minor' => $minor,
                'patch' => $patch,
            ),
        );
    }

    /**
     * Returns the first fetched stream extra id after the form has been validated.
     *
     * @return integer|false
     */
    public function getFirstStreamExtraId() {
        return $this->stream_extra_ids[0];
    }

    /**
     * Returns the fetched stream extra ids after the form has been validated.
     *
     * @return integer|false
     */
    public function geAllStreamExtraIds() {
        return $this->stream_extra_ids;
    }

}

?>