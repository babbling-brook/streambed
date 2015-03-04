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
 * Main site actions.
 *
 * @package PHP_Controllers
 */
class SiteController extends Controller
{

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl',
        );
    }

    /**
     * Specifies the access control rules. This method is used by the 'accessControl' filter.
     *
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array(
                'allow',
                'actions' => array(
                    'Index',
                    'StoreExists',
                    'Error',
                    'JSError',
                    'DomusLogout',
                    'LogoutAll',
                ),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(),
                'users' => array('@'), // allow authenticated user
            ),
            array(
                'deny',
                'users' => array('*'),  // deny all other users
            ),
        );
    }

    /**
     * Reports that this is indeed a babbling brook store
     *
     * @return Allways returns true.
     */
    public function actionStoreExists() {
        if (Yii::app()->params['babbling_store'] !== true) {
            $exists = false;
        } else {
            $exists = true;
        }
        echo JSON::encode(array('exists' => $exists));
    }

    /**
     * Site home page.
     *
     * @return void
     */
    public function actionIndex() {
        $this->render('/Domus/Site/Index');
    }

    /**
     * This is the action to handle external exceptions.
     *
     * @return void
     */
    public function actionError() {
        $error = Yii::app()->errorHandler->error;
        if ($error !== false) {
            if (Yii::app()->request->isAjaxRequest === true && isset($_POST['ajaxurl']) === false) {
                echo $error['message'];
            } else {
                $this->render('/Shared/Page/Site/Error', $error);
            }
        }
    }

    /**
     * This is the action to log javascript errors.
     *
     * Request varaibles:
     * $_POST['type'] The type of error. See lookup table jserror.type
     * $_POST['data'] A string of JSON data.
     * $_POST['message'] The error message
     * $_POST['location'] Is this the scientia, domus or client domain?
     *
     * @return void
     */
    public function actionJSError() {
        $json = array();
        $model = new JsError;
        if (isset($_POST['type']) === true) {
            $model->type = $_POST['type'];
        }
        if (isset($_POST['data']) === true) {
            $model->data = $_POST['data'];
        }
        if (isset($_POST['message']) === true) {
            $model->message = $_POST['message'];
        }
        if (isset($_POST['location']) === true) {
            $model->location_name = $_POST['location'];
        }

        if ($model->validate() === true) {
            $model->save();
            $json['success'] = true;
        } else {
            $json['success'] = false;
        }
        echo JSON::encode($json);
    }

    /**
     * Logs any logged in user out of this local data store site.
     *
     * @param string $p_client_domain The domain name of the client site that is being logged out.
     *
     * @return void
     */
    public function actionDomusLogout($p_client_domain) {

        if (Yii::app()->user->isGuest === true) {
            $success = true;
        } else {

            // Check if this client domain is logged in.
            $site_access_id = SiteAccess::getSiteAccessID(Yii::app()->user->name, $p_client_domain);
            if ($site_access_id === false) {
                $success = true;
            } else {
                $site_id = SiteMulti::getSiteID($p_client_domain);
                SiteAccess::removeClient(Yii::app()->user->getId(), $site_id);

                // If there an no more clients then fully log the user off.
                $rows = SiteAccess::getAll();
                if (count($rows) === 0) {
                    Yii::app()->user->logout();
                }

                $success = true;
            }
        }

        $json = array('success' => $success);
        echo JSON::encode($json);
    }

    /**
     * Logs all client sites out of a data store.
     *
     * This is seperate from a client logout request and can only be accessed from the domus domain.
     *
     * @return void
     */
    public function actionLogoutAll() {
//        if(Yii::app()->user->isGuest !== true) {
//            $current_user_id = Yii::app()->user->getId();
//            SiteAccess::removeAllForUser($current_user_id);
//            Yii::app()->user->logout();
//        }
//
//        $this->render('logoutall');
        echo ('loggoutall currently broken.');

    }
}

?>