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
 * USer Controller
 *
 * @package PHP_Controllers
 */
class AdminController extends Controller
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
                'actions' => array(),
                'users' => array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'update' actions
                'actions' => array(),
                'users' => array('@'),
            ),
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array(
                    'admin',
                    'UpdateTreeDecendents',
                    'EnsureAllStreamsHaveADefaultRhtyhm',
                    'UpdateAllUserConfigActionLocation',
                    'UpdateAllChildCounts',
                    'Minify',
                    'DeleteTests',
                    'DeleteOneTestUser',
                    'DeleteAllTestUsers',
                    'ExportUser',
                    'GetUserData',
                ),
                'users' => array('admin'),
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionExportUser() {
        $this->render('/Client/Admin/ExportUser');
    }

    public function actionGetUserData($p_username) {
        $json = array();
        if (strlen($p_username) < 1) {
            $json['success'] = false;
            $json['error'] = 'You must enter a username.';
        } else {
            if (strpos($p_username, '@') === false) {
                $p_username = $p_username . '@' . HOST;
            }
            $user_id = User::getIDFromFullName($p_username);
            if ($user_id === false) {
                $json['success'] = false;
                $json['error'] = 'Username not found.';
            } else {
                $json['success'] = true;
                $json['user_data'] = ExportMulti::getUser($user_id);
            }
        }

        echo JSON::encode($json);
    }

    public function actionDeleteTests() {
        $this->render('/Client/Admin/DeleteTests');
    }

    public function actionDeleteOneTestUser($p_test_username) {
        $json = array();
        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername($p_test_username, false);
        if ($user_id === false) {
                $json['success'] = false;
                $json['error'] = 'username not found.';
        } else if (User::isTestUser($user_id) === false) {
                $json['success'] = false;
                $json['error'] = 'This is not a test user.';
        } else {
            try {
                $delete_multi = new DeleteMulti();
                $delete_multi->deleteUser($user_id);
                $json['success'] = true;
            } catch (Exception $ex) {
                $json['success'] = false;
                $json['error'] = $ex->getMessage();
                $json['stack'] = $ex->getTrace();
            }
        }

        echo JSON::encode($json);
    }

    public function actionDeleteAllTestUsers() {
        $json = array();

        try {
            $delete_multi = new DeleteMulti();
            User::markTestUsers();
            $delete_multi->deleteAllTestUsers();
            $json['success'] = true;
        } catch (Exception $ex) {
            $json['success'] = false;
            $json['error'] = $ex->getMessage();
            $json['stack'] = $ex->getTrace();
        }

        echo JSON::encode($json);
    }


    public function actionMinify() {
        LessFacade::minify();
        UglifyFacade::minify();
        echo "done";
    }

    public function actionUpdateTreeDecendents() {
        PostDescendent::recreateAll();
        echo "done";
    }

    public function actionEnsureAllStreamsHaveADefaultRhtyhm() {
        StreamDefaultRhythm::ensureAllStreamsHaveADefaultRhtyhm();
        echo "done";
    }

    public function actionUpdateAllUserConfigActionLocation() {
        UserConfig::updateAllUserConfigActionLocation();
        echo "done";
    }

    public function actionUpdateAllChildCounts() {
        $post_id_rows = Post::getAllPostIds();
        foreach ($post_id_rows as $post_id_row) {
            $child_count = PostDescendent::getChildCount($post_id_row['post_id']);
            Post::updateChildCount($post_id_row['post_id'], $child_count);
        }
        echo "done";
    }
}

?>
