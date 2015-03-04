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
 * Stream controller
 *
 * @package PHP_Controllers
 * @fixme this needs factoring out and replacing with $version_link.
 */
class StreamController extends Controller
{
    /**
     * The username of the user who owns the stream in the action url.
     *
     * @var string
     */
    public $username;

    /**
     * The primary key of the user who owns the stream in the action url.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The version part of the stream in the action url. In dot format.
     *
     * @var string
     */
    public $version_string;

    /**
     * The version part of the stream in the action url. In major/minor/patch format.
     *
     * @var string
     */
    public $version_link;

    /**
     * The currently loaded data model instance. Includes sub models for version, cat and user.
     *
     * @var Rhythm
     */
    public $model;

    /**
     * The stream_extra_id of this stream.
     *
     * @var string
     */
    public $model_id;

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
            array(            // Checks name and version and sets $model_id but does not load the model
                'application.filters.VersionNoModelFilter
                    + PostsHead,
                      GetPostsBlock,
                      GetPostsSearch,
                      Take,
                      PostUser,
                      GetBlockNumber,
                      GetUserTakes,
                      GetPostsLatest,
                      GetUserTakeBlockNumber,
                      OpenlistSuggestionsFetch',
                'data_type' => 'stream',
            ),
            array(            // Must run before the remaining filters. Loads the model
                'application.filters.VersionFilter
                    + StoreOwnersStreamSortResults',
                'data_type' => 'stream',
                'version_type' => 'stream',
            ),
            array(
                'application.filters.OwnerFilter
                    + Delete,
                    StoreOwnersStreamSortResults'
            ),
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
                    // For the Rhythm action, see Posts (redirected in config)
                    'GetPostsBlock',
                    'GetPostsSearch',
                    'postuser',
                    'GetBlockNumber',
                    'GetUserTakes',
                    'GetPostsLatest',
                    'GetUserTakeBlockNumber',
                    'GetBlockHeader',
                    'GetStreamTakes',
                    'StoreOwnersStreamSortResults',
                    'OpenlistSuggestionsFetch',
                    'GetVersions',
                    'GetExactVersions',
                    'GetStreamBlockNumbers',
                ),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(
                    'Take',
                    'JSON',
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
     * For streams with a kind of 'user', Checks if a user has an post against them for this stream.
     *
     * Returns the post it is present.
     *
     * @param {string} $g_domain The domain of the user to check for an post.
     * @param {string} $g_username The username of the user to check for an post.
     *
     * @return void
     */
    public function actionPostUser($g_domain, $g_username) {
        if (isset($g_domain) === false) {
            throw new CHttpException(400, 'Bad Request. domain of user not present');
        }
        if (isset($g_username) === false) {
            throw new CHttpException(400, 'Bad Request. username not present');
        }

        $json_array = PostUser::getPostForUser($g_domain, $g_username, $this->model_id);
        if ($json_array === false) {
            echo JSON::encode(array('post' => false));
        } else {
            echo JSON::encode($json_array);
        }

    }

    /**
     * Returns details about the blocks in this stream.
     *
     * @param string $g_user The username of the user who owns this stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param string $g_post_id The of the root post if this is a request for a tree block.
     *
     * @return void
     */
    public function actionGetBlockHeader($g_user, $g_stream, $g_major, $g_minor, $g_patch, $g_post_id=null) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        if (isset($g_post_id) === true && $g_post_id === '') {
            if (ctype_digit($g_post_id) === false) {
                throw new CHttpException(400, 'Bad Request. post_id is not valid.');
            }

            $last_block_number = StreamBlockTree::getLatest($g_post_id);
        } else {
            $last_block_number = StreamBlock::getLatest($stream_extra_id);
        }

        echo JSON::encode(
            array(
                'last_block_number' => $last_block_number,
                'refresh_frequency' => Yii::app()->params['refresh_frequency'],
            )
        );
    }

    /**
     * Fetches all the block numbers for a stream between two timestamps.
     *
     * @param string $g_user The stream username.
     * @param string $g_stream The stream name.
     * @param string $g_major The major version of the stream.
     * @param string $g_minor The minor version of the stream.
     * @param string $g_patch The patch version of the stream.
     * @param string $g_to_timestamp The to timestamp to fetch block numbers for.
     * @param string $g_from_timestamp The from timestamp to fetch block numbers for.
     *
     * @return void
     */
    public function actionGetStreamBlockNumbers($g_user, $g_stream, $g_major, $g_minor, $g_patch,
        $g_to_timestamp, $g_from_timestamp
    ) {
        $json = array();

        if (ctype_digit($g_to_timestamp) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["$g_to_timestamp"] is not a valid timestamp.');
        }
        if (ctype_digit($g_from_timestamp) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["from_timestamp"] is not a valid timestamp.');
        }

        $stream_name_form = new StreamNameForm();
        $stream_name_form->makeStreamObject(
            Yii::app()->params['host'],
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $stream_valid = $stream_name_form->validate();
        if ($stream_valid === false) {
            $json['error'] = 'This is not a valid stream.';
            $json['success'] = false;
        } else {
            $json['success'] = true;
            $stream_extra_id = $stream_name_form->getFirstStreamExtraId();
            $blocks = StreamBlock::getStreamBlockNumbers($stream_extra_id, $g_to_timestamp, $g_from_timestamp);
            if (empty($blocks) === true) {
                $json['blocks'] = array(
                    array(
                        'block_number' => 0,
                        'from_timestamp' => 0,
                        'to_timestamp' => time(),
                    ),
                );
            } else {
                $json['blocks'] = $blocks;
            }
        }
        echo JSON::encode($json);
    }


    /**
     * Fetches all the takes in a stream block number
     *
     * @param string $g_user The stream username.
     * @param string $g_stream The stream name.
     * @param string $g_major The major version of the stream.
     * @param string $g_minor The minor version of the stream.
     * @param string $g_patch The patch version of the stream.
     * @param string $g_to_timestamp The to timestamp to fetch block numbers for.
     * @param string $g_from_timestamp The from timestamp to fetch block numbers for.
     *
     * @return void
     */
    public function actionGetStreamTakes($g_user, $g_stream, $g_major, $g_minor, $g_patch,
        $g_block_number, $g_field_id
    ) {
        $json = array();

        if (ctype_digit($g_block_number) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["$g_block_number"] is not a valid timestamp.');
        }
        if (ctype_digit($g_field_id) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["$g_field_id"] is not a valid timestamp.');
        }

        $stream_name_form = new StreamNameForm();
        $stream_name_form->makeStreamObject(
            Yii::app()->params['host'],
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $stream_valid = $stream_name_form->validate();
        if ($stream_valid === false) {
            $json['error'] = 'This is not a valid stream.';
            $json['success'] = false;
        } else {
            $json['success'] = true;
            $json['streams'] = array();
            $stream_extra_id = $stream_name_form->getFirstStreamExtraId();

            $json['takes'] = TakeMulti::GetTakesForBlock($stream_extra_id, $g_block_number, $g_field_id);

        }
        echo JSON::encode($json);
    }

    /**
     * Returns the block number required for an stream, given a timestamp.
     *
     * Available request variables:
     * $_GET['time']
     * $_GET['type'] Valid values are 'stream' and 'tree'
     * $_GET['post_id'] Only required if type = tree
     *
     * @return void
     *
     * @fixme refactor $_GET["time"] to be $_GET["timestamp"].
     */
    public function actionGetBlockNumber() {
        if (isset($_GET['time']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["time"] is not present');
        }
        if (ctype_digit($_GET['time']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["time"] is not a valid value : ' . $_GET['time']);
        }

        if (isset($_GET['type']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["type"] is not present');
        }
        if ($_GET['type'] !== 'stream' && $_GET['type'] !== 'tree') {
            throw new CHttpException(400, 'Bad Request. $_GET["type"] value is not valid : ' . $_GET['type']);
        }

        if ($_GET['type'] === 'tree'
            && (isset($_GET['post_id']) === false || ctype_digit($_GET['post_id'])=== false)
        ) {
            throw new CHttpException(400, 'Bad Request. $_GET["post_id"] is missing on a tree request');
        }

        if ($_GET['type'] === 'stream') {
            $block_number = StreamBlock::getBlockNumber($this->model_id, $_GET['time']);
            if ($block_number === false) {
                $block_number = StreamBlock::getNearestBlockNumber($this->model_id, $_GET['time']);
            }
        } else if ($_GET['type'] === 'tree') {
            $block_number = StreamBlockTree::getBlockNumber($_GET['post_id'], $_GET['time']);
            if ($block_number === false) {
                $block_number = StreamBlockTree::getNearestBlockNumber($_GET['post_id'], $_GET['time']);
            }
        }

        echo JSON::encode(
            array(
                'block_number' => $block_number,
                'refresh_frequency' => Yii::app()->params['refresh_frequency'],
            )
        );
    }

    /**
     * Return the posts for this stream.
     *
     * Available request variables:
     * $_GET['block_number']
     * $_GET['post_id'] Only populated if a sub post search is requested.
     * $_GET['stream'] The name of the stream
     * $_GET['with_content'] should the post content be fetch along with the post?
     *
     * @return void
     */
    public function actionGetPostsBlock() {
        if (isset($_GET['block_number']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["block_number"] is not present');
        }

        if (ctype_digit($_GET['block_number']) === false) {
            throw new CHttpException(
                400,
                'Bad Request. $_GET["block_number"] is not a valid value : ' . $_GET['block_number']
            );
        }

        if (isset($_GET['type']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["type"] is not present');
        }
        if ($_GET['type'] !== 'stream' && $_GET['type'] !== 'tree') {
            throw new CHttpException(400, 'Bad Request. $_GET["type"] is not valid. Should be "tree" or "stream"');
        }

        if ($_GET['type'] === 'tree'
            && (isset($_GET['post_id']) === false|| ctype_digit($_GET['post_id']) === false)
        ) {
            throw new CHttpException(
                400, 'Bad Request. $_GET["post_id"] is not present or valid for a tree request.'
            );
        }

        // This is to present fake data to test that the correct error is raised when stream data is corrupt.
        if ($_GET['stream'] === 'test stream 8'
            || (isset($_GET['post_id']) === true && $_GET['post_id'] === '59') === true
        ) {
            echo JSON::encode(array('posts' => array('wrong' => true)));
            return;
        }

        if ($_GET['type'] === 'tree') {
            // Tree request.
            $posts = Post::getSubPostsBlock($_GET['post_id'], $_GET['block_number']);

        } else if ($_GET['type'] === 'stream') {
            // Stream request.
            $posts = Post::getPostsBlock($this->model_id, $_GET['block_number']);
        }

        if (isset($_GET['with_content']) === true && $_GET['with_content'] === 'true') {
            foreach ($posts as $key => $post) {
                $posts[$key] = PostContent::appendContent($post);
            }
        }

        echo JSON::encode(
            array(
                'posts' => $posts,
            )
        );
    }

    /**
     * Return the posts for this stream that match the search criteria.
     *
     *
     * @param string $g_user The username of the owner of the stream the posts being fetched are from.
     * @param string $g_name The name of the owner of the stream the posts being fetched are from.
     * @param string $g_major The major version number of the stream the posts being fetched are from.
     * @param string $g_minor The minor version number of the stream the posts being fetched are from.
     * @param string $g_patch The patch version number of the stream the posts being fetched are from.
     * @param string $p_from_timestamp Unix timestamp from which to fetch posts.
     * @param string $p_to_timestamp Unix timestamp upto which to fetch posts.
     * @param string $p_type Is this a 'tree' or 'stream' search.
     * @param string $p_search_phrase The search phrase to filter the results with.
     * @param string $p_search_title Should the title be searched.
     * @param string $p_search_other_fields Should the rest of the text fields be searched.
     * @param string $p_post_id Only populated if a tree post search is requested.
     *
     * @return void
     */
    public function actionGetPostsSearch($g_user, $g_stream, $g_major, $g_minor, $g_patch,
        $p_from_timestamp, $p_to_timestamp, $p_type,
        $p_search_phrase, $p_search_title, $p_search_other_fields, $p_post_id=null
    ) {

        if ($p_type !== 'stream' && $p_type !== 'tree') {
            throw new CHttpException(400, 'Bad Request. type is not valid. Should be "tree" or "stream"');
        }

        if ($p_type === 'tree'
            && (isset($p_post_id) === false || strlen($p_post_id) === 0 || ctype_digit($p_post_id) === false)
        ) {
            throw new CHttpException(
                400, 'Bad Request. post_id is not present or valid for a tree request.'
            );
        }

        if (ctype_digit($p_to_timestamp) === false) {
            throw new CHttpException(400, 'Bad Request. to_timestamp is not a valid timestamp.');
        }

        if (ctype_digit($p_from_timestamp) === false) {
            throw new CHttpException(400, 'Bad Request. from_timestamp is not a valid timestamp.');
        }

        if (isset($p_search_phrase) === true && strlen($p_search_phrase) > 1) {
            if (strlen($p_search_phrase) < 4) {
                throw new CHttpException(400, 'Bad Request. search_phrase must have at least 4 characters.');
            }
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

        if ($p_type === 'tree') {
            // Tree request.
            $posts = Post::getTreeSearch(
                $p_post_id,
                $p_from_timestamp,
                $p_to_timestamp,
                $p_search_phrase,
                $p_search_title,
                $p_search_other_fields,
                1
            );

        } else if ($p_type === 'stream') {
            // Stream request.
            $posts = Post::getStreamSearch(
                $this->model_id,
                $p_from_timestamp,
                $p_to_timestamp,
                $p_search_phrase,
                $p_search_title,
                $p_search_other_fields,
                1
            );
        }

        $json = array();
        $json['success'] = true;
        $json['posts'] = $posts;

        echo JSON::encode($json);
    }

    /**
     * Return the latest update of posts for this stream.
     *
     * Available request variables:
     * $_GET['post_id'] Only populated if a sub post search is requested.
     *
     * @return void
     */
    public function actionGetPostsLatest() {
        if (isset($_GET['post_id']) === true && ctype_digit($_GET['post_id']) === true) {
            // Tree request.
            $posts = Post::getLatestSubPosts($_GET['post_id']);
        } else {
            // Stream request.
            $posts = Post::getLatestPosts($this->model_id);
        }
        echo JSON::encode(
            array(
                'posts' => $posts,
                'refresh_frequency' => Yii::app()->params['refresh_frequency'],
            )
        );
    }

    /**
     * Fetch all takes for a user in a particular block of a stream or tree.
     *
     * @param string $g_user Full username of the user
     * @param string $g_block_number The stream or tree block number that takes are being fethed for.
     * @param string $g_type The type of request. Valid values are 'stream' and 'tree'.
     * @param string [$g_post_id] Optional. If present then tree blocks are used instead of stream blocks.
     * @param string [$g_field_id=2] The id of the stream field for the value that takes are being fetched for.
     *      defaults to 2, which is the main field.
     *
     * @return void
     * @fixme should be called
     * @fixme needs to specify an optional field id (defaults to main), and results need to indicate the field id.
     */
    public function actionGetUserTakes($g_username, $g_block_number, $g_type, $g_post_id=null, $g_field_id=2) {
        if (ctype_digit($g_block_number) === false) {
            throw new CHttpException(
                400,
                'Bad Request. $g_block_number is not a valid value : ' . $g_block_number
            );
        }
        $g_block_number = intval($g_block_number);

        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername($g_username, false);
        if (isset($user_id) === false || is_int($user_id) === false) {
            throw new CHttpException(
                400,
                'Bad Request. $g_username is not a valid local user : ' . $g_username
            );
        }

        if ($g_type !== 'stream' && $g_type !== 'tree') {
            throw new CHttpException(400, 'Bad Request. $g_type is not valid : ' . $g_type);
        }

        // This is a fake response for testing if broken ring moderation triggers a client error.
        if ($_GET['stream'] === 'test stream 9') {
            echo JSON::encode(array('wrong' => true));
            return;
        }

        $last_full_block = null;
        if ($g_type === 'stream') {
            $takes = Take::getUserStreamTakes($g_block_number, $this->model_id, $user_id, $g_field_id);
            if ($g_block_number === 0) {
                $last_full_block = Take::getLastFullStreamBlockNumber($this->model_id, $user_id);
            }
        } else if ($g_type === 'tree') {
            if (isset($g_post_id) === false) {
                throw new CHttpException(400, 'Bad Request. $g_post_id is not present');
            }
            if (ctype_digit($g_post_id) === false) {
                throw new CHttpException(
                    400,
                    'Bad Request. $g_post_id is not a valid value : ' . $g_post_id
                );
            }
            $takes = Take::getUSerTreeTakes($g_block_number, $user_id, $g_post_id);
        }

        $data = array(
            'takes' => $takes,
        );
        if (isset($last_full_block) === true) {
            $data['last_full_block'] = $last_full_block;
        }
        echo JSON::encode($data);
    }

    /**
     * Registers a take from a user.
     *
     * $_POST['field_id'] is 1 based.
     * @fixme $_POST['field_id'] is really the display_order not the stream_field_id
     *
     * @return void
     * @fixme owned errors on a private stream. The post page should not even be displayed!
     * @fixme ensure a duplicate is not made.
     * @fixme ensure that the take value is not to large or small. need to check both post and stream max and mins.
     *      currently just defaults to the max or min rather than erroring.
     * @fixme this is assuming that the user is on the same domain as the stream.
     *      Need to pass a secret and user details.
     * @fixme this is currently called by the domus domain, it should be passed to the https scientia domain
     * @fixme This is currently in the stream controller. Needs a seperate take controller, that is a subset of
     * the post controller.
     */
    public function actionTake() {
        if (isset($_GET['post_id']) === false) {
            throw new CHttpException(400, 'Bad Request. Post id not present');
        }
        if (isset($_POST['field_id']) === false) {
            throw new CHttpException(400, 'Bad Request. field_id id not present');
        }
        if (ctype_digit($_POST['field_id']) === false) {
            throw new CHttpException(400, 'Bad Request. field_id not an integer');
        }
        if (isset($_POST['value']) === false) {
            throw new CHttpException(400, 'Bad Request. Value not present');
        }

        if (ctype_digit($_POST['value']) === false
            && ctype_digit(substr($_POST['value'], 1)) === false
            && substr($_POST['value'], 0, 1) !== '-'
        ) {
            throw new CHttpException(400, 'Bad Request. Value is not an integer (whole number)');
        }
        if (isset($_POST['mode']) === false) {
            throw new CHttpException(400, 'Bad Request. mode not present');
        }
        if ($_POST['mode'] !== 'new' && $_POST['mode'] !== 'add') {
            throw new CHttpException(400, 'Bad Request. Mode is not valid (must be "new" or "add")');
        }

        $user_id = Yii::app()->user->getId(); // See the todo above about passing in user details.

        // Check this post is valid for this stream
        if ($this->model_id === false) {
            throw new CHttpException(
                400,
                'Bad Request. Stream not found. Is it private? (stream_extra_id ' . $this->model_id . ')'
            );
        }

        // Check this post is valid for this stream
        if (PostMulti::owned($_GET['post_id'], $this->model_id) === false) {
            throw new CHttpException(
                400,
                'Bad Request. Post not owned by stream (post_id : '
                    . $_GET['post_id'] . ', stream_extra_id ' . $this->model_id . ')'
            );
        }

        // Check this take field is permited for this user.
        $stream_extra_id = Post::getStreamExtraId($_GET['post_id']);
        $who_can_take = StreamField::getWhoCanTake($stream_extra_id, $_POST['field_id']);
        if ($who_can_take === 'owner') {
            $owner_verified = StreamBedMulti::checkOwnerExtra($stream_extra_id, $user_id);
            if ($owner_verified === false) {
                throw new CHttpException(400, 'Bad Request. This field requires the take user to be the stream owner.');
            }
        }

        $value = TakeMulti::take(
            $_GET['post_id'],
            (int)$_POST['value'],
            $user_id,
            $_POST['field_id'],
            $_POST['mode']
        );

        if ($value === false) {
            $status = false;
        } else {
            $status = true;
        }

        $json = array(
            'value' => $value,
            'status' => $status,
        );

        echo JSON::encode($json);
    }

    /**
     * Fetch the stream as JSON.
     *
     * @return void
     * @fixme this needs to return default moderation rings
     * - and code if a user is not subscribed to a stream then they should use these rings.
     */
    public function actionJson($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        // This is to present fake data to test that the correct error is raised when stream data is corrupt.
        if ($g_stream === 'test stream 10') {
            echo JSON::encode(array('wrong' => true));
            return;
        }

        $json = array();
        $stream_name_form = new StreamNameForm();
        $stream_name_form->makeStreamObject(
            HOST,
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        if ($stream_name_form->validate() === false) {
            $json['error'] = ErrorHelper::model($stream_name_form->getErrors());
        } else {
            $stream_extra_ids = $stream_name_form->geAllStreamExtraIds();

            $streams = array();
            $json['streams'] = array();
            foreach ($stream_extra_ids as $stream_extra_id) {
                $stream_model = StreamBedMulti::getByIDWithExtra($stream_extra_id);
                $json['streams'][] = StreamBedMulti::getJSON(
                    $stream_model,
                    $stream_model->user->username,
                    HOST
                );
            }
        }

        echo JSON::encode($json);
    }


    /**
     * Get a user take block number.
     *
     * A users takes are cached into three different types of block.
     * First a generic block, based on time.
     * Second, blocks within a tress, based on time.
     * Third, blocks within a stream, based on time.
     *
     * Given a timestamp, a block number is fetched for a stream. This allows for further requests to be made
     * about a block of posts and their takes in a stream.
     *
     * @return void
     */
    public function actionGetUserTakeBlockNumber() {
        if (isset($_GET['username']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["username"] is not present');
        }
        if (isset($_GET['time']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["time"] is not present');
        }
        if (ctype_digit($_GET['time']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["time"] is not a valid value : ' . $_GET['time']);
        }
        if (isset($_GET['type']) === false) {
            throw new CHttpException(400, 'Bad Request. $_GET["type"] is not present');
        }
        if ($_GET['type'] !== 'stream' && $_GET['type'] !== 'tree') {
            throw new CHttpException(400, 'Bad Request. $_GET["type"] is not valid : ' . $_GET['type']);
        }

        $user_multi = new UserMulti();
        $user_id = $user_multi->getIDFromUsername($_GET['username'], false);
        if (isset($user_id) === false || ctype_digit($user_id) === false) {
            throw new CHttpException(
                400,
                'Bad Request. $_GET["username"] is not a valid local user : ' . $_GET['username']
            );
        }


        if ($_GET['type'] === 'stream') {
            $block_number = Take::getStreamBlockNumber($this->model_id, $user_id, $_GET['time']);
            if ($block_number === false) {
                $block_number = Take::getNearestStreamBlockNumber($this->model_id, $user_id, $_GET['time']);
            }
        }

        if ($block_number === false) {
            $block_number = 0;
        }
        echo JSON::encode(
            array(
                'block_number' => $block_number,
            )
        );

    }

    /**
     * Stors the sort results for the owner of a stream so that they can be used as public results.
     *
     * @param array $p_filter_rhythm
     * @param array $p_posts
     * @param string [$p_top_parent_id] If this is a tree sort then this is the top parent.
     *
     * @return void
     */
    public function actionStoreOwnersStreamSortResults(array $p_filter_rhythm, array $p_posts=null,
        $p_top_parent_id=null
    ) {
        $rhythm_name_form = new RhythmNameForm($p_filter_rhythm);
        if ($rhythm_name_form->validate() === false) {
            $errors = ErrorHelper::model($rhythm_name_form->getErrors());
            throw new CHttpException(400, 'The rhythm object is not correctly structured: ' . $errors);
        }
        $p_filter_rhythm = $rhythm_name_form->rhythm;
        $site_id = SiteMulti::getSiteID($p_filter_rhythm['domain']);
        $user_multi = new UserMulti($site_id);
        $user_id = $user_multi->getIDFromUsername($p_filter_rhythm['username']);
        $rhythm_extra_id = Rhythm::getIDByName(
            $user_id,
            $p_filter_rhythm['name'],
            $p_filter_rhythm['version']['major'],
            $p_filter_rhythm['version']['minor'],
            $p_filter_rhythm['version']['patch']
        );
        if ($rhythm_extra_id === false) {
            throw new CHttpException(400, 'The rhythm has not been found');
        }

        $extra_id = $this->model->extra->stream_extra_id;
        StreamPublic::clearPublic($extra_id, $rhythm_extra_id, $p_top_parent_id);
        if (isset($p_posts) === false) {
            StreamPublicRhythm::removeRhythm($extra_id, $rhythm_extra_id, $p_top_parent_id);
        } else {
            $post_scores_form = new PostScoresForm;
            $post_scores_form->posts = $p_posts;
            $post_scores_form->top_parent_id = $p_top_parent_id;
            if ($post_scores_form->validate() === false) {
                $errors = ErrorHelper::model($post_scores_form->getErrors());
                throw new CHttpException(400, 'The posts object is not correctly structured: ' . $errors);
            }

            StreamPublic::storeGenerated($extra_id, $rhythm_extra_id, $p_posts, $p_top_parent_id);
            StreamPublicRhythm::addRhythm($extra_id, $rhythm_extra_id, $p_top_parent_id, $p_top_parent_id);
        }

        echo JSON::encode(array('success' => true));
    }

    /**
     *
     * @param type $g_field_id
     * @param type $g_text_to_fetch_suggestions_for
     * @throws CHttpException
     */
    public function actionOpenlistSuggestionsFetch($g_field_id, $g_text_to_fetch_suggestions_for) {
        if (ctype_digit($g_field_id) === false) {
            throw new CHttpException(400, 'Bad Request. field_id is not numeric');
        }
        $field_id = intval($g_field_id);

        $items = StreamOpenListItem::findSuggestions($this->model_id, $field_id, $g_text_to_fetch_suggestions_for);
        echo JSON::encode(array('suggestions' => $items));
    }

    /**
     * Fetches a stream extra id when passed the parts of a stream name.
     *
     * @param string $domain The username of the stream.
     * @param string $username The username of the stream.
     * @param string $name The name of the stream.
     * @param string $major The major version number of the stream.
     * @param string $minor The minor version number of the stream.
     * @param string $patch The patch version number of the stream.
     *
     * @return integer The streams extra id.
     */
    private function getStreamExtraIdFromStreamNameParts($username, $name, $major, $minor, $patch) {
        $stream_name_form = new StreamNameForm();
        $stream_name_form->makeStreamObject(
            Yii::app()->params['host'],
            $username,
            $name,
            $major,
            $minor,
            $patch
        );
        $stream_valid = $stream_name_form->validate();
        if ($stream_valid === false) {
            $stream_error =  ErrorHelper::model($stream_name_form->getErrors(), '<br />');
            throw new CHttpException(400, 'Bad Request. stream does not exist : ' . $stream_error);
        }
        $stream_extra_id = $stream_name_form->getFirstStreamExtraId();
        return $stream_extra_id;
    }

    /**
     * Fetch the available versions for this stream.
     *
     * @param string $g_user The username for this stream.
     * @param string $g_stream The name for this stream.
     * @param string $g_major The major version number for this stream.
     * @param string $g_minor The minor version number for this stream.
     * @param string $g_patch The patch version number for this stream.
     *
     * @return void
     */
    public function actionGetVersions($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $stream_id = StreamBedMulti::getIDFromExtraID($stream_extra_id);
        $versions = Version::getPublicVersions($stream_id, 'stream');

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
     * Fetch an exact version of a stream when given a 'latest' version.
     *
     * @param string $g_user The username for this stream.
     * @param string $g_stream The name for this stream.
     * @param string $g_major The major version number for this stream. Or 'latest'.
     * @param string $g_minor The minor version number for this stream. Or 'latest'.
     * @param string $g_patch The patch version number for this stream. Or 'latest'.
     *
     * @return void
     */
    public function actionGetExactVersions($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $json = array();
        $stream_name_form = new StreamNameForm();
        $stream_name_form->makeStreamObject(
            Yii::app()->params['host'],
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $stream_valid = $stream_name_form->validate();
        if ($stream_valid === false) {
            $json['error'] = 'This is not a valid stream.';
            $json['success'] = false;
        } else {
            $json['success'] = true;
            $json['streams'] = array();
            $stream_extra_ids = $stream_name_form->geAllStreamExtraIds();
            foreach ($stream_extra_ids as $stream_extra_id) {
                $stream_model = StreamBedMulti::getByIDWithExtra($stream_extra_id);
                $stream = array(
                    'domain' => Yii::app()->params['host'],
                    'username' => $stream_model->user->username,
                    'name' => $stream_model->name,
                    'version' => array(
                        'major' => $stream_model->extra->version->major,
                        'minor' => $stream_model->extra->version->minor,
                        'patch' => $stream_model->extra->version->patch,
                    ),
                );
                array_push($json['streams'], $stream);
            }
        }
        echo JSON::encode($json);
    }
}

?>