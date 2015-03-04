<?php
/**
 *
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
                'actions' => array(),
                'users' => array('*'),
            ),
            array(
                'allow', // Authenticated users
                'actions' => array(
                    'Index',
                    'NewRing',
                    'Passwords',
                    'SuperInvitors',
                    'AcceptInvitation',
                    'LeaveConfirmed',
                    'Leave',
                    'Resign',
                    'Invite',
                    'SendInvitation',
                    'Invitations',
                    'Members',
                    'InsertTakeName',
                    'ManageTakeNames',
                    'DeleteTakeName',
                    'Create',
                    'CreateJson',
                    'Update',
                    'UpdateJson',
                    'VetMembershipRequests',
                ),
                'users' => array('@'),
            ),
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array(),
                'users' => array('admin'),
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Index action.
     *
     * @param string [$g_leave_ring] A ring that has been resigned from.
     *
     * @return void
     */
    public function actionIndex($g_leave_ring=null) {
        $this->render(
            '/Client/Page/Ring/Index',
            array(
                'leave_ring' => $g_leave_ring,
            )
        );
    }

    /**
     * JSON action used to create a new ring.
     *
     * @param string $p_name The name of the ring
     * @param string $p_membership The membership type of the ring.
     * @param string $p_membership_rhythm If the membership type is 'rhythm' then this is the rhythm.
     * @param string $p_membership_super_ring If the membership type is 'super_ring' then this is the ring.
     * @param string $p_admin_type The administration type.
     * @param string $p_admin_super_ring If the administration type is set to 'super_ring', then this is the ring.
     * @param string $p_ring_rhythm The rhythm that runs on members of this ring.
     *
     * @return void
     *
     */
    public function actionCreateJson($p_name, $p_membership, $p_membership_rhythm, $p_membership_super_ring,
        $p_admin_type, $p_admin_super_ring, $p_ring_rhythm
    ) {
           $model = new RingForm();
           $model->name = $p_name;
           // @fixme This loookup helper (and the one below and two in actionUpdateJSON) should be inside a model rule.
           $model->membership = LookupHelper::getId('ring.membership_type', $p_membership);
           $model->membership_rhythm = $p_membership_rhythm;
           $model->membership_super_ring = $p_membership_super_ring;
           $model->admin_type = LookupHelper::getId('ring.admin_type', $p_admin_type);
           $model->admin_super_ring = $p_admin_super_ring;
           $model->ring_rhythm = $p_ring_rhythm;
           $json = array();
        if ($model->validate() === true) {
            $ring_id = Ring::createRing($model);
            UserRing::createUserAccess($ring_id, Yii::app()->user->getId(), true, true);
            $new_ring = UserRing::getConfig(Yii::app()->user->getId(), $ring_id);
            $json['new_ring'] = $new_ring[0];
        } else {
            $json['errors'] = $model->getErrors();
        }
        echo JSON::encode($json);
    }

    /**
     * JSON action used to update a ring.
     *
     * @param string $p_name The name of the ring
     * @param string $p_membership The membership type of the ring.
     * @param string $p_membership_rhythm If the membership type is 'rhythm' then this is the rhythm.
     * @param string $p_membership_super_ring If the membership type is 'super_ring' then this is the ring.
     * @param string $p_admin_type The administration type.
     * @param string $p_admin_super_ring If the administration type is set to 'super_ring', then this is the ring.
     * @param string $p_ring_rhythm The rhythm that runs on members of this ring.
     *
     * @return void
     */
    public function actionUpdateJson($p_name, $p_membership, $p_membership_rhythm, $p_membership_super_ring,
        $p_admin_type, $p_admin_super_ring, $p_ring_rhythm
    ) {
        $ring_id = Ring::getId($p_name);
        $this->checkRingAdmin($ring_id);

        $model = RingForm::load($p_name);
        $model->membership = LookupHelper::getId('ring.membership_type', $p_membership);
        $model->membership_rhythm = $p_membership_rhythm;
        $model->membership_super_ring = $p_membership_super_ring;
        $model->admin_type = LookupHelper::getId('ring.admin_type', $p_admin_type);
        $model->admin_super_ring = $p_admin_super_ring;
        $model->ring_rhythm = $p_ring_rhythm;

        $json = array();
        if ($model->validate() === true) {
            $admin_type = LookupHelper::getValue($model->admin_type);
            Ring::updateRing($model, $ring_id);
            // If the ring admin type has been changed to super user then remove this user from the admin privilage.
            // If they are a member of the super ring then they will still have access to it.
            if ($admin_type === 'super_ring') {
                UserRing::removeAllAdmins($ring_id);
            } else {
                UserRing::setAdmin($ring_id, Yii::app()->user->getId());
            }
            $new_ring = UserRing::getConfig(Yii::app()->user->getId(), $ring_id);
            $json['updated_ring'] = $new_ring[0];
        } else {
            $json['errors'] = $model->getErrors();
        }
        echo JSON::encode($json);
    }

    /**
     * Presents the form required to make a new ring.
     *
     * @return void
     */
    public function actionCreate() {
        $model = new RingForm();
        $this->render(
            '/Client/Page/Ring/Create',
            array(
                'model' => $model,
            )
        );
    }

    /**
     * Update action.
     *
     * @return void
     */
    public function actionUpdate() {
        $ring_name = $_GET['user'];

        $model = RingForm::load($ring_name);
        if ($model === false) {
            throw new CHttpException(404, 'Page not Found. No ring available with the name: ' . $ring_name);
        }

        // Check that the current user is allowed to update this ring
        $ring_id = Ring::getId($model->name);
        $this->checkRingAdmin($ring_id);

        $admin_type = LookupHelper::getValue($model->admin_type);

        $this->render(
            '/Client/Page/Ring/Update',
            array(
                'model' => $model,
                'admin_type' => $admin_type,
                'ring_id' => $ring_id,
            )
        );
    }

    /**
     * Echo a JSON object containing the ring passwords requested.
     *
     * @return void
     */
    public function actionPasswords() {
        if (isset($_POST['rings']) === false) {
            throw new CHttpException(400, 'Bad data. No rings posted');
        }

        $rings = Ring::getPasswordsFromNames($_POST['rings'], Yii::app()->user->getId());

        $json = array('success' => 'true', 'rings' => $rings);

        echo JSON::encode($json);
    }

    /**
     * Echo JSON data about admin and member permissions for sending super_ring invites.
     *
     * @return void
     */
    public function actionSuperInvitors() {
        $rings = UserRing::getSuperInviters(Yii::app()->user->getId());

        echo JSON::encode($rings);
    }

    /**
     * Validates if a given user can send invitations.
     *
     * @param integer $ring_id The id of the ring to check.
     * @param string The type of invitation being sent. Valid types are 'member' and 'admin'.
     *
     * @return void
     */
    private function checkCanSendInvites($ring_id, $type, $invite_type) {
        // Check that this is a ring that allows invitations

        if ($type !== 'member' && $type !== 'admin') {
            throw new CHttpException(400, 'Bad data. Type is not valid');
        }

        if ($type === 'member' && $invite_type === 'invitation') {
            if (UserRing::checkIfMember($ring_id, Yii::app()->user->getId()) === false) {
                throw new CHttpException(403, 'Forbidden. You are not a member of this ring.');
            }
        } else if ($type === 'member' && $invite_type === 'admin_invitation') {
            if (UserRing::checkIfAdmin($ring_id, Yii::app()->user->getId()) === false) {
                throw new CHttpException(403, 'Forbidden. You are not an admin of this ring.');
            }
        } else if ($type === 'admin') {
            if (UserRing::checkIfAdmin($ring_id, Yii::app()->user->getId()) === false) {
                throw new CHttpException(403, 'Forbidden. You are not an admin of this ring.');
            }
            $admin_type = Ring::getAdminType($ring_id);
            if ($admin_type !== 'invitation') {
                throw new CHttpException(403, 'Forbidden. This ring does not allow admin invitations.');
            }
        } else {
            throw new CHttpException(403, 'Forbidden. This ring does not allow member invitations.');
        }
    }

    /**
     * JSON request to Send an invitation to the provided user.
     *
     * Only rings with membership set to 'invitation' or 'admin_invitation' can do this.
     *
     * @param array $p_to The username and domain of the user to send the invitation to.
     * @param string $p_type The type of invitation to send. 'member', or 'admin'.
     *
     * @return void
     */
    public function actionSendInvitation(array $p_to, $p_type) {
        $ring_id = Ring::getId($this->username);
        $error = '';
        if ($ring_id === false) {
            $error = 'This is not a valid ring.';
        } else {
            $invite_type = Ring::getMemberType($ring_id);
            $this->checkCanSendInvites($ring_id, $p_type, $invite_type);

            $to = $p_to['domain'] . '/' . $p_to['username'];
            // Check that the username is valid
            $to_user_id = User::isUserValid($to);

            if ($to_user_id === false) {
                $error = 'Not a valid user. Please try again.';
            } else {
                if (UserRing::checkBanned($ring_id, $to_user_id) === true) {
                    $error = 'This user is banned from this ring.';
                }
                if ($invite_type === 'member' && UserRing::checkIfMember($ring_id, $to_user_id) === true) {
                    $error = 'This user is already a member of this ring.';
                }
                if ($invite_type === 'admin'    // Wierd xdebug socket exception if this is on one line!
                    && UserRing::checkIfAdmin($ring_id, $to_user_id) === true
                ) {
                    $error = 'This user is already an administrator of this ring.';
                }
                if (Invitation::checkIfAlreadyInvited($ring_id, $to_user_id, $p_type) === true) {
                    $error = 'This user has already been invited to join this ring';
                }
            }
        }
        if ($error === '') {
            Invitation::sendInvite(Yii::app()->user->getId(), $to_user_id, $p_type, $ring_id);
            echo JSON::encode(array('success' => 'true'));
        } else {
            echo JSON::encode(
                array(
                    'success' => 'false',
                    'error' => $error,
                )
            );
        }
    }

    /**
     * JSON request to fetch a users waiting invitations.
     *
     * @note Not sure this is the right place for this action. Its path uses the logged in users username rather
     * than a ring username.
     *
     * @param array $p_to The username and domain of the user to send the invitation to.
     * @param string $p_type The type of invitation to send. 'member', or 'admin'.
     *
     * @return void
     */
    public function actionInvitations() {
        $invitations = Invitation::getAllForUser(Yii::app()->user->getId());
        echo JSON::encode(
            array(
                'success' => 'true',
                'invitations' => $invitations,
            )
        );
    }

    /**
     * Send an invite to another user.
     *
     * Used for both member and admin members.
     *
     * @param string $g_type Is this a 'member' or 'admin' invitation.
     * @param stirng $g_menu_type Was this accessed from an admin or membership area.
     * @param stirng $g_to Who the invitation is being sent to.
     *
     * @return void
     */
    public function actionInvite($g_type, $g_menu_type, $g_to=null) {
        $ring_id = Ring::getId($this->username);
        $invite_type = Ring::getMemberType($ring_id);
        $admin_type = Ring::getAdminType($ring_id);
        $this->checkCanSendInvites($ring_id, $g_type, $invite_type);

        $this->render(
            '/Client/Page/Ring/Invite',
            array(
                'ring_id' => $ring_id,
                'type' => $g_type,
                'invite_type' => $invite_type,
                'admin_type' => $admin_type,
                'menu_type' => $g_menu_type,
                'to' => $g_to,
                'ring_name' => $this->username,
            )
        );
    }

    /**
     * Accepts an invitation.
     *
     * @param string $p_ring_domain The domain of the ring that an invite has been accepted for.
     * @param string $p_ring_username The username of the ring that an invite has been accepted for.
     *
     * @return void
     */
    public function actionAcceptInvitation($p_ring_domain, $p_ring_username, $p_type) {

        $ring_id = Ring::getId($this->username, Yii::app()->params['site_id']);
        if ($ring_id === 0) {
            throw new CHttpException(400, 'Bad data. Ring not found.');
        }

        $user_id = Yii::app()->user->getId();

        $type_id = LookupHelper::getId('invitation.type', $p_type);
        if ($type_id === null) {
            throw new CHttpException(400, 'Bad data. Type not found.');
        }

        if (UserRing::checkBanned($ring_id, $user_id) === true) {
            throw new CHttpException(403, 'Forbidden. You have been banned from this Ring.');
        }

        $invite_id = Invitation::getInviteByRingAndUser($user_id, $ring_id, $p_type);
        if ($invite_id === false) {
            throw new CHttpException(403, 'Forbidden. You do not have an invitation for this Ring.');
        }

        if ($p_type === 'admin') {
            $accepted = UserRing::checkIfAdmin($ring_id, $user_id);
        } else {
            $accepted = UserRing::checkIfMember($ring_id, $user_id);
        }

        UserRing::changePermission($ring_id, $user_id, $p_type, true);
        Invitation::deleteInvite($ring_id, $user_id, $p_type);

        $new_ring = UserRing::getConfig($user_id, $ring_id);

        echo JSON::encode(
            array(
                'success' => 'true',
                'ring' => $new_ring[0],
            )
        );
    }

    /**
     * Allows an admin to ban a user.
     *
     * @return void
     */
    public function actionBan() {
        $ring_id = Ring::getId($_GET['user']);
        $this->checkRingAdmin($ring_id);

        $user_ring_model = new UserRing('search');
        $user_ring_model->unsetAttributes();  // clear any default values

        if (isset($_GET['UserRing']) === true) {
            $user_ring_model->attributes=$_GET['UserRing'];
        }

        $this->render(
            '/Client/Page/Ring/Ban',
            array(
                'ring_id' => $ring_id,
                'ring_name' => $_GET['user'],
                'user_ring_model' => $user_ring_model,
            )
        );
    }

    /**
     * Members page.
     *
     * @return void
     */
    public function actionMembers() {
        $ring_id = Ring::getId($_GET['user']);

        if (UserRing::checkIfMember($ring_id, Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'Forbidden. You are not a member of this ring.');
        }

        $ring_user_id = Ring::getRingUserId($ring_id);
        $user_profile_model = UserProfile::get($ring_user_id);
        if (isset($user_profile_model) === false) {
            throw new CHttpException(400, 'Bad data. Ring user profile does not exist.');
        }

        $type = Ring::getMemberType($ring_id);

        $this->render(
            '/Client/Page/Ring/Members',
            array(
                'ring_name' => $_GET['user'],
                'ring_id' => $ring_id,
                'type' => $type,
                'user_profile_model' => $user_profile_model,
            )
        );
    }

    /**
     * Action for a member to leave a Ring.
     *
     * @return void
     */
    public function actionLeave() {
        $ring_id = Ring::getId($_GET['user']);

        if (UserRing::checkIfMember($ring_id, Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'Forbidden. You are not a member of this ring.');
        }

        $type = Ring::getMemberType($ring_id);

        $this->render(
            '/Client/Page/Ring/Leave',
            array(
                'ring_id' => $ring_id,
                'type' => $type,
                'ring_name' => $_GET['user'],
                'ring_domain' => Yii::app()->params['host'],
            )
        );
    }

    /**
     * Action for confirming that a user wants to leave a ring.
     *
     * @return void
     */
    public function actionLeaveConfirmed() {
        $ring_id = Ring::getId($_GET['user']);

        if (UserRing::checkIfMember($ring_id, Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'Forbidden. You are not a member of this ring.');
        }

        UserRing::changePermission($ring_id, Yii::app()->user->getId(), 'member', false);

        echo JSON::encode(array('success' => true));
    }

    /**
     * Manage the take names for a ring.
     *
     * @return void
     */
    public function actionManageTakeNames() {
        $ring_id = Ring::getId($_GET['user']);
        $this->checkRingAdmin($ring_id);

        $admin_type = Ring::getAdminType($ring_id);

        $take_names = RingTakeName::getForRing($ring_id);

        $this->render(
            '/Client/Page/Ring/ManageTakeNames',
            array(
                'ring_id' => $ring_id,
                'admin_type' => $admin_type,
                'ring_name' => $_GET['user'],
                'take_names' => $take_names,
            )
        );
    }

    /**
     * Insert a new take name or produce an error.
     *
     * @return void
     */
    public function actionInsertTakeName() {
        $ring_id = Ring::getId($_GET['user']);
        $this->checkRingAdmin($ring_id);

        // Insert or update?
        $ring_take_name_id = Yii::app()->request->getPost('ring_take_name_id');
        if (empty($ring_take_name_id) === true) {
            $take_name_model = new RingTakeName;
            $take_name_model->ring_id = $ring_id;
        } else {
            if (ctype_digit($ring_take_name_id) === false) {
                throw new CHttpException(400, 'Bad Data. ring_take_name_id is not an integer.');
            }

            $take_name_model = RingTakeName::load($ring_take_name_id);

            if ($take_name_model->ring_id !== $ring_id) {
                throw new CHttpException(403, 'Forbidden. Ring does not own this resource.');
            }

        }

        $take_name_model->name = Yii::app()->request->getPost('take_name');
        $take_name_model->amount = Yii::app()->request->getPost('amount');
        $take_name_model->stream_url = Yii::app()->request->getPost('stream');

        $errors = false;
        if ($take_name_model->save() === false) {
            $errors = JSONHelper::convertYiiModelError($take_name_model->getErrors());
        }

        $ring_take_name_id = $take_name_model->ring_take_name_id;

        echo JSON::encode(
            array(
                'errors' => $errors,
                'ring_take_name_id' => $ring_take_name_id,
            )
        );
    }

    /**
     * Deletes a take name for a ring.
     *
     * @return void
     */
    public function actionDeleteTakeName() {
        $ring_id = Ring::getId($_GET['user']);
        $this->checkRingAdmin($ring_id);

        $ring_take_name_id = Yii::app()->request->getPost('ring_take_name_id');
        if (ctype_digit($ring_take_name_id) === false) {
            throw new CHttpException(400, 'Bad Data. ring_take_name_id is not an integer.');
        }
        $take_name_model = RingTakeName::load($ring_take_name_id);
        if ($take_name_model->ring_id !== $ring_id) {
            throw new CHttpException(403, 'Forbidden. Ring does not own this resource.');
        }

        RingTakeName::deleteRow($ring_take_name_id);
        echo JSON::encode(array('success' => true));
    }

    /**
     * Allows an admin to resign.
     *
     * @return void
     */
    public function actionResign() {
        $ring_id = Ring::getId($_GET['user']);
        $this->checkRingAdmin($ring_id);

        if (isset($_POST['confirm']) === true) {
            UserRing::changePermission($ring_id, Yii::app()->user->getId(), 'admin', false);
            $this->render(
                '/Client/Page/Ring/ResignedAdmin',
                array(
                    'ring_name' => $_GET['user'],
                )
            );
            return;
        }

        $admin_type = Ring::getAdminType($ring_id);

        $admins = UserRing::getAdmins($ring_id);
        $admin_count = count($admins);

        $this->render(
            '/Client/Page/Ring/Resign', array(
                'ring_name' => $_GET['user'],
                'admin_type' => $admin_type,
                'admin_count' => $admin_count,
            )
        );
    }

    /**
     * Gives ring admin users the option to vet new member requests.
     *
     * @param string $g_user The username of the ring whose members are being vetted.
     *
     * @return void
     */
    public function actionVetMembershipRequests($g_user) {
        $ring_id = Ring::getId($g_user);
        $this->checkRingAdmin($ring_id);

        $member_type = Ring::getMemberType($ring_id);
        if ($member_type !== 'request') {
            throw new CHttpException(404, 'Page not found. This ring does not have a membership type of \'request\'.');
        }

        $this->render(
            '/Client/Page/Ring/VetMembershipRequests', array(
                'ring_name' => $g_user,
                'ring_id' => $ring_id,
            )
        );
    }

    /**
     * Checks if a user has admin permissions for this ring.
     *
     * @param integer $ring_id The id of the ring to check for admin permissions.
     *
     * @return void
     */
    protected function checkRingAdmin($ring_id) {
        if (isset($ring_id) === false) {
            throw new CHttpException(400, 'Bad data. Ring name not found.');
        }
        if (UserRing::checkIfAdmin($ring_id, Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'Forbidden. You do not have permission to modify this resource.');
        }
    }

    /**
     * Returns the items for a CMenu that are used on ring admin pages.
     *
     * @param string $type The type of ring menu. Valid values are 'member' and 'admin'.
     * @param string $admin_type The type of menu. Valid values are 'invitation' and 'only_me'.
     * @param string $membership_type The type of membership this ring has.
     *      USed to display a link to invite members if the invitation type is set to admin_invite.
     *
     * @return array(string)
     */
    protected static function adminMenu($admin_type, $membership_type) {
        $menu = array(
            array(
                'label' => 'Update',
                'url' => '/' . $_GET['user'] .'/ring/update',
            ),
            array(
                'label' => 'Manage Users',
                'url' => '/' . $_GET['user'] .'/ring/ban',
            ),
            array(
                'label' => 'Edit Ring Profile',
                'url' => '/' . $_GET['user'] .'/editprofile',
            ),
            array(
                'label' => 'Manage Take Names',
                'url' => '/' . $_GET['user'] .'/ring/managetakenames',
            ),
        );

        $member_invite_class = '';
        if ($membership_type !== 'admin_invitation') {
            $member_invite_class = 'hide';
        }
        array_push(
            $menu,
            array(
                'label' => 'Send Member Invitation',
                'url' => '/' . $_GET['user'] .'/ring/invite?menu_type=admin&type=member',
                'itemOptions' => array(
                    'class' => $member_invite_class,
                    'id' => 'ring_member_invite_menu',
                )
            )
        );

        $membership_request_class = '';
        if ($membership_type !== 'request') {
            $membership_request_class = 'hide';
        }
        array_push(
            $menu,
            array(
                'label' => 'Vet Membership Requests',
                'url' => '/' . $_GET['user'] .'/ring/vetmembershiprequests',
                'itemOptions' => array(
                    'class' => $membership_request_class,
                    'id' => 'vet_membership_requests_menu',
                )
            )
        );

        $admin_invite_class = '';
        if ($admin_type !== 'invitation') {
            $admin_invite_class = 'hide';
        }
        array_push(
            $menu,
            array(
                'label' => 'Send Admin Invitation',
                'url' => '/' . $_GET['user'] .'/ring/invite?menu_type=admin&type=admin',
                'itemOptions' => array(
                    'class' => $admin_invite_class,
                    'id' => 'ring_admin_invite_menu',
                )
            ),
            array(
                'label' => 'Resign',
                'url' => '/' . $_GET['user'] .'/ring/resign',
                'itemOptions' => array(
                    'class' => $admin_invite_class,
                    'id' => 'ring_resign_menu',
                )
            )
        );

        return $menu;
    }

    /**
     * Returns the items for a CMenu that are used on ring admin pages.
     *
     * @param string $type The type of menu. Valid values are 'invitation' and 'only_me'.
     *
     * @return array(array)
     */
    protected static function memberMenu($type) {
        $menu = array(
            array(
                'label' => 'Membership Area',
                'url' => '/' . $_GET['user'] .'/ring/members',
            ),
            array(
                'label' => 'Ring Profile',
                'url' => '/' . $_GET['user'] .'/profile',
            ),
            array(
                'label' => 'Leave Ring',
                'url' => '/' . $_GET['user'] .'/ring/leave',
            ),
        );
        if ($type === 'invitation') {
            array_push(
                $menu,
                array(
                    'label' => 'Send Member Invitation',
                    'url' => '/' . $_GET['user'] .'/ring/invite?menu_type=member&type=member',
                )
            );
        }
        return $menu;
    }

}

?>