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
 * USer Controller
 *
 * @package PHP_Controllers
 */
class UserController extends Controller
{

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules. This method is used by the 'accessControl' filter.
     *
     * @return array access control rules.
     */
    public function accessRules() {
        return array(
            array(
                'allow',
                'actions' => array(
                    'Profile',
                ),
                'users' => array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'update' actions
                'actions' => array(
                    'NewUser',
                    'EditProfile',
                    'NewProfileImage',
                    'EditProfileFields',
                    'StartTutorials',
                    'ExitTutorials',
                    'RestartTutorials',
                    'LevelUp',
                    'StreamSubscriptions',
                    'Download',
                    'DownloadJSON',
                ),
                'users' => array('@'),
            ),
            array(
                'allow',
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
     * Start or restart the tutorials for the logged in user.
     */
    public function actionStartTutorials() {
        UserLevel::startTutorials(Yii::App()->user->getID());
        echo JSON::encode(array('success' => true));
    }

    /**
     * Turn tutorials off for the logged in user.
     */
    public function actionExitTutorials() {
        $level = UserLevel::exitTutorials(Yii::App()->user->getID());
        echo JSON::encode(array('success' => true));
    }

    /**
     * Restart the current tutorial set from the beggining.
     */
    public function actionRestartTutorials() {
        $level = UserLevel::restartTutorials(Yii::App()->user->getID());
        echo JSON::encode(array('level' => $level));
    }

    /**
     * Turn tutorials off for the logged in user.
     */
    public function actionLevelUp() {
        $user_level = UserLevel::LevelUp(Yii::App()->user->getID());
        echo JSON::encode(array('level' => $user_level));
    }

    /**
     * Displays a profile.
     *
     * This may be called for a local user via /<user>/profile
     * or for another sites user via /elsewhere/<domain>/<user>/profile
     * Request variables:
     * $_GET['domain'] The domain of the profile
     * $_GET['user'] The username of the profile
     *
     * @return void
     */
    public function actionProfile() {
        // If this user is logged on or remote then render shell view - data is retrieved via json.
        if (isset($_GET['domain']) === true || Yii::app()->user->isGuest === false) {
            if (isset($_GET['domain']) === true) {
                $domain = $_GET['domain'];
            } else {
                $domain = Yii::app()->params['host'];
            }

            $is_ring = User::isRing($domain, $_GET['user']);

            $this->render(
                '/Client/Page/User/Profile',
                array(
                    'username' => $_GET['user'],
                    'domain' => $domain,
                    'is_ring' => $is_ring,
                )
            );
            return;
        }

        // else display a static verison of the page for search engines etc.
        //Check user exists
        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername(Yii::app()->request->getQuery('user'), false);
        if ($user_id === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        $model = UserProfile::get($user_id);
        if (isset($model) === false) {
            throw new CHttpException(400, "Bad Request. No profile attatched to this user.");
        }

        $this->render(
            '/Public/Page/User/Profile', array(
                'user_model' => $model,
                'username' => Yii::app()->request->getQuery('user'),
            )
        );
    }

    /**
     * Allows a user to edit their profile.
     *
     * @return void
     */
    public function actionEditProfile() {

        $ring_id = $this->checkProfileEditable();

        // Use the url username rather than logged in version as it might be a ring
        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername($_GET['user']);
        $model = UserProfile::get($user_id);

        if (is_null($model) === true) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        $this->render(
            '/Client/Page/User/EditProfile',
            array(
                'model' => $model,
                'ring_id' => $ring_id,
            )
        );

    }

    /**
     * JSON action for updating profile fields. Only updates one field at a time.
     *
     * @param string $p_real_name The profiles real name field.
     * @param string $p_about The profiles about field.
     *
     * @return void
     */
    public function actionEditProfileFields($p_field, $p_value) {
        $this->checkProfileEditable();

        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername($_GET['user']);
        $profile_model = UserProfile::get($user_id);
        if (is_null($profile_model) === true) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        $json_array = JSONHelper::oneField($profile_model, $p_field, $p_value);
        echo JSON::encode($json_array);
    }

    /**
     * A new profile image is being uploaded.
     *
     * @return void
     */
    public function actionNewProfileImage($g_user) {

        $this->checkProfileEditable();

        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("jpeg", "jpg", "gif", "png");
        // max file size in bytes
        $sizeLimit = 2 * 1024 * 1024;

        $root = realpath(Yii::app()->basePath . "/../");

        $domain = str_replace(' ', '-', HOST);

        // If this is not the users profile. It might be a ring that they administer.
        if ($g_user !== Yii::app()->user->name) {
            $ring_id = Ring::getId($g_user);
            if ($ring_id === false) {
                throw new CHttpException(403, 'You are not the owner of this profile.');
            }
            if (UserRing::checkIfAdmin($ring_id, Yii::app()->user->getId()) === false) {
                throw new CHttpException(403, 'You are not an administrator of this ring profile.');
            }
        }

        $username = str_replace(' ', '-', $g_user);


        $path_to_user_images = $root . '/images/user/' . $domain . '/';
        if (file_exists($path_to_user_images) === false) {
            mkdir($path_to_user_images, 0777, true);
        }
        $path_to_user_images = $path_to_user_images . $username . '/';
        if (file_exists($path_to_user_images) === false) {
            mkdir($path_to_user_images, 0777, true);
        }
        if (file_exists($path_to_user_images . '/profile/') === false) {
            mkdir($path_to_user_images . '/profile/', 0777, true);
        }
        if (file_exists($path_to_user_images . '/profile/original/') === false) {
            mkdir($path_to_user_images . '/profile/original/', 0777, true);
        }
        if (file_exists($path_to_user_images. '/profile/small/') === false) {
            mkdir($path_to_user_images. '/profile/small/', 0777, true);
        }
        if (file_exists($path_to_user_images. '/profile/large/') === false) {
            mkdir($path_to_user_images. '/profile/large/', 0777, true);
        }

        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload(
            $path_to_user_images . '/profile/original/',
            true,
            'profile'
        );

        // If there has been an error then report it and die.
        // to pass data through iframe you will need to encode all html tags
        if ($result !== true) {
            echo JSON::encode(array('error' => $result));
            return;
        }

        // Resize the image.
        $original_filename =  'profile.' . $uploader->ext;
        $large_image = new SimpleImage();
        $large_image->load($path_to_user_images . '/profile/original/' . $original_filename);
        $large_image->resize(500, 500);
        $large_image->save($path_to_user_images . '/profile/large/profile.jpg', IMAGETYPE_JPEG, 90);

        $small_image = new SimpleImage();
        $small_image->load($path_to_user_images . '/profile/original/' . $original_filename);
        $small_image->resize(75, 75);
        $small_image->save($path_to_user_images . '/profile/small/profile.jpg', IMAGETYPE_JPEG, 90);

        echo JSON::encode(array('success' => true));
    }

    /**
     * Throws an error if the profile in the url is not editable by the logged on user.
     *
     * @return integer|boolean If this is ring profile then the id of the ring is returned. Otherwise false.
     */
    protected function checkProfileEditable() {
        $ring_id = false;
        $user_site_id = Yii::app()->user->getSiteID();
        if (Yii::app()->user->getName() !== $_GET['user'] || $user_site_id !== Yii::app()->params['site_id']) {
            // This may be a ring profile, in which case need to check for admin permissions
            $ring_id = Ring::getId($_GET['user']);
            if (UserRing::checkIfAdmin($ring_id, Yii::app()->user->getId()) === false) {
                throw new CHttpException(403, "Forbidden. You do not have permission to access this resource");
            }
        }
        return $ring_id;
    }

    /**
     * The page a user sees after signing up.
     *
     * @param string $g_user The username of the new user
     *
     * @return void
     */
    public function actionNewUser($g_user) {
        if ($g_user !== Yii::app()->user->getName()) {
            throw new CHttpException(403, 'Forbidden. Not logged on as this user.');
        }

        $user_multi = new UserMulti(Yii::app()->user->getSiteID());
        $user = $user_multi->getUserFromUserName($g_user);

        if (Yii::app()->params['site_id'] !== $user->site_id) {
            $site_url = SiteMulti::getDomain($user->site_id);
            throw new CHttpException(
                403,
                'Forbidden. Yor data store is not on this site. <br />'
                    . '<a href="http://' . $site_url . '/' . $g_user . '">Your data store is here.</a> '
                    . '<em>(opens in a new window)</em>'
            );
        }

        $this->render(
            '/Client/Page/User/NewUser',
            array(
                'model' => $user,
            )
        );
    }

    /**
     * Enables the logged on user to download all their data.
     *
     *
     */
    public function actionDownload() {
        $this->render('/Client/Page/User/Download');
    }

    /**
     * Downloads all of a users data in JSON format.
     *
     */
    public function actionDownloadJSON() {
        $json = array();
        $json['success'] = true;
        $json['user_data'] = UserMulti::getAllUserData(Yii::app()->user->getId());
        echo JSON::encode($json);
    }

    /**
     * Show generic streams.
     *
     * @return void
     */
    public function actionStreamSubscriptions() {
        $this->render('/Client/Page/User/StreamSubscriptions');
    }

}

?>