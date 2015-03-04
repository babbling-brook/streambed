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
 * A collection of functions that affect multiple db tables to do with users.
 *
 * @package PHP_Model_Forms
 */
class UserMulti
{

    const PASSWORD_ERROR = -1;
    const USER_ERROR = -2;
    const SALT_ERROR = -3;

    /**
     * The id of the domain that this user belongs to.
     *
     * @var type
     */
    private $site_id;

    /**
     * Constructor.
     *
     * @param string $site_id The id for the domain of the domus for this user.
     */
    public function __construct($site_id=null) {
        // default the domain to this site
        if ($site_id === null) {
            $site_id = Yii::app()->params['site_id'];
        }
        $this->site_id = $site_id;
    }

    /**
     * Get a usernames primary key.
     *
     * @param string $username The username we are fetching an id for.
     * @param boolean $error True == throw an error, False == return false.
     * @param boolean $remote Try and fetch from the remote site if requested.
     *
     * @return integer|boolean primary key for the supplied username or false.
     */
    public function getIDFromUsername($username, $error=true, $remote=false) {
        $row = User::model()->find(
            array(
                'condition' => 'username=:username AND site_id=:site_id',
                'params' => array(
                    ':username' => strtolower($username),
                    ':site_id' => $this->site_id,
                )
            )
        );

        if (isset($row->user_id) === false) {
            if ($remote === true) {
                $domain = SiteMulti::getDomain($this->site_id);
                $odh = new OtherDomainsHelper($domain);
                $user_exists = $odh->checkUserExists($username);
                if ($user_exists === true) {
                    $user_id = $this->insertRemoteUser($username);
                    return $user_id;
                }
            }
            if ($error === true) {
                throw new Exception("Username not found");
            } else {
                return false;
            }
        }
        return (int)$row->user_id;
    }

    /**
     * Fetch the Username for a user given their ID.
     *
     * @param integer $user_id The id of the user we are fetching a username for.
     *
     * @return string|boolean Username or false.
     */
    public function getUsernameFromID($user_id) {
        $row = User::model()->find(
            array(
                'select' => 'username',
                'condition' => 'user_id=:user_id',
                'params' => array(
                    ':user_id' => $user_id,
                ),
            )
        );
        if (isset($row) === true) {
            return $row->username;
        } else {
            return false;
        }
    }

    /**
     * Return if a user exists.
     *
     * @param string $username The username we are checking exists.
     * @param boolean $check_remote If the user is from a remote domain, should it be checked.
     * @param string $domain If a remote site is being checked then include the domain here to avoid it being looked up.
     *
     * @return boolean true if the user is found, else False
     */
    public function userExists($username, $check_remote=false, $domain=null) {

        $row = User::model()->find(
            array(
                'select' => 'user_id',
                'condition' => 'username=:username AND site_id=:site_id',
                'params' => array(
                    ':username' => strtolower($username),
                    ':site_id' => $this->site_id,
                )
            )
        );

        if (isset($row) === false) {
            if ($check_remote === true) {
                if (isset($domain) === false) {
                    $domain = SiteMulti::getDomain($this->site_id);
                }
                $odh = new OtherDomainsHelper($domain);
                $user_exists = $odh->checkUserExists($username);
                if ($user_exists === true) {
                    $this->insertRemoteUser($username);
                    return true;
                } else {
                    return false;
                }
            }

            return false;
        }
        return true;
    }

    /**
     * Validates a user with a hashed password (used to log in).
     *
     * @param string $username The username we are checking is valid.
     * @param string $password_hash The hash of the users password.
     *
     * @return integer ID of user or failure status (see class constants).
     */
    public function userValid($username, $password_hash) {
        $row = User::model()->find(
            array(
                'condition' => 'username=:username AND site_id=:site_id AND password=:password',
                'params' => array(
                    ':username' => strtolower($username),
                    ':password' => $password_hash,
                    ':site_id' => $this->site_id,
                ),
            )
        );
        if (is_null($row) === true) {
            if ($this->userExists($username) === true) {
                return self::PASSWORD_ERROR;
            } else {
                return self::USER_ERROR;
            }
        }
        return $row->user_id;
    }

