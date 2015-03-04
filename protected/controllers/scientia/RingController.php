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
 * Ring controller.
 *
 * @package PHP_Controllers
 */
class RingController extends Controller
{

    /**
     * The username of the ring.
     *
     * @var string
     */
    public $username;

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'user',
        );
    }

    /**
     * Which user are we looking at.
     *
     * Only used for users from the url.
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return void
     */
    public function filterUser($filterChain) {
        if (isset($_GET['user']) === false) {
            throw new CHttpException(400, 'Bad Request. User is not defined');
        }

        // Check that the user exists
        $user_multi = new UserMulti;
        if ($user_multi->userExists($_GET['user']) === false) {
            throw new CHttpException(400, 'Bad Request. User does not exist');
        }

        $this->username = $_GET['user'];
        $filterChain->run();
    }

    /**
     * Specifies the access control rules. This method is used by the 'accessControl' filter.
     *
     * @return array access control rules.
     */
    public function accessRules() {
        return array(
            array(
                'allow',    // All users.
                'actions' => array(
                    'Take',
                    'TakeStatus',
                    'StoreRhythmData',
                    'Join',
                    'AcceptRingMembership',
                    'GetRingUsersWaitingToBeVetted',
                    'RequestRingMembership',
                    'DeclineRingMembership',
                    'BanMember',
                    'ReinstateMember',
                ),
                'users' => array('*'),
            ),
            array(
                'allow', // Authenticated users
                'actions' => array(),
                'users' => array('@'),
            ),
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array(),
                'users' => array(),
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }


    /**
     * A JSON request by a user through the scientia iframe. Attempts to take an post using a ring.
     *
     * Request variables are:
     * $_POST['post_domain']
     * $_POST['post_id']
     * $_POST['ring_password']
     * $_POST['take_name']
     * $_POST['user_domain']
     * $_POST['username']
     * $_POST['untake']
     *
     * @return void
     */
    public function actionTake() {
        $take_form = new RingTakeForm;
        $take_form->ring_name = Yii::app()->request->getQuery('user');
        $take_form->post_domain = Yii::app()->request->getPost('post_domain');
        $take_form->site_post_id = Yii::app()->request->getPost('post_id');
        $take_form->ring_password = Yii::app()->request->getPost('ring_password');
        $take_form->take_name = Yii::app()->request->getPost('take_name');
        $take_form->user_domain = Yii::app()->request->getPost('user_domain');
        $take_form->username = Yii::app()->request->getPost('username');
        $untake = Yii::app()->request->getPost('untake');
        if ($untake === 'false') {
            $untake = false;
        } else {
            $untake = (bool)$untake;
        }
        $take_form->untake = $untake;
        $errors = false;
        $success = false;
        if ($take_form->validate() === false) {
            $errors = JSONHelper::convertYiiModelError($take_form->getErrors());
        } else {
            $success = Ring::take($take_form);
            if ($success !== true) {
                $errors = $success;
                $success = false;
            }
        }

        echo JSON::encode(
            array(
                'errors' => $errors,
                'success' => $success,
            )
        );
    }

    /**
     * Fetch the take status for the take_names on an post_id for a user.
     *
     * Request variables are:
     * $_POST['post_domain']
     * $_POST['post_id']
     * $_POST['user_domain']
     * $_POST['username']
     * $_POST['ring_password']
     *
     * @return void
     */
    public function actionTakeStatus($g_user, $p_post_domain, $p_post_id, $p_field_id,
        $p_user_domain, $p_username, $p_ring_password
    ) {
        $form = new RingTakeStatusForm;
        $form->ring_name = $g_user;
        $form->post_domain = $p_post_domain;
        $form->site_post_id = $p_post_id;
        $form->field_id = $p_field_id;
        $form->user_domain = $p_user_domain;
        $form->username = $p_username;
        $form->ring_password = $p_ring_password;

        $errors = false;
        $take_status = false;
        if ($form->validate() === false) {
            $errors = JSONHelper::convertYiiModelErrortoString($form->getErrors());
            echo JSON::encode(array('error' => $errors));
        } else {
            $take_status = RingUserTake::getStatus($form);
            echo JSON::encode(array('take_status' => $take_status));
        }
    }


    /**
     * Stores the results of a ring rhythm having been processed on a users ring domain.
     *
     * @param string $g_user The username of the ring.
     * @param string $p_ring_member_username The username of the user who processed the Rhythm.
     * @param string $p_ring_member_domain The domain of the user who processed the Rhythm.
     * @param string $p_ring_password The ring password of the user who has calculated these results.
     * @param string $p_rhythm_domain The domain of the Rhythm used to calculate results.
     * @param string $p_rhythm_username The username of the Rhythm used to caluculate results.
     * @param string $p_rhythm_name The name of the Rhythm that was used to calculate these results.
     * @param string $p_rhythm_version The version of the Rhythm that was used to calculate these results.
     * @param string $p_rhythm_type member or admin.
     * @param string $p_computed_data The results of the Rhythm.
     *
     * @return void
     */
    public function actionStoreRhythmData($g_user, $p_ring_member_username, $p_ring_member_domain, $p_ring_password,
        $p_rhythm_domain, $p_rhythm_username, $p_rhythm_name, $p_rhythm_version,
        $p_rhythm_type, $p_computed_data
    ) {
        $model = new RingStoreResults();
        $model->username = $p_ring_member_username;
        $model->domain = $p_ring_member_domain;
        $model->ring_domain = Yii::app()->params['host'];
        $model->ring_username = $g_user;
        $model->ring_password = $p_ring_password;
        $model->rhythm_domain = $p_rhythm_domain;
        $model->rhythm_username = $p_rhythm_username;
        $model->rhythm_name = $p_rhythm_name;
        $model->rhythm_version = $p_rhythm_version;
        $model->rhythm_type = $p_rhythm_type;
        $model->computed_data = $p_computed_data;

        if ($model->validate() === true) {
            $temp = true;
            RingStoreResults::save();
            $json = array('success' => true);
        } else {
            $errors = array('error' => $model->getErrors());
            $json = array('error' => $errors);
        }
        echo JSON::encode($json);
    }

    /**
     * Allows a user to join a public ring.
     *
     *
     *
     * @return void
     */
    public function actionJoin($g_user, array $p_user) {
        $json = array();
        $json['success'] = false;

        $site_id = SiteMulti::getSiteID($p_user['domain'], true, true);
        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($p_user['username'], false, true);
        if ($user_id === false) {
            $json['error'] = 'User not found.';
            echo JSON::encode($json);
            return;
        }

        $ring_id = Ring::getId($g_user);
        if ($ring_id === 0) {
            $json['error'] = 'Ring name not found.';
            echo JSON::encode($json);
            return;
        }

        $ring_membership_type = Ring::getMemberType($ring_id);
        if ($ring_membership_type !== 'public') {
            $json['error'] = 'This Ring does not allow public users to join.';
            echo JSON::encode($json);
            return;
        }

        if (UserRing::checkBanned($ring_id, Yii::app()->user->getId()) === true) {
            $json['error'] = 'User does not have permission to join this ring.';
            echo JSON::encode($json);
            return;
        }

        if (isset($json['error']) === false && UserRing::checkIfMember($ring_id, Yii::app()->user->getId()) === true) {
            $json['error'] = 'USer is already a member of this ring.';
            echo JSON::encode($json);
            return;
        }


        UserRing::changePermission($ring_id, Yii::app()->user->getId(), 'member', true);
        $rings = UserRing::getConfig(Yii::app()->user->getId(), $ring_id);
        $json['ring_client_data'] = $rings[0];

        $ring_user_id = Ring::getRingUserId($ring_id);
        $json['ring_domus_password'] = UserRingPassword::getPassword($user_id, $ring_user_id);

        $json['success'] = true;
        echo JSON::encode($json);
    }

    /**
     * Accepts a membership request from a user.
     *
     * Submitted by a ring admin or a membership rhythm running on an admins account.
     *
     * @param {string} $g_user The username of the ring that a membership request has been accepted for.
     * @param {array} $p_user A user object for the user whose membership request has been accepted.
     * @param {string} $p_admin_user  A user object for the admin user whose rhythm accepted this membership request.
     * @param {string} $p_admin_passsword The admins password for this ring, to prove that they
     *      did indeed send this request.
     *
     * @return void
     */
    public function actionAcceptRingMembership($g_user, array $p_user, array $p_admin_user, $p_admin_passsword) {
        $json = array();
        $json['success'] = false;

        $ring_user = array(
            'username' => $g_user,
            'domain' => HOST,
        );
        $ring_admin_form = new RingAdminForm;
        $ring_admin_form->admin_user = $p_admin_user;
        $ring_admin_form->ring_user = $ring_user;
        $ring_admin_form->admin_password = $p_admin_passsword;
        if ($ring_admin_form->validate() === false) {
            $json['error'] = ErrorHelper::model($ring_admin_form->getErrors());
        } else {
            $ring_id = $ring_admin_form->getRingId();
        }

        $user_name_form = new UserNameForm($p_user);
        if ($user_name_form->validate() === false) {
            $json['error'] = ErrorHelper::model($user_name_form->getErrors());
        } else {
            $user_id = $user_name_form->getUserId();
        }

        if (isset($json['error']) === false) {
            // check for a valid appliction.
            $application_id = RingApplication::getApplication($ring_id, $user_id);
            if ($application_id === false) {
                $json['error'] = 'This user has not applied for membership of this ring.';
            } else {
                RingMulti::acceptMembershipApplication($ring_id, $user_id, $ring_user, Yii::app()->user->getId());
                $json['success'] = true;
            }
        }

        echo JSON::encode($json);
    }


    /**
     * Declines a membership request from a user.
     *
     * Submitted by a ring admin or a membership rhythm running on an admins account.
     *
     * @param {string} $g_user The username of the ring that a membership request has been accepted for.
     * @param {array} $p_user A user object for the user whose membership request has been accepted.
     * @param {string} $p_admin_user  A user object for the admin user whose rhythm accepted this membership request.
     * @param {string} $p_admin_passsword The admins password for this ring, to prove that they
     *      did indeed send this request.
     *
     * @return void
     */
    public function actionDeclineRingMembership($g_user, array $p_user, array $p_admin_user, $p_admin_passsword) {
        $json = array();
        $json['success'] = false;

        $ring_user = array(
            'username' => $g_user,
            'domain' => HOST,
        );
        $ring_admin_form = new RingAdminForm;
        $ring_admin_form->admin_user = $p_admin_user;
        $ring_admin_form->ring_user = $ring_user;
        $ring_admin_form->admin_password = $p_admin_passsword;
        if ($ring_admin_form->validate() === false) {
            $json['error'] = ErrorHelper::model($ring_admin_form->getErrors());
        } else {
            $ring_id = $ring_admin_form->getRingId();
        }

        $user_name_form = new UserNameForm($p_user);
        if ($user_name_form->validate() === false) {
            $json['error'] = ErrorHelper::model($user_name_form->getErrors());
        } else {
            $user_id = $user_name_form->getUserId();
        }

        if (isset($json['error']) === false) {
            // check for a valid appliction.
            $application_id = RingApplication::getApplication($ring_id, $user_id);
            if ($application_id === false) {
                $json['error'] = 'This user has not applied for membership of this ring.';
            } else {
                RingMulti::declineMembershipApplication($ring_id, $user_id, $ring_user, Yii::app()->user->getId());
                $json['success'] = true;
            }
        }

        echo JSON::encode($json);
    }

    /**
     * Get the number of ring users that are waiting to be vetted for an admin user.
     *
     * @param array $g_user The username of the ring that users waiting to be vetted are in.
     * @param array $p_admin_user A standard user object containing the ring admin users username and domain.
     * @param string $p_admin_password The admin users password for this domain.
     *
     * @return void
     */
    public function actionGetRingUsersWaitingToBeVetted($g_user, array $p_admin_user, $p_admin_password) {
        $json = array();
        $json['success'] = false;

        $ring_user = array(
            'username' => $g_user,
            'domain' => HOST,
        );
        $ring_admin_form = new RingAdminForm;
        $ring_admin_form->admin_user = $p_admin_user;
        $ring_admin_form->ring_user = $ring_user;
        $ring_admin_form->admin_password = $p_admin_password;
        if ($ring_admin_form->validate() === false) {
            $json['error'] = ErrorHelper::model($ring_admin_form->getErrors());
        } else {
            $ring_id = $ring_admin_form->getRingId();
            $json['qty'] = RingApplication::getApplicantCountForRing($ring_id);
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Get the number of ring users that are waiting to be vetted for an admin user.
     *
     * @param array $g_user The username of the ring that membership is being requested of.
     * @param array $p_membership_request_user A standard user object for the user requesting membership.
     *
     * @return void
     */
    public function actionRequestRingMembership($g_user, array $p_membership_request_user) {
        $json = array();
        $json['success'] = false;

        $ring_user = array(
            'username' => $g_user,
            'domain' => HOST,
            'is_ring' => true,
        );
        $ring_user_name_form = new UserNameForm($ring_user);
        $ring_user_valid = $ring_user_name_form->validate();
        if ($ring_user_valid === false) {
            $json['error'] = ErrorHelper::model($ring_user_valid->getErrors());
        }
        $ring_user_id = $ring_user_name_form->getUserId();

        $user_name_form = new UserNameForm($p_membership_request_user);
        $user_valid = $user_name_form->validate();
        if ($user_valid === false) {
            $json['error'] = ErrorHelper::model($user_valid->getErrors());
        }
        $user_id = $user_name_form->getUserId();

        if (isset($json['error']) === false) {
            RingApplication::insertApplication($ring_user_id, $user_id);
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Ban a user from a Ring.
     *
     * Submitted by a ring admin or a membership rhythm running on an admins account.
     *
     * @param {string} $g_user The username of the ring that is member is being banned from.
     * @param {array} $p_user A user object for the user who is being banned.
     * @param {string} $p_admin_user A user object for the admin user who is banning this member.
     * @param {string} $p_admin_passsword The admins password for this ring, to prove that they
     *      did indeed send this request.
     *
     * @return void
     */
    public function actionBanMember($g_user, array $p_user, array $p_admin_user, $p_admin_passsword) {
        $json = array();
        $json['success'] = false;

        $ring_user = array(
            'username' => $g_user,
            'domain' => HOST,
        );
        $ring_admin_form = new RingAdminForm;
        $ring_admin_form->admin_user = $p_admin_user;
        $ring_admin_form->ring_user = $ring_user;
        $ring_admin_form->admin_password = $p_admin_passsword;
        if ($ring_admin_form->validate() === false) {
            $json['error'] = ErrorHelper::model($ring_admin_form->getErrors());
        } else {
            $ring_id = $ring_admin_form->getRingId();
        }

        $user_name_form = new UserNameForm($p_user);
        if ($user_name_form->validate() === false) {
            $json['error'] = ErrorHelper::model($user_name_form->getErrors());
        } else {
            $ban_user_id = $user_name_form->getUserId();
        }

        if (isset($json['error']) === false) {
            RingMulti::banAUser($ring_id, $ban_user_id);
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Reinstate a banned a user to a Ring.
     *
     * Submitted by a ring admin or a membership rhythm running on an admins account.
     *
     * @param {string} $g_user The username of the ring that is member is being reinstated to.
     * @param {array} $p_user A user object for the user who is being reinstated.
     * @param {string} $p_admin_user A user object for the admin user who is reinstating this member.
     * @param {string} $p_admin_passsword The admins password for this ring, to prove that they
     *      did indeed send this request.
     *
     * @return void
     */
    public function actionReinstateMember($g_user, array $p_user, array $p_admin_user, $p_admin_passsword) {
        $json = array();
        $json['success'] = false;

        $ring_user = array(
            'username' => $g_user,
            'domain' => HOST,
        );
        $ring_admin_form = new RingAdminForm;
        $ring_admin_form->admin_user = $p_admin_user;
        $ring_admin_form->ring_user = $ring_user;
        $ring_admin_form->admin_password = $p_admin_passsword;
        if ($ring_admin_form->validate() === false) {
            $json['error'] = ErrorHelper::model($ring_admin_form->getErrors());
        } else {
            $ring_id = $ring_admin_form->getRingId();
        }

        $user_name_form = new UserNameForm($p_user);
        if ($user_name_form->validate() === false) {
            $json['error'] = ErrorHelper::model($user_name_form->getErrors());
        } else {
            $ban_user_id = $user_name_form->getUserId();
        }

        if (isset($json['error']) === false) {
            RingMulti::banAUser($ring_id, $ban_user_id);
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }
}

?>