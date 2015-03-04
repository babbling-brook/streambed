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
                'application.filters.UsernameFilter'
            ),
            array(            // Must run before the remaining filters. Loads the model
                'application.filters.VersionFilter
                    +view,
                     update,
                     AddParameter,
                     RemoveParameter
                     UpdateParameter',
                "data_type" => "rhythm",
                "version_type" => "rhythm",
            ),
            array(
                'application.filters.PrivateStatusFilter + view'
            ),
            array(
                'application.filters.OwnerFilter + update, delete'
            ),
            array(            // Must run before the remaining filters. Loads the model
                'application.filters.VersionRedirectFilter + view, update',
                "data_type" => "rhythm",
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
                'actions' => array('index', 'view', 'Versions'),
                'users' => array('*'),
            ),
            array(
                'allow' => true,
                'actions' => array(
                    'Create',
                    'Update',
                    'Delete',
                    'AjaxPost',
                    'ChangeStatus',
                    'AddParameter',
                    'RemoveParameter',
                    'UpdateParameter',
                    'UpdateJSON',
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
     * Presents a list of Rhythms owned by a user.
     *
     * If the owner is viewing, then they see an editable list,
     * with links to view, mini, full, edit, publish, depreciate, duplicate
     * If the public is viewing then they see a list of public/depreciated, with links to view, mini and full
     *
     * @param Rhythm $g_rhythm An rhythm model instance.
     * @param string $g_m A message for display to the user.
     *                    Used to inform the user of actions taken when they are rediredcted to this page.
     *
     * @return void
     */
    public function actionIndex(array $g_rhythm=null, $g_m="") {
        $model = new Rhythm('search');
        $model->unsetAttributes();  // clear any default values

        if ($g_rhythm !== null) {
            $model->attributes = $g_rhythm;
        }

        $this->render(
            '/Client/Page/ManageRhythm/List',
            array(
                'model' => $model,
                'message' => $g_m,
            )
        );

    }

    /**
     *  View a rhythms code.
     *
     * @return void
     */
    public function actionView() {

        if ($this->model->extra->status->value === 'private'
            && intval($this->model->user->user_id) !== Yii::app()->user->getId()
        ) {
            throw new CHttpException(404, 'Stream not found.');
        }

        $this->render(
            '/Client/Page/ManageRhythm/View',
            array(
                'model' => $this->model,
                'owned' => $this->user_id === Yii::app()->user->getId(),
            )
        );
    }

    /**
     * Ajax request to create a new rhythm.
     *
     * @param string $p_name The name of the new rhythm.
     * @param string $p_description The description of the new rhythm.
     * @param string $p_category The category of the new rhythm.
     * @param string $p_javascript The javascript of the new rhythm.
     */
    public function actionMake($p_name, $p_description, $p_category, $p_javascript) {
        $json = array();
        $json['success'] = true;

        $model= new Rhythm;
        $model->extra = new RhythmExtra;

        $model->name = $p_name;
        $model->extra->description = $p_description;
        $model->extra->full = $p_javascript;
        $model->extra->status_id = StatusHelper::getID("private");

        // Validate
        $model->setScenario('create');
        $model->validate();
        $model->extra->rhythm_cat_id = $p_category;
        $model->extra->validate();
        if ($model->extra->getErrors() === array() && $model->getErrors() === array()) {
            // Create a new version and attatch
            $model->extra->version_id = Version::insertNew(LookupHelper::getID("version.type", "rhythm"));

            Yii::import('application.components.jsmin');
            $model->extra->mini = JSMin::minify($p_javascript);

            // Save
            if ($model->validate() === true) {
                $model->save();
                $model->extra->rhythm_id = $model->rhythm_id;
                $model->extra->save();
                // Update version type and family id
                Version::updateType(
                    $model->extra->version_id,
                    LookupHelper::getid("version.type", "rhythm"),
                    $model->rhythm_id
                );

                // Create a meta post for this Rhythm.
                Rhythm::createMetaPost($model);


            } else {
                throw new Exception('Something went wrong when inserting the rhythm into the database.');
            }
        } else {
            $json['success'] = false;

            $errors1 = $model->getErrors();
            $errors2 = $model->extra->getErrors();
            $json['errors'] = $this->generateErrorsForJSON($errors1, $errors2);
        }

        echo JSON::encode($json);
    }

    /**
     * Generates the error array for create and update requests.
     *
     * @param array $errors1 The errors from the rhythm model.
     * @param array $errors2 The errors from the rhythm extra model.
     *
     * @return array An array of errors formatted for JSON.
     */
    private function generateErrorsForJSON($errors1, $errors2) {
        $final_errors = array();
        if (isset($errors1['name'][0]) === true) {
            $final_errors['name'] = $errors1['name'][0];
        }
        if (isset($errors2['description'][0]) === true) {
            $final_errors['description'] = $errors2['description'][0];
        }
        if (isset($errors2['rhythm_cat_id'][0]) === true) {
            $final_errors['category'] = $errors2['rhythm_cat_id'][0];
        }
        if (isset($errors2['full'][0]) === true) {
            $final_errors['javascript'] = $errors2['full'][0];
        }
        return $final_errors;
    }

    /**
     * Create a new Rhythm.
     *
     * @return void
     */
    public function actionCreate() {
        $model= new Rhythm;
        $model->extra = new RhythmExtra;

        $this->render(
            '/Client/Page/ManageRhythm/Create', array(
                'model' => $model,
            )
        );
    }


    /** WAITING TO BE MOVED TO THE SCIENTIA DOMAIN **/

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
     * Handles requests to change the status of an rhythm by its owner.
     *
     * @param string $g_user The username of the rhythm whos status is being changed.
     * @param string $g_rhythm The name of the rhythm whos status is being changed.
     * @param string $g_major The major version number of the rhythm whos status is being changed.
     * @param string $g_minor The minor version number of the rhythm whos status is being changed.
     * @param string $g_patch The patch version number of the rhythm whos status is being changed.
     * @param string $p_action What kind of request is this. Valid values are 'publish', 'deprecate' and 'delete'.
     *
     * @return void
     */
    public function actionChangeStatus($g_user, $g_rhythm, $g_major, $g_minor, $g_patch, $p_action) {
        $rhythm_extra_id = $this->getRhythmExtraIdFromRhythmNameParts(
            $g_user,
            $g_rhythm,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($rhythm_extra_id);

        $is_deletable = false;
        $status = RhythmExtra::getStatus($rhythm_extra_id);
        if ($status === 'private') {
            $is_deletable = true;
        }

        if ($p_action !== 'publish' && $p_action !== 'deprecate' && $p_action !== 'delete' && $p_action !== 'revert') {
            $error = 'Action is not a valid status request action. Should be "publish", "deprecate" or "delete".'
                . ' Given ' . $p_action;
        } else {
            switch ($p_action) {
                case 'publish':
                    Rhythm::updateStatus($rhythm_extra_id, StatusHelper::getID('public'));
                    break;

                case 'delete':
                    $rhythm = Rhythm::getByID($rhythm_extra_id);
                    if ($is_deletable === false) {
                        $error = 'Only private rhythms can be deleted.';
                    } else {
                        RhythmMulti::deleteByRhythmExtraId($rhythm_extra_id);
                    }
                    break;

                case 'deprecate':
                    if ($status !== 'public') {
                        $error = 'Only public rhythms can be deprecated.';
                    } else {
                        Rhythm::updateStatus($rhythm_extra_id, StatusHelper::getID('deprecated'));
                    }
                    break;

                case 'revert':
                    if ($status === 'private') {
                        $error = 'Private rhythms cannot be revertrd.';
                    } else if ($is_deletable === false) {
                            $error = 'Rhythm cannot be reverted to draft status. It contains posts by other users.'
                                . ' Create a new version instead.';
                    } else {
                        Rhythm::updateStatus($rhythm_extra_id, StatusHelper::getID('private'));
                    }
                    break;
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['error'] = $error;
            $json['success'] = false;
        } else {
            $json['success'] = true;
            $json['deletable'] = $is_deletable;
        }
        echo JSON::encode($json);
    }

    /**
     * Verifies that the logged on user  owns this rhythm.
     *
     * @param type $rhythm_extra_id
     *
     * @return void
     */
    private function throwErrorIfNotOwner($rhythm_extra_id) {
        $rhythm_owner_id = RhythmExtra::getOwnerID($rhythm_extra_id);
        if (intval($rhythm_owner_id) !== Yii::app()->user->getId()) {
            throw new CHttpException(403, 'Access Denied.');
        }
    }

    /**
     * Edit an Rhythm.
     *
     * @return void
     */
    public function actionUpdate() {
        //
        $version_error = false;

        if (isset($_POST['duplicate']) === true) {
            $this->model = $this->duplicate($this->model);
            return;
        } else if (isset($_POST['new_version']) === true) {
            $this->newVersion($this->model, $_POST['new_version']);
            return;
            $version_error = true;
        }

        $versions = Version::getNextVersions(
            $this->model->extra->version->family_id,
            LookupHelper::getID("version.type", "rhythm"),
            $this->model->extra->version->major,
            $this->model->extra->version->minor,
            $this->model->extra->version->patch,
            StatusHelper::getId("private") === $this->model->extra->status_id ? true : false // Post current version?
        );

        $this->render(
            '/Client/Page/ManageRhythm/Update',
            array(
                'model' => $this->model,
                'versions' => $versions,
                'version_error' => $version_error,
            )
        );
    }


    /**
     * Update an Rhythm.
     *
     *
     * @return Rhythm model with error messages.
     */
    public function actionUpdateJSON($g_user, $g_rhythm, $g_major, $g_minor, $g_patch,
        $p_description, $p_category, $p_javascript
    ) {
        $json = array();

        $rhythm_name_form = new RhythmNameForm;
        $rhythm_name_form->makeRhythmObject(HOST, $g_user, $g_rhythm, $g_major, $g_minor, $g_patch);
        $valid = $rhythm_name_form->validate();
        if ($valid === true) {
            $rhythm_extra_id = $rhythm_name_form->getRhythmExtraId();
            $rhythm_extra_model = $rhythm_name_form->getRhythmModel();

            if (Rhythm::checkOwner($rhythm_extra_id, Yii::app()->user->getId()) === false) {
                throw new CHttpException(403, 'The requested page is forbidden.');
            }

            // Check is private
            if ($rhythm_extra_model->status_id !== (string)StatusHelper::getID("private")) {
                throw new CHttpException(403, 'The requested page is forbidden.');
            }

            $rhythm_extra_model->description = $p_description;
            $rhythm_extra_model->full = $p_javascript;

            Yii::import('application.components.jsmin');
            $rhythm_extra_model->mini = JSMin::minify($p_javascript);

            $rhythm_extra_model->rhythm_cat_id = $p_category;
            if ($rhythm_extra_model->validate() === false) {
                $json['success'] = false;
                $errors2 = $rhythm_extra_model->getErrors();
                $json['errors'] = $this->generateErrorsForJSON(array(), $errors2);
            } else {
                $rhythm_extra_model->save();
                $json['success'] = true;
            }

        } else {
            throw new CHttpException(404, 'The rhythm being updated has not been found.');
        }

        echo JSON::encode($json);
    }

    /**
     * Duplicates an existing stream with a new name.
     *
     * @param Stream $model The model from the database that matches this postback.
     *
     * @return Rhythm
     */
    protected function duplicate($model) {
        // Validate new name
        $model->name = $_POST['duplicate_name'];
        $model->setScenario("duplicate");
        if ($model->validate() === false) {
            $json = array(
                "error" => 'This name already exists.',
            );
            echo JSON::encode($json);
            return;
        }

        $model->extra->version_id = Version::insertNew(LookupHelper::getID("version.type", "rhythm"));
        $model->extra->version->version_id = $model->extra->version_id;
        $model->extra->version->major = 0;
        $model->extra->version->minor = 0;
        $model->extra->version->patch = 0;
        $this->version_string = "0/0/0";

        $original_rhythm_extra_id = $model->extra->rhythm_extra_id;
        $model->extra->rhythm_extra_id = null;
        $model->rhythm_id = null;
        $model->extra->status_id = StatusHelper::getID("private");
        $model->isNewRecord = true;
        $model->extra->isNewRecord = true;
        $model->extra->date_created = null;

        $model->extra->validate();
        $model->validate();
        if ($model->extra->hasErrors() === true || $model->hasErrors() === true) {
            $json = array(
                "error" => 'An unknown error occured when saving the Rhythm.',
            );
            echo JSON::encode($json);
            return;
        }

        $model->save();
        $model->extra->rhythm_id = $model->rhythm_id;
        $model->extra->save();

        Version::updateType(
            $model->extra->version_id,
            LookupHelper::getid("version.type", "rhythm"),
            $model->rhythm_id
        );

        RhythmParam::duplicateParams($original_rhythm_extra_id, $model->extra->rhythm_extra_id);

        Rhythm::createMetaPost($model);

        // return redirect link
        $json = array(
            "url" => '/' . $this->username .'/rhythm/'
                . $model->name . '/' . $this->version_string . '/update',
        );
        echo JSON::encode($json);

    }

    /**
     * Create a new version of an existing stream.
     *
     * @param Stream $model The model from the database that matches this postback.
     * @param string $new_version The new version to create
     *
     * @return Rhythm
     */
    protected function newVersion($model, $new_version) {
        // Do nothing if this is the same version
        if ($new_version === "No change") {
            return $model;
        }

        $family_id = $model->rhythm_id;
        $is_next_version_valid = Version::checkValidNext(
            $family_id,
            LookupHelper::getID("version.type", "rhythm"),
            $new_version
        );
        if ($is_next_version_valid === true) {
            $model->extra->version_id = Version::insertNewFromString(
                $family_id,
                LookupHelper::getID("version.type", "rhythm"),
                $new_version
            );
        } else {
            throw new CHttpException(400, 'Bad Request.');
        }
        $this->version_string = $new_version;

        $original_rhythm_extra_id = $model->extra->rhythm_extra_id;
        $model->extra->rhythm_extra_id = null;
        $model->extra->status_id = StatusHelper::getID("private");
        $model->extra->isNewRecord = true;
        $model->extra->date_created = null;

        $model->extra->validate();
        if ($model->extra->hasErrors() === true) {
            throw new Exception("Error saving new version to DB.");
        }

        $model->extra->save();

        Version::updateType(
            $model->extra->version_id,
            LookupHelper::getid("version.type", "rhythm"),
            $model->rhythm_id
        );

        $version_parts = explode("/", $new_version);
        $model->extra->version->major = $version_parts[0];
        $model->extra->version->minor = $version_parts[1];
        $model->extra->version->patch = $version_parts[2];

        RhythmParam::duplicateParams($original_rhythm_extra_id, $model->extra->rhythm_extra_id);

        Rhythm::createMetaPost($model);

        // return redirect link
        $json = array(
            'url' => '/' . $this->username .'/rhythm/'
                . $model->name . '/' . $this->version_string . '/update',
        );
        echo JSON::encode($json);
    }


    /**
     * Performs Ajax requests on Rhythm pages.
     *
     * Include 'publish', 'deprecate', and 'delete' requests.
     *
     * @return void
     */
    public function actionAjaxPost() {
        if (isset($_POST['rhythm_extra_id']) === false) {
            throw new CHttpException(400, 'Bad Request.');
        }

        if (is_numeric($_POST['rhythm_extra_id']) === false) {
            throw new CHttpException(400, 'Bad Request.');
        }

        if (isset($_POST['action']) === false) {
            throw new CHttpException(400, 'Bad Request.');
        }

        if ($_POST['action'] !== "publish" && $_POST['action'] !== "deprecate" && $_POST['action'] !== "delete") {
            throw new CHttpException(400, 'Bad Request.');
        }

        $user_id = Yii::app()->user->getId();
        if (Rhythm::checkOwner($_POST['rhythm_extra_id'], $user_id) === false) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        switch ($_POST['action']) {
            case 'publish':
                Rhythm::updateStatus($_POST['rhythm_extra_id'], StatusHelper::getID("public"));
                break;

            case 'delete':
                // Check is a private rhythm
                $rhythm = Rhythm::getByID($_POST['rhythm_extra_id']);
                if ($rhythm->status_id !== (string)StatusHelper::getID("private")) {
                    throw new CHttpException(403, 'The requested page is forbidden.');
                }

                $name = $rhythm->rhythm->name;
                RhythmMulti::deleteByRhythmExtraId($_POST['rhythm_extra_id']);
                break;

            case 'deprecate':
                // Check is public
                $rhythm = Rhythm::getByID($_POST['rhythm_extra_id']);
                if ($rhythm->status_id !== (string)StatusHelper::getID("public")) {
                    throw new CHttpException(403, 'The requested page is forbidden.');
                }

                Rhythm::updateStatus($_POST['rhythm_extra_id'], StatusHelper::getID("deprecated"));
                break;
        }

        $json_name = "";
        if (isset($name) === true) {
            $json_name = ', "name" : "' . $name . '"';
        }

        $json_array = array();
        $json_array['success'] = true;
        $json_array['rhythm_extra_id'] = $_POST['rhythm_extra_id'];
        $json_array['user'] = $this->username . '"'. $json_name;

        echo JSON::encode($json_array);
    }

    /**
     * Allows a rhythm owner to add a new client paramater to the rhythm.
     *
     * @param string $p_name The name of the new parameter to add.
     * @param string $p_hint The hint text to display alongside this parameter.
     *
     * @return void
     */
    public function actionAddParameter($p_name, $p_hint) {
        if (Rhythm::checkOwner($this->model->extra->rhythm_extra_id, Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        $result = RhythmParam::createParameter($this->model->extra->rhythm_extra_id, $p_name, $p_hint);

        $json_array = array();
        if ($result !== true) {
            $json_array['errors'] = JSONHelper::convertYiiModelError($result);
        } else {
            $json_array['success'] = true;
        }

        echo JSON::encode($json_array);
    }

    /**
     * Allows a rhythm owner to remove a client paramater from a the rhythm.
     *
     * @param string $p_name The name of the new parameter to add.
     *
     * @return void
     */
    public function actionRemoveParameter($p_name) {

        if (Rhythm::checkOwner($this->model->extra->rhythm_extra_id, Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        RhythmParam::removeParameterByName($this->model->extra->rhythm_extra_id, $p_name);

        $json_array = array('success' => true);
        echo JSON::encode($json_array);
    }

    /**
     * Allows a rhythm owner to remove a client paramater from a the rhythm.
     *
     * @param string $p_original_name The original name of the parameter to update.
     * @param string $p_name The new name of the parameter to update.
     * @param string $p_hint The hint text to display alongside this parameter.
     *
     * @return void
     */
    public function actionUpdateParameter($p_original_name, $p_name, $p_hint) {

        if (Rhythm::checkOwner($this->model->extra->rhythm_extra_id, Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        $result = RhythmParam::updateParameterByName(
            $this->model->extra->rhythm_extra_id,
            $p_original_name,
            $p_name,
            $p_hint
        );

        $json_array = array();
        if ($result !== true) {
            $json_array['errors'] = JSONHelper::convertYiiModelError($result);
        } else {
            $json_array['success'] = true;
        }
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
    public function actionVersions($g_user, $g_rhythm, $g_major, $g_minor, $g_patch) {
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
            $versions = Rhythm::getPartialVersions(
                $rhythm_name['name'],
                $rhythm_name['domain'],
                $rhythm_name['username']
            );
        } else {
            $error = ErrorHelper::model($rhythm_name_form->getErrors(), '<br />');
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
            $json['versions'] = $versions;
        }
        echo JSON::encode($json);
    }

    /**
     * Generate the operations menu for displaying on Rhythm pages.
     *
     * @param string $active Name of the calling action, so that it can be styled as current on the menu.
     *
     * @return array
     */
    protected function operationsMenu($active="") {
        if ($this->username === Yii::app()->user->getName()) {
            $create = array(
                "label" => "Create",
                "url" => "/" . $this->username ."/rhythms/create",
                "active" => $active === "create",
                "linkOptions" => array(
                    "title" => "Create a new Rhythm",
                )
            );
            if ($active === "view" || $active === "update" ) {
                $update =  array(
                    "label" => "Update",
                    "url" => "/" . $this->username ."/rhythm" . $this->version_link . '/update',
                    "active" => $active === "update",
                    "linkOptions" => array(
                        "title" => "Update this Rhythm",
                    ),
                    "itemOptions" => array(
                        'id' => 'sidebar_update_rhythm',
                    )
                );
            }
        }



        $index = array(
            "label" => "List All",
            "url" => "/" . $this->username ."/rhythms/",
            "active" => $active === "view",
            "linkOptions" => array(
                "title" => "View a list of all Rhythms by this user",
            )
        );

        if ($active === "view" || $active === "update" ) {
            $view =  array(
                "label" => "View",
                "url" => "/" . $this->username ."/rhythm" . $this->version_link,
                "active" => $active === "view",
                "linkOptions" => array(
                    "title" => "View this Rhythm",
                ),
                "itemOptions" => array(
                    'id' => 'sidebar_view_rhythm',
                )
            );
        }

        $menu_array = array();
        if (isset($index) === true) {
            array_push($menu_array, $index);
        }
        if (isset($update) === true) {
            array_push($menu_array, $update);
        }
        if (isset($view) === true) {
            array_push($menu_array, $view);
        }
        if (isset($create) === true) {
            array_push($menu_array, $create);
        }

        return $menu_array;
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     *
     * If the data model is not found, an HTTP exception will be raised.
     *
     * @return Stream Model.
     */
    protected function loadModel() {
        if ($this->model === null) {
            if (isset($this->rhythm_id) === true) {
                $this->model=Rhythm::getForUser($this->rhythm_id, $this->user_id);
            }
            if ($this->model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
            }
        }
        return $this->model;
    }
}

?>