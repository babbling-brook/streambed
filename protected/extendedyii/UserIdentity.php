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
 * UserIdentity represents the data needed to identitify a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 *
 * @package PHP_ExtendedYii
 */
class UserIdentity extends CUserIdentity
{

    /**
     * The primary key in the user table for this user.
     *
     * @var integer
     */
    private $id;

    /**
     * Has this user been authenticated.
     *
     * @var boolean
     */
    private $authenticated = false;

    /**
     * The site id for this user. Indicate the domain of their domus domain.
     *
     * @var integer
     */
    public $site_id;

    /**
     * Overridden constructor.
     *
     * @param string $username The username of this indentity.
     * @param string $password The password for this identity.
     * @param string|integer $domain    The domain or site_id for where this user is registered.
     *                                  If null then assumed that this site is home.
     *                                  If a string then it is the domain name.
     *                                  If an INT then it is the site_id.
     */
    public function __construct($username, $password, $domain=null) {
        parent::__construct($username, $password);
        if (isset($domain) === true && is_numeric($domain) === true) {
            $this->site_id = $domain;
        } else if (isset($domain) === true) {
            $this->site_id = SiteMulti::getSiteID($domain);
        } else {
            $this->site_id = Yii::app()->params['site_id'];
        }
    }

    /**
     * Logs a user in to their domus.
     *
     * Use loginClientUser to log a user into a client site when they have a remote domus.
     *
     * @param string $domain The domain name of the client site. May be local or third party site.
     * @param integer $remember_time Time to remember this user if the session ends.
     *
     * @return boolean whether login is successful.
     */
    public function loginUser($domain, $remember_time) {
        if ($this->authenticated === false) {
            $this->authenticate();
        }

        if ($this->errorCode !== UserIdentity::ERROR_NONE) {
            return false;
        }
        SiteAccess::storeSiteAccess(
            $domain,
            $this->username,
            $remember_time
        );

        $duration = 0;
        if ($remember_time > time()) {
            $duration = $remember_time - time();
        }

        Yii::app()->user->login($this, $duration);

        User::createNewCSFRToken(Yii::app()->user->getId());

        if (Yii::app()->user->isGuest === true) {
            throw new Exception("An error occurred whilst logging on a user.");
        }
        return true;
    }

    /**
     * If this is a client website, then this logs a user in to it if they have a remote store account.
     *
     * @param integer $remember_time Unix timestamp to remember this user if the session ends.
     *                               This needs to be passed in from the client domain so that the timeouts match.
     *
     * @return boolean whether login is successful.
     */
    public function loginClientUser($remember_time) {
        $this->clientAuthenticate();

        $duration = 0;
        if ($remember_time > time()) {
            $duration = $remember_time - time();
        }
        Yii::app()->user->login($this, $duration);

        if (Yii::app()->user->isGuest === true) {
            throw new Exception("An error occurred whilst logging on a user.");
            Yii::app()->end();
        }
        return !Yii::app()->user->isGuest;
    }

    /**
     * Authenticates a user for a client website.
     *
     * Does not need a password as the user is already logged on.
     * This must only be accessed from this domains client site.
     *
     * @return integer Error code
     */
    protected function clientAuthenticate() {
        $user=User::model()->find(
            array(
                'condition' => 'username=:username AND site_id=:site_id',
                'params' => array(
                    'username' => $this->username,
                    'site_id' => $this->site_id,
                )
            )
        );
        if ($user === null) {
            // This user has not used this site before. Insert them.
            $user_multi = new UserMulti($this->site_id);
            $this->id = $user_multi->insertRemoteUser($this->username);
            UserLevel::createUser($user_id);
            $this->errorCode = self::ERROR_NONE;
        } else {
            $this->id = $user->user_id;
            $this->username = $user->username;
            $this->errorCode = self::ERROR_NONE;
        }
        return $this->errorCode == self::ERROR_NONE;
    }

