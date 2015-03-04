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
 * Form Model to validate login requests.
 *
 * @package PHP_Model_Forms
 */
class LoginForm extends CFormModel
{
    /**
     * The password that has been entered by the user.
     *
     * @var string
     */
    public $password;

    /**
     * The username that is requesting to login.
     *
     * @var string
     */
    public $username;

    /**
     * Is the remember me checkbox checked.
     *
     * @var boolean
     */
    public $remember_me;

    /**
     * The identity authentication object.
     *
     * @var Identity
     */
    private $identity;

    /**
     * The domain of the client site that is requesting login.
     *
     * @var sting
     */
    public $domain;

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     * There are three differnt login scenarios.
     * 1. user name, password and remember_me. This is the site scenario.
     * 2. password, remember_me . This is the user_permission scenario.
     * 3. only remberMeSite (user is already logged in). This is the permission scenario.
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('password, username, domain', 'required'),
            array(
                'remember_me',
                'boolean',
                'trueValue' => true,
                'falseValue' => false,
            ),
            // password needs to be authenticated
            array('password', 'authenticate', 'skipOnError' => true),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'remember_me' => 'Remember Me',
        );
    }

    /**
     * Store the user identity object.
     *
     * This needs to be passed in before authentication to prevent the need to duplicate authentication.
     *
     * @param Identity &$identity The identity object.
     *
     * @return void
     */
    public function setIdentity(&$identity) {
        $this->identity = &$identity;
    }

    /**
     * Authenticate a user who has entered their log in details on the form.
     *
     * @return void
     */
    public function authenticate() {

        $this->identity->authenticate();
        switch($this->identity->errorCode) {
            case UserIdentity::ERROR_USERNAME_INVALID:
                $this->addError('username', 'Username does not exist.');
                break;

            case UserIdentity::ERROR_PASSWORD_INVALID:
                $this->addError('password', 'Password is incorrect.');
                break;
        }
    }
}

?>