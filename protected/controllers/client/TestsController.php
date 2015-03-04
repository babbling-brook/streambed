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
 * Test management.
 *
 * @package PHP_Controllers
 */
class TestsController extends Controller
{

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            array(
                'application.filters.IsDomusFilter + filteriframe suggestioniframe kindrediframe'
            ),
            array(
                'application.filters.IsScientiafilter + suggestiontestdata'
            ),
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
                'allow',    // Public access
                'actions' => array(
                    'suggestiontestdata',
                ),
                'users' => array('*'),
            ),
            array(
                'allow', // Admin access
                'actions' => array(
                    "index",
                    "DeleteTestData",

                    "Restore",

                    "TestLocalStorage",
                    "Javascript",
                    "Domusiframe",
                    "Scientiaiframe",
                    "Suggestioniframe",
                    "Kindrediframe",
                    "Filteriframe",
                    "SetupStreamUpdateTest",
                    "TriggerStreamUpdateTest",
                    "TriggerCommentsUpdateTest",
                    "TriggerCommentsEditTest",
                ),
                'expression' => 'Yii::app()->user->isadmin()',
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * List of all JavaScript Tests.
     *
     * @return void
     */
    public function actionIndex() {
        $this->render('/Client/Page/Tests/index');
    }



    /**
     * Delete all test data from the Data base.
     *
     * @return void
     */
    public function actionDeleteTestData() {
        TestData::deleteTestData();
        echo ("Test data has been deleted.");
    }

    /**
     * Runs the resotre process to restore the test data.
     *
     * If the test user data is deleted thin this won't run. In which case temporarily comment out the filter funtion.
     * This will allow this funtion to run again. *** Be sure to reinstate it afterwards ***.
     *
     * @return void
     */
    public function actionRestore() {
        TestData::restore();
        echo ("Test data has been reset.");
    }

    /**
     * JavaScript Tests.
     *
     * @return void
     */
    public function actionJavascript() {
        $this->render('/Client/Admin/Tests/Javascript');
    }

    /**
     * Domus iframe.
     *
     * @return void
     */
    public function actionDomusiframe() {
        TestData::restore();
        $this->render('/Client/Admin/Tests/Domusiframe');
    }

    /**
     * Scientia iframe tests.
     *
     * @return void
     */
    public function actionScientiaiframe() {
        TestData::restore();
        $this->render('/Client/Admin/Tests/Scientiaiframe');
    }

    /**
     * Filter iframe tests.
     *
     * @return void
     */
    public function actionFilteriframe() {
        TestData::restore();
        $this->render('/Client/Admin/Tests/Filteriframe');
    }

    /**
     * Filter iframe tests.
     *
     * @return void
     */
    public function actionSuggestioniframe() {
        TestData::restore();
        $this->render('/Client/Admin/Tests/Suggestioniframe');
    }

    /**
     * Filter iframe tests.
     *
     * @return void
     */
    public function actionKindrediframe() {
        TestData::restore();
        $this->render('/Client/Admin/Tests/Kindrediframe');
    }

    /**
     * Provides test data for the test suggestion Rhythm.
     *
     * This is accessed via the scientia domain.
     * Supplies a stream in the data which is then used as a suggestion.
     *
     * @return void
     */
    public function actionSuggestionTestData() {

        $data = array(
            "name" => "test suggested stream",
            "domain" => "cobaltcascade.localhost",
            "username" => "test",
            "version" => "latest/latest/latest",
            "type" => "stream",
        );
        echo JSON::encode($data);
    }

    /**
     * Sets up the test data in preperation for a stream update test. Temporarily removes some test data.
     *
     * @return void
     */
    public function actionSetupStreamUpdateTest() {
        TestData::setupStreamUpdateTest();
        echo ("Test data has been set up for an update.");
    }

    /**
     * Triggers the stream update test by inserting posts ready for download as an update.
     *
     * @return void
     */
    public function actionTriggerStreamUpdateTest() {
        TestData::triggerStreamUpdateTest();
        echo ("Test posts have been set live.");
    }

    /**
     * Triggers the posts update test by inserting comment posts ready for download as an update.
     *
     * @return void
     */
    public function actionTriggerCommentsUpdateTest() {
        TestData::triggerCommentsUpdateTest();
    }

    /**
     * Triggers the posts edit test by editing a comment so that another user can see the 'show revision' link.
     *
     * @return void
     */
    public function actionTriggerCommentsEditTest() {
        TestData::triggerCommentsEditTest();
    }

    /**
     * Displays the javascript test for local storage.
     */
    public function actionTestLocalStorage() {
        $this->render('/Client/Page/Tests/TestLocalStorage');
    }

}

?>