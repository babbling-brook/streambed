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
                    'StreamSearch',
                    'RhythmSearch',
                    'UserSearch',
                ),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(
                    'GetTakes',
                    'MakePost',
                    'DeletePost',
                    'GetSelection',
                    'StreamVersions',
                    'FilterVersions',
                    'UsernameSuggestions',
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
     * Search for streams matching the given criteria.
     *
     * @param string $g_domain_filter A full or partial domain name to select results with.
     * @param string $g_username_filter A full or partial username to select results with.
     * @param string $g_name_filter A full or partial stream name to select results with.
     * @param string $g_version_filter A full or partial version number to seelct results with.
     * @param string $g_status The publish status of the stream.
     * @param string $g_kind The kind of stream
     * @param string $g_include_versions Should all versions be included in the results.
     * @param string $g_page The page number of results requested.
     * @param string $g_row_qty The number of results per page.
     * @param array $g_sort_order An indexed array of sort orders.
     * @param array $g_sort_priority An array column headings indexed by the priority to sort results.
     * @param array $g_exact_match A index of column names and if should be exactly matched or not (boolean).
     *
     *
     * @return void
     */
    public function actionStreamSearch($g_domain_filter, $g_username_filter, $g_name_filter, $g_version_filter,
        $g_status, $g_kind, $g_include_versions, $g_page, $g_row_qty, array $g_sort_order, array $g_sort_priority,
        array $g_exact_match
    ) {
        $fmodel = new StreamSearchForm();

        $fmodel->domain_filter = $g_domain_filter;
        $fmodel->username_filter = $g_username_filter;
        $fmodel->name_filter = $g_name_filter;
        $fmodel->version_filter = $g_version_filter;
        $fmodel->status = $g_status;
        $fmodel->kind = $g_kind;
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
            $json['streams'] = StreamMulti::searchForStreams($fmodel);
        }

        echo JSON::encode($json);
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
     * @param string $g_users_to_vet_for_ring Only return results for users waiting to be vetted by this ring.
     *      Valid values are 'true' or 'false'
     string
     * @return void
     */
    public function actionUserSearch($g_domain_filter, $g_username_filter, $g_user_type,
        $g_page, $g_row_qty, array $g_sort_order, array $g_sort_priority, array $g_exact_match,
        $g_ring_username, $g_ring_domain, $g_ring_ban_filter, $g_only_joinable_rings, array $g_users_to_vet_for_ring
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
        if (isset($g_users_to_vet_for_ring['username']) === true) {
            $fmodel->users_to_vet_for_ring = $g_users_to_vet_for_ring;
        }

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

            default:
                // Not needed
                break;
        }
        echo JSON::encode($json_array);
    }

    /**
     * Fetch the available versions for this filter.
     *
     * !!!!! IMPORTANT This is deprecated. Use actionVersions in the Rhythm controler instead
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
     * Retrieve JSON data on takes that have been cast but not caculated via the relationship Rhythm.
     *
     * Request variables:
     * $_GET['form']['start_age']How old the takes should be in seconds before they are included.
     * $_GET['form']['qty'] Maximum qty to return.
     * $_GET['form']['user_rhythm_id'] The local user_rhythm_id for the kindred Rhythm being used.
     *
     * @return void
     */
    public function actionGetTakes() {
        $fmodel = new GetTakesForm();
        if (isset($_GET['form']) === true) {
            $fmodel->attributes = $_GET['form'];
        } else {
            throw new CHttpException(400, 'Bad Request. Form data missing.');
        }

        if ($fmodel->validate() === false) {
            throw new CHttpException(
                400,
                'Bad Request. Form did not validate <br/><br/>' . print_r($fmodel->getErrors(), true)
            );
        }

        $takes = TakeMulti::getNextKindredTakes($fmodel);

        if (empty($takes) === true) {
            $takes = (object)$takes;
        }
        //echo JSON::encode($takes);
        echo JSON::encode($takes);
    }

    /**
     * Inserts a new post and posts back its id in JSON.
     *
     * @param array $p_content The submitted fields that make up this post.
     * @param array $p_stream The stream object.
     * @param array $p_submitting_user Contains details on the user who is submitting the post.  Two elements:
     *      username
     *      domain
     * @param string $p_secret The secret used to verify the user if they belong to a remote domain.
     *      Should be set to 'false' if this domain is the same as the user making the post.
     * @param string [$p_parent_id] The id - local to the stream - of the parent post for this post.
     *      Only present if there is a parent.
     * @param string [$p_top_parent_id] The id - local to the stream - of the top parent post for this post.
     *      Only present if there is a top parent.
     * @param string [$p_post_id] The post id, if this is an edit, otherwise null.
     * @param array [$p_private_addresses] If there are any private addresses then this post is only for users listed in
     *      this array. It is not sent to the stream domain, only stored in the senders and recipients data stores.
     * @param string [$p_revision] The revision of this post to insert (If it is an edit).
     *
     * @return void
     * @refactor there is an inconsistant use of ID and id and Id in the project. needs sorting.
     * @fixme top_parent_id should be calculated here rather than submitted. This will prevent accidental or on
     *      purpose spoofing.
     */
    public function actionMakePost(array $p_content, array $p_stream, array $p_submitting_user, $p_secret,
        $p_parent_id=null, $p_top_parent_id=null, $p_post_id=null, array $p_private_addresses=null, $p_revision=null
    ) {
        $this->ensureSSL();

        if (isset($p_stream['name']) === false
            || isset($p_stream['domain']) === false
            || isset($p_stream['username']) === false
            || isset($p_stream['version']) === false
        ) {
            throw new CHttpException(400, 'Bad Request. Data missing');
        }

        if ($p_secret === 'false' && $p_submitting_user['domain'] !== Yii::app()->params['host']) {
            throw new CHttpException(400, 'Bad Request. A secret is needed to make an post at a remote domain.');
        }

        // If $p_parent_id is set then so must $p_top_parent_id
        if (isset($p_parent_id) === true || isset($p_parent_id) === true) {
            if (isset($p_parent_id) !== true || isset($p_parent_id) !== true) {
                throw new CHttpException(
                    400,
                    'Bad Request. If parent_id is set, then so must be top_parent_id and visa versa.'
                );
            }
        }

        // Verify the integrity of parent and top parent ids if the stream is local.
        if ($p_stream['domain'] === Yii::app()->params['host'] && isset($p_parent_id) === true) {
            Post::verifyParent($p_parent_id, $p_top_parent_id);
        }

        // If this is not the domain of the stream the post is being submitted to then the post is being
        // cached here for someone else. It needs to be marked as private.
        // This is because deleted posts are only sure to have been deleted on the stream server.
        $status = "public";
        if ($p_stream['domain'] !== Yii::app()->params['host']) {
            $status = 'private';
        }

        // Fetch user_ids of the recipients if this is a private post.
        $private_user_ids = array();
        if (isset($p_private_addresses) === true) {
            $status = "private";
            foreach ($p_private_addresses as $address) {
                $name_parts = User::getNamePartsFromFullName($address);
                if ($name_parts === false) {
                    throw new CHttpException(
                        400,
                        'Bad Request. Private username is not a valid BabblingBrook username.'
                    );
                }
                $private_site_id = SiteMulti::getSiteID($name_parts[0]);
                $private_ub = new UserMulti($private_site_id);
                $private_user_id = $private_ub->getIDFromUsername($name_parts[1], false);
                if ($private_user_id === false) {
                    if ($private_site_id === Yii::app()->params['site_id']) {
                        throw new CHttpException(400, 'Bad Request. Private username does not exist.');
                    } else {
                        $private_user_id = $private_ub->insertRemoteUser($name_parts[1]);
                    }
                }
                $private_user_ids[] = $private_user_id;
            }
        }

        // Verify the secret if the user is not local.
        if ($p_secret !== 'false') {
            $submitting_site_id = SiteMulti::getSiteId($p_submitting_user['domain'], true, true);
            if ($submitting_site_id === false) {
                throw new CHttpException(400, 'Bad Request. Unable to contact users data store.');
            }

            $user_multi = new UserMulti($submitting_site_id);
            $submitting_user_id = $user_multi->getIDFromUsername($p_submitting_user['username'], false, true);
            if ($submitting_user_id === false) {
                throw new CHttpException(400, 'Bad Request. Username not found in users data store.');
            }

            $ots = new OtherDomainsHelper;
            $secret_valid = $ots->verifySecret($p_submitting_user['domain'], $p_submitting_user['username'], $p_secret);
            if ($secret_valid === false) {
                throw new CHttpException(
                    400,
                    'Bad Request. The secret for the ' . $p_submitting_user['domain']
                        . ' domain is not valid when inserting an post.'
                );
            }

        } else {
            if ($p_submitting_user['username'] === Yii::app()->user->getName()
                && $p_submitting_user['domain'] === Yii::app()->params['host']
            ) {
                $submitting_user_id = Yii::app()->user->getId();
            }
        }

        // Make sure that there is a local version of this stream.
        if ($p_stream['domain'] === Yii::app()->params['host']) {
            $site_id = Yii::app()->params['site_id'];
        } else {
            $site_id = SiteMulti::getSiteID($p_stream['domain']);
        }
        if ($site_id === false) {
            throw new CHttpException(400, 'Bad Request. stream_domain does not exist.');
        }
        $user_multi = new UserMulti($site_id);
        $stream_user_id = $user_multi->getIDFromUsername($p_stream['username']);
        $version_array = explode("/", $p_stream['version']);
        $stream_extra_id = StreamBedMulti::getIDByName(
            $stream_user_id,
            $p_stream['name'],
            $version_array[0],
            $version_array[1],
            $version_array[2],
            $p_stream['domain']  // If the stream is remote and private, then this will return false.
        );
        if ($stream_extra_id === false) {
            throw new CHttpException(400, 'Bad Request. stream does not exist.');
        }

        // Ascertain that the submitter is permitted to make an post in this stream.
        $post_mode = StreamExtra::getPostMode($stream_extra_id);
        if ($post_mode === 'owner' && $status !== "private") {
            if ($stream_user_id !== Yii::app()->user->getId()) {
                throw new CHttpException(403, 'The requested page is forbidden.');
            }
        }

        // Get the post_id if it is posted back - making this an edit rather than a new post
        $post_id = null;
        if (isset($p_post_id) === true) {
            if (ctype_digit($p_post_id) === false) {
                throw new CHttpException(400, 'Bad Request. post_id is not an unsigned int.');
            }
            $post_id = $p_post_id;

            // If this is the stream domain the the revision number needs fetching, if it is
            // another domain then it needs check for availability.
            if (isset($p_revision) === false && $p_stream['domain'] !== Yii::app()->params['host']) {
                $error_message = 'Bad Request. revision number must be present if this is not the stream domain.';
                throw new CHttpException(400, $error_message);

            } else if (isset($p_revision) === true && ctype_digit($p_revision) === false) {
                throw new CHttpException(400, 'Bad Request. revision number is not an unsigned int.');

            } else if (isset($p_revision) === true) {
                if (Post::checkRevisionAvailable($post_id, $p_revision) === false) {
                    throw new CHttpException(400, 'Bad Request. revision number has already been taken.');
                }
            }

            // Ascertain that submitter is allowed to make an edit.
            // @fixme bug what if this is an editors domain, and there is no previous record of the post?
            $edit_mode = StreamExtra::getEditMode($stream_extra_id);
            $post_user_id = Post::getUserId($post_id);
            if ($edit_mode !== 'anyone' && $submitting_user_id !== $post_user_id) {
                throw new CHttpException(400, 'Bad Request. User is not allowed to edit this post.');
            }

        }

        // Check fields are valid if set and then submit
        $result = PostMulti::insertPost(
            $stream_extra_id,
            $p_content,
            $submitting_user_id,
            null,
            null,
            $p_parent_id,
            $p_top_parent_id,
            $post_id,
            $status
        );

        if (is_array($result) === true) {
            throw new CHttpException(
                400,
                'Bad Request. Stream field data does not match stream: ' . implode(",", $result)
            );
        }

        if (isset($result->post_id) === true) {
            $post_id = $result->post_id;
        }

        if ($post_id === null) {
            throw new CHttpException(400, 'Bad Request. Post id not returned');
        }

        // If this is a private post then the recipients need linking to the post.
        if ($result !== false && empty($private_user_ids) === false) {
            foreach ($private_user_ids as $private_user_id) {
                $link_row = new PostPrivateRecipient;
                $link_row->post_id = $post_id;
                $link_row->user_id = $private_user_id;
                $link_row->save();
            }
        }

        if ($result === false) {
            $json = array(
                "error" => "The data store that owns this stream is not responding.",
            );
        } else {
            // Fetch the post and return it
            $post = PostMulti::getPost($post_id);
            $json = array(
                "post" => $post,
            );
        }

        echo JSON::encode($json);
    }

    /**
     * Delete an post.
     *
     * There are several ways in which an post can be deleted.
     * 1. A 'full' delete happens when an post has no children or takes is public and
     *          the delete request is made by the post maker.
     * 2. A 'hidden' delete happens when an post has children or takes.
     * 3. An post_private_recipient link is flagged as deleted if the request is from
     *      the recipient of the post.
     *
     * This action is used by both the data store that houses the stream that the post is in and also the
     * data store of the user who made the post. Requests are differentiated
     * by the pressence of a secret that is required if this is a request from the stream data store.
     *
     * The cooldown time is not explicitely checked because there should be no children or takes if an post is in
     * its cooldown time.
     *
     * @param string $p_post_id The local id of the post to delete.
     * @param string|null $p_secret The secret that was used to create the post.
     *      This is only used if this is not the data store of the user who made the post.
     *
     * @return void
     * @fixme if the delete timeout has failed then mark the post as user deleted.
     */
    public function actionDeletePost($p_post_id, $p_secret=null) {
        $this->ensureSSL();

        if (ctype_digit($p_post_id) === false) {
            throw new CHttpException(400, 'Bad Request. post_id is not a positive integer.');
        }

        // If there is a secret then this data store is not the same data store as the
        // data store of the user who made the post.
        if (empty($p_secret) === false) {

            $post_user = Post::getUserOwner($p_post_id);
            if ($post_user === false) {
                echo JSON::encode(
                    array(
                        'success' => false,
                        'status' => 'not_found',
                    )
                );
                return;
            }

            $user = Yii::app()->user;
            $user_owned = false;
            if ($post_user['domain'] === $user->getDomain() && $post_user['username'] === $user->getName()) {
                $user_owned = true;
            }

            if ($user_owned === true && $user['domain'] === Yii::app()->params['host']) {
                throw new CHttpException(
                    400,
                    'Bad Request. Secret found but this is not the home domain for the user.'
                );
            }

            $ots = new OtherDomainsHelper;
            $secret_valid = $ots->verifySecret($user['domain'], $user['username'], $p_secret);
            if ($secret_valid === false) {
                throw new CHttpException(400, 'Bad Request. The secret is not valid.');
            }

        }

        $delete_status = Post::deletePost($p_post_id);

        $success = true;
        if ($delete_status === false) {
            $success = false;
        }

        echo JSON::encode(
            array(
                'success' => $success,
                'status' => $delete_status,
            )
        );

    }

    /**
     * Returns valid username suggestions based on a partial username.
     *
     * @param {string} $p_partial_username The partial domain to fetch suggestions for.
     *
     * @return void
     */
    public function actionUsernameSuggestions($p_partial_username) {

        $suggestions = User::getSuggestions($p_partial_username);

        echo JSON::encode(
            array(
                "usernames" => $suggestions,
            )
        );
    }

}

?>