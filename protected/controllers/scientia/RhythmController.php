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
 * Rhythm controller
 * All actions are called via /user/rhythm/action
 *
 * @package PHP_Controllers
 */
class RhythmController extends Controller
{

    /**
     * The username of the user who owns the Rhythm in the action url.
     *
     * @var string
     */
    public $username;

    /**
     * The primary key of the user who owns the Rhythm in the action url.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The version part of the Rhythm in the action url. In dot format.
     *
     * @var string
     */
    public $version_string;

    /**
     * The version part of the Rhythm in the action url. In major/minor/patch format.
     *
     * @var string
     */
    public $version_link;

    /**
     * Rhythm management links.
     *
     * @var string
     */
    public $menu_drop_down;

    /**
     * The currently loaded data model instance. Includes sub models for version, cat and user.
     *
     * @var Rhythm
     */
    public $model;

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            array(
                'application.filters.UsernameFilter - exists'
            ),
            array(            // Must run before the remaining filters. Loads the model
                'application.filters.VersionFilter
                    +json,
                     minijson',
                "data_type" => "rhythm",
                "version_type" => "rhythm",
            ),
            array(
                'application.filters.PrivateStatusFilter + json, minijson',
            ),
        );
    }

    /**
     * Specifies the access control rules.
     *
     * This method is used by the 'accessControl' filter.
     *
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array(
                'allow' => true,  // allow all users to perform 'index' and 'view' actions
                'actions' => array('miniJson', 'json', 'exists', 'GetMetaPostID', 'GetVersions'),
                'users' => array('*'),
            ),
            array(
                'allow' => true,
                'actions' => array(
                    'StoreData',
                    'GetData',
                ),
                'users' => array('@'), // allow authenticated user
            ),
            array(
                'deny' => true,
                'users' => array('*'),  // deny all other users
            ),
        );
    }

    /**
     * Fetch the minified version of an Rhythm.
     *
     * @return void
     */
    public function actionMiniJson() {
        $json = Rhythm::getJSON(
            2,
            Yii::app()->params['host'],
            $this->username,
            $_GET['rhythm'],
            $this->model->extra->version->major,
            $this->model->extra->version->minor,
            $this->model->extra->version->patch
        );

        echo JSON::encode($json);
    }

    /**
     * Fetch the full version of an Rhythm and display as JSON.
     *
     * @param string $g_rhythm The Rhythm name.
     * @param string $g_major The major version number of the Rhythm.
     * @param string $g_patch The patch version of the version number.
     *
     * @return void
     */
    public function actionJson($g_rhythm, $g_major, $g_minor, $g_patch) {

        // Special case for testing that if the data is missing an error bubbles up to the user.
        if ($g_rhythm === "test missing sort") {
            echo JSON::encode(array());
        }

        $json = Rhythm::getJSON(
            1,
            Yii::app()->params['host'],
            $this->username,
            $g_rhythm,
            $this->model->extra->version->major,
            $this->model->extra->version->minor,
            $this->model->extra->version->patch
        );
        echo JSON::encode($json);
    }

    /**
     * Fetch the meta post
     *
     * @param string $g_rhythm The Rhythm name.
     * @param string $g_major The major version number of the Rhythm.
     * @param string $g_minor The minor version number of the Rhythm.
     * @param string $g_patch The patch version of the version number.
     *
     * @return void
     */
    public function actionGetMetaPostID($g_rhythm, $g_major, $g_minor, $g_patch) {
        $rhythm = array(
            'domain' => Yii::app()->params['host'],
            'username' => $this->username,
            'name' => $g_rhythm,
            'version' => array(
                'major' => $g_major,
                'minor' => $g_minor,
                'patch' => $g_patch,
            )
        );
        $rhythm_name_form = new RhythmNameForm($rhythm);
        $rhythm_name_form->rhythm = $rhythm;
        $json = array();
        if (Rhythm::validate() === false) {
            $json['error'] = JSONHelper::convertYiiModelErrortoString(Rhythm::getErrors());
        } else {
            $meta_post_id = RhythmExtra::getMetaPostId(Rhythm::getRhythmExtraId());
            if ($meta_post_id === false) {
                $json['error'] = 'Meta post id not found.';
            } else {
                $json['meta_post_id'] = $meta_post_id;
            }
        }
        echo JSON::encode($json);
    }


    /**
     * Fetches a rhythm extra id when passed the parts of a rhythm name.
     *
     * @param string $domain The username of the rhythm.
     * @param string $username The username of the rhythm.
     * @param string $name The name of the rhythm.
     * @param string $major The major version number of the rhythm.
     * @param string $minor The minor version number of the rhythm.
     * @param string $patch The patch version number of the rhythm.
     *
     * @return integer The rhythms extra id.
     */
    private function getRhythmExtraIdFromRhythmNameParts($username, $name, $major, $minor, $patch) {
        $rhythm_name_form = new RhythmNameForm();
        $rhythm_name_form->makeRhythmObject(
            Yii::app()->params['host'],
            $username,
            $name,
            $major,
            $minor,
            $patch
        );
        $rhythm_valid = $rhythm_name_form->validate();
        if ($rhythm_valid === false) {
            $rhythm_error =  ErrorHelper::model($rhythm_name_form->getErrors(), '<br />');
            throw new CHttpException(400, 'Bad Request. rhythm does not exist : ' . $rhythm_error);
        }
        $rhythm_extra_id = $rhythm_name_form->getRhythmExtraId();
        return $rhythm_extra_id;
    }

    /**
     * Checks if an Rhythm exists.
     *
     * May be an Rhythm from another site, in which case it should be fetched and cached
     * echos JSON response.
     *
     * @return void
     */
    public function actionExists() {
        if (isset($_GET['rhythm_url']) === false) {
            throw new CHttpException(400, 'Bad Request.');
        }

        $rhythm_id = Rhythm::getIDFromUrl($_GET['rhythm_url']);

        $valid = true;
        if (is_numeric($rhythm_id) === false) {
            $valid = false;
        }

        // Return JSON
        $json_array = array(
            "exists" => $valid,
        );
        echo JSON::encode($json_array);

    }

    /**
     * Enables a Rhythm to store temporary data between uses in the users data store.
     *
     * Doesn't use the default mrhythm model so that a remote one can be fetched if needed.
     *
     * @param string $g_rhythm The name of the rhythm data is being stored for.
     * @param string $g_major The major version number of the rhythm data is being stored for.
     * @param string $g_minor The minor version number of the rhythm data is being stored for.
     * @param string $g_patch The patch version number of the rhythm data is being stored for.
     * @param string $p_rhythm_data A JSON string containing the data that a rhythm is storing for a user.
     *
     * @return void
     */
    public function actionStoreData($g_rhythm, $g_major, $g_minor, $g_patch, $p_rhythm_data) {
        $rhythm = Rhythm::getByName($this->user_id, $g_rhythm, $g_major, $g_minor, $g_patch);
        if (isset($rhythm) === false) {
            // !!! fetch the rythem from external domain.
             throw new CHttpException(400, 'Bad Request. rhythm does not exist');
        }

        if (strlen($p_rhythm_data) > 10000) {
            throw new CHttpException(400, 'Bad Request. rhythm_data must be less than 10000 characters long.');
        }

        RhythmUserData::storeData($rhythm->extra->rhythm_extra_id, Yii::app()->user->getId(), $p_rhythm_data);

        $json_array = array(
            "success" => true,
        );
        echo JSON::encode($json_array);
    }


    /**
     * Enables a Rhythm to fetch temporarily stored data between uses in the users data store.
     *
     * Sends the stored JSON string or 'false'.
     * Doesn't use the default mrhythm model so that a remote one can be fetched if needed.
     *
     * @param string $g_rhythm The name of the rhythm data is being stored for.
     * @param string $g_major The major version number of the rhythm data is being stored for.
     * @param string $g_minor The minor version number of the rhythm data is being stored for.
     * @param string $g_patch The patch version number of the rhythm data is being stored for.
     *
     * @return void
     */
    public function actionGetData($g_rhythm, $g_major, $g_minor, $g_patch) {
        $rhythm = Rhythm::getByName($this->user_id, $g_rhythm, $g_major, $g_minor, $g_patch);
        if (isset($rhythm) === false) {
            // !!! fetch the rythem from external domain.
             throw new CHttpException(400, 'Bad Request. rhythm does not exist');
        }
        $data = RhythmUserData::getData($rhythm->extra->rhythm_extra_id, Yii::app()->user->getId());

        $json_array = array(
            "data" => $data,
        );
        echo JSON::encode($json_array);
    }

    /**
     * Fetch the available versions for this filter.
     *
     * @param string $g_user The username for this rhythm.
     * @param string $g_rhythm The name for this rhythm.
     * @param string $g_major The major version number for this rhythm.
     * @param string $g_minor The minor version number for this rhythm.
     * @param string $g_patch The patch version number for this rhythm.
     *
     * @return void
     */
    public function actionGetVersions($g_user, $g_rhythm, $g_major, $g_minor, $g_patch) {
        $rhythm_name_form = new RhythmNameForm;
        $rhythm_name_form->makeRhythmObject(
            Yii::app()->params['host'],
            $g_user,
            $g_rhythm,
            $g_major,
            $g_minor,
            $g_patch
        );
        $rhythm_valid = $rhythm_name_form->validate();
        if ($rhythm_valid === true) {
            $rhythm_name = $rhythm_name_form->getRhythmObject();
            $versions = Rhythm::getVersions($rhythm_name['name'], $rhythm_name['domain'], $rhythm_name['username']);
            $json_versions = array();
            foreach ($versions as $version) {
                $json_versions[$version] = $version;
            }
        } else {
            $error = ErrorHelper::model($rhythm_name_form->getErrors(), '<br />');
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
            $json['versions'] = $json_versions;
        }
        echo JSON::encode($json);
    }

}

?>