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
 * User Config Controller. Enables the user to change their config options.
 *
 * @package PHP_Controllers
 */
class UserConfigController extends Controller
{

    /**
     * The username of the user who is the owner of the url in this request.
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
            'User',
        );
    }

    /**
     * Validates the user and populates public username variable.
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
                'allow',
                'actions' => array(),
                'users' => array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'update' actions
                'actions' => array(
                    'Index',
                    'Get',
                    'ChangeRow',
                    'Reset',
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
     * Display the config page to the user.
     *
     * @return void
     */
    public function actionIndex() {
        $this->render('/Client/Page/Settings/Index');
    }

    /**
     * Fetch the config data for the current logged on user.
     *
     * @return void
     */
    public function actionGet() {
        $default_config = UserConfigDefault::getForSettingsPage();
        echo JSON::encode($default_config);
    }

}

?>