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
 * Post controller
 *
 * @package PHP_Controllers
 */
class PostController extends Controller
{


    /**
     * Filters to restrict access and precalculate common functionality.
     *
     * @return array action filters.
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
                'allow',
                'actions' => array('json', 'subposts', 'takes', 'revisions', 'UserTake'),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(''),
                'users' => array('@'), // allow authenticated user
            ),
            array(
                'deny',
                'users' => array('*'),  // deny all other users
            ),
        );
    }

    /**
     * Returns full post details in json.
     *
     * This is always the posts home, so site scientia is irrelevant.
     * This data is accessed via a post, but all the data needed to make the request is in the url.
     *
     * @param string $g_post_id The id of the post to fetch.
     * @param string [$p_private=false] If this is set then only return the post if it is private.
     *      Otherwise only public posts are fetched.
     * @param string [$g_revision] If this is set then return the specific revision,
     *      otherwise the latest version is fetched.
     *
     *
     * @return void
     * @refactor protocol needs redesigning along with stream requests. Currently a mess as to when a stream needs
     *      attatching to an post.
     */
    public function actionJson($g_post_id, $p_private='false', $g_revision='latest') {

        // This is to present fake data to test that the correct error is raised when post data is corrupt.
        // This needs to use an post that is not in the first page, to make other tests simpler.
//        if($g_post_id === '3') {
//            echo JSON::encode(array('post' => 'wrong'));
//            return;
//        }

        $status = 'public';
        if ($p_private === 'true') {
            $status = 'private';
        }

        $json_post = PostMulti::getPost($g_post_id, $g_revision);
        // Replace a deleted status with private, if the post is private - the origional may have been deleted
        // but not the recipient who is requesting it.
        if (is_array($json_post) === true && isset($json_post['status']) === true && $status === 'private') {
            $json_post['status'] = 'private';
        }


        $json = array();
        if (is_array($json_post) === false) {
            $json = array(
                'success' => false,
                'error_code' => $json_post,
            );
        } else {
            $json = array(
                'success' => true,
                'post' => $json_post,
            );
        }

        echo JSON::encode($json);
    }

    /**
     * Fetches all revisions of an post as an array.
     *
     * @param string $g_post_id The id of the post to fetch revisions for.
     * @param string [$p_private=false] Is the post private. Note string values of 'false' and 'true'
     *
     * @return void
     */
    public function actionRevisions($g_post_id, $p_private='false') {
        $json = array();

        $status = 'public';
        if ($p_private === 'true') {
            $status = 'private';
        }

        $last_revision = PostMulti::getPost($g_post_id);

        if (is_array($last_revision) === true) {

            // Replace a deleted status with private, if the post is private - the original may have been deleted
            // but not the recipient who is requesting it.
            if (isset($json['status']) === true && $status === 'private') {
                $json['status'] = 'private';
            }
            array_push($json, $last_revision);
            if ((int)$last_revision['revision'] > 1) {
                for ($revision = $last_revision['revision'] - 1; $revision > 0; $revision--) {
                    $next_revision = PostMulti::getPost($g_post_id, $revision);
                    array_unshift($json, $next_revision);
                }
            }

        } else {
            $json = array(
                'error_code' => $json,
            );
        }

        echo JSON::encode($json);
    }

    /**
     * Returns a JSON object containing all the takes against this post. Used by Rhythms.
     *
     * @return void
     */
    public function actionTakes() {
        if (ctype_digit($_GET['post_id']) === false) {
            throw new CHttpException(400, 'Bad Request. post_id is not a positive integer.');
        }

        $json_array = Post::getTakes($_GET['post_id']);

        echo JSON::encode($json_array);
    }

    /**
     * Returns a JSON object containing a single users take of an post.
     *
     * Possible error codes are
     * post_domain_not_found
     * post_not_found
     * user_domain_not_found
     * user_not_found
     *
     * @return void
     */
    public function actionUserTake($g_domain, $g_post_id, $g_user_domain, $g_username) {

        if (ctype_digit($_GET['post_id']) === false) {
            throw new CHttpException(400, 'Bad Request. post_id is not a positive integer.');
        }

        $local_post_id = PostMulti::getLocalPostId($g_domain, $g_post_id);
        if (ctype_digit($local_post_id) === false) {
            if ($local_post_id === 'domain_not_found') {
                $error = 'post_domain_not_found';
            } else if ($local_post_id === 'post_not_found') {
                $error = 'post_not_found';
            }
        }

        $user_site_id = SiteMulti::getSiteID($g_user_domain, true, true);
        if ($user_site_id === false) {
            $error = 'user_domain_not_found';
        } else {
            $user_multi = new UserMulti($user_site_id);
            $user_id = $user_multi->getIDFromUsername($g_username, false, true);
        }
        if ($user_site_id === false) {
            $error = 'user_not_found';
        }

        if (isset($error) === true) {
            $json_array = array('error' => $error);
            echo JSON::encode($json_array);
        }

        // !!! If the post has just been fetched from a remote domain then we need to fetch the take value
        // however takes with zero value are deleted, which would result in constantly refetching it if it has
        // not been taken.
        // need to keep a record of a take with zero value if it is for a remote post.
        $value = Take::getTakeByUser($user_id, $user_site_id, $local_post_id);
        $json_array = array('value' => $value);
        echo JSON::encode($json_array);
    }

}

?>