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
                    'Error',
                    'JSError',
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
     * Site home page.
     *
     * @return void
     */
    public function actionIndex() {
        $this->render('/Kindred/Site/Index');
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

        $error = '';
        if ($model->save() === false) {
            $error = $model->getErrors();
        }
        echo JSON::encode(array('error' => $error));
    }

}

?>