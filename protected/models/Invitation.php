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
 * Model for the invitation DB table.
 * The table holds information about invitations to join rings sent from one user to another.
 *
 * @package PHP_Models
 */
class Invitation extends CActiveRecord
{

    /**
     * The primary key of the invitation table.
     *
     * @var integer
     */
    public $invitation_id;

    /**
     * The primary key of the user who the invitaiton is being sent from.
     *
     * @var integer
     */
    public $from_user_id;

    /**
     * The primary key of the user who the invitaiton is being sent to.
     *
     * @var integer
     */
    public $to_user_id;

    /**
     * The primary key of the ring that the invitation is for.
     *
     * @var integer
     */
    public $ring_id;

    /**
     * The type of invitation. See invitation.type in the lookup table for options.
     *
     * @var integer
     */
    public $type;

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
        return 'invitation';
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
            array('from_user_id, to_user_id, ring_id, type', 'required'),
            array('from_user_id, to_user_id, ring_id, type', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'invitation_id' => 'Invitation',
            'from_user_id' => 'Form User',
            'to_user_id' => 'To User',
            'ring_id' => 'Ring',
            'type' => 'Type',
        );
    }

    /**
     * Send an invitation to another member.
     *
     * @param integer $from_user_id The user the invitation is being sent from.
     * @param integer $to_user_id The user the invitation is being sent to.
     * @param string $type The type of invitation. See invitation.type in the lookup table for valid options.
     * @param integer $ring_id The id of the ring that the invitation is for.
     *
     * @return void
     */
    public static function sendInvite($from_user_id, $to_user_id, $type, $ring_id) {
        $stream = Yii::app()->params['invitation_stream'];
        $user_multi = new UserMulti(Yii::app()->params['site_id']);
        $stream_user_id = $user_multi->getIDFromUsername($stream['username']);
        $version_array = explode("/", $stream['version']);
        $stream_extra_id = StreamBedMulti::getIDByName(
            $stream_user_id,
            $stream['name'],
            $version_array[0],
            $version_array[1],
            $version_array[2],
            $stream['domain']
        );

        $to_user = User::getFullUsernameParts($to_user_id);

        $ring_user_id = Ring::getRingUserId($ring_id);
        $ring_user = User::getFullUsername($ring_user_id);

        $ring_member = User::getFullUsername($from_user_id);

        $message = Yii::app()->params['invitation_message'];
        $message = str_replace('*ring_name*', $ring_user, $message);
        $message = str_replace('*ring_member*', $ring_member, $message);
        $message = str_replace(
            '*users_ring_index*',
            'http://' . $to_user['domain'] . '/' . $to_user['username'] . '/ring/index',
            $message
        );

        $content = array(
            0 => array(
                'display_order' => '1',
                'text' => $message,
            ),
            1 => array(
                'display_order' => '2',
            ),
        );

        if (User::isLocal($to_user_id) === true) {
            $transaction = Yii::app()->db->beginTransaction();
            try {
                Invitation::insertInvitation($from_user_id, $to_user_id, $type, $ring_id);
                $result = PostMulti::insertPost(
                    $stream_extra_id,
                    $content,
                    $from_user_id,
                    null,
                    null,
                    null,
                    null,
                    null,
                    'private'
                );
                if (is_array($result) === true) {
                    throw new Exception(
                        'There was an error when creating an invitation post. ' . implode(",", $result)
                    );
                }
                $link_row = new PostPrivateRecipient;
                $link_row->post_id = $result->post_id;
                $link_row->user_id = $to_user_id;
                $link_row->save();
                if ($link_row->hasErrors() === true) {
                    throw new Exception(
                        'There was an error when creating an invitation posts recipient link. '
                        . ErrorHelper::model($login_model->getErrors())
                    );
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new Exception('There was an exception when sending a ring invitation. ' . $e);
            }
        } else {
            $temp = true;
        }
    }

    /**
     * Send an invitation to another member.
     *
     * @param integer $from_user_id The user the invitation is being sent from.
     * @param integer $to_user_id The user the invitation is being sent to.
     * @param string $type The type of invitation. See invitation.type in the lookup table for valid options.
     * @param integer $ring_id The id of the ring that the invitation is for.
     *
     * @return void
     */
    public static function insertInvitation($from_user_id, $to_user_id, $type, $ring_id) {
        // Check it does not already exist
        $invite_id = Invitation::getInviteByRingAndUser($to_user_id, $ring_id, $type);
        if ($invite_id !== false) {
            return;
        }

        // @fixme check they are not already a member/admin of this ring

        $type_id = LookupHelper::getID("invitation.type", $type);

        $query = "INSERT INTO invitation
                      (
                            from_user_id
                           ,to_user_id
                           ,ring_id
                           ,type
                       ) VALUES (
                            :from_user_id
                           ,:to_user_id
                           ,:ring_id
                           ,:type_id
                       )";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":from_user_id", $from_user_id, PDO::PARAM_INT);
        $command->bindValue(":to_user_id", $to_user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":type_id", $type_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Get an invitation by the ring id, user id and type.
     *
     * @param integer $to_user_id The user the invitation is being sent to.
     * @param integer $ring_id The id of the ring that the invitation is for.
     * @param string $type The type of invitation. See invitation.type in the lookup table for valid options.
     *
     * @return integer|boolean Primary key or false.
     */
    public static function getInviteByRingAndUser($to_user_id, $ring_id, $type) {
        $type_id = LookupHelper::getID("invitation.type", $type);
        $query = "SELECT invitation_id FROM invitation
                  WHERE
                      to_user_id = :to_user_id
                      AND ring_id = :ring_id
                      AND type = :type_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":to_user_id", $to_user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":type_id", $type_id, PDO::PARAM_INT);
        $invitation_id = $command->queryScalar();

        if ($invitation_id === 0) {
            return false;
        }
        return $invitation_id;
    }

    /**
     * Delete an invitation.
     *
     * @param integer $ring_id The id of the ring that the invitation is for.
     * @param integer $to_user_id The user the invitation was being sent to.
     * @param string $type The type of invitation. See invitation.type in the lookup table for valid options.
     *
     * @return void
     */
    public static function deleteInvite($ring_id, $to_user_id, $type) {
        $type_id = LookupHelper::getID("invitation.type", $type);
        $query = "DELETE FROM invitation
                  WHERE
                      to_user_id = :to_user_id
                      AND ring_id = :ring_id
                      AND type = :type_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":to_user_id", $to_user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":type_id", $type_id, PDO::PARAM_INT);
        $invitation_id = $command->execute();
    }

    /**
     * Fetch all ring invitations for a user.
     *
     * @param integer $user_id The id of the user that invitations are being fetched for.
     *
     * @return array Rows or ring invitations.
     */
    public static function getAllForUser($user_id) {
        $query = "
            SELECT
                 from_user.username AS from_username
                ,from_site.domain AS from_domain
                ,ring_user.username AS ring_username
                ,ring_site.domain AS ring_domain
                ,lookup.value AS type
            FROM
                invitation
                INNER JOIN user AS from_user ON invitation.from_user_id = from_user.user_id
                INNER JOIN site AS from_site ON from_user.site_id = from_site.site_id
                INNER JOIN ring ON invitation.ring_id = ring.ring_id
                INNER JOIN user AS ring_user ON ring.user_id = ring_user.user_id
                INNER JOIN site AS ring_site ON ring_user.site_id = ring_site.site_id
                INNER JOIN lookup ON invitation.type = lookup.lookup_id
            WHERE
                to_user_id = :to_user_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":to_user_id", $user_id, PDO::PARAM_INT);
        $invitations = $command->queryAll();
        return $invitations;
    }

    /**
     * Checks if a user has already been invited to join a ring.
     *
     * @param integer $ring_id The id of the ring to check.
     * @param integer $to_user_id The id of the user the invite is being sent to.
     * @param string $type The type of invitation ('member' or 'admin').
     *
     * @return boolean
     */
    public static function checkIfAlreadyInvited($ring_id, $to_user_id, $type) {
        $query = "
            SELECT
                 invitation_id
            FROM
                invitation
            WHERE
                to_user_id = :to_user_id
                AND ring_id = :ring_id
                AND type = :type_id";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":to_user_id", $to_user_id, PDO::PARAM_INT);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->bindValue(":type_id", LookupHelper::getID('invitation.type', $type), PDO::PARAM_INT);
        $invitation_id = $command->queryScalar();
        if ($invitation_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Deletes invitation rows by their ring_id.
     *
     * @param integer $ring_id The id of the ring whose invitation data is being deleted.
     *
     * @return void
     */
    public static function deleteByRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM invitation
            WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes invitation rows by their from_user_id.
     *
     * @param integer $user_id The id of the user whose invitations data for from_user_id is being deleted.
     *
     * @return void
     */
    public static function deleteByFromUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM invitation
            WHERE from_user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Deletes invitation rows by their to_user_id.
     *
     * @param integer $user_id The id of the user whose invitations data for to_user_id is being deleted.
     *
     * @return void
     */
    public static function deleteByToUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM invitation
            WHERE to_user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select rows of invitation data for a ring.
     *
     * @param integer $ring_id The id of the ring to select data for.
     *
     * @return array
     */
    public static function getRowsForRingId($ring_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM invitation
                WHERE ring_id = :ring_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":ring_id", $ring_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }
}

?>