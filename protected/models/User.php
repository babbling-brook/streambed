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
 * Model for the user DB table.
 * The table holds information about a user.
 *
 * @package PHP_Models
 */
class User extends CActiveRecord
{

    /**
     * The primary key for this user. Also acts as the users id.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The users username.
     *
     * @var string
     */
    public $username;

    /**
     * The id of the site that this user belongs to.
     *
     * @var integer
     */
    public $site_id;

    /**
     * The hash of htis users password.
     *
     * @var string
     */
    public $password;

    /**
     * This users email address.
     *
     * @var string
     */
    public $email;

    /**
     * What is the role of this user. See user.role in the lookup table for options.
     *
     * @var integer
     */
    public $role;

    /**
     * Does this user represent a ring.
     *
     * @var boolean
     */
    public $is_ring;

    /**
     * If set to '1' then this is a test user. All test user data is regularly deleted and reset.
     *
     * @var boolean
     */
    public $test_user;

    /**
     * The meta post id for this user/ring. Used for disscussion about this user.
     *
     * @var integer
     */
    public $meta_post_id;

    /**
     * When a user requests a password reset, this secret is sent in the email and must match to reset the password.
     *
     * @var integer
     */
    public $reset_secret;

    /**
     * Time for when a reset_secret is made.
     *
     * @var string
     */
    public $reset_time;