    /**
     * Retrieve the password salt for a user.
     *
     * @param string $username The username we are fetching a salt for.
     *
     * @return string The Salt
     */
    public function getSalt($username) {
        $params = array(
            ':username' => strtolower($username),
            ':site_id' => $this->site_id,
        );
        $row = User::model()->find(
            array(
                'condition' => 'username=:username AND site_id=:site_id',
                'params' => $params,
            )
        );

        if (is_null($row) === true) {
            return self::USER_ERROR;
        }
        if ($row->salt === "") {
            return self::SALT_ERROR;
        }

        return $row->salt;
    }

    /**
     * Insert a new user.
     *
     * @param string $username The username of the new user.
     * @param string $hashed_password The password hash for the new user.
     * @param string $salt That was used to hash the password.
     * @param string $email The users email address.
     * @param boolean $ring Is this user also a ring.
     *
     * @return integer The new users primary key.
     */
    public function insertUser($username, $hashed_password, $salt, $email=null, $ring=false) {

        if ($this->userExists($username) === true) {
            throw new Exception("Error inserting a user. User already exists");
        }

        $user = new User;
        $user->username = strtolower($username);
        $user->site_id = $this->site_id;
        $user->password = $hashed_password;
        $user->salt = $salt;
        $user->email = $email;
        $user->is_ring = $ring;
        $user->role = LookupHelper::getID('user.role', 'standard');
        $user->setScenario('new');

        if ($user->save() === false) {
            throw new Exception("Error inserting a user");
        }

        // If this user is local then create a meta post for it in the meta user stream.
        if ($user->site_id === Yii::app()->params['site_id']) {
            $user->meta_post_id = User::createMetaPost($user->username, $ring, $user->getPrimaryKey());
            $user->setScenario(null);
            if ($user->save() === false) {
                throw new Exception(
                    "Error inserting a meta post id for a user :" . ErrorHelper::model($user->getErrors())
                );
            }
        }

        return $user->getPrimaryKey();
    }

    /**
     * Insert a remote user.
     *
     * @param string $username The username to insert.
     *
     * @return integer The id of the inserted user.
     */
    public function insertRemoteUser($username) {
        if ($this->userExists($username) === true) {
            throw new Exception("Error inserting a remote user. User already exists.");
        }

        if ($this->site_id === Yii::app()->params['site_id']) {
            throw new Exception("Error inserting a remote user. Can not insert a remote user with a local site_id.");
        }

        $user_id = $this->insertUser($username, "remote_user", "");
        return $user_id;
    }

    /**
     * Fetch a users site_id.
     *
     * @param integer $user_id The id of the user we are fetching a site_id for.
     *
     * @return integer A site_id
     */
    public function getSiteID($user_id) {
        throw new Exception("UserMulti->getSiteId has been discontinued. Use SiteMulti::getSiteID");
    }

    /**
     * Fetch a user from their ID.
     *
     * @param integer $user_id The id of the user we are fetching.
     *
     * @return User A user model.
     */
    public function getUser($user_id) {
        $user = User::model()->findByPk($user_id);

        if (isset($user) === false) {
            throw new Exception("User not found");
        }

        return $user;
    }


    /**
     * Fetch a user from their username.
     *
     * @param string $username The username of the user we are fetching.
     *
     * @return User A user model.
     */
    public function getUserFromUserName($username) {
        return User::model()->find(
            array(
                "condition" => "username=:username AND site_id=:site_id",
                "params" => array(
                    ":username" => $username,
                    ":site_id" => $this->site_id,
                )
            )
        );
    }

    /**
     * Fetch an array of the sites that this user is logged onto, along with their secrets.
     *
     * @param integer $user_id The id of the user we are fetch site access data for.
     *
     * @return array An array of table rows. Each containing site access details.
     */
    public function getSiteAccess($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 site.domain
            FROM site INNER JOIN site_access ON site.site_id = site_access.site_id
            WHERE
                site_access.user_id = :user_id
                AND login_expires < NOW()
                AND site_access.session_id = :session_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $session_id = Yii::app()->session->sessionID;    // errors if used inline
        $command->bindValue(":session_id", $session_id, PDO::PARAM_STR);
        $rows = $command->queryAll();
        $sites = array();
        foreach ($rows as $row) {
            $sites[$row['domain']] = true;
        }
        return $sites;
    }

