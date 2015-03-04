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
 * Used for user JSON data retrieval and posting.
 *
 * @package PHP_Controllers
 */
class UserDataController extends Controller
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
                    'RhythmSearch',
                    'UserSearch',
                ),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(
                    'ClientUser',
                    'StreamRemove',
                    'StreamDescriptions',
                    'StreamNewVersion',
                    'FilterRemove',
                    'GetSelection',
                    'StreamVersions',
                    'FilterNewVersion',
                    'StreamFilterSubscribe',
                    'StreamRingRemove',
                    'StreamRingAdd',
                    'FilterVersions',
                ),
                'users' => array('@'),
            ),
            array(
                'allow',
                'actions' => array(''),
                'users' => array('admin'),
            ),
            array(
                'deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Search for rhythns matching the given criteria.
     *
     * @param string $g_domain_filter A full or partial domain name to select results with.
     * @param string $g_username_filter A full or partial username to select results with.
     * @param string $g_name_filter A full or partial rhythm name to select results with.
     * @param string $g_version_filter A full or partial version number to seelct results with.
     * @param string $g_status The publish status of the rhythm.
     * @param string $g_cat_type The kind of rhythm
     * @param string $g_include_versions Should all versions be included in the results.
     * @param string $g_page The page number of results requested.
     * @param string $g_row_qty The number of results per page.
     * @param array $g_sort_order An indexed array of sort orders.
     * @param array $g_sort_priority An array column headings indexed by the priority to sort results.
     * @param array $g_exact_match A index of column names and if should be exactly matched or not (boolean).
     *
     * @return void
     */
    public function actionRhythmSearch($g_domain_filter, $g_username_filter, $g_name_filter, $g_version_filter,
        $g_status, $g_cat_type, $g_include_versions, $g_page, $g_row_qty, array $g_sort_order, array $g_sort_priority,
        array $g_exact_match
    ) {
        $fmodel = new RhythmSearchForm();

        $fmodel->domain_filter = $g_domain_filter;
        $fmodel->username_filter = $g_username_filter;
        $fmodel->name_filter = $g_name_filter;
        $fmodel->version_filter = $g_version_filter;
        $fmodel->status = $g_status;
        $fmodel->cat_type = $g_cat_type;
        $fmodel->show_version = $g_include_versions;
       // $fmodel->date_created = $g_date_created;
        $fmodel->page = $g_page;
        $fmodel->row_qty = $g_row_qty;
        $fmodel->sort_order = $g_sort_order;
        $fmodel->sort_priority = $g_sort_priority;
        $fmodel->include_test_users = $this->testing;
        $fmodel->exact_match = $g_exact_match;

        $json = array();
        if ($fmodel->validate() === false) {
            $json['error'] = ErrorHelper::model($fmodel->getErrors(), '<br />');
            $json['success'] = false;

        } else {
            $json['success'] = true;
            $json['rhythms'] = RhythmMulti::searchForRhythms($fmodel);
        }

        echo JSON::encode($json);
    }

    /**
     * Search for users matching the given criteria.
     *
     * @param string $g_domain_filter A full or partial domain name to select results with.
     * @param string $g_username_filter A full or partial username to select results with.
     * @param string $g_user_type Ristricts the search to a particular type of user.
     *      Valid values include 'user', 'ring'.
     * @param string $g_ring_membership_type Restricts the search to a particular type of ring membership
     *      Only used if user_type is set to 'ring'.
     * @param string $g_page The page number of results requested.
     * @param string $g_row_qty The number of results per page.
     * @param array $g_sort_order An indexed array of sort orders.
     * @param array $g_sort_priority An array column headings indexed by the priority to sort results.
     * @param array $g_exact_match A index of column names and if should be exactly matched or not (boolean).
     * @param string $g_ring_username A ring username to restrict results to just that rings members.
     * @param string $g_ring_domain A ring domain to restrict results to just that rings members.
     * @param string $g_ring_ban_filter On request restricted to a ring. This filters them by their ban status.
     * @param string $g_only_joinable_rings Only return results that allow users to request to join.
     *      Valid values are 'true' or 'false'
     string
     * @return void
     */
    public function actionUserSearch($g_domain_filter, $g_username_filter, $g_user_type,
        $g_page, $g_row_qty, array $g_sort_order, array $g_sort_priority, array $g_exact_match,
        $g_ring_username, $g_ring_domain, $g_ring_ban_filter, $g_only_joinable_rings
    ) {
        $fmodel = new UserSearchForm();

        $fmodel->domain_filter = $g_domain_filter;
        $fmodel->username_filter = $g_username_filter;
        $fmodel->user_type = $g_user_type;
        $fmodel->only_joinable_rings = $g_only_joinable_rings;
        $fmodel->page = $g_page;
        $fmodel->row_qty = $g_row_qty;
        $fmodel->sort_order = $g_sort_order;
        $fmodel->sort_priority = $g_sort_priority;
        $fmodel->include_test_users = $this->testing;
        $fmodel->exact_match = $g_exact_match;
        $fmodel->ring_username = $g_ring_username;
        $fmodel->ring_domain = $g_ring_domain;
        $fmodel->ring_ban_filter = $g_ring_ban_filter;

        $json = array();
        if ($fmodel->validate() === false) {
            $json['error'] = ErrorHelper::model($fmodel->getErrors(), '<br />');
            $json['success'] = false;

        } else {
            $json['success'] = true;
            $json['users'] = UserMulti::searchForUsers($fmodel);
        }

        echo JSON::encode($json);
    }

    /**
     * Return some data for a select table.
     *
     * A selection of streams, Rhythms or streams.
     *
     * @return void
     */
    public function actionGetSelection() {
        $fmodel = new GetSelectionForm();

        if (isset($_GET['site_filter']) === true) {
            $fmodel->site_filter = $_GET['site_filter'];
        }
        if (isset($_GET['user_filter']) === true) {
            $fmodel->user_filter = $_GET['user_filter'];
        }
        if (isset($_GET['name_filter']) === true) {
            $fmodel->name_filter = $_GET['name_filter'];
        }
        if (isset($_GET['version_filter']) === true) {
            $fmodel->version_filter = $_GET['version_filter'];
        }
        if (isset($_GET['type']) === true) {
            $fmodel->type = $_GET['type'];
        }
        if (isset($_GET['page']) === true) {
            $fmodel->page = $_GET['page'];
        }
        if (isset($_GET['rows']) === true) {
            $fmodel->rows = $_GET['rows'];
        }
        if (isset($_GET['show_version']) === true) {
            if ($_GET['show_version'] === 'true') {
                $fmodel->show_version = true;
            } else {
                $fmodel->show_version = false;
            }
        }
        if (isset($_GET['rhythm_cat_type']) === true) {
            $fmodel->rhythm_cat_type = $_GET['rhythm_cat_type'];
        }
        if (isset($_GET['stream_kind']) === true) {
            $fmodel->stream_kind = $_GET['stream_kind'];
        }
        if (isset($_GET['show_domain']) === true) {
            if ($_GET['show_domain'] === 'true') {
                $fmodel->show_domain = true;
            } else {
                $fmodel->show_domain = false;
            }
        }
        if (isset($_GET['only_rings']) === true) {
            if ($_GET['only_rings'] === 'true') {
                $fmodel->only_rings = true;
            } else {
                $fmodel->only_rings = false;
            }
        }
        if (isset($_GET['ring_type']) === true && $_GET['ring_type'] !== false) {
            $fmodel->ring_type = $_GET['ring_type'];
        }

        if ($fmodel->validate() === false) {
            throw new CHttpException(
                400,
                'Bad Request. Form did not validate <br/><br/>' . print_r($fmodel->getErrors(), true)
            );
        }

        switch($_GET['type']) {
            case "user":
                $json_array = User::generateJSONSearch($fmodel);
                break;

            case "rhythm":
                $json_array = Rhythm::generateRhythmSearch($fmodel);
                break;

            case "stream":
                $json_array = StreamMulti::generateStreamSearch($fmodel);
                break;
        }
        echo JSON::encode($json_array);
    }

    /**
     * Sets a new version for a subscribed filter.
     *
     * Request variables:
     * $_GET['id'] : local user_stream_subscription_id for the stream.
     * $_GET['new_version'] : String representing a partial or full version.
     *
     * @return void
     */
    public function actionFilterNewVersion() {
        if (isset($_POST['id']) === false) {
            throw new CHttpException(400, 'Bad Request. id not found in post variables.');
        }
        if (ctype_digit($_POST['id']) === false) {
            throw new CHttpException(400, 'Bad Request. id is not a positive integer.');
        }
        if (isset($_POST['new_version']) === false) {
            throw new CHttpException(400, 'Bad Request. new_version not found in post variables.');
        }

        $success = UserStreamSubscriptionFilter::updateVersion(
            $_POST['id'],
            Yii::app()->user->getId(),
            $_POST['new_version']
        );

        echo JSON::encode($success);
    }

    /**
     * Fetch the descriptions for all the streams registered by a user and all the embedded filters.
     *
     * @return void
     */
    public function actionStreamDescriptions() {

        $user_multi = new UserMulti();

        $descriptions = $user_multi->getDescriptions(Yii::app()->user->getId());

        echo JSON::encode($descriptions);
    }

    /**
     * Fetch the available full and partial versions for a users subscribed stream.
     *
     * Request variables:
     * $_GET['id'] : local user_stream_subscription_id for the stream.
     * Does not need to check that the id is owned by the relevant user as the data returned is public.
     *
     * @return void
     */
    public function actionStreamVersions() {
        if (isset($_POST['id']) === false) {
            throw new CHttpException(400, 'Bad Request. id not found in post variables.');
        }
        if (ctype_digit($_POST['id']) === false) {
            throw new CHttpException(400, 'Bad Request. id is not a positive integer.');
        }

        $json_array = StreamMulti::getVersions($_POST['id']);

        echo JSON::encode($json_array);
    }

    /**
     * Sets a new version for a subscribed stream.
     *
     * Request variables:
     * $_GET['id'] : local user_stream_subscription_id for the stream.
     * $_GET['new_version'] : String representing a partial or full version.
     *
     * @return void
     */
    public function actionStreamNewVersion() {
        if (isset($_POST['id']) === false) {
            throw new CHttpException(400, 'Bad Request. id not found in post variables.');
        }
        if (ctype_digit($_POST['id']) === false) {
            throw new CHttpException(400, 'Bad Request. id is not a positive integer.');
        }
        if (isset($_POST['new_version']) === false) {
            throw new CHttpException(400, 'Bad Request. new_version not found in post variables.');
        }

        $success = StreamMulti::updateVersion($_POST['id'], Yii::app()->user->getId(), $_POST['new_version']);

        echo JSON::encode($success);
    }


    /**
     * Fetch the available versions for this filter.
     *
     * !!!!! IMPORTANT This is deprecated. Use actionVersions in the Rhythm controler instead
     * Should be removed when stream subscriptions page 'change version' link is refactored.
     *
     * Request variables:
     * $_GET['name'] : name of the Rhythm.
     * $_GET['domain'] : domain of the data store that holds the Rhythm.
     * $_GET['user'] : user of the owner of the Rhythm.
     * Does not need to check that the id is owned by the relevant user as the data returned is public.
     *
     * @return void
     */
    public function actionFilterVersions() {
        if (isset($_POST['name']) === false) {
            throw new CHttpException(400, 'Bad Request. name not found in post variables.');
        }
        if (isset($_POST['domain']) === false) {
            throw new CHttpException(400, 'Bad Request. domain not found in post variables.');
        }
        if (isset($_POST['user']) === false) {
            throw new CHttpException(400, 'Bad Request. user not found in post variables.');
        }

        $json_array = Rhythm::getPartialVersions($_POST['name'], $_POST['domain'], $_POST['user']);

        echo JSON::encode($json_array);
    }

}

?>