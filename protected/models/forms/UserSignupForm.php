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
 * Form to validate a new user who is signing up.
 *
 * @package PHP_Model_Forms
 */
class UserSignupForm extends CFormModel
{
    /**
     * The new username to create
     *
     * @var string $username
     */
    public $username;

    /**
     * The password to associate with this username.
     *
     * @var string $password
     */
    public $password;

    /**
     * A second password field to be sure the user has enetered the correct password.
     *
     * @var string $password
     */
    public $verify_password;

    /**
     * The email address for this username.
     *
     * @var string $email
     */
    public $email;

    /**
     * The signup code used to activate this users account.
     *
     * @var string $signup_code
     */
    public $signup_code;

    /**
     * If the username starts with test then a warning is shown
     *
     * @var string $signup_code
     */
    public $test_ok = 'false';

    /**
     * Rules applied to this Form.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        return array(
            array('username, password, verify_password, test_ok', 'required'),
            array('username, password, email, verify_password', 'length', 'max' => 128),            array(
                'username',
                'match',
                'pattern' => '/^[a-z0-9](?:\x20?[a-z0-9])*$/',
                'message' => 'Username can only contain lower case letters, digits 0 to 9 and spaces.'
                    . 'It cannot start or end with a space and double spaces are not allowed.',
            ),
            array('username', 'ruleUsernameValid'),
            array('username', 'rulePasswordsMatch'),
            array('signup_code', 'ruleSignupCode'),
            array('username', 'ruleTestOK'),
        );
    }

    /**
     * Provides a warning if a username begining with 'test' is used.
     */
    public function ruleTestOK() {
        if (substr($this->username, 0, 4) === 'test'
            && $this->test_ok !== 'true'
            && empty($this->getErrors()) === true
        ) {
            $this->addError(
                'username',
                'Usernames starting with \'test\' are treated as test accounts '
                    . 'and the account and all data associated with it will be deleted on the whims of the developers.'
                    . ' Please submit again to create a test account, '
                    . 'or change the username to create a noramal account.'
            );
            $this->test_ok = 'true';
        } else {
            $this->test_ok = 'false';
        }
    }


    /**
     * If a sign up code is required, is it present and is it valid.
     */
    public function ruleUsernameValid() {
        $user_model = new User;
        $user_model->username = $this->username;
        $user_model->password = $this->password;
        $user_model->email = $this->email;
        $user_model->setScenario('new');
        if ($user_model->validate() === false) {
            $username_error = $user_model->getError('username');
            if (isset($username_error) === true) {
                $this->addError('username', $username_error);
            }
            $password_error = $user_model->getError('password');
            if (isset($password_error) === true) {
                $this->addError('password', $password_error);
            }
            $email_error = $user_model->getError('email');
            if (isset($email_error) === true) {
                $this->addError('email', $email_error);
            }
            if (isset($username_error) === false && isset($password_error) === true && isset($email_error) === true) {
                $this->addError('username', 'An unknown error has occurred.');
            }
        }
    }

    /**
     * If a sign up code is required, is it present and is it valid.
     */
    public function rulePAsswordsMatch() {
        if ($this->password !== $this->verify_password) {
            $this->addError('verify_password', 'Passwords do not match.');
        }
    }

    /**
     * If a sign up code is required, is it present and is it valid.
     */
    public function ruleSignupCode() {
        if (Yii::app()->params['use_signup_codes'] === true) {
            $valid = SignupCode::isValid($this->signup_code);
            if ($valid === false) {
                $this->addError('signup_code', 'This activation code is not valid.');
            }
        }
    }

}

?>