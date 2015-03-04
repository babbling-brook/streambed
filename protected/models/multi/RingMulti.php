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
 * A collection of static functions that affect multiple db tables to do with rings.
 *
 * @package PHP_Model_Forms
 */
class RingMulti
{
    /**
     * Bans a user from a ring regardless of if they are already a member or not.
     *
     * @param type $ring_id The id of the ring that the user is being banned from.
     * @param type $user_id The id of the user that is being banned.
     *
     * @returns void
     */
    public static function banAUser($ring_id, $user_id) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $ring_member_id = UserRing::getIDByRingAndUser($ring_id, $user_id);
            if ($ring_member_id === false) {
                UserRing::createUserAccess($ring_id, $user_id, true, false);
            }

            UserRing::banUser($ring_id, $user_id, 1);

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('There was an exception when banning a member of a ring. ' . $e);
        }
    }

    /**
     * Reinstates a user to a ring they have been banned from.
     *
     * @param type $ring_id The id of the ring that the user is being rinstated to.
     * @param type $user_id The id of the user that is being reinstated.
     *
     * @returns void
     */
    public static function reinstateAUser($ring_id, $user_id) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $ring_member_id = UserRing::getIDByRingAndUser($ring_id, $user_id);
            if ($ring_member_id === false) {
                UserRing::createUserAccess($ring_id, $user_id, true, false);
            }

            UserRing::banUser($ring_id, $user_id, 0);

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('There was an exception when reinstating a member of a ring. ' . $e);
        }
    }

    /**
     * Accept a ring application for a new member.
     *
     * @param integer $ring_id The id of the ring that a membership application is being accepted for.
     * @param integer $user_id The id of the user that a membership application is being accepted for.
     * @param array $ring_user A standard user object for the ring that is accepting membership.
     * @param integer $accepting_admin_user_id The id of the admin user who is accepting this membership.
     *
     * @return void
     */
    public static function acceptMembershipApplication($ring_id, $user_id, $ring_user, $accepting_admin_user_id) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $rows_affected = RingApplication::deleteApplication($ring_id, $user_id);
            if ($rows_affected < 1) {
                throw new Exception('Application not found.');
            }

            UserRing::createUserAccess($ring_id, $user_id, true, false);

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

            $message = Yii::app()->params['accept_ring_membership_messsage'];
            $message = str_replace('*ring-name*', $ring_user['username'] . '@' . $ring_user['domain'], $message);
            $content = array(
                0 => array(
                    'display_order' => '1',
                    'text' => $message,
                ),
                1 => array(
                    'display_order' => '2',
                ),
            );

            $result = PostMulti::insertPost(
                $stream_extra_id,
                $content,
                $accepting_admin_user_id,
                null,
                null,
                null,
                null,
                null,
                'private'
            );
            $link_row = new PostPrivateRecipient;
            $link_row->post_id = $result->post_id;
            $link_row->user_id = $user_id;
            $link_row->save();
            if ($link_row->hasErrors() === true) {
                throw new Exception(
                    'There was an error when creating a post link for a ring acceptance message. '
                    . ErrorHelper::model($link_row->getErrors())
                );
            }


            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('There was an exception when commiting a ring membership application. ' . $e);
        }
    }

    /**
     * Decline a ring application for a new member.
     *
     * @param integer $ring_id The id of the ring that a membership application is being declined for.
     * @param integer $user_id The id of the user that a membership application is being declined for.
     * @param array $ring_user A standard user object for the ring that is declining membership.
     * @param integer $accepting_admin_user_id The id of the admin user who is declining this membership.
     *
     * @return void
     */
    public static function declineMembershipApplication($ring_id, $user_id, $ring_user, $accepting_admin_user_id) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $rows_affected = RingApplication::deleteApplication($ring_id, $user_id);
            if ($rows_affected < 1) {
                throw new Exception('Application not found.');
            }

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

            $message = Yii::app()->params['decline_ring_membership_messsage'];
            $message = str_replace('*ring-name*', $ring_user['username'] . '@' . $ring_user['domain'], $message);
            $content = array(
                0 => array(
                    'display_order' => '1',
                    'text' => $message,
                ),
                1 => array(
                    'display_order' => '2',
                ),
            );

            $result = PostMulti::insertPost(
                $stream_extra_id,
                $content,
                $accepting_admin_user_id,
                null,
                null,
                null,
                null,
                null,
                'private'
            );
            $link_row = new PostPrivateRecipient;
            $link_row->post_id = $result->post_id;
            $link_row->user_id = $user_id;
            $link_row->save();
            if ($link_row->hasErrors() === true) {
                throw new Exception(
                    'There was an error when creating a post link for a ring membership declined message. '
                    . ErrorHelper::model($link_row->getErrors())
                );
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('There was an exception when commiting a ring membership application. ' . $e);
        }
    }

}

?>