    /**
     * Cross site forgery request token. Changed on each login.
     *
     * @var integer
     */
    public $csfr;

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
        return '{{user}}';
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'site' => array(self::BELONGS_TO, 'Site', 'site_id', 'joinType' => 'INNER JOIN'),
            //'site' => array(self::HAS_MANY, 'Site', 'user_id', 'joinType' => 'INNER JOIN'),
            'site_access' => array(self::HAS_MANY, 'SiteAccess', 'user_id'),
            'transaction' => array(self::HAS_MANY, 'Transaction', 'user_id'),
            'rhythm' => array(self::HAS_MANY, 'Rhythm', 'rhythm_id'),
            'stream' => array(self::HAS_MANY, 'Stream', 'user_id'),
        );
    }

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('username, password, ', 'required'),
            array('username, password, email', 'length', 'max' => 128),
            array('username', 'isUserUnique', 'on' => 'new'),
            array('username', 'safe', 'on' => 'search'),
            array('email', 'CEmailValidator'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'username' => 'Username',
            'password' => 'Password',
            'email' => 'Email',
        );
    }

    /**
     * Checks if a username is unique.
     *
     * @return void
     */
    public function isUserUnique() {
        if ($this->hasErrors() === false) { // we only want to authenticate when no input errors
            $identity = new UserIdentity($this->username, $this->password, $this->site_id);
            $identity->usernameUnique();
            if ($identity->errorCode === UserIdentity::ERROR_USERNAME_INVALID) {
                $this->addError('username', 'Username is already in use. Please try another.');
            }
        }
    }

    /**
     * Search for a user.
     *
     * @param GetSelectionForm $fmodel A form representing a user search.
     *
     * @return array Array of users that match the search criteria.
     */
    public static function generateJSONSearch($fmodel) {
        if ($fmodel->only_rings === true && empty($fmodel->ring_type) === false) {
            return User::generateJSONSearchWithRingType($fmodel);
        }


        if ($fmodel->show_domain === false) {
            $fmodel->site_filter = Yii::app()->params['host'];
        }

        $sql = "
            SELECT
                 user.username
                ,site.domain
            FROM
                user
                INNER JOIN site ON site.site_id = user.site_id
            WHERE
                (user.username LIKE :username OR :emptyusername = '')
                AND (site.domain LIKE :domain OR :emptydomain = '') ";

        if ($fmodel->only_rings === true) {
            $sql .=" AND user.is_ring = 1 ";
        }

        $sql .= "ORDER BY user.username, site.domain LIMIT "
            . ($fmodel->page - 1) * $fmodel->rows . ", " . $fmodel->rows;

        $command = Yii::app()->db->createCommand($sql);
        $user_filter = "%" . $fmodel->user_filter . "%";
        $site_filter = "%" . $fmodel->site_filter . "%";
        $command->bindValue(":domain", $site_filter, PDO::PARAM_STR);
        $command->bindValue(":username", $user_filter, PDO::PARAM_STR);
        $command->bindValue(":emptydomain", $fmodel->site_filter, PDO::PARAM_STR);
        $command->bindValue(":emptyusername", $fmodel->user_filter, PDO::PARAM_STR);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Search for Rings with a particular type.
     *
     * @param GetSelectionForm $fmodel A form contianing the search paramaters.
     *
     * @return array
     */
    public static function generateJSONSearchWithRingType($fmodel) {
        if ($fmodel->show_domain === 0) {
            $fmodel->site_filter = Yii::app()->params['host'];
        }

        $type_id = LookupHelper::getId("ring.membership_type", $fmodel->ring_type);

        $sql = "
            SELECT
                 user.username
                ,site.domain
            FROM user
                INNER JOIN site ON site.site_id = user.site_id
                INNER JOIN ring ON user.user_id = ring.user_id
            WHERE
                (user.username LIKE :username OR :emptyusername = '')
                AND (site.domain LIKE :domain OR :emptydomain = '')
                AND user.is_ring = 1
                AND ring.membership_type = :membership_type
            ORDER BY user.username, site.domain
            LIMIT " . ($fmodel->page - 1) * $fmodel->rows . ", " . $fmodel->rows;

        $command = Yii::app()->db->createCommand($sql);
        $user_filter = "%" . $fmodel->user_filter . "%";
        $site_filter = "%" . $fmodel->site_filter . "%";
        $command->bindValue(":domain", $site_filter, PDO::PARAM_STR);
        $command->bindValue(":username", $user_filter, PDO::PARAM_STR);
        $command->bindValue(":emptydomain", $fmodel->site_filter, PDO::PARAM_STR);
        $command->bindValue(":emptyusername", $fmodel->user_filter, PDO::PARAM_STR);
        $command->bindValue(":membership_type", $type_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Checks if this this user is a ring.
     *
     * @param string $domain The users home domian.
     * @param string $username The users username.
     *
     * @return boolean
     */
    public static function isRing($domain, $username) {
        $sql = "
            SELECT user.is_ring
            FROM
                user
                INNER JOIN site ON site.site_id = user.site_id
            WHERE
                user.username = :username
                AND site.domain = :domain";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":domain", $domain, PDO::PARAM_STR);
        $command->bindValue(":username", $username, PDO::PARAM_STR);
        $is_ring = $command->queryScalar();
        return (bool)$is_ring;    // queryScalar returns false if nothing found
    }

    /**
     * Checks if a full username is valid. If not found locally, will check the remote domain.
     *
     * @param string $full_username The domain/username for this user.
     *
     * @return integer local user_id
     * @fixme Instances of this need to use ValidUserForm
     */
    public static function isUserValid($full_username) {
        $name_parts = User::getNamePartsFromFullName($full_username, false);
        if ($name_parts === false) {
            return false;
        }

        $site_id = SiteMulti::getSiteID($name_parts[0], false, true);
        if ($site_id === false) {
            return false;
        }

        $user_multi = new UserMulti($site_id);
        $user_id= $user_multi->getIDFromUsername($name_parts[1], false, true);

        return $user_id;
    }

    /**
     * Is this user a local user.
     *
     * @param integer $user_id The id of the user to check.
     *
     * @return boolean
     */
    public static function isLocal($user_id) {
        $user_multi = new UserMulti;
        $user_model = $user_multi->getUser($user_id);
        if ($user_model->site_id !== Yii::app()->params['site_id']) {
            return false;
        }
        return true;
    }

    /**
     * Gets a user_id from a users useraname and domain.
     *
     * @param string $username The username of the user to check.
     * @param string $domain The domain of the user to check.
     *
     * @return integer|boolean user_id or false
     */
    public static function getIDFromUsernameAndDomain($username, $domain, $check_remote_store=true) {
        $site_id = SiteMulti::getSiteID($domain, $check_remote_store, $check_remote_store);
        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($username, false, $check_remote_store);
        return $user_id;
    }

    /**
     * Split a users full name into parts.
     *
     * @param string $fullname Breaks a fullname in the format of domain/username into an array of domain and username.
     * @param boolean $error Throw an error or not. If not it returns false.
     *
     * @return array The name parts. first element is domain, second is username.
     */
    public static function getNamePartsFromFullName($fullname, $error=true) {
        if (strpos($fullname, '/') === false && strpos($fullname, '@') === false) {
            if ($error === true) {
                throw new Exception("fullname is does not contain a forward slash: " . $fullname);
            } else {
                return false;
            }
        }
        if (strpos($fullname, '/') !== false) {
            $name_parts = explode("/", $fullname);
        } else {
            $name_parts = explode("@", $fullname);
        }
        if (count($name_parts) !== 2) {
            if ($error === true) {
                throw new Exception("fullname is not in correct format: " . $fullname);
            } else {
                return false;
            }
        }
        if (strpos($fullname, '@') > 0 ) {
            $domain = $name_parts[1];
            $name_parts[1] = $name_parts[0];
            $name_parts[0] = $domain;
        }
        return $name_parts;
    }


    /**
     * Gets a user_id from a users useraname and domain.
     *
     * @param string $full_username The domain/username for this user.
     *
     * @return integer user_id
     */
    public static function getIDFromFullName($full_username) {
        $name_parts = User::getNamePartsFromFullName($full_username);
        $user_id = User::getIDFromUsernameAndDomain($name_parts[1], $name_parts[0]);
        return $user_id;
    }

    /**
     * Create a meta post for this local user.
     *
     * @param string $username The name of the ring.
     * @param string $user_type Is this user a ring.
     * @param string $user_id The id of the user who owns this meta post.
     *
     * @return integer The metta post id.
     */
    public static function createMetaPost($username, $is_ring, $user_id) {
        if ($is_ring === true) {
            $user_type = 'ring';
        } else {
            $user_type = 'user';
        }

        $profile_title = 'http://' . Yii::app()->params['host'] . '/' . $username . '/profile';
        $profile_link = 'http://' . Yii::app()->params['host'] . '/' . rawurlencode($username) . '/profile';

        $result = PostMulti::insertPost(
            Yii::app()->params['meta_user_id'],
            array(
                array(
                    'display_order' => '1',
                    'text' => 'Discussion about the ' . $username . '@' . Yii::app()->params['host'] . ' ' . $user_type,
                ),
                array(
                    'display_order' => '2',
                ),
                array(
                    'display_order' => '3',
                    'link_title' => $profile_title,
                    'link' => $profile_link,
                ),
            ),
            $user_id
        );
        if (is_array($result) === true) {
            throw new Exception('Meta post for ' . $user_type . ' not submitting. ' . ErrorHelper::ary($result));
        } else if ($result === false) {
            throw new Exception(
                'Remote site not accepting new ' . $user_type . '.  Should never happen as it should be local.'
            );
        } else {
            return $result->post_id;
        }
    }

    /**
     * Fetch the meta post id for a user or ring.
     *
     * @param $user_id The id of this user.
     *
     * @return integer The meta post id.
     */
    public static function getMetaPostId($user_id) {
        $sql = "
            SELECT meta_post_id
            FROM user
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_id = $command->queryScalar();
        return $user_id;
    }

    /**
     * Sets the metapost of a user to NULL.
     *
     * @param $user_id The id of the user whose meta_post_id is being set to NULL.
     *
     * @return void
     */
    public static function setMetaPostToNull($user_id) {
        $sql = "
            UPDATE user
            SET meta_post_id = NULL
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Sets the meta_post of all users with a given post_id to NULL.
     *
     * @param $post_id The id of the post whose meta_post_id is being set to NULL.
     *
     * @return void
     */
    public static function setMetaPostToNullForPostId($post_id) {
        $sql = "
            UPDATE user
            SET meta_post_id = NULL
            WHERE meta_post_id = :post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Fetch both the username and the domain of a user and return them in an array.
     *
     * @param integer $user_id The id of the user to fetch.
     * @param boolean [$throw_error=true]  If the user is not found then throw an error if set to true
     *      , else return false if the user not found.
     *
     * @return array|false
     */
    public static function getFullUsernameParts($user_id, $throw_error=true) {

        $sql = "
            SELECT
                 username
                ,site_id
            FROM user
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user = $command->queryRow();

        if ($user === false) {
            if ($throw_error === false) {
                return false;
            } else {
                throw new Exception("user_id is not found.");
            }
        }

        $domain = SiteMulti::getDomain($user['site_id'], $throw_error);
        $user_array = array(
            'username' => $user['username'],
            'domain' => $domain,
        );
        return $user_array;
    }

    /**
     * Fetch the full username of a user.
     *
     * @param integer $user_id The id of the user to fetch.
     *
     * @return string
     */
    public static function getFullUsername($user_id) {
        $user_parts = self::getFullUsernameParts($user_id);
        if (is_string($user_parts) === true) {
            return $user_parts;
        } else {
            return $user_parts['domain'] . '/' . $user_parts['username'];
        }
    }


    /**
     * Fetch valid username suggestions based on a partial username.
     *
     * @param string $partial_username The partial username to search for suggestions on.
     *
     * @return array A list of suggested valid usernames.
     */
    public static function getSuggestions($partial_username) {
        $query = "
            SELECT username
            FROM user
            WHERE
                username LIKE :partial_username
                AND site_id = :site_id
            ORDER BY username
            LIMIT 10";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":partial_username", $partial_username . "%", PDO::PARAM_STR);
        $command->bindValue(":site_id", Yii::app()->params['site_id'], PDO::PARAM_INT);
        $username_rows = $command->queryAll();

        $usernames = array();
        foreach ($username_rows as $username_row) {
            $usernames[] = $username_row['username'];
        }

        return $usernames;
    }

    /**
     * Fetches a users email address from their username.
     *
     * @param string $username The username of the user to fetch an email address for.
     *
     * @return string|false
     */
    public static function getEmail($username) {
        $query = "
            SELECT email
            FROM user
            WHERE username = :username AND site_id = :site_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":username", $username, PDO::PARAM_STR);
        $command->bindValue(":site_id", Yii::app()->params['site_id'], PDO::PARAM_INT);
        $email = $command->queryScalar();
        if ($email === false) {
            return false;
        } else if (empty($email) === true) {
            return false;
        } else {
            return $email;
        }
    }

    /**
     * Stores the reset secret for a user when they request a reset email.
     *
     * @param string $g_username The username of the user whose reset secret is being stored.
     * @param string $reset_secret The secret to store.
     *
     * @return void
     */
    public static function storeResetSecret($username, $reset_secret) {
        $user_row = User::model()->find(
            array(
                'condition' => 'username=:username AND site_id=:site_id',
                'params' => array(
                    ':username' => $username,
                    ':site_id' => Yii::app()->params['site_id'],
                ),
            )
        );
        $user_row->reset_secret = $reset_secret;
        $user_row->reset_time = date('Y-m-d H:i:s');
        $user_row->save();
    }

    /**
     * Checks that a reset secret is valid.
     *
     * @param string $g_username The username of the user whose reset secret is being stored.
     * @param string $reset_secret The secret to store.
     *
     * @return boolean
     */
    public static function checkResetSecret($username, $reset_secret) {
        $query = "
            SELECT reset_secret
            FROM user
            WHERE
                username = :username
                AND site_id = :site_id
                AND (reset_time + INTERVAL 1 DAY) > :now";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":username", $username, PDO::PARAM_STR);
        $command->bindValue(":site_id", Yii::app()->params['site_id'], PDO::PARAM_INT);
        $command->bindValue(":now", date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $t = date('Y-m-d H:i:s');
        $stored_reset_Secret = $command->queryScalar();
        if ($stored_reset_Secret === false || $stored_reset_Secret !== $reset_secret) {
            return false;
        } else {
            return true;
        }
    }

    public static function createNewCSFRToken($user_id) {
        $new_csfr_token = CryptoHelper::makeGuid();
        User::model()->updateByPk(
            $user_id,
            array(
                'csfr' => $new_csfr_token,
            )
        );
    }

    /**
     * Delete a user by its user_id.
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $user_id The id of the user used to delete the user.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM user
                WHERE user_id = :user_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            $command->execute();

        } catch (Exception $e) {
            throw new Exception(
                'User::deleteByUserId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Is this user a test user.
     *
     * @param integer $user_id The id of this user.
     *
     * @return boolean
     */
    public static function isTestUser($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT test_user
            FROM user
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $test_user = (bool)$command->queryScalar();
        return $test_user;
    }

    /**
     * Flags all the test users in preperation for their data being deleted.
     *
     * @return void
     */
    public static function markTestUsers() {
        $sql = "UPDATE user SET test_user = 1 WHERE username like 'test%'";
        $command = Yii::app()->db->createCommand($sql);
        $command->execute();
    }

    /**
     * Gets the user_ids for all test users.
     *
     * @return array
     */
    public static function getAllTestUserIds() {
        $connection = Yii::app()->db;
        $sql = "
            SELECT user_id
            FROM user
            WHERE test_user = 1";
        $command = $connection->createCommand($sql);
        $test_user_ids = $command->queryColumn();
        return $test_user_ids;
    }

    /**
     * Select a row of user data.
     *
     * @param type $user_id The id of the user to select a row of data for.
     *
     * @return array. Indexed by column name.
     */
    public static function getRowByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_row = $command->queryRow();
        return $user_row;
    }
}

?>
