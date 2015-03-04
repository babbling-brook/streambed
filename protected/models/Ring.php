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
 * Model for the ring DB table.
 * The table holds ring details.
 *
 * @package PHP_Models
 */
class Ring extends CActiveRecord
{
    /**
     * The primary key of this ring.
     *
     * @var integer
     */
    public $ring_id;

    /**
     * The id of the user that represents this ring.
     *
     * @var integer
     */
    public $user_id;

    /**
     * Defines how new members are admitted to this ring. See ring.membership_type in the lookup table.
     *
     * @var integer
     */
    public $membership_type;

    /**
     * The id of the ring rhythmrthim that all administrators run to grant access to new members.
     *
     * Connects to rhythm_extra.rhythm_extra_id.
     *
     * @var integer
     */
    public $ring_rhythm_id;

    /**
     * The version type of the Rhythm that all administrators run to grant access to new members.
     *
     * See lookup table - version_type for options.
     *
     * @var integer
     */
    public $membership_rhythm_version_type;

    /**
     * The ring that administrates membership of a ring. See ring.admin_type in the lookup table.
     *
     * Links to user.user_id Only used if membership_type is set to super_ring.
     *
     * @var integer
     */
    public $membership_super_ring_user_id;

    /**
     * The type of administration that is assigned to this ring. See ring.admin_type in the lookup table.
     *
     * @var integer
     */
    public $admin_type;

    /**
     * The id of the ring that administrates this one.
     *
     * Links to user.user_id, not ring.ring_id.
     *
     * @var integer
     */
    public $admin_super_ring_user_id;