    /**
     * Generate search results for users.
     *
     * @param UserSearchForm $fmodel Contains the search paramaters.
     *
     * @return array An array of search result rows.
     */
    public static function searchForUsers($fmodel) {

        $connection = Yii::app()->db;
        $sql = "
            SELECT
                 site.domain AS domain
                ,user.username
                ,user.is_ring";


        if (isset($fmodel->ring_filter_id) === true) {
            $sql .= "
                ,CASE
                    WHEN user_ring.ban IS NULL THEN 'false'
                    WHEN user_ring.ban = 0 THEN 'false'
                    ELSE 'true'
                 END AS ring_ban";
        }

        $sql .= "
            FROM
                user
                INNER JOIN site ON user.site_id = site.site_id ";

        if ($fmodel->user_type === 'ring' && $fmodel->only_joinable_rings === true) {
            $sql .= "
                INNER JOIN ring AS ring_type
                    ON user.user_id = ring_type.user_id AND (
                        ring_type.membership_type = :public_membership_type
                        OR ring_type.membership_type = :request_membership_type
                    )";
        }

        if (isset($fmodel->ring_filter_id) === true) {
            $sql .= "
                INNER JOIN user_ring
                    ON user.user_id = user_ring.user_id AND (user_ring.admin != 1 OR user_ring.admin IS NULL)
                INNER JOIN ring AS ring_filter
                    ON user_ring.ring_id = ring_filter.ring_id AND ring_filter.ring_id = :ring_filter_id";
        }

        if (isset($fmodel->ring_vet_id) === true) {
            $sql .= "
                INNER JOIN ring_application
                    ON user.user_id = ring_application.user_id AND ring_application.ring_id = :ring_vet_id";
        }

        $sql .= "
            WHERE 0 = 0";

        if (strlen($fmodel->domain_filter) > 0) {
            if ($fmodel->exact_match['domain'] === true) {
                $sql .= " AND site.domain = :domain_filter";
            } else {
                $sql .= " AND site.domain LIKE CONCAT('%', :domain_filter, '%')";
            }
        }
        if (strlen($fmodel->username_filter) > 0) {
            if ($fmodel->exact_match['username'] === true) {
                $sql .= " AND user.username = :username_filter";
            } else {
                $sql .= " AND user.username LIKE CONCAT('%', :username_filter, '%')";
            }
        }
        if ($fmodel->user_type === 'ring') {
            $sql .= " AND user.is_ring = 1";
        } else if ($fmodel->user_type === 'user') {
            $sql .= " AND user.is_ring = 0";
        }
        if ($fmodel->include_test_users === false) {
            $sql .= " AND user.test_user = false";
        }

        if (isset($fmodel->ring_filter_id) === true && $fmodel->ring_ban_filter !== 'all') {
            if ($fmodel->ring_ban_filter === 'banned') {
                $sql .= " AND user_ring.ban = 1";
            } else {
                $sql .= " AND (user_ring.ban != 1 OR user_ring.ban IS NULL)";
            }
        }

        $sql .= " ORDER BY ";
        $order_by_comma = '';
        foreach ($fmodel->sort_priority as $sort) {
            if ($fmodel->sort_order[$sort] === 'ascending') {
                $sort_direction = ' ';
            } else {
                $sort_direction = ' DESC ';
            }
            switch ($sort) {
                case 'domain':
                    $sql .= $order_by_comma . 'site.domain ' . $sort_direction;
                    break;
                case 'username':
                    $sql .= $order_by_comma . 'user.username ' . $sort_direction;
                    break;
                case 'user_type':
                    $sql .= $order_by_comma . 'user.is_ring ' . $sort_direction;
                    break;
                case 'ring_ban':
                    if (isset($fmodel->ring_filter_id) === true) {
                        $sql .= $order_by_comma . 'user_ring.ban ' . $sort_direction;
                    }
                    break;
            }
            $order_by_comma = ', ';
        }

        $sql .= " LIMIT :start, :row_qty";

        $command = $connection->createCommand($sql);

        if (strlen($fmodel->domain_filter) > 0) {
            $command->bindValue(":domain_filter", $fmodel->domain_filter, PDO::PARAM_STR);
        }
        if (strlen($fmodel->username_filter) > 0) {
            $command->bindValue(":username_filter", $fmodel->username_filter, PDO::PARAM_STR);
        }
        if ($fmodel->user_type === 'ring' && $fmodel->only_joinable_rings === true) {
            $command->bindValue(
                ":public_membership_type",
                LookupHelper::getID('ring.membership_type', 'public'),
                PDO::PARAM_STR
            );
            $command->bindValue(
                ":request_membership_type",
                LookupHelper::getID('ring.membership_type', 'request'),
                PDO::PARAM_STR
            );
        }
        $command->bindValue(":start", intval(($fmodel->page - 1) * 10), PDO::PARAM_INT);
        $command->bindValue(":row_qty", intval($fmodel->row_qty), PDO::PARAM_INT);
        if (isset($fmodel->ring_filter_id) === true) {
            $command->bindValue(":ring_filter_id", intval($fmodel->ring_filter_id), PDO::PARAM_INT);
        }
        if (isset($fmodel->ring_vet_id) === true) {
            $command->bindValue(":ring_vet_id", intval($fmodel->ring_vet_id), PDO::PARAM_INT);
        }

        $users = $command->queryAll();

        return $users;
    }

