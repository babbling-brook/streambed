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
 * @fixme $version_string needs factoring out and replacing with $version_link.
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
     * Stream management links.
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
                'application.filters.UsernameFilter - posts'
            ),
            array(            // Must run before the remaining filters. Loads the model
                'application.filters.VersionFilter
                    + View,
                    Update,
                    Edit,
                    EditFields,
                    UpdateDescription,
                    UpdatePresentationType',
                'data_type' => 'stream',
                'version_type' => 'stream',
            ),
            array(
                'application.filters.PrivateStatusFilter + view'
            ),
            array(
                'application.filters.OwnerFilter
                    + Update,
                    UpdateDescription,
                    UpdatePresentationType,
                    Edit,
                    EditFields,
                    Delete'
            ),
            array(
                'application.filters.VersionRedirectFilter
                    + View,
                    Update,
                    Edit,
                    EditFields,
                    UpdateDescription,
                    UpdatePresentationType',
                'data_type' => 'stream',
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
                    'Index',
                    'View',
                    'Posts',
                    'GetChildStreams',
                    'GetDefaultRhythms',
                    'GetDefaultModerationRings',
                    'Versions',
                ),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(
                    'create',
                    'Make',
                    'Update',
                    'Edit',
                    'EditFields',
                    'UpdateDescription',
                    'UpdatePresentationType',
                    'NewVersion',
                    'delete',
                    'ChangeStatus',
                    'GetDeletableStatus',
                    'PostMode',
                    'AddDefaultRhythm',
                    'DeleteDefaultRhythm',
                    'ReplaceDefaultRhythm',
                    'SwapDefaultRhythm',
                    'AddChildStream',
                    'ReplaceChildStream',
                    'SwitchChildStreamPlaces',
                    'DeleteChildStream',
                    'SwapChildStream',
                    'AddDefaultModerationRing',
                    'DeleteDefaultModerationRing',
                    'ReplaceDefaultModerationRing',
                    'SwapDefaultModerationRing',
                    'DeleteAllOwnerPosts',
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
     * This is the public view of the posts, so that they may 'take' them.
     *
     * All take pages, serve up a static version if users are not logged in
     * and a customised version if they are.
     *
     * The rhythm action also redirect here.
     *
     * @param string $g_user The username of the user who owns this stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param string [$g_rhythm_domain] If a rhythm is specified in the url, then this is its domain.
     * @param string [$g_rhythm_user] If a rhythm is specified in the url, then this is its username.
     * @param string [$g_rhythm_name] If a rhythm is specified in the url, then this is its name.
     * @param string [$g_rhythm_major] If a rhythm is specified in the url, then this is its major version.
     * @param string [$g_rhythm_minor] If a rhythm is specified in the url, then this is its minor version.
     * @param string [$g_rhythm_patch] If a rhythm is specified in the url, then this is its patch version.
     *
     * @return void
     */
    public function actionPosts($g_user, $g_stream, $g_major, $g_minor, $g_patch, $g_page=null,
        $g_rhythm_domain=null, $g_rhythm_user=null, $g_rhythm_name=null,
        $g_rhythm_major=null, $g_rhythm_minor=null, $g_rhythm_patch=null
    ) {
        if (Yii::app()->user->isGuest === true) {
            $this->publicStream(
                $g_user,
                $g_stream,
                $g_major,
                $g_minor,
                $g_patch,
                $g_page,
                $g_rhythm_domain,
                $g_rhythm_user,
                $g_rhythm_name,
                $g_rhythm_major,
                $g_rhythm_minor,
                $g_rhythm_patch
            );
        } else {
            $this->authenticatedStream($g_user, $g_stream, $g_major, $g_minor, $g_patch);
        }
    }

    /**
     * The public version of the post page for when users are not logged in. Also for search engines.
     *
     * @param string $g_user The username of the user who owns this stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     *
     * @return void
     */
    protected function publicStream($g_user, $g_stream, $g_major, $g_minor, $g_patch, $g_page,
        $g_rhythm_domain, $g_rhythm_user, $g_rhythm_name, $g_rhythm_major, $g_rhythm_minor, $g_rhythm_patch
    ) {
        $this->public_stream_view = true;

        if (isset($g_page) === false) {
            $page = 1;
        } else if (isset($g_page) === true && ctype_digit($g_page) === false) {
            throw new CHttpException(400, 'Bad Request. page number is invalid : ' . $g_page);
        } else {
            $page = intval($g_page);
        }

        $user_id = User::getIDFromUsernameAndDomain($g_user, Yii::app()->params['host']);
        $stream = StreamBedMulti::getByName($user_id, $g_stream, $g_major, $g_minor, $g_patch);

        $rhythm_extra_id = 10017;  // defaults to popular recently.

        if (isset($g_rhythm_domain) === true) {
            $rhythm_user_id = User::getIDFromUsernameAndDomain($g_rhythm_user, $g_rhythm_domain);
            $rhythm_extra_id = Rhythm::getIDByName(
                $rhythm_user_id,
                $g_rhythm_name,
                $g_rhythm_major,
                $g_rhythm_minor,
                $g_rhythm_patch,
                $g_rhythm_domain
            );
        }
//        else {
// Turning off custom public sorts for now. Just using the defaults.
//            // If there is a default subscription for this stream then use its default filter rhythm.
//            foreach (Yii::app()->params['default_subscriptions'] as $subscription) {
//                if ($subscription['name'] === $g_stream && $subscription['username'] === $g_user) {
//                    $filter = $subscription['filters'][0];
//                    $rhythm_user_id = User::getIDFromUsernameAndDomain($filter['username'], $filter['domain']);
//                    $rhythm_extra_id = Rhythm::getIDByName(
//                        $rhythm_user_id,
//                        $filter['name'],
//                        $filter['version']['major'],
//                        $filter['version']['minor'],
//                        $filter['version']['patch'],
//                        $filter['domain']
//                    );
//                }
//            }
//        }

        $posts = StreamPublic::getPostsForStream($stream->extra->stream_extra_id, $rhythm_extra_id, $page);
        if (empty($posts) === true
            || time() - Yii::app()->params['public_post_cache_time'] > $posts[0]['time_cached']
        ) {
            switch($rhythm_extra_id) {
                case '10002' :  // newest
                    StreamPublic::makeNewestStream($stream, $rhythm_extra_id);
                    break;
                case '10011' :  // popular in last hour
                    StreamPublic::makePopularStream($stream, $rhythm_extra_id, 'hourly');
                    break;
                case '10012' :  // popular in last day
                    StreamPublic::makePopularStream($stream, $rhythm_extra_id, 'daily');
                    break;
                case '10013' :  // popular in last week
                    StreamPublic::makePopularStream($stream, $rhythm_extra_id, 'weekly');
                    break;
                case '10008' :  // skys priority.
                    StreamPublic::makeSkysStream($stream, $rhythm_extra_id);
                    break;
                case '10017' :  // last 5000 posts.
                    StreamPublic::makePopularStream($stream, $rhythm_extra_id, 'recently');
                    break;
//                default :
//                    throw new CHttpException(400, 'Bad Request. rhythm data is not valid for a public stream.');
            }
            $posts = StreamPublic::getPostsForStream($stream->extra->stream_extra_id, $rhythm_extra_id, $page);

//            // If there are still no posts then default to skysstream
//            if (empty($posts) === true) {
//                StreamPublic::makeSkysStream($stream, 10008);
//                $posts = StreamPublic::getPostsForStream($stream->extra->stream_extra_id, 10008, $page);
//            }
//            // If there are still no posts then default to newewst
//            if (empty($posts) === true) {
//                StreamPublic::makeNewestStream($stream, 10002);
//                $posts = StreamPublic::getPostsForStream($stream->extra->stream_extra_id, 10002, $page);
//            }

            //$rhythm_extra_id = false;
        }

        // If this is true then this is a specific rhythm sort request, only there are no results for the
        // rhythm. In which case we don't want to display the popular posts, but an empty list.
        // The posts still need calculating above so that the list is generated if it is selected.
        if ($rhythm_extra_id === 0) {
            $posts = array();
        }

        $rhythms = StreamPublicRhythm::getRhythmsStoredForStream($stream->extra->stream_extra_id);

        $presentation_type = LookupHelper::getValue($stream->extra->presentation_type_id);
        $view = '/Public/Page/Stream/List';

        switch ($presentation_type) {
            case 'list':
                $view = '/Public/Page/Stream/List';
                break;
            case 'photowall':
                $view = '/Public/Page/Stream/Photowall';
                break;
        }

        $this->render(
            $view,
            array(
                'stream' => $stream,
                'posts' => $posts,
                'rhythms' => $rhythms,
                'current_rhythm_extra_id' => $rhythm_extra_id,
                'selected_rhythm' => array(
                    'domain' => $g_rhythm_domain,
                    'username' => $g_rhythm_user,
                    'name' => $g_rhythm_name,
                    'major' => $g_rhythm_major,
                    'minor' => $g_rhythm_minor,
                    'patch' => $g_rhythm_patch,
                ),
                'full_stream_version' => array(
                    'major' => $g_major,
                    'minor' => $g_minor,
                    'patch' => $g_patch,
                ),
            )
        );
    }

    /**
     * The logged in version of the stream page.
     *
     * This is largely generic, with details fetched from javascript.
     *
     * @param string $g_user The username of the user who owns this stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     *
     * @return void
     */
    protected function authenticatedStream($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $this->render('/Client/Page/Stream/Stream');
    }

    /**
     * Lists all models.
     *
     * @return void
     */
    public function actionIndex() {
        $model = new Stream('search');
        $model->unsetAttributes();  // clear any default values

        if (isset($_GET['Stream']) === true) {
            $model->attributes=$_GET['Stream'];
        }

        $this->render(
            '/Client/Page/ManageStream/List',
            array(
                'model' => $model,
            )
        );
    }

    /**
     * Displays a particular model.
     *
     * @return void
     */
    public function actionView() {
        $model = $this->loadModel();

        if ($model->extra->status->value === 'private' && intval($model->user->user_id) !== Yii::app()->user->getId()) {
            throw new CHttpException(404, 'Stream not found.');
        }

        $versions = Version::getFamilyVersions($model->extra->version_id, 'stream');

        $this->render(
            '/Client/Page/ManageStream/View',
            array(
                'model' => $model,
                'versions' => $versions,
            )
        );
    }

    /**
     * Displays the page for making new streams.
     *
     * @return void
     */
    public function actionCreate() {
        $this->render('/Client/Page/ManageStream/Create');
    }




    /** TO MOVE TO THE SCENTIA DOMAIN **/


    /**
     * Ajax action for making a new stream. Called by javascript on actionCreate page.
     *
     * @param {string} $p_name The name of the new stream.
     * @param {string} $p_description The description of the new stream.
     * @param {string} $p_kind The kind of stream that this is.
     * @param {string} [$p_post_mode] The post_mode to start this post with.
     *
     * @returns void
     */
    public function actionMake($p_name, $p_description, $p_kind, $p_post_mode=null) {
        if (isset($p_post_mode) === false) {
            $p_post_mode = 'owner';
        }

        $stream_transaction = new NewStream($p_name, $p_description, $p_kind, $p_post_mode);
        $success = $stream_transaction->getSuccess();
        $errors = $stream_transaction->getErrors();
        echo JSON::encode(
            array(
                'success' => $success,
                'errors' => $errors,
            )
        );
    }

    /**
     * Reports if a stream is deletable or not.
     *
     * @param string $g_user The username of the stream whose deletable status is being reported.
     * @param string $g_stream The name of the stream whose deletable status is being reported.
     * @param string $g_major The major version number of the stream whose deletable status is being reported.
     * @param string $g_minor The minor version number of the stream whose deletable status is being reported.
     * @param string $g_patch The patch version number of the stream whose deletable status is being reported.
     *
     * @return void
     */
    public function actionGetDeletableStatus($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $status = StreamExtra::getStatus($stream_extra_id);
        if ($status === 'private') {
            $this->throwErrorIfNotOwner($stream_extra_id);
        }

        $deletable = StreamMulti::isDeletable($stream_extra_id, Yii::app()->user->getId());
        $json = array();
        $json['success'] = true;
        $json['deletable'] = $deletable;
        echo JSON::encode($json);
    }

    /**
     * Handles requests to change the status of an stream by its owner.
     *
     * @param string $g_user The username of the stream whos status is being changed.
     * @param string $g_stream The name of the stream whos status is being changed.
     * @param string $g_major The major version number of the stream whos status is being changed.
     * @param string $g_minor The minor version number of the stream whos status is being changed.
     * @param string $g_patch The patch version number of the stream whos status is being changed.
     * @param string $p_action What kind of request is this. Valid values are 'publish', 'deprecate' and 'delete'.
     *
     * @return void
     */
    public function actionChangeStatus($g_user, $g_stream, $g_major, $g_minor, $g_patch, $p_action) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $is_deletable = StreamMulti::isDeletable($stream_extra_id, Yii::app()->user->getId());

        if ($p_action !== 'publish' && $p_action !== 'deprecate' && $p_action !== 'delete' && $p_action !== 'revert') {
            $error = 'Action is not a valid status request action. Should be "publish", "deprecate" or "delete".'
                . ' Given ' . $p_action;
        } else {
            switch ($p_action) {
                case 'publish':
                    StreamBedMulti::updateStatus($stream_extra_id, StatusHelper::getID('public'));
                    break;

                case 'delete':
                    $stream = StreamBedMulti::getByID($stream_extra_id);
                    if ($is_deletable === false) {
                        $error = 'Only private streams can be deleted.';
                    } else {
                        StreamMulti::deleteStream($stream_extra_id);
                    }
                    break;

                case 'deprecate':
                    $stream = StreamBedMulti::getByID($stream_extra_id);
                    if ((int)$stream->extra->status_id !== StatusHelper::getID('public')) {
                        $error = 'Only public streams can be deprecated.';
                    } else {
                        StreamBedMulti::updateStatus($stream_extra_id, StatusHelper::getID('deprecated'));
                    }
                    break;

                case 'revert':
                    $stream = StreamBedMulti::getByID($stream_extra_id);
                    if ((int)$stream->extra->status_id === StatusHelper::getID('private')) {
                        $error = 'Private streams cannot be revertrd.';
                    } else if ($is_deletable === false) {
                            $error = 'Stream cannot be reverted to draft status. It contains posts by other users.'
                                . ' Create a new version instead.';
                    } else {
                        StreamBedMulti::updateStatus($stream_extra_id, StatusHelper::getID('private'));
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
     * Deletes all posts by the owner of the stream.
     *
     * Called when the owner wants to edit the fields.
     *
     * @param string $g_user The username of the stream whos status is being changed.
     * @param string $g_stream The name of the stream whos status is being changed.
     * @param string $g_major The major version number of the stream whos status is being changed.
     * @param string $g_minor The minor version number of the stream whos status is being changed.
     * @param string $g_patch The patch version number of the stream whos status is being changed.
     *
     * @return void
     */
    public function actionDeleteAllOwnerPosts($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $are_there_posts_not_by_the_owner = Post::areTherePostsNotByOwner($stream_extra_id, Yii::app()->user->getId());
        if ($are_there_posts_not_by_the_owner === true) {
            $error = 'Can only perform this action if there are no postsby other users.';
        } else {
            Post::deleteStreamOwnerPosts($stream_extra_id, Yii::app()->user->getId());
        }
        $json = array();
        if (isset($error) === true) {
            $json['error'] = $error;
            $json['success'] = false;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Change the post mode for an stream.
     *
     * @param string $p_post_mode_id The lookup id of the stream mode to change this stream to.
     * @param string $p_stream_extra_id The extra id of the stream to change.
     *
     * @return void
     */
    public function actionPostMode($p_post_mode_id, $p_stream_extra_id) {

        if (ctype_digit($p_stream_extra_id) === false) {
            throw new CHttpException(400, 'Bad Request. stream_extra_id is not numeric');
        }

        if (ctype_digit($p_post_mode_id) === false) {
            throw new CHttpException(400, 'Bad Request. post_mode_id is not numeric');
        }

        // Check ownership. Also assertains that $p_stream_extra_id exists.
        $user_id = Yii::app()->user->getId();
        if (StreamBedMulti::checkOwnerExtra($p_stream_extra_id, $user_id) === false) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        if (LookupHelper::validId("stream_extra.post_mode", $p_post_mode_id, false) === false) {
            throw new CHttpException(400, 'Bad Request. post_mode_id is not valid.');
        }

        $sucesss = StreamExtra::updatePostMode($p_stream_extra_id, $p_post_mode_id);

        echo JSON::encode(array('success' => $sucesss));
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
    public function actionVersions($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $stream_id = StreamBedMulti::getIDFromExtraID($stream_extra_id);
        $versions = Version::getPublicVersions($stream_id, 'stream');
        $partial_versions = Version::getPartialVersions($versions);

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
            $json['versions'] = $partial_versions;
        }
        echo JSON::encode($json);
    }

    /**
     * Fetches thechid streams for a stream.
     *
     * Echos a json array with rows in the following format:
     * {
     *  domain : <stream_domain>,
     *  username : <atream_username>,
     *  name : <stream_name>,
     *  version : {
     *   major : <major_version>|'latest',
     *   minor : <minor_version>|'latest',
     *   patch : <patch_version>|'latest',
     *  },
     *  sort_order : <integer>
     * }
     *
     * @param string $g_user The username of the parent stream.
     * @param string $g_stream The name of the parent stream.
     * @param string $g_major The major version number of the parent stream.
     * @param string $g_minor The minor version number of the parent stream.
     * @param string $g_patch The patch version number of the parent stream.
     *
     * @return void
     */
    public function actionGetChildStreams($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $child_streams = StreamChild::getChildrenInNameFormat($stream_extra_id);
        $json = array();
        if (is_string($child_streams) === true) {
            $json['success'] = false;
            $json['error'] = $child_streams;
        } else {
            $json['success'] = true;
            $json['items'] = $child_streams;
        }
        echo JSON::encode($json);
    }

    /**
     * Adds a child stream to a stream.
     *
     * @param string $g_user The username of the parent stream.
     * @param string $g_stream The name of the parent stream.
     * @param string $g_major The major version number of the parent stream.
     * @param string $g_minor The minor version number of the parent stream.
     * @param string $g_patch The patch version number of the parent stream.
     * @param array $p_name A standard json stream name object for the child stream.
     *
     * @return void
     */
    public function actionAddChildStream($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_name
    ) {
        $parent_stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($parent_stream_extra_id);

        $child_stream_name_form = new StreamNameForm($p_name);
        $child_stream_valid = $child_stream_name_form->validate();
        if ($child_stream_valid === false) {
            $error = 'Stream url invalid : <br />' .
                ErrorHelper::model($child_stream_name_form->getErrors(), '<br />');
        }

        if (isset($error) === false) {
            $child_stream_extra_id = $child_stream_name_form->getFirstStreamExtraId();
            $version_type_id = Version::getTypeId(
                $p_name['version']['major']
                . '/' . $p_name['version']['minor']
                . '/' . $p_name['version']['patch']
            );
            $success = StreamChild::addChild($parent_stream_extra_id, $child_stream_extra_id, $version_type_id);
            if ($success !== true) {
                $errors = ErrorHelper::model($success, '<br />');
                throw newCHttpException(401, 'An error occurred when insering child stream : ' . $errors);
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Switches around the display order for two child streams.
     *
     * @param string $g_user The username of the parent stream.
     * @param string $g_stream The name of the parent stream.
     * @param string $g_major The major version number of the parent stream.
     * @param string $g_minor The minor version number of the parent stream.
     * @param string $g_patch The patch version number of the parent stream.
     * @param array $p_name1 A standard full stream name object representing one of the streams to switch.
     * @param array $p_name2 A standard full stream name object representing one of the streams to switch.
     *
     * @return void
     */
    public function actionSwitchChildStreamPlaces($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_name1, array $p_name2
    ) {
        $parent_stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($parent_stream_extra_id);

        $stream_name_form_1 = new StreamNameForm($p_name1);
        $stream_1_valid = $stream_1_valid->validate();
        if ($stream_1_valid === false) {
            $error = 'Stream url invalid : <br />' .
                ErrorHelper::model($stream_name_form_1->getErrors(), '<br />');
        }

        $stream_name_form_2 = newStreamNameForm($p_name2);
        $stream_2_valid = $stream_1_valid->validate();
        if ($stream_2_valid === false) {
            $error = 'Stream url invalid : <br />' .
                ErrorHelper::model($stream_name_form_2->getErrors(), '<br />');
        }

        if (isset($error) === false) {
            $stream_extra_id_1 = $stream_name_form_1->getFirstStreamExtraId();
            $stream_extra_id_2 = $stream_name_form_2->getFirstStreamExtraId();

            $version_type_1_id = Version::getTypeId(
                $p_name1['version']['major']
                . '/' . $p_name1['version']['minor']
                . '/' . $p_name1['version']['patch']
            );
            $version_type_2_id = Version::getTypeId(
                $p_name2['version']['major']
                . '/' . $p_name2['version']['minor']
                . '/' . $p_name2['version']['patch']
            );

            $result = StreamChild::switchChildrensDisplayOrder(
                $parent_stream_extra_id,
                $stream_extra_id_1,
                $version_type_1_id,
                $stream_extra_id_2,
                $version_type_2_id
            );
            if (is_string($result) === true) {
                $error = $result;
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Replace a child stream witha another one.
     *
     * @param string $g_user The username of the parent stream.
     * @param string $g_stream The name of the parent stream.
     * @param string $g_major The major version number of the parent stream.
     * @param string $g_minor The minor version number of the parent stream.
     * @param string $g_patch The patch version number of the parent stream.
     * @param array $p_old_name A standard full stream name object representing the child stream to replace.
     * @param array $p_new_name A standard full stream name object representing the new child stream.
     *
     * @return void
     */
    public function actionReplaceChildStream($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_old_name, array $p_new_name
    ) {
        $parent_stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($parent_stream_extra_id);

        $old_stream_name_form = new StreamNameForm($p_old_name);
        $old_stream_valid = $old_stream_name_form->validate();
        if ($old_stream_valid === false) {
            $error = 'Stream url invalid : <br />' .
                ErrorHelper::model($old_stream_name_form->getErrors(), '<br />');
        }

        $new_stream_name_form = new StreamNameForm($p_new_name);
        $new_stream_valid = $new_stream_name_form->validate();
        if ($new_stream_valid === false) {
            $error = 'Stream url invalid : <br />' .
                ErrorHelper::model($old_stream_name_form->getErrors(), '<br />');
        }

        if (isset($error) === false) {
            $old_stream_extra_id = $old_stream_name_form->getFirstStreamExtraId();
            $new_stream_extra_id = $new_stream_name_form->getFirstStreamExtraId();

            $old_version_type_id = Version::getTypeId(
                $p_old_name['version']['major']
                . '/' . $p_old_name['version']['minor']
                . '/' . $p_old_name['version']['patch']
            );
            $new_version_type_id = Version::getTypeId(
                $p_new_name['version']['major']
                . '/' . $p_new_name['version']['minor']
                . '/' . $p_new_name['version']['patch']
            );

            $result = StreamChild::replaceChildStream(
                $parent_stream_extra_id,
                $old_stream_extra_id,
                $old_version_type_id,
                $new_stream_extra_id,
                $new_version_type_id
            );
            if ($result !== true) {
                $error = 'An error occurred when saving the new child stream : '
                    . ErrorHelper::model($result, '<br />');
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Removes a child stream from a stream.
     *
     * @param string $g_user The username of the parent stream.
     * @param string $g_stream The name of the parent stream.
     * @param string $g_major The major version number of the parent stream.
     * @param string $g_minor The minor version number of the parent stream.
     * @param string $g_patch The patch version number of the parent stream.
     * @param array $p_name A standard json stream name object representing the child stream to delete.
     */
    public function actionDeleteChildStream($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_name
    ) {
        $parent_stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($parent_stream_extra_id);

        $stream_name_form = new StreamNameForm($p_name);
        $stream_valid = $stream_name_form->validate();
        if ($stream_valid !== false) {
            $child_stream_extra_id = $stream_name_form->getFirstStreamExtraId();

            $version_type_id = Version::getTypeId(
                $p_name['version']['major']
                . '/' . $p_name['version']['minor']
                . '/' . $p_name['version']['patch']
            );
            $error = StreamChild::deleteChild($parent_stream_extra_id, $child_stream_extra_id, $version_type_id);
        } else {
            $error = ErrorHelper::model($stream_name_form->getErrors(), '<br />');
        }
        $json = array();
        if (is_string($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Swap one child streams sort order with a another one.
     *
     * @param string $g_user The username of the parent stream.
     * @param string $g_stream The name of the parent stream.
     * @param string $g_major The major version number of the parent stream.
     * @param string $g_minor The minor version number of the parent stream.
     * @param string $g_patch The patch version number of the parent stream.
     * @param array $p_item_name_1 A standard full stream name object representing the first child stream to swap.
     * @param array $p_item_name_2 A standard full stream name object representing the second child stream to swap.
     *
     * @return void
     */
    public function actionSwapChildStream($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_item_name_1, array $p_item_name_2
    ) {
        $parent_stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($parent_stream_extra_id);

        $stream_name_form_1 = new StreamNameForm($p_item_name_1);
        $stream_valid_1 = $stream_name_form_1->validate();
        if ($stream_valid_1 === false) {
            $error = 'Stream url invalid : <br />' .
                ErrorHelper::model($stream_name_form_1->getErrors(), '<br />');
        }

        $stream_name_form_2 = new StreamNameForm($p_item_name_2);
        $stream_valid_2 = $stream_name_form_2->validate();
        if ($stream_valid_2 === false) {
            $error = 'Stream url invalid : <br />' .
                ErrorHelper::model($stream_name_form_2->getErrors(), '<br />');
        }

        if (isset($error) === false) {
            $stream_extra_id_1 = $stream_name_form_1->getFirstStreamExtraId();
            $stream_extra_id_2 = $stream_name_form_2->getFirstStreamExtraId();

            $version_type_id_1 = Version::getTypeId(
                $p_item_name_1['version']['major']
                . '/' . $p_item_name_1['version']['minor']
                . '/' . $p_item_name_1['version']['patch']
            );
            $version_type_id_2 = Version::getTypeId(
                $p_item_name_2['version']['major']
                . '/' . $p_item_name_2['version']['minor']
                . '/' . $p_item_name_2['version']['patch']
            );

            $result = StreamChild::swapChildStream(
                $parent_stream_extra_id,
                $stream_extra_id_1,
                $version_type_id_1,
                $stream_extra_id_2,
                $version_type_id_2
            );
            if (is_string($result) === true) {
                $error = $result;
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Fetches the default rhythms for a stream.
     *
     * Echos a json array with rows in the following format:
     * {
     *  domain : <rhythm_domain>,
     *  username : <rhythm_username>,
     *  name : <rhythm_name>,
     *  version : {,
     *   major : <major_version>|'latest',
     *   minor : <minor_version>|'latest',
     *   patch : <patch_version>|'latest',
     *  },
     *  sort_order : <integer>
     * }
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     *
     * @return void
     */
    public function actionGetDefaultRhythms($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $default_rhythms = StreamDefaultRhythm::getDefaults($stream_extra_id);
        $json = array();
        if (is_string($default_rhythms) === true) {
            $json['success'] = false;
            $json['error'] = $default_rhythms;
        } else {
            $json['success'] = true;
            $json['items'] = $default_rhythms;
        }
        echo JSON::encode($json);
    }

    /**
     * Add a default rhythm to a stream.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param array $p_name A standard json rhythm name object.
     *
     * @return void
     */
    public function actionAddDefaultRhythm($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_name
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $rhythm_name_form = new RhythmNameForm($p_name);
        $rhythm_valid = $rhythm_name_form->validate();
        if ($rhythm_valid !== false) {
            $rhythm_extra_id = $rhythm_name_form->getRhythmExtraId();

            $version_type_id = Version::getTypeId(
                $p_name['version']['major']
                . '/' . $p_name['version']['minor']
                . '/' . $p_name['version']['patch']
            );
            $error = StreamDefaultRhythm::insertDefault($stream_extra_id, $rhythm_extra_id, $version_type_id);
        } else {
            $error = ErrorHelper::model($rhythm_name_form->getErrors(), '<br />');
        }
        $json = array();
        if (is_string($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Removes a default rhythm from a stream.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param array $p_name A standard json rhythm name object.
     */
    public function actionDeleteDefaultRhythm($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_name
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $rhythm_name_form = new RhythmNameForm($p_name);
        $rhythm_valid = $rhythm_name_form->validate();
        if ($rhythm_valid !== false) {
            $rhythm_extra_id = $rhythm_name_form->getRhythmExtraId();

            $version_type_id = Version::getTypeId(
                $p_name['version']['major']
                . '/' . $p_name['version']['minor']
                . '/' . $p_name['version']['patch']
            );
            $error = StreamDefaultRhythm::deleteDefault($stream_extra_id, $rhythm_extra_id, $version_type_id);
        } else {
            $error = ErrorHelper::model($rhythm_name_form->getErrors(), '<br />');
        }
        $json = array();
        if (is_string($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Replaces one rhythm with another.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param array $p_old_name A standard full rhythm name object.
     * @param array $p_new_name A standard full rhythm name object.
     */
    public function actionReplaceDefaultRhythm($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_old_name, array $p_new_name
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $old_rhythm_name_form = new RhythmNameForm($p_old_name);
        $old_rhythm_valid = $old_rhythm_name_form->validate();
        if ($old_rhythm_valid === false) {
            $error = 'Old Rhythm url invalid : <br />' .
                ErrorHelper::model($old_rhythm_name_form->getErrors(), '<br />');
        }

        $new_rhythm_name_form = new RhythmNameForm($p_new_name);
        $new_rhythm_valid = $new_rhythm_name_form->validate();
        if ($new_rhythm_valid === false) {
            $error = 'New Rhythm url invalid : <br />' .
                ErrorHelper::model($new_rhythm_name_form->getErrors(), '<br />');
        }

        if (isset($error) === false) {
            $old_rhythm_extra_id = $old_rhythm_name_form->getRhythmExtraId();
            $new_rhythm_extra_id = $new_rhythm_name_form->getRhythmExtraId();

            $old_version_type_id = Version::getTypeId(
                $p_old_name['version']['major']
                . '/' . $p_old_name['version']['minor']
                . '/' . $p_old_name['version']['patch']
            );
            $new_version_type_id = Version::getTypeId(
                $p_new_name['version']['major']
                . '/' . $p_new_name['version']['minor']
                . '/' . $p_new_name['version']['patch']
            );

            $result = StreamDefaultRhythm::switchRhythmExtraId(
                $stream_extra_id,
                $old_rhythm_extra_id,
                $old_version_type_id,
                $new_rhythm_extra_id,
                $new_version_type_id
            );
            if (is_string($result) === true) {
                $error = $result;
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }


    /**
     * Switches the display order for two default rhythms.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param array $p_item_name_1 A standard full rhythm name object.
     * @param array $p_item_name_2 A standard full rhythm name object.
     */
    public function actionSwapDefaultRhythm($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_item_name_1, array $p_item_name_2
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $hythm_name_form_1 = new RhythmNameForm($p_item_name_1);
        $rhythm_1_valid = $hythm_name_form_1->validate();
        if ($rhythm_1_valid === false) {
            $error = 'Rhythm url invalid : <br />' .
                ErrorHelper::model($hythm_name_form_1->getErrors(), '<br />');
        }

        $hythm_name_form_2 = new RhythmNameForm($p_item_name_2);
        $rhythm_2_valid = $hythm_name_form_2->validate();
        if ($rhythm_2_valid === false) {
            $error = 'Rhythm url invalid : <br />' .
                ErrorHelper::model($hythm_name_form_2->getErrors(), '<br />');
        }

        if (isset($error) === false) {
            $rhythm_extra_id_1 = $hythm_name_form_1->getRhythmExtraId();
            $rhythm_extra_id_2 = $hythm_name_form_2->getRhythmExtraId();

            $version_type_id_1 = Version::getTypeId(
                $p_item_name_1['version']['major']
                . '/' . $p_item_name_1['version']['minor']
                . '/' . $p_item_name_1['version']['patch']
            );
            $version_type_id_2 = Version::getTypeId(
                $p_item_name_2['version']['major']
                . '/' . $p_item_name_2['version']['minor']
                . '/' . $p_item_name_2['version']['patch']
            );

            $result = StreamDefaultRhythm::swapRhythms(
                $stream_extra_id,
                $rhythm_extra_id_1,
                $version_type_id_1,
                $rhythm_extra_id_2,
                $version_type_id_2
            );
            if (is_string($result) === true) {
                $error = $result;
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Fetches the default rhythms for a stream.
     *
     * Echos a json array with rows in the following format:
     * {
     *  domain : <rhythm_domain>,
     *  username : <rhythm_username>,
     *  sort_order : <integer>
     * }
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     *
     * @return void
     */
    public function actionGetDefaultModerationRings($g_user, $g_stream, $g_major, $g_minor, $g_patch) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $rings = StreamDefaultRing::getDefaults($stream_extra_id, false);
        $json = array();
        if (is_string($rings) === true) {
            $json['success'] = false;
            $json['error'] = $rings;
        } else {
            $json['success'] = true;
            $json['items'] = $rings;
        }
        echo JSON::encode($json);
    }

    /**
     * Add a default moderation ring to a stream.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param array $p_name A standard json ring name object.
     *
     * @return void
     */
    public function actionAddDefaultModerationRing($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_name
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $user_name_form = new UserNameForm($p_name);
        $user_valid = $user_name_form->validate();
        if ($user_valid !== false) {
            $user_id = $user_name_form->getUserId();
            $error = StreamDefaultRing::insertRing($stream_extra_id, $user_id);
        } else {
            $error = ErrorHelper::model($user_name_form->getErrors(), '<br />');
        }
        $json = array();
        if (is_string($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Delete a default moderation ring from a stream.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param array $p_name A standard json user name object.
     */
    public function actionDeleteDefaultModerationRing($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_name
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $user_name_form = new UserNameForm($p_name);
        $user_valid = $user_name_form->validate();
        if ($user_valid !== false) {
            $user_id = $user_name_form->getUserId();
            $error = StreamDefaultRing::removeRing($stream_extra_id, $user_id);
        } else {
            $error = ErrorHelper::model($user_name_form->getErrors(), '<br />');
        }
        $json = array();
        if (is_string($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Replaces one moderation ring with another.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param array $p_old_name A standard full user name object.
     * @param array $p_new_name A standard full user name object.
     *
     * @return void
     */
    public function actionReplaceDefaultModerationRing($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_old_name, array $p_new_name
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);

        $old_name_form = new UserNameForm($p_old_name);
        $old_name_valid = $old_name_form->validate();
        if ($old_name_valid === false) {
            $error = 'Old name url invalid : <br />' .
                ErrorHelper::model($old_name_form->getErrors(), '<br />');
        }

        $new_name_form = new UserNameForm($p_new_name);
        $new_name_valid = $new_name_form->validate();
        if ($new_name_valid === false) {
            $error = 'New ring url is invalid : <br />' .
                ErrorHelper::model($new_name_form->getErrors(), '<br />');
        }

        if (isset($error) === false) {
            $old_user_id = $old_name_form->getUserId();
            $new_user_id = $new_name_form->getUserId();

            $result = StreamDefaultRing::switchRings(
                $stream_extra_id,
                $old_user_id,
                $new_user_id
            );
            if (is_string($result) === true) {
                $error = $result;
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }


    /**
     * Replaces one moderation ring with another.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param array $p_item_name_1 A standard full user name object.
     * @param array $p_item_name_2 A standard full user name object.
     *
     * @return void
     */
    public function actionSwapDefaultModerationRing($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, array $p_item_name_1, array $p_item_name_2
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);
        $p_item_name_1['is_ring'] = true;
        $name_form_1 = new UserNameForm($p_item_name_1);
        $name_valid_1 = $name_form_1->validate();
        if ($name_valid_1 === false) {
            $error = 'Old name url invalid : <br />' .
                ErrorHelper::model($name_form_1->getErrors(), '<br />');
        }

        $p_item_name_2['is_ring'] = true;
        $name_form_2 = new UserNameForm($p_item_name_2);
        $name_valid_2 = $name_form_2->validate();
        if ($name_valid_2 === false) {
            $error = 'New ring url is invalid : <br />' .
                ErrorHelper::model($name_form_2->getErrors(), '<br />');
        }

        if (isset($error) === false) {
            $user_id_1 = $name_form_1->getUserId();
            $user_id_2 = $name_form_2->getUserId();

            $result = StreamDefaultRing::swapDefaultRings(
                $stream_extra_id,
                $user_id_1,
                $user_id_2
            );
            if (is_string($result) === true) {
                $error = $result;
            }
        }

        $json = array();
        if (isset($error) === true) {
            $json['success'] = false;
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Make a new version of a stream.
     *
     * @param string $g_user The username of the stream.
     * @param string $g_stream The name of the stream.
     * @param string $g_major The major version number of the stream.
     * @param string $g_minor The minor version number of the stream.
     * @param string $g_patch The patch version number of the stream.
     * @param string $p_new_version The new version number in 'minor/major/patch' format.
     *
     * @return void
     */
    public function actionNewVersion($g_user, $g_stream, $g_major, $g_minor,
        $g_patch, $p_new_version
    ) {
        $stream_extra_id = $this->getStreamExtraIdFromStreamNameParts(
            $g_user,
            $g_stream,
            $g_major,
            $g_minor,
            $g_patch
        );
        $this->throwErrorIfNotOwner($stream_extra_id);
        $model = StreamBedMulti::getByIDWithExtra($stream_extra_id);

        if ($p_new_version === 'No change') {
            $error = 'Please select a new version.';
        } else {
            $result = StreamMulti::newVersion($model, $p_new_version);
        }

        $json = array();
        if (is_string($result) === true) {
            $json['error'] = $result;
        } else if (isset($error) === true) {
            $json['error'] = $error;
        } else {
            $json['url'] = '/' . $this->username .'/stream/' . $model->name . '/' . $_POST['new_version'] . '/update';
        }
        echo JSON::encode($json);
    }

    /**
     * Updates a particular model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @return void
     */
    public function actionUpdate() {
        if (isset($_POST['duplicate_name']) === true) {
            $this->model = $this->duplicate($this->model);
            return;
        } else if (isset($_POST['new_version']) === true) {
            $this->newVersion($this->model);
            return;
        }

        $this->render(
            '/Client/Page/ManageStream/UpdateTop',
            array(
                'model' => $this->model,
                'update_template' => 'Update',
            )
        );
    }

    /**
     * Updates thedescription for a stream.
     *
     * @param $p_description The new description for this stream.
     *
     * @return void
     */
    public function actionUpdateDescription($p_description) {
        if ((int)$this->model->extra->status_id !== StatusHelper::getID('private')) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        $this->model->extra->description = $p_description;
        $json = array();
        $this->model->extra->save();
        $error = $this->model->extra->getError('description');
        if ($error !== null) {
            $json['error'] =  $error;
        } else {
            $error = false;
        }
        echo JSON::encode($json);
    }

    /**
     * Updates thedescription for a stream.
     *
     * @param $p_presentation_type The new presentation type for this stream.
     *
     * @return void
     */
    public function actionUpdatePresentationType($p_presentation_type) {
        if ((int)$this->model->extra->status_id !== StatusHelper::getID('private')) {
            throw new CHttpException(403, 'The requested page is forbidden.');
        }

        $json = array();
        $this->model->extra->presentation_type = $p_presentation_type;
        $this->model->extra->save();
        $error = $this->model->extra->getError('presentation_type_id');
        if ($error !== null) {
            $json['error'] = $error;
        } else {
            $json['success'] = true;
        }
        echo JSON::encode($json);
    }

    /**
     * Provides the edit view for updating a stream.
     *
     * @return void
     */
    public function actionEdit() {
        $this->render(
            '/Client/Page/ManageStream/UpdateTop',
            array(
                'model' => $this->model,
                'update_template' => 'Edit',
            )
        );
    }


    /**
     * Provides the view for editing fields.
     *
     * @return void
     */
    public function actionEditFields() {
        $this->render(
            '/Client/Page/ManageStream/UpdateTop',
            array(
                'model' => $this->model,
                'update_template' => 'EditFields',
            )
        );
    }

    /**
     * Duplicates an existing stream with a new name.
     *
     * @param Stream $model The model from the database that matches this postback.
     *
     * @return void
     */
    protected function duplicate($model) {
        $result = StreamMulti::duplicateStream($model, $_POST['duplicate_name']);

        $json = array();
        if (is_string($result) === true) {
            $json['error'] = $result;
        } else {
            $json['url'] = '/' . $this->username .'/stream/' . $result->name . '/' . $this->version_string . '/update';
        }
        echo JSON::encode($json);
    }

    /**
     * Generate the operations menu for displaying on stream pages.
     *
     * @param string $active Name of the calling action, so that it can be styled as current on the menu.
     *
     * @return array Operations Menu.
     */
    protected function operationsMenu($active='') {
        if ($this->username === Yii::app()->user->getName()) {
            $create = array(
                'label' => 'Create',
                'url' => '/' . $this->username . '/streams/create',
                'active' => $active === 'create',
                'itemOptions' => array('id' => 'create_stream_operation'),
                'linkOptions' => array(
                    'title' => 'Create a new stream',
                )
            );
            if ($active === 'view' || $active === 'actions' || $active === 'take'
                || $active === 'edit' || $active === 'fields'
            ) {
                $actions =  array(
                    'label' => 'Actions',
                    'url' => '/' . $this->username .'/stream' . $this->version_link . '/update',
                    'active' => $active === 'actions',
                    'itemOptions' => array('id' => 'update_stream_operation'),
                    'linkOptions' => array(
                        'title' => 'Publish and copy this stream.',
                    )
                );
                $edit =  array(
                    'label' => 'Edit',
                    'url' => '/' . $this->username .'/stream' . $this->version_link . '/edit',
                    'active' => $active === 'edit',
                    'itemOptions' => array('id' => 'edit_stream_operation'),
                    'linkOptions' => array(
                        'title' => 'Edit this stream.',
                    )
                );
                $fields =  array(
                    'label' => 'Edit Fields',
                    'url' => '/' . $this->username .'/stream' . $this->version_link . '/editfields',
                    'active' => $active === 'fields',
                    'itemOptions' => array('id' => 'edit_stream_fields_operation'),
                    'linkOptions' => array(
                        'title' => 'Edit the fields of this stream.',
                    )
                );
            }
        }

        $index = array(
            'label' => 'List',
            'url' => '/' . $this->username .'/streams/',
            'active' => $active === 'view',
            'itemOptions' => array('id' => 'list_stream_operation'),
            'linkOptions' => array(
                'title' => 'View a list of all streams by this user',
            )
        );

        if ($active === 'view' || $active === 'actions' || $active === 'take'
            || $active === 'edit' || $active === 'fields'
        ) {
            $view = array(
                'label' => 'Details',
                'url' => '/' . $this->username . '/stream' . $this->version_link . '/view',
                'active' => $active === 'view',
                'itemOptions' => array('id' => 'stream_details_operation'),
                'linkOptions' => array(
                    'title' => 'View this stream',
                )
            );
            $take = array(
                'label' => 'Posts',
                'url' => '/' . $this->username . '/stream' . $this->version_link,
                'active' => $active === 'take',
                'itemOptions' => array('id' => 'stream_posts_operation'),
                'linkOptions' => array(
                    'title' => 'Post and take using this stream',
                )
            );
        }

        $empty = array(
            'label' => '',
            'url' => '',
            'active' => false,
            'itemOptions' => array(
                'class' => 'spacer',
                'id' => 'stream_spacer_operation',
            )
        );

        $menu_array = array();

        if (isset($view) === true) {
            array_push($menu_array, $view);
        }
        if (isset($actions) === true) {
            array_push($menu_array, $actions);
        }
        if (isset($edit) === true) {
            array_push($menu_array, $edit);
        }
        if (isset($fields) === true) {
            array_push($menu_array, $fields);
        }
        if (isset($take) === true) {
            array_push($menu_array, $take);
        }
        if (isset($view) === true || isset($actions) === true || isset($edit) === true
            || isset($fields) === true || isset($take) === true
        ) {
            array_push($menu_array, $empty);
        }

        if (isset($index) === true) {
            array_push($menu_array, $index);
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
     * @return Stream Model
     */
    protected function loadModel() {
        if ($this->model === null) {
            if (isset($this->stream_id) === true) {
                $this->model = StreamBedMulti::getPost($this->stream_id, $this->user_id);
            }
            if ($this->model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
            }
        }
        return $this->model;
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
     * Verifies that the logged on user  owns this stream.
     *
     * @param type $stream_extra_id
     *
     * @return void
     */
    private function throwErrorIfNotOwner($stream_extra_id) {
        $stream_owner_id = StreamExtra::getOwnerID($stream_extra_id);
        if (intval($stream_owner_id) !== Yii::app()->user->getId()) {
            throw new CHttpException(403, 'Access Denied.');
        }
    }

}

?>