    /**
     * The version type of the Rhythm that all members run. See lookup table - version_type for options.
     *
     * @var integer
     */
    public $ring_rhythm_version_type;

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
        return 'ring';
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
            array('user_id, membership_type, admin_type', 'required'),
            array(
                'user_id,
                 membership_type,
                 membership_rhythm_id,
                 membership_rhythm_version_type,
                 admin_super_ring_user_id,
                 admin_type,
                 membership_super_ring_user_id,
                 ring_rhythm_id,
                 ring_rhythm_version_type',
                'length',
                'max' => 11,
            ),
            array('membership_type', 'ruleMembershipValid'),
            array('admin_type', 'ruleAdminTypeValid'),
        );
    }


    /**
     * Checks if the membership type is valid.
     *
     * @return void
     */
    public function ruleMemberShipValid() {
        if (LookupHelper::validId("ring.membership_type", $this->membership_type, false) === false) {
            $this->addError('membership_type', 'Please select a membership type.');
        }
    }

    /**
     * Checks if the admin type is valid.
     *
     * @return void
     */
    public function ruleAdminTypeValid() {
        if (LookupHelper::validId("ring.admin_type", $this->admin_type, false) === false) {
            $this->addError('admin_type', 'Please select a administrator type.');
        }
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'ring_id' => 'Ring',
            'user_id' => 'User',
            'membership_type' => 'Membership Type',
            'membership_rhythm_id' => 'Membership Rhythm',
            'membership_super_ring_user_id' => 'Membership Super Ring',
            'admin_type' => 'Administrator Type',
            'admin_super_ring_user_id' => 'Administrator Super Ring',
            'ring_rhythm_id' => 'Ring Rhythm',
        );
    }

    /**
     * Create a new ring. Also creates a new user to represent the ring.
     *
     * @param RingForm $ring_form Contains details of a validated ring.
     *
     * @return integer The primary key.
     */
    public static function createRing($ring_form) {
        $password = UserIdentity::createRandomPassword();
        $user_ident = new UserIdentity($ring_form->name, $password);
        $user_ident->signUp(null, true);

        $membership_rhythm_id = null;
        if (LookupHelper::getValue($ring_form->membership) === "rhythm"
            && empty($ring_form->membership_rhythm) === false
        ) {
            // Work out the rhythm id
            $membership_rhythm_id = Rhythm::getIDFromUrl($ring_form->membership_rhythm);
            if (is_int($membership_rhythm_id) === false) {
                throw new Exception("Membership Rhythm not valid - need to validate before calling this funciton.");
            }
            $category = Rhythm::getRhythmCat($membership_rhythm_id);
            if ($category !== "ring") {
                throw new Exception("Membership Rhythm not valid - needs to be of category 'ring'.");
            }

            // Work out the version type.
            $version = Version::splitFromEndOfUrl($ring_form->membership_rhythm);
            if (Version::checkValidLatestVersion($version, "/") === false) {
                throw new Exception(
                    "Membership Rhythm version not valid - "
                        . "needs to be in the format major/minor/patch with each value being an integer or 'latest'"
                );
            }
            $membership_rhythm_version_type = Version::getTypeId($version);
        } else {
            $membership_rhythm_version_type = null;
        }

        $ring_rhythm_id = null;
        if (empty($ring_form->ring_rhythm) === false) {
            // Work out the rhythm id
            $ring_rhythm_id = Rhythm::getIDFromUrl($ring_form->ring_rhythm);
            if (is_int($ring_rhythm_id) === false) {
                throw new Exception("Ring Rhythm not valid - need to validate before calling this funciton.");
            }
            $category = Rhythm::getRhythmCat($ring_rhythm_id);
            if ($category !== "ring") {
                throw new Exception("Ring Rhythm not valid - needs to be of category 'ring'.");
            }

            // Work out the version type.
            $version = Version::splitFromEndOfUrl($ring_form->ring_rhythm);
            if (Version::checkValidLatestVersion($version, "/") === false) {
                throw new Exception(
                    "Ring Rhythm version not valid - "
                        . "needs to be in the format major/minor/patch with each value being an integer or 'latest'"
                );
            }
            $ring_rhythm_version_type = Version::getTypeId($version);
        } else {
            $ring_rhythm_version_type = null;
        }

        $membership_super_ring_user_id = null;
        if (LookupHelper::getValue($ring_form->membership) === "super_ring") {
            $membership_super_ub = new UserMulti();
            $membership_super_ring_user_id = $membership_super_ub->getIDFromUsername($ring_form->membership_super_ring);
        }

        $admin_super_ring_user_id = null;
        if (empty($ring_form->admin_super_ring) === false) {
            $super_ub = new UserMulti();
            $admin_super_ring_user_id = $super_ub->getIDFromUsername($ring_form->admin_super_ring);
        }

        $ring = new Ring();
        $ring->user_id = $user_ident->getId();
        $ring->membership_type = $ring_form->membership;
        $ring->membership_rhythm_id = $membership_rhythm_id;
        $ring->membership_rhythm_version_type = $membership_rhythm_version_type;
        $ring->membership_super_ring_user_id = $membership_super_ring_user_id;
        $ring->admin_type = $ring_form->admin_type;
        $ring->admin_super_ring_user_id = $admin_super_ring_user_id;
        $ring->ring_rhythm_id = $ring_rhythm_id;
        $ring->ring_rhythm_version_type = $ring_rhythm_version_type;
        if ($ring->save() === false) {
            throw new Exception("Ring not saved.");
        }

        return $ring->ring_id;
    }

    /**
     * Update a ring.
     *
     * @param RingForm $ring_form Contains details of a validated ring to update.
     * @param integer $ring_id The id of the ring to update.
     *
     * @return void
     */
    public static function updateRing($ring_form, $ring_id) {
        // This part is duplicated from the create function needs abstracting.
        $membership_rhythm_id = null;
        $membership_rhythm_version_type = null;
        $ring_rhythm_version_type = null;
        if (LookupHelper::getValue($ring_form->membership) === "rhythm"
            && empty($ring_form->membership_rhythm) === false
        ) {
            // Work out the rhythm id
            $membership_rhythm_id = Rhythm::getIDFromUrl($ring_form->membership_rhythm);
            if (is_int($membership_rhythm_id) === false) {
                throw new Exception("Membership Rhythm not valid - need to validate before calling this funciton.");
            }
            $category = Rhythm::getRhythmCat($membership_rhythm_id);
            if ($category !== "ring") {
                throw new Exception("Membership Rhythm not valid - needs to be of category 'ring'.");
            }

            // Work out the version type.
            $version = Version::splitFromEndOfUrl($ring_form->membership_rhythm);
            if (Version::checkValidLatestVersion($version, "/") === false) {
                throw new Exception(
                    "Membership Rhythm version not valid - "
                        . "needs to be in the format major/minor/patch with each value being an integer or 'latest'"
                );
            }
            $membership_rhythm_version_type = Version::getTypeId($version);

        }

        $ring_rhythm_id = null;
        if (empty($ring_form->ring_rhythm) === false) {
            // Work out the rhythm id
            $ring_rhythm_id = Rhythm::getIDFromUrl($ring_form->ring_rhythm);
            if (is_int($ring_rhythm_id) === false) {
                throw new Exception("Ring Rhythm not valid - need to validate before calling this funciton.");
            }
            $category = Rhythm::getRhythmCat($ring_rhythm_id);
            if ($category !== "ring") {
                throw new Exception("Ring Rhythm not valid - needs to be of category 'ring'.");
            }

            // Work out the version type.
            $version = Version::splitFromEndOfUrl($ring_form->ring_rhythm);
            if (Version::checkValidLatestVersion($version, "/") === false) {
                throw new Exception(
                    "Ring Rhythm version not valid - "
                        . "needs to be in the format major/minor/patch with each value being an integer or 'latest'"
                );
            }
            $ring_rhythm_version_type = Version::getTypeId($version);
        }

        $membership_super_ring_user_id = null;
        if (LookupHelper::getValue($ring_form->membership) === "super_ring") {
            $membership_super_ub = new UserMulti();
            $membership_super_ring_user_id = $membership_super_ub->getIDFromUsername($ring_form->membership_super_ring);
        }

        $admin_super_ring_user_id = null;
        if (empty($ring_form->admin_super_ring) === false) {
            $super_ub = new UserMulti();
            $admin_super_ring_user_id = $super_ub->getIDFromUsername($ring_form->admin_super_ring);
        }

        $sql = "
            UPDATE ring
            SET
                 membership_type = :membership_type
                ,membership_rhythm_id = :membership_rhythm_id
                ,membership_rhythm_version_type = :membership_rhythm_version_type
                ,membership_super_ring_user_id = :membership_super_ring_user_id
                ,admin_type = :admin_type
                ,admin_super_ring_user_id = :admin_super_ring_user_id
                ,ring_rhythm_id = :ring_rhythm_id
                ,ring_rhythm_version_type = :ring_rhythm_version_type
            WHERE ring_id = :ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":membership_type", $ring_form->membership, PDO::PARAM_INT);
        $command->bindValue(":membership_rhythm_id", $membership_rhythm_id, PDO::PARAM_INT);
        $command->bindValue(":membership_rhythm_version_type", $membership_rhythm_version_type, PDO::PARAM_INT);
        $command->bindValue(":membership_super_ring_user_id", $membership_super_ring_user_id, PDO::PARAM_INT);
        $command->bindValue(":admin_type", $ring_form->admin_type, PDO::PARAM_INT);
        $command->bindValue(":admin_super_ring_user_id", $admin_super_ring_user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_rhythm_id", $ring_rhythm_id, PDO::PARAM_INT);
        $command->bindValue(":ring_rhythm_version_type", $ring_rhythm_version_type, PDO::PARAM_INT);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $ring_id = $command->execute();
    }

    /**
     * Get the id of a ring from its users username and site_id.
     *
     * @param string $username The username of the user that owns this ring.
     * @param integer $site_id The id of the domain that this rings username belongs to.
     *
     * @return integer The ring_id. False if not found.
     */
    public static function getId($username, $site_id=null) {
        if (isset($site_id) === false) {
            $site_id = Yii::app()->params['site_id'];
        }

        $sql = "
            SELECT ring.ring_id
            FROM
                ring
                INNER JOIN user ON ring.user_id = user.user_id
            WHERE
                user.username = :username
                AND user.site_id = :site_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":username", $username, PDO::PARAM_STR);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $ring_id = $command->queryScalar();
        return $ring_id;
    }

    /**
     * Fetch the admin type for a ring.
     *
     * @param integer $ring_id The id of the ring that we are fetching an admin type for.
     *
     * @return string
     */
    public static function getAdminType($ring_id) {
        $sql = "SELECT admin_type FROM ring WHERE ring_id = :ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $admin_type = $command->queryScalar();
        if (isset($admin_type) === false) {
            throw new Exception("Ring not found");
        }
        return LookupHelper::getValue($admin_type);
    }

    /**
     * Fetch the member type for a ring.
     *
     * @param integer $ring_id The id of the ring that we are fetching a member type for.
     *
     * @return string
     */
    public static function getMemberType($ring_id) {
        $sql =    "SELECT membership_type FROM ring WHERE ring_id = :ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $member_type = $command->queryScalar();
        if ($member_type === 0) {
            throw new Exception("Ring not found");
        }
        return LookupHelper::getValue($member_type);
    }


    /**
     * Fetch the member type for a ring.
     *
     * @param integer $user_id The rings user id.
     *
     * @return string|boolean
     */
    public static function getMemberTypeByUserID($user_id) {
        $sql =    "SELECT membership_type FROM ring WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $member_type = $command->queryScalar();
        if ($member_type === false) {
            return false;
        }
        return LookupHelper::getValue($member_type);
    }

    /**
     * Fetch the admin super ring id for a ring.
     *
     * @param integer $ring_id The id of the ring that we are fetching an admin super ring for.
     *
     * @return integer
     */
    public static function getAdminSuperRing($ring_id) {
        $sql = "
            SELECT
                 ring2.ring_id
            FROM ring AS ring1
                INNER JOIN ring AS ring2 ON ring1.admin_super_ring_user_id = ring2.user_id
            WHERE ring1.ring_id = :ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $super_ring_id = $command->queryScalar();
        if (isset($super_ring_id) === false) {
            throw new Exception("Ring not found");
        }
        return $super_ring_id;
    }

    /**
     * Fetches the user_id that represents this ring.
     *
     * @param integer $ring_id The id of the ring that we are fetching a user id for.
     *
     * @return integer
     */
    public static function getRingUserId($ring_id) {
        $sql =    "SELECT user_id FROM ring WHERE ring_id = :ring_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $user_id = $command->queryScalar();
        if (isset($user_id) === false) {
            throw new Exception("Ring not found");
        }
        return $user_id;
    }

    /**
     * Fetch the passwords for the passed in ring names for a user.
     *
     * @param array $rings The rings to fetch passwords for.
     *                     An array with two elements 'name' and 'domain'.
     * @param string $user_id The user_id of a member of a ring we are fetchpasswords for.
     *
     * @return array The $rings array with an extra password field for each ring.
     */
    public static function getPasswordsFromNames($rings, $user_id) {
        $passwords = array();
        $ring_count = count($rings);
        for ($i = 0; $i < $ring_count; $i++) {
            $ring = $rings[$i];
            $sql = "
                SELECT
                     user_ring_password.password
                FROM
                    user_ring_password
                    INNER JOIN user ON user_ring_password.ring_user_id = user.user_id
                    INNER JOIN site ON user.site_id = site.site_id
                WHERE
                    site.domain = :domain
                    AND user.username = :username
                    AND user_ring_password.user_id = :user_id";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":username", $ring['name'], PDO::PARAM_STR);
            $command->bindValue(":domain", $ring['domain'], PDO::PARAM_STR);
            $command->bindValue(":user_id", $user_id, PDO::PARAM_STR);
            $password = $command->queryScalar();
            if ($password === false) {
                $rings[$i]["password"] = false;
            } else {
                $rings[$i]["password"] = $password;
            }
        }

        return $rings;
    }

    /**
     * Take an post in the name of a ring.
     *
     * @param RingForm $ring_take_form Contains details of a validated ring to take for.
     *
     * @return boolean|string true or error message.
     */
    public static function take($ring_take_form) {
        // Check if this user has already taken this post
        $taken = RingUserTake::isTaken(
            $ring_take_form->ring_take_name_id,
            $ring_take_form->user_id,
            $ring_take_form->post_id
        );
        if ($taken === true && $ring_take_form->untake === true) {
            // Remove the take and mark down the ring take value
            RingUserTake::untake(
                $ring_take_form->ring_take_name_id,
                $ring_take_form->user_id,
                $ring_take_form->post_id
            );
            TakeMulti::take(
                $ring_take_form->post_id, -
                $ring_take_form->amount,
                $ring_take_form->ring_user_id,
                2,
                'add'
            );

        } else if ($taken === false && $ring_take_form->untake === false) {
            // Take by this user and increase the ring take value
            RingUserTake::take($ring_take_form->ring_take_name_id, $ring_take_form->user_id, $ring_take_form->post_id);
            TakeMulti::take($ring_take_form->post_id, $ring_take_form->amount, $ring_take_form->ring_user_id, 2, 'add');

        } else if ($taken === true && $ring_take_form->untake === false) {
            return "Post already taken by this user for this ring.";

        } else if ($taken === false && $ring_take_form->untake === true) {
            return "Trying to untake a take that has not been taken.";
        }
        return true;
    }

    /**
     * Get a ring id from its user id.
     *
     * @param integer $ring_user_id The id of the user that owns this ring.
     *
     * @return integer ring_id
     */
    public static function getRingIdFromUserId($ring_user_id) {
        $sql = "SELECT ring_id FROM ring WHERE user_id = :ring_user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $ring_id = $command->queryScalar();
        if (isset($ring_id) === false) {
            throw new Exception("Ring not found");
        }
        return $ring_id;
    }

    /**
     * Get a ring from its user id.
     *
     * @param integer $ring_user_id The id of the user that owns this ring.
     *
     * @return array|boolean The ring or false.
     */
    public static function getFromUserID($ring_user_id) {
        $sql = "
            SELECT
                 ring_id
                ,membership_type
                ,membership_rhythm_id
                ,membership_rhythm_version_type
                ,membership_super_ring_user_id
                ,admin_type
                ,admin_super_ring_user_id
                ,ring_rhythm_id
                ,ring_rhythm_version_type
            FROM ring
            WHERE user_id = :ring_user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $ring = $command->queryRow();
        return $ring;
    }

    /**
     * Fetch the ring id from the rings user_id
     *
     * @param integer $ring_user_id The rings user id.
     * @param boolean [$throw_error=true] Should an error be thrown if the ring is not found
     *
     * @return integer
     */
    public static function getRingIdFromRingUserId($ring_user_id, $throw_error=true) {
        $sql = "SELECT ring_id FROM ring WHERE user_id = :ring_user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":ring_user_id", $ring_user_id, PDO::PARAM_INT);
        $ring_id = $command->queryScalar();
        if (isset($ring_id) === false) {
            if ($throw_error === true) {
                throw new Exception("Ring not found");
            } else {
                return false;
            }
        }
        return (int)$ring_id;
    }

    /**
     * Sets the admin_super_ring_user_id ring id to NULL for a ring.
     *
     * @param integer $user_id The id of the user whose admin_super_ring_user data is being set to NULL.
     *
     * @return void
     */
    public static function setAdminSuperRingUserIdToNull($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            UPDATE ring
            SET admin_super_ring_user_id = NULL
            WHERE admin_super_ring_user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Sets a rings ring_rhythm_id to null for a rhythm_extra id.
     *
     * @param integer $rhythm_extra_id The extra id of the rings ring_rhythm_id to set to NULL.
     *
     * @return void
     */
    public static function removeRingRhythm($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            UPDATE ring
            SET ring_rhythm_id = NULL
            WHERE ring_rhythm_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Sets a rings membership_rhythm_id to null for a rhythm_extra.
     *
     * @param integer $rhythm_extra_id The extra id of the rings membership_rhythm_id to set to NULL.
     *
     * @return void
     */
    public static function removeMembershipRhythm($rhythm_extra_id) {
        $connection = Yii::app()->db;
        $sql = "
            UPDATE ring
            SET membership_rhythm_id = NULL
            WHERE membership_rhythm_id = :rhythm_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":rhythm_extra_id", $rhythm_extra_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Delete rings by their user_id.
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $user_id The id of the user used to delete the rings.
     *
     * @return void
     */
    public static function deleteByUserId($user_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM ring
                WHERE user_id = :user_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            $command->execute();

        } catch (Exception $e) {
            throw new Exception(
                'Ring::deleteByUserId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Select rows of ring data for a user_id. (the user_id of the ring).
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM ring
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>