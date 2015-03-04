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
     * The username of the user in the action url. Populated by UsernameFilter filter.
     *
     * @var string
     */
    public $username;

    /**
     * The primary key of the user in the action url. Populated by UsernameFilter filter.
     *
     * @var integer
     */
    public $user_id;

    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            array(
                'application.filters.UsernameFilter
                    +GetTakes,
                     GetLatestTakes,
                     GetPostTakes'
            ),
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
                    'GetLatestTakes',
                    'GetPostTakes',
                    'GetTakes',
                    'DomusUser',
                    'GetSelection',
                    'FilterVersions',
                    'StoreFeatureUsage',
                    'GetDomainSuggestions',
                    'GetDomainAndUsernameSuggestions',
                    'GetPrivatePosts',
                    'GetUserPosts',
                    'GetWaitingPostCount',
                    'SetWaitingPostCount',
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
     * Fetch the waiting message count for a user.
     *
     * @param string $p_client_domain the client domain that made the request.
     *
     * @return void
     */
    public function actionGetWaitingPostCount($p_client_domain) {
        $json_wait_count = Post::fetchWaitingPostCount(Yii::app()->user->getId(), $p_client_domain);
        echo JSON::encode($json_wait_count);
    }

    /**
     * Sets a new time for having viewed either a client or global inbox.
     *
     * @param string $p_client_domain The client domain that sent the data.
     * @param string $p_global Is this a global inbox that is being set.
     * @param string $p_time_viewed The time the user viewed the inbox.
     *      This should be the time the posts where requested, not the time the viewer actually viewed them.
     * @param string $p_type The type of posts updating the time for. 'public' or 'private'.
     *
     * @return void
     */
    public function actionSetWaitingPostCount($p_client_domain, $p_global, $p_time_viewed, $p_type) {
        $type_id = LookupHelper::getID('waiting_post_time.type_id', $p_type, false);
        if ($type_id === null) {
            throw new CHttpException(400, "Bad Request. type is not valid. Must be 'public' or 'private'.");
        }

        if (ctype_digit($p_time_viewed) === false) {
            throw new CHttpException(400, 'Bad Request. time_viewed is not a valid timestamp.');
        }
        $p_time_viewed = intval($p_time_viewed);

        if ($p_global !== 'false' && $p_global !== 'true') {
            throw new CHttpException(400, 'Bad Request. The global paramater needs to be "true" or "false".');
        }

        if ($p_global === 'false') {
            $site_id = Site::getSiteId($p_client_domain);
            WaitingPostTime::storeTime(Yii::app()->user->getId(), $p_time_viewed, $site_id, $p_type);
        } else {
            WaitingPostTime::storeTime(Yii::app()->user->getId(), $p_time_viewed, null, $p_type);
        }

        echo JSON::encode(array('success' => true));
    }

    /**
     * Fetch all public posts for a user within a timespan.
     *
     * @param string $p_domain The domain of the user to fetch posts for.
     * @param string $p_username The username of the user to fetch posts for.
     * @param string [$p_stream_url] Url of the stream to fetch posts for.
     *      Restricts the response to a stream.
     * @param string [$p_post_id] The id of the post to fetch child posts for.
     *      Restircts the reponse to a tree.
     * @param string [$p_oldest_post_date] The cut off timestamp to fetch private posts up to.
     *      If blank then the server config max is used.
     * @param string [$p_newest_post_date] The most recent time to search for posts from.
     *      If null then the current timestamp is used.
     * @param string [$p_page The page] of results being requested. Defaults to first page.
     * @param string [$p_with_content=false] Should the content of the posts be included.
     * @param string [$p_search_phrase] If set then the results will be searched for this phrase.
     * @param string [$p_search_title] Should the content of the posts be included.
     * @param string [$p_search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     */
    public function actionGetUserPosts($p_domain, $p_username, $p_stream_url=null, $p_post_id=null,
        $p_oldest_post_date=null, $p_newest_post_date=null, $p_page=null, $p_with_content='false',
        $p_search_phrase=null, $p_search_title=null, $p_search_other_fields=null
    ) {

        $site_id = SiteMulti::getSiteId($p_domain, true, true);
        if ($site_id === false) {
            throw new CHttpException(400, 'Bad Request. Unable to contact users data store.');
        }

        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($p_username, false, true);
        if ($user_id === false) {
            throw new CHttpException(400, 'Bad Request. Username not found in users data store.');
        }

        $stream_extra_id = false;
        if (empty($p_stream_url) === false) {
            $stream_extra_id = StreamBedMulti::getIdFromUrl($p_stream_url, true);
            if (is_numeric($stream_extra_id) === false) {
                $stream_extra_id = false;
            }
        }

        if (empty($p_post_id) === false && ctype_digit($p_post_id) === false) {
            throw new CHttpException(400, 'Bad Request. p_post_id is not numeric or empty.');
        }
        if (empty($p_post_id) === true) {
            $p_post_id = false;
        } else {
            $p_post_id = (int)$p_post_id;
        }

        if (empty($p_oldest_post_date) === false && ctype_digit($p_oldest_post_date) === false) {
            throw new CHttpException(400, 'Bad Request. p_last_post_date is not numeric or empty.');
        }
        if (empty($p_oldest_post_date) === true) {
            $p_oldest_post_date = false;
        } else {
            $p_oldest_post_date = (int)$p_oldest_post_date;
        }

        if (empty($p_newest_post_date) === false && ctype_digit($p_newest_post_date) === false) {
            throw new CHttpException(
                400,
                'Bad Request. p_start_post_date is not numeric or empty. ' . $p_newest_post_date
            );
        }
        if (empty($p_newest_post_date) === true) {
            $p_newest_post_date = time();
        } else {
            $p_newest_post_date = (int)$p_newest_post_date;
        }

        if ($p_with_content !== 'false' && $p_with_content !== 'true') {
            throw new CHttpException(400, 'Bad Request. with_content must be "true" or "false".');
        }
        $with_content = false;
        if ($p_with_content === 'true') {
            $with_content = true;
        }

        if (empty($p_page) === false && ctype_digit($p_page) === false) {
            throw new CHttpException(400, 'Bad Request. page is not numeric or empty.');
        }
        if (empty($p_page) === false && intval($p_page) <= 0) {
            throw new CHttpException(400, 'Bad Request. page must be 1 or greater.');
        }

        if (isset($p_search_phrase) === true && strlen($p_search_phrase) > 1) {
            if (isset($p_search_title) === false) {
                throw new CHttpException(400, 'Bad Request. search_title must be set if search_phrase is set.');
            }
            if (isset($p_search_other_fields) === false) {
                throw new CHttpException(400, 'Bad Request. search_other_fields must be set if search_phrase is set.');
            }
            if ($p_search_title === 'false') {
                $p_search_title = false;
            } else if ($p_search_title === 'true') {
                $p_search_title = true;
            } else {
                throw new CHttpException(400, 'Bad Request. search_title must be set to "true" or "false".');
            }
            if ($p_search_other_fields === 'false') {
                $p_search_other_fields = false;
            } else if ($p_search_other_fields === 'true') {
                $p_search_other_fields = true;
            } else {
                throw new CHttpException(400, 'Bad Request. search_other_fields must be set to "true" or "false".');
            }
        }

        if (empty($p_post_id) === false) {
            $json_posts = Post::getTreePostsForUser(
                $user_id,
                $p_post_id,
                $p_oldest_post_date,
                $p_newest_post_date,
                $p_page,
                $with_content,
                $p_search_phrase,
                $p_search_title,
                $p_search_other_fields
            );
        } else if (empty($stream_extra_id) === false) {
            $json_posts = Post::getStreamPostsForUser(
                $user_id,
                $stream_extra_id,
                $p_oldest_post_date,
                $p_newest_post_date,
                $p_page,
                $with_content,
                'public',
                $p_search_phrase,
                $p_search_title,
                $p_search_other_fields
            );
        } else {
            $json_posts = Post::getPublicPostResponses(
                $user_id,
                $p_oldest_post_date,
                $p_newest_post_date,
                $p_page,
                $with_content,
                $p_search_phrase,
                $p_search_title,
                $p_search_other_fields
            );
        }
        echo JSON::encode(array('posts' => $json_posts));
    }

    /**
     * Fetch posts that are private to the logged on user.
     *
     * @param string $p_client_domain The domain of the site that is requesting the posts.
     * @param string $p_stream_url Url of the stream to fetch private posts for.
     *      If blank then all latest private posts are fetched.
     * @param string $p_post_id The id of the post to fetch child posts for.
     *      If blank then no child posts are returned.
     * @param string $p_oldest_post_date The cut off timestamp to fetch private posts up to.
     *      If blank then the server config max is used.
     * @param string $p_newest_post_date The most recent time to search for posts from. If null then the current
     *      time stamp is used.
     * @param string $p_page The page of results being requested.
     * @param string $p_type The type of private results being requested. Valid values are:
     *      'local_private', 'global_private', 'local_sent_private', 'global_sent_private'
     *      Only required if this is not a stream or tree request.
     * @param string [$p_with_content=false] Should the content of the posts be included.
     * @param string [$p_search_phrase] If set then the results will be searched for this phrase.
     * @param string [$p_search_title] Should the content of the posts be included.
     * @param string [$p_search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return false
     * @fixme this needs to be accessed over https only... needs a check here as well.
     */
    public function actionGetPrivatePosts($p_client_domain=null, $p_stream_url=null, $p_post_id=null,
        $p_oldest_post_date=null, $p_newest_post_date=null, $p_page=null, $p_type=null, $p_with_content='false',
        $p_search_phrase=null, $p_search_title=null, $p_search_other_fields=null
    ) {
        $stream_extra_id = false;
        if (empty($p_stream_url) === false) {
            $stream_extra_id = StreamBedMulti::getIdFromUrl($p_stream_url, true);
            if (is_numeric($stream_extra_id) === false) {
                $stream_extra_id = false;
            }
        }

        if (empty($p_post_id) === false && ctype_digit($p_post_id) === false) {
            throw new CHttpException(400, 'Bad Request. p_post_id is not numeric or empty.');
        }
        if (empty($p_post_id) === true) {
            $p_post_id = false;
        } else {
            $p_post_id = (int)$p_post_id;
        }

        if (empty($p_oldest_post_date) === false && ctype_digit($p_oldest_post_date) === false) {
            throw new CHttpException(400, 'Bad Request. p_last_post_date is not numeric or empty.');
        }
        if (empty($p_oldest_post_date) === true) {
            $p_oldest_post_date = false;
        } else {
            $p_oldest_post_date = (int)$p_oldest_post_date;
        }

        if (empty($p_newest_post_date) === false && ctype_digit($p_newest_post_date) === false) {
            throw new CHttpException(
                400,
                'Bad Request. p_start_post_date is not numeric or empty. ' . $p_newest_post_date
            );
        }
        if (empty($p_newest_post_date) === true) {
            $p_newest_post_date = time();
        } else {
            $p_newest_post_date = (int)$p_newest_post_date;
        }

        if (empty($p_stream_url) === true && empty($p_post_id) === true) {
            if (empty($p_page) === false && ctype_digit($p_page) === false) {
                throw new CHttpException(400, 'Bad Request. page is not numeric or empty.');
            }
            if (empty($p_page) === false && intval($p_page) <= 0) {
                throw new CHttpException(400, 'Bad Request. page must be 1 or greater.');
            }
            $valid_types = array('local_private', 'global_private', 'local_sent_private', 'global_sent_private');
            if (in_array($p_type, $valid_types) === false) {
                throw new CHttpException(400, 'Bad Request. p_type is not valid.');
            }
        }

        if ((empty($p_stream_url) === false || empty($p_post_id) === false) && empty($p_page) === false) {
            throw new CHttpException(
                400,
                'Bad Request. page can not be used in combination with stream_url or post_id.'
            );
        }

        if ($p_with_content !== 'false' && $p_with_content !== 'true') {
            throw new CHttpException(400, 'Bad Request. with_content must be "true" or "false".');
        }
        $with_content = false;
        if ($p_with_content === 'true') {
            $with_content = true;
        }

        if (isset($p_search_phrase) === true && strlen($p_search_phrase) > 1) {
            if (isset($p_search_title) === false) {
                throw new CHttpException(400, 'Bad Request. search_title must be set if search_phrase is set.');
            }
            if (isset($p_search_other_fields) === false) {
                throw new CHttpException(400, 'Bad Request. search_other_fields must be set if search_phrase is set.');
            }
            if ($p_search_title === 'false') {
                $p_search_title = false;
            } else if ($p_search_title === 'true') {
                $p_search_title = true;
            } else {
                throw new CHttpException(400, 'Bad Request. search_title must be set to "true" or "false".');
            }
            if ($p_search_other_fields === 'false') {
                $p_search_other_fields = false;
            } else if ($p_search_other_fields === 'true') {
                $p_search_other_fields = true;
            } else {
                throw new CHttpException(400, 'Bad Request. search_other_fields must be set to "true" or "false".');
            }
        }

        $json_posts = Post::getPrivatePosts(
            $stream_extra_id,
            $p_post_id,
            $p_oldest_post_date,
            $p_newest_post_date,
            $p_page,
            $p_type,
            $p_client_domain,
            $with_content,
            $p_search_phrase,
            $p_search_title,
            $p_search_other_fields
        );

        echo JSON::encode(array('posts' => $json_posts));
    }

    /**
     * Get takes for an post for a user.
     *
     * @param string $p_post_domain The domain of the post to fetch takes for.
     * @param string $p_post_id The post id of the post to fetch takes for - local to the domain.
     * @param string $p_user_domain The domain of the user whose takes are being fetched.
     * @param string $p_username The username of the user whose takes are being fetched. (short form without domain)
     *
     * @return void
     * @refactor Only fetch private takes if the user is the logged on user or the post maker.
     * @refactor. This should not be needed. Should be using GetUserTake in the stream controller.
     */
    public function actionGetPostTakes($p_post_domain, $p_post_id, $p_username) {
        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername($p_username, false);
        if ($user_id === false) {
            throw new CHttpException(400, 'Bad Request. username not found.');
        }

        $takes = Take::getTakesForPost(
            $p_post_domain,
            $p_post_id,
            $user_id
        );
        if (empty($takes) === false) {
            $json_takes = $this->createdNestedTakes($takes);
        } else {
            // Create an empty array.
            $json_takes = array($p_post_domain => array($p_post_id => new stdClass()));// Forces an empty object
        }
        echo JSON::encode($json_takes);
    }

    /**
     * Takes a flat array of takes data and creates a nested array.
     *
     * Used for exporting take data to json.
     * Nested by domain, site_id and field.
     *
     * @param array $takes An array of take data.
     * @param string $takes[domain] The domain of the post the take is for.
     * @param number $takes[site_post_id] The id of the post the take is for - relative to the domain.
     * @param number $takes[field_id] The id of the field that the take is for.
     * @param number $takes[take_time] Timestamp for when the take was made.
     * @param number $takes[value] The value of the take.
     *
     * @return array
     */
    protected function createdNestedTakes($takes) {
        $json_takes = array();
        $current_post = array();
        $current_domain = "";
        $current_post_id = $takes[0]['site_post_id'];
        foreach ($takes as $take) {
            if ($current_post_id !== $take['site_post_id']) {
                if (array_key_exists($current_domain, $json_takes) === false) {
                    $json_takes[$current_domain] = array();
                }
                $json_takes[$current_domain][$current_post_id] = $current_post;
                $current_post = array();
            }

            // Prepare the current field.
            $current_field = array(
                'take_time' => strtotime($take['take_time']),
                'value' => $take['value'],
            );
            $current_post[$take['field_id']] = $current_field;
            $current_domain = $take['domain'];
            $current_post_id = $take['site_post_id'];
        }
        // append the last take
        $json_takes[$current_domain][$current_post_id] = $current_post;
        return $json_takes;
    }

    /**
     * Fetch the latest x takes for a logged in user.
     *
     * Latest is not defined by the time of the take, but the time of the post the take is for.
     * This allows the domus to fetch most of the latest takes, but still have a cut off date so that the
     * domus knows when to make a seperate request.
     * The number of takes fetched is set in the config.
     * This is not part of the protocol, but a helper function for the domus domain.
     *
     * @return void
     */
    public function actionGetLatestTakes() {

        $qty = Yii::app()->params['user_takes_qty'];
        $takes = Take::getLatestTakesByPost($qty, Yii::app()->user->getId());
        $last_take = end(array_values($takes));
        $last_post_time = $last_take['post_time'];

        // Ensure that the last take does not split an post in half
        // - due to multiple takes on different fields of an post.
        $last_takes = Take::getTakesForPost(
            $last_take['domain'],
            $last_take['site_post_id'],
            Yii::app()->user->getId()
        );
        $include_extra = false;
        foreach ($last_takes as $a_last_take) {
            if ($a_last_take['field_id'] === $last_take['field_id']) {
                $include_extra = true;
                continue;
            }
            if ($include_extra === true) {
                array_push($takes, $last_takes);
            }
        }

        $json_takes = $this->createdNestedTakes($takes);

        echo JSON::encode(
            array(
                'takes' => $json_takes,
                'last_post_time' => strtotime($last_post_time),
            )
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
     * Returns JSON information for the current logged on user.
     *
     * @return void
     * @fixme this code is duplicated in the domus view.
     */
    public function actionDomusUser() {
        if (Yii::app()->user->isGuest === true) {
            $json = array();
        } else {
            // Fetch rhythm usage
            $rhythm = Rhythm::getUserKindredRhythm(Yii::app()->user->getId());

            $site_access_rows = SiteAccess::getAll();
            $site_access = array();
            foreach ($site_access_rows as $row) {
                array_push($site_access, $row['domain']);
            }

            $admin_rings = UserRing::getRingDetailsForDomus(Yii::app()->user->getId(), 'admin');
            $member_rings = UserRing::getRingDetailsForDomus(Yii::app()->user->getId(), 'member');

            $ary = array(
                "username" => Yii::app()->user->getName(),
                "domain" => Yii::app()->user->getDomain(),
                "site_access" => $site_access,
                "kindred_rhythm" => $rhythm,
                "max_takes_from_server" => Yii::app()->params['takes_to_process'],
                "initial_wait_before_processing_takes" => Yii::app()->params['initial_wait_before_processing_takes'],
                "short_wait_before_processing_takes" => Yii::app()->params['short_wait_before_processing_takes'],
                "long_wait_before_processing_takes" => Yii::app()->params['long_wait_before_processing_takes'],
                "ring_pause" => Yii::app()->params['ring_pause'],
                "member_rings" => $member_rings,
                "admin_rings" => $admin_rings,
            );
        }
        $json = JSON::encode($ary);
        echo $json;
    }

    /**
     * Stores a users feature usage data.
     *
     * @return void
     */
    public function actionStoreFeatureUsage() {
        $fmodel = new FeatureUsage();
        if (isset($_POST['date']) === true) {
            $fmodel->date = $_POST['date'];
        }
        if (isset($_POST['feature_usage']) === true) {
            $fmodel->feature_usage = $_POST['feature_usage'];
        }

        if ($fmodel->validate() === false) {
            throw new CHttpException(
                400,
                'Bad Request. FeatureUsage form did not validate <br/><br/>' . print_r($fmodel->getErrors(), true)
            );
        }

        $success =  UserFeatureUsage::insertRowsByUserId(Yii::app()->user->getId(), $fmodel);
        // @fixme should return an error = 'message' rather than success = false.

        echo JSON::encode(
            array(
                "success" => $success,
                "line_errors" => $fmodel->getLineErrors(),
            )
        );
    }

    /**
     * Returns valid data store domain suggestions based on a partial domain.
     *
     * @param {string} $p_partial_domain The partial domain to fetch suggestions for.
     *
     * @return void
     */
    public function actionGetDomainSuggestions($p_partial_domain) {

        $suggestions = Site::getSuggestions($p_partial_domain);

        echo JSON::encode(
            array(
                "domains" => $suggestions,
            )
        );
    }

    /**
     * Returns valid data store domain suggestions based on a partial domain.
     *
     * @param {string} $p_partial_domain The partial domain to fetch suggestions for.*
     * @param {string} $p_partial_username The partial username to fetch suggestions for.
     *
     * @return void
     */
    public function actionGetDomainAndUsernameSuggestions($p_partial_domain, $p_partial_username) {

        $suggestions = UserMulti::getDomainAndUsernameSuggestions($p_partial_domain, $p_partial_username);

        echo JSON::encode(
            array(
                'success' => true,
                'suggestions' => $suggestions,
            )
        );
    }
}

?>