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
 * Model for the user_ring DB table.
 * The is a link table for users who are subscribed to rings.
 *
 * @package PHP_Models
 */
class UserRing extends CActiveRecord
{

    /**
     * The priamry key for this relationship.
     *
     * @var integer
     */
    public $user_ring_id;

    /**
     * The id of the ring that the user is subscribed to.
     *
     * @var integer
     */
    public $ring_id;

    /**
     * The id of the user who is subscribed to this ring.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The password that this user uses to validate that the user really is the user they claim to be.
     *
     * This is used behind the scenes by javascript - it is never passed to client sites, only to the users
     * data store, which comminicates directly with the rings data store.
     *
     * @var string
     */
    public $password;

    /**
     * Is this an admin subscription.
     *
     * @var boolean
     */
    public $admin;

    /**
     * Is this a member subscription.
     *
     * @var boolean
     */
    public $member;

    /**
     * Is this user banned.
     *
     * @var boolean
     */
    public $ban;


    /**
     * Enables searching on gridviews (from user).
     *
     * Not a table column.
     *
     * @var string
     */
    public $username;

    /**
     * Enables searching on gridviews (from user.site).
     *
     * Not a table column.
     *
     * @var string
     */
    public $domain;

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
        return 'user_ring';
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
            array('ring_id, user_id, password', 'required'),
            array('ring_id, user_id, password', 'numerical', 'integerOnly' => true),
            array('password', 'length', 'max' => 255),
            array('username, domain', 'safe', 'on' => 'search'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            //array('user_ring, ring_id, user_id, password', 'safe', 'on' => 'search'),
        );
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
            'user' => array(self::BELONGS_TO, 'User', 'user_id', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'user_ring_id' => 'User Ring',
            'ring_id' => 'Ring',
            'user_id' => 'User',
            'password' => 'Password',
            'username' => 'Name',
            'domain' => 'Domain',
        );
    }

    /**
     * Get the rings that a user is a member or administrotor of.
     *
     * @param integer $user_id The id of the user that represents this ring.
     * @param integer [$ring_id] The id of the specific ring to fetch details for. If ommited then all are fetched.
     *
     * @return array
     */
    public static function getConfig($user_id, $ring_id=null) {
        $query = "
            SELECT
                user.username AS name
               ,site.domain
               ,COALESCE(user_ring.member, 0) AS member
               ,COALESCE(user_ring.admin, 0) AS admin
               ,member_type_lookup.value AS member_type
               ,admin_type_lookup.value AS admin_type
               ,ring.ring_id
           FROM
               user_ring
               INNER JOIN ring ON user_ring.ring_id = ring.ring_id
               INNER JOIN user ON ring.user_id = user.user_id
               INNER JOIN site ON user.site_id = site.site_id
               INNER JOIN lookup AS member_type_lookup ON member_type_lookup.lookup_id = ring.membership_type
               INNER JOIN lookup AS admin_type_lookup ON admin_type_lookup.lookup_id = ring.admin_type
           WHERE
               user_ring.user_id = :user_id
               AND (ring.ring_id = :ring_id OR :ring_id IS NULL)
               AND user_ring.ban IS NULL
               AND (user_ring.member = 1 OR user_ring.admin = 1)
           ORDER BY user.username DESC";    // Order by descending as they are prepended; resulting in ASC order.
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        if (isset($ring_id) === false) {
            $command->bindValue(":ring_id", null, PDO::PARAM_NULL);
        } else {
            $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        }
        $rings = $command->queryAll();

        foreach ($rings as $key => $ring) {
            $take_names = RingTakeName::getForRing($ring['ring_id']);
            // Remove the stuff we don't want downloading to the client
            foreach ($take_names as $name_key => $name) {
                unset($take_names[$name_key]['ring_take_name_id']);
                unset($take_names[$name_key]['stream_id']);
                unset($take_names[$name_key]['stream_version']);
            }
            $rings[$key]['take_names'] = $take_names;
            unset($rings[$key]['ring_id']);
        }

        return $rings;
    }

    /**
     * Get the rings that a user has the permision to send admin or membership invites to due to super_ring permissions.
     *
     * @param integer $user_id The id of the user that represents the ring that we are fetching permissions for.
     *
     * @return array Contains two sub arrays, one for admin permissions and another for members.
     */
    public static function getSuperInviters($user_id) {
        // @fixme need to get ability to send invites as member and admin. Easy to check amdin_tyupe and member type
        // but what about super groups in contorl of invitations?

        // Super member rings
        $query = "SELECT
                      user.username AS name
                     ,site.domain
                     ,1 AS member
                     ,0 AS admin
                     ,member_type_lookup.value AS member_type
                     ,'' AS admin_type
                     ,'true' AS super_ring
                  FROM
                  user_ring
                  -- We need the child ring for its name details and the super ring for permissions
                  INNER JOIN ring AS child_ring ON user_ring.ring_id = child_ring.ring_id
                  INNER JOIN user ON child_ring.user_id = user.user_id
                  INNER JOIN site ON user.site_id = site.site_id
                  INNER JOIN user AS super_user ON child_ring.membership_super_ring_user_id = super_user.user_id
                  INNER JOIN ring AS super_ring ON super_ring.user_id = super_user.user_id
                  INNER JOIN lookup AS member_type_lookup ON member_type_lookup.lookup_id = child_ring.membership_type
                  WHERE
                      user_ring.user_id = :user_id
                      AND member_type_lookup.value = 'super_ring'
                      AND user_ring.ban IS NULL
                  ORDER BY user.username DESC";    // Order by descending as they are prepended; resulting in ASC order.
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $membership_super_rings = $command->queryAll();

        // Super admin rings
        $query = "SELECT
                      user.username AS name
                     ,site.domain
                     ,0 AS member
                     ,1 AS admin
                     ,'' AS member_type
                     ,admin_type_lookup.value AS admin_type
                  FROM
                  user_ring
                  -- We need the child ring for its name details and the super ring for permissions
                  INNER JOIN ring AS child_ring ON user_ring.ring_id = child_ring.ring_id
                  INNER JOIN user ON child_ring.user_id = user.user_id
                  INNER JOIN site ON user.site_id = site.site_id
                  INNER JOIN user AS super_user ON child_ring.admin_super_ring_user_id = super_user.user_id
                  INNER JOIN ring AS super_ring ON super_ring.user_id = super_user.user_id
                  INNER JOIN lookup AS admin_type_lookup ON admin_type_lookup.lookup_id = child_ring.admin_type
                  WHERE
                      user_ring.user_id = :user_id
                      AND admin_type_lookup.value = 'super_ring'
                      AND user_ring.ban IS NULL
                  ORDER BY user.username DESC";    // Order by descending as they are prepended; resulting in ASC order.
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $admin_super_rings = $command->queryAll();

        return array(
            "member_super_rings" => $membership_super_rings,
            "admin_super_rings" => $admin_super_rings,
        );

    }

    /**
     * Check if a user has admin privilages.
     *
     * @param integer $ring_id The id of the ring that we are checking.
     * @param integer $user_id The id of the user we are checking has admin permissions for.
     *
     * @return boolean
     */
    public static function checkIfAdmin($ring_id, $user_id) {
        // Check admin_type for this ring
        $admin_type = Ring::getAdminType($ring_id);

        // Might have to check if a member of the super ring rather than this one
        if ($admin_type === "super_ring") {
            $super_ring_id = Ring::getAdminSuperRing($ring_id);
            return UserRing::checkIfMember($super_ring_id, $user_id);

        } else {
            $sql = "
                SELECT user_ring.ring_id
                FROM
                    user_ring
                WHERE
                    user_id = :user_id
                    AND ring_id = :ring_id
                    AND admin = 1";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
            $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            $row = $command->queryScalar();
            if ($row !== false) {
                return true;
            } else {
                return false;
            }
        }
    }



    /**
     * Check if a user has member privilages.
     *
     * @param integer $ring_id The id of the ring we are checking.
     * @param integer $user_id The id of the user we are checking has member privilages for.
     *
     * @return boolean
     */
    public static function checkIfMember($ring_id, $user_id) {
        $sql = "
            SELECT
                 user_ring.ring_id
            FROM
                user_ring
            WHERE
                user_id = :user_id
                AND ring_id = :ring_id
                AND member = 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_ring_id = $command->queryScalar();
        if ($user_ring_id !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove all admins from a ring.
     *
     * Use carefully! This will lock all administrators out. The ring admin_type needs to be set to a super_ring
     *
     * @param integer $ring_id The id to remove admins for.
     *
     * @return void
     * @fixme ensure the ring admin_type is set to a super_ring before proceeding.
     */
    public static function removeAllAdmins($ring_id) {
        $sql = "UPDATE user_ring SET admin = null WHERE ring_id = :ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Set a user as an admin of a ring.
     *
     * @param integer $ring_id The id of the ring to set a user to be admin of.
     * @param integer $user_id The id of the user to set as an administrator.
     *
     * @return void
     */
    public static function setAdmin($ring_id, $user_id) {
        $sql = "UPDATE user_ring SET admin = 1 WHERE ring_id = :ring_id AND user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Insert or change the permission of a user on a ring.
     *
     * @param integer $ring_id The ring to change perimissions on.
     * @param integer $user_id The id of the user to change permissions on.
     * @param string $type The type of permission to grant. Valid values are 'admin' or 'member'.
     * @param boolean $access Are we changing the permission to true or false.
     *
     * @return void
     */
    public static function changePermission($ring_id, $user_id, $type, $access) {
        $user_ring_id = UserRing::getIDByRingAndUser($ring_id, $user_id);
        if ($user_ring_id === false) {
            $is_admin = $is_member = false;
            if ($type === "admin" && $access === true) {
                $is_admin = true;
            }
            if ($type === "member" && $access === true) {
                $is_member = true;
            }
            UserRing::createUserAccess($ring_id, $user_id, $is_member, $is_admin);
        } else {
            if ($type === "admin") {
                UserRing::changeAdminPermission($user_ring_id, $access);
            }
            if ($type === "member") {
                UserRing::changeMembershipPermission($user_ring_id, $access);
            }
        }
    }

    /**
     * Change the membership and admin permissions for a user in a ring.
     *
     * @param integer $user_ring_id The id of the users ring subscription.
     * @param boolean $admin True to grant admin permissions. False to remove.
     *
     * @return void
     */
    public static function changeAdminPermission($user_ring_id, $admin) {
        $sql = "UPDATE user_ring SET admin = :admin WHERE user_ring_id = :user_ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":admin", $admin, PDO::PARAM_INT);
        $command->bindValue(":user_ring_id", $user_ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Change the membership and admin permissions for a user in a ring.
     *
     * @param integer $user_ring_id The id of the ring to change permissions for.
     * @param boolean $member Is Grant membership or not.
     *
     * @return void
     */
    public static function changeMembershipPermission($user_ring_id, $member) {
        $sql = "UPDATE user_ring SET member = :member WHERE user_ring_id = :user_ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":member", $member, PDO::PARAM_INT);
        $command->bindValue(":user_ring_id", $user_ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Get the primary key by the ring_id and user_id.
     *
     * @param integer $ring_id The id of the ring to fetch a user_ring_id for.
     * @param integer $user_id The id of the user to fetch a user_ring_id for.
     *
     * @return integer|boolean
     */
    public static function getIDByRingAndUser($ring_id, $user_id) {
        $sql = "
            SELECT
                 user_ring.user_ring_id
            FROM
                user_ring
            WHERE
                user_id = :user_id
                AND ring_id = :ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_ring_id = $command->queryScalar();
        if ($user_ring_id !== 0) {
            return $user_ring_id;
        } else {
            return false;
        }
    }

    /**
     * Insert this user with access to a ring.
     *
     * @param integer $ring_id The id of the ring to create access permission for.
     * @param integer $user_id The id of the user to grant permissions to.
     * @param boolean $is_member Is this user a member.
     * @param boolean $is_admin Is this user an admin.
     *
     * @return void
     */
    public static function createUserAccess($ring_id, $user_id, $is_member, $is_admin) {
        $password = UserRing::generatePassword();

        if ($is_admin === true) {
            $is_admin = 1;
        } else {
            $is_admin = null;
        }
        if ($is_member === true) {
            $is_member = 1;
        } else {
            $is_member = null;
        }

        $sql = "
            INSERT INTO user_ring
            (
                ring_id
                ,user_id
                ,password
                ,admin
                ,member
            )VALUES(
                :ring_id
                ,:user_id
                ,:password
                ,:is_admin
                ,:is_member
            )";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":is_admin", $is_admin, PDO::PARAM_INT);
        $command->bindValue(":is_member", $is_member, PDO::PARAM_INT);
        $command->bindValue(":password", $password, PDO::PARAM_STR);

        $row = $command->execute();


        // If the user is a member of this data store then we can store the access password locally.
        // If the user is remote then we need to send it to their data store.
        // @fixme this needs https
        if (Yii::app()->user->getSiteID() === Yii::app()->params['site_id']) {
            $ring_user_id = Ring::getRingUserId($ring_id);
            UserRingPassword::insertPassword(Yii::app()->user->getId(), $ring_user_id, $password);

        } else {
            // @fixme send password to the users data store.
            $temp = true;
        }

    }

    /**
     * Generate a new password for a ring.
     *
     * @return string
     */
    public static function generatePassword() {
        return UserIdentity::createRandomPassword();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions for users in a ring.
     *
     * @param integer $ring_id The id of the ring to list the members of.
     *
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function listUsers($ring_id) {
        $criteria = new CDbCriteria;
        $criteria->with = array(
            'user' => array(
                'select' => 'username',
                'joinType' => 'INNER JOIN',
            ),
            'user.site' => array(
                'select' => 'domain',
                'joinType' => 'INNER JOIN',
            ),
        );
        $criteria->addCondition("t.ring_id = " . $ring_id);
        $criteria->addCondition("t.member = 1 OR t.ban = 1");
        $criteria->compare('user.username', $this->username, true);
        $criteria->compare('site.domain', $this->domain, true);

        return new CActiveDataProvider(
            new UserRing,
            array(
                'criteria' => $criteria,
                'pagination' => array(
                    'pageSize' => 20,
                ),
                'sort' => array(
                    'defaultOrder' => 'domain, username',
                    'attributes' => array(
                        'domain' => array(
                            'asc' => 'domain, username',
                            'desc' => 'domain DESC, username',
                        ),
                        'username' => array(
                            'asc' => 'username, domain',
                            'desc' => 'username DESC, domain',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Ban of reinstate a user.
     *
     * User relationships are never deleted once created - so that banned users can be tracked.
     *
     * @param integer $ring_id The id of the ring to ban a user from.
     * @param integer $ban_user_id The id of the user to ban.
     * @param integer|null $ban Set to 1 to ban and null to reinstate.
     *
     * @return void
     */
    public static function banUser($ring_id, $ban_user_id, $ban) {
        if ($ban === 1) {
            $member = 0;
        } else {
            $member = 1;
        }
        $sql = "
            UPDATE user_ring
            SET
                 member = :member
                ,ban = :ban
            WHERE
                ring_id = :ring_id
                AND user_id = :ban_user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":ban_user_id", $ban_user_id, PDO::PARAM_INT);
        $command->bindValue(":ban", $ban, PDO::PARAM_INT);
        $command->bindValue(":member", $member, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Check if a user is banned from a ring.
     *
     * @param integer $ring_id The id of the ring to check if a user is banned.
     * @param integer $user_id The id of the user to check if banned.
     *
     * @return boolean
     */
    public static function checkBanned($ring_id, $user_id) {
        $sql = "
            SELECT user_ring.user_ring_id
            FROM user_ring
            WHERE
                user_id = :user_id
                AND ring_id = :ring_id
                AND ban = 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_ring_id = $command->queryScalar();
        if ($user_ring_id !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all the admins for a ring.
     *
     * @param integer $ring_id The id of th ering to fetch admins for.
     *
     * @return array
     */
    public static function getAdmins($ring_id) {
        $sql = "
            SELECT
                 user.username
                ,site.domain
            FROM
                user_ring
                INNER JOIN user ON user_ring.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
            WHERE
                user_ring.ring_id = :ring_id
                AND user_ring.admin = 1
            ORDER BY user_ring.user_ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $admins = $command->queryAll();
        return $admins;
    }

    /**
     * Fetch the detials for all the rings a user administers for the domus domain (includes passwords).
     *
     * @param integer $user_id The id of the users whos ring rhythm urls we are fetching.
     * @param string $type Should 'member' or 'admin' rings be fetched.
     *
     * @return array An array of urls.
     */
    public static function getRingDetailsForDomus($user_id, $type) {
        if ($type === 'member') {
            $type_sql = 'AND user_ring.member = 1';
        } else if ($type === 'admin') {
            $type_sql = 'AND user_ring.admin = 1';
        } else {
            throw new Exception('Invalid typoe when calling UserRing::getRingDetailsForDomus : ' . $type);
        }

        $sql = "
            SELECT
                 ring_user.username
                ,ring_site.domain
                ,user_ring.password
                ,rhythm_user.username AS rhythm_username
                ,rhythm_site.domain AS rhythm_domain
                ,rhythm.name AS rhythm_name
                ,version.major AS rhythm_major
                ,version.minor AS rhythm_minor
                ,version.patch AS rhythm_patch
                ,ring.ring_rhythm_version_type AS rhythm_version_type
            FROM
                user_ring
                INNER JOIN ring ON user_ring.ring_id = ring.ring_id
                INNER JOIN user AS ring_user ON ring.user_id = ring_user.user_id
                INNER JOIN site AS ring_site ON ring_user.site_id = ring_site.site_id
                LEFT JOIN rhythm_extra ON ring.membership_rhythm_id = rhythm_extra.rhythm_extra_id
                LEFT JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id
                LEFT JOIN user AS rhythm_user ON rhythm.user_id = rhythm_user.user_id
                LEFT JOIN site AS rhythm_site ON rhythm_user.site_id = rhythm_site.site_id
                LEFT JOIN version ON rhythm_extra.version_id = version.version_id
            WHERE
                user_ring.user_id = :user_id
                " . $type_sql . "
            ORDER BY user_ring_id DESC";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_STR);
        $rows = $command->queryAll();
        $ring_objects = array();
        foreach ($rows as $row) {
            $ring_object = array();
            $url = '';
            if (isset($row['rhythm_name']) === true) {
                $version = Version::makeVersionUrlFromVersionTypeId(
                    $row['rhythm_version_type'],
                    $row['rhythm_major'],
                    $row['rhythm_minor'],
                    $row['rhythm_patch']
                );
                $url = $row['rhythm_domain'] . "/"
                    . $row['rhythm_username'] . "/rhythm/json/"
                    . $row['rhythm_name'] . "/"
                    . $version;
            }

            $ring_object['url'] = $url;
            $ring_object['username'] = $row['username'];
            $ring_object['domain'] = $row['domain'];
            $ring_object['password'] = $row['password'];

            array_push($ring_objects, $ring_object);
        }
        return $ring_objects;
    }

    /**
     * Check if a rings password is valid.
     *
     * @param type $ring_id
     * @param type $user_id
     * @param type $ring_password
     */
    public static function checkPassword($ring_id, $user_id, $ring_password) {
        $sql = "
            SELECT user_ring_id
            FROM user_ring
            WHERE
                user_ring.ring_id = :ring_id
                AND user_ring.user_id = :user_id
                AND user_ring.password = :password";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":password", $ring_password, PDO::PARAM_STR);
        $user_ring_id = $command->queryScalar();
        if ($user_ring_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if a user is an admin of a ring.
     *
     * @param integer $ring_id
     * @param integer $user_id
     *
     * @return boolean
     */
    public static function isAdmin($ring_id, $user_id) {
        $sql = "
            SELECT user_ring_id
            FROM user_ring
            WHERE
                user_ring.ring_id = :ring_id
                AND user_ring.user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_ring_id = $command->queryScalar();
        if ($user_ring_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Deletes user_ring rows by their user_id.
     *
     * @param integer $user_id The id of the user whose user ring data is being deleted.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_ring
            WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes user_ring rows by their ring_id
     *
     * @param integer $ring_id The id of the ring whose user_ring data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM user_ring
            WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of user_ring data for a ring.
     *
     * @param integer $ring_id The id of the ring to select data for.
     *
     * @return array
     */
    public static function getRowsForRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM user_ring
                WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>