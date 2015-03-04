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
 * Manages regular expressions attatched to stream fields.
 *
 * @package PHP_Controllers
 */
class StreamRegexController extends Controller
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
     * @return array access control rules.
     */
    public function accessRules() {
        return array(
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete', 'create', 'update', 'index', 'view'),
                'expression' => 'Yii::app()->user->isadmin()',
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     *
     * @param integer $id The ID of the model to be displayed.
     *
     * @return void
     */
    public function actionView($id) {
        $this->render(
            '/Client/Admin/StreamRegex/View',
            array(
                'model' => $this->loadModel($id),
            )
        );
    }

    /**
     * Creates a new model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return void
     */
    public function actionCreate() {
        $model=new StreamRegex;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['StreamRegex']) === true) {
            $model->attributes=$_POST['StreamRegex'];
            if ($model->save() === true) {
                $this->redirect(array('view', 'id' => $model->stream_regex_id));
            }
        }

        $this->render(
            '/Client/Admin/StreamRegex/Create', array(
                'model' => $model,
            )
        );
    }

    /**
     * Updates a particular model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id The ID of the model to be updated.
     *
     * @return void
     */
    public function actionUpdate($id) {
        $model=$this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['StreamRegex']) === true) {
            $model->attributes=$_POST['StreamRegex'];
            if ($model->save() === true) {
                $this->redirect(array('view', 'id' => $model->stream_regex_id));
            }
        }

        $this->render(
            '/Client/Admin/StreamRegex/Update',
            array(
                'model' => $model,
            )
        );
    }

    /**
     * Deletes a particular model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id The ID of the model to be deleted.
     *
     * @return void
     */
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest === true) {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (isset($_GET['ajax']) === false) {
                $this->redirect(isset($_POST['returnUrl']) === true ? $_POST['returnUrl'] : array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Lists all models.
     *
     * @return void
     */
    public function actionIndex() {
        $dataProvider=new CActiveDataProvider('StreamRegex');
        $this->render(
            '/Client/Admin/StreamRegex/Index',
            array(
                'dataProvider' => $dataProvider,
            )
        );
    }

    /**
     * Manages all models.
     *
     * @return void
     */
    public function actionAdmin() {
        $model=new StreamRegex('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['StreamRegex']) === true) {
            $model->attributes=$_GET['StreamRegex'];
        }

        $this->render(
            '/Client/Admin/StreamRegex/Admin',
            array(
                'model' => $model,
            )
        );
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     *
     * If the data model is not found, an HTTP exception will be raised.
     *
     * @param integer $id The ID of the model to be loaded.
     *
     * @return StreamRegex
     */
    public function loadModel($id) {
        $model = StreamRegex::model()->findByPk((int)$id);
        if ($model === null ) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     *
     * @param StreamRegex $model The model to be validated.
     *
     * @return void
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) === true && $_POST['ajax'] === 'post-type-regex-form') {
            echo CActiveForm::validate($model);
        }
    }
}

?>