    /**
     * Authenticates a user.
     *
     * @return boolean whether authentication succeeds.
     */
    public function authenticate() {
        $user_mulit = new UserMulti($this->site_id);
        $user_id = $user_mulit->userValid($this->username, $this->hashPassword());
        if ($user_id === UserMulti::USER_ERROR) {
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        } else if ($user_id === UserMulti::PASSWORD_ERROR) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            $this->id = $user_id;
            $this->authenticated = true;
            $this->username = $this->username;
            $this->errorCode = self::ERROR_NONE;
        }
        return $this->errorCode==self::ERROR_NONE;
    }

    /**
     * Checks if a username is available.
     *
     * Sets an error code rather than returning the error.
     *
     * @return void
     */
    public function usernameUnique() {
        $UserMulti = new UserMulti($this->site_id);
        $user = $UserMulti->userExists($this->username);
        if ($user === true) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        }

        // If this is a local user then check there are no conflicts with standard site urls.
        if ($this->site_id === Yii::app()->params['site_id']) {
            // Check the username is not a root site folder
            $reserved = array(
                'site',
                'assets',
                'css',
                'images',
                'js',
                'themes',
                'admin',
                'system',
            );
            foreach ($reserved as $res) {
                $pos = strpos($this->username, $res . "/");
                if ($pos === 0 && $pos !== false) { //first position but not false
                    $this->errorCode = self::ERROR_USERNAME_INVALID;
                }
                if ($res === $this->username) {
                    $this->errorCode = self::ERROR_USERNAME_INVALID;
                }
            }
        }
    }

    /**
     * Checks if a remote user is already registered.
     *
     * @param string $user Username to check.
     *
     * @return boolean
     */
    public function remoteUserUnique($user) {
        $user_multi = new UserMulti($this->site_id);
        if ($user_multi->userExists($user) === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the user id.
     *
     * @return integer the ID of the user record.
     */
    public function getId() {
        // Copy into a temp variable to prevent passing by reference.
        $user_id = $this->id;
        return intval($user_id);
    }

    /**
     * Generates the password hash.
     *
     * @param string $salt Unique ueser salt for password.
     *
     * @return string hash
     */
    public function hashPassword($salt=null) {
        //Fetch salt from DB if not passed in
        if ($salt === null) {
            $user_mulit = new UserMulti($this->site_id);
            $salt = $user_mulit->getSalt($this->username);
        }

        if ($salt === UserMulti::USER_ERROR || $salt ===  UserMulti::SALT_ERROR) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        }

        return hash('sha256', $this->password . $salt);
    }

    /**
     * Signs a new user up to the site.
     *
     * @param string $email Email address of the user.
     * @param boolean $ring Is this user a ring.
     *
     * @return void
     */
    public function signUp($email=null, $ring=false) {
        $salt = $this->generateSalt();
        $user_multi = new UserMulti($this->site_id);
        $user_id = $user_multi->insertUser($this->username, $this->hashPassword($salt), $salt, $email, $ring);
        if ($ring === false) {
            $this->loginUser(Yii::app()->params['host'], 0);
        }
        $this->id = $user_id;
        UserProfile::createProfile($user_id);
        UserRhythm::createDefaultKindredRhyhtm($user_id);
        UserStreamSubscription::insertDefaults($user_id);
        UserLevel::createUser($user_id);
    }

    /**
     * Generates a unique salt that can be used to generate a password hash.
     *
     * @return string the salt
     */
    protected function generateSalt() {
        return uniqid('', true);
    }

    /**
     * Generate a random password for a user.
     *
     * Primarily used when creating rings.
     *
     * @return string
     */
    public static function createRandomPassword() {
        $length = 30;

        $password = "";

        // define possible characters - any character in this string can be picked for use in the password
        $possible = "2346789bcdfghjkmnpqrtvwxyz!&*()[]BCDFGHJKLMNPQRTVWXYZ";

        $maxlength = strlen($possible);

        // add random characters to $password until $length is reached
        $i = 0;
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, $maxlength-1), 1);
            $password .= $char;
            $i++;
        }

        return $password;
    }

    /**
     * Given a username in short form, url form or email form return a standard form.
     *
     * @param type $username The users username in short form, url form or email form.
     *
     * @return array With paramaters username and domain.
     */
    public static function standardiseUser($username) {
        $user = array();

        // Url form.
        if (strpos($username, "/") > 0) {
            $exploded_user = explode("/", $username);
            $user['domain'] = $exploded_user[0];
            $user['username'] = $exploded_user[1];

        // email form.
        } else if (strpos($username, "@") > 0) {
            $exploded_user = explode("@", $username);
            $user['domain'] = $exploded_user[1];
            $user['username'] = $exploded_user[0];

        // Short form.
        } else {
            $user['domain'] = Yii::app()->params['host'];
            $user['username'] = $username;
        }
        return $user;
    }

    /**
     * Updates the password for a user.
     *
     * @return void
     */
    public function updatePassword() {
        $user_row = User::model()->find(
            array(
                'condition' => 'username=:username AND site_id=:site_id',
                'params' => array(
                    ':username' => $this->username,
                    ':site_id' => $this->site_id,
                ),
            )
        );
        $salt = $this->generateSalt();
        $hashed_password = $this->hashPassword($salt);
        $user_row->password = $hashed_password;
        $user_row->salt = $salt;
        $user_row->reset_secret = null;
        $user_row->reset_time = null;
        $user_row->save();
    }
}

?>