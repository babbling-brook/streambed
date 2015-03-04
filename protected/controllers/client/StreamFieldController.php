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
 * Creation and management of fields that are attatched to an stream.
 * Viewed via an Iframe in the Stream management area.
 *
 * @package PHP_Controllers
 * @fixme configure etags for all caching elements. http://en.wikipedia.org/wiki/HTTP_ETag
 */
class StreamFieldController extends Controller
{

    /**
     * The username of the user who owns the stream which owns these fields.
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
            'Owner + delete, title, mainvalue', // @fixme moveup and move down should also check the owner.
            'FieldID + delete, movedown, moveup',
            'PostStreamID + create',
        );
    }


    /**
     * Validate user.
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return void
     */
    public function filterUser($filterChain) {
        if (isset($_GET['user']) === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        $this->username = $_GET['user'];
        $filterChain->run();
    }

    /**
     * Checks if posted stream id is valid.
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return void
     */
    public function filterPostStreamID($filterChain) {
        if (isset($_POST['stream_extra_id']) === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        if (is_numeric($_POST['stream_extra_id']) === false) {
            throw new CHttpException(400, 'Bad Request.');
        }

        //check stream id is owned by this user
        if (StreamBedMulti::checkOwnerExtra($_POST['stream_extra_id'], Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        $filterChain->run();
    }

    /**
     * Check that the posted field ID is valid.
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return void
     */
    public function filterFieldID($filterChain) {
        if (isset($_POST['field_id']) === false) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        if (is_numeric($_POST['field_id']) === false) {
            throw new CHttpException(400, 'Bad Request.');
        }

        // Check the user owns this field
        if (StreamField::checkOwner($_POST['field_id'], Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        $filterChain->run();
    }

    /**
     * Checks that the user requesting access is the owner.
     *
     * @param FilterChain $filterChain The chain of filters that are being applied before an action is called.
     *
     * @return boolean
     */
    public function filterOwner($filterChain) {
        if (strtolower($this->username) === Yii::app()->user->getName()) {
            $filterChain->run();
        } else {
            return false;
        }
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
                    'Delete',
                    'Update',
                    'TypeChanged',
                    'ValueTypeChanged',
                    'Title',
                    'Mainvalue',
                    'Create',
                    'AddListItem',
                    'AddValueListItem',
                    'RemoveListItem',
                    'RemoveValueListItem',
                    'WhoCanTakeChanged',
                    'Moveup',
                    'Movedown',
                ),
                'users' => array('@'), // allow authenticated user
            ),
            array(
                'deny',
                'users' => array('*'),  // deny all other users
            ),
        );
    }

    /**
     * Delete a field.
     *
     * @return void
     */
    public function actionDelete() {

        $this->checkKindFromFieldID($_POST['field_id']);

        // Delete the field
        if (StreamBedMulti::deleteStreamField($_POST['field_id']) === false) {
            throw new CHttpException(400, 'Bad Request.');
        }

        // Return JSON
        $json_array = array(
            "id" => $_POST['field_id'],
        );
        echo (JSON::encode($json_array));
    }

    /**
     * Update a stream field.
     *
     * @param string $p_field_id The id of this field.
     * @param string $p_type The type of field being updated.
     * @param string $p_label The fields label.
     * @param string $p_max_size The maximum allowed length of the field.
     * @param string $p_required Is the field required. Valid values are 'true' and 'false'.
     * @param string $p_filter The stream_regex_id of the filter or blank for none, or 'more' for custom.
     * @param string $p_regex The custom regex for the filter.
     * @param string $p_regex_error The error message to display if the custom regex fails.
     * @param string $p_checkbox_default The default status of a checkbox field. Valid values are 'true' and 'false'.
     * @param string $p_select_qty_min The minimum number of items on a list that must be selected.
     * @param string $p_select_qty_max The maximum number of items on a list that must be selected.
     * @param string $p_value_type The type of value that this field has, if it is a value field.
     * @param string $p_value_option The method by which max and min values for values are defined.
     * @param string $p_value_min The minimum value that a take can enter in a value field.
     * @param string $p_value_max The maximum value that a take can enter in a value field.
     * @param string $p_value_rhythm The Rhythm that defines max and min values for a taker.
     *
     * @return void
     */
    public function actionUpdate($p_field_id, $p_type, $p_label, $p_max_size='',
        $p_required='false', $p_filter='', $p_regex='', $p_regex_error='', $p_checkbox_default='false',
        $p_text_type=null, $p_select_qty_min=null, $p_select_qty_max=null,
        $p_value_type=null, $p_value_option=null, $p_value_min=null, $p_value_max=null, $p_value_rhythm=null
    ) {
        $this->checkFieldId($p_field_id);
        $this->checkOwnership($p_field_id);
        $this->checkStatusValid($p_field_id);
        $this->checkTypeValid($p_type);
        $this->checkKindFromFieldID($p_field_id);

        // Assign the field data to the correct field type.
        $model = StreamField::getField($p_field_id);
        $model->stream_field_id = $p_field_id;
        $model->label = $p_label;
        switch($p_type) {
            case 'textbox':
                $model->setScenario('textbox_update');
                $model->max_size = $p_max_size;
                $model->required = $p_required;
                $model->text_type = $p_text_type;

                if (ctype_digit($p_filter) === true) {
                    $regex_model = StreamRegex::model()->findByPk($p_filter);
                    if ($regex_model === false) {
                        throw new CHttpException(400, 'Bad Data. Filter id (stream_regex_id) not found.');
                    }
                    $model->regex = $regex_model->regex;
                    $model->regex_error = $regex_model->error;
                } else if ($p_filter === 'more') {
                    $model->regex = $p_regex;
                    $model->regex_error = $p_regex_error;
                } else {
                    $model->regex = $p_regex;
                    $model->regex_error = $p_regex_error;
                }
                break;

            case 'link':
                $model->setScenario('link_update');
                $model->required = $p_required;
                break;

            case 'checkbox':
                $model->setScenario('checkbox_update');
                $model->checkbox_default = $p_checkbox_default;
                break;

            case 'list':
                $model->setScenario('list_update');
                $model->select_qty_min = $p_select_qty_min;
                $model->select_qty_max = $p_select_qty_max;
                break;

            case 'openlist':
                $model->setScenario('openlist_update');
                $model->select_qty_min = $p_select_qty_min;
                $model->select_qty_max = $p_select_qty_max;
                break;

            case 'value':
                $model->setScenario('value_update');
                $model->value_type = $p_value_type;
                $model->value_options = $p_value_option;
                $model->value_max = $p_value_max;
                $model->value_min = $p_value_min;
                $model->rhythm_check_url = $p_value_rhythm;
                break;
        }

        // Save the data.
        if ($model->validate() === true) {
            $model->save();
            $json_array = array('success' => true);
        } else {
            $json_array = array(
                'success' => false,
                'errors' => JSONHelper::convertYiiModelError($model->getErrors()),
            );
        }
        echo (JSON::encode($json_array));
    }

    /**
     * Checks that a type is valid.
     *
     * @param string $type The type to check.
     *
     * @return void
     */
    private function checkTypeValid($type) {
        if (in_array($type, array('textbox', 'link', 'checkbox', 'list', 'openlist', 'value')) === false) {
            throw new CHttpException(400, 'Bad Data. The "type" is not valid.');
        }
    }

    /**
     * Throw an error if field is not owned by the logged on user.
     *
     * @param type $field_id The id of the field we are testing ownership of.
     *
     * @return void
     */
    private function checkOwnership($field_id) {
        if (StreamField::checkOwner($field_id, Yii::app()->user->getId()) === false) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }
    }

    /**
     * Checks that the status of stream is in a valid state (private) for its fields to be edited.
     *
     * @param type $field_id The id of the field we are testing ownership of.
     *
     * @return void
     */
    private function checkStatusValid($field_id) {
        if (StreamField::getStreamStatus($field_id) !== 'private') {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }
    }

    /**
     * Throw an error if field id is not an unsigned int.
     *
     * @param type $field_id An stream_field_id.
     *
     * @return void
     */
    private function checkFieldId($field_id) {
        if (ctype_digit($field_id) === false) {
            throw new CHttpException(400, 'Bad Request. field_id is invalid.');
        }
    }

    /**
     * Called when a fields type is changed. Returns the HTML for the new type and resets the data.
     *
     * @param string $p_field_id The id of this field.
     * @param string $p_type The type of field being updated.
     *
     * @return void
     */
    public function actionTypeChanged($p_field_id, $p_type) {

        $this->checkFieldId($p_field_id);
        $this->checkOwnership($p_field_id);
        $this->checkStatusValid($p_field_id);
        $this->checkTypeValid($p_type);
        $this->checkKindFromFieldID($p_field_id);

        $type_id = LookupHelper::getID('stream_field.field_type', $p_type);

        StreamField::updateType($type_id, $p_field_id);

        $model = StreamField::getField($p_field_id);

        $html = $this->renderPartial(
            '/Client/Page/ManageStream/StreamField/' . ucfirst(LookupHelper::getValue($type_id)),
            array(
                'model' => $model,
            ),
            true
        );
        $json_array = array(
            'success' => true,
            'html' => $html,
        );
        echo (JSON::encode($json_array));
    }

    /**
     * Called when a fields type is changed. Returns the HTML for the new type and resets the data.
     *
     * @param string $p_field_id The id of this field.
     * @param string $p_value_id The id of the value type that the field has been updated to.
     *
     * @return void
     */
    public function actionValueTypeChanged($p_field_id, $p_value_id) {

        $this->checkFieldId($p_field_id);
        $this->checkKindFromFieldID($p_field_id);
        $this->checkOwnership($p_field_id);
        $this->checkStatusValid($p_field_id);

        if (LookupHelper::validId("stream_field.value_type", $p_value_id, false) === false) {
            throw new CHttpException(400, 'Bad Request. Value id is not valid.');
        }

        StreamField::updateValueType($p_field_id, $p_value_id);

        $json_array = array(
            'success' => true,
        );
        echo (JSON::encode($json_array));

    }

    /**
     * Create a new field.
     *
     * @return void
     */
    public function actionCreate() {

        $this->checkKindFromStreamExtraID($_POST['stream_extra_id']);

        $model = StreamField::insertNew($_POST['stream_extra_id']);
        $html = $this->renderPartial(
            '/Client/Page/ManageStream/StreamField/_update',
            array(
                'data' => $model,
            ),
            true
        );


        $json_array = array(
            "display_order" => $model->display_order,
            "id" => $model->stream_field_id,
            "html" => $html,
        );
        echo (JSON::encode($json_array));
    }

    /**
     * Adds a new list item for a list field.
     *
     * Responds with JSON object containing the primary key of the new list item or an error.
     *
     * @param string $p_field_id The id of this field.
     * @param string $p_new_list_item The new list item.
     *
     * @return void
     */
    public function actionAddListItem($p_field_id, $p_new_list_item) {

        $this->checkFieldId($p_field_id);
        $this->checkOwnership($p_field_id);
        $this->checkStatusValid($p_field_id);
        $this->checkKindFromFieldID($p_field_id);

        // Check the item is not already present
        $errors = array();
        $list_id = false;
        if (StreamList::doesItemExist($p_new_list_item, $p_field_id) === true) {
            $errors[] = "List value already exists";
        } else {
            $model = StreamList::insertItem($p_new_list_item, $p_field_id);
            if ($model->getErrors() === true) {
                $errors = JSONHelper::convertYiiModelError($model->getErrors());
            } else {
                $list_id = $model->getPrimaryKey();
            }
        }

        // return JSON object with status
        if (count($errors) > 0) {
            $json_array = array(
                'success' => false,
                'errors' => $errors,
            );
        } else {
            $json_array = array(
                'success' => true,
                'list_id' => $list_id,
            );
        }
        echo (JSON::encode($json_array));
    }

    /**
     * Adds a new list item for a take list field.
     *
     * Responds with JSON object containing the take value of the new list item or an error.
     *
     * @param string $p_field_id The id of this field.
     * @param string $p_new_value_list_item The new list item.
     *
     * @return void
     */
    public function actionAddValueListItem($p_field_id, $p_new_value_list_item) {

        $this->checkFieldId($p_field_id);
        $this->checkOwnership($p_field_id);
        $this->checkStatusValid($p_field_id);
        $this->checkKindFromFieldID($p_field_id);

        // Check the item is not already present
        $errors = array();
        $list_id = false;
        $otlb = new TakeValueList;
        if (TakeValueList::rowExists($p_field_id, $p_new_value_list_item) === true) {
            $errors[] = "List value already exists";

        } else {
            $model = TakeValueList::insertNewValue($p_field_id, $p_new_value_list_item);
            if ($model->getErrors() === true) {
                $errors = JSONHelper::convertYiiModelError($model->getErrors());
            }
        }

        // return JSON object with status
        if (count($errors) > 0) {
            $json_array = array(
                'success' => false,
                'errors' => $errors,
            );
        } else {
            $json_array = array(
                'success' => true,
                'value' => $model->value,
                'take_value_list_id' => $model->take_value_list_id,
            );
        }
        echo (JSON::encode($json_array));
    }

    /**
     * Removes a list item for a list field.
     *
     * Responds with JSON object containing a success statment or an error.
     *
     * @param string $p_field_id The id of this field.
     * @param string $p_list_item_to_delete The list item to remove.
     *
     * @return void
     */
    public function actionRemoveListItem($p_field_id, $p_list_item_to_delete) {

        $this->checkFieldId($p_field_id);
        $this->checkOwnership($p_field_id);
        $this->checkStatusValid($p_field_id);
        $this->checkKindFromFieldID($p_field_id);

        // Delete the entry
        StreamList::deleteItem($p_list_item_to_delete, $p_field_id);

        // return JSON object with status
        $json_array = array(
            'success' => true,
        );
        echo (JSON::encode($json_array));
    }

    /**
     * Removes a list item for a value list field.
     *
     * Responds with JSON object containing a success statment or an error.
     *
     * @param string $p_field_id The id of this field.
     * @param string $p_take_value_list_id The id of the list item to remove.
     *
     * @return void
     */
    public function actionRemoveValueListItem($p_field_id, $p_take_value_list_id) {

        $this->checkFieldId($p_field_id);
        $this->checkOwnership($p_field_id);
        $this->checkStatusValid($p_field_id);
        $this->checkKindFromFieldID($p_field_id);

        // Delete the entry
        TakeValueList::deleteRow($p_take_value_list_id, $p_field_id);

        // return JSON object with status
        $json_array = array(
            'success' => true,
        );
        echo (JSON::encode($json_array));
    }

    /**
     * Changes the value of who can take a value field.
     *
     * @param string $p_field_id The id of the field whose value is being changed.
     * @param string $p_who_can_take The value of who can take this field.
     *
     * @return void
     */
    public function actionWhoCanTakeChanged($p_field_id, $p_who_can_take) {
        $this->checkFieldId($p_field_id);
        $this->checkOwnership($p_field_id);
        $this->checkStatusValid($p_field_id);
        $this->checkKindFromFieldID($p_field_id);

        // The update query has a where clause that forbids the main value field being changed.
        StreamField::updateWhoCanTake($p_field_id, $p_who_can_take);

        // return JSON object with status
        $json_array = array(
            'success' => true,
        );
        echo (JSON::encode($json_array));
    }

    /**
     * Moves a field up in the display order.
     *
     * @return void
     */
    public function actionMoveup() {
        $this->move(-1);
    }

    /**
     * Moves a field up in the display order.
     *
     * @return void
     */
    public function actionMovedown() {
        $this->move(1);
    }

    /**
     * Move display order.
     *
     * Displays a JSON object containg the switched ID.
     *
     * @param integer $direction The amount to move. -1 = move one up. 3 = move 3 down etc.
     *
     * @return void
     */
    protected function move($direction) {
        // Switch display order with filed above. $result will be id of the switched row or false if operation failed
        $result = StreamField::moveDisplayOrder($_POST['field_id'], $direction);

        // If result failed then it can't move and should not have been requested to do so
        if ($result === false) {
            throw new CHttpException(400, 'Bad Request.');
        }

        //return JSON success and the id of the element switched with.
        $json_array = array(
            "switch_id" => $result,
        );
        echo (JSON::encode($json_array));
    }

    /**
     * Checks the kind of stream from the field_id. Throws an error if editing fields is not allowed.
     *
     * @param integer $field_id The id of the field in an stream that is being checked.
     */
    private function checkKindFromFieldID($field_id) {
        $kind_id = StreamField::getKind($field_id);
        if ($kind_id === (string)LookupHelper::getID('stream.kind', 'user')) {
            throw new CHttpException(
                400,
                'Bad Request. Fields for streams with a kind of user are not modifiable.'
            );
        }
    }


    /**
     * Checks the kind of stream from the field_id. Throws an error if editing fields is not allowed.
     *
     * @param integer $stream_extra_id The extra id of the stream being checked.
     */
    private function checkKindFromStreamExtraID($stream_extra_id) {
        $kind_id = StreamBedMulti::getKindFromStreamExtra($stream_extra_id);
        if ($kind_id === (string)LookupHelper::getID('stream.kind', 'user')) {
            throw new CHttpException(
                400,
                'Bad Request. Fields for streams with a kind of user are not modifiable.'
            );
        }
    }

}

?>