    /**
     * Fetches all data associated with a user ready for download to a json object
     *
     * @param integer $user_id The id of the user whose data is to be fetched.
     *
     * @return array An array of user data indexed by table and column names.
     */
    public static function getAllUserData($user_id) {
        $user_data = array();
//        $user_data['invitation'] =  // to_user and from_user.
//                                    // convert ring_id to domain and username
//                                    // convert type(id) to type(string)
//                                    // convert to_user_id and from_user_id to domain and username
//        $user_data['kindred'] =     // only user_id (not kindred_user_id)
//                                    // convert kindred_user_id to username and domain.
//                                    // convert user_rhythm_id to rhythm name object.
//        $user_data['post'] =        // convert site_id to domain.
//                                    // convert stream_extra_id to stream name object
//                                    // convert parent to domain + parent.
//                                    // convert top_parent to domain + parent
//                                    // convert status(int) to text.
//        $user_data['invitation'] =  // to_user and from_user
//        $user_data['invitation'] =  // to_user and from_user
//        $user_data['invitation'] =  // to_user and from_user
//        $user_data['invitation'] =  // to_user and from_user



        return $user_data;
    }

    /**
     * Fetch suggestions that match both domain and username.
     *
     * @param string $partial_domain The partial domain to match.
     * @param string $partial_username The partial username to match.
     *
     * @return array of data ready for json parsing.
     */
    public static function getDomainAndUsernameSuggestions($partial_domain, $partial_username) {
        $query = "
            SELECT
                 site.domain
                ,user.username
            FROM
                site
                INNER JOIN user ON user.site_id = site.site_id
            WHERE
                site.domain LIKE :partial_domain
                AND user.username LIKE :partial_username
            ORDER BY domain, username
            LIMIT 10";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":partial_domain", $partial_domain . "%", PDO::PARAM_STR);
        $command->bindValue(":partial_username", $partial_username . "%", PDO::PARAM_STR);
        $rows = $command->queryAll();

        $suggestions = array();
        foreach ($rows as $row) {
            $suggestions[] = $row['username'] . '@' . $row['domain'];
        }

        return $suggestions;
    }

    /**
     * Get a users username and domain.
     *
     * @param integer $user_id The id of the user to fetch details for.
     *
     * @return array.
     */
    public static function getUserNameArray($user_id) {
        $query = "
            SELECT
                 site.domain
                ,user.username
            FROM
                site
                INNER JOIN user ON user.site_id = site.site_id
            WHERE user.user_id = :user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user = $command->queryRow();
        return $user;
    }
}

?